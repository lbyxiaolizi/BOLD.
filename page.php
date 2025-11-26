<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>


<div class="w-full md:w-2/3 border-b-4 md:border-b-0 md:border-r-4 border-black bg-white flex flex-col">
    <article class="flex-grow">
        <header class="p-6 md:p-10 border-b-4 border-black bg-white relative overflow-hidden">
            <div class="absolute -left-10 -top-10 w-40 h-40 bg-pink-300 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
            <div class="absolute right-10 bottom-10 w-20 h-20 bg-cyan-300 rounded-full blur-2xl opacity-50 pointer-events-none"></div>
            <h1 class="text-4xl md:text-6xl font-black uppercase relative z-10"><?php $this->title() ?></h1>
        </header>

        <div class="p-6 md:p-10 prose prose-lg prose-slate max-w-none prose-headings:font-black prose-p:text-gray-800 prose-img:rounded-none">
            
            <!-- 如果是“关于”页面，或者是设置了头像的页面，显示个人简介卡片 -->
            <div class="not-prose bg-yellow-50 border-4 border-black p-6 md:p-8 mb-10 shadow-[8px_8px_0px_0px_#000]">
                <div class="flex flex-col md:flex-row gap-6 items-center md:items-start">
                    <div class="w-32 h-32 md:w-40 md:h-40 bg-black border-4 border-white shadow-[0px_0px_0px_4px_#000] overflow-hidden flex-shrink-0 flex items-center justify-center">
                        <?php if($this->options->avatarUrl): ?>
                            <img src="<?php $this->options->avatarUrl(); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <svg class="w-20 h-20 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        <?php endif; ?>
                    </div>
                    <div class="text-center md:text-left flex-grow">
                        <h3 class="text-2xl font-black uppercase mb-2">lbyxiaolizi</h3>
                        <p class="font-bold text-gray-700 mb-4"><?php $this->options->descriptions(); ?></p>
                        <div class="flex flex-wrap gap-3 justify-center md:justify-start">
                             <?php if ($this->options->githubLink): ?>
                                <a href="<?php $this->options->githubLink(); ?>" target="_blank" class="bg-white text-black border-2 border-black px-5 py-2 font-bold hover:bg-blue-400 hover:text-white transition-colors no-underline">GitHub</a>
                            <?php endif; ?>
                            
                            <?php if ($this->options->bilibiliLink): ?>
                                <a href="<?php $this->options->bilibiliLink(); ?>" target="_blank" class="bg-white text-black border-2 border-black px-5 py-2 font-bold hover:bg-pink-400 transition-colors no-underline">Bilibili</a>
                            <?php endif; ?>
                            
                            <?php if ($this->options->email): ?>
                                <a href="<?php $this->options->email(); ?>" target="_blank" class="bg-white text-black border-2 border-black px-5 py-2 font-bold hover:bg-yellow-400 transition-colors no-underline">Email</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php $this->content(); ?>
        </div>
    </article>
    <?php $this->need('comments.php'); ?>
</div>

<?php $this->need('sidebar.php'); ?>
<?php $this->need('footer.php'); ?>