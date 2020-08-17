const wkmpMain = jQuery.noConflict();

wkmpMain(document).ready(function($){

  if ( window.history.replaceState ) {
    window.history.replaceState( null, null, window.location.href );
  }

  wkmpMain( '.wkmp-order-refund-button' ).on( 'click', (e) => {

    wkmpMain( '.wkmp-order-refund' ).toggle();

    if( wkmpMain( '.wkmp-order-refund' ).css('display') == 'table-cell' ) {
      wkmpMain( e.target).text(the_mpajax_script.mkt_tr.fajax16)
    } else {
      wkmpMain( e.target).text(the_mpajax_script.mkt_tr.fajax17)
    }

  } );

  if( wkmpMain( '.refund_line_total' ) ) {

    wkmpMain( '.refund_line_total' ).on( 'change', (e) => {

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

  wkmpMain( '#seller_countries_field' ).on( 'change', function(ert){

    if(wkmpMain( '#wk_store_country' ).val()){
      country_code = wkmpMain( '#wk_store_country' ).val();

      wkmpMain.ajax({
          type: 'POST',
          url: the_mpajax_script.mpajaxurl,
          data: {
            "action": "country_get_state",
            "country_code": country_code,
            "nonce": the_mpajax_script.nonce
          },
          success: function (data) {

            if( data ){
              wkmpMain('#wk_store_state').siblings('span.select2').remove();
              wkmpMain('#wk_store_state').replaceWith(data);
              if ( wkmpMain('#wk_store_state').is( 'select' ) ) {
                wkmpMain('#wk_store_state').select2();
              }
            }
          }
        });
    }
  });
  
  wkmpMain('#wkmp-bulk-delete').on('click', function (ext) {
  	action = wkmpMain(this).data('action');
  	if (action == 'bulk') {
  		ext.preventDefault();
  		chk = wkmpMain('#wkmp-product-list-form').find('input:checkbox:checked');
  		
  		if (chk.length > 0) {
  			let product_arr = [];
  			chk.each(function (p) {
  				product_arr.push(parseInt(wkmpMain(this).val()));
  			})

  			wkmpMain.ajax({
  				type: 'POST',
  				url: the_mpajax_script.mpajaxurl,
  				data: {
  					'action': 'mp_bulk_delete_product',
  					'product_ids': product_arr,
  					'nonce': the_mpajax_script.nonce
  				},
  				beforeSend: function () {
  					wkmpMain('body').append('<div class="wk-mp-loader"><div class="wk-mp-spinner wk-mp-skeleton"><!--////--></div></div>')
  					wkmpMain('.wk-mp-loader').css('display', 'block')
  					wkmpMain('body').css({
  						'overflow': 'hidden',
  						'position': 'relative'
  					})
  				},
  				complete: function () {
  					setTimeout(function () {
  						wkmpMain('body').css('overflow', 'auto')
  						wkmpMain('.wk-mp-loader').remove()
  					}, 1500)
  				},
  				success: function (response) {
  				  window.location.reload();
  				}
  			});

  		} else {
  			alert('Selct Some Product(s) to delete')
  		}
  	}
  });

  wkmpMain( '.woocommerce-MyAccount-navigation-link--seperate-dashboard a' ).on( 'click', function( eve ){
    eve.preventDefault();
    wkmpMain.ajax({
      type: 'POST',
      url: the_mpajax_script.mpajaxurl,
      data: {
        "action":"change_seller_dashboard",
        "change_to":'backend_dashboard',
        "nonce":the_mpajax_script.nonce
      },
      success: function (data) {
        data = wkmpMain.parseJSON(data);
        if (data) {
          window.location.href = data.redirect;
        }
      }

    })
  } );

    wkmpMain( "#notify-customer .close, #notify-customer #wk-cancel-mail" ).on("click", function(){
      wkmpMain("#notify-customer").fadeOut("slow");
    });

    wkmpMain( "#save_account_details" ).on("click", function(e){
      e.preventDefault();
      var current_pass  = wkmpMain( "#password_current" );
      var new_pass      = wkmpMain( "#password_1" );
      var confirm_pass  = wkmpMain( "#password_2" );

      if( ! current_pass.val() )
        current_pass.focus();

      else if( ! new_pass.val() )
        new_pass.focus();

      else if( ! confirm_pass.val() )
        confirm_pass.focus();

      else
        wkmpMain("#mp-seller-change-password").submit();

    });

    // Popup to send email to customers

    wkmpMain(".mail-to-follower button").on("click",function(){

      customer_checked=[];

      customer_checked=wkmpMain(".shop-fol tbody tr").find(".icheckbox_square-blue input:checkbox:checked").map(function(){
        return wkmpMain(this).val();
       }).get();
      if(customer_checked.length > 0){
          wkmpMain("#notify-customer").fadeIn("slow");
      }
      else{
        alert( the_mpajax_script.mkt_tr.mkt1 );
      }

    });


    wkmpMain(document).on("click",".favourite-seller .icheckbox_square-blue .mass-action-checkbox",function(){

       wkmpMain(this).parent().toggleClass('checked');

       if(wkmpMain(this).parent().parent().hasClass('select-all-box')){
        if(wkmpMain(this).parent().hasClass('checked')){
          wkmpMain(".favourite-seller").find(".mass-action-checkbox").prop('checked', this.checked);
          wkmpMain(".favourite-seller").find(".mass-action-checkbox").parent().addClass('checked');
        }
        else{
          wkmpMain(".favourite-seller").find(".mass-action-checkbox").prop('checked', false);
          wkmpMain(".favourite-seller").find(".mass-action-checkbox").parent().removeClass('checked');
        }
      }

       if(wkmpMain('.shop-fol tbody input:checkbox:checked').length==wkmpMain('.shop-fol tbody input:checkbox').length){
          wkmpMain(".select-all-box .icheckbox_square-blue .mass-action-checkbox").prop('checked', this.checked);
          wkmpMain(".select-all-box .icheckbox_square-blue .mass-action-checkbox").parent().addClass('checked');
       }
       else{

        wkmpMain(".select-all-box .icheckbox_square-blue .mass-action-checkbox").prop('checked',false);
        wkmpMain(".select-all-box .icheckbox_square-blue .mass-action-checkbox").parent().removeClass('checked');
       }

    });

   wkmpMain('.mp-role-selector li').on('click', function() {
        var currentElm=wkmpMain(this);
        var currentElmRadio=wkmpMain(this).find('input:radio');
        currentElm.addClass("active").siblings().removeClass('active');
        if ( currentElm.data("target")==1) {
            wkmpMain('.show_if_seller').slideDown();
            wkmpMain(".show_if_seller").find(":input").removeAttr("disabled");
            if ( wkmpMain( '.tc_check_box' ).length > 0 )
                wkmpMain('input[name=register]').attr('disabled','disabled');
        } else {
            wkmpMain(".show_if_seller").find(":input").attr("disabled","disabled");
            wkmpMain('.show_if_seller').slideUp();
            if ( wkmpMain( '.tc_check_box' ).length > 0 )
                wkmpMain( 'input[name=register]' ).removeAttr( 'disabled' );
        }
    });

});

 // Place selected thumbnail ID into custom field to save as featured image
 wkmpMain(document).on('click', '#thumbs img', function() {

    wkmpMain('#thumbs img').removeClass('chosen');

    var thumb_ID = wkmpMain(this).attr('id').substring(3);

    wkmpMain('#wpuf_featured_img').val(thumb_ID);

    wkmpMain(this).addClass('chosen');
});

wkmpMain(document).ready(function(){

wkmpMain(document).on('mouseover','.help-tip',function(){
	wkmpMain(this).prev('div').css('display','block');
});
wkmpMain(document).on('mouseout','.help-tip',function(){
	wkmpMain(this).prev('div').css('display','none');
});


//banner trigger file upload
   wkmpMain('#wkmp_seller_banner').click(function(){
    wkmpMain('#wk_mp_shop_banner').trigger('click');
  });


  wkmpMain('#id_attribute_downloads_files').click(function(){
    wkmpMain('#attribute_downloads_files').trigger('click');
  });
// banner trigger file  upload end


//banner on mouse over effect
// wkmpMain('.wkmp_shop_banner').on('mouseover',function(){
//   wkmpMain('.wkmp-fade-banner').css('display','block');
// });
// wkmpMain('.wkmp_shop_banner').on('mouseout',function(){
//   wkmpMain('.wkmp-fade-banner').css('display','none');
// });
//banner on mouse over effect end

wkmpMain('#seller_sub_login').submit(function(){
var selleremail=wkmpMain('#username').val();
var sellerpass=wkmpMain('#password').val();
  if(selleremail.length<=0)
  {
    wkmpMain('#sellerusername_error').html(the_mpajax_script.mkt_tr.mkt39);
    return false;
  }
  else
  {
    wkmpMain('#sellerusername_error').html('');
  }
  if(sellerpass.length<=0)
  {
    wkmpMain('#sellerpassword_error').html(the_mpajax_script.mkt_tr.mkt38);
    return false;
  }
  else
  {
    wkmpMain('#sellerpassword_error').html('');
  }
});
// slider
wkmpMain(document).ready(function(){
  var size=wkmpMain('.view-port-mp-slider-absolute').find('img').length;
  var pos=1;

  if(size>0){
    new_size=size*200;
    wkmpMain('.view-port-mp-slider-absolute').css('width',new_size);
    // var old_width=parseInt(wkmpMain('.view-port-mp-slider').css('width'));
  }
  wkmpMain('.wkmp-bx-next-slider').on('click',function(){
    if(pos>=1 && pos<(size-2)){
      pos++;
      wkmpMain('.view-port-mp-slider-absolute').animate({
        left:'-=200px',
      },'slow');
    }
  });
  wkmpMain('.wkmp-bx-prev-slider').on('click',function(){

    if(pos>1 && pos<=(size-2)){
      pos--;
      wkmpMain('.view-port-mp-slider-absolute').animate({
        left:'+=200px',
      },'slow');
    }
  });
});
//  getting parameters value from url
  var checkuser=1;
    wkmpMain.extend({
      getUrlVars: function(){
      var vars = [], hash;
      var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
      for(var i = 0; i < hashes.length; i++)
      {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
      }
      return vars;
      },
      getUrlVar: function(name){
      return wkmpMain.getUrlVars()[name];
      }
    });

    var page_id = wkmpMain.getUrlVar('page_id');
    var page = wkmpMain.getUrlVar('page');

    if(page=='To'){
    wkmpMain('<div class="wkmp-modal-backdrop">&nbsp;</div>').appendTo('body');
    }

//product validation
    wkmpMain('#add_product_sub').click(function(e) {
      if(wkmpMain(this).attr('type')=='submit'){
        var product_name=wkmpMain('#product_name').val();
        product_name=trim_wkmp_value(product_name);
        var product_sku=wkmpMain('#product_sku').val();
        var regu_price=wkmpMain('#regu_price').val();
        // var sale_price=wkmpMain('#sale_price').val();
        // var long_desc = tinyMCE.get('product_desc').getContent();
        // var short_desc = tinyMCE.get('short_desc').getContent();
        var ck_name = /^[A-Za-z0-9 _-]{1,40}$/;
        var price=/^\d+(\.\d{1,2})?$/;
        var error = 0;
        if(product_name.length==0)
        {
          wkmpMain('#pro_name_error').html(the_mpajax_script.mkt_tr.mkt2);
          error++;
        }
        // if(long_desc.length<10)
        // {
        // wkmpMain('#long_desc_error').html('About product field can not be left blank');
        // return false;
        // }
        // else{
        // wkmpMain('#long_desc_error').html('');
        // }
        if( typeof(product_sku) != 'undefined' && product_sku.length<3)
        {
        	wkmpMain('#pro_sku_error').css('color','red');
       		wkmpMain('#pro_sku_error').html(the_mpajax_script.mkt_tr.mkt3);
			    error++;
        }

        if( !product_type ){
          pro_type = wkmpMain('#product-form').find('input[name="product_type"]').val()
        }
        if(wkmpMain('#product_type').val()!='variable' &&pro_type!='variable' && pro_type!='grouped'){
          if(!wkmpMain.isNumeric(regu_price))
          {
            wkmpMain('#regl_pr_error').html(the_mpajax_script.mkt_tr.mkt6);
            error++;
          }
          else{
            wkmpMain('#regl_pr_error').html('');
          }
        }
        var sale_price=wkmpMain('#sale_price').val();
        // var price=/^\d+(\.\d{1,2})?$/;
        var regular=parseFloat(wkmpMain('#regu_price').val());
        var sale=parseFloat(wkmpMain('#sale_price').val());
        if( wkmpMain('#sale_price').val() ){
         if(!wkmpMain.isNumeric(sale_price))
          {
            wkmpMain('#sale_pr_error').html(the_mpajax_script.mkt_tr.mkt6);
            error++;
          }else if(sale>regular){
            wkmpMain('#sale_pr_error').html(the_mpajax_script.mkt_tr.mkt5);
            error++;
          }else
          {
            wkmpMain('#sale_pr_error').html('');
          }
        }
        var product_sku=wkmpMain('#product_sku').val();
        // product_sku_validation(product_sku);
        // if(short_desc.length<10)
        // {
        // wkmpMain('#short_desc_error').html('About product field can not be left blank');
        // return false;
        // }
        // else{
        // wkmpMain('#short_desc_error').html('');
        // } ==product-form==
        wkmpMain('.wkmp_variable_sku').each(function(){
          var wkmp_variable_sku=wkmpMain(this).val();
          var this_sel=this;
          // variation_sku_validation(wkmp_variable_sku,this_sel)
        });

        if(error)
          return false;
        // return false;
      }
      });
  function trim_wkmp_value (item) {
    item=wkmpMain.trim(item);
    return item;
  }
//sku validation
      var ps=wkmpMain('#product_sku').val();
      wkmpMain('#product_sku').blur(function(){
        var product_sku=wkmpMain('#product_sku').val();
        wkmpMain('#pro_sku_error').html('');
        if(product_sku!=ps)
          product_sku_validation(product_sku);
      });
      function product_sku_validation (argument) {
        var product_sku=argument;
        var reg_sku=/^[a-z0-9A-Z]{1,20}$/;
        wkmpMain('#pro_sku_error').css('color','red');
        if (product_sku=='') {
          wkmpMain('#pro_sku_error').html(the_mpajax_script.mkt_tr.mkt4);
            return false;
        }else if(!reg_sku.test(product_sku))
        {
        	// wkmpMain('#pro_sku_error').html('special character and space are not allowed');
          //   return false;
        }else if( typeof( product_sku ) != 'undefined' &&  product_sku.length<3)
        {
        	wkmpMain('#pro_sku_error').css('color','red');
        	wkmpMain('#pro_sku_error').html(the_mpajax_script.mkt_tr.mkt3);
        	return false;
        }
        else
        {
        	wkmpMain('#pro_sku_error').html('');
        }
        wkmpMain.ajax({
            type: 'POST',
            url: the_mpajax_script.mpajaxurl,
            dataType: "json",
            data: {
              "action": "product_sku_validation",
              "psku": product_sku,
              "nonce": the_mpajax_script.nonce
            },
            success: function (data) {
              if (data && data.success === true) {
                wkmpMain('#pro_sku_error').css('color', 'green');
                wkmpMain('#pro_sku_error').html(data.message);
                wkmpMain('#add_product_sub').removeAttr('disabled');
              } else {
                wkmpMain('#pro_sku_error').css('color','red');
                wkmpMain('#pro_sku_error').html(data.message);
                wkmpMain('#add_product_sub').attr('disabled', 'disabled');
              }
            }
          });
      }
// variation sku validation
      wkmpMain(document).on('blur','.wkmp_variable_sku',function(){
        var wkmp_variable_sku=wkmpMain(this).val();
        var this_sel=this;
        wkmpMain(this).siblings('.wk_variable_sku_err').html('');
        if(wkmpMain(this).val()!=wkmpMain(this).attr('placeholder'))
          variation_sku_validation(wkmp_variable_sku,this_sel);
      });
      function variation_sku_validation (argument1,argument2){
        var wkmp_variable_sku=argument1;
        var reg_sku=/^[a-z0-9A-Z]{1,20}$/;
        var this_sel=argument2;
        wkmpMain(this_sel).siblings('.wk_variable_sku_err').css('color','red');
        if (wkmp_variable_sku=='') {
          wkmpMain(this_sel).siblings('.wk_variable_sku_err').html(the_mpajax_script.mkt_tr.mkt4);
            return false;
        }else if(!reg_sku.test(wkmp_variable_sku))
          {
            // wkmpMain(this_sel).siblings('.wk_variable_sku_err').html('special character and space are not allowed');
            // return false;
          }
          else
          {
            wkmpMain(this_sel).siblings('.wk_variable_sku_err').html('');
          }
          wkmpMain.ajax({
            type: 'POST',
            url: the_mpajax_script.mpajaxurl,
            dataType: "json",
            data: {"action": "product_sku_validation", "psku":wkmp_variable_sku,"nonce":the_mpajax_script.nonce},
            success: function(data){
              if (data && data.success === true) {
                wkmpMain(this_sel).siblings('.wk_variable_sku_err').css('color','green');
                wkmpMain(this_sel).siblings('.wk_variable_sku_err').html(data.message);
              }else{
                wkmpMain(this_sel).siblings('.wk_variable_sku_err').css('color','red');
                wkmpMain(this_sel).siblings('.wk_variable_sku_err').html(data.message);
                return false;
              }
            }
          });
      }
// variation regular price validation
      wkmpMain(document).on('blur','.wc_input_price',function(){
        var no=wkmpMain(this).val();
        wkmpMain(this).next('.error-class').remove()
        if (no && !wkmpMain.isNumeric(no)) {
          wkmpMain(this).after('<span class="error-class">' + the_mpajax_script.mkt_tr.mkt6 + '</span>')
        }
      });

// variation weight price validation
      wkmpMain(document).on('blur','.wc_input_decimal, #wk-mp-stock-qty',function(){
        var no = wkmpMain(this).val();
        wkmpMain(this).parent('.wrap').children('.error-class').remove()
        if (no && !wkmpMain.isNumeric(no)) {
          wkmpMain(this).parent('.wrap').append('<span class="error-class">' + the_mpajax_script.mkt_tr.mkt7 + '</span>')
        }
      });

      // stock
      wkmpMain(document).on('blur', '._weight_field .wc_input_decimal, #wk-mp-stock-qty', function() {
        var no = wkmpMain(this).val();
        wkmpMain(this).next('.error-class').remove()
        if (no && !wkmpMain.isNumeric(no)) {
          wkmpMain(this).after('<span class="error-class">' + the_mpajax_script.mkt_tr.mkt7 + '</span>')
        }
      });
// variation weight price validation
      wkmpMain(document).on('keyup','.wkmp_variable_stock',function(){
        var no=wkmpMain(this).val();
        var no_int=no;
        var stock=/^\d+(\.\d{1,2})?$/;
        a=no_int;
        if(no==no_int)
          a=no_int;
        if(wkmpMain(this).val()!='' && stock.test(a)){
          wkmpMain(this).val(a);
        }else{
          wkmpMain(this).val('');
          a=0;
        }
      });
//product name valdiation
      wkmpMain('#product_name').blur(function(){
        var product_name=wkmpMain('#product_name').val();
        var ck_name = /^[A-Za-z0-9 _-]{1,40}$/;
        if(product_name==''){
			wkmpMain('#pro_name_error').html(the_mpajax_script.mkt_tr.mkt8);
			return false;
        }
        else
        {
          wkmpMain('#pro_name_error').html('');
        }
      });
//product regular price validation
      wkmpMain('#regu_price').blur(function(){
          var regu_price=wkmpMain('#regu_price').val();
          var price=/^\d+(\.\d{1,2})?$/;
          var pro_type = wkmpMain('#product_type');
          if( !product_type ){
            pro_type = wkmpMain('#product-form').find('input[name="product_type"]').val()
          }
          if(!wkmpMain.isNumeric( regu_price )&&pro_type!='variable' && pro_type!='grouped')
          {
          wkmpMain('#regl_pr_error').html(the_mpajax_script.mkt_tr.mkt6);
          return false;
          }
          else
          {
          wkmpMain('#regl_pr_error').html('');
          }
      });

//product sale price validation
      wkmpMain('#sale_price').blur(function(){
        var sale_price=wkmpMain('#sale_price').val();
        var price=/^\d+(\.\d{1,2})?$/;
        var regular=parseFloat(wkmpMain('#regu_price').val());
        var sale=parseFloat(wkmpMain('#sale_price').val());
        var pro_type = wkmpMain('#product_type');
        if( !product_type ){
          pro_type = wkmpMain('#product-form').find('input[name="product_type"]').val()
        }
        if(wkmpMain('#sale_price').val()!='' && pro_type!='variable' && pro_type!='grouped'){
          if(!wkmpMain.isNumeric(sale_price))
          {
            wkmpMain('#sale_pr_error').html(the_mpajax_script.mkt_tr.mkt6);
          return false;
        }else if(sale>=regular){
            wkmpMain('#sale_pr_error').html(the_mpajax_script.mkt_tr.mkt5);
            return false;
          }else
          {
            wkmpMain('#sale_pr_error').html('');
          }
        }
      });
      wkmpMain(document).on('blur', '.wkmp_variable_sale_price', function(){

        var sale_price=wkmpMain(this).val();
        var price=/^\d+(\.\d{1,2})?$/;
        var regular=parseFloat(wkmpMain(this).parent().siblings().children('.wkmp_variable_regular_price').val());
        var sale=parseFloat(wkmpMain(this).val());
        if(wkmpMain(this).val()!=''){
          if(!wkmpMain.isNumeric(sale_price))
          {
            wkmpMain(this).siblings('.sale_pr_error').html(the_mpajax_script.mkt_tr.mkt6);
          return false;
        }else if(sale>=regular){
            wkmpMain(this).siblings('.sale_pr_error').html(the_mpajax_script.mkt_tr.mkt5);
            return false;
          }else
          {
            wkmpMain('#sale_pr_error').html('');
          }
        }
      });
// product validtion end

if ( wkmpMain('#wk_store_country').length ) {
  wkmpMain('#wk_store_country').select2();
  if ( wkmpMain('#wk_store_state').is( 'select' ) ) {
    wkmpMain('#wk_store_state').select2();
  }
}

// profile update validation
wkmpMain('#update_profile_submit').click(function () {
  var error = 0
  var user_name = /^[A-Za-z0-9_-]{1,40}$/
  var first_name = wkmpMain('#wk_firstname').val()
  var last_name = wkmpMain('#wk_lastname').val()
  var shop_name = wkmpMain('#wk_storename').val()
  var shopTest = /^[A-Za-z0-9_-\s]{1,40}$/
  var phoneTest = /^[0-9]{1,10}$/
  var email_reg = /^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,4})$/
  var user_email = wkmpMain('#wk_useremail').val()
  var shopPhone = wkmpMain('#wk_storephone').val()

  if ( first_name && !user_name.test(first_name)) {
    wkmpMain('#first_name_error').html(the_mpajax_script.mkt_tr.mkt9).siblings('input').focus()
    error = 1
  } else {
    wkmpMain('#first_name_error').html('')
  }

  if( last_name && !user_name.test(last_name)) {
    wkmpMain('#last_name_error').html(the_mpajax_script.mkt_tr.mkt10).siblings('input').focus()
    error = 1
  } else {
    wkmpMain('#last_name_error').html('')
  }

  if (!user_email) {
    wkmpMain('input:text[name=user_email]').focus().siblings('.error-class').html(the_mpajax_script.mkt_tr.mkt36)
    error = 1
  } else if( user_email && !email_reg.test(user_email)) {
    wkmpMain('#email_reg_error').html(the_mpajax_script.mkt_tr.mkt11).siblings('input').focus()
    error = 1
  } else {
    wkmpMain('#email_reg_error').html('')
  }

  if (!shop_name) {
    wkmpMain('input:text[name=wk_storename]').focus().siblings('.error-class').html(the_mpajax_script.mkt_tr.mkt36)
    error = 1
  } else if ( shop_name && !shopTest.test(shop_name)) {
    wkmpMain('#seller_storename').html(the_mpajax_script.mkt_tr.mkt12).siblings('input').focus()
    error = 1
  } else {
    wkmpMain('#seller_storename').html('')
  }

  if (shopPhone) {
    if (shopPhone.length > 10) {
      wkmpMain('#seller_storephone').html(the_mpajax_script.mkt_tr.mkt13).siblings('input').focus()
      error = 1
    } else if(!phoneTest.test(shopPhone)) {
      wkmpMain('#seller_storephone').html(the_mpajax_script.mkt_tr.mkt14).siblings('input').focus()
      error = 1
    }
  } else {
    wkmpMain('#seller_storephone').html('')
  }

  if (error) {
    return false;
  }

  wkmpMain('#user_profile_form').submit();
})

//name validation alert on out focus
wkmpMain('#wk_firstname').blur(function(){
  var firstn=wkmpMain('#wk_firstname').val();
  // var user_name = /^[A-Za-z0-9_-]{1,40}$/;
  if(firstn==''){
  	wkmpMain('#seller_first_name').html(the_mpajax_script.mkt_tr.mkt15);
      checkuser=0;
  }else{
      wkmpMain('#seller_first_name').html('');
    }
  });

//last name validation

wkmpMain('#wk_lastname').blur(function(){
        var lastn=wkmpMain('#wk_lastname').val();
        if(lastn==''){
        	wkmpMain('#seller_last_name').html(the_mpajax_script.mkt_tr.mkt15);
            checkuser=0;
        }else{
            wkmpMain('#seller_last_name').html('');
          }
      });

//existing user validation
      wkmpMain('#wk_username').blur(function(){
        var seller_login=wkmpMain('#wk_username').val();
        var a=0;
        if(seller_login==''){
        	wkmpMain('#seller_user_name').html(the_mpajax_script.mkt_tr.mkt16);
        }else{
          wkmpMain('#seller_user_name').html('');
        }
        wkmpMain.ajax({
          type: 'POST',
          url: the_mpajax_script.mpajaxurl,
          data: {"action": "existing_user", "exist_user":seller_login,"nonce":the_mpajax_script.nonce},
          success: function(data){
          if(data==1 && a==0)
          {
          	if(seller_login!=''){
          		wkmpMain('#seller_user_name').html('<span style="color:green;">' + the_mpajax_script.mkt_tr.mkt17 + '</span>');
        		checkuser=1;
          	}
          }
          else if(a==0)
          {
	          wkmpMain('#seller_user_name').html(the_mpajax_script.mkt_tr.mkt18);
	          checkuser=0;
          }
          }
        });
      });

      wkmpMain('#org-name').on('focusout', function() {
          var value = wkmpMain(this).val().toLowerCase().replace(/-+/g, '').replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
          wkmpMain('#seller-shop').val(value);
      });

//validation for email ids
	wkmpMain('#wk_useremail').blur(function()
	{
		var seller_email=wkmpMain('#wk_useremail').val();
        var email_reg= /^[A-Z0-9._%+-]+@[A-Z0-9.-]+.[A-Z]{2,4}$/igm;
        if(seller_email == ''){
        	wkmpMain('#seller_email').html(the_mpajax_script.mkt_tr.mkt19);
        }else if(!email_reg.test(seller_email)) {
          wkmpMain('#seller_email').html(the_mpajax_script.mkt_tr.mkt21);
          checkuser=0;
        }else
        {
			wkmpMain('#seller_email').html('');
        }
        wkmpMain.ajax({
			type: 'POST',
			url: the_mpajax_script.mpajaxurl,
			data: {"action": "seller_email_availability", "seller_email":seller_email,"nonce":the_mpajax_script.nonce},
			success: function(data){
            //wkmpMain('#seller_email').html(data);
			if(data==1)
			{
				wkmpMain('#seller_email').html(the_mpajax_script.mkt_tr.mkt20);
				checkuser=0;
			}
        }
        });
      });
//registration validation
	wkmpMain('#registration_form').submit(function()
	{
		var user_name=/^[A-Za-z0-9_-]{1,40}$/;
		var shop_name=/^[A-Za-z0-9_-]{1,40}$/;
		var login_name = /^[a-zA-Z](([\._\-][a-zA-Z0-9])|[a-zA-Z0-9])*[a-z0-9]$/;
		var email_reg=/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,4})$/;
		var seller_login=wkmpMain('#wk_username').val();
		var first_name=wkmpMain('#wk_firstname').val();
		var last_name=wkmpMain('#wk_lastname').val();
		var email=wkmpMain('#wk_useremail').val();
		var seller=wkmpMain("input[name='role']:checked").val();
    if (wkmpMain('#seller_user_name').text()=='user name already taken') {
      wkmpMain('#wk_username').trigger('blur');
      return false;
    }
			wkmpMain('#seller_user_name').html('');

		if (wkmpMain('#seller_email').html()!='') {
			wkmpMain('#wk_useremail').trigger('blur');
			return false;
		}


          wkmpMain('#seller_first_name').html('');


       	  wkmpMain('#seller_last_name').html('');

          if(!email_reg.test(email))
          {
          wkmpMain('#seller_email').html(the_mpajax_script.mkt_tr.mkt21);
          return false;
          }
          else
          {
          wkmpMain('#seller_email').html('');
          }
          if(seller!=1 && seller!=0)
          {
          wkmpMain('#select-seller_access').html(the_mpajax_script.mkt_tr.mkt22);
          return false;
          }else
          {
          	wkmpMain('#select-seller_access').html('');
            if(seller==1)
            {
            var store=wkmpMain('#wk_storename').val();
            var user_address=wkmpMain('#wk_user_address').val();
              if(store.length<1)
              {
              wkmpMain('#seller_storename').html(the_mpajax_script.mkt_tr.mkt23);
              return false;
              }
              else{
              wkmpMain('#seller_storename').html('');
              }
              if(user_address.length<1)
              {
              wkmpMain('#seller_user_address').html(the_mpajax_script.mkt_tr.mkt24);
              return false;
              }
              else{
              wkmpMain('#seller_user_address').html('');
              }
            }
          wkmpMain('#select-seller_access').html('');
          }
	});

// ask to admin validation

wkmpMain('#resetbtn').on('click',function () {
  wkmpMain('#askesub_error').html('')
  wkmpMain('#askquest_error').html('')
})

// Ask to admin form

wkmpMain('#askToAdminBtn').on('click', function (event) {

  var subReg = /^[A-Za-z0-9 ]{1,100}$/
  var usersub = wkmpMain('#query_user_sub').val()
  var userQuery = wkmpMain('#userquery').val()
  var checkuser = 0
  usersub = wkmpMain.trim(usersub)
  userQuery = wkmpMain.trim(userQuery)
  wkmpMain('#askquest_error').html('')
  wkmpMain('#askesub_error').html('')

  if (!usersub) {
    wkmpMain('#askesub_error').html(the_mpajax_script.mkt_tr.mkt25)
    checkuser = 1
  } else if (!subReg.test(usersub)) {
    wkmpMain('#askesub_error').html(the_mpajax_script.mkt_tr.mkt26)
    checkuser = 1
  }

  if( userQuery.length > 500) {
    wkmpMain('#askquest_error').html(the_mpajax_script.mkt_tr.mkt27)
    checkuser = 1
  }

  if (checkuser) {
    event.preventDefault()
    return false
  }

})

//ask to admin end

//add product status

wkmpMain('.mp-toggle-sider-edit,.mp-toggle-save').on('click',function(){
  var status=wkmpMain('#product_post_status').val();
  if(status=='publish')
  {
    wkmpMain('.mp-toggle-selected-display').html(the_mpajax_script.mkt_tr.mkt28);
  }
  else
  {
    wkmpMain('.mp-toggle-selected-display').html(status);
  }

  wkmpMain('.wkmp-toggle-select-container').toggle()
  });
  wkmpMain('a.mp-toggle-cancel').on('click',function(){
  wkmpMain('.wkmp-toggle-select-container').hide();
});

//product type sidebar
  var product_type=wkmpMain('#product_type').val();

  var var_type=wkmpMain('#var_variation_display').val();
  if(product_type=='variable' && var_type=='yes')
  {
    wkmpMain( "#edit_product_tab li" ).eq(6).show();
  }
  if(product_type=='external')
  {
    wkmpMain( "#edit_product_tab li" ).eq(5).show();
  }




wkmpMain(document).on('change','body #product_type', function() {
  var product_type=wkmpMain('#product_type').val();
  var var_type=wkmpMain('#var_variation_display').val();
  if(product_type=='variable' && var_type=='yes')
    {
      wkmpMain( "#edit_product_tab li" ).eq(6).show();
    }
    else
    {
      	wkmpMain( "#edit_product_tab li" ).eq(6).hide();
    }

    if(product_type=='variable'){
        wkmpMain('#regu_price').attr("disabled", true);
        wkmpMain('#sale_price').attr("disabled", true);
    }else{
        wkmpMain('#regu_price').attr("disabled", false);
        wkmpMain('#sale_price').attr("disabled", false);
    }

    if(product_type=='grouped') {
        wkmpMain('#regu_price').attr("disabled", true);
        wkmpMain('#sale_price').attr("disabled", true);
    }else{
        wkmpMain('#regu_price').attr("disabled", false);
        wkmpMain('#sale_price').attr("disabled", false);
    }

    if(product_type=='external')
    {
      wkmpMain( "#edit_product_tab li" ).eq(5).show();
    }

    else {
      wkmpMain( "#edit_product_tab li" ).eq(5).hide();
    }
});
  wkmpMain('a.mp-toggle-type-cancel').on('click',function(){
  wkmpMain('.mp-toggle-select-type-container').css('display','none');
});
wkmpMain('.mp_value_asc').change(function(){
var str=wkmpMain(this).val();
var newUrl=window.location.href+'&'+str;
window.location=newUrl;
});
//downloadable check
wkmpMain('#_ckdownloadable').change(function(){
wkmpMain('.wk-mp-side-body').slideToggle( "slow");
});

wkmpMain('#_ckvirtual').change(function(){
  if(wkmpMain( "#edit_product_tab li" ).eq(2).css( 'display')!='none'){
    wkmpMain( "#edit_product_tab li" ).eq(2).css( 'display', 'none');
  }else{
    wkmpMain( "#edit_product_tab li" ).eq(2).css( 'display', 'block');
  }
  });

/***********Seller multiple downloadable files starts***********/

wkmpMain( '.wk-mp-side-body' ).on( 'click','.downloadable_files a.insert', function() {
  wkmpMain( this ).closest( '.downloadable_files' ).find( 'tbody' ).append( wkmpMain( this ).data( 'row' ) );
  return false;
});

wkmpMain( '.wk-mp-side-body' ).on( 'click','.downloadable_files a.delete',function() {
  wkmpMain( this ).closest( 'tr' ).remove();
  return false;
});

/***********Seller multiple downloadable files ends***********/

wkmpMain(document).on('change','.checkbox_is_virtual',function(){
	wkmpMain(this).parents('tbody').children('tr').eq(0).find('.virtual').slideToggle('fast');
});
wkmpMain(document).on('change','.checkbox_is_downloadable',function(){
	wkmpMain(this).parents('tbody').children('tr').eq(0).find('.downloadable').slideToggle('fast');
});
wkmpMain(document).on('change','.checkbox_manage_stock',function(){
	wkmpMain(this).parents('tbody').children('tr').eq(0).find('.wkmp_stock_status').slideToggle('fast');
});


// upload file name handler

//upload button for product image file
wkmpMain('.add-mp-product-images').on('click', function(event) {
    var file_frame;
    var image_id=wkmpMain(this).attr('id');
    var image_id_field = wkmpMain('#product_image_Galary_ids').val();
    var galary_ids = '';
    var typeError = 0;

    wkmpMain('#wk-mp-product-images').find('.error-class').remove();

    if (image_id_field == '') {
        galary_ids = '';
    } else {
        galary_ids = image_id_field + ',';
    }

    event.preventDefault();
    // If the media frame already exists, reopen it.
    if ( file_frame ) {
        file_frame.open();
        return;
    }

    // Create the media frame.
    file_frame = wp.media.frames.file_frame = wp.media({
        title: wkmpMain( this ).data( 'uploader_title' ),
        button: { text: wkmpMain( this ).data( 'uploader_button_text' ) },
        multiple: true  // Set to true to allow multiple files to be selected
    });

   // When frame is open, select existing image attachments from custom field
   file_frame.on( 'open', function() {
      var selection = file_frame.state().get('selection');
   });

   var query = wp.media.query();

   query.filterWithIds = function(ids) {
      return _(this.models.filter(function(c) { return _.contains(ids, c.id); }));
   };

   // When images are selected, place IDs in hidden custom field and show thumbnails.
   file_frame.on( 'select', function() {
      var selection = file_frame.state().get('selection');
      // Place IDs in custom field
      var attachment_ids = selection.map( function( attachment ) {
          attachment = attachment.toJSON();

          if (attachment.sizes != undefined) {
              galary_ids = galary_ids+attachment.id+',';
              wkmpMain('#handleFileSelectgalaray').append("<img src='"+attachment.sizes.thumbnail.url+"' width='50' height='50'/>");
              return attachment.id;
          } else {
              typeError = 1;
          }
      });

      if (typeError) {
          wkmpMain('#wk-mp-product-images').append("<p class=error-class>"+ wkmpMain(".mp_product_thumb_image.button").data('type-error') +"</p>");
      }

      galary_ids = galary_ids.replace(/,\s*$/, "");
      wkmpMain('#product_image_Galary_ids').val(galary_ids);

   });

   // Finally, open the modal
   file_frame.open();
});

/* mp thumb image */
wkmpMain('.mp_product_thumb_image').on('click', function (event) {
    var file_frame;

    event.preventDefault();

    if ( file_frame ) {
        file_frame.open();
        return;
    }

    // Create the media frame.
    file_frame = wp.media.frames.file_frame = wp.media({
        title: wkmpMain( this ).data( 'uploader_title' ),
        button: { text: wkmpMain( this ).data( 'uploader_button_text' ) },
        multiple: false  // Set to true to allow multiple files to be selected
    });

   // When frame is open, select existing image attachments from custom field
   file_frame.on( 'open', function() {
      var selection = file_frame.state().get('selection');
   });

   var query = wp.media.query();

   query.filterWithIds = function(ids) {
      return _(this.models.filter(function(c) { return _.contains(ids, c.id); }));
   };

   // When images are selected, place IDs in hidden custom field and show thumbnails.
   file_frame.on( 'select', function() {
      var selection = file_frame.state().get('selection');
      // Place IDs in custom field
      var attachment_ids = selection.map( function( attachment ) {
          attachment = attachment.toJSON();
          wkmpMain(".mp_product_thumb_image.button").siblings('.error-class').remove();

          if (attachment.sizes != undefined) {
              wkmpMain('#product_thumb_image_mp').val(attachment.id);
              wkmpMain('#mp-product-thumb-img-div').html("").html("<img src='"+attachment.sizes.thumbnail.url+"' width='50' height='auto'/>");
              return attachment.id;
          } else {
              wkmpMain(".mp_product_thumb_image.button").parent().append("<p class=error-class>"+ wkmpMain(".mp_product_thumb_image.button").data('type-error') +"</p>");
          }
      });

    });

    // Finally, open the modal
    file_frame.open();
});

/* mp thumb image end */

/* remove thumb image product */
wkmpMain('#mp-product-thumb-img-div .mp-image-remove-icon').on('click', function () {
  wkmpMain('#product_thumb_image_mp').val('')
  wkmpMain('#mp-product-thumb-img-div').remove()
  wkmpMain(this).siblings('img').attr('src', '')
})


  // tabs on edit product page
  wkmpMain('#edit_product_tab li a:not(:first)').addClass('inactive');
  if (! wkmpMain('#edit_notification_tab li a').length) {
    wkmpMain('.wkmp_container').hide();
    wkmpMain('.wkmp_container:first').show();
  }

  var activeproducttab = wkmpMain('#active_product_tab')
  if( activeproducttab.val() ) {
    var activeproducttabvalue = activeproducttab.val()
    if(wkmpMain('#' + activeproducttabvalue ).hasClass('inactive')) {
      wkmpMain('#edit_product_tab li a').addClass('inactive');
      wkmpMain('#' + activeproducttabvalue ).removeClass('inactive');

      wkmpMain('.wkmp_container').hide();
      wkmpMain('#'+ wkmpMain('#' + activeproducttabvalue).attr('id') + 'wk').fadeIn('slow');
    }
  }
  wkmpMain('#edit_product_tab li a').click(function(){
    var t = wkmpMain(this).attr('id');
    activeproducttab.val(t)
    if(wkmpMain(this).hasClass('inactive')){ //this is the start of our condition
      wkmpMain('#edit_product_tab li a').addClass('inactive');
      wkmpMain(this).removeClass('inactive');

      wkmpMain('.wkmp_container').hide();
      wkmpMain('#'+ t + 'wk').fadeIn('slow');
    }
  });
  wkmpMain('#edit_notification_tab li a').click(function(){
    var t = wkmpMain(this).attr('id');
    if(wkmpMain(this).hasClass('inactive')){ //this is the start of our condition
      wkmpMain('#edit_notification_tab li a').addClass('inactive');
      wkmpMain(this).removeClass('inactive');

      wkmpMain('.wkmp_container').hide();
      wkmpMain('#'+ t + 'wk').fadeIn('slow');
    }
  });
  //attribute dynamic fields
    var wrapper         = wkmpMain(".wk_marketplace_attributes"); //Fields wrapper
    var add_button      = wkmpMain(".add-variant-attribute"); //Add button ID
    var attribute_no   = wkmpMain("div.wk_marketplace_attributes > div.wkmp_attributes").length;
    var x = attribute_no;
    wkmpMain(document).on('click','.add-variant-attribute',function(e){ //on add input button click
	   e.preventDefault();
     var type = wkmpMain('#sell_pr_type').val();
     if(type == 'variable'){
    wkmpMain(wrapper).append('<div class="wkmp_attributes"><div class="box-header attribute-remove"><input type="text" class="mp-attributes-name wkmp_product_input" placeholder="' + the_mpajax_script.mkt_tr.mkt29 + '" name="pro_att['+x+'][name]" value=""/><input type="text" class="option wkmp_product_input" title="' + the_mpajax_script.mkt_tr.mkt30 + '" placeholder="' + the_mpajax_script.mkt_tr.mkt31 + '" name="pro_att['+x+'][value]" /><input type="hidden" name="pro_att['+x+'][position]" class="attribute_position" value="1"/><span class="mp_actions"><button class="mp_attribute_remove btn btn-danger">' + the_mpajax_script.mkt_tr.mkt32 + '</button></span></div><div class="box-inside clearfix"><div class="wk-mp-attribute-config"><div class="wkmp-checkbox-inline"><input type="checkbox" class="checkbox" name="pro_att['+x+'][is_visible]" id="is_visible_page'+x+'" value="1"/><label for="is_visible_page'+x+'">' + the_mpajax_script.mkt_tr.mkt33 + '</label></div>  <div class="wkmp-checkbox-inline"><input type="checkbox" class="checkbox" name="pro_att['+x+'][is_variation]" id="product_att_varition_'+x+'" value="1"/><label for="product_att_varition_'+x+'">' + the_mpajax_script.mkt_tr.mkt34 + '</label></div><input type="hidden" name="pro_att['+x+'][is_taxonomy]" value="0"/></div><div class="attribute-options"></div></div></div>');
    }
    else{
    wkmpMain(wrapper).append('<div class="wkmp_attributes"><div class="box-header attribute-remove"><input type="text" class="mp-attributes-name wkmp_product_input" placeholder="'+the_mpajax_script.mkt_tr.mkt29+'" name="pro_att['+x+'][name]" value=""/><input type="text" class="option wkmp_product_input" title="'+the_mpajax_script.mkt_tr.mkt30+'" placeholder="'+the_mpajax_script.mkt_tr.mkt31+'" name="pro_att['+x+'][value]" /><input type="hidden" name="pro_att['+x+'][position]" class="attribute_position" value="1"/><span class="mp_actions"><button class="mp_attribute_remove btn btn-danger">'+the_mpajax_script.mkt_tr.mkt32+'</button></span></div><div class="box-inside clearfix"><div class="wk-mp-attribute-config"><div class="wkmp-checkbox-inline"><input type="checkbox" class="checkbox" name="pro_att['+x+'][is_visible]" id="is_visible_page'+x+'" value="1"/><label for="is_visible_page'+x+'">'+the_mpajax_script.mkt_tr.mkt33+'</label></div><input type="hidden" name="pro_att['+x+'][is_taxonomy]" value="0"/></div><div class="attribute-options"></div></div></div>');
    }
    x++;
    });

    wkmpMain(wrapper).on("click",".mp_attribute_remove", function(e){ //user click on remove text
        e.preventDefault();
    wkmpMain(this).parent().parent().parent().remove();
    })

wkmpMain('.wkmp_variation_downloadable_file').on("click",'.mp_var_del',function(){
  var del_id=wkmpMain(this).attr('id');
wkmpMain('#'+del_id).parent().parent().remove();
});


wkmpMain('#mp_attribute_variations').on("click",".upload_image_button",function(){
var file_type_id=wkmpMain(this).attr('id')+'upload';
wkmpMain('#'+file_type_id).trigger('click');
})

wkmpMain(document).on("click",'#mp_attribute_variations div.wkmp_variation_downloadable_file .wkmp_downloadable_upload_file',function(event){
  event.preventDefault();
  var trigger_id=wkmpMain(this).attr('id');
  // var up_id=trigger_id.split('_');
  // var upload_file_id='downloadable_upload_file_'+up_id[0];
  var text_box_file_url='downloadable_upload_file_url_'+trigger_id;
  var file_frame;
 // If the media frame already exists, reopen it.
if ( file_frame ) {
  file_frame.open();
  return;
}

// Create the media frame.
file_frame = wp.media.frames.file_frame = wp.media({
  title: wkmpMain( this ).data( 'uploader_title' ),
  button: { text: wkmpMain( this ).data( 'uploader_button_text' ) },
  multiple: false  // Set to true to allow multiple files to be selected
});
   // When frame is open, select existing image attachments from custom field
file_frame.on( 'open', function() {
var selection = file_frame.state().get('selection');
//var attachment_ids = wkmpMain('#attachment_ids').val().split(',');
 });
var query = wp.media.query();

query.filterWithIds = function(ids) {
    return _(this.models.filter(function(c) { return _.contains(ids, c.id); }));
};

var res = query.filterWithIds([3]); // change these to your IDs

res.each(function(v){
    console.log( v.toJSON() );
});

  // When images are selected, place IDs in hidden custom field and show thumbnails.
file_frame.on( 'select', function() {

var selection = file_frame.state().get('selection');

// Place IDs in custom field
var attachment_ids = selection.map( function( attachment ) {
  attachment = attachment.toJSON();
  wkmpMain('#'+text_box_file_url).val(attachment.url);
  return attachment.id;
});
});

// Finally, open the modal
file_frame.open();
});

  // variation attribute


  // multiple thumb image upload and view
  function handleFileSelect(evt)
  {
    wkmpMain('#product_image').empty();
    var files = evt.target.files; // FileList object
    // Loop through the FileList and render image files as thumbnails.
    for (var i = 0, f; f = files[i]; i++)
  {
    // Only process image files.
    if (!f.type.match('image.*'))
    {
      continue;
    }
    var reader = new FileReader();
    // Closure to capture the file information.
    reader.onload = (function(theFile){
      return function(e)
      {
        // Render thumbnail.
        var div = document.createElement('div');
        //wkmpMain(div).attr({class:'ingdiv'});
        div.innerHTML = ['<img class="thumb" src="', e.target.result,'" title="', escape(theFile.name), '"/><span class="wkmp_image_over" ></span><input type="hidden" name ="mpthumbimg[]" value="',escape(theFile.name),'">'].join('');
        document.getElementById('product_image').insertBefore(div, null);
        wkmpMain('#product_image div').attr({class:'imgdiv'});
        wk_imgview();
      };
        })(f);
    // Read in the image file as a data URL.
    reader.readAsDataURL(f);
    }
    function wk_imgview()
  {
    wkmpMain('div.imgdiv').mouseover(function(event){
        //alert('Hello div');
       wkmpMain(this).find(".wkmp_image_over").css({display:"block"});
        wkmpMain(this).find("img").css("opacity","0.4");    wkmpMain(this).find(".wkmp_image_over").on('click', function () {
          wkmpMain(this).parent("div").remove();
        });
    });

    wkmpMain("div.imgdiv").mouseout(function(event){
        wkmpMain(this).find(".wkmp_image_over").css({display:"none"});
        wkmpMain(this).find("img").css("opacity","1");
    });
  }
  }

   // multiple galary image upload and view
  function handleFilegalaray(evt)
  {
    wkmpMain('#handleFileSelectgalaray').empty();
    var files = evt.target.files; // FileList object
    // Loop through the FileList and render image files as thumbnails.
    for (var i = 0, f; f = files[i]; i++)
  {
    // Only process image files.
    if (!f.type.match('image.*'))
    {
      continue;
    }
    var reader = new FileReader();
    // Closure to capture the file information.
    reader.onload = (function(theFile){
      return function(e)
      {
        // Render thumbnail.
        var div = document.createElement('div');        div.innerHTML = ['<img class="thumb" src="', e.target.result,'" title="', escape(theFile.name), '"/><span class="wkmp_image_over" ></span><input type="hidden" name ="mpproductgall[]" value="',escape(theFile.name),'">'].join('');
        document.getElementById('handleFileSelectgalaray').insertBefore(div, null);
        wkmpMain('#handleFileSelectgalaray div').attr({class:'imgdiv'});
        wk_imgview();
      };
        })(f);
    // Read in the image file as a data URL.
    reader.readAsDataURL(f);
    }
    function wk_imgview()
  {
    wkmpMain('div.imgdiv').mouseover(function(event){
        //alert('Hello div');
        wkmpMain(this).find(".wkmp_image_over").css({display:"block"});
        wkmpMain(this).find("img").css({"opacity":"0.4"});
  // For Delete the image  Div at Click on Cross Icon
          wkmpMain(this).find(".wkmp_image_over").on('click', function () {
            wkmpMain(this).parent("div").remove();
          });

  });

    wkmpMain("div.imgdiv").mouseout(function(event){
       wkmpMain(this).find(".wkmp_image_over").css({display:"none"});
       wkmpMain(this).find("img").css({"opacity":"1"});
    });

  }
  }



  /* function to change profile image */
  function changeprofile_image(evt)
  {
    wkmpMain('#mp_seller_image').empty();
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
        div.innerHTML = ['<img class="thumb" src="', e.target.result,'" title="', escape(theFile.name), '"/><span class="wkmp_image_over" ></span><input type="hidden" name ="mpthumbimg[]" value="',escape(theFile.name),'">'].join('');
        document.getElementById('mp_seller_image').insertBefore(div, null);
        wkmpMain('#mp_seller_image div').attr({class:'imgdiv'});
      };
        })(f);
    reader.readAsDataURL(f);
    }
  }
  /* change profile image change end */

  /* function to change banner image */
  function changeseller_bannerimage(evt)
  {
    wkmpMain('#wk_seller_banner').empty();

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
        div.innerHTML = ['<img class="thumb" src="', e.target.result,'" title="', escape(theFile.name), '"/><span class="wkmp_image_over" ></span><input type="hidden" name ="mpthumbimg[]" value="',escape(theFile.name),'">'].join('');
        document.getElementById('wk_seller_banner').insertBefore(div, null);
        wkmpMain('#wk_seller_banner div').attr({class:'imgdiv'});
      };
        })(f);
    reader.readAsDataURL(f);
    }
  }
  /* change banner image change end */

  /* remove logo image start */
  wkmpMain('#seller_com_logo_img .mp-image-remove-icon').on('click', function() {
    var thumbId = wkmpMain(this).data('id')
    var defaultSrc = wkmpMain(this).data('default')
    wkmpMain(this).siblings('img').attr('src', defaultSrc)
    wkmpMain(this).next('.mp-remove-company-logo').val(thumbId)
    wkmpMain(this).remove()
  })
  /* remove logo image ends */

  /* remove banner image start */
  wkmpMain('#wk_seller_banner .mp-image-remove-icon').on('click', function() {
    var thumbId = wkmpMain(this).data('id')
    var defaultSrc = wkmpMain(this).data('default')
    wkmpMain(this).siblings('img').attr('src', defaultSrc)
    wkmpMain(this).next('.mp-remove-shop-banner').val(thumbId)
    wkmpMain(this).remove()
  })
  /* remove banner image ends */

  /* remove banner image start */
  wkmpMain('#mp_seller_image .mp-image-remove-icon').on('click', function() {
    var thumbId = wkmpMain(this).data('id')
    var defaultSrc = wkmpMain(this).data('default')
    wkmpMain(this).siblings('img').attr('src', defaultSrc)
    wkmpMain(this).next('.mp-remove-avatar').val(thumbId)
    wkmpMain(this).remove()
  })
  /* remove banner image ends */

  /* seller logo image */
  function seller_logo_image(evt)
  {
    wkmpMain('#seller_com_logo_img').empty();

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
        div.innerHTML = ['<img class="thumb" src="', e.target.result,'" title="', escape(theFile.name), '"/><span class="wkmp_image_over" ></span><input type="hidden" name ="mpthumbimg[]" value="',escape(theFile.name),'">'].join('');
        if(document.getElementById('seller_com_logo_img')!=''){
          document.getElementById('seller_com_logo_img').insertBefore(div, null);
        }
        wkmpMain('#seller_com_logo_img div').attr({class:'imgdiv'});
      };
        })(f);
    reader.readAsDataURL(f);
    }
  }
  /* seller logo change */

  wkmpMain('.mp_product_images_file').click(function(){
  document.getElementById('upload_attachment_image').addEventListener('change', handleFilegalaray, false);
  });
    wkmpMain('.mp_product_thumb_image').click(function(){
      if(typeof document.getElementById('product_thumb_image') !== 'undefined' && document.getElementById('product_thumb_image') !== null){
         document.getElementById('product_thumb_image').addEventListener('change', handleFileSelect, false);
    }
    /*sdsd*/
    /*if(document.getElementById('product_thumb_image')!=''){
       document.getElementById('product_thumb_image').addEventListener('change', handleFileSelect, false);
    }*/
  });
  wkmpMain('.mp_seller_profile_img').click(function(){
  document.getElementById('mp_useravatar').addEventListener('change', changeprofile_image, false);
  });
  wkmpMain('.wkmp-fade-banner').click(function(){
  document.getElementById('wk_mp_shop_banner').addEventListener('change', changeseller_bannerimage, false);
  });
  wkmpMain('.Company_Logo').click(function(){
  document.getElementById('mp_company_logo').addEventListener('change', seller_logo_image, false);
  });

  //multiple image upload and view end

/* user login from seller profile to commect seller profile   */
wkmpMain('a.wk_mpsocial_feedback').on('click',function(){
  var seller_shop = wkmpMain('#feedbackloged_in_status').val();
  if(wkmpMain('#feedbackloged_in_status').val())
  {
    var redirecturl = the_mpajax_script.site_url+'/'+the_mpajax_script.seller_page+'/add-feedback/'+seller_shop;
    wkmpMain(location).attr('href',redirecturl);
  }
  else{
    wkmpMain('.error-class').remove()
    wkmpMain('.wkmp-feedback-popup-container').css('display','block');
    wkmpMain('.wkmp_feedback_popup').css('display','block');
    wkmpMain('<div class="wkmp-modal-backdrop">&nbsp;</div>').appendTo('body');
  }
//wkmpMain('.wk_feedback_popup_back').css('display','block');
});
  // wkmpMain('.Give_feedback,.wkmp_login_to_feedback').click(function()
  // {
  //   wkmpMain(this).css('display','none');
  //   wkmpMain('#Mp_feedback').css('display','block');
  // });
wkmpMain('.wkmp_feedback_popup .wkmp_cross_login').on('click',function(){
  wkmpMain('.wkmp-feedback-popup-container').css('display', 'none');
  wkmpMain('#username_error').html('');
  wkmpMain('#wk_password_error').html('');
wkmpMain('div.wkmp-modal-backdrop').remove();
wkmpMain('.wkmp_feedback_popup').css('display','none');

});

// feedback form
  wkmpMain('.mp-seller-review-form').on('submit', function (evt) {
    var error = 0
    wkmpMain('#feedback-rate-error').empty()
    wkmpMain('input:text[name=feed_summary]').siblings('.error-class').remove()
    wkmpMain('textarea[name=feed_review]').siblings('.error-class').remove()

    if(!wkmpMain('#feed-price-rating').val() || !wkmpMain('#feed-value-rating').val() || !wkmpMain('#feed-quality-rating').val()){
      wkmpMain('#feedback-rate-error').append('<p>' + the_mpajax_script.mkt_tr.mkt35 + '</p>')
      error = 1
    }

    if(wkmpMain('input:text[name=feed_summary]').val() === ''){
      wkmpMain('input:text[name=feed_summary]').after('<p class="error-class">'+the_mpajax_script.mkt_tr.mkt36+'</p>')
      error = 1
    }
    if(wkmpMain('textarea[name=feed_review]').val() === ''){
      wkmpMain('textarea[name=feed_review]').after('<p class="error-class">'+the_mpajax_script.mkt_tr.mkt36+'</p>')
      error = 1
    }
    if (error) {
      evt.preventDefault()
    }
  })
});


/*administrator ajax---------------------*/

wkmpMain(document).ready(function(){

/* seller product sorting */
      wkmpMain('.mp_value_asc').change(function()
      {
      var hashes = window.location.href.split('&str=');
      var str=wkmpMain(this).val();
      if(hashes[0]!='')
      {
      window.location=hashes[0]+'&str='+str;
      }
      else
      {
      window.location=window.location.href+'&str='+str;
      }
      });

      wkmpMain('#submit-btn-feedback').on('click',function(){
        var error = 0
        wkmpMain('.error-class').remove()
        if(wkmpMain('#username').val()=='')
        {
          html = '<span class="error-class">'+the_mpajax_script.mkt_tr.mkt37+'</span>'
          wkmpMain('#username').after(html)
          error = 1
        }
        if(wkmpMain('#password').val()=='')
        {
          html = '<span class="error-class">'+the_mpajax_script.mkt_tr.mkt38+'</span>'
          wkmpMain('#password').after(html)
          error = 1
        }
        if (error == 1) {
          return false
        }
      });



      wkmpMain(document).ready(function(){

        var fb_app_id=wkmpMain('#wkfb_mp_key_app_idID').val();
        var fb_app_key=wkmpMain('#wkfb_mp_app_secret_kekey').val();
          window.fbAsyncInit = function()
            {
              FB.init({ appId:fb_app_id,
              status: true,
              cookie: true,
              xfbml: true,
              oauth: true});


              };
              (function() {
                elmFb=document.getElementById('fb-root');
                var e = document.createElement('script'); e.async = true;
                e.src = document.location.protocol+ '//connect.facebook.net/en_US/all.js';
                if(elmFb != null)
                  elmFb.appendChild(e);
              }());
      });



    /* hide downloadable area on edit product page */

    var download_check = wkmpMain(".checkbox_is_downloadable");
    download_check.each(function() {
    });
    if(wkmpMain('#wkmp_variable_is_downloadable:checkbox:checked'))
    {
      wkmpMain('.mpshow_if_variation_downloadable').css('display','table-row');
    }
    else
    {
      wkmpMain('.mpshow_if_variation_downloadable').css('display','none');
    }


    wkmpMain('#wkmp_variable_is_downloadable').change(function(){
     if(this.checked){
           wkmpMain('.mpshow_if_variation_downloadable').css('display','table-row');
     }
     else
     {
           wkmpMain('.mpshow_if_variation_downloadable').css('display','none');
     }

    });
    /* hide downloadable area on edit product page */




    /* hide virtual area on edit product page */

    if(wkmpMain('#wkmp_variable_is_virtual:checkbox:checked'))
    {

      wkmpMain('.mp_hide_if_variation_virtual').css('display','table-cell');
    }
     else
     {
           wkmpMain('.mp_hide_if_variation_virtual').css('display','none');

     }


    wkmpMain('#wkmp_variable_is_virtual').change(function(){
     if(this.checked){
           wkmpMain('.mp_hide_if_variation_virtual').css('display','table-cell');
     }
     else
     {
           wkmpMain('.mp_hide_if_variation_virtual').css('display','none');
     }

    });
    /* hide virtual area on edit product page */


/* hide manage stock area on edit product page */
/*
    if(wkmpMain('#wkmp_variable_manage_stock:checkbox:checked'))
    {
      wkmpMain('.mpshow_if_variation_manage_stock').css('display','table-row');
    }
    else
    {
		wkmpMain('.mpshow_if_variation_manage_stock').css('display','none');
    }

    wkmpMain('#wkmp_variable_manage_stock').change(function(){
     if(this.checked){
           wkmpMain('.mpshow_if_variation_manage_stock').css('display','table-row');
     }
     else
     {
           wkmpMain('.mpshow_if_variation_manage_stock').css('display','none');
     }
    });*/
    /* hide manage stock area on edit product page */

    /* show sale schedule*/
    wkmpMain(document).on("click",'.mp_sale_schedule',function(){
      // wkmpMain('.mp_sale_schedule').css('display','none');
      wkmpMain(this).css('display','none');
      // wkmpMain('.mp_cancel_sale_schedule').css('display','block');
      wkmpMain(this).siblings('.mp_cancel_sale_schedule').css('display','inline-block');
      // wkmpMain('.mp_sale_price_dates_fields').css('display','block');
      wkmpMain(this).parents('tr').siblings('.mp_sale_price_dates_fields').css('display','table-row');
    });
    wkmpMain(document).on("click",'.mp_cancel_sale_schedule',function(){
      // wkmpMain('.mp_cancel_sale_schedule').css('display','none');
      wkmpMain(this).css('display','none');
      // wkmpMain('.mp_sale_schedule').css('display','block');
      wkmpMain(this).siblings('.mp_sale_schedule').css('display','inline-block');
      // wkmpMain('.mp_sale_price_dates_fields').css('display','none');
      wkmpMain(this).parents('tr').siblings('.mp_sale_price_dates_fields').css('display','none');
    });
/* ------------------------------------------downloadable product Image----------------------------------*/

wkmpMain('#mp_attribute_variations').on('click','td.wkmp_upload_image_variation a.upload_var_image_button', function( event ){
var file_frame;
var image_id=wkmpMain(this).attr('id');
var image_val_id='upload_'+image_id;
var image_url_set_id='wkmp_variation_product_'+image_id;
event.preventDefault();
// If the media frame already exists, reopen it.
if ( file_frame ) {
  file_frame.open();
  return;
}

// Create the media frame.
file_frame = wp.media.frames.file_frame = wp.media({
  title: wkmpMain( this ).data( 'uploader_title' ),
  button: { text: wkmpMain( this ).data( 'uploader_button_text' ) },
  multiple: false  // Set to true to allow multiple files to be selected
});

   // When frame is open, select existing image attachments from custom field
file_frame.on( 'open', function() {
var selection = file_frame.state().get('selection');
 });
var query = wp.media.query();

query.filterWithIds = function(ids) {
    return _(this.models.filter(function(c) { return _.contains(ids, c.id); }));
};

  // When images are selected, place IDs in hidden custom field and show thumbnails.
file_frame.on( 'select', function() {

var selection = file_frame.state().get('selection');

// Place IDs in custom field
var attachment_ids = selection.map( function( attachment ) {
  attachment = attachment.toJSON();
  wkmpMain('#'+image_val_id).val(attachment.id);
  wkmpMain('#'+image_url_set_id).attr("src", attachment.sizes.thumbnail.url);
  return attachment.id;
})
});

// Finally, open the modal
file_frame.open();
});

/* product status downloadable file */

var file_path_field;

wkmpMain( '.wk-mp-side-body' ).on( "click", '.upload_downloadable_file', function( event )
{
    var file_frame;

    var $el = wkmpMain( this );

    file_path_field = $el.closest( 'tr' ).find( 'td.file_url input' );

    event.preventDefault();

   // If the media frame already exists, reopen it.
   if ( file_frame )
   {
      file_frame.open();
      return;
   }

   // Create the media frame.
   file_frame = wp.media.frames.file_frame = wp.media({
       title: $el.data('choose'),
       button: {
          text: $el.data('update')
       },
       multiple: false  // Set to true to allow multiple files to be selected
   });

   // When frame is open, select existing image attachments from custom field

   file_frame.on( 'open', function() {
      var selection = file_frame.state().get('selection');
      //var attachment_ids = wkmpMain('#attachment_ids').val().split(',');
   });

   var query = wp.media.query();

   query.filterWithIds = function(ids) {
      return _(this.models.filter(function(c) { return _.contains(ids, c.id); }));
   };

   var res = query.filterWithIds([3]); // change these to your IDs

   res.each(function(v){
      console.log( v.toJSON() );
   });

   // When images are selected, place IDs in hidden custom field and show thumbnails.

   file_frame.on( 'select', function() {
      var file_path = '';
      var selection = file_frame.state().get('selection');

      // Place IDs in custom field

      var attachment_ids = selection.map( function( attachment ) {
          attachment = attachment.toJSON();
          if ( attachment.url ) {
  					file_path = attachment.url;
  				}
          file_path_field.val( file_path ).change();
          return attachment.id;
      });
   });

   // Finally, open the modal

   file_frame.open();
});

wkmpMain(".select-group .dropdown-togle").on("click",function(){

    wkmpMain(this).parent().toggleClass("open");

});

wkmpMain(document).on("click",".group-selected a" ,function() {
    if(wkmpMain(".select-group .group-dropdown-menu").hasClass('open'))
      wkmpMain(".select-group .group-select").removeClass('open');
    var attr = wkmpMain(this).data('group-id');
    $grp_name = wkmpMain(this).text().trim();

    if (typeof attr !== typeof undefined && attr !== false) {
        $val = attr;
        wkmpMain("input[name='group_id']").val($val);
    }

    wkmpMain("span.filter-option").text($grp_name);
  });


});


/*----------->>> Select 2 <<<----------*/
wkmpMain(document).on("ready", function(){
  if (wkmpMain("#mp_seller_product_categories").length) {
    wkmpMain("#mp_seller_product_categories").select2();
    wkmpMain('.wc-product-search').select2();
  }
  if (wkmpMain('#new_zone_locations').length) {
    wkmpMain('#new_zone_locations').select2()
  }
    if(wkmpMain(document).find('#wk-mp-stock-qty').val()){
      wkmpMain(document).find('#wk_stock_management').parent().parent().siblings().not(":last-child").css('display', 'block')
    }
    wkmpMain(document).on('click', '#wk_stock_management', function () {
      if (wkmpMain(this).is(':checked')) {
        wkmpMain(this).parent().parent().siblings().not(":last-child").css('display', 'block')
      }
      else{
        wkmpMain(this).parent().parent().siblings().not(":last-child").css('display', 'none')
      }
    })

    // seller review box
    wkmpMain('.mp-avg-rating-box-link').on('click', function (event) {
      if(wkmpMain(event.target).hasClass('mp-avg-rating-box-link')) {
        wkmpMain('.mp-avg-rating-box').toggle()
        wkmpMain(this).toggleClass('open')
      }
    })

    wkmpMain('body').on('click', '.mp-seller-review-form p.mp-star-rating a', function () {
      var feedType = wkmpMain( this ).data('type')
			var $star   	= wkmpMain( this ),
				$rating 	= wkmpMain( this ).closest( '.mp-star-rating' ).siblings( '#feed-' + feedType + '-rating' ),
				$container 	= wkmpMain( this ).closest( '.mp-star-rating' )

			$rating.val( $star.data('rate') )
			$star.siblings( 'a' ).removeClass( 'active' )
			$star.addClass( 'active' )
			$container.addClass( 'selected' )

			return false
    });
  if (wkmpMain('table.productlist').length) {
    var mq = window.matchMedia( "(max-width: 991px)" );
    if (mq.matches) {
      if (!wkmpMain('.productlist tbody tr .wkmp-viewall').length) {
        wkmpMain('.productlist tbody tr').append('<td class="wkmp-viewall display-block"></td>');
      }
    }
    else {
      if (wkmpMain('.productlist tbody tr .wkmp-viewall').length) {
        wkmpMain('.productlist tbody tr').children('.wkmp-viewall').remove();
      }
    }
    if (wkmpMain('.wkmp-viewall').length) {
      wkmpMain('.wkmp-viewall').on('click', function () {
        wkmpMain(this).parent().children('td').filter(function () {
          return wkmpMain(this).attr("data-name") != undefined;
        }).toggleClass('display-block');
        wkmpMain(this).toggleClass('wk-mp-view');
      });
    }
    
  } 
});
