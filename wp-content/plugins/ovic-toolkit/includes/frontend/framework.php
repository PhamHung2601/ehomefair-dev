<?php
/**
 * Ovic Framework setup
 *
 * @author   KHANH
 * @category API
 * @package  Ovic_Framework_Options
 * @since    1.0.1 : <span class="spinner is-active"></span>
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('Ovic_Framework_Options')) {
    class Ovic_Framework_Options
    {
        public function __construct()
        {
            $this->define_constants();
            add_action('plugins_loaded', array($this, 'includes'));
            add_action('wp_enqueue_scripts', array($this, 'front_scripts'));
            add_action('after_setup_theme', array($this, 'setup_theme'));
            add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
            add_action('widgets_init', array($this, 'widgets_init'));
            if (!is_admin()) {
                add_filter('body_class', array($this, 'add_body_class'));
                /* CUSTOM ENQUEUE */
                add_filter('script_loader_src', array($this, 'remove_query_string_version'), 999);
                add_filter('style_loader_src', array($this, 'remove_query_string_version'), 999);
                /* CUSTOM IMAGE ELEMENT */
                add_filter('post_thumbnail_html', array($this, 'ovic_post_thumbnail_html'), 10, 5);
                add_filter('vc_wpb_getimagesize', array($this, 'ovic_vc_wpb_getimagesize'), 10, 3);
                add_filter('wp_kses_allowed_html', array($this, 'ovic_wp_kses_allowed_html'), 10, 2);
                add_filter('wp_get_attachment_url', array($this, 'ovic_wp_get_attachment_url'), 10, 2);
                add_filter('wp_get_attachment_image_attributes', array($this, 'ovic_lazy_attachment_image'), 10, 3);
            }
            add_action('ovic_get_home_url', array($this, 'ovic_get_home_url'));
            /* FILTER */
            add_filter('ovic_get_option', array($this, 'ovic_get_option'), 10, 2);
            add_filter('ovic_carousel_data_attributes', array($this, 'ovic_carousel_data_attributes'), 10, 2);
            add_filter('ovic_getProducts', array($this, 'ovic_getProducts'), 10, 3);
            /* CUSTOM IMAGE ELEMENT */
            add_filter('ovic_resize_image', array($this, 'ovic_resize_image'), 10, 6);
            /* GET TEMPLATE */
            add_action('ovic_product_template', array($this, 'ovic_product_template'));
            add_action('get_template_blog', array($this, 'get_template_blog'));
            /* REGISTER POSTTYPE */
            add_action('init', array($this, 'register_post_type'), 999);
        }

        /**
         * Define Ovic Constants.
         */
        private function define_constants()
        {
            if (!defined('OVIC_FRAMEWORK_VERSION')) {
                define('OVIC_FRAMEWORK_VERSION', OVIC_TOOLKIT_VERSION);
            }
            if (!defined('OVIC_FRAMEWORK_URI')) {
                define('OVIC_FRAMEWORK_URI', plugin_dir_url(__FILE__));
            }
            if (!defined('OVIC_FRAMEWORK_THEME_PATH')) {
                define('OVIC_FRAMEWORK_THEME_PATH', get_template_directory());
            }
            if (!defined('OVIC_FRAMEWORK_PATH')) {
                define('OVIC_FRAMEWORK_PATH', plugin_dir_path(__FILE__));
            }
            if (!defined('OVIC_PRODUCT_PATH')) {
                define('OVIC_PRODUCT_PATH', apply_filters('ovic_woocommece_path', '/woocommerce/product-styles/'));
            }
            if (!defined('OVIC_BLOG_PATH')) {
                define('OVIC_BLOG_PATH', apply_filters('ovic_template_blog_style', '/templates/blog/blog-style/'));
            }
        }

        public function includes()
        {
            if (is_admin()) {
                include_once('includes/ovic-plugins-load.php');
            }
            include_once('theme-options/theme-options.php');
            include_once('includes/ovic-ajax.php');
            include_once('includes/ovic-breadcrumbs.php');
            include_once('includes/ovic-abstracts-widget.php');
            include_once('includes/ovic-helpers.php');
            if (class_exists('Vc_Manager')) {
                include_once('includes/visual-composer/visual-composer.php');
            }
            if (class_exists('WooCommerce')) {
                include_once('includes/woocommerce/template-hook.php');
            }
            /* WIDGET */
            $this->ovic_includes_widgets_template();
        }

        public function add_body_class($classes)
        {
            $classes[] = "ovic-toolkit-".OVIC_TOOLKIT_VERSION;

            return $classes;
        }

        public function ovic_includes_widgets_template()
        {
            $directory_widget = '';
            $widgets_name     = array(
                'widget-socials.php',
                'widget-instagram.php',
                'widget-newsletter.php',
                'widget-iconbox.php',
                'widget-custommenu.php',
            );
            if (class_exists('WooCommerce')) {
                $widgets_name[] = 'widget-price-filter.php';
                $widgets_name[] = 'widget-catalog-ordering.php';
                $widgets_name[] = 'widget-attribute-product.php';
            }
            $widgets_name   = apply_filters('ovic_name_widgets_template', $widgets_name);
            $path_templates = apply_filters('ovic_templates_widgets', 'framework/widgets');
            if (!empty($widgets_name)) {
                foreach ($widgets_name as $widget) {
                    if (is_file(plugin_dir_path(__FILE__).'includes/widgets/'.$widget)) {
                        $directory_widget = 'includes/widgets/'.$widget;
                    }
                    if (is_file(get_template_directory().'/'.$path_templates.'/'.$widget)) {
                        $directory_widget = get_template_directory().'/'.$path_templates.'/'.$widget;
                    }
                    if ($directory_widget != '') {
                        include_once($directory_widget);
                    }
                }
            }
        }

        public function ovic_get_option($option_name = '', $default = '')
        {
            $cs_option = null;
            if (defined('OVIC_CUSTOMIZE')) {
                $cs_option = get_option(OVIC_CUSTOMIZE);
            }
            if (isset($_GET[$option_name])) {
                $default                 = $_GET[$option_name];
                $cs_option[$option_name] = $_GET[$option_name];
            }
            $options = apply_filters('ovic_get_customize_option', $cs_option, $option_name, $default);
            if (!empty($options) && isset($options[$option_name])) {
                $option = $options[$option_name];
                if (is_array($option) && isset($option['multilang']) && $option['multilang'] == true) {
                    if (defined('ICL_LANGUAGE_CODE')) {
                        if (isset($option[ICL_LANGUAGE_CODE])) {
                            return $option[ICL_LANGUAGE_CODE];
                        }
                    } else {
                        $option = reset($option);
                    }
                }

                return (has_filter('ovic_get_demo_option')) ? apply_filters('ovic_get_demo_option', $option_name, $option) : $option;
            } else {
                return $default;
            }
        }

        public function ovic_get_home_url()
        {
            echo get_home_url();
        }

        public function widgets_init()
        {
            $multi_sidebar = $this->ovic_get_option('multi_sidebar', '');
            if (is_array($multi_sidebar) && count($multi_sidebar) > 0) {
                foreach ($multi_sidebar as $sidebar) {
                    if ($sidebar) {
                        register_sidebar(array(
                                'name'          => $sidebar['add_sidebar'],
                                'id'            => 'custom-sidebar-'.sanitize_key($sidebar['add_sidebar']),
                                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                                'after_widget'  => '</div>',
                                'before_title'  => '<h2 class="widgettitle">',
                                'after_title'   => '<span class="arrow"></span></h2>',
                            )
                        );
                    }
                }
            }
        }

        public function admin_scripts()
        {
            wp_enqueue_style('font-awesome', OVIC_FRAMEWORK_URI.'assets/css/font-awesome.min.css');
            wp_enqueue_style('chosen', OVIC_FRAMEWORK_URI.'assets/css/chosen.min.css');
            wp_enqueue_style('themify', OVIC_FRAMEWORK_URI.'assets/css/themify-icons.css');
            wp_enqueue_style('ovic-backend', OVIC_FRAMEWORK_URI.'assets/css/backend.css');
            /* SCRIPTS */
            wp_enqueue_script('chosen', OVIC_FRAMEWORK_URI.'assets/js/libs/chosen.min.js', array(), null, true);
            //wp_enqueue_script( 'ovic-backend', OVIC_FRAMEWORK_URI . 'assets/js/backend.js', array(), null, true );
        }

        public function remove_query_string_version($src)
        {
            $arr_params = array('ver', 'id', 'type', 'version');
            $src        = urldecode(remove_query_arg($arr_params, $src));

            return $src;
        }

        public function front_scripts()
        {
            global $post;
            $enable_typography = $this->ovic_get_option('ovic_enable_typography');
            $typography_group  = $this->ovic_get_option('typography_group');
            if ($enable_typography == 1 && !empty($typography_group)) {
                wp_enqueue_style('ovic-fonts', $this->google_fonts_url(), array(), null);
            }
            if (!is_admin()) {
                wp_dequeue_style('woocommerce_admin_styles');
            }
            wp_enqueue_style('animate-css', OVIC_FRAMEWORK_URI.'assets/css/animate.min.css', array(), '3.7.0');
            wp_enqueue_style('magnific-popup', OVIC_TOOLKIT_PLUGIN_URL.'assets/css/magnific-popup.css');
            wp_enqueue_style('bootstrap', OVIC_FRAMEWORK_URI.'assets/css/bootstrap.min.css');
            wp_enqueue_style('font-awesome', OVIC_FRAMEWORK_URI.'assets/css/font-awesome.min.css');
            wp_enqueue_style('pe-icon-7-stroke', OVIC_FRAMEWORK_URI.'assets/css/pe-icon-7-stroke.min.css');
            wp_enqueue_style('slick', OVIC_FRAMEWORK_URI.'assets/css/slick.min.css', array(), '3.3.7');
            wp_enqueue_style('themify', OVIC_FRAMEWORK_URI.'assets/css/themify-icons.css', array(), '1.8.2');
            wp_enqueue_style('chosen', OVIC_FRAMEWORK_URI.'assets/css/chosen.min.css', array(), '1.8.2');
            wp_enqueue_style('growl', OVIC_FRAMEWORK_URI.'assets/css/growl.min.css', array(), '1.3.5');
            /* SCRIPTS */
            $google_map_api = $this->ovic_get_option('ovic_gmap_api_key');
            if ($google_map_api != '') {
                $map_api = add_query_arg(
                    array(
                        'key'       => $google_map_api,
                        'libraries' => 'places',
                    ),
                    'https://maps.googleapis.com/maps/api/js'
                );
                wp_enqueue_script('ovic-maps-api', esc_url_raw($map_api), array(), false);
            }
            wp_enqueue_script('threesixty', OVIC_FRAMEWORK_URI.'assets/js/libs/threesixty.min.js', array('jquery'), '1.0', true);
            wp_enqueue_script('serialize-object', OVIC_FRAMEWORK_URI.'assets/js/libs/serialize-object.min.js', array('jquery'), '2.5.0', true);
            wp_enqueue_script('magnific-popup', OVIC_TOOLKIT_PLUGIN_URL.'assets/js/jquery.magnific-popup.min.js', array('jquery'), '1.0', true);
            wp_enqueue_script('bootstrap', OVIC_FRAMEWORK_URI.'assets/js/libs/bootstrap.min.js', array(), '3.3.7', true);
            wp_enqueue_script('slick', OVIC_FRAMEWORK_URI.'assets/js/libs/slick.min.js', array(), '1.0.0', true);
            /* http://hilios.github.io/jQuery.countdown/documentation.html */
            wp_enqueue_script('countdown', OVIC_FRAMEWORK_URI.'assets/js/libs/countdown.min.js', array(), '1.0.0', true);
            wp_enqueue_script('chosen', OVIC_FRAMEWORK_URI.'assets/js/libs/chosen.min.js', array(), '1.8.7', true);
            /* http://jquery.eisbehr.de/lazy */
            wp_enqueue_script('lazyload', OVIC_FRAMEWORK_URI.'assets/js/libs/lazyload.min.js', array(), '1.7.9', true);
            wp_enqueue_script('growl', OVIC_FRAMEWORK_URI.'assets/js/libs/growl.min.js', array(), '1.3.5', true);
            wp_enqueue_script('ovic-script', OVIC_FRAMEWORK_URI.'assets/js/frontend.min.js', array(), '1.0', true);
            /* Custom js */
            $ovic_custom_js = $this->ovic_get_option('ovic_ace_script', '');
            $content        = preg_replace('/\s+/', ' ', $ovic_custom_js);
            wp_add_inline_script('ovic-script', $content);
            /* ATTRIBUTE SLIDE THUMB PRODUCT */
            $single_add_to_cart = $this->ovic_get_option('ovic_ajax_add_to_cart');
            $single_thumbnail   = $this->ovic_get_option('ovic_single_product_thumbnail', 'vertical');
            $atts               = array(
                'owl_loop'         => false,
                'owl_slide_margin' => 10,
                'owl_focus_select' => true,
                'owl_ts_items'     => $this->ovic_get_option('ovic_product_thumbnail_ts_items', '2'),
                'owl_xs_items'     => $this->ovic_get_option('ovic_product_thumbnail_xs_items', '2'),
                'owl_sm_items'     => $this->ovic_get_option('ovic_product_thumbnail_sm_items', '3'),
                'owl_md_items'     => $this->ovic_get_option('ovic_product_thumbnail_md_items', '3'),
                'owl_lg_items'     => $this->ovic_get_option('ovic_product_thumbnail_lg_items', '3'),
                'owl_ls_items'     => $this->ovic_get_option('ovic_product_thumbnail_ls_items', '3'),
            );
            if ($single_thumbnail == 'vertical') {
                $atts['owl_vertical']            = true;
                $atts['owl_responsive_vertical'] = 768;
            }
            $atts             = apply_filters('ovic_thumb_product_single_slide', $atts);
            $owl_settings     = explode(' ', apply_filters('ovic_carousel_data_attributes', 'owl_', $atts));
            $slick_data       = isset($owl_settings[3]) ? $owl_settings[3] : '';
            $slick_responsive = isset($owl_settings[6]) ? $owl_settings[6] : '';
            /* AJAX VALUE GLOBAL */
            wp_localize_script('ovic-script', 'ovic_ajax_frontend', array(
                    'ajaxurl'                     => admin_url('admin-ajax.php', 'relative'),
                    'ovic_ajax_url'               => class_exists('OVIC_AJAX') ? OVIC_AJAX::get_endpoint('%%endpoint%%') : admin_url('admin-ajax.php', 'relative'),
                    'security'                    => wp_create_nonce('ovic_ajax_frontend'),
                    'view_cart_notification_text' => apply_filters('ovic_view_cart_notification_text', esc_html__('View Cart', 'ovic-toolkit')),
                    'added_to_cart_text'          => apply_filters('ovic_adding_to_cart_text', esc_html__('Product has been added to cart!', 'ovic-toolkit')),
                    'wc_cart_url'                 => (function_exists('wc_get_cart_url') ? esc_url(wc_get_cart_url()) : ''),
                    'added_to_wishlist_text'      => get_option('yith_wcwl_product_added_text', esc_html__('Product has been added to wishlist!', 'ovic-toolkit')),
                    'removed_from_wishlist_text'  => esc_html__('Product has been removed from wishlist!', 'ovic-toolkit'),
                    'wishlist_url'                => (function_exists('YITH_WCWL') ? esc_url(YITH_WCWL()->get_wishlist_url()) : ''),
                    'browse_wishlist_text'        => get_option('yith_wcwl_browse_wishlist_text', esc_html__('Browse Wishlist', 'ovic-toolkit')),
                    'growl_notice_text'           => esc_html__('Notice!', 'ovic-toolkit'),
                    'growl_duration'              => 3000,
                    'removed_cart_text'           => esc_html__('Product Removed', 'ovic-toolkit'),
                    'wp_nonce_url'                => (function_exists('wc_get_cart_url') ? wp_nonce_url(wc_get_cart_url()) : ''),
                    'data_slick'                  => urldecode($slick_data),
                    'data_responsive'             => urldecode($slick_responsive),
                    'single_add_to_cart'          => $single_add_to_cart,
                )
            );
            /* DEQUEUE SCRIPTS - OPTIMIZER */
            if (class_exists('WPCF7') && is_a($post, 'WP_Post') && !has_shortcode($post->post_content, 'contact-form-7')) {
                wp_dequeue_style('contact-form-7');
                wp_dequeue_script('contact-form-7');
            }
            /* AWESOME REV */
            if (class_exists('RevSliderFront')) {
                remove_action('wp_footer', array('RevSliderFront', 'load_icon_fonts'));
            }
            /* WOOCOMMERCE */
            if (class_exists('WooCommerce')) {
                if (class_exists('YITH_WCQV_Frontend')) {
                    wp_dequeue_style('yith-quick-view');
                }
                if (defined('YITH_WCWL')) {
                    $wishlist_page_id = yith_wcwl_object_id(get_option('yith_wcwl_wishlist_page_id'));
                    if (!is_page($wishlist_page_id)) {
                        wp_dequeue_script('prettyPhoto');
                        wp_dequeue_script('jquery-selectBox');
                        wp_dequeue_style('woocommerce_prettyPhoto_css');
                        wp_dequeue_style('jquery-selectBox');
                        wp_dequeue_style('yith-wcwl-main');
                        wp_dequeue_style('yith-wcwl-user-main');
                    }
                    wp_dequeue_style('yith-wcwl-font-awesome');
                }
                if (!is_product()) {
                    /* PLUGIN GIFT */
                    if (class_exists('Woocommerce_Multiple_Free_Gift')) {
                        wp_dequeue_style('wfg-core-styles');
                        wp_dequeue_style('wfg-styles');
                        wp_dequeue_script('wfg-scripts');
                    }
                    /* PLUGIN SIZE CHART */
                    if (class_exists('Size_Chart_For_Woocommerce')) {
                        wp_dequeue_style('size-chart-for-woocommerce');
                        wp_dequeue_script('size-chart-for-woocommerce');
                    }
                }
                if (class_exists('Vc_Manager')) {
                    wp_dequeue_script('vc_woocommerce-add-to-cart-js');
                }
            }
        }

        /**
         * Register custom fonts.
         */
        public function google_fonts_url()
        {
            $enable_typography = $this->ovic_get_option('ovic_enable_typography');
            $typography_group  = $this->ovic_get_option('typography_group');
            $font_families     = array();
            if ($enable_typography == 1 && !empty($typography_group)) {
                foreach ($typography_group as $typography) {
                    $font_families[] = str_replace(' ', '+', $typography['ovic_typography_font_family']['family']);
                }
            }
            $query_args = array(
                'family' => urlencode(implode('|', $font_families)),
                'subset' => urlencode('latin,latin-ext'),
            );
            $fonts_url  = add_query_arg($query_args, 'https://fonts.googleapis.com/css');

            return esc_url_raw($fonts_url);
        }

        public function custom_inline_css()
        {
            $ace_style         = $this->ovic_get_option('ovic_ace_style', '');
            $enable_typography = $this->ovic_get_option('ovic_enable_typography');
            $typography_group  = $this->ovic_get_option('typography_group');
            $custom_css        = $ace_style;
            if ($enable_typography == 1 && !empty($typography_group)) {
                foreach ($typography_group as $typography) {
                    $custom_css .= "{$typography['ovic_element_tag']}{";
                    if ($typography['ovic_typography_font_family']['family']) {
                        $custom_css .= "font-family: {$typography['ovic_typography_font_family']['family']};";
                    }
                    if ($typography['ovic_typography_font_family']['weight']) {
                        $custom_css .= "font-weight: {$typography['ovic_typography_font_family']['weight']};";
                    }
                    $custom_css .= "font-size: {$typography['ovic_typography_font_size']}px;";
                    $custom_css .= "line-height: {$typography['ovic_typography_line_height']}px;";
                    $custom_css .= "color: {$typography['ovic_body_text_color']};";
                    $custom_css .= "}";
                }
            }
            $css     = apply_filters('ovic_main_custom_css', $custom_css);
            $content = preg_replace('/\s+/', ' ', $css);
            wp_enqueue_style('ovic-style', OVIC_FRAMEWORK_URI.'assets/css/frontend.css', array(), '1.0.0');
            wp_add_inline_style('ovic-style', $content);
        }

        public function setup_theme()
        {
            // Add support for Block Styles.
            add_theme_support('wp-block-styles');
            // Add support for full and wide align images.
            add_theme_support('align-wide');
            // Add support for responsive embedded content.
            add_theme_support('responsive-embeds');
            // Add inline style
            add_action('wp_enqueue_scripts', array($this, 'custom_inline_css'));
        }

        public function get_template_blog($style)
        {
            if (is_file(get_template_directory().OVIC_BLOG_PATH.'content-blog-'.$style.'.php')) {
                get_template_part(OVIC_BLOG_PATH.'content-blog', $style);
            } else {
                echo '<div class="post-thumb">'.get_the_post_thumbnail(get_the_ID(), 'full').'</div>';
                echo '<div class="post-info">
					<ul class="post-meta">
						<li class="date">
							<i class="fa fa-calendar" aria-hidden="true"></i>
							'.get_the_date().'
						</li>
						<li class="author">
							<i class="fa fa-user" aria-hidden="true"></i>
							<span>'.esc_html__('By: ', 'ovic-toolkit').'</span>
							<a href="'.esc_url(get_author_posts_url(get_the_author_meta('ID'))).'">
								'.get_the_author().'
							</a>
						</li>
					</ul>
					<h2 class="post-title"><a href="'.get_permalink().'">'.get_the_title().'</a></h2>
					</div>';
            }
        }

        public function ovic_product_template($product_name)
        {
            $product_path = OVIC_PRODUCT_PATH.'content-product';
            if (is_file(get_template_directory().$product_path.'-'.$product_name.'.php')) {
                get_template_part($product_path, $product_name);
            } else {
                do_action('ovic_default_products');
            }
        }

        public function register_post_type()
        {
            $enable_posttype = $this->ovic_get_option('ovic_enable_posttype');
            $posttype_group  = $this->ovic_get_option('posttype_group', array());
            $taxonomy_group  = $this->ovic_get_option('taxonomy_group', array());
            if ($enable_posttype == 1) {
                if (!empty($posttype_group)) {
                    foreach ($posttype_group as $posttype) {
                        ovic_install_post_type($posttype);
                    }
                }
                if (!empty($taxonomy_group)) {
                    foreach ($taxonomy_group as $taxonomy) {
                        ovic_install_taxonomy($taxonomy);
                    }
                }
            }
        }

        public static function ovic_data_responsive_carousel()
        {
            $responsive = array(
                'desktop'          => array(
                    'screen'   => 1500,
                    'name'     => 'lg_items',
                    'title'    => esc_html__('The items on desktop (Screen resolution of device >= 1200px and < 1500px )', 'ovic-toolkit'),
                    'settings' => array(),
                ),
                'laptop'           => array(
                    'screen'   => 1200,
                    'name'     => 'md_items',
                    'title'    => esc_html__('The items on desktop (Screen resolution of device >= 992px < 1200px )', 'ovic-toolkit'),
                    'settings' => array(),
                ),
                'tablet'           => array(
                    'screen'   => 992,
                    'name'     => 'sm_items',
                    'title'    => esc_html__('The items on tablet (Screen resolution of device >=768px and < 992px )', 'ovic-toolkit'),
                    'settings' => array(),
                ),
                'mobile_landscape' => array(
                    'screen'   => 768,
                    'name'     => 'xs_items',
                    'title'    => esc_html__('The items on mobile landscape(Screen resolution of device >=480px and < 768px)', 'ovic-toolkit'),
                    'settings' => array(),
                ),
                'mobile'           => array(
                    'screen'   => 480,
                    'name'     => 'ts_items',
                    'title'    => esc_html__('The items on mobile (Screen resolution of device < 480px)', 'ovic-toolkit'),
                    'settings' => array(),
                ),
            );

            return apply_filters('ovic_filter_carousel_responsive_screen', $responsive);
        }

        public function ovic_carousel_data_attributes($prefix, $atts)
        {
            $responsive = array();
            $slick      = array();
            $results    = '';
            if (isset($atts[$prefix.'autoplay']) && $atts[$prefix.'autoplay'] == 'true') {
                $slick['autoplay'] = true;
                if (isset($atts[$prefix.'autoplayspeed']) && $atts[$prefix.'autoplay'] == 'true') {
                    $slick['autoplaySpeed'] = intval($atts[$prefix.'autoplayspeed']);
                }
            }
            if (isset($atts[$prefix.'navigation'])) {
                $slick['arrows'] = $atts[$prefix.'navigation'] == 'true' ? true : false;
            }
            if (isset($atts[$prefix.'slide_margin'])) {
                $slick['slidesMargin'] = intval($atts[$prefix.'slide_margin']);
            }
            if (isset($atts[$prefix.'dots'])) {
                $slick['dots'] = $atts[$prefix.'dots'] == 'true' ? true : false;
            }
            if (isset($atts[$prefix.'loop'])) {
                $slick['infinite'] = $atts[$prefix.'loop'] == 'true' ? true : false;
            }
            if (isset($atts[$prefix.'fade'])) {
                $slick['fade'] = $atts[$prefix.'fade'] == 'true' ? true : false;
            }
            if (isset($atts[$prefix.'slidespeed'])) {
                $slick['speed'] = intval($atts[$prefix.'slidespeed']);
            }
            if (isset($atts[$prefix.'ls_items'])) {
                $slick['slidesToShow'] = intval($atts[$prefix.'ls_items']);
            }
            if (isset($atts[$prefix.'vertical']) && $atts[$prefix.'vertical'] == 'true') {
                $slick['vertical'] = true;
                if (isset($atts[$prefix.'verticalswiping']) && $atts[$prefix.'verticalswiping'] == 'true') {
                    $slick['verticalSwiping'] = true;
                }
            }
            if (isset($atts[$prefix.'center_mode']) && $atts[$prefix.'center_mode'] == 'true') {
                $slick['centerMode'] = true;
                if (isset($atts[$prefix.'center_padding'])) {
                    $slick['centerPadding'] = $atts[$prefix.'center_padding'].'px';
                }
            }
            if (isset($atts[$prefix.'focus_select']) && $atts[$prefix.'focus_select'] == 'true') {
                $slick['focusOnSelect'] = true;
            }
            if (isset($atts[$prefix.'number_row'])) {
                $slick['rows'] = intval($atts[$prefix.'number_row']);
            }
            $slick   = apply_filters('ovic_filter_carousel_slick_attributes', $slick, $prefix, $atts);
            $results .= ' data-slick = '.json_encode($slick).' ';
            /* RESPONSIVE */
            $slick_responsive = $this->ovic_data_responsive_carousel();
            foreach ($slick_responsive as $key => $item) {
                if (isset($atts[$prefix.$item['name']]) && intval($atts[$prefix.$item['name']]) > 0) {
                    $responsive[$key] = array(
                        'breakpoint' => $item['screen'],
                        'settings'   => array(
                            'slidesToShow' => intval($atts[$prefix.$item['name']]),
                        ),
                    );
                    if (isset($item['settings']) && !empty($item['settings'])) {
                        $responsive[$key]['settings'] = array_merge($responsive[$key]['settings'], $item['settings']);
                    }
                    /* RESPONSIVE VERTICAL */
                    if (isset($atts[$prefix.'responsive_vertical']) && $atts[$prefix.'responsive_vertical'] >= $item['screen']) {
                        $responsive[$key]['settings']['vertical'] = false;
                        if (isset($atts[$prefix.'slide_margin'])) {
                            $responsive[$key]['settings']['slidesMargin'] = intval($atts[$prefix.'slide_margin']);
                        }
                    }
                    /* RESPONSIVE ROWS */
                    if (isset($atts[$prefix.'responsive_rows']) && $atts[$prefix.'responsive_rows'] >= $item['screen']) {
                        $responsive[$key]['settings']['rows'] = 1;
                    }
                    /* RESPONSIVE MARGIN */
                    if (isset($atts[$prefix.'responsive_margin']) && $atts[$prefix.'responsive_margin'] >= $item['screen']) {
                        if (isset($atts[$prefix.'slide_margin']) && $atts[$prefix.'slide_margin'] > 10) {
                            $responsive[$key]['settings']['slidesMargin'] = 10;
                        }
                    }
                }
            }
            $responsive = apply_filters('ovic_filter_carousel_responsive_attributes', $responsive, $prefix, $atts);
            $results    .= 'data-responsive = '.json_encode(array_values($responsive)).' ';

            return wp_specialchars_decode($results);
        }

        public function ovic_getProducts($atts, $args = array(), $ignore_sticky_posts = 1)
        {
            extract($atts);
            $target            = isset($target) ? $target : 'recent-product';
            $meta_query        = WC()->query->get_meta_query();
            $tax_query         = WC()->query->get_tax_query();
            $args['post_type'] = 'product';
            if (isset($atts['taxonomy']) and $atts['taxonomy']) {
                $tax_query[] = array(
                    'taxonomy' => 'product_cat',
                    'terms'    => is_array($atts['taxonomy']) ? array_map('sanitize_title', $atts['taxonomy']) : array_map('sanitize_title', explode(',', $atts['taxonomy'])),
                    'field'    => 'slug',
                    'operator' => 'IN',
                );
            }
            $args['post_status']         = 'publish';
            $args['ignore_sticky_posts'] = $ignore_sticky_posts;
            $args['suppress_filter']     = true;
            if (isset($atts['per_page']) && $atts['per_page']) {
                $args['posts_per_page'] = $atts['per_page'];
            }
            $ordering_args = WC()->query->get_catalog_ordering_args();
            $orderby       = isset($atts['orderby']) ? $atts['orderby'] : $ordering_args['orderby'];
            $order         = isset($atts['order']) ? $atts['order'] : $ordering_args['order'];
            $meta_key      = isset($atts['meta_key']) ? $atts['meta_key'] : $ordering_args['meta_key'];
            switch ($target):
                case 'best-selling' :
                    $args['meta_key'] = 'total_sales';
                    $args['orderby']  = 'meta_value_num';
                    $args['order']    = $order;
                    break;
                case 'top-rated' :
                    $args['meta_key'] = '_wc_average_rating';
                    $args['orderby']  = 'meta_value_num';
                    $args['order']    = $order;
                    break;
                case 'product-category' :
                    $args['orderby']  = $orderby;
                    $args['order']    = $order;
                    $args['meta_key'] = $meta_key;
                    break;
                case 'products' :
                    $args['posts_per_page'] = -1;
                    if (!empty($ids)) {
                        $args['post__in'] = array_map('trim', explode(',', $ids));
                        $args['orderby']  = 'post__in';
                    }
                    if (!empty($skus)) {
                        $meta_query[] = array(
                            'key'     => '_sku',
                            'value'   => array_map('trim', explode(',', $skus)),
                            'compare' => 'IN',
                        );
                    }
                    break;
                case 'featured_products' :
                    $tax_query[] = array(
                        'taxonomy' => 'product_visibility',
                        'field'    => 'name',
                        'terms'    => 'featured',
                        'operator' => 'IN',
                    );
                    break;
                case 'product_attribute' :
                    $tax_query[] = array(
                        array(
                            'taxonomy' => strstr($atts['attribute'], 'pa_') ? sanitize_title($atts['attribute']) : 'pa_'.sanitize_title($atts['attribute']),
                            'terms'    => $atts['filter'] ? array_map('sanitize_title', explode(',', $atts['filter'])) : array(),
                            'field'    => 'slug',
                            'operator' => 'IN',
                        ),
                    );
                    break;
                case 'on_new' :
                    $newness            = $this->ovic_get_option('ovic_product_newness', 7);    // Newness in days as defined by option
                    $args['date_query'] = array(
                        array(
                            'after'     => ''.$newness.' days ago',
                            'inclusive' => true,
                        ),
                    );
                    if ($orderby == '_sale_price') {
                        $orderby = 'date';
                        $order   = 'DESC';
                    }
                    $args['orderby'] = $orderby;
                    $args['order']   = $order;
                    break;
                case 'on_sale' :
                    $product_ids_on_sale = wc_get_product_ids_on_sale();
                    $args['post__in']    = array_merge(array(0), $product_ids_on_sale);
                    if ($orderby == '_sale_price') {
                        $orderby = 'date';
                        $order   = 'DESC';
                    }
                    $args['orderby'] = $orderby;
                    $args['order']   = $order;
                    break;
                default :
                    $args['orderby'] = $orderby;
                    $args['order']   = $order;
                    if (isset($ordering_args['meta_key'])) {
                        $args['meta_key'] = $ordering_args['meta_key'];
                    }
                    WC()->query->remove_ordering_args();
                    break;
            endswitch;
            $args['meta_query'] = $meta_query;
            $args['tax_query']  = $tax_query;

            return $products = new WP_Query(apply_filters('woocommerce_shortcode_products_query', $args, $atts, $args['post_type']));
        }

        public function ovic_wp_kses_allowed_html($allowedposttags, $context)
        {
            $allowedposttags['img']['data-src']    = true;
            $allowedposttags['img']['data-srcset'] = true;
            $allowedposttags['img']['data-sizes']  = true;

            return $allowedposttags;
        }

        function ovic_wp_get_attachment_url($url, $post_id)
        {
            if (function_exists('jetpack_photon_url')) {
                $url = jetpack_photon_url($url);
            }

            return $url;
        }

        public function ovic_lazy_attachment_image($attr, $attachment, $size)
        {
            $enable_lazy = $this->ovic_get_option('ovic_theme_lazy_load');
            $image_size  = apply_filters('woocommerce_gallery_image_size', 'woocommerce_single');
            if ($size == $image_size && class_exists('WooCommerce')) {
                if (is_product()) {
                    $enable_lazy = 0;
                }
            }
            if ($enable_lazy == 1) {
                list($url, $width, $height) = wp_get_attachment_image_src($attachment->ID, $size);
                $attr['data-src'] = $attr['src'];
                $attr['src']      = "data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22{$width}%22%20height%3D%22{$height}%22%20viewBox%3D%220%200%20{$width}%20{$height}%22%3E%3C%2Fsvg%3E";
                $attr['class']    .= ' lazy';
                if (isset($attr['srcset']) && $attr['srcset'] != '') {
                    $attr['data-srcset'] = $attr['srcset'];
                    $attr['data-sizes']  = $attr['sizes'];
                    unset($attr['srcset']);
                    unset($attr['sizes']);
                }
            }

            return $attr;
        }

        public function ovic_post_thumbnail_html($html, $post_ID, $post_thumbnail_id, $size, $attr)
        {
            $enable_lazy = $this->ovic_get_option('ovic_theme_lazy_load');
            if ($enable_lazy == 1) {
                $html = '<figure>'.$html.'</figure>';
            }

            return $html;
        }

        public function ovic_vc_wpb_getimagesize($img, $attach_id, $params)
        {
            $enable_lazy = $this->ovic_get_option('ovic_theme_lazy_load');
            if ($enable_lazy == 1) {
                $img['thumbnail'] = '<figure>'.$img['thumbnail'].'</figure>';
            }

            return $img;
        }

        public function ovic_get_attachment_image($attachment_id, $src, $width, $height, $lazy)
        {
            $image    = '';
            $img_lazy = "data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22{$width}%22%20height%3D%22{$height}%22%20viewBox%3D%220%200%20{$width}%20{$height}%22%3E%3C%2Fsvg%3E";
            if ($src) {
                $hwstring   = image_hwstring($width, $height);
                $size_class = $width.'x'.$height;
                $attachment = get_post($attachment_id);
                $alt        = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
                if ($alt == '') {
                    $alt = $attachment->post_title;
                }
                $attr = array(
                    'src'   => $src,
                    'class' => "wp-post-image attachment-$size_class size-$size_class",
                    'alt'   => trim(strip_tags($alt)),
                );
                if ($lazy == true) {
                    $attr['src']      = $img_lazy;
                    $attr['data-src'] = $src;
                    $attr['class']    .= ' lazy';
                }
                $attr  = apply_filters('ovic_get_attachment_image_attributes', $attr, $attachment);
                $attr  = array_map('esc_attr', $attr);
                $image = rtrim("<img $hwstring");
                foreach ($attr as $name => $value) {
                    $image .= " $name=".'"'.$value.'"';
                }
                $image .= ' />';
            }

            return array(
                'url'    => $src,
                'width'  => $width,
                'height' => $height,
                'img'    => $image,
            );
        }

        public function ovic_resize_image($attachment_id, $width, $height, $crop = false, $use_lazy = false, $placeholder = true)
        {
            $original          = false;
            $needs_resize      = true;
            $image_src         = array();
            $width             = absint($width);
            $height            = absint($height);
            $enable_lazy       = $this->ovic_get_option('ovic_theme_lazy_load');
            $placeholder_image = $this->ovic_get_option('ovic_placeholder_image');
            if ($enable_lazy != 1 && $use_lazy == true) {
                $use_lazy = false;
            }
            if ($width == false && $height == false) {
                $original = true;
            }
            if (is_numeric($attachment_id)) {
                $image_src     = wp_get_attachment_image_src($attachment_id, 'full');
                $attached_file = get_attached_file($attachment_id);
                // this is not an attachment, let's use the image url
            } elseif (!empty($attachment_id) && @getimagesize($attachment_id)) {
                $img_url       = $attachment_id;
                $file_path     = parse_url($img_url);
                $attached_file = rtrim(ABSPATH, '/').$file_path['path'];
                $orig_size     = @getimagesize($attached_file);
                $image_src[0]  = $img_url;
                $image_src[1]  = $orig_size[0];
                $image_src[2]  = $orig_size[1];
            }

            if (!empty($attached_file)) {
                // checking if the full size
                if ($original == true) {
                    return $this->ovic_get_attachment_image(
                        $attachment_id,
                        $image_src[0],
                        $image_src[1],
                        $image_src[2],
                        $use_lazy
                    );
                }
                // Look through the attachment meta data for an image that fits our size.
                $meta       = wp_get_attachment_metadata($attachment_id);
                $upload_dir = wp_upload_dir();
                $base_dir   = strtolower($upload_dir['basedir']);
                $base_url   = strtolower($upload_dir['baseurl']);
                $src        = trailingslashit($base_url).$meta['file'];
                $path       = trailingslashit($base_dir).$meta['file'];
                foreach ($meta['sizes'] as $key => $size) {
                    if (($size['width'] == $width && $size['height'] == $height) || $key == sprintf('resized-%dx%d', $width, $height)) {
                        if (!empty($size['file'])) {
                            $file = str_replace(basename($path), $size['file'], $path);
                            if (file_exists($file)) {
                                $needs_resize = false;
                                $src          = str_replace(basename($src), $size['file'], $src);
                            }
                        }
                        break;
                    }
                }
                // checking if the file size is larger than the target size
                // if it is smaller or the same size, stop right here and return
                if ($needs_resize) {
                    $resized = image_make_intermediate_size($attached_file, $width, $height, $crop);

                    if (is_wp_error($resized)) {
                        return $this->ovic_get_attachment_image(
                            $attachment_id,
                            $image_src[0],
                            $image_src[1],
                            $image_src[2],
                            $use_lazy
                        );
                    }
                    if (empty($resized)) {
                        $image_no_crop = wp_get_attachment_image_src($attachment_id, array($width, $height));

                        return $this->ovic_get_attachment_image(
                            $attachment_id,
                            $image_no_crop[0],
                            $image_no_crop[1],
                            $image_no_crop[2],
                            $use_lazy
                        );
                    }

                    // Let metadata know about our new size.
                    $key                 = sprintf('resized-%dx%d', $width, $height);
                    $meta['sizes'][$key] = $resized;
                    if (!empty($resized['file'])) {
                        $src = str_replace(basename($src), $resized['file'], $src);
                    }
                    wp_update_attachment_metadata($attachment_id, $meta);

                    // Record in backup sizes so everything's cleaned up when attachment is deleted.
                    $backup_sizes = get_post_meta($attachment_id, '_wp_attachment_backup_sizes', true);
                    if (!is_array($backup_sizes)) {
                        $backup_sizes = array();
                    }
                    $backup_sizes[$key] = $resized;
                    update_post_meta($attachment_id, '_wp_attachment_backup_sizes', $backup_sizes);
                }

                // output image
                return $this->ovic_get_attachment_image(
                    $attachment_id,
                    $src,
                    $width,
                    $height,
                    $use_lazy
                );
            } elseif (!empty($image_src)) {
                return $this->ovic_get_attachment_image(
                    $attachment_id,
                    $image_src[0],
                    $image_src[1],
                    $image_src[2],
                    $use_lazy
                );
            }
            // placeholder image
            if ($placeholder) {
                if (!empty($placeholder_image)) {
                    $placeholder_img = $this->ovic_resize_image($placeholder_image, $width, $height, $crop, $use_lazy, $placeholder);
                } else {
                    $placeholder_url = "https://via.placeholder.com/{$width}x{$height}?text={$width}x{$height}";
                    $placeholder_img = array(
                        'url'    => $placeholder_url,
                        'width'  => $width,
                        'height' => $height,
                        'img'    => "<img class='attachment-{$width}x{$height} size-{$width}x{$height}' src='{$placeholder_url}' ".image_hwstring($width, $height)." alt='placeholder'>",
                    );
                }
            } else {
                $placeholder_img = array(
                    'url'    => '',
                    'width'  => '',
                    'height' => '',
                    'img'    => '',
                );
            }

            return $placeholder_img;
        }
    }

    new Ovic_Framework_Options();
}