<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<div class="w-full md:w-2/3 border-b-4 md:border-b-0 md:border-r-4 border-black bg-white flex flex-col justify-center items-center min-h-[60vh] p-6 text-center relative overflow-hidden">
    
    <!-- 背景大字装饰 (CSS 动画浮动效果) -->
    <span class="absolute text-[12rem] md:text-[20rem] font-black text-gray-100 select-none z-0 pointer-events-none opacity-50 animate-pulse">404</span>
    
    <div class="relative z-10 bg-white border-4 border-black p-8 md:p-12 shadow-[8px_8px_0px_0px_#db2777] max-w-2xl w-full">
        <h1 class="text-6xl md:text-8xl font-black mb-4 tracking-tighter">OOPS!</h1>
        <div class="h-2 bg-black w-24 mx-auto mb-6"></div>
        
        <h2 class="text-2xl font-bold uppercase mb-4">Page Not Found</h2>
        <p class="text-gray-600 mb-8 font-medium text-lg">
            你似乎来到了荒原。这个页面可能已被删除、移动，或者你输入了错误的坐标。
        </p>
        
        <div class="flex flex-col md:flex-row gap-4 justify-center">
            <a href="<?php $this->options->siteUrl(); ?>" class="inline-block bg-black text-white px-8 py-3 font-black text-lg uppercase tracking-widest hover:bg-yellow-400 hover:text-black transition-colors border-2 border-transparent hover:border-black shadow-none hover:shadow-[4px_4px_0px_0px_#000]">
                返回首页
            </a>
            <button onclick="history.back()" class="inline-block bg-white text-black border-2 border-black px-8 py-3 font-black text-lg uppercase tracking-widest hover:bg-cyan-400 transition-colors shadow-[4px_4px_0px_0px_#000] hover:shadow-none hover:translate-x-1 hover:translate-y-1 active:translate-x-1 active:translate-y-1">
                返回上一页
            </button>
        </div>
    </div>

</div>

<?php $this->need('sidebar.php'); ?>
<?php $this->need('footer.php'); ?>