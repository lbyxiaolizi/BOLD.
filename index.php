<?php
/**
 * BOLD - 一款简洁大胆的新粗野主义 (Neo-Brutalism) 风格主题
 * * @package BOLD Theme
 * @author YourName
 * @version 1.0
 * @link http://yourwebsite.com
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
?>

<div class="w-full md:w-2/3 border-b-4 md:border-b-0 md:border-r-4 border-black flex flex-col">
    
    <!-- 文章循环区域 -->
    <div>
        <?php while($this->next()): ?>
        <article class="p-6 md:p-10 border-b-4 border-black hover:bg-yellow-100 transition-colors group cursor-pointer relative overflow-hidden">
            <!-- 背景数字装饰 -->
            <span class="absolute -right-2 -bottom-4 md:-right-4 md:-bottom-10 text-[5rem] md:text-[10rem] font-black text-gray-100 opacity-50 z-0 pointer-events-none group-hover:text-yellow-300 transition-colors leading-none">
                <?php $this->sequence(); ?>
            </span>
            
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-3 md:mb-4 text-xs font-bold uppercase tracking-wider">
                    <span class="bg-blue-600 text-white px-3 py-1 border-2 border-black shadow-[2px_2px_0px_0px_#000]"><?php $this->category(','); ?></span>
                    <time class="bg-white px-2 py-1 border-2 border-black" datetime="<?php $this->date('c'); ?>"><?php $this->date(); ?></time>
                </div>

                <h2 class="text-2xl sm:text-3xl md:text-4xl font-black mb-3 md:mb-4 leading-tight group-hover:text-blue-900 transition-colors">
                    <a href="<?php $this->permalink() ?>"><?php $this->title() ?></a>
                </h2>

                <div class="text-base md:text-lg font-medium text-gray-700 mb-4 md:mb-6 line-clamp-3 group-hover:text-black">
                    <!-- 修复点：使用自定义函数 printExcerpt -->
                    <?php printExcerpt($this, 140); ?>
                </div>

                <a href="<?php $this->permalink() ?>" class="inline-flex items-center font-black text-base md:text-lg border-b-2 border-black hover:bg-blue-600 hover:text-white transition-all px-1">
                    阅读全文 
                    <svg class="w-4 h-4 md:w-5 md:h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </article>
        <?php endwhile; ?>
    </div>

    <!-- 分页 -->
    <div class="mt-auto p-6 md:p-10 border-t-4 border-black bg-black text-white flex justify-between items-center font-bold">
        <?php $this->pageLink('← 上一页', 'prev'); ?>
        <span class="text-xs md:text-sm tracking-widest border border-white px-2 md:px-3 py-1 rounded-full">PAGE <?php if($this->_currentPage>1) echo $this->_currentPage;  else echo 1;?> / <?php echo ceil($this->getTotal() / $this->parameter->pageSize); ?></span>
        <?php $this->pageLink('下一页 →', 'next'); ?>
    </div>
</div>

<?php $this->need('sidebar.php'); ?>
<?php $this->need('footer.php'); ?>