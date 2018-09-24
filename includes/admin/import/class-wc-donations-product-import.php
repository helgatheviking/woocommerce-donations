<?php
/**
 * Product Import Class
 *
 * @author   Kathy Darling
 * @category Admin
 * @package  WooCommerce Donations/Admin/Import
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Donations_Product_Import Class.
 *
 * Add support for Donations to WooCommerce product import.
 */
class WC_Donations_Product_Import {

	/**
	 * Hook in.
	 */
	public static function init() {

		// Map custom column titles.
		add_filter( 'woocommerce_csv_product_import_mapping_options', array( __CLASS__, 'map_columns' ) );
		add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array( __CLASS__, 'add_columns_to_mapping_screen' ) );

		// Parse MnM items.
		add_filter( 'woocommerce_product_importer_parsed_data', array( __CLASS__, 'parse_mnm_items' ), 10, 2 );

		// Set MnM-type props.
		add_filter( 'woocommerce_product_import_pre_insert_product_object', array( __CLASS__, 'set_mnm_props' ), 10, 2 );
	}

	/**
	 * Register the 'Custom Column' column in the importer.
	 *
	 * @param  array  $options
	 * @return array  $options
	 */
	public static function map_columns( $options ) {

		$options[ 'WC_Donations_contents' ]  		           = __( 'MnM Contents (JSON-encoded)', 'wc-donations' );
		$options[ 'WC_Donations_min_container_size' ]        = __( 'MnM Minimum Container Size', 'wc-donations' );
		$options[ 'WC_Donations_max_container_size' ]        = __( 'MnM Maximum Container Size', 'wc-donations' );
		$options[ 'WC_Donations_priced_per_product' ]        = __( 'MnM Per-Item Pricing', 'wc-donations' );
		$options[ 'WC_Donations_shipped_per_product' ] 	   = __( 'MnM Per-Item Shipping', 'wc-donations' );

		return apply_filters( 'woocommerce_mnm_csv_product_import_mapping_options', $options );
	}

	/**
	 * Add automatic mapping support for custom columns.
	 *
	 * @param  array  $columns
	 * @return array  $columns
	 */
	public static function add_columns_to_mapping_screen( $columns ) {

		$columns[ __( 'MnM Contents (JSON-encoded)', 'wc-donations' ) ] 	= 'WC_Donations_contents';
		$columns[ __( 'MnM Minimum Container Size', 'wc-donations' ) ]  	= 'WC_Donations_min_container_size';
		$columns[ __( 'MnM Maximum Container Size', 'wc-donations' ) ]    = 'WC_Donations_max_container_size';
		$columns[ __( 'MnM Per-Item Pricing', 'wc-donations' ) ]          = 'WC_Donations_priced_per_product';
		$columns[ __( 'MnM Per-Item Shipping', 'wc-donations' ) ]    		= 'WC_Donations_shipped_per_product';

		// Always add English mappings.
		$columns[ 'MnM Contents (JSON-encoded)' ]	= 'WC_Donations_contents';
		$columns[ 'MnM Minimum Container Size' ]    = 'WC_Donations_min_container_size';
		$columns[ 'MnM Maximum Container Size' ]    = 'WC_Donations_max_container_size';
		$columns[ 'MnM Per-Item Pricing' ]          = 'WC_Donations_priced_per_product';
		$columns[ 'MnM Per-Item Shipping' ]     	= 'WC_Donations_shipped_per_product';

		return apply_filters( 'woocommerce_mnm_csv_product_import_mapping_default_columns', $columns );
	}

	/**
	 * Decode MNM data items and parse relative IDs.
	 *
	 * @param  array                    $parsed_data
	 * @param  WC_Product_CSV_Importer  $importer
	 * @return array
	 */
	public static function parse_mnm_items( $parsed_data, $importer ) {

		if ( ! empty( $parsed_data[ 'WC_Donations_contents' ] ) ) {

			$mnm_data_items = json_decode( $parsed_data[ 'WC_Donations_contents' ], true );

			unset( $parsed_data[ 'WC_Donations_contents' ] );

			if ( is_array( $mnm_data_items ) ) {

				$parsed_data[ 'WC_Donations_contents' ] = array();

				foreach ( $mnm_data_items as $mnm_data_item_key => $mnm_data_item ) {

					$mnm_product_id = $mnm_data_items[ $mnm_data_item_key ][ 'product_id' ];

					$parsed_data[ 'WC_Donations_contents' ][ $mnm_data_item_key ]                 = $mnm_data_item;
					$parsed_data[ 'WC_Donations_contents' ][ $mnm_data_item_key ][ 'product_id' ] = $importer->parse_relative_field( $mnm_product_id );
				}
			}
		}

		return $parsed_data;
	}

	/**
	 * Set bundle-type props.
	 *
	 * @param  array  $parsed_data
	 * @return array
	 */
	public static function set_mnm_props( $product, $data ) {

		if ( is_a( $product, 'WC_Product' ) && $product->is_type( 'mix-and-match' ) ) {

			$mnm_data_items = ! empty( $data[ 'WC_Donations_contents' ] ) ? $data[ 'WC_Donations_contents' ] : array();

			$props = apply_filters( 'woocommerce_mnm_import_set_props', array(
				'min_container_size'    => isset( $data[ 'WC_Donations_min_container_size' ] ) ? intval( $data[ 'WC_Donations_min_container_size' ] ) : 0,
				'max_container_size'    => isset( $data[ 'WC_Donations_max_container_size' ] ) && $data[ 'WC_Donations_max_container_size' ] != '' ? intval( $data[ 'WC_Donations_max_container_size' ] ) : '',
				'contents'      		=> $mnm_data_items,
				'shipped_per_product'	=> isset( $data[ 'WC_Donations_shipped_per_product' ] ) && 1 === intval( $data[ 'WC_Donations_shipped_per_product' ] ) ? 'yes' : 'no',
				'priced_per_product'    => isset( $data[ 'WC_Donations_priced_per_product' ] ) && 1 === intval( $data[ 'WC_Donations_priced_per_product' ] ) ? 'yes' : 'no',
			), $product, $data );

			$product->set_props( $props );
		}

		return $product;
	}
}

WC_Donations_Product_Import::init();
