<?php

if ( ! defined( 'ABSPATH' ) ) {

	exit;
}


wc_print_notices();
$current_user = wp_get_current_user();
$firstname    = get_user_meta ( $current_user->ID, 'first_name' );
$lastname     = get_user_meta ( $current_user->ID, 'last_name' );


?>

<form action="" method="post" name="mp-seller-change-password" id="mp-seller-change-password">
	<?php do_action( 'woocommerce_edit_account_form_start' ); ?>
	<div style="display:none">
		<p class="form-row form-row-first">
			<label for="account_first_name"><?php esc_html_e( 'First Name', 'marketplace' ); ?> <span class="required">*</span></label>
			<input type="text" class="input-text" name="account_first_name" id="account_first_name" value="<?php echo esc_attr( $firstname[0] ); ?>" readonly />
		</p>
		<p class="form-row form-row-last">
			<label for="account_last_name"><?php esc_html_e( 'Last Name', 'marketplace' ); ?> <span class="required">*</span></label>
			<input type="text" class="input-text" name="account_last_name" id="account_last_name" value="<?php echo esc_attr( $lastname[0] ); ?>" readonly />
		</p>
		<div class="clear"></div>

		<p class="form-row form-row-wide">
			<label for="account_email"><?php esc_html_e( 'Email address', 'marketplace' ); ?> <span class="required">*</span></label>
			<input type="email" class="input-text" name="account_email" id="account_email" value="<?php echo esc_attr( $current_user->user_email ); ?>" readonly />
		</p>
	</div>

	<fieldset>
		<legend><h2><?php esc_html_e( 'Password Change', 'marketplace' ); ?></h2></legend>

		<p class="form-row form-row-wide">
			<label for="password_current"><?php esc_html_e( 'Current Password (Leave blank to leave unchanged)', 'marketplace' ); ?></label>
			<input type="password" class="input-text" name="password_current" id="password_current" />
		</p>
		<p class="form-row form-row-wide">
			<label for="password_1"><?php esc_html_e( 'New Password (Leave blank to leave unchanged)', 'marketplace' ); ?></label>
			<input type="password" class="input-text" name="password_1" id="password_1" />
		</p>
		<p class="form-row form-row-wide">
			<label for="password_2"><?php esc_html_e( 'Confirm New Password', 'marketplace' ); ?></label>
			<input type="password" class="input-text" name="password_2" id="password_2" />
		</p>
	</fieldset>
	<div class="clear"></div>

	<?php do_action( 'woocommerce_edit_account_form' ); ?>

	<p>
		<?php wp_nonce_field( 'save_account_details' ); ?>
		<input type="submit" class="button" name="save_account_details" id="save_account_details" value="<?php esc_html_e( 'Save changes', 'marketplace' ); ?>" />
		<input type="hidden" name="action" value="save_account_details" />
	</p>

	<?php do_action( 'woocommerce_edit_account_form_end' ); ?>

</form>
