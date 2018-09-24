<?php
/**
 * WC_Donations_Admin_Ajax class
 *
 * @author   Kathy Darling
 * @package  WooCommerce Donations
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin AJAX meta-box handlers.
 *
 * @class     WC_Donations_Admin_Ajax
 * @version   1.0.0
 */
class WC_Donations_Admin_Ajax {

	/**
	 * Hook in.
	 */
	public static function init() {

		// Load donation variations.
		add_action( 'wp_ajax_woocommerce_load_donation_variations', array( __CLASS__, 'load_donation_variations' ) );
		
		// Ajax add donation variation.
		add_action( 'wp_ajax_woocommerce_add_donation_variation', array( __CLASS__, 'add_donation_variation' ) );
		
		// Ajax remove donation variations.
		add_action( 'wp_ajax_woocommerce_remove_donation_variations', array( __CLASS__, 'remove_donation_variations' ) );
	
		
	}

	/**
	 * Load variations via AJAX.
	 */
	public static function load_donation_variations() {
		
		ob_start();

		check_ajax_referer( 'load-donation-variations', 'security' );

		if ( ! current_user_can( 'edit_products' ) || empty( $_POST['product_id'] ) ) {
			wp_die( -1 );
		}

		// Set $post global so its available, like within the admin screens
		global $post;

		$loop           = 0;
		$product_id     = absint( $_POST['product_id'] );
		$post           = get_post( $product_id );
		$product_object = wc_get_product( $product_id );

		$variations     = wc_get_products(
			array(
				'status'  => array( 'private', 'publish' ),
				'type'    => 'variation',
				'parent'  => $product_id,
				'limit'   => -1,
				'orderby' => array(
					'menu_order' => 'ASC',
					'ID'         => 'DESC',
				),
				'return'  => 'objects',
			)
		);

		if ( $variations ) {
			foreach ( $variations as $variation ) {
				include 'meta-boxes/views/html-donation-variation-admin.php';
				$loop++;
			}
		}
		wp_die();
	}
	
	/**
	 * Handles adding variataions via ajax.
	 */
	public static function add_donation_variation() {
		
		check_ajax_referer( 'add-donation-variation', 'security' );

		if ( ! current_user_can( 'edit_products' ) ) {
			wp_die( -1 );
		}

		global $post; // Set $post global so its available, like within the admin screens.

		$product_id       = intval( $_POST['product_id'] );
		$post             = get_post( $product_id );
		$loop             = intval( $_POST['loop'] );
		$amount = wc_clean( wp_unslash( $_POST['amount'] ) );
		
		$variation= new WC_Product_Variation();
		$variation->set_parent_id( $product_id );
		$variation->set_attributes( array( '_wcd_amount' => $amount ) );
		$variation->set_regular_price( $amount );
		$variation_id = $variation->save();

		include 'meta-boxes/views/html-donation-variation-admin.php';
		wp_die();
	
	}
	
	/**
	 * Handles removing variations via ajax.
	 */
	public static function remove_donation_variations() { error_log('arrive at callback');

		check_ajax_referer( 'delete-donation-variations', 'security' );

		if ( current_user_can( 'edit_products' ) ) {
			$variation_ids = (array) $_POST['variation_ids'];

			foreach ( $variation_ids as $variation_id ) {
				if ( 'product_variation' === get_post_type( $variation_id ) ) {
					$variation = wc_get_product( $variation_id );
					$variation->delete( true );
				}
			}
		}

		wp_die( -1 );
		
	}
	
	
}

WC_Donations_Admin_Ajax::init();
