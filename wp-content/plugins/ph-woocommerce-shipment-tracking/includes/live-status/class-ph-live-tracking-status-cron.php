<?php

class PH_Cron_For_Live_Tracking_Status {

	public function __construct() {

		$this->settingsId = 'ph_shipment_tracking_cron_settings';

		$this->settings 			= get_option( $this->settingsId, true );
		
		$this->liveTrackingCron 	= isset($this->settings['live_tracking_cron_enable']) && $this->settings['live_tracking_cron_enable'] == 'yes' ? 'yes' : 'no';
		$this->fromOrderStatus 		= isset($this->settings['from_order_status_to_track']) && !empty($this->settings['from_order_status_to_track']) ? $this->settings['from_order_status_to_track'] : array('wc-pending', 'wc-processing', 'wc-completed');
		$this->priorToOrderDate 	= isset($this->settings['prior_to_order_days']) && !empty($this->settings['prior_to_order_days']) ? $this->settings['prior_to_order_days'] : '14';
		$this->cronInterval 		= isset($this->settings['cron_interval_time']) && !empty($this->settings['cron_interval_time']) ? $this->settings['cron_interval_time'] : '60';
		$this->toOrderStatus 		= isset($this->settings['to_order_status_after_delivery']) && !empty($this->settings['to_order_status_after_delivery']) ? $this->settings['to_order_status_after_delivery'] : '';

		if( $this->toOrderStatus == 'wc-ph-delivered' ) {

			// Add New Status
			add_action( 'init', array($this, 'register_ph_delivered_shipment_order_status'));
			add_filter( 'wc_order_statuses', array($this, 'add_ph_delivered_shipment_to_order_statuses'));
		}

		if ( $this->liveTrackingCron == 'yes' ) {

			// Cron Schedule
			add_action( 'init', array($this, 'ph_shipment_tracking_live_status_scheduled_check'));
			add_filter( 'cron_schedules', array($this,'set_live_tracking_cron_interval' ));
			add_action( 'ph_live_tracking_status_action_cron', array($this, 'ph_live_tracking_status_monitor_cron'));
		}

	}

	/**
	* Register new Order Status
	**/
	function register_ph_delivered_shipment_order_status() {

		register_post_status( 'wc-ph-delivered', array(
			'label'						=> __( 'Delivered', 'woocommerce-shipment-tracking'),
			'public'					=> true,
			'exclude_from_search'		=> false,
			'show_in_admin_all_list'	=> true,
			'show_in_admin_status_list'	=> true,
			'label_count'				=> _n_noop( 'Delivered (%s)', 'Delivered (%s)', 'woocommerce-shipment-tracking' )
		) );
	}

	/**
	* Add New Order Status
	* @param array $order_statuses Order Status
	**/
	function add_ph_delivered_shipment_to_order_statuses( $order_statuses ) {

		$new_order_statuses = array();

		// Add new order Status after On Hold
		foreach ( $order_statuses as $key => $status ) {

			$new_order_statuses[ $key ] = $status;

			if ( 'wc-on-hold' === $key ) {

				$new_order_statuses['wc-ph-delivered'] = __( 'Delivered', 'woocommerce-shipment-tracking');
			}
		}

		return $new_order_statuses;
	}

	/**
	* Schedule Cron for Live Tracking Status
	**/
	public function ph_shipment_tracking_live_status_scheduled_check() {

		$settings 			= get_option( 'ph_shipment_tracking_cron_settings', true );
		$liveTrackingCron 	= isset($settings['live_tracking_cron_enable']) && $settings['live_tracking_cron_enable'] == 'yes' ? 'yes' : 'no';

		if ( $liveTrackingCron == 'yes' && !wp_next_scheduled('ph_live_tracking_status_action_cron') ) {
			wp_schedule_event(time(), 'ph_live_tracking_status_actions_cron_interval', 'ph_live_tracking_status_action_cron');
		}
	}

	/**
	* Clear Scheduled Cron
	**/
	public function clear_ph_shipment_tracking_live_status_scheduled_check() {

		wp_clear_scheduled_hook('ph_live_tracking_status_action_cron');
	}

	/**
	* Schedule Cron Interval Time
	* @param array $schedules Cron Schedules
	**/
	public function set_live_tracking_cron_interval( $schedules ) {

		$settings 				= get_option( 'ph_shipment_tracking_cron_settings', true );

		$this->cronInterval 	= isset($settings['cron_interval_time']) && !empty($settings['cron_interval_time']) ? $settings['cron_interval_time'] : '60';
		$cron_interval 			= $this->cronInterval;

		$schedules['ph_live_tracking_status_actions_cron_interval'] = array(
			'interval'  => (int) $cron_interval * 60 ,
			'display'   => sprintf(__('Every %d minutes', 'ph-shipment-tracking-addon'), (int) $cron_interval)
		);

		return $schedules;
	}

	/**
	* Cron function to check Tracking Status and change Order Status
	**/
	public function ph_live_tracking_status_monitor_cron()
	{

		$settings 					= get_option( 'ph_shipment_tracking_cron_settings', true );
		
		$this->fromOrderStatus 		= isset($settings['from_order_status_to_track']) && !empty($settings['from_order_status_to_track']) ? $settings['from_order_status_to_track'] : array('wc-completed');
		$this->priorToOrderDate 	= isset($settings['prior_to_order_days']) && !empty($settings['prior_to_order_days']) ? $settings['prior_to_order_days'] : '14';
		$this->toOrderStatus 		= isset($settings['to_order_status_after_delivery']) && !empty($settings['to_order_status_after_delivery']) ? $settings['to_order_status_after_delivery'] : '';

		$start_date 	= date('Y-m-d');
		$date 			= DateTime::createFromFormat('Y-m-d', $start_date);

		$date->modify('-'.$this->priorToOrderDate.' day');

		$args 	= array(
			'limit' 		=> -1,
			'type' 			=> 'shop_order',
			'status' 		=> $this->fromOrderStatus,
			'date_modified'	=> $date->format('Y-m-d') . '...' . $start_date
		);

		// Get the Orders to check for Live Status based on Settings
		$orders = PH_Live_Tracking_Order_Manager::get_orders($args);
		
		if( !empty($orders) && is_array($orders) ) {

			foreach ($orders as $order) {

				if($order instanceof WC_Order) {

					$order_id 	= $order->get_id();
					$meta_key 	= 'wf_wc_shipment_source';
					$name 		= $order->get_shipping_first_name();
					$email 		= $order->get_billing_email();

					$shipment_source_data 	  = PH_Live_Tracking_Order_Manager::get_shipment_source_data( $order_id, $meta_key );

					if( !empty($shipment_source_data) && !empty($email) ) {

						$shipment_result 	= Ph_Shipment_Tracking_Util::get_shipment_result( $shipment_source_data );

						if( isset($shipment_result->tracking_info_api_obj_array) && !empty($shipment_result->tracking_info_api_obj_array) ) {

							$carrierName 		= $shipment_source_data['shipping_service'];
							$trackingApiInfo 	= $shipment_result->tracking_info_api_obj_array;

							foreach ($trackingApiInfo as $trackingInfo) {

								// Send Tracking Mail on Status Change
								PH_Live_Tracking_Order_Manager::send_tracking_mail_based_on_status( $order_id, $name, $email, $carrierName, $trackingInfo, $settings );

							}

							// Call Order Status Change Function
							PH_Live_Tracking_Order_Manager::change_order_status( $order_id, $carrierName, $trackingApiInfo, $this->toOrderStatus );
							
						}
					}
				}
			}
		}
	} 

}