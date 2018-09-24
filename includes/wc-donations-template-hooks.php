<?php
/**
 * WooCommerce Donations template hooks
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Front-end styles.
add_action( 'wp_enqueue_scripts', 'wc_donations_front_end_styles' );

// Single product template for Donations. Form location: Default.
add_action( 'woocommerce_donation_add_to_cart', 'wc_donation_template_add_to_cart' );

// Custom Add to Cart Handler.
add_action( 'woocommerce_add_to_cart_handler_donation', 'wc_donation_add_to_cart_handler' );

// Front End Display.
add_action( 'woocommerce_before_donation_variations', 'wc_donations_template_variations_wrapper_open', 0 );
add_action( 'woocommerce_before_donation_variations', 'wc_donations_template_variations_chose', 10 );
add_action( 'woocommerce_donation_variation', 'wc_donations_template_donation_variation', 20, 2 );
add_action( 'woocommerce_after_donation_variations', 'wc_donations_template_variations_wrapper_close', 100 );
