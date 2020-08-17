<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Admin Class
 *
 * Handles generic Admin functionality and AJAX requests.
 *
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
class WOO_Vou_Admin {

    var $scripts, $model, $render, $voumeta, $voucher;

    public function __construct() {

        global $woo_vou_scripts, $woo_vou_model, $woo_vou_voucher,
        $woo_vou_render, $woo_vou_admin_meta;

        $this->scripts = $woo_vou_scripts;
        $this->model = $woo_vou_model;
        $this->render = $woo_vou_render;
        $this->voumeta = $woo_vou_admin_meta;
        $this->voucher = $woo_vou_voucher;
    }

    /**
     * Adding Submenu Page
     * 
     * Handles to adding submenu page for
     * voucher extension
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    public function woo_vou_admin_submenu() {

        global $current_user, $woo_vou_vendor_role;

        $main_menu_slug = WOO_VOU_MAIN_MENU_NAME;

        //Current user role
        $user_roles = isset($current_user->roles) ? $current_user->roles : array();
        $user_role = array_shift($user_roles);

        //get voucher admins
        $voucher_admins = woo_vou_assigned_admin_roles();

        // Get settings of voucher vendor access for voucher templates
        $vendor_access_vou_template = get_option('vou_disable_vendor_access_voucher_template');

        //Remove Voucher Template menu
        remove_menu_page('edit.php?post_type=' . WOO_VOU_POST_TYPE);

        if (in_array($user_role, $voucher_admins) || in_array($user_role, $woo_vou_vendor_role)) {

            if (current_user_can('manage_woocommerce')) { // administrator or shop manager
                //voucher codes page
				
                $voucher_page = add_submenu_page($main_menu_slug, esc_html__('Voucher Codes', 'woovoucher'), esc_html__('Voucher Codes', 'woovoucher'), 'read', 'woo-vou-codes', 'woo_vou_codes_page');
            } else {
					
                $main_menu_slug = 'woo-vou-codes';
                //add WooCommerce Page
                add_menu_page(__('WooCommerce', 'woovoucher'), esc_html__('WooCommerce', 'woovoucher'), 'read', $main_menu_slug, '');
                add_submenu_page($main_menu_slug, esc_html__('Voucher Codes', 'woovoucher'), esc_html__('Voucher Codes', 'woovoucher'), 'read', $main_menu_slug, 'woo_vou_codes_page');
                // if user is vendor and not disable vocher template access
                if (in_array($user_role, $woo_vou_vendor_role) && ($vendor_access_vou_template != 'yes')) {
                    add_submenu_page($main_menu_slug, esc_html__('Voucher Templates', 'woovoucher'), esc_html__('Voucher Templates', 'woovoucher'), 'read', 'edit.php?post_type=' . WOO_VOU_POST_TYPE);
                }
            }

            //add check voucher code page
            $check_voucher_page = add_submenu_page($main_menu_slug, esc_html__('Check Voucher Code', 'woovoucher'), __('Check Voucher Code', 'woovoucher'), 'read', 'woo-vou-check-voucher-code', 'woo_vou_check_voucher_code_page');
        }

        // check if is voucher template lists page
        if (!empty($_REQUEST['post_type']) && ( $_REQUEST['post_type'] == WOO_VOU_POST_TYPE )) {
            if (in_array($user_role, $woo_vou_vendor_role) && ($vendor_access_vou_template == 'yes')) {
                wp_die(esc_html__('Sorry, you are not allowed to access this page.'), 403);
            }
        }

        // check if is voucher template edit page
        if (!empty($_REQUEST['post']) && !empty($_REQUEST['action']) && ( $_REQUEST['action'] == 'edit' )) {
            $post_data = get_post($_REQUEST['post']);
            if (!empty($post_data) && ($post_data->post_type == WOO_VOU_POST_TYPE) && in_array($user_role, $woo_vou_vendor_role) && ($vendor_access_vou_template == 'yes')) {
                wp_die(esc_html__('Sorry, you are not allowed to access this page.'), 403);
            }
        }

        // check if voucher code is not avaible
        if (!empty($_GET['page']) && !empty($_GET['vou_code']) && $_GET['page'] == 'woo-vou-codes') {

            // Get vouchercodes data 
            $voucodeid = $_GET['vou_code'];
            $voucher_data = get_post($voucodeid);

            if (empty($voucodeid) || empty($voucher_data) || ($voucher_data->post_type != WOO_VOU_CODE_POST_TYPE)) {
                wp_die(sprintf(esc_html__("%sYou attempted to view an voucher that doesn't exist. %s %s Perhaps it was deleted?%s", 'woovoucher'), '<h1>', '</h1>', '<p>', '</p>'), 403);
            }
        }
    }

    /**
     * Add Custom meta boxs  for voucher templates post tpye
     * 
     * Handles to add custom meta boxs in voucher templates
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    public function woo_vou_editor_meta_box() {

        global $wp_meta_boxes;

        // add metabox for edtior
        add_meta_box('woo_vou_page_voucher', esc_html__('Voucher', 'woovoucher'), 'woo_vou_editor_control', WOO_VOU_POST_TYPE, 'normal', 'high', 1);

        // add metabox for style options
        add_meta_box('woo_vou_pdf_options', esc_html__('Voucher Options', 'woovoucher'), 'woo_vou_pdf_options_page', WOO_VOU_POST_TYPE, 'normal', 'high');
    }

    /**
     * Custom column
     * 
     * Handles the custom columns to voucher listing page
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    public function woo_vou_manage_custom_column($column_name, $post_id) {

        global $wpdb, $post;

        $prefix = WOO_VOU_META_PREFIX;

        switch ($column_name) {

            case 'voucher_preview' :
                $preview_url = woo_vou_get_preview_link($post_id);
                echo '<a href="' . esc_url($preview_url) . '" class="woo-vou-pdf-preview" target="_blank">' . esc_html__('View Preview', 'woovoucher') . '</a>';
                break;
        }
    }

    /**
     * Add New Column to voucher listing page
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    function woo_vou_add_new_columns($new_columns) {

        unset($new_columns['date']);

        $new_columns['voucher_preview'] = esc_html__('View Preview', 'woovoucher');
        $new_columns['date'] = _x('Date', 'column name', 'woovoucher');

        return $new_columns;
    }

    /**
     * Add New Action For Create Duplicate
     * 
     * Handles to add new action for 
     * Create Duplicate link of that voucher
     *
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    public function woo_vou_dupd_action_new_link_add($actions, $post) {

        //check current user can have administrator rights
        //post type must have vouchers post type
        if (!current_user_can('manage_options') || $post->post_type != WOO_VOU_POST_TYPE)
            return $actions;

        // add new action for create duplicate
        $args = array('action' => 'woo_vou_duplicate_vou', 'woo_vou_dupd_vou_id' => $post->ID);
        $dupdurl = add_query_arg($args, admin_url('edit.php'));
        $actions['woo_vou_duplicate_vou'] = '<a href="' . wp_nonce_url($dupdurl, 'duplicate-vou_' . $post->ID) . '" title="' . esc_html__('Make a duplicate from this voucher', 'woovoucher')
                . '" rel="permalink">' . esc_html__('Duplicate', 'woovoucher') . '</a>';

        // return all actions
        return $actions;
    }

    /**
     * Add Preview Button
     * 
     * Handles to add preview button within
     * Publish meta box
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    function woo_vou_add_preview_button() {

        global $typenow, $post;

        if (!current_user_can('manage_options') || !is_object($post) || $post->post_type != WOO_VOU_POST_TYPE) {
            return;
        }

        if (isset($_GET['post'])) {

            $args = array('action' => 'woo_vou_duplicate_vou', 'woo_vou_dupd_vou_id' => absint($_GET['post']));
            $dupdurl = add_query_arg($args, admin_url('edit.php'));
            $notifyUrl = wp_nonce_url($dupdurl, 'duplicate-vou_' . $_GET['post']);
            ?>
            <div id="duplicate-action"><a class="submitduplicate duplication" href="<?php echo esc_url($notifyUrl); ?>"><?php esc_html_e('Copy to a new draft', 'woovoucher'); ?></a></div>
            <?php
        }

        $preview_url = woo_vou_get_preview_link($post->ID);
        echo '<a href="' . esc_url($preview_url) . '" class="button button-secondary button-large woo-vou-pdf-preview-button" target="_blank">' . esc_html__('Preview', 'woovoucher') . '</a>';
    }

    /**
     * Add Voucher Details meta box within Order
     *
     * @package WooCommerce - PDF Vouchers
     * @since 1.1.0
     */
    public function woo_vou_order_meta_boxes() {

        global $post;

        //get meta prefix
        $prefix = WOO_VOU_META_PREFIX;

        //Get details
        $order_details  = get_post_meta( $post->ID, $prefix .'meta_order_details', true );
        $order_hide_vou = get_post_meta( $post->ID, $prefix .'order_hide_voucher_data', true );

        //Check if voucher order & not setup as hide data
        if( $order_details && ! $order_hide_vou ) {
            add_meta_box('woo-vou-order-voucher-details', esc_html__('Voucher Details', 'woovoucher'), 'woo_vou_display_voucher_data', WOO_VOU_MAIN_SHOP_POST_TYPE, 'normal', 'default');
        }
    }

    /**
     * Download Pdf by admin
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.0.3
     */
    public function woo_vou_admin_voucher_pdf_download() {

        global $current_user;

        if (!empty($_GET['download_file']) && !empty($_GET['key']) && !empty($_GET['woo_vou_admin']) && !empty($_GET['woo_vou_order_id'])) {

            if (current_user_can('moderate_comments')) {

                $product_id = (int) $_GET['download_file'];
                $email = sanitize_email(str_replace(' ', '+', $_GET['email']));
                $download_id = isset($_GET['key']) ? preg_replace('/\s+/', ' ', $_GET['key']) : '';
                $order_id = $_GET['woo_vou_order_id'];
                $item_id = isset($_GET['item_id']) ? $_GET['item_id'] : '';

                //Generate PDF
                $this->voucher->woo_vou_generate_pdf_voucher($email, $product_id, $download_id, $order_id, $item_id);
            } else {

                wp_die('<p>' . esc_html__('You are not allowed to access this URL.', 'woovoucher') . '</p>');
            }

            exit;
        }
    }

    /**
     * Send Gift notification email using cron jobs
     *
     * @package WooCommerce - PDF Vouchers
     * @since 2.8.1
     */
    public function woo_vou_send_gift_notification_email() {

        global $vou_order;

        //Get prefix
        $prefix = WOO_VOU_META_PREFIX;

        // get all orders
        $woo_vou_get_all_orders = get_posts(array(
            'numberposts' => -1,
            'post_type' => 'shop_order',
            'post_status' => array('wc-processing', 'wc-completed'),
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => $prefix . 'recipient_email_sent',
                    'compare' => 'NOT EXISTS',
                    'value' => '' // This is ignored, but is necessary...
                )
            )
        ));

        if (empty($woo_vou_get_all_orders)) {
            return;
        }

        // get option
        $vou_download_gift_mail = get_option('vou_download_gift_mail');
        $vou_attach_gift_mail = get_option('vou_attach_gift_mail');
        $grant_access_after_payment = get_option('woocommerce_downloads_grant_access_after_payment');

        // today date
        $woo_vou_today_date = $this->model->woo_vou_current_date('Y-m-d');


        // loop through orders
        foreach ($woo_vou_get_all_orders as $order_id) {

            // Initialize variable
            $recipient_gift_email_send = true;
            $vou_order = $order_id;
            $cart_details = wc_get_order($order_id); // get order details

            $order_status = woo_vou_get_order_status($cart_details); // Get order status

            if ($order_status == 'processing' && $grant_access_after_payment == 'no') {
                continue;
            }

            $order_items = $cart_details->get_items(); // get order items

            if (!empty($order_items)) { //if item is empty
                foreach ($order_items as $product_item_key => $product_data) {

                    /*
                     * product price shortcode start
                     */
                    $data_id = !empty($product_data['variation_id']) ? $product_data['variation_id'] : $product_data['product_id'];
                    $product_details = $this->model->woo_vou_get_product_details($order_id);
                    $product_price = !empty($product_details[$data_id]['product_formated_price']) ? $product_details[$data_id]['product_formated_price'] : get_woocommerce_currency_symbol() . $product_details[$data_id]['product_price'];
                    
                    //get product quantity
                    $productqty = apply_filters('woo_vou_order_item_qty', $product_data['qty'], $product_data);

                    //Get product item meta
                    $product_item_meta = isset($product_data['item_meta']) ? $product_data['item_meta'] : array();

                    // skip loop if recipient_gift_email_send_item is set to yes
                    if (array_key_exists($prefix . 'recipient_gift_email_send_item', $product_item_meta) && $product_item_meta[$prefix . 'recipient_gift_email_send_item'] == 'yes') {
                        continue;
                    }

                    // get recipient details                    
                    $recipient_details = $this->model->woo_vou_get_recipient_data($product_item_meta);

                    $woo_vou_order_gift_date = !empty($recipient_details['recipient_giftdate']) ? apply_filters('woo_vou_replace_giftdate', $recipient_details['recipient_giftdate'], $order_id, $product_item_key) : '';

                    // skip loop if order item doesn't have gift date
                    if (empty($woo_vou_order_gift_date)) {
                        continue;
                    }

                    // if today date and gift date is same then send gift notification email
                    if (strtotime($woo_vou_today_date) == strtotime($woo_vou_order_gift_date)) {

                        $product_id = isset($product_data['product_id']) ? $product_data['product_id'] : '';
                        $variation_id = isset($product_data['variation_id']) ? $product_data['variation_id'] : '';
                        $vou_voucher_delivery_type = 'email';


                        // Getting Voucher Delivery. This meta will contain voucher delivery selected by admin and not user
                        $woo_vou_all_ordered_data = $this->model->woo_vou_get_all_ordered_data($order_id);

                        if (!empty($variation_id)) { // If this variation then get it's product id
                            $_variation_pro = wc_get_product($variation_id);
                            $parent_product_id = $_variation_pro->get_parent_id();
                            $vou_voucher_delivery_type = $woo_vou_all_ordered_data[$parent_product_id]['voucher_delivery'][$variation_id];
                        } else {

                            if (!empty($woo_vou_all_ordered_data) && array_key_exists($product_id, $woo_vou_all_ordered_data) && !empty($woo_vou_all_ordered_data[$product_id]) && is_array($woo_vou_all_ordered_data[$product_id]) && array_key_exists('voucher_delivery', $woo_vou_all_ordered_data[$product_id])) {

                                $vou_voucher_delivery_type = $woo_vou_all_ordered_data[$product_id]['voucher_delivery'];
                            }
                        }

                        // Get user selected voucher delivery
                        // This will override voucher delivery selected by admin
                        $user_selected_delivery_type = $product_data->get_meta($prefix . 'delivery_method', true);
                        if (!empty($user_selected_delivery_type) && is_array($user_selected_delivery_type) && !empty($user_selected_delivery_type['value'])) {

                            $vou_voucher_delivery_type = $user_selected_delivery_type['value'];
                        }

                        // Apply filter to delivery type
                        $vou_voucher_delivery_type = apply_filters('woo_vou_check_product_delivery_type', $vou_voucher_delivery_type);

                        // skip loop if voucher delivery set to offline
                        if ($vou_voucher_delivery_type == 'offline') {

                            $recipient_gift_email_send = false;
                            continue;
                        }

                        $payment_user_info = $this->model->woo_vou_get_buyer_information($order_id); // Get payment information
                        $first_name = $payment_user_info['first_name']; // Get billing first name
                        $last_name = $payment_user_info['last_name']; // Get billing last name
                        $_product = apply_filters('woocommerce_order_item_product', $cart_details->get_product_from_item($product_data), $product_data);

                        if (!$_product) { //If product deleted
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
                                $links_plain[] = sprintf(esc_html__('Download file%s', 'woovoucher'), ( count($download_file_data) > 1 ? ' ' . $i . ': ' : ': ')) . esc_url($download_url);
                            }
                        }

                        if ($vou_download_gift_mail == 'yes') { //If download enable for gift notification
                            $recipient_details['recipient_voucher'] = '<br/>' . implode('<br/>', $links);
                            $recipient_details['recipient_voucher_plain'] = "\n" . implode("\n", $links_plain);
                        } else {
                            $recipient_voucher = '';
                        }

                        // added filter to send extra emails on diferent email ids by other extensions                        
                        $woo_vou_extra_emails = apply_filters('woo_vou_pdf_recipient_email', false, $product_id);

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

                            if (!empty($vou_attach_gift_mail) && $vou_attach_gift_mail == 'yes') { //If attachment enable for gift notification
                                //Get product/variation ID
                                $product_id = !empty($product_data['variation_id']) ? $product_data['variation_id'] : $product_data['product_id'];

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
                                            'item_id' => $product_item_key,
                                            'pdf_vou_key' => $attach_key,
                                        );
                                        $attach_pdf_file_name = apply_filters('woo_vou_attach_pdf_file_name', $attach_pdf_file_name, $pdf_file_args);

                                        // Remove forward slash from name
                                        $attach_pdf_file_name = str_replace('/', '', $attach_pdf_file_name);

                                        //Voucher attachment path
                                        $vou_pdf_path = WOO_VOU_UPLOAD_DIR . $attach_pdf_file_name . $order_id . '-' . $product_id . '-' . $product_item_key; // Voucher pdf path
                                        // Replacing voucher pdf name with given value
                                        $orderdvoucode_key = str_replace('woo_vou_pdf_', '', $attach_key);

                                        //if user buy more than 1 quantity of voucher
                                        if (isset($productqty) && $productqty > 1) {
                                            $vou_pdf_path .= '-' . $orderdvoucode_key;
                                        }

                                        //if voucher using type is more than one time then generate voucher codes
                                        $vou_using_type = $woo_vou_all_ordered_data[$product_id]['using_type'];
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
                                            $attachments = apply_filters('woo_vou_gift_email_attachments', woo_vou_attach_voucher_to_email(array(), 'customer_processing_order', $cart_details), $order_id, $product_data);
                                        }
                                    }
                                }
                            }

                            


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

                            
                            do_action('woo_vou_gift_email', $gift_data);

                            // Give permission for download voucher to recipient email user
                            woo_vou_permission_download_recipient_user($order_id);

                            // Add new item meta for recording flag that mail is sent or not
                            $product_data->update_meta_data($prefix . 'recipient_gift_email_send_item', 'yes');
                            // Save updated meta
                            $product_data->save_meta_data();
                        }
                    } else if (strtotime($woo_vou_today_date) < strtotime($woo_vou_order_gift_date)) {

                        $recipient_gift_email_send = false;
                    }
                }

                if ($recipient_gift_email_send) {
                    //Update post meta for email attachment issue
                    update_post_meta($order_id, $prefix . 'recipient_email_sent', true);
                }

                // Add action after gift email is sent
                do_action('woo_vou_after_gift_email', $order_id);
            }
        }
    }

    /**
     * Handles to insert html fieldsa on
     * coupon add / edit page
     *
     * @package WooCommerce - PDF Vouchers
     * @since 2.9.2
     */
    public function woo_vou_coupon_options() {

        global $post;

        $prefix = WOO_VOU_META_PREFIX;

        // Disable redeem voucher
        $redeem_days = array(
            'Monday' => esc_html__('Monday', 'woovoucher'),
            'Tuesday' => esc_html__('Tuesday', 'woovoucher'),
            'Wednesday' => esc_html__('Wednesday', 'woovoucher'),
            'Thursday' => esc_html__('Thursday', 'woovoucher'),
            'Friday' => esc_html__('Friday', 'woovoucher'),
            'Saturday' => esc_html__('Saturday', 'woovoucher'),
            'Sunday' => esc_html__('Sunday', 'woovoucher')
        );

        // Get coupon type
        $woo_vou_coupon_type = get_post_meta($post->ID, $prefix . 'coupon_type', true);

        // Start date
        woocommerce_wp_text_input(array('id' => $prefix . 'start_date', 'label' => esc_html__('Coupon start date', 'woovoucher'), 'placeholder' => _x('YYYY-MM-DD', 'placeholder', 'woovoucher'), 'description' => '', 'class' => 'date-picker', 'custom_attributes' => array('pattern' => "[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])")));

        if (!empty($post)) { // if post is not empty
            $post_id = $post->ID; // Get post id
            // Get meta
            $coupon_type = get_post_meta($post_id, $prefix . 'coupon_type', true); // Get coupon type
        }
        ?>
        <p class="form-field"><label for="product_categories"><?php esc_html_e('Choose which days coupon can not be used', 'woovoucher'); ?></label>
            <select id="disable_redeem_days" name="<?php echo $prefix; ?>disable_redeem_days[]" data-width="50%" class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e('Select days', 'woovoucher'); ?>">
                <?php
                $rest_days_meta = (array) get_post_meta($post->ID, $prefix . 'disable_redeem_day', true);

                if ($redeem_days)
                    foreach ($redeem_days as $redeem_day_key => $redeem_day_val) {
                        echo '<option value="' . $redeem_day_key . '"' . selected(in_array($redeem_day_key, $rest_days_meta), true, false) . '>' . $redeem_day_val . '</option>';
                    }
                ?>
            </select> <?php echo wc_help_tip(esc_html__('If you want to restrict use of Coupon Code for specific days, you can select days here. Leave it blank for no restriction.', 'woovoucher')); ?></p>
        <?php

        $discount_apply_options = apply_filters( 'woo_vou_product_coupon_discount_options', array(
            '' => esc_html__( 'Default', 'woovoucher' ),
            'subtotal' => esc_html__( 'Cart SubTotal', 'woovoucher' ),
            ) );

        $default = sprintf(esc_html__('%sDefault:%s Discount will be applied as per default WooCommerce coupons.', 'woovoucher'), '<b>','</b>');

        $cart_subtotal = sprintf( esc_html__('%sCart Subtotal:%s Discount will be applied on cart subtotal.', 'woovoucher'), '<b>','</b>');

         woocommerce_wp_select(array(
                            'id'          => $prefix.'discount_on_tax_type',
                            'label'       => __( 'Discount on:', 'woocommerce' ),
                            'options'     => $discount_apply_options,
                            'class'       => 'select short',
                            'desc_tip' => true,
                            'description' => esc_html__( 'Select the option on which the discount will be applied.', 'woovoucher' ),
                        ));

        print '<p class="discount-on-desc">'.$default.'<br>'.$cart_subtotal.'</p>';
        // Coupon type
        if (!empty($woo_vou_coupon_type) && $woo_vou_coupon_type == 'voucher_code') {
            echo '<p class="form-field"><label for="product_categories">' . esc_html__('Coupon Type', 'woovoucher') . '</label>';
            echo '<span>' . esc_html__('Voucher Code', 'woovoucher') . '</span></p>';
        }
    }

    /**
     * Handles to add voucher price in product meta
     * under sale price
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.9.8
     */
    public function woo_vou_product_options_add_voucher_price() {

        $prefix = WOO_VOU_META_PREFIX; // Get prefix

        $price_options = get_option('vou_voucher_price_options'); // Get voucher price options
        // Check if price_option is set to voucher price
        if (!empty($price_options) && $price_options == 2) {

            // Add Voucher Price field below Sale Price field in product meta settings
            woocommerce_wp_text_input(array('id' => $prefix . 'voucher_price', 'label' => esc_html__('Voucher price', 'woovoucher') . ' (' . get_woocommerce_currency_symbol() . ')', 'data_type' => 'price'));
        }
    }

    /**
     * Handles to generate and download system log file
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 2.9.8
     */
    public function woo_vou_generate_system_log() {

        // If post data is set
        if (!empty($_GET['tab']) && $_GET['tab'] == 'woo-vou-settings' && !empty($_GET['woo_vou_gen_sys_log']) && $_GET['woo_vou_gen_sys_log'] == 1) {

            // Voucher price options
            $voucher_price_options = array(
                '' => esc_html__('Sale Price', 'woovoucher'),
                '1' => esc_html__('Regular Price', 'woovoucher'),
                '2' => esc_html__('Custom Voucher Price', 'woovoucher')
            );

            // get all required options to show in system log
            $delete_option = get_option('vou_delete_options');
            $enable_partial_redeem = get_option('vou_enable_partial_redeem');
            $enable_coupon_code = get_option('vou_enable_coupon_code');
            $multiple_pdf = get_option('multiple_pdf');
            $revoke_used_exp_vou_link = get_option('revoke_voucher_download_link_access');
            $vou_attach_processing_mail = get_option('vou_attach_processing_mail');
            $vou_attach_gift_mail = get_option('vou_attach_gift_mail');
            $vou_download_processing_mail = get_option('vou_download_processing_mail');
            $vou_download_gift_mail = get_option('vou_download_gift_mail');
            $vou_download_dashboard = get_option('vou_download_dashboard');
            $allow_redeem_expired_vou = get_option('vou_allow_redeem_expired_voucher');
            $vou_voucher_price_options = get_option('vou_voucher_price_options');
            $vou_allow_bcc_to_admin = get_option('vou_allow_bcc_to_admin');
            $enable_vendor_access_all_voucodes = get_option('vou_enable_vendor_access_all_voucodes');
            $vou_enable_relative_path = get_option('vou_enable_relative_path');

            // Open new file
            $handle = fopen("pdf-voucher-system-report.txt", "w");

            // Start writing data in our file
            $log_data = '--- WPWeb PDF Vouchers Log Information ---';

            // Declare woocommerce variables to use for getting system data
            $system_status = new WC_REST_System_Status_Controller;
            $environment = $system_status->get_environment_info();
            $database = $system_status->get_database_info();
            $active_plugins = $system_status->get_active_plugins();
            $theme = $system_status->get_theme_info();

            // HTML for WordPress environment
            $log_data .= "\n\n" . esc_html__('--- WordPress Environment ---', 'woovoucher');
            $log_data .= "\n" . esc_html__('Home URL: ', 'woovoucher') . $environment['home_url'];
            $log_data .= "\n" . esc_html__('WorPress Version: ', 'woovoucher') . $environment['wp_version'];
            $log_data .= "\n" . esc_html__('WooCommerce Version: ', 'woovoucher') . $environment['version'];
            $log_data .= "\n" . esc_html__('WP Debug Mode: ', 'woovoucher') . ( $environment['wp_debug_mode'] ? esc_html__('Yes', 'woovoucher') : esc_html__('No', 'woovoucher') );
            $log_data .= "\n" . esc_html__('WP cron: ', 'woovoucher') . ( $environment['wp_cron'] ? esc_html__('Yes', 'woovoucher') : esc_html__('No', 'woovoucher') );

            // HTML for Server environment
            $log_data .= "\n\n" . esc_html__('--- Server Environment ---', 'woovoucher');
            $log_data .= "\n" . esc_html__('PHP Version: ', 'woovoucher') . $environment['php_version'];
            $log_data .= "\n" . esc_html__('WC Database Version: ', 'woovoucher') . $database['wc_database_version'];
            $log_data .= "\n" . esc_html__('fsockopen/cURL: ', 'woovoucher') . ( $environment['fsockopen_or_curl_enabled'] ? esc_html__('Yes', 'woovoucher') : esc_html__('No', 'woovoucher') );

            // HTML for Active plugins
            $log_data .= "\n\n" . esc_html__('--- Active Plugins ---', 'woovoucher');
            foreach ($active_plugins as $plugin) {

                if (!empty($plugin['name'])) {
                    $dirname = dirname($plugin['plugin']);

                    // Link the plugin name to the plugin url if available.
                    $plugin_name = esc_html($plugin['name']);

                    $version_string = '';
                    $network_string = '';
                    if (strstr($plugin['url'], 'woothemes.com') || strstr($plugin['url'], 'woocommerce.com')) {
                        if (!empty($plugin['version_latest']) && version_compare($plugin['version_latest'], $plugin['version'], '>')) {
                            $version_string = ' - (' . sprintf(esc_html__('%s is available', 'woovoucher'), $plugin['version_latest']) . ')';
                        }

                        if (false != $plugin['network_activated']) {
                            $network_string = ' - (' . esc_html__('Network enabled', 'woovoucher') . ')';
                        }
                    }

                    $log_data .= "\n" . $plugin_name . esc_html__(' by ', 'woovoucher') . $plugin['author_name'] . ' - ' . esc_html($plugin['version']) . $version_string . $network_string;
                }
            }

            // HTML for Active theme
            $log_data .= "\n\n" . esc_html__('--- Active Theme ---', 'woovoucher');
            $log_data .= "\n" . esc_html__('Theme Name: ', 'woovoucher') . $theme['name'];
            $log_data .= "\n" . esc_html__('Version: ') . $theme['version'];
            $log_data .= "\n" . esc_html__('Author URL: ') . $theme['author_url'];
            $log_data .= "\n" . esc_html__('Child theme: ') . ( $theme['is_child_theme'] ? esc_html__('Yes', 'woovoucher') : esc_html__('No', 'woovoucher') );

            // HTML for Plugin settings
            $log_data .= "\n\n" . esc_html__('--- Plugin Settings ---', 'woovoucher');
            $log_data .= "\n" . esc_html__("Delete Option: ", 'woovoucher') . (!empty($delete_option) ? ucfirst($delete_option) : esc_html__('No', 'woovoucher') );
            $log_data .= "\n" . esc_html__("Enable Partial Redemption: ", 'woovoucher') . (!empty($enable_partial_redeem) ? ucfirst($enable_partial_redeem) : esc_html__('No', 'woovoucher') );
            $log_data .= "\n" . esc_html__("Auto Enable Coupon Code Generation: ", 'woovoucher') . (!empty($enable_coupon_code) ? ucfirst($enable_coupon_code) : esc_html__('No', 'woovoucher') );
            $log_data .= "\n" . esc_html__("Multiple Voucher: ", 'woovoucher') . (!empty($multiple_pdf) ? ucfirst($multiple_pdf) : esc_html__('No', 'woovoucher') );
            $log_data .= "\n" . esc_html__("Remove Used / Expired Voucher Download Link: ", 'woovoucher') . (!empty($revoke_used_exp_vou_link) ? ucfirst($revoke_used_exp_vou_link) : esc_html__('No', 'woovoucher') );
            $log_data .= "\n" . esc_html__("Voucher Attachment in Processing Order Mail: ", 'woovoucher') . (!empty($vou_attach_processing_mail) ? ucfirst($vou_attach_processing_mail) : esc_html__('No', 'woovoucher') );
            $log_data .= "\n" . esc_html__("Voucher Attachment in Gift Notification Mail: ", 'woovoucher') . (!empty($vou_attach_gift_mail) ? ucfirst($vou_attach_gift_mail) : esc_html__('No', 'woovoucher') );
            $log_data .= "\n" . esc_html__("Voucher Download from Processing Order Mail: ", 'woovoucher') . (!empty($vou_download_processing_mail) ? ucfirst($vou_download_processing_mail) : esc_html__('No', 'woovoucher') );
            $log_data .= "\n" . esc_html__("Voucher Download from Gift Notification Mail: ", 'woovoucher') . (!empty($vou_download_gift_mail) ? ucfirst($vou_download_gift_mail) : esc_html__('No', 'woovoucher') );
            $log_data .= "\n" . esc_html__("Voucher Download from Customer Dashboard: ", 'woovoucher') . (!empty($vou_download_dashboard) ? ucfirst($vou_download_dashboard) : esc_html__('No', 'woovoucher') );
            $log_data .= "\n" . esc_html__("Allow Redeem for Expired Vouchers: ", 'woovoucher') . (!empty($allow_redeem_expired_vou) ? ucfirst($allow_redeem_expired_vou) : esc_html__('No', 'woovoucher') );
            $log_data .= "\n" . esc_html__("Default Voucher Value: ", 'woovoucher') . $voucher_price_options[$vou_voucher_price_options];
            $log_data .= "\n" . esc_html__("Send Emails to Admin: ", 'woovoucher') . (!empty($vou_allow_bcc_to_admin) ? ucfirst($vou_allow_bcc_to_admin) : esc_html__('No', 'woovoucher') );
            $log_data .= "\n" . esc_html__("Enable Vendors to Access all Voucher Codes: ", 'woovoucher') . (!empty($enable_vendor_access_all_voucodes) ? ucfirst($enable_vendor_access_all_voucodes) : esc_html__('No', 'woovoucher') );
            $log_data .= "\n" . esc_html__("Enable Relative Path: ", 'woovoucher') . (!empty($vou_enable_relative_path) ? ucfirst($vou_enable_relative_path) : esc_html__('No', 'woovoucher') );

            fwrite($handle, $log_data);
            fclose($handle);

            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename('pdf-voucher-system-report.txt') . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize('pdf-voucher-system-report.txt'));
            readfile('pdf-voucher-system-report.txt');
            unlink("pdf-voucher-system-report.txt");
            exit;
        }
    }

    /**
     * Display license activation notice
     * 
     * On Dismiss plugin will expire notice for 30 days. If plugin updated to new version then 
     * it will display notice again.
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 3.2.5
     */
    public function woo_vou_license_activating_notice() {
        $user_id = get_current_user_id();
        $woovoudeactivationmsg = get_user_meta( $user_id, 'woo_vou_dismiss_license_activation', true);
        if (!$this->model->woo_vou_is_activated() &&
                ( empty($woovoudeactivationmsg) || version_compare($woovoudeactivationmsg, WOO_VOU_PLUGIN_VERSION, '<') )) {

            wp_enqueue_script( 'woo-vou-plugin-updater-notice-script' );
            $redirect = add_query_arg(array('page' => 'wpweb-upd-helper'), esc_url(( is_multisite() ? network_admin_url() : admin_url())));
            echo '<div class="updated woo_vou_license-activation-notice" id="woo_vou_license-activation-notice"><p>' . sprintf(esc_html__('Hola! Would you like to receive automatic updates? Please %1$sactivate your copy%2$s of WooCommerce - PDF Vouchers.', 'woovoucher'), '<a href="'.esc_url($redirect).'">', '</a>') . '</p>' . '<button type="button" class="notice-dismiss woo-vou-notice-dismiss"><span class="screen-reader-text">' . esc_html__('Dismiss this notice.', 'woovoucher') . '</span></button></div>';
        }
    }

    /**
     * Display WPWEB Upgrade notice
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.2.5
     */
    public function woo_vou_check_wpweb_updater_upgrate_notice() {
        ?>
        <div class="error fade notice is-dismissible" id="woo-wpweb-upgrade-notice">
            <p><?php echo sprintf(esc_html__('WooCommerce - PDF Voucher requires WPWEB Updater version greater then 1.0.4. Please Upgrade to latest version.', 'woovoucher'), $redirect); ?></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.', 'woovoucher'); ?></span></button>
        </div>
        <?php
    }

    /**
     * Check WPWEB Updater v1.0.4 or old version activated
     *
     * If yes then Deactivated WPWEB updater plugin and display notice to install latest updater plugin
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.2.5
     */
    public function woo_vou_check_wpweb_updater_activation() {

        // if WPWEB Updater is activated
        if (class_exists('Wpweb_Upd_Admin') && version_compare(WPWEB_UPD_VERSION, '1.0.5', '<')) {
            // deactivate the WPWEB Updater plugin
            deactivate_plugins('wpweb-updater/wpweb-updater.php');
            // Display notice of WPWEB Updater older version
            add_action('admin_notices', array($this, 'woo_vou_check_wpweb_updater_upgrate_notice'));
        }
    }

    /**
     * Handles to partial redeem popup 
     *
     * Function handles to view partial redeem popup after voucher display settings
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.3.1
     */
    public function woocommerce_settings_vou_display_settings() {

        ob_start();
        include_once( WOO_VOU_ADMIN . '/forms/woo-vou-partial-redeem-popup.php' ); // Including purchased voucher code file
        $html = ob_get_clean();

        echo $html;
    }

    /**
     * 
     * Handles to update list the Voucher Information 
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 3.3.1
     */
    public function woo_vou_codes_page_voucher_information_update() {

        global $woo_vou_model;

        $prefix = WOO_VOU_META_PREFIX; // Get prefix
        // If update the recipient details
        if (isset($_REQUEST['woo_vou_voucher_information_update']) && isset($_REQUEST['woo_vou_order_id'])) {
			
            // Get data from submited form
            $woo_vou_order_id = $_REQUEST['woo_vou_order_id'];
            $woo_vou_item_id = $_REQUEST['woo_vou_item_id'];
            $woo_vou_product_id = $_REQUEST['woo_vou_product_id'];
            $woo_vou_code_id = $_REQUEST['woo_vou_code_id'];
            $woo_new_vendor_logo = $_REQUEST[$prefix . 'logo'];
            $woo_new_vendor_address = $woo_vou_model->woo_vou_escape_slashes_deep($_REQUEST[$prefix . 'vendor_address']);
            $woo_new_voucher_website_url = $woo_vou_model->woo_vou_escape_slashes_deep($_REQUEST[$prefix . 'voucher_website_url']);
            $woo_new_voucher_redeem = $woo_vou_model->woo_vou_escape_slashes_deep($_REQUEST[$prefix . 'voucher_redeem']);
            $woo_new_voucher_expires_date = $woo_vou_model->woo_vou_escape_slashes_deep($_REQUEST[$prefix . 'voucher_expires_date']);

            $meta_order_details = get_post_meta($woo_vou_order_id, $prefix . 'meta_order_details', true);
            if (isset($meta_order_details[$woo_vou_item_id])) {

                // Replace the vocher information
                $meta_order_details[$woo_vou_item_id]['vendor_logo'] = $woo_new_vendor_logo;
                $meta_order_details[$woo_vou_item_id]['website_url'] = $woo_new_voucher_website_url;
                $meta_order_details[$woo_vou_item_id]['redeem'] = $woo_new_voucher_redeem;
                $meta_order_details[$woo_vou_item_id]['exp_date'] = $woo_new_voucher_expires_date;

                if ($woo_vou_product_id == $woo_vou_item_id) { // Replace vender address if is the simple product
                    $meta_order_details[$woo_vou_item_id]['vendor_address'] = $woo_new_vendor_address;
                } elseif (!empty($woo_vou_product_id)) { // Replace the vender address with product variation id if is the variable
                    $meta_order_details[$woo_vou_item_id]['vendor_address'][$woo_vou_product_id] = $woo_new_vendor_address;
                }
            }
            // Update all meta details in order meta
            update_post_meta($woo_vou_order_id, $prefix . 'meta_order_details', $meta_order_details);
            // Update expire date in voucher meta
            $woo_vou_expires_date_meta = date('Y-m-d G:H:s', strtotime($woo_new_voucher_expires_date));
            update_post_meta($woo_vou_code_id, $prefix . 'exp_date', $woo_vou_expires_date_meta);
        }
    }

    /**
     * Handles to load product based on search criteria
     * and page specified
     * 
     * @package WooCommerce - PDF Vouchers
     * @since 3.3.1
     */
    public function woo_vou_load_products() {

        $search_by = wc_clean($_POST['search_by']); // Get search by
        $page = !empty($_POST['page']) ? wc_clean($_POST['page']) : 1; // Get page required
        // Initialising variables
        $vou_enable_array = array();
        $html = '';
        $product_counter = 1;
        $products_list_2 = false;

        // Get all ids for page and search by
        $ids = $this->model->woo_vou_search_products($page, $search_by);

        // Loop on all ids to get only simple / parent products (No product variants will be included)
        foreach ($ids as $id) {

            if (empty($id))
                continue;

            $_product = wc_get_product($id); // Get product
            $_variation_id = $is_variable = ''; // Declaring variables
            // if product is variable or variation
            if ($_product->is_type('variable') || $_product->is_type('variation')) {

                $vou_enable_array[$id] = $_product->get_children();
            } else {

                $vou_enable_array[$id] = $id;
            }
        }

        // Start output buffer
        ob_start();

        // If product array is not empty
        if (!empty($vou_enable_array)) {

            echo '<div class="woo-vou-product-list">';

            $total_products = count($vou_enable_array) / 2;
            $vou_partial_redeem_product_ids = !empty($_POST['total_selected_pros']) ? $_POST['total_selected_pros'] : '';
            $vou_partial_redeem_product_ids_array = explode(',', $vou_partial_redeem_product_ids);

            /// Loop on all products ids
            foreach ($vou_enable_array as $product_id => $product_variable_ids) {

                $_product = wc_get_product($product_id); // Get product
                if ($product_counter == 1) {

                    echo '<ul class="woo_vou_product_partial_list">';
                }

                $_product_title = $_product->get_title(); // Get product title
                // If length is greater than 35 than remove extra characters
                if (strlen($_product_title) > 35) {

                    $_product_title = substr($_product_title, 0, 35);
                    $_product_title = $_product_title . '...';
                }

                // If current product counter is greater-than half total product
                if (( $total_products < $product_counter ) && ( $products_list_2 == false )) {

                    $products_list_2 = true;
                    echo '</ul><ul class="woo_vou_product_partial_list">';
                }

                // Loop on product variants to show variations
                if (is_array($product_variable_ids)) {

                    echo ' <li>';
                    echo '  <input type="checkbox" class="woo-vou-product-partial-input woo-vou-product-variation-parent" value="' . $product_id . '" />';
                    echo '  <a class="woo-vou-variable-parent" href="' . esc_url(get_edit_post_link($product_id)) . '" target="_blank">' . '#' . $product_id . ' - ' . $_product_title . '</a>';
                    echo '   <p class="woo-vou-variation-wrapper">';
                    echo '<span class="woo-vou-toggle-variations woo-vou-plus"></span>';
                    echo '</p>';
                    echo '<ul class="woo-vou-product-variation-list">';

                    // Loop on all variable ids
                    foreach ($product_variable_ids as $variation_id) {

                        $variable_product = wc_get_product($variation_id); // Get variable product
                        $_variation_title = $variable_product->get_name(); // Get product title
                        $checked = ( in_array($variation_id, $vou_partial_redeem_product_ids_array) ) ? 'checked="checked"' : ''; // Check if ir is already checked
                        // If length is greater than 35 than remove extra characters
                        if (strlen($_variation_title) > 35) {

                            $_variation_title = substr($_variation_title, 0, 35);
                            $_variation_title = $_variation_title . '...';
                        }

                        echo '<li>';
                        echo '<input type="checkbox" class="woo-vou-product-partial-input woo-vou-product-variation woo-vou-product-parent-' . $product_id . '" id="woo_vou_product_partial_' . $variation_id . '" name="woo_vou_product_partial[]" value="' . $variation_id . '" ' . $checked . ' />';
                        echo '<label for="woo_vou_product_partial_' . $variation_id . '"> #' . $variation_id . ' - ' . $_variation_title . '</label>';
                        echo '</li>';
                    }
                    echo '</ul>';
                    echo '</li>';
                } else { // If product is simple than generate it's html
                    $checked = ( in_array($product_id, $vou_partial_redeem_product_ids_array) ) ? 'checked="checked"' : '';
                    echo '<li>';
                    echo '<input type="checkbox" class="woo-vou-product-partial-input" id="woo_vou_product_partial_' . $product_id . '" name="woo_vou_product_partial[]" value="' . $product_id . '" ' . $checked . ' />';
                    echo '<label for="woo_vou_product_partial_' . $product_id . '"><a href="' . esc_url(get_edit_post_link($product_id)) . '" target="_blank"> #' . $product_id . ' - ' . esc_html__($_product_title, 'woovoucher') . '</a></label>';
                    echo '</li>';
                }

                if ($product_counter == $total_products) {

                    echo '</ul>';
                }

                $product_counter++;
            }
            echo '</div>';
        } else { // If no product is available
            echo '<div class="woo-vou-no-more-products"></div>';
        }

        // Echo out html
        $html .= ob_get_clean();
        echo $html;
        exit;
    }

    /**
     * Handles to add dashboard widget
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.5.0
     */
    public function woo_vou_add_dashboard_widgets() {
        wp_add_dashboard_widget('dashboard_widget', esc_html__('WooCommerce PDF Vouchers Status', 'woovoucher'), array($this, 'woo_vou_dashboard_widget_function'));
    }

    /**
     * Handles to generate dashboard widget html
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.5.0
     */
    public function woo_vou_dashboard_widget_function($post, $callback_args) {
        //echo "Hello World, this is my first Dashboard Widget!";

        global $wpdb, $current_user, $woo_vou_vendor_role, $woo_vou_model, $woo_vou_render, $woo_vou_voucher;

        $prefix = WOO_VOU_META_PREFIX;
        $args = $data = $search_meta = $date_query = array();

        //Current user role
        $user_roles = isset($current_user->roles) ? $current_user->roles : array();
        $user_role = array_shift($user_roles);

        //voucher admin roles
        $admin_roles = woo_vou_assigned_admin_roles();

        // Get option whether to allow all vendor to redeem voucher codes
        $vou_enable_vendor_access_all_voucodes = get_option('vou_enable_vendor_access_all_voucodes');

        //Get voucher page url
        $voucher_codes_page_url = add_query_arg(array('page' => 'woo-vou-codes'), admin_url('admin.php'));

        // Get Purchased voucher codes
        $purchased_codes_args = array(
            'posts_per_page' => -1,
            'woo_vou_list' => true,
            'meta_query' => array(
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
                    'relation' => 'OR',
                    array(
                        'key' => $prefix . 'exp_date',
                        'value' => '',
                        'compare' => '='
                    ),
                    array(
                        'key' => $prefix . 'exp_date',
                        'compare' => '>=',
                        'value' => $woo_vou_model->woo_vou_current_date()
                    )
                )
            )
        );
        // admin can view all voucher codes
        if (!in_array($user_role, $admin_roles) && ( $vou_enable_vendor_access_all_voucodes != 'yes' )) {
            $purchased_codes_args['author'] = $current_user->ID;
        }
        $purchased_codes_data = woo_vou_get_voucher_details($purchased_codes_args);
        $purchased_codes_total = isset($purchased_codes_data['total']) ? $purchased_codes_data['total'] : '';

        // Get Used voucher codes
        $used_codes_args = array(
            'posts_per_page' => -1,
            'woo_vou_list' => true,
            'meta_query' => array(
                array(
                    'key' => $prefix . 'used_codes',
                    'value' => '',
                    'compare' => '!='
                )
            )
        );
        // admin can view all voucher codes
        if (!in_array($user_role, $admin_roles) && ( $vou_enable_vendor_access_all_voucodes != 'yes' )) {
            $used_codes_args['author'] = $current_user->ID;
        }
        $used_codes_data = woo_vou_get_voucher_details($used_codes_args);
        $used_codes_total = isset($used_codes_data['total']) ? $used_codes_data['total'] : '';

        // Get Unused voucher codes
        $expired_codes_args = array(
            'posts_per_page' => -1,
            'woo_vou_list' => true,
            'meta_query' => array(
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
                ),
                array(
                    'key' => $prefix . 'exp_date',
                    'value' => '',
                    'compare' => '!='
                )
            )
        );
        // admin can view all voucher codes
        if (!in_array($user_role, $admin_roles) && ( $vou_enable_vendor_access_all_voucodes != 'yes' )) {
            $expired_codes_args['author'] = $current_user->ID;
        }
        $expired_codes_data = woo_vou_get_voucher_details($expired_codes_args);
        $expired_codes_total = isset($expired_codes_data['total']) ? $expired_codes_data['total'] : '';
        ?>
        <ul class="woo-vou-status-list">
            <?php if (isset($purchased_codes_total)) { ?>
                <li class="woo-vou-purchased-codes woo-vou-vou-code-wrapper">
                    <a href="<?php echo esc_url(add_query_arg(array('vou-data' => 'purchased'), $voucher_codes_page_url) ); ?>">
                        <div class="woo-vou-dashboard-widget-purchased-codes"><?php echo $purchased_codes_total; ?></div>
                        <span class="woo-vou-purchased-code-text"><?php echo esc_html__('Purchased Vouchers', 'woovoucher'); ?></span>
                    </a>
                </li>
            <?php } ?>

            <?php if (isset($used_codes_total)) { ?>
                <li class="woo-vou-used-codes woo-vou-vou-code-wrapper">
                    <a href="<?php echo esc_url( add_query_arg(array('vou-data' => 'used'), $voucher_codes_page_url) ); ?>">
                        <div class="woo-vou-dashboard-widget-used-codes"><?php echo $used_codes_total; ?></div>
                        <span class="woo-vou-used-code-text"><?php echo esc_html__('Used Vouchers', 'woovoucher'); ?></span>
                    </a>
                </li><?php } ?>

            <?php if (isset($expired_codes_total)) { ?>
                <li class="woo-vou-unused-codes woo-vou-vou-code-wrapper">
                    <a href="<?php echo esc_url(add_query_arg(array('vou-data' => 'expired'), $voucher_codes_page_url) ); ?>">
                        <div class="woo-vou-dashboard-widget-unused-codes"><?php echo $expired_codes_total; ?></div>
                        <span class="woo-vou-unused-code-text"><?php echo esc_html__('Unused Vouchers', 'woovoucher'); ?></span>
                    </a>
                </li><?php } ?>
        </ul>
        <?php
    }

    /**
     * Display notice if woocommerce_uploads directory not exists
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.5.5
     */
    public function woo_vou_display_pdf_uploads_directory_notice() {
        $upload_dir = wp_upload_dir();
        $upload_path = isset($upload_dir['basedir']) ? $upload_dir['basedir'] . '/woocommerce_uploads/wpv-uploads/' : ABSPATH;
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo sprintf(esc_html__('Error: Could not create directory %2$s%1$s%3$s', 'woovoucher'), $upload_path, '<code>','</code>'); ?></p>
        </div>
        <?php
    }

    /**
     * Display notice if preview_upload directory not exists
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.5.5
     */
    public function woo_vou_display_preview_upload_directory_notice() {
        $upload_dir = wp_upload_dir();
        $upload_path = isset($upload_dir['basedir']) ? $upload_dir['basedir'] . '/wpv-preview-uploads/' : ABSPATH;
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo sprintf(esc_html__('Error: Could not create directory %2$s%1$s%3$s', 'woovoucher'), $upload_path, '<code>','</code>'); ?></p>            
        </div>
        <?php
    }

    /**
     * Check required directory exists or not. If not then create it. 
     * If not able to create then display warning.
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.6.3
     */
    public function woo_vou_check_directory_exists() {

        // Create directory if not exist. Retrun false if not able to create directory
        if (!wp_mkdir_p(WOO_VOU_UPLOAD_DIR)) {
            add_action('admin_notices', array($this, 'woo_vou_display_pdf_uploads_directory_notice'));
        }

        // Create directory if not exist. Retrun false if not able to create directory
        if (!wp_mkdir_p(WOO_VOU_PREVIEW_UPLOAD_DIR)) {
            add_action('admin_notices', array($this, 'woo_vou_display_preview_upload_directory_notice'));
        }
    }

    /**
     * Check if the Itheme security plugin installed then return rules for htaccess. 
     * Hooks function for Wp rocket
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.7.0
     */
    public function woo_vou_itheme_security_htaccess_rule( $rules ) {

        if( class_exists( 'ITSEC_Core' ) ) {
            $rules = woo_vou_htaccess_rule_string();
        }

        return $rules;
    }

    /**
     * Handle to show admin notice on order page
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.8.7
     */
    public function woo_vou_check_order_admin_notice(){

        if( isset( $_GET['post'] ) && !empty( $_GET['post'] ) ){
            
            $post_id = $_GET['post'];

            $post_type = get_post_type( $post_id );

            if( !empty( $post_type ) && $post_type == 'shop_order') {

                $order = new WC_Order( $post_id );
                $status = $order->get_status();

                $is_cancel_to_complete = get_post_meta( $post_id, '_woo_vou_order_hide_voucher_data', true );
                $is_pdf_voucher = get_post_meta( $post_id, '_woo_vou_multiple_pdf', true );
                
                if( $status == 'cancelled' &&  !empty( $is_cancel_to_complete ) && !empty( $is_pdf_voucher ) ){
                    add_action('admin_notices', array($this, 'woo_vou_display_cancel_order_notice'));
                }
                elseif( $status == 'completed' && !empty( $is_cancel_to_complete ) && !empty( $is_pdf_voucher ) ){
                    add_action('admin_notices', array($this, 'woo_vou_display_complete_order_notice'));
                }

            }
        }
    }

    /**
     * Handle to show admin notice on order page if order is cancel
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.8.7
     */
    public function woo_vou_display_cancel_order_notice(){

      $user_id = get_current_user_id();
      $woo_voo_order_cancelled = get_user_meta( $user_id, 'woo_vou_dismiss_order_cancelled', true);

      if( empty( $_COOKIE['woo_voo_order_cancelled'] ) ){
        wp_enqueue_script( 'woo-vou-admin-common-script' );
     ?>
        <div id="woo-voo-order-cancel-notice-dismiss" class="error notice woo-voo-order-status-notice-position">
            <p><?php echo esc_html__('When order cancelled, voucher(s) is get restored. If you will change order status to completed then voher will be not generated again.', 'woovoucher'); ?></p>
            <button type="button" class="notice-dismiss woo-voo-order-cancel-notice-dismiss"><span class="screen-reader-text">'<?php esc_html_e( 'Dismiss this notice.', 'wpwautoposter' );?></span></button>
        </div>
    <?php
        }
    }

    /**
     * Handle to show admin notice on order page if order is complete from cancel
     *
     * @package WooCommerce - PDF Vouchers
     * @since 3.8.7
    */
    public function woo_vou_display_complete_order_notice(){
     
     $user_id = get_current_user_id();
     $woo_voo_order_complete = get_user_meta( $user_id, 'woo_vou_dismiss_order_complete', true);

     if( empty( $woo_voo_order_complete ) ){
        wp_enqueue_script( 'woo-vou-admin-common-script' );
     ?>
        <div id="woo-voo-order-complete-notice-dismiss" class="error notice woo-voo-order-status-notice-position">
            <p><?php echo esc_html__('You have changed order status from cancelled to completed, so you will not see voucher as it\'s restored when order was cancelled', 'woovoucher'); ?></p>
            <button type="button" class="notice-dismiss woo-voo-order-complete-notice-dismiss"><span class="screen-reader-text">'<?php esc_html_e( 'Dismiss this notice.', 'wpwautoposter' );?></span></button>
        </div>
        <?php
        }
    }


     /**
     * Handle to set dismiss licence activation notice
     *
     * @package WooCommerce - PDF Vouchers
     * @since 4.1.5
    */
    public function woo_vou_dismiss_license_activation(){
        if( isset( $_GET['process'] ) && $_GET['process'] == 'set_dismiss_data' ){
            $user_id = get_current_user_id();
            update_user_meta( $user_id, 'woo_vou_dismiss_license_activation',WOO_VOU_PLUGIN_VERSION);
        }
        
    }

    public function woo_vou_dismiss_order_complete(){
        if( isset( $_GET['process'] ) && $_GET['process'] == 'woo_voo_order_complete' ){
            $user_id = get_current_user_id();
            update_user_meta( $user_id, 'woo_vou_dismiss_order_complete','1');
        }
    }


    public function woo_vou_dismiss_order_cancelled(){
        if( isset( $_GET['process'] ) && $_GET['process'] == 'woo_voo_order_cancelled' ){
            $user_id = get_current_user_id();
            update_user_meta( $user_id, 'woo_vou_dismiss_order_cancelled','1');
        }
    }

    


    /**
     * Adding Hooks
     *
     * @package WooCommerce - PDF Vouchers
     * @since 1.0.0
     */
    public function add_hooks() {

        if (woo_vou_is_edit_page()) {

            //add content for import voucher codes in footer
            add_action('admin_footer', 'woo_vou_import_footer');
        }


        //add action to import csv file for codes with Ajaxform
        add_action('init', 'woo_vou_import_codes');

        //add submenu page
        add_action('admin_menu', array($this, 'woo_vou_admin_submenu'));

        //AJAX action for import code
        add_action('wp_ajax_woo_vou_import_code', 'woo_vou_import_code');
        add_action('wp_ajax_nopriv_woo_vou_import_code', 'woo_vou_import_code');

        //add new field to voucher listing page
        add_action('manage_' . WOO_VOU_POST_TYPE . '_posts_custom_column', array($this, 'woo_vou_manage_custom_column'), 10, 2);
        add_filter('manage_edit-' . WOO_VOU_POST_TYPE . '_columns', array($this, 'woo_vou_add_new_columns'));

        //add action to add custom metaboxes on voucher template post type
        add_action('add_meta_boxes', array($this, 'woo_vou_editor_meta_box'));

        //saving voucher meta on update or publish voucher template post type
        add_action('save_post', 'woo_vou_save_metadata');

        //ajax call to edit all controls
        add_action('wp_ajax_woo_vou_page_builder', array($this->render, 'woo_vou_page_builder'));
        add_action('wp_ajax_nopriv_woo_vou_page_builder', array($this->render, 'woo_vou_page_builder'));

        //add filter to add new action "duplicate" on admin vouchers page
        add_filter('post_row_actions', array($this, 'woo_vou_dupd_action_new_link_add'), 10, 2);

        //add action to add preview button after update button
        add_action('post_submitbox_start', array($this, 'woo_vou_add_preview_button'));

        //add action to create duplicate voucher
        add_action('admin_init', 'woo_vou_duplicate_process');

        //add filter to display vouchers by menu order with ascending order
        add_filter('posts_orderby', 'woo_vou_edit_posts_orderby');

        // Add product meta field in product price option
        add_action('woocommerce_product_options_pricing', array($this, 'woo_vou_product_options_add_voucher_price'));

        // Add Voucher settings in variation pricing option
        add_action('woocommerce_variation_options_pricing', 'woo_vou_variation_options_add_voucher_price', 10, 3);

        // add metabox in products
        add_action('woocommerce_product_write_panel_tabs', array($this->voumeta, 'woo_vou_product_write_panel_tab'));

        // To make compatible with previous versions of 3.0.0
        if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1) {
            // woocommerce_product_write_panels is deprecated since version 2.6!
            add_action('woocommerce_product_write_panels', array($this->voumeta, 'woo_vou_product_write_panel'));
        } else {
            add_action('woocommerce_product_data_panels', array($this->voumeta, 'woo_vou_product_write_panel'));
        }
        add_action('woocommerce_process_product_meta', 'woo_vou_product_save_data', 20, 2);

        //add action to display voucher history
        add_action('add_meta_boxes', array($this, 'woo_vou_order_meta_boxes'), 35);

        //add action to delete order meta when woocommerce order delete
        add_action('before_delete_post', 'woo_vou_order_delete');

        // add action to add an extra fields in edit user page
        add_action('edit_user_profile', 'woo_vou_user_edit_profile_fields');

        // add action to store user meta in database
        add_action('edit_user_profile_update', 'woo_vou_update_profile_fields');
        add_action('personal_options_update', 'woo_vou_update_profile_fields');

        // Add action to show voucher options to vendor users
        add_action('show_user_profile', 'woo_vou_user_edit_profile_fields');

        // Action for product variation meta
        add_action('woocommerce_product_after_variable_attributes', 'woo_vou_product_variable_meta', 10, 3);

        // Action to save product variation meta
        add_action('woocommerce_save_product_variation', 'woo_vou_product_save_variable_meta', 10, 2);

        // Action to flush the voucher upload dir
        add_action('woo_vou_flush_upload_dir_cron', 'woo_vou_flush_upload_dir');

        //File download access to admin
        add_action('init', array($this, 'woo_vou_admin_voucher_pdf_download'), 9);

        //add action for email templates classes for woo pdf vouchers
        add_filter('woocommerce_email_classes', 'woo_vou_add_email_classes');

        // Add action for delete voucher codes
        add_action('admin_init', 'woo_vou_delete_vou_codes');

        // Add action to send gift notification email ( daily cron )        
        add_action('woo_vou_send_gift_notification', array($this, 'woo_vou_send_gift_notification_email'));

        // Add filter to change Order Status to 'on-hold', while checkout with COD, if PDF VOucher is enabled
        add_filter('woocommerce_cod_process_payment_order_status', 'woo_vou_cod_process_payment_order_status_func', 999, 2);

        // Add action to add custom fields on coupon page
        add_action('woocommerce_coupon_options', array($this, 'woo_vou_coupon_options'));

        // Add action to save custom fields on coupon page
        add_action('woocommerce_coupon_options_save', 'woo_vou_save_coupon_options', 15);

        // Add filter to modify get vouchers args
        add_filter('woo_vou_get_vouchers_args', 'woo_vou_get_vouchers_args_func', 10, 1);

        // Add action to modify custom query in post list
        add_action('parse_query', 'woo_vou_post_list_query_filter_func');

        // Add action to modify post variable before data gets saved
        add_filter('woocommerce_coupon_code', 'woo_vou_save_coupon_code');

        // Add filter for adding plugin settings
        add_filter('woocommerce_get_settings_pages', 'woo_vou_admin_settings_tab');

        // Ajax call to pre submit product start and end date validate
        add_action('wp_ajax_woo_vou_product_pre_submit_validation', 'woo_vou_product_pre_submit_validation');
        add_action('wp_ajax_nopriv_woo_vou_product_pre_submit_validation', 'woo_vou_product_pre_submit_validation');


        // Add action to download system log file
        add_action('admin_init', array($this, 'woo_vou_generate_system_log'));

        add_action('admin_notices', array($this, 'woo_vou_license_activating_notice'));
        add_action('network_admin_notices', array($this, 'woo_vou_license_activating_notice'));

        if (is_multisite() && !is_network_admin()) { // for multisite
            remove_action('admin_notices', array($this, 'woo_vou_license_activating_notice'));
        }

        //Check WPWEB Updater version 
        add_action('admin_init', array($this, 'woo_vou_check_wpweb_updater_activation'));

        // Add action to add new settings in popup for General Settings
        add_action('woocommerce_settings_vou_display_settings_after', array($this, 'woocommerce_settings_vou_display_settings'));

        // Add action to search products
        add_action('wp_ajax_woo_vou_load_products', array($this, 'woo_vou_load_products'));

        // Add action to remove builder functionality when enfold theme is active
        add_action('admin_init', 'woo_vou_enfold_remove_builder');

        // Register the new dashboard widget with the 'wp_dashboard_setup' action
        add_action('wp_dashboard_setup', array($this, 'woo_vou_add_dashboard_widgets'));

        // AJAX action to load more voucher code on product page
        add_action('wp_ajax_woo_vou_load_more_used_voucode', 'woo_vou_load_more_used_voucode');
        add_action('wp_ajax_nopriv_woo_vou_load_more_used_voucode', 'woo_vou_load_more_used_voucode');

        // AJAX action to load more voucher code on product page
        add_action('wp_ajax_woo_vou_load_more_purchased_voucode', 'woo_vou_load_more_purchased_voucode');
        add_action('wp_ajax_nopriv_woo_vou_load_more_purchased_voucode', 'woo_vou_load_more_purchased_voucode');

        // AJAX action to load more unused voucher code on product page
        add_action('wp_ajax_woo_vou_load_more_unused_voucode', 'woo_vou_load_more_unused_voucode');
        add_action('wp_ajax_nopriv_woo_vou_load_more_unused_voucode', 'woo_vou_load_more_unused_voucode');

        //ajax call to voucher redeem popup
        add_action('wp_ajax_woo_vou_voucher_redeem_popup', 'woo_vou_voucher_redeem_popup');
        add_action('wp_ajax_nopriv_woo_vou_voucher_redeem_popup', 'woo_vou_voucher_redeem_popup');

        // check if pdf voucher directory not exist on upload directory
        add_action('admin_init', array($this, 'woo_vou_check_directory_exists'));

        // Wp rocket filter to add HTACCESS rule before Wp rockets rules for iTheme Security . 
        add_filter('before_rocket_htaccess_rules', array($this, 'woo_vou_itheme_security_htaccess_rule'));
        
        // Hook to add HTACCESS rules for iTheme Security .
        add_action('admin_init', 'woo_vou_add_rules_to_htaccess');

        // Hook to remove code from htacess file when iTheme security plugin is deactivated/uninstalled.
        add_action( 'itsec_modules_do_plugin_deactivation', 'woo_vou_remove_rules_from_htaccess' );
        add_action( 'itsec_modules_do_plugin_uninstall', 'woo_vou_remove_rules_from_htaccess' );

        add_action('admin_init', array($this, 'woo_vou_check_order_admin_notice'));

        add_action('wp_ajax_woo_vou_dismiss_license_activation', array($this, 'woo_vou_dismiss_license_activation'));

        add_action('wp_ajax_woo_vou_dismiss_order_complete', array($this, 'woo_vou_dismiss_order_complete'));

        add_action('wp_ajax_woo_vou_dismiss_order_cancelled', array($this, 'woo_vou_dismiss_order_cancelled'));

        


    }

}
