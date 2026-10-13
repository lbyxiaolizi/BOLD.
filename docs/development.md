# 开发与验证

[返回项目首页](../README.md) · [安装与升级](installation.md) · [安全审计记录](../security.md)

本文档面向参与 BOLD 主题开发或二次定制的贡献者。以下所有命令均默认在**主题仓库根目录**下执行。主题线上运行仅依赖 Typecho 与 PHP；Node.js / npm 仅用于本地编译 Tailwind CSS 及运行浏览器端回归测试。

## 项目目录结构

| 路径 | 核心职责 |
| --- | --- |
| `functions.php` | 主题入口：版本号与全局常量定义、后台外观配置面板（`themeConfig`）、`inc/` 模块挂载与 Typecho 钩子注册 |
| `index.php` / `archive.php` | 首页文章流及分类、标签、搜索、作者等归档列表页模板 |
| `post.php` / `page.php` | 单篇文章详情页与默认独立页面模板 |
| `page-archive.php` / `page-links.php` | 时间轴归档（Timeline）与友情链接（Links）自定义独立页面模板 |
| `header.php` / `footer.php` / `sidebar.php` / `comments.php` / `404.php` | 公共页面骨架、静态资源与 SEO 输出、侧边栏、嵌套评论区及 404 错误页 |
| `inc/i18n.php` | 中 / 英双语界面词条字典与翻译辅助函数 |
| `inc/password.php` | 多层级密码门禁判定、`v2` HMAC 签名票据、CSRF 防伪令牌、评论签名回执与防缓存响应头 |
| `inc/content.php` | `{password:...}` 与 `{hide}` 标记解析、授权后 Markdown 重建渲染、RSS/Atom 脱敏与安全摘要提取 |
| `inc/comments.php` | Cloudflare Turnstile 校验、评论入库钩子与多级评论列表渲染 |
| `inc/helpers.php` | 阅读量统计与列迁移、阅读耗时、分类层级解析、时间轴查询、分页及 Canonical URL 计算 |
| `inc/seo.php` | SEO 描述（`meta description`）、Open Graph 与 JSON-LD 结构化数据生成 |
| `assets/css/tailwind.input.css` / `tailwind.config.js` | Tailwind CSS 源样式表与类名扫描配置 |
| `assets/css/tailwind.min.css` | 预编译并纳入版本控制的 Tailwind CSS 生产产物 |
| `assets/css/bold.css` / `assets/js/bold.js` | 主题自定义样式表与前端交互脚本（暗色模式、目录、代码块、移动菜单等） |
| `assets/vendor/view-image.min.js` | 主题自托管并经过安全加固的图片灯箱脚本 |
| `tests/` | PHP 专项回归测试、Typecho 核心集成测试与 Playwright 浏览器灯箱测试脚本 |
| `docs/` / `security.md` | 用户与开发文档目录 / 按时间线记录的安全审计与验收报告 |

> **版本号与缓存标识**：静态资源的缓存破坏参数（`?v=...`）统一读取 `functions.php` 中的 `BOLD_VERSION` 常量。发布静态资源或样式变更时，请同步递增该常量并保持主题注释元信息一致。

## 构建样式

当修改了 `.php` 模板、`inc/**/*.php` 或 `assets/js/**/*.js` 中的 Tailwind CSS 工具类，或调整了 `tailwind.config.js` 时，需要重新编译生产环境 CSS：

```bash
npm ci
npm run build:css
```

- 构建过程严格使用 `package-lock.json` 中锁定的依赖版本，扫描上述 PHP 与 JS 文件并输出压缩后的 `assets/css/tailwind.min.css`。请将编译后的 CSS 产物随代码变更一并提交。
- 若仅修改 `assets/css/bold.css` 中的原生 CSS 规则，则无需运行 Tailwind 构建。

## 语法与代码格式检查

提交前请运行以下命令，确保所有 PHP 与 JavaScript 文件无语法错误且无空白字符污染：

```bash
rg --files -g '*.php' -g '!node_modules/**' -0 | xargs -0 -n 1 php -l
node --check assets/js/bold.js
node --check assets/vendor/view-image.min.js
node --check tests/view_image_regression.js
git diff --check
```

## PHP 专项回归测试

`tests/` 目录下的五组专项回归脚本内置了轻量级 Typecho 测试替身（Test Doubles），无需连接真实数据库或部署完整站点即可直接运行：

```bash
php tests/helpers_regression.php
php tests/password_security_regression.php
php tests/protected_content_regression.php
php tests/reply_unlock_regression.php
php tests/audit_regression.php
```

| 测试脚本 | 覆盖范围 |
| --- | --- |
| `helpers_regression.php` | 主分类选择与父级目录树还原、Canonical 与分页 URL 生成、按需资源探测等辅助函数 |
| `password_security_regression.php` | 站点密钥签名、`v2` 票据防篡改与过期失效、多作用域 Cookie 隔离、CSRF 校验及用户角色免密边界 |
| `protected_content_regression.php` | `{password:...}` 与 `{hide}` 嵌套解析、未闭合 / 畸形标记的保密截断、处理流程隔离及 Feed 上下文判定 |
| `reply_unlock_regression.php` | 匿名评论服务端 HMAC 回执、精确已审核评论校验、记忆邮箱伪造拦截与跨文章重放隔离 |
| `audit_regression.php` | 密码 `'0'` 严格空串判断、分类归档门禁开关、数字类型自定义字段及原生密码文章的时间轴排除 |

所有测试脚本均应输出通过信息并以状态码 `0` 退出。

## Typecho 核心集成回归

若修改了内容渲染、保护标记解析或自定义字段读取逻辑，请准备一份官方 Typecho `1.2.1` 源码目录（需 PHP 启用 `pdo_sqlite` 扩展），将下方路径替换为本地实际路径后执行：

```bash
BOLD_TYPECHO_ROOT=/path/to/typecho php tests/typecho_integration_regression.php
```

该测试会加载真实的 Typecho Markdown 解析器、字段读取器并创建临时 SQLite 数据库（执行完毕后自动清理），端到端验证脚注与引用定义防泄露、授权后正文 / 摘要 / Feed 重建、数字类型密码字段及内容插件钩子兼容性。

## 浏览器灯箱回归

若修改了 `assets/vendor/view-image.min.js` 或图片灯箱调用逻辑，可使用 `playwright-core` 与本地 Chromium / Chrome 运行真实浏览器回归（二者不强制列入项目 `package.json`，可通过环境变量指定本机路径）：

```bash
PLAYWRIGHT_MODULE=/path/to/playwright-core BROWSER_BIN=/path/to/chrome node tests/view_image_regression.js
```

若当前 Node.js 环境已能直接解析 `playwright-core` 且已安装默认浏览器，也可直接执行：

```bash
node tests/view_image_regression.js
```

该脚本在真实 DOM 中验证含引号图片的 XSS 属性注入防护、灯箱打开、按钮与键盘方向键切换、首尾循环、关闭及二次打开（可通过 `VIEW_IMAGE_SCRIPT` 环境变量覆盖待测脚本路径）。

## 提交与发布规范

1. **按需执行回归**：修改 PHP 逻辑后运行对应专项回归；涉及保护标记或 Markdown 渲染时增跑 Typecho 核心集成回归；修改灯箱脚本时运行浏览器回归；增删 Tailwind 类名后执行 `npm run build:css`。
2. **多视口与交互检查**：涉及页面布局或交互改动时，请同时在桌面端与移动端视口检查亮 / 暗色模式切换、键盘 Tab 焦点环及横向滚动溢出。
3. **同步维护文档**：功能或配置变更请同步更新 `docs/` 下对应文档；涉及安全边界或漏洞修复时，请在 `security.md` 中补充审计日期、验证环境与残余边界说明。
4. **规范提交 PR**：在 Pull Request 中清晰说明问题原因、解决方案与已执行的验证命令。生产环境部署与升级验收请参阅 [升级指南](installation.md#升级指南) 与 [安全审计记录](../security.md#验证记录与部署验收)。
