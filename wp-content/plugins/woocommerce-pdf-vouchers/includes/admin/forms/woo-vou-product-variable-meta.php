<?php
/**
 * Handles the product variable meta HTML
 *
 * The html markup for the product variable
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

global $woo_vou_voucher;

$prefix					= WOO_VOU_META_PREFIX;

$variation_id 			= isset($variation->ID) ? $variation->ID : '';

if( empty( $variation_id) ) 
	$variation_id = isset( $variation['id'] ) ? $variation['id'] : '';

$woo_vou_variable_codes = get_post_meta( $variation_id, $prefix . 'codes', true ); // Getting voucher code

$woo_vou_variable_vendor_address = get_post_meta( $variation_id, $prefix . 'vendor_address', true ); // Getting vendor Address


$variable_voucher_expiration_date_type = get_post_meta( $variation_id, $prefix . 'variable_voucher_expiration_date_type', true ); // Getting variable expiration type

$variable_voucher_expiration_start_date = get_post_meta( $variation_id, $prefix . 'variable_voucher_expiration_start_date', true ); // Getting variable expiration start date

$variable_voucher_expiration_end_date = get_post_meta( $variation_id, $prefix . 'variable_voucher_expiration_end_date', true ); // Getting variable expiration end date

$variable_voucher_day_diff = get_post_meta( $variation_id, $prefix . 'variable_voucher_day_diff', true ); // Getting variable expiration day diff

$variable_voucher_expiration_custom_day = get_post_meta( $variation_id, $prefix . 'variable_voucher_expiration_custom_day', true ); // Getting variable expiration day diff

$voucher_data			= woo_vou_get_vouchers(); // Getting All Voucher Templates
$woo_vou_pdf_template	= get_post_meta( $variation_id, $prefix . 'pdf_template', true ); // Getting Selected Voucher Template

$variable_voucher_delivery	= array(
							'default'	=> esc_html__( 'Default', 'woovoucher' ), 
							'email' 	=> esc_html__( 'Email', 'woovoucher' ), 
							'offline' 	=> esc_html__( 'Offline', 'woovoucher' )
						); // Set voucher delivery options
$woo_vou_voucher_delivery	= get_post_meta( $variation_id, $prefix . 'voucher_delivery', true ); // Getting Selected Voucher Delivery
?>

<div class="show_if_variation_downloadable woo-vou-variation-voucher-options">
	<p class="form-field variable_pdf_template_field form-row form-row-first">
		<label for="woo-vou-pdf-variable-pdf-template-<?php echo $loop; ?>" class="woo-vou-pdf-template-variation-label"><?php esc_html_e('PDF Template', 'woovoucher'); ?></label>
		<span data-tip="<?php esc_html_e( 'Select a PDF template.', 'woovoucher' ); ?>" class="woocommerce-help-tip"></span>
		<select data-width="100%" class="chosen_select" name="<?php echo $prefix; ?>variable_pdf_template[<?php echo $loop; ?>]" id="woo-vou-pdf-variable-pdf-template-<?php echo $loop; ?>">
			<option value=""><?php esc_html_e('Please Select', 'woovoucher'); ?></option>
				<?php foreach ( $voucher_data as $voucher ) { ?>
					<option value="<?php echo $voucher['ID']; ?>" <?php if( $woo_vou_pdf_template == $voucher['ID'] ) echo "selected=selected"; ?>><?php echo $voucher['post_title']; ?></option>
				<?php } ?>
		</select>
	</p>

	<p class="form-field variable_voucher_delivery_field form-row form-row-last">
		<label for="woo-vou-pdf-variable-voucher-delivery-<?php echo $loop; ?>" class="woo-vou-voucher-delivery-variation-label"><?php esc_html_e('Voucher Delivery', 'woovoucher'); ?></label>
		<span data-tip="<?php esc_html_e( 'Choose how your customer receives the PDF Voucher.', 'woovoucher' ); ?>" class="woocommerce-help-tip"></span>
		<select data-width="100%" class="chosen_select" name="<?php echo $prefix; ?>variable_voucher_delivery[<?php echo $loop; ?>]" id="woo-vou-pdf-variable-voucher-delivery-<?php echo $loop; ?>">
			<?php foreach ( $variable_voucher_delivery as $voucher_delivery_key => $voucher_delivery_value ) { ?>
				<option value="<?php echo $voucher_delivery_key; ?>" <?php if( $woo_vou_voucher_delivery == $voucher_delivery_key ) echo "selected=selected"; ?>><?php echo $voucher_delivery_value; ?></option>
			<?php } ?>
		</select>
	</p>
	
	<p class="form-field variable_codes_field form-row form-row-full">
		<label for="woo-vou-variable-codes-<?php echo $loop; ?>"><?php esc_html_e('Voucher Codes', 'woovoucher'); ?></label>
		<span data-tip="<?php esc_html_e( 'If you have a list of Voucher Codes you can copy and paste them in to this option. Make sure, that they are comma separated.', 'woovoucher' ); ?>" class="woocommerce-help-tip"></span>
		<textarea rows="2" placeholder="" id="woo-vou-variable-codes-<?php echo $loop; ?>" name="<?php echo $prefix; ?>variable_codes[<?php echo $loop; ?>]" class="short"><?php echo $woo_vou_variable_codes; ?></textarea>
	</p>
        
    <p class="form-field variable_vendor_address_field form-row form-row-full">
		<label for="woo-vou-variable-vendor-address-<?php echo $loop; ?>"><?php esc_html_e('Vendor Address', 'woovoucher'); ?></label>
		<span data-tip="<?php esc_html_e( 'Here you can enter the complete Vendor\'s address. This will be displayed on the PDF voucher sent to the customers so that they know where to redeem this Voucher. Limited HTML is allowed.', 'woovoucher' ); ?>" class="woocommerce-help-tip"></span>
		<textarea rows="2" placeholder="" id="woo-vou-variable-vendor-address-<?php echo $loop; ?>" name="<?php echo $prefix; ?>variable_vendor_address[<?php echo $loop; ?>]" class="short"><?php echo $woo_vou_variable_vendor_address; ?></textarea>
	</p>
	
	<?php 
	//voucher expiration date type			
	$expdate_types = apply_filters('woo_vou_exp_date_types', array( 'default' => __( 'Default', 'woovoucher' ), 'specific_date' => esc_html__( 'Specific Time', 'woovoucher' ), 'based_on_purchase' => esc_html__( 'Based on Purchase', 'woovoucher' ), 'based_on_gift_date' => esc_html__( 'Based on Recipient Gift Date', 'woovoucher' ) ));
	
	$based_on_purchase_opt  = array(
            '7' 		=> '7 Days',
            '15' 		=> '15 Days',
            '30' 		=> '1 Month (30 Days)',
            '90' 		=> '3 Months (90 Days)',
            '180' 		=> '6 Months (180 Days)',
            '365' 		=> '1 Year (365 Days)',
            'cust'		=> 'Custom',
        );
	
	?>
	<p class="form-field variable_voucher_expiration_date_type_field form-row form-row-full">
		<label for="woo-vou-variable-expiration-date-type-<?php echo $loop; ?>"><?php esc_html_e('Expiration Date Type:', 'woovoucher'); ?></label>
		<span data-tip="<?php esc_html_e( 'Please select expiration date type either a Specific Time, Based on Purchased voucher date or Based on Recipient Gift Date like after 7 days, 30 days, 1 year etc. 
This setting modifies the global voucher expiration date setting and overrides voucher\'s expiration date value. Set expiration date type "Default" to use the global/voucher settings. ', 'woovoucher' ); ?>" class="woocommerce-help-tip"></span>
		<select data-width="100%" class="variable_voucher_expiration_date_type_field_select" data-variable-id="variable_<?php echo $variation_id; ?>"  name="<?php echo $prefix; ?>variable_voucher_expiration_date_type[<?php echo $loop; ?>]" id="woo-vou-pdf-variable-expiration-date-type-<?php echo $loop; ?>">			
				<?php foreach ( $expdate_types as $expdate_type_key=>$expdate_type_value ) { ?>
					<option value="<?php echo $expdate_type_key; ?>" <?php if( $expdate_type_key == $variable_voucher_expiration_date_type ) echo "selected=selected"; ?>><?php echo $expdate_type_value; ?></option>
				<?php } ?>
		</select>
	</p>
	
	
	<!-- Option for specific time-->
	<div class="specific-time-settings variable_<?php echo $variation_id; ?>" >
		<p class="form-field variable_voucher_expiration_date_type_field_start_date form-row form-row-full">
			<label for="woo-vou-variable-expiration-date-type-field-start-date-<?php echo $loop; ?>"><?php esc_html_e('Voucher Start Date', 'woovoucher'); ?></label>
			<span data-tip="<?php esc_html_e( 'If you want to make the voucher codes valid for a specific time only, you can enter a start date here.', 'woovoucher' ); ?>" class="woocommerce-help-tip"></span>
			<input type="text" data-var_id="<?php echo $variation_id ?>" id="woo-vou-variable-expiration-date-type-field-start-date-<?php echo $variation_id ?>"  name="<?php echo $prefix; ?>variable_voucher_expiration_start_date[<?php echo $loop; ?>]"  placeholder = "YYYY-MM-DD H:I" class="woo-vou-meta-datetime-start-time" rel="yy-mm-dd" value="<?php echo $variable_voucher_expiration_start_date ?>" />
		</p>
		<p class="form-field variable_voucher_expiration_date_type_field_end_date form-row form-row-full">
			<label for="woo-vou-variable-expiration-date-type-field-end-date-<?php echo $loop; ?>"><?php esc_html_e('Voucher Expiration Date', 'woovoucher'); ?></label>
			<span data-tip="<?php esc_html_e( 'If you want to make the voucher codes valid for a specific time only, you can enter a expiration date here. If the Voucher Code never expires, then leave that option blank.', 'woovoucher' ); ?>" class="woocommerce-help-tip"></span>
			<input type="datetime" data-var_id="<?php echo $variation_id ?>" id="woo-vou-variable-expiration-date-type-field-end-date-<?php echo $variation_id ?>"  name="<?php echo $prefix; ?>variable_voucher_expiration_end_date[<?php echo $loop; ?>]" rel="yy-mm-dd" placeholder = "YYYY-MM-DD H:I"  class="woo-vou-meta-datetime-exp-time" value="<?php echo $variable_voucher_expiration_end_date ?>" />
		</p>
	</div>
	<!-- End Specific time Option-->
	
	<!-- option for based on purchanse -->
		<div class="based-on-purchase-settings variable_<?php echo $variation_id; ?>">
			<p class="form-field variable_voucher_expiration_date_type_field_day_diff form-row form-row-full">
				<label for="woo-vou-variable-expiration-date-type-field-day-diff-<?php echo $loop; ?>"><?php esc_html_e('Expiration Days:', 'woovoucher'); ?></label>
				<select data-width="100%"  data-variable-id="variable_<?php echo $variation_id; ?>" class="variable_voucher_expiration_date_type_field_day_diff_select" name="<?php echo $prefix; ?>variable_voucher_day_diff[<?php echo $loop; ?>]" id="woo-vou-variable-expiration-date-type-field-day-diff-<?php echo $loop; ?>"]>
				<span data-tip="<?php esc_html_e( 'After Purchase or Recipient Gift Date.', 'woovoucher' ); ?>" class="woocommerce-help-tip"></span>
				
						<?php foreach ( $based_on_purchase_opt as $purchase_opt_key=>$purchase_opt_val ) { ?>
							<option value="<?php echo $purchase_opt_key; ?>" <?php if( $purchase_opt_key == $variable_voucher_day_diff ) echo "selected=selected"; ?>><?php echo $purchase_opt_val; ?></option>
						<?php } ?>
				</select>
			</p>
			
			<p class="form-field variable_voucher_expiration_date_type_field_custom_days form-row form-row-full variable_<?php echo $variation_id; ?>">
				<label for="woo-vou-variable-expiration-date-type-field-custom-day-<?php echo $loop; ?>"><?php esc_html_e('Number Of Days', 'woovoucher'); ?></label>
				
				<input type="text" class="custom-day" id="woo-vou-variable-expiration-date-type-field-custom-day-<?php echo $loop; ?>"  name="<?php echo $prefix; ?>variable_voucher_expiration_custom_day[<?php echo $loop; ?>]"  value="<?php echo $variable_voucher_expiration_custom_day; ?>" />
			</p>
		</div>
		
	<!--END option for based on purchanse -->
	
</div>