<?php
/** Scoped uninstall; Oxygen and other plugins' data always remain. @package OxygenQuickNav */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }

$ddw_oqn_updater_class = require __DIR__ . '/includes/deckerweb-github-release-updater-v2.php';
if ( is_string( $ddw_oqn_updater_class ) && method_exists( $ddw_oqn_updater_class, 'clear_repository_cache' ) ) {
	$ddw_oqn_updater_class::clear_repository_cache( 'https://github.com/deckerweb/oxygen-quicknav' );
}

require_once __DIR__ . '/includes/deckerweb-plugin-library/lifecycle.php';
deckerweb_library_uninstall_v3( __DIR__ . '/oxygen-quicknav.php' );

/**
 * Remove one website's host settings only when that website opted in.
 * @return void Deletes QuickNav preferences; never Oxygen content or shared user accounts.
 */
function ddw_oqn_uninstall_site() {
	$settings = get_option( 'ddw_oqn_settings', array() );
	if ( is_array( $settings ) && ! empty( $settings['delete_data'] ) ) {
		delete_option( 'ddw_oqn_settings' );
		global $wpdb;
		$meta_key = $wpdb->get_blog_prefix( get_current_blog_id() ) . 'ddw_oqn_preferences';
		delete_metadata( 'user', 0, $meta_key, '', true );
	}
}

if ( is_multisite() ) {
	$ddw_oqn_offset = 0;
	do {
		$ddw_oqn_sites = get_sites( array( 'fields' => 'ids', 'number' => 100, 'offset' => $ddw_oqn_offset ) );
		foreach ( $ddw_oqn_sites as $ddw_oqn_site ) { switch_to_blog( $ddw_oqn_site ); ddw_oqn_uninstall_site(); restore_current_blog(); }
		$ddw_oqn_offset += 100;
	} while ( count( $ddw_oqn_sites ) === 100 );
} else { ddw_oqn_uninstall_site(); }
