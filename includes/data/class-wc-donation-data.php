<?php
/**
 * Mix and Match Register Data Store
 *
 * @author   Kathy Darling
 * @package  WooCommerce Mix and Match Products/Data
 * @since    1.0.0
 * @version  1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Donation_Data Class.
 *
 * MnM Data filters and includes.
 */
class WC_Donation_Data {

	public static function init() {

		// Mix and Match product custom post type data store.
		require_once( 'class-wc-product-donation-data-store-cpt.php' );

		// Register the Mix and Match custom post type data store.
		add_filter( 'woocommerce_data_stores', array( __CLASS__, 'register_new_data_store' ), 10 );

	}

	/**
	 * Registers the new product custom post type data store.
	 *
	 * @param  array  $stores
	 * @return array
	 */
	public static function register_new_data_store( $stores ) {

		$stores[ 'product-donation' ] = 'WC_Product_Donation_Data_Store_CPT';

		return $stores;
	}
}

WC_Donation_Data::init();
