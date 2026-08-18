<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * BOLD 主题入口：常量、配置面板、模块加载与钩子注册。
 * 具体实现按职责拆分在 inc/ 目录。
 */

define('BOLD_VERSION', '1.4.1');
define('BOLD_UNLOCK_TTL', 604800);   // 解锁 Cookie 有效期（秒）= 7 天
define('BOLD_READING_SPEED', 300);   // 阅读速度（字/分钟）
define('BOLD_EXCERPT_LENGTH', 140);  // 默认摘要长度
define('BOLD_PRISM_VERSION', '1.29.0');
define('BOLD_TOCBOT_VERSION', '4.18.2');
define('BOLD_MATHJAX_VERSION', '3.2.2');
define('BOLD_MERMAID_VERSION', '10.9.6');

require_once __DIR__ . '/inc/i18n.php';
require_once __DIR__ . '/inc/password.php';
require_once __DIR__ . '/inc/content.php';
require_once __DIR__ . '/inc/comments.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/seo.php';

/**
 * 主题后台配置面板
 */
function themeConfig($form) {
    // 只在后台主题设置页执行阅读量列迁移，前台请求保持只读。
    bold_ensure_views_column();

    // 1. 语言设置
    $languageSetting = new Typecho_Widget_Helper_Form_Element_Radio('languageSetting',
        array('en' => _t('English (英文)'), 'cn' => _t('Chinese (中文)')),
        'en', _t('界面标题语言'), _t('切换侧边栏、评论区等标题的显示语言'));
    $form->addInput($languageSetting);

    // 2. 站点 Logo 文字
    $logoText = new Typecho_Widget_Helper_Form_Element_Text('logoText', NULL, NULL, _t('站点 Logo 文字'), _t('支持 HTML，例如 <span class="text-pink-600">.</span>'));
    $form->addInput($logoText);

    $faviconUrl = new Typecho_Widget_Helper_Form_Element_Text('faviconUrl', NULL, NULL, _t('Favicon 图标 URL'), _t('浏览器标签页图标，留空则不显示'));
    $form->addInput($faviconUrl);

    $AuthorName = new Typecho_Widget_Helper_Form_Element_Text('AuthorName', NULL, NULL, _t('作者名称'), _t('作者名称'));
    $form->addInput($AuthorName);

    $avatarUrl = new Typecho_Widget_Helper_Form_Element_Text('avatarUrl', NULL, NULL, _t('个人头像 URL'), _t('输入头像图片的地址，将显示在侧边栏或个人卡片中'));
    $form->addInput($avatarUrl);

    $descriptions = new Typecho_Widget_Helper_Form_Element_Text('descriptions', NULL, NULL, _t('个人简介'), _t('个人简介'));
    $form->addInput($descriptions);

    $githubLink = new Typecho_Widget_Helper_Form_Element_Text('githubLink', NULL, NULL, _t('GitHub 链接'), _t('您的 GitHub 主页地址'));
    $form->addInput($githubLink);

    $bilibiliLink = new Typecho_Widget_Helper_Form_Element_Text('bilibiliLink', NULL, NULL, _t('Bilibili 链接'), _t('您的 Bilibili 主页地址'));
    $form->addInput($bilibiliLink);

    $email = new Typecho_Widget_Helper_Form_Element_Text('email', NULL, NULL, _t('Email'), _t('您的邮箱地址（自动补全 mailto: 链接）'));
    $form->addInput($email);

    $icpNum = new Typecho_Widget_Helper_Form_Element_Text('icpNum', NULL, NULL, _t('ICP 备案号'), _t('中国大陆网站需填写，显示在页脚'));
    $form->addInput($icpNum);

    // 3. 资源加载
    $loadGoogleFonts = new Typecho_Widget_Helper_Form_Element_Radio('loadGoogleFonts',
        array('1' => _t('加载'), '0' => _t('不加载（使用系统字体）')),
        '1', _t('加载 Google Fonts 字体'), _t('中国大陆无法访问 fonts.googleapis.com，大陆站点建议选择「不加载」以避免长时间白屏'));
    $form->addInput($loadGoogleFonts);

    // 4. 自定义头部/底部代码
    $customHead = new Typecho_Widget_Helper_Form_Element_Textarea('customHead', NULL, NULL, _t('自定义头部 HTML'), _t('位于 &lt;/head&gt; 之前，可填写自定义 CSS 或 验证 meta 标签'));
    $form->addInput($customHead);

    $customFooter = new Typecho_Widget_Helper_Form_Element_Textarea('customFooter', NULL, NULL, _t('自定义底部 HTML'), _t('位于 &lt;/body&gt; 之前，可填写 Google/百度统计代码或自定义 JS'));
    $form->addInput($customFooter);

    // 5. Cloudflare Turnstile 配置
    $turnstileSiteKey = new Typecho_Widget_Helper_Form_Element_Text('turnstileSiteKey', NULL, NULL, _t('Turnstile Site Key'), _t('Cloudflare Turnstile 站点密钥。需与 Secret Key 同时填写才会启用'));
    $form->addInput($turnstileSiteKey);

    $turnstileSecretKey = new Typecho_Widget_Helper_Form_Element_Text('turnstileSecretKey', NULL, NULL, _t('Turnstile Secret Key'), _t('Cloudflare Turnstile 密钥。需与 Site Key 同时填写才会启用'));
    $form->addInput($turnstileSecretKey);

    // 6. 侧边栏作者名称
    $sidebarAuthorName = new Typecho_Widget_Helper_Form_Element_Text('sidebarAuthorName', NULL, NULL, _t('侧边栏作者名称'), _t('显示在侧边栏个人卡片中的名称，留空则使用「作者名称」'));
    $form->addInput($sidebarAuthorName);

    // 7. 打赏功能设置
    $wechatQrUrl = new Typecho_Widget_Helper_Form_Element_Text('wechatQrUrl', NULL, NULL, _t('微信收款码 URL'), _t('微信打赏二维码图片地址，留空则显示占位符'));
    $form->addInput($wechatQrUrl);

    $alipayQrUrl = new Typecho_Widget_Helper_Form_Element_Text('alipayQrUrl', NULL, NULL, _t('支付宝收款码 URL'), _t('支付宝打赏二维码图片地址，留空则显示占位符'));
    $form->addInput($alipayQrUrl);

    // 8. 默认封面图
    $defaultOgImage = new Typecho_Widget_Helper_Form_Element_Text('defaultOgImage', NULL, NULL, _t('默认封面图 URL'), _t('当文章没有图片时使用的默认社交分享封面图，留空则不输出 og:image'));
    $form->addInput($defaultOgImage);

    // 9. 文章/分类密码保护
    $postPassword = new Typecho_Widget_Helper_Form_Element_Text('postPassword', NULL, NULL, _t('全站加密密码'), _t('设置后，访客需要输入密码才能查看所有文章内容。留空则不启用'));
    $form->addInput($postPassword);

    $passwordProtectedCategories = new Typecho_Widget_Helper_Form_Element_Text('passwordProtectedCategories', NULL, NULL, _t('加密分类 (用英文逗号分隔)'), _t('输入需要密码保护的分类别名(slug)，多个用逗号分隔。例如: private,secret'));
    $form->addInput($passwordProtectedCategories);

    $categoryPasswords = new Typecho_Widget_Helper_Form_Element_Textarea('categoryPasswords', NULL, NULL, _t('分类独立密码设置'), _t('为不同的分类设置不同的密码。格式：分类slug:密码，每行一个。例如：<br>private:password123<br>secret:mySecret456<br>如果某分类未单独设置密码，将使用全站加密密码'));
    $form->addInput($categoryPasswords);

    $hideProtectedCategoriesFromHome = new Typecho_Widget_Helper_Form_Element_Radio('hideProtectedCategoriesFromHome',
        array('1' => _t('隐藏'), '0' => _t('显示')),
        '0', _t('加密分类文章在首页的显示'), _t('选择是否在首页隐藏属于加密分类的文章'));
    $form->addInput($hideProtectedCategoriesFromHome);

    $requireCategoryArchivePassword = new Typecho_Widget_Helper_Form_Element_Radio('requireCategoryArchivePassword',
        array('1' => _t('需要'), '0' => _t('不需要')),
        '1', _t('加密分类的归档页面是否需要密码验证'), _t('选择访问加密分类的归档页面时是否需要输入密码。选择"需要"时，访问加密分类归档页面需要先验证密码才能查看文章列表'));
    $form->addInput($requireCategoryArchivePassword);

    $protectFeed = new Typecho_Widget_Helper_Form_Element_Radio('protectFeed',
        array('1' => _t('保护（推荐）'), '0' => _t('不保护')),
        '1', _t('RSS/Atom 订阅源中的加密文章'), _t('Typecho 的订阅源不经过主题模板。选择「保护」时，受密码保护的文章在订阅源中只输出提示，不泄露正文与内联密码'));
    $form->addInput($protectFeed);
}

/**
 * 首页查询层过滤：开启「首页隐藏加密分类」后，直接在 SQL 中排除
 * 加密分类的文章，每页条数保持正确（不再依赖渲染循环里跳过）。
 *
 * 挂在 Widget_Archive 的 query 钩子上——它在主题 functions.php
 * 加载之后才触发（handleInit 触发于加载之前，主题注册不到）。
 * 早期回调只修改共享 SQL，末尾回调仅在其他插件没有取数时恢复核心取数。
 */
class BoldHooks {
    public static function filterProtectedFromIndex($archive, $select) {
        try {
            $options = Helper::options();
            $hideEnabled = !empty($options->hideProtectedCategoriesFromHome)
                && $options->hideProtectedCategoriesFromHome == '1';

            if ($hideEnabled && $archive->is('index')) {
                $slugs = bold_get_protected_slugs();
                if (!empty($slugs)) {
                    $db = Typecho_Db::get();
                    $rows = $db->fetchAll($db->select('mid')->from('table.metas')
                        ->where('type = ?', 'category')
                        ->where('slug IN ?', $slugs));
                    $mids = array();
                    foreach ($rows as $row) {
                        $mids[] = intval($row['mid']);
                    }
                    if (!empty($mids)) {
                        $joinTable = 'table.relationships AS bold_protected_relationships';
                        $joinCondition = 'table.contents.cid = bold_protected_relationships.cid'
                            . ' AND bold_protected_relationships.mid IN (' . implode(',', $mids) . ')';
                        $select->join($joinTable, $joinCondition, Typecho_Db::LEFT_JOIN)
                            ->where('bold_protected_relationships.mid IS NULL');

                        // query 钩子触发前核心已克隆 countSql；同步过滤条件，
                        // 避免首页隐藏后的总页数继续包含受保护文章。
                        $countSql = $archive->getCountSql();
                        if ($countSql) {
                            $countSql->join($joinTable, $joinCondition, Typecho_Db::LEFT_JOIN)
                                ->where('bold_protected_relationships.mid IS NULL');
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // 过滤失败时退回模板层兜底（index.php 的 continue），不影响取数
        }
    }

    public static function fetchArchiveQuery($archive, $select) {
        if (!$archive->have()) {
            Typecho_Db::get()->fetchAll($select, array($archive, 'push'));
        }
    }
}

/**
 * Typecho 在主题函数加载后、Feed 输出前提供的初始化时机。
 */
function themeInit($archive) {
    // 列表摘要和可见条目可能随访客的解锁 Cookie 改变。主题初始化发生在
    // HTML 输出之前，此处尽早阻止 CDN/共享缓存混用已解锁与未解锁页面。
    $isListing = $archive->is('index') || $archive->is('archive')
        || $archive->is('category') || $archive->is('tag')
        || $archive->is('search') || $archive->is('author') || $archive->is('date');
    if (!bold_is_feed($archive) && $isListing && bold_listings_may_vary_by_unlock_cookie()) {
        bold_private_cache_headers();
    }

    bold_protect_feed_metadata($archive);
}

// 评论人机验证
Typecho_Plugin::factory('Widget_Feedback')->comment = array('ThemeHooks', 'verifyTurnstile');
// 匿名评论成功入库后签发服务器证明；待审状态仍需数据库 approved 才解锁。
Typecho_Plugin::factory('Widget_Feedback')->finishComment = array('ThemeHooks', 'rememberReplyAuthorization');

// RSS/Atom 订阅源保护 + 正文图片懒加载
Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = 'bold_content_filter';
Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = 'bold_excerpt_filter';

// 首页查询层过滤加密分类（query 钩子：主题 functions.php 加载后、主查询执行前触发）。
// 仅在确实开启了「首页隐藏加密分类」且配置了加密分类时才注册。
// query_0 使过滤先于普通插件，query_1000000 在末尾检查是否需要兜底取数，
// 避免与已经执行 fetchAll 的插件重复压入同一批文章。
if (Helper::options()->hideProtectedCategoriesFromHome == '1' && count(bold_get_protected_slugs()) > 0) {
    $archiveFactory = Typecho_Plugin::factory('Widget_Archive');
    $archiveFactory->{'query_0'} = array('BoldHooks', 'filterProtectedFromIndex');
    $archiveFactory->{'query_1000000'} = array('BoldHooks', 'fetchArchiveQuery');
}

// 评论订阅源保护：受保护文章的评论不以公开 XML 外泄
Typecho_Plugin::factory('Widget_Abstract_Comments')->contentEx = 'bold_comment_feed_filter';
Typecho_Plugin::factory('Widget_Archive')->{'commentFeedItem_0'} = 'bold_comment_feed_item_filter';
