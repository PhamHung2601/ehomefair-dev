var ovic_bundleTimeout = null;
jQuery(document).ready(function (jQuery) {

    // hide search result box by default
    jQuery('#ovic_bundle_results').hide();
    jQuery('#ovic_bundle_loading').hide();

    // total price
    if ( jQuery('#product-type').val() == 'simple' ) {
        ovic_bundle_change_regular_price();
    }

    // search input
    jQuery('#ovic_bundle_keyword').keyup(function () {
        if ( jQuery('#ovic_bundle_keyword').val() !== '' ) {
            jQuery('#ovic_bundle_loading').show();
            if ( ovic_bundleTimeout !== null ) {
                clearTimeout(ovic_bundleTimeout);
            }
            ovic_bundleTimeout = setTimeout(ovic_bundle_ajax_get_data, 300);
            return false;
        }
    });

    // actions on search result items
    jQuery('#ovic_bundle_results').on('click', 'li', function () {
        jQuery(this).children('span.qty').html('<input type="number" value="1" min="0"/>');
        jQuery(this).children('span.sale').html('<input type="number" value="0" min="0" max="100"/>%');
        jQuery(this).children('span.remove').html('×');
        jQuery('#ovic_bundle_selected ul').append(jQuery(this));
        jQuery('#ovic_bundle_results').hide();
        jQuery('#ovic_bundle_keyword').val('');
        ovic_bundle_get_ids();
        ovic_bundle_change_regular_price();
        ovic_bundle_arrange();
        return false;
    });

    // change qty of each item
    jQuery('#ovic_bundle_selected').on('keyup change', '.qty input', function () {
        jQuery(this).parent().parent().find('.sale>input').trigger('change');
        ovic_bundle_get_ids();
        ovic_bundle_change_regular_price();
        return false;
    });

    // change sale of each item
    jQuery('#ovic_bundle_selected').on('keyup change', '.sale input', function () {
        var num   = jQuery(this).val(),
            price = jQuery(this).parent().parent().attr('data-price'),
            total = price - ((num / 100) * price);
        jQuery(this).parent().parent().attr('data-price-sale', total);
        ovic_bundle_get_ids();
        ovic_bundle_change_regular_price();
        return false;
    });

    // actions on selected items
    jQuery('#ovic_bundle_selected').on('click', 'span.remove', function () {
        jQuery(this).parent().remove();
        ovic_bundle_get_ids();
        ovic_bundle_change_regular_price();
        return false;
    });

    // hide search result box if click outside
    jQuery(document).on('click', function (e) {
        if ( jQuery(e.target).closest(jQuery('#ovic_bundle_results')).length === 0 ) {
            jQuery('#ovic_bundle_results').hide();
        }
    });

    // arrange
    ovic_bundle_arrange();

    jQuery(document).on('ovic_bundleDragEndEvent', function () {
        ovic_bundle_get_ids();
    });
});

function ovic_bundle_arrange() {
    jQuery('#ovic_bundle_selected li').arrangeable({
        dragEndEvent: 'ovic_bundleDragEndEvent',
        dragSelector: '.move'
    });
}

function ovic_bundle_get_ids() {
    var listId = [];
    jQuery('#ovic_bundle_selected li').each(function () {
        listId.push(jQuery(this).attr('data-id') + '/' + jQuery(this).find('.qty>input').val() + '/' + jQuery(this).find('.sale>input').val());
    });
    if ( listId.length > 0 ) {
        jQuery('#ovic_bundle_ids').val(listId.join(','));
    } else {
        jQuery('#ovic_bundle_ids').val('');
    }
}

function ovic_bundle_change_regular_price() {
    var total     = 0;
    var total_max = 0;
    jQuery('#ovic_bundle_selected li').each(function () {
        total += jQuery(this).attr('data-price-sale') * jQuery(this).find('.qty>input').val();
        total_max += jQuery(this).attr('data-price-max') * jQuery(this).find('.qty>input').val();
    });
    total     = accounting.formatMoney(total, '', ovic_bundle_vars.price_decimals, ovic_bundle_vars.price_thousand_separator, ovic_bundle_vars.price_decimal_separator);
    total_max = accounting.formatMoney(total_max, '', ovic_bundle_vars.price_decimals, ovic_bundle_vars.price_thousand_separator, ovic_bundle_vars.price_decimal_separator);
    if ( total == total_max ) {
        jQuery('#ovic_bundle_regular_price').html(total);
    } else {
        jQuery('#ovic_bundle_regular_price').html(total + ' - ' + total_max);
    }
}

function ovic_bundle_ajax_get_data() {
    // ajax search product
    ovic_bundleTimeout = null;
    var _keyWord       = jQuery('#ovic_bundle_keyword').val(),
        _ids           = jQuery('#ovic_bundle_ids').val(),
        _loading       = jQuery('#ovic_bundle_loading'),
        _results       = jQuery('#ovic_bundle_results'),
        _data          = {
            security: ovic_bundle_vars.security,
            term: _keyWord,
            limit: ovic_bundle_vars.limit,
        };
    if ( _keyWord !== '' ) {
        jQuery.ajax({
            url: ovic_bundle_vars.url,
            data: _data,
            success: function (response) {
                _results.show();
                _results.html(response);
                _loading.hide();
            },
        });
    } else {
        _results.hide();
        _loading.hide();
    }
}