<?php
/**
 * Add an extra fields in edit user page
 *
 * @package WooCommerce - PDF Vouchers
 * @since 1.5
 */

	// Exit if accessed directly
	if ( !defined( 'ABSPATH' ) ) exit;
	
	global $woo_vou_voucher;
	$prefix 		= WOO_VOU_META_PREFIX;
	
	$voucher_data	= woo_vou_get_vouchers();
	$pdf_template	= get_user_meta( $user->ID, $prefix.'pdf_template', true );
	$using_type		= get_user_meta( $user->ID, $prefix.'using_type', true );
	$address_phone	= get_user_meta( $user->ID, $prefix.'address_phone', true );
	$vendor_logo	= get_user_meta( $user->ID, $prefix.'logo', true );
	$siteurl_text	= get_user_meta( $user->ID, $prefix.'website', true );
	$how_to_use		= get_user_meta( $user->ID, $prefix.'how_to_use', true );
	$avail_locations= get_user_meta( $user->ID, $prefix.'avail_locations', true );	

	//Get vendor sale enabled option
	$vendor_sale_settings	= get_option('woocommerce_woo_vou_vendor_sale_notification_settings');
	$vendor_sale_enabled	= isset( $vendor_sale_settings['enabled'] ) ? $vendor_sale_settings['enabled'] : '';
	
	// get email notification settings from user meta
	$vendor_sale_email_notification = get_user_meta( $user->ID, $prefix.'enable_vendor_sale_email_notification', true );
		
	?>
	<table class="form-table">		
		<h3><?php echo esc_html__( 'Voucher Options', 'woovoucher' );?></h3>		
		<tbody>
			<!-- PDF Template -->
			<tr>
				<th><label for="<?php echo $prefix . 'pdf_template'; ?>"><?php esc_html_e( 'PDF Template', 'woovoucher' ); ?></label></th>
				<td>
					<select data-width="180px" class="chosen_select" name="<?php echo $prefix.'pdf_template'; ?>" id="<?php echo $prefix.'pdf_template'; ?>">
						<option value=""><?php esc_html_e('Please Select', 'woovoucher'); ?></option>
							<?php foreach ( $voucher_data as $voucher ) { ?>
								<option value="<?php echo $voucher['ID']; ?>" <?php if( $pdf_template == $voucher['ID'] ) echo "selected=selected"; ?>><?php echo $voucher['post_title']; ?></option>
							<?php } ?>
					</select><br />
					<span class="description woo-vou-description"><?php esc_html_e( 'Select a PDF template. Leave it empty to use the template from the settings page.', 'woovoucher' ); ?></span>
				</td>
			</tr>
			
			<?php do_action( 'woo_vou_vendor_setting_after_pdf_template', $user->ID );?>
			
			<!-- Usability -->
			<tr>
				<th><label for="<?php echo $prefix . 'using_type'; ?>"><?php esc_html_e( 'Usability', 'woovoucher' ); ?></label></th>
				<td>
					<select data-width="180px" class="chosen_select" name="<?php echo $prefix.'using_type'; ?>" id="<?php echo $prefix.'using_type'; ?>">
						<option value=""><?php esc_html_e('Default', 'woovoucher'); ?></option>
						<option value="0" <?php if( $using_type == '0' ) echo "selected=selected"; ?>><?php esc_html_e('One time only', 'woovoucher'); ?></option>
						<option value="1" <?php if( $using_type == '1' ) echo "selected=selected"; ?>><?php esc_html_e('Unlimited', 'woovoucher'); ?></option>						
					</select><br />
					<span class="description woo-vou-description"><?php esc_html_e( 'Choose how many times the same Voucher Code can be used by the users. Leave it empty to use the Usability from the settings page.', 'woovoucher' ); ?></span>
				</td>
			</tr>
			
			<?php do_action( 'woo_vou_vendor_setting_after_using_type', $user->ID );?>
			
		<!-- Vendor sale enable/disable email notification -->
		<?php
		
			
			// show enable/disable vendor sale email notification option only if global settings is disabled
			if( $vendor_sale_enabled != "yes" ) { ?>								
				<tr>
					<th><label for="<?php echo $prefix . 'enable_vendor_sale_email_notification'; ?>"><?php esc_html_e( 'Enable/Disable email notification', 'woovoucher' ); ?></label></th>
					<td>
						<input type="checkbox" id="<?php echo $prefix . 'enable_vendor_sale_email_notification'; ?>" name="<?php echo $prefix . 'enable_vendor_sale_email_notification'; ?>" value="1" <?php checked( "1", $vendor_sale_email_notification ); ?>/><?php esc_html_e('Enable vendor sale email notification', 'woovoucher'); ?>						
					</td>
				</tr><?php 
			}
			
			if( !empty( $vendor_logo ) ) { //check connect button image
				$show_img_connect = ' <img src="'.esc_url($vendor_logo).'" alt="'.esc_html__('Image','woovoucher').'" />';
			} else {
				$show_img_connect = '';
			}
			?>
			<!-- Vendor Logo -->
			<tr>
				<th>
					<label for="<?php echo $prefix.'logo'; ?>"><?php esc_html_e( 'Vendor Logo', 'woovoucher' ); ?></label>
				</th>
				<td><input class="regular-text" type="text" id="<?php echo $prefix.'logo'; ?>" name="<?php echo $prefix.'logo'; ?>" value="<?php echo $vendor_logo; ?>" />
					<input type="button" class="button-secondary woo-vou-upload-button" id="woo-vou-img-uploader-btn" value="<?php esc_html_e( 'Choose image.', 'woovoucher' ); ?>"><br />
					<span class="description woo-vou-description"><?php esc_html_e( 'Allows you to upload a logo of the vendor for which this Voucher is valid. The logo will also be displayed on the PDF document.', 'woovoucher' ); ?></span><br /><br />
					<div id="woo-vou-image-view" class="woo-vou-img-view"><?php echo $show_img_connect; ?></div>								
				</td>
			 </tr>
			 
			 <?php do_action( 'woo_vou_vendor_setting_after_logo', $user->ID );?>
			 
			<!-- Vendor Address -->
			<tr>
				<th>
					<label for="<?php echo $prefix . 'address_phone';?>"><?php esc_html_e( 'Vendor Address', 'woovoucher' ); ?></label>
				</th>
				<td>
					<textarea cols="50" rows="5" placeholder="" id="<?php echo $prefix . 'address_phone'; ?>" name="<?php echo $prefix . 'address_phone'; ?>"><?php echo $address_phone; ?></textarea><br />
					<span class="description"><?php esc_html_e( 'Here you can enter the complete Vendor\'s address. This will be displayed on the PDF voucher sent to the customers so that they know where to redeem this Voucher. Limited HTML is allowed.', 'woovoucher' ); ?></span>
				</td>
			</tr>
			
			<?php do_action( 'woo_vou_vendor_setting_after_address_phone', $user->ID );?>
			
			<!-- Website URL -->
			<tr>
				<th>
					<label for="<?php echo $prefix . 'siteurl_text'; ?>"><?php esc_html_e( 'Website URL', 'woovoucher' ); ?></label>
				</th>
				<td>
					<input class="regular-text" type="text" placeholder="" id="<?php echo $prefix . 'siteurl_text'; ?>" name="<?php echo $prefix . 'siteurl_text'; ?>" value="<?php echo $siteurl_text; ?>"><br />
					<span class="description woo-vou-description"><?php esc_html_e( 'Enter the Vendor\'s website URL here. This will be display on the PDF voucher.', 'woovoucher' ); ?></span>
				</td>
			</tr>
			
			<?php do_action( 'woo_vou_vendor_setting_after_siteurl_text', $user->ID );?>
			
			<!-- Redeem Instructions -->
			<tr>
				<th>
					<label for="<?php echo $prefix . 'how_to_use';?>"><?php esc_html_e( 'Redeem Instructions', 'woovoucher' ); ?></label>
				</th>
				<td>
					<textarea cols="50" rows="5" placeholder="" id="<?php echo $prefix . 'how_to_use'; ?>" name="<?php echo $prefix . 'how_to_use'; ?>"><?php echo $how_to_use; ?></textarea><br />
					<span class="description"><?php esc_html_e( 'Within this option, you can enter instructions on how this Voucher can be redeemed. This instruction will be displayed on the PDF voucher sent to the customer after successful purchase. Limited HTML is allowed.', 'woovoucher' ); ?></span>
				</td>
			</tr>
			
			<?php do_action( 'woo_vou_vendor_setting_after_how_to_use', $user->ID );?>
			
			<!-- Locations -->
			<tr>
				<th>
					<?php esc_html_e( 'Locations', 'woovoucher' ); ?>
				</th>
				<td>
					<div class='woo-vou-meta-repeat' id='locations'>						
						<?php if( !empty( $avail_locations ) ) { 
							
							foreach ($avail_locations as $key => $avail_location ) { 
								
								if( $key > 0 ) {
									$showremove = "woo-vou-block-section";
								} else {
									$showremove = "woo-vou-hide-section";
								}
								?>
							
							<div class='woo-vou-meta-repater-block'>
								<p>
									<label class='woo-vou-location-label'><?php esc_html_e('Location :', 'woovoucher'); ?></label>
									<input type='text' name=<?php echo $prefix . 'locations[]'; ?> class='woo-vou-meta-text regular-text woo-vou-repeater-text' value="<?php echo $avail_location[$prefix.'locations']; ?>"/>
									<span class="description woo-vou-cust-description"><?php esc_html_e( 'Enter the address of the location where the voucher code can be redeemed. This will be displayed on the PDF document sent to the customer. Limited HTML is allowed.', 'woovoucher' ) ?></span>
								</p>
								<p>
									<label class='woo-vou-locationlink-label'><?php esc_html_e('Location Map Link:', 'woovoucher'); ?></label>
									<input type='text' name=<?php echo $prefix . 'map_link[]'; ?> class='woo-vou-meta-text regular-text woo-vou-repeater-text' value="<?php echo $avail_location[$prefix.'map_link']; ?>"/>
									<span class="description woo-vou-cust-description"><?php esc_html_e( 'Enter a link to a Google Map for the location here. This will be displayed on the PDF document sent to the customer.', 'woovoucher' ) ?></span>
								</p>
								<?php do_action( 'woo_vou_vendor_setting_add_location_fields', $user->ID, $key );?>
								<img id='remove-locations' class='woo-vou-repeater-remove <?php echo $showremove; ?>' title="<?php esc_html_e('Remove', 'woovoucher'); ?>" alt="<?php esc_html_e('Remove', 'woovoucher'); ?>" src="<?php echo esc_url(WOO_VOU_META_URL).'/images/remove.png'; ?>">
							</div><!--.woo-vou-meta-repater-block-->
							
							<?php }
						} else { ?>
						
							<div class='woo-vou-meta-repater-block'>
								<p>
									<label class='woo-vou-location-label'><?php esc_html_e('Location :', 'woovoucher'); ?></label>
									<input type='text' name='<?php echo $prefix . 'locations[]'; ?>' class='woo-vou-meta-text regular-text woo-vou-repeater-text'/> <br />
									<span class="description woo-vou-cust-description"><?php esc_html_e( 'Enter the address of the location where the voucher code can be redeemed. This will be displayed on the PDF document sent to the customer. Limited HTML is allowed.', 'woovoucher' ) ?></span>
								</p>
								<p>
									<label class='woo-vou-locationlink-label'><?php esc_html_e('Location Map Link:', 'woovoucher'); ?></label>
									<input type='text' name='<?php echo $prefix . 'map_link[]'; ?>' class='woo-vou-meta-text regular-text woo-vou-repeater-text'/>
									<span class="description woo-vou-cust-description"><?php esc_html_e( 'Enter a link to a Google Map for the location here. This will be displayed on the PDF document sent to the customer.', 'woovoucher' ) ?></span>
								</p>
								<?php do_action( 'woo_vou_vendor_setting_add_location_fields', $user->ID, 0 );?>
								<img id='remove-locations' class='woo-vou-repeater-remove woo-vou-meta-display-none' title="<?php esc_html_e('Remove', 'woovoucher'); ?>" alt="<?php esc_html_e('Remove', 'woovoucher'); ?>" src="<?php echo esc_url(WOO_VOU_META_URL).'/images/remove.png'; ?>">
							</div><!--.woo-vou-meta-repater-block--><?php 
						}?>
						<img id='add-locations' class='woo-vou-repeater-add' title="<?php esc_html_e( 'Add','woovoucher' ); ?>" alt="<?php esc_html_e('Add', 'woovoucher'); ?>" src="<?php echo esc_url(WOO_VOU_META_URL).'/images/add.png'; ?>" >
					</div><!--.woo-vou-meta-repeat-->
					<span class="description woo-vou-description"><?php esc_html_e( 'If the Vendor of the Voucher has more than one location where the Voucher can be redeemed, then you can add all the locations within this option.', 'woovoucher' ); ?></span>
				</td>
				<?php do_action( 'woo_vou_vendor_setting_after_locations', $user->ID );?>
			</tr>
		</tbody>
	</table>
	<?php do_action( 'woo_vou_after_user_edit_profile', $user->ID ); ?>