<?php
define('__TYPECHO_ROOT_DIR__', dirname(__DIR__));
require_once dirname(__DIR__) . '/inc/helpers.php';

class Helper {
    public static $options;
    public static function options() { return self::$options; }
}

class Typecho_Request {
    public static $instance;
    public $requestUri = '/';
    public static function getInstance() {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }
    public function getRequestUri() { return $this->requestUri; }
}

class BoldTestQuery {
    public $limitValue = null;
    public function select(...$args) { return $this; }
    public function from($table) { return $this; }
    public function where(...$args) { return $this; }
    public function order(...$args) { return $this; }
    public function limit($limit) { $this->limitValue = intval($limit); return $this; }
}

class Typecho_Db {
    const SORT_ASC = 'ASC';
    const SORT_DESC = 'DESC';
    public static $instance;
    public $fetchQueue = array();
    public $queries = array();
    public static function get() {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }
    public function select(...$args) { return new BoldTestQuery(); }
    public function fetchAll($query) {
        $this->queries[] = $query;
        return array_shift($this->fetchQueue) ?: array();
    }
}

class Typecho_Widget {
    public static function widget($name) {
        return new class {
            public function push($row) {
                $row['permalink'] = '/post/' . intval($row['cid']);
                return $row;
            }
        };
    }
}

class Typecho_Router {
    public static $routes = array(
        'index_page' => array('format' => '/page/%s/', 'params' => array('page')),
        'search_page' => array('format' => '/search/%s/%s/', 'params' => array('keywords', 'page')),
    );
    public static function get($name) { return self::$routes[$name] ?? null; }
    public static function url($name, $value = null, $prefix = null) {
        $route = self::$routes[$name];
        $pattern = array();
        foreach ($route['params'] as $row) {
            $pattern[$row] = $value[$row] ?? '{' . $row . '}';
        }
        return rtrim($prefix ?? '', '/') . vsprintf($route['format'], $pattern);
    }
}

function get_theme_text($key, $archive = null) { return '[' . $key . ']'; }
function bold_cid_is_protected($cid) {
    return in_array(intval($cid), $GLOBALS['bold_test_protected_cids'] ?? array(), true);
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
    'timezone' => 0,
    'time' => 2000,
    'siteUrl' => 'https://example.test/blog/',
);

Typecho_Request::getInstance()->requestUri = '/blog/category/dev/?page=2&utm_source=ignored';
$canonicalArchive = new class {
    public $options;
    public function __construct() { $this->options = Helper::options(); }
    public function is($type) { return false; }
    public function getArchiveUrl() { return 'https://example.test/blog/category/dev/?ref=ignored#top'; }
};
bold_test_same('https://example.test/blog/category/dev/?page=2', bold_canonical_url($canonicalArchive),
    'Canonical URLs must prefer the core archive URL and preserve only page=N.');

Typecho_Request::getInstance()->requestUri = '/blog/search/term/?q=term&page=3';
$fallbackArchive = new class {
    public $options;
    public function __construct() { $this->options = Helper::options(); }
    public function is($type) { return false; }
};
bold_test_same('https://example.test/blog/search/term/?page=3', bold_canonical_url($fallbackArchive),
    'Canonical fallback must use site origin so a subdirectory is not duplicated.');

$categories = array(
    array('mid' => 30, 'order' => 1, 'slug' => 'later-mid', 'parent' => 0),
    array('mid' => 20, 'order' => 2, 'slug' => 'later-order', 'parent' => 0),
    array('mid' => 10, 'order' => 1, 'slug' => 'primary', 'parent' => 0),
);

$sorted = bold_sort_categories($categories);
bold_test_same(array(10, 30, 20), array_column($sorted, 'mid'),
    'Categories must be sorted by order and then MID.');
bold_test_same(10, bold_primary_category($categories)['mid'],
    'The first sorted category must be used as the primary category.');

$categoryByMid = array(
    1 => array('mid' => 1, 'slug' => 'root', 'parent' => 0),
    2 => array('mid' => 2, 'slug' => 'child', 'parent' => 1),
    3 => array('mid' => 3, 'slug' => 'leaf', 'parent' => 2),
);
bold_test_same(array('root', 'child', 'leaf'),
    bold_category_directory_slugs($categoryByMid[3], $categoryByMid),
    'Category directories must run from the root to the primary category.');

$missingParent = array('mid' => 4, 'slug' => 'orphan', 'parent' => 99);
bold_test_same(array('orphan'), bold_category_directory_slugs($missingParent, $categoryByMid),
    'A missing parent must not remove the primary category.');

$cycle = array(
    7 => array('mid' => 7, 'slug' => 'cycle-a', 'parent' => 8),
    8 => array('mid' => 8, 'slug' => 'cycle-b', 'parent' => 7),
);
bold_test_same(array('cycle-a'),
    bold_category_directory_slugs($cycle[7], $cycle),
    'A category cycle must fall back to the primary category only.');

bold_test_same(array(), bold_category_directory_slugs(null, $categoryByMid),
    'Posts without categories must use an empty directory.');

$mathArchive = new class {
    public $text = '';
    public function is($type) { return $type === 'post'; }
};
foreach (array('$9', '$9 to $29', 'Save \\$5 today', 'USD $29.00') as $priceText) {
    $mathArchive->text = $priceText;
    bold_test_same(false, bold_page_has_math($mathArchive),
        'Currency text must not trigger MathJax: ' . $priceText);
}
foreach (array('$x$', '$x_i$', '$E=mc^2$', '$$x + y$$', "$$\nx + y\n$$", '\\(x + y\\)', '\\begin{align}x&=y\\end{align}') as $mathText) {
    $mathArchive->text = $mathText;
    bold_test_same(true, bold_page_has_math($mathArchive),
        'Math delimiters must trigger MathJax: ' . $mathText);
}

$db = Typecho_Db::get();
$db->fetchQueue = array(
    array(array('cid' => 2), array('cid' => 3), array('cid' => 4), array('cid' => 5)),
    array(
        array('cid' => 2, 'title' => 'PRIVATE TITLE', 'created' => 1900),
        array('cid' => 3, 'title' => 'Public A', 'created' => 1800),
        array('cid' => 4, 'title' => 'PRIVATE TITLE TWO', 'created' => 1700),
        array('cid' => 5, 'title' => 'Public B', 'created' => 1600),
    ),
);
$GLOBALS['bold_test_protected_cids'] = array(2, 4);
$relatedArchive = new class {
    public $cid = 1;
    public $tags = array(array('mid' => 10));
};
ob_start();
getRelatedPosts($relatedArchive, 2);
$relatedHtml = ob_get_clean();
bold_test_same(false, strpos($relatedHtml, 'PRIVATE TITLE') !== false,
    'Related posts must not expose protected titles.');
bold_test_same(2, substr_count($relatedHtml, '<li>'),
    'Related posts must overfetch and fill the requested public result limit.');
bold_test_same(100, $db->queries[1]->limitValue,
    'Related post content candidates must be overfetched before privacy filtering.');

$firstAdjacentBatch = array();
for ($cid = 100; $cid < 150; $cid++) {
    $firstAdjacentBatch[] = array('cid' => $cid, 'title' => 'Private ' . $cid, 'created' => 1000 - $cid);
}
$db->fetchQueue = array(
    $firstAdjacentBatch,
    array(array('cid' => 151, 'title' => 'Public Previous', 'created' => 849)),
);
$GLOBALS['bold_test_protected_cids'] = range(100, 149);
$adjacentArchive = new class { public $cid = 50; public $created = 1000; };
bold_test_same(
    array('title' => 'Public Previous', 'permalink' => '/post/151'),
    bold_get_adjacent_public_post($adjacentArchive, 'previous'),
    'Adjacent navigation must continue past a full batch of protected posts.'
);
bold_test_same(null, bold_get_adjacent_public_post($adjacentArchive, 'sideways'),
    'Unknown adjacent navigation directions must fail closed.');

Helper::$options->index = 'https://example.test/blog';
function bold_test_page_archive($type, $pageRow, $total, $currentPage) {
    return new class($type, $pageRow, $total, $currentPage) {
        public $parameter;
        public $_currentPage;
        private $pageRow;
        private $total;
        public function __construct($type, $pageRow, $total, $currentPage) {
            $this->parameter = (object) array('type' => $type, 'pageSize' => 5);
            $this->pageRow = $pageRow;
            $this->total = $total;
            $this->_currentPage = $currentPage;
        }
        public function getPageRow() { return $this->pageRow; }
        public function getTotal() { return $this->total; }
        public function pageLink($word, $page) { echo '<a class="' . $page . '">' . $word . '</a>'; }
    };
}

bold_test_same('https://example.test/blog/page/{page}/',
    bold_page_url_template(bold_test_page_archive('index', array(), 20, 1)),
    'Index pagination template must keep the {page} placeholder.');
bold_test_same('https://example.test/blog/search/a%20b/{page}/',
    bold_page_url_template(bold_test_page_archive('search_page', array('keywords' => 'a%20b', 'page' => 3), 20, 3)),
    'Paged archives must reuse the page row but drop the concrete page number.');
bold_test_same('', bold_page_url_template(bold_test_page_archive('unknown', array(), 20, 1)),
    'Unknown routes must fall back to plain page text.');

ob_start();
bold_render_pagination(bold_test_page_archive('index', array(), 20, 2));
$paginationHtml = ob_get_clean();
bold_test_same(true, strpos($paginationHtml, 'data-page-template="https://example.test/blog/page/{page}/"') !== false
    && strpos($paginationHtml, 'data-total-pages="4"') !== false
    && strpos($paginationHtml, 'value="2"') !== false,
    'Multi-page listings must render the page jump form.');

ob_start();
bold_render_pagination(bold_test_page_archive('index', array(), 3, 1));
$singlePageHtml = ob_get_clean();
bold_test_same(false, strpos($singlePageHtml, '<form'), 'Single-page listings must not render a page jump form.');
bold_test_same(true, strpos($singlePageHtml, '[page] 1 / 1') !== false, 'Single-page listings keep the static page label.');

ob_start();
bold_render_pagination(bold_test_page_archive('index', array(), 0, 1));
bold_test_same('', ob_get_clean(), 'Empty listings must not render pagination.');

fwrite(STDOUT, "helpers regression tests passed\n");
