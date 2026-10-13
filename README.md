# BOLD.

<p align="center">
  <a href="https://github.com/lbyxiaolizi/BOLD.">
    <img alt="BOLD Theme" src="./BOLD.icon.png" width="220"/>
  </a>
</p>

<p align="center"><strong>A BOLD Neo-Brutalism Typecho Theme</strong></p>
<p align="center">一款简洁大胆的新粗野主义（Neo-Brutalism）风格 Typecho 主题，内置中 / 英双语界面与多层级内容保护。</p>

## 文档导航

| 场景 | 文档链接 |
| --- | --- |
| 首次安装、版本升级、目录结构与故障排查 | [安装与升级](docs/installation.md) |
| 后台外观设置项、默认值与文章自定义字段 | [配置说明](docs/configuration.md) |
| 评论后可见、独立页面模板、扩展语法与自定义代码 | [内容与页面用法](docs/usage.md) |
| 全站、分类、单篇及正文段落的密码门禁与 Feed 保护 | [密码保护指南](docs/password-protection.md) · [English Guide](docs/password-protection.en.md) |
| 项目目录职责、本地构建 CSS 与回归测试 | [开发与验证](docs/development.md) |
| 历史安全审计记录、修复详情与生产部署边界 | [安全审计记录](security.md) |

## 核心特性

- **视觉与交互**：鲜明的粗黑边框与高对比色彩碰撞、自适应响应式布局、自动记忆偏好的暗色模式（Dark Mode）、移动端抽屉菜单，以及完备的键盘焦点与无障碍跳过链接。
- **沉浸阅读体验**：字数与阅读耗时估算、文章阅读量统计、基于标签的相关文章推荐、桌面端粘性目录（Sticky TOC）与移动端折叠目录、上下文篇导航、一键复制链接与分页快速跳转。
- **多层级内容保护**：支持全站密码、分类独立密码、单篇文章 / 页面密码（自定义字段）、正文内联密码块（`{password:...}`）与评论后可见（`{hide}`），并在列表摘要、SEO 元信息及 RSS/Atom 订阅源中自动执行脱敏。
- **评论与站点扩展**：支持多级嵌套评论、博主的作者标识、Cloudflare Turnstile 人机验证、微信 / 支付宝打赏二维码、社交主页图标、页脚 ICP 备案号，以及开箱即用的 SEO、Open Graph 与 JSON-LD 结构化数据。
- **页面模板与按需资源**：内置时间轴归档（Timeline）与友情链接（Links）模板；预编译本地 Tailwind CSS、自托管图片灯箱（ViewImage），并按页面内容动态加载 Prism.js 代码高亮、MathJax 数学公式与 Mermaid 流程图。

## 快速开始

### 环境要求

- **Typecho**：`1.2.1` 或兼容版本（密码签名功能依赖安装时生成的非空随机站点 `secret`）。
- **PHP**：`7.3` 及以上版本。
- **Node.js**：仅在修改 Tailwind 类名并重新编译 CSS 时需要；仓库已内置编译产物 `assets/css/tailwind.min.css`，直接部署无需安装 Node.js。

### 安装步骤

1. 进入 Typecho 站点的 `usr/themes/` 目录并克隆仓库（确保文件夹名称为 `BOLD`）：

   ```bash
   git clone https://github.com/lbyxiaolizi/BOLD. BOLD
   ```

2. 登录 Typecho 后台，进入 **「控制台 → 外观」** 启用 **BOLD** 主题。
3. 打开 **「设置外观」**，根据需要配置语言、个人资料与安全选项并保存（首次打开设置页会自动检查并迁移文章阅读量 `views` 字段）。

关于压缩包安装、旧版升级注意事项及常见问题排查，请查阅 [安装与升级](docs/installation.md)。

## 贡献与反馈

欢迎通过 [GitHub Issues](https://github.com/lbyxiaolizi/BOLD./issues) 报告问题或提出建议，也欢迎直接提交 Pull Request。

- 提交代码前请阅读 [开发与验证](docs/development.md)，并在 PR 中说明问题背景、改动范围与已执行的回归测试结果。
- 主题兼容现代主流浏览器（Chrome、Edge、Firefox、Safari 及移动端浏览器）；涉及前端样式与交互的修改请同时验证桌面端与移动端视口。

## 许可证与致谢

本项目基于 [MIT License](LICENSE) 开源。感谢 [Typecho](https://typecho.org/)、[Tailwind CSS](https://tailwindcss.com/) 与 [Cloudflare Turnstile](https://www.cloudflare.com/products/turnstile/)。
