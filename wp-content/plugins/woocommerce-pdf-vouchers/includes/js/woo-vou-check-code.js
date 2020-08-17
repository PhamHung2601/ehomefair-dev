"use strict";

jQuery( document ).ready( function( $ ) {
// Check Voucher code is valid or not
	$( document ).on( 'click', '#woo_vou_check_voucher_code', function() {
		$( '.woo-vou-voucher-code-submit-wrap' ).hide();

		//Voucher Code
		var voucode = $( '#woo_vou_voucher_code' ).val();
		$( '#woo_vou_valid_voucher_code' ).val(voucode);

		if( voucode == '' || voucode == 'undefine' ) {

			//hide submit row
			$( '.woo-vou-voucher-code-submit-wrap' ).fadeOut();
			$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-success' ).addClass( 'woo-vou-voucher-code-error' ).html( WooVouCheck.check_code_error ).show();
		} else {

			//show loader
			$( '.woo-vou-check-voucher-code-loader' ).css( 'display', 'inline' );

			//hide error message
			$( '.woo-vou-voucher-code-msg' ).hide();
			$( '.woo-vou-voucher-reverse-redeem' ).html( "" ).hide();

			// Get currency. Used to add comatible with currency switcher(realmag777)
			var currency = $(this).data('currency');
						
			var data = {
							action	: 'woo_vou_check_voucher_code',
							voucode	: voucode,
							ajax	: true,
							currency: currency
						};
			//call ajax to chcek voucher code
			jQuery.post( WooVouCheck.ajaxurl, data, function( response ) {

				var response_data = jQuery.parseJSON(response);

				if( response_data.success ) {

					$('.woo-vou-loader.woo-vou-voucher-code-submit-loader').hide();
					//show submit row
					if( response_data.loggedin_guest_user ) {

						if( response_data.expire ) {

							if(response_data.allow_redeem_expired_voucher == 'yes') {
								$( '.woo-vou-voucher-code-submit-wrap' ).fadeIn();
								$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-error' ).addClass( 'woo-vou-voucher-code-success' ).html( response_data.success ).show();
							} else {
								$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-success' ).addClass( 'woo-vou-voucher-code-error' ).html( response_data.success ).show();
							}
						} else {
							if( WooVouCheck.allow_guest_redeem_voucher == 'yes'){
								$( '.woo-vou-voucher-code-submit-wrap' ).fadeIn();
							}
							
							$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-error' ).addClass( 'woo-vou-voucher-code-success' ).html( response_data.success ).show();
						}
					} else if( response_data.expire && response_data.allow_redeem_expired_voucher == 'no' ) {
						
						$( '.woo-vou-voucher-code-submit-wrap' ).fadeOut();
						$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-success' ).addClass( 'woo-vou-voucher-code-error' ).html( response_data.success ).show();
					} else if( response_data.success && response_data.before_start_date ) {
						
						$( '.woo-vou-voucher-code-submit-wrap' ).fadeOut();
						$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-success' ).addClass( 'woo-vou-voucher-code-error' ).html( response_data.success ).show();

					}
					else if( response_data.vendors_access === false ) {
						
						$( '.woo-vou-voucher-code-submit-wrap' ).fadeOut();
						$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-error' ).addClass( 'woo-vou-voucher-code-success' ).html( response_data.success ).show();
					}
					else if( response_data.vendors_access == true ) {
						
						$( '.woo-vou-voucher-code-submit-wrap' ).fadeIn();
						$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-error' ).addClass( 'woo-vou-voucher-code-success' ).html( response_data.success ).show();
					} else {

						$( '.woo-vou-voucher-code-submit-wrap' ).fadeIn();
						$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-error' ).addClass( 'woo-vou-voucher-code-success' ).html( response_data.success ).show();
					}

					if( response_data.product_detail ) {
						$( '.woo-vou-voucher-code-msg' ).append(response_data.product_detail);
					}
					if( response_data.reverse_redeem ) {
						$( '.woo-vou-voucher-reverse-redeem' ).html( response_data.reverse_redeem ).show();
					}
				} else if( response_data.error ) {

					//hide submit row
					$( '.woo-vou-voucher-code-submit-wrap' ).fadeOut();
					$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-success' ).addClass( 'woo-vou-voucher-code-error' ).html( response_data.error ).show();
				} else if ( response_data.used ) {

					//hide submit row
					$( '.woo-vou-voucher-code-submit-wrap' ).fadeOut();

					$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-success' ).addClass( 'woo-vou-voucher-code-error' ).html( response_data.used ).show();

					if( response_data.product_detail ) {
						$( '.woo-vou-voucher-code-msg' ).append(response_data.product_detail);
					}
					if( response_data.reverse_redeem ) {
						$( '.woo-vou-voucher-reverse-redeem' ).html( response_data.reverse_redeem ).show();
					}
				}
				//hide loader
				$( '.woo-vou-check-voucher-code-loader' ).hide();

			});
		}
	});

	// Submit Voucher code ( Redeem vocher code )
	$( document ).on( 'click', '#woo_vou_voucher_code_submit', function() {
	
		
		//Voucher Code
		var voucode = $( '#woo_vou_valid_voucher_code' ).val();
		var vou_enable_partial_redeem = $('#vou_enable_partial_redeem').val();

		if( ( voucode == '' || voucode == 'undefine' ) ) {

			//hide submit row
			$( '.woo-vou-voucher-code-submit-wrap' ).fadeOut();
			$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-success' ).addClass( 'woo-vou-voucher-code-error' ).html( WooVouCheck.check_code_error ).show();
		} else {

			var redeem_amount = '';
			var redeem_method = '';
			var total_price = '';
			var redeemed_price = '';
			var remaining_redeem_price = '';

			total_price				= woo_vou_check_number($('#vou_code_total_price').val());
			redeemed_price			= woo_vou_check_number($('#vou_code_total_redeemed_price').val());
			remaining_redeem_price 	= woo_vou_check_number($('#vou_code_remaining_redeem_price').val());

			// check partial redeem is enabled
			if( vou_enable_partial_redeem == "yes" ) {

				// get redeem amount and redeem method
				redeem_amount			= woo_vou_check_number($('#vou_partial_redeem_amount').val());
				redeem_method 			= $('#vou_redeem_method').val();

				// redeem amount validation
				if( redeem_method == 'partial' && ( redeem_amount == '' || isNaN( redeem_amount ) ) ) {

					$('.woo-vou-partial-redeem-amount .woo-vou-voucher-code-error').html( WooVouCheck.redeem_amount_empty_error ).fadeIn();
					return false;
				} else if( redeem_method == 'partial' && redeem_amount > remaining_redeem_price ) {

					$('.woo-vou-partial-redeem-amount .woo-vou-voucher-code-error').html( WooVouCheck.redeem_amount_greaterthen_redeemable_amount ).fadeIn();
					return false;
				}
			}

			//hide error message
			$( '.woo-vou-voucher-code-msg' ).hide();
			$( '.woo-vou-voucher-reverse-redeem' ).html( "" ).hide();

			//show loader
			$( '.woo-vou-voucher-code-submit-loader' ).css( 'display', 'inline' );

			var data = {
							action							: 'woo_vou_save_voucher_code',
							voucode							: voucode,
							vou_code_total_price			: total_price,
							vou_code_total_redeemed_price	: redeemed_price,
							vou_code_remaining_redeem_price	: remaining_redeem_price,
							ajax							: true
						};

			// check partial redeem is enabled
			if( vou_enable_partial_redeem == "yes" ) {

				data['vou_partial_redeem_amount'] 		= redeem_amount;
				data['vou_redeem_method'] 				= redeem_method;
			}

			//Add trigger for redeem data
			$( document ).trigger( "vou_redeem_data", data );

			//call ajax to save voucher code
			jQuery.post( WooVouCheck.ajaxurl, data, function( response ) {

				var response_data = jQuery.parseJSON(response);

				if( response_data.success ) {

					//Voucher Code
					$( '#woo_vou_voucher_code' ).val( '' );
					$( '#woo_vou_valid_voucher_code' ).val( '' );
					//hide submit row
					$( '.woo-vou-voucher-code-submit-wrap' ).fadeOut();
					$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-error' ).addClass( 'woo-vou-voucher-code-success' ).html( WooVouCheck.code_used_success ).show();
				} else {

					//Voucher Code
					$( '#woo_vou_voucher_code' ).val( '' );
					$( '#woo_vou_valid_voucher_code' ).val( '' );
					//hide submit row
					$( '.woo-vou-voucher-code-submit-wrap' ).fadeOut();
					$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-success' ).addClass( 'woo-vou-voucher-code-error' ).html( response_data.error_message ).show();
				}
				//hide loader
				$( '.woo-vou-voucher-code-submit-loader' ).hide();
			});
		}
	});

	// Confirm Delete Voucher code
	$( document ).on( 'click', '.woo_vou_delete_voucher_code', function() {

		if ( confirm( WooVouCheck.delete_code_confirm ) == true ) {
	        return true;
	    } else {
	        return false;
	    }
	});

	// Date Time picker Field
	$('.woo-vou-meta-datetime').each( function() {

		var jQuerythis  = jQuery(this),
		format = jQuerythis.attr('rel');

		jQuerythis.datetimepicker({
			ampm: true,
			dateFormat : format,
			changeMonth: true,
			changeYear: true,
			yearRange: "-100:+0",
			showTime: false,
		});
    });

    if( $('.woo_vou_multi_select').length ) {

    	// apply select2 on simple select dropdown
    	$('.woo_vou_multi_select').select2();
    }

    // hide/show redeem amount on change of redeem method
    $( document ).on( 'change', '#vou_redeem_method',  function() {

    	// get selected redeem method value
    	var redeem_method = $( this ).val();
    	if( redeem_method == 'partial' ) {
    		$('.woo-vou-partial-redeem-amount').fadeIn();
    	} else {
    		$('.woo-vou-partial-redeem-amount').fadeOut	();
    	}
    });

	

    function woo_vou_check_number(number){
    	number = Math.round(number * 100) / 100;
    	var pattern = /^\d+(.\d{1,2})?$/;
    	return pattern.test(number)?number:'';
    }
});