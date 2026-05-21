# Techanum Maintenance — WP.org Pre-Submission Checklist

> **Day 10 · Final Sanity Check before hitting "Submit"**  
> Version: 1.0.0 | Plugin slug: `techanum-maintenance`  
> Last updated: 2026-05-21

Work through every item below. Do not submit until every box is ticked. ✅

---

## 1 · Functionality

- [x] Plugin activates without fatal errors or PHP warnings (`WP_DEBUG = true`)
- [x] Plugin deactivates cleanly — maintenance mode is turned off; all other settings are preserved
- [x] Maintenance page is served with HTTP `503 Service Unavailable` + `Retry-After: 3600` header
- [x] Excluded roles bypass the maintenance page correctly
- [x] Administrators **always** bypass the maintenance page regardless of settings
- [x] Admin notices are hidden for configured silent roles
- [x] Administrators **always** see admin notices regardless of settings
- [x] Logo upload / remove works via the WordPress Media Library
- [x] Custom message overrides the AI-generated message when set
- [x] AI message is fetched from the Gemini API and cached for 1 hour via WordPress transients
- [x] Fallback message is shown when no API key is configured or the API call fails
- [x] API key can be stored in the database **or** defined as `TECHANUM_ANTIGRAVITY_API_KEY` in `wp-config.php`
- [x] Settings are saved and retrieved correctly via the WordPress Settings API
- [x] All settings are sanitized on save (roles → `sanitize_excluded_roles` / `sanitize_silent_roles`; URL → `esc_url_raw`; textarea → `sanitize_textarea_field`; API key → `sanitize_text_field` + `trim`)

---

## 2 · Code Quality

- [x] No PHP warnings or notices with `WP_DEBUG = true` and `WP_DEBUG_LOG = true`
- [x] All class names use the `Techanum_` prefix (`Techanum_Maintenance_Mode`, `Techanum_Antigravity_API`, `Techanum_Maintenance_Settings`, `Techanum_Maintenance_Admin_Notices`)
- [x] All global functions use the `techanum_` prefix (`techanum_maintenance_load_textdomain`, `techanum_maintenance_init`, `techanum_maintenance_activate`, `techanum_maintenance_deactivate`)
- [x] No unprefixed global variables
- [x] All PHP files begin with `if ( ! defined( 'ABSPATH' ) ) { exit; }`
- [x] All PHP files have a `@license GPL-3.0-or-later` tag in the file docblock
- [x] No hardcoded strings outside translation functions
- [x] All output is properly escaped (`esc_html`, `esc_attr`, `esc_url`, `esc_textarea`, `esc_js`)
- [x] All user input is sanitized before saving
- [x] WordPress Coding Standards followed throughout (tabs for indentation, Yoda conditions, etc.)
- [x] `error_log()` calls are present only for genuine API/network errors (not debug noise)

---

## 3 · Internationalization

- [x] All user-facing strings are wrapped in `__()`, `_e()`, `esc_html__()`, `esc_attr__()`, `esc_html_e()`, or `esc_attr_e()`
- [x] Text domain `techanum-maintenance` matches the plugin slug and the `Text Domain:` header in `techanum-maintenance.php`
- [x] `.pot` file is present at `languages/techanum-maintenance.pot` (34 translatable strings)
- [x] `load_plugin_textdomain()` is hooked to `init` (not `plugins_loaded`)
- [x] Translator comment present for string with placeholder: `/* translators: %s: site name */`

---

## 4 · readme.txt

- [x] `readme.txt` is present in the plugin root
- [x] Plugin header block is complete:
  - `Contributors: gregorytriglidis`
  - `Tags: maintenance, maintenance-mode, under-construction, coming-soon, admin-notices, ai, api`
  - `Requires at least: 6.0`
  - `Tested up to: 6.8`
  - `Requires PHP: 7.4`
  - `Stable tag: 1.0.0`
  - `License: GPLv3`
  - `License URI: https://www.gnu.org/licenses/gpl-3.0.html`
- [x] Short description is ≤ 150 characters *(current: 131 characters)*
- [x] `== Description ==` section is present and informative
- [x] `== Installation ==` section is present with step-by-step instructions (manual + auto)
- [x] `== Frequently Asked Questions ==` section has 4 Q&A entries
- [x] `== Screenshots ==` section lists descriptions for all 5 screenshots
- [x] `== Changelog ==` section has a detailed entry for version 1.0.0
- [x] `== Upgrade Notice ==` section has an entry for version 1.0.0
- [x] `Stable tag: 1.0.0` in `readme.txt` matches `Version: 1.0.0` in the main plugin file
- [ ] **TODO:** Validate with the [WordPress readme validator](https://wordpress.org/plugins/developers/readme-validator/) — paste `readme.txt` content and confirm zero errors

---

## 5 · Screenshots

Screenshots must be placed in the **SVN `assets/` folder** (not inside the plugin zip).

- [ ] `screenshot-1.png` — Full Settings page (all three sections visible) — recommended 1200 × 900 px
- [ ] `screenshot-2.png` — Maintenance page as a visitor sees it (logo + AI message) — 1200 × 900 px
- [ ] `screenshot-3.png` — Admin Notices Management section with roles checked — 1200 × 900 px
- [ ] `screenshot-4.png` — Pro teaser box at the bottom of the Settings page — 1200 × 900 px
- [ ] `screenshot-5.png` — Settings page with maintenance mode toggled ON — 1200 × 900 px
- [ ] All screenshot files are PNG format, minimum 880 × 660 px

---

## 6 · Plugin Assets (SVN `assets/` folder)

These files go in the SVN `assets/` folder — **not** inside the plugin zip.

- [ ] `banner-772x250.png` — Plugin directory banner (standard resolution)
- [ ] `banner-1544x500.png` — Plugin directory banner (retina / HiDPI) *(optional but recommended)*
- [ ] `icon-128x128.png` — Plugin directory icon (standard resolution)
- [ ] `icon-256x256.png` — Plugin directory icon (retina / HiDPI) *(optional but recommended)*

---

## 7 · Licensing

- [x] `License: GPLv3` and `License URI: https://www.gnu.org/licenses/gpl-3.0.html` in plugin header
- [x] All PHP files have `@license GPL-3.0-or-later` in their file docblock
- [x] No proprietary or incompatible third-party code included
- [x] External API used (Google Gemini) is called via `wp_remote_post()` — no bundled SDK

---

## 8 · Security

- [x] `register_activation_hook` / `register_deactivation_hook` used correctly
- [x] Settings page protected with `current_user_can( 'manage_options' )` check
- [x] WordPress Settings API used for all form handling (nonce handled automatically)
- [x] No direct `$_POST` / `$_GET` access without nonce verification
- [x] API key never exposed in front-end HTML output
- [x] `autocomplete="new-password"` on the API key field to prevent browser autofill leaks

---

## 9 · Repository / Submission

- [ ] Plugin slug `techanum-maintenance` is available on WordPress.org *(confirmed at submission)*
- [ ] Plugin submitted via **https://wordpress.org/plugins/developers/add/** and approval e-mail received
- [ ] SVN repository checked out: `svn co https://plugins.svn.wordpress.org/techanum-maintenance/`
- [ ] Plugin files copied to `trunk/` (dev-only files excluded)
- [ ] Plugin assets copied to `assets/`
- [ ] Release tagged: `svn cp trunk tags/1.0.0`
- [ ] Initial commit pushed: `svn ci -m "Initial release 1.0.0"`
- [ ] Plugin page live at **https://wordpress.org/plugins/techanum-maintenance/**

---

## 10 · Compatibility Testing

- [ ] Tested on WordPress **6.8** (latest) — no errors
- [ ] Tested on WordPress **6.0** (minimum required) — no errors
- [ ] Tested on **PHP 7.4** (minimum required)
- [ ] Tested on **PHP 8.0**
- [ ] Tested on **PHP 8.1**
- [ ] Tested on **PHP 8.2**
- [ ] Tested on **PHP 8.3**
- [ ] Tested with **WooCommerce** active (to verify admin notices suppression works with extra roles)
- [ ] Tested with a **multisite** installation *(optional for v1.0.0)*

---

## 11 · Final Pre-Submit Verification (30-second check)

Run through these five points immediately before clicking "Submit":

1. **`Version:` == `Stable tag:` == tag folder name** → all three are `1.0.0` ✅
2. **`readme.txt` validates** → zero errors at https://wordpress.org/plugins/developers/readme-validator/ ⬜
3. **Plugin activates on a clean WP install** → no fatal errors, no warnings ✅
4. **Maintenance page returns 503** → verify with `curl -I https://yoursite.com` ✅
5. **No dev files in the zip** → `.git/`, `SUBMISSION-CHECKLIST.md`, `SVN-SUBMISSION-GUIDE.md`, `LAUNCH-MATERIALS.md` are absent ⬜

---

*Plugin: techanum-maintenance v1.0.0 | Author: Gregory Triglidis | https://techanum.com/*
