<?php
/**
 * Ovic Visual composer setup
 *
 * @author   KHANH
 * @category API
 * @package  Ovic_Visual_composer
 * @since    1.0.0
 */
require_once plugin_dir_path(__FILE__) . 'params.php';
if ( !class_exists('Ovic_Visual_composer') ) {
    class Ovic_Visual_composer
    {
        public $options  = array();
        public $map_keys = array();

        public function __construct()
        {
            $this->options = get_option('_ovic_responsive_vc_settings');

            add_action('vc_before_mapping', array( $this, 'ovic_map_shortcode' ));
            add_action('vc_after_mapping', array( $this, 'ovic_add_param_all_shortcode' ));
            add_filter('vc_iconpicker-type-oviccustomfonts', array( $this, 'iconpicker_type_oviccustomfonts' ));
            if ( is_admin() ) {
                /* OPTIONS DEFAULT */
                add_action('vc_before_mapping', array( $this, 'autocomplete' ));
                add_filter('ovic_registered_settings', array( $this, 'add_options' ), 10, 1);
            } else {
                add_filter('ovic_main_custom_css', array( $this, 'ovic_shortcodes_custom_css' ));
            }
            /* CUSTOM CSS EDITOR */
            add_filter('vc_shortcodes_css_class', array( $this, 'ovic_change_element_class_name' ), 10, 3);
            /* INCLUDE SHORTCODE */
            add_action('vc_after_init', array( $this, 'ovic_include_shortcode' ));
            /* TEMPLATE DEFAULT */
            add_action('vc_load_default_templates_action', array( $this, 'ovic_load_default_templates' ));
        }

        public static function ovic_responsive_vc_data()
        {
            $enable_responsive = get_option('_ovic_responsive_vc_settings');
            $switcher          = ( isset($enable_responsive['switcher_res']) ) ? $enable_responsive['switcher_res'] : 'on';
            $options           = get_option('_ovic_responsive_vc_settings');
            $editor_names      = array(
                'desktop' => array(
                    'screen' => 999999,
                    'name'   => 'Desktop',
                ),
                'laptop'  => array(
                    'screen' => isset($options['screen_laptop']) ? $options['screen_laptop'] : 1499,
                    'name'   => 'Laptop',
                ),
                'tablet'  => array(
                    'screen' => isset($options['screen_tablet']) ? $options['screen_tablet'] : 1199,
                    'name'   => 'Tablet',
                ),
                'ipad'    => array(
                    'screen' => isset($options['screen_ipad']) ? $options['screen_ipad'] : 991,
                    'name'   => 'Ipad',
                ),
                'mobile'  => array(
                    'screen' => isset($options['screen_mobile']) ? $options['screen_mobile'] : 767,
                    'name'   => 'Mobile',
                ),
            );
            if ( isset($options['advanced_screen']) && !empty($options['advanced_screen']) ) {
                foreach ( $options['advanced_screen'] as $data ) {
                    $delimiter = '_';
                    $slug      = strtolower(trim(preg_replace('/[\s-]+/', $delimiter,
                        preg_replace('/[^A-Za-z0-9-]+/', $delimiter,
                            preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', $data['name'])))), $delimiter));
                    /* regen array */
                    $editor_names[$slug] = array(
                        'screen' => $data['screen'],
                        'name'   => $data['name'],
                    );
                }
            }
            if ( $switcher != 'on' ) {
                $editor_names = array(
                    'desktop' => array(
                        'screen' => 999999,
                        'name'   => 'Desktop',
                    ),
                );
            }

            return apply_filters('ovic_responsive_vc_data', $editor_names);
        }

        public function add_options( $options )
        {
            $options['extends_vc'] = array(
                'id'         => 'extends_vc',
                'title'      => __('Extends Visual', 'ovic-toolkit'),
                'show_names' => true,
                'sections'   => array(
                    '_ovic_responsive_vc_settings' => array(
                        'id'     => '_ovic_responsive_vc_settings',
                        'title'  => __('Ovic Responsive VC', 'ovic-toolkit'),
                        'fields' => array(
                            array(
                                'name'    => __('Enable Responsive', 'ovic-toolkit'),
                                'id'      => 'switcher_res',
                                'type'    => 'switch',
                                'default' => 'on',
                            ),
                            array(
                                'name'    => __('Advanced Responsive', 'ovic-toolkit'),
                                'id'      => 'advanced_res',
                                'type'    => 'switch',
                                'default' => 'on',
                            ),
                            array(
                                'name'    => __('Screen Laptop (max-width)', 'ovic-toolkit'),
                                'default' => 1499,
                                'id'      => 'screen_laptop',
                                'type'    => 'text_small',
                                'desc'    => __('px', 'ovic-toolkit'),
                            ),
                            array(
                                'name'    => __('Screen Tablet (max-width)', 'ovic-toolkit'),
                                'default' => 1199,
                                'id'      => 'screen_tablet',
                                'type'    => 'text_small',
                                'desc'    => __('px', 'ovic-toolkit'),
                            ),
                            array(
                                'name'    => __('Screen Ipad (max-width)', 'ovic-toolkit'),
                                'default' => 991,
                                'id'      => 'screen_ipad',
                                'type'    => 'text_small',
                                'desc'    => __('px', 'ovic-toolkit'),
                            ),
                            array(
                                'name'    => __('Screen Mobile (max-width)', 'ovic-toolkit'),
                                'default' => 767,
                                'id'      => 'screen_mobile',
                                'type'    => 'text_small',
                                'desc'    => __('px', 'ovic-toolkit'),
                            ),
                            array(
                                'id'      => 'advanced_screen',
                                'name'    => __('Add More Screen', 'ovic-toolkit'),
                                'type'    => 'group',
                                'options' => array(
                                    'group_title'   => 'Screen #{#}',
                                    'add_button'    => __('Add a Screen', 'ovic-toolkit'),
                                    'remove_button' => __('Remove this Screen', 'ovic-toolkit'),
                                ),
                                'fields'  => array(
                                    array(
                                        'name' => __('Name Screen', 'ovic-toolkit'),
                                        'id'   => 'name',
                                        'type' => 'text_medium',
                                    ),
                                    array(
                                        'name' => __('Screen (max-width)', 'ovic-toolkit'),
                                        'id'   => 'screen',
                                        'type' => 'text_small',
                                        'desc' => __('px', 'ovic-toolkit'),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            );

            return $options;
        }

        function ovic_load_default_templates()
        {
            if ( file_exists(get_template_directory() . '/vc_template.json') ) {
                $option_file_url = get_theme_file_uri('vc_template.json');
                if ( !is_wp_error(wp_remote_get($option_file_url)) ) {
                    $option_content = wp_remote_get($option_file_url);
                    if ( !empty($option_content) && isset($option_content['body']) ) {
                        $option_content  = $option_content['body'];
                        $options_configs = json_decode($option_content, true);
                        if ( !empty($options_configs) ) {
                            foreach ( $options_configs as $options ) {
                                $data             = array();
                                $data['name']     = $options['name'];
                                $data['disabled'] = false;
                                $data['content']  = $options['content'];
                                vc_add_default_templates($data);
                            }
                        }
                    }
                }
            }
        }

        function ovic_shortcodes_custom_css( $css )
        {
            $id_page    = '';
            $inline_css = array();
            // Get all custom inline CSS.
            if ( is_singular() ) {
                $id_page = get_the_ID();
            } elseif ( function_exists('is_shop') && is_shop() ) {
                $id_page = get_option('woocommerce_shop_page_id');
            }
            if ( $id_page != '' ) {
                $inline_css[] = get_post_meta($id_page, '_Ovic_Shortcode_custom_css', true);
                if ( !empty($inline_css) ) {
                    $css .= implode(' ', $inline_css);
                }
            }

            return $css;
        }

        function change_font_container_output_data( $data, $fields, $values, $settings )
        {
            if ( isset($fields['text_align']) ) {
                $data['text_align'] = '
                <div class="vc_row-fluid vc_column">
                    <div class="wpb_element_label">' . __('Text align', 'ovic-toolkit') . '</div>
                    <div class="vc_font_container_form_field-text_align-container">
                        <select class="vc_font_container_form_field-text_align-select">
                            <option value="" class="" ' . ( '' === $values['text_align'] ? 'selected="selected"' : '' ) . '>' . __('None',
                        'ovic-toolkit') . '</option>
                            <option value="left" class="left" ' . ( 'left' === $values['text_align'] ? 'selected="selected"' : '' ) . '>' . __('Left',
                        'ovic-toolkit') . '</option>
                            <option value="right" class="right" ' . ( 'right' === $values['text_align'] ? 'selected="selected"' : '' ) . '>' . __('Right',
                        'ovic-toolkit') . '</option>
                            <option value="center" class="center" ' . ( 'center' === $values['text_align'] ? 'selected="selected"' : '' ) . '>' . __('Center',
                        'ovic-toolkit') . '</option>
                            <option value="justify" class="justify" ' . ( 'justify' === $values['text_align'] ? 'selected="selected"' : '' ) . '>' . __('Justify',
                        'ovic-toolkit') . '</option>
                        </select>
                    </div>';
                if ( isset($fields['text_align_description']) && strlen($fields['text_align_description']) > 0 ) {
                    $data['text_align'] .= '
                    <span class="vc_description clear">' . $fields['text_align_description'] . '</span>
                    ';
                }
                $data['text_align'] .= '</div>';
            }

            return $data;
        }

        public static function get_google_font_data( $tag, $atts, $key = 'google_fonts' )
        {
            extract($atts);
            $google_fonts_field          = WPBMap::getParam($tag, $key);
            $google_fonts_obj            = new Vc_Google_Fonts();
            $google_fonts_field_settings = isset($google_fonts_field['settings'], $google_fonts_field['settings']['fields']) ? $google_fonts_field['settings']['fields'] : array();
            $google_fonts_data           = strlen($atts[$key]) > 0 ? $google_fonts_obj->_vc_google_fonts_parse_attributes($google_fonts_field_settings,
                $atts[$key]) : '';

            return $google_fonts_data;
        }

        function ovic_change_element_class_name( $class_string, $tag, $atts )
        {
            $editor_names = $this->ovic_responsive_vc_data();
            $atts         = function_exists('vc_map_get_attributes') ? vc_map_get_attributes($tag, $atts) : $atts;
            // Extract shortcode parameters.
            extract($atts);
            $google_fonts_data = array();
            $class_string      = array( $class_string );
            $class_string[]    = isset($atts['el_class']) ? $atts['el_class'] : '';
            $class_string[]    = isset($atts['ovic_custom_id']) ? $atts['ovic_custom_id'] : '';
            if ( strpos($tag,
                    'vc_wp') === false && $tag != 'vc_btn' && $tag != 'vc_tta_section' && $tag != 'vc_icon' ) {
                $class_string[] = isset($atts['css']) ? vc_shortcode_custom_css_class($atts['css'], ' ') : '';
            }
            $settings = get_option('wpb_js_google_fonts_subsets');
            if ( is_array($settings) && !empty($settings) ) {
                $subsets = '&subset=' . implode(',', $settings);
            } else {
                $subsets = '';
            }
            if ( !empty($editor_names) ) {
                foreach ( $editor_names as $key => $data ) {
                    $class_string[] = isset($atts["css_{$key}"]) ? vc_shortcode_custom_css_class($atts["css_{$key}"],
                        '') : '';
                    /* GOOGLE FONT */
                    if ( isset($atts["google_fonts_{$key}"]) ) {
                        $google_fonts_data = $this->get_google_font_data($tag, $atts, "google_fonts_{$key}");
                    }
                    if ( ( !isset($atts["use_theme_fonts_{$key}"]) || 'yes' !== $atts["use_theme_fonts_{$key}"] ) && isset($google_fonts_data['values']['font_family']) ) {
                        wp_enqueue_style('vc_google_fonts_' . vc_build_safe_css_class($google_fonts_data['values']['font_family']),
                            '//fonts.googleapis.com/css?family=' . $google_fonts_data['values']['font_family'] . $subsets);
                    }
                }
            }

            return preg_replace('/\s+/', ' ', implode(' ', $class_string));
        }

        public function ovic_add_param_all_shortcode()
        {
            global $shortcode_tags;
            $editor_names = $this->ovic_responsive_vc_data();
            WPBMap::addAllMappedShortcodes();
            $switcher = ( isset($this->options['switcher_res']) ) ? $this->options['switcher_res'] : 'on';
            $advanced = ( isset($this->options['advanced_res']) ) ? $this->options['advanced_res'] : 'on';
            if ( count($shortcode_tags) > 0 && !empty($editor_names) ) {
                $unallow     = array(
                    'vc_btn',
                    'vc_icon',
                    'vc_tta_section',
                );
                $none_editor = array(
                    'woocommerce_cart',
                    'woocommerce_checkout',
                    'woocommerce_order_tracking',
                );
                foreach ( $shortcode_tags as $tag => $function ) {
                    if ( strpos($tag, 'vc_wp') === false && !in_array($tag, $unallow) ) {
                        if ( class_exists('WooCommerce') && in_array($tag, $none_editor) ) {
                            continue;
                        }
                        vc_remove_param($tag, 'css');
                        add_filter('vc_base_build_shortcodes_custom_css', '__return_empty_string');
                        add_filter('vc_font_container_output_data', array( $this, 'change_font_container_output_data' ),
                            10, 4);
                        /* MARKUP HTML TAB */
                        $html_tab = '<div class="tabs-css">';
                        foreach ( $editor_names as $key => $data ) {
                            $name     = ucfirst($data['name']);
                            $active   = ( $key == 'desktop' ) ? ' active' : '';
                            $html_tab .= "<span class='tab_css {$key}{$active}' data-tabs='{$key}'>{$name}</span>";
                        }
                        $html_tab .= '</div>';
                        if ( $switcher != 'on' ) {
                            $html_tab = '';
                        }
                        /* MARKUP HTML TITLE */
                        $html_title = '<div class="tabs-title">';
                        $html_title .= "<h3 class='title'>" . esc_html__('Advanced Options', 'ovic-toolkit') . "</h3>";
                        $html_title .= '</div>';
                        $attributes = array(
                            array(
                                'type'        => 'textfield',
                                'heading'     => esc_html__('Extra class name', 'ovic-toolkit'),
                                'param_name'  => 'el_class',
                                'description' => esc_html__('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.',
                                    'ovic-toolkit'),
                            ),
                            array(
                                'param_name' => 'hidden_markup_01',
                                'type'       => 'ovic_markup',
                                'markup'     => $html_tab,
                                'group'      => esc_html__('Design Options', 'ovic-toolkit'),
                            ),
                            array(
                                'param_name'       => 'ovic_custom_id',
                                'heading'          => esc_html__('Hidden ID', 'ovic-toolkit'),
                                'type'             => 'uniqid',
                                'edit_field_class' => 'hidden',
                            ),
                        );
                        /* CSS EDITOR */
                        foreach ( $editor_names as $key => $data ) {
                            $advanced_editor   = array();
                            $name              = ucfirst($data['name']);
                            $hidden            = $key != 'desktop' ? ' hidden' : '';
                            $screen            = $data['screen'] < 999999 ? " ( max-width: {$data['screen']}px )" : '';
                            $attributes_editor = array(
                                /* CSS EDITOR */
                                array(
                                    'type'             => 'css_editor',
                                    'heading'          => esc_html__("Screen {$name}{$screen}", 'ovic-toolkit'),
                                    'param_name'       => "css_{$key}",
                                    'group'            => esc_html__('Design Options', 'ovic-toolkit'),
                                    'edit_field_class' => "vc_col-xs-12 {$key}{$hidden}",
                                ),
                            );
                            if ( $advanced == 'on' ) {
                                $advanced_editor = array(
                                    array(
                                        'param_name'       => "hidden_markup_{$key}",
                                        'type'             => 'ovic_markup',
                                        'markup'           => $html_title,
                                        'group'            => esc_html__('Design Options', 'ovic-toolkit'),
                                        'edit_field_class' => "vc_col-xs-12 {$key}{$hidden}",
                                    ),
                                    /* CHECKBOX BACKGROUND */
                                    array(
                                        'type'             => 'checkbox',
                                        'heading'          => esc_html__('Disable Background?', 'ovic-toolkit'),
                                        'param_name'       => "disable_bg_{$key}",
                                        'description'      => esc_html__('Disable Background in this screen.',
                                            'ovic-toolkit'),
                                        'value'            => array( esc_html__('Yes', 'ovic-toolkit') => 'yes' ),
                                        'edit_field_class' => "vc_col-xs-12 vc_col-sm-4 {$key}{$hidden}",
                                        'group'            => esc_html__('Design Options', 'ovic-toolkit'),
                                    ),
                                    /* WIDTH CONTAINER */
                                    array(
                                        'type'             => 'textfield',
                                        'heading'          => esc_html__("Width {$name}", 'ovic-toolkit'),
                                        'param_name'       => "width_rows_{$key}",
                                        'group'            => esc_html__('Design Options', 'ovic-toolkit'),
                                        'edit_field_class' => "vc_col-xs-12 vc_col-sm-4 {$key}{$hidden}",
                                    ),
                                    /* UNIT CSS WIDTH */
                                    array(
                                        'type'             => 'dropdown',
                                        'heading'          => esc_html__('Unit', 'ovic-toolkit'),
                                        'param_name'       => "width_unit_{$key}",
                                        'value'            => array(
                                            esc_html__('Percent (%)', 'ovic-toolkit')     => '%',
                                            esc_html__('Pixel (px)', 'ovic-toolkit')      => 'px',
                                            esc_html__('Em (em)', 'ovic-toolkit')         => 'em',
                                            esc_html__('View Width (vw)', 'ovic-toolkit') => 'vw',
                                            esc_html__('Custom Width', 'ovic-toolkit')    => 'none',
                                        ),
                                        'std'              => '%',
                                        'group'            => esc_html__('Design Options', 'ovic-toolkit'),
                                        'edit_field_class' => "vc_col-xs-12 vc_col-sm-4 {$key}{$hidden}",
                                    ),
                                    /* TEXT FONT */
                                    array(
                                        'type'             => 'textfield',
                                        'heading'          => esc_html__('Letter Spacing', 'ovic-toolkit'),
                                        'param_name'       => "letter_spacing_{$key}",
                                        'description'      => esc_html__('Enter letter spacing.', 'ovic-toolkit'),
                                        'group'            => esc_html__('Design Options', 'ovic-toolkit'),
                                        'edit_field_class' => "vc_col-xs-12 {$key}{$hidden}",
                                    ),
                                    array(
                                        'type'             => 'font_container',
                                        'group'            => esc_html__('Design Options', 'ovic-toolkit'),
                                        'param_name'       => "responsive_font_{$key}",
                                        'edit_field_class' => "vc_col-xs-12 {$key}{$hidden}",
                                        'settings'         => array(
                                            'fields' => array(
                                                'text_align',
                                                'font_size',
                                                'line_height',
                                                'color',
                                                'text_align_description'  => esc_html__('Select text alignment.',
                                                    'ovic-toolkit'),
                                                'font_size_description'   => esc_html__('Enter font size.',
                                                    'ovic-toolkit'),
                                                'line_height_description' => esc_html__('Enter line height.',
                                                    'ovic-toolkit'),
                                                'color_description'       => esc_html__('Select heading color.',
                                                    'ovic-toolkit'),
                                            ),
                                        ),
                                    ),
                                    array(
                                        'type'             => 'checkbox',
                                        'heading'          => esc_html__('Use theme default font family?',
                                            'ovic-toolkit'),
                                        'param_name'       => "use_theme_fonts_{$key}",
                                        'value'            => array(
                                            esc_html__('Yes', 'ovic-toolkit') => 'yes',
                                        ),
                                        'std'              => 'yes',
                                        'description'      => esc_html__('Use font family from the theme.',
                                            'ovic-toolkit'),
                                        'edit_field_class' => "vc_col-xs-12 {$key}{$hidden}",
                                        'group'            => esc_html__('Design Options', 'ovic-toolkit'),
                                    ),
                                    array(
                                        'type'             => 'google_fonts',
                                        'param_name'       => "google_fonts_{$key}",
                                        'value'            => 'font_family:Abril%20Fatface%3Aregular|font_style:400%20regular%3A400%3Anormal',
                                        'settings'         => array(
                                            'fields' => array(
                                                'font_family_description' => esc_html__('Select font family.',
                                                    'ovic-toolkit'),
                                                'font_style_description'  => esc_html__('Select font styling.',
                                                    'ovic-toolkit'),
                                            ),
                                        ),
                                        'dependency'       => array(
                                            'element'            => "use_theme_fonts_{$key}",
                                            'value_not_equal_to' => 'yes',
                                        ),
                                        'group'            => esc_html__('Design Options', 'ovic-toolkit'),
                                        'edit_field_class' => "vc_col-xs-12 {$key}{$hidden}",
                                    ),
                                    /* CUSTOM CSS */
                                    array(
                                        'type'             => 'textarea',
                                        'heading'          => esc_html__('Custom CSS', 'ovic-toolkit'),
                                        'param_name'       => "custom_css_{$key}",
                                        'description'      => esc_html__('Enter css Properties.', 'ovic-toolkit'),
                                        'group'            => esc_html__('Design Options', 'ovic-toolkit'),
                                        'edit_field_class' => "vc_col-xs-12 {$key}{$hidden}",
                                    ),
                                );
                            }
                            if ( $tag !== 'vc_cta' ) {
                                $advanced_editor = array_merge($advanced_editor,
                                    array(
                                        array(
                                            'type'             => 'css_editor',
                                            'param_name'       => 'css',
                                            'group'            => esc_html__('Design Options', 'ovic-toolkit'),
                                            'edit_field_class' => 'hidden',
                                        ),
                                    )
                                );
                            }
                            $attributes = array_merge($attributes, $attributes_editor, $advanced_editor);
                        }
                    } else {
                        $attributes = array(
                            array(
                                'type'        => 'textfield',
                                'heading'     => esc_html__('Extra class name', 'ovic-toolkit'),
                                'param_name'  => 'el_class',
                                'description' => esc_html__('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.',
                                    'ovic-toolkit'),
                            ),
                            array(
                                'param_name'       => 'ovic_custom_id',
                                'heading'          => esc_html__('Hidden ID', 'ovic-toolkit'),
                                'type'             => 'uniqid',
                                'edit_field_class' => 'hidden',
                            ),
                        );
                    }
                    if ( $tag == 'vc_tta_section' ) {
                        $attributes = array(
                            array(
                                'type'        => 'attach_image',
                                'param_name'  => 'title_image',
                                'heading'     => esc_html__('Title image', 'ovic-toolkit'),
                                'description' => esc_html__('If you select image, title will display none',
                                    'ovic-toolkit'),
                                'group'       => esc_html__('Image Group', 'ovic-toolkit'),
                            ),
                        );
                    }
                    vc_add_params($tag, $attributes);
                }
            }
        }

        public function iconpicker_type_oviccustomfonts()
        {
            $icons['Ovic Fonts'] = apply_filters('ovic_add_icon_field', array());

            return $icons;
        }

        /**
         * load param autocomplete render
         * */
        public function autocomplete()
        {
            if ( class_exists('Vc_Vendor_Woocommerce') ) {
                $vendor_woocommerce = new Vc_Vendor_Woocommerce();
                //Filters For autocomplete param:
                //For suggestion: vc_autocomplete_[shortcode_name]_[param_name]_callback
                add_filter('vc_autocomplete_ovic_products_2_ids_callback',
                    array(
                        $vendor_woocommerce,
                        'productIdAutocompleteSuggester',
                    ), 10, 1
                ); // Get suggestion(find). Must return an array
                add_filter('vc_autocomplete_ovic_products_2_ids_render',
                    array(
                        $vendor_woocommerce,
                        'productIdAutocompleteRender',
                    ), 10, 1
                ); // Render exact product. Must return an array (label,value)
                //For param: ID default value filter
                add_filter('vc_form_fields_render_field_ovic_products_2_ids_param_value',
                    array(
                        $vendor_woocommerce,
                        'productsIdsDefaultValue',
                    ), 10, 4
                ); // Defines default value for param if not provided. Takes from other param value.
                //For param: "filter" param value
                //vc_form_fields_render_field_{shortcode_name}_{param_name}_param
                add_filter('vc_form_fields_render_field_ovic_products_2_filter_param',
                    array(
                        $vendor_woocommerce,
                        'productAttributeFilterParamValue',
                    ), 10, 4
                ); // Defines default value for param if not provided. Takes from other param value.
            }
            add_filter('vc_autocomplete_ovic_products_ids_callback', array( $this, 'productIdAutocompleteSuggester' ),
                10, 1);
            add_filter('vc_autocomplete_ovic_products_ids_render', array( $this, 'productIdAutocompleteRender' ), 10,
                1);
        }

        protected function getCategoryChildsFull( $parent_id, $array, $level, &$dropdown )
        {
            $keys = array_keys($array);
            $i    = 0;
            while ( $i < count($array) ) {
                $key  = $keys[$i];
                $item = $array[$key];
                $i++;
                if ( $item->category_parent == $parent_id ) {
                    $name       = str_repeat('- ', $level) . $item->name;
                    $value      = $item->slug;
                    $dropdown[] = array(
                        'label' => $name . '(' . $item->term_id . ')',
                        'value' => $value,
                    );
                    unset($array[$key]);
                    $array = $this->getCategoryChildsFull($item->term_id, $array, $level + 1, $dropdown);
                    $keys  = array_keys($array);
                    $i     = 0;
                }
            }

            return $array;
        }

        /**
         * Suggester for autocomplete by id/name/title/sku
         *
         * @param $query
         *
         * @return array - id's from products with title/sku.
         * @since 4.4
         *
         */
        public static function productIdAutocompleteSuggester( $query )
        {
            global $wpdb;
            $product_id      = (int) $query;
            $post_meta_infos = $wpdb->get_results($wpdb->prepare("SELECT a.ID AS id, a.post_title AS title, b.meta_value AS sku
					FROM {$wpdb->posts} AS a
					LEFT JOIN ( SELECT meta_value, post_id  FROM {$wpdb->postmeta} WHERE `meta_key` = '_sku' ) AS b ON b.post_id = a.ID
					WHERE a.post_type = 'product' AND ( a.ID = '%d' OR b.meta_value LIKE '%%%s%%' OR a.post_title LIKE '%%%s%%' )",
                $product_id > 0 ? $product_id : -1, stripslashes($query), stripslashes($query)
            ), ARRAY_A
            );
            $results         = array();
            if ( is_array($post_meta_infos) && !empty($post_meta_infos) ) {
                foreach ( $post_meta_infos as $value ) {
                    $data          = array();
                    $data['value'] = $value['id'];
                    $data['label'] = esc_html__('Id',
                            'ovic-toolkit') . ': ' . $value['id'] . ( ( strlen($value['title']) > 0 ) ? ' - ' . esc_html__('Title',
                                'ovic-toolkit') . ': ' . $value['title'] : '' ) . ( ( strlen($value['sku']) > 0 ) ? ' - ' . esc_html__('Sku',
                                'ovic-toolkit') . ': ' . $value['sku'] : '' );
                    $results[]     = $data;
                }
            }

            return $results;
        }

        /**
         * Find product by id
         *
         * @param $query
         *
         * @return bool|array
         * @since 4.4
         *
         */
        public static function productIdAutocompleteRender( $query )
        {
            $query = trim($query['value']); // get value from requested
            if ( !empty($query) ) {
                // get product
                $product_object = wc_get_product((int) $query);
                if ( is_object($product_object) ) {
                    $product_sku         = $product_object->get_sku();
                    $product_title       = $product_object->get_title();
                    $product_id          = $product_object->get_id();
                    $product_sku_display = '';
                    if ( !empty($product_sku) ) {
                        $product_sku_display = ' - ' . esc_html__('Sku', 'ovic-toolkit') . ': ' . $product_sku;
                    }
                    $product_title_display = '';
                    if ( !empty($product_title) ) {
                        $product_title_display = ' - ' . esc_html__('Title', 'ovic-toolkit') . ': ' . $product_title;
                    }
                    $product_id_display = esc_html__('Id', 'ovic-toolkit') . ': ' . $product_id;
                    $data               = array();
                    $data['value']      = $product_id;
                    $data['label']      = $product_id_display . $product_title_display . $product_sku_display;

                    return !empty($data) ? $data : false;
                }

                return false;
            }

            return false;
        }

        public static function ovic_vc_bootstrap( $dependency = null, $value_dependency = null )
        {
            $data_value     = array();
            $data_bootstrap = array(
                'boostrap_rows_space' => array(
                    'type'        => 'dropdown',
                    'heading'     => esc_html__('Rows space', 'ovic-toolkit'),
                    'param_name'  => 'boostrap_rows_space',
                    'value'       => array(
                        esc_html__('Default', 'ovic-toolkit') => 'rows-space-0',
                        esc_html__('10px', 'ovic-toolkit')    => 'rows-space-10',
                        esc_html__('20px', 'ovic-toolkit')    => 'rows-space-20',
                        esc_html__('30px', 'ovic-toolkit')    => 'rows-space-30',
                        esc_html__('40px', 'ovic-toolkit')    => 'rows-space-40',
                        esc_html__('50px', 'ovic-toolkit')    => 'rows-space-50',
                        esc_html__('60px', 'ovic-toolkit')    => 'rows-space-60',
                        esc_html__('70px', 'ovic-toolkit')    => 'rows-space-70',
                        esc_html__('80px', 'ovic-toolkit')    => 'rows-space-80',
                        esc_html__('90px', 'ovic-toolkit')    => 'rows-space-90',
                        esc_html__('100px', 'ovic-toolkit')   => 'rows-space-100',
                    ),
                    'std'         => 'rows-space-0',
                    'save_always' => true,
                    'group'       => esc_html__('Boostrap settings', 'ovic-toolkit'),
                    'dependency'  => array(
                        'element' => $dependency, 'value' => array( $value_dependency ),
                    ),
                ),
                'boostrap_bg_items'   => array(
                    'type'        => 'dropdown',
                    'heading'     => esc_html__('Items per row on Desktop', 'ovic-toolkit'),
                    'param_name'  => 'boostrap_bg_items',
                    'value'       => array(
                        esc_html__('1 item', 'ovic-toolkit')  => '12',
                        esc_html__('2 items', 'ovic-toolkit') => '6',
                        esc_html__('3 items', 'ovic-toolkit') => '4',
                        esc_html__('4 items', 'ovic-toolkit') => '3',
                        esc_html__('5 items', 'ovic-toolkit') => '15',
                        esc_html__('6 items', 'ovic-toolkit') => '2',
                    ),
                    'description' => esc_html__('(Item per row on screen resolution of device >= 1500px )',
                        'ovic-toolkit'),
                    'group'       => esc_html__('Boostrap settings', 'ovic-toolkit'),
                    'std'         => '12',
                    'save_always' => true,
                    'dependency'  => array(
                        'element' => $dependency, 'value' => array( $value_dependency ),
                    ),
                ),
                'boostrap_lg_items'   => array(
                    'type'        => 'dropdown',
                    'heading'     => esc_html__('Items per row on Desktop', 'ovic-toolkit'),
                    'param_name'  => 'boostrap_lg_items',
                    'value'       => array(
                        esc_html__('1 item', 'ovic-toolkit')  => '12',
                        esc_html__('2 items', 'ovic-toolkit') => '6',
                        esc_html__('3 items', 'ovic-toolkit') => '4',
                        esc_html__('4 items', 'ovic-toolkit') => '3',
                        esc_html__('5 items', 'ovic-toolkit') => '15',
                        esc_html__('6 items', 'ovic-toolkit') => '2',
                    ),
                    'description' => esc_html__('(Item per row on screen resolution of device >= 1200px and < 1500px )',
                        'ovic-toolkit'),
                    'group'       => esc_html__('Boostrap settings', 'ovic-toolkit'),
                    'std'         => '12',
                    'save_always' => true,
                    'dependency'  => array(
                        'element' => $dependency, 'value' => array( $value_dependency ),
                    ),
                ),
                'boostrap_md_items'   => array(
                    'type'        => 'dropdown',
                    'heading'     => esc_html__('Items per row on landscape tablet', 'ovic-toolkit'),
                    'param_name'  => 'boostrap_md_items',
                    'value'       => array(
                        esc_html__('1 item', 'ovic-toolkit')  => '12',
                        esc_html__('2 items', 'ovic-toolkit') => '6',
                        esc_html__('3 items', 'ovic-toolkit') => '4',
                        esc_html__('4 items', 'ovic-toolkit') => '3',
                        esc_html__('5 items', 'ovic-toolkit') => '15',
                        esc_html__('6 items', 'ovic-toolkit') => '2',
                    ),
                    'description' => esc_html__('(Item per row on screen resolution of device >=992px and < 1200px )',
                        'ovic-toolkit'),
                    'group'       => esc_html__('Boostrap settings', 'ovic-toolkit'),
                    'std'         => '12',
                    'save_always' => true,
                    'dependency'  => array(
                        'element' => $dependency, 'value' => array( $value_dependency ),
                    ),
                ),
                'boostrap_sm_items'   => array(
                    'type'        => 'dropdown',
                    'heading'     => esc_html__('Items per row on portrait tablet', 'ovic-toolkit'),
                    'param_name'  => 'boostrap_sm_items',
                    'value'       => array(
                        esc_html__('1 item', 'ovic-toolkit')  => '12',
                        esc_html__('2 items', 'ovic-toolkit') => '6',
                        esc_html__('3 items', 'ovic-toolkit') => '4',
                        esc_html__('4 items', 'ovic-toolkit') => '3',
                        esc_html__('5 items', 'ovic-toolkit') => '15',
                        esc_html__('6 items', 'ovic-toolkit') => '2',
                    ),
                    'description' => esc_html__('(Item per row on screen resolution of device >=768px and < 992px )',
                        'ovic-toolkit'),
                    'group'       => esc_html__('Boostrap settings', 'ovic-toolkit'),
                    'std'         => '12',
                    'save_always' => true,
                    'dependency'  => array(
                        'element' => $dependency, 'value' => array( $value_dependency ),
                    ),
                ),
                'boostrap_xs_items'   => array(
                    'type'        => 'dropdown',
                    'heading'     => esc_html__('Items per row on Mobile', 'ovic-toolkit'),
                    'param_name'  => 'boostrap_xs_items',
                    'value'       => array(
                        esc_html__('1 item', 'ovic-toolkit')  => '12',
                        esc_html__('2 items', 'ovic-toolkit') => '6',
                        esc_html__('3 items', 'ovic-toolkit') => '4',
                        esc_html__('4 items', 'ovic-toolkit') => '3',
                        esc_html__('5 items', 'ovic-toolkit') => '15',
                        esc_html__('6 items', 'ovic-toolkit') => '2',
                    ),
                    'description' => esc_html__('(Item per row on screen resolution of device >=480  add < 768px )',
                        'ovic-toolkit'),
                    'group'       => esc_html__('Boostrap settings', 'ovic-toolkit'),
                    'std'         => '12',
                    'save_always' => true,
                    'dependency'  => array(
                        'element' => $dependency, 'value' => array( $value_dependency ),
                    ),
                ),
                'boostrap_ts_items'   => array(
                    'type'        => 'dropdown',
                    'heading'     => esc_html__('Items per row on Mobile', 'ovic-toolkit'),
                    'param_name'  => 'boostrap_ts_items',
                    'value'       => array(
                        esc_html__('1 item', 'ovic-toolkit')  => '12',
                        esc_html__('2 items', 'ovic-toolkit') => '6',
                        esc_html__('3 items', 'ovic-toolkit') => '4',
                        esc_html__('4 items', 'ovic-toolkit') => '3',
                        esc_html__('5 items', 'ovic-toolkit') => '15',
                        esc_html__('6 items', 'ovic-toolkit') => '2',
                    ),
                    'description' => esc_html__('(Item per row on screen resolution of device < 480px)',
                        'ovic-toolkit'),
                    'group'       => esc_html__('Boostrap settings', 'ovic-toolkit'),
                    'std'         => '12',
                    'save_always' => true,
                    'dependency'  => array(
                        'element' => $dependency, 'value' => array( $value_dependency ),
                    ),
                ),
            );
            $data_bootstrap = apply_filters('ovic_vc_options_bootstrap', $data_bootstrap, $dependency,
                $value_dependency);
            if ( $dependency == null && $value_dependency == null ) {
                foreach ( $data_bootstrap as $value ) {
                    unset($value['dependency']);
                    $data_value[] = $value;
                }
            } else {
                foreach ( $data_bootstrap as $value ) {
                    $data_value[] = $value;
                }
            }

            return $data_value;
        }

        public static function ovic_vc_carousel( $dependency = null, $value_dependency = null )
        {
            $data_value      = array();
            $data_carousel   = array(
                'owl_number_row'       => array(
                    'type'        => 'dropdown',
                    'value'       => array(
                        esc_html__('1 Row', 'ovic-toolkit')  => '1',
                        esc_html__('2 Rows', 'ovic-toolkit') => '2',
                        esc_html__('3 Rows', 'ovic-toolkit') => '3',
                        esc_html__('4 Rows', 'ovic-toolkit') => '4',
                        esc_html__('5 Rows', 'ovic-toolkit') => '5',
                        esc_html__('6 Rows', 'ovic-toolkit') => '6',
                    ),
                    'std'         => '1',
                    'save_always' => true,
                    'heading'     => esc_html__('The number of rows which are shown on block', 'ovic-toolkit'),
                    'param_name'  => 'owl_number_row',
                    'group'       => esc_html__('Carousel settings', 'ovic-toolkit'),
                    'dependency'  => array(
                        'element' => $dependency, 'value' => array( $value_dependency ),
                    ),
                ),
                'owl_rows_space'       => array(
                    'type'        => 'dropdown',
                    'heading'     => esc_html__('Rows space', 'ovic-toolkit'),
                    'param_name'  => 'owl_rows_space',
                    'value'       => array(
                        esc_html__('Default', 'ovic-toolkit') => 'rows-space-0',
                        esc_html__('5px', 'ovic-toolkit')     => 'rows-space-5',
                        esc_html__('10px', 'ovic-toolkit')    => 'rows-space-10',
                        esc_html__('15px', 'ovic-toolkit')    => 'rows-space-15',
                        esc_html__('20px', 'ovic-toolkit')    => 'rows-space-20',
                        esc_html__('30px', 'ovic-toolkit')    => 'rows-space-30',
                        esc_html__('40px', 'ovic-toolkit')    => 'rows-space-40',
                        esc_html__('50px', 'ovic-toolkit')    => 'rows-space-50',
                        esc_html__('60px', 'ovic-toolkit')    => 'rows-space-60',
                        esc_html__('70px', 'ovic-toolkit')    => 'rows-space-70',
                        esc_html__('80px', 'ovic-toolkit')    => 'rows-space-80',
                        esc_html__('90px', 'ovic-toolkit')    => 'rows-space-90',
                        esc_html__('100px', 'ovic-toolkit')   => 'rows-space-100',
                    ),
                    'std'         => 'rows-space-0',
                    'save_always' => true,
                    'group'       => esc_html__('Carousel settings', 'ovic-toolkit'),
                    'dependency'  => array(
                        'element' => 'owl_number_row', 'value' => array( '2', '3', '4', '5', '6' ),
                    ),
                ),
                'owl_center_mode'      => array(
                    'type'       => 'dropdown',
                    'value'      => array(
                        esc_html__('Yes', 'ovic-toolkit') => 'true',
                        esc_html__('No', 'ovic-toolkit')  => 'false',
                    ),
                    'std'        => 'false',
                    'heading'    => esc_html__('Center Mode', 'ovic-toolkit'),
                    'param_name' => 'owl_center_mode',
                    'group'      => esc_html__('Carousel settings', 'ovic-toolkit'),
                    'dependency' => array(
                        'element' => $dependency, 'value' => array( $value_dependency ),
                    ),
                ),
                'owl_center_padding'   => array(
                    'type'        => 'number',
                    'heading'     => esc_html__('Center Padding', 'ovic-toolkit'),
                    'param_name'  => 'owl_center_padding',
                    'value'       => '50',
                    'min'         => 0,
                    'save_always' => true,
                    'suffix'      => esc_html__('Pixel', 'ovic-toolkit'),
                    'description' => esc_html__('Distance( or space) between 2 item', 'ovic-toolkit'),
                    'group'       => esc_html__('Carousel settings', 'ovic-toolkit'),
                    'dependency'  => array(
                        'element' => 'owl_center_mode', 'value' => array( 'true' ),
                    ),
                ),
                'owl_vertical'         => array(
                    'type'        => 'dropdown',
                    'value'       => array(
                        esc_html__('Yes', 'ovic-toolkit') => 'true',
                        esc_html__('No', 'ovic-toolkit')  => 'false',
                    ),
                    'std'         => 'false',
                    'save_always' => true,
                    'heading'     => esc_html__('Vertical Mode', 'ovic-toolkit'),
                    'param_name'  => 'owl_vertical',
                    'group'       => esc_html__('Carousel settings', 'ovic-toolkit'),
                    'dependency'  => array(
                        'element' => $dependency, 'value' => array( $value_dependency ),
                    ),
                ),
                'owl_verticalswiping'  => array(
                    'type'        => 'dropdown',
                    'value'       => array(
                        esc_html__('Yes', 'ovic-toolkit') => 'true',
                        esc_html__('No', 'ovic-toolkit')  => 'false',
                    ),
                    'std'         => 'false',
                    'save_always' => true,
                    'heading'     => esc_html__('verticalSwiping', 'ovic-toolkit'),
                    'param_name'  => 'owl_verticalswiping',
                    'group'       => esc_html__('Carousel settings', 'ovic-toolkit'),
                    'dependency'  => array(
                        'element' => 'owl_vertical', 'value' => array( 'true' ),
                    ),
                ),
                'owl_autoplay'         => array(
                    'type'        => 'dropdown',
                    'value'       => array(
                        esc_html__('Yes', 'ovic-toolkit') => 'true',
                        esc_html__('No', 'ovic-toolkit')  => 'false',
                    ),
                    'std'         => 'false',
                    'save_always' => true,
                    'heading'     => esc_html__('AutoPlay', 'ovic-toolkit'),
                    'param_name'  => 'owl_autoplay',
                    'group'       => esc_html__('Carousel settings', 'ovic-toolkit'),
                    'dependency'  => array(
                        'element' => $dependency, 'value' => array( $value_dependency ),
                    ),
                ),
                'owl_autoplayspeed'    => array(
                    'type'        => 'number',
                    'heading'     => esc_html__('Autoplay Speed', 'ovic-toolkit'),
                    'param_name'  => 'owl_autoplayspeed',
                    'value'       => '1000',
                    'min'         => 0,
                    'save_always' => true,
                    'suffix'      => esc_html__('milliseconds', 'ovic-toolkit'),
                    'description' => esc_html__('Autoplay speed in milliseconds', 'ovic-toolkit'),
                    'group'       => esc_html__('Carousel settings', 'ovic-toolkit'),
                    'dependency'  => array(
                        'element' => 'owl_autoplay', 'value' => array( 'true' ),
                    ),
                ),
                'owl_navigation'       => array(
                    'type'        => 'dropdown',
                    'value'       => array(
                        esc_html__('No', 'ovic-toolkit')  => 'false',
                        esc_html__('Yes', 'ovic-toolkit') => 'true',
                    ),
                    'std'         => 'true',
                    'save_always' => true,
                    'heading'     => esc_html__('Navigation', 'ovic-toolkit'),
                    'param_name'  => 'owl_navigation',
                    'description' => esc_html__("Show buton 'next' and 'prev' buttons.", 'ovic-toolkit'),
                    'group'       => esc_html__('Carousel settings', 'ovic-toolkit'),
                    'dependency'  => array(
                        'element' => $dependency, 'value' => array( $value_dependency ),
                    ),
                ),
                'owl_navigation_style' => array(
                    'type'        => 'dropdown',
                    'heading'     => esc_html__('Navigation style', 'ovic-toolkit'),
                    'param_name'  => 'owl_navigation_style',
                    'value'       => array(
                        esc_html__('Default', 'ovic-toolkit') => '',
                    ),
                    'std'         => '',
                    'save_always' => true,
                    'group'       => esc_html__('Carousel settings', 'ovic-toolkit'),
                    'dependency'  => array( 'element' => 'owl_navigation', 'value' => array( 'true' ) ),
                ),
                'owl_dots'             => array(
                    'type'        => 'dropdown',
                    'value'       => array(
                        esc_html__('No', 'ovic-toolkit')  => 'false',
                        esc_html__('Yes', 'ovic-toolkit') => 'true',
                    ),
                    'std'         => 'false',
                    'save_always' => true,
                    'heading'     => esc_html__('Dots', 'ovic-toolkit'),
                    'param_name'  => 'owl_dots',
                    'description' => esc_html__("Show dots buttons.", 'ovic-toolkit'),
                    'group'       => esc_html__('Carousel settings', 'ovic-toolkit'),
                    'dependency'  => array(
                        'element' => $dependency, 'value' => array( $value_dependency ),
                    ),
                ),
                'owl_loop'             => array(
                    'type'        => 'dropdown',
                    'value'       => array(
                        esc_html__('Yes', 'ovic-toolkit') => 'true',
                        esc_html__('No', 'ovic-toolkit')  => 'false',
                    ),
                    'std'         => 'false',
                    'save_always' => true,
                    'heading'     => esc_html__('Loop', 'ovic-toolkit'),
                    'param_name'  => 'owl_loop',
                    'description' => esc_html__('Inifnity loop. Duplicate last and first items to get loop illusion.',
                        'ovic-toolkit'),
                    'group'       => esc_html__('Carousel settings', 'ovic-toolkit'),
                    'dependency'  => array(
                        'element' => $dependency, 'value' => array( $value_dependency ),
                    ),
                ),
                'owl_slidespeed'       => array(
                    'type'        => 'number',
                    'heading'     => esc_html__('Slide Speed', 'ovic-toolkit'),
                    'param_name'  => 'owl_slidespeed',
                    'value'       => '300',
                    'min'         => 0,
                    'save_always' => true,
                    'suffix'      => esc_html__('milliseconds', 'ovic-toolkit'),
                    'description' => esc_html__('Slide speed in milliseconds', 'ovic-toolkit'),
                    'group'       => esc_html__('Carousel settings', 'ovic-toolkit'),
                    'dependency'  => array(
                        'element' => $dependency, 'value' => array( $value_dependency ),
                    ),
                ),
                'owl_slide_margin'     => array(
                    'type'        => 'number',
                    'heading'     => esc_html__('Margin', 'ovic-toolkit'),
                    'param_name'  => 'owl_slide_margin',
                    'value'       => '30',
                    'min'         => 0,
                    'save_always' => true,
                    'suffix'      => esc_html__('Pixel', 'ovic-toolkit'),
                    'description' => esc_html__('Distance( or space) between 2 item', 'ovic-toolkit'),
                    'group'       => esc_html__('Carousel settings', 'ovic-toolkit'),
                    'dependency'  => array(
                        'element' => $dependency, 'value' => array( $value_dependency ),
                    ),
                ),
                'owl_ls_items'         => array(
                    'type'        => 'number',
                    'heading'     => esc_html__('The items on desktop (Screen resolution of device >= 1500px )',
                        'ovic-toolkit'),
                    'param_name'  => 'owl_ls_items',
                    'value'       => '4',
                    'suffix'      => esc_html__('item(s)', 'ovic-toolkit'),
                    'group'       => esc_html__('Carousel settings', 'ovic-toolkit'),
                    'min'         => 1,
                    'save_always' => true,
                    'dependency'  => array(
                        'element' => $dependency, 'value' => array( $value_dependency ),
                    ),
                ),
            );
            $data_responsive = Ovic_Framework_Options::ovic_data_responsive_carousel();
            if ( !empty($data_responsive) ) {
                arsort($data_responsive);
                foreach ( $data_responsive as $key => $item ) {
                    if ( $item['screen'] == 1500 ) {
                        $std = '4';
                    } elseif ( $item['screen'] == 1200 ) {
                        $std = '3';
                    } elseif ( $item['screen'] == 992 || $item['screen'] == 768 ) {
                        $std = '2';
                    } elseif ( $item['screen'] == 480 ) {
                        $std = '1';
                    }
                    $data_carousel["owl_{$item['name']}"] = array(
                        'type'        => 'number',
                        'heading'     => $item['title'],
                        'param_name'  => "owl_{$item['name']}",
                        'value'       => isset($std) ? $std : '',
                        'suffix'      => esc_html__('item(s)', 'ovic-toolkit'),
                        'group'       => esc_html__('Carousel settings', 'ovic-toolkit'),
                        'min'         => 1,
                        'save_always' => true,
                        'dependency'  => array(
                            'element' => $dependency, 'value' => array( $value_dependency ),
                        ),
                    );
                }
            }
            $data_carousel = apply_filters('ovic_vc_options_carousel', $data_carousel, $dependency, $value_dependency);
            if ( $dependency == null && $value_dependency == null ) {
                $match = array(
                    'owl_navigation_style',
                    'owl_autoplayspeed',
                    'owl_rows_space',
                    'owl_verticalswiping',
                    'owl_center_padding',
                );
                foreach ( $data_carousel as $value ) {
                    if ( !in_array($value['param_name'], $match) ) {
                        unset($value['dependency']);
                    }
                    $data_value[] = $value;
                }
            } else {
                foreach ( $data_carousel as $value ) {
                    $data_value[] = $value;
                }
            }

            return $data_value;
        }

        public function ovic_params_products()
        {
            $args             = array(
                'type'         => 'post',
                'child_of'     => 0,
                'parent'       => '',
                'orderby'      => 'name',
                'order'        => 'ASC',
                'hide_empty'   => false,
                'hierarchical' => 1,
                'exclude'      => '',
                'include'      => '',
                'number'       => '',
                'taxonomy'     => 'product_cat',
                'pad_counts'   => false,
            );
            $order_by_values  = array(
                '',
                esc_html__('Date', 'ovic-toolkit')               => 'date',
                esc_html__('ID', 'ovic-toolkit')                 => 'ID',
                esc_html__('Author', 'ovic-toolkit')             => 'author',
                esc_html__('Title', 'ovic-toolkit')              => 'title',
                esc_html__('Modified', 'ovic-toolkit')           => 'modified',
                esc_html__('Random', 'ovic-toolkit')             => 'rand',
                esc_html__('Comment count', 'ovic-toolkit')      => 'comment_count',
                esc_html__('Menu order', 'ovic-toolkit')         => 'menu_order',
                esc_html__('Price: low to high', 'ovic-toolkit') => 'price',
                esc_html__('Price: high to low', 'ovic-toolkit') => 'price-desc',
                esc_html__('Average Rating', 'ovic-toolkit')     => 'rating',
                esc_html__('Popularity', 'ovic-toolkit')         => 'popularity',
            );
            $order_way_values = array(
                '',
                esc_html__('Descending', 'ovic-toolkit') => 'DESC',
                esc_html__('Ascending', 'ovic-toolkit')  => 'ASC',
            );
            $attributes_tax   = wc_get_attribute_taxonomies();
            $attributes       = array( '' );
            foreach ( $attributes_tax as $attribute ) {
                $attributes[$attribute->attribute_label] = $attribute->attribute_name;
            }
            // CUSTOM PRODUCT SIZE
            $product_size_width_list = array();
            $width                   = 300;
            $height                  = 300;
            if ( function_exists('wc_get_image_size') ) {
                $size   = wc_get_image_size('shop_catalog');
                $width  = isset($size['width']) ? $size['width'] : $width;
                $height = isset($size['height']) ? $size['height'] : $height;
            }
            for ( $i = 100; $i < $width; $i = $i + 10 ) {
                array_push($product_size_width_list, $i);
            }
            $product_size_list                         = array();
            $product_size_list[$width . 'x' . $height] = $width . 'x' . $height;
            foreach ( $product_size_width_list as $k => $w ) {
                $w      = intval($w);
                $width  = intval($width);
                $height = intval($height);
                if ( isset($width) && $width > 0 ) {
                    $h = round($height * $w / $width);
                } else {
                    $h = $w;
                }
                $product_size_list[$w . 'x' . $h] = $w . 'x' . $h;
            }
            $product_size_list['Custom'] = 'custom';
            // All this move to product
            $categories                  = get_categories($args);
            $product_categories_dropdown = array( '' );
            $this->getCategoryChildsFull(0, $categories, 0, $product_categories_dropdown);
            $param_maps['ovic_products_2'] = array(
                'base'        => 'ovic_products_2',
                'name'        => esc_html__('Ovic: Products', 'ovic-toolkit'),
                'icon'        => OVIC_FRAMEWORK_URI . 'assets/images/shopping-bag.svg',
                'category'    => esc_html__('Ovic Shortcode New', 'ovic-toolkit'),
                'description' => esc_html__('Display Products', 'ovic-toolkit'),
                'params'      => array(
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Title', 'ovic-toolkit'),
                        'param_name'  => 'title',
                        'admin_label' => true,
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Product List style', 'ovic-toolkit'),
                        'param_name'  => 'productsliststyle',
                        'value'       => array(
                            esc_html__('Grid Bootstrap', 'ovic-toolkit') => 'grid',
                            esc_html__('Owl Carousel', 'ovic-toolkit')   => 'owl',
                        ),
                        'save_always' => true,
                        'description' => esc_html__('Select a style for list', 'ovic-toolkit'),
                        'std'         => 'grid',
                    ),
                    array(
                        'type'        => 'select_preview',
                        'heading'     => esc_html__('Product style', 'ovic-toolkit'),
                        'value'       => ovic_product_options('Shortcode'),
                        'default'     => '1',
                        'admin_label' => true,
                        'param_name'  => 'product_style',
                        'description' => esc_html__('Select a style for product item', 'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Image size', 'ovic-toolkit'),
                        'param_name'  => 'product_image_size',
                        'value'       => $product_size_list,
                        'save_always' => true,
                        'description' => esc_html__('Select a size for product', 'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'number',
                        'heading'     => esc_html__('Width', 'ovic-toolkit'),
                        'param_name'  => 'custom_thumb_width',
                        'value'       => $width,
                        'save_always' => true,
                        'suffix'      => esc_html__('px', 'ovic-toolkit'),
                        'dependency'  => array(
                            'element' => 'product_image_size',
                            'value'   => array( 'custom' ),
                        ),
                    ),
                    array(
                        'type'        => 'number',
                        'heading'     => esc_html__('Height', 'ovic-toolkit'),
                        'param_name'  => 'custom_thumb_height',
                        'value'       => $height,
                        'save_always' => true,
                        'suffix'      => esc_html__('px', 'ovic-toolkit'),
                        'dependency'  => array(
                            'element' => 'product_image_size',
                            'value'   => array( 'custom' ),
                        ),
                    ),
                    array(
                        'type'        => 'checkbox',
                        'heading'     => esc_html__('Enable Pagination', 'ovic-toolkit'),
                        'param_name'  => 'pagination',
                        'value'       => array( esc_html__('Enable', 'ovic-toolkit') => '1' ),
                        'save_always' => true,
                        'group'       => esc_html__('Product Options', 'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Target', 'ovic-toolkit'),
                        'param_name'  => 'target',
                        'value'       => array(
                            esc_html__('Recent Products', 'ovic-toolkit')       => 'recent_products',
                            esc_html__('Feature Products', 'ovic-toolkit')      => 'featured_products',
                            esc_html__('Sale Products', 'ovic-toolkit')         => 'sale_products',
                            esc_html__('Best Selling Products', 'ovic-toolkit') => 'best_selling_products',
                            esc_html__('Top Rated Products', 'ovic-toolkit')    => 'top_rated_products',
                            esc_html__('Products', 'ovic-toolkit')              => 'products',
                            esc_html__('Products Category', 'ovic-toolkit')     => 'product_category',
                            esc_html__('Products Attribute', 'ovic-toolkit')    => 'product_attribute',
                        ),
                        'save_always' => true,
                        'description' => esc_html__('Choose the target to filter products', 'ovic-toolkit'),
                        'std'         => 'recent_products',
                        'group'       => esc_html__('Product Options', 'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'autocomplete',
                        'heading'     => esc_html__('Products', 'ovic-toolkit'),
                        'param_name'  => 'ids',
                        'settings'    => array(
                            'multiple'      => true,
                            'sortable'      => true,
                            'unique_values' => true,
                            // In UI show results except selected. NB! You should manually check values in backend
                        ),
                        'save_always' => true,
                        'group'       => esc_html__('Product Options', 'ovic-toolkit'),
                        'description' => esc_html__('Enter List of Products', 'ovic-toolkit'),
                        'dependency'  => array(
                            'element' => 'target',
                            'value'   => array( 'products' ),
                        ),
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Categories', 'ovic-toolkit'),
                        'value'       => $product_categories_dropdown,
                        'param_name'  => 'category',
                        'save_always' => true,
                        'group'       => esc_html__('Product Options', 'ovic-toolkit'),
                        'description' => esc_html__('List of product categories', 'ovic-toolkit'),
                    ),
                    array(
                        'type'       => 'hidden',
                        'param_name' => 'skus',
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Per page', 'ovic-toolkit'),
                        'value'       => 6,
                        'param_name'  => 'limit',
                        'save_always' => true,
                        'group'       => esc_html__('Product Options', 'ovic-toolkit'),
                        'description' => esc_html__('How much items per page to show', 'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Order by', 'ovic-toolkit'),
                        'param_name'  => 'orderby',
                        'value'       => $order_by_values,
                        'save_always' => true,
                        'group'       => esc_html__('Product Options', 'ovic-toolkit'),
                        'description' => sprintf(__('Select how to sort retrieved products. More at %s.',
                            'ovic-toolkit'),
                            '<a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters" target="_blank">WordPress codex page</a>'),
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Sort order', 'ovic-toolkit'),
                        'param_name'  => 'order',
                        'value'       => $order_way_values,
                        'save_always' => true,
                        'group'       => esc_html__('Product Options', 'ovic-toolkit'),
                        'description' => sprintf(__('Designates the ascending or descending order. More at %s.',
                            'ovic-toolkit'),
                            '<a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters" target="_blank">WordPress codex page</a>'),
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Attribute', 'ovic-toolkit'),
                        'param_name'  => 'attribute',
                        'value'       => $attributes,
                        'save_always' => true,
                        'group'       => esc_html__('Product Options', 'ovic-toolkit'),
                        'description' => esc_html__('List of product taxonomy attribute', 'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'checkbox',
                        'heading'     => esc_html__('Filter', 'ovic-toolkit'),
                        'param_name'  => 'filter',
                        'value'       => array( 'empty' => 'empty' ),
                        'save_always' => true,
                        'group'       => esc_html__('Product Options', 'ovic-toolkit'),
                        'description' => esc_html__('Taxonomy values', 'ovic-toolkit'),
                        'dependency'  => array(
                            'callback' => 'vcWoocommerceProductAttributeFilterDependencyCallback',
                        ),
                    ),
                    array(
                        'type'       => 'grid',
                        'heading'    => esc_html__('Bootstrap Settings', 'ovic-toolkit'),
                        'param_name' => 'bootstrap',
                        'dependency' => array(
                            'element' => 'productsliststyle',
                            'value'   => array( 'grid' ),
                        ),
                        'group'      => esc_html__('Bootstrap Settings', 'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Rows space', 'ovic-toolkit'),
                        'param_name'  => 'owl_rows_space',
                        'value'       => array(
                            esc_html__('Default', 'ovic-toolkit') => 'rows-space-0',
                            esc_html__('5px', 'ovic-toolkit')     => 'rows-space-5',
                            esc_html__('10px', 'ovic-toolkit')    => 'rows-space-10',
                            esc_html__('15px', 'ovic-toolkit')    => 'rows-space-15',
                            esc_html__('20px', 'ovic-toolkit')    => 'rows-space-20',
                            esc_html__('30px', 'ovic-toolkit')    => 'rows-space-30',
                            esc_html__('40px', 'ovic-toolkit')    => 'rows-space-40',
                            esc_html__('50px', 'ovic-toolkit')    => 'rows-space-50',
                            esc_html__('60px', 'ovic-toolkit')    => 'rows-space-60',
                            esc_html__('70px', 'ovic-toolkit')    => 'rows-space-70',
                            esc_html__('80px', 'ovic-toolkit')    => 'rows-space-80',
                            esc_html__('90px', 'ovic-toolkit')    => 'rows-space-90',
                            esc_html__('100px', 'ovic-toolkit')   => 'rows-space-100',
                        ),
                        'std'         => 'rows-space-0',
                        'save_always' => true,
                        'group'       => esc_html__('Carousel Settings', 'ovic-toolkit'),
                        'dependency'  => array(
                            'element' => 'productsliststyle',
                            'value'   => array( 'owl' ),
                        ),
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Navigation style', 'ovic-toolkit'),
                        'param_name'  => 'owl_navigation_style',
                        'value'       => array(
                            esc_html__('Default', 'ovic-toolkit') => '',
                        ),
                        'std'         => '',
                        'save_always' => true,
                        'group'       => esc_html__('Carousel Settings', 'ovic-toolkit'),
                        'dependency'  => array(
                            'element' => 'productsliststyle',
                            'value'   => array( 'owl' ),
                        ),
                    ),
                    array(
                        'type'        => 'carousel',
                        'heading'     => esc_html__('Carousel Settings', 'ovic-toolkit'),
                        'param_name'  => 'carousel',
                        'dependency'  => array(
                            'element' => 'productsliststyle',
                            'value'   => array( 'owl' ),
                        ),
                        'save_always' => true,
                        'group'       => esc_html__('Carousel Settings', 'ovic-toolkit'),
                    ),
                ),
            );

            return $param_maps['ovic_products_2'];
        }

        public function ovic_param_visual_composer()
        {
            $attributes_tax = array();
            if ( function_exists('wc_get_attribute_taxonomies') ) {
                $attributes_tax = wc_get_attribute_taxonomies();
            }
            $attributes = array();
            if ( is_array($attributes_tax) && count($attributes_tax) > 0 ) {
                foreach ( $attributes_tax as $attribute ) {
                    $attributes[$attribute->attribute_label] = $attribute->attribute_name;
                }
            }
            // CUSTOM PRODUCT SIZE
            $product_size_width_list = array();
            $width                   = 300;
            $height                  = 300;
            $crop                    = 1;
            if ( function_exists('wc_get_image_size') ) {
                $size   = wc_get_image_size('shop_catalog');
                $width  = isset($size['width']) ? $size['width'] : $width;
                $height = isset($size['height']) ? $size['height'] : $height;
                $crop   = isset($size['crop']) ? $size['crop'] : $crop;
            }
            for ( $i = 100; $i < $width; $i = $i + 10 ) {
                array_push($product_size_width_list, $i);
            }
            $product_size_list                         = array();
            $product_size_list[$width . 'x' . $height] = $width . 'x' . $height;
            foreach ( $product_size_width_list as $k => $w ) {
                $w      = intval($w);
                $width  = intval($width);
                $height = intval($height);
                if ( isset($width) && $width > 0 ) {
                    $h = round($height * $w / $width);
                } else {
                    $h = $w;
                }
                $product_size_list[$w . 'x' . $h] = $w . 'x' . $h;
            }
            $product_size_list['Custom'] = 'custom';
            /* Map New Custom menu */
            $all_menu = array();
            $menus    = get_terms('nav_menu', array( 'hide_empty' => false ));
            if ( $menus && count($menus) > 0 ) {
                foreach ( $menus as $m ) {
                    $all_menu[$m->name] = $m->slug;
                }
            }
            $param['ovic_custommenu'] = array(
                'base'        => 'ovic_custommenu',
                'name'        => esc_html__('Ovic: Custom Menu', 'ovic-toolkit'),
                'icon'        => OVIC_FRAMEWORK_URI . 'assets/images/menu.svg',
                'category'    => esc_html__('Ovic Shortcode', 'ovic-toolkit'),
                'description' => esc_html__('Display Custom Menu', 'ovic-toolkit'),
                'params'      => array(
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Title', 'ovic-toolkit'),
                        'param_name'  => 'title',
                        'description' => esc_html__('What text use as a widget title. Leave blank to use default widget title.',
                            'ovic-toolkit'),
                        'admin_label' => true,
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Menu', 'ovic-toolkit'),
                        'value'       => $all_menu,
                        'admin_label' => true,
                        'param_name'  => 'nav_menu',
                        'description' => esc_html__('Select menu to display.', 'ovic-toolkit'),
                    ),
                ),
            );
            $param['ovic_iconbox']    = array(
                'base'        => 'ovic_iconbox',
                'name'        => esc_html__('Ovic: Icon Box', 'ovic-toolkit'),
                'icon'        => OVIC_FRAMEWORK_URI . 'assets/images/happiness.svg',
                'category'    => esc_html__('Ovic Shortcode', 'ovic-toolkit'),
                'description' => esc_html__('Display Icon Box', 'ovic-toolkit'),
                'params'      => array(
                    array(
                        'type'        => 'select_preview',
                        'heading'     => esc_html__('Select style', 'ovic-toolkit'),
                        'value'       => array(
                            'default' => array(
                                'title'   => esc_html__('Default', 'ovic-toolkit'),
                                'preview' => '',
                            ),
                        ),
                        'default'     => 'default',
                        'admin_label' => true,
                        'param_name'  => 'style',
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Title', 'ovic-toolkit'),
                        'param_name'  => 'title',
                        'admin_label' => true,
                    ),
                    array(
                        'param_name' => 'text_content',
                        'heading'    => esc_html__('Content', 'ovic-toolkit'),
                        'type'       => 'textarea',
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Icon library', 'ovic-toolkit'),
                        'value'       => array(
                            esc_html__('Font Awesome', 'ovic-toolkit') => 'fontawesome',
                            esc_html__('Open Iconic', 'ovic-toolkit')  => 'openiconic',
                            esc_html__('Typicons', 'ovic-toolkit')     => 'typicons',
                            esc_html__('Entypo', 'ovic-toolkit')       => 'entypo',
                            esc_html__('Linecons', 'ovic-toolkit')     => 'linecons',
                            esc_html__('Mono Social', 'ovic-toolkit')  => 'monosocial',
                            esc_html__('Material', 'ovic-toolkit')     => 'material',
                            esc_html__('Ovic Fonts', 'ovic-toolkit')   => 'oviccustomfonts',
                        ),
                        'admin_label' => true,
                        'param_name'  => 'type',
                        'description' => esc_html__('Select icon library.', 'ovic-toolkit'),
                    ),
                    array(
                        'param_name'  => 'icon_oviccustomfonts',
                        'heading'     => esc_html__('Icon', 'ovic-toolkit'),
                        'description' => esc_html__('Select icon from library.', 'ovic-toolkit'),
                        'type'        => 'iconpicker',
                        'settings'    => array(
                            'emptyIcon' => false,
                            'type'      => 'oviccustomfonts',
                        ),
                        'dependency'  => array(
                            'element' => 'type',
                            'value'   => 'oviccustomfonts',
                        ),
                    ),
                    array(
                        'type'        => 'iconpicker',
                        'heading'     => esc_html__('Icon', 'ovic-toolkit'),
                        'param_name'  => 'icon_fontawesome',
                        'value'       => 'fa fa-adjust',
                        'settings'    => array(
                            'emptyIcon'    => false,
                            'iconsPerPage' => 100,
                        ),
                        'dependency'  => array(
                            'element' => 'type',
                            'value'   => 'fontawesome',
                        ),
                        'description' => esc_html__('Select icon from library.', 'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'iconpicker',
                        'heading'     => esc_html__('Icon', 'ovic-toolkit'),
                        'param_name'  => 'icon_openiconic',
                        'value'       => 'vc-oi vc-oi-dial',
                        'settings'    => array(
                            'emptyIcon'    => false,
                            'type'         => 'openiconic',
                            'iconsPerPage' => 100,
                        ),
                        'dependency'  => array(
                            'element' => 'type',
                            'value'   => 'openiconic',
                        ),
                        'description' => esc_html__('Select icon from library.', 'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'iconpicker',
                        'heading'     => esc_html__('Icon', 'ovic-toolkit'),
                        'param_name'  => 'icon_typicons',
                        'value'       => 'typcn typcn-adjust-brightness',
                        'settings'    => array(
                            'emptyIcon'    => false,
                            'type'         => 'typicons',
                            'iconsPerPage' => 100,
                        ),
                        'dependency'  => array(
                            'element' => 'type',
                            'value'   => 'typicons',
                        ),
                        'description' => esc_html__('Select icon from library.', 'ovic-toolkit'),
                    ),
                    array(
                        'type'       => 'iconpicker',
                        'heading'    => esc_html__('Icon', 'ovic-toolkit'),
                        'param_name' => 'icon_entypo',
                        'value'      => 'entypo-icon entypo-icon-note',
                        'settings'   => array(
                            'emptyIcon'    => false,
                            'type'         => 'entypo',
                            'iconsPerPage' => 100,
                        ),
                        'dependency' => array(
                            'element' => 'type',
                            'value'   => 'entypo',
                        ),
                    ),
                    array(
                        'type'        => 'iconpicker',
                        'heading'     => esc_html__('Icon', 'ovic-toolkit'),
                        'param_name'  => 'icon_linecons',
                        'value'       => 'vc_li vc_li-heart',
                        'settings'    => array(
                            'emptyIcon'    => false,
                            'type'         => 'linecons',
                            'iconsPerPage' => 100,
                        ),
                        'dependency'  => array(
                            'element' => 'type',
                            'value'   => 'linecons',
                        ),
                        'description' => esc_html__('Select icon from library.', 'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'iconpicker',
                        'heading'     => esc_html__('Icon', 'ovic-toolkit'),
                        'param_name'  => 'icon_monosocial',
                        'value'       => 'vc-mono vc-mono-fivehundredpx',
                        'settings'    => array(
                            'emptyIcon'    => false,
                            'type'         => 'monosocial',
                            'iconsPerPage' => 100,
                        ),
                        'dependency'  => array(
                            'element' => 'type',
                            'value'   => 'monosocial',
                        ),
                        'description' => esc_html__('Select icon from library.', 'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'iconpicker',
                        'heading'     => esc_html__('Icon', 'ovic-toolkit'),
                        'param_name'  => 'icon_material',
                        'value'       => 'vc-material vc-material-cake',
                        'settings'    => array(
                            'emptyIcon'    => false,
                            'type'         => 'material',
                            'iconsPerPage' => 100,
                        ),
                        'dependency'  => array(
                            'element' => 'type',
                            'value'   => 'material',
                        ),
                        'description' => esc_html__('Select icon from library.', 'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'vc_link',
                        'heading'     => esc_html__('Link', 'ovic-toolkit'),
                        'param_name'  => 'link',
                        'description' => esc_html__('The Link to Icon', 'ovic-toolkit'),
                    ),
                ),
            );
            $param['ovic_instagram']  = array(
                'base'        => 'ovic_instagram',
                'name'        => esc_html__('Ovic: Instagram', 'ovic-toolkit'),
                'icon'        => OVIC_FRAMEWORK_URI . 'assets/images/instagram.svg',
                'category'    => esc_html__('Ovic Shortcode', 'ovic-toolkit'),
                'description' => esc_html__('Display Instagram', 'ovic-toolkit'),
                'params'      => array(
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Title', 'ovic-toolkit'),
                        'param_name'  => 'title',
                        'description' => esc_html__('The title of shortcode', 'ovic-toolkit'),
                        'admin_label' => true,
                    ),
                    array(
                        'type'       => 'dropdown',
                        'heading'    => esc_html__('Instagram List style', 'ovic-toolkit'),
                        'param_name' => 'productsliststyle',
                        'value'      => array(
                            esc_html__('Grid Bootstrap', 'ovic-toolkit') => 'grid',
                            esc_html__('Owl Carousel', 'ovic-toolkit')   => 'owl',
                        ),
                        'std'        => 'grid',
                    ),
                    array(
                        'type'       => 'dropdown',
                        'heading'    => esc_html__('Image Source', 'ovic-toolkit'),
                        'param_name' => 'image_source',
                        'value'      => array(
                            esc_html__('From Instagram', 'ovic-toolkit')   => 'instagram',
                            esc_html__('From Local Image', 'ovic-toolkit') => 'gallery',
                        ),
                        'std'        => 'instagram',
                    ),
                    array(
                        'type'       => 'attach_images',
                        'heading'    => esc_html__('Image Gallery', 'ovic-toolkit'),
                        'param_name' => 'image_gallery',
                        'dependency' => array(
                            'element' => 'image_source',
                            'value'   => array( 'gallery' ),
                        ),
                    ),
                    array(
                        'type'       => 'dropdown',
                        'heading'    => esc_html__('Image Resolution', 'ovic-toolkit'),
                        'param_name' => 'image_resolution',
                        'value'      => array(
                            esc_html__('Thumbnail', 'ovic-toolkit')           => 'thumbnail',
                            esc_html__('Low Resolution', 'ovic-toolkit')      => 'low_resolution',
                            esc_html__('Standard Resolution', 'ovic-toolkit') => 'standard_resolution',
                        ),
                        'std'        => 'thumbnail',
                        'dependency' => array(
                            'element' => 'image_source',
                            'value'   => array( 'instagram' ),
                        ),
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('ID Instagram', 'ovic-toolkit'),
                        'param_name'  => 'id_instagram',
                        'admin_label' => true,
                        'dependency'  => array(
                            'element' => 'image_source',
                            'value'   => array( 'instagram' ),
                        ),
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Token Instagram', 'ovic-toolkit'),
                        'param_name'  => 'token',
                        'dependency'  => array(
                            'element' => 'image_source',
                            'value'   => array( 'instagram' ),
                        ),
                        'description' => wp_kses(sprintf('<a href="%s" target="_blank">' . esc_html__('Get Token Instagram Here!',
                                'ovic-toolkit') . '</a>', 'http://instagram.pixelunion.net'),
                            array( 'a' => array( 'href' => array(), 'target' => array() ) )),
                    ),
                    array(
                        'type'        => 'number',
                        'heading'     => esc_html__('Items Instagram', 'ovic-toolkit'),
                        'param_name'  => 'items_limit',
                        'description' => esc_html__('the number items show', 'ovic-toolkit'),
                        'std'         => '4',
                        'dependency'  => array(
                            'element' => 'image_source',
                            'value'   => array( 'instagram' ),
                        ),
                    ),
                ),
            );
            $param['ovic_map']        = array(
                'base'        => 'ovic_map',
                'name'        => esc_html__('Ovic: Google Map', 'ovic-toolkit'),
                'icon'        => OVIC_FRAMEWORK_URI . 'assets/images/google.svg',
                'category'    => esc_html__('Ovic Shortcode', 'ovic-toolkit'),
                'description' => esc_html__('Display Google Map', 'ovic-toolkit'),
                'params'      => array(
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Title', 'ovic-toolkit'),
                        'param_name'  => 'title',
                        'admin_label' => true,
                        'description' => esc_html__('title.', 'ovic-toolkit'),
                        'std'         => 'KuteThemes',
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Phone', 'ovic-toolkit'),
                        'param_name'  => 'phone',
                        'description' => esc_html__('phone.', 'ovic-toolkit'),
                        'std'         => '088-465 9965 02',
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Email', 'ovic-toolkit'),
                        'param_name'  => 'email',
                        'description' => esc_html__('email.', 'ovic-toolkit'),
                        'std'         => 'kutethemes@gmail.com',
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Address', 'ovic-toolkit'),
                        'param_name'  => 'address',
                        'admin_label' => true,
                        'description' => esc_html__('address.', 'ovic-toolkit'),
                        'std'         => esc_html__('Z115 TP. Thai Nguyen', 'ovic-toolkit'),
                    ),
                    array(
                        'type'       => 'number',
                        'heading'    => esc_html__('Map Height', 'ovic-toolkit'),
                        'param_name' => 'map_height',
                        'std'        => '400',
                    ),
                    array(
                        'type'       => 'dropdown',
                        'heading'    => esc_html__('Maps type', 'ovic-toolkit'),
                        'param_name' => 'map_type',
                        'value'      => array(
                            esc_html__('ROADMAP', 'ovic-toolkit')   => 'ROADMAP',
                            esc_html__('SATELLITE', 'ovic-toolkit') => 'SATELLITE',
                            esc_html__('HYBRID', 'ovic-toolkit')    => 'HYBRID',
                            esc_html__('TERRAIN', 'ovic-toolkit')   => 'TERRAIN',
                        ),
                        'std'        => 'ROADMAP',
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Longitude', 'ovic-toolkit'),
                        'param_name'  => 'longitude',
                        'admin_label' => true,
                        'description' => esc_html__('longitude.', 'ovic-toolkit'),
                        'std'         => '105.800286',
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Latitude', 'ovic-toolkit'),
                        'param_name'  => 'latitude',
                        'admin_label' => true,
                        'description' => esc_html__('latitude.', 'ovic-toolkit'),
                        'std'         => '21.587001',
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Zoom', 'ovic-toolkit'),
                        'param_name'  => 'zoom',
                        'admin_label' => true,
                        'description' => esc_html__('zoom.', 'ovic-toolkit'),
                        'std'         => '14',
                    ),
                ),
            );
            $param['ovic_accordion']  = array(
                'name'                    => esc_html__('Ovic: Accordion', 'ovic-toolkit'),
                'base'                    => 'ovic_accordion',
                'icon'                    => 'icon-wpb-ui-accordion',
                'is_container'            => true,
                'show_settings_on_create' => false,
                'as_parent'               => array(
                    'only' => 'vc_tta_section',
                ),
                'category'                => esc_html__('Ovic Shortcode', 'ovic-toolkit'),
                'description'             => esc_html__('Accordions content', 'ovic-toolkit'),
                'params'                  => array(
                    array(
                        'type'        => 'select_preview',
                        'heading'     => esc_html__('Select style', 'ovic-toolkit'),
                        'value'       => array(
                            'default' => array(
                                'title'   => 'Default',
                                'preview' => '',
                            ),
                        ),
                        'default'     => 'default',
                        'admin_label' => true,
                        'param_name'  => 'style',
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Title', 'ovic-toolkit'),
                        'param_name'  => 'tab_title',
                        'description' => esc_html__('The title of shortcode', 'ovic-toolkit'),
                        'admin_label' => true,
                    ),
                    vc_map_add_css_animation(),
                    array(
                        'param_name' => 'ajax_check',
                        'heading'    => esc_html__('Using Ajax Tabs', 'ovic-toolkit'),
                        'type'       => 'dropdown',
                        'value'      => array(
                            esc_html__('Yes', 'ovic-toolkit') => '1',
                            esc_html__('No', 'ovic-toolkit')  => '0',
                        ),
                        'std'        => '0',
                    ),
                    array(
                        'type'       => 'number',
                        'heading'    => esc_html__('Active Section', 'ovic-toolkit'),
                        'param_name' => 'active_section',
                        'std'        => 1,
                    ),
                ),
                'js_view'                 => 'VcBackendTtaAccordionView',
                'custom_markup'           => '
                        <div class="vc_tta-container" data-vc-action="collapseAll">
                            <div class="vc_general vc_tta vc_tta-accordion vc_tta-color-backend-accordion-white vc_tta-style-flat vc_tta-shape-rounded vc_tta-o-shape-group vc_tta-controls-align-left vc_tta-gap-2">
                               <div class="vc_tta-panels vc_clearfix {{container-class}}">
                                  {{ content }}
                                  <div class="vc_tta-panel vc_tta-section-append">
                                     <div class="vc_tta-panel-heading">
                                        <h4 class="vc_tta-panel-title vc_tta-controls-icon-position-left">
                                           <a href="javascript:;" aria-expanded="false" class="vc_tta-backend-add-control">
                                               <span class="vc_tta-title-text">' . esc_attr__('Add Section',
                        'ovic-toolkit') . '</span>
                                                <i class="vc_tta-controls-icon vc_tta-controls-icon-plus"></i>
                                            </a>
                                        </h4>
                                     </div>
                                  </div>
                               </div>
                            </div>
                        </div>',
                'default_content'         => '
                        [vc_tta_section title="' . sprintf('%s %d', esc_attr__('Section', 'ovic-toolkit'), 1) . '"][/vc_tta_section]
                        [vc_tta_section title="' . sprintf('%s %d', esc_attr__('Section', 'ovic-toolkit'), 2) . '"][/vc_tta_section]
					',
            );
            $param['ovic_tabs']       = array(
                'base'                    => 'ovic_tabs',
                'name'                    => esc_html__('Ovic: Tabs', 'ovic-toolkit'),
                'icon'                    => OVIC_FRAMEWORK_URI . 'assets/images/tab.svg',
                'category'                => esc_html__('Ovic Shortcode', 'ovic-toolkit'),
                'description'             => esc_html__('Display Tabs', 'ovic-toolkit'),
                'is_container'            => true,
                'show_settings_on_create' => false,
                'as_parent'               => array(
                    'only' => 'vc_tta_section',
                ),
                'params'                  => array(
                    array(
                        'type'        => 'select_preview',
                        'heading'     => esc_html__('Select style', 'ovic-toolkit'),
                        'value'       => array(
                            'default' => array(
                                'title'   => esc_html__('Default', 'ovic-toolkit'),
                                'preview' => '',
                            ),
                        ),
                        'default'     => 'default',
                        'admin_label' => true,
                        'param_name'  => 'style',
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Title', 'ovic-toolkit'),
                        'param_name'  => 'tab_title',
                        'description' => esc_html__('The title of shortcode', 'ovic-toolkit'),
                        'admin_label' => true,
                    ),
                    vc_map_add_css_animation(),
                    array(
                        'param_name' => 'ajax_check',
                        'heading'    => esc_html__('Using Ajax Tabs', 'ovic-toolkit'),
                        'type'       => 'dropdown',
                        'value'      => array(
                            esc_html__('Yes', 'ovic-toolkit') => '1',
                            esc_html__('No', 'ovic-toolkit')  => '0',
                        ),
                        'std'        => '0',
                    ),
                    array(
                        'type'       => 'number',
                        'heading'    => esc_html__('Active Section', 'ovic-toolkit'),
                        'param_name' => 'active_section',
                        'std'        => 0,
                    ),
                ),
                'js_view'                 => 'VcBackendTtaTabsView',
                'custom_markup'           => '
                    <div class="vc_tta-container" data-vc-action="collapse">
                        <div class="vc_general vc_tta vc_tta-tabs vc_tta-color-backend-tabs-white vc_tta-style-flat vc_tta-shape-rounded vc_tta-spacing-1 vc_tta-tabs-position-top vc_tta-controls-align-left">
                            <div class="vc_tta-tabs-container">'
                    . '<ul class="vc_tta-tabs-list">'
                    . '<li class="vc_tta-tab" data-vc-tab data-vc-target-model-id="{{ model_id }}" data-element_type="vc_tta_section"><a href="javascript:;" data-vc-tabs data-vc-container=".vc_tta" data-vc-target="[data-model-id=\'{{ model_id }}\']" data-vc-target-model-id="{{ model_id }}"><span class="vc_tta-title-text">{{ section_title }}</span></a></li>'
                    . '</ul>
                            </div>
                            <div class="vc_tta-panels vc_clearfix {{container-class}}">
                              {{ content }}
                            </div>
                        </div>
                    </div>',
                'default_content'         => '
                        [vc_tta_section title="' . sprintf('%s %d', esc_html__('Tab', 'ovic-toolkit'), 1) . '"][/vc_tta_section]
                        [vc_tta_section title="' . sprintf('%s %d', esc_html__('Tab', 'ovic-toolkit'), 2) . '"][/vc_tta_section]
                    ',
                'admin_enqueue_js'        => array(
                    vc_asset_url('lib/vc_tabs/vc-tabs.min.js'),
                ),
            );
            $param['ovic_products']   = array(
                'base'        => 'ovic_products',
                'name'        => esc_html__('Ovic: Products', 'ovic-toolkit'),
                'icon'        => OVIC_FRAMEWORK_URI . 'assets/images/shopping-bag.svg',
                'category'    => esc_html__('Ovic Shortcode', 'ovic-toolkit'),
                'description' => esc_html__('Display Products', 'ovic-toolkit'),
                'params'      => array(
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Title', 'ovic-toolkit'),
                        'param_name'  => 'title',
                        'admin_label' => true,
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Product List style', 'ovic-toolkit'),
                        'param_name'  => 'productsliststyle',
                        'value'       => array(
                            esc_html__('Grid Bootstrap', 'ovic-toolkit') => 'grid',
                            esc_html__('Owl Carousel', 'ovic-toolkit')   => 'owl',
                        ),
                        'description' => esc_html__('Select a style for list', 'ovic-toolkit'),
                        'std'         => 'grid',
                    ),
                    array(
                        'type'        => 'select_preview',
                        'heading'     => esc_html__('Product style', 'ovic-toolkit'),
                        'value'       => ovic_product_options('Shortcode'),
                        'default'     => '1',
                        'admin_label' => true,
                        'param_name'  => 'product_style',
                        'description' => esc_html__('Select a style for product item', 'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Image size', 'ovic-toolkit'),
                        'param_name'  => 'product_image_size',
                        'value'       => $product_size_list,
                        'description' => esc_html__('Select a size for product', 'ovic-toolkit'),
                    ),
                    array(
                        'type'       => 'number',
                        'heading'    => esc_html__('Width', 'ovic-toolkit'),
                        'param_name' => 'product_custom_thumb_width',
                        'value'      => $width,
                        'suffix'     => esc_html__('px', 'ovic-toolkit'),
                        'dependency' => array(
                            'element' => 'product_image_size',
                            'value'   => array( 'custom' ),
                        ),
                    ),
                    array(
                        'type'       => 'number',
                        'heading'    => esc_html__('Height', 'ovic-toolkit'),
                        'param_name' => 'product_custom_thumb_height',
                        'value'      => $height,
                        'suffix'     => esc_html__('px', 'ovic-toolkit'),
                        'dependency' => array(
                            'element' => 'product_image_size',
                            'value'   => array( 'custom' ),
                        ),
                    ),
                    /* Products */
                    array(
                        'type'        => 'taxonomy',
                        'heading'     => esc_html__('Product Category', 'ovic-toolkit'),
                        'param_name'  => 'taxonomy',
                        'options'     => array(
                            'multiple'   => true,
                            'hide_empty' => true,
                            'taxonomy'   => 'product_cat',
                        ),
                        'placeholder' => esc_html__('Choose category', 'ovic-toolkit'),
                        'description' => esc_html__('Note: If you want to narrow output, select category(s) above. Only selected categories will be displayed.',
                            'ovic-toolkit'),
                        'group'       => esc_html__('Products options', 'ovic-toolkit'),
                        'dependency'  => array(
                            'element'            => 'target',
                            'value_not_equal_to' => array(
                                'products',
                            ),
                        ),
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Target', 'ovic-toolkit'),
                        'param_name'  => 'target',
                        'value'       => array(
                            esc_html__('Best Selling Products', 'ovic-toolkit') => 'best-selling',
                            esc_html__('Top Rated Products', 'ovic-toolkit')    => 'top-rated',
                            esc_html__('Recent Products', 'ovic-toolkit')       => 'recent-product',
                            esc_html__('Product Category', 'ovic-toolkit')      => 'product-category',
                            esc_html__('Products', 'ovic-toolkit')              => 'products',
                            esc_html__('Featured Products', 'ovic-toolkit')     => 'featured_products',
                            esc_html__('On Sale', 'ovic-toolkit')               => 'on_sale',
                            esc_html__('On New', 'ovic-toolkit')                => 'on_new',
                        ),
                        'description' => esc_html__('Choose the target to filter products', 'ovic-toolkit'),
                        'std'         => 'recent-product',
                        'group'       => esc_html__('Products options', 'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Order by', 'ovic-toolkit'),
                        'param_name'  => 'orderby',
                        'value'       => array(
                            esc_html__('Date', 'ovic-toolkit')          => 'date',
                            esc_html__('ID', 'ovic-toolkit')            => 'ID',
                            esc_html__('Author', 'ovic-toolkit')        => 'author',
                            esc_html__('Title', 'ovic-toolkit')         => 'title',
                            esc_html__('Modified', 'ovic-toolkit')      => 'modified',
                            esc_html__('Random', 'ovic-toolkit')        => 'rand',
                            esc_html__('Comment count', 'ovic-toolkit') => 'comment_count',
                            esc_html__('Menu order', 'ovic-toolkit')    => 'menu_order',
                            esc_html__('Sale price', 'ovic-toolkit')    => '_sale_price',
                        ),
                        'std'         => 'date',
                        'description' => esc_html__('Select how to sort.', 'ovic-toolkit'),
                        'dependency'  => array(
                            'element'            => 'target',
                            'value_not_equal_to' => array(
                                'products',
                            ),
                        ),
                        'group'       => esc_html__('Products options', 'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Order', 'ovic-toolkit'),
                        'param_name'  => 'order',
                        'value'       => array(
                            esc_html__('ASC', 'ovic-toolkit')  => 'ASC',
                            esc_html__('DESC', 'ovic-toolkit') => 'DESC',
                        ),
                        'std'         => 'DESC',
                        'description' => esc_html__('Designates the ascending or descending order.', 'ovic-toolkit'),
                        'dependency'  => array(
                            'element'            => 'target',
                            'value_not_equal_to' => array(
                                'products',
                            ),
                        ),
                        'group'       => esc_html__('Products options', 'ovic-toolkit'),
                    ),
                    array(
                        'type'       => 'number',
                        'heading'    => esc_html__('Product per page', 'ovic-toolkit'),
                        'param_name' => 'per_page',
                        'value'      => 6,
                        'min'        => 1,
                        'dependency' => array(
                            'element'            => 'target',
                            'value_not_equal_to' => array(
                                'products',
                            ),
                        ),
                        'group'      => esc_html__('Products options', 'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'autocomplete',
                        'heading'     => esc_html__('Products', 'ovic-toolkit'),
                        'param_name'  => 'ids',
                        'settings'    => array(
                            'multiple'      => true,
                            'sortable'      => true,
                            'unique_values' => true,
                        ),
                        'save_always' => true,
                        'description' => esc_html__('Enter List of Products', 'ovic-toolkit'),
                        'dependency'  => array(
                            'element' => 'target',
                            'value'   => array( 'products' ),
                        ),
                        'group'       => esc_html__('Products options', 'ovic-toolkit'),
                    ),
                ),
            );
            $param['ovic_slide']      = array(
                'base'                    => 'ovic_slide',
                'name'                    => esc_html__('Ovic: Slide', 'ovic-toolkit'),
                'icon'                    => OVIC_FRAMEWORK_URI . 'assets/images/slider.svg',
                'category'                => esc_html__('Ovic Shortcode', 'ovic-toolkit'),
                'description'             => esc_html__('Display Slide', 'ovic-toolkit'),
                'as_parent'               => array(
                    'only' => 'vc_single_image, vc_custom_heading, ovic_person, vc_column_text, ovic_iconbox, ovic_category, ovic_socials, vc_row',
                ),
                'content_element'         => true,
                'show_settings_on_create' => true,
                'js_view'                 => 'VcColumnView',
                'params'                  => array(
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Title', 'ovic-toolkit'),
                        'param_name'  => 'slider_title',
                        'admin_label' => true,
                    ),
                ),
            );
            $param['ovic_blog']       = array(
                'base'        => 'ovic_blog',
                'name'        => esc_html__('Ovic: Blog', 'ovic-toolkit'),
                'icon'        => OVIC_FRAMEWORK_URI . 'assets/images/blogger.svg',
                'category'    => esc_html__('Ovic Shortcode', 'ovic-toolkit'),
                'description' => esc_html__('Display Blog', 'ovic-toolkit'),
                'params'      => array(
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Title', 'ovic-toolkit'),
                        'param_name'  => 'blog_title',
                        'description' => esc_html__('The title of shortcode', 'ovic-toolkit'),
                        'admin_label' => true,
                    ),
                    array(
                        'type'        => 'loop',
                        'heading'     => esc_html__('Option Query', 'ovic-toolkit'),
                        'param_name'  => 'loop',
                        'save_always' => true,
                        'value'       => 'size:3|order_by:date|post_type:post',
                        'settings'    => array(
                            'size'     => array(
                                'hidden' => false,
                                'value'  => 3,
                            ),
                            'order_by' => array( 'value' => 'date' ),
                        ),
                        'description' => esc_html__('Create WordPress loop, to populate content from your site.',
                            'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'select_preview',
                        'heading'     => esc_html__('Blog style', 'ovic-toolkit'),
                        'value'       => ovic_blog_options(),
                        'default'     => 'style-1',
                        'admin_label' => true,
                        'param_name'  => 'blog_style',
                        'description' => esc_html__('Select a style for blog item', 'ovic-toolkit'),
                    ),
                ),
            );
            $param['ovic_person']     = array(
                'base'        => 'ovic_person',
                'name'        => esc_html__('Ovic: Person', 'ovic-toolkit'),
                'icon'        => OVIC_FRAMEWORK_URI . 'assets/images/rating.svg',
                'category'    => esc_html__('Ovic Shortcode', 'ovic-toolkit'),
                'description' => esc_html__('Display Person', 'ovic-toolkit'),
                'params'      => array(
                    array(
                        'type'       => 'attach_image',
                        'heading'    => esc_html__('Avatar Person', 'ovic-toolkit'),
                        'param_name' => 'avatar',
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Name', 'ovic-toolkit'),
                        'param_name'  => 'name',
                        'description' => esc_html__('Name of Person.', 'ovic-toolkit'),
                        'admin_label' => true,
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Positions', 'ovic-toolkit'),
                        'param_name'  => 'positions',
                        'admin_label' => true,
                    ),
                    array(
                        'type'       => 'textarea',
                        'heading'    => esc_html__('Descriptions', 'ovic-toolkit'),
                        'param_name' => 'desc',
                    ),
                    array(
                        'type'       => 'vc_link',
                        'heading'    => esc_html__('Link', 'ovic-toolkit'),
                        'param_name' => 'link',
                    ),
                ),
            );
            $param['ovic_progress']   = array(
                'base'        => 'ovic_progress',
                'name'        => esc_html__('Ovic: Progress', 'ovic-toolkit'),
                'icon'        => OVIC_FRAMEWORK_URI . 'assets/images/bar-chart.svg',
                'category'    => esc_html__('Ovic Shortcode', 'ovic-toolkit'),
                'description' => esc_html__('Display Progress', 'ovic-toolkit'),
                'params'      => array(
                    array(
                        'type'        => 'param_group',
                        'heading'     => esc_html__('Values', 'ovic-toolkit'),
                        'param_name'  => 'values',
                        'description' => esc_html__('Enter values for graph - value, title and color.', 'ovic-toolkit'),
                        'params'      => array(
                            array(
                                'type'        => 'textfield',
                                'heading'     => esc_html__('Title', 'ovic-toolkit'),
                                'param_name'  => 'title',
                                'admin_label' => true,
                                'description' => esc_html__('shortcode title.', 'ovic-toolkit'),
                            ),
                            array(
                                'type'        => 'number',
                                'heading'     => esc_html__("Percent", 'ovic-toolkit'),
                                'param_name'  => 'percent',
                                'admin_label' => true,
                            ),
                        ),
                    ),
                ),
            );
            $param['ovic_pinmapper']  = array(
                'base'        => 'ovic_pinmapper',
                'name'        => esc_html__('Ovic: Pin Map', 'ovic-toolkit'),
                'icon'        => OVIC_FRAMEWORK_URI . 'assets/images/push-pin.svg',
                'category'    => esc_html__('Ovic Shortcode', 'ovic-toolkit'),
                'description' => esc_html__('Display Pin Map', 'ovic-toolkit'),
                'params'      => array(
                    array(
                        'type'        => 'select_preview',
                        'heading'     => esc_html__('Pinmaper style', 'ovic-toolkit'),
                        'value'       => ovic_pinmapper_options(),
                        'admin_label' => true,
                        'param_name'  => 'pinmaper_style',
                        'description' => esc_html__('Select a style for pinmaper item', 'ovic-toolkit'),
                    ),
                ),
            );
            $socials                  = array();
            $all_socials              = apply_filters('ovic_get_option', 'user_all_social');
            if ( !empty($all_socials) ) {
                foreach ( $all_socials as $key => $social ) {
                    if ( !empty($social['title_social']) ) {
                        $socials[$social['title_social']] = $key;
                    }
                }
            }
            $param['ovic_socials']        = array(
                'base'        => 'ovic_socials',
                'name'        => esc_html__('Ovic: Socials', 'ovic-toolkit'),
                'icon'        => OVIC_FRAMEWORK_URI . 'assets/images/share.svg',
                'category'    => esc_html__('Ovic Shortcode', 'ovic-toolkit'),
                'description' => esc_html__('Display Socials', 'ovic-toolkit'),
                'params'      => array(
                    array(
                        'type'       => 'checkbox',
                        'heading'    => esc_html__('List Social', 'ovic-toolkit'),
                        'param_name' => 'socials',
                        'value'      => $socials,
                    ),
                ),
            );
            $param['ovic_disabled_popup'] = array(
                'base'        => 'ovic_disabled_popup',
                'name'        => esc_html__('Ovic: Disable Popup', 'ovic-toolkit'),
                'icon'        => OVIC_FRAMEWORK_URI . 'assets/images/disabled.svg',
                'category'    => esc_html__('Ovic Shortcode', 'ovic-toolkit'),
                'description' => esc_html__('Display Disable Popup', 'ovic-toolkit'),
                'params'      => array(
                    array(
                        'type'       => 'textfield',
                        'heading'    => esc_html__('Text', 'ovic-toolkit'),
                        'param_name' => 'text',
                        'std'        => esc_html__('Don&rsquo;t show this popup again', 'ovic-toolkit'),
                    ),
                ),
            );
            $param['ovic_360degree']      = array(
                'base'        => 'ovic_360degree',
                'name'        => esc_html__('Ovic: 360 Degree', 'ovic-toolkit'),
                'icon'        => OVIC_FRAMEWORK_URI . 'assets/images/360-degrees.svg',
                'category'    => esc_html__('Ovic Shortcode', 'ovic-toolkit'),
                'description' => esc_html__('Display 360 Degree Image', 'ovic-toolkit'),
                'params'      => array(
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Title', 'ovic-toolkit'),
                        'param_name'  => 'title',
                        'admin_label' => true,
                        'description' => esc_html__('shortcode title.', 'ovic-toolkit'),
                    ),
                    array(
                        'type'       => 'attach_images',
                        'heading'    => esc_html__('Gallery 360 Degree', 'ovic-toolkit'),
                        'param_name' => 'gallery_degree',
                    ),
                ),
            );
            $param['ovic_newsletter']     = array(
                'base'        => 'ovic_newsletter',
                'name'        => esc_html__('Ovic: Newsletter', 'ovic-toolkit'),
                'icon'        => OVIC_FRAMEWORK_URI . 'assets/images/newsletter.svg',
                'category'    => esc_html__('Ovic Shortcode', 'ovic-toolkit'),
                'description' => esc_html__('Display Newsletter', 'ovic-toolkit'),
                'params'      => array(
                    array(
                        'type'       => 'dropdown',
                        'heading'    => esc_html__('Show Mailchimp List', 'ovic-toolkit'),
                        'param_name' => 'show_list',
                        'value'      => array(
                            esc_html__('Yes', 'ovic-toolkit') => 'yes',
                            esc_html__('No', 'ovic-toolkit')  => 'no',
                        ),
                        'std'        => 'no',
                    ),
                    array(
                        'type'       => 'dropdown',
                        'heading'    => esc_html__('Show Field Name', 'ovic-toolkit'),
                        'param_name' => 'field_name',
                        'value'      => array(
                            esc_html__('Yes', 'ovic-toolkit') => 'yes',
                            esc_html__('No', 'ovic-toolkit')  => 'no',
                        ),
                        'std'        => 'no',
                    ),
                    array(
                        'type'       => 'textfield',
                        'heading'    => esc_html__('First Name Text', 'ovic-toolkit'),
                        'param_name' => 'fname_text',
                        'std'        => esc_html__('First Name', 'ovic-toolkit'),
                        'dependency' => array(
                            'element' => 'field_name',
                            'value'   => 'yes',
                        ),
                    ),
                    array(
                        'type'       => 'textfield',
                        'heading'    => esc_html__('Last Name Text', 'ovic-toolkit'),
                        'param_name' => 'lname_text',
                        'std'        => esc_html__('Last Name', 'ovic-toolkit'),
                        'dependency' => array(
                            'element' => 'field_name',
                            'value'   => 'yes',
                        ),
                    ),
                    array(
                        'type'       => 'textfield',
                        'heading'    => esc_html__('Placeholder Text', 'ovic-toolkit'),
                        'param_name' => 'placeholder',
                        'std'        => esc_html__('Your email letter', 'ovic-toolkit'),
                    ),
                    array(
                        'type'       => 'textfield',
                        'heading'    => esc_html__('Button Text', 'ovic-toolkit'),
                        'param_name' => 'button_text',
                        'std'        => esc_html__('Subscribe', 'ovic-toolkit'),
                    ),
                ),
            );
            /* === NEW SHORTCODE === */
            $param_new                     = array();
            $param_new['ovic_instagram_2'] = array(
                'base'        => 'ovic_instagram_2',
                'name'        => esc_html__('Ovic: Instagram', 'ovic-toolkit'),
                'icon'        => OVIC_FRAMEWORK_URI . 'assets/images/instagram.svg',
                'category'    => esc_html__('Ovic Shortcode New', 'ovic-toolkit'),
                'description' => esc_html__('Display Instagram', 'ovic-toolkit'),
                'params'      => array(
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Title', 'ovic-toolkit'),
                        'param_name'  => 'title',
                        'description' => esc_html__('The title of shortcode', 'ovic-toolkit'),
                        'admin_label' => true,
                    ),
                    array(
                        'type'       => 'dropdown',
                        'heading'    => esc_html__('Instagram List style', 'ovic-toolkit'),
                        'param_name' => 'productsliststyle',
                        'value'      => array(
                            esc_html__('Grid Bootstrap', 'ovic-toolkit') => 'grid',
                            esc_html__('Owl Carousel', 'ovic-toolkit')   => 'owl',
                        ),
                        'std'        => 'grid',
                    ), array(
                        'type'       => 'grid',
                        'heading'    => esc_html__('Bootstrap Settings', 'ovic-toolkit'),
                        'param_name' => 'bootstrap',
                        'dependency' => array(
                            'element' => 'productsliststyle',
                            'value'   => array( 'grid' ),
                        ),
                        'group'      => esc_html__('Bootstrap Settings', 'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Rows space', 'ovic-toolkit'),
                        'param_name'  => 'owl_rows_space',
                        'value'       => array(
                            esc_html__('Default', 'ovic-toolkit') => 'rows-space-0',
                            esc_html__('5px', 'ovic-toolkit')     => 'rows-space-5',
                            esc_html__('10px', 'ovic-toolkit')    => 'rows-space-10',
                            esc_html__('15px', 'ovic-toolkit')    => 'rows-space-15',
                            esc_html__('20px', 'ovic-toolkit')    => 'rows-space-20',
                            esc_html__('30px', 'ovic-toolkit')    => 'rows-space-30',
                            esc_html__('40px', 'ovic-toolkit')    => 'rows-space-40',
                            esc_html__('50px', 'ovic-toolkit')    => 'rows-space-50',
                            esc_html__('60px', 'ovic-toolkit')    => 'rows-space-60',
                            esc_html__('70px', 'ovic-toolkit')    => 'rows-space-70',
                            esc_html__('80px', 'ovic-toolkit')    => 'rows-space-80',
                            esc_html__('90px', 'ovic-toolkit')    => 'rows-space-90',
                            esc_html__('100px', 'ovic-toolkit')   => 'rows-space-100',
                        ),
                        'std'         => 'rows-space-0',
                        'save_always' => true,
                        'group'       => esc_html__('Carousel Settings', 'ovic-toolkit'),
                        'dependency'  => array(
                            'element' => 'productsliststyle',
                            'value'   => array( 'owl' ),
                        ),
                    ),
                    array(
                        'type'        => 'carousel',
                        'heading'     => esc_html__('Carousel Settings', 'ovic-toolkit'),
                        'param_name'  => 'carousel',
                        'dependency'  => array(
                            'element' => 'productsliststyle',
                            'value'   => array( 'owl' ),
                        ),
                        'save_always' => true,
                        'group'       => esc_html__('Carousel Settings', 'ovic-toolkit'),
                    ),
                    array(
                        'type'       => 'dropdown',
                        'heading'    => esc_html__('Image Source', 'ovic-toolkit'),
                        'param_name' => 'image_source',
                        'value'      => array(
                            esc_html__('From Instagram', 'ovic-toolkit')   => 'instagram',
                            esc_html__('From Local Image', 'ovic-toolkit') => 'gallery',
                        ),
                        'std'        => 'instagram',
                    ),
                    array(
                        'type'       => 'attach_images',
                        'heading'    => esc_html__('Image Gallery', 'ovic-toolkit'),
                        'param_name' => 'image_gallery',
                        'dependency' => array(
                            'element' => 'image_source',
                            'value'   => array( 'gallery' ),
                        ),
                    ),
                    array(
                        'type'       => 'dropdown',
                        'heading'    => esc_html__('Image Resolution', 'ovic-toolkit'),
                        'param_name' => 'image_resolution',
                        'value'      => array(
                            esc_html__('Thumbnail', 'ovic-toolkit')           => 'thumbnail',
                            esc_html__('Low Resolution', 'ovic-toolkit')      => 'low_resolution',
                            esc_html__('Standard Resolution', 'ovic-toolkit') => 'standard_resolution',
                        ),
                        'std'        => 'thumbnail',
                        'dependency' => array(
                            'element' => 'image_source',
                            'value'   => array( 'instagram' ),
                        ),
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('ID Instagram', 'ovic-toolkit'),
                        'param_name'  => 'id_instagram',
                        'admin_label' => true,
                        'dependency'  => array(
                            'element' => 'image_source',
                            'value'   => array( 'instagram' ),
                        ),
                    ),
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Token Instagram', 'ovic-toolkit'),
                        'param_name'  => 'token',
                        'dependency'  => array(
                            'element' => 'image_source',
                            'value'   => array( 'instagram' ),
                        ),
                        'description' => wp_kses(sprintf('<a href="%s" target="_blank">' . esc_html__('Get Token Instagram Here!',
                                'ovic-toolkit') . '</a>', 'http://instagram.pixelunion.net'),
                            array( 'a' => array( 'href' => array(), 'target' => array() ) )),
                    ),
                    array(
                        'type'        => 'number',
                        'heading'     => esc_html__('Items Instagram', 'ovic-toolkit'),
                        'param_name'  => 'items_limit',
                        'description' => esc_html__('the number items show', 'ovic-toolkit'),
                        'std'         => '4',
                        'dependency'  => array(
                            'element' => 'image_source',
                            'value'   => array( 'instagram' ),
                        ),
                    ),
                ),
            );
            if ( class_exists('WooCommerce') ) {
                $param_new['ovic_products_2'] = $this->ovic_params_products();
            }
            $param_new['ovic_slide_2'] = array(
                'base'                    => 'ovic_slide_2',
                'name'                    => esc_html__('Ovic: Slide', 'ovic-toolkit'),
                'icon'                    => OVIC_FRAMEWORK_URI . 'assets/images/slider.svg',
                'category'                => esc_html__('Ovic Shortcode New', 'ovic-toolkit'),
                'description'             => esc_html__('Display Slide', 'ovic-toolkit'),
                'as_parent'               => array(
                    'only' => 'vc_single_image, vc_custom_heading, ovic_person, vc_column_text, ovic_iconbox, ovic_category, ovic_socials, vc_row',
                ),
                'content_element'         => true,
                'show_settings_on_create' => true,
                'js_view'                 => 'VcColumnView',
                'params'                  => array(
                    array(
                        'type'        => 'textfield',
                        'heading'     => esc_html__('Title', 'ovic-toolkit'),
                        'param_name'  => 'slider_title',
                        'admin_label' => true,
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Rows space', 'ovic-toolkit'),
                        'param_name'  => 'owl_rows_space',
                        'value'       => array(
                            esc_html__('Default', 'ovic-toolkit') => 'rows-space-0',
                            esc_html__('5px', 'ovic-toolkit')     => 'rows-space-5',
                            esc_html__('10px', 'ovic-toolkit')    => 'rows-space-10',
                            esc_html__('15px', 'ovic-toolkit')    => 'rows-space-15',
                            esc_html__('20px', 'ovic-toolkit')    => 'rows-space-20',
                            esc_html__('30px', 'ovic-toolkit')    => 'rows-space-30',
                            esc_html__('40px', 'ovic-toolkit')    => 'rows-space-40',
                            esc_html__('50px', 'ovic-toolkit')    => 'rows-space-50',
                            esc_html__('60px', 'ovic-toolkit')    => 'rows-space-60',
                            esc_html__('70px', 'ovic-toolkit')    => 'rows-space-70',
                            esc_html__('80px', 'ovic-toolkit')    => 'rows-space-80',
                            esc_html__('90px', 'ovic-toolkit')    => 'rows-space-90',
                            esc_html__('100px', 'ovic-toolkit')   => 'rows-space-100',
                        ),
                        'std'         => 'rows-space-0',
                        'save_always' => true,
                        'group'       => esc_html__('Carousel Settings', 'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'dropdown',
                        'heading'     => esc_html__('Navigation style', 'ovic-toolkit'),
                        'param_name'  => 'owl_navigation_style',
                        'value'       => array(
                            esc_html__('Default', 'ovic-toolkit') => '',
                        ),
                        'std'         => '',
                        'save_always' => true,
                        'group'       => esc_html__('Carousel Settings', 'ovic-toolkit'),
                    ),
                    array(
                        'type'        => 'carousel',
                        'heading'     => esc_html__('Carousel Settings', 'ovic-toolkit'),
                        'param_name'  => 'carousel',
                        'save_always' => true,
                        'group'       => esc_html__('Carousel Settings', 'ovic-toolkit'),
                    ),
                ),
            );
            /* === NEW SHORTCODE === */
            $param          = apply_filters('ovic_add_param_visual_composer', $param);
            $this->map_keys = array_keys($param);

            return $param;
        }

        public function ovic_map_shortcode()
        {
            $param_maps = self::ovic_param_visual_composer();
            foreach ( $param_maps as $value ) {
                if ( $value['base'] == 'ovic_products' || $value['base'] == 'ovic_instagram' ) {
                    $value['params'] = array_merge(
                        $value['params'],
                        self::ovic_vc_carousel('productsliststyle', 'owl'),
                        self::ovic_vc_bootstrap('productsliststyle', 'grid')
                    );
                }
                if ( $value['base'] == 'ovic_slide' || $value['base'] == 'ovic_blog' ) {
                    $value['params'] = array_merge(
                        $value['params'],
                        self::ovic_vc_carousel()
                    );
                }
                if ( function_exists('vc_map') ) {
                    vc_map($value);
                }
            }
        }

        private function ovic_get_templates( $template_name )
        {
            $active_plugin_wc = is_plugin_active('woocommerce/woocommerce.php');
            $path_templates   = apply_filters('ovic_templates_shortcode', 'vc_templates');
            if ( $template_name == 'ovic_products' && !$active_plugin_wc ) {
                return;
            }
            $directory_shortcode = '';
            if ( is_file(plugin_dir_path(__FILE__) . 'shortcode/' . $template_name . '.php') ) {
                $directory_shortcode = 'shortcode';
            }
            if ( is_file(get_template_directory() . '/' . $path_templates . '/' . $template_name . '.php') ) {
                $directory_shortcode = get_template_directory() . '/' . $path_templates;
            }
            if ( $directory_shortcode != '' ) {
                include_once $directory_shortcode . '/' . $template_name . '.php';
            }
        }

        function ovic_include_shortcode()
        {
            foreach ( $this->map_keys as $name ) {
                self::ovic_get_templates($name);
            }
        }
    }

    new Ovic_Visual_composer();
}
VcShortcodeAutoloader::getInstance()->includeClass('WPBakeryShortCode_VC_Tta_Accordion');

class WPBakeryShortCode_Ovic_Tabs extends WPBakeryShortCode_VC_Tta_Accordion
{
}

class WPBakeryShortCode_Ovic_Accordion extends WPBakeryShortCode_VC_Tta_Accordion
{
}

class WPBakeryShortCode_Ovic_Slide extends WPBakeryShortCodesContainer
{
}

class WPBakeryShortCode_Ovic_Slide_2 extends WPBakeryShortCodesContainer
{
}