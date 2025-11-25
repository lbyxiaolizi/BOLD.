<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php $this->archiveTitle(array(
            'category'  =>  _t('分类 %s 下的文章'),
            'search'    =>  _t('包含关键字 %s 的文章'),
            'tag'       =>  _t('标签 %s 下的文章'),
            'author'    =>  _t('%s 发布的文章')
        ), '', ' - '); ?><?php $this->options->title(); ?></title>
    
    <meta name="description" content="<?php echo get_seo_description($this); ?>" />
    <meta name="keywords" content="<?php $this->keywords(','); ?>" />
    
    <!-- Open Graph / Twitter Card (SEO) -->
    <meta property="og:site_name" content="<?php $this->options->title(); ?>" />
    <meta property="og:type" content="<?php echo $this->is('post') ? 'article' : 'website'; ?>" />
    <meta property="og:url" content="<?php $this->permalink(); ?>" />
    <meta property="og:title" content="<?php $this->archiveTitle('', '', ' - '); ?><?php $this->options->title(); ?>" />
    <meta property="og:description" content="<?php echo get_seo_description($this); ?>" />
    <meta property="og:image" content="<?php echo get_og_image($this); ?>" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php $this->archiveTitle('', '', ' - '); ?><?php $this->options->title(); ?>" />
    <meta name="twitter:description" content="<?php echo get_seo_description($this); ?>" />
    <meta name="twitter:image" content="<?php echo get_og_image($this); ?>" />

    <!-- 资源引用 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;700;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-okaidia.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tocbot/4.18.2/tocbot.css">
    
    <!-- Cloudflare Turnstile JS (新增) -->
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

    <style>
        /* 防闪烁 (Anti-FOUC) */
        body { opacity: 0; visibility: hidden; transition: opacity 0.3s ease-in-out; }
        body.loaded { opacity: 1; visibility: visible; }
        
        /* --- 核心变量定义 --- */
        :root {
            /* 日间模式 (Light) */
            --bg-page: #f8f8f8;
            --bg-card: #ffffff;
            --text-main: #1a1a1a;
            --text-muted: #4b5563;
            --border-color: #000000;
            --accent-color: #db2777; /* 粉色 */
            --accent-bg: #fce7f3;
            --btn-bg: #000000;
            --btn-text: #ffffff;
            --gradient-center: #db2777; 
            --gradient-edge: #000000;
            --dot-color: #e5e7eb;
        }

        /* 暗黑模式 (Dark) - 统一使用 .dark-mode 类 */
        .dark-mode {
            --bg-page: #121212;          /* 深灰黑背景 */
            --bg-card: #1e1e1e;          /* 卡片背景 */
            --text-main: #e5e5e5;        /* 主文字 */
            --text-muted: #a3a3a3;       /* 次要文字 */
            --border-color: #10b981;     /* 翡翠绿 */
            --accent-color: #10b981;     /* 强调色 */
            --accent-bg: #064e3b;        /* 强调背景 */
            --btn-bg: #10b981;           /* 按钮背景 */
            --btn-text: #000000;         /* 按钮文字 */
            --gradient-center: #10b981;
            --gradient-edge: #ffffff;
            --dot-color: #333333;
        }

        /* --- 全局样式 --- */
        body { 
            font-family: 'Noto Sans SC', sans-serif; 
            background-color: var(--bg-page); 
            color: var(--text-main); 
            background-image: radial-gradient(var(--dot-color) 1px, transparent 1px); 
            background-size: 20px 20px; 
            transition: background-color 0.3s, color 0.3s, background-image 0.3s;
        }

        /* --- 强制 Tailwind 类适配 (Universal Override) --- */
        
        /* 背景适配 */
        .dark-mode .bg-white { background-color: var(--bg-card) !important; color: var(--text-main) !important; }
        .dark-mode .bg-gray-50 { background-color: #18181b !important; } 
        .dark-mode .bg-yellow-50 { background-color: #262626 !important; } 
        .dark-mode .bg-purple-50 { background-color: #171717 !important; }
        .dark-mode .bg-purple-100 { background-color: var(--accent-color) !important; color: #000 !important; }
        
        /* 文本适配 */
        .dark-mode .text-black { color: var(--text-main) !important; }
        .dark-mode .text-gray-500, 
        .dark-mode .text-gray-600, 
        .dark-mode .text-gray-700, 
        .dark-mode .text-gray-800, 
        .dark-mode .text-gray-900 { color: var(--text-muted) !important; }
        
        /* 边框适配 */
        .dark-mode .border-black { border-color: var(--border-color) !important; }
        
        /* 按钮适配 */
        .dark-mode .bg-black { 
            background-color: var(--btn-bg) !important; 
            color: var(--btn-text) !important; 
        }
        .dark-mode .text-white { color: var(--btn-text) !important; }
        
        /* 阴影适配 */
        .dark-mode [class*="shadow-"] {
            box-shadow: 4px 4px 0px 0px var(--border-color) !important;
        }
        .dark-mode .shadow-\[8px_8px_0px_0px_\#000\] { box-shadow: 8px 8px 0px 0px var(--border-color) !important; }
        
        /* 交互悬停适配 */
        .dark-mode .hover\:bg-yellow-100:hover,
        .dark-mode .hover\:bg-yellow-200:hover { 
            background-color: #2d2d2d !important;
            /* border-color: var(--accent-color) !important; */
            color: var(--accent-color) !important; 
        }
        
        .dark-mode .hover\:text-black:hover { color: var(--text-main) !important; }
        .dark-mode .group:hover .text-pink-600 { color: var(--accent-color) !important; }

        /* 侧边栏评论特殊适配 */
        .dark-mode .group .bg-purple-100,
        .dark-mode .group .bg-gray-200 { 
            background-color: var(--accent-color) !important; 
            color: #000 !important; 
        }
        .dark-mode .group:hover .bg-purple-100,
        .dark-mode .group:hover .bg-gray-200 {
            background-color: #a855f7 !important;
            color: #fff !important;
        }

        /* 滚动条 */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: var(--bg-card); }
        ::-webkit-scrollbar-thumb { background: #000; border: 2px solid #fff; }
        .dark-mode ::-webkit-scrollbar-thumb { background: var(--border-color); border-color: #000; }
        .dark-mode ::-webkit-scrollbar-track { background: #000; border-left: 3px solid var(--border-color); }

        /* Prose 适配 */
        .dark-mode .prose { color: var(--text-main) !important; }
        .dark-mode .prose p, .dark-mode .prose ul, .dark-mode .prose ol { color: var(--text-main) !important; }
        .dark-mode .prose h1, .dark-mode .prose h2, .dark-mode .prose h3, 
        .dark-mode .prose strong, .dark-mode .prose b { color: #ffffff !important; }
        .dark-mode .prose a { color: var(--accent-color) !important; border-bottom-color: var(--accent-color) !important; }
        .dark-mode .prose blockquote { 
            background-color: #262626 !important; 
            border-left-color: var(--border-color) !important; 
            color: var(--text-main) !important; 
        }
        .dark-mode .prose pre { 
            border-color: var(--border-color) !important; 
            box-shadow: 6px 6px 0px 0px var(--border-color) !important; 
        }
        
        /* 输入框 */
        .dark-mode input, .dark-mode textarea { 
            background-color: #000 !important; 
            color: #fff !important; 
            border-color: var(--border-color) !important; 
        }

        /* 标题光标跟随特效 */
        .mouse-gradient-text { 
            color: var(--text-main); 
            transition: color 0.1s; 
            position: relative;
            z-index: 10;
        }
        .group:hover .mouse-gradient-text {
            color: transparent; 
            background-image: radial-gradient(
                circle 180px at var(--mouse-x, 50%) var(--mouse-y, 50%), 
                var(--gradient-center) 0%, 
                var(--gradient-edge) 120%
            );
            background-clip: text; 
            -webkit-background-clip: text; 
            background-repeat: no-repeat;
        }
        .group:hover .mouse-gradient-text span { color: transparent; }
        
        /* 链接动画 */
        .hover-underline-animation { display: inline-block; position: relative; } 
        .hover-underline-animation::after { content: ''; position: absolute; width: 100%; transform: scaleX(0); height: 4px; bottom: 0; left: 0; background-color: var(--accent-color); transform-origin: bottom right; transition: transform 0.25s ease-out; } 
        .hover-underline-animation:hover::after { transform: scaleX(1); transform-origin: bottom left; }
        
        /* TOC Active Link */
        .dark-mode .is-active-link { background: var(--accent-color); color: #000 !important; box-shadow: 4px 4px 0px 0px #fff; }

        /* --- TOC Sticky (目录悬浮) --- */
        #toc-wrapper {
            position: -webkit-sticky;
            position: sticky;
            top: 160px; /* 避开顶部导航栏的高度 */
            z-index: 30;
            max-height: calc(100vh - 180px);
            overflow-y: auto;
            background-color: var(--bg-card); 
            scrollbar-width: none;
        }
        #toc-wrapper::-webkit-scrollbar { display: none; }
    </style>
    
    <script>
        function applyTheme() {
            const isDark = localStorage.getItem('darkMode') === 'true';
            if (isDark) {
                document.documentElement.classList.add('dark-mode');
                if (document.body) document.body.classList.add('dark-mode');
            } else {
                document.documentElement.classList.remove('dark-mode');
                if (document.body) document.body.classList.remove('dark-mode');
            }
        }
        applyTheme();
        
        document.addEventListener('DOMContentLoaded', function() { 
            setTimeout(function() { document.body.classList.add('loaded'); }, 50); 
            applyTheme(); 
        });
        window.onload = function() { document.body.classList.add('loaded'); };
    </script>

    <?php $this->header(); ?>
</head>
<body class="flex flex-col min-h-screen border-x-0 md:border-x-4 border-black max-w-7xl mx-auto bg-white shadow-none md:shadow-[8px_8px_0px_0px_#db2777] my-0 md:my-8 transition-all">

<header class="border-b-4 border-black p-6 md:p-10 bg-white sticky top-0 z-50 transition-colors duration-300">
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

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const logoLink = document.getElementById('site-logo');
                    if (logoLink) {
                        const titleText = logoLink.querySelector('.mouse-gradient-text');
                        logoLink.addEventListener('mousemove', (e) => {
                            const rect = titleText.getBoundingClientRect();
                            const x = e.clientX - rect.left;
                            const y = e.clientY - rect.top;
                            titleText.style.setProperty('--mouse-x', `${x}px`);
                            titleText.style.setProperty('--mouse-y', `${y}px`);
                        });
                    }
                });
            </script>
        </div>
        
        <nav class="w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
            <ul class="flex md:flex-wrap gap-6 text-lg font-bold items-center whitespace-nowrap">
                <li><a href="<?php $this->options->siteUrl(); ?>" class="hover-underline-animation <?php if($this->is('index')): ?>text-pink-600 dark:text-[#10b981]<?php endif; ?>">首页</a></li>
                <?php $this->widget('Widget_Contents_Page_List')->to($pages); ?>
                <?php while($pages->next()): ?>
                <li><a href="<?php $pages->permalink(); ?>" class="hover-underline-animation <?php if($this->is('page', $pages->slug)): ?>text-pink-600 dark:text-[#10b981]<?php endif; ?>"><?php $pages->title(); ?></a></li>
                <?php endwhile; ?>
            </ul>
        </nav>
    </div>
</header>

<main class="flex-grow flex flex-col md:flex-row">