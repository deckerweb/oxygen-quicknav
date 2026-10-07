<?php
/** Generation-specific, read-only Oxygen integration. @package OxygenQuickNav */
namespace Deckerweb\OxygenQuickNav;

defined( 'ABSPATH' ) || exit;

/** Resolves active builder families without changing vendor files or content. */
final class Builder {
	/** @return array Active supported generations, with independent menu namespaces. */
	public static function active() {
		$builders = array();
		if ( defined( 'BREAKDANCE_MODE' ) && 'oxygen' === BREAKDANCE_MODE && defined( '__BREAKDANCE_VERSION' ) && function_exists( '\Breakdance\Admin\get_builder_loader_url' ) ) { $builders[] = 'modern'; }
		if ( defined( 'CT_VERSION' ) && version_compare( CT_VERSION, '4.0', '>=' ) && function_exists( 'ct_get_post_builder_link' ) ) { $builders[] = 'classic'; }
		return $builders;
	}

	/**
	 * Get a generation's configured template categories.
	 * @param string $generation Supported generation identifier.
	 * @return array Group keys mapped to label, post type, and archive URL.
	 */
	public static function groups( $generation ) {
		if ( 'classic' === $generation ) {
			$url = admin_url( 'edit.php?post_type=ct_template' );
			return array(
				'templates' => array( __( 'Templates', 'oxygen-quicknav' ), 'ct_template', $url ),
				'components' => array( __( 'Reusable Parts', 'oxygen-quicknav' ), 'ct_template', add_query_arg( 'is_reusable', 'true', $url ) ),
			);
		}
		return array(
			'templates' => array( __( 'Templates', 'oxygen-quicknav' ), 'oxygen_template', admin_url( 'admin.php?page=oxygen_template' ) ),
			'headers' => array( __( 'Headers', 'oxygen-quicknav' ), 'oxygen_header', admin_url( 'admin.php?page=oxygen_header' ) ),
			'footers' => array( __( 'Footers', 'oxygen-quicknav' ), 'oxygen_footer', admin_url( 'admin.php?page=oxygen_footer' ) ),
			'components' => array( __( 'Components', 'oxygen-quicknav' ), 'oxygen_block', admin_url( 'admin.php?page=oxygen_block' ) ),
		);
	}

	/**
	 * Apply WordPress and builder permissions to one edit target.
	 * @param int $id Post ID.
	 * @param string $generation Supported generation identifier.
	 * @return bool Whether the current user may open this target in the builder.
	 */
	public static function can_edit( $id, $generation ) {
		if ( ! current_user_can( 'edit_post', $id ) ) { return false; }
		if ( 'classic' === $generation ) {
			return function_exists( 'oxygen_vsb_current_user_can_access' ) && oxygen_vsb_current_user_can_access();
		}
		return function_exists( '\Breakdance\Permissions\hasMinimumPermission' ) && \Breakdance\Permissions\hasMinimumPermission( 'edit' )
			&& ( ! function_exists( '\Breakdance\Permissions\isPostTypeAllowed' ) || \Breakdance\Permissions\isPostTypeAllowed( get_post_type( $id ) ) );
	}

	/**
	 * Use the builder's own URL helper, including Classic inner-content flags.
	 * @param int $id Post ID.
	 * @param string $generation Supported generation identifier.
	 * @return string Builder URL, or an empty string when permission is missing.
	 */
	public static function edit_url( $id, $generation ) {
		if ( ! self::can_edit( $id, $generation ) ) { return ''; }
		if ( 'modern' === $generation ) { return \Breakdance\Admin\get_builder_loader_url( $id ); }
		return function_exists( 'oxy_get_builder_url' ) ? oxy_get_builder_url( $id ) : ct_get_post_builder_link( $id );
	}

	/**
	 * Identify actual builder content or template classification from stored data.
	 * @param \WP_Post $post Candidate post.
	 * @param string $generation Supported generation identifier.
	 * @param string $group Content or template group key.
	 * @return bool Whether the post belongs to the requested group.
	 */
	private static function matches( $post, $generation, $group ) {
		if ( 'classic' === $generation && 'ct_template' === $post->post_type ) {
			$reusable = 'reusable_part' === get_post_meta( $post->ID, '_ct_template_type', true );
			return 'components' === $group ? $reusable : ! $reusable;
		}
		if ( 'content' === $group ) {
			$keys = 'classic' === $generation ? array( '_ct_builder_json', '_ct_builder_shortcodes' ) : array( '_oxygen_data' );
			foreach ( $keys as $key ) {
				$value = get_post_meta( $post->ID, $key, true );
				if ( is_string( $value ) && '' !== trim( $value ) && ! in_array( trim( $value ), array( '{}', '[]', 'null' ), true ) ) { return true; }
				if ( is_array( $value ) && ! empty( $value ) ) { return true; }
			}
			return false;
		}
		if ( 'modern' === $generation && 'templates' === $group ) {
			$settings = get_post_meta( $post->ID, '_oxygen_template_settings', true );
			if ( is_string( $settings ) ) { $settings = json_decode( $settings, true ); }
			return ! ( is_array( $settings ) && ! empty( $settings['fallback'] ) );
		}
		return true;
	}

	/**
	 * Retrieve a bounded list, filling gaps caused by fallback or inaccessible items.
	 * @param string $type Registered post type.
	 * @param string $generation Supported generation identifier.
	 * @param string $group Content or template group key.
	 * @param array $settings Effective settings.
	 * @return array Authorized WP_Post objects, limited to the configured count.
	 */
	public static function posts( $type, $generation, $group, $settings ) {
		if ( ! post_type_exists( $type ) ) { return array(); }
		$args = array( 'post_type' => $type, 'posts_per_page' => min( 100, max( 20, $settings['limit'] * 2 ) ), 'post_status' => $settings['drafts'] ? array( 'publish', 'draft', 'pending', 'future', 'private' ) : 'publish', 'orderby' => array( $settings['orderby'] => 'title' === $settings['orderby'] ? 'ASC' : 'DESC', 'ID' => 'DESC' ), 'no_found_rows' => true );
		if ( 'content' === $group ) {
			$keys = 'classic' === $generation ? array( '_ct_builder_json', '_ct_builder_shortcodes' ) : array( '_oxygen_data' );
			$args['meta_query'] = array( 'relation' => 'OR' );
			foreach ( $keys as $key ) { $args['meta_query'][] = array( 'key' => $key, 'compare' => 'EXISTS' ); }
		}
		if ( 'classic' === $generation && 'components' === $group ) { $args['meta_query'] = array( array( 'key' => '_ct_template_type', 'value' => 'reusable_part' ) ); }
		/**
		 * Filters candidate query arguments; return the modified array.
		 * @since 1.0.0
		 * @param array $args WordPress query arguments.
		 * @param string $type Requested post type.
		 */
		$args = apply_filters( 'ddw/quicknav/oxy_get_template_type', $args, $type );
		if ( ! is_array( $args ) ) { return array(); }
		$args['post_type'] = $type;
		$args['posts_per_page'] = min( 100, max( 1, absint( $args['posts_per_page'] ?? 40 ) ) );
		$args['no_found_rows'] = true;
		$found = array();
		// A finite scan prevents an unbounded toolbar query on large installations.
		for ( $page = 1; $page <= 5 && count( $found ) < $settings['limit']; $page++ ) {
			$args['paged'] = $page;
			$posts = get_posts( $args );
			foreach ( $posts as $post ) {
				if ( $post instanceof \WP_Post && self::can_edit( $post->ID, $generation ) && self::matches( $post, $generation, $group ) ) { $found[ $post->ID ] = $post; }
				if ( count( $found ) >= $settings['limit'] ) { break; }
			}
			if ( count( $posts ) < $args['posts_per_page'] ) { break; }
		}
		return array_values( $found );
	}

	/**
	 * Return registered modern tabs or the known Classic settings tabs.
	 * @param string $generation Supported generation identifier.
	 * @return array Tab slugs mapped to human-readable labels.
	 */
	public static function tabs( $generation ) {
		if ( 'classic' === $generation ) {
			$tabs = array( 'general' => __( 'General', 'oxygen-quicknav' ), 'client_control' => __( 'Client Control', 'oxygen-quicknav' ), 'security_manager' => __( 'Security', 'oxygen-quicknav' ), 'svg_manager' => __( 'SVG Sets', 'oxygen-quicknav' ), 'typekit_manager' => 'Typekit', 'license_manager' => __( 'License', 'oxygen-quicknav' ), 'cache' => __( 'CSS Cache', 'oxygen-quicknav' ), 'bloat' => __( 'Bloat Eliminator', 'oxygen-quicknav' ), 'library_manager' => __( 'Library', 'oxygen-quicknav' ), 'revisions' => __( 'Revisions', 'oxygen-quicknav' ), 'tools' => __( 'Tools', 'oxygen-quicknav' ) );
			if ( defined( 'CT_FREE' ) ) { unset( $tabs['license_manager'] ); }
			if ( class_exists( 'Oxygen_Gutenberg' ) ) { $tabs['gutenberg'] = 'Gutenberg'; }
		} else {
			$tabs = array();
			$class = '\\Breakdance\\Admin\\SettingsPage\\SettingsPageController';
			if ( class_exists( $class ) && is_callable( array( $class, 'getInstance' ) ) ) {
				$registered = $class::getInstance()->tabs;
				if ( is_array( $registered ) ) {
					/** @param array $a First tab. @param array $b Second tab. @return int Tab order comparison. */
					usort( $registered, static function ( $a, $b ) { return ( $a['order'] ?? 0 ) <=> ( $b['order'] ?? 0 ); } );
					foreach ( $registered as $tab ) { if ( is_string( $tab['slug'] ?? null ) && is_string( $tab['name'] ?? null ) ) { $tabs[ $tab['slug'] ] = wp_strip_all_tags( $tab['name'] ); } }
				}
			}
			if ( ! $tabs ) { $tabs = array( 'license' => __( 'License', 'oxygen-quicknav' ), 'bloat_eliminator' => __( 'Performance', 'oxygen-quicknav' ), 'api_keys' => __( 'API Keys', 'oxygen-quicknav' ), 'post_types' => __( 'Post Types', 'oxygen-quicknav' ), 'advanced' => __( 'Advanced', 'oxygen-quicknav' ), 'design_library' => __( 'Design Library', 'oxygen-quicknav' ), 'header_footer' => __( 'Custom Code', 'oxygen-quicknav' ), 'tools' => __( 'Tools', 'oxygen-quicknav' ), 'extensions' => __( 'Extensions', 'oxygen-quicknav' ) ); }
		}
		/**
		 * Filters settings labels; return slug => label pairs for existing tabs.
		 * @since 1.0.0
		 * @param array $tabs Available settings tabs.
		 */
		$tabs = apply_filters( 'ddw/quicknav/oxy_settings', $tabs );
		return is_array( $tabs ) ? array_filter( $tabs, 'is_string' ) : array();
	}

	/**
	 * Resolve the active generation's settings page and optional tab.
	 * @param string $generation Supported generation identifier.
	 * @param string $tab Optional tab slug.
	 * @return string Settings page URL.
	 */
	public static function settings_url( $generation, $tab = '' ) {
		if ( 'modern' === $generation && function_exists( '\Breakdance\Admin\SettingsPage\settings_page_url' ) ) { return \Breakdance\Admin\SettingsPage\settings_page_url( sanitize_key( $tab ) ); }
		$args = array( 'page' => 'classic' === $generation ? 'oxygen_vsb_settings' : 'oxygen_settings' );
		if ( '' !== $tab ) { $args['tab'] = sanitize_key( $tab ); }
		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/** @return array Detected official addon names mapped to safe version strings. */
	public static function addons() {
		$out = array();
		foreach ( array( 'BREAKDANCE_ELEMENTS_FOR_OXYGEN_VERSION' => 'Breakdance Elements for Oxygen', 'BREAKDANCE_FORMS_FOR_OXYGEN_VERSION' => 'Breakdance Forms for Oxygen', 'BREAKDANCE_WOOCOMMERCE_FOR_OXYGEN_VERSION' => 'Breakdance WooCommerce for Oxygen', 'CT_OXYGEN_GUTENBERG_VERSION' => 'Oxygen Gutenberg', 'CT_OXYGEN_WOOCOMMERCE_VERSION' => 'Oxygen WooCommerce' ) as $constant => $name ) {
			if ( defined( $constant ) && is_scalar( constant( $constant ) ) ) { $out[ $name ] = sanitize_text_field( (string) constant( $constant ) ); }
		}
		return $out;
	}
}
