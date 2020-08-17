<?php

/**
 * @author Webkul
 * @version 2.0.0
 * This file handles all front end action callbacks.
 */

namespace WpMarketplaceBuyerSellerChat\Includes\Front;

use WpMarketplaceBuyerSellerChat\Helper;
use WpMarketplaceBuyerSellerChat\Templates\Front;

if (!defined('ABSPATH')) {
    exit;
}

if (! class_exists('Mpbs_Front_Function_Handler')) {
    /**
     *
     */
    class Mpbs_Front_Function_Handler implements Util\Functions_Interface
    {
        public function __construct()
        {
            global $wpdb;

            $this->helper = new Helper\Mpbs_Data();

            $this->user_table = $wpdb->prefix . 'user_table';
        }

        /**
         * Initialize function
         */
        public function mpbs_initialize()
        {
            add_action('wp_footer', array($this, 'mpbs_chatbox_template'));

            $this->mpbs_initialize_user_table($this->helper->mpbs_get_current_user_id());
        }

        /**
         * Chatbox template function
         */
        public function mpbs_chatbox_template()
        {
            $user_id = $this->helper->mpbs_get_current_user_id();

            // customer chatbox template
            if (is_product()) {

                $seller_id = '';

                $product_id = get_the_ID();

                $marketplace_product = $this->helper->mpbs_get_product_seller_details($product_id);

                $seller_id = $marketplace_product['is_seller'] ? intval($marketplace_product['seller_id']) : '' ;

                if ($seller_id != '' && $this->helper->mpbs_can_chat_enable($seller_id)) {
                    ?>
                    <script>
                      window.mpbsChatboxConfig = <?php echo json_encode($this->helper->mpbs_get_chatbox_config($seller_id)); ?>
                    </script>

                    <div class="mpbs-chatbox-container open mpbs-chatbox-hidden" id="mpbs-customer-chatbox-<?php echo $this->helper->mpbs_get_user_unique_id($user_id); ?>">
                        <?php
                        require MPBS_FILE . 'templates/front/mpbs-chatbox-topbar.php';
                        require MPBS_FILE . 'templates/front/mpbs-chatbox-reply.php';
                        ?>
                    </div>
                    <?php
                    require MPBS_FILE . 'templates/front/mpbs-setting-box.php';
                }
            }

            // seller chatbox

            $mp_page_name = $this->helper->mpbs_get_seller_page_name();

            if (is_page($mp_page_name) && $this->helper->mpbs_author_is_seller($user_id)) {
                $chatbox = new \WpMarketplaceBuyerSellerChat\Templates\Front\Mpbs_Seller_Chatbox();
                ?>
                <script>
                  window.mpbsSellerChatboxConfig = <?php echo json_encode($this->helper->mpbs_get_seller_chatbox_config()); ?>
                </script>
                <?php
                echo '<div class="mpbs-active-users">';
                    $chatbox->mpbs_seller_chatbox();
                echo '</div>';
                require MPBS_FILE . 'templates/front/mpbs-setting-box.php';
            }
        }

        /**
         * Redirect to same page if invalid credentials with notice
         */
        function mpbs_chat_box_login_failed()
        {
            $referrer = $_SERVER['HTTP_REFERER'];

            $referrer = explode('?', $referrer);

            if (! empty($referrer) && ! strstr($referrer[0], 'wp-login') && ! strstr($referrer[0], 'wp-admin')) {
                wc_add_notice(__('Wrong Email or Password.', 'mp_buyer_seller_chat'), 'error');
                wp_redirect($referrer[0]);
                exit;
            }
        }

        /**
         * Insert user entry in user table if not exists
         * @param $user_id
         */
        public function mpbs_initialize_user_table($user_id)
        {
            global $wpdb;

            $query = $wpdb->prepare("SELECT user_id FROM $this->user_table WHERE user_id = '%d'", $user_id);

            $check_user = $wpdb->get_var($query);

            if ($user_id != $check_user) {
                $wpdb->insert(
                    $this->user_table,
                    array(
                        'user_id' => $user_id,
                        'status'  => 1
                    ),
                    array(
                        '%d',
                        '%d'
                    )
                );
            }
            if (get_user_meta($user_id, 'mpbs_user_unique_id', true) == '') {
                update_user_meta($user_id, 'mpbs_user_unique_id', $this->mpbs_generate_unique_id());
            }
        }

        /**
         * Update user status on login
         *
         * @param $user_login, $user
         */
        public function mpbs_user_online_change_status($user_login, $user)
        {
            global $wpdb;

            $row_exists = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $this->user_table WHERE user_id = '%d'", $user->ID));

            if (! $row_exists) {
                $wpdb->query($wpdb->prepare("INSERT INTO $this->user_table (user_id, status) VALUES (%d, 1)", $user->ID));
            } else {
                $wpdb->query($wpdb->prepare("UPDATE $this->user_table SET status=1 WHERE user_id ='%d'", $user->ID));
            }
        }

        /**
         * Save profile data
         */
        public function mpbs_save_profile_data()
        {
            if (! isset($_POST['mpbs_profile_nonce']) || ! wp_verify_nonce($_POST['mpbs_profile_nonce'], 'mpbs_profile_nonce_action')) {
                wc_add_notice(__('Sorry, your nonce did not verify.', 'mp_buyer_seller_chat'), 'error');
                wp_redirect($_SERVER['HTTP_REFERER']);
                exit;
            } else {
                // process form data
                $error = 0;

                $success = 0;

                $notices = array();

                $user_id = $this->helper->mpbs_get_current_user_id();

                $first_name = $_POST['buyer_first_name'];

                $last_name = $_POST['buyer_last_name'];

                if ($first_name) {
                    if (validate_username($first_name)) {
                        update_user_meta($user_id, 'first_name', $first_name);
                        $success = 1;
                    } else {
                        $notices[] = array(
                            'type'      => 'error',
                            'message'  => __('Invalid first name.', 'mp_buyer_seller_chat')
                        );
                    }
                }
                if ($last_name) {
                    if (validate_username($last_name)) {
                        update_user_meta($user_id, 'last_name', $last_name);
                        $success = 1;
                    } else {
                        $notices[] = array(
                            'type'      => 'error',
                            'message'  => __('Invalid last name.', 'mp_buyer_seller_chat')
                        );
                    }
                }

                if (isset($_FILES['buyer_profile_img']) && ! empty($_FILES['buyer_profile_img']['tmp_name'])) {
                    if (! function_exists('wp_handle_upload')) {
                        require_once(ABSPATH . 'wp-admin/includes/file.php');
                    }

                    $image_types = array('image/jpeg', 'image/png');

                    $uploadedfile = $_FILES['buyer_profile_img'];

                    if (! in_array(mime_content_type($uploadedfile['tmp_name']), $image_types)) {
                        $error = 1;
                        $notices[] = array(
                            'type'      => 'error',
                            'message'  => __('Only png/jpeg image supported.', 'mp_buyer_seller_chat')
                        );
                    }

                    $upload_overrides = array( 'test_form' => false );

                    if (! $error) {
                        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

                        if ($movefile && ! isset($movefile['error'])) {
                            $dir = wp_upload_dir();
                            $image_path = $movefile['url'];
                            $image_path = str_replace($dir['baseurl'], '', $image_path);
                            update_user_meta($user_id, 'mpbs_user_image', $image_path);
                            $notices[] = array(
                                'type'      => 'success',
                                'message'  => __('Image uploaded successfully.', 'mp_buyer_seller_chat')
                            );
                        } else {
                            $notices[] = array(
                                'type'      => 'error',
                                'message'  => $movefile['error']
                            );
                        }
                    }
                }

                if ($success && ! $notices) {
                    $notices[] = array(
                        'type'      => 'success',
                        'message'  => __('Updated Successfully.', 'mp_buyer_seller_chat')
                    );
                }

                if ($notices) {
                    foreach ($notices as $key => $value) {
                        wc_add_notice($value['message'], $value['type']);
                    }
                    wp_redirect($_SERVER['HTTP_REFERER']);
                    exit;
                }
            }
        }

        /**
         * @return string
         */
        public function mpbs_generate_unique_id()
        {
            $pass = array();
            $string = 'abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $string_length = strlen($string) - 1;
            for ($i = 0; $i < 8; $i++) {
                $n = rand(0, $string_length);
                $pass[] = $string[$n];
            }
            return implode($pass);
        }
    }
}
