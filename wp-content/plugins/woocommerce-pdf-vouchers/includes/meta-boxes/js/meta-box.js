"use strict";

/**
 * All Types Meta Box Class JS
 *
 * JS used for the custom metaboxes and other form items.
 *
 * Copyright 2011 Ohad Raz (admin@bainternet.info)
 * @since 1.0
 */

function wooVouUpdateRepeaterFields() {
    
      
    /**
     * Datepicker Field.
     *
     * @since 1.0
     */
    jQuery('.woo-vou-meta-date').each( function() {
      
      var jQuerythis  = jQuery(this),
          format = jQuerythis.attr('rel');
  
      jQuerythis.datepicker( { showButtonPanel: true, dateFormat: format } );
      
    });
    
    jQuery('.woo-vou-meta-datetime').each( function() {
      
      var jQuerythis  = jQuery(this),
          format = jQuerythis.attr('rel');
      jQuerythis.datetimepicker({ampm: true,dateFormat : format, showTime: false,});//
      
    });
  
    /**
     * Timepicker Field.
     *
     * @since 1.0
     */
    jQuery('.woo-vou-meta-time').each( function() {
      
      var jQuerythis   = jQuery(this),
          format   = jQuerythis.attr('rel'),
          aampm    = jQuerythis.attr('data-ampm');
      if ('true' == aampm)
        aampm = true;
      else
        aampm = false;

      jQuerythis.timepicker( { showSecond: true, timeFormat: format, ampm: aampm } );
      
    });
  
    /**
     * Colorpicker Field.
     *
     * @since 1.0
     */
    /*
    
    
    
    /**
     * Select Color Field.
     *
     * @since 1.0
     */
    jQuery(document).on('click', '.woo-vou-meta-color-select', function(){
      var jQuerythis = jQuery(this);
      var id = jQuerythis.attr('rel');
      jQuery(this).siblings('.woo-vou-meta-color-picker').farbtastic("#" + id).toggle();
      return false;
    });
  
    /**
     * Add Files.
     *
     * @since 1.0
     */
    jQuery(document).on('click','.woo-vou-meta-add-file', function() {
      var jQueryfirst = jQuery(this).parent().find('.file-input:first');
      jQueryfirst.clone().insertAfter(jQueryfirst).show();
      return false;
    });
    
     jQuery(document).on('click','.woo-vou-meta-add-fileadvanced', function() {
      var jQueryfirst = jQuery(this).parent().find('.file-input-advanced:first');
      jQueryfirst.clone().insertAfter(jQueryfirst).show();
      return false;
    });
  
    /**
     * Delete File.
     *
     * @since 1.0
     */
  	jQuery( document ).on('click','.woo-vou-meta-upload .woo-vou-meta-delete-file',function(e){
      
      var jQuerythis   = jQuery(this),
          jQueryparent = jQuerythis.parent(),
          data     = jQuerythis.attr('rel');
          
      jQuery.post( ajaxurl, { action: 'at_delete_file', data: data }, function(response) {
        response == '0' ? ( alert( 'File has been successfully deleted.' ), jQueryparent.remove() ) : alert( 'You do NOT have permission to delete this file.' );
      });
      
      return false;
    
    });
  
    /**
     * Reorder Images.
     *
     * @since 1.0
     */
    jQuery('.woo-vou-meta-images').each( function() {
      
      var jQuerythis = jQuery(this);
      var order, data;
      
      jQuerythis.sortable( {
        placeholder: 'ui-state-highlight',
        update: function (){
          order = jQuerythis.sortable('serialize');
          data   = order + '|' + jQuerythis.siblings('.woo-vou-meta-images-data').val();
  
          jQuery.post(ajaxurl, {action: 'at_reorder_images', data: data}, function(response){
            response == '0' ? alert( 'Order saved!' ) : alert( "You don't have permission to reorder images." );
          });
        }
      });
      
    });
    
    /**
     * repeater sortable
     * @since 2.1
     */
    jQuery('.repeater-sortable').sortable();		
  
  }
var Ed_array = Array;
jQuery(document).ready(function($) {

	 /**
     * DateTimepicker Field.
     *
     * @since 1.0
     */
 	 
    jQuery('.woo-vou-meta-datetime').each( function() {
      
  		var jQuerythis  = jQuery(this),
          format = jQuerythis.attr('rel'),
          id = jQuerythis.attr('id');
          	  	
	  	if( id == '_woo_vou_start_date' || id == '_woo_vou_exp_date' ) {
	  		
  	  		var vou_start_date = $('#_woo_vou_start_date');
			var vou_end_date = $('#_woo_vou_exp_date');
			
			vou_start_date.datetimepicker({ 
				dateFormat : format,
				timeFormat: "hh:mm tt",
				ampm: true,
				showTime: false,
				onClose: function(dateText, inst) {
					if (vou_end_date.val() != '') {
						var VouStartDate = vou_start_date.datetimepicker('getDate');
						var VouEndDate = vou_end_date.datetimepicker('getDate');
						if (VouStartDate > VouEndDate)
							vou_end_date.datetimepicker('setDate', VouStartDate);
					}
					else {
						vou_end_date.val(dateText);
					}
				},
				onSelect: function (selectedDateTime){
					vou_end_date.datetimepicker('option', 'minDate', vou_start_date.datetimepicker('getDate') );
				}
			});
			
			vou_end_date.datetimepicker({ 
				dateFormat : format,
				timeFormat: "hh:mm tt",
				ampm: true,
				showTime: false,
				onClose: function(dateText, inst) {
					if (vou_start_date.val() != '') {
						var VouStartDate = vou_start_date.datetimepicker('getDate');
						var VouEndDate = vou_end_date.datetimepicker('getDate');
						if (VouStartDate > VouEndDate)
							vou_end_date.datetimepicker('setDate', VouStartDate);
					}
					else {
						vou_start_date.val(dateText);
					}
				},
				onSelect: function (selectedDateTime){
					vou_start_date.datetimepicker('option', 'maxDate', vou_end_date.datetimepicker('getDate') );
				}
			});
  	  	} else if( id == '_woo_vou_product_start_date' || id == '_woo_vou_product_exp_date' ) {
  	  		
  	  		var pro_start_date = $('#_woo_vou_product_start_date');
			var pro_end_date = $('#_woo_vou_product_exp_date');
			
			pro_start_date.datetimepicker({ 
				dateFormat : format,
				timeFormat: "hh:mm tt",
				ampm: true,
				showTime: false,
				onClose: function(dateText, inst) {
					if (pro_end_date.val() != '') {
						var ProStartDate = pro_start_date.datetimepicker('getDate');
						var ProEndDate = pro_end_date.datetimepicker('getDate');
						if (ProStartDate > ProEndDate)
							pro_end_date.datetimepicker('setDate', ProStartDate);
					}
					else {
						pro_end_date.val(dateText);
					}
				},
				onSelect: function (selectedDateTime){
					pro_end_date.datetimepicker('option', 'minDate', pro_start_date.datetimepicker('getDate') );
				}
			});
			
			pro_end_date.datetimepicker({ 
				dateFormat : format,
				timeFormat: "hh:mm tt",
				ampm: true,
				showTime: false,
				onClose: function(dateText, inst) {
					if (pro_start_date.val() != '') {
						var ProStartDate = pro_start_date.datetimepicker('getDate');
						var ProEndDate = pro_end_date.datetimepicker('getDate');
						if (ProStartDate > ProEndDate)
							pro_end_date.datetimepicker('setDate', ProStartDate);
					}
					else {
						pro_start_date.val(dateText);
					}
				},
				onSelect: function (selectedDateTime){
					pro_start_date.datetimepicker('option', 'maxDate', pro_end_date.datetimepicker('getDate') );
				}
			});
  	  	} else {  	        	
	      	jQuerythis.datetimepicker({ampm: true,dateFormat : format });//,timeFormat:'hh:mm:ss',showSecond:true
  	  	}	  	
	});
        
  /**
   *  conditinal fields
   *  @since 2.9.9
   */
  jQuery(document).on('click', ".conditinal_control",function(){
    if(jQuery(this).is(':checked')){
      jQuery(this).next().show('fast');    
    }else{
      jQuery(this).next().hide('fast');    
    }
  });

  /**
   * repeater sortable
   * @since 2.1
   */
  jQuery('.repeater-sortable').sortable(); 
  
  /**
   * repater Field
   * @since 1.1
   */
  //edit
  jQuery( document ).on('click','.woo-vou-meta-re-toggle',function(){
    //jQuery(this).prev().toggle('slow');
    if( jQuery(this).prev().is(':visible') ) {
    	jQuery(this).prev().hide();
    } else {
    	jQuery(this).prev().show();
    }
  });
  
  
  /**
   * Datepicker Field.
   *
   * @since 1.0
   */
  jQuery('.woo-vou-meta-date').each( function() {
    
    var jQuerythis  = jQuery(this),
        format = jQuerythis.attr('rel');

    jQuerythis.datepicker( { showButtonPanel: true, dateFormat: format } );
    
  });

  /**
   * Timepicker Field.
   *
   * @since 1.0
   */
  jQuery('.woo-vou-meta-time').each( function() {
    
    var jQuerythis   = jQuery(this),
          format   = jQuerythis.attr('rel'),
          aampm    = jQuerythis.attr('data-ampm');
      if ('true' == aampm)
        aampm = true;
      else
        aampm = false;

      jQuerythis.timepicker( { showSecond: true, timeFormat: format, ampm: aampm } );
    
  });

  /**
   * Colorpicker Field.
   *
   * @since 1.0
   * better handler for color picker with repeater fields support
   * which now works both when button is clicked and when field gains focus.
   */
  if (jQuery.farbtastic){//since WordPress 3.5
  	jQuery( document ).on('focus','.woo-vou-meta-color',function(){
      load_colorPicker(jQuery(this).next());
    });

  	jQuery( document ).on('focusout','.woo-vou-meta-color',function(){
      hide_colorPicker(jQuery(this).next());
    });

    /**
     * Select Color Field.
     *
     * @since 1.0
     */
  	jQuery( document ).on('click','.woo-vou-meta-color-select',function(){
      if (jQuery(this).next('div').css('display') == 'none')
        load_colorPicker(jQuery(this));
      else
        hide_colorPicker(jQuery(this));
    });

    function load_colorPicker(ele){
      var colorPicker = jQuery(ele).next('div');
      var input = jQuery(ele).prev('input');

      jQuery.farbtastic(jQuery(colorPicker), function(a) { jQuery(input).val(a).css('background', a); });

      colorPicker.show();

    }

    function hide_colorPicker(ele){
      var colorPicker = jQuery(ele).next('div');
      jQuery(colorPicker).hide();
    }
    //issue #15
    jQuery('.woo-vou-meta-color').each(function(){
      var colo = jQuery(this).val();
      if (colo.length == 7)
        jQuery(this).css('background',colo);
    });
  }
  
  /**
   * Add Files.
   *
   * @since 1.0
   */
  jQuery(document).on('click','.woo-vou-meta-add-file', function() {
    var jQueryfirst = jQuery(this).parent().find('.file-input:first');
    jQueryfirst.clone().insertAfter(jQueryfirst).show();
    return false;
  });
  /*
  *
  * Advanced Add Files
  */
  jQuery( document ).on('click','.woo-vou-meta-add-fileadvanced',function(){
     var jQueryfirst = jQuery(this).parent().find('.file-input-advanced:last');
     jQueryfirst.clone().insertAfter(jQueryfirst).show();
     jQuery(this).parent().find('.file-input-advanced:last .woo-vou-upload-file-link').val('');
     jQuery(this).parent().find('.file-input-advanced:last .woo-vou-upload-file-name').val('');
     return false;
   });
   
  /*
   *
   * Advanced Add Files
   */
  jQuery( document ).on('click','.woo-vou-delete-fileadvanced',function(){
  	var row = jQuery(this).parent().parent().parent( 'tr' );
  	var count =	row.find('.file-input-advanced').length;
	  	if(count > 1) {
	     jQuery(this).parent('.file-input-advanced').remove();
	  	} else {
	  		alert( WooVou.one_file_min );
	  	}
     return false;
   });
   
   // WP 3.5+ uploader
	
  	jQuery( document ).on('click','.woo-vou-upload-fileadvanced',function(e){

		e.preventDefault();
		
		if(typeof wp == "undefined" || WooVou.new_media_ui != '1' ){// check for media uploader
				
			//Old Media uploader
				
			window.formfield = '';
			e.preventDefault();
			
			window.formfield = jQuery(this).closest('.file-input-advanced');
			
			tb_show('', 'media-upload.php?post_id='+ jQuery('#post_ID').val() + '&type=image&amp;TB_iframe=true');
		      //store old send to editor function
		      window.restore_send_to_editor = window.send_to_editor;
		      //overwrite send to editor function
		      window.send_to_editor = function(html) {
		        var attachmenturl = jQuery('a', '<div>' + html + '</div>').attr('href');
		        var attachmentname = jQuery('a', '<div>' + html + '</div>').html();
		        
		        window.formfield.find('.woo-vou-upload-file-link').val(attachmenturl);
	        	window.formfield.find('.woo-vou-upload-file-name').val(attachmentname);
		        wooVouLoadImagesMuploader();
		        tb_remove();
		        //restore old send to editor function
		        window.send_to_editor = window.restore_send_to_editor;
		      }
	      return false;
		      
		} else {
			
			var file_frame;
			window.formfield = '';
			
			//new media uploader
			var button = jQuery(this);
	
			window.formfield = jQuery(this).closest('.file-input-advanced');
		
			// If the media frame already exists, reopen it.
			if ( file_frame ) {

				file_frame.open();
			  return;
			}
	
			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
				frame: 'post',
				state: 'insert',
				title: button.data( 'uploader_title' ),
				button: {
					text: button.data( 'uploader_button_text' ),
				},
				multiple: true  // Set to true to allow multiple files to be selected
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
					
					if(index == 0){
						// place first attachment in field
						window.formfield.find('.woo-vou-upload-file-link').val(attachment_url);
						window.formfield.find('.woo-vou-upload-file-name').val(attachment.name);
						
					} else{
						window.formfield.find('.woo-vou-upload-file-name').val(attachment.name);
						window.formfield.find('.woo-vou-upload-file-link').val(attachment_url);
						
					}
				});
			});
	
			// Finally, open the modal
			file_frame.open();
		}
		
	});

  /**
   * Delete File.
   *
   * @since 1.0
   */
  jQuery( document ).on('click','.woo-vou-meta-upload .woo-vou-meta-delete-file',function(e){
    
    var jQuerythis   = jQuery(this),
        jQueryparent = jQuerythis.parent(),
        data = jQuerythis.attr('rel');
    
    var ind = jQuery(this).index()
    jQuery.post( ajaxurl, { action: 'atm_delete_file', data: data, tag_id: jQuery('#post_ID').val() }, function(response) {
      response == '0' ? ( alert( 'File has been successfully deleted.' ), jQueryparent.remove() ) : alert( 'You do NOT have permission to delete this file.' );
    });
    
    return false;
  
  });

	//Media Uploader
	$( document ).on( 'click', '.woo-vou-meta-upload-button', function() {
	
		var imgfield,showfield;
		imgfield = jQuery(this).prev('input').attr('id');
		showfield = jQuery(this).parents('td').find('.woo-vou-img-view');
		 
		if(typeof wp == "undefined" || WooVou.new_media_ui != '1' ){// check for media uploader
				
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

  //new image upload field
  function wooVouLoadImagesMuploader(){
    jQuery(".mupload_img_holder").each(function(i,v){
      if (jQuery(this).next().next().val() != ''){
        if (!jQuery(this).children().size() > 0){
          jQuery(this).append('<img src="' + jQuery(this).next().next().val() + '" style="height: 150px;width: 150px;" />');
          jQuery(this).next().next().next().val("Delete Image");
          jQuery(this).next().next().next().removeClass('woo-vou-meta-upload_image_button').addClass('woo-vou-meta-delete_image_button');
        }
      }
    });
  }
  
  wooVouLoadImagesMuploader();
  //delete img button
  
  jQuery( document ).on('click','.woo-vou-meta-delete_image_button',function(e){
  	jQuery(this).prev().val('');
  	jQuery(this).prev().prev().val('');
  	jQuery(this).prev().prev().prev().html('');
  	jQuery(this).val("Upload Image");
    jQuery(this).removeClass('woo-vou-meta-delete_image_button').addClass('woo-vou-meta-upload_image_button');
  });
 
  
  
  // WP 3.5+ uploader
	
	var formfield1;
    var formfield2;
    
  	jQuery( document ).on('click','.woo-vou-meta-upload_image_button',function(e){

		e.preventDefault();
		formfield1 = jQuery(this).prev();
		formfield2 = jQuery(this).prev().prev();
		var button = jQuery(this);
			
		if(typeof wp == "undefined" || WooVou.new_media_ui != '1' ){// check for media uploader//
			 
			  tb_show('', 'media-upload.php?post_id='+ jQuery('#post_ID').val() + '&type=image&amp;TB_iframe=true');
		      //store old send to editor function
		      window.restore_send_to_editor = window.send_to_editor;
		      //overwrite send to editor function
		      window.send_to_editor = function(html) {
		      	
		        var imgurl = jQuery('img',html).attr('src');
		        
		        if(jQuery('img',html).attr('class')) {
		        	
			        var img_calsses = jQuery('img',html).attr('class').split(" ");
			        var att_id = '';
			        jQuery.each(img_calsses,function(i,val){
			          if (val.indexOf("wp-image") != -1){
			            att_id = val.replace('wp-image-', "");
			          }
			        });
			
			        jQuery(formfield2).val(att_id);
		        }
		        
		        jQuery(formfield1).val(imgurl);
		        wooVouLoadImagesMuploader();
		        tb_remove();
		        //restore old send to editor function
		        window.send_to_editor = window.restore_send_to_editor;
		      }
		      return false;
		      
		} else {
			
			
			var file_frame;
			
			// If the media frame already exists, reopen it.
			if ( file_frame ) {

				file_frame.open();
			  return;
			}
	
			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
				frame: 'post',
				state: 'insert',
				title: button.data( 'uploader_title' ),
				button: {
					text: button.data( 'uploader_button_text' ),
				},
				multiple: true  // Set to true to allow multiple files to be selected
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
					
					if(index == 0){
						// place first attachment in field
						jQuery(formfield2).val(attachment.id);
	        			jQuery(formfield1).val(attachment_url);
	        			wooVouLoadImagesMuploader();
					
					} else{
						
						jQuery(formfield2).val(attachment.id);
	        			jQuery(formfield1).val(attachment_url);
	        			wooVouLoadImagesMuploader();
					}
				});
			});
	
			// Finally, open the modal
			file_frame.open();
		}
		
	});
  
  
  //added for tabs in metabox
  // tab between them
	jQuery('.metabox-tabs li a').each(function(i) {
		var thisTab = jQuery(this).parent().attr('class').replace(/active /, '');
		
		if ( 'active' != jQuery(this).attr('class') )
			jQuery('div.' + thisTab).hide();

		jQuery('div.' + thisTab).addClass('tab-content');
 
		jQuery(this).on('click',function(){
			// hide all child content
			jQuery(this).parent().parent().parent().children('div').hide();
 
			// remove all active tabs
			jQuery(this).parent().parent('ul').find('li.active').removeClass('active');
 
			// show selected content
			jQuery(this).parent().parent().parent().find('div.'+thisTab).show();
			jQuery(this).parent().parent().parent().find('li.'+thisTab).addClass('active');
		});
	});

	jQuery('.metabox-tabs').show();
	
	wooVouCheckErrorMessage();
  	jQuery( document ).on('click','#_woo_vou_enable',function(e){
		wooVouCheckErrorMessage();
	});
	
  	jQuery( document ).on('blur','#_woo_vou_end_date',function(e){
		wooVouCheckErrorMessage();
	});
		
    /**
     * Select Background Image Option.
     *
     * @since 1.0
     */
    
	// Background style code
    jQuery( document ).on( 'click', '.woo-vou-meta-radio', function(){
    
    	var bg_style = jQuery( this ).val();
    	
		jQuery( '.woo-vou-meta-bg-pattern-wrap' ).hide();
    	jQuery( '.woo-vou-meta-bg-image-wrap' ).hide();
    	
    	if( bg_style == 'image' ) { // Check backgroung image
	    	
	    	jQuery( '.woo-vou-meta-bg-image-wrap' ).show();
	    	
    	} else if( bg_style == 'pattern' ) { // Check backgroung pattern
    		
    		jQuery( '.woo-vou-meta-bg-pattern-wrap' ).show();
    	}
    	
    });
    
    /**
     * Select Background Pattern Field.
     *
     * @since 1.0
     */
    jQuery( document ).on( 'click', '.woo-vou-meta-bg-patterns', function(){
    
    	var pattern = jQuery( this ).attr( 'data-pattern' );
    	jQuery( '.woo-vou-meta-bg-patterns' ).removeClass( 'woo-vou-meta-bg-pattern-selected' );
    	jQuery( '#woo_vou_meta_img_' + pattern ).addClass( 'woo-vou-meta-bg-pattern-selected' );
    	jQuery( '.woo-vou-meta-bg-patterns-opt' ).val( pattern );
    	
    });       
	    
});

function wooVouCheckErrorMessage() {
	
	var end_date = jQuery('#_woo_vou_end_date').val();
	
	if( jQuery('#_woo_vou_enable').is(":checked") && end_date == '' ) {
		
		jQuery('#woo_vou_error_message_box').addClass('woo-vou-error-message').show();
	} else {
		jQuery('#woo_vou_error_message_box').removeClass('woo-vou-error-message').hide();
	}
}
