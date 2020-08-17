;(function ($) {
    "use strict";

    var kt_import_percent          = 0,
        kt_import_percent_increase = 0,
        kt_import_index_request    = 0,
        kt_arr_import_request_data = [],
        optionid                   = '';

    $(document).on('click', '.button-primary.open-import', function () {
        var _contentID = $(this).data('id');
        tb_show('Import Option', '#TB_inline?inlineId=content-demo-' + _contentID + '');
    });

    function kt_import_ajax_handle() {
        if ( kt_import_index_request == kt_arr_import_request_data.length ) {
            $('#option-' + optionid).addClass('done-import');
            $('[data-option="' + optionid + '"]').find('.progress').hide();
            $('[data-option="' + optionid + '"]').find('.progress-wapper').addClass('complete');
            return;
        }
        $('[data-option="' + optionid + '"] .progress-item').find('.' + kt_arr_import_request_data[ kt_import_index_request ][ "action" ]).show();

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: kt_arr_import_request_data[ kt_import_index_request ],
            complete: function (jqXHR, textStatus) {
                $('[data-option="' + optionid + '"] .progress-item').find('.' + kt_arr_import_request_data[ kt_import_index_request ][ "action" ]).addClass('complete');
                kt_import_percent += kt_import_percent_increase;
                kt_progress_bar_handle();
                kt_import_index_request++;
                setTimeout(function () {
                    kt_import_ajax_handle();
                }, 200);
            }
        });
    }

    function kt_progress_bar_handle() {

        if ( kt_import_percent > 100 ) {
            kt_import_percent = 100;
        }
        var progress_bar  = $('[data-option="' + optionid + '"]').find('.progress-circle .c100'),
            class_percent = 'p' + Math.ceil(kt_import_percent);
        progress_bar.addClass(class_percent);

        progress_bar.find('.percent').html(Math.ceil(kt_import_percent) + '%');
    }

    $(document).on('click', '.kt-button-import', function () {
        $(this).closest('#TB_ajaxContent').find('.progress-wapper').show();

        var id           = $(this).data('id'),
            slug         = $(this).data('slug'),
            content_ajax = $(this).closest('#TB_ajaxContent');

        content_ajax.find('[data-percent="1"]').attr('class', 'c100 p0 dark green');
        content_ajax.find('.percent').html('0%');
        content_ajax.find('.progress-wapper').show();
        kt_import_percent          = 0;
        kt_import_percent_increase = 0;
        kt_import_index_request    = 0;
        kt_arr_import_request_data = [];
        optionid                   = $(this).data('optionid');

        var
            import_rtl_content     = 0,
            import_full_content    = false,
            import_page            = false,
            import_post            = false,
            import_product         = false,
            import_menu            = false,
            import_widget          = false,
            import_revslider       = false,
            import_theme_options   = false,
            import_setting_options = false,
            import_attachments     = false;

        $('[data-option="' + optionid + '"]').find('.progress-wapper .item').removeClass('complete').css('display', 'none');
        /* IMPORT PAGE */
        if ( content_ajax.find('[name="import_rtl_content"]').is(':checked') ) {
            import_rtl_content = 1;
        } else {
            import_rtl_content = 0;
        }
        if ( $('#kt_import_page_content-' + id).is(':checked') ) {
            import_page = true;
        } else {
            import_page = false;
        }
        if ( $('#kt_import_post_content-' + id).is(':checked') ) {
            import_post = true;
        } else {
            import_post = false;
        }
        if ( $('#kt_import_product_content-' + id).is(':checked') ) {
            import_product = true;
        } else {
            import_product = false;
        }
        if ( $('#kt_import_product_content-' + id).is(':checked') ) {
            import_product = true;
        } else {
            import_product = false;
        }
        if ( $('#kt_import_widget-' + id).is(':checked') ) {
            import_widget = true;
        } else {
            import_widget = false;
        }
        if ( $('#kt_import_revslider-' + id).is(':checked') ) {
            import_revslider = true;
        } else {
            import_revslider = false;
        }
        if ( $('#kt_import_attachments-' + id).is(':checked') ) {
            import_attachments = true;
        } else {
            import_attachments = false;
        }
        if ( $('#kt_import_menu-' + id).is(':checked') ) {
            import_menu = true;
        } else {
            import_menu = false;
        }
        if ( $('#kt_import_theme_options-' + id).is(':checked') ) {
            import_theme_options = true;
        } else {
            import_theme_options = false;
        }
        if ( $('#kt_import_setting_options-' + id).is(':checked') ) {
            import_setting_options = true;
        } else {
            import_setting_options = false;
        }
        if ( $('#kt_import_full_content-' + id).is(':checked') ) {
            import_full_content    = true;
            import_widget          = true;
            import_revslider       = true;
            import_menu            = true;
            import_attachments     = true;
            import_theme_options   = true;
            import_setting_options = true;
        }

        // Demo content
        kt_arr_import_request_data.push({
            'action': 'kt_import_single_page_content',
            'optionid': optionid,
            'rtl_content': import_rtl_content,
            'slug_home': [ slug, 'blog', 'contact-us', 'about-us' ],
        });
        if ( import_full_content ) {
            var data = {
                'action': 'kt_import_full_content',
                'optionid': optionid,
                'rtl_content': import_rtl_content,
            };
            kt_arr_import_request_data.push(data);
        }

        if ( import_page ) {
            var data = {
                'action': 'kt_import_page_content',
                'optionid': optionid,
                'rtl_content': import_rtl_content,
            }
            kt_arr_import_request_data.push(data);
        }
        if ( import_post ) {
            var data = {
                'action': 'kt_import_post_content',
                'optionid': optionid,
                'rtl_content': import_rtl_content,
            }
            kt_arr_import_request_data.push(data);
        }
        if ( import_product ) {
            var data = {
                'action': 'kt_import_product_content',
                'optionid': optionid,
                'rtl_content': import_rtl_content,
            }
            kt_arr_import_request_data.push(data);
        }
        if ( import_attachments ) {
            var data = {
                'action': 'kt_import_attachments',
                'optionid': optionid,
                'rtl_content': import_rtl_content,
            }
            kt_arr_import_request_data.push(data);
        }
        if ( import_menu ) {
            kt_arr_import_request_data.push({
                'action': 'kt_import_menu',
                'optionid': optionid,
                'rtl_content': import_rtl_content,
            });
        }
        if ( import_theme_options ) {
            kt_arr_import_request_data.push({
                'action': 'kt_import_theme_options',
                'optionid': optionid,
                'rtl_content': import_rtl_content,
            });
        }
        if ( import_setting_options ) {
            kt_arr_import_request_data.push({
                'action': 'kt_import_setting_options',
                'optionid': optionid,
                'rtl_content': import_rtl_content,
            });
        }
        if ( import_widget ) {
            kt_arr_import_request_data.push({
                'action': 'kt_import_widget',
                'optionid': optionid,
                'rtl_content': import_rtl_content,
            });
        }
        if ( import_revslider ) {
            kt_arr_import_request_data.push({
                'action': 'kt_import_revslider',
                'optionid': optionid,
                'rtl_content': import_rtl_content,
            });
        }

        kt_arr_import_request_data.push({
            'action': 'kt_import_config',
            'optionid': optionid,
            'rtl_content': import_rtl_content,
        });
        console.log(import_rtl_content);

        var total_ajaxs = kt_arr_import_request_data.length;

        if ( total_ajaxs == 0 ) {
            return;
        }

        kt_import_percent_increase = (100 / total_ajaxs);

        kt_import_ajax_handle();
    });

    function full_content_change() {
        $('.kt_import_full_content').each(function () {
            var _this = $(this);
            if ( _this.is(':checked') ) {
                _this.closest('.group-control').find('input[type="checkbox"]').not(_this).attr('checked', false);
                _this.closest('.group-control').find('label').not(_this.parent()).css({
                    'pointer-events': 'none',
                    'opacity': '0.4'
                });
            } else {
                _this.closest('.group-control').find('label').not(_this.parent()).css({
                    'pointer-events': 'initial',
                    'opacity': '1'
                });
            }
        })
    }

    full_content_change();

    $(document).on('change', function () {
        full_content_change()
    });

})(jQuery, window, document);