<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Voucher Class
 * 
 * Handles generic voucher functions.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.0.0
 */
class WOO_Vou_Voucher {

    var $model;

    public function __construct() {

        global $woo_vou_model;

        $this->model = $woo_vou_model;
    }

    
    /**
     * Return voucher code status
     * 
     * "purchased"  - voucher code is purchased and still not expired or used
     * "used" 		- voucher code is redeemed
     * "expired"	- voucher code is expired and its not redeemed
     * "invalid"	- voucher code not exist or invalid
     *
     * @package WooCommerce - PDF Vouchers
     * @since 2.6.4
     */
    public function woo_vou_get_voucher_code_status($voucode) {

        global $current_user, $woo_vou_vendor_role;

        $prefix = WOO_VOU_META_PREFIX;
        $vou_code_status = 'invalid';
        $vou_code_args = array();
        $used_code_args = array();

        if (!empty($voucode)) { // Check voucher code is not empty
            //Voucher Code
            $voucode = strtolower($voucode);

            //Get User roles
            $user_roles = isset($current_user->roles) ? $current_user->roles : array();
            $user_role = array_shift($user_roles);

            //voucher admin roles
            $admin_roles = woo_vou_assigned_admin_roles();


            // arguments for get purchase voucher details
            $vou_code_args['fields'] = 'ids';
            $vou_code_args['meta_query'] = array(
                array(
                    'key' => $prefix . 'purchased_codes',
                    'value' => $voucode
                ),
                array(
                    'key' => $prefix . 'used_codes',
                    'compare' => 'NOT EXISTS'
                )
            );

            // get purchsed voucher codes data
            $voucodedata = woo_vou_get_voucher_details($vou_code_args);

            if (!empty($voucodedata) && is_array($voucodedata)) { // Check voucher code ids are not empty				
                // set voucher status to purchased
                $vou_code_status = 'purchased';

                // get voucher code id
                $voucodeid = isset($voucodedata[0]) ? $voucodedata[0] : '';

                // get voucher expired date
                $expiry_date = get_post_meta($voucodeid, $prefix . 'exp_date', true);

                // check voucher is expired or not		
                if (isset($expiry_date) && !empty($expiry_date)) {

                    if ($expiry_date < $this->model->woo_vou_current_date()) {
                        // set voucher status to expired
                        $vou_code_status = 'expired';
                    }
                }
            } else {

                // argunments array for used voucher code
                $used_code_args['fields'] = 'ids';
                $used_code_args['meta_query'] = array(
                    array(
                        'key' => $prefix . 'used_codes',
                        'value' => $voucode
                    )
                );

                // get used voucher code data
                $usedcodedata = woo_vou_get_voucher_details($used_code_args);

                if (!empty($usedcodedata) && is_array($usedcodedata)) {
                    // set voucher status to used
                    $vou_code_status = 'used';
                }
            }

            return $vou_code_status;
        }
    }

    /**
     * Get all users by vouchers
     * 
     * Handles to return all users by vouchers
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.6.4
     */
    public function woo_vou_get_redeem_users_by_voucher($args = array()) {

        $prefix = WOO_VOU_META_PREFIX;

        $args['fields'] = 'id=>parent';

        $voucodesdata = woo_vou_get_voucher_details($args);

        $users_data = array();

        foreach ($voucodesdata as $voucodes) {

            $user_id = get_post_meta($voucodes['ID'], $prefix . 'redeem_by', true);

            if (!key_exists($user_id, $users_data)) {

                $user_detail = get_userdata($user_id);
                if (!empty($user_detail)) {
                    $user_display_name = $user_detail->display_name;
                    $users_data[$user_id] = $user_display_name;
                }
            } elseif( $user_id == '0' ){
                $users_data[$user_id] = esc_html('Guest User','woovoucher');
            }
        }

        return $users_data;
    }

    
    /**
     * Create Duplicate Voucher
     * 
     * Handles to create duplicate voucher
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    public function woo_vou_dupd_create_duplicate_vou($vou_id) {

        // get the vou data
        $vou = get_post($vou_id);

        $prefix = WOO_VOU_META_PREFIX;

        // start process to create a new vou
        $suffix = esc_html__('(Copy)', 'woovoucher');

        // get post table data
        $post_author = $vou->post_author;
        $post_date = current_time('mysql');
        $post_date_gmt = get_gmt_from_date($post_date);
        $post_type = $vou->post_type;
        $post_parent = $vou->post_parent;
        $post_content = str_replace("'", "''", $vou->post_content);
        $post_content_filtered = str_replace("'", "''", $vou->post_content_filtered);
        $post_excerpt = str_replace("'", "''", $vou->post_excerpt);
        $post_title = str_replace("'", "''", $vou->post_title) . ' ' . $suffix;
        $post_name = str_replace("'", "''", $vou->post_name);
        $post_comment_status = str_replace("'", "''", $vou->comment_status);
        $post_ping_status = str_replace("'", "''", $vou->ping_status);

        // get the column keys
        $post_data = array(
            'post_author' => $post_author,
            'post_date' => $post_date,
            'post_date_gmt' => $post_date_gmt,
            'post_content' => $post_content,
            'post_title' => $post_title,
            'post_excerpt' => $post_excerpt,
            'post_status' => 'draft',
            'post_type' => WOO_VOU_POST_TYPE,
            'post_content_filtered' => $post_content_filtered,
            'comment_status' => $post_comment_status,
            'ping_status' => $post_ping_status,
            'post_password' => $vou->post_password,
            'to_ping' => $vou->to_ping,
            'pinged' => $vou->pinged,
            'post_modified' => $post_date,
            'post_modified_gmt' => $post_date_gmt,
            'post_parent' => $post_parent,
            'menu_order' => $vou->menu_order,
            'post_mime_type' => $vou->post_mime_type
        );

        // returns the vou id if we successfully created that vou
        $post_id = wp_insert_post($post_data);

        //update vous meta values
        woo_vou_dupd_post_meta($vou->ID, $post_id);

        // if successfully created vou than redirect to main page
        wp_redirect(add_query_arg(array('post_type' => WOO_VOU_POST_TYPE, 'action' => 'edit', 'post' => $post_id), admin_url('post.php')));

        // to avoid junk
        exit;
    }

    /**
     * Check Enable Voucher
     * 
     * Handles to check enable voucher using product id
     *
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    public function woo_vou_check_enable_voucher($productid, $variation_id = false, $args = array() ) {

        $enable = false;

        if (!empty($productid)) { // Check product id is not empty
            $prefix = WOO_VOU_META_PREFIX;

            //enable voucher
            $enable_vou = get_post_meta($productid, $prefix . 'enable', true);

            // If variation id
            if (!empty($variation_id)) {

                $is_downloadable = get_post_meta($variation_id, '_downloadable', true);
            } else { // is downloadable
                $is_downloadable = get_post_meta($productid, '_downloadable', true);
            }

            // Check enable voucher meta & product is downloadable
            // Check Voucher codes are not empty 
            if ($enable_vou == 'yes' && $is_downloadable == 'yes') { // Check enable voucher meta & product is downloadable
                $enable = true;
            }
        }

        return apply_filters('woo_vou_check_enable_voucher', $enable, $productid, $variation_id, $args);
    }

    /**
     * Check product is expired/upcoming
     * 
     * Handles to check product is expired/upcoming based on start date and end date
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.4.2
     */
    public function woo_vou_check_product_is_expired($product) {

        //Get Prefix
        $prefix = WOO_VOU_META_PREFIX;

        // Get product ID
        $product_id = woo_vou_get_product_id($product);

        $enabled = get_post_meta($product_id, $prefix . 'enable', true); // Check voucher is enable

        $is_downloadable = '';
        
        if( !empty( $product ) ) {
            // If product is variable
            if ($product->is_type('variable') || $product->is_type('variation')) {

                // Declare variable
                $is_downloadable = 'no';

                // Get children products
                $pro_childrens = $product->get_children();

                // Loop on children products
                foreach ($pro_childrens as $pro_children) {

                    $variation_downloadable = get_post_meta($pro_children, '_downloadable', true); // Is variation downloadable
                    // If variation is downloadable
                    if (!empty($variation_downloadable) && $variation_downloadable == 'yes') {
                        $is_downloadable = 'yes';
                        break;
                    }
                }
            } else {

                $is_downloadable = get_post_meta($product_id, '_downloadable', true); // Is product downloadable
            }
        }

        $expired = false;

        if (!empty($enabled) && $enabled == 'yes' && !empty($is_downloadable) && $is_downloadable == 'yes') { // check expiration type is based on purchase
            // get start date
            $product_start_date = get_post_meta($product_id, $prefix . 'product_start_date', true);
            // get end date
            $product_end_date = get_post_meta($product_id, $prefix . 'product_exp_date', true);
            // get today date
            $today_date = date('Y-m-d H:i:s', current_time('timestamp'));

            if (empty($product_start_date) && empty($product_end_date)) {
                $expired = false;
            } elseif (!empty($product_start_date) && $product_start_date > $today_date) {
                $expired = 'upcoming';
            } elseif (!empty($product_end_date) && $product_end_date < $today_date) {
                $expired = 'expired';
            }
        }

        return apply_filters('woo_vou_check_product_is_expired', $expired, $product);
    }

    
    /**
     * Check item is already exist in order
     * 
     * Handles to check the item is already exist in order or not
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.0
     */
    public function woo_vou_generate_pdf_voucher($email = '', $product_id = '', $download_id = '', $order_id = '', $item_id = '') {

        $prefix = WOO_VOU_META_PREFIX;
        
        $vou_codes_key = woo_vou_get_multi_voucher_key($order_id, $product_id, $item_id);

        // if product is variable then product_id will be variation id. So get main product id.
        $product_obj = wc_get_product($product_id);
        $main_product_id = $this->model->woo_vou_get_item_productid_from_product($product_obj);

        // Get mutiple pdf option from order meta
        $multiple_pdf = empty($order_id) ? '' : get_post_meta($order_id, $prefix . 'multiple_pdf', true);
        if (is_array($multiple_pdf)) {
            $multiple_pdf = !empty($multiple_pdf[$main_product_id]) ? $multiple_pdf[$main_product_id] : '';
        }

        $orderdvoucodes = array();

        if (!empty($multiple_pdf)) {

            $orderdvoucodes = woo_vou_get_multi_voucher($order_id, $product_id, $item_id);
        }

        // Check out voucher download key
        if (in_array($download_id, $vou_codes_key) || $download_id == 'woo_vou_pdf_1') {

            //product voucher pdf
            woo_vou_process_product_pdf($product_id, $order_id, $item_id, $orderdvoucodes);
        }
    }

    
    /**
     * Save partially redeem voucher code information
     *
     * @param string $voucode 		- voucher code
     * @param array  $voucodeid 	- voucher code id
     * @param string $redeem_amount - how much amount need to redeem
     *
     * @package WooCommerce - PDF Vouchers
     * @since 2.7.2
     */
    public function woo_vou_save_partialy_redeem_voucher_code($voucodeid, $redeem_amount, $voucode, $redeemed_page) {

        global $current_user;

        $prefix = WOO_VOU_META_PREFIX;

        
        //Get user id
        $user_id = !empty($current_user->ID) ? $current_user->ID : '0';

        // update used code date
        update_post_meta($voucodeid, $prefix . 'redeem_method', 'partial');

        // Insert new patially redeem voucher post to save voucher details
        $partial_redeem_codes_args = array(
            'post_author' => $user_id,
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => WOO_VOU_PARTIAL_REDEEM_POST_TYPE,
            'post_parent' => $voucodeid
        );

        $partial_redeem_post_id = wp_insert_post($partial_redeem_codes_args);
		
        // update redeem amount
        update_post_meta($partial_redeem_post_id, $prefix . 'partial_redeem_amount', $redeem_amount);

        // update redeem by
        update_post_meta($partial_redeem_post_id, $prefix . 'redeem_by', $user_id);

        // update redeemed page
        update_post_meta($partial_redeem_post_id, $prefix . 'redeemed_page', $redeemed_page);

        // get current date
        $today = $this->model->woo_vou_current_date();

        // update used code date
        update_post_meta($partial_redeem_post_id, $prefix . 'used_code_date', $today);

        // get product id from voucher code id.
        $product_id = wp_get_post_parent_id($voucodeid);

        update_post_meta($partial_redeem_post_id, $prefix . 'product_id', $product_id);

        update_post_meta($partial_redeem_post_id, $prefix . 'purchased_codes', $voucode);
		
		// Update count of voucher redeem.
		$vocher_redeem_limit = woo_vou_get_voucher_uses_limit_by_voucher_id($voucodeid);
		
		if( isset($vocher_redeem_limit) && !empty($vocher_redeem_limit) ){
			$voucher_uses_count  = !empty(get_post_meta($voucodeid,$prefix.'voucher_uses_count',true))?get_post_meta($voucodeid,$prefix.'voucher_uses_count',true):0;
			update_post_meta($voucodeid,$prefix.'voucher_uses_count',$voucher_uses_count + 1);
		}
		

        //after partialy voucher code
        do_action('woo_vou_partialy_redeemed_voucher_code', $partial_redeem_post_id, $voucodeid);
    }

    /**
     * Save unlimited redeem voucher code information
     *
     * @param string $voucode 		- voucher code
     * @param array  $voucodeid 	- voucher code id
     * @param string $redeem_amount - how much amount need to redeem
     *
     * @package WooCommerce - PDF Vouchers
     * @since 2.7.2
     */
    public function woo_vou_save_unlimited_redeem_voucher_code($voucodeid, $redeem_amount, $voucode, $redeemed_page) {
		
        global $current_user;

        $prefix = WOO_VOU_META_PREFIX;

        //Get user id
        $user_id = isset($current_user->ID) ? $current_user->ID : '';

        // Insert new patially redeem voucher post to save voucher details
        $unlimit_redeem_codes_args = array(
            'post_author' => $user_id,
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => WOO_VOU_UNLIMITED_REDEEM_POST_TYPE,
            'post_parent' => $voucodeid
        );
	
		$product_id = wp_get_post_parent_id($voucodeid);
        $unlimited_redeem_post_id = wp_insert_post($unlimit_redeem_codes_args);

        // update redeem amount
        update_post_meta($unlimited_redeem_post_id, $prefix . 'partial_redeem_amount', $redeem_amount);

        // update redeem by
        update_post_meta($unlimited_redeem_post_id, $prefix . 'redeem_by', $user_id);

        // update redeemed page
        update_post_meta($unlimited_redeem_post_id, $prefix . 'redeemed_page', $redeemed_page);

        // get current date
        $today = $this->model->woo_vou_current_date();

        // update used code date
        update_post_meta($unlimited_redeem_post_id, $prefix . 'used_code_date', $today);

        update_post_meta($unlimited_redeem_post_id, $prefix . 'product_id', $product_id);

        update_post_meta($unlimited_redeem_post_id, $prefix . 'purchased_codes', $voucode);

		// Update count of voucher redeem.
		$vocher_redeem_limit = woo_vou_get_voucher_uses_limit_by_voucher_id($voucodeid);
		
		if( isset($vocher_redeem_limit) && !empty($vocher_redeem_limit) ){
			$voucher_uses_count  = !empty(get_post_meta($voucodeid,$prefix.'voucher_uses_count',true))?get_post_meta($voucodeid,$prefix.'voucher_uses_count',true):0;
			update_post_meta($voucodeid,$prefix.'voucher_uses_count',$voucher_uses_count + 1);
		}
		
        //after partialy voucher code
        do_action('woo_vou_unlimited_redeemed_voucher_code', $unlimited_redeem_post_id, $voucodeid);
    }

    /**
     * Get total redeemed price for voucher code
     *
     * @param  string $voucodeid 			- voucher code post id
     * @return string $total_redeemed_price - total redeemed price 
     *
     * @package WooCommerce - PDF Vouchers
     * @since 2.7.2
     */
    public function woo_vou_get_total_redeemed_price_for_vouchercode($voucodeid) {

        $prefix = WOO_VOU_META_PREFIX;

        $total_redeemed_price = 0;

        // get all patially redeemed post for voucher code = $voucodeid
        $args = array(
            'post_type' => WOO_VOU_PARTIAL_REDEEM_POST_TYPE,
            'post_parent' => $voucodeid,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => $prefix . 'partial_redeem_amount',
                ),
            ),
        );
        $partially_redeemed_posts = get_posts($args);

        // if found any parially redeemed post, then calculate total redeemed price
        if (!empty($partially_redeemed_posts) && is_array($partially_redeemed_posts)) {

            foreach ($partially_redeemed_posts as $key => $partially_redeemed_post) {

                // get redeemed price
                $price = get_post_meta($partially_redeemed_post->ID, $prefix . 'partial_redeem_amount', true);
                // add redeemed price to total
                $total_redeemed_price += $price;
            }
        }

        // return total redeemed price
        return $total_redeemed_price;
    }

    /**
     * Create new woocommerce coupon code or update meta if coupon code is exits as per voucher code
     * Par: $voucherCode array with code, code amount and exp date, order object to redeem code if used in order
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.9.3
     */
    public function woo_vou_create_wc_coupon_code($voucherCode = array(), $order, $product_id = '') {
		
        // Declare global variables
        global $post_type;

        // Get prefix
        $prefix = WOO_VOU_META_PREFIX;

        // Declare variables
        $usability = 1;

        // If enable then set coupon usability to infinity
        $enable_partial_redeem = woo_vou_check_partial_redeem_by_order($voucherCode['vou_code'], $order);

        // Get option for unlimited redeem
        $allow_unlimited_redeem = get_option('vou_allow_unlimited_redeem_vou_code');

        // If enable then set coupon usability to infinity
        if ($enable_partial_redeem == 'yes' || $allow_unlimited_redeem == 'yes') {

            $usability = 0;
        }
		
		$usability_limit  = woo_vou_get_voucher_uses_limit_by_voucher_id(0,$product_id);
		if(isset($usability_limit) && !empty($usability_limit)){
			$usability = $usability_limit; 
		}
		
        //Get post author
        $post_author = get_post_field('post_author', $product_id);

        // get active coupons
        $args = array(
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'asc',
            'post_type' => 'shop_coupon',
            'post_status' => 'publish',
        );

        $coupons = get_posts($args); // Activate coupons
        // create an array of active couopon code
        $get_coupon_titles = array();

        foreach ($coupons as $coupon) {
            $get_coupon_titles[] = $coupon->post_title;
        }

        $code = $voucherCode['vou_code'];    // coupon code
        $amount = apply_filters('woo_vou_gen_coupon_code_amount',$voucherCode['vou_amount']);   // Amount
        $exp_date = !empty($voucherCode['vou_exp_date']) ? $voucherCode['vou_exp_date'] : '';   // Voucher expiry date
        $start_date = !empty($voucherCode['vou_start_date']) ? $voucherCode['vou_start_date'] : ''; // Voucher Start date
        $rest_days = !empty($voucherCode['vou_rest_days']) ? $voucherCode['vou_rest_days'] : '';  // Voucher restriction days
        // get coupon product ids from product meta
        $coupon_products = get_post_meta($product_id, $prefix . 'coupon_products', true);
        $product_ids = (!empty($coupon_products) && is_array($coupon_products) ) ? implode(',', $coupon_products) : '';

        // get coupon exclude product ids from product meta
        $coupon_exclude_products = get_post_meta($product_id, $prefix . 'coupon_exclude_products', true);
        $exclude_product_ids = (!empty($coupon_exclude_products) && is_array($coupon_exclude_products) ) ? implode(',', $coupon_exclude_products) : '';
		
		
		// get coupon categories ids from product meta
		$coupon_categories = get_post_meta($product_id, $prefix . 'coupon_categories', true);
		
		// get coupon exclude categories ids from product meta
		$coupon_exclude_categories = get_post_meta($product_id, $prefix . 'coupon_exclude_categories', true);

        // get coupon exclude discount on tax option from product meta
        $discount_on_tax_type = get_post_meta($product_id, $prefix . 'discount_on_tax_type', true);		
		
		$coupon_minimum_spend_amount = get_post_meta($product_id,$prefix . 'coupon_minimum_spend_amount', true);
		$coupon_maximum_spend_amount = get_post_meta($product_id,$prefix . 'coupon_maximum_spend_amount', true);
		
        // Create WC coupon code if not exists
        if (!in_array($code, $get_coupon_titles)) {

            $coupon = array(
                'post_title' => $code,
                'post_content' => '',
                'post_status' => 'publish',
                'post_author' => $post_author,
                'post_type' => 'shop_coupon'
            );

            // Generate coupon
            $new_coupon_id = wp_insert_post($coupon);

            // Type: fixed_cart, percent, fixed_product, percent_product
            $discount_type = apply_filters('woo_vou_gen_coupon_code_type', 'fixed_cart', $new_coupon_id, $voucherCode, $order, $product_id);

            if ($new_coupon_id) {

                // Add meta
                update_post_meta($new_coupon_id, 'discount_type', $discount_type); // Add discount type
                update_post_meta($new_coupon_id, 'coupon_amount', $amount); // Add Coupon amount
                update_post_meta($new_coupon_id, 'individual_use', 'no'); // Set usage type
                update_post_meta($new_coupon_id, 'usage_limit', $usability); // Set usage limit
                update_post_meta($new_coupon_id, $prefix . 'start_date', $start_date); // Set start date
                update_post_meta($new_coupon_id, 'expiry_date', $exp_date); // Set expiry date
                update_post_meta($new_coupon_id, $prefix . 'disable_redeem_day', $rest_days); // Set days only on which this can be used
                update_post_meta($new_coupon_id, 'apply_before_tax', 'yes');
                update_post_meta($new_coupon_id, 'free_shipping', 'no');
                update_post_meta($new_coupon_id, $prefix . 'coupon_type', 'voucher_code');
                update_post_meta($new_coupon_id, $prefix . 'order_id', woo_vou_get_order_id($order)); // Insert order id
                update_post_meta($new_coupon_id, 'product_ids', $product_ids); // Add product ids
                update_post_meta($new_coupon_id, 'exclude_product_ids', $exclude_product_ids); // Add exclude product ids
                update_post_meta($new_coupon_id, 'product_categories', $coupon_categories); // Add exclude categories ids
                update_post_meta($new_coupon_id, 'exclude_product_categories', $coupon_exclude_categories); // Add exclude categories ids
                update_post_meta($new_coupon_id, $prefix .'discount_on_tax_type', $discount_on_tax_type);
                update_post_meta($new_coupon_id,'minimum_amount', $coupon_minimum_spend_amount);
                update_post_meta($new_coupon_id,'maximum_amount', $coupon_maximum_spend_amount);

                do_action('woo_vou_gen_coupon_post_meta', $new_coupon_id, $voucherCode, $order, $product_id);

                //reset variables blank					
                $code = "";
                $exp_date = "";
                $amount = "";
            }
        }

        unset($voucherCode); // remove array value
    }

    
    /**
     * Get downloadable vouchers files
     * 
     * Handles to get downloadable vouchers files
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    public function woo_vou_get_vouchers_download_key($order_id = '', $product_id = '', $item_id = '', $item = '') {

        global $post;

        $prefix = WOO_VOU_META_PREFIX;
        $downloadable_files = array();

        //Get mutiple pdf option from order meta
        $multiple_pdf = empty($order_id) ? '' : get_post_meta($order_id, $prefix . 'multiple_pdf', true);
		
		
        if (is_array($multiple_pdf)) {
            $multiple_pdf = !empty($multiple_pdf[$item['product_id']]) ? $multiple_pdf[$item['product_id']] : '';
        }

        // Getting Voucher Delivery
        $woo_vou_all_ordered_data = $this->model->woo_vou_get_all_ordered_data($order_id); // Getting order meta data
		
        $product_data = wc_get_product($product_id); //Getting the product
        $parent_product_id = $this->model->woo_vou_get_item_productid_from_product($product_data); // Get parent id
		
        $vou_voucher_delivery_type = 'email'; // Declare Voucher Delivery
        // If this variation then get it's product id
        if ($product_data->is_type('variation') && (isset($woo_vou_all_ordered_data[$parent_product_id]['voucher_delivery']) && is_array($woo_vou_all_ordered_data[$parent_product_id]['voucher_delivery']) && !empty($woo_vou_all_ordered_data[$parent_product_id]['voucher_delivery'][$product_id]) )) {

            $vou_voucher_delivery_type = $woo_vou_all_ordered_data[$parent_product_id]['voucher_delivery'][$product_id]; // Get voucher delivery type
        } elseif (isset($woo_vou_all_ordered_data[$product_id]['voucher_delivery'])) {

            $vou_voucher_delivery_type = $woo_vou_all_ordered_data[$product_id]['voucher_delivery']; // Get voucher delievery type
        }

        // Get user selected voucher delivery if item is object
        // This will override voucher delivery selected by admin
        if (!empty($item)) {

            $user_selected_delivery_type = $item->get_meta($prefix . 'delivery_method', true);
            if (!empty($user_selected_delivery_type) && is_array($user_selected_delivery_type) && !empty($user_selected_delivery_type['value'])) {

                $vou_voucher_delivery_type = $user_selected_delivery_type['value'];
            }
        }

        // If page parent is woocommerce and post type is shop order
        if (is_user_logged_in() && is_admin() && current_user_can('manage_options') && !empty($post) && $post->post_type == 'shop_order') {

            $vou_voucher_delivery_type = 'email';
        }
		
	
		$recipient_details = $this->model->woo_vou_get_recipient_data($item);
		
        if (!empty($order_id) && ( in_array( $vou_voucher_delivery_type, array( 'email', 'offline' ) ) ) ) {		
		
            if ($multiple_pdf == 'yes') { //If multiple pdf is set
                $vouchercodes = woo_vou_get_multi_voucher_key($order_id, $product_id, $item_id);				
                foreach ($vouchercodes as $codes) {				
					$downloadable_files[$codes] = array(
						'name' => woo_vou_voucher_download_text($product_id),
						'file' => get_permalink($product_id)
					);
                }
            } else {
				// Set our vocher download file in download files
				$downloadable_files['woo_vou_pdf_1'] = array(
					'name' => woo_vou_voucher_download_text($product_id),
					'file' => get_permalink($product_id)
				);
            }
        }
        return apply_filters('woo_vou_get_vouchers_download_key', $downloadable_files, $order_id, $product_id, $item_id);
    }

    /**
     * Check Voucher Code
     * 
     * Handles to check voucher code
     * is valid or invalid via ajax
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.1.0
     */
    public function woo_vou_check_voucher_code() {

        global $current_user, $woo_vou_vendor_role;	

        $prefix = WOO_VOU_META_PREFIX;
        $product_name = '';
        $product_id = '';
        $expiry_Date = '';
        $voucodeid = '';
        $response['expire'] = false;
        $vou_code_args = array();
        $used_code_args = array();

        $current_user_id = $current_user->ID;
        $response['loggedin_guest_user'] = false;
        $order_customer = '';

        // Get "Check Voucher Code for all logged in users" option
        $vou_enable_logged_user_check_voucher_code = get_option('vou_enable_logged_user_check_voucher_code');
        $vou_enable_logged_user_redeem_vou_code = get_option('vou_enable_logged_user_redeem_vou_code');
        // Get "Check Voucher Code for guest users" option
        $vou_enable_guest_user_check_voucher_code = get_option('vou_enable_guest_user_check_voucher_code');
        // Get option whether to allow all vendor to redeem voucher codes
        $vou_enable_vendor_access_all_voucodes = get_option('vou_enable_vendor_access_all_voucodes');
	
        // Get option whether to allow secondary vendor to redeem primary vendor voucher codes
        $vou_allow_secondary_vendor_redeem_primary_voucher = get_option('vou_allow_secondary_vendor_redeem_primary_voucher');

        if ((!is_user_logged_in()) && ($vou_enable_guest_user_check_voucher_code != 'yes')) {

            $response['error'] = apply_filters('woo_vou_voucher_code_guest_user_message', esc_html__('You need to be logged in to your account to see check voucher code.', 'woovoucher'));
            $response['allow_redeem_expired_voucher'] = "no";
            if (isset($_POST['ajax']) && $_POST['ajax'] == true) {  // if request through ajax
                echo json_encode($response);
                exit;
            } else {
                return $response;
            }
        } elseif (!empty($_POST['voucode'])) { // Check voucher code is not empty
            //Voucher Code
			
			
            $voucode = strtolower($_POST['voucode']);
            $voucode = trim($voucode);
            

            // Add compatibility on backend check voucher code page with currency switcher plugin(realmag777)
            if( !empty($_POST['currency']) ) {
                if (class_exists('WOOCS')) {
                      global $WOOCS;                      
                      if( $_POST['currency']  != $WOOCS->current_currency){           
                           $WOOCS->set_currency($currency);  
                      }
                 }
           }
            //Get User roles
            $user_roles = isset($current_user->roles) ? $current_user->roles : array();
            $user_role = array_shift($user_roles);		
			
			
            //voucher admin roles
            $admin_roles = woo_vou_assigned_admin_roles();

            if ($vou_enable_logged_user_check_voucher_code != 'yes') { // check if all logged in user not access
                if (!in_array($user_role, $admin_roles)) {// voucher admin can redeem all codes
                    $vou_code_args['author'] = $current_user->ID;
                    $used_code_args['author'] = $current_user->ID;
                }
            }
			
            // added code to display only the voucher which are assign to secondary voucher on check voucher page
            if ( in_array($user_role, $woo_vou_vendor_role)  && ( $vou_enable_vendor_access_all_voucodes != 'yes' )  ) {
                if (!in_array($user_role, $admin_roles)) {// voucher admin can redeem all codes
					
                    $vou_code_args['author'] = $current_user->ID;
                    $used_code_args['author'] = $current_user->ID;
                }
            }

            // arguments for get purchased an used voucher code detail
            $vou_code_args['fields'] = 'ids';
            $vou_code_args['meta_query'] = array(
                array(
                    'key' => $prefix . 'purchased_codes',
                    'value' => $voucode
                ),
                array(
                    'key' => $prefix . 'used_codes',
                    'compare' => 'NOT EXISTS'
                )
            );

            // this always return array
            $voucodedata = woo_vou_get_voucher_details(apply_filters('woo_vou_get_primary_vendor_purchase_voucode_args', $vou_code_args));

            // argunments array for used voucher code
            $used_code_args['fields'] = 'ids';
            $used_code_args['meta_query'] = array(
                array(
                    'key' => $prefix . 'used_codes',
                    'value' => $voucode
                )
            );

            // for used voucher code
            $usedcodedata = woo_vou_get_voucher_details(apply_filters('woo_vou_get_primary_vendor_used_voucode_args', $used_code_args));

            //Make meta args for secondary vendor
            $secvendor_args = array(
                'key' => $prefix . 'sec_vendor_users',
                'value' => $current_user->ID,
                'compare' => 'LIKE'
            );

            //Argument for second query voucher code
            unset($vou_code_args['author']);
            $vou_code_args['meta_query'][] = $secvendor_args;

            //Combined both result in main voucher code
            $voucodedata2 = woo_vou_get_voucher_details(apply_filters('woo_vou_get_secondary_vendor_purchase_voucode_args', $vou_code_args));
            $voucodedata = array_unique(array_merge($voucodedata, $voucodedata2));

            //Argument for second query voucher code
            unset($used_code_args['author']);
            $used_code_args['meta_query'][] = $secvendor_args;

            //Combined both result in main voucher code
            $usedcodedata2 = woo_vou_get_voucher_details(apply_filters('woo_vou_get_secondary_vendor_used_voucode_args', $used_code_args));
            $usedcodedata = array_unique(array_merge($usedcodedata, $usedcodedata2));

            if (!empty($voucodedata) && is_array($voucodedata)) { // Check voucher code ids are not empty
				
                $voucodeid = isset($voucodedata[0]) ? $voucodedata[0] : '';
				
                if (!empty($voucodeid)) {					
					
					do_action("woo_vou_before_check_voucher_code_restriction",$voucodeid); //Add new hook
                    //get vouchercodes data 
                    $voucher_data = get_post($voucodeid);
                    $order_id = get_post_meta($voucodeid, $prefix . 'order_id', true);		
					
					
                    $order_customer = get_post_meta($order_id, '_customer_user', true);
                    $cart_details = new Wc_Order($order_id);					
                    $order_items = $cart_details->get_items();

                    foreach ($order_items as $item_id => $download_data) {

                        $voucher_codes = wc_get_order_item_meta($item_id, $prefix . 'codes');
                        $voucher_codes = !empty($voucher_codes) ? explode(',', $voucher_codes) : array();
                        $voucher_codes = array_map('trim', $voucher_codes);
                        $voucher_codes = array_map('strtolower', $voucher_codes);

                        if (in_array($voucode, $voucher_codes)) {

                            //get product data
                            $product_name = $download_data['name'];
                            $product_id = $download_data['product_id'];
                        }
                    }
                }
				
				

                //voucher start date
                $start_Date = get_post_meta($voucodeid, $prefix . 'start_date', true);

                //voucher expired date
				
                $expiry_Date = get_post_meta($voucodeid, $prefix . 'exp_date', true);
				
                if (!in_array($user_role, $admin_roles) && !in_array($user_role, $woo_vou_vendor_role) && ($vou_enable_logged_user_check_voucher_code == 'yes') && ( ($vou_enable_logged_user_redeem_vou_code == 'no') || ( ($vou_enable_logged_user_redeem_vou_code == 'yes') && ( $order_customer != $current_user->ID ) ) )
                ) {
                    $response['success'] = apply_filters('woo_vou_voucher_code_valid_message', sprintf(esc_html__("Voucher code is valid and this voucher code has been bought for %s.", 'woovoucher'), $product_name), $product_name, $voucodeid);
                } else {
                    $response['success'] = apply_filters('woo_vou_voucher_code_valid_message', sprintf(esc_html__("Voucher code is valid and this voucher code has been bought for %s. \nIf you would like to redeem voucher code, Please click on the redeem button below:", 'woovoucher'), $product_name), $product_name, $voucodeid);
                    $response = apply_filters('woo_vou_voucher_code_check_response', $response, $voucodeid, '');
                }

                if (!empty($product_id)) {
                    $disable_redeem_days = get_post_meta($voucodeid, $prefix . 'disable_redeem_day', true);
                    if (!empty($disable_redeem_days)) { // check days are selected					
                        $current_day = date('l');

                        if (in_array($current_day, $disable_redeem_days)) { // check current day redeem is enable or not
                            $message = implode(", ", $disable_redeem_days);

                            $response['success'] = apply_filters('woo_vou_voucher_code_disabled_message', sprintf(esc_html__("Sorry, voucher code is not allowed to be used on %s. \n", 'woovoucher'), $message, $product_name));
                            $response['allow_redeem_expired_voucher'] = "no";
                            $response['expire'] = true;
                        }
                    }
                }

                if (isset($start_Date) && !empty($start_Date)) {

                    if ($start_Date > $this->model->woo_vou_current_date()) {

                        $response['before_start_date'] = true;
                        $response['success'] = apply_filters('woo_vou_voucher_code_before_start_message', sprintf(esc_html__("Voucher code cannot be redeemed before %s for %s. \n", 'woovoucher'), $this->model->woo_vou_get_date_format($start_Date, true), $product_name), $product_name, $start_Date, $voucodeid);
                    }
                }

                if (isset($expiry_Date) && !empty($expiry_Date)) {

                    if ($expiry_Date < $this->model->woo_vou_current_date()) {

                        $response['expire'] = true;

                        // check need to allow redeem for expired vouchers
                        $allow_redeem_expired_voucher = get_option('vou_allow_redeem_expired_voucher');
                        if ($allow_redeem_expired_voucher == "yes")
                            $response['allow_redeem_expired_voucher'] = "yes";
                        else
                            $response['allow_redeem_expired_voucher'] = "no";

                        $response['success'] = apply_filters('woo_vou_voucher_code_expired_message', sprintf(esc_html__("Voucher code was expired on %s for %s. \n", 'woovoucher'), $this->model->woo_vou_get_date_format($expiry_Date, true), $product_name), $product_name, $expiry_Date, $voucodeid);
                    }
                }

                $response['product_detail'] = woo_vou_get_product_detail($order_id, $voucode, $voucodeid);
            } else if (!empty($usedcodedata) && is_array($usedcodedata)) { // Check voucher code is used or not
                $voucodeid = isset($usedcodedata[0]) ? $usedcodedata[0] : '';

                if (!empty($voucodeid)) { //if voucher code id is not empty

                    do_action("woo_vou_before_check_voucher_code_restriction",$voucodeid); //Add new hook
                    
                    $voucher_data = get_post($voucodeid);
                    $order_id = get_post_meta($voucodeid, $prefix . 'order_id', true);
                    $cart_details = new Wc_Order($order_id);
										
                    $order_items = $cart_details->get_items();

                    foreach ($order_items as $item_id => $download_data) {

                        $voucher_codes = wc_get_order_item_meta($item_id, $prefix . 'codes');
                        $voucher_codes = !empty($voucher_codes) ? explode(',', $voucher_codes) : array();
                        $voucher_codes = array_map('trim', $voucher_codes);
                        $voucher_codes = array_map('strtolower', $voucher_codes);

                        $check_code = trim($voucode);
                        $check_code = strtolower($check_code);

                        if (in_array($check_code, $voucher_codes)) {

                            //get product data
                            $product_name = $download_data['name'];
                        }
                    }

                    $response['product_detail'] = woo_vou_get_product_detail($order_id, $check_code, $voucodeid);
                }

                // get used code date
                $used_code_date = get_post_meta($voucodeid, $prefix . 'used_code_date', true);
                $response['used'] = apply_filters('woo_vou_voucher_code_used_message', sprintf(esc_html__('Voucher code is invalid, was used on %s for %s.', 'woovoucher'), $this->model->woo_vou_get_date_format($used_code_date, true), $product_name), $product_name, $used_code_date, $voucodeid);

                $response = apply_filters('woo_vou_voucher_code_check_response', $response, $voucodeid, '');


            } else {
                $response['error'] = apply_filters('woo_vou_voucher_code_invalid_message', esc_html__('Voucher code doesn\'t exist.', 'woovoucher'));
            }

            /**
             * Check if all logged in user access
             * Checks if we should give access to logged in User
             * If yes than checks if logged in user is whether admin and vendor
             * If user is vendor than checks whether "Enable Vendor to access all voucher codes" is tick
             * If condition is satisifed than logged-in user is non-admin and either non-vendor or not allowed to access that voucher code
             * If condition is satisfied than it hide redeem button else shows it
             */
                
            if (( ( $vou_enable_logged_user_check_voucher_code == 'yes' ) && ( $vou_enable_logged_user_redeem_vou_code == 'yes' ) && (!in_array($user_role, $admin_roles) ) && (!in_array($user_role, $woo_vou_vendor_role) ) && ( $order_customer != $current_user_id ) )

            ) { // this condtion to allow customers to redeem their own vouchers


                $response['loggedin_guest_user'] = true; // it wont allow to show redeem button
            }

            $response['vendors_access'] = '';
            
            if ( in_array($user_role, $woo_vou_vendor_role)  && ( $vou_enable_vendor_access_all_voucodes == 'yes' || $vou_allow_secondary_vendor_redeem_primary_voucher == 'yes'  )  ) {
                // If allow vendors to access all voucher codes OR allow secondary vendors to redeem voucher codes
                   $response['vendors_access'] = true; 
            }
            elseif ( in_array($user_role, $woo_vou_vendor_role)  && ( empty( $vou_allow_secondary_vendor_redeem_primary_voucher ) || $vou_allow_secondary_vendor_redeem_primary_voucher == 'no') && $voucher_data->post_author != $current_user->ID ) {
                
                // Not allow secondary vendor to redeem voucher codes
                   $response['vendors_access'] = false; 
            }           
            
            if (isset($_POST['ajax']) && $_POST['ajax'] == true) {  // if request through ajax
                echo json_encode($response);
                exit;
            } else {
                return $response;
            }
        }
    }

   
    
    /**
     * Save Voucher Code
     * 
     * Handles to save voucher code via ajax
     *
     * @package WooCommerce - PDF Vouchers
     * @since 1.1.0
     */
    public function woo_vou_save_voucher_code($coupon = '', $order = '') {

        // Get global variables
        global $woo_vou_vendor_role, $current_user,$woo_vou_wc_currency_switch;

        // Declare prefix variable
        $prefix = WOO_VOU_META_PREFIX;

        // Get "Check Voucher Code for all logged in users" option
        $vou_enable_logged_user_check_voucher_code = get_option('vou_enable_logged_user_check_voucher_code');
        $vou_enable_logged_user_redeem_vou_code = get_option('vou_enable_logged_user_redeem_vou_code');

        $tax_inclusive = get_option('woocommerce_prices_include_tax'); // Get Woocommerce option for tax inclusive/exclusive

        $enable_tax_calc = get_option('woocommerce_calc_taxes'); // Get option whether tax are enabled or not

        $decimal_points = get_option('woocommerce_price_num_decimals'); // Get option decimal points number after points
        // Define variable
        $redeemed_page = 'check_voucher_code';

        if (!empty($coupon)) {

            $redeemed_page = 'coupon';
        } else if (!empty($_POST['woo_vou_voucher_code_submit'])) { // if form is submited
            $redeemed_page = 'check_qrcode';
        }
		

        // Add third party plugin to validate before redeem voucher code
        $result = apply_filters('woo_vou_before_save_voucher_code', array('result' => 'sucess'), $_POST);
        if (isset($result['result']) && $result['result'] == 'fail') {
            return $result;
        }

        
        if (!empty($_POST['voucode'])) { // Check voucher code is not empty
            //Voucher Code
            $voucode = $_POST['voucode'];

            // Get partial redeem
            $enable_partial_redeem = woo_vou_check_partial_redeem_by_order($voucode);

            $usage_limit = $usage_count = $redeem_method = '';

            $vou_redeem_method = !empty($coupon) ? esc_html__('Online', 'woovoucher') : esc_html__('Offline', 'woovoucher');

            if ( $enable_partial_redeem == "yes") {

                // Redeem Amount
                $redeem_amount = isset($_POST['vou_partial_redeem_amount']) && !empty($_POST['vou_partial_redeem_amount']) ? $_POST['vou_partial_redeem_amount'] : '';
            } else{
                // full Redeem Amount
                $redeem_amount = isset($_POST['vou_code_total_price']) && !empty($_POST['vou_code_total_price']) ? $_POST['vou_code_total_price'] : '';
            }
			
			
            if (isset($_POST['ajax']) && $_POST['ajax'] == true) {
                
                // total price
                $total_price = isset($_POST['vou_code_total_price']) && !empty($_POST['vou_code_total_price']) ? $_POST['vou_code_total_price'] : '';

                $args['fields'] = 'ids';
                    $args['meta_query'] = array(
                        array(
                            'key' => $prefix . 'purchased_codes',
                            'value' => $voucode
                        ),
                        array(
                            'key' => $prefix . 'used_codes',
                            'compare' => 'NOT EXISTS'
                        )
                    );

                $voucodedata = woo_vou_get_voucher_details($args);

                if( !empty( $voucodedata ) ){
                    // Loop on voucher codes
                    foreach ($voucodedata as $voucodeid) {
                        $vou_code_total_redeemed_price = $this->woo_vou_get_total_redeemed_price_for_vouchercode( $voucodeid );
                        $remaining_redeem_price = $total_price - $vou_code_total_redeemed_price;
                    }
                }

                $redeem_amount = !empty( $redeem_amount ) ? (float) trim($redeem_amount) : '';
                $remaining_redeem_price = !empty( $remaining_redeem_price ) ? (float) trim($remaining_redeem_price) : '';


                 if( $redeem_amount > $remaining_redeem_price  ){

                    $response['fail'] = 'fail';
                    $response['error_message'] = apply_filters('woo_vou_voucher_code_unsufficient_bal_message', sprintf(esc_html__('Voucher code has not sufficient amount to redeem the amount %s', 'woovoucher'), $redeem_amount), $product_name, $used_code_date);
                    ;
                    echo json_encode($response);
                    exit;
                }
            }			
			
            $redeem_amount = $woo_vou_wc_currency_switch->woo_vou_multi_currency_price($redeem_amount);
			
			
            if ($enable_partial_redeem == "yes") {

                if (isset($_POST['ajax']) && $_POST['ajax'] == true) {

                    // redeem Amount
                    $redeem_amount = isset($_POST['vou_partial_redeem_amount']) && !empty($_POST['vou_partial_redeem_amount']) ? $_POST['vou_partial_redeem_amount'] : '';

                    $redeem_amount = $woo_vou_wc_currency_switch->woo_vou_multi_currency_price($redeem_amount);

                    // redeem Method  
                    $redeem_method = isset($_POST['vou_redeem_method']) && !empty($_POST['vou_redeem_method']) ? $_POST['vou_redeem_method'] : '';
                    // total price
                    $total_price = isset($_POST['vou_code_total_price']) && !empty($_POST['vou_code_total_price']) ? $_POST['vou_code_total_price'] : '';
                    // redeemed price
                    $total_redeemed_price = isset($_POST['vou_code_total_redeemed_price']) && !empty($_POST['vou_code_total_redeemed_price']) ? $_POST['vou_code_total_redeemed_price'] : '';

                    
                    // remaining redeem price
                    $remaining_redeem_price = isset($_POST['vou_code_remaining_redeem_price']) && !empty($_POST['vou_code_remaining_redeem_price']) ? $_POST['vou_code_remaining_redeem_price'] : '';
                    
                    // in case if javascript validation fail then this will prevent from redeem wrong amount
                    if ($redeem_method == 'partial' && ( $redeem_amount == '' || $redeem_amount > $remaining_redeem_price )) {
                        return;
                    }
                } else {
					
                    // Voucher code gets redeemed from coupon code
                    if ($order) {

                        // redeem Amount
                        $redeem_amount = $order->get_subtotal();

                        // redeem Method  
                        $redeem_method = 'partial';

                        // remaining redeem price
                        // We are taking this as 0 because we won't get remainning price
                        // from the order page. So we will take it from coupon amount
                        // as can be seen in further code
                        $remaining_redeem_price = 0;
                    } else {

                        // Redeem method
                        $redeem_method = $_POST['vou_redeem_method'];
                        
                        // Redeem Amount
                        $redeem_amount = $_POST['vou_partial_redeem_amount'];

                        $args['fields'] = 'ids';
                        $args['meta_query'] = array(
                            array(
                                'key' => $prefix . 'purchased_codes',
                                'value' => $voucode
                            ),
                            array(
                                'key' => $prefix . 'used_codes',
                                'compare' => 'NOT EXISTS'
                            )
                        );

                        $voucodedata = woo_vou_get_voucher_details($args);
						
						$total_price = isset($_POST['vou_code_total_price']) ? $_POST['vou_code_total_price'] : '';
						
                        if( !empty( $voucodedata ) ){
                            // Loop on voucher codes
                            foreach ($voucodedata as $voucodeid) {
                                $vou_code_total_redeemed_price = $this->woo_vou_get_total_redeemed_price_for_vouchercode( $voucodeid );
                                $remaining_redeem_price = $total_price - $vou_code_total_redeemed_price;
                            }
                        } else{

                            // Remainning Redeem Price
                            $remaining_redeem_price = $_POST['vou_code_remaining_redeem_price'];
                        }

                    }

                    $redeem_amount = $woo_vou_wc_currency_switch->woo_vou_multi_currency_price($redeem_amount);

                }
            }
		

            //Check voucher code
            $args = array();

            //Get User roles
            $user_roles = isset($current_user->roles) ? $current_user->roles : array();

            //Get user id
            $user_id = !empty($current_user->ID) ? $current_user->ID : '';
			$woo_vou_guest_user_allow_redeem_voucher = get_option('woo_vou_guest_user_allow_redeem_voucher');
			if($woo_vou_guest_user_allow_redeem_voucher == 'yes'){
				$user_id = !empty($user_id) ? $user_id : '0';
			}
			
            //Get user role
            $user_role = array_shift($user_roles);

            // Get "Enable Vendor to access all Voucher Codes" option
            $vou_enable_vendor_access_all_voucodes = get_option('vou_enable_vendor_access_all_voucodes');

            //get voucher admin roles
            $admin_roles = woo_vou_assigned_admin_roles();

            // If logged in user is not vendor, admin & Enable check logged in users can Check and Redeem allowed
            if (!in_array($user_role, $admin_roles) && !in_array($user_role, $woo_vou_vendor_role) && ($vou_enable_logged_user_check_voucher_code == 'yes') && ($vou_enable_logged_user_redeem_vou_code == 'yes')
            ) {

                unset($args['author']);
            }

            $args['fields'] = 'ids';
            $args['meta_query'] = array(
                array(
                    'key' => $prefix . 'purchased_codes',
                    'value' => $voucode
                ),
                array(
                    'key' => $prefix . 'used_codes',
                    'compare' => 'NOT EXISTS'
                )
            );

            $voucodedata = woo_vou_get_voucher_details($args);

            //Make meta args for secondary vendor
            $secvendor_args = array(
                'key' => $prefix . 'sec_vendor_users',
                'value' => $user_id,
                'compare' => 'LIKE'
            );

            //Argument for second query voucher code
            unset($args['author']);
            $args['meta_query'][] = $secvendor_args;

            //Combined both result in main voucher code
            $voucodedata2 = woo_vou_get_voucher_details($args);
            $voucodedata = array_unique(array_merge($voucodedata, $voucodedata2));

            // arguments for getting coupon id
            $args = array(
                'fields' => 'ids',
                'name' => strtolower($voucode),
                array(
                    'key' => $prefix . 'coupon_type',
                    'value' => 'voucher_code'
                ),
            );

            // Get Coupon code data
            $coupon_code_data = woo_vou_get_coupon_details($args);
			
			
            if (!empty($coupon_code_data)) {

                foreach ($coupon_code_data as $coupon_code) {

                    // Get coupon_type
                    $coupon_type = get_post_meta($coupon_code, $prefix . 'coupon_type', true);

                    // Get coupon amount
                    $coupon_amount = get_post_meta($coupon_code, 'coupon_amount', true);

                    // Get usage limit for coupon
                    $usage_limit = get_post_meta($coupon_code, 'usage_limit', true);

                    // Get usage count
                    $usage_count = get_post_meta($coupon_code, 'usage_count', true);
                }
            }

            $usage_count = (int) $usage_count;
			
            $voucodedata = apply_filters("woo_vou_voucodedata", $voucodedata);
			
            if (!empty($voucodedata) && is_array($voucodedata)) {

                // Get whether unlimited redeem is enabled for voucher code
                $allow_unlimited_redeem = get_option('vou_allow_unlimited_redeem_vou_code');

                $today = $this->model->woo_vou_current_date(); // Current date

                $user_first_name = apply_filters('woo_vou_modify_redeem_user_fname', '', $voucode);
                $user_last_name = apply_filters('woo_vou_modify_redeem_user_lname', '', $voucode);

                if ( $user_id != '' ) {
					// Check for is voucher is redeem by guest user
					if($user_id == '0'){
						$user_first_name = esc_html__( 'Guest User', 'woovoucher' );
						$user_last_name = '';
					}else{
						$user = get_user_by('ID', $user_id); // User

						$user_first_name = !empty($order) ? $order->get_billing_first_name() : $user->first_name; // Get user first name
						$user_last_name = !empty($order) ? $order->get_billing_last_name() : $user->last_name; // Get user last name
					}                    
                }
				
                $current_currency = '';

                if (class_exists('WOOCS')) {
                    global $WOOCS;

                    $current_currency = $WOOCS->current_currency;
                    
                    $currencies = $WOOCS->get_currencies();
                    $default_currency = '';

                    if (!empty($currencies) AND is_array($currencies)) {

                        foreach ( $currencies as $key => $currency) {
                            if ($currency['is_etalon']) {
                                $default_currency = $key;
                                break;
                            }
                        }
                    }

                    $WOOCS->set_currency($default_currency);
                }

                // Create an array for voucher redeem information
                $vou_redeem_info = array(
                    'voucode' => $voucode,
                    'first_name' => $user_first_name,
                    'last_name' => $user_last_name,
                    'redeem_date' => $today,
                    'redeem_method' => ucfirst($redeem_method),
                    'redeem_amount' => wc_price($redeem_amount),
                    'vou_redeem_method' => $vou_redeem_method
                );

                if (class_exists('WOOCS')) {
                    global $WOOCS;

                    if( !empty( $current_currency ) ){
                        $WOOCS->set_currency($current_currency);
                    }
                }
				
                if (( empty($allow_unlimited_redeem) || $allow_unlimited_redeem == 'no' ) && ( empty($usage_limit) || ( $usage_count + 1 ) <= $usage_limit )) { // Check voucher code ids are not empty
					
                    // If partial redeem is enabled then process parial redeem
					
					
                    if ($enable_partial_redeem == "yes" && !empty($redeem_method) && $redeem_method == 'partial') {
						
                        // Assign discount amount
                        $remaining_redeem_price = $remaining_redeem_price - $redeem_amount;

                        if (!empty($order)) { // If order is not empty
                            $_coupons = $order->get_items('coupon'); // Get coupon items							
							
                            foreach ($_coupons as $item_id => $item) {

                                if ($coupon_type == 'voucher_code' && !empty($coupon_amount)) {
                                    $discount = $item['discount_amount']; // Get coupon discount amount

                                    if (!empty($item['discount_tax']) && $enable_tax_calc === 'yes' && $tax_inclusive === 'yes') {

                                        $discount = $discount + $item['discount_tax'];
                                    }
                                    
                                    $vou_redeem_info['redeem_amount'] = wc_price($discount);
                                }
                            }

                            // If coupon type is 'voucher_code' and coupon_amount is empty
                            if ($coupon_type == 'voucher_code' && !empty($coupon_amount)) {

                                // Assign discount amount
                                $redeem_amount = $discount;
                                $redeem_amount = $woo_vou_wc_currency_switch->woo_vou_multi_currency_price($redeem_amount);
                                $remaining_redeem_price = $coupon_amount - $discount;
                            }
                        }						
						
                        if (!empty($coupon_code_data)) {

                            foreach ($coupon_code_data as $coupon_code) {

                                // We need to modify remainning redeem price, as $remaining_redeem_price
                                // won't be supplied if voucher code gets redeemed from checkout page
                                $remaining_redeem_price = $coupon_amount - $redeem_amount;

                                $remaining_redeem_price = round($remaining_redeem_price, $decimal_points);

                                // Update coupon amount
                                update_post_meta($coupon_code, 'coupon_amount', $remaining_redeem_price);

                                if (empty($coupon)) { // Only update usage_count if voucher code is not redeemed from online store
                                    // Update meta for 'usage_count'
                                    update_post_meta($coupon_code, 'usage_count', $usage_count + 1);
                                }

                                // If redeemable price is empty then update usage_limit to maximum usage_count
                                if ($remaining_redeem_price <= 0) {

                                    // Update usage_limit to maximum number of usages
                                    update_post_meta($coupon_code, 'usage_limit', $usage_count + 1);

                                    // Update meta for '_used_by'
                                    add_post_meta($coupon_code, '_used_by', $user_id);
                                } else if (!empty($usage_limit)) {

                                    // Update usage_limit to maximum number of usages
                                    update_post_meta($coupon_code, 'usage_limit', 0);
                                    $usage_limit = 0;
                                }
                            }
                        }

                        // Loop on voucher codes
                        foreach ($voucodedata as $voucodeid) {

                            if (!empty($order)) { // If order is not empty
                                $_coupons = $order->get_items('coupon'); // Get coupon items

                                foreach ($_coupons as $item_id => $item) {
                                    if ($coupon_type == 'voucher_code' && !empty($coupon_amount)) {
                                        $discount = $item['discount_amount']; // Get coupon discount amount

                                        if (!empty($item['discount_tax']) && $enable_tax_calc === 'yes' && $tax_inclusive === 'yes') {

                                            $discount = $discount + $item['discount_tax'];
                                        }
                                    }
                                }

                                // If coupon type is 'voucher_code' and coupon_amount is empty
                                if ($coupon_type == 'voucher_code' && !empty($coupon_amount)) {

                                    // Assign discount amount
                                    $redeem_amount = $discount;

                                    $redeem_amount = $woo_vou_wc_currency_switch->woo_vou_multi_currency_price($redeem_amount);

                                    $remaining_redeem_price = $coupon_amount - $redeem_amount;
                                }
                            }

                            // Collect information for redeem code
                            $vou_redeem_info['voucodeid'] = $voucodeid;
                            $vou_redeem_info['order_id'] = get_post_meta($voucodeid, $prefix . 'order_id', true);

                            $redeem_amount = round($redeem_amount, $decimal_points);
                            $this->woo_vou_save_partialy_redeem_voucher_code($voucodeid, $redeem_amount, $voucode, $redeemed_page);

                            // Do action to send voucher code redeem email to admin
                            do_action('woo_vou_redeem_email', $vou_redeem_info);

                            if ( $remaining_redeem_price > 0 ) { //after partial redeem voucher code

                                do_action('woo_vou_partial_redeemed_voucher_code', $voucodeid, $vou_redeem_info);
                            }
							
                            if ($remaining_redeem_price == 0 || (!empty($coupon) && !empty($usage_limit) )) { // need to save full redeem data
								
								// update used codes
                                update_post_meta($voucodeid, $prefix . 'used_codes', $voucode);
								
								

                                // update redeem by
                                update_post_meta($voucodeid, $prefix . 'redeem_by', $user_id);

                                // update used code date
                                update_post_meta($voucodeid, $prefix . 'used_code_date', $today);

                                //after redeem voucher code
                                do_action('woo_vou_redeemed_voucher_code', $voucodeid, $vou_redeem_info);
                            }

                            // break is neccessary so if 2 code found then only 1 get marked as completed.
                            break;
                        }
                    } else {
						
                        foreach ($voucodedata as $voucodeid) {

                            if ($redeem_method == 'full') {

                                $this->woo_vou_save_partialy_redeem_voucher_code($voucodeid, $remaining_redeem_price, $voucode, $redeemed_page);
                            }

                            // Collect information for redeem code
                            $vou_redeem_info['voucodeid'] = $voucodeid;
                            $vou_redeem_info['order_id'] = get_post_meta($voucodeid, $prefix . 'order_id', true);
                            // Do action to send voucher code redeem email to admin
                            do_action('woo_vou_redeem_email', $vou_redeem_info);

                            // update used codes
                            update_post_meta($voucodeid, $prefix . 'used_codes', $voucode);
							
                            // update redeem by
                            update_post_meta($voucodeid, $prefix . 'redeem_by', $user_id);

                            // update used code date
                            update_post_meta($voucodeid, $prefix . 'used_code_date', $today);

                            // update redeem method to full
                            update_post_meta($voucodeid, $prefix . 'redeem_method', $redeem_method);

                            // update redeem page meta
                            update_post_meta($voucodeid, $prefix . 'redeemed_page', $redeemed_page);

                            // Collect information for redeem code
                            $vou_redeem_info['voucodeid'] = $voucodeid;
                            $vou_redeem_info['order_id'] = get_post_meta($voucodeid, $prefix . 'order_id', true);
                            // break is neccessary so if 2 code found then only 1 get marked as completed.
                            break;
                        }

                        foreach ($coupon_code_data as $coupon_code) {

                            if (empty($coupon)) { // Only update usage_count if voucher code is not redeemed from online store
                                // Update meta for 'usage_count'
                                update_post_meta($coupon_code, 'usage_count', $usage_count + 1);

                                // Update meta for '_used_by'
                                add_post_meta($coupon_code, '_used_by', $user_id);
                            }

                            // Update coupon amount
                            update_post_meta($coupon_code, 'coupon_amount', 0);

                            if (empty($usage_limit)) { // If $usage_limit is 0 then update it to maximum usage_count
                                // Update usage_limit to maximum number of usages
                                update_post_meta($coupon_code, 'usage_limit', $usage_count + 1);
                            }
                        }
                    }
                } else if (!empty($allow_unlimited_redeem) && $allow_unlimited_redeem == 'yes') { // If allow unlimited redeem is set to true 
				
                    foreach ($voucodedata as $voucodeid) {

                        if (!empty($order)) { // If order is not empty
                            $_coupons = $order->get_items('coupon'); // Get coupon items
								
                            foreach ($_coupons as $item_id => $item) {

                                $coupon_name = $item->get_name();

                                if (!empty($coupon) && $coupon_name == $coupon && $coupon_type == 'voucher_code' && !empty($coupon_amount)) {

                                    $redeem_amount = $item->get_discount(); // Get coupon discount amount	
                                    if (!empty($item['discount_tax']) && $enable_tax_calc === 'yes' && $tax_inclusive === 'yes') {

                                        $redeem_amount = $redeem_amount + $item['discount_tax'];
                                    }

                                    $redeem_amount = round($redeem_amount, $decimal_points);

                                    break;
                                }
                            }
                        }
						
						//check vocher uses limit is over add used meta to vocher.						
						$voucher_used_conut = get_post_meta($voucodeid,$prefix.'voucher_uses_count',true);
						$total_used_conut = woo_vou_get_voucher_uses_limit_by_voucher_id($voucodeid);
						$is_partial_redeem_enable = get_option('vou_enable_partial_redeem');

						if( ( ($voucher_used_conut < $total_used_conut-1) || (empty($total_used_conut)) ) || $is_partial_redeem_enable == 'yes'  ){
							// Save unlimited voucher code option						
							$this->woo_vou_save_unlimited_redeem_voucher_code($voucodeid, $redeem_amount, $voucode, $redeemed_page);
						}
						else{				
						
							if ($redeem_method == 'full') {

                                $this->woo_vou_save_partialy_redeem_voucher_code($voucodeid, $remaining_redeem_price, $voucode, $redeemed_page);
                            }

                            // update used codes
                            update_post_meta($voucodeid, $prefix . 'used_codes', $voucode);					
							
							
                            // update redeem by
                            update_post_meta($voucodeid, $prefix . 'redeem_by', $user_id);

                            // update used code date
                            update_post_meta($voucodeid, $prefix . 'used_code_date', $today);

                            // update redeem method to full
                            update_post_meta($voucodeid, $prefix . 'redeem_method', $redeem_method);

                            // update redeem page meta
                            update_post_meta($voucodeid, $prefix . 'redeemed_page', $redeemed_page);

                            // Collect information for redeem code
                            $vou_redeem_info['voucodeid'] = $voucodeid;
                            $vou_redeem_info['order_id'] = get_post_meta($voucodeid, $prefix . 'order_id', true);
							// break is neccessary so if 2 code found then only 1 get marked as completed.
                            break;
						}
						
						
                        

                        if (!empty($coupon_code_data)) {

                            foreach ($coupon_code_data as $coupon_code) {

                                if (empty($coupon)) { // Only update usage_count if voucher code is not redeemed from online store
                                    // Update meta for 'usage_count'
                                    update_post_meta($coupon_code, 'usage_count', $usage_count + 1);

                                    // Update meta for 'usage_limit' so as to allow usage infinite times
                                    update_post_meta($coupon_code, 'usage_limit', 0);
                                }
                            }
                        }

                        // Collect information for redeem code
                        $vou_redeem_info['voucodeid'] = $voucodeid;
                        $vou_redeem_info['order_id'] = get_post_meta($voucodeid, $prefix . 'order_id', true);

                        // Do action to send voucher code redeem email to admin
                        do_action('woo_vou_redeem_email', $vou_redeem_info);

                        // After redeem voucher code
                        do_action('woo_vou_redeemed_voucher_code', $voucodeid);
                    }
                } else {
					
                    $used_code_args['fields'] = 'ids';
                    $used_code_args['meta_query'] = array(
                        array(
                            'key' => $prefix . 'purchased_codes',
                            'value' => $voucode
                        )
                    );

                    $voucodedata = woo_vou_get_voucher_details($used_code_args);

                    foreach ($voucodedata as $voucodeid) {

                        if (!empty($voucodeid)) { //if voucher code id is not empty
                            $voucher_data = get_post($voucodeid);
                            $order_id = get_post_meta($voucodeid, $prefix . 'order_id', true);
                            $cart_details = new Wc_Order($order_id);
                            $order_items = $cart_details->get_items();

                            foreach ($order_items as $item_id => $download_data) {

                                $voucher_codes = wc_get_order_item_meta($item_id, $prefix . 'codes');
                                $voucher_codes = !empty($voucher_codes) ? explode(',', $voucher_codes) : array();
                                $voucher_codes = array_map('trim', $voucher_codes);
                                $voucher_codes = array_map('strtolower', $voucher_codes);

                                $check_code = trim($voucode);
                                $check_code = strtolower($check_code);

                                if (in_array($check_code, $voucher_codes)) {

                                    //get product data
                                    $product_name = $download_data['name'];
                                }
                            }
                        }

                        // get used code date
                        $used_code_date = get_post_meta($voucodeid, $prefix . 'used_code_date', true);
                    }

                    $response['fail'] = 'fail';
                    $response['error_message'] = apply_filters('woo_vou_voucher_code_used_message', sprintf(esc_html__('Voucher code is invalid, was used on %s for %s.', 'woovoucher'), $this->model->woo_vou_get_date_format($used_code_date, true), $product_name), $product_name, $used_code_date);
                    ;
                    echo json_encode($response);
                    exit;
                }
            }

            if (isset($_POST['ajax']) && $_POST['ajax'] == true) { // if request through ajax
                $response['success'] = 'success';
                echo json_encode($response);
                exit;
            } else {
                return 'success';
            }
        }
    }

    /**
     * AJAX call 
     * 
     * Handles to show details of with ajax
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.8.1
     */
    public function woo_vou_used_voucher_codes_ajax() {

        if (is_user_logged_in()) {
            ob_start();
            //do action to load used voucher codes html via ajax
            do_action('woo_vou_used_voucher_codes');
            echo ob_get_clean();
            exit;
        } else {
            return esc_html__('You have no redeemed Voucher Codes yet.', 'woovoucher');
        }
    }

    /**
     * AJAX call 
     * 
     * Handles to show details for purchased voucher codes with ajax
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.8.1
     */
    public function woo_vou_purchased_voucher_codes_ajax() {

        if (is_user_logged_in()) {
            ob_start();
            //do action to load used voucher codes html via ajax
            do_action('woo_vou_purchased_voucher_codes');
            echo ob_get_clean();
            exit;
        } else {
            return esc_html__('You have no Purchased Voucher Codes yet.', 'woovoucher');
        }
    }

    /**
     * Restore Voucher Code
     * 
     * Handles to restore voucher codes
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.6.2
     */
    public function woo_vou_restore_voucher_codes($order_id, $old_status, $new_status) {

        //Get prefix
        $prefix = WOO_VOU_META_PREFIX;

        if ($new_status == 'cancelled') { //If status cancelled, failed
            woo_vou_restore_order_voucher_codes($order_id);
        }

        if ($new_status == 'refunded') { //If status refunded
            woo_vou_refund_order_voucher_codes($order_id);
        }
    }

    /**
     * Restore Voucher When Resume Order
     * 
     * Handle to restore old deduct voucher
     * when item overwite in meta field
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.4.0
     */
    public function woo_vou_resume_order_voucher_codes($order_id) {

        // Second parameter was added because there when order is resumed it hides voucher details 
        // meta box in order page but it should show to admin.
        woo_vou_restore_order_voucher_codes($order_id, 'resume_order_voucher_codes');
    }

    
    /**
     * Change Voucher Code Expiry Date
     * 
     * Handles to change voucher code expiry date
     * is valid or invalid via ajax
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 3.2.3
     */
    public function woo_vou_change_voucher_expiry_date() {

        global $model;
        $prefix = WOO_VOU_META_PREFIX;

        // Declare variables
        $voucher_id = $_POST['voucher_id'];
        $order_id = get_post_meta($voucher_id, $prefix . 'order_id', true);
        $voucher_parent_id = wp_get_post_parent_id($voucher_id);
        $purchased_codes = get_post_meta($voucher_id, $prefix . 'purchased_codes', true);
        $voucher_expiry_date = $_POST['voucher_expiry_date'];
        $voucher_old_exp_date = get_post_meta($voucher_id, $prefix . 'exp_date', true);
        $voucher_new_exp_date = !empty($voucher_expiry_date) ? date('Y-m-d H:i:s', strtotime($voucher_expiry_date)) : '';

        $meta_order_details = get_post_meta($order_id, $prefix . 'meta_order_details', true);

        $meta_order_details[$voucher_parent_id]['exp_date'] = $voucher_new_exp_date;

        $response['success'] = false;
        if (!empty($order_id) && !empty($voucher_parent_id)) {
            $response['success'] = true;

            update_post_meta($voucher_id, $prefix . 'exp_date', $voucher_new_exp_date);
            update_post_meta($order_id, $prefix . 'exp_date', $voucher_new_exp_date);
            update_post_meta($order_id, $prefix . 'meta_order_details', $meta_order_details);

            // Getting the coucher codes from order id
            $woo_shop_coupon_posts_args = array(
                'post_type' => 'shop_coupon',
                'posts_per_page' => -1,
                'title' => $purchased_codes,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => $prefix . 'order_id',
                        'value' => $order_id,
                        'compare' => '=',
                    ),
                    array(
                        'key' => $prefix . 'coupon_type',
                        'value' => 'voucher_code',
                        'compare' => '=',
                    ),
                ),
            );
            $woo_shop_coupon_posts = get_posts($woo_shop_coupon_posts_args);

            if (!empty($woo_shop_coupon_posts)) {

                foreach ($woo_shop_coupon_posts as $woo_shop_coupon_post_data) {

                    $woo_shop_coupon_id = $woo_shop_coupon_post_data->ID;

                    $woo_shop_coupon_expiry_date = get_post_meta($woo_shop_coupon_id, 'expiry_date', true);
                    update_post_meta($woo_shop_coupon_id, 'expiry_date', $voucher_new_exp_date, $woo_shop_coupon_expiry_date);
                }
            }
        }
        $response['error_msg'] = esc_html__('Sorry, voucher code expiry date not changed. ', 'woovoucher');
        $response['success_msg'] = esc_html__('Voucher code expiry date has changed. ', 'woovoucher');

        echo json_encode($response);
        exit();
    }

    /**
     * AJAX call 
     * 
     * Handles to show details of with ajax
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 3.3.1
     */
    public function woo_vou_unused_voucher_codes_ajax() {

        if (is_user_logged_in()) {

            ob_start();

            //do action to load unused voucher codes html via ajax
            do_action('woo_vou_unused_voucher_codes');

            echo ob_get_clean();
            exit;
        } else {
            return esc_html__('You have no Unused Voucher Codes yet.', 'woovoucher');
        }
    }

    /**
     * AJAX call 
     * 
     * Handles to send/resend gift notification mail
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 3.3.1
     */
    public function woo_vou_resend_gift_notification_email() {

        $prefix = WOO_VOU_META_PREFIX;

        $response['error'] = esc_html__('Please enter valid Email ID', 'woovoucher');

        // get option
        $vou_download_gift_mail = get_option('vou_download_gift_mail');
        $vou_attach_gift_mail = get_option('vou_attach_gift_mail');

        // If email list, order id and product id are not empty
        if (!empty($_POST['email_list']) && !empty($_POST['order_id']) && !empty($_POST['code_id'])) {

            $email_arr = $product_data = '';
            $mail_sent = false;
            $email_arr = explode(',', $_POST['email_list']);
            $order_id = isset($_POST['order_id']) ? $_POST['order_id'] : ''; // Order id
            $cart_details = wc_get_order($_POST['order_id']); // get order details
            $order_items = $cart_details->get_items(); // get order items
            $item_id = (isset($_POST['item_id'])) ? $_POST['item_id'] : ''; // Get item id
            $productqty = wc_get_order_item_meta($item_id, '_qty', true); //get product quantity
            $order_all_data = $this->model->woo_vou_get_all_ordered_data($_POST['order_id']); // Getting the order meta data

            if (!empty($order_items)) { //if item is empty
                foreach ($order_items as $product_item_key => $product_data) {
                    
                    if ($product_item_key == $item_id) {
                        break;
                    }
                }
            }
                   
            if (!empty($email_arr)) {

                if (!$product_data) { //If product deleted
                    $download_file_data = array();
                } else {
                    $download_file_data = $this->model->woo_vou_get_item_downloads_from_order($cart_details, $product_data);
                }

                $links = array();
                $i = 0;
                $attach_key = array();

                foreach ($download_file_data as $key => $download_file) {

                    $check_key = strpos($key, 'woo_vou_pdf_');

                    if (!empty($download_file) && $check_key !== false) {

                        $attach_keys[] = $key;
                        $i++;
                        $download_url = add_query_arg('woo_vou_screen', 'gift', $download_file['download_url']);

                        $links[] = '<small><a href="' . esc_url($download_url) . '">' . sprintf(esc_html__('Download file%s', 'woovoucher'), ( count($download_file_data) > 1 ? ' ' . $i . ': ' : ': ')) . esc_html($download_file['name']) . '</a></small>';
                    }
                }

                if ($vou_download_gift_mail == 'yes') { //If download enable for gift notification
                    $recipient_voucher = '<br/>' . implode('<br/>', $links);
                } else {
                    $recipient_voucher = '';
                }

                $attachments = array();

                if (!empty($vou_attach_gift_mail) && $vou_attach_gift_mail == 'yes') { //If attachment enable for gift notification
                    //Get product/variation ID
                    $product_id = !empty($product_data['variation_id']) ? $product_data['variation_id'] : $product_data['product_id'];
                    $vou_using_type = $order_all_data[$product_data['product_id']]['using_type'];

                    if (!empty($attach_keys)) {//attachments keys not empty
                        foreach ($attach_keys as $attach_key) {

                            // Getting option for pdf name
                            $attach_pdf_file_name = get_option('attach_pdf_name');

                            // Apply filter to allow 3rd party people to change it
                            $date_format = apply_filters('woo_vou_voucher_date_format', 'Y-m-d');

                            // If name is not empty than replace shortcodes
                            // Else add default prefix
                            if (!empty($attach_pdf_file_name)) {

                                $product_title = get_the_title($product_data['product_id']);
                                $product_title = str_replace(" ", "-", $product_title);

                                // Find and replace shortcodes
                                $find = array("{current_date}", "{product_title}");
                                $replace = array(date($date_format), $product_title);
                                $attach_pdf_file_name = str_replace($find, $replace, $attach_pdf_file_name);
                            } else {

                                $attach_pdf_file_name = 'woo-voucher-';
                            }

                            // Add filter for PDF attach name
                            $pdf_file_args = array(
                                'order_id' => $order_id,
                                'product_id' => $product_id,
                                'item_id' => $item_id,
                                'pdf_vou_key' => $attach_key,
                            );
                            $attach_pdf_file_name = apply_filters('woo_vou_attach_pdf_file_name', $attach_pdf_file_name, $pdf_file_args);

                            // Remove forward slash from name
                            $attach_pdf_file_name = str_replace('/', '', $attach_pdf_file_name);

                            //Voucher attachment path
                            $vou_pdf_path = WOO_VOU_UPLOAD_DIR . $attach_pdf_file_name . $order_id . '-' . $product_id . '-' . $product_item_key; // Voucher pdf path
                            // Replacing voucher pdf name with given value
                            $orderdvoucode_key = str_replace('woo_vou_pdf_', '', $orderdvoucode_key);

                            //if user buy more than 1 quantity of voucher
                            if (isset($productqty) && $productqty > 1) {
                                $vou_pdf_path .= '-' . $orderdvoucode_key;
                            }

                            //if voucher using type is more than one time then generate voucher codes
                            if (!empty($vou_using_type)) {

                                // Get vouche code postfix from option
                                $vou_code_postfix = get_option('vou_code_postfix');

                                if (isset($productqty) && !empty($vou_code_postfix)) {
                                    $vou_code_postfix = (int) $vou_code_postfix - ( $productqty - $orderdvoucode_key ) - 1;
                                    $vou_pdf_path .= '-' . $vou_code_postfix;
                                }
                            }
                            // set PDF path with extension
                            $vou_pdf_name = $vou_pdf_path . '.pdf';

                            // If voucher pdf exist in folder
                            if (file_exists($vou_pdf_name)) {

                                // Adding the voucher pdf in attachment array
                                $attachments[] = apply_filters('woo_vou_gift_email_attachments', $vou_pdf_name, $order_id, $product_data);
                            } else { // If voucher pdf doesn't exist then we will generate that
                                // Call function to generate Voucher PDF
                                $attachments = apply_filters('woo_vou_gift_email_attachments', woo_vou_attach_voucher_to_email(array(), 'customer_processing_order', $cart_details, $item_id), $order_id, $product_data);
                            }
                        }
                    }
                }
                
            /*
            * product price shortcode start
            */            
            $product_details = $this->model->woo_vou_get_product_details($order_id);
            $product_price = !empty($product_details[$product_id]['product_formated_price']) ? $product_details[$product_id]['product_formated_price'] : '';
            
                // Looping on email IDs
                foreach ($email_arr as $email) {

                    if (is_email(trim($email))) {

                        //Get All Data for gift notify
                        $gift_data = array(
                            'first_name' => $_POST['first_name'],
                            'last_name' => $_POST['last_name'],
                            'recipient_name' => $_POST['recipient_name'],
                            'recipient_email' => trim($email),
                            'recipient_message' => $_POST['recipient_message'],
                            'voucher_link' => $recipient_voucher,
                            'attachments' => $attachments,
                            'woo_vou_extra_emails' => false,
                            'order_id' => $_POST['order_id'],
                            'product_price' => $product_price
                        );

                        $gift_data = apply_filters('woo_vou_gift_notification_data', $gift_data, $product_data);

                        // Fires when gift notify.
                        do_action('woo_vou_gift_email', $gift_data);

                        $mail_sent = true;
                    }
                }

                // If mail is sent
                if ($mail_sent) {

                    // Add new item meta for recording flag that mail is sent or not
                    $product_data->update_meta_data($prefix . 'recipient_gift_email_send_item', 'yes');
                    // Save updated meta
                    $product_data->save_meta_data();
                    $response['success'] = esc_html__('Mail sent successfully.', 'woovoucher');
                }
            }
        }

        echo json_encode($response);
        exit;
    }

}
