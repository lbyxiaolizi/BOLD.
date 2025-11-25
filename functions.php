<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 主题后台配置面板
 */
function themeConfig($form) {
    $logoText = new Typecho_Widget_Helper_Form_Element_Text('logoText', NULL, NULL, _t('站点 Logo 文字'), _t('支持 HTML，例如 <span class="text-pink-600">.</span>'));
    $form->addInput($logoText);

    $avatarUrl = new Typecho_Widget_Helper_Form_Element_Text('avatarUrl', NULL, NULL, _t('个人头像 URL'), _t('输入头像图片的地址，将显示在侧边栏或个人卡片中'));
    $form->addInput($avatarUrl);
    
    $descriptions = new Typecho_Widget_Helper_Form_Element_Text('descriptions', NULL, NULL, _t('个人简介'), _t('个人简介'));
    $form->addInput($descriptions);

    $githubLink = new Typecho_Widget_Helper_Form_Element_Text('githubLink', NULL, NULL, _t('GitHub 链接'), _t('您的 GitHub 主页地址'));
    $form->addInput($githubLink);

    $bilibiliLink = new Typecho_Widget_Helper_Form_Element_Text('bilibiliLink', NULL, NULL, _t('Bilibili 链接'), _t('您的 Bilibili 主页地址'));
    $form->addInput($bilibiliLink);
    
    $email = new Typecho_Widget_Helper_Form_Element_Text('email', NULL, NULL, _t('email'), _t('您的email'));
    $form->addInput($email);
}

/**
 * 自定义评论输出结构
 */
function threadedComments($comments, $options) {
    $commentClass = '';
    if ($comments->authorId) {
        if ($comments->authorId == $comments->ownerId) {
            $commentClass .= ' comment-by-author';
        } else {
            $commentClass .= ' comment-by-user';
        }
    }
?>
<li id="li-<?php $comments->theId(); ?>" class="comment-body<?php 
    if ($comments->levels > 0) {
        echo ' comment-child';
        $comments->levelsAlt(' comment-level-odd', ' comment-level-even');
    } else {
        echo ' comment-parent';
    }
    $comments->alt(' comment-odd', ' comment-even');
    echo $commentClass;
?> mb-8 list-none">
    <div id="<?php $comments->theId(); ?>" class="flex flex-col md:flex-row gap-4">
        <div class="flex-shrink-0">
            <div class="w-12 h-12 border-2 border-black overflow-hidden shadow-[2px_2px_0px_0px_#000]">
                <?php $comments->gravatar('48', ''); ?>
            </div>
        </div>
        <div class="flex-grow">
            <div class="bg-white border-2 border-black p-4 relative shadow-[4px_4px_0px_0px_#1a1a1a] transition-transform hover:-translate-y-1">
                <div class="absolute top-4 -left-2 w-4 h-4 bg-white border-b-2 border-l-2 border-black transform rotate-45 hidden md:block"></div>
                <div class="absolute -top-2 left-4 w-4 h-4 bg-white border-t-2 border-l-2 border-black transform rotate-45 md:hidden"></div>
                <div class="flex flex-wrap justify-between items-center mb-2 border-b-2 border-gray-100 pb-2">
                    <div class="flex items-center gap-2">
                        <span class="font-black text-lg uppercase"><?php $comments->author(); ?></span>
                        <?php if ($comments->authorId == $comments->ownerId): ?>
                            <span class="bg-black text-white text-[10px] px-1 font-bold">AUTHOR</span>
                        <?php endif; ?>
                    </div>
                    <span class="text-xs font-bold text-gray-500 font-mono"><?php $comments->date('Y-m-d H:i'); ?></span>
                </div>
                <div class="font-medium text-gray-800 prose prose-sm max-w-none mb-3">
                    <?php $comments->content(); ?>
                </div>
                <div class="text-right">
                    <?php $comments->reply('<span class="inline-block text-xs font-black bg-black text-white px-2 py-1 hover:bg-pink-500 transition-colors cursor-pointer border-2 border-transparent hover:border-black">REPLY</span>'); ?>
                </div>
            </div>
        </div>
    </div>
    <?php if ($comments->children) { ?>
        <div class="comment-children ml-0 md:ml-16 mt-4 border-l-4 border-gray-200 pl-4">
            <?php $comments->threadedComments($options); ?>
        </div>
    <?php } ?>
</li>
<?php } 

/**
 * 文章阅读量统计函数
 */
function getPostViews($archive) {
    $cid    = $archive->cid;
    $db     = Typecho_Db::get();
    $prefix = $db->getPrefix();
    if (!array_key_exists('views', $db->fetchRow($db->select()->from('table.contents')))) {
        $db->query('ALTER TABLE `' . $prefix . 'contents` ADD `views` INT(10) DEFAULT 0;');
        echo 0; return;
    }
    $row = $db->fetchRow($db->select('views')->from('table.contents')->where('cid = ?', $cid));
    if ($archive->is('single')) {
        $views = Typecho_Cookie::get('extend_contents_views');
        if (empty($views)) { $views = array(); } else { $views = explode(',', $views); }
        if (!in_array($cid, $views)) {
            $db->query($db->update('table.contents')->rows(array('views' => (int) $row['views'] + 1))->where('cid = ?', $cid));
            array_push($views, $cid);
            $views = implode(',', $views);
            Typecho_Cookie::set('extend_contents_views', $views);
        }
    }
    echo $row['views'];
}

/**
 * 新增功能：获取预计阅读时间
 */
function getReadingTime($archive) {
    $content = $archive->content;
    $content = ($content === null) ? '' : strval($content);
    $text = trim(strip_tags($content));
    $textLen = mb_strlen($text, 'UTF-8');
    // 假设阅读速度为 300 字/分钟，最少 1 分钟
    $readTime = ceil($textLen / 300);
    return max(1, $readTime);
}

/**
 * 新增功能：获取相关文章 (基于标签)
 */
function getRelatedPosts($archive, $limit = 3) {
    $db = Typecho_Db::get();
    $tags = $archive->tags;
    
    if ($tags) {
        $tagIds = array();
        foreach ($tags as $tag) {
            $tagIds[] = $tag['mid'];
        }
        
        $related = $db->fetchAll($db->select()->from('table.contents')
            ->join('table.relationships', 'table.contents.cid = table.relationships.cid')
            ->where('table.relationships.mid IN ?', $tagIds)
            ->where('table.contents.cid != ?', $archive->cid)
            ->where('table.contents.type = ?', 'post')
            ->where('table.contents.status = ?', 'publish')
            ->limit($limit)
            ->order('table.contents.created', Typecho_Db::SORT_DESC));
            
        if ($related) {
            foreach ($related as $post) {
                $post = Typecho_Widget::widget('Widget_Abstract_Contents')->push($post);
                $title = $post['title'];
                $permalink = $post['permalink'];
                $date = date('Y-m-d', $post['created']);
                echo "<li>
                        <a href='$permalink' class='flex justify-between items-center border-2 border-black p-3 hover:bg-yellow-200 transition-colors group'>
                            <span class='font-bold truncate group-hover:text-pink-600 transition-colors'>$title</span>
                            <span class='text-xs font-mono whitespace-nowrap ml-2 bg-black text-white px-1'>$date</span>
                        </a>
                      </li>";
            }
        } else {
            echo '<li class="p-3 border-2 border-dashed border-black text-gray-500 text-sm bg-gray-50">暂无相关推荐，看看别的吧。</li>';
        }
    } else {
         echo '<li class="p-3 border-2 border-dashed border-black text-gray-500 text-sm bg-gray-50">暂无相关推荐，看看别的吧。</li>';
    }
}

/**
 * SEO 助手函数
 */
function get_seo_description($archive) {
    if ($archive->is('index')) { return Helper::options()->description; }
    if ($archive->is('post') || $archive->is('page')) {
        $description = '';
        if (isset($archive->excerpt) && is_string($archive->excerpt) && !empty($archive->excerpt)) {
            $description = $archive->excerpt;
        } else if (isset($archive->content) && is_string($archive->content)) {
            $description = $archive->content;
        }
        if (!is_string($description)) { $description = ''; }
        $description = strip_tags($description);
        $description = str_replace(array("\r\n", "\r", "\n"), "", $description);
        return mb_substr($description, 0, 150, 'utf-8') . '...';
    }
    if ($archive->is('category')) { return $archive->getDescription() ? $archive->getDescription() : $archive->getArchiveTitle(); }
    return Helper::options()->description;
}

function get_og_image($archive) {
    $default_img = 'https://cdn.tailwindcss.com/img/card-top.jpg'; 
    if (($archive->is('post') || $archive->is('page'))) {
        $content = '';
        if (isset($archive->content) && is_string($archive->content)) { $content = $archive->content; }
        if (!empty($content)) {
            preg_match_all("/<img.*?src=\"(.*?)\".*?>/i", $content, $matches);
            if (isset($matches[1][0])) { return $matches[1][0]; }
        }
    }
    return $default_img;
}
?>