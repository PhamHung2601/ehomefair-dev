<?php
if (!defined('ABSPATH')) {
    exit; // disable direct access
}
/**
 * Define replace class import.
 *
 * @string  do_action( 'ovic_update_menu_meta', $id, $item['postmeta'] ); ( Line: 842 )
 */
if (!class_exists('Ovic_Import_Data')) {
    class Ovic_Import_Data
    {
        public $data_demos    = array();
        public $default_demo  = array();
        public $data_import   = array();
        public $data_advanced = array();
        public $data_megamenu = array();
        public $woo_pages;
        public $woo_catalog;
        public $woo_single;
        public $woo_thumbnail;
        public $woo_ratio;
        public $options;
        public $ovic_options;
        public $cs_options;
        public $redux_options;
        public $has_config    = false;

        public function __construct()
        {
            global $pagenow;

            if (('admin.php' === $pagenow) && ('ovic-import' !== $_GET['page'])) {
                return;
            }
            $include_import = trailingslashit(get_template_directory()).'importer/importer.php';
            if (!has_filter('ovic_import_config') && file_exists($include_import)) {
                $this->has_config = true;
                include $include_import;
            }
            $this->options();
            $this->ovic_options  = apply_filters('ovic_import_key_ovic_options', '_ovic_customize_options');
            $this->cs_options    = apply_filters('ovic_import_key_cs_options', '_cs_options');
            $this->redux_options = apply_filters('ovic_import_key_redux_options', '_redux_options');
            $this->options       = get_option('_ovic_import_checker') !== false ? get_option('_ovic_import_checker') : 0;
            /* ENQUEUE SCRIPT */
            add_action('ovic_importer_page_content', array($this, 'importer_page_content'));
            add_action('admin_enqueue_scripts', array($this, 'register_scripts'), 999);
            /* REGISTER FUNCTION ACTION */
            add_action('ovic_before_content_import', array($this, 'config_before_import'));
            add_action('ovic_after_content_import', array($this, 'update_mega_menu'));
            /* REGISTER AJAX ACTION */
            add_action('wp_ajax_ovic_import_content', array($this, 'ovic_import_content'));
            /* CUSTOM IMPORT META */
            add_action('ovic_update_menu_meta', array($this, 'process_nav_menu_meta'), 10, 2);
        }

        public function options()
        {
            $registed_menu = array(
                'primary' => esc_html__('Primary Menu', 'ovic-import'),
            );
            $menu_location = array(
                'primary' => 'Primary Menu',
            );
            $data_filter   = array(
                'data_advanced' => array(
                    'att' => esc_html__('Demo Attachments', 'ovic-import'),
                    'rtl' => esc_html__('RTL Demo Content', 'ovic-import'),
                    'wid' => esc_html__('Import Widget', 'ovic-import'),
                    'rev' => esc_html__('Slider Revolution', 'ovic-import'),
                ),
                'data_import'   => array(
                    'main_demo'        => 'https://kutethemes.com/',
                    'theme_option'     => get_template_directory().'/importer/data/theme-options.txt',
                    'setting_option'   => get_template_directory().'/importer/data/setting-options.txt',
                    'content_path'     => get_template_directory().'/importer/data/content.xml',
                    'content_path_rtl' => get_template_directory().'/importer/data/content-rtl.xml',
                    'widget_path'      => get_template_directory().'/importer/data/widgets.wie',
                    'revslider_path'   => get_template_directory().'/importer/revsliders/',
                ),
                'default_demo'  => array(
                    'slug'           => 'home-01',
                    'menus'          => $registed_menu,
                    'homepage'       => 'Home 01',
                    'blogpage'       => 'Blog',
                    'menu_locations' => $menu_location,
                    'option_key'     => '_ovic_customize_options',
                ),
                'mega_menu'     => array(),
                'data_demos'    => array(
                    array(
                        'name'           => esc_html__('Demo 01', 'ovic-import'),
                        'slug'           => 'home-01',
                        'menus'          => $registed_menu,
                        'homepage'       => 'Home 01',
                        'blogpage'       => 'Blog',
                        'preview'        => get_theme_file_uri('screenshot.png'),
                        'demo_link'      => 'https://kutethemes.com/',
                        'menu_locations' => $menu_location,
                    ),
                ),
                'woo_pages'     => array(
                    'woocommerce_shop_page_id'      => 'Shop',
                    'woocommerce_cart_page_id'      => 'Cart',
                    'woocommerce_checkout_page_id'  => 'Checkout',
                    'woocommerce_myaccount_page_id' => 'My Account',
                ),
                'woo_ratio'     => '1:1',
                'woo_catalog'   => 300,
                'woo_single'    => 600,
            );
            $import_data   = apply_filters('ovic_import_config', $data_filter);
            // SET DATA DEMOS
            $this->data_demos    = isset($import_data['data_demos']) ? $import_data['data_demos'] : array();
            $this->default_demo  = isset($import_data['default_demo']) ? $import_data['default_demo'] : array();
            $this->data_import   = isset($import_data['data_import']) ? $import_data['data_import'] : array();
            $this->data_advanced = isset($import_data['data_advanced']) ? $import_data['data_advanced'] : array();
            $this->data_megamenu = isset($import_data['mega_menu']) ? $import_data['mega_menu'] : array();
            $this->woo_pages     = isset($import_data['woo_pages']) ? $import_data['woo_pages'] : array();
            $this->woo_catalog   = isset($import_data['woo_catalog']) ? $import_data['woo_catalog'] : 300;
            $this->woo_single    = isset($import_data['woo_single']) ? $import_data['woo_single'] : 600;
            $this->woo_ratio     = isset($import_data['woo_ratio']) ? $import_data['woo_ratio'] : '4:3';
        }

        public function register_scripts($preflix)
        {
            if ($preflix == 'ovic-plugins_page_ovic-import') {
                wp_enqueue_style('ovic-import', OVIC_IMPORT_PLUGIN_URL.'/assets/import.css');
                wp_enqueue_script('ovic-import', OVIC_IMPORT_PLUGIN_URL.'/assets/import.js');
            }
        }

        public function importer_page_content()
        {
            $class      = array(
                'option',
                'content-import'
            );
            $done       = 'HAVE IMPORTED';
            $theme      = wp_get_theme();
            $theme_name = $theme->get('Name');
            $screenshot = $theme->get_screenshot();
            if ($this->options == 1 || $this->has_config == false) {
                $class[] = 'done-import';
            }
            if ($this->has_config == false) {
                $done = 'NO AVAILABLE';
            }
            ?>
            <div class="ovic-importer-wrapper">
                <?php Ovic_Export_Data::export_button(); ?>
                <div class="progress_test" style="height: 5px; background-color: red; width: 0;"></div>
                <h1 class="heading"><?php echo ucfirst(esc_html($theme_name)); ?> - Install Demo Content</h1>
                <div class="header">
                    <div class="main">
                        <div class="options theme-browser">
                            <div class="<?php echo esc_attr(implode(' ', $class)); ?>">
                                <div class="inner">
                                    <div class="preview">
                                        <img src="<?php echo esc_url($screenshot); ?>" alt="ovic-import">
                                    </div>
                                    <span class="more-details"><?php echo esc_html($done); ?></span>
                                    <h3 class="demo-name theme-name">IMPORT PRIMARY CONTENT</h3>
                                    <div class="group-control import-actions wrapper-button">
                                        <div class="control-inner">
                                            <?php if ($this->options == 0 && $this->has_config == true): ?>
                                                <span class="spinner"></span>
                                                <button data-full="1"
                                                        data-content="1"
                                                        class="button button-primary ovic-button-import full-content">
                                                    Install
                                                </button>
                                            <?php endif; ?>
                                            <a target="_blank" class="button"
                                               href="<?php echo $this->data_import['main_demo']; ?>">View demo</a>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($this->has_config == true): ?>
                                    <p class="import-attachments">
                                        <input type="checkbox" value="1" name="fetch_attachments"
                                               id="import-attachments"/>
                                        <label for="import-attachments"><?php _e('Download and import file attachments', 'ovic-import'); ?></label>
                                    </p>
                                    <a href="#" class="toggle-adv">
                                        <span class="dashicons dashicons-admin-generic"></span>
                                    </a>
                                    <div class="import-advanced"
                                         <?php if ($this->options == 1): ?>style="display: block;" <?php endif; ?>>
                                        <?php if (!empty($this->data_advanced)): ?>
                                            <?php foreach ($this->data_advanced as $key => $name): ?>
                                                <div class="wrapper-button">
                                                    <span class="spinner"></span>
                                                    <button data-<?php echo esc_attr($key); ?>="1"
                                                            data-content="0"
                                                            class="button button-primary ovic-button-import">
                                                        <?php echo esc_html($name); ?>
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <div class="wrapper-button">
                                            <span class="spinner"></span>
                                            <button data-reset="1"
                                                    class="button button-primary ovic-button-import reset">
                                                Reset
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="note">
                        <h3>Please read before importing:</h3>
                        <p><strong>Maybe duplicated Content and Menu</strong> if using <strong>IMPORT PRIMARY
                                CONTENT</strong> many times</p>
                        <p>Please ensure that you have already <strong>installed</strong> and <strong>activated</strong>
                            All
                            Require Plugins.</p>
                        <p>Please note that importing data only builds a frame for your website. <strong>It Will
                                Import All Raw Demo Contents.</strong></p>
                        <p>Or can import single option with "Gear Button" in left side of main import.</p>
                        <p>It can take a few minutes to complete. <strong>Please don't close your browser while
                                importing.</strong></p>
                        <p><strong>Note: </strong> Importing without attachments help to import faster and you can
                            import attachments after importing contents have done.</p>
                        <h3>Select the options below which you want to import config demo:</h3>
                    </div>
                </div>
                <?php if (!empty($this->data_demos) && $this->has_config == true) : ?>
                    <div class="options theme-browser demo-config">
                        <?php foreach ($this->data_demos as $key => $data): ?>
                            <div id="option-<?php echo esc_attr($key); ?>" class="option content-import">
                                <div class="inner">
                                    <div class="preview">
                                        <img src="<?php echo esc_url($data['preview']); ?>">
                                    </div>
                                    <span class="more-details">HAVE IMPORTED</span>
                                    <h3 class="demo-name theme-name"><?php echo $data['name']; ?></h3>
                                    <div class="group-control import-actions wrapper-button">
                                        <div class="control-inner">
                                            <span class="spinner"></span>
                                            <button data-id="<?php echo esc_attr($key); ?>"
                                                    data-slug="<?php echo esc_attr($data['slug']); ?>"
                                                    data-content="<?php echo (isset($data['content_path'])) ? 1 : 0; ?>"
                                                    class="button button-primary ovic-button-import">Install
                                            </button>
                                            <a target="_blank" class="button"
                                               href="<?php echo esc_url($data['demo_link']); ?>">View demo</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php
        }

        public function config_before_import($data)
        {
            /* INCLUDE CLASS */
            $this->include_importer_classes();
            $this->include_importer_woocommerce();
            /* IMPORT THEME OPTIONS */
            $this->import_setting_options($data);
            $this->import_theme_options($data);
        }

        /* Include Importer Classes */
        public function include_importer_classes()
        {
            if (!class_exists('Ovic_WP_Import')) {
                include_once dirname(__FILE__).'/vendor/wordpress-importer.php';
            }
        }

        public function include_importer_woocommerce()
        {
            if (class_exists('WooCommerce')) {
                global $wpdb;
                if (current_user_can('administrator')) {
                    $attributes = array(
                        array(
                            'attribute_label'   => 'Color',
                            'attribute_name'    => 'color',
                            'attribute_type'    => 'box_style', // text, box_style, select
                            'attribute_orderby' => 'menu_order',
                            'attribute_public'  => '0',
                            'attribute_size'    => '40x40',
                        ),
                        array(
                            'attribute_label'   => 'Size',
                            'attribute_name'    => 'size',
                            'attribute_type'    => 'select', // text, box_style, select
                            'attribute_orderby' => 'menu_order',
                            'attribute_public'  => '0',
                            'attribute_size'    => '40x40',
                        ),
                    );
                    $attributes = apply_filters('ovic_import_wooCommerce_attributes', $attributes);
                    foreach ($attributes as $attribute):
                        if (empty($attribute['attribute_name']) || empty($attribute['attribute_label'])) {
                            return new WP_Error('error', __('Please, provide an attribute name and slug.', 'ovic-import'));
                        } elseif (($valid_attribute_name = $this->wc_valid_attribute_name($attribute['attribute_name'])) && is_wp_error($valid_attribute_name)) {
                            return $valid_attribute_name;
                        } elseif (taxonomy_exists(wc_attribute_taxonomy_name($attribute['attribute_name']))) {
                            return new WP_Error('error', sprintf(__('Slug "%s" is already in use. Change it, please.', 'ovic-import'), sanitize_title($attribute['attribute_name'])));
                        }
                        $wpdb->insert($wpdb->prefix.'woocommerce_attribute_taxonomies', $attribute);
                        do_action('woocommerce_attribute_added', $wpdb->insert_id, $attribute);
                        $attribute_name = wc_sanitize_taxonomy_name('pa_'.$attribute['attribute_name']);
                        if (!taxonomy_exists($attribute_name)) {
                            $args = array(
                                'hierarchical' => true,
                                'show_ui'      => false,
                                'query_var'    => true,
                                'rewrite'      => false,
                            );
                            register_taxonomy($attribute_name, array('product'), $args);
                        }
                        flush_rewrite_rules();
                        delete_transient('wc_attribute_taxonomies');
                    endforeach;
                }
            }

            return false;
        }

        public function import_theme_options($data)
        {
            $theme_option = '';
            if (!empty($this->data_import['theme_option'])) {
                $theme_option = $this->get_file(
                    $this->data_import['theme_option']
                );
            }
            if (!empty($data) && isset($data['id']) && isset($data['slug'])) {
                if (isset($this->data_demos[$data['id']]['theme_option'])) {
                    $theme_option = $this->get_file(
                        $this->data_demos[$data['id']]['theme_option']
                    );
                }
            }
            if (is_file($theme_option)) {
                $source     = file_get_contents($theme_option);
                $option_key = !empty($this->default_demo['option_key']) ? $this->default_demo['option_key'] : $this->ovic_options;

                if (function_exists('ovic_decode_string')) {
                    update_option($this->ovic_options, ovic_decode_string($source));
                }
                if (function_exists('cs_decode_string')) {
                    update_option($this->cs_options, cs_decode_string($source));
                }
                if (class_exists('ReduxFramework')) {
                    update_option($this->redux_options, json_decode($source, true));
                }
                if (class_exists('Ovic_Import_Demo_Content')) {
                    update_option(wp_unslash($option_key), wp_unslash(json_decode($source, true)));
                }
            }
        }

        public function import_setting_options($option_id)
        {
            if (class_exists('Ovic_Settings_Backup')) {
                $theme_option = '';
                if (isset($this->data_import['setting_option']) && $this->data_import['setting_option'] != "") {
                    $theme_option = $this->data_import['setting_option'];
                }
                if (isset($this->data_demos[$option_id]['setting_option'])) {
                    $theme_option = $this->data_demos[$option_id]['setting_option'];
                }
                $data = file_get_contents($theme_option);
                Ovic_Settings_Backup::import_data($data);
            }
        }

        public function wc_valid_attribute_name($attribute_name)
        {
            if (!class_exists('WooCommerce')) {
                return false;
            }
            if (strlen($attribute_name) >= 28) {
                return new WP_Error('error', sprintf(__('Slug "%s" is too long (28 characters max). Shorten it, please.', 'ovic-import'), sanitize_title($attribute_name)));
            } elseif (wc_check_if_attribute_name_is_reserved($attribute_name)) {
                return new WP_Error('error', sprintf(__('Slug "%s" is not allowed because it is a reserved term. Change it, please.', 'ovic-import'), sanitize_title($attribute_name)));
            }

            return true;
        }

        public function option_data_checker($name, $value)
        {
            if (get_option($name) !== false) {
                // The option already exists, so we just update it.
                update_option($name, $value);
            } else {
                // The option hasn't been added yet. We'll add it with $autoload set to 'no'.
                $deprecated = null;
                $autoload   = 'no';
                add_option($name, $value, $deprecated, $autoload);
            }
        }

        public function no_resize_image($sizes)
        {
            return array();
        }

        public function memory_exceeded()
        {
            // Raise memory limit.
            if (function_exists('wp_raise_memory_limit')) {
                wp_raise_memory_limit();
            }
            // Disable error reporting.
            if (function_exists('error_reporting')) {
                error_reporting(0);
            }
            // Do not limit execution time.
            if (function_exists('set_time_limit')) {
                set_time_limit(0);
            }
        }

        public function get_file($path)
        {
            if (strpos($path, 'http') === false) {
                return $path;
            }

            global $wp_filesystem;

            $upload     = wp_upload_dir();
            $filename   = basename($path);
            $path_file  = $upload['basedir'].'/armania-import/'.$filename;
            $path_local = $upload['basedir'].'/armania-import/';

            // download file
            $this->download($path, $path_file);

            if (strpos($path_file, '.zip') !== false) {

                $unzip_file = unzip_file($path_file, $path_local);

                if (is_wp_error($unzip_file)) {
                    // Try another method.
                    if (class_exists('ZipArchive')) {
                        $zip = new ZipArchive;

                        if ($zip->open($path_file)) {
                            $zip->extractTo($path_local);
                        }

                        $zip->close();
                    }
                }

                // Clean up temporary data.
                unlink($path_file) || $wp_filesystem->delete($path_file);

                $path_file = str_replace('.zip', '', $path_file);

            }

            return $path_file;
        }

        public function ovic_import_content()
        {
            /* MORE MEMORY */
            $this->memory_exceeded();

            if (!defined('WP_LOAD_IMPORTERS')) {
                define('WP_LOAD_IMPORTERS', true);
            }
            $content     = 0;
            $options_id  = null;
            $attachments = false;
            $ajax_path   = !empty($this->data_import['content_path']) ? $this->get_file($this->data_import['content_path']) : false;
            $data        = isset($_POST['form']) ? $_POST['form'] : array();
            add_filter('intermediate_image_sizes_advanced', array($this, 'no_resize_image'));
            /* CONTROL IMPORT */
            if (!empty($data) && isset($data['rev'])) {
                $this->import_revslider($data);
                wp_die();
            }
            if (!empty($data) && isset($data['wid'])) {
                $this->import_widget();
                wp_die();
            }
            if (!empty($data) && isset($data['id']) && isset($data['slug'])) {
                if (isset($this->data_demos[$data['id']]['content_path'])) {
                    $attachments = true;
                    $ajax_path   = $this->get_file(
                        $this->data_demos[$data['id']]['content_path']
                    );
                    $this->import_widget($data);
                    $this->import_revslider($data);
                    $this->option_data_checker('_ovic_import_checker', 1);
                } else {
                    add_filter('wp_import_posts',
                        function ($posts) use ($data) {
                            $new_posts = array();
                            foreach ($posts as $post) {
                                if ($post['post_type'] == 'page') {
                                    $new_posts[] = $post;
                                }
                            }

                            return $new_posts;
                        }
                    );
                }
            }
            if (!empty($data) && isset($data['att'])) {
                $attachments = true;
                add_filter('wp_import_posts',
                    function ($posts) {
                        $new_posts = array();
                        foreach ($posts as $post) {
                            if ($post['post_type'] == 'attachment') {
                                $new_posts[] = $post;
                            }
                        }

                        return $new_posts;
                    }
                );
            }
            if (!empty($data) && isset($data['rtl'])) {
                $content = 1;
            }
            if (!empty($data) && isset($data['image'])) {
                $attachments = true;
            }
            if (!empty($data) && isset($data['full'])) {
                $this->import_widget();
                $this->import_revslider($data);
                $this->option_data_checker('_ovic_import_checker', 1);
            }
            if (!empty($data) && isset($data['reset'])) {
                $this->option_data_checker('_ovic_import_checker', 0);
                wp_die();
            }
            /* IMPORT XML */
            do_action('ovic_before_content_import', $data);

            if ($content == 1) {
                $ajax_path = $this->get_file(
                    $this->data_import['content_path_rtl']
                );
            }

            if (is_file($ajax_path)) {
                $importer                    = new Ovic_WP_Import();
                $importer->fetch_attachments = $attachments;
                $importer->import($ajax_path);
            }

            /* IMPORT XML */
            if (!empty($data) && isset($data['id']) && isset($data['slug'])) {
                $this->import_config($this->data_demos[$data['id']]);
            } else {
                $this->import_config($this->default_demo);
            }

            do_action('ovic_after_content_import', $data);

            wp_die();
        }

        /**
         * Prepare a directory.
         *
         * @param  string  $path  Directory path.
         *
         * @return  mixed
         */
        protected static function prepare_directory($path)
        {
            if (!is_dir($path)) {
                $results = explode('/', str_replace('\\', '/', $path));
                $path    = array();
                while (count($results)) {
                    $path[] = current($results);
                    // Shift paths.
                    array_shift($results);
                }
            }

            // Re-build target directory.
            $path = is_array($path) ? implode('/', $path) : $path;

            if (!wp_mkdir_p($path)) {
                return false;
            }

            if (!is_dir($path)) {
                return false;
            }

            return $path;
        }

        /**
         * Fetch a remote URI then return results.
         *
         * @param  string  $uri  Remote URI for fetching content.
         * @param  string  $target  Local file path to store fetched content.
         *
         * @return  mixed
         */
        public static function download($uri, $target = '')
        {
            // download_url function is part of wp-admin.
            if (!function_exists('download_url')) {
                include_once ABSPATH.'wp-admin/includes/file.php';
            }

            include_once ABSPATH.'wp-admin/includes/class-wp-filesystem-base.php';
            include_once ABSPATH.'wp-admin/includes/class-wp-filesystem-direct.php';

            if (!class_exists('WP_Filesystem_Direct')) {
                return false;
            }

            $wp_filesystem = new WP_Filesystem_Direct(null);

            // Download remote content.
            $result = strpos($uri, 'http') !== false ? download_url($uri) : $uri;

            if (is_wp_error($result)) {
                return false;
            }

            if (!empty($target)) {
                // Prepare target directory.
                $path = implode('/', array_slice(explode('/', str_replace('\\', '/', $target)), 0, -1));

                if (!self::prepare_directory($path)) {
                    return false;
                }
                // Move file.
                $wp_filesystem->move($result, $target, true);

                $content = ($content = filesize($target)) ? $content : $wp_filesystem->size($target);
            } else {
                $content = ($content = file_get_contents($result)) ? $content : $wp_filesystem->get_contents($result);

                // Remove downloaded file.
                unlink($result) || $wp_filesystem->delete($result);
            }

            return $content;
        }

        public function install_plugin()
        {
            global $wp_filesystem;
            if (!class_exists('TGM_Plugin_Activation')) {
                return;
            }
            // Install Plugins
            $plugins = TGM_Plugin_Activation::$instance->plugins;
            $tgm     = TGM_Plugin_Activation::$instance;
            if (!empty($plugins)) {
                foreach ($plugins as $plugin) {
                    if ($tgm->is_plugin_active($plugin['slug'])) {
                        continue;
                    } elseif ($tgm->is_plugin_installed($plugin['slug'])) {
                        // Active Plugin
                        activate_plugins($plugin['file_path']);
                        // Disable Visual Composer welcome page redirection.
                        if ('js_composer' == $plugin['slug']) {
                            delete_transient('_vc_page_welcome_redirect');
                        }
                    } else {
                        // get link download
                        $link = $tgm->get_download_url($plugin['slug']);

                        // Download an Unzip
                        if ($link != "") {
                            $file_name        = $plugin['slug'].'.zip';
                            $plugin_path      = ABSPATH.'/wp-content/plugins/';
                            $plugin_path_file = $plugin_path.$file_name;
                            if (self::download($link, $plugin_path_file)) {
                                $unzip_file = unzip_file($plugin_path_file, $plugin_path);
                                if (is_wp_error($unzip_file)) {
                                    // Try another method.
                                    if (class_exists('ZipArchive')) {
                                        $zip = new ZipArchive;

                                        if ($zip->open($plugin_path_file)) {
                                            $zip->extractTo($plugin_path);
                                        }

                                        $zip->close();
                                    }
                                }

                                // Clean up temporary data.
                                unlink($plugin_path_file) || $wp_filesystem->delete($plugin_path_file);
                            }
                        }
                        // Active Plugin
                        activate_plugins($plugin['file_path']);
                    }
                }
            }

            wp_clean_plugins_cache();
        }

        public function process_nav_menu_meta($id, $metas)
        {
            foreach ($metas as $meta) {
                if (trim($meta['key']) == '_ovic_menu_settings') {
                    update_post_meta($id, '_ovic_menu_settings', maybe_unserialize($meta['value']));
                }
                if (trim($meta['key']) == '_menu_item_megamenu_font_icon') {
                    update_post_meta($id, '_menu_item_megamenu_font_icon', maybe_unserialize($meta['value']));
                }
                if (trim($meta['key']) == '_menu_item_megamenu_item_icon_type') {
                    update_post_meta($id, '_menu_item_megamenu_item_icon_type', maybe_unserialize($meta['value']));
                }
                if (trim($meta['key']) == '_menu_item_megamenu_mega_menu_width') {
                    update_post_meta($id, '_menu_item_megamenu_mega_menu_width', maybe_unserialize($meta['value']));
                }
                if (trim($meta['key']) == '_menu_item_megamenu_mega_menu_url') {
                    update_post_meta($id, '_menu_item_megamenu_mega_menu_url', maybe_unserialize($meta['value']));
                }
                if (trim($meta['key']) == '_menu_item_megamenu_img_note') {
                    update_post_meta($id, '_menu_item_megamenu_img_note', maybe_unserialize($meta['value']));
                }
                if (trim($meta['key']) == '_menu_item_megamenu_img_icon') {
                    update_post_meta($id, '_menu_item_megamenu_img_icon', maybe_unserialize($meta['value']));
                }
            }
        }

        /* import Sidebar Content */
        public function import_widget($data_ajax = null)
        {
            if (isset($this->data_import['widget_path'])) {
                $url = $this->get_file(
                    $this->data_import['widget_path']
                );
            } else {
                $url = '';
            }
            if ($data_ajax != null && isset($data_ajax['id']) && isset($data_ajax['slug'])) {
                if (isset($this->data_demos[$data_ajax['id']]['widget_path'])) {
                    $url = $this->get_file(
                        $this->data_demos[$data_ajax['id']]['widget_path']
                    );
                } else {
                    $url = '';
                }
            }
            if ($url != '') {
                $data = file_get_contents($url);
                $data = json_decode($data);
                global $wp_registered_sidebars;
                if (!empty($data) || is_object($data)) {
                    update_option('sidebars_widgets', array(false));
                    do_action('wie_before_import');
                    $data              = apply_filters('wie_import_data', $data);
                    $available_widgets = $this->available_widgets();
                    $widget_instances  = array();
                    foreach ($available_widgets as $widget_data) {
                        $widget_instances[$widget_data['id_base']] = get_option('widget_'.$widget_data['id_base']);
                    }
                    $results = array();
                    foreach ($data as $sidebar_id => $widgets) {
                        if ('wp_inactive_widgets' == $sidebar_id) {
                            continue;
                        }
                        if (isset($wp_registered_sidebars[$sidebar_id])) {
                            $sidebar_available    = true;
                            $use_sidebar_id       = $sidebar_id;
                            $sidebar_message_type = 'success';
                            $sidebar_message      = '';
                        } else {
                            $sidebar_available    = false;
                            $use_sidebar_id       = 'wp_inactive_widgets'; // add to inactive if sidebar does not exist in theme
                            $sidebar_message_type = 'error';
                            $sidebar_message      = __('Sidebar does not exist in theme (using Inactive)', 'ovic-import');
                        }
                        $results[$sidebar_id]['name']         = !empty($wp_registered_sidebars[$sidebar_id]['name']) ? $wp_registered_sidebars[$sidebar_id]['name'] : $sidebar_id; // sidebar name if theme supports it; otherwise ID
                        $results[$sidebar_id]['message_type'] = $sidebar_message_type;
                        $results[$sidebar_id]['message']      = $sidebar_message;
                        $results[$sidebar_id]['widgets']      = array();
                        foreach ($widgets as $widget_instance_id => $widget) {
                            $fail               = false;
                            $id_base            = preg_replace('/-[0-9]+$/', '', $widget_instance_id);
                            $instance_id_number = str_replace($id_base.'-', '', $widget_instance_id);
                            if (!$fail && !isset($available_widgets[$id_base])) {
                                $fail                = true;
                                $widget_message_type = 'error';
                                $widget_message      = __('Site does not support widget', 'ovic-import');
                            }
                            $widget = apply_filters('wie_widget_settings', $widget);
                            $widget = json_decode(json_encode($widget), true);
                            $widget = apply_filters('wie_widget_settings_array', $widget);
                            if (!$fail && isset($widget_instances[$id_base])) {
                                $sidebars_widgets        = get_option('sidebars_widgets');
                                $sidebar_widgets         = isset($sidebars_widgets[$use_sidebar_id]) ? $sidebars_widgets[$use_sidebar_id] : array();
                                $single_widget_instances = !empty($widget_instances[$id_base]) ? $widget_instances[$id_base] : array();
                                foreach ($single_widget_instances as $check_id => $check_widget) {
                                    if (in_array("$id_base-$check_id", $sidebar_widgets) && (array) $widget == $check_widget) {
                                        $fail                = true;
                                        $widget_message_type = 'warning';
                                        $widget_message      = __('Widget already exists', 'ovic-import');
                                        break;
                                    }
                                }
                            }
                            if (!$fail) {
                                $single_widget_instances   = get_option('widget_'.$id_base);
                                $single_widget_instances   = !empty($single_widget_instances) ? $single_widget_instances : array('_multiwidget' => 1);
                                $single_widget_instances[] = $widget;
                                end($single_widget_instances);
                                $new_instance_id_number = key($single_widget_instances);
                                if ('0' === strval($new_instance_id_number)) {
                                    $new_instance_id_number                           = 1;
                                    $single_widget_instances[$new_instance_id_number] = $single_widget_instances[0];
                                    unset($single_widget_instances[0]);
                                }
                                if (isset($single_widget_instances['_multiwidget'])) {
                                    $multiwidget = $single_widget_instances['_multiwidget'];
                                    unset($single_widget_instances['_multiwidget']);
                                    $single_widget_instances['_multiwidget'] = $multiwidget;
                                }
                                update_option('widget_'.$id_base, $single_widget_instances);
                                $sidebars_widgets                    = get_option('sidebars_widgets');
                                $new_instance_id                     = $id_base.'-'.$new_instance_id_number;
                                $sidebars_widgets[$use_sidebar_id][] = $new_instance_id;
                                update_option('sidebars_widgets', $sidebars_widgets);
                                $after_widget_import = array(
                                    'sidebar'           => $use_sidebar_id,
                                    'sidebar_old'       => $sidebar_id,
                                    'widget'            => $widget,
                                    'widget_type'       => $id_base,
                                    'widget_id'         => $new_instance_id,
                                    'widget_id_old'     => $widget_instance_id,
                                    'widget_id_num'     => $new_instance_id_number,
                                    'widget_id_num_old' => $instance_id_number,
                                );
                                do_action('wie_after_widget_import', $after_widget_import);
                                if ($sidebar_available) {
                                    $widget_message_type = 'success';
                                    $widget_message      = __('Imported', 'ovic-import');
                                } else {
                                    $widget_message_type = 'warning';
                                    $widget_message      = __('Imported to Inactive', 'ovic-import');
                                }
                            }
                            $results[$sidebar_id]['widgets'][$widget_instance_id]['name']         = isset($available_widgets[$id_base]['name']) ? $available_widgets[$id_base]['name'] : $id_base; // widget name or ID if name not available (not supported by site)
                            $results[$sidebar_id]['widgets'][$widget_instance_id]['title']        = !empty($widget['title']) ? $widget['title'] : __('No Title', 'ovic-import');                    // show "No Title" if widget instance is untitled
                            $results[$sidebar_id]['widgets'][$widget_instance_id]['message_type'] = $widget_message_type;
                            $results[$sidebar_id]['widgets'][$widget_instance_id]['message']      = $widget_message;
                        }
                    }
                    do_action('wie_after_import');
                }
            }
        }

        public function available_widgets()
        {
            global $wp_registered_widget_controls;
            $widget_controls   = $wp_registered_widget_controls;
            $available_widgets = array();
            foreach ($widget_controls as $widget) {
                if (!empty($widget['id_base']) && !isset($available_widgets[$widget['id_base']])) { // no dupes
                    $available_widgets[$widget['id_base']]['id_base'] = $widget['id_base'];
                    $available_widgets[$widget['id_base']]['name']    = $widget['name'];
                }
            }

            return apply_filters('wie_available_widgets', $available_widgets);
        }

        /* Import Revolution Slider */
        public function import_revslider($data_ajax = null)
        {
            if (isset($this->data_import['revslider_path'])) {
                $rev_directory = $this->get_file(
                    $this->data_import['revslider_path']
                );
            } else {
                $rev_directory = '';
            }
            if ($data_ajax != null && isset($data_ajax['id']) && isset($data_ajax['slug'])) {
                if (isset($this->data_demos[$data_ajax['id']]['revslider_path'])) {
                    $rev_directory = $this->get_file(
                        $this->data_demos[$data_ajax['id']]['revslider_path']
                    );
                } else {
                    $rev_directory = '';
                }
            }
            if (class_exists('RevSlider') && $rev_directory != '') {
                $slider = new RevSlider();
                if (class_exists('RevSliderSliderImport')) {
                    $import = new RevSliderSliderImport();
                }
                $rev_files = array();
                $response  = array();
                foreach (glob($rev_directory.'*.zip') as $filename) {
                    $filename    = basename($filename);
                    $rev_files[] = $rev_directory.$filename;
                }
                foreach ($rev_files as $index => $rev_file) {
                    if (version_compare(RS_REVISION, '6.0.0', '<')) {
                        $response[] = $slider->importSliderFromPost(true, true, $rev_file);
                    } elseif (class_exists('RevSliderSliderImport')) {
                        $response[] = $import->import_slider(true, $rev_file);
                    }
                }
                //wp_send_json( $response );
            }
        }

        public function import_config($demo)
        {
            if (!empty($demo)) {
                if (is_array($demo)) {
                    $this->mega_menu($demo);
                    $this->woocommerce_settings($demo);
                    $this->menu_locations($demo);
                    $this->update_options($demo);
                }
            }
        }

        public function mega_menu($demo)
        {
            if (isset($demo['mega_menu']) && !empty($demo['mega_menu'])) {
                foreach ($demo['mega_menu'] as $item) {
                    $menu = wp_get_nav_menu_object($item['name']);
                    if (!empty($menu) && !empty($item['metas'])) {
                        foreach ($item['metas'] as $key => $value) {
                            update_term_meta($menu->term_id, $key, $value);
                        }
                    }
                }
            }
        }

        public function update_mega_menu()
        {
            if (isset($this->data_megamenu) && !empty($this->data_megamenu)) {
                foreach ($this->data_megamenu as $item) {
                    $menu = wp_get_nav_menu_object($item['name']);
                    if (!empty($menu) && !empty($item['metas'])) {
                        foreach ($item['metas'] as $key => $value) {
                            update_term_meta($menu->term_id, $key, $value);
                        }
                    }
                }
            }
        }

        /* WooCommerce Settings */
        public function woocommerce_settings($demo)
        {
            foreach ($this->woo_pages as $woo_page_name => $woo_page_title) {
                $woopage = get_page_by_title($woo_page_title);
                if (isset($woopage->ID) && $woopage->ID) {
                    update_option($woo_page_name, $woopage->ID);
                }
            }
            if (class_exists('YITH_Woocompare')) {
                update_option('yith_woocompare_compare_button_in_products_list', 'yes');
                update_option('yith_woocompare_is_button', 'link');
            }
            if (class_exists('WC_Admin_Notices')) {
                WC_Admin_Notices::remove_notice('install');
            }
            delete_transient('_wc_activation_redirect');
            // Image sizes
            $this->woo_ratio   = isset($demo['woo_ratio']) ? $demo['woo_ratio'] : $this->woo_ratio;
            $this->woo_catalog = isset($demo['woo_catalog']) ? $demo['woo_catalog'] : $this->woo_catalog;
            $this->woo_single  = isset($demo['woo_single']) ? $demo['woo_single'] : $this->woo_single;
            $ratio             = explode(':', $this->woo_ratio);
            update_option('woocommerce_thumbnail_cropping', 'custom');
            update_option('woocommerce_thumbnail_image_width', $this->woo_catalog);
            update_option('woocommerce_thumbnail_cropping_custom_width', $ratio[0]);
            update_option('woocommerce_thumbnail_cropping_custom_height', $ratio[1]);
            update_option('woocommerce_single_image_width', $this->woo_single);    // Single product image
            flush_rewrite_rules();
        }

        /* Menu Locations */
        public function menu_locations($demo)
        {
            $menu_location = array();
            $locations     = get_theme_mod('nav_menu_locations');
            $menus         = wp_get_nav_menus();
            if (isset($demo['menu_locations']) && is_array($demo['menu_locations'])) {
                if ($menus) {
                    foreach ($menus as $menu) {
                        foreach ($demo['menu_locations'] as $key => $value) {
                            if ($menu->name == $value) {
                                $menu_location[$key] = $menu->term_id;
                            }
                        }
                    }
                }
                set_theme_mod('nav_menu_locations', $menu_location);
            } elseif (isset($demo['menus']) && is_array($demo['menus'])) {
                $menu_location = $locations;
                set_theme_mod('nav_menu_locations', $menu_location);
            }
        }

        /* Update Options */
        public function update_options($demo)
        {
            // Permalink
            update_option('permalink_structure', '/%postname%/');
            // Home page
            if (isset($demo['homepage']) && $demo['homepage'] != "") {
                $homepage = get_page_by_title($demo['homepage']);
                if (isset($homepage) && $homepage->ID) {
                    update_option('show_on_front', 'page');
                    update_option('page_on_front', $homepage->ID);
                }
            }
            // Blog page
            if (isset($demo['blogpage']) && $demo['blogpage'] != "") {
                $post_page = get_page_by_title($demo['blogpage']);
                if (isset($post_page) && $post_page->ID) {
                    update_option('show_on_front', 'page');
                    update_option('page_for_posts', $post_page->ID);
                }
            }
            flush_rewrite_rules();
        }
    }

    new Ovic_Import_Data();
}