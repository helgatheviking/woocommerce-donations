<?php
/**
 * Product Data Metabox Class
 *
 * @author   Kathy Darling
 * @category Admin
 * @package  WooCommerce Donations/Admin/Meta Boxes
 * @since    1.2.0
 * @version  1.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Donations_Admin_Meta_Boxes Class.
 *
 * Adds and save product meta.
 */
class WC_Donations_Admin_Meta_Boxes {

	/**
	 * Bootstraps the class and hooks required.
	 */
	public static function init() {

		// Creates the MnM panel tab.
		add_action( 'woocommerce_product_data_tabs', array( __CLASS__, 'product_data_tab' ) );

		// Adds the meta options.
		add_action( 'wc_donation_product_data', array( __CLASS__, 'donation_variations' ), 20, 2 );

		// Creates the panel for selecting product options.
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'product_data_panel' ) );

		// Processes and saves the necessary post metas from the selections made above.
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'process_wc_donation_data' ), 20 );
	}


	/**
	 * Adds the MnM Product write panel tabs.
	 *
	 * @param  array $tabs
	 * @return array
	 */
	public static function product_data_tab( $tabs ) {

		global $post, $product_object, $donation_product_object;

		/*
		 * Create a global product object to use for populating fields.
		 */
		$post_id = $post->ID;

		if ( empty( $product_object ) || false === $product_object->is_type( 'donation' ) ) {
			$donation_product_object = $post_id ? new WC_Product_Donation( $post_id ) : new WC_Product_Donation();
		} else {
			$donation_product_object = $product_object;
		}

		$tabs[ 'shipping' ][ 'class' ][] = 'hide_if_donation';

		$tabs[ 'donation' ] = array(
			'label'  => __( 'Donation', 'wc-donations' ),
			'target' => 'donation_product_data',
			'class'  => array( 'show_if_donation', 'wc_donation_product_tab', 'wc_donation_product_data' )
		);

		return $tabs;
	}


	/**
	 * Write panel.
	 */
	public static function product_data_panel() {
		global $post;

		?>
		<div id="donation_product_data" class="wc_donation_panel panel wc-metaboxes-wrapper hidden">

			<?php

			$post_id = $post->ID;

			$donation_product_object = $post_id ? new WC_Product_Donation( $post_id ) : new WC_Product_Donation();

			/**
			 * Add Donation Product Options.
			 *
			 * @param int $post_id
			 *
			 * @see self::donation_variations   - 20
			 */
			do_action( 'wc_donation_product_data', $post->ID, $donation_product_object );
			?>

		</div>

	<?php

	}


	/**
	 * Render Amount options on 'wc_donation_product_data'.
	 *
	 * @param  int $post_id
	 * @param  WC_Donations  $donation_product_object
	 */
	public static function donation_variations( $post_id, $donation_product_object ) { 

		global $wpdb;

		$donation_variations_count       = absint( apply_filters( 'woocommerce_admin_meta_boxes_variations_count', $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_parent = %d AND post_type = 'product_variation' AND post_status IN ('publish', 'private')", $post_id ) ), $post_id ) );

		include( 'views/html-donation-variations.php' );

	}

	/**
	 * Process, verify and save product data
	 *
	 * @param  WC_Product  $product
	 */
	public static function process_wc_donation_data( $product ) {

		if ( $product->is_type( 'donation' ) ) {

			$props = array(
				'layout'                    => 'default',
				'add_to_cart_form_location' => 'default',
			);

			// Layout.
			if ( ! empty( $_POST[ '_wc_donation_layout_style' ] ) ) {

				$layout = wc_clean( $_POST[ '_wc_donation_layout_style' ] );

				if ( in_array( $layout, array_keys( WC_Product_Donation::get_layout_options() ) ) ) {
					$props[ 'layout' ] = $layout;
				}

			}

			// Add to cart form location.
			if ( ! empty( $_POST[ '_wc_donation_add_to_cart_form_location' ] ) ) {

				$form_location = wc_clean( $_POST[ '_wc_donation_add_to_cart_form_location' ] );

				if ( in_array( $form_location, array_keys( WC_Product_Donation::get_add_to_cart_form_location_options() ) ) ) {
					$props[ 'add_to_cart_form_location' ] = $form_location;
				}
			}


			$product->get_data_store()->sync_variation_names( $product, wc_clean( $_POST['original_post_title'] ), wc_clean( $_POST['post_title'] ) );

			do_action( 'woocommerce_admin_process_donation_product_object', $product );

		}
	}

	/**
	 * Save meta box data.
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public static function save_donation_variations( $post_id, $post ) {
		if ( isset( $_POST['donation_post_id'] ) ) {
			$parent = wc_get_product( $post_id );

			$max_loop   = max( array_keys( $_POST['donation_post_id'] ) );
			$data_store = $parent->get_data_store();
			$data_store->sort_all_product_variations( $parent->get_id() );

			for ( $i = 0; $i <= $max_loop; $i ++ ) {

				if ( ! isset( $_POST['donation_post_id'][ $i ] ) ) {
					continue;
				}
				$variation_id = absint( $_POST['donation_post_id'][ $i ] );
				$variation    = new WC_Product_Variation( $variation_id );
				$stock        = null;

				$errors = $variation->set_props(
					array(
						'status'            => 'publish',
						'menu_order'        => wc_clean( $_POST['variation_menu_order'][ $i ] ),
						'regular_price'     => wc_clean( $_POST['variable_regular_price'][ $i ] )
					)
				);

				if ( is_wp_error( $errors ) ) {
					WC_Admin_Meta_Boxes::add_error( $errors->get_error_message() );
				}

				$variation->save();

				do_action( 'woocommerce_save_donation_variation', $variation_id, $i );
			}
		}

}

// Launch the admin class.
WC_Donations_Admin_Meta_Boxes::init();
