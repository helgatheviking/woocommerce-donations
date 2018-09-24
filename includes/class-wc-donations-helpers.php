<?php
/**
 * WC_Donations_Helpers class
 *
 * @class 	WC_Donations_Order
 * @version 0.1.0
 * @since   0.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Donations_Helpers {

	/**
	 * Check is the installed version of WooCommerce is 2.5 or newer.
	 *
	 * @return	boolean
	 * @access 	public
	 * @since 	0.1.0
	 */
	public static function is_woocommerce_2_5() {
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.5.0-dev' ) >= 0 ) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Is the Sample Product Checkbox Checked
	 *
	 * @param  int $product_id
	 * @return	boolean
	 * @access 	public
	 * @since 	0.1.0
	 */
	public static function is_product_checkbox( $product_id ) {
		if ( 'yes' == get_post_meta( $product_id, '_wc_donations', true ) ) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Get the Sample Number
	 *
	 * @return	boolean
	 * @access 	public
	 * @since 	0.1.0
	 */
	public static function get_sample_number( $product_id ) {
		return floatval( get_post_meta( $product_id, '_wc_donations_number', true ) );
	}


	/**
	 * Get the Sample Textbox
	 *
	 * @return	boolean
	 * @access 	public
	 * @since 	0.1.0
	 */
	public static function get_sample_textbox( $product_id ) {
		return get_post_meta( $product_id, '_wc_donations_textbox', true );
	}

} //end class