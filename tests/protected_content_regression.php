<?php
define('__TYPECHO_ROOT_DIR__', dirname(__DIR__));

class Typecho_Router {
    public static $current = 'index';
}

class Typecho_Request {
    private static $instance;
    public $path = '/';

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPathInfo() {
        return $this->path;
    }
}

class Typecho_Widget {
    public static function widget($name) {
        return new class {
            public $uid = 0;
            public function hasLogin() { return false; }
        };
    }
}

class Typecho_Cookie {
    public static function get($name) { return null; }
}

class Typecho_Db {
    public static function get() { return new self(); }
}

class Helper {
    public static function options() { return (object) array('protectFeed' => '1'); }
}

function bold_private_cache_headers() {}
function get_theme_text($key, $archive = null) { return '[' . $key . ']'; }
function isPasswordProtected($archive) { return !empty($archive->protected); }
function getBoldSecretSalt() { return 'test-only-server-secret'; }
function bold_check_unlock_token($token, $password) { return false; }
function bold_make_unlock_token($password) { return 'unused'; }
function bold_set_unlock_cookie($key, $value, $expires) { return false; }
function bold_redirect_after_unlock($archive) {}

require_once dirname(__DIR__) . '/inc/content.php';

function bold_test_same($expected, $actual, $message) {
    if ($expected === $actual) {
        return;
    }
    fwrite(STDERR, $message . "\nExpected: " . var_export($expected, true)
        . "\nActual:   " . var_export($actual, true) . "\n");
    exit(1);
}

$cases = array(
    array('plain text', 'plain text', 'Plain text must remain unchanged.'),
    array('beforeafter', 'before{hide}secret{/hide}after', 'A hide block must be removed.'),
    array('beforeafter', 'before{password:pw}secret{/password}after', 'A password block must be removed.'),
    array('tail', '{hide}A {hide}B{/hide} LEAK{/hide}tail', 'Nested hide blocks must not leak an outer tail.'),
    array('tail', '{password:a}A {password:b}B{/password} C{/password}tail', 'Nested password blocks must be removed as one protected tree.'),
    array('tail', '{hide}A{password:p}B{/password}C{/hide}tail', 'Cross-type nesting must remain protected.'),
    array('SAFE[LOCKED]', 'SAFE{password:pw}SECRET', 'An unclosed password block must fail closed.'),
    array('SAFE[LOCKED]', 'SAFE{hide}SECRET', 'An unclosed hide block must fail closed.'),
    array('SAFE[LOCKED]', 'SAFE{hide}SECRET{/password}LEAK{/hide}', 'A mismatched close must fail at the outer opening.'),
    array('SAFE[LOCKED]', 'SAFE{/hide}LEAK', 'An orphan close must fail closed.'),
    array('SAFE[LOCKED]', 'SAFE{hideoops}LEAK', 'A malformed marker must fail closed.'),
    array('SAFE[LOCKED]', 'SAFE{password: }LEAK{/password}', 'An empty password must fail closed.'),
);

foreach ($cases as $case) {
    bold_test_same($case[0], bold_strip_protected_markers($case[1], '[LOCKED]'), $case[2]);
}

$deep = '';
for ($depth = 0; $depth < 40; $depth++) {
    $deep .= $depth % 2 === 0 ? '{hide}' : '{password:p' . $depth . '}';
}
$deep .= 'DEEP_SECRET';
for ($depth = 39; $depth >= 0; $depth--) {
    $deep .= $depth % 2 === 0 ? '{/hide}' : '{/password}';
}
$deep .= 'tail';
bold_test_same('tail', bold_strip_protected_markers($deep, '[LOCKED]'),
    'Deep mixed nesting must be stripped without exposing descendants.');

$archive = new class {
    public $cid = 7;
    public $authorId = 99;
    public function is($type) { return $type === 'single'; }
};
$hideOnly = 'before{hide}secret{/hide}after';
bold_test_same($hideOnly, parseInlinePasswordContent($hideOnly, $archive),
    'Inline-password parsing must preserve hide-only content for reply parsing.');

$replyLocked = parseReplyContent($hideOnly, $archive);
bold_test_same(false, strpos($replyLocked, 'secret') !== false,
    'Reply parsing must not render a locked hide block.');

$passwordLocked = parseInlinePasswordContent(
    'before{password:pw}PASSWORD_SECRET{/password}after',
    $archive
);
bold_test_same(false, strpos($passwordLocked, 'PASSWORD_SECRET') !== false,
    'Inline-password parsing must not render locked descendants.');

$nestedLocked = parseInlinePasswordContent(
    '{password:outer}OUTER{password:inner}INNER{/password}TAIL{/password}',
    $archive
);
bold_test_same(false, strpos($nestedLocked, 'OUTER') !== false || strpos($nestedLocked, 'INNER') !== false
    || strpos($nestedLocked, 'TAIL') !== false,
    'A locked outer password block must suppress its entire nested subtree.');

Typecho_Request::getInstance()->path = '/normal/post';
bold_test_same(false, bold_is_feed(), 'A normal request must not be classified as a feed.');
Typecho_Request::getInstance()->path = '/feed/atom/category/private/';
bold_test_same(true, bold_is_feed(), 'Atom/category feed paths must be detected after Router state changes.');
Typecho_Request::getInstance()->path = '/feed/comments/';
bold_test_same(true, bold_is_feed(), 'The global comments feed must be detected.');

$feedWidget = new class {
    public function getFeed() { return new stdClass(); }
};
Typecho_Request::getInstance()->path = '/normal/post';
bold_test_same(true, bold_is_feed($feedWidget), 'An initialized Archive Feed object must take precedence.');

$metadataArchive = new class {
    public $protected = false;
    private $description = 'public {hide}CHANNEL_SECRET{/hide} tail';
    public function is($type) { return $type === 'single'; }
    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = $description; }
};
Typecho_Request::getInstance()->path = '/feed/archives/7/';
bold_protect_feed_metadata($metadataArchive);
bold_test_same('public  tail', $metadataArchive->getDescription(),
    'A public single-comment feed channel description must strip protected markers.');

$metadataArchive->protected = true;
$metadataArchive->setDescription('WHOLE_CHANNEL_SECRET');
bold_protect_feed_metadata($metadataArchive);
bold_test_same('[feed_protected]', $metadataArchive->getDescription(),
    'A protected single-comment feed channel description must be replaced completely.');

fwrite(STDOUT, "protected content regression tests passed\n");
