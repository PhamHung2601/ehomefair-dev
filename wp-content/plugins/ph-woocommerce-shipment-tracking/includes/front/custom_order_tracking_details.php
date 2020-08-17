<?php

if( !isset($_GET['OTNum']) ) {

	require_once('custom_tracking_page.php');
}

if( isset($args) && !empty($args) ) {
	
	?>
	<p><?php echo __( $args['message'], 'woocommerce-shipment-tracking'); ?></p>
	<?php
	if( !empty($args['liveTracking']) && is_array($args['liveTracking']) ) {

		foreach ( $args['liveTracking'] as $liveTracking ) {

			if( !empty($liveTracking->shipment_progress) ) {

				if (strpos($liveTracking->tracking_link, $liveTracking->tracking_id) !== false) {
					$tracking_link = $liveTracking->tracking_link;
				}else{
					$tracking_link = $liveTracking->tracking_link.''.$liveTracking->tracking_id;
				}

				echo "<table>";
					echo "<caption><a href='". $tracking_link ."' target='_blank'>".$liveTracking->tracking_id."</a></caption>";
					
					echo "<tr>";
						echo "<th>". __( 'Location', 'woocommerce-shipment-tracking'). "</th>";
						echo "<th>". __( 'Date', 'woocommerce-shipment-tracking') ."</th>";
						echo "<th>". __( 'Activity', 'woocommerce-shipment-tracking'). "</th>";
					echo "</tr>";

					foreach( $liveTracking->shipment_progress as $shipment_progress ) {
						echo "<tr>";
							echo "<td>". __( $shipment_progress['location'], 'woocommerce-shipment-tracking') . "</td>";
							echo "<td>". __( $shipment_progress['date'], 'woocommerce-shipment-tracking') . "</td>";
							echo "<td>". __( $shipment_progress['status'], 'woocommerce-shipment-tracking') . "</td>";
						echo "</tr>";
					}
				echo "</table>";
			}
		}
	}
}