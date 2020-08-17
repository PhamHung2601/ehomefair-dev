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

	.tracking_details
	{
		border-bottom: 1px solid red;
	}

	.tracking_button
	{
		width: 100%;
	}


	.tracking_button span
	{
		font-size: 25px !important;
	}
</style>

<?php 

$store_id 	= get_option( Ph_Shipment_Tracking_Util::TRACKING_SETTINGS_TAB_KEY.'_ph_store_id' );

if( !empty($store_id) ) { 
	?>

	<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="get">

		<table cellpadding="0" width="100%" bgcolor="" align="center" cellspacing="0" >
			<tr>
				<td>
					<input type="text" name="tracking_number" required id="tracking_number" value="<?php echo isset( $_GET['tracking_number'] ) ? $_GET['tracking_number']:'';?>" placeholder="Tracking Number">
				</td>
				<td>
					<button type="submit" class="tracking_button"><span class="dashicons dashicons-location"></span> <?php echo  __( 'Track', 'woocommerce-shipment-tracking' ); ?> </button>
				</td>
			</tr>
		</table>

	</form>

<?php } else { ?>

	<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post">

		<table cellpadding="0" width="100%" bgcolor="" align="center" cellspacing="0" >
			<tr>
				<td>
					<input type="number" min="0" name="order_number" required id="order_number" value="<?php echo isset( $_POST['order_number'] ) ? $_POST['order_number']:'';?>" placeholder="Order Number">
				</td>
				<td>
					<input type="email" name="order_email" required id="order_email" value="<?php echo isset( $_POST['order_email'] ) ? $_POST['order_email']:'';?>" placeholder="Order Email">
				</td>
				<td>
					<button type="submit" class="tracking_button"><span class="dashicons dashicons-location"></span> <?php echo  __( 'Track', 'woocommerce-shipment-tracking' ); ?> </button>
				</td>
			</tr>
		</table>

	</form>

<?php } ?>