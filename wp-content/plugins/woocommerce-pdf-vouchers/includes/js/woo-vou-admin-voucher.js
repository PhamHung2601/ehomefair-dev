"use strict";

jQuery( document ).ready( function( $ ) {
	
	var pagevoucher	= $('#woo_vou_page_voucher');
	var postdiv		= $('#postdivrich');
	pagevoucher.hide();

	if( $('input#post_type').length && $('input#post_type').val() == 'woovouchers' ) {
		
		var settings 	= {};
		var options 	= { portal 			: "columns",
							editorEnabled 	: true};
		var data 		= {};

		var portal;

		Event.observe(window, "load", function() {
			portal = new Portal(settings, options, data);
		});

		if( WooVouTranObj.wp_version >= 3.5 ){
			var inputcolor = $('.woo-vou-meta-color-iris').prev('input').val();
			$('.woo-vou-meta-color-iris').prev('input').css('background-color',inputcolor);
			$(document).on('click','.woo-vou-meta-color-iris',function(e) {
				colorPicker = $(this).next('div');
				input = $(this).prev('input');
				$.farbtastic($(colorPicker), function(a) { $(input).val(a).css('background', a); });
				colorPicker.show();
				e.preventDefault();
				$(document).mousedown( function() { $(colorPicker).hide(); });
			});
		} else{
			$('.woo-vou-meta-color-iris').wpColorPicker();
		}


	}
	
	if( $( '#woo_vou_page_voucher' ).length == 1 ) {
		$( 'div#titlediv' ).after( '<p class="woo_vou_page_voucher_button"><a href="javascript:void(0)" id="woo_vou_builder_switch">'+WooVouTranObj.offbuttontxt+'</a></p>' );
	}
	
	// On click of the button changing the editor  start
	$( document ).on( 'click', '#woo_vou_builder_switch', function() {
		if( postdiv.is( ":visible" ) ) {
			woo_vou_switch_default_editor_visual( 'content' );
			var editor_empty	= true;
			if ( typeof tinyMCE != "undefined" && tinyMCE.get( 'content' ).getContent() != '' ) {
					editor_empty	= false;
					var answer		= confirm ( WooVouTranObj.switchanswer );
					if( answer ) {
						editor_empty = true;
					}
			}
			if( editor_empty ) {
				
				postdiv.hide();
				pagevoucher.show();
				$( '#woo_vou_editor_status' ).val( 'true' );
				$( this ).html( WooVouTranObj.onbuttontxt );
				$( '.woo_vou_page_voucher_button' ).addClass( 'switch_active' );
				return false;
			}
		} else {
			
			postdiv.show();
			pagevoucher.hide();
			$( '#woo_vou_editor_status' ).val( 'false' );
			woo_vou_give_shortcode_to_editor();
			$( this ).html( WooVouTranObj.offbuttontxt );
			$( '.woo_vou_page_voucher_button' ).removeClass( 'switch_active' );
			return false;
			
		}
	});
	// On click of the button changing the editor end
	
	// On page load which pagevoucher will be showing start
	if( $( '#woo_vou_editor_status' ).val() == 'true' ) {
		
		pagevoucher.show();
		postdiv.hide();
		$( '#woo_vou_builder_switch' ).html(WooVouTranObj.onbuttontxt);
		$( '.woo_vou_page_voucher_button' ).addClass( 'switch_active' );
		
	} else {
		
		pagevoucher.hide();
		postdiv.show();
		$( '#woo_vou_builder_switch' ).html(WooVouTranObj.offbuttontxt);
		$( '.woo_vou_page_voucher_button' ).removeClass( 'switch_active' );
		
	}
	// On page load which pagevoucher will be showing end
	
	// On Click of edit will show editor start
	$( document ).on( 'click', '.woo_vou_change', function( pb ) {
		
		var element = jQuery(this).closest('.text_column');
		
		$( '.woo_vou_controls_editor' ).hide();
		$( '.woo_vou_main_editor' ).hide();
		$( '.woo_vou_editor' ).show();
		
		if( $( this ).hasClass( 'editcode' ) ) {
			
			var bg_color		= $(this).closest('.textblock').find('.woo_vou_text_bg').val();
			var font_color		= $(this).closest('.textblock').find('.woo_vou_text_font_color').val();
			var font_size		= $(this).closest('.textblock').find('.woo_vou_text_font_size').val();
			var text_align		= $(this).closest('.textblock').find('.woo_vou_text_text_align').val();
			var code_text_align	= $(this).closest('.textblock').find('.woo_vou_text_code_text_align').val();
			var code_border		= $(this).closest('.textblock').find('.woo_vou_text_code_border').val();
			var code_column		= $(this).closest('.textblock').find('.woo_vou_text_code_column').val();

			var content			= $(this).closest('.textblock').find('.woo_vou_text').html();
			var content_codes	= $(this).closest('.textblock').find('.woo_vou_text_codes').html();
			
			$('#woo_vou_edit_form').html('<div class="woo_vou_editor_heading"><h3><strong>'+WooVouTextBlock.textblocktitle+'</strong></h3></div><div class="woo_vou_editor_controls"><div class="editor_content"></div></div><div class="woo_vou_form_action"><input type="button" id="woo_vou_pbr_save" class="button-primary text_edit_save" name="save" value="'+WooVouTranObj.btnsave+'" /><input type="button" id="woo_vou_pbr_cancel" class="button-primary text_cancel" name="cancel" value="'+WooVouTranObj.btncancel+'" /></div>');
			
			var data = {
							action 			: 'woo_vou_page_builder',
							type			: 'textblock',
							editorid		: 'wpspbrtextblockedit',
							bgcolor			: bg_color,
							fontcolor		: font_color,
							fontsize		: font_size,
							textalign		: text_align,
							codetextalign	: code_text_align,
							codeborder		: code_border,
							codecolumn		: code_column,
						};
			
			jQuery.post( ajaxurl, data, function( response ) {
				
				jQuery('.editor_content').html(response);
				woo_vou_init_tiny_mce();
				tinyMCE.get('wpspbrtextblockedit').setContent(content);
				tinyMCE.get('wpspbrtextblockeditcodes').setContent(content_codes);
				
				$('#woo_vou_edit_bg_color').css('background-color',bg_color);
				$('#woo_vou_edit_font_color').css('color',font_color);
				
				woo_vou_set_colorpicker( $('#woo_vou_edit_bg_color') );
				woo_vou_set_colorpicker( $('#woo_vou_edit_font_color') );
				
				$('#woo_vou_edit_bg_color').val(bg_color);
				$('#woo_vou_edit_bg_color').css('background-color',bg_color);
				$('#woo_vou_edit_font_color').val(font_color);
				$('#woo_vou_edit_font_color').css('color',font_color);
				
			});
			
		} else if($(this).hasClass('editredeem')) {
			
			var bg_color = $(this).closest('.messagebox').find('.woo_vou_text_bg').val();
			var content = $(this).closest('.messagebox').find('.woo_vou_text').html();
			
			$('#woo_vou_edit_form').html('<div class="woo_vou_editor_heading"><h3><strong>'+WooVouMsgBox.msgboxtitle+'</strong></h3></div><div class="woo_vou_editor_controls"><div class="editor_content"></div></div><div class="woo_vou_form_action"><input type="button" id="woo_vou_pbr_save" class="button-primary message_edit_save" name="save" value="'+WooVouTranObj.btnsave+'" /><input type="button" id="woo_vou_pbr_cancel" class="button-primary text_cancel" name="cancel" value="'+WooVouTranObj.btncancel+'" /></div>');
			
			var data = {
							action	 : 'woo_vou_page_builder',
							editorid : 'wpspbrmessageedit',
							type	 : 'message',
							bgcolor	 : bg_color
						};
			
			jQuery.post( ajaxurl, data, function( response ) {
				
				$('.editor_content').html(response);
				woo_vou_init_tiny_mce();
				tinyMCE.get('wpspbrmessageedit').setContent(content);
				
				woo_vou_set_colorpicker( $('#woo_vou_edit_bg_color') );
				
				$('#woo_vou_edit_bg_color').css("background-color",bg_color);
				$('#woo_vou_edit_bg_color').val(bg_color);
				
			});
		} else if( $(this).hasClass( 'editexpire' ) ) {
			
			var bg_color	= $(this).closest('.expireblock').find('.woo_vou_text_bg').val();
			var content		= $(this).closest('.expireblock').find('.woo_vou_text').html();
			
			$('#woo_vou_edit_form').html('<div class="woo_vou_editor_heading"><h3><strong>'+WooVouExpireBlock.expireblocktitle+'</strong></h3></div><div class="woo_vou_editor_controls"><div class="editor_content"></div></div><div class="woo_vou_form_action"><input type="button" id="woo_vou_pbr_save" class="button-primary expire_edit_save" name="save" value="'+WooVouTranObj.btnsave+'" /><input type="button" id="woo_vou_pbr_cancel" class="button-primary text_cancel" name="cancel" value="'+WooVouTranObj.btncancel+'" /></div>');
			
			var data = {
							action 			: 'woo_vou_page_builder',
							type			: 'expireblock',
							editorid		: 'wpspbrexpireblockedit',
							bgcolor			: bg_color
						};
			
			jQuery.post( ajaxurl, data, function( response ) {
				
				jQuery('.editor_content').html(response);
				woo_vou_init_tiny_mce();
				tinyMCE.get('wpspbrexpireblockedit').setContent(content);
				
				$('#woo_vou_edit_bg_color').css('background-color',bg_color);
				
				woo_vou_set_colorpicker( $('#woo_vou_edit_bg_color') );
				
				$('#woo_vou_edit_bg_color').val(bg_color);
				$('#woo_vou_edit_bg_color').css('background-color',bg_color);
			});
		} else if( $(this).hasClass( 'editvenaddr' ) ) {
			
			var bg_color	= $(this).closest('.venaddrblock').find('.woo_vou_text_bg').val();
			var content		= $(this).closest('.venaddrblock').find('.woo_vou_text').html();
			
			$('#woo_vou_edit_form').html('<div class="woo_vou_editor_heading"><h3><strong>'+WooVouVenAddrBlock.venaddrblocktitle+'</strong></h3></div><div class="woo_vou_editor_controls"><div class="editor_content"></div></div><div class="woo_vou_form_action"><input type="button" id="woo_vou_pbr_save" class="button-primary venaddr_edit_save" name="save" value="'+WooVouTranObj.btnsave+'" /><input type="button" id="woo_vou_pbr_cancel" class="button-primary text_cancel" name="cancel" value="'+WooVouTranObj.btncancel+'" /></div>');
			
			var data = {
							action 			: 'woo_vou_page_builder',
							type			: 'venaddrblock',
							editorid		: 'wpspbrvenaddrblockedit',
							bgcolor			: bg_color
						};
			
			jQuery.post( ajaxurl, data, function( response ) {
				
				jQuery('.editor_content').html(response);
				woo_vou_init_tiny_mce();
				tinyMCE.get('wpspbrvenaddrblockedit').setContent(content);
				
				$( '#woo_vou_edit_bg_color').css('background-color', bg_color);
				
				woo_vou_set_colorpicker( $( '#woo_vou_edit_bg_color' ) );
				
				$( '#woo_vou_edit_bg_color').val(bg_color);
				$( '#woo_vou_edit_bg_color').css('background-color', bg_color );
			});
		} else if( $(this).hasClass('editsiteurl') ) {
			
			var bg_color	= $(this).closest('.siteurlblock').find('.woo_vou_text_bg').val();
			var content		= $(this).closest('.siteurlblock').find('.woo_vou_text').html();
			
			$('#woo_vou_edit_form').html('<div class="woo_vou_editor_heading"><h3><strong>'+WooVouSiteURLBlock.siteurlblocktitle+'</strong></h3></div><div class="woo_vou_editor_controls"><div class="editor_content"></div></div><div class="woo_vou_form_action"><input type="button" id="woo_vou_pbr_save" class="button-primary siteurl_edit_save" name="save" value="'+WooVouTranObj.btnsave+'" /><input type="button" id="woo_vou_pbr_cancel" class="button-primary text_cancel" name="cancel" value="'+WooVouTranObj.btncancel+'" /></div>');
			
			var data = {
							action 			: 'woo_vou_page_builder',
							type			: 'siteurlblock',
							editorid		: 'wpspbrsiteurlblockedit',
							bgcolor			: bg_color
						};
			
			jQuery.post( ajaxurl, data, function( response ) {
				
				jQuery('.editor_content').html(response);
				woo_vou_init_tiny_mce();
				tinyMCE.get('wpspbrsiteurlblockedit').setContent(content);
				
				$('#woo_vou_edit_bg_color').css('background-color',bg_color);
				
				woo_vou_set_colorpicker( $('#woo_vou_edit_bg_color') );
				
				$( '#woo_vou_edit_bg_color' ).val( bg_color );
				$( '#woo_vou_edit_bg_color' ).css( 'background-color', bg_color );
			});
		} else if( $(this).hasClass('editloc') ) {
			
			var bg_color	= $(this).closest('.locblock').find('.woo_vou_text_bg').val();
			var content		= $(this).closest('.locblock').find('.woo_vou_text').html();
			
			$('#woo_vou_edit_form').html('<div class="woo_vou_editor_heading"><h3><strong>'+WooVouLocBlock.locblocktitle+'</strong></h3></div><div class="woo_vou_editor_controls"><div class="editor_content"></div></div><div class="woo_vou_form_action"><input type="button" id="woo_vou_pbr_save" class="button-primary loc_edit_save" name="save" value="'+WooVouTranObj.btnsave+'" /><input type="button" id="woo_vou_pbr_cancel" class="button-primary text_cancel" name="cancel" value="'+WooVouTranObj.btncancel+'" /></div>');
			
			var data = {
							action 			: 'woo_vou_page_builder',
							type			: 'locblock',
							editorid		: 'wpspbrlocblockedit',
							bgcolor			: bg_color
						};
			
			jQuery.post( ajaxurl, data, function( response ) {
				
				jQuery('.editor_content').html(response);
				woo_vou_init_tiny_mce();
				tinyMCE.get('wpspbrlocblockedit').setContent(content);
				
				$('#woo_vou_edit_bg_color').css('background-color',bg_color);
				
				woo_vou_set_colorpicker( $('#woo_vou_edit_bg_color') );
				
				$('#woo_vou_edit_bg_color').val(bg_color);
				$('#woo_vou_edit_bg_color').css('background-color',bg_color);
			});
		} else if( $(this).hasClass( 'editcustom' ) ) {
			
			var bg_color	= $(this).closest('.customblock').find('.woo_vou_text_bg').val();
			var content		= $(this).closest('.customblock').find('.woo_vou_text').html();
			
			$('#woo_vou_edit_form').html('<div class="woo_vou_editor_heading"><h3><strong>'+WooVouCustomBlock.customblocktitle+'</strong></h3></div><div class="woo_vou_editor_controls"><div class="editor_content"></div></div><div class="woo_vou_form_action"><input type="button" id="woo_vou_pbr_save" class="button-primary custom_edit_save" name="save" value="'+WooVouTranObj.btnsave+'" /><input type="button" id="woo_vou_pbr_cancel" class="button-primary text_cancel" name="cancel" value="'+WooVouTranObj.btncancel+'" /></div>');
			
			var data = {
							action 			: 'woo_vou_page_builder',
							type			: 'customblock',
							editorid		: 'wpspbrcustomblockedit',
							bgcolor			: bg_color
						};
			
			jQuery.post( ajaxurl, data, function( response ) {
				
				jQuery( '.editor_content' ).html(response);
				woo_vou_init_tiny_mce();
				tinyMCE.get( 'wpspbrcustomblockedit' ).setContent(content);
				
				$( '#woo_vou_edit_bg_color' ).css(  'background-color', bg_color );
				
				woo_vou_set_colorpicker( $( '#woo_vou_edit_bg_color' ) );
				
				$( '#woo_vou_edit_bg_color' ).val( bg_color );
				$( '#woo_vou_edit_bg_color' ).css( 'background-color', bg_color );
			});
			
		} else if( $(this).hasClass( 'editqrcode' ) ) {
			
			var qrcodewidth			= $(this).closest('.qrcodeblock').find('.woo_vou_qrcode_width').val();
			var qrcodeheight		= $(this).closest('.qrcodeblock').find('.woo_vou_qrcode_height').val();
			var qrcodecolor			= $(this).closest('.qrcodeblock').find('.woo_vou_qrcode_color').val();
			var qrcodesymboltype	= $(this).closest('.qrcodeblock').find('.woo_vou_qrcode_symbol_type').val();
			var qrcodeborder		= $(this).closest('.qrcodeblock').find('.woo_vou_qrcode_border').val();
			var qrcoderesponse		= $(this).closest('.qrcodeblock').find('.woo_vou_qrcode_response').val();
			
			var content		= $(this).closest('.qrcodeblock').find('.woo_vou_text').html();
			
			$( '#woo_vou_edit_form' ).html('<div class="woo_vou_editor_heading"><h3><strong>'+WooVouQrcodeBlock.qrcodeblocktitle+'</strong></h3></div><div class="woo_vou_editor_controls"><div class="editor_content"></div></div><div class="woo_vou_form_action"><input type="button" id="woo_vou_pbr_save" class="button-primary qrcode_edit_save" name="save" value="'+WooVouTranObj.btnsave+'" /><input type="button" id="woo_vou_pbr_cancel" class="button-primary text_cancel" name="cancel" value="'+WooVouTranObj.btncancel+'" /></div>');
			
			var data = {
							action 				: 'woo_vou_page_builder',
							type				: 'qrcodeblock',
							editorid			: 'wpspbrqrcodeblockedit',
							qrcodewidth			: qrcodewidth,
							qrcodeheight		: qrcodeheight,
							qrcodecolor	    	: qrcodecolor,
							qrcodesymboltype	: qrcodesymboltype,
							qrcodeborder		: qrcodeborder,
							qrcoderesponse		: qrcoderesponse
						};
			
			jQuery.post( ajaxurl, data, function( response ) {
				
				jQuery('.editor_content').html(response);
				woo_vou_init_tiny_mce();
				tinyMCE.get('wpspbrqrcodeblockedit').setContent(content);
				
				woo_vou_set_colorpicker( $('#woo_vou_edit_qrcode_color') );
				
			});
		} else if( $(this).hasClass( 'editqrcodes' ) ) {
			
			var qrcodewidth			= $(this).closest('.qrcodesblock').find('.woo_vou_qrcode_width').val();
			var qrcodeheight		= $(this).closest('.qrcodesblock').find('.woo_vou_qrcode_height').val();
			var qrcodecolor			= $(this).closest('.qrcodesblock').find('.woo_vou_qrcode_color').val();
			var qrcodesymboltype	= $(this).closest('.qrcodesblock').find('.woo_vou_qrcode_symbol_type').val();
			var qrcodetype			= $(this).closest('.qrcodesblock').find('.woo_vou_qrcode_type').val();
			var qrcodeborder		= $(this).closest('.qrcodesblock').find('.woo_vou_qrcode_border').val();
			var qrcoderesponse		= $(this).closest('.qrcodesblock').find('.woo_vou_qrcode_response').val();
			
			var content		= $(this).closest('.qrcodesblock').find('.woo_vou_text').html();
			
			$( '#woo_vou_edit_form' ).html('<div class="woo_vou_editor_heading"><h3><strong>'+WooVouQrcodesBlock.qrcodesblocktitle+'</strong></h3></div><div class="woo_vou_editor_controls"><div class="editor_content"></div></div><div class="woo_vou_form_action"><input type="button" id="woo_vou_pbr_save" class="button-primary qrcodes_edit_save" name="save" value="'+WooVouTranObj.btnsave+'" /><input type="button" id="woo_vou_pbr_cancel" class="button-primary text_cancel" name="cancel" value="'+WooVouTranObj.btncancel+'" /></div>');
			
			var data = {
							action 				: 'woo_vou_page_builder',
							type				: 'qrcodesblock',
							editorid			: 'wpspbrqrcodesblockedit',
							qrcodewidth			: qrcodewidth,
							qrcodeheight		: qrcodeheight,
							qrcodecolor			: qrcodecolor,
							qrcodesymboltype	: qrcodesymboltype,
							qrcodetype			: qrcodetype,
							qrcodeborder		: qrcodeborder,
							qrcoderesponse		: qrcoderesponse
						};
			
			jQuery.post( ajaxurl, data, function( response ) {
				
				jQuery('.editor_content').html(response);
				woo_vou_init_tiny_mce();
				tinyMCE.get('wpspbrqrcodesblockedit').setContent(content);
				
				woo_vou_set_colorpicker( $('#woo_vou_edit_qrcode_color') );
				
			});
		} else if( $(this).hasClass( 'editbarcode' ) ) {
			
			var barcodewidth	= $(this).closest('.barcodeblock').find('.woo_vou_barcode_width').val();
			var barcodeheight	= $(this).closest('.barcodeblock').find('.woo_vou_barcode_height').val();
			var barcodecolor	= $(this).closest('.barcodeblock').find('.woo_vou_barcode_color').val();
			var barcodetype		= $(this).closest('.barcodeblock').find('.woo_vou_barcode_type').val();
			var barcodeborder	= $(this).closest('.barcodeblock').find('.woo_vou_barcode_border').val();
			
			var content		= $(this).closest('.barcodeblock').find('.woo_vou_text').html();
			
			$( '#woo_vou_edit_form' ).html('<div class="woo_vou_editor_heading"><h3><strong>'+WooVouBarcodeBlock.barcodeblocktitle+'</strong></h3></div><div class="woo_vou_editor_controls"><div class="editor_content"></div></div><div class="woo_vou_form_action"><input type="button" id="woo_vou_pbr_save" class="button-primary barcode_edit_save" name="save" value="'+WooVouTranObj.btnsave+'" /><input type="button" id="woo_vou_pbr_cancel" class="button-primary text_cancel" name="cancel" value="'+WooVouTranObj.btncancel+'" /></div>');
			
			var data = {
							action 			: 'woo_vou_page_builder',
							type			: 'barcodeblock',
							editorid		: 'wpspbrbarcodeblockedit',
							barcodewidth	: barcodewidth,
							barcodeheight	: barcodeheight,
							barcodecolor	: barcodecolor,
							barcodetype		: barcodetype,
							barcodeborder	: barcodeborder,
						};
			
			jQuery.post( ajaxurl, data, function( response ) {
				
				jQuery('.editor_content').html(response);
				woo_vou_init_tiny_mce();
				tinyMCE.get('wpspbrbarcodeblockedit').setContent(content);
				
				woo_vou_set_colorpicker( $('#woo_vou_edit_barcode_color') );
			});
		} else if( $(this).hasClass( 'editbarcodes' ) ) {
			
			var barcodewidth	= $(this).closest('.barcodesblock').find('.woo_vou_barcode_width').val();
			var barcodeheight	= $(this).closest('.barcodesblock').find('.woo_vou_barcode_height').val();
			var barcodecolor	= $(this).closest('.barcodesblock').find('.woo_vou_barcode_color').val();
			var barcodetype		= $(this).closest('.barcodesblock').find('.woo_vou_barcode_type').val();
			var barcodedisptype	= $(this).closest('.barcodesblock').find('.woo_vou_barcode_disp_type').val();
			var barcodeborder	= $(this).closest('.barcodesblock').find('.woo_vou_barcode_border').val();
			
			var content		= $(this).closest('.barcodesblock').find('.woo_vou_text').html();
			
			$( '#woo_vou_edit_form' ).html('<div class="woo_vou_editor_heading"><h3><strong>'+WooVouBarcodesBlock.barcodesblocktitle+'</strong></h3></div><div class="woo_vou_editor_controls"><div class="editor_content"></div></div><div class="woo_vou_form_action"><input type="button" id="woo_vou_pbr_save" class="button-primary barcodes_edit_save" name="save" value="'+WooVouTranObj.btnsave+'" /><input type="button" id="woo_vou_pbr_cancel" class="button-primary text_cancel" name="cancel" value="'+WooVouTranObj.btncancel+'" /></div>');
			
			var data = {
							action 			: 'woo_vou_page_builder',
							type			: 'barcodesblock',
							editorid		: 'wpspbrbarcodesblockedit',
							barcodewidth	: barcodewidth,
							barcodeheight	: barcodeheight,
							barcodecolor	: barcodecolor,
							barcodetype		: barcodetype,
							barcodedisptype	: barcodedisptype,
							barcodeborder	: barcodeborder,
						};
			
			jQuery.post( ajaxurl, data, function( response ) {
				
				jQuery('.editor_content').html(response);
				woo_vou_init_tiny_mce();
				tinyMCE.get('wpspbrbarcodesblockedit').setContent(content);
				
				woo_vou_set_colorpicker( $('#woo_vou_edit_barcode_color') );
			});
		} else if( $(this).hasClass( 'editproductimage' ) ) {

			var productimagewidth		= $(this).closest('.productimageblock').find('.woo_vou_product_image_width').val();
			var productimageheight		= $(this).closest('.productimageblock').find('.woo_vou_product_image_height').val();
			
			var content		= $(this).closest('.productimageblock').find('.woo_vou_text').html();
			
			$( '#woo_vou_edit_form' ).html('<div class="woo_vou_editor_heading"><h3><strong>'+WooVouProductImageBlock.productimageblocktitle+'</strong></h3></div><div class="woo_vou_editor_controls"><div class="editor_content"></div></div><div class="woo_vou_form_action"><input type="button" id="woo_vou_pbr_save" class="button-primary product_image_edit_save" name="save" value="'+WooVouTranObj.btnsave+'" /><input type="button" id="woo_vou_pbr_cancel" class="button-primary text_cancel" name="cancel" value="'+WooVouTranObj.btncancel+'" /></div>');
			
			var data = {
							action 				: 'woo_vou_page_builder',
							type				: 'productimageblock',
							editorid			: 'wpspbrqrcodeblockedit',
							productimagewidth	: productimagewidth,
							productimageheight	: productimageheight,
						};
			
			jQuery.post( ajaxurl, data, function( response ) {
				
				jQuery('.editor_content').html(response);
				woo_vou_init_tiny_mce();
				tinyMCE.get('wpspbrqrcodeblockedit').setContent(content);
			});
		}
		woo_vou_ini_form_editing(element);
	});
	// On Click of edit will show editor end
	
	// On click removing a element form page builder area  start
	$( document ).on( 'click', '.woo_vou_remove', function() {
		
		var answer = confirm ('Click on OK to delete this section, click on Cancel to leave');
		if (answer) {
			$(this).closest('.text_column').remove();

			if($('.woo_vou_controls').html() == "") {
				
				$('.woo_vou_builder_area').show();
			}
		}
	});
	// On click removing a element form page builder area end
	
	// On Click of increase/decrease width start
	$( document ).on( 'click', '.woo_vou_greater_width', function() {
		
		var columnwidth = jQuery(this).closest(".text_column"),
			columnsizes = woo_vou_get_column_width(columnwidth),
			widthofblock = $(this).closest('.text_column').find('.woo_vou_txtclass_width').val();
		
		//show hide the button for resizing when selected controll is add to cart 
		if($(this).hasClass('add_cart') && ( columnsizes[3] == '1/1' || columnsizes[3] == '3/4' || columnsizes[3] == '1/2')) {
			
			$(this).closest('.text_column').find('.woo_vou_lesser_width').show();
			
		} else if ($(this).hasClass('add_cart') && ( columnsizes[3] == '1/4' )) {
			
			$(this).closest('.text_column').find('.woo_vou_lesser_width').hide();
		}
		
		if (columnsizes[1]) {
			
			columnwidth.removeClass(columnsizes[0]).addClass(columnsizes[1]);
			/* get updated column size */
			$(this).closest('.text_column').find('.woo_vou_txtclass_width').val(columnsizes[1]);
			columnsizes = woo_vou_get_column_width(columnwidth);
			jQuery(columnwidth).find(".width_size").html(columnsizes[3]);
 		}
	});
	$( document ).on( 'click', '.woo_vou_lesser_width', function() {
				
		var columnwidth = jQuery(this).closest(".text_column"),
			columnsizes = woo_vou_get_column_width(columnwidth),
			widthofblock = $(this).closest('.text_column').find('.woo_vou_txtclass_width').val();
		
				//hide the lesser width when add to cart is lesser then one_half
				if($(this).hasClass('add_cart') && ( columnsizes[3] == '1/1' || columnsizes[3] == '3/4' )) {
					$(this).show();
				} else if ($(this).hasClass('add_cart') && ( columnsizes[3] == '1/4' )) {
					$(this).hide();
				}
		if( columnsizes[2] ) {
			
			columnwidth.removeClass(columnsizes[0]).addClass(columnsizes[2]);
			/* get updated column size */
			$(this).closest('.text_column').find('.woo_vou_txtclass_width').val(columnsizes[2]);
			columnsizes = woo_vou_get_column_width(columnwidth);
			jQuery(columnwidth).find(".width_size").html(columnsizes[3]);
		}
	});
	// On Click of increase/decrease width end
	
	// On click of button add a text control start 
	$( document ).on( 'click', '#woo_vou_text_btn', function() {
		jQuery('.woo_vou_builder_area').hide();
		jQuery('.woo_vou_controls').append('<div class="woo_vou_controls_editor text_column textblock full_width draghandle" style="background-color:#FFFFFF;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editcode" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text">'+WooVouTextBlock.textblockdesc+'</div><div class="woo_vou_text_codes">'+WooVouTextBlock.textblockdesccodes+'</div><input type="hidden" value="full_width" class="woo_vou_txtclass_width" id="woo_vou_text_width" name="woo_vou_text_width"><input type="hidden" class="woo_vou_text_bg" id="woo_vou_text_bg" name="woo_vou_text_bg" value=""><input type="hidden" class="woo_vou_text_font_color" id="woo_vou_text_font_color" name="woo_vou_text_font_color" value="#000000"><input type="hidden" class="woo_vou_text_font_size" id="woo_vou_text_font_size" name="woo_vou_text_font_size" value="10"><input type="hidden" class="woo_vou_text_text_align" id="woo_vou_text_text_align" name="woo_vou_text_text_align" value="left"><input type="hidden" class="woo_vou_text_code_text_align" id="woo_vou_text_code_text_align" name="woo_vou_text_code_text_align" value="left"><input type="hidden" class="woo_vou_text_code_border" id="woo_vou_text_code_border" name="woo_vou_text_code_border" value=""><input type="hidden" class="woo_vou_text_code_column" id="woo_vou_text_code_column" name="woo_vou_text_code_column" value="1"></div>');
			if(typeof(Prototype) != "undefined")  {
   				portal = new Portal( settings, options, data );
  			}
		return false;
	});
	// On click of button add a text control end 
	
	// On click of button add message box start
	$( document ).on( 'click', '#woo_vou_message_btn', function() {
		jQuery('.woo_vou_builder_area').hide();
		jQuery('.woo_vou_controls').append('<div class="woo_vou_controls_editor text_column messagebox full_width draghandle" style="background-color:#FFFFFF;color:#000000;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editredeem" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text">'+WooVouMsgBox.msgboxdesc+'</div><input type="hidden" value="" class="woo_vou_text_bg" id="woo_vou_msg_color" name="woo_vou_msg_color"><input id="woo_vou_messagebox_width" class="woo_vou_txtclass_width" type="hidden" name="woo_vou_text_width" value="full_width"></div></div>');
		if(typeof(Prototype) != "undefined")  {
   			portal = new Portal(settings, options, data);
  		}
		return false;
	});
	// On click of button add message box end
	
	// On click of button add a site logo control start
	$( document ).on( 'click', '#woo_vou_site_logo_btn', function() {
		jQuery('.woo_vou_builder_area').hide();
		jQuery('.woo_vou_controls').append('<div class="woo_vou_controls_editor text_column sitelogoblock full_width draghandle" style="background-color:#FFFFFF;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>'+WooVouSiteLogoBox.sitelogoboxdesc+'</p></div><input type="hidden" value="full_width" class="woo_vou_txtclass_width" id="woo_vou_site_logo_width" name="woo_vou_text_width"></div>');
			if(typeof(Prototype) != "undefined")  {
   				portal = new Portal(settings, options, data);
  			}
		return false;
	});
	// On click of button add a settings logo control end 
	
	// On click of button add a logo control start 
	$( document ).on( 'click', '#woo_vou_logo_btn', function() {
		jQuery('.woo_vou_builder_area').hide();
		jQuery('.woo_vou_controls').append('<div class="woo_vou_controls_editor text_column logoblock full_width draghandle" style="background-color:#FFFFFF;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>'+WooVouLogoBox.logoboxdesc+'</p></div><input type="hidden" value="full_width" class="woo_vou_txtclass_width" id="woo_vou_logo_width" name="woo_vou_text_width"></div>');
			if(typeof(Prototype) != "undefined")  {
   				portal = new Portal(settings, options, data);
  			}
		return false;
	});
	// On click of button add a logo control end 
	
	// On click of button add a expire date control start 
	$( document ).on( 'click', '#woo_vou_expire_btn', function() {
		jQuery('.woo_vou_builder_area').hide();
		jQuery('.woo_vou_controls').append('<div class="woo_vou_controls_editor text_column expireblock full_width draghandle" style="background-color:#FFFFFF;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editexpire" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>'+WooVouExpireBlock.expireblockdesc+'</p></div><input type="hidden" value="full_width" class="woo_vou_txtclass_width" id="woo_vou_expire_width" name="woo_vou_text_width"><input type="hidden" class="woo_vou_text_bg" id="woo_vou_expire_bg" name="woo_vou_expire_bg" value=""></div>');
			if(typeof(Prototype) != "undefined")  {
   				portal = new Portal(settings, options, data);
  			}
		return false;
	});
	// On click of button add a expire date control end 
	
	// On click of button add a vendor's address control start 
	$( document ).on( 'click', '#woo_vou_venaddr_btn', function() {
		jQuery('.woo_vou_builder_area').hide();
		jQuery('.woo_vou_controls').append('<div class="woo_vou_controls_editor text_column venaddrblock full_width draghandle" style="background-color:#FFFFFF;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editvenaddr" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>'+WooVouVenAddrBlock.venaddrblockdesc+'</p></div><input type="hidden" value="full_width" class="woo_vou_txtclass_width" id="woo_vou_venaddr_width" name="woo_vou_text_width"><input type="hidden" class="woo_vou_text_bg" id="woo_vou_venaddr_bg" name="woo_vou_venaddr_bg" value=""></div>');
			if(typeof(Prototype) != "undefined")  {
   				portal = new Portal(settings, options, data);
  			}
		return false;
	});
	// On click of button add a vendor's address control end 
	
	// On click of button add a vendor's address control start 
	$( document ).on( 'click', '#woo_vou_siteurl_btn', function() {
		jQuery('.woo_vou_builder_area').hide();
		jQuery('.woo_vou_controls').append('<div class="woo_vou_controls_editor text_column siteurlblock full_width draghandle" style="background-color:#FFFFFF;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editsiteurl" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>'+WooVouSiteURLBlock.siteurlblockdesc+'</p></div><input type="hidden" value="full_width" class="woo_vou_txtclass_width" id="woo_vou_siteurl_width" name="woo_vou_text_width"><input type="hidden" class="woo_vou_text_bg" id="woo_vou_siteurl_bg" name="woo_vou_siteurl_bg" value=""></div>');
			if(typeof(Prototype) != "undefined")  {
   				portal = new Portal(settings, options, data);
  			}
		return false;
	});
	// On click of button add a vendor's address control end 
	
	// On click of button add a voucher locations control start 
	$( document ).on( 'click', '#woo_vou_loc_btn', function() {
		jQuery('.woo_vou_builder_area').hide();
		jQuery('.woo_vou_controls').append('<div class="woo_vou_controls_editor text_column locblock full_width draghandle" style="background-color:#FFFFFF;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editloc" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text">'+WooVouLocBlock.locblockdesc+'</div><input type="hidden" value="full_width" class="woo_vou_txtclass_width" id="woo_vou_loc_width" name="woo_vou_text_width"><input type="hidden" class="woo_vou_text_bg" id="woo_vou_loc_bg" name="woo_vou_loc_bg" value=""></div>');
			if(typeof(Prototype) != "undefined")  {
   				portal = new Portal(settings, options, data);
  			}
		return false;
	});
	// On click of button add a voucher locations control end 
	
	// On click of button add blank box start
	$( document ).on( 'click', '#woo_vou_blank_btn', function() {
		jQuery('.woo_vou_builder_area').hide();
		jQuery('.woo_vou_controls').append('<div class="woo_vou_controls_editor text_column blankbox full_width draghandle" style="background-color:#FFFFFF;color:#000000;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>'+WooVouBlankBox.blankboxdesc+'</p></div><input type="hidden" value="full_width" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width"><input type="hidden" class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value=""></div>');
		if(typeof(Prototype) != "undefined")  {
   			portal = new Portal(settings, options, data);
  		}
		return false;
	});
	// On click of button add blank box end
	
	// On click of button add custom box start
	$( document ).on( 'click', '#woo_vou_custom_btn', function() {
		jQuery('.woo_vou_builder_area').hide();
		jQuery('.woo_vou_controls').append('<div class="woo_vou_controls_editor text_column customblock full_width draghandle" style="background-color:#FFFFFF;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editcustom" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>'+WooVouCustomBlock.customblockdesc+'</p></div><input type="hidden" value="full_width" class="woo_vou_txtclass_width" id="woo_vou_custom_width" name="woo_vou_text_width"><input type="hidden" class="woo_vou_text_bg" id="woo_vou_custom_bg" name="woo_vou_custom_bg" value=""></div>');
			if(typeof(Prototype) != "undefined")  {
   				portal = new Portal(settings, options, data);
  			}
		return false;
	});
	// On click of button add custom box end
	
	//On click of editor cancel  start
	$( document ).on( 'click', '#woo_vou_pbr_cancel', function() {
		jQuery('.woo_vou_editor').hide();
		jQuery('.woo_vou_controls_editor').show();
		jQuery('.woo_vou_main_editor').show();
		jQuery('#woo_vou_page_builder').removeClass('woo_vou_edit_mode')

		if(jQuery('#woo_vou_pbr_save').hasClass('tabs_edit_save')) {
			jQuery('#woo_vou_edit_form .woo_vou_textareaeditor').each(function(index) {
				woo_vou_get_tiny_content( "woo_vou_text_editor_"+index );
			});
		} else {
			var editor_ID = tinyMCE.activeEditor.id;
			woo_vou_get_tiny_content(editor_ID);
		}
		jQuery('#publish').show();
	});
	//On click of editor cancel  end
	
	
	/** Add QrCode Builder Functionality ( 2.4.6 ) **/
	// On click of button add qrcode box start
	$( document ).on( 'click', '#woo_vou_qrcode_btn', function() {
		jQuery('.woo_vou_builder_area').hide();
		jQuery('.woo_vou_controls').append( '<div class="woo_vou_controls_editor text_column qrcodeblock full_width draghandle" style="background-color:#FFFFFF;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editqrcode" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>'+WooVouQrcodeBlock.qrcodeblockdesc+'</p></div><input type="hidden" value="full_width" class="woo_vou_txtclass_width" id="woo_vou_custom_width" name="woo_vou_text_width"><input type="hidden" class="woo_vou_qrcode_width" id="woo_vou_qrcode_width" name="woo_vou_qrcode_width" value=""><input type="hidden" class="woo_vou_qrcode_height" id="woo_vou_qrcode_height" name="woo_vou_qrcode_height" value=""><input type="hidden" class="woo_vou_qrcode_color" id="woo_vou_qrcode_color" name="woo_vou_qrcode_color" value=""><input type="hidden" class="woo_vou_qrcode_symbol_type" id="woo_vou_qrcode_symbol_type" name="woo_vou_qrcode_symbol_type" value=""><input type="hidden" class="woo_vou_qrcode_border" id="woo_vou_qrcode_border" name="woo_vou_qrcode_border" value="1"><input type="hidden" class="woo_vou_qrcode_response" id="woo_vou_qrcode_response" name="woo_vou_qrcode_response" value="url"></div>' );
			if(typeof(Prototype) != "undefined")  {
   				portal = new Portal(settings, options, data);
  			}
		return false;
	});
	// On click of button add qrcode box End
	
	/** Add QrCodes Builder Functionality ( 2.4.6 ) **/
	// On click of button add qrcodes box start
	$( document ).on( 'click', '#woo_vou_qrcodes_btn', function() {
		jQuery('.woo_vou_builder_area').hide();
		jQuery('.woo_vou_controls').append( '<div class="woo_vou_controls_editor text_column qrcodesblock full_width draghandle" style="background-color:#FFFFFF;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editqrcodes" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>'+WooVouQrcodesBlock.qrcodesblockdesc+'</p></div><input type="hidden" value="full_width" class="woo_vou_txtclass_width" id="woo_vou_custom_width" name="woo_vou_text_width"><input type="hidden" class="woo_vou_qrcode_width" id="woo_vou_qrcode_width" name="woo_vou_qrcode_width" value=""><input type="hidden" class="woo_vou_qrcode_height" id="woo_vou_qrcode_height" name="woo_vou_qrcode_height" value=""><input type="hidden" class="woo_vou_qrcode_color" id="woo_vou_qrcode_color" name="woo_vou_qrcode_color" value=""><input type="hidden" class="woo_vou_qrcode_symbol_type" id="woo_vou_qrcode_symbol_type" name="woo_vou_qrcode_symbol_type" value=""><input type="hidden" class="woo_vou_qrcode_type" id="woo_vou_qrcode_type" name="woo_vou_qrcode_type" value="vertical"><input type="hidden" class="woo_vou_qrcode_border" id="woo_vou_qrcode_border" name="woo_vou_qrcode_border" value="1"><input type="hidden" class="woo_vou_qrcode_response" id="woo_vou_qrcode_response" name="woo_vou_qrcode_response" value="url"></div>' );
			if(typeof(Prototype) != "undefined")  {
   				portal = new Portal(settings, options, data);
  			}
		return false;
	});
	// On click of button add qrcodes box End
	
	/** Add barcode Builder Functionality ( 2.4.6 ) **/
	// On click of button add barcode box start
	$( document ).on( 'click', '#woo_vou_barcode_btn', function() {
		jQuery('.woo_vou_builder_area').hide();
		jQuery('.woo_vou_controls').append( '<div class="woo_vou_controls_editor text_column barcodeblock full_width draghandle" style="background-color:#FFFFFF;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editbarcode" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>'+WooVouBarcodeBlock.barcodeblockdesc+'</p></div><input type="hidden" value="full_width" class="woo_vou_txtclass_width" id="woo_vou_custom_width" name="woo_vou_text_width"><input type="hidden" class="woo_vou_barcode_width" id="woo_vou_barcode_width" name="woo_vou_barcode_width" value=""><input type="hidden" class="woo_vou_barcode_height" id="woo_vou_barcode_height" name="woo_vou_barcode_height" value=""><input type="hidden" class="woo_vou_barcode_color" id="woo_vou_barcode_color" name="woo_vou_barcode_color" value=""><input type="hidden" class="woo_vou_barcode_type" id="woo_vou_barcode_type" name="woo_vou_barcode_type" value=""><input type="hidden" class="woo_vou_barcode_border" id="woo_vou_barcode_border" name="woo_vou_barcode_border" value="0"></div>' );
			if(typeof(Prototype) != "undefined")  {
   				portal = new Portal(settings, options, data);
  			}
		return false;
	});
	// On click of button add barcode box End
	
	/** Add barcodes Builder Functionality ( 2.4.6 ) **/
	// On click of button add barcodes box start
	$( document ).on( 'click', '#woo_vou_barcodes_btn', function() {
		jQuery('.woo_vou_builder_area').hide();
		jQuery('.woo_vou_controls').append( '<div class="woo_vou_controls_editor text_column barcodesblock full_width draghandle" style="background-color:#FFFFFF;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editbarcodes" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>'+WooVouBarcodesBlock.barcodesblockdesc+'</p></div><input type="hidden" value="full_width" class="woo_vou_txtclass_width" id="woo_vou_custom_width" name="woo_vou_text_width"><input type="hidden" class="woo_vou_barcode_width" id="woo_vou_barcode_width" name="woo_vou_barcode_width" value=""><input type="hidden" class="woo_vou_barcode_height" id="woo_vou_barcode_height" name="woo_vou_barcode_height" value=""><input type="hidden" class="woo_vou_barcode_color" id="woo_vou_barcode_color" name="woo_vou_barcode_color" value=""><input type="hidden" class="woo_vou_barcode_type" id="woo_vou_barcode_type" name="woo_vou_barcode_type" value="C128"><input type="hidden" class="woo_vou_barcode_disp_type" id="woo_vou_barcode_disp_type" name="woo_vou_barcode_disp_type" value="vertical"><input type="hidden" class="woo_vou_barcode_border" id="woo_vou_barcode_border" name="woo_vou_barcode_border" value="0"></div>' );
			if(typeof(Prototype) != "undefined")  {
   				portal = new Portal(settings, options, data);
  			}
		return false;
	});
	// On click of button add barcode box End

	// On click of product image button
	$( document ).on( 'click', '#woo_vou_product_image_btn', function() {
		jQuery('.woo_vou_builder_area').hide();
		jQuery('.woo_vou_controls').append( '<div class="woo_vou_controls_editor text_column productimageblock full_width draghandle" style="background-color:#FFFFFF;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editproductimage" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>'+WooVouProductImageBlock.productimageblockdesc+'</p></div><input type="hidden" value="full_width" class="woo_vou_txtclass_width" id="woo_vou_custom_width" name="woo_vou_text_width"><input type="hidden" class="woo_vou_product_image_width" id="woo_vou_product_image_width" name="woo_vou_product_image_width" value=""><input type="hidden" class="woo_vou_product_image_height" id="woo_vou_product_image_height" name="woo_vou_product_image_height" value=""></div>' );
			if(typeof(Prototype) != "undefined")  {
   				portal = new Portal(settings, options, data);
  			}
		return false;
	});

	// Update the content of page builder start
	$( document ).on( 'click', '#publish, #save-post', function() {
		
		if($('#woo_vou_builder_switch').html() == WooVouTranObj.onbuttontxt) {
			woo_vou_give_shortcode_to_editor();
		}
		
		var metaboxdata = jQuery('.woo_vou_controls').html();
		$('#woo_vou_meta_content').val( metaboxdata );
	});//Update the content of page builder end
	
	//make editor to visual mode
	function woo_vou_switch_default_editor_visual( editor ) {
		if (jQuery('#wp-'+editor+'-wrap').hasClass('html-active')) {
			if (typeof switchEditors != "undefined")
				switchEditors.go(editor, 'tinymce');
		}
	}

	function woo_vou_ini_form_editing(element) {

		jQuery('#woo_vou_page_builder').addClass('woo_vou_edit_mode');
		jQuery('#publish').hide();

		//On click of save changing the value of textarea to p start
		jQuery('#woo_vou_pbr_save').on( 'click', function(e) {

			if(jQuery('#woo_vou_pbr_save').hasClass('text_edit_save')) { // save chages for textblock
	
				var edited = woo_vou_get_tiny_content('wpspbrtextblockedit');
				var editedcodes = woo_vou_get_tiny_content('wpspbrtextblockeditcodes');

				var bg_color = jQuery('#woo_vou_edit_bg_color').val();
				var font_color = jQuery('#woo_vou_edit_font_color').val();
				var font_size = jQuery('#woo_vou_edit_font_size').val();
				var text_align = jQuery('#woo_vou_edit_text_align').val();
				var code_text_align = jQuery('#woo_vou_edit_code_text_align').val();
				var code_border = jQuery('#woo_vou_edit_code_border').val();
				var code_column = jQuery('#woo_vou_edit_code_column').val();

				if(font_color == '') {
					font_color = '#000000';
				}
				if(font_size == '') {
					font_size = '10';
				}
				if(text_align == '') {
					text_align = 'left';
				}
				if(code_text_align == '') {
					code_text_align = 'left';
				}

				jQuery("#woo_vou_edit_form #woo_vou_edit_bg_color").each(function(index) {

					var element_to_update = 'woo_vou_text';
					var element_to_update_codes = 'woo_vou_text_codes';
					var bg_color_to_update = 'woo_vou_text_bg';
					var font_color_to_update = 'woo_vou_text_font_color';
					var font_size_to_update = 'woo_vou_text_font_size';
					var text_align_to_update = 'woo_vou_text_text_align';
					var code_text_align_to_update = 'woo_vou_text_code_text_align';
					var code_border_to_update = 'woo_vou_text_code_border';
					var code_column_to_update = 'woo_vou_text_code_column';

					if (element.find('.'+element_to_update).is('div, h1,h2,h3,h4,h5,h6, span, i, b, strong, p')) {

						element.find('.'+element_to_update).html(edited);
						element.find('.'+element_to_update_codes).html(editedcodes);
						element.find('.'+bg_color_to_update).val(bg_color);
						if(bg_color == '') {
							bg_color = '#FFFFFF';
						}
						element.find('.'+bg_color_to_update).closest('.textblock').css('background-color',bg_color);
						element.find('.'+font_size_to_update).val(font_size);
						element.find('.'+font_size_to_update).closest('.textblock').find('.woo_vou_text').css('font-size',font_size+'pt');
						element.find('.'+text_align_to_update).val(text_align);
						element.find('.'+text_align_to_update).closest('.textblock').css('text-align',text_align);
						element.find('.'+code_text_align_to_update).val(code_text_align);
						element.find('.'+code_border_to_update).val(code_border);
						element.find('.'+code_column_to_update).val(code_column);

					} else {

						element.find('.'+element_to_update).html(edited);
						element.find('.'+element_to_update_codes).html(editedcodes);
						element.find('.'+bg_color_to_update).val(bg_color);
						element.find('.'+font_size_to_update).val(font_size);
						element.find('.'+text_align_to_update).val(text_align);
						element.find('.'+code_text_align_to_update).val(code_text_align);
						element.find('.'+code_border_to_update).val(code_border);
						element.find('.'+code_column_to_update).val(code_column);
					}
				});

			} else if (jQuery('#woo_vou_pbr_save').hasClass('message_edit_save')) {	 // save changes messagebox

				var message_content = woo_vou_get_tiny_content('wpspbrmessageedit');
				var messagebgcolor = jQuery('#woo_vou_edit_bg_color').val();

				jQuery("#woo_vou_edit_form #woo_vou_edit_bg_color").each(function(index) {

					var content_to_update = 'woo_vou_text';
					var color_to_update = 'woo_vou_text_bg';

					if (element.find('.'+content_to_update).is('div, h1,h2,h3,h4,h5,h6, span, i, b, strong, p')) {

						element.find('.'+content_to_update).html(message_content);
						element.find('.'+color_to_update).val(messagebgcolor);
						if(messagebgcolor == '') {
							messagebgcolor = '#FFFFFF';
						}
						element.find('.'+color_to_update).closest('.messagebox').css("background-color",messagebgcolor);

					} else {
						element.find('.'+content_to_update).html(message_content);
						element.find('.'+color_to_update).val(messagebgcolor);
					}
				});

			} else if(jQuery('#woo_vou_pbr_save').hasClass('expire_edit_save')) { // save chages for expireblock

				var edited = woo_vou_get_tiny_content('wpspbrexpireblockedit');
				var bg_color = jQuery('#woo_vou_edit_bg_color').val();

				jQuery("#woo_vou_edit_form #woo_vou_edit_bg_color").each(function(index) {

					var element_to_update = 'woo_vou_text';
					var bg_color_to_update = 'woo_vou_text_bg';

					if (element.find('.'+element_to_update).is('div, h1,h2,h3,h4,h5,h6, span, i, b, p')) {

						element.find('.'+element_to_update).html(edited);
						element.find('.'+bg_color_to_update).val(bg_color);
						if(bg_color == '') {
							bg_color = '#FFFFFF';
						}
						element.find('.'+bg_color_to_update).closest('.expireblock').css('background-color',bg_color);

					} else {

						element.find('.'+element_to_update).val(edited);
						element.find('.'+bg_color_to_update).val(bg_color);
					}
				});

			} else if(jQuery('#woo_vou_pbr_save').hasClass('venaddr_edit_save')) { // save chages for venaddrblock

				var edited = woo_vou_get_tiny_content('wpspbrvenaddrblockedit');
				var bg_color = jQuery('#woo_vou_edit_bg_color').val();

				jQuery("#woo_vou_edit_form #woo_vou_edit_bg_color").each(function(index) {

					var element_to_update = 'woo_vou_text';
					var bg_color_to_update = 'woo_vou_text_bg';

					if (element.find('.'+element_to_update).is('div, h1,h2,h3,h4,h5,h6, span, i, b, p')) {

						element.find('.'+element_to_update).html(edited);
						element.find('.'+bg_color_to_update).val(bg_color);
						if(bg_color == '') {
							bg_color = '#FFFFFF';
						}
						element.find('.'+bg_color_to_update).closest('.venaddrblock').css('background-color',bg_color);

					} else {

						element.find('.'+element_to_update).val(edited);
						element.find('.'+bg_color_to_update).val(bg_color);
					}
				});

			} else if(jQuery('#woo_vou_pbr_save').hasClass('siteurl_edit_save')) { // save chages for siteurlblock

				var edited = woo_vou_get_tiny_content('wpspbrsiteurlblockedit');
				var bg_color = jQuery('#woo_vou_edit_bg_color').val();

				jQuery("#woo_vou_edit_form #woo_vou_edit_bg_color").each(function(index) {

					var element_to_update = 'woo_vou_text';
					var bg_color_to_update = 'woo_vou_text_bg';

					if (element.find('.'+element_to_update).is('div, h1,h2,h3,h4,h5,h6, span, i, b, p')) {

						element.find('.'+element_to_update).html(edited);
						element.find('.'+bg_color_to_update).val(bg_color);
						if(bg_color == '') {
							bg_color = '#FFFFFF';
						}
						element.find('.'+bg_color_to_update).closest('.siteurlblock').css('background-color',bg_color);

					} else {

						element.find('.'+element_to_update).val(edited);
						element.find('.'+bg_color_to_update).val(bg_color);
					}
				});
					
			} else if(jQuery('#woo_vou_pbr_save').hasClass('loc_edit_save')) { // save chages for locblock
					
				var edited = woo_vou_get_tiny_content('wpspbrlocblockedit');
				var bg_color = jQuery('#woo_vou_edit_bg_color').val();
				
				jQuery("#woo_vou_edit_form #woo_vou_edit_bg_color").each(function(index) {
					
					var element_to_update = 'woo_vou_text';
					var bg_color_to_update = 'woo_vou_text_bg';
					
					if (element.find('.'+element_to_update).is('div, h1,h2,h3,h4,h5,h6, span, i, b, p')) {

						element.find('.'+element_to_update).html(edited);
						element.find('.'+bg_color_to_update).val(bg_color);
						if(bg_color == '') {
							bg_color = '#FFFFFF';
						}
						element.find('.'+bg_color_to_update).closest('.locblock').css('background-color',bg_color);
						
					} else {
						
						element.find('.'+element_to_update).val(edited);
						element.find('.'+bg_color_to_update).val(bg_color);
					}
				});
					
			} else if(jQuery('#woo_vou_pbr_save').hasClass('custom_edit_save')) { // save chages for customblock
					
				var edited = woo_vou_get_tiny_content('wpspbrcustomblockedit');
				var bg_color = jQuery('#woo_vou_edit_bg_color').val();
				
				jQuery("#woo_vou_edit_form #woo_vou_edit_bg_color").each(function(index) {
					
					var element_to_update = 'woo_vou_text';
					var bg_color_to_update = 'woo_vou_text_bg';
					
					if (element.find('.'+element_to_update).is('div, h1,h2,h3,h4,h5,h6, span, i, b, p')) {

						element.find('.'+element_to_update).html(edited);
						element.find('.'+bg_color_to_update).val(bg_color);
						if(bg_color == '') {
							bg_color = '#FFFFFF';
						}
						element.find('.'+bg_color_to_update).closest('.customblock').css('background-color',bg_color);
						
					} else {
						
						element.find('.'+element_to_update).val(edited);
						element.find('.'+bg_color_to_update).val(bg_color);
					}
				});
					
			} else if( jQuery( '#woo_vou_pbr_save' ).hasClass( 'qrcode_edit_save') ) { // save chages for qrcodeblock
				
				var edited				= woo_vou_get_tiny_content( 'wpspbrqrcodeblockedit' );
				
				var qrcode_width		= jQuery('#woo_vou_edit_qrcode_width').val();
				var qrcode_height		= jQuery('#woo_vou_edit_qrcode_height').val();
				var qrcode_color		= jQuery('#woo_vou_edit_qrcode_color').val();
				var qrcode_symbol_type	= jQuery('#woo_vou_edit_qrcode_symbol_type').val();
				var qrcode_response		= jQuery('#woo_vou_edit_qrcode_response').val();
				
				if( jQuery('#woo_vou_edit_qrcode_border').is(":checked") ) {
					var qrcode_border	= 1;
				} else {
					var qrcode_border	= 0;
				}
				
				jQuery( "#woo_vou_edit_form #woo_vou_edit_qrcode_width" ).each( function( index ) {
					
					var element_to_update		  		= 'woo_vou_text';
					var qrcode_width_to_update 	  		= 'woo_vou_qrcode_width';
					var qrcode_height_to_update	  		= 'woo_vou_qrcode_height';
					var qrcode_color_to_update			= 'woo_vou_qrcode_color';
					var qrcode_symbol_type_to_update	= 'woo_vou_qrcode_symbol_type';
					var qrcode_border_to_update   		= 'woo_vou_qrcode_border';
					var qrcode_response_to_update 		= 'woo_vou_qrcode_response';
					
					if( element.find( '.' + element_to_update ).is( 'div, h1,h2,h3,h4,h5,h6, span, i, b, p' ) ) {
						
						element.find( '.' + element_to_update ).html( edited );
						element.find( '.' + qrcode_width_to_update ).val( qrcode_width );
						element.find( '.' + qrcode_height_to_update ).val( qrcode_height );
						element.find( '.' + qrcode_color_to_update ).val( qrcode_color );
						element.find( '.' + qrcode_symbol_type_to_update ).val( qrcode_symbol_type );
						element.find( '.' + qrcode_border_to_update ).val( qrcode_border );
						element.find( '.' + qrcode_response_to_update ).val( qrcode_response );
						
					} else {
						
						element.find( '.' + element_to_update ).val( edited );
						element.find( '.' + qrcode_width_to_update ).val( qrcode_width );
						element.find( '.' + qrcode_height_to_update ).val( qrcode_height );
						element.find( '.' + qrcode_color_to_update ).val( qrcode_color );
						element.find( '.' + qrcode_symbol_type_to_update ).val( qrcode_symbol_type );
						element.find( '.' + qrcode_border_to_update ).val( qrcode_border );
						element.find( '.' + qrcode_response_to_update ).val( qrcode_response );
					}
				});
			} else if( jQuery( '#woo_vou_pbr_save' ).hasClass( 'qrcodes_edit_save') ) { // save chages for qrcodesblock
				
				var edited				= woo_vou_get_tiny_content( 'wpspbrqrcodesblockedit' );
				
				var qrcode_width		= jQuery('#woo_vou_edit_qrcode_width').val();
				var qrcode_height		= jQuery('#woo_vou_edit_qrcode_height').val();
				var qrcode_color		= jQuery('#woo_vou_edit_qrcode_color').val();
				var qrcode_symbol_type	= jQuery('#woo_vou_edit_qrcode_symbol_type').val();
				var qrcode_type			= jQuery('#woo_vou_edit_qrcode_type').val();
				var qrcode_response		= jQuery('#woo_vou_edit_qrcode_response').val();
				
				if( jQuery('#woo_vou_edit_qrcode_border').is(":checked") ) {
					var qrcode_border	= 1;
				} else {
					var qrcode_border	= 0;
				}
				
				jQuery( "#woo_vou_edit_form #woo_vou_edit_qrcode_width" ).each( function( index ) {
					
					var element_to_update		  		= 'woo_vou_text';
					var qrcode_width_to_update	  		= 'woo_vou_qrcode_width';
					var qrcode_height_to_update   		= 'woo_vou_qrcode_height';
					var qrcode_color_to_update	  		= 'woo_vou_qrcode_color';
					var qrcode_symbol_type_to_update	= 'woo_vou_qrcode_symbol_type';
					var qrcode_type_to_update	  		= 'woo_vou_qrcode_type';
					var qrcode_border_to_update   		= 'woo_vou_qrcode_border';
					var qrcode_response_to_update 		= 'woo_vou_qrcode_response';
					
					if( element.find( '.' + element_to_update ).is( 'div, h1,h2,h3,h4,h5,h6, span, i, b, p' ) ) {
						
						element.find( '.' + element_to_update ).html( edited );
						element.find( '.' + qrcode_width_to_update ).val( qrcode_width );
						element.find( '.' + qrcode_height_to_update ).val( qrcode_height );
						element.find( '.' + qrcode_color_to_update ).val( qrcode_color );
						element.find( '.' + qrcode_symbol_type_to_update ).val( qrcode_symbol_type );
						element.find( '.' + qrcode_type_to_update ).val( qrcode_type );
						element.find( '.' + qrcode_border_to_update ).val( qrcode_border );
						element.find( '.' + qrcode_response_to_update ).val( qrcode_response );
						
					} else {
						
						element.find( '.' + element_to_update ).val( edited );
						element.find( '.' + qrcode_width_to_update ).val( qrcode_width );
						element.find( '.' + qrcode_height_to_update ).val( qrcode_height );
						element.find( '.' + qrcode_color_to_update ).val( qrcode_color );
						element.find( '.' + qrcode_symbol_type_to_update ).val( qrcode_symbol_type );
						element.find( '.' + qrcode_type_to_update ).val( qrcode_type );
						element.find( '.' + qrcode_border_to_update ).val( qrcode_border );
						element.find( '.' + qrcode_response_to_update ).val( qrcode_response );
					}
				});
			} else if( jQuery( '#woo_vou_pbr_save' ).hasClass( 'barcode_edit_save') ) { // save chages for barcodeblock
				
				var edited		= woo_vou_get_tiny_content( 'wpspbrbarcodeblockedit' );
				
				var barcode_width	= jQuery('#woo_vou_edit_barcode_width').val();
				var barcode_height	= jQuery('#woo_vou_edit_barcode_height').val();
				var barcode_color	= jQuery('#woo_vou_edit_barcode_color').val();
				var barcode_type	= jQuery('#woo_vou_edit_barcode_type').val();
				
				if( jQuery('#woo_vou_edit_barcode_border').is(":checked") ) {
					var barcode_border	= 1;
				} else {
					var barcode_border	= 0;
				}
				
				jQuery( "#woo_vou_edit_form #woo_vou_edit_barcode_width" ).each( function( index ) {
					
					var element_to_update		 = 'woo_vou_text';
					var barcode_width_to_update	 = 'woo_vou_barcode_width';
					var barcode_height_to_update = 'woo_vou_barcode_height';
					var barcode_color_to_update  = 'woo_vou_barcode_color';
					var barcode_type_to_update   = 'woo_vou_barcode_type';
					var barcode_border_to_update = 'woo_vou_barcode_border';
					
					if( element.find( '.' + element_to_update ).is( 'div, h1,h2,h3,h4,h5,h6, span, i, b, p' ) ) {
						
						element.find( '.' + element_to_update ).html( edited );
						element.find( '.' + barcode_width_to_update ).val( barcode_width );
						element.find( '.' + barcode_height_to_update ).val( barcode_height );
						element.find( '.' + barcode_color_to_update ).val( barcode_color );
						element.find( '.' + barcode_type_to_update ).val( barcode_type );
						element.find( '.' + barcode_border_to_update ).val( barcode_border );
						
					} else {
						
						element.find( '.' + element_to_update ).val( edited );
						element.find( '.' + barcode_width_to_update ).val( barcode_width );
						element.find( '.' + barcode_height_to_update ).val( barcode_height );
						element.find( '.' + barcode_color_to_update ).val( barcode_color );
						element.find( '.' + barcode_type_to_update ).val( barcode_type );
						element.find( '.' + barcode_border_to_update ).val( barcode_border );
					}
				});
			} else if( jQuery( '#woo_vou_pbr_save' ).hasClass( 'barcodes_edit_save') ) { // save chages for barcodeblock
				
				var edited				= woo_vou_get_tiny_content( 'wpspbrbarcodesblockedit' );
				
				var barcode_width		= jQuery('#woo_vou_edit_barcode_width').val();
				var barcode_height		= jQuery('#woo_vou_edit_barcode_height').val();
				var barcode_color		= jQuery('#woo_vou_edit_barcode_color').val();
				var barcode_type		= jQuery('#woo_vou_edit_barcode_type').val();
				var barcode_disp_type	= jQuery('#woo_vou_edit_barcode_disp_type').val();
				
				if( jQuery('#woo_vou_edit_barcode_border').is(":checked") ) {
					var barcode_border	= 1;
				} else {
					var barcode_border	= 0;
				}
				
				jQuery( "#woo_vou_edit_form #woo_vou_edit_barcode_width" ).each( function( index ) {
					
					var element_to_update		 	= 'woo_vou_text';
					var barcode_width_to_update	 	= 'woo_vou_barcode_width';
					var barcode_height_to_update 	= 'woo_vou_barcode_height';
					var barcode_color_to_update  	= 'woo_vou_barcode_color';
					var barcode_type_to_update   	= 'woo_vou_barcode_type';
					var barcode_disp_type_to_update = 'woo_vou_barcode_disp_type';
					var barcode_border_to_update 	= 'woo_vou_barcode_border';
					
					if( element.find( '.' + element_to_update ).is( 'div, h1,h2,h3,h4,h5,h6, span, i, b, p' ) ) {
						
						element.find( '.' + element_to_update ).html( edited );
						element.find( '.' + barcode_width_to_update ).val( barcode_width );
						element.find( '.' + barcode_height_to_update ).val( barcode_height );
						element.find( '.' + barcode_color_to_update ).val( barcode_color );
						element.find( '.' + barcode_type_to_update ).val( barcode_type );
						element.find( '.' + barcode_disp_type_to_update ).val( barcode_disp_type );
						element.find( '.' + barcode_border_to_update ).val( barcode_border );
						
					} else {
						
						element.find( '.' + element_to_update ).val( edited );
						element.find( '.' + barcode_width_to_update ).val( barcode_width );
						element.find( '.' + barcode_height_to_update ).val( barcode_height );
						element.find( '.' + barcode_color_to_update ).val( barcode_color );
						element.find( '.' + barcode_type_to_update ).val( barcode_type );
						element.find( '.' + barcode_disp_type_to_update ).val( barcode_disp_type );
						element.find( '.' + barcode_border_to_update ).val( barcode_border );
					}
				});
			} else if ( jQuery( '#woo_vou_pbr_save' ).hasClass( 'product_image_edit_save') ) {

				var edited			= woo_vou_get_tiny_content( 'wpspbrqrcodeblockedit' );
				
				var product_image_width		= jQuery('#woo_vou_edit_product_image_width').val();
				var product_image_height	= jQuery('#woo_vou_edit_product_image_height').val();
	
				jQuery( "#woo_vou_edit_form #woo_vou_edit_product_image_width" ).each( function( index ) {
					
					var element_to_update		  		= 'woo_vou_text';
					var product_image_width_to_update 	= 'woo_vou_product_image_width';
					var product_image_height_to_update	= 'woo_vou_product_image_height';
	
					if( element.find( '.' + element_to_update ).is( 'div, h1,h2,h3,h4,h5,h6, span, i, b, p' ) ) {
	
						element.find( '.' + element_to_update ).html( edited );
						element.find( '.' + product_image_width_to_update ).val( product_image_width );
						element.find( '.' + product_image_height_to_update ).val( product_image_height );
					} else {
						
						element.find( '.' + element_to_update ).val( edited );
						element.find( '.' + product_image_width_to_update ).val( product_image_width );
						element.find( '.' + product_image_height_to_update ).val( product_image_height );
					}
				});
			}

			jQuery( '.woo_vou_editor' ).hide();
			jQuery( '.woo_vou_controls_editor' ).show();
			jQuery( '.woo_vou_main_editor' ).show();
			jQuery( '#woo_vou_edit_form' ).empty();
			jQuery( '#woo_vou_page_builder' ).removeClass( 'woo_vou_edit_mode' );
			jQuery( '#publish' ).show();
		});
		
		//On click of save changing the value of textarea to p end
	}
	var originalSendToEditor = '';

	if (window.send_to_editor) {
			originalSendToEditor = window.send_to_editor;
	}
	function woo_vou_give_shortcode_to_editor(){
		
		var pbrshortcodes = '<table class="woo_vou_pdf_table">'; // Create table
		var createtr = 0;
		var tdcolspan = 4;
		var i = 0;
		
		jQuery(".text_column").each(function(index) {
			
			var widthclass = $(this).closest('.text_column').find('.woo_vou_txtclass_width').val();
			var tdclass = '';
			
			if( widthclass == 'full_width' ) { // Check 4/4 Width
				tdcolspan = 4;
				tdclass = "full_width";
			} else if( widthclass == 'three_fourth' ) { // Check 3/4 Width
				tdcolspan = 3;
				tdclass = "three_fourth";
			} else if( widthclass == 'one_half' ) { // Check 2/4 Width
				tdcolspan = 2;
				tdclass = "one_half";
			} else if( widthclass == 'one_fourth' ) { // Check 1/4 Width
				tdcolspan = 1;
				tdclass = "one_fourth";
			} else if( widthclass == 'one_third' ) { // Check 1/3 Width
				i = i + 1;

				tdcolspan = 1;

				if( i == 3 ){ // code to fix complete the 4 colspan if 1/3 block add #4493
					i = 0;
					tdcolspan = 2;
				}

				tdclass = "one_third";
			} else {
				tdcolspan = 4;
				tdclass = "full_width";
			}
			
			if( createtr == 0 ) { // First Time Create New Row
				pbrshortcodes += '<tr>';
			}
			createtr += tdcolspan;
			if( createtr > 4 ) { // Check for Create New Row
				createtr = tdcolspan;
				pbrshortcodes += '</tr>';
				pbrshortcodes += '<tr>';
			}
			
			if( tdcolspan > 0) { // Assign Colspan
				pbrshortcodes += '<td colspan="'+tdcolspan+'" class="'+tdclass+'">';
			} else {
				pbrshortcodes += '<td>';
			}
			
			if($(this).closest('.text_column').hasClass('textblock')) { // shortcode for voucher code
				
				var content = $(this).closest('.text_column').find('.woo_vou_text').html();
				var content_codes = $(this).closest('.text_column').find('.woo_vou_text_codes').html();
				var bg_color = $(this).closest('.text_column').find('.woo_vou_text_bg').val();
				var font_color = $(this).closest('.text_column').find('.woo_vou_text_font_color').val();
				var font_size = $(this).closest('.text_column').find('.woo_vou_text_font_size').val();
				var text_align = $(this).closest('.text_column').find('.woo_vou_text_text_align').val();
				var code_text_align = $(this).closest('.text_column').find('.woo_vou_text_code_text_align').val();
				var code_border = $(this).closest('.text_column').find('.woo_vou_text_code_border').val();
				var code_column = $(this).closest('.text_column').find('.woo_vou_text_code_column').val();
				
				pbrshortcodes += '[woo_vou_code_title';
				
					if(bg_color != '' && bg_color != 'undefined') {
						pbrshortcodes += ' bgcolor="'+bg_color+'"';
					}
					
					if(font_size != '' && font_size != 'undefined') {
						pbrshortcodes += ' fontsize="'+font_size+'"';
					}
					if(text_align != '' && text_align != 'undefined') {
						pbrshortcodes += ' textalign="'+text_align+'"';
					}
					
				pbrshortcodes += '] ' + content + ' [/woo_vou_code_title]';
				pbrshortcodes += '[woo_vou_code';
				
					if(code_text_align != '' && code_text_align != 'undefined') {
						pbrshortcodes += ' codetextalign="'+code_text_align+'"';
					}
					if(code_border != '' && code_border != 'undefined') {
						pbrshortcodes += ' codeborder="'+code_border+'"';
					}

				pbrshortcodes += '] ' + content_codes + ' [/woo_vou_code]';

				
			} else if($(this).closest('.text_column').hasClass('messagebox')){ // shortcode for voucher redeem instruction
			
				var content = $(this).closest('.text_column').find('.woo_vou_text').html();
				var bg_color = $(this).closest('.text_column').find('.woo_vou_text_bg').val();
					
				pbrshortcodes += '[woo_vou_redeem';
				
					if(bg_color != '' && bg_color != 'undefined') {
						pbrshortcodes += ' bgcolor="'+bg_color+'"';
					}
					
				pbrshortcodes += '] ' + content + ' [/woo_vou_redeem]';
				
			} else if($(this).closest('.text_column').hasClass('sitelogoblock')) { // shortcode for voucher site logo
				
				var content = $(this).closest('.text_column').find('.woo_vou_text').html();
				
				pbrshortcodes += '[woo_vou_site_logo]' + content + ' [/woo_vou_site_logo]';
				
			} else if($(this).closest('.text_column').hasClass('logoblock')) { // shortcode for voucher logo
				
				var content = $(this).closest('.text_column').find('.woo_vou_text').html();
				
				pbrshortcodes += '[woo_vou_logo]' + content + ' [/woo_vou_logo]';
				
			} else if($(this).closest('.text_column').hasClass('expireblock')) { // shortcode for voucher expire date
				
				var content = $(this).closest('.text_column').find('.woo_vou_text').html();
				var bg_color = $(this).closest('.text_column').find('.woo_vou_text_bg').val();
				
				pbrshortcodes += '[woo_vou_expire_date';
				
					if(bg_color != '' && bg_color != 'undefined') {
						pbrshortcodes += ' bgcolor="'+bg_color+'"';
					}
					
				pbrshortcodes += '] ' + content + ' [/woo_vou_expire_date]';

			} else if($(this).closest('.text_column').hasClass('venaddrblock')) { // shortcode for vendor's address
				
				var content = $(this).closest('.text_column').find('.woo_vou_text').html();
				var bg_color = $(this).closest('.text_column').find('.woo_vou_text_bg').val();
				
				pbrshortcodes += '[woo_vou_vendor_address';
				
					if(bg_color != '' && bg_color != 'undefined') {
						pbrshortcodes += ' bgcolor="'+bg_color+'"';
					}
					
				pbrshortcodes += '] ' + content + ' [/woo_vou_vendor_address]';

			} else if($(this).closest('.text_column').hasClass('siteurlblock')) { // shortcode for website URL
				
				var content = $(this).closest('.text_column').find('.woo_vou_text').html();
				var bg_color = $(this).closest('.text_column').find('.woo_vou_text_bg').val();
				
				pbrshortcodes += '[woo_vou_siteurl';
				
					if(bg_color != '' && bg_color != 'undefined') {
						pbrshortcodes += ' bgcolor="'+bg_color+'"';
					}
					
				pbrshortcodes += '] ' + content + ' [/woo_vou_siteurl]';

			} else if($(this).closest('.text_column').hasClass('locblock')) { // shortcode for vendor's address
				
				var content = $(this).closest('.text_column').find('.woo_vou_text').html();
				var bg_color = $(this).closest('.text_column').find('.woo_vou_text_bg').val();
				
				pbrshortcodes += '[woo_vou_location';
				
					if(bg_color != '' && bg_color != 'undefined') {
						pbrshortcodes += ' bgcolor="'+bg_color+'"';
					}
					
				pbrshortcodes += '] ' + content + ' [/woo_vou_location]';

			} else if($(this).closest('.text_column').hasClass('customblock')) { // shortcode for custom block
				
				var content = $(this).closest('.text_column').find('.woo_vou_text').html();
				var bg_color = $(this).closest('.text_column').find('.woo_vou_text_bg').val();
				
				pbrshortcodes += '[woo_vou_custom';
				
					if(bg_color != '' && bg_color != 'undefined') {
						pbrshortcodes += ' bgcolor="'+bg_color+'"';
					}
					
				pbrshortcodes += '] ' + content + ' [/woo_vou_custom]';

			} else if($(this).closest('.text_column').hasClass('blankbox')){ // shortcode for voucher blank box
				
				pbrshortcodes += '&nbsp;';
				
			} else if( $(this).closest( '.text_column' ).hasClass( 'qrcodeblock' ) ) { // shortcode for voucher qrcode box
				
				var content				= $( this ).closest( '.text_column' ).find( '.woo_vou_text' ).html();
				var qrcode_width		= $( this ).closest( '.text_column' ).find( '.woo_vou_qrcode_width' ).val();
				var qrcode_height		= $( this ).closest( '.text_column' ).find( '.woo_vou_qrcode_height' ).val();
				var qrcode_color		= $( this ).closest( '.text_column' ).find( '.woo_vou_qrcode_color' ).val();
				var qrcode_symbol_type	= $( this ).closest( '.text_column' ).find( '.woo_vou_qrcode_symbol_type' ).val();
				var qrcode_border		= $( this ).closest( '.text_column' ).find( '.woo_vou_qrcode_border' ).val();
				var qrcode_response		= $( this ).closest( '.text_column' ).find( '.woo_vou_qrcode_response' ).val();
				
				pbrshortcodes += '[woo_vou_qrcode';
				
					if(qrcode_width != '' && qrcode_width != 'undefined') {
						pbrshortcodes += ' qrcode_width="'+qrcode_width+'"';
					}
					if(qrcode_height != '' && qrcode_height != 'undefined') {
						pbrshortcodes += ' qrcode_height="'+qrcode_height+'"';
					}
					if(qrcode_color != '' && qrcode_color != 'undefined') {
						pbrshortcodes += ' qrcode_color="'+qrcode_color+'"';
					}
					if(qrcode_symbol_type != '' && qrcode_symbol_type != 'undefined') {
						pbrshortcodes += ' qrcode_symbol_type="'+qrcode_symbol_type+'"';
					}
					if(qrcode_border != '' && qrcode_border != 'undefined') {
						pbrshortcodes += ' qrcode_border="'+qrcode_border+'"';
					}
					if(qrcode_response != '' && qrcode_response != 'undefined') {
						pbrshortcodes += ' qrcode_response="'+qrcode_response+'"';
					}
					
				pbrshortcodes += '] ' + content + ' [/woo_vou_qrcode]';
				
			} else if( $(this).closest( '.text_column' ).hasClass( 'qrcodesblock' ) ) { // shortcodes for voucher qrcodes box
				
				var content		    	= $( this ).closest( '.text_column' ).find( '.woo_vou_text' ).html();
				var qrcode_width    	= $( this ).closest( '.text_column' ).find( '.woo_vou_qrcode_width' ).val();
				var qrcode_height   	= $( this ).closest( '.text_column' ).find( '.woo_vou_qrcode_height' ).val();
				var qrcode_color    	= $( this ).closest( '.text_column' ).find( '.woo_vou_qrcode_color' ).val();
				var qrcode_symbol_type  = $( this ).closest( '.text_column' ).find( '.woo_vou_qrcode_symbol_type' ).val();
				var qrcode_type     	= $( this ).closest( '.text_column' ).find( '.woo_vou_qrcode_type' ).val();
				var qrcode_border   	= $( this ).closest( '.text_column' ).find( '.woo_vou_qrcode_border' ).val();
				var qrcode_response		= $( this ).closest( '.text_column' ).find( '.woo_vou_qrcode_response' ).val();
				
				pbrshortcodes += '[woo_vou_qrcodes';
				
					if(qrcode_width != '' && qrcode_width != 'undefined') {
						pbrshortcodes += ' qrcode_width="'+qrcode_width+'"';
					}
					if(qrcode_height != '' && qrcode_height != 'undefined') {
						pbrshortcodes += ' qrcode_height="'+qrcode_height+'"';
					}
					if(qrcode_color != '' && qrcode_color != 'undefined') {
						pbrshortcodes += ' qrcode_color="'+qrcode_color+'"';
					}
					if(qrcode_color != '' && qrcode_color != 'undefined') {
						pbrshortcodes += ' qrcode_color="'+qrcode_color+'"';
					}
					if(qrcode_symbol_type != '' && qrcode_symbol_type != 'undefined') {
						pbrshortcodes += ' qrcode_symbol_type="'+qrcode_symbol_type+'"';
					}
					if(qrcode_border != '' && qrcode_border != 'undefined') {
						pbrshortcodes += ' qrcode_border="'+qrcode_border+'"';
					}
					if(qrcode_response != '' && qrcode_response != 'undefined') {
						pbrshortcodes += ' qrcode_response="'+qrcode_response+'"';
					}
					
				pbrshortcodes += '] ' + content + ' [/woo_vou_qrcodes]';
				
			} else if( $(this).closest( '.text_column' ).hasClass( 'barcodeblock' ) ) { // shortcode for voucher barcode box
				
				var content			= $( this ).closest( '.text_column' ).find( '.woo_vou_text' ).html();
				var barcode_width	= $( this ).closest( '.text_column' ).find( '.woo_vou_barcode_width' ).val();
				var barcode_height	= $( this ).closest( '.text_column' ).find( '.woo_vou_barcode_height' ).val();
				var barcode_color	= $( this ).closest( '.text_column' ).find( '.woo_vou_barcode_color' ).val();
				var barcode_type	= $( this ).closest( '.text_column' ).find( '.woo_vou_barcode_type' ).val();
				var barcode_border	= $( this ).closest( '.text_column' ).find( '.woo_vou_barcode_border' ).val();
				
				pbrshortcodes += '[woo_vou_barcode';
				
					if(barcode_width != '' && barcode_width != 'undefined') {
						pbrshortcodes += ' barcode_width="'+barcode_width+'"';
					}
					if(barcode_height != '' && barcode_height != 'undefined') {
						pbrshortcodes += ' barcode_height="'+barcode_height+'"';
					}
					if(barcode_color != '' && barcode_color != 'undefined') {
						pbrshortcodes += ' barcode_color="'+barcode_color+'"';
					}
					if(barcode_type != '' && barcode_type != 'undefined') {
						pbrshortcodes += ' barcode_type="'+barcode_type+'"';
					}
					if(barcode_border != '' && barcode_border != 'undefined') {
						pbrshortcodes += ' barcode_border="'+barcode_border+'"';
					}
					
				pbrshortcodes += '] ' + content + ' [/woo_vou_barcode]';
			} else if( $(this).closest( '.text_column' ).hasClass( 'barcodesblock' ) ) { // shortcode for voucher barcodes box
				
				var content				= $( this ).closest( '.text_column' ).find( '.woo_vou_text' ).html();
				var barcode_width		= $( this ).closest( '.text_column' ).find( '.woo_vou_barcode_width' ).val();
				var barcode_height		= $( this ).closest( '.text_column' ).find( '.woo_vou_barcode_height' ).val();
				var barcode_color		= $( this ).closest( '.text_column' ).find( '.woo_vou_barcode_color' ).val();
				var barcode_type		= $( this ).closest( '.text_column' ).find( '.woo_vou_barcode_type' ).val();
				var barcode_disp_type	= $( this ).closest( '.text_column' ).find( '.woo_vou_barcode_disp_type' ).val();
				var barcode_border		= $( this ).closest( '.text_column' ).find( '.woo_vou_barcode_border' ).val();
				
				pbrshortcodes += '[woo_vou_barcodes';
				
					if(barcode_width != '' && barcode_width != 'undefined') {
						pbrshortcodes += ' barcode_width="'+barcode_width+'"';
					}
					if(barcode_height != '' && barcode_height != 'undefined') {
						pbrshortcodes += ' barcode_height="'+barcode_height+'"';
					}
					if(barcode_color != '' && barcode_color != 'undefined') {
						pbrshortcodes += ' barcode_color="'+barcode_color+'"';
					}
					if(barcode_type != '' && barcode_type != 'undefined') {
						pbrshortcodes += ' barcode_type="'+barcode_type+'"';
					}
					if(barcode_disp_type != '' && barcode_disp_type != 'undefined') {
						pbrshortcodes += ' barcode_disp_type="'+barcode_disp_type+'"';
					}
					if(barcode_border != '' && barcode_border != 'undefined') {
						pbrshortcodes += ' barcode_border="'+barcode_border+'"';
					}
					
				pbrshortcodes += '] ' + content + ' [/woo_vou_barcodes]';
			} else if( $(this).closest( '.text_column' ).hasClass( 'productimageblock' ) ) { // shortcode for voucher product image block
				
				var content					= $( this ).closest( '.text_column' ).find( '.woo_vou_text' ).html();
				var product_image_width		= $( this ).closest( '.text_column' ).find( '.woo_vou_product_image_width' ).val();
				var product_image_height	= $( this ).closest( '.text_column' ).find( '.woo_vou_product_image_height' ).val();
				
				pbrshortcodes += '[woo_vou_product_image';
				
					if(product_image_width != '' && product_image_width != 'undefined') {
						pbrshortcodes += ' product_image_width="'+product_image_width+'"';
					}
					if(product_image_height != '' && product_image_height != 'undefined') {
						pbrshortcodes += ' product_image_height="'+product_image_height+'"';
					}
					
				pbrshortcodes += '] ' + content + ' [/woo_vou_product_image]';
				
			}
	
			pbrshortcodes += '</td>'; // Close td part
			
		});
		
		pbrshortcodes += '</tr>'; // Close tr part
		
		pbrshortcodes += '</table>'; // Close table part
		
		woo_vou_switch_default_editor_visual('content');
		if( typeof tinyMCE != "undefined" )
			tinyMCE.get('content').setContent(pbrshortcodes, {format : 'raw'});
	}
	
	/* function for getting the column width */
	function woo_vou_get_column_width(column) {
		
		if (column.hasClass("full_width"))
			return new Array("full_width", false, "three_fourth", "1/1");
		
		else if (column.hasClass("three_fourth"))
			return new Array("three_fourth", "full_width", "one_half", "3/4");
			
		else if (column.hasClass("one_half"))
			return new Array("one_half", "three_fourth", "one_third", "1/2");
			
		else if (column.hasClass("one_third"))
			return new Array("one_third", "one_half", "one_fourth", "1/3");
		
		else if (column.hasClass("one_fourth"))
			return new Array("one_fourth", "one_third", false, "1/4");
		
		else 
			return false;
	} // end woo_vou_get_column_width()

	woo_vou_set_colorpicker( $('.woo-vou-meta-color-iris') );

	//click on  button
	$(document).on('keyup', '.woo_vou_font_size_box', function(e) {

		if( ! /^[0-9]+$/.test( $(this).val() ) ) {

			jQuery( this ).parents( 'td' ).append( '<div class="woo-vou-fade-error">'+ WooVouMessage.invalid_number +'</div>' );

			jQuery( ".woo-vou-fade-error" ).fadeOut( 3000, function() {
				jQuery( '.woo-vou-fade-error' ).remove();
			});

			jQuery( this ).val('');
			return false;
		}
	});
});

function woo_vou_init_tiny_mce() {

	jQuery('.pbrtextareahtml').each(function(index) {
	
		var editor_id = jQuery(this).attr('id');
		
		tinymce.execCommand("mceRemoveEditor", false, editor_id);
		tinymce.execCommand("mceAddEditor", false, editor_id);
		
		jQuery(this).closest('.woo_vou_ajax_editor').find('.wp-switch-editor').removeAttr("onclick");
		jQuery(this).closest('.woo_vou_ajax_editor').find('.switch-tmce').on('click', function() {
			
			jQuery(this).closest('.woo_vou_ajax_editor').find('.wp-editor-wrap').removeClass('html-active').addClass('tmce-active');
			tinyMCE.execCommand("mceAddEditor", false, editor_id);
		});
		
		jQuery(this).closest('.woo_vou_ajax_editor').find('.switch-html').on('click', function() {
			
			jQuery(this).closest('.woo_vou_ajax_editor').find('.wp-editor-wrap').removeClass('tmce-active').addClass('html-active');
			tinyMCE.execCommand("mceRemoveEditor", false, editor_id);
			
		});
		
	});
}
function woo_vou_get_tiny_content(obj) {
	
	var editor_id = obj,
		response;
	
	try {
		response = tinyMCE.get( editor_id).getContent();
		tinyMCE.execCommand('mceRemoveControl', false,  editor_id);
	}
	catch (err) {
		response = switchEditors.wpautop(jQuery('#'+obj).val());
	}
		return response;
}
function woo_vou_set_colorpicker( obj ) {
	
	//code for color picker
	if( WooVouSettings != '1' ) {
		obj.wpColorPicker();
	} else {
		var inputcolor = obj.prev('input').val();
		obj.prev('input').css('background-color',inputcolor);
		obj.on('click', function(e) {
			colorPicker = jQuery(this).next('div');
			input = jQuery(this).prev('input');
			jQuery.farbtastic(jQuery(colorPicker), function(a) { jQuery(input).val(a).css('background', a); });
			colorPicker.show();
			e.preventDefault();
			jQuery(document).mousedown( function() { jQuery(colorPicker).hide(); });
		});
	}	
}