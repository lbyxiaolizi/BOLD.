# 分类独立密码管理指南 / Category Password Management Guide

## 中文说明

### 功能介绍

本主题提供三种密码保护方式：

1. **分类密码保护** - 为不同的加密分类设置不同的密码
2. **单篇文章密码保护** - 为特定文章设置独立密码
3. **内联密码保护** - 在文章内部保护特定段落

访客输入正确密码后，可以访问相应的加密内容。浏览器保存的是使用 Typecho 站点密钥签名、有效期为 7 天的 HMAC 解锁票据，不是密码或可永久重放的裸哈希。

### 一、分类密码保护

#### 配置步骤

1. **进入主题设置**
   - 登录 Typecho 后台
   - 进入"控制台" → "外观" → "设置外观"

2. **设置加密分类**
   - 在"加密分类 (用英文逗号分隔)"字段中，输入需要密码保护的分类别名（slug）
   - 多个分类用英文逗号分隔
   - 例如：`private,secret,vip`

3. **设置分类独立密码**
   - 在"分类独立密码设置"字段中，为每个分类设置不同的密码
   - 格式：`分类slug:密码`，每行一个
   - 例如：
     ```
     private:password123
     secret:mySecret456
     vip:vippass2024
     ```

4. **设置全站密码（可选）**
   - 在"全站加密密码"字段中设置一个全局密码
   - 如果某个加密分类没有设置独立密码，将使用这个全局密码
   - 如果您想让所有加密分类使用相同密码，只需设置全站密码即可
   - 已列入加密分类但既没有独立密码也没有全站密码时，主题会保持锁定；请补全配置后再发布

5. **设置加密分类在首页的显示**
   - 在"加密分类文章在首页的显示"选项中，选择"隐藏"或"显示"
   - 选择"隐藏"：加密分类的文章不会出现在首页列表中
   - 选择"显示"（默认）：加密分类的文章会在首页显示，但摘要会显示提示信息

6. **设置加密分类的归档页面是否需要密码验证**
   - 在"加密分类的归档页面是否需要密码验证"选项中，选择"需要"或"不需要"
   - 选择"需要"（默认）：访问加密分类归档页面时需要先输入密码才能查看文章列表
   - 选择"不需要"：访问加密分类归档页面时不需要密码，可以直接看到文章列表
   - 注意：当选择"不需要"时，为防止内容通过摘要泄露，文章摘要将自动隐藏显示为密码保护提示

#### 摘要显示规则

为了防止加密文章内容通过摘要泄露，系统采用以下规则：
- 未解锁时，受全站密码、分类密码或文章自定义字段 `password` 保护的内容，在首页、分类、标签、搜索和作者列表中一律显示保护提示
- 解锁后可以显示该文章的普通摘要；选择"首页隐藏"时，加密分类文章不会进入首页列表
- 即使分类归档页面设置为"不需要"密码，列表摘要仍保持隐藏；该选项只决定是否显示分类页门禁，不会公开正文摘要

#### 分类页密码保护

- 访问加密分类的归档页面（例如 `/category/private`）时也需要输入密码
- 输入正确密码后才能查看该分类下的文章列表
- 密码验证状态与文章页共享（同一个分类，验证一次即可）

### 二、单篇文章密码保护

#### 使用方法

1. **编辑文章时添加自定义字段**
   - 在文章或独立页面编辑界面，找到"自定义字段"区域
   - 添加字段名：`password`
   - 字段值：设置您想要的密码，例如：`myarticlepass`
   - 默认独立页、时间轴和友情链接页面模板使用相同的页面级密码门禁

2. **密码优先级**
   - 文章自定义字段密码 > 分类密码 > 全站密码
   - 如果文章设置了独立密码，将优先使用文章密码

### 三、内联密码保护（文章内部分段落）

#### 使用语法

在文章内容中使用以下格式保护特定段落：

```
{password:您的密码}
这里是需要密码保护的内容
可以是多行
可以包含图片、链接等任何内容
{/password}
```

#### 示例

```markdown
这是公开的内容，所有人都可以看到。

{password:secret123}
这段内容需要输入密码 "secret123" 才能查看。
这里可以放置敏感信息、会员专享内容等。
{/password}

这又是公开内容了。

{password:another456}
这是另一个密码保护的段落，密码是 "another456"。
每个密码块可以设置不同的密码。
{/password}
```

#### 特点

- 同一篇文章可以有多个不同密码的保护块
- 每个密码块独立验证，互不影响
- 已验证的密码块会显示解锁提示
- 在列表页不会显示密码保护的内容
- 标记嵌套或遗漏闭合标签时按保密失败处理：从未闭合的开启标记处停止公开输出

### 使用场景示例

**场景 1：不同分类不同密码**
```
加密分类: private,secret
分类独立密码设置:
private:abc123
secret:xyz789
```
结果：
- private 分类的文章需要输入 `abc123`
- secret 分类的文章需要输入 `xyz789`

**场景 2：单篇文章使用独立密码**
```
文章 A：设置自定义字段 password = article123
文章 A 属于 private 分类，private 分类密码是 abc123
```
结果：
- 文章 A 需要输入 `article123`（优先使用文章独立密码）
- private 分类的其他文章需要输入 `abc123`

**场景 3：在文章内保护特定段落**
```markdown
这是公开内容。

{password:part1}
这段需要密码 "part1" 才能查看。
{/password}

这也是公开内容。

{password:part2}
这段需要密码 "part2" 才能查看。
{/password}
```
结果：
- 每个密码块独立验证
- 访客可以只解锁部分内容

### 技术说明

- Cookie 中保存的是使用 Typecho 站点密钥签名、有效期为 7 天的 HMAC 票据，不保存明文密码或裸 SHA-256 密码哈希
- 解锁 Cookie 使用 `HttpOnly`、`SameSite=Lax`，HTTPS 请求同时启用 `Secure`；主题要求 Typecho 提供安装时随机生成的站点 `secret`，缺失时拒绝签发票据
- 票据与当前所需密码绑定；分类 slug 和内联内容块使用隔离的 Cookie 名称，中文 slug 也不会相互冲突
- 页面级密码仅允许**编辑及以上**登录用户免密查看；文章作者仍可查看自己文章中的评论后可见及内联密码块
- 匿名评论后可见要求服务器签名的 7 天回执与对应的已审核评论同时成立；伪造记忆邮箱、跨文章复制回执或借用同邮箱历史评论均不会解锁，升级前的匿名评论者需要重新提交一次评论
- 受保护响应发送 `Cache-Control: private, no-store` 与 `Vary: Cookie`；部署在 CDN/整页缓存之后时仍应确认上游遵守这些响应头
- 密码优先级：文章独立密码 > 分类密码 > 全站密码

---

## English Guide

### Feature Introduction

This theme provides three types of password protection:

1. **Category Password Protection** - Set different passwords for different encrypted categories
2. **Individual Article Password Protection** - Set independent passwords for specific articles
3. **Inline Password Protection** - Protect specific paragraphs within articles

Visitors can access protected content after entering the correct password. The browser stores a 7-day HMAC unlock ticket signed with Typecho's site secret, not the password or a permanently replayable bare hash.

### 1. Category Password Protection

#### Configuration Steps

1. **Access Theme Settings**
   - Log in to Typecho admin panel
   - Navigate to "Dashboard" → "Appearance" → "Theme Settings"

2. **Set Encrypted Categories**
   - In the "Encrypted Categories (comma separated)" field, enter the category slugs that need password protection
   - Separate multiple categories with commas
   - Example: `private,secret,vip`

3. **Set Category-Specific Passwords**
   - In the "Category Password Settings" field, set different passwords for each category
   - Format: `category-slug:password`, one per line
   - Example:
     ```
     private:password123
     secret:mySecret456
     vip:vippass2024
     ```

4. **Set Global Password (Optional)**
   - In the "Global Encryption Password" field, set a global password
   - If an encrypted category doesn't have a specific password, it will use this global password
   - If you want all encrypted categories to use the same password, just set the global password
   - A protected category with neither a category password nor a global password remains locked; complete the configuration before publishing it

5. **Set Homepage Display for Encrypted Categories**
   - In "Encrypted category articles on homepage display" option, select "Hide" or "Show"
   - Select "Hide": Articles from encrypted categories won't appear on the homepage list
   - Select "Show" (default): Articles from encrypted categories will appear on homepage, but excerpt shows a protection notice

6. **Set Category Archive Page Password Verification**
   - In "Require password for category archive page" option, select "Required" or "Not Required"
   - Select "Required" (default): Accessing encrypted category archive pages requires password before viewing article list
   - Select "Not Required": Accessing encrypted category archive pages doesn't require password, article list is visible
   - Note: When set to "Not Required", to prevent content leakage through excerpts, article excerpts will automatically be hidden and show password protection notice

#### Excerpt Display Rules

To prevent encrypted article content from leaking through excerpts, the system follows these rules:
- Until unlocked, content protected by a global, category, or per-entry `password` field always shows a protection notice on home, category, tag, search, and author listings
- After a matching unlock, the normal excerpt may be shown; choosing "Hide" keeps protected-category entries out of the homepage listing
- Setting the category archive gate to "Not Required" exposes only the listing, not protected excerpts or article bodies

#### Category Page Password Protection

- Accessing an encrypted category's archive page (e.g., `/category/private`) also requires password
- Must enter the correct password to view the article list in that category
- Password verification status is shared with article pages (verify once for the same category)

### 2. Individual Article Password Protection

#### How to Use

1. **Add Custom Field When Editing Article**
   - In the post or page editor, find the "Custom Fields" section
   - Add field name: `password`
   - Field value: Set your desired password, e.g., `myarticlepass`
   - Default pages, timeline pages, and links pages enforce the same page-level password gate

2. **Password Priority**
   - Article custom field password > Category password > Global password
   - If an article has its own password, it will be used first

### 3. Inline Password Protection (Protect Specific Paragraphs)

#### Syntax

Use the following format in article content to protect specific paragraphs:

```
{password:YourPassword}
This is the content that requires password protection
Can be multiple lines
Can contain images, links, and any other content
{/password}
```

#### Examples

```markdown
This is public content that everyone can see.

{password:secret123}
This paragraph requires password "secret123" to view.
You can place sensitive information, member-exclusive content, etc. here.
{/password}

This is public content again.

{password:another456}
This is another password-protected paragraph with password "another456".
Each password block can have different passwords.
{/password}
```

#### Features

- Multiple password-protected blocks with different passwords in one article
- Each password block is verified independently
- Verified blocks show an unlock indicator
- Password-protected content is hidden in article lists
- Nested or unclosed protection markers fail closed: public output stops at the unmatched opening marker

### Usage Examples

**Scenario 1: Different Passwords for Different Categories**
```
Encrypted Categories: private,secret
Category Password Settings:
private:abc123
secret:xyz789
```
Result:
- Articles in the "private" category require password `abc123`
- Articles in the "secret" category require password `xyz789`

**Scenario 2: Individual Article with Its Own Password**
```
Article A: Set custom field password = article123
Article A belongs to "private" category, which has password abc123
```
Result:
- Article A requires password `article123` (article password takes priority)
- Other articles in "private" category require password `abc123`

**Scenario 3: Protect Specific Paragraphs Within Article**
```markdown
This is public content.

{password:part1}
This paragraph requires password "part1" to view.
{/password}

This is also public content.

{password:part2}
This paragraph requires password "part2" to view.
{/password}
```
Result:
- Each password block is verified independently
- Visitors can unlock only specific parts of the content

### Technical Details

- Cookies contain 7-day HMAC tickets signed with Typecho's site secret; they do not contain plaintext passwords or bare SHA-256 password hashes
- Unlock cookies use `HttpOnly` and `SameSite=Lax`, plus `Secure` on HTTPS requests; the theme requires Typecho's installation-generated site `secret` and refuses to issue tickets when it is missing
- Tickets are bound to the currently required password; category slugs and inline blocks use isolated cookie names, including non-ASCII slugs
- Only logged-in users with **editor or higher** privileges bypass page-level password gates; authors may still view reply-only and inline-password blocks in their own posts
- Anonymous reply-only access requires both a 7-day server-signed receipt and the exact approved comment; a forged remembered email, a receipt copied from another post, or an older comment sharing the email cannot unlock it, and pre-upgrade anonymous commenters must submit again
- Protected responses send `Cache-Control: private, no-store` and `Vary: Cookie`; verify that any upstream CDN or full-page cache honors these headers
- Password priority: Article password > Category password > Global password

---

## RSS/Atom 订阅源保护

Typecho 的订阅源不经过主题模板渲染，且内部路由可能在生成条目时发生切换。主题通过内容钩子识别主订阅源、分类订阅源、文章评论订阅源及 `/feed/comments/`，并自动：

1. 将受密码保护（全站密码 / 加密分类 / 文章自定义字段 `password`）的文章正文替换为保护提示；
2. 对所有文章剥离 `{hide}...{/hide}` 与 `{password:...}...{/password}` 标记块，内联明文密码不会随订阅源外泄；
3. 将受保护文章下的评论内容替换为提示，避免密码墙后的讨论从评论订阅源公开。

可在主题设置「RSS/Atom 订阅源中的加密文章」中关闭整体脱敏（不建议）。文章**标题、发布时间和永久链接**仍会出现在订阅源中，与归档页的公开元数据一致，因此不要在标题中写入秘密。升级前已被 Feed 阅读器、搜索引擎或 CDN 缓存的内容无法由主题主动撤回，部署后应清理可控缓存。

## 解锁凭据说明

密码验证成功后写入的 Cookie 是使用 Typecho 随机站点密钥签名、带 7 天期限的 `v2` HMAC 票据。票据过期、修改对应密码或轮换 Typecho 站点密钥后立即失效。所有旧格式 Cookie 均不兼容，升级后访客需要重新输入一次密码。已登录用户中仅**编辑及以上**权限可免密通过页面级门禁。
