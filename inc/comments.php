<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * Turnstile 是否已完整配置（Site Key 与 Secret Key 必须同时存在，
 * 半配置状态下前端不渲染组件、服务端不校验，行为保持一致）
 */
function bold_turnstile_enabled() {
    $options = Helper::options();
    return !empty($options->turnstileSiteKey) && !empty($options->turnstileSecretKey);
}

/**
 * 评论验证钩子
 */
class ThemeHooks {
    public static function verifyTurnstile($comment, $post) {
        if (!bold_turnstile_enabled()) {
            return $comment;
        }

        $token = Typecho_Request::getInstance()->get('cf-turnstile-response');
        if (empty($token)) {
            throw new Typecho_Widget_Exception(_t('请完成人机验证 (Please complete the CAPTCHA)'));
        }

        $postData = http_build_query(array(
            'secret'   => Helper::options()->turnstileSecretKey,
            'response' => $token,
            'remoteip' => Typecho_Request::getInstance()->getIp(),
        ));

        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        $response = false;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT        => 10,
            ));
            $response = curl_exec($ch);
            curl_close($ch);
        } else {
            // 无 curl 扩展时回退到流封装
            $context = stream_context_create(array('http' => array(
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postData,
                'timeout' => 10,
            )));
            $response = @file_get_contents($url, false, $context);
        }

        if (empty($response)) {
            throw new Typecho_Widget_Exception(_t('无法连接人机验证服务，请稍后重试 (CAPTCHA service unreachable, please retry)'));
        }

        $result = json_decode($response, true);
        if (!is_array($result) || empty($result['success'])) {
            throw new Typecho_Widget_Exception(_t('人机验证失败，请刷新重试 (CAPTCHA verification failed, please retry)'));
        }

        return $comment;
    }

    /**
     * Widget_Feedback::finishComment 在评论成功写入并 push 当前行后触发。
     * 票据证明当前浏览器确实提交过该文章/邮箱组合；是否最终解锁仍由
     * parseReplyContent 查询 approved 状态决定，因此待审评论不会提前授权。
     */
    public static function rememberReplyAuthorization($feedback, $lastResult = null) {
        $cid = intval($feedback->cid ?? 0);
        $coid = intval($feedback->coid ?? 0);
        $mail = trim(strval($feedback->mail ?? ''));
        $authorId = intval($feedback->authorId ?? 0);

        if ($cid <= 0 || $coid <= 0 || $mail === '' || $authorId > 0) {
            return $lastResult;
        }

        try {
            bold_issue_reply_unlock_ticket($cid, $coid, $mail);
        } catch (Throwable $e) {
            // 评论已经成功入库；证明签发失败时保持锁定，不把提交变成 500。
        }
        return $lastResult;
    }
}

/**
 * 自定义评论输出结构
 */
function threadedComments($comments, $options) {
    $commentClass = '';
    if ($comments->authorId) {
        if ($comments->authorId == $comments->ownerId) {
            $commentClass .= ' comment-by-author';
        } else {
            $commentClass .= ' comment-by-user';
        }
    }
?>
<li id="li-<?php $comments->theId(); ?>" class="comment-body<?php
    if ($comments->levels > 0) {
        echo ' comment-child';
        $comments->levelsAlt(' comment-level-odd', ' comment-level-even');
    } else {
        echo ' comment-parent';
    }
    $comments->alt(' comment-odd', ' comment-even');
    echo $commentClass;
?> mb-8 list-none">
    <div id="<?php $comments->theId(); ?>" class="flex flex-col md:flex-row gap-4 flex-wrap">
        <div class="flex-shrink-0">
            <div class="w-12 h-12 border-2 border-black overflow-hidden shadow-[2px_2px_0px_0px_#000]">
                <?php $comments->gravatar('48', ''); ?>
            </div>
        </div>
        <div class="flex-grow">
            <div class="bg-white border-2 border-black p-4 relative shadow-[4px_4px_0px_0px_#1a1a1a] transition-transform hover:-translate-y-1">
                <div class="absolute top-4 -left-2 w-4 h-4 bg-white border-b-2 border-l-2 border-black transform rotate-45 hidden md:block"></div>
                <div class="absolute -top-2 left-4 w-4 h-4 bg-white border-t-2 border-l-2 border-black transform rotate-45 md:hidden"></div>
                <div class="flex flex-wrap justify-between items-center mb-2 border-b-2 border-gray-100 pb-2">
                    <div class="flex items-center gap-2">
                        <span class="font-black text-lg uppercase"><?php $comments->author(); ?></span>
                        <?php if ($comments->authorId == $comments->ownerId): ?>
                            <span class="bg-black text-white text-[10px] px-1 font-bold">AUTHOR</span>
                        <?php endif; ?>
                        <?php if (isset($comments->status) && $comments->status !== 'approved'): ?>
                            <span class="bg-yellow-300 text-black text-[10px] px-1 font-bold border border-black"><?php echo get_theme_text('comment_waiting', $comments); ?></span>
                        <?php endif; ?>
                    </div>
                    <!-- 评论日期沿用后台「评论日期格式」设置 -->
                    <span class="text-xs font-bold text-gray-500 font-mono"><?php $comments->date(); ?></span>
                </div>
                <div class="font-medium text-gray-800 prose prose-sm max-w-none mb-3">
                    <?php $comments->content(); ?>
                </div>
                <div class="text-right">
                    <?php $comments->reply('<span class="inline-block text-xs font-black bg-black text-white px-2 py-1 hover:bg-pink-500 transition-colors cursor-pointer border-2 border-transparent hover:border-black">REPLY</span>'); ?>
                </div>
            </div>
        </div>
    </div>
    <?php if ($comments->children) { ?>
        <div class="comment-children ml-0 md:ml-16 mt-4 border-l-4 border-gray-200 pl-4">
            <?php $comments->threadedComments($options); ?>
        </div>
    <?php } ?>
</li>
<?php }
