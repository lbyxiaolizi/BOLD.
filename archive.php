<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<?php
// 处理分类页密码验证
$passwordError = false;
$needsPassword = false;

if ($this->is('category')) {
    // 检查是否需要为分类归档页面验证密码
    if (requirePasswordForCategoryArchive()) {
        $passwordError = handlePasswordVerification($this);
        $needsPassword = isPasswordProtected($this) && !isPasswordVerified($this);
    }
}

$hoverColors = [
    'hover:bg-red-200', 'hover:bg-orange-200', 'hover:bg-amber-200',
    'hover:bg-yellow-200', 'hover:bg-lime-200', 'hover:bg-green-200',
    'hover:bg-emerald-200', 'hover:bg-teal-200', 'hover:bg-cyan-200',
    'hover:bg-sky-200', 'hover:bg-blue-200', 'hover:bg-indigo-200',
    'hover:bg-violet-200', 'hover:bg-purple-200', 'hover:bg-fuchsia-200',
    'hover:bg-pink-200', 'hover:bg-rose-200'
];
?>

<div class="w-full md:w-2/3 border-b-4 md:border-b-0 md:border-r-4 border-black flex flex-col dark:border-[#10b981]">
    
    <div class="p-6 md:p-10 border-b-4 border-black bg-cyan-400 relative overflow-hidden group dark:bg-[#10b981] dark:border-[#10b981]">
        <span class="inline-block bg-black text-white px-3 py-1 text-xs font-bold uppercase tracking-widest border-2 border-white mb-4 dark:bg-[#121212] dark:border-[#10b981]">Archive</span>
        <h1 class="text-5xl md:text-7xl font-black mb-6 uppercase tracking-tight text-black dark:text-black">
            <?php $this->archiveTitle(array(
                'category'  =>  _t('%s'),
                'search'    =>  _t('搜索: %s'),
                'tag'       =>  _t('标签: %s'),
                'author'    =>  _t('作者: %s')
            ), '', ''); ?>
        </h1>
    </div>

    <?php if ($needsPassword): ?>
    <!-- 分类密码保护 -->
    <div class="p-6 md:p-10">
        <?php renderPasswordForm($this, $passwordError); ?>
    </div>
    <?php else: ?>
    <div class="flex-grow">
        <?php if ($this->have()): ?>
        <?php while($this->next()): ?>
        <?php $randomHover = $hoverColors[array_rand($hoverColors)]; ?>
        <article class="p-6 md:p-10 border-b-4 border-black transition-colors group cursor-pointer relative overflow-hidden <?php echo $randomHover; ?> dark:border-[#10b981] dark:hover:bg-[#2d2d2d]">
            <div class="relative z-10">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-black mb-3 md:mb-4 leading-tight group-hover:text-blue-900 transition-colors dark:text-[#e5e5e5] dark:group-hover:text-[#10b981]">
                    <a href="<?php $this->permalink() ?>"><?php $this->title() ?></a>
                </h2>
                <div class="text-base md:text-lg font-medium text-gray-700 mb-4 md:mb-6 line-clamp-3 group-hover:text-black dark:text-[#a3a3a3] dark:group-hover:text-white">
                    <?php printExcerpt($this, 120); ?>
                </div>
                <a href="<?php $this->permalink() ?>" class="inline-flex items-center font-black text-base md:text-lg border-b-2 border-black hover:bg-blue-600 hover:text-white transition-all px-1 dark:text-[#10b981] dark:border-[#10b981] dark:hover:bg-[#10b981] dark:hover:text-black">
                    阅读全文 <svg class="w-4 h-4 md:w-5 md:h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </article>
        <?php endwhile; ?>
        <?php else: ?>
            <div class="p-10 text-xl font-bold dark:text-white">没有找到内容</div>
        <?php endif; ?>
    </div>

    <div class="mt-auto p-6 md:p-10 border-t-4 border-black bg-black text-white flex justify-between items-center font-bold dark:bg-[#10b981] dark:text-black dark:border-[#10b981]">
        <?php $this->pageLink('← 上一页', 'prev'); ?>
        <?php $this->pageLink('下一页 →', 'next'); ?>
    </div>
    <?php endif; ?>
</div>

<?php $this->need('sidebar.php'); ?>
<?php $this->need('footer.php'); ?>