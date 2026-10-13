# Password Protection Guide

[Project Home](../README.md) · [中文指南](password-protection.md) · [Configuration](configuration.md)

## Choose a Protection Scope

| Scope | Where to Configure | Behavior & Use Case |
| --- | --- | --- |
| **Site-wide posts & pages** | Theme setting `postPassword` | Provides a fallback password gate for all entries without a higher-priority password |
| **Specific categories** | `passwordProtectedCategories` & `categoryPasswords` | Protects all entries in selected categories with a shared or per-category password |
| **Single post or page** | Custom field `password` in the editor | Sets an entry-specific password that overrides any category or global password |
| **Inline content section** | `{password:your-pass}...{/password}` | Encrypts specific paragraphs, images, or code blocks while keeping surrounding text public |

- **Page-level priority**: **Entry custom field `password` ＞ Category password ＞ Global password**.
- **Independent inline gates**: `{password:...}` blocks are verified separately from page-level gates. If an entry has both a page-level password and inline blocks, readers must pass the page-level gate first before unlocking individual sections.

## Category Passwords

1. In Typecho's admin panel, open **Dashboard → Appearance → Theme Settings**.
2. Enter comma-separated category **slugs** (e.g., `private,secret,vip`) in `passwordProtectedCategories`.
3. Enter one `category-slug:password` rule per line in `categoryPasswords`:

   ```text
   private:your-private-password
   secret:your-secret-password
   vip:your-vip-password
   ```

4. Configure homepage visibility, category archive access, and RSS/Atom feed protection as needed.

> **Configuration notes**:
> - Always use category **slugs** rather than display names.
> - Any category assigned a password in `categoryPasswords` is automatically treated as protected, even if omitted from `passwordProtectedCategories`.
> - Each line is split only on the **first colon (`:`)**, so passwords themselves may contain colons; leading and trailing whitespace is trimmed automatically.

### Relationship with the Global Password

- A category listed in `passwordProtectedCategories` without its own rule in `categoryPasswords` falls back to the global password (`postPassword`). If neither is configured, the category remains locked until a password is set.
- **Important**: Setting a global password (`postPassword`) **also locks all posts and pages outside protected categories**. If you only want a few categories to share a password while keeping the rest of your site public, leave `postPassword` empty and assign that shared password to each target category in `categoryPasswords`.

### Listings, Archive Access, and Excerpts

| Setting / State | Frontend Behavior |
| --- | --- |
| **Homepage: Show (default)** | Protected-category entries appear in homepage listings, with a protection notice replacing locked excerpts |
| **Homepage: Hide** | Protected-category entries are excluded at the SQL query level on the homepage (even after unlocking) |
| **Category archive gate: Required (default)** | Readers must unlock the category password before viewing its archive listing (`/category/{slug}/`) |
| **Category archive gate: Not Required** | The category listing is public, while locked excerpts and article bodies remain protected |
| **Entry unlocked with required password** | Normal text excerpts are shown in listings, with inline `{password:...}` and `{hide}` blocks stripped |

- A category archive and all entries using that category's password **share the same unlock state**.
- If an entry inside a protected category defines its own `password` custom field, unlocking the category does not unlock that entry; its custom field password is still required.

## Individual Posts and Pages

In the post or page editor, expand the **Custom Fields** panel and add:

| Field Property | Example Value |
| --- | --- |
| **Field name** | `password` |
| **Field type** | Text (`str`) |
| **Field value** | `your-entry-password` |

- Default pages (`page.php`), timeline pages (`page-archive.php`), and links pages (`page-links.php`) all enforce the same page-level gate.
- For an entry in category `private` that also defines a custom field `password`, the entry's custom field password takes precedence over the category password.
- The theme's `password` custom field operates independently from Typecho's built-in native password field. Note that the public timeline always excludes entries with a non-empty native password at the SQL level (even when unlocked by the visitor) to prevent exposing protected titles.

## Inline Protected Sections

Wrap sensitive sections in `{password:your-password}` and `{/password}` tags inside your post or page body:

```markdown
This paragraph is publicly visible.

{password:your-section-password}
This section requires its own password (`your-section-password`).
It can contain multiple paragraphs, Markdown formatting, images, and links.
{/password}

This paragraph is publicly visible again.

{password:another-section-password}
Another independently verified inline section in the same entry.
{/password}
```

- Multiple inline blocks in the same entry are verified and unlocked independently.
- Logged-in authors can view all inline blocks in their own entries without entering a password.
- Inline protected sections are automatically stripped from listing excerpts, SEO descriptions, Open Graph image extraction, and RSS/Atom feeds.
- **Fail-closed marker parsing**: Nested markers must close in the exact reverse order they were opened. Malformed, mismatched, or unclosed markers halt public output from the corresponding opening tag to prevent accidental leaks. Footnotes and reference link definitions inside protected blocks are stripped prior to Markdown rendering. Always preview in an incognito window before publishing.

## Unlock Tickets and Permissions

- **Signed HMAC tickets**: Upon successful password verification, the browser stores a **7-day `v2` HMAC ticket** signed with Typecho's installation-generated random site `secret`.
- **Automatic invalidation**: Tickets are cryptographically bound to the active password and expiration timestamp. Expiration, changing the password, or rotating the site `secret` immediately invalidates existing tickets. If the site `secret` is missing or empty, ticket issuance is refused.
- **Scope isolation**: Entry, category, global, and inline scopes use separate cookies so unlocking one never overwrites another; non-ASCII category slugs are automatically hashed for safe cookie naming.
- **Cookie & CSRF hardening**: Unlock cookies use `HttpOnly` and `SameSite=Lax` (plus `Secure` over HTTPS). Both page-level and inline password forms require an anonymous double-submit CSRF cookie (`bold_password_csrf`) paired with a context-bound token.
- **Role-based bypass**: Only logged-in users with **Editor (`editor`) or higher** privileges bypass page-level password gates (Subscribers and Contributors cannot bypass them). Inline password blocks and reply-to-unlock (`{hide}`) blocks only grant automatic bypass to the entry's author.

> **Reply-to-unlock (`{hide}`)**: Reply-only blocks use a separate 7-day server-signed receipt bound to the specific entry (`cid`), comment (`coid`), and normalized email address, and require the comment to be `approved`. Logged-in users are verified against their approved comments on that entry. Legacy anonymous commenters from older theme versions must submit a new comment to receive a signed receipt. See [Content & Page Usage](usage.md) and [Installation & Upgrade](installation.md).

## RSS/Atom Feed Protection

Feed protection (`protectFeed`) is enabled by default across the main feed, category feeds, per-entry comment feeds, and the global comments feed (`/feed/comments/`):

1. **Whole-entry redaction**: Posts protected by a global, category, or custom-field password have their feed body and excerpt replaced with a protection notice.
2. **Pre-render marker stripping**: `{hide}` and `{password:...}` sections—along with any footnotes or reference definitions inside them—are stripped from raw text before Markdown rendering.
3. **Comment & channel redaction**: Comments on protected entries have both their body and participant metadata (author name, email, URL, timestamp, permalink) anonymized in feeds; per-entry comment feed channel descriptions are also rebuilt from sanitized source text.

> Setting `protectFeed` to `0` disables whole-entry and protected-comment redaction in feeds, while inline `{hide}` / `{password:...}` blocks and channel descriptions remain sanitized. Note that entry titles, publication dates, and permalinks are public routing metadata and should not contain sensitive secrets.

## Cache Headers and Security Boundaries

- **Cache isolation**: Protected responses and listings whose output may vary by unlock cookies send `Cache-Control: private, no-store` and `Vary: Cookie` before rendering. Ensure any reverse proxy, CDN, or full-page cache respects these headers, and purge HTML and feed caches after upgrading or changing passwords.
- **Application-layer boundary**: Theme passwords enforce access control at the PHP application layer; raw content and passwords remain stored in the database, backups, and theme settings. Static attachments (such as uploaded images or archives) are served directly by your web server via their URLs and require web-server-level access rules if strict confidentiality is needed.
- **Brute-force mitigation**: Password checks use constant-time comparison (`hash_equals`) and random failure delays, but the theme does not maintain persistent per-IP rate-limiting state. Always deploy over **HTTPS**, choose high-entropy passwords, and configure rate limiting on POST requests at your reverse proxy or WAF.

For the full chronological audit log, verified test environments, and residual deployment boundaries, see the [Security Review](../security.md).
