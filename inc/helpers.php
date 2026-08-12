<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 按站点时区（后台设置的 timezone 偏移）格式化 GMT 时间戳。
 * 原生 date() 走 PHP 默认时区，与站点时区不一致时日期会错位。
 */
function bold_site_date($format, $timestamp) {
    return gmdate($format, intval($timestamp) + intval(Helper::options()->timezone));
}

/**
 * 输出带正确时区后缀的 ISO 8601 时间（用于 datetime 属性、OG、JSON-LD）。
 * date('c') 的数字部分与偏移后缀会分属两个时区，此处保证自洽。
 */
function bold_iso8601($timestamp) {
    $offset = intval(Helper::options()->timezone);
    $sign = $offset < 0 ? '-' : '+';
    $abs = abs($offset);
    return gmdate('Y-m-d\TH:i:s', intval($timestamp) + $offset)
        . sprintf('%s%02d:%02d', $sign, intdiv($abs, 3600), intdiv($abs % 3600, 60));
}

/**
 * 把裸邮箱地址补全为 mailto: 链接；已是 URL 或 mailto: 的原样返回
 */
function bold_mailto($value) {
    $value = trim(strval($value));
    if ($value !== '' && strpos($value, '@') !== false
        && stripos($value, 'mailto:') !== 0 && !preg_match('#^[a-z][a-z0-9+.-]*://#i', $value)) {
        return 'mailto:' . $value;
    }
    return $value;
}

/**
 * 生成当前页面 canonical URL。非文章页面优先采用 Typecho 的归档 URL；
 * 回退时只使用站点配置中的 origin 和当前请求路径，避免子目录重复拼接。
 */
function bold_canonical_url($archive) {
    if ($archive->is('post') || $archive->is('page')) {
        ob_start();
        $archive->permalink();
        return trim(ob_get_clean());
    }

    $requestUri = strval(Typecho_Request::getInstance()->getRequestUri());
    $siteUrl = strval($archive->options->siteUrl ?? Helper::options()->siteUrl ?? '');
    $siteParts = parse_url($siteUrl);
    $origin = '';
    if (is_array($siteParts) && !empty($siteParts['host'])) {
        $scheme = strtolower(strval($siteParts['scheme'] ?? 'http'));
        if ($scheme === 'http' || $scheme === 'https') {
            $origin = $scheme . '://' . $siteParts['host'];
            if (!empty($siteParts['port'])) {
                $origin .= ':' . intval($siteParts['port']);
            }
        }
    }

    $archiveUrl = '';
    try {
        if (method_exists($archive, 'getArchiveUrl')) {
            $archiveUrl = trim(strval($archive->getArchiveUrl()));
        }
    } catch (Throwable $e) {
        $archiveUrl = '';
    }

    $archiveParts = $archiveUrl !== '' ? parse_url($archiveUrl) : false;
    if (is_array($archiveParts)) {
        $archiveScheme = strtolower(strval($archiveParts['scheme'] ?? ''));
        if ($archiveScheme !== '' && $archiveScheme !== 'http' && $archiveScheme !== 'https') {
            $archiveParts = false;
        }
    }

    if (is_array($archiveParts)) {
        if (!empty($archiveParts['host'])) {
            $scheme = strtolower(strval($archiveParts['scheme'] ?? 'http'));
            $canonical = $scheme . '://' . $archiveParts['host'];
            if (!empty($archiveParts['port'])) {
                $canonical .= ':' . intval($archiveParts['port']);
            }
            $canonical .= $archiveParts['path'] ?? '/';
        } elseif ($origin !== '') {
            $canonical = rtrim($origin, '/') . '/' . ltrim(strval($archiveParts['path'] ?? '/'), '/');
        } else {
            $canonical = strval($archiveParts['path'] ?? '/');
        }
    } else {
        $requestPath = parse_url($requestUri, PHP_URL_PATH);
        $requestPath = is_string($requestPath) && $requestPath !== '' ? $requestPath : '/';
        $canonical = $origin !== ''
            ? rtrim($origin, '/') . '/' . ltrim($requestPath, '/')
            : $requestPath;
    }

    parse_str(strval(parse_url($requestUri, PHP_URL_QUERY) ?: ''), $queryArgs);
    $page = isset($queryArgs['page']) && is_scalar($queryArgs['page'])
        ? intval($queryArgs['page']) : 0;
    if ($page > 0) {
        $canonical .= '?page=' . $page;
    }

    return $canonical;
}

/**
 * 列表内容是否可能因解锁 Cookie 而不同。配置型密码可直接判断；文章字段
 * 密码只做一次轻量存在性查询。探测失败时按保密优先返回 true。
 */
function bold_listings_may_vary_by_unlock_cookie() {
    static $varies = null;
    if ($varies !== null) {
        return $varies;
    }

    $options = Helper::options();
    if (!empty($options->postPassword) || !empty(bold_get_protected_slugs())) {
        return $varies = true;
    }

    try {
        $field = Typecho_Db::get()->fetchRow(Typecho_Db::get()->select('cid')
            ->from('table.fields')
            ->where('name = ?', 'password')
            ->where('str_value <> ?', '')
            ->limit(1));
        return $varies = !empty($field);
    } catch (Throwable $e) {
        return $varies = true;
    }
}

/**
 * 当前文章/页面是否包含代码块（决定是否加载 Prism）。
 * 原文启发式覆盖 ```/~~~ 围栏与 HTML 写法；缩进式代码块等
 * 测不到的写法由渲染层兜底（bold_content_filter 在渲染结果中
 * 见到 <pre>/<code> 时置 $GLOBALS['bold_has_code']，footer 一并判断）。
 */
function bold_page_has_code($archive) {
    if (!$archive->is('post') && !$archive->is('page')) {
        return false;
    }
    $text = (string)($archive->text ?? '');
    return strpos($text, '```') !== false
        || strpos($text, '~~~') !== false
        || stripos($text, '<pre') !== false
        || stripos($text, '<code') !== false;
}

/**
 * 当前文章/页面是否包含可识别的数学定界符（决定是否加载 MathJax）。
 * 行内 $...$ 还需包含变量或运算信号，避免价格文案触发整套依赖。
 */
function bold_page_has_math($archive) {
    if (!$archive->is('post') && !$archive->is('page')) {
        return false;
    }
    $text = (string)($archive->text ?? '');
    if (preg_match('/(?<!\\\\)\$\$\s*([\s\S]*?\S)\s*\$\$/', $text)) {
        return true;
    }
    if (strpos($text, '\\(') !== false
        || strpos($text, '\\[') !== false
        || strpos($text, '\\begin{') !== false) {
        return true;
    }

    if (!preg_match_all('/(?<![\\\\$])\$(?![\s$])([^$\r\n]+?)(?<!\s)\$(?!\$)/', $text, $matches)) {
        return false;
    }

    foreach ($matches[1] as $formula) {
        $formula = trim($formula);
        if (preg_match('/^[A-Za-z]$/', $formula)
            || preg_match('/[\\\\_^={}+*\/><]/', $formula)
            || preg_match('/[A-Za-z]\s*=|=\s*[A-Za-z0-9]/', $formula)) {
            return true;
        }
    }

    return false;
}

/**
 * 探测阅读量列是否存在。
 */
function bold_views_column_exists() {
    $db = Typecho_Db::get();

    try {
        $db->fetchRow($db->select('views')->from('table.contents')->limit(1));
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * 在后台打开主题设置时完成阅读量列迁移。
 * 公开请求不调用此函数，避免匿名流量触发 DDL。
 */
function bold_ensure_views_column() {
    if (bold_views_column_exists()) {
        return true;
    }

    $db = Typecho_Db::get();
    $table = $db->getPrefix() . 'contents';

    try {
        $db->query(
            'ALTER TABLE ' . $table . ' ADD COLUMN views INTEGER DEFAULT 0',
            Typecho_Db::WRITE
        );
        return true;
    } catch (Exception $e) {
        // 并发迁移可能已由另一请求完成，失败后再探测一次。
        return bold_views_column_exists();
    }
}

/**
 * 文章阅读量统计。
 *  - 公开请求只探测/读取 views 列，缺列时安全降级为 0
 *  - 计数使用写连接的原子 UPDATE，不再读-改-写
 *  - 只有更新成功时才记录防重 Cookie
 */
function getPostViews($archive) {
    static $hasColumn = null;

    $cid = intval($archive->cid);
    $db = Typecho_Db::get();
    $prefix = $db->getPrefix();

    if ($hasColumn === null) {
        $hasColumn = bold_views_column_exists();
    }

    if (!$hasColumn) {
        echo 0;
        return;
    }

    $row = $db->fetchRow($db->select('views')->from('table.contents')->where('cid = ?', $cid));
    $views = intval($row['views'] ?? 0);

    if ($archive->is('single')) {
        $seen = Typecho_Cookie::get('extend_contents_views');
        $seenList = $seen ? array_values(array_filter(explode(',', $seen))) : array();
        if (!in_array(strval($cid), $seenList, true)) {
            $updated = false;
            try {
                $affected = $db->query(
                    'UPDATE ' . $prefix . 'contents SET views = COALESCE(views, 0) + 1 WHERE cid = ' . $cid,
                    Typecho_Db::WRITE,
                    Typecho_Db::UPDATE
                );
                $updated = intval($affected) > 0;
            } catch (Exception $e) {
                // 统计失败不影响页面
            }

            if ($updated) {
                $views++;
                $seenList[] = strval($cid);
                // 防止 Cookie 无限增长
                if (count($seenList) > 100) {
                    $seenList = array_slice($seenList, -100);
                }
                Typecho_Cookie::set('extend_contents_views', implode(',', $seenList));
            }
        }
    }

    echo $views;
}

/**
 * 获取阅读时间（分钟）
 */
function getReadingTime($archive) {
    $content = (string)($archive->text ?? '');
    $text = trim(strip_tags($content));
    $textLen = mb_strlen($text, 'UTF-8');
    return max(1, (int) ceil($textLen / BOLD_READING_SPEED));
}

/**
 * 获取相关文章。
 * 第一步查询加 LIMIT 防止 IN 列表无界；只取已发布、已到发布时间的文章；
 * 候选必须经过统一保护判定，避免相关文章泄露加密文章的标题和链接。
 * permalink 直接使用 Widget 计算结果，不再维护手写伪静态回退。
 */
function getRelatedPosts($archive, $limit = 3) {
    $emptyItem = '<li class="p-3 border-2 border-dashed border-black text-gray-500 text-sm bg-gray-50">'
        . get_theme_text('no_related', $archive) . '</li>';

    $limit = max(0, intval($limit));
    if ($limit === 0) {
        return;
    }

    $tags = $archive->tags;
    if (empty($tags)) {
        echo $emptyItem;
        return;
    }

    $tagIds = array();
    foreach ($tags as $tag) {
        if (isset($tag['mid'])) $tagIds[] = intval($tag['mid']);
    }
    if (empty($tagIds)) {
        echo $emptyItem;
        return;
    }

    $db = Typecho_Db::get();

    $relRows = $db->fetchAll($db->select('DISTINCT cid')->from('table.relationships')
        ->where('mid IN ?', $tagIds)
        ->limit(100));
    $relatedCids = array();
    foreach ($relRows as $r) {
        if (isset($r['cid']) && $r['cid'] != $archive->cid) {
            $relatedCids[] = intval($r['cid']);
        }
    }
    if (empty($relatedCids)) {
        echo $emptyItem;
        return;
    }

    // relationships 查询最多返回 100 个 cid，这里取回全部候选再过滤，
    // 否则前几个候选受保护时会少于调用方要求的数量。
    $related = $db->fetchAll($db->select()->from('table.contents')
        ->where('cid IN ?', $relatedCids)
        ->where('type = ?', 'post')
        ->where('status = ?', 'publish')
        ->where('created < ?', Helper::options()->time)
        ->order('created', Typecho_Db::SORT_DESC)
        ->limit(100));

    if (empty($related)) {
        echo $emptyItem;
        return;
    }

    $rendered = 0;
    foreach ($related as $row) {
        $cid = intval($row['cid'] ?? 0);
        try {
            $protected = $cid <= 0 || bold_cid_is_protected($cid);
        } catch (Throwable $e) {
            // 无法证明候选公开时失败即保密。
            $protected = true;
        }
        if ($protected) {
            continue;
        }

        $post = Typecho_Widget::widget('Widget_Abstract_Contents')->push($row);

        $permalink = htmlspecialchars($post['permalink'] ?? '#', ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8', false);
        $date = !empty($post['created']) ? bold_site_date('Y-m-d', $post['created']) : '';

        echo "<li>
                <a href=\"{$permalink}\" class='flex justify-between items-center border-2 border-black p-3 hover:bg-yellow-200 transition-colors group'>
                    <span class='font-bold truncate group-hover:text-pink-600 transition-colors'>{$title}</span>
                    <span class='text-xs font-mono whitespace-nowrap ml-2 bg-black text-white px-1'>{$date}</span>
                </a>
              </li>";

        $rendered++;
        if ($rendered >= $limit) {
            break;
        }
    }

    if ($rendered === 0) {
        echo $emptyItem;
    }
}

/**
 * 获取时间上相邻的公开文章。
 *
 * previous/prev 指更早的文章，next 指更新的文章。查询使用 created + cid
 * 作为稳定游标并分批向外搜索，因此一批候选全受保护时仍能找到更远的公开文章。
 *
 * @return array|null ['title' => ..., 'permalink' => ...]
 */
function bold_get_adjacent_public_post($archive, $direction) {
    $direction = strtolower(trim(strval($direction)));
    if ($direction === 'previous') {
        $direction = 'prev';
    }
    if ($direction !== 'prev' && $direction !== 'next') {
        return null;
    }

    $currentCid = intval($archive->cid ?? 0);
    $currentCreated = intval($archive->created ?? 0);
    if ($currentCid <= 0 || $currentCreated <= 0) {
        return null;
    }

    $db = Typecho_Db::get();
    $before = $direction === 'prev';
    $operator = $before ? '<' : '>';
    $sort = $before ? Typecho_Db::SORT_DESC : Typecho_Db::SORT_ASC;
    $cursorCreated = $currentCreated;
    $cursorCid = $currentCid;

    while (true) {
        $rows = $db->fetchAll($db->select()->from('table.contents')
            ->where('type = ?', 'post')
            ->where('status = ?', 'publish')
            ->where('created < ?', Helper::options()->time)
            ->where(
                '(created ' . $operator . ' ? OR (created = ? AND cid ' . $operator . ' ?))',
                $cursorCreated,
                $cursorCreated,
                $cursorCid
            )
            ->order('created', $sort)
            ->order('cid', $sort)
            ->limit(50));

        if (empty($rows)) {
            return null;
        }

        foreach ($rows as $row) {
            $cid = intval($row['cid'] ?? 0);
            try {
                $protected = $cid <= 0 || bold_cid_is_protected($cid);
            } catch (Throwable $e) {
                $protected = true;
            }
            if ($protected) {
                continue;
            }

            try {
                $post = Typecho_Widget::widget('Widget_Abstract_Contents')->push($row);
            } catch (Throwable $e) {
                continue;
            }
            $permalink = strval($post['permalink'] ?? '');
            if ($permalink === '') {
                continue;
            }

            return array(
                'title' => strval($post['title'] ?? $row['title'] ?? ''),
                'permalink' => $permalink,
            );
        }

        if (count($rows) < 50) {
            return null;
        }

        $last = $rows[count($rows) - 1];
        $nextCreated = intval($last['created'] ?? 0);
        $nextCid = intval($last['cid'] ?? 0);
        if ($nextCreated === $cursorCreated && $nextCid === $cursorCid) {
            return null;
        }
        $cursorCreated = $nextCreated;
        $cursorCid = $nextCid;
    }
}

/**
 * 按 Typecho 核心规则排序文章分类：先 order，再 mid。
 */
function bold_sort_categories($categories) {
    usort($categories, function ($a, $b) {
        $orderCompare = intval($a['order'] ?? 0) <=> intval($b['order'] ?? 0);
        if ($orderCompare !== 0) {
            return $orderCompare;
        }

        return intval($a['mid'] ?? 0) <=> intval($b['mid'] ?? 0);
    });

    return $categories;
}

/**
 * 返回 Typecho 用于 permalink 的主分类。
 */
function bold_primary_category($categories) {
    $categories = bold_sort_categories($categories);
    return $categories[0] ?? null;
}

/**
 * 返回从根分类到主分类的 slug 目录。
 */
function bold_category_directory_slugs($primary, $categoryByMid) {
    if (empty($primary)) {
        return array();
    }

    $primaryMid = intval($primary['mid'] ?? 0);
    $seen = $primaryMid > 0 ? array($primaryMid => true) : array();
    $parents = array();
    $parentMid = intval($primary['parent'] ?? 0);

    while ($parentMid > 0 && isset($categoryByMid[$parentMid])) {
        if (isset($seen[$parentMid])) {
            return array((string)($primary['slug'] ?? ''));
        }

        $parent = $categoryByMid[$parentMid];
        $parents[] = (string)($parent['slug'] ?? '');
        $seen[$parentMid] = true;
        $parentMid = intval($parent['parent'] ?? 0);
    }

    $parents = array_reverse($parents);
    $parents[] = (string)($primary['slug'] ?? '');
    return $parents;
}

/**
 * 时间轴归档数据。
 * 三次轻量查询分别取文章、分类树和关联映射，不读正文，
 * 同时复制 Typecho 的主分类、父级目录与 URL 编码规则。
 * 受保护分类的文章对未解锁访客直接隐藏。
 *
 * @return array [ ['permalink' => ..., 'title' => ..., 'created' => ...], ... ]
 */
function bold_timeline_posts() {
    $db = Typecho_Db::get();
    $options = Helper::options();

    $rows = $db->fetchAll($db->select('cid', 'title', 'slug', 'created', 'type')
        ->from('table.contents')
        ->where('type = ?', 'post')
        ->where('status = ?', 'publish')
        ->where('created < ?', $options->time)
        ->order('created', Typecho_Db::SORT_DESC));

    if (empty($rows)) {
        return array();
    }

    // 分类树用于还原多级分类固定链接。
    $cids = array_map(function ($row) { return intval($row['cid']); }, $rows);
    $catRows = $db->fetchAll($db->select('mid', 'slug', 'parent', 'order')
        ->from('table.metas')
        ->where('type = ?', 'category'));

    $categoryByMid = array();
    foreach ($catRows as $catRow) {
        $mid = intval($catRow['mid']);
        $catRow['mid'] = $mid;
        $catRow['parent'] = intval($catRow['parent'] ?? 0);
        $catRow['order'] = intval($catRow['order'] ?? 0);
        $categoryByMid[$mid] = $catRow;
    }

    $cidCategories = array();
    if (!empty($categoryByMid)) {
        $relationshipRows = $db->fetchAll($db->select('cid', 'mid')
            ->from('table.relationships')
            ->where('cid IN ?', $cids)
            ->where('mid IN ?', array_keys($categoryByMid)));

        foreach ($relationshipRows as $relationshipRow) {
            $mid = intval($relationshipRow['mid']);
            if (isset($categoryByMid[$mid])) {
                $cidCategories[intval($relationshipRow['cid'])][] = $categoryByMid[$mid];
            }
        }
    }

    $protectedSlugs = bold_get_protected_slugs();
    if (!empty($protectedSlugs)) {
        // 是否显示取决于访客解锁 Cookie
        bold_private_cache_headers();
    }

    $posts = array();
    foreach ($rows as $row) {
        $cid = intval($row['cid']);
        $categories = bold_sort_categories($cidCategories[$cid] ?? array());
        $categorySlugs = array_map(function ($category) {
            return (string)$category['slug'];
        }, $categories);

        // 受保护分类：未解锁访客不显示
        $protected = array_values(array_intersect($categorySlugs, $protectedSlugs));
        if (!empty($protected) && !bold_is_category_unlocked($protected[0])) {
            continue;
        }

        $primary = bold_primary_category($categories);
        $directorySlugs = bold_category_directory_slugs($primary, $categoryByMid);
        // 路由的 year/month/day 参数必须与核心生成 permalink 时
        // 完全一致（核心用 Typecho_Date，受服务器时区 DST 影响），
        // 否则日期型固定链接会差一天而 404；显示用日期才走 bold_site_date
        $routeDate = new Typecho_Date(intval($row['created']));
        $params = array(
            'cid'       => $cid,
            'slug'      => urlencode($row['slug']),
            'category'  => $primary ? urlencode($primary['slug']) : '',
            'directory' => implode('/', array_map('urlencode', $directorySlugs)),
            'year'      => $routeDate->year,
            'month'     => $routeDate->month,
            'day'       => $routeDate->day,
        );

        try {
            $permalink = Typecho_Router::url('post', $params, $options->index);
        } catch (Exception $e) {
            $permalink = '#';
        }

        $posts[] = array(
            'permalink' => $permalink,
            'title'     => $row['title'],
            'created'   => intval($row['created']),
        );
    }

    return $posts;
}

/**
 * 输出带有独立颜色的分类标签
 */
function printColoredCategories($archive) {
    // 颜色池：高饱和度背景色，适合搭配 text-white
    $colors = array(
        'bg-blue-600',
        'bg-pink-600',
        'bg-purple-600',
        'bg-green-600',
        'bg-red-600',
        'bg-indigo-600',
        'bg-cyan-600',
        'bg-rose-600',
        'bg-emerald-600',
        'bg-fuchsia-600',
    );

    if ($archive->categories) {
        foreach ($archive->categories as $category) {
            // 根据分类 MID 取模，保证颜色固定
            $colorIndex = $category['mid'] % count($colors);
            $colorClass = $colors[$colorIndex];

            $permalink = htmlspecialchars($category['permalink'] ?? '#', ENT_QUOTES, 'UTF-8');
            $name = htmlspecialchars($category['name'] ?? '', ENT_QUOTES, 'UTF-8', false);

            echo '<a href="' . $permalink . '" class="' . $colorClass . ' text-white px-3 py-1 border-2 border-black shadow-[2px_2px_0px_0px_#000] hover:shadow-none hover:translate-x-[2px] hover:translate-y-[2px] transition-all dark:border-[#10b981] dark:shadow-[2px_2px_0px_0px_#10b981] mr-2 no-underline">' . $name . '</a>';
        }
    }
}
