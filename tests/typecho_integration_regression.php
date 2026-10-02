<?php
// Use an unpacked official Typecho 1.2.1 checkout; no production database is used.
$typechoRoot = getenv('BOLD_TYPECHO_ROOT');
if (!$typechoRoot || !is_file($typechoRoot . '/var/Typecho/Common.php')) {
    fwrite(STDERR, "Set BOLD_TYPECHO_ROOT to an official Typecho 1.2.1 checkout.\n");
    exit(1);
}
define('__TYPECHO_ROOT_DIR__', $typechoRoot);
define('BOLD_UNLOCK_TTL', 604800);
require $typechoRoot . '/var/Typecho/Common.php';
// Keep the namespaced core base separate from the legacy user-widget test double.
require $typechoRoot . '/var/Typecho/Widget.php';

class Helper {
    public static $options;
    public static function options() { return self::$options; }
}
class Typecho_Request {
    public static $path = '/post/501';
    public static function getInstance() { return new self(); }
    public function getPathInfo() { return self::$path; }
    public function isSecure() { return true; }
}
class Typecho_Router { public static $current = 'index'; }
class Typecho_Widget {
    public static $uid = 0;
    public static function widget($name) {
        return new class {
            public $uid;
            public function __construct() { $this->uid = Typecho_Widget::$uid; }
            public function hasLogin() { return $this->uid > 0; }
            public function pass($role, $strict = false) { return false; }
        };
    }
}
class Typecho_Cookie {
    public static function getPrefix() { return 'test_'; }
    public static function getPath() { return '/'; }
    public static function getDomain() { return ''; }
    public static function getSecure() { return false; }
    public static function get($key, $default = null) { return $_COOKIE['test_' . $key] ?? $default; }
}
function get_theme_text($key, $archive = null) { return '[' . $key . ']'; }

require dirname(__DIR__) . '/inc/password.php';
require dirname(__DIR__) . '/inc/content.php';
require dirname(__DIR__) . '/inc/helpers.php';

class SourceArchive {
    public $cid = 501;
    public $authorId = 99;
    public $text;
    public $isMarkdown = true;
    public $fields;
    public $categories = array();
    public $response;
    private $description;
    public function __construct($text) {
        $this->text = $text;
        $this->fields = new Typecho\Config(array('password' => ''));
        $this->response = new class {
            public $target;
            public function redirect($target) { $this->target = $target; }
        };
    }
    public function is($type) { return $type === 'single' || $type === 'post'; }
    public function markdown($source) { return Utils\Markdown::convert($source); }
    public function autoP($source) { return (new Utils\AutoP())->parse($source); }
    public function getDescription() { return $this->description; }
    public function setDescription($value) { $this->description = $value; }
    public function originalContent() {
        return $this->isMarkdown ? $this->markdown($this->text) : $this->autoP($this->text);
    }
    public function pageContent() {
        // Same hook/legacy-template order as Typecho + post.php.
        $content = bold_content_filter($this->originalContent(), $this);
        return parseReplyContent(parseInlinePasswordContent($content, $this), $this);
    }
}

function integration_same($expected, $actual, $message) {
    if ($expected === $actual) return;
    throw new RuntimeException($message . "\nExpected: " . var_export($expected, true)
        . "\nActual: " . var_export($actual, true));
}
function integration_public($html, $message) {
    integration_same(false, strpos($html, 'PRIVATE_') !== false, $message);
    integration_same(true, strpos($html, 'PUBLIC') !== false, $message . ' Public text must remain.');
    integration_same(false, strpos($html, 'BOLDPROTECTED') !== false, 'Placeholders must never reach output.');
}

Helper::$options = (object) array('secret' => 'server-only-test-secret', 'postPassword' => '',
    'passwordProtectedCategories' => '', 'categoryPasswords' => '', 'protectFeed' => '1');
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/post/501';
ob_start();

$fixtureFile = tempnam(sys_get_temp_dir(), 'bold-fields-regression-');
try {
    $db = new Typecho\Db('Pdo_SQLite', 'typecho_');
    $db->addServer(array('file' => $fixtureFile), Typecho\Db::READ | Typecho\Db::WRITE);
    Typecho\Db::set($db);
    $db->query('CREATE TABLE typecho_fields (cid INTEGER, name TEXT, type TEXT, str_value TEXT, int_value INTEGER, float_value REAL)', Typecho\Db::WRITE);
    $db->query('CREATE TABLE typecho_comments (coid INTEGER, cid INTEGER, authorId INTEGER, mail TEXT, status TEXT)', Typecho\Db::WRITE);

    $protectedSources = array(
        "PUBLIC\n\n{hide}\nPRIVATE_BODY[^a]\n\n[^a]: PRIVATE_FOOTNOTE\n{/hide}\n\nPUBLIC_TAIL",
        "PUBLIC\n\n{password:pw}\nPRIVATE_BODY[^a]\n\n[^a]: PRIVATE_FOOTNOTE\n{/password}\n\nPUBLIC_TAIL",
        "PUBLIC [file][ref]\n\n{hide}\n[ref]: https://example.test/PRIVATE_LINK\n{/hide}",
        "PUBLIC\n\n{hide}\n{password:pw}\nPRIVATE_BODY[^a]\n\n[^a]: PRIVATE_FOOTNOTE\n{/password}\n{/hide}",
        "PUBLIC\n\n{hide}\nPRIVATE_BODY[^a]\n\n[^a]: PRIVATE_FOOTNOTE",
    );
    foreach ($protectedSources as $source) {
        $archive = new SourceArchive($source);
        handlePasswordVerification($archive);
        integration_public($archive->pageContent(), 'Anonymous HTML must suppress protected body/footnotes/references.');
        Typecho_Request::$path = '/feed/archives/501/';
        foreach (array('1', '0') as $setting) {
            Helper::$options->protectFeed = $setting;
            integration_public(bold_content_filter($archive->originalContent(), $archive), 'Feed body must suppress protected descendants.');
            integration_public(bold_excerpt_filter($archive->originalContent(), $archive), 'Feed excerpts must suppress protected descendants.');
            $archive->setDescription(strip_tags($archive->originalContent()));
            bold_protect_feed_metadata($archive);
            integration_public($archive->getDescription(), 'Feed metadata must suppress relocated footnotes even with whole-post protection off.');
        }
        Typecho_Request::$path = '/post/501';
    }
    Helper::$options->protectFeed = '1';

    $archive = new SourceArchive($protectedSources[0]);
    Typecho_Widget::$uid = 99;
    $html = $archive->pageContent();
    integration_same(true, strpos($html, 'PRIVATE_FOOTNOTE') !== false, 'Authors must retain authorized footnotes.');
    Typecho_Widget::$uid = 0;
    $db->query("INSERT INTO typecho_comments VALUES (5011, 501, 0, 'reader@example.test', 'approved')", Typecho\Db::WRITE);
    bold_issue_reply_unlock_ticket(501, 5011, 'reader@example.test');
    $_COOKIE['test___typecho_remember_mail'] = 'reader@example.test';
    integration_same(true, strpos($archive->pageContent(), 'PRIVATE_FOOTNOTE') !== false, 'A signed approved reply must reveal its footnotes.');
    unset($_COOKIE['test___typecho_remember_mail']);

    $archive = new SourceArchive($protectedSources[1]);
    $form = $archive->pageContent();
    integration_same(true, strpos($form, '<form method="post"') !== false, 'Password forms must remain active HTML after Markdown.');
    integration_same(false, strpos($form, '&lt;form') !== false, 'Password forms must not become escaped code.');
    preg_match('/name="(inline_password_[a-f0-9]+)"/', $form, $fieldMatch);
    preg_match('/name="bold_password_csrf" value="([^"]+)"/', $form, $csrfMatch);
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = array($fieldMatch[1] => 'wrong', 'bold_password_csrf' => $csrfMatch[1]);
    integration_public($archive->pageContent(), 'A wrong inline password must keep footnotes locked.');
    $_POST[$fieldMatch[1]] = 'pw';
    integration_same(true, strpos($archive->pageContent(), 'PRIVATE_FOOTNOTE') !== false, 'A valid password POST must reveal authorized footnotes.');
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_POST = array();
    integration_same(true, strpos($archive->pageContent(), 'PRIVATE_FOOTNOTE') !== false, 'The signed inline ticket must remain accepted after redirect.');
    $archive = new SourceArchive("PUBLIC\n\n{hide}\nPRIVATE_BODY\n{/hide}");
    $archive->isMarkdown = false;
    integration_public($archive->pageContent(), 'Non-Markdown content must also retain its gate.');

    foreach (array('int' => 123456, 'float' => 12.34, 'str' => '0') as $type => $value) {
        $cid = $type === 'int' ? 101 : ($type === 'float' ? 102 : 103);
        $row = array('cid' => $cid, 'name' => 'password', 'type' => $type,
            'str_value' => $type === 'str' ? $value : null,
            'int_value' => $type === 'int' ? $value : 0,
            'float_value' => $type === 'float' ? $value : 0);
        $db->query($db->insert('table.fields')->rows($row), Typecho\Db::WRITE);
        $core = (new ReflectionClass(Widget\Base\Contents::class))->newInstanceWithoutConstructor();
        $rowProperty = new ReflectionProperty(Typecho\Widget::class, 'row');
        if (PHP_VERSION_ID < 80100) $rowProperty->setAccessible(true);
        $rowProperty->setValue($core, array('cid' => $cid));
        $dbProperty = new ReflectionProperty(Widget\Base::class, 'db');
        if (PHP_VERSION_ID < 80100) $dbProperty->setAccessible(true);
        $dbProperty->setValue($core, $db);
        $fieldsMethod = new ReflectionMethod(Widget\Base\Contents::class, '___fields');
        if (PHP_VERSION_ID < 80100) $fieldsMethod->setAccessible(true);
        $fields = $fieldsMethod->invoke($core);
        $archive = new SourceArchive('PRIVATE_BODY');
        $archive->cid = $cid;
        $archive->fields = $fields;
        integration_same(strval($value), bold_entry_password($archive), 'Core typed field values must match entry passwords.');
        integration_same(true, bold_cid_is_protected($cid), 'CID checks must agree with core typed passwords.');
        Typecho_Request::$path = '/feed/comments/';
        integration_same('[feed_protected]', bold_comment_feed_filter('PRIVATE_DISCUSSION', (object) array('cid' => $cid)), 'Typed passwords must redact comment feeds.');
    }
    Typecho_Request::$path = '/post/501';
    integration_same(true, bold_listings_may_vary_by_unlock_cookie(), 'Numeric password fields must also disable shared listing caches.');
    Typecho\Plugin::init(array());
    Typecho_Plugin::factory('Widget_Abstract_Contents')->content = function ($text, $widget) {
        integration_same(false, strpos($text, 'PRIVATE_') !== false, 'Content plugins must receive authorized source.');
        return Utils\Markdown::convert(str_replace('[public-shortcode]', 'PUBLIC_SHORTCODE_RENDERED', $text));
    };
    $archive = new SourceArchive("PUBLIC [public-shortcode]\n\n{hide}PRIVATE_BODY{/hide}");
    integration_same(true, strpos($archive->pageContent(), 'PUBLIC_SHORTCODE_RENDERED') !== false,
        'Source content plugin transformations must remain available on protected posts.');
    Typecho_Plugin::factory('Widget_Abstract_Contents')->excerpt = function ($text, $widget) {
        integration_same(false, strpos($text, 'PRIVATE_') !== false, 'Excerpt plugins must receive authorized source.');
        return Utils\Markdown::convert(str_replace('[public-shortcode]', 'PUBLIC_EXCERPT_RENDERED', $text));
    };
    Typecho_Request::$path = '/feed/archives/501/';
    integration_same(true, strpos(bold_excerpt_filter($archive->originalContent(), $archive), 'PUBLIC_EXCERPT_RENDERED') !== false,
        'Source excerpt plugin transformations must remain available in feeds.');
    ob_end_clean();
    fwrite(STDOUT, "Typecho integration regression tests passed (Markdown, authorization, feed metadata, typed SQLite fields).\n");
} finally {
    unlink($fixtureFile);
}
