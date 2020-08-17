<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Settings Page
 *
 * The code for the plugins main settings page
 *
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */		
	global $current_user;
 ?>
<div class="wrap">

	<!-- plugin name -->
	<h2 class="woo-vou-settings-title"><?php esc_html_e( 'Voucher Codes', 'woovoucher' ); ?></h2><br />
	
	<?php do_action( 'woo_vou_admin_voucher_code_after_header' ); ?>
	
		<!-- beginning of the left meta box section -->
		<div class="content woo-vou-content-section">
		
			<h2 class="nav-tab-wrapper woo-vou-h2">
				<?php

					//Get vouchers code tabs
					$voucher_code_tabs	= array(
											'purchased' => array(
																	'name' => esc_html__( 'Unredeemed Voucher Codes', 'woovoucher' ),
																	'path' => WOO_VOU_ADMIN .'/forms/voucher-codes/woo-vou-purchased-list.php'
																),
											'used' 		=> array(
																	'name' => esc_html__( 'Redeemed Voucher Codes', 'woovoucher' ),
																	'path' => WOO_VOU_ADMIN .'/forms/voucher-codes/woo-vou-used-list.php'
																),
											'expired' 	=> array(
																	'name' => esc_html__( 'Expired Voucher Codes', 'woovoucher' ),
																	'path' => WOO_VOU_ADMIN .'/forms/voucher-codes/woo-vou-purchased-list-expire.php'
																),
											);
												
					//Get voucher codes tabs and their display name
					$voucher_code_tabs 		= apply_filters( 'woo_vou_admin_voucher_code_tabs', $voucher_code_tabs );

					// default tabs key
					$default_key	= is_array( $voucher_code_tabs ) ? key( $voucher_code_tabs ) : '';

					//Get voucher page url
					$voucher_codes_page_url = add_query_arg( array( 'page' => 'woo-vou-codes' ), admin_url( 'admin.php' ) );

					//Get current voucher tab
					$curr_tab 	= !empty( $_GET['vou-data'] ) ? $_GET['vou-data'] : $default_key;

					//Check voucher tabs exists
					if( !empty( $voucher_code_tabs ) ) {

						foreach ( $voucher_code_tabs as $tab => $tab_details ) {

							//Get selected tab and page url
							$selected	= ( $tab == $curr_tab ) ? ' nav-tab-active' : '';
							$vou_code_page_url 	= add_query_arg( array( 'vou-data' => $tab ), $voucher_codes_page_url );
							?>
					        <a class="nav-tab<?php echo $selected;?>" href="<?php echo esc_url($vou_code_page_url);  ?>"><?php echo !empty( $tab_details['name'] ) ? $tab_details['name'] : '';?></a>
							<?php
						}
					}?>
		    </h2><!--nav-tab-wrapper-->
		    <!--beginning of tabs panels-->
			 <div class="woo-voucher-code-content">
			 
			 	<?php
			 		//Check if voucher tab exists then include file
					if( !empty( $voucher_code_tabs[$curr_tab]['path'] ) && file_exists( $voucher_code_tabs[$curr_tab]['path'] ) ) {
						include_once( $voucher_code_tabs[$curr_tab]['path'] );
					} else {
						echo esc_html__( 'File not found.', 'woovoucher' );
					}
				?>
			 <!--end of tabs panels-->
			 </div>
		<!--end of the left meta box section -->
		</div><!--.content woo-vou-content-section-->
	
<!--end .wrap-->
</div>