<?php

if( ! defined('ABSPATH') )	exit;

if( ! class_exists('Ph_Shipment_Tracking_Storepep_Mobile_App_Compatibility') ) {
	class Ph_Shipment_Tracking_Storepep_Mobile_App_Compatibility extends WC_REST_Controller {


		private static $_instance;
		
		/**
		 * Create instance of class Ph_Shipment_Tracking_Storepep_Mobile_App_Compatibility
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Initialize
		 */
		public function init() {
			$this->register_rest_route_update_tracking();
		}

		/**
		 * Register rest api for Pluginhive Shipment Tracking.
		 */
		public function register_rest_route_update_tracking() {
			// Update Tracking Number rest api
			register_rest_route(
				'wc/phshipmenttracking/v2', 'updatetracking', array(
					'methods'	=>	WP_REST_SERVER::EDITABLE,
					'callback'	=>	array($this, 'updateTrackingDetails'),
					'permission_callback' => array($this, 'create_item_permissions_check'),
				)
			);

			// Read the Tracking data of settings rest api
			register_rest_route(
				'wc/phshipmenttracking/v2', 'getsettings', array(
					'methods'	=>	WP_REST_SERVER::READABLE,
					'callback'	=>	array($this, 'getTrackingSettingsData'),
					'permission_callback' => array($this, 'create_item_permissions_check'),
				)
			);
		}

		/**
		 * Update Tracking details using Rest API, Storepep Mobile App.
		 */
		public function updateTrackingDetails( $request ) {
			$result = array(
				'status'	=>	false,
				'message'	=>	"",
			);

			if( is_a( $request, 'WP_REST_Request' ) ) {
				$success	= false;
				$req_body	= $request->get_body();
				$req_body	= json_decode($req_body);
				if( ! empty($req_body) ) {
					if( is_array($req_body) ) {
						$result=array();
						foreach( $req_body as $req ) {
							if( is_object($req) ) {
								$order_id				= $req->id;
								$trackingnumber			= $req->trackingID;
								$carrier				= $req->carrier;
								$shippingdate			= '';
								$tracking_description	= null;
								$order 				= wc_get_order($order_id);
								if( is_a( $order, 'WC_Order') ) {
									$message = Ph_Shipment_Tracking_Util::update_tracking_data($order_id, $trackingnumber, sanitize_title($carrier), WF_Tracking_Admin::SHIPMENT_SOURCE_KEY, WF_Tracking_Admin::SHIPMENT_RESULT_KEY, $shippingdate, $tracking_description );
									update_post_meta( $order_id, WF_Tracking_Admin::TRACKING_MESSAGE_KEY, $message );
									$result[] = array(
										'id'=>$order_id,
										'success'	=>	true,
										'code' => 200,
										'message'	=>	"Tracking information updated.",
									);
								}
								else{
									$result[] = array(
										'id'=>$order_id,
										'success'	=>	false,
										'code' => 400,
										'message'	=>	"Order not found.",
									);
								}
							}
						}
						$results=array('success'=>true,
										'code' => 200,
										'message'=>'Valid request',
										'result'=>$result
										);
					}
					else{
						$results = array(
							'success'	=>	false,
							'code' =>400,
							'message'	=>	"Request is not in proper format (Array of JSON).",
						);
					}
				}
				else{
					$results = array(
						'success'	=>	false,
						'code' =>400,
						'message'	=>	"Request is empty.",
					);
				}
			}
			else{
				$results = array(
					'success'	=>	false,
					'code' =>400,
					'message'	=>	"Something wrong with request",
				);
			}
			return $results;
		}

		/**
		 * Get the Carrier data.
		 */
		public function getTrackingSettingsData() {
			
			$data 					= array();
			$this->tracking_data	= get_option('ph_shipment_tracking_saved_carrier_list');
			
			if( !empty($this->tracking_data) ) {

				foreach( $this->tracking_data as $key => $carrier_details ) {
					
					$data[$key] = $carrier_details['name'];
				}
			}

			return $data;
		}

		/**
		 * Check if a given request has write access.
		 *
		 * @param  WP_REST_Request $request Full details about the request.
		 *
		 * @return bool|WP_Error
		 */
		public function create_item_permissions_check($request) {
			if (!wc_rest_check_post_permissions( 'shop_order', 'create')) {
				return new WP_Error('woocommerce_rest_cannot_create', __('Sorry, you are not allowed to create resources.', 'woocommerce'), array('status' => rest_authorization_required_code()));
			}

			return true;
		}

	}
}