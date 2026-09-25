<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 获取主题多语言文本
 *
 * @param string $key 词条键名
 * @param mixed $archive 当前归档对象（保留参数以兼容旧调用）
 * @return string
 */
function get_theme_text($key, $archive = null) {
    $lang = Helper::options()->languageSetting;
    if (empty($lang)) $lang = 'en';

    $texts = array(
        'search'                      => array('en' => 'SEARCH', 'cn' => '搜索'),
        'search_placeholder'          => array('en' => 'Type keywords...', 'cn' => '输入关键词...'),
        'categories'                  => array('en' => 'CATEGORIES', 'cn' => '分类'),
        'comments'                    => array('en' => 'COMMENTS', 'cn' => '评论'),
        'tags'                        => array('en' => 'TAGS', 'cn' => '标签'),
        'toc'                         => array('en' => 'TABLE OF CONTENTS', 'cn' => '文章目录'),
        'toggle_toc'                  => array('en' => 'Toggle table of contents', 'cn' => '展开或收起文章目录'),
        'menu'                        => array('en' => 'Menu', 'cn' => '菜单'),
        'main_navigation'             => array('en' => 'Main navigation', 'cn' => '主导航'),
        'skip_content'                => array('en' => 'SKIP TO CONTENT', 'cn' => '跳到主要内容'),
        'leave_reply'                 => array('en' => 'LEAVE A REPLY', 'cn' => '发表评论'),
        'submit_comment'              => array('en' => 'SUBMIT COMMENT', 'cn' => '提交评论'),
        'cancel_reply'                => array('en' => 'Cancel', 'cn' => '取消回复'),
        'comment_waiting'             => array('en' => 'PENDING REVIEW', 'cn' => '待审核'),
        'related_posts'               => array('en' => 'YOU MAY ALSO LIKE', 'cn' => '相关推荐'),
        'no_related'                  => array('en' => 'Nothing related yet, explore something else.', 'cn' => '暂无相关推荐，看看别的吧。'),
        'timeline_title'              => array('en' => 'TIMELINE <span class="text-white">ARCHIVE</span>', 'cn' => '时间轴 <span class="text-white">归档</span>'),
        'timeline_desc'               => array('en' => 'Record every shining moment.', 'cn' => '记录每一个闪光的瞬间。'),
        'links_title'                 => array('en' => 'FRIENDS <span class="text-white">LINKS</span>', 'cn' => '友情 <span class="text-white">链接</span>'),
        'links_desc'                  => array('en' => 'Connect the unknown islands.', 'cn' => '连接未知的孤岛。'),
        'home'                        => array('en' => 'HOME', 'cn' => '首页'),
        'read_more'                   => array('en' => 'READ MORE', 'cn' => '阅读全文'),
        'no_content'                  => array('en' => 'Nothing found.', 'cn' => '没有找到内容'),
        'prev_page'                   => array('en' => '← PREV', 'cn' => '← 上一页'),
        'next_page'                   => array('en' => 'NEXT →', 'cn' => '下一页 →'),
        'page'                        => array('en' => 'PAGE', 'cn' => 'PAGE'),
        'page_n'                      => array('en' => 'Page %d', 'cn' => '第 %d 页'),
        'no_tags'                     => array('en' => 'No tags', 'cn' => '无标签'),
        'back_home'                   => array('en' => 'BACK HOME', 'cn' => '返回首页'),
        'go_back'                     => array('en' => 'GO BACK', 'cn' => '返回上一页'),
        'close'                       => array('en' => 'CLOSE / 关闭', 'cn' => '关闭 / CLOSE'),
        'reward'                      => array('en' => '$ BUY ME A COFFEE', 'cn' => '$ 打赏一杯咖啡'),
        'reward_thanks'               => array('en' => 'THANK YOU!', 'cn' => '感谢支持！'),
        'copy_link'                   => array('en' => 'COPY LINK', 'cn' => '复制链接'),
        'link_copied'                 => array('en' => 'LINK COPIED', 'cn' => '链接已复制'),
        'copy_failed'                 => array('en' => 'COPY FAILED', 'cn' => '复制失败'),
        'post_navigation'             => array('en' => 'More posts', 'cn' => '继续阅读'),
        'previous_post'               => array('en' => '← PREVIOUS', 'cn' => '← 上一篇'),
        'next_post'                   => array('en' => 'NEXT →', 'cn' => '下一篇 →'),
        'password_required'           => array('en' => 'PASSWORD REQUIRED', 'cn' => '需要密码'),
        'password_placeholder'        => array('en' => 'Enter password...', 'cn' => '请输入密码...'),
        'password_submit'             => array('en' => 'UNLOCK', 'cn' => '解锁'),
        'password_error'              => array('en' => 'Incorrect password, please try again', 'cn' => '密码错误，请重试'),
        'password_protected_content'  => array('en' => 'This content is password protected. Please enter the password to view.', 'cn' => '此内容受密码保护，请输入密码查看。'),
        'password_protected_category' => array('en' => 'This content belongs to the encrypted category "%s". Please enter the category password to view.', 'cn' => '此内容属于加密分类「%s」，请输入分类密码查看。'),
        'inline_password_title'       => array('en' => 'PASSWORD PROTECTED', 'cn' => '密码保护内容'),
        'inline_password_placeholder' => array('en' => 'Enter password to unlock...', 'cn' => '输入密码解锁此内容...'),
        'protected_excerpt'           => array('en' => '🔐 This content is password protected...', 'cn' => '🔐 此文章内容受密码保护...'),
        'feed_protected'              => array('en' => 'This article is password protected. Please visit the site and unlock it with the password.', 'cn' => '本文受密码保护，请访问网站原文并输入密码查看。'),
        'protected_comment_author'    => array('en' => 'Protected comment', 'cn' => '受保护评论'),
        'unlocked'                    => array('en' => '🔓 UNLOCKED', 'cn' => '🔓 内容已解锁 / UNLOCKED'),
        'unlocked_author'             => array('en' => '🔓 UNLOCKED (Author)', 'cn' => '🔓 内容已解锁（作者）/ UNLOCKED (Author)'),
        'locked_title'                => array('en' => 'LOCKED CONTENT', 'cn' => 'LOCKED CONTENT'),
        'locked_desc'                 => array('en' => 'This section is hidden.<br>Reply below and refresh the page to view.', 'cn' => '此区域包含隐藏内容。<br>请在下方评论后刷新页面查看。'),
        'go_reply'                    => array('en' => 'REPLY', 'cn' => '去评论 / REPLY'),
    );

    return isset($texts[$key][$lang]) ? $texts[$key][$lang] : '';
}
