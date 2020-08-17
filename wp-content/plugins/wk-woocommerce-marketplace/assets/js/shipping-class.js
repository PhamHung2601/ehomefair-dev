const wkmpShipping = jQuery.noConflict();

wkmpShipping(document).ready(function(){

	        // tabs on manage shipping page
        wkmpShipping('#edit_ship_tab li a:not(:first)').addClass('inactive');
        wkmpShipping('.shipping-container').hide();
        wkmpShipping('.shipping-container:first').show();
        // Manage shipping Tabs
        wkmpShipping('#edit_ship_tab li a').click(function(){
            shipElm = wkmpShipping(this).data('link-id');
	            if(wkmpShipping(this).hasClass('inactive')){ //this is the start of our condition
		            wkmpShipping('#edit_ship_tab li a').addClass('inactive');
		            wkmpShipping(this).removeClass('inactive');

		            wkmpShipping('.shipping-container').hide();
		            wkmpShipping('#'+ shipElm).fadeIn('slow');

             }
        });


	wkmpShipping(".wc-shipping-class-add").on("click",function(evt){
		wkmpShipping(".wc-shipping-class-save").removeAttr("disabled");
		rand_no=Math.floor((Math.random() * 10000000) + 1);
		html='<tr data-id="new-3-'+rand_no+'" class="editing"><td class="wc-shipping-class-name"><div class="view" style="display:none"><div class="row-actions"><a class="wc-shipping-class-edit" href="#">Edit</a> | <a href="#" class="wc-shipping-class-delete">'+the_mpajax_shipping_script.ship_tr.ship1+'</a></div></div><div class="edit" style="display:block;"><input type="text" name="name[new-3-'+ rand_no +']" data-attribute="name" value="" placeholder="'+the_mpajax_shipping_script.ship_tr.ship2+'"><div class="row-actions"><a class="wc-shipping-class-cancel-edit" href="#">'+the_mpajax_shipping_script.ship_tr.ship3+'</a></div></div></td><td class="wc-shipping-class-slug"><div class="edit" style="display: block;"><input type="text" name="slug[new-3-'+ rand_no +']" data-attribute="slug" value="" placeholder="'+the_mpajax_shipping_script.ship_tr.ship4+'"></div></td>';
    html += '<td class="wc-shipping-class-description"><div class="view" style="display: none;"></div><div class="edit" style="display: block;"><input type="text" name="description[new-3-'+ rand_no +']" data-attribute="description" value="" placeholder="'+the_mpajax_shipping_script.ship_tr.ship5+'"></div></td><td class="wc-shipping-class-count"><a href=""></a></td>	</tr>';

		wkmpShipping("table.wc-shipping-classes tbody.wc-shipping-class-rows").append(html);

		evt.preventDefault();
	});

	wkmpShipping(".wc-shipping-classes").on("click",".wc-shipping-class-name .wc-shipping-class-edit",function(evt){

		wkmpShipping(".wc-shipping-class-save").removeAttr("disabled");

		wkmpShipping(this).closest("tr").find(".view").hide();
		wkmpShipping(this).closest("tr").find(".edit").show();

		evt.preventDefault();
	});

	wkmpShipping(".wc-shipping-classes").on("click",".wc-shipping-class-cancel-edit",function(evt){
		wkmpShipping(this).closest("tr").remove();
		evt.preventDefault();
	});

	wkmpShipping("#ship_data").submit(function(evt){
		wkmpShipping.ajax({
	        url : the_mpajax_shipping_script.shippingajaxurl,
	        type : "POST",
	        data : {"action":"add_shipping_class","data":wkmpShipping("#ship_data").serialize(),"nonce" :the_mpajax_shipping_script.shippingNonce},
	        success : function(data) {
	            window.location.reload();
	        }
	    });

		evt.preventDefault();
	});

	wkmpShipping(".btn-save-cost").on("click",function(evt){
		shipping_cost=wkmpShipping(this).closest(".shipping-method-add-cost").find("input,select").serialize();
    instance_id = wkmpShipping(this).prev().val();
		wkmpShipping.ajax({
	        url : the_mpajax_shipping_script.shippingajaxurl,
	        type : "POST",
	        data : {"action":"save_shipping_cost","ship_cost":shipping_cost,"instance_id":instance_id,"nonce" :the_mpajax_shipping_script.shippingNonce},
	        success : function(data) {
	            window.location.reload();
	        }
	    });

		evt.preventDefault();

	});

  // delete shipping class
  wkmpShipping(".wk_del_ship_class").on("click",function(evt){

		term_id = wkmpShipping(this).data('term');

		wkmpShipping.ajax({
	        url : the_mpajax_shipping_script.shippingajaxurl,
	        type : "POST",
	        data : {"action":"delete_shipping_class","get-term":term_id,"nonce" :the_mpajax_shipping_script.shippingNonce},
	        success : function(data) {
	            window.location.reload();
	        }
	    });

		evt.preventDefault();

	});

  // Send ajax request to get country and state list
  wkmpShipping.ajax({
    type: 'POST',
    url: the_mpajax_script.mpajaxurl,
    data: {"action": "get_all_countries","nonce":the_mpajax_script.nonce},
      success: function(data)
      {
        // append country list to unordered list
          wkmpShipping(".live-search-list").append(data);
         }
    });
    var rowNum = 0;


    wkmpShipping(".wc-shipping-zones").on("click",".wc-shipping-zone-cancel-edit",function(evt){

        dynElm=wkmpShipping(this).closest("tr.editing");
        dynElm.remove();
        evt.preventDefault();
    });
    wkmpShipping(".wc-shipping-zones").on("click",".wc-shipping-zone-postcodes-toggle",function(evt){
        wkmpShipping(this).hide();
        wkmpShipping(this).next(".wc-shipping-zone-postcodes").show();
        evt.preventDefault();
    });

        // Show list of countries and states on focus input box

        wkmpShipping(document).on("focusin","#unused_elm",function(){
            wkmpShipping(this).siblings(".live-search-list").slideDown();
        });

        // On click to country or state show it on input box and save it on input type hidden

       wkmpShipping(document).on("click",".live-search-list li",function(){
            wkmpShipping(this).parent(".live-search-list").slideUp();
            currentVal=wkmpShipping(this).text().trim();
            searched_term=wkmpShipping(this).data("search-term");
            tag = wkmpShipping('<div class="mp_ship_tags" data-value='+searched_term+'>' + currentVal + '<a class="mp_del_tag">x</a></div>');
            if (wkmpShipping(this).parent().prev("#mp_set_zone_location").val()=='')
              wkmpShipping(this).parent().prev("#mp_set_zone_location").val(wkmpShipping(this).parent().prev("#mp_set_zone_location").val()+searched_term);
            else
              wkmpShipping(this).parent().prev("#mp_set_zone_location").val(wkmpShipping(this).parent().prev("#mp_set_zone_location").val()+','+searched_term);

             tag.insertBefore(wkmpShipping(this).parent().siblings("#unused_elm"), wkmpShipping(this).parent().siblings("#unused_elm"));

             wkmpShipping(this).parent().siblings("#unused_elm").val('');


         });
        wkmpShipping(document).on('click', '.mp_del_tag', function () {
              searched_term = wkmpShipping(this).parent().data("value");
              if(searched_term){
                  nowReq=wkmpShipping(this).parent().siblings("#mp_set_zone_location").val();
                  var new_term_1 = searched_term+',';
                  var new_term_2 = ','+searched_term;
                  if(nowReq.indexOf(new_term_1) !== -1){
                    var splitReq = nowReq.replace(searched_term+',',"");
                  }
                  else if(nowReq.indexOf(new_term_2) !== -1){
                    var splitReq = nowReq.replace(','+searched_term,"");
                  }
                  else {
                    var splitReq = nowReq.replace(searched_term,"");
                  }
                wkmpShipping(this).parent().siblings("#mp_set_zone_location").val(splitReq);
                wkmpShipping(this).parent().remove();
              }
            });

        // delete tags on click to delete button




        // Limit search country or state result on every charater input

        wkmpShipping(document).on('keyup',".live-search-box", function(){
            var searchTerm = wkmpShipping(this).val();
            str = searchTerm.toLowerCase().replace(/\b[a-z]/g, function(letter) {
                return letter.toUpperCase();
            });
            wkmpShipping(this).siblings('.live-search-list').find("li").each(function(){
                if (wkmpShipping(this).is(":contains("+str+")") || str.length < 1) {
                    wkmpShipping(this).show();
                } else {
                    wkmpShipping(this).hide();

                }

            });


        });

        // Add Shipping Method

        wkmpShipping(".add-ship-method").on("click",function(){
            selectedZoneId=wkmpShipping("#add_method_id").data('get-zone');
            selected_Shipmethod=wkmpShipping("#add_method_id").val();

            if (selected_Shipmethod && selectedZoneId) {

                  wkmpShipping.ajax({
                    type: 'POST',
                    url: the_mpajax_script.mpajaxurl,
                    data: {"action": "add_shipping_method","zone-id":selectedZoneId,"ship-method":selected_Shipmethod,"nonce":the_mpajax_script.nonce},
                      success: function(data)
                      {
                        if(data)
                          location.reload();
                      }
                });

              }


        });

        // Delete shipping method_disabled

          wkmpShipping(".del-ship-method").on("click",function(ext){

            meth_id = wkmpShipping(".del-ship-method").data('methid');
            meth_id = meth_id.split('-');
            zone_id = meth_id[0];
            instance_id = meth_id[1];
            if ( zone_id && instance_id ) {

                  wkmpShipping.ajax({
                    type: 'POST',
                    url: the_mpajax_script.mpajaxurl,
                    data: {"action": "delete_shipping_method","zone-id":zone_id,"instance-id":instance_id,"nonce":the_mpajax_script.nonce},
                      success: function(data)
                      {
                        if(data)
                          ext.target.closest('li').remove();
                          // location.reload();
                        }
                });

              }


        });

      // Delete shipping Zones

      wkmpShipping(".wc-shipping-zone-delete").on("click",function(evt){
           ret_val = confirm( the_mpajax_shipping_script.ship_tr.ship6 );

           if( ret_val ) {

             getElm=wkmpShipping(this);
             if(typeof getElm.data("zone-id") !=='undefined'){
               del_zone=getElm.data('zone-id');
               if (del_zone) {

                 wkmpShipping.ajax({
                   type: 'POST',
                   url: the_mpajax_script.mpajaxurl,
                   data: {"action": "del_zone","zone-id":del_zone,"nonce":the_mpajax_script.nonce},
                   success: function(data)
                   {
                     location.reload();
                   }
                 });

               }
             }

             evt.preventDefault();
           }
        });

});
