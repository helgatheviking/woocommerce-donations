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
define('WC_DONATIONS_UPDATING', true );
			if ( ! defined( 'WC_DONATIONS_UPDATING' ) ) {

				// Initialize the child content.
				$wc_donation_contents_data = array();

				// Populate with product data.
				if ( isset( $_POST[ 'wc_donation_allowed_contents' ] ) && ! empty( $_POST[ 'wc_donation_allowed_contents' ] ) ) {

					$wc_donation_allowed_contents = array_filter( array_map( 'intval', (array) $_POST[ 'wc_donation_allowed_contents' ] ) );

					$unsupported_error = false;

					// Check product types of selected items.
					foreach ( $wc_donation_allowed_contents as $wc_donation_id ) {

						$wc_donation_product = wc_get_product( $wc_donation_id );

						if ( ! in_array( $wc_donation_product->get_type(), WC_Donations_Helpers::get_supported_product_types() ) || ( $wc_donation_product->is_type( 'variation' ) && ! WC_Donations_Core_Compatibility::has_all_attributes_set( $wc_donation_product ) ) ) {
							$unsupported_error = true;
						} else {
							// Product-specific data, such as discounts, or min/max quantities in container may be included later on.
							$wc_donation_contents_data[ $wc_donation_id ][ 'product_id' ] = $wc_donation_product->get_id();
						}
					}

					if ( $unsupported_error ) {
						WC_Admin_Meta_Boxes::add_error( __( 'Mix & Match supports simple products and product variations with all attributes defined. Other product types and partially-defined variations cannot be added to the Mix & Match container.', 'wc-donations' ) );
					}
				}

				// Show a notice if the user hasn't selected any items for the container.
				if ( empty( $wc_donation_contents_data ) ) {
					WC_Admin_Meta_Boxes::add_error( __( 'Please select at least one product to use for this Mix & Match product.', 'wc-donations' ) );
				} else {
					$props['contents'] = $wc_donation_contents_data;
				}

				// Finally, set the properties for saving.
				$product->set_props( $props );

			} else {
				WC_Admin_Meta_Boxes::add_error( __( 'Your changes have not been saved &ndash; please wait for the <strong>WooCommerce Donations Data Update</strong> routine to complete before creating new Mix and Match products or making changes to existing ones.', 'wc-donations' ) );
			}

			do_action( 'woocommerce_admin_process_donation_product_object', $product );

		}
	}
}

// Launch the admin class.
WC_Donations_Admin_Meta_Boxes::init();
