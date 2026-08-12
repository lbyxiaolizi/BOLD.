<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 密码保护子系统
 *
 * 解锁凭据使用「过期时间.HMAC」格式的票据，绑定有效期，
 * 不再是可永久重放的裸密码哈希。
 */

/**
 * 获取服务端秘密盐值。Typecho 1.2 的 secret 是安装时生成的随机值。
 * 缺失 secret 时拒绝生成可由公开站点信息伪造的票据。
 */
function getBoldSecretSalt() {
    $secret = trim(strval(Helper::options()->secret ?? ''));
    if ($secret === '') {
        throw new RuntimeException('Typecho site secret is required for BOLD password protection.');
    }
    return hash_hmac('sha256', 'bold-theme-unlock-key-v2', $secret);
}

/**
 * 生成带版本与过期时间的解锁票据。
 */
function bold_make_unlock_token($password) {
    $expires = time() + BOLD_UNLOCK_TTL;
    return 'v2.' . $expires . '.'
        . hash_hmac('sha256', $password . '|' . $expires, getBoldSecretSalt());
}

/**
 * 校验解锁票据（时序安全，过期即失效）。旧版公开密钥票据不兼容，
 * 避免升级后继续保留可伪造的授权窗口。
 */
function bold_check_unlock_token($token, $password) {
    if (empty($token) || !is_string($token) || empty($password)) {
        return false;
    }

    $parts = explode('.', $token);
    if (count($parts) !== 3 || $parts[0] !== 'v2') {
        return false;
    }

    $expiresPart = $parts[1];
    $signature = $parts[2];

    if (!ctype_digit($expiresPart)) {
        return false;
    }
    $expires = intval($expiresPart);
    if ($expires < time() || $expires > time() + BOLD_UNLOCK_TTL + 60) {
        return false;
    }
    $expected = hash_hmac('sha256', $password . '|' . $expiresPart, getBoldSecretSalt());
    return hash_equals($expected, $signature);
}

/**
 * 解锁 Cookie 统一使用 HttpOnly、SameSite=Lax，并在 HTTPS 请求上启用 Secure。
 */
function bold_set_unlock_cookie($key, $value, $expires) {
    if (headers_sent()) {
        return false;
    }

    $cookieName = Typecho_Cookie::getPrefix() . $key;
    $secure = Typecho_Cookie::getSecure();
    try {
        $secure = $secure || Typecho_Request::getInstance()->isSecure();
    } catch (Throwable $e) {
        // 保留 Typecho 全局 Cookie 配置。
    }

    $result = setrawcookie($cookieName, rawurlencode(strval($value)), array(
        'expires' => intval($expires),
        'path' => Typecho_Cookie::getPath(),
        'domain' => Typecho_Cookie::getDomain(),
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ));

    if ($result) {
        $_COOKIE[$cookieName] = strval($value);
    }
    return $result;
}

/**
 * 密码表单使用匿名双提交 Cookie。Cookie 本身不可由脚本读取，表单仅携带
 * 绑定当前页面上下文的 HMAC，避免第三方站点构造有效的解锁 POST。
 */
function bold_password_csrf_cookie_name() {
    return 'bold_password_csrf';
}

function bold_password_csrf_cookie($issueOnGet = false) {
    $cookieName = bold_password_csrf_cookie_name();
    $cookieValue = Typecho_Cookie::get($cookieName, '');
    $nonce = is_string($cookieValue) ? $cookieValue : '';
    if (preg_match('/\A[a-f0-9]{64}\z/', $nonce)) {
        return $nonce;
    }

    if (!$issueOnGet || strtoupper(strval($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
        return null;
    }

    try {
        $nonce = bin2hex(random_bytes(32));
    } catch (Throwable $e) {
        return null;
    }

    return bold_set_unlock_cookie($cookieName, $nonce, time() + BOLD_UNLOCK_TTL)
        ? $nonce
        : null;
}

function bold_ensure_password_csrf_cookie() {
    return bold_password_csrf_cookie(true);
}

/**
 * 安全调用 Widget_Archive::is()。测试替身和部分内容 Widget 不提供该方法。
 */
function bold_archive_is($archive, $type) {
    if (!is_object($archive) || !method_exists($archive, 'is')) {
        return false;
    }

    try {
        return !!$archive->is($type);
    } catch (Throwable $e) {
        return false;
    }
}

function bold_category_archive_slug($archive) {
    if (is_object($archive) && method_exists($archive, 'getArchiveSlug')) {
        try {
            $slug = trim(strval($archive->getArchiveSlug()));
            if ($slug !== '') {
                return $slug;
            }
        } catch (Throwable $e) {
            // 继续使用请求参数兼容旧版 Typecho。
        }
    }

    if (isset($archive->request) && !empty($archive->request->slug)) {
        return trim(strval($archive->request->slug));
    }
    if (isset($archive->parameter) && !empty($archive->parameter->slug)) {
        return trim(strval($archive->parameter->slug));
    }

    $resolvedSlug = trim(strval(resolveCategorySlugFromRequest()));
    if ($resolvedSlug !== '') {
        return $resolvedSlug;
    }

    // 最后兼容不提供 getArchiveSlug() 的旧 Widget/测试替身。
    return isset($archive->slug) ? trim(strval($archive->slug)) : '';
}

/**
 * 构造稳定的页面身份。分类归档不能使用当前结果行 cid，否则翻页或迭代会
 * 改变 CSRF 上下文；单篇内容优先绑定 cid，其余归档绑定请求路径。
 */
function bold_password_archive_identity($archive) {
    if (bold_archive_is($archive, 'category')) {
        return 'category:' . bold_category_archive_slug($archive);
    }

    $cid = intval($archive->cid ?? 0);
    if ($cid > 0) {
        return 'cid:' . $cid;
    }

    $path = '/';
    try {
        $path = strval(Typecho_Request::getInstance()->getPathInfo());
    } catch (Throwable $e) {
        $path = strval($_SERVER['REQUEST_URI'] ?? '/');
    }
    return 'archive:' . hash('sha256', $path);
}

function bold_password_csrf_context($archive, $purpose = 'page', $detail = '') {
    return 'password|' . strval($purpose) . '|'
        . bold_password_archive_identity($archive) . '|' . strval($detail);
}

function bold_password_csrf_token($context) {
    $nonce = bold_password_csrf_cookie(true);
    if ($nonce === null) {
        return '';
    }

    return 'v1.' . hash_hmac(
        'sha256',
        'csrf|' . $nonce . '|' . strval($context),
        getBoldSecretSalt()
    );
}

function bold_validate_password_csrf($token, $context) {
    $nonce = bold_password_csrf_cookie(false);
    if ($nonce === null || !is_string($token)
        || !preg_match('/\Av1\.([a-f0-9]{64})\z/', $token, $matches)) {
        return false;
    }

    $expected = hash_hmac(
        'sha256',
        'csrf|' . $nonce . '|' . strval($context),
        getBoldSecretSalt()
    );
    return hash_equals($expected, $matches[1]);
}

/**
 * 密码 POST 成功后仅重定向到当前站内 path + query，避免重复提交。
 */
function bold_redirect_after_unlock($archive) {
    $requestUri = strval($_SERVER['REQUEST_URI'] ?? '/');
    $parts = parse_url($requestUri);
    $path = is_array($parts) && isset($parts['path']) ? $parts['path'] : '/';
    if ($path === '' || $path[0] !== '/') {
        $path = '/';
    }
    $target = $path;
    if (is_array($parts) && isset($parts['query']) && $parts['query'] !== '') {
        $target .= '?' . $parts['query'];
    }
    $archive->response->redirect($target);
}

/**
 * 匿名评论解锁证明绑定文章与规范化邮箱，不能仅凭客户端可写的
 * __typecho_remember_mail Cookie 获得回复可见内容。
 */
function bold_reply_unlock_material($cid, $coid, $mail) {
    return 'reply|' . intval($cid) . '|' . intval($coid) . '|'
        . strtolower(trim(strval($mail)));
}

function bold_reply_unlock_cookie_name($cid) {
    return 'bold_reply_verified_' . max(0, intval($cid));
}

function bold_issue_reply_unlock_ticket($cid, $coid, $mail) {
    $cid = intval($cid);
    $coid = intval($coid);
    $mail = trim(strval($mail));
    if ($cid <= 0 || $coid <= 0 || $mail === '') {
        return false;
    }

    $material = bold_reply_unlock_material($cid, $coid, $mail);
    return bold_set_unlock_cookie(
        bold_reply_unlock_cookie_name($cid),
        $coid . ':' . bold_make_unlock_token($material),
        time() + BOLD_UNLOCK_TTL
    );
}

function bold_reply_unlock_comment_id($cid, $mail) {
    $cid = intval($cid);
    $mail = trim(strval($mail));
    if ($cid <= 0 || $mail === '') {
        return 0;
    }

    $ticket = Typecho_Cookie::get(bold_reply_unlock_cookie_name($cid));
    if (!is_string($ticket) || strpos($ticket, ':') === false) {
        return 0;
    }
    list($coidPart, $token) = explode(':', $ticket, 2);
    if (!ctype_digit($coidPart) || intval($coidPart) <= 0) {
        return 0;
    }

    $coid = intval($coidPart);
    return bold_check_unlock_token($token, bold_reply_unlock_material($cid, $coid, $mail))
        ? $coid
        : 0;
}

function bold_has_reply_unlock_ticket($cid, $mail) {
    return bold_reply_unlock_comment_id($cid, $mail) > 0;
}

/**
 * 受保护内容的响应禁止被共享缓存（整页缓存/CDN 场景下
 * 「解锁正文」与「密码表单」共用同一 URL，仅靠 Cookie 区分）
 */
function bold_private_cache_headers() {
    static $sent = false;
    if ($sent || headers_sent()) {
        return;
    }
    $sent = true;
    header('Cache-Control: private, no-store, max-age=0');
    header('Vary: Cookie', false);
}

/**
 * 清理分类 slug 用于 Cookie 名称。
 * 非 ASCII slug（如纯中文）会被清空，因此追加原始 slug 的哈希后缀，
 * 保证不同分类永远不会共用同一个 Cookie。
 */
function sanitizeCategorySlugForCookie($slug) {
    $slug = strval($slug);
    $safe = preg_replace('/[^a-zA-Z0-9_-]/', '', $slug);
    if ($safe !== $slug || $safe === '') {
        $safe .= '_' . substr(hash('sha256', $slug), 0, 8);
    }
    return $safe;
}

/**
 * 从请求中解析分类 slug，兼容 /category/{slug}/ 或包含 index.php 的路径
 * @return string|null
 */
function resolveCategorySlugFromRequest() {
    $request = Typecho_Request::getInstance();

    // 1) 优先显式参数
    $slug = $request->get('slug');
    if (!empty($slug)) {
        return $slug;
    }

    // 2) 从路径解析
    $pathInfo = trim($request->getPathInfo(), '/');
    if (!empty($pathInfo)) {
        $segments = array_values(array_filter(explode('/', $pathInfo)));

        // 在路径中找到 "category" 段，取其后的下一个元素作为 slug
        $categoryIndex = array_search('category', $segments, true);
        if ($categoryIndex !== false && isset($segments[$categoryIndex + 1])) {
            $slugCandidate = $segments[$categoryIndex + 1];
        } else {
            // 如果不存在明确的 category 段，尝试使用最后一个段
            $slugCandidate = end($segments);
        }

        if (!empty($slugCandidate)) {
            // 去除可能的后缀（如 .html）并解码
            $slugCandidate = preg_replace('/\.(html?|php)$/i', '', $slugCandidate);
            return urldecode($slugCandidate);
        }
    }

    // 3) 通过 mid 查找
    $mid = $request->get('mid');
    if (!empty($mid)) {
        $db = Typecho_Db::get();
        $meta = $db->fetchRow($db->select('slug')->from('table.metas')->where('mid = ?', $mid)->limit(1));
        if ($meta && !empty($meta['slug'])) {
            return $meta['slug'];
        }
    }

    return null;
}

/**
 * 解析分类密码配置（请求级静态缓存，列表页不再逐篇重复解析）
 * @return array 分类slug => 密码
 */
function parseCategoryPasswords() {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $options = Helper::options();
    $categoryPasswords = array();

    if (!empty($options->categoryPasswords)) {
        $lines = explode("\n", $options->categoryPasswords);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, ':') === false) {
                continue;
            }
            // 只按第一个冒号拆分，密码本身允许包含冒号
            list($slug, $password) = explode(':', $line, 2);
            $slug = trim($slug);
            $password = trim($password);
            if (!empty($slug) && !empty($password)) {
                $categoryPasswords[$slug] = $password;
            }
        }
    }

    return $cache = $categoryPasswords;
}

/**
 * 全部受保护分类 slug（显式配置 + 配了独立密码的分类）
 * @return array
 */
function bold_get_protected_slugs() {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $options = Helper::options();
    $protectedSlugs = array();

    if (!empty($options->passwordProtectedCategories)) {
        $protectedSlugs = array_map('trim', explode(',', $options->passwordProtectedCategories));
    }

    // 配了独立密码的分类同样视为受保护，防止漏配导致归档绕过
    $categoryPasswords = parseCategoryPasswords();
    if (!empty($categoryPasswords)) {
        $protectedSlugs = array_merge($protectedSlugs, array_keys($categoryPasswords));
    }

    return $cache = array_values(array_unique(array_filter($protectedSlugs)));
}

/**
 * 某个受保护分类实际所需的密码；不受保护返回 null
 */
function bold_category_password($slug) {
    $requirement = bold_category_password_requirement($slug);
    return $requirement === null ? null : $requirement['password'];
}

function bold_category_password_requirement($slug) {
    if (!in_array($slug, bold_get_protected_slugs())) {
        return null;
    }

    $categoryPasswords = parseCategoryPasswords();
    if (!empty($categoryPasswords[$slug])) {
        return array(
            'password' => $categoryPasswords[$slug],
            'source' => 'category',
            'id' => $slug,
            'cookie' => bold_category_unlock_cookie_name($slug)
        );
    }

    $options = Helper::options();
    if (!empty($options->postPassword)) {
        return array(
            'password' => strval($options->postPassword),
            'source' => 'global',
            'id' => 'global',
            'cookie' => 'bold_password_verified'
        );
    }

    // 未配置任何密码时保持不可解锁，且该值不能由公开站点信息计算。
    return array(
        'password' => hash_hmac('sha256', 'unconfigured-category|' . $slug, getBoldSecretSalt()),
        'source' => 'category',
        'id' => $slug,
        'cookie' => bold_category_unlock_cookie_name($slug)
    );
}

/**
 * 检查某个分类当前访客是否已解锁（供不持有 archive 对象的场景使用，如时间轴）
 */
function bold_is_category_unlocked($slug) {
    $user = Typecho_Widget::widget('Widget_User');
    if ($user->hasLogin() && $user->pass('editor', true)) {
        return true;
    }
    $requirement = bold_category_password_requirement($slug);
    if ($requirement === null) {
        return true;
    }
    return bold_check_unlock_token(
        Typecho_Cookie::get($requirement['cookie']),
        $requirement['password']
    );
}

/**
 * 读取当前内容行的自定义字段密码。
 */
function bold_entry_password($archive) {
    if (intval($archive->cid) <= 0) {
        return null;
    }

    // Widget 的 fields 是计算型魔术属性，不能在链式 isset() 中读取；
    // 必须先取出 Config，否则 Typecho 会把它误判为不存在。
    $fields = $archive->fields;
    $password = strval($fields->password ?? '');
    return $password !== '' ? $password : null;
}

/**
 * 默认只在单篇文章、独立页面和 Feed 条目上读取当前行的字段密码。
 * 分类/搜索等归档的 archive 对象会随结果集移动，不能把当前行字段当成
 * 整个归档页密码；列表摘要可通过显式参数要求检查该条目。
 */
function bold_should_check_entry_password($archive) {
    if (function_exists('bold_is_feed') && bold_is_feed($archive)) {
        return true;
    }

    if (bold_archive_is($archive, 'category')) {
        return false;
    }

    if (bold_archive_is($archive, 'single')
        || bold_archive_is($archive, 'post')
        || bold_archive_is($archive, 'page')) {
        return true;
    }

    // 非 Archive 内容 Widget 没有 is()，保持原有的单条内容行为。
    return !is_object($archive) || !method_exists($archive, 'is');
}

function bold_entry_unlock_cookie_name($cid) {
    return 'bold_entry_verified_' . max(0, intval($cid));
}

function bold_category_unlock_cookie_name($slug) {
    return 'bold_category_verified_' . sanitizeCategorySlugForCookie($slug);
}

/**
 * 返回密码及其授权作用域。作用域决定唯一的 Cookie，避免两个独立文章密码
 * 或文章密码与分类密码互相覆盖。
 */
function bold_password_requirement($archive, $includeEntryPassword = null) {
    if ($includeEntryPassword === null) {
        $includeEntryPassword = bold_should_check_entry_password($archive);
    }

    if ($includeEntryPassword) {
        $entryPassword = bold_entry_password($archive);
        if ($entryPassword !== null) {
            $cid = intval($archive->cid ?? 0);
            return array(
                'password' => $entryPassword,
                'source' => 'entry',
                'id' => $cid,
                'cookie' => bold_entry_unlock_cookie_name($cid)
            );
        }
    }

    $categorySlug = getProtectedCategorySlug($archive, $includeEntryPassword);
    if ($categorySlug !== null) {
        return bold_category_password_requirement($categorySlug);
    }

    $globalPassword = strval(Helper::options()->postPassword ?? '');
    if ($globalPassword !== '') {
        return array(
            'password' => $globalPassword,
            'source' => 'global',
            'id' => 'global',
            'cookie' => 'bold_password_verified'
        );
    }

    return null;
}

/**
 * 获取文章/分类所需的密码（优先级：文章独立密码 > 分类独立密码 > 全站密码）
 * @return string|null 不需要密码时返回 null
 */
function getRequiredPassword($archive, $includeEntryPassword = null) {
    $requirement = bold_password_requirement($archive, $includeEntryPassword);
    return $requirement === null ? null : $requirement['password'];
}

/**
 * 获取文章所属的受保护分类slug（支持文章页和分类页）
 * @return string|null
 */
function getProtectedCategorySlug($archive, $includeEntryCategories = null) {
    if ($includeEntryCategories === null) {
        $includeEntryCategories = bold_should_check_entry_password($archive);
    }

    $protectedSlugs = bold_get_protected_slugs();
    if (empty($protectedSlugs)) {
        return null;
    }

    // 分类页：检查当前分类是否需要密码保护
    if (bold_archive_is($archive, 'category')) {
        $currentSlug = bold_category_archive_slug($archive);
        if ($currentSlug !== '' && in_array($currentSlug, $protectedSlugs)) {
            return $currentSlug;
        }

        // 页面级检查不能退回当前结果行；显式的单条摘要检查则必须继续
        // 检查该文章的其它分类，防止跨分类文章从公开归档泄露摘要。
        if (!$includeEntryCategories) {
            return null;
        }
    }

    // 文章所属分类（列表页与文章页都检测，避免摘要泄露）
    if (!empty($archive->categories)) {
        foreach ($archive->categories as $category) {
            if (in_array($category['slug'], $protectedSlugs)) {
                return $category['slug'];
            }
        }
    }

    return null;
}

/**
 * 检查文章是否需要密码保护
 */
function isPasswordProtected($archive, $includeEntryPassword = null) {
    if (getRequiredPassword($archive, $includeEntryPassword) === null) {
        return false;
    }
    // HTML 页面的展示取决于访客 Cookie，禁止共享缓存；
    // feed 输出对所有访客一致（受保护文章一律是提示文本），保持可缓存
    if (!bold_is_feed($archive)) {
        bold_private_cache_headers();
    }
    return true;
}

/**
 * 判断某篇文章（按 cid）是否受密码保护（供评论订阅源等
 * 不持有文章 Widget 的场景使用；请求级缓存）
 */
function bold_cid_is_protected($cid) {
    static $cache = array();

    $cid = intval($cid);
    if ($cid <= 0) {
        return false;
    }
    if (isset($cache[$cid])) {
        return $cache[$cid];
    }

    if (!empty(Helper::options()->postPassword)) {
        return $cache[$cid] = true;
    }

    $db = Typecho_Db::get();

    // 文章自定义字段密码
    $field = $db->fetchRow($db->select('str_value')->from('table.fields')
        ->where('cid = ?', $cid)
        ->where('name = ?', 'password')
        ->limit(1));
    if ($field && !empty($field['str_value'])) {
        return $cache[$cid] = true;
    }

    // 加密分类
    $slugs = bold_get_protected_slugs();
    if (empty($slugs)) {
        return $cache[$cid] = false;
    }
    $rows = $db->fetchAll($db->select('table.metas.slug')->from('table.relationships')
        ->join('table.metas', 'table.relationships.mid = table.metas.mid')
        ->where('table.relationships.cid = ?', $cid)
        ->where('table.metas.type = ?', 'category'));
    foreach ($rows as $row) {
        if (in_array($row['slug'], $slugs)) {
            return $cache[$cid] = true;
        }
    }

    return $cache[$cid] = false;
}

/**
 * 检查密码是否已验证。
 * 登录用户仅编辑及以上直接放行——订阅者等低权限账号不再绕过密码。
 */
function isPasswordVerified($archive, $includeEntryPassword = null) {
    $user = Typecho_Widget::widget('Widget_User');
    if ($user->hasLogin() && $user->pass('editor', true)) {
        return true;
    }

    $requirement = bold_password_requirement($archive, $includeEntryPassword);
    if ($requirement === null) {
        return true;
    }

    return bold_check_unlock_token(
        Typecho_Cookie::get($requirement['cookie']),
        $requirement['password']
    );
}

/**
 * 处理密码验证请求
 * @return bool true 表示本次提交验证失败（模板据此显示错误提示）
 */
function handlePasswordVerification($archive) {
    $requestMethod = strtoupper(strval($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($requestMethod === 'GET') {
        bold_ensure_password_csrf_cookie();
        return false;
    }
    if ($requestMethod !== 'POST' || !isset($_POST['bold_password'])) {
        return false;
    }

    bold_private_cache_headers();
    if (!is_string($_POST['bold_password'])) {
        return true;
    }
    $inputPassword = $_POST['bold_password'];
    $requirement = bold_password_requirement($archive);
    $correctPassword = $requirement === null ? null : $requirement['password'];

    $csrfContext = bold_password_csrf_context($archive, 'page');
    if (!bold_validate_password_csrf($_POST['bold_password_csrf'] ?? '', $csrfContext)) {
        return true;
    }

    // Referer 仅在「存在且主机不匹配」时拒绝；浏览器隐私策略
    // （Referrer-Policy: no-referrer 等）不携带 Referer 时不应锁死用户
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if ($referer !== '') {
        $refererHost = parse_url($referer, PHP_URL_HOST);
        $siteHost = parse_url(Helper::options()->siteUrl, PHP_URL_HOST);
        if (empty($refererHost) || empty($siteHost) || strcasecmp($refererHost, $siteHost) !== 0) {
            return true;
        }
    }

    if (!empty($correctPassword) && hash_equals(strval($correctPassword), $inputPassword)) {
        $token = bold_make_unlock_token($correctPassword);
        $cookieSet = bold_set_unlock_cookie(
            $requirement['cookie'],
            $token,
            time() + BOLD_UNLOCK_TTL
        );

        if ($cookieSet) {
            bold_redirect_after_unlock($archive);
        }
        return true;
    }

    // 失败时随机延迟：抬高爆破成本，并平滑比较路径的时序差异
    usleep(random_int(200000, 500000));
    return true;
}

/**
 * 输出密码保护表单（样式在 assets/css/bold.css）
 */
function renderPasswordForm($archive, $hasError = false) {
    // 获取受保护的分类名用于提示文案
    $categorySlug = getProtectedCategorySlug($archive);
    $categoryName = null;
    if ($categorySlug && !empty($archive->categories)) {
        foreach ($archive->categories as $category) {
            if ($category['slug'] === $categorySlug) {
                $categoryName = $category['name'];
                break;
            }
        }
    }

    if ($categoryName) {
        $desc = sprintf(get_theme_text('password_protected_category', $archive),
            htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8', false));
    } else {
        $desc = get_theme_text('password_protected_content', $archive);
    }
    $csrfToken = bold_password_csrf_token(bold_password_csrf_context($archive, 'page'));
    ?>
    <div class="password-form-container my-8">
        <div class="password-form-inner flex flex-col items-center justify-center text-center p-6 md:p-10">
            <div class="text-6xl mb-4" aria-hidden="true">🔐</div>
            <h3 class="text-2xl font-black uppercase mb-2"><?php echo get_theme_text('password_required', $archive); ?></h3>
            <p class="font-bold mb-6 max-w-md"><?php echo $desc; ?></p>

            <?php if ($hasError): ?>
            <div class="bg-red-100 border-2 border-red-500 text-red-700 px-4 py-2 mb-4 font-bold" role="alert">
                <?php echo get_theme_text('password_error', $archive); ?>
            </div>
            <?php endif; ?>

            <form method="post" class="w-full max-w-sm">
                <input type="hidden" name="bold_password_csrf" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="password" name="bold_password" placeholder="<?php echo get_theme_text('password_placeholder', $archive); ?>"
                    aria-label="<?php echo get_theme_text('password_placeholder', $archive); ?>"
                    class="w-full p-3 font-bold border-4 border-black focus:outline-none focus:border-pink-500 mb-4 text-center dark:bg-[#121212] dark:text-white dark:border-[#10b981]" required>
                <button type="submit" class="w-full bg-black text-white px-8 py-3 font-black text-lg uppercase tracking-widest hover:bg-pink-500 transition-colors border-4 border-black shadow-[4px_4px_0px_0px_#000] dark:bg-[#10b981] dark:text-black dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#000]">
                    <?php echo get_theme_text('password_submit', $archive); ?>
                </button>
            </form>
        </div>
    </div>
    <?php
}

/**
 * 检查文章是否应该从首页隐藏（属于加密分类且设置了隐藏）
 */
function shouldHideFromHome($archive) {
    $options = Helper::options();

    if (empty($options->hideProtectedCategoriesFromHome) || $options->hideProtectedCategoriesFromHome == '0') {
        return false;
    }
    if (empty($archive->categories)) {
        return false;
    }

    $protectedSlugs = bold_get_protected_slugs();
    foreach ($archive->categories as $category) {
        if (in_array($category['slug'], $protectedSlugs)) {
            return true;
        }
    }

    return false;
}
