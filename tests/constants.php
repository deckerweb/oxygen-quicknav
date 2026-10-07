<?php
/** Legacy immutable configuration and target isolation tests. */
use Deckerweb\OxygenQuickNav\Settings;
use Deckerweb\OxygenQuickNav\Plugin;
define( 'OQN_NAME_IN_ADMINBAR', 'Configured' );
define( 'OQN_NUMBER_TEMPLATES', 7 );
define( 'OQN_VIEW_CAPABILITY', 'edit_posts' );
define( 'OQN_ENABLED_USERS', array() );
define( 'OQN_DISABLE_FOOTER', 'yes' );
wp_set_current_user( 1 );
$values = Settings::get();
if ( 'Configured' !== $values['name'] || 7 !== $values['limit'] || 'edit_posts' !== $values['capability'] || array( -1 ) !== $values['users'] || $values['footer'] ) { throw new RuntimeException( 'Legacy precedence failed.' ); }
echo "PASS: legacy constants take final precedence\n";
require_once ABSPATH . WPINC . '/class-wp-admin-bar.php';
show_admin_bar( true ); $bar = new WP_Admin_Bar(); ( new Plugin() )->toolbar( $bar );
if ( $bar->get_node( 'ddw-oxygen-quicknav' ) ) { throw new RuntimeException( 'Empty configured user allowlist allowed a user.' ); }
echo "PASS: empty legacy allowlist denies every user\nRESULT: 2 constants checks\n";
