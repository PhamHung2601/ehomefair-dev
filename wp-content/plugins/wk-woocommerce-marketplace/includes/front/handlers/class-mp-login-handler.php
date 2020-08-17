<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('MP_Login_Handler')) {

    class MP_Login_Handler
    {

        /**
         * Process the login form.
         */
        public function process_mp_login()
        {

            if (!empty($_POST['shipping_nonce']) && isset($_POST['shipping_nonce'])) {
                if (!wp_verify_nonce($_POST['shipping_nonce'], 'shipping_action')) {
                    print __('Sorry, your nonce did not verify.', 'marketplace');
                    exit;
                } else {
                    if (isset($_POST['save_shipping_details']) & !empty($_POST['save_shipping_details']) || isset($_POST['update_shipping_details']) & !empty($_POST['update_shipping_details'])) {
                        $saveDetails = new SaveShipingOptions();
                        $saveDetails->saveShippingDetails($_POST);
                    }
                }
            }

            if (!empty($_POST['wkmp_username']) && !empty($_POST['_wpnonce'])) {
                wp_verify_nonce($_POST['_wpnonce'], 'marketplace-username');
                try {
                    $creds = array();
                    $validation_error = new WP_Error();
                    $validation_error = apply_filters('marketplace_process_login_errors', $validation_error, $_POST['wkmp_username'], $_POST['password']);
                    if ($validation_error->get_error_code()) {
                        throw new Exception('<strong>' . __('Error', 'marketplace') . ':</strong> ' . $validation_error->get_error_message());
                    }

                    if (is_email($_POST['wkmp_username'])) {
                        $user = get_user_by('email', $_POST['wkmp_username']);
                        if (isset($user->user_login)) {
                            $creds['user_login'] = $user->user_login;
                        } else {
                            throw new Exception('<strong>' . __('Error', 'marketplace') . ':</strong> ' . __('A user could not be found with this email address.', 'marketplace'));
                        }
                        if ($user->user_activation_key != '' && $user->user_status == 1) {
                            $error_message = '<strong>' . __('Error', 'marketplace') . ':</strong> ' . __('User not activated.', 'marketplace') . '<br>';
                            $error_message .= '<strong>' . __('Error', 'marketplace') . ':</strong> ' . __('Please check your mail for activation link.', 'marketplace');
                            throw new Exception($error_message);
                        }
                    } else {
                        $user = get_user_by('slug', $_POST['wkmp_username']);
                        if (isset($user->user_login)) {
                            $creds['user_login'] = $_POST['wkmp_username'];
                        } else {
                            throw new Exception('<strong>' . __('Error', 'marketplace') . ':</strong> ' . __('A user could not be found with this username.', 'marketplace'));
                        }
                        if ($user->user_activation_key != '' && $user->user_status == 1) {
                            $error_message = '<strong>' . __('Error', 'marketplace') . ':</strong> ' . __('User not activated.', 'marketplace') . '<br>';
                            $error_message .= '<strong>' . __('Error', 'marketplace') . ':</strong> ' . __('Please check you mail for activation link.', 'marketplace');
                            throw new Exception($error_message);
                        }
                    }
                    $creds['user_password'] = $_POST['password'];
                    $secure_cookie = is_ssl() ? true : false;
                    $user = wp_signon($creds, $secure_cookie);
                    if (is_wp_error($user)) {
                        throw new Exception($user->get_error_message());
                    } else {
                        if (wp_get_referer()) {
                            $mp_redirect = wp_get_referer();
                            wp_redirect($mp_redirect);
                            exit;
                        } else {
                            if (isset($_POST['_wp_http_referer'])) {
                                if ($_POST['_wp_http_referer'] != '') {
                                    wp_redirect($_POST['_wp_http_referer']);
                                    die('if');
                                    exit;
                                }
                            } else {
                                wp_redirect(get_post_permalink());
                                die('else');
                                exit;
                            }
                        }
                    }
                } catch (Exception $e) {
                    wc_add_notice(apply_filters('login_errors', $e->getMessage()), 'error');
                }
            } else {
                if (empty($_POST['wkmp_username']) && !empty($_POST['_wpnonce']) && isset($_POST['wkmp_username'])) {
                    $error_message = '<strong>' . __('Error', 'marketplace') . ':</strong> ' . __('Username is required.', 'marketplace') . '<br>';
                    wc_add_notice($error_message, 'error');
                }
            }
        }

        public function mp_login_redirect($redirect, $user)
        {

            if (user_can($user, 'wk_marketplace_seller')) {
                global $wpdb;
                $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option('wkmp_seller_page_title') . "'");
                $redirect = site_url('/' . $page_name . '/'. get_option( 'mp_dashboard', 'dashboard' ));
            }
            return $redirect;
        }

    }

}
