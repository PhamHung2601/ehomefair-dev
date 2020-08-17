"use strict";

jQuery( document ).ready( function( $ ) {	
	
			
			
			
	var selected_pdf_template_options = $('#_woo_vou_pdf_template_selection option:selected').length; // Get total selected option length
	var all_pdf_template_options = $('#_woo_vou_pdf_template_selection option').length; // Get total template option
	
	if( selected_pdf_template_options == all_pdf_template_options ){ // If length of both are same then check checkbox
		$("#_woo_vou_pdf_template_select_deselect").prop("checked", true);
	}
	
	woo_vou_enable_vou_code();
	
	$('#_downloadable').change (function() { // on click downlable checkbox
		
		woo_vou_enable_vou_code();
	});

	//show and hide voucher price
	if($('#_downloadable').is(":checked")){
		$("._woo_vou_voucher_price_field").show();
	} else {
		$("._woo_vou_voucher_price_field").hide();
	}
		
	// hide voucher price on change product type    
	$(document).on('change', '#product-type', function() { 
		$("._woo_vou_voucher_price_field").hide();
	});

	
	jQuery(document).on('change', 'body .variable_voucher_expiration_date_type_field .variable_voucher_expiration_date_type_field_select',function(){
		
		var expiration_date_type = $(this).val();
		var variable_id = $(this).data('variable-id');

		if(expiration_date_type == 'based_on_purchase' || expiration_date_type == 'based_on_gift_date'){
			$('.based-on-purchase-settings.'+variable_id).show();
			$('.specific-time-settings.'+variable_id).hide();
			$('.variable_voucher_expiration_date_type_field_day_diff_select').trigger( "change" );
		}
		else if(expiration_date_type == 'specific_date' ){
			$('.specific-time-settings.'+variable_id).show();
			$('.based-on-purchase-settings.'+variable_id).hide();
		}
		else{
			$('.specific-time-settings.'+variable_id).hide();
			$('.based-on-purchase-settings.'+variable_id).hide();
			$('.variable_voucher_expiration_date_type_field_day_diff_select').trigger( "change" );
		}
		
		
	});
	
	jQuery(document).on('change', '.variable_voucher_expiration_date_type_field_day_diff_select',function(){
		
		var day_diff = $(this).val();	
		var variable_id = $(this).data('variable-id');
		
		$('.variable_voucher_expiration_date_type_field_custom_days.'+variable_id).hide();
		if(day_diff == 'cust'){
			$('.variable_voucher_expiration_date_type_field_custom_days.'+variable_id).show();
		}	
	});

	jQuery(document).on('woocommerce_variations_loaded', function(){	

		jQuery( "body .variable_voucher_expiration_date_type_field .variable_voucher_expiration_date_type_field_select" ).trigger( "change" );
		$('.woo-vou-meta-datetime').datepicker( "destroy" );
		
		$(".woo-vou-meta-datetime-start-time").each(function(index) {
			var this_id = $(this).attr("id");
			var var_id = $(this).data("var_id");
			
			$("#"+this_id).datetimepicker({
				dateFormat : 'yy-mm-dd',
				showTime: false,
				 onSelect: function (selected) {
					  var dt = new Date(selected);
					  dt.setDate(dt.getDate() + 1);
					$("#woo-vou-variable-expiration-date-type-field-end-date-"+var_id).datetimepicker("option", "minDate", dt);
				}       
			});
		});
		
		$(".woo-vou-meta-datetime-exp-time").each(function(index) {
			var this_id = $(this).attr("id");
			
			var var_id = $(this).data("var_id");
			
			$("#"+this_id).datetimepicker({
				dateFormat : 'yy-mm-dd',
				showTime: false,
				 onSelect: function (selected) {
					  var dt = new Date(selected);
					  dt.setDate(dt.getDate());
					$("#woo-vou-variable-expiration-date-type-field-start-date-"+var_id).datetimepicker("option", "maxDate", dt);
				}       
			});
		});
		
		
		$(".variable_voucher_expiration_date_type_field .woo-vou-meta-datetime-end-time").each(function(index) {
			
			/*$(this_id).datetimepicker({
				dateFormat : 'yy-mm-dd',
				showTime: false,
			}); */
		});
	});

	//jQuery( ".variable_voucher_expiration_date_type_field_select" ).trigger( "change" );
	
	
	/* function woo_vou_toggle_expiration_date_type_for_variable_product(){
		
		var expiration_date_type = $(this).val();
		
		if(expiration_date_type == 'based_on_purchase' || expiration_date_type == 'based_on_gift_date'){
			$('.based-on-purchase-settings').show();
		}
		else if(expiration_date_type == 'specific_date' ){
			$('.specific-time-settings').show();
		}else{
			$('.specific-time-settings').hide();
			$('.based-on-purchase-settings').hide();
		}
	} */
	

	woo_vou_toggle_expiration_date_type();
	jQuery(document).on('change', 'select[name=_woo_vou_exp_type]',function(){

		woo_vou_toggle_expiration_date_type();
	});
	
	jQuery(document).on('change', '._woo_vou_days_diff', function() {

		if( jQuery('ul.woo-vou-add-tabination li.woo-vou-tab-general').hasClass('woo-vou-tab-active') ) {
			var days_diff = $(this).val();
			
	        if( days_diff == 'cust' ){
	        	jQuery( '._woo_vou_custom_days_field' ).show();
	        	jQuery( '.custom-desc' ).hide();
	        }else{
	        	jQuery( '._woo_vou_custom_days_field' ).hide();
	        	jQuery( '.custom-desc' ).show();
	        }
		}
	});
		
	//on click of used codes button 
	$( document ).on( "click", ".woo-vou-meta-vou-purchased-data", function() {
		
		var popupcontent = $(this).parent().parent().find( '.woo-vou-purchased-codes-popup' );
		popupcontent.show();
		$(this).parent().parent().find( '.woo-vou-purchased-codes-popup-overlay' ).show();
		$('html, body').animate({ scrollTop: popupcontent.offset().top - 60 }, 500);
		
	});

	woo_vou_hide_buyer_order_extra_fields();
	
	function woo_vou_hide_buyer_order_extra_fields(){
		$( '.woo-vou-purchased-codes-popup .woo-vou-buyer-info-table .buyer_address').hide();
		$( '.woo-vou-purchased-codes-popup .woo-vou-buyer-info-table .buyer_phone').hide();
		$( '.woo-vou-purchased-codes-popup .woo-vou-order-info-table .payment_method').hide();
		$( '.woo-vou-purchased-codes-popup .woo-vou-order-info-table .order_total').hide();
		$( '.woo-vou-purchased-codes-popup .woo-vou-order-info-table .order_discount').hide();

		$( '.woo-vou-used-codes-popup .woo-vou-buyer-info-table .buyer_address').hide();
		$( '.woo-vou-used-codes-popup .woo-vou-buyer-info-table .buyer_phone').hide();
		$( '.woo-vou-used-codes-popup .woo-vou-order-info-table .payment_method').hide();
		$( '.woo-vou-used-codes-popup .woo-vou-order-info-table .order_total').hide();
		$( '.woo-vou-used-codes-popup .woo-vou-order-info-table .order_discount').hide();
                
                                        $( '.woo-vou-unused-codes-popup .woo-vou-buyer-info-table .buyer_address').hide();
		$( '.woo-vou-unused-codes-popup .woo-vou-buyer-info-table .buyer_phone').hide();
		$( '.woo-vou-unused-codes-popup .woo-vou-order-info-table .payment_method').hide();
		$( '.woo-vou-unused-codes-popup .woo-vou-order-info-table .order_total').hide();
		$( '.woo-vou-unused-codes-popup .woo-vou-order-info-table .order_discount').hide();
		
		$( '.woo-vou-purchased-codes-popup a.woo-vou-show-buyer').show();
		$( '.woo-vou-used-codes-popup a.woo-vou-show-buyer').show();
                                        $( '.woo-vou-unused-codes-popup a.woo-vou-show-buyer').show();
		$( '.woo-vou-purchased-codes-popup a.woo-vou-show-order').show();
		$( '.woo-vou-used-codes-popup a.woo-vou-show-order').show();
                                        $( '.woo-vou-unused-codes-popup a.woo-vou-show-order').show();
	}

	//on click of show buyer button 
	$( document ).on( "click", ".woo-vou-purchased-codes-popup a.woo-vou-show-buyer, .woo-vou-used-codes-popup a.woo-vou-show-buyer, .woo-vou-unused-codes-popup a.woo-vou-show-buyer", function() {
		
		var voucherid = $(this).data('voucherid');
		$(this).hide();
		$( '#buyer_voucher_'+voucherid+' .woo-vou-buyer-info-table .buyer_address').show();
		$( '#buyer_voucher_'+voucherid+' .woo-vou-buyer-info-table .buyer_phone').show();
		
	});
	//on click of show order button 
	$( document ).on( "click", ".woo-vou-purchased-codes-popup a.woo-vou-show-order, .woo-vou-used-codes-popup a.woo-vou-show-order, .woo-vou-unused-codes-popup a.woo-vou-show-order", function() {
		
		var voucherid = $(this).data('voucherid');
		$(this).hide();
		$( '#order_voucher_'+voucherid+' .woo-vou-order-info-table .payment_method').show();
		$( '#order_voucher_'+voucherid+' .woo-vou-order-info-table .order_total').show();
		$( '#order_voucher_'+voucherid+' .woo-vou-order-info-table .order_discount').show();
		
	});


	//on click of import coupon codes button, import code
	$( document ).on( "click", "#woo_vou_purchased_load_more_btn", function() {
		
		var purchased_post_id = $('#woo_vou_purchased_post_id').val();
		var purchased_paged = $('#woo_vou_purchased_paged').val();
		var purchased_postsperpage = $('#woo_vou_purchased_postsperpage').val();
		

		$('.woo-vou-purchased-popup-loader').show();
		$('.woo-vou-purchased-popup-overlay').show();
		var data = {
					action					: 'woo_vou_load_more_purchased_voucode',
					purchased_post_id		: purchased_post_id,
					purchased_paged			: purchased_paged,
					purchased_postsperpage	: purchased_postsperpage					
				};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			$.post( WooVouMeta.ajaxurl, data, function(response) {

				var response_data = jQuery.parseJSON(response);

				if( !response_data.norecfound ) {
					var purchased_code = response_data.html;
					$( '.woo-vou-purchased-codes-popup #woo_vou_purchased_codes_table' ).append( purchased_code );
					woo_vou_hide_buyer_order_extra_fields();
					$('#woo_vou_purchased_paged').val( parseInt(purchased_paged) + 1 );
				} else {

					$('#woo_vou_purchased_load_more_btn').hide();
				}

				$('.woo-vou-purchased-popup-loader').hide();
				$('.woo-vou-purchased-popup-overlay').hide();
			});
		
	});
	
	//on click of import coupon codes button, import code
	$( document ).on( "click", "#woo_vou_used_load_more_btn", function() {
		
		var used_post_id = $('#woo_vou_used_post_id').val();
		var used_paged = $('#woo_vou_used_paged').val();
		var used_postsperpage = $('#woo_vou_used_postsperpage').val();		

		$('.woo-vou-used-popup-loader').show();
		$('.woo-vou-used-popup-overlay').show();
		var data = {
					action				: 'woo_vou_load_more_used_voucode',
					used_post_id		: used_post_id,
					used_paged			: used_paged,
					used_postsperpage	: used_postsperpage					
				};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			$.post( WooVouMeta.ajaxurl, data, function(response) {

				var response_data = jQuery.parseJSON(response);

				if( !response_data.norecfound ) {

					var used_code = response_data.html;
					$( '.woo-vou-used-codes-popup #woo_vou_used_codes_table' ).append( used_code );
					woo_vou_hide_buyer_order_extra_fields();
					$('#woo_vou_used_paged').val( parseInt(used_paged) + 1 );
				} else {

					$('#woo_vou_used_load_more_btn').hide();
				}

				$('.woo-vou-used-popup-loader').hide();
				$('.woo-vou-used-popup-overlay').hide();
			});
		
	});

	
	//on click of used codes button
	$( document ).on( "click", ".woo-vou-meta-vou-used-data", function() {
		
		var popupcontent = $(this).parent().parent().find( '.woo-vou-used-codes-popup' );
		popupcontent.show();
		$(this).parent().parent().find( '.woo-vou-used-codes-popup-overlay' ).show();
		$('html, body').animate({ scrollTop: popupcontent.offset().top - 60 }, 500);
		
	});

	$( document ).on( "click", "#woo_vou_unused_load_more_btn", function() {

		var unused_post_id = $('#woo_vou_unused_post_id').val();
		var unused_paged = $('#woo_vou_unused_paged').val();
		var unused_postsperpage = $('#woo_vou_unused_postsperpage').val();		

		$('.woo-vou-unused-popup-loader').show();
		$('.woo-vou-unused-popup-overlay').show();
		var data = {
					action				: 'woo_vou_load_more_unused_voucode',
					unused_post_id		: unused_post_id,
					unused_paged		: unused_paged,
					unused_postsperpage	: unused_postsperpage					
				};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			$.post( WooVouMeta.ajaxurl, data, function(response) {

				var response_data = jQuery.parseJSON(response);

				if( !response_data.norecfound ) {

					var used_code = response_data.html;
					$( '.woo-vou-unused-codes-popup #woo_vou_unused_codes_table' ).append( used_code );
					woo_vou_hide_buyer_order_extra_fields();
					$('#woo_vou_unused_paged').val( parseInt(unused_paged) + 1 );
				} else {

					$('#woo_vou_unused_load_more_btn').hide();
				}

				$('.woo-vou-unused-popup-loader').hide();
				$('.woo-vou-unused-popup-overlay').hide();
			});
	});

	//, .woo-vou-meta-vou-import-data
	$( document ).on( "click", ".woo-vou-meta-vou-import-data", function() {
		
		$('.woo-vou-file-errors').hide();
		$('.woo-vou-delete-code').val('');
		$('.woo-vou-no-of-voucher').val('');
		$('.woo-vou-code-prefix').val('');
		$('.woo-vou-code-seperator').val('');
		$('.woo-vou-code-pattern').val('');
		$('.woo-vou-csv-sep').val('');
		$('.woo-vou-csv-enc').val('');
		$('.woo-vou-csv-file').val('');
		
		$( '.woo-vou-import-content' ).show();
		$( '.woo-vou-import-overlay' ).show();
		
		var importcodecontent = $( '.woo-vou-import-content' );
		$('html, body').animate({ scrollTop: importcodecontent.offset().top - 60 }, 500);
		
	});

	$( document ).on( "click", ".woo-vou-meta-vou-unused-data", function() {
		
		var unusedpopup = $(this).parent().parent().find( '.woo-vou-unused-codes-popup' );
		unusedpopup.show();
		$(this).parent().parent().find( '.woo-vou-unused-codes-popup-overlay' ).show();
		$('html, body').animate({ scrollTop: unusedpopup.offset().top - 60 }, 500);
	});
	
	//on click of close button or overlay
		
	$( document ).on( "click", ".woo-vou-popup-overlay, .woo-vou-close-button", function() {
		
		//when import csv file popup is open
		if( $('.woo-vou-file-errors').length > 0 ) {
			$('.woo-vou-file-errors').hide();
			$('.woo-vou-file-errors').html('');
		}
		
		//common code for both popup of voucher codes used and import csv file
		$( '.woo-vou-popup-content' ).hide();
		$( '.woo-vou-popup-overlay' ).hide();
		woo_vou_hide_buyer_order_extra_fields();
	});
	
	//on click of import coupon codes button, import code
	$( document ).on( "click", ".woo-vou-import-btn", function() {
		
		var existing_code = $('#_woo_vou_codes').val();
		var delete_code = $( '.woo-vou-delete-code' ).val();
		var no_of_voucher = $( '.woo-vou-no-of-voucher' ).val();
		var code_prefix = $( '.woo-vou-code-prefix' ).val();
		var code_seperator = $( '.woo-vou-code-seperator' ).val();
		var code_pattern = $( '.woo-vou-code-pattern' ).val();
		
		$( '.woo-vou-file-errors' ).html('').hide();
		
		var error_msg = '';		
		if( no_of_voucher == '' ) {
			error_msg += WooVouMeta.noofvouchererror;
		} else{	
			var numeric_pattern = '^[0-9]*$';	
			if( !no_of_voucher.match( numeric_pattern ) ){
				error_msg += WooVouMeta.onlydigitserror;
			}
		}
		if( code_pattern == '' ) {
			
			error_msg += WooVouMeta.patternemptyerror;
			
		} else if( code_pattern.indexOf('l') == '-1' && code_pattern.indexOf('d') == '-1' && code_pattern.indexOf('L') == '-1' && code_pattern.indexOf('D') == '-1' ) {
			
			error_msg += WooVouMeta.generateerror;
		}
		
		if( error_msg != '' ) {
			$('.woo-vou-file-errors').html(error_msg).show();
			$('.woo-vou-popup').scrollTop(0);
		} else {
		
			$( '.woo-vou-loader' ).show();
			var data = {
							action			: 'woo_vou_import_code',
							noofvoucher		: no_of_voucher,
							codeprefix		: code_prefix,
							codeseperator	: code_seperator,
							codepattern		: code_pattern,
							existingcode	: existing_code,
							deletecode		: delete_code
						};
		
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			$.post( WooVouMeta.ajaxurl, data, function(response) {
				var import_code = response;
				$( '.woo-vou-loader' ).hide();
				$( '#_woo_vou_codes' ).val(import_code);
				$( '.woo-vou-popup-content' ).hide();
				$( '.woo-vou-popup-overlay' ).hide();
				$( '#woo_vou_codes_error' ).hide();
				$( '#woo_vou_days_error' ).hide();
				
				var voucodecontent = $( '#_woo_vou_codes' ).removeClass( 'woo-vou-codes-red-border' );
				$('html, body').animate({ scrollTop: voucodecontent.offset().top - 50 }, 500);
				
			});
		}
	});
	
	//ajax call to get voucher codes from csv file
	$( document ).on( "click", ".woo-vou-meta-vou-import-codes", function() {
		
		$('.woo-vou-file-errors').hide();
		$('.woo-vou-file-errors').html('');
		
		var fseprator = $('.woo-vou-csv-sep').val();
		var fenclosure = $('.woo-vou-csv-enc').val();
		var existing_code = $('#_woo_vou_codes').val();
		var ext = '';
		var filename = '';
		var error = false;
		var errorstr = '';
		
		$('.woo-vou-csv-file').filter(function(){
			
			filename = $(this).val();
			//alert(filename);
			ext = filename.substring(filename.lastIndexOf('.') + 1);
			
			if( filename == '' ) {
				error = true;
				errorstr += WooVouMeta.fileerror;
			}
			if( filename != '' && ext != 'csv') {
				error = true;
				errorstr += WooVouMeta.filetypeerror;
			}
		});
		
		if( error == true ) { //check file type must be csv
			
			$('.woo-vou-file-errors').show();
			$('.woo-vou-file-errors').html(errorstr);
			$('.woo-vou-popup').scrollTop(0);
			return false;
			
		} else {
			
			if( filename != '' ) {
				 
				$('#woo_vou_existing_code').val( existing_code );
				
				$('form#woo_vou_import_csv').ajaxForm({
				    beforeSend: function() {
				    },
				    uploadProgress: function(event, position, total, percentComplete) {
				    },
				    success: function() {
				    },
					complete: function(xhr) {
						
						$('textarea#_woo_vou_codes').val(xhr.responseText);
						$( '.woo-vou-popup-content' ).hide();
						$( '.woo-vou-popup-overlay' ).hide();
						$( '#woo_vou_codes_error' ).hide();
						$( '#woo_vou_days_error' ).hide();
						$('.woo-vou-csv-file').attr({ value: '' });

						var voucodecontent = $( '#_woo_vou_codes' ).removeClass( 'woo-vou-codes-red-border' );
						$('html, body').animate({ scrollTop: voucodecontent.offset().top - 50 }, 500);
					}
				});
			}
		}
	});
	
	//repeater field add more
	jQuery( document ).on( "click", ".woo-vou-repeater-add", function() {
	
		jQuery(this).prev('div.woo-vou-meta-repater-block')
			.clone(true,true)
			.insertAfter('.woo-vou-meta-repeat div.woo-vou-meta-repater-block:last');
			
		jQuery(this).parent().find('div.woo-vou-meta-repater-block:last input').val('');
		jQuery(this).parent().find('div.woo-vou-meta-repater-block:last .woo-vou-repeater-remove').show();
		jQuery(this).trigger( "afterclone", [ jQuery(this) ] );
	});
	
	//remove repeater field
	jQuery( document ).on( "click", ".woo-vou-repeater-remove", function() {
	   jQuery(this).parent('.woo-vou-meta-repater-block').remove();
	});
	
	// Hide woocommerce voucher by changed product type bundle
	$( document ).on( 'change', '#_woo_product_type', function() {

		woo_vou_manage_voucher_option_by_bundle_product();
	});
	
	// Hide woocommerce voucher by clicked enable voucher
	$( document ).on( 'click', '#woo_variable_pricing', function() {

		woo_vou_manage_voucher_option_by_variable_product();
	});
	
	// Check Voucher Code is not empty on clicked publish/update button
	$( document ).on( 'click', '#publish', function() {
		
		var error = 'false';

		$( '#woo_vou_days_error' ).hide();
		
		
		var product_type = $( '#product-type' ).val();
		var validate = 'false';
		
		if( product_type == 'simple' && $( '#_downloadable' ).is( ':checked' ) ){
			var validate = 'true';
		} if( product_type == 'booking' && $( '#_downloadable' ).is( ':checked' ) ) {
			var validate = 'true';
		} else if( product_type == 'variable' ){
			var validate = 'true';
		}
		
		// validate url
		if( $("#_woo_vou_website").length > 0 ) {
			
			var website_url = $("#_woo_vou_website").val();
			if( $( '#_woo_vou_enable' ).is( ':checked' ) && website_url != '' && !woo_vou_is_url_valid( website_url ) ) {
				
				$( this ).parent().find( '.spinner' ).hide();
				$( this ).removeClass( 'button-primary-disabled' );
				$('#woo_vou_website_url_error').show();
				
				websitecontent = $('#_woo_vou_website').addClass('woo-vou-codes-red-border').focus();
				
				$('html, body').animate({ scrollTop: websitecontent.offset().top - 50 }, 500);			
				error = 'true';
			}	
		}		
		
		if( error == 'true' ){
			return false;
		}else {
			return true;
		}
	});

	check_is_enable_pdf_template_selection();
	check_is_enable_delivery_method();

	$(document).find('div.woo-vou-data p input[id^="_woo_vou_enable_recipient_"]').on('change', function(){

		check_is_enable_recipient_details();
		woo_vou_toggle_delivery_recipient_checkbox();
	});

	$( document ).on( 'change', 'div.woo-vou-delivery-method div.woo-vou-data p input[id^="_woo_vou_recipient_delivery"]', function(){

		check_is_enable_delivery_method();
	});

	$( document ).on( 'change', '#_woo_vou_enable_pdf_template_selection', function() {
		
		check_is_enable_pdf_template_selection();
	});

	$( document ).on( 'click', '.woo-vou-check-all-templates', function(e) {

		e.preventDefault();
		jQuery("#_woo_vou_pdf_template_selection > option").prop("selected","selected");
        jQuery("#_woo_vou_pdf_template_selection").trigger("change");
	});

	$( document ).on( 'click', '.woo-vou-uncheck-all-templates', function(e) {

		e.preventDefault();
		jQuery("#_woo_vou_pdf_template_selection > option").removeAttr("selected");
        jQuery("#_woo_vou_pdf_template_selection").trigger("change");
	});

	// select all coupon products
	$( document ).on( 'click', '.woo-vou-check-all-coupon-products', function(e) {

		e.preventDefault();
		jQuery("#_woo_vou_coupon_products > option").prop("selected","selected");
        jQuery("#_woo_vou_coupon_products").trigger("change");
	});

	// unselect all coupon products
	$( document ).on( 'click', '.woo-vou-uncheck-all-coupon-products', function(e) {

		e.preventDefault();
		jQuery("#_woo_vou_coupon_products > option").removeAttr("selected");
        jQuery("#_woo_vou_coupon_products").trigger("change");
	});
	
	
	// select all coupon exclude products
	$( document ).on( 'click', '.woo-vou-check-all-coupon-exclude-products', function(e) {

		e.preventDefault();
		jQuery("#_woo_vou_coupon_exclude_products > option").prop("selected","selected");
        jQuery("#_woo_vou_coupon_exclude_products").trigger("change");
	});

	// unselect all coupon exclude products
	$( document ).on( 'click', '.woo-vou-uncheck-all-coupon-exclude-products', function(e) {

		e.preventDefault();
		jQuery("#_woo_vou_coupon_exclude_products > option").removeAttr("selected");
        jQuery("#_woo_vou_coupon_exclude_products").trigger("change");
	});
	
	
	// select all coupon categories
	$( document ).on( 'click', '.woo-vou-check-all-coupon-categories', function(e) {

		e.preventDefault();
		jQuery("#_woo_vou_coupon_categories > option").prop("selected","selected");
        jQuery("#_woo_vou_coupon_categories").trigger("change");
	});

	// unselect all coupon categories
	$( document ).on( 'click', '.woo-vou-uncheck-all-coupon-categories', function(e) {

		e.preventDefault();
		jQuery("#_woo_vou_coupon_categories > option").removeAttr("selected");
        jQuery("#_woo_vou_coupon_categories").trigger("change");
	});
	
	
	// select all coupon exclude categories
	$( document ).on( 'click', '.woo-vou-check-all-coupon-exclude-categories', function(e) {

		e.preventDefault();
		jQuery("#_woo_vou_coupon_exclude_categories > option").prop("selected","selected");
        jQuery("#_woo_vou_coupon_exclude_categories").trigger("change");
	});
	// unselect all coupon exclude categories
	$( document ).on( 'click', '.woo-vou-uncheck-all-coupon-exclude-categories', function(e) {

		e.preventDefault();
		jQuery("#_woo_vou_coupon_exclude_categories > option").removeAttr("selected");
        jQuery("#_woo_vou_coupon_exclude_categories").trigger("change");
	});


	$( document ).on( 'change', '#_woo_vou_pdf_template_selection', function() {
	
		var selected_pdf_template_options = $('#_woo_vou_pdf_template_selection option:selected').length;
		var all_pdf_template_options = $('#_woo_vou_pdf_template_selection option').length;
		
		if( selected_pdf_template_options != all_pdf_template_options ){
			if($("#_woo_vou_pdf_template_select_deselect").is(':checked')) {
			   $("#_woo_vou_pdf_template_select_deselect").prop("checked", false);
			}
		}else{
			if($("#_woo_vou_pdf_template_select_deselect").not(':checked')) {
			   $("#_woo_vou_pdf_template_select_deselect").prop("checked", true);
			}
		}
		
	});
	
	//click on  button
	$(document).on('focusout', '._woo_vou_map_link_field .woo-vou-meta-text', function(e) {

		var url_pattern	= /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;

		if( ! url_pattern.test( $(this).val() ) ) {

			jQuery( this ).parents( 'p' ).append( '<div class="woo-vou-fade-error">'+ WooVouMeta.invalid_url +'</div>' );

			jQuery( ".woo-vou-fade-error" ).fadeOut( 3000, function() {
				jQuery( '.woo-vou-fade-error' ).remove();
			});

			jQuery( this ).val('');
			return false;
		}
	});
	
	// show/hide pdf template selection at variation once variation is loaded
	$( document ).on( "woocommerce_variations_loaded", function() {		
		if( $("#_woo_vou_enable_pdf_template_selection").is(':checked') ) {
			$('.woo-vou-pdf-template-variation-section').hide();
		} else {
			$('.woo-vou-pdf-template-variation-section').show();
		}
	});
	
	// on change of enable voucher selection hide/show template selection at variation level
	$( document ).on( 'change', '#_woo_vou_enable_pdf_template_selection', function() {
		if( $(this).is(':checked') ) {
			$('.woo-vou-pdf-template-variation-section').hide();
		} else {
			$('.woo-vou-pdf-template-variation-section').show();
		}
	});
	
	// Disallow key press for any kwy except L & D
	$('.woo-vou-code-pattern').keydown(function (e) {
		var allowed_codes = [68, 76, 8, 46, 37, 39, 65, 88, 116];
	    if($.inArray(e.keyCode, allowed_codes) === -1){
	        e.preventDefault();
	    }
	});

	// Code to add plcaeholde on focus and remove when focus out
	$('.woo-vou-code-prefix').focusin(function(){
		$(this).removeAttr("placeholder");
	});
	$('.woo-vou-code-prefix').focusout(function(){
		$(this).attr("placeholder", WooVouMeta.prefix_placeholder);
	});
	$('.woo-vou-code-seperator').focusin(function(){
		$(this).removeAttr("placeholder");
	});
	$('.woo-vou-code-seperator').focusout(function(){
		$(this).attr("placeholder", WooVouMeta.seperator_placeholder);
	});
	$('.woo-vou-code-pattern').focusin(function(){
		$(this).removeAttr("placeholder");
	});
	$('.woo-vou-code-pattern').focusout(function(){
		$(this).attr("placeholder", WooVouMeta.pattern_placeholder);
	});

	if(!WooVouMeta.is_translated) {

		// On click of publish function
		$(document).on('click', "#publish", function(e){
			// If "Enable Voucher Codes" is checked
			if ($('input#_woo_vou_enable').is(':checked')){
	
				woo_vou_remove_all_err_css(); // Remove all errors related to CSS
				var usability = $('#_woo_vou_using_type').find(":selected").val(); // Check selected usability
				var vou_codes = woo_vou_check_vou_codes(); // Get voucher codes entered
				var manage_stock = $("div#inventory_product_data #_manage_stock").is(':checked'); // Check manage stock
				var stock = $('#_stock').val(); // Check stock quantity
				var recipient_giftdate = jQuery("#_woo_vou_enable_recipient_giftdate").is(':checked'); // Check recipient giftdate
				var exp_type = jQuery( 'select[name=_woo_vou_exp_type]' ).val();  // Check Voucher expiration type
				var pro_valid = true;
	
				if( !recipient_giftdate && exp_type == 'based_on_gift_date' ){
					woo_vou_show_empty_vou_exp_type_error(e); // Go for Vou code empty error
					pro_valid = false;
				}
				// If usability is empty
				if(!$.trim(usability)){
					// Check global usability settings
					if(WooVouMeta.global_vou_pdf_usability == 0 && vou_codes == 'false'){ // If usability is set to one time only and voucher codes are not entered at product level
						woo_vou_show_empty_vou_codes_error(e); // Go for Vou code empty error
						pro_valid = false;
					} else if(WooVouMeta.global_vou_pdf_usability == 1 && manage_stock && stock == 0 && vou_codes == 'false'){ // If usability is set to unlimited and manage stock is ticked with quantity set to 0
						woo_vou_show_empty_stock_qty(e); // Go for stock quantity error
						pro_valid = false;
					}
				} else if (usability == 0 && vou_codes == 'false'){ // if usability is "One time Only" and voucher codes are not entered
					woo_vou_show_empty_vou_codes_error(e); // Go for Vouc code empty error
					pro_valid = false;
				} else if (usability == 1){ // If usability is set to unlimited and manage stock is ticked with quantity set to 0
					if(manage_stock && stock == 0 && vou_codes == 'false'){
						woo_vou_show_empty_stock_qty(e); // Go for stock quantity error
						pro_valid = false;
					}
				}

				if(pro_valid){
					if($('input#_woo_vou_enable_recipient_delivery_method').is(':checked')){

						jQuery('.woo-vou-delivery-method-error').hide();
						jQuery('.woo-vou-offline-delivery-error').hide();
						var is_delivery_method_checked = false;
						$('div.recipient-delivery-method-detail-wrap p').find('input[id^="_woo_vou_recipient_delivery"]').each(function(){
							if($(this).is(':checked')){
								is_delivery_method_checked = true;
							}
						});

						if(!is_delivery_method_checked){
							woo_vou_show_delivery_method_error(e);
							pro_valid = false;
						}
					}
				}
			}
		});
	
		$(document).on('change', '#_woo_vou_using_type', function(e){
	
			if ($('input#_woo_vou_enable').is(':checked')){
				var usability = $(this).val(); // Check selected usability
				var vou_codes = woo_vou_check_vou_codes(); // Get voucher codes entered
				woo_vou_remove_all_err_css();
	
				// If usability is empty
				if($.trim(usability) && usability == 0 && !vou_codes){
	
					woo_vou_show_empty_vou_codes_error(e); // Go for Vouc code empty error
				}
			}
		});
	
		$(document).on('focusout', '#_woo_vou_codes', function(e){
	
			if ($('input#_woo_vou_enable').is(':checked')){
				var usability = $('#_woo_vou_using_type').find(":selected").val(); // Check selected usability
				var vou_codes = $(this).val();
				woo_vou_remove_all_err_css();
				if(usability == 0 && !vou_codes){
		
					woo_vou_show_empty_vou_codes_error(e); // Go for Vouc code empty error
				}
			}
		});
	
		$(document).on('focusout', '#_stock', function(){
			if($('#woo_vou_stock_error').length){
				var stock = $.trim($(this).val());
				if(stock != 0 || stock != ''){
					woo_vou_remove_all_err_css();
				}
			}
		});
	}

	woo_vou_toggle_tabination('general');
	//jquery code to hide/show tabs on tab changes
	jQuery(document).on('click', 'ul.woo-vou-add-tabination li.woo-vou-tabination-wrapper',function(e){

		e.preventDefault();
		var show_tab = jQuery(this).children().data('show-info');
		jQuery('ul.woo-vou-add-tabination li.woo-vou-tabination-wrapper').removeClass('woo-vou-tab-active');
		jQuery(this).addClass('woo-vou-tab-active');
		woo_vou_toggle_tabination(show_tab);
	});

	woo_vou_toggle_recipient_fields();
	// On change on enable delivery method checkbox
	jQuery(document).on('change', '#_woo_vou_enable_recipient_delivery_method', function(){
		woo_vou_toggle_recipient_fields();
	});

	function woo_vou_toggle_recipient_fields(){

		if( jQuery('#_woo_vou_enable_recipient_delivery_method').is(':checked') ){
	
			jQuery('.recipient-delivery-method-detail-wrap').show();
		} else {
	
			jQuery('.recipient-delivery-method-detail-wrap').hide();
		}
		woo_vou_toggle_delivery_recipient_checkbox();
	}

	// Loop on all delivery recipient checkbox to hide/show recipient detail checkbox inside delivery method
	// Calls on page load, delivery method tick/untick and recipient details tick/untick
	function woo_vou_toggle_delivery_recipient_checkbox(){
	
		var recipient_checked = 'false';
		// Loop on all recipient information
		jQuery('div.woo-vou-recipient-detail input[id^="_woo_vou_enable_recipient_"]').each(function(){
	
			// Declaring variables
			var id = $(this).attr('id'); // Get recipient detail id
			var recipient_detail = id.replace('_woo_vou_enable_',''); // Get recipient checkbox type to know which checkbox user have modified
			var recipient_data_input = $(this); // Get current object
	
			// Check recipient email checkbox
			if( recipient_detail == 'recipient_email' ) {
				if(recipient_data_input.is(':checked') && !WooVouMeta.is_translated){
					jQuery('input#_woo_vou_recipient_delivery\\[enable_email\\]').removeAttr('disabled');
				}
			}

			// Looping on all recipient delivery checkboxes
			$("input[id^='_woo_vou_recipient_delivery']").each(function(){

				// If recipient detail is same as value
				if($(this).attr('value') == recipient_detail){
	
					// If checkbox is checked
					if(recipient_data_input.is(":checked")){
						$(this).parent('div').show(); // Show it
	
						recipient_checked = 'true';
					} else {
						$(this).parent('div').hide(); // Hide it
					}
				}
			});
		});
	
		if( recipient_checked == 'true' ) {
			$('.woo-vou-recipient-errors').hide();
			$('.woo-vou-recipient-errors').nextAll().each(function(){
				if($(this).is('p')){
					$(this).show();
					if($(this).find('input').is(':checked')){
						$(this).next().show();
					} else {
						$(this).next().hide();
					}
				}
			});
		} else {
			$('.woo-vou-recipient-errors').show();
			$('.woo-vou-recipient-errors').nextAll().hide();
		}
	}
	
	function woo_vou_toggle_tabination(show_tab){
	
		jQuery('ul.woo-vou-add-tabination').nextAll().each(function(){
	
			var el_type = jQuery(this).data('field-type');
			if(jQuery.trim(el_type)) {
				if(el_type == show_tab){
					jQuery(this).show();
				} else {
					jQuery(this).hide();
				}
			}
		});
	
		if(show_tab == 'general' && !$('#_woo_vou_enable_recipient_delivery_method').is(':checked')){
			$('._woo_vou_voucher_delivery_field').show();
		} else {
			$('._woo_vou_voucher_delivery_field').hide();
		}

		if(show_tab == 'recipient'){
			check_is_enable_recipient_details();
			check_is_enable_delivery_method();
		}
		check_is_enable_pdf_template_selection();
		woo_vou_toggle_expiration_date_type();
	}
	
	// Function to hide/show recipient name details based on "Enable Recipient Name" checkbox
	// Calls on initialisation and whenever Enable Recipient Name checkbox is changed
	function check_is_enable_recipient_details(){
		
		if( $('ul.woo-vou-add-tabination li.woo-vou-tab-recipient').hasClass('woo-vou-tab-active') ) 

			$( 'div.woo-vou-data p').find( 'input[id^="_woo_vou_enable_recipient_"]' ).each(function(){

				if($(this).is( ':checked' ) ){
	
					$(this).parents('div.woo-vou-data').find('div.recipient-detail-wrap').show();
				} else {
	
					$(this).parents('div.woo-vou-data').find('div.recipient-detail-wrap').hide();
				}
			});
	}
	
	function check_is_enable_delivery_method(){
	
		if( jQuery('ul.woo-vou-add-tabination li.woo-vou-tab-recipient').hasClass('woo-vou-tab-active') ){
			jQuery('div.woo-vou-delivery-method div.woo-vou-data p input[id^="_woo_vou_recipient_delivery"]').each(function(){

				if( $(this).is(":checked") ) {
					$(this).parents('p').next().show();
				} else {
					$(this).parents('p').next().hide();
				}
			});
		}
	}
	
	function check_is_enable_pdf_template_selection(){
	
		if(jQuery('ul.woo-vou-add-tabination li.woo-vou-tab-voutemplates').hasClass('woo-vou-tab-active')) {
			if( jQuery( '#_woo_vou_enable_pdf_template_selection' ).is( ':checked' ) ){
					
				jQuery('._woo_vou_pdf_template_selection_field').show();
				jQuery('._woo_vou_pdf_template_selection_label_field').show();
				jQuery('._woo_vou_pdf_template_selection_is_required_field').show();
				jQuery('._woo_vou_pdf_template_select_deselect_field').show();
				jQuery('._woo_vou_pdf_selection_desc_field').show();
				jQuery('._woo_vou_pdf_template_field').hide();
				
			} else {
				
				jQuery('._woo_vou_pdf_template_selection_field').hide();
				jQuery('._woo_vou_pdf_template_selection_label_field').hide();
				jQuery('._woo_vou_pdf_template_selection_is_required_field').hide();
				jQuery('._woo_vou_pdf_template_select_deselect_field').hide();
				jQuery('._woo_vou_pdf_selection_desc_field').hide();
				jQuery('._woo_vou_pdf_template_field').show();
			}
		} else {
	
			jQuery('._woo_vou_pdf_template_selection_field').hide();
			jQuery('._woo_vou_pdf_template_selection_label_field').hide();
			jQuery('._woo_vou_pdf_template_selection_is_required_field').hide();
			jQuery('._woo_vou_pdf_template_select_deselect_field').hide();
			jQuery('._woo_vou_pdf_selection_desc_field').hide();
			jQuery('._woo_vou_pdf_template_field').hide();
		}
	}
	
	function woo_vou_is_numeric(input){
		
	    return (input - 0) == input && (''+input).replace(/^\s+|\s+$/g, "").length > 0;
	}
	
	// The function that allow only number [0-9]
	function woo_vou_is_number_key_per_page( evt ) {
		
		var charCode = (evt.which) ? evt.which : evt.keyCode;
		
		if (charCode > 31 && (charCode < 48 || charCode > 57))
			return false;
		return true;
	}
	
	// function to validate url
	function woo_vou_is_url_valid( url ) {
	    return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
	}
	
	// Function to tick / untick Enable Voucher Code ate product meta level
	function woo_vou_enable_vou_code(){
	
		//To automaticaly check/uncheck Enable Voucher code checkbox on product page
		if ( WooVouMeta.enable_voucher == 'yes' ) { // check global enable voucher option
			if(jQuery('input[name="_downloadable"]').is(":visible")){
				// get downloadable checkbox value
				var woo_vou_downloadable_check = jQuery('input[name="_downloadable"]:checked').val();
				if 	 ( woo_vou_downloadable_check == 'on' ) {	
					jQuery( "#_woo_vou_enable" ).prop( "checked", true );
					jQuery( "._woo_vou_voucher_price_field" ).show();	
				} else {	
					jQuery( "#_woo_vou_enable" ).prop( "checked", false );
					jQuery( "._woo_vou_voucher_price_field" ).hide();
				}
			}
		}
	}
	
	// Function to check whether voucher codes entered at variation/product level and returns accordingly
	function woo_vou_check_vou_codes(){
	
		var vou_codes = 'false'; // Declare variable
	
		if(WooVouMeta.is_variable == 1){ // If product is variable
	
			var vou_code_flag = 0; // Set flag to 0
			if(jQuery('#variable_product_options div.woocommerce_variation textarea[id^="woo-vou-variable-codes-"]').length){ // Check length for variations in product
				jQuery('#variable_product_options div.woocommerce_variation textarea[id^="woo-vou-variable-codes-"]').each(function(){ // Loop on each variation
					if(jQuery.trim(jQuery(this).val()).length) { // Check length of voucher codes, if not empty return true
						vou_code_flag = 1;
						vou_codes = 'true';
					}
				});
			} else if(jQuery('#_woo_vou_is_variable_voucher').val() == 1) { // If variation is not found, than serach for meta
				vou_code_flag = 1;
			}
	
			if(vou_code_flag == 1){ // If flag is set
				vou_codes = 'true';
			} else if(vou_code_flag == 0 && jQuery.trim(jQuery('#_woo_vou_codes').val()).length){ // If flag is not set than check length for voucher codes at product level
				vou_codes = 'true';
			} else { // Else returns false
				vou_codes = 'false';
			}
		} else { // If product is ot variable
	
			if(jQuery.trim(jQuery('#_woo_vou_codes').val()).length) // Check length for voucher codes
				vou_codes = 'true';
			else
				vou_codes = 'false';
		}
	
		// return
		return vou_codes;
	}
	
	// Function to show Voucher codes empty error
	function woo_vou_show_empty_vou_codes_error(e){
	
		jQuery('li.woo_vou_voucher_tab').css("cssText", "border: 1px solid #ff0000 !important;"); // Make border for Voucher code tab as red
		jQuery('li.woo_vou_voucher_tab a').trigger('click'); // Trigger click event for voucher tab
		jQuery('li.woo-vou-tab-general').trigger('click');
		jQuery('#_woo_vou_codes').css("border-color", "#ff0000"); // Make border for Voucher Codes textarea
		jQuery('html, body').animate({scrollTop: jQuery('#_woo_vou_codes').offset().top - 50}, 500); // Scroll to textarea
		jQuery('#woo_vou_codes_error').removeClass('woo-vou-display-none').removeAttr('style');
	
		e.preventDefault(); // Prevent updating page
	}

	// Function to show Voucher expiration type error
	function woo_vou_show_empty_vou_exp_type_error(e) {

		jQuery('li.woo_vou_voucher_tab').css("cssText", "border: 1px solid #ff0000 !important;"); // Make border for Voucher code tab as red
		jQuery('li.woo_vou_voucher_tab a').trigger('click'); // Trigger click event for voucher tab
		jQuery('li.woo-vou-tab-general').trigger('click');
		jQuery('._woo_vou_exp_type_field .select2-container .select2-selection[aria-labelledby="select2-_woo_vou_exp_type-container"]').css("border-color", "#ff0000"); // Make border for Voucher Codes textarea
		jQuery('html, body').animate({scrollTop: jQuery('#_woo_vou_exp_type').offset().top - 50}, 500); // Scroll to textarea
		jQuery('#woo_vou_exp_type_error').removeClass('woo-vou-display-none').removeAttr('style');
		
		e.preventDefault(); // Prevent updating page
	}
	
	// Function to show stock quantity error
	function woo_vou_show_empty_stock_qty(e){
	
		jQuery('li.inventory_tab').css("cssText", "border: 1px solid #ff0000 !important;"); // Make border for Inventory tab as red
		jQuery('li.inventory_tab a').trigger('click'); // Trigger click event for Inventory tab
		jQuery('html, body').animate({scrollTop: jQuery('#_stock').offset().top - 50}, 500); // Scroll to stock input field
		jQuery('div#inventory_product_data').prepend(WooVouMeta.stock_qty_err);
	
		e.preventDefault(); // Prevent updating the page
	}
	
	// Function to show delivery method error
	function woo_vou_show_delivery_method_error(e){
	
		jQuery('li.woo_vou_voucher_tab').css("cssText", "border: 1px solid #ff0000 !important;"); // Make border for Voucher code tab as red
		jQuery('li.woo_vou_voucher_tab a').trigger('click'); // Trigger click event for voucher tab
		jQuery('li.woo-vou-tab-recipient').trigger('click');
		if(jQuery('div.woo-vou-delivery-method').hasClass('closed')){
			jQuery('div.woo-vou-delivery-method h3').trigger('click');
		}
		jQuery('#_woo_vou_codes').css("border-color", "#ff0000"); // Make border for Voucher Codes textarea
		jQuery('html, body').animate({scrollTop: jQuery('div.woo-vou-delivery-method').offset().top - 50}, 500); // Scroll to textarea
		jQuery('.woo-vou-delivery-method-error').show();
	
		e.preventDefault(); // Prevent updating page
	}
	
	// Function to show delivery method error
	function woo_vou_show_offline_method_error(e){
	
		jQuery('li.woo_vou_voucher_tab').css("cssText", "border: 1px solid #ff0000 !important;"); // Make border for Voucher code tab as red
		jQuery('li.woo_vou_voucher_tab a').trigger('click'); // Trigger click event for voucher tab
		jQuery('li.woo-vou-tab-recipient').trigger('click');
		if(jQuery('div.woo-vou-delivery-method').hasClass('closed')){
			jQuery('div.woo-vou-delivery-method h3').trigger('click');
		}
		jQuery('#_woo_vou_codes').css("border-color", "#ff0000"); // Make border for Voucher Codes textarea
		jQuery('html, body').animate({scrollTop: (jQuery('div#woocommerce-product-data').offset().top + jQuery(document).find('div#woocommerce-product-data').prop("scrollHeight") - 150)}, 500); // Scroll to textarea
		jQuery('.woo-vou-offline-delivery-error').show();
	
		e.preventDefault(); // Prevent updating page
	}
	
	// Function to remove all errors generated from woo_vou_show_empty_vou_codes_error & woo_vou_show_empty_stock_qty, woo_vou_show_empty_vou_exp_type_error function
	function woo_vou_remove_all_err_css(){
	
		jQuery('li.woo_vou_voucher_tab, li.inventory_tab').removeAttr('style'); // Remove border from tab
		jQuery('#_woo_vou_codes').css("border-color", "#ddd"); // Remove border for input field
		jQuery( '#woo_vou_codes_error' ).hide();
		jQuery('#woo_vou_stock_error').detach();
		jQuery('._woo_vou_exp_type_field .select2-container .select2-selection[aria-labelledby="select2-_woo_vou_exp_type-container"]').removeAttr('style'); // Make border for Voucher Codes textarea
		jQuery('#woo_vou_exp_type_error').hide();

	}
	
	function woo_vou_toggle_expiration_date_type(){
	
		if( jQuery('ul.woo-vou-add-tabination li.woo-vou-tab-general').hasClass('woo-vou-tab-active') ) {
			var exp_type = jQuery( 'select[name=_woo_vou_exp_type]' ).val();
		
			if( exp_type == 'based_on_purchase' ){
				jQuery( '._woo_vou_exp_date_field' ).hide();
				jQuery( '._woo_vou_start_date_field' ).hide();
				$('._woo_vou_days_diff_field').css('display', 'block');
				
				var woo_vou_days_diff = jQuery('select[name=_woo_vou_days_diff] option:selected').val();
				if( woo_vou_days_diff == 'cust' ){
					jQuery( '._woo_vou_custom_days_field' ).show();
					jQuery( '.custom-desc' ).hide();
				}else{
					jQuery( '._woo_vou_custom_days_field' ).hide();
					jQuery( '.custom-desc' ).show();
				}
			}else if( exp_type == 'based_on_gift_date' ){

				jQuery( '._woo_vou_exp_date_field' ).hide();
				jQuery( '._woo_vou_start_date_field' ).hide();
				$('._woo_vou_days_diff_field').css('display', 'block');
				
				var woo_vou_days_diff = jQuery('select[name=_woo_vou_days_diff] option:selected').val();
				if( woo_vou_days_diff == 'cust' ){
					jQuery( '._woo_vou_custom_days_field' ).show();
					jQuery( '.custom-desc' ).hide();
				}else{
					jQuery( '._woo_vou_custom_days_field' ).hide();
					jQuery( '.custom-desc' ).show();
				}
			}else if( exp_type == 'specific_date' ){
				
				jQuery( '._woo_vou_exp_date_field' ).show();
				jQuery( '._woo_vou_start_date_field' ).show();
				jQuery( '._woo_vou_days_diff_field' ).hide();
				jQuery( '._woo_vou_custom_days_field' ).hide();
			} else if( exp_type == 'default' ){
					
				jQuery( '._woo_vou_exp_date_field' ).hide();
				jQuery( '._woo_vou_start_date_field' ).hide();
				jQuery( '._woo_vou_days_diff_field' ).hide();
				jQuery( '._woo_vou_custom_days_field' ).hide();
				jQuery( '.custom-desc' ).hide();
			}
		} else {
	
			jQuery( '._woo_vou_exp_date_field' ).hide();
			jQuery( '._woo_vou_start_date_field' ).hide();
			jQuery( '._woo_vou_days_diff_field' ).hide();
			jQuery( '._woo_vou_custom_days_field' ).hide();
			jQuery( '.custom-desc' ).hide();
		}
	}

	woo_vou_toggle_coupon_tab();
	$(document).on('change', "#_woo_vou_enable_coupon_code",function(){
		woo_vou_toggle_coupon_tab();
	});

	function woo_vou_toggle_coupon_tab(){

		$('li.woo-vou-tabination-wrapper.woo-vou-tab-wccoupon').hide();
		if((WooVouMeta.enable_coupon_code === 'yes' && $("#_woo_vou_enable_coupon_code option:selected").val() !== 'no')
			|| ($("#_woo_vou_enable_coupon_code option:selected").val() === 'yes')){
	
			$('li.woo-vou-tabination-wrapper.woo-vou-tab-wccoupon').show();
		}
	}
	
	//added for  WC Vendors pro compatibility 
	 $('.wcv-pro-dashboard ._woo_vou_vendor_user_field  .woo-vou-meta-select').select2();	
	 $('.wcv-pro-dashboard ._woo_vou_sec_vendor_users_field .woo-vou-meta-select').select2();	
});