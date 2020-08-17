<?php

/**
 * Recipient Fields Template
 * 
 * Handles to load Recipient Fields template
 * 
 * Override this template by copying it to yourtheme/woocommerce/woocommerce-pdf-vouchers/recipient-fields/woo-vou-recipient-name.php
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.3.3
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product;

//Get Prefix
$prefix		= WOO_VOU_META_PREFIX;

$recipient_name_lable	= !empty( $recipient_name_label ) ? $recipient_name_label : esc_html__( 'Recipient Name' , 'woovoucher' );
		  		
if( !empty( $recipient_name_required ) && $recipient_name_required == "yes" ) {
	$recipient_name_lable .= '<span class="woo-vou-gift-field-required"> *</span>';
}

$name_maxlength	= !empty( $recipient_name_max_length ) ? intval( $recipient_name_max_length ) : '';
?>
<tr>
	<td class="label">
		<label for="recipient_name-<?php echo $variation_id; ?>"><?php echo $recipient_name_lable; ?></label>
	</td>
	<td class="value">
		<input 
			type="text" 
			class="woo-vou-recipient-details" 
			<?php if( !empty($name_maxlength) ) { echo 'maxlength="'.$name_maxlength.'"'; } ?> 
			value="<?php echo $recipient_name; ?>" 
			id="recipient_name-<?php echo $variation_id; ?>" 
			<?php echo !empty( $recipient_name_required ) && $recipient_name_required == "yes" ? 'data-required="true"' : ''; ?> 
			name="<?php echo $prefix; ?>recipient_name[<?php echo $variation_id; ?>]" />
		<?php
			if( !empty( $recipient_name_desc ) ) {
				echo '<small class="description">' . $recipient_name_desc . '</small>';
			}
		?>
	</td>
</tr>
<?php

// Add Field after Name on Product Page
do_action( 'woo_vou_add_field_on_product_page_after_name', $variation_id );
?>