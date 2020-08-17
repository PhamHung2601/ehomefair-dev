<?php

if( ! defined('ABSPATH') ) {
	exit;
}

if( ! class_exists('Ph_Shipment_Tracking_On_Order_Page') ) {

	class Ph_Shipment_Tracking_On_Order_Page {

		const SHIPMENT_RESULT_KEY		= "wf_wc_shipment_result";
		
		/**
		 * API that Support Live Tracking Status.
		**/
		private static $live_api_supported_services = array(
			'ups',
			'fedex',
			'united-states-postal-service-usps',
			'canada-post',
			'blue-dart',
			'australia-post',
			'delhivery',
			'dhl-express',
			'aramex',
		);

		/**
		 * Constructor
		**/
		public function __construct() {

			$carrier_status 		= get_option( 'ph_shipment_tracking_carrier_cred_status' );

			if( is_array($carrier_status) && !empty($carrier_status) && in_array( true, $carrier_status) ) {

				add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_tracking_status_column_on_shop_order_page') );
				add_action( 'manage_shop_order_posts_custom_column', array( $this, 'add_tracking_status_column_data_on_shop_order_page'), 10, 2 );
				add_action(	'admin_footer', 	array($this, 'add_bulk_shipment_tracking_refresh_option'));
				add_action(	'load-edit.php',	array($this, 'bulk_shipment_tracking_status_refresh') );
				add_action(	'add_meta_boxes', array($this, 'ph_shipment_tracking_status_meta_box') );
			}
		}

		/**
		 * Add Shipment Tracking Refresh button on Shop Order page.
		**/
		public function add_bulk_shipment_tracking_refresh_option(){

			global $post_type;
			if($post_type == 'shop_order') {
				?>
				<script type="text/javascript">
					jQuery(document).ready(function() {
						jQuery('<option>').val('ph_shipment_tracking_refresh_order_shipment_tracking_live_status').text('<?php _e('Refresh Live Tracking Status', 'woocommerce-shipment-tracking');?>').appendTo("select[name='action']");
						jQuery('<option>').val('ph_shipment_tracking_refresh_order_shipment_tracking_live_status').text('<?php _e('Refresh Live Tracking Status', 'woocommerce-shipment-tracking');?>').appendTo("select[name='action2']");
					});
				</script>
				<?php
			}
		}

		/**
		 * Perform Shipment Tracking Status on Shop Order Page.
		**/
		public function bulk_shipment_tracking_status_refresh() {

			$wp_list_table 	= _get_list_table('WP_Posts_List_Table');
			$action 		= $wp_list_table->current_action();

			if( isset($_REQUEST['post']) && is_array($_REQUEST['post']) && $action == 'ph_shipment_tracking_refresh_order_shipment_tracking_live_status' ) {

				foreach( $_REQUEST['post'] as $order_id ) {

					$shipment_source_data 	= get_post_meta( $order_id, 'wf_wc_shipment_source', true);

					if( ! empty($shipment_source_data) && in_array( $shipment_source_data['shipping_service'], self::$live_api_supported_services ) ) {

						$shipment_result 	= Ph_Shipment_Tracking_Util::get_shipment_result( $shipment_source_data );

						if ( null != $shipment_result && is_object( $shipment_result ) ) {

							$shipment_result_array = Ph_Shipment_Tracking_Util::convert_shipment_result_obj_to_array ( $shipment_result );
							update_post_meta( $order_id, 'wf_wc_shipment_result', $shipment_result_array );
						}
					}
				}
			}
		}

		/**
		 * Add Tracking Status to Shop Order Columns. 
		 * @param array $columns Shop Order Columns.
		 * @return array Shop Order Columns.
		**/
		public function add_tracking_status_column_on_shop_order_page( $columns ) {
			
			if( array_key_exists( 'order_total', $columns) ) {
				foreach( $columns as $column_key => $column_name ) {
					if( $column_key == 'order_total') {
						$new_columns['ph_shipment_tracking_status'] = __( '<span class="tips" data-tip="This field will display live tracking status for shipments. <br/> To refresh the tracking status, select the Order(s) and click Refresh Live Tracking Status option under Bulk Actions and click on Apply.">Live Tracking Status</span>', 'woocommerce-shipment-tracking' );
					}
					$new_columns[$column_key] = $column_name;
				}
			} else {
				$new_columns = $columns;
				$new_columns['ph_shipment_tracking_status'] = __( '<span class="tips" data-tip="This field will display live tracking status for shipments. <br/> To refresh the tracking status, select the Order(s) and click Refresh Live Tracking Status option under Bulk Actions and click on Apply.">Live Tracking Status</span>', 'woocommerce-shipment-tracking' );
			}
			return $new_columns;
		}

		/**
		 * Add Tracking status information to Order Column tracking status.
		 * @param string $column_id Column Id.
		 * @param mixed $order_id int | string Order Id.
		**/
		public function add_tracking_status_column_data_on_shop_order_page( $column_id, $order_id ) {

			if( $column_id == 'ph_shipment_tracking_status') {

				$data = get_post_meta( $order_id, 'wf_wc_shipment_result', true);

				if( !empty($data['tracking_info_api']) && is_array($data['tracking_info_api']) ) {

					$settings 	= get_option( 'ph_shipment_tracking_settings_data' );
					$url_link 	= isset($settings['custom_page_url']) ? $settings['custom_page_url'] : '';
					$store_id 	= get_option( 'wf_tracking_ph_store_id' );

					foreach( $data['tracking_info_api'] as $details ) {

						if( ! empty($details['api_tracking_status']) ) {

							$first 		= substr($details['api_tracking_status'], 0, 45);
							$theRest 	= substr($details['api_tracking_status'], 45);
							
							if( !empty( $store_id ) ) {
								
								$trackingNumLink = $details['tracking_id'];

							} else if( !empty($url_link) ) {

								$url_link_with_query 	= $url_link.'?OTNum='.base64_encode($order_id.'|'.$details['tracking_id']);
								$trackingNumLink 		= '<a href="'.$url_link_with_query.'" target="_blank" >'.$details['tracking_id'].'</a>';

							} else if( '' == $details['tracking_link'] ) {

								$trackingNumLink = $details['tracking_id'];

							} else {

								$trackingNumLink = ' <a href="'.$details['tracking_link'].'" target="_blank" >'.$details['tracking_id'].'</a>';
							}
							
							echo '<p style="margin-top: 5px;"><b>'. $trackingNumLink .'</b>: </p>';
							echo '<p style="margin-bottom: 5px;">'.$first;

							if(!empty($theRest)) {

								echo '<span id="dots">...</span><span id="more" style="display:none;">'.$theRest.'</span><a class="read_more" href="javascript:void(0)">Read More</a>';
							}

							echo '</p>';
						}
					}
				}
			}
		}

		/**
		 * Add Tracking History MetaBox for Live Tracking Carriers
		**/
		public function ph_shipment_tracking_status_meta_box() {

			global $post;

			if( ! $post ) {
				return;
			}

			if( $post->post_type == 'shop_order' ) {

				$order = wc_get_order($post->ID);

				if( ! empty($order) ) {

					$this->shipment_result_array 	= get_post_meta( $post->ID , self::SHIPMENT_RESULT_KEY, true );

					if( !empty( $this->shipment_result_array ) && !empty( $this->shipment_result_array['tracking_info_api'] ) ) {

						add_meta_box('ph_shipment_tracking_status', __('Tracking History', 'wf-shipping-fedex'), array($this, 'ph_shipment_tracking_status_content'), 'shop_order', 'advanced', 'default');
					}
				}
			}
		}

		/**
		 * Metabox Function
		**/
		public function ph_shipment_tracking_status_content() {

			global $post;

			if( ! $post )	return;

			$this->display_shipment_progress( $this->shipment_result_array['tracking_info_api'], $post->ID, null );
		}

		/**
		 * Display Tracking History in MetaBox for Live Tracking Carriers
		 * @param array $tracking_info_api_array Tracking History
		 * @param mixed $order_id Order Number
		 * @param string $carrier_name Carrier Name
		**/
		public function display_shipment_progress( $tracking_info_api_array, $order_id = null, $carrier_name = null ) {

			?>
			<style>

				table.ph_tracking_status_history_table {
					border-collapse: collapse;
					width: 100%;
					margin: 5px;
				}

				table.ph_tracking_status_history_table td, table.ph_tracking_status_history_table th {
					border: 1px solid #ddd;
					padding: 8px;
				}

				table.ph_tracking_status_history_table th {
					padding-top: 12px;
					padding-bottom: 12px;
					text-align: left;
					background-color:#f9f9f9;
					color: black;
				}

				table.ph_tracking_status_history_table tr:nth-child(odd){ background-color: #f2f2f2; }

			</style>
			<?php

			$wp_date_format = get_option('date_format');

			foreach ( $tracking_info_api_array as $tracking_info_api ) {

				if( ! empty($tracking_info_api['shipment_progress']) ) {

					if (strpos($tracking_info_api['tracking_link'], $tracking_info_api['tracking_id']) !== false) {

						$tracking_link = $tracking_info_api['tracking_link'];
					} else {

						$tracking_link = $tracking_info_api['tracking_link'].''.$tracking_info_api['tracking_id'];
					}

					echo "<strong>".__( 'Tracking ID : ', '')."</strong><a href='$tracking_link' target='_blank'>".$tracking_info_api['tracking_id']."</a><br/>";

					echo "<table class='ph_tracking_status_history_table'>";

						echo "<tr>";
							echo "<th>". __( 'Location', 'woocommerce-shipment-tracking'). "</th>";
							echo "<th>". __( 'Date', 'woocommerce-shipment-tracking') ."</th>";
							echo "<th>". __( 'Activity', 'woocommerce-shipment-tracking'). "</th>";
						echo "</tr>";

						// Data
						foreach( $tracking_info_api['shipment_progress'] as $shipment_progress ) {
							
							echo "<tr>";
								echo "<td>". $shipment_progress['location']. "</td>";
								
								$date = date_create($shipment_progress['date']);
								$date = ($date instanceof DateTime) ? $date->format($wp_date_format) : $shipment_progress['date'];
								
								echo "<td>". $date. "</td>";
								echo "<td>". $shipment_progress['status']. "</td>";
							echo "</tr>";
						}

					echo "</table><br/>";

				} else {
					echo "<p>".$tracking_info_api['api_tracking_error']."</p>";
				}
			}
		}
	}
	new Ph_Shipment_Tracking_On_Order_Page();
}