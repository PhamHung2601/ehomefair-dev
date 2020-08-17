<?php
/**
 * Handels to show change voucher code redeem option and voucher price
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.2.4
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

$current_currency = '';

if (class_exists('WOOCS')) {
    global $WOOCS;

    $current_currency = $WOOCS->current_currency;
    
    $currencies = $WOOCS->get_currencies();
    $default_currency = '';

    if (!empty($currencies) AND is_array($currencies)) {

        foreach ( $currencies as $key => $currency) {
            if ($currency['is_etalon']) {
                $default_currency = $key;
                break;
            }
        }
    }

    $WOOCS->set_currency($default_currency);
}

/*
* If user role is admin
* If user role is voucher vendor then check the user is voucher post author 
* OR checks whether "Enable Vendor to access all voucher codes" is tick
*/
$allow_user_redeem  = false;
if( in_array( $user_role, $admin_roles ) ) {
    $allow_user_redeem = true;
} elseif( in_array( $user_role, $woo_vou_vendor_role ) ) {
    if ( ( $voucher_data->post_author == $current_user->ID ) || ( $vou_enable_vendor_access_all_voucodes == 'yes' ) ){
        $allow_user_redeem = true;
    }
}

/**
 * Check allow user redeem
 * If partial redeem enabled then show partial redeem option
 * If condition is satisifed than logged-in user is non-admin and either non-vendor or not allowed to access that voucher code
 * If condition is satisfied than it hide redeem button else shows it
 */

if( $allow_user_redeem && ( (int)$vou_code_remaining_redeem_price > 0 ) ) {
    if( $enable_partial_redeem == "yes" ) { 
    ob_start(); ?>

        <td>
            <label for="vou_redeem_method"><?php esc_html_e( 'Redeem Method', 'woovoucher' ); ?></label>
        </td>
        <td>
            <select name="vou_redeem_method" id="vou_redeem_method">
            <?php
                foreach ( $redeem_methods as $key => $value ) {
                    echo '<option value="'. $key .'">'. $value .'</option>';
                }?>
            </select><br/>
            <?php
            $partially_redeemed = get_post_meta( $voucodeid, $prefix . 'redeem_method', true );
            if( !empty( $partially_redeemed ) && $partially_redeemed == 'partial' ) { ?>
                <span class="description"><?php echo sprintf( esc_html__( 'If you select %sFull%s method then it will redeem remaining amount. If you select %sPartial%s then you have option to enter the partial redeem amount.', 'woovoucher' ), '<b>', '</b>', '<b>', '</b>' ); ?></span>
            <?php } else { ?>
                <span class="description"><?php echo sprintf( esc_html__( 'If you select %sFull%s method then it will redeem full amount. If you select %sPartial%s then you have option to enter the partial redeem amount.', 'woovoucher' ), '<b>', '</b>', '<b>', '</b>' ); ?></span>
            <?php } ?>                  
        </td>

    <?php 
    $response['redeem_method'] = ob_get_clean();
    ob_start();
    ?>
        <td>
            <label for="vou_partial_redeem_amount"><?php esc_html_e( 'Redeem Amount', 'woovoucher' ); ?></label>
        </td>
        <td>
            <input type="number" name="vou_partial_redeem_amount" id="vou_partial_redeem_amount" value="<?php echo $vou_code_remaining_redeem_price; ?>" max="<?php echo $vou_code_remaining_redeem_price; ?>" step="any"/><br />
            <span class="description"><?php esc_html_e( 'Enter the amount you want to redeem.', 'woovoucher' ); ?></span>
        </td>
    <?php 
    $response['redeem_amount'] = ob_get_clean();

    }
}

ob_start();

echo wc_price( $vou_code_remaining_redeem_price ); ?>

<input type="hidden" value="<?php echo $vou_code_total_price; ?>" name="vou_code_total_price" id="vou_code_total_price" />
<input type="hidden" value="<?php echo $vou_code_total_redeemed_price; ?>" name="vou_code_total_redeemed_price" id="vou_code_total_redeemed_price" />
<input type="hidden" value="<?php echo $vou_code_remaining_redeem_price; ?>" name="vou_code_remaining_redeem_price" id="vou_code_remaining_redeem_price" />
<input type="hidden" value="<?php echo $enable_partial_redeem; ?>" name="vou_enable_partial_redeem" id="vou_enable_partial_redeem" />

<?php 

$response['price'] = ob_get_clean();


