<?php
/**
 * BOLD - 一款简洁大胆的新粗野主义 (Neo-Brutalism) 风格主题
 *
 * @package BOLD Theme
 * @author lbyxiaolizi
 * @version 1.4
 * @link https://blog.vh.gs
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;
// 摘要可随文章解锁 Cookie 改变，必须在 header.php 输出前设置缓存策略。
if (bold_listings_may_vary_by_unlock_cookie()) bold_private_cache_headers();
$this->need('header.php');

// 定义文章卡片悬停颜色池
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

    <div>
        <?php if ($this->have()): ?>
        <?php while($this->next()): ?>
        <?php
            // 加密分类文章已在查询层（BoldHooks::filterProtectedFromIndex）排除，
            // 此处 continue 仅作兜底，正常情况下不会触发
            if (shouldHideFromHome($this)) {
                continue;
            }

            // 随机卡片悬停颜色
            $randomHover = $hoverColors[array_rand($hoverColors)];
        ?>
        <article class="p-6 md:p-10 border-b-4 border-black transition-colors group relative overflow-hidden <?php echo $randomHover; ?> dark:border-[#10b981] dark:hover:bg-[#2d2d2d]">
            <span class="absolute -right-2 -bottom-4 md:-right-4 md:-bottom-10 text-[5rem] md:text-[10rem] font-black text-gray-100 opacity-50 z-0 pointer-events-none group-hover:text-white/50 transition-colors leading-none dark:text-[#1e1e1e] dark:group-hover:text-[#10b981]/20" aria-hidden="true">
                <?php $this->sequence(); ?>
            </span>

            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-3 md:mb-4 text-xs font-bold uppercase tracking-wider flex-wrap">
                    <?php printColoredCategories($this); ?>

                    <time class="bg-white px-2 py-1 border-2 border-black dark:bg-[#121212] dark:text-[#e5e5e5] dark:border-[#10b981]" datetime="<?php echo bold_iso8601($this->created); ?>"><?php $this->date(); ?></time>
                </div>

                <h2 class="text-2xl sm:text-3xl md:text-4xl font-black mb-3 md:mb-4 leading-tight group-hover:text-blue-900 transition-colors dark:text-[#e5e5e5] dark:group-hover:text-[#10b981]">
                    <a href="<?php $this->permalink() ?>"><?php $this->title() ?></a>
                </h2>

                <div class="text-base md:text-lg font-medium text-gray-700 mb-4 md:mb-6 line-clamp-3 group-hover:text-black dark:text-[#a3a3a3] dark:group-hover:text-white">
                    <?php printExcerpt($this, 140); ?>
                </div>

                <a href="<?php $this->permalink() ?>" class="inline-flex items-center font-black text-base md:text-lg border-b-2 border-black hover:bg-blue-600 hover:text-white transition-all px-1 dark:text-[#10b981] dark:border-[#10b981] dark:hover:bg-[#10b981] dark:hover:text-black">
                    <?php echo get_theme_text('read_more', $this); ?>
                    <svg class="w-4 h-4 md:w-5 md:h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </article>
        <?php endwhile; ?>
        <?php else: ?>
            <div class="p-10 text-xl font-bold dark:text-white"><?php echo get_theme_text('no_content', $this); ?></div>
        <?php endif; ?>
    </div>

    <?php if ($this->getTotal() > 0): ?>
    <div class="mt-auto p-6 md:p-10 border-t-4 border-black bg-black text-white flex justify-between items-center font-bold dark:bg-[#10b981] dark:text-black dark:border-[#10b981]">
        <?php $this->pageLink(get_theme_text('prev_page', $this), 'prev'); ?>
        <span class="text-xs md:text-sm tracking-widest border border-white px-2 md:px-3 py-1 rounded-full dark:border-black"><?php echo get_theme_text('page', $this); ?> <?php echo max(1, intval($this->_currentPage)); ?> / <?php echo max(1, (int) ceil($this->getTotal() / $this->parameter->pageSize)); ?></span>
        <?php $this->pageLink(get_theme_text('next_page', $this), 'next'); ?>
    </div>
    <?php endif; ?>
</div>

<?php $this->need('sidebar.php'); ?>
<?php $this->need('footer.php'); ?>
