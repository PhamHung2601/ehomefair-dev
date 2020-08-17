(function ($) {
    "use strict";

    /* Category */
    $.fn.ovic_vertical_menu = function () {
        /* SHOW ALL ITEM */
        var _countLi      = 0,
            _verticalMenu = $(this).find('.vertical-menu'),
            _blockNav     = $(this).closest('.block-nav-category'),
            _blockTitle   = $(this).find('.block-title');

        $(this).each(function () {
            var _dataItem = $(this).data('items') - 1;
            _countLi      = $(this).find('.vertical-menu>li').length;

            if (_countLi > (_dataItem + 1)) {
                $(this).addClass('show-button-all');
            }
            $(this).find('.vertical-menu>li').each(function (i) {
                _countLi = _countLi + 1;
                if (i > _dataItem) {
                    $(this).addClass('link-other');
                }
            });
        });
        /* VERTICAL MENU ITEM */
        if (_verticalMenu.length > 0) {
            $(document).on('click', '.open-cate', function (e) {
                _blockNav.find('li.link-other').each(function () {
                    $(this).slideDown();
                });
                $(this).addClass('close-cate').removeClass('open-cate').html($(this).data('closetext'));
                e.preventDefault();
            });
            $(document).on('click', '.close-cate', function (e) {
                _blockNav.find('li.link-other').each(function () {
                    $(this).slideUp();
                });
                $(this).addClass('open-cate').removeClass('close-cate').html($(this).data('alltext'));
                e.preventDefault();
            });

            _blockTitle.on('click', function () {
                $(this).toggleClass('active');
                $(this).parent().toggleClass('has-open');
                $('body').toggleClass('category-open');
            });
        }
    };
    /* Animate */
    $.fn.ovic_animation_tabs = function (_tab_animated) {
        _tab_animated = (_tab_animated === undefined || _tab_animated === '') ? '' : _tab_animated;
        if (_tab_animated !== '') {
            $(this).find('.owl-slick .slick-active, .product-list-grid .product-item').each(function (i) {
                var _this  = $(this),
                    _style = _this.attr('style'),
                    _delay = i * 200;

                _style = (_style === undefined) ? '' : _style;
                _this.attr('style', _style +
                    ';-webkit-animation-delay:' + _delay + 'ms;' +
                    '-moz-animation-delay:' + _delay + 'ms;' +
                    '-o-animation-delay:' + _delay + 'ms;' +
                    'animation-delay:' + _delay + 'ms;'
                ).addClass(_tab_animated + ' animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function () {
                    _this.removeClass(_tab_animated + ' animated');
                    _this.attr('style', _style);
                });
            });
        }
    };
    $.fn.ovic_init_carousel  = function () {
        $(this).not('.slick-initialized').each(function () {
            var _this       = $(this),
                _responsive = _this.data('responsive'),
                _config     = [];

            if ($('body').hasClass('rtl')) {
                _config.rtl = true;
            }
            if (_this.hasClass('slick-vertical')) {
                _config.prevArrow = '<span class="fa fa-angle-up prev"></span>';
                _config.nextArrow = '<span class="fa fa-angle-down next"></span>';
            } else {
                _config.prevArrow = '<span class="fa fa-angle-left prev"></span>';
                _config.nextArrow = '<span class="fa fa-angle-right next"></span>';
            }
            if (_responsive !== "" || _responsive !== undefined)
                _config.responsive = _responsive;

            _this.on('init', function () {
                _this.trigger('ovic_trigger_init_slide');
            });
            /* SLICK INSTALL */
            _this.slick(_config);
            /* SLICK EVENT */
            _this.on('beforeChange', function () {
                _this.trigger('ovic_trigger_before_change_slide');
            });
            _this.on('afterChange', function () {
                _this.find('.lazy').ovic_init_lazy_load();
                _this.trigger('ovic_trigger_after_change_slide');
            });
            _this.on('setPosition', function () {
                _this.trigger('ovic_trigger_setPosition_slide');
            });
        });
    };
    $.fn.ovic_product_thumb  = function () {
        $(this).not('.slick-initialized').each(function () {
            var _this       = $(this),
                _responsive = ovic_ajax_frontend.data_responsive !== "" ? JSON.parse(ovic_ajax_frontend.data_responsive) : "",
                _config     = ovic_ajax_frontend.data_slick !== "" ? JSON.parse(ovic_ajax_frontend.data_slick) : "";

            if (_config !== "") {
                if ($('body').hasClass('rtl')) {
                    _config.rtl = true;
                }
                _config.prevArrow = '<span class="fa fa-angle-left prev"></span>';
                _config.nextArrow = '<span class="fa fa-angle-right next"></span>';
                if (_responsive !== "" || _responsive !== undefined)
                    _config.responsive = _responsive;

                setTimeout(function () {
                    _this.slick(_config);
                }, 10, _this, _config);
            }
        });
    };
    $.fn.ovic_init_lazy_load = function () {
        $(this).each(function () {
            var _this   = $(this),
                _config = [];

            _config.beforeLoad     = function (element) {
                if (element.is('div') === true) {
                    element.addClass('loading-lazy');
                } else {
                    element.parent().addClass('loading-lazy');
                }
            };
            _config.afterLoad      = function (element) {
                if (element.is('div') === true) {
                    element.removeClass('loading-lazy');
                } else {
                    element.parent().removeClass('loading-lazy');
                }
            };
            _config.onFinishedAll  = function () {
                if (!this.config('autoDestroy'))
                    this.destroy();
            }
            _config.effect         = "fadeIn";
            _config.enableThrottle = true;
            _config.throttle       = 250;
            _config.effectTime     = 600;
            if (_this.closest('.ovic-menu-clone-wrap').find('.ovic-menu-panel').length) {
                _config.appendScroll = _this.closest('.ovic-menu-clone-wrap').find('.ovic-menu-panel');
            }
            _this.lazy(_config);
        });
    };
    /* Add To Cart Button */
    $.fn.ovic_alert_variable_product = function () {
        var _this = $(this);
        _this.on('ovic_alert_variable_product', function () {
            if ($(this).hasClass('disabled')) {
                $(this).popover({
                    content  : 'Plz Select option before Add To Cart.',
                    trigger  : 'hover',
                    placement: 'bottom'
                });
            } else {
                $(this).popover('destroy');
            }
        }).trigger('ovic_alert_variable_product');
        $(document).on('change', function () {
            _this.trigger('ovic_alert_variable_product');
        });
    };
    /* ovic_init_dropdown */
    $(document).on('click', function (event) {
        var _target = $(event.target).closest('.ovic-dropdown'),
            _parent = $('.ovic-dropdown');

        if (_target.length > 0) {
            _parent.not(_target).removeClass('open');
            if (
                $(event.target).is('[data-ovic="ovic-dropdown"]') ||
                $(event.target).closest('[data-ovic="ovic-dropdown"]').length > 0
            ) {
                _target.toggleClass('open');
                event.preventDefault();
            }
        } else {
            $('.ovic-dropdown').removeClass('open');
        }
    });
    /* ovic_better_equal_elems */
    $.fn.ovic_better_equal_elems = function () {
        var _this = $(this);
        _this.on('ovic_better_equal_elems', function () {
            setTimeout(function () {
                _this.each(function () {
                    if ($(this).find('.equal-elem').length) {
                        $(this).find('.equal-elem').css({
                            'height': 'auto'
                        });
                        var _height = 0;
                        $(this).find('.equal-elem').each(function () {
                            if (_height < $(this).height()) {
                                _height = $(this).height();
                            }
                        });
                        $(this).find('.equal-elem').height(_height);
                    }
                });
            }, 100);
        }).trigger('ovic_better_equal_elems');
        $(window).on('resize', function () {
            _this.trigger('ovic_better_equal_elems');
        });
    };
    $.fn.ovic_gallery_images     = function () {
        var _this = $(this);
        _this.on('ovic_gallery_images', function () {
            _this.each(function () {
                $(this).magnificPopup({
                    type     : 'image',
                    mainClass: 'mfp-with-zoom', // this class is for CSS animation below
                    delegate : 'a', // the selector for gallery item
                    gallery  : {
                        enabled: true
                    },
                    zoom     : {
                        enabled : true, // By default it's false, so don't forget to enable it
                        duration: 300, // duration of the effect, in milliseconds
                        easing  : 'ease-in-out', // CSS transition easing function
                        opener  : function (openerElement) {
                            return openerElement.is('img') ? openerElement : openerElement.find('img');
                        }
                    }
                });
            });
        }).trigger('ovic_gallery_images');
    };

    function ovic_setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires     = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + "; " + expires;
    }

    /* Ovic Ajax Tabs */
    $(document).on('click', '.ovic-tabs .tab-link a, .ovic-accordion .panel-heading a', function (e) {
        e.preventDefault();
        var _this   = $(this),
            _data   = _this.data(),
            _tabID  = _this.attr('href'),
            _loaded = _this.closest('.tab-link,.ovic-accordion').find('a.loaded').attr('href');

        if (_data.ajax == 1 && !_this.hasClass('loaded')) {
            $(_tabID).closest('.tab-container,.ovic-accordion').addClass('loading');
            _this.parent().addClass('active').siblings().removeClass('active');
            $.ajax({
                type     : 'POST',
                url      : ovic_ajax_frontend.ovic_ajax_url.toString().replace('%%endpoint%%', 'ovic_get_tabs_shortcode'),
                data     : {
                    security  : ovic_ajax_frontend.security,
                    id        : _data.id,
                    section_id: _data.section,
                },
                success  : function (response) {
                    if (response.success == 'ok') {
                        $(_tabID).html($(response.html).find('.vc_tta-panel-body').html());
                        $('[href="' + _loaded + '"]').removeClass('loaded');
                        if ($(_tabID).find('.lazy').length > 0) {
                            $(_tabID).find('.lazy').lazy({
                                delay: 0
                            });
                        }
                        if ($(_tabID).find('.owl-slick').length > 0) {
                            $(_tabID).find('.owl-slick').ovic_init_carousel();
                        }
                        if ($(_tabID).find('.equal-container.better-height').length > 0) {
                            $(_tabID).find('.equal-container.better-height').ovic_better_equal_elems();
                        }
                        $(_tabID).trigger('ovic_ajax_tabs_complete');
                        _this.addClass('loaded');
                    } else {
                        $(_tabID).closest('.tab-container,.ovic-accordion').removeClass('loading');
                        $(_tabID).html('<strong>Error: Can not Load Data ...</strong>');
                    }
                    /* for accordion */
                    _this.closest('.panel-default').addClass('active').siblings().removeClass('active');
                    _this.closest('.ovic-accordion').find(_tabID).slideDown(400);
                    _this.closest('.ovic-accordion').find('.panel-collapse').not(_tabID).slideUp(400);
                },
                complete : function () {
                    setTimeout(function (_tabID, _tab_animated, _loaded) {
                        $(_tabID).closest('.tab-container,.ovic-accordion').removeClass('loading');
                        $(_tabID).addClass('active').siblings().removeClass('active');
                        $(_tabID).ovic_animation_tabs(_tab_animated);
                        $(_loaded).html('');
                    }, 10, _tabID, _data.animate, _loaded);
                },
                ajaxError: function () {
                    $(_tabID).closest('.tab-container,.ovic-accordion').removeClass('loading');
                    $(_tabID).html('<strong>Error: Can not Load Data ...</strong>');
                }
            });
        } else {
            _this.parent().addClass('active').siblings().removeClass('active');
            $(_tabID).addClass('active').siblings().removeClass('active');
            /* for accordion */
            _this.closest('.panel-default').addClass('active').siblings().removeClass('active');
            _this.closest('.ovic-accordion').find(_tabID).slideDown(400);
            _this.closest('.ovic-accordion').find('.panel-collapse').not(_tabID).slideUp(400);
            /* for animate */
            $(_tabID).ovic_animation_tabs(_data.animate);
        }
    });
    /* ADD TO CART */
    var serializeObject = function (form) {
        var o = {};
        var a = form.serializeArray();
        $.each(a, function () {
            if (o[this.name]) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };
    $(document).on('submit', '.product:not(.product-type-external) form.cart', function (e) {

        var form        = $(this),
            data        = serializeObject(form),
            $thisbutton = form.find('.single_add_to_cart_button');

        if (!$thisbutton.hasClass('disabled') && ovic_ajax_frontend.single_add_to_cart == 1) {

            if ($thisbutton.val()) {
                data.product_id = $thisbutton.val();
            }

            $thisbutton.addClass('loading');

            // Trigger event.
            $(document.body).trigger('adding_to_cart', [$thisbutton, data]);

            // Ajax action.
            $.post(ovic_ajax_frontend.ovic_ajax_url.toString().replace('%%endpoint%%', 'ovic_add_to_cart_single'), data, function (response) {

                $thisbutton.removeClass('loading');

                if (!response) {
                    return;
                }

                // Redirect to cart option
                if (wc_add_to_cart_params.cart_redirect_after_add === 'yes') {
                    window.location = wc_add_to_cart_params.cart_url;
                    return;
                }

                // Trigger event so themes can refresh other areas.
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);

            });
            e.preventDefault();

        }
    });

    /* NOIFICATIONS -- START */

    $.fn.ovic_add_notify = function ($text_content) {
        var config    = [],
            $img_html = '',
            $this     = $(this),
            $img      = $this.closest('.product-item').find('img.wp-post-image'),
            pName     = $this.attr('aria-label');

        config.duration = ovic_ajax_frontend.growl_duration;
        config.title    = ovic_ajax_frontend.growl_notice_text;

        $this.removeClass('loading');

        // if from wishlist
        if (!$img.length) {
            $img = $this.closest('tr').find('.product-thumbnail img');
        }
        // if from single product page
        if (!$img.length) {
            $img = $this.closest('.product').find('.woocommerce-product-gallery__wrapper img');
        }
        if (typeof pName === 'undefined' || pName === '') {
            pName = $this.closest('.product').find('.summary .product_title').text().trim();
        }
        // if from mini cart
        if ($this.closest('.mini_cart_item').length) {
            $img  = $this.closest('.mini_cart_item').find('a > img');
            pName = $this.closest('.mini_cart_item').find('a:not(.remove)').clone().children().remove().end().text();
        }

        // reset state after 5 sec
        setTimeout(function () {
            $this.removeClass('added').removeClass('recent-added');
            $this.next('.added_to_cart').remove();
        }, 3000, $this);

        if (typeof pName === 'undefined' || pName === '') {
            pName = $this.closest('.product-item').find('.product_title a').text().trim();
        }

        if (typeof pName !== 'undefined' && pName !== '') {
            var string_start = pName.indexOf("“") + 1,
                string_end   = pName.indexOf("”"),
                pName        = string_start > 1 ? pName.slice(string_start, string_end) : pName;

            pName = '<span>' + pName + '</span>';
        } else {
            pName = '';
        }

        if ($img.length) {
            $img_html = '<figure><img src="' + $img.attr('src') + '"' + ' alt="' + pName + '" class="growl-thumb" /></figure>';
        }

        config.message = $img_html + '<p class="growl-content">' + pName + '' + $text_content + '</p>';

        $.growl.notice(config);
    };

    $(document.body).on('removed_from_cart', function (event, fragments, cart_hash, $button) {

        $button.ovic_add_notify(
            ovic_ajax_frontend.removed_cart_text
        );

    });

    $(document).on('added_to_cart', function (event, fragments, cart_hash, $button) {

        $button.ovic_add_notify(
            ovic_ajax_frontend.added_to_cart_text + '</br>' +
            '<a href="' + ovic_ajax_frontend.wc_cart_url + '">' +
            ovic_ajax_frontend.view_cart_notification_text + '</a>'
        );

    });

    $(document).on('added_to_wishlist', function (event, $button) {

        var html    = '',
            message = ovic_ajax_frontend.added_to_wishlist_text;

        if ($button.hasClass('delete_item')) {
            message = ovic_ajax_frontend.removed_from_wishlist_text;
        }

        html += message + '</br>';
        html += '<a href="' + ovic_ajax_frontend.wishlist_url + '">';
        if (!$button.hasClass('delete_item')) {
            html += ovic_ajax_frontend.browse_wishlist_text;
        }
        html += '</a>';

        $button.ovic_add_notify(html);

        $button.removeClass('loading');

    });

    $(document).on('click', function (event) {
        var _target = $(event.target).closest('#growls-default'),
            _parent = $('#growls-default');

        if (!_target.length) {
            $('.growl-close').trigger('click');
        }
    });

    /* NOIFICATIONS -- END */

    $(document).on('change', '.per-page-form .option-perpage', function () {
        $(this).closest('form').submit();
    });
    $(document).on('vc-full-width-row', function (event) {
        if ($(event.target).find('[data-vc-full-width="true"]').length) {
            var $elements = $(event.target).find('[data-vc-full-width="true"]');
            $.each($elements, function () {
                $(this).css('padding-left', $(this).css('padding-right'));
            });
        }
    });
    $(document).on('qv_loader_stop', function (event) {
        if ($(event.target).find('.flex-control-thumbs').length > 0) {
            $(event.target).find('.flex-control-thumbs').ovic_product_thumb();
        }
    });
    $(document).on('wc-product-gallery-after-init', function (event, target) {
        if ($(target).find('.flex-control-thumbs').length) {
            $(target).find('.flex-control-thumbs').ovic_product_thumb();
        }
    });
    $(document).ajaxComplete(function (event, xhr) {
        if (xhr.status == 200 && xhr.responseText && event.target) {
            if ($(event.target).find('.lazy').length > 0) {
                $(event.target).find('.lazy').ovic_init_lazy_load();
            }
        }
    });
    window.addEventListener("load", function load() {
        /**
         * remove listener, no longer needed
         * */
        window.removeEventListener("load", load, false);
        /**
         * start functions
         * */
        if ($('.owl-slick').length > 0) {
            $('.owl-slick').ovic_init_carousel();
        }
        if ($('.lazy').length > 0) {
            $('.lazy').ovic_init_lazy_load();
        }
        if ($('.category-search-option').length > 0) {
            $('.category-search-option').chosen();
        }
        if ($('.single_add_to_cart_button').length > 0) {
            $('.single_add_to_cart_button').ovic_alert_variable_product();
        }
        if ($('.block-nav-category').length > 0) {
            $('.block-nav-category').ovic_vertical_menu();
        }
        if ($('.ovic-gallery-image').length > 0) {
            $('.ovic-gallery-image').ovic_gallery_images();
        }
        if ($('.equal-container.better-height').length > 0) {
            $('.equal-container.better-height').ovic_better_equal_elems();
        }
    }, false);
})(window.jQuery);