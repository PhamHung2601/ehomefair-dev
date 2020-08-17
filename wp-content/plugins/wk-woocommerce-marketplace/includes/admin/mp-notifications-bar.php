<?php

global $wpdb;

$user_id = get_current_user_id();

$total_count = $wpdb->get_results( "Select count(id) as total from {$wpdb->prefix}mp_notifications where read_flag = '0' and author_id = '$user_id' ", ARRAY_A );

$total_count = $total_count[0]['total'];

$all_reader_text = sprintf( '%s' . _n( ' notification', ' notifications', $total_count, 'marketplace' ), number_format_i18n( $total_count ) );

$all_counter = sprintf( ' <div class="wp-core-ui wp-ui-notification mp-notification-counter"><span aria-hidden="true">%d</span><span class="screen-reader-text">%s</span></div>', $total_count, $all_reader_text );

$order_count = $wpdb->get_results( "Select count(id) as total from {$wpdb->prefix}mp_notifications where read_flag = '0' and type='order' and author_id = '$user_id' ", ARRAY_A );

$order_reader_text = sprintf( '%s' . _n( ' notification', ' notifications', $order_count[0]['total'], 'marketplace' ), number_format_i18n( $order_count[0]['total'] ) );

$order_counter = sprintf( ' <div class="wp-core-ui wp-ui-notification mp-notification-counter"><span aria-hidden="true">%d</span><span class="screen-reader-text">%s</span></div>', $order_count[0]['total'], $order_reader_text );

$product_count = $wpdb->get_results( "Select count(id) as total from {$wpdb->prefix}mp_notifications where read_flag = '0' and type='product' and author_id = '$user_id' ", ARRAY_A );

$product_reader_text = sprintf( '%s' . _n( ' notification', ' notifications', $product_count[0]['total'], 'marketplace' ), number_format_i18n( $product_count[0]['total'] ) );

$product_counter = sprintf( ' <div class="wp-core-ui wp-ui-notification mp-notification-counter"><span aria-hidden="true">%d</span><span class="screen-reader-text">%s</span></div>', $product_count[0]['total'], $product_reader_text );

$seller_count = $wpdb->get_results( "Select count(id) as total from {$wpdb->prefix}mp_notifications where read_flag = '0' and type='seller' and author_id = '$user_id' ", ARRAY_A );

$seller_reader_text = sprintf( '%s' . _n( ' notification', ' notifications', $seller_count[0]['total'], 'marketplace' ), number_format_i18n( $seller_count[0]['total'] ) );

$seller_counter = sprintf( ' <div class="wp-core-ui wp-ui-notification mp-notification-counter"><span aria-hidden="true">%d</span><span class="screen-reader-text">%s</span></div>', $seller_count[0]['total'], $seller_reader_text );



$admin_bar->add_menu( array(
	'id'    => 'mp-notification',
	'title' => '<img style="position: relative; top: 5px;" src="' . WK_MARKETPLACE . 'assets/images/notify.png">' . $all_counter,
	'href'  => admin_url( 'admin.php?page=mp-notification' ),
	'meta'  => array(
		'title' => esc_html__( 'Notification', 'marketplace' ),
	),
));

$admin_bar->add_menu( array(
	'parent' => 'mp-notification',
	'id'     => 'mp-notification-order',
	'title'  => __( 'Orders', 'marketplace' ) . $order_counter,
	'href'   => admin_url( 'admin.php?page=mp-notification' ),
	'meta'   => array(),
) );

$admin_bar->add_menu( array(
	'parent' => 'mp-notification',
	'id'     => 'mp-notification-product',
	'title'  => __( 'Products', 'marketplace' ) . $product_counter,
	'href'   => admin_url( 'admin.php?page=mp-notification&tab=products' ),
	'meta'   => array(),
) );

// $admin_bar->add_menu( array(
//   	'parent' => 'mp-notification',
//   	'id'     => 'mp-notification-seller',
//   	'title'  => __( 'Seller', 'marketplace' ) . $seller_counter,
//   	'href'   => admin_url( 'admin.php?page=mp-notification&tab=seller' ),
//   	'meta'   => array(  ),
// ) );
