<?php
/*
Forked from "Breakdance Navigator" by Peter Kulcsár
License: GPL v2 or later
GitHub Repository: https://github.com/beamkiller/breakdance-navigator
Original Copyright: © 2024, Peter Kulcsár
*/
 
/*
Plugin Name:        Oxygen QuickNav
Plugin URI:         https://github.com/deckerweb/oxygen-quicknav
Description:        Adds a quick-access navigator to the WordPress Admin Bar (Toolbar). It allows easy access to Oxygen Templates, Headers, Footers, Components, and Pages edited with Oxygen, along with some other essential settings. For Oxygen 6+ only!
Project:            Code Snippet: DDW Oxygen QuickNav
Version:            1.1.0
Author:             David Decker – DECKERWEB
Author URI:         https://deckerweb.de/
Text Domain:        oxygen-quicknav
Domain Path:        /languages/
License:            GPL-2.0-or-later
License URI:        https://www.gnu.org/licenses/gpl-2.0.html
Requires WP:        6.7
Requires PHP:       7.4
GitHub Plugin URI:  https://github.com/deckerweb/oxygen-quicknav
Primary Branch:     main

Original Copyright: © 2024, Peter Kulcsár
Copyright:          © 2025, David Decker – DECKERWEB

TESTED WITH:
Product			Versions
--------------------------------------------------------------------------------------------------------------
PHP 			8.0, 8.3
WordPress		6.7.2 ... 6.8 Beta
Oxygen 6+	    6.0.0 Beta
--------------------------------------------------------------------------------------------------------------

VERSION HISTORY:
Date        Version     Description
--------------------------------------------------------------------------------------------------------------
2025-04-??	1.1.0       New: Show Admin Bar also in Block Editor full screen mode
                        New: Add info to Site Health Debug, useful for our constants for custom tweaking
                        New: Installable and updateable via Git Updater plugin
2025-03-09	1.0.0	    Initial release
2025-03-07	0.5.0       Internal test version
2025-03-07	0.0.0	    Development start
--------------------------------------------------------------------------------------------------------------
*/

/** Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly.

if ( ! class_exists( 'DDW_Oxygen_QuickNav' ) ) :

class DDW_Oxygen_QuickNav {
    
    /** Class constants & variables */
    private const VERSION = '1.1.0';
    private const NUMBER_OF_TEMPLATES = 20;
    
    /**
     * Constructor
     */
    public function __construct() {           
        add_action( 'admin_bar_menu',              array( $this, 'add_admin_bar_menu' ), 999 );
        add_action( 'admin_enqueue_scripts',       array( $this, 'enqueue_admin_bar_styles' ) );  // for Admin
        add_action( 'wp_enqueue_scripts',          array( $this, 'enqueue_admin_bar_styles' ) );  // for front-end
        add_action( 'enqueue_block_editor_assets', array( $this, 'adminbar_block_editor_fullscreen' ) );  // for Block Editor
        add_filter( 'debug_information',           array( $this, 'site_health_debug_info' ), 9 );
    }

    /**
     * Get specific Admin Color scheme colors we need. Covers all 9 default
     *	 color schemes coming with a default WordPress install.
     *   (helper function)
     */
    private function get_scheme_colors() {
        
        $scheme_colors = array(
            'fresh' => array(
                'bg'    => '#1d2327',
                'base'  => 'rgba(240,246,252,.6)',
                'hover' => '#72aee6',
            ),
            'light' => array(
                'bg'    => '#e5e5e5',
                'base'  => '#999',
                'hover' => '#04a4cc',
            ),
            'modern' => array(
                'bg'    => '#1e1e1e',
                'base'  => '#f3f1f1',
                'hover' => '#33f078',
            ),
            'blue' => array(
                'bg'    => '#52accc',
                'base'  => '#e5f8ff',
                'hover' => '#fff',
            ),
            'coffee' => array(
                'bg'    => '#59524c',
                'base'  => 'hsl(27.6923076923,7%,95%)',
                'hover' => '#c7a589',
            ),
            'ectoplasm' => array(
                'bg'    => '#523f6d',
                'base'  => '#ece6f6',
                'hover' => '#a3b745',
            ),
            'midnight' => array(
                'bg'    => '#363b3f',
                'base'  => 'hsl(206.6666666667,7%,95%)',
                'hover' => '#e14d43',
            ),
            'ocean' => array(
                'bg'    => '#738e96',
                'base'  => '#f2fcff',
                'hover' => '#9ebaa0',
            ),
            'sunrise' => array(
                'bg'    => '#cf4944',
                'base'  => 'hsl(2.1582733813,7%,95%)',
                'hover' => 'rgb(247.3869565217,227.0108695652,211.1130434783)',
            ),
        );
        
        /** No filter currently b/c of sanitizing issues with the above CSS values */
        //$scheme_colors = (array) apply_filters( 'ddw/quicknav/on_scheme_colors', $scheme_colors );
        
        return $scheme_colors;
    }
    
    /**
     * Enqueue custom styles for the admin bar.
     */
    public function enqueue_admin_bar_styles() {
        
        /**
         * Depending on user color scheme get proper base and hover color values for the main item (svg) icon.
         */
        $user_color_scheme = get_user_option( 'admin_color' );
        $user_color_scheme = ( is_admin() || is_network_admin() ) ? $user_color_scheme : 'fresh';
        $admin_scheme      = $this->get_scheme_colors();
        
        $base_color  = $admin_scheme[ $user_color_scheme ][ 'base' ];
        $hover_color = $admin_scheme[ $user_color_scheme ][ 'hover' ];
        
        $inline_css = sprintf(
            '
            /* Style for the separator */
            #wp-admin-bar-ddw-oxygen-quicknav > .ab-sub-wrapper #wp-admin-bar-oqn-settings {
                border-bottom: 1px dashed rgba(255, 255, 255, 0.33);
                padding-bottom: 5px;
            }
            
            /* for icons */
            #wpadminbar .has-icon .icon-svg svg {
                display: inline-block;
                margin-bottom: 3px;
                vertical-align: middle;
                width: 16px;
                height: 16px;
            }
            '
        );
        
        if ( is_admin_bar_showing() ) {
            wp_add_inline_style( 'admin-bar', $inline_css );
        }
    }

    /**
     * Number of templates/pages to query for. Can be tweaked via constant.
     *   (Helper function)
     *
     * @return int Number of templates.
     */
    private function number_of_templates() {
            
        $number_of_templates = defined( 'OQN_NUMBER_TEMPLATES' ) ? (int) OQN_NUMBER_TEMPLATES : self::NUMBER_OF_TEMPLATES;
        
        return $number_of_templates;
    }
    
    /**
     * Get items of a Breakdance template type. (Helper function)
     *
     * @uses get_posts()
     *
     * @param string $post_type Slug of post type to query for.
     */
    private function get_oxygen_template_type( $post_type ) {
        
        /** only Oxygen-edited pages have the key: '_breakdance_data' */
        $pages_meta_query = ( 'page' === $post_type ) ? [ 'key' => '_oxygen_data', 'compare' => 'EXISTS' ] : [];
            
        $args = array(
            'post_type'      => sanitize_key( $post_type ),
            'posts_per_page' => absint( $this->number_of_templates() ),
            'post_status'    => 'publish',
            'orderby'        => 'modified',
            'order'          => 'DESC',
            'meta_query'     => [ $pages_meta_query ],  // optional
        );
        
        apply_filters( 'ddw/quicknav/oxy_get_template_type', $args, $post_type );
        
        return get_posts( $args );
    }
    
    /**
     * Adds the main Oxygen menu and its submenus to the Admin Bar.
     *
     * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance.
     */
    public function add_admin_bar_menu( $wp_admin_bar ) {
        
        $enabled_users = defined( 'OQN_ENABLED_USERS' ) ? (array) OQN_ENABLED_USERS : [];
        
        /** Optional: let only defined user IDs access the plugin */
        if ( defined( 'OQN_ENABLED_USERS' ) && ! in_array( get_current_user_id(), $enabled_users ) ) {
            return;
        }
        
        /** Don't do anything if Oxygen Builder v6+ plugin is NOT active */
        if ( ! defined( 'BREAKDANCE_MODE' ) && 'oxygen' !== BREAKDANCE_MODE ) return;
        
        $oqn_permission = ( defined( 'OQN_VIEW_CAPABILITY' ) ) ? OQN_VIEW_CAPABILITY : 'activate_plugins';
        
        if ( ! current_user_can( sanitize_key( $oqn_permission ) ) ) {
            return;
        }
        
        $oqn_name = ( defined( 'OQN_NAME_IN_ADMINBAR' ) ) ? esc_html( OQN_NAME_IN_ADMINBAR ) : esc_html__( 'Oxy', 'oxygen-quicknav' );
        
        /**
         * Add the parent menu item with an icon (main node)
         */
        $oxy_builder_icon  = 'oxygen/builder/dist/favicon-oxygen-dark.svg';
        $oxy_packaged_icon = plugin_dir_url( __FILE__ ) . 'images/oxygen-icon.png';

        $icon_path  = trailingslashit( WP_PLUGIN_DIR ) . $oxy_builder_icon;
        $icon_url   = file_exists( $icon_path ) ? plugins_url( $oxy_builder_icon, dirname( __FILE__ ) ) : $oxy_packaged_icon;
        $icon_url   = ( defined( 'OQN_ICON' ) && 'blue' === OQN_ICON ) ? $oxy_packaged_icon : $icon_url;
        $title_html = '<img src="' . esc_url( $icon_url ) . '" style="display:inline-block;padding-right:6px;padding-bottom:3px;vertical-align:middle;width:15px;height:15px;" alt="" />' . $oqn_name;
        $title_html = wp_kses( $title_html, array(
            'img' => array(
                'src'   => array(),
                'style' => array(),
                'alt'   => array(),
            ),
        ) );

        /** Main menu item */
        $wp_admin_bar->add_node( array(
            'id'    => 'ddw-oxygen-quicknav',
            'title' => $title_html,
            'href'  => '#',
        ) );

        /** Add submenus (all group nodes!) */
        $this->add_templates_group( $wp_admin_bar );
        $this->add_settings_group( $wp_admin_bar );
        $this->add_plugin_support_group( $wp_admin_bar );
        $this->add_footer_group( $wp_admin_bar );
    }

    /**
     * Add group node for Oxygen-edited Pages and all Oxygen Template types.
     */
    private function add_templates_group( $wp_admin_bar ) {
        $wp_admin_bar->add_group( array(
            'id'     => 'oqn-group-templates',
            'parent' => 'ddw-oxygen-quicknav',
        ) );
        
        $this->add_pages_submenu( $wp_admin_bar );
        $this->add_templates_submenu( $wp_admin_bar );
        $this->add_headers_submenu( $wp_admin_bar );
        $this->add_footers_submenu( $wp_admin_bar );
        $this->add_components_submenu( $wp_admin_bar );
    }
    
    /**
     * Add Oxygen-edited Pages submenu (just regular WordPress Pages).
     */
    private function add_pages_submenu( $wp_admin_bar ) {
        $wp_admin_bar->add_node( array(
            'id'     => 'oqn-pages',
            'title'  => esc_html__( 'Pages', 'oxygen-quicknav' ),
            'href'   => esc_url( admin_url( 'edit.php?post_type=page' ) ),
            'parent' => 'oqn-group-templates',
        ) );

        $oxy_pages = $this->get_oxygen_template_type( 'page' );
        
        if ( $oxy_pages ) {
            foreach ( $oxy_pages as $oxy_page ) {
                $edit_link = site_url( '/?oxygen=builder&id=' . intval( $oxy_page->ID ) );
        
                $wp_admin_bar->add_node( array(
                    'id'     => 'oqn-page-' . intval( $oxy_page->ID ),
                    'title'  => esc_html( $oxy_page->post_title ),
                    'href'   => esc_url( $edit_link ),
                    'parent' => 'oqn-pages',
                ) );
            }  // end foreach
        }  // end if
    }
    
    /**
     * Add Oxygen Templates submenu.
     */
    private function add_templates_submenu( $wp_admin_bar ) {
        $wp_admin_bar->add_node( array(
            'id'     => 'oqn-templates',
            'title'  => esc_html__( 'Templates', 'oxygen-quicknav' ),
            'href'   => esc_url( admin_url( 'admin.php?page=oxygen_template' ) ),
            'parent' => 'oqn-group-templates',
        ) );

        $templates = $this->get_oxygen_template_type( 'oxygen_template' );
        
        if ( $templates ) {
            foreach ( $templates as $template ) {
                /** Skip the internal Oxy Fallback templates */
                if ( strpos( $template->post_title, 'Fallback: ' ) === 0 ) {
                    continue;
                }
        
                $edit_link = site_url( '/?oxygen=builder&id=' . intval( $template->ID ) );
        
                $wp_admin_bar->add_node( array(
                    'id'     => 'oqn-template-' . intval( $template->ID ),
                    'title'  => esc_html( $template->post_title ),
                    'href'   => esc_url( $edit_link ),
                    'parent' => 'oqn-templates',
                ) );
            }  // end foreach
        }  // end if
    }

    /**
     * Add Oxygen Headers submen.
     */
    private function add_headers_submenu( $wp_admin_bar ) {
        $wp_admin_bar->add_node( array(
            'id'     => 'oqn-headers',
            'title'  => esc_html__( 'Headers', 'oxygen-quicknav' ),
            'href'   => esc_url( admin_url( 'admin.php?page=oxygen_header' ) ),
            'parent' => 'oqn-group-templates',
        ) );

        $headers = $this->get_oxygen_template_type( 'oxygen_header' );
        
        if ( $headers ) {
            foreach ( $headers as $header ) {
                $edit_link = site_url( '/?oxygen=builder&id=' . intval( $header->ID ) );
        
                $wp_admin_bar->add_node( array(
                    'id'     => 'oqn-header-' . intval( $header->ID ),
                    'title'  => esc_html( $header->post_title ),
                    'href'   => esc_url( $edit_link ),
                    'parent' => 'oqn-headers',
                ) );
            }  // end foreach
        }  // end if
    }

    /**
     * Add Oxygen Footers submenu.
     */
    private function add_footers_submenu( $wp_admin_bar ) {
        $wp_admin_bar->add_node( array(
            'id'     => 'oqn-footers',
            'title'  => esc_html__( 'Footers', 'oxygen-quicknav' ),
            'href'   => esc_url( admin_url( 'admin.php?page=oxygen_footer' ) ),
            'parent' => 'oqn-group-templates',
        ) );

        $footers = $this->get_oxygen_template_type( 'oxygen_footer' );
        
        if ( $footers ) {
            foreach ( $footers as $footer ) {
                $edit_link = site_url( '/?oxygen=builder&id=' . intval( $footer->ID ) );
        
                $wp_admin_bar->add_node( array(
                    'id'     => 'bdn-footer-' . intval( $footer->ID ),
                    'title'  => esc_html( $footer->post_title ),
                    'href'   => esc_url( $edit_link ),
                    'parent' => 'oqn-footers',
                ) );
            }  // end foreach
        }  // end if
    }

    /**
     * Add Oxygen Components submenu.
     */
    private function add_components_submenu( $wp_admin_bar ) {
        $wp_admin_bar->add_node( array(
            'id'     => 'oqn-components',
            'title'  => esc_html__( 'Components', 'oxygen-quicknav' ),
            'href'   => esc_url( admin_url( 'admin.php?page=oxygen_block' ) ),
            'parent' => 'oqn-group-templates',
        ) );

        $blocks = $this->get_oxygen_template_type( 'oxygen_block' );
        
        if ( $blocks ) {
            foreach ( $blocks as $block ) {
                $edit_link = site_url( '/?oxygen=builder&id=' . intval( $block->ID ) );
        
                $wp_admin_bar->add_node( array(
                    'id'     => 'oqn-component-' . intval( $block->ID ),
                    'title'  => esc_html( $block->post_title ),
                    'href'   => esc_url( $edit_link ),
                    'parent' => 'oqn-components',
                ) );
            }  // end foreach
        }  // end if
    }

    /**
     * Add group node for actions & settings.
     */
    private function add_settings_group( $wp_admin_bar ) {
        $wp_admin_bar->add_group( array(
            'id'     => 'oqn-group-settings',
            'parent' => 'ddw-oxygen-quicknav',
        ) );
        
        //$this->add_actions_submenu( $wp_admin_bar );
        $this->add_settings_submenu( $wp_admin_bar );
    }
    
    /**
     * Add Oxygen Settings submenu (parent node)
     */
    private function add_settings_submenu( $wp_admin_bar ) {
        
        $icon_settings = '<span class="icon-svg"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M5.32943 3.27158C6.56252 2.8332 7.9923 3.10749 8.97927 4.09446C10.1002 5.21537 10.3019 6.90741 9.5843 8.23385L20.293 18.9437L18.8788 20.3579L8.16982 9.64875C6.84325 10.3669 5.15069 10.1654 4.02952 9.04421C3.04227 8.05696 2.7681 6.62665 3.20701 5.39332L5.44373 7.63C6.02952 8.21578 6.97927 8.21578 7.56505 7.63C8.15084 7.04421 8.15084 6.09446 7.56505 5.50868L5.32943 3.27158ZM15.6968 5.15512L18.8788 3.38736L20.293 4.80157L18.5252 7.98355L16.7574 8.3371L14.6361 10.4584L13.2219 9.04421L15.3432 6.92289L15.6968 5.15512ZM8.97927 13.2868L10.3935 14.7011L5.09018 20.0044C4.69966 20.3949 4.06649 20.3949 3.67597 20.0044C3.31334 19.6417 3.28744 19.0699 3.59826 18.6774L3.67597 18.5902L8.97927 13.2868Z"></path></svg></span> ';
        
        $wp_admin_bar->add_node( array(
            'id'     => 'oqn-settings',
            'title'  => $icon_settings . esc_html__( 'Settings', 'oxygen-quicknav' ),
            'href'   => esc_url( admin_url( 'admin.php?page=oxygen_settings' ) ),
            'parent' => 'oqn-group-settings',
            'meta'   => array( 'class' => 'has-icon oxy-settings-separator' ),
        ) );

        $settings_submenus = array(
            'performance'    => __( 'Performance', 'oxygen-quicknav' ),
            'api_keys'       => __( 'API Keys', 'oxygen-quicknav' ),
            'post_types'     => __( 'Post Types', 'oxygen-quicknav' ),
            'advanced'       => __( 'Advanced', 'oxygen-quicknav' ),
            'design_library' => __( 'Design Library', 'oxygen-quicknav' ),
            'custom_code'    => __( 'Custom Code', 'oxygen-quicknav' ),
            'tools'          => __( 'Tools', 'oxygen-quicknav' ),
            'extensions'     => __( 'Extensions', 'oxygen-quicknav' ),
        );

        /** Offical extension */
        if ( function_exists( 'Breakdance\MigrationMode\saveActivatingUserIp' ) ) {
            $settings_submenus[ 'migration-mode' ] = __( 'Migration Mode', 'oxygen-quicknav' );
        }
        
        /** License always at the bottom, before filter */
        $settings_submenus[ 'license' ] = __( 'License', 'oxygen-quicknav' );
        
        /** Make settings array filterable */
        apply_filters( 'ddw/quicknav/oxy_settings', $settings_submenus );
        
        foreach ( $settings_submenus as $tab => $title ) {
            $wp_admin_bar->add_node( array(
                'id'     => 'oqn-settings-' . sanitize_key( $tab ),
                'title'  => esc_html( $title ),
                'href'   => esc_url( admin_url( 'admin.php?page=oxygen_settings&tab=' . urlencode( $tab ) ) ),
                'parent' => 'oqn-settings',
            ) );
        }  // end foreach
    }

    /**
     * Add group node for plugin support
     */
    private function add_plugin_support_group( $wp_admin_bar ) {
        $wp_admin_bar->add_group( array(
            'id'     => 'oqn-group-plugins',
            'parent' => 'ddw-oxygen-quicknav',
        ) );
        
        $this->maybe_add_plugin_submenus( $wp_admin_bar );
    }

    /**
     * Add submenus for supported plugins - if they are active.
     */
    private function maybe_add_plugin_submenus( $wp_admin_bar ) {
        
        if ( class_exists( '\Yabe\Webfont\Plugin' ) ) {
            $wp_admin_bar->add_node( array(
                'id'     => 'oqn-yabe-webfont',
                'title'  => esc_html__( 'Yabe Webfont', 'oxygen-quicknav' ),
                'href'   => esc_url( admin_url( 'themes.php?page=yabe_webfont' ) ),
                'parent' => 'oqn-group-plugins',
            ) );
        }
        
        if ( defined( 'WPSIX_EXPORTER_URL' ) ) {
            $wp_admin_bar->add_node( array(
                'id'     => 'oqn-wpsix-exporter',
                'title'  => esc_html__( 'WPSix Exporter', 'oxygen-quicknav' ),
                'href'   => esc_url( admin_url( 'admin.php?page=wpsix_exporter' ) ),
                'parent' => 'oqn-group-plugins',
            ) );
        }   
    }
    
    /**
     * Add group node for footer items (Links & About)
     */
    private function add_footer_group( $wp_admin_bar ) {
        /** Allows for custom disabling */
        if ( defined( 'OQN_DISABLE_FOOTER' ) && 'yes' === OQN_DISABLE_FOOTER ) {
            return $wp_admin_bar;
        }
        
        $wp_admin_bar->add_group( array(
            'id'     => 'oqn-group-footer',
            'parent' => 'ddw-oxygen-quicknav',
            'meta'   => array( 'class' => 'ab-sub-secondary' ),
        ) );
        
        $this->add_links_submenu( $wp_admin_bar );
        $this->add_about_submenu( $wp_admin_bar );
    }
    
    /**
     * Add Links submenu
     */
    private function add_links_submenu( $wp_admin_bar ) {
        
        $icon = '<span class="icon-svg"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M10 6V8H5V19H16V14H18V20C18 20.5523 17.5523 21 17 21H4C3.44772 21 3 20.5523 3 20V7C3 6.44772 3.44772 6 4 6H10ZM21 3V11H19L18.9999 6.413L11.2071 14.2071L9.79289 12.7929L17.5849 5H13V3H21Z"></path></svg></span> ';
        
        $wp_admin_bar->add_node( array(
            'id'     => 'oqn-links',
            'title'  => $icon . esc_html__( 'Links', 'oxygen-quicknav' ),
            'href'   => '#',
            'parent' => 'oqn-group-footer',
            'meta'   => array( 'class' => 'has-icon' ),
        ) );

        $links = array(
            'oxygen' => array(
                'title' => __( 'Oxygen HQ', 'oxygen-quicknav' ),
                'url'   => 'https://oxygenbuilder.com/',
            ),
            'oxygen-learn' => array(
                'title' => __( 'Learn Oxygen (Tutorials)', 'oxygen-quicknav' ),
                'url'   => 'https://oxygenbuilder.com/learn/',
            ),
            'oxygen-docs' => array(
                'title' => __( 'Oxygen Documentation', 'oxygen-quicknav' ),
                'url'   => 'https://oxygenbuilder.com/documentation/',
            ),
            'oxygen-youtube' => array(
                'title' => __( 'Oxygen YouTube Channel', 'oxygen-quicknav' ),
                'url'   => 'https://www.youtube.com/oxygen-builder',
            ),
            'oxygen-fb-group' => array(
                'title' => __( 'Oxygen FB Group', 'oxygen-quicknav' ),
                'url'   => 'https://www.facebook.com/groups/1626639680763454',
            ),
            'oxygen4fun' => array(
                'title' => __( 'Oxygen4Fun (Tutorials, Tips, Resources)', 'oxygen-quicknav' ),
                'url'   => 'https://oxygen4fun.supadezign.com/',
            ),
        );

        foreach ( $links as $id => $info ) {
            $wp_admin_bar->add_node( array(
                'id'     => 'oqn-link-' . sanitize_key( $id ),
                'title'  => esc_html( $info[ 'title' ] ),
                'href'   => esc_url( $info[ 'url' ] ),
                'parent' => 'oqn-links',
                'meta'   => array( 'target' => '_blank', 'rel' => 'nofollow noopener noreferrer' ),
            ) );
        }  // end foreach
    }

    /**
     * Add About submenu
     */
    private function add_about_submenu( $wp_admin_bar ) {
        
        $icon = '<span class="icon-svg"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17.841 15.659L18.017 15.836L18.1945 15.659C19.0732 14.7803 20.4978 14.7803 21.3765 15.659C22.2552 16.5377 22.2552 17.9623 21.3765 18.841L18.0178 22.1997L14.659 18.841C13.7803 17.9623 13.7803 16.5377 14.659 15.659C15.5377 14.7803 16.9623 14.7803 17.841 15.659ZM12 14V16C8.68629 16 6 18.6863 6 22H4C4 17.6651 7.44784 14.1355 11.7508 14.0038L12 14ZM12 1C15.315 1 18 3.685 18 7C18 10.2397 15.4357 12.8776 12.225 12.9959L12 13C8.685 13 6 10.315 6 7C6 3.76034 8.56434 1.12237 11.775 1.00414L12 1ZM12 3C9.78957 3 8 4.78957 8 7C8 9.21043 9.78957 11 12 11C14.2104 11 16 9.21043 16 7C16 4.78957 14.2104 3 12 3Z"></path></svg></span> ';
        
        $wp_admin_bar->add_node( array(
            'id'     => 'oqn-about',
            'title'  => $icon . esc_html__( 'About', 'oxygen-quicknav' ),
            'href'   => '#',
            'parent' => 'oqn-group-footer',
            'meta'   => array( 'class' => 'has-icon' ),
        ) );

        $about_links = array(
            'author'       => array(
                'title' => __( 'Author: David Decker', 'oxygen-quicknav' ),
                'url'   => 'https://deckerweb.de/',
            ),
            'github'       => array(
                'title' => __( 'Plugin on GitHub', 'oxygen-quicknav' ),
                'url'   => 'https://github.com/deckerweb/oxygen-quicknav',
            ),
            'kofi' => array(
                'title' => __( 'Buy Me a Coffee', 'oxygen-quicknav' ),
                'url'   => 'https://ko-fi.com/deckerweb',
            ),
        );

        foreach ( $about_links as $id => $info ) {
            $wp_admin_bar->add_node( array(
                'id'     => 'oqn-about-' . sanitize_key( $id ),
                'title'  => esc_html( $info[ 'title' ] ),
                'href'   => esc_url( $info[ 'url' ] ),
                'parent' => 'oqn-about',
                'meta'   => array( 'target' => '_blank', 'rel' => 'nofollow noopener noreferrer' ),
            ) );
        }  // end foreach
    }
    
    /**
     * Show the Admin Bar also in Block Editor full screen mode.
     */
    public function adminbar_block_editor_fullscreen() {
        
        if ( ! is_admin_bar_showing() ) return;
        
        /**
         * Depending on user color scheme get proper bg color value for admin bar.
         */
        $user_color_scheme = get_user_option( 'admin_color' );
        $admin_scheme      = $this->get_scheme_colors();
        
        $bg_color = $admin_scheme[ $user_color_scheme ][ 'bg' ];
        
        $inline_css_block_editor = sprintf(
            '
                @media (min-width: 600px) {
                    body.is-fullscreen-mode .block-editor__container {
                        top: var(--wp-admin--admin-bar--height);
                    }
                }
                
                @media (min-width: 782px) {
                    body.js.is-fullscreen-mode #wpadminbar {
                        display: block;
                    }
                
                    body.is-fullscreen-mode .block-editor__container {
                        min-height: calc(100vh - var(--wp-admin--admin-bar--height));
                    }
                
                    body.is-fullscreen-mode .edit-post-layout .editor-post-publish-panel {
                        top: var(--wp-admin--admin-bar--height);
                    }
                    
                    .edit-post-fullscreen-mode-close.components-button {
                        background: %s;
                    }
                    
                    .edit-post-fullscreen-mode-close.components-button::before {
                        box-shadow: none;
                    }
                }
                
                @media (min-width: 783px) {
                    .is-fullscreen-mode .interface-interface-skeleton {
                        top: var(--wp-admin--admin-bar--height);
                    }
                }
            ',
            sanitize_hex_color( $bg_color )
        );
        
        wp_add_inline_style( 'wp-block-editor', $inline_css_block_editor );
        
        $inline_css_edit_site = sprintf(
            '
            body.is-fullscreen-mode .edit-site {
                top: var(--wp-admin--admin-bar--height);
            }
            
            body.is-fullscreen-mode .edit-site-layout__canvas-container {
                top: calc( var(--wp-admin--admin-bar--height) * -1 );
            }
            
            .edit-site-editor__view-mode-toggle .edit-site-editor__view-mode-toggle-icon img,
            .edit-site-editor__view-mode-toggle .edit-site-editor__view-mode-toggle-icon svg {
                    background: %s;
            }
            '
        );
        
        wp_add_inline_style( 'wp-edit-site', $inline_css_edit_site );
        
        add_action( 'admin_bar_menu', array( $this, 'remove_adminbar_nodes' ), 999 );
    }
    
    /**
     * Remove Admin Bar nodes.
     */
    public function remove_adminbar_nodes( $wp_admin_bar ) {
        $wp_admin_bar->remove_node( 'wp-logo' );  
    }
    
    /**
     * Add additional plugin related info to the Site Health Debug Info section.
     *
     * @link https://make.wordpress.org/core/2019/04/25/site-health-check-in-5-2/
     *
     * @param array $debug_info Array holding all Debug Info items.
     * @return array Modified array of Debug Info.
     */
    public function site_health_debug_info( $debug_info ) {
    
        $string_undefined = esc_html_x( 'Undefined', 'Site Health Debug info', 'oxygen-quicknav' );
        $string_enabled   = esc_html_x( 'Enabled', 'Site Health Debug info', 'oxygen-quicknav' );
        $string_disabled  = esc_html_x( 'Disabled', 'Site Health Debug info', 'oxygen-quicknav' );
        $string_value     = ' – ' . esc_html_x( 'value', 'Site Health Debug info', 'oxygen-quicknav' ) . ': ';
        $string_version   = defined( '__BREAKDANCE_VERSION' ) ? __BREAKDANCE_VERSION : '';
    
        /** Add our Debug info */
        $debug_info[ 'oxygen-quicknav' ] = array(
            'label'  => esc_html__( 'Oxygen QuickNav', 'oxygen-quicknav' ) . ' (' . esc_html__( 'Plugin', 'oxygen-quicknav' ) . ')',
            'fields' => array(
    
                /** Various values */
                'oqn_plugin_version' => array(
                    'label' => esc_html__( 'Plugin version', 'oxygen-quicknav' ),
                    'value' => self::VERSION,
                ),
                'oqn_install_type' => array(
                    'label' => esc_html__( 'WordPress Install Type', 'oxygen-quicknav' ),
                    'value' => ( is_multisite() ? esc_html__( 'Multisite install', 'oxygen-quicknav' ) : esc_html__( 'Single Site install', 'oxygen-quicknav' ) ),
                ),
    
                /** Oxygen QuickNav constants */
                'OQN_VIEW_CAPABILITY' => array(
                    'label' => 'OQN_VIEW_CAPABILITY',
                    'value' => ( ! defined( 'OQN_VIEW_CAPABILITY' ) ? $string_undefined : ( OQN_VIEW_CAPABILITY ? $string_enabled : $string_disabled ) ),
                ),
                'OQN_ENABLED_USERS' => array(
                    'label' => 'OQN_ENABLED_USERS',
                    'value' => ( ! defined( 'OQN_ENABLED_USERS' ) ? $string_undefined : ( OQN_ENABLED_USERS ? $string_enabled . $string_value . implode( ', ', array_map( 'absint', OQN_ENABLED_USERS ) ) : $string_disabled ) ),
                ),
                'OQN_NAME_IN_ADMINBAR' => array(
                    'label' => 'OQN_NAME_IN_ADMINBAR',
                    'value' => ( ! defined( 'OQN_NAME_IN_ADMINBAR' ) ? $string_undefined : ( OQN_NAME_IN_ADMINBAR ? $string_enabled . $string_value . esc_html( OQN_NAME_IN_ADMINBAR )  : $string_disabled ) ),
                ),
                'OQN_ICON' => array(
                    'label' => 'OQN_ICON',
                    'value' => ( ! defined( 'OQN_ICON' ) ? $string_undefined : ( OQN_ICON ? $string_enabled . $string_value . sanitize_key( OQN_ICON ) : $string_disabled ) ),
                ),
                'OQN_NUMBER_TEMPLATES' => array(
                    'label' => 'OQN_NUMBER_TEMPLATES',
                    'value' => ( ! defined( 'OQN_NUMBER_TEMPLATES' ) ? $string_undefined : ( OQN_NUMBER_TEMPLATES ? $string_enabled . $string_value . absint( OQN_NUMBER_TEMPLATES ) : $string_disabled ) ),
                ),
                'OQN_DISABLE_FOOTER' => array(
                    'label' => 'OQN_DISABLE_FOOTER',
                    'value' => ( ! defined( 'OQN_DISABLE_FOOTER' ) ? $string_undefined : ( OQN_DISABLE_FOOTER ? $string_enabled : $string_disabled ) ),
                ),
                'oqn_bd_version' => array(
                    'label' => esc_html( 'Oxygen 6 Version', 'oxygen-quicknav' ),
                    'value' => ( ! defined( '__BREAKDANCE_VERSION' ) ? esc_html__( 'Plugin not installed', 'oxygen-quicknav' ) : $string_version ),
                ),
            ),  // end array
        );
    
        /** Return modified Debug Info array */
        return $debug_info;
    }
    
}  // end of class

new DDW_Oxygen_QuickNav();
    
endif;


if ( ! function_exists( 'ddw_oqn_pluginrow_meta' ) ) :
    
add_filter( 'plugin_row_meta', 'ddw_oqn_pluginrow_meta', 10, 2 );
/**
 * Add plugin related links to plugin page.
 *
 * @param array  $ddwp_meta (Default) Array of plugin meta links.
 * @param string $ddwp_file File location of plugin.
 * @return array $ddwp_meta (Modified) Array of plugin links/ meta.
 */
function ddw_oqn_pluginrow_meta( $ddwp_meta, $ddwp_file ) {
 
     if ( ! current_user_can( 'install_plugins' ) ) return $ddwp_meta;
 
     /** Get current user */
     $user = wp_get_current_user();
     
     /** Build Newsletter URL */
     $url_nl = sprintf(
         'https://deckerweb.us2.list-manage.com/subscribe?u=e09bef034abf80704e5ff9809&amp;id=380976af88&amp;MERGE0=%1$s&amp;MERGE1=%2$s',
         esc_attr( $user->user_email ),
         esc_attr( $user->user_firstname )
     );
     
     /** List additional links only for this plugin */
     if ( $ddwp_file === trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) . basename( __FILE__ ) ) {
         $ddwp_meta[] = sprintf(
             '<a class="button button-inline" href="https://ko-fi.com/deckerweb" target="_blank" rel="nofollow noopener noreferrer" title="%1$s">❤ <b>%1$s</b></a>',
             esc_html_x( 'Donate', 'Plugins page listing', 'oxygen-quicknav' )
         );
 
         $ddwp_meta[] = sprintf(
             '<a class="button-primary" href="%1$s" target="_blank" rel="nofollow noopener noreferrer" title="%2$s">⚡ <b>%2$s</b></a>',
             $url_nl,
             esc_html_x( 'Join our Newsletter', 'Plugins page listing', 'oxygen-quicknav' )
         );
     }  // end if
 
     return apply_filters( 'ddw/admin_extras/pluginrow_meta', $ddwp_meta );
 
 }  // end function
 
 endif;