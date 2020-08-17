<?php

/**
 * @author Webkul
 * @version 2.0.0
 * This file handles data interface.
 */

namespace WpMarketplaceBuyerSellerChat\Helper\Util;

if (!defined('ABSPATH')) {
    exit;
}

interface Data_Interface
{
    /**
     * Get current user id
     */
    public function mpbs_get_current_user_id();

    /**
     * Get Seller details by product id
     */
    public function mpbs_get_product_seller_details($product_id);

    /**
     * Check author is seller or not by user id
     */
    public function mpbs_author_is_seller($author_id);

    /**
     * Check server running
     */
    public function mpbs_is_server_running();

    /**
     * Return MP seller page name
     */
    public function mpbs_get_seller_page_name();

    /**
     * Get admin chatbox configuration
     */
    public function mpbs_get_admin_chatbox_config();

    /**
     * Get chatbox configuration product page
     */
    public function mpbs_get_chatbox_config($seller_id);

    /**
     * Get seller chatbox configuration
     */
    public function mpbs_get_seller_chatbox_config();

    /**
     * Get user data by id
     */
    public function mpbs_get_user_data($user_id);

    /**
     * Get user status by id
     */
    public function mpbs_get_user_status_by_id($user_id);

    /**
     * Get customer config data if chat already started with seller
     * @param $seller_id
     */
    public function mpbs_get_customer_config($seller_id, $customer_id);

    /**
     * Check chat started between user and seller
     * @param $buyer_id and $seller_id
     */
    public function mpbs_check_chat_started_bs($seller_id, $buyer_id);

    /**
     * Get user image by id
     * @param $user_id
     * @return $image_url
     */
    public function mpbs_get_user_image_by_id($user_id);

    /**
     * Get user name by id
     * @param $user_id
     * @return $name
     */
    public function mpbs_get_user_name_by_id($user_id);

    /**
     * Get initial chat list for seller
     */
    public function mpbs_get_initialised_chat_list();

    /**
     * Get user unique id
     */
    public function mpbs_get_user_unique_id($user_id);

    /**
     * Get user id by unique id
     */
    public function mpbs_get_userid_by_unique_id($unique_id);
}
