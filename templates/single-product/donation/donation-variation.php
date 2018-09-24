<?php
/**
 * Donation Variations Wrapper
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/donation/donation-variation.php.
 *
 * HOWEVER, on occasion WooCommerce Mix and Match will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  Kathy Darling
 * @package WooCommerce Donations/Templates
 * @since   1.0.0
 * @version 1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ){
	exit;
}
?>
<li class="donation-amount">
  	<input type="radio" name="variation_id" id="<?php esc_attr_e( 'donation-' . $product->get_id() . '-' . $variation->get_id() ); ?>" value="<?php esc_attr_e( $variation->get_id() );?>" <?php checked( isset( $_POST['variation_id'] ) && $_POST['variation_id'] == $variation->get_id(), 1 ); ?> />
  	<label for="<?php esc_attr_e( 'donation-' . $product->get_id() . '-' . $variation->get_id() ); ?>">
		<?php  echo $variation->get_price_html(); ?>
	</label>
</li>