<?php
/**
 * Extension Compatibilty
 *
 * @author   Kathy Darling
 * @category Classes
 * @package  WooCommerce Donations/Compatibility
 * @since    1.0.0
 * @version  1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Donations_Compatibility Class.
 *
 * Load classes for making Donations compatible with other plugins.
 */
class WC_Donations_Compatibility {

	/**
	 * Init compatibility classes.
	 */
	public static function init() {

		// Name Your Price support.
		if ( class_exists( 'WC_Name_Your_Price' ) ) {
			require_once( 'modules/class-wc-nyp-compatibility.php' );
		}

		// Subscriptions support.
		if ( class_exists( 'WC_Points_Rewards_Product' ) ) {
			//require_once( 'modules/class-wc-subscriptions-compatibility.php' );
		}

		// One Page Checkout support.
		if ( function_exists( 'is_wcopc_checkout' ) ) {
			//require_once( 'modules/class-wc-opc-compatibility.php' );
		}

	}

	/**
	 * Tells if a product is a Name Your Price product, provided that the extension is installed.
	 *
	 * @param  mixed  $product
	 * @return bool
	 */
	public static function is_nyp( $product ) {

		if ( class_exists( 'WC_Name_Your_Price_Helpers' ) && WC_Name_Your_Price_Helpers::is_nyp( $product ) ) {
			return true;
		} else {
			return false;
		}
		
	}
}

add_action( 'plugins_loaded', array( 'WC_Donations_Compatibility', 'init' ), 100 );