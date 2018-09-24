<?php
/**
 * Donation Product Data Store
 *
 * @author   Kathy Darling
 * @category Class
 * @package  WooCommerce Donations/Data
 * @since    1.0.0
 * @version  1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Product_Donation_Data_Store_CPT Class.
 *
 * Donation data stored as Custom Post Type. For use with the WC 3.0+ CRUD API.
 *
 * @uses  WC_Product_Data_Store_CPT
 */
class WC_Product_Donation_Data_Store_CPT extends WC_Product_Variable_Data_Store_CPT {

	/**
	 * Data stored in meta keys, but not considered "meta" for the Donation type.
	 * 
	 * @var array
	 */
	protected $extended_internal_meta_keys = array(
		'_donation_layout_style',
		'_donation_add_to_cart_form_location'
	);

	/**
	 * Maps extended properties to meta keys.
	 * 
	 * @var array
	 */
	protected $props_to_meta_keys = array(
		'layout'                     => '_donation_layout_style',
		'add_to_cart_form_location'  => '_donation_add_to_cart_form_location',
	);

	/**
	 * Callback to exclude donation specific meta data.
	 *
	 * @param  object  $meta
	 * @return bool
	 */
	protected function exclude_internal_meta_keys( $meta ) {
		return parent::exclude_internal_meta_keys( $meta ) && ! in_array( $meta->meta_key, $this->extended_internal_meta_keys );
	}

	/**
	 * Reads all donation specific post meta.
	 *
	 * @param  WC_Product_Donation  $product
	 */
	protected function read_extra_data( &$product ) {

		foreach ( $this->props_to_meta_keys as $property => $meta_key ) {

			// Get meta value.
			$function = 'set_' . $property;
			if ( is_callable( array( $product, $function ) ) ) {
				$product->{$function}( get_post_meta( $product->get_id(), $meta_key, true ) );
			}

		}

	}

	/**
	 * Writes all donation specific post meta.
	 *
	 * @param  WC_Product_Donation  $product
	 * @param  bool                   $force
	 */
	protected function update_post_meta( &$product, $force = false ) {

		$this->extra_data_saved = true;

		parent::update_post_meta( $product, $force );

		$id                 = $product->get_id();
		$meta_keys_to_props = array_flip( array_diff_key( $this->props_to_meta_keys, array( 'price' => 1, 'min_raw_price' => 1, 'min_raw_regular_price' => 1 ) ) );
		$props_to_update    = $force ? $meta_keys_to_props : $this->get_props_to_update( $product, $meta_keys_to_props );

		foreach ( $props_to_update as $meta_key => $property ) {

			$property_get_fn = 'get_' . $property;

			// Get meta value.
			$meta_value = $product->$property_get_fn( 'edit' );

			// Sanitize bool for storage.
			if ( is_bool( $meta_value ) ) {
				$meta_value = wc_bool_to_string( $meta_value );
			}

			if ( update_post_meta( $id, $meta_key, $meta_value ) && ! in_array( $property, $this->updated_props ) ) {
				$this->updated_props[] = $meta_key;
			}
		}
	}

}
