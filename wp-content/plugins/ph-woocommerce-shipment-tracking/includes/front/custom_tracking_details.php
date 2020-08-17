<?php

const DHL_CARRIER_CODE 			= "C1";
const FEDEX_CARRIER_CODE 		= "C2";
const UPS_CARRIER_CODE 			= "C3";
const USPS_CARRIER_CODE 		= "C5";
const CANADA_POST_CARRIER_CODE 	= "C6";
const DELHIVERY_CARRIER_CODE 	= "C7";
const AUSTRALIA_POST_CARRIER_CODE = "C8";
const BLUE_DART_CARRIER_CODE 	= "C9";

if( isset($_GET['idType']) && $_GET['idType'] == 'phtrackingId' ) {
	// add nothing
	?>
	<style>
		h2,h3,p
		{
			text-align: center;
		}

		input
		{
			width: 100%;
		}

		.tracking_status
		{
			width: 100%;
			text-align: center;
			background-color: #32455e;
			font-weight: bold;
			color: white;
			padding: 20px;
		}

	</style>
	<?php
}else{
	require_once('custom_tracking_page.php');
}

if( isset($args) && !empty($args) )
{
	if( empty($args['TrackingHistory']) &&  $args['Status'] )
	{
		?>	
		<h3> No tracking details found </h3>
		<?php
	}else if( empty($args['TrackingHistory']) && !empty( $args['Code'] ) ){
		?>	
		<h3 class="ph_error_message"> <?php echo $args['Message']; ?> </h3>
		<?php
	}else{
		if( isset($args['Order']) && !empty($args['Order']) )
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

			$tracking_status_id = isset($args['Order']->status) ? $args['Order']->status : 'Not Found';

			if( array_key_exists($tracking_status_id, $tracking_status_text) )
			{
				$tracking_status = $tracking_status_text[$tracking_status_id];
			}else{
				$tracking_status = $tracking_status_id;
			}

			$order_id = isset($args['Order']->orderId) ? $args['Order']->orderId : 'Not Found';

			if( isset($args['Order']->lastTrackingStatus) )
			{
				$tracking_number = isset($args['Order']->lastTrackingStatus->trackingNumber) ? $args['Order']->lastTrackingStatus->trackingNumber : 'Not Found';
			}else{
				$tracking_number = 'Not Found';
			}
			
			?>

			<table cellpadding="0" width="100%" bgcolor="" align="center" cellspacing="0" >

				<tr>
					<div class="tracking_status"><?php echo $tracking_status; ?></div>
				</tr>

				<tr>
					<td>
						<label for="order_id"><?php _e( '<b>Order:</b> #'.$order_id, 'woocommerce-shipment-tracking' ); ?></label>
					</td>
					<td colspan="2" style="text-align: right;">
						<label for="tracking_number"><?php _e( '<b>Tracking Number: </b>'.$tracking_number, 'woocommerce-shipment-tracking' ); ?></label>
					</td>
				</tr>

				<tr class="tracking_header">
					<td > Date </td>
					<td> Location </td>
					<td> Summary </td>
				</tr>

				<?php
				if( is_array($args['TrackingHistory']) )
				{
					foreach ($args['TrackingHistory'] as $tracking_data)
					{
						if( is_object($tracking_data) )
						{
							$date_time = isset($tracking_data->dateAndTime) ? $tracking_data->dateAndTime : 'Not Found';
							$location = isset($tracking_data->location) ? $tracking_data->location : 'Not Found';
							$summary = isset($tracking_data->summary) ? $tracking_data->summary : '---';

							?>
							<tr class="tracking_details">
								<td><?php echo $date_time; ?></td>
								<td><?php echo $location; ?></td>
								<td><?php echo $summary; ?></td>
							</tr>

							<?php
						}
					}

					$url				= '';
					$tracking_number 	= '';
					$carrier_page 		= get_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_view_on_carriers_page' );
					$tracking_status_id = isset($args['Order']->carrierType) && !empty($args['Order']->carrierType) ? $args['Order']->carrierType : '';

					if( isset( $args['Order']->lastTrackingStatus) )
					{
						$tracking_number = isset($args['Order']->lastTrackingStatus->trackingNumber) ? $args['Order']->lastTrackingStatus->trackingNumber : '';
					}


					if( !empty($tracking_status_id) &&  !empty($tracking_number) )
					{
						switch ($tracking_status_id) {
							case DHL_CARRIER_CODE:
							$url = 'http://www.dhl.co.in/en/express/tracking.html?AWB='.$tracking_number;
							break;
							case FEDEX_CARRIER_CODE:
							$url = 'https://www.fedex.com/apps/fedextrack/?action=track&trackingnumber='.$tracking_number;
							break;
							case UPS_CARRIER_CODE:
							$url = 'https://wwwapps.ups.com/tracking/tracking.cgi?tracknum='.$tracking_number;
							break;
							case USPS_CARRIER_CODE:
							$url = 'https://tools.usps.com/go/TrackConfirmAction?tRef=fullpage&tLc=2&text28777=&tLabels='.$tracking_number;
							break;
							case CANADA_POST_CARRIER_CODE:
							$url = 'https://www.canadapost.ca/trackweb/en#/resultList?searchFor='.$tracking_number;
							break;
							case DELHIVERY_CARRIER_CODE:
							$url = 'https://www.delhivery.com/track/package/'.$tracking_number;
							break;
							case AUSTRALIA_POST_CARRIER_CODE:
							$url = 'https://www.auspost.com.au/track/track.html?id='.$tracking_number;
							break;
							case BLUE_DART_CARRIER_CODE:
							$url = 'https://bluedart.com/tracking';
							break;
						}
					}

					if( $carrier_page == 'yes' && !empty($url) )
					{
						?>

						<tr>
							<td colspan="3" style="text-align: right;"><a href="<?php echo $url; ?>" target='_BLANK'>View on Shipping Carrier's Page >></a></td>
						</tr>

						<?php
					}
				}else{
					?>
					<tr>
						<td colspan="3" style="text-align: center;"><?php echo __( 'No Tracking Data Available', 'woocommerce-shipment-tracking' ); ?></td>
					</tr>
					<?php
				}
				?>


			</table>
			<?php
		}
	}
}