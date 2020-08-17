<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="show_if_seller">

	<div class="split-row form-row-wide">

			<p class="form-row form-group">
					<label for="first-name"><?php esc_html_e( 'First Name', 'marketplace' ); ?> <span class="required">*</span></label>
					<input type="text" class="input-text form-control" name="wk_firstname" id="wk_firstname" value="<?php if ( ! empty( $postdata['wk_firstname'] ) ) echo esc_attr( $postdata['wk_firstname'] ); ?>" required="required"/>
					<div class="error-class" id="seller_first_name"></div>
			</p>


			<p class="form-row form-group">
					<label for="last-name"><?php esc_html_e( 'Last Name', 'marketplace' ); ?> <span class="required">*</span></label>
					<input type="text" class="input-text form-control" name="wk_lastname" id="wk_lastname" value="<?php if ( ! empty( $postdata['wk_lastname'] ) ) echo esc_attr( $postdata['wk_lastname'] ); ?>" required="required"/>
					<div class="error-class" id="seller_last_name"></div>
			</p>
	</div>

		<p class="form-row form-group form-row-wide">
				<label for="company-name"><?php esc_html_e( 'Shop Name', 'marketplace' ); ?> <span class="required">*</span></label>
				<input type="text" class="input-text form-control" name="shopname" id="org-name" value="<?php if ( ! empty( $postdata['shopname'] ) ) echo esc_attr( $postdata['shopname'] ); ?>" required="required"/>
		</p>

		<p class="form-row form-group form-row-wide">
				<label for="seller-url" class="pull-left"><?php esc_html_e( 'Shop URL', 'marketplace' ); ?> <span class="required">*</span></label>
				<strong id="seller-shop-alert-msg" class="pull-right"></strong>
				<input type="text" class="input-text form-control" name="shopurl" placeholder="eg- webkul" id="seller-shop" value="<?php if ( ! empty( $postdata['shopurl'] ) ) echo esc_attr( $postdata['shopurl'] ); ?>" required="required"/>
		</p>

		<p class="form-row form-group form-row-wide">
				<label for="shop-phone"><?php esc_html_e( 'Phone Number', 'marketplace' ); ?><span class="required">*</span></label>
				<input type="text" class="input-text form-control" name="phone" id="shop-phone" value="<?php if ( ! empty( $postdata['phone'] ) ) echo esc_attr( $postdata['phone'] ); ?>" required="required"/>
		</p>

		<?php do_action( 'wk_mkt_add_register_field' ); ?>

</div>

<p class="form-row form-group user-role">
		<input type="hidden" name="role" value="seller">
</p>
