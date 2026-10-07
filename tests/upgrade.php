<?php
/** Real local WordPress upgrader regression in an isolated installation. */
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
$package = getenv( 'OQN_UPGRADE_ZIP' );
if ( ! $package || ! is_file( $package ) ) { throw new RuntimeException( 'Missing test package.' ); }
$plugin = plugin_basename( DDW_OQN_FILE );
update_option( 'ddw_oqn_settings', array( 'name' => 'Retained', 'limit' => 13 ) );
set_site_transient( 'update_plugins', (object) array( 'checked' => array( $plugin => DDW_OQN_VERSION ), 'response' => array( $plugin => (object) array( 'slug' => 'oxygen-quicknav', 'plugin' => $plugin, 'new_version' => '2.0.0', 'package' => $package ) ) ) );
$upgrader = new Plugin_Upgrader( new WP_Upgrader_Skin() );
$result = $upgrader->upgrade( $plugin );
if ( true !== $result ) { throw new RuntimeException( 'Native update failed: ' . ( is_wp_error( $result ) ? $result->get_error_message() : var_export( $result, true ) ) ); }
wp_clean_plugins_cache( false ); $info = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
if ( '2.0.0' !== $info['Version'] || 'Retained' !== get_option( 'ddw_oqn_settings' )['name'] ) { throw new RuntimeException( 'Version or preserved settings mismatch.' ); }
echo "RESULT: 2 native upgrade checks (replacement and preserved settings)\n";
