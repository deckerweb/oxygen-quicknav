<?php
/** Toolbar and native WordPress settings UI. @package OxygenQuickNav */
namespace Deckerweb\OxygenQuickNav;

defined( 'ABSPATH' ) || exit;

/** Coordinates toolbar rendering, diagnostics, and website/user settings. */
final class Plugin {
	/** @return void Registers one instance after all active plugins load. */
	public static function boot() {
		new self();
	}

	/** @return void Registers hooks without querying content or making remote requests. */
	public function __construct() {
		add_action( 'init', array( $this, 'translations' ), 5 );
		add_action( 'init', array( '\Deckerweb\OxygenQuickNav\Updates', 'register' ), 20 );
		add_action( 'admin_bar_menu', array( $this, 'toolbar' ), 999 );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 99 );
		add_action( 'admin_notices', array( $this, 'builder_notice' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( DDW_OQN_FILE ), array( $this, 'action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'row_meta' ), 10, 2 );
		add_filter( 'debug_information', array( $this, 'diagnostics' ) );
		add_action( 'show_user_profile', array( $this, 'profile' ) );
		add_action( 'edit_user_profile', array( $this, 'profile' ) );
		add_action( 'personal_options_update', array( $this, 'save_profile' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_profile' ) );
	}

	/** @return void Makes packaged host translations available from init onward. */
	public function translations() {
		load_plugin_textdomain( 'oxygen-quicknav', false, dirname( plugin_basename( DDW_OQN_FILE ) ) . '/languages' );
	}

	/**
	 * Resolve a repository document in the active admin language.
	 * @param string $document Maintained document basename (FAQ or CHANGELOG).
	 * @return string Real formatted repository document URL.
	 */
	public static function documentation_url( $document ) {
		$language = 0 === strpos( determine_locale(), 'de' ) ? '-de' : '';
		return 'https://github.com/deckerweb/oxygen-quicknav/blob/main/docs/' . $document . $language . '.md';
	}

	/** @return string Website-scoped settings URL. */
	public static function url() {
		return admin_url( ( self::parent_menu() ? 'admin.php' : 'options-general.php' ) . '?page=oxygen-quicknav' );
	}

	/**
	 * Find the active generation's native parent without creating an empty menu.
	 * @return string Registered Oxygen parent slug, or an empty fallback marker.
	 */
	public static function parent_menu() {
		global $menu;
		$active = Builder::active();
		foreach ( array( 'modern' => 'oxygen', 'classic' => 'ct_dashboard_page' ) as $generation => $slug ) {
			if ( ! in_array( $generation, $active, true ) ) { continue; }
			if ( ! is_admin() ) {
				$full = 'modern' === $generation
					? ( function_exists( '\\Breakdance\\Permissions\\hasPermission' ) && \Breakdance\Permissions\hasPermission( 'full' ) && current_user_can( 'manage_options' ) )
					: ( function_exists( 'oxygen_vsb_current_user_can_full_access' ) && oxygen_vsb_current_user_can_full_access() );
				if ( $full ) { return $slug; }
				continue;
			}
			foreach ( (array) $menu as $item ) {
				if ( isset( $item[2] ) && $slug === $item[2] ) { return $slug; }
			}
		}
		return '';
	}

	/** @return void Adds settings under the available Oxygen menu, with a native fallback. */
	public function admin_menu() {
		$parent = self::parent_menu();
		if ( $parent ) {
			add_submenu_page( $parent, 'Oxygen QuickNav', 'QuickNav', 'manage_options', 'oxygen-quicknav', array( $this, 'page' ) );
		} else {
			add_options_page( 'Oxygen QuickNav', 'Oxygen QuickNav', 'manage_options', 'oxygen-quicknav', array( $this, 'page' ) );
		}
	}

	/** @return void Explains inactive navigation within the website's native plugin screen. */
	public function builder_notice() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'plugins' !== $screen->id || is_network_admin() || ! current_user_can( 'activate_plugins' ) || Builder::active() ) { return; }
		echo '<div class="notice notice-info"><p><strong>Oxygen QuickNav:</strong> ' . esc_html__( 'No supported Oxygen builder (Oxygen 6+ or Oxygen Classic 4.x) is active. QuickNav settings remain available; toolbar navigation resumes when a supported builder is active.', 'oxygen-quicknav' );
		if ( current_user_can( 'manage_options' ) ) { echo ' <a href="' . esc_url( self::url() ) . '">' . esc_html__( 'Settings', 'oxygen-quicknav' ) . '</a>'; }
		echo '</p></div>';
	}

	/** @return void Registers one sanitized website option with the WordPress Settings API. */
	public function register_settings() {
		register_setting( 'ddw_oqn', Settings::OPTION, array( 'type' => 'array', 'sanitize_callback' => array( $this, 'save_settings' ), 'default' => Settings::defaults() ) );
	}


	/**
	 * Preserve stored defaults for fields locked by deployment constants.
	 * @param mixed $input Submitted website settings.
	 * @return array Validated website settings with existing locked values retained.
	 */
	public function save_settings( $input ) {
		$input = is_array( $input ) ? $input : array();
		$old = get_option( Settings::OPTION, Settings::defaults() );
		$old = is_array( $old ) ? array_merge( Settings::defaults(), $old ) : Settings::defaults();
		foreach ( Settings::constant_map() as $key => $constant ) { if ( defined( $constant ) ) { $input[ $key ] = $old[ $key ]; } }
		return Settings::sanitize( $input );
	}

	/**
	 * Check the user's toolbar visibility independently of target permissions.
	 * @param array $settings Effective settings.
	 * @return bool Whether QuickNav may render in the current website context.
	 */
	private function visible( $settings ) {
		return ! is_network_admin() && ! is_user_admin() && is_user_logged_in() && is_admin_bar_showing()
			&& ! empty( $settings[ is_admin() ? 'backend' : 'frontend' ] )
			&& current_user_can( $settings['capability'] )
			&& ( empty( $settings['users'] ) || in_array( get_current_user_id(), $settings['users'], true ) );
	}

	/**
	 * Load local UI assets only on the host page or an eligible visible toolbar.
	 * @param string $hook Optional WordPress admin screen hook suffix.
	 * @return void Enqueues scoped CSS and settings-only accessible dialog JS.
	 */
	public function assets( $hook = '' ) {
		$page = (bool) preg_match( '/(?:^|_)page_oxygen-quicknav$/', $hook ) || 'settings_page_oxygen-quicknav' === $hook;
		if ( ! $page && ( ! $this->visible( Settings::get() ) || ! Builder::active() ) ) { return; }
		wp_enqueue_style( 'ddw-oqn', plugins_url( 'assets/quicknav.css', DDW_OQN_FILE ), array(), DDW_OQN_VERSION );
		if ( $page ) { wp_enqueue_script( 'ddw-oqn-dialog', plugins_url( 'assets/dialog.js', DDW_OQN_FILE ), array(), DDW_OQN_VERSION, true ); }
	}

	/**
	 * Prepend the settings action in the website plugin list.
	 * @param array $links Existing action links.
	 * @return array Settings followed by existing actions when authorized.
	 */
	public function action_links( $links ) {
		if ( ! is_network_admin() && current_user_can( 'manage_options' ) ) { array_unshift( $links, '<a href="' . esc_url( self::url() ) . '">' . esc_html__( 'Settings', 'oxygen-quicknav' ) . '</a>' ); }
		return $links;
	}

	/**
	 * Add neutral documentation and support links without personal URL parameters.
	 * @param array $links Existing metadata links.
	 * @param string $file Listed plugin basename.
	 * @return array Unchanged links or the augmented host row.
	 */
	public function row_meta( $links, $file ) {
		if ( plugin_basename( DDW_OQN_FILE ) !== $file ) { return $links; }
		$links[] = '<a href="https://github.com/deckerweb/oxygen-quicknav" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Documentation', 'oxygen-quicknav' ) . '</a>';
		$links[] = '<a href="https://ko-fi.com/deckerweb" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Support this plugin', 'oxygen-quicknav' ) . '</a>';
		$links[] = '<a href="https://eepurl.com/gbAUUn" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Newsletter', 'oxygen-quicknav' ) . '</a>';
		return $links;
	}

	/**
	 * Render only active generations and authorized navigation targets.
	 * @param \WP_Admin_Bar $bar WordPress toolbar.
	 * @return void Adds generation-scoped nodes without altering native editor visibility.
	 */
	public function toolbar( $bar ) {
		$settings = Settings::get();
		$builders = Builder::active();
		if ( ! $builders || ! $this->visible( $settings ) ) { return; }
		$title = esc_html( $settings['name'] );
		if ( 'none' !== $settings['icon'] ) {
			$icon = 'blue' === $settings['icon'] ? 'images/oxygen-icon.png' : 'assets/navigation.svg';
			$title = '<img class="oqn-icon" src="' . esc_url( plugins_url( $icon, DDW_OQN_FILE ) ) . '" alt="" width="16" height="16">' . $title;
		}
		$root = 'ddw-oxygen-quicknav';
		$bar->add_node( array( 'id' => $root, 'title' => $title, 'href' => current_user_can( 'manage_options' ) ? self::url() : '#', 'meta' => array( 'menu_title' => 'Oxygen QuickNav' ) ) );
		foreach ( $builders as $generation ) {
			$parent = $root;
			if ( count( $builders ) > 1 ) {
				$parent = 'oqn-' . $generation;
				$bar->add_node( array( 'id' => $parent, 'parent' => $root, 'title' => 'classic' === $generation ? 'Oxygen Classic' : 'Oxygen' ) );
			}
			$prefix = 'oqn-' . $generation . '-';
			if ( in_array( 'content', $settings['groups'], true ) ) {
				foreach ( $settings['post_types'] as $type ) {
					$object = get_post_type_object( $type );
					if ( ! $object || ! current_user_can( $object->cap->edit_posts ) ) { continue; }
					$this->content_group( $bar, $prefix . 'content-' . $type, $parent, $object->labels->name, admin_url( 'edit.php?post_type=' . $type ), $type, $generation, 'content', $settings );
				}
			}
			foreach ( Builder::groups( $generation ) as $group => $info ) {
				$object = get_post_type_object( $info[1] );
				$full = 'modern' === $generation ? ( function_exists( '\Breakdance\Permissions\hasPermission' ) && \Breakdance\Permissions\hasPermission( 'full' ) ) : ( function_exists( 'oxygen_vsb_current_user_can_full_access' ) && oxygen_vsb_current_user_can_full_access() );
				if ( ! $full || ! $object || ! current_user_can( $object->cap->edit_posts ) || ! in_array( $group, $settings['groups'], true ) ) { continue; }
				$this->content_group( $bar, $prefix . $group, $parent, $info[0], $info[2], $info[1], $generation, $group, $settings );
			}
			if ( in_array( 'settings', $settings['groups'], true ) && current_user_can( 'classic' === $generation ? 'read' : 'manage_options' ) && $this->full_access( $generation ) ) {
				if ( 'classic' === $generation && function_exists( 'ct_css_management_page' ) ) {
					$bar->add_node( array( 'id' => $prefix . 'stylesheets', 'parent' => $parent, 'title' => esc_html__( 'Stylesheets', 'oxygen-quicknav' ), 'href' => admin_url( 'admin.php?page=ct_css_management' ) ) );
				}
				$id = $prefix . 'settings';
				$bar->add_node( array( 'id' => $id, 'parent' => $parent, 'title' => esc_html__( 'Oxygen Settings', 'oxygen-quicknav' ), 'href' => Builder::settings_url( $generation ) ) );
				foreach ( Builder::tabs( $generation ) as $tab => $label ) { $bar->add_node( array( 'id' => $id . '-' . sanitize_key( $tab ), 'parent' => $id, 'title' => esc_html( $label ), 'href' => Builder::settings_url( $generation, $tab ) ) ); }
				if ( 'classic' === $generation && function_exists( 'ct_export_import_page' ) ) {
					$bar->add_node( array( 'id' => $prefix . 'export-import', 'parent' => $id, 'title' => esc_html__( 'Export & Import', 'oxygen-quicknav' ), 'href' => admin_url( 'admin.php?page=ct_export_import' ) ) );
				}
				if ( 'modern' === $generation && function_exists( '\Breakdance\DesignLibrary\hideDesignLibrary' ) && ! \Breakdance\DesignLibrary\hideDesignLibrary() ) { $bar->add_node( array( 'id' => $prefix . 'design-library', 'parent' => $parent, 'title' => esc_html__( 'Design Library', 'oxygen-quicknav' ), 'href' => admin_url( 'admin.php?page=oxygen_design_library' ) ) ); }
			}
			if ( in_array( 'addons', $settings['groups'], true ) ) { $this->addon_nodes( $bar, $parent, $prefix, $generation ); }
		}
		if ( current_user_can( 'manage_options' ) ) { $bar->add_node( array( 'id' => 'oqn-own-settings', 'parent' => $root, 'title' => esc_html__( 'QuickNav Settings', 'oxygen-quicknav' ), 'href' => self::url() ) ); }
		if ( $settings['footer'] ) { $this->resources( $bar, $root ); }
	}

	/**
	 * Retain the original resource and author menus for either Oxygen generation.
	 * @param \WP_Admin_Bar $bar WordPress toolbar instance.
	 * @param string $root QuickNav root node.
	 * @return void Adds the original shared Links/About footer group.
	 */
	private function resources( $bar, $root ) {
		$bar->add_group( array( 'id' => 'oqn-group-footer', 'parent' => $root, 'meta' => array( 'class' => 'ab-sub-secondary' ) ) );
		$bar->add_node( array( 'id' => 'oqn-links', 'parent' => 'oqn-group-footer', 'title' => esc_html__( 'Links', 'oxygen-quicknav' ) ) );
		$links = array(
			'oxygen' => array( __( 'Oxygen HQ', 'oxygen-quicknav' ), 'https://oxygenbuilder.com/' ),
			'oxygen-learn' => array( __( 'Learn Oxygen (Tutorials)', 'oxygen-quicknav' ), 'https://oxygenbuilder.com/learn/' ),
			'oxygen-docs' => array( __( 'Oxygen Documentation', 'oxygen-quicknav' ), 'https://oxygenbuilder.com/documentation/' ),
			'oxygen-youtube' => array( __( 'Oxygen YouTube Channel', 'oxygen-quicknav' ), 'https://www.youtube.com/oxygen-builder' ),
			'oxygen-fb-group' => array( __( 'Oxygen FB Group', 'oxygen-quicknav' ), 'https://www.facebook.com/groups/1626639680763454' ),
			'oxygen4fun' => array( __( 'Oxygen4Fun (Tutorials, Tips, Resources)', 'oxygen-quicknav' ), 'https://oxygen4fun.supadezign.com/' ),
		);
		$bar->add_node( array( 'id' => 'oqn-about', 'parent' => 'oqn-group-footer', 'title' => esc_html__( 'About', 'oxygen-quicknav' ) ) );
		$about = array(
			'author' => array( __( 'Author: David Decker', 'oxygen-quicknav' ), 'https://deckerweb.de/' ),
			'github' => array( __( 'Plugin on GitHub', 'oxygen-quicknav' ), 'https://github.com/deckerweb/oxygen-quicknav' ),
			'kofi' => array( __( 'Buy Me a Coffee', 'oxygen-quicknav' ), 'https://ko-fi.com/deckerweb' ),
		);
		foreach ( array( 'links' => $links, 'about' => $about ) as $group => $items ) {
			foreach ( $items as $key => $item ) {
				$bar->add_node( array( 'id' => ( 'links' === $group ? 'oqn-link-' : 'oqn-about-' ) . $key, 'parent' => 'oqn-' . $group, 'title' => esc_html( $item[0] ), 'href' => esc_url( $item[1] ), 'meta' => array( 'target' => '_blank', 'rel' => 'nofollow noopener noreferrer' ) ) );
			}
		}
	}

	/**
	 * Check full builder access for administrative navigation.
	 * @param string $generation Supported generation identifier.
	 * @return bool Full interface permission.
	 */
	private function full_access( $generation ) {
		return 'classic' === $generation ? ( function_exists( 'oxygen_vsb_current_user_can_full_access' ) && oxygen_vsb_current_user_can_full_access() ) : ( function_exists( '\Breakdance\Permissions\hasPermission' ) && \Breakdance\Permissions\hasPermission( 'full' ) );
	}

	/**
	 * Add one archive and its authorized builder links.
	 * @param \WP_Admin_Bar $bar Toolbar instance.
	 * @param string $id Unique node ID.
	 * @param string $parent Parent node ID.
	 * @param string $label Group label.
	 * @param string $url Archive URL.
	 * @param string $type Registered post type.
	 * @param string $generation Supported generation identifier.
	 * @param string $group Content category.
	 * @param array $settings Effective configuration.
	 * @return void Adds bounded post nodes.
	 */
	private function content_group( $bar, $id, $parent, $label, $url, $type, $generation, $group, $settings ) {
		$bar->add_node( array( 'id' => $id, 'parent' => $parent, 'title' => esc_html( $label ), 'href' => esc_url( $url ) ) );
		foreach ( Builder::posts( $type, $generation, $group, $settings ) as $post ) {
			$title = '' !== trim( $post->post_title ) ? $post->post_title : __( '(no title)', 'oxygen-quicknav' );
			if ( 'publish' !== $post->post_status ) { $status = get_post_status_object( $post->post_status ); $title .= ' — ' . ( $status ? $status->label : $post->post_status ); }
			$bar->add_node( array( 'id' => $id . '-' . $post->ID, 'parent' => $id, 'title' => esc_html( $title ), 'href' => esc_url( Builder::edit_url( $post->ID, $generation ) ) ) );
		}
	}

	/**
	 * Add official addon destinations and preserve useful existing integrations.
	 * @param \WP_Admin_Bar $bar Toolbar instance.
	 * @param string $parent Parent node ID.
	 * @param string $prefix Generation-specific node prefix.
	 * @param string $generation Supported generation identifier.
	 * @return void Adds only detected, authorized destinations.
	 */
	private function addon_nodes( $bar, $parent, $prefix, $generation ) {
		$nodes = array();
		if ( 'modern' === $generation && defined( 'BREAKDANCE_FORMS_FOR_OXYGEN_VERSION' ) && post_type_exists( 'breakdance_form_res' ) && function_exists( '\Breakdance\Forms\Submission\canViewSubmissions' ) && \Breakdance\Forms\Submission\canViewSubmissions() ) { $nodes['forms'] = array( __( 'Form Submissions', 'oxygen-quicknav' ), admin_url( 'edit.php?post_type=breakdance_form_res' ) ); }
		if ( current_user_can( 'manage_options' ) && $this->full_access( $generation ) ) {
			if ( 'modern' === $generation && ( defined( 'BREAKDANCE_ELEMENTS_FOR_OXYGEN_VERSION' ) || defined( 'BREAKDANCE_FORMS_FOR_OXYGEN_VERSION' ) || defined( 'BREAKDANCE_WOOCOMMERCE_FOR_OXYGEN_VERSION' ) ) ) { $nodes['extensions'] = array( __( 'Official Extensions', 'oxygen-quicknav' ), Builder::settings_url( $generation, 'extensions' ) ); }
			if ( 'modern' === $generation && defined( 'BREAKDANCE_ELEMENTS_FOR_OXYGEN_VERSION' ) && isset( Builder::tabs( $generation )['elements'] ) ) { $nodes['elements'] = array( __( 'Element Management', 'oxygen-quicknav' ), Builder::settings_url( $generation, 'elements' ) ); }
			if ( 'classic' === $generation && defined( 'CT_OXYGEN_GUTENBERG_VERSION' ) && class_exists( 'Oxygen_Gutenberg' ) ) { $nodes['gutenberg'] = array( __( 'Gutenberg Settings', 'oxygen-quicknav' ), Builder::settings_url( $generation, 'gutenberg' ) ); }
			if ( 'modern' === $generation && class_exists( '\Yabe\Webfont\Plugin' ) ) { $nodes['webfont'] = array( 'Yabe Webfont', admin_url( 'themes.php?page=yabe_webfont' ) ); }
			if ( 'modern' === $generation && defined( 'WPSIX_EXPORTER_URL' ) ) { $nodes['exporter'] = array( 'WPSix Exporter', admin_url( 'admin.php?page=wpsix_exporter' ) ); }
		}
		if ( ( 'modern' === $generation && defined( 'BREAKDANCE_WOOCOMMERCE_FOR_OXYGEN_VERSION' ) ) || ( 'classic' === $generation && defined( 'CT_OXYGEN_WOOCOMMERCE_VERSION' ) ) ) {
			$object = get_post_type_object( 'product' );
			if ( $object && current_user_can( $object->cap->edit_posts ) ) { $nodes['products'] = array( $object->labels->name, admin_url( 'edit.php?post_type=product' ) ); }
		}
		foreach ( $nodes as $key => $info ) { $bar->add_node( array( 'id' => $prefix . 'addon-' . $key, 'parent' => $parent, 'title' => esc_html( $info[0] ), 'href' => esc_url( $info[1] ) ) ); }
	}

	/**
	 * Publish dataminimal builder and component versions in Site Health.
	 * @param array $info Existing debug sections.
	 * @return array Debug sections with no user IDs, keys, licenses, or content.
	 */
	public function diagnostics( $info ) {
		$fields = array( 'plugin' => array( 'label' => __( 'Plugin version', 'oxygen-quicknav' ), 'value' => DDW_OQN_VERSION ), 'builders' => array( 'label' => __( 'Active builders', 'oxygen-quicknav' ), 'value' => implode( ', ', Builder::active() ) ?: __( 'None', 'oxygen-quicknav' ) ) );
		foreach ( Builder::active() as $generation ) { $fields[ $generation ] = array( 'label' => 'classic' === $generation ? 'Oxygen Classic' : 'Oxygen', 'value' => 'classic' === $generation ? CT_VERSION : __BREAKDANCE_VERSION ); }
		foreach ( Builder::addons() as $name => $version ) { $fields[ sanitize_key( $name ) ] = array( 'label' => $name, 'value' => $version ); }
		$fields['library'] = array( 'label' => 'deckerweb Library', 'value' => '1.1.0' );
		$fields['updater'] = array( 'label' => 'deckerweb Updater', 'value' => '2.2.1' );
		$fields['overrides'] = array( 'label' => __( 'Configuration overrides', 'oxygen-quicknav' ), 'value' => implode( ', ', array_filter( Settings::constant_map(), 'defined' ) ) ?: __( 'None', 'oxygen-quicknav' ) );
		$info['oxygen-quicknav'] = array( 'label' => 'Oxygen QuickNav', 'fields' => $fields );
		return $info;
	}

	/** @return void Renders the authorized settings page with local structured history. */
	public function page() {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'You do not have permission to manage these settings.', 'oxygen-quicknav' ) ); }
		$s = Settings::get( false );
		echo '<div class="wrap oqn-settings"><header class="oqn-header"><img src="' . esc_url( plugins_url( 'assets/brand/icon.svg', DDW_OQN_FILE ) ) . '" width="52" height="52" alt=""><div><h1>Oxygen QuickNav</h1><p>' . esc_html__( 'Your shortcuts. Both Oxygen generations.', 'oxygen-quicknav' ) . '</p></div></header><hr class="wp-header-end">';
		if ( self::parent_menu() ) { settings_errors(); }
		if ( ! Builder::active() ) { echo '<div class="notice notice-info inline"><p>' . esc_html__( 'No supported Oxygen builder (Oxygen 6+ or Oxygen Classic 4.x) is active. QuickNav settings remain available; toolbar navigation resumes when a supported builder is active.', 'oxygen-quicknav' ) . '</p></div>'; }
		if ( version_compare( PHP_VERSION, '8.1', '<' ) || Updates::$error ) { echo '<div class="notice notice-warning inline"><p>' . esc_html__( 'Navigation remains available. The bundled Library needs PHP 8.0; the updater integration needs PHP 8.1 and a compatible deckerweb Updater. Update manually while a component is unavailable.', 'oxygen-quicknav' ) . '</p></div>'; }
		echo '<form method="post" action="options.php">';
		settings_fields( 'ddw_oqn' );
		echo '<h2>' . esc_html__( 'Display & Content', 'oxygen-quicknav' ) . '</h2><p>' . esc_html__( 'These defaults apply to this website. Personal toolbar preferences are available in your user profile.', 'oxygen-quicknav' ) . '</p><table class="form-table" role="presentation">';
		$this->field( 'name', __( 'Toolbar label', 'oxygen-quicknav' ), 'text', $s );
		$this->field( 'icon', __( 'Icon', 'oxygen-quicknav' ), array( 'auto' => __( 'Navigation icon', 'oxygen-quicknav' ), 'blue' => __( 'Original blue icon', 'oxygen-quicknav' ), 'none' => __( 'No icon', 'oxygen-quicknav' ) ), $s );
		$this->field( 'limit', __( 'Items per group', 'oxygen-quicknav' ), 'number', $s );
		$this->field( 'orderby', __( 'Sort by', 'oxygen-quicknav' ), array( 'modified' => __( 'Recently modified', 'oxygen-quicknav' ), 'title' => __( 'Title', 'oxygen-quicknav' ) ), $s );
		foreach ( array( 'backend' => __( 'Show in the Admin Dashboard', 'oxygen-quicknav' ), 'frontend' => __( 'Show on the website', 'oxygen-quicknav' ), 'footer' => __( 'Show Links & About', 'oxygen-quicknav' ), 'drafts' => __( 'Include unpublished content you can edit', 'oxygen-quicknav' ) ) as $key => $label ) { $this->field( $key, $label, 'checkbox', $s ); }
		echo '<tr><th scope="row" id="oqn-content-types-label">' . esc_html__( 'Content types', 'oxygen-quicknav' ) . '</th><td><fieldset class="oqn-choices" aria-labelledby="oqn-content-types-label"><input type="hidden" name="ddw_oqn_settings[post_types][]" value="">';
		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $type => $object ) { $this->choice( 'post_types', $type, $object->labels->name, $s['post_types'] ); }
		echo '</fieldset></td></tr><tr><th scope="row" id="oqn-menu-groups-label">' . esc_html__( 'Menu groups', 'oxygen-quicknav' ) . '</th><td><fieldset class="oqn-choices" aria-labelledby="oqn-menu-groups-label"><input type="hidden" name="ddw_oqn_settings[groups][]" value="">';
		foreach ( array( 'content' => __( 'Content', 'oxygen-quicknav' ), 'templates' => __( 'Templates', 'oxygen-quicknav' ), 'headers' => __( 'Headers', 'oxygen-quicknav' ), 'footers' => __( 'Footers', 'oxygen-quicknav' ), 'components' => __( 'Components / Reusable Parts', 'oxygen-quicknav' ), 'settings' => __( 'Oxygen Settings', 'oxygen-quicknav' ), 'addons' => __( 'Add-ons', 'oxygen-quicknav' ) ) as $key => $label ) { $this->choice( 'groups', $key, $label, $s['groups'] ); }
		echo '<p class="description">' . esc_html__( 'Only groups available in the active builder are displayed.', 'oxygen-quicknav' ) . '</p></fieldset></td></tr></table><h2>' . esc_html__( 'Access', 'oxygen-quicknav' ) . '</h2><table class="form-table" role="presentation">';
		$this->field( 'capability', __( 'Required toolbar capability', 'oxygen-quicknav' ), 'text', $s );
		$this->field( 'users', __( 'Restrict to user IDs', 'oxygen-quicknav' ), 'text', $s );
		echo '</table><p>' . esc_html__( 'Leave user IDs empty to allow all eligible users. Separate IDs with commas. WordPress and Oxygen permissions always apply to each destination.', 'oxygen-quicknav' ) . '</p><h2>' . esc_html__( 'Data', 'oxygen-quicknav' ) . '</h2><table class="form-table" role="presentation">';
		$this->field( 'delete_data', __( 'Delete QuickNav settings when uninstalling', 'oxygen-quicknav' ), 'checkbox', $s );
		echo '</table><p>' . esc_html__( 'Disabled by default. When enabled, uninstall removes this website’s QuickNav settings and personal QuickNav preferences. Oxygen content and other plugins’ data are always retained. Network uninstall applies each website’s own choice.', 'oxygen-quicknav' ) . '</p>';
		submit_button();
		echo '</form><h2>' . esc_html__( 'Detected components', 'oxygen-quicknav' ) . '</h2><dl class="oqn-diagnostics">';
		foreach ( $this->diagnostics( array() )['oxygen-quicknav']['fields'] as $field ) { echo '<dt>' . esc_html( $field['label'] ) . '</dt><dd>' . esc_html( $field['value'] ) . '</dd>'; }
		echo '</dl><footer class="oqn-footer" aria-label="' . esc_attr__( 'Plugin information', 'oxygen-quicknav' ) . '"><div><strong>Oxygen QuickNav</strong> <span>' . esc_html__( 'Version', 'oxygen-quicknav' ) . ' ' . esc_html( DDW_OQN_VERSION ) . '</span> · <a class="button-link" href="' . esc_url( self::documentation_url( 'CHANGELOG' ) ) . '" data-oqn-open="oqn-history">' . esc_html__( 'Changelog', 'oxygen-quicknav' ) . '</a> · <a href="' . esc_url( self::documentation_url( 'FAQ' ) ) . '">' . esc_html__( 'Documentation', 'oxygen-quicknav' ) . '</a><p>' . esc_html__( 'Your shortcuts. Both Oxygen generations.', 'oxygen-quicknav' ) . '</p></div><div><span>© 2025–2026 <a href="https://github.com/deckerweb" target="_blank" rel="noopener noreferrer">David Decker – DECKERWEB</a></span><a href="https://github.com/deckerweb/oxygen-quicknav" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Plugin website', 'oxygen-quicknav' ) . '</a></div></footer>';
		echo '<dialog id="oqn-history" hidden aria-labelledby="oqn-history-title"><form method="dialog"><button type="submit" class="button oqn-close" data-oqn-close>' . esc_html__( 'Close', 'oxygen-quicknav' ) . '</button></form><h2 id="oqn-history-title">' . esc_html__( 'Changelog', 'oxygen-quicknav' ) . '</h2>';
		$history = require DDW_OQN_DIR . '/includes/plugin-history.php';
		echo wp_kses_post( $history( 0 === strpos( determine_locale(), 'de' ) ) );
		echo '</dialog></div>';
	}

	/**
	 * Render an accessible native setting control with visible constant overrides.
	 * @param string $key Setting key.
	 * @param string $label Translated field label.
	 * @param string|array $kind Control type or choices.
	 * @param array $settings Effective website settings.
	 * @return void Outputs an escaped form row.
	 */
	private function field( $key, $label, $kind, $settings ) {
		$constant = Settings::constant_map()[ $key ] ?? '';
		$locked = $constant && defined( $constant );
		$id = 'oqn-' . $key;
		$name = Settings::OPTION . '[' . $key . ']';
		$value = 'users' === $key ? implode( ', ', $settings[ $key ] ) : $settings[ $key ];
		echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label></th><td>';
		if ( is_array( $kind ) ) {
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . disabled( $locked, true, false ) . '>';
			foreach ( $kind as $option => $text ) { echo '<option value="' . esc_attr( $option ) . '"' . selected( $value, $option, false ) . '>' . esc_html( $text ) . '</option>'; }
			echo '</select>';
		} elseif ( 'checkbox' === $kind ) {
			echo '<input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="1"' . checked( $value, true, false ) . disabled( $locked, true, false ) . '>';
		} else { echo '<input class="regular-text" type="' . esc_attr( $kind ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '"' . ( 'number' === $kind ? ' min="1" max="100"' : '' ) . disabled( $locked, true, false ) . '>'; }
		if ( $locked ) { echo '<p class="description">' . esc_html__( 'Set by configuration:', 'oxygen-quicknav' ) . ' <code>' . esc_html( $constant ) . '</code></p>'; }
		echo '</td></tr>';
	}

	/**
	 * Render a selection in a multi-choice setting.
	 * @param string $key Setting key.
	 * @param string $value Choice value.
	 * @param string $label Choice label.
	 * @param array $selected Selected choice values.
	 * @return void Outputs one escaped checkbox label.
	 */
	private function choice( $key, $value, $label, $selected ) {
		echo '<label class="oqn-choice"><input type="checkbox" name="' . esc_attr( Settings::OPTION . '[' . $key . '][]' ) . '" value="' . esc_attr( $value ) . '"' . checked( in_array( $value, $selected, true ), true, false ) . '><span>' . esc_html( $label ) . '</span></label>';
	}

	/**
	 * Render personal, website-scoped toolbar preferences on a user profile.
	 * @param \WP_User $user Profile user.
	 * @return void Outputs controls; personal settings cannot expand site permissions.
	 */
	public function profile( $user ) {
		if ( is_network_admin() || is_user_admin() || ! current_user_can( 'edit_user', $user->ID ) ) { return; }
		$prefs = get_user_option( Settings::USER_OPTION, $user->ID );
		$prefs = is_array( $prefs ) ? $prefs : array();
		echo '<h2>Oxygen QuickNav</h2><p>' . esc_html__( 'Personal preferences for this website. Website permissions and configuration overrides still apply.', 'oxygen-quicknav' ) . '</p>';
		wp_nonce_field( 'oqn-profile-' . $user->ID, 'oqn_profile_nonce' );
		echo '<table class="form-table" role="presentation">';
		foreach ( array( 'backend' => __( 'Admin Dashboard toolbar', 'oxygen-quicknav' ), 'frontend' => __( 'Website toolbar', 'oxygen-quicknav' ) ) as $key => $label ) {
			echo '<tr><th><label for="oqn-personal-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td><select id="oqn-personal-' . esc_attr( $key ) . '" name="oqn_preferences[' . esc_attr( $key ) . ']"><option value="inherit">' . esc_html__( 'Use website default', 'oxygen-quicknav' ) . '</option><option value="hide"' . selected( $prefs[ $key ] ?? '', 'hide', false ) . '>' . esc_html__( 'Hide for me', 'oxygen-quicknav' ) . '</option></select></td></tr>';
		}
		echo '<tr><th><label for="oqn-personal-limit">' . esc_html__( 'Items per group', 'oxygen-quicknav' ) . '</label></th><td><input type="number" min="0" max="100" id="oqn-personal-limit" name="oqn_preferences[limit]" value="' . esc_attr( $prefs['limit'] ?? 0 ) . '"><p class="description">' . esc_html__( '0 uses the website default.', 'oxygen-quicknav' ) . '</p></td></tr></table>';
	}

	/**
	 * Save authorized, nonce-verified personal preferences for this website only.
	 * @param int $id Profile user ID.
	 * @return void Saves validated values or leaves existing preferences untouched.
	 */
	public function save_profile( $id ) {
		if ( is_network_admin() || is_user_admin() || ! current_user_can( 'edit_user', $id ) || ! isset( $_POST['oqn_profile_nonce'] ) || ! is_string( $_POST['oqn_profile_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['oqn_profile_nonce'] ) ), 'oqn-profile-' . $id ) ) { return; }
		$input = isset( $_POST['oqn_preferences'] ) && is_array( $_POST['oqn_preferences'] ) ? map_deep( wp_unslash( $_POST['oqn_preferences'] ), 'sanitize_text_field' ) : array();
		$prefs = array( 'limit' => is_scalar( $input['limit'] ?? null ) ? min( 100, absint( $input['limit'] ) ) : 0 );
		foreach ( array( 'backend', 'frontend' ) as $key ) { $prefs[ $key ] = 'hide' === ( $input[ $key ] ?? '' ) ? 'hide' : 'inherit'; }
		update_user_option( $id, Settings::USER_OPTION, $prefs, false );
	}
}
