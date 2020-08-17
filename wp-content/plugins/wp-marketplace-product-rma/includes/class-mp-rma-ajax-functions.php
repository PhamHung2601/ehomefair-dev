<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'MP_Rma_Ajax' ) )
{
    /**
     *   RMA: Ajax functions front-end
     */
    class MP_Rma_Ajax
    {

        function __construct()
        {
            // return order items
            add_action( 'wp_ajax_nopriv_mp_rma_get_order_items', array( $this, 'mp_rma_get_order_items' ) );
            add_action( 'wp_ajax_mp_rma_get_order_items', array( $this, 'mp_rma_get_order_items' ) );

            // update rma status
            add_action( 'wp_ajax_nopriv_mp_update_rma_status', array( $this, 'mp_update_rma_status' ) );
            add_action( 'wp_ajax_mp_update_rma_status', array( $this, 'mp_update_rma_status' ) );

            // check product author
            add_action( 'wp_ajax_nopriv_mp_check_product_author', array( $this, 'mp_check_product_author' ) );
            add_action( 'wp_ajax_mp_check_product_author', array( $this, 'mp_check_product_author' ) );

        }

        // return order items
        function mp_rma_get_order_items()
        {
            if ( !isset( $_POST[ 'nonce' ] ) && empty( $_POST[ 'nonce' ] ) )
            {
              wp_die(__('Security check failed.', 'marketplace-rma'));
            }
            else
            {

                $nonce = $_POST[ 'nonce' ];

                if ( ! wp_verify_nonce( $nonce, 'rma_ajax_nonce' ) )
                {
                wp_die(__('Security check failed.', 'marketplace-rma'));
                }
                else
                {
                    global $wpdb;
                    $order_id = $_POST[ 'order_id' ];
                    $order = wc_get_order( $order_id );
                    $items = $order->get_items();

                    $order_delivery_status = $order->get_status();

                    $order_delivery_status_html = '<option value="'.esc_attr($order_delivery_status).'">'.esc_html__( ucfirst($order_delivery_status), 'marketplace-rma' ).'</option>';

                    if( $order_delivery_status == 'completed' ) {
                        $resolution_type_html = '<option value="">'.__('--Select--', 'marketplace-rma').'</option><option value="refund">'.__('Refund', 'marketplace-rma').'</option><option value="exchange">'.__('Exchange', 'marketplace-rma').'</option>';
                    }
                    else {
                        $resolution_type_html = '<option value="">'.__('--Select--', 'marketplace-rma').'</option><option value="refund">'.__('Refund', 'marketplace-rma').'</option>';
                    }

                    $response = '';
                    $item_requested = array();
                    $sql = $wpdb->get_results( "Select items from {$wpdb->prefix}mp_rma_requests where order_no = '$order_id'", ARRAY_A );
                    if ( $sql ) {
                        foreach ($sql as $key => $value) {
                            foreach ( maybe_unserialize($value['items'])['items'] as $k => $val ) {
                                $item_requested[] = $val;
                            }
                        }
                    }

                    foreach ( $items as $item )
                    {
                        $pro_id = !empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'];
                        
                        if ( !in_array( $pro_id, $item_requested ) ) {
                            $product_author = get_post_field( 'post_author', $pro_id );
                            $response .= '<tr>
                                <td>'.$item['name'].'</td>
                                <td class="text-center">'.$item['quantity'].'</td>
                                <td><input type="checkbox" class="check-item" name="mp_item_select['.$pro_id.']" value="'.$pro_id.'" /></td>
                                <td>
                                    <select name="mp_rma_reason['.$pro_id.']" class="full-width form-control reason-select" disabled>';
                                    foreach ($this->mp_get_seller_rma_reason( $product_author ) as $key => $value) {
                                        $response .='<option value="'.$value->id.'">'.$value->reason.'</option>';
                                    }
                                    $response .= '</select>
                                </td>
                                <td><input type="number" min="1" max="'.$item['quantity'].'" class="full-width form-control item-qty" name="mp_returned_quantity['.$pro_id.']" disabled /></td>
                            </tr>';
                        }
                    }

                    $result = array(
                        'response' => $response,
                        'order_delivery_status_html' => $order_delivery_status_html,
                        'resolution_type_html' => $resolution_type_html,
                    );

                    wp_send_json( $result );

                    // echo $response;

                }

            }
            wp_die();
        }

				// return rma reasons per Seller
				function mp_get_seller_rma_reason( $author_id )
				{
						global $wpdb;
						$table_name = $wpdb->prefix.'mp_rma_reasons';
						$wk_posts = $wpdb->get_results("Select * from $table_name where status = 'enabled' and user_id = '$author_id'");
						return $wk_posts;
				}

        // update rma status
        function mp_update_rma_status()
        {
            if ( !isset( $_POST[ 'nonce' ] ) && empty( $_POST[ 'nonce' ] ) )
            {
                wp_die(__('Security check failed.', 'marketplace-rma'));
            }
            else
            {

                $nonce = $_POST[ 'nonce' ];

                if ( ! wp_verify_nonce( $nonce, 'rma_ajax_nonce' ) )
                {
                  wp_die(__('Security check failed.', 'marketplace-rma'));
                }
                else
                {
                    global $wpdb;
                    $table = $wpdb->prefix.'mp_rma_requests';
                    $rma_id = $_POST['mp_rma_id'];
                    $mp_data = apply_filters( 'mp_get_rma_data', $rma_id );
                    $user_id = apply_filters( 'mp_rma_user_id', 'user_id' );
                    $sql = $wpdb->update( $table,
                        array(
                            'rma_status'  => $_POST['rma_status']
                        ),
                        array(
                            'id'  => $rma_id
                        )
                    );

                    $rma_stat = array(
                        'pending' => esc_html__('pending', 'marketplace-rma'),
                        'cancelled' => esc_html__('cancelled', 'marketplace-rma'),
                    );
                    
                    $message = array();
                    $message[] = __('From', 'marketplace-rma')." :".get_user_by( 'ID', $user_id )->user_email;
                    $message[] = __('RMA for order', 'marketplace-rma').' #'.$mp_data[0]->order_no.' '.sprintf(__('has been set to %s by customer', 'marketplace-rma'), $rma_stat[$_POST['rma_status']])."\n";
                    $message[] = __('RMA ID', 'marketplace-rma').': '.$rma_id."\n\n";
                    $data = array(
                        'msg'=>$message,
                        'email'=>get_option('admin_email'),
                    );
                    do_action('woocommerce_mp_rma_mail', $data);

                    echo $sql;
                }

            }

            wp_die();

        }

        // check product author
        function mp_check_product_author()
        {
            $ids = explode( ",", $_POST['product_id'] );
            $this_id = $_POST['this_id'];
            foreach ($ids as $key => $value)
            {
                $product_author[] = get_post_field( 'post_author', $value );
            }
            $a = count(array_unique($product_author));

            if ( $a == 1 ) :
                    echo 'true';
            else :
                    echo 'false';
            endif;
            wp_die();
        }

    }

    new MP_Rma_Ajax();

}
