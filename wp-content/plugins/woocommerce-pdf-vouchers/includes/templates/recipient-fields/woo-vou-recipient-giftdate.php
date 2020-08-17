<?php

/**
 * Recipient Fields Template
 * 
 * Handles to load Recipient Fields template
 * 
 * Override this template by copying it to yourtheme/woocommerce/woocommerce-pdf-vouchers/recipient-fields/woo-vou-recipient-giftdate.php
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.3.3
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product;

//Get Prefix
$prefix		= WOO_VOU_META_PREFIX;

$recipient_giftdate_label = !empty( $recipient_giftdate_label ) ? $recipient_giftdate_label : esc_html__( 'Recipient\'s Gift Date' , 'woovoucher' );
if( !empty( $recipient_giftdate_required ) && $recipient_giftdate_required == "yes" ) {
	$recipient_giftdate_label .= '<span class="woo-vou-gift-field-required"> *</span>';
}
$dateformat = apply_filters( 'woo_vou_giftdate_datepicker_format', 'mm/dd/yy' );

$datepicker_id = 'recipient_giftdate-' . $variation_id;
if( !empty( $delivery_method_key ) ) {
	$datepicker_id .= '-'.$delivery_method_key;
}

?>
<tr>
	<td class="label">
		<label for="recipient_giftdate-<?php echo $variation_id; ?>"><?php echo $recipient_giftdate_label; ?></label>
	</td>
	<td class="value">
		<input 
			type="text" 
			class="woo-vou-recipient-details" 
			placeholder="<?php echo $dateformat; ?>" 
			rel="<?php echo $dateformat; ?>" 
			value="<?php echo $recipient_giftdate; ?>" 
			id="<?php echo $datepicker_id; ?>" 
			<?php echo !empty( $recipient_giftdate_required ) && $recipient_giftdate_required == "yes" ? 'data-required="true"' : ''; ?>
			name="<?php echo $prefix; ?>recipient_giftdate[<?php echo $variation_id; ?>]" 
		/>
		<?php
			if( !empty( $recipient_giftdate_desc ) ) {
				echo '<small class="description">' . $recipient_giftdate_desc . '</small>';
			}
		?>
	</td>
</tr><?php 

// Add Recipient field after email field
do_action( 'woo_vou_add_field_on_product_page_after_giftdate', $variation_id );

?>