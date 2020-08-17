<?php
/**
 * File for seller shop followers.
 *
 * @package  wk-woocommerce-marketplace/includes/seperate-seller-dashboard/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'Seller_Shop_Followers' ) ) {

	/**
	 * Class for listing seller queries.
	 */
	class Seller_Shop_Followers extends WP_List_Table {

		/**
		 * Constructor.
		 */
		public function __construct() {
			parent::__construct(
				array(
					'singular' => 'query',
					'plural'   => 'queries',
					'ajax'     => false,
				)
			);
		}

		/**
		 * Prepare item.
		 */
		public function prepare_items() {
			global $wpdb;

			$columns = $this->get_columns();

			$sortable = $this->get_sortable_columns();

			$hidden = $this->get_hidden_columns();

			$this->process_bulk_action();

			$data = ( $this->table_data() ) ? $this->table_data() : array();

			$totalitems = count( $data );

			$perpage = 20;

			$this->_column_headers = array( $columns, $hidden, $sortable );

			usort( $data, array( $this, 'wk_usort_reorder' ) );

			$totalpages = ceil( $totalitems / $perpage );

			$currentpage = $this->get_pagenum();

			$data = array_slice( $data, ( ( $currentpage - 1 ) * $perpage ), $perpage );

			$this->set_pagination_args( array(

				'total_items' => $totalitems,

				'total_pages' => $totalpages,

				'per_page'    => $perpage,

			) );

			$this->items = $data;

		}

		/**
		 * Sort reorder function.
		 *
		 * @param string $a a.
		 * @param string $b b.
		 */
		public function wk_usort_reorder( $a, $b ) {

			$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'name'; // If no sort, default to title.

			$order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'desc'; // If no order, default to asc.

			$result = strcmp( $a[ $orderby ], $b[ $orderby ] ); // Determine sort order.

			return ( 'asc' === $order ) ? $result : -$result; // Send final sort direction to usort.

		}

		/**
		 * For hidden columns.
		 */
		public function get_hidden_columns() {
			return array();
		}

		public function get_columns() {
			$columns = array(
				'cb'    => '<input type="checkbox" />',
				'name'  => __( 'Customer Name', 'marketplace' ),
				'email' => __( 'Customer Email', 'marketplace' ),
			);

			return $columns;
		}

		/**
		 * Get sortable columns.
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'name' => array( 'date', true ),
			);

			return $sortable_columns;
		}

		/**
		 * Get Default columns.
		 *
		 * @param array  $item item data.
		 * @param string $column_name col name.
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'cb':
				case 'name':
				case 'email':
					return $item[ $column_name ];
				default:
					return print_r( $item, true );
			}
		}

		/**
		 * Column checkbox data.
		 *
		 * @param array $item item array.
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" id="user_%s"name="user[]" value="%s" />', $item['id'], $item['id'] );
		}

		/**
		 * Column checkbox data.
		 *
		 * @param array $item item array.
		 */
		public function column_name( $item ) {

			$actions = array(
				'delete' => sprintf( '<a class="submitdelete" href="?page=seller-shop-followers&action=delete&user=%s&_wpnonce=%s">Delete</a>', $item['id'], wp_create_nonce( 'del_mp_nonceuser_' . $item['id'] ) ),
			);
			return sprintf( '%1$s %2$s', $item['name'], $this->row_actions( $actions ) );
		}

		/**
		 * Bulk action.
		 */
		public function get_bulk_actions() {
			$actions = array(
				'delete' => 'Delete',
			);
			return $actions;
		}

		/**
		 * Process bulk action.
		 */
		public function process_bulk_action() {
			$get = $_GET;
			if ( isset( $get['action'] ) && ! empty( $get['action'] ) ) {
				$action = $get['action'];
			}
			if ( ! empty( $action ) ) {

				if ( isset( $get['user'] ) ) {
					switch ( $action ) {
						case 'delete':
							$user_id = $get['user'];
							if ( ! is_array( $user_id ) ) {
								$user_id = array( $user_id );
							}
							foreach ( $user_id as $u_id ) {
								$seller = get_current_user_id();

								$customer_acc = intval( $u_id );

								if ( ! empty( $seller ) && ! empty( $customer_acc ) ) {

									$res = delete_user_meta( $customer_acc, 'favourite_seller', $seller );

								} else {

									$res = 0;

								}
							}
					}

					?>

					<div class="updated notice is-dismissible">
						<p> User deleted. </p>
					</div>

					<?php
				}
			}
		}

		/**
		 * Get data for Table.
		 */
		private function table_data() {

			$data = array();

			$current_user = get_current_user_id();

			$customer_list = get_users( array(
				'meta_key'   => 'favourite_seller',
				'meta_value' => $current_user,
			));

			if ( $customer_list ) {
				foreach ( $customer_list as $key => $value ) {
					$data[] = array(
						'id'    => $value->data->ID,
						'name'  => $value->data->display_name,
						'email' => $value->data->user_email,
					);
				}
			}

			return $data;
		}

	}

	global $wpdb;

	$client_info_table = new Seller_Shop_Followers();

	$client_info_table->prepare_items();

	?>

	<div class="wrap">

		<h1 class="wp-heading-inline"><?php echo esc_html__( 'Query list', 'marketplace' ); ?></h1>

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
