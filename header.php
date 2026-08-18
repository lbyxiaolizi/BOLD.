<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
$_boldIsArticle = $this->is('post') || $this->is('page');

$_boldCurrentUrl = bold_canonical_url($this);
$_boldCurrentUrlAttr = htmlspecialchars($_boldCurrentUrl, ENT_QUOTES, 'UTF-8');

// 描述与标题统一转义后进入属性上下文（double_encode=false 避免二次转义）
$_boldDescriptionAttr = htmlspecialchars(get_seo_description($this), ENT_QUOTES, 'UTF-8', false);
$_boldSiteTitleAttr = htmlspecialchars(strval($this->options->title), ENT_QUOTES, 'UTF-8', false);
$_boldFaviconAttr = htmlspecialchars(strval($this->options->faviconUrl ?? ''), ENT_QUOTES, 'UTF-8');

ob_start();
$this->archiveTitle('', '', ' - ');
$this->options->title();
$_boldFullTitle = trim(ob_get_clean());
$_boldFullTitleAttr = htmlspecialchars($_boldFullTitle, ENT_QUOTES, 'UTF-8', false);

$_boldOgImage = get_og_image($this);
?>
<!DOCTYPE html>
<html lang="<?php echo $this->options->languageSetting === 'cn' ? 'zh-CN' : 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- 强制浏览器使用网站指定的颜色方案，防止移动端系统黑暗模式与网站白天模式冲突 -->
    <meta name="color-scheme" id="color-scheme-meta" content="light">

    <title><?php $this->archiveTitle(array(
            'category'  =>  _t('分类 %s 下的文章'),
            'search'    =>  _t('包含关键字 %s 的文章'),
            'tag'       =>  _t('标签 %s 下的文章'),
            'author'    =>  _t('%s 发布的文章')
        ), '', ' - '); ?><?php $this->options->title(); ?><?php if ($this->_currentPage > 1): ?> - <?php echo sprintf(get_theme_text('page_n', $this), $this->_currentPage); ?><?php endif; ?></title>

    <!-- Favicon -->
    <?php if ($this->options->faviconUrl): ?>
    <link rel="icon" href="<?php echo $_boldFaviconAttr; ?>" />
    <?php endif; ?>

    <!-- Canonical URL (SEO 核心: 规范化链接) -->
    <link rel="canonical" href="<?php echo $_boldCurrentUrlAttr; ?>" />

    <?php if ($this->is('search')): ?>
    <!-- 搜索结果页不进索引 -->
    <meta name="robots" content="noindex, follow" />
    <?php endif; ?>

    <!-- RSS & Atom Feeds (博客标配: 订阅源) -->
    <link rel="alternate" type="application/rss+xml" title="<?php echo $_boldSiteTitleAttr; ?> &raquo; RSS 2.0" href="<?php $this->options->feedUrl(); ?>" />
    <link rel="alternate" type="application/rdf+xml" title="<?php echo $_boldSiteTitleAttr; ?> &raquo; RSS 1.0" href="<?php $this->options->feedUrl('rss1'); ?>" />
    <link rel="alternate" type="application/atom+xml" title="<?php echo $_boldSiteTitleAttr; ?> &raquo; Atom 1.0" href="<?php $this->options->feedUrl('atom'); ?>" />

    <!-- Meta SEO -->
    <meta name="description" content="<?php echo $_boldDescriptionAttr; ?>" />
    <meta name="keywords" content="<?php $this->keywords(','); ?>" />

    <!-- Open Graph / Twitter Card (社交分享优化) -->
    <meta property="og:site_name" content="<?php echo $_boldSiteTitleAttr; ?>" />
    <meta property="og:type" content="<?php echo $this->is('post') ? 'article' : 'website'; ?>" />
    <meta property="og:url" content="<?php echo $_boldCurrentUrlAttr; ?>" />
    <meta property="og:title" content="<?php echo $_boldFullTitleAttr; ?>" />
    <meta property="og:description" content="<?php echo $_boldDescriptionAttr; ?>" />
    <?php if (!empty($_boldOgImage)): ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($_boldOgImage, ENT_QUOTES, 'UTF-8'); ?>" />
    <?php endif; ?>
    <?php if ($_boldIsArticle): ?>
    <meta property="article:published_time" content="<?php echo bold_iso8601($this->created); ?>" />
    <meta property="article:modified_time" content="<?php echo bold_iso8601($this->modified); ?>" />
    <?php endif; ?>

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo $_boldFullTitleAttr; ?>" />
    <meta name="twitter:description" content="<?php echo $_boldDescriptionAttr; ?>" />
    <?php if (!empty($_boldOgImage)): ?>
    <meta name="twitter:image" content="<?php echo htmlspecialchars($_boldOgImage, ENT_QUOTES, 'UTF-8'); ?>" />
    <?php endif; ?>

    <!-- JSON-LD Structured Data（json_encode 保证转义合法，正文含引号/反斜杠不再破坏结构） -->
    <?php
    if ($_boldIsArticle) {
        ob_start(); $this->title();  $_boldPostTitle = ob_get_clean();
        ob_start(); $this->author(); $_boldAuthorName = ob_get_clean();
        $_boldJsonLd = array(
            '@context'      => 'https://schema.org',
            '@type'         => 'BlogPosting',
            'headline'      => html_entity_decode(strip_tags($_boldPostTitle), ENT_QUOTES, 'UTF-8'),
            'datePublished' => bold_iso8601($this->created),
            'dateModified'  => bold_iso8601($this->modified),
            'author'        => array(
                '@type' => 'Person',
                'name'  => html_entity_decode(strip_tags($_boldAuthorName), ENT_QUOTES, 'UTF-8'),
            ),
            'description'   => get_seo_description($this),
            'mainEntityOfPage' => array(
                '@type' => 'WebPage',
                '@id'   => $_boldCurrentUrl,
            ),
        );
        if (!empty($_boldOgImage)) {
            $_boldJsonLd['image'] = array($_boldOgImage);
        }
    } else {
        $_boldJsonLd = array(
            '@context'    => 'https://schema.org',
            '@type'       => 'WebSite',
            'name'        => html_entity_decode(strip_tags(strval($this->options->title)), ENT_QUOTES, 'UTF-8'),
            'url'         => strval($this->options->siteUrl),
            'description' => strval($this->options->description),
        );
    }
    ?>
    <script type="application/ld+json"><?php echo json_encode(
        $_boldJsonLd,
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ); ?></script>

    <script>document.documentElement.classList.add('bold-loading', 'has-js');</script>
    <style>
        /* 防闪烁 (Anti-FOUC)：门控与解除规则必须在任何同步外链脚本前注册，
           外部 CSS/JS 加载失败时也不会把页面永远锁在隐藏状态 */
        body { transition: opacity 0.3s ease-in-out; }
        html.bold-loading body { opacity: 0; visibility: hidden; }
        html:not(.bold-loading) body, body.loaded { opacity: 1; visibility: visible; }
    </style>
    <noscript>
        <!-- 禁用 JS 时直接显示页面 -->
        <style>body { opacity: 1 !important; visibility: visible !important; }</style>
    </noscript>

    <script>
        // 主题色初始化：localStorage 读取包裹 try/catch，
        // 隐私模式/禁用存储抛异常时按浅色处理，绝不阻断后续脚本
        window.applyTheme = function() {
            var dark = false;
            try { dark = localStorage.getItem('darkMode') === 'true'; } catch (e) {}
            var targets = [document.documentElement, document.body];
            for (var i = 0; i < targets.length; i++) {
                if (!targets[i]) continue;
                targets[i].classList.toggle('dark-mode', dark);
                targets[i].classList.toggle('dark', dark);
            }
            var colorSchemeMeta = document.getElementById('color-scheme-meta');
            if (colorSchemeMeta) colorSchemeMeta.setAttribute('content', dark ? 'dark' : 'light');
            return dark;
        };
        applyTheme();
    </script>
    <script>
        // 解除首屏遮罩：与主题初始化解耦的独立块，
        // 并带 1.5s 超时兜底 —— 任何后续脚本/CDN 失败都不会白屏
        (function() {
            function reveal() {
                document.documentElement.classList.remove('bold-loading');
                if (document.body) document.body.classList.add('loaded');
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() { applyTheme(); reveal(); });
            } else { applyTheme(); reveal(); }
            window.addEventListener('load', reveal);
            setTimeout(reveal, 1500);
        })();
    </script>

    <!-- 资源引用：preconnect 提前建连 -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>

    <?php if ($this->options->loadGoogleFonts != '0'): ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- 异步加载字体样式表，不阻塞首屏渲染 -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;700;900&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;700;900&display=swap"></noscript>
    <?php endif; ?>

    <?php if ($_boldIsArticle): ?>
    <!-- Prism 样式在文章/独立页加载，覆盖渲染后才识别出代码块的兜底路径；JS 仍按内容加载 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/<?php echo BOLD_PRISM_VERSION; ?>/themes/prism-okaidia.min.css" rel="stylesheet" integrity="sha384-qTzu9jz8wpyzFe5KLoZfw0CS5iY+kCoZlBd5ByJ3f0NUT9dgCIU19M1IQKj594Ei" crossorigin="anonymous" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/<?php echo BOLD_PRISM_VERSION; ?>/plugins/toolbar/prism-toolbar.min.css" rel="stylesheet" integrity="sha384-EUzJ34/1CCeefTGUKLgvA5Z/vYIwi+Jyu8aAaCfFDxfwZ3Xs3OfkkIeegsLRM11e" crossorigin="anonymous" />
    <?php endif; ?>

    <?php if ($_boldIsArticle): ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tocbot/<?php echo BOLD_TOCBOT_VERSION; ?>/tocbot.css" integrity="sha384-HuS9Oz9KEyjRH338lAKGE0+hsZL1wg/gXPfyHzbC27dcfnr04LmgdtUqn9PU91i0" crossorigin="anonymous">
    <?php endif; ?>

    <!-- Tailwind 与主题样式均为离线构建/静态资源，可被浏览器缓存 -->
    <link rel="stylesheet" href="<?php $this->options->themeUrl('assets/css/tailwind.min.css'); ?>?v=<?php echo BOLD_VERSION; ?>">
    <link rel="stylesheet" href="<?php $this->options->themeUrl('assets/css/bold.css'); ?>?v=<?php echo BOLD_VERSION; ?>">

    <?php if (bold_turnstile_enabled() && $_boldIsArticle): ?>
    <!-- 显式渲染可在 Typecho 移动回复表单后可靠地重建验证组件 -->
    <script id="bold-turnstile-api" src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
    <?php endif; ?>

    <!-- 自定义头部代码 (如验证Meta、自定义CSS) -->
    <?php $this->options->customHead(); ?>

    <?php $this->header('description=&keywords=&generator=&template=&rss2=&rss1=&atom='); ?>
</head>
<body class="flex flex-col min-h-screen border-t-4 border-x-0 md:border-x-4 border-black max-w-7xl mx-auto bg-white shadow-none md:shadow-[8px_8px_0px_0px_#db2777] my-0 md:my-8 transition-all">

<a href="#main-content" class="skip-link"><?php echo get_theme_text('skip_content', $this); ?></a>

<header class="site-header border-b-4 border-black p-6 md:p-10 bg-white sticky top-0 z-50 transition-colors duration-300">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="flex justify-between w-full md:w-auto items-center">

            <a href="<?php $this->options->siteUrl(); ?>" class="group block relative" id="site-logo">
                <h1 class="mouse-gradient-text text-4xl sm:text-5xl md:text-7xl font-black tracking-tighter uppercase leading-none transition-all">
                    <?php if($this->options->logoText): ?>
                        <?php $this->options->logoText(); ?>
                    <?php else: ?>
                        <?php $this->options->title() ?><span class="text-pink-600 transition-colors dark:text-[#10b981]">.</span>
                    <?php endif; ?>
                </h1>
                <p class="text-xs sm:text-sm font-bold mt-2 uppercase tracking-widest border-l-4 border-black pl-3 ml-1 group-hover:border-pink-500 transition-colors dark:group-hover:border-[#10b981]">
                    <?php $this->options->description() ?>
                </p>
            </a>

            <button type="button" id="nav-toggle" class="nav-toggle md:hidden" aria-expanded="false" aria-controls="primary-navigation">
                <span class="sr-only"><?php echo get_theme_text('menu', $this); ?></span>
                <svg class="nav-toggle-open" width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="3" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                <svg class="nav-toggle-close" width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="3" d="M6 18 18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <nav id="primary-navigation" class="primary-navigation w-full md:w-auto pb-1 md:pb-0" aria-label="<?php echo get_theme_text('main_navigation', $this); ?>">
            <ul class="flex md:flex-wrap gap-6 text-lg font-bold items-center">
                <li><a href="<?php $this->options->siteUrl(); ?>" class="hover-underline-animation <?php if($this->is('index')): ?>text-pink-600 dark:text-[#10b981]<?php endif; ?>"<?php if($this->is('index')): ?> aria-current="page"<?php endif; ?>><?php echo get_theme_text('home', $this); ?></a></li>
                <?php $this->widget('Widget_Contents_Page_List')->to($pages); ?>
                <?php while($pages->next()): ?>
                <li><a href="<?php $pages->permalink(); ?>" class="hover-underline-animation <?php if($this->is('page', $pages->slug)): ?>text-pink-600 dark:text-[#10b981]<?php endif; ?>"<?php if($this->is('page', $pages->slug)): ?> aria-current="page"<?php endif; ?>><?php $pages->title(); ?></a></li>
                <?php endwhile; ?>
            </ul>
        </nav>
    </div>
</header>

<main id="main-content" class="flex-grow flex flex-col md:flex-row" tabindex="-1">
