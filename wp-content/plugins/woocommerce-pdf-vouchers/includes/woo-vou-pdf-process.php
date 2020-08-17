<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

function woo_vou_generate_pdf_by_html($html = '', $pdf_args = array()) {

    if (!class_exists('WPWEB_TCPDF')) { //If class not exist
        //include tcpdf file
        require_once WOO_VOU_DIR . '/includes/tcpdf/tcpdf.php';
    }

    // Get prefix
    $prefix = WOO_VOU_META_PREFIX;

    $pdf_margin_top = WPWEB_PDF_MARGIN_TOP;
    $pdf_margin_bottom = WPWEB_PDF_MARGIN_BOTTOM;
    $pdf_margin_left = WPWEB_PDF_MARGIN_LEFT;
    $pdf_margin_right = WPWEB_PDF_MARGIN_RIGHT;
    $pdf_bg_image = '';
    $vou_template_pdf_view = '';

    // get voucher template id
    $pdf_template_id = !empty($pdf_args['vou_template_id']) ? $pdf_args['vou_template_id'] : '';

    //check need to save/open pdf in browser
    $pdf_enable_preview = get_option('vou_enable_preview_in_browser');

    // This is default font
    $pdf_font = 'helvetica';

    if (!empty($pdf_args['char_support'])) { // if character support is checked
        $pdf_font = 'freeserif';
    }

    // Get PDF Title, Author name and Creater name 
    $vou_pdf_title   = get_option( 'vou_pdf_title' );
    $vou_pdf_author  = get_option( 'vou_pdf_author' );
    $vou_pdf_creator = get_option( 'vou_pdf_creator' );

    // Get option of pdf password protected
    $vou_enable_pdf_password  = get_option( 'vou_enable_pdf_password_protected' );
    $vou_pdf_password_pattern = get_option( 'vou_pdf_password_pattern' );

    $vou_pdf_author  = !empty( $vou_pdf_author ) ? $vou_pdf_author : esc_html__('WooCommerce', 'woovoucher');
    $vou_pdf_creator = !empty( $vou_pdf_creator ) ? $vou_pdf_creator : esc_html__('WooCommerce', 'woovoucher');
    $vou_pdf_title 	 = !empty( $vou_pdf_title ) ? $vou_pdf_title : esc_html__('WooCommerce Voucher', 'woovoucher');

    $pdf_save = !empty($pdf_args['save_file']) ? true : false; // Pdf store in a folder or not
    $font_size = 12;

    if (isset($pdf_args['vou_template_id']) && !empty($pdf_args['vou_template_id'])) {

        global $woo_vou_template_id;

        //Voucher PDF ID
        $woo_vou_template_id = $pdf_args['vou_template_id'];

        //Get pdf size meta
        $woo_vou_template_size = get_post_meta($woo_vou_template_id, $prefix . 'pdf_size', true);
        $woo_vou_template_size = !empty($woo_vou_template_size) ? $woo_vou_template_size : 'A4';

        //Get size array
        $woo_vou_allsize_array = woo_vou_get_pdf_sizes();

        $woo_vou_size_array = $woo_vou_allsize_array[$woo_vou_template_size];

        $pdf_width = isset($woo_vou_size_array['width']) ? $woo_vou_size_array['width'] : '210';
        $pdf_height = isset($woo_vou_size_array['height']) ? $woo_vou_size_array['height'] : '297';
        $font_size = isset($woo_vou_size_array['fontsize']) ? $woo_vou_size_array['fontsize'] : '12';

        // Extend the TCPDF class to create custom Header and Footer
        if (!class_exists('VOUPDF')) {

            class VOUPDF extends WPWEB_TCPDF {

                function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false) {

                    // Call parent constructor
                    parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
                }

                //Page header
                public function Header() {

                    global $woo_vou_model, $woo_vou_template_id;

                    //model class
                    $model = $woo_vou_model;

                    $prefix = WOO_VOU_META_PREFIX;

                    $vou_relative_path_option = get_option('vou_enable_relative_path'); // Get global setting for Relative path
                    $woo_vou_img_path = !empty($vou_relative_path_option) && $vou_relative_path_option == 'yes' ? WOO_VOU_IMG_DIR : WOO_VOU_IMG_URL; // Set image path to directory or url depending on global setting

                    $vou_template_bg_style = get_post_meta($woo_vou_template_id, $prefix . 'pdf_bg_style', true);
                    $vou_template_bg_pattern = get_post_meta($woo_vou_template_id, $prefix . 'pdf_bg_pattern', true);
                    $vou_template_bg_img = get_post_meta($woo_vou_template_id, $prefix . 'pdf_bg_img', true);
                    $vou_template_bg_color = get_post_meta($woo_vou_template_id, $prefix . 'pdf_bg_color', true);
                    $vou_template_pdf_view = get_post_meta($woo_vou_template_id, $prefix . 'pdf_view', true);

                    //Get pdf size meta
                    $woo_vou_template_size = get_post_meta($woo_vou_template_id, $prefix . 'pdf_size', true);
                    $woo_vou_template_size = !empty($woo_vou_template_size) ? $woo_vou_template_size : 'A4';

                    //Get size array
                    $woo_vou_allsize_array = woo_vou_get_pdf_sizes();

                    $woo_vou_size_array = $woo_vou_allsize_array[$woo_vou_template_size];

                    $pdf_width = isset($woo_vou_size_array['width']) ? $woo_vou_size_array['width'] : '210';
                    $pdf_height = isset($woo_vou_size_array['height']) ? $woo_vou_size_array['height'] : '297';
                    $font_size = isset($woo_vou_size_array['fontsize']) ? $woo_vou_size_array['fontsize'] : '12';

                    //Voucher PDF Background Color
                    if (!empty($vou_template_bg_color)) {

                        if ($vou_template_pdf_view == 'land') { // Check PDF View option is landscape
                            // Background color
                            $this->Rect(0, 0, $pdf_height, $pdf_width, 'F', '', $fill_color = $model->woo_vou_hex_2_rgb($vou_template_bg_color));
                        } else {

                            // Background color      
                            $this->Rect(0, 0, $pdf_width, $pdf_height, 'F', '', $fill_color = $model->woo_vou_hex_2_rgb($vou_template_bg_color));
                        }
                    }

                    //Voucher PDF Background style is image & image is not empty
                    if (!empty($vou_template_bg_style) && $vou_template_bg_style == 'image' && isset($vou_template_bg_img['src']) && !empty($vou_template_bg_img['src'])) {

                        if (!empty($vou_relative_path_option) && $vou_relative_path_option == 'yes') {

                            $vou_site_attachment_id = $model->woo_vou_get_attachment_id_from_url($vou_template_bg_img['src']);
                            $img_file = get_attached_file($vou_site_attachment_id);
                        } else {
                            $img_file = $vou_template_bg_img['src'];
                        }
                    } else if (!empty($vou_template_bg_style) && $vou_template_bg_style == 'pattern' && !empty($vou_template_bg_pattern)) {//Voucher PDF Background style is pattern & Background Pattern is not selected
                        if ($vou_template_pdf_view == 'land') { // Check PDF View option is landscape
                            // Background Pattern Image
                            $img_file = $woo_vou_img_path . '/patterns/' . $vou_template_bg_pattern . '.png';
                        } else {
                            // Background Pattern Image      
                            $img_file = $woo_vou_img_path . '/patterns/port_' . $vou_template_bg_pattern . '.png';
                        }
                    }

                    if (!empty($img_file)) { //Check image file
                        // get the current page break margin
                        $bMargin = $this->getBreakMargin();
                        // get current auto-page-break mode
                        $auto_page_break = $this->AutoPageBreak;
                        // disable auto-page-break
                        $this->SetAutoPageBreak(false, 0);

                        if ($vou_template_pdf_view == 'land') { // Check PDF View option is landscape

                            // If any one using custom pdf size then pass width and height as it is
                            if( !in_array($woo_vou_template_size, woo_vou_get_default_pdf_sizes() ) ) {
                                $land_width = $pdf_width;
                                $land_height = $pdf_height;
                            } else {
                                // Make pdf width as height and pdf height as width in Landscape mode
                                $land_width = $pdf_height;
                                $land_height = $pdf_width;
                            }

                            // Background image
                            $this->Image($img_file, 0, 0, $land_width, $land_height, '', '', '', false, 300, '', false, false, 0);
                        } else {

                            // Background image
                            $this->Image($img_file, 0, 0, $pdf_width, $pdf_height, '', '', '', false, 300, '', false, false, 0);
                        }
                        
                        // restore auto-page-break status
                        $this->SetAutoPageBreak($auto_page_break, $bMargin);

                        
                        // set the starting point for the page content
                        $this->setPageMark();
                    }

                    // Store pdf in a folder
                    if( !empty( $_POST ) && array_key_exists( 'is_preview', $_POST ) ) {

                        $img_file           = $woo_vou_img_path.'/preview.png';
                        $preview_img_option = get_option( 'vou_preview_image' );

                        if( !empty( $preview_img_option ) ) {

                            $upload_dir = wp_upload_dir();
                            $base_url   = $upload_dir['baseurl'];
                            $img_file   = $base_url.$preview_img_option;
                        }

                        $img_file = apply_filters( 'woo_vou_preview_img', $img_file );

                        if( $img_file ) {

                            // Render the image
                            if( $vou_template_pdf_view == 'land') { // Landscape mode
                              $this->Image($img_file, 10, 25, 500, 300, 'PNG', '', 'C', false, 300, '', false, false, 0);
                            } else { // portrait mode
                                $this->Image($img_file, 10, 25, 225, 300, 'PNG', '', 'C', false, 300, '', false, false, 0);
                            }
                        }
                    }
                }
            }
        } // class exist
        //Voucher PDF Margin Top
        $vou_template_pdf_view = get_post_meta($woo_vou_template_id, $prefix . 'pdf_view', true);

        //Voucher PDF Margin Top
        $vou_template_margin_top = get_post_meta($woo_vou_template_id, $prefix . 'pdf_margin_top', true);
        if (!empty($vou_template_margin_top)) {
            $pdf_margin_top = $vou_template_margin_top;
        }
        //Voucher PDF Margin Top
        $vou_template_margin_bottom = get_post_meta($woo_vou_template_id, $prefix . 'pdf_margin_bottom', true);
        if (!empty($vou_template_margin_bottom)) {
            $pdf_margin_bottom = $vou_template_margin_bottom;
        }

        //Voucher PDF Margin Left
        $vou_template_margin_left = get_post_meta($woo_vou_template_id, $prefix . 'pdf_margin_left', true);
        if (!empty($vou_template_margin_left)) {
            $pdf_margin_left = $vou_template_margin_left;
        }

        //Voucher PDF Margin Right
        $vou_template_margin_right = get_post_meta($woo_vou_template_id, $prefix . 'pdf_margin_right', true);
        if (!empty($vou_template_margin_right)) {
            $pdf_margin_right = $vou_template_margin_right;
        }

        if ($vou_template_pdf_view == 'land') {
            $pdf_size_param = array($pdf_width, $pdf_height);
        } else {
            $pdf_size_param = array($pdf_height, $pdf_width);
        }

        // create new PDF document
        $pdf = new VOUPDF(WPWEB_PDF_PAGE_ORIENTATION, WPWEB_PDF_UNIT, $pdf_size_param, true, 'UTF-8', false);
    } else {

        $woo_vou_template_size = 'A4';

        // create new PDF document
        $pdf = new WPWEB_TCPDF(WPWEB_PDF_PAGE_ORIENTATION, WPWEB_PDF_UNIT, WPWEB_PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // remove default header
        $pdf->setPrintHeader(false);
    }

    // remove default footer
    $pdf->setPrintFooter(false);

    // Auther name and Creater name 
    $pdf->SetCreator(utf8_decode(apply_filters('woo_vou_set_pdf_creator', $vou_pdf_creator )));
    $pdf->SetAuthor(utf8_decode(apply_filters('woo_vou_set_pdf_author', $vou_pdf_author )));
    $pdf->SetTitle(utf8_decode(apply_filters('woo_vou_set_pdf_title', $vou_pdf_title )));


    // set header and footer fonts
    $pdf->setHeaderFont(Array(WPWEB_PDF_FONT_NAME_MAIN, '', WPWEB_PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(WPWEB_PDF_FONT_NAME_DATA, '', WPWEB_PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(WPWEB_PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins($pdf_margin_left, $pdf_margin_top, $pdf_margin_right);
    $pdf->SetHeaderMargin(WPWEB_PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(WPWEB_PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, $pdf_margin_bottom);

    // set image scale factor
    $pdf->setImageScale(WPWEB_PDF_IMAGE_SCALE_RATIO);

    // set default font subsetting mode
    $pdf->setFontSubsetting(true);

    // ---------------------------------------------------------
    // set font
    $pdf->SetFont(apply_filters('woo_vou_pdf_generate_fonts', $pdf_font, $pdf_template_id), '', $font_size);

    // add a page
    if ($vou_template_pdf_view == 'land') { // Check PDF View option is landscape
        $pdf->AddPage('L');
    } else {
        $pdf->AddPage();
    }

    do_action('woo_vou_voucher_pdf_after_backgound', $pdf, $pdf_template_id);

    // set cell margins
    $pdf->setCellMargins(0, 1, 0, 1);

    // set font color
    $pdf->SetTextColor(50, 50, 50);
    $pdf->SetFillColor(238, 238, 238);

    $vou_relative_path_option = get_option('vou_enable_relative_path'); // Get global setting for Relative path
    $woo_vou_img_path = !empty($vou_relative_path_option) && $vou_relative_path_option == 'yes' ? WOO_VOU_IMG_DIR : WOO_VOU_IMG_URL; // Set image path to directory or url depending on global setting

    $pdf_vou_codes = !empty($pdf_args['vou_codes']) ? $pdf_args['vou_codes'] : '';
    $pdf_args_vou_codes = !empty($pdf_vou_codes) ? explode(',', $pdf_vou_codes) : array();

    //Get QR code Dimantion
    $qrcode_dimention = apply_filters('woo_vou_qrcode_dimention', array(
        'width' => round($font_size * 1.5),
        'height' => round($font_size * 1.5)
            ), $font_size, $woo_vou_template_size);

    $qrcode_code_w = isset($qrcode_dimention['width']) ? $qrcode_dimention['width'] : '';
    $qrcode_code_h = isset($qrcode_dimention['height']) ? $qrcode_dimention['height'] : '';

    //Get Bar code Dimantion
    $barcode_dimention = apply_filters('woo_vou_barcode_dimention', array(
        'width' => round($font_size * 1.5) * 5,
        'height' => round($font_size * 1.5)
            ), $font_size, $woo_vou_template_size);

    $barcode_code_w = isset($barcode_dimention['width']) ? $barcode_dimention['width'] : '';
    $barcode_code_h = isset($barcode_dimention['height']) ? $barcode_dimention['height'] : '';

    //initilize qrcode and barcode settings
    $pdf->serializeTCPDFtagParameters(array());

    if (!empty($pdf_vou_codes) && strpos($html, '{qrcodes}') !== false) {// If qrcodes is there
        $vou_qr_msg = $vou_qrcode = '';
        if (!empty($pdf_args_vou_codes)) {

            $vou_qrcode .= '<table>';

            foreach ($pdf_args_vou_codes as $pdf_args_vou_code) {

                if (!empty($pdf_args_vou_code)) {


                    $vou_qrcode .= '<tr><td>';

                    $vou_qr_msg = trim($pdf_args_vou_code);

                    // make qrcode url used at scanning time
                    $vou_qr_msg = site_url() . "?woo_vou_code=" . urlencode($vou_qr_msg);

                    $vou_qr_params = $pdf->serializeTCPDFtagParameters(array($vou_qr_msg, 'QRCODE,H', '', '', $qrcode_code_w, $qrcode_code_h, array('border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100), 'N'));
                    $vou_qrcode .= '<tcpdf method="write2DBarcode" params="' . $vou_qr_params . '" />';

                    $vou_qrcode .= '</td></tr>';
                }
            }

            $vou_qrcode .= '</table>';
        }

        $html = str_replace('{qrcodes}', $vou_qrcode, $html);
    }

    if (!empty($pdf_vou_codes) && strpos($html, '{qrcode}') !== false) {// If qrcode is there
        $vou_qr_msg = $vou_qrcode = '';

        $vou_qr_msg = trim($pdf_vou_codes);

        // make qrcode url used at scanning time

        $vou_qr_msg = site_url() . "?woo_vou_code=" . $vou_qr_msg;

        $vou_qr_params = $pdf->serializeTCPDFtagParameters(array($vou_qr_msg, 'QRCODE,H', '', '', $qrcode_code_w, $qrcode_code_h, array('border' => 1, 'padding' => 1, 'fgcolor' => array(0, 0, 0), 'fontsize' => 100), 'N'));
        $vou_qrcode .= '<tcpdf method="write2DBarcode" params="' . $vou_qr_params . '" />';

        $html = str_replace('{qrcode}', $vou_qrcode, $html);
    }

    if (!empty($pdf_vou_codes) && strpos($html, '{barcode}') !== false) {// If barcode is there
        $vou_bar_msg = $vou_barcode = '';

        $vou_bar_msg = trim($pdf_vou_codes);

        // make barcode url used at scanning time
        $vou_bar_msg = $vou_bar_msg;

        $vou_bar_params = $pdf->serializeTCPDFtagParameters(array($vou_bar_msg, 'C128', '', '', $barcode_code_w, $barcode_code_h, 0.2, array('position' => 'S', 'border' => false, 'padding' => 'auto', 'fgcolor' => array(0, 0, 0), 'text' => false, 'font' => 'helvetica', 'fontsize' => 100, 'stretchtext' => 10), 'N'));

        $vou_barcode .= '<tcpdf method="write1DBarcode" params="' . $vou_bar_params . '" />';

        $html = str_replace('{barcode}', $vou_barcode, $html);
    }

    if (!empty($pdf_vou_codes) && strpos($html, '{barcodes}') !== false) {// If barcodes is there
        $vou_qr_msg = $vou_barcode = '';

        if (!empty($pdf_args_vou_codes)) {

            foreach ($pdf_args_vou_codes as $pdf_args_vou_code) {

                $vou_bar_msg = trim($pdf_args_vou_code);

                // make qrcode url used at scanning time
                $vou_bar_msg = $vou_bar_msg;

                $vou_bar_params = $pdf->serializeTCPDFtagParameters(array($vou_bar_msg, 'C128', '', '', $barcode_code_w, $barcode_code_h, 0.2, array('position' => 'S', 'border' => false, 'padding' => 'auto', 'fgcolor' => array(0, 0, 0), 'text' => false, 'font' => 'helvetica', 'fontsize' => 100, 'stretchtext' => 10), 'N'));
                $vou_barcode .= '<tcpdf method="write1DBarcode" params="' . $vou_bar_params . '" />';
            }
        }

        $html = str_replace('{barcodes}', $vou_barcode, $html);
    }

    if (!empty($pdf_vou_codes) && strpos($html, '{productimage}') !== false) {// If qrcode is there
        //Get Product Image dimension
        $product_image_dimention = apply_filters('woo_vou_qrcode_dimention', array(
            'width' => 100,
            'height' => 100
                ), $font_size, $woo_vou_template_size);

        $product_image_w = isset($product_image_dimention['width']) ? $product_image_dimention['width'] : ''; // Get product image width
        $product_image_h = isset($product_image_dimention['height']) ? $product_image_dimention['height'] : ''; // Get product image height
        // If action is set for preview
        if (!empty($_GET['woo_vou_pdf_action']) && $_GET['woo_vou_pdf_action'] == 'preview') {

            // Product Image
            $productimage_url = $woo_vou_img_path . '/placeholder.png';
            $productimage_html = '<img src="' . esc_url($productimage_url) . '" alt="" width="' . $product_image_w . '" height=' . $product_image_h . ' />';
        } else {

            // If relative path option is set
            if (!empty($vou_relative_path_option) && $vou_relative_path_option == 'yes') {
                $prd_img_file = '';
                if( !empty( $pdf_args['vou_variation_id'] ) ){
                    $prd_img_file = get_attached_file(get_post_meta($pdf_args['vou_variation_id'], '_thumbnail_id', true));    
                } 

                if( empty( $prd_img_file ) ){
                    $prd_img_file = get_attached_file(get_post_meta($pdf_args['vou_product_id'], '_thumbnail_id', true));
                }

                $productimage_html = '<img src="' . esc_url($prd_img_file) . '" alt="" width="' . $product_image_w . '" height=' . $product_image_h . ' />'; // Get relative path and append in image tag
            } else {
                $productimage = '';

                if( !empty( $pdf_args['vou_variation_id'] ) ){
                    $productimage = get_the_post_thumbnail_url($pdf_args['vou_variation_id'], apply_filters('woo_vou_productimage_shortcode', 'full'));
                } 
                if( empty( $productimage )){
                    // Product featured image
                    $productimage = get_the_post_thumbnail_url($pdf_args['vou_product_id'], apply_filters('woo_vou_productimage_shortcode', 'full'));
                }
                $productimage_html = '<img src="' . esc_url($productimage) . '" alt="" width="' . $product_image_w . '" height=' . $product_image_h . ' />';
            }
        }

        $html = str_replace('{productimage}', $productimage_html, $html); // Replace html for image
    }

    // output the HTML content
    $pdf->writeHTML($html, true, 0, true, 0);

    // reset pointer to the last page
    $pdf->lastPage();

    // ---------------------------------------------------------
    $order_pdf_name = get_option('order_pdf_name');
    $woo_vou_pdf_action = !empty($_GET['woo_vou_pdf_action']) ? $_GET['woo_vou_pdf_action'] : '';
    // Apply filter to allow 3rd party people to change it
	$date_format = apply_filters( 'woo_vou_voucher_date_format', 'Y-m-d' );

    if (!empty($order_pdf_name) && !empty($pdf_args['vou_product_id']) 
     	&& $woo_vou_pdf_action != 'preview') {

        $product_title = get_the_title($pdf_args['vou_product_id']);
        $product_title = str_replace(array( " ", "/" ), array( "-", "" ), $product_title);

        // Find and replace shortcodes
        $find		   = array( "{current_date}", "{product_title}" );
        $replace       = array( date($date_format), $product_title );
        $pdf_file_name = str_replace( $find, $replace, $order_pdf_name );
    } else {

    	// Find and replace shortcodes
        $find		   = array( "{current_date}", "{product_title}" );
        $replace       = array( date($date_format), esc_html__('Test Product', 'woovoucher') );
        $pdf_file_name = str_replace( $find, $replace, $order_pdf_name );
    }

    //Get pdf name
    $pdf_name = isset($pdf_args['pdf_name']) && !empty($pdf_args['pdf_name']) ? $pdf_args['pdf_name'] : $pdf_file_name;

    // clean output just before generate voucher
    if (ob_get_contents() || ob_get_length())
        ob_end_clean();

    /*
     * If it is not voucher template preview,
     * And PDF password enable, PDF password pattern is set.
     **/
    if ( !empty($vou_enable_pdf_password) && $vou_enable_pdf_password == 'yes'
        && !empty($vou_pdf_password_pattern) && $woo_vou_pdf_action != 'preview'
        && !isset($_GET['woo_vou_admin'])
        && ( !array_key_exists( 'is_preview', $_POST ) 
            || ( array_key_exists( 'is_preview', $_POST ) && $_POST['is_preview'] != 'true' ) ) ){

        $pdf_permissions = array( 'modify', 'copy' );
        $vou_order_id   = $pdf_args['vou_order_id'];

        $vou_order      = new WC_Order( $vou_order_id ); // Get order
        $user_id        = $vou_order->user_id; // Get userid
        $user           = get_user_by('ID', $user_id); // User data
        $first_name     = !empty($vou_order->get_billing_first_name()) ? $vou_order->get_billing_first_name() : $user->first_name; // Get user first name
        $last_name      = !empty($vou_order->get_billing_last_name()) ? $vou_order->get_billing_last_name() : $user->last_name; // Get user last name
        $buyer_email    = !empty($vou_order->get_billing_email()) ? $vou_order->get_billing_email() : $user->user_email; // Get user email

        $ordered_date   = strtotime($vou_order->get_date_completed()); // Get order completed date
        $vou_order_date = date( 'Y-m-d', $ordered_date ); // Change Date format

        // Find and replace shortcodes
        $find           = array( "{order_id}", "{order_date}", "{first_name}", "{last_name}", "{buyer_email}" );
        $replace        = array( $vou_order_id, $vou_order_date, $first_name, $last_name, $buyer_email );
        $pdf_password   = str_replace( $find, $replace, $vou_pdf_password_pattern );

        // Set PDF Protection
        $pdf->SetProtection( $pdf_permissions, "PasswordForUsers", $pdf_password, 0 );
    }

    // Store pdf in a folder
    if( !empty( $_POST ) && array_key_exists( 'is_preview', $_POST ) ) {
        
        
        $pdf_enable_preview_option = get_option('vou_enable_voucher_preview_open_option');
        $pdf_enable_preview_option = empty( $pdf_enable_preview_option ) ? 'popup':$pdf_enable_preview_option;

    	$random_number	= mt_rand( 10000000, 99999999);
    	$vou_pdf_path 	= WOO_VOU_PREVIEW_UPLOAD_DIR . $pdf_name . '-preview-' . $random_number; // Voucher pdf path
        $vou_pdf_name 	= $vou_pdf_path;
        $vou_pdf_url 	= WOO_VOU_PREVIEW_UPLOAD_URL . $pdf_name . '-preview-'.$random_number.'.pdf';

        if( $pdf_enable_preview_option == 'newtab' ){
            
            $pdf->Output($vou_pdf_name . '.pdf', 'I');
        } 
        else {
            $pdf->Output($vou_pdf_name . '.pdf', 'F');
            

            $result['pdf_name'] 	= $pdf_name . '-preview-' . $random_number . '.pdf';
            $result['pdf_preview'] 	= "<iframe class='woo-vou-preview-pdf-iframe' src='".esc_url($vou_pdf_url)."' width='100%' height='100%'></iframe>";

            echo json_encode( $result );
        }
        exit;
    } else if ($pdf_save) {
        $pdf->Output($pdf_name . '.pdf', 'F');
    } else if (!empty($pdf_enable_preview) && $pdf_enable_preview == 'yes') {
        //open pdf in browser
        $pdf->Output($pdf_name . '.pdf');
        exit;
    } else {
        //Close and output PDF document
        //Second Parameter I that means display direct and D that means ask product or open this file
        $pdf->Output($pdf_name . '.pdf', 'D');
    }
}

/**
 * View Preview for Voucher PDF
 * 
 * Handles to view preview for voucher pdf
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_preview_pdf() {
    
    global $woo_vou_model, $pdf_voucodes;

    $prefix = WOO_VOU_META_PREFIX;

    if (empty($pdf_voucodes)) {
        $pdf_voucodes = '[The voucher code will be inserted automatically here]';
    }

    $model = $woo_vou_model;

    $pdf_args = array();

    if (isset($_GET['voucher_id']) && !empty($_GET['voucher_id']) && isset($_GET['woo_vou_pdf_action']) && $_GET['woo_vou_pdf_action'] == 'preview') {

        // Getting voucher character support
        $voucher_char_support = get_option('vou_char_support');

        $voucher_template_id = $_GET['voucher_id'];

        $pdf_args['vou_template_id'] = $voucher_template_id;

        $codes = esc_html__('[The voucher code will be inserted automatically here]', 'woovoucher');

        // Voucher template style
        $voucher_tempelate_style = '.woo_vou_textblock {
	text-align: justify;
}
.woo_vou_messagebox {
	text-align: justify;
}
.one_third {
	width: 33%;
}';

    	//Voucher PDF Custom Style
	    $vou_template_custom_css = get_post_meta($voucher_template_id, $prefix . 'pdf_custom_css', true);
	    if (!empty($vou_template_custom_css)) {
	        $voucher_tempelate_style .= $vou_template_custom_css;
	    }

        // Filter to change style
        $voucher_tempelate_style = apply_filters('woo_vou_pdf_template_styles', $voucher_tempelate_style, $voucher_template_id);

        $voucher_template_html = '<html>
									<head>
										<style>' . $voucher_tempelate_style . '</style>
									</head>
									<body>';
        $content_post = get_post($voucher_template_id);
        $content = isset($content_post->post_content) ? $content_post->post_content : '';

        $content = apply_filters('woo_vou_voucher_template_content', $content, $voucher_template_id);
        $post_title = isset($content_post->post_title) ? $content_post->post_title : '';
        $voucher_template_html .= do_shortcode($content);

        // add filter to modify generated preview pdf voucher HTML OR to replace shortcodes with values
        $voucher_template_html = apply_filters('woo_vou_pdf_template_preview_html', $voucher_template_html, $voucher_template_id);
        $voucher_template_html .= '</body>
							</html>';       

        //Set pdf name
        $post_title = str_replace(' ', '-', strtolower($post_title));
        $pdf_args['pdf_name'] = $post_title . esc_html__('-preview-', 'woovoucher') . $voucher_template_id;
        $pdf_args['vou_codes'] = $codes;
        $pdf_args['char_support'] = (!empty($voucher_char_support) && $voucher_char_support == 'yes' ) ? 1 : 0; // Character support

        woo_vou_generate_pdf_by_html($voucher_template_html, $pdf_args);
    }
}

add_action('init', 'woo_vou_preview_pdf', 9);

/**
 * Generate PDF for Voucher
 * 
 * Handles to Generate PDF on run time when 
 * user will execute the url which is sent to
 * user email with purchase receipt
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_process_product_pdf($productid, $orderid, $item_id = '', $orderdvoucodes = array(), $pdf_args = array()) {

    $prefix = WOO_VOU_META_PREFIX;

    global $current_user, $woo_vou_model, $orderID, $itemID;
    $orderID = $orderid;
    $itemID = $item_id;

    //model class
    $model = $woo_vou_model;

    $woo_vou_details = array();

    $vou_relative_path_option = get_option('vou_enable_relative_path');

    // If pdf argument is not array then make it array
    $pdf_args = !is_array($pdf_args) ? (array) $pdf_args : $pdf_args;

    // Taking voucher key
    if (!empty($pdf_args['pdf_vou_key'])) {
        $pdf_vou_key = $pdf_args['pdf_vou_key'];
    } else {
        $pdf_vou_key = isset($_GET['key']) ? $_GET['key'] : '';
    }

    if (!empty($productid) && !empty($orderid)) { // Check product id & order id are not empty
        //get all voucher details from order meta
        $allorderdata = $model->woo_vou_get_all_ordered_data($orderid);

        // Creating order object for order details
        $woo_order = wc_get_order($orderid);
        $items = $woo_order->get_items();

        // Getting product name
        $woo_product_details = $model->woo_vou_get_product_details($orderid, $items);

        // get product		
        $product = wc_get_product($productid);

        // if product type is variable then $productid will contain variation id
        $variation_id = $productid;
        // check if product type is variable then we need to take parent id of variation id
        if ($product->is_type('variation') || $product->is_type('variable')) {

            // productid is variation id in case of variable product so we need to take actual product id					
            $woo_variation = new WC_Product_Variation($productid);
            
        	$productid = $model->woo_vou_get_item_productid_from_product($woo_variation);
        }

        $productname = isset($woo_product_details[$variation_id]) ? $woo_product_details[$variation_id]['product_name'] : '';

        $_product = wc_get_product( $productid );
        // To make compatible with previous versions of 3.0.0
        if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1) {
            //get product full information;
            $productfulldesc = isset($_product->post->post_content) ? $_product->post->post_content : '';
        } else {
            $productfulldesc = $_product->get_description();
        }
        $productfulldesc =  apply_filters('the_content', $productfulldesc );

        //Added in version 2.3.7 for sold indivdual bug
        $item_key = $item_id;

        if (empty($item_key)) {
            $item_key = isset($woo_product_details[$variation_id]) ? $woo_product_details[$variation_id]['item_key'] : '';
        }

        $variation_data = $model->woo_vou_get_variation_data($woo_order, $item_key);
        if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1) {
            $producttitle = $productname;
            $productname = $productname . $variation_data;
        } else {
            $producttitle = $product->get_title();
        }

        $voucherprice = $model->woo_vou_get_product_price($orderid, $item_id, $items[$item_id]); // Get Voucher Price from function

        $productprice = wc_price($voucherprice); // Get wc_price

        //Check if variable product fixed bug in 2.6.0
        if ($product->is_type('variation') || $product->is_type('variable')) {
            $temp_pro_data = new WC_Product_Variation($variation_id);
        } else {
            $temp_pro_data = new WC_Product($variation_id);
        }

        $regularprice_without_format = woo_vou_get_pro_regular_price($temp_pro_data);
        $saleprice_without_format = woo_vou_get_pro_sale_price($temp_pro_data);
        $regularprice = wc_price(woo_vou_get_pro_regular_price($temp_pro_data));
        $saleprice = wc_price(woo_vou_get_pro_sale_price($temp_pro_data));
        // calculate discount price
        if (isset($saleprice_without_format) && !empty($saleprice_without_format) && !empty($woo_product_details[$variation_id]['product_price'])) {

            $discountprice = $regularprice_without_format - $saleprice_without_format;
            $discountprice = wc_price($discountprice);
        } else {
            $discountprice = wc_price(0);
        }

        //Get voucher codes
        $voucher_codes = wc_get_order_item_meta($item_id, $prefix . 'codes');

        //get all voucher details from order meta

        $allvoucherdata = apply_filters( 'woo_vou_order_voucher_metadata', isset( $allorderdata[$productid] ) ? $allorderdata[$productid] : array(), $orderid, $item_id, $productid );

        //how to use the voucher details
        $howtouse = isset($allvoucherdata['redeem']) ? $allvoucherdata['redeem'] : '';

        //start date
        //$start_date = isset($allvoucherdata['start_date']) ? $allvoucherdata['start_date'] : '';
		if(isset($allvoucherdata['start_date']) && is_array($allvoucherdata['start_date'])){
            $exp_date = $allvoucherdata['start_date'][$variation_id];
        } elseif (isset($allvoucherdata['start_date'])){
            $exp_date = $allvoucherdata['start_date'];
        }
        //expiry data
        //$exp_date = isset($allvoucherdata['exp_date']) ? $allvoucherdata['exp_date'] : '';
		if(isset($allvoucherdata['exp_date']) && is_array($allvoucherdata['exp_date'])){
            $exp_date = $allvoucherdata['exp_date'][$variation_id];
        } elseif (isset($allvoucherdata['exp_date'])){
            $exp_date = $allvoucherdata['exp_date'];
        }
	
        //vou order date
        $orderdate = get_the_time('Y-m-d', $orderid);
        if (!empty($orderdate)) {
            $orderdate = $model->woo_vou_get_date_format($orderdate);
        }

        //vou logo
        $voulogo = isset($allvoucherdata['vendor_logo']) ? $allvoucherdata['vendor_logo'] : '';

        //vendor logo
        $voulogohtml = '';

        if (!empty($voulogo['src'])) {

	        // If relative path setting is enabled
	        if (!empty($vou_relative_path_option) && $vou_relative_path_option == 'yes') {
	
	            $vou_site_attachment_id = $model->woo_vou_get_attachment_id_from_url($voulogo['src']); // Get attachment id
	            $image_size = @getimagesize($voulogo['src']);          // Get Image size from URL
	            $voulogo = get_attached_file($vou_site_attachment_id);      // Get relative path
	        } else {
	
	            $voulogo = isset($voulogo['src']) && !empty($voulogo['src']) ? $voulogo['src'] : '';
	        }

            $voulogohtml .= '<img src="' . esc_url($voulogo) . '" alt=""';

            // If image_size is set and not empty then append height width attributes to image tag
            if (isset($image_size) && !empty($image_size)) {
                $voulogohtml .= ' width="' . $image_size[0] . '" height=' . $image_size[1];
            }
            $voulogohtml .= '/>';
        }

        //vendor email
        $woo_vou_pro_primary_vendor_email = '';

        $woo_vou_pro_primary_vendor_id = get_post_meta($productid, $prefix . 'vendor_user', true);

        if (!empty($woo_vou_pro_primary_vendor_id)) {

            //get user data
            $woo_vou_pro_primary_vendor = get_userdata($woo_vou_pro_primary_vendor_id);

            if (!empty($woo_vou_pro_primary_vendor)) {

                $woo_vou_pro_primary_vendor_email = $woo_vou_pro_primary_vendor->data->user_email;
            }
        }

        //site logo 
        $vousitelogohtml = '';
        $vou_site_url = get_option('vou_site_logo');
        if (!empty($vou_site_url)) {
            if (!empty($vou_relative_path_option) && $vou_relative_path_option == 'yes') {

                $vou_site_attachment_id = $model->woo_vou_get_attachment_id_from_url($vou_site_url); // Get attachment id
                $image_size = @getimagesize($vou_site_url);        // Get Image size from URL
                $voulogo = get_attached_file($vou_site_attachment_id);     // Get relative path
                // create HTML
                $vousitelogohtml .= '<img src="' . esc_url(get_attached_file($vou_site_attachment_id)) . '" alt=""';

                // If image_size is set and not empty then append height width attributes to image tag
                if (isset($image_size) && !empty($image_size)) {
                    $vousitelogohtml .= ' width="' . $image_size[0] . '" height=' . $image_size[1];
                }
                $vousitelogohtml .= ' />';
            } else {
				do_action('woo_vou_pdf_pdf_before_site_logo');
                $vousitelogohtml = '<img src="' . esc_url($vou_site_url) . '" alt="" />';
            }
        }

        //start date
        if (!empty($start_date)) {
            $start_date_time = $model->woo_vou_get_date_format($start_date, true);
            $start_date = $model->woo_vou_get_date_format($start_date);
        } else {
            $start_date = $start_date_time = esc_html__('No Start Date', 'woovoucher');
        }

        //expiration date
        if (!empty($exp_date)) {
            $expiry_date = $model->woo_vou_get_date_format($exp_date);
            $expiry_date_time = $model->woo_vou_get_date_format($exp_date, true);
        } else {
            $expiry_date = $expiry_date_time = esc_html__('Never Expire', 'woovoucher');
        }

        //website url
        $website = isset($allvoucherdata['website_url']) ? $allvoucherdata['website_url'] : '';

        //vendor address
        if(isset($allvoucherdata['vendor_address']) && is_array($allvoucherdata['vendor_address'])){
            $vendor_address_data = $allvoucherdata['vendor_address'][$variation_id];
        } elseif (isset($allvoucherdata['vendor_address'])){
            $vendor_address_data = $allvoucherdata['vendor_address'];
        }
        $addressphone = isset($vendor_address_data) ? $vendor_address_data : '';

        //location where voucher is availble
        $locations = isset($allvoucherdata['avail_locations']) ? $allvoucherdata['avail_locations'] : '';

        // PDF template
        if(isset($allvoucherdata['pdf_template']) && is_array($allvoucherdata['pdf_template'])){
            $pdf_template_id = $allvoucherdata['pdf_template'][$variation_id];
        } elseif (isset($allvoucherdata['pdf_template'])){
            $pdf_template_id = $allvoucherdata['pdf_template'];
        }
        $pdf_template_meta = !empty($pdf_template_id) ? $pdf_template_id : '';
        
        //vendor user
        $vendor_user = get_post_meta($productid, $prefix . 'vendor_user', true);

        $vendor_shopname = apply_filters( 'woo_vou_shopname_pdf_process', '', $vendor_user );
            
        //get vendor detail
        $vendor_detail = $model->woo_vou_get_vendor_detail($productid, $variation_id, $vendor_user);

        //PDF Selection Data
        if (isset($items[$item_key]['woo_vou_pdf_template_selection'])) {
            $pdf_selection_data = maybe_unserialize($items[$item_key]['woo_vou_pdf_template_selection']);
        }

        if (isset($pdf_selection_data['value']) && !empty($pdf_selection_data['value'])) {
            $pdf_template_meta = $pdf_selection_data['value'];
        } 

        $variationname = $variationdesc = '';
        // check if product type is variable then we need to check all pdf voucher options
        if ($product->is_type('variation') || $product->is_type('variable')) {
            
            //sku
            $sku = get_post_meta($variation_id, '_sku', true);
            $variationname = wc_get_formatted_variation( $woo_variation, true, true ); //variation name
            $variationdesc = $woo_variation->get_description(); // Variation description

            if (empty($sku)) {
                //sku
                $sku = get_post_meta($productid, '_sku', true);
            }
        } else {


            //sku
            $sku = get_post_meta($productid, '_sku', true);
        }
        

        $voucodes = '';

        //Get mutiple pdf option from order meta
        $multiple_pdf = empty($orderid) ? '' : get_post_meta($orderid, $prefix . 'multiple_pdf', true);        
        if( is_array( $multiple_pdf ) ) {
            $multiple_pdf = !empty( $multiple_pdf[$productid] ) ? $multiple_pdf[$productid] : '';
        }

        if ($multiple_pdf == 'yes' && !empty($orderdvoucodes)) { //check is enable multiple pdf
            $key = $pdf_vou_key;
            $voucodes = $orderdvoucodes[$key];
        } elseif (!empty($voucher_codes)) {

            $voucodes = trim($voucher_codes);
        }
		$productid = apply_filters('woo_vou_voucher_desc_product_id',$productid,$orderid);
        $productshortdesc = apply_filters('the_excerpt', get_post_field('post_excerpt', $productid));
        include( WOO_VOU_DIR . '/includes/woo-vou-generate-order-pdf.php' );
    }
}

/**
 * Generate PDF for Voucher
 * 
 * Handles to Generate PDF on run time when 
 * user will execute the url which is sent to
 * user email with purchase receipt
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.5.4
 */
function woo_vou_process_product_pdf_preview( $post_arr ) {

    $prefix = WOO_VOU_META_PREFIX;

    global $current_user, $woo_vou_model;

    $product_id = $post_arr['product_id'];
    $productid 	= array_key_exists('variation_id', $post_arr) && !empty( $post_arr['variation_id'] ) ? $post_arr['variation_id'] : $post_arr['product_id'];

    $woo_vou_details = array();

    $vou_relative_path_option = get_option('vou_enable_relative_path');

    if ( !empty( $product_id ) ) { // Check product id & order id are not empty

    	//get all voucher details from order meta
        $allorderdata = $woo_vou_model->woo_vou_get_all_product_data($product_id, $productid, $post_arr);

        // get product (it will be vaiation product incase variation id is available)	
        $product = wc_get_product( $productid );

        // get product
        $_product = wc_get_product( $product_id );

        // To make compatible with previous versions of 3.0.0
        if ( version_compare( WOOCOMMERCE_VERSION, "3.0.0" ) == -1 ) {
            //get product full information;
            $productfulldesc = isset($_product->post->post_content) ? $_product->post->post_content : '';
        } else {
            $productfulldesc = $_product->get_description();
        }
        $productfulldesc =  apply_filters('the_content', $productfulldesc );

        // if product type is variable then $productid will contain variation id
        $variation_id = !empty( $post_arr['variation_id']) ? $post_arr['variation_id'] : '';
        
        // check if product type is variable then we need to take parent id of variation id
        if ($product->is_type('variation') || $product->is_type('variable')) {

            // productid is variation id in case of variable product so we need to take actual product id					
            $woo_variation = wc_get_product($productid);
        }

        $productname = $product->get_name();

        if (version_compare(WOOCOMMERCE_VERSION, "3.0.0") == -1) {
            $producttitle = $productname;
            $productname = $productname . $variation_data;
        } else {
            $producttitle = $product->get_title();
        }

        $voucherprice = woo_vou_get_voucher_price(null, $productid, array('is_preview' => true)); // Get Voucher Price from function

        $voucherprice = apply_filters( 'woo_vou_pdf_preview_price', $voucherprice, $post_arr );

        $productprice = wc_price($voucherprice); // Get wc_price
        //Check if variable product fixed bug in 2.6.0
        if ($product->is_type('variation') || $product->is_type('variable')) {
            $temp_pro_data = wc_get_product($productid);
        } else {
            $temp_pro_data = wc_get_product($product_id);
        }

        $regularprice_without_format 	= woo_vou_get_pro_regular_price($temp_pro_data);
        $saleprice_without_format 		= woo_vou_get_pro_sale_price($temp_pro_data);

        $regularprice 	= wc_price(woo_vou_get_pro_regular_price($temp_pro_data));
        $saleprice 		= wc_price(woo_vou_get_pro_sale_price($temp_pro_data));
        // calculate discount price
        if (isset($saleprice_without_format) && !empty($saleprice_without_format)) {

            $discountprice = $regularprice_without_format - $saleprice_without_format;
            $discountprice = wc_price($discountprice);
        } else {
            $discountprice = wc_price(0);
        }

        //Get voucher codes
        $voucher_codes = implode(', ', array_fill(0, $_POST['quantity'], 'XXX-XXX-XXXXX') );

        //get all voucher details from order meta
        $allvoucherdata = $allorderdata;

        //how to use the voucher details
        $howtouse = isset($allvoucherdata['redeem']) ? $allvoucherdata['redeem'] : '';

        //start date
       // $start_date = isset($allvoucherdata['start_date']) ? $allvoucherdata['start_date'] : '';
		if(isset($allvoucherdata['start_date']) && is_array($allvoucherdata['start_date'])){
            $exp_date = $allvoucherdata['start_date'][$variation_id];
        } elseif (isset($allvoucherdata['start_date'])){
            $exp_date = $allvoucherdata['start_date'];
        }
        //expiry data
        $exp_date = isset($allvoucherdata['exp_date']) ? $allvoucherdata['exp_date'] : '';
		if(isset($allvoucherdata['exp_date']) && is_array($allvoucherdata['exp_date'])){
            $exp_date = $allvoucherdata['exp_date'][$variation_id];
        } elseif (isset($allvoucherdata['exp_date'])){
            $exp_date = $allvoucherdata['exp_date'];
        }

        //vou order date
        $orderdate = get_the_time('Y-m-d', strtotime('now'));

        //vou logo
        $voulogo = isset($allvoucherdata['vendor_logo']) ? $allvoucherdata['vendor_logo'] : '';

        //vendor logo
        $voulogohtml = '';

        if (!empty($voulogo['src'])) {

	        // If relative path setting is enabled
	        if (!empty($vou_relative_path_option) && $vou_relative_path_option == 'yes') {

	            $vou_site_attachment_id = $woo_vou_model->woo_vou_get_attachment_id_from_url($voulogo['src']); // Get attachment id
	            $image_size = @getimagesize($voulogo['src']);          // Get Image size from URL
	            $voulogo = get_attached_file($vou_site_attachment_id);      // Get relative path
	        } else {
	
	            $voulogo = isset($voulogo['src']) && !empty($voulogo['src']) ? $voulogo['src'] : '';
	        }

            $voulogohtml .= '<img src="' . esc_url($voulogo) . '" alt=""';

            // If image_size is set and not empty then append height width attributes to image tag
            if (isset($image_size) && !empty($image_size)) {
                $voulogohtml .= ' width="' . $image_size[0] . '" height=' . $image_size[1];
            }
            $voulogohtml .= '/>';
        }

        //vendor email
        $woo_vou_pro_primary_vendor_email = '';

        $woo_vou_pro_primary_vendor_id = get_post_meta($product_id, $prefix . 'vendor_user', true);

        if (!empty($woo_vou_pro_primary_vendor_id)) {

            //get user data
            $woo_vou_pro_primary_vendor = get_userdata($woo_vou_pro_primary_vendor_id);

            if (!empty($woo_vou_pro_primary_vendor)) {

                $woo_vou_pro_primary_vendor_email = $woo_vou_pro_primary_vendor->data->user_email;
            }
        }

        //site logo 
        $vousitelogohtml = '';
        $vou_site_url = get_option('vou_site_logo');
        if (!empty($vou_site_url)) {
            if (!empty($vou_relative_path_option) && $vou_relative_path_option == 'yes') {

                $vou_site_attachment_id = $woo_vou_model->woo_vou_get_attachment_id_from_url($vou_site_url); // Get attachment id
                $image_size = @getimagesize($vou_site_url);        // Get Image size from URL
                $voulogo = get_attached_file($vou_site_attachment_id);     // Get relative path
                // create HTML
                $vousitelogohtml .= '<img src="' . esc_url(get_attached_file($vou_site_attachment_id)) . '" alt=""';

                // If image_size is set and not empty then append height width attributes to image tag
                if (isset($image_size) && !empty($image_size)) {
                    $vousitelogohtml .= ' width="' . $image_size[0] . '" height=' . $image_size[1];
                }
                $vousitelogohtml .= ' />';
            } else {

                $vousitelogohtml = '<img src="' . esc_url($vou_site_url) . '" alt="" />';
            }
        }

        //start date
        if (!empty($start_date)) {
            $start_date_time = $woo_vou_model->woo_vou_get_date_format($start_date, true);
            $start_date = $woo_vou_model->woo_vou_get_date_format($start_date);
        } else {
            $start_date = $start_date_time = esc_html__('No Start Date', 'woovoucher');
        }

        //expiration date
        if (!empty($exp_date)) {
            $expiry_date = $woo_vou_model->woo_vou_get_date_format($exp_date);
            $expiry_date_time = $woo_vou_model->woo_vou_get_date_format($exp_date, true);
        } else {
            $expiry_date = $expiry_date_time = esc_html__('No Expiration', 'woovoucher');
        }

        //website url
        $website = isset($allvoucherdata['website_url']) ? $allvoucherdata['website_url'] : '';

        //vendor address
        $vendor_address_data = $allvoucherdata['vendor_address'];

        $addressphone = isset($vendor_address_data) ? $vendor_address_data : '';

        //location where voucher is availble
        $locations = isset($allvoucherdata['avail_locations']) ? $allvoucherdata['avail_locations'] : '';

        // PDF template
        $pdf_template_id = $allvoucherdata['pdf_template'];
        $pdf_template_meta = !empty($pdf_template_id) ? $pdf_template_id : '';
        
        //vendor user
        $vendor_user = get_post_meta($productid, $prefix . 'vendor_user', true);

        $vendor_shopname = apply_filters( 'woo_vou_shopname_pdf_preview', '', $vendor_user );
            
        //get vendor detail
        $vendor_detail = $woo_vou_model->woo_vou_get_vendor_detail($productid, $variation_id, $vendor_user);

        //PDF Selection Data
        if (array_key_exists( '_woo_vou_pdf_template_selection', $_POST ) && !empty( $_POST['_woo_vou_pdf_template_selection'][$productid] )) {
            $pdf_template_meta = $_POST['_woo_vou_pdf_template_selection'][$productid];
        }

        $variationname = $variationdesc = '';
        // check if product type is variable then we need to check all pdf voucher options
        if ($product->is_type('variation') || $product->is_type('variable')) {
            
            //pdf template
            $pdf_template_meta = !empty($pdf_template_meta) ? $pdf_template_meta : get_post_meta($variation_id, $prefix . 'pdf_template', true) ;

            //sku
            $sku = get_post_meta($productid, '_sku', true);
            $variationname = wc_get_formatted_variation( $woo_variation, true, true ); //variation name
            $variationdesc = $woo_variation->get_description(); // Variation description

            if (empty($sku)) {
                //sku
                $sku = get_post_meta($product_id, '_sku', true);
            }
        } else {

            //pdf template
            $pdf_template_meta = !empty($pdf_template_meta) ? $pdf_template_meta : $vendor_detail['pdf_template'];

            //sku
            $sku = get_post_meta($product_id, '_sku', true);
        }

        $productshortdesc = apply_filters('the_excerpt', get_post_field('post_excerpt', $post_arr['product_id']));

        $voucodes = '';

        // Get mutiple pdf option from order meta
        $multiple_pdf = empty($orderid) ? '' : get_post_meta($orderid, $prefix . 'multiple_pdf', true);
        if( is_array( $multiple_pdf ) ) {
            $multiple_pdf = !empty( $multiple_pdf[$product_id] ) ? $multiple_pdf[$product_id] : '';
        }
        
        if ($multiple_pdf == 'yes' && !empty($orderdvoucodes)) { //check is enable multiple pdf
            $key = $pdf_vou_key;
            $voucodes = $orderdvoucodes[$key];
        } elseif (!empty($voucher_codes)) {

            $voucodes = trim($voucher_codes);
        }

        $date_format = get_option('date_format');
        $woo_vou_details['recipientname']       = !empty( $_POST[$prefix.'recipient_name'][$productid] ) ? $woo_vou_model->woo_vou_escape_attr($_POST[$prefix.'recipient_name'][$productid])    : '';
		$woo_vou_details['recipientemail']		= !empty( $_POST[$prefix.'recipient_email'][$productid] ) ? $_POST[$prefix.'recipient_email'][$productid] 	: '';
		$woo_vou_details['recipientmessage']  = !empty( $_POST[$prefix.'recipient_message'][$productid] ) ? $woo_vou_model->woo_vou_escape_attr($_POST[$prefix.'recipient_message'][$productid])  : '';
        $woo_vou_details['recipientphone']  = !empty( $_POST[$prefix.'recipient_phone'][$productid] ) ? $woo_vou_model->woo_vou_escape_attr($_POST[$prefix.'recipient_phone'][$productid])  : '';
		$woo_vou_details['recipientgiftdate']   = !empty( $_POST[$prefix.'recipient_giftdate'][$productid] ) ? date( $date_format, strtotime( $_POST[$prefix.'recipient_giftdate'][$productid] ) )  : '';

		// Set to main product_id for adding compatibility with existing code
		$productid = $product_id;

        include( WOO_VOU_DIR . '/includes/woo-vou-generate-order-pdf.php' );
    }
}
