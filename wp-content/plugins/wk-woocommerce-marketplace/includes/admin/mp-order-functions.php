<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Functionon changing the order status.
 *
 * @param int $order_id   order id.
 * @param int $old_status order old status.
 * @param int $new_status order new status.
 */
function mp_order_status_changed_action( $order_id, $old_status, $new_status ) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'mpseller_orders';

	$sql = $wpdb->update(
		$table_name,
		array(
			'order_status' => 'wc-' . $new_status,
		),
		array(
			'order_id' => $order_id,
		),
		array(
			'%s',
		),
		array(
			'%d',
		)
	);
}

/**
 * Function for managing seller order.
 *
 * @param int $seller_id seller id.
 */
function mp_manage_seller_orders( $seller_id ) {

	global $wpdb;

	$order_list = $wpdb->get_results( $wpdb->prepare( "select distinct woitems.order_id from {$wpdb->prefix}woocommerce_order_itemmeta woi join {$wpdb->prefix}woocommerce_order_items woitems on woitems.order_item_id=woi.order_item_id join {$wpdb->prefix}posts post on woi.meta_value=post.ID where woi.meta_key=%s and post.ID=woi.meta_value and post.post_author=%d order by woitems.order_id DESC", '_product_id', $seller_id ) );

	$items = array(
		'Order',
		'Product',
		'Quantity',
		'Product Total',
		'Status',
		'Paid Status',
	);

	require_once WK_MARKETPLACE_DIR . 'includes/admin/account/seller/orders.php';

}
