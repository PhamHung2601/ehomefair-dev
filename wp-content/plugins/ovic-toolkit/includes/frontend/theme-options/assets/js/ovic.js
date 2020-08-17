/**
 *
 * -----------------------------------------------------------
 *
 * Kutethemes Customize
 * A Lightweight and easy-to-use WordPress Options Framework
 *
 * Copyright 2015 Codestar <info@codestarlive.com>
 *
 * -----------------------------------------------------------
 *
 */
;
(function($, window, document, undefined) {
    'use strict';

    // caching
    var OVIC = {};
    var $body = $('body');
    var has_rtl = $body.hasClass('rtl');

    OVIC.funcs = {};
    OVIC.vars = {};

    //
    // Helper Functions
    //
    OVIC.helper = {
        name_replace: function($selector) {

            $selector.find('.ovic-cloneable-item').each(function(index) {
                $(this).find(':input').each(function() {
                    this.name = this.name.replace(/\[(\d+)\]/, '[' + index + ']');
                });
            });

        },

        debounce: function(callback, threshold, immediate) {
            var timeout;
            return function() {
                var context = this,
                    args = arguments;
                var later = function() {
                    timeout = null;
                    if (!immediate) {
                        callback.apply(context, args);
                    }
                };
                var callNow = (immediate && !timeout);
                clearTimeout(timeout);
                timeout = setTimeout(later, threshold);
                if (callNow) {
                    callback.apply(context, args);
                }
            };
        }
    };

    //
    // Custom clone for textarea and select clone() bug
    //
    $.fn.ovic_clone = function() {

        var base = $.fn.clone.apply(this, arguments),
            clone = this.find('select').add(this.filter('select')),
            cloned = base.find('select').add(base.filter('select'));

        for (var i = 0; i < clone.length; ++i) {
            for (var j = 0; j < clone[i].options.length; ++j) {

                if (clone[i].options[j].selected === true) {
                    cloned[i].options[j].selected = true;
                }

            }
        }

        return base;

    };

    //
    // Navigation
    //
    $.fn.ovic_navigation = function() {
        return this.each(function() {

            var $nav = $(this),
                $parent = $nav.closest('.ovic'),
                $section = $parent.find('.ovic-section-id'),
                $expand = $parent.find('.ovic-expand-all'),
                $tabbed;

            $nav.find('ul:first a').on('click', function(e) {

                e.preventDefault();

                var $el = $(this),
                    $next = $el.next(),
                    $target = $el.data('section');

                if ($next.is('ul')) {

                    $el.closest('li').toggleClass('ovic-tab-active');

                } else {

                    $tabbed = $('#ovic-tab-' + $target);

                    $tabbed.removeClass('hidden').siblings().addClass('hidden');

                    $nav.find('a').removeClass('ovic-section-active');
                    $el.addClass('ovic-section-active');
                    $section.val($target);
                    if (!$tabbed.data('inited')) {
                        $tabbed.ovic_reload_script();
                    }
                }

            });

            $expand.on('click', function(e) {

                e.preventDefault();

                $parent.find('.ovic-wrapper').toggleClass('ovic-show-all');
                if (!$parent.find('.ovic-section').not('.ovic-onload').data('inited')) {
                    $parent.find('.ovic-section').not('.ovic-onload').ovic_reload_script();
                }
                $(this).find('.fa').toggleClass('fa-eye-slash').toggleClass('fa-eye');

            });

        });
    };

    //
    // Search
    //
    $.fn.ovic_search = function() {
        return this.each(function() {

            var $this = $(this),
                $input = $this.find('input');

            $input.on('change keyup', function() {

                var value = $(this).val(),
                    $wrapper = $('.ovic-wrapper'),
                    $section = $wrapper.find('.ovic-section'),
                    $fields = $section.find('> .ovic-field:not(.hidden)'),
                    $titles = $fields.find('> .ovic-title, .ovic-search-tags');

                if (value.length > 3) {

                    $fields.addClass('ovic-hidden');
                    $wrapper.addClass('ovic-search-all');

                    $titles.each(function() {

                        var $title = $(this);

                        if ($title.text().match(new RegExp('.*?' + value + '.*?', 'i'))) {

                            var $field = $title.closest('.ovic-field');

                            $field.removeClass('ovic-hidden');
                            $field.parent().ovic_reload_script();

                        }

                    });

                } else {

                    $fields.removeClass('ovic-hidden');
                    $wrapper.removeClass('ovic-search-all');

                }

            });

        });
    };

    //
    // Sticky Header
    //
    $.fn.ovic_sticky = function() {
        return this.each(function() {

            var $this = $(this),
                $window = $(window),
                $inner = $this.find('.ovic-header-inner'),
                padding = parseInt($inner.css('padding-left')) + parseInt($inner.css('padding-right')),
                offset = 32,
                scrollTop = 0,
                lastTop = 0,
                ticking = false,
                onSticky = function() {

                    scrollTop = $window.scrollTop();
                    requestTick();

                },
                requestTick = function() {

                    if (!ticking) {
                        requestAnimationFrame(function() {
                            stickyUpdate();
                            ticking = false;
                        });
                    }

                    ticking = true;

                },
                stickyUpdate = function() {

                    var offsetTop = $this.offset().top,
                        stickyTop = Math.max(offset, offsetTop - scrollTop),
                        winWidth = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);

                    if (stickyTop <= offset && winWidth > 782) {
                        $inner.css({
                            width: $this.outerWidth() - padding
                        });
                        $this.css({
                            height: $this.outerHeight()
                        }).addClass('ovic-sticky');
                    } else {
                        $inner.removeAttr('style');
                        $this.removeAttr('style').removeClass('ovic-sticky');
                    }

                };

            $window.on('scroll resize', onSticky);

            onSticky();

        });
    };

    //
    // Dependency System
    //
    $.fn.ovic_dependency = function(param) {
        return this.each(function() {

            var base = this,
                $this = $(this);

            base.init = function() {

                base.ruleset = $.deps.createRuleset();

                var cfg = {
                    show: function(el) {
                        el.removeClass('hidden');
                    },
                    hide: function(el) {
                        el.addClass('hidden');
                    },
                    log: false,
                    checkTargets: false
                };

                if (param !== undefined) {
                    base.depSub();
                } else {
                    base.depRoot();
                }

                $.deps.enable($this, base.ruleset, cfg);

            };

            base.depRoot = function() {

                $this.each(function() {

                    $(this).find('[data-controller]').each(function() {

                        var $this = $(this),
                            _controller = $this.data('controller').split('|'),
                            _condition = $this.data('condition').split('|'),
                            _value = $this.data('value').toString().split('|'),
                            _rules = base.ruleset;

                        $.each(_controller, function(index, element) {

                            var value = _value[index] || '',
                                condition = _condition[index] || _condition[0];

                            _rules = _rules.createRule('[data-depend-id="' + element + '"]', condition, value);
                            _rules.include($this);

                        });

                    });

                });

            };

            base.depSub = function() {

                $this.each(function() {

                    $(this).find('[data-sub-controller]').each(function() {

                        var $this = $(this),
                            _controller = $this.data('sub-controller').split('|'),
                            _condition = $this.data('sub-condition').split('|'),
                            _value = $this.data('sub-value').toString().split('|'),
                            _rules = base.ruleset;

                        $.each(_controller, function(index, element) {

                            var value = _value[index] || '',
                                condition = _condition[index] || _condition[0];

                            _rules = _rules.createRule('[data-sub-depend-id="' + element + '"]', condition, value);
                            _rules.include($this);

                        });

                    });

                });

            };

            base.init();

        });
    };

    //
    // Chosen Script
    //
    $.fn.ovic_chosen = function() {
        return this.each(function() {

            $(this).chosen({
                allow_single_deselect: true,
                disable_search_threshold: 15,
                width: parseFloat($(this).actual('width') + 25) + 'px'
            });

        });
    };

    //
    // Field Image Selector
    //
    $.fn.ovic_field_image_selector = function() {
        return this.each(function() {

            $(this).find('label').on('click', function() {
                $(this).siblings().find('input').prop('checked', false);
            });

        });
    };

    //
    // Field Sorter
    //
    $.fn.ovic_field_sorter = function() {
        return this.each(function() {

            var $this = $(this),
                $enabled = $this.find('.ovic-enabled'),
                $has_disabled = $this.find('.ovic-disabled'),
                $disabled = ($has_disabled.length) ? $has_disabled : false;

            $enabled.sortable({
                connectWith: $disabled,
                placeholder: 'ui-sortable-placeholder',
                update: function(event, ui) {


                    var $el = ui.item.find('input');

                    if (ui.item.parent().hasClass('ovic-enabled')) {
                        $el.attr('name', $el.attr('name').replace('disabled', 'enabled'));
                    } else {
                        $el.attr('name', $el.attr('name').replace('enabled', 'disabled'));
                    }

                    $this.ovic_customizer_refresh();

                }
            });

            if ($disabled) {

                $disabled.sortable({
                    connectWith: $enabled,
                    placeholder: 'ui-sortable-placeholder'
                });

            }

        });
    };

    //
    // Field Upload
    //
    $.fn.ovic_field_upload = function() {
        return this.each(function() {

            var $this = $(this),
                $button = $this.find('.ovic-button'),
                $preview = $this.find('.ovic-image-preview'),
                $remove = $this.find('.ovic-image-remove'),
                $img = $this.find('img'),
                $input = $this.find('input'),
                extensions = ['jpg', 'gif', 'png', 'svg', 'jpeg'],
                wp_media_frame;

            $button.on('click', function(e) {

                e.preventDefault();

                if (typeof wp === 'undefined' || !wp.media || !wp.media.gallery) {
                    return;
                }

                if (wp_media_frame) {
                    wp_media_frame.open();
                    return;
                }

                wp_media_frame = wp.media({
                    title: $button.data('frame-title'),
                    library: {
                        type: $button.data('upload-type')
                    },
                    button: {
                        text: $button.data('insert-title'),
                    }
                });

                wp_media_frame.on('select', function() {

                    var attachment = wp_media_frame.state().get('selection').first();

                    $input.val(attachment.attributes.url).trigger('change');

                });

                wp_media_frame.open();

            });

            if ($preview.length) {

                $input.on('change keyup', function() {

                    var $this = $(this),
                        value = $this.val(),
                        ext = value.toLowerCase().slice((value.toLowerCase().lastIndexOf('.') - 1) + 2);

                    if ($.inArray(ext, extensions) > -1) {
                        $preview.removeClass('hidden');
                        $img.attr('src', value);
                    } else {
                        $preview.addClass('hidden');
                    }

                });

                $remove.on('click', function(e) {

                    e.preventDefault();
                    $input.val('').trigger('change');
                    $preview.addClass('hidden');

                });

            }

        });

    };

    //
    // Field Image
    //
    $.fn.ovic_field_image = function() {
        return this.each(function() {

            var $this = $(this),
                $button = $this.find('.ovic-button'),
                $preview = $this.find('.ovic-image-preview'),
                $remove = $this.find('.ovic-image-remove'),
                $input = $this.find('input'),
                $img = $this.find('img'),
                wp_media_frame;

            $button.on('click', function(e) {

                e.preventDefault();

                if (typeof wp === 'undefined' || !wp.media || !wp.media.gallery) {
                    return;
                }

                if (wp_media_frame) {
                    wp_media_frame.open();
                    return;
                }

                wp_media_frame = wp.media({
                    library: {
                        type: 'image'
                    }
                });

                wp_media_frame.on('select', function() {

                    var attachment = wp_media_frame.state().get('selection').first().attributes;
                    var thumbnail = (typeof attachment.sizes !== 'undefined' && typeof attachment.sizes.thumbnail !== 'undefined') ? attachment.sizes.thumbnail.url : attachment.url;

                    $preview.removeClass('hidden');
                    $img.attr('src', thumbnail);
                    $input.val(attachment.id).trigger('change');

                });

                wp_media_frame.open();

            });

            $remove.on('click', function(e) {
                e.preventDefault();
                $input.val('').trigger('change');
                $preview.addClass('hidden');
            });

        });

    };

    //
    // Field Gallery
    //
    $.fn.ovic_field_gallery = function() {
        return this.each(function() {

            var $this = $(this),
                $edit = $this.find('.ovic-edit-gallery'),
                $clear = $this.find('.ovic-clear-gallery'),
                $list = $this.find('ul'),
                $input = $this.find('input'),
                $img = $this.find('img'),
                wp_media_frame,
                wp_media_click;

            $this.on('click', '.ovic-button, .ovic-edit-gallery', function(e) {

                var $el = $(this),
                    what = ($el.hasClass('ovic-edit-gallery')) ? 'edit' : 'add',
                    state = (what === 'edit') ? 'gallery-edit' : 'gallery-library';

                e.preventDefault();

                if (typeof wp === 'undefined' || !wp.media || !wp.media.gallery) {
                    return;
                }

                if (wp_media_frame) {
                    wp_media_frame.open();
                    wp_media_frame.setState(state);
                    return;
                }

                wp_media_frame = wp.media({
                    library: {
                        type: 'image'
                    },
                    frame: 'post',
                    state: 'gallery',
                    multiple: true
                });

                wp_media_frame.on('open', function() {

                    var ids = $input.val();

                    if (ids) {

                        var get_array = ids.split(',');
                        var library = wp_media_frame.state('gallery-edit').get('library');

                        wp_media_frame.setState(state);

                        get_array.forEach(function(id) {
                            var attachment = wp.media.attachment(id);
                            library.add(attachment ? [attachment] : []);
                        });

                    }
                });

                wp_media_frame.on('update', function() {

                    var inner = '';
                    var ids = [];
                    var images = wp_media_frame.state().get('library');

                    images.each(function(attachment) {

                        var attributes = attachment.attributes;
                        var thumbnail = (typeof attributes.sizes.thumbnail !== 'undefined') ? attributes.sizes.thumbnail.url : attributes.url;

                        inner += '<li><img src="' + thumbnail + '"></li>';
                        ids.push(attributes.id);

                    });

                    $input.val(ids).trigger('change');
                    $list.html('').append(inner);
                    $clear.removeClass('hidden');
                    $edit.removeClass('hidden');

                });

                wp_media_frame.open();
                wp_media_click = what;

            });

            $clear.on('click', function(e) {
                e.preventDefault();
                $list.html('');
                $input.val('').trigger('change');
                $clear.addClass('hidden');
                $edit.addClass('hidden');
            });

        });

    };

    //
    // Field Group
    //
    $.fn.ovic_field_group = function() {
        return this.each(function() {

            var $this = $(this),
                $wrapper = $this.find('.ovic-cloneable-wrapper'),
                $data = $this.find('.ovic-cloneable-data'),
                $hidden = $this.find('.ovic-cloneable-hidden'),
                unique = $data.data('unique-id'),
                limit = parseInt($data.data('limit'));

            $wrapper.accordion({
                header: '.ovic-cloneable-title',
                collapsible: true,
                active: false,
                animate: false,
                heightStyle: 'content',
                icons: {
                    'header': 'ovic-cloneable-header-icon fa fa-angle-right',
                    'activeHeader': 'ovic-cloneable-header-icon fa fa-angle-down'
                },
                beforeActivate: function(event, ui) {

                    var $panel = ui.newPanel;

                    if ($panel.length && !$panel.data('opened')) {

                        $panel.find('.ovic-field').removeClass('ovic-no-script');
                        $panel.ovic_reload_script('sub');
                        $panel.data('opened', true);

                    }

                }
            });

            $wrapper.sortable({
                axis: 'y',
                handle: '.ovic-cloneable-title',
                helper: 'original',
                cursor: 'move',
                placeholder: 'widget-placeholder',
                start: function(event, ui) {

                    $wrapper.accordion({
                        active: false
                    });
                    $wrapper.sortable('refreshPositions');

                },
                stop: function(event, ui) {

                    OVIC.helper.name_replace($wrapper);
                    $wrapper.ovic_customizer_refresh();

                }
            });

            $this.on('click', '.ovic-cloneable-add', function(e) {

                e.preventDefault();

                var count = $wrapper.find('.ovic-cloneable-item').length;

                if (limit && (count + 1) > limit) {
                    $data.show();
                    return;
                }

                var $cloned_item = $hidden.ovic_clone().removeClass('ovic-cloneable-hidden');

                $cloned_item.find(':input').each(function() {
                    this.name = this.name.replace('_nonce', unique).replace('num', count);
                });

                $wrapper.append($cloned_item);
                $wrapper.accordion('refresh');
                $wrapper.accordion({
                    active: count
                });
                $wrapper.ovic_customizer_refresh();
                $wrapper.ovic_customizer_listen(true);

            });

            $wrapper.on('click', '.ovic-cloneable-clone', function(e) {

                e.preventDefault();

                if (limit && parseInt($wrapper.find('.ovic-cloneable-item').length + 1) > limit) {
                    $data.show();
                    return;
                }

                var $this = $(this),
                    $parent = $this.closest('.ovic-cloneable-item'),
                    $cloned = $parent.ovic_clone().addClass('ovic-cloned'),
                    $childs = $wrapper.children();

                $childs.eq($parent.index()).after($cloned);

                OVIC.helper.name_replace($wrapper);

                $wrapper.accordion('refresh');
                $wrapper.ovic_customizer_refresh();
                $wrapper.ovic_customizer_listen(true);

            });

            $wrapper.on('click', '.ovic-cloneable-remove', function(e) {

                e.preventDefault();

                $(this).closest('.ovic-cloneable-item').remove();

                OVIC.helper.name_replace($wrapper);

                $wrapper.ovic_customizer_refresh();

                $data.hide();

            });

        });
    };

    //
    // Field Accordion
    //
    $.fn.ovic_field_accordion = function() {
        return this.each(function() {

            var $titles = $(this).find('.ovic-accordion-title');

            $titles.on('click', function() {

                var $title = $(this),
                    $icon = $title.find('.ovic-accordion-icon'),
                    $content = $title.next();

                if ($icon.hasClass('fa-angle-right')) {
                    $icon.removeClass('fa-angle-right').addClass('fa-angle-down');
                } else {
                    $icon.removeClass('fa-angle-down').addClass('fa-angle-right');
                }

                if (!$content.data('opened')) {

                    $content.find('.ovic-field').removeClass('ovic-no-script');
                    $content.ovic_reload_script('sub');
                    $content.data('opened', true);

                }

                $content.toggleClass('ovic-accordion-open');

            });

        });
    };

    //
    // Field Slider
    //
    $.fn.ovic_field_slider = function() {
        return this.each(function() {

            var $this = $(this),
                $input = $this.find('input'),
                $slider = $this.find('.ovic-slider-ui'),
                data = $input.data(),
                value = $input.val() || 0;

            $slider.slider({
                range: 'min',
                value: parseInt(value),
                min: parseInt(data.min),
                max: parseInt(data.max),
                step: parseInt(data.step),
                slide: function(e, o) {
                    $input.val(o.value).trigger('change');
                }
            });

            $input.keyup(function() {
                $slider.slider('value', parseInt($input.val()));
            });

        });
    };

    //
    // Field Repeater
    //
    $.fn.ovic_field_repeater = function() {
        return this.each(function() {

            var $this = $(this),
                $wrapper = $this.find('.ovic-cloneable-wrapper'),
                $hidden = $this.find('.ovic-cloneable-hidden'),
                $data = $this.find('.ovic-cloneable-data'),
                unique = $data.data('unique-id'),
                limit = parseInt($data.data('limit'));

            $wrapper.sortable({
                axis: 'y',
                handle: '.ovic-cloneable-sort',
                helper: 'original',
                cursor: 'move',
                placeholder: 'widget-placeholder',
                stop: function(event, ui) {

                    OVIC.helper.name_replace($wrapper);
                    $wrapper.ovic_customizer_refresh();

                }
            });

            $this.on('click', '.ovic-cloneable-add', function(e) {

                e.preventDefault();

                var count = $wrapper.find('.ovic-cloneable-item').length;

                if (limit && (count + 1) > limit) {
                    $data.show();
                    return;
                }

                var $cloned = $hidden.ovic_clone().removeClass('ovic-cloneable-hidden');

                $wrapper.append($cloned);

                $cloned.find(':input').each(function() {
                    this.name = this.name.replace('_nonce', unique).replace('num', count);
                });

                $cloned.find('.ovic-field').removeClass('ovic-no-script');
                $cloned.ovic_reload_script('sub');

                $wrapper.ovic_customizer_refresh();
                $wrapper.ovic_customizer_listen(true);

            });

            $wrapper.on('click', '.ovic-cloneable-clone', function(e) {

                e.preventDefault();

                if (limit && parseInt($wrapper.find('.ovic-cloneable-item').length + 1) > limit) {
                    $data.show();
                    return;
                }

                var $this = $(this),
                    $parent = $this.closest('.ovic-cloneable-item'),
                    $index = $parent.index(),
                    $cloned = $parent.ovic_clone(),
                    $childs = $wrapper.children();

                $childs.eq($index).after($cloned);

                $cloned.addClass('ovic-cloned').ovic_reload_script('sub');

                OVIC.helper.name_replace($wrapper);

                $wrapper.ovic_customizer_refresh();
                $wrapper.ovic_customizer_listen(true);

            });

            $wrapper.on('click', '.ovic-cloneable-remove', function(e) {

                e.preventDefault();

                $(this).closest('.ovic-cloneable-item').remove();

                $data.hide();

                OVIC.helper.name_replace($wrapper);

                $wrapper.ovic_customizer_refresh();

            });

        });
    };

    //
    // Field Icon
    //
    $.fn.ovic_field_icon = function() {

        return this.each(function() {

            var $this = $(this);

            $this.on('click', '.ovic-icon-add', function(e) {

                var $modal = $('#ovic-modal-icon');

                e.preventDefault();

                $modal.show();
                $body.addClass('ovic-icon-scrolling');

                OVIC.vars.$icon_target = $this;

                if (!OVIC.vars.icon_modal_loaded) {

                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'ovic-get-icons'
                        },
                        success: function(content) {

                            OVIC.vars.icon_modal_loaded = true;

                            var $load = $modal.find('.ovic-modal-content').html(content);

                            $load.on('click', 'a', function(e) {

                                e.preventDefault();

                                var icon = $(this).data('ovic-icon');

                                OVIC.vars.$icon_target.find('i').removeAttr('class').addClass(icon);
                                OVIC.vars.$icon_target.find('input').val(icon).trigger('change');
                                OVIC.vars.$icon_target.find('.ovic-icon-preview').removeClass('hidden');
                                OVIC.vars.$icon_target.find('.ovic-icon-remove').removeClass('hidden');

                                $modal.hide();
                                $body.removeClass('ovic-icon-scrolling');

                            });

                            $modal.on('change keyup', '.ovic-icon-search', function() {

                                var value = $(this).val(),
                                    $icons = $load.find('a');

                                $icons.each(function() {

                                    var $elem = $(this);

                                    if ($elem.data('ovic-icon').search(new RegExp(value, 'i')) < 0) {
                                        $elem.hide();
                                    } else {
                                        $elem.show();
                                    }

                                });

                            });

                            $modal.on('click', '.ovic-modal-close, .ovic-modal-overlay', function() {

                                $modal.hide();
                                $body.removeClass('ovic-icon-scrolling');

                            });

                        }

                    });

                }

            });

            $this.on('click', '.ovic-icon-remove', function(e) {

                e.preventDefault();

                $this.find('.ovic-icon-preview').addClass('hidden');
                $this.find('input').val('').trigger('change');
                $(this).addClass('hidden');

            });

        });
    };

    //
    // Color Picker Helper
    //
    if (typeof Color === 'function') {

        Color.fn.toString = function() {

            if (this._alpha < 1) {
                return this.toCSS('rgba', this._alpha).replace(/\s+/g, '');
            }

            var hex = parseInt(this._color, 10).toString(16);

            if (this.error) {
                return '';
            }

            if (hex.length < 6) {
                for (var i = 6 - hex.length - 1; i >= 0; i--) {
                    hex = '0' + hex;
                }
            }

            return '#' + hex;

        };

    }

    OVIC.funcs.PARSE_COLOR_VALUE = function(val) {

        var value = val.replace(/\s+/g, ''),
            alpha = (value.indexOf('rgba') !== -1) ? parseFloat(value.replace(/^.*,(.+)\)/, '$1') * 100) : 100,
            rgba = (alpha < 100) ? true : false;

        return {
            value: value,
            alpha: alpha,
            rgba: rgba
        };

    };

    //
    // Field Color Picker
    //
    $.fn.ovic_field_colorpicker = function() {

        return this.each(function() {

            var $this = $(this),
                $input = $this.find('.ovic-wp-color-picker'),
                $wppicker = $this.find('.wp-picker-container');

            // Destroy and Reinit
            if ($wppicker.length) {
                $wppicker.after($input).remove();
            }

            if ($input.data('rgba') !== false) {

                var picker = OVIC.funcs.PARSE_COLOR_VALUE($input.val());

                $input.wpColorPicker({

                    clear: function() {
                        $input.trigger('keyup');
                    },

                    change: function(event, ui) {

                        var ui_color_value = ui.color.toString();

                        $input.closest('.wp-picker-container').find('.ovic-alpha-slider-offset').css('background-color', ui_color_value);
                        $input.val(ui_color_value).trigger('change');

                    },

                    create: function() {

                        var a8cIris = $input.data('a8cIris'),
                            $container = $input.closest('.wp-picker-container'),

                            $alpha_wrap = $('<div class="ovic-alpha-wrap">' +
                                '<div class="ovic-alpha-slider"></div>' +
                                '<div class="ovic-alpha-slider-offset"></div>' +
                                '<div class="ovic-alpha-text"></div>' +
                                '</div>').appendTo($container.find('.wp-picker-holder')),

                            $alpha_slider = $alpha_wrap.find('.ovic-alpha-slider'),
                            $alpha_text = $alpha_wrap.find('.ovic-alpha-text'),
                            $alpha_offset = $alpha_wrap.find('.ovic-alpha-slider-offset');

                        $alpha_slider.slider({

                            slide: function(event, ui) {

                                var slide_value = parseFloat(ui.value / 100);

                                a8cIris._color._alpha = slide_value;
                                $input.wpColorPicker('color', a8cIris._color.toString());
                                $alpha_text.text((slide_value < 1 ? slide_value : ''));

                            },

                            create: function() {

                                var slide_value = parseFloat(picker.alpha / 100),
                                    alpha_text_value = slide_value < 1 ? slide_value : '';

                                $alpha_text.text(alpha_text_value);
                                $alpha_offset.css('background-color', picker.value);

                                $container.on('click', '.wp-picker-clear', function() {

                                    a8cIris._color._alpha = 1;
                                    $alpha_text.text('').trigger('change');
                                    $alpha_slider.slider('option', 'value', 100).trigger('slide');

                                });

                                $container.on('click', '.wp-picker-default', function() {

                                    var default_picker = OVIC.funcs.PARSE_COLOR_VALUE($input.data('default-color')),
                                        default_value = parseFloat(default_picker.alpha / 100),
                                        default_text = default_value < 1 ? default_value : '';

                                    a8cIris._color._alpha = default_value;
                                    $alpha_text.text(default_text);
                                    $alpha_slider.slider('option', 'value', default_picker.alpha).trigger('slide');

                                });

                                $container.on('click', '.wp-color-result', function() {
                                    $alpha_wrap.toggle();
                                });

                                $body.on('click.wpcolorpicker', function() {
                                    $alpha_wrap.hide();
                                });

                            },

                            value: picker.alpha,
                            step: 1,
                            min: 1,
                            max: 100

                        });
                    }

                });

            } else {

                $input.wpColorPicker({
                    clear: function() {
                        $input.trigger('keyup');
                    },
                    change: function(event, ui) {
                        $input.val(ui.color.toString()).trigger('change');
                    }
                });

            }

        });

    };

    //
    // Field Ace Editor
    //
    $.fn.ovic_field_ace_editor = function() {
        return this.each(function() {

            if (typeof ace !== 'undefined') {

                var $this = $(this),
                    $textarea = $this.find('.ovic-ace-editor-textarea'),
                    options = JSON.parse($this.find('.ovic-ace-editor-options').val()),
                    editor = ace.edit($this.find('.ovic-ace-editor').attr('id'));

                // global settings of ace editor
                editor.getSession().setValue($textarea.val());

                editor.setOptions(options);

                editor.on('change', function(e) {
                    $textarea.val(editor.getSession().getValue()).trigger('change');
                });

            }

        });
    };

    //
    // Field Datepicker
    //
    $.fn.ovic_field_datepicker = function() {
        return this.each(function() {

            var $this = $(this),
                $input = $this.find('input'),
                options = JSON.parse($this.find('.ovic-datepicker-options').val()),
                wrapper = '<div class="ovic-datepicker-wrapper"></div>',
                $datepicker;

            var defaults = {
                beforeShow: function(input, inst) {
                    $datepicker = $('#ui-datepicker-div');
                    $datepicker.wrap(wrapper);
                },
                onClose: function() {
                    var cancelInterval = setInterval(function() {
                        if ($datepicker.is(':hidden')) {
                            $datepicker.unwrap(wrapper);
                            clearInterval(cancelInterval);
                        }
                    }, 100);
                }
            };

            options = $.extend({}, options, defaults);

            $input.datepicker(options);

        });
    };

    //
    // Field Tabbed
    //
    $.fn.ovic_field_tabbed = function() {
        return this.each(function() {

            var $this = $(this),
                $links = $this.find('.ovic-tabbed-nav a'),
                $section = $this.find('.ovic-tabbed-section');

            $links.on('click', function(e) {

                e.preventDefault();

                var $link = $(this),
                    index = $link.index();

                $link.addClass('ovic-tabbed-active').siblings().removeClass('ovic-tabbed-active');
                $section.eq(index).removeClass('hidden').siblings().addClass('hidden');

            });

        });
    };

    //
    // Options Reset Customizer Field
    //
    $.fn.ovic_reset_field = function() {
        return this.each(function() {
            var $this = $(this),
                $reset = $this.data('field');

            $(this).on('click', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: 'ovic-reset-field',
                        reset: $reset,
                    },
                    success: function(content) {
                        location.reload();
                    }
                });
            });
        });
    };

    //
    // Field Backup
    //
    $.fn.ovic_field_backup = function() {
        return this.each(function() {

            var $this = $(this),
                $reset = $this.find('.ovic-reset-js'),
                $import = $this.find('.ovic-import-js'),
                data = $this.find('.ovic-data').data();

            $reset.on('click', function(e) {

                $('.ovic-options').addClass('ovic-saving');

                e.preventDefault();

                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: 'ovic-reset-options',
                        unique: data.unique,
                        wpnonce: data.wpnonce
                    },
                    success: function() {
                        location.reload();
                    }
                });

            });

            $import.on('click', function(e) {

                $('.ovic-options').addClass('ovic-saving');

                e.preventDefault();

                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: 'ovic-import-options',
                        unique: data.unique,
                        wpnonce: data.wpnonce,
                        value: $this.find('.ovic-import-data').val()
                    },
                    success: function(content) {
                        location.reload();
                    }
                });

            });


        });
    };

    //
    // Confirm
    //
    $.fn.ovic_confirm = function() {
        return this.each(function() {
            $(this).on('click', function(e) {
                if (!confirm('Are you sure?')) {
                    e.preventDefault();
                }
            });
        });
    };

    //
    // Options Save
    //
    $.fn.ovic_save = function() {
        return this.each(function() {

            var $this = $(this),
                $text = $this.data('save'),
                $value = $this.val(),
                $ajax = $('.ovic-save-ajax'),
                $panel = $('.ovic-options');

            $(document).on('keydown', function(event) {
                if (event.ctrlKey || event.metaKey) {
                    if (String.fromCharCode(event.which).toLowerCase() === 's') {
                        event.preventDefault();
                        $this.trigger('click');
                    }
                }
            });

            $this.on('click', function(e) {

                if ($ajax.length) {

                    if (typeof tinyMCE === 'object') {
                        tinyMCE.triggerSave();
                    }

                    $panel.addClass('ovic-saving');
                    $this.prop('disabled', true).attr('value', $text);

                    var serializedOptions = $('#OVIC_form').serialize();

                    $.post('options.php', serializedOptions).error(function() {
                        alert('Error, Please try again.');
                    }).success(function() {
                        $panel.removeClass('ovic-saving');
                        $this.prop('disabled', false).attr('value', $value);
                    });

                    e.preventDefault();

                } else {

                    $this.addClass('disabled').attr('value', $text);

                }

            });

        });
    };

    //
    // Taxonomy Framework
    //
    $.fn.ovic_taxonomy = function() {
        return this.each(function() {

            var $this = $(this),
                $parent = $this.parent();

            if ($parent.attr('id') === 'addtag') {

                var $submit = $parent.find('#submit'),
                    $clone = $this.find('.ovic-field').ovic_clone(),
                    $list = $('#the-list'),
                    flooding = false;

                $submit.on('click', function() {

                    if (!flooding) {

                        $list.on('DOMNodeInserted', function() {

                            if (flooding) {

                                $this.empty();
                                $this.html($clone);
                                $clone = $clone.ovic_clone();

                                $this.ovic_reload_script();

                                flooding = false;

                            }

                        });

                    }

                    flooding = true;

                });

            }

        });
    };

    //
    // Shortcode Framework
    //
    $.fn.ovic_shortcode = function() {

        var instance = this,
            deploy_atts;

        instance.validate_atts = function(_atts, _this) {

            var el_value;

            if (_this.data('check') !== undefined && deploy_atts === _atts) {
                return '';
            }

            deploy_atts = _atts;

            if (_this.closest('.pseudo-field').hasClass('hidden') === true) {
                return '';
            }
            if (_this.hasClass('pseudo') === true) {
                return '';
            }

            if (_this.is(':checkbox') || _this.is(':radio')) {
                el_value = _this.is(':checked') ? _this.val() : '';
            } else {
                el_value = _this.val();
            }

            if (_this.data('check') !== undefined) {
                el_value = _this.closest('.ovic-field').find('input:checked').map(function() {
                    return $(this).val();
                }).get();
            }

            if (el_value !== null && el_value !== undefined && el_value !== '' && el_value.length !== 0) {
                return ' ' + _atts + '="' + el_value + '"';
            }

            return '';

        };

        instance.insertAtChars = function(_this, currentValue) {

            var obj = (typeof _this[0].name !== 'undefined') ? _this[0] : _this;

            if (obj.value.length && typeof obj.selectionStart !== 'undefined') {
                obj.focus();
                return obj.value.substring(0, obj.selectionStart) + currentValue + obj.value.substring(obj.selectionEnd, obj.value.length);
            } else {
                obj.focus();
                return currentValue;
            }

        };

        instance.send_to_editor = function(html, editor_id) {

            var tinymce_editor;

            if (typeof tinymce !== 'undefined') {
                tinymce_editor = tinymce.get(editor_id);
            }

            if (tinymce_editor && !tinymce_editor.isHidden()) {
                tinymce_editor.execCommand('mceInsertContent', false, html);
            } else {
                var $editor = $('#' + editor_id);
                $editor.val(instance.insertAtChars($editor, html)).trigger('change');
            }

        };

        return this.each(function() {

            var $this = $(this),
                $content = $this.find('.ovic-modal-content'),
                $insert = $this.find('.ovic-modal-insert'),
                $select = $this.find('select'),
                modal_id = $this.data('modal-id'),
                editor_id,
                sc_name,
                sc_view,
                sc_clone,
                $sc_elem;

            $(document).on('click', '.ovic-shortcode-button[data-modal-button-id="' + modal_id + '"]', function(e) {

                var $button = $(this);

                e.preventDefault();

                $sc_elem = $button;
                editor_id = $button.data('editor-id') || false;

                $this.show();
                $body.addClass('ovic-shortcode-scrolling');

            });

            $select.on('change', function() {

                var $elem = $(this);
                sc_name = $elem.val();
                sc_view = $elem.find(':selected').data('view');

                if (sc_name.length) {

                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'ovic-get-shortcode-' + modal_id,
                            shortcode: sc_name
                        },
                        success: function(content) {

                            $content.html(content);
                            $insert.parent().removeClass('hidden');

                            sc_clone = $('.ovic-shortcode-clone', $this).ovic_clone();

                            $content.ovic_reload_script('sub');

                        }
                    });

                } else {

                    $insert.parent().addClass('hidden');
                    $content.html('');

                }

            });

            $insert.on('click', function(e) {

                e.preventDefault();

                var shortcode = '',
                    ruleAttr = 'data-atts',
                    cloneAttr = 'data-clone-atts',
                    cloneID = 'data-clone-id';

                switch (sc_view) {

                    case 'contents':

                        $this.find('[' + ruleAttr + ']').each(function() {
                            var _this = $(this),
                                _atts = _this.data('atts');
                            shortcode += '[' + _atts + ']';
                            shortcode += _this.val();
                            shortcode += '[/' + _atts + ']';
                        });

                        break;

                    case 'clone':

                        shortcode += '[' + sc_name;

                        $('[' + ruleAttr + ']', $this.find('.ovic-field:not(.hidden)')).each(function() {
                            var _this_main = $(this),
                                _this_main_atts = _this_main.data('atts');
                            shortcode += instance.validate_atts(_this_main_atts, _this_main);
                        });

                        shortcode += ']';

                        $this.find('[' + cloneID + ']').each(function() {

                            var _this_clone = $(this),
                                _clone_id = _this_clone.data('clone-id');

                            shortcode += '[' + _clone_id;

                            $('[' + cloneAttr + ']', _this_clone.find('.ovic-field:not(.hidden)')).each(function() {

                                var _this_multiple = $(this),
                                    _atts_multiple = _this_multiple.data('clone-atts');

                                if (_atts_multiple !== 'content') {
                                    shortcode += instance.validate_atts(_atts_multiple, _this_multiple);
                                } else if (_atts_multiple === 'content') {
                                    shortcode += ']';
                                    shortcode += _this_multiple.val();
                                    shortcode += '[/' + _clone_id + '';
                                }
                            });

                            shortcode += ']';

                        });

                        shortcode += '[/' + sc_name + ']';

                        break;

                    case 'clone_duplicate':

                        $this.find('[' + cloneID + ']').each(function() {

                            var _this_clone = $(this),
                                _clone_id = _this_clone.data('clone-id');

                            shortcode += '[' + _clone_id;

                            $('[' + cloneAttr + ']', _this_clone.find('.ovic-field:not(.hidden)')).each(function() {

                                var _this_multiple = $(this),
                                    _atts_multiple = _this_multiple.data('clone-atts');

                                if (_atts_multiple !== 'content') {
                                    shortcode += instance.validate_atts(_atts_multiple, _this_multiple);
                                } else if (_atts_multiple === 'content') {
                                    shortcode += ']';
                                    shortcode += _this_multiple.val();
                                    shortcode += '[/' + _clone_id + '';
                                }
                            });

                            shortcode += ']';

                        });

                        break;

                    default:

                        shortcode += '[' + sc_name;

                        $('[' + ruleAttr + ']', $this.find('.ovic-field:not(.hidden)')).each(function() {

                            var _this = $(this),
                                _atts = _this.data('atts');

                            if (_atts !== 'content') {
                                shortcode += instance.validate_atts(_atts, _this);
                            } else if (_atts === 'content') {
                                shortcode += ']';
                                shortcode += _this.val();
                                shortcode += '[/' + sc_name + '';
                            }

                        });

                        shortcode += ']';

                        break;

                }

                if (!editor_id) {
                    var $textarea = $sc_elem.next();
                    $textarea.val(instance.insertAtChars($textarea, shortcode)).trigger('change');
                } else {
                    instance.send_to_editor(shortcode, editor_id);
                }

                deploy_atts = null;

                $this.hide();
                $body.removeClass('ovic-shortcode-scrolling');

            });

            $content.on('click', '.ovic-clone-button', function(e) {

                e.preventDefault();

                var $cloned = sc_clone.ovic_clone().addClass('ovic-shortcode-cloned');

                $content.find('.ovic-clone-button-wrapper').before($cloned);

                $cloned.find(':input').attr('name', '_nonce_' + $cloned.index());

                $cloned.find('.ovic-remove-clone').show().on('click', function(e) {

                    $cloned.remove();
                    e.preventDefault();

                });

                // reloadPlugins
                $cloned.ovic_reload_script('sub');

            });

            $this.on('click', '.ovic-modal-close, .ovic-modal-overlay', function() {
                $this.hide();
                $body.removeClass('ovic-shortcode-scrolling');
            });

        });
    };

    //
    // Helper Tooltip
    //
    $.fn.ovic_tooltip = function() {
        return this.each(function() {

            var $this = $(this),
                $tooltip,
                tooltip_left;

            $this.on({
                mouseenter: function() {

                    $tooltip = $('<div class="ovic-tooltip"></div>').html($this.attr('data-title')).appendTo('body');

                    tooltip_left = (has_rtl) ? ($this.offset().left + 24) : ($this.offset().left - $tooltip.outerWidth());

                    $tooltip.css({
                        top: $this.offset().top - (($tooltip.outerHeight() / 2) - 12),
                        left: tooltip_left,
                    });

                },
                mouseleave: function() {

                    if ($tooltip !== undefined) {
                        $tooltip.remove();
                    }

                }

            });

        });
    };

    //
    // Customize Refresh
    //
    $.fn.ovic_customizer_refresh = function() {
        return this.each(function() {

            var $this = $(this),
                $complex = $this.closest('.ovic-customize-complex');

            $(document).trigger('ovic-customizer-refresh', $this);

            if (wp.customize === undefined || $complex.length === 0) {
                return;
            }

            var $input = $complex.find(':input'),
                $unique = $complex.data('unique-id'),
                $option = $complex.data('option-id'),
                obj = $input.serializeObjectOVIC(),
                data = (!$.isEmptyObject(obj)) ? obj[$unique][$option] : '';

            wp.customize.control($unique + '[' + $option + ']').setting.set(data);

        });
    };

    //
    // Customize Listen Form Elements
    //
    $.fn.ovic_customizer_listen = function(has_closest) {
        return this.each(function() {

            if (wp.customize === undefined) {
                return;
            }

            var $this = (has_closest) ? $(this).closest('.ovic-customize-complex') : $(this),
                $input = $this.find(':input'),
                $unique = $this.data('unique-id'),
                $option = $this.data('option-id');

            if ($unique === undefined) {
                return;
            }

            $input.on('change keyup', OVIC.helper.debounce(function() {

                var obj = $this.find(':input').serializeObjectOVIC();
                var data = (!$.isEmptyObject(obj)) ? obj[$unique][$option] : '';

                wp.customize.control($unique + '[' + $option + ']').setting.set(data);

            }, 250));

        });
    };

    //
    // Customizer Listener for Reload JS
    //
    $(document).on('expanded', '.control-section-ovic', function() {

        var $this = $(this);

        if (!$this.data('inited')) {
            $this.ovic_reload_script();
            $this.find('.ovic-customize-complex').ovic_customizer_listen();
        }

    });

    //
    // Widgets Framework
    //
    $.fn.ovic_widgets = function() {
        return this.each(function() {

            var $this = $(this),
                $widgets = $this.find('.widget-liquid-right .widget');

            $widgets.each(function() {

                var $widget = $(this),
                    $title = $widget.find('.widget-top');

                $title.on('click', function() {
                    $widget.ovic_reload_script();
                });

            });

        });
    };

    //
    // Widget Listener for Reload JS
    //
    $(document).on('widget-added widget-updated', function(event, $widget) {
        $widget.ovic_reload_script();
    });

    //
    // Reload Widget Plugins
    //
    $.fn.ovic_reload_script = function(has_sub) {
        return this.each(function() {

            var $this = $(this),
                $dependency = $this;

            $this.find('.ovic-field-image-selector').not('.ovic-no-script').ovic_field_image_selector();
            $this.find('.ovic-field-image').not('.ovic-no-script').ovic_field_image();
            $this.find('.ovic-field-gallery').not('.ovic-no-script').ovic_field_gallery();
            $this.find('.ovic-field-sorter').not('.ovic-no-script').ovic_field_sorter();
            $this.find('.ovic-field-upload').not('.ovic-no-script').ovic_field_upload();
            $this.find('.ovic-field-color_picker').not('.ovic-no-script').ovic_field_colorpicker();
            $this.find('.ovic-field-icon').not('.ovic-no-script').ovic_field_icon();
            $this.find('.ovic-field-group').not('.ovic-no-script').ovic_field_group();
            $this.find('.ovic-field-accordion').not('.ovic-no-script').ovic_field_accordion();
            $this.find('.ovic-field-slider').not('.ovic-no-script').ovic_field_slider();
            $this.find('.ovic-field-repeater').not('.ovic-no-script').ovic_field_repeater();
            $this.find('.ovic-field-ace_editor').not('.ovic-no-script').ovic_field_ace_editor();
            $this.find('.ovic-field-date').not('.ovic-no-script').ovic_field_datepicker();
            $this.find('.ovic-field-tabbed').not('.ovic-no-script').ovic_field_tabbed();
            $this.find('.ovic-field-backup').not('.ovic-no-script').ovic_field_backup();
            $this.find('.ovic-help').not('.ovic-no-script').ovic_tooltip();
            $this.find('.chosen').not('.ovic-no-script').ovic_chosen();

            if ($this.closest('.ovic-wrapper').length > 0) {
                $dependency = $this.closest('.ovic-wrapper');
            }
            $dependency.ovic_dependency();

            if (has_sub === 'sub') {
                $dependency.ovic_dependency('sub');
            }

            $this.data('inited', true);

            $(document).trigger('ovic-reload-script', $this);

        });
    };

    window.addEventListener('load',
        function(ev) {
            $('.ovic-save').ovic_save();
            $('.ovic-confirm').ovic_confirm();
            $('.ovic-nav').ovic_navigation();
            $('.ovic-search').ovic_search();
            $('.ovic-sticky-header').ovic_sticky();
            $('.ovic-taxonomy').ovic_taxonomy();
            $('.ovic-shortcode').ovic_shortcode();
            $('.widgets-php').ovic_widgets();
            $('.ovic-onload').ovic_reload_script();
            $('.reset-field-customizer').ovic_reset_field();
        }, false);

})(jQuery, window, document);