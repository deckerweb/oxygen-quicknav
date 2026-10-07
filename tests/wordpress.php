<?php
/** Run with WP-CLI eval-file in an isolated installation. */
use Deckerweb\OxygenQuickNav\Builder;
use Deckerweb\OxygenQuickNav\Settings;
use Deckerweb\OxygenQuickNav\Plugin;
use Deckerweb\OxygenQuickNav\Updates;

global $checks;
$checks = 0;
/**
 * Assert a test condition and fail the CLI execution on regression.
 * @param bool $condition Expected truth value.
 * @param string $label Test label.
 * @return void Prints one passed assertion.
 */
function oqn_assert( $condition, $label ) {
	global $checks;
	if ( ! $condition ) { throw new RuntimeException( 'FAIL: ' . $label ); }
	$checks++; echo 'PASS: ' . $label . "\n";
}
require_once ABSPATH . WPINC . '/class-wp-admin-bar.php';
wp_set_current_user( 1 );
show_admin_bar( true );
$plugin = new Plugin();
$settings = Settings::defaults();
update_option( Settings::OPTION, $settings );
$generation = Builder::active()[0] ?? 'none';
$expected = getenv( 'OQN_EXPECT_GENERATION' ) ?: $generation;
oqn_assert( $expected === $generation, 'generation detection: ' . $expected );
$admin = new WP_Admin_Bar();
$plugin->toolbar( $admin );
oqn_assert( ( 'none' !== $generation ) === (bool) $admin->get_node( 'ddw-oxygen-quicknav' ), 'safe toolbar with/without builder' );
oqn_assert( ! has_action( 'enqueue_block_editor_assets', array( $plugin, 'adminbar_block_editor_fullscreen' ) ), 'no editor toolbar override' );
$sanitized = Settings::sanitize( array( 'limit' => array( 1 ), 'name' => array( 1 ), 'capability' => array( 1 ), 'post_types' => array( 'page', 'invalid' ), 'users' => '1,2;2 -3 nonsense' ) );
oqn_assert( 20 === $sanitized['limit'] && 'Oxy' === $sanitized['name'], 'malformed input safe defaults' );
oqn_assert( array( 'page' ) === $sanitized['post_types'], 'unregistered post types rejected' );
oqn_assert( array( 1, 2, 3 ) === $sanitized['users'], 'user IDs normalized and deduplicated' );
oqn_assert( 100 === Settings::sanitize( array( 'limit' => 999999 ) )['limit'], 'bounded content query limit' );
oqn_assert( array() === Settings::sanitize( array( 'groups' => array( '' ), 'post_types' => array( '' ) ) )['groups'], 'all groups can be disabled' );
$other = new WP_Admin_Bar();
$settings['users'] = array( 999999 ); update_option( Settings::OPTION, $settings ); $plugin->toolbar( $other );
oqn_assert( ! $other->get_node( 'ddw-oxygen-quicknav' ), 'user allowlist enforced' );
$settings['users'] = array(); update_option( Settings::OPTION, $settings );
update_user_option( 1, Settings::USER_OPTION, array( 'frontend' => 'hide', 'limit' => 7 ), false );
oqn_assert( ! Settings::get()['frontend'] && 7 === Settings::get()['limit'], 'personal site preferences' );
update_user_option( 1, Settings::USER_OPTION, array(), false );
$_POST = array( 'oqn_preferences' => array( 'limit' => 88 ) ); $plugin->save_profile( 1 );
oqn_assert( array() === get_user_option( Settings::USER_OPTION ), 'profile save without nonce rejected' );
$_POST['oqn_profile_nonce'] = wp_create_nonce( 'oqn-profile-1' ); $plugin->save_profile( 1 );
oqn_assert( 88 === get_user_option( Settings::USER_OPTION )['limit'], 'nonce-authorized profile save' );
update_user_option( 1, Settings::USER_OPTION, array(), false );
$subscriber = wp_insert_user( array( 'user_login' => 'oqn-subscriber-' . wp_generate_password( 8, false ), 'user_pass' => wp_generate_password(), 'role' => 'subscriber' ) );
wp_set_current_user( $subscriber ); $_POST['oqn_profile_nonce'] = wp_create_nonce( 'oqn-profile-1' ); $plugin->save_profile( 1 );
oqn_assert( array() === get_user_option( Settings::USER_OPTION, 1 ), 'profile capability rejects another user' );
$bar = new WP_Admin_Bar(); $plugin->toolbar( $bar );
oqn_assert( ! $bar->get_node( 'ddw-oxygen-quicknav' ), 'subscriber cannot see toolbar' );
wp_set_current_user( 1 );
$links = $plugin->action_links( array( 'deactivate' => '<a>Deactivate</a>' ) );
oqn_assert( false !== strpos( reset( $links ), 'options-general.php?page=oxygen-quicknav' ), 'settings action comes first' );
$meta = implode( ' ', $plugin->row_meta( array(), plugin_basename( DDW_OQN_FILE ) ) );
oqn_assert( false === strpos( $meta, 'MERGE0' ) && false === strpos( $meta, 'test@example' ), 'newsletter has no personal parameters' );
$sections = $plugin->diagnostics( array() );
oqn_assert( ! isset( $sections['oxygen-quicknav']['fields']['OQN_ENABLED_USERS'] ), 'diagnostics exclude personal user IDs' );
if ( 'none' !== $generation ) {
	$key = 'modern' === $generation ? '_oxygen_data' : '_ct_builder_json';
	$ids = array();
	foreach ( array( 'publish', 'draft' ) as $status ) {
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_title' => 'QuickNav ' . $status, 'post_status' => $status ) );
		update_post_meta( $id, $key, '{"tree_json_string":"{\"root\":{\"children\":[1]}}"}' ); $ids[ $status ] = $id;
	}
	$empty = wp_insert_post( array( 'post_type' => 'page', 'post_title' => 'Empty meta', 'post_status' => 'publish' ) ); update_post_meta( $empty, $key, '' );
	$settings = Settings::defaults();
	$posts = Builder::posts( 'page', $generation, 'content', $settings ); $found = wp_list_pluck( $posts, 'ID' );
	oqn_assert( in_array( $ids['publish'], $found, true ) && ! in_array( $ids['draft'], $found, true ) && ! in_array( $empty, $found, true ), 'published builder content only by default' );
	$settings['drafts'] = true; $found = wp_list_pluck( Builder::posts( 'page', $generation, 'content', $settings ), 'ID' );
	oqn_assert( in_array( $ids['draft'], $found, true ), 'draft content opt-in' );
	update_option( 'siteurl', 'http://127.0.0.1:8097/wordpress' ); update_option( 'home', 'http://127.0.0.1:8097' );
	$url = Builder::edit_url( $ids['publish'], $generation );
	oqn_assert( false === strpos( $url, '/wordpress/' ), 'builder URL respects home URL' );
	oqn_assert( false !== strpos( $url, 'modern' === $generation ? 'oxygen=builder' : 'ct_builder=true' ), 'native generation-specific edit URL' );
	update_option( 'siteurl', 'http://127.0.0.1:8097' );
	$filter = static function ( $args ) { $args['post__in'] = array( -1 ); return $args; };
	add_filter( 'ddw/quicknav/oxy_get_template_type', $filter );
	oqn_assert( ! Builder::posts( 'page', $generation, 'content', $settings ), 'query filter return applied' ); remove_filter( 'ddw/quicknav/oxy_get_template_type', $filter );
	$filter = static function ( $tabs ) { return array( 'tools' => 'Filtered tools' ); }; add_filter( 'ddw/quicknav/oxy_settings', $filter );
	oqn_assert( array( 'tools' => 'Filtered tools' ) === Builder::tabs( $generation ), 'settings filter return applied' ); remove_filter( 'ddw/quicknav/oxy_settings', $filter );
	if ( 'modern' === $generation ) {
		$tabs = Builder::tabs( $generation );
		oqn_assert( isset( $tabs['bloat_eliminator'], $tabs['header_footer'], $tabs['elements'] ), 'actual modern registered settings tabs' );
		$id = wp_insert_post( array( 'post_type' => 'oxygen_template', 'post_title' => 'Renamed internal template', 'post_status' => 'publish' ) );
		update_post_meta( $id, '_oxygen_template_settings', '{"fallback":true}' );
		oqn_assert( ! in_array( $id, wp_list_pluck( Builder::posts( 'oxygen_template', 'modern', 'templates', $settings ), 'ID' ), true ), 'fallback hidden by metadata, independent of title' );
		$id2 = wp_insert_post( array( 'post_type' => 'oxygen_template', 'post_title' => 'Fallback: user template', 'post_status' => 'publish' ) );
		update_post_meta( $id2, '_oxygen_template_settings', '{"fallback":false}' );
		oqn_assert( in_array( $id2, wp_list_pluck( Builder::posts( 'oxygen_template', 'modern', 'templates', $settings ), 'ID' ), true ), 'legitimate template title preserved' );
	} else {
		$reusable = wp_insert_post( array( 'post_type' => 'ct_template', 'post_title' => 'Reusable', 'post_status' => 'publish' ) ); update_post_meta( $reusable, '_ct_template_type', 'reusable_part' );
		$template = wp_insert_post( array( 'post_type' => 'ct_template', 'post_title' => 'Template', 'post_status' => 'publish' ) ); update_post_meta( $template, '_ct_template_type', 'other_template' );
		oqn_assert( in_array( $reusable, wp_list_pluck( Builder::posts( 'ct_template', 'classic', 'components', $settings ), 'ID' ), true ), 'Classic reusable parts detected' );
		oqn_assert( ! in_array( $reusable, wp_list_pluck( Builder::posts( 'ct_template', 'classic', 'templates', $settings ), 'ID' ), true ), 'Classic reusable parts separated from templates' );
		update_post_meta( $template, '_ct_builder_json', '{"name":"ct_inner_content"}' );
		oqn_assert( false !== strpos( Builder::edit_url( $template, 'classic' ), 'ct_inner=true' ), 'Classic native inner content flag' );
		oqn_assert( ! isset( Builder::groups( 'classic' )['headers'] ), 'no invented Classic header type' );
	}

	$bar = new WP_Admin_Bar(); $plugin->toolbar( $bar );
	if ( defined( 'BREAKDANCE_FORMS_FOR_OXYGEN_VERSION' ) && 'modern' === $generation ) {
		oqn_assert( (bool) $bar->get_node( 'oqn-modern-addon-forms' ), 'official Forms submissions shortcut' );
	}
	if ( defined( 'BREAKDANCE_ELEMENTS_FOR_OXYGEN_VERSION' ) && 'modern' === $generation ) {
		oqn_assert( (bool) $bar->get_node( 'oqn-modern-addon-elements' ), 'official Elements management shortcut' );
	}
	if ( defined( 'CT_OXYGEN_GUTENBERG_VERSION' ) && 'classic' === $generation ) {
		oqn_assert( (bool) $bar->get_node( 'oqn-classic-addon-gutenberg' ), 'Classic Gutenberg settings shortcut' );
	}
	if ( defined( 'CT_OXYGEN_WOOCOMMERCE_VERSION' ) && 'classic' === $generation ) {
		oqn_assert( isset( Builder::addons()['Oxygen WooCommerce'] ), 'Classic WooCommerce version detected' );
		oqn_assert( ! $bar->get_node( 'oqn-classic-addon-woocommerce-settings' ), 'no fictitious Classic WooCommerce settings page' );
	}
	wp_set_current_user( $subscriber );
	oqn_assert( '' === Builder::edit_url( $ids['publish'], $generation ), 'unauthorized builder URL withheld' ); wp_set_current_user( 1 );
}
foreach ( array( 'de_DE' => 'Deine Direktwege. Beide Oxygen-Generationen.', 'de_DE_formal' => 'Ihre Direktwege. Beide Oxygen-Generationen.' ) as $locale => $expected_text ) {
	unload_textdomain( 'oxygen-quicknav', true );
	load_textdomain( 'oxygen-quicknav', DDW_OQN_DIR . '/languages/oxygen-quicknav-' . $locale . '.mo', $locale );
	oqn_assert( $expected_text === __( 'Your shortcuts. Both Oxygen generations.', 'oxygen-quicknav' ), $locale . ' host translation' );
}
$history = require DDW_OQN_DIR . '/includes/plugin-history.php';
oqn_assert( false !== strpos( $history( true ), 'Neu:' ) && false === strpos( $history( true ), '<pre>' ), 'structured categorized German history' );
ob_start(); $plugin->page(); $html = ob_get_clean();
oqn_assert( false !== strpos( $html, 'action="options.php"' ) && false !== strpos( $html, '<dialog' ), 'native Settings API and accessible history dialog' );
oqn_assert( false === strpos( $html, 'is-fullscreen-mode' ), 'native editor visibility untouched' );
oqn_assert( '2.1.0' === \Deckerweb\GitHubReleaseUpdater\V2\Updater::IMPLEMENTATION_VERSION, 'pinned updater registered' );
oqn_assert( isset( $GLOBALS['deckerweb_library_candidates_v1'] ), 'shared Library registered' );
echo 'RESULT: ' . $checks . ' checks; WordPress ' . $GLOBALS['wp_version'] . '; PHP ' . PHP_VERSION . '; ' . $generation . "\n";
