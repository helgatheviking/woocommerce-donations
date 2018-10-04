<?php
/**
 * Admin Class
 *
 * @author   Kathy Darling
 * @category Admin
 * @package  WooCommerce Donations/Admin
 * @since    1.0.0
 * @version  1.3.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Donations_Admin Class.
 *
 * Loads admin tabs, scripts and adds related hooks / filters.
 */
class WC_Donations_Admin {

	/**
	 * Bootstraps the class and hooks required.
	 */
	public static function init() {

		add_action( 'admin_init', array( __CLASS__, 'includes' ) );

		// Add a message in the WP Privacy Policy Guide page.
		add_action( 'admin_init', array( __CLASS__, 'add_privacy_policy_guide_content' ) );

		// Admin jquery.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ) );

		// Template override scan path.
		add_filter( 'woocommerce_template_overrides_scan_paths', array( __CLASS__, 'template_scan_path' ) );

		// Show outdated templates in the system status.
		add_action( 'woocommerce_system_status_report', array( __CLASS__ , 'render_system_status_items' ) );

		// Add links.
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );

	}

	/**
	 * Admin init.
	 */
	public static function includes() {
		
		// AJAX.
		require_once( 'class-wc-donations-admin-ajax.php' );

		// Product Import/Export.
		require_once( 'export/class-wc-donations-product-export.php' );
		require_once( 'import/class-wc-donations-product-import.php' );

		// Metaboxes.
		require_once( 'meta-boxes/class-wc-donations-meta-box-product-data.php' );

	}

	/**
	 * Add a message in the WP Privacy Policy Guide page.
	 *
	 * @since  1.3.3
	 */
	public static function add_privacy_policy_guide_content() {
		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
			wp_add_privacy_policy_content( __( 'WooCommerce Donations', 'wc-donations' ), self::get_privacy_policy_guide_message() );
		}
	}

	/**
	 * Message to add in the WP Privacy Policy Guide page.
	 *
	 * @since  1.3.3
	 *
	 * @return string
	 */
	protected static function get_privacy_policy_guide_message() {

		$content = '
			<div contenteditable="false">' .
				'<p class="wp-policy-help">' .
					__( 'Donations does not collect, store or share any personal data.', 'wc-donations' ) .
				'</p>' .
			'</div>';

		return $content;
	}

	/**
	 * Load the product metabox script.
	 */
	public static function admin_scripts() {
		
		global $post;

		// Get admin screen id.
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		$version = WC_Donations::VERSION;

		// WooCommerce product admin page.
		if ( 'product' === $screen_id ) {

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'wc-donations-writepanel', WC_Donations()->get_plugin_url() . '/assets/js/admin/wc-donations-write-panel' . $suffix . '.js', array( 'jquery', 'wc-enhanced-select' ), time() );


			$params = array(
					'post_id'                             => isset( $post->ID ) ? $post->ID : '',
					'ajax_url'                            => admin_url( 'admin-ajax.php' ),
					'add_variation_nonce'                 => wp_create_nonce( 'add-donation-variation' ),
					'delete_variations_nonce'             => wp_create_nonce( 'delete-donation-variations' ),
					'load_variations_nonce'               => wp_create_nonce( 'load-donation-variations' ),
					'save_variations_nonce'               => wp_create_nonce( 'save-donation-variations' ),
					'i18n_no_variations_added'            => esc_js( __( 'No amount added', 'woocommerce' ) ),
					'i18n_remove_variation'               => esc_js( __( 'Are you sure you want to remove this amount?', 'woocommerce' ) )

				);

			wp_localize_script( 'wc-donations-writepanel', 'WC_DONATIONS_ADMIN_META_BOX_VARIATIONS', $params );
			
			
			// Metabox styles.
			wp_enqueue_style( 'wc_donations_admin', WC_Donations()->get_plugin_url() . '/assets/css/admin/wc-donations-write-panel.css', array(), time() );
			
			add_action( 'admin_head', array( __CLASS__, 'admin_header' ) );

		}

	}

	/**
	 * Add an icon to Donation product data tab
	 */
	public static function admin_header() { ?>
		<style>
			#woocommerce-product-data ul.wc-tabs li.donation_options a:before { content: "\f511"; font-family: "Dashicons"; }
	    </style>
	    <?php
	}
	
	/**
	 * Add template overrides for MNM to WooCommerce Tracker.
	 *
	 * @param  array  $paths
	 * @return array
	 */
	public static function template_scan_path( $paths ) {
		$paths[ 'WooCommerce Donations' ] = WC_Donations()->plugin_path() . '/templates/';
		return $paths;
	}

	/**
	 * Renders the MNM information in the WC status page
	 * @props to ProsPress
	 */
	public static function render_system_status_items() {
		$debug_data   = array();

		$theme_overrides = self::get_theme_overrides();
		$debug_data['wc_donations_theme_overrides'] = array(
			'name'      => _x( 'Template Overrides', 'label for the system status page', 'wc-donations' ),
			'mark'      => '',
			'mark_icon' => $theme_overrides['has_outdated_templates'] ? 'warning' : 'yes',
			'data'      => $theme_overrides,
		);

		if( $theme_overrides['has_outdated_templates'] ) {
			$debug_data['wc_donations_outdated_templates'] = array(
				'name'      => _x( 'Outdated Templates', 'label for the system status page', 'wc-donations' ),
				'mark'      => 'error',
				'mark_icon' => 'warning',
				'note'    => '<a href="https://docs.woocommerce.com/document/fix-outdated-templates-woocommerce/" target="_blank">' . __( 'Learn how to update', 'wc-donations' ) . '</a>'
			);
		}

		$debug_data = apply_filters( 'wc_donations_system_status', $debug_data );

		include( 'views/html-status.php' );
	}

	/**
	 * Determine which of our files have been overridden by the theme.
	 *
	 * @author Jeremy Pry
	 * @return array Theme override data.
	 */
	private static function get_theme_overrides() {
		$wc_donations_template_dir = WC_Donations()->plugin_path() . '/templates/';
		$wc_template_path = trailingslashit( wc()->template_path() );
		$theme_root       = trailingslashit( get_theme_root() );
		$overridden       = array();
		$outdated         = false;
		$templates        = WC_Admin_Status::scan_template_files( $wc_donations_template_dir );

		foreach ( $templates as $file ) {
			$theme_file = $is_outdated = false;
			$locations  = array(
				get_stylesheet_directory() . "/{$file}",
				get_stylesheet_directory() . "/{$wc_template_path}{$file}",
				get_template_directory() . "/{$file}",
				get_template_directory() . "/{$wc_template_path}{$file}",
			);

			foreach ( $locations as $location ) {
				if ( is_readable( $location ) ) {
					$theme_file = $location;
					break;
				}
			}

			if ( ! empty( $theme_file ) ) {
				$core_version  = WC_Admin_Status::get_file_version( $wc_donations_template_dir . $file );
				$theme_version = WC_Admin_Status::get_file_version( $theme_file );
				if ( $core_version && ( empty( $theme_version ) || version_compare( $theme_version, $core_version, '<' ) ) ) {
					$outdated = $is_outdated = true;
				}
				$overridden[] = array(
					'file'         => str_replace( $theme_root, '', $theme_file ),
					'version'      => $theme_version,
					'core_version' => $core_version,
					'is_outdated'  => $is_outdated,
				);
			}
		}

		return array(
			'has_outdated_templates' => $outdated,
			'overridden_templates'   => $overridden,
		);
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param	mixed  $links
	 * @param	mixed  $file
	 * @return	array
	 */
	public static function plugin_row_meta( $links, $file ) {

		if ( $file == 'woocommerce-donations/wc-donations.php' ) {
			$row_meta = array(
				'docs'    => '<a href="https://docs.woocommerce.com/document/woocommerce-donations/">' . __( 'Documentation', 'wc-donations' ) . '</a>',
				'support' => '<a href="https://woocommerce.com/my-account/tickets/">' . __( 'Support', 'wc-donations' ) . '</a>',
			);

			$links = array_merge( $links, $row_meta );
		}

		return $links;

	}

}
// Launch the admin class.
WC_Donations_Admin::init();
