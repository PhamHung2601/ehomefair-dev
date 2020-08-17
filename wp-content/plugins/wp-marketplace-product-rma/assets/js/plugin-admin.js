mp_rma = jQuery.noConflict();

(function(mp_rma) {
    mp_rma(document).ready(function() {
        mp_rma(".delete-reason").on("click", function(event) {
            return confirm(adminobj.rma_arr.rma1);
        });
        mp_rma(".delete-rma").on("click", function(event) {
            return confirm(adminobj.rma_arr.rma2);
        });
        mp_rma("#doaction").on("click", function() {
          if (window.location.search.split('=')[1] != undefined && window.location.search.split('=')[1] == 'mp-rma-reasons') {
            if (mp_rma("#bulk-action-selector-top").val() != -1 ) {
                return confirm(adminobj.rma_arr.rma1);
            }
          }
        });

        mp_rma(".mp-rma-image-link").on("click", function(evt) {
            evt.preventDefault();
            src = mp_rma(this).data('source');
            mp_rma('.mp-rma-image-full-cover img').attr('src', src);
            mp_rma(".mp-rma-image-full-overlay-bg").show();
            mp_rma(".mp-rma-image-full-overlay").show();
        });

        mp_rma(".mfp-close").on("click", function() {
            mp_rma(".mp-rma-image-full-overlay-bg").hide();
            mp_rma(".mp-rma-image-full-overlay").hide();
        });

        //upload shipping label
        mp_rma(document).on('click','#upload_shipping_label',function(event) {

            var custom_uploader;

            event.preventDefault();

            var custom_uploader = wp.media({

                title:adminobj.rma_arr.rma3,

                button: {

                    text: adminobj.rma_arr.rma4,

                },

                multiple: false  // Set this to true to allow multiple files to be selected

            })

            .on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                mp_rma('.shipping-label-path').val(attachment.url);
            })
            .open();

        });

    });
})(mp_rma);
