<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<div class="w-full md:w-2/3 border-b-4 md:border-b-0 md:border-r-4 border-black flex flex-col">
    
    <!-- 归档头部 Banner -->
    <div class="p-6 md:p-10 border-b-4 border-black bg-cyan-400 relative overflow-hidden group">
        <span class="inline-block bg-black text-white px-3 py-1 text-xs font-bold uppercase tracking-widest border-2 border-white mb-4">Archive</span>
        <h1 class="text-5xl md:text-7xl font-black mb-6 uppercase tracking-tight text-black">
            <?php $this->archiveTitle(array(
                'category'  =>  _t('%s'),
                'search'    =>  _t('搜索: %s'),
                'tag'       =>  _t('标签: %s'),
                'author'    =>  _t('作者: %s')
            ), '', ''); ?>
        </h1>
    </div>

    <!-- 文章列表容器 -->
    <div class="flex-grow">
        <?php if ($this->have()): ?>
        <?php while($this->next()): ?>
        <article class="p-6 md:p-10 border-b-4 border-black hover:bg-yellow-100 transition-colors group cursor-pointer relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-black mb-3 md:mb-4 leading-tight group-hover:text-blue-900 transition-colors">
                    <a href="<?php $this->permalink() ?>"><?php $this->title() ?></a>
                </h2>
                <div class="text-base md:text-lg font-medium text-gray-700 mb-4 md:mb-6 line-clamp-3 group-hover:text-black">
                    <!-- 修复点：使用自定义函数 printExcerpt -->
                    <?php printExcerpt($this, 120); ?>
                </div>
                <a href="<?php $this->permalink() ?>" class="inline-flex items-center font-black text-base md:text-lg border-b-2 border-black hover:bg-blue-600 hover:text-white transition-all px-1">
                    阅读全文 <svg class="w-4 h-4 md:w-5 md:h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </article>
        <?php endwhile; ?>
        <?php else: ?>
            <div class="p-10 text-xl font-bold">没有找到内容</div>
        <?php endif; ?>
    </div>

    <!-- 分页 -->
    <div class="mt-auto p-6 md:p-10 border-t-4 border-black bg-black text-white flex justify-between items-center font-bold">
        <?php $this->pageLink('← 上一页', 'prev'); ?>
        <?php $this->pageLink('下一页 →', 'next'); ?>
    </div>
</div>

<?php $this->need('sidebar.php'); ?>
<?php $this->need('footer.php'); ?>