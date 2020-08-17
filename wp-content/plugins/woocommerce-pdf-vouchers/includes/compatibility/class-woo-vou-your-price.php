<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WC Name Your Price & WC Pay Your Price Compability Class
 * 
 * Handles Compatibility of WooCommerce Name Your Price 
 * & WooCommerce Pay Your Price
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.8.2
 */
class WOO_Vou_Your_Price {
	
	public $model;
	
	function __construct() {
		
		global $woo_vou_model;
		$this->model = $woo_vou_model;
	}

	/**
	 * Add value in public script localise array
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.8.2
	 */
	public function woo_vou_yourprice_script_localise_arr($public_localise_arr) {

        if ( class_exists('WC_Name_Your_Price') ){
        	$public_localise_arr['payyourprice'] = 'nyp';
        } elseif ( class_exists('PayYourPrice') ){
        	$public_localise_arr['payyourprice'] = 'payyourprice_contribution';
        }
        
	    return $public_localise_arr;
	}

	/**
	 * Add value in public script localise array
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.8.2
	 */
	public function woo_vou_yourprice_pdf_preview_price( $voucherprice, $post_arr ) {


        if ( !empty($post_arr['payyourprice'] ) ){
            $voucherprice = $post_arr['payyourprice'];
        }
        
	    return $voucherprice;
	}

	/**
	 * Adding Hooks
	 * 
	 * Adding proper hooks for the WC Your Price.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.8.2
	 */
	public function add_hooks() {

        //Add value in public script localise array
        add_filter( 'woo_vou_public_script_localise_arr', array( $this, 'woo_vou_yourprice_script_localise_arr' ) );

        //Add value in public script localise array
        add_filter( 'woo_vou_pdf_preview_price', array( $this, 'woo_vou_yourprice_pdf_preview_price' ), 10, 2 );
	}
}

/**
 * JS code added in:
 * File: woo-vou-public.js
 * Line: 219 to 223
*/