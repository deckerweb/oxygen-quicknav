<?php
/** Actual vendor-menu placement and localized documentation regressions. */
use Deckerweb\OxygenQuickNav\Plugin;
use Deckerweb\OxygenQuickNav\Builder;
global $menu, $submenu;
$menu = array();
$submenu = array();
do_action( 'admin_menu' );
$expected = in_array( 'modern', Builder::active(), true ) ? 'oxygen' : ( in_array( 'classic', Builder::active(), true ) ? 'ct_dashboard_page' : 'options-general.php' );
$rows = $submenu[ $expected ] ?? array();
$found = array_filter( $rows, static function ( $row ) { return 'oxygen-quicknav' === ( $row[2] ?? '' ); } );
if ( 1 !== count( $found ) ) { throw new RuntimeException( 'Wrong native submenu parent: ' . $expected ); }
if ( 'options-general.php' !== $expected && isset( $submenu['options-general.php'] ) ) {
 foreach ( $submenu['options-general.php'] as $row ) { if ( 'oxygen-quicknav' === ( $row[2] ?? '' ) ) { throw new RuntimeException( 'Duplicate settings entry' ); } }
}
$url = Plugin::url();
if ( false === strpos( $url, ( 'options-general.php' === $expected ? 'options-general.php' : 'admin.php' ) . '?page=oxygen-quicknav' ) ) { throw new RuntimeException( 'Wrong settings URL' ); }
add_filter( 'pre_determine_locale', static function () { return 'de_DE'; } );
if ( false === strpos( Plugin::documentation_url( 'FAQ' ), 'FAQ-de.md' ) ) { throw new RuntimeException( 'German admin document not localized' ); }

echo 'RESULT: native menu, unique entry, action URL, localized documentation passed: ' . $expected . "\n";
