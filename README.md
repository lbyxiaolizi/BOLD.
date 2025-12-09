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
- **Dark Mode Support** - 完整的暗黑模式支持，自动适配系统主题
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

- **SHA-256 Hashing** - 密码使用 SHA-256 哈希加密
- **CSRF Protection** - 严格的 CSRF 保护
- **Cookie Sanitization** - Cookie 名称清理防止注入
- **Timing-Attack Resistant** - 防时序攻击的密码比较
- **Secure Fallback** - 未配置密码时的安全回退机制
- **No Content Leakage** - 加密内容摘要不泄露信息

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
