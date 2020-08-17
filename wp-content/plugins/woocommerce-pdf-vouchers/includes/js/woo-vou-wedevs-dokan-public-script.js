"use strict";

jQuery(document).ready(function($){
	 
	//added for  WC Vendors pro compatibility 
	$('.dokan-dashboard ._woo_vou_vendor_user_field  .woo-vou-meta-select').select2();	
	$('.dokan-dashboard ._woo_vou_sec_vendor_users_field .woo-vou-meta-select').select2();	
	$('.dokan-dashboard ._woo_vou_coupon_products_field  .woo-vou-meta-select').select2();	
	$('.dokan-dashboard ._woo_vou_coupon_categories_field .woo-vou-meta-select').select2();	
	$('.dokan-dashboard ._woo_vou_coupon_exclude_products .woo-vou-meta-select').select2();	
	$('.dokan-dashboard ._woo_vou_coupon_exclude_categories_field .woo-vou-meta-select').select2();	
	$('.dokan-dashboard ._woo_vou_coupon_exclude_products_field .woo-vou-meta-select').select2();	
	$('.dokan-dashboard ._woo_vou_pdf_template_selection_field .woo-vou-meta-select').select2();	
	 
	var acess_pdf_pab = WooVouDokanPublic.vou_enable_wedevs_dokan_vendor_acess_pdf_vou_meta
	$( document ).on( 'change', '.dokan-dashboard ._is_downloadable', function(){
		$('body.dokan-dashboard  .dokan-product-pdf-voucher').hide();
		if($(this).is(':checked') && acess_pdf_pab == 'yes'){
			$('body.dokan-dashboard  .dokan-product-pdf-voucher').show();
		}		
	});	  
	if(acess_pdf_pab  == 'yes'){
		$('._woo_vou_enable_coupon_code_field').hide();
		$('._woo_vou_enable_coupon_code_field #_woo_vou_enable_coupon_code').val('no');
	}
	
	jQuery('.woo-vou-meta-datetime').each( function() {
      
  		var jQuerythis  = jQuery(this),
          format = jQuerythis.attr('rel'),
          id = jQuerythis.attr('id');
          	  	
	  
	  		
  	  	if( id == '_woo_vou_product_start_date' || id == '_woo_vou_product_exp_date' && acess_pdf_pab == 'yes' ) {
  	  		
  	  		var pro_start_date = $('#_woo_vou_product_start_date');
			var pro_end_date = $('#_woo_vou_product_exp_date');
			$(pro_start_date).datepicker("destroy");
			$(pro_end_date).datepicker("destroy");
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
			  
});