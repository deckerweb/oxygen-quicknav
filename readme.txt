=== Oxygen QuickNav ===
Contributors: daveshine
Tags: oxygen, toolbar, navigation
Requires at least: 6.7
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 2.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Quick access to Oxygen and Oxygen Classic content, templates, settings, and add-ons from the WordPress toolbar.

== Description ==

Version 2.1.0. Requires WordPress 6.7+ and PHP 8.1+. Includes deckerweb Updater 2.2.1 and Library 1.1.0. Oxygen has its own platform requirements.

* Automatic generation detection and generation-specific builder URLs.
* Pages, posts, and selected public custom post types; published content by default.
* Templates, headers, footers, and components in Oxygen; templates and reusable parts in Classic. Classic also links directly to stylesheets and export/import tools.
* Official add-on detection, form submissions, element management, and optional product navigation. Migration Mode is picked up automatically from registered settings tabs.
* Website defaults, personal profile preferences, legacy constants, and permission-aware destinations.
* Local English, German (Du), and German (Sie) resources, structured local history, and Multisite support.

== Installation ==

1. Install the oxygen-quicknav.zip test package under Plugins → Add New → Upload Plugin.
2. Activate QuickNav alongside your supported Oxygen builder.
3. Open Oxygen → QuickNav (or Settings → Oxygen QuickNav when Oxygen is inactive). Choose content types, groups, sorting, and visibility.
4. Open Users → Profile for personal visibility and item-count preferences.

== Frequently Asked Questions ==

= Does it choose the right Oxygen automatically? =

Yes. The active builder determines data queries, categories, permissions, settings, and edit URLs. If both integrations are present, they receive separate submenus. This does not make otherwise incompatible vendor plugins coexist.

= Why are there no separate headers and footers in Classic? =

Classic stores templates and reusable parts in ct_template. QuickNav follows that structure instead of guessing a header or footer from its name.

= Can editors use it? =

An administrator can change the required toolbar capability. Each destination still checks WordPress and Oxygen permissions; changing QuickNav visibility does not grant builder access.

= Can I show drafts, posts, or products? =

Yes. Choose public content types and optionally unpublished content in the settings. Only editable items with builder data appear. Products require their registered post type and user permission.

= Does network activation share my settings? =

No. Defaults and personal preferences are scoped to each website. New websites use defaults automatically. The shared Library manages its own website/network scope.

= What happens to my data when I uninstall? =

QuickNav settings remain by default. Optional deletion removes only each opted-in website’s QuickNav settings and personal preferences. Oxygen content stays. Library cleanup retains shared data while another embedded host is installed.

= Does it contact external services? =

The updater checks this public GitHub repository through WordPress update requests; GitHub receives normal request metadata. No token is required and no telemetry is added. The Library’s optional online catalog starts disabled; enabling it allows its documented GitHub catalog and package requests.

== Changelog ==

= 2.1.0 =

* New: QuickNav settings appear under the active Oxygen menu, with a WordPress Settings fallback when no Oxygen menu is available.
* Improved: Includes deckerweb Updater 2.2.1 and Library 1.1.0 with current host translations.
* Fixed: The local changelog remains hidden when closed and keeps a working documentation link if the dialog cannot open.
* Misc: Requires PHP 8.1 or later. The WordPress minimum remains 6.7.

= 2.0.0 =

* New: Automatic support for Oxygen and Oxygen Classic, including reusable parts and official add-on shortcuts.
* New: Website settings and personal toolbar preferences; configurable content types, groups, sorting, and unpublished content.
* New: Oxygen Classic navigation includes Stylesheets and Export & Import in their appropriate menu positions.
* Improved: Each destination respects WordPress and builder permissions. Missing builders safely pause navigation.
* Improved: Clearer Admin Dashboard labels, readable checkbox groups, matching artwork, and original resource links. Registered Oxygen settings tabs include Migration Mode when available.
* Fixed: Corrected builder detection, settings links, query filters, and builder URLs for subdirectory installations.
* Fixed: Activation works without a supported builder; settings remain available and notices appear within WordPress admin below the complete header.
* Misc: Uses native WordPress editor toolbar visibility. Includes deckerweb Updater 2.1.0 (PHP 8.1+) and Library 0.8.0 (PHP 8.0+); navigation retains PHP 7.4 compatibility.

= 1.1.0 =

* New: Configurable user IDs and item limits, Site Health information, German translations, and Git Updater integration.
* New: Toolbar display in block editor fullscreen mode.
* Improved: Toolbar styling and navigation defaults.

= 1.0.0 =

* New: Oxygen 6 toolbar navigation for pages, templates, headers, footers, components, and settings.
* New: Shortcuts for detected Migration Mode, Yabe Webfont, and WPSix Exporter integrations.
