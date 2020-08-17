<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('MP_Register_Handler')) {
    class MP_Register_Handler
    {
        /**
         * Validates seller registration form from my-account page.
         *
         * @param WP_Error $error
         *
         * @return \WP_Error
         */
        public function mp_seller_registration_errors($error)
        {
            $allowed_roles = apply_filters('mp_register_user_role', array('customer', 'seller'));

            // is the role name allowed or user is trying to manipulate.
            if (isset($_POST['role']) && !in_array($_POST['role'], $allowed_roles)) {
                return new WP_Error('role-error', esc_html__('Cheating, huh?', 'marketplace'));
            }
            $role = $_POST['role'];
            if ($role == 'seller') {
                $first_name = trim($_POST['wk_firstname']);
                if (empty($first_name)) {
                    return new WP_Error('fname-error', esc_html__('Please enter your first name.', 'marketplace'));
                }

                $last_name = trim($_POST['wk_lastname']);
                if (empty($last_name)) {
                    return new WP_Error('lname-error', esc_html__('Please enter your last name.', 'marketplace'));
                }

                $shopname = trim($_POST['shopname']);

                $url_slug = $_POST['shopurl'];

                $user = get_user_by('slug', $url_slug);

                if (empty($shopname)) {
                    return new WP_Error('shopname-error', esc_html__('Please enter your Shop Name.', 'marketplace'));
                }

                $shopurl = trim($_POST['shopurl']);
                if (empty($shopurl)) {
                    return new WP_Error('shopurl-error', esc_html__('Please enter valid Shop URL.', 'marketplace'));
                } elseif (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬]/', $shopurl)) {
                    return new WP_Error('shopurl-error', esc_html__('You can not use Special characters in shop url except HYPHEN(-).', 'marketplace'));
                } elseif (ctype_space($shopurl)) {
                    return new WP_Error('shopurl-error', esc_html__('May be  you are using a white space in shop url.', 'marketplace'));
                } elseif ($user) {
                    return new WP_Error('shopurl-error', esc_html__('This shop URl already EXISTS please try different shop url.', 'marketplace'));
                }

                $phone = trim($_POST['phone']);

                if (empty($phone)) {
                    return new WP_Error('phone-error', esc_html__('Please enter your phone number.', 'marketplace'));
                }

                if (!preg_match('/^[0-9]{1,12}$/', $phone)) {
                    return new WP_Error('phone-error', esc_html__('Please enter valid phone number.', 'marketplace'));
                }
            }

            return $error;
        }

        /**
         * Inject first and last name to WooCommerce for new seller registraion.
         *
         * @param array $data data
         *
         * @return array
         */
        public function marketplace_new_customer_data($data)
        {
            $allowed_roles = array('customer', 'seller');
            $role = (isset($_POST['role']) && in_array($_POST['role'], $allowed_roles)) ? $_POST['role'] : 'customer';
            $data['role'] = $role;

            if ($role == 'seller') {
                $data['wk_firstname'] = strip_tags($_POST['wk_firstname']);
                $data['wk_lastname'] = strip_tags($_POST['wk_lastname']);
                $data['user_nicename'] = sanitize_title($_POST['shopurl']);
                $data['store_name'] = sanitize_title($_POST['shopname']);
                $data['phone'] = intval($_POST['phone']);
                $data['register'] = sanitize_title($_POST['register']);
            }

            return $data;
        }

        public function process_registration($user_id, $data)
        {
            global $wpdb;

            if (isset($data['register'])) {
                if (isset($data['user_login']) && isset($data['wk_firstname']) && isset($data['wk_lastname']) && isset($data['user_login']) && isset($data['user_nicename']) && isset($data['store_name'])) {
                    $user_creds = array();
                    $user_nick = $data['user_login'];
                    $store_url = $data['user_nicename'];
                    $store_name = $data['store_name'];
                    $user_first_name = isset($data['wk_firstname']) ? $data['wk_firstname'] : '';
                    $user_last_name = isset($data['wk_lastname']) ? $data['wk_lastname'] : '';
                    $user_email = $data['user_email'];
                    $seller_vl = $data['role'];
                    $shop_name = isset($data['store_name']) ? $data['store_name'] : '';
                    $sel_phone = isset($data['phone']) ? $data['phone'] : '';
                    $seller_add = isset($_POST['wk_user_address']) ? $_POST['wk_user_address'] : '';

                    /*check for activation*/

                    try {
                        if (email_exists($user_email)) {
                            $user_creds = array('user_nicename' => "$store_url", 'display_name' => "$user_nick");
                            $newuser_id = wp_update_user($user_creds);
                        }
                        unset($_POST);

                        $time = time();
                        update_user_meta($user_id, 'first_name', $user_first_name);
						update_user_meta($user_id, 'last_name', $user_last_name);
						
                        if (isset($seller_vl) && $seller_vl != 'customer') {
                            update_user_meta($user_id, 'shop_name', $shop_name);
                            update_user_meta($user_id, 'shop_address', $store_url);
                            update_user_meta($user_id, 'billing_phone', $sel_phone);
                            update_user_meta($user_id, 'wk_user_address', $seller_add);
                            $result = $this->mp_seller_meta($user_id, 'role', $seller_vl);
                        }

                        $tableName = 'new_seller';
                        do_action('new_seller_registration', $data);

                        throw new Exception('success');
                    } catch (Exception $e) {
                        if ($e->getMessage() == 'success') {
                            wc_add_notice(apply_filters('register_errors', esc_html__('Registration complete check your mail for password!', 'marketplace')), $e->getMessage());
                        } else {
                            wc_add_notice(apply_filters('register_errors', $e->getMessage()), 'error');
                        }
                    }
                }
            }
        }

        //send mail to new registerd user
        public function mp_seller_meta($user, $seller_key, $seller_val)
        {
            global $wpdb;
            $seller_table = $wpdb->prefix.'mpsellerinfo';
            if (get_option('wkmp_auto_approve_seller')) {
                $seller = array('user_id' => $user, 'seller_key' => $seller_key, 'seller_value' => 'seller');
            } else {
                $seller = array('user_id' => $user, 'seller_key' => $seller_key, 'seller_value' => 'customer');
            }
            $seller_res = $wpdb->insert($seller_table, $seller);
            /* defining role as marketplace seller*/
            $user_role = new WP_User($user);
            //$user_role->remove_role(get_option('default_role');
            $user_role->add_role('wk_marketplace_seller');
            if (get_option('wkmp_auto_approve_seller')) {
                $user_role->set_role('wk_marketplace_seller');
            } else {
                $user_role->set_role(get_option('default_role'));
            }

            return $seller_res;
        }
    }
}
