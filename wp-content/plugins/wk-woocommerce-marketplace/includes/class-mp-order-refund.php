<?php
/**
 * Order refunds related functions and actions.
 *
 * @author   Webkul
 * @category Admin
 * @package  webkul/Classes
 * @version     4.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MP_Order_Refund' ) ) {
	/**
	 * MP_Order_Refund Class.
	 */
	class MP_Order_Refund {

		protected $refund_args = array();
		protected $user_id = array();
		protected $wpdb = '';
		protected $mporders_meta_table = '';
		protected $mpcommision_table = '';

		public function __construct( $args = array() ) {

			global $wpdb;

			$this->wpdb = $wpdb;

			$this->refund_args = $args;

			$this->user_id = get_current_user_id();

			$this->mporders_meta_table = $this->wpdb->prefix . 'mporders_meta';

			$this->mpcommision_table = $this->wpdb->prefix . 'mpcommision';

		}

		public function wkmp_process_refund() {
			
			// Create the refund object.
			$refund = wc_create_refund( $this->refund_args );

			if ( !empty( $refund ) && is_wp_error( $refund ) ) {

				if( is_admin() ) {
					?>
					<div class='notice notice-error is-dismissible'>
						<p><?php echo $refund->get_error_message(); ?></p>
					</div>
					<?php
				} else {
					wc_print_notice( $refund->get_error_message(), 'error' );
				}

			} else {
				
				$this->wkmp_set_seller_order_refund_data();

				$order = wc_get_order( $this->refund_args[ 'order_id' ] ) ;

				$seller_email = $this->wkmp_get_seller_email();

				do_action( 'woocommerce_seller_order_refunded', $order->get_items(), $seller_email, $this->refund_args[ 'amount' ]);

				if( is_admin() ) {
				?>
				<div class='notice notice-success is-dismissible'>
					<p><?php echo esc_html__( 'Refunded successfully.', 'marketplace' ); ?></p>
				</div>
				<?php
				} else {
				wc_print_notice( esc_html__( 'Refunded successfully.', 'marketplace' ), 'success' );
				}

			}

		}

		public function wkmp_set_refund_args( $args = array() ) {

			$this->refund_args = $args;

		}

		public function wkmp_get_seller_email() {

			return get_userdata( $this->user_id )->user_email;

		}

		public function wkmp_set_seller_order_refund_data() {

			$order_id = $this->refund_args[ 'order_id' ];

			$this->user_id = apply_filters( 'wkmp_modify_order_refund_user_id', $this->user_id, $order_id );

			$seller_order_refund_data = $this->wkmp_get_seller_order_refund_data( $order_id );

			if( empty( $seller_order_refund_data ) ) {
					
				$seller_order_refund_data = array(
					'line_items' 	  => $this->refund_args[ 'line_items' ],
					'refunded_amount' => round( $this->refund_args[ 'amount' ], 2 )
				);

				$this->wpdb->insert(
					
					$this->mporders_meta_table,
					array(
						'seller_id'  => $this->user_id,
						'order_id'   => $order_id,
						'meta_key'   => '_wkmp_refund_status',
						'meta_value' => maybe_serialize( $seller_order_refund_data ),
					),
					array( '%d', '%d', '%s', '%s' )

				);

			} else {

				$seller_order_refund_data = maybe_unserialize( $seller_order_refund_data );

				foreach ( $this->refund_args[ 'line_items' ] as $line_item_id => $line_items ) {

					if( array_key_exists( $line_item_id, $seller_order_refund_data[ 'line_items' ] ) ) {

						$seller_order_refund_data[ 'line_items' ][ $line_item_id ][ 'qty' ] += $line_items[ 'qty' ];
						$seller_order_refund_data[ 'line_items' ][ $line_item_id ][ 'refund_total' ] += round( $line_items[ 'refund_total' ], 2 );

					} else {

						$seller_order_refund_data[ 'line_items' ][ $line_item_id ][ 'qty' ] = $line_items[ 'qty' ];
						$seller_order_refund_data[ 'line_items' ][ $line_item_id ][ 'refund_total' ] = round( $line_items[ 'refund_total' ], 2 );

					}

				}

				$seller_order_refund_data[ 'refunded_amount' ] += round( $this->refund_args[ 'amount' ], 2 );
				
				$this->wpdb->update(
					
					$this->mporders_meta_table,
					array(
						'meta_value' => maybe_serialize( $seller_order_refund_data ),
					),
					array(
						'seller_id'  => $this->user_id,
						'order_id'   => $order_id,
						'meta_key'   => '_wkmp_refund_status',
					),
					array( '%s' ),
					array( '%d', '%d', '%s' )
					
				);

			}

			$this->wkmp_update_refund_data_in_seller_sales();

		}

		public function wkmp_update_refund_data_in_seller_sales() {

			$sales_data = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT seller_total_ammount, paid_amount, total_refunded_amount FROM $this->mpcommision_table WHERE seller_id=%d", $this->user_id ), ARRAY_A );

			$order_id = $this->refund_args[ 'order_id' ];

			$paid_status = $this->wpdb->get_var( $this->wpdb->prepare( "SELECT meta_value FROM {$this->wpdb->prefix}mporders_meta WHERE seller_id=%d AND order_id=%d and meta_key=%s", $this->user_id, $order_id, 'paid_status' ) );

			$exchange_rate = get_post_meta( $order_id, 'mpmc_exchange_rate', true );

            $exchange_rate = !empty( $exchange_rate ) ? $exchange_rate : 1;

			$refunded_amount = $this->refund_args[ 'amount' ] / $exchange_rate;

			$seller_total_ammount = floatval( $sales_data['seller_total_ammount'] - round( $refunded_amount, 2 ) );
			
			$total_refunded_amount = floatval( $sales_data['total_refunded_amount'] + round( $refunded_amount, 2 ) );
			
			if( $paid_status == 'paid' ) {
				
				$paid_amount = floatval( $sales_data['paid_amount'] - round( $refunded_amount, 2 ) );

				$this->wpdb->update(
						
					$this->mpcommision_table,
					array(
						'seller_total_ammount'  => $seller_total_ammount,
						'paid_amount'           => $paid_amount,
						'total_refunded_amount' => $total_refunded_amount,
					),
					array(
						'seller_id'  => $this->user_id,
					),
					array( '%f', '%f', '%f' ),
					array( '%d' )
					
				);

			} else {

				$this->wpdb->update(
						
					$this->mpcommision_table,
					array(
						'seller_total_ammount'  => $seller_total_ammount,
						'total_refunded_amount' => $total_refunded_amount,
					),
					array(
						'seller_id'  => $this->user_id,
					),
					array( '%f', '%f', '%f' ),
					array( '%d' )
					
				);

			}

		}

		public function wkmp_get_seller_order_refund_data( $order_id ) {

			$seller_order_refund_data = $this->wpdb->get_var( $this->wpdb->prepare( "SELECT meta_value FROM $this->mporders_meta_table WHERE seller_id=%d AND order_id=%d AND meta_key=%s", $this->user_id, $order_id, '_wkmp_refund_status' ) );

			return !empty( $seller_order_refund_data ) ? maybe_unserialize( $seller_order_refund_data ) : array();

		}

	}

}