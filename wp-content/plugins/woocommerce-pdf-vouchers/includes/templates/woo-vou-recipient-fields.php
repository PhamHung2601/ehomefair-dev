<?php

/**
 * Recipient Fields Template
 * 
 * Handles to load Recipient Fields template
 * 
 * Override this template by copying it to yourtheme/woocommerce/woocommerce-pdf-vouchers/woo-vou-recipient-fields.php
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.5.3
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product;

//Get Prefix
$prefix		= WOO_VOU_META_PREFIX;
?>

<div class="vou-clearfix woo-vou-fields-wrapper<?php echo $product->is_type( 'variation' ) ? '-variation' : ''; ?>" id="woo-vou-fields-wrapper-<?php echo $variation_id; ?>">
	<table cellspacing="0" class="woo-vou-recipient-fields">
	  <tbody><?php 
		
	  	if( $enable_pdf_template_selection == 'yes' ) {
			
	  		$preview_images    				= array();
	  		$pdf_template_selection_label	= !empty( $pdf_template_selection_label ) ? $pdf_template_selection_label : esc_html__( 'Voucher Template' , 'woovoucher' ); 
	  		$pdf_template_selection_label  .= '<span class="woo-vou-gift-field-required"> *</span>'; ?>
			<tr>
				<td class="label" colspan="2">
					<label for="pdf_template_selection-<?php echo $variation_id; ?>"><?php echo $pdf_template_selection_label; ?></label>
				</td>
			</tr>
			<tr>
				<td class="value" colspan="2">
					<div class="woo-vou-preview-template-img-wrap">
						<?php
						if( !empty( $product_templates ) ){
							foreach( $product_templates as $key => $value ){
								
								$image = '';
								
								$image = wp_get_attachment_url( get_post_thumbnail_id( $value ) );
								
								if( empty( $image ) ){
									$image = WOO_VOU_IMG_URL.'/no-preview.png';
								}
						?>
								<div class="woo-vou-image-wrap">

									<img src="<?php echo esc_url($image); ?>" class="woo-vou-preview-template-img" data-id="<?php echo $value; ?>" title="<?php echo get_the_title( $value ); ?>" >
									
									<span class="woo-vou-view-preview-template-img" data-index="<?php echo $key; ?>"><img src="<?php echo esc_url(WOO_VOU_IMG_URL) .'/view_image.png';?>" data-src="<?php echo esc_url($image); ?>"></span>
								</div>
						<?php
							}
						}
						?>
					</div>
					<input type="hidden" name="<?php echo $prefix.'pdf_template_selection['.$variation_id.']'; ?>" value="<?php echo $pdf_template_selection; ?>" class="woo-vou-preview-template-img-id">
					<?php
						if( !empty( $pdf_template_desc ) ) {
							echo '<small class="description">' . $pdf_template_desc . '</small>';
						}
					?>
				</td>
			</tr><?php
	  	}

	  	// Add Field after Template on Product Page
	  	do_action( 'woo_vou_add_field_on_product_page_after_template', $variation_id );

	  	if( !empty( $individual_recipient_details ) ){

		  	// Loop on all recipient fields and check which is enabled for delivery method
        	foreach ( $recipient_detail_order as $recipient_detail ) {

        		if( $recipient_detail == $prefix . 'enable_recipient_name' && array_key_exists( 'recipient_name', $individual_recipient_details ) 
        			&& $enable_recipient_name == 'yes' ) {

        			do_action( 'woo_vou_recipient_name_html', $recipient_name );
        		} else if( $recipient_detail == $prefix . 'enable_recipient_email' && array_key_exists( 'recipient_email', $individual_recipient_details ) 
        			&& $enable_recipient_email == 'yes' ) {

			  		do_action( 'woo_vou_recipient_email_html', $recipient_email );
			  	} else if( $recipient_detail == $prefix . 'enable_recipient_message' && array_key_exists( 'recipient_message', $individual_recipient_details ) 
        			&& $enable_recipient_message == 'yes' ) {
	
			  		do_action( 'woo_vou_recipient_message_html', $recipient_message );
			  	} else if( $recipient_detail == $prefix . 'enable_recipient_giftdate' && array_key_exists( 'recipient_giftdate', $individual_recipient_details ) 
        			&& $enable_recipient_giftdate == 'yes' ) {
	
			  		do_action( 'woo_vou_recipient_giftdate_html', $recipient_giftdate );
			  	} else {

			  		$_recipient_detail = str_replace( $prefix.'enable_', '', $recipient_detail );
			  		if( array_key_exists( $_recipient_detail, $individual_recipient_details ) && ${'enable_'.$_recipient_detail} == 'yes'
			  			&& array_key_exists( $_recipient_detail, $recipient_columns ) ) {

			  			ob_start();
			  			do_action( 'woo_vou_cstm_recipient_html', $$_recipient_detail, $_recipient_detail );
			  			$recipient_cstm_html = ob_get_clean();
			  			echo apply_filters( 'woo_vou_cstm_recipient_detail_html', $recipient_cstm_html, $_recipient_detail, $recipient_giftdate );
			  		}
			  	}
        	}
	  	}

	  	if ( !empty( $recipient_delivery_method ) && !empty( $default_delivery_method ) 
	  		&& !empty( $enable_recipient_delivery_method ) && $enable_recipient_delivery_method == 'yes'
	  		&& ( $enable_recipient_email == 'yes' || $enable_recipient_name == 'yes' || $enable_recipient_message == 'yes' || $enable_recipient_giftdate == 'yes' ) ) {

	  		$delivery_method_counter = 1;
	  		echo '<tr><td colspan="2"><strong>' . $recipient_delivery_label . '</strong></td></tr>';
        	foreach( $default_delivery_method as $delivery_method_key => $delivery_method_val ) {

        		if( !empty( $recipient_delivery_method['enable_'.$delivery_method_key] ) && $recipient_delivery_method['enable_'.$delivery_method_key] == 'yes' 
        			&& !empty( $recipient_delivery_method[$delivery_method_key] ) ) {

        			$checked_attr = "";
        			$recipient_giftdate_args['delivery_method'] = $delivery_method_key;
        			if( !empty( $delivery_method ) && $delivery_method == $delivery_method_key ) {

        				$checked_attr = "checked='checked'";
        				$recipient_name_args['recipient_name'] 			= $recipient_name;
        				$recipient_email_args['recipient_email'] 		= $recipient_email;
        				$recipient_message_args['recipient_message'] 	= $recipient_message;
        				$recipient_giftdate_args['recipient_giftdate'] 	= $recipient_giftdate;
        			} else {

        				if( $delivery_method_counter == 1 ) {
        					$checked_attr = "checked='checked'";
        				}
        				$recipient_name_args['recipient_name'] = $recipient_email_args['recipient_email'] = '';
        				$recipient_message_args['recipient_message'] = $recipient_giftdate_args['recipient_giftdate'] = '';
        			}

        			echo '<tr><td colspan="2"><table class="woo-vou-delivery-' . $delivery_method_key . ' woo-vou-recipient-delivery-method"><tbody>';
					echo '<tr class="woo-vou-delivery-method-wrapper">';
					echo '<td class="label" colspan="2">';
					echo '<input type="radio" id="recipient_delivery_method_' . $delivery_method_key . '-' . $variation_id . '" value="' . $delivery_method_key . '" class="woo-vou-delivery-method" name="' . $prefix . 'delivery_method[' . $variation_id . ']" ' . $checked_attr . '>';
					echo '<label for="recipient_delivery_method_' . $delivery_method_key . '-' . $variation_id . '">' . $recipient_delivery_method['label_'.$delivery_method_key] . '</label>';
					echo '</tr>';

					// Loop on all recipient fields and check which is enabled for delivery method
        			foreach ( $recipient_detail_order as $recipient_detail ) {

        				// If recipient column is recipient name
        				if( $recipient_detail == $prefix . 'enable_recipient_name' && is_array( $recipient_delivery_method[$delivery_method_key] )
        					&& in_array( 'recipient_name', $recipient_delivery_method[$delivery_method_key] ) && $enable_recipient_name == 'yes' ) {

					  		do_action( 'woo_vou_recipient_name_html', $recipient_name );
					  	} 
					  	// Else If recipient column is recipient email
					  	else if( $recipient_detail == $prefix . 'enable_recipient_email' && is_array( $recipient_delivery_method[$delivery_method_key] )
					  		&& in_array( 'recipient_email', $recipient_delivery_method[$delivery_method_key] ) && $enable_recipient_email == 'yes' ) {

					  		do_action( 'woo_vou_recipient_email_html', $recipient_email );
					  	} 
					  	// Else If recipient column is recipient message
					  	else if( $recipient_detail == $prefix . 'enable_recipient_message' && is_array( $recipient_delivery_method[$delivery_method_key] )
					  		&& in_array( 'recipient_message', $recipient_delivery_method[$delivery_method_key] ) && $enable_recipient_message == 'yes' ) {

					  		do_action( 'woo_vou_recipient_message_html', $recipient_message );
					  	} 
					  	// Else If recipient column is recipient giftdate
					  	else if( $recipient_detail == $prefix . 'enable_recipient_giftdate' && is_array( $recipient_delivery_method[$delivery_method_key] )
					  		&& in_array( 'recipient_giftdate', $recipient_delivery_method[$delivery_method_key] ) && $enable_recipient_giftdate == 'yes' ) {

					  		$recipient_giftdate['delivery_method_key'] = $delivery_method_key;
					  		do_action( 'woo_vou_recipient_giftdate_html', $recipient_giftdate );
					  	} else { // Else, If it is custom column

					  		$_recipient_detail = str_replace( $prefix.'enable_', '', $recipient_detail );
					  		if( is_array( $recipient_delivery_method[$delivery_method_key] )
					  			&& in_array( $_recipient_detail, $recipient_delivery_method[$delivery_method_key] ) 
					  			&& array_key_exists( $_recipient_detail, $recipient_columns )
					  			&& ${'enable_'.$_recipient_detail} == 'yes' ) {

					  			ob_start();
					  			do_action( 'woo_vou_cstm_recipient_html', $$_recipient_detail, $_recipient_detail );
					  			$recipient_cstm_html = ob_get_clean();
					  			echo apply_filters( 'woo_vou_cstm_recipient_detail_html', $recipient_cstm_html, $_recipient_detail, $recipient_giftdate );
					  		}
					  	}

					  	// Increase the counter on each loop
					  	$delivery_method_counter++;
        			}

        			if( !empty( $recipient_delivery_method['desc_'.$delivery_method_key] ) ) {
        				echo '<tr><td colspan="2">' . $recipient_delivery_method['desc_'.$delivery_method_key] . '</td></tr>';
        			}

        			echo '</tbody></table></td></tr>';
        		}
        	}	
	  	}
	  	
	  	// Add Field after Template on Product Page
	  	do_action( 'woo_vou_add_field_on_product_page_after_recipient_details', $variation_id );
	  	?>
	  </tbody>
	</table>
</div>