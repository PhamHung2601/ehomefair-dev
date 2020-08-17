<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<?php settings_errors(); ?>
<form method="post" action="options.php">

		<?php settings_fields( 'marketplace-settings-group' ); ?>

		<table class="form-table">

				<tr valign="top">

						<th scope="row"><label for="wkfb_mp_key_app_ID"><?php esc_html_e( 'Facebook App ID', 'marketplace' ); ?></label></th>

						<td>
								<input name="wkfb_mp_key_app_ID" type="text" class="regular-text" id="wkfb_mp_key_app_ID" value="<?php echo get_option( 'wkfb_mp_key_app_ID' ); ?>" />
								<p class="description">ex. 5464646446464646</p>
						</td>

				</tr>

				<tr>

						<th scope="row"><label for="wkfb_mp_app_secret_key"><?php esc_html_e( 'Facebook App Secret', 'marketplace' ); ?></label></th>

						<td>
								<input name="wkfb_mp_app_secret_key" class="regular-text" type="text" id="wkfb_mp_app_secret_key" value="<?php echo get_option( 'wkfb_mp_app_secret_key' ); ?>" />
								<p class="description">ex. 7582182bwedbe0469caf79ae7877b1b2</p>
						</td>

				</tr>

				<tr>

						<th scope="row"><label for="wkmpcom_minimum_com_onseller"><?php esc_html_e( 'Minimum Commission', 'marketplace' ); ?></label></th>

						<td>
								<input name="wkmpcom_minimum_com_onseller" class="regular-text" type="text" id="wkmpcom_minimum_com_onseller" value="<?php echo get_option( 'wkmpcom_minimum_com_onseller' ); ?>" />
								<p class="description"><?php echo esc_html__( 'ex. 10 in percent', 'marketplace' ); ?></p>
						</td>

				</tr>

				<tr>

						<th scope="row"><label for="wkmp_seller_menu_tile"><?php esc_html_e( 'Seller Menu Title', 'marketplace' ); ?></label></th>

						<td>
								<input name="wkmp_seller_menu_tile" type="text" class="regular-text" id="wkmp_seller_menu_tile" value="<?php echo get_option( 'wkmp_seller_menu_tile' ); ?>" />
						</td>

				</tr>

				<input name="wkmp_seller_page_title" type="hidden" id="wkmp_seller_page_title" value="<?php echo get_option( 'wkmp_seller_page_title' ); ?>" /></td>

				<tr>

						<th scope="row"><?php esc_html_e( 'Auto Approve Seller', 'marketplace' ); ?></th>

						<td>
								<label for="wkmp_auto_approve_seller">
										<input name="wkmp_auto_approve_seller" type="checkbox" id="wkmp_auto_approve_seller" value="1" <?php checked( get_option( 'wkmp_auto_approve_seller' ), 1 ); ?>/>
										<?php echo esc_html_e( 'Seller will be automatically approved', 'marketplace' ); ?>
								</label>
						</td>

				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Separate seller dashboard', 'marketplace' ); ?></th>

					<td>
						<label for="wkmp_enable_seller_seperate_dashboard">
							<input type="checkbox" name="wkmp_enable_seller_seperate_dashboard" id="wkmp_enable_seller_seperate_dashboard" value="1" <?php checked( get_option( 'wkmp_enable_seller_seperate_dashboard' ), 1 ); ?>><?php echo esc_html__( 'If checked, seller will have separate dashboard like admin', 'marketplace' ); ?>
						</label>
					</td>
				</tr>

				<tr>

						<th scope="row"><?php esc_html_e( 'Separate Login Form', 'marketplace' ); ?></th>

						<td>
								<label for="wkmp_show_seller_seperate_form">
										<input name="wkmp_show_seller_seperate_form" type="checkbox" id="wkmp_show_seller_seperate_form" value="1" <?php checked( get_option( 'wkmp_show_seller_seperate_form' ), 1 ); ?>/>
										<?php echo esc_html_e( 'If checked, a separate login form will be created for sellers', 'marketplace' ); ?>
								</label>
						</td>

				</tr>

				<?php	do_action( 'wkmp_add_settings_field' ); ?>

		</table>

		<?php	submit_button(); ?>

</form>
