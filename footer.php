<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $_boldIsArticle = $this->is('post') || $this->is('page'); ?>
</main>

<footer class="border-t-4 border-black bg-black text-white p-6 md:p-10 relative overflow-hidden transition-colors duration-300 dark:border-[#10b981] dark:bg-[#10b981] dark:text-black">
    <div class="absolute top-0 right-0 w-48 h-48 md:w-64 md:h-64 bg-pink-600 rounded-full blur-[80px] md:blur-[100px] opacity-20 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-48 h-48 md:w-64 md:h-64 bg-blue-600 rounded-full blur-[80px] md:blur-[100px] opacity-20 pointer-events-none"></div>
    <div class="flex flex-col md:flex-row justify-between items-center gap-6 relative z-10">
        <div class="text-center md:text-left">
            <h2 class="text-3xl md:text-4xl font-black mb-2 tracking-tighter"><a href="https://github.com/lbyxiaolizi/BOLD." target="_blank" rel="noopener">BOLD.</a></h2>
            <p class="text-white text-sm font-mono font-bold dark:text-black">
                &copy; 2019-<?php echo bold_site_date('Y', Helper::options()->time); ?> <?php $this->options->title(); ?>. Powered by Typecho.
                <?php if ($this->options->icpNum): ?>
                    <span class="mx-2">|</span>
                    <a href="https://beian.miit.gov.cn/" target="_blank" rel="nofollow noopener" class="hover:underline"><?php $this->options->icpNum(); ?></a>
                <?php endif; ?>
            </p>
        </div>
    </div>
</footer>

<!-- 阅读进度条（transform 驱动，样式见 bold.css） -->
<div id="reading-progress" class="fixed top-0 left-0 h-1.5 bg-pink-500 z-[100] border-b border-black dark:bg-[#10b981] dark:border-[#10b981]" aria-hidden="true"></div>

<!-- 按钮组容器（移动端 fixed，桌面按 v7 容器参数靠近内容右侧；样式见 bold.css） -->
<div id="fab-container" class="fab-buttons">
    <div id="fab-list" class="flex flex-col gap-3 items-end">
        <!-- RSS -->
        <a id="fab-rss" href="<?php $this->options->feedUrl(); ?>" target="_blank"
           class="fab-btn fab-extra bg-white text-black border-4 border-black shadow-[4px_4px_0px_0px_#000] flex items-center justify-center transition-all duration-300 hover:bg-orange-500 hover:text-white hover:-translate-y-1 transform dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#10b981] dark:bg-[#121212] dark:text-[#10b981] dark:hover:bg-[#10b981] dark:hover:text-black"
           title="RSS Feed" aria-label="RSS Feed">
            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 5c7.18 0 13 5.82 13 13M6 11a7 7 0 017 7m-6 0a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
        </a>

        <!-- 主题切换 -->
        <button id="fab-theme" class="fab-btn fab-extra bg-white text-black border-4 border-black shadow-[4px_4px_0px_0px_#000] flex items-center justify-center transition-all duration-300 hover:bg-black hover:text-white hover:-translate-y-1 transform dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#10b981] dark:bg-[#121212] dark:text-[#10b981] dark:hover:bg-[#10b981] dark:hover:text-black" aria-label="切换主题">
            <svg id="icon-sun" class="w-5 h-5 md:w-6 md:h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            <svg id="icon-moon" class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
        </button>

        <!-- 回到顶部 -->
        <button id="fab-back" class="fab-btn fab-extra bg-white text-black border-4 border-black shadow-[4px_4px_0px_0px_#000] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300 hover:bg-pink-500 hover:text-white hover:-translate-y-1 transform dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#10b981] dark:bg-[#121212] dark:text-[#10b981] dark:hover:bg-[#10b981] dark:hover:text-black" aria-label="回到顶部">
            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
        </button>

        <!-- 移动端主折叠按钮 -->
        <button id="fab-toggle" class="fab-btn bg-white text-black w-12 h-12 md:hidden border-4 border-black shadow-[4px_4px_0px_0px_#000] flex items-center justify-center transition-all duration-200 hover:-translate-y-1 transform dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#10b981] dark:bg-[#121212] dark:text-[#10b981] dark:hover:bg-[#10b981] dark:hover:text-black" aria-expanded="false" aria-label="更多操作">
            <svg id="fab-toggle-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 5v14M5 12h14"></path></svg>
        </button>
    </div>
</div>

<?php /* 原文启发式 + 渲染层探测（bold_content_filter 置 $GLOBALS 标记）双保险 */ ?>
<?php if (bold_page_has_code($this) || !empty($GLOBALS['bold_has_code'])): ?>
<!-- Prism 代码高亮：仅在正文含代码块时加载（defer 不阻塞渲染，autoloader 自动按语言拉取组件） -->
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/prism/<?php echo BOLD_PRISM_VERSION; ?>/prism.min.js" integrity="sha384-06z5D//U/xpvxZHuUz92xBvq3DqBBFi7Up53HRrbV7Jlv7Yvh/MZ7oenfUe9iCEt" crossorigin="anonymous"></script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/prism/<?php echo BOLD_PRISM_VERSION; ?>/plugins/autoloader/prism-autoloader.min.js" integrity="sha384-Uq05+JLko69eOiPr39ta9bh7kld5PKZoU+fF7g0EXTAriEollhZ+DrN8Q/Oi8J2Q" crossorigin="anonymous"></script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/prism/<?php echo BOLD_PRISM_VERSION; ?>/plugins/toolbar/prism-toolbar.min.js" integrity="sha384-jC1G68eGEXJpPwMDNqyIUQsQlcUCdCU+a7GGuoV4TUZvM1gLYTMJUDvqBnxtZLWA" crossorigin="anonymous"></script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/prism/<?php echo BOLD_PRISM_VERSION; ?>/plugins/copy-to-clipboard/prism-copy-to-clipboard.min.js" integrity="sha384-ZdEfx8sYX8i4IVXU1tUbqwOp4PBUCCmnpagpiHchnstXkEczkzPfUd9fvBrntM+F" crossorigin="anonymous"></script>
<?php endif; ?>

<?php if ($_boldIsArticle): ?>
<!-- 目录与图片灯箱：仅文章/独立页需要（ViewImage 已随主题自托管，消除第三方个人源的供应链风险） -->
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/tocbot/<?php echo BOLD_TOCBOT_VERSION; ?>/tocbot.min.js" integrity="sha384-c6t5W+XVwk37x28hNXW497daR4yjHg3RxlpTzkUQ7H91a70dC3TF4nWEe0fBiXqz" crossorigin="anonymous"></script>
<script defer src="<?php $this->options->themeUrl('assets/vendor/view-image.min.js'); ?>?v=<?php echo BOLD_VERSION; ?>"></script>
<?php endif; ?>

<?php if (bold_page_has_math($this)): ?>
<!-- MathJax：仅在正文疑似包含公式时加载 -->
<script>
MathJax = { tex: { inlineMath: [['$', '$'], ['\\(', '\\)']] }, svg: { fontCache: 'global' } };
</script>
<script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@<?php echo BOLD_MATHJAX_VERSION; ?>/es5/tex-chtml.js" integrity="sha384-AHAnt9ZhGeHIrydA1Kp1L7FN+2UosbF7RQg6C+9Is/a7kDpQ1684C2iH2VWil6r4" crossorigin="anonymous"></script>
<?php endif; ?>

<!-- 主题脚本（Mermaid 检测到 .mermaid 时才动态加载，全站不再无条件下载 3MB 库） -->
<script defer src="<?php $this->options->themeUrl('assets/js/bold.js'); ?>?v=<?php echo BOLD_VERSION; ?>"></script>

<!-- 自定义底部代码 (统计代码等) -->
<?php $this->options->customFooter(); ?>

<?php $this->footer(); ?>
</body>
</html>
