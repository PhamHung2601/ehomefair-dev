<?php
/**
 * Check voucher code with Qrcode and Barcode
 * 
 * Handles to check voucher code with Qrcode and Barcode
 * 
 * Override this template by copying it to yourtheme/woocommerce/woocommerce-pdf-vouchers/qrcode/woo-vou-check-qrcode.php
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.7.1
 */

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
?>
<!doctype html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=3.0">
	<title><?php echo apply_filters( 'woo_vou_check_qrcode_page_title', esc_html__( 'Redeem Voucher Code', 'woovoucher' ) ); ?></title>	
	<?php echo apply_filters ( 'woo_vou_check_qrcode_cstm_style', '<style></style>' ); ?>
	<?php wp_head(); ?>
</head>
<body><?php
	
	global $woo_vou_voucher, $current_user, $woo_vou_vendor_role;
	$redeem = false;
	$prefix = WOO_VOU_META_PREFIX;
		
	//Get User roles
	$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
	$user_role	= array_shift( $user_roles );

	// Get option whether to allow all vendor to redeem voucher codes
    $vou_enable_vendor_access_all_voucodes = get_option('vou_enable_vendor_access_all_voucodes');

	// Get "Check Voucher Code for all logged in users" option
	$vou_enable_logged_user_check_voucher_code = get_option('vou_enable_logged_user_check_voucher_code');
	$vou_enable_logged_user_redeem_vou_code = get_option('vou_enable_logged_user_redeem_vou_code');

	// Get "Check Voucher Code for guest users" option
	$vou_enable_guest_user_check_voucher_code = get_option('vou_enable_guest_user_check_voucher_code');

	$order_customer	= '';

	//voucher admin roles
	$admin_roles	= woo_vou_assigned_admin_roles();

	if( !empty( $redeem_response ) && $redeem_response == 'success' ) {

		echo "<div class='woo-vou-voucher-code-msg success'>" . esc_html__( 'Thank you for your business, voucher code submitted successfully.', 'woovoucher' ) . "</div>";
		unset( $_GET['woo_vou_code'] );
		unset( $_POST['voucode'] );
		$redeem = true;

	} elseif( !empty( $redeem_response ) && !empty( $redeem_response['success'] ) && !empty( $redeem_response['success_message'] ) && $redeem_response['success'] == 'success' ) {

		echo "<div class='woo-vou-voucher-code-msg success'>" . $redeem_response['success_message'] . "</div>";
		unset( $_GET['woo_vou_code'] );
		unset( $_POST['voucode'] );
		$redeem = true;

	}

	//Check if the user is logged in or is allow for guest user.  If not, show the login form.
	if ( !is_user_logged_in() && $vou_enable_guest_user_check_voucher_code != "yes" && apply_filters( 'woo_vou_without_login_access_qrcode_redeem', true ) ) {

		$args = array(
		        'echo'		=> true,
		        'redirect'	=> add_query_arg( get_site_url(), $_SERVER["QUERY_STRING"] )
		);

		wp_login_form( $args );
	} else {

		if( !$redeem ) {

			foreach ( $voucodes as $voucode ) {

				$voucode = trim( $voucode ); // remove spaces from voucher code

				// assign voucher code to $_POST variable.
				// Needed because $_POST['voucode'] used in function woo_vou_check_voucher_code()
				$_POST['voucode'] = $voucode;

				// Check voucher code and get result
				$voucher_data = $woo_vou_voucher->woo_vou_check_voucher_code();

				if( !empty( $voucher_data ) ) {

					if( empty( $voucode ) ) {

						echo "<div class='woo-vou-voucher-code-msg error'>" . esc_html__( 'Please enter voucher code.', 'woovoucher' ) . "</div>";
					} else if( !empty( $voucher_data['success'] ) ) { ?>
						<form class="woo-vou-check-vou-code-form" method="post" action="">
							<?php echo apply_filters('woo_vou_voucher_code_input','<input type="hidden" name="voucode" value="'.$voucode.'" />',$voucode); ?>
							<table class="form-table woo-vou-check-code">
								<?php 
									/**
									 * Add do action to add custom code by other plugins
									 */
									do_action( 'woo_vou_check_qrcode_top', $redeem_response ); 
									$vou_allow_redeem_expired_voucher = get_option( 'vou_allow_redeem_expired_voucher' );
								?>
								<tr>
									<td>
										<?php
											$class = 'success';
											// Check if code is expired, if yes than check if Allow Redeem for Expired code is not tick
											if( isset( $voucher_data['expire'] ) && $voucher_data['expire'] == true 
												&& ( empty( $vou_allow_redeem_expired_voucher ) || $vou_allow_redeem_expired_voucher == 'no' ) ) {
												$class = 'error';
											}
										?>									
										<div class="woo-vou-voucher-code-msg <?php echo $class; ?>">
											<span><?php echo $voucher_data['success']; ?></span>
										</div>
										<?php do_action('woo_vou_before_qrcode_product_details', $voucher_data );?>
										<?php echo $voucher_data['product_detail']; ?>
									</td>
								</tr>
								<?php

								// Get voucher id from voucher code
								$voucodeid 			= woo_vou_get_voucodeid_from_voucode($voucode);
								$voucher_post_data 	= get_post( $voucodeid ); // Get voucher post data
								$order_id 			= get_post_meta( $voucodeid , $prefix.'order_id' , true ); // Get order id from voucher meta
								$order_customer		= get_post_meta( $order_id , '_customer_user' , true ); // Get order customer user id from order meta.
								$allow_user_redeem 	= false;

								/*
								* If user role is admin
								* If user role is voucher vendor then check the user is voucher post author 
								* OR checks whether "Enable Vendor to access all voucher codes" is tick
								* If user is customer of this voucher's order and check logged in users can Check and Redeem allowed
								*/
								if( in_array( $user_role, $admin_roles ) ){
									$allow_user_redeem = true;
								} elseif( in_array( $user_role, $woo_vou_vendor_role ) ) {
									if ( ( $voucher_post_data->post_author == $current_user->ID ) || ( empty($vou_enable_vendor_access_all_voucodes) || $vou_enable_vendor_access_all_voucodes != 'no' ) ){
										$allow_user_redeem = true;
									}
								} elseif( $order_customer == $current_user->ID ){
									if( ( $vou_enable_logged_user_check_voucher_code == 'yes' ) && ( $vou_enable_logged_user_redeem_vou_code == 'yes' ) ){
										$allow_user_redeem = true;
									}
								}

								/*
								* Check if code is not expired, if expired than check if Allow Redeem for Expired code tick
								* Check allow user redeem
								* Checks if we should give access to logged in User
								*/
								if( ( empty( $voucher_data['expire'] )
									  || ( !empty( $voucher_data['expire'] ) 
										  && ( !empty( $vou_allow_redeem_expired_voucher ) 
											&& $vou_allow_redeem_expired_voucher == 'yes' )
										 )
									) && ( $allow_user_redeem || apply_filters( 'woo_vou_without_login_access_qrcode_redeem_submit', false ) )
								  ) { ?>
									<tr class="woo-vou-voucher-code-submit-wrap">
										<td>
											<?php 
												echo apply_filters('woo_vou_voucher_code_submit',
													'<input type="submit" id="woo_vou_voucher_code_submit" name="woo_vou_voucher_code_submit" class="button-primary" value="' . esc_html__( "Redeem", "woovoucher" ) . '"/>'
												);
											?>
											<div class="woo-vou-loader woo-vou-voucher-code-submit-loader"><img src="<?php echo esc_url(WOO_VOU_IMG_URL);?>/ajax-loader.gif"/></div>
										</td>
									</tr>									
								<?php } ?>
								<?php 
									/**
									 * Add do action to add custom code by other plugins
									 */
									do_action( 'woo_vou_check_qrcode_bottom' ); 
								?>
							</table>
						</form><?php

					} else if( !empty( $voucher_data['error'] ) ) {
						echo "<div class='woo-vou-voucher-code-msg woo-vou-check-qrcode-error error'>" . $voucher_data['error'] . "</div>";
					} else if( !empty( $voucher_data['used'] ) ) { ?>
						<form class="woo-vou-check-vou-code-form" method="post" action="">
							<input type="hidden" name="voucode" value="<?php echo $voucode; ?>" />
						
						<table class="form-table woo-vou-check-code">
							<?php 
								/**
								 * Add do action to add custom code by other plugins
								 */
								do_action( 'woo_vou_check_qrcode_top' ); 
							?>
							<tr>
								<td>								
									<div class="woo-vou-voucher-code-msg error">
										<span><?php echo $voucher_data['used']; ?></span>
									</div>
									<?php do_action('woo_vou_before_qrcode_product_details', $voucher_data );?>
									<?php echo $voucher_data['product_detail']; ?>
								</td>
							</tr>
							<?php 
								/**
								 * Add do action to add custom code by other plugins
								 */
								do_action( 'woo_vou_check_qrcode_bottom' ); 
							?>
						</table>
						</form><?php
					}
				}
			} // End of foreach
		} // End of if $redeem
	}?>
	
    <?php do_action( 'woo_vou_check_qrcode_cstm_script'); ?>  

</body>
</html><?php
exit();