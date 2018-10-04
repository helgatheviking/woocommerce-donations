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
	 * The single instance of the class.
	 *
	 * @var WC_Donations
	 */
	protected static $_instance = null;

	/**
	 * Plugin Path
	 *
	 * @var string $path
	 */
	private $plugin_path = '';

	/**
	 * Plugin URL
	 *
	 * @var string $url
	 */
	private $plugin_url = '';


	/**
	 * Main class Instance.
	 *
	 * Ensures only one instance of class is loaded or can be loaded.
	 *
	 * @static
	 * @return WC_Donations - Main instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'wc-donations' ) );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'wc-donations' ) );
	}

	/**
	 * Constructor.
	 */
	public function __construct(){
		$this->environment_check();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function environment_check() {

		// check we're running the required version of WC
		if ( ! defined( 'WC_VERSION' ) || version_compare( WC_VERSION, self::REQUIRED_WC, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice' ) );
			return false;
		}

	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		/**
		 * Class autoloader.
		 */
		//include_once WC_ABSPATH . 'includes/class-wc-autoloader.php';

		/*
		 * Register our autoloader
		 */
		//spl_autoload_register( array( $this, 'autoloader' ) );
		
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
	 */
	private function init_hooks() {
	
		// Load translation files.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Include required files.
		add_action( 'after_setup_theme', array( $this, 'template_includes' ) );

		// Add donation product type.
		add_filter( 'product_type_selector', array( $this, 'add_new_product_type' ) );
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
	public function autoloader( $class_name ){
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
	 */
	public function template_includes(){
		require_once( 'includes/wc-donations-template-functions.php' );
		require_once( 'includes/wc-donations-template-hooks.php' );
	}


	/**
	 * Displays a warning message if version check fails.
	 * @return string
	 */
	public function admin_notice() {
		echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Donations requires at least WooCommerce %s in order to function. Please upgrade WooCommerce.', 'wc-donations' ), self::REQUIRED_WC ) . '</p></div>';
	}


	/*-----------------------------------------------------------------------------------*/
	/* Localization */
	/*-----------------------------------------------------------------------------------*/


	/**
	 * Make the plugin translation ready
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
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
	public function add_new_product_type( $product_types ) {
		$product_types['donation']          = __( 'Donation', 'wc-donations' );
		return $product_types;
	}

	/*-----------------------------------------------------------------------------------*/
	/* Helper Functions */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Get plugin path
	 */
	public function get_plugin_path() {
		if( $this->plugin_path == '' ) {
			$this->plugin_path = untrailingslashit( plugin_dir_path(__FILE__) );
		}
		return $this->plugin_path;
	}

	/**
	 * Get plugin URL
	 */
	public function get_plugin_url() {
		if( $this->plugin_url == '' ) {
			$this->plugin_url = untrailingslashit( plugin_dir_url(__FILE__) );
		}
		return $this->plugin_url;
	}

} //end class: do not remove or there will be no more guacamole for you

endif; // end class_exists check

/**
 * Main instance of WooCommerce Donations.
 *
 * Returns the main instance of to prevent the need to use globals.
 *
 * @return WC_Donations
 */
function WC_Donations() {
	return WC_Donations::get_instance();
}

// Launch the whole plugin.
add_action( 'woocommerce_loaded', 'WC_Donations' );