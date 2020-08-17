<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;
    
/**
 * Add Page to See Used Voucher for
 * all Products
 * 
 * Handles to list the products for which vouchers
 * used
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.1.0
 */
function woo_vou_codes_page() {

    // Include voucher details file if voucher id is set
    global $current_user, $woo_vou_vendor_role;

    // If we get voucher code id in URL
    if (isset($_GET['vou_code']) && !empty($_GET['vou_code'])) {

    	// Get option whether to allow all vendor to redeem voucher codes
	    $vou_enable_vendor_access_all_voucodes = get_option('vou_enable_vendor_access_all_voucodes');

	    // Get Voucher code information
        $voucodeid 			= $_GET['vou_code'];
        $voucher_admins 	= woo_vou_assigned_admin_roles(); //get voucher admins
        $current_user_id 	= $current_user->ID; // Get logged in user ID
        $post_author 		= get_post_field('post_author', $voucodeid); // Get voucher code post author
        $user_roles 		= isset($current_user->roles) ? $current_user->roles : array(); // Get logged in user role
        $user_role 			= array_shift($user_roles); // Get first user role from array of roles

        // If logged in user is admin or vendor and if vendor is assigned the code than show details
        if ( in_array($user_role, $voucher_admins) ||
           ( in_array($user_role, $woo_vou_vendor_role) && ( $current_user_id == $post_author ||
           ( !empty( $vou_enable_vendor_access_all_voucodes ) && $vou_enable_vendor_access_all_voucodes == 'yes' )))) {

            include_once(WOO_VOU_DIR . '/includes/public/woo-vou-code-details-info.php' ); // call function to get voucher details data
        } else { // Else redirect to voucher code page

            $redirect_url = add_query_arg(array('page' => 'woo-vou-codes'), admin_url('admin.php'));
            wp_redirect($redirect_url);
            exit;
        }
    } else { // Else display voucher code page

        include_once( WOO_VOU_ADMIN . '/forms/woo-vou-codes-page.php' );
    }
}

/**
 * Check Voucher Code Page for
 * all Products
 * 
 * Handles to check voucher code page
 * for all voucher codes and manage codes
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.1.0
 */
function woo_vou_check_voucher_code_page() {
    include_once( WOO_VOU_ADMIN . '/forms/woo-vou-check-code.php' );
}

/**
 * Import Codes From CSV
 * 
 * Handle to import voucher codes from CSV Files
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_import_codes() {

    //import csv file code for voucher code importing to textarea
    if (( isset($_FILES['woo_vou_csv_file']['tmp_name']) && !empty($_FILES['woo_vou_csv_file']['tmp_name']))) {

    	$importcodes = '';
        $pattern_data = array();
    	
        $filename = $_FILES['woo_vou_csv_file']['tmp_name'];
        $deletecode = isset($_POST['woo_vou_delete_code']) && !empty($_POST['woo_vou_delete_code']) ? $_POST['woo_vou_delete_code'] : '';
        $existingcode = isset($_POST['woo_vou_existing_code']) && !empty($_POST['woo_vou_existing_code']) ? $_POST['woo_vou_existing_code'] : '';
        $csvseprator = isset($_POST['woo_vou_csv_sep']) && !empty($_POST['woo_vou_csv_sep']) ? $_POST['woo_vou_csv_sep'] : ',';
        $csvenclosure = isset($_POST['woo_vou_csv_enc']) ? $_POST['woo_vou_csv_enc'] : '';

        if (!empty($existingcode) && $deletecode != 'y') { // check existing code and existing code not remove
            $pattern_data = explode(',', $existingcode);
            $pattern_data = array_map('trim', $pattern_data);
        }

        if (!empty($filename) && ( $handle = fopen($filename, "r") ) !== FALSE) {

            if (!empty($csvenclosure)) {

                while (($data = fgetcsv($handle, 1000, $csvseprator, $csvenclosure)) !== FALSE) { // check all row of csv
                    foreach ($data as $key => $value) { // check all column of particular row
                        if (!empty($value) && !in_array($value, $pattern_data)) { // cell value is not empty and avoid duplicate code
                            $pattern_data[] = str_replace(',', '', $value);
                        }
                    }
                }
            } else {

                while (($data = fgetcsv($handle, 1000, $csvseprator)) !== FALSE) { // check all row of csv
                    foreach ($data as $key => $value) { // check all column of particular row
                        if (!empty($value) && !in_array($value, $pattern_data)) { // cell value is not empty and avoid duplicate code
                            $pattern_data[] = str_replace(',', '', $value);
                        }
                    }
                }
            }

            fclose($handle);
            unset($_FILES['woo_vou_csv_file']);
        }

        $import_code = implode(', ', $pattern_data); // all pattern codes

        echo $import_code;
        exit;
    }
}

/**
 * Import Random Code using AJAX
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_import_code() {
	
	global $woo_vou_voucher;

    $noofvoucher = !empty($_POST['noofvoucher']) ? $_POST['noofvoucher'] : 0;
    $codeprefix = !empty($_POST['codeprefix']) ? $_POST['codeprefix'] : '';
    $codeseperator = !empty($_POST['codeseperator']) ? $_POST['codeseperator'] : '';
    $pattern = !empty($_POST['codepattern']) ? $_POST['codepattern'] : '';
    $existingcode = !empty($_POST['existingcode']) ? $_POST['existingcode'] : '';
    $deletecode = !empty($_POST['deletecode']) ? $_POST['deletecode'] : '';

    $pattern_prefix = $codeprefix . $codeseperator; // merge prefix with seperator

    $pattern_data = array();
    if (!empty($existingcode) && $deletecode != 'y') { // check existing code and existing code not remove
        $pattern_data = explode(',', $existingcode);
        $pattern_data = array_map('trim', $pattern_data);
    }

    for ($j = 0; $j < $noofvoucher; $j++) { // no of codes are generate
        $pattern_string = $pattern_prefix . woo_vou_get_pattern_string($pattern);

        while (in_array($pattern_string, $pattern_data)) { // avoid duplicate pattern code
            $pattern_string = $pattern_prefix . woo_vou_get_pattern_string($pattern);
        }

        $pattern_data[] = str_replace(',', '', $pattern_string);
    }
    $import_code = implode(', ', $pattern_data); // all pattern codes

    echo $import_code;
    exit;
}

/**
 * Add Popup For import Voucher Code
 * 
 * Handels to show import voucher code popup
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_import_footer() {

    global $post;

    //Check product post type page
    if (isset($post->post_type) && $post->post_type == WOO_VOU_MAIN_POST_TYPE) {

        include_once( WOO_VOU_ADMIN . '/forms/metabox-popups/woo-vou-import-code-popup.php' );
    }
}

/**
 * Add Custom Editor
 * 
 * Handles to add custom editor
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_editor_control() {

    include( WOO_VOU_ADMIN . '/forms/woo-vou-editor.php' );
}

/**
 * Add Style Options
 * 
 * Handles to add Style Options
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_pdf_options_page() {

    include( WOO_VOU_ADMIN . '/forms/woo-vou-meta-options.php' );
}

/**
 * Save Voucher Meta Content
 * 
 * Handles to saving voucher meta on update voucher template post type
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_save_metadata($post_id) {    
    global $post_type;


    $prefix = WOO_VOU_META_PREFIX;

    $post_type_object = get_post_type_object($post_type);

    // Check for which post type we need to add the meta box
    $pages = array(WOO_VOU_POST_TYPE);

    if (( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )                // Check Autosave
            || (!isset($_POST['post_ID']) || $post_id != $_POST['post_ID'] )        // Check Revision
            || (!in_array($post_type, $pages) )              // Check if current post type is supported.
            || (!check_admin_referer(WOO_VOU_PLUGIN_BASENAME, 'at_woo_vou_meta_box_nonce') )      // Check nonce - Security
            || (!current_user_can($post_type_object->cap->edit_post, $post_id) )) {       // Check permission
        return $post_id;
    }

    $metacontent = isset($_POST['woo_vou_meta_content']) ? $_POST['woo_vou_meta_content'] : '';
    $metacontent = trim($metacontent);
    update_post_meta($post_id, $prefix . 'meta_content', $metacontent); // updating the content of page builder editor
    //Update Editor Status
    if (isset($_POST[$prefix . 'editor_status'])) {
        update_post_meta($post_id, $prefix . 'editor_status', $_POST[$prefix . 'editor_status']);
    }

    //Update Background Style
    if (isset($_POST[$prefix . 'pdf_bg_style'])) {
        update_post_meta($post_id, $prefix . 'pdf_bg_style', $_POST[$prefix . 'pdf_bg_style']);
    }

    //Update Background Pattern
    if (isset($_POST[$prefix . 'pdf_bg_pattern'])) {
        update_post_meta($post_id, $prefix . 'pdf_bg_pattern', $_POST[$prefix . 'pdf_bg_pattern']);
    }

    //Update Background Image
    if (isset($_POST[$prefix . 'pdf_bg_img'])) {
        update_post_meta($post_id, $prefix . 'pdf_bg_img', $_POST[$prefix . 'pdf_bg_img']);
    }

    //Update Background Color
    if (isset($_POST[$prefix . 'pdf_bg_color'])) {
        update_post_meta($post_id, $prefix . 'pdf_bg_color', $_POST[$prefix . 'pdf_bg_color']);
    }

    //Update PDF View
    if (isset($_POST[$prefix . 'pdf_view'])) {
        update_post_meta($post_id, $prefix . 'pdf_view', $_POST[$prefix . 'pdf_view']);
    }

    //Update PDF Size
    if (isset($_POST[$prefix . 'pdf_size'])) {
        update_post_meta($post_id, $prefix . 'pdf_size', $_POST[$prefix . 'pdf_size']);
    }

    //Update Margin Top
    if (!empty($_POST[$prefix . 'pdf_margin_top'])) {
        update_post_meta($post_id, $prefix . 'pdf_margin_top', $_POST[$prefix . 'pdf_margin_top']);
    }

    //Update Margin Bottom
    if (!empty($_POST[$prefix . 'pdf_margin_bottom'])) {

        update_post_meta($post_id, $prefix . 'pdf_margin_bottom', $_POST[$prefix . 'pdf_margin_bottom']);
    }
    //Update Margin Left
    if (!empty($_POST[$prefix . 'pdf_margin_left'])) {
        update_post_meta($post_id, $prefix . 'pdf_margin_left', $_POST[$prefix . 'pdf_margin_left']);
    }
    //Update Margin Right
    if (!empty($_POST[$prefix . 'pdf_margin_right'])) {
        update_post_meta($post_id, $prefix . 'pdf_margin_right', $_POST[$prefix . 'pdf_margin_right']);
    }

    //Update Custom CSS
    if (isset($_POST[$prefix . 'pdf_custom_css'])) {
        update_post_meta($post_id, $prefix . 'pdf_custom_css', $_POST[$prefix . 'pdf_custom_css']);
    }

    
}

/**
 * Get Preview Link
 *
 * Handles to get preview link
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_get_preview_link($postid) {

    $preview_url = add_query_arg(array('post_type' => WOO_VOU_POST_TYPE, 'woo_vou_pdf_action' => 'preview', 'voucher_id' => $postid), admin_url('edit.php'));

    return $preview_url;
}

/**
 * Duplicate Voucher
 * 
 * Handles to creating duplicate voucher
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_duplicate_process() {
	
	global $woo_vou_voucher;

    //check the duplicate create action is set or not and order id is not empty
    if (isset($_GET['action']) && !empty($_GET['action']) && $_GET['action'] == 'woo_vou_duplicate_vou' && isset($_GET['woo_vou_dupd_vou_id']) && !empty($_GET['woo_vou_dupd_vou_id'])) {

        // get the vou id
        $vou_id = $_GET['woo_vou_dupd_vou_id'];

        //check admin referer	
        check_admin_referer('duplicate-vou_' . $vou_id);

        // create duplicate voucher
        $woo_vou_voucher->woo_vou_dupd_create_duplicate_vou($vou_id);
    }
}

/**
 * Vouchers Lists display based on menu order with ascending order
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_edit_posts_orderby($orderby_statement) {

    global $wpdb;

    //Check post type is woovouchers & sorting not applied by user
    if (isset($_GET['post_type']) && $_GET['post_type'] == WOO_VOU_POST_TYPE && !isset($_GET['orderby'])) {

        $orderby_statement = "{$wpdb->posts}.menu_order ASC, {$wpdb->posts}.post_date DESC";
    }
    return $orderby_statement;
}

/**
 * Save Metabox Data
 * 
 * Handles to save metabox details
 * to database
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_product_save_data($post_id, $post) {
   
	
	global $post_type, $woo_vou_model;
    // get prefix
    $prefix = WOO_VOU_META_PREFIX;

    //is downloadable
    $is_downloadable = get_post_meta($post_id, '_downloadable', true);

    // Getting product type
    $product_type = !empty($_POST['product-type']) ? $_POST['product-type'] : '';

    // Enable Voucher Codes
    $woo_vou_enable = !empty($_POST[$prefix . 'enable']) ? 'yes' : '';

    // get Pdf template
    $woo_vou_pdf_template = isset($_POST[$prefix . 'pdf_template']) ? $_POST[$prefix . 'pdf_template'] : '';

    // Usability
    $woo_vou_using_type = isset($_POST[$prefix . 'using_type']) ? $_POST[$prefix . 'using_type'] : '';

    // get logo
    $woo_vou_logo = isset($_POST[$prefix . 'logo']) ? $_POST[$prefix . 'logo'] : '';

    // get address
    $woo_vou_address_phone = isset($_POST[$prefix . 'address_phone']) ? $_POST[$prefix . 'address_phone'] : '';

    // get website
    $woo_vou_website = isset($_POST[$prefix . 'website']) ? $_POST[$prefix . 'website'] : '';

    // get redeem instructions
    $woo_vou_how_to_use = isset($_POST[$prefix . 'how_to_use']) ? $_POST[$prefix . 'how_to_use'] : '';

    // Recipient Ordering information
    $woo_vou_recipient_data_order = !empty($_POST[$prefix.'recipient_detail_order']) ? $_POST[$prefix.'recipient_detail_order'] : '';
    
	// Vocher Uses Limit
	$voucher_uses_limit = !empty($_POST[$prefix.'voucher_uses_limit']) ? $_POST[$prefix.'voucher_uses_limit'] : '';


    // enable pdf template selection
    $woo_vou_pdf_template_selection = !empty($_POST[$prefix . 'enable_pdf_template_selection']) ? 'yes' : '';
    $pdf_template_selection_label = !empty($_POST[$prefix . 'pdf_template_selection_label']) ? trim($_POST[$prefix . 'pdf_template_selection_label']) : '';
    $pdf_template_selection_is_required = !empty($_POST[$prefix . 'pdf_template_selection_is_required']) ? 'yes' : '';
    $pdf_template_selection = !empty($_POST[$prefix . 'pdf_template_selection']) ? $_POST[$prefix . 'pdf_template_selection'] : '';
    $pdf_template_selection_desc = !empty($_POST[$prefix . 'pdf_selection_desc']) ? $_POST[$prefix . 'pdf_selection_desc'] : '';

    // enable pdf template selection
    $enable_pdf_preview = !empty($_POST[$prefix . 'enable_pdf_preview']) ? $_POST[$prefix . 'enable_pdf_preview'] : '';

    // enable coupon code generation
    $enable_coupon_code = !empty($_POST[$prefix . 'enable_coupon_code']) ? $_POST[$prefix . 'enable_coupon_code'] : '';
    
    // get enable 1 voucher per pdf
    $enable_multiple_pdf = !empty($_POST[$prefix . 'enable_multiple_pdf']) ? $_POST[$prefix . 'enable_multiple_pdf'] : '';

    // enable recipient delivery by email
    $delivery_methods = woo_vou_voucher_delivery_methods();
    $woo_vou_enable_recipient_delivery_method = !empty($_POST[$prefix . 'enable_recipient_delivery_method']) ? 'yes' : '';
    $woo_vou_recipient_delivery_data = !empty($_POST[$prefix.'recipient_delivery']) ? $_POST[$prefix.'recipient_delivery'] : '';
    $woo_vou_recipient_delivery_label = !empty($_POST[$prefix.'recipient_delivery_label']) ? $_POST[$prefix.'recipient_delivery_label'] : '';

    foreach( $delivery_methods as $delivery_method_key => $delivery_method_val ){

    	if( !empty( $woo_vou_recipient_delivery_data['enable_'.$delivery_method_key] ) ) {
    		$woo_vou_recipient_delivery_data['enable_'.$delivery_method_key] = 'yes';
    	} else {
    		$woo_vou_recipient_delivery_data['enable_'.$delivery_method_key] = 'no';
    	}

    if( !isset( $woo_vou_recipient_delivery_data[$delivery_method_key] ) ) {

    		$woo_vou_recipient_delivery_data[$delivery_method_key] = 'no';
    	}
    }

    $disable_redeem_day = !empty($_POST[$prefix . 'disable_redeem_day']) ? $_POST[$prefix . 'disable_redeem_day'] : '';

    // Get voucher amount
    $woo_vou_voucher_price = isset($_POST[$prefix . 'voucher_price']) ? $_POST[$prefix . 'voucher_price'] : '';

    // Check if downloadable is on or variable product then set voucher enable option otherwise not set
    if ($is_downloadable == 'yes' || $product_type == 'variable') {

        $enable_voucher = $woo_vou_enable;
    } else {
        $enable_voucher = '';
    }


    update_post_meta($post_id, $prefix . 'enable', $enable_voucher);

    // Get all recipient columns from
    $recipient_details = woo_vou_voucher_recipient_details();

    // Looping on recipient columns
    foreach( $recipient_details as $recipient_key => $recipient_val ) {

    	// Declaring blank variables so as to save default value
    	$recipient_key_enable = $recipient_key_max_length = $recipient_key_label = '';
    	$recipient_key_is_required = $recipient_key_desc = '';

    	// If options is enabled then save yes
    	if( isset( $_POST[$prefix.'enable_'.$recipient_key] ) ) {

    		$recipient_key_enable = 'yes';
    	}

    	// If max length for that column is set then save it
    	if( !empty( $_POST[$prefix.$recipient_key.'_max_length'] ) && is_numeric( $_POST[$prefix.$recipient_key.'_max_length'] ) ) {

    		$recipient_key_max_length = trim(round($_POST[$prefix.$recipient_key.'_max_length']));
    	}

    	// If label is set for recipient column
    	if( !empty( $_POST[$prefix.$recipient_key.'_label'] ) ) {

    		$recipient_key_label = trim($_POST[$prefix.$recipient_key.'_label']);
    	}

    	// if recipient column is required
    	if( !empty( $_POST[$prefix.$recipient_key.'_is_required'] ) ) {

    		$recipient_key_is_required = 'yes';
    	}

    	// If description for recipient column is added
    	if( !empty( $_POST[$prefix.$recipient_key.'_desc'] ) ) {

    		$recipient_key_desc = trim($_POST[$prefix.$recipient_key.'_desc']);
    	}


    	// Update post meta for all the mets data
    	update_post_meta( $post_id, $prefix.'enable_'.$recipient_key, $recipient_key_enable );
    	update_post_meta( $post_id, $prefix.$recipient_key.'_max_length', $recipient_key_max_length );
    	$test = update_post_meta( $post_id, $prefix.$recipient_key.'_label', $recipient_key_label);

    	update_post_meta( $post_id, $prefix.$recipient_key.'_is_required', $recipient_key_is_required);
    	update_post_meta( $post_id, $prefix.$recipient_key.'_desc', $recipient_key_desc);
    }

    //Delivery method detail update
    update_post_meta($post_id, $prefix . 'enable_recipient_delivery_method', $woo_vou_enable_recipient_delivery_method);
    update_post_meta($post_id, $prefix . 'recipient_delivery', $woo_vou_recipient_delivery_data);
    update_post_meta($post_id, $prefix . 'recipient_delivery_label', $woo_vou_recipient_delivery_label);

    // Recipient Ordering detail
    update_post_meta($post_id, $prefix . 'recipient_detail_order', $woo_vou_recipient_data_order);

    //Pdf Template Selection Detail Update
    update_post_meta($post_id, $prefix . 'enable_pdf_template_selection', $woo_vou_pdf_template_selection);
    update_post_meta($post_id, $prefix . 'pdf_template_selection_label', $pdf_template_selection_label);
    update_post_meta($post_id, $prefix . 'pdf_template_selection_is_required', $pdf_template_selection_is_required);
    update_post_meta($post_id, $prefix . 'pdf_template_selection', $pdf_template_selection);
    update_post_meta($post_id, $prefix . 'pdf_selection_desc', $pdf_template_selection_desc);
    update_post_meta($post_id, $prefix . 'enable_pdf_preview', $enable_pdf_preview);
    update_post_meta($post_id, $prefix . 'enable_coupon_code', $enable_coupon_code);
    update_post_meta($post_id, $prefix . 'enable_multiple_pdf', $enable_multiple_pdf);

    update_post_meta($post_id, $prefix . 'disable_redeem_day', $disable_redeem_day); // disbale reedem days

    // wc_format_decimal function is used to take care for decimal seperator setting
    update_post_meta($post_id, $prefix . 'voucher_price', wc_format_decimal($woo_vou_voucher_price)); // Voucher Price Update
    // PDF Template
    update_post_meta($post_id, $prefix . 'pdf_template', $woo_vou_pdf_template);

    // Vendor User
    update_post_meta($post_id, $prefix . 'vendor_user', $_POST[$prefix . 'vendor_user']);
	
	// Save uses limit meta
    update_post_meta($post_id, $prefix . 'voucher_uses_limit', $_POST[$prefix . 'voucher_uses_limit']);

    // Voucher Delivery
    $woo_vou_voucher_delivery = isset($_POST[$prefix . 'voucher_delivery']) ? ($_POST[$prefix . 'voucher_delivery']) : '';
    if ( $woo_vou_voucher_delivery == 'default' ){
        $woo_vou_voucher_delivery = get_option('vou_voucher_delivery_options');
    }
    update_post_meta($post_id, $prefix . 'voucher_delivery', $woo_vou_voucher_delivery);
    
    $secondary_vendor_users = isset($_POST[$prefix . 'sec_vendor_users']) ? $_POST[$prefix . 'sec_vendor_users'] : '';
    // Secondary Vendor Users
    $secondary_vendor_users = isset($_POST[$prefix . 'sec_vendor_users']) && !empty($_POST[$prefix . 'sec_vendor_users']) ? $_POST[$prefix . 'sec_vendor_users'] : '';
    update_post_meta($post_id, $prefix . 'sec_vendor_users', $secondary_vendor_users);

    //expire type
    if (isset($_POST[$prefix . 'exp_type'])) {
        update_post_meta($post_id, $prefix . 'exp_type', $_POST[$prefix . 'exp_type']);
    }

    update_post_meta($post_id, $prefix . 'days_diff', $_POST[$prefix . 'days_diff']);

    $custom_days = !empty($_POST[$prefix . 'custom_days']) && is_numeric($_POST[$prefix . 'custom_days']) ? trim(round($_POST[$prefix . 'custom_days'])) : '';
    update_post_meta($post_id, $prefix . 'custom_days', $custom_days);


    // Product Start Date
    $product_start_date = $_POST[$prefix . 'product_start_date'];

    if (!empty($product_start_date)) {
        $product_start_date = strtotime($woo_vou_model->woo_vou_escape_slashes_deep(strtoupper($product_start_date)));
        $product_start_date = date('Y-m-d H:i:s', $product_start_date);
    }
    update_post_meta($post_id, $prefix . 'product_start_date', $product_start_date);

    // Expiration Date
    $product_exp_date = $_POST[$prefix . 'product_exp_date'];

    if (!empty($product_exp_date)) {
        $product_exp_date = strtotime($woo_vou_model->woo_vou_escape_slashes_deep(strtoupper($product_exp_date)));
        $product_exp_date = date('Y-m-d H:i:s', $product_exp_date);
    }
    update_post_meta($post_id, $prefix . 'product_exp_date', $product_exp_date);

    // Coupon products
    $coupon_products = isset($_POST[$prefix . 'coupon_products']) && !empty($_POST[$prefix . 'coupon_products']) ? $_POST[$prefix . 'coupon_products'] : '';
    update_post_meta($post_id, $prefix . 'coupon_products', $coupon_products);

    // Coupon exclude products
    $coupon_exclude_products = isset($_POST[$prefix . 'coupon_exclude_products']) && !empty($_POST[$prefix . 'coupon_exclude_products']) ? $_POST[$prefix . 'coupon_exclude_products'] : '';
    // Remove products ids which selected in coupon products meta
    if( is_array($coupon_products) && is_array($coupon_exclude_products) ){
        $coupon_exclude_products = array_diff($coupon_exclude_products, $coupon_products);
    }
    update_post_meta($post_id, $prefix . 'coupon_exclude_products', $coupon_exclude_products);

		
	// Coupon products
    $coupon_categories = isset($_POST[$prefix . 'coupon_categories']) && !empty($_POST[$prefix . 'coupon_categories']) ? $_POST[$prefix . 'coupon_categories'] : '';
    update_post_meta($post_id, $prefix . 'coupon_categories', $coupon_categories);


    // Coupon exclude products
    $coupon_exclude_categories = isset($_POST[$prefix . 'coupon_exclude_categories']) && !empty($_POST[$prefix . 'coupon_exclude_categories']) ? $_POST[$prefix . 'coupon_exclude_categories'] : '';
    // Remove products ids which selected in coupon products meta
    if( is_array($coupon_categories) && is_array($coupon_exclude_categories) ){
        $coupon_exclude_categories = array_diff($coupon_exclude_categories, $coupon_categories);
    }
    update_post_meta($post_id, $prefix . 'coupon_exclude_categories', $coupon_exclude_categories);
	
    // Apply Coupon discount option on
    $discount_on_tax_type = isset($_POST[$prefix . 'discount_on_tax_type']) && !empty($_POST[$prefix . 'discount_on_tax_type']) ? $_POST[$prefix . 'discount_on_tax_type'] : '';

    update_post_meta($post_id, $prefix . 'discount_on_tax_type', $discount_on_tax_type);	
	
	

	// Save minimum spend coupon amount
	$coupon_minimum_spend_amount = isset($_POST[$prefix . 'coupon_minimum_spend_amount']) && !empty($_POST[$prefix . 'coupon_minimum_spend_amount']) ? $_POST[$prefix . 'coupon_minimum_spend_amount'] : '';
	update_post_meta($post_id, $prefix . 'coupon_minimum_spend_amount', $coupon_minimum_spend_amount);	
	 
	// Save maximum spend coupon amount
	$coupon_maximum_spend_amount = isset($_POST[$prefix . 'coupon_maximum_spend_amount']) && !empty($_POST[$prefix . 'coupon_maximum_spend_amount']) ? $_POST[$prefix . 'coupon_maximum_spend_amount'] : '';
	update_post_meta($post_id, $prefix . 'coupon_maximum_spend_amount', $coupon_maximum_spend_amount);	

    // Start Date
    $start_date = $_POST[$prefix . 'start_date'];

    if (!empty($start_date)) {
        $start_date = strtotime($woo_vou_model->woo_vou_escape_slashes_deep($start_date));
        $start_date = date('Y-m-d H:i:s', $start_date);
    }
    update_post_meta($post_id, $prefix . 'start_date', $start_date);

    // Expiration Date
    $exp_date = $_POST[$prefix . 'exp_date'];

    if (!empty($exp_date)) {
        $exp_date = strtotime($woo_vou_model->woo_vou_escape_slashes_deep($exp_date));
        $exp_date = date('Y-m-d H:i:s', $exp_date);
    }
    update_post_meta($post_id, $prefix . 'exp_date', $exp_date);

    // Voucher Codes
    $voucher_codes = isset($_POST[$prefix . 'codes']) ? $woo_vou_model->woo_vou_escape_slashes_deep($_POST[$prefix . 'codes']) : '';
    update_post_meta($post_id, $prefix . 'codes', html_entity_decode( $voucher_codes ) );

    $usability = $woo_vou_using_type;

    if (isset($_POST[$prefix . 'vendor_user']) && !empty($_POST[$prefix . 'vendor_user']) && $usability == '') {//if vendor user is set and usability is default 
        $usability = get_user_meta($_POST[$prefix . 'vendor_user'], $prefix . 'using_type', true);
    }

    // If usability is default then take it from setting
    if ($usability == '') {
        $usability = get_option('vou_pdf_usability');
    }

    update_post_meta($post_id, $prefix . 'using_type', $usability);

    // vendor's Logo
    update_post_meta($post_id, $prefix . 'logo', $woo_vou_logo);

    // Vendor's Address
    update_post_meta($post_id, $prefix . 'address_phone', $woo_vou_model->woo_vou_escape_slashes_deep($woo_vou_address_phone, true, true));

    // Website URL
    update_post_meta($post_id, $prefix . 'website', $woo_vou_model->woo_vou_escape_slashes_deep($woo_vou_website));

    // Redeem Instructions
    update_post_meta($post_id, $prefix . 'how_to_use', $woo_vou_model->woo_vou_escape_slashes_deep($woo_vou_how_to_use, true, true));

    // update available products count on bases of entered voucher codes
    if (isset($_POST[$prefix . 'codes']) && $enable_voucher == 'yes') {

        $voucount = '';
        $vouchercodes = trim($_POST[$prefix . 'codes'], ',');
        if (!empty($vouchercodes)) {
            $vouchercodes = explode(',', $vouchercodes);
            $voucount = count($vouchercodes);
        }

        if (empty($usability)) {// using type is only one time
            $avail_total = empty($voucount) ? '0' : $voucount;

            // Getting variable product id
            $variable_post_id = (!empty($_POST['variable_post_id'])) ? $_POST['variable_post_id'] : array();

            // If product is variable and id's are not blank then update their quantity with blank
            if ($product_type == 'variable' && !empty($variable_post_id)) {

                // set flag false
                $variable_code_flag = false;

                foreach ($variable_post_id as $variable_post) {


                    $variable_is_downloadable = get_post_meta($variable_post, '_downloadable', true);
                    $variable_codes = get_post_meta($variable_post, $prefix . 'codes', true);

                    if ($variable_is_downloadable == 'yes' && !empty($variable_codes)) {

                        // if variation is set as downloadable and vochers codes set at variation level
                        $variable_code_flag = true;
                    }
                }

                if ($variable_code_flag == true) {

                    // mark this product as variable voucher so we consider it to take vouchers from variations 
                    update_post_meta($post_id, $prefix . 'is_variable_voucher', '1');
                } else {

                    update_post_meta($post_id, $prefix . 'is_variable_voucher', '');
                }

                // default variable auto enable is true
                $variable_auto_enable = true;

                // get auto download option
                $disable_variations_auto_downloadable = get_option('vou_disable_variations_auto_downloadable');
                if ($disable_variations_auto_downloadable == 'yes') { // if disable option
                    $variable_auto_enable = false;
                }

                // disable auto enable
                $auto_enable = apply_filters('woo_vou_auto_enable_downloadable_variations', $variable_auto_enable, $post_id);

                foreach ($variable_post_id as $variable_post) {

                    if ($variable_code_flag != true) { // if there no voucher codes set on variation level
                        // get voucher codes
                        $var_vou_codes = get_post_meta($variable_post, $prefix . 'codes', true);

                        if ($auto_enable || !empty($var_vou_codes)) {

                            // update variation manage stock as no
                            update_post_meta($variable_post, '_manage_stock', 'no');

                            // Update variation stock qty with blank
                            update_post_meta($variable_post, '_stock', '');

                            // Update variation downloadable with yes
                            update_post_meta($variable_post, '_downloadable', 'yes');
                        }
                    } else {

                        //update manage stock with yes
                        update_post_meta($variable_post, '_manage_stock', 'yes');

                        $variable_voucount = '';
                        $variable_codes = get_post_meta($variable_post, $prefix . 'codes', true);

                        $vouchercodes = trim($variable_codes, ',');
                        if (!empty($vouchercodes)) {
                            $vouchercodes = explode(',', $vouchercodes);
                            $variable_voucount = count($vouchercodes);
                        }

                        $variable_avail_total = empty($variable_voucount) ? '0' : $variable_voucount;

                        wc_update_product_stock($variable_post, $variable_avail_total);
                    }
                }
            }

             $enabled_stock_mgmt = get_option( 'woocommerce_manage_stock' );
            // When product is variable and global manage stock is disable then no need to update stock
            // To resolve out of stock when adding new variation
            if( $product_type == 'variable' && $enabled_stock_mgmt == "no" ) {} else {
                //update manage stock with yes
                update_post_meta($post_id, '_manage_stock', 'yes');
            }

            //update available count on bases of 
            wc_update_product_stock($post_id, $avail_total);
        }
    }

    //update location and map links
    $availlocations = array();
    if (isset($_POST[$prefix . 'locations'])) {

        $locations = $_POST[$prefix . 'locations'];
        $maplinks = $_POST[$prefix . 'map_link'];
        for ($i = 0; $i < count($locations); $i++) {
            if (!empty($locations[$i]) || !empty($maplinks[$i])) { //if location or map link is not empty then
                $availlocations[$i][$prefix . 'locations'] = $woo_vou_model->woo_vou_escape_slashes_deep($locations[$i], true, true);
                $availlocations[$i][$prefix . 'map_link'] = $woo_vou_model->woo_vou_escape_slashes_deep($maplinks[$i]);
            }
        }
    }

    //update location and map links
    update_post_meta($post_id, $prefix . 'avail_locations', $availlocations);
}

/**
 * Display Voucher Data within order meta
 *
 * @package WooCommerce - PDF Vouchers
 * @since 1.1.0
 */
function woo_vou_display_voucher_data() {

    include( WOO_VOU_ADMIN . '/forms/woo-vou-meta-history.php' );
}

/**
 * Delete order meta and all order detail whene order delete.
 *
 * @package WooCommerce - PDF Vouchers
 * @since 1.4.1
 */
function woo_vou_order_delete($order_id = '') {

    $prefix = WOO_VOU_META_PREFIX;

    if (!empty($order_id)) { // check if order id is not empty
        $post_type = get_post_type($order_id); // get	post type from order id

        if ($post_type == 'shop_order') { // check if post type is shop_order
            $args = array(
                'post_type' => WOO_VOU_CODE_POST_TYPE,
                'post_status' => 'any',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => $prefix . 'order_id',
                        'value' => $order_id
                    )
                )
            );

            // get posts from order id
            $posts = get_posts($args);

            if (!empty($posts)) { // check if get any post
                foreach ($posts as $post) {

                    wp_delete_post($post->ID, true);
                }
            }
        }
    }
}

/**
 * Function for Add an extra fields in edit user page
 *
 * @package WooCommerce - PDF Vouchers
 * @since 1.5
 */
function woo_vou_user_edit_profile_fields($user) {

    global $current_user, $woo_vou_vendor_role, $woo_vou_vendor_role, $woo_vou_admin;

    $vendor_access_vou_setting_area = get_option('vou_allow_vendor_access_voucher_settings');

    //Get user role
    $user_roles = isset($user->roles) ? $user->roles : array();
    $user_role = array_shift($user_roles);

    // Vendor user role
    $vendor_user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
	$vendor_user_role	= array_shift( $vendor_user_roles );

    //check if user role is vendor or not
    if ( ( current_user_can('manage_options') && isset($user_role) && in_array($user_role, $woo_vou_vendor_role) )
    	|| ( in_array($vendor_user_role, $woo_vou_vendor_role) && !empty( $vendor_access_vou_setting_area )
    	&& $vendor_access_vou_setting_area == 'yes' ) ) {

        include_once( WOO_VOU_ADMIN . '/forms/woo-vou-user-meta.php' );
    }
}

/**
 * Function for update an user meta fields
 *
 * @package WooCommerce - PDF Vouchers
 * @since 1.5
 */
function woo_vou_update_profile_fields($user_id) {
	
	global $woo_vou_model;

    $prefix = WOO_VOU_META_PREFIX;

    // update pdf template to user meta
    if (isset($_POST[$prefix . 'pdf_template']))
        update_user_meta($user_id, $prefix . 'pdf_template', $_POST[$prefix . 'pdf_template']);

    // update pdf template to user meta
    if (isset($_POST[$prefix . 'using_type']))
        update_user_meta($user_id, $prefix . 'using_type', $_POST[$prefix . 'using_type']);

    // update vendor address to user meta
    if (isset($_POST[$prefix . 'address_phone']))
        update_user_meta($user_id, $prefix . 'address_phone', trim($woo_vou_model->woo_vou_escape_slashes_deep($_POST[$prefix . 'address_phone'], true, true)));

    // update vendor address to user meta
    if (isset($_POST[$prefix . 'siteurl_text']))
        update_user_meta($user_id, $prefix . 'website', trim($woo_vou_model->woo_vou_escape_slashes_deep($_POST[$prefix . 'siteurl_text'])));

    // update vendor logo to user meta
    if (isset($_POST[$prefix . 'logo']))
        update_user_meta($user_id, $prefix . 'logo', $_POST[$prefix . 'logo']);

    // update vendor Redeem Instructions to user meta
    if (isset($_POST[$prefix . 'how_to_use']))
        update_user_meta($user_id, $prefix . 'how_to_use', trim($woo_vou_model->woo_vou_escape_slashes_deep($_POST[$prefix . 'how_to_use'], true, true)));

    //update location and map links
    $availlocations = array();
    if (isset($_POST[$prefix . 'locations'])) {

        $locations = $_POST[$prefix . 'locations'];
        $maplinks = $_POST[$prefix . 'map_link'];
        for ($i = 0; $i < count($locations); $i++) {
            if (!empty($locations[$i]) || !empty($maplinks[$i])) { //if location or map link is not empty then
                $availlocations[$i][$prefix . 'locations'] = $woo_vou_model->woo_vou_escape_slashes_deep($locations[$i], true, true);
                $availlocations[$i][$prefix . 'map_link'] = $woo_vou_model->woo_vou_escape_slashes_deep($maplinks[$i]);
            }
        }
    }

    //update location and map links
    update_user_meta($user_id, $prefix . 'avail_locations', $availlocations);

    // update vendor sale email notification settings
    $vendor_sale_email_notification = isset($_POST[$prefix . 'enable_vendor_sale_email_notification']) && !empty($_POST[$prefix . 'enable_vendor_sale_email_notification']) ? "1" : "";
    update_user_meta($user_id, $prefix . 'enable_vendor_sale_email_notification', $vendor_sale_email_notification);
}

/**
 * Function for product variable meta
 *
 * @package WooCommerce - PDF Vouchers
 * @since 1.5
 */
function woo_vou_product_variable_meta($loop, $variation_data, $variation) {

    include( WOO_VOU_ADMIN . '/forms/woo-vou-product-variable-meta.php' );
}

/**
 * Function to save product variable meta
 *
 * @package WooCommerce - PDF Vouchers
 * @since 1.5
 */
function woo_vou_product_save_variable_meta($variation_id, $i) {
	
	global $woo_vou_model;
	
    if (!empty($variation_id)) {
			
        $prefix = WOO_VOU_META_PREFIX;

        $variation_pro 	= wc_get_product($variation_id); // Get variation id
        $product_id 	= $variation_pro->get_parent_id(); // Get product id
        $global_voucher_delivery_type 	= get_option('vou_voucher_delivery_options'); // Getting voucher delivery option
        $product_voucher_delivery_type 	= get_post_meta($product_id, $prefix . 'voucher_delivery', true); // product Voucher Delivery

        $variable_voucher_delivery = isset($_POST[$prefix . 'variable_voucher_delivery'][$i]) ? $_POST[$prefix . 'variable_voucher_delivery'][$i] : '';
        if( $variable_voucher_delivery == 'default' ) {

            $variable_voucher_delivery = !empty( $product_voucher_delivery_type ) ? $product_voucher_delivery_type : $global_voucher_delivery_type;
        }

        $variable_pdf_template = isset($_POST[$prefix . 'variable_pdf_template'][$i]) ? $woo_vou_model->woo_vou_escape_slashes_deep($_POST[$prefix . 'variable_pdf_template'][$i]) : '';

        $variable_pdt_code = isset($_POST[$prefix . 'variable_codes'][$i]) ? $woo_vou_model->woo_vou_escape_slashes_deep($_POST[$prefix . 'variable_codes'][$i]) : '';

        $variable_pdt_address = isset($_POST[$prefix . 'variable_vendor_address'][$i]) ? $woo_vou_model->woo_vou_escape_slashes_deep($_POST[$prefix . 'variable_vendor_address'][$i]) : '';

        $variable_vou_price = isset($_POST[$prefix . 'variable_voucher_price'][$i]) ? $woo_vou_model->woo_vou_escape_slashes_deep($_POST[$prefix . 'variable_voucher_price'][$i]) : '';
				
		$variable_voucher_expiration_date_type = isset($_POST[$prefix . 'variable_voucher_expiration_date_type'][$i]) ? $_POST[$prefix . 'variable_voucher_expiration_date_type'][$i] : '';
		
		$variable_voucher_expiration_start_date = isset($_POST[$prefix . 'variable_voucher_expiration_start_date'][$i]) ? $_POST[$prefix . 'variable_voucher_expiration_start_date'][$i] : '';
		
		$variable_voucher_expiration_end_date = isset($_POST[$prefix . 'variable_voucher_expiration_end_date'][$i]) ?$_POST[$prefix . 'variable_voucher_expiration_end_date'][$i] : '';
		
		$variable_voucher_day_diff = isset($_POST[$prefix . 'variable_voucher_day_diff'][$i]) ? $woo_vou_model->woo_vou_escape_slashes_deep($_POST[$prefix . 'variable_voucher_day_diff'][$i]) : '';
		
		$variable_voucher_expiration_custom_day = isset($_POST[$prefix . 'variable_voucher_expiration_custom_day'][$i]) ? $_POST[$prefix . 'variable_voucher_expiration_custom_day'][$i]: ''; 
		
        // Updating variable pdf template
        update_post_meta($variation_id, $prefix . 'pdf_template', $variable_pdf_template);
        
        // Updating variable voucher delivery
        update_post_meta($variation_id, $prefix . 'voucher_delivery', $variable_voucher_delivery);

        // Updating variable voucher code
        update_post_meta($variation_id, $prefix . 'codes', html_entity_decode( $variable_pdt_code ));

        // Updating variable voucher code
        update_post_meta($variation_id, $prefix . 'vendor_address', $variable_pdt_address);

        // Updating variable price meta
        update_post_meta($variation_id, $prefix . 'voucher_price', wc_format_decimal($variable_vou_price));
		
		 //Updating Variable expiration date type
        update_post_meta($variation_id, $prefix . 'variable_voucher_expiration_date_type',$variable_voucher_expiration_date_type);
		
		//Updating Variable expiration start date
        update_post_meta($variation_id, $prefix . 'variable_voucher_expiration_start_date', $variable_voucher_expiration_start_date);
		
		//Updating Variable expiration end date
        update_post_meta($variation_id, $prefix . 'variable_voucher_expiration_end_date', $variable_voucher_expiration_end_date);
		
		//Updating Variable expiration day diff
        update_post_meta($variation_id, $prefix . 'variable_voucher_day_diff', $variable_voucher_day_diff);
		
		//Updating Variable expiration day diff
        update_post_meta($variation_id, $prefix . 'variable_voucher_expiration_custom_day', $variable_voucher_expiration_custom_day);
		
		
    }
}

/**
 * Function to unlink the pdf voucher from the folder
 * If the file is created before 2 hours
 * File creation time is in UTC
 *
 * @package WooCommerce - PDF Vouchers
 * @since 2.0
 */
function woo_vou_flush_upload_dir() {

    // Get pdf vouchers from the upload dir
    $vou_pdf_files = array_merge( glob(WOO_VOU_UPLOAD_DIR . '*.pdf'), glob(WOO_VOU_PREVIEW_UPLOAD_DIR . '*.pdf') );

    if (!empty($vou_pdf_files)) {

        foreach ($vou_pdf_files as $vou_pdf_files_key => $vou_pdf_files_val) {

            // If file exist in folder
            if (file_exists($vou_pdf_files_val)) {

                // Getting voucher pdf creation time in UTC format
                $vou_time = date_i18n('Y-m-d H:i:s', filemtime($vou_pdf_files_val), 'gmt');

                // Getting current time in UTC format
                $current_time = date_i18n('Y-m-d H:i:s', false, 'gmt');

                // Getting time difference of file
                $timediff = round((strtotime($current_time) - strtotime($vou_time)) / (3600), 1);

                // If file is created before 2 houes
                if (!empty($timediff) && $timediff > 2) {
                    unlink($vou_pdf_files_val);
                }
            } // End of file exist
        }
    } // End of main if
}

/**
 * Add Email Class In Woocommerce
 * 
 * Handle to add email class to wocommerce
 *
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.4
 */
function woo_vou_add_email_classes($email_classes) {

    //Include vendor sale notification email class file
    require_once ( WOO_VOU_ADMIN . '/class-woo-vou-vendor-sale.php' );
    $email_classes['Woo_Vou_Vendor_Sale'] = new Woo_Vou_Vendor_Sale();

    //Include gift notification email class file
    require_once ( WOO_VOU_ADMIN . '/class-woo-vou-gift-notification.php' );
    $email_classes['Woo_Vou_Gift_Notification'] = new Woo_Vou_Gift_Notification();

    //Include voucher redeem notification email class file
    require_once ( WOO_VOU_ADMIN . '/class-woo-vou-redeem-notification.php' );
    $email_classes['Woo_Vou_Redeem_Notification'] = new Woo_Vou_Redeem_Notification();

    return $email_classes;
}

/**
 * Delete voucher codes
 *
 * @package WooCommerce - PDF Vouchers
 * @since 2.6.3
 */
function woo_vou_delete_vou_codes() {

    // check if action is not blank and page is woo voucher code
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['page']) && $_GET['page'] == 'woo-vou-codes' && isset($_GET['vou-data']) && ( $_GET['vou-data'] == 'purchased' || $_GET['vou-data'] == 'used' || $_GET['vou-data'] == 'expired' || $_GET['vou-data'] == 'partially-redeemed')) {

        // get redirect url
        $redirect_url = add_query_arg(array('page' => 'woo-vou-codes', 'vou-data' => $_GET['vou-data']), admin_url('admin.php'));

        if (isset($_GET['code_id']) && !empty($_GET['code_id']) && !get_post_status($_GET['order_id'])) {

            $delete_post = wp_delete_post($_GET['code_id']);

            if ($delete_post)
                $redirect_url = add_query_arg(array('message' => '1'), $redirect_url);
        }
        wp_redirect($redirect_url);
        exit;
    }
}

/**
 * Handles to change status to 'on-hold', during checkout through 'COD',
 * if PDF Voucher is enabled at product level
 *
 * @package WooCommerce - PDF Vouchers
 * @since 2.9.0
 */
function woo_vou_cod_process_payment_order_status_func($order_status, $order) {
	
	global $woo_vou_model;

    $prefix = WOO_VOU_META_PREFIX;

    foreach ($order->get_items() as $item) {
        $_product = $order->get_product_from_item($item);

        if ($_product && $_product->exists()) {

            $product_id = $woo_vou_model->woo_vou_get_item_productid_from_product($_product);
            $woo_vou_pro_enable = get_post_meta($product_id, $prefix . 'enable', true);

            if (!empty($woo_vou_pro_enable) && $woo_vou_pro_enable == 'yes' && $_product->is_downloadable()) {

                $order_status = 'on-hold';
                break;
            }
        }
    }

    return $order_status;
}

/**
 * Handles to save data in coupon's meta
 *
 * @package WooCommerce - PDF Vouchers
 * @since 2.9.2
 */
function woo_vou_save_coupon_options($post_id) {

    // Get prefix
    $prefix = WOO_VOU_META_PREFIX;

    // Get coupon start date from $_POST
    $woo_vou_start_date = wc_clean($_POST[$prefix . 'start_date']);

    // Get restriction days from $_POST
    $woo_vou_rest_days = isset($_POST[$prefix . 'disable_redeem_day']) ? $_POST[$prefix . 'disable_redeem_day'] : array();

    $exclude_discount_on_tax = isset($_POST[$prefix . 'discount_on_tax_type']) ? $_POST[$prefix . 'discount_on_tax_type'] : '';

    // Update coupon start date in coupon meta
    update_post_meta($post_id, $prefix . 'start_date', $woo_vou_start_date);

    // Update restriction days in coupon meta
    update_post_meta($post_id, $prefix . 'disable_redeem_day', $woo_vou_rest_days);

    update_post_meta($post_id, $prefix . 'discount_on_tax_type', $exclude_discount_on_tax );
}

/**
 * Handles to modify get vouchers arguments
 *
 * @package WooCommerce - PDF Vouchers
 * @since 2.9.7
 */
function woo_vou_get_vouchers_args_func($args) {

    global $woo_vou_vendor_role;

    //Restrict voucher templates to view only that vendor
    $curr_userobj = wp_get_current_user();
    $curr_role = !empty($curr_userobj->roles[0]) ? $curr_userobj->roles[0] : '';
    $admin_ids = get_users(array('role' => 'administrator', 'fields' => 'IDs'));
    if (!empty($curr_role) && is_array($woo_vou_vendor_role) && in_array($curr_role, $woo_vou_vendor_role)) {
        if (!empty($curr_userobj->ID))
            $admin_ids[] = $curr_userobj->ID;
        $args['author__in'] = $admin_ids;
    }

    return $args;
}

/**
 * Handles to customize voucher template listing query
 *
 * @package WooCommerce - PDF Vouchers
 * @since 2.9.7
 */
function woo_vou_post_list_query_filter_func($query) {

    // Create page array
	$pages = array(WOO_VOU_POST_TYPE);

	// If current screen is on admin side and post type is WOO_VOU_POST_TYPE
	if(is_admin() && isset($query->query['post_type']) && in_array($query->query['post_type'], $pages)) {

		// Declare global variable
	    global $woo_vou_vendor_role;

	    $curr_userobj = wp_get_current_user(); // Get current user
	    $curr_role = !empty($curr_userobj->roles[0]) ? $curr_userobj->roles[0] : ''; // Get current user role
	    $admin_ids = get_users(array('role' => 'administrator', 'fields' => 'IDs')); // Get admin ids
	
	    // Check if post type is Voucher Template & is admin
	    if ( !empty($curr_role) && is_array($woo_vou_vendor_role) && in_array($curr_role, $woo_vou_vendor_role)) {
	
	        if (!empty($curr_userobj->ID)) {
	            $admin_ids[] = $curr_userobj->ID;
	        }

	        $query->set('author__in', $admin_ids);
	    }
	}
}

/**
 * Handles to add voucher price in variation meta
 * under sale price
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.9.8
 */
function woo_vou_variation_options_add_voucher_price($loop, $variation_data, $variation) {

    $prefix = WOO_VOU_META_PREFIX; // Get prefix
    $price_options = get_option('vou_voucher_price_options'); // Get voucher price options
    $variation_id = isset($variation->ID) ? $variation->ID : '';  // Get variation id
    $_voucher_price = get_post_meta($variation_id, $prefix . 'voucher_price', true); // Getting voucher code
    // Check if price_option is set to voucher price
    if (!empty($price_options) && $price_options == 2) {
        ?>
        <p class="form-row show_if_variation_downloadable">
            <label><?php echo esc_html__('Voucher price', 'woovoucher') . ' (' . get_woocommerce_currency_symbol() . ')'; ?>
                <input id="<?php echo $prefix; ?>voucher_price" type="text" size="5" name="<?php echo $prefix; ?>variable_voucher_price[<?php echo $loop; ?>]" value="<?php if (isset($_voucher_price)) echo esc_attr($_voucher_price); ?>" class="wc_input_price" />
        </p>
        <?php
    }
}

/**
 * Handles to modify post variable while edit coupon
 * if coupon type is voucher_code
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.9.8
 */
function woo_vou_save_coupon_code($post_title) {

    $prefix = WOO_VOU_META_PREFIX; // Get prefix
    $post_id = !empty($_POST['post_ID']) ? $_POST['post_ID'] : ''; // Get post id
    // If post id is not empty in $_POST
    if (!empty($post_id)) {

        $coupon_type = get_post_meta($post_id, $prefix . 'coupon_type', true); // Get coupon type
        // If coupon type is voucher_code
        if (!empty($coupon_type) && $coupon_type == 'voucher_code') {

            if (!isset($_POST['discount_type'])) { // If discount_type is not set in $_POST
                $_POST['discount_type'] = get_post_meta($post_id, 'discount_type', true); // Set discount_type from previous data
            }

            if (!isset($_POST['coupon_amount'])) { // If coupon_amount is not set in $_POST
                $_POST['coupon_amount'] = get_post_meta($post_id, 'coupon_amount', true); // Set coupon_amount from previous data
            }

            if (!isset($_POST['usage_limit'])) { // If usage_limit is not set in $_POST
                $_POST['usage_limit'] = get_post_meta($post_id, 'usage_limit', true); // Set usage_limit from previous data
            }

            if (!isset($_POST['expiry_date'])) { // If expiry_date is not set in $_POST
                $_POST['expiry_date'] = get_post_meta($post_id, 'expiry_date', true); // Set expiry_date from previous data
            }

            if (!isset($_POST[$prefix . 'start_date'])) { // If start_date is not set in $_POST
                $_POST[$prefix . 'start_date'] = get_post_meta($post_id, $prefix . 'start_date', true); // Set start_date from previous meta
            }

            if (!isset($_POST[$prefix . 'disable_redeem_day'])) { // If diable_redeem_day is not set in $_POST
                $_POST[$prefix . 'disable_redeem_day'] = get_post_meta($post_id, $prefix . 'disable_redeem_day', true); // Set disable_redeem_day from previous data
            }
        }
    }

    // Return post_title
    return $post_title;
}

/**
 * Handles to add plugin settings in Woocommerce -> Settings
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.9.8
 */
function woo_vou_admin_settings_tab($settings) {

    // Add settings in array
    $settings[] = include( WOO_VOU_ADMIN . '/class-woo-vou-admin-settings-tabs.php' );

    return $settings; // Return
}

/**
 * Ajax call to validate product start and end date
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.9.8
 */
function woo_vou_product_pre_submit_validation() {

    //simple Security check
    check_ajax_referer('woo_vou_pre_publish_validation', 'security');

    //convert the string of data received to an array
    parse_str($_POST['form_data'], $vars);

    if (isset($vars['_woo_vou_product_exp_date'])) {

        $start_date = (isset($vars['_woo_vou_product_start_date'])) ? $vars['_woo_vou_product_start_date'] : '';
        $end_date = (isset($vars['_woo_vou_product_exp_date'])) ? $vars['_woo_vou_product_exp_date'] : '';

        if ($end_date < $start_date) {
            esc_html_e('Product End date/time cannot before Start date/time');
            die();
        } elseif ($end_date == $start_date) {
            esc_html_e('Product End date/time cannot equal to Start date/time');
            die();
        }
    }
    //everything ok, allow submission
    echo 'true';
    die();
}


/**
 * Handles to remove builder functionality for enfold theme
 *
 * @package WooCommerce - PDF Vouchers
 * @since 3.3.4
 */
function woo_vou_enfold_remove_builder(){

    // If Enfold theme is active
    if ( class_exists( 'AviaBuilder' ) ) {

        global $typenow, $pagenow;

        $builder_object = Avia_Builder();

        //If typenow is empty
        if( empty( $typenow ) ) {

            if ( !empty( $_GET['post'] ) ) {// try to pick it up from the query string

                $post       = get_post( $_GET['post'] );
                $typenow    = $post->post_type;
            } elseif( !empty( $_POST['post_ID'] ) ) {// try to pick it up from the quick edit AJAX post

                $post       = get_post( $_POST['post_ID'] );
                $typenow    = $post->post_type;
            }
        }

        // If page is add/edit post and post type is woovouchers
        if( ( $pagenow == 'post-new.php' || $pagenow == 'post.php' ) && $typenow == 'woovouchers' ) {

        	// Remove enfold builder functionality
            remove_action( 'load-post.php', array( $builder_object, 'admin_init') , 5 );
            remove_action( 'load-post-new.php', array( $builder_object, 'admin_init') , 5 );
        }
    }
}

/**
 * 
 *
 * @package WooCommerce - PDF Vouchers
 * @since 3.6.8
 */
function woo_vou_voucher_redeem_popup(){

    global $current_user, $woo_vou_model, $woo_vou_voucher, $woo_vou_vendor_role;

    $prefix = WOO_VOU_META_PREFIX;
    $response['error'] = false;

    //get voucher admin roles
    $admin_roles    = woo_vou_assigned_admin_roles();
    //Get User roles
    $user_roles     = isset( $current_user->roles ) ? $current_user->roles : array();
    $user_role      = array_shift( $user_roles );

    // Declare variables
    $voucodeid  = isset($_POST['voucher_id']) ? $_POST['voucher_id'] : 0 ;
    $voucode    = get_post_meta( $voucodeid, $prefix.'purchased_codes', true );

    // Set response data
    $response['success_status']  = ( !empty($voucode) || !empty($voucodeid) )? true : false;
    $response['voucher_id']      = ( !empty($voucodeid) )? $voucodeid : '';
    $response['voucher_code']    = ( !empty($voucode) )? $voucode : '';

    // Get option whether to allow all vendor to redeem voucher codes
    $vou_enable_vendor_access_all_voucodes      = get_option('vou_enable_vendor_access_all_voucodes');
    
    //Get voucher redeem methods
    $redeem_methods = apply_filters( 'woo_vou_redeem_methods', array(
                            'full'      => esc_html__( 'Full', 'woovoucher' ),
                            'partial'   => esc_html__( 'Partial', 'woovoucher' )
                        ));

    if( !empty( $voucodeid ) ) {		
		
        //get vouchercodes data 
        $voucher_data   = get_post( $voucodeid );
        $order_id       = get_post_meta( $voucodeid , $prefix.'order_id' , true );
        $cart_details   = new Wc_Order( $order_id );

        if( !empty($order_id) && !empty($cart_details) ){

            $order_items    = $cart_details->get_items();

            $item_array = $woo_vou_model->woo_vou_get_item_data_using_voucher_code( $order_items, $voucode );

            $item       = isset( $item_array['item_data'] ) ? $item_array['item_data'] : array();
            $item_id    = isset( $item_array['item_id'] ) ? $item_array['item_id'] : array();

            //get product data
            $product_name  = isset( $item['name'] ) ? $item['name'] : '' ;
            $product_id    = isset( $item['product_id'] ) ? $item['product_id'] : '' ;
            $variation_id  = isset( $item['variation_id'] ) ? $item['variation_id'] : '' ;
            $data_id       = !empty($variation_id) ? $variation_id : $product_id ;

            // get total price of voucher code
            $vou_code_total_price   =  $woo_vou_model->woo_vou_get_product_price( $order_id, $item_id, $item );

            // get remaining price for redeem
            $vou_code_remaining_redeem_price = number_format( (float)($vou_code_total_price), 2, '.', '' );

            // get partial redeem
            $enable_partial_redeem = apply_filters( 'woo_vou_enable_partial_redeem_during_check_voucher', woo_vou_check_partial_redeem_by_product_id( $data_id ), $order_id, $voucode );

            $enable_partial_redeem = !empty( $enable_partial_redeem ) && $enable_partial_redeem == 1 ? 'yes' : 'no';
            
            // Allow unlimited redeem
            $allow_unlimited_redeem = get_option('vou_allow_unlimited_redeem_vou_code');

            // if partial redeem is enabled
            if( $enable_partial_redeem == "yes" || ( !empty( $allow_unlimited_redeem ) && $allow_unlimited_redeem == "yes" ) ) {

                // get total redeemed price
                $vou_code_total_redeemed_price = $woo_vou_voucher->woo_vou_get_total_redeemed_price_for_vouchercode( $voucodeid );

                // get remaining price for redeem
                $vou_code_remaining_redeem_price = number_format( (float)($vou_code_total_price - $vou_code_total_redeemed_price), 2, '.', '' );
            }

            //voucher start date
            $start_Date = get_post_meta( $voucodeid , $prefix .'start_date' ,true );
            
            //voucher expired date
            $expiry_Date = get_post_meta( $voucodeid , $prefix .'exp_date' ,true );
            
            $response['success'] = apply_filters( 'woo_vou_voucher_code_valid_message', sprintf( esc_html__( "This voucher code has been bought for %s. \nIf you would like to redeem voucher code, Please click on the redeem button below:", 'woovoucher' ), $product_name ), $product_name, $voucodeid );

            if( !empty( $product_id) ) {                
                $disable_redeem_days = get_post_meta( $voucodeid, $prefix.'disable_redeem_day', true );             
                if( !empty($disable_redeem_days ) ) { // check days are selected                    
                    $current_day = date('l');
                    
                    if( in_array( $current_day, $disable_redeem_days ) ) { // check current day redeem is enable or not
                        $message = implode(", ", $disable_redeem_days );

                        $response['success'] = apply_filters( 'woo_vou_voucher_code_disabled_message', sprintf( esc_html__( "Sorry, voucher code is not allowed to be used on %s. \n" ,'woovoucher'), $message ,$product_name ));
                        $response['error'] = true;
                    }
                }
            }
            
            if( isset( $start_Date ) && !empty( $start_Date ) ) {

                if( $start_Date > $woo_vou_model->woo_vou_current_date() ) {

                    $response['success'] = apply_filters( 'woo_vou_voucher_code_before_start_message', sprintf( esc_html__( "Voucher code cannot be redeemed before %s for %s. \n" ,'woovoucher'), $woo_vou_model->woo_vou_get_date_format( $start_Date , true ) ,$product_name ), $product_name, $start_Date, $voucodeid );
                    $response['error'] = true;
                }
            }

            // Include file to get redeem option and price.
            include_once( WOO_VOU_ADMIN . '/forms/metabox-popups/woo-vou-voucher-redeem-option.php' );

        }
    
    }
    $response = array_map('utf8_encode', $response);
    echo json_encode( $response );
    exit();

}