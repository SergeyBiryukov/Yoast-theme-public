(function( $ ) {
	'use strict';

	var $body;

	function init() {
		$body = $(document.body);

		initCheckoutPage();
	}

	function initCheckoutPage() {
		$body.on( 'edd_quantity_updated', handleQuantityUpdate );
		$body.on( 'change', '.yst-edd-pricing-switcher', handleChangeDownloadVariation );
		$body.on( 'edd_cart_billing_address_updated', hideProvinceField );

		$( '#edd_first' ).focus();
	}

	/**
	 * Updates the price after the product after a quantity update
	 *
	 * @param {jQuery.Event} e
	 * @param {Object} data
	 */
	function handleQuantityUpdate( e, data ) {
		EDD_Checkout.recalculate_taxes();
	}

	/**
	 * Handles a change to the download variation by the user
	 *
	 * @param {jQuery.Event} e
	 */
	function handleChangeDownloadVariation( e ) {
		var $this = $( e.currentTarget );

		var download_id = $this.data( 'download-id' );
		var price_id = $this.val();

		var postData = {
			action: 'yst_update_variation',
			download_id: download_id,
			price_id: price_id
		};

		$.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: edd_global_vars.ajaxurl,
			xhrFields: {
				withCredentials: true
			}
		}).success(function( response ) {
			if ( 'success' === response.status ) {
				EDD_Checkout.recalculate_taxes();
			}
		});
	}

	/**
	 * If a country has no states, hide the province field
	 *
	 * @param {jQuery.Event} e
	 * @param {String} data
	 */
	function hideProvinceField( e, data ) {
		if ( 'nostates' === data ) {
			$( '#edd-card-state-wrap' ).hide();
		} else {
			$( '#edd-card-state-wrap' ).show();
		}
	}

	$( init );
}( jQuery ));
