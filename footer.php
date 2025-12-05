<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
</main>

<footer class="border-t-4 border-black bg-black text-white p-6 md:p-10 relative overflow-hidden transition-colors duration-300 dark:border-[#10b981] dark:bg-[#10b981] dark:text-black">
    <div class="absolute top-0 right-0 w-48 h-48 md:w-64 md:h-64 bg-pink-600 rounded-full blur-[80px] md:blur-[100px] opacity-20 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-48 h-48 md:w-64 md:h-64 bg-blue-600 rounded-full blur-[80px] md:blur-[100px] opacity-20 pointer-events-none"></div>
    <div class="flex flex-col md:flex-row justify-between items-center gap-6 relative z-10">
        <div class="text-center md:text-left">
            <h2 class="text-3xl md:text-4xl font-black mb-2 tracking-tighter"><a href="https://github.com/lbyxiaolizi/BOLD." href="_blank">BOLD.</a></h2>
            <p class="text-white text-sm font-mono font-bold dark:text-black">
                &copy; 2019-2025 <?php $this->options->title(); ?>. Powered by Typecho.
                <?php if ($this->options->icpNum): ?>
                    <span class="mx-2">|</span>
                    <a href="https://beian.miit.gov.cn/" target="_blank" rel="nofollow" class="hover:underline"><?php $this->options->icpNum(); ?></a>
                <?php endif; ?>
            </p>
        </div>
    </div>
</footer>

<!-- 阅读进度条 -->
<div id="reading-progress" class="fixed top-0 left-0 h-1.5 bg-pink-500 z-[100] transition-all duration-75 border-b border-black w-0 dark:bg-[#10b981] dark:border-[#10b981]"></div>

<!-- 按钮组容器（移动端 fixed，桌面按 v7 容器参数靠近内容右侧） -->
<style>
/* 仅在移动端生效的 overflow-x 修复，桌面端不受影响 */
@media (max-width: 767px) {
    html, body { max-width: 100vw; overflow-x: hidden; }
}

/* --- v7 参数（Tailwind max-w-7xl = 80rem = 1280px） --- */
:root {
    --site-max-width: 80rem; /* 1280px */
    /* 负值将按钮向左移动（越负越靠近内容/侧栏） */
    --fab-left-offset: -88px; /* 调整这个值以微调水平位置（例如 -64 / -72 / -96） */
}

/* FAB 基础 */
.fab-buttons { transform: translateZ(0); touch-action: manipulation; }
.fab-btn { transition: transform .18s ease, opacity .18s ease; box-sizing: border-box; border-radius: 0px !important; padding: 0 !important; }

/* 折叠隐藏/显示的动画 (移动端折叠逻辑复用) */
.fab-collapsed .fab-extra { opacity: 0; transform: translateY(8px) scale(.95); pointer-events: none; }
.fab-expanded .fab-extra { opacity: 1; transform: translateY(0) scale(1); pointer-events: auto; }

/* 默认移动端：fixed 到视口右下，且与安全区有间距 */
#fab-container {
    position: fixed;
    right: calc(env(safe-area-inset-right, 1rem) + 12px);
    bottom: calc(env(safe-area-inset-bottom, 1rem) + 12px);
    z-index: 50;
    display: flex;
    flex-direction: column;
    gap: .75rem;
}

/* 移动端按钮尺寸 */
@media (max-width: 767px) {
    .fab-btn { width: 40px !important; height: 40px !important; }
    .fab-btn svg { width: 18px !important; height: 18px !important; }
}

/* 桌面：把按钮相对于视口计算，让它靠近内容/侧栏右侧（v7 参数） */
@media (min-width: 768px) {
    /* 计算：单侧空白 = (100vw - siteMaxWidth) / 2
       右偏移 = 单侧空白 + var(--fab-left-offset)
       clamp 用于防止极端值 */
    #fab-container {
        position: fixed; /* 使用 fixed 可以在页面滚动时保持位置一致 */
        right: clamp(16px, calc((100vw - var(--site-max-width)) / 2 + var(--fab-left-offset)), 3rem) !important;
        bottom: 3.5rem; /* 根据视觉把按钮稍微往上提一点，按需调整 */
        z-index: 50;
    }

    .fab-btn { width: 48px !important; height: 48px !important; }
    .fab-btn svg { width: 20px !important; height: 20px !important; }
}
</style>

<div id="fab-container" class="fab-buttons" aria-hidden="false">
    <div id="fab-list" class="flex flex-col gap-3 items-end">
        <!-- RSS -->
        <a id="fab-rss" href="<?php $this->options->feedUrl(); ?>" target="_blank"
           class="fab-btn fab-extra bg-white text-black border-4 border-black shadow-[4px_4px_0px_0px_#000] flex items-center justify-center transition-all duration-300 hover:bg-orange-500 hover:text-white hover:-translate-y-1 transform dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#10b981] dark:bg-[#121212] dark:text-[#10b981] dark:hover:bg-[#10b981] dark:hover:text-black"
           title="RSS Feed" style="touch-action:manipulation;">
            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 5c7.18 0 13 5.82 13 13M6 11a7 7 0 017 7m-6 0a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
        </a>

        <!-- 主题切换 -->
        <button id="fab-theme" class="fab-btn fab-extra bg-white text-black border-4 border-black shadow-[4px_4px_0px_0px_#000] flex items-center justify-center transition-all duration-300 hover:bg-black hover:text-white hover:-translate-y-1 transform dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#10b981] dark:bg-[#121212] dark:text-[#10b981] dark:hover:bg-[#10b981] dark:hover:text-black" aria-label="切换主题" style="touch-action:manipulation;">
            <svg id="icon-sun" class="w-5 h-5 md:w-6 md:h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            <svg id="icon-moon" class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
        </button>

        <!-- 回到顶部 -->
        <button id="fab-back" class="fab-btn fab-extra bg-white text-black border-4 border-black shadow-[4px_4px_0px_0px_#000] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300 hover:bg-pink-500 hover:text-white hover:-translate-y-1 transform dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#10b981] dark:bg-[#121212] dark:text-[#10b981] dark:hover:bg-[#10b981] dark:hover:text-black" aria-label="回到顶部" style="touch-action:manipulation;">
            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
        </button>

        <!-- 移动端主折叠按钮 -->
        <button id="fab-toggle" class="fab-btn bg-white text-black w-12 h-12 md:hidden border-4 border-black shadow-[4px_4px_0px_0px_#000] flex items-center justify-center transition-all duration-200 hover:-translate-y-1 transform dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#10b981] dark:bg-[#121212] dark:text-[#10b981] dark:hover:bg-[#10b981] dark:hover:text-black" aria-expanded="false" aria-label="更多操作" style="touch-action:manipulation;">
            <svg id="fab-toggle-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 5v14M5 12h14"></path></svg>
        </button>
    </div>
</div>

<!-- 保留原有脚本（主题切换、TOC、灯箱、回到顶部等） -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/toolbar/prism-toolbar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/copy-to-clipboard/prism-copy-to-clipboard.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tocbot/4.18.2/tocbot.min.js"></script>
<script src="https://tokinx.github.io/ViewImage/view-image.min.js"></script>
<script>
MathJax = { tex: { inlineMath: [['$', '$'], ['\\(', '\\)']] }, svg: { fontCache: 'global' } };
</script>
<script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateIcons() {
        const isDark = document.documentElement.classList.contains('dark-mode');
        const iconSun = document.getElementById('icon-sun');
        const iconMoon = document.getElementById('icon-moon');
        if (!iconSun || !iconMoon) return;
        if (isDark) { iconSun.classList.remove('hidden'); iconMoon.classList.add('hidden'); }
        else { iconSun.classList.add('hidden'); iconMoon.classList.remove('hidden'); }
    }
    updateIcons();

    // 回到顶部 & 进度条（边界保护）
    var backToTopBtn = document.getElementById('fab-back');
    var progressBar = document.getElementById('reading-progress');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) { backToTopBtn.classList.remove('opacity-0', 'pointer-events-none'); } 
        else { backToTopBtn.classList.add('opacity-0', 'pointer-events-none'); }

        var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
        var scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        var percent = 0;
        if (scrollHeight > 0) {
            percent = Math.min(100, Math.max(0, (scrollTop / scrollHeight) * 100));
        }
        if (progressBar) { progressBar.style.width = percent + '%'; }
    });
    backToTopBtn && backToTopBtn.addEventListener('click', function() { window.scrollTo({ top: 0, behavior: 'smooth' }); });

    // TOC & 图片灯箱
    var content = document.querySelector('.prose');
    if (content) {
        var headers = content.querySelectorAll('h2, h3');
        if (headers.length > 0) {
            var tocWrapper = document.getElementById('toc-wrapper');
            if(tocWrapper) tocWrapper.classList.remove('hidden');
            headers.forEach(function(header, index) { if (!header.id) { header.id = 'section-' + index; } });
            tocbot.init({ tocSelector: '.toc-container', contentSelector: '.prose', headingSelector: 'h2, h3', hasInnerContainers: true, scrollSmooth: true, scrollSmoothDuration: 400, headingsOffset: 80, scrollSmoothOffset: -80 });
        }
    }
    if (typeof ViewImage !== 'undefined') { ViewImage.init('.prose img'); }

    // 外链安全
    var links = document.links;
    for (var i = 0; i < links.length; i++) {
        if (links[i].hostname != window.location.hostname && links[i].href.startsWith('http')) {
            links[i].target = '_blank'; links[i].rel = 'noopener noreferrer';
        }
    }

    // FAB 折叠逻辑（移动端）
    var fabContainer = document.getElementById('fab-container');
    var fabList = document.getElementById('fab-list');
    var fabToggle = document.getElementById('fab-toggle');
    var isExpanded = false;

    function initFabState() {
        if (window.matchMedia && window.matchMedia('(max-width: 767px)').matches) {
            fabContainer.classList.add('fab-collapsed');
            fabList.classList.add('fab-collapsed');
            isExpanded = false;
            if (fabToggle) fabToggle.setAttribute('aria-expanded', 'false');
        } else {
            fabContainer.classList.remove('fab-collapsed');
            fabList.classList.remove('fab-collapsed');
            isExpanded = true;
            if (fabToggle) fabToggle.setAttribute('aria-expanded', 'true');
        }
    }
    initFabState();

    var resizeTimer = null;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(initFabState, 120);
    });

    if (fabToggle) {
        fabToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            isExpanded = !isExpanded;
            if (isExpanded) {
                fabList.classList.remove('fab-collapsed');
                fabContainer.classList.add('fab-expanded');
                fabContainer.classList.remove('fab-collapsed');
                fabToggle.setAttribute('aria-expanded', 'true');
                document.getElementById('fab-toggle-icon').innerHTML = '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"3\" d=\"M6 18L18 6M6 6l12 12\"/>';
            } else {
                fabList.classList.add('fab-collapsed');
                fabContainer.classList.remove('fab-expanded');
                fabContainer.classList.add('fab-collapsed');
                fabToggle.setAttribute('aria-expanded', 'false');
                document.getElementById('fab-toggle-icon').innerHTML = '<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"3\" d=\"M12 5v14M5 12h14\"/>';
            }
        });
    }

    document.addEventListener('click', function(e){
        if (window.matchMedia && window.matchMedia('(max-width: 767px)').matches && isExpanded && fabToggle && !fabToggle.contains(e.target) && !fabList.contains(e.target)) {
            fabToggle.click();
        }
    });

    // 暗黑模式按钮事件
    var fabTheme = document.getElementById('fab-theme');
    if (fabTheme) {
        fabTheme.addEventListener('click', function() {
            const currentMode = localStorage.getItem('darkMode') === 'true';
            const newMode = !currentMode;
            localStorage.setItem('darkMode', newMode);
            if (typeof applyTheme === 'function') { applyTheme(); }
            updateIcons();
        });
    }
});
</script>

<!-- 自定义底部代码 (统计代码等) -->
<?php $this->options->customFooter(); ?>

<?php $this->footer(); ?>
</body>
</html>