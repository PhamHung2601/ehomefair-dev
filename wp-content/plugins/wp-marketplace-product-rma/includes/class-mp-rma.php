<?php

/**
 *   files include template
 */

if (!defined('MP_RMA_PATH')) {

    exit('Direct access forbidden.');

}

if (!class_exists('MP_Wk_RMA')) {

    class MP_Wk_RMA
    {

        public function __construct()
        {
            $require = array(

                'common_to_all' => array(
                    'install.php',
                    'includes/class-mp-rma-getter.php',
                    'includes/class-mp-rma-ajax-functions.php',
                    'includes/class-mp-save-rma-reason.php',
                    'includes/class-mp-rma-details.php',
                    'includes/class-mp-rma-products.php',
                    'includes/class-mp-rma-images.php',
                    'includes/class-mp-rma-conversation.php',
                    'includes/class-mp-save-rma-conversation.php',
                ),
                'adminend' => array(
                    'includes/admin/class-mp-rma-admin-menus.php',
                    'includes/admin/class-mp-rma-settings.php',
                    'includes/admin/class-mp-rma-reasons.php',
                    'includes/admin/class-mp-rma-shipping-label.php',
                    'includes/admin/class-mp-save-rma-shipping-label.php',
                    'includes/admin/class-mp-rma-update-status-manage.php',
                ),
                'frontend' => array(
                    'includes/front/class-mp-rma-front-handler.php',
                    'includes/front/class-mp-manage-rma.php',
                    'includes/front/class-mp-save-rma.php',
                    'includes/front/class-mp-rma-update-manage.php',
                    'includes/front/class-mp-rma-print-shipping-label.php',
                    'includes/front/class-mp-rma-update-status-manage.php',
                ),
            );

            $this->mp_rma_require_files($require);

            add_action('init', array($this, 'wk_rma_front_init'));

            add_action('wp_head', array($this, 'mp_rma_calling_pages'));

        }

        protected function mp_rma_require_files($require_files)
        {

            foreach ($require_files as $section => $classes) {

                foreach ($classes as $class) {

                    if ('common_to_all' == $section || ('frontend' == $section && !is_admin()) || ('adminend' == $section && is_admin()) && file_exists(MP_RMA_PATH . $class)) {

                        require_once MP_RMA_PATH . $class;

                    }

                }

            }

        }

        public function wk_rma_front_init()
        {
            if (!is_admin()) {
                new MP_Rma_Front_Handler();
            }

        }

        public function mp_rma_calling_pages()
        {
            global $current_user, $wpdb, $wp_query;

            $current_user = wp_get_current_user();

            $seller_info = $wpdb->get_var("SELECT user_id FROM " . $wpdb->prefix . "mpsellerinfo WHERE user_id = '" . $current_user->ID . "' and seller_value='seller'");

            $pagename = get_query_var('pagename');

            $main_page = get_query_var('main_page');

            $info = get_query_var('info');

            $action = get_query_var('action');

            $id = get_query_var('pid');

            if (!empty($pagename)) {

                if ($main_page == "manage-rma" && ($current_user->ID || $seller_info > 0)) {
                    require_once MP_RMA_PATH . 'includes/front/class-mp-manage-seller-rma.php';
                    $obj = new MP_Manage_Seller_Rma();
                    add_shortcode('marketplace', array($obj, 'mp_manage_seller_rmas'));
                } else if (($main_page == "add-reason" && ($current_user->ID || $seller_info > 0)) || ($main_page == "rma-reason" && $action == "edit" && ($current_user->ID || $seller_info > 0))) {
                    require_once MP_RMA_PATH . 'includes/class-mp-add-rma-reason.php';
                    $obj = new MP_RMA_Add_Reason();
                    add_shortcode('marketplace', array($obj, 'mp_add_reason_rma'));
                } else if ($main_page == "rma-reason" && ($current_user->ID || $seller_info > 0)) {
                    require_once MP_RMA_PATH . 'includes/front/class-mp-manage-rma-reason.php';
                    $obj = new MP_Manage_Seller_Rma_Reason();
                    add_shortcode('marketplace', array($obj, 'mp_manage_seller_reason'));
                } else if ($main_page == "rma" && $info == "add" && is_user_logged_in() && empty($action)) {
                    require_once MP_RMA_PATH . 'includes/front/class-mp-add-rma.php';
                    $obj = new MP_Add_Front_Rma();
                    add_shortcode('marketplace', array($obj, 'mp_add_new_rma'));
                } else if ($main_page == "rma" && $action == "edit" && is_user_logged_in() && !empty($id)) {
                    require_once MP_RMA_PATH . 'includes/front/class-mp-view-rma.php';
                    $obj = new MP_View_Front_Rma();
                    add_shortcode('marketplace', array($obj, 'mp_front_view_rma'));
                } else if ($main_page == "rma-print" && $action == "edit" && is_user_logged_in() && !empty($id)) {
                    require_once MP_RMA_PATH . 'includes/front/class-mp-rma-print-shipping-label.php';
                    $obj = new MP_Rma_print_Shipping();
                    add_shortcode('marketplace', array($obj, 'mp_rma_print_shipping_label'));
                }

            }

        }

    }

}
