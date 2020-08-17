<?php

if (! defined('ABSPATH')) {
	exit; //exit if directly accessed
}

/**
 * Seller assets visibility configuration
 */

?>

<p><?php echo __('Configure sellers asset visibility on profile page.', 'marketplace'); ?></p>
<?php settings_errors(); ?>
<form method="post" action="options.php">
	<?php settings_fields('marketplace-assets-settings-group'); ?>
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="seller_email_visible"><?php echo __('Email', 'marketplace'); ?></label>
				</th>

				<td class="forminp">
					<select name="wkmp_show_seller_email" id="seller_email_visible" style="min-width:350px;" class="">
						<option value=""><?php echo __( 'Select', 'marketplace' ); ?></option>
						<option value="yes" <?php if ( get_option('wkmp_show_seller_email') == 'yes' ) echo 'selected'; ?>><?php echo __( 'Yes', 'marketplace' ); ?></option>
						<option value="no" <?php if ( get_option('wkmp_show_seller_email') == 'no' ) echo 'selected'; ?>><?php echo __( 'No', 'marketplace' ); ?></option>
					</select>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="seller_phone_visible"><?php echo __('Phone Number', 'marketplace'); ?></label>
				</th>

				<td class="forminp">
					<select name="wkmp_show_seller_contact" id="seller_phone_visible" style="min-width:350px;" class="">
						<option value=""><?php echo __( 'Select', 'marketplace' ); ?></option>
						<option value="yes" <?php if ( get_option('wkmp_show_seller_contact') == 'yes' ) echo 'selected'; ?>><?php echo __( 'Yes', 'marketplace' ); ?></option>
						<option value="no" <?php if ( get_option('wkmp_show_seller_contact') == 'no' ) echo 'selected'; ?>><?php echo __( 'No', 'marketplace' ); ?></option>
					</select>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="seller_address_visible"><?php echo __('Address', 'marketplace'); ?></label>
				</th>

				<td class="forminp">
					<select name="wkmp_show_seller_address" id="seller_address_visible" style="min-width:350px;" class="">
						<option value=""><?php echo __( 'Select', 'marketplace' ); ?></option>
						<option value="yes" <?php if ( get_option('wkmp_show_seller_address') == 'yes' ) echo 'selected'; ?>><?php echo __( 'Yes', 'marketplace' ); ?></option>
						<option value="no" <?php if ( get_option('wkmp_show_seller_address') == 'no' ) echo 'selected'; ?>><?php echo __( 'No', 'marketplace' ); ?></option>
					</select>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="seller_social_visible"><?php echo __('Social Links', 'marketplace'); ?></label>
				</th>

				<td class="forminp">
					<select name="wkmp_show_seller_social_links" id="seller_social_visible" style="min-width:350px;" class="">
						<option value=""><?php echo __( 'Select', 'marketplace' ); ?></option>
						<option value="yes" <?php if ( get_option('wkmp_show_seller_social_links') == 'yes' ) echo 'selected'; ?>><?php echo __( 'Yes', 'marketplace' ); ?></option>
						<option value="no" <?php if ( get_option('wkmp_show_seller_social_links') == 'no' ) echo 'selected'; ?>><?php echo __( 'No', 'marketplace' ); ?></option>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
	<?php submit_button(); ?>
</form>
