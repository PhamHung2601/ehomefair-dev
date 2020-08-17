<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="show_if_seller" <?php echo $role_style; ?>>

		<div class="split-row form-row-wide">

				<p class="form-row form-group">
						<label for="first-name"><?php esc_html_e( 'First Name', 'marketplace' ); ?> <span class="required">*</span></label>
						<input type="text" class="input-text form-control" name="wk_firstname" id="wk_firstname" value="<?php if ( ! empty( $postdata['wk_firstname'] ) ) echo esc_attr( $postdata['wk_firstname'] ); ?>" required="required" disabled/>
						<div class="error-class" id="seller_first_name"></div>
				</p>

				<p class="form-row form-group">
						<label for="last-name"><?php esc_html_e( 'Last Name', 'marketplace' ); ?> <span class="required">*</span></label>
						<input type="text" class="input-text form-control" name="wk_lastname" id="wk_lastname" value="<?php if ( ! empty( $postdata['wk_lastname'] ) ) echo esc_attr( $postdata['wk_lastname'] ); ?>" required="required" disabled/>
						<div class="error-class" id="seller_last_name"></div>
				</p>
		</div>

		<p class="form-row form-group form-row-wide">
				<label for="company-name"><?php esc_html_e( 'Shop Name', 'marketplace' ); ?> <span class="required">*</span></label>
				<input type="text" class="input-text form-control" name="shopname" id="org-name" value="<?php if ( ! empty( $postdata['shopname'] ) ) echo esc_attr( $postdata['shopname'] ); ?>" required="required" disabled/>
		</p>

		<p class="form-row form-group form-row-wide">
				<label for="seller-url" class="pull-left"><?php esc_html_e( 'Shop URL', 'marketplace' ); ?> <span class="required">*</span></label>
				<strong id="seller-shop-alert-msg" class="pull-right"></strong>
				<input type="text" class="input-text form-control" name="shopurl" placeholder="eg- webkul" id="seller-shop" value="<?php if ( ! empty( $postdata['shopurl'] ) ) echo esc_attr( $postdata['shopurl'] ); ?>" required="required" disabled/>
		</p>

		<p class="form-row form-group form-row-wide">
				<label for="shop-phone"><?php esc_html_e( 'Phone Number', 'marketplace' ); ?><span class="required">*</span></label>
				<input type="text" class="input-text form-control" name="phone" id="shop-phone" value="<?php if ( ! empty( $postdata['phone'] ) ) echo esc_attr( $postdata['phone'] ); ?>" required="required" disabled/>
		</p>

		<?php do_action( 'wk_mkt_add_register_field' ); ?>
</div>


<p class="form-row form-group user-role">

	<ul class="nav mp-role-selector" role="tablist" style="padding:0;">

		<li class="active" data-target="0">

			<label class="radio" style="padding:0;margin:0;">
						<input type="radio" name="role" value="customer"<?php checked( $role, 'customer' ); ?> >
						<?php esc_html_e( 'I am a customer', 'marketplace' ); ?>
				</label>

		</li>

			<li data-target="1">

				<label class="radio" style="padding:0;margin:0;">
						<input type="radio" name="role" value="seller"<?php checked( $role, 'seller' ); ?> >
						<?php echo esc_html_e( 'I am a seller', 'marketplace' ); ?>
				</label>

			</li>
	</ul>

</p>
