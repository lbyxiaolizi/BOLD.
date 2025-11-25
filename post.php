<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<div class="w-full md:w-2/3 border-b-4 md:border-b-0 md:border-r-4 border-black bg-white flex flex-col dark:border-[#10b981] dark:bg-[#121212]">
    <article class="flex-grow">
        <header class="p-6 md:p-10 border-b-4 border-black bg-yellow-50 relative overflow-hidden dark:border-[#10b981] dark:bg-[#262626]">
            <div class="absolute top-0 right-0 p-4 opacity-10 font-black text-9xl pointer-events-none dark:text-white/10">#</div>
            
            <div class="flex flex-wrap gap-2 mb-4 relative z-10">
                <span class="bg-blue-600 text-white px-3 py-1 border-2 border-black font-bold text-xs uppercase shadow-[2px_2px_0px_0px_#000] dark:border-[#10b981] dark:shadow-[2px_2px_0px_0px_#10b981]"><?php $this->category(','); ?></span>
                <span class="bg-white text-black px-3 py-1 border-2 border-black font-bold text-xs uppercase dark:bg-[#121212] dark:text-[#e5e5e5] dark:border-[#10b981]"><?php $this->date(); ?></span>
            </div>
            
            <h1 class="text-3xl md:text-5xl font-black leading-tight mb-6 relative z-10"><?php $this->title() ?></h1>
            
            <div class="flex flex-wrap items-center gap-4 md:gap-6 text-sm font-bold border-t-4 border-black pt-4 relative z-10 dark:border-[#10b981]">
                 <span class="uppercase flex items-center gap-1">
                     By <a href="<?php $this->author->permalink(); ?>" class="border-b-2 border-transparent hover:border-black dark:hover:border-[#10b981]"><?php $this->author(); ?></a>
                 </span>
                 <span class="uppercase text-gray-500 flex items-center gap-1 dark:text-[#a3a3a3]">
                     <?php getPostViews($this); ?> Views
                 </span>
                 <span class="uppercase text-pink-600 flex items-center gap-1 dark:text-[#10b981]">
                     <?php echo getReadingTime($this); ?> MIN READ
                 </span>
                 <a href="#comments" class="uppercase text-blue-600 hover:text-black flex items-center gap-1 dark:text-[#10b981] dark:hover:text-white">
                     <?php $this->commentsNum('0', '1', '%d'); ?> Comments
                 </a>
            </div>
        </header>

        <div class="p-6 md:p-10 prose prose-lg prose-slate max-w-none prose-headings:font-black prose-p:text-gray-800 prose-img:rounded-none prose-strong:font-black prose-strong:bg-pink-200 prose-strong:px-1 dark:prose-invert">
            <?php echo parseReplyContent($this->content, $this); ?>
        </div>

        <div class="px-6 md:px-10 pb-10 flex flex-wrap gap-2">
            <span class="font-black mr-2 text-lg">TAGS:</span>
            <?php $this->tags(' ', true, '无标签'); ?>
        </div>

        <div class="px-6 md:px-10 pb-10">
            <div class="border-4 border-black p-6 bg-white shadow-[4px_4px_0px_0px_#000] dark:bg-[#1e1e1e] dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#10b981]">
                <h3 class="font-black text-xl uppercase mb-4 flex items-center gap-2">
                    <div class="w-4 h-4 bg-cyan-400 border-2 border-black dark:border-[#10b981] dark:bg-[#10b981]"></div> <?php echo get_theme_text('related_posts', $this); ?>
                </h3>
                <ul class="space-y-3">
                    <?php getRelatedPosts($this); ?>
                </ul>
            </div>
        </div>
    </article>

    <?php $this->need('comments.php'); ?>
</div>

<?php $this->need('sidebar.php'); ?>
<?php $this->need('footer.php'); ?>