<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<aside class="w-full md:w-1/3 bg-white">
    
    <!-- 1. 个人简介 (仅当配置了头像URL时显示) -->
    <?php if ($this->options->avatarUrl): ?>
    <div class="p-6 md:p-10 border-b-4 border-black bg-yellow-50">
        <div class="flex flex-col items-center text-center">
            <div class="w-24 h-24 border-4 border-black overflow-hidden shadow-[4px_4px_0px_0px_#000] mb-4">
                <img src="<?php $this->options->avatarUrl(); ?>" alt="Avatar" class="w-full h-full object-cover">
            </div>
            <h3 class="font-black text-xl uppercase mb-2">lbyxiaolizi</h3>
            <p class="font-bold text-sm text-gray-700 mb-4"><?php $this->options->description(); ?></p>
            
            <div class="flex gap-3">
                <?php if ($this->options->githubLink): ?>
                <a href="<?php $this->options->githubLink(); ?>" target="_blank" class="bg-black text-white border-2 border-black px-5 py-2 font-bold hover:bg-blue-400 hover:text-white transition-colors no-underline">GitHub</a>
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
    <?php endif; ?>

    <!-- 2. 文章目录 (TOC) - 仅在文章或独立页显示 -->
    <?php if ($this->is('post') || $this->is('page')): ?>
    <div id="toc-wrapper" class="p-6 md:p-10 border-b-4 border-black hidden">
        <h3 class="font-black text-xl mb-4 uppercase flex items-center gap-2">
            <div class="w-4 h-4 bg-pink-500 border-2 border-black"></div> Table of Contents
        </h3>
        <!-- 目录内容生成于此 -->
        <div class="toc-container font-bold text-sm"></div>
    </div>
    <?php endif; ?>

    <!-- 3. 搜索 -->
    <div class="p-6 md:p-10 border-b-4 border-black">
        <h3 class="font-black text-xl mb-4 uppercase flex items-center gap-2">
            <div class="w-4 h-4 bg-yellow-400 border-2 border-black"></div> Search
        </h3>
        <form method="post" action="<?php $this->options->siteUrl(); ?>" role="search" class="flex border-4 border-black focus-within:ring-4 ring-pink-400 transition-all shadow-none md:shadow-[4px_4px_0px_0px_#000]">
            <input type="text" name="s" placeholder="输入关键词..." class="w-full p-3 font-bold focus:outline-none bg-white placeholder-gray-400 text-black">
            <button type="submit" class="bg-black text-white px-4 hover:bg-pink-500 hover:text-white transition-colors border-l-4 border-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </button>
        </form>
    </div>

    <!-- 4. 分类 -->
    <div class="p-6 md:p-10 border-b-4 border-black">
        <h3 class="font-black text-xl mb-6 uppercase flex items-center gap-2">
            <div class="w-4 h-4 bg-cyan-400 border-2 border-black"></div> Categories
        </h3>
        <ul class="space-y-3 font-bold text-lg">
            <?php $this->widget('Widget_Metas_Category_List')->to($category); ?>
            <?php while ($category->next()): ?>
            <li><a href="<?php $category->permalink(); ?>" class="block p-3 border-2 border-black hover:bg-yellow-200 transition-all flex justify-between items-center shadow-[4px_4px_0px_0px_#1a1a1a] hover:translate-x-1 hover:shadow-none hover:translate-y-1 active:shadow-none active:translate-x-1 active:translate-y-1">
                <span><?php $category->name(); ?></span> <span class="text-xs bg-black text-white px-2 py-1 rounded-none font-mono"><?php $category->count(); ?></span>
            </a></li>
            <?php endwhile; ?>
        </ul>
    </div>

    <!-- 5. 最新评论 -->
    <div class="p-6 md:p-10">
        <h3 class="font-black text-xl mb-6 uppercase flex items-center gap-2">
            <div class="w-4 h-4 bg-lime-400 border-2 border-black"></div> Comments
        </h3>
        <ul class="space-y-6 text-sm font-medium">
            <?php $this->widget('Widget_Comments_Recent')->to($comments); ?>
            <?php while($comments->next()): ?>
            <li class="group">
                <a href="<?php $comments->permalink(); ?>" class="block relative">
                    <div class="absolute -left-3 top-0 bottom-0 w-1 bg-gray-200 group-hover:bg-purple-500 transition-colors"></div>
                    <span class="font-black text-base bg-purple-100 group-hover:bg-purple-500 group-hover:text-white px-2 py-0.5 border-2 border-transparent group-hover:border-black transition-all"><?php $comments->author(false); ?></span>
                    <span class="text-gray-600 block mt-2 group-hover:text-black"><?php $comments->excerpt(35, '...'); ?></span>
                </a>
            </li>
            <?php endwhile; ?>
        </ul>
    </div>
</aside>