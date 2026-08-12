<?php
define('__TYPECHO_ROOT_DIR__', dirname(__DIR__));
define('BOLD_UNLOCK_TTL', 604800);

class Helper {
    public static $options;
    public static function options() { return self::$options; }
}

class Typecho_Cookie {
    public static function getPrefix() { return 'test_'; }
    public static function getPath() { return '/blog/'; }
    public static function getDomain() { return ''; }
    public static function getSecure() { return false; }
    public static function get($key, $default = null) {
        return $_COOKIE['test_' . $key] ?? $default;
    }
}

class Typecho_Request {
    public static $slug = null;
    public static $pathInfo = '/';
    public static function getInstance() { return new self(); }
    public function isSecure() { return true; }
    public function get($key) { return $key === 'slug' ? self::$slug : null; }
    public function getPathInfo() { return self::$pathInfo; }
}

class BoldTestUser {
    public $role = 'visitor';
    public $uid = 0;
    public function hasLogin() { return $this->role !== 'visitor'; }
    public function pass($role, $strict = false) {
        $levels = array('subscriber' => 1, 'contributor' => 2, 'editor' => 3, 'administrator' => 4);
        return isset($levels[$this->role], $levels[$role]) && $levels[$this->role] >= $levels[$role];
    }
}

class Typecho_Widget {
    public static $user;
    public static function widget($name) { return self::$user; }
}

class BoldTestConfig {
    private $values;
    public function __construct($values) { $this->values = $values; }
    public function __get($name) { return $this->values[$name] ?? null; }
    public function __isSet($name) { return isset($this->values[$name]); }
}

class BoldTestResponse {
    public $redirectedTo = null;
    public function redirect($target) { $this->redirectedTo = $target; }
}

class BoldTestArchive {
    public $cid;
    public $slug;
    public $fields;
    public $categories = array();
    public $authorId = 99;
    public $response;
    private $types;
    private $archiveSlug;

    public function __construct($cid, $types, $password = '', $slug = '') {
        $this->cid = $cid;
        $this->types = $types;
        $this->slug = $slug;
        $this->archiveSlug = $slug;
        $this->fields = new BoldTestConfig(array('password' => $password));
        $this->response = new BoldTestResponse();
    }

    public function is($type) { return in_array($type, $this->types, true); }
    public function getArchiveSlug() { return $this->archiveSlug; }
}

class Typecho_Router {
    public static $current = 'index';
}

function get_theme_text($key, $archive = null) { return '[' . $key . ']'; }

require_once dirname(__DIR__) . '/inc/password.php';

function bold_test_true($value, $message) {
    if ($value) {
        return;
    }
    fwrite(STDERR, $message . "\n");
    exit(1);
}

function bold_test_same($expected, $actual, $message) {
    if ($expected === $actual) {
        return;
    }
    fwrite(STDERR, $message . "\nExpected: " . var_export($expected, true)
        . "\nActual:   " . var_export($actual, true) . "\n");
    exit(1);
}

Helper::$options = (object) array(
    'siteUrl' => 'https://public.example/',
    'secret' => 'server-only-random-secret',
    'postPassword' => '',
    'passwordProtectedCategories' => 'private,shared',
    'categoryPasswords' => 'private:category-secret'
);

$publicLegacyKey = hash('sha256', Helper::$options->siteUrl . 'bold_theme_salt');
bold_test_true(getBoldSecretSalt() !== $publicLegacyKey,
    'The active ticket key must not be derived only from the public site URL.');

$token = bold_make_unlock_token('correct horse');
bold_test_true(strpos($token, 'v2.') === 0, 'New tickets must carry the v2 format marker.');
bold_test_true(bold_check_unlock_token($token, 'correct horse'), 'A new ticket must validate.');
bold_test_true(!bold_check_unlock_token($token, 'wrong password'), 'A ticket must remain bound to its password.');

$tamperedToken = substr($token, 0, -1) . (substr($token, -1) === '0' ? '1' : '0');
bold_test_true(!bold_check_unlock_token($tamperedToken, 'correct horse'),
    'A v2 ticket with a tampered signature must be rejected.');

$expiredAt = time() - 1;
$expiredToken = 'v2.' . $expiredAt . '.' . hash_hmac(
    'sha256',
    'correct horse|' . $expiredAt,
    getBoldSecretSalt()
);
bold_test_true(!bold_check_unlock_token($expiredToken, 'correct horse'),
    'An expired v2 ticket must be rejected even with a valid signature.');

$farFutureAt = time() + BOLD_UNLOCK_TTL + 120;
$farFutureToken = 'v2.' . $farFutureAt . '.' . hash_hmac(
    'sha256',
    'correct horse|' . $farFutureAt,
    getBoldSecretSalt()
);
bold_test_true(!bold_check_unlock_token($farFutureToken, 'correct horse'),
    'A signed v2 ticket beyond the maximum lifetime must be rejected.');

$legacyExpires = time() + 60;
$legacyToken = $legacyExpires . '.' . hash_hmac(
    'sha256',
    'correct horse|' . $legacyExpires,
    $publicLegacyKey
);
bold_test_true(!bold_check_unlock_token($legacyToken, 'correct horse'),
    'Public-key legacy tickets must be invalidated during the key upgrade.');

$savedSecret = Helper::$options->secret;
Helper::$options->secret = '';
$missingSecretRejected = false;
try {
    getBoldSecretSalt();
} catch (RuntimeException $e) {
    $missingSecretRejected = true;
}
bold_test_true($missingSecretRejected, 'A missing server secret must fail closed.');
Helper::$options->secret = $savedSecret;

$archive = new BoldTestArchive(42, array('single', 'post'), 'field-secret');
bold_test_same('field-secret', getRequiredPassword($archive),
    'A custom-field password must be recognized on a single entry.');

$publicCategoryArchive = new BoldTestArchive(77, array('category'), 'row-secret', 'public');
$publicCategoryArchive->slug = 'current-post-slug';
bold_test_same(null, getRequiredPassword($publicCategoryArchive),
    'A public category archive must ignore the current result row custom-field password.');

$protectedCategoryArchive = new BoldTestArchive(78, array('category'), 'row-secret', 'private');
$protectedCategoryArchive->slug = 'another-current-post';
bold_test_same('category-secret', getRequiredPassword($protectedCategoryArchive),
    'A protected category archive must use its category password, not the current result row field.');

bold_test_same('row-secret', getRequiredPassword($publicCategoryArchive, true),
    'An explicit per-entry check must still honor the current row custom-field password.');

$crossListedArchive = new BoldTestArchive(79, array('category'), '', 'public');
$crossListedArchive->categories = array(array('slug' => 'private', 'name' => 'Private'));
bold_test_same(null, getRequiredPassword($crossListedArchive),
    'A public category page must not be locked by a current row secondary category.');
bold_test_same('category-secret', getRequiredPassword($crossListedArchive, true),
    'An explicit excerpt check must honor a current row protected secondary category.');

Typecho_Widget::$user = new BoldTestUser();
Typecho_Widget::$user->role = 'subscriber';
bold_test_true(!isPasswordVerified($archive),
    'A logged-in subscriber must not bypass a protected entry.');
Typecho_Widget::$user->role = 'editor';
bold_test_true(isPasswordVerified($archive),
    'An editor must retain the configured privileged bypass.');
Typecho_Widget::$user->role = 'subscriber';

$entry42Requirement = bold_password_requirement($archive);
$entry43 = new BoldTestArchive(43, array('single', 'post'), 'another-secret');
$entry43Requirement = bold_password_requirement($entry43);
$categoryRequirement = bold_password_requirement($protectedCategoryArchive);
bold_test_same('bold_entry_verified_42', $entry42Requirement['cookie'],
    'A custom entry password must use a cid-scoped unlock cookie.');
bold_test_same('bold_entry_verified_43', $entry43Requirement['cookie'],
    'A second custom entry password must use a separate cid-scoped cookie.');
bold_test_same('bold_category_verified_private', $categoryRequirement['cookie'],
    'A category password must retain its category-scoped unlock cookie.');

Helper::$options->postPassword = 'global-secret';
$globalArchive = new BoldTestArchive(0, array('index'));
$globalRequirement = bold_password_requirement($globalArchive);
bold_test_same('bold_password_verified', $globalRequirement['cookie'],
    'The site-wide password must retain the global unlock cookie.');
$sharedCategoryArchive = new BoldTestArchive(0, array('category'), '', 'shared');
$sharedCategoryRequirement = bold_password_requirement($sharedCategoryArchive);
bold_test_same('global', $sharedCategoryRequirement['source'],
    'A protected category without an independent password must use the global source.');
bold_test_same('bold_password_verified', $sharedCategoryRequirement['cookie'],
    'A category backed by the global password must reuse the global ticket.');
Helper::$options->postPassword = '';

$_COOKIE['test_' . $entry42Requirement['cookie']] = bold_make_unlock_token('field-secret');
bold_test_true(isPasswordVerified($archive),
    'A valid entry ticket must unlock its own entry.');
bold_test_true(!isPasswordVerified($entry43),
    'An entry ticket must not unlock another unclassified entry.');
bold_test_true(!isPasswordVerified($protectedCategoryArchive),
    'An entry ticket must not overwrite or authorize a category ticket.');

$_COOKIE['test_' . $categoryRequirement['cookie']] = bold_make_unlock_token('category-secret');
bold_test_true(isPasswordVerified($protectedCategoryArchive),
    'A category ticket must coexist with an existing entry ticket.');
bold_test_true(isPasswordVerified($archive),
    'Adding a category ticket must not overwrite an existing entry ticket.');

$cookieToken = bold_make_unlock_token('cookie password');
bold_test_true(bold_set_unlock_cookie('bold_test_unlock', $cookieToken, time() + 60),
    'The hardened cookie helper must accept a normal unlock cookie.');
bold_test_same($cookieToken, $_COOKIE['test_bold_test_unlock'] ?? null,
    'The hardened cookie helper must synchronize the current request cookie state.');

$csrfArchive = new BoldTestArchive(84, array('single', 'post'), 'csrf-secret');
unset($_COOKIE['test_' . bold_password_csrf_cookie_name()]);
unset($_COOKIE['test_' . bold_entry_unlock_cookie_name(84)]);
$_SERVER['REQUEST_URI'] = '/private-entry?source=test';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = array('bold_password' => 'csrf-secret');
bold_test_true(handlePasswordVerification($csrfArchive),
    'A direct password POST without a CSRF cookie/token must be rejected.');
bold_test_true(!isset($_COOKIE['test_' . bold_entry_unlock_cookie_name(84)]),
    'A rejected direct POST must not issue an entry unlock ticket.');
bold_test_same(null, $csrfArchive->response->redirectedTo,
    'A rejected direct POST must not redirect as a successful unlock.');

$_SERVER['REQUEST_METHOD'] = 'GET';
$_POST = array();
bold_test_true(handlePasswordVerification($csrfArchive) === false,
    'A GET request must only prepare the anonymous CSRF cookie.');
$csrfCookie = $_COOKIE['test_' . bold_password_csrf_cookie_name()] ?? '';
bold_test_true((bool) preg_match('/\A[a-f0-9]{64}\z/', $csrfCookie),
    'The password CSRF cookie must contain a random 256-bit nonce.');
$csrfContext = bold_password_csrf_context($csrfArchive, 'page');
$csrfToken = bold_password_csrf_token($csrfContext);
bold_test_true(bold_validate_password_csrf($csrfToken, $csrfContext),
    'A CSRF token must validate with its cookie and exact page context.');
$otherEntryCsrfContext = bold_password_csrf_context($entry43, 'page');
bold_test_true(!bold_validate_password_csrf($csrfToken, $otherEntryCsrfContext),
    'A CSRF token copied to another entry context must be rejected.');

ob_start();
renderPasswordForm($csrfArchive);
$pagePasswordForm = ob_get_clean();
bold_test_true(strpos($pagePasswordForm,
    'name="bold_password_csrf" value="' . $csrfToken . '"') !== false,
    'The page password form must include its context-bound CSRF token.');

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = array(
    'bold_password' => 'csrf-secret',
    'bold_password_csrf' => bold_password_csrf_token($otherEntryCsrfContext)
);
handlePasswordVerification($csrfArchive);
bold_test_true(!isset($_COOKIE['test_' . bold_entry_unlock_cookie_name(84)]),
    'A validly signed token for another entry must not issue an unlock ticket.');
bold_test_same(null, $csrfArchive->response->redirectedTo,
    'A cross-entry CSRF token must not trigger the successful redirect.');

$_POST = array(
    'bold_password' => 'csrf-secret',
    'bold_password_csrf' => $csrfToken
);
handlePasswordVerification($csrfArchive);
$csrfUnlockTicket = $_COOKIE['test_' . bold_entry_unlock_cookie_name(84)] ?? '';
bold_test_true(bold_check_unlock_token($csrfUnlockTicket, 'csrf-secret'),
    'A valid cookie/token pair and password must issue the scoped unlock ticket.');
bold_test_same('/private-entry?source=test', $csrfArchive->response->redirectedTo,
    'A valid password submission must retain the local path and query redirect.');

unset($_COOKIE['test_' . bold_reply_unlock_cookie_name(42)]);
$_COOKIE['test___typecho_remember_mail'] = 'victim@example.com';
bold_test_true(!bold_has_reply_unlock_ticket(42, 'victim@example.com'),
    'A forged remember-mail cookie alone must not authorize reply-only content.');

bold_test_true(bold_issue_reply_unlock_ticket(42, 4201, ' Owner@Example.com '),
    'A successful anonymous comment must receive a signed reply ticket.');
$validReplyToken = $_COOKIE['test_' . bold_reply_unlock_cookie_name(42)];
bold_test_true(bold_has_reply_unlock_ticket(42, 'owner@example.COM'),
    'The signed reply ticket must accept the same normalized email and article.');
bold_test_same(4201, bold_reply_unlock_comment_id(42, 'owner@example.com'),
    'A valid ticket must identify the exact submitted comment.');

$_COOKIE['test_' . bold_reply_unlock_cookie_name(42)] = $validReplyToken . 'tampered';
bold_test_true(!bold_has_reply_unlock_ticket(42, 'owner@example.com'),
    'A tampered reply ticket must be rejected.');

list($validCoid, $validSignedPart) = explode(':', $validReplyToken, 2);
$_COOKIE['test_' . bold_reply_unlock_cookie_name(42)] = '4202:' . $validSignedPart;
bold_test_true(!bold_has_reply_unlock_ticket(42, 'owner@example.com'),
    'The signed ticket must reject a substituted comment ID.');

$_COOKIE['test_' . bold_reply_unlock_cookie_name(43)] = $validReplyToken;
bold_test_true(!bold_has_reply_unlock_ticket(43, 'owner@example.com'),
    'A reply ticket copied to another article must be rejected.');

$_COOKIE['test_' . bold_reply_unlock_cookie_name(42)] = $validReplyToken;
bold_test_true(!bold_has_reply_unlock_ticket(42, 'other@example.com'),
    'A reply ticket must be rejected when the remembered email changes.');

require_once dirname(__DIR__) . '/inc/content.php';

$inlineArchive = new BoldTestArchive(55, array('single', 'post'));
$inlineContent = 'before{password:block-secret}INLINE_SECRET{/password}after';
$blockStart = strpos($inlineContent, '{password:');
$blockMaterial = '55|' . $blockStart . '|block-secret';
$blockId = substr(hash_hmac('sha256', $blockMaterial, getBoldSecretSalt()), 0, 12);
$inlineField = 'inline_password_' . $blockId;
$inlineCookie = 'bold_inline_verified_' . $blockId;

unset($_COOKIE['test_' . bold_password_csrf_cookie_name()]);
unset($_COOKIE['test_' . $inlineCookie]);
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = array($inlineField => 'block-secret');
$directInlineOutput = parseInlinePasswordContent($inlineContent, $inlineArchive);
bold_test_true(strpos($directInlineOutput, 'INLINE_SECRET') === false,
    'An inline password POST without a CSRF cookie/token must remain locked.');
bold_test_true(!isset($_COOKIE['test_' . $inlineCookie]),
    'A rejected inline password POST must not issue an unlock ticket.');

$_SERVER['REQUEST_METHOD'] = 'GET';
$_POST = array();
$inlineForm = parseInlinePasswordContent($inlineContent, $inlineArchive);
bold_test_true((bool) preg_match('/name="bold_password_csrf" value="([^"]+)"/',
    $inlineForm, $inlineTokenMatch),
    'The inline password form must include a CSRF token.');
$inlineToken = $inlineTokenMatch[1];
bold_test_true(bold_validate_password_csrf(
    $inlineToken,
    bold_password_csrf_context($inlineArchive, 'inline', $blockId)
), 'The inline form token must be bound to the article and protected block.');

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = array(
    $inlineField => 'block-secret',
    'bold_password_csrf' => $inlineToken
);
$unlockedInlineOutput = parseInlinePasswordContent($inlineContent, $inlineArchive);
bold_test_true(strpos($unlockedInlineOutput, 'INLINE_SECRET') !== false,
    'A valid inline CSRF token and password must unlock the protected block.');
bold_test_true(bold_check_unlock_token(
    $_COOKIE['test_' . $inlineCookie] ?? '',
    'block-secret'
), 'A successful inline unlock must issue its block-scoped ticket.');

fwrite(STDOUT, "password security regression tests passed\n");
