<?php
/**
 * Name Your Price Compatibility
 *
 * @author   Kathy Darling
 * @category Compatibility
 * @package  WooCommerce Donations/Compatibility
 * @since    1.0.0
 * @version  1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Donations_NYP_Compatibility Class.
 *
 * Adds compatibility with WooCommerce Cost of Goods.
 */
class WC_Donations_NYP_Compatibility {

	public static function init() {

		// Enable NYP support.
		add_action( 'wc_nyp_simple_supported_types', array( __CLASS__, 'add_nyp_support' ) );

		// Adds the meta options.
		add_action( 'wc_donation_product_options', array( __CLASS__, 'add_meta_options' ), 30, 2 );

		// Processes and saves the necessary post metas from the selections made above.
		add_action( 'woocommerce_admin_process_donation_product_object', array( __CLASS__, 'save_meta_data' ) );
	}

	/**
	 * Tell NYP to support Donations'. 
	 *
	 * @param  int $post_id
	 * @param  WC_Donations  $donation_product_object
	 */
	public static function add_nyp_support( $types ) { 
		$types[] = 'donation';
		return $types;

		// @Todo instead of this we may need to filter woocommerce_is_nyp ?
	}

	/**
	 * Render NYP option on 'wc_donation_product_options'.
	 *
	 * @param  int $post_id
	 * @param  WC_Donations  $donation_product_object
	 */
	public static function add_meta_options( $post_id, $donation_product_object ) { ?>

		<div class="option_group">

		<?php

		// Support custom NYP amount.
		woocommerce_wp_checkbox( array(
			'id'          => '_custom_amount',
			'label'       => __( 'Enable custom amounts', 'wc-donations' ),
			'value'       => WC_Name_Your_Price_Helpers::is_nyp( $donation_product_object ) ? 'yes' : 'no',
			'description' => __( 'Customers are allowed to determine their own donation amount.', 'wc-donations' ),
			'desc_tip'    => true
		) );

		?>

		</div>
		
		<?php

	}

	/**
	 * Process, verify and save product data
	 *
	 * @param  WC_Product  $product
	 */
	public static function save_meta_data( $product ) {

		if ( isset( $_POST['_custom_amount'] ) ) {
			$product->update_meta_data( '_nyp', 'yes' );
		} else {
			$product->update_meta_data( '_nyp', 'no' );
		}

	}

}

WC_Donations_NYP_Compatibility::init();