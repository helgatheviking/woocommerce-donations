<?php
/**
 * WooCommerce Core Compatibilty
 *
 * @author   Kathy Darling
 * @category Classes
 * @package  WooCommerce Donations/Compatibility
 * @since    1.0.0
 * @version  1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Donations_Core_Compatibility Class.
 *
 * Wrapper functions for WC core back-compatibility.
 */
class WC_Donations_Core_Compatibility {

	/**
	 * Cache 'gte' >= comparison results.
	 * 
	 * @var array
	 */
	private static $is_wc_version_gte = array();

	/**
	 * Cache 'gt' comparison results.
	 * 
	 * @var array
	 */
	private static $is_wc_version_gt = array();

	/**
	 * Helper method to get the version of the currently installed WooCommerce.
	 *
	 * @return string
	 */
	private static function get_wc_version() {
		return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than or equal to $version.
	 *
	 * @param  string  $version the version to compare
	 * @return bool true if the installed version of WooCommerce is > $version
	 */
	public static function is_wc_version_gte( $version ) {
		if ( ! isset( self::$is_wc_version_gte[ $version ] ) ) {
			self::$is_wc_version_gte[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>=' );
		}
		return self::$is_wc_version_gte[ $version ];
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than $version.
	 *
	 * @param  string  $version the version to compare
	 * @return bool true if the installed version of WooCommerce is > $version
	 */
	public static function is_wc_version_gt( $version ) {
		if ( ! isset( self::$is_wc_version_gt[ $version ] ) ) {
			self::$is_wc_version_gt[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>' );
		}
		return self::$is_wc_version_gt[ $version ];
	}

}