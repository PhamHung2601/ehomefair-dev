jQuery(document).ready(function( $ ) {

 	jQuery(document).find("select.chosen_select").select2();

	var options='';

	if(typeof country_script != 'undefined'){

		if(country_script.country_list !=0) {

			jQuery.each(country_script.country_list, function(i,d) {
				options +="<option value='"+i+"'>"+d+"</option>";
			});


  	}

	}

	i=0;

  $(".table_rate_shipping_container").on( "click", "#insert_new", function(evt) {

		$(".table_rate_shipping tbody").append("<tr><td><input type='text' name='_table_zname["+i+"]' placeholder='Zone Label eg-Label'></td><td><select name='selected_zone["+i+"][]' multiple='multiple' class='chosen_select enhanced wp-shipping-table-rate' style='width:160px;'>"+options+"</select></td><td><select name='select_type["+i+"]' class='shipping_basis_selector'><option>Select Type</option><option value='pro_weight'>Weight</option><option value='pro_pincode'>Pincode</option><option value='pro_global'>Global Shipping</option></select></td><td><input type='text' name='_table_min_val["+i+"]' placeholder='eg-1234'></td><td><input type='text' name='_table_max_val["+i+"]' placeholder='eg-1234'></td><td><input type='text' name='_ship_price["+i+"]' placeholder='eg-1234'></td><td><button class='button-primary remove-table-row'>Remove</button></td></tr>");

    jQuery(document).find("select.chosen_select").select2();

		i++;
		evt.preventDefault();

	});

  $(".table_rate_shipping_container").on( "change", '.shipping_basis_selector', function(evt) {

    if( $(this).val() == 'pro_global' ) {

      $(this).closest('td').next().children( $('input[type=text]') ).attr('value', '*').attr('readonly', 'readonly');
      $(this).closest('td').next().next().children( $('input[type=text]') ).attr('value', '*').attr('readonly', 'readonly');

    }
    else {

      $(this).closest('td').next().children( $('input[type=text]') ).attr('value', '').removeAttr('readonly');
      $(this).closest('td').next().next().children( $('input[type=text]') ).attr('value', '').removeAttr('readonly');

    }

  });

	$(document).on("click",".remove-table-row",function(evt) {
		currentTabElm=$(this);
		if( currentTabElm.prev(".tab_rate_id").length ) {
			var shipping_id=currentTabElm.prev(".tab_rate_id").val();
			var retVal = confirm("Are You sure you want to delete this row..?");
      if( retVal == true ){

        jQuery.ajax({
          type: 'POST',
          url: country_script.sajaxurl,
          data: {"action": "row_delete_confirmation", "shipping_id":shipping_id,"nonce":country_script.nonce},
          success: function(data){
            if( data ) {
              currentTabElm.closest('tr').remove();
            	alert("Row successfully deleted...!");
            }
            else{
            	alert("Unable to delete row...!");
            }
          }
        });

      }

		}
		else{

			$(this).closest('tr').remove();

		}

		evt.preventDefault();

	});

});
