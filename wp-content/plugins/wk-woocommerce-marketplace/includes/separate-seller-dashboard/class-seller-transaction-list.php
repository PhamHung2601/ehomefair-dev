<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'Seller_Transaction_List_Table' ) ) {
	class Seller_Transaction_List_Table extends WP_List_Table {
		public function __construct() {
			parent::__construct(
				array(
					'singular' => 'order',
					'plural'   => 'orders',
					'ajax'     => false
				)
			);
		}

		public function prepare_items()
			 {
					 global $wpdb;

					 $columns = $this->get_columns();

					 $sortable = $this->get_sortable_columns();

					 $hidden = $this->get_hidden_columns();

					 $data = ($this->table_data()) ? $this->table_data() : array();

					 $totalitems = count( $data );

					 $perpage = $this->get_items_per_page( 'order_per_page', 20 );

					 $this->_column_headers = array( $columns, $hidden, $sortable );

					 usort( $data, array( $this, 'wk_usort_reorder' ) );

					 $totalpages = ceil( $totalitems/$perpage );

					 $currentPage = $this->get_pagenum();

					 $data = array_slice( $data,( ( $currentPage-1 ) * $perpage ), $perpage );

					 $this->set_pagination_args( array(

						 "total_items" => $totalitems,

						 "total_pages" => $totalpages,

						 "per_page"    => $perpage,

					 ) );

					 $this->items = $data;

			 }

			 function wk_usort_reorder( $a, $b )
			 {

					 $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'txn_id'; //If no sort, default to title

					 $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc

					 $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order

					 return ( $order === 'asc' ) ? $result : -$result; //Send final sort direction to usort

			 }

			 public function get_hidden_columns() {
					 return array();
			 }

			 function get_columns() {
					 $columns = array(
							 'txn_id'     => __( 'Transaction ID', 'marketplace' ),
							 'txn_date'		=> __( 'Date', 'marketplace' ),
							 'txn_total'	=> __( 'Amount', 'marketplace' ),
							 'action'     => __( 'Action', 'marketplace' ),
					 );

					 return $columns;
			 }

			 public function get_sortable_columns() {
					 $sortable_columns = array(
							 'txn_id'       => array( 'order_id', true ),
							 'txn_date'     => array( 'order_date', true ),
					 );

					 return $sortable_columns;
			 }

			 public function column_default( $item, $column_name ) {
				 switch( $column_name ) {
					 case 'txn_id':
					 case 'txn_date':
					 case 'txn_total':
					 case 'action':
						return $item[$column_name];
					 default:
						return print_r( $item, true ) ;
				 }
			 }

			 private function table_data() {
				 global $wpdb, $transaction;

				 $user_id = get_current_user_id();

				 $transactions = $transaction->get( $user_id );

				 $data = array();

					if ( $transactions ) {
						foreach ( $transactions as $key => $value ) {
							$data[] = array(
								'txn_id'  => $value['transaction_id'],
								'txn_date'   => get_date_from_gmt( $value['transaction_date'] ),
								'txn_total' => wc_price( $value['amount'] ),
								'action'  => '<a href="' . admin_url( 'admin.php?page=seller-transaction&action=view&tid=' . $value['id'] ) . '" class="button button-primary">' . __( 'View', 'marketplace' ) . '</a>'
							);
						}
					}

					return $data;
			 }

	 }

	 global $wpdb;

	 $client_info_table = new Seller_Transaction_List_Table();

	 if( isset( $_GET['s'] ) ) {
		 $client_info_table->prepare_items($_GET['s']);
	 } else {
			 $client_info_table->prepare_items();
	 }

	 ?>

	 <div class="wrap">

			 <h1 class="wp-heading-inline"><?php echo __( 'Transactions', 'marketplace' ); ?></h1>

			 <hr>

			 <form method="GET">

					 <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

					 <?php

					 $client_info_table->search_box( 'Search', 'search-id' );

					 $client_info_table->display();

					 ?>

			 </form>

	 </div>

	 <?php
}
