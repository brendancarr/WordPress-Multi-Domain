# Multi-Domain Support for WordPress

This plugin allows a single WordPress installation to serve the **same content** across **multiple domains**, without hardcoded domain redirects, and with full HTTPS and CORS support.

## ✅ Features

- Serve identical content from multiple domains (e.g., `domain1.com`, `domain2.com`)
- Automatically adjusts site URLs dynamically based on the request
- Disables WordPress's default canonical redirection to a single domain
- Optionally enforces HTTPS on all URLs and asset links
- Admin interface to configure allowed CORS origins
- Supports cross-origin AJAX and font loading (woff, ttf, woff2) * mostly
- Canonical tag override for SEO consistency

---

## 🚫 How This Is Different from WordPress Multisite

**WordPress Multisite** is designed for managing multiple **distinct sites** (each with different content, users, themes, etc.) from one WordPress installation.

**This plugin**, on the other hand:

- Keeps a single site with the **same content across all domains**
- Does **not** create multiple sites/subsites in the database
- Requires no changes to WordPress core or database structure
- Useful for white-labeled domains, region-specific domains, or marketing aliases

---

## 📦 Installation

1. Upload the plugin folder to `wp-content/plugins/`
2. Activate it from the WordPress admin
3. Go to **Settings > Multi-Domain** to configure your allowed domains (one per line)

---

## 🛠 Example Use Case

You want your WordPress site to appear identical on:

- `https://example.com`
- `https://example.net`
- `https://example.co.uk`

But WordPress by default tries to redirect everything to one "main" domain. This plugin disables that behavior, makes everything HTTPS-aware, and enables safe CORS handling for all front-end and API requests.

---

## ⚠️ SEO Note

To avoid duplicate content penalties, configure a single canonical domain inside the plugin settings.

---

## Additional Notes

You may need to set this for your fonts - 

<FilesMatch "\.(ttf|otf|eot|woff|woff2)$">
    Header set Access-Control-Allow-Origin "*"
</FilesMatch>
