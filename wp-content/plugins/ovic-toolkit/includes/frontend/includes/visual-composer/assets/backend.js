;(function ($) {
    "use strict";

    /* FRAMEWORK JS */
    $(document).on('click', '.vc_edit-form-tab .tab_css', function () {
        var _this     = $(this),
            _data_tab = _this.data('tabs');

        _this.addClass('active').siblings().removeClass('active');
        _this.closest('.vc_edit-form-tab').find('.vc_shortcode-param').not(_this.closest('.vc_shortcode-param')).css('display', 'none');
        _this.closest('.vc_edit-form-tab').find('.vc_shortcode-param.' + _data_tab).css('display', 'block');
    });
    /* PARAMS FIELD GRID DATA */
    var serializeObject = function (form) {
        var o = {};
        var a = form.serializeArray();
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

    $.fn.ovic_vc_dependency_data = function () {
        $(this).each(function () {
            var _this       = $(this),
                _data       = _this.data(),
                _parent     = _this.closest('form'),
                _value      = _this.find('.value_input'),
                _dependency = _parent.find(_data.dependency);

            if ( _data.compare === 'not' ) {
                if ( JSON.parse(_value.val()) != _data.value || _value.val() != _data.value ) {
                    _dependency.slideDown(300);
                } else {
                    _dependency.slideUp(300);
                }
            } else if ( _data.compare === 'check' ) {
                if ( _value.is(':checked') ) {
                    _dependency.slideDown(300);
                } else {
                    _dependency.slideUp(300);
                }
            } else {
                if ( JSON.parse(_value.val()) == _data.value || _value.val() == _data.value ) {
                    _dependency.slideDown(300);
                } else {
                    _dependency.slideUp(300);
                }
            }
        });
    };

    $.fn.ovic_vc_generate_data = function () {
        $(this).each(function () {
            var obj    = $(this).serialize(),
                parent = $(this).closest('.grid-field-settings');

            parent.find('.wpb_vc_param_value').val(obj);
        });
    };

    $(document).on('change', '.form-grid-data .dependency', function () {
        $(this).ovic_vc_dependency_data();
    });

    $(document).on('change', '.form-grid-data', function () {
        $(this).ovic_vc_generate_data();
    });

    $(document).on('click', '.form-grid-data .vc_param_group-add_content', function () {
        var ID      = function () {
                // Math.random should be unique because of its seeding algorithm.
                // Convert it to base 36 (numbers + letters), and grab the first 9 characters
                // after the decimal.
                return '_' + Math.random().toString(36).substr(2, 9);
            },
            ids     = 'responsive_' + ID(),
            name    = 'new_screen_' + ID(),
            content = '<p class="field-item ' + name + '">' +
                '<span class="wpb_element_label">New Screen</span>' +
                '<label data-tip="Screen Responsive"><input style="width:calc(100% - 180px);" name="responsive[' + name + '][breakpoint]" type="text" class="value_input" value="1024"></label>' +
                '<label data-tip="Item to Show"><input style="width:60px;" name="responsive[' + name + '][settings][slidesToShow]" type="text" class="value_input" value="4"></label>' +
                '<label data-tip="Margin Items"><input style="width:60px;" name="responsive[' + name + '][settings][slidesMargin]" type="text" class="value_input" value="30"></label>' +
                '<label data-tip="Number Rows"><input style="width:60px;" name="responsive[' + name + '][settings][rows]" type="text" class="value_input" value="1"></label>' +
                '<label for="' + ids + '" class="disable-vertical"><input id="' + ids + '" name="responsive[' + name + '][settings][vertical]" type="checkbox" class="value_input" value="false"> Disable Vertical</label>' +
                '<span class="vc_description vc_clearfix"></span>' +
                '<span class="remove button">Remove</span>' +
                '</p>';
        $(content).insertBefore(this);
    });

    $(document).on('click', '.form-grid-data .field-item .remove', function () {
        var _field = $(this).closest('.field-item'),
            _form  = $(this).closest('.form-grid-data');

        _field.remove();
        _form.ovic_vc_generate_data();
    });

    $.fn.ovic_vc_datetime = function () {
        var _this = $(this);
        _this.on('ovic_vc_datetime', function () {
            _this.each(function () {
                var _date  = $(this).find('.vc-field-date').val(),
                    _time  = $(this).find('.vc-field-time').val(),
                    _value = $(this).find('.wpb_vc_param_value');

                _value.val(_date + ' ' + _time);
            })
        }).trigger('ovic_vc_datetime');
        $(document).on('change', function () {
            _this.trigger('ovic_vc_datetime');
        });
    };

    $.fn.ovic_vc_datepicker = function () {
        var _this = $(this);
        _this.on('ovic_vc_datepicker', function () {
            _this.each(function () {
                var $this   = $(this),
                    $input  = $this.find('input'),
                    options = JSON.parse($this.find('.ovic-vc-datepicker-options').val()),
                    wrapper = '<div class="ovic-vc-datepicker-wrapper"></div>',
                    $datepicker;

                var defaults = {
                    beforeShow: function (input, inst) {
                        $datepicker = $('#ui-datepicker-div');
                        $datepicker.wrap(wrapper);
                    },
                    onClose: function () {
                        var cancelInterval = setInterval(function () {
                            if ( $datepicker.is(':hidden') ) {
                                $datepicker.unwrap(wrapper);
                                clearInterval(cancelInterval);
                            }
                        }, 100);
                    }
                };

                options = $.extend({}, options, defaults);

                $input.datepicker(options);
            })
        }).trigger('ovic_vc_datepicker');
        $(document).on('change', function () {
            _this.trigger('ovic_vc_datepicker');
        });
    };

    $.fn.vc_select_preview = function () {
        var _this = $(this);
        _this.on('vc_select_preview', function () {
            _this.each(function () {
                var url = $(this).find(':selected').data('preview');
                $(this).closest('.container-select_preview').find('.image-preview img').attr('src', url);
            });
        }).trigger('vc_select_preview');
        $(document).on('change', function () {
            _this.trigger('vc_select_preview');
        });
    };

    $(document).ajaxComplete(function (event, xhr, settings) {
        if ( xhr.status == 200 && xhr.responseText &&
            settings.data !== undefined && 0 <= settings.data.indexOf('action=vc_edit_form') ) {
            if ( wp.media ) {
                wp.media.view.Modal.prototype.on('close', function () {
                    setTimeout(function () {
                        $('.supports-drag-drop').css('display', 'none');
                    }, 1000)
                });
            }
            if ( $(event.target).find('.ovic_select_preview').length ) {
                $(event.target).find('.ovic_select_preview').vc_select_preview();
            }
            if ( $(event.target).find('.ovic-vc-field-date').length ) {
                $(event.target).find('.ovic-vc-field-date').ovic_vc_datepicker();
                $(event.target).find('.vc-date-time-picker').ovic_vc_datetime();
            }
            if ( $(event.target).find('form.form-grid-data').length ) {
                $(event.target).find('.form-grid-data .dependency').ovic_vc_dependency_data();
                $(event.target).find('.form-grid-data').ovic_vc_generate_data();
            }
        }
    });
    $(document).on('click', '.tabs-settings .tab_item', function () {
        var _this     = $(this),
            _data_tab = _this.data('tab');

        _this.addClass('active').siblings().removeClass('active');
        _this.closest('.vc_edit-form-tab').find('.vc_shortcode-param').not('.wpb_el_type_tabs_settings').css('display', 'none');
        _this.closest('.vc_edit-form-tab').find('.vc_shortcode-param.' + _data_tab).css('display', 'block');
    });

})(jQuery, window, document);