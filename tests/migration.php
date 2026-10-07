<?php
/** Tests the unmodified manufacturer Migration Mode with actual Oxygen packages. */
use Deckerweb\OxygenQuickNav\Builder;
use Deckerweb\OxygenQuickNav\Plugin;
wp_set_current_user(1); show_admin_bar(true);
require_once ABSPATH . WPINC . '/class-wp-admin-bar.php';
$enabled = 'yes' === getenv('OQN_MIGRATION_ENABLED');
$active = Builder::active();
if ($enabled !== in_array('modern',$active,true)) { throw new RuntimeException('Migration IP gating mismatch'); }
$bar = new WP_Admin_Bar(); (new Plugin())->toolbar($bar);
$node = $bar->get_node('oqn-modern-settings-migration-mode');
if ($enabled) {
 if (!$node || false === strpos($node->href,'page=oxygen_settings') || false === strpos($node->href,'tab=migration-mode')) { throw new RuntimeException('Missing native Oxygen Migration Mode shortcut'); }
 if ($node->parent !== 'oqn-modern-settings') { throw new RuntimeException('Wrong migration submenu position'); }
 if (!isset(Builder::tabs('modern')['migration-mode'])) { throw new RuntimeException('Unregistered tab'); }
 echo "PASS: actual registered Migration Mode tab, Oxygen URL and submenu parent\n";
} else {
 if ($node || $bar->get_node('ddw-oxygen-quicknav')) { throw new RuntimeException('Disabled builder still exposed'); }
 echo "PASS: unlisted IP disables Oxygen and QuickNav navigation\n";
}
echo 'RESULT: migration enabled=' . ($enabled?'yes':'no') . '; Oxygen=' . (defined('__BREAKDANCE_VERSION')?__BREAKDANCE_VERSION:'paused') . "\n";
