<?php
$settings_auto_import   = get_option('woocommerce_' . WF_SHIPMENT_TRACKING_IMP_EXP_ID . '_settings', null);
$settings               = get_option('wf_shipment_tracking_importer_ftp', null);


$enable_ftp_ie      = isset($settings['enable_ftp_ie']) ? $settings['enable_ftp_ie'] : '';
$ftp_or_sftp        = isset($settings['ftp_or_sftp']) ? $settings['ftp_or_sftp'] : '';
$ftp_server         = isset($settings['ftp_server']) ? $settings['ftp_server'] : '';
$ftp_user           = isset($settings['ftp_user']) ? $settings['ftp_user'] : '';
$ftp_password       = isset($settings['ftp_password']) ? $settings['ftp_password'] : '';
$use_ftps           = isset($settings['use_ftps']) ? $settings['use_ftps'] : '';
$use_passive_mode   = isset($settings['use_passive_mode']) ? $settings['use_passive_mode'] : true;
$ftp_port           = !empty($settings['ftp_port']) ? $settings['ftp_port'] : '';
$ftp_timeout        = !empty($settings['ftp_timeout']) ? $settings['ftp_timeout'] : '90';
$ftp_server_path    = !empty($settings['ftp_server_path']) ? $settings['ftp_server_path'] : '';

$shipment_tracking_auto_import 				= isset($settings_auto_import['shipment_tracking_auto_import']) ? $settings_auto_import['shipment_tracking_auto_import'] : 'Disabled';
$shipment_tracking_auto_import_start_time 	= isset($settings_auto_import['shipment_tracking_auto_import_start_time']) ? $settings_auto_import['shipment_tracking_auto_import_start_time'] : '';
$shipment_tracking_auto_import_interval 	= isset($settings_auto_import['shipment_tracking_auto_import_interval']) ? $settings_auto_import['shipment_tracking_auto_import_interval'] : '';
$shipment_tracking_auto_import_merge 		= isset($settings_auto_import['shipment_tracking_auto_import_merge']) ? $settings_auto_import['shipment_tracking_auto_import_merge'] : 1;
$shipment_tracking_auto_import_delimiter 	= isset($settings_auto_import['shipment_tracking_auto_import_delimiter']) ? $settings_auto_import['shipment_tracking_auto_import_delimiter'] : ',';

wp_localize_script('woocommerce-shipment-tracking-csv-importer', 'woocommerce_shipment_tracking_csv_cron_params', array('enable_ftp_ie' => $enable_ftp_ie, 'shipment_tracking_auto_import' => $shipment_tracking_auto_import));
if ($scheduled_timestamp = wp_next_scheduled('wf_shipment_tracking_csv_im_ex_auto_import')) {
	$scheduled_desc = sprintf(__('The next export is scheduled on <code>%s</code>', 'woocommerce-shipment-tracking'), get_date_from_gmt(date('Y-m-d H:i:s', $scheduled_timestamp), wc_date_format() . ' ' . wc_time_format()));
} else {
	$scheduled_desc = __('There is no export scheduled.', 'woocommerce-shipment-tracking');
}
?>

<div class="tool-box">

	<h2><?php _e('Automatically Import Shipment Tracking Details to WooCommerce Orders via FTP or SFTP', 'woocommerce-shipment-tracking'); ?></h2>

	<form action="<?php echo admin_url('admin.php?page=import_shipment_tracking_csv&action=settings'); ?>" method="post">
		
		<table class="form-table">
			<tr>
				<th>
					<label for="enable_ftp_ie"><?php _e('Enable FTP/SFTP', 'woocommerce-shipment-tracking'); ?></label>
				</th>
				<td>
					<input type="checkbox" name="enable_ftp_ie" id="enable_ftp_ie" class="checkbox" <?php checked($enable_ftp_ie, 1); ?> />
				</td>
			</tr>
		</table>

		<table class="form-table" id="shipment_tracking_import_section_all">
			<tr>
				<th>
					<label for="ftp_or_sftp"><?php _e( 'Select FTP or SFTP', 'woocommerce-shipment-tracking' ); ?></label>
				</th>
				<td>
					<select name="ftp_or_sftp" id="ftp_or_sftp" style="width: 16.5%;">
						<option <?php if ($ftp_or_sftp === 'ftp') echo 'selected'; ?> value="ftp"><?php _e( 'FTP', 'woocommerce-shipment-tracking' ); ?></option>
						<option <?php if ($ftp_or_sftp === 'sftp') echo 'selected'; ?> value="sftp"><?php _e( 'SFTP', 'woocommerce-shipment-tracking' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<label for="ftp_server"><?php _e('FTP/SFTP Server Host/IP', 'woocommerce-shipment-tracking'); ?></label>
				</th>
				<td>
					<input type="text" name="ftp_server" id="ftp_server" placeholder="<?php _e('XXX.XXX.XXX.XXX', 'woocommerce-shipment-tracking'); ?>" value="<?php echo $ftp_server; ?>" class="input-text" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="ftp_user"><?php _e('FTP/SFTP User Name', 'woocommerce-shipment-tracking'); ?></label>
				</th>
				<td>
					<input type="text" name="ftp_user" id="ftp_user" placeholder="<?php _e('', 'woocommerce-shipment-tracking'); ?>" value="<?php echo $ftp_user; ?>" class="input-text" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="ftp_password"><?php _e('FTP/SFTP Password', 'woocommerce-shipment-tracking'); ?></label>
				</th>
				<td>
					<input type="password" name="ftp_password" id="ftp_password" placeholder="<?php _e('', 'woocommerce-shipment-tracking'); ?>" value="<?php echo $ftp_password; ?>" class="input-text" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="ftp_port"><?php _e('FTP/SFTP Port', 'woocommerce-shipment-tracking'); ?></label>
					<img class="help_tip" style="float:none;" data-tip="<?php _e('Default port will be used if left empty.', 'woocommerce-shipment-tracking'); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
				</th>
				<td>
					<input type="text" name="ftp_port" id="ftp_port" placeholder="<?php _e('21', 'woocommerce-shipment-tracking'); ?>" value="<?php if( ! empty($ftp_port) )  echo $ftp_port; ?>" class="input-text" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="ftp_timeout"><?php _e('FTP/SFTP Timeout', 'woocommerce-shipment-tracking'); ?></label>
					<img class="help_tip" style="float:none;" data-tip="<?php _e('Default timeout default value will be used if left empty.', 'woocommerce-shipment-tracking'); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
				</th>
				<td>
					<input type="text" name="ftp_timeout" id="ftp_timeout" placeholder="<?php _e('90', 'woocommerce-shipment-tracking'); ?>" value="<?php if( ! empty($ftp_timeout)) echo $ftp_timeout; ?>" class="input-text" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="ftp_server_path"><?php _e('Path/CSV File Name', 'woocommerce-shipment-tracking'); ?></label>
					<img class="help_tip" style="float:none;" data-tip="<?php _e('Remote CSV File Path starting from FTP/SFTP home directory excluding leading slash and File Name in the end.', 'woocommerce-shipment-tracking'); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
				</th>
				<td>
					<input type="text" name="ftp_server_path" id="ftp_server_path" placeholder="<?php _e('Path/StartingFrom/FTP-Directory/FileName.csv', 'woocommerce-shipment-tracking'); ?>" value="<?php echo $ftp_server_path; ?>" class="input-text" />
					<br/>
					<small>Note: <i>For SFTP Connections the path should always end with file name (Folder name is not supported).</i></small>
				</td>
			</tr>

			<tr>
				<th>
					<label for="use_ftps"><?php _e('Use FTPS', 'woocommerce-shipment-tracking'); ?></label>
				</th>
				<td>
					<input type="checkbox" name="use_ftps" id="use_ftps" class="checkbox" <?php checked($use_ftps, 1); ?> />
				</td>
			</tr>

			<tr>
				<th>
					<label for="use_passive_mode"><?php _e('Use Passive Mode', 'woocommerce-shipment-tracking'); ?></label>
				</th>
				<td>
					<input type="checkbox" name="use_passive_mode" id="use_passive_mode" class="checkbox" <?php checked($use_passive_mode, 1); ?> />
				</td>
			</tr>

			<tr>
				<th>
					<label for="shipment_tracking_auto_import"><?php _e('Automatically Import CSV', 'woocommerce-shipment-tracking'); ?></label>
				</th>
				<td>
					<select class="" style="width: 16.5%;" id="shipment_tracking_auto_import" name="shipment_tracking_auto_import">
						<option <?php if ($shipment_tracking_auto_import === 'Disabled') echo 'selected'; ?> value="Disabled"><?php _e('Disabled', 'shipment_tracking_auto_import'); ?></option>
						<option <?php if ($shipment_tracking_auto_import === 'Enabled') echo 'selected'; ?> value="Enabled"><?php _e('Enabled', 'shipment_tracking_auto_import'); ?></option>
					</select>
				</td>
			</tr>
			<tbody class="shipment_tracking_import_section">
				<tr>
					<th>
						<label for="shipment_tracking_auto_import_start_time"><?php _e('Import Start Time', 'woocommerce-shipment-tracking'); ?></label>
					</th>
					<td>
						<input type="text" name="shipment_tracking_auto_import_start_time" id="shipment_tracking_auto_import_start_time"  value="<?php echo $shipment_tracking_auto_import_start_time; ?>"/>
						<span class="description"><?php echo sprintf(__('Local time is <code>%s</code>.', 'woocommerce-shipment-tracking'), date_i18n(wc_time_format())) . ' ' . $scheduled_desc; ?></span>
						<br/>
						<span class="description"><?php _e('<code>Enter like 6:18pm or 12:27am</code>', 'woocommerce-shipment-tracking'); ?></span>
					</td>
				</tr>
				<tr>
					<th>
						<label for="shipment_tracking_auto_import_interval"><?php _e('Import Interval [ Minutes ]', 'woocommerce-shipment-tracking'); ?></label>
					</th>
					<td>
						<input type="text" name="shipment_tracking_auto_import_interval" id="shipment_tracking_auto_import_interval"  value="<?php echo $shipment_tracking_auto_import_interval; ?>"  />
					</td>
				</tr>
				<tr>
					<th>
						<label for="shipment_tracking_auto_import_delimiter"><?php _e('Delimiter', 'woocommerce-shipment-tracking'); ?></label>
					</th>
					<td>
						<input type="text" name="shipment_tracking_auto_import_delimiter" id="shipment_tracking_auto_import_delimiter"  value="<?php echo $shipment_tracking_auto_import_delimiter; ?>"  />
					</td>
				</tr>
				<tr>
					<th>
						<label for="shipment_tracking_auto_import_merge"><?php _e('Skip Already Processed', 'woocommerce-shipment-tracking'); ?></label>
					</th>
					<td>
						<input type="checkbox" name="shipment_tracking_auto_import_merge" id="shipment_tracking_auto_import_merge" class="checkbox" <?php checked($shipment_tracking_auto_import_merge, 1); ?> />
					</td>
				</tr>
			</tbody>
		</table>    
		<p class="submit"><input type="submit" class="button button-primary" value="<?php _e('Save Settings', 'woocommerce-shipment-tracking'); ?>" /></p>
	</form>
</div>