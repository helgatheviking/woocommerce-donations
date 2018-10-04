<?php
/**
 * Donation data variations
 *
 * @package WooCommerce\Admin\Metaboxes\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Donation product options.
 */
 
?>

<div id="wc_donations_metaboxes_wrapper_inner" class="options_group">
	
	<?php if ( ! $donation_variations_count ) : ?>

		<div class="inline notice needs_donation_variation woocommerce-message">
			<p><?php echo wp_kses_post( __( 'You must enable at least one suggested donation amount. Enter the amount in the box below and press enter.', 'wc-donations' ) ); ?></p>
		</div>
	
	<?php endif; ?>

	<div class="toolbar toolbar-variations toolbar-top">
		<div class="add_donation_variation form-field">
			<span class="add_prompt"></span>
			<input type="text" class="short wc_input_price" id="donation_amount" name="donation_amount" placeholder="<?php _e( 'Add a donation amount&hellip;', 'wc-donations' ); ?>" />

			<?php echo wc_help_tip( __( 'Enter a donation amount without any currency symbols and press enter.', 'wc-donations' ) ); ?>
		</div>

		<div class="controls hidden">
			<span class="expand-close">
				<a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'wc-donations' ); ?></a>
				<a href="#" class="close_all"><?php esc_html_e( 'Close', 'wc-donations' ); ?></a>
			</span>
		</div>
		<div class="clear"></div>
	</div>

	<div class="wc_donation_variations wc-metaboxes" data-total-variations="<?php echo esc_attr( $donation_variations_count ); ?>" data-edited="false"></div>

	<div class="toolbar hidden">
		<button type="button" class="button-primary save-donation-variation-changes" disabled="disabled"><?php esc_html_e( 'Save changes', 'wc-donations' ); ?></button>
		<button type="button" class="button cancel-donation-variation-changes" disabled="disabled"><?php esc_html_e( 'Cancel', 'wc-donations' ); ?></button>

		<div class="controls">
			<span class="expand-close">
				<a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'wc-donations' ); ?></a> 
				<a href="#" class="close_all"><?php esc_html_e( 'Close', 'wc-donations' ); ?></a>
			</span>
		</div>
		<div class="clear"></div>
	</div>
	</div>
