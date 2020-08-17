(function ($) {
    "use strict"; // Start of use strict

    /* ---------------------------------------------
     Init popup
     --------------------------------------------- */
    function ovic_init_popup() {
        if ( ovic_popup.enable_popup_mobile != 'on' ) {
            if ( $(window).innerWidth() < 768 ) {

                return false;
            }
        }
        var disabled_popup_by_user = getCookie('ovic_disabled_popup_by_user');
        if ( disabled_popup_by_user == 'true' ) {
            return false;
        } else {
            if ( $('body').hasClass('ovic-popup-on') && ovic_popup.enable_popup == 'on' ) {
                setTimeout(function () {
                    var data = {
                        action: 'ovic_get_content_popup',
                        current_page_id:ovic_popup.current_page_id
                    };

                    $.post(ovic_popup.ajaxurl, data, function (response) {
                        $.magnificPopup.open({
                            items: {
                                src: '<div class="white-popup mfp-with-anim">'+response.content+'</div>', // can be a HTML string, jQuery object, or CSS selector
                                type: 'inline',

                            },
                            removalDelay: 500, //delay removal by X to allow out-animation
                            callbacks: {
                                beforeOpen: function() {
                                    this.st.mainClass = response.display_effect;
                                }
                            },
                            midClick: true
                        });

                    });
                }, ovic_popup.delay_time);

            }
        }
    }

    $(document).on('change', '.ovic_disabled_popup_by_user', function () {
        if ( $(this).is(":checked") ) {
            setCookie("ovic_disabled_popup_by_user", 'true', 7);
            $('#popup-newsletter button.close').trigger('click');
        } else {
            setCookie("ovic_disabled_popup_by_user", '', 0);
        }
    });

    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires     = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + "; " + expires;
    }

    function getCookie(cname) {
        var name = cname + "=";
        var ca   = document.cookie.split(';');
        for ( var i = 0; i < ca.length; i++ ) {
            var c = ca[ i ];
            while ( c.charAt(0) == ' ' ) {
                c = c.substring(1);
            }
            if ( c.indexOf(name) == 0 ) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    /* ---------------------------------------------
     Scripts ready
     --------------------------------------------- */
    $(document).ready(function () {
        ovic_init_popup();
    });
})
(jQuery); // End of use strict