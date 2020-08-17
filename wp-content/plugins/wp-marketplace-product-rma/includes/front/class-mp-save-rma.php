<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'MP_Save_Rma_Front' ) )
{
    /**
     *
     */
    class MP_Save_Rma_Front
    {

        function __construct()
        {
            add_action( 'mp_save_rma_request_details', array( $this, 'mp_save_request_rma' ), 1 );
        }

        function mp_save_request_rma( $data )
        {

            global $wpdb;

			$valid = true;

			$dir = wp_upload_dir();

			$error = array();

            if ( !isset($data['mp_policy_agree']) ) {
				wc_add_notice( __('Sorry, Please agree to the policy.', 'marketplace-rma'), 'error' );
				wp_redirect($_SERVER['HTTP_REFERER']);
				exit;
            }
            else if ( ! isset( $data['request_rma_nonce'] ) || ! wp_verify_nonce( $data['request_rma_nonce'], 'request_rma_nonce_action' )  )
            {
				wc_add_notice( __('Sorry, your nonce did not verify.', 'marketplace-rma'), 'error' );
				wp_redirect($_SERVER['HTTP_REFERER']);
				exit;
            }
            else
            {
                if ( isset( $data['mp_item_select'] ) && isset( $data['mp_rma_reason'] ) && isset($data['mp_returned_quantity']) && !empty($data['mp_order_status']) && !empty($data['mp_rma_order']) && !empty($data['mp_resolution_type']) )
                {
                    $order_no   = $data['mp_rma_order'];
                    $items      = $data['mp_item_select'];
                    $reason_ids = $data['mp_rma_reason'];
                    $quantity   = $data['mp_returned_quantity'];
                    $add_info   = strip_tags($data['mp_add_info']);
                    $order_status = strip_tags($data['mp_order_status']);
                    $resolution = strip_tags($data['mp_resolution_type']);
					$con_num    = strip_tags($data['mp_autono']);
					
					$item_data = array(
						'items'    => $items,
						'reason'   => $reason_ids,
						'quantity' => $quantity
					);

					$order = wc_get_order( $order_no );

					$order_items = $order->get_items();

					foreach ($items as $key => $value)
					{
						$product_author[] = get_post_field( 'post_author', $value );
					}

					if ( count(array_unique($product_author)) != 1 )
					{
						return wc_print_notice( 'You can create rma only for one seller'."'".'s product at a time.', 'error' );
					}

                    foreach ($data['mp_item_select'] as $key => $value) {
						if ( empty( $data['mp_returned_quantity'][$key] ) || empty( $data['mp_rma_reason'][$key] ) ) {
	                          $valid = false;
							$error['reason-error-' . $key] = __( 'Quantity/Reason are required fields', 'marketplace-rma' );
						}
						if ( $valid && $data['mp_returned_quantity'][$key] < 0 ) {
							$valid = false;
							$error['quantity-error-' . $key] = __( 'Quantity can&#39;t be -ve.', 'marketplace-rma' );
						}
						if ( $order && $valid ) {
							foreach ($order_items as  $order_items_value ) {
								if ( $key == $order_items_value['product_id'] ) {
									if ( intval( $data['mp_returned_quantity'][$key] ) > $order_items_value['quantity'] ) {
										$valid = false;
										$error['quantity-error-' . $key] = __( 'Quantity can&#39;t exceed '.$order_items_value['quantity'].' for product "'.$order_items_value['name'].'".', 'marketplace-rma' );
									}
								}
							}
						}
					}
					
					if ( $valid ) {

						$customer_id = apply_filters( 'mp_rma_user_id', 'user_id' );

						$imagePaths = array();
						$imagePathString = "no image";

						if ( ! function_exists( 'wp_handle_upload' ) )
						{
							require_once( ABSPATH . 'wp-admin/includes/file.php' );
						}

						$upload_overrides = array( 'test_form' => false );

						if ( isset( $_FILES ) )
						{
							foreach ( $_FILES as $key => $value )
							{
								if ( $value['error'] == UPLOAD_ERR_OK && is_uploaded_file( $value['tmp_name'] ) )
								{
									if ( $value['size'] > 2097152 ) {
										$error['img-error'] = __( 'Image size is too large [<2 MB].', 'marketplace-rma' );
									} elseif ( mime_content_type($value[ 'tmp_name' ]) == "image/jpeg" || mime_content_type($value[ 'tmp_name' ]) == "image/png" )
									{
										$tempArr = wp_handle_upload( $value , $upload_overrides );
										$imagePaths[] = str_replace( $dir[ 'baseurl' ], "",$tempArr['url'] );
									}
									else {
										$error['img-error'] = __( 'Only jpeg/png images are allowed.', 'marketplace-rma' );
									}
								}
							}
						}
						$imagePathString = implode( ";", $imagePaths );
						$table_name = $wpdb->prefix.'mp_rma_requests';

						$sql = $wpdb->insert(
							$table_name,
							array(
								'order_no'  	=> $order_no,
								'customer_id' => $customer_id,
								'seller_id'		=> array_unique($product_author)[0],
								'items'     	=> maybe_serialize($item_data),
								'images_path' => $imagePathString,
								'information' => $add_info,
								'order_status'=> $order_status,
								'resolution'  => $resolution,
								'consignment_num' => $con_num
							)
						);

						if ( $error ) {
							foreach ( $error as $key => $value ) {
								wc_add_notice( $value, 'error' );
							}
						}

						if ( $sql )
						{
							$count = get_post_meta( $order_no, 'order_rma_count', true );
							if ( ! $count )
							{
								$count = 0;
							}
							$count++;
							update_post_meta( $order_no, 'order_rma_count', $count );
							$message = array();
							$message[] = __('From', 'marketplace-rma') . " : " . get_user_by('ID', $customer_id)->user_email;
							$message[] = __('New RMA is generated for order', 'marketplace-rma').' #'.$order_no.".\n\n";
							$data = array(
									'msg'=>$message,
									'email'=>get_option('admin_email'),
							);
							do_action('woocommerce_mp_rma_mail', $data);
							wp_mail( get_option('admin_email'), 'New RMA', $message, $headers );
							wc_add_notice(__('Request placed successfully!', 'marketplace-rma'), 'success');
							wp_redirect( site_url('/my-account/rma'));
							exit;
						}
					} else {
						if ( $error ) {
							foreach ( $error as $key => $value ) {
								wc_print_notice( $value, 'error' );
							}
						}
					}
                }
                else {
                    wc_print_notice( __('Fill all required fields.', 'marketplace-rma'), 'error' );
                }
            }
        }

    }

    new MP_Save_Rma_Front();

}
