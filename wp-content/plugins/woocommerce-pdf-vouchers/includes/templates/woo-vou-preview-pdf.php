<?php
/**
 * Preview PDF Template
 * 
 * Handles to load Preview PDF template
 * 
 * Override this template by copying it to yourtheme/woocommerce/woocommerce-pdf-vouchers/woo-vou-preview-pdf.php
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.5.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//Get Prefix
$prefix		= WOO_VOU_META_PREFIX;
$preview_pdf_title = apply_filters( 'woo_vou_preview_pdf_title', esc_html__( 'Preview', 'woovoucher' ) );
?>
<div id="woo-vou-preview-pdf-wrap-<?php echo $variation_id; ?>" class="woo-vou-preview-pdf-wrap">
	<input type="hidden" name="woo_vou_product_id" id="woo_vou_product_id" value="<?php echo $product_id; ?>" />
	<input type="hidden" name="woo_vou_variation_id" id="woo_vou_variation_id" value="<?php echo $variation_id; ?>" />
	<a href="#" class="woo_vou_preview_pdf"><?php echo $preview_pdf_title; ?></a><img src="<?php echo esc_url(WOO_VOU_URL) . '/includes/images/ajax-loader-2.gif'; ?>" class="woo-vou-preview-loader" />
</div>