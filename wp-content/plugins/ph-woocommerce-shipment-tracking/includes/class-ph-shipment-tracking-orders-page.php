<style type="text/css">
	
	.ph_order_packages, .ph_tracking_history
	{
		color: #3580b2 !important;
		cursor: pointer;
		padding: 10px;
	}

	.ph_order_tracking * tr th
	{
		padding: 15px;
		background-color: aliceblue;
		font-weight: bold;
		font-style: italic;
	}

	.shipment-tracking_page_pluginhive-orders tr th {

		padding: 1em !important;
		width: 20ch;
	}

	.shipment-tracking_page_pluginhive-orders tr td {
		padding: 1.5em;
		vertical-align: middle;
	}

	.shipment-tracking_page_pluginhive-orders tr td span{
		display: block;
		width: 55%;
		padding: 0 1px !important;
		text-align: center;
		line-height: 30px;
	}

	.ph_out_for_delivery_status {
		background: #3f9dbc;
		color: #fff;
		border-radius: 4px;
	}

	.ph_canceled_status {
		background: #e5e5e5;
		color: #777;
		border-radius: 4px;
	}

	.ph_exception_status {
		background: #eba3a3;
		color: #761919;
		border-radius: 4px;
	}

	.ph_delivered_status {
		background: #5b841b;
		color: #fff;
		border-radius: 4px;
	}

	.ph_transit_status {
		background: #c6e1c6;
		color: #5b841b;
		border-radius: 4px;
	}

	.ph_initial_status {
		background: #f8dda7;
		color: #94660c;
		border-radius: 4px;
	}

	.ph-tracking-data {
		float: right;
		width: 16px;
		padding: 20px 4px 4px 4px;
		height: 0;
		overflow: hidden;
		position: relative;
		border: 2px solid transparent;
		border-radius: 4px;

	}

	.ph-tracking-data:before {
		font-family: WooCommerce;
		speak: none;
		font-weight: 400;
		font-variant: normal;
		text-transform: none;
		line-height: 1 !important;
		margin: 0;
		text-indent: 0;
		position: absolute;
		top: 0 !important;
		left: 0;
		width: 100%;
		height: 100%;
		text-align: center;
		content: "";
		line-height: 16px;
		font-size: 14px;
		vertical-align: middle;
		top: 4px;
		border: none;
	}

</style>
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class PH_Live_Order_Tracking extends WP_List_Table {
	
	public function __construct()
	{
		parent::__construct( array() );
	}
	
	public function get_columns()
	{
		$columns = array(
			
			'order' 			=> __( 'Order', 'Column label', 'woocommerce-shipment-tracking' ),
			'carrier' 			=> __( 'Carrier', 'Column label', 'woocommerce-shipment-tracking' ),
			'status' 			=> __( 'Status', 'Column label', 'woocommerce-shipment-tracking' ),
			'destination' 		=> __( 'Destination', 'Column label', 'woocommerce-shipment-tracking' ),
			'expected_delivery' => __( 'Expected Delivery', 'Column label', 'woocommerce-shipment-tracking' ),
		);
		return $columns;
	}
	
	public function views() {

		$views = $this->get_views();
		
		/**
		 * Filters the list of available list table views.
		 *
		 * The dynamic portion of the hook name, `$this->screen->id`, refers
		 * to the ID of the current screen, usually a string.
		 *
		 * @since 3.5.0
		 *
		 * @param string[] $views An array of available list table views.
		 */
		$views = apply_filters( "views_{$this->screen->id}", $views );

		if ( empty( $views ) ) {
			return;
		}

		$this->screen->render_screen_reader_content( 'heading_views' );

		echo "<ul class='subsubsub'>\n";
		foreach ( $views as $class => $view ) {
			$views[ $class ] = "\t<li class='$class'><a>$view</a>";
		}
		echo implode( " |</li>\n", $views ) . "</li>\n";
		echo '</ul>';
	}

	protected function get_views() {

		$store_id 			= get_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_ph_store_id' );
		$api_object			= new PH_Shipment_Tracking_API();

		$all_orders 		= '';
		$initial 			= '';
		$in_transit 		= '';
		$delivered 			= '';
		$out_for_delivery 	= '';
		$exceptions 		= '';

		$order_count 		= array();
		$views 				= array();

		if( !empty($store_id) && is_object($api_object) )
		{
			$order_count = $api_object->ph_shipmint_tracking_get_order_count();
		}

		if( is_array($order_count) && isset($order_count['Count']) && !empty($order_count['Count']) )
		{
			$all_orders 		= isset($order_count['Count']['allOrders']) ? $order_count['Count']['allOrders'] : '';
			$initial 			= isset($order_count['Count']['initial']) ? $order_count['Count']['initial'] : '';
			$in_transit 		= isset($order_count['Count']['inTransit']) ? $order_count['Count']['inTransit'] : '';
			$delivered 			= isset($order_count['Count']['delivered']) ? $order_count['Count']['delivered'] : '';
			$out_for_delivery 	= isset($order_count['Count']['outForDelivery']) ? $order_count['Count']['outForDelivery'] : '';
			$exceptions 		= isset($order_count['Count']['exceptions']) ? $order_count['Count']['exceptions'] : '';
		}

		$current = ( isset($_REQUEST['order_status']) && !empty($_REQUEST['order_status']) ) ? $_REQUEST['order_status'] : 'all_orders';

		$all_orders_class 		= ( $current == 'all_orders' ) ? 'current' :'';
		$all_url 				= remove_query_arg( array('order_status', 'paged') );
		$views['all_orders'] 	= "<a href=".$all_url." class=".$all_orders_class." >All Orders </a>(".$all_orders.")";

		$initial_class 			= ( $current == 'initial' ) ? 'current' :'';
		$initial_url 			= add_query_arg('order_status','initial');
		$initial_url 			= remove_query_arg('paged', $initial_url);
		$views['initial'] 		= "<a href=".$initial_url." class=".$initial_class." >Initial </a>(".$initial.")";

		$transit_class 			= ( $current == 'in_transit' ) ? 'current' :'';
		$in_transit_url 		= add_query_arg('order_status','in_transit');
		$in_transit_url 		= remove_query_arg('paged', $in_transit_url);
		$views['in_transit'] 	= "<a href=".$in_transit_url." class=".$transit_class." >In Transit </a>(".$in_transit.")";

		$out_for_delivery_class 	= ( $current == 'out_for_delivery' ) ? 'current' :'';
		$out_for_delivery_url 		= add_query_arg('order_status','out_for_delivery');
		$out_for_delivery_url 		= remove_query_arg('paged', $out_for_delivery_url);
		$views['out_for_delivery'] 	= "<a href=".$out_for_delivery_url." class=".$out_for_delivery_class." >Out For Delivery  </a>(".$out_for_delivery.")";

		$delivered_class 		= ( $current == 'delivered' ) ? 'current' :'';
		$delivered_url 			= add_query_arg('order_status','delivered');
		$delivered_url 			= remove_query_arg('paged', $delivered_url);
		$views['delivered'] 	= "<a href=".$delivered_url." class=".$delivered_class." >Delivered </a>(".$delivered.")";

		$exceptions_class 		= ( $current == 'exceptions' ) ? 'current' :'';
		$exceptions_url 		= add_query_arg('order_status','exceptions');
		$exceptions_url 		= remove_query_arg('paged', $exceptions_url);
		$views['exceptions'] 	= "<a href=".$exceptions_url." class=".$exceptions_class." >Exceptions </a>(".$exceptions.")";

		return $views;
	}

	protected function column_default( $item, $column_name )
	{
		$class_name = 'ph_initial_status';

		switch ( $item['status_id'] ) {

			case 'INITIAL':
			$class_name = 'ph_initial_status';
			break;
			
			case 'IN_TRANSIT':
			$class_name = 'ph_transit_status';
			break;

			case 'OUT_FOR_DELIVERY':
			$class_name = 'ph_out_for_delivery_status';
			break;

			case 'EXCEPTION_1':
			$class_name = 'ph_exception_status';
			break;

			case 'EXCEPTION_2':
			$class_name = 'ph_exception_status';
			break;
			case 'EXCEPTION_3':

			case 'DELIVERED':
			$class_name = 'ph_delivered_status';
			break;

			case 'CANCELLED':
			$class_name = 'ph_canceled_status';
			break;
		}

		switch ( $column_name ) {
			case 'order':
			return "<div class='ph_order_packages' data-id='".$item['orderUUID']."'><a href='javascript:void(0)' class='ph-tracking-data' title='View Tracking Data'>View Tracking Data</a><strong>#".$item[ $column_name ]."</strong></div>";
			case 'destination':
			case 'carrier':
			case 'expected_delivery':
			return $item[ $column_name ];
			case 'status':
			return "<span class='".$class_name."''>".$item[ $column_name ]."</span>";
			default:
			return print_r( $item, true ); 
		}
	}

	function prepare_order_items() {

		$per_page = 50;
		$hidden   = array();
		$sortable = array();

		$columns  = $this->get_columns();
		// $sortable = $this->get_sortable_order_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page 	= $this->get_pagenum();

		$data 			= $this->get_order_items($current_page);

		$store_id 			= get_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_ph_store_id' );
		$api_object			= new PH_Shipment_Tracking_API();

		$all_orders 		= '';
		$initial 			= '';
		$in_transit 		= '';
		$delivered 			= '';
		$out_for_delivery 	= '';
		$exceptions 		= '';

		$order_count 		= array();
		$views 				= array();

		if( !empty($store_id) && is_object($api_object) )
		{
			$order_count = $api_object->ph_shipmint_tracking_get_order_count();
		}

		if( is_array($order_count) && isset($order_count['Count']) && !empty($order_count['Count']) )
		{
			$all_orders 		= isset($order_count['Count']['allOrders']) ? $order_count['Count']['allOrders'] : '';
			$initial 			= isset($order_count['Count']['initial']) ? $order_count['Count']['initial'] : '';
			$in_transit 		= isset($order_count['Count']['inTransit']) ? $order_count['Count']['inTransit'] : '';
			$delivered 			= isset($order_count['Count']['delivered']) ? $order_count['Count']['delivered'] : '';
			$out_for_delivery 	= isset($order_count['Count']['outForDelivery']) ? $order_count['Count']['outForDelivery'] : '';
			$exceptions 		= isset($order_count['Count']['exceptions']) ? $order_count['Count']['exceptions'] : '';
		}

		$current = ( isset($_REQUEST['order_status']) && !empty($_REQUEST['order_status']) ) ? $_REQUEST['order_status'] : 'all_orders';

		switch ( $current ) {
			case 'all_orders':
			$total_items 	= $all_orders;
			break;
			case 'initial':
			$total_items 	= $initial;
			break;
			case 'in_transit':
			$total_items 	= $in_transit;
			break;
			case 'out_for_delivery':
			$total_items 	= $out_for_delivery;
			break;
			case 'delivered':
			$total_items 	= $delivered;
			break;
			case 'exceptions':
			$total_items 	= $exceptions;
			break;
			
			default:
			$total_items 	= count( $data );
		}

		$total_items = !empty($total_items) ? $total_items : count( $data );

		// $data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->items = $data;

		$this->set_pagination_args( array(
			'total_items' => $total_items,                     
			'per_page'    => $per_page,                        
			'total_pages' => ceil( $total_items / $per_page ), 
		) );
	}

	public function get_order_items($page_num)
	{

		$store_id 			= get_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_ph_store_id' );
		$access_key 		= get_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_access_token_id' );

		$current_page 		= ( isset($_REQUEST['order_status']) && !empty($_REQUEST['order_status']) ) ? $_REQUEST['order_status'] : 'all_orders';
		$page_array			= array(
			'all_orders'		=> 'ALLORDERS',
			'initial'			=> 'INITIAL',
			'in_transit'		=> 'IN_TRANSIT',
			'out_for_delivery'	=> 'OUT_FOR_DELIVERY',
			'delivered'			=> 'DELIVERED',
			'exceptions'		=> 'EXCEPTIONS',
		);

		$page_link 			= ( !empty($current_page) && array_key_exists($current_page, $page_array) ) ? $page_array[$current_page] : 'ALLORDERS';

		if( $page_num == '1' )
		{
			$url 			= PH_SHIPMENT_TRACKING_STORE_ID_URL.'/api/v1/stores/order/'.$page_link; 	// Order URL	
		}else{
			$url 			= PH_SHIPMENT_TRACKING_STORE_ID_URL.'/api/v1/stores/order/'.$page_link.'?pageNumber='.$page_num; 	// Order URL	
		}
		
		$result 			= array();
		$all_orders 		= array();

		if( !empty($store_id) && !empty($access_key) )
		{
			$response = wp_remote_get( $url, array(
				'headers'	=> array(
					'Authorization' 			=> 'Barrer ' . $access_key,
					'x-ph-wc-track-store-id' 	=> $store_id,
				),
				'timeout'	=>	20,
				'body'		=>	array(),
			));
			
			if( is_wp_error($response) ) {

				$error_code = $response->get_error_code();

				$result		= array(
					'Status'	=>	false,
					'Code'		=>	$error_code,
					'Message'	=>	$response->get_error_message($error_code),
					'Orders'	=>	Null,
				);

			}else{

				$response_body	= isset( $response['body'] ) ?  $response['body'] : '';
				$response_code	= isset( $response['response']['code'] ) ?  $response['response']['code'] : '';

				if( $response_body && $response_code == '200' )
				{
					$response_body_obj = json_decode($response_body);

					if( $response_body_obj->success )
					{

						$result		= array(
							'Status'	=>	true,
							'Code'		=>	$response_code,
							'Message'	=>	'',
							'Orders'	=>	$response_body_obj->orders,
						);
					}else{

						$result		= array(
							'Status'	=>	false,
							'Code'		=>	$response_code,
							'Message'	=>	$response_body_obj->message,
							'Orders'	=>	Null,
						);
					}

				}elseif( $response_code == '404' ){

					$result		= array(
						'Status'	=>	false,
						'Code'		=>	$response_code,
						'Message'	=>	"Server Not Found",
						'Orders'	=>	Null,
					);

				}else{

					$result		= array(
						'Status'	=>	false,
						'Code'		=>	$response_code,
						'Message'	=>	$response['response']['message'],
						'Orders'	=>	Null,
					);
				}
			}
		}

		if( isset($result['Orders']) && !empty($result['Orders']) && is_array($result['Orders']) )
		{

			$wp_date_format 	= get_option('date_format');

			foreach( $result['Orders'] as $orders )
			{
				if( is_object($orders) )
				{
					$tracking_status_text = array(
						'INITIAL' 			=> 'Initial',
						'IN_TRANSIT' 		=> 'In Transit',
						'OUT_FOR_DELIVERY' 	=> 'Out for Delivery',
						'EXCEPTION_1' 		=> 'Delivery Exception',
						'EXCEPTION_2' 		=> 'Damaged Exception',
						'EXCEPTION_3' 		=> 'Return Exception',
						'DELIVERED' 		=> 'Delivered',
						'CANCELLED' 		=> 'Cancelled',
					);


					if( isset($orders->shipping) ) {

						$first_name 		= isset($orders->shipping->firstName) ? $orders->shipping->firstName : '';
						$last_name 			= isset($orders->shipping->lastName) ? $orders->shipping->lastName : '';
						$company 			= isset($orders->shipping->company) ? $orders->shipping->company : '';
						$address_line1 		= isset($orders->shipping->addressLine1) ? $orders->shipping->addressLine1 : '';
						$address_line2 		= isset($orders->shipping->addressLine2) ? $orders->shipping->addressLine2 : '';
						$city		 		= isset($orders->shipping->city) ? $orders->shipping->city : '';
						$state 		 		= isset($orders->shipping->state) ? $orders->shipping->state : '';
						$postcode 		 	= isset($orders->shipping->postcode) ? $orders->shipping->postcode : '';
						$country 		 	= isset($orders->shipping->country) ? $orders->shipping->country : '---';
						$email 		 		= isset($orders->shipping->email) ? $orders->shipping->email : '';
						$phone 		 		= isset($orders->shipping->phone) ? $orders->shipping->phone : '';

					} else {

						$first_name 		= '';
						$last_name 			= '';
						$company 			= '';
						$address_line1 		= '';
						$address_line2 		= '';
						$city		 		= '';
						$state 		 		= '';
						$postcode 		 	= '';
						$country 		 	= '---';
						$email 		 		= '';
						$phone 		 		= '';
					}

					$name 				= $first_name.' '.$last_name;
					$order_id 			= isset($orders->orderId) ? $orders->orderId : '---';
					$orderUUID 			= isset($orders->orderUUID) ? $orders->orderUUID : '---';
					$carrier_name 		= isset($orders->carrierDisplayName) ? $orders->carrierDisplayName : '---';
					$tracking_status_id = isset($orders->status) ? $orders->status : '---';
					$delivery_time 		= isset($orders->estimatedDeliveryDate) ? date($wp_date_format, strtotime($orders->estimatedDeliveryDate)) : '---';

					$shipping_address  = !empty($name) ? $name.', ' : '';
					$shipping_address .= !empty($company) ? $company.', ' : '';
					$shipping_address .= !empty($address_line1) ? $address_line1.', ' : '';
					$shipping_address .= !empty($address_line2) ? $address_line2.', ' : '';
					$shipping_address .= !empty($city) ? $city.', ' : '';
					$shipping_address .= !empty($state) ? $state.' ' : '';
					$shipping_address .= !empty($postcode) ? $postcode : '';

					if( array_key_exists($tracking_status_id, $tracking_status_text) )
					{
						$tracking_status = $tracking_status_text[$tracking_status_id];
					}else{
						$tracking_status = $tracking_status_id;
					}

					$all_orders[] = array(
						'order' 			=> $order_id.' '.$name,
						'carrier' 			=> $carrier_name,
						'destination' 		=> $shipping_address,
						'status' 			=> $tracking_status,
						'expected_delivery' => $delivery_time,
						'orderUUID'			=> $orderUUID,
						'status_id'			=> $tracking_status_id,
					);
					
				}else{
					return $all_orders;
				}	
			}

			return $all_orders;

		}else{
			return $all_orders;
		}
	}
}