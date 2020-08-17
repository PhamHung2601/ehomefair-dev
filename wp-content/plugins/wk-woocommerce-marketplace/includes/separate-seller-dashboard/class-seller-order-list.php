<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'Seller_Order_List_Table' ) ) {
	class Seller_Order_List_Table extends WP_List_Table {
		public function __construct() {
			parent::__construct(
				array(
					'singular' => 'order',
					'plural'   => 'orders',
					'ajax'     => false,
				)
			);
		}

		public function prepare_items() {
			global $wpdb;

			$columns = $this->get_columns();

			$sortable = $this->get_sortable_columns();

			$hidden = $this->get_hidden_columns();

			$data = ( $this->table_data() ) ? $this->table_data() : array();

			$totalitems = count( $data );

			$perpage = $this->get_items_per_page( 'order_per_page', 20 );

			$this->_column_headers = array( $columns, $hidden, $sortable );

			usort( $data, array( $this, 'wk_usort_reorder' ) );

			$totalpages = ceil( $totalitems / $perpage );

			$currentPage = $this->get_pagenum();

			$data = array_slice( $data, ( ( $currentPage - 1 ) * $perpage ), $perpage );

			$this->set_pagination_args( array(
				'total_items' => $totalitems,
				'total_pages' => $totalpages,
				'per_page'    => $perpage,
			) );

			$this->items = $data;

		}

		public function wk_usort_reorder( $a, $b ) {

			$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'order_id'; // If no sort, default to title.

			$order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'desc'; // If no order, default to asc.

			$result = strcmp( $a[ $orderby ], $b[ $orderby ] ); // Determine sort order.

			return ( $order === 'asc' ) ? $result : -$result; // Send final sort direction to usort.

		}

		public function get_hidden_columns() {
			return array();
		}

		function get_columns() {
			$columns = array(
				'order_id'     => __( 'Order', 'marketplace' ),
				'order_status' => __( 'Status', 'marketplace' ),
				'order_date'   => __( 'Date', 'marketplace' ),
				'order_total'  => __( 'Total', 'marketplace' ),
				'action'       => __( 'Action' ),
			);

			return $columns;
		}

		public function get_sortable_columns() {
			$sortable_columns = array(
				'order_id'   => array( 'order_id', true ),
				'order_date' => array( 'order_date', true ),
			);

			return $sortable_columns;
		}

		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'order_id':
				case 'order_status':
				case 'order_date':
				case 'order_total':
				case 'action':
					return $item[ $column_name ];
				default:
					return print_r( $item, true );
			}
		}

		private function table_data() {

			global $wpdb;

			$commission = new MP_Commission();

			$wpmp_obj5 = new MP_Form_Handler();

			$user_id = get_current_user_id();

			$page_id = $wpmp_obj5->get_page_id(get_option('wkmp_seller_page_title'));

			$search = '';

			if ( isset( $_GET['s'] ) && filter_input( INPUT_GET, 's', FILTER_SANITIZE_NUMBER_INT ) ) {
				$search = 'and order_id like "%' . filter_input( INPUT_GET, 's', FILTER_SANITIZE_NUMBER_INT ) . '%"';
			}

			$page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option('wkmp_seller_page_title') . "'");

			$order_detail = $wpdb->get_results("SELECT DISTINCT order_id FROM {$wpdb->prefix}mporders WHERE seller_id='" . $user_id . "' $search ORDER BY order_id DESC");

			$order_detail = apply_filters('mp_vendor_split_orders', $order_detail, $user_id);
			
			$all_order_details = array();
			$order_id_list = array();
			
			foreach ($order_detail as $order_dtl) {
				$order_status = $query_result = '';
				$order_id = $order_dtl->order_id;
				// $order_id = apply_filters('mp_set_parent_order', $order_id);
				$order_id_list[] = $order_id;
				$order = wc_get_order($order_id);
				if( !empty( $order ) ) {

					$cur_symbol = get_woocommerce_currency_symbol($order->get_currency());
					$get_item = $order->get_items();

					if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s;', $wpdb->prefix.'mpseller_orders')) === $wpdb->prefix.'mpseller_orders') {
						$query = $wpdb->prepare("SELECT order_status from {$wpdb->prefix}mpseller_orders where order_id = '%d' and seller_id = '%d'", $order_id, $user_id);
						$query_result = $wpdb->get_results($query);
					}
		
					if ($query_result) {
						$order_status = $query_result[0]->order_status;
					}
					if (!$order_status) {
						$order_status = get_post_field('post_status', $order_id);
					}

					$status_array = wc_get_order_statuses();

					foreach ($get_item as $key => $value) {
						$product_id = $value->get_product_id();
						$variable_id = $value->get_variation_id();
						$post = get_post($product_id, ARRAY_A);
						if ($post['post_author'] == $user_id) {
							$price_id = $product_id;
							$type = 'simple';
							$qty = $value->get_quantity();
							$product = new WC_Product($price_id);
							if ($variable_id != 0) {
								$price_id = $variable_id;
								$type = 'variable';
								$product = new WC_Product_Variation($price_id);
							}
							$product_price = $product->get_price();
							$display_status = isset($status_array[$order_status]) ? $status_array[$order_status] : '-';

							$all_order_details[$order_id][] = array(
								'order_date' => date_format($order->get_date_created(), 'Y-m-d H:i:s'),
								'order_status' => $display_status,
								'product_price' => $product_price,
								'qty' => $qty,
							);
						}
					}

				}
			}
			
			for ($counter = 0; $counter < count($order_id_list); ++$counter) {
				$order_id = $order_id_list[$counter];
				foreach ($all_order_details as $key => $value) {
					if ($order_id == $key) {
						$order = wc_get_order($order_id);
						if( !empty( $order ) ) {
							
							$cur_symbol = get_woocommerce_currency_symbol($order->get_currency());
							foreach ($value as $index => $val) {
								$qty = $val['qty'];
								$total_price = $val['product_price'];
								$status = $val['order_status'];
								$date = $val['order_date'];

								if (isset($order_by_table[$key])) {
									$total_price = $order_by_table[$key]['total_price'] + $total_price;
									$total_qty = $order_by_table[$key]['total_qty'] + $qty;

									$order_by_table[$key] = array(
										'symbol' => $cur_symbol,
										'status' => $status,
										'date' => $date,
										'total_price' => $total_price,
										'total_qty' => $total_qty,
									);
								} else {
									$order_by_table[$key] = array(
										'symbol' => $cur_symbol,
										'status' => $status,
										'date' => $date,
										'total_price' => $total_price,
										'total_qty' => $qty,
									);
								}

								$ord_info = $commission->get_seller_order_info($key, $user_id);

								$order_by_table[$key]['total_qty'] = $ord_info['total_qty'];
								$order_by_table[$key]['total_price'] = $ord_info['total_sel_amt'] + $ord_info['ship_data'];
							}

						}
					}
				}
			}

			$data = array();

			if ( $order_by_table ) {
				foreach ( $order_by_table as $key => $value ) {
					$seller_order_refund_data = maybe_unserialize( $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}mporders_meta WHERE seller_id=%d AND order_id=%d AND meta_key=%s", $user_id, $key, '_wkmp_refund_status' ) ) );

					if( !empty( $seller_order_refund_data[ 'refunded_amount' ] ) ) {

                        $total = '<del>' . $value['total_price'] . '</del> ' . $value['symbol'] . round( $value['total_price'] - $seller_order_refund_data[ 'refunded_amount' ], 2 );

                    } else {

                        $total = $value['total_price'];

                    }

					$data[] = array(
						'order_id'     => $key,
						'order_status' => $value['status'],
						'order_date'   => $value['date'],
						'order_total'  => $value['symbol'] . $total . ' ' . __('for', 'marketplace') . ' ' . $value['total_qty'] . ' ' . __(' items', 'marketplace'),
						'action'       => '<a href="' . admin_url( 'admin.php?page=order-history&action=view&oid=' . $key ) . '" class="button button-primary">' . __( 'View', 'marketplace' ) . '</a>',
					);
				}
			}

			return $data;
		}
	}

	global $wpdb;

	$client_info_table = new Seller_Order_List_Table();

	if ( isset( $_GET['s'] ) ) {
		$client_info_table->prepare_items( $_GET['s'] );
	} else {
		$client_info_table->prepare_items();
	}

	?>

	<div class="wrap">

		<h1 class="wp-heading-inline"><?php echo esc_html__( 'Orders', 'marketplace' ); ?></h1>

	<hr>

	<form method="GET">

	<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />

	<?php

	$client_info_table->search_box( 'Search', 'search-id' );

	$client_info_table->display();

	?>

	</form>

	</div>

	<?php
}