<?php
define('__TYPECHO_ROOT_DIR__', dirname(__DIR__));
define('BOLD_UNLOCK_TTL', 604800);

class Helper {
    public static $options;
    public static function options() { return self::$options; }
}

class Typecho_Router {
    public static $current = 'index';
}

class Typecho_Request {
    private static $instance;
    public static function getInstance() {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }
    public function getPathInfo() { return '/post'; }
    public function isSecure() { return true; }
}

class Typecho_Cookie {
    public static function getPrefix() { return 'test_'; }
    public static function getPath() { return '/'; }
    public static function getDomain() { return ''; }
    public static function getSecure() { return false; }
    public static function get($key, $default = null) {
        return $_COOKIE['test_' . $key] ?? $default;
    }
}

class BoldTestUser {
    public $uid = 0;
    public function hasLogin() { return false; }
}

class Typecho_Widget {
    public static function widget($name) { return new BoldTestUser(); }
}

class BoldTestQuery {
    public $conditions = array();
    public function from($table) { return $this; }
    public function where($condition, $value) {
        $this->conditions[$condition] = $value;
        return $this;
    }
    public function limit($limit) { return $this; }
}

class Typecho_Db {
    public static $comments = array();
    public static function get() { return new self(); }
    public function select() { return new BoldTestQuery(); }
    public function fetchRow($query) {
        $coid = intval($query->conditions['coid = ?'] ?? 0);
        $cid = intval($query->conditions['cid = ?'] ?? 0);
        $mail = strval($query->conditions['mail = ?'] ?? '');
        $status = strval($query->conditions['status = ?'] ?? '');
        $comment = self::$comments[$coid] ?? null;
        return $comment && $comment['cid'] === $cid && $comment['mail'] === $mail
            && $comment['status'] === $status ? $comment : false;
    }
}

function get_theme_text($key, $archive = null) { return '[' . $key . ']'; }

Helper::$options = (object) array(
    'siteUrl' => 'https://example.test/',
    'secret' => 'server-only-random-secret',
    'postPassword' => '',
    'passwordProtectedCategories' => '',
    'categoryPasswords' => ''
);

require_once dirname(__DIR__) . '/inc/password.php';
require_once dirname(__DIR__) . '/inc/content.php';
require_once dirname(__DIR__) . '/inc/comments.php';

function bold_test_visible($expected, $content, $message) {
    $visible = strpos($content, 'REPLY_SECRET') !== false;
    if ($visible === $expected) return;
    fwrite(STDERR, $message . "\nOutput: " . $content . "\n");
    exit(1);
}

class BoldTestArchive {
    public $cid;
    public $authorId = 99;
    public function __construct($cid) { $this->cid = $cid; }
    public function is($type) { return $type === 'single'; }
}

$protected = 'before{hide}REPLY_SECRET{/hide}after';
Typecho_Db::$comments = array(
    4201 => array('coid' => 4201, 'cid' => 42, 'mail' => 'owner@example.com', 'status' => 'approved'),
    4202 => array('coid' => 4202, 'cid' => 42, 'mail' => 'other@example.com', 'status' => 'approved'),
    4203 => array('coid' => 4203, 'cid' => 42, 'mail' => 'owner@example.com', 'status' => 'waiting'),
    4301 => array('coid' => 4301, 'cid' => 43, 'mail' => 'owner@example.com', 'status' => 'approved'),
    4401 => array('coid' => 4401, 'cid' => 44, 'mail' => 'pending@example.com', 'status' => 'waiting')
);

// 客户端可以任意伪造 Typecho 的记忆邮箱，但没有服务器票据仍应锁定。
$_COOKIE['test___typecho_remember_mail'] = 'owner@example.com';
unset($_COOKIE['test_' . bold_reply_unlock_cookie_name(42)]);
bold_test_visible(false, parseReplyContent($protected, new BoldTestArchive(42)),
    'A forged remember-mail cookie must not unlock reply-only content.');

$feedback = (object) array('cid' => 42, 'coid' => 4201, 'mail' => 'Owner@Example.com', 'authorId' => 0);
ThemeHooks::rememberReplyAuthorization($feedback, null);
$_COOKIE['test___typecho_remember_mail'] = 'owner@example.com';
bold_test_visible(true, parseReplyContent($protected, new BoldTestArchive(42)),
    'An approved comment plus a valid signed ticket must unlock the matching article.');

$validToken = $_COOKIE['test_' . bold_reply_unlock_cookie_name(42)];
$_COOKIE['test_' . bold_reply_unlock_cookie_name(42)] = $validToken . 'tampered';
bold_test_visible(false, parseReplyContent($protected, new BoldTestArchive(42)),
    'A tampered signed ticket must not unlock reply-only content.');

$_COOKIE['test_' . bold_reply_unlock_cookie_name(43)] = $validToken;
bold_test_visible(false, parseReplyContent($protected, new BoldTestArchive(43)),
    'A signed ticket must not cross article boundaries.');

$_COOKIE['test_' . bold_reply_unlock_cookie_name(42)] = $validToken;
$_COOKIE['test___typecho_remember_mail'] = 'other@example.com';
bold_test_visible(false, parseReplyContent($protected, new BoldTestArchive(42)),
    'A signed ticket must not authorize a different remembered email.');

ThemeHooks::rememberReplyAuthorization(
    (object) array('cid' => 42, 'coid' => 4203, 'mail' => 'owner@example.com', 'authorId' => 0),
    null
);
$_COOKIE['test___typecho_remember_mail'] = 'owner@example.com';
bold_test_visible(false, parseReplyContent($protected, new BoldTestArchive(42)),
    'A new pending comment must not piggyback an older approved comment with the same email.');

ThemeHooks::rememberReplyAuthorization(
    (object) array('cid' => 44, 'coid' => 4401, 'mail' => 'pending@example.com', 'authorId' => 0),
    null
);
$_COOKIE['test___typecho_remember_mail'] = 'pending@example.com';
bold_test_visible(false, parseReplyContent($protected, new BoldTestArchive(44)),
    'A valid submission ticket must remain locked until the comment is approved.');

Typecho_Db::$comments[4401]['status'] = 'approved';
bold_test_visible(true, parseReplyContent($protected, new BoldTestArchive(44)),
    'The same signed submission ticket must activate after that exact comment is approved.');

fwrite(STDOUT, "reply unlock regression tests passed\n");
