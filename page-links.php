<?php
/**
 * Links (友情链接)
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

$passwordError = handlePasswordVerification($this);
$needsPassword = isPasswordProtected($this) && !isPasswordVerified($this);
?>
<?php $this->need('header.php'); ?>

<div class="w-full md:w-2/3 border-b-4 md:border-b-0 md:border-r-4 border-black bg-white flex flex-col dark:border-[#10b981] dark:bg-[#121212]">
    <article class="flex-grow">
        <!-- 头部 Banner -->
        <header class="p-6 md:p-10 border-b-4 border-black bg-pink-400 relative overflow-hidden dark:border-[#10b981] dark:bg-[#10b981]">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white rounded-full blur-3xl opacity-30 pointer-events-none"></div>
            <h1 class="text-4xl md:text-6xl font-black uppercase relative z-10">
                <?php echo get_theme_text('links_title', $this); ?>
            </h1>
            <p class="mt-4 font-bold text-lg border-l-4 border-black pl-4 dark:border-black/50">
                <?php echo get_theme_text('links_desc', $this); ?>
            </p>
        </header>

        <div class="p-6 md:p-10 prose prose-lg prose-slate max-w-none prose-headings:font-black prose-p:text-gray-800 prose-img:rounded-none links-grid dark:prose-invert">
            <?php if ($needsPassword): ?>
                <?php renderPasswordForm($this, $passwordError); ?>
            <?php else: ?>
            <?php
                // 获取内容（与其他页面模板一致，先解析密码/隐藏标记）
                $content = $this->content;
                $content = parseInlinePasswordContent($content, $this);
                $content = parseReplyContent($content, $this);

                // 正则替换：将 Markdown 的列表项 - [名字](链接) : 描述 转化为卡片结构
                // 增强正则：支持空格、横杠、冒号分隔，并支持无描述的情况，增加负向预查防重复
                $pattern = '/<li>\s*<a href="(.*?)">(.*?)<\/a>(?!\s*<span)(?:\s*[:：-]?\s*|\s+)(.*?)<\/li>/s';
                $replacement = '<li><a href="$1" target="_blank" rel="noopener"><span class="link-title">$2</span><span class="link-desc">$3</span></a></li>';
                // 先匹配有描述的
                $content = preg_replace($pattern, $replacement, $content);

                // 再匹配无描述的 (排除已经被替换过的 target="_blank")
                $pattern2 = '/<li>\s*<a href="(.*?)">(.*?)<\/a>(?!.*target="_blank")\s*<\/li>/s';
                $replacement2 = '<li><a href="$1" target="_blank" rel="noopener"><span class="link-title">$2</span><span class="link-desc"></span></a></li>';
                $content = preg_replace($pattern2, $replacement2, $content);

                echo parseMermaidContent($content);
            ?>
            <?php endif; ?>
        </div>
    </article>
</div>

<?php $this->need('sidebar.php'); ?>
<?php $this->need('footer.php'); ?>
