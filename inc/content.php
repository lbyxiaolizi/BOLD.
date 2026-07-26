<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 是否处于 RSS/Atom 订阅源渲染场景
 * （feed 路由不加载主题模板，必须依靠内容钩子做保护）
 */
function bold_is_feed($widget = null) {
    // Feed Archive 会持有已初始化的 Feed 对象。Router::$current 在
    // Archive 构造期间会被内部 Router::match() 改写，不能单独依赖。
    if (is_object($widget) && method_exists($widget, 'getFeed')) {
        try {
            if (is_object($widget->getFeed())) {
                return true;
            }
        } catch (Throwable $e) {
            // 普通 Archive 的 typed property 尚未初始化，继续检查请求路径。
        }
    }

    try {
        $pathInfo = Typecho_Request::getInstance()->getPathInfo();
        $pathInfo = '/' . ltrim(strval($pathInfo), '/');
        if ($pathInfo === '/feed' || strpos($pathInfo, '/feed/') === 0) {
            return true;
        }
    } catch (Throwable $e) {
        // 仅保留 Router 作为兼容旧版 Typecho 的最后兜底。
    }

    return class_exists('Typecho_Router') && Typecho_Router::$current === 'feed';
}

/**
 * 将保护标记解析为树。遇到孤立关闭、交叉闭合、空密码或未闭合标记时，
 * 只保留最早不安全位置之前的合法节点，避免正则处理嵌套块时泄露尾部。
 */
function bold_build_protected_tree($text) {
    $root = array('type' => 'root', 'children' => array(), 'start' => 0);
    $stack = array();
    $stack[] =& $root;
    $lastOffset = 0;
    $invalidAt = null;

    $pattern = '/\{hide\}|\{\/hide\}|\{password:[^}\r\n]+\}|\{\/password\}|\{hide|\{\/hide|\{password:|\{\/password/';
    $matchCount = preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

    if ($matchCount === false) {
        $positions = array();
        foreach (array('{hide', '{/hide', '{password:', '{/password') as $prefix) {
            $position = strpos($text, $prefix);
            if ($position !== false) {
                $positions[] = $position;
            }
        }
        $invalidAt = empty($positions) ? 0 : min($positions);
        return array('root' => $root, 'invalid_at' => $invalidAt, 'has_markers' => true);
    }

    foreach ($matches[0] as $match) {
        $token = $match[0];
        $position = $match[1];
        $parentIndex = count($stack) - 1;

        if ($position > $lastOffset) {
            $stack[$parentIndex]['children'][] = array(
                'type' => 'text',
                'content' => substr($text, $lastOffset, $position - $lastOffset)
            );
        }

        $nodeType = null;
        $password = null;
        if ($token === '{hide}') {
            $nodeType = 'hide';
        } elseif (preg_match('/^\{password:([^}\r\n]+)\}$/', $token, $passwordMatch)) {
            $nodeType = 'password';
            $password = trim($passwordMatch[1]);
            if ($password === '') {
                $invalidAt = count($stack) > 1 ? $stack[1]['start'] : $position;
                break;
            }
        } elseif ($token === '{/hide}' || $token === '{/password}') {
            $expectedType = $token === '{/hide}' ? 'hide' : 'password';
            if (count($stack) === 1 || $stack[$parentIndex]['type'] !== $expectedType) {
                $invalidAt = count($stack) > 1 ? $stack[1]['start'] : $position;
                break;
            }
            array_pop($stack);
            $lastOffset = $position + strlen($token);
            continue;
        } else {
            // 命中了保护标记前缀但不是完整合法标记。
            $invalidAt = count($stack) > 1 ? $stack[1]['start'] : $position;
            break;
        }

        $parentIndex = count($stack) - 1;
        $stack[$parentIndex]['children'][] = array(
            'type' => $nodeType,
            'password' => $password,
            'children' => array(),
            'start' => $position
        );
        $childIndex = count($stack[$parentIndex]['children']) - 1;
        $stack[] =& $stack[$parentIndex]['children'][$childIndex];
        $lastOffset = $position + strlen($token);
    }

    if ($invalidAt === null) {
        if (count($stack) > 1) {
            $invalidAt = $stack[1]['start'];
        } elseif ($lastOffset < strlen($text)) {
            $root['children'][] = array(
                'type' => 'text',
                'content' => substr($text, $lastOffset)
            );
        }
    }

    return array(
        'root' => $root,
        'invalid_at' => $invalidAt,
        'has_markers' => $matchCount > 0
    );
}

function bold_parse_protected_content($text) {
    if (!is_string($text) || $text === '') {
        return array(
            'nodes' => array(array('type' => 'text', 'content' => is_string($text) ? $text : '')),
            'invalid' => false,
            'has_markers' => false
        );
    }

    $parsed = bold_build_protected_tree($text);
    if ($parsed['invalid_at'] === null) {
        return array(
            'nodes' => $parsed['root']['children'],
            'invalid' => false,
            'has_markers' => $parsed['has_markers']
        );
    }

    $safePrefix = substr($text, 0, $parsed['invalid_at']);
    $safe = bold_build_protected_tree($safePrefix);
    return array(
        'nodes' => $safe['root']['children'],
        'invalid' => true,
        'has_markers' => true
    );
}

function bold_nodes_have_type($nodes, $type) {
    foreach ($nodes as $node) {
        if ($node['type'] === $type) {
            return true;
        }
        if (!empty($node['children']) && bold_nodes_have_type($node['children'], $type)) {
            return true;
        }
    }
    return false;
}

function bold_render_stripped_nodes($nodes) {
    $output = '';
    foreach ($nodes as $node) {
        if ($node['type'] === 'text') {
            $output .= $node['content'];
        }
    }
    return $output;
}

/**
 * 剥离所有合法保护块；语法异常时从最早不安全位置截断。
 */
function bold_strip_protected_markers($text, $invalidReplacement = '') {
    $parsed = bold_parse_protected_content($text);
    $output = bold_render_stripped_nodes($parsed['nodes']);
    return $parsed['invalid'] ? $output . $invalidReplacement : $output;
}

function bold_protection_notice($archive) {
    return '<div class="inline-password-container my-6"><div class="inline-password-inner p-4 text-center font-bold">'
        . get_theme_text('password_protected_content', $archive)
        . '</div></div>';
}

/**
 * contentEx 钩子：
 *  - feed 场景：受密码保护的文章整体替换为提示，未保护文章也剥离
 *    {hide}/{password:} 块，杜绝明文密码与隐藏内容外泄
 *  - 普通场景：为未声明 loading 的图片注入懒加载属性，并记录
 *    渲染结果是否含代码块（footer 据此决定加载 Prism）
 */
function bold_content_filter($content, $widget) {
    if (!is_string($content)) {
        return $content;
    }

    if (bold_is_feed($widget)) {
        if (Helper::options()->protectFeed != '0' && isPasswordProtected($widget)) {
            return '<p>' . get_theme_text('feed_protected', $widget) . '</p>';
        }
        return bold_strip_protected_markers(
            $content,
            '<p>' . get_theme_text('feed_protected', $widget) . '</p>'
        );
    }

    // 渲染层代码块探测：缩进式/波浪线围栏等原文启发式测不到的写法，
    // 渲染后一定是 <pre>/<code>，footer 的 Prism 加载以此为准
    if (stripos($content, '<pre') !== false || stripos($content, '<code') !== false) {
        $GLOBALS['bold_has_code'] = true;
    }

    if (stripos($content, '<img') !== false) {
        $content = preg_replace('/<img(?![^>]*\bloading=)([^>]*?)(\/?)>/i', '<img loading="lazy" decoding="async"$1$2>', $content);
    }

    return $content;
}

/**
 * excerptEx 钩子：feed 摘要同样不泄露受保护内容与密码标记
 */
function bold_excerpt_filter($excerpt, $widget) {
    if (!is_string($excerpt) || !bold_is_feed($widget)) {
        return $excerpt;
    }
    if (Helper::options()->protectFeed != '0' && isPasswordProtected($widget)) {
        return '<p>' . get_theme_text('feed_protected', $widget) . '</p>';
    }
    return bold_strip_protected_markers(
        $excerpt,
        '<p>' . get_theme_text('feed_protected', $widget) . '</p>'
    );
}

/**
 * 单篇评论 Feed 的频道描述由核心在加载主题 functions.php 前生成，
 * 不经过 contentEx/excerptEx。themeInit 时补做整篇保护或标记剥离。
 */
function bold_protect_feed_metadata($archive) {
    if (!bold_is_feed($archive) || Helper::options()->protectFeed == '0'
        || !$archive->is('single') || !method_exists($archive, 'setDescription')) {
        return;
    }

    $notice = get_theme_text('feed_protected', $archive);
    try {
        if (isPasswordProtected($archive)) {
            $archive->setDescription($notice);
            return;
        }

        $description = method_exists($archive, 'getDescription')
            ? strval($archive->getDescription()) : '';
        $archive->setDescription(bold_strip_protected_markers($description, $notice));
    } catch (Throwable $e) {
        // 无法证明频道描述公开时失败即保密。
        $archive->setDescription($notice);
    }
}

/**
 * 评论订阅源保护：/feed/comments/ 及各文章评论订阅源不经过主题
 * 模板，受保护文章的评论内容在这里统一替换为提示，
 * 避免密码墙后的讨论以公开 XML 外泄。
 */
function bold_comment_feed_filter($content, $widget) {
    if (!is_string($content) || !bold_is_feed($widget)) {
        return $content;
    }
    if (Helper::options()->protectFeed == '0') {
        return $content;
    }
    try {
        $cid = intval($widget->cid ?? 0);
        if ($cid <= 0 || bold_cid_is_protected($cid)) {
            return get_theme_text('feed_protected', $widget);
        }
    } catch (Throwable $e) {
        // 无法证明评论所属文章公开时失败即保密。
        return get_theme_text('feed_protected', $widget);
    }
    return $content;
}

/**
 * 评论 Feed 在正文之外还会输出评论者、时间与永久链接。受保护文章的
 * 评论项统一匿名化这些字段，避免密码墙后的参与者元数据继续外泄。
 */
function bold_comment_feed_item_filter($feedType, $widget) {
    if (Helper::options()->protectFeed == '0') {
        return null;
    }

    try {
        $cid = intval($widget->cid ?? 0);
        $protected = $cid <= 0 || bold_cid_is_protected($cid);
    } catch (Throwable $e) {
        $protected = true;
    }

    if (!$protected) {
        return null;
    }

    $notice = get_theme_text('feed_protected', $widget);
    $widget->author = get_theme_text('protected_comment_author', $widget);
    $widget->authorId = 0;
    $widget->mail = '';
    $widget->url = '';
    $widget->text = $notice;
    $widget->created = 0;
    $widget->permalink = strval(Helper::options()->siteUrl);
    return null;
}

/**
 * 核心逻辑：评论可见
 */
function parseReplyContent($content, $archive) {
    $parsed = bold_parse_protected_content($content);
    if (!$archive->is('single')) {
        return bold_render_stripped_nodes($parsed['nodes'])
            . ($parsed['invalid'] ? bold_protection_notice($archive) : '');
    }

    $hasHide = bold_nodes_have_type($parsed['nodes'], 'hide');
    if ($hasHide || $parsed['invalid']) {
        // 是否解锁取决于访客 Cookie / 登录态，禁止共享缓存
        bold_private_cache_headers();
    }

    $hasComment = false;
    if ($hasHide) {
        $db = Typecho_Db::get();
        $user = Typecho_Widget::widget('Widget_User');

        if ($user->hasLogin() && $user->uid == $archive->authorId) {
            $hasComment = true;
        }
        elseif ($user->hasLogin()) {
            // 仅已通过审核的评论才解锁内容
                try {
                    $comment = $db->fetchRow($db->select()->from('table.comments')
                        ->where('cid = ?', $archive->cid)
                        ->where('authorId = ?', $user->uid)
                        ->where('status = ?', 'approved')
                        ->limit(1));
                } catch (Throwable $e) {
                    $comment = false;
                }
                if ($comment) $hasComment = true;
            }
        else {
            $email = Typecho_Cookie::get('__typecho_remember_mail');
            $replyCommentId = 0;
            if ($email) {
                try {
                    $replyCommentId = bold_reply_unlock_comment_id($archive->cid, $email);
                } catch (Throwable $e) {
                    $replyCommentId = 0;
                }
            }
            if ($email && $replyCommentId > 0) {
                try {
                    $comment = $db->fetchRow($db->select()->from('table.comments')
                        ->where('coid = ?', $replyCommentId)
                        ->where('cid = ?', $archive->cid)
                        ->where('mail = ?', $email)
                        ->where('status = ?', 'approved')
                        ->limit(1));
                } catch (Throwable $e) {
                    $comment = false;
                }
                if ($comment) $hasComment = true;
            }
        }
    }

    $hideNotice = '
            <div class="reply2view-container my-8">
                <div class="reply2view-inner flex flex-col items-center justify-center text-center p-6 md:p-10">
                    <div class="text-6xl mb-4" aria-hidden="true">🔒</div>
                    <h3 class="text-2xl font-black uppercase mb-2">' . get_theme_text('locked_title', $archive) . '</h3>
                    <p class="font-bold mb-6 max-w-md">' . get_theme_text('locked_desc', $archive) . '</p>
                    <a href="#comments" class="inline-block bg-black text-white px-8 py-3 font-black text-lg uppercase tracking-widest hover:bg-white hover:text-black transition-colors border-4 border-black shadow-[4px_4px_0px_0px_#fff] dark:shadow-[4px_4px_0px_0px_#10b981] dark:hover:bg-[#10b981] dark:hover:border-[#10b981]">
                        ' . get_theme_text('go_reply', $archive) . '
                    </a>
                </div>
            </div>
            ';

    $render = function ($nodes) use (&$render, $hasComment, $hideNotice, $archive) {
        $output = '';
        foreach ($nodes as $node) {
            if ($node['type'] === 'text') {
                $output .= $node['content'];
            } elseif ($node['type'] === 'hide') {
                $output .= $hasComment ? $render($node['children']) : $hideNotice;
            } else {
                // 此阶段出现 password 节点说明上一步未处理，不能渲染其子节点。
                $output .= bold_protection_notice($archive);
            }
        }
        return $output;
    };

    $content = $render($parsed['nodes']);
    if ($parsed['invalid']) {
        $content .= bold_protection_notice($archive);
    }
    if ($hasComment && $hasHide) {
        $content = '<div class="p-4 border-l-4 border-green-500 bg-green-50 dark:bg-green-900/20 dark:border-green-400 mb-6">
                        <p class="font-bold text-green-700 dark:text-green-400 m-0">' . get_theme_text('unlocked', $archive) . '</p>
                    </div>' . $content;
    }

    return $content;
}

/**
 * 解析内联密码保护内容 {password:密码}内容{/password}
 */
function parseInlinePasswordContent($content, $archive) {
    $parsed = bold_parse_protected_content($content);
    if (!$archive->is('single')) {
        return bold_render_stripped_nodes($parsed['nodes'])
            . ($parsed['invalid'] ? bold_protection_notice($archive) : '');
    }

    $hasPassword = bold_nodes_have_type($parsed['nodes'], 'password');
    if ($hasPassword || $parsed['invalid']) {
        // 解锁状态取决于 Cookie，禁止共享缓存
        bold_private_cache_headers();
    }
    if (!$hasPassword && !$parsed['invalid']) {
        // 合法的 hide-only 内容必须原样留给回复可见阶段处理。
        return $content;
    }

    $user = Typecho_Widget::widget('Widget_User');
    $render = function ($nodes) use (&$render, $archive, $user) {
        $output = '';
        foreach ($nodes as $node) {
            if ($node['type'] === 'text') {
                $output .= $node['content'];
                continue;
            }
            if ($node['type'] === 'hide') {
                // 回复可见块留给 parseReplyContent，不能在这里提前剥离。
                $output .= '{hide}' . $render($node['children']) . '{/hide}';
                continue;
            }

            $requiredPassword = $node['password'];
            $blockMaterial = intval($archive->cid ?? 0) . '|' . $node['start'] . '|' . $requiredPassword;
            $blockId = substr(hash_hmac('sha256', $blockMaterial, getBoldSecretSalt()), 0, 12);
            $cookieName = 'bold_inline_verified_' . $blockId;
            $isAuthor = $user->hasLogin() && $user->uid == $archive->authorId;
            $isVerified = $isAuthor
                || bold_check_unlock_token(Typecho_Cookie::get($cookieName), $requiredPassword);

            $postField = 'inline_password_' . $blockId;
            $attempted = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST[$postField]);
            if ($attempted && !$isVerified) {
                $inputPassword = strval($_POST[$postField]);
                if (hash_equals($requiredPassword, $inputPassword)) {
                    $token = bold_make_unlock_token($requiredPassword);
                    if (bold_set_unlock_cookie($cookieName, $token, time() + BOLD_UNLOCK_TTL)) {
                        bold_redirect_after_unlock($archive);
                        $isVerified = true;
                    }
                } else {
                    usleep(random_int(200000, 500000));
                }
            }

            if ($isVerified) {
                $messageKey = $isAuthor ? 'unlocked_author' : 'unlocked';
                $output .= '<div class="p-4 border-l-4 border-green-500 bg-green-50 dark:bg-green-900/20 dark:border-green-400 mb-6">
                                <p class="font-bold text-green-700 dark:text-green-400 m-0">' . get_theme_text($messageKey, $archive) . '</p>
                            </div>' . $render($node['children']);
                continue;
            }

            $errorMsg = $attempted ? get_theme_text('password_error', $archive) : '';
            $output .= '
            <div class="inline-password-container my-6">
                <div class="inline-password-inner flex flex-col items-center justify-center text-center p-6">
                    <div class="text-4xl mb-3" aria-hidden="true">🔐</div>
                    <h4 class="text-lg font-black uppercase mb-3">' . get_theme_text('inline_password_title', $archive) . '</h4>';

            if ($errorMsg !== '') {
                $output .= '<div class="bg-red-100 border-2 border-red-500 text-red-700 px-3 py-2 mb-3 font-bold text-sm" role="alert">' . $errorMsg . '</div>';
            }

            $output .= '<form method="post" class="w-full max-w-xs">
                        <input type="password" name="' . $postField . '" placeholder="' . get_theme_text('inline_password_placeholder', $archive) . '"
                            aria-label="' . get_theme_text('inline_password_placeholder', $archive) . '"
                            class="w-full p-2 font-bold border-2 border-black focus:outline-none focus:border-pink-500 mb-3 text-center text-sm dark:bg-[#121212] dark:text-white dark:border-[#10b981]" required>
                        <button type="submit" class="w-full bg-black text-white px-4 py-2 font-black text-sm uppercase tracking-wider hover:bg-pink-500 transition-colors border-2 border-black shadow-[2px_2px_0px_0px_#000] dark:bg-[#10b981] dark:text-black dark:border-[#10b981] dark:shadow-[2px_2px_0px_0px_#000]">
                            ' . get_theme_text('password_submit', $archive) . '
                        </button>
                    </form>
                </div>
            </div>';
        }
        return $output;
    };

    $content = $render($parsed['nodes']);
    return $parsed['invalid'] ? $content . bold_protection_notice($archive) : $content;
}

/**
 * 将 Markdown 渲染后的 mermaid 代码块转换为 Mermaid 可识别的容器
 */
function parseMermaidContent($content) {
    if (!is_string($content) || stripos($content, 'mermaid') === false) {
        return $content;
    }

    // 兼容内容尚未经过 Markdown 渲染时的 fenced code block。
    $content = preg_replace_callback('/(^|\n)```mermaid[ \t]*\r?\n(.*?)\r?\n```/is', function ($matches) {
        return $matches[1] . renderMermaidBlock($matches[2]);
    }, $content);

    // Typecho Markdown 通常会输出 <pre><code class="language-mermaid">...</code></pre>。
    return preg_replace_callback('/<pre\b[^>]*>\s*<code\b([^>]*)>(.*?)<\/code>\s*<\/pre>/is', function ($matches) {
        $codeAttributes = $matches[1];
        $code = $matches[2];

        if (!preg_match('/class\s*=\s*(["\'])(.*?)\1/i', $codeAttributes, $classMatch)) {
            return $matches[0];
        }

        $classes = preg_split('/\s+/', trim($classMatch[2]));
        $isMermaid = false;
        foreach ($classes as $className) {
            if (in_array(strtolower($className), array('mermaid', 'language-mermaid', 'lang-mermaid'), true)) {
                $isMermaid = true;
                break;
            }
        }

        if (!$isMermaid) {
            return $matches[0];
        }

        return renderMermaidBlock(html_entity_decode($code, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }, $content);
}

/**
 * 输出 Mermaid 图表外壳，内容保持转义，交由前端 Mermaid 渲染。
 */
function renderMermaidBlock($diagramCode) {
    $diagramCode = trim((string) $diagramCode);
    if ($diagramCode === '') {
        return '';
    }

    return '<div class="not-prose mermaid-wrapper"><div class="mermaid" aria-label="Mermaid diagram">'
        . htmlspecialchars($diagramCode, ENT_NOQUOTES, 'UTF-8')
        . '</div></div>';
}

/**
 * 从原始正文提取纯文本摘要。
 * 直接处理未渲染的 Markdown 原文，避免列表页对每篇文章做一次完整渲染。
 */
function bold_plain_text($archive, $length) {
    $text = (string)($archive->text ?? '');
    $text = preg_replace('/^<!--markdown-->/', '', $text);
    // 摘要只取 <!--more--> 之前的部分
    $parts = explode('<!--more-->', $text, 2);
    $text = $parts[0];
    $text = bold_strip_protected_markers($text);
    $text = preg_replace('/```[\s\S]*?(?:```|$)/', ' ', $text);
    $text = preg_replace('/!\[[^\]]*\]\([^)]*\)/', ' ', $text);
    $text = preg_replace('/\[([^\]]*)\]\([^)]*\)/', '$1', $text);
    $text = strip_tags($text);
    $text = preg_replace('/^\s{0,3}(?:#{1,6}|>+)\s*/m', '', $text);
    $text = preg_replace('/[*_`~]+/', '', $text);
    $text = preg_replace('/\s+/u', ' ', trim($text));
    return Typecho_Common::subStr($text, 0, $length, '...');
}

/**
 * 摘要输出。
 * 属于加密分类且访客尚未通过验证时，一律显示保护提示——
 * 标签页/搜索页/作者页等所有列表场景不再泄露正文前 N 字。
 */
function printExcerpt($archive, $length = BOLD_EXCERPT_LENGTH) {
    if (isPasswordProtected($archive) && !isPasswordVerified($archive)) {
        echo get_theme_text('protected_excerpt', $archive);
        return;
    }

    echo bold_plain_text($archive, $length);
}
