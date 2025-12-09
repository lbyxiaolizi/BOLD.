# 分类独立密码管理指南 / Category Password Management Guide

## 中文说明

### 功能介绍

现在您可以为不同的加密分类设置不同的密码。这意味着：
- 每个加密分类可以有自己独立的密码
- 访客输入正确的分类密码后，可以访问该分类下的所有文章
- 如果某个分类没有设置独立密码，将使用全站加密密码（如果设置了）

### 配置步骤

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

**场景 2：部分分类使用独立密码，部分使用全站密码**
```
全站加密密码: defaultpass
加密分类: private,secret,normal
分类独立密码设置:
private:specialpass
```
结果：
- private 分类的文章需要输入 `specialpass`
- secret 和 normal 分类的文章需要输入 `defaultpass`

**场景 3：仅使用全站密码（向后兼容）**
```
全站加密密码: mypassword
加密分类: private,secret
分类独立密码设置: (留空)
```
结果：
- 所有加密分类的文章都需要输入 `mypassword`

### 技术说明

- 密码验证通过 Cookie 保存，有效期为 7 天
- 不同分类的密码验证是独立的（使用不同的 Cookie）
- 已登录的用户可以直接查看所有加密内容
- 密码使用 SHA-256 哈希加密存储在 Cookie 中

---

## English Guide

### Feature Introduction

You can now set different passwords for different encrypted categories. This means:
- Each encrypted category can have its own independent password
- Visitors can access all articles in a category after entering the correct category password
- If a category doesn't have a specific password set, it will use the global site password (if configured)

### Configuration Steps

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

**Scenario 2: Some Categories Use Specific Passwords, Others Use Global Password**
```
Global Encryption Password: defaultpass
Encrypted Categories: private,secret,normal
Category Password Settings:
private:specialpass
```
Result:
- Articles in "private" category require password `specialpass`
- Articles in "secret" and "normal" categories require password `defaultpass`

**Scenario 3: Only Use Global Password (Backward Compatible)**
```
Global Encryption Password: mypassword
Encrypted Categories: private,secret
Category Password Settings: (leave empty)
```
Result:
- All articles in encrypted categories require password `mypassword`

### Technical Details

- Password verification is stored in cookies with a 7-day expiration
- Password verification for different categories is independent (using different cookies)
- Logged-in users can directly view all encrypted content
- Passwords are stored in cookies using SHA-256 hash encryption
