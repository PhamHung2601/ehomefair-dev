<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Barcode HTML
 * 
 * Handles to get barcode html
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.4.6
 */
function woo_vou_barcode_html($voucher_template_id, $pdf_vou_codes, $barcode_args = array()) {

    $prefix = WOO_VOU_META_PREFIX;

    $pdf_vou_codes = !empty($pdf_vou_codes) ? $pdf_vou_codes : '';
    $pdf_args_vou_codes = !empty($pdf_vou_codes) ? explode(',', $pdf_vou_codes) : array();

    //Get pdf size meta
    $woo_vou_template_size = get_post_meta($voucher_template_id, $prefix . 'pdf_size', true);
    $woo_vou_template_size = !empty($woo_vou_template_size) ? $woo_vou_template_size : 'A4';

    $font_size = isset($woo_vou_size_array['fontsize']) ? $woo_vou_size_array['fontsize'] : '12';

    //Get Barcode Dimantion
    $barcode_dimention = apply_filters('woo_vou_barcode_dimention', array(
        'width' => !empty($barcode_args['barcode_width']) ? $barcode_args['barcode_width'] : round($font_size * 1.5) * 5,
        'height' => !empty($barcode_args['barcode_height']) ? $barcode_args['barcode_height'] : round($font_size * 1.5)
            ), $font_size, $woo_vou_template_size);

    $barcode_code_w = isset($barcode_dimention['width']) ? $barcode_dimention['width'] : '';
    $barcode_code_h = isset($barcode_dimention['height']) ? $barcode_dimention['height'] : '';
    $barcode_code_c = isset($barcode_args['barcode_color']) ? $barcode_args['barcode_color'] : '#000000';
    $barcode_code_a = isset($barcode_args['barcode_disp_type']) ? $barcode_args['barcode_disp_type'] : 'horizontal';
    $barcode_code_t = !empty($barcode_args['barcode_type']) && $barcode_args['barcode_type'] != 'undefined' ? $barcode_args['barcode_type'] : 'C128';
    $barcode_code_b = !empty($barcode_args['barcode_border']) ? true : false;

    $html = !empty($barcode_args['content']) ? $barcode_args['content'] : '{barcode}';

    if (!class_exists('WPWEB_TCPDF')) { //If class not exist
        //include tcpdf file
        require_once WOO_VOU_DIR . '/includes/tcpdf/tcpdf.php';
    }

    // pdf object for barcode
    $pdf = new WPWEB_TCPDF(WPWEB_PDF_PAGE_ORIENTATION, WPWEB_PDF_UNIT, WPWEB_PDF_PAGE_FORMAT, true, 'UTF-8', false);

    if (!empty($pdf_vou_codes) && strpos($html, '{barcode}') !== false) {// If barcode is there
        $vou_bar_msg = $vou_barcode = '';

        $vou_bar_msg = trim($pdf_vou_codes);

        // make barcode url used at scanning time
        $vou_bar_msg = $vou_bar_msg;

        $vou_bar_params = $pdf->serializeTCPDFtagParameters(array($vou_bar_msg, $barcode_code_t, '', '', $barcode_code_w, $barcode_code_h, 0.2, array('position' => 'S', 'border' => $barcode_code_b, 'padding' => 'auto', 'fgcolor' => woo_vou_hex_to_rgb($barcode_code_c), 'text' => false, 'font' => 'helvetica', 'fontsize' => 100, 'stretchtext' => 10), 'N'));

        $vou_barcode .= '<tcpdf method="write1DBarcode" params="' . $vou_bar_params . '" />';

        $html = str_replace('{barcode}', $vou_barcode, $html);
    }

    if (!empty($pdf_vou_codes) && strpos($html, '{barcodes}') !== false) {// If barcodes is there
        $vou_bar_msg = $vou_barcode = '';

        $vou_barcode .= '<table>';

        if ($barcode_code_a == 'vertical') {

            foreach ($pdf_args_vou_codes as $pdf_args_vou_code) {

                $vou_barcode .= '<tr><td>';

                $vou_bar_msg = trim($pdf_args_vou_code);

                // make barcode url used at scanning time
                $vou_bar_msg = $vou_bar_msg;

                $vou_bar_params = $pdf->serializeTCPDFtagParameters(array($vou_bar_msg, $barcode_code_t, '', '', $barcode_code_w, $barcode_code_h, 0.2, array('position' => 'S', 'border' => $barcode_code_b, 'padding' => 'auto', 'fgcolor' => woo_vou_hex_to_rgb($barcode_code_c), 'text' => false, 'font' => 'helvetica', 'fontsize' => 100, 'stretchtext' => 10), 'N'));
                $vou_barcode .= '<tcpdf method="write1DBarcode" params="' . $vou_bar_params . '" />';

                $vou_barcode .= '</td></tr>';
            }
        } else {

            $vou_barcode .= '<tr>';

            foreach ($pdf_args_vou_codes as $pdf_args_vou_code) {

                $vou_barcode .= '<td>';

                $vou_bar_msg = trim($pdf_args_vou_code);

                // make barcode url used at scanning time
                $vou_bar_msg = $vou_bar_msg;

                $vou_bar_params = $pdf->serializeTCPDFtagParameters(array($vou_bar_msg, $barcode_code_t, '', '', $barcode_code_w, $barcode_code_h, 0.2, array('position' => 'S', 'border' => $barcode_code_b, 'padding' => 'auto', 'fgcolor' => woo_vou_hex_to_rgb($barcode_code_c), 'text' => false, 'font' => 'helvetica', 'fontsize' => 100, 'stretchtext' => 10), 'N'));
                $vou_barcode .= '<tcpdf method="write1DBarcode" params="' . $vou_bar_params . '" />';

                $vou_barcode .= '</td>';
            }

            $vou_barcode .= '</tr>';
        }

        $vou_barcode .= '</table>';

        $html = str_replace('{barcodes}', $vou_barcode, $html);
    }

    return apply_filters('woo_vou_barcode_html', $html, $voucher_template_id, $pdf_vou_codes, $barcode_args);
}

/**
 * QRCode HTML
 * 
 * Handles to get qrcode html
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.4.6
 */
function woo_vou_qrcode_html($voucher_template_id, $pdf_vou_codes, $qrcode_args = array()) {

    $prefix = WOO_VOU_META_PREFIX;

    $content = '';

    $pdf_vou_codes = !empty($pdf_vou_codes) ? $pdf_vou_codes : '';
    $pdf_args_vou_codes = !empty($pdf_vou_codes) ? explode(',', $pdf_vou_codes) : array();

    //Get pdf size meta
    $woo_vou_template_size = get_post_meta($voucher_template_id, $prefix . 'pdf_size', true);
    $woo_vou_template_size = !empty($woo_vou_template_size) ? $woo_vou_template_size : 'A4';

    $font_size = isset($woo_vou_size_array['fontsize']) ? $woo_vou_size_array['fontsize'] : '12';

    //Get QR code Dimantion
    $qrcode_dimention = apply_filters('woo_vou_qrcode_dimention', array(
        'width' => !empty($qrcode_args['qrcode_width']) ? $qrcode_args['qrcode_width'] : round($font_size * 1.5),
        'height' => !empty($qrcode_args['qrcode_height']) ? $qrcode_args['qrcode_height'] : round($font_size * 1.5)
            ), $font_size, $woo_vou_template_size);

    $qrcode_code_w = isset($qrcode_dimention['width']) ? $qrcode_dimention['width'] : '';
    $qrcode_code_h = isset($qrcode_dimention['height']) ? $qrcode_dimention['height'] : '';
    $qrcode_code_c = isset($qrcode_args['qrcode_color']) ? $qrcode_args['qrcode_color'] : '#000000';
    $qrcode_code_a = isset($qrcode_args['qrcode_type']) ? $qrcode_args['qrcode_type'] : 'horizontal';
    $qrcode_code_t = !empty($qrcode_args['qrcode_symbol_type']) && $qrcode_args['qrcode_symbol_type'] != 'undefined' ? $qrcode_args['qrcode_symbol_type'] : 'QRCODE,H';
    $qrcode_code_b = !empty($qrcode_args['qrcode_border']) ? true : false;
    $qrcode_code_r = isset($qrcode_args['qrcode_response']) ? $qrcode_args['qrcode_response'] : 'url';

    $html = !empty($qrcode_args['content']) ? $qrcode_args['content'] : '{qrcode}';

    if (!class_exists('WPWEB_TCPDF')) { //If class not exist
        //include tcpdf file
        require_once WOO_VOU_DIR . '/includes/tcpdf/tcpdf.php';
    }

    // pdf object for qr code
    $pdf = new WPWEB_TCPDF(WPWEB_PDF_PAGE_ORIENTATION, WPWEB_PDF_UNIT, WPWEB_PDF_PAGE_FORMAT, true, 'UTF-8', false);

    if (!empty($pdf_vou_codes) && strpos($html, '{qrcode}') !== false) {// If qrcode is there

        $vou_qr_msg = $vou_qrcode = '';

        $vou_qr_msg = trim($pdf_vou_codes);

        if ($qrcode_code_r == 'url') {

            // make qrcode url used at scanning time
            $vou_qr_msg = apply_filters('woo_vou_qrcode_msg_data', site_url() . "?woo_vou_code=" . $vou_qr_msg, $vou_qr_msg);
        }

        $qrcode_style = apply_filters( 'woo_vou_qrcode_style', array(
        								'border' => $qrcode_code_b, 
        								'padding' => 1, 
        								'fgcolor' => woo_vou_hex_to_rgb($qrcode_code_c),
        								'fontsize' => 100
        							), $qrcode_code_b, $qrcode_code_c, $voucher_template_id);
        
        $vou_qr_params = $pdf->serializeTCPDFtagParameters(array($vou_qr_msg, $qrcode_code_t, '', '', $qrcode_code_w, $qrcode_code_h, $qrcode_style, 'N'));
        $vou_qrcode .= '<tcpdf method="write2DBarcode" params="' . $vou_qr_params . '" />';

        $html = str_replace('{qrcode}', $vou_qrcode, $html);
    }

    if (!empty($pdf_vou_codes) && strpos($html, '{qrcodes}') !== false) {// If qrcodes is there
        
        $vou_qr_msg = $vou_qrcode = '';

        if (!empty($pdf_args_vou_codes)) {

            $vou_qrcode .= '<table>';

            if ($qrcode_code_a == 'vertical') {

                foreach ($pdf_args_vou_codes as $pdf_args_vou_code) {

                    if (!empty($pdf_args_vou_code)) {

                        $vou_qrcode .= '<tr><td>';

                        $vou_qr_msg = trim($pdf_args_vou_code);

                        if ($qrcode_code_r == 'url') {

                            // make qrcode url used at scanning time
                            $vou_qr_msg = site_url() . "?woo_vou_code=" . urlencode($vou_qr_msg);
                        }

                        $vou_qr_params = $pdf->serializeTCPDFtagParameters(array($vou_qr_msg, $qrcode_code_t, '', '', $qrcode_code_w, $qrcode_code_h, array('border' => $qrcode_code_b, 'padding' => 1, 'fgcolor' => woo_vou_hex_to_rgb($qrcode_code_c), 'fontsize' => 100), 'N'));
                        $vou_qrcode .= '<tcpdf method="write2DBarcode" params="' . $vou_qr_params . '" />';

                        $vou_qrcode .= '</td></tr>';
                    }
                }
            } else {
                $vou_qrcode .= '<tr>';

                foreach ($pdf_args_vou_codes as $pdf_args_vou_code) {

                    if (!empty($pdf_args_vou_code)) {

                        $vou_qrcode .= '<td>';

                        $vou_qr_msg = trim($pdf_args_vou_code);

                        if ($qrcode_code_r == 'url') {

                            // make qrcode url used at scanning time
                            $vou_qr_msg = site_url() . "?woo_vou_code=" . urlencode($vou_qr_msg);
                        }

                        $vou_qr_params = $pdf->serializeTCPDFtagParameters(array($vou_qr_msg, $qrcode_code_t, '', '', $qrcode_code_w, $qrcode_code_h, array('border' => $qrcode_code_b, 'padding' => 1, 'fgcolor' => woo_vou_hex_to_rgb($qrcode_code_c), 'fontsize' => 100), 'N'));
                        $vou_qrcode .= '<tcpdf method="write2DBarcode" params="' . $vou_qr_params . '" />';

                        $vou_qrcode .= '</td>';
                    }
                }

                $vou_qrcode .= '</tr>';
            }

            $vou_qrcode .= '</table>';
        }

        $html = str_replace('{qrcodes}', $vou_qrcode, $html);
    }

    return apply_filters('woo_vou_qrcode_html', $html, $voucher_template_id, $pdf_vou_codes, $qrcode_args);
}

/**
 * Handles to generate product image html and return it
 *
 * @package WooCommerce - PDF Vouchers
 * @since 2.9.3
 */
function woo_vou_productimage_html($pdf_template_id, $vou_product_id, $product_image_args = array()) {

    $prefix = WOO_VOU_META_PREFIX; // Get prefix		
    $vou_relative_path_option = get_option('vou_enable_relative_path'); // Get global option for relative path
    $woo_vou_img_path = !empty($vou_relative_path_option) && $vou_relative_path_option == 'yes' ? WOO_VOU_IMG_DIR : WOO_VOU_IMG_URL; // Get image path depending on global option
    $html = !empty($product_image_args['content']) ? $product_image_args['content'] : '{productimage}'; // Get content for shortcode
    //Get pdf size meta
    $woo_vou_template_size = get_post_meta($pdf_template_id, $prefix . 'pdf_size', true);
    $woo_vou_template_size = !empty($woo_vou_template_size) ? $woo_vou_template_size : 'A4';

    $font_size = isset($woo_vou_size_array['fontsize']) ? $woo_vou_size_array['fontsize'] : '12';

    //Get Product Image dimension
    $product_image_dimention = apply_filters('woo_vou_qrcode_dimention', array(
        'width' => !empty($product_image_args['product_image_width']) ? $product_image_args['product_image_width'] : 100,
        'height' => !empty($product_image_args['product_image_height']) ? $product_image_args['product_image_height'] : 100
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

            $productimage_html = '<img src="' . esc_url(get_attached_file(get_post_meta($vou_product_id, '_thumbnail_id', true))) . '" alt="" width="' . $product_image_w . '" height=' . $product_image_h . ' />'; // Get relative path and append in image tag
        } else {

            // Product featured image
            $productimage = get_the_post_thumbnail_url($vou_product_id, apply_filters('woo_vou_productimage_shortcode', 'full'));
            $productimage_html = '<img src="' . esc_url($productimage) . '" alt="" width="' . $product_image_w . '" height=' . $product_image_h . ' />';
        }
    }

    $html = str_replace('{productimage}', $productimage_html, $html); // Replace html for image
    return apply_filters('woo_vou_productimage_html', $html, $pdf_template_id, $vou_product_id, $product_image_args); // Return html
}
