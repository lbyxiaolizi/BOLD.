<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 主题后台配置面板
 */
function themeConfig($form) {
    // 1. 语言设置 (新增)
    $languageSetting = new Typecho_Widget_Helper_Form_Element_Radio('languageSetting',
        array('en' => _t('English (英文)'), 'cn' => _t('Chinese (中文)')),
        'en', _t('界面标题语言'), _t('切换侧边栏、评论区等标题的显示语言'));
    $form->addInput($languageSetting);

    // 2. 站点 Logo 文字
    $logoText = new Typecho_Widget_Helper_Form_Element_Text('logoText', NULL, NULL, _t('站点 Logo 文字'), _t('支持 HTML，例如 <span class="text-pink-600">.</span>'));
    $form->addInput($logoText);
    
    $faviconUrl = new Typecho_Widget_Helper_Form_Element_Text('faviconUrl', NULL, NULL, _t('Favicon 图标 URL'), _t('浏览器标签页图标，留空则不显示'));
    $form->addInput($faviconUrl);

    $AuthorName = new Typecho_Widget_Helper_Form_Element_Text('AuthorName', NULL, NULL, _t('作者名称'), _t('作者名称'));
    $form->addInput($AuthorName);

    $avatarUrl = new Typecho_Widget_Helper_Form_Element_Text('avatarUrl', NULL, NULL, _t('个人头像 URL'), _t('输入头像图片的地址，将显示在侧边栏或个人卡片中'));
    $form->addInput($avatarUrl);
    
    $descriptions = new Typecho_Widget_Helper_Form_Element_Text('descriptions', NULL, NULL, _t('个人简介'), _t('个人简介'));
    $form->addInput($descriptions);

    $githubLink = new Typecho_Widget_Helper_Form_Element_Text('githubLink', NULL, NULL, _t('GitHub 链接'), _t('您的 GitHub 主页地址'));
    $form->addInput($githubLink);

    $bilibiliLink = new Typecho_Widget_Helper_Form_Element_Text('bilibiliLink', NULL, NULL, _t('Bilibili 链接'), _t('您的 Bilibili 主页地址'));
    $form->addInput($bilibiliLink);
    
    $email = new Typecho_Widget_Helper_Form_Element_Text('email', NULL, NULL, _t('email'), _t('您的email'));
    $form->addInput($email);
    
    $icpNum = new Typecho_Widget_Helper_Form_Element_Text('icpNum', NULL, NULL, _t('ICP 备案号'), _t('中国大陆网站需填写，显示在页脚'));
    $form->addInput($icpNum);

    // 7. 自定义头部/底部代码 (新增)
    $customHead = new Typecho_Widget_Helper_Form_Element_Textarea('customHead', NULL, NULL, _t('自定义头部 HTML'), _t('位于 &lt;/head&gt; 之前，可填写自定义 CSS 或 验证 meta 标签'));
    $form->addInput($customHead);

    $customFooter = new Typecho_Widget_Helper_Form_Element_Textarea('customFooter', NULL, NULL, _t('自定义底部 HTML'), _t('位于 &lt;/body&gt; 之前，可填写 Google/百度统计代码或自定义 JS'));
    $form->addInput($customFooter);
    
    // 5. Cloudflare Turnstile 配置 (新增)
    $turnstileSiteKey = new Typecho_Widget_Helper_Form_Element_Text('turnstileSiteKey', NULL, NULL, _t('Turnstile Site Key'), _t('Cloudflare Turnstile 站点密钥，留空则不启用'));
    $form->addInput($turnstileSiteKey);

    $turnstileSecretKey = new Typecho_Widget_Helper_Form_Element_Text('turnstileSecretKey', NULL, NULL, _t('Turnstile Secret Key'), _t('Cloudflare Turnstile 密钥，留空则不启用'));
    $form->addInput($turnstileSecretKey);
    
    // 6. 侧边栏作者名称
    $sidebarAuthorName = new Typecho_Widget_Helper_Form_Element_Text('sidebarAuthorName', NULL, NULL, _t('侧边栏作者名称'), _t('显示在侧边栏个人卡片中的名称'));
    $form->addInput($sidebarAuthorName);
    
    // 7. 打赏功能设置
    $wechatQrUrl = new Typecho_Widget_Helper_Form_Element_Text('wechatQrUrl', NULL, NULL, _t('微信收款码 URL'), _t('微信打赏二维码图片地址，留空则显示占位符'));
    $form->addInput($wechatQrUrl);
    
    $alipayQrUrl = new Typecho_Widget_Helper_Form_Element_Text('alipayQrUrl', NULL, NULL, _t('支付宝收款码 URL'), _t('支付宝打赏二维码图片地址，留空则显示占位符'));
    $form->addInput($alipayQrUrl);
    
    // 8. 默认封面图
    $defaultOgImage = new Typecho_Widget_Helper_Form_Element_Text('defaultOgImage', NULL, NULL, _t('默认封面图 URL'), _t('当文章没有图片时使用的默认社交分享封面图'));
    $form->addInput($defaultOgImage);
    
    // 9. 文章/分类密码保护
    $postPassword = new Typecho_Widget_Helper_Form_Element_Text('postPassword', NULL, NULL, _t('全站加密密码'), _t('设置后，访客需要输入密码才能查看所有文章内容。留空则不启用'));
    $form->addInput($postPassword);
    
    $passwordProtectedCategories = new Typecho_Widget_Helper_Form_Element_Text('passwordProtectedCategories', NULL, NULL, _t('加密分类 (用英文逗号分隔)'), _t('输入需要密码保护的分类别名(slug)，多个用逗号分隔。例如: private,secret'));
    $form->addInput($passwordProtectedCategories);
    
    $categoryPasswords = new Typecho_Widget_Helper_Form_Element_Textarea('categoryPasswords', NULL, NULL, _t('分类独立密码设置'), _t('为不同的分类设置不同的密码。格式：分类slug:密码，每行一个。例如：<br>private:password123<br>secret:mySecret456<br>如果某分类未单独设置密码，将使用全站加密密码'));
    $form->addInput($categoryPasswords);
    
    $hideProtectedCategoriesFromHome = new Typecho_Widget_Helper_Form_Element_Radio('hideProtectedCategoriesFromHome',
        array('1' => _t('隐藏'), '0' => _t('显示')),
        '0', _t('加密分类文章在首页的显示'), _t('选择是否在首页隐藏属于加密分类的文章'));
    $form->addInput($hideProtectedCategoriesFromHome);
    
    $requireCategoryArchivePassword = new Typecho_Widget_Helper_Form_Element_Radio('requireCategoryArchivePassword',
        array('1' => _t('需要'), '0' => _t('不需要')),
        '1', _t('加密分类的归档页面是否需要密码验证'), _t('选择访问加密分类的归档页面时是否需要输入密码。选择"需要"时，访问加密分类归档页面需要先验证密码才能查看文章列表'));
    $form->addInput($requireCategoryArchivePassword);
}

/**
 * 获取主题多语言文本
 */
function get_theme_text($key, $archive) {
    $lang = Helper::options()->languageSetting;
    if (empty($lang)) $lang = 'en';

    $texts = array(
        'search' => array('en' => 'SEARCH', 'cn' => '搜索'),
        'categories' => array('en' => 'CATEGORIES', 'cn' => '分类'),
        'comments' => array('en' => 'COMMENTS', 'cn' => '评论'),
        'tags' => array('en' => 'TAGS', 'cn' => '标签'),
        'toc' => array('en' => 'TABLE OF CONTENTS', 'cn' => '文章目录'),
        'leave_reply' => array('en' => 'LEAVE A REPLY', 'cn' => '发表评论'),
        'submit_comment' => array('en' => 'SUBMIT COMMENT', 'cn' => '提交评论'),
        'related_posts' => array('en' => 'YOU MAY ALSO LIKE', 'cn' => '相关推荐'),
        'timeline_title' => array('en' => 'TIMELINE <span class="text-white">ARCHIVE</span>', 'cn' => '时间轴 <span class="text-white">归档</span>'),
        'links_title' => array('en' => 'FRIENDS <span class="text-white">LINKS</span>', 'cn' => '友情 <span class="text-white">链接</span>'),
        'password_required' => array('en' => 'PASSWORD REQUIRED', 'cn' => '需要密码'),
        'password_placeholder' => array('en' => 'Enter password...', 'cn' => '请输入密码...'),
        'password_submit' => array('en' => 'UNLOCK', 'cn' => '解锁'),
        'password_error' => array('en' => 'Incorrect password', 'cn' => '密码错误'),
        'password_protected_content' => array('en' => 'This content is password protected.', 'cn' => '此内容受密码保护。'),
    );

    return isset($texts[$key][$lang]) ? $texts[$key][$lang] : '';
}

/**
 * 评论验证钩子
 */
class ThemeHooks {
    public static function verifyTurnstile($comment, $post) {
        $options = Helper::options();
        $siteKey = $options->turnstileSiteKey;
        $secretKey = $options->turnstileSecretKey;

        if (empty($siteKey) || empty($secretKey)) {
            return $comment;
        }

        $token = Typecho_Request::getInstance()->get('cf-turnstile-response');
        if (empty($token)) {
            throw new Typecho_Widget_Exception(_t('请完成人机验证 (Please complete the CAPTCHA)'));
        }

        $ip = Typecho_Request::getInstance()->getIp();
        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        $data = [
            'secret' => $secretKey,
            'response' => $token,
            'remoteip' => $ip
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        if (!$result['success']) {
            throw new Typecho_Widget_Exception(_t('人机验证失败，请刷新重试'));
        }

        return $comment;
    }
}
Typecho_Plugin::factory('Widget_Feedback')->comment = array('ThemeHooks', 'verifyTurnstile');

/**
 * 密码保护功能
 */

/**
 * 安全清理分类slug用于Cookie名称
 * @param string $slug 分类slug
 * @return string 清理后的slug
 */
function sanitizeCategorySlugForCookie($slug) {
    // 只允许字母、数字、下划线和短横线
    return preg_replace('/[^a-zA-Z0-9_-]/', '', $slug);
}

/**
 * 从请求中解析分类 slug，兼容 /category/{slug}/ 或包含 index.php 的路径
 * @return string|null
 */
function resolveCategorySlugFromRequest() {
    $request = Typecho_Request::getInstance();

    // 1) 优先显式参数
    $slug = $request->get('slug');
    if (!empty($slug)) {
        return $slug;
    }

    // 2) 从路径解析
    $pathInfo = trim($request->getPathInfo(), '/');
    if (!empty($pathInfo)) {
        $segments = array_values(array_filter(explode('/', $pathInfo)));

        // 在路径中找到 "category" 段，取其后的下一个元素作为 slug
        $categoryIndex = array_search('category', $segments, true);
        if ($categoryIndex !== false && isset($segments[$categoryIndex + 1])) {
            $slugCandidate = $segments[$categoryIndex + 1];
        } else {
            // 如果不存在明确的 category 段，尝试使用最后一个段
            $slugCandidate = end($segments);
        }

        if (!empty($slugCandidate)) {
            // 去除可能的后缀（如 .html）并解码
            $slugCandidate = preg_replace('/\.(html?|php)$/i', '', $slugCandidate);
            return urldecode($slugCandidate);
        }
    }

    // 3) 通过 mid 查找
    $mid = $request->get('mid');
    if (!empty($mid)) {
        $db = Typecho_Db::get();
        $meta = $db->fetchRow($db->select('slug')->from('table.metas')->where('mid = ?', $mid)->limit(1));
        if ($meta && !empty($meta['slug'])) {
            return $meta['slug'];
        }
    }

    return null;
}

/**
 * 解析分类密码配置
 * @return array 返回 分类slug => 密码 的映射数组
 */
function parseCategoryPasswords() {
    $options = Helper::options();
    $categoryPasswords = array();
    
    if (!empty($options->categoryPasswords)) {
        $lines = explode("\n", $options->categoryPasswords);
        foreach ($lines as $line) {
            $line = trim($line);
            // Skip empty lines
            if (empty($line)) {
                continue;
            }
            // Check that there's at least one colon
            if (strpos($line, ':') === false) {
                continue;
            }
            // Split on first colon only (allows password to contain colons)
            list($slug, $password) = explode(':', $line, 2);
            $slug = trim($slug);
            $password = trim($password);
            if (!empty($slug) && !empty($password)) {
                $categoryPasswords[$slug] = $password;
            }
        }
    }
    
    return $categoryPasswords;
}

/**
 * 获取文章/分类所需的密码（优先级：文章独立密码 > 分类独立密码 > 全站密码）
 * @param object $archive 文章/分类对象
 * @return string|null 返回需要的密码，如果不需要密码则返回null
 */
function getRequiredPassword($archive) {
    $options = Helper::options();
    $categoryPasswords = parseCategoryPasswords();
    
    // 优先检查文章自定义字段中的密码（单篇文章密码）
    if ($archive->is('single') && isset($archive->fields->password) && !empty($archive->fields->password)) {
        return $archive->fields->password;
    }
    
    // 获取受保护的分类slug（支持文章页和分类页）
    $categorySlug = getProtectedCategorySlug($archive);
    
    if ($categorySlug !== null) {
        // 如果有分类独立密码，使用分类密码
        if (isset($categoryPasswords[$categorySlug]) && !empty($categoryPasswords[$categorySlug])) {
            return $categoryPasswords[$categorySlug];
        }
        // 否则使用全站密码
        if (!empty($options->postPassword)) {
            return $options->postPassword;
        }
        // 如果都没有配置密码，使用一个安全的随机值（确保无法被猜测）
        // 使用站点特定盐值、分类slug和当前日期的组合，每天生成不同的哈希
        // 这样可以防止未配置密码时被绕过，同时给管理员提示需要配置密码
        $dateComponent = date('Y-m-d'); // 每天变化
        return hash('sha256', getBoldSecretSalt() . $categorySlug . $dateComponent . 'no_password_configured');
    }
    
    // 检查全站密码
    if (!empty($options->postPassword)) {
        return $options->postPassword;
    }
    
    return null;
}

/**
 * 获取文章所属的受保护分类slug（支持文章页和分类页）
 * @param object $archive 文章/分类对象
 * @return string|null 返回受保护的分类slug，如果没有则返回null
 */
function getProtectedCategorySlug($archive) {
    $options = Helper::options();

    $protectedSlugs = array();

    if (!empty($options->passwordProtectedCategories)) {
        $protectedSlugs = array_map('trim', explode(',', $options->passwordProtectedCategories));
    }

    // 将独立分类密码中的 slug 也视为受保护分类，防止漏配导致归档绕过
    $categoryPasswords = parseCategoryPasswords();
    if (!empty($categoryPasswords)) {
        $protectedSlugs = array_merge($protectedSlugs, array_keys($categoryPasswords));
    }

    $protectedSlugs = array_values(array_unique(array_filter($protectedSlugs)));

    if (empty($protectedSlugs)) {
        return null;
    }
    
    // 如果是分类页面，检查当前分类是否需要密码保护
    if ($archive->is('category')) {
        $candidateSlugs = array();

        if (isset($archive->slug) && !empty($archive->slug)) {
            $candidateSlugs[] = $archive->slug;
        }

        // 请求参数中的 slug（Typecho 路由变量）
        if (isset($archive->request) && !empty($archive->request->slug)) {
            $candidateSlugs[] = $archive->request->slug;
        }

        // 归档参数中的 slug（某些情况下会存放在 parameter 对象内）
        if (isset($archive->parameter) && !empty($archive->parameter->slug)) {
            $candidateSlugs[] = $archive->parameter->slug;
        }

        // 路径或 mid 解析的 slug 兜底
        $resolvedSlug = resolveCategorySlugFromRequest();
        if (!empty($resolvedSlug)) {
            $candidateSlugs[] = $resolvedSlug;
        }

        foreach ($candidateSlugs as $currentSlug) {
            $currentSlug = trim($currentSlug);
            if (!empty($currentSlug) && in_array($currentSlug, $protectedSlugs)) {
                return $currentSlug;
            }
        }

        // 某些情况下分类归档对象会携带 categories 列表，但不包含 slug 字段
        if (!empty($archive->categories)) {
            foreach ($archive->categories as $category) {
                if (!empty($category['slug']) && in_array($category['slug'], $protectedSlugs)) {
                    return $category['slug'];
                }
            }
        }

        return null;
    }
    
    // 检查文章所属分类（在列表页和文章页都需要检测，避免摘要泄露）
    if (!empty($archive->categories)) {
        foreach ($archive->categories as $category) {
            if (in_array($category['slug'], $protectedSlugs)) {
                return $category['slug'];
            }
        }
    }
    
    return null;
}

/**
 * 检查文章是否需要密码保护
 */
function isPasswordProtected($archive) {
    return getRequiredPassword($archive) !== null;
}

/**
 * 检查密码是否已验证
 */
function isPasswordVerified($archive) {
    $options = Helper::options();
    
    // 已登录用户直接通过
    $user = Typecho_Widget::widget('Widget_User');
    if ($user->hasLogin()) {
        return true;
    }
    
    $requiredPassword = getRequiredPassword($archive);
    if ($requiredPassword === null) {
        return true; // 不需要密码
    }
    
    // 获取受保护的分类slug（如果是分类保护）
    $categorySlug = getProtectedCategorySlug($archive);
    
    // 如果是分类密码保护，检查分类特定的Cookie
    if ($categorySlug !== null) {
        $safeCategorySlug = sanitizeCategorySlugForCookie($categorySlug);
        $cookieName = 'bold_category_verified_' . $safeCategorySlug;
        $verifiedHash = Typecho_Cookie::get($cookieName);
        if (!empty($verifiedHash)) {
            // 验证分类特定密码的哈希
            if (hash_equals(hash('sha256', $requiredPassword . getBoldSecretSalt()), $verifiedHash)) {
                return true;
            }
        }
    }
    
    // 检查全站密码验证
    $verifiedHash = Typecho_Cookie::get('bold_password_verified');
    if (!empty($verifiedHash)) {
        // 使用更安全的哈希比较
        if (!empty($requiredPassword) && hash_equals(hash('sha256', $requiredPassword . getBoldSecretSalt()), $verifiedHash)) {
            return true;
        }
    }
    
    return false;
}

/**
 * 获取安全盐值 (基于站点URL生成唯一盐)
 */
function getBoldSecretSalt() {
    $options = Helper::options();
    return hash('sha256', $options->siteUrl . 'bold_theme_salt');
}

/**
 * 处理密码验证请求
 */
function handlePasswordVerification($archive) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bold_password'])) {
        $options = Helper::options();
        // 输入清理
        $inputPassword = isset($_POST['bold_password']) ? strval($_POST['bold_password']) : '';
        
        // 获取当前文章需要的正确密码
        $correctPassword = getRequiredPassword($archive);
        
        // CSRF 保护 - 验证来源
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        $siteUrl = $options->siteUrl;
        
        // Extract hostname from both URLs for exact matching
        $refererHost = !empty($referer) ? parse_url($referer, PHP_URL_HOST) : false;
        $siteHost = parse_url($siteUrl, PHP_URL_HOST);
        
        // Strict hostname comparison - must match exactly, handle false return from parse_url
        if ($refererHost === false || $siteHost === false || $refererHost !== $siteHost) {
            return true; // 返回错误状态
        }
        
        if (!empty($correctPassword) && $inputPassword === $correctPassword) {
            // 获取分类slug（如果是分类保护）
            $categorySlug = getProtectedCategorySlug($archive);
            
            // 使用更安全的哈希，设置验证 Cookie (有效期 7 天)
            $passwordHash = hash('sha256', $correctPassword . getBoldSecretSalt());
            
            if ($categorySlug !== null) {
                $safeCategorySlug = sanitizeCategorySlugForCookie($categorySlug);
                // 设置分类特定的Cookie
                $cookieName = 'bold_category_verified_' . $safeCategorySlug;
                Typecho_Cookie::set($cookieName, $passwordHash, time() + 604800);
            } else {
                // 设置全站密码Cookie
                Typecho_Cookie::set('bold_password_verified', $passwordHash, time() + 604800);
            }
            
            // 安全重定向 - 仅使用路径部分
            $redirectPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
            $safeUri = $redirectPath . ($query ? '?' . $query : '');
            header('Location: ' . $safeUri);
            exit;
        } else {
            return true; // 返回错误状态
        }
    }
    return false;
}

/**
 * 输出密码保护表单
 */
function renderPasswordForm($archive, $hasError = false) {
    $lang = Helper::options()->languageSetting;
    if (empty($lang)) $lang = 'en';
    
    // 获取受保护的分类信息
    $categorySlug = getProtectedCategorySlug($archive);
    $categoryName = null;
    if ($categorySlug && !empty($archive->categories)) {
        foreach ($archive->categories as $category) {
            if ($category['slug'] === $categorySlug) {
                $categoryName = $category['name'];
                break;
            }
        }
    }
    
    // 根据是否有分类信息调整描述文本
    if ($categoryName) {
        $desc = $lang === 'cn' 
            ? "此内容属于加密分类「{$categoryName}」，请输入分类密码查看。" 
            : "This content belongs to the encrypted category \"{$categoryName}\". Please enter the category password to view.";
    } else {
        $desc = $lang === 'cn' ? '此内容受密码保护，请输入密码查看。' : 'This content is password protected. Please enter the password to view.';
    }
    
    $texts = array(
        'title' => $lang === 'cn' ? '需要密码' : 'PASSWORD REQUIRED',
        'desc' => $desc,
        'placeholder' => $lang === 'cn' ? '请输入密码...' : 'Enter password...',
        'submit' => $lang === 'cn' ? '解锁' : 'UNLOCK',
        'error' => $lang === 'cn' ? '密码错误，请重试' : 'Incorrect password, please try again',
    );
    
    ?>
    <div class="password-form-container my-8">
        <div class="password-form-inner flex flex-col items-center justify-center text-center p-6 md:p-10">
            <div class="text-6xl mb-4">🔐</div>
            <h3 class="text-2xl font-black uppercase mb-2"><?php echo $texts['title']; ?></h3>
            <p class="font-bold mb-6 max-w-md"><?php echo $texts['desc']; ?></p>
            
            <?php if ($hasError): ?>
            <div class="bg-red-100 border-2 border-red-500 text-red-700 px-4 py-2 mb-4 font-bold">
                <?php echo $texts['error']; ?>
            </div>
            <?php endif; ?>
            
            <form method="post" class="w-full max-w-sm">
                <input type="password" name="bold_password" placeholder="<?php echo $texts['placeholder']; ?>" 
                    class="w-full p-3 font-bold border-4 border-black focus:outline-none focus:border-pink-500 mb-4 text-center dark:bg-[#121212] dark:text-white dark:border-[#10b981]" required>
                <button type="submit" class="w-full bg-black text-white px-8 py-3 font-black text-lg uppercase tracking-widest hover:bg-pink-500 transition-colors border-4 border-black shadow-[4px_4px_0px_0px_#000] dark:bg-[#10b981] dark:text-black dark:border-[#10b981] dark:shadow-[4px_4px_0px_0px_#000]">
                    <?php echo $texts['submit']; ?>
                </button>
            </form>
        </div>
    </div>
    <style>
        .password-form-container {
            background: repeating-linear-gradient(45deg, #fef08a, #fef08a 20px, #000 20px, #000 40px);
            padding: 10px; border: 4px solid #000; box-shadow: 8px 8px 0px 0px #000;
        }
        .password-form-inner { background: #fff; border: 4px solid #000; }
        body.dark-mode .password-form-container {
            background: repeating-linear-gradient(45deg, #064e3b, #064e3b 20px, #000 20px, #000 40px);
            border-color: #10b981; box-shadow: 8px 8px 0px 0px #10b981;
        }
        body.dark-mode .password-form-inner { background: #121212; border-color: #10b981; color: #e5e5e5; }
    </style>
    <?php
}

/**
 * 核心逻辑：评论可见
 */
function parseReplyContent($content, $archive) {
    if (!$archive->is('single')) {
        return preg_replace("/{hide}(.*?){\/hide}/sm", '', $content);
    }

    if (strpos($content, '{hide}') !== false) {
        $db = Typecho_Db::get();
        $hasComment = false;
        
        $user = Typecho_Widget::widget('Widget_User');

        if ($user->hasLogin() && $user->uid == $archive->authorId) {
            $hasComment = true;
        }
        elseif ($user->hasLogin()) {
            $comment = $db->fetchRow($db->select()->from('table.comments')
                ->where('cid = ?', $archive->cid)
                ->where('authorId = ?', $user->uid)
                ->limit(1));
            if ($comment) $hasComment = true;
        }
        else {
            $email = Typecho_Cookie::get('__typecho_remember_mail');
            if ($email) {
                $comment = $db->fetchRow($db->select()->from('table.comments')
                    ->where('cid = ?', $archive->cid)
                    ->where('mail = ?', $email)
                    ->limit(1));
                if ($comment) $hasComment = true;
            }
        }

        if ($hasComment) {
            $content = str_replace(array('{hide}', '{/hide}'), '', $content);
            $content = '<div class="p-4 border-l-4 border-green-500 bg-green-50 dark:bg-green-900/20 dark:border-green-400 mb-6">
                            <p class="font-bold text-green-700 dark:text-green-400 m-0">🔓 内容已解锁 / UNLOCKED</p>
                        </div>' . $content;
        } else {
            $hideNotice = '
            <div class="reply2view-container my-8">
                <div class="reply2view-inner flex flex-col items-center justify-center text-center p-6 md:p-10">
                    <div class="text-6xl mb-4">🔒</div>
                    <h3 class="text-2xl font-black uppercase mb-2">LOCKED CONTENT</h3>
                    <p class="font-bold mb-6 max-w-md">此区域包含隐藏内容。<br>请在下方评论后刷新页面查看。</p>
                    <a href="#comments" class="inline-block bg-black text-white px-8 py-3 font-black text-lg uppercase tracking-widest hover:bg-white hover:text-black transition-colors border-4 border-black shadow-[4px_4px_0px_0px_#fff] dark:shadow-[4px_4px_0px_0px_#10b981] dark:hover:bg-[#10b981] dark:hover:border-[#10b981]">
                        去评论 / REPLY
                    </a>
                </div>
            </div>
            <style>
                .reply2view-container {
                    background: repeating-linear-gradient(45deg, #fef08a, #fef08a 20px, #000 20px, #000 40px);
                    padding: 10px; border: 4px solid #000; box-shadow: 8px 8px 0px 0px #000;
                }
                .reply2view-inner { background: #fff; border: 4px solid #000; }
                body.dark-mode .reply2view-container {
                    background: repeating-linear-gradient(45deg, #064e3b, #064e3b 20px, #000 20px, #000 40px);
                    border-color: #10b981; box-shadow: 8px 8px 0px 0px #10b981;
                }
                body.dark-mode .reply2view-inner { background: #121212; border-color: #10b981; color: #e5e5e5; }
                body.dark-mode .reply2view-inner a.bg-black { background-color: #10b981; color: #000; border-color: #10b981; box-shadow: 4px 4px 0px 0px #000; }
                body.dark-mode .reply2view-inner a.bg-black:hover { background-color: #000; color: #10b981; box-shadow: 4px 4px 0px 0px #10b981; }
            </style>
            ';
            $content = preg_replace("/{hide}(.*?){\/hide}/sm", $hideNotice, $content);
        }
    }
    return $content;
}

/**
 * 解析内联密码保护内容 {password:密码}内容{/password}
 */
function parseInlinePasswordContent($content, $archive) {
    // 如果不是单页面或没有密码标签，直接返回
    if (!$archive->is('single') || strpos($content, '{password:') === false) {
        // 在列表页移除所有密码保护内容（要求至少一个字符的密码）
        return preg_replace("/{password:[^}]+}(.*?){\/password}/sm", '', $content);
    }
    
    // 查找所有密码保护的内容块（要求至少一个字符的密码）
    preg_match_all("/{password:([^}]+)}(.*?){\/password}/sm", $content, $matches, PREG_SET_ORDER);
    
    if (empty($matches)) {
        return $content;
    }
    
    $user = Typecho_Widget::widget('Widget_User');
    $lang = Helper::options()->languageSetting;
    if (empty($lang)) $lang = 'en';
    
    foreach ($matches as $match) {
        $fullMatch = $match[0];
        $requiredPassword = trim($match[1]);
        $protectedContent = $match[2];
        
        // 已登录用户且是文章作者，直接显示内容
        if ($user->hasLogin() && $user->uid == $archive->authorId) {
            $replacement = '<div class="p-4 border-l-4 border-green-500 bg-green-50 dark:bg-green-900/20 dark:border-green-400 mb-6">
                            <p class="font-bold text-green-700 dark:text-green-400 m-0">🔓 内容已解锁（作者）/ UNLOCKED (Author)</p>
                        </div>' . $protectedContent;
            $content = str_replace($fullMatch, $replacement, $content);
            continue;
        }
        
        // 验证密码不为空（去除空白后）
        if (empty($requiredPassword)) {
            // 跳过空密码的块
            continue;
        }
        
        // 生成内容块的唯一ID（使用 SHA-256 而不是 MD5 以保持安全一致性）
        $blockId = substr(hash('sha256', $requiredPassword . $protectedContent . getBoldSecretSalt()), 0, 12);
        $blockId = sanitizeCategorySlugForCookie($blockId); // 确保Cookie名称安全
        $cookieName = 'bold_inline_verified_' . $blockId;
        
        // 检查是否已验证
        $verifiedHash = Typecho_Cookie::get($cookieName);
        $isVerified = false;
        
        if (!empty($verifiedHash) && !empty($requiredPassword)) {
            if (hash_equals(hash('sha256', $requiredPassword . getBoldSecretSalt()), $verifiedHash)) {
                $isVerified = true;
            }
        }
        
        // 处理密码提交
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inline_password_' . $blockId])) {
            $inputPassword = isset($_POST['inline_password_' . $blockId]) ? strval($_POST['inline_password_' . $blockId]) : '';
            
            if (!empty($requiredPassword) && $inputPassword === $requiredPassword) {
                $passwordHash = hash('sha256', $requiredPassword . getBoldSecretSalt());
                Typecho_Cookie::set($cookieName, $passwordHash, time() + 604800);
                $isVerified = true;
            }
        }
        
        if ($isVerified) {
            // 显示已解锁的内容
            $replacement = '<div class="p-4 border-l-4 border-green-500 bg-green-50 dark:bg-green-900/20 dark:border-green-400 mb-6">
                            <p class="font-bold text-green-700 dark:text-green-400 m-0">🔓 内容已解锁 / UNLOCKED</p>
                        </div>' . $protectedContent;
        } else {
            // 显示密码输入表单
            $errorMsg = '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inline_password_' . $blockId])) {
                $errorMsg = $lang === 'cn' ? '密码错误，请重试' : 'Incorrect password, please try again';
            }
            
            $placeholderText = $lang === 'cn' ? '输入密码解锁此内容...' : 'Enter password to unlock...';
            $submitText = $lang === 'cn' ? '解锁' : 'UNLOCK';
            
            $replacement = '
            <div class="inline-password-container my-6">
                <div class="inline-password-inner flex flex-col items-center justify-center text-center p-6">
                    <div class="text-4xl mb-3">🔐</div>
                    <h4 class="text-lg font-black uppercase mb-3">' . ($lang === 'cn' ? '密码保护内容' : 'PASSWORD PROTECTED') . '</h4>';
            
            if (!empty($errorMsg)) {
                $replacement .= '<div class="bg-red-100 border-2 border-red-500 text-red-700 px-3 py-2 mb-3 font-bold text-sm">' . $errorMsg . '</div>';
            }
            
            $replacement .= '<form method="post" class="w-full max-w-xs">
                        <input type="password" name="inline_password_' . $blockId . '" placeholder="' . $placeholderText . '" 
                            class="w-full p-2 font-bold border-2 border-black focus:outline-none focus:border-pink-500 mb-3 text-center text-sm dark:bg-[#121212] dark:text-white dark:border-[#10b981]" required>
                        <button type="submit" class="w-full bg-black text-white px-4 py-2 font-black text-sm uppercase tracking-wider hover:bg-pink-500 transition-colors border-2 border-black shadow-[2px_2px_0px_0px_#000] dark:bg-[#10b981] dark:text-black dark:border-[#10b981] dark:shadow-[2px_2px_0px_0px_#000]">
                            ' . $submitText . '
                        </button>
                    </form>
                </div>
            </div>
            <style>
                .inline-password-container {
                    background: repeating-linear-gradient(45deg, #fef08a, #fef08a 15px, #000 15px, #000 30px);
                    padding: 6px; border: 2px solid #000; box-shadow: 4px 4px 0px 0px #000;
                }
                .inline-password-inner { background: #fff; border: 2px solid #000; }
                body.dark-mode .inline-password-container {
                    background: repeating-linear-gradient(45deg, #064e3b, #064e3b 15px, #000 15px, #000 30px);
                    border-color: #10b981; box-shadow: 4px 4px 0px 0px #10b981;
                }
                body.dark-mode .inline-password-inner { background: #121212; border-color: #10b981; color: #e5e5e5; }
            </style>';
        }
        
        $content = str_replace($fullMatch, $replacement, $content);
    }
    
    return $content;
}

/**
 * 检查文章是否应该从首页隐藏（属于加密分类且设置了隐藏）
 * @param object $archive 文章对象
 * @return bool 返回true表示应该隐藏
 */
function shouldHideFromHome($archive) {
    $options = Helper::options();
    
    // 如果未启用隐藏功能，返回false
    if (empty($options->hideProtectedCategoriesFromHome) || $options->hideProtectedCategoriesFromHome == '0') {
        return false;
    }
    
    // 检查文章是否属于加密分类
    if (empty($options->passwordProtectedCategories) || empty($archive->categories)) {
        return false;
    }
    
    $protectedSlugs = array_map('trim', explode(',', $options->passwordProtectedCategories));
    
    foreach ($archive->categories as $category) {
        if (in_array($category['slug'], $protectedSlugs)) {
            return true;
        }
    }
    
    return false;
}

/**
 * 摘要输出 - 如果文章属于加密分类且需要隐藏摘要，显示提示信息
 * 隐藏摘要的条件：
 * - 如果 hideProtectedCategoriesFromHome 为开（文章在首页隐藏），或
 * - 如果 requireCategoryArchivePassword 为关（归档页不需要密码验证）
 * 则隐藏仅通过分类加密的文章的摘要，否则显示正常摘要
 */
function printExcerpt($archive, $length = 140) {
    $options = Helper::options();
    
    // 检查是否属于加密分类
    if (getProtectedCategorySlug($archive) !== null) {
        // 获取配置选项
        $hideFromHome = !empty($options->hideProtectedCategoriesFromHome) && $options->hideProtectedCategoriesFromHome == '1';
        $requireArchivePassword = empty($options->requireCategoryArchivePassword) || $options->requireCategoryArchivePassword == '1';

        // 判断是否需要隐藏摘要
        // 需求：如果首页隐藏开启，或分类归档无需密码，则不显示摘要内容
        $shouldHideExcerpt = $hideFromHome || !$requireArchivePassword;
        
        if ($shouldHideExcerpt) {
            $lang = $options->languageSetting;
            if (empty($lang)) $lang = 'en';
            $text = $lang === 'cn' ? '🔐 此文章内容受密码保护...' : '🔐 This content is password protected...';
            echo $text;
            return;
        }
    }
    
    $content = $archive->content;
    $content = preg_replace("/{hide}(.*?){\/hide}/sm", '', $content);
    $content = preg_replace("/{password:[^}]+}(.*?){\/password}/sm", '', $content);
    $content = strip_tags($content);
    echo Typecho_Common::subStr($content, 0, $length, '...');
}

/**
 * 自定义评论输出结构
 */
function threadedComments($comments, $options) {
    $commentClass = '';
    if ($comments->authorId) {
        if ($comments->authorId == $comments->ownerId) {
            $commentClass .= ' comment-by-author';
        } else {
            $commentClass .= ' comment-by-user';
        }
    }
?>
<li id="li-<?php $comments->theId(); ?>" class="comment-body<?php 
    if ($comments->levels > 0) {
        echo ' comment-child';
        $comments->levelsAlt(' comment-level-odd', ' comment-level-even');
    } else {
        echo ' comment-parent';
    }
    $comments->alt(' comment-odd', ' comment-even');
    echo $commentClass;
?> mb-8 list-none">
    <div id="<?php $comments->theId(); ?>" class="flex flex-col md:flex-row gap-4">
        <div class="flex-shrink-0">
            <div class="w-12 h-12 border-2 border-black overflow-hidden shadow-[2px_2px_0px_0px_#000]">
                <?php $comments->gravatar('48', ''); ?>
            </div>
        </div>
        <div class="flex-grow">
            <div class="bg-white border-2 border-black p-4 relative shadow-[4px_4px_0px_0px_#1a1a1a] transition-transform hover:-translate-y-1">
                <div class="absolute top-4 -left-2 w-4 h-4 bg-white border-b-2 border-l-2 border-black transform rotate-45 hidden md:block"></div>
                <div class="absolute -top-2 left-4 w-4 h-4 bg-white border-t-2 border-l-2 border-black transform rotate-45 md:hidden"></div>
                <div class="flex flex-wrap justify-between items-center mb-2 border-b-2 border-gray-100 pb-2">
                    <div class="flex items-center gap-2">
                        <span class="font-black text-lg uppercase"><?php $comments->author(); ?></span>
                        <?php if ($comments->authorId == $comments->ownerId): ?>
                            <span class="bg-black text-white text-[10px] px-1 font-bold">AUTHOR</span>
                        <?php endif; ?>
                    </div>
                    <span class="text-xs font-bold text-gray-500 font-mono"><?php $comments->date('Y-m-d H:i'); ?></span>
                </div>
                <div class="font-medium text-gray-800 prose prose-sm max-w-none mb-3">
                    <?php $comments->content(); ?>
                </div>
                <div class="text-right">
                    <?php $comments->reply('<span class="inline-block text-xs font-black bg-black text-white px-2 py-1 hover:bg-pink-500 transition-colors cursor-pointer border-2 border-transparent hover:border-black">REPLY</span>'); ?>
                </div>
            </div>
        </div>
    </div>
    <?php if ($comments->children) { ?>
        <div class="comment-children ml-0 md:ml-16 mt-4 border-l-4 border-gray-200 pl-4">
            <?php $comments->threadedComments($options); ?>
        </div>
    <?php } ?>
</li>
<?php } 

/**
 * 文章阅读量统计
 */
function getPostViews($archive) {
    $cid    = $archive->cid;
    $db     = Typecho_Db::get();
    $prefix = $db->getPrefix();
    if (!array_key_exists('views', $db->fetchRow($db->select()->from('table.contents')))) {
        $db->query('ALTER TABLE `' . $prefix . 'contents` ADD `views` INT(10) DEFAULT 0;');
        echo 0; return;
    }
    $row = $db->fetchRow($db->select('views')->from('table.contents')->where('cid = ?', $cid));
    if ($archive->is('single')) {
        $views = Typecho_Cookie::get('extend_contents_views');
        if (empty($views)) { $views = array(); } else { $views = explode(',', $views); }
        if (!in_array($cid, $views)) {
            $db->query($db->update('table.contents')->rows(array('views' => (int) $row['views'] + 1))->where('cid = ?', $cid));
            array_push($views, $cid);
            $views = implode(',', $views);
            Typecho_Cookie::set('extend_contents_views', $views);
        }
    }
    echo $row['views'];
}

/**
 * 获取阅读时间
 */
function getReadingTime($archive) {
    $content = $archive->content;
    $content = ($content === null) ? '' : strval($content);
    $text = trim(strip_tags($content));
    $textLen = mb_strlen($text, 'UTF-8');
    $readTime = ceil($textLen / 300);
    return max(1, $readTime);
}

/**
 * 获取相关文章
 */
function getRelatedPosts($archive, $limit = 3) {
    $db = Typecho_Db::get();
    $tags = $archive->tags;

    if (empty($tags)) {
        echo '<li class="p-3 border-2 border-dashed border-black text-gray-500 text-sm bg-gray-50">暂无相关推荐，看看别的吧。</li>';
        return;
    }

    // 收集 tag id
    $tagIds = array();
    foreach ($tags as $tag) {
        if (isset($tag['mid'])) $tagIds[] = $tag['mid'];
    }

    if (empty($tagIds)) {
        echo '<li class="p-3 border-2 border-dashed border-black text-gray-500 text-sm bg-gray-50">暂无相关推荐，看看别的吧。</li>';
        return;
    }

    // 获取站点 URL（用于生成伪静态回退链接）
    $options = Typecho_Widget::widget('Widget_Options');
    $siteUrl = rtrim($options->siteUrl ?? '', '/');

    // 查询：通过 relationships 找到同 tag 的文章，去重 contents.cid，按创建时间倒序
    $select = $db->select()->from('table.contents')
        ->join('table.relationships', 'table.contents.cid = table.relationships.cid')
        ->where('table.relationships.mid IN ?', $tagIds)
        ->where('table.contents.cid != ?', $archive->cid)
        ->where('table.contents.type = ?', 'post')
        ->where('table.contents.status = ?', 'publish')
        ->group('table.contents.cid')
        ->order('table.contents.created', Typecho_Db::SORT_DESC)
        ->limit($limit);

    $related = $db->fetchAll($select);

    if (empty($related)) {
        echo '<li class="p-3 border-2 border-dashed border-black text-gray-500 text-sm bg-gray-50">暂无相关推荐，看看别的吧。</li>';
        return;
    }

    // slugify（用于 post slug，保留小写行为）
    $slugify = function ($text) {
        $text = (string)$text;
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text) ?: $text;
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = strtolower($text); // post slug 通常小写
        if (empty($text)) return 'post-' . time();
        return $text;
    };

    // category 处理函数：清理不合法字符但不强制小写（保留原大小写以匹配站点 URL）
    $sanitizeCategorySegment = function ($text) {
        $text = (string)$text;
        // 将空白和特殊字符替换为短横
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        // 尝试把非 ASCII 字符转为近似 ASCII（可选），iconv 失败则保留原
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        if ($converted !== false) $text = $converted;
        // 去掉不安全字符，但不 strtolower
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        if ($text === '') return 'uncategorized';
        return $text;
    };

    foreach ($related as $row) {
        $post = Typecho_Widget::widget('Widget_Abstract_Contents')->push($row);

        // 优先使用已有 permalink/url
        $permalink = $post['permalink'] ?? $post['url'] ?? null;

        // 如果没有 permalink，按伪静态 /{category}/{slug}.html 生成
        if (empty($permalink)) {
            // 先尝试从 $post['categories'] 取得第一个 category 的 slug 或 name
            $category_segment = null;
            if (!empty($post['categories']) && is_array($post['categories'])) {
                $firstCat = reset($post['categories']);
                if (!empty($firstCat['slug'])) {
                    // 如果数据库里有 slug，直接使用（保留其原有大小写）
                    $category_segment = $firstCat['slug'];
                } elseif (!empty($firstCat['name'])) {
                    // 否则使用 name，经 sanitize 但保留大小写
                    $category_segment = $sanitizeCategorySegment($firstCat['name']);
                }
            }

            // 若没有，从数据库查 relationship -> metas 中的 category（取第一个）
            if (empty($category_segment) && !empty($post['cid'])) {
                $catRow = $db->fetchRow($db->select('table.metas.slug, table.metas.name')
                    ->from('table.relationships')
                    ->join('table.metas', 'table.relationships.mid = table.metas.mid')
                    ->where('table.relationships.cid = ?', $post['cid'])
                    ->where('table.metas.type = ?', 'category')
                    ->limit(1));
                if ($catRow) {
                    if (!empty($catRow['slug'])) {
                        $category_segment = $catRow['slug'];
                    } elseif (!empty($catRow['name'])) {
                        $category_segment = $sanitizeCategorySegment($catRow['name']);
                    }
                }
            }

            if (empty($category_segment)) {
                $category_segment = 'uncategorized';
            }

            // post slug：优先 post['slug']，否则从 title 生成（使用小写 slugify）
            $post_slug_raw = $post['slug'] ?? $post['title'] ?? $post['cid'] ?? '';
            $post_slug = $post['slug'] ?? $slugify($post_slug_raw);

            // 生成伪静态链接
            if (!empty($siteUrl)) {
                $permalink = $siteUrl . '/' . $category_segment . '/' . $post_slug . '.html';
            } else {
                $permalink = '/' . $category_segment . '/' . $post_slug . '.html';
            }
        }

        // 安全输出
        $permalink_escaped = htmlspecialchars($permalink ?? '#', ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $created = isset($post['created']) ? intval($post['created']) : 0;
        $date = $created ? date('Y-m-d', $created) : '';

        echo "<li>
                <a href=\"{$permalink_escaped}\" class='flex justify-between items-center border-2 border-black p-3 hover:bg-yellow-200 transition-colors group'>
                    <span class='font-bold truncate group-hover:text-pink-600 transition-colors'>{$title}</span>
                    <span class='text-xs font-mono whitespace-nowrap ml-2 bg-black text-white px-1'>{$date}</span>
                </a>
              </li>";
    }
}

/**
 * 输出带有独立颜色的分类标签 (新增)
 */
function printColoredCategories($archive) {
    // 颜色池：高饱和度背景色，适合搭配 text-white
    $colors = [
        'bg-blue-600',
        'bg-pink-600',
        'bg-purple-600', 
        'bg-green-600',
        'bg-red-600', 
        'bg-indigo-600',
        'bg-cyan-600',
        'bg-rose-600',
        'bg-emerald-600',
        'bg-fuchsia-600'
    ];
    
    if ($archive->categories) {
        foreach ($archive->categories as $category) {
            // 根据分类MID取模，保证颜色固定
            $colorIndex = $category['mid'] % count($colors);
            $colorClass = $colors[$colorIndex];
            
            echo '<a href="' . $category['permalink'] . '" class="' . $colorClass . ' text-white px-3 py-1 border-2 border-black shadow-[2px_2px_0px_0px_#000] hover:shadow-none hover:translate-x-[2px] hover:translate-y-[2px] transition-all dark:border-[#10b981] dark:shadow-[2px_2px_0px_0px_#10b981] mr-2 no-underline">' . $category['name'] . '</a>';
        }
    }
}

/**
 * SEO: 纯文本描述
 */
function get_seo_description($archive) {
    if ($archive->is('index')) { return Helper::options()->description; }
    if ($archive->is('post') || $archive->is('page')) {
        $description = '';
        if (isset($archive->excerpt) && is_string($archive->excerpt) && !empty($archive->excerpt)) {
            $description = $archive->excerpt;
        } else if (isset($archive->content) && is_string($archive->content)) {
            $description = $archive->content;
        }
        if (!is_string($description)) { $description = ''; }
        $description = strip_tags($description);
        $description = str_replace(array("\r\n", "\r", "\n"), "", $description);
        return mb_substr($description, 0, 150, 'utf-8') . '...';
    }
    if ($archive->is('category')) { return $archive->getDescription() ? $archive->getDescription() : $archive->getArchiveTitle(); }
    return Helper::options()->description;
}

/**
 * SEO: 封面图
 */
function get_og_image($archive) {
    $options = Helper::options();
    $default_img = !empty($options->defaultOgImage) ? $options->defaultOgImage : 'https://cdn.tailwindcss.com/img/card-top.jpg'; 
    if (($archive->is('post') || $archive->is('page'))) {
        $content = '';
        if (isset($archive->content) && is_string($archive->content)) { $content = $archive->content; }
        if (!empty($content)) {
            preg_match_all("/<img.*?src=\"(.*?)\".*?>/i", $content, $matches);
            if (isset($matches[1][0])) { return $matches[1][0]; }
        }
    }
    return $default_img;
}
?>