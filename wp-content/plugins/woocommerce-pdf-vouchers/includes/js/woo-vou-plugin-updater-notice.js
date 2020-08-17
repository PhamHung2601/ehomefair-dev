"use strict";

(function ($) {
    var setCookie = function (c_name, value, exdays) {
        var exdate = new Date();
        exdate.setDate(exdate.getDate() + exdays);
        var c_value = encodeURIComponent(value) + ((null === exdays) ? "" : "; expires=" + exdate.toUTCString());
        document.cookie = c_name + "=" + c_value;
    };
    $(document).on('click.woo-vou-notice-dismiss',
            '.woo-vou-notice-dismiss',
            function (e) {
                e.preventDefault();
                var $el = $(this).closest('#woo_vou_license-activation-notice');
                $el.fadeTo(100, 0, function () {
                    $el.slideUp(100, function () {
                        $el.remove();
                    });
                });

                var data = {
                            action  : 'woo_vou_dismiss_license_activation',
                            'process': 'set_dismiss_data'
                        };
                // call ajax to set dismiss data
                jQuery.get( WooVouNotice.ajaxurl, data, function( response ) {

                });
            });
})(window.jQuery);