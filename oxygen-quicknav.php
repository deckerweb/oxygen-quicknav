<?php
/**
 * Forked from "Breakdance Navigator" by Peter Kulcsár
 * License: GPL v2 or later
 * GitHub Repository: https://github.com/beamkiller/breakdance-navigator
 */
 
/**
 * Plugin Name: Oxygen QuickNav
 * Plugin URI: https://github.com/deckerweb/oxygen-quicknav
 * Description: Adds a quick-access navigator to the WordPress Admin Bar (Toolbar). It allows easy access to Oxygen Templates, Headers, Footers, Components, and Pages edited with Oxygen, along with some other essential settings. For Oxygen 6+ only!
 * Version: 1.0.0
 * Author: David Decker – DECKERWEB
 * Author URI: https://deckerweb.de/
 * Text Domain: oxygen-quicknav
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires WP: 6.7
 * Requires PHP: 7.4
 *
 * Original Copyright (c) 2024 Peter Kulcsár
 * Copyright (c) 2025 David Decker – DECKERWEB
 */

/** Prevent direct access */
if ( ! defined( 'ABSPATH' ) ) {
    exit;  // Exit if accessed directly.
}


if ( ! class_exists( 'DDW_Oxygen_QuickNav' ) ) {

    class DDW_Oxygen_QuickNav {

        public function __construct() {           
            add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 999 );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_bar_styles' ) );  // for Admin
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_admin_bar_styles' ) );     // for front-end
        }

        /**
         * Enqueue custom styles for the admin bar.
         */
        public function enqueue_admin_bar_styles() {      
            $inline_css = sprintf(
                '
                /* Style for the separator */
                #wp-admin-bar-ddw-oxygen-quicknav > .ab-sub-wrapper #wp-admin-bar-oqn-settings {
                    border-bottom: 1px dashed rgba(255, 255, 255, 0.33);
                    padding-bottom: 5px;
                }
                '
            );
            
            if ( is_admin_bar_showing() ) {
                wp_add_inline_style( 'admin-bar', $inline_css );
            }
        }

        /**
         * Adds the main Oxygen menu and its submenus to the Admin Bar.
         *
         * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance.
         */
        public function add_admin_bar_menu( $wp_admin_bar ) {
            
            /** Don't do anything if Oxygen Builder v6+ plugin is NOT active */
            if ( !defined( 'BREAKDANCE_MODE' ) && 'oxygen' !== BREAKDANCE_MODE ) {
                return;
            }
            
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

            /** Add submenus */
            $this->add_pages_submenu( $wp_admin_bar );
            $this->add_templates_submenu( $wp_admin_bar );
            $this->add_headers_submenu( $wp_admin_bar );
            $this->add_footers_submenu( $wp_admin_bar );
            $this->add_components_submenu( $wp_admin_bar );
            $this->add_settings_submenu( $wp_admin_bar );
            $this->add_plugin_support_group( $wp_admin_bar );  // group node
            $this->add_yabe_webfont_submenu( $wp_admin_bar );
            $this->add_wpsix_exporter_submenu( $wp_admin_bar );
            $this->add_footer_group( $wp_admin_bar );  // group node
            $this->add_links_submenu( $wp_admin_bar );
            $this->add_about_submenu( $wp_admin_bar );
        }

        /**
         * Add Pages submenu (just regular WordPress Pages)
         * NOTE: This sets the parent item; no Oxygen related stuff here, yet.
         */
        private function add_pages_submenu( $wp_admin_bar ) {
            $wp_admin_bar->add_node( array(
                'id'     => 'oqn-pages',
                'title'  => esc_html__( 'Pages', 'oxygen-quicknav' ),
                'href'   => esc_url( admin_url( 'edit.php?post_type=page' ) ),
                'parent' => 'ddw-oxygen-quicknav',
            ) );

            $this->add_oxygen_pages_to_admin_bar( $wp_admin_bar );
        }

        /**
         * Add up to 10 Oxygen-edited Pages
         */
        private function add_oxygen_pages_to_admin_bar( $wp_admin_bar ) {
            $oxy_pages = $this->get_oxygen_pages();

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
         * Get all Oxygen-edited Pages. Helper function.
         */
        private function get_oxygen_pages() {
            $args = array(
                'post_type'      => 'page',
                'posts_per_page' => 10,
                'post_status'    => 'publish',
                'orderby'        => 'modified',
                'order'          => 'DESC',
                'meta_query'     => array(
                    array(
                        'key'     => '_oxygen_data',  // only Oxy-edited pages have that
                        'compare' => 'EXISTS',
                    ),
                ),
            );
            return get_posts( $args );
        }

        /**
         * Get items of a Oxygen 6+ template type. Helper function.
         *
         * @uses get_posts()
         *
         * @param string $post_type Slug of post type to query for.
         */
        private function get_oxygen_template_type( $post_type ) {
            $args = array(
                'post_type'      => sanitize_key( $post_type ),
                'posts_per_page' => 10,
                'post_status'    => 'publish',
                'orderby'        => 'modified',
                'order'          => 'DESC',
            );
            
            apply_filters( 'ddw/quicknav/oxy_get_template_type', $args, $post_type );
            
            return get_posts( $args );
        }
        
        /**
         * Add Oxygen Templates submenu (parent node)
         */
        private function add_templates_submenu( $wp_admin_bar ) {
            $wp_admin_bar->add_node( array(
                'id'     => 'oqn-templates',
                'title'  => esc_html__( 'Templates', 'oxygen-quicknav' ),
                'href'   => esc_url( admin_url( 'admin.php?page=oxygen_template' ) ),
                'parent' => 'ddw-oxygen-quicknav',
            ) );

            $this->add_templates_to_admin_bar( $wp_admin_bar );
        }

        /**
         * Add up to 10 Oxygen Templates (child nodes)
         */
        private function add_templates_to_admin_bar( $wp_admin_bar ) {
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
         * Add Oxygen Headers submenu (parent node)
         */
        private function add_headers_submenu( $wp_admin_bar ) {
            $wp_admin_bar->add_node( array(
                'id'     => 'oqn-headers',
                'title'  => esc_html__( 'Headers', 'oxygen-quicknav' ),
                'href'   => esc_url( admin_url( 'admin.php?page=oxygen_header' ) ),
                'parent' => 'ddw-oxygen-quicknav',
            ) );

            $this->add_headers_to_admin_bar( $wp_admin_bar );
        }

        /**
         * Add up to 10 Oxygen Header templates (child nodes)
         */
        private function add_headers_to_admin_bar( $wp_admin_bar ) {
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
         * Add Oxygen Footers submenu (parent node)
         */
        private function add_footers_submenu( $wp_admin_bar ) {
            $wp_admin_bar->add_node( array(
                'id'     => 'oqn-footers',
                'title'  => esc_html__( 'Footers', 'oxygen-quicknav' ),
                'href'   => esc_url( admin_url( 'admin.php?page=oxygen_footer' ) ),
                'parent' => 'ddw-oxygen-quicknav',
            ) );

            $this->add_footers_to_admin_bar( $wp_admin_bar );
        }

        /**
         * Add up to 10 Oxygen Footer templates (child nodes)
         */
        private function add_footers_to_admin_bar( $wp_admin_bar ) {
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
         * Add Oxygen Components submenu (parent node)
         */
        private function add_components_submenu( $wp_admin_bar ) {
            $wp_admin_bar->add_node( array(
                'id'     => 'oqn-components',
                'title'  => esc_html__( 'Components', 'oxygen-quicknav' ),
                'href'   => esc_url( admin_url( 'admin.php?page=oxygen_block' ) ),
                'parent' => 'ddw-oxygen-quicknav',
            ) );

            $this->add_components_to_admin_bar( $wp_admin_bar );
        }

        /**
         * Add up to 10 Oxygen Components templates (child nodes)
         */
        private function add_components_to_admin_bar( $wp_admin_bar ) {
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
         * Add Oxygen Settings submenu (parent node)
         */
        private function add_settings_submenu( $wp_admin_bar ) {
            $wp_admin_bar->add_node( array(
                'id'     => 'oqn-settings',
                'title'  => esc_html__( 'Settings', 'oxygen-quicknav' ),
                'href'   => esc_url( admin_url( 'admin.php?page=oxygen_settings' ) ),
                'parent' => 'ddw-oxygen-quicknav',
                'meta'   => array( 'class' => 'oxy-settings-separator' ),
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
            }
        }
 
        /**
         * Add group node for plugin support
         */
        private function add_plugin_support_group( $wp_admin_bar ) {
            $wp_admin_bar->add_group( array(
                'id'     => 'oqn-plugins',
                'parent' => 'ddw-oxygen-quicknav',
            ) );
        }
 
        /**
         * Add Yabe Webfont (free & Pro) submenu if the plugin is active
         */
        private function add_yabe_webfont_submenu( $wp_admin_bar ) {
        
            if ( class_exists( '\Yabe\Webfont\Plugin' ) ) {
                $wp_admin_bar->add_node( array(
                    'id'     => 'oqn-yabe-webfont',
                    'title'  => esc_html__( 'Yabe Webfont', 'oxygen-quicknav' ),
                    'href'   => esc_url( admin_url( 'themes.php?page=yabe_webfont' ) ),
                    'parent' => 'oqn-plugins',
                ) );
            }
        }
        
        /**
         * Add WPSix Exporter submenu if the plugin is active
         */
        private function add_wpsix_exporter_submenu( $wp_admin_bar ) {
        
            if ( defined( 'WPSIX_EXPORTER_URL' ) ) {
                $wp_admin_bar->add_node( array(
                    'id'     => 'oqn-wpsix-exporter',
                    'title'  => esc_html__( 'WPSix Exporter', 'oxygen-quicknav' ),
                    'href'   => esc_url( admin_url( 'admin.php?page=wpsix_exporter' ) ),
                    'parent' => 'oqn-plugins',
                ) );
            }
        }
        
        /**
         * Add group node for footer items (Links & About)
         */
        private function add_footer_group( $wp_admin_bar ) {
            /** Allows for custom disabling */
            if ( defined( 'OQN_DISABLE_FOOTER' ) && 'yes' === OQN_DISABLE_FOOTER ) {
                return;
            }
            
            $wp_admin_bar->add_group( array(
                'id'     => 'oqn-footer',
                'parent' => 'ddw-oxygen-quicknav',
            ) );
        }
        
        /**
         * Add Links submenu
         */
        private function add_links_submenu( $wp_admin_bar ) {
            $wp_admin_bar->add_node( array(
                'id'     => 'oqn-links',
                'title'  => esc_html__( 'Links', 'oxygen-quicknav' ),
                'href'   => '#',
                'parent' => 'oqn-footer',
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
                    'meta'   => array( 'target' => '_blank' ),
                ) );
            }  // end foreach
        }

        /**
         * Add About submenu
         */
        private function add_about_submenu( $wp_admin_bar ) {
            $wp_admin_bar->add_node( array(
                'id'     => 'oqn-about',
                'title'  => esc_html__( 'About', 'oxygen-quicknav' ),
                'href'   => '#',
                'parent' => 'oqn-footer',
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
                    'meta'   => array( 'target' => '_blank' ),
                ) );
            }  // end foreach
        }
    }  // end of class

    new DDW_Oxygen_QuickNav();
    
}  // end if