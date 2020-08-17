<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WK_RMA_Install')) {

    /**
     *     Activation class
     */

    class MP_RMA_Install
    {

        public function mp_rma_activation()
        {

            add_action('admin_init', array($this, 'wk_rma_settings'));

            $this->wk_rma_create_table();

        }

        public function wk_rma_settings()
        {
            register_setting('mp_rma_settings_group', 'mp_rma_status');
            register_setting('mp_rma_settings_group', 'mp_rma_time');
            register_setting('mp_rma_settings_group', 'mp_rma_order_statuses');
            register_setting('mp_rma_settings_group', 'mp_rma_address');
            register_setting('mp_rma_settings_group', 'mp_rma_policy');
        }

        public function wk_rma_create_table()
        {
            global $wpdb;

            $table_name = $wpdb->prefix . 'mp_rma_reasons';

            $charset_collate = $wpdb->get_charset_collate();

            if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {

                $sql = "CREATE TABLE $table_name (
										`id` bigint(20) NOT NULL AUTO_INCREMENT,
										`user_id` bigint(20) NOT NULL,
										`reason` varchar(2000) NOT NULL,
										`status` varchar(20) NOT NULL,
										PRIMARY KEY (id)
								) $charset_collate;";

                if (!function_exists('dbDelta')) {
                    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                }

                dbDelta($sql);

            }

            $table_name1 = $wpdb->prefix . 'mp_rma_requests';

            if ($wpdb->get_var("show tables like '$table_name1'") != $table_name1) {

                $sql1 = "CREATE TABLE $table_name1 (
										`id` bigint(20) NOT NULL AUTO_INCREMENT,
										`order_no` bigint(20) NOT NULL,
										`customer_id` bigint(20) NOT NULL,
										`seller_id` bigint(20) NOT NULL,
										`items` text NOT NULL,
										`images_path` text NOT NULL,
										`information` text NOT NULL,
										`order_status` varchar(200) NOT NULL,
										`rma_status` varchar(200) NOT NULL DEFAULT 'pending',
										`resolution` varchar(200) NOT NULL,
										`consignment_num` bigint(20) NOT NULL,
										`datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
										PRIMARY KEY (id)
								) $charset_collate;";

                if (!function_exists('dbDelta')) {
                    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                }

                dbDelta($sql1);

            }

            $table_name2 = $wpdb->prefix . 'mp_rma_conversation';

            if ($wpdb->get_var("show tables like '$table_name2'") != $table_name2) {

                $sql1 = "CREATE TABLE $table_name2 (
										`id` bigint(20) NOT NULL AUTO_INCREMENT,
										`rma_id` bigint(20) NOT NULL,
										`user_id` bigint(20) NOT NULL,
										`message` longtext NOT NULL,
										`attachment` text NOT NULL,
										`datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
										PRIMARY KEY (id)
								) $charset_collate;";

                if (!function_exists('dbDelta')) {
                    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                }

                dbDelta($sql1);

            }

            $table_name3 = $wpdb->prefix . 'mp_rma_request_meta';

            if ($wpdb->get_var("show tables like '$table_name3'") != $table_name3) {

                $sql1 = "CREATE TABLE $table_name3 (
										`id` bigint(20) NOT NULL AUTO_INCREMENT,
										`rma_id` bigint(20) NOT NULL,
										`meta_key` varchar(255) NOT NULL,
										`meta_value` longtext NOT NULL,
										PRIMARY KEY (id)
								) $charset_collate;";

                if (!function_exists('dbDelta')) {
                    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                }

                dbDelta($sql1);

            }

        }

    }

}
