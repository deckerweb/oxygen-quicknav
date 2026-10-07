<?php
/** Mixed-Library activation regression; isolated no-builder WordPress required. */
use Deckerweb\OxygenQuickNav\Plugin;
use Deckerweb\OxygenQuickNav\Builder;
if ( Builder::active() ) { throw new RuntimeException( 'Use an isolated no-builder fixture.' ); }
wp_set_current_user( 1 );
global $checks; $checks = 0;
/** @param bool $ok Expected condition. @param string $label Assertion description. @return void */
function oqn_activation_assert( $ok, $label ) { global $checks; if ( ! $ok ) { throw new RuntimeException( 'FAIL: ' . $label ); } $checks++; echo 'PASS: ' . $label . "\n"; }
$library = $GLOBALS['deckerweb_library_runtime_v1'];
echo 'Elected Library: ' . constant( get_class( $library ) . '::VERSION' ) . "\n";
// Run the native pre-activation sequence, including cold handoff and own-host correction.
do_action( 'activate_plugin', plugin_basename( DDW_OQN_FILE ), false );
oqn_activation_assert( true, 'own host activation returns normally without a builder' );
/** @return callable Test-only die handler preserving the actual Library message. */
$handler = static function () {
	/** @param mixed $message WordPress error body. @param mixed $title Error title. @param mixed $args Die arguments. @return void */
	return static function ( $message, $title = '', $args = array() ) { throw new RuntimeException( wp_strip_all_tags( (string) $message ) ); };
};
add_filter( 'wp_die_handler', $handler, PHP_INT_MAX );
$blocked = false;
try { do_action( 'activate_plugin', 'breakdance-quicknav/breakdance-quicknav.php', false ); }
catch ( RuntimeException $error ) { $blocked = false !== stripos( $error->getMessage(), 'Breakdance' ); }
finally { remove_filter( 'wp_die_handler', $handler, PHP_INT_MAX ); }
oqn_activation_assert( $blocked, 'same request still enforces another host dependency' );
require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
require_once ABSPATH . 'wp-admin/includes/screen.php';
set_current_screen( 'plugins' );
$plugin = new Plugin(); ob_start(); $plugin->builder_notice(); $notice = ob_get_clean();
oqn_activation_assert( false !== strpos( $notice, 'notice notice-info' ) && false !== strpos( $notice, 'Oxygen 6+' ) && false !== strpos( $notice, 'Oxygen Classic 4.x' ), 'both generations named in native plugin-screen notice' );
ob_start(); $plugin->page(); $page = ob_get_clean();
oqn_activation_assert( false !== strpos( $page, 'notice notice-info inline' ) && false !== strpos( $page, 'Oxygen Classic 4.x' ), 'settings retain inline builder notice' );
echo 'RESULT: ' . $checks . " activation checks\n";
