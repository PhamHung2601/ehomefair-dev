<?php
/**
 * File for seller queries.
 *
 * @package  wk-woocommerce-marketplace/includes/admin/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'Mp_Seller_Query' ) ) {

	/**
	 * Class for listing seller queries.
	 */
	class Mp_Seller_Query extends WP_List_Table {

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

			$data = $this->table_data();

			$totalitems = count( $data );

			$perpage = 10;

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

			$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'date'; // If no sort, default to title.

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

		/**
		 * Get columns.
		 */
		public function get_columns() {
			$columns = array(
				'seller'  => __( 'Seller', 'marketplace' ),
				'date'    => __( 'Date', 'marketplace' ),
				'subject' => __( 'Subject', 'marketplace' ),
				'message' => __( 'Message', 'marketplace' ),
				'action'  => __( 'Action', 'marketplace' ),
			);

			return $columns;
		}

		/**
		 * Column Seller.
		 *
		 * @param array $item item array.
		 */
		public function column_seller( $item ) {
			return sprintf( '%s', get_user_by( 'id', $item['id'] )->display_name );
		}

		/**
		 * Column Action.
		 *
		 * @param array $item item array.
		 */
		public function column_action( $item ) {
			return sprintf( $item['action'] );
		}

		/**
		 * Add thickbox.
		 *
		 * @param object $q_data query data.
		 */
		public function thickbox_content( $q_data ) {
			?>
			<div id="meta-box-<?php echo esc_attr( $q_data->id ); ?>" class="meta-bx" style="display:none" >
				<h2><?php echo esc_html__( 'Reply to', 'marketplace' ); ?>  <?php echo get_user_by( 'id', $q_data->seller_id )->user_login; ?> </h2>
				<table style="width:100%">
					<tr>
						<td><label><h4><b> <?php echo esc_html( 'Subject', 'marketplace' ); ?> </b></h4></label></td>
						<td colspan="2"><span> <?php echo esc_html( $q_data->subject ); ?> </span></td>
					</tr>
					<tr>
						<td><label><h4><b> <?php echo esc_html( 'Query', 'marketplace' ); ?> </b></h4></label></td>
						<td colspan="2"><span> <?php echo esc_html( $q_data->message ); ?> </span></td>
					</tr>
				</table>
				<div class="reply-mes">
					<label><h3> <?php echo esc_html( 'Reply Message', 'marketplace' ); ?> </h3></label>
					<textarea name="reply" class="admin_msg_to_seller" id="relpy" style="white-space: pre-wrap; margin:10px;width:90%" rows="5" cols="60" ></textarea>
				</div>
				<button class="button-primary seller-query-revert" data-qid="<?php echo intval( $q_data->id ); ?>" ><?php echo esc_html( 'Send', 'marketplace' ); ?></button>
			</div>
			<?php
		}

		/**
		 * Get sortable columns.
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'date'    => array( 'date', true ),
				'subject' => array( 'subject', true ),
				'action'  => array( 'action', true ),
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
				case 'date':
				case 'subject':
				case 'message':
					return $item[ $column_name ];
				default:
					return print_r( $item, true );
			}
			return $data;
		}

		/**
		 * Get data for Table.
		 */
		private function table_data() {
			global $wpdb;

			$user_id = get_current_user_id();

			if ( isset( $_GET['s'] ) && sanitize_key( $_GET['s'] ) ) {
				$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mpseller_asktoadmin where subject like %s", '%' . $_GET['s'] . '%' );
			} else {
				$query = "SELECT * FROM {$wpdb->prefix}mpseller_asktoadmin";
			}

			$query_result = $wpdb->get_results( $query );

			$data = array();

			if ( $query_result ) {
				foreach ( $query_result as $key => $value ) {

					$action = '';
					$qury   = $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}mpseller_asktoadmin_meta where id = %d", $value->id );

					$res = $wpdb->get_results( $qury );

					if ( $res ) {
						$action = '<span><b>' . __( 'Replied', 'marketplace' ) . '<b></span>';
					} else {
						add_thickbox();
						$this->thickbox_content( $value );
						$action = '<a href="#TB_inline?width=600&height=400&inlineId=meta-box-' . $value->id . '" title="' . __( 'Reply', 'marketplace' ) . '" class="thickbox button button-primary">' . __( 'Reply', 'marketplace' ) . '</a>';
					}

					$data[] = array(
						'id'      => $value->seller_id,
						'date'    => get_date_from_gmt( $value->create_date ),
						'subject' => $value->subject,
						'message' => $value->message,
						'action'  => $action,
					);
				}
			}

			return $data;
		}

	}

	global $wpdb;

	$client_info_table = new Mp_Seller_Query();

	$client_info_table->prepare_items();

	?>

	<div class="wrap">

		<h1 class="wp-heading-inline"><?php echo esc_html__( 'Query list', 'marketplace' ); ?></h1>

		<hr>

		<form method="GET">

			<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />

			<?php

			$client_info_table->display();

			?>

		</form>

	</div>

<?php
}
