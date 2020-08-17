<?php
/**
 * Scripts
 *
 * @package     Woocommerce Table Rate Shipping\Scripts
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Load frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function mp_table_rate_scripts( $hook ) {

	$countries=WC()->countries->countries;

	wp_enqueue_script( 'select2',MP_SHIPPING_URL.'assets/js/select2.min.js' );

  wp_enqueue_script( 'wk_table_rate_admin_js', MP_SHIPPING_URL . 'assets/js/mp-table-rate.js', array( 'jquery' ) );

  wp_enqueue_style( 'wk_table_rate_admin_css', MP_SHIPPING_URL . 'assets/css/style.css' );

  wp_localize_script( 'wk_table_rate_admin_js', 'country_script', array( 'country_list' => $countries,'sajaxurl'=>admin_url( 'admin-ajax.php' ),'nonce'=>wp_create_nonce('table-rate-ajaxnonce')));
	
}


/*$mp_objvariation*/
add_action( 'wp_ajax_nopriv_row_delete_confirmation','row_delete_confirmation' );
add_action( 'wp_ajax_row_delete_confirmation','row_delete_confirmation');

function row_delete_confirmation(){

	if( check_ajax_referer( 'table-rate-ajaxnonce', 'nonce', false ) ) {

		global $wpdb;

		$table_name = "{$wpdb->prefix}woocommerce_table_rate_shipping";

    $shipping_id= intval( $_POST['shipping_id'] );

    // Using where formatting.
		$res = $wpdb->delete( $table_name, array( 'shipping_id' => $shipping_id ), array( '%d' ) );

		echo $res;

	  die;

	}

}

add_action( 'wp_enqueue_scripts', 'mp_table_rate_scripts' );

add_action( 'admin_enqueue_scripts', 'mp_table_rate_scripts' );
