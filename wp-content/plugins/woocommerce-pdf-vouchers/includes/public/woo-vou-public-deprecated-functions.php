<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;
    
    /**
 * Display Download Voucher Link
 * 
 * Handles to display product voucher link for user
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_downloadable_files_26_deprecated($downloadable_files, $product) {

    global $post, $vou_order, $woo_vou_item_id, $woo_vou_model, $woo_vou_voucher;

    $prefix = WOO_VOU_META_PREFIX;

    $pdf_downloadable_files = array();

    // Taking product/variation id
    $variation_id = $product->is_type('variation') ? woo_vou_get_product_variation_id($product) : woo_vou_get_product_id($product);

    $order_id = $woo_vou_model->woo_vou_get_orderid_for_page(); // Getting order id
    //Get Order id on shop_order page
    // this is called when we make order complete from the backend
    if (is_admin() && !empty($post->post_type) && $post->post_type == 'shop_order') {
        $order_id = isset($post->ID) ? $post->ID : '';
    }

    if (empty($order_id)) { // Return download files if order id not found
        return $downloadable_files;
    }

    if (empty($woo_vou_item_id)) {
        return $downloadable_files;
    }

    //Get vouchers download files
    $pdf_downloadable_files = $woo_vou_voucher->woo_vou_get_vouchers_download_key($order_id, $variation_id, $woo_vou_item_id);

    //Mearge existing download files with vouchers file
    if (!empty($downloadable_files)) {
        $downloadable_files = array_merge($downloadable_files, $pdf_downloadable_files);
    } else {
        $downloadable_files = $pdf_downloadable_files;
    }

    return apply_filters('woo_vou_downloadable_files', $downloadable_files, $product);
}

/**
 * Set Global Item ID For Voucher Key Generater
 * 
 * Handle to Set global item id for voucher key generater
 * For older version of woocommerce to add backward compatibity
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.0
 */
function woo_vou_set_global_item_id_26_deprecated($product, $item, $order) {

    global $woo_vou_item_id;

    //Get prefix
    $prefix = WOO_VOU_META_PREFIX;

    $product_item_meta = isset($item['item_meta']) ? $item['item_meta'] : array();

    //Get voucher codes
    $voucher_codes = isset($product_item_meta[$prefix . 'codes'][0]) ? $product_item_meta[$prefix . 'codes'][0] : '';

    if (!empty($voucher_codes)) {

        //Get order items
        $order_items = $order->get_items();

        if (!empty($order_items)) { // If order not empty
            // Check cart details
            foreach ($order_items as $item_id => $item) {

                //Get voucher codes
                $codes = wc_get_order_item_meta($item_id, $prefix . 'codes');

                if ($codes == $voucher_codes) {//If voucher code matches
                    $woo_vou_item_id = $item_id;
                    break;
                }
            }
        }
    }

    return $product;
}

/**
 * add cart item to the order.
 * 
 * Handles to add cart item to the order.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.0
 */
function woo_vou_add_order_item_meta_26_deprecated($item_id, $values) {
	
	global $woo_vou_model, $woo_vou_voucher;

    //Get prefix
    $prefix = WOO_VOU_META_PREFIX;

    //Initilize recipients labels
    $woo_vou_recipient_labels = array();

    //Get product ID
    $_product_id = isset($values['variation_id']) && !empty($values['variation_id']) ? $values['variation_id'] : $values['product_id'];

    $recipient_labels = $woo_vou_model->woo_vou_get_product_recipient_meta($_product_id);

    // Get voucher price 
    $voucher_price = woo_vou_get_voucher_price($item_id, $_product_id, $values);

    if (!empty($voucher_price)) { // Add voucher price in order item meta
        wc_add_order_item_meta($item_id, $prefix . 'voucher_price', $voucher_price);
    }

    if (!empty($values[$prefix.'recipient_name'])) {//Add recipient name field
        wc_add_order_item_meta($item_id, $prefix . 'recipient_name', array(
            'label' => $recipient_labels['recipient_name_lable'],
            'value' => $values[$prefix.'recipient_name']
        ));

        wc_add_order_item_meta($item_id, $recipient_labels['recipient_name_lable'], $values[$prefix.'recipient_name']);
    }

    if (!empty($values[$prefix.'recipient_email'])) {//Add recipient email field
        wc_add_order_item_meta($item_id, $prefix . 'recipient_email', array(
            'label' => $recipient_labels['recipient_email_label'],
            'value' => $values[$prefix.'recipient_email']
        ));

        wc_add_order_item_meta($item_id, $recipient_labels['recipient_email_label'], $values[$prefix.'recipient_email']);
    }

    if (!empty($values[$prefix.'recipient_message'])) {//Add recipient message field
        wc_add_order_item_meta($item_id, $prefix.'recipient_message', array(
            'label' => $recipient_labels['recipient_message_label'],
            'value' => $values[$prefix.'recipient_message']
        ));

        wc_add_order_item_meta($item_id, $recipient_labels['recipient_message_label'], $values[$prefix.'recipient_message']);
    }

    if (!empty($values[$prefix.'recipient_giftdate'])) {//Add recipient giftdate field
        wc_add_order_item_meta($item_id, $prefix.'recipient_giftdate', array(
            'label' => $recipient_labels['recipient_giftdate_label'],
            'value' => $values[$prefix.'recipient_giftdate']
        ));

        wc_add_order_item_meta($item_id, $recipient_labels['recipient_giftdate_label'], $values[$prefix.'recipient_giftdate']);
    }

    if (!empty($values[$prefix.'pdf_template_selection'])) {//Add pdf template selection field
        wc_add_order_item_meta($item_id, $prefix.'pdf_template_selection', array(
            'label' => $recipient_labels['pdf_template_selection_label'],
            'value' => $values[$prefix.'pdf_template_selection']
        ));

        // check if template display is anable or not
        $enable_display_template = woo_vou_enable_template_display_features();

        if ($enable_display_template) { // if enable template preview image display
            //pdf template preview image
            $pdf_template_preview_img = wp_get_attachment_url(get_post_thumbnail_id($values[$prefix.'pdf_template_selection']));

            if (empty($pdf_template_preview_img)) {
                $pdf_template_preview_img = WOO_VOU_IMG_URL . '/no-preview.png';
            }

            $pdf_template_preview_img_title = get_the_title($values[$prefix.'pdf_template_selection']);

            wc_add_order_item_meta($item_id, $recipient_labels['pdf_template_selection_label'], '<img src="' . esc_url($pdf_template_preview_img) . '" style="width:50px !important;height:50px !important;cursor:pointer;" title="' . $pdf_template_preview_img_title . '">');
        }
    }
}

/**
 * Hide recipient variation from product name field
 * 
 * Handle to hide recipient variation from product name field
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.0
 */
function woo_vou_hide_recipients_item_variations_26_deprecated($product_variations = array(), $product_item_meta = array()) {

	global $woo_vou_model, $woo_vou_voucher;

    $prefix = WOO_VOU_META_PREFIX;

    $recipient_string = '';

    //Get product ID
    $product_id = isset($product_item_meta['_product_id']) ? $product_item_meta['_product_id'] : '';

    //Get product recipient lables
    $product_recipient_lables = $woo_vou_model->woo_vou_get_product_recipient_meta($product_id);
    
    if (isset($product_item_meta[$prefix . 'recipient_name']) && !empty($product_item_meta[$prefix . 'recipient_name'][0])) {
        if (is_serialized($product_item_meta[$prefix . 'recipient_name'][0])) { // New recipient name field
            $recipient_name_fields = maybe_unserialize($product_item_meta[$prefix . 'recipient_name'][0]);
            $recipient_name_lable = isset($recipient_name_fields['label']) ? $recipient_name_fields['label'] : $product_recipient_lables['recipient_name_lable'];

            if (isset($product_variations[$recipient_name_lable])) {
                unset($product_variations[$recipient_name_lable]);
            }
        }
    }

    if (isset($product_item_meta[$prefix . 'recipient_email']) && !empty($product_item_meta[$prefix . 'recipient_email'][0])) {
        if (is_serialized($product_item_meta[$prefix . 'recipient_email'][0])) { // New recipient email field
            $recipient_email_fields = maybe_unserialize($product_item_meta[$prefix . 'recipient_email'][0]);
            $recipient_email_lable = isset($recipient_email_fields['label']) ? $recipient_email_fields['label'] : $product_recipient_lables['recipient_email_label'];

            if (isset($product_variations[$recipient_email_lable])) {
                unset($product_variations[$recipient_email_lable]);
            }
        }
    }

    if (isset($product_item_meta[$prefix . 'recipient_message']) && !empty($product_item_meta[$prefix . 'recipient_message'][0])) {
        if (is_serialized($product_item_meta[$prefix . 'recipient_message'][0])) { // New recipient message field
            $recipient_msg_fields = maybe_unserialize($product_item_meta[$prefix . 'recipient_message'][0]);
            $recipient_msg_lable = isset($recipient_msg_fields['label']) ? $recipient_msg_fields['label'] : $product_recipient_lables['recipient_message_label'];

            if (isset($product_variations[$recipient_msg_lable])) {
                unset($product_variations[$recipient_msg_lable]);
            }
        }
    }

    if (isset($product_item_meta[$prefix . 'pdf_template_selection']) && !empty($product_item_meta[$prefix . 'pdf_template_selection'][0])) {
        if (is_serialized($product_item_meta[$prefix . 'pdf_template_selection'][0])) { // New recipient message field
            $pdf_temp_selection_fields = maybe_unserialize($product_item_meta[$prefix . 'pdf_template_selection'][0]);
            $pdf_temp_selection_lable = isset($pdf_temp_selection_fields['label']) ? $pdf_temp_selection_fields['label'] : $product_recipient_lables['pdf_template_selection_label'];

            if (isset($product_variations[$pdf_temp_selection_lable])) {
                unset($product_variations[$pdf_temp_selection_lable]);
            }
        }
    }

    if (isset($product_item_meta[$prefix . 'recipient_giftdate']) && !empty($product_item_meta[$prefix . 'recipient_giftdate'][0])) {

        if (is_serialized($product_item_meta[$prefix . 'recipient_giftdate'][0])) { // New recipient date field
            $recipient_giftdate_selection_fields = maybe_unserialize($product_item_meta[$prefix . 'recipient_giftdate'][0]);
            $recipient_giftdate_selection_lable = isset($recipient_giftdate_selection_fields['label']) ? $recipient_giftdate_selection_fields['label'] : $product_recipient_lables['recipient_giftdate_label'];

            if (isset($product_variations[$recipient_giftdate_selection_lable])) {
                unset($product_variations[$recipient_giftdate_selection_lable]);
            }
        }
    }
    
    return $product_variations;
}