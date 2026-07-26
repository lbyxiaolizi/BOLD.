<?php
define('__TYPECHO_ROOT_DIR__', dirname(__DIR__));
require_once dirname(__DIR__) . '/inc/helpers.php';

function bold_test_same($expected, $actual, $message) {
    if ($expected === $actual) {
        return;
    }

    fwrite(STDERR, $message . "\nExpected: " . var_export($expected, true)
        . "\nActual:   " . var_export($actual, true) . "\n");
    exit(1);
}

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

fwrite(STDOUT, "helpers regression tests passed\n");
