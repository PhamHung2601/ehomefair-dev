<?php

/**
 * @author Webkul
 * @version 2.0.0
 * This file handles all front end ajax callbacks.
 */

namespace WpMarketplaceBuyerSellerChat\Includes\Front;

use WpMarketplaceBuyerSellerChat\Helper;

if (!defined('ABSPATH')) {
    exit;
}

if (! class_exists('Mpbs_Front_Ajax_Functions')) {
    /**
     *
     */
    class Mpbs_Front_Ajax_Functions implements Util\Ajax_Functions_Interface
    {
        public function __construct()
        {
            global $wpdb;

            $helper = new Helper\Mpbs_Data();

            $this->helper = $helper;

            $this->current_user = $helper->mpbs_get_current_user_id();

            $this->user_table = $wpdb->prefix . 'user_table';

            $this->user_table_meta = $wpdb->prefix . 'user_table_meta';
        }

        /**
         *  Update user status in db
         */
        public function mpbs_update_user_status()
        {
            $response = array();

            if (check_ajax_referer('mpbs-front-ajax-nonce', 'nonce', false)) {
                global $wpdb;

                $user_id = $this->helper->mpbs_get_userid_by_unique_id($_POST['user_id']);

                $status_code = $_POST['status_code'];

                if ($user_id && $status_code) {
                    $wpdb->update(
                        $this->user_table,
                        array(
                            'status'  => $status_code,
                        ),
                        array(
                            'user_id' => $user_id
                        ),
                        array(
                            '%d'
                        ),
                        array(
                            '%d'
                        )
                    );

                    $response = array(
                        'error'   => false,
                        'message' => esc_html__( 'Updated Successfully!', 'mp_buyer_seller_chat' ),
                    );
                } else {
                    $response = array(
                        'error'   => true,
                        'message' => esc_html__( 'Invalid Data!', 'mp_buyer_seller_chat' ),
                    );
                }
            } else {
                $response = array(
                    'error'   => true,
                    'message' => esc_html__( 'Security check failed!', 'mp_buyer_seller_chat' ),
                );
            }
            echo json_encode($response);
            wp_die();
        }

        /**
         *  Check seller available for chat
         */
        public function mpbs_check_user_is_available()
        {
            $response = array();

            if (check_ajax_referer('mpbs-front-ajax-nonce', 'nonce', false)) {
                global $wpdb;

                $user_id = $this->helper->mpbs_get_userid_by_unique_id($_POST['data']['customerId']);

                $name = $_POST['data']['type'] == 'seller' ? __('Seller', 'mp_buyer_seller_chat') : __('User', 'mp_buyer_seller_chat');

                $query = $wpdb->prepare("SELECT status from $this->user_table where user_id = '%d'", $user_id);

                $status = $wpdb->get_var($query);

                if (in_array($status, array(0,1, 2, 3))) {
                    $response = array(
                        'error'     => false,
                        'available' => true,
                        'message'   => sprintf( esc_html__( '%s available!', 'mp_buyer_seller_chat' ), $name ),
                    );
                } else {
                    $response = array(
                        'error'     => true,
                        'available' => false,
                        'message'   => sprintf( esc_html__( '%s is not available!', 'mp_buyer_seller_chat' ), $name ),
                    );
                }

            } else {
                $response = array(
                    'error'     => true,
                    'available' => false,
                    'message'   => esc_html__( 'Security check failed!', 'mp_buyer_seller_chat' ),
                );
            }
            echo json_encode($response);
            wp_die();
        }

        /**
         *  initialize Chat User Meta
         */
        public function mpbs_initialize_chat_user_meta()
        {
            $sql = '';

            $response = array();

            if (check_ajax_referer('mpbs-front-ajax-nonce', 'nonce', false)) {
                global $wpdb;

                $seller_id = $this->helper->mpbs_get_userid_by_unique_id($_POST['data']['receiverData']['sellerId']);

                if ($seller_id && $this->current_user) {
                    $query = $wpdb->prepare("SELECT id from $this->user_table_meta where seller_id = '%d' and buyer_id = '%d'", $seller_id, $this->current_user);

                    $chat_row = $wpdb->get_var($query);

                    if (! $chat_row) {
                        $sql = $wpdb->insert(
                            $this->user_table_meta,
                            array(
                                'seller_id'   => $seller_id,
                                'buyer_id'    => $this->current_user,
                                'chat_window' => 1
                            ),
                            array(
                                '%d',
                                '%d',
                                '%d'
                            )
                        );
                    } else {
                        $sql = $wpdb->update(
                            $this->user_table_meta,
                            array(
                                'chat_window' => 1
                            ),
                            array(
                                'seller_id' => $seller_id,
                                'buyer_id'  => $this->current_user
                            ),
                            array(
                                '%d'
                            ),
                            array(
                                '%d',
                                '%d'
                            )
                        );
                    }

                    if ($sql) {
                        $response = array(
                            'error'     => false,
                            'message'   => esc_html__( 'Initialized Successfully!', 'mp_buyer_seller_chat' ),
                        );
                    }
                } else {
                    $response = array(
                        'error'     => true,
                        'message'   => esc_html__( 'Invalid Data!', 'mp_buyer_seller_chat' ),
                    );
                }
            } else {
                $response = array(
                    'error'     => true,
                    'message'   => esc_html__( 'Security check failed!', 'mp_buyer_seller_chat' ),
                );
            }
            echo json_encode($response);
            wp_die();
        }

        /**
         *  Fetch chat history
         */
        public function mpbs_fetch_chat_history()
        {
            $response = array();

            if (check_ajax_referer('mpbs-front-ajax-nonce', 'nonce', false)) {
                global $wpdb;

                $chat_table = $wpdb->prefix . 'chat_table';

                $load_date = date("Y-m-d H:i:s");

                $data = $_POST['data'];

                $load_time = $data['loadTime'];

                $seller_id = $this->helper->mpbs_get_userid_by_unique_id($data['receiverId']);

                $buyer_id = $this->helper->mpbs_get_userid_by_unique_id($data['customerId']);

                if ($load_time == 1) {
                    $load_date = date('Y-m-d H:i:s', strtotime($load_date . ' -1 day'));
                } elseif ($load_time == 2) {
                    $load_date = date('Y-m-d H:i:s', strtotime($load_date . ' -7 day'));
                } elseif ($load_time == 3) {
                    $load_date = date('Y-m-d H:i:s', strtotime($load_date . ' -30 day'));
                } elseif ($load_time == 4) {
                    $load_date = date('Y-m-d H:i:s', strtotime($load_date . ' -(5*365) day'));
                } else {
                    $load_date = date('Y-m-d H:i:s', strtotime($load_date . ' -12 hour'));
                }

                $load_date = strtotime($load_date);

                $query = $wpdb->prepare("SELECT *	FROM $chat_table WHERE ((sender_id = $seller_id	AND receiver_id = $buyer_id) OR (sender_id = $buyer_id AND receiver_id = $seller_id))	AND time_stamp > '%d'", $load_date);

                $chat_result = $wpdb->get_results($query);


                foreach ($chat_result as $key => $value) {
                    $value_data['message'] = stripslashes($value->message);
                    $value_data['sender_id'] = $this->helper->mpbs_get_user_unique_id($value->sender_id);
                    $value_data['receiver_id'] = $this->helper->mpbs_get_user_unique_id($value->receiver_id);
                    $value_data['datetime'] = date('Y-m-d h:i', $value->time_stamp);
                    $message_data['message'][$key] = $value_data;
                }
                $message_data['error'] = false;
                $response = $message_data;
            } else {
                $response = array(
                    'error'     => true,
                    'message'   => esc_html__( 'Security check failed!', 'mp_buyer_seller_chat' ),
                );
            }
            echo json_encode($response);
            wp_die();
        }

        /**
         * save chat data in db
         */
        public function mpbs_save_chat_data()
        {
            $response = array();

            if (check_ajax_referer('mpbs-front-ajax-nonce', 'nonce', false)) {
                global $wpdb;

                $sender_id = $this->helper->mpbs_get_userid_by_unique_id($_POST['data']['senderId']);

                $receiver_id = $this->helper->mpbs_get_userid_by_unique_id($_POST['data']['receiverId']);

                $datetime = strtotime($_POST['data']['dateTime']);

                $message = htmlentities($_POST['data']['message']);

                $query = $wpdb->insert(
                    $wpdb->prefix . 'chat_table',
                    array(
                        'sender_id'       => $sender_id,
                        'receiver_id'     => $receiver_id,
                        'time_stamp'      => $datetime,
                        'status'          => 1,
                        'message'         => $message,
                        'user_public_ip'  => ''
                    ),
                    array(
                        '%d',
                        '%d',
                        '%d',
                        '%d',
                        '%s',
                        '%s'
                    )
                );
                $response = array(
                    'error'     => false,
                    'message'   => esc_html__( 'Success!', 'mp_buyer_seller_chat' ),
                );
            } else {
                $response = array(
                    'error'     => true,
                    'message'   => esc_html__( 'Security check failed!', 'mp_buyer_seller_chat' ),
                );
            }
            echo json_encode($response);
            wp_die();
        }

        public function mpbs_get_customer_config_in_js()
        {
            $response = array();

            if (check_ajax_referer('mpbs-front-ajax-nonce', 'nonce', false)) {
                $seller_id = $this->helper->mpbs_get_userid_by_unique_id($_POST['seller_id']);
                $response_data = $this->helper->mpbs_get_customer_config($seller_id, $this->current_user);
                $response = array(
                    'error'     => false,
                    'message'   => $response_data
                );
            } else {
                $response = array(
                    'error'     => true,
                    'message'   => esc_html__( 'Security check failed!', 'mp_buyer_seller_chat' ),
                );
            }
            echo json_encode($response);
            wp_die();
        }
    }
}
