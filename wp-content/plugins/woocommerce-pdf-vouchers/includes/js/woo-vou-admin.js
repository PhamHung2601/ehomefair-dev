"use strict";

jQuery( document ).ready( function( $ ) {
	
	//Hide Save Changes bottom in WooCommerce Add-on section		
	if(WooVouAdminSettings.is_addon == "vou_addon"){
		$('p.submit button.woocommerce-save-button').hide();
	}
	
		
	if( $('input#post_type').length && $('input#post_type').val() == 'woovouchers' ) {

		$(document).on("DOMNodeInserted", function () {
	        // only allow images
	        $('select.attachment-filters [value="image"]').attr('selected', true).parent().trigger('change');
	    });
	}

	//Media Uploader
	$( document ).on( 'click', '.woo-vou-upload-button', function() {
	
		var imgfield,showfield;
		imgfield = jQuery(this).prev('input').attr('id');
		showfield = jQuery(this).parents('td').find('.woo-vou-img-view');
    	
		if(typeof wp == "undefined" || WooVouAdminSettings.new_media_ui != '1' ){// check for media uploader
				
			tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
	    	
			window.original_send_to_editor = window.send_to_editor;
			window.send_to_editor = function(html) {
				
				if(imgfield)  {
					
					var mediaurl = $('img',html).attr('src');
					$('#'+imgfield).val(mediaurl);
					showfield.html('<img src="'+mediaurl+'" />');
					tb_remove();
					imgfield = '';
					
				} else {
					
					window.original_send_to_editor(html);
					
				}
			};
	    	return false;
			  
		} else {
			
			var file_frame;
			
			//new media uploader
			var button = jQuery(this);
		
			// If the media frame already exists, reopen it.
			if ( file_frame ) {
				file_frame.open();
			  return;
			}
	
			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
				frame: 'post',
				state: 'insert',
				multiple: false  // Set to true to allow multiple files to be selected
			});
	
			file_frame.on( 'menu:render:default', function(view) {
		        // Store our views in an object.
		        var views = {};
	
		        // Unset default menu items
		        view.unset('library-separator');
		        view.unset('gallery');
		        view.unset('featured-image');
		        view.unset('embed');
	
		        // Initialize the views in our view object.
		        view.set(views);
		    });
	
			// When an image is selected, run a callback.
			file_frame.on( 'insert', function() {
	
				// Get selected size from media uploader
				var selected_size = $('.attachment-display-settings .size').val();
				
				var selection = file_frame.state().get('selection');
				selection.each( function( attachment, index ) {
					attachment = attachment.toJSON();
					
					// Selected attachment url from media uploader
					var attachment_url = attachment.sizes[selected_size].url;
					var attachment_id = attachment.id;
					
					if(index == 0){
						// place first attachment in field
						$('#'+imgfield).val(attachment_url);
						showfield.html('<img src="'+attachment_url+'" />');
						
					} else{
						$('#'+imgfield).val(attachment_url);
						showfield.html('<img src="'+attachment_url+'" />');
					}
				});
			});
	
			// Finally, open the modal
			file_frame.open();			
		}
		
	});

	//Media Uploader
	$( document ).on( 'click', '.woo-vou-upload-preview-button', function() {
	
		var imgfield,showfield;
		imgfield = jQuery(this).prev('input').attr('id');
		showfield = jQuery(this).parents('td').find('.woo-vou-preview-img-view');
    	
		if(typeof wp == "undefined" || WooVouAdminSettings.new_media_ui != '1' ){// check for media uploader
				
			tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
	    	
			window.original_send_to_editor = window.send_to_editor;
			window.send_to_editor = function(html) {
				
				if(imgfield)  {
					
					var mediaurl = $('img',html).attr('src');
					mediaurl = mediaurl.replace(WooVouAdminSettings.upload_base_url,'');
					$('#'+imgfield).val(mediaurl);
					showfield.html('<img src="'+mediaurl+'" />');
					tb_remove();
					imgfield = '';
					
				} else {
					
					window.original_send_to_editor(html);
					
				}
			};
	    	return false;
			  
		} else {
			
			var file_frame;
			
			//new media uploader
			var button = jQuery(this);
		
			// If the media frame already exists, reopen it.
			if ( file_frame ) {
				file_frame.open();
			  return;
			}
	
			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
				frame: 'post',
				state: 'insert',
				multiple: false  // Set to true to allow multiple files to be selected
			});
	
			file_frame.on( 'menu:render:default', function(view) {
		        // Store our views in an object.
		        var views = {};
	
		        // Unset default menu items
		        view.unset('library-separator');
		        view.unset('gallery');
		        view.unset('featured-image');
		        view.unset('embed');
	
		        // Initialize the views in our view object.
		        view.set(views);
		    });
	
			// When an image is selected, run a callback.
			file_frame.on( 'insert', function() {
	
				// Get selected size from media uploader
				var selected_size = $('.attachment-display-settings .size').val();
				
				var selection = file_frame.state().get('selection');
				selection.each( function( attachment, index ) {
					attachment = attachment.toJSON();
					
					// Selected attachment url from media uploader
					var attachment_url = attachment.sizes[selected_size].url;
					var attachment_id = attachment.id;
					
					if(index == 0){
						// place first attachment in field
						showfield.html('<img src="'+attachment_url+'" height="200" width="200" />');
						attachment_url = attachment_url.replace(WooVouAdminSettings.upload_base_url,'');
						$('#'+imgfield).val(attachment_url);

					} else{
						showfield.html('<img src="'+attachment_url+'" height="200" width="200" />');
						attachment_url = attachment_url.replace(WooVouAdminSettings.upload_base_url,'');
						$('#'+imgfield).val(attachment_url);
					}
				});
			});
	
			// Finally, open the modal
			file_frame.open();			
		}
		
	});
	
	
	// Setting page toggle for guest user reddeem voucher code
	function woo_vou_toggle_guest_user_redeem_vou_code_option() {
		
		if( $("#vou_enable_guest_user_check_voucher_code").is(':checked') ) {
			$("#woo_vou_guest_user_allow_redeem_voucher").parents('tr').fadeIn();
		} else {
			$("#woo_vou_guest_user_allow_redeem_voucher").parents('tr').fadeOut();
		}
	}
	
	// Setting page onload show/hide logged user redeem voucher code option
	woo_vou_toggle_guest_user_redeem_vou_code_option();
	
	// Setting page toggle logged user redeem voucher code on click logged user check voucher code checkbox
	$(document).on('click', "#vou_enable_guest_user_check_voucher_code", function() {		
		woo_vou_toggle_guest_user_redeem_vou_code_option();
	});
	
	
	// function to toggle remove voucher dowload link option
	function woo_vou_toggle_remove_voucher_download_link_option() {
		
		if( $("input[name='multiple_pdf']").is(':checked') ) {
			$("#revoke_voucher_download_link_access").parents('tr').fadeIn();
		} else {
			$("#revoke_voucher_download_link_access").parents('tr').fadeOut();
		}
	}

	// Setting page onload show/hide voucher password pattern option
	woo_vou_toggle_voucher_password_pattern_option();

	// Setting page toggle voucher password pattern on click voucher password checkbox
	$(document).on('click', "input[name='vou_enable_pdf_password_protected']", function(){
		woo_vou_toggle_voucher_password_pattern_option();
	});

	// function to toggle remove voucher dowload link option
	function woo_vou_toggle_voucher_password_pattern_option() {

		if( $("input[name='vou_enable_pdf_password_protected']").is(':checked') ) {
			$("#vou_pdf_password_pattern").parents('tr').fadeIn();
		} else {
			$("#vou_pdf_password_pattern").parents('tr').fadeOut();
		}
	}

	// Setting page onload show/hide remove voucher download link option
	woo_vou_toggle_remove_voucher_download_link_option();
	
	// Setting page toggle remove voucher download link on click multiple voucher checkbox
	$(document).on('click', "input[name='multiple_pdf']", function() {
		woo_vou_toggle_remove_voucher_download_link_option();
	});

	// function to toggle logged user redeem voucher code option
	function woo_vou_toggle_logged_user_redeem_vou_code_option() {
		
		if( $("input[name='vou_enable_logged_user_check_voucher_code']").is(':checked') ) {
			$("#vou_enable_logged_user_redeem_vou_code").parents('tr').fadeIn();
		} else {
			$("#vou_enable_logged_user_redeem_vou_code").parents('tr').fadeOut();
		}
	}
	
	// Setting page onload show/hide logged user redeem voucher code option
	woo_vou_toggle_logged_user_redeem_vou_code_option();
	
	// Setting page toggle logged user redeem voucher code on click logged user check voucher code checkbox
	$(document).on('click', "input[name='vou_enable_logged_user_check_voucher_code']", function() {
		woo_vou_toggle_logged_user_redeem_vou_code_option();
	});

	// Disable coupon fields if coupon is 'voucher_code' type
	if ( WooVouAdminSettings.coupon_type == 'voucher_code' ) {
		
		$('#discount_type').prop('disabled', true);			// Disable Cart Discount type
		$('#coupon_amount').prop('disabled', true);         // Disable Coupon Amount
		$('#usage_limit').prop('disabled', true);           // Disable Usage Limit
		$('#expiry_date').prop('disabled', true);           // Disable expiry date field
		$('#_woo_vou_start_date').prop('disabled', true);   // Disable start date field
		$('#disable_redeem_days').prop('disabled', true);   // Disable product restriction days field
		$('#_woo_vou_coupon_type').prop('disabled', true);  // Disable coupon type field
		$('#minimum_amount').prop('disabled', true);  // Disable coupon mimimum amount
		$('#maximum_amount').prop('disabled', true);  // Disable coupon Maximum amount
		
		$('#usage_restriction_coupon_data .wc-product-search').prop('disabled', true);  // Disable coupon accessible product field
		$('#usage_restriction_coupon_data .wc-product-search[name="exclude_product_ids[]"]').prop('disabled', true);  // Disable coupon not accessible product field
		$('#usage_restriction_coupon_data #product_categories').prop('disabled', true);  // Disable coupon accessible product field
		$('#usage_restriction_coupon_data #exclude_product_categories').prop('disabled', true);  // Disable coupon not accessible product field
	}
	woo_vou_toggle_exp_type();
	$(document).on("change","#vou_exp_type",function(){

    	woo_vou_toggle_exp_type();
	});

	
	$(document).on('change', '#vou_days_diff', function(){

		woo_vou_toggle_days_no();
	});

	jQuery('.woo-vou-meta-datetime').each( function() {

		var jQuerythis  = jQuery(this),
	    format = jQuerythis.attr('rel'),
	    id = jQuerythis.attr('id');

	  	if( id == 'vou_start_date' || id == 'vou_exp_date' ) {
			var start_date = jQuery('#vou_start_date');
		  	var expire_date = jQuery('#vou_exp_date');
	  		$.timepicker.datetimeRange(
	  			start_date,
	  			expire_date,
				{
					minInterval: (1000*60*60), // 1hr
					ampm: true,
					dateFormat : format,
					timeFormat: 'HH:mm',
				}
			);
	  	} else if( id == '_woo_vou_start_date' || id == '_woo_vou_exp_date' ) {
	  		var start_date = jQuery('#_woo_vou_start_date');
		  	var expire_date = jQuery('#_woo_vou_exp_date');
	  		$.timepicker.datetimeRange(
	  			start_date,
	  			expire_date,
				{
					minInterval: (1000*60*60), // 1hr
					ampm: true,
					dateFormat : format,
					timeFormat: 'HH:mm',
				}
			);
	  	} else if( id == '_woo_vou_product_start_date' || id == '_woo_vou_product_exp_date' ) {
	  		var start_date = jQuery('#_woo_vou_product_start_date');
		  	var expire_date = jQuery('#_woo_vou_product_exp_date');
	  		$.timepicker.datetimeRange(
	  			start_date,
	  			expire_date,
				{
					minInterval: (1000*60*60), // 1hr
					ampm: true,
					dateFormat : format,
					timeFormat: 'HH:mm',
				}
			);
	  	} else {  	        	
	      	jQuerythis.datetimepicker({ampm: true,dateFormat : format });//,timeFormat:'hh:mm:ss',showSecond:true
  	  	}
	});

	function woo_vou_toggle_exp_type(){

		var vou_type = $("#vou_exp_type").val();
		if(vou_type == 'specific_date'){

			$("#vou_exp_type").parents('tr').show();
			$("#vou_exp_type").parents('tr').next().show();
			$("#vou_exp_type").parents('tr').next().next().show();
			$("#vou_exp_type").parents('tr').next().next().next().hide();
			$("#vou_exp_type").parents('tr').next().next().next().next().hide();
		} else {

			$("#vou_exp_type").parents('tr').show();
			$("#vou_exp_type").parents('tr').next().hide();
			$("#vou_exp_type").parents('tr').next().next().hide();
			$("#vou_exp_type").parents('tr').next().next().next().show();
			woo_vou_toggle_days_no();
		}
	}

	function woo_vou_toggle_days_no(){

		var days_diff = $('#vou_days_diff').val();
		$('#vou_days_diff').parents('tr').next().hide();
		if(days_diff == 'cust'){

			$('#vou_days_diff').parents('tr').next().show();
		}
	}
	
	$( document ).on( 'click', '.view_redeem_info > .woo-vou-code-expiry-date', function() {
		
		var woo_voucher_id = $( this ).data( 'voucherid' );
		var data = {
					action		: 'woo_vou_get_voucher_expiry_date',
					voucher_id	: woo_voucher_id,
					ajax		: true,
				};

		//call ajax to chcek voucher code
		jQuery.post( WooVouAdminSettings.ajaxurl, data, function( response ) {
			
			var response_data = jQuery.parseJSON(response);
			// Check if response will be success
			if( response_data['success'] ){

				// Declare variables
				var woo_voucher_id 			= response_data['voucher_id'];
				var woo_voucher_code 		= response_data['purchased_codes'];
				var woo_voucher_start_date 	= ( response_data['start_date'].length != 0 )? response_data['start_date']: 0;
				var woo_voucher_expiry_date = response_data['exp_date'];
				
				$('#woo_vou_voucher_expiry_date .woo-vou-voucher-code').html( woo_voucher_code );
				$('#woo_vou_voucher_expiry_date #woo_voucher_id').val( woo_voucher_id );
				$('#woo_vou_voucher_expiry_date #woo_vou_exp_datetime').val( woo_voucher_expiry_date );

				// Date Time picker Field
				$('#woo_vou_voucher_expiry_date #woo_vou_exp_datetime').each( function() {
			      
					var jQuerythis  = jQuery(this),
						format = jQuerythis.attr('rel');
						
						jQuerythis.datetimepicker({
							
							dateFormat : format,
							timeFormat: "hh:mm tt",
							changeMonth: true,
							changeYear: true,
							minDate: woo_voucher_start_date,
							yearRange: "-100:+20",
							showTime: false,
						});
			    });

				$('.woo-vou-popup-content.woo-vou-expiry-date-content').fadeIn();
				$('.woo-vou-popup-overlay.woo-vou-expiry-date-overlay').show();
			}
		});
	});

	// Attribute ordering. JS for moving blocks horizontally
    // Code from WC
    $('.woo-vou-recipient-detail-wraps').sortable({
        items: '.woo-vou-recipient-detail:not(:last-child)',
        cursor: 'move',
        axis: 'y',
        handle: 'h3',
        scrollSensitivity: 40,
        forcePlaceholderSize: true,
        helper: 'clone',
        opacity: 0.65,
        placeholder: 'wc-metabox-sortable-placeholder',
        start: function (event, ui) {
            ui.item.css('background-color', '#f6f6f6');
        },
        stop: function (event, ui) {
            ui.item.removeAttr('style');
            woo_vou_recipient_indexes();
        }
    });
	
	//on click of close button or overlay
	$( document ).on( "click", ".woo-vou-popup-overlay.woo-vou-expiry-date-overlay, .woo-vou-expiry-date-content .woo-vou-close-button", function() {
		
		//common code for both popup of voucher codes used and import csv file
		$('.woo-vou-popup-content.woo-vou-expiry-date-content').hide();
		$('.woo-vou-popup-overlay.woo-vou-expiry-date-overlay').hide();
		$('#woo_vou_voucher_expiry_date .woo-vou-voucher-code').html( '' );
		$('#woo_vou_voucher_expiry_date #woo_voucher_id').val( '' );
		$('#woo_vou_voucher_expiry_date #woo_vou_exp_datetime').val( '' );
		$('.woo-vou-expiry-date-content .woo-vou-expiry-errors').hide();
		$('.woo-vou-expiry-date-content .woo-vou-expiry-errors').html('');
		$('.woo-vou-expiry-date-content .woo-vou-expiry-errors').removeClass('woo-vou-expiry-success');
	});
	
	//on click of change expiry date button
	$( document ).on( "click", ".woo-vou-expiry-date-content .woo-vou-voucher-expiry-btn", function() {
		
		var woo_voucher_expiry_date = $('#woo_vou_voucher_expiry_date #woo_vou_exp_datetime').val();
		var woo_voucher_id = $('#woo_vou_voucher_expiry_date #woo_voucher_id').val();
		var data = {
						action				: 'woo_vou_change_voucher_expiry_date',							
						voucher_expiry_date	: woo_voucher_expiry_date,
						voucher_id			: woo_voucher_id,
						ajax				: true,
					};
		//call ajax to change voucher code expiry date
		jQuery.post( WooVouAdminSettings.ajaxurl, data, function( response ) {
			
			var response_data = jQuery.parseJSON(response);
			if( response_data['success'] ){
				$('.woo-vou-expiry-date-content .woo-vou-expiry-errors').html( response_data['success_msg'] );
				$('.woo-vou-expiry-date-content .woo-vou-expiry-errors').addClass( 'woo-vou-expiry-success' );
			} else {
				$('.woo-vou-expiry-date-content .woo-vou-expiry-errors').html( response_data['error_msg'] );
				$('.woo-vou-expiry-date-content .woo-vou-expiry-errors').removeClass( 'woo-vou-expiry-success' );
			}
			$('.woo-vou-expiry-date-content .woo-vou-expiry-errors').show();
			setTimeout(function(){
				location.reload();
			 }, 1000);
			
		});
	});

	$('#vou_partial_redeem_product_ids').parent().parent('tr').hide(); // hide the product ids input field
	
	//on click of open the popup
	$( document ).on( "click", ".woo-vou-select-part-redeem-product", function(e) {

		e.preventDefault();

		var vou_enable_partial_redeem = $("#vou_enable_partial_redeem").prop("checked");
		if( vou_enable_partial_redeem ){
			$('.woo-vou-popup-content.woo-vou-product-partial-codes-popup').addClass('woo-vou-partial-disable-popup');
		} else {
			$('.woo-vou-popup-content.woo-vou-product-partial-codes-popup').removeClass('woo-vou-partial-disable-popup');
		}
		$('.woo-vou-popup-content.woo-vou-product-partial-codes-popup').show();
		$('.woo-vou-popup-overlay.woo-vou-product-partial-codes-popup').show();
	});

	// on click close button or popup overlay section to hide popup
	$( document ).on( "click", ".woo-vou-popup-overlay.woo-vou-product-partial-codes-popup, .woo-vou-product-partial-codes-popup .woo-vou-close-button", function() {
		$('.woo-vou-popup-content.woo-vou-product-partial-codes-popup').hide();
		$('.woo-vou-popup-overlay.woo-vou-product-partial-codes-popup').hide();
		woo_vou_product_partial_count();
	});
	// on click check all enable all product checkboxes
	$( document ).on( "click", ".woo_vou_product_partial_submit .woo_vou_checkall_products", function() {
		$('.woo-vou-product-partial-input').prop('checked', true);
		$('.woo-vou-product-partial-input').trigger('change');
	});

	// on click uncheck all desable all product checkboxes
	$( document ).on( "click", ".woo_vou_product_partial_submit .woo_vou_uncheckall_products", function() {
		$('.woo-vou-product-partial-input').prop('checked', false);
		$('.woo-vou-product-partial-input').trigger('change');
	});

	// on click done save selected product checkboxes
	$( document ).on( "click", ".woo-vou-product-partial-codes-popup #woo_vou_set_submit_indivisual", function() {

		var woo_vou_partial_product_ids = $.map(  $('input[name="woo_vou_product_partial[]"]:checked' ), function(e,i) {
		    return +e.value;
		});
		
		$('#vou_partial_redeem_product_ids').val(woo_vou_partial_product_ids);
		$('.woo-vou-popup-content.woo-vou-product-partial-codes-popup').hide();
		$('.woo-vou-popup-overlay.woo-vou-product-partial-codes-popup').hide();
		woo_vou_product_partial_count();
	    $('html, body').animate({
	        scrollTop: $("form#mainform").offset().top
	    });
	});

	// Count the selected product for partial redeem
	woo_vou_product_partial_count();
	function woo_vou_product_partial_count(){
		var total_selected_product = $( '.woo-vou-product-partial-input[name="woo_vou_product_partial[]"]:checked' ).length;
		$(".woo-vou-part-redeem-product-count").html($('#woo_vou_total_selected_products').val());
	}

	woo_vou_check_uncheck_parent_checkbox();

	// on change parent product checkboxe then changes on it's child variation
	$( document ).on( "change", ".woo-vou-product-partial-codes-popup .woo-vou-product-variation-parent", function() {
		var woo_vou_product_variation = $(this).val();
		if( $(this).prop("checked") ){
			$('.woo-vou-product-partial-input.woo-vou-product-parent-'+woo_vou_product_variation).prop('checked', true);
		} else {
			$('.woo-vou-product-partial-input.woo-vou-product-parent-'+woo_vou_product_variation).prop('checked', false);
		}
		$(this).next().next().find('.woo-vou-toggle-variations.woo-vou-plus').trigger( "click" );
	}); 
	
	// on change parent product checkboxe then changes on it's child variation
	$( document ).on( "change", '.woo-vou-product-partial-codes-popup .woo-vou-product-variation', function() {
		var woo_vou_product_variation_parent = $(this).parent().parent().parent().find('.woo-vou-product-variation-parent');

		if( $(this).prop("checked") ){
			var woo_vou_product_parent_checked = true;
			$('.woo-vou-product-partial-input.woo-vou-product-parent-'+woo_vou_product_variation_parent.val()).each(function () {
				if ( $(this).prop("checked") == false ) {
					woo_vou_product_parent_checked = false;
				}
				woo_vou_product_variation_parent.prop('checked', woo_vou_product_parent_checked);
			});
		} else {
			woo_vou_product_variation_parent.prop('checked', false);
		}
	}); 

	// Handles to search products based on search criteria when search button is clicked
	$(document).on('click', 'input#woo_vou_search_product_by_btn', function(e){
		e.preventDefault();

		var search_by = $('input#woo_vou_search_product_by').val(); // Get search criteria
		var total_selected_pros = $('#woo_vou_selected_products').val(); // Get total selected products

		// Show loader and overlay before firing AJAX
		$('.woo-vou-partial-redeem-popup-loader, .woo-vou-partial-redeem-popup-overlay').show();
		// Collecting data for ajax
		var data = {
						action		: 'woo_vou_load_products',
						search_by	: search_by,
						total_selected_pros : total_selected_pros
					};
		//call ajax to load more products
		jQuery.post( WooVouAdminSettings.ajaxurl, data, function( response ) {

			$('.woo-vou-partial-redeem-popup-loader, .woo-vou-partial-redeem-popup-overlay').hide(); // Hide loader and overlay
			$('div.woo-vou-popup').html(response); // Replace response in popup html
			$('#woo_vou_current_page').val(1); // Change pagination to 1 so as we can load more
			$('input#woo_vou_load_more_btn').show(); // Show load more button in case it is hide
			woo_vou_check_uncheck_parent_checkbox();
			woo_vou_hide_load_more_btn(); // hide load more button
		});
	});

	// Handles to load more products
	$(document).on('click', 'input#woo_vou_load_more_btn',function(e){
		e.preventDefault();

		var search_by = $('input#woo_vou_search_product_by').val(); // Get search string
		var total_selected_pros = $('#woo_vou_selected_products').val(); // Get selected products
		var page = parseInt($('#woo_vou_current_page').val())+1; // Get current page and increment by 1 for next page

		$('.woo-vou-partial-redeem-popup-loader, .woo-vou-partial-redeem-popup-overlay').show(); // Show loader and overlay
		// Collecting data for load more ajax
		var data = {
						action		: 'woo_vou_load_products',
						search_by	: search_by,
						page		: page,
						total_selected_pros : total_selected_pros
					};
		//call ajax to change voucher code expiry date
		jQuery.post( WooVouAdminSettings.ajaxurl, data, function( response ) {

			$('.woo-vou-partial-redeem-popup-loader, .woo-vou-partial-redeem-popup-overlay').hide(); // Hide loader and AJAX
			// If there are no more products
			if(response.indexOf("woo-vou-no-more-products") > -1) {

				$('input#woo_vou_load_more_btn').hide(); // Hide Load More button
			} else { // Else

				// Append response
				$('div.woo-vou-popup').append(response);
				$('#woo_vou_current_page').val(page);
				woo_vou_check_uncheck_parent_checkbox();
				woo_vou_hide_load_more_btn(); // hide load more button
			}
		});
	});

	// On click of any checkbox in popup
	$(document).on( 'change', '.woo-vou-product-partial-input', function(){

		var total_selected_pros = $('#woo_vou_selected_products').val(); // Get selected product ids
		var selected_pro_arr = total_selected_pros.split(','); // Get selected product array
		var inputs_ticked = $(this).val(); // Get checkbox value

		// If selected checkbox is parent of variation product
		if($(this).hasClass('woo-vou-product-variation-parent')){
			var inputs_ticked = '';
			// Loop on all variations and generate string containing all values variants
			$(this).siblings('ul.woo-vou-product-variation-list').find('li').each(function(){
				if($.trim(inputs_ticked)){
					inputs_ticked = inputs_ticked + ',' + $(this).find('input.woo-vou-product-partial-input').val();
				} else {
					inputs_ticked = $(this).find('input.woo-vou-product-partial-input').val();
				}
			});
		}

		// If checkbox is checked
		if($(this).is(':checked')){

			var inputs_ticked_arr = inputs_ticked.split(','); // Split string to get array
			$.each(inputs_ticked_arr, function(index, value){ // Loop on array
				index = $.inArray(value, selected_pro_arr); // Check if value exists in array
				if(index == -1){ // If value not exist than add value in array

					// If value is not empty
					if($.trim($('#woo_vou_selected_products').val())) {
						$('#woo_vou_selected_products').val($('#woo_vou_selected_products').val()+','+value); // Append value
					} else {
						$('#woo_vou_selected_products').val(value); // Add new value
					}
					// Increase total counter
					$('#woo_vou_total_selected_products').val(parseInt($('#woo_vou_total_selected_products').val())+1);
				}
			});
		} else { // If not checked

			var inputs_ticked_arr = inputs_ticked.split(','); // Split string to array
			$.each(inputs_ticked_arr, function(index, value){ // Loop on ticked checkbox array
				index = $.inArray(value, selected_pro_arr); // If it is available in selected product array
				if (index > -1) {
				    selected_pro_arr.splice(index, 1); // Remove it from selected product array
				    $('#woo_vou_total_selected_products').val(parseInt($('#woo_vou_total_selected_products').val())-1); // Decrease total counter
				}
			});
			$('#woo_vou_selected_products').val(selected_pro_arr.join(',')); // Implode string and uodate input field
		}
	});

	// On click of woo-vou-show-variation show product variations
	$(document).on( 'click', '.woo-vou-toggle-variations', function(e){
		e.preventDefault();

		if($(this).hasClass('woo-vou-minus')) {
			$(this).removeClass('woo-vou-minus').addClass('woo-vou-plus');
		} else {
			$(this).removeClass('woo-vou-plus').addClass('woo-vou-minus');
		}
		$(this).parent().parent().find('ul.woo-vou-product-variation-list').toggle();
	});

	function woo_vou_check_uncheck_parent_checkbox(){

		$('.woo-vou-product-partial-codes-popup .woo-vou-product-variation').each(function () {

			var woo_vou_product_variation_parent = $(this).parent().parent().parent().find('.woo-vou-product-variation-parent');
			var woo_vou_product_parent_checked = true;
			if( $(this).prop("checked") ){
				$('.woo-vou-product-partial-input.woo-vou-product-parent-'+woo_vou_product_variation_parent.val()).each(function () {
					if ( $(this).prop("checked") == false ) {
						woo_vou_product_parent_checked = false;
					}
					woo_vou_product_variation_parent.prop('checked', woo_vou_product_parent_checked);
				});
			} else {
				woo_vou_product_variation_parent.prop('checked', false);
			}
		});
	}
	
	woo_vou_hide_load_more_btn(); // hide load more button

	// Count the last product list section li, it lessthan 20 then hide load more button
	function woo_vou_hide_load_more_btn(){

		if( $('.woo-vou-product-list:last-child > ul > li').length < 20 ){
			$('input#woo_vou_load_more_btn').hide(); // Hide Load More button
		}
	}

	function woo_vou_recipient_indexes(){
		$('.woo-vou-recipient-detail-wraps .woo-vou-recipient-detail').each(function (index, el) {
            $('.woo_vou_recipient_detail_order', el).attr('name', '_woo_vou_recipient_detail_order['+parseInt($(el).index('.woo-vou-recipient-detail-wraps .woo-vou-recipient-detail'), 10)+']');
        });
	}
	
	$( document ).on( 'click', '.column-code_details > .woo-vou-code-redeem', function() {
		
		var woo_voucher_id = $( this ).data( 'voucherid' );
		var data = {
					action		: 'woo_vou_voucher_redeem_popup',
					voucher_id	: woo_voucher_id,
					ajax		: true,
				};

		//call ajax to chcek voucher code
		jQuery.post( WooVouAdminSettings.ajaxurl, data, function( response ) {
			
			var response_data = jQuery.parseJSON(response);
				
			// Check if response will be success
			if( response_data['success_status'] ){
				
				
					
				// Declare variables
				var woo_voucher_id 			= response_data['voucher_id'];
				var woo_voucher_code 		= response_data['voucher_code'];
				var woo_voucher_price       = response_data['price'];
				var woo_vou_redeem_method   = response_data['redeem_method'];
				var woo_vou_redeem_amount   = response_data['redeem_amount'];
				var woo_voucher_redeem_msg  = response_data['success'];
				var woo_voucher_error  		= response_data['error'];
				
				if( woo_voucher_error ) {
					$('.woo-vou-code-redeem-submit-wrap').hide();
					$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-success' ).addClass( 'woo-vou-voucher-code-error' );
				} else  {
					$('.woo-vou-code-redeem-submit-wrap').show();
					$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-error' ).addClass( 'woo-vou-voucher-code-success' );
				}

				$('.woo-vou-partial-redeem-amount').hide();
				$('#woo_vou_voucher_redeem .woo-vou-voucher-code').html( woo_voucher_code );
				$('#woo_vou_voucher_redeem #woo_voucher_code').val( woo_voucher_code );
				$('#woo_vou_voucher_redeem .woo-vou-voucher-code-msg').html( woo_voucher_redeem_msg );
				$('#woo_vou_voucher_redeem #woo_voucher_id').val( woo_voucher_id );
				$('#woo_vou_voucher_redeem .woo-vou-voucher-price').html( woo_voucher_price );
				$('#woo_vou_voucher_redeem .woo-vou-voucher-redeem-method').html( woo_vou_redeem_method );
				$('#woo_vou_voucher_redeem .woo-vou-voucher-redeem-amount').html( woo_vou_redeem_amount );

				$('.woo-vou-popup-content.woo-vou-voucher-redeem-content').fadeIn();
				$('.woo-vou-popup-overlay.woo-vou-voucher-redeem-overlay').show();
			}
		});
		return false;
	});

	// Submit Voucher code ( Redeem vocher code )
	$( document ).on( 'click', '#woo_vou_voucher_redeem #woo_vou_voucher_code_redeem', function() {

		//Voucher Code
		var voucode = $( '#woo_voucher_code' ).val();
		var vou_enable_partial_redeem = $('#vou_enable_partial_redeem').val();

		if( ( voucode != '' && voucode != 'undefine' ) ) {

			var redeem_amount = '';
			var redeem_method = '';
			var total_price = '';
			var redeemed_price = '';
			var remaining_redeem_price = '';

			total_price				= woo_vou_number_check($('#vou_code_total_price').val());
			redeemed_price			= woo_vou_number_check($('#vou_code_total_redeemed_price').val());
			remaining_redeem_price 	= woo_vou_number_check($('#vou_code_remaining_redeem_price').val());

			// check partial redeem is enabled
			if( vou_enable_partial_redeem == "yes" ) {

				// get redeem amount and redeem method
				redeem_amount			= woo_vou_number_check($('#vou_partial_redeem_amount').val());
				redeem_method 			= $('#vou_redeem_method').val();

				// redeem amount validation
				if( redeem_method == 'partial' && ( redeem_amount == '' || isNaN( redeem_amount ) ) ) {

					$('.woo-vou-partial-redeem-amount .woo-vou-voucher-code-error').html( WooVouAdminSettings.redeem_amount_empty_error ).fadeIn();
					return false;
				} else if( redeem_method == 'partial' && redeem_amount > remaining_redeem_price ) {

					$('.woo-vou-partial-redeem-amount .woo-vou-voucher-code-error').html( WooVouAdminSettings.redeem_amount_greaterthen_redeemable_amount ).fadeIn();
					return false;
				}
			}

			//hide error message
			$( '#woo_vou_voucher_redeem .woo-vou-loader' ).hide();

			//show loader
			$( '#woo_vou_voucher_redeem .woo-vou-loader' ).css( 'display', 'inline' );

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
			jQuery.post( WooVouAdminSettings.ajaxurl, data, function( response ) {

				var response_data = jQuery.parseJSON(response);

				if( response_data.success ) {

					//hide submit row
					$( '.woo-vou-code-redeem-submit-wrap' ).fadeOut();
					$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-error' ).addClass( 'woo-vou-voucher-code-success' ).html( WooVouAdminSettings.code_used_success ).show();
				} else {

					$( '.woo-vou-voucher-code-msg' ).removeClass( 'woo-vou-voucher-code-success' ).addClass( 'woo-vou-voucher-code-error' ).html( response_data.error_message ).show();
				}
				//hide loader
				$( '#woo_vou_voucher_redeem .woo-vou-loader' ).hide();
				setTimeout(function() { window.location.reload(); }, 1000);
			});
		}
	});

    function woo_vou_number_check(number){
    	number = Math.round(number * 100) / 100;
    	var pattern = /^\d+(.\d{1,2})?$/;
    	return pattern.test(number)?number:'';
    }

    if( !$('#vou_enable_voucher_preview').is(":checked") ){
		$('#vou_enable_voucher_preview').parents('tr').next().hide();
		var preview_img = $('#vou_preview_image').parents('tr');
		$(preview_img).hide();
	} 

	$(document).on('change', '#vou_enable_voucher_preview', function(){
		if( $(this).is(":checked")){
			$('#vou_enable_voucher_preview').parents('tr').next().show();
			$('#vou_enable_voucher_preview').parents('tr').next().next().show();
		} else{
			$('#vou_enable_voucher_preview').parents('tr').next().hide();
			$('#vou_enable_voucher_preview').parents('tr').next().next().hide();
		}

	});

	$(".column-code_details .woo-vou-action-button").tipTip({
		fadeIn:50,
		fadeOut:50,
		delay:200
	});
	
	
	
	// Show hide set Usage Limit filed

	if( !$('#vou_allow_unlimited_redeem_vou_code').is(":checked") || WooVouAdminSetOpt.is_partial_option == 'yes' ){
		$('#vou_allow_unlimited_redeem_vou_code').parents('tr').next().hide();
		$('#vou_allow_unlimited_limit_vou_code').val('');
		//Hide code
	} 

	$(document).on('change', '#vou_allow_unlimited_redeem_vou_code', function(){
		
		if( $(this).is(":checked") && WooVouAdminSetOpt.is_partial_option != 'yes' ){
			$('#vou_allow_unlimited_redeem_vou_code').parents('tr').next().show();
		} else{
			$('#vou_allow_unlimited_redeem_vou_code').parents('tr').next().hide();
		} 

	});
	
});