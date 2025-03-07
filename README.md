# Oxygen QuickNav (Oxygen 6+)

The **Oxygen QuickNav** plugin adds a quick-access navigator to the WordPress Admin Bar (Toolbar). It allows easy access to Oxygen Builder Templates, Headers, Footers, Global Blocks, Popups, and (regular WordPress) Pages edited with Oxygen, along with other essential settings. – NOTE: This plugin is for Oxygen 6+ only (first released in February of 2025)!

### Tested Compatibility
- **Oxygen**: 6.0.0 Beta
- **WPSix Exporter**: 1.0.8
- **WordPress**: 6.7.2
- **PHP**: 8.3+

---

## Support the Project

If you find this project helpful, consider showing your support by buying me a coffee! Your contribution helps me keep developing and improving this plugin.

[![Buy me a coffee](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://buymeacoffee.com/daveshine)

Enjoying the plugin? Feel free to treat me to a cup of coffee ☕🙂 through the following options:

- [Buy me a coffee](https://buymeacoffee.com/daveshine)

---

## Installation

1. Click the `Code` button above and select `Download ZIP` to download the plugin.
2. In your WordPress admin dashboard, go to **Plugins** > **Add New**, then click on `Upload Plugin` and select the downloaded ZIP file.
3. Activate the plugin.
4. Once activated, you’ll see the **Oxy** menu item in the Admin Bar.

---

## How this Plugin Works

1. **Pages, Templates, Headers, Footers, Components**: Displays up to 10 items, ordered by the last modified date (descending). The "Pages" menu only shows pages built with Oxygen by checking the `_oxygen_data` custom field.
2. **Settings**: Direct links to relevant sections.
3. **Additional Links**: Includes links to resources like the Breakdance website and Facebook group. Some may contain affiliate links.
4. **About**: Includes links to the plugin author.

---

## Custom Tweaks via Constants

#### 1) Default capability (aka permission)
The intended usage of this plugin is for Administrator users only. Therefore the default capability to see the new Admin Bar node is set to `activate_plugins`. You can change this via the constant `OQN_VIEW_CAPABILITY` – define that via wp-config.php or via a Code Snippet plugin: `define( 'OQN_VIEW_CAPABILITY', 'edit_posts' );`

#### 2) Name of main menu item
The default is just "Oxy" – catchy and short. However, if you don't enjoy "Oxy" you can tweak that also via the constant `OQN_NAME_IN_ADMINBAR` – define that also via wp-config.php or via a Code Snippet plugin: `define( 'OQN_NAME_IN_ADMINBAR', 'Oxygen Nav' );`

#### 3) Default icon of main menu item 
The blue-ish default logo icon is awesome but a bit too dark-ish for my taste – at least within the Admin Bar. Therefore I pull in the builder icon intended for dark mode (light logo on dark background). If that is not there for whatever reason it pulls in the blue original icon (in local plugin folder). You can also tweak that via a constant in wp-config.php or via a Code Snippets plugin: `define( 'OQN_ICON', 'blue' );`

#### 4) Disable footer items (Links & About)
To disable these menu items, just use another constant in wp-config.php or via a Code Snippets plugin: `define( 'OQN_DISABLE_FOOTER', 'yes' );`

---

## Changelog

### 1.0.0
- Initial release
- Includes support for "Breakdance Migration Mode" (official add-on) which also works with Oxygen 6+ (!!)
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

_Note:_ This plugin was originally developed by Peter Kulcsár under the name "Breakdance Navigator". Since it is licensed GPL v2 or later, I decided to fork it to aadapt it for Oxygen Builder v6+ and tweak some things. – Special thanks to the original author for his great work!