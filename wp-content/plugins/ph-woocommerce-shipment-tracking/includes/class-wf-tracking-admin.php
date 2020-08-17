<?php

class WF_Tracking_Admin
{
	const SHIPPING_METHOD_DISPLAY	= "Tracking";
	const TRACKING_TITLE_DISPLAY	= "Shipment Tracking";

	const TRACK_SHIPMENT_KEY		= "wf_wc_track_shipment"; // If you are changing this, change it in JS too.
	const SHIPMENT_SOURCE_KEY		= "wf_wc_shipment_source";
	const SHIPMENT_RESULT_KEY		= "wf_wc_shipment_result";
	const TRACKING_MESSAGE_KEY 		= "wftrackingmsg";
	const TRACKING_METABOX_KEY		= "WF_Tracking_Metabox";
	
	private function wf_init() {
		if ( ! class_exists( 'PH_ShipmentTrackingFactory' ) )
			include_once ( 'track/class-wf-tracking-factory.php' );
		if ( ! class_exists( 'Ph_Shipment_Tracking_Util' ) )
			include_once ( 'track/class-wf-tracking-util.php' );

		// Sorted tracking data.
		$this->tracking_data	= Ph_Shipment_Tracking_Util::load_tracking_data( true );
	}

	function __construct() {

		$this->wf_init();

		$this->settings 					= get_option( 'ph_shipment_tracking_settings_data' );
		$shipment_tracking_customer 		= isset($this->settings['tracking_to_customer']) ? $this->settings['tracking_to_customer'] : '';
		$shipment_tracking_email_customer 	= isset($this->settings['tracking_to_mail']) ? $this->settings['tracking_to_mail'] : '';

		if ( is_admin() ) { 
			add_action( 'add_meta_boxes', array( $this, 'wf_add_tracking_metabox' ), 15 );
			add_action('admin_notices', array( $this, 'wf_admin_notice'), 15);

			if ( isset( $_GET[self::TRACK_SHIPMENT_KEY] ) ) {
				add_action( 'init', array( $this, 'wf_display_admin_track_shipment' ), 15 );
			}

			if ( isset( $_POST['update_tracking_shipment_ids'] ) ) {
				add_action( 'init', array( $this, 'ph_display_admin_track_shipment_update_order' ), 15 );
			}
		}
		
		if( isset($_GET['refres_realtime_track']) ){
			add_action( 'init', array( $this, 'wf_refresh_realtime_tracking_info' ), 15 );
		}

		if( $shipment_tracking_customer == 'yes' || empty($shipment_tracking_customer) ) {
			// Shipment Tracking - Customer Order Details Page.
			add_action( 'ph_fetch_shipment_tracking_details_view_order', array( $this, 'wf_display_tracking_info_for_customer' ), 10,1 );   // to display shipment tracking details any place arguments required - order_id
			add_action( 'woocommerce_view_order', array( $this, 'wf_display_tracking_info_for_customer' ), 6 );
			add_action( 'woocommerce_view_order', array( $this, 'wf_display_tracking_api_info_for_customer' ), 20 );
		}

		if( $shipment_tracking_email_customer == 'yes' || empty($shipment_tracking_email_customer) ) {
			add_action( 'woocommerce_email_order_meta', array( $this, 'wf_add_tracking_info_to_email'), 20 );
		}
		
		// To get shipment tracking details outside
		add_action( 'ph_fetch_shipment_tracking_details', array( $this, 'wf_add_tracking_info_to_email') );

		//Update the tracking info while updating the 'Shipping easy' tracking info via REST API
		add_action( 'woocommerce_rest_insert_order_note', array( $this, 'update_tracking_info_on_rest_insert_order_note'),10,2 );
	}

	function update_tracking_info_on_rest_insert_order_note( $note, $request ){
		
		$shipping_easy 	= isset($this->settings['shipping_easy']) ? $this->settings['shipping_easy'] : '';
		$go_shippo 		= isset($this->settings['go_shippo']) ? $this->settings['go_shippo'] : '';

		if( !($shipping_easy=='yes') && !($go_shippo=='yes') ){
			return;
		}

		if( empty($note->comment_content) ){
			return;
		}

		$trackingnumber 	= '';
		$carrier 			= '';

		if( $go_shippo == 'yes' )
		{
			preg_match('/(?<=tracking number )\S+/i', $note->comment_content, $match);

			if( isset($match[0]) ) {

				$trackingnumber = $match[0];
			}

			preg_match('/^([\w]+)/i', $note->comment_content, $match);
			

			if( isset($match[0]) ) {

				$carrier = $this->get_trackingpro_carrier_code( sanitize_title($match[0]) );
			}

		}else if( $shipping_easy == 'yes' ){

			preg_match('/(?<=Tracking Number: )\S+/i', $note->comment_content, $match);

			if( isset($match[0]) ) {

				$trackingnumber = $match[0];
			}

			preg_match('/(?<=Carrier Key: )\S+/i', $note->comment_content, $match);

			if( isset($match[0]) ) {

				$carrier = $this->get_trackingpro_carrier_code( sanitize_title($match[0]) );
			}
		}

		$order_id = $note->comment_post_ID;
		$shippingdate = $note->comment_date;

		if( !empty($order_id) && !empty($trackingnumber) && !empty($carrier) ){

			$order = wc_get_order($order_id);
			
			$message = Ph_Shipment_Tracking_Util::update_tracking_data( $order_id, $trackingnumber, $carrier, self::SHIPMENT_SOURCE_KEY, self::SHIPMENT_RESULT_KEY, $shippingdate );
			
			update_post_meta( $order_id, self::TRACKING_MESSAGE_KEY, $message );
			$order->update_status('completed');
		}
	}

	private function get_trackingpro_carrier_code($code){
		$code = strtolower($code);
		$this->carrier_codes = array(
			'usps' => 'united-states-postal-service-usps',
		);
		return !empty( $this->carrier_codes[$code] ) ? $this->carrier_codes[$code] : '';
	}

	function wf_add_tracking_info_to_email( $order, $sent_to_admin = false, $plain_text = false ) {
		$order = $this->wf_load_order( $order );
		$shipment_result_array 	= get_post_meta( $order->id , self::SHIPMENT_RESULT_KEY, true );

		if( !empty( $shipment_result_array ) ) {
			$shipping_title = apply_filters('wf_shipment_tracking_email_shipping_title', __( 'Shipping Detail', 'woocommerce-shipment-tracking' ) ,$order->id);
			echo '<h3>'.$shipping_title.'</h3>';
			$shipment_source_data 	= $this->get_shipment_source_data( $order->id );
			$order_notice 	= Ph_Shipment_Tracking_Util::get_shipment_display_message ( $shipment_result_array, $shipment_source_data );
			echo '<p>'.$order_notice.'</p></br>';
			$order_shipment_description = Ph_Shipment_Tracking_Util::get_shipment_description_as_message( $shipment_source_data );
			if( ! empty($order_shipment_description) ) {
				echo "<br>".$order_shipment_description."<br>";
			}
		}
	}

	public function wf_display_tracking_info_for_customer( $order_id ) {
		
		$shipment_result_array 	= get_post_meta( $order_id , self::SHIPMENT_RESULT_KEY, true );

		if( !empty( $shipment_result_array ) ) {
			// Note: There is a bug in wc_add_notice which gives inconstancy while displaying messages.
			// Uncomment after it gets resolved.
			// $this->display_notice_message( $order_notice );
			$shipment_source_data 	= $this->get_shipment_source_data( $order_id );
			$order_notice 	= Ph_Shipment_Tracking_Util::get_shipment_display_message ( $shipment_result_array, $shipment_source_data );
			echo $order_notice;
		}
	}

	public function wf_display_tracking_api_info_for_customer( $order_id ) {

		$turn_on_api 	= isset($this->settings['turn_on_api']) ? $this->settings['turn_on_api'] : '';

		if( 'no' == $turn_on_api ) {
			return;
		}
		
		$shipment_result_array 	= get_post_meta( $order_id , self::SHIPMENT_RESULT_KEY, true );

		if( !empty( $shipment_result_array ) ) {
			if( !empty( $shipment_result_array['tracking_info_api'] ) ) {
				$this->display_api_message_table( $shipment_result_array['tracking_info_api'], $order_id );
			}
		}
	}

	function display_api_message_table ( $tracking_info_api_array ,$order_id ) {
		
		$carrier_name = $this->wf_get_shipping_service_from_url($tracking_info_api_array[0]['tracking_link']);
		$tracking_ids = '';

		foreach ( $tracking_info_api_array as $tracking_info_api ) {
			$tracking_ids .=$tracking_info_api['tracking_id'].',';
		}

		$tracking_ids = rtrim($tracking_ids,',');

		$automatic_tracking_live_status_refresh 	= isset($this->settings['auto_refresh']) ? $this->settings['auto_refresh'] : '';

		if( $automatic_tracking_live_status_refresh == 'enable' ) {
			$this->automatic_tracking_live_status_refresh( $order_id, $tracking_ids, $carrier_name, $tracking_info_api_array);
			$shipment_result_array 		= get_post_meta( $order_id , self::SHIPMENT_RESULT_KEY, true );
			$tracking_info_api_array 	= $shipment_result_array['tracking_info_api'];
		}
		
		echo '<h3>'.__( self::TRACKING_TITLE_DISPLAY, 'woocommerce-shipment-tracking' ).'</h3>';
		echo '<table class="shop_table wooforce_tracking_details">';
		echo '<thead>';
		echo '<tr>';
		echo '<th class="product-name">'.__( 'Tracking ID(s)', 'woocommerce-shipment-tracking' ).'</th>';
		echo '<th class="product-total">'.__( 'Status', 'woocommerce-shipment-tracking' ).'</th>';
		if( $automatic_tracking_live_status_refresh != 'enable' ) {
			$refres_url = get_permalink().'?refres_realtime_track='.$order_id.'&wf_wc_track_shipment='.$tracking_ids.'&shipping_service='.$carrier_name;
			echo '<th class="product-total"><p style="float: right;"> <a href="'.$refres_url.'">'.__( 'Refresh', 'woocommerce-shipment-tracking' ).'</p></th>';
		}
		echo '</tr>';
		echo '</thead>';
		echo '<tfoot>';

		foreach ( $tracking_info_api_array as $tracking_info_api ) {
			echo '<tr>';
			if (strpos($tracking_info_api['tracking_link'], $tracking_info_api['tracking_id']) !== false) {
				$tracking_link = $tracking_info_api['tracking_link'];
			}
			else{
				$tracking_link = $tracking_info_api['tracking_link'].''.$tracking_info_api['tracking_id'];
			}

			echo '<th scope="row">'.'<a href="'.$tracking_link.'" target="_blank">'.$tracking_info_api['tracking_id'].'</a></th>';
			
			if( '' == $tracking_info_api['api_tracking_status'] ) {
				$message = __( 'Unable to update real time status at this point of time. Please follow the link on shipment id to check status.', 'woocommerce-shipment-tracking' );
			}
			else {
				$message = $tracking_info_api['api_tracking_status'];
			}

			if( $automatic_tracking_live_status_refresh != 'enable' ) {
				echo'<td colspan="2"><span>'.__( $message, 'woocommerce-shipment-tracking' ).'</span></td>';
			} else {
				echo'<td><span>'.__( $message, 'woocommerce-shipment-tracking' ).'</span></td>';
			}
			echo '</tr>';
		}
		echo '</tfoot>
		</table>';

		$this->display_shipment_progress( $tracking_info_api_array, $order_id, $carrier_name );
	}

	public function automatic_tracking_live_status_refresh($post_id,$shipment_id_cs, $shipping_service, $tracking_info_api_array ) {

		$shipment_source_data	= Ph_Shipment_Tracking_Util::prepare_shipment_source_data( $post_id, $shipment_id_cs, $shipping_service, '' );

		$location = isset( $_SERVER['HTTP_REFERER'] ) ?  $_SERVER['HTTP_REFERER'] : '';

		if( empty($location) )
		{
			$location = home_url("my-account/view-order/$post_id");
		}

		try {
			$shipment_result 	= Ph_Shipment_Tracking_Util::get_shipment_result( $shipment_source_data );
		}catch( Exception $e ) {
			wp_redirect( $location );
			exit;
		}

		if ( null != $shipment_result && is_object( $shipment_result ) ) {
			$shipment_result_array = Ph_Shipment_Tracking_Util::convert_shipment_result_obj_to_array ( $shipment_result );
			update_post_meta( $post_id, self::SHIPMENT_RESULT_KEY, $shipment_result_array );
		}
	}

	public function display_shipment_progress( $tracking_info_api_array, $order_id = null, $carrier_name = null ) {
		
		$wp_date_format = get_option('date_format');
		
		foreach ( $tracking_info_api_array as $tracking_info_api ) {
			if( ! empty($tracking_info_api['shipment_progress']) ) {

				echo "<h3>Shipment Progress</h3>";

				if (strpos($tracking_info_api['tracking_link'], $tracking_info_api['tracking_id']) !== false) {
					$tracking_link = $tracking_info_api['tracking_link'];
				}else{
					$tracking_link = $tracking_info_api['tracking_link'].''.$tracking_info_api['tracking_id'];
				}
				echo "<table>";
				echo "<caption><a href='". $tracking_link ."' target='_blank'>".$tracking_info_api['tracking_id']."</a></caption>";
					// Headings
				echo "<tr>";
						// Location and Date not availabe in case of USPS
						// if( $carrier_name != 'united-states-postal-service-usps' ) {
				echo "<th>". __( 'Location', 'woocommerce-shipment-tracking'). "</th>";
				echo "<th>". __( 'Date', 'woocommerce-shipment-tracking') ."</th>";
						// }
				echo "<th>". __( 'Activity', 'woocommerce-shipment-tracking'). "</th>";
				echo "</tr>";

					// Data
				foreach( $tracking_info_api['shipment_progress'] as $shipment_progress ) {
					echo "<tr>";
							// Location and Date not availabe in case of USPS
							// if( $carrier_name != 'united-states-postal-service-usps' ) {
					echo "<td>". $shipment_progress['location']. "</td>";
					$date = date_create($shipment_progress['date']);
					$date = ($date instanceof DateTime) ? $date->format($wp_date_format) : $shipment_progress['date'];
					echo "<td>". $date. "</td>";
							// }
					echo "<td>". $shipment_progress['status']. "</td>";
					echo "</tr>";
				}
				echo "</table>";
			}
		}
	}

	function wf_get_shipping_service_from_url( $url ){
		$url = parse_url($url);
		foreach ($this->tracking_data as $key => $tracking_ele) {

			if( !empty($tracking_ele['api_url']) ){
				$api_url = parse_url( $tracking_ele['api_url'] );
				if( !empty($api_url['host']) && $api_url['host'] == $url['host'] ){
					return $key;
				}
			}

			$tracking_url = parse_url( $tracking_ele['tracking_url'] );
			if( !empty($tracking_url['host']) && $tracking_url['host'] == $url['host'] ){
				return $key;
			}
		}
		return false;
	}

	function wf_refresh_realtime_tracking_info(){
		$post_id 			= isset( $_GET['refres_realtime_track'] ) ? $_GET['refres_realtime_track'] : '';
		$shipment_id_cs		= isset( $_GET[ 'wf_wc_track_shipment' ] ) ? $_GET[ 'wf_wc_track_shipment' ] : '';
		$shipping_service	= isset( $_GET[ 'shipping_service' ] ) ? $_GET[ 'shipping_service' ] : '';
		$order_date			= isset( $_GET[ 'order_date' ] ) ? $_GET[ 'order_date' ] : '';
		
		$location = isset( $_SERVER['HTTP_REFERER'] ) ?  $_SERVER['HTTP_REFERER'] : '';

		if( empty($location) )
		{
			$location = home_url("my-account/view-order/$post_id");
		}

		$shipment_source_data	= Ph_Shipment_Tracking_Util::prepare_shipment_source_data( $post_id, $shipment_id_cs, $shipping_service, $order_date );
		
		try {
			$shipment_result 	= Ph_Shipment_Tracking_Util::get_shipment_result( $shipment_source_data );
		}catch( Exception $e ) {
			wp_redirect( $location );
			exit;
		}

		if ( null != $shipment_result && is_object( $shipment_result ) ) {
			$shipment_result_array = Ph_Shipment_Tracking_Util::convert_shipment_result_obj_to_array ( $shipment_result );
			update_post_meta( $post_id, self::SHIPMENT_RESULT_KEY, $shipment_result_array );
			$admin_notice = Ph_Shipment_Tracking_Util::get_shipment_display_message ( $shipment_result_array, $shipment_source_data );
		}
		else {
			$admin_notice = __( 'Unable to update tracking info.', 'woocommerce-shipment-tracking' );
			update_post_meta( $post_id, self::SHIPMENT_RESULT_KEY, '' );
		}

		wp_redirect( $location );
		exit;
	}

	function display_notice_message( $message, $type = 'notice' ) {
		if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '>=' ) ) {
			wc_add_notice( $message, $type );
		} else {
			global $woocommerce;
			$woocommerce->add_message( $message );
		}
	}

	function wf_admin_notice(){
		global $pagenow;
		global $post;
		
		if( !isset( $_GET[ self::TRACKING_MESSAGE_KEY ] ) && empty( $_GET[ self::TRACKING_MESSAGE_KEY ] ) ) {
			return;
		}

		$wftrackingmsg = $_GET[ self::TRACKING_MESSAGE_KEY ];

		switch ( $wftrackingmsg ) {
			case "0":
			echo '<div id="message" class="error"><p>'.self::SHIPPING_METHOD_DISPLAY.': '.__( 'Sorry, Unable to proceed.', 'woocommerce-shipment-tracking' ).'</p></div>';
			break;
			case "4":
			echo '<div id="message" class="error"><p>'.self::SHIPPING_METHOD_DISPLAY.': '.__( 'Unable to track the shipment. Please cross check shipment id or try after some time.', 'woocommerce-shipment-tracking' ).'</p></div>';
			break;
			case "5":
			$wftrackingmsg = get_post_meta( $post->ID, self::TRACKING_MESSAGE_KEY, true);
			if( '' != trim( $wftrackingmsg )) {
				echo '<div id="message" class="updated"><p>'.__( $wftrackingmsg, 'woocommerce-shipment-tracking' ).'</p></div>';
			}
			break;
			case "6":
			echo '<div id="message" class="updated"><p>'.__( 'Tracking is unset.', 'woocommerce-shipment-tracking' ).'</p></div>';
			break;
			case "7":
			echo '<div id="message" class="updated"><p>'.__( 'Tracking Data is reset to default.', 'woocommerce-shipment-tracking' ).'</p></div>';
			break;
			default:
			break;
		}
	}

	function wf_add_tracking_metabox() {

		global $post;

		if ( !$post ) return;
		
		if ( !in_array( $post->post_type, array('shop_order') ) ) return;

		$order = $this->wf_load_order( $post->ID );
		if ( !$order ) return; 


		// Shipping method is available. 
		add_meta_box( self::TRACKING_METABOX_KEY, __( self::TRACKING_TITLE_DISPLAY, 'woocommerce-shipment-tracking' ), array( $this, 'wf_tracking_metabox_content' ), 'shop_order', 'side', 'default' );
	}

	function get_shipment_source_data( $post_id ) {
		$shipment_source_data 	= get_post_meta( $post_id, self::SHIPMENT_SOURCE_KEY, true );
		
		if ( empty( $shipment_source_data ) || !is_array( $shipment_source_data ) ) {
			$shipment_source_data	= array();
			$shipment_source_data['shipment_id_cs']		= '';
			$shipment_source_data['shipping_service']	= '';
			$shipment_source_data['order_date']			= '';
			$shipment_source_data['order_id']			= '';
			$shipment_source_data['tracking_shipment_descriptions']='';
		}

		return $shipment_source_data;
	}
	
	function wf_tracking_metabox_content() {

		global $post;
		
		$shipmentId 	= '';
		$order 			= $this->wf_load_order( $post->ID );
		$tracking_url 	= admin_url( '/?post='.( $post->ID ) );
		
		$shipment_source_data 	= $this->get_shipment_source_data( $post->ID );
		$trackinglist 			= get_option('ph_shipment_tracking_saved_carrier_list');

		if( empty($trackinglist) ) {

			$trackinglist = Ph_Shipment_Tracking_Util::load_tracking_data( true, true );
		}

		ksort($trackinglist);
		
		?>
		<ul class="order_actions submitbox">

			<li id="actions" class="wide">

				<select name="shipping_service" id="shipping_service">
					<?php

					echo "<option value=''>".__( 'None', 'woocommerce-shipment-tracking' )."</option>";

					foreach ( $trackinglist as $key => $details ) {

						$default_selected = apply_filters('wf_shipment_tracking_default_provider', $shipment_source_data['shipping_service']);

						echo '<option value='.$key.' '.selected($default_selected, $key).' >'.__( $details[ "name" ], 'woocommerce-shipment-tracking' ).'</option>';
					}

					$tracking_description = isset($shipment_source_data['tracking_shipment_descriptions']) ? $shipment_source_data['tracking_shipment_descriptions'] : null;
					?>

				</select><br>
				<strong><?php _e( 'Enter Tracking IDs', 'woocommerce-shipment-tracking' ) ?></strong>
				<img class="help_tip" style="float:none;" data-tip="<?php _e( 'Comma separated, in case of multiple shipment ids for this order.', 'woocommerce-shipment-tracking' ); ?>" src="<?php echo WC()->plugin_url();?>/assets/images/help.png" height="16" width="16" /><br>
				<textarea id="tracking_shipment_ids" class="input-text" type="text" name="tracking_shipment_ids" ><?php echo $shipment_source_data['shipment_id_cs']; ?></textarea><br>
				<strong><?php _e( 'Descriptions', 'woocommerce-shipment-tracking' ) ?></strong>
				<img class="help_tip" style="float:none;" data-tip="<?php _e( 'Pipe separated, in case of multiple shipment description.', 'woocommerce-shipment-tracking' ); ?>" src="<?php echo WC()->plugin_url();?>/assets/images/help.png" height="16" width="16" /><br>
				<textarea id="tracking_shipment_descriptions" class="input-text" type="text" name="tracking_shipment_descriptions" placeholder="<?php _e( 'Use Pipe `|` to seperate description of different shipment ids.', 'woocommerce-shipment-tracking' ); ?>"><?php echo $tracking_description; ?></textarea><br>
				<strong><?php _e('Shipment Date', 'woocommerce-shipment-tracking') ?></strong>
				<img class="help_tip" style="float:none;" data-tip="<?php _e( 'This field is Optional.', 'woocommerce-shipment-tracking' ); ?>" src="<?php echo WC()->plugin_url();?>/assets/images/help.png" height="16" width="16" /><br>
				<input type="text" id="order_date" class="date-picker wf-date-picker" value="<?php echo $shipment_source_data['order_date']; ?>"></p>
			</li>

			<li id="" class="wide">
				<a class="button button-primary woocommerce_shipment_tracking tips" href="<?php echo $tracking_url; ?>" data-tip="<?php _e( 'Save/Show Tracking Info', 'woocommerce-shipment-tracking' ); ?>"><?php _e('Save/Show Tracking Info', 'woocommerce-shipment-tracking'); ?></a>
			</li>

		</ul>
		<script>
			jQuery(document).ready(function($) {
				$( ".wf-date-picker" ).datepicker();
			});
			
			jQuery("a.woocommerce_shipment_tracking").on("click", function() {
				location.href = this.href + '&wf_wc_track_shipment=' + jQuery('#tracking_shipment_ids').val().replace(/ /g,'')+'&shipping_service='+ jQuery( "#shipping_service" ).val()+'&order_date='+ jQuery( "#order_date" ).val()+'&tracking_shipment_descriptions='+jQuery( "#tracking_shipment_descriptions" ).val();
				return false;
			});

			jQuery("button.save_order").on("click", function(e) {

				if( jQuery.trim(jQuery('#tracking_shipment_ids').val()) !== '')
				{
					e.preventDefault();
					var html = "<input type='hidden' name='update_tracking_shipment_ids' value='"+jQuery('#tracking_shipment_ids').val().replace(/ /g,'')+"' >\
					<input type='hidden' name='update_shipping_service' value='"+jQuery( "#shipping_service" ).val()+"' >\
					<input type='hidden' name='update_order_date' value='"+jQuery( "#order_date" ).val()+"' >\
					<input type='hidden' name='update_tracking_shipment_descriptions' value='"+jQuery( "#tracking_shipment_descriptions" ).val()+"' >\
					";

					jQuery("form[name='post']").append(html);
					
					jQuery("form[name='post']").submit();
				}
			});

		</script>
		<?php
	}

	function wf_display_admin_track_shipment() {
		if( !$this->wf_user_check() ) {
			_e( "You don't have admin privileges to view this page.", 'woocommerce-shipment-tracking' );
			exit;
		}

		$post_id 			= isset( $_GET['post'] ) ? $_GET['post'] : '';
		$shipment_id_cs		= isset( $_GET[ self::TRACK_SHIPMENT_KEY ] ) ? $_GET[ self::TRACK_SHIPMENT_KEY ] : '';
		$shipping_service	= isset( $_GET[ 'shipping_service' ] ) ? $_GET[ 'shipping_service' ] : '';
		$order_date			= isset( $_GET[ 'order_date' ] ) ? $_GET[ 'order_date' ] : '';
		$tracking_description	= isset( $_GET[ 'tracking_shipment_descriptions' ] ) ? $_GET[ 'tracking_shipment_descriptions' ] : '';

		$shipment_source_data	= Ph_Shipment_Tracking_Util::prepare_shipment_source_data( $post_id, $shipment_id_cs, $shipping_service, $order_date, $tracking_description );
		$shipment_result 		= $this->get_shipment_info( $post_id, $shipment_source_data );

		if ( null != $shipment_result && is_object( $shipment_result ) ) {
			$shipment_result_array = Ph_Shipment_Tracking_Util::convert_shipment_result_obj_to_array ( $shipment_result );
			
			update_post_meta( $post_id, self::SHIPMENT_RESULT_KEY, $shipment_result_array );
			$admin_notice = Ph_Shipment_Tracking_Util::get_shipment_display_message ( $shipment_result_array, $shipment_source_data );
		}
		else {
			$admin_notice = __( 'Unable to update tracking info.', 'woocommerce-shipment-tracking' );
			update_post_meta( $post_id, self::SHIPMENT_RESULT_KEY, '' );
		}

		self::display_admin_notification_message( $post_id, $admin_notice );
	}

	public static function display_admin_notification_message( $post_id, $admin_notice ) {
		$wftrackingmsg = 5;
		update_post_meta( $post_id, self::TRACKING_MESSAGE_KEY, $admin_notice );
		wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&'.self::TRACKING_MESSAGE_KEY.'='.$wftrackingmsg ) );
		exit;
	}

	// Added similar function in compatibility - func-pluginhive-shipping-plugins-integration.php
	// Any changes to this function should be updated in that function
	function get_shipment_info( $post_id, $shipment_source_data ) {

		if( empty( $post_id ) ) {
			$wftrackingmsg = 0;
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&'.self::TRACKING_MESSAGE_KEY.'='.$wftrackingmsg ) );
			exit;
		}
		
		if( '' == $shipment_source_data['shipping_service'] ) {
			update_post_meta( $post_id, self::SHIPMENT_SOURCE_KEY, $shipment_source_data );
			update_post_meta( $post_id, self::SHIPMENT_RESULT_KEY, '' );

			$wftrackingmsg = 6;
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&'.self::TRACKING_MESSAGE_KEY.'='.$wftrackingmsg ) );
			exit;
		}
		
		update_post_meta( $post_id, self::SHIPMENT_SOURCE_KEY, $shipment_source_data );
		
		try {
			$shipment_result 	= Ph_Shipment_Tracking_Util::get_shipment_result( $shipment_source_data );
		}catch( Exception $e ) {
			$wftrackingmsg = 0;
			wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&'.self::TRACKING_MESSAGE_KEY.'='.$wftrackingmsg ) );
			exit;
		}

		return $shipment_result;
	}

	function ph_display_admin_track_shipment_update_order() {
		
		if( !$this->wf_user_check() ) {
			_e( "You don't have admin privileges to view this page.", 'woocommerce-shipment-tracking' );
			exit;
		}

		$post_id 			= isset( $_POST['post_ID'] ) ? $_POST['post_ID'] : '';
		$shipment_id_cs		= isset( $_POST[ 'update_tracking_shipment_ids' ] ) ? $_POST[ 'update_tracking_shipment_ids' ] : '';
		$shipping_service	= isset( $_POST[ 'update_shipping_service' ] ) ? $_POST[ 'update_shipping_service' ] : '';
		$order_date			= isset( $_POST[ 'update_order_date' ] ) ? $_POST[ 'update_order_date' ] : '';
		$tracking_description	= isset( $_POST[ 'update_tracking_shipment_descriptions' ] ) ? $_POST[ 'update_tracking_shipment_descriptions' ] : '';

		$shipment_source_data	= Ph_Shipment_Tracking_Util::prepare_shipment_source_data( $post_id, $shipment_id_cs, $shipping_service, $order_date, $tracking_description );
		
		$shipment_result 		= $this->get_shipment_info( $post_id, $shipment_source_data );

		if ( null != $shipment_result && is_object( $shipment_result ) ) {
			$shipment_result_array = Ph_Shipment_Tracking_Util::convert_shipment_result_obj_to_array ( $shipment_result );
			
			update_post_meta( $post_id, self::SHIPMENT_RESULT_KEY, $shipment_result_array );
			$admin_notice = Ph_Shipment_Tracking_Util::get_shipment_display_message ( $shipment_result_array, $shipment_source_data );
		}
		else {
			$admin_notice = __( 'Unable to update tracking info.', 'woocommerce-shipment-tracking' );
			update_post_meta( $post_id, self::SHIPMENT_RESULT_KEY, '' );
		}
		
		self::ph_update_order_with_shipment_details( $post_id, $admin_notice );
	}

	public static function ph_update_order_with_shipment_details( $post_id, $admin_notice ) {

		$wftrackingmsg = 5;
		update_post_meta( $post_id, self::TRACKING_MESSAGE_KEY, $admin_notice );

		wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit&'.self::TRACKING_MESSAGE_KEY.'='.$wftrackingmsg ) );
	}
	
	function wf_load_order( $orderId ){
		if ( !class_exists( 'WC_Order' ) ) {
			return false;
		}
		return ( WC()->version < '2.7.0' ) ? new WC_Order( $orderId ) : new wf_order( $orderId );    
	}

	function wf_user_check() {
		if ( is_admin() ) {
			return true;
		}
		return false;
	}
}

new WF_Tracking_Admin();

?>