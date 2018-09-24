<?php
/**
 * Product Class
 *
 * @author   Kathy Darling
 * @category Classes
 * @package  WooCommerce Donations/Classes/Products
 * @since    1.0.0
 * @version  1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Product_Donation Class.
 *
 * The custom product type for WooCommerce.
 *
 * @uses  WC_Product
 */
class WC_Product_Donation extends WC_Product_Variable {

	/**
	 * Array of donations variations that are available.
	 * @var array
	 */
	private $donation_variations;
	
	/**
	 * Layout options data.
	 * @see 'WC_Product_Donation::get_layout_options'.
	 * @var array
	 */
	private static $layout_options_data = null;

	/**
	 *  Define type-specific properties.
	 * @var array
	 */
	protected $extra_data = array(
		'layout'                     => 'default',
		'add_to_cart_form_location'  => 'default'
	);

	/**
	 * __construct function.
	 *
	 * @param  mixed $product
	 */
	public function __construct( $product = 0 ) {
		parent::__construct( $product );
	}


	/*
	|--------------------------------------------------------------------------
	| CRUD Getters.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get internal type.
	 * @return string
	 */
	public function get_type() {
		return 'donation';
	}

	/**
	 * "Form Location" getter.
	 *
	 * @since  1.0.0
	 *
	 * @param  string  $context
	 * @return string
	 */
	public function get_add_to_cart_form_location( $context = 'view' ) {
		return $this->get_prop( 'add_to_cart_form_location', $context );
	}


	/**
	 * "Layout" getter.
	 *
	 * @since  1.0.0
	 *
	 * @param  string  $context
	 * @return string
	 */
	public function get_layout( $context = 'any' ) {
		return $this->get_prop( 'layout', $context );
	}


	/**
	 * Contained product IDs getter.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $context
	 * @return array
	 */
	public function get_contents( $context = 'view' ) {
		return $this->get_prop( 'contents', $context );
	}

	/*
	|--------------------------------------------------------------------------
	| CRUD Setters.
	|--------------------------------------------------------------------------
	*/

	/**
	 * "Form Location" setter.
	 *
	 * @since  1.0.0
	 *
	 * @param  string  $value
	 */
	public function	set_add_to_cart_form_location( $value ) {
		$value = in_array( $value, array_keys( self::get_add_to_cart_form_location_options() ) ) ? $value : 'default';
		return $this->set_prop( 'add_to_cart_form_location', $value );
	}

	/**
	 * "Layout" setter.
	 *
	 * @since  1.0.0
	 *
	 * @param  string  $layout
	 */
	public function set_layout( $layout ) {
		$layout = array_key_exists( $layout, self::get_layout_options() ) ? $layout : 'tabular';
		$this->set_prop( 'layout', $layout );
	}

	/**
	 * Contained product IDs setter.
	 *
	 * @since  1.2.0
	 *
	 * @param  string  $value
	 */
	public function set_contents( $value ) {
		$this->set_prop( 'contents', array_map( 'absint', ( array ) $value ) );
	}

	/*
	|--------------------------------------------------------------------------
	| Other methods.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get an array of available variations for the current product.
	 *
	 * @return array
	 */
	public function get_available_variations() {
		$available_variations = array();

		foreach ( $this->get_children() as $child_id ) {
			$variation = wc_get_product( $child_id );

			// Hide out of stock variations if 'Hide out of stock items from the catalog' is checked.
			if ( ! $variation || ! $variation->exists() || ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && ! $variation->is_in_stock() ) ) {
				continue;
			}

			// Filter 'woocommerce_hide_invisible_variations' to optionally hide invisible variations (disabled variations and variations with empty price).
			if ( apply_filters( 'woocommerce_hide_invisible_variations', true, $this->get_id(), $variation ) && ! $variation->variation_is_visible() ) {
				continue;
			}

			$available_variations[] = $this->get_available_variation( $variation );
		}
		$available_variations = array_values( array_filter( $available_variations ) );

		return $available_variations;
	}
	
	
	/**
	 * Is this a NYP product?
	 * @return bool
	 */
	public function is_nyp() {
		if ( ! isset( $this->is_nyp ) ) {
			$this->is_nyp = WC_Donations_Compatibility::is_nyp( $this );
		}
		return $this->is_nyp;
	}


	/**
	 * The Donation Varitions / Suggested Amounts
	 *
	 * @return WC_Product_Variation[]
	 */
	public function get_donation_variations() {

		if ( ! is_array( $this->donation_variations ) ) {

			$this->donation_variations = array();

			if ( $children = $this->get_children() ) {
				foreach ( $children as $variation_id ) {
					$variation = wc_get_product( $variation_id );
					
					// Hide any variations that got borked or aren't purchasable.
					if ( ! $variation || ! $variation->exists() || ! $variation->is_purchasable() ) {
						continue;
					}
		
					$this->donation_variations[ $variation_id ] = $variation;
					
				}
			}

		}

		/**
		 * Donation Variations.
		 *
		 * @param  array			  $children
		 * @param  obj WC_Product 	  $this
	 	*/
		return apply_filters( 'wc_donation_get_donation_variations', $this->donation_variations, $this );
	}




	/**
	 * Returns whether or not the product has any variations.
	 *
	 * @return bool
	 */
	public function has_children() {
		return sizeof( $this->get_children() ) ? true : false;
	}


	/**
	 * A MnM product must contain children and have a price in static mode only.
	 *
	 * @return bool
	 */
	public function is_purchasable() {

		// TEMP - until we can check for IS NYP or presence of amount options
		return true;

		$is_purchasable = true;

		// Products must exist of course
		if ( ! $this->exists() ) {
			
			$is_purchasable = false;

		// Check the product is published
		} elseif ( $this->get_status() !== 'publish' && ! current_user_can( 'edit_post', $this->get_id() ) ) {

			$is_purchasable = false;

		} elseif ( false === $this->has_children() ) {

			$is_purchasable = false;
		}

		/**
		 * WooCommerce product is purchasable.
		 *
		 * @param  str $is_purchasable
		 * @param  obj WC_Product_Donation $this
	 	 */
		return apply_filters( 'woocommerce_is_purchasable', $is_purchasable, $this );
	}


    /**
	 * Returns whether or not the product container has any available child items.
	 *
	 * @return bool
	 */
	public function has_available_children() {
		return sizeof( $this->get_available_children() ) ? true : false;
	}

	/**
	 * Get the add to cart button text
	 *
	 * @return string
	 */
	public function add_to_cart_text() {

		$text = __( 'Read More', 'wc-donations' );

		if ( $this->is_purchasable() ) {
			$text =  __( 'Select amount', 'wc-donations' );
		}

		/**
		 * Add to cart text.
		 *
		 * @param  str $text
		 * @param  obj WC_Product_Donation $this
	 	 */
		return apply_filters( 'wc_donation_add_to_cart_text', $text, $this );
	}

	/**
	 * Get the add to cart button text for the single page.
	 *
	 * @return string
	 */
	public function single_add_to_cart_text() {
		return apply_filters( 'woocommerce_product_single_add_to_cart_text', __( 'Donate', 'wc-donations' ), $this );
	}

	/**
	 * Returns the price in html format.
	 *
	 * @param string $price Price (default: '').
	 * @return string
	 */
	public function get_price_html( $price = '' ) {
		// @todo: If only a SINGLE variation, display the price of that variation.
		return apply_filters( 'woocommerce_get_price_html', '', $this );
	}
	
	/**
	 * Returns whether or not the product has additional options that need
	 * selecting before adding to cart.
	 *
	 * @since  1.0.0
	 * @return boolean
	 */
	public function has_options() {
		return true;
	}
	
	/*
	|--------------------------------------------------------------------------
	| Static methods.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Supported "Form Location" options.
	 *
	 * @since  1.0.0
	 *
	 * @return array
	 */
	public static function get_add_to_cart_form_location_options() {

		$options = array(
			'default'      => array(
				'title'       => __( 'Default', 'wc-donations' ),
				'description' => __( 'The add-to-cart form is displayed inside the single-product summary.', 'wc-donations' )
			),
			'after_summary' => array(
				'title'       => __( 'After summary', 'wc-donations' ),
				'description' => __( 'The add-to-cart form is displayed after the single-product summary. Usually allocates the entire page width for displaying form content. Note that some themes may not support this option.', 'wc-donations' )
			)
		);

		return apply_filters( 'wc_donation_add_to_cart_form_location_options', $options );
	}

	/**
	 * Supported layouts.
	 *
	 * @since  1.0.0
	 *
	 * @return array
	 */
	public static function get_layout_options() {
		if ( is_null( self::$layout_options_data ) ) {
			self::$layout_options_data = apply_filters( 'wc_donation_supported_layouts', array(
				'default' => __( 'List', 'wc-donations' ),
				'grid' => __( 'Buttons', 'wc-donations' )
			) );
		}
		return self::$layout_options_data;
	}

}
