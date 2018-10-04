<?php
/**
 * Outputs a donation variation for editing.
 *
 * @var WC_Product_Variation $variation
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="woocommerce_variation wc_donation_variation wc-metabox closed">
	<h3>
		<a href="#" class="remove_variation delete" rel="<?php echo esc_attr( $variation->get_id() ); ?>"><?php esc_html_e( 'Remove', 'wc-donations' ); ?></a>
		<div class="handlediv" aria-label="<?php esc_attr_e( 'Click to toggle', 'wc-donations' ); ?>"></div>
		<div class="tips sort" data-tip="<?php esc_attr_e( 'Drag and drop', 'wc-donations' ); ?>"></div>
		<strong><?php echo wc_price( $variation->get_regular_price( 'edit' ) )?></strong> 
		
		<input type="hidden" name="donation_post_id[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation->get_id() ); ?>" />
		<input type="hidden" class="variation_menu_order donation_variation_menu_order" name="donation_variation_menu_order[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation->get_menu_order( 'edit' ) ); ?>" />
	</h3>
	<div class="woocommerce_variable_attributes wc-metabox-content" style="display: none;">
		<div class="data">
			
			<?php if( has_action( 'wc_donation_variation_options' ) ) : ?>

				<p class="form-row form-row-full options">
					<?php do_action( 'wc_donation_variation_options', $loop, $variation ); ?>
				</p>

			<?php endif;?>

			<?php do_action( 'wc_donation_product_before_variable_attributes', $loop, $variation ); ?>

			<div class="variable_pricing">
				<?php
				$label = sprintf(
					/* translators: %s: currency symbol */
					__( 'Amount (%s)', 'wc-donations' ),
					get_woocommerce_currency_symbol()
				);

				woocommerce_wp_text_input(
					array(
						'id'            => "variable_regular_price_{$loop}",
						'name'          => "variable_regular_price[{$loop}]",
						'value'         => wc_format_localized_price( $variation->get_regular_price( 'edit' ) ),
						'label'         => $label,
						'data_type'     => 'price',
						'wrapper_class' => 'form-row form-row-first',
						'placeholder'   => __( 'Suggested donation amount (required)', 'wc-donations' ),
					)
				);


				/**
				 * woocommerce_variation_options_pricing action.
				 *
				 *
				 * @param int     $loop
				 * @param WC_Product_Variation $variation
				 */
				do_action( 'woocommerce_variation_options_pricing', $loop, $variation );
				?>
			</div>

			<div>
				<?php
				woocommerce_wp_textarea_input(
					array(
						'id'            => "variable_description{$loop}",
						'name'          => "variable_description[{$loop}]",
						'value'         => $variation->get_description( 'edit' ),
						'label'         => __( 'Description', 'wc-donations' ),
						'desc_tip'      => true,
						'description'   => __( 'Enter an optional description for this suggested donation.' ),
						'wrapper_class' => 'form-row form-row-full',
					)
				);
				?>
			</div>

			<?php do_action( 'wc_donation_product_after_variable_attributes', $loop, $variation ); ?>
		</div>
	</div>
</div>
