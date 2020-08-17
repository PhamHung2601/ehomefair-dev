(function ($) {
    "use strict";

    function ovic_bundle_check_ready() {
        var need_selection = false,
            is_empty       = true;

        $('.product-type-simple .ovic_bundle-products .ovic_bundle-product').each(function () {
            if ( $(this).attr('data-qty') > 0 && $(this).attr('data-id') === 0 ) {
                need_selection = true;
            }
            if ( $(this).attr('data-qty') > 0 ) {
                is_empty = false;
            }
        });
        if ( need_selection || is_empty ) {
            $('.product-type-simple .ovic_bundle-wrap .single_add_to_cart_button').addClass('disabled');
            if ( need_selection ) {
                $('.product-type-simple .single_add_to_cart_button').addClass('ovic_bundle-selection');
            } else {
                $('.product-type-simple .single_add_to_cart_button').removeClass('ovic_bundle-selection');
            }
            if ( is_empty ) {
                $('.product-type-simple .ovic_bundle-wrap .single_add_to_cart_button').addClass('ovic_bundle-empty');
            } else {
                $('.product-type-simple .ovic_bundle-wrap .single_add_to_cart_button').removeClass('ovic_bundle-empty');
            }
        } else {
            $('.product-type-simple .ovic_bundle-wrap .single_add_to_cart_button').removeClass('disabled ovic_bundle-selection ovic_bundle-empty');
        }
        ovic_bundle_calc_price();
    }

    function ovic_bundle_calc_price() {
        var total           = 0,
            total_save      = 0,
            price_qty       = 0,
            total_html      = '',
            total_save_html = '';

        $('.product-type-simple .ovic_bundle-products .ovic_bundle-product').each(function () {
            if ( $(this).attr('data-price') > 0 ) {
                if ( $(this).find('.ovic_bundle-check input').length && $(this).find('.ovic_bundle-check input').is(':checked') ) {
                    price_qty = $(this).attr('data-price') * $(this).attr('data-qty');
                    total_save += price_qty;
                    total += price_qty - (($(this).attr('data-sale') / 100) * price_qty);
                } else {
                    price_qty = $(this).attr('data-price') * $(this).attr('data-qty');
                    total_save += price_qty;
                    total += price_qty - (($(this).attr('data-sale') / 100) * price_qty);
                }
            }
        });
        total_save              = total_save - total;
        var total_formated      = ovic_bundle_format_money(total, ovic_bundle_vars.price_decimals, '', ovic_bundle_vars.price_thousand_separator, ovic_bundle_vars.price_decimal_separator);
        var total_save_formated = ovic_bundle_format_money(total_save, ovic_bundle_vars.price_decimals, '', ovic_bundle_vars.price_thousand_separator, ovic_bundle_vars.price_decimal_separator);
        switch ( ovic_bundle_vars.price_format ) {
            case '%1$s%2$s':
                //left
                total_html += ovic_bundle_vars.currency_symbol + '' + total_formated;
                total_save_html += ovic_bundle_vars.currency_symbol + '' + total_save_formated;
                break;
            case '%1$s %2$s':
                //left with space
                total_html += ovic_bundle_vars.currency_symbol + ' ' + total_formated;
                total_save_html += ovic_bundle_vars.currency_symbol + ' ' + total_save_formated;
                break;
            case '%2$s%1$s':
                //right
                total_html += total_formated + '' + ovic_bundle_vars.currency_symbol;
                total_save_html += total_save_formated + '' + ovic_bundle_vars.currency_symbol;
                break;
            case '%2$s %1$s':
                //right with space
                total_html += total_formated + ' ' + ovic_bundle_vars.currency_symbol;
                total_save_html += total_save_formated + ' ' + ovic_bundle_vars.currency_symbol;
                break;
            default:
                //default
                total_html += ovic_bundle_vars.currency_symbol + '' + total_formated;
                total_save_html += ovic_bundle_vars.currency_symbol + '' + total_save_formated;
        }
        $('#ovic_bundle_total').html(ovic_bundle_vars.bundle_price_text + ' <span>' + total_html + '</span>').slideDown();
        $('#ovic_bundle_total_save').html(ovic_bundle_vars.bundle_price_save_text + ' <span>' + total_save_html + '</span>').slideDown();
        $(document.body).trigger('ovic_bundle_calc_price', [ total, total_formated, total_html ]);
    }

    function ovic_bundle_save_ids() {
        var ovic_bundle_ids = Array();
        $('.product-type-simple .ovic_bundle-products .ovic_bundle-product').each(function () {
            if ( $(this).attr('data-id') !== 0 ) {
                ovic_bundle_ids.push($(this).attr('data-id') + '/' + $(this).attr('data-qty') + '/' + $(this).attr('data-sale'));
            }
        });
        $('#ovic_bundle_ids').val(ovic_bundle_ids.join(','));
    }

    function ovic_bundle_format_money(number, places, symbol, thousand, decimal) {
        number = number || 0;
        places = !isNaN(places = Math.abs(places)) ? places : 2;
        symbol       = symbol !== undefined ? symbol : "$";
        thousand     = thousand || ",";
        decimal      = decimal || ".";
        var negative = number < 0 ? "-" : "",
            i        = parseInt(number = Math.abs(+number || 0).toFixed(places), 10) + "",
            j        = (
                j = i.length
            ) > 3 ? j % 3 : 0;
        return symbol + negative + (
            j ? i.substr(0, j) + thousand : ""
        ) + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousand) + (
            places ? decimal + Math.abs(number - i).toFixed(places).slice(2) : ""
        );
    }

    $('.product-type-simple .ovic_bundle-products select').on('change', function () {
        $(this).closest('.ovic_bundle-product').attr('data-id', 0);
        ovic_bundle_check_ready();
    });

    $(document).on('found_variation', function (e, t) {
        if ( $('#ovic_bundle_products').length ) {
            if ( t.image.url && t.image.srcset ) {
                // change image
                $(e.target).closest('.ovic_bundle-product').find('.ovic_bundle-thumb-ori').hide();
                $(e.target).closest('.ovic_bundle-product').find('.ovic_bundle-thumb-new').html('<img src="' + t.image.url + '" srcset="' + t.image.srcset + '"/>').show();
            }
            if ( t.price_html ) {
                // change price
                $(e.target).closest('.ovic_bundle-product').find('.ovic_bundle-price-ori').hide();
                $(e.target).closest('.ovic_bundle-product').find('.ovic_bundle-price-new').html(t.price_html).show();
            }
            if ( t.is_purchasable ) {
                // change stock notice
                if ( t.is_in_stock ) {
                    $('#ovic_bundle_wrap').next('p.stock').show();
                    $(e.target).closest('.ovic_bundle-product').attr('data-id', t.variation_id);
                    $(e.target).closest('.ovic_bundle-product').attr('data-price', t.display_price);
                    ovic_bundle_check_ready();
                    ovic_bundle_save_ids();
                } else {
                    $('#ovic_bundle_wrap').next('p.stock').hide();
                }
                if ( t.availability_html !== '' ) {
                    $(e.target).closest('.variations_form').find('p.stock').remove();
                    $(e.target).closest('.variations_form').append(t.availability_html);
                }
            }
            if ( t.variation_description !== '' ) {
                $(e.target).closest('.ovic_bundle-product').find('.ovic_bundle-variation-description').html(t.variation_description).show();
            } else {
                $(e.target).closest('.ovic_bundle-product').find('.ovic_bundle-variation-description').html('').hide();
            }

            if ( ovic_bundle_vars.change_image == 'no' ) {
                // prevent changing the main image
                $(e.target).closest('.variations_form').trigger('reset_image');
            }
        }
    });

    $(document).on('reset_data', function (e) {
        if ( $('#ovic_bundle_products').length ) {
            // reset thumb
            $(e.target).closest('.ovic_bundle-product').find('.ovic_bundle-thumb-new').hide();
            $(e.target).closest('.ovic_bundle-product').find('.ovic_bundle-thumb-ori').show();
            // reset price
            $(e.target).closest('.ovic_bundle-product').find('.ovic_bundle-price-new').hide();
            $(e.target).closest('.ovic_bundle-product').find('.ovic_bundle-price-ori').show();
            // reset stock
            $(e.target).closest('.variations_form').find('p.stock').remove();
            // reset desc
            $(e.target).closest('.ovic_bundle-product').find('.ovic_bundle-variation-description').html('').hide();
        }
    });

    //$('.single_add_to_cart_button').on('click', function () {
    //    $(this).addClass('loading');
    //    if ( $('#ovic_bundle_ids').length ) {
    //        $.ajax({
    //            url: ovic_bundle_vars.ajax_url,
    //            type: "POST",
    //            data: {
    //                action: 'ovic_bundle_custom_data',
    //                ovic_bundle_ids: $('#ovic_bundle_ids').val(),
    //                ovic_bundle_nonce: ovic_bundle_vars.ovic_bundle_nonce
    //            },
    //        });
    //    }
    //});

    $('.product-type-simple .ovic_bundle-products .ovic_bundle-qty input').on('keyup change', function () {
        var qty     = parseInt($(this).val());
        var min_qty = parseInt($(this).attr('min'));
        var max_qty = parseInt($(this).attr('max'));
        if ( !isNaN(min_qty) && (
            qty < min_qty
        ) ) {
            qty = min_qty;
        }
        if ( !isNaN(max_qty) && (
            qty > max_qty
        ) ) {
            qty = max_qty;
        }
        $(this).val(qty);
        $(this).closest('.ovic_bundle-product').attr('data-qty', qty);
        ovic_bundle_check_ready();
        ovic_bundle_save_ids();
    });

    $('.product-type-simple').on('click', '.single_add_to_cart_button.disabled', function (e) {
        if ( $(this).hasClass('ovic_bundle-selection') ) {
            alert(ovic_bundle_vars.alert_selection);
        } else if ( $(this).hasClass('ovic_bundle-empty') ) {
            alert(ovic_bundle_vars.alert_empty);
        }
        e.preventDefault();
    });

    $('.product-type-simple').on('click', '.ovic_bundle-check input', function () {
        var _this  = $(this),
            _wrap  = _this.closest('.ovic_bundle-product'),
            _input = _wrap.find('.ovic_bundle-qty input');

        if ( _this.is(':checked') ) {
            _input.val(1).trigger('change');
            _input.removeAttr('disabled');
        } else {
            _input.val(0).trigger('change');
            _input.attr({
                'disabled': 'disabled'
            });
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        ovic_bundle_check_ready();
    });

})(jQuery);