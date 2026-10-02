<?php
define('__TYPECHO_ROOT_DIR__', dirname(__DIR__));
define('BOLD_UNLOCK_TTL', 604800);

class Helper {
    public static $options;
    public static function options() { return self::$options; }
}

class Typecho_Cookie {
    public static function getPrefix() { return 'test_'; }
    public static function getPath() { return '/'; }
    public static function getDomain() { return ''; }
    public static function getSecure() { return false; }
    public static function get($key, $default = null) { return $_COOKIE['test_' . $key] ?? $default; }
}

class Typecho_Request {
    public static function getInstance() { return new self(); }
    public function getPathInfo() { return '/category/private/'; }
    public function isSecure() { return true; }
}

class Typecho_Widget {
    public static function widget($name) {
        return new class {
            public function hasLogin() { return false; }
        };
    }
}

class AuditQuery {
    public $table;
    public $conditions = array();
    public function from($table) { $this->table = $table; return $this; }
    public function where($condition, ...$values) {
        $this->conditions[$condition] = $values;
        return $this;
    }
    public function order(...$args) { return $this; }
    public function limit($limit) { return $this; }
    public function join(...$args) { return $this; }
}

class Typecho_Db {
    const SORT_DESC = 'DESC';
    public static $fields = array();
    public static $posts = array();
    public static $queries = array();
    public static function get() { return new self(); }
    public function select(...$columns) { return new AuditQuery(); }
    public function fetchRow($query) {
        $cid = $query->conditions['cid = ?'][0] ?? 0;
        if (!isset(self::$fields[$cid])) return false;
        return is_array(self::$fields[$cid]) ? self::$fields[$cid] : array('type' => 'str', 'str_value' => self::$fields[$cid]);
    }
    public function fetchAll($query) {
        self::$queries[] = $query;
        if ($query->table !== 'table.contents') return array();
        return array_values(array_filter(self::$posts, function ($post) use ($query) {
            return !isset($query->conditions['(password IS NULL OR password = ?)'])
                || ($post['password'] ?? '') === '';
        }));
    }
}

class Typecho_Date {
    public $year = 1970;
    public $month = 1;
    public $day = 1;
    public function __construct($timestamp) {}
}

class Typecho_Router {
    public static $current = 'index';
    public static function url($type, $params, $index) { return $index . $params['cid']; }
}

class AuditArchive {
    public $cid = 0;
    public $fields;
    public $categories = array();
    public $authorId = 99;
    public $created = 1000;
    public $parameter;
    public $response;
    public $_currentPage = 1;
    private $type;
    private $slug;
    public function __construct($type, $slug = '', $password = '') {
        $this->type = $type;
        $this->slug = $slug;
        $this->fields = (object) array('password' => $password);
        $this->parameter = (object) array('pageSize' => 10);
        $this->response = new class {
            public $redirectedTo = null;
            public function redirect($target) { $this->redirectedTo = $target; }
        };
    }
    public function is($type) { return $type === $this->type || ($this->type === 'post' && $type === 'single'); }
    public function getArchiveSlug() { return $this->slug; }
    public function need($file) {}
    public function archiveTitle(...$args) {}
    public function have() { return false; }
    public function getTotal() { return 0; }
    public function renderArchive() {
        ob_start();
        include dirname(__DIR__) . '/archive.php';
        return ob_get_clean();
    }
}

function get_theme_text($key, $archive = null) { return '[' . $key . ']'; }
function _t($text) { return $text; }

require_once dirname(__DIR__) . '/inc/password.php';
require_once dirname(__DIR__) . '/inc/content.php';
require_once dirname(__DIR__) . '/inc/helpers.php';

Helper::$options = (object) array(
    'secret' => 'server-only-test-secret',
    'postPassword' => '',
    'passwordProtectedCategories' => 'private,shared,0',
    'categoryPasswords' => "private:0\n0:zero-slug-password",
    'protectFeed' => '1',
    'requireCategoryArchivePassword' => '0',
    'time' => 2000,
    'index' => 'https://example.test/',
);
$_SERVER['REQUEST_METHOD'] = 'GET';
ob_start();
$failures = array();
function audit_same($expected, $actual, $message) {
    if ($expected !== $actual) $GLOBALS['failures'][] = $message;
}

$ticket = bold_make_unlock_token('0');
audit_same(true, bold_check_unlock_token($ticket, '0'), 'A signed ticket must accept password "0".');
audit_same(false, bold_check_unlock_token($ticket, ''), 'An empty password must remain invalid.');
audit_same(false, bold_check_unlock_token($ticket . 'tampered', '0'), 'A zero-password ticket must reject tampering.');
audit_same('0', bold_category_password('private'), 'Category password "0" must not be discarded.');
audit_same('zero-slug-password', bold_category_password('0'), 'Category slug "0" must remain protected.');

$entry = new AuditArchive('post', '', '0');
$entry->cid = 901;
Typecho_Db::$fields[901] = '0';
audit_same(true, isPasswordProtected($entry), 'An entry password "0" must protect the page.');
$_COOKIE['test_' . bold_entry_unlock_cookie_name(901)] = $ticket;
audit_same(true, isPasswordVerified($entry), 'The matching ticket must unlock a zero-password entry.');
unset($_COOKIE['test_' . bold_entry_unlock_cookie_name(901)]);
handlePasswordVerification($entry);
$csrfToken = bold_password_csrf_token(bold_password_csrf_context($entry, 'page'));
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/post/901';
$_POST = array('bold_password' => '0', 'bold_password_csrf' => $csrfToken);
handlePasswordVerification($entry);
audit_same('/post/901', $entry->response->redirectedTo, 'A zero-password POST must redirect after unlocking.');
audit_same(true, isPasswordVerified($entry), 'A zero-password POST must issue an accepted entry ticket.');
$_SERVER['REQUEST_METHOD'] = 'GET';
$_POST = array();

$inlineContent = 'before{password:0}INLINE_SECRET{/password}after';
$blockId = substr(hash_hmac('sha256', '901|6|0', getBoldSecretSalt()), 0, 12);
$inlineContext = bold_password_csrf_context($entry, 'inline', $blockId);
$_POST = array('inline_password_' . $blockId => '0', 'bold_password_csrf' => bold_password_csrf_token($inlineContext));
$_SERVER['REQUEST_METHOD'] = 'POST';
audit_same(true, strpos(parseInlinePasswordContent($inlineContent, $entry), 'INLINE_SECRET') !== false,
    'A zero-password inline POST must reveal its protected block.');
$_SERVER['REQUEST_METHOD'] = 'GET';
$_POST = array();
audit_same(true, strpos(parseInlinePasswordContent($inlineContent, $entry), 'INLINE_SECRET') !== false,
    'A zero-password inline ticket must remain accepted on subsequent requests.');

audit_same(true, bold_cid_is_protected(901), 'CID privacy checks must recognize field password "0".');
foreach (array('int' => 123456, 'float' => 12.34) as $type => $value) {
    $cid = $type === 'int' ? 904 : 905;
    Typecho_Db::$fields[$cid] = array('type' => $type, 'str_value' => null, $type . '_value' => $value);
    $typedEntry = new AuditArchive('post', '', $value);
    $typedEntry->cid = $cid;
    audit_same(true, isPasswordProtected($typedEntry), 'Typed field passwords must protect the page: ' . $type);
    audit_same(true, bold_cid_is_protected($cid), 'Typed field passwords must also protect CID exports: ' . $type);
}
Typecho_Router::$current = 'feed';
$comment = (object) array('cid' => 901);
audit_same('[feed_protected]', bold_comment_feed_filter('PRIVATE_DISCUSSION', $comment),
    'Comment feeds must redact discussions on a zero-password entry.');
foreach (array(904, 905) as $cid) {
    audit_same('[feed_protected]', bold_comment_feed_filter('PRIVATE_DISCUSSION', (object) array('cid' => $cid)),
        'Comment feeds must redact discussions on typed-password entries.');
}
Typecho_Db::$fields[902] = '';
audit_same('PUBLIC_DISCUSSION', bold_comment_feed_filter('PUBLIC_DISCUSSION', (object) array('cid' => 902)),
    'Comment feeds must preserve public discussions.');
Typecho_Router::$current = 'index';

Helper::$options->postPassword = '0';
audit_same('0', bold_category_password('shared'), 'A protected category must inherit global password "0".');
audit_same(true, bold_cid_is_protected(903), 'CID privacy checks must recognize global password "0".');
Helper::$options->postPassword = '';

$category = new AuditArchive('category', 'private');
foreach (array('0', 0, '1', 1, null) as $setting) {
    Helper::$options->requireCategoryArchivePassword = $setting;
    $html = $category->renderArchive();
    audit_same($setting !== '0' && $setting !== 0,
        strpos($html, 'password-form-container') !== false,
        'Archive password toggle must distinguish disabled from unset: ' . var_export($setting, true));
}

Typecho_Db::$posts = array(
    array('cid' => 1, 'title' => 'PUBLIC_EMPTY_PASSWORD', 'slug' => 'public', 'created' => 1000, 'password' => ''),
    array('cid' => 2, 'title' => 'NATIVE_PRIVATE_TITLE', 'slug' => 'private', 'created' => 1000, 'password' => 'secret'),
    array('cid' => 3, 'title' => 'NATIVE_ZERO_PRIVATE_TITLE', 'slug' => 'zero', 'created' => 1000, 'password' => '0'),
    array('cid' => 4, 'title' => 'PUBLIC_NULL_PASSWORD', 'slug' => 'public-null', 'created' => 1000, 'password' => null),
);
Typecho_Db::$queries = array();
audit_same(array('PUBLIC_EMPTY_PASSWORD', 'PUBLIC_NULL_PASSWORD'), array_column(bold_timeline_posts(), 'title'),
    'Timeline must exclude native password posts and retain public empty/null password posts.');
audit_same(array(''), Typecho_Db::$queries[0]->conditions['(password IS NULL OR password = ?)'] ?? null,
    'Timeline must filter native passwords in SQL before reading titles.');

ob_end_clean();
if ($failures) {
    fwrite(STDERR, implode("\n", $failures) . "\n");
    exit(1);
}
fwrite(STDOUT, "audit regression tests passed\n");
