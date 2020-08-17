<?php

/**
 * Recipient Fields Template
 * 
 * Handles to load Recipient Fields template
 * 
 * Override this template by copying it to yourtheme/woocommerce/woocommerce-pdf-vouchers/recipient-fields/woo-vou-recipient-message.php
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.3.3
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product;

//Get Prefix
$prefix		= WOO_VOU_META_PREFIX;

$recipient_message_label	= !empty( $recipient_message_label ) ? $recipient_message_label : esc_html__( 'Message to Recipient' , 'woovoucher' );
$msg_maxlength				= intval( $recipient_message_max_length );
if( !empty( $recipient_message_required ) && $recipient_message_required == "yes" ) {
	$recipient_message_label .= '<span class="woo-vou-gift-field-required"> *</span>';
}
	?>
<tr>
	<td class="label">
		<label for="recipient_message-<?php echo $variation_id; ?>"><?php echo $recipient_message_label;?></label>
	</td>
	<td class="value">
		<textarea 
			<?php if( !empty( $msg_maxlength ) ) { echo 'maxlength="'.$msg_maxlength.'"'; } ?> 
			class="woo-vou-recipient-details" 
			id="recipient_message-<?php echo $variation_id; ?>" 
			<?php echo !empty( $recipient_message_required ) && $recipient_message_required == "yes" ? 'data-required="true"' : ''; ?> 
			name="<?php echo $prefix; ?>recipient_message[<?php echo $variation_id; ?>]"><?php echo $recipient_message; ?></textarea>
		<?php
			if( !empty( $recipient_message_desc ) ) {
				echo '<small class="description">' . $recipient_message_desc . '</small>';
			}
		?>
	</td>
</tr><?php 

// Add recipient field on product page after message
do_action( 'woo_vou_add_field_on_product_page_after_message', $variation_id );

?>