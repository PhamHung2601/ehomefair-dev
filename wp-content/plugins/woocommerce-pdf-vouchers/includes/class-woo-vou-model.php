<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Model Class
 * 
 * Handles generic plugin functionality.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
class WOO_Vou_Model {

    public function __construct() {
        
    }

    /**
     * Escape Tags & Slashes
     * 
     * Handles escaping the slashes and tags
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    public function woo_vou_escape_attr($data) {
        return esc_attr(stripslashes($data));
    }

    /**
     * Strip Slashes From Array
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    public function woo_vou_escape_slashes_deep($data = array(), $flag = false, $limited = false) {

        if ($flag != true) {
            $data = $this->woo_vou_nohtml_kses($data);
        } else {
            if ($limited == true) {
                $data = wp_kses_post($data);
            }
        }

        $data = stripslashes_deep($data);

        return $data;
    }

    /**
     * Strip Html Tags
     * 
     * It will sanitize text input (strip html tags, and escape characters)
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    public function woo_vou_nohtml_kses($data = array()) {

        if (is_array($data)) {
            $data = array_map(array($this, 'woo_vou_nohtml_kses'), $data);
        } elseif (is_string($data)) {
            $data = wp_filter_nohtml_kses($data);
        }

        return $data;
    }

    /**
     * Convert Object To Array
     * 
     * Converting Object Type Data To Array Type
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    public function woo_vou_object_to_array($result) {

        $array = array();
        foreach ($result as $key => $value) {

            if (is_object($value)) {
                $array[$key] = $this->woo_vou_object_to_array($value);
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    /**
     * Get Date Format
     * 
     * Handles to return formatted date which format is set in backend
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    public function woo_vou_get_date_format($date, $time = false) {

        $format = $time ? get_option('date_format') . ' ' . get_option('time_format') : get_option('date_format');

        // added new filter for date format
        $format = apply_filters('woo_vou_before_get_date_format', $format );

        $date = date_i18n($format, strtotime($date));
        return apply_filters('woo_vou_get_date_format', $date);
    }

    /**
     * Group By Order ID
     *
     * Handles to group by order id
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    public function woo_vou_groupby_order_id($groupby) {

        global $wpdb;

        $groupby = "{$wpdb->posts}.post_title"; // post_title is used for order id

        return $groupby;
    }

    /**
     * Convert Color Hexa to RGB
     *
     * Handles to return RGB color from hexa color
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    public function woo_vou_hex_2_rgb($hex) {

        $rgb = array();
        if (!empty($hex)) {

            $hex = str_replace("#", "", $hex);

            if (strlen($hex) == 3) {
                $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
                $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
                $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
            } else {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
            }
            $rgb = array($r, $g, $b);
        }

        return apply_filters('woo_vou_hex_2_rgb', $rgb, $hex); // returns an array with the rgb values
    }

    /**
     * Get All voucher order details
     * 
     * Handles to return all voucher order details
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.1.0
     */
    public function woo_vou_get_all_ordered_data($orderid) {

        $prefix = WOO_VOU_META_PREFIX;

        $data = get_post_meta($orderid, $prefix . 'meta_order_details', true);
        return apply_filters('woo_vou_all_ordered_data', $data);
    }

    public function woo_vou_get_all_product_data($productid, $variation_id='', $post_arr ){

        $prefix = WOO_VOU_META_PREFIX;

        $return = array();

        $start_date = get_post_meta($productid, $prefix.'start_date', true); // start date
        $start_date = !empty($start_date) ? date('Y-m-d H:i:s', strtotime($start_date)) : ''; // format start date
        $manual_expire_date = get_post_meta($productid, $prefix.'exp_date', true); // expiration date
        $exp_date = !empty($manual_expire_date) ? date('Y-m-d H:i:s', strtotime($manual_expire_date)) : ''; // format exp date
        $disable_redeem_days = get_post_meta($productid, $prefix.'disable_redeem_day', true); // Disable redeem days
        
        if (empty($disable_redeem_days))
            $disable_redeem_days = '';

        $exp_type = get_post_meta($productid, $prefix . 'exp_type', true); //get expiration tpe
        $custom_days = $allcodes = ''; //custom days

        if ($exp_type == 'based_on_purchase') { //If expiry type based in purchase
            //get days difference
            $days_diff = get_post_meta($productid, $prefix.'days_diff', true);

            if ($days_diff == 'cust') {
                $custom_days = get_post_meta($productid, $prefix . 'custom_days', true);
                $custom_days = isset($custom_days) ? $custom_days : '';

                if (!empty($custom_days)) {

                    $add_days = '+' . $custom_days . ' days';
                    $exp_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d') . $add_days));
                } else {

                    $exp_date = '';
                }
            } else {
                $custom_days = $days_diff;
                $add_days = '+' . $custom_days . ' days';
                $exp_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d') . $add_days));
            }
        } else if ($exp_type == 'based_on_gift_date') { //If expiry type based Recipient Gift Date
            //get days difference
            $days_diff = get_post_meta($productid, $prefix.'days_diff', true);

            // Set gift date
            if( !empty($post_arr[$prefix . 'recipient_giftdate']) ){

                // Get product id / variation id
                $data_id        = !empty($variation_id) ? $variation_id : $productid;
                $recipient_giftdate = ( !empty($post_arr[$prefix . 'recipient_giftdate'][$data_id]) ) ? ($post_arr[$prefix . 'recipient_giftdate'][$data_id]) : date('Y-m-d') ;
            } else {
                $recipient_giftdate = date('Y-m-d');
            }
            if ($days_diff == 'cust') {
                $custom_days = get_post_meta($productid, $prefix . 'custom_days', true);
                $custom_days = isset($custom_days) ? $custom_days : '';

                if (!empty($custom_days)) {

                    $add_days = '+' . $custom_days . ' days';
                    $exp_date = date('Y-m-d H:i:s', strtotime($recipient_giftdate . $add_days));
                } else {

                    $exp_date = '';
                }
            } else {
                $custom_days = $days_diff;
                $add_days = '+' . $custom_days . ' days';
                $exp_date = date('Y-m-d H:i:s', strtotime($recipient_giftdate . $add_days));
            }
        } else if( ($exp_type == 'default')) { // If product meta is set to default

            $exp_type = get_option('vou_exp_type'); //get expiration type 
            
            if($exp_type == 'specific_date') { //If expiry type specific date

                $start_date = get_option('vou_start_date'); // start date
                $start_date = !empty($start_date) ? date('Y-m-d H:i:s', strtotime($start_date)) : ''; // format start date
                $manual_expire_date = get_option('vou_exp_date'); // expiration date
                $exp_date = !empty($manual_expire_date) ? date('Y-m-d H:i:s', strtotime($manual_expire_date)) : ''; // format exp date

            } else if ($exp_type == 'based_on_purchase') { //If expiry type based in purchase

                //get days difference
                $days_diff = get_option('vou_days_diff');

                if ($days_diff == 'cust') {
                    $custom_days = get_option('vou_custom_days');
                    $custom_days = isset($custom_days) ? $custom_days : '';

                    if (!empty($custom_days)) {
                        $add_days = '+' . $custom_days . ' days';
                        $exp_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d') . $add_days));
                    } else {
                        $exp_date = date('Y-m-d H:i:s', current_time('timestamp'));
                    }
                } else {
                    $custom_days = $days_diff;
                    $add_days = '+' . $custom_days . ' days';
                    $exp_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d') . $add_days));
                }
            }
        }

        //vendor user
        $vendor_user = get_post_meta($productid, $prefix.'vendor_user', true);

        //get vendor detail
        $vendor_detail = $this->woo_vou_get_vendor_detail($productid, '', $vendor_user );

        $return['redeem']           = $vendor_detail['how_to_use'];
        $return['start_date']       = $start_date;
        $return['exp_date']         = $exp_date;
        $return['vendor_logo']      = $vendor_detail['vendor_logo'];
        $return['website_url']      = $vendor_detail['vendor_website'];
        $return['vendor_address']   = $vendor_detail['vendor_address'];
        $return['avail_locations']  = $vendor_detail['avail_locations'];
        $return['pdf_template']     = $vendor_detail['pdf_template'];

        return $return;
    }

    /**
     * Get the current date from timezone
     * 
     * Handles to get current date
     * according to timezone setting
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.1.0
     */
    public function woo_vou_current_date($format = 'Y-m-d H:i:s') {

        if (!empty($format)) {
            $date_time = date($format, current_time('timestamp'));
        } else {
            $date_time = date('Y-m-d H:i:s', current_time('timestamp'));
        }

        return apply_filters('woo_vou_current_date', $date_time, $format);
    }

    /**
     * Get the product details from order id
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.1.0
     */
    public function woo_vou_get_product_details($orderid, $items = array()) {

        // If order id is empty then return
        if (empty($orderid))
            return false;

        // Taking some defaults
        $result_item = array();

        //Get Order
        $woo_order = new WC_Order($orderid);

        // If item is empty or not passed then get from order.
        if (empty($items) || !is_array($items)) {            
            $items = $woo_order->get_items();
        }

        if (!empty($items)) {

            foreach ($items as $item_key => $item_val) {

                if (isset($item_val['product_id']) || $item_val['variation_id']) {

                    if (!empty($item_val['variation_id'])) {
                        $product_id = $item_val['variation_id'];
                    } else {
                        $product_id = $item_val['product_id'];
                    }

                    // Get productid, added to add compatibility with WPML
                    $product_id = apply_filters('woo_vou_productid_from_orderid', $product_id, $orderid);

                    //Product name
                    $result_item[$product_id]['product_name'] = !empty($item_val['name']) ? $item_val['name'] : '';                    
                    // filter added to add compatiblity with qTranslate-X
                    $result_item[$product_id]['product_name'] = apply_filters('woo_vou_product_name', $result_item[$product_id]['product_name']);                    
                    // filter added to add comapatibility with WPML
                    $result_item[$product_id]['product_name'] = apply_filters('woo_vou_product_name_from_productid', $result_item[$product_id]['product_name'], $product_id);
                    $result_item[$product_id]['item_key'] = $item_key;

                    //Product price
                    if (!empty($item_val['qty'])) {
                        $product_price = ( $item_val['line_total'] / $item_val['qty'] );
                    } else {
                        $product_price = '';
                    }

                    $result_item[$product_id]['product_price'] = $product_price;
                    $result_item[$product_id]['product_formated_price'] = $this->woo_vou_get_formatted_product_price($orderid, $item_val);

                    // Total order price
                    $result_item[$product_id]['product_price_total'] = isset($item_val['line_total']) ? $item_val['line_total'] : '';
                    $result_item[$product_id]['product_quantity'] = isset($item_val['qty']) && !empty($item_val['qty']) ? $item_val['qty'] : '';
                    $result_item[$product_id]['recipient_name'] = isset($item_val['woo_vou_recipient_name']) ? $item_val['woo_vou_recipient_name'] : '';
                    $result_item[$product_id]['recipient_email'] = isset($item_val['woo_vou_recipient_email']) ? $item_val['woo_vou_recipient_email'] : '';
                    $result_item[$product_id]['recipient_message'] = isset($item_val['woo_vou_recipient_message']) ? $item_val['woo_vou_recipient_message'] : '';
                }
            }
        } // End of if 

        return apply_filters('woo_vou_get_product_details', $result_item, $orderid, $items);
    }

    /**
     * Gets Order product Price in voucher code
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.1.0
     */
    public function woo_vou_get_formatted_product_price($orderid, $item, $tax_display = '') {

        //Get prefix
        $prefix = WOO_VOU_META_PREFIX;

        //Get Order
        $woo_order = new WC_Order($orderid);

        $tax_display = $tax_display ? $tax_display : get_option('woocommerce_tax_display_cart');

        if (!isset($item['line_subtotal']) || !isset($item['line_subtotal_tax'])) {
            return '';
        }

        //get multipdf option from order meta
        $multiple_pdf = get_post_meta( $orderid, $prefix . 'multiple_pdf', true );
        if( is_array( $multiple_pdf ) ) {
            $multiple_pdf = !empty( $multiple_pdf[$item['product_id']] ) ? $multiple_pdf[$item['product_id']] : '';
        }

        //Get Item quantity
        $item_qty = isset($item['qty']) ? $item['qty'] : '';

        if ('excl' == $tax_display) {

            if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1)
                $ex_tax_label = $woo_order->prices_include_tax ? 1 : 0;
            else
                $ex_tax_label = $woo_order->get_prices_include_tax() ? 1 : 0;
            $line_subtotal = $woo_order->get_line_subtotal($item);
            if ($multiple_pdf == 'yes' && !empty($item_qty)) {
                $line_subtotal = $line_subtotal / $item_qty;
            }

            $subtotal = wc_price($line_subtotal, array('ex_tax_label' => $ex_tax_label, 'currency' => woo_vou_get_order_currency($woo_order)));
        } else {

            $line_subtotal = $woo_order->get_line_subtotal($item, true);
            if ($multiple_pdf == 'yes' && !empty($item_qty)) {
                $line_subtotal = $line_subtotal / $item_qty;
            }
            $subtotal = wc_price($line_subtotal, array('currency' => woo_vou_get_order_currency($woo_order)));
        }

        return apply_filters('woo_vou_get_formatted_product_price', $subtotal, $orderid, $item, $tax_display);
    }
    
    /**
     * Gets Order product Price in voucher code
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.1.0
     */
    public function woo_vou_get_product_price($orderid, $item_id, $item, $tax_display = '') {

        $prefix     = WOO_VOU_META_PREFIX; // Get prefix
        $woo_order  = wc_get_order($orderid); // Get Order
        $subtotal   = wc_get_order_item_meta( $item_id, $prefix.'voucher_price', true ); // Get total price of voucher code

        // Check voucher price empty or not
        if( empty( $subtotal ) ) {

            $tax_display = $tax_display ? $tax_display : get_option('woocommerce_tax_display_cart');
    
            if (!isset($item['line_subtotal']) || !isset($item['line_subtotal_tax'])) {
                return '';
            }
    
            //get multipdf option from order meta
            $multiple_pdf = get_post_meta( $orderid, $prefix . 'multiple_pdf', true );
            if( is_array( $multiple_pdf ) ) {
                $multiple_pdf = !empty( $multiple_pdf[$item['product_id']] ) ? $multiple_pdf[$item['product_id']] : '';
            }
    
            //Get Item quantity
            $item_qty = isset($item['qty']) ? $item['qty'] : '';
    
            if ('excl' == $tax_display) {
    
                if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1)
                    $ex_tax_label = $woo_order->prices_include_tax ? 1 : 0;
                else
                    $ex_tax_label = $woo_order->get_prices_include_tax() ? 1 : 0;
                $line_subtotal = $woo_order->get_line_subtotal($item);
                if ($multiple_pdf == 'yes' && !empty($item_qty)) {
                    $line_subtotal = $line_subtotal / $item_qty;
                }
    
                $subtotal = $line_subtotal;
            } else {
    
                $line_subtotal = $woo_order->get_line_subtotal($item, true);
                if ($multiple_pdf == 'yes' && !empty($item_qty)) {
                    $line_subtotal = $line_subtotal / $item_qty;
                }
                $subtotal = $line_subtotal;
            }
        }

        return apply_filters('woo_vou_get_product_price', $subtotal, $orderid, $item_id, $item, $tax_display);
    }

    /**
     * Get the vendor detail to store in order meta
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.5
     */
    public function woo_vou_get_vendor_detail($productid, $variation_id, $vendor_user) {

        global $woo_vou_vendor_role;

        $prefix = WOO_VOU_META_PREFIX;
        $vendor_detail = array();

        $user_data = get_userdata($vendor_user);

        //Get User roles
        $user_roles = isset($user_data->roles) ? $user_data->roles : array();
        $user_role = array_shift($user_roles);

        //Vendor Logo
        $ven_logo = get_post_meta($productid, $prefix . 'logo', true);

        $vendor_logo = !empty( $ven_logo ) ? $ven_logo : array();
        
        //Vendor Address
        $vendor_address = get_post_meta($productid, $prefix . 'address_phone', true);
        //Website URL
        $website_url = get_post_meta($productid, $prefix . 'website', true);
        //Redeem Instructions
        $how_to_use = get_post_meta($productid, $prefix . 'how_to_use', true);
        //Locations
        $avail_locations = get_post_meta($productid, $prefix . 'avail_locations', true);
        //Usability
        $using_type = get_post_meta($productid, $prefix . 'using_type', true);
        //PDF Template
        $pdf_template = get_post_meta($productid, $prefix . 'pdf_template', true);
        
        // if product is variable and varition id set
        if( !empty( $variation_id ) ) {
           
           //Vendor Address
           $variable_vendor_address = get_post_meta($variation_id, $prefix . 'vendor_address', true);                      
           if( !empty( $variable_vendor_address ) )                
                $vendor_address = $variable_vendor_address;            
            
            //PDF Template
            $variable_pdf_template = get_post_meta($variation_id, $prefix . 'pdf_template', true);
            if( !empty( $variable_pdf_template ) )
                $pdf_template = $variable_pdf_template;
        }       
       
        // check if user id is not empty and user role is vendor
        if (!empty($vendor_user) && in_array($user_role, $woo_vou_vendor_role)) {

            //Vendor Logo
            if (empty($vendor_logo['src'])) {
                $vendor_logo['src'] = get_user_meta($vendor_user, $prefix . 'logo', true);
            }

           //Vendor Address
            if (empty($vendor_address)) {
                $vendor_address = get_user_meta($vendor_user, $prefix . 'address_phone', true);
            }

            //Website URL
            if (empty($website_url)) {
                $website_url = get_user_meta($vendor_user, $prefix . 'website', true);
            }

            //Redeem Instructions
            if (empty($how_to_use)) {
                $how_to_use = get_user_meta($vendor_user, $prefix . 'how_to_use', true);
            }

            //Locations
            if (empty($avail_locations)) {
                $avail_locations = get_user_meta($vendor_user, $prefix . 'avail_locations', true);
            }

            //Usability
            if ($using_type == '') {
                $using_type = get_user_meta($vendor_user, $prefix . 'using_type', true);
            }

            //PDF Template
            if (empty($pdf_template)) {
                $pdf_template = get_user_meta($vendor_user, $prefix . 'pdf_template', true);
            }
        }

        // If using type is blank then take it from setting
        if ($using_type == '') {
            $using_type = get_option('vou_pdf_usability');
        }

        $vendor_detail = array(
            'vendor_logo' => $vendor_logo,
            'vendor_address' => $vendor_address,
            'vendor_website' => $website_url,
            'how_to_use' => $how_to_use,
            'avail_locations' => $avail_locations,
            'using_type' => $using_type,
            'pdf_template' => $pdf_template
        );

        return apply_filters('woo_vou_get_vendor_detail', $vendor_detail, $productid, $variation_id, $vendor_user);
    }

    /**
     * Set the orderid as global
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.6
     */
    public function woo_vou_get_orderid_for_page() {

        global $vou_order;

        //Set OrderId Blank
        $order_id = '';

        // Order id get from order detail page
        $order_recieved_id = get_query_var('order-received');

        // Order id get from view order page
        $order_view_id = get_query_var('view-order');

        if (!empty($order_recieved_id)) {  // If on order detail page
            $order_id = $order_recieved_id;
        } else if (!empty($order_view_id)) { // If on view order page
            $order_id = $order_view_id;
        } else if (!empty($vou_order)) {  // If global order id is set
            $order_id = $vou_order;
        }

        return apply_filters('woo_vou_get_orderid_for_page', $order_id);
    }

    /**
     * Get variation detail from order and item id
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.2.2
     */
    public function woo_vou_get_variation_data($woo_order = array(), $item_key = '') {

        $items = $woo_order->get_items();

        $item_array = $this->woo_vou_get_item_data_using_item_key($items, $item_key);

        $item = isset($item_array['item_data']) ? $item_array['item_data'] : array();
        $item_id = isset($item_array['item_id']) ? $item_array['item_id'] : array();

        //Get product from Item ( It is required otherwise multipdf voucher link not work and global $woo_vou_item_id will not work )
        $_product = $woo_order->get_product_from_item($item);

        //Get variation data without recipient fields
        $variation_data = $this->woo_vou_display_product_item_name($item, $_product, true);

        return apply_filters('woo_vou_get_variation_data', $variation_data, $woo_order, $item_key);
    }

    /**
     * Get variation Data From Item Key
     * 
     * Handle to get variation recipient data from order item key
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.2.2
     */
    public function woo_vou_get_recipient_data_using_item_key($item_key = '') {
        
        //Prefix
        $prefix = WOO_VOU_META_PREFIX;

        // To make compatible with previous versions of 3.0.0
        if ( version_compare( WOOCOMMERCE_VERSION, "3.0.0" ) == -1 ) {

            $wc_order = new WC_Order;
            $product_item_meta = $wc_order->get_item_meta($item_key);
        } else {

            // Recipient Data Columns
            $recipient_data_cols = woo_vou_voucher_recipient_details();

            foreach( $recipient_data_cols as $recipient_col_key => $recipient_col_val ) {

                $product_item_meta[$prefix.$recipient_col_key]      = wc_get_order_item_meta($item_key, $prefix.$recipient_col_key);
            }
            $product_item_meta[$prefix . 'pdf_template_selection']  = wc_get_order_item_meta($item_key, $prefix . 'pdf_template_selection');
        }

        //Get variation data without recipient fields
        $variation_data = $this->woo_vou_get_recipient_data($product_item_meta);
        return apply_filters('woo_vou_get_recipient_data_using_item_key', $variation_data, $item_key);
    }

    /**
     * Get product recipient meta setting
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.0
     */
    public function woo_vou_get_product_recipient_meta($product_id = '') {

        //Prefix
        $prefix = WOO_VOU_META_PREFIX;

        //default recipient data
        $recipient_data = array(
            'enable_pdf_template_selection' => '',
            'pdf_template_selection_label' => esc_html__('PDF Template', 'woovoucher'),
            'pdf_template_desc' => '',
            'recipient_detail_order' => array( $prefix.'enable_recipient_name', $prefix.'enable_recipient_email', $prefix.'enable_recipient_message', $prefix.'enable_recipient_giftdate' ),
            'enable_recipient_delivery_method' => false,
            'recipient_delivery_method' => array(),
            'recipient_delivery_label' => esc_html__('Delivery Method', 'woovoucher'),
            'individual_recipient_details' => array(),
            'default_delivery_meths' => woo_vou_voucher_delivery_methods(),
            'delivery_meth_configured' => false,
            'recipient_columns' => woo_vou_voucher_recipient_details()
        );

        if (!empty($product_id)) {

            // Initialising default
            $recipient_order_default_arr = array();

            foreach ( $recipient_data['recipient_columns'] as $recipient_key => $recipient_val ) {

                // Declaring blank variables so as to save default value
                $recipient_key_enable = $recipient_key_max_length = $recipient_key_label ='';
                $recipient_key_is_required = $recipient_key_desc = '';
        
                $recipient_key_enable       = get_post_meta( $product_id, $prefix.'enable_'.$recipient_key, true );

                $recipient_key_label        = get_post_meta( $product_id, $prefix.$recipient_key.'_label', true );
                $recipient_key_label        = !empty( $recipient_key_label ) ? $recipient_key_label : ( !empty( $recipient_val['label'] ) ? $recipient_val['label'] : '' );

                $recipient_key_max_length   = get_post_meta( $product_id, $prefix.$recipient_key.'_max_length', true );
                $recipient_key_is_required  = get_post_meta( $product_id, $prefix.$recipient_key.'_is_required', true );
                $recipient_key_desc         = get_post_meta( $product_id, $prefix.$recipient_key.'_desc', true );

                // Update post meta for all the mets data
                $recipient_data['enable_'.$recipient_key]       = $recipient_key_enable;
                $recipient_data[$recipient_key.'_label']        = $recipient_key_label;
                $recipient_data[$recipient_key.'_max_length']   = $recipient_key_max_length;
                $recipient_data[$recipient_key.'_is_required']  = $recipient_key_is_required;
                $recipient_data[$recipient_key.'_desc']         = $recipient_key_desc;

                $recipient_order_default_arr[] = $prefix.'enable_'.$recipient_key;
            }

            //pdf template selection fields
            $recipient_data['enable_pdf_template_selection'] = get_post_meta($product_id, $prefix . 'enable_pdf_template_selection', true);

            $pdf_template_selection_label = get_post_meta($product_id, $prefix . 'pdf_template_selection_label', true);

            $pdf_template_selection_label = !empty($pdf_template_selection_label) ? $pdf_template_selection_label : esc_html__('Voucher Template', 'woovoucher');

            $recipient_data['pdf_template_selection_label'] = $pdf_template_selection_label;
            $recipient_data['pdf_template_desc'] = get_post_meta($product_id, $prefix . 'pdf_selection_desc', true);

            // Recipient Details Order fields
            $recipient_detail_order = get_post_meta( $product_id, $prefix . 'recipient_detail_order', true );
            if( !empty($recipient_detail_order) ) {

                $recipient_data['recipient_detail_order'] = $recipient_detail_order;
            } else {

                $recipient_data['recipient_detail_order'] = $recipient_order_default_arr;
            }

            $delivery_methods               = woo_vou_voucher_delivery_methods();
            $product_delivery_meth          = get_post_meta($product_id, $prefix . 'recipient_delivery', true);
            $delivery_label                 = get_post_meta($product_id, $prefix . 'recipient_delivery_label', true);
            $delivery_meth_configured       = false;
            $individual_recipient_details   = woo_vou_voucher_recipient_details();

            $recipient_data['enable_recipient_delivery_method'] = $enable_recipient_delivery_method = get_post_meta( $product_id, $prefix . 'enable_recipient_delivery_method', true );
            $recipient_data['recipient_delivery_label']         = !empty( $delivery_label ) ? $delivery_label : esc_html__( 'Delivery Method', 'woovoucher' );

            // If product delivery method is not empty
            // And Delivery methods array is not empty
            // And delivery method is enabled
            if ( !empty( $product_delivery_meth ) && !empty( $delivery_methods ) && $enable_recipient_delivery_method == 'yes' ) {

                // Set initial flag to false
                $delivery_method_flag = false;

                // Initialise array
                $recipient_data['recipient_delivery_method'] = array();

                // Looping on all delivery methods
                foreach( $delivery_methods as $delivery_method_key => $delivery_method_val ) {

                    // If particular delivery method is enabled
                    if( ( !empty( $product_delivery_meth['enable_'.$delivery_method_key] ) && $product_delivery_meth['enable_'.$delivery_method_key] == 'yes' ) ) {

                        $delivery_method_flag = true;
                        $recipient_data['recipient_delivery_method']['enable_'.$delivery_method_key] = $product_delivery_meth['enable_'.$delivery_method_key];
                        $recipient_data['recipient_delivery_method'][$delivery_method_key] = array_key_exists( $delivery_method_key, $product_delivery_meth ) && !empty( $product_delivery_meth[$delivery_method_key] ) ? $product_delivery_meth[$delivery_method_key] : '';
                        $recipient_data['recipient_delivery_method']['label_'.$delivery_method_key] = !empty( $product_delivery_meth['label_'.$delivery_method_key] ) ? $product_delivery_meth['label_'.$delivery_method_key] : $delivery_method_val;
                        $recipient_data['recipient_delivery_method']['desc_'.$delivery_method_key] = !empty( $product_delivery_meth['desc_'.$delivery_method_key] ) ? $product_delivery_meth['desc_'.$delivery_method_key] : '';

                        if( !empty( $product_delivery_meth[$delivery_method_key] ) && is_array( $product_delivery_meth[$delivery_method_key] ) ) {

                            foreach( $product_delivery_meth[$delivery_method_key] as $delivery_recipient_method ) {
    
                                if( array_key_exists( $delivery_recipient_method, $individual_recipient_details ) ){

                                    unset( $individual_recipient_details[$delivery_recipient_method] );
                                    $delivery_meth_configured = true;
                                }
                            }
                        }
                    }
                }
            }

            $recipient_data['individual_recipient_details'] = $individual_recipient_details;
            $recipient_data['delivery_meth_configured'] = $delivery_meth_configured;
        }

        return apply_filters('woo_vou_get_product_recipient_meta', $recipient_data, $product_id);
    }

    /**
     * Get Recipient Data
     * 
     * Handles to replace recipient data in gift notification email and 
     * in downloaded pdf for recipient name, email, message
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.2.2
     */
    public function woo_vou_get_recipient_data($product_item_meta = array()) {

        //Prefix
        $prefix = WOO_VOU_META_PREFIX;

        //initilize recipient array
        $recipient_details = array();

        if (!empty($product_item_meta)) {

            // To make compatible with previous versions of 3.0.0
            if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1) {
                //Get recipient name from orders
                if (!empty($product_item_meta[$prefix . 'recipient_name']) && !empty($product_item_meta[$prefix . 'recipient_name'][0])) {

                    if (is_serialized($product_item_meta[$prefix . 'recipient_name'][0])) { // for new orders
                        $recipient_name_field = maybe_unserialize($product_item_meta[$prefix . 'recipient_name'][0]);
                        $recipient_details['recipient_name'] = isset($recipient_name_field['value']) ? $recipient_name_field['value'] : '';
                    } else { // for old orders
                        $recipient_details['recipient_name'] = $product_item_meta[$prefix . 'recipient_name'][0];
                    }
                }

                //Get recipient email from orders
                if (!empty($product_item_meta[$prefix . 'recipient_email']) && !empty($product_item_meta[$prefix . 'recipient_email'][0])) {

                    if (is_serialized($product_item_meta[$prefix . 'recipient_email'][0])) { // for new orders
                        $recipient_email_field = maybe_unserialize($product_item_meta[$prefix . 'recipient_email'][0]);
                        $recipient_details['recipient_email'] = isset($recipient_email_field['value']) ? $recipient_email_field['value'] : '';
                    } else { // for old orders
                        $recipient_details['recipient_email'] = $product_item_meta[$prefix . 'recipient_email'][0];
                    }
                }

                //Get recipient message from orders
                if (!empty($product_item_meta[$prefix . 'recipient_message']) && !empty($product_item_meta[$prefix . 'recipient_message'][0])) {

                    if (is_serialized($product_item_meta[$prefix . 'recipient_message'][0])) { // for new orders
                        $recipient_msg_field = maybe_unserialize($product_item_meta[$prefix . 'recipient_message'][0]);
                        $recipient_details['recipient_message'] = isset($recipient_msg_field['value']) ? $recipient_msg_field['value'] : '';
                    } else { // for old orders
                        $recipient_details['recipient_message'] = $product_item_meta[$prefix . 'recipient_message'][0];
                    }
                }

                //Get pdf template from orders
                if (!empty($product_item_meta[$prefix . 'pdf_template_selection']) && !empty($product_item_meta[$prefix . 'pdf_template_selection'][0])) {

                    if (is_serialized($product_item_meta[$prefix . 'pdf_template_selection'][0])) { // for new orders
                        $pdf_temp_selection_field = maybe_unserialize($product_item_meta[$prefix . 'pdf_template_selection'][0]);
                        $recipient_details['pdf_template_selection'] = isset($pdf_temp_selection_field['value']) ? $pdf_temp_selection_field['value'] : '';
                    } else { // for old orders
                        $recipient_details['pdf_template_selection'] = $product_item_meta[$prefix . 'pdf_template_selection'][0];
                    }
                }

                //Get pdf template from orders
                if (!empty($product_item_meta[$prefix . 'recipient_giftdate']) && !empty($product_item_meta[$prefix . 'recipient_giftdate'][0])) {

                    if (is_serialized($product_item_meta[$prefix . 'recipient_giftdate'][0])) { // for new orders
                        $recipient_date_field = maybe_unserialize($product_item_meta[$prefix . 'recipient_giftdate'][0]);
                        $recipient_details['recipient_giftdate'] = isset($recipient_date_field['value']) ? $recipient_date_field['value'] : '';
                    } else { // for old orders
                        $recipient_details['recipient_giftdate'] = $product_item_meta[$prefix . 'recipient_giftdate'][0];
                    }
                }
            } else {

                // Recipient Data Columns
                $recipient_data_cols = woo_vou_voucher_recipient_details();

                foreach( $recipient_data_cols as $recipient_col_key => $recipient_col_val ) {

                    //Get recipient name from orders
                    if (!empty($product_item_meta[$prefix.$recipient_col_key])) {
    
                        if (isset($product_item_meta[$prefix.$recipient_col_key][0]) && is_serialized($product_item_meta[$prefix.$recipient_col_key][0])) { // for new orders
                            $recipient_col_field = maybe_unserialize($product_item_meta[$prefix.$recipient_col_key][0]);
                            $recipient_details[$recipient_col_key] = isset($recipient_col_field['value']) ? $recipient_col_field['value'] : '';
                        } else { // for old orders
                            $recipient_details[$recipient_col_key] = $product_item_meta[$prefix . $recipient_col_key]['value'];
                        }
                    }
                }

                //Get pdf template from orders
                if (!empty($product_item_meta[$prefix . 'pdf_template_selection'])) {

                    if (isset($product_item_meta[$prefix . 'pdf_template_selection'][0]) && is_serialized($product_item_meta[$prefix . 'pdf_template_selection'][0])) { // for new orders
                        $pdf_temp_selection_field = maybe_unserialize($product_item_meta[$prefix . 'pdf_template_selection'][0]);
                        $recipient_details['pdf_template_selection'] = isset($pdf_temp_selection_field['value']) ? $pdf_temp_selection_field['value'] : '';
                    } else { // for old orders
                        $recipient_details['pdf_template_selection'] = $product_item_meta[$prefix . 'pdf_template_selection']['value'];
                    }
                }
            }
        }

        return apply_filters('woo_vou_get_recipient_data', $recipient_details, $product_item_meta);
    }

   /**
     * Display Product Item Name
     * 
     * Handles to display product item name
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.2.2
     */
    public function woo_vou_display_product_item_name($item = array(), $product = array(), $filter_recipient = false) {

        // Prefix
        $prefix = WOO_VOU_META_PREFIX;

        // Get date format from global setting
        $date_format = get_option( 'date_format' );

        // Get product item meta
        $product_item_meta = isset($item['item_meta']) ? $item['item_meta'] : array();
        $product_item_name = $product_id = '';

        // To make compatible with previous versions of 3.0.0
        if ( version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1 ) {
            $product_id = isset($product_item_meta['_product_id']) ? $product_item_meta['_product_id'] : '';
        } else {
            if( !empty( $product ) && is_object( $product ) ) {
                if ( $product->is_type( 'variable' ) || $product->is_type( 'variation' ) ) {
                    $product_id = $product->get_parent_id();                        
                } else {
                    $product_id = $product->get_id();                            
                }
            }
        }
        $product_recipient_labels = $this->woo_vou_get_product_recipient_meta($product_id);

        if (!empty($product_item_meta)) { // if not empty product meta
            // this is added due to skip depricted function get_formatted_legacy from woocommerce
            if (!defined('DOING_AJAX')) {
                define('DOING_AJAX', true);
            }

            // To make compatible with previous versions of 3.0.0
            if( version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1 ) {

                //Item meta object            
                $item_meta_object = new WC_Order_Item_Meta($product_item_meta);

                //Get product variations
                $product_variations = $item_meta_object->get_formatted();                
            } else {

                // get meta
                $meta_data = $item->get_formatted_meta_data();
                $product_variations = array();

            }            

            if ($filter_recipient) { // If you want to hide some of variations using filter
                $product_variations = apply_filters('woo_vou_hide_recipient_variations', $product_variations, $product_item_meta);
            } else { // Displaying old order variations

                // Get all Recipient Columns
                $recipient_columns  = woo_vou_voucher_recipient_details();

                // Looping on all recipient columns
                foreach( $recipient_columns as $recipient_key => $recipient_val ) {

                    //Get recipient name from old orders
                    if (!empty($product_item_meta[$prefix.$recipient_key]) && !empty($product_item_meta[$prefix.$recipient_key]['value'])) {

                        $recipient_name_lbl = $product_recipient_labels[$recipient_key.'_label'];
                        $column_val = $product_item_meta[$prefix.$recipient_key]['value'];

                        if( !empty( $recipient_val ) && is_array( $recipient_val )
                            && array_key_exists( 'type', $recipient_val ) ) {

                            if( $recipient_val['type'] == 'date' ) {
                                $column_val = date( $date_format, strtotime( $product_item_meta[$prefix . $recipient_key]['value'] ) );
                            } else if ( $recipient_val['type'] == 'textarea' ) {
                                $column_val = nl2br( $product_item_meta[$prefix . $recipient_key]['value'] );
                            }
                        }

                        $product_variations[$recipient_name_lbl] = array(
                            'label' => $recipient_name_lbl,
                            'value' => $column_val
                        );
                    }
                }

                //Get recipient message from old orders
                if (!empty($product_item_meta[$prefix . 'pdf_template_selection']) && !empty($product_item_meta[$prefix . 'pdf_template_selection']['value'])) {

                    $pdf_temp_selection_lbl = $product_recipient_labels['pdf_template_selection_label'];
                    $pdf_temp_selection_val = get_the_title( $product_item_meta[$prefix . 'pdf_template_selection']['value'] );

                    $product_variations[$pdf_temp_selection_lbl] = array(
                        'label' => $pdf_temp_selection_lbl,
                        'value' => $pdf_temp_selection_val
                    );
                }

                //Get delivery method from old orders
                if (!empty($product_item_meta[$prefix . 'delivery_method']) && !empty($product_item_meta[$prefix . 'delivery_method']['value'])) {

                    $delivery_meth_selection_lbl = $product_recipient_labels['recipient_delivery_label'];

                    $product_variations[$delivery_meth_selection_lbl] = array(
                        'label' => $delivery_meth_selection_lbl,
                        'value' => $product_item_meta[$product_item_meta[$prefix . 'delivery_method']['label']]
                    );
                }
            }

            // Hide variation from item
            $product_variations = apply_filters('woo_vou_hide_item_variations', $product_variations, $product_item_meta);

            // Create variations Html
            if (!empty($product_variations)) {

                //variation display format
                $variation_param_string = apply_filters('woo_vou_variation_name_string_format', '<br /><strong>%1$s</strong>: %2$s', $product_item_meta);

                foreach ($product_variations as $product_variation) {
                    $product_item_name .= sprintf($variation_param_string, $product_variation['label'], $product_variation['value']);
                }
            }
        }

        return apply_filters('woo_vou_display_product_item_name', $product_item_name, $product_item_meta, $filter_recipient);
    }

    /**
     * Vendor Sale Notification
     * 
     * Handles to send vendor sale notification
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.3.5
     */
    public function woo_vou_vendor_sale_notification($product_id = '', $variation_id = '', $item_key = '', $item_data = '', $order_id = '', $order = array()) {

        global $woo_vou_voucher;

        // Get prefix
        $prefix = WOO_VOU_META_PREFIX;

        // Declare variables
        $vendor_users = array();

        // Get product id / variation id
        $data_id        = !empty($variation_id) ? $variation_id : $product_id;

        // Get vendor sale and secondary enabled option
        $vendor_sale_settings = get_option('woocommerce_woo_vou_vendor_sale_notification_settings');
        $global_vendor_sale_enabled = isset($vendor_sale_settings['enabled']) ? $vendor_sale_settings['enabled'] : '';
        $global_sec_vendor_sale_enabled = isset($vendor_sale_settings['enabled_sec_vendor_notification']) ? $vendor_sale_settings['enabled_sec_vendor_notification'] : '';

        // Vendor email notification code
        $primary_vendor_user = get_post_meta($product_id, $prefix . 'vendor_user', true);
        $sec_vendor_users = get_post_meta($product_id, $prefix . 'sec_vendor_users', true);

        // Get user vendor sale email notification settings
        $vendor_sale_email_notification = get_user_meta($primary_vendor_user, $prefix . 'enable_vendor_sale_email_notification', true);

        // Check whether to email to primary vendor
        if ($global_vendor_sale_enabled == "yes") { // If global settings is turned on then add user to array
            $vendor_users[] = $primary_vendor_user;
        } else { // Else check for user meta settings
            if (!empty($vendor_sale_email_notification)) { // Check whether user meta settings is ticked
                $vendor_users[] = $primary_vendor_user; // If yes then add id in vendor user email
            }
        }

        // Check whether to email to secondary vendor
        if ($global_sec_vendor_sale_enabled == "yes" && !empty($sec_vendor_users)) { // If global settings is turned on than add all user to array
            $vendor_users = array_merge($vendor_users, $sec_vendor_users);
        } else { // If not checked than check user level settings
            if (!empty($sec_vendor_users)) { // If Secodary Vendor Users are not empty
                foreach ($sec_vendor_users as $sec_vendor_user) { // Loop on all secondary vendor user
                    $sec_vendor_email_notification = get_user_meta($sec_vendor_user, $prefix . 'enable_vendor_sale_email_notification', true); // Get user level setting
                    if (!empty($sec_vendor_email_notification)) { // If user meta is enable
                        array_push($vendor_users, $sec_vendor_user); // Add user to array
                    }
                }
            }
        }

        $vendor_users = apply_filters('woo_vou_vendor_email_array', $vendor_users, $product_id);

        if (!empty($vendor_users)) { // Check vendor user is not empty
            // get cart detail
            $cart_details = new Wc_Order($order_id);
            // Get order date
            $order_date = $this->woo_vou_get_order_date_from_order($cart_details); // order date
            $order_date = !empty( $order_date ) ? $this->woo_vou_get_date_format( $order_date, true ) : '';

            //Get product from Item ( It is required otherwise multipdf voucher link not work and global $woo_vou_item_id will not work )
            $_product = apply_filters('woocommerce_order_item_product', $cart_details->get_product_from_item($item_data), $item_data);
            $download_file_data = $this->woo_vou_get_item_downloads_from_order($cart_details, $item_data);
            $i = 0;
            $links = array();
            foreach ($download_file_data as $key => $download_file) {

                $check_key = strpos($key, 'woo_vou_pdf_');

                if (!empty($download_file) && $check_key !== false) {

                    $attach_keys[] = $key;
                    $i++;
                    $links[] = '<small><a href="' . esc_url($download_file['download_url']) . '">' . sprintf(esc_html__('Download file%s', 'woovoucher'), ( count($download_file_data) > 1 ? ' ' . $i . ': ' : ': ')) . esc_html($download_file['name']) . '</a></small>';
                }
            }

            // get voucher link
            $voucher_link = '<br/>' . implode('<br/>', $links);
            $product_title = apply_filters( 'woo_vou_product_title_vendor_sale_email', get_the_title($product_id), $product_id, $variation_id, $item_key, $order_id );
            $site_name = get_bloginfo('name');
            $product_details = $this->woo_vou_get_product_details($order_id);
            $variation_data = $this->woo_vou_get_variation_data($order, $item_key);
            $product_title = $product_title . $variation_data;
            $product_price = !empty($product_details[$data_id]['product_formated_price']) ? $product_details[$data_id]['product_formated_price'] : '';
            $product_quantity = !empty($product_details[$data_id]['product_quantity']) ? $product_details[$data_id]['product_quantity'] : '';

            $billing_address_details    = $this->woo_vou_get_buyer_information($order_id); // Get billing information from order
            $shipping_address_details   = $this->woo_vou_get_buyer_shipping_information($order_id); // Get shipping information from order
            $first_name                 = !empty($billing_address_details['first_name']) ? $billing_address_details['first_name'] : ( !empty($shipping_address_details['first_name']) ? $shipping_address_details['first_name'] : '' ); // Get first name
            $last_name                  = !empty($billing_address_details['last_name']) ? $billing_address_details['last_name'] : ( !empty($shipping_address_details['last_name']) ? $shipping_address_details['last_name'] : '' ); // Get last name
            $address_1                  = !empty($billing_address_details['address_1']) ? $billing_address_details['address_1'] : ( !empty($shipping_address_details['address_1']) ? $shipping_address_details['address_1'] : '' ); // Get address 1
            $address_2                  = !empty($billing_address_details['address_2']) ? $billing_address_details['address_2'] : ( !empty($shipping_address_details['address_2']) ? $shipping_address_details['address_2'] : '' ); // Get address 2
            $postcode                   = !empty($billing_address_details['postcode']) ? $billing_address_details['postcode'] : ( !empty($shipping_address_details['postcode']) ? $shipping_address_details['postcode'] : '' ); // Get postcode
            $city                       = !empty($billing_address_details['city']) ? $billing_address_details['city'] : ( !empty($shipping_address_details['city']) ? $shipping_address_details['city'] : '' ); // Get city
            
            $customer_name = $first_name . ' ' . $last_name;
            $shipping_address = $address_1 . ' ' . $address_2;

            //Get voucher code from item meta
            $allcodes       = wc_get_order_item_meta($item_key, $prefix . 'codes');
            // Get first voucher code when multiple voucher codes
            $voucher_codes  = explode( ',', $allcodes );
            $voucher_code   = $voucher_codes[0];
            // Get voucher code id and vouche expiry data
            $voucodeid      = woo_vou_get_voucodeid_from_voucode( $voucher_code );
            $vou_exp_date   = get_post_meta($voucodeid, $prefix . 'exp_date', true);
            $vou_exp_date   = !empty( $vou_exp_date ) ? $this->woo_vou_get_date_format( $vou_exp_date, true ) : '';

            // Get Recipient name and email
            $vou_recipient_name  = wc_get_order_item_meta($item_key, $prefix . 'recipient_name');
            $vou_recipient_email = wc_get_order_item_meta($item_key, $prefix . 'recipient_email');
            $recipient_name      = ( isset($vou_recipient_name['value']) ) ? $vou_recipient_name['value'] : '' ;
            $recipient_email     = ( isset($vou_recipient_email['value']) ) ? $vou_recipient_email['value'] : '' ;

            foreach ($vendor_users as $vendor_user) { // Loop on all vendor users array
                $vendor_user_data = get_user_by('id', $vendor_user);
                $vendor_email       = isset($vendor_user_data->user_email) ? $vendor_user_data->user_email : '';
                $vendor_first_name  = isset($vendor_user_data->first_name) ? $vendor_user_data->first_name : '';
                $vendor_last_name   = isset($vendor_user_data->last_name) ? $vendor_user_data->last_name : '';

                //Get All Data for vendor notify
                $vendor_data = array(
                    'site_name'         => $site_name,
                    'product_title'     => $product_title,
                    'product_quantity'  => $product_quantity,
                    'voucher_code'      => $allcodes,
                    'product_price'     => $product_price,
                    'vendor_email'      => $vendor_email,
                    'vendor_first_name' => $vendor_first_name,
                    'vendor_last_name'  => $vendor_last_name,
                    'order_id'          => $order_id,
                    'voucher_link'      => $voucher_link,
                    'customer_name'     => $customer_name,
                    'shipping_address'  => $shipping_address,
                    'shipping_postcode' => $postcode,
                    'shipping_city'     => $city,
                    'recipient_name'    => $recipient_name,
                    'recipient_email'   => $recipient_email,
                    'order_date'        => $order_date,
                    'vou_exp_date'      => $vou_exp_date,
                );

                //Fires when sale notify to vendor.
                do_action('woo_vou_vendor_sale_email', $vendor_data);
            }
        }
    }

    /**
     * Voucher Get Shipping Information
     * 
     * Handles to get Shipping information
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.3.6
     */
    public function woo_vou_get_buyer_shipping_information($order_id = '') {

        $shipping_address = array();

        if ($order_id) {

            // get shipping detail                  
            $order = wc_get_order($order_id);

            if (!empty($order)) {

                // if version is lower then 3.0.0
                if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1) {

                    $shipping_address = array(
                        'first_name'=> isset($order->shipping_first_name) ? $order->shipping_first_name : '',
                        'last_name' => isset($order->shipping_last_name) ? $order->shipping_last_name : '',
                        'company'   => isset($order->shipping_company) ? $order->shipping_company : '',
                        'address_1' => isset($order->shipping_address_1) ? $order->shipping_address_1 : '',
                        'address_2' => isset($order->shipping_address_2) ? $order->shipping_address_2 : '',
                        'city'      => isset($order->shipping_city) ? $order->shipping_city : '',
                        'state'     => isset($order->shipping_state) ? $order->shipping_state : '',
                        'postcode'  => isset($order->shipping_postcode) ? $order->shipping_postcode : '',
                        'country'   => isset($order->shipping_country) ? $order->shipping_country : '',
                    );
                } else {
                    $shipping_address = array(
                        'first_name'=> $order->get_shipping_first_name(),
                        'last_name' => $order->get_shipping_last_name(),
                        'company'   => $order->get_shipping_company(),
                        'address_1' => $order->get_shipping_address_1(),
                        'address_2' => $order->get_shipping_address_2(),
                        'city'      => $order->get_shipping_city(),
                        'state'     => $order->get_shipping_state(),
                        'postcode'  => $order->get_shipping_postcode(),
                        'country'   => $order->get_shipping_country(),
                    );
                }
            }
        }

        return apply_filters('woo_vou_get_buyer_shipping_information', $shipping_address, $order);
    }

    /**
     * Voucher Get Buyer Information
     * 
     * Handles to get buyer information
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.3.6
     */
    public function woo_vou_get_buyer_information($order_id = '') {

        $buyer_details = array();
        $order = array();

        if ($order_id) {

            // get order detail
            $order = wc_get_order($order_id);
            if (!empty($order)) {

                // if version is lower then 3.0.0
                if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1) {
                    // buyer's details array
                    $buyer_details = array(
                        'first_name'=> $order->billing_first_name,
                        'last_name' => $order->billing_last_name,
                        'address_1' => $order->billing_address_1,
                        'address_2' => $order->billing_address_2,
                        'city'      => $order->billing_city,
                        'state'     => $order->billing_state,
                        'postcode'  => $order->billing_postcode,
                        'country'   => $order->billing_country,
                        'email'     => $order->billing_email,
                        'phone'     => $order->billing_phone
                    );
                } else {
                    // buyer's details array
                    $buyer_details = array(
                        'first_name'=> $order->get_billing_first_name(),
                        'last_name' => $order->get_billing_last_name(),
                        'address_1' => $order->get_billing_address_1(),
                        'address_2' => $order->get_billing_address_2(),
                        'city'      => $order->get_billing_city(),
                        'state'     => $order->get_billing_state(),
                        'postcode'  => $order->get_billing_postcode(),
                        'country'   => $order->get_billing_country(),
                        'email'     => $order->get_billing_email(),
                        'phone'     => $order->get_billing_phone()
                    );
                }
            }
        }

        return apply_filters('woo_vou_get_buyer_information', $buyer_details, $order);
    }

    /**
     * Get Item Data From Voucher Code
     * 
     * Handles to get voucher data using
     * voucher codes from order items
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.3.6
     */
    public function woo_vou_get_item_data_using_voucher_code($order_items, $voucode) {

        // Get prefix
        $prefix = WOO_VOU_META_PREFIX;

        //initilize item
        $return_item = array('item_id' => '', 'item_data' => array());

        if (!empty($order_items)) {//if items are not empty
            foreach ($order_items as $item_id => $item) {

                $voucher_codes = wc_get_order_item_meta($item_id, $prefix . 'codes');

                //vouchers data of pdf
                $voucher_codes = !empty($voucher_codes) ? explode(',', $voucher_codes) : array();
                $voucher_codes = array_map('trim', $voucher_codes);
                $voucher_codes = array_map('strtolower', $voucher_codes);

                $check_code = trim($voucode);
                $check_code = strtolower($voucode);

                if (in_array($check_code, $voucher_codes)) {

                    $return_item['item_id'] = $item_id;
                    $return_item['item_data'] = $item;
                    break;
                }
            }
        }

        return apply_filters('woo_vou_get_item_data_using_voucher_code', $return_item, $order_items, $voucode);
    }

    /**
     * Get Item Data From Item ID
     * 
     * Handles to get voucher data using
     * voucher codes from order items
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.3.6
     */
    public function woo_vou_get_item_data_using_item_key($order_items, $item_key) {

        $prefix = WOO_VOU_META_PREFIX;

        //initilize item
        $return_item = array('item_id' => '', 'item_data' => array());

        if (!empty($order_items)) {//if items are not empty
            foreach ($order_items as $item_id => $item) {

                if ($item_key == $item_id) {

                    $return_item['item_id'] = $item_id;
                    $return_item['item_data'] = $item;
                    break;
                }
            }
        }

        return apply_filters('woo_vou_get_item_data_using_item_key', $return_item, $order_items, $item_key);
    }

    /**
     * Get a download from the database.
     * 
     * Handles to get a download from the database.
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.3.7
     */
    public function woo_vou_get_download_data($args = array()) {

        global $wpdb;

        $query = "SELECT * FROM " . $wpdb->prefix . "woocommerce_downloadable_product_permissions ";
        $query .= "WHERE user_email = %s ";
        $query .= "AND order_key = %s ";
        $query .= "AND product_id = %s ";

        if ($args['download_id']) {
            $query .= "AND download_id = %s ";
        }

        return $wpdb->get_row($wpdb->prepare($query, array($args['email'], $args['order_key'], $args['product_id'], $args['download_id'])));
    }

    /**
     * Log the download + increase counts
     * 
     * Handles to Log the download + increase counts
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.3.7
     */
    public function woo_vou_count_download($download_data) {

        global $wpdb;

        $wpdb->update(
                $wpdb->prefix . "woocommerce_downloadable_product_permissions", array(
            'download_count' => $download_data->download_count + 1,
            'downloads_remaining' => $download_data->downloads_remaining > 0 ? $download_data->downloads_remaining - 1 : $download_data->downloads_remaining,
                ), array(
            'permission_id' => absint($download_data->permission_id),
                ), array('%d', '%s'), array('%d')
        );
    }

    /**
     * Update product stock
     * 
     * Handles to Update Product Stock
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.4.0
     */
    public function woo_vou_update_product_stock($product_id = '', $variation_id = '', $voucher_codes = array()) {

        //Total avialable voucher code
        $avail_total_codes = count($voucher_codes);

        if (!empty($variation_id)) {
            wc_update_product_stock($variation_id, $avail_total_codes);
        } else {
            wc_update_product_stock($product_id, $avail_total_codes);
        }
    }

    /**
     * Get image attachment_id from attachment_url
     *
     * @package WooCommerce - PDF Vouchers
     * @since 2.8.2
     */
    public function woo_vou_get_attachment_id_from_url($url) {

        $attachment_id = 0;
        $dir = wp_upload_dir();

        // If URL contains upload directory's path
        if (false !== strpos($url, $dir['baseurl'] . '/')) { // Is URL in uploads directory?
            // Get basename for file
            $file = basename($url);

            // Create query args
            $query_args = array(
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'fields' => 'ids',
                'meta_query' => array(
                    array(
                        'value' => $file,
                        'compare' => 'LIKE',
                        'key' => '_wp_attachment_metadata',
                    ),
                )
            );

            $query = new WP_Query($query_args);

            if ($query->have_posts()) {

                foreach ($query->posts as $post_id) {

                    $meta = wp_get_attachment_metadata($post_id);
                    $original_file = basename($meta['file']);
                    $cropped_image_files = wp_list_pluck($meta['sizes'], 'file');

                    if ($original_file === $file || in_array($file, $cropped_image_files)) {

                        $attachment_id = $post_id;
                        break;
                    }
                }
            }
        }

        // Return attachment_id
        return $attachment_id;
    }

    /**
     * Habdles to ger voucher start and end date
     *
     * @param  string $product_id - product id
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.5.0
     */
    public function woo_vou_get_minmax_date_from_product( $product_id ){

        // Get Prefix
        $prefix     = WOO_VOU_META_PREFIX;

        // Declaring variables
        $vou_minmax_date    = array();
        $vou_min_date       = $vou_max_date = '';
        $exp_type           = '';

        $expiration_date_type = get_post_meta( $product_id, $prefix.'exp_type', true ); // Get expiration date type

        // If expiration date type is specific date
        if(!empty($expiration_date_type) && $expiration_date_type == 'specific_date') { // If expiration date is set to specific date

            $vou_exp_start_date = get_post_meta($product_id, $prefix.'start_date', true); // Get voucher start date
            $vou_exp_end_date   = get_post_meta($product_id, $prefix.'exp_date', true); // Get voucher expiry date
        } else if(!empty($expiration_date_type) && $expiration_date_type == 'based_on_purchase') { // If expiration date is set to based on purchase

            $vou_exp_days_diff_meta = get_post_meta($product_id, $prefix.'days_diff', true); // Get days difference
            if($vou_exp_days_diff_meta == 'cust') { // If days difference is custom

                $vou_exp_days_diff = get_post_meta($product_id, $prefix.'custom_days', true); // Get custom days from meta
            } else {

                $vou_exp_days_diff = $vou_exp_days_diff_meta;
            }
        } else if( ($expiration_date_type == 'default')) { // If expiration date is set to default

            $exp_type = get_option('vou_exp_type'); //get expiration type 

            if ($exp_type == 'specific_date') { //If expiry type specific date

                $vou_exp_start_date = get_option('vou_start_date'); // start date
                $vou_exp_start_date = !empty( $vou_exp_start_date ) ? date('Y-m-d H:i:s', strtotime($vou_exp_start_date)) : ''; // format start date
                $vou_exp_end_date   = get_option('vou_exp_date'); // expiration date
                $vou_exp_end_date   = !empty( $vou_exp_end_date ) ? date('Y-m-d H:i:s', strtotime($vou_exp_end_date)) : ''; // format exp date
            } elseif ($exp_type == 'based_on_purchase') { //If expiry type based in purchase
                //get days difference
                $days_diff = get_option('vou_days_diff');

                if ($days_diff == 'cust') {
                    $custom_days = get_option('vou_custom_days');
                    $custom_days = isset($custom_days) ? $custom_days : '';
                    if (!empty($custom_days)) {

                        $add_days = '+' . $custom_days . ' days';
                        $vou_exp_end_date = date('Y-m-d H:i:s', strtotime( $add_days ));
                    } else {

                        $vou_exp_end_date = date('Y-m-d H:i:s', current_time('timestamp'));
                    }
                } else {
                    $custom_days        = $days_diff;
                    $add_days           = '+' . $custom_days . ' days';
                    $vou_exp_end_date   = date('Y-m-d H:i:s', strtotime( $add_days ));
                }
            }
        }

        $date_format  = apply_filters('woo_vou_giftdate_start_end_date_format', 'm/d/Y');
        $vou_min_date = !empty($vou_exp_start_date) ? date($date_format, strtotime($vou_exp_start_date)) : (!empty( $vou_exp_days_diff) ? date($date_format) : 0); // Format Voucher start date
        $vou_max_date = !empty($vou_exp_end_date) ? date($date_format, strtotime($vou_exp_end_date)) : (!empty( $vou_exp_days_diff) ? date($date_format, strtotime("+".$vou_exp_days_diff." days")) : ''); // Format Voucher expiry date

        // Get global time to send Gift Notification email
        $vou_gift_notification_time = get_option('vou_gift_notification_time');

        // If gift notification time is not empty and minimum date is less than current date
        if( !empty( $vou_gift_notification_time ) && ( strtotime('today') >= strtotime( $vou_min_date ) ) ) {

            $current_hour = current_time('G'); // Get current hour

            // If current date is selected then email sent instantlt so not needed this code
            // If notification time is passed or it is same as current hour
            
            if ( strtotime('today') >= strtotime( $vou_min_date ) ) {

                $vou_min_date = current_time($date_format); // Say minimum date as next day
            }
        }

        if( $expiration_date_type == 'specific_date' || $exp_type == 'specific_date' ) {
            $vou_min_date = current_time($date_format);
        }

        $vou_minmax_date['vou_min_date'] = $vou_min_date;
        $vou_minmax_date['vou_max_date'] = $vou_max_date;

        return $vou_minmax_date;
    }



        /**
     * Habdles to ger voucher start and end date
     *
     * @param  string $product_id - product id
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.5.0
     */
    public function woo_vou_get_minmax_date_from_product_variation( $product_id ){

        // Get Prefix
        $prefix     = WOO_VOU_META_PREFIX;

        // Declaring variables
        $vou_minmax_date    = array();
        $vou_min_date       = $vou_max_date = '';
        $exp_type           = '';

        $expiration_date_type = get_post_meta( $product_id, $prefix.'variable_voucher_expiration_date_type', true ); // Get expiration date type

        // If expiration date type is specific date
        if(!empty($expiration_date_type) && $expiration_date_type == 'specific_date') { // If expiration date is set to specific date

            $vou_exp_start_date = get_post_meta($product_id, $prefix.'variable_voucher_expiration_start_date', true); // Get voucher start date
            $vou_exp_end_date   = get_post_meta($product_id, $prefix.'variable_voucher_expiration_end_date', true); // Get voucher expiry date
        } else if(!empty($expiration_date_type) && $expiration_date_type == 'based_on_purchase') { // If expiration date is set to based on purchase

            $vou_exp_days_diff_meta = get_post_meta($product_id, $prefix.'variable_voucher_day_diff', true); // Get days difference
            if($vou_exp_days_diff_meta == 'cust') { // If days difference is custom

                $vou_exp_days_diff = get_post_meta($product_id, $prefix.'variable_voucher_expiration_custom_day', true); // Get custom days from meta
            } else {

                $vou_exp_days_diff = $vou_exp_days_diff_meta;
            }
        } else if( ($expiration_date_type == 'default')) { // If expiration date is set to default

            $exp_type = get_option('vou_exp_type'); //get expiration type 

            if ($exp_type == 'specific_date') { //If expiry type specific date

                $vou_exp_start_date = get_option('vou_start_date'); // start date
                $vou_exp_start_date = !empty( $vou_exp_start_date ) ? date('Y-m-d H:i:s', strtotime($vou_exp_start_date)) : ''; // format start date
                $vou_exp_end_date   = get_option('vou_exp_date'); // expiration date
                $vou_exp_end_date   = !empty( $vou_exp_end_date ) ? date('Y-m-d H:i:s', strtotime($vou_exp_end_date)) : ''; // format exp date
            } elseif ($exp_type == 'based_on_purchase') { //If expiry type based in purchase
                //get days difference
                $days_diff = get_option('vou_days_diff');

                if ($days_diff == 'cust') {
                    $custom_days = get_option('vou_custom_days');
                    $custom_days = isset($custom_days) ? $custom_days : '';
                    if (!empty($custom_days)) {

                        $add_days = '+' . $custom_days . ' days';
                        $vou_exp_end_date = date('Y-m-d H:i:s', strtotime( $add_days ));
                    } else {

                        $vou_exp_end_date = date('Y-m-d H:i:s', current_time('timestamp'));
                    }
                } else {
                    $custom_days        = $days_diff;
                    $add_days           = '+' . $custom_days . ' days';
                    $vou_exp_end_date   = date('Y-m-d H:i:s', strtotime( $add_days ));
                }
            }
        }

        $date_format  = apply_filters('woo_vou_giftdate_start_end_date_format', 'm/d/Y');
        $vou_min_date = !empty($vou_exp_start_date) ? date($date_format, strtotime($vou_exp_start_date)) : (!empty( $vou_exp_days_diff) ? date($date_format) : 0); // Format Voucher start date
        $vou_max_date = !empty($vou_exp_end_date) ? date($date_format, strtotime($vou_exp_end_date)) : (!empty( $vou_exp_days_diff) ? date($date_format, strtotime("+".$vou_exp_days_diff." days")) : ''); // Format Voucher expiry date

        // Get global time to send Gift Notification email
        $vou_gift_notification_time = get_option('vou_gift_notification_time');

        // If gift notification time is not empty and minimum date is less than current date
        if( !empty( $vou_gift_notification_time ) && ( strtotime('today') >= strtotime( $vou_min_date ) ) ) {

            $current_hour = current_time('G'); // Get current hour

            // If current date is selected then email sent instantlt so not needed this code
            // If notification time is passed or it is same as current hour
            
            if ( strtotime('today') >= strtotime( $vou_min_date ) ) {

                $vou_min_date = current_time($date_format); // Say minimum date as next day
            }
        }

        if( $expiration_date_type == 'specific_date' || $exp_type == 'specific_date' ) {
            $vou_min_date = current_time($date_format);
        }

        $vou_minmax_date['vou_min_date'] = $vou_min_date;
        $vou_minmax_date['vou_max_date'] = $vou_max_date;

        return $vou_minmax_date;
    }

    /**
     * Check date
     *
     * @param  string $date - date
     *
     * @package WooCommerce - PDF Vouchers
     * @since 2.9.3
     */
    public function woo_vou_check_date($date, $product_id='') {

        // Get Prefix
        $prefix     = WOO_VOU_META_PREFIX;

        // Declare variables
        $result     = array('error' => 'false');
        $error      = false;
        $wc_date    = date_i18n( 'm/d/Y', strtotime( $date ) );
        $_date      = date( 'Ymd', strtotime( $date ) );

        // Validate date
        if ( empty( $wc_date ) ) {
            $result     = array('error' => true, 'error_type' => 'date_not_proper');
            $error      = true;
        }

        if ( strtotime($date) != strtotime($wc_date) ){
            $result     = array('error' => true, 'error_type' => 'date_not_proper');
            $error      = true;
        }

        // If there is no error then we will check min and max date
        if( !$error && $product_id ) {

            $min_max_date = $this->woo_vou_get_minmax_date_from_product($product_id);
            extract( $min_max_date );
            $_vou_min_date = !empty( $vou_min_date ) ? date( 'Ymd', strtotime( $vou_min_date ) ) : '';
            $_vou_max_date = !empty( $vou_max_date ) ? date( 'Ymd', strtotime( $vou_max_date ) ) : '';

            if( $_vou_min_date && ( $_date < $_vou_min_date ) ) {

                $result     = array(
                                        'error'         => true, 
                                        'error_type'    => 'min_date_not_proper',
                                        'vou_min_date'  => $vou_min_date,
                                        'vou_max_date'  => $vou_max_date
                                    );
            }

            if( $_vou_max_date && ( $_date > $_vou_max_date ) ) {

                $result     = array(
                                        'error'         => true, 
                                        'error_type'    => 'min_date_not_proper',
                                        'vou_min_date'  => $vou_min_date,
                                        'vou_max_date'  => $vou_max_date
                                    );
            }
        }

        if( $product_id ) {
            return $result;
        } else {
            return $error;
        }
    }


    /**
     * Check date
     *
     * @param  string $date - date
     *
     * @package WooCommerce - PDF Vouchers
     * @since 2.9.3
     */
    public function woo_vou_check_variation_date($date, $product_id='') {

        // Get Prefix
        $prefix     = WOO_VOU_META_PREFIX;

        // Declare variables
        $result     = array('error' => 'false');
        $error      = false;
        $wc_date    = date_i18n( 'm/d/Y', strtotime( $date ) );
        $_date      = date( 'Ymd', strtotime( $date ) );

        // Validate date
        if ( empty( $wc_date ) ) {
            $result     = array('error' => true, 'error_type' => 'date_not_proper');
            $error      = true;
        }

        if ( strtotime($date) != strtotime($wc_date) ){
            $result     = array('error' => true, 'error_type' => 'date_not_proper');
            $error      = true;
        }

        // If there is no error then we will check min and max date
        if( !$error && $product_id ) {

            $min_max_date = $this->woo_vou_get_minmax_date_from_product_variation($product_id);
            extract( $min_max_date );
            $_vou_min_date = !empty( $vou_min_date ) ? date( 'Ymd', strtotime( $vou_min_date ) ) : '';
            $_vou_max_date = !empty( $vou_max_date ) ? date( 'Ymd', strtotime( $vou_max_date ) ) : '';

            if( $_vou_min_date && ( $_date < $_vou_min_date ) ) {

                $result     = array(
                                        'error'         => true, 
                                        'error_type'    => 'min_date_not_proper',
                                        'vou_min_date'  => $vou_min_date,
                                        'vou_max_date'  => $vou_max_date
                                    );
            }

            if( $_vou_max_date && ( $_date > $_vou_max_date ) ) {

                $result     = array(
                                        'error'         => true, 
                                        'error_type'    => 'min_date_not_proper',
                                        'vou_min_date'  => $vou_min_date,
                                        'vou_max_date'  => $vou_max_date
                                    );
            }
        }

        if( $product_id ) {
            return $result;
        } else {
            return $error;
        }
    }

     /**
     * Handles to return order date from order
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.1.0
     */
    public function woo_vou_get_order_date_from_order($order){

        if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1) {

            // Get Order Date
            $order_date = isset($order->order_date) ? $order->order_date : '';
        } else {
    
            // get order date and check if its not empty then call date('c') function.
            // To resolve fatal error when creating order from backend
            $order_date = $order->get_date_created(); //->date('c');
            $order_date = !empty($order_date) ? $order->get_date_created()->date('c') : '';
        }
        
        return $order_date;
    }
    
    /**
     * Handles to return order payment method
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.1.0
     */
    public function woo_vou_get_payment_method_from_order($order){

        if ( version_compare( WOOCOMMERCE_VERSION, "3.0.0" ) == -1 ) {
            // get payment method
            $payment_method = isset( $order->payment_method_title ) ? $order->payment_method_title : '';
        } else {
            // get payment method title
            $payment_method = $order->get_payment_method_title();
        }
        
        return $payment_method;
    }
    
    /**
     * Handles to return order item downloads
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.1.0
     */
    public function woo_vou_get_item_downloads_from_order($order, $product_data){

        // To make compatible with previous versions of 3.0.0
        if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1) {
            //Get download files
            $download_file_data = $order->get_item_downloads($product_data);
        } else {
            //Get download files
            $download_file_data = $product_data->get_item_downloads();
        }

        $download_file_data = apply_filters( 'woo_vou_pdf_download_link_agrs',$download_file_data,$order);

        return $download_file_data;
    }
    
    /**
     * Handles to return product id from product
     * 
     * Returns parent product id, if variable product is passed
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.1.0
     */
    public function woo_vou_get_item_productid_from_product($_product){

        if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1) {
            $product_id = $_product->id;
        } else {
            if ( $_product->is_type( 'variable' ) || $_product->is_type( 'variation' ) ) {
                $product_id = $_product->get_parent_id();                        
            } else {
                $product_id = $_product->get_id();                            
            }
        }
        
        return $product_id;
    }
    
    /**
     * Handles to return coupon id from coupon
     * 
     * Returns coupon id from coupon
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.1.0
     */
    public function woo_vou_get_coupon_id_from_coupon($coupon){

        // Get coupon_id
        if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1)
            $coupon_id = $coupon->id;
        else
            $coupon_id = $coupon->get_id();
        
        return $coupon_id;
    }
    
    /**
     * Check license key is activated or not
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.2.5
     */
    public function woo_vou_is_activated() {

        $purchase_code = wpweb_get_plugin_purchase_code( WOO_VOU_PLUGIN_KEY );
        $email = wpweb_get_plugin_purchase_email( WOO_VOU_PLUGIN_KEY );
        
        if( !empty( $purchase_code ) && !empty( $email ) ) {
            return true;
        }
        return false;
    }

    /**
     * Handles to search and load products
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.2.5
     */
    public function woo_vou_search_products( $page=1, $search_by='' ){

        global $wpdb;

        $type          = '';
        $like_term     = '%' . $wpdb->esc_like( $search_by ) . '%';
        $post_types    = array( 'product', 'product_variation' );
        $post_statuses = current_user_can( 'edit_private_products' ) ? array( 'private', 'publish' ) : array( 'publish' );
        $type_join     = '';
        $type_where    = '';

        // Join on post meta to search only those products which have _woo_vou_enable checkbox enabled
        $type_join     = " LEFT JOIN {$wpdb->postmeta} postmeta_type_vou_enable ON posts.ID = postmeta_type_vou_enable.post_id ";
        $type_where    = " AND ( postmeta_type_vou_enable.meta_key = '_woo_vou_enable' AND postmeta_type_vou_enable.meta_value = 'yes' ) ";

        // Prepare and get query results
        $product_ids = $wpdb->get_col(
            $wpdb->prepare( "
                SELECT DISTINCT posts.ID FROM {$wpdb->posts} posts
                LEFT JOIN {$wpdb->postmeta} postmeta ON posts.ID = postmeta.post_id
                $type_join
                WHERE (
                    posts.post_title LIKE %s
                    OR posts.post_content LIKE %s
                    OR (
                        postmeta.meta_key = '_sku' AND postmeta.meta_value LIKE %s
                    )
                )
                AND posts.post_type IN ('" . implode( "','", $post_types ) . "')
                AND posts.post_status IN ('" . implode( "','", $post_statuses ) . "')
                $type_where
                ORDER BY posts.post_date_gmt ASC
                LIMIT 20 OFFSET %d
                ",
                $like_term,
                $like_term,
                $like_term,
                ($page-1)*20
            )
        );

        if ( is_numeric( $search_by ) ) {
            $post_id   = absint( $search_by );
            $post_type = get_post_type( $post_id );

            if ( 'product_variation' === $post_type || 'product' === $post_type ) {
                $product_ids[] = $post_id;
            }

            $product_ids[] = wp_get_post_parent_id( $post_id );
        }

        // Return product ids
        return wp_parse_id_list( $product_ids );
    }
}