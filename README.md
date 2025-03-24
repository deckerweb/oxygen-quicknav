# Oxygen QuickNav (Oxygen 6+)

![Oxygen QuickNav plugin in action](https://raw.githubusercontent.com/deckerweb/oxygen-quicknav/master/assets-github/oxygen-quicknav-screenshot.png)

The **Oxygen QuickNav** plugin adds a quick-access navigator to the WordPress Admin Bar (Toolbar). It allows easy access to Oxygen Builder Templates, Headers, Footers, Components, and (regular WordPress) Pages edited with Oxygen, along with other essential settings. – _NOTE:_ This plugin is **for Oxygen 6+ only** (first released in February of 2025)!

### Tested Compatibility
- **Oxygen**: 6.0.0 Beta
- **WPSix Exporter**: 1.0.8
- **Yabe Webfont** 1.0.70 / 2.0.70
- **WordPress**: 6.7.2
- **PHP**: 8.3+

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

**Quick Install:**
1. **Download ZIP:** [**oxygen-quicknav.zip**](https://github.com/deckerweb/oxygen-quicknav/releases/latest/download/oxygen-quicknav.zip)
2. Upload via WordPress Plugins > Add New > Upload Plugin
3. Once activated, you’ll see the **Oxy** menu item in the Admin Bar.

---

## How this Plugin Works

1. **Pages, Templates, Headers, Footers, Components**: Displays up to 10 items, ordered by the last modified date (descending). The "Pages" menu only shows pages built with Oxygen by checking the `_oxygen_data` custom field.
2. **Settings**: Direct links to relevant sections.
3. **Additional Links**: Includes links to resources like the Oxygen website and Facebook group. Some may contain affiliate links.
4. **About**: Includes links to the plugin author.

---

## Custom Tweaks via Constants

### 1) Default capability (aka permission)
The intended usage of this plugin is for Administrator users only. Therefore the default capability to see the new Admin Bar node is set to `activate_plugins`. You can change this via the constant `OQN_VIEW_CAPABILITY` – define that via wp-config.php or via a Code Snippet plugin: `define( 'OQN_VIEW_CAPABILITY', 'edit_posts' );`

### 2) Name of main menu item
The default is just "Oxy" – catchy and short. However, if you don't enjoy "Oxy" you can tweak that also via the constant `OQN_NAME_IN_ADMINBAR` – define that also via wp-config.php or via a Code Snippet plugin: `define( 'OQN_NAME_IN_ADMINBAR', 'Oxygen Nav' );`

### 3) Default icon of main menu item 
The blue-ish default logo icon is awesome but a bit too dark-ish for my taste – at least within the Admin Bar. Therefore I pull in the builder icon intended for dark mode (light logo on dark background). If that is not there for whatever reason it pulls in the blue original icon (in local plugin folder). You can also tweak that via a constant in wp-config.php or via a Code Snippets plugin: `define( 'OQN_ICON', 'blue' );`

### 4) Disable footer items (Links & About)
To disable these menu items, just use another constant in wp-config.php or via a Code Snippets plugin: `define( 'OQN_DISABLE_FOOTER', 'yes' );`

---

## Changelog / Releases

### 1.0.0
- Initial release
- Includes support for "Breakdance Migration Mode" (official add-on) which also works with Oxygen 6+ (!!)
- Includes support for "Yabe Webfont" plugin (third-party; free & Pro version!)
- Includes support for "WPSix Exporter" plugin (third-party)
- _Note:_ Forked from "Breakdance Navigator" v1.0.1 by Peter Kulcsár (licensed GPL v2 or later)

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

Icon used in promo graphics: © Remix Icon

Readme & Plugin Copyright © 2025 David Decker – DECKERWEB.de