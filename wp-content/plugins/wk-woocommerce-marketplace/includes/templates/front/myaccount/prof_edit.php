<?php

global $current_user, $wpdb;

$wpmp_obj12 = new MP_Form_Handler();

if ( isset( $_POST['update_profile_submit'] ) ) {

	$wpmp_obj12->profile_edit_redirection();

}

$current_user = get_current_user_ID();

$current_user = get_user_by( 'ID', $current_user );

if ( $current_user->ID ) {

	$avatar = $wpmp_obj12->get_user_avatar( $current_user->ID, 'avatar' );

	$shop_banner = $wpmp_obj12->get_user_avatar( $current_user->ID, 'shop_banner' );

	$com_logo = $wpmp_obj12->get_user_avatar( $current_user->ID, 'company_logo' );

	$usermeta_row_data = $wpdb->get_results( "select * from $wpdb->usermeta where user_id=" . $current_user->ID );

	$user_meta_arr = array();

	foreach ( $usermeta_row_data as $key => $value ) {
		$user_meta_arr[ $value->meta_key ] = $value->meta_value;
	}
}

$page_name = $wpdb->get_var( "SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option( 'wkmp_seller_page_title' ) . "'" );

?>
<div class="woocommerce-account">
	<?php

	do_action( 'mp_get_wc_account_menu', 'marketplace' );

?>

<div class="wk_profileupdate woocommerce-MyAccount-content">

	<div class="wkmp_profile_preview_link">
		<a href="<?php echo esc_html( site_url() . '/' . $page_name . '/' . get_option( 'mp_store', 'store' ) . '/' . $user_meta_arr['shop_address'] ); ?>" class="button" target="_blank"><?php echo esc_html__( 'View Profile', 'marketplace' ); ?></a>
	</div>

	<form action="" method="post" enctype="multipart/form-data">

		<div class="wkmp-tab-content">

		<div class="wkmp_profileinfo">

				<div class="wkmp_profile_input">
				<label for="wk_username"><?php esc_html_e( 'Username', 'marketplace' ); ?></label>
				<input type="text" name="wk_username" value="<?php echo esc_html( $current_user->user_login ); ?>" id="wk_username" readonly disabled="disabled" /><br>
				<i><?php esc_html( 'Username cannot be changed.', 'marketplace' ); ?></i>
				<input type="hidden" name="wk_user_nonece" value="<?php echo esc_html( $current_user->user_login ); ?>" id="wk_user_nonece" readonly />
				<div id=""></div>
				</div>
				<div class="wkmp_profile_input">
				<label for="wk_firstname"><?php esc_html_e( 'First Name', 'marketplace' ); ?></label>
				<input type="text" value="<?php echo isset( $user_meta_arr['first_name'] ) ? esc_html( $user_meta_arr['first_name'] ) : ''; ?>" name="wk_firstname" id="wk_firstname" />
				<div id="first_name_error" class="error-class"></div>
				</div>
				<div class="wkmp_profile_input">
				<label for="wk_lastname"><?php esc_html_e( 'Last Name', 'marketplace' ); ?></label>
				<input type="text" value="<?php echo isset( $user_meta_arr['last_name'] ) ? esc_html( $user_meta_arr['last_name'] ) : ''; ?>" name="wk_lastname"  id="wk_lastname" />
				<div id="last_name_error" class="error-class"></div>
				</div>
				<div class="wkmp_profile_input">
				<label for="wk_useremail"><?php esc_html_e( 'E-mail', 'marketplace' ); ?></label>
				<input type="text" value="<?php echo esc_html( $current_user->user_email ); ?>" name="user_email" id="wk_useremail" />
				<div class="error-class" id="email_reg_error"></div>
				</div>

				<div class="wkmp_profile_input">
					<label for="wk_store_name"><?php esc_html_e( 'Shop Name', 'marketplace' ); ?></label>
					<input type="text" placeholder="" value="<?php echo isset( $user_meta_arr['shop_name'] ) ? esc_html( $user_meta_arr['shop_name'] ) : ''; ?>" name="wk_storename" id="wk_storename" class="wk_loginput"/>
					<div class="error-class" id="seller_storename"></div>
				</div>
				<div class="wkmp_profile_input">
					<label for="wk_shop_add"><?php esc_html_e( 'Shop URL', 'marketplace' ); ?></label>
					<input type="text" placeholder="<?php esc_html_e( 'Shop Address', 'marketplace' ); ?>" value="<?php echo isset( $user_meta_arr['shop_address'] ) ? esc_html( $user_meta_arr['shop_address'] ) : ''; ?>" name="wk_storeurl" id="wk_storeurl" class="wk_loginput" disabled="disabled" readonly/>
					<div class="error-class" id="seller_storeaddress"></div>
					<i><?php echo esc_html__( 'Shop URL cannot be changed.', 'marketplace' ); ?></i>
				</div>
				<div class="wkmp_profile_input">
					<label for="wk_shop_add"><?php esc_html_e( 'Phone Number', 'marketplace' ); ?></label>
					<input type="text" placeholder="<?php esc_html_e( 'Shop Phone Number', 'marketplace' ); ?>" value="<?php echo isset( $user_meta_arr['billing_phone'] ) ? esc_html( $user_meta_arr['billing_phone'] ) : ''; ?>" name="wk_storephone" id="wk_storephone" class="wk_loginput"/>
					<div class="error-class" id="seller_storephone"></div>
				</div>

				<div class="wkmp_profile_input">
					<label for="wk_store_add1"><?php esc_html_e( 'Address Line 1', 'marketplace' ); ?></label>
					<input type="text" placeholder="<?php esc_html_e( 'Shop Address 1', 'marketplace' ); ?>" name="wk_store_add1" id="wk-store-add1" value="<?php echo isset( $user_meta_arr['billing_address_1'] ) ? esc_html( $user_meta_arr['billing_address_1'] ) : ''; ?>" class="wk_loginput"/>
					<div class="error-class" id="seller_store_add1"></div>
				</div>

				<div class="wkmp_profile_input">
					<label for="wk_store_add2"><?php esc_html_e( 'Address Line 2', 'marketplace' ); ?></label>
					<input type="text" placeholder="<?php esc_html_e( 'Shop Address 2', 'marketplace' ); ?>" value="<?php echo isset( $user_meta_arr['billing_address_2'] ) ? esc_html( $user_meta_arr['billing_address_2'] ) : ''; ?>" name="wk_store_add2" id="wk-store-add2" class="wk_loginput"/>
					<div class="error-class" id="seller_store_add2"></div>
				</div>

				<div class="wkmp_profile_input">
					<label for="wk_store_city"><?php esc_html_e( 'City', 'marketplace' ); ?></label>
					<input type="text" placeholder="<?php esc_html_e( 'City', 'marketplace' ); ?>" value="<?php echo isset( $user_meta_arr['billing_city'] ) ? esc_html( $user_meta_arr['billing_city'] ) : ''; ?>" name="wk_store_city" id="wk-store-city" class="wk_loginput"/>
					<div class="error-class" id="seller_store_city"></div>
				</div>

				<div class="wkmp_profile_input">
					<label for="wk_store_postcode"><?php esc_html_e( 'Postal Code', 'marketplace' ); ?></label>
					<input type="text" placeholder="<?php esc_html_e( 'Postcode', 'marketplace' ); ?>" value="<?php echo isset( $user_meta_arr['billing_postcode'] ) ? esc_html( $user_meta_arr['billing_postcode'] ) : ''; ?>" name="wk_store_postcode" id="wk-store-postcode" class="wk_loginput"/>
					<div class="error-class" id="seller_store_postcode"></div>
				</div>

				<div class="wkmp_profile_input">
					<label for="wk_store_country"><?php esc_html_e( 'Country', 'marketplace' ); ?></label>
						<?php
						global $woocommerce;

						$countries_obj = new WC_Countries();
						$countries     = $countries_obj->__get( 'countries' );
						$country_code  = isset( $user_meta_arr['billing_country'] ) ? $user_meta_arr['billing_country'] : '';

						echo '<div id="seller_countries_field">';
						woocommerce_form_field('wk_store_country', array(
							'type'        => 'select',
							'default'     => $country_code,
							'class'       => array( 'chzn-drop' ),
							'options'     => $countries,
							'placeholder' => __( 'Select a country', 'marketplace' ),
						) );
						echo '</div></div>';
						$state_code = isset( $user_meta_arr['billing_state'] ) ? $user_meta_arr['billing_state'] : '';
						if ( WC()->countries->get_states( $country_code ) ) {

							$states = WC()->countries->get_states( $country_code );
							?>
							<div class="wkmp_profile_input" >
								<label for="wk_store_state"><?php esc_html_e( 'State / County', 'marketplace' ); ?></label>
								<?php
								woocommerce_form_field('wk_store_state', array(
									'id'          => 'wk_store_state',
									'type'        => 'select',
									'default'     => $state_code,
									'class'       => array( 'chzn-drop' ),
									'options'     => $states,
									'placeholder' => __( 'Select a country', 'marketplace' ),
								) );
								?>
							</div>
							<?php

						} else {
						?>
						<div class="wkmp_profile_input" >
							<label for="wk_store_state"><?php esc_html_e( 'State / County', 'marketplace' ); ?></label>
							<input id="wk_store_state" type="text" placeholder="<?php esc_html_e( 'State', 'marketplace' ); ?>" name="wk_store_state" class="wk_loginput" value="<?php echo esc_html( $state_code ); ?>" />
						</div>
						<?php
						}
						?>

		<div class="wkmp_avatar_logo_section">

			<div class="wkmp_profileimg">
				<?php
				if ( isset( $avatar[0]->meta_value ) ) {
					$avatar_thumb_id = ( $user_meta_arr['_thumbnail_id_avatar'] ) ? $user_meta_arr['_thumbnail_id_avatar'] : '';
					echo '<div class="wkmp_editmp_img" id="mp_seller_image"><img src="' . content_url() . '/uploads/' . $avatar[0]->meta_value . '" /><span data-id="' . $avatar_thumb_id . '" data-default="' . WK_MARKETPLACE . 'assets/images/genric-male.png" class="mp-image-remove-icon">x</span><input type="hidden" class="mp-remove-avatar" name="mp-remove-avatar" value="" /></div>';
				} else {
					echo '<div class="wkmp_editmp_img" id="mp_seller_image"><img src="' . WK_MARKETPLACE . 'assets/images/genric-male.png" /></div>';
				}
				?>
				<div class="wkmp-fileUpload wkmp_profile_input">
					<label><?php echo esc_html__( 'User Image', 'marketplace' ); ?></label>
					<i><?php echo esc_html__( 'Upload image jpeg or png', 'marketplace' ); ?></i>
					<span class="button"><?php esc_html_e( 'Upload', 'marketplace' ); ?><input type="file" class="upload mp_seller_profile_img" name="mp_useravatar" id="mp_useravatar" /></span>
				</div>
			</div>

			<div class="wkmp_profile_logo">
				<?php
				if ( $com_logo ) {
					$logo_thumb_id = ( $user_meta_arr['_thumbnail_id_company_logo'] ) ? $user_meta_arr['_thumbnail_id_company_logo'] : '';
					echo '<div class="seller_logo_img" id="seller_com_logo_img"><img src="' . content_url() . '/uploads/' . $com_logo[0]->meta_value . '" /><span data-id="' . $logo_thumb_id . '" data-default="' . WK_MARKETPLACE . 'assets/images/shop-logo.png" class="mp-image-remove-icon">x</span><input type="hidden" class="mp-remove-company-logo" name="mp-remove-company-logo" value="" /></div>';
				} else {
					echo '<div class="seller_logo_img" id="seller_com_logo_img"><img src="' . WK_MARKETPLACE . 'assets/images/shop-logo.png" /></div>';
				}
				?>
				<div class="wkmp-fileUpload wkmp_profile_input">
					<label><?php echo esc_html__( 'Shop Logo', 'marketplace' ); ?></label>
					<i><?php echo esc_html__( 'Upload image jpeg or png', 'marketplace' ); ?></i>
					<span class="button"><?php esc_html_e( 'Upload', 'marketplace' ); ?><input type="file" class="upload Company_Logo" name="mp_company_logo" id="mp_company_logo" /></span>
				</div>
			</div>

		</div>


		<!-- shop banner -->
		<div class=" wkmp_profile_input">
			<label><?php echo esc_html__( 'Banner Image', 'marketplace' ); ?></label>
			<div class="banner-checkbox"><input type="checkbox" name="mp_display_banner" id="banner_visibility" value="yes" <?php if ( isset( $user_meta_arr['shop_banner_visibility'] ) && $user_meta_arr['shop_banner_visibility'] == 'yes' ) echo 'checked'; ?> /><label for="banner_visibility"><?php echo esc_html__( 'Show banner on seller page', 'marketplace' ); ?></label></div>
			<div class="wkmp_shop_banner">
				<div class="wkmp-fade-banner" id="wkmp_seller_banner">
						<p><?php echo esc_html__( 'Upload', 'marketplace' ); ?></p>
				</div>
				<div class="wkmp-fileUpload wkmp_up_shop_banner">
					<span><?php esc_html_e( 'Upload', 'marketplace' ); ?></span>
					<input type="file" class="upload" name="wk_mp_shop_banner" id="wk_mp_shop_banner"/>
				</div>
				<?php
				if ( $shop_banner ) {
					$banner_thumb_id = ( $user_meta_arr['_thumbnail_id_shop_banner'] ) ? $user_meta_arr['_thumbnail_id_shop_banner'] : '';
					echo '<div class="wk_banner_img" id="wk_seller_banner"><img src="' . content_url() . '/uploads/' . $shop_banner[0]->meta_value . '" /><span title="' . esc_html__( 'Remove', 'marketplace' ) . '" data-id="' . $banner_thumb_id . '" data-default="' . WK_MARKETPLACE . 'assets/images/woocommerce-marketplace-banner.png" class="mp-image-remove-icon">x</span><input type="hidden" class="mp-remove-shop-banner" name="mp-remove-shop-banner" value="" /></div>';
				} else {
					echo '<div class="wk_banner_img" id="wk_seller_banner"><img src= "' . WK_MARKETPLACE . 'assets/images/woocommerce-marketplace-banner.png" /></div>';
				}
				?>
			</div>
		</div>
		<!-- shop banner end-->

		<div class="wkmp_profile_input">
			<label for="wk_marketplace_about_shop"><?php esc_html_e( 'About Shop', 'marketplace' ); ?></label>
			<textarea name="wk_marketplace_about_shop" rows="4" id="wk_marketplace_about_shop" class="wk_loginput"><?php echo isset( $user_meta_arr['about_shop'] ) ? $user_meta_arr['about_shop'] : ''; ?></textarea>
		</div>

		<h3><b><?php esc_html_e( 'Social Profile', 'marketplace' ); ?></b></h3>

		<div class="wkmp_profile_input">

			<label for="settings[social][fb]"><?php echo esc_html__( 'Facebook Profile ID', 'marketplace' ); ?></label><i> <?php echo '(' . esc_html__( 'optional', 'marketplace' ) . ')'; ?></i>
			<div class="social-seller-input">
				<input id="settings[social][fb]"  type="text" placeholder="http://" name="settings[social][fb]" value="<?php echo isset( $user_meta_arr['social_facebook'] ) ? $user_meta_arr['social_facebook'] : ''; ?>">
			</div>
			<div class="error-class" id="seller_user_address"></div>

		</div>
		<div class="wkmp_profile_input">
			<label for="settings[social][twitter]"><?php echo esc_html__( 'Twitter Profile ID', 'marketplace' ); ?></label><i> <?php echo '(' . esc_html__( 'optional', 'marketplace' ) . ')'; ?></i>
			<div class="social-seller-input">
				<input id="settings[social][twitter]"  type="text" placeholder="http://" name="settings[social][twitter]" value="<?php echo isset( $user_meta_arr['social_twitter'] ) ? $user_meta_arr['social_twitter'] : ''; ?>">
			</div>
			<div class="error-class" id="seller_user_address"></div>
		</div>
		<div class="wkmp_profile_input">

			<label for="settings[social][gplus]"><?php echo esc_html__( 'Google Plus ID', 'marketplace' ); ?></label><i> <?php echo '(' . esc_html__( 'optional', 'marketplace' ) . ')'; ?></i>
			<div class="social-seller-input">

				<input id="settings[social][gplus]"  type="text" placeholder="http://" name="settings[social][gplus]" value="<?php echo isset( $user_meta_arr['social_gplus'] ) ? $user_meta_arr['social_gplus'] : ''; ?>">

			</div>
			<div class="error-class" id="seller_user_address"></div>
		</div>
		<div class="wkmp_profile_input">
			<label for="settings[social][linked]"><?php echo esc_html__( 'Linkedin Profile ID', 'marketplace' ); ?></label><i> <?php echo '(' . esc_html__( 'optional', 'marketplace' ) . ')'; ?></i>
			<div class="social-seller-input">
				<input id="settings[social][linked]"  type="text" placeholder="http://" name="settings[social][linked]" value="<?php echo isset( $user_meta_arr['social_linkedin'] ) ? $user_meta_arr['social_linkedin'] : ''; ?>">
			</div>
			<div class="error-class" id="seller_user_address"></div>
		</div>
		<div class="wkmp_profile_input">
			<label for="settings[social][youtube]"><?php echo esc_html__( 'Youtube Profile ID', 'marketplace' ); ?></label><i> <?php echo '(' . esc_html__( 'optional', 'marketplace' ) . ')'; ?></i>
			<div class="social-seller-input">
				<input id="settings[social][youtube]"  type="text" placeholder="http://" name="settings[social][youtube]" value="<?php echo isset( $user_meta_arr['social_youtube'] ) ? $user_meta_arr['social_youtube'] : ''; ?>">
			</div>
			<div class="error-class" id="seller_user_address"></div>
		</div>
	</div>

	<!-- seller paymentmethod -->
	<div class="wkmp_profile_input">
		<label for="mp_seller_payment_details"><?php esc_html_e( 'Payment Information', 'marketplace' ); ?></label>
		<textarea name="mp_seller_payment_details" placeholder="<?php esc_html_e('eg : test@paypal.com', 'marketplace'); ?>"><?php if ( isset( $user_meta_arr['mp_seller_payment_details'] ) ) echo $user_meta_arr['mp_seller_payment_details']; ?></textarea><br /><br />
			<?php
				$paymet_gateways = WC()->payment_gateways->payment_gateways();
				do_action( 'marketplace_payment_gateway' );
			?>

	</div>

	<?php do_action( 'mp_add_seller_profile_field', $current_user->ID ); ?>

	<div class="wkmp_profile_btn">
		<input type="submit" value="<?php echo esc_html__( 'Update', 'marketplace' ); ?>" name="update_profile_submit" id="update_profile_submit" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<a href="<?php echo esc_url( get_permalink() ); ?>" class="button"><?php esc_html_e( 'Cancel', 'marketplace' ); ?></a>
	</div>

	<?php wp_nonce_field( 'edit_profile', 'wk_user_nonece' ); ?>

	</form>
</div>

</div>
