<?php
/**
 * Plugin Name: WooCommerce Donations
 * Plugin URI:  github.com/helgatheviking/woocommerce-donations
 * Description: Donations for WooCommerce. Enhanced by Name Your Price and Subscriptions.
 * Version:     1.0.0
 * Author:      Kathy Darling
 * Author URI:  kathyisawesome.com
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wc_donations
 * Domain Path: /languages
 * Requires at least: 4.0.0
 * Tested up to: 4.4.0
 * WC requires at least: 3.0.0
 * WC tested up to: 3.5.0   
 */

/**
 * Copyright: © 2018 Kathy Darling.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

/**
 * The Main WC_Donations class
 **/
if ( ! class_exists( 'WC_Donations' ) ) :

class WC_Donations {

	const VERSION = '1.0.0';
	const REQUIRED_WC = '3.4.0';

	/**
	 * Plugin Path
	 *
	 * @since 1.0.0
	 * @var string $path
	 */
	public static $plugin_path = '';

	/**
	 * Plugin URL
	 *
	 * @since 1.0.0
	 * @var string $url
	 */
	public static $plugin_url = '';

	public static function init(){

		// check we're running the required version of WC
		if ( ! defined( 'WC_VERSION' ) || version_compare( WC_VERSION, self::REQUIRED_WC, '<' ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'admin_notice' ) );
			return false;
		}

		self::includes();
		self::init_hooks();

	}


	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public static function includes() {
		/**
		 * Class autoloader.
		 */
		//include_once WC_ABSPATH . 'includes/class-wc-autoloader.php';

		/*
		 * Register our autoloader
		 */
		//spl_autoload_register( array( __CLASS__, 'autoloader' ) );
		//
		// Data class.
		require_once( 'includes/data/class-wc-donation-data.php' );

		// Product class.
		require_once( 'includes/class-wc-product-donation.php' );

		// include admin class to handle all backend functions
		if( is_admin() ){
			update_option( 'wc_donations_version', self::VERSION );
			require_once( 'includes/admin/class-wc-donations-admin.php' );
		}

		// Include the front-end functions.
/*		if ( ! is_admin() ) {
			self::$instance->display = new WC_Donations_Display();
			self::$instance->cart = new WC_Donations_Cart();
			self::$instance->order = new WC_Donations_Order();
		}
*/
		// For compatibility with other extensions.
		require_once( 'includes/compatibility/class-wc-donations-compatibility.php' );

	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 2.3
	 */
	private static function init_hooks() {
	
		// Load translation files.
		add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ) );

		// Include required files.
		add_action( 'after_setup_theme', array( __CLASS__, 'template_includes' ) );

		// Add donation product type.
		add_filter( 'product_type_selector', array( __CLASS__, 'add_new_product_type' ) );
	}


	/*-----------------------------------------------------------------------------------*/
	/* Required Files */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Load Classes
	 *
	 * @return      void
	 * @since       1.0.0
	 */
	public static function autoloader( $class_name ){
		if ( class_exists( $class_name ) ) {
			return;
		}

		if ( false === strpos( $class_name, self::PREFIX ) ) {
			return;
		}

		$class_name = 'class-' . strtolower( $class_name );
		$classes_dir = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;

		$class_file = str_replace( '_', '-', $class_name ) . '.php';

		if ( file_exists( $classes_dir . $class_file ) ){
			require_once $classes_dir . $class_file;
		}
	}

	/**
	 * Include frontend functions and hooks
	 *
	 * @return void
	 * @since  1.0
	 */
	public static function template_includes(){
		require_once( 'includes/wc-donations-template-functions.php' );
		require_once( 'includes/wc-donations-template-hooks.php' );
	}


	/**
	 * Displays a warning message if version check fails.
	 * @return string
	 * @since  2.1
	 */
	public static function admin_notice() {
		echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Donations requires at least WooCommerce %s in order to function. Please upgrade WooCommerce.', 'wc-donations' ), self::REQUIRED_WC ) . '</p></div>';
	}


	/*-----------------------------------------------------------------------------------*/
	/* Localization */
	/*-----------------------------------------------------------------------------------*/


	/**
	 * Make the plugin translation ready
	 *
	 * @return void
	 * @since  1.0
	 */
	public static function load_plugin_textdomain() {
		load_plugin_textdomain( 'wc-donations' , false , dirname( plugin_basename( __FILE__ ) ) .  '/languages/' );
	}

	/*-----------------------------------------------------------------------------------*/
	/* Add new product type */
	/*-----------------------------------------------------------------------------------*/


	/**
	 * Add the 'donation' product type to the WooCommerce product type select box.
	 *
	 * @param array Array of Product types & their labels, excluding the Subscription product type.
	 * @return array Array of Product types & their labels, including the Subscription product type.
	 * @since 1.0
	 */
	public static function add_new_product_type( $product_types ) {
		$product_types['donation']          = __( 'Donation', 'wc-donations' );
		return $product_types;
	}

	/*-----------------------------------------------------------------------------------*/
	/* Helper Functions */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Get plugin path
	 */
	public static function get_plugin_path() {
		if( self::$plugin_path == '' ) {
			self::$plugin_path = untrailingslashit( plugin_dir_path(__FILE__) );
		}
		return self::$plugin_path;
	}

	/**
	 * Get plugin URL
	 */
	public static function get_plugin_url() {
		if( self::$plugin_url == '' ) {
			self::$plugin_url = untrailingslashit( plugin_dir_url(__FILE__) );
		}
		return self::$plugin_url;
	}

} //end class: do not remove or there will be no more guacamole for you

endif; // end class_exists check

// Launch the whole plugin
add_action( 'woocommerce_loaded', 'WC_Donations::init' );