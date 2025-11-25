<?php
/**
 * Archive Timeline (时间轴归档)
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<!-- 添加内联样式修复时间标签在暗黑模式下的显示问题 -->
<style>
    /* 强制覆盖时间标签在暗黑模式下的样式 */
    body.dark-mode .time-tag {
        background-color: #000000 !important; /* 纯黑背景 */
        color: #10b981 !important;           /* 翡翠绿文字 */
        border-color: #10b981 !important;    /* 翡翠绿边框 */
    }
</style>

<div class="w-full md:w-2/3 border-b-4 md:border-b-0 md:border-r-4 border-black bg-white flex flex-col dark:border-[#10b981] dark:bg-[#121212]">
    <article class="flex-grow">
        <!-- 页面头部 -->
        <header class="p-6 md:p-10 border-b-4 border-black bg-cyan-400 relative overflow-hidden dark:border-[#10b981] dark:bg-[#10b981]">
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white rounded-full blur-3xl opacity-30 pointer-events-none"></div>
            <h1 class="text-4xl md:text-6xl font-black uppercase relative z-10">
                TIMELINE <span class="text-white">ARCHIVE</span>.
            </h1>
            <p class="mt-4 font-bold text-lg border-l-4 border-black pl-4 dark:border-black/50">
                记录每一个闪光的瞬间。
            </p>
        </header>

        <div class="p-6 md:p-10">
            <?php 
            // 获取所有文章
            $this->widget('Widget_Contents_Post_Recent', 'pageSize=10000')->to($archives); 
            
            $year = 0; 
            // 时间轴主线容器 (适配暗黑模式边框颜色)
            $output = '<div class="relative border-l-4 border-black ml-4 md:ml-6 space-y-8 pb-10 dark:border-[#10b981]">'; 
            
            while($archives->next()):
                $year_tmp = date('Y', $archives->created);
                
                // 年份发生变化时，插入年份分隔
                if ($year != $year_tmp) {
                    $year = $year_tmp;
                    
                    // 年份徽章
                    $output .= '<div class="relative pt-4">';
                    // 暗黑模式：黑底白字，边框绿，阴影绿
                    $output .= '<span class="absolute -left-[2.4rem] md:-left-[2.9rem] bg-black text-white font-black px-2 py-1 text-xl border-2 border-black shadow-[4px_4px_0px_0px_#db2777] z-10 dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#10b981]">' . $year . '</span>';
                    $output .= '</div>';
                    
                    // 增加一点间距
                    $output .= '<div class="h-4"></div>';
                }
                
                // 文章卡片容器
                $output .= '<div class="relative group pl-6 md:pl-10">';
                
                // 时间轴圆点 (装饰)
                // 暗黑模式：边框黑 -> 绿
                $output .= '<div class="absolute -left-[0.65rem] top-6 w-4 h-4 bg-white border-4 border-black rounded-full group-hover:bg-yellow-400 transition-colors z-10 dark:border-[#10b981] dark:group-hover:bg-[#10b981]"></div>';
                
                // 卡片主体
                // 暗黑模式：背景深灰，边框绿，阴影绿，悬停阴影绿
                $output .= '<article class="border-2 border-black bg-white p-4 shadow-[4px_4px_0px_0px_#000] hover:shadow-[6px_6px_0px_0px_#db2777] hover:-translate-y-1 transition-all duration-200 dark:bg-[#1e1e1e] dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#10b981] dark:hover:shadow-[6px_6px_0px_0px_#10b981]">';
                $output .= '<div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-1">';
                
                // 标题 (链接悬停颜色适配)
                $output .= '<h3 class="text-lg font-black uppercase leading-tight"><a href="'.$archives->permalink .'" class="hover:text-pink-600 transition-colors dark:text-[#e5e5e5] dark:hover:text-[#10b981]">'. $archives->title .'</a></h3>';
                
                // 日期 
                // 修复点：添加 time-tag 类，利用上方 CSS 覆盖默认样式
                $output .= '<time class="time-tag text-xs font-mono font-bold bg-gray-100 px-2 py-1 border border-black whitespace-nowrap self-start md:self-auto">'.date('M d', $archives->created).'</time>';
                $output .= '</div>';
                
                $output .= '</article></div>';
                
            endwhile;
            
            $output .= '</div>'; // 结束时间轴容器
            echo $output;
            ?>
        </div>
    </article>
</div>

<?php $this->need('sidebar.php'); ?>
<?php $this->need('footer.php'); ?>