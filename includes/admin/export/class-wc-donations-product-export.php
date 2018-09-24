<?php
/**
 * Product Export Class
 *
 * @author   Kathy Darling
 * @category Admin
 * @package  WooCommerce Donations/Admin/Export
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Donations_Product_Export Class.
 *
 * Add support for Donations to WooCommerce product export.
 */
class WC_Donations_Product_Export {

	/**
	 * Hook in.
	 */
	public static function init() {

		// Add CSV columns for exporting bundle data.
		add_filter( 'woocommerce_product_export_column_names', array( __CLASS__, 'add_columns' ) );
		add_filter( 'woocommerce_product_export_product_default_columns', array( __CLASS__, 'add_columns' ) );

		// "MnM Items" column data.
		add_filter( 'woocommerce_product_export_product_column_WC_Donations_contents', array( __CLASS__, 'export_contents' ), 10, 2 );
		add_filter( 'woocommerce_product_export_product_column_WC_Donations_min_container_size', array( __CLASS__, 'export_min_container_size' ), 10, 2 );
		add_filter( 'woocommerce_product_export_product_column_WC_Donations_max_container_size', array( __CLASS__, 'export_max_container_size' ), 10, 2 );
		add_filter( 'woocommerce_product_export_product_column_WC_Donations_priced_per_product', array( __CLASS__, 'export_priced_per_product' ), 10, 2 );
		add_filter( 'woocommerce_product_export_product_column_WC_Donations_shipped_per_product', array( __CLASS__, 'export_shipped_per_product' ), 10, 2 );
	}

	/**
	 * Add CSV columns for exporting bundle data.
	 *
	 * @param  array  $columns
	 * @return array  $columns
	 */
	public static function add_columns( $columns ) {

		$columns[ 'WC_Donations_contents' ]  		           = __( 'MnM Contents (JSON-encoded)', 'wc-donations' );
		$columns[ 'WC_Donations_min_container_size' ]        = __( 'MnM Minimum Container Size', 'wc-donations' );
		$columns[ 'WC_Donations_max_container_size' ]        = __( 'MnM Maximum Container Size', 'wc-donations' );
		$columns[ 'WC_Donations_priced_per_product' ]        = __( 'MnM Per-Item Pricing', 'wc-donations' );
		$columns[ 'WC_Donations_shipped_per_product' ] 	   = __( 'MnM Per-Item Shipping', 'wc-donations' );

		/**
		 * Mix and Match Export columns.
		 *
		 * @param  array $columns
		 */
		return apply_filters( 'woocommerce_mnm_export_column_names', $columns );
	}

	/**
	 * MnM contents data column content.
	 *
	 * @param  mixed       $value
	 * @param  WC_Product  $product
	 * @return mixed       $value
	 */
	public static function export_contents( $value, $product ) {

		if ( $product->is_type( 'mix-and-match' ) ) {

			$mnm_contents = $product->get_contents( 'edit' );

			if ( ! empty( $mnm_contents ) ) {

				$data = array();

				foreach ( $mnm_contents as $mnm_item_id => $mnm_item_data ) {
					
					$mnm_item_data = array();

					$mnm_product    = wc_get_product( $mnm_item_id );
					
					$mnm_product_id = $mnm_product->is_type( 'variation ' ) ? $mnm_product->get_parent_id( 'edit' ) : $mnm_product->get_id( 'edit' );

					if ( ! $mnm_product ) {
						return $value;
					}

					$mnm_product_sku = $mnm_product->get_sku( 'edit' );

					// Refer to exported products by their SKU, if present.
					$mnm_item_data[ 'product_id' ] = $mnm_product_sku ? $mnm_product_sku : 'id:' . $mnm_product_id;

					$data[ $mnm_item_id ] = $mnm_item_data;
				}

				$value = json_encode( $data );

			}
		}

		return $value;
	}

	/**
	 * "Min Container Quantity" column content.
	 *
	 * @param  mixed       $value
	 * @param  WC_Product  $product
	 * @return mixed       $value
	 */
	public static function export_min_container_size( $value, $product ) {

		if ( $product->is_type( 'mix-and-match' ) ) {
			$value = $product->get_min_container_size( 'edit' );
		}

		return $value;
	}

	/**
	 * "max Container Quantity" column content.
	 *
	 * @param  mixed       $value
	 * @param  WC_Product  $product
	 * @return mixed       $value
	 */
	public static function export_max_container_size( $value, $product ) {

		if ( $product->is_type( 'mix-and-match' ) ) {
			$value = $product->get_max_container_size( 'edit' );
		}

		return $value;
	}

	/**
	 * "Container priced per product" column content.
	 *
	 * @param  mixed       $value
	 * @param  WC_Product  $product
	 * @return mixed       $value
	 */
	public static function export_priced_per_product( $value, $product ) {

		if ( $product->is_type( 'mix-and-match' ) ) {
			$value = $product->is_priced_per_product( 'edit' ) ? 1 : 0;
		}

		return $value;
	}

	/**
	 * "Container shipped per product" column content.
	 *
	 * @param  mixed       $value
	 * @param  WC_Product  $product
	 * @return mixed       $value
	 */
	public static function export_shipped_per_product( $value, $product ) {

		if ( $product->is_type( 'mix-and-match' ) ) {
			$value = $product->is_shipped_per_product( 'edit' ) ? 1 : 0;
		}

		return $value;
	}
}

WC_Donations_Product_Export::init();
