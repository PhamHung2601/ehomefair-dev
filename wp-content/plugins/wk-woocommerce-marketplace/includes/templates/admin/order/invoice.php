<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*----------*/ /*---------->>> Order Invoice Template <<<----------*/ /*----------*/

$hook = add_submenu_page( null, 'Invoice', 'Invoice', 'administrator', 'invoice', function() {} );

add_action('load-' . $hook, function() {

	if ( is_user_logged_in() && is_admin() ) {

		$order_id = base64_decode( $_GET['order_id'] );

		wk_admin_end_invoice( $order_id );
	} else {

		wp_die( '<h1>' . __( 'Cheatin’ uh?', 'marketplace' ) . '</h1><p>' . __( 'Sorry, you are not allowed to access invoice.', 'marketplace' ) . '</p>' );
	}

	exit;

});
