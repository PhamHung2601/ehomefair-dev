;(function ($) {
    "use strict";

    var MARKETPLACE_FRAMEWORK = {
        init: function () {
            this.marketplace_countdown();
            this.marketplace_woo_quantily();
            this.marketplace_category_product();
            this.marketplace_click_action();
            this.marketplace_hover_product_item();
            this.marketplace_sticky_header();
            this.marketplace_mobile_footer();
        },
        onResize: function () {
            this.marketplace_sticky_header();
            this.marketplace_mobile_footer();
            this.marketplace_hover_product_item();
        },
        onScroll: function () {
            if ( $(window).scrollTop() > 200 ) {
                $('.backtotop').addClass('active');
            } else {
                $('.backtotop').removeClass('active');
            }
        },
        marketplace_mobile_footer: function () {
            if ( $(window).innerWidth() < 768 ) {
                var lastScrollTop = 0;
                var countItem     = $('.mobile-footer-inner>div:visible').length;
                $('.mobile-footer-inner>div:visible').css('width', 100 / countItem + '%');
                $(window).scroll(function (event) {
                    var st = $(this).scrollTop();
                    if ( st > lastScrollTop ) {
                        if ( $(window).scrollTop() + $(window).height() + 60 >= $(document).height() ) {
                            $('.mobile-footer').addClass('is-sticky');
                        } else {
                            $('.mobile-footer').removeClass('is-sticky');
                        }
                    } else {
                        $('.mobile-footer').addClass('is-sticky');
                    }
                    lastScrollTop = st;
                });
            }
        },
        marketplace_category_product: function () {
            if ( $('.widget_product_categories .product-categories').length > 0 ) {
                $('.widget_product_categories .product-categories').each(function () {
                    var _main = $(this);
                    _main.find('.cat-parent').each(function () {
                        if ( $(this).hasClass('current-cat-parent') ) {
                            $(this).addClass('show-sub');
                            $(this).children('.children').slideDown(400);
                        }
                        $(this).children('.children').before('<span class="carets"></span>');
                    });
                    _main.children('.cat-parent').each(function () {
                        var curent = $(this).find('.children');
                        $(this).children('.carets').on('click', function () {
                            $(this).parent().toggleClass('show-sub');
                            $(this).parent().children('.children').slideToggle(400);
                            _main.find('.children').not(curent).slideUp(400);
                            _main.find('.cat-parent').not($(this).parent()).removeClass('show-sub');
                        });
                        var next_curent = $(this).find('.children');
                        next_curent.children('.cat-parent').each(function () {
                            var child_curent = $(this).find('.children');
                            $(this).children('.carets').on('click', function () {
                                $(this).parent().toggleClass('show-sub');
                                $(this).parent().parent().find('.cat-parent').not($(this).parent()).removeClass('show-sub');
                                $(this).parent().parent().find('.children').not(child_curent).slideUp(400);
                                $(this).parent().children('.children').slideToggle(400);
                            })
                        });
                    });
                });
            }
        },
        marketplace_woo_quantily: function () {
            $('body').on('click', '.quantity .quantity-plus', function (e) {
                var _this  = $(this).closest('.quantity').find('input.qty'),
                    _value = parseInt(_this.val()),
                    _max   = parseInt(_this.attr('max')),
                    _step  = parseInt(_this.data('step')),
                    _value = _value + _step;
                if ( _max && _value > _max ) {
                    _value = _max;
                }
                _this.val(_value);
                _this.trigger("change");
                e.preventDefault();
            });
            $(document).on('change', function () {
                $('.quantity').each(function () {
                    var _this  = $(this).find('input.qty'),
                        _value = _this.val(),
                        _max   = parseInt(_this.attr('max'));
                    if ( _value > _max ) {
                        $(this).find('.quantity-plus').css('pointer-events', 'none')
                    } else {
                        $(this).find('.quantity-plus').css('pointer-events', 'auto')
                    }
                })
            });
            $('body').on('click', '.quantity .quantity-minus', function (e) {
                var _this  = $(this).closest('.quantity').find('input.qty'),
                    _value = parseInt(_this.val()),
                    _min   = parseInt(_this.attr('min')),
                    _step  = parseInt(_this.data('step')),
                    _value = _value - _step;
                if ( _min && _value < _min ) {
                    _value = _min;
                }
                if ( !_min && _value < 0 ) {
                    _value = 0;
                }
                _this.val(_value);
                _this.trigger("change");
                e.preventDefault();
            });
        },
        marketplace_countdown: function () {
            if ( $('.ovic-countdown').length > 0 ) {
                $('.ovic-countdown').each(function () {
                    var _this           = $(this),
                        _text_countdown = '';

                    _this.countdown(_this.data('datetime'), function (event) {
                        _text_countdown = event.strftime(
                            '<span class="days"><span class="number">%D</span><span class="text">Days</span></span>' +
                            '<span class="hour"><span class="number">%H</span><span class="text">Hrs</span></span>' +
                            '<span class="mins"><span class="number">%M</span><span class="text">Mins</span></span>' +
                            '<span class="secs"><span class="number">%S</span><span class="text">Secs</span></span>'
                        );
                        _this.html(_text_countdown);
                    });
                });
            }
        },
        marketplace_sticky_header: function () {
            if ( marketplace_global_frontend.ovic_sticky_menu == 1 && $('.header .header-nav').length > 0 ) {
                var _head           = $('.header-sticky'),
                    _verticalHeight = 0;
                if ( $('.header .verticalmenu-content').length > 0 ) {
                    var _vertical       = $('.header .verticalmenu-content'),
                        _verticalOffset = _vertical.offset(),
                        _verticalHeight = _vertical.height() + _verticalOffset.top;
                    if ( !_vertical.parent().hasClass('always-open') ) {
                        _verticalHeight = 0;
                    }
                }
                if ( $(window).innerWidth() > 1024 ) {
                    $(document).on('scroll', function (ev) {
                        if ( $(window).scrollTop() > _verticalHeight + 300 ) {
                            _head.addClass('is-sticky');
                        } else {
                            _head.removeClass('is-sticky');
                            _head.find('.block-nav-category').removeClass('has-open');
                        }
                    });
                }
            }
        },
        marketplace_click_action: function () {
            $(document).on('click', 'a.backtotop', function (e) {
                $('html, body').animate({scrollTop: 0}, 800);
                e.preventDefault();
            });
        },
        marketplace_hover_product_item: function () {
            var _winw = $(window).innerWidth();
            if ( _winw > 1024 ) {
                $('.product-list-owl .product-item.style-1, .product-grid .owl-products .product-item.style-1').on({
                    mouseenter: function () {
                        $(this).closest('.slick-list').css({
                            'padding': '10px 10px 100px',
                            'margin': '-10px -10px -100px',
                        });
                    },
                    mouseleave: function () {
                        $(this).closest('.slick-list').css({
                            'padding': '0',
                            'margin': '0',
                        });
                    }
                });
            }
        },
    };
    $(document).on('added_to_wishlist removed_from_wishlist', function () {
        $.get(marketplace_ajax_frontend.ajaxurl, {action: 'marketplace_update_wishlist_count'}, function (count) {
            if ( !count ) {
                count = 0;
            }
            $('.woo-wishlist-link .count').text(count);
        });
    });
    $(window).on('resize', function () {
        MARKETPLACE_FRAMEWORK.onResize();
    });
    $(document).scroll(function () {
        MARKETPLACE_FRAMEWORK.onScroll();
    });
    $(document).ready(function () {
        if ( $('body').hasClass('rtl') ) {
            $('.chosen-container').each(function () {
                $(this).addClass('chosen-rtl');
            });
        }
    });
    $(document).on('ovic_trigger_init_slide', function () {
        MARKETPLACE_FRAMEWORK.marketplace_countdown();
        MARKETPLACE_FRAMEWORK.marketplace_hover_product_item();
    });
    $(document).on('ovic_trigger_after_change_slide', function () {
        MARKETPLACE_FRAMEWORK.marketplace_hover_product_item();
    });
    $(document).on('click', '.ovic-tabs .tab-link a', function () {
        $(this).closest('.tab-head').removeClass('open');
    });
    window.addEventListener('load',
        function (ev) {
            MARKETPLACE_FRAMEWORK.init();
        }, false);

})(jQuery, window, document);