<?php
/**
 * Subscription Product Variation Class
 *
 * The subscription product variation class extends the WC_Product_Variation product class
 * to create subscription product variations.
 *
 * @class 		WC_Product_Subscription
 * @package		WooCommerce Subscriptions
 * @category	Class
 * @since		1.3
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Product_Donation_Variation extends WC_Product_Variation {

	/**
	 * Create a simple subscription product object.
	 *
	 * @access public
	 * @param mixed $product
	 */
	public function __construct( $product = 0 ) {
		parent::__construct( $product );
	}

	/**
	 * Get internal type.
	 *
	 * @return string
	 */
	public function get_type() {
		return 'donation_variation';
	}

	/**
	 * Get variation price HTML. Prices are not inherited from parents.
	 *
	 * @return string containing the formatted price
	 */
	public function get_price_html( $price = '' ) {

		$price = parent::get_price_html( $price );

		return $price;
	}

	/**
	 * Get the add to cart button text
	 *
	 * @access public
	 * @return string
	 */
	public function add_to_cart_text() {

		if ( $this->is_purchasable() ) {
			$text = __( 'Donate Now', 'wc-donations' );
		} else {
			$text = parent::add_to_cart_text(); // translated "Read More"
		}

		return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
	}

	/**
	 * Get the add to cart button text for the single page
	 *
	 * @access public
	 * @return string
	 */
	public function single_add_to_cart_text() {
		return apply_filters( 'woocommerce_product_single_add_to_cart_text', self::add_to_cart_text(), $this );
	}

	/**
	 * Checks if the variable product this variation belongs to is purchasable.
	 *
	 * @access public
	 * @return bool
	 */
	public function is_purchasable() {
		return apply_filters( 'wc_donation_variation_is_purchasable', parent::is_purchasable() && '' !== $this->get_price(), $this );
	}

	/**
	 * Checks the product type to see if it is either this product's type or the parent's
	 * product type.
	 *
	 * @access public
	 * @param mixed $type Array or string of types
	 * @return bool
	 */
	public function is_type( $type ) {
		if ( 'variation' == $type || ( is_array( $type ) && in_array( 'variation', $type ) ) ) {
			return true;
		} else {
			return parent::is_type( $type );
		}

		/// ???????
	}

}
