<?php
if (!defined('ABSPATH')) {
    exit; // disable direct access
}
if (!class_exists('Ovic_Import_Database_Sample_Data')) {
    class Ovic_Import_Database_Sample_Data
    {
        /**
         * Variable to hold the initialization state.
         *
         * @var  boolean
         */
        protected static $initialized          = false;
        public static    $sample_packages      = array();
        public static    $max_backup_file_size = 2097152;
        /**
         * How to store and access images used in sample data.
         *
         * Either one of following options:
         *
         * 'local' - All demo images will be downloaded and stored locally.
         * 'placeholder' - All demo images will be replaced with gray images from https://via.placeholder.com.
         * 'remote' - All demo images will be accessed remotely.
         *
         * @var  string
         */
        protected static $demo_images_storage = '';
        /**
         * Define regular expression pattern to look for demo site URL.
         *
         * @var  string
         */
        protected static $demo_site_pattern = '';
        /**
         * Define regular expression pattern to look for demo image URL.
         *
         * @var  string
         */
        protected static $demo_image_pattern = '(%2F|\\\\*/)([^\s\'"]*)wp-content[%2F\\\\/]+uploads([^\s\'"]+)';
        /**
         * Plug into WordPress.
         *
         * @return  void
         */
        protected static $reserved_options   = array();
        public static    $sample_data_folder = '';
        public static    $theme_option_key   = '';
        public static    $file_path          = '';

        public static function initialize()
        {
            // Do nothing if pluggable functions already initialized.
            if (self::$initialized) {
                return;
            }

            if (function_exists('set_time_limit') && false === strpos(ini_get('disable_functions'),
                    'set_time_limit') && !ini_get('safe_mode')) { // phpcs:ignore PHPCompatibility.PHP.DeprecatedIniDirectives.safe_modeDeprecatedRemoved
                @set_time_limit(0); // @codingStandardsIgnoreLine
            }

            // Get WordPress database object and table prefix.
            global $wpdb, $table_prefix;

            self::$file_path = trailingslashit(get_template_directory()).'importer';

            $include_import = self::$file_path.'/importer-db.php';

            if (!has_filter('import_sample_data_packages') && file_exists($include_import)) {
                include $include_import;
            }

            self::$sample_packages     = self::get_sample_packages();
            self::$reserved_options    = array(
                'home',
                'siteurl',
                'template',
                'stylesheet',
                'template_root',
                'stylesheet_root',
                'current_theme',
                'active_plugins',
                "{$table_prefix}user_roles",
                'db_version',
                'initial_db_version',
                'woocommerce_db_version',
                'admin_email',
                'user_roles',

            );
            $theme                     = wp_get_theme();
            self::$sample_data_folder  = sanitize_file_name(strtolower($theme->get('Name')));
            self::$demo_site_pattern   = Ovic_Import_Database_Settings::demo_site_pattern();
            self::$theme_option_key    = Ovic_Import_Database_Settings::theme_option_key();
            self::$demo_images_storage = get_option('import_sample_data_demo_images_storage', 'placeholder');

            // Export data
            add_action('init', array(__CLASS__, 'export_sample_data'));

            // Register Ajax actions for sample data installation.
            add_action('wp_ajax_import_sample_data_install_sample_data', array(__CLASS__, 'install_sample_data'));
            add_action('wp_ajax_nopriv_import_sample_data_install_sample_data',
                array(__CLASS__, 'install_sample_data'));

            add_action('wp_ajax_import_sample_data_uninstall_sample_data', array(__CLASS__, 'uninstall_sample_data'));
            add_action('wp_ajax_nopriv_import_sample_data_uninstall_sample_data',
                array(__CLASS__, 'uninstall_sample_data'));

            add_action('wp_ajax_import_sample_data_install_plugin', array(__CLASS__, 'install_plugin'));
            add_action('wp_ajax_nopriv_import_sample_data_install_plugin', array(__CLASS__, 'install_plugin'));

            add_action('wp_ajax_import_sample_data_clear_temporary', array(__CLASS__, 'clear_temporary'));
            add_action('wp_ajax_nopriv_import_sample_data_clear_temporary', array(__CLASS__, 'clear_temporary'));

            // Register filter to alter URL for demo images.
            $match = array(
                'remote',
                'placeholder',
            );
            if (in_array(self::$demo_images_storage,
                    $match) && false !== get_option('import_sample_data_install_sample_package')) {
                add_filter('wp_get_attachment_url', array(__CLASS__, 'get_attachment_url'), 10, 2);
                add_filter('wp_get_attachment_thumb_url', array(__CLASS__, 'get_attachment_url'), 10, 2);
                add_filter('wp_get_attachment_image_src', array(__CLASS__, 'get_attachment_image_src'), 10, 4);
                add_filter('wp_calculate_image_srcset', array(__CLASS__, 'calculate_image_srcset'), 10, 5);
                //add_filter( 'post_thumbnail_html', array( __CLASS__, 'post_thumbnail_html' ), 10, 5 );
            }

            // State that initialization completed.
            self::$initialized = true;
        }

        public static function action_clear_temporary()
        {
            global $wp_filesystem;

            if (empty($wp_filesystem)) {
                require_once(ABSPATH.'/wp-admin/includes/file.php');
                WP_Filesystem();
            }
            $response   = array(
                'success' => 'no',
                'count'   => 0,
            );
            $count      = 0;
            $upload_dir = wp_upload_dir();
            $themes     = wp_get_themes();
            if (!empty($themes)) {
                foreach ($themes as $theme) {
                    $theme_name = sanitize_file_name(strtolower($theme->get('Name')));
                    $path       = $upload_dir['basedir'].'/'.$theme_name.'/sample-data/';
                    if (file_exists($path)) {
                        $count++;
                        $response['success'] = 'yes';
                        $wp_filesystem->delete(str_replace('sample-data/', '', $path), true);
                    }
                }
            }
            // Clear export data.
            $path_export = $upload_dir['basedir'].'/ovic-import-data/';
            if (file_exists($path_export)) {
                $count               = $count + 1;
                $response['success'] = 'yes';
                $wp_filesystem->delete($path_export, true);
            }
            $response['count'] = $count;

            return $response;
        }

        public static function clear_temporary()
        {
            // Verify nonce.
            if (!isset($_REQUEST['security']) || !wp_verify_nonce($_REQUEST['security'],
                    'import_sample_data_ajax_admin')) {
                self::print_confirm_message('failed_security');
            }

            $response = self::action_clear_temporary();

            self::print_confirm_message('clear_temporary', $response);

            wp_die();
        }

        public static function download_image($image_url, $target)
        {
            $parsed_url = wp_parse_url($image_url);

            // Check parsed URL.
            if (!$parsed_url || !is_array($parsed_url)) {
                return false;
            }

            // Ensure url is valid.
            $image_url = esc_url_raw($image_url);

            // download_url function is part of wp-admin.
            if (!function_exists('download_url')) {
                include_once ABSPATH.'wp-admin/includes/file.php';
            }

            $file_array         = array();
            $file_array['name'] = basename(current(explode('?', $image_url)));

            // Download file to temp location.
            $file_array['tmp_name'] = download_url($image_url);

            // If error storing temporarily, return the error.
            if (is_wp_error($file_array['tmp_name'])) {
                return false;
            }

            // Do the validation and storage stuff.
            $file_name = basename($image_url);
            $time      = str_replace($file_name, '', $target);
            $file      = wp_handle_sideload(
                $file_array,
                array(
                    'test_form' => false,
                    'mimes'     => apply_filters(
                        'ovic_rest_allowed_image_mime_types',
                        array(
                            'jpg|jpeg|jpe' => 'image/jpeg',
                            'gif'          => 'image/gif',
                            'png'          => 'image/png',
                            'bmp'          => 'image/bmp',
                            'tiff|tif'     => 'image/tiff',
                            'ico'          => 'image/x-icon',
                            'svg'          => 'image/svg+xml',
                        )
                    ),
                ),
                $time
            );

            if (isset($file['error'])) {
                @unlink($file_array['tmp_name']);

                return false;
            }

            do_action('ovic_rest_api_uploaded_image_from_url', $file, $image_url);

            return true;
        }

        public static function import_attachment()
        {
            $total       = 0;
            $done        = 0;
            $attachments = get_posts(array(
                'post_type'      => 'attachment',
                'posts_per_page' => -1,
            ));

            if (!defined('ALLOW_UNFILTERED_UPLOADS')) {
                define('ALLOW_UNFILTERED_UPLOADS', true);
            }

            if (!empty($attachments)) {
                $total = count($attachments);

                foreach ($attachments as $attachment) {
                    // Check if attachment file exists.
                    $attachment_url = $attachment->guid;

                    if (preg_match('#'.self::$demo_site_pattern.self::$demo_image_pattern.'#i', $attachment_url)) {
                        // Get base local and remote URL.
                        $uploads    = explode('/wp-content/uploads/', $attachment->guid);
                        $remote     = apply_filters('ovic_import_remote_image', $attachment->guid);
                        $local_file = end($uploads);
                        if (self::download_image($remote, $local_file)) {
                            $done++;
                        }
                    }
                }
            }

            $data = sprintf('<span class="count"> - %s ( %s / %s )</span>',
                esc_html__('Downloaded', 'ovic-import'),
                $done,
                $total
            );

            // Attachment data import successfully.
            wp_send_json_success($data);

            wp_die();
        }

        public static function import_placeholder()
        {
            $attachments = get_posts(
                array(
                    'post_type'      => 'attachment',
                    'posts_per_page' => -1,
                )
            );

            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    // Check if attachment file exists.
                    $upload = wp_upload_dir();

                    $metadata = wp_get_attachment_metadata($attachment->ID);

                    // Get base local and remote URL.
                    $remote_dir = explode('/wp-content/uploads/', $attachment->guid);

                    // Replace local base with remote base.
                    $remote_file = OVIC_IMPORT_PLUGIN_URL.'includes/classes/image.php?'.$metadata['width'].'x'.$metadata['height'];

                    $local_file = $upload['basedir'].'/'.end($remote_dir);

                    if (!self::download($remote_file, $local_file)) {
                        continue;
                    }
                }
            }
            // Attachment data import successfully.
            wp_send_json_success();

            wp_die();
        }

        public static function uninstall_sample_data()
        {
            // Verify nonce.
            if (!isset($_REQUEST['security']) || !wp_verify_nonce($_REQUEST['security'],
                    'import_sample_data_ajax_admin')) {
                self::print_confirm_message('failed_security');
            }

            // Get selected sample data package.
            if (!isset($_REQUEST['package'])) {
                self::print_confirm_message('missing_package');
            }

            // Get package step.
            $package = self::get_sample_package($_REQUEST['package']);

            if (empty($package)) {
                self::print_confirm_message('failed_package', $package);
            }

            // Get current step.
            $step = isset($_REQUEST['step']) ? $_REQUEST['step'] : 1;

            switch ($step) {
                case '1' :
                    // Print confirm message.
                    self::print_confirm_message('uninstall', $package);

                    break;

                case '2' :
                    // Download the selected sample package.
                    self::restore_backup_data($package);

                    // Clear temp folder.
                    self::action_clear_temporary();

                    break;
            }

            wp_send_json_success();
        }

        public static function restore_backup_data($package)
        {
            // Get WordPress file system object.
            global $wp_filesystem;

            // Get all backup files.
            $upload_dir = wp_upload_dir();
            $backup_dir = "{$upload_dir['basedir']}/".self::$sample_data_folder."/sample-data/{$package['id']}";

            if (is_dir("{$backup_dir}/backups")) {
                $backup = glob("{$backup_dir}/backups/*");
            }

            if (!isset($backup) || !count($backup)) {
                $backup = glob("{$backup_dir}/backup_*.sql");
            }

            if (count($backup)) {
                // Get the latest backup.
                rsort($backup);
                reset($backup);

                $backup = current($backup);

                if (is_dir($backup)) {
                    $backup = glob("{$backup}/*.sql");
                }

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

                global $wpdb;
                // Start output buffering to capture error message.
                ob_start();

                // Import backup data.
                foreach (( array ) $backup as $file) {
                    // Read and execute queries from backup file.
                    $wpdb->query('START TRANSACTION;');

                    foreach (
                        explode(";\n",
                            ($buffer = file_get_contents($file)) ? $buffer : $wp_filesystem->get_contents($file)) as $query
                    ) {
                        if (trim($query, ';') != '') {
                            $wpdb->query("{$query};");
                        }
                    }

                    // Commit transaction.
                    if (false === $wpdb->query('COMMIT;')) {
                        $result = ob_get_contents();

                        // Roll back transaction.
                        $wpdb->query('ROLLBACK;');

                        wp_send_json_error(
                            sprintf(
                                esc_html__('Restoring backup has encountered an error and cannot continue: %s',
                                    'ovic-import'),
                                $wpdb->last_error ? $wpdb->last_error : $result
                            )
                        );
                    }
                }

                // Stop output buffering.
                ob_end_clean();

                // Let WordPress handle database upgrade.
                if (!function_exists('wp_upgrade')) {
                    include_once ABSPATH.'wp-admin/includes/upgrade.php';
                }

                wp_upgrade();
            } else {
                wp_send_json_error(esc_html__('Not found any backup to restore.', 'ovic-import'));
            }
        }

        public static function install_sample_data()
        {
            // Verify nonce.
            if (!isset($_REQUEST['security']) || !wp_verify_nonce($_REQUEST['security'],
                    'import_sample_data_ajax_admin')) {
                self::print_confirm_message('failed_security');
            }

            // Get selected sample data package.
            if (!isset($_REQUEST['package'])) {
                self::print_confirm_message('missing_package');
            }

            // Get package step.
            $package = self::get_sample_package($_REQUEST['package']);

            if (empty($package)) {
                self::print_confirm_message('failed_package', $package);
            }

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

            // Get current step.
            $step = isset($_REQUEST['step']) ? $_REQUEST['step'] : 1;

            switch ($step) {
                case '1' :
                    // Print confirm message.
                    self::print_confirm_message('install', $package);

                    break;

                case '2' :
                    if (!empty($package['download']) && strpos($package['download'], '.zip') === false) {
                        wp_send_json_success();
                    } elseif (strpos($package['download'], '.zip') !== false) {
                        self::download_sample_package($package);
                    } else {
                        $path = self::$file_path.'/sample-data/';
                        if (file_exists($path)) {
                            wp_send_json_success();
                        } else {
                            self::download_sample_package($package);
                        }
                    }
                    // Download the selected sample package.
                    break;

                case '3' :
                    // Install the selected sample package.
                    self::import_sample_package($package);

                    break;

                case '4' :
                    // Download the attachment.
                    $demo_images_storage = !empty($_REQUEST['attachment']) ? $_REQUEST['attachment'] : 'placeholder';

                    update_option('import_sample_data_demo_images_storage', $demo_images_storage);

                    if ($demo_images_storage == 'local') {
                        self::import_attachment();
                    } else {
                        $data = sprintf('<span class="count"> - %s</span>',
                            esc_html__('Image Remote', 'ovic-import')
                        );
                        wp_send_json_success($data);
                    }

                    break;
            }

            wp_die();
        }

        public static function import_sample_package($package)
        {
            // Get WordPress file system object.
            global $wp_filesystem;

            // Generate path to downloaded sample data package.
            $path     = self::$file_path.'/sample-data/';
            $is_unzip = false;
            if (!is_dir($path)) {
                $path = wp_upload_dir();
                $path = "{$path['basedir']}/".self::$sample_data_folder."/sample-data/{$package['id']}";
                if (!is_dir($path)) {
                    // Extract sample data package.
                    $unzip_file = unzip_file("{$path}.zip", $path);
                    if (is_wp_error($unzip_file)) {
                        if (class_exists('ZipArchive')) {
                            $zip = new ZipArchive;
                            if ($zip) {
                                if ($zip->open("{$path}.zip")) {
                                    $zip->extractTo($path);
                                    $is_unzip = true;
                                } else {
                                    $path = '';
                                }
                                $zip->close();
                            } else {
                                $path = '';
                            }
                        } else {
                            $path = '';
                        }
                    }
                }
                if (!is_dir($path)) {
                    wp_send_json_error(esc_html__('Failed to extract downloaded package to file system.',
                        'ovic-import'));
                }
            } else {
                if (is_dir(self::$file_path.'/sample-data/'.$package['id'].'/')) {
                    $path = self::$file_path.'/sample-data/'.$package['id'].'/';
                }
            }
            // Look for sample data declaration file.
            $sql = glob("{$path}/*.sql");

            if (!count($sql)) {
                wp_send_json_error(esc_html__('Invalid sample data package.', 'ovic-import'));
            }

            // Start importing sample data.
            $option = (isset($_REQUEST['option']) && 'undefined' != $_REQUEST['option'])
                ? $_REQUEST['option']
                : 'full';
            if ($option == 'full') {
                if (self::backup_database($package)) {
                    foreach ($sql as $file) {
                        $sql_content = file_get_contents($file);
                        self::import_full_demo_site($package, $sql_content);
                    }

                    //Update Options
                    update_option('import_sample_data_install_sample_package', 1);
                    update_option('import_sample_data_current_sample_data', $package['id']);

                    // Update Extend Settings
                    $sample_page = isset($_POST['sample_page']) ? $_POST['sample_page'] : '';
                    if ($sample_page != '') {
                        $sample_page_settings = isset($package['sample-page'][$sample_page]['settings']) ? $package['sample-page'][$sample_page]['settings'] : array();

                        if (!empty($sample_page_settings)) {
                            // Update Theme Options
                            global $wp_object_cache;
                            $wp_object_cache->reset();
                            $all_options = get_option(self::$theme_option_key, true);
                            if ($all_options) {
                                foreach ($sample_page_settings as $key => $value) {
                                    $all_options[$key] = $value;
                                }
                                update_option(self::$theme_option_key, $all_options);
                            }
                        }

                        // Update Home Settings
                        $home_slug = isset($package['sample-page'][$sample_page]['slug']) ? $package['sample-page'][$sample_page]['slug'] : '';
                        if ($home_slug != '') {
                            $page = get_page_by_path($home_slug);
                            if ($page) {
                                update_option('show_on_front', 'page');
                                update_option('page_on_front', $page->ID);
                            }
                        }
                    }
                    do_action('import_sample_data_after_install_sample_data', $package);
                } else {
                    wp_send_json_error(esc_html__('The backup process error.', 'ovic-import'));
                }
            } else {
                wp_send_json_error(esc_html__('Invalid parameters.', 'ovic-import'));
            }
            // Clean up temporary data.
            if ($is_unzip) {
                unlink("{$path}.zip") || $wp_filesystem->delete("{$path}.zip");
                foreach (glob("{$path}/*.sql") as $sql) {
                    unlink($sql) || $wp_filesystem->delete($sql);
                }
            }
            // Sample data import successfully.
            wp_send_json_success();
        }

        public static function backup_database($package)
        {
            // Get WordPress file system object.
            global $wp_filesystem;

            // Generate path to downloaded sample data package.
            $upload_dir = wp_upload_dir();
            $path       = "{$upload_dir['basedir']}/".self::$sample_data_folder."/sample-data/{$package['id']}";

            // Get WordPress database object and table prefix.
            global $wpdb, $table_prefix;

            // Get all tables in database.
            $tables = $wpdb->get_results('SHOW TABLES;', ARRAY_N);
            // Backup current data.
            $backup_dir = "{$upload_dir['basedir']}/".self::$sample_data_folder."/sample-data/{$package['id']}/backups/".date('YmdHis');
            $num_file   = 1;
            $queries    = '';

            if (!self::prepare_directory($backup_dir)) {
                return false;
            }

            if ($tables && count($tables)) {
                foreach ($tables as $key => $table) {
                    $table = $table[0];
                    // Drop existing table first.
                    $queries .= "DROP TABLE IF EXISTS `{$table}`;\n";

                    // Get table creation schema.
                    $results = $wpdb->get_results("SHOW CREATE TABLE `{$table}`;", ARRAY_A);
                    $results = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $results[0]['Create Table']);
                    $queries .= str_replace("\n", '', $results).";\n";

                    // Get table data.
                    $i = 0;

                    do {
                        $results = $wpdb->get_results("SELECT * FROM `{$table}` WHERE 1 LIMIT {$i}, 500;", ARRAY_A);

                        if ($results) {
                            foreach ($results as $result) {
                                // Generate column list.
                                $keys = '(`'.implode('`, `', array_keys($result)).'`)';

                                // Generate value list.
                                $values = array();

                                foreach (array_values($result) as $value) {
                                    $values[] = str_replace(
                                        array('\\', "\r", "\n", "'"),
                                        array('\\\\', '\\r', '\\n', "\\'"),
                                        $value
                                    );
                                }

                                $values = "('".implode("', '", $values)."')";

                                // Store insert query.
                                $query = "INSERT INTO `{$table}` {$keys} VALUES {$values};\n";

                                if (strlen($queries.$query) > self::$max_backup_file_size) {
                                    // Generate backup file path.
                                    $path = str_repeat('0', 4 - strlen(( string ) $num_file));
                                    $path = "{$backup_dir}/backup_{$path}{$num_file}.sql";

                                    // Write current data to file.

                                    self::put_content($path, $queries);

                                    // Reset data.
                                    $queries = '';

                                    // Increase file counter.
                                    $num_file++;
                                }

                                $queries .= $query;
                            }

                            $i += count($results);
                        }
                    } while ($results && count($results));
                }
            }

            // Finalize backup task.
            if ($queries != '') {
                // Generate backup file path.
                if ($num_file > 1) {
                    $path = str_repeat('0', 4 - strlen(( string ) $num_file));
                    $path = "{$backup_dir}/backup_{$path}{$num_file}.sql";
                } else {
                    $path = "{$backup_dir}/backup.sql";
                }

                // Write data to file.
                self::put_content($path, $queries);
            }

            return true;
        }

        public static function import_full_demo_site($package, $sql)
        {
            global $wpdb;

            // Install Sample data
            $wpdb->query('START TRANSACTION;');
            foreach (explode(";\n", ($sql) ? $sql : '') as $query) {
                if (trim($query, ';') != '' && strpos(trim($query, ';'), 'customer_lookup') === false) {
                    $query = str_replace('#__', $wpdb->prefix, $query);
                    $wpdb->query("{$query};");
                }
            }
            // Commit transaction.
            if (false === $wpdb->query('COMMIT;')) {
                $result = ob_get_contents();

                // Roll back transaction.
                $wpdb->query('ROLLBACK;');

                wp_send_json_error(
                    sprintf(
                        esc_html__('Restoring backup has encountered an error and cannot continue: %s', 'ovic-import'),
                        $wpdb->last_error ? $wpdb->last_error : $result
                    )
                );
            }

            // Stop output buffering.
            ob_end_clean();
        }

        public static function install_plugin()
        {
            global $wp_filesystem;

            $plugin = isset($_POST['plugin']) ? $_POST['plugin'] : array();

            // Verify nonce.
            if (!isset($_REQUEST['security']) || !wp_verify_nonce($_REQUEST['security'],
                    'import_sample_data_ajax_admin')) {
                wp_send_json_error(esc_html__('Nonce verification failed. This might due to your working session has been expired. Please reload the page to renew your working session.',
                    'ovic-import'));
            }
            if (empty($plugin)) {
                wp_send_json_error(esc_html__('No plugin specified.', 'ovic-import'));
            }

            // Get All Plugins
            $installed_plugins = get_plugins();

            // Install Plugins
            $plugins = Ovic_Import_Database_Settings::plugins();

            if (!empty($plugins)) {
                foreach ($plugins as $value) {
                    if ($value['slug'] == $plugin['slug']) {
                        if (isset($installed_plugins[$plugin['file_path']]) && !empty($installed_plugins[$plugin['file_path']])) {
                            break;
                        }
                        // download
                        $link = '';
                        if ($value['source_type'] == 'repo') {
                            $link = self::get_wp_repo_download_url($value['slug']);
                        } elseif ($value['source_type'] == 'external') {
                            $link = $value['source'];
                        }

                        // Download an Unzip

                        if ($link != "") {
                            $file_name        = $value['slug'].'.zip';
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

                        break;
                    }
                }
            }

            // Active Plugin
            // Disnable Metabox Plugin Redirect
            $_REQUEST['tgmpa'] = 'tgmpa';
            wp_clean_plugins_cache();

            $result = activate_plugin($plugin['file_path']);
            if (is_wp_error($result)) {
                wp_send_json_error(sprintf(esc_html__('Failed to activate %s plugin', 'ovic-import'), $plugin['name']));
            }
            // Disable Visual Composer welcome page redirection.
            if ('js_composer' == $plugin['slug']) {
                delete_transient('_vc_page_welcome_redirect');
            }

            wp_send_json_success();
        }

        /**
         * Retrieve the download URL for a WP repo package.
         *
         * @param  string  $slug  Plugin slug.
         *
         * @return string Plugin download URL.
         * @since 2.5.0
         *
         */
        public static function get_wp_repo_download_url($slug)
        {
            $source = '';
            $api    = self::get_plugins_api($slug);

            if (isset($api->download_link)) {
                return $api->download_link;
            }

            return $source;
        }

        /**
         * Try to grab information from WordPress API.
         *
         * @param  string  $slug  Plugin slug.
         *
         * @return object Plugins_api response object on success, WP_Error on failure.
         * @since 2.5.0
         *
         */
        public static function get_plugins_api($slug)
        {
            static $api = array(); // Cache received responses.

            if (!isset($api[$slug])) {
                if (!function_exists('plugins_api')) {
                    require_once ABSPATH.'wp-admin/includes/plugin-install.php';
                }

                $response = plugins_api('plugin_information',
                    array(
                        'slug'   => $slug,
                        'fields' => array(
                            'sections' => false
                        )
                    )
                );

                $api[$slug] = false;

                if (is_wp_error($response)) {
                    wp_die(esc_html(''));
                } else {
                    $api[$slug] = $response;
                }
            }

            return $api[$slug];
        }

        public static function download_sample_package($package)
        {
            // Generate path to store downloaded sample data package.
            $path   = wp_upload_dir();
            $path   = "{$path['basedir']}/".self::$sample_data_folder."/sample-data/{$package['id']}";
            $path_z = $path.'.zip';

            $error = array();
            if (!self::download($package['download'], $path_z)) {
                $error[] = sprintf(
                    esc_html__('Failed to download the selected sample data package &#39;%s&#39;.', 'ovic-import'),
                    $package['id']
                );
            }

            if (count($error)) {
                if (count($error) == 1) {
                    wp_send_json_error(implode($error));
                } else {
                    wp_send_json_error('<ul><li>'.implode('</li><li>', $error).'</li></ul>');
                }
            }

            // Sample package downloaded successfully.
            wp_send_json_success();
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
            // Gives us access to the download_url() and wp_handle_sideload() functions.
            if (!function_exists('download_url')) {
                require_once ABSPATH.'wp-admin/includes/file.php';
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
                $wp_filesystem->copy($result, $target, true);

                $content = ($content = filesize($target)) ? $content : $wp_filesystem->size($target);
            } else {
                $content = ($content = file_get_contents($result)) ? $content : $wp_filesystem->get_contents($result);

                // Remove downloaded file.
                unlink($result) || $wp_filesystem->delete($result);
            }

            return $content;
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

        public static function print_confirm_message($action, $package = array())
        {
            echo '<div class="install-sample-popup">';

            switch ($action) {
                case 'install' :
                    ?>
                    <div class="popup-content">
                        <div id="sample-data-installation-step-1">
                            <div class="notice-warning settings-error notice">
                                <strong class="label label-danger">
                                    <?php esc_html_e('Important Notice', 'ovic-import'); ?>
                                </strong>
                                <ul>
                                    <li><?php printf(__('Installing sample data will replace the content of current website with <a href="%1$s" target="_blank" rel="noopener noreferrer"><strong>%2$s</strong></a>.',
                                            'ovic-import'), $package['demo'], $package['name']); ?></li>
                                    <li><?php esc_html_e('You can later uninstall sample data to restore the original data back.',
                                            'ovic-import'); ?></li>
                                </ul>
                            </div>
                            <?php
                            if (array_key_exists('sample-page', $package) && !empty($package['sample-page'])) :
                                ?>
                                <div id="sample-data-installation-options" class="import-sample-data-wrap">

                                    <div class="radio">
                                        <label>
                                            <input name="extend_settings" type="checkbox" value="1">
                                            <?php esc_html_e('Extend Settings', 'ovic-import'); ?>
                                        </label>
                                    </div>
                                    <div class="box-wrap three-col select-page" style="display: none;">
                                        <p><?php esc_html_e('Select demo that you want to install.',
                                                'ovic-import'); ?></p>
                                        <?php foreach (( array ) $package['sample-page'] as $id => $page) : ?>
                                            <div class="col">
                                                <div class="box">
                                                    <a href="javascript:void(0)">
                                                        <img src="<?php echo esc_url($page['thumbnail']); ?>"
                                                             alt="<?php echo esc_attr($page['name']); ?>"/>
                                                    </a>
                                                    <div class="box-info">
                                                        <h5><?php echo esc_html($page['name']); ?></h5>
                                                    </div>
                                                    <input type="radio" name="sample_page" style="display: none;"
                                                           value="<?php
                                                           echo esc_attr($id);
                                                           ?>"/>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="checkbox">
                                <p style="margin:0;padding:0;">
                                    <label>
                                        <input name="agree" value="1" id="confirm-sample-data-installation"
                                               type="checkbox">
                                        <?php esc_html_e('I understand the impact of installing sample data.',
                                            'ovic-import'); ?>
                                    </label>
                                </p>
                                <p class="attachment-options" style="padding:0;">
                                    <?php
                                    $remote_txt      = esc_html__('All demo images will be accessed remotely - FAST IMPORT',
                                        'ovic-import');
                                    $local_txt       = esc_html__('All demo images will be downloaded and stored locally - SLOW IMPORT',
                                        'ovic-import');
                                    $placeholder_txt = esc_html__('All demo images will be replaced with gray images from https://via.placeholder.com - FASTEST IMPORT',
                                        'ovic-import');
                                    ?>
                                    <label class="wrapper-storage">
                                        <label for="confirm-sample-data-images-placeholder">
                                            <input name="images_storage" value="placeholder"
                                                   id="confirm-sample-data-images-placeholder"
                                                   type="radio" checked
                                                   data-txt="<?php echo esc_attr($placeholder_txt); ?>">
                                            <?php esc_html_e('Placeholder', 'ovic-import'); ?>
                                        </label>
                                        <label for="confirm-sample-data-images-remote">
                                            <input name="images_storage" value="remote"
                                                   id="confirm-sample-data-images-remote"
                                                   type="radio"
                                                   data-txt="<?php echo esc_attr($remote_txt); ?>">
                                            <?php esc_html_e('Remote', 'ovic-import'); ?>
                                        </label>
                                        <label for="confirm-sample-data-images-local">
                                            <input name="images_storage" value="local"
                                                   id="confirm-sample-data-images-local"
                                                   type="radio" data-txt="<?php echo esc_attr($local_txt); ?>">
                                            <?php esc_html_e('Local', 'ovic-import'); ?>
                                        </label>
                                    </label>
                                    <span class="desc alert alert-success"><?php echo esc_html($placeholder_txt); ?></span>
                                </p>
                            </div>
                            <div class="installation-actions">
                                <button data-package="<?php echo esc_attr($package['id']); ?>"
                                        id="config-action" disabled="disabled"
                                        class="button button-primary button-install">
                                    <?php esc_html_e('Continue', 'ovic-import'); ?>
                                </button>
                                <button id="cancel-action"
                                        class="button button-cancel">
                                    <?php esc_html_e('Cancel', 'ovic-import'); ?>
                                </button>
                            </div>

                        </div>
                        <div id="sample-data-installation-step-2" class="hidden">
                            <p style="margin-top:0;padding-top:0;">
                                <?php esc_html_e('There are several stages involved in the process. Please be patient.',
                                    'ovic-import'); ?>
                            </p>
                            <ul id="install-processes">
                                <li id="install-sample-data-download-package">
                                    <span class="title"><?php esc_html_e('Download sample data package.',
                                            'ovic-import'); ?></span>
                                    <span class="spinner is-active"></span>
                                </li>
                                <li id="install-sample-data-import-data" class="hidden">
                                    <span class="title"><?php esc_html_e('Install sample data.',
                                            'ovic-import'); ?></span>
                                    <span class="spinner is-active"></span>
                                </li>
                                <li id="install-sample-data-attachment" class="hidden">
                                    <span class="title"><?php esc_html_e('Download Attachment',
                                            'ovic-import'); ?></span>
                                    <span class="spinner is-active"></span>
                                </li>
                                <li id="install-sample-data-required-plugins" class="hidden">
                                    <span class="title"><?php esc_html_e('Install required plugins.',
                                            'ovic-import'); ?></span>
                                    <span class="spinner is-active"></span>
                                    <div class="install-status"></div>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar">
                                            <span class="percentage">0</span>%
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div id="install-sample-data-success-message" class="success-message hidden">
                            <h3>
                                <?php esc_html_e('Sample data was successfully installed.', 'ovic-import'); ?>
                            </h3>
                            <p>
                                <a href="<?php echo esc_url(get_home_url()); ?>"
                                   class=""><?php esc_html_e('Visit Site', 'ovic-import'); ?></a>

                            </p>
                        </div>
                        <div id="install-sample-data-failure-message" class="failure-message hidden">
                            <h3>
                                <?php esc_html_e('Sample data was not successfully installed.', 'ovic-import'); ?>
                            </h3>
                        </div>
                    </div>
                    <?php

                    break;

                case 'uninstall' :
                    ?>
                    <div class="popup-content">
                        <div id="sample-data-uninstallation-step-1">
                            <div class="notice-warning settings-error notice">
                                <strong class="label label-danger">
                                    <?php esc_html_e('Important Notice', 'ovic-import'); ?>
                                </strong>
                                <ul>
                                    <li><?php esc_html_e('Uninstalling sample data will restore the backed up original data before the current sample data was installed..',
                                            'ovic-import'); ?></li>
                                </ul>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input name="agree" value="1" id="confirm-sample-data-installation"
                                           type="checkbox">
                                    <?php esc_html_e('	I understand the impact of uninstalling sample data.',
                                        'ovic-import'); ?>
                                </label>
                            </div>
                            <div class="uninstallation-actions">
                                <button data-package="<?php echo esc_attr($package['id']); ?>" id="config-action"
                                        disabled="disabled"
                                        class="button button-primary button-uninstall"><?php esc_html_e('Continue',
                                        'ovic-import'); ?></button>
                                <button id="cancel-action"
                                        class="button button-cancel"><?php esc_html_e('Cancel',
                                        'ovic-import'); ?></button>
                            </div>
                        </div>
                        <div id="sample-data-uninstallation-step-2" class="hidden">
                            <p>
                                <?php esc_html_e('There are several stages involved in the process. Please be patient.',
                                    'ovic-import'); ?>
                            </p>
                            <ul id="install-processes">
                                <li id="uninstall-sample-data">
                                    <span class="title"><?php esc_html_e('Uninstall', 'ovic-import'); ?></span>
                                    <span class="spinner is-active"></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <?php

                    break;

                case 'failed_security' :
                    ?>
                    <div class="popup-content">
                        <div id="sample-data-failed_security">
                            <div class="notice-warning settings-error notice" style="margin-bottom:0">
                                <ul>
                                    <li><?php esc_html_e('Nonce verification failed. This might due to your working session has been expired. Please reload the page to renew your working session.',
                                            'ovic-import'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php

                    break;

                case 'failed_package' :
                    ?>
                    <div class="popup-content">
                        <div id="sample-data-failed_package">
                            <div class="notice-warning settings-error notice" style="margin-bottom:0">
                                <ul>
                                    <li><?php esc_html_e('Failed to get data for the selected sample data package.',
                                            'ovic-import'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php

                    break;

                case 'missing_package' :
                    ?>
                    <div class="popup-content">
                        <div id="sample-data-missing_package">
                            <div class="notice-warning settings-error notice" style="margin-bottom:0">
                                <ul>
                                    <li><?php esc_html_e('Missing sample data package to install.',
                                            'ovic-import'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php

                    break;

                case 'clear_temporary' :
                    $class = 'notice-warning settings-error notice';
                    $text = esc_html__('Temp folder is do not exist.', 'ovic-import');
                    if ($package['success'] == 'yes') {
                        $class = 'notice-success notice';
                        $text  = sprintf('%s %s',
                            $package['count'],
                            esc_html__('Temp folder is removed.', 'ovic-import')
                        );
                    }
                    ?>
                    <div class="popup-content">
                        <div id="sample-data-clear_temporary">
                            <div class="<?php echo esc_attr($class); ?>" style="margin-bottom:0">
                                <ul>
                                    <li><?php echo esc_html($text); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php

                    break;
            }

            echo '</div>';

            wp_die();
        }

        public static function get_sample_package($package)
        {
            $packages = self::get_sample_packages();

            return isset($packages[$package]) ? $packages[$package] : array();
        }

        public static function get_sample_packages()
        {
            $packages = array();
            $packages = apply_filters('import_sample_data_packages', $packages);

            return $packages;
        }

        /**
         * Get remote URL for demo image.
         *
         * @param  string  $url  URL for the given attachment.
         * @param  int  $post_id  Attachment ID.
         *
         * @return  string
         */
        public static function get_attachment_url($url, $post_id)
        {
            // Check if attachment file exists.
            $upload     = wp_upload_dir();
            $attachment = get_post($post_id);
            $file       = str_replace($upload['baseurl'], $upload['basedir'], $url);
            if (!@is_file($file) && $attachment) {
                if (preg_match('#'.self::$demo_site_pattern.self::$demo_image_pattern.'#i', $attachment->guid)) {
                    // Get base local and remote URL.
                    $remote_base = current(explode('/wp-content/uploads/', $attachment->guid)).'/wp-content/uploads';
                    $remote_base = str_replace(
                        array('https://', 'http://'),
                        array('', ''),
                        $remote_base
                    );
                    // Replace local base with remote base.
                    $baseurl = str_replace(
                        array('https://', 'http://'),
                        array('', ''),
                        $upload['baseurl']
                    );
                    $url     = str_replace($baseurl, $remote_base, $url);

                    if (self::$demo_images_storage == 'placeholder') {
                        $metadata = wp_get_attachment_metadata($attachment->ID);
                        $size     = $metadata['width'].'x'.$metadata['height'];
                        $url      = 'https://via.placeholder.com/'.$size.'&text='.$size;
                    }
                }
            }

            return $url;
        }

        /**
         * Get remote source for demo image.
         *
         * @param  array|false  $image  Either array with src, width & height, icon src, or false.
         * @param  int  $attachment_id  Image attachment ID.
         * @param  string|array  $size  Size of image. Image size or array of width and height values (in that order). Default 'thumbnail'.
         * @param  bool  $icon  Whether the image should be treated as an icon. Default false.
         *
         * @return  array|false
         */
        public static function get_attachment_image_src($image, $attachment_id, $size, $icon)
        {
            // Check if attachment file exists.
            $upload = wp_upload_dir();
            $file   = str_replace($upload['baseurl'], $upload['basedir'], $image[0]);

            if (!@is_file($file) && $attachment = get_post($attachment_id)) {
                if (preg_match('#'.self::$demo_site_pattern.self::$demo_image_pattern.'#i', $attachment->guid)) {
                    // Get base local and remote URL.
                    $remote_base = current(explode('/wp-content/uploads/', $attachment->guid)).'/wp-content/uploads';
                    $remote_base = str_replace(
                        array('https://', 'http://'),
                        array('', ''),
                        $remote_base
                    );
                    // Replace local base with remote base.
                    $baseurl  = str_replace(
                        array('https://', 'http://'),
                        array('', ''),
                        $upload['baseurl']
                    );
                    $image[0] = str_replace($baseurl, $remote_base, $image[0]);

                    if (self::$demo_images_storage == 'placeholder') {
                        $metadata = wp_get_attachment_metadata($attachment->ID);
                        $size     = $metadata['width'].'x'.$metadata['height'];
                        $image[0] = 'https://via.placeholder.com/'.$size.'&text='.$size;
                    }
                }
            }

            return $image;
        }

        /**
         * Calculate remote source set for demo image.
         *
         * @param  array  $sources  {
         *     One or more arrays of source data to include in the 'srcset'.
         *
         * @param  array  $size_array  Array of width and height values in pixels (in that order).
         * @param  string  $image_src  The 'src' of the image.
         * @param  array  $image_meta  The image meta data as returned by 'wp_get_attachment_metadata()'.
         * @param  int  $attachment_id  Image attachment ID or 0.
         *
         * @return  array|false
         */
        public static function calculate_image_srcset($sources, $size_array, $image_src, $image_meta, $attachment_id)
        {
            foreach ($sources as $width => $define) {
                // Check if attachment file exists.
                $upload = isset($upload) ? $upload : wp_upload_dir();
                $file   = str_replace($upload['baseurl'], $upload['basedir'], $define['url']);

                if (!@is_file($file)) {
                    if (preg_match('#'.self::$demo_site_pattern.self::$demo_image_pattern.'#i', $image_src)) {
                        $remote_src = $image_src;
                    } elseif ($attachment = get_post($attachment_id)) {
                        if (preg_match('#'.self::$demo_site_pattern.self::$demo_image_pattern.'#i',
                            $attachment->guid)) {
                            $remote_src = $attachment->guid;
                        }
                    }

                    if (isset($remote_src)) {
                        // Get base local and remote URL.
                        $remote_base = current(explode('/wp-content/uploads/', $remote_src)).'/wp-content/uploads';
                        $remote_base = str_replace(
                            array('https://', 'http://'),
                            array('', ''),
                            $remote_base
                        );
                        // Replace local base with remote base.
                        $baseurl                = str_replace(
                            array('https://', 'http://'),
                            array('', ''),
                            $upload['baseurl']
                        );
                        $sources[$width]['url'] = str_replace($baseurl, $remote_base, $define['url']);

                        if (self::$demo_images_storage == 'placeholder') {
                            $metadata = wp_get_attachment_metadata($attachment->ID);
                            if (!empty($metadata)) {
                                $size                   = $metadata['width'].'x'.$metadata['height'];
                                $sources[$width]['url'] = 'https://via.placeholder.com/'.$size.'&text='.$size;
                            }
                        }
                    }
                }
            }

            return $sources;
        }

        /**
         * Prepare HTML for post thumbnail.
         *
         * @param  string  $html  The post thumbnail HTML.
         * @param  int  $post_id  The post ID.
         * @param  string  $post_thumbnail_id  The post thumbnail ID.
         * @param  string|array  $size  The post thumbnail size. Image size or array of width and height
         *                                            values (in that order). Default 'post-thumbnail'.
         * @param  string  $attr  Query string of attributes.
         *
         * @return  string
         */
        public static function post_thumbnail_html($html, $post_id, $post_thumbnail_id, $size, $attr)
        {
            $upload = wp_upload_dir();

            if ($attachment = get_post($post_thumbnail_id)) {
                if (preg_match('#'.self::$demo_site_pattern.self::$demo_image_pattern.'#i', $attachment->guid)) {
                    // Get base remote URL.
                    $remote_base = current(explode('/wp-content/uploads/', $attachment->guid)).'/wp-content/uploads';
                    $remote_base = str_replace(
                        array('https://', 'http://'),
                        array('', ''),
                        $remote_base
                    );
                    // Replace local base with remote base.
                    $baseurl = str_replace(
                        array('https://', 'http://'),
                        array('', ''),
                        $upload['baseurl']
                    );
                    $html    = str_replace($baseurl, $remote_base, $html);

                    if (self::$demo_images_storage == 'placeholder') {
                        $metadata = wp_get_attachment_metadata($attachment->ID);
                        $size     = $metadata['width'].'x'.$metadata['height'];
                        $html     = str_replace(
                            $attachment->guid,
                            'https://via.placeholder.com/'.$size.'&text='.$size,
                            $html
                        );
                    }
                }
            }

            return $html;
        }

        public static function export_sample_data()
        {
            if (isset($_REQUEST['import_sample_data_action']) && $_REQUEST['import_sample_data_action'] == 'export-sample-data') {
                $backup_filename = isset($_REQUEST['backup_filename']) ? $_REQUEST['backup_filename'] : '';
                $nonce           = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : '';

                if (wp_verify_nonce($nonce, 'export-sample-data-form') && !empty($backup_filename)) {
                    self::generate_sample_data(true, $backup_filename);
                }
            }
        }

        public static function generate_sample_data($download, $backup_filename)
        {
            // Generate path to downloaded sample data package.
            $upload_dir = wp_upload_dir();

            // Get WordPress database object and table prefix.
            global $wpdb, $table_prefix;

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

            // Get all tables in database.
            $tables = $wpdb->get_results('SHOW TABLES;', ARRAY_N);

            $blog_prefix = $wpdb->get_blog_prefix();
            $ms_global   = $wpdb->tables('ms_global');
            $global      = $wpdb->tables('global');

            // Backup current data.
            $backup_dir = "{$upload_dir['basedir']}/".self::$sample_data_folder."/sample-data/export-data/".date('YmdHis');
            $num_file   = 1;
            $queries    = '';

            if (!self::prepare_directory($backup_dir)) {
                wp_send_json_error(esc_html__('Failed to create directory to store database backup file.',
                    'ovic-import'));
            }
            $exclude_table = array(
                'options',
                'users',
                'usermeta',
                'site',
                'sitemeta',
                'wc_download_log',
                'wc_customer_lookup',
                'woocommerce_downloadable_product_permissions',
            );
            foreach ($ms_global as $name => $value) {
                array_push($exclude_table, $name);
            }
            if ($tables && count($tables)) {
                foreach ($tables as $key => $table) {
                    $table = $table[0];

                    // Check Mutilsite Table
                    $a = str_replace($blog_prefix, '', $table);
                    $b = (int) $a;
                    if ($b > 0 || strpos($a, $wpdb->base_prefix) !== false) {
                        continue;
                    }

                    //Exclude Table
                    if (in_array(str_replace($table_prefix, '', $table), $exclude_table)) {
                        continue;
                    }

                    $table_replace = str_replace($table_prefix, '#__', $table);

                    // Drop existing table first.
                    $queries .= "DROP TABLE IF EXISTS `{$table_replace}`;\n";

                    // Get table creation schema.
                    $results = $wpdb->get_results("SHOW CREATE TABLE `{$table}`;", ARRAY_A);

                    $results = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $results[0]['Create Table']);
                    $results = str_replace($table, $table_replace, $results);
                    $queries .= str_replace("\n", '', $results).";\n";

                    // Get table data.
                    $i = 0;

                    do {
                        $results = $wpdb->get_results("SELECT * FROM `{$table}` WHERE 1 LIMIT {$i}, 500;", ARRAY_A);

                        if ($results) {
                            foreach ($results as $result) {
                                // Generate column list.
                                $keys = '(`'.implode('`, `', array_keys($result)).'`)';

                                // Generate value list.
                                $values = array();

                                foreach (array_values($result) as $value) {
                                    $values[] = str_replace(
                                        array('\\', "\r", "\n", "'"),
                                        array('\\\\', '\\r', '\\n', "\\'"),
                                        $value
                                    );
                                }

                                $values = "('".implode("', '", $values)."')";

                                // Store insert query.
                                $query = "INSERT INTO `{$table_replace}` {$keys} VALUES {$values};\n";

                                if (strlen($queries.$query) > self::$max_backup_file_size) {
                                    // Generate backup file path.
                                    $path = str_repeat('0', 4 - strlen(( string ) $num_file));
                                    $path = "{$backup_dir}/sample_data_{$path}{$num_file}.sql";

                                    // Write current data to file.

                                    self::put_content($path, $queries);

                                    // Reset data.
                                    $queries = '';

                                    // Increase file counter.
                                    $num_file++;
                                }

                                $queries .= $query;
                            }

                            $i += count($results);
                        }
                    } while ($results && count($results));
                }
            }
            // Export Options
            $options       = '';
            $table_options = $wpdb->prefix.'options';
            $i             = 0;
            do {
                $results = $wpdb->get_results("SELECT option_name,option_value, autoload FROM `{$table_options}` WHERE 1 LIMIT {$i}, 500;",
                    ARRAY_A);

                if ($results) {
                    foreach ($results as $result) {
                        if (in_array($result['option_name'], self::$reserved_options)) {
                            continue;
                        }
                        if ($result['option_name'] == 'permalink_structure') {
                            if (strpos($result['option_value'], '/blog') !== false) {
                                $result['option_value'] = str_replace('/blog', '', $result['option_value']);
                            }
                        }
                        // Generate column list.
                        $keys = '(`'.implode('`, `', array_keys($result)).'`)';

                        // Generate value list.
                        $values = array();

                        foreach (array_values($result) as $value) {
                            $values[] = str_replace(
                                array('\\', "\r", "\n", "'"),
                                array('\\\\', '\\r', '\\n', "\\'"),
                                $value
                            );
                        }

                        $values = "('".implode("', '", $values)."')";

                        // Store insert query.
                        $query = "REPLACE INTO `#__options` {$keys} VALUES {$values};\n";

                        if (strlen($queries.$query) > self::$max_backup_file_size) {
                            // Generate backup file path.
                            $path = str_repeat('0', 4 - strlen(( string ) $num_file));
                            $path = "{$backup_dir}/sample_data_{$path}{$num_file}.sql";

                            // Write current data to file.

                            self::put_content($path, $queries);

                            // Reset data.
                            $queries = '';

                            // Increase file counter.
                            $num_file++;
                        }
                        $queries .= $query;
                    }

                    $i += count($results);
                }
            } while ($results && count($results));

            // Finalize backup task.
            if ($queries != '') {
                // Generate backup file path.
                if ($num_file > 1) {
                    $path = str_repeat('0', 4 - strlen(( string ) $num_file));
                    $path = "{$backup_dir}/sample_data_{$path}{$num_file}.sql";
                } else {
                    $path = "{$backup_dir}/sample_data.sql";
                }

                // Write data to file.
                self::put_content($path, $queries);
            }

            if ($queries != '') {
                // Zip and download
                if ($num_file > 1) {
                    $files = array();
                    for ($i = 1; $i <= $num_file; $i++) {
                        $path    = str_repeat('0', 4 - strlen(( string ) $i));
                        $files[] = "sample_data_{$path}{$i}.sql";
                    }
                } else {
                    $files = array('sample_data.sql');
                }

                self::createZipAndDownload(
                    $files,
                    $backup_dir.'/',
                    $backup_filename.'.zip',
                    $download
                );
            }
        }

        public static function createZipAndDownload($files, $filesPath, $zipFileName, $download)
        {
            $zipFileName = ($download === true) ? $zipFileName : $download.$zipFileName;

            // Create instance of ZipArchive. and open the zip folder.
            $zip = new ZipArchive();

            if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                exit("cannot open <$zipFileName>\n");
            }
            // Adding every attachments files into the ZIP.
            foreach ($files as $file) {
                $zip->addFile($filesPath.$file, $file);
            }

            $zip->close();

            if ($download === true) {
                // Download the created zip file
                header("Content-type: application/zip");
                header("Content-Disposition: attachment; filename = $zipFileName");
                header("Pragma: no-cache");
                header("Expires: 0");
                readfile("$zipFileName");
                exit;
            }
        }

        public static function put_content($path, $content)
        {
            $file = fopen($path, 'w');
            if ($file) {
                fwrite($file, $content);
                fclose($file);

                return true;
            }

            return false;
        }
    }
}