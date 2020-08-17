const wkmpAdminAjax = jQuery.noConflict();

wkmpAdminAjax(document).ready(function () {
	if ( window.history.replaceState ) {
		window.history.replaceState( null, null, window.location.href );
	  }
	
	  wkmpAdminAjax( '.wkmp-order-refund-button' ).on( 'click', (e) => {
	
		wkmpAdminAjax( '.wkmp-order-refund' ).toggle();
	
		if( wkmpAdminAjax( '.wkmp-order-refund' ).css('display') == 'table-cell' ) {
		  wkmpAdminAjax( e.target).text(the_mpajax_script.mkt_tr.fajax16)
		} else {
		  wkmpAdminAjax( e.target).text(the_mpajax_script.mkt_tr.fajax17)
		}
	
	  } );
	
	  if( wkmpAdminAjax( '.refund_line_total' ) ) {
	
		wkmpAdminAjax( '.refund_line_total' ).on( 'change', (e) => {
	
		  let refundTotal = 0;
	
		  document.querySelectorAll( '.refund_line_total' ).forEach( (input) => {
	
			let qty = 0;
	
			if( input.type === 'checkbox' && input.checked ) {
	
			  qty = input.value;
	
			} else if( input.type !== 'checkbox' ) {
	
			  qty = input.value;
	
			}
	
			refundTotal += qty * input.previousElementSibling.value;
	
		  } );
	
		  document.querySelector( '#refund-amount' ).value = Math.round( refundTotal * 100 ) / 100;
	
		} );
	  }
	wkmpAdminAjax('.seller-query-revert').on('click', function(evt){
		query_id = wkmpAdminAjax(this).data('qid');
		reply_message = wkmpAdminAjax(this).prev('div').find('.admin_msg_to_seller').val();
		reply_message = reply_message.replace(/\r\n|\r|\n/g,"<br/>");
		wkmpAdminAjax.ajax({
			type: 'POST',
			url: the_mpadminajax_script.mpajaxurl,
			data: {
				"action":"send_mail_to_seller",
				"qid":query_id,
				"reply_message": reply_message,
				"nonce":the_mpadminajax_script.nonce
			},
			success: function (data) {
				if (data) {
						location.reload()
				} else{
					alert( the_mpadminajax_script.adajax_tr.aajax29 );
				}
			}

		})
	});

	//banner trigger file upload
   wkmpAdminAjax('#wkmpAdminAjax_seller_banner').click(function(){
    wkmpAdminAjax('#wk_mp_shop_banner').trigger('click');
  });

	wkmpAdminAjax('.wkmpAdminAjax-fade-banner').click(function(){
  document.getElementById('wk_mp_shop_banner').addEventListener('change', changeseller_bannerimage, false);
  });

	wkmpAdminAjax('.mp_seller_profile_img').click(function(){
  document.getElementById('mp_useravatar').addEventListener('change', changeprofile_image, false);
  });

	wkmpAdminAjax('.Company_Logo').click(function(){
  document.getElementById('mp_company_logo').addEventListener('change', seller_logo_image, false);
  });

	/* function to change profile image */
	function changeprofile_image(evt) {
		wkmpAdminAjax('#mp_seller_image').empty();
		var files = evt.target.files;
		for (var i = 0, f; f = files[i]; i++)
	{
		if (!f.type.match('image.*'))
		{
			continue;
		}
		var reader = new FileReader();
		reader.onload = (function(theFile){
			return function(e)
			{
				var div = document.createElement('div');
				div.innerHTML = ['<img class="thumb" src="', e.target.result,'" title="', escape(theFile.name), '"/><span class="wkmpAdminAjax_image_over" ></span><input type="hidden" name ="mpthumbimg[]" value="',escape(theFile.name),'">'].join('');
				document.getElementById('mp_seller_image').insertBefore(div, null);
				wkmpAdminAjax('#mp_seller_image div').attr({class:'imgdiv'});
			};
				})(f);
		reader.readAsDataURL(f);
		}
	}

	/* seller logo image */
  function seller_logo_image(evt) {
    wkmpAdminAjax('#seller_com_logo_img').empty();

    var files = evt.target.files;
    for (var i = 0, f; f = files[i]; i++)
  {
    if (!f.type.match('image.*'))
    {
      continue;
    }
    var reader = new FileReader();
    reader.onload = (function(theFile){
      return function(e)
      {
        var div = document.createElement('div');
        div.innerHTML = ['<img class="thumb" src="', e.target.result,'" title="', escape(theFile.name), '"/><span class="wkmpAdminAjax_image_over" ></span><input type="hidden" name ="mpthumbimg[]" value="',escape(theFile.name),'">'].join('');
        if(document.getElementById('seller_com_logo_img')!=''){
          document.getElementById('seller_com_logo_img').insertBefore(div, null);
        }
        wkmpAdminAjax('#seller_com_logo_img div').attr({class:'imgdiv'});
      };
        })(f);
    reader.readAsDataURL(f);
    }
  }

	/* function to change banner image */
	function changeseller_bannerimage(evt) {
		wkmpAdminAjax('#wk_seller_banner').empty();

		var files = evt.target.files;
		for (var i = 0, f; f = files[i]; i++)
	{
		if (!f.type.match('image.*'))
		{
			continue;
		}
		var reader = new FileReader();
		reader.onload = (function(theFile){
			return function(e){
				var div = document.createElement('div');
				div.innerHTML = ['<img class="thumb" src="', e.target.result,'" title="', escape(theFile.name), '"/><span class="wkmpAdminAjax_image_over" ></span><input type="hidden" name ="mpthumbimg[]" value="',escape(theFile.name),'">'].join('');
				document.getElementById('wk_seller_banner').insertBefore(div, null);
				wkmpAdminAjax('#wk_seller_banner div').attr({class:'imgdiv'});
			};
				})(f);
		reader.readAsDataURL(f);
		}
	}

	/* remove logo image start */
  wkmpAdminAjax('#seller_com_logo_img .mp-image-remove-icon').on('click', function() {
    var thumbId = wkmpAdminAjax(this).data('id')
    var defaultSrc = wkmpAdminAjax(this).data('default')
    wkmpAdminAjax(this).siblings('img').attr('src', defaultSrc)
    wkmpAdminAjax(this).next('.mp-remove-company-logo').val(thumbId)
    wkmpAdminAjax(this).remove()
  })
  /* remove logo image ends */

  /* remove banner image start */
  wkmpAdminAjax('#wk_seller_banner .mp-image-remove-icon').on('click', function() {
    var thumbId = wkmpAdminAjax(this).data('id')
    var defaultSrc = wkmpAdminAjax(this).data('default')
    wkmpAdminAjax(this).siblings('img').attr('src', defaultSrc)
    wkmpAdminAjax(this).next('.mp-remove-shop-banner').val(thumbId)
    wkmpAdminAjax(this).remove()
  })
  /* remove banner image ends */

  /* remove banner image start */
  wkmpAdminAjax('#mp_seller_image .mp-image-remove-icon').on('click', function() {
    var thumbId = wkmpAdminAjax(this).data('id')
    var defaultSrc = wkmpAdminAjax(this).data('default')
    wkmpAdminAjax(this).siblings('img').attr('src', defaultSrc)
    wkmpAdminAjax(this).next('.mp-remove-avatar').val(thumbId)
    wkmpAdminAjax(this).remove()
  })

	wkmpAdminAjax( '#wp-admin-bar-mp-seperate-seller-dashboard a' ).on( 'click', function( ev ){
		nonce = wkmpAdminAjax(this).attr('href');
		nonce = nonce.split( '=' );
		nonce = nonce[1];

		if( nonce == the_mpadminajax_script.nonce ) {
			ev.preventDefault();
			wkmpAdminAjax.ajax({
				type: 'POST',
				url: the_mpadminajax_script.mpajaxurl,
				data: {
					"action":"change_seller_dashboard",
					"change_to":'front_dashboard',
					"nonce":the_mpadminajax_script.nonce
				},
				success: function (data) {
					data = wkmpAdminAjax.parseJSON(data);
					if (data) {
						window.location.href = data.redirect;
					}
				}

			})
		}
	} );

	if ( wkmpAdminAjax('#wk_store_country').length ) {
	  wkmpAdminAjax('#wk_store_country').select2();
	  if ( wkmpAdminAjax('#wk_store_state').is( 'select' ) ) {
	    wkmpAdminAjax('#wk_store_state').select2();
	  }
	}

	wkmpAdminAjax( '#seller_countries_field' ).on( 'change', function(ert){

		if(wkmpAdminAjax( '#wk_store_country' ).val()){
		country_code = wkmpAdminAjax( '#wk_store_country' ).val();
		wkmpAdminAjax.ajax({
			type: 'POST',
			url: the_mpadminajax_script.mpajaxurl,
			data: {
				"action": "country_get_state",
				"country_code": country_code,
				"nonce": the_mpadminajax_script.nonce,
			},
			success: function (data) {

				if( data ){
				// wkmpAdminAjax('#wk_store_state').replaceWith(data);
								wkmpAdminAjax('#wk_store_state').siblings('span.select2').remove();
				wkmpAdminAjax('#wk_store_state').replaceWith(data);
				if ( wkmpAdminAjax('#wk_store_state').is( 'select' ) ) {
					wkmpAdminAjax('#wk_store_state').select2();
				}
				}
			}
			});
		}
  	});


	var name_regex = /^[a-zA-Z\s-, ]+$/
	var contact_regex = /^[0-9]+$/

	wkmpAdminAjax(document).on("blur","#tmplt_name",function(){
		if(wkmpAdminAjax("#tmplt_name").val()==''){
				wkmpAdminAjax("#tmplt_name").next("span.name_err").text(the_mpadminajax_script.adajax_tr.aajax1);
		}
		else{
			if(!wkmpAdminAjax("#tmplt_name").val().match(name_regex)){
				wkmpAdminAjax("#tmplt_name").next("span.name_err").text(the_mpadminajax_script.adajax_tr.aajax2);
			} else{
				wkmpAdminAjax("#tmplt_name").next("span.name_err").text('');
			}
		}
	});

	wkmpAdminAjax(document).on("blur","#clr1",function(){
		if(wkmpAdminAjax("#clr1").val()==''){
			wkmpAdminAjax("#clr1").next("span.bsclr_err").text(the_mpadminajax_script.adajax_tr.aajax1);
		}
		else{
			wkmpAdminAjax("#clr1").next("span.bsclr_err").text('');
		}
	});

	wkmpAdminAjax(document).on("blur","#clr2",function(){
		if(wkmpAdminAjax("#clr2").val()==''){
			wkmpAdminAjax("#clr2").next("span.bdclr_err").text(the_mpadminajax_script.adajax_tr.aajax1);
		} else{
			wkmpAdminAjax("#clr2").next("span.bdclr_err").text('');
		}
	});

	wkmpAdminAjax(document).on("blur","#clr3",function(){
		if(wkmpAdminAjax("#clr3").val()==''){
			wkmpAdminAjax("#clr3").next("span.bkclr_err").text(the_mpadminajax_script.adajax_tr.aajax1);
		}else{
			wkmpAdminAjax("#clr3").next("span.bkclr_err").text('');
		}
	});

	wkmpAdminAjax(document).on("blur","#clr4",function(){
		if(wkmpAdminAjax("#clr4").val()==''){
			wkmpAdminAjax("#clr4").next("span.txclr_err").text(the_mpadminajax_script.adajax_tr.aajax1);
		}
		else{
			wkmpAdminAjax("#clr4").next("span.txclr_err").text('');
		}
	});


	wkmpAdminAjax("form#emailtemplate input").on('focus',function(evt) {
		wkmpAdminAjax('span.required').remove();
	});

 	wkmpAdminAjax("form#emailtemplate").on('submit',function(evt){

		var t_name=wkmpAdminAjax('.tmplt_name').val();
		var t_clr1=wkmpAdminAjax('#clr1').val();
		var t_clr2=wkmpAdminAjax('#clr2').val();
		var t_clr3=wkmpAdminAjax('#clr3').val();
		var t_clr4=wkmpAdminAjax('#clr4').val();
		var error = 0

		var name_regex = /^[a-zA-Z0-9\s-, ]+$/;
		var contact_regex = /^[0-9]+$/;

		wkmpAdminAjax('span.required').remove();
		if(t_name=='' || t_clr1=='' || t_clr2=='' || t_clr3=='' || t_clr4=='') {
			if(t_name==''){
				wkmpAdminAjax('.tmplt_name').after('<span class="required">'+the_mpadminajax_script.adajax_tr.aajax3+'</span>');
				return false;
			}else{
				if(!name_regex.test(t_name)){
					wkmpAdminAjax('.tmplt_name').after('<span class="required">'+the_mpadminajax_script.adajax_tr.aajax2+'</span>');
					return false;
				}
			}

			if(t_clr1==''){
				wkmpAdminAjax('#clr1').after('<span class="required">'+the_mpadminajax_script.adajax_tr.aajax5+'</span>');
				return false;
			}
			if(t_clr2==''){
				wkmpAdminAjax('#clr2').after('<span class="required">'+the_mpadminajax_script.adajax_tr.aajax6+'</span>');
				return false;
			}
			if(t_clr3==''){
				wkmpAdminAjax('#clr3').after('<span class="required">'+the_mpadminajax_script.adajax_tr.aajax7+'</span>');
				return false;
			}
			if(t_clr4==''){
				wkmpAdminAjax('#clr4').after('<span class="required">'+the_mpadminajax_script.adajax_tr.aajax8+'</span>');
				return false;
			}
			if(t_width==''){
				wkmpAdminAjax('.width_err').after('<br><span class="required">'+the_mpadminajax_script.adajax_tr.aajax9+'</span>');
				return false;
			}else{
				if((!b_contact.match(contact_regex))){
					wkmpAdminAjax('.width_err').after('<span class="required">'+the_mpadminajax_script.adajax_tr.aajax26+'</span>');
					return false;
				}
			}
			evt.preventDefault();
		}
		else{
			if(!name_regex.test(t_name)){
				wkmpAdminAjax('.tmplt_name').after('<span class="required">'+the_mpadminajax_script.adajax_tr.aajax2+'</span>');
				evt.preventDefault();
			}
		}
 	});

	wkmpAdminAjax(document).on('click','a.wk_seller_app_button', function(w){
		w.preventDefault();	
		let seller_status=this.id;
		let elm = wkmpAdminAjax(this);
		let status =  confirm(the_mpadminajax_script.adajax_tr.aajax10);
		if ( status ) {
			wkmpAdminAjax.ajax({
				type: 'POST',
				url: the_mpadminajax_script.mpajaxurl,
				data: {"action": "wk_admin_seller_approve", "seller_app":seller_status},
				beforeSend : function(){
						elm.addClass('mp-disabled');
						elm.attr('href','');
				},
				success: function(data){
					let sel_data=data.split(':');
					if(sel_data[1]==0) {
						wkmpAdminAjax('#'+seller_status).addClass('active');
						var this_sel_id='wk_seller_approval_mp'+sel_data[0]+'_mp1';
						this_sel_id=this_sel_id.replace(/\s+/g, '');
						wkmpAdminAjax('#'+seller_status).text(the_mpadminajax_script.adajax_tr.aajax11);
						wkmpAdminAjax('#'+seller_status).attr('id',this_sel_id);
					}
					else {
						wkmpAdminAjax('#'+seller_status).removeClass('active');
						let this_sel_id='wk_seller_approval_mp'+sel_data[0]+'_mp0';
						this_sel_id=this_sel_id.replace(/\s+/g, '');
						wkmpAdminAjax('#'+seller_status).text(the_mpadminajax_script.adajax_tr.aajax12);
						wkmpAdminAjax('#'+seller_status).attr('id',this_sel_id);
					}
					elm.removeClass('mp-disabled');
				}
			});
		}
	});

	if (wkmpAdminAjax(".return-seller select").length) {
		wkmpAdminAjax(".return-seller select").select2()
	}

	wkmpAdminAjax('select#role').on('change', function() {

		if (wkmpAdminAjax(this).val() == 'wk_marketplace_seller') {
			wkmpAdminAjax('.mp-seller-details').show();
			wkmpAdminAjax('#org-name').focus();
		}
		else {
			wkmpAdminAjax('.mp-seller-details').hide();
		}

	});

	wkmpAdminAjax('#org-name').on('focusout', function() {
		var value = wkmpAdminAjax(this).val().toLowerCase().replace(/-+/g, '').replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
		if (value == '') {
			wkmpAdminAjax('#seller-shop-alert-msg').removeClass('text-success').addClass('text-danger').text(the_mpadminajax_script.adajax_tr.aajax13);
			wkmpAdminAjax('#org-name').focus();
		}
		else {
			wkmpAdminAjax('#seller-shop-alert-msg').text("");
		}
		wkmpAdminAjax('#seller-shop').val(value);
	});

	wkmpAdminAjax('#seller-shop').on('focusout', function() {
		var self = wkmpAdminAjax(this);
		wkmpAdminAjax.ajax({
			type: 'POST',
			url: the_mpadminajax_script.mpajaxurl,
			data: {"action": "wk_check_myshop","shop_slug":self.val(),"nonce":the_mpadminajax_script.nonce},
			success: function(response)
			{
				if ( response == 0){
					wkmpAdminAjax('#seller-shop-alert').removeClass('text-success').addClass('text-danger');
					wkmpAdminAjax('#seller-shop-alert-msg').removeClass('text-success').addClass('text-danger').text(the_mpadminajax_script.adajax_tr.aajax14);
				}else if(response == 2){
					wkmpAdminAjax('#seller-shop-alert').removeClass('text-success').addClass('text-danger');
					wkmpAdminAjax('#seller-shop-alert-msg').removeClass('text-success').addClass('text-danger').text(the_mpadminajax_script.adajax_tr.aajax15);
					wkmpAdminAjax('#org-name').focus();
				}else{
					wkmpAdminAjax('#seller-shop-alert').removeClass('text-danger').addClass('text-success');
					wkmpAdminAjax('#seller-shop-alert-msg').removeClass('text-danger').addClass('text-success').text(the_mpadminajax_script.adajax_tr.aajax16);
				}
			}
		});
	});

	wkmpAdminAjax(document).ready(function($){
		wkmpAdminAjax("#uploadButton").click(function(event) {
			var frame = wp.media({
			title: the_mpadminajax_script.adajax_tr.aajax17,
			button: {
				text: the_mpadminajax_script.adajax_tr.aajax18,
			},
			multiple: false
			});
			frame.on( 'select', function() {
				var attachment = frame.state().get('selection').first().toJSON();
				wkmpAdminAjax("#img_url").val(attachment.url);
			});
			frame.open();
		});
	});
	wkmpAdminAjax(document).ready(function($){
		// Add Color Picker to all inputs that have 'color-field' class
		$(function() {
			$('#clr1').wpColorPicker();
			$('#clr2').wpColorPicker();
			$('#clr3').wpColorPicker();
			$('#clr4').wpColorPicker();
		});
	});


	/* commission payment and seller payment */
	wkmpAdminAjax('tbody').on("click",".column-pay_action .pay",function(){

		var seller_com_id=wkmpAdminAjax(this).attr('id');

		if(seller_com_id){

			wkmpAdminAjax.ajax({
				type: 'POST',
				url: the_mpadminajax_script.mpajaxurl,
				data: {"action": "marketplace_statndard_payment", "seller_id":seller_com_id,"nonce":the_mpadminajax_script.nonce},
				success: function(data) {
					wkmpAdminAjax('#com-pay-ammount').html(data);
					wkmpAdminAjax('#com-pay-ammount').css('display','block');
					wkmpAdminAjax('<div class="standard-pay-backdrop">&nbsp;</div>').appendTo('body');
				}

			});

		}


	});

	wkmpAdminAjax('#com-pay-ammount').on('click','.standard-pay-close',function(){
		wkmpAdminAjax('#com-pay-ammount').hide();
		wkmpAdminAjax( "div" ).remove( ".standard-pay-backdrop" );
	});

	wkmpAdminAjax('#com-pay-ammount').on('click','#MakePaymentbtn',function(evt){

		var remain_ammount=wkmpAdminAjax('#com-pay-ammount').find('#mp_remain_ammount').val();
		var pay_ammount=wkmpAdminAjax('#com-pay-ammount').find('#mp_paying_ammount').val();
		var seller_acc=wkmpAdminAjax('#com-pay-ammount').find('#mp_paying_acc_id').val();
		pay_ammount = parseInt( pay_ammount );

		if( ( parseInt(remain_ammount) < parseInt( pay_ammount ) ) || ( pay_ammount <= 0 || pay_ammount == '' ) || isNaN( pay_ammount ) ) {
			if( isNaN( pay_ammount ) ) {
				wkmpAdminAjax('#com-pay-ammount').find('#mp_paying_ammount_error').text(the_mpadminajax_script.adajax_tr.aajax19);
			}else{
				wkmpAdminAjax('#com-pay-ammount').find('#mp_paying_ammount_error').text(the_mpadminajax_script.adajax_tr.aajax20);
			}
		} else {

			wkmpAdminAjax.ajax({
				type: 'POST',
				url: the_mpadminajax_script.mpajaxurl,
				data: {
					"action": "marketplace_mp_make_payment",
					"seller_acc":seller_acc,
					"pay":pay_ammount,
					"nonce":the_mpadminajax_script.nonce
				},
				beforeSend : function (){
					wkmpAdminAjax("#MakePaymentbtn").val(the_mpadminajax_script.adajax_tr.aajax21).attr('disabled','true');
				},
				success: function(data) {
					if( data ) {
						if( data.error != undefined ) {
							if( data.error == 1 ){
								wkmpAdminAjax("#MakePaymentbtn").val(the_mpadminajax_script.adajax_tr.aajax27);
								wkmpAdminAjax(".wkmpAdminAjax-modal-footer").prepend("<p class='mp-error'>"+data.msg+"</p>");
								window.setTimeout(function(){
									location.reload();
								}, 2000);
							} else if( data.error == 0 ){
								wkmpAdminAjax("#MakePaymentbtn").val(the_mpadminajax_script.adajax_tr.aajax28);
								wkmpAdminAjax(".wkmpAdminAjax-modal-footer").prepend("<p class='mp-success'>"+data.msg+"</p>");
								window.setTimeout(function(){
									location.reload();
								}, 2000);
							}
						}
					}
				}
			});
		}
	})

	setTimeout(function(){
		wkmpAdminAjax('#wk_payment_success').remove();
	}, 5000);
	/* commission payment and seller payment */
});

wkmpAdminAjax(document).ready(function () {
	wkmpAdminAjax('.admin-order-pay').on('click', function () {
		var order_pay = wkmpAdminAjax(this)
		var id = wkmpAdminAjax(this).data('id')
		var seller_id = wkmpAdminAjax('#seller_id').val()
		if ( id && seller_id ) {
			wkmpAdminAjax.ajax({
				type: 'POST',
				url: the_mpadminajax_script.mpajaxurl,
				data: {
					"action": "mp_order_manual_payment",
					"id": id,
					"seller_id": seller_id,
					"nonce": the_mpadminajax_script.nonce
				},
				beforeSend : function (){
					order_pay.html(the_mpadminajax_script.adajax_tr.aajax21).attr('disabled', 'true')
				},
				success: function (response) {
					if ( response == 'done' ) {
						order_pay.replaceWith('<button class="button button-primary" disabled>'+the_mpadminajax_script.adajax_tr.aajax22+'</button>')
						wkmpAdminAjax( '#notice-wrapper' ).html( '<div  class="notice notice-success is-dismissible"><p>'+the_mpadminajax_script.adajax_tr.aajax23+'</p></div>' )
					} else if ( response == 'Already Paid' ) {
						order_pay.replaceWith('<button class="button button-primary" disabled>'+the_mpadminajax_script.adajax_tr.aajax24+'</button>')
						wkmpAdminAjax( '#notice-wrapper' ).html( '<div  class="notice notice-error is-dismissible"><p>'+the_mpadminajax_script.adajax_tr.aajax25+'</p></div>' )
					}
				},
			})
		}
	})

	// product seller assign in bulk
	if (wkmpAdminAjax('#mp-product-seller-select-list').length) {
		wkmpAdminAjax('#mp-product-seller-select-list').select2();

		wkmpAdminAjax('#mp-assign-product-seller').on('click', function () {
				if (wkmpAdminAjax('#mp-product-seller-select-list').val()) {
					return confirm(wkmpAdminAjax(this).data('alert-msg'));
				}
		});
	}

	if (wkmpAdminAjax('#wkmpAdminAjax_seller_allowed_product_types').length) {
		wkmpAdminAjax('#wkmpAdminAjax_seller_allowed_product_types').select2();
		wkmpAdminAjax('#wkmpAdminAjax_seller_allowed_categories').select2();
	}

	if (wkmpAdminAjax('#wkmpAdminAjax_allowed_categories_per_seller').length) {
		wkmpAdminAjax('#wkmpAdminAjax_allowed_categories_per_seller').select2();
	}

	if (wkmpAdminAjax('#reassign_user').length) {
		wkmpAdminAjax('#reassign_user').select2();
	}
	if (wkmpAdminAjax('.mp-endpoints-text').length) {
		wkmpAdminAjax('.regular-text').on('blur', function (e) {
			var endpointval = wkmpAdminAjax(this).val().trim();
			if (wkmpAdminAjax(this).hasClass('mp-endpoints-text')) {
				endpointval = endpointval.replace(/[^A-Za-z0-9 -]/g,"");
				wkmpAdminAjax(this).val(endpointval.replace(/ /g, "-"));
			} else {
				wkmpAdminAjax(this).val(endpointval)
			}
		});
		
  }
})
