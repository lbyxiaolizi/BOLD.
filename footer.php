<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
</main>

<footer class="border-t-4 border-black bg-black text-white p-6 md:p-10 relative overflow-hidden transition-colors duration-300 dark:border-[#10b981] dark:bg-[#10b981] dark:text-black">
    <div class="absolute top-0 right-0 w-48 h-48 md:w-64 md:h-64 bg-pink-600 rounded-full blur-[80px] md:blur-[100px] opacity-20 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-48 h-48 md:w-64 md:h-64 bg-blue-600 rounded-full blur-[80px] md:blur-[100px] opacity-20 pointer-events-none"></div>
    <div class="flex flex-col md:flex-row justify-between items-center gap-6 relative z-10">
        <div class="text-center md:text-left">
            <h2 class="text-3xl md:text-4xl font-black mb-2 tracking-tighter">BOLD.</h2>
            <p class="text-white text-sm font-mono font-bold dark:text-black">
                &copy; <?php echo date('Y'); ?> <?php $this->options->title(); ?>. Powered by Typecho.
                
                <!-- ICP 备案号 (新增) -->
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

<!-- 按钮组容器 -->
<div class="fixed bottom-8 right-8 z-50 flex flex-col gap-3">
    
    <!-- 暗黑模式切换按钮 -->
    <button id="theme-toggle" class="bg-white text-black w-12 h-12 border-4 border-black shadow-[4px_4px_0px_0px_#000] flex items-center justify-center transition-all duration-300 hover:bg-black hover:text-white hover:-translate-y-1 transform dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#10b981] dark:bg-[#121212] dark:text-[#10b981] dark:hover:bg-[#10b981] dark:hover:text-black">
        <!-- 太阳图标 (Light Mode) -->
        <svg id="icon-sun" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        <!-- 月亮图标 (Dark Mode) -->
        <svg id="icon-moon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
    </button>

    <!-- 回到顶部按钮 -->
    <button id="back-to-top" class="bg-white text-black w-12 h-12 border-4 border-black shadow-[4px_4px_0px_0px_#000] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300 hover:bg-pink-500 hover:text-white hover:-translate-y-1 transform dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#10b981] dark:bg-[#121212] dark:text-[#10b981] dark:hover:bg-[#10b981] dark:hover:text-black">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
    </button>
</div>

<!-- Scripts -->
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

<!-- 初始化脚本 -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. 暗黑模式切换逻辑 ---
        const themeToggleBtn = document.getElementById('theme-toggle');
        const iconSun = document.getElementById('icon-sun');
        const iconMoon = document.getElementById('icon-moon');

        function updateIcons() {
            const isDark = document.documentElement.classList.contains('dark-mode');
            if (isDark) {
                iconSun.classList.remove('hidden');
                iconMoon.classList.add('hidden');
            } else {
                iconSun.classList.add('hidden');
                iconMoon.classList.remove('hidden');
            }
        }

        updateIcons();

        themeToggleBtn.addEventListener('click', () => {
            const currentMode = localStorage.getItem('darkMode') === 'true';
            const newMode = !currentMode;
            localStorage.setItem('darkMode', newMode);
            
            if (typeof applyTheme === 'function') {
                applyTheme();
            }
            updateIcons();
        });

        // --- 2. 目录 (TOC) ---
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

        // --- 3. 图片灯箱 ---
        if (typeof ViewImage !== 'undefined') { ViewImage.init('.prose img'); }

        // --- 4. 回到顶部 & 进度条 ---
        var backToTopBtn = document.getElementById('back-to-top');
        var progressBar = document.getElementById('reading-progress');
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) { backToTopBtn.classList.remove('opacity-0', 'pointer-events-none'); } 
            else { backToTopBtn.classList.add('opacity-0', 'pointer-events-none'); }
            
            var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
            var scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            if(progressBar) { progressBar.style.width = (scrollTop / scrollHeight) * 100 + '%'; }
        });

        backToTopBtn.addEventListener('click', function() { window.scrollTo({ top: 0, behavior: 'smooth' }); });

        // --- 5. 外链优化 ---
        var links = document.links;
        for (var i = 0; i < links.length; i++) {
            if (links[i].hostname != window.location.hostname && links[i].href.startsWith('http')) {
                links[i].target = '_blank'; links[i].rel = 'noopener noreferrer';
            }
        }
    });
</script>

<!-- 自定义底部代码 (统计代码等) -->
<?php $this->options->customFooter(); ?>

<?php $this->footer(); ?>
</body>
</html>