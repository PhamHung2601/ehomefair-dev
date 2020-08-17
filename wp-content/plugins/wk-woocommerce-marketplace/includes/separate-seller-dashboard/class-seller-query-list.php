<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'Seller_Query_List_Table' ) ) {
	/**
	 * Seller query class
	 */
	class Seller_Query_List_Table extends WP_List_Table {

		/**
		 * Class constructor
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
		 * Function prepare items
		 */
		public function prepare_items() {
			global $wpdb;

			$columns = $this->get_columns();

			$sortable = $this->get_sortable_columns();

			$hidden = $this->get_hidden_columns();

			$data = ( $this->table_data() ) ? $this->table_data() : array();

			$totalitems = count( $data );

			$perpage = 20;

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

		/**
		 * Function for sorting columns
		 *
		 * @param int $a col.
		 * @param int $b col.
		 */
		public function wk_usort_reorder( $a, $b ) {

			$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'date'; // If no sort, default to title.

			$order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'desc'; // If no order, default to asc.

			$result = strcmp( $a[ $orderby ], $b[ $orderby ] ); // Determine sort order.

			return ( $order === 'asc' ) ? $result : -$result; // Send final sort direction to usort.

		}

		/**
		 * Get hidden columns
		 */
		public function get_hidden_columns() {
			return array();
		}

		/**
		 * Get columns.
		 */
		public function get_columns() {
			$columns = array(
				'date'    => __( 'Date', 'marketplace' ),
				'subject' => __( 'Subject', 'marketplace' ),
				'message' => __( 'Message', 'marketplace' ),
			);

			return $columns;
		}

		/**
		 * Get sortable columns
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'date'    => array( 'date', true ),
				'subject' => array( 'subject', true ),
			);

			return $sortable_columns;
		}

		/**
		 * Returns columns data.
		 *
		 * @param array  $item data array.
		 * @param string $column_name col name.
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'date':
				case 'subject':
				case 'message':
					return $item[ $column_name ];
				default:
					return print_r( $item, true );
			}
		}

		/**
		 * Function for retriving data from table.
		 */
		private function table_data() {
			global $wpdb;

			$user_id = get_current_user_id();

			if ( isset( $_GET['s'] ) && sanitize_key( $_GET['s'] ) ) {
				$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mpseller_asktoadmin where seller_id = '%d' and subject like %s", $user_id, '%' . $_GET['s'] . '%' );
			} else {
				$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mpseller_asktoadmin where seller_id = '%d'", $user_id );
			}

			$query_result = $wpdb->get_results( $query );

			$data = array();

			if ( $query_result ) {
				foreach ( $query_result as $key => $value ) {
					$data[] = array(
						'date'    => get_date_from_gmt( $value->create_date ),
						'subject' => $value->subject,
						'message' => $value->message,
					);
				}
			}

			return $data;
		}

	}

	global $wpdb;

	$client_info_table = new Seller_Query_List_Table();

	$client_info_table->prepare_items();

	?>

	<div class="wrap">

		<h1 class="wp-heading-inline"><?php echo esc_html__( 'Query list', 'marketplace' ); ?></h1>

		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ask-to-admin&action=add' ) ); ?>" class="page-title-action"><?php echo esc_html__( 'Ask Query', 'marketplace' ); ?></a>

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
