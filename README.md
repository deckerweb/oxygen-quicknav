# Oxygen QuickNav (Oxygen 6+)

![Oxygen QuickNav plugin in action](https://raw.githubusercontent.com/deckerweb/oxygen-quicknav/master/assets-github/oxygen-quicknav-screenshot.png)

The **Oxygen QuickNav** plugin adds a quick-access navigator to the WordPress Admin Bar (Toolbar). It allows easy access to Oxygen Builder Templates, Headers, Footers, Components, and (regular WordPress) Pages edited with Oxygen, along with other essential settings. – _NOTE:_ This plugin is **for Oxygen 6+ only** (first released in February of 2025)!

### Tested Compatibility
- **Oxygen**: 6.0.0 Beta
- **WordPress**: 6.7.2 / 6.8 Beta
- **PHP**: 8.0 – 8.3

---

[Support Project](#support-the-project) | [Installation](#installation) | [How Plugin Works](#how-this-plugin-works) | [Custom Tweaks](#custom-tweaks) | [Changelog](#changelog--releases) | [Plugin Scope / Disclaimer](#plugin-scope--disclaimer) | [Fork](#fork)

---

## Support the Project

If you find this project helpful, consider showing your support by buying me a coffee! Your contribution helps me keep developing and improving this plugin.

Enjoying the plugin? Feel free to treat me to a cup of coffee ☕🙂 through the following options:

- [![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/W7W81BNTZE)
- [Buy me a coffee](https://buymeacoffee.com/daveshine)
- [PayPal donation](https://paypal.me/deckerweb)
- [Join my **newsletter** for DECKERWEB WordPress Plugins](https://eepurl.com/gbAUUn)

---

## Installation

#### **Quick Install – as Plugin**
1. **Download ZIP:** [**oxygen-quicknav.zip**](https://github.com/deckerweb/oxygen-quicknav/releases/latest/download/oxygen-quicknav.zip)
2. Upload via WordPress Plugins > Add New > Upload Plugin
3. Once activated, you’ll see the **Oxy** menu item in the Admin Bar.

#### **Alternative: Use as Code Snippet**
1. Below, download the appropriate snippet version
2. activate or deactivate in your snippets plugin

[**Download .json**](https://github.com/deckerweb/oxygen-quicknav/releases/latest/download/ddw-oxygen-quicknav.code-snippets.json) version for: _Code Snippets_ (free & Pro), _Advanced Scripts_ (Premium), _Scripts Organizer_ (Premium)
--> just use their elegant script import features
--> in _Scripts Organizer_ use the "Code Snippets Import"

For all other snippet manager plugins just use our plugin's main .php file [`oxygen-quicknav.php`](https://github.com/deckerweb/oxygen-quicknav/blob/master/oxygen-quicknav.php) and use its content as snippet (bevor saving your snippet: please check for your plugin if the opening php tag needs to be removed or not!).

--> Please decide for one of both alternatives!

#### Minimum Requirements 
* WordPress version 6.7 or higher
* PHP version 7.4 or higher (better 8.3+)
* MySQL version 8.0 or higher / OR MariaDB 10.1 or higher
* Administrator user with capability `manage_options` and `activate_plugins`

---

## How this Plugin Works

1. **Pages, Templates, Headers, Footers, Components**: Displays up to 10 items, ordered by the last modified date (descending). The "Pages" menu only shows pages built with Oxygen by checking the `_oxygen_data` custom field.
2. **Settings**: Direct links to relevant sections.
3. **Additional Links**: Includes links to resources like the Oxygen website and Facebook group. Some may contain affiliate links.
4. **About**: Includes links to the plugin author.
5. Show Admin Bar also in Block Editor full screen mode. (Not in WP default but this plugin here changes that!)

---

## Custom Tweaks via Constants

### 1) Default capability (aka permission)
The intended usage of this plugin is for Administrator users only. Therefore the default capability to see the new Admin Bar node is set to `activate_plugins`. You can change this via the constant `OQN_VIEW_CAPABILITY` – define that via `wp-config.php` or via a Code Snippet plugin: `define( 'OQN_VIEW_CAPABILITY', 'edit_posts' );`

### 2) Restrict to defined user IDs only (since v1.1.0)
You can define an array of user IDs (can also be only _one_ ID) and that way restrict showing the Snippets Admin Bar item only for those users. Define that via `wp-config.php` or via a Code Snippet plugin:
```
define( 'OQN_ENABLED_USERS', [ 1, 500, 867 ] );
```
This would enable only for the users with the IDs 1, 500 and 867. Note the square brackets around, and no single quotes, just the ID numbers.

For example you are one of many admin users (role `administrator`) but _only you_ want to show it _for yourself_. Given you have user ID 1:
```
define( 'OQN_ENABLED_USERS', [ 1 ] );
```
That way only you can see it, the other admins can't!

### 3) Name of main menu item
The default is just "Oxy" – catchy and short. However, if you don't enjoy "Oxy" you can tweak that also via the constant `OQN_NAME_IN_ADMINBAR` – define that also via `wp-config.php` or via a Code Snippet plugin: `define( 'OQN_NAME_IN_ADMINBAR', 'Oxygen Nav' );`

### 4) Default icon of main menu item 
The blue-ish default logo icon is awesome but a bit too dark-ish for my taste – at least within the Admin Bar. Therefore I pull in the builder icon intended for dark mode (light logo on dark background). If that is not there for whatever reason it pulls in the blue original icon (in local plugin folder). You can also tweak that via a constant in `wp-config.php` or via a Code Snippets plugin: `define( 'OQN_ICON', 'blue' );`

### 5) Adjust the number of displayed Templates/ Pages.
The default number of displayed Templates/ Pages got increased to 20 (instead of 10). That means up to 20 items, starting from latest (newest) down to older ones. And, now you can adjust that value via constant in `wp-config.php` or via a Code Snippets plugin:
```
define( 'OQN_NUMBER_TEMPLATES', 5 );
```
In that example it would only display up to 5 items. NOTE: just add the number, no quotes around it.

### 6) Disable footer items (Links & About)
To disable these menu items, just use another constant in `wp-config.php` or via a Code Snippets plugin: `define( 'OQN_DISABLE_FOOTER', 'yes' );`

---

## [Changelog / Releases](https://github.com/deckerweb/oxygen-quicknav/releases)

### 🎉 v1.1.0 – 2025-04-??
* New: Show Admin Bar also in Block Editor full screen mode
* New: Adjust the number of shown Templates / Pages via constant (default: up to 20 - instead of 10) (new custom tweak)
* New: Optionally only enable for defined user IDs (new custom tweak)
* New: Add info to Site Health Debug, useful for our constants for custom tweaking
* New: Added `.pot` file (to translate plugin into your language), plus packaged German translations
* New: Installable and updateable via [Git Updater plugin](https://git-updater.com/)
* Change: Remove packaged icon image file in favor of svg-ed version, inline – makes "plugin" usable as code snippet
* Improved and simplified code to make better maintainable
* Plugin: Add meta links on WP Plugins page
* Alternate install: Use "plugin" as Code Snippet version (see under [Installation](#installation))

### 🎉 v1.0.0 – 2025-03-09
* Initial release
* Includes support for "Breakdance Migration Mode" (official add-on) which also works with Oxygen 6+ (!!)
* Includes support for "Yabe Webfont" plugin (third-party; free & Pro version!)
* Includes support for "WPSix Exporter" plugin (third-party)
* _Note:_ Forked from "Breakdance Navigator" v1.0.1 by Peter Kulcsár (licensed GPL v2 or later)

---

## Plugin Scope / Disclaimer

This plugin comes as is. I have no intention to add support for every little detail / third-party plugin / library etc. Its main focus is support for the template types and Oxygen 6+ settings. Plugin support is added where it makes sense for the daily work of an Administrator and Site Builder.

_Disclaimer 1:_ So far I will support the plugin for breaking errors to keep it working. Otherwise support will be very limited. Also, it will NEVER be released to WordPress.org Plugin Repository for a lot of reasons. Furthermore, I will ONLY add support for direct Oxygen 6+ add-on plugins. And I can only add support if I would own a license myself (for testing etc.). Therefore, if there might be Oxygen 6+ plugins you want me to add integration for, please open an issue on the plugin page on GitHub so we might discuss that. (Thanks in advance!)

_Disclaimer 2:_ All of the above might change. I do all this stuff only in my spare time.

_Most of all:_ Have fun building great Oxygen 6+ powered sites!!! ;-)

---

## Fork

_Note:_ This plugin was originally developed by [Peter Kulcsár](https://github.com/beamkiller) under the name ["Breakdance Navigator"](https://github.com/beamkiller/breakdance-navigator). Since it is licensed GPL v2 or later, I decided to fork it to aadapt it for Oxygen Builder v6+ and tweak some things. – Special thanks to the original author for his great work!

---

Official _Oxygen_ product logo icon: © Soflyy

Icon used in promo graphics: © Remix Icon

Readme & Plugin Copyright: © 2025, David Decker – DECKERWEB.de