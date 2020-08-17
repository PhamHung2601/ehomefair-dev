<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * QtranslateX Compatibility Class
 * 
 * Handles to Add QtranslateX compatibility
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.8.0
 */
class WOO_Vou_QtranslateX {		
	
	function __construct() {
				
	}
	
	/**
	 * Adding Hooks
	 * 
	 * Adding proper hooks for QTranslateX.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.0
	 */
	public function add_hooks() {
	
		/**
		 * As QTranslateX save all languages code in post content so voucher template content will be
		 * in all languages. So we need to get activated language content otherwise in voucher template
		 * PDF, all languages content will appear
		 * 
		 * This filter will only download voucher content of current language
		 */
		add_filter( 'woo_vou_voucher_template_content', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage', 20 );
        add_filter( 'woo_vou_product_name', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage', 20 );
        add_filter( 'woocommerce_product_title','qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage', 20 );
	}
}