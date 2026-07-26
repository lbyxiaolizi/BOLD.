<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * SEO: 纯文本描述。
 * 基于原始正文提取（不触发整篇 Markdown 渲染），
 * 自动剥离 {hide}/{password:} 标记，杜绝内联密码进入 meta。
 * 请求级缓存，header 中多次调用只计算一次。
 */
function get_seo_description($archive) {
    static $cache = array();

    $options = Helper::options();

    if ($archive->is('index')) {
        return strval($options->description);
    }

    if ($archive->is('post') || $archive->is('page')) {
        if (isPasswordProtected($archive) && !isPasswordVerified($archive)) {
            return get_theme_text('protected_excerpt', $archive);
        }

        $key = 'c' . intval($archive->cid);
        if (!isset($cache[$key])) {
            $description = bold_plain_text($archive, 150);
            $cache[$key] = $description !== '' ? $description : strval($options->description);
        }
        return $cache[$key];
    }

    if ($archive->is('category')) {
        return $archive->getDescription() ? $archive->getDescription() : $archive->getArchiveTitle();
    }

    return strval($options->description);
}

/**
 * SEO: 社交分享封面图。
 * 未配置默认图且正文无图时返回空串，由模板省略 og:image，
 * 不再输出会 404 的第三方示例图。
 */
function get_og_image($archive) {
    static $cache = array();

    $options = Helper::options();
    $default = trim(strval($options->defaultOgImage ?? ''));

    if ($archive->is('post') || $archive->is('page')) {
        $key = 'c' . intval($archive->cid);
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        if (isPasswordProtected($archive) && !isPasswordVerified($archive)) {
            return $cache[$key] = $default;
        }

        $text = (string)($archive->text ?? '');
        if ($text !== '') {
            // 内联密码与回复可见区域的图片不能成为公开 OG 元数据。
            $text = bold_strip_protected_markers($text);
            if (preg_match('/<img[^>]*src=["\']([^"\']+)["\']/i', $text, $m)) {
                return $cache[$key] = $m[1];
            }
            if (preg_match('/!\[[^\]]*\]\(\s*(\S+?)(?:\s+["\'][^)]*)?\)/', $text, $m)) {
                return $cache[$key] = $m[1];
            }
        }
        return $cache[$key] = $default;
    }

    return $default;
}
