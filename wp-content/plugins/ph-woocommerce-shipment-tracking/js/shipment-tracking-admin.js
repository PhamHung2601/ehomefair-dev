jQuery(document).ready(function(){

	jQuery("#ph_shipment_tracking_get_store_id").click( function(){

		access_key 	= jQuery('#wf_tracking_access_token_id').val();

		if( !access_key )
		{
			alert("Please enter the the Access Key.");
			return;
		}

		// Disable the Button
		jQuery("#ph_shipment_tracking_get_store_id").attr( 'disabled', 'disabled');

		current_date 	= new Date().toISOString();
		
		let key_data = {
			action 		: 'ph_shipment_tracking_get_store_id',
			access_key 	: access_key,
			current_date: current_date,
		}

		jQuery.post( ajaxurl, key_data, function( result, status ){

			console.log(result);

			try{

				let response = JSON.parse(result);

				if( response.Status == true )
				{
					alert("Successfully Connected.");

					jQuery("#ph_shipment_tracking_get_store_id").closest('tr').hide();

				}else{
					alert(response.Message);
				}
			}
			catch(err) {
				alert(err.message);
			}

			jQuery("#ph_shipment_tracking_get_store_id").removeAttr("disabled");
			
			
		})
	});

	jQuery(".ph_order_packages").click( function(){

		jQuery( ".shipment-tracking_page_pluginhive-orders").block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.5
			}
		});

		var orderUUID = jQuery(this).data("id");
		var thisOrder = jQuery(this).closest('tr');
		
		if( !orderUUID )
		{
			alert("orderUUID is missing for this Order Id");
			return;
		}
		
		let key_data = {
			action 		: 'ph_shipment_tracking_get_order_packages',
			orderUUID 	: orderUUID,
		}

		jQuery.post( ajaxurl, key_data, function( result, status ){

			try{

				let response = JSON.parse(result);

				if( response.Status == true && response.Packages[0] )
				{

					html = "<tr id="+response.Packages[0].phTrackingNumber +"><td colspan='5'><table class='wp-list-table widefat striped ph_order_tracking' width='100%'><thead><tr><th>Tracking Number</th><th>Last Checkpoint</th><th>Expected Delivery</th><th>Tracking Status</th><th>Tracking Information</th></tr></thead>";
					jQuery.each( response.Packages, function( i, tracking ) {

						if( !tracking.carrierTrackingId || tracking.carrierTrackingId == 'undefined' )
						{
							carrierTrackingId = '---';
						}else{
							carrierTrackingId = tracking.carrierTrackingId;
						}

						if( !tracking.status || tracking.status == 'undefined' )
						{
							status = '---';
						}else{
							status = tracking.status;
						}

						if( !tracking.estimatedDeliveryDate || tracking.estimatedDeliveryDate == 'undefined' )
						{
							estimatedDeliveryDate = '---';
						}else{
							
							months 			= ["January", "February", "March", "April", "May", "June","July", "August", "September", "October", "November", "December"];
							new_date 		= new Date(tracking.estimatedDeliveryDate);
							display_date 	= tracking.estimatedDeliveryDate;

							switch(ph_shipment_tracking_admin_js.wpDateFormat){

								case 'F j, Y':display_date =  months[new_date.getMonth()]+' '+new_date.getDate()+', '+new_date.getFullYear();
								break;
								case 'Y-m-d' :display_date = new_date.getFullYear()+'-'+(new_date.getMonth()+1)+'-'+new_date.getDate();
								break;
								case 'm/d/Y': display_date = (new_date.getMonth()+1)+'/'+new_date.getDate()+'/'+new_date.getFullYear();
								break;
								case 'd/m/Y': display_date = new_date.getDate()+'-'+(new_date.getMonth()+1)+'-'+new_date.getFullYear();
								break;
								default: display_date = new_date.getFullYear()+'-'+(new_date.getMonth()+1)+'-'+new_date.getDate();
								break;
							}
							estimatedDeliveryDate = display_date;
						}

						if( !tracking.lastTrackingStatus || tracking.lastTrackingStatus == 'undefined' )
						{
							trackLocation = '---';
							summary  = '---';
						}else{
							if( !tracking.lastTrackingStatus.location || tracking.lastTrackingStatus.location == 'undefined' )
							{
								trackLocation 	= '---';
							}else{
								trackLocation = tracking.lastTrackingStatus.location;
							}

							if( !tracking.lastTrackingStatus.summary || tracking.lastTrackingStatus.summary == 'undefined' )
							{
								summary = '---';
							}else{
								summary = tracking.lastTrackingStatus.summary;
							}
						}

						if( !tracking.phTrackingNumber || tracking.phTrackingNumber == 'undefined' )
						{
							phTrackingNumber = '';
						}else{
							phTrackingNumber = tracking.phTrackingNumber;
						}

						html += "<tr>";
						
						if( ph_shipment_tracking_admin_js.urlLink && ph_shipment_tracking_admin_js.urlLink !== 'NA' && phTrackingNumber)
						{
							html += "<td><a href='"+ph_shipment_tracking_admin_js.urlLink+"?idType=phtrackingId&tracking_number="+phTrackingNumber+"' target='_BLANK' class='ph_tracking_history_details'><strong>"+carrierTrackingId+"</strong></a></td>";
						}else{
							html += "<td><strong>"+carrierTrackingId+"</strong></a></td>";
						}

						html += "<td>"+trackLocation+"</td><td>"+estimatedDeliveryDate+"</td><td>"+status+"</td><td>"+summary+"</td>";

						html += "</tr>";
					});

					html += "</table></td></tr>";

					var rowId = document.getElementById(response.Packages[0].phTrackingNumber);

					if(!rowId)
					{
						jQuery(html).insertAfter(thisOrder);
					}else{
						jQuery("#"+response.Packages[0].phTrackingNumber).remove();
					}

					jQuery( ".shipment-tracking_page_pluginhive-orders").unblock({
						message: null,
						overlayCSS: {}
					});

				}else{

					jQuery( ".shipment-tracking_page_pluginhive-orders").unblock({
						message: null,
						overlayCSS: {}
					});

					alert(response.Message);
				}
			}
			catch(err) {

				jQuery( ".shipment-tracking_page_pluginhive-orders").unblock({
					message: null,
					overlayCSS: {}
				});

				alert(err.message);
			}
			
		})
	});
});

jQuery(document).ready(function(){

	jQuery("#from_order_status_to_track").select2();

	cronEnabled = jQuery("input[name='live_tracking_cron_enable']:checked").val();

	if( cronEnabled !== 'on' ) {
		jQuery( '.ph_live_tracking_cron_settings' ).closest('tr').hide();
	}

	jQuery('#live_tracking_cron_enable').click(function() {

		checked = jQuery("input[name='live_tracking_cron_enable']:checked").val();

		if( checked !== 'on' ) {
			jQuery( '.ph_live_tracking_cron_settings' ).closest('tr').hide();
		} else{
			jQuery( '.ph_live_tracking_cron_settings' ).closest('tr').show();
		}
	});

	liveTracking = jQuery("input[name='turn_on_api']:checked").val();

	if( liveTracking !== 'on' ) {
		jQuery( '#auto_refresh' ).closest('tr').hide();
	}

	jQuery('#turn_on_api').click(function() {

		checked = jQuery("input[name='turn_on_api']:checked").val();

		if( checked !== 'on' ) {
			jQuery( '#auto_refresh' ).closest('tr').hide();
		} else{
			jQuery( '#auto_refresh' ).closest('tr').show();
		}
	});

	jQuery("#add_carrier").click( function(e) {

		var selectedCarrier 	= jQuery("#ph_tracking_carrier").val();
		var customName 			= jQuery("#ph_carrier_custom_name").val();
		var customURL 			= jQuery("#ph_custom_carrier_url").val();

		if( !customName ) {
			
			jQuery('#ph_carrier_custom_name').addClass('required_field');
			return false;
		}
		
		if( selectedCarrier == 'custom-carrier' && !customURL ) {

			jQuery('#ph_custom_carrier_url').addClass('required_field');
			return false;
		}

		uspsUserId 		 	= jQuery("#ph_usps_user_id").val();

		upsUserId 		 	= jQuery("#ph_ups_user_id").val();
		upsPassword 		= jQuery("#ph_ups_password").val();
		upsAccessKey 		= jQuery("#ph_ups_access_key").val();

		cpUserId 		 	= jQuery("#ph_canadapost_user_id").val();
		cpPassword 		 	= jQuery("#ph_canadapost_user_password").val();

		fedexAccount 		= jQuery("#ph_fedex_account_number").val();
		fedexMeterNumber 	= jQuery("#ph_fedex_meter_number").val();
		fedexServiceKey  	= jQuery("#ph_fedex_web_service_key").val();
		fedexServicePassword= jQuery("#ph_fedex_web_service_password").val();

		bluedartUserId 		= jQuery("#ph_bluedart_user_id").val();
		bluedartApiKey 		= jQuery("#ph_bluedart_api_key").val();

		delhiveryUserId 	= jQuery("#ph_delhivery_user_id").val();
		delhiveryApiKey 	= jQuery("#ph_delhivery_api_key").val();

		dhlSiteId 			= jQuery("#ph_dhlexpress_site_id").val();
		dhlApiKey 			= jQuery("#ph_dhlexpress_api_key").val();

		auAccountNum 		= jQuery("#ph_au_account_num").val();
		auAPIKey 			= jQuery("#ph_au_api_key").val();
		auPassword 			= jQuery("#ph_au_password").val();

		aramexUserName 		= jQuery("#ph_aramex_username").val();
		aramexPassword 		= jQuery("#ph_aramex_password").val();
		aramexAccountNum 	= jQuery("#ph_aramex_accountnum").val();
		aramexAccountPin 	= jQuery("#ph_aramex_accountpin").val();
		aramexEntity 		= jQuery("#ph_aramex_accountentity").val();
		aramexCountryCode 	= jQuery("#ph_aramex_countrycode").val();

		let key_data = {

			action 				: 'ph_shipment_tracking_save_carrier',
			selected_carrier 	: selectedCarrier,
			custom_name 	 	: customName,
			custom_url 			: customURL,
			usps_userid 		: uspsUserId,
			ups_userid 			: upsUserId,
			ups_password 		: upsPassword,
			ups_access_key 		: upsAccessKey,
			canadapost_userid 	: cpUserId,
			canadapost_password : cpPassword,
			fedex_account 		: fedexAccount,
			fedex_meter_num 	: fedexMeterNumber,
			fedex_service_key 	: fedexServiceKey,
			fedex_service_pass 	: fedexServicePassword,
			bluedart_userid 	: bluedartUserId,
			bluedart_api_key 	: bluedartApiKey,
			delhivery_userid 	: delhiveryUserId,
			delhivery_api_key 	: delhiveryApiKey,
			dhl_siteid 			: dhlSiteId,
			dhl_api_key 		: dhlApiKey,
			au_account_num 		: auAccountNum,
			au_api_key 			: auAPIKey,
			au_password 		: auPassword,
			aramex_user_name	: aramexUserName,
			aramex_password 	: aramexPassword,
			aramex_account_num 	: aramexAccountNum,
			aramex_account_pin  : aramexAccountPin,
			aramex_entity 		: aramexEntity,
			aramex_country_code : aramexCountryCode,
			
		}

		ph_save_carrier_details( key_data );

		return false;
	});

	jQuery("#edit_carrier").click( function() {

		var customName 	= jQuery('#ph_edit_carrier_custom_name').val();
		var customURL 	= jQuery('#ph_edit_custom_carrier_url').val();
		
		if( !customName ) {
			
			jQuery('#ph_edit_carrier_custom_name').addClass('required_field');
			return false;
		}

		selectedCarrier 	= jQuery("#ph_edit_tracking_carrier").val();
		customName 		 	= jQuery("#ph_edit_carrier_custom_name").val();
		customURL 		 	= jQuery("#ph_edit_custom_carrier_url").val();

		uspsUserId 		 	= jQuery("#ph_edit_usps_user_id").val();

		upsUserId 		 	= jQuery("#ph_edit_ups_user_id").val();
		upsPassword 		= jQuery("#ph_edit_ups_password").val();
		upsAccessKey 		= jQuery("#ph_edit_ups_access_key").val();

		cpUserId 		 	= jQuery("#ph_edit_canadapost_user_id").val();
		cpPassword 		 	= jQuery("#ph_edit_canadapost_user_password").val();

		fedexAccount 		= jQuery("#ph_edit_fedex_account_number").val();
		fedexMeterNumber 	= jQuery("#ph_edit_fedex_meter_number").val();
		fedexServiceKey  	= jQuery("#ph_edit_fedex_web_service_key").val();
		fedexServicePassword= jQuery("#ph_edit_fedex_web_service_password").val();

		bluedartUserId 		= jQuery("#ph_edit_bluedart_user_id").val();
		bluedartApiKey 		= jQuery("#ph_edit_bluedart_api_key").val();

		delhiveryUserId 	= jQuery("#ph_edit_delhivery_user_id").val();
		delhiveryApiKey 	= jQuery("#ph_edit_delhivery_api_key").val();

		dhlSiteId 			= jQuery("#ph_edit_dhlexpress_site_id").val();
		dhlApiKey 			= jQuery("#ph_edit_dhlexpress_api_key").val();

		auAccountNum 		= jQuery("#ph_edit_au_account_num").val();
		auAPIKey 			= jQuery("#ph_edit_au_api_key").val();
		auPassword 			= jQuery("#ph_edit_au_password").val();

		aramexUserName 		= jQuery("#ph_edit_aramex_username").val();
		aramexPassword 		= jQuery("#ph_edit_aramex_password").val();
		aramexAccountNum 	= jQuery("#ph_edit_aramex_accountnum").val();
		aramexAccountPin 	= jQuery("#ph_edit_aramex_accountpin").val();
		aramexEntity 		= jQuery("#ph_edit_aramex_accountentity").val();
		aramexCountryCode 	= jQuery("#ph_edit_aramex_countrycode").val();

		let key_data = {

			action 				: 'ph_shipment_tracking_save_carrier',
			selected_carrier 	: selectedCarrier,
			custom_name 	 	: customName,
			custom_url 			: customURL,
			usps_userid 		: uspsUserId,
			ups_userid 			: upsUserId,
			ups_password 		: upsPassword,
			ups_access_key 		: upsAccessKey,
			canadapost_userid 	: cpUserId,
			canadapost_password : cpPassword,
			fedex_account 		: fedexAccount,
			fedex_meter_num 	: fedexMeterNumber,
			fedex_service_key 	: fedexServiceKey,
			fedex_service_pass 	: fedexServicePassword,
			bluedart_userid 	: bluedartUserId,
			bluedart_api_key 	: bluedartApiKey,
			delhivery_userid 	: delhiveryUserId,
			delhivery_api_key 	: delhiveryApiKey,
			dhl_siteid 			: dhlSiteId,
			dhl_api_key 		: dhlApiKey,
			au_account_num 		: auAccountNum,
			au_api_key 			: auAPIKey,
			au_password 		: auPassword,
			aramex_user_name	: aramexUserName,
			aramex_password 	: aramexPassword,
			aramex_account_num 	: aramexAccountNum,
			aramex_account_pin  : aramexAccountPin,
			aramex_entity 		: aramexEntity,
			aramex_country_code : aramexCountryCode,
			
		}

		ph_save_carrier_details( key_data );

		return false;
	});

	jQuery(".remove_modal").click( function() {

		self.parent.tb_remove();

		return false;
	});

	jQuery(".delete_carrier").click( function() {

		jQuery( ".ph_selected_tracking_carriers").block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.5
			}
		});

		var carrierId = jQuery(this).data("id");

		let key_data = {

			action 				: 'ph_shipment_tracking_delete_carrier',
			selected_carrier 	: carrierId,
		}

		jQuery(this).closest('tr').remove();

		jQuery.post( ajaxurl, key_data, function( result, status ) {

			jQuery( ".ph_selected_tracking_carriers").unblock({
				message: null,
				overlayCSS: {}
			});
		});

		return false;
	});

	function ph_save_carrier_details( key_data ) {

		jQuery( ".toplevel_page_shipment_tracking_pro").block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.5
			}
		});

		jQuery.post( ajaxurl, key_data, function( result, status ) {

			setTimeout(location.reload.bind(location), 0);

			jQuery( ".toplevel_page_shipment_tracking_pro").unblock({
				message: null,
				overlayCSS: {}
			});
			
		});

		self.parent.tb_remove();
	}
	
});