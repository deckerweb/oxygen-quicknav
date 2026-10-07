<?php
/** Isolated candidate validation regressions; run using WP-CLI eval-file. */
use Deckerweb\OxygenQuickNav\Updates;
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
WP_Filesystem();
$source = getenv( 'OQN_CANDIDATE' );
if ( ! $source || ! is_dir( $source ) ) { throw new RuntimeException( 'Missing isolated candidate directory.' ); }
$basename = plugin_basename( DDW_OQN_FILE );
$offer = (object) array( 'response' => array( $basename => (object) array( 'new_version' => '2.0.0' ) ) );
set_site_transient( 'update_plugins', $offer );
$context = array( 'plugin' => $basename, 'type' => 'plugin', 'action' => 'update' );
global $checks;
$checks = 0;
/** @param bool $ok Test result. @param string $label Assertion description. @return void */
function oqn_package_assert( $ok, $label ) { global $checks; if ( ! $ok ) { throw new RuntimeException( 'FAIL: ' . $label ); } $checks++; echo 'PASS: ' . $label . "\n"; }
$upgrader = new Plugin_Upgrader();
oqn_package_assert( $source === Updates::validate_update_source( $source, $source, $upgrader, $context ), 'complete offered newer package accepted' );
$other = $context; $other['plugin'] = 'other/other.php';
oqn_package_assert( '/missing' === Updates::validate_update_source( '/missing', '/missing', $upgrader, $other ), 'foreign update untouched' );
$upgrader->bulk = true;
oqn_package_assert( $source === Updates::validate_update_source( $source, $source, $upgrader, array( 'plugin' => $basename ) ), 'native bulk context accepted' );
$history = $source . '/includes/history.json'; $contents = file_get_contents( $history ); unlink( $history );
oqn_package_assert( is_wp_error( Updates::validate_update_source( $source, $source, $upgrader, $context ) ), 'missing local history rejected' ); file_put_contents( $history, $contents );
$library = $source . '/includes/deckerweb-plugin-library/src/Library.php'; $contents = file_get_contents( $library ); file_put_contents( $library, $contents . "\n// changed\n" );
oqn_package_assert( is_wp_error( Updates::validate_update_source( $source, $source, $upgrader, $context ) ), 'damaged Library manifest member rejected' ); file_put_contents( $library, $contents );
$main = $source . '/oxygen-quicknav.php'; $contents = file_get_contents( $main ); file_put_contents( $main, str_replace( 'Plugin Name: Oxygen QuickNav', 'Plugin Name: Other', $contents ) );
oqn_package_assert( is_wp_error( Updates::validate_update_source( $source, $source, $upgrader, $context ) ), 'wrong host identity rejected' ); file_put_contents( $main, $contents );
$offer->response[$basename]->new_version = '9.9.9'; set_site_transient( 'update_plugins', $offer );
oqn_package_assert( is_wp_error( Updates::validate_update_source( $source, $source, $upgrader, $context ) ), 'offered version mismatch rejected' );
echo 'RESULT: ' . $checks . " package checks\n";
