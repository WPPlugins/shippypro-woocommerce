<?php
/*
	Plugin Name: ShippyPro WooCommerce Plugin
	Description: Obtain Real time shipping rates via the ShippyPro API.
	Version: 0.0.1
	Author: ShippyPro
	Author URI: https://www.shippypro.com
*/

//Dev Version: 2.6.1
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Required functions
if ( ! function_exists( 'shp_is_woocommerce_active' ) ) {
	require_once( 'shippypro-includes/shp-functions.php' );
}

// WC active check
if ( ! shp_is_woocommerce_active() ) {
	return;
}

/**
 * Plugin activation check
 */
function shp_shippypro_welcome_screen_activation_redirect(){
	set_transient('shp_shippypro_welcome_screen_activation_redirect', true, 30);
}

register_activation_hook( __FILE__, 'shp_shippypro_welcome_screen_activation_redirect' );

define("SHP_SHIPPYPRO_ID", "shp_shipping_shippypro");

/**
 * ShippyPro_WooCommerce class
 */
if(!class_exists('ShippyPro_WooCommerce')){
	class ShippyPro_WooCommerce {
		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'init' ) );
            add_action('admin_init', array($this,'shp_shippypro_welcome'));
            add_action('admin_menu', array($this,'shp_shippypro_welcome_screen'));
            add_action('admin_head', array($this,'shp_shippypro_welcome_screen_remove_menus'));
                
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'shp_plugin_action_links' ) );
			add_action( 'woocommerce_shipping_init', array( $this, 'shp_shipping_init') );
			add_filter( 'woocommerce_shipping_methods', array( $this, 'shp_shippypro_add_method') );
		}


		public function init(){
			if ( ! class_exists( 'shp_order' ) ) {
				include_once 'includes/class-shp-legacy.php';
			}		
		}
        
        public function shp_shippypro_welcome()
        {
            if (!get_transient('shp_shippypro_welcome_screen_activation_redirect')) {
                 return;
            }
            delete_transient('shp_shippypro_welcome_screen_activation_redirect');
            wp_safe_redirect(add_query_arg(array('page' => 'ShippyPro-Welcome'), admin_url('index.php')));
        }
        
        public function shp_shippypro_welcome_screen()
        {
            add_dashboard_page('Welcome To ShippyPro', 'Welcome To ShippyPro', 'read', 'ShippyPro-Welcome', array($this,'shp_shippypro_screen_content'));
        }
        
        public function shp_shippypro_screen_content()
        {
            include 'includes/shp_shippypro_welcome.php';
        }
        
        public function shp_shippypro_welcome_screen_remove_menus()
        {
             remove_submenu_page('index.php', 'ShippyPro-Welcome');
        }

		/**
		 * Plugin page links
		 */
		public function shp_plugin_action_links( $links ) {
			$plugin_links = array(
				'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=shp_shipping_shippypro' ) . '">' . __( 'Settings', 'shippypro-woocommerce' ) . '</a>',
				'<a href="' . admin_url('index.php?page=ShippyPro-Welcome') . '" target="_blank">' . __( 'Get Started', 'shippypro-woocommerce' ) . '</a>'
			);
			return array_merge( $plugin_links, $links );
		}

		/**
		 * shp_shippypro_init function.
		 *
		 * @access public
		 * @return void
		 */
		function shp_shipping_init() {
			include_once( 'includes/class-shp-shippypro.php' );
		}

		/**
		 * shp_shippypro_add_method function.
		 *
		 * @access public
		 * @param mixed $methods
		 * @return void
		 */
		function shp_shippypro_add_method( $methods ) {
			$methods[] = 'Shp_Shipping_ShippyPro';
			return $methods;
		}
	}
	new ShippyPro_WooCommerce();
}
