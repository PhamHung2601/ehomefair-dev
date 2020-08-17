(function ($) {
    "use strict";

    $.fn.OvicRenderPin = function () {
        var $map        = $(this),
            $pin        = $map.find('.ovic-pin'),
            $img_width  = $map.data('width'),
            $img_height = $map.data('height');

        $pin.each(function () {
            var $pin_top  = $(this).data('top'),
                $pin_left = $(this).data('left');

            if ( $pin_top.substr && '%' != $pin_top.substr(-1) ) {
                $pin_top = (($pin_top / $img_height) * 100) + 'px';
            }

            if ( $pin_left.substr && '%' != $pin_left.substr(-1) ) {
                $pin_left = (($pin_left / $img_width) * 100) + 'px';
            }

            $(this).css({
                "top": $pin_top,
                "left": $pin_left,
            })
        });
    }

    $.fn.OvicInitPopup = function () {
        var pin = $(this).find('.ovic-pin .action-pin');

        pin.on('click', function () {

            var _this = $(this),
                popup = _this.siblings('.ovic-popup');

            if ( !popup.length ) {
                return;
            }

            var parent = _this.closest('.ovic-pin');

            if ( parent.hasClass('actived') ) {
                parent.removeClass('actived');

                setTimeout(function () {
                    popup.removeAttr('style');
                }, 300);
                return;
            }

            var position = parent.data('position');

            // Reset style
            popup.css({
                'transition': 'none',
                'width': '',
                'left': ''
            });
            setTimeout(function () {
                popup.css({
                    'transition': ''
                });
            });
            popup.removeClass('remove-redirect right left top bottom');

            // Add class for position setting
            popup.addClass(position);

            var _this_info    = _this[ 0 ].getBoundingClientRect(),
                popup_info    = popup[ 0 ].getBoundingClientRect(),
                popup_width   = popup.width(),
                popup_height  = popup.height(),
                browser_width = $(window).width(),
                flag_width    = false;
            if ( popup_width > browser_width ) {
                popup.removeClass('right left top').addClass('bottom');
                popup.width(browser_width);
                flag_width = true;
            } else {
                switch ( position ) {
                    case 'right':
                        var offset_right = browser_width - (_this_info.right + popup_width + 8);

                        if ( offset_right < 0 ) {
                            if ( popup_width > _this_info.right ) {
                                popup.removeClass('right').addClass('bottom');
                                flag_width = false;
                            } else {
                                popup.removeClass('right').addClass('left');
                            }
                        }
                        break;
                    case 'left':
                        var offset_left = _this_info.left - popup_width + 8;
                        if ( offset_left < 0 ) {
                            if ( popup_width > _this_info.right ) {
                                popup.removeClass('left').addClass('bottom');
                                flag_width = false;
                            } else {
                                popup.removeClass('left').addClass('right');
                            }
                        }
                        break;
                    case 'top':
                        var offset_top_popup = parseInt(parent.css('top'));
                        if ( popup_height > offset_top_popup ) {
                            popup.removeClass('top').addClass('bottom');
                        }
                        break;
                    case 'bottom':
                        var offset_bottom_popup = parseInt(parent.css('bottom'));
                        if ( popup_height > offset_bottom_popup ) {
                            popup.removeClass('bottom').addClass('top');
                        }
                        break;
                }
            }

            if ( popup.hasClass('top') || popup.hasClass('bottom') ) {
                popup.css('left', 0);

                var offset_popup = popup.offset();

                if ( offset_popup.left <= 0 ) {
                    popup.css({
                        left: -offset_popup.left
                    });
                    popup.addClass('remove-redirect');
                } else {
                    if ( flag_width ) {
                        var right_position = offset_popup.left + browser_width;
                    } else {
                        var right_position = offset_popup.left + popup_width;
                    }

                    if ( right_position > browser_width ) {
                        var left_position = browser_width - right_position;
                        popup.css({
                            left: left_position
                        });
                        popup.addClass('remove-redirect');
                    } else {
                        popup.css('left', '');
                    }
                }
            }

            $('.content-text').css({
                'max-height': popup_width - 80 + 'px',
                'overflow': 'auto'
            });
            $('.ovic-mapper .ovic-pin .ovic-popup-header h2').css('max-width', popup_width - 10);

            // Set height content for image type
            if ( popup.hasClass('ovic-image') ) {
                var popup_header_height = popup.find('.ovic-popup-header').outerHeight(true);
                popup.find('.ovic-popup-main').css('height', (popup_height - popup_header_height));
            }

            // Add Actived class
            setTimeout(function () {
                // Remove all pin actived class
                $('.ovic-mapper .ovic-pin.actived').removeClass('actived');

                // Active pin current
                parent.addClass('actived');
            }, 300);
        });

        $('.ovic-pin .close-modal').on('click', function () {
            var parent = $(this).closest('.ovic-pin'),
                popup  = parent.find('.ovic-popup');

            parent.removeClass('actived');

            setTimeout(function () {
                popup.removeAttr('style');
            }, 300);
        });

        var filter_blur = 'blur(2px)',
            filter_gray = 'grayscale(100%)',
            flag        = false;

        pin.hover(function () {
            var _this = $(this);

            flag = true;
            _this.closest('.blur').children('img').css('filter', filter_blur).css('webkitFilter', filter_blur).css('mozFilter', filter_blur).css('oFilter', filter_blur).css('msFilter', filter_blur);

            _this.closest('.gray').children('img').css('filter', filter_gray).css('webkitFilter', filter_gray).css('mozFilter', filter_gray).css('oFilter', filter_gray).css('msFilter', filter_gray);

            _this.closest('.mask').children('.mask').css('opacity', 1);
        }, function () {
            var _this = $(this);

            flag = false;
            _this.closest('.ovic-mapper').children('img').removeAttr('style');
            _this.closest('.mask').children('.mask').removeAttr('style');
        });
    }

    window.addEventListener('load',
        function (ev) {

            if ( $('.ovic-mapper').length ) {
                $('.ovic-mapper').each(function () {
                    $(this).OvicRenderPin();
                    $(this).OvicInitPopup();
                });
            }

        }, false);

})(window.jQuery);