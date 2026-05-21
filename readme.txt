=== Techanum Maintenance ===
Contributors: gregorytriglidis
Donate link: https://techanum.com/maintenance/
Tags: maintenance, maintenance-mode, under-construction, coming-soon, admin-notices, ai, api
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Replace the default WordPress maintenance page with a beautiful, customizable under-construction page — and hide admin notices from non-admin users.

== Description ==

**Techanum Maintenance** gives you full control over your site's maintenance experience. Instead of the plain default WordPress maintenance screen, your visitors see a clean, modern, branded page that communicates professionalism even while your site is offline.

The plugin integrates with the **Google Gemini AI API** to automatically generate a friendly, dynamic maintenance message every hour — so your visitors always see something fresh. If you prefer, you can override the AI message with your own custom text at any time. You can also upload your own logo to replace the default wrench icon on the maintenance page.

Beyond the front-end experience, Techanum Maintenance also helps you manage the **WordPress admin dashboard**. You can choose which user roles should have admin notices silently hidden — keeping the dashboard clean for editors, shop managers, or any other role that doesn't need to see plugin update banners and other notices. Administrators always retain full notice visibility.

Whether you are a developer putting the finishing touches on a client site, a store owner running a flash update, or a blogger redesigning their theme, Techanum Maintenance is the lightweight, no-bloat solution that gets out of your way and just works.

== Features ==

= Free =

* **One-click maintenance mode toggle** — enable or disable the maintenance page from the Settings screen.
* **Custom logo** — upload your own logo via the WordPress Media Library to brand the maintenance page.
* **Custom message** — write your own maintenance message or let the AI generate one for you.
* **AI-powered messages** — integrates with the Google Gemini API to generate a friendly, dynamic message (cached for 1 hour to minimise API calls).
* **Role-based bypass** — choose which user roles (e.g. Editor, Shop Manager) can still see the normal site while maintenance mode is active. Administrators are always excluded.
* **Admin notices management** — hide WordPress admin notices for selected user roles to keep the dashboard clean.
* **Proper HTTP 503 response** — the maintenance page returns a 503 Service Unavailable status with a `Retry-After: 3600` header, which is correct for SEO and search-engine crawlers.
* **Settings preserved on deactivation** — your configuration is never deleted when you deactivate the plugin; only maintenance mode is turned off.
* **Fully internationalized** — all strings are wrapped in translation functions and a `.pot` file is included.
* **WordPress Coding Standards** — clean, well-documented code with proper prefixes and no naming conflicts.

= Pro (Coming Soon) =

* **Scheduled maintenance windows** — set a start and end date/time for automatic activation and deactivation.
* **Countdown timer** — display a live countdown on the maintenance page so visitors know when to come back.
* **Multiple maintenance page templates** — choose from a library of professionally designed templates.
* **Social media links** — add links to your social profiles on the maintenance page.
* **Email notification** — notify subscribers when the site is back online.
* **White-label mode** — remove all Techanum branding from the maintenance page.

== Installation ==

1. Download the plugin `.zip` file from the WordPress.org plugin repository.
2. In your WordPress admin dashboard, go to **Plugins → Add New → Upload Plugin**.
3. Choose the downloaded `.zip` file and click **Install Now**.
4. After installation, click **Activate Plugin**.
5. Go to **Settings → Techanum Maintenance** to configure the plugin.
6. *(Optional)* Enter your Google Gemini API key in the **API Settings** section to enable AI-generated maintenance messages.
7. Toggle **Enable Maintenance Page** to put your site into maintenance mode.

**Manual installation:**

1. Upload the `techanum-maintenance` folder to the `/wp-content/plugins/` directory via FTP.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Go to **Settings → Techanum Maintenance** to configure the plugin.

== Frequently Asked Questions ==

= How do I get a Google Gemini API key? =

Visit [Google AI Studio](https://aistudio.google.com/app/apikey) and sign in with your Google account. Click **Create API key**, copy the key, and paste it into the **API Key** field under **Settings → Techanum Maintenance → API Settings**. The key is stored securely in your WordPress database and is never exposed in the front end.

= What happens if I leave the API key field empty? =

No problem at all. If no API key is configured (or if the API call fails for any reason), the plugin automatically falls back to a built-in static message: *"We are performing scheduled maintenance. We will be back soon!"* You can also override this at any time by entering your own text in the **Custom Maintenance Message** field.

= How do I exclude certain user roles from the maintenance page? =

Go to **Settings → Techanum Maintenance** and scroll to the **Maintenance Page** section. Under **Excluded Roles**, check the boxes next to the roles you want to bypass the maintenance page (for example, Editor or Shop Manager). Users with those roles will see the normal site even when maintenance mode is active. Administrators are always excluded and do not appear in this list.

= Will I lose my settings if I deactivate the plugin? =

No. Deactivating the plugin only turns off maintenance mode — it does not delete any of your saved settings (logo, message, API key, role configuration). If you re-activate the plugin later, all your settings will be exactly as you left them. If you want to remove all plugin data, you must delete the plugin entirely (not just deactivate it) — and even then, the database options are not automatically removed in the current version (a future Pro feature will add a "Delete all data on uninstall" option).

= Can I define the API key in wp-config.php instead of the settings page? =

Yes. As an alternative to entering the key in the admin UI, you can define the constant `TECHANUM_ANTIGRAVITY_API_KEY` in your `wp-config.php` file:

`define( 'TECHANUM_ANTIGRAVITY_API_KEY', 'your-api-key-here' );`

The settings-page value takes priority; the constant is used only as a fallback.

== Screenshots ==

1. The full Settings page with all three sections visible: Maintenance Page, Admin Notices Management, and API Settings.
2. The maintenance page as a visitor sees it — featuring the custom logo, the AI-generated message, and the site copyright footer.
3. The "Admin Notices Management" section with several roles checked to suppress notices in the dashboard.
4. The Pro teaser box at the bottom of the Settings page, highlighting upcoming premium features.
5. The Settings page with the "Enable Maintenance Page" checkbox toggled ON, clearly showing the active state.

== Changelog ==

= 1.0.0 =
* Initial release.
* One-click maintenance mode toggle with HTTP 503 + Retry-After header.
* Custom logo upload via the WordPress Media Library.
* Custom maintenance message field.
* Google Gemini AI API integration for dynamic, AI-generated maintenance messages (cached for 1 hour via WordPress transients).
* Role-based bypass: choose which roles can still access the normal site during maintenance.
* Admin Notices Management: hide WordPress admin notices for selected user roles.
* API key can be stored in the database or defined as a constant in wp-config.php.
* Settings are preserved on deactivation; maintenance mode is automatically disabled on deactivation.
* Fully internationalized with a `.pot` file included (34 translatable strings).
* Pro teaser box with information about upcoming premium features.

== Upgrade Notice ==

= 1.0.0 =
This is the initial release of Techanum Maintenance. No upgrade steps are required — simply install and activate.
