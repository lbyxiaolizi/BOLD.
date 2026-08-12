<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
// 在输出 header 前完成 Cookie 与重定向处理。
$passwordError = handlePasswordVerification($this);
$needsPassword = isPasswordProtected($this) && !isPasswordVerified($this);
$hasReward = !empty($this->options->wechatQrUrl) || !empty($this->options->alipayQrUrl);
?>
<?php $this->need('header.php'); ?>

<div class="w-full md:w-2/3 border-b-4 md:border-b-0 md:border-r-4 border-black bg-white flex flex-col dark:border-[#10b981] dark:bg-[#121212]">
    <article class="flex-grow">
        <header class="p-6 md:p-10 border-b-4 border-black bg-yellow-50 relative overflow-hidden dark:border-[#10b981] dark:bg-[#262626]">
            <div class="absolute top-0 right-0 p-4 opacity-10 font-black text-9xl pointer-events-none dark:text-white/10" aria-hidden="true">#</div>

            <div class="flex flex-wrap gap-2 mb-4 relative z-10 items-center">
                <?php printColoredCategories($this); ?>

                <time datetime="<?php echo bold_iso8601($this->created); ?>" class="bg-white text-black px-3 py-1 border-2 border-black font-bold text-xs uppercase dark:bg-[#121212] dark:text-[#e5e5e5] dark:border-[#10b981]"><?php $this->date(); ?></time>
            </div>

            <h1 class="text-3xl md:text-5xl font-black leading-tight mb-6 relative z-10 dark:text-white"><?php $this->title() ?></h1>

            <div class="flex flex-wrap items-center gap-4 md:gap-6 text-sm font-bold border-t-4 border-black pt-4 relative z-10 dark:border-[#10b981]">
                 <span class="uppercase flex items-center gap-1 dark:text-[#e5e5e5]">
                     By <a href="<?php $this->author->permalink(); ?>" class="border-b-2 border-transparent hover:border-black dark:hover:border-[#10b981]"><?php $this->author(); ?></a>
                 </span>
                 <?php if (!$needsPassword): ?>
                 <span class="uppercase text-gray-500 flex items-center gap-1 dark:text-[#a3a3a3]">
                     <?php getPostViews($this); ?> Views
                 </span>
                 <?php endif; ?>
                 <span class="uppercase text-pink-600 flex items-center gap-1 dark:text-[#10b981]">
                     <?php echo getReadingTime($this); ?> MIN READ
                 </span>
                 <a href="#comments" class="uppercase text-blue-600 hover:text-black flex items-center gap-1 dark:text-[#10b981] dark:hover:text-white">
                     <?php $this->commentsNum('0', '1', '%d'); ?> Comments
                 </a>
            </div>
        </header>

        <?php if ($needsPassword): ?>
        <!-- 密码保护内容 -->
        <div class="p-6 md:p-10">
            <?php renderPasswordForm($this, $passwordError); ?>
        </div>
        <?php else: ?>
        <!-- 正常内容 -->
        <div id="mobile-toc-slot" class="mobile-toc-slot"></div>
        <div class="p-6 md:p-10 prose prose-lg prose-slate max-w-none prose-headings:font-black prose-p:text-gray-800 prose-img:rounded-none prose-strong:font-black prose-strong:bg-pink-200 prose-strong:px-1 dark:prose-invert">
            <?php
                $content = $this->content;
                $content = parseInlinePasswordContent($content, $this);
                $content = parseReplyContent($content, $this);
                $content = parseMermaidContent($content);
                echo $content;
            ?>
        </div>

        <div class="px-6 md:px-10 pb-6">
            <div class="bg-gray-50 border-2 border-black p-6 dark:bg-[#1e1e1e] dark:border-[#10b981]">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="flex flex-col gap-2 text-center md:text-left w-full md:w-auto">
                        <div class="text-xs font-bold text-gray-600 dark:text-gray-400">
                            <p>本文由 <span class="text-black dark:text-white"><?php $this->author(); ?></span> 原创</p>
                            <p>采用 <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/" target="_blank" rel="license noopener" class="underline hover:text-pink-600 dark:hover:text-[#10b981]">CC BY-NC-SA 4.0</a> 协议进行许可</p>
                            <p>转载请注明出处：<a href="<?php $this->permalink(); ?>" class="underline hover:text-pink-600 dark:hover:text-[#10b981]"><?php $this->permalink(); ?></a></p>
                        </div>
                    </div>

                    <div class="article-actions flex flex-wrap justify-center md:justify-end gap-3 flex-shrink-0">
                    <button type="button" id="copy-article-link" data-copy-url="<?php $this->permalink(); ?>" data-copy-success="<?php echo htmlspecialchars(get_theme_text('link_copied', $this), ENT_QUOTES, 'UTF-8'); ?>" data-copy-failure="<?php echo htmlspecialchars(get_theme_text('copy_failed', $this), ENT_QUOTES, 'UTF-8'); ?>" class="bg-white text-black px-5 py-2 font-black uppercase border-2 border-black shadow-[4px_4px_0px_0px_#000] hover:translate-y-1 hover:shadow-none transition-all dark:bg-[#121212] dark:text-[#10b981] dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#10b981] dark:hover:shadow-none">
                        <?php echo get_theme_text('copy_link', $this); ?>
                    </button>
                    <?php if ($hasReward): ?>
                    <button type="button" id="reward-open" aria-haspopup="dialog" aria-controls="reward-modal" class="bg-pink-500 text-white px-6 py-2 font-black uppercase border-2 border-black shadow-[4px_4px_0px_0px_#000] hover:translate-y-1 hover:shadow-none transition-all dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#10b981] dark:hover:shadow-none">
                        <?php echo get_theme_text('reward', $this); ?>
                    </button>
                    <?php endif; ?>
                    </div>
                    <span id="copy-link-status" class="sr-only" role="status" aria-live="polite"></span>
                </div>
            </div>
        </div>

        <?php if ($hasReward): ?>
        <div id="reward-modal" class="reward-modal fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" role="dialog" aria-modal="true" aria-labelledby="reward-title" aria-hidden="true">
            <div class="reward-dialog bg-white border-4 border-black p-8 max-w-sm w-full text-center relative shadow-[8px_8px_0px_0px_#db2777] dark:bg-[#121212] dark:border-[#10b981] dark:shadow-[8px_8px_0px_0px_#10b981]">
                <h3 id="reward-title" class="text-2xl font-black uppercase mb-6 dark:text-white"><?php echo get_theme_text('reward_thanks', $this); ?></h3>
                <div class="reward-options grid gap-4 mb-6">
                    <?php if ($this->options->wechatQrUrl): ?>
                    <div class="text-center">
                        <img src="<?php $this->options->wechatQrUrl(); ?>" alt="WeChat QR" loading="lazy" decoding="async" class="w-full aspect-square object-contain bg-gray-200 mb-2 dark:bg-[#1e1e1e]">
                        <span class="font-bold text-sm dark:text-gray-300">WeChat</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($this->options->alipayQrUrl): ?>
                    <div class="text-center">
                        <img src="<?php $this->options->alipayQrUrl(); ?>" alt="Alipay QR" loading="lazy" decoding="async" class="w-full aspect-square object-contain bg-gray-200 mb-2 dark:bg-[#1e1e1e]">
                        <span class="font-bold text-sm dark:text-gray-300">Alipay</span>
                    </div>
                    <?php endif; ?>
                </div>
                <button type="button" id="reward-close" class="w-full bg-black text-white py-3 font-bold border-2 border-transparent hover:bg-white hover:text-black hover:border-black transition-colors dark:bg-[#10b981] dark:text-black dark:hover:bg-black dark:hover:text-[#10b981] dark:hover:border-[#10b981]">
                    <?php echo get_theme_text('close', $this); ?>
                </button>
            </div>
        </div>
        <?php endif; ?>

        <div class="px-6 md:px-10 pb-10 flex flex-wrap gap-2">
            <span class="font-black mr-2 text-lg dark:text-white"><?php echo get_theme_text('tags', $this); ?>:</span>
            <?php $this->tags(' ', true, get_theme_text('no_tags', $this)); ?>
        </div>

        <?php
        $previousPost = bold_get_adjacent_public_post($this, 'previous');
        $nextPost = bold_get_adjacent_public_post($this, 'next');
        ?>
        <?php if ($previousPost || $nextPost): ?>
        <nav class="post-navigation px-6 md:px-10 pb-10" aria-label="<?php echo get_theme_text('post_navigation', $this); ?>">
            <div class="grid md:grid-cols-2 gap-4">
                <?php if ($previousPost): ?>
                <a href="<?php echo htmlspecialchars($previousPost['permalink'], ENT_QUOTES, 'UTF-8'); ?>" class="post-navigation-link post-navigation-previous">
                    <span><?php echo get_theme_text('previous_post', $this); ?></span>
                    <strong><?php echo htmlspecialchars($previousPost['title'], ENT_QUOTES, 'UTF-8', false); ?></strong>
                </a>
                <?php else: ?><span aria-hidden="true"></span><?php endif; ?>
                <?php if ($nextPost): ?>
                <a href="<?php echo htmlspecialchars($nextPost['permalink'], ENT_QUOTES, 'UTF-8'); ?>" class="post-navigation-link post-navigation-next">
                    <span><?php echo get_theme_text('next_post', $this); ?></span>
                    <strong><?php echo htmlspecialchars($nextPost['title'], ENT_QUOTES, 'UTF-8', false); ?></strong>
                </a>
                <?php endif; ?>
            </div>
        </nav>
        <?php endif; ?>

        <div class="px-6 md:px-10 pb-10">
            <div class="border-4 border-black p-6 bg-white shadow-[4px_4px_0px_0px_#000] dark:bg-[#1e1e1e] dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#10b981]">
                <h3 class="font-black text-xl uppercase mb-4 flex items-center gap-2 dark:text-white">
                    <div class="w-4 h-4 bg-cyan-400 border-2 border-black dark:border-[#10b981] dark:bg-[#10b981]" aria-hidden="true"></div> <?php echo get_theme_text('related_posts', $this); ?>
                </h3>
                <ul class="space-y-3">
                    <?php getRelatedPosts($this); ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </article>

    <?php if (!$needsPassword): ?>
    <?php $this->need('comments.php'); ?>
    <?php endif; ?>
</div>

<?php $this->need('sidebar.php'); ?>
<?php $this->need('footer.php'); ?>
