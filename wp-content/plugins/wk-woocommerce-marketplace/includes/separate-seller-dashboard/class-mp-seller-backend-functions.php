<?php
/**
 * File for seller backend functions.
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Mp_Seller_Backend_Functions')) {
    /**
     * Seller dashboard backend class.
     */
    class Mp_Seller_Backend_Functions
    {
        /**
         * Seller menu at backend.
         */
        public function mp_seller_admin_menu()
        {
            add_menu_page(__('Seller', 'marketplace'), __('Marketplace', 'marketplace'), 'wk_marketplace_seller', 'seller', array($this, 'mp_seller_admin_dashboard'), WK_MARKETPLACE.'assets/images/MP.png', 55);

            add_submenu_page('seller', __('Seller Dashboard', 'marketplace'), __('Reports', 'marketplace'), 'wk_marketplace_seller', 'seller', array($this, 'mp_seller_admin_dashboard'));

            $hook_option = add_submenu_page('seller', __('Orders', 'marketplace'), __('Order History', 'marketplace'), 'wk_marketplace_seller', 'order-history', array($this, 'mp_seller_admin_order_history'));

            add_submenu_page('seller', __('Transaction', 'marketplace'), __('Transaction', 'marketplace'), 'wk_marketplace_seller', 'seller-transaction', array($this, 'mp_seller_admin_transactions'));

            add_submenu_page('seller', __('Notifications', 'marketplace'), __('Notifications', 'marketplace'), 'wk_marketplace_seller', 'seller-notifications', array($this, 'mp_seller_admin_notifications'));

            add_submenu_page('seller', __('Shop Followers', 'marketplace'), __('Shop Followers', 'marketplace'), 'wk_marketplace_seller', 'seller-shop-followers', array($this, 'mp_seller_admin_shop_followers'));

            add_submenu_page('seller', __('My Profile', 'marketplace'), __('My Profile', 'marketplace'), 'wk_marketplace_seller', 'seller-profile', array($this, 'mp_seller_admin_side_profile'));

            add_submenu_page('seller', __('Ask to admin', 'marketplace'), __('Ask to admin', 'marketplace'), 'wk_marketplace_seller', 'ask-to-admin', array($this, 'mp_seller_ask_to_admin'));

            add_action('load-'.$hook_option, array($this, 'mp_add_page_option_order_list'));

            add_filter('set-screen-option', array($this, 'mp_order_table_set_option'), 10, 3);
            
        }

        /**
         * Seller shop followers tab.
         */
        public function mp_seller_admin_shop_followers()
        {
            require 'class-seller-shop-followers.php';
        }

        /**
         * Seller notifications menu.
         */
        public function mp_seller_admin_notifications()
        {
            require_once WK_MARKETPLACE_DIR.'includes/admin/class-mp-notifications.php';
        }

        /**
         * Add option for order list.
         */
        public function mp_add_page_option_order_list()
        {
            $args = array(
                'label' => __('Order per page', 'marketplace'),
                'default' => 10,
                'option' => 'order_per_page',
            );

            add_screen_option('per_page', $args);
        }

        /**
         * Function to set order table option.
         *
         * @param string $status
         * @param string $option
         * @param string $value
         */
        public function mp_order_table_set_option($status, $option, $value)
        {
            return $value;
        }

        /**
         * Shows seller transaction.
         */
        public function mp_seller_admin_transactions()
        {
            if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['tid']) && !empty($_GET['tid'])) {
                global $transaction, $commission;
                $current_user = get_current_user_id();
                $transaction_id = filter_input(INPUT_GET, 'tid', FILTER_SANITIZE_NUMBER_INT);
                $transaction_detail = $transaction->get_by_id($transaction_id, $current_user);
                $admin_rate = $commission->get_admin_rate($current_user);
                extract($transaction_detail);

                $columns = apply_filters('mp_account_transactions_columns', array(
                    'order-id' => __('Order Id', 'woocommerce'),
                    'product-name' => __('Product Name', 'woocommerce'),
                    'product-quantity' => __('Qty', 'woocommerce'),
                    'total-price' => __('Order Total', 'woocommerce'),
                    'commission' => __('Commission', 'woocommerce'),
                    'subtotal' => __('Transaction Total', 'woocommerce'),
                ));

                include WK_MARKETPLACE_DIR.'includes/templates/admin/account/seller/transaction/view.php';
            } else {
                include 'class-seller-transaction-list.php';
            }
        }

        public function mp_seller_admin_dashboard()
        {
            if (current_user_can('manage_woocommerce')) {
                if (!class_exists('WC_Admin_Report')) {
                    require WC_ABSPATH.'includes/admin/reports/class-wc-admin-report.php';
                }

                require_once WK_MARKETPLACE_DIR.'includes/templates/front/myaccount/class-mp-report-dashboard.php';

                $dash_obj = new MP_Report_Dashboard();

                echo '<div class="wrap"><h1>'.esc_html__('Dashboard', 'marketplace').'</h1><hr>';

                $dash_obj->mp_dashboard_page();

                echo '</div>';
            } else {
                wp_safe_redirect(admin_url('?page=seller'));
            }
        }

        public function mp_seller_admin_order_history()
        {
            if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['oid']) && !empty($_GET['oid'])) {
                include WK_MARKETPLACE_DIR.'includes/templates/front/class-mp-order-functions.php';
                include WK_MARKETPLACE_DIR.'includes/templates/front/order/order-view.php';
            } else {
                include 'class-seller-order-list.php';
            }
        }

        public function mp_seller_admin_side_profile()
        {
            if (isset($_GET['page']) && $_GET['page'] == 'seller-profile') {
                include 'class-seller-mp-profile.php';
            }
        }

        public function mp_seller_product_type_selector($types)
        {
            global $pagenow, $current_user, $post;

            $allowed_product_types = get_option('wkmp_seller_allowed_product_types');

            if ($allowed_product_types) {
                if ('post.php' == $pagenow && in_array('wk_marketplace_seller', $current_user->roles, true)) {
                    $product_id = $post->ID;
                    $product = wc_get_product($product_id);
                    $type = $product->get_type();
                    if (!in_array($type, $allowed_product_types, true)) {
                        array_push($allowed_product_types, $type);
                    }
                    foreach ($types as $key => $value) {
                        if (!in_array($key, $allowed_product_types, true)) {
                            unset($types[$key]);
                        }
                    }
                }
            }

            return $types;
        }

        /**
         * Function for overriding terms.
         *
         * @param array $args       args array
         * @param array $taxonomies taxonomies array
         */
        public function mp_override_get_terms_args($args, $taxonomies)
        {
            global $pagenow, $current_user;

            if (!in_array('product_cat', $taxonomies, true)) {
                return $args;
            }

            if (('post.php' == $pagenow || 'post-new.php' == $pagenow) && in_array('product_cat', $taxonomies, true) && in_array('wk_marketplace_seller', $current_user->roles, true)) {
                $allowed_cat = get_user_meta($current_user->ID, 'wkmp_seller_allowed_categories', true);

                if (!$allowed_cat) {
                    $allowed_categories = get_option('wkmp_seller_allowed_categories');
                } else {
                    $allowed_categories = $allowed_cat;
                }

                $args['slug'] = $allowed_categories;
            }

            return $args;
        }

        /**
         * Function for filtering product query.
         *
         * @param obj $query qury object
         */
        public function mp_products_admin_filter_query($query)
        {
            global $typenow, $current_user;

            if ('product' == $typenow && in_array('wk_marketplace_seller', $current_user->roles, true)) {
                $query->query_vars['author'] = $current_user->ID;
            }
        }

        /**
         * Add the capabilities to seller role.
         */
        public function mp_seller_capabilities()
        {
            $role = get_role('wk_marketplace_seller');

            $role->add_cap('manage_woocommerce');
            $role->add_cap('read_product');
            $role->add_cap('edit_product');
            $role->add_cap('delete_product');
            $role->add_cap('edit_products');
            $role->add_cap('publish_products');
            $role->add_cap('read_private_products');
            $role->add_cap('delete_products');
            $role->add_cap('edit_published_products');
            $role->add_cap('delete_published_products');
            $role->add_cap('assign_product_terms');
        }

        /**
         * Function for managing seller setting tabs.
         *
         * @param array $tabs array of tabs
         */
        public function mp_manage_wc_settings_tab_seller($tabs)
        {
            global $current_user, $current_tab;

            if (is_admin() && in_array('wk_marketplace_seller', $current_user->roles, true)) {
                if ($current_tab !== 'shipping') {
                    wp_safe_redirect(admin_url('admin.php?page=wc-settings&tab=shipping'));
                    exit;
                }

                return array(
                    'shipping' => $tabs['shipping'],
                );
            } else {
                return $tabs;
            }
        }

        /**
         * Function for managing shipping submenu.
         *
         * @param array $sections section array
         */
        public function mp_manage_wc_shipping_submenu($sections)
        {
            global $current_user, $current_section;

            if (is_admin() && in_array('wk_marketplace_seller', $current_user->roles, true)) {
                if (!in_array($current_section, array('', 'classes'), true)) {
                    wp_safe_redirect(admin_url('admin.php?page=wc-settings&tab=shipping&section'));
                    exit;
                }

                return array(
                    '' => $sections[''],
                    'classes' => $sections['classes'],
                );
            } else {
                return $sections;
            }
        }

        /**
         * Function for filtering shipping classes.
         *
         * @param array $shipping_classes array of shipping classes
         */
        public function mp_filter_seller_shipping_classes($shipping_classes)
        {
            global $current_user;

            if (is_admin() && in_array('wk_marketplace_seller', $current_user->roles, true)) {
                $user_shipping_classes = get_user_meta($current_user->ID, 'shipping-classes', true);

                if ($user_shipping_classes) {
                    $user_shipping_classes = maybe_unserialize($user_shipping_classes);

                    foreach ($shipping_classes as $key => $value) {
                        if (!in_array($value->term_id, $user_shipping_classes, true)) {
                            unset($shipping_classes[$key]);
                        }
                    }
                }
            }

            return $shipping_classes;
        }

        /**
         * Ask to admin tab in backend.
         */
        public function mp_seller_ask_to_admin()
        {
            if (isset($_GET['page']) && $_GET['page'] == 'ask-to-admin' && isset($_GET['action']) && $_GET['action'] = 'add') {
                require WK_MARKETPLACE_DIR.'includes/front/class-mp-user-functions.php';
                echo '<div class="wrap"><h1 class="wp-heading-inline">'.esc_html__('Ask to Admin', 'marketplace').'</h1>';
                echo '<a href="'.esc_url(admin_url('admin.php?page=ask-to-admin')).'" class="page-title-action">'.esc_html__('Back', 'marketplace').'</a>';
                require WK_MARKETPLACE_DIR.'includes/templates/front/myaccount/ask-to-admin.php';
                echo '</div>';
            } else {
                require 'class-seller-query-list.php';
            }
        }
    }
}
