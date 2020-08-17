(function ($) {
    "use strict"; // Start of use strict

    var serializeObject = function ($form) {
        var o = {};
        var a = $form.serializeArray();
        $.each(a, function () {
            if ( o[ this.name ] ) {
                if ( !o[ this.name ].push ) {
                    o[ this.name ] = [ o[ this.name ] ];
                }
                o[ this.name ].push(this.value || '');
            } else {
                o[ this.name ] = this.value || '';
            }
        });
        return o;
    };
    var validateEmail   = function (email) {
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
    $(document).on('click', '.submit-newsletter', function (e) {

        var thisWrap = $(this).closest('.newsletter-form-wrap'),
            form     = serializeObject(thisWrap),
            input    = thisWrap.find('input[name="email"]');

        if ( input.val() === '' ) {
            if ( $.growl ) {
                $.growl.error({
                    title: ovic_mailchimp.notice_title,
                    message: '<p class="growl-content">' + ovic_mailchimp.text_empty + '</p>'
                });
            } else {
                thisWrap.parent().append('<div class="return-message bg-danger">' + ovic_mailchimp.text_empty + '</div>');
            }
            return false;
        } else if ( !validateEmail(input.val()) ) {
            if ( $.growl ) {
                $.growl.error({
                    title: ovic_mailchimp.notice_title,
                    message: '<p class="growl-content">' + ovic_mailchimp.format_email + '</p>'
                });
            } else {
                thisWrap.parent().append('<div class="return-message bg-danger">' + ovic_mailchimp.format_email + '</div>');
            }
            return false;
        }

        if ( thisWrap.hasClass('processing') ) {
            return false;
        }
        thisWrap.addClass('processing');
        thisWrap.parent().find('.return-message').remove();

        $.ajax({
            type: 'POST',
            url: ovic_mailchimp.ajaxurl,
            data: {
                action: 'submit_mailchimp_via_ajax',
                data: form,
            },
            success: function (response) {
                if ( $.trim(response.success) == 'yes' ) {

                    thisWrap.trigger("reset");

                    if ( $.growl ) {
                        $.growl.notice({
                            title: ovic_mailchimp.notice_title,
                            message: '<p class="growl-content">' + response.message + '</p>'
                        });
                    } else {
                        thisWrap.parent().append('<div class="return-message bg-success">' + response.message + '</div>');
                    }
                    $(document.body).trigger('ovic_newsletter_success', response.message);
                } else {
                    if ( $.growl ) {
                        $.growl.error({
                            title: ovic_mailchimp.notice_title,
                            message: '<p class="growl-content">' + response.message + '</p>'
                        });
                    } else {
                        thisWrap.parent().append('<div class="return-message bg-danger">' + response.message + '</div>');
                    }
                    $(document.body).trigger('ovic_newsletter_error', response.message);
                }
            },
            complete: function () {
                thisWrap.removeClass('processing');
            },
            ajaxError: function () {
                thisWrap.removeClass('processing');
            }
        });

        e.preventDefault();
    })

})(jQuery); // End of use strict