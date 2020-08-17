(function ($) {
    "use strict"; // Start of use strict

    var delay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();

    $(document).ready(function () {
        setTimeout(function () { // Set Time Out for event off

            $(document).on(' keyup', '.ovic-live-search-form .txt-livesearch', function (e) {
                var _this         = $(this);
                delay(function(){
                    var container     = _this.closest('.ovic-live-search-form'),
                        key_board     = e.keyCode,
                        list_products = container.find('.products-search .product-search'),
                        keyword       = _this.val(),
                        product_cat   = $(this).closest('.ovic-live-search-form').find('select[name="product_cat"]').val();

                    if ( typeof product_cat === "undefined" || product_cat == 0 ) {
                        product_cat = '';
                    }
                    if ( keyword.length < ovic_ajax_live_search.ovic_live_search_min_characters ) {
                        return false;
                    }
                    var data = {
                        action: 'ovic_live_search',
                        security: ovic_ajax_live_search.security,
                        keyword: keyword,
                        product_cat: product_cat
                    };
                    container.addClass('loading');
                    $.post(ovic_ajax_live_search.ajaxurl, data, function (response) {
                        container.removeClass('loading');
                        container.find('.suggestion-search-data').remove();
                        container.find('.not-results-search').remove();
                        container.find('.products-search').remove();

                        // Prepare response.
                        if ( response.message ) {
                            container.find('.results-search').append('<div class="not-results-search">' + response.message + '</div>');
                        } else {
                            container.find('.results-search').append('<div class="products-search"></div>');
                            // Show suggestion.
                            if ( response.suggestion ) {
                                container.find('.results-search').append('<div class="suggestion-search suggestion-search-data">' + response.suggestion + '</div>');
                            }

                            // Show results.
                            //container.find( '.products-search' ).append( '<div class="products-search-result-head">'+ovic_ajax_live_search.product_matches_text+'<span class="count">('+response.result_count+')'+ovic_ajax_live_search.results_text+'</span></div>' );
                            $.each(response.list_product, function (key, value) {
                                container.find('.products-search').append('<div class="product-search-item"><div class="product-image">' + value.image + '</div><div class="product-title-price"><div class="product-title"><a class="mask-link" href="' + value.url + '">' + value.title.replace(new RegExp('(' + keyword + ')', 'ig'), '<span class="keyword-current">$1</span>') + '</a></div><div class="product-price">' + value.price + '</div></div></div>');
                            });

                            container.find('.products-search').append('<div class="product-search view-all button">' + ovic_ajax_live_search.view_all_text + '</div>');
                        }
                    });
                }, 1000 );

            });

            $('body').on('click', '.ovic-live-search-form .view-all', function () {
                var _this  = $(this);
                var parent = _this.closest('.ovic-live-search-form ').submit();
            });

            $(document).click(function (event) {
                var container = $(event.target).closest(".ovic-live-search-form");
                if ( !container.length ) {
                    $('.ovic-live-search-form .products-search').stop().hide();
                    $('.ovic-live-search-form .not-results-search').stop().hide();
                }
                else{
                    $('.ovic-live-search-form .products-search').stop().show();
                    $('.ovic-live-search-form .not-results-search').stop().show();
                }
            });


        }, 1)


    });
})(jQuery); // End of use strict