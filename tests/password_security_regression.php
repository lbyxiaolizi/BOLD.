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
    public static function getInstance() { return new self(); }
    public function isSecure() { return true; }
}

class BoldTestUser {
    public $role = 'visitor';
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
    'passwordProtectedCategories' => '',
    'categoryPasswords' => ''
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

$archive = new class {
    public $cid = 42;
    public $fields;
    public function __construct() {
        $this->fields = new BoldTestConfig(array('password' => 'field-secret'));
    }
};
bold_test_same('field-secret', getRequiredPassword($archive),
    'A custom-field password must be recognized outside single/feed views.');

Typecho_Widget::$user = new BoldTestUser();
Typecho_Widget::$user->role = 'subscriber';
bold_test_true(!isPasswordVerified($archive),
    'A logged-in subscriber must not bypass a protected entry.');
Typecho_Widget::$user->role = 'editor';
bold_test_true(isPasswordVerified($archive),
    'An editor must retain the configured privileged bypass.');

$cookieToken = bold_make_unlock_token('cookie password');
bold_test_true(bold_set_unlock_cookie('bold_test_unlock', $cookieToken, time() + 60),
    'The hardened cookie helper must accept a normal unlock cookie.');
bold_test_same($cookieToken, $_COOKIE['test_bold_test_unlock'] ?? null,
    'The hardened cookie helper must synchronize the current request cookie state.');

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

fwrite(STDOUT, "password security regression tests passed\n");
