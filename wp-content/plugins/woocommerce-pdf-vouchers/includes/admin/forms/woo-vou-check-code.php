<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Check Voucher Code Page
 * 
 * The html markup for the check voucher code
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.1.0
 */
?>
<div class="wrap">
    
    <h2><?php echo esc_html__( 'Check Voucher Code', 'woovoucher' ); ?></h2>
	
    <div id="woo_vou_check_voucher_code_wrap" class="post-box-container">
		<div class="metabox-holder">
			<div class="meta-box-sortables ui-sortable">
				<div id="woo_vou_check_voucher_code_container" class="postbox">
					<div class="handlediv" title="<?php echo esc_html__( 'Click to toggle', 'woovoucher' ); ?>"><br /></div>
					<!-- settings box title -->
					<h3 class="hndle">
						<span><?php echo esc_html__( 'Check Voucher Code', 'woovoucher' ); ?></span>
					</h3>
					<div class="inside"><?php 
						do_action( 'woo_vou_check_code_content' );?>
					</div><!-- .inside -->
				</div><!-- #woo_vou_check_voucher_code_container -->
			</div><!-- .meta-box-sortables ui-sortable -->
		</div><!-- .metabox-holder -->
	</div><!-- #woo_vou_check_voucher_code_wrap -->
</div><!-- .wrap -->