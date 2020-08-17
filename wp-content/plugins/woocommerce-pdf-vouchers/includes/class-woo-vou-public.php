<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Public Pages Class
 * 
 * Handles all the different features and functions
 * for the front end pages.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
class WOO_Vou_Public {

    public $model;
    public $voucher;

    public function __construct() {

        global $woo_vou_model, $woo_vou_voucher;

        $this->model = $woo_vou_model;
        $this->voucher = $woo_vou_voucher;
    }

    /**
     * Display Check Code Html
     * 
     * Handles to display check code html for user and admin
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    public function woo_vou_check_code_content() {

        global $current_user, $woo_vou_vendor_role;

        //Get User roles
        $user_roles = isset($current_user->roles) ? $current_user->roles : array();
        $user_role = array_shift($user_roles);

        // Get "Check Voucher Code for all logged in users" option
        $vou_enable_logged_user_check_voucher_code = get_option('vou_enable_logged_user_check_voucher_code');
        $vou_enable_logged_user_redeem_vou_code = get_option('vou_enable_logged_user_redeem_vou_code');

        //voucher admin roles
        $admin_roles = woo_vou_assigned_admin_roles();
        $currency = get_woocommerce_currency();
        ?>

        <table class="form-table woo-vou-check-code">
            <tr>
                <th>
                    <label for="woo_vou_voucher_code"><?php esc_html_e('Enter Voucher Code', 'woovoucher') ?></label>
                </th>
                <td>
                    <input type="text" id="woo_vou_voucher_code" name="woo_vou_voucher_code" value="" />
                    <input type="hidden" id="woo_vou_valid_voucher_code" name="woo_vou_valid_voucher_code" value="" />
                    <input type="button" id="woo_vou_check_voucher_code" name="woo_vou_check_voucher_code" class="button-primary" value="<?php esc_html_e('Check It', 'woovoucher') ?>" data-currency="<?php echo $currency; ?>"/>
                    <?php do_action('woo_vou_after_check_voucher_code_btn'); ?>
                    <div class="woo-vou-loader woo-vou-check-voucher-code-loader"><img src="<?php echo esc_url(WOO_VOU_IMG_URL); ?>/ajax-loader.gif"/></div>
                    <?php do_action('woo_vou_after_check_voucher_code_loader'); ?>
                    <div class="woo-vou-voucher-code-msg"></div>
                </td>
            </tr>
            <?php           
            if ((in_array($user_role, $admin_roles) || in_array($user_role, $woo_vou_vendor_role)) || (!in_array($user_role, $admin_roles) && !in_array($user_role, $woo_vou_vendor_role) && ($vou_enable_logged_user_check_voucher_code == 'yes') && ($vou_enable_logged_user_redeem_vou_code == 'yes') ) || apply_filters('woo_vou_access_redeem_btn_without_login', false)
            ) {// voucher admin can redeem all codes
              
                ?>
                <tr class="woo-vou-voucher-code-submit-wrap">
                    <th>
                    </th>
                    <td>
                        <?php echo apply_filters('woo_vou_voucher_code_submit', '<input type="submit" id="woo_vou_voucher_code_submit" name="woo_vou_voucher_code_submit" class="button-primary" value="' . esc_html__("Redeem", "woovoucher") . '"/>'); ?>
                        <div class="woo-vou-loader woo-vou-voucher-code-submit-loader"><img src="<?php echo esc_url(WOO_VOU_IMG_URL); ?>/ajax-loader.gif"/></div>
                    </td>
                </tr>
            <?php } do_action('woo_vou_inner_check_code_table'); ?>
        </table><?php
        do_action('woo_vou_after_check_code_content');
    }

    /**
     * This is used to ensure any required user input fields are supplied
     * 
     * Handles to This is used to ensure any required user input fields are supplied
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.0
     */
    public function woo_vou_add_to_cart_validation($valid, $product_id = '', $quantity = '', $variation_id = '', $variations = array(), $cart_item_data = array()) {

        // Get prefix
        $prefix = WOO_VOU_META_PREFIX;

        if (array_key_exists('woo_vou_is_ajax', $_POST) && $_POST['woo_vou_is_ajax']) {

            $response = array();
            $return_html = '';

            $product_id = $_POST['product_id'];
            $variation_id = array_key_exists('variation_id', $_POST) && !empty($_POST['variation_id']) ? $_POST['variation_id'] : '';
        }

        $_product_id = $variation_id ? $variation_id : $product_id;
        $product = wc_get_product($_product_id);

        // Voucher enable or not
        $voucher_enable = $this->voucher->woo_vou_check_enable_voucher($product_id, $variation_id);

        // Get recipient delivery details
        $product_delivery_meth = get_post_meta($product_id, $prefix . 'recipient_delivery', true);

        if ($voucher_enable) {//If voucher enable
            $_delivery_method = '';

            //Get product recipient meta setting
            $recipient_data = $this->model->woo_vou_get_product_recipient_meta($product_id);
            $recipient_columns = woo_vou_voucher_recipient_details();
            $individual_recipient_details = !empty($recipient_data['individual_recipient_details']) ? $recipient_data['individual_recipient_details'] : array();

            if (isset($_POST[$prefix . 'delivery_method'][$_product_id])) {
                $_delivery_method = $_POST[$prefix . 'delivery_method'][$_product_id];
            }

            if (empty($_delivery_method) && !empty($product_delivery_meth) && $recipient_data['delivery_meth_configured'] == true) {

                if (array_key_exists('woo_vou_is_ajax', $_POST) && $_POST['woo_vou_is_ajax']) {

                    $return_html .= '<li><p class="woo-vou-recipient-error">' . esc_html__("Field", 'woovoucher') ." ". $recipient_data['recipient_delivery_label'] .' '. esc_html__("is required.", 'woovoucher') . '</p></li>';
                } else {

                    wc_add_notice('<p class="woo-vou-recipient-error">' . esc_html__("Field", 'woovoucher') ." ". $recipient_data['recipient_delivery_label'] .' '. esc_html__("is required.", 'woovoucher') . '</p>', 'error');
                }
                $valid = false;
            }

            // Looping on all recipient details
            foreach ($recipient_columns as $recipient_key => $recipient_val) {

                // Check if Email is Selected or not in Delivery Choice
                ${$recipient_key . '_err_enable'} = apply_filters('woo_vou_' . $recipient_key . '_error_enable', $_product_id);

                if (isset($_POST[$prefix . $recipient_key][$_product_id])) {//Strip recipient name
                    $_POST[$prefix . $recipient_key][$_product_id] = $this->model->woo_vou_escape_slashes_deep(trim($_POST[$prefix . $recipient_key][$_product_id]));
                }

                /**
                 * Checks whether recipient detail is individual
                 * If not than check whether it consist in the delivery method selected
                 * If any of the above condition satisfies then check for validation
                 * This applies to all recipient details validation check
                 */
                if (array_key_exists($recipient_key, $individual_recipient_details) || empty($_delivery_method) || (!empty($_delivery_method) && $product_delivery_meth['enable_' . $_delivery_method] == 'yes' && !empty($product_delivery_meth[$_delivery_method]) && is_array($product_delivery_meth[$_delivery_method]) && in_array($recipient_key, $product_delivery_meth[$_delivery_method]) )) {

                    // Not empty validation for all reipient fields
                    if ($recipient_data['enable_' . $recipient_key] == 'yes' && $recipient_data[$recipient_key . '_is_required'] == 'yes' && empty($_POST[$prefix . $recipient_key][$_product_id]) && ${$recipient_key . '_err_enable'}) {

                        if (array_key_exists('woo_vou_is_ajax', $_POST) && $_POST['woo_vou_is_ajax']) {

                            $return_html .= '<li><p class="woo-vou-recipient-error">' . esc_html__("Field", 'woovoucher') ." ". $recipient_data[$recipient_key . '_label'] .' '. esc_html__("is required.", 'woovoucher') . '</p></li>';
                        } else {

                            wc_add_notice('<p class="woo-vou-recipient-error">' . esc_html__("Field", 'woovoucher') . ' ' . $recipient_data[$recipient_key . '_label'] . ' ' . esc_html__("is required.", 'woovoucher') . '</p>', 'error');
                        }
                        $valid = false;
                    }
                    // Email format valiadtion if the recipient column is email
                    else if (!empty($_POST[$prefix . $recipient_key][$_product_id]) && array_key_exists('type', $recipient_columns[$recipient_key]) && !empty($recipient_columns[$recipient_key]['type']) && $recipient_columns[$recipient_key]['type'] == 'email' && !is_email($_POST[$prefix . $recipient_key][$_product_id])) {

                        if (array_key_exists('woo_vou_is_ajax', $_POST) && $_POST['woo_vou_is_ajax']) {

                            $return_html .= '<li><p class="woo-vou-recipient-error">' . esc_html__("Please Enter Valid", 'woovoucher') . ' ' . $recipient_data[$recipient_key . '_label'] . '.</p></li>';
                        } else {

                            wc_add_notice('<p class="woo-vou-recipient-error">' . esc_html__("Please Enter Valid", 'woovoucher') . ' ' . $recipient_data[$recipient_key . '_label'] . '.</p>', 'error');
                        }
                        $valid = false;
                    }
                    // Date format and min max date valiadtion if the recipient column is giftdate
                    else if (!empty($_POST[$prefix . $recipient_key][$_product_id]) && array_key_exists('type', $recipient_columns[$recipient_key]) && !empty($recipient_columns[$recipient_key]['type']) && $recipient_columns[$recipient_key]['type'] == 'date') {
                        
                        if( !empty( $variation_id ) ) {
                            $is_date_valid = $this->model->woo_vou_check_variation_date($_POST[$prefix . $recipient_key][$_product_id], $_product_id);
                        }
                        else {
                            $is_date_valid = $this->model->woo_vou_check_date($_POST[$prefix . $recipient_key][$_product_id], $product_id);
                        }

                        if ($is_date_valid['error'] && array_key_exists('error_type', $is_date_valid)) {
                            if ($is_date_valid['error_type'] == 'date_not_proper') {

                                if (array_key_exists('woo_vou_is_ajax', $_POST) && $_POST['woo_vou_is_ajax']) {

                                    $return_html .= '<li><p class="woo-vou-recipient-error">' . esc_html__("Please Enter Valid", 'woovoucher') . ' ' . $recipient_data[$recipient_key . '_label'] . '.</p></li>';
                                } else {

                                    wc_add_notice('<p class="woo-vou-recipient-error">' . esc_html__("Please Enter Valid", 'woovoucher') . ' ' . $recipient_data[$recipient_key . '_label'] . '.</p>', 'error');
                                }
                                $valid = false;
                            } elseif ($recipient_key == 'recipient_giftdate' && $is_date_valid['error_type'] == 'min_date_not_proper') {

                                $date_format = apply_filters('woo_vou_giftdate_start_end_date_format', 'm/d/Y');
                                $vou_min_date = array_key_exists('vou_min_date', $is_date_valid) && !empty($is_date_valid['vou_min_date']) ? $is_date_valid['vou_min_date'] : date($date_format);
                                $vou_max_date = array_key_exists('vou_max_date', $is_date_valid) && !empty($is_date_valid['vou_max_date']) ? $is_date_valid['vou_max_date'] : '';

                                if (!empty($vou_max_date)) {

                                    if (array_key_exists('woo_vou_is_ajax', $_POST) && $_POST['woo_vou_is_ajax']) {

                                        $return_html .= '<li><p class="woo-vou-recipient-error">' . esc_html__("Entered {$recipient_data['recipient_giftdate_label']} date is not allowed. Please select date between {$vou_min_date} and {$vou_max_date}", 'woovoucher') . '.</p></li>';
                                    } else {

                                        wc_add_notice('<p class="woo-vou-recipient-error">' . esc_html__("Entered {$recipient_data['recipient_giftdate_label']} date is not allowed. Please select date between {$vou_min_date} and {$vou_max_date}", 'woovoucher').'</p>', 'error');
                                    }
                                } else {

                                    if (array_key_exists('woo_vou_is_ajax', $_POST) && $_POST['woo_vou_is_ajax']) {

                                        $return_html .= '<li><p class="woo-vou-recipient-error">' . esc_html__("Entered {$recipient_data['recipient_giftdate_label']} date is not allowed. Please select select a future date.", 'woovoucher') . '.</p></li>';
                                    } else {

                                        wc_add_notice('<p class="woo-vou-recipient-error">' . esc_html__("Entered {$recipient_data['recipient_giftdate_label']} date is not allowed. Please select select a future date.</p>", 'woovoucher'), 'error');
                                    }
                                }

                                $valid = false;
                            }
                        }
                    }
                }
            }

            // PDF template selection validation
            if ($recipient_data['enable_pdf_template_selection'] == 'yes' && empty($_POST[$prefix . 'pdf_template_selection'][$_product_id])) {

                if (array_key_exists('woo_vou_is_ajax', $_POST) && $_POST['woo_vou_is_ajax']) {

                    $return_html .= '<li><p class="woo-vou-recipient-error">' . esc_html__("Field", 'woovoucher') . ' ' . $recipient_data['pdf_template_selection_label'] . ' ' . esc_html__("is required.", 'woovoucher') . '.</p></li>';
                } else {

                    wc_add_notice('<p class="woo-vou-recipient-error">' . esc_html__("Field", 'woovoucher') . ' ' . $recipient_data['pdf_template_selection_label'] . ' ' . esc_html__("is required.", 'woovoucher') . '</p>', 'error');
                }
                $valid = false;
            }
        }

        if (array_key_exists('woo_vou_is_ajax', $_POST) && $_POST['woo_vou_is_ajax']) {

            $response['valid'] = $valid === false ? false : true;
            $response['html'] = $return_html;

            echo json_encode($response);
            exit;
        } else {

            return $valid;
        }
    }

    /**
     * This is used to send an email after order completed to recipient user
     * 
     * Handles to send an email after order completed
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.0
     */
    public function woo_vou_payment_process_or_complete($order_id) {
        
        global $wpdb;

        $prefix = WOO_VOU_META_PREFIX; // Get prefix
        $cart_details = wc_get_order($order_id); // Get order
        $order_status = woo_vou_get_order_status($cart_details); // Get order status
        // Get option for grant access from global WooCommerce
        $woocommerce_downloads_grant_access_after_payment = get_option('woocommerce_downloads_grant_access_after_payment');

        if ($order_status == 'processing' && !empty($woocommerce_downloads_grant_access_after_payment) && $woocommerce_downloads_grant_access_after_payment == 'no') {
            return;
        }

        //Get and check if order's voucher data need hide
        if( get_post_meta($order_id, $prefix .'order_hide_voucher_data', true) ) return;

        // get option
        $vou_download_gift_mail = get_option('vou_download_gift_mail');
        $vou_attach_gift_mail = get_option('vou_attach_gift_mail');
        
        // record the fact that the vouchers have been sent
        if (get_post_meta($order_id, $prefix . 'recipient_email_sent', true)) {
            return;
        }

        $recipient_gift_email_send = true;
        $order_items = $cart_details->get_items();
        $payment_user_info = $this->model->woo_vou_get_buyer_information($order_id); // Get payment information
        $first_name = $payment_user_info['first_name']; // Get billing first name
        $last_name = $payment_user_info['last_name']; // Get billing last name
        // If item is not empty
        if (!empty($order_items)) {
            
            // Looping on all items
            foreach ($order_items as $product_item_key => $product_data) {

                // Declare variable to save flag for mail sent at item level
                $recipient_gift_email_send_item = 'no';

                // Get product from Item ( It is required otherwise multipdf voucher link not work and global $woo_vou_item_id will not work )
                $_product = apply_filters('woocommerce_order_item_product', $cart_details->get_product_from_item($product_data), $product_data);
                
                $download_file_data = $this->model->woo_vou_get_item_downloads_from_order($cart_details, $product_data); // Get downloadable files
                $product_id = isset($product_data['product_id']) ? $product_data['product_id'] : '';
                $variation_id = isset($product_data['variation_id']) ? $product_data['variation_id'] : '';

                /*
                * product price shortcode start
                */
                $data_id = !empty($variation_id) ? $variation_id : $product_id;
                $product_details = $this->model->woo_vou_get_product_details($order_id);
                $product_price = !empty($product_details[$data_id]['product_formated_price']) ? $product_details[$data_id]['product_formated_price'] : '';                
                
                // Getting the order meta data
                $order_all_data = $this->model->woo_vou_get_all_ordered_data($order_id);
                $vou_using_type = isset($order_all_data[$product_id]['using_type']) ? $order_all_data[$product_id]['using_type'] : '';

                //get product quantity
                $productqty = apply_filters('woo_vou_order_item_qty', $product_data['qty'], $product_data);

                // Vendor sale notification
                $this->model->woo_vou_vendor_sale_notification($product_id, $variation_id, $product_item_key, $product_data, $order_id, $cart_details);

                // Initilize recipient detail and other variables
                $recipient_details = $links = $attach_key = array();

                // Get product item meta
                $product_item_meta = isset($product_data['item_meta']) ? $product_data['item_meta'] : array();
                $recipient_details = $this->model->woo_vou_get_recipient_data($product_item_meta);
                $i = 0;

                if (!empty($download_file_data)) {
                    foreach ($download_file_data as $key => $download_file) {

                        $check_key = strpos($key, 'woo_vou_pdf_');

                        if (!empty($download_file) && $check_key !== false) {

                            $attach_keys[] = $key;
                            $i++;
                            $download_url = add_query_arg('woo_vou_screen', 'gift', $download_file['download_url']);
                            $links[] = '<small><a href="' . esc_url($download_url) . '">' . sprintf(esc_html__('Download file%s', 'woovoucher'), ( count($download_file_data) > 1 ? ' ' . $i . ': ' : ': ')) . esc_html($download_file['name']) . '</a></small>';
                            $links_plain[] = sprintf(esc_html__('Download file%s', 'woovoucher'), ( count($download_file_data) > 1 ? ' ' . $i . ': ' : ': ')) . esc_url($download_url);
                        }
                    }
                }

                if ($vou_download_gift_mail == 'yes') { //If download enable from gift mail.
                    $recipient_details['recipient_voucher'] = ( !empty( $links ) && is_array( $links ) ) ? '<br/>' . implode('<br/>', $links) : '';
                    $recipient_details['recipient_voucher_plain'] = ( !empty( $links_plain ) && is_array( $links_plain )  ) ? "\n" . implode("\n", $links_plain) : '';
                } else {
                    $recipient_voucher = '';
                }

                // added filter to send extra emails on diferent email ids by other extensions
                $woo_vou_extra_emails = false;
                $woo_vou_extra_emails = apply_filters('woo_vou_pdf_recipient_email', $woo_vou_extra_emails, $product_id);

                if (( isset($recipient_details['recipient_email']) && !empty($recipient_details['recipient_email']) ) ||
                        (!empty($woo_vou_extra_emails) )) {

                    $recipient_name = isset($recipient_details['recipient_name']) ? $recipient_details['recipient_name'] : '';
                    $recipient_email = isset($recipient_details['recipient_email']) ? $recipient_details['recipient_email'] : '';
                    $recipient_message = isset($recipient_details['recipient_message']) ? '"' . nl2br($recipient_details['recipient_message']) . '"' : '';
                    $recipient_voucher = isset($recipient_details['recipient_voucher']) ? $recipient_details['recipient_voucher'] : '';
                    $recipient_voucher_plain = isset($recipient_details['recipient_voucher_plain']) ? $recipient_details['recipient_voucher_plain'] : '';

                    // Get Extra email if passed through filter
                    $woo_vou_extra_emails = !empty($woo_vou_extra_emails) ? $woo_vou_extra_emails : '';

                    $attachments = array();

                    if (!empty($vou_attach_gift_mail) && $vou_attach_gift_mail == 'yes') { //If attachment enable for gift mail.
                        //Get product/variation ID
                        $product_id = !empty($product_data['variation_id']) ? $product_data['variation_id'] : $product_data['product_id'];

                        if (!empty($attach_keys)) {//attachments keys not empty
                            foreach ($attach_keys as $attach_key) {

                                $attach_pdf_file_name = get_option('attach_pdf_name');

                                // Apply filter to allow 3rd party people to change it
                                $date_format = apply_filters('woo_vou_voucher_date_format', 'Y-m-d');
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
                                    'item_id' => $product_item_key,
                                    'pdf_vou_key' => $attach_key,
                                );
                                $attach_pdf_file_name = apply_filters('woo_vou_attach_pdf_file_name', $attach_pdf_file_name, $pdf_file_args);

                                // Remove forward slash from name
                                $attach_pdf_file_name = str_replace('/', '', $attach_pdf_file_name);

                                // Voucher attachment path
                                $vou_pdf_path = WOO_VOU_UPLOAD_DIR . $attach_pdf_file_name . $order_id . '-' . $product_id . '-' . $product_item_key; // Voucher pdf path
                                // Replacing voucher pdf name with given value
                                $orderdvoucode_key = str_replace('woo_vou_pdf_', '', $attach_key);

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
                                }
                            }
                        }
                    }

                    // Get Recipient gift date
                    $recipient_giftdate = !empty($recipient_details['recipient_giftdate']) ? $recipient_details['recipient_giftdate'] : '';
                    $recipient_giftdate = apply_filters('woo_vou_replace_giftdate', $recipient_giftdate, $order_id, $product_item_key);

                    // Getting Voucher Delivery. This meta will contain voucher delivery selected by admin and not user
                    $woo_vou_all_ordered_data = $this->model->woo_vou_get_all_ordered_data($order_id);

                    if (!empty($variation_id)) { // If this variation then get it's product id
                        $_variation_pro = wc_get_product($variation_id);
                        $parent_product_id = $_variation_pro->get_parent_id();
                        $allvoucherdata = apply_filters('woo_vou_order_voucher_metadata', isset($woo_vou_all_ordered_data[$parent_product_id]) ? $woo_vou_all_ordered_data[$parent_product_id] : array(), $order_id, $product_item_key, $parent_product_id);
                        $vou_voucher_delivery_type = $allvoucherdata['voucher_delivery'][$variation_id];
                    } else {

                        $allvoucherdata = apply_filters('woo_vou_order_voucher_metadata', isset($woo_vou_all_ordered_data[$product_id]) ? $woo_vou_all_ordered_data[$product_id] : array(), $order_id, $product_item_key, $product_id);
                        $vou_voucher_delivery_type = $allvoucherdata['voucher_delivery'];
                    }

                    // Get user selected voucher delivery
                    // This will override voucher delivery selected by admin
                    $user_selected_delivery_type = $product_data->get_meta($prefix . 'delivery_method', true);
                    if (!empty($user_selected_delivery_type) && is_array($user_selected_delivery_type) && !empty($user_selected_delivery_type['value'])) {

                        $vou_voucher_delivery_type = $user_selected_delivery_type['value'];
                    }

                    // Apply filter to delivery type
                    $vou_voucher_delivery_type = apply_filters('woo_vou_check_product_delivery_type', $vou_voucher_delivery_type);

                    $giftdate_stamp = date('Y-m-d', strtotime($recipient_giftdate));
                    $current_date = current_time('Y-m-d');

                    // Check if voucher delivery set 
                    if ($vou_voucher_delivery_type == 'offline') {
                        $recipient_gift_email_send = false;

                        // check if gift date is set. If yes, then no need to send email right now.
                        // Will send email on selected gift date    
                    } elseif (!empty($recipient_giftdate) && $giftdate_stamp != $current_date) {
                        $recipient_gift_email_send = false;
                    } else {

                        //Get All Data for gift notify
                        $gift_data = array(
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'recipient_name' => $recipient_name,
                            'recipient_email' => $recipient_email,
                            'recipient_message' => $recipient_message,
                            'voucher_link' => $recipient_voucher,
                            'voucher_link_plain' => $recipient_voucher_plain,
                            'attachments' => $attachments,
                            'woo_vou_extra_emails' => $woo_vou_extra_emails,
                            'order_id' => $order_id,
                            'product_price' => $product_price
                        );

                        $gift_data = apply_filters('woo_vou_gift_notification_data', $gift_data, $product_data);


                        // Fires when gift notify.
                        do_action('woo_vou_gift_email', $gift_data);

                        // Give permission for download voucher to recipient email user
                        woo_vou_permission_download_recipient_user($order_id);

                        $recipient_gift_email_send_item = 'yes';
                    }
                }

                // Add new item meta for recording flag that mail is sent or not
                $product_data->add_meta_data($prefix . 'recipient_gift_email_send_item', $recipient_gift_email_send_item, true);
                // Save updated meta
                $product_data->save_meta_data();
            } //end foreach
            // Add action after gift email is sent
            do_action('woo_vou_after_gift_email', $order_id);
        }

        if ($recipient_gift_email_send) {
            //Update post meta for email attachment issue
            update_post_meta($order_id, $prefix . 'recipient_email_sent', true);
        }
    }

    /**
     * Prevent product from being added to cart (free or priced) with ?add-to-cart=XXX
     * When product expired or upcoming
     *
     * @package WooCommerce - PDF Vouchers
     * @since 2.4.0
     */
    public function woo_vou_prevent_product_add_to_cart($passed, $product_id) {

        // Get complete product details from product id
        $product = wc_get_product($product_id);

        $expired = $this->voucher->woo_vou_check_product_is_expired($product);

        if ($expired == 'upcoming') {
            wc_add_notice(esc_html__('You can not add upcoming products to cart.', 'woovoucher'), 'error');
            $passed = false;
        } elseif ($expired == 'expired') {
            wc_add_notice(esc_html__('You can not add expired products to cart.', 'woovoucher'), 'error');
            $passed = false;
        }

        return $passed;
    }

    /**
     * Valiate product added in cart is expired/upcoming
     * 
     * Handles to display error if proudct added in cart is expired/upcoming
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.4.0
     */
    public function woo_vou_woocommerce_checkout_process() {

        // get added products in cart
        $cart_details = WC()->session->cart;
        if (!empty($cart_details)) { // if cart is not empty
            foreach ($cart_details as $key => $product_data) {

                // get product id
                $product_id = $product_data['product_id'];

                // Get complete product details from product id
                $product = wc_get_product($product_id);

                // check product is expired/upcoming
                $expired = $this->voucher->woo_vou_check_product_is_expired($product);
                if ($expired == 'upcoming') {
                    if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1)
                        wc_add_notice(sprintf(esc_html__('%s is no longer available.', 'woovoucher'), $product->post->post_title), 'error');
                    else
                        wc_add_notice(sprintf(esc_html__('%s is no longer available.', 'woovoucher'), $product->get_title()), 'error');
                    return;
                } elseif ($expired == 'expired') {
                    if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1)
                        wc_add_notice(sprintf(esc_html__('%s is no longer available.', 'woovoucher'), $product->post->post_title), 'error');
                    else
                        wc_add_notice(sprintf(esc_html__('%s is no longer available.', 'woovoucher'), $product->get_title()), 'error');
                    return;
                }
            }
        }
    }

    /**
     * Error message
     * 
     * Handles to throw custom error message
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.9.2
     */
    public function woo_vou_coupon_err_message($err, $err_code, $coupon) {

        //get prefix
        $prefix = WOO_VOU_META_PREFIX;

        if ($coupon) {

            $coupon_id = $this->model->woo_vou_get_coupon_id_from_coupon($coupon); // Get coupon id
            $coupon_start_date = get_post_meta($coupon_id, $prefix . 'start_date', true); // Get coupon start date

            $coupon_rest_days = get_post_meta($coupon_id, $prefix . 'disable_redeem_day', true); // Get coupon restriction days
            // Check error code for start date
            if ( $err_code == $prefix . 'start_date_err') {

                $err = sprintf(esc_html__('This Coupon Code cannot be used before %s', 'woovoucher'), date('Y-m-d H:i:s', strtotime($coupon_start_date))); // Throw error message               
            }
            elseif ( $err_code == $prefix . 'day_err' ) {  // Check error for restriction days

                $message = implode(", ", $coupon_rest_days); // Get all days
                $err = sprintf(esc_html__('Sorry, coupon Code cannot be used on %s.', 'woovoucher'), $message); // Throw error message
            }
        }

        // Return error
        return $err;
    }


     /**
     * Handles to display voucher link on thank you page for voucher
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 3.7.12
     */
    function woo_vou_dummy_download_file_for_voucher( $file, $product, $download_id ){
       
       if( empty( $download_id) ) {
            // set dummy downloadable file with dummy id 
            $download_object = new WC_Product_Download();

            $download_object->set_id( 'sdfdsds5f64dsfdfs4f6s54f6sd4' );
            $download_object->set_name( 'Pdf Voucher' );
            $download_object->set_file( WOO_VOU_IMG_URL.'/vendor-logo.png');

            return $download_object;
        }
        
        return $file;
    }

    /**
     * Function to not apply disocunt coupon on tax if selected option is subtotal for coupon code
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 3.9.3
     */
    public function woo_vou_voucher_coupon_code_apply_without_tax( $total, $item, $cart_total ){

        global $woocommerce;
        $prefix = WOO_VOU_META_PREFIX;
        $coupons = $woocommerce->cart->get_applied_coupons();

        if( empty( $coupons ) )
            return $total;

        foreach ( $coupons as $key => $coupon ) {

            // arguments for getting coupon id
            $args = array(
                'fields' => 'ids',
                'name' => strtolower($coupon),
                array(
                    'key' => $prefix . 'coupon_type',
                    'value' => 'voucher_code'
                ),
            );

            // Get Coupon code data
            $coupon_code_data = woo_vou_get_coupon_details($args);

            if (!empty($coupon_code_data)) {
                foreach ( $coupon_code_data  as $key => $coupon_id ) {
                    
                    $exclude_discount_on_tax = get_post_meta($coupon_id, $prefix.'discount_on_tax_type', true);
                    
                    // check if the discount code option is subtotal selected then customer will give all tax 
                    if( wc_tax_enabled() &&  !empty($exclude_discount_on_tax) && $exclude_discount_on_tax == 'subtotal' ){

                        return WC_Tax::calc_tax( $item->price, $item->tax_rates, $item->price_includes_tax );
                    }
                }
            }
        }
        
        return $total;
    }


    /**
     * Function to not apply disocunt coupon on tax if selected option is subtotal for coupon code and for inclusive tax option
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 3.9.3
     */
    public function woo_vou_voucher_coupon_code_apply_without_tax_for_inclusive_option( $total, $item, $cart){

        global $woocommerce;
        $prefix = WOO_VOU_META_PREFIX;

        $coupons = $woocommerce->cart->get_applied_coupons();

        if( empty( $coupons ) )
            return $total;

        foreach ( $coupons as $key => $coupon ) {

            // arguments for getting coupon id
            $args = array(
                'fields' => 'ids',
                'name' => strtolower($coupon),
                array(
                    'key' => $prefix . 'coupon_type',
                    'value' => 'voucher_code'
                ),
            );

            // Get Coupon code data
            $coupon_code_data = woo_vou_get_coupon_details($args);

            if (!empty($coupon_code_data)) {
                foreach ( $coupon_code_data  as $key => $coupon_id ) {
                    
                    $exclude_discount_on_tax = get_post_meta($coupon_id, $prefix.'discount_on_tax_type', true);
                    
                    // check if the discount code option is subtotal selected then customer will give all tax 
                    if( wc_tax_enabled() &&  wc_prices_include_tax() && !empty($exclude_discount_on_tax) && $exclude_discount_on_tax == 'subtotal' ){

                        // check if subtotal is 0 then total should be tax amount 
                        if( $total <= 0 && !empty($item['line_subtotal'] ) && $item['line_subtotal_tax'] > 0 ){

                            $total = $item['line_subtotal_tax'];
                        }
                    }
                }
            }
        } 

        return $total;
    }


    /**
     * Function to display full coupon amount without deducting tax for inclusive of tax option
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 3.9.3
     */
    public function woo_vou_voucher_coupon_code_apply_without_tax_for_inclusive_amount_display( $discount_amount_html, $coupon ){
        global $woocommerce;

        $prefix = WOO_VOU_META_PREFIX;
        if ( is_string( $coupon ) ) {
            $coupon = new WC_Coupon( $coupon );
        }

        if( !empty( $coupon ) ){
            // arguments for getting coupon id
            $args = array(
                'fields' => 'ids',
                'name' => strtolower($coupon->get_code()),
                array(
                    'key' => $prefix . 'coupon_type',
                    'value' => 'voucher_code'
                ),
            );

            // Get Coupon code data
            $coupon_code_data = woo_vou_get_coupon_details($args);

            if (!empty($coupon_code_data)) {

                foreach ( $coupon_code_data  as $key => $coupon_id ) {
                    
                    $exclude_discount_on_tax = get_post_meta($coupon_id, $prefix.'discount_on_tax_type', true);

                    // check if the discount code option is subtotal selected then customer will give all tax 
                    if( wc_tax_enabled() && !empty($exclude_discount_on_tax) && $exclude_discount_on_tax == 'subtotal' && wc_prices_include_tax() ){

                        $couponObj = new WC_Coupon($coupon_id);

                        $orginal_coupon_amount = $couponObj->get_amount();

                        if( get_option( 'woocommerce_tax_display_cart' ) == 'incl' ){
                            $exclusive_tax = true;
                            if( $woocommerce->cart->subtotal > $orginal_coupon_amount ){
                                $exclusive_tax = false;
                            }
                            $amount = WC()->cart->get_coupon_discount_amount( $coupon->get_code(), $exclusive_tax );
                            $discount_amount_html = '-' . wc_price( $amount );
                        }
                        elseif( $woocommerce->cart->subtotal > $orginal_coupon_amount  ){
                            $exclusive_tax = false;
                            $amount = WC()->cart->get_coupon_discount_amount( $coupon->get_code(), $exclusive_tax );
                            $discount_amount_html = '-' . wc_price( $amount );                
                        }  


                    }
                }
            }
        }
        
        return $discount_amount_html;
    }


    /**
     * Function to display full discount amount without for inclusive of tax on order detilas
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 3.9.3
     */
    public function woo_vou_voucher_coupon_code_apply_without_tax_for_inclusive_for_order_display( $total_discount, $order ){

        $prefix = WOO_VOU_META_PREFIX;
        
        if( wc_prices_include_tax() && wc_tax_enabled() ){
            $coupons = $order->get_coupon_codes();
            $order_status = $order->get_status('no');
            // print $order->get_subtotal();exit;
            if( !empty( $coupons )){
                foreach ( $coupons as $key => $coupon ) {
                    // arguments for getting coupon id
                    $args = array(
                        'fields' => 'ids',
                        'name' => strtolower($coupon),
                        array(
                            'key' => $prefix . 'coupon_type',
                            'value' => 'voucher_code'
                        ),
                    );

                    // Get Coupon code data
                    $coupon_code_data = woo_vou_get_coupon_details($args);

                    if (!empty($coupon_code_data)) {

                        foreach ( $coupon_code_data  as $key => $coupon_id ) {    
                            $exclude_discount_on_tax = get_post_meta($coupon_id, $prefix.'discount_on_tax_type', true);
                            if( !is_admin() && ( $order_status != 'processing' && $order_status != 'completed' ) && get_option( 'woocommerce_tax_display_cart' ) == 'incl' ){
                                $total_discount = $order->get_discount_total();
                            }
                            elseif( !empty($exclude_discount_on_tax) && $exclude_discount_on_tax == 'subtotal' && get_option( 'woocommerce_tax_display_cart' ) && ( $order_status == 'processing' || $order_status == 'completed' ) ){
                                $total_discount = $order->get_discount_total() + $order->get_discount_tax();
                            }
                        }
                    }
                }
            }
        }

        return $total_discount;
    }


    /**
     * Function to fix the coupon code have been used on other issue
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 3.9.3
    */
    public function woo_vou_disable_hold_stock_for_checkout(){

        return false;
    } 


    /**
     * Function to hide the voucher download link for the customer
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 3.9.8
     */
    public function woo_vou_hide_download_link_checkout_and_thank_you_page($downloads,$orderdata){
        
        $items  = $orderdata->get_items();
        $allow_recipient_to_get_voucher_info = get_option('vou_allow_recipient_to_get_voucher_info');
        
        foreach ( $items as $item ) {
            $recipient_details = $this->model->woo_vou_get_recipient_data($item);
            
            if(!empty($recipient_details) && $allow_recipient_to_get_voucher_info == 'yes'){
                foreach ($downloads as $key => $download_file) {
                    
                    $check_key = strpos($download_file['download_id'],'woo_vou_pdf_');      
                    
                    if (!empty($download_file)  && $check_key !== false) {                      
                        unset($downloads[$key]);
                    }
                }
            }
        }
        return $downloads;
    }


     /**
     * Function to allow guest user to redeem voucher code in check voucher code page and QR code page
     * @package WooCommerce - PDF Vouchers
     * @since 3.9.8
     */
    
    public function vou_vou_allow_guest_user_redeem_voucher( $enble ){
        
        $allow_redeem_voucher = get_option('woo_vou_guest_user_allow_redeem_voucher');
        $guest_user_check_voucher_code = get_option('vou_enable_guest_user_check_voucher_code');
        
        if($allow_redeem_voucher == 'yes' && $guest_user_check_voucher_code == 'yes') {
            $enble = true;
        }   
            
        return $enble;
    }

    public function woo_vou_variation_start_enddate(){
        if( isset($_POST['variation_id'] ) && !empty($_POST['variation_id'] )){
            $dates = $this->model->woo_vou_get_minmax_date_from_product_variation($_POST['variation_id']);
            wp_send_json($dates);
            exit;
        }
    }
     
    /**
     * Adding Hooks
     * 
     * Adding proper hoocks for the discount codes
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    public function add_hooks() {

        //add action to save voucher in order
        add_action('woocommerce_checkout_update_order_meta', 'woo_vou_product_purchase');

        //add action for add custom notifications
        add_filter('woocommerce_email_actions', 'woo_vou_add_email_notification');

        //insert pdf vouchers in woocommerce downloads fiels table
        add_action('woocommerce_grant_product_download_permissions', 'woo_vou_insert_downloadable_files');

        //add action to product process
        add_action('woocommerce_download_product', 'woo_vou_download_process', 10, 6);

        //add filter to add admin access for vendor role
        add_filter('woocommerce_prevent_admin_access', 'woo_vou_prevent_admin_access');

        //ajax call to edit all controls
        add_action('wp_ajax_woo_vou_check_voucher_code', array($this->voucher, 'woo_vou_check_voucher_code'));
        add_action('wp_ajax_nopriv_woo_vou_check_voucher_code', array($this->voucher, 'woo_vou_check_voucher_code'));

        //ajax call to save voucher code
        add_action('wp_ajax_woo_vou_save_voucher_code', array($this->voucher, 'woo_vou_save_voucher_code'));
        add_action('wp_ajax_nopriv_woo_vou_save_voucher_code', array($this->voucher, 'woo_vou_save_voucher_code'));

        // add action to add html for check voucher code
        add_action('woo_vou_check_code_content', array($this, 'woo_vou_check_code_content'));

        // add action to set order as a global variable
        add_action('woocommerce_email_before_order_table', 'woo_vou_email_before_order_table');

        //filter to set order product data as a global variable
        add_filter('woocommerce_order_item_product', 'woo_vou_order_item_product', 10, 2);

        //restore voucher codes if order is failed or cancled
        add_action('woocommerce_order_status_changed', array($this->voucher, 'woo_vou_restore_voucher_codes'), 10, 3);

        //add custom html to single product page before add to cart button
        add_action('woocommerce_before_add_to_cart_button', 'woo_vou_after_before_add_to_cart_button');

        //add to cart in item data
        add_filter('woocommerce_add_cart_item_data', 'woo_vou_woocommerce_add_cart_item_data', 10, 3);

        // add to cart in item data from session
        add_filter('woocommerce_get_cart_item_from_session', 'woo_vou_get_cart_item_from_session', 10, 2);

        // get to cart in item data to display in cart page
        add_filter('woocommerce_get_item_data', 'woo_vou_woocommerce_get_item_data', 10, 2);

        //add filter to validate custom fields of product page
        add_filter('woocommerce_add_to_cart_validation', array($this, 'woo_vou_add_to_cart_validation'), 10, 6);

        // Add filter to validate recipient details while previewing from template
        add_filter('wp_ajax_woo_vou_add_to_cart_validation', array($this, 'woo_vou_add_to_cart_validation'));
        add_filter('wp_ajax_nopriv_woo_vou_add_to_cart_validation', array($this, 'woo_vou_add_to_cart_validation'));

        // add action when order status goes to complete
        add_action('woocommerce_order_status_completed_notification', array($this, 'woo_vou_payment_process_or_complete'), 100);
        add_action('woocommerce_order_status_pending_to_processing_notification', array($this, 'woo_vou_payment_process_or_complete'), 100);
        add_action('woocommerce_order_status_on-hold_to_processing_notification', array($this, 'woo_vou_payment_process_or_complete'), 100);

        //add action to hide recipient in order meta
        add_filter('woocommerce_hidden_order_itemmeta', 'woo_vou_hide_recipient_itemmeta');

        //filter to attach the voucher pdf in mail
        add_filter('woocommerce_email_attachments', 'woo_vou_attach_voucher_to_email', 10, 3);

        //add action to check qrcode
        add_action('init', 'woo_vou_check_qrcode');

        //Add order manually from backend
        add_action('woocommerce_process_shop_order_meta', 'woo_vou_process_shop_order_manually');

        // Add action to allow vendor to upload their media files
        add_action('admin_init', 'woo_vou_allow_vendor_uploads');

        // To make compatible with previous versions of 3.0.0
        if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1) {
            //add filter to merge voucher pdf with product files
            add_filter('woocommerce_product_files', 'woo_vou_downloadable_files_26_deprecated', 10, 2);

            //Set global item id for voucher key generater
            add_filter('woocommerce_get_product_from_item', 'woo_vou_set_global_item_id_26_deprecated', 10, 3);

            // add action to add cart item to the order.
            add_action('woocommerce_add_order_item_meta', 'woo_vou_add_order_item_meta_26_deprecated', 10, 2);

            //Hide recipient variation from product name field
            add_filter('woo_vou_hide_recipient_variations', 'woo_vou_hide_recipients_item_variations_26_deprecated', 10, 2);
        } else {
            //Set global item id for voucher key generater
            add_filter('woocommerce_order_item_product', 'woo_vou_set_global_item_id', 10, 2);

            // add action to add cart item to the order.
            add_action('woocommerce_checkout_create_order_line_item', 'woo_vou_add_order_item_meta', 10, 4);

            // Add filter to add image on thankyou page, removed by woocommerce 3.0
            add_filter('woocommerce_display_item_meta', 'woo_vou_display_item_meta', 10, 3);

            //Hide recipient variation from product name field
            add_filter('woo_vou_hide_recipient_variations', 'woo_vou_hide_recipients_item_variations', 10, 2);
        }

        // Add downlodable files and add Item ID in generated pdf download URL
        add_filter('woocommerce_get_item_downloads', 'woo_vou_get_item_pdf_downloads', 10, 3);

        //Add voucher download links to my account page
        add_action('woocommerce_customer_get_downloadable_products', 'woo_vou_my_pdf_vouchers_download_link');

        //restore old voucher code again when resume old order due to overwrite item
        add_action('woocommerce_resume_order', array($this->voucher, 'woo_vou_resume_order_voucher_codes'));

        // add action to update stock as per no. of voucher codes
        add_action('woocommerce_reduce_order_stock', 'woo_vou_update_order_stock');

        // add filter to remove add to cart button on shop page for expire product
        add_action('woocommerce_loop_add_to_cart_link', 'woo_vou_shop_add_to_cart', 10, 1);

        // prevent add to cart product if some one try directly using url   
        add_filter('woocommerce_add_to_cart_validation', array($this, 'woo_vou_prevent_product_add_to_cart'), 10, 2);

        // add action on place order check product is expired/upcoming in checkout page
        add_action('woocommerce_checkout_process', array($this, 'woo_vou_woocommerce_checkout_process'), 10);

        //ajax pagination for used voucher codes
        add_action('wp_ajax_woo_vou_used_codes_next_page', array($this->voucher, 'woo_vou_used_voucher_codes_ajax'));
        add_action('wp_ajax_nopriv_woo_vou_used_codes_next_page', array($this->voucher, 'woo_vou_used_voucher_codes_ajax'));

        //ajax pagination for purchased voucher codes
        add_action('wp_ajax_woo_vou_purchased_codes_next_page', array($this->voucher, 'woo_vou_purchased_voucher_codes_ajax'));
        add_action('wp_ajax_nopriv_woo_vou_purchased_codes_next_page', array($this->voucher, 'woo_vou_purchased_voucher_codes_ajax'));

        //ajax pagination for unused voucher codes
        add_action('wp_ajax_woo_vou_unused_codes_next_page', array($this->voucher, 'woo_vou_unused_voucher_codes_ajax'));
        add_action('wp_ajax_nopriv_woo_vou_unused_codes_next_page', array($this->voucher, 'woo_vou_unused_voucher_codes_ajax'));

        // add filter to remove voucher download link
        add_filter('woo_vou_remove_download_link', 'woo_vou_remove_voucher_download_link', 10, 3);
        

        // allow to add admin email in bcc
        add_filter('woocommerce_email_headers', 'woo_vou_allow_admin_to_bcc', 10, 3);

        // Add filter to validate extra fields
        add_filter('woocommerce_coupon_is_valid', 'woo_vou_validate_coupon', 10, 2);

        // Add filter to add custom coupon error message
        add_filter('woocommerce_coupon_error', array($this, 'woo_vou_coupon_err_message'), 10, 3);

        // Add action to generate coupon code when order status gets processing
        add_action('woocommerce_grant_product_download_permissions', 'woo_vou_generate_couponcode_from_vouchercode', 20);

        // Add action to enable recipient form below add to cart button
        add_action('wp', 'woo_vou_enable_after_add_to_cart_button');

        // Add action to add change used listing arguments for "Used Voucher Code" page
        add_filter('woo_vou_get_used_vou_list_args', 'woo_vou_check_vendor_author_args');

        // Add action to add change used listing arguments for "Purchased Voucher Code" page
        add_filter('woo_vou_get_purchased_vou_list_args', 'woo_vou_check_vendor_author_args');

        // Add action to add change used listing arguments for "Purchased Voucher Code" page
        add_filter('woo_vou_get_partial_vou_list_args', 'woo_vou_check_vendor_author_args');

        // Add action to add change used listing arguments for "Expire Voucher Code" page
        add_filter('woo_vou_get_expire_vou_list_args', 'woo_vou_check_vendor_author_args');

        // Add action to add change used listing arguments for "Check Voucher Code" purchased codes
        add_filter('woo_vou_get_primary_vendor_purchase_voucode_args', 'woo_vou_check_vendor_author_args');

        // Add action to add change used listing arguments for "Check Voucher Code" used codes
        add_filter('woo_vou_get_primary_vendor_used_voucode_args', 'woo_vou_check_vendor_author_args');

        // To make compatible with versions of 3.2.0 or greater
        if (version_compare(WOOCOMMERCE_VERSION, "3.2.0") != -1) {

            // Add action to add downlodable files list in emails
            add_action('woocommerce_email_order_details', 'woo_vou_email_order_details', 5, 4);
        }

        //ajax call to update voucher expiry date
        add_action('wp_ajax_woo_vou_change_voucher_expiry_date', array($this->voucher, 'woo_vou_change_voucher_expiry_date'));
        add_action('wp_ajax_nopriv_woo_vou_change_voucher_expiry_date', array($this->voucher, 'woo_vou_change_voucher_expiry_date'));

        //ajax call to get voucher expiry date
        add_action('wp_ajax_woo_vou_get_voucher_expiry_date', 'woo_vou_get_voucher_expiry_date');
        add_action('wp_ajax_nopriv_woo_vou_get_voucher_expiry_date', 'woo_vou_get_voucher_expiry_date' );

        // add action to get voucher details data for front end
        add_action('woo_vou_code_details_data', 'woo_vou_get_vou_details_data');
        // add action to update voucher information from frontend
        add_action('init', 'woo_vou_update_voucher_information');

        // add action to validate/check submitted redeem information from frontend
        add_action('wp_ajax_woo_vou_check_redeem_info', 'woo_vou_check_redeem_info');
        add_action('wp_ajax_nopriv_woo_vou_check_redeem_info', 'woo_vou_check_redeem_info');

        // add action to update recipient information from frontend
        add_action('init', 'woo_vou_update_recipient_details');

        // Add action to modify expiry date
        add_filter('woocommerce_coupon_get_date_expires', 'woo_vou_get_coupon_expiry_date', 10, 2);

        // Add action to send gift notification email
        add_action('wp_ajax_woo_vou_resend_gift_notification_email', array($this->voucher, 'woo_vou_resend_gift_notification_email'));
        add_action('wp_ajax_nopriv_woo_vou_resend_gift_notification_email', array($this->voucher, 'woo_vou_resend_gift_notification_email'));

        //Add voucher download links to my account page
        add_action('woocommerce_customer_get_downloadable_products', 'woo_vou_recipient_permission_vouchers_download_link');

        // Add action to import voucher codes, once user gets registered
        add_action('user_register', 'woo_vou_created_customer');

        // add action to update voucher information from frontend
        add_action('init', 'woo_vou_update_voucher_extra_note');

        // Add filter to modify the recipient giftdate formatted value, which WooCommerce gets from function get_formatted_meta_data
        add_filter('woocommerce_order_item_display_meta_value', 'woo_vou_change_giftdate_formatted_metadata', 10, 3);

        // Add filter to remove order downloadable items from processing/completed order mail and order thank you page.
        add_filter('woocommerce_order_get_downloadable_items', 'woo_vou_remove_order_downloadable_items', 15, 2);

        // Add action to check if voucher code id exists or not
        add_action('wp', 'woo_vou_check_voucher_code_exist');

        // Add action to generate popup html in footer
        add_action('wp_footer', 'woo_vou_render_preview_pdf_popup');

        // Add action to generate Preview PDF
        add_action('wp_ajax_woo_vou_generate_preview_pdf', 'woo_vou_generate_preview_pdf');
        add_action('wp_ajax_nopriv_woo_vou_generate_preview_pdf', 'woo_vou_generate_preview_pdf');

        // added hook to allow generate preview pdf in new tab 
        add_action('init', 'woo_vou_generate_preview_pdf');

        // Add action to delete Preview PDF
        add_action('wp_ajax_woo_vou_unlink_preview_pdf', 'woo_vou_unlink_preview_pdf');
        add_action('wp_ajax_nopriv_woo_vou_unlink_preview_pdf', 'woo_vou_unlink_preview_pdf');

        // filter to show download link on thank you page for voucher
        add_action('woocommerce_product_file', array( $this, 'woo_vou_dummy_download_file_for_voucher') , 10,3);    
        
        // filter to apply coupon code discount on price only not on tax        
        add_filter('woocommerce_calculate_item_totals_taxes', array( $this,'woo_vou_voucher_coupon_code_apply_without_tax'),10,3);

        // filter to apply coupon code discount on price only not on tax for inclusive tax options        
        add_filter('woocommerce_get_discounted_price', array( $this, 'woo_vou_voucher_coupon_code_apply_without_tax_for_inclusive_option'),10,3);

        // filter hook to display full coupon amount without debucting tax for inclusive of tax option
        add_filter('woocommerce_coupon_discount_amount_html', array( $this, 'woo_vou_voucher_coupon_code_apply_without_tax_for_inclusive_amount_display') ,10,2);
        
        // hook to display full discount amount without for inclusive of tax on order detilas
        add_filter('woocommerce_order_get_total_discount', array( $this, 'woo_vou_voucher_coupon_code_apply_without_tax_for_inclusive_for_order_display') ,10,2);
        // Disable the woocommerce_hold_stock_for_checkout.
        add_filter('woocommerce_hold_stock_for_checkout', array($this,'woo_vou_disable_hold_stock_for_checkout'));
        
       // Filter to hide download link from the checkout and thank you page
        add_filter('woocommerce_order_get_downloadable_items',array($this,'woo_vou_hide_download_link_checkout_and_thank_you_page'),10,2);

        //Allow Guest user to redeem voucher code in check voucher code page
        add_filter('woo_vou_access_redeem_btn_without_login',array($this,'vou_vou_allow_guest_user_redeem_voucher'));

        //Allow Guest user to redeem voucher code in QR code page
        add_filter('woo_vou_without_login_access_qrcode_redeem_submit',array($this,'vou_vou_allow_guest_user_redeem_voucher'));

        //Allow Guest user to redeem voucher code in QR code page
        add_filter('woo_vou_access_partial_redeem_without_login',array($this,'vou_vou_allow_guest_user_redeem_voucher'));

        add_action('wp_ajax_woo_vou_variation_start_enddate', array( $this, 'woo_vou_variation_start_enddate'));
        add_action('wp_ajax_nopriv_woo_vou_variation_start_enddate', array( $this, 'woo_vou_variation_start_enddate'));
    }
}
