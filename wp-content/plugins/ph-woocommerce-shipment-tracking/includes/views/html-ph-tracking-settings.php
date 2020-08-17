<form method="post">

	<table class="form-table">
		<tbody>

			<tr valign="top">

				<th scope="row" class="titledesc" style="width:250px;">
					<label for="ph_selected_tracking_carriers"><?php echo  __( 'Tracking Carriers', 'woocommerce-shipment-tracking' ); ?></label>
				</th>

				<td class="forminp forminp-checkbox">
					<table class="wp-list-table widefat fixed striped ph_selected_tracking_carriers">
						
						<tbody>
							
							<?php

							if( !empty($savedListSettings) && is_array($savedListSettings) ) {

								echo "<tr>";
								echo "<th>".__( 'Shipping Carrier', 'woocommerce-shipment-tracking' )."</th>";
								echo "<th>".__( 'Display Name', 'woocommerce-shipment-tracking' )."</th>";
								echo "<th>".__( 'Live Status', 'woocommerce-shipment-tracking' )."</th>";
								echo "<th>".__( 'Actions', 'woocommerce-shipment-tracking' )."</th>";
								echo "</tr>";

								foreach ($savedListSettings as $carrierKey => $carrier) {

									$height = 185;

									if( $carrierKey == 'united-states-postal-service-usps' ) {

										$height = 300;
									} else if( $carrierKey == 'canada-post' || $carrierKey == 'blue-dart' || $carrierKey == 'delhivery' || $carrierKey == 'dhl-express' ) {

										$height = 350;
									} else if( $carrierKey == 'ups' || $carrierKey == 'australia-post' ) {

										$height = 400;
									} else if( $carrierKey == 'fedex' ) {

										$height = 440;
									} else if( $carrierKey == 'aramex' ) {

										$height = 530;
									} else if( !empty($carrier['url']) ) {

										$height = 220;
									}

									$ajax_url = add_query_arg( 
										array( 
											'action'			=> 'ph_shipment_tracking_edit_carrier',
											'editing_carrier'	=>	$carrierKey,
											'height'			=> 	$height,
											'width'				=> 	525,
											'modal'				=>	'true',
										), 
										'admin-ajax.php'
									);

									$display 	= strtoupper( str_replace('-', ' ', $carrierKey) );
									?>
									<tr>
										<td><?php echo __( $display, 'woocommerce-shipment-tracking' ); ?></td>
										<td><?php echo __( $carrier['name'], 'woocommerce-shipment-tracking' ); ?></td>
										<td>
											<?php 
											if( array_key_exists($carrierKey, $this->liveTrackigEnabled) ) {

												if( $this->liveTrackigEnabled[$carrierKey] ) {
													echo __( 'Enabled', 'woocommerce-shipment-tracking' );
												} else {
													echo __( 'Disabled', 'woocommerce-shipment-tracking' );
												}
											} else {
												echo __( 'N/A', 'woocommerce-shipment-tracking' );
											}
											?>
										</td>
										<td>
											<a href="<?php echo $ajax_url; ?>" title="Edit Tracking Carrier" class="thickbox edit-carrier" style="padding: 5px 3px;">
												<i class="dashicons dashicons-edit"></i>
											</a>
											&nbsp;&nbsp;
											<a href="javascript:void(0)" title="Delete Tracking Carrier" data-id="<?php echo $carrierKey; ?>" class="delete_carrier" style="padding: 5px 3px;">
												<i class="dashicons dashicons-trash"></i>
											</a>
										</td>
										
									</tr>

									<?php
								}

							} else {
								echo '<tr><th colspan="4" style="font-size: small; font-weight: bold;">'.__( "Click on 'Add Carrier' to add Shipping Carrier(s)", 'woocommerce-shipment-tracking' ).'</th></tr>';
							}
							?>
						</tbody>
					</table>
				</td>

			</tr>

			<tr valign="top">

				<th scope="row" class="titledesc" style="width:250px;">
					<label for="custom_page_url"><?php echo  __( 'Tracking Page URL', 'woocommerce-shipment-tracking' ); ?></label>
				</th>

				<td class="forminp forminp-checkbox">
					<input type="text" name="custom_page_url" id="custom_page_url" value="<?php echo $custom_page_url; ?>" style="width:60%"><br/>
					<small><i><?php echo __('1. Create a Custom Page that you want to use for Shipment Tracking <br/>2.  Add the shortcode <b>[ph-shipment-tracking-page]</b> to that page to display Live Tracking Details. <br/>3. Copy the Page’s URL and paste it here.','woocommerce-shipment-tracking'); ?></i></small>
				</td>

			</tr>

			<tr valign="top">

				<th scope="row" class="titledesc" style="width:250px;">
					<label for="live_tracking_cron_enable"><?php echo  __( 'Live Shipment Tracking Notifications', 'woocommerce-shipment-tracking' ); ?></label>
				</th>

				<td class="forminp forminp-checkbox">
					<input type="checkbox" name="live_tracking_cron_enable" id="live_tracking_cron_enable" <?php echo $live_tracking_cron_enable=='yes' ? 'checked' : '';?> >
					<small><i><?php echo __('Enabling this will let customers get Live Tracking Notifications via Email as soon as the Shipping Carrier updates the Tracking Status of the Shipment.','woocommerce-shipment-tracking'); ?></i></small>
				</td>

			</tr>

			<tr valign="top" class="ph_live_tracking_cron_settings">
				
				<th scope="row" class="titledesc" style="width:250px;">
					<label for="from_order_status_to_track"><?php echo  __( 'Select All Orders with Order Status', 'woocommerce-shipment-tracking' ); ?></label>
				</th>

				<td class="forminp forminp-text">
					<select multiple name="from_order_status_to_track[]" id="from_order_status_to_track" style="width:25%">
						<?php $statuses = wc_get_order_statuses();

						$excluded_statuses = array(
							'wc-cancelled',
							'wc-failed',
						);

						foreach ( $statuses as $status => $status_name ) {

							if( !in_array( esc_attr( $status ), $excluded_statuses ) ) {
								echo '<option value="' . esc_attr( $status ) . '" ' . $this->selected_status( $status, $from_order_status_to_track ) . '>' . __( esc_html( $status_name ), 'woocommerce-shipment-tracking' ) . '</option>';
							}
						}
						?>
					</select>

					<small for="prior_to_order_days" style="margin: 0px 25px;"><?php echo  __( 'in Last', 'woocommerce-shipment-tracking' ); ?></small>

					<input name="prior_to_order_days" id="prior_to_order_days" type="number" style="width:25%" value="<?php echo $prior_to_order_days?>" class="prior_to_order_days" placeholder="">
					<small for="prior_to_order_days"><?php echo  __( 'Days', 'woocommerce-shipment-tracking' ); ?></small>
				</td>
			</tr>

			<tr valign="top" class="ph_live_tracking_cron_settings">

				<th scope="row" class="titledesc" style="width:250px;">
					<label for="cron_interval_time"><?php echo  __( 'Repeat Every', 'woocommerce-shipment-tracking' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<input name="cron_interval_time" id="cron_interval_time" type="number" style="width:25%" value="<?php echo $cron_interval_time?>" placeholder="">
					<small for="cron_interval_time"><?php echo  __( 'Minutes', 'woocommerce-shipment-tracking' ); ?></small>
				</td>
			</tr>

			<tr valign="top" class="ph_live_tracking_cron_settings">

				<th scope="row" class="titledesc" style="width:250px;">
					<label for="to_order_status_after_delivery"><?php echo  __( 'After Delivery Change Order Status To', 'woocommerce-shipment-tracking' ); ?></label>
				</th>

				<td class="forminp forminp-radio">
					<select name="to_order_status_after_delivery" id="to_order_status_after_delivery" style="width:25%" class="">
						<option value=""><?php echo  __( 'No Action Required', 'woocommerce-shipment-tracking' ); ?></option>
						<option value="wc-ph-delivered" <?php echo selected( 'wc-ph-delivered', $to_order_status_after_delivery, false ); ?> ><?php echo  __( 'Delivered (Custom)', 'woocommerce-shipment-tracking' ); ?></option>
						<?php 
						$statuses = wc_get_order_statuses();

						$excluded_statuses = array(
							'wc-pending',
							'wc-processing',
							'wc-on-hold',
							'wc-cancelled',
							'wc-refunded',
							'wc-failed',
							'wc-ph-delivered',
						);

						foreach ( $statuses as $status => $status_name ) {

							if( !in_array( esc_attr( $status ), $excluded_statuses ) ) {

								echo '<option value="' . esc_attr( $status ) . '" ' . selected( $status, $to_order_status_after_delivery, false ) . '>' . __( esc_html( $status_name ), 'woocommerce-shipment-tracking' ) . '</option>';
							}
						}
						?>
					</select>
				</td>

			</tr>

			<tr valign="top" class="ph_live_tracking_cron_settings">

				<th scope="row" class="titledesc" style="width:250px;">
					<label for="sender_email_name"><?php echo  __( 'Sender Email Details', 'woocommerce-shipment-tracking' ); ?></label>
				</th>
				<td class="forminp forminp-text">

					<input name="sender_email_name" id="sender_email_name" type="text" style="width:25%" value="<?php echo $sender_email_name?>" placeholder="Name">
					<small for="sender_email_address" style="margin-right: 50px;"><?php echo  __( 'Name', 'woocommerce-shipment-tracking' ); ?></small>

					<input name="sender_email_address" id="sender_email_address" type="email" style="width:25%" value="<?php echo $sender_email_address?>" placeholder="">
					<small for="sender_email_address"><?php echo  __( 'Email', 'woocommerce-shipment-tracking' ); ?></small>
					
				</td>
			</tr>

			<tr valign="top" class="ph_live_tracking_cron_settings">

				<th scope="row" class="titledesc" style="width:250px;">
					<label for="tracking_mail_subject"><?php echo  __( 'Tracking Email Subject', 'woocommerce-shipment-tracking' ); ?></label>
				</th>
				<td class="forminp forminp-text">

					<input name="tracking_mail_subject" id="tracking_mail_subject" type="text" style="width:60%; float: left;" value="<?php !empty($tracking_mail_subject) ? $tracking_mail_subject : $defaultSubject;?>" placeholder="<?php echo $defaultSubject; ?>">
					<div style="float:left;font-size:12px;margin: 7px 0 0 5px;"><i><?php echo  __( 'Supported Tags: ', 'woocommerce-shipment-tracking' ); ?></i><b>[ORDER_NUM]</b> & <b>[TRACKING_ID]</b></div>
				</td>
			</tr>

			<tr valign="top" class="ph_live_tracking_cron_settings">

				<th scope="row" class="titledesc" style="width:250px;">
					<label for="tracking_mail_template"><?php echo __('Tracking Email Template','woocommerce-shipment-tracking'); ?></label>
				</th>
				<td class="forminp forminp-checkbox">
					
					<textarea type="textarea" name="tracking_mail_template" id="tracking_mail_template"  style="width: 60%;height: 120px; float: left" placeholder="<?php echo $defaultTemplate; ?>"><?php echo !empty($tracking_mail_template) ? $tracking_mail_template : '';?></textarea>
					
					<div style="float:left;font-size:12px;margin-left: 3px;">
						<i><?php echo __( 'Use the following Tags to customize your Email Template.','woocommerce-shipment-tracking'); ?></i>
						<br> <b>[CUSTOMER_NAME]</b> - <?php echo __('Customer Name','woocommerce-shipment-tracking'); ?>
						<br> <b>[EMAIL_ID]</b> - <?php echo __('Customer Email Id','woocommerce-shipment-tracking'); ?>
						<br> <b>[ORDER_NUM]</b> - <?php echo __('Order Number','woocommerce-shipment-tracking'); ?>
						<br> <b>[CARRIER_NAME]</b> - <?php echo __('Carrier Name','woocommerce-shipment-tracking'); ?>
						<br> <b>[TRACKING_ID]</b> - <?php echo __('Tracking Id','woocommerce-shipment-tracking'); ?>
						<br> <b>[TRACKING_STATUS]</b> - <?php echo __('Tracking Status','woocommerce-shipment-tracking'); ?>
						<br> <b>[SHIPMENT_PROGRESS]</b> - <?php echo __('Tracking History in Table Format','woocommerce-shipment-tracking'); ?>
					</div>
				</td>
			</tr>

			<tr valign="top">

				<th scope="row" class="titledesc" style="width:250px;">
					<label for="tracking_to_customer"><?php echo  __( 'Tracking Details To Customer', 'woocommerce-shipment-tracking' ); ?></label>
				</th>

				<td class="forminp forminp-checkbox">
					<input type="checkbox" name="tracking_to_customer" id="tracking_to_customer" <?php echo $tracking_to_customer=='yes' ? 'checked' : '';?> >
					<small><i><?php echo __('Display Tracking Details on customers My account Order page','woocommerce-shipment-tracking'); ?></i></small>
				</td>

			</tr>

			<tr valign="top">

				<th scope="row" class="titledesc" style="width:250px;">
					<label for="turn_on_api"><?php echo  __( 'Live Tracking in My Account', 'woocommerce-shipment-tracking' ); ?></label>
				</th>

				<td class="forminp forminp-checkbox">
					<input type="checkbox" name="turn_on_api" id="turn_on_api" <?php echo $turn_on_api=='yes' ? 'checked' : '';?> >
					<small><i><?php echo __('Display Live Tracking status on customers My Account Order page.','woocommerce-shipment-tracking'); ?></i></small>
				</td>

			</tr>

			<tr valign="top">

				<th scope="row" class="titledesc" style="width:250px;">
					<label for="auto_refresh"><?php echo  __( 'Automatic Refresh', 'woocommerce-shipment-tracking' ); ?></label>
				</th>

				<td class="forminp forminp-radio">
					<select name="auto_refresh" id="auto_refresh" style="width:25%">
						<?php 
						$refresh_options = array(
							'enable'	=> __( 'Enable', 'woocommerce-shipment-tracking' ),
							'disable' 	=> __( 'Disable', 'woocommerce-shipment-tracking' ),
						);
						foreach ( $refresh_options as $option => $option_name ) {
							echo '<option value="' . esc_attr( $option ) . '" ' . selected( $option, $auto_refresh, false ) . '>' . esc_html( $option_name ) . '</option>';
						}
						?>
					</select><br/>
					<small><i><?php echo  __( 'Enable - It will automatically refresh the Shipment Tracking status on My Account > Orders page.<br/>Disable - It will display a Refresh button on the My Account > Orders page that will refresh the tracking status manually.', 'woocommerce-shipment-tracking' ); ?></i></small>
				</td>

			</tr>

			<tr valign="top">

				<th scope="row" class="titledesc" style="width:250px;">
					<label for="tracking_to_mail"><?php echo  __( 'Send Tracking details via Email', 'woocommerce-shipment-tracking' ); ?></label>
				</th>

				<td class="forminp forminp-checkbox">
					<input type="checkbox" name="tracking_to_mail" id="tracking_to_mail" <?php echo $tracking_to_mail=='yes' ? 'checked' : '';?> >
					<small><i><?php echo __('Send Tracking Details to customers via WooCommerce Order Completion Email','woocommerce-shipment-tracking'); ?></i></small>
				</td>

			</tr>

			<tr valign="top">

				<th scope="row" class="titledesc" style="width:250px;">
					<label for="custom_message"><?php echo __('Custom Shipment Message','woocommerce-shipment-tracking'); ?></label>
				</th>
				<td class="forminp forminp-checkbox">
					
					<textarea type="textarea" name="custom_message" id="custom_message"  style="width: 60%;" placeholder="<?php echo Ph_Shipment_Tracking_Util::get_default_shipment_message_placeholder(); ?>"><?php echo !empty($custom_message) ? $custom_message : '';?></textarea>
					<br/><small><i><?php echo __('Define your own shipment message. Use the place holder tags [ID], [SERVICE] and [DATE] for Shipment Id, Shipment Service and Shipment Date respectively.','woocommerce-shipment-tracking'); ?></i></small>
				</td>
			</tr>

			<tr valign="top">

				<th scope="row" class="titledesc" colspan="2" style="padding: 0px;">
					<h4 style="font-size: 1.2em"><?php echo  __( 'Third Party Tracking Integration', 'woocommerce-shipment-tracking' ); ?></h4>
				</th>

			</tr>

			<tr valign="top">

				<th scope="row" class="titledesc" style="width:250px;">
					<label for="ups_integration"><?php echo  __( ' PluginHive UPS', 'woocommerce-shipment-tracking' ); ?></label>
				</th>

				<td class="forminp forminp-checkbox">
					<input type="checkbox" name="ups_integration" id="ups_integration" <?php echo $ups_integration=='yes' ? 'checked' : '';?> >
					<small><i><?php echo __('This will sync Tracking Details to the orders. Make sure to Disable Shipment Tracking for Customer within UPS plugin to avoid displaying tracking details twice.','woocommerce-shipment-tracking'); ?></i></small>
				</td>

			</tr>

			<tr valign="top">

				<th scope="row" class="titledesc" style="width:250px;">
					<label for="fedex_integration"><?php echo  __( ' PluginHive FedEx', 'woocommerce-shipment-tracking' ); ?></label>
				</th>

				<td class="forminp forminp-checkbox">
					<input type="checkbox" name="fedex_integration" id="fedex_integration" <?php echo $fedex_integration=='yes' ? 'checked' : '';?> >
					<small><i><?php echo __('This will sync Tracking Details to the orders. Make sure to Disable Shipment Tracking for Customer within FedEx plugin to avoid displaying tracking details twice.','woocommerce-shipment-tracking'); ?></i></small>
				</td>

			</tr>

			<tr valign="top">

				<th scope="row" class="titledesc" style="width:250px;">
					<label for="shipping_easy"><?php echo  __( 'ShippingEasy', 'woocommerce-shipment-tracking' ); ?></label>
				</th>

				<td class="forminp forminp-checkbox">
					<input type="checkbox" name="shipping_easy" id="shipping_easy" <?php echo $shipping_easy=='yes' ? 'checked' : '';?> >
					<small><i><?php echo __('This will sync Tracking Details from ShippingEasy to your WooCommerce Orders.','woocommerce-shipment-tracking'); ?></i></small>
				</td>

			</tr>

			<tr valign="top">

				<th scope="row" class="titledesc" style="width:250px;">
					<label for="go_shippo"><?php echo  __( 'Shippo', 'woocommerce-shipment-tracking' ); ?></label>
				</th>

				<td class="forminp forminp-checkbox">
					<input type="checkbox" name="go_shippo" id="go_shippo" <?php echo $go_shippo=='yes' ? 'checked' : '';?> >
					<small><i><?php echo __('This will sync Tracking Details from Shippo to your WooCommerce Orders.','woocommerce-shipment-tracking'); ?></i></small>
				</td>

			</tr>

		</tbody>

	</table>

	<p class="submit">
		<button name="ph_save_tracking_settings" class="button-primary woocommerce-save-button" type="submit" value="Save changes"><?php echo  __( 'Save changes', 'woocommerce-shipment-tracking' )?></button>		
	</p>
</form>
<div id="add-new-carrier" style="display:none;">

	<div id="a" name="a" style="margin: auto; display: grid; overflow: hidden;text-align: center;">

		<br/>

		<table class="new_carrier_details" cellpadding="5">

			<tbody>

				<tr>
					<th style="text-align:left;width:33%"><i><?php echo  __( 'Shipping Carrier', 'woocommerce-shipment-tracking' ); ?></i></th>
					<td>
						<select id="ph_tracking_carrier" name="ph_tracking_carrier" style="width: 100%;">
							<option value="" ><?php echo  __( '- Select -', 'woocommerce-shipment-tracking' ); ?></option>
							<option value="custom-carrier" ><?php echo  __( 'Add Custom Carrier', 'woocommerce-shipment-tracking' ); ?></option>
							<?php 
							foreach ( $this->tracking_data as $trackingValue => $trackingContent ) {
								echo '<option value="' . esc_attr( $trackingValue ) . '" >' . esc_html( strtoupper($trackingContent['name']) ) . '</option>';
							}
							?>
						</select>
					</td>
				</tr>

			</tbody>

			<tfoot>
				<tr>
					<th colspan="2">
						<br/>
						<input type="submit" name="add_carrier" id="add_carrier" class="button button-primary add_carrier" value="Add Carrier" style="width:25%; margin: auto 2px;" />
						<input type="submit" name="remove_modal" id="remove_modal" class="button remove_modal" value="Cancel" style="width:25%; margin: auto 2px;"/>
					</th>
				</tr>
			</tfoot>

		</table>

	</div>
</div>
<script type="text/javascript">
	
	ph_show_carrier_credential_settings();

	jQuery('#ph_tracking_carrier').change(function() {

		ph_adjust_add_carrier_height();
		ph_show_carrier_credential_settings();
	});

	function ph_adjust_add_carrier_height() {

		var TB_HEIGHT = 185;

		selectedCarrier 	= jQuery("#ph_tracking_carrier").val();

		if( selectedCarrier == 'united-states-postal-service-usps' ) {
			TB_HEIGHT = 300;
		}else if( selectedCarrier == 'canada-post' || selectedCarrier == 'blue-dart' || selectedCarrier == 'delhivery' || selectedCarrier == 'dhl-express' ) {
			TB_HEIGHT = 350;
		}else if( selectedCarrier == 'ups' || selectedCarrier == 'australia-post' ) {
			TB_HEIGHT = 400;
		}else if( selectedCarrier == 'fedex' ) {
			TB_HEIGHT = 440;
		}else if( selectedCarrier == 'aramex' ) {
			TB_HEIGHT = 530;
		}else if( selectedCarrier == 'custom-carrier' ) {
			TB_HEIGHT = 220;
		}

		jQuery('#TB_ajaxContent').css('height', TB_HEIGHT + 'px');

		TB_HEIGHT = TB_HEIGHT + 30;

		jQuery("#TB_window").animate({
			height: TB_HEIGHT + 'px',
		});

	}

	function ph_show_carrier_credential_settings() {

		var availableCarriers = {
			'united-states-postal-service-usps' : 'United States Postal Service (USPS)',
			'ups' 								: 'UPS',
			'canada-post' 						: 'Canada Post',
			'fedex'								: 'FedEx',
			'blue-dart' 						: 'Blue Dart',
			'australia-post' 					: 'Australia Post',
			'delhivery' 						: 'Delhivery',
			'dhl-express' 						: 'DHL Express',
			'dhl-usa' 							: 'DHL USA',
			'dhl-global' 						: 'DHL Global',
			'dhl-parcel-belgium' 				: 'DHL Parcel Belgium',
			'bpost-belgium' 					: 'Bpost Belgium',
			'ontrac'							: 'OnTrac',
			'icc-world' 						: 'ICC World',
			'royal-mail'						: 'Royal Mail',
			'parcel-force' 						: 'Parcel Force',
			'tnt-consignment' 					: 'TNT (Consignment)',
			'tnt-reference'						: 'TNT (Reference)',
			'yrc-freight'						: 'YRC Freight',
			'yrc-regional' 						: 'YRC Regional',
			'db-schenker' 						: 'DB Schenker',
			'roadrunner'						: 'Roadrunner',
			'dpd-de' 							: 'dpd (DE)',
			'aramex'							: 'Aramex',
			'dsv'								: 'DSV',
			'canpar-courier' 					: 'Canpar Courier',
			'purolator'							: 'Purolator',
			'asendia-usa'						: 'ASENDIA (USA)',
			'lasership'							: 'LaserShip',
			'i-parcel-ups' 						: 'i-parcel (UPS)',
			'abf-com' 							: 'ABF.com',
			'estes-express' 					: 'ESTES Express',
			'rl-carriers' 						: 'RL Carriers',
			'skynet-worldwide-express'			: 'SkyNet Worldwide Express',
			'globegistics' 						: 'Globegistics',
			'old-dominion' 						: 'Old Dominion',
			'saia' 								: 'SAIA',
			'ceva-logistics' 					: 'CEVA Logistics',
			'india-post' 						: 'India Post',
			'con-way-freight' 					: 'Con-Way Freight',
			'averitt-express'					: 'Averitt Express',
			'colis-prive-adrexo' 				: 'Colis Prive (Adrexo)',
			'freightquote'						: 'FreightQuote',
			'correios'							: 'Correios',
			'the-professional-couriers' 		: 'The Professional Couriers',
			'japan-post' 						: 'Japan Post',
			'yodel-direct' 						: 'Yodel Direct',
			'collect' 							: 'Collect+',
			'apc-overnight'						: 'APC Overnight',
			'interlink-express-1' 				: 'Interlink Express (1)',
			'interlink-express-2' 				: 'Interlink Express (2)',
			'uk-mail'							: 'UK Mail',
			'hermesworld' 						: 'Hermesworld',
			'myhermes-uk' 						: 'myHermes (UK)',
			'fastway-couriers' 					: 'Fastway Couriers',
			'posti'								: 'Posti',
			'2go'								: '2GO',
			'fedex-sameday'						: 'FedEx SameDay',
			'postnord'							: 'Postnord',
			'pbt-couriers'						: 'PBT Couriers',
			'new-zealand-post' 					: 'New Zealand Post',
			'courierpost'						: 'CourierPost',
			'postnl'							: 'PostNL',
			'dpd-nl'							: 'DPD (NL)',
			'gojavas'							: 'Gojavas',
			'deutsche-post-dhl'					: 'Deutsche Post (DHL)',
			'dhl-intraship-de'					: 'DHL Intraship (DE)',
			'colissimo'							: 'Colissimo',
			'dpd-cz'							: 'DPD (CZ)',
			'dhl-cz'							: 'DHL (CZ)',
			'posta-cz'							: 'Posta (CZ)',
			'ppl-cz'							: 'PPL (CZ)',
			'post-ag'							: 'Post AG',
			'postnl-02'							: 'PostNL (02)',
			'stamps-com-usps'					: 'Stamps.com (USPS)',
			'ctt-expresso'						: 'CTT Expresso',
			'giaohangnhanh'						: 'Giaohangnhanh',
			'asm-es'							: 'ASM (ES)',
			'tourline-express-es'				: 'Tourline express (ES)',
			'correos-express'					: 'Correos Express',
			'mrw-es'							: 'MRW (ES)',
			'la-poste'							: 'La Poste',
			'custom-carrier'					: '',
		}

		jQuery('.appendedTracking').closest('tr').remove();

		selectedCarrier 	= jQuery("#ph_tracking_carrier").val();
		selectedCarrierName = availableCarriers[selectedCarrier];
		trackingData 		= <?php echo json_encode($this->tracking_data); ?>;
		savedList 	 		= <?php echo json_encode($savedListSettings); ?>;
		customUrl 			= '';

		if( selectedCarrier && !(selectedCarrier in availableCarriers) ) {

			if(selectedCarrier in savedList) {
				selectedCarrierName 	= savedList[selectedCarrier]['name'];
			} else {
				selectedCarrierName 	= trackingData[selectedCarrier]['name'];
			}

			customUrl = trackingData[selectedCarrier]['tracking_url'];
		}

		uspsUserid				= '<?php echo $usps_userid; ?>';
		upsUserid				= '<?php echo $ups_userid; ?>';
		upsPassword				= '<?php echo $ups_password; ?>';
		upsAccessKey			= '<?php echo $ups_access_key; ?>';
		canadapostUserid		= '<?php echo $canadapost_userid; ?>';
		canadapostPassword		= '<?php echo $canadapost_password; ?>';
		fedexAccount			= '<?php echo $fedex_account; ?>';
		fedexMeterNum			= '<?php echo $fedex_meter_num; ?>';
		fedexServiceKey			= '<?php echo $fedex_service_key; ?>';
		fedexServicePass		= '<?php echo $fedex_service_pass; ?>';
		bluedartUserid			= '<?php echo $bluedart_userid; ?>';
		bluedartApiKey			= '<?php echo $bluedart_api_key; ?>';
		delhiveryUserid			= '<?php echo $delhivery_userid; ?>';
		delhiveryApiKey			= '<?php echo $delhivery_api_key; ?>';
		dhlSiteid				= '<?php echo $dhl_siteid; ?>';
		dhlApiKey				= '<?php echo $dhl_api_key; ?>';
		auAccountNum			= '<?php echo $au_account_num; ?>';
		auApiKey				= '<?php echo $au_api_key; ?>';
		auPassword				= '<?php echo $au_password; ?>';
		aramexUserName			= '<?php echo $aramex_user_name; ?>';
		aramexPassword			= '<?php echo $aramex_password; ?>';
		aramexAccountNum		= '<?php echo $aramex_account_num; ?>';
		aramexAccountPin		= '<?php echo $aramex_account_pin; ?>';
		aramexEntity			= '<?php echo $aramex_entity; ?>';
		aramexCountryCode		= '<?php echo $aramex_country_code; ?>';

		htmlCode 				= '';

		if( selectedCarrier ) {

			htmlCode 	= '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>Display Name</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="text" name="ph_carrier_custom_name" id="ph_carrier_custom_name" value="'+selectedCarrierName+'" placeholder="Carrier Name" required></td>';
			htmlCode	= htmlCode + '<tr>';
		}

		if( selectedCarrier == 'custom-carrier' || ( selectedCarrier && !(selectedCarrier in availableCarriers) ) ) {

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>Tracking URL</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="text" name="ph_custom_carrier_url" id="ph_custom_carrier_url" value="'+customUrl+'" placeholder="Tracking URL" required></td>';
			htmlCode	= htmlCode + '<tr>';
		}

		if( selectedCarrier == 'united-states-postal-service-usps' ) {

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th colspan="2"><hr/><br/>Enter your USPS Account Credentials for Live Tracking (optional)<br/><br/></th>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>User Id</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="text" name="ph_usps_user_id" id="ph_usps_user_id" value="'+uspsUserid+'" placeholder="User Id"></td>';
			htmlCode	= htmlCode + '<tr>';

		}

		if( selectedCarrier == 'ups' ) {

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th colspan="2"><hr/><br/>Enter your UPS Account Credentials for Live Tracking (optional)<br/><br/></th>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>User Id</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="text" name="ph_ups_user_id" id="ph_ups_user_id" value="'+upsUserid+'" placeholder="User Id"></td>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>Password</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_ups_password" id="ph_ups_password" value="'+upsPassword+'" placeholder="Password"></td>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>Access Key</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_ups_access_key" id="ph_ups_access_key" value="'+upsAccessKey+'" placeholder="Access Key"></td>';
			htmlCode	= htmlCode + '<tr>';

		}

		if( selectedCarrier == 'canada-post' ) {

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th colspan="2"><hr/><br/>Enter your Canada Post Account Credentials for Live Tracking (optional)<br/><br/></th>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>User Id</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="text" name="ph_canadapost_user_id" id="ph_canadapost_user_id" value="'+canadapostUserid+'" placeholder="User Id"></td>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>Password</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_canadapost_user_password" id="ph_canadapost_user_password" value="'+canadapostPassword+'" placeholder="Password"></td>';
			htmlCode	= htmlCode + '<tr>';
		}

		if( selectedCarrier == 'fedex' ) {

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th colspan="2"><hr/><br/>Enter your FedEx Account Credentials for Live Tracking (optional)<br/><br/></th>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>Account Number</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="text" name="ph_fedex_account_number" id="ph_fedex_account_number" value="'+fedexAccount+'" placeholder="Account Number"></td>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>Meter Number</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="text" name="ph_fedex_meter_number" id="ph_fedex_meter_number" value="'+fedexMeterNum+'" placeholder="Meter Number"></td>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>Web Services Key</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_fedex_web_service_key" id="ph_fedex_web_service_key" value="'+fedexServiceKey+'" placeholder="Web Services Key"></td>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>Web Services Password</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_fedex_web_service_password" id="ph_fedex_web_service_password" value="'+fedexServicePass+'" placeholder="Web Services Password"></td>';
			htmlCode	= htmlCode + '<tr>';

		}

		if( selectedCarrier == 'blue-dart' ) {

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th colspan="2"><hr/><br/>Enter your Blue Dart Account Credentials for Live Tracking (optional)<br/><br/></th>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>User Id</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="text" name="ph_bluedart_user_id" id="ph_bluedart_user_id" value="'+bluedartUserid+'" placeholder="User Id"></td>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>API Key</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_bluedart_api_key" id="ph_bluedart_api_key" value="'+bluedartApiKey+'" placeholder="API Key"></td>';
			htmlCode	= htmlCode + '<tr>';
		}

		if( selectedCarrier == 'delhivery' ) {

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th colspan="2"><hr/><br/>Enter your Delhivery Account Credentials for Live Tracking (optional)<br/><br/></th>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>User Id</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="text" name="ph_delhivery_user_id" id="ph_delhivery_user_id" value="'+delhiveryUserid+'" placeholder="User Id"></td>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>API Key</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_delhivery_api_key" id="ph_delhivery_api_key" value="'+delhiveryApiKey+'" placeholder="API Key"></td>';
			htmlCode	= htmlCode + '<tr>';
		}

		if( selectedCarrier == 'dhl-express' ) {

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th colspan="2"><hr/><br/>Enter your DHL Express Account Credentials for Live Tracking (optional)<br/><br/></th>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>Site Id</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="text" name="ph_dhlexpress_site_id" id="ph_dhlexpress_site_id" value="'+dhlSiteid+'" placeholder="Site Id"></td>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>Password</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_dhlexpress_api_key" id="ph_dhlexpress_api_key" value="'+dhlApiKey+'" placeholder="Password"></td>';
			htmlCode	= htmlCode + '<tr>';
		}

		if( selectedCarrier == 'australia-post' ) {

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th colspan="2"><hr/><br/>Enter your Australia Post Account Credentials for Live Tracking (optional)<br/><br/></th>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>Account Number</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="text" name="ph_au_account_num" id="ph_au_account_num" value="'+auAccountNum+'" placeholder="Account Number"></td>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>API Key</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_au_api_key" id="ph_au_api_key" value="'+auApiKey+'" placeholder="API Key"></td>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>API Password</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_au_password" id="ph_au_password" value="'+auPassword+'" placeholder="API Password"></td>';
			htmlCode	= htmlCode + '<tr>';
		}

		if( selectedCarrier == 'aramex' ) {

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th colspan="2"><hr/><br/>Enter your Aramex Credentials for Live Tracking (optional)<br/><br/></th>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>User Name</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="text" name="ph_aramex_username" id="ph_aramex_username" value="'+aramexUserName+'" placeholder="User Name"></td>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>Password</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_aramex_password" id="ph_aramex_password" value="'+aramexPassword+'" placeholder="Password"></td>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>Account Number</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="text" name="ph_aramex_accountnum" id="ph_aramex_accountnum" value="'+aramexAccountNum+'" placeholder="Account Number"></td>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>Account Pin</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="password" autocomplete="off" name="ph_aramex_accountpin" id="ph_aramex_accountpin" value="'+aramexAccountPin+'" placeholder="Account Pin"></td>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>Account Entity</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="text" name="ph_aramex_accountentity" id="ph_aramex_accountentity" value="'+aramexEntity+'" placeholder="AMM"></td>';
			htmlCode	= htmlCode + '<tr>';

			htmlCode 	= htmlCode + '<tr class="appendedTracking">';
			htmlCode	= htmlCode + '<th style="text-align:left;width:33%"><i>Account Country Code</i></th>';
			htmlCode 	= htmlCode + '<td><input style="width: 100%;" type="text" name="ph_aramex_countrycode" id="ph_aramex_countrycode" maxlength="2" value="'+aramexCountryCode+'" placeholder="Country Code"></td>';
			htmlCode	= htmlCode + '<tr>';
		}

		jQuery('.new_carrier_details').find('tbody').append( htmlCode );
	}
</script>
<style type="text/css">
	
	.required_field {
		border: 2px solid red !important;
	}

	.ph_selected_tracking_carriers {
		width: 50% !important;
		background-color: white;
	}

	.ph_selected_tracking_carriers tr {

		border-bottom: 1px solid grey;
	}

	.ph_selected_tracking_carriers tr th {
		text-align: center;
		padding: 5px !important;
	}

	.ph_selected_tracking_carriers tr td {
		text-align: center;
		padding: 7px !important;
	}

	.notice {
		display: none;
	}

</style>