<?php

/**
 * Preview PDF Popup Template
 * 
 * Handles to load Preview PDF Popup template
 * 
 * Override this template by copying it to yourtheme/woocommerce/woocommerce-pdf-vouchers/woo-vou-preview-pdf-popup.php
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.5.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product;

//Get Prefix
$prefix		= WOO_VOU_META_PREFIX;
$preview_pdf_popup_title = apply_filters( 'woo_vou_preview_pdf_title', esc_html__( 'Preview', 'woovoucher' ) );
?>

<div class="woo-vou-popup-content woo-vou-preview-pdf-content">
				
	<div class="woo-vou-header">
		<div class="woo-vou-header-title"><?php echo $preview_pdf_popup_title; ?></div>
		<div class="woo-vou-popup-close"><a href="javascript:void(0);" class="woo-vou-close-button"><img src="<?php echo WOO_VOU_URL .'includes/images/tb-close.png'; ?>" alt="<?php esc_html_e( 'Close','woovoucher' ); ?>"></a></div>
	</div>
		
	<div class="woo-vou-popup">
		<iframe class="woo-vou-preview-pdf-iframe" src="" width="100%" height="100%"></iframe>
	</div><!--.woo-vou-popup-->
</div><!--.woo-vou-popup-content-->

<div class="woo-vou-popup-overlay woo-vou-preview-pdf-overlay"></div>