/* global wp, WC_DONATIONS_ADMIN_META_BOX_VARIATIONS, woocommerce_admin, accounting */
jQuery( function( $ ) {
    'use strict';

	/**
	 * Variations actions
	 */
	var wc_meta_boxes_donation_variations_actions = {

		/**
		 * Variation wrapper object
		 *
		 * @type {Object}
		 */
		donations_wrapper: null,

		/**
		 * Initialize variations actions
		 */
		init: function() { 

			this.donations_wrapper = $( '#donation_product_data' ).find( '.wc_donation_variations' );

			$( '#woocommerce-product-data' ).on( 'wc_donation_variations_loaded', this.variations_loaded );
			$( document.body ).on( 'wc_donation_variation_added', this.variation_added );
			$( document.body ).on( 'wc_donation_variation_removed', this.variation_removed );
				
		},
		
		
		/**
		 * Run actions when variations are loaded
		 *
		 * @param {Object} event
		 * @param {Int} needsUpdate
		 */
		variations_loaded: function( event, needsUpdate ) {
			needsUpdate = needsUpdate || false;

			var $product_data = $( '#woocommerce-product-data' );

			if ( ! needsUpdate ) {

				// Remove variation-needs-update classes.
				$( '.wc_donation_variations .variation-needs-update', $product_data ).removeClass( 'variation-needs-update' );

				// Disable cancel and save buttons.
				$( 'button.cancel-donation-variation-changes, button.save-donation-variation-changes', $product_data ).attr( 'disabled', 'disabled' );
			}	
		
			// Allow sorting.
			$( '.wc_donation_variations', $product_data ).sortable({
				items:                '.wc_donation_variation',
				cursor:               'move',
				axis:                 'y',
				handle:               '.sort',
				scrollSensitivity:    40,
				forcePlaceholderSize: true,
				helper:               'clone',
				opacity:              0.65,
				stop:                 function() {
				    wc_meta_boxes_donation_variations_actions.variation_row_indexes();
				}
			});

			// Init TipTip
			$( '.wc_donation_variations .tips, .wc_donation_variations .help_tip, .wc_donation_variations .woocommerce-help-tip', $product_data ).tipTip({
				'attribute': 'data-tip',
				'fadeIn':    50,
				'fadeOut':   50,
				'delay':     200
			});

		},

		/**
		 * Run actions when added a variation
		 */
		variation_added: function( event, success ) {
			success = success || false;

			var current_qty = this.donations_wrapper.data( 'total-variations' );

			if( success ) {
				this.donations_wrapper.data( 'total-variations', current_qty + 1 );
			}
			
			// toggle toolbars.
		},

		/**
		 * Run actions when a variation is removed
		 */
		variation_added: function( event, success ) {
			success = success || false;

			var current_qty = this.donations_wrapper.data( 'total-variations' );

			if( success ) {
				this.donations_wrapper.data( 'total-variations', current_qty - 1 );
			}
			
			// toggle toolbars.
		},

		/**
		 * Set menu order
		 */
		variation_row_indexes: function() {		
			$( '.wc_donation_variations .wc_donation_variation' ).each( function ( index, el ) {
				$( '.variation_menu_order', el ).val( parseInt( $( el ).index( '.wc_donation_variations .wc_donation_variation' ), 10 ) ).change();
			});
		}
		
	};
	
	/**
	 * Product variations metabox ajax methods
	 */
	var wc_meta_boxes_donation_variations_ajax = {

		/**
		 * Initialize variations ajax methods
		 */
		init: function() {
			$( 'li.donation_tab a' ).on( 'click', this.initial_load );
			
			$( '#donation_product_data' )
				.on( 'keypress', '#donation_amount', this.enter_amount )
				.on( 'click', 'button.save-donation-variation-changes', this.save_variations )
				.on( 'click', 'button.cancel-donation-variation-changes', this.cancel_variations )
				.on( 'click', '.remove_variation', this.remove_variation );

			$( document.body )
				.on( 'change', '#donation_product_data .wc_donation_variations :input', this.input_changed );

			$( 'form#post' ).on( 'submit', this.save_on_submit );
		},

		/**
		 * Check if have some changes before leave the page
		 *
		 * @return {Bool}
		 */
		check_for_changes: function() {
			var need_update = $( '#donation_product_data' ).find( '.wc_donation_variations .variation-needs-update' );

			if ( 0 < need_update.length ) {
				if ( window.confirm( woocommerce_admin_meta_boxes_variations.i18n_edited_variations ) ) {
					wc_meta_boxes_donation_variations_ajax.save_changes();
				} else {
					need_update.removeClass( 'variation-needs-update' );
					return false;
				}
			}

			return true;
		},

		/**
		 * Block edit screen
		 */
		block: function() {
			$( '#woocommerce-product-data' ).block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
		},

		/**
		 * Unblock edit screen
		 */
		unblock: function() {
			$( '#woocommerce-product-data' ).unblock();
		},
		
		/**
		 * Initial load variations
		 *
		 * @return {Bool}
		 */
		initial_load: function() { 

			var total_qty = wc_meta_boxes_donation_variations_actions.donations_wrapper.data( 'total-variations' );

			if ( total_qty != 0 && 0 === $( '#donation_product_data' ).find( '.wc_donation_variations .wc_donation_variation' ).length ) {
				wc_meta_boxes_donation_variations_ajax.load_variations();
			}
		},
		
		/**
		 * Load variations via Ajax
		 */
		load_variations: function() {
			
			wc_meta_boxes_donation_variations_ajax.block();

			$.ajax({
				url: WC_DONATIONS_ADMIN_META_BOX_VARIATIONS.ajax_url,
				data: {
					action:     'woocommerce_load_donation_variations',
					security:   WC_DONATIONS_ADMIN_META_BOX_VARIATIONS.load_variations_nonce,
					product_id: WC_DONATIONS_ADMIN_META_BOX_VARIATIONS.post_id,
				},
				type: 'POST',
				success: function( response ) {
					if( response != '-1' ) {
						wc_meta_boxes_donation_variations_actions.donations_wrapper.empty().append( response );
					}
					$( '#woocommerce-product-data' ).trigger( 'wc_donation_variations_loaded' );
				},
				complete: function ( response ) {
				   wc_meta_boxes_donation_variations_ajax.unblock();
				}
			});
			
			
		},

		/**
		 * Get variations fields and convert to object
		 *
		 * @param  {Object} fields
		 *
		 * @return {Object}
		 */
		get_variations_fields: function( fields ) {
			var data = $( ':input', fields ).serializeJSON();

			$( '.variations-defaults select' ).each( function( index, element ) {
				var select = $( element );
				data[ select.attr( 'name' ) ] = select.val();
			});

			return data;
		},

		/**
		 * Save variations changes
		 *
		 * @param {Function} callback Called once saving is complete
		 */
		save_changes: function( callback ) {
			var wrapper     = $( '#donation_product_data' ).find( '.wc_donation_variations' ),
				need_update = $( '.variation-needs-update', wrapper ),
				data        = {};

			// Save only with products need update.
			if ( 0 < need_update.length ) {
				wc_meta_boxes_donation_variations_ajax.block();

				data                 = wc_meta_boxes_donation_variations_ajax.get_variations_fields( need_update );
				data.action          = 'woocommerce_save_donation_variations';
				data.security        = WC_DONATIONS_ADMIN_META_BOX_VARIATIONS.save_variations_nonce;
				data.product_id      = WC_DONATIONS_ADMIN_META_BOX_VARIATIONS.post_id;
				data['product-type'] = $( '#product-type' ).val();

				$.ajax({
					url: WC_DONATIONS_ADMIN_META_BOX_VARIATIONS.ajax_url,
					data: data,
					type: 'POST',
					success: function( response ) {
						// Allow change page, delete and add new variations
						need_update.removeClass( 'variation-needs-update' );
						$( 'button.cancel-donation-variation-changes, button.save-donation-variation-changes' ).attr( 'disabled', 'disabled' );

						$( '#woocommerce-product-data' ).trigger( 'wc_donation_variations_saved' );

						if ( typeof callback === 'function' ) {
							callback( response );
						}

						wc_meta_boxes_donation_variations_ajax.unblock();
					}
				});
			}
		},

		/**
		 * Save variations
		 *
		 * @return {Bool}
		 */
		save_variations: function() {
			$( '#donation_product_data' ).trigger( 'wc_donation_variations_save_variations_button' );

			wc_meta_boxes_donation_variations_ajax.save_changes( function( error ) {
				var wrapper = $( '#donation_product_data' ).find( '.wc_donation_variations' ),
					current = wrapper.attr( 'data-page' );

				$( '#donation_product_data' ).find( '#woocommerce_errors' ).remove();

				if ( error ) {
					wrapper.before( error );
				}

				$( '.variations-defaults select' ).each( function() {
					$( this ).attr( 'data-current', $( this ).val() );
				});

			});

			return false;
		},

		/**
		 * Save on post form submit
		 */
		save_on_submit: function( e ) {
			var need_update = $( '#donation_product_data' ).find( '.wc_donation_variations .variation-needs-update' );

			if ( 0 < need_update.length ) {
				e.preventDefault();
				$( '#donation_product_data' ).trigger( 'wc_donation_variations_save_variations_on_submit' );
				wc_meta_boxes_donation_variations_ajax.save_changes( wc_meta_boxes_donation_variations_ajax.save_on_submit_done );
			}
		},

		/**
		 * After saved, continue with form submission
		 */
		save_on_submit_done: function() {
			$( 'form#post' ).submit();
		},

		/**
		 * Discart changes.
		 *
		 * @return {Bool}
		 */
		cancel_variations: function() {
			var current = parseInt( $( '#donation_product_data' ).find( '.wc_donation_variations' ).attr( 'data-page' ), 10 );

			$( '#donation_product_data' ).find( '.wc_donation_variations .variation-needs-update' ).removeClass( 'variation-needs-update' );
			$( '.variations-defaults select' ).each( function() {
				$( this ).val( $( this ).attr( 'data-current' ) );
			});

			return false;
		},

		/**
		 * Add variation
		 *
		 * @return {Bool}
		 */
		enter_amount: function(e) {

        	if( e.which === 13 ){
        		
        		e.preventDefault();
        		
        		var val = $(this).val();

	            // Disable textbox to prevent multiple submit.
	            $(this).attr('disabled', 'disabled');
	
	            // Do Stuff, submit, etc..
				wc_meta_boxes_donation_variations_ajax.add_variation( val );
				
	            // Enable the textbox again if needed.
	            $(this).removeAttr('disabled');
	         }
	         
		},
		
		/**
		 * Add variation
		 *
		 * @return {Bool}
		 */
		add_variation: function( amount ) {
			
			amount = amount || 0;
			
			wc_meta_boxes_donation_variations_ajax.block();

			$.ajax({
				url: WC_DONATIONS_ADMIN_META_BOX_VARIATIONS.ajax_url,
				data: {
					action:     'woocommerce_add_donation_variation',
					security:   WC_DONATIONS_ADMIN_META_BOX_VARIATIONS.add_variation_nonce,
					product_id: WC_DONATIONS_ADMIN_META_BOX_VARIATIONS.post_id,
					loop: $( '.wc_donation_variation' ).length,
					amount: amount
				},
				type: 'POST',
				success: function( response ) {
					var variation = $( response );
					variation.addClass( 'variation-needs-update' );
	
					$( '#donation_product_data' ).find( '.wc_donation_variations' ).append( variation );
					$( 'button.cancel-donation-variation-changes, button.save-donation-variation-changes' ).removeAttr( 'disabled' );
					$( '#donation_product_data' ).trigger( 'wc_donation_variation_added', 1 );
		
				},
				complete: function ( response ) {
				   wc_meta_boxes_donation_variations_ajax.unblock();
				}
			});

			return false;
		},

		/**
		 * Remove variation
		 *
		 * @return {Bool}
		 */
		remove_variation: function() {
			wc_meta_boxes_donation_variations_ajax.check_for_changes();

			if ( window.confirm( WC_DONATIONS_ADMIN_META_BOX_VARIATIONS.i18n_remove_variation ) ) {
				var variation_id     = $( this ).attr( 'rel' ),
					variation_ids = [];
					
					var $variation = $(this).closest('.wc_donation_variation');
					
					if ( 0 < variation_id ) {
						variation_ids.push( variation_id );

						wc_meta_boxes_donation_variations_ajax.block();
				
						$.ajax({
							url: WC_DONATIONS_ADMIN_META_BOX_VARIATIONS.ajax_url,
							data: {
								action:     'woocommerce_remove_donation_variations',
								security:   WC_DONATIONS_ADMIN_META_BOX_VARIATIONS.delete_variations_nonce,
								product_id: WC_DONATIONS_ADMIN_META_BOX_VARIATIONS.post_id,
								variation_ids: variation_ids
							},
							type: 'POST',
							success: function( response ) {
								$variation.remove();
								$( '#woocommerce-product-data' ).trigger( 'wc_donation_variation_removed', 1 );
							},
							complete: function ( response ) {
							   wc_meta_boxes_donation_variations_ajax.unblock();
							}
						});
						
					}

	
			}

			return false;
		},	
		
		/**
		 * Add new class when have changes in some input
		 */
		input_changed: function() {
			$( this )
				.closest( '.wc_donation_variation' )
				.addClass( 'variation-needs-update' );

			$( 'button.cancel-donation-variation-changes, button.save-donation-variation-changes' ).removeAttr( 'disabled' );

			$( '#donation_product_data' ).trigger( 'wc_donation_variations_input_changed' );
		},
	};
	
	/**
	 * Donation variations navigation
	 */
	var wc_meta_boxes_donation_variations_toolbar = {
		
		/**
		 * Initialize products variations meta box
		 */
		init: function() {
			$( document.body )
				.on( 'wc_donation_variations_loaded', this.toggle_toolbars );
		},
		
		/**
		 * Show/hide the toolbar
		 *
		 */
		toggle_toolbars: function() {
			
			var wrapper = $( '#donation_product_data' ).find( '.wc_donation_variations' ),
				toolbar          = $( '#donation_product_data' ).find( '.toolbar' ),
				controls         = toolbar.find( $( '.controls' ) ),
				message          = $( '#donation_product_data' ).find( '.notice.needs_donation_variation' );
				

			if( 0 === wrapper.find( '.wc_donation_variation' ).length ){
				message.show();
				toolbar.not( '.toolbar-top, .toolbar-buttons' ).hide();
				controls.hide();
			} else {
				message.hide();
				toolbar.show();
				controls.show();
			}
		},
		
		
	};
	
	
	wc_meta_boxes_donation_variations_actions.init();
	wc_meta_boxes_donation_variations_ajax.init();
	wc_meta_boxes_donation_variations_toolbar.init();

});
