<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 主题后台配置面板
 */
function themeConfig($form) {
    // 1. 语言设置 (新增)
    $languageSetting = new Typecho_Widget_Helper_Form_Element_Radio('languageSetting',
        array('en' => _t('English (英文)'), 'cn' => _t('Chinese (中文)')),
        'en', _t('界面标题语言'), _t('切换侧边栏、评论区等标题的显示语言'));
    $form->addInput($languageSetting);

    // 2. 站点 Logo 文字
    $logoText = new Typecho_Widget_Helper_Form_Element_Text('logoText', NULL, NULL, _t('站点 Logo 文字'), _t('支持 HTML，例如 <span class="text-pink-600">.</span>'));
    $form->addInput($logoText);
    
    $faviconUrl = new Typecho_Widget_Helper_Form_Element_Text('faviconUrl', NULL, NULL, _t('Favicon 图标 URL'), _t('浏览器标签页图标，留空则不显示'));
    $form->addInput($faviconUrl);


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
    
    $icpNum = new Typecho_Widget_Helper_Form_Element_Text('icpNum', NULL, NULL, _t('ICP 备案号'), _t('中国大陆网站需填写，显示在页脚'));
    $form->addInput($icpNum);

    // 7. 自定义头部/底部代码 (新增)
    $customHead = new Typecho_Widget_Helper_Form_Element_Textarea('customHead', NULL, NULL, _t('自定义头部 HTML'), _t('位于 &lt;/head&gt; 之前，可填写自定义 CSS 或 验证 meta 标签'));
    $form->addInput($customHead);

    $customFooter = new Typecho_Widget_Helper_Form_Element_Textarea('customFooter', NULL, NULL, _t('自定义底部 HTML'), _t('位于 &lt;/body&gt; 之前，可填写 Google/百度统计代码或自定义 JS'));
    $form->addInput($customFooter);
    
    // 5. Cloudflare Turnstile 配置 (新增)
    $turnstileSiteKey = new Typecho_Widget_Helper_Form_Element_Text('turnstileSiteKey', NULL, NULL, _t('Turnstile Site Key'), _t('Cloudflare Turnstile 站点密钥，留空则不启用'));
    $form->addInput($turnstileSiteKey);

    $turnstileSecretKey = new Typecho_Widget_Helper_Form_Element_Text('turnstileSecretKey', NULL, NULL, _t('Turnstile Secret Key'), _t('Cloudflare Turnstile 密钥，留空则不启用'));
    $form->addInput($turnstileSecretKey);
}

/**
 * 获取主题多语言文本 (新增)
 */
function get_theme_text($key, $archive) {
    // 修复点：使用 Helper::options() 获取全局配置，避免 $archive->options 为空导致的错误
    $lang = Helper::options()->languageSetting;
    if (empty($lang)) $lang = 'en'; // 默认为英文

    $texts = array(
        'search' => array('en' => 'SEARCH', 'cn' => '搜索'),
        'categories' => array('en' => 'CATEGORIES', 'cn' => '分类'),
        'comments' => array('en' => 'COMMENTS', 'cn' => '评论'),
        'toc' => array('en' => 'TABLE OF CONTENTS', 'cn' => '文章目录'),
        'leave_reply' => array('en' => 'LEAVE A REPLY', 'cn' => '发表评论'),
        'submit_comment' => array('en' => 'SUBMIT COMMENT', 'cn' => '提交评论'),
        'related_posts' => array('en' => 'YOU MAY ALSO LIKE', 'cn' => '相关推荐'),
        'timeline_title' => array('en' => 'TIMELINE <span class="text-white">ARCHIVE</span>', 'cn' => '时间轴 <span class="text-white">归档</span>'),
        'links_title' => array('en' => 'FRIENDS <span class="text-white">LINKS</span>', 'cn' => '友情 <span class="text-white">链接</span>'),
    );

    return isset($texts[$key][$lang]) ? $texts[$key][$lang] : '';
}

/**
 * 评论验证钩子
 */
class ThemeHooks {
    public static function verifyTurnstile($comment, $post) {
        $options = Helper::options();
        $siteKey = $options->turnstileSiteKey;
        $secretKey = $options->turnstileSecretKey;

        if (empty($siteKey) || empty($secretKey)) {
            return $comment;
        }

        $token = Typecho_Request::getInstance()->get('cf-turnstile-response');
        if (empty($token)) {
            throw new Typecho_Widget_Exception(_t('请完成人机验证 (Please complete the CAPTCHA)'));
        }

        $ip = Typecho_Request::getInstance()->getIp();
        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        $data = [
            'secret' => $secretKey,
            'response' => $token,
            'remoteip' => $ip
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        if (!$result['success']) {
            throw new Typecho_Widget_Exception(_t('人机验证失败，请刷新重试'));
        }

        return $comment;
    }
}
Typecho_Plugin::factory('Widget_Feedback')->comment = array('ThemeHooks', 'verifyTurnstile');

/**
 * 核心逻辑：评论可见
 */
function parseReplyContent($content, $archive) {
    if (!$archive->is('single')) {
        return preg_replace("/{hide}(.*?){\/hide}/sm", '', $content);
    }

    if (strpos($content, '{hide}') !== false) {
        $db = Typecho_Db::get();
        $hasComment = false;
        
        $user = Typecho_Widget::widget('Widget_User');

        if ($user->hasLogin() && $user->uid == $archive->authorId) {
            $hasComment = true;
        }
        elseif ($user->hasLogin()) {
            $comment = $db->fetchRow($db->select()->from('table.comments')
                ->where('cid = ?', $archive->cid)
                ->where('authorId = ?', $user->uid)
                ->limit(1));
            if ($comment) $hasComment = true;
        }
        else {
            $email = Typecho_Cookie::get('__typecho_remember_mail');
            if ($email) {
                $comment = $db->fetchRow($db->select()->from('table.comments')
                    ->where('cid = ?', $archive->cid)
                    ->where('mail = ?', $email)
                    ->limit(1));
                if ($comment) $hasComment = true;
            }
        }

        if ($hasComment) {
            $content = str_replace(array('{hide}', '{/hide}'), '', $content);
            $content = '<div class="p-4 border-l-4 border-green-500 bg-green-50 dark:bg-green-900/20 dark:border-green-400 mb-6">
                            <p class="font-bold text-green-700 dark:text-green-400 m-0">🔓 内容已解锁 / UNLOCKED</p>
                        </div>' . $content;
        } else {
            $hideNotice = '
            <div class="reply2view-container my-8">
                <div class="reply2view-inner flex flex-col items-center justify-center text-center p-6 md:p-10">
                    <div class="text-6xl mb-4">🔒</div>
                    <h3 class="text-2xl font-black uppercase mb-2">LOCKED CONTENT</h3>
                    <p class="font-bold mb-6 max-w-md">此区域包含隐藏内容。<br>请在下方评论后刷新页面查看。</p>
                    <a href="#comments" class="inline-block bg-black text-white px-8 py-3 font-black text-lg uppercase tracking-widest hover:bg-white hover:text-black transition-colors border-4 border-black shadow-[4px_4px_0px_0px_#fff] dark:shadow-[4px_4px_0px_0px_#10b981] dark:hover:bg-[#10b981] dark:hover:border-[#10b981]">
                        去评论 / REPLY
                    </a>
                </div>
            </div>
            <style>
                .reply2view-container {
                    background: repeating-linear-gradient(45deg, #fef08a, #fef08a 20px, #000 20px, #000 40px);
                    padding: 10px; border: 4px solid #000; box-shadow: 8px 8px 0px 0px #000;
                }
                .reply2view-inner { background: #fff; border: 4px solid #000; }
                body.dark-mode .reply2view-container {
                    background: repeating-linear-gradient(45deg, #064e3b, #064e3b 20px, #000 20px, #000 40px);
                    border-color: #10b981; box-shadow: 8px 8px 0px 0px #10b981;
                }
                body.dark-mode .reply2view-inner { background: #121212; border-color: #10b981; color: #e5e5e5; }
                body.dark-mode .reply2view-inner a.bg-black { background-color: #10b981; color: #000; border-color: #10b981; box-shadow: 4px 4px 0px 0px #000; }
                body.dark-mode .reply2view-inner a.bg-black:hover { background-color: #000; color: #10b981; box-shadow: 4px 4px 0px 0px #10b981; }
            </style>
            ';
            $content = preg_replace("/{hide}(.*?){\/hide}/sm", $hideNotice, $content);
        }
    }
    return $content;
}

/**
 * 摘要输出
 */
function printExcerpt($archive, $length = 140) {
    $content = $archive->content;
    $content = preg_replace("/{hide}(.*?){\/hide}/sm", '', $content);
    $content = strip_tags($content);
    echo Typecho_Common::subStr($content, 0, $length, '...');
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
 * 文章阅读量统计
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
 * 获取阅读时间
 */
function getReadingTime($archive) {
    $content = $archive->content;
    $content = ($content === null) ? '' : strval($content);
    $text = trim(strip_tags($content));
    $textLen = mb_strlen($text, 'UTF-8');
    $readTime = ceil($textLen / 300);
    return max(1, $readTime);
}

/**
 * 获取相关文章
 */
function getRelatedPosts($archive, $limit = 3) {
    $db = Typecho_Db::get();
    $tags = $archive->tags;
    if ($tags) {
        $tagIds = array();
        foreach ($tags as $tag) { $tagIds[] = $tag['mid']; }
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
        } else { echo '<li class="p-3 border-2 border-dashed border-black text-gray-500 text-sm bg-gray-50">暂无相关推荐，看看别的吧。</li>'; }
    } else { echo '<li class="p-3 border-2 border-dashed border-black text-gray-500 text-sm bg-gray-50">暂无相关推荐，看看别的吧。</li>'; }
}

/**
 * SEO: 纯文本描述
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

/**
 * SEO: 封面图
 */
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