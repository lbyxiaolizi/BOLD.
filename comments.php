<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<div id="comments" class="border-t-4 border-black bg-purple-50 p-3 md:p-10"> <!-- 优化1：移动端外间距减小为 p-3 -->
    <?php $this->comments()->to($comments); ?>
    
    <h3 class="text-xl md:text-3xl font-black mb-4 md:mb-8 uppercase flex items-center gap-2 md:gap-3"> <!-- 优化2：标题字号变小 -->
        <span class="bg-black text-white px-1.5 md:px-2"><?php $this->commentsNum(_t('0'), _t('1'), _t('%d')); ?></span> Comments
    </h3>

    <?php if($this->allow('comment')): ?>
    <!-- 优化3：移动端边框改为 border-2 (原来是4)，阴影减小 -->
    <div id="<?php $this->respondId(); ?>" class="mb-8 md:mb-16 bg-white border-2 md:border-4 border-black p-3 md:p-8 shadow-[3px_3px_0px_0px_#000] md:shadow-[8px_8px_0px_0px_#000]">
        
        <div class="mb-3 md:mb-4">
            <?php $comments->cancelReply('<span class="text-xs font-bold text-red-500 underline">取消回复 / Cancel</span>'); ?>
        </div>

        <h4 class="font-bold text-base md:text-lg mb-3 md:mb-4 uppercase border-b-2 border-black pb-2">Leave a Reply</h4>
        
        <form method="post" action="<?php $this->commentUrl() ?>" id="comment-form">
            <?php if($this->user->hasLogin()): ?>
            <p class="mb-3 md:mb-4 font-bold text-sm md:text-base">
                Logged in as <a href="<?php $this->options->profileUrl(); ?>" class="text-pink-600 underline font-black"><?php $this->user->screenName(); ?></a>. 
                <a href="<?php $this->options->logoutUrl(); ?>" class="text-gray-500 text-xs md:text-sm hover:text-black">Logout &raquo;</a>
            </p>
            <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4 mb-3 md:mb-4">
                <!-- 优化4：移动端输入框 border-2，字号 text-sm -->
                <input type="text" name="author" placeholder="Name *" class="w-full p-2 font-bold border-2 md:border-4 border-black focus:outline-none focus:bg-yellow-50 focus:border-pink-500 transition-colors placeholder-gray-400 text-sm md:text-base" value="<?php $this->remember('author'); ?>" required>
                <input type="email" name="mail" placeholder="Email *" class="w-full p-2 font-bold border-2 md:border-4 border-black focus:outline-none focus:bg-yellow-50 focus:border-pink-500 transition-colors placeholder-gray-400 text-sm md:text-base" value="<?php $this->remember('mail'); ?>" required>
            </div>
            <?php endif; ?>
            
            <!-- 优化5：文本域 border-2，高度适中 -->
            <textarea name="text" rows="3" placeholder="Your Message..." class="w-full p-2 font-bold border-2 md:border-4 border-black focus:outline-none focus:bg-yellow-50 focus:border-pink-500 transition-colors mb-3 md:mb-4 placeholder-gray-400 text-sm md:text-base" required><?php $this->remember('text'); ?></textarea>
            
            <!-- 优化6：按钮字号 text-sm，border-2 -->
            <button type="submit" class="w-full md:w-auto bg-black text-white px-4 py-2 md:px-8 md:py-3 font-black text-sm md:text-lg uppercase tracking-widest hover:bg-pink-500 transition-colors border-2 border-transparent hover:border-black shadow-none hover:shadow-[3px_3px_0px_0px_#000] md:hover:shadow-[4px_4px_0px_0px_#000]">
                Submit Comment
            </button>
        </form>
    </div>
    <?php endif; ?>

    <?php if ($comments->have()): ?>
    
    <div class="comment-list-wrapper">
        <style>
            .comment-list { margin: 0; padding: 0; list-style: none; }
            .avatar { display: block; width: 100%; height: auto; }
        </style>
        
        <?php $comments->listComments(array(
            'before'        =>  '<ol class="comment-list space-y-4 md:space-y-8">', // 列表间距减小
            'after'         =>  '</ol>',
            'style'         =>  'ol',
            'type'          =>  'comment',
            'replyWord'     =>  'Reply',
            'callback'      =>  'threadedComments'
        )); ?>
    </div>

    <?php if($comments->have()): ?>
    <div class="p-3 md:p-4 flex justify-between font-bold border-t-2 border-black mt-6 md:mt-8 text-sm md:text-base">
        <?php $comments->pageNav('←', '→', 1, '...', array('wrapTag' => 'ul', 'wrapClass' => 'flex gap-2', 'itemTag' => 'li', 'textClass' => 'hover:text-pink-600', 'currentClass' => 'text-pink-600 underline')); ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>