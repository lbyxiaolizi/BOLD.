# 分类独立密码管理指南 / Category Password Management Guide

## 中文说明

### 功能介绍

本主题提供三种密码保护方式：

1. **分类密码保护** - 为不同的加密分类设置不同的密码
2. **单篇文章密码保护** - 为特定文章设置独立密码
3. **内联密码保护** - 在文章内部保护特定段落

访客输入正确密码后，可以访问相应的加密内容。密码验证通过 Cookie 保存，有效期为 7 天。

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

5. **设置加密分类在首页的显示**
   - 在"加密分类文章在首页的显示"选项中，选择"隐藏"或"显示"
   - 选择"隐藏"：加密分类的文章不会出现在首页列表中
   - 选择"显示"（默认）：加密分类的文章会在首页显示，但摘要会显示提示信息

#### 分类页密码保护

- 访问加密分类的归档页面（例如 `/category/private`）时也需要输入密码
- 输入正确密码后才能查看该分类下的文章列表
- 密码验证状态与文章页共享（同一个分类，验证一次即可）

### 二、单篇文章密码保护

#### 使用方法

1. **编辑文章时添加自定义字段**
   - 在文章编辑页面，找到"自定义字段"区域
   - 添加字段名：`password`
   - 字段值：设置您想要的密码，例如：`myarticlepass`

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

- 密码验证通过 Cookie 保存，有效期为 7 天
- 不同分类/文章/内容块的密码验证是独立的（使用不同的 Cookie）
- 已登录的用户（文章作者）可以直接查看所有加密内容
- 密码使用 SHA-256 哈希加密存储在 Cookie 中
- 密码优先级：文章独立密码 > 分类密码 > 全站密码

---

## English Guide

### Feature Introduction

This theme provides three types of password protection:

1. **Category Password Protection** - Set different passwords for different encrypted categories
2. **Individual Article Password Protection** - Set independent passwords for specific articles
3. **Inline Password Protection** - Protect specific paragraphs within articles

Visitors can access encrypted content after entering the correct password. Password verification is stored in cookies with a 7-day expiration.

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

5. **Set Homepage Display for Encrypted Categories**
   - In "Encrypted category articles on homepage display" option, select "Hide" or "Show"
   - Select "Hide": Articles from encrypted categories won't appear on the homepage list
   - Select "Show" (default): Articles from encrypted categories will appear on homepage, but excerpt shows a protection notice

#### Category Page Password Protection

- Accessing an encrypted category's archive page (e.g., `/category/private`) also requires password
- Must enter the correct password to view the article list in that category
- Password verification status is shared with article pages (verify once for the same category)

### 2. Individual Article Password Protection

#### How to Use

1. **Add Custom Field When Editing Article**
   - In the article editor, find the "Custom Fields" section
   - Add field name: `password`
   - Field value: Set your desired password, e.g., `myarticlepass`

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

- Password verification is stored in cookies with a 7-day expiration
- Password verification for different categories/articles/content blocks is independent (using different cookies)
- Logged-in users (article authors) can directly view all encrypted content
- Passwords are stored in cookies using SHA-256 hash encryption
- Password priority: Article password > Category password > Global password
