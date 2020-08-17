$dship = jQuery.noConflict();

(function($dship){

$dship(document).ready(function () {


$dship('#custom_countries select').on("change", function(e) {
//Do stufflog
thisVal = $dship(this).val();

if( thisVal ) {
  $dship.ajax({
    type: 'POST',
    url: mp_form_script.mp_form_ajax,
    data: {"action": "get_mp_select_state","nonce":mp_form_script.mp_form_nonce,"s_country":thisVal},
    beforeSend: function(){
      
    },
    success: function(response) {

      $dship("#custom_states select").empty();

      if( response != 0) {
        var options = '';
        $dship.each( response, function( i, val ) {
          options += '<option value='+i+'>'+val+'</option>';
        })

        $dship("#custom_states select").append(options);

      } else {

        alert("No states available for his country");

      }

    }
  });
}

});
  $dship('form.woocommerce-form-register').attr("enctype", "multipart/form-data");
});

})(jQuery);
