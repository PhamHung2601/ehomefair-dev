<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;


/**
 * Generate Random Pattern Code
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_get_pattern_string($pattern) {

    $pattern_string = '';
    $pattern_length = strlen(trim($pattern, ' '));

    for ($i = 0; $i < $pattern_length; $i++) {

        $pattern_code = substr($pattern, $i, 1);

        if ($pattern_code == 'l') {
            $pattern_string .= woo_vou_get_random_letter();
        } else if ($pattern_code == 'L') {
            $pattern_string .= woo_vou_get_capital_random_letter();
        } else if (strtolower($pattern_code) == 'd') {
            $pattern_string .= woo_vou_get_random_number();
        }
    }

    return apply_filters('woo_vou_get_pattern_string', $pattern_string, $pattern);
}

/**
 * Generate Random Letter
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_get_random_letter($len = 1) {

    $alphachar = "abcdefghijklmnopqrstuvwxyz";
    $rand_string = substr(str_shuffle($alphachar), 0, $len);

    return apply_filters('woo_vou_get_random_letter', $rand_string, $len);
}

/**
 * Generate Capital Random Letter
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.7
 */
function woo_vou_get_capital_random_letter($len = 1) {

    $alphachar = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $rand_string = substr(str_shuffle($alphachar), 0, $len);

    return apply_filters('woo_vou_get_capital_random_letter', $rand_string, $len);
}

/**
 * Generate Random Number
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */

function woo_vou_get_random_number($len = 1) {

    $alphanum = "0123456789";
    $rand_number = substr(str_shuffle($alphanum), 0, $len);

    return apply_filters('woo_vou_get_random_number', $rand_number, $len);
}

function woo_vou_generate_posts_from_vou_codes($allcodes, $order_id, $vou_code_post_data) {

    $prefix = WOO_VOU_META_PREFIX; //Get prefix

    extract($vou_code_post_data);
    $all_vou_codes = !empty($allcodes) ? explode(', ', $allcodes) : array();

    $order_customer_user = get_post_meta($order_id, '_customer_user', true); //Customer ID

    foreach ($all_vou_codes as $vou_code) {

        $vou_code = trim($vou_code, ',');
        $vou_code = trim($vou_code);

        //Insert voucher details into custom post type with seperate voucher code
        $vou_codes_args = array(
            'post_title' => $order_id,
            'post_content' => '',
            'post_status' => 'pending',
            'post_type' => WOO_VOU_CODE_POST_TYPE,
            'post_parent' => $productid
        );

        if (!empty($vendor_user)) { // Check vendor user is not empty
            $vou_codes_args['post_author'] = $vendor_user;
        }

        $vou_codes_id = wp_insert_post($vou_codes_args);

        if ($vou_codes_id) { // Check voucher codes id is not empty
            // update buyer first name
            update_post_meta($vou_codes_id, $prefix . 'first_name', $userfirstname);
            // update buyer last name
            update_post_meta($vou_codes_id, $prefix . 'last_name', $userlastname);
            // update order id
            update_post_meta($vou_codes_id, $prefix . 'order_id', $order_id);
            // update order date
            update_post_meta($vou_codes_id, $prefix . 'order_date', $order_date);
            // update start date
            update_post_meta($vou_codes_id, $prefix . 'start_date', $start_date);
            // update expires date
            update_post_meta($vou_codes_id, $prefix . 'exp_date', $exp_date);
            // update disable redeem days
            update_post_meta($vou_codes_id, $prefix . 'disable_redeem_day', $disable_redeem_days);
            // update purchased codes
            update_post_meta($vou_codes_id, $prefix . 'purchased_codes', $vou_code);
            // update customer id
            update_post_meta($vou_codes_id, $prefix . 'customer_user', $order_customer_user);
            //update secondary vendors
            $sec_vendors = !empty($sec_vendor_users) ? implode(',', $sec_vendor_users) : '';
            update_post_meta($vou_codes_id, $prefix . 'sec_vendor_users', $sec_vendors);

            $vou_from_variation = get_post_meta($productid, $prefix . 'is_variable_voucher', true);

            if (!empty($vou_from_variation)) {

                // update purchased codes
                update_post_meta($vou_codes_id, $prefix . 'vou_from_variation', $data_id);
            }

            do_action('woo_vou_update_voucher_code_meta', $vou_codes_id, $order_id, $item_id, $productid);
        }
    }
}

/**
 * Update Duplicate Post Metas
 * 
 * Handles to update all old vous meta to 
 * duplicate meta
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_dupd_post_meta($old_id, $new_id) {

    // set prefix for meta fields 
    $prefix = WOO_VOU_META_PREFIX;

    // get all post meta for vou
    $meta_fields = get_post_meta($old_id);

    // take array to store metakeys of old vou
    $meta_keys = array();

    foreach ($meta_fields as $metakey => $matavalues) {
        // meta keys store in a array
        $meta_keys[] = $metakey;
    }

    foreach ($meta_keys as $metakey) {

        // get metavalue from metakey
        $meta_value = get_post_meta($old_id, $metakey, true);

        // update meta values to new duplicate vou meta
        update_post_meta($new_id, $metakey, $meta_value);
    }
}

/**
 * Get Voucher Keys
 * 
 * Handles to get voucher keys
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.1.0
 */
function woo_vou_get_multi_voucher_key($order_id = '', $product_id = '', $item_id = '') {

    $voucher_keys = array();
    $vouchers = woo_vou_get_multi_voucher($order_id, $product_id, $item_id);

    if (!empty($vouchers)) {

        $voucher_keys = array_keys($vouchers);
    }

    return apply_filters('woo_vou_get_multi_voucher_key', $voucher_keys, $order_id, $product_id, $item_id);
}

/**
 * Get Vouchers
 * 
 * Handles to get vouchers
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.1.0
 */
function woo_vou_get_multi_voucher($order_id = '', $product_id = '', $item_id = '') {

    $prefix = WOO_VOU_META_PREFIX;

    //Get voucher codes
    $codes = wc_get_order_item_meta($item_id, $prefix . 'codes');

    $codes = !empty($codes) ? explode(', ', $codes) : array();
    $vouchers = array();

    if (!empty($codes)) {

        $key = 1;
        foreach ($codes as $code) {

            $vouchers['woo_vou_pdf_' . $key] = $code;
            $key++;
        }
    }

    return apply_filters('woo_vou_get_multi_voucher', $vouchers, $order_id, $product_id, $item_id);
}


/**
 * Check to get voucher codes from variations or from product meta
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.6
 */
function woo_vou_get_voucher_code($productid, $variation_id = false) {

    $prefix = WOO_VOU_META_PREFIX;
    $vou_codes = '';

    $productid = apply_filters('woo_vou_before_get_voucher_code', $productid);

    //get voucher codes
    $vou_codes = get_post_meta($productid, $prefix . 'codes', true);

    // If variation id
    if (!empty($variation_id)) {

        $vou_is_var = get_post_meta($productid, $prefix . 'is_variable_voucher', true);

        // if voucher codes set at variation level then get it from there
        if ($vou_is_var) {
            $variation_id = apply_filters('woo_vou_before_get_voucher_code', $variation_id);
            $vou_codes = get_post_meta($variation_id, $prefix . 'codes', true);
        }
    }

    //trim voucher codes
    $vou_codes = trim($vou_codes);

    return apply_filters('woo_vou_get_voucher_code', $vou_codes, $productid, $variation_id);
}


/**
 * Check and Update voucher codes into variations or in product meta
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.6
 */
function woo_vou_update_voucher_code($productid, $variation_id = false, $voucodes = '') {

    $prefix = WOO_VOU_META_PREFIX;
    $woo_vou_var_flag = false;

    // If variation id
    if (!empty($variation_id)) {

        $vou_is_var = get_post_meta($productid, $prefix . 'is_variable_voucher', true);

        // if voucher codes set at variation level and get it from there
        if ($vou_is_var) {
            $woo_vou_var_flag = true;
            $variation_id = apply_filters('woo_vou_before_update_voucher_code', $variation_id);
            update_post_meta($variation_id, $prefix . 'codes', trim(html_entity_decode($voucodes)));

            $product = wc_get_product($productid);
            $variations = $product->get_visible_children();
        }
    }

    // if product is simple or variable but there is no voucher code set on variation level 
    if ($woo_vou_var_flag != true) {
        $productid = apply_filters('woo_vou_before_update_voucher_code', $productid);
        update_post_meta($productid, $prefix . 'codes', trim(html_entity_decode($voucodes)));
    }
}

/**
 * Get coupon details
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.2.4
 */
function woo_vou_get_coupon_details($args) {

    global $woo_vou_model;

    $prefix = WOO_VOU_META_PREFIX;

    $post_status = isset($args['post_status']) ? $args['post_status'] : 'publish';

    $coupargs = array('post_type' => 'shop_coupon', 'post_status' => $post_status);

    $coupargs = wp_parse_args($args, $coupargs);

    //return only id
    if (isset($args['fields']) && !empty($args['fields'])) {
        $coupargs['fields'] = $args['fields'];
    }

    //return based on meta query
    if (isset($args['meta_query']) && !empty($args['meta_query'])) {
        $coupargs['meta_query'] = $args['meta_query'];
    }

    //fire query in to table for retriving data
    $result = new WP_Query($coupargs);

    if (isset($args['getcount']) && $args['getcount'] == '1') {
        $postslist = $result->post_count;
    } else {
        //retrived data is in object format so assign that data to array for listing
        $postslist = $woo_vou_model->woo_vou_object_to_array($result->posts);

        // if get list for voucher list then return data with data and total array
        if (isset($args['woo_vou_list']) && $args['woo_vou_list']) {

            $data_res = array();

            $data_res['data'] = $postslist;

            //To get total count of post using "found_posts" and for users "total_users" parameter
            $data_res['total'] = isset($result->found_posts) ? $result->found_posts : '';

            return $data_res;
        }
    }

    return apply_filters('woo_vou_get_coupon_details', $postslist, $args);
}

/**
 * Get Product Detail From Order ID
 * 
 * Handles to get product detail
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.6.2
 */
function woo_vou_get_product_detail($order_id, $voucode, $voucodeid = '') {

    ob_start();
    require( apply_filters('woo_vou_check_code_product_info', WOO_VOU_ADMIN . '/forms/woo-vou-check-code-product-info.php', $order_id, $voucode, $voucodeid) );
    $html = ob_get_clean();

    return apply_filters('woo_vou_get_product_detail', $html, $order_id, $voucode, $voucodeid);
}


/**
 * Get partially redeem voucher code information
 *
 * @package WooCommerce - PDF Vouchers
 * @since 2.7.2
 */
function woo_vou_get_partially_redeem_details($args = array()) {

    global $woo_vou_model;

    $prefix = WOO_VOU_META_PREFIX;

    $post_status = isset($args['post_status']) ? $args['post_status'] : 'publish';

    $vouargs = array('post_type' => array(WOO_VOU_PARTIAL_REDEEM_POST_TYPE, WOO_VOU_UNLIMITED_REDEEM_POST_TYPE), 'post_status' => $post_status);

    $vouargs = wp_parse_args($args, $vouargs);

    //return only id
    if (isset($args['fields']) && !empty($args['fields'])) {
        $vouargs['fields'] = $args['fields'];
    }

    //return based on post ids
    if (isset($args['post__in']) && !empty($args['post__in'])) {
        $vouargs['post__in'] = $args['post__in'];
    }

    //return based on author
    if (isset($args['author']) && !empty($args['author'])) {
        $vouargs['author'] = $args['author'];
    }

    //return based on meta query
    if (isset($args['meta_query']) && !empty($args['meta_query'])) {
        $vouargs['meta_query'] = $args['meta_query'];
    }

    //show how many per page records
    if (isset($args['posts_per_page']) && !empty($args['posts_per_page'])) {
        $vouargs['posts_per_page'] = $args['posts_per_page'];
    } else {
        $vouargs['posts_per_page'] = '-1';
    }

    //get by post parent records
    if (isset($args['post_parent']) && !empty($args['post_parent'])) {
        $vouargs['post_parent'] = $args['post_parent'];
    }

    //show per page records
    if (isset($args['paged']) && !empty($args['paged'])) {
        $vouargs['paged'] = $args['paged'];
    }

    //get order by records
    $vouargs['order'] = 'DESC';
    $vouargs['orderby'] = 'date';

    //show how many per page records
    if (isset($args['order']) && !empty($args['order'])) {
        $vouargs['order'] = $args['order'];
    }

    //show how many per page records
    if (isset($args['orderby']) && !empty($args['orderby'])) {
        $vouargs['orderby'] = $args['orderby'];
    }

    //fire query in to table for retriving data
    $result = new WP_Query($vouargs);

    if (isset($args['getcount']) && $args['getcount'] == '1') {
        $postslist = $result->post_count;
    } else {
        //retrived data is in object format so assign that data to array for listing
        $postslist = $woo_vou_model->woo_vou_object_to_array($result->posts);

        // if get list for voucher list then return data with data and total array
        if (isset($args['woo_vou_list']) && $args['woo_vou_list']) {

            $data_res = array();

            $data_res['data'] = $postslist;

            //To get total count of post using "found_posts" and for users "total_users" parameter
            $data_res['total'] = isset($result->found_posts) ? $result->found_posts : '';

            return $data_res;
        }
    }

    return apply_filters('woo_vou_get_partially_redeem_details', $postslist, $args);
}

/**
 * Get all voucher details
 * 
 * Handles to return all voucher details
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.1.0
 */
function woo_vou_get_voucher_details($args = array()) {

    global $woo_vou_model;

    $prefix = WOO_VOU_META_PREFIX;

    $post_status = isset($args['post_status']) ? $args['post_status'] : 'publish';

    $vouargs = array('post_type' => WOO_VOU_CODE_POST_TYPE, 'post_status' => $post_status);

    $vouargs = wp_parse_args($args, $vouargs);

    //return only id
    if (isset($args['fields']) && !empty($args['fields'])) {
        $vouargs['fields'] = $args['fields'];
    }

    //return based on post ids
    if (isset($args['post__in']) && !empty($args['post__in'])) {
        $vouargs['post__in'] = $args['post__in'];
    }

    //return based on author
    if (isset($args['author']) && !empty($args['author'])) {
        $vouargs['author'] = $args['author'];
    }

    //return based on meta query
    if (isset($args['meta_query']) && !empty($args['meta_query'])) {
        $vouargs['meta_query'] = $args['meta_query'];
    }

    //show how many per page records
    if (isset($args['posts_per_page']) && !empty($args['posts_per_page'])) {
        $vouargs['posts_per_page'] = $args['posts_per_page'];
    } else {
        $vouargs['posts_per_page'] = '-1';
    }

    //get by post parent records
    if (isset($args['post_parent']) && !empty($args['post_parent'])) {
        $vouargs['post_parent'] = $args['post_parent'];
    }

    //show per page records
    if (isset($args['paged']) && !empty($args['paged'])) {
        $vouargs['paged'] = $args['paged'];
    }

    //get order by records
    $vouargs['order'] = 'DESC';
    $vouargs['orderby'] = 'date';

    //show how many per page records
    if (isset($args['order']) && !empty($args['order'])) {
        $vouargs['order'] = $args['order'];
    }

    //show how many per page records
    if (isset($args['orderby']) && !empty($args['orderby'])) {
        $vouargs['orderby'] = $args['orderby'];
    }

    //fire query in to table for retriving data
    $result = new WP_Query($vouargs);

    if (isset($args['getcount']) && $args['getcount'] == '1') {
        $postslist = $result->post_count;
    } else {
        //retrived data is in object format so assign that data to array for listing
        $postslist = $woo_vou_model->woo_vou_object_to_array($result->posts);

        // if get list for voucher list then return data with data and total array
        if (isset($args['woo_vou_list']) && $args['woo_vou_list']) {

            $data_res = array();

            $data_res['data'] = $postslist;

            //To get total count of post using "found_posts" and for users "total_users" parameter
            $data_res['total'] = isset($result->found_posts) ? $result->found_posts : '';

            return $data_res;
        }
    }

    return apply_filters('woo_vou_get_voucher_details', $postslist, $args);
}

/**
 * Get all products by vouchers
 * 
 * Handles to return all products by vouchers
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.1.0
 */
function woo_vou_get_products_by_voucher($args = array()) {

    global $woo_vou_model;

    $prefix = WOO_VOU_META_PREFIX;

    $args['fields'] = 'id=>parent';

    $voucodesdata = woo_vou_get_voucher_details($args);

    $product_ids = array();
    foreach ($voucodesdata as $voucodes) {

        if (!in_array($voucodes['post_parent'], $product_ids)) {

            $product_ids[] = $voucodes['post_parent'];
        }
    }

    if (!empty($product_ids)) { // Check products ids are not empty
        $vouargs = array('post_type' => WOO_VOU_MAIN_POST_TYPE, 'post_status' => 'publish', 'post__in' => $product_ids);

        //display based on per page
        if (isset($args['posts_per_page']) && !empty($args['posts_per_page'])) {
            $vouargs['posts_per_page'] = $args['posts_per_page'];
        } else {
            $vouargs['posts_per_page'] = '-1';
        }

        //get order by records
        $vouargs['order'] = 'DESC';
        $vouargs['orderby'] = 'date';

        //fire query in to table for retriving data
        $result = new WP_Query($vouargs);

        if (isset($args['getcount']) && $args['getcount'] == '1') {
            $products = $result->post_count;
        } else {
            //retrived data is in object format so assign that data to array for listing
            $products = $woo_vou_model->woo_vou_object_to_array($result->posts);
        }
        return $products;
    } else {
        return array();
    }
}

/**
 * Return voucher code id from voucher code
 *
 * @package WooCommerce - PDF Vouchers
 * @since 3.2.3
 */
function woo_vou_get_voucodeid_from_voucode($voucode) {

    // Get prefix
    $prefix = WOO_VOU_META_PREFIX;

    // Declare variable
    $voucode_id = 0;

    // arguments for get purchase voucher details
    $vou_code_args['fields'] = 'ids';
    $vou_code_args['meta_query'] = array(
        array(
            'key' => $prefix . 'purchased_codes',
            'value' => $voucode
        )
    );

    // get purchsed voucher codes data
    $voucodedata = woo_vou_get_voucher_details($vou_code_args);

    if (!empty($voucodedata)) {

        $voucode_id = $voucodedata[0];
    }

    // Return voucher code
    return $voucode_id;
}


/**
 * Get all vouchers templates
 * 
 * Handles to return all vouchers templates
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_get_vouchers($args = array()) {

    global $woo_vou_model;

    $vouargs = array('post_type' => WOO_VOU_POST_TYPE, 'post_status' => 'publish');

    //return only id
    if (isset($args['fields']) && !empty($args['fields'])) {
        $vouargs['fields'] = $args['fields'];
    }

    //return based on meta query
    if (isset($args['meta_query']) && !empty($args['meta_query'])) {
        $vouargs['meta_query'] = $args['meta_query'];
    }

    //show how many per page records
    if (isset($args['posts_per_page']) && !empty($args['posts_per_page'])) {
        $vouargs['posts_per_page'] = $args['posts_per_page'];
    } else {
        $vouargs['posts_per_page'] = '-1';
    }

    //get by post parent records
    if (isset($args['post_parent']) && !empty($args['post_parent'])) {
        $vouargs['post_parent'] = $args['post_parent'];
    }

    //show per page records
    if (isset($args['paged']) && !empty($args['paged'])) {
        $vouargs['paged'] = $args['paged'];
    }

    //get order by records
    $vouargs['order'] = 'DESC';
    $vouargs['orderby'] = 'date';

    //Filter args
    $vouargs = apply_filters('woo_vou_get_vouchers_args', $vouargs);

    //fire query in to table for retriving data
    $result = new WP_Query($vouargs);

    if (isset($args['getcount']) && $args['getcount'] == '1') {
        $postslist = $result->post_count;
    } else {
        //retrived data is in object format so assign that data to array for listing
        $postslist = $woo_vou_model->woo_vou_object_to_array($result->posts);
    }

    return $postslist;
}


/**
 * Get Voucher Code Expiry Date
 * 
 * Handles to getting voucher code expiry date
 * is valid or invalid via ajax
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.2.3
 */
function woo_vou_get_voucher_expiry_date() {

    $prefix = WOO_VOU_META_PREFIX;

    // Declare variables
    $voucher_id = $_POST['voucher_id'];
    $purchased_codes = get_post_meta($voucher_id, $prefix . 'purchased_codes', true);
    $voucher_exp_date = get_post_meta($voucher_id, $prefix . 'exp_date', true);
    $voucher_start_date = get_post_meta($voucher_id, $prefix . 'start_date', true);

    // Set response data
    $response['success'] = (!empty($purchased_codes) || !empty($voucher_id) ) ? true : false;
    $response['voucher_id'] = (!empty($voucher_id) ) ? $voucher_id : '';
    $response['purchased_codes'] = (!empty($purchased_codes) ) ? $purchased_codes : '';
    $response['start_date'] = (!empty($voucher_start_date) ) ? date('Y-m-d h:i a', strtotime($voucher_start_date)) : date('Y-m-d'); //.' 00:00';
    $response['exp_date'] = (!empty($voucher_exp_date) ) ? date('Y-m-d h:i a', strtotime($voucher_exp_date)) : '';

    echo json_encode($response);
    exit();
}

/**
 * Get used codes by product id
 * 
 * Handles to get used codes by product id
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.1.0
 */
function woo_vou_get_used_codes_by_product_id($product_id, $posts_per_page = -1, $paged = 1) {

    //Check product id is empty
    if (empty($product_id))
        return array();

    global $current_user, $woo_vou_vendor_role, $woo_vou_model;

    $prefix = WOO_VOU_META_PREFIX;

    $args = array('post_parent' => $product_id, 'fields' => 'ids', 'posts_per_page' => $posts_per_page, 'paged' => $paged);
    $args['meta_query'] = array(
        array(
            'key' => $prefix . 'used_codes',
            'value' => '',
            'compare' => '!='
        )
    );

    //Get User roles
    $user_roles = isset($current_user->roles) ? $current_user->roles : array();
    $user_role = array_shift($user_roles);

    if (in_array($user_role, $woo_vou_vendor_role)) { // Check vendor user role
        $args['author'] = $current_user->ID;
    }

    //add filter to group by order id
    add_filter('posts_groupby', array($woo_vou_model, 'woo_vou_groupby_order_id'));

    $voucodesdata = woo_vou_get_voucher_details($args);

    //remove filter to group by order id
    remove_filter('posts_groupby', array($woo_vou_model, 'woo_vou_groupby_order_id'));

    $vou_code_details = array();
    if (!empty($voucodesdata) && is_array($voucodesdata)) {

        foreach ($voucodesdata as $vou_codes_id) {

            // get order id
            $order_id = get_post_meta($vou_codes_id, $prefix . 'order_id', true);

            // get order date
            $order_date = get_post_meta($vou_codes_id, $prefix . 'order_date', true);

            //buyer's first name who has purchased voucher code
            $first_name = get_post_meta($vou_codes_id, $prefix . 'first_name', true);

            //buyer's last name who has purchased voucher code
            $last_name = get_post_meta($vou_codes_id, $prefix . 'last_name', true);

            //buyer's name who has purchased voucher code               
            $buyer_name = $first_name . ' ' . $last_name;

            $args = array('post_parent' => $product_id, 'fields' => 'ids');
            $args['meta_query'] = array(
                array(
                    'key' => $prefix . 'used_codes',
                    'value' => '',
                    'compare' => '!='
                ),
                array(
                    'key' => $prefix . 'order_id',
                    'value' => $order_id
                )
            );
            $vouorderdata = woo_vou_get_voucher_details($args);

            $used_codes = $redeem_by = array();
            if (!empty($vouorderdata) && is_array($vouorderdata)) {

                foreach ($vouorderdata as $order_vou_id) {

                    // get purchased codes
                    $used_codes[] = get_post_meta($order_vou_id, $prefix . 'used_codes', true);
                    $redeem_by[] = get_post_meta($order_vou_id, $prefix . 'redeem_by', true);
                }
            }

            // Check purchased codes are not empty
            if (!empty($used_codes)) {

                $vou_code_details[] = array(
                    'order_id' => $order_id,
                    'order_date' => $order_date,
                    'voucode_id' => $vou_codes_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'buyer_name' => $buyer_name,
                    'vou_codes' => implode(',', $used_codes),
                    'redeem_by' => implode(',', $redeem_by)
                );
            }
        }
    }

    return apply_filters('woo_vou_get_used_codes_by_product_id', $vou_code_details, $product_id);
}

/**
 * Get purchased codes by product id
 * 
 * Handles to get purchased codes by product id
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.1.0
 */
function woo_vou_get_purchased_codes_by_product_id($product_id, $posts_per_page = -1, $paged = 1) {

    global $woo_vou_vendor_role, $woo_vou_model, $current_user;

    //Check product id is empty
    if (empty($product_id))
        return array();

    $prefix = WOO_VOU_META_PREFIX;

    $args = array(
        'post_parent' => $product_id,
        'fields' => 'ids',
        'posts_per_page' => $posts_per_page,
        'paged' => $paged
    );
    $args['meta_query'] = array(
        array(
            'key' => $prefix . 'purchased_codes',
            'value' => '',
            'compare' => '!='
        ),
        array(
            'key' => $prefix . 'used_codes',
            'compare' => 'NOT EXISTS'
        )
    );

    //Get User roles
    $user_roles = isset($current_user->roles) ? $current_user->roles : array();
    $user_role = array_shift($user_roles);

    if (in_array($user_role, $woo_vou_vendor_role)) { // Check vendor user role
        $args['author'] = $current_user->ID;
    }

    //add filter to group by order id
    add_filter('posts_groupby', array($woo_vou_model, 'woo_vou_groupby_order_id'));

    $voucodesdata = woo_vou_get_voucher_details($args);

    //remove filter to group by order id
    remove_filter('posts_groupby', array($woo_vou_model, 'woo_vou_groupby_order_id'));

    $vou_code_details = array();
    if (!empty($voucodesdata) && is_array($voucodesdata)) {

        foreach ($voucodesdata as $vou_codes_id) {

            // get order id
            $order_id = get_post_meta($vou_codes_id, $prefix . 'order_id', true);

            // get order date
            $order_date = get_post_meta($vou_codes_id, $prefix . 'order_date', true);

            //buyer's first name who has purchased voucher code
            $first_name = get_post_meta($vou_codes_id, $prefix . 'first_name', true);

            //buyer's last name who has purchased voucher code
            $last_name = get_post_meta($vou_codes_id, $prefix . 'last_name', true);

            //buyer's name who has purchased voucher code
            $buyer_name = $first_name . ' ' . $last_name;

            $args = array('post_parent' => $product_id, 'fields' => 'ids');
            $args['meta_query'] = array(
                array(
                    'key' => $prefix . 'purchased_codes',
                    'value' => '',
                    'compare' => '!='
                ),
                array(
                    'key' => $prefix . 'order_id',
                    'value' => $order_id
                ),
                array(
                    'key' => $prefix . 'used_codes',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'relation' => 'OR',
                    array(
                        'key' => $prefix . 'exp_date',
                        'compare' => '>=',
                        'value' => $woo_vou_model->woo_vou_current_date()
                    ),
                    array(
                        'key' => $prefix . 'exp_date',
                        'value' => '',
                        'compare' => '='
                    )
                )
            );
            $vouorderdata = woo_vou_get_voucher_details($args);

            $purchased_codes = array();
            if (!empty($vouorderdata) && is_array($vouorderdata)) {

                foreach ($vouorderdata as $order_vou_id) {

                    // get purchased codes
                    $purchased_codes[] = get_post_meta($order_vou_id, $prefix . 'purchased_codes', true);
                }
            }

            // Check purchased codes are not empty
            if (!empty($purchased_codes)) {

                $vou_code_details[] = array(
                    'order_id' => $order_id,
                    'order_date' => $order_date,
                    'voucode_id' => $vou_codes_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'buyer_name' => $buyer_name,
                    'vou_codes' => implode(', ', $purchased_codes)
                );
            }
        }
    }

    return apply_filters('woo_vou_get_purchased_codes_by_product_id', $vou_code_details, $product_id);
}


/**
 * Get unused codes by product id
 * 
 * Handles to get unused codes by product id
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.5.3
 */
function woo_vou_get_unused_codes_by_product_id($product_id, $posts_per_page = -1, $paged = 1) {

    //Check product id is empty
    if (empty($product_id)) {

        return array();
    }

    global $current_user, $woo_vou_vendor_role, $woo_vou_model;

    $prefix = WOO_VOU_META_PREFIX;

    $args = array(
        'post_parent' => $product_id,
        'fields' => 'ids',
        'posts_per_page' => $posts_per_page,
        'paged' => $paged
    );

    $args['meta_query'] = array(
        array(
            'key' => $prefix . 'purchased_codes',
            'value' => '',
            'compare' => '!='
        ),
        array(
            'key' => $prefix . 'used_codes',
            'compare' => 'NOT EXISTS'
        ),
        array(
            'key' => $prefix . 'exp_date',
            'compare' => '<=',
            'value' => $woo_vou_model->woo_vou_current_date()
        )
    );


    //Get User roles
    $user_roles = isset($current_user->roles) ? $current_user->roles : array();
    $user_role = array_shift($user_roles);

    if (in_array($user_role, $woo_vou_vendor_role)) { // Check vendor user role
        $args['author'] = $current_user->ID;
    }

    //add filter to group by order id
    add_filter('posts_groupby', array($woo_vou_model, 'woo_vou_groupby_order_id'));

    $voucodesdata = woo_vou_get_voucher_details($args);

    //remove filter to group by order id
    remove_filter('posts_groupby', array($woo_vou_model, 'woo_vou_groupby_order_id'));

    $vou_code_details = array();
    if (!empty($voucodesdata) && is_array($voucodesdata)) {

        foreach ($voucodesdata as $vou_codes_id) {

            $order_id = get_post_meta($vou_codes_id, $prefix . 'order_id', true); // get order id
            $order_date = get_post_meta($vou_codes_id, $prefix . 'order_date', true); // get order date
            $first_name = get_post_meta($vou_codes_id, $prefix . 'first_name', true); //buyer's first name who has unused voucher code
            $last_name = get_post_meta($vou_codes_id, $prefix . 'last_name', true); //buyer's last name who has unused voucher code
            //buyer's name who has unused voucher code              
            $buyer_name = $first_name . ' ' . $last_name;

            $args = array('post_parent' => $product_id, 'fields' => 'ids');
            $args['meta_query'] = array(
                array(
                    'key' => $prefix . 'order_id',
                    'value' => $order_id
                ),
                array(
                    'key' => $prefix . 'exp_date',
                    'compare' => '<=',
                    'value' => $woo_vou_model->woo_vou_current_date()
                )
            );

            $vouorderdata = woo_vou_get_voucher_details($args);
            $unused_codes = $redeem_by = array();

            // If unused codes are there
            if (!empty($vouorderdata) && is_array($vouorderdata)) {

                foreach ($vouorderdata as $order_vou_id) {

                    // get unused codes
                    $unused_codes[] = get_post_meta($order_vou_id, $prefix . 'purchased_codes', true);
                    $redeem_by[] = get_post_meta($order_vou_id, $prefix . 'redeem_by', true);
                }
            }

            // Check unused codes are not empty
            if (!empty($unused_codes)) {

                $vou_code_details[] = array(
                    'order_id' => $order_id,
                    'order_date' => $order_date,
                    'voucode_id' => $vou_codes_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'buyer_name' => $buyer_name,
                    'vou_codes' => implode(',', $unused_codes),
                    'redeem_by' => implode(',', $redeem_by)
                );
            }
        }
    }

    return apply_filters('woo_vou_get_unused_codes_by_product_id', $vou_code_details, $product_id);
}



/**
 * Restore voucher code to product
 * 
 * Handles to Restore voucher code to product again
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.4.0
 */
function woo_vou_restore_order_voucher_codes($order_id, $called_from = '') {

    $prefix = WOO_VOU_META_PREFIX;

    if (!empty($order_id)) {

        $args = array(
            'post_status' => array('pending'),
            'meta_query' => array(
                array(
                    'key' => $prefix . 'order_id',
                    'value' => $order_id,
                )
            )
        );

        //Get vouchers code of this order
        $vochers = woo_vou_get_voucher_details($args);

        if (!empty($vochers)) {//If empty voucher codes
            //get order meta
            $meta_order_details = get_post_meta($order_id, $prefix . 'meta_order_details', true);

            foreach ($vochers as $vocher) {

                //Initilize voucher codes array
                $salecode = array();

                //Get voucher code ID
                $vou_codes_id = isset($vocher['ID']) ? $vocher['ID'] : '';

                //Get product ID
                $product_id = isset($vocher['post_parent']) ? $vocher['post_parent'] : '';

                //Get voucher codes
                $voucher_codes = get_post_meta($vou_codes_id, $prefix . 'purchased_codes', true);

                //meta detail of specific product
                $product_meta_detail = isset($meta_order_details[$product_id]) ? $meta_order_details[$product_id] : array();

                //Voucher uses types
                $voucher_uses_type = isset($product_meta_detail['using_type']) ? $product_meta_detail['using_type'] : '';

                if (!empty($voucher_codes) && empty($voucher_uses_type)) {//If voucher codes available and type is not unlimited
                    $variation_id = get_post_meta($vou_codes_id, $prefix . 'vou_from_variation', true);

                    if (!empty($variation_id)) {

                        //voucher codes
                        $product_vou_codes = get_post_meta($variation_id, $prefix . 'codes', true);

                        //explode all voucher codes
                        $salecode = !empty($product_vou_codes) ? explode(',', $product_vou_codes) : array();

                        //append sales code array
                        $salecode[] = $voucher_codes;

                        //trim code
                        foreach ($salecode as $code_key => $code) {

                            $salecode[$code_key] = trim($code);
                        }

                        //Total avialable voucher code
                        $avail_total_codes = count($salecode);

                        //update total voucher codes
                        wc_update_product_stock($variation_id, $avail_total_codes);

                        //after restore code in array update in code meta
                        $lessvoucodes = implode(',', $salecode);
                        update_post_meta($variation_id, $prefix . 'codes', trim(html_entity_decode($lessvoucodes)));
                    } else {

                        //voucher codes
                        $product_vou_codes = get_post_meta($product_id, $prefix . 'codes', true);

                        //explode all voucher codes
                        $salecode = !empty($product_vou_codes) ? explode(',', $product_vou_codes) : array();

                        //append sales code array
                        $salecode[] = $voucher_codes;

                        //trim code
                        foreach ($salecode as $code_key => $code) {

                            $salecode[$code_key] = trim($code);
                        }

                        //Total avialable voucher code
                        $avail_total_codes = count($salecode);

                        //update total voucher codes
                        update_post_meta($product_id, $prefix . 'avail_total', $avail_total_codes);

                        //update total voucher codes
                        wc_update_product_stock($product_id, $avail_total_codes);

                        //after restore code in array update in code meta
                        $lessvoucodes = implode(',', $salecode);
                        update_post_meta($product_id, $prefix . 'codes', trim(html_entity_decode($lessvoucodes)));
                    }

                    //delete voucher post
                    wp_delete_post($vou_codes_id, true);

                    //If voucher codes available and type is unlimited
                } else if (!empty($voucher_codes) && !empty($voucher_uses_type)) {

                    //simply delete voucher post. in unlimited, no need to restore voucher codes.
                    wp_delete_post($vou_codes_id, true);
                }
            }

            //delete voucher order details
            delete_post_meta($order_id, $prefix . 'order_details');
            //delete voucher order details with all meta data
            delete_post_meta($order_id, $prefix . 'meta_order_details');

            // To resolved issue when order is resumed it hides voucher details. 
            // When order is resumed, called_from will not empty, so it will not hide voucher meta box
            if( empty( $called_from ) ) {
                //Set as hide voucher data ( For Cancelled/Refunded or simply hide )
                update_post_meta( $order_id, $prefix .'order_hide_voucher_data', true );
            }

        }
    }
}


/**
 * Refund voucher code to product
 * 
 * Handles to Refund voucher code to product again
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.4.0
 */
function woo_vou_refund_order_voucher_codes($order_id) {

    $prefix = WOO_VOU_META_PREFIX;

    if (!empty($order_id)) {

        $args = array(
            'post_status' => array('pending', 'publish'),
            'meta_query' => array(
                array(
                    'key' => $prefix . 'order_id',
                    'value' => $order_id,
                )
            )
        );

        //Get vouchers code of this order
        $vochers = woo_vou_get_voucher_details($args);

        /**
         * Restore coupons when refund order
         */
        $coupon_args = array(
            'post_status' => array('pending', 'publish'),
            'meta_query' => array(
                array(
                    'key' => $prefix . 'order_id',
                    'value' => $order_id,
                )
            )
        );

        //Get vouchers code of this order
        $coupons = woo_vou_get_coupon_details($coupon_args);
        if (!empty($coupons))
            $vochers = array_merge($vochers, $coupons);

        if (!empty($vochers)) {//If empty voucher codes
            foreach ($vochers as $vocher) {

                $vou_codes_id = isset($vocher['ID']) ? $vocher['ID'] : '';

                if (!empty($vou_codes_id)) {

                    $update_refund = array(
                        'ID' => $vou_codes_id,
                        'post_status' => WOO_VOU_REFUND_STATUS
                    );

                    //set status refunded of voucher post
                    wp_update_post($update_refund);
                }
            }
        }

        //Set as hide voucher data ( For Cancelled/Refunded or simply hide )
        update_post_meta( $order_id, $prefix .'order_hide_voucher_data', true );

    }
}


function woo_vou_unlink_preview_pdf(){

    if( array_key_exists( 'preview_file_name', $_POST ) && !empty( $_POST['preview_file_name'] ) ) {

        unlink(WOO_VOU_PREVIEW_UPLOAD_DIR.$_POST['preview_file_name']);

        echo true;
        exit;
    }
}

function woo_vou_generate_preview_pdf(){

    if( !empty( $_POST ) && array_key_exists( 'is_preview', $_POST )
        && $_POST['is_preview'] == 'true' && !empty( $_POST['product_id'] ) ) {

        $post_arr = $_POST;

        //product voucher pdf
        woo_vou_process_product_pdf_preview( $post_arr );
    }
}


/**
 * Allow Vendor User to upload image/logo at their profile
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.5.6
 */
function woo_vou_allow_vendor_uploads() {

    global $current_user, $woo_vou_vendor_role, $woo_vou_vendor_role;

    // Vendor user role
    $vendor_user_roles  = isset( $current_user->roles ) ? $current_user->roles : array();
    $vendor_user_role   = array_shift( $vendor_user_roles );

    if ( in_array( $vendor_user_role, $woo_vou_vendor_role ) ) {

        $contributor = get_role( $vendor_user_role );
        $contributor->add_cap('upload_files');
    }
}

/**
 * Check if current page is single product page
 * and generate popup html accordingly
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.5.6
 */
function woo_vou_render_preview_pdf_popup(){

    // If current page is single product page
    if( is_product() ) {
        global $post;
        $prefix = WOO_VOU_META_PREFIX; // Get prefix

        if( !empty( $post ) ){
            $post_id = $post->ID;
            $woo_vou_enable = get_post_meta( $post_id, $prefix.'enable', true );
            if( !empty( $woo_vou_enable ) && $woo_vou_enable == 'yes' ) {
              do_action( 'woo_vou_preview_pdf_popup' );
            }
        }
    }
}


/**
 * Remove Order Downloadable Items
 * 
 * Handles to remove order downloadable items from processing order, 
 * completed order mail and order thank you page.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.5.0
 */
function woo_vou_remove_order_downloadable_items( $downloads, $order ){

    //Get download enable for processing/completed order mail
    $vou_download_processing_mail = get_option('vou_download_processing_mail');

    // If backend is rendered
    if ( is_admin() ) {

        // Get current screen
        $get_current_screen = get_current_screen();

        // If page parent is woocommerce and post type is shop order
        if( !empty($get_current_screen) && $get_current_screen->parent_base == 'woocommerce' && $get_current_screen->post_type == 'shop_order' ){

            $vou_download_processing_mail = 'yes';
        }
    }

    // If download disable for processing/completed order mail
    if( !empty($vou_download_processing_mail) && ($vou_download_processing_mail == 'no') ) {

        $i = 0;
        foreach( $downloads as $download ) {

            $download_id = $download['download_id'];
            if (strpos($download_id, 'woo_vou_pdf_') !== false) {
                unset($downloads[$i]);
            }

            $i++;
        }
    }
	
    return $downloads;
}

/**
 * Change Recipient Giftdate format
 * 
 * Handles to change recipient giftdate format
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.5.0
 */
function woo_vou_change_giftdate_formatted_metadata( $display_value, $meta, $item ){

    $prefix = WOO_VOU_META_PREFIX;

    $recipient_giftdate_meta_data = $item->get_meta( $prefix.'recipient_giftdate', true );

    if( !empty( $recipient_giftdate_meta_data ) ) {

        $meta->key     = rawurldecode( (string) $meta->key );
        $meta->value   = rawurldecode( (string) $meta->value );
        $attribute_key = str_replace( 'attribute_', '', $meta->key );

        if( $attribute_key == $recipient_giftdate_meta_data['label'] ) {

            $date_format    = get_option( 'date_format' );
    
            $display_value  = date( $date_format, strtotime( $recipient_giftdate_meta_data['value'] ) );
        }
    }

    return $display_value;
}


/**
 * 
 * Handles to update the Voucher Extra Note from front end
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.3.4
 */
function woo_vou_update_voucher_extra_note() {

    global $woo_vou_model;
    
    $prefix = WOO_VOU_META_PREFIX; // Get prefix

    // If update the extra note
    if( isset($_REQUEST['woo_vou_extra_note_update']) && isset($_REQUEST[ 'woo_vou_code_id' ]) ){

        // Get data from submited form
        $woo_vou_code_id      = $_REQUEST[ 'woo_vou_code_id' ];
        $woo_vou_extra_note   = $woo_vou_model->woo_vou_escape_slashes_deep( trim( $_REQUEST[ 'woo_vou_extra_note' ] ) );

        update_post_meta( $woo_vou_code_id, $prefix.'extra_note', $woo_vou_extra_note );

        // Add message argument for Voucode Note and reload page
        wp_redirect( add_query_arg( array( 'message' => 'woo_vou_voucode_note_changed' ) ) );
        exit;
    }
}

/**
 * Handles to show PDF in downloads section
 * to which recipient email is sent
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.4.1
 */
function woo_vou_recipient_permission_vouchers_download_link( $downloads = array() ){

    global $woo_vou_model, $woo_vou_voucher;

    $prefix = WOO_VOU_META_PREFIX; // Get prefix
 
    $vou_permission_vou_download_recipient_user = get_option( 'vou_enable_permission_vou_download_recipient_user' ); // Get permission option

    // If user is logged in and check permission option and download enable for customer dashboard
    $vou_download_dashboard = get_option('vou_download_dashboard');
    if ( is_user_logged_in() && ( $vou_download_dashboard == 'yes') && !empty($vou_permission_vou_download_recipient_user) 
        && ( $vou_permission_vou_download_recipient_user == 'yes' ) ) {
    
        // Get user ID
        $user_id = get_current_user_id();
        $voucode_args = array(
            'meta_query' => array(
                array(
                    'key'       => $prefix . 'recipient_userid',
                    'value'     => $user_id,
                    'compare'   => '='
                ),
            )
        );

		
        // Get purchased voucher codes data from database
        $woo_data   = woo_vou_get_voucher_details( $voucode_args );
		
        if( !empty( $woo_data ) && is_array( $woo_data ) ){

            foreach( $woo_data as $vou_code ) {

                // Get voucher id and oreder id
                $vou_code_id = $vou_code['ID'];
                $order_id  = get_post_meta($vou_code_id, $prefix.'order_id', true);
                
                if( !empty( $order_id ) ) {
					
                    //Get cart details
                    $cart_details = wc_get_order($order_id);
                    $order_items = $cart_details->get_items();
                    $order_date = $woo_vou_model->woo_vou_get_order_date_from_order($cart_details); // Get order date
                    $order_date = date('F j, Y', strtotime($order_date));

                    if (!empty($order_items)) {// Check cart details are not empty
                        foreach ($order_items as $item_id => $product_data) {

                            //Get product from Item ( It is required otherwise multipdf voucher link not work and global $woo_vou_item_id will not work )
                            $_product = apply_filters('woocommerce_order_item_product', $cart_details->get_product_from_item($product_data), $product_data);

                            if (!$_product) {//If product deleted
                                $download_file_data = array();
                            } else {
                                $download_file_data = $woo_vou_model->woo_vou_get_item_downloads_from_order($cart_details, $product_data);
                            }

                            //Get voucher codes
                            $codes = wc_get_order_item_meta($item_id, $prefix.'codes');
                            $vou_codes = !empty($codes) ? explode(', ', $codes) : '';
                            if( !empty( $vou_codes ) && is_array( $vou_codes ) ) {

                                $vou_code_sr_no = 1;
                                foreach( $vou_codes as $vou_code ){
									
                                    $_voucodeid        = woo_vou_get_voucodeid_from_voucode($vou_code); // Get voucher code id

                                    if( $_voucodeid == $vou_code_id ) {

                                        //Get product from Item ( It is required otherwise multipdf voucher link not work and global $woo_vou_item_id will not work )
                                        $_product = apply_filters('woocommerce_order_item_product', $cart_details->get_product_from_item($product_data), $product_data);

                                        if ( !$_product ) {//If product deleted

                                            $download_file_data = array();
                                        } else {

                                            $download_file_data = $woo_vou_model->woo_vou_get_item_downloads_from_order($cart_details, $product_data);
                                        }

                                        //Get voucher codes
                                        $codes = wc_get_order_item_meta($item_id, $prefix.'codes');

                                        if (!empty($download_file_data) && !empty($codes)) {//If download exist and code is not empty
                                            foreach ($download_file_data as $key => $download_file) {

                                                //check download key is voucher key or not
                                                $check_key = strpos($key, 'woo_vou_pdf_');

                                                //get voucher number
                                                $voucher_number = str_replace('woo_vou_pdf_', '', $key);

                                                if (empty($voucher_number)) {//If empty voucher number
                                                    $voucher_number = 1;
                                                }

                                                if( $voucher_number == $vou_code_sr_no ) {

                                                    if (!empty($download_file) && $check_key !== false) {

                                                        //Get download URL
                                                        $download_url = $download_file['download_url'];

                                                        //add arguments array
                                                        $add_arguments = array('item_id' => $item_id, 'woo_vou_screen' => 'download');

                                                        //PDF Download URL
                                                        $download_url = add_query_arg($add_arguments, $download_url);

                                                        // To make compatible with previous versions of 3.0.0
                                                        if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1) {
                                                            //Get product ID
                                                            $product_id = isset($_product->post->ID) ? $_product->post->ID : '';
                                                            //get product name
                                                            $product_name = isset($_product->post->post_title) ? $_product->post->post_title : '';
                                                        } else {
                                                            //Get product ID
                                                            $product_id = $_product->get_id();
                                                            //get product name
                                                            $product_name = $_product->get_title();
                                                        }

                                                        $vou_codes          = $codes; // Get voucher codes from meta
                                                        $vou_code           = explode(',', $vou_codes); // Explode voucher code in case it is having multiple voucher codes
                                                        $vou_code_id        = woo_vou_get_voucodeid_from_voucode($vou_code[0]); // Get voucher code id
                                                        $vou_expiry_date    = ''; // Declare variable
                                                        if(!empty($vou_code_id)){

                                                            // Get voucher expiry date
                                                            $vou_expiry_date = get_post_meta($vou_code_id, $prefix.'exp_date', true);
                                                        }

                                                        //Download file arguments
                                                        $download_args = array(
                                                            'product_id' => $product_id,
                                                            'product_url' => get_permalink( $product_id ),
                                                            'product_name' => $product_name,
                                                            'download_url' => $download_url,
                                                            'download_name' => apply_filters( 'woo_vou_download_page_vou_download_btn', $product_name . ' - ' . $download_file['name'] . ' ' . $voucher_number . ' ( ' . $order_date . ' )', $product_id, $product_name, $download_file, $voucher_number, $order_date),
                                                            'downloads_remaining' => '',
                                                            'access_expires' => $vou_expiry_date,
                                                            'file' => array(
                                                                'name' => $download_file['name'],
                                                                'file' => $download_file['file'],
                                                            ),
                                                        );

                                                        //append voucher download to downloads array
                                                        $downloads[] = $download_args;
                                                    }
                                                }
                                            }
                                        }
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }	
    return $downloads;
}

/**
 * Handles to update user meta
 * 
 * This function handles to assign the vouchers to the newly created user
 * when recipient email is sent to that email id and customer was not registered
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.4.1
 */
function woo_vou_created_customer( $customer_id ){

    $prefix = WOO_VOU_META_PREFIX; // Get prefix

    $vou_nonuser_recipient_email = get_option('vou_nonuser_recipient_email'); // get option if allow recipient email is set

    // If customer id is not empty and allow recipient email is set
    if( !empty( $customer_id ) && !empty( $vou_nonuser_recipient_email ) ) {

        $new_customer_data = get_user_by( 'id', $customer_id );
        foreach ( $vou_nonuser_recipient_email as $email => $voucodeids ) {

            if( !empty( $new_customer_data ) && $email == $new_customer_data->data->user_email ) {

                if( !empty( $voucodeids ) ){
                    foreach( $voucodeids as $voucodeid ){
                        update_post_meta($voucodeid, $prefix.'recipient_userid', $customer_id );
                    }
                }

                unset($vou_nonuser_recipient_email[$email]);
                update_option('vou_nonuser_recipient_email', $vou_nonuser_recipient_email);
                break;
            }
        }
    }
}