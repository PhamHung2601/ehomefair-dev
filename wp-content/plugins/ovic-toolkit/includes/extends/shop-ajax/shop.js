(function ($) {
    'use strict';

    function removeParam(key, sourceURL) {
        var rtn         = sourceURL.split("?")[ 0 ],
            param,
            params_arr  = [],
            queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[ 1 ] : "";
        if ( queryString !== "" ) {
            params_arr = queryString.split("&");
            for ( var i = params_arr.length - 1; i >= 0; i -= 1 ) {
                param = params_arr[ i ].split("=")[ 0 ];
                if ( param === key ) {
                    params_arr.splice(i, 1);
                }
            }
            rtn = rtn + "?" + params_arr.join("&");
        }
        return rtn;
    }

    function raw_content_page(data) {
        var _content = $('.woocommerce.woocommerce-page'),
            _loading = _content.find('.ovic-products'),
            _equal   = _content.find('.equal-container.better-height');

        data.url = removeParam('ovic_raw_content', data.url);
        _loading.addClass('loading');
        $.ajax({
            type: 'GET',
            url: data.url,
            data: {
                ovic_raw_content: 1
            },
            success: function (response) {
                var $html   = $.parseHTML(response, document, true),
                    $class  = ovic_shop_ajax.response_class,
                    $script = ovic_shop_ajax.response_script;

                for ( var i in $class ) {
                    $($class[ i ]).each(function (index) {
                        var _elems = $($class[ i ], $html)[ index ] ? $($class[ i ], $html)[ index ].innerHTML : '';

                        if ( _elems == '' ) {
                            $(this).addClass('elem-hidden');
                        } else {
                            $(this).removeClass('elem-hidden');
                        }
                        $(this).html(_elems);
                    });
                }
                for ( var i in $script ) {
                    $.getScript($script[ i ], function (data, textStatus, jqxhr) {
                    });
                }
                if ( data.push == true )
                    history.pushState({path: data.url, title: document.title}, null, data.url);
            },
            complete: function () {
                if ( _equal.length )
                    _equal.ovic_better_equal_elems();
                _loading.removeClass('loading');
                _loading.trigger('ovic_ajax_shop_complete');
            }
        });
    }

    /** AJAX SHOP
     *
     * Class js: .ovic-shop-filter, .filter-item, .reset, .display-mode.
     * Markup HTML:
     * <div class="alert alert-info ovic-shop-filter">
     *       <div class="list-filter"></div>
     *       <a href="' . get_permalink( wc_get_page_id( 'shop' ) ) . '" class="filter-item reset">Reset</a>
     * </div>
     * */
    $.fn.ShopAjaxSerializeObject = function () {
        var o = {};
        var a = this.serializeArray();
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

    $.fn.before_action_request = function () {
        var _url  = '',
            _form = {},
            _data = '',
            _this = $(this),
            _curl = ovic_shop_ajax.woo_shop_link;

        if ( _this.closest('form').length > 0 ) {
            _form = _this.closest('form').ShopAjaxSerializeObject();
            if ( _this.hasClass('display-mode') && _this.attr('name') !== undefined ) {
                _form[ _this.attr('name') ] = _this.val();
            } else if ( _this.hasClass('ordering-item') ) {
                _form.orderby = _this.val();
            } else {
                _form = _this.closest('form').ShopAjaxSerializeObject();
            }
            if ( _this.closest('form').attr('action') !== undefined && _this.closest('form').attr('action') !== '' ) {
                _curl = _this.closest('form').attr('action');
            }
            _data = $.param(_form);
            _url  = _curl + (_curl.split('?')[ 1 ] ? '&' : '?') + _data;
        } else {
            _url = _this.attr('href');
        }
        if ( _this.hasClass('reset') ) {
            _url = _curl;
        }

        raw_content_page({
            url: _url,
            push: true
        });
    };

    var _class_supports = '.widget_product_tag_cloud a,' +
        '.widget_product_categories a,' +
        '.widget_layered_nav a,' +
        '.grid-view-mode .display-mode,' +
        '.widget_price_filter button,' +
        '.widget-ovic-price-filter button,' +
        '.ovic-product-price-filter button,' +
        '.widget-ovic-catalog-ordering button,' +
        '.ovic-catalog-ordering button,' +
        '.widget_layered_nav_filters a,' +
        '.woocommerce-pagination a,' +
        '.ovic-shop-filter a';

    $(document).on('click', _class_supports, function (event) {
        event.preventDefault();
        $(this).before_action_request();
    });
    $(document).on('submit', '.woocommerce-ordering, .per-page-form, .woocommerce-widget-layered-nav-dropdown', function (event) {
        event.preventDefault();
        $(this).before_action_request();
    });

    window.addEventListener('popstate', function (event) {
        if ( event.state && $('body').hasClass('woocommerce woocommerce-page') ) {
            document.title = event.state.title;
            raw_content_page({
                url: event.state.path,
                push: false
            });
        }
    }, false);
    /* AJAX SHOP */
    window.addEventListener('load',
        function (ev) {
            if ( $('body').hasClass('woocommerce woocommerce-page') ) {
                history.pushState({path: window.location.href, title: document.title}, null, window.location.href);
            }
        }, false);
})(window.jQuery);