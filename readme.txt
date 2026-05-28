=== Techanum Maintenance ===
Contributors: techanum
Tags: maintenance mode, coming soon, under construction, ai, admin notices
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 1.1.0
Requires PHP: 7.4
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Replace the default WordPress maintenance screen with a friendly, custom page — powered by AI if you choose.

== Description ==

Techanum Maintenance gives you a beautiful, customizable maintenance page in seconds. Upload your logo, write your own message, or let AI generate one for you. The plugin also cleans up the WordPress dashboard by hiding distracting admin notices from non‑admin users.

**✨ Features**
- One‑click maintenance mode — no coding required.
- Upload your own logo and write a custom headline / message.
- AI‑powered messages: generate a unique maintenance message using OpenAI, Anthropic (Claude), Google Gemini, or any OpenAI‑compatible API (OpenRouter, Together AI, LM Studio, Ollama, and more).
- "Generate with AI" button — get a fresh message instantly.
- Hide admin notices for editors, contributors, and customers.
- Clean, minimal settings page — no ads, no upsells.
- Fully responsive maintenance page template.

**🤖 Supported AI Providers**
- OpenAI (GPT‑4o mini, GPT‑3.5 Turbo)
- Anthropic (Claude 3 Haiku)
- Google Gemini (2.0 Flash)
- Any OpenAI‑compatible endpoint (Custom provider)
- Local LLMs: LM Studio, Ollama, and similar

**💰 100% Free & Community‑Driven**
Techanum Maintenance is free and will stay free. Development is funded by user donations and affiliate links to AI providers — you’ll never be forced to upgrade or pay for features. Join our Telegram group to suggest ideas, vote on what’s next, and help shape the plugin’s future.

== Installation ==

1. Upload the `techanum-maintenance` folder to `/wp-content/plugins/`, or install directly from the WordPress plugin directory.
2. Activate the plugin through the 'Plugins' menu.
3. Go to **Settings → Techanum Maintenance**.
4. Enable maintenance mode and customize your page.
5. (Optional) Add an AI API key and click "Generate with AI".

== Frequently Asked Questions ==

= Is this plugin really free? =
Yes. There are no paid plans, and the core plugin will always remain free. We accept donations and use affiliate links to cover costs.

= How do I get an AI API key? =
You can get a free key from OpenRouter or Google AI Studio, or use your existing OpenAI / Anthropic key. Visit our [AI Tools page](https://techanum.com/ai-tools/) for affiliate links — signing up through them supports the plugin at no extra cost to you.

= Does it work with local LLMs like LM Studio or Ollama? =
Absolutely. Select "Custom (OpenAI‑compatible)" as your provider and enter your local URL (e.g., `http://localhost:1234/v1`).

= What happens if the AI call fails? =
The plugin falls back to a friendly default message, so visitors never see a broken page.

= Where can I suggest a new feature? =
Join our [Telegram group](https://t.me/TechanumChat) and share your ideas. The community discusses and votes on what gets built next.

= Where do I report a bug? =
Open an issue on [GitHub](https://github.com/techanum/techanum-maintenance) or post in the WordPress.org support forum.

== Screenshots ==

1. The settings page — everything in one place.
2. The maintenance page as visitors see it.
3. AI message generation in action.

== Changelog ==

= 1.1.0 =
* Added native Anthropic (Claude) API support
* Added "Generate with AI" button with AJAX for on‑demand message generation
* Improved provider auto‑detection (sk‑, AIza‑, sk‑ant‑ prefixes)
* Added Custom provider for any OpenAI‑compatible endpoint
* Expanded error logging throughout the AI router
* Fixed Gemini endpoint (updated model from gemini‑pro to gemini‑2.0‑flash)
* Removed in‑plugin Pro promotion (plugin is now 100% free)

= 1.0.0 =
* Initial release
* Custom maintenance page with logo and message
* AI message generation (OpenAI, Gemini, AI/ML API)
* Admin notice hiding for non‑admin roles

== Support ==

- [Telegram Community](https://t.me/TechanumChat) — chat, ideas, and early feature discussions
- [WordPress.org Forum](https://wordpress.org/support/plugin/techanum-maintenance/)
- [GitHub Issues](https://github.com/techanum/techanum-maintenance/issues)