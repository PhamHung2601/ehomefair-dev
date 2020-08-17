<?php

/**
 * Recipient Fields Template
 * 
 * Handles to load Recipient Fields template
 * 
 * Override this template by copying it to yourtheme/woocommerce/woocommerce-pdf-vouchers/recipient-fields/woo-vou-recipient-email.php
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.3.3
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product;

//Get Prefix
$prefix		= WOO_VOU_META_PREFIX;
$recipient_email_label = !empty( $recipient_email_label ) ? $recipient_email_label : esc_html__( 'Recipient Email' , 'woovoucher' );
if( !empty( $recipient_email_required ) && $recipient_email_required == "yes" ) {
	$recipient_email_label .= '<span class="woo-vou-gift-field-required"> *</span>';
}
?>
<tr>
	<td class="label">
		<label for="recipient_email-<?php echo $variation_id; ?>"><?php echo $recipient_email_label; ?></label>
	</td>
	<td class="value">
		<input 
			type="text" 
			class="woo-vou-recipient-details" 
			value="<?php echo $recipient_email; ?>" 
			id="recipient_email-<?php echo $variation_id; ?>" 
			<?php echo !empty( $recipient_email_required ) && $recipient_email_required == "yes" ? 'data-required="true"' : ''; ?> 
			name="<?php echo $prefix; ?>recipient_email[<?php echo $variation_id; ?>]"
		/>
		<?php
			if( !empty( $recipient_email_desc ) ) {
				echo '<small class="description">' . $recipient_email_desc . '</small>';
			}
		?>
	</td>
</tr><?php 

// Add Recipient field after email field
do_action( 'woo_vou_add_field_on_product_page_after_email', $variation_id );