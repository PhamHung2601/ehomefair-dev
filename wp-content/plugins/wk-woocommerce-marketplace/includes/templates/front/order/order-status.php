<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$row_count = 0;

$table_name = $wpdb->prefix . 'mpseller_orders';

$error = new WP_Error();

if ( ! isset( $data['mp_order_status_nonce'] ) || ! wp_verify_nonce( $data['mp_order_status_nonce'], 'mp_order_status_nonce_action' ) ) {

	$error->add( 'nonce-error', __( 'Sorry, your nonce did not verify.', 'marketplace' ) );

} else {

	$order_status = ( $data['mp-order-status'] ) ? sanitize_text_field( $data['mp-order-status'] ) : '';
	$order_id     = ( $data['mp-order-id'] ) ? intval( $data['mp-order-id'] ) : '';
	$seller_id    = ( $data['mp-seller-id'] ) ? intval( $data['mp-seller-id'] ) : '';
	$old_status   = ( $data['mp-old-order-status'] ) ? sanitize_text_field( $data['mp-old-order-status'] ) : '';

	$order = new WC_Order( $order_id );

	$items = $order->get_items();

	foreach ( $items as $key => $value ) {
		$author_array[] = get_post_field( 'post_author', $value->get_product_id() );
	}

	$order_author_count = count( $author_array );

	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s;", $wpdb->prefix . 'mpseller_orders' ) ) === $wpdb->prefix . 'mpseller_orders' ) {

		if ( empty( $order_status ) ) {

			$error->add( 'status-error', __( 'Select status for order.', 'marketplace' ) );

		} else if( $order_status == $old_status ) {

			$error->add( 'status-error', __( 'Order status is already "', 'marketplace' ) . ucfirst( explode( '-', $order_status )[1] ) . '".' );

		} else {

			$sql = $wpdb->update(
				$table_name,
				array(
					'order_status' => $order_status,
				),
				array(
					'order_id'  => $order_id,
					'seller_id' => $seller_id,
				),
				array(
					'%s',
				),
				array(
					'%d',
					'%d',
				)
			);


			if ( $sql ) {

				$author_name = get_user_by( 'ID', $seller_id );

				$status_array = wc_get_order_statuses();

				$author_name = $author_name->user_nicename;

				$note = __( "Vendor `{$author_name}` changed Order Status from {$status_array[$old_status]} to {$status_array[$order_status]} for it's own products." );

				$query = $wpdb->prepare( "SELECT count(*) as total from $table_name where order_id = '%d' and order_status = '%s'", $order_id, $order_status );

				$query_result = $wpdb->get_results( $query );

				if ( intval( $query_result[0]->total ) === $order_author_count ) {
					$order->update_status( $order_status, __( "Status updated to {$status_array[$order_status]} based on status updated by vendor's.", 'marketplace' ) );
				} else {
					$order->add_order_note( $note, 1 );
				}

				if ( is_admin() ) {
					?>
					<div class="wrap">
						<div class="notice notice-success">
							<p><?php echo esc_html__( 'Order status updated.', 'marketplace' ); ?></p>
						</div>
					</div>
					<?php
				} else {
					wc_add_notice( esc_html__( 'Order status updated.', 'marketplace' ), 'success' );
				}
			}

			do_action( 'wkmp_after_seller_update_order_status', $order, $data );

			$page_name = $wpdb->get_var( "SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option( 'wkmp_seller_page_title' ) . "'" );

			if ( ! is_admin() ) {
				wp_redirect( home_url( $page_name . '/' . get_option( 'mp_order_history', 'order-history' ) . '/' . $order_id ) );
				exit;
			}
		}
	} else {
		$error->add( 'status-error', __( 'Database table does not exist.', 'marketplace' ) );
	}
}

if ( is_wp_error( $error ) ) {
	foreach ( $error->get_error_messages() as $key => $value ) {
		if ( is_admin() ) {
			?>
			<div class="wrap">
				<div class="notice notice-error">
					<p><?php echo $value; ?></p>
				</div>
			</div>
			<?php
		} else {
			wc_print_notice( $value, 'error' );
		}
	}
}
