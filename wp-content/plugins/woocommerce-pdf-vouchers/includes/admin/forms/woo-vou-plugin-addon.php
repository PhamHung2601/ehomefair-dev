<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WooCommerce - PDF Vouchers Extensions
 *
 * The html markup for the plugin extensions list
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.8.15
 */

?>

<div class="wrap">

    <div class="woo-vou-extension-main-title">
        <!-- wpweb logo -->
        <img src="<?php echo esc_url(WOO_VOU_IMG_URL) . '/wpweb-logo.png'; ?>" class="wpweb-logo" alt="<?php esc_html_e( 'WP Web Logo', 'woovoucher' );?>" />
        <!-- plugin name -->
        <h2><?php esc_html_e( 'WooCommerce PDF Vouchers - Add-ons', 'woovoucher' ); ?></h2>
    </div>
    <p><?php esc_html_e( 'The following are available extensions to extend WooCommerce PDF Vouchers functionality.', 'woovoucher' ); ?></p><br />

        <!-- beginning of the content section -->
        <div class="content woo-vou-content-section">

            <!--beginning of extensions main-->
             <div class="woo-vou-extensions-main">

             <?php
                    /**
                     * Fires woocommerce PDF vouchers extensions box before.
                     * 
                     * @package WooCommerce - PDF Vouchers
                     * @since 3.8.15
                     */
                    do_action( 'woo_vou_extensions_box_before', '' );
                ?>

                <div class="woo-vou-available-extensions-inner-box woo-vou-schedule-emails">
                    <img class="woo-vou-extensions-thumbnail" alt="Extensions Image" src="<?php echo esc_url(WOO_VOU_IMG_URL) . '/reverse-redemption-banner.png'; ?>">
                    <h3><?php esc_html_e( 'WooCommerce PDF Vouchers - Reverse Redemption add-on', 'woovoucher' ); ?></h3>
                    <p><?php printf( esc_html__( 'WooCommerce PDF vouchers - %sReverse Redemption add-on%s allows you to undo already redeemed voucher codes.', 'woovoucher' ), '<strong>','</strong>'); ?></p>
                    <span class="woo-vou-action-links"><a target="_blank" href="https://1.envato.market/61Ddr" class="button button"><?php esc_html_e( 'Purchase Extension', 'woovoucher' ); ?></a></span>
                </div>
                <div class="woo-vou-available-extensions-inner-box woo-vou-schedule-emails">
                    <img class="woo-vou-extensions-thumbnail" alt="Extensions Image" src="<?php echo esc_url(WOO_VOU_IMG_URL) . '/otp-verification-banner.png'; ?>">
                    <h3><?php esc_html_e( 'WooCommerce PDF Vouchers - OTP Verification add-on', 'woovoucher' ); ?></h3>
                    <p><?php printf( esc_html__( 'WooCommerce PDF vouchers - %sOTP Verification add-on%s allows you to send OTP (Once Time Passcode) either on email or mobile on voucher code redemption.', 'woovoucher' ), '<strong>','</strong>'); ?></p>
                    <span class="woo-vou-action-links"><a target="_blank" href="https://1.envato.market/nvBE7" class="button button"><?php esc_html_e( 'Purchase Extension', 'woovoucher' ); ?></a></span>
                </div>
                <?php
                    /**
                     * Fires woocommerce PDF vouchers extensions box after.
                     * 
                     * @package WooCommerce - PDF Vouchers
                     * @since 3.8.15
                     */
                    do_action( 'woo_vou_extensions_box_after', '' );
                ?>

             </div><!--end of extensions main-->

        </div><!--.content woo-vou-content-section-->

</div><!--end .wrap-->