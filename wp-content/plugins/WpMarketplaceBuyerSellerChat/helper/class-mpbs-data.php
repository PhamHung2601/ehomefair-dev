<?php

/**
 * @author Webkul
 * @version 2.1.0
 * This file handles helper data class.
 */

namespace WpMarketplaceBuyerSellerChat\Helper;

if (!defined('ABSPATH')) {
    exit;
}

if (! class_exists('Mpbs_Data')) {
    /**
     *
     */
    class Mpbs_Data implements Util\Data_Interface
    {
        public function __construct()
        {
            $this->current_user = $this->mpbs_get_current_user_id();
        }

        /**
         * Return current user id
         * @return $user_id
         */
        public function mpbs_get_current_user_id()
        {
            return get_current_user_id();
        }
        
        /**
         * Check user is seller by user id
         * @param $user_id
         * @return boolean
         */
        function mpbs_author_is_seller($author_id)
        {
            global $wpdb;
            $seller_info = $wpdb->get_var("SELECT user_id FROM {$wpdb->prefix}mpsellerinfo WHERE user_id = {$author_id} and seller_value='seller'");
            if ($seller_info) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Get product author details
         * @param $product_id
         * @return array
         */
        function mpbs_get_product_seller_details($product_id)
        {
            $author_id = get_post_field('post_author', $product_id);
            $is_seller = true;
            $return_data = array(
                'is_seller' => $is_seller,
                'seller_id' => $author_id
            );
            return $return_data;
        }

        /**
         * Check user is admin by user id
         * @param $user_id
         * @return boolean
         */
        function mpbs_author_is_admin($author_id)
        {
            global $wpdb;
            $admin_info = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}users WHERE ID =%d", $author_id ) );
            
            if ($admin_info) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Get admin chatbox configuration
         * @return $array
         */
        public function mpbs_get_admin_chatbox_config()
        {
            $data = array(
                'sellerChatData'         => array(
                    'sellerId'       => $this->mpbs_get_user_unique_id($this->current_user),
                    'image'          => $this->mpbs_get_user_image_by_id($this->current_user),
                    'chatStatus'     => $this->mpbs_get_user_status_by_id($this->current_user),
                    'sellerName'     => $this->mpbs_get_user_name_by_id($this->current_user)
                ),
                'chatEnabled'        => true
            );
            return $data;
        }

        /**
         * Chat allowed or not
         * @return boolean
         */
        function mpbs_can_chat_enable($seller_id)
        {
            if (is_user_logged_in()) {
                $customer_id = get_current_user_id();
                if ($seller_id !== $customer_id) {
                    return true;
                }
                return false;
            } else {
                return true;
            }
        }

        /**
         * Get chatbox configuration product page
         * @return array $data
         */
        function mpbs_get_chatbox_config($seller_id)
        {
            $data = array(
                'customerData'   => $this->mpbs_get_customer_config($seller_id, $this->current_user),
                'sellerData'     => array(
                    'sellerId'       => $this->mpbs_get_user_unique_id($seller_id),
                    'chatStatus'     => $this->mpbs_get_user_status_by_id($seller_id),
                    'image'          => $this->mpbs_get_user_image_by_id($seller_id)
                )
            );
            return $data;
        }

        /**
         * Get customer config data if chat already started with seller
         * @param $seller_id
         */
        public function mpbs_get_customer_config($seller_id, $customer_id)
        {
            $chat_row = $this->mpbs_check_chat_started_bs($seller_id, $customer_id);

            if ($chat_row && is_user_logged_in()) {
                $data = array(
                  'customerId'         => $this->mpbs_get_user_unique_id($customer_id),
                  'isCustomerLoggedIn' => is_user_logged_in(),
                  'chatStatus'         => $this->mpbs_get_user_status_by_id($customer_id),
                  'name'               => $this->mpbs_get_user_name_by_id($customer_id),
                  'email'              => $this->mpbs_get_user_data($customer_id)->user_email,
                  'src'                => $this->mpbs_get_user_image_by_id($customer_id)
                );
            } else {
                $data = array();
            }

            return $data;
        }

        public function mpbs_get_customer_config_for_chatlist($seller_id, $customer_id)
        {

            $data = array(
              'customerId'         => $this->mpbs_get_user_unique_id($customer_id),
              'isCustomerLoggedIn' => is_user_logged_in(),
              'chatStatus'         => $this->mpbs_get_user_status_by_id($customer_id),
              'name'               => $this->mpbs_get_user_name_by_id($customer_id),
              'email'              => $this->mpbs_get_user_data($customer_id)->user_email,
              'src'                => $this->mpbs_get_user_image_by_id($customer_id)
            );

            return $data;
        }

        /**
         * Check chat started between user and seller
         * @param $buyer_id and $seller_id
         */
        public function mpbs_check_chat_started_bs($seller_id, $buyer_id)
        {
            global $wpdb;

            $query = $wpdb->prepare("SELECT chat_window from {$wpdb->prefix}user_table_meta where seller_id = '%d' and buyer_id = '%d'", $seller_id, $buyer_id);

            $chat_row = $wpdb->get_var($query);

            return intval($chat_row);
        }

        /**
         * Check server running
         * @return bool
         */
        public function mpbs_is_server_running()
        {
            $host = get_option('mpbs_host_name');
            $port = get_option('mpbs_port_number');

            $timeout = 2;

            $handle = curl_init($host.':'.$port);
            curl_setopt_array($handle, [
                CURLOPT_NOBODY          => true,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_TIMEOUT         => $timeout
            ]);
            curl_exec($handle);
            $code = curl_getinfo($handle);
            return ! empty( $code['primary_port'] ) ? true : false;
        }

        /**
         * Return MP seller page name
         * @return $page_name
         */
        public function mpbs_get_seller_page_name()
        {
            global $wpdb;
            $page_name_query = $wpdb->prepare("SELECT post_name FROM $wpdb->posts WHERE post_name ='%s'", get_option('wkmp_seller_page_title'));
            $page_name = $wpdb->get_var($page_name_query);
            return $page_name;
        }

        /**
         * Get seller chatbox configuration
         * @return $array
         */
        public function mpbs_get_seller_chatbox_config()
        {
            $data = array(
                'sellerChatData'         => array(
                    'sellerId'       => $this->mpbs_get_user_unique_id($this->current_user),
                    'image'          => $this->mpbs_get_user_image_by_id($this->current_user),
                    'chatStatus'     => $this->mpbs_get_user_status_by_id($this->current_user),
                    'sellerName'     => $this->mpbs_get_user_name_by_id($this->current_user)
                ),
                'chatEnabled'        => true
            );
            return $data;
        }

        /**
         * Get user data by id
         * @param $user_id
         * @return userdata object
         */
        public function mpbs_get_user_data($user_id)
        {
            return get_userdata($user_id);
        }

        /**
         * Get user status by id
         * @param $user_id
         * @return $status
         */
        public function mpbs_get_user_status_by_id($user_id)
        {
            global $wpdb;
            $query = $wpdb->prepare("SELECT status from {$wpdb->prefix}user_table where user_id = '%d'", $user_id);
            return $wpdb->get_var($query);
        }

        /**
         * Get user image by id
         * @param $user_id
         * @return $image_url
         */
        public function mpbs_get_user_image_by_id($user_id)
        {
            $dir = wp_upload_dir();
            $image = get_user_meta($user_id, 'mpbs_user_image', true);

            if ($image) {
              $image_path = $dir['baseurl'] . $image;
            } else {
               $image_path = '//0.gravatar.com/avatar/6d08c36207db514399f344acd45bf7bd?s=35&amp;d=mm&amp;r=g';
            }
            return $image_path;
        }

        /**
         * Get user name by id
         * @param $user_id
         * @return $name
         */
        public function mpbs_get_user_name_by_id($user_id)
        {
          $first_name = get_user_meta($user_id, 'first_name', true );
          $last_name = get_user_meta($user_id, 'last_name', true );

            return $first_name . ' ' . $last_name;
        }

        /**
         * Initial chat list for seller
         */
        public function mpbs_get_initialised_chat_list()
        {
            global $wpdb;

            $customer_arr = array();
            $user_id = $this->mpbs_get_current_user_id();

            $user = wp_get_current_user();

            $is_admin = false;
            if (in_array("administrator", $user->roles)){
                $is_admin = true;
            }
           
          if($is_admin == true){
          
          $admin_list =  $wpdb->get_results("SELECT u.ID
                            FROM {$wpdb->prefix}users u
                            INNER JOIN {$wpdb->prefix}usermeta m ON m.user_id = u.ID
                            WHERE m.meta_key = 'wp_capabilities'
                            AND m.meta_value LIKE '%administrator%'
                            ORDER BY u.user_registered",ARRAY_A);

         if(is_array($admin_list)){
            $list_admin = [];
            foreach($admin_list as $valueAdmin){
               
                $list_admin[] = $valueAdmin['ID'];
            }

            $admin_str = implode(",",$list_admin);
            $customers = $wpdb->get_results("SELECT buyer_id from {$wpdb->prefix}user_table_meta where seller_id IN ($admin_str)");

                if ($customers) {
                    foreach ($customers as $key => $value) {

                        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}users WHERE ID = %d", $value->buyer_id));
                        if($count == 1){
                            $customer_arr[] = array(
                                'data'  => json_encode($this->mpbs_get_customer_config_for_chatlist($user_id, $value->buyer_id))
                            );
                        }
                    }
                }
            }

          }else{
            
            $query = $wpdb->prepare("SELECT buyer_id from {$wpdb->prefix}user_table_meta where seller_id = '%d'", $user_id);

            $customers = $wpdb->get_results($query);
                if ($customers) {

                    foreach ($customers as $key => $value) {

                        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}users WHERE ID = %d", $value->buyer_id));
                        if($count == 1){
                            $customer_arr[] = array(
                                'data'  => json_encode($this->mpbs_get_customer_config_for_chatlist($user_id, $value->buyer_id))
                            );
                        }
                    }
                }
            }

            return $customer_arr;
        }

        /**
         * Get user unique id
         */
        public function mpbs_get_user_unique_id($user_id)
        {
            $id = get_user_meta($user_id, 'mpbs_user_unique_id', true);
            if ($id) {
                return $id;
            } else {
                return $user_id;
            }
        }

        /**
         * Get user id by unique id
         */
        public function mpbs_get_userid_by_unique_id($unique_id)
        {
            global $wpdb;

            $query = $wpdb->prepare("SELECT user_id from {$wpdb->prefix}usermeta where meta_key = 'mpbs_user_unique_id' and meta_value = '%s'", $unique_id);

            $user_id = $wpdb->get_var($query);

            if ($user_id) {
                return $user_id;
            } else {
                return $unique_id;
            }
        }

        public function mpbs_get_buyer_user_list(){

            $args = array(
                'role'    => 'customer',
                'orderby' => 'user_nicename',
                'order'   => 'ASC'
            );
            $buyer_list =array();
            $users = get_users($args);

            if(count($users)>0){
                foreach($users as $user){
                 $buyer_list[$user->ID] = $user->user_login;
                }
            }

            return $buyer_list;
            
        }

        public function mpbs_get_seller_user_list(){

            $args = array(
                'role'    => 'wk_marketplace_seller',
                'orderby' => 'user_nicename',
                'order'   => 'ASC'
            );
            $seller_list =array();
            $users = get_users($args);
            if(count($users)>0){
                foreach($users as $user){
                 $seller_list[$user->ID] = $user->user_login;
                }
            }

           

            return $seller_list;
            
        }


        public function mpbs_buyer_seller_chat_total_count($post){
        

            global $wpdb;
            $seller_id =  $post['mpbs_seller_id'];
            $buyer_id =  $post['mpbs_buyer_id'];
            $chat_table = $wpdb->prefix.'chat_table';
           
            $query = $wpdb->prepare("SELECT *FROM $chat_table WHERE ((sender_id = '%d' AND receiver_id = '%d') OR (sender_id = '%d' AND receiver_id = '%d'))",$seller_id,$buyer_id,$buyer_id,$seller_id);
            $wpdb->get_results($query);
            return $wpdb->num_rows;

        }

        public function mpbs_buyer_seller_chat_details($post,$offset,$no_of_records_per_page){

             global $wpdb;
             $seller_id =  $post['mpbs_seller_id'];
             $buyer_id =  $post['mpbs_buyer_id'];
             $chat_table = $wpdb->prefix.'chat_table';


             $emoticons = array(
                ':smiley:',
                ':smile:',
                ':wink:',
                ':blush:',
                ':angry:',
                ':laughing:',
                ':smirk:',
                ':disappointed:',
                ':sleeping:',
                ':rage:',
                ':cry:',
                ':yum:',
                ':neutral_face:',
                ':sunglasses:',
                ':astonished:',
                ':stuck_out_tongue_winking_eye:',
                ':confused:',
                ':scream:',
                ':stuck_out_tongue:',
                ':fearful:',
                ':punch:',
                ':ok_hand:',
                ':clap:',
                ':thumbsup:',
                ':thumbsdown:'
             );

             
             

             $emoticons_value = array(
                "<img src='". MPBS_URL ."assets/images/emoji/smiley.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/smile.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/wink.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/blush.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/angry.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/laughing.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/smirk.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/angry.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/sleeping.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/rage.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/cry.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/confused.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/neutral_face.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/sunglasses.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/astonished.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/stuck_out_tongue_winking_eye.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/confused.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/scream.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/stuck_out_tongue.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/fearful.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/punch.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/ok_hand.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/clap.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/thumbsup.png' style='width: 20px;height: 20px; margin-left: 2px'>",
                "<img src='". MPBS_URL ."assets/images/emoji/thumbsdown.png' style='width: 20px;height: 20px; margin-left: 2px'>"
             );


        
             $query = "SELECT *	FROM $chat_table WHERE ((sender_id = $seller_id	AND receiver_id = $buyer_id) OR (sender_id = $buyer_id AND receiver_id = $seller_id )) order by id desc limit %d , %d";

            
            $chat_result = $wpdb->get_results($wpdb->prepare($query,$offset,$no_of_records_per_page));


                    $message_data['message'] =array();
                    foreach ($chat_result as $key => $value) {
                    $user_meta=get_userdata($value->sender_id);
                    $user_name = $user_meta->user_login;
                    
                    $user_roles=$user_meta->roles;
                    $role = '';
                        if ( in_array( 'customer', $user_roles, true ) ) {
                        $role = 'customer';
                        }

                        if ( in_array( 'wk_marketplace_seller', $user_roles, true ) ) {
                        $role = 'wk_marketplace_seller';
                        }

                        
                    $newphrase = str_replace($emoticons, $emoticons_value, $value->message);

                   

                    
                    $value_data['message'] = $newphrase;
                    $value_data['sender_id'] = $value->sender_id;
                    $value_data['sender_name'] = $user_name;
                    $value_data['receiver_id'] = $value->receiver_id;
                    $value_data['role'] = $role;
                    $value_data['datetime'] = date('Y-m-d h:i', $value->time_stamp);
                    $message_data['message'][$key] = $value_data;
                    }

                    

                    $response = $message_data['message'];
                    
                    return $response;
        }


        public function mpbs_get_buyer_list_from_seller($seller_id){

            global $wpdb;
            $chat_table = $wpdb->prefix.'user_table_meta';

            $query = $wpdb->prepare("SELECT buyer_id from {$wpdb->prefix}user_table_meta where seller_id = '%d'", $seller_id);
            $customers = $wpdb->get_results($query);

            $buyer_val = array();
            foreach($customers as $customer){
                $buyer_id = $customer->buyer_id;
                $user_meta=get_userdata($buyer_id);
                $user_name = $user_meta->user_login;
                if(!empty($user_name)){
                $buyer_val[$buyer_id] = $user_name;  
                }
            }

            return $buyer_val;

        }
    }

}
