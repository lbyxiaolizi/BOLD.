<?php
/**
 * Links (友情链接)
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<style>
    /* 友链页面专用的样式覆盖 */
    .links-grid ul {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1.5rem;
        padding: 0;
        list-style: none !important;
    }
    @media (min-width: 768px) {
        .links-grid ul { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    
    /* 隐藏默认的列表圆点 */
    .links-grid li::before { content: none !important; }
    
    /* 默认日间模式样式 */
    .links-grid li {
        margin: 0 !important;
        padding: 0 !important;
        border: 4px solid #000;
        background: #fff;
        box-shadow: 6px 6px 0px 0px #000;
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative; /* 确保 z-index 生效 */
    }
    
    .links-grid li:hover {
        transform: translate(-4px, -4px);
        box-shadow: 10px 10px 0px 0px #db2777;
    }

    /* 暗黑模式适配 */
    body.dark-mode .links-grid li {
        border-color: var(--border-color); /* 使用全局变量 #10b981 */
        background: #1e1e1e; /* 深色卡片背景 */
        box-shadow: 6px 6px 0px 0px var(--border-color);
    }
    
    body.dark-mode .links-grid li:hover {
        /* 悬停时阴影变大，保持绿色 */
        box-shadow: 10px 10px 0px 0px #fff; 
        /* 或者使用绿色阴影加强版，这里用白色作为强对比 */
    }

    .links-grid a {
        display: block;
        padding: 1.5rem;
        text-decoration: none !important;
        border: none !important;
    }
    
    .link-title {
        display: block;
        font-weight: 900;
        font-size: 1.25rem;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
        color: #000;
        transition: color 0.2s;
    }
    /* 暗黑模式标题颜色 */
    body.dark-mode .link-title { color: #fff; }
    body.dark-mode .links-grid li:hover .link-title { color: var(--accent-color); }
    
    .link-desc {
        display: block;
        font-size: 0.875rem;
        color: #666;
        font-weight: bold;
    }
    /* 暗黑模式描述颜色 */
    body.dark-mode .link-desc { color: #a3a3a3; }
</style>

<div class="w-full md:w-2/3 border-b-4 md:border-b-0 md:border-r-4 border-black bg-white flex flex-col dark:border-[#10b981] dark:bg-[#121212]">
    <article class="flex-grow">
        <!-- 头部 Banner: 粉色 -> 翡翠绿 -->
        <header class="p-6 md:p-10 border-b-4 border-black bg-pink-400 relative overflow-hidden dark:border-[#10b981] dark:bg-[#10b981]">
             <!-- 装饰 -->
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white rounded-full blur-3xl opacity-30 pointer-events-none"></div>
            <h1 class="text-4xl md:text-6xl font-black uppercase relative z-10">
                FRIENDS <span class="text-white">LINKS</span>.
            </h1>
            <p class="mt-4 font-bold text-lg border-l-4 border-black pl-4 dark:border-black/50">
                连接未知的孤岛。
            </p>
        </header>

        <div class="p-6 md:p-10 prose prose-lg prose-slate max-w-none prose-headings:font-black prose-p:text-gray-800 prose-img:rounded-none links-grid dark:prose-invert">
            <?php 
                // 获取内容
                $content = $this->content;
                // 正则替换：将 Markdown 的列表项 - [名字](链接) : 描述 转化为卡片结构
                // 格式 1: - [Name](Link) : Desc
                $pattern = '/<li>\s*<a href="(.*?)">(.*?)<\/a>\s*[:：]\s*(.*?)<\/li>/s';
                $replacement = '<li><a href="$1" target="_blank"><span class="link-title">$2</span><span class="link-desc">$3</span></a></li>';
                $content = preg_replace($pattern, $replacement, $content);
                
                // 格式 2: - [Name](Link) (无描述)
                $pattern2 = '/<li>\s*<a href="(.*?)">(.*?)<\/a>\s*<\/li>/s';
                $replacement2 = '<li><a href="$1" target="_blank"><span class="link-title">$2</span><span class="link-desc"></span></a></li>';
                $content = preg_replace($pattern2, $replacement2, $content);
                
                echo $content;
            ?>
        </div>
    </article>
</div>

<?php $this->need('sidebar.php'); ?>
<?php $this->need('footer.php'); ?>