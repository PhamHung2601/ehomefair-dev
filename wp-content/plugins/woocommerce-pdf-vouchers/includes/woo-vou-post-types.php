<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Post Type Functions
 *
 * Handles all custom post types
 * functions
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0 
 */

/**
 * Register Post Type
 *
 * Handles to registers the Voucher 
 * post type
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_register_post_types() {
	
	//register Woocommerce voucher templates post type
	$voulabels = array(
					'name'					=> esc_html__( 'Voucher Templates', 'woovoucher' ),
					'singular_name'			=> esc_html__( 'Voucher Template', 'woovoucher' ),
					'add_new'				=> _x( 'Add New', WOO_VOU_POST_TYPE, 'woovoucher' ),
					'add_new_item'			=> sprintf( esc_html__( 'Add New %s' , 'woovoucher' ), esc_html__( 'Voucher Template' , 'woovoucher' ) ),
					'edit_item'				=> sprintf( esc_html__( 'Edit %s' , 'woovoucher' ), esc_html__( 'Voucher Template' , 'woovoucher' ) ),
					'new_item'				=> sprintf( esc_html__( 'New %s' , 'woovoucher' ), esc_html__( 'Voucher Template' , 'woovoucher' ) ),
					'all_items'				=> esc_html__( 'Voucher Templates' , 'woovoucher' ),
					'view_item'				=> sprintf( esc_html__( 'View %s' , 'woovoucher' ), esc_html__( 'Voucher Template' , 'woovoucher' ) ),
					'search_items'			=> sprintf( esc_html__( 'Search %a' , 'woovoucher' ), esc_html__( 'Voucher Templates' , 'woovoucher' ) ),
					'not_found'				=> sprintf( esc_html__( 'No %s Found' , 'woovoucher' ), esc_html__( 'Voucher Templates' , 'woovoucher' ) ),
					'not_found_in_trash'	=> sprintf( esc_html__( 'No %s Found In Trash' , 'woovoucher' ), esc_html__( 'Voucher Templates' , 'woovoucher' ) ),
					'parent_item_colon'		=> '',
					'menu_name' 			=> esc_html__( 'Voucher Templates' , 'woovoucher' ),
					'featured_image'        => esc_html__( 'Preview Image', 'woovoucher' ),
					'set_featured_image'    => esc_html__( 'Set preview image', 'woovoucher' ),
					'remove_featured_image' => esc_html__( 'Remove preview image', 'woovoucher' ),
					'use_featured_image'    => esc_html__( 'Use as preview image', 'woovoucher' ),
				);

	$vouargs = array(
				'labels'				=> $voulabels,
				'public' 				=> false,
			    'show_ui' 				=> true, 
			    'capability_type' 		=> WOO_VOU_POST_TYPE,
			    'publicly_queryable' 	=> false,
			    'exclude_from_search'	=> true,
				'map_meta_cap'        	=> true,
			    'show_in_menu' 			=> current_user_can( 'manage_woocommerce' ) ? WOO_VOU_MAIN_MENU_NAME : true,
			    'hierarchical' 			=> false,
				'show_in_nav_menus'   	=> false,
			    'rewrite' 				=> false,
			    'query_var' 			=> false,
			    'supports' 				=> array( 'title', 'editor', 'thumbnail' ),
		  	);
	register_post_type( WOO_VOU_POST_TYPE, $vouargs );
	
	//register Woocommerce voucher codes post type
	$voucodelabels = array(
					'name'					=> esc_html__( 'Voucher Codes', 'woovoucher' ),
					'singular_name'			=> esc_html__( 'Voucher Code', 'woovoucher' ),
					'add_new'				=> _x( 'Add New', WOO_VOU_CODE_POST_TYPE, 'woovoucher' ),
					'add_new_item'			=> sprintf( esc_html__( 'Add New %s' , 'woovoucher' ), esc_html__( 'Voucher Code' , 'woovoucher' ) ),
					'edit_item'				=> sprintf( esc_html__( 'Edit %s' , 'woovoucher' ), esc_html__( 'Voucher Code' , 'woovoucher' ) ),
					'new_item'				=> sprintf( esc_html__( 'New %s' , 'woovoucher' ), esc_html__( 'Voucher Code' , 'woovoucher' ) ),
					'all_items'				=> esc_html__( 'Voucher Codes' , 'woovoucher' ),
					'view_item'				=> sprintf( esc_html__( 'View %s' , 'woovoucher' ), esc_html__( 'Voucher Code' , 'woovoucher' ) ),
					'search_items'			=> sprintf( esc_html__( 'Search %a' , 'woovoucher' ), esc_html__( 'Voucher Codes' , 'woovoucher' ) ),
					'not_found'				=> sprintf( esc_html__( 'No %s Found' , 'woovoucher' ), esc_html__( 'Voucher Codes' , 'woovoucher' ) ),
					'not_found_in_trash'	=> sprintf( esc_html__( 'No %s Found In Trash' , 'woovoucher' ), esc_html__( 'Voucher Codes' , 'woovoucher' ) ),
					'parent_item_colon'		=> '',
					'menu_name' 			=> esc_html__( 'Voucher Codes' , 'woovoucher' )
				);

	$voucodeargs = array(
				'labels'				=> $voucodelabels,
				'public' 				=> false,
			    'exclude_from_search'	=> true,
			    'query_var' 			=> false,
			    'rewrite' 				=> false,
			    'capability_type' 		=> WOO_VOU_CODE_POST_TYPE,
			    'hierarchical' 			=> false,
			    'supports' 				=> array( 'title' )
			);
	register_post_type( WOO_VOU_CODE_POST_TYPE, $voucodeargs );
	
	// register WooCommerce partially redeem voucher codes post type
	$vou_partial_redeem_labels = array(
		'name'					=> esc_html__( 'Partially Redeem Voucher Codes', 'woovoucher' ),
		'singular_name'			=> esc_html__( 'Partially Redeem Voucher Code', 'woovoucher' ),
		'add_new'				=> _x( 'Add New', WOO_VOU_PARTIAL_REDEEM_POST_TYPE, 'woovoucher' ),
		'add_new_item'			=> sprintf( esc_html__( 'Add New %s' , 'woovoucher' ), esc_html__( 'Partially Redeem Voucher Code' , 'woovoucher' ) ),
		'edit_item'				=> sprintf( esc_html__( 'Edit %s' , 'woovoucher' ), esc_html__( 'Partially Redeem Voucher Code' , 'woovoucher' ) ),
		'new_item'				=> sprintf( esc_html__( 'New %s' , 'woovoucher' ), esc_html__( 'Partially Redeem Voucher Code' , 'woovoucher' ) ),
		'all_items'				=> esc_html__( 'Partially Redeem Voucher Codes' , 'woovoucher' ),
		'view_item'				=> sprintf( esc_html__( 'View %s' , 'woovoucher' ), esc_html__( 'Partially Redeem Voucher Code' , 'woovoucher' ) ),
		'search_items'			=> sprintf( esc_html__( 'Search %a' , 'woovoucher' ), esc_html__( 'Partially Redeem Voucher Codes' , 'woovoucher' ) ),
		'not_found'				=> sprintf( esc_html__( 'No %s Found' , 'woovoucher' ), esc_html__( 'Partially Redeem Voucher Codes' , 'woovoucher' ) ),
		'not_found_in_trash'	=> sprintf( esc_html__( 'No %s Found In Trash' , 'woovoucher' ), esc_html__( 'Partially Redeem Voucher Codes' , 'woovoucher' ) ),
		'parent_item_colon'		=> '',
		'menu_name' 			=> esc_html__( 'Partially Redeem Voucher Codes' , 'woovoucher' )
	);

	$vou_partial_redeem_args = array(
		'labels'				=> $vou_partial_redeem_labels,
		'public' 				=> false,
	    'exclude_from_search'	=> true,
	    'query_var' 			=> false,
	    'rewrite' 				=> false,
	    'capability_type' 		=> WOO_VOU_PARTIAL_REDEEM_POST_TYPE,
	    'hierarchical' 			=> false,
	    'supports' 				=> array( 'title' )
	);
	
	// finally register post type
	register_post_type( WOO_VOU_PARTIAL_REDEEM_POST_TYPE, $vou_partial_redeem_args );
}
//register custom post type
// we need to keep priority 100, because we need to execute this init action after all other init action called.
add_action( 'init', 'woo_vou_register_post_types' );

/**
 * Register Post Status
 * 
 * Handles to registers voucher post status
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.3
 */
function woo_vou_register_post_status() {
	
	register_post_status( WOO_VOU_REFUND_STATUS, array(
		'label'                     => _x( 'Refunded', 'Voucher status', 'woovoucher' ),
		'public'                    => false,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'woovoucher' )
	) );
}
add_action( 'init', 'woo_vou_register_post_status', 9 );