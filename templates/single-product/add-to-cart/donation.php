<?php
/**
 * Donation Product Add to Cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/donation.php.
 *
 * HOWEVER, on occasion WooCommerce Donations will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  Kathy Darling
 * @package WooCommerce Donations/Templates
 * @since   1.0.0
 * @version 1.3.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ){
	exit;
}
 
global $product;

/**
 * woocommerce_before_add_to_cart_form hook.
 */
do_action( 'woocommerce_before_add_to_cart_form' ); 
?>

<form class="donation_form cart <?php echo esc_attr( $classes ); ?>" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>">
	
	<?php

	/**
	 * 'woocommerce_before_donation_variations' action.
	 *
	 * @param WC_Donation_Product $product
	 *
	 * @hooked wc_donations_template_variations_wrapper_open - 0
	 */
	do_action( 'woocommerce_before_donation_variations', $product );
	
	foreach ( $donation_variations as $variation ) : ?>
	
	<?php

		/**
		 * 'woocommerce_mnm_item_details' action.
		 *
		 * @param WC_Donation_Product_Variation $variation
		 *
		 * @hooked wc_donations_template_donation_variation 				-  20
		 */
		do_action( 'woocommerce_donation_variation', $variation, $product );
		
	?>
	
		
	<?php endforeach; ?>
	
	<?php

	/**
	 * 'woocommerce_after_donation_variations' action.
	 *
	 * @param  WC_Donation_Product  $product
	 *
	 * @hooked wc_donations_template_variations_wrapper_close 		- 100
	 */
	do_action( 'woocommerce_after_donation_variations', $product );

?>
	
	<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>

</form>

<?php 
/**
 * woocommerce_after_add_to_cart_form hook.
 */
do_action( 'woocommerce_after_add_to_cart_form' ); 
?>