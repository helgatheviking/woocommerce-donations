<?php
/**
 * WooCommerce Donations template functions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/*--------------------------------------------------------*/
/*  Donations single product template functions           */
/*--------------------------------------------------------*/

/**
 * Front-end styles.
 */
function wc_donations_front_end_styles(){
	wp_register_style( 'wc-donations-style', WC_Donations()->get_plugin_url() . '/assets/css/frontend/wc-donations-frontend.css', false, time() );
	//wp_style_add_data( 'wc-donations-style', 'rtl', 'replace' );

	wp_enqueue_style( 'wc-donations-style' );
}
		
/**
 * Add-to-cart template for Donations. Handles the 'Form location > After summary' case.
 */
function wc_donation_template_add_to_cart() {

	global $product;

	if ( doing_action( 'woocommerce_single_product_summary' ) ) {
		if ( 'after_summary' === $product->get_add_to_cart_form_location() ) {
			return;
		}
	}

	wp_enqueue_style( 'wc-donation-css' );

	$donation_variations = $product->get_donation_variations();
	
	$form_classes  = array( 'layout_' . $product->get_layout() );

	if ( ! empty( $donation_variations ) ) {
		wc_get_template( 'single-product/add-to-cart/donation.php', array(
			'donation_variations'	=> $donation_variations,
			'product'           => $product,
			'product_id'        => $product->get_id(),
			'classes'           => implode( ' ', $form_classes )
		), false, WC_Donations()->get_plugin_path() . '/templates/' );
	}
}


/**
 * Opens the donation variations wrapper.
 *
 * @param obj WC_Product_Donation $product the parent container
 */
function wc_donations_template_variations_wrapper_open( $product ) {

	if( $product->has_children() ) { 

		// Get the columns.
		$columns = intval( apply_filters( 'woocommerce_donations_layout_columns', 3, $product ) ); 

		// Reset the loop.
		wc_set_loop_prop( 'loop', 0 );
		wc_set_loop_prop( 'columns', $columns ); 
		
		$classes = array( 'donation-amounts', 'columns-' . $columns );

		wc_get_template(
			'single-product/donation/donation-variations-wrapper-open.php',
			array( 
				'classes'	  => $classes
			),
			'',
			WC_Donations()->get_plugin_path() . '/templates/'
		);

	}

}

/**
 * Echo choose markup.
 *
 * @param obj $product WC_Product_Donation of parent product
 */
function wc_donations_template_variations_chose( $product ) {

	if( $product->has_children() ) { 

		wc_get_template(
			'single-product/donation/donation-variations-choose.php',
			array(),
			'',
			WC_Donations()->get_plugin_path() . '/templates/'
		);

	}

}

/**
 * Load the donation variation template.
 *
 * @param obj WC_Product_Donation_Variation $variation
 * @param obj WC_Product_Donation $product
 */
function wc_donations_template_donation_variation( $variation, $product ) {

	wc_get_template(
		'single-product/donation/donation-variation.php',
		array( 
			'product'	=> $product,
			'variation'	=> $variation
		),
		'',
		WC_Donations()->get_plugin_path() . '/templates/'
	);

}

/**
 * Echo ending markup if neccessary.
 *
 * @param obj $product WC_Product_Donation of parent product
 */
function wc_donations_template_variations_wrapper_close( $product ) {

	if( $product->has_children() ) { 

		wc_get_template(
			'single-product/donation/donation-variations-wrapper-close.php',
			array(),
			'',
			WC_Donations()->get_plugin_path() . '/templates/'
		);

	}

}



/*--------------------------------------------------------*/
/*  Custom Add to Cart Handler    */
/*--------------------------------------------------------*/

/**
 * Add-to-cart handler for Donations.
 */
function wc_donation_add_to_cart_handler( $url ) {

	try {
		$product_id         = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_REQUEST['add-to-cart'] ) );
		$variation_id       = empty( $_REQUEST['variation_id'] ) ? '' : absint( wp_unslash( $_REQUEST['variation_id'] ) );
		$quantity           = empty( $_REQUEST['quantity'] ) ? 1 : absint( wp_unslash( $_REQUEST['quantity'] ) );
		$variations         = array();
		$adding_to_cart     = wc_get_product( $product_id );

		if ( ! $adding_to_cart ) {
			$was_added_to_cart = false;
		}

		// If the $product_id was in fact a variation ID, update the variables.
		if ( $adding_to_cart->is_type( 'variation' ) ) {
			$variation_id   = $product_id;
			$product_id     = $adding_to_cart->get_parent_id();
			$adding_to_cart = wc_get_product( $product_id );

			if ( ! $adding_to_cart ) {
				$was_added_to_cart = false;
			}
		}

		// Do we have a variation ID?
		if ( empty( $variation_id ) ) {
			throw new Exception( __( 'Please choose an amount&hellip;', 'wc-donations' ) );
		}
		
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations );

		if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations ) ) {
			wc_add_to_cart_message( array( $product_id => $quantity ), true );
			$was_added_to_cart = true;
		}

	} catch ( Exception $e ) {
		wc_add_notice( $e->getMessage(), 'error' );
		$was_added_to_cart = false;
	}

	// If we added the product to the cart we can now optionally do a redirect.
	if ( $was_added_to_cart && 0 === wc_notice_count( 'error' ) ) {
		if ( $url = apply_filters( 'woocommerce_add_to_cart_redirect', $url ) ) {
			wp_safe_redirect( $url );
			exit;
		} elseif ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
			wp_safe_redirect( wc_get_cart_url() );
			exit;
		}
	}
	
}