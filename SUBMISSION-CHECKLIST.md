# Techanum Maintenance — WP.org Pre-Submission Checklist

## ✅ Functionality
- [ ] Plugin activates without fatal errors or PHP warnings (`WP_DEBUG = true`)
- [ ] Plugin deactivates cleanly (maintenance mode is turned off; settings are preserved)
- [ ] Maintenance page is served with HTTP 503 + `Retry-After: 3600` header
- [ ] Excluded roles bypass the maintenance page correctly
- [ ] Administrators always bypass the maintenance page regardless of settings
- [ ] Admin notices are hidden for configured silent roles
- [ ] Administrators always see admin notices regardless of settings
- [ ] Logo upload/remove works via the WordPress Media Library
- [ ] Custom message overrides the AI-generated message when set
- [ ] AI message is fetched from the Gemini API and cached for 1 hour via transients
- [ ] Fallback message is shown when no API key is configured or the API call fails
- [ ] API key can be stored in the database **or** defined as `TECHANUM_ANTIGRAVITY_API_KEY` in `wp-config.php`
- [ ] Settings are saved and retrieved correctly via the WordPress Settings API
- [ ] All settings are sanitized on save (roles, URL, textarea, API key)

## ✅ Code Quality
- [ ] No PHP warnings or notices with `WP_DEBUG = true` and `WP_DEBUG_LOG = true`
- [ ] All class names use the `Techanum_` prefix
- [ ] All global functions use the `techanum_` prefix
- [ ] No unprefixed global variables
- [ ] All PHP files begin with `if ( ! defined( 'ABSPATH' ) ) { exit; }`
- [ ] All PHP files have a GPL-3.0-or-later `@license` tag in the file docblock
- [ ] No hardcoded strings outside translation functions
- [ ] All output is properly escaped (`esc_html`, `esc_attr`, `esc_url`, `esc_textarea`)
- [ ] All user input is sanitized before saving
- [ ] WordPress Coding Standards followed throughout

## ✅ Internationalization
- [ ] All user-facing strings are wrapped in `__()`, `_e()`, `esc_html__()`, `esc_attr__()`, etc.
- [ ] Text domain `techanum-maintenance` matches the plugin slug and the `Text Domain:` header
- [ ] `.pot` file is present at `languages/techanum-maintenance.pot` (34 strings)
- [ ] `load_plugin_textdomain()` is hooked to `init`

## ✅ readme.txt
- [ ] `readme.txt` is present in the plugin root
- [ ] Plugin header block is complete (Contributors, Tags, Requires at least, Tested up to, Requires PHP, Stable tag, License, License URI)
- [ ] Short description is ≤ 150 characters
- [ ] `== Description ==` section is present and informative
- [ ] `== Installation ==` section is present with step-by-step instructions
- [ ] `== Frequently Asked Questions ==` section has at least 3 Q&A entries
- [ ] `== Screenshots ==` section lists descriptions for all screenshots
- [ ] `== Changelog ==` section has an entry for version 1.0.0
- [ ] `== Upgrade Notice ==` section has an entry for version 1.0.0
- [ ] `Stable tag` in `readme.txt` matches `Version:` in the main plugin file (`1.0.0`)
- [ ] Validate with the [WordPress readme validator](https://wordpress.org/plugins/developers/readme-validator/)

## ✅ Screenshots
- [ ] `screenshot-1.png` — Full Settings page (all sections visible) — 1200×900 px
- [ ] `screenshot-2.png` — Maintenance page as a visitor sees it (logo + message) — 1200×900 px
- [ ] `screenshot-3.png` — Admin Notices Management section with roles checked — 1200×900 px
- [ ] `screenshot-4.png` — Pro teaser box at the bottom of the Settings page — 1200×900 px
- [ ] `screenshot-5.png` — Settings page with maintenance mode toggled ON — 1200×900 px
- [ ] All screenshot files are placed in the **plugin root** folder
- [ ] Recommended size: **1200×900 px** (or 880×660 px minimum); PNG format

## ✅ Licensing
- [ ] `License: GPLv3` and `License URI: https://www.gnu.org/licenses/gpl-3.0.html` in plugin header
- [ ] All PHP files have `@license GPL-3.0-or-later` in their file docblock
- [ ] No proprietary or incompatible third-party code included

## ✅ Repository / Submission
- [ ] Plugin slug `techanum-maintenance` is available on WordPress.org
- [ ] SVN repository structure is correct: `trunk/`, `tags/1.0.0/`, `assets/`
- [ ] Plugin assets (banner, icon) are placed in the SVN `assets/` folder (not in the plugin zip)
  - `banner-772x250.png` and/or `banner-1544x500.png`
  - `icon-128x128.png` and/or `icon-256x256.png`
- [ ] Plugin zip does not contain development files (`.git`, `node_modules`, `SUBMISSION-CHECKLIST.md`, etc.)
- [ ] Tested on the latest WordPress version (6.8) and the minimum required version (6.0)
- [ ] Tested on PHP 7.4, 8.0, 8.1, 8.2, and 8.3

---

*Last updated: 2026-05-21*
