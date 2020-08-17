<?php
/**
 * This file handles list for seller reviews.
 *
 * @package Woocommerce Marketplace
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Seller review list table.
 */
class Seller_Review_List extends WP_List_Table {
	/**
	 * Seller Review List
	 *
	 * @var array review List.
	 */
	public function __construct() {
			parent::__construct(
				array(
					'singular' => 'Seller Review List',
					'plural'   => 'Seller Review List',
					'ajax'     => false,
				)
			);
	}

	/**
	 * Handles all list functions.
	 */
	public function prepare_items() {
		global $wpdb;
		$columns  = $this->get_columns();
		$sortable = $this->get_sortable_columns();
		$this->process_bulk_action();
		$hidden     = $this->get_hidden_columns();
		$data       = $this->table_data();
		$totalitems = count( $data );
		$user       = get_current_user_id();
		$screen     = get_current_screen();
		$perpage    = $this->get_items_per_page( 'product_per_page', 20 );

		$this->_column_headers = array( $columns, $hidden, $sortable );

		if ( empty( $per_page ) || $per_page < 1 ) {
				$per_page = $screen->get_option( 'per_page', 'default' );
		}

		/**
		 * Handles sort order of data.
		 *
		 * @param array $a Default Order.
		 * @param array $b Result Order.
		 */
		function usort_reorder( $a, $b ) {
			$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'created'; // If no sort, default to title.
			$order   = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'desc'; // If no order, default to asc.

			$result = strcmp( $a[ $orderby ], $b[ $orderby ] ); // Determine sort order.

			return ( 'asc' === $order ) ? $result : -$result; // Send final sort direction to usort.
		}

			usort( $data, 'usort_reorder' );

			$totalpages = ceil( $totalitems / $perpage );

			$currentpage = $this->get_pagenum();

			$data = array_slice( $data, ( ( $currentpage - 1 ) * $perpage ), $perpage );

			$this->set_pagination_args(array(
				'total_items' => $totalitems,
				'total_pages' => $totalpages,
				'per_page'    => $perpage,
			));

			$this->items = $data;
	}

	/**
	 * Define the columns that are going to be used in the table
	 *
	 * @return array $columns, the array of columns to use with the table
	 */
	public function get_columns() {
		$columns = array(
			'cb'             => '<input type="checkbox" />', // Render a checkbox instead of text.
			'seller_id'      => esc_html__( 'Shop Name', 'marketplace' ),
			'value'          => esc_html__( 'Value Rating', 'marketplace' ),
			'price'          => esc_html__( 'Price Rating', 'marketplace' ),
			'quality'        => esc_html__( 'Quality Rating', 'marketplace' ),
			'review_summary' => esc_html__( 'Summary', 'marketplace' ),
			'review_desc'    => esc_html__( 'Description', 'marketplace' ),
			'status'         => esc_html__( 'Status', 'marketplace' ),
			'created'        => esc_html__( 'Created', 'marketplace' ),
		);
		return $columns;
	}

	/**
	 * Default Column name with data goes here.
	 *
	 * @param array  $item Column Array.
	 * @param string $column_name Individual Column Slug.
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'seller_id':
			case 'value':
			case 'price':
			case 'quality':
			case 'review_summary':
			case 'review_desc':
			case 'status':
			case 'created':
				return $item[ $column_name ];
			default:
				return print_r( $item, true );
		}
	}

	/**
	 * Decide which columns to activate the sorting functionality on
	 *
	 * @return array $sortable, the array of columns that can be sorted by the user
	 */
	public function get_sortable_columns() {
			$sortable = array(
				'seller_id'      => array( 'seller_id', true ),
				'value'          => array( 'value', true ),
				'price'          => array( 'price', true ),
				'quality'        => array( 'quality', true ),
				'review_summary' => array( 'review_summary', true ),
				'review_desc'    => array( 'review_desc', true ),
				'status'         => array( 'status', true ),
				'created'        => array( 'created', true ),
			);
		return $sortable;
	}

	/**
	 * Hidden Columns.
	 */
	public function get_hidden_columns() {
			return array();
	}

	/**
	 * Checkbox column data.
	 *
	 * @param array $item column data.
	 */
	public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" id="reviewid_%s" name="reviewid[]" value="%s" />', $item['review_id'], $item['review_id'] );
	}

	/**
	 * Table Data.
	 */
	private function table_data() {
		global $wpdb;

		$result = $wpdb->get_results( "SELECT * from {$wpdb->prefix}mpfeedback order by ID desc" );

		$data = array();

		$table_name = $wpdb->prefix . 'mpfeedback_meta';

		foreach ( $result as $key => $value ) {
			if ( $value->status ) {
					$status = __( 'Approved', 'marketplace' );
			} else {
					$status = __( 'Disapproved', 'marketplace' );
			}

			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->users WHERE ID = %d", $value->seller_id ) );

			if ( $count == 1 ) {
				$data[] = array(
					'review_id'      => $value->ID,
					'seller_id'      => '<a href="' . esc_url( admin_url( 'admin.php?page=sellers&action=set&tab=details&sid=' . $value->seller_id ) ) . '"><strong>' . ucfirst( get_user_meta( $value->seller_id, 'shop_name', true ) ) . '(#' . $value->seller_id . ') </strong></a>',
					'value'          => $value->value_r . ' / 5',
					'price'          => $value->price_r . ' / 5',
					'quality'        => $value->quality_r . ' / 5',
					'review_summary' => stripslashes( $value->review_summary ),
					'review_desc'    => stripslashes( $value->review_desc ),
					'status'         => $status,
					'created'        => $value->review_time,
				);
			}
		}

			return $data;
	}

	/**
	 * Column created.
	 *
	 * @param array $item item array.
	 */
	public function column_created( $item ) {

		if ( get_option( 'timezone_string' ) ) {
			$timezone = get_option( 'timezone_string' );
		} elseif ( get_option( 'gmt_offset' ) ) {
			$timezone = get_option( 'gmt_offset' );
		} else {
			$timezone = 'UTC';
		}

		$original_datetime = $item['created'];
		$original_timezone = new DateTimeZone( 'UTC' );

		$datetime = new DateTime( $original_datetime, $original_timezone );

		$target_timezone = new DateTimeZone( $timezone );
		$datetime->setTimeZone( $target_timezone );
		$triggeron = $datetime->format( 'Y-m-d H:i:s' );
		return sprintf( '%s', $triggeron );
	}

	/**
	 * List bulk action.
	 */
	public function get_bulk_actions() {
			$actions = array(
				'approve'    => __( 'Approve', 'marketplace' ),
				'disapprove' => __( 'Disapprove', 'marketplace' ),
			);
			return $actions;
	}

	/**
	 * Process bulk action.
	 */
	public function process_bulk_action() {
		if ( $this->current_action() ) {
			global $wpdb;

			$table_name = $wpdb->prefix . 'mpfeedback';

			if ( $this->current_action() === 'approve' ) {
				$status = 1;
			}

			if ( $this->current_action() === 'disapprove' ) {
					$status = 0;
			}

			if ( isset( $_POST['reviewid'] ) && is_array( $_POST['reviewid'] ) ) {
				foreach ( $_POST['reviewid'] as $value ) {
					$wpdb->update(
						$table_name, array(
							'status' => $status,
						), array(
							'ID' => $value,
						), array(
							'%d',
						), array(
							'%d',
						)
					);
				}
					?>
				<div id="message" class="updated notice is-dismissible"><p><?php echo esc_html__( 'Status updated to "' ) . esc_html( ucfirst( $this->current_action() ) ) . esc_html__( '" for' ) . count( $_POST['reviewid'] ) . esc_html__( ' review(s).' ); ?></div>
					<?php
			} else {
					?>
					<div id="message" class="error notice is-dismissible"><p><?php echo esc_html__( 'Please select review(s).', 'marketplace' ); ?></div>
					<?php
			}
		}
	}
}

$seller_review_list = new Seller_Review_List();

printf( '<div class="wrap" id="product-list-table"><h1 class="wp-heading-inline">%s</h1>', esc_html__( 'Manage Feedback', 'marketplace' ) );

		$seller_review_list->prepare_items();

		?>
		<form method="post">
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
			<?php
				$seller_review_list->display();
			?>
		</form>
		<?php

		echo '</div>';
