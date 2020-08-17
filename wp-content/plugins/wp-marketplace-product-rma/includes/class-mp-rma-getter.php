<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( 'MP_Rma_Getter' ) )
{

    /**
     *
     */
    class MP_Rma_Getter
    {

        function __construct()
        {
            add_filter( 'mp_rma_user_id', array( $this, 'mp_return_customer_id' ) );
            add_filter( 'mp_rma_id', array( $this, 'mp_return_rma_id' ) );
            add_filter( 'mp_get_rma_data', array( $this, 'mp_return_rma_data' ), 1 );
            add_filter( 'mp_get_rma_conversation', array( $this, 'mp_return_rma_conversation' ), 1 );
            add_filter( 'mp_get_customer_rma_order_id', array( $this, 'mp_return_customer_rma_order_id' ), 1 );
						add_filter( 'mp_get_shipping_labels', array( $this, 'mp_return_shipping_labels' ) );
            add_filter( 'mp_get_rma_reasons', array( $this, 'mp_get_rma_reasons' ), 1 );
						add_filter( 'mp_get_rma_data_by_customer', array( $this, 'mp_return_rma_data_by_customer' ), 10, 2 );
        }

        // return current user id
        function mp_return_customer_id()
        {
            return get_current_user_id();
        }

        // return rma data
        function mp_return_rma_data( $id )
        {
            global $wpdb;
            $table_name = $wpdb->prefix.'mp_rma_requests';
            $mp_data = $wpdb->get_results("Select * from $table_name where id = '$id'");
            return $mp_data;
        }

        // return conversation
        function mp_return_rma_conversation( $id )
        {
            global $wpdb;
            $table_name = $wpdb->prefix.'mp_rma_conversation';
            $pagination = '';
            $query           = "SELECT * FROM $table_name where rma_id = '$id'";
            $total_query     = "SELECT COUNT(1) FROM (${query}) AS combined_table";
            $total           = $wpdb->get_var( $total_query );
            $items_per_page  = 10;
            $page            = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;
            $offset          = ( $page * $items_per_page ) - $items_per_page;
            $result          = $wpdb->get_results( $query . " ORDER BY id DESC LIMIT ${offset}, ${items_per_page}" );
            $totalPage       = ceil($total / $items_per_page);

            if( $totalPage > 1 )
            {
                $pagination = '<div>'.paginate_links(
                    array(
                        'base'      => add_query_arg( 'cpage', '%#%' ),
                        'format'    => '',
                        'prev_text' => __('&laquo;'),
                        'next_text' => __('&raquo;'),
                        'total'     => $totalPage,
                        'current'   => $page
                    )
                ).'</div>';
            }

            return array(
              'data' => $result,
              'count'=> $pagination
            );

        }

        // return rma id
        function mp_return_rma_id()
        {
            global $wp_query;
            if ( isset( $_GET['rid'] ) )
            {
                $rma_id = $_GET['rid'];
            }
            else
            {
                $rma_id = get_query_var('pid');
            }
            return $rma_id;
        }

        //return customer rma's order id
        function mp_return_customer_rma_order_id( $id )
        {
            global $wpdb;
            $return = $sql1 = array();
            $sql = $wpdb->get_results( "Select order_no from {$wpdb->prefix}mp_rma_requests where customer_id = '$id'", ARRAY_A );

            foreach ($sql as $key => $value) {
                $sql1[] = $value['order_no'];
            }
            foreach (array_unique($sql1) as $key => $value) {
                $product_author = array();
                $order = wc_get_order( $value );
                if( !empty( $order ) ) {

                    $items = $order->get_items();
                    foreach ($items as $k => $val)
                    {
                            $product_author[] = get_post_field( 'post_author', $val->get_product_id() );
                    }

                    $count = get_post_meta( $value, 'order_rma_count', true ) ? get_post_meta( $value, 'order_rma_count', true ) : '0';
                    if ( count(array_unique($product_author)) == $count)
                    {
                            $return[]	= $value;
                    }
                }
            }
            return $return;
        }

        // return shpping labels
        function mp_return_shipping_labels( $rma_id )
        {
						global $wpdb;
						$sql = $wpdb->get_results( "Select seller_id from {$wpdb->prefix}mp_rma_requests where id = '$rma_id'", ARRAY_A );
						$author = $sql[0]['seller_id'];
						$user_id = apply_filters( 'mp_rma_user_id', 'user_id' );
						$users = array( $author, $user_id );

						if ( $author == $user_id )
						{
								$paths = get_user_meta( $user_id, 'mp_rma_shipping_label_path' ) ? get_user_meta( $user_id, 'mp_rma_shipping_label_path', true ) : array();
						}
						else
						{
								foreach ($users as $key => $value)
								{
										$paths[] = get_user_meta( $value, 'mp_rma_shipping_label_path' ) ? get_user_meta( $value, 'mp_rma_shipping_label_path', true ) : array();
								}
								$paths = array_merge( $paths[0], $paths[1] );
						}

            return $paths;
        }

				// return rma reasons
				function mp_get_rma_reasons( $user_id )
				{
						global $wpdb;
						$sql = $wpdb->get_results( "Select * from {$wpdb->prefix}mp_rma_reasons where user_id = '$user_id'", ARRAY_A );
            return $sql;
				}

				// return rma data by customer id
				function mp_return_rma_data_by_customer($user_id, $page_query)
				{
						global $wpdb;
						$table_name = $wpdb->prefix . 'mp_rma_requests';

                        $pagination = $rma_status_filter_query = '';
                        
						if( !empty( $_REQUEST['rma-select-status'] ) ) {
							$rma_status = $_REQUEST['rma-select-status'];
							$rma_status_filter_query = "AND rma_status='$rma_status'";
						}

						$query = "SELECT * from $table_name where customer_id = '$user_id' $rma_status_filter_query";

						$total_query     = "SELECT COUNT(1) FROM (${query}) AS combined_table";
						$total           = $wpdb->get_var( $total_query );
						$items_per_page  = get_option('posts_per_page');
						if( is_numeric($page_query) ) {
								$page = $page_query;
						} else {
								$page = 1;
						}
						$offset          = ( $page * $items_per_page ) - $items_per_page;
						$result          = $wpdb->get_results( $query . " ORDER BY id DESC LIMIT ${offset}, ${items_per_page}" );
						$totalPage       = ceil($total / $items_per_page);
						$big 						 = 999999999;

						if( $totalPage > 1 )
						{
								$pagination = '<div class="woocommerce-pagination">'.paginate_links(
										array(
												'base'      => str_replace( $big, '%#%', wc_get_endpoint_url('rma') . $big ),
												'format'    => '/page/%#%',
												'prev_text' => __('&laquo;'),
												'next_text' => __('&raquo;'),
												'total'     => $totalPage,
												'current'   => $page,
												'type'			=> 'list'
										)
								).'</div>';
						}

						return array(
							'data' => $result,
							'count'=> $pagination
						);
				}

    }

    new MP_Rma_Getter();

}
