<?php

/**
 * Recipient Fields Template
 * 
 * Handles to load Recipient Fields template
 * 
 * Override this template by copying it to yourtheme/woocommerce/woocommerce-pdf-vouchers/recipient-fields/woo-vou-recipient-cstm.php
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.5.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product;

//Get Prefix
$prefix					= WOO_VOU_META_PREFIX;
$recipient_cstm_label	= $cstm_recipient_details_args[$recipient_column.'_label'];
$recipient_col_val		= $recipient_col_desc = $maxlength = $recipient_html = '';
$variation_id			= 0;
		  		
if( !empty( $cstm_recipient_details_args[$recipient_column.'_required'] ) && $cstm_recipient_details_args[$recipient_column.'_required'] == "yes" ) {
	$recipient_cstm_label .= '<span class="woo-vou-gift-field-required"> *</span>';
}

if( !empty( $cstm_recipient_details_args[$recipient_column] ) ) {
	$recipient_col_val = $cstm_recipient_details_args[$recipient_column];
}

if( !empty( $cstm_recipient_details_args[$recipient_column.'_desc'] ) ) {
	$recipient_col_desc = $cstm_recipient_details_args[$recipient_column.'_desc'];
}

if( !empty( $cstm_recipient_details_args['variation_id'] ) ) {
	$variation_id = $cstm_recipient_details_args['variation_id'];
}

$cstm_maxlength	= !empty( $cstm_recipient_details_args[$recipient_column.'_max_length'] ) ? intval( $cstm_recipient_details_args[$recipient_column.'_max_length'] ) : '';
if( !empty( $cstm_maxlength ) ) {

	$maxlength = "maxlength='{$cstm_maxlength}'";
}
?>
<tr>
	<td class="label">
		<label for="<?php echo $recipient_column.'-'.$variation_id; ?>"><?php echo $recipient_cstm_label; ?></label>
	</td>
	<td class="value">
		<?php 
			ob_start();
			if( $cstm_recipient_details_args['type'] == 'email' ) {

				echo '<input type="text" class="woo-vou-recipient-details" '. $maxlength . ' value="'.$recipient_col_val.'" id="'.$recipient_column.'-'.$variation_id.'" name="'.$prefix.$recipient_column.'['.$variation_id.']'.'">';
			} else if( $cstm_recipient_details_args['type'] == 'date' ) {

				$dateformat 	= apply_filters( 'woo_vou_giftdate_datepicker_format', 'mm/dd/yy' );
				$datepicker_id 	= $recipient_column.'-'.$variation_id;
				echo '<input type="text" class="woo-vou-recipient-details woo_vou_cust_date_field" placeholder="'.$dateformat.'" rel="'.$dateformat.'" value="'.$recipient_col_val.'" id="'.$datepicker_id.'" name="'.$prefix.$recipient_column.'['.$variation_id.']">';
			} else if( $cstm_recipient_details_args['type'] == 'textarea' ) {

				echo '<textarea '.$maxlength.' class="woo-vou-recipient-details" id="'.$recipient_column.'-'.$variation_id.'" name="'.$prefix.$recipient_column.'['.$variation_id.']">'.$recipient_col_val.'</textarea>';
			} else {

				echo '<input type="text" class="woo-vou-recipient-details" '. $maxlength . ' value="'.$recipient_col_val.'" id="'.$recipient_column.'-'.$variation_id.'" name="'.$prefix.$recipient_column.'['.$variation_id.']'.'">';
			}

			$recipient_html .= ob_get_clean();
			echo apply_filters( 'woo_vou_cstm_recipient_input_html', $recipient_html, $variation_id, $maxlength, $recipient_col_val, $recipient_column);
			if( !empty( $recipient_col_desc ) ) {
				echo '<small class="description">' . $recipient_col_desc . '</small>';
			}
		?>
	</td>
</tr>
<?php

// Add Field after Name on Product Page
do_action( 'woo_vou_add_field_on_product_page_after_'.$recipient_column, $variation_id );
?>