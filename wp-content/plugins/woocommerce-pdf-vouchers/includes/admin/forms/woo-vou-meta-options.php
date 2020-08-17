<?php
/**
 * Handles to add Style Options
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

global $woo_vou_model, $post;

$model = $woo_vou_model;

$prefix = WOO_VOU_META_PREFIX;
		
$bg_style = woo_vou_meta_value( array( 'id' => $prefix . 'pdf_bg_style', 'type' => 'radio' ) );
$pdf_view = woo_vou_meta_value( array( 'id' => $prefix . 'pdf_view', 'type' => 'select' ) );
$bg_pattern_css = $bg_image_css = $pdf_size_css = 'woo-vou-meta-display-none';

if( $bg_style == 'color' ) { //Check background style is color
	
} else if( $bg_style == 'image' ) { //Check background style is image
	$bg_image_css = '';
} else { //Check background style is pattern
	$bg_pattern_css = '';
}


$pdf_size_css	= '';


//Get pdf sizes
$pdf_sizes	= woo_vou_get_pdf_sizes_select();

wp_nonce_field( WOO_VOU_PLUGIN_BASENAME, 'at_woo_vou_meta_box_nonce' );

	woo_vou_content_begin();
	
		// voucher background image option
		woo_vou_add_radio( array( 'id' => $prefix . 'pdf_bg_style', 'name'=> esc_html__( 'Background Style:', 'woovoucher' ), 'default' => 'pattern', 'options' => array( 'pattern' => esc_html__( 'Background Pattern', 'woovoucher' ), 'image' => esc_html__( 'Background Image', 'woovoucher' ), 'color' => esc_html__( 'Background Color', 'woovoucher' ) ), 'desc' => esc_html__( 'Choose the background style for the PDF.', 'woovoucher' ) ) );
	
		// voucher background pattern
		woo_vou_add_bg_pattern( array( 'id' => $prefix . 'pdf_bg_pattern', 'wrap_class' => 'woo-vou-meta-bg-pattern-wrap ' . $bg_pattern_css, 'name'=> esc_html__( 'Background Pattern:', 'woovoucher' ), 'default' => 'pattern1', 'options' => array( 'pattern1', 'pattern2', 'pattern3', 'pattern4', 'pattern5' ), 'desc' => esc_html__( 'Select background pattern for the PDF.', 'woovoucher' ) ) );
	
		// voucher background image
		woo_vou_add_image( array( 'id' => $prefix . 'pdf_bg_img', 'wrap_class' => 'woo-vou-meta-bg-image-wrap ' . $bg_image_css, 'name'=> esc_html__( 'Background Image:', 'woovoucher' ), 'desc' => sprintf(esc_html__( 'Upload the background image for the PDF. %s Note: %s Image height/width should be the same size as per the PDF size you select.', 'woovoucher' ), "<br><b>", "</b>") ) );
	
		// voucher background color
		woo_vou_add_color( array( 'id' => $prefix . 'pdf_bg_color', 'name'=> esc_html__( 'Background Color:', 'woovoucher' ), 'desc' => esc_html__( 'Select background color for the PDF.', 'woovoucher' ) ) );
	
		// voucher lanscap or portrait view
		woo_vou_add_select( array( 'id' => $prefix . 'pdf_view', 'style' => 'min-width:200px;float: left;', 'class' => 'regular-text wc-enhanced-select', 'name'=> esc_html__( 'View:', 'woovoucher' ), 'options' => array( 'land' => esc_html__( 'Landscape', 'woovoucher' ), 'port' => esc_html__( 'Portrait', 'woovoucher' ) ), 'desc' => esc_html__( 'Select voucher PDF view in landscape or portrait.', 'woovoucher' ) ) );
		
		// voucher pdf size
		woo_vou_add_select( array( 'id' => $prefix . 'pdf_size', 'wrap_class' => 'woo-vou-meta-pdf-size-wrap ' . $pdf_size_css, 'default' => 'A4', 'style' => 'min-width:200px;float: left;', 'class' => 'regular-text wc-enhanced-select', 'name'=> esc_html__( 'PDF Size:', 'woovoucher' ), 'options' => $pdf_sizes, 'desc' => esc_html__( 'Select voucher PDF size.', 'woovoucher' ) ) );
		
		// voucher margin top
		woo_vou_add_number( array( 'id' => $prefix . 'pdf_margin_top', 'class' => 'small-text', 'name'=> esc_html__( 'Margin Top:', 'woovoucher' ), 'default' => 27, 'desc' => sprintf( esc_html__( 'Enter the margin top in a pixel for the PDF. Value must be greater then %s.', 'woovoucher' ), '<strong>0px</strong>'), 'field_desc' => ' px', 'min' => 1 ) );
		
		// voucher margin bottom
		woo_vou_add_number( array( 'id' => $prefix . 'pdf_margin_bottom', 'class' => 'small-text', 'name'=> esc_html__( 'Margin Bottom:', 'woovoucher' ), 'default' => 25, 'desc' => sprintf( esc_html__( 'Enter the margin bottom in a pixel for the PDF. Value must be greater then %s.', 'woovoucher' ), '<strong>0px</strong>'), 'field_desc' => ' px', 'min' => 1 ) );
	
		// voucher margin left
		woo_vou_add_number( array( 'id' => $prefix . 'pdf_margin_left', 'class' => 'small-text', 'name'=> esc_html__( 'Margin Left:', 'woovoucher' ), 'default' => 15, 'desc' => sprintf( esc_html__( 'Enter the margin left in a pixel for the PDF. Value must be greater then %s.', 'woovoucher' ), '<strong>0px</strong>'), 'field_desc' => ' px', 'min' => 1 ) );
	
		// voucher margin right
		woo_vou_add_number( array( 'id' => $prefix . 'pdf_margin_right', 'class' => 'small-text', 'name'=> __( 'Margin Right:', 'woovoucher' ), 'default' => 15, 'desc' => sprintf( esc_html__( 'Enter the margin right in a pixel for the PDF. Value must be greater then %s.', 'woovoucher' ), '<strong>0px</strong>'), 'field_desc' => ' px', 'min' => 1 ) );

		// voucher custom css
		woo_vou_add_textarea( array( 'id' => $prefix . 'pdf_custom_css', 'name'=> esc_html__( 'Custom CSS:', 'woovoucher' ), 'desc' => esc_html__( 'Here you can enter your custom CSS for the Voucher Template. It will only supports basic CSS like font-size, font-weight, font-style, color, text-decoration, height, width and text-align.', 'woovoucher' ) ) );
	
	woo_vou_content_end();	

?>