<?php
/**
 * Archive Timeline (时间轴归档)
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

// 时间轴条目可随分类解锁 Cookie 改变，必须在 header.php 输出前设置。
if (bold_listings_may_vary_by_unlock_cookie()) bold_private_cache_headers();

$passwordError = handlePasswordVerification($this);
$needsPassword = isPasswordProtected($this) && !isPasswordVerified($this);
?>
<?php $this->need('header.php'); ?>

<div class="w-full md:w-2/3 border-b-4 md:border-b-0 md:border-r-4 border-black bg-white flex flex-col dark:border-[#10b981] dark:bg-[#121212]">
    <article class="flex-grow">
        <!-- 页面头部 -->
        <header class="p-6 md:p-10 border-b-4 border-black bg-cyan-400 relative overflow-hidden dark:border-[#10b981] dark:bg-[#10b981]">
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white rounded-full blur-3xl opacity-30 pointer-events-none"></div>
            <h1 class="text-4xl md:text-6xl font-black uppercase relative z-10">
                <?php echo get_theme_text('timeline_title', $this); ?>
            </h1>
            <p class="mt-4 font-bold text-lg border-l-4 border-black pl-4 dark:border-black/50">
                <?php echo get_theme_text('timeline_desc', $this); ?>
            </p>
        </header>

        <div class="p-6 md:p-10">
            <?php if ($needsPassword): ?>
                <?php renderPasswordForm($this, $passwordError); ?>
            <?php else: ?>
            <?php
            // 时间轴数据：三次轻量查询取文章（不含正文）、分类树与分类映射，
            // 日期按站点时区分组；受保护分类的文章对未解锁访客隐藏
            $timelinePosts = bold_timeline_posts();

            $year = 0;
            $output = '<div class="relative border-l-4 border-black ml-4 md:ml-6 space-y-8 pb-10 dark:border-[#10b981]">';

            foreach ($timelinePosts as $timelinePost):
                $year_tmp = bold_site_date('Y', $timelinePost['created']);

                // 年份发生变化时，插入年份分隔
                if ($year != $year_tmp) {
                    $year = $year_tmp;

                    // 年份徽章
                    $output .= '<div class="relative pt-4">';
                    $output .= '<span class="absolute -left-[2.4rem] md:-left-[2.9rem] bg-black text-white font-black px-2 py-1 text-xl border-2 border-black shadow-[4px_4px_0px_0px_#db2777] z-10 dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#10b981]">' . $year . '</span>';
                    $output .= '</div>';
                    $output .= '<div class="h-4"></div>';
                }

                $permalink = htmlspecialchars($timelinePost['permalink'], ENT_QUOTES, 'UTF-8');
                $title = htmlspecialchars($timelinePost['title'], ENT_QUOTES, 'UTF-8', false);

                // 文章卡片容器
                $output .= '<div class="relative group pl-6 md:pl-10">';
                // 时间轴圆点 (装饰)
                $output .= '<div class="absolute -left-[0.65rem] top-6 w-4 h-4 bg-white border-4 border-black rounded-full group-hover:bg-yellow-400 transition-colors z-10 dark:border-[#10b981] dark:group-hover:bg-[#10b981]" aria-hidden="true"></div>';
                // 卡片主体
                $output .= '<article class="border-2 border-black bg-white p-4 shadow-[4px_4px_0px_0px_#000] hover:shadow-[6px_6px_0px_0px_#db2777] hover:-translate-y-1 transition-all duration-200 dark:bg-[#1e1e1e] dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#10b981] dark:hover:shadow-[6px_6px_0px_0px_#10b981]">';
                $output .= '<div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-1">';
                // 标题
                $output .= '<h3 class="text-lg font-black uppercase leading-tight"><a href="' . $permalink . '" class="hover:text-pink-600 transition-colors dark:text-[#e5e5e5] dark:hover:text-[#10b981]">' . $title . '</a></h3>';
                // 日期（time-tag 暗黑模式样式见 bold.css）
                $output .= '<time datetime="' . bold_iso8601($timelinePost['created']) . '" class="time-tag text-xs font-mono font-bold bg-gray-100 px-2 py-1 border border-black whitespace-nowrap self-start md:self-auto">' . bold_site_date('M d', $timelinePost['created']) . '</time>';
                $output .= '</div>';

                $output .= '</article></div>';

            endforeach;

            $output .= '</div>'; // 结束时间轴容器
            echo $output;
            ?>
            <?php endif; ?>
        </div>
    </article>
</div>

<?php $this->need('sidebar.php'); ?>
<?php $this->need('footer.php'); ?>
