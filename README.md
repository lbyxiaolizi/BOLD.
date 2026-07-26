# BOLD.

<p align="center">
  <a href="https://github.com/lbyxiaolizi/BOLD.">
    <img alt="BOLD Theme" src="./BOLD.icon.png" width="220"/>
  </a>
</p>

<p align="center">
  <strong>A BOLD Neo-Brutalism Typecho Theme</strong>
</p>

<p align="center">
  一款简洁大胆的新粗野主义 (Neo-Brutalism) 风格 Typecho 主题
</p>

---

## ✨ Features / 功能特性

### 🎨 Design / 设计风格
- **Neo-Brutalism Style** - 新粗野主义设计风格，大胆的色彩和边框
- **Dark Mode Support** - 完整的暗黑模式支持，手动切换并在浏览器中记忆偏好
- **Responsive Design** - 全响应式设计，完美适配移动端和桌面端
- **TailwindCSS** - 使用 TailwindCSS 构建，现代化的样式系统

### 🔒 Password Protection System / 密码保护系统
- **Category Password Protection** - 分类密码保护，不同分类可设置不同密码
- **Individual Article Password** - 单篇文章独立密码，通过自定义字段设置
- **Inline Content Protection** - 文章内联密码保护 `{password:xxx}content{/password}`
- **Category Archive Protection** - 分类归档页面也需要密码验证
- **Homepage Filtering** - 可选择隐藏加密分类文章在首页的显示
- **Excerpt Protection** - 加密内容的摘要自动显示保护提示，防止信息泄露

### 💬 Comment Features / 评论功能
- **Comment to View** - 评论后可见功能 `{hide}content{/hide}`
- **Cloudflare Turnstile** - 支持 Cloudflare Turnstile 人机验证
- **Nested Comments** - 嵌套评论显示，清晰的层级结构
- **Author Badge** - 作者标识，区分文章作者的评论

### 📝 Content Features / 内容功能
- **Reading Time** - 自动计算阅读时间
- **Post Views** - 文章浏览量统计
- **Related Posts** - 基于标签的相关文章推荐
- **Table of Contents** - 文章目录自动生成
- **Colored Categories** - 彩色分类标签，每个分类有独特颜色
- **SEO Optimization** - SEO 友好的描述和 Open Graph 标签

### 🌐 Internationalization / 国际化
- **Bilingual Interface** - 双语界面支持（中文/英文）
- **Language Switch** - 后台可切换界面语言
- **Localized Text** - 所有界面文本都支持本地化

### 🎁 Additional Features / 额外功能
- **Donation/Reward** - 打赏功能，支持微信和支付宝二维码
- **Social Links** - 社交链接（GitHub, Bilibili, Email）
- **Custom Code Injection** - 自定义头部/底部 HTML 代码
- **ICP Filing** - ICP 备案号显示
- **Timeline Archive** - 时间轴归档页面模板
- **Links Page** - 友情链接页面模板

---

## 📦 Installation / 安装

运行环境：Typecho 1.2.1 或兼容版本（必须提供随机站点 `secret`），PHP 7.3 及以上。

1. **Download** - 下载主题文件
   ```bash
   git clone https://github.com/lbyxiaolizi/BOLD.git
   ```

2. **Upload** - 上传到 Typecho 主题目录
   ```
   /usr/themes/BOLD/
   ```

3. **Activate** - 在 Typecho 后台启用主题
   - 进入"控制台" → "外观" → 启用 "BOLD" 主题

4. **Configure** - 配置主题选项
   - 进入"控制台" → "外观" → "设置外观"

### Upgrade Notes / 升级说明

- 升级时必须完整上传新增的 `inc/`、`assets/` 目录以及根目录模板文件，不能只覆盖 `functions.php`。
- 新版解锁 Cookie 使用站点密钥签名、带 7 天期限的 `v2` HMAC 票据。所有旧格式 Cookie 会立即失效，访客需要重新输入一次密码。
- 匿名评论后可见现在要求服务器签名的评论回执；升级前已评论的匿名访客需要重新提交一次评论，登录用户不受影响。
- 后台重新保存一次主题设置后可看到 Google Fonts 与 Feed 保护开关；未保存时仍使用安全默认值。
- 升级前如使用了整页缓存或 CDN，请在部署后清理旧缓存，避免继续返回升级前生成的页面或订阅源。

---

## ⚙️ Configuration / 配置选项

### Basic Settings / 基础设置
- **Language Setting** - 界面语言（English/中文）
- **Logo Text** - 站点 Logo 文字（支持 HTML）
- **Favicon URL** - 浏览器图标地址
- **Avatar URL** - 个人头像地址
- **Description** - 个人简介
- **Social Links** - GitHub, Bilibili, Email 链接
- **ICP Number** - ICP 备案号

### Advanced Settings / 高级设置
- **Custom Head HTML** - 自定义头部 HTML（统计代码、CSS等）
- **Custom Footer HTML** - 自定义底部 HTML（JS 脚本等）
- **Default OG Image** - 默认社交分享封面图
- **Sidebar Author Name** - 侧边栏作者名称

### Comment Settings / 评论设置
- **Turnstile Site Key** - Cloudflare Turnstile 站点密钥
- **Turnstile Secret Key** - Cloudflare Turnstile 密钥

### Donation Settings / 打赏设置
- **WeChat QR URL** - 微信收款码地址
- **Alipay QR URL** - 支付宝收款码地址

### Password Protection Settings / 密码保护设置
- **Global Password** - 全站加密密码
- **Protected Categories** - 加密分类列表（逗号分隔）
- **Category Passwords** - 分类独立密码设置（每行一个）
- **Homepage Display** - 加密分类文章在首页的显示（隐藏/显示）
- **Feed Protection** - RSS/Atom 文章与评论订阅源中的受保护内容脱敏（默认开启）

### Performance Settings / 性能设置
- **Google Fonts** - 可关闭 Google Fonts（中国大陆站点建议关闭，使用系统字体）
- **Conditional Loading** - Prism 脚本、MathJax 与 Mermaid 按内容加载；MathJax 只识别成对数学定界符，不会因普通价格中的 `$` 加载
- **Static Assets** - Tailwind/typography 已离线编译到 `assets/css/tailwind.min.css`，主题样式与脚本可被浏览器长期缓存

修改模板中的 Tailwind 类名后，用固定版本依赖重新生成 CSS：

```bash
npm ci
npm run build:css
```

部署现有发行包不需要 Node.js；仓库已包含编译后的 CSS。

### Page Fields / 页面自定义字段
- **hideProfile** - 独立页面添加自定义字段 `hideProfile = 1` 可隐藏页首的个人简介卡片
- **password** - 文章或独立页面添加自定义字段 `password` 可设置单篇密码；默认模板、时间轴和友情链接模板遵循相同门禁

---

## 📖 Usage Guide / 使用指南

### Comment to View / 评论后可见

在文章内容中使用以下标签：

```markdown
这是公开内容

{hide}
这部分内容需要评论后才能查看
{/hide}

这又是公开内容
```

匿名访客提交评论后会获得绑定文章、评论记录和规范化邮箱的 7 天签名回执；只有该条评论处于已审核状态时才会解锁。单独伪造 Typecho 的记忆邮箱 Cookie、复制其他文章回执或借用同邮箱历史评论均无效。登录用户仍按自己的已审核评论记录判断。

### Password Protection / 密码保护

#### 1. Category Password / 分类密码

在主题设置中配置：

```
加密分类: private,secret
分类独立密码设置:
private:password123
secret:secret456
```

#### 2. Article Password / 文章密码

在文章编辑页面添加自定义字段：
- 字段名：`password`
- 字段值：`your_password`

#### 3. Inline Password / 内联密码

在文章内容中使用：

```markdown
这是公开内容

{password:mypass123}
这部分需要密码 mypass123 才能查看
{/password}

这又是公开内容
```

详细密码管理指南请查看：[CATEGORY_PASSWORD_GUIDE.md](./CATEGORY_PASSWORD_GUIDE.md)

### Special Pages / 特殊页面

#### Timeline Archive / 时间轴归档

创建新页面，选择模板 "时间轴归档"

#### Links Page / 友情链接

创建新页面，选择模板 "友情链接"，在页面内容中添加链接信息。

---

## 🎨 Customization / 自定义

### Custom Styles / 自定义样式

在主题设置的"自定义头部 HTML"中添加：

```html
<style>
/* Your custom CSS */
</style>
```

### Custom Scripts / 自定义脚本

在主题设置的"自定义底部 HTML"中添加：

```html
<script>
// Your custom JavaScript
</script>
```

---

## 🔐 Security Features / 安全特性

- **HMAC Unlock Tokens** - 解锁凭据为使用 Typecho 站点密钥签名、带 7 天期限的 HMAC 票据，不再保存可永久重放的裸密码哈希
- **Hardened Cookies** - 解锁 Cookie 使用 `HttpOnly`、`SameSite=Lax`，HTTPS 请求同时启用 `Secure`
- **RSS Feed Protection** - 文章、分类和评论订阅源中的受保护内容自动脱敏，内联密码及 `{hide}` 内容不会进入 Feed
- **Timing-Attack Resistant** - 密码比较使用 `hash_equals`，失败附加随机延迟
- **Cookie Sanitization** - Cookie 名称清理防止注入，中文分类 slug 自动哈希隔离
- **Cache Safety** - 受保护页面自动发送 `Cache-Control: private` / `Vary: Cookie`，兼容整页缓存/CDN
- **Least-Privilege Bypass** - 仅编辑及以上登录用户可免密查看，订阅者不再绕过
- **Signed Reply Proof** - 匿名评论后可见同时校验服务器签名回执与具体已审核评论，记忆邮箱不能单独充当授权
- **Fail-Closed Markers** - 保护标记嵌套或闭合错误时从开启处停止公开输出，避免作者笔误导致正文外泄
- **No Content Leakage** - 未解锁时列表、SEO 描述和社交图片不读取受保护正文；最新评论始终排除受保护文章下的讨论

完整审计记录、验证状态和残余边界见 [security.md](./security.md)。

---

## 📱 Browser Support / 浏览器支持

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers

---

## 🤝 Contributing / 贡献

欢迎提交 Issue 和 Pull Request！

---

## 📄 License / 许可证

MIT License

---

## 🙏 Credits / 致谢

- [Typecho](https://typecho.org/) - 优雅的博客系统
- [TailwindCSS](https://tailwindcss.com/) - 实用优先的 CSS 框架
- [Cloudflare Turnstile](https://www.cloudflare.com/products/turnstile/) - 人机验证服务

---

## 📞 Support / 支持

如有问题或建议，请提交 [Issue](https://github.com/lbyxiaolizi/BOLD./issues)

---

**Enjoy BOLD! 享受大胆的设计！** 🎨✨
