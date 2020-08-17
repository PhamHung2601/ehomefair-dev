<?php
/**
 * File for invoice.
 *
 * @package wk-woocommerce-marketplace/includes/templates/admin/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/*----------*/ /*---------->>> Order Invoice Template <<<----------*/ /*----------*/

/**
 * Invoice page.
 */
function mp_virtual_menu_invoice_page() {
		require_once 'order/invoice.php';
}

/*----------*/ /*---------->>> Order Invoice Button <<<----------*/ /*----------*/

/**
 * Order invoice button.
 *
 * @param obj $order order object.
 */
function order_invoice_button( $order ) {
		require 'order/invoice-button.php';
}

/**
 * Admin side invoice.
 *
 * @param int $order_id order id.
 */
function wk_admin_end_invoice( $order_id ) {
	require_once 'order/admin-invoice.php';
}
