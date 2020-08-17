<?php

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$y = $var_id;

$thumb_id = get_post_meta($var_id, '_thumbnail_id', true);

$variable_product_images = get_product_image_mp($var_id, '_thumbnail_id');

if (!empty($variable_product_images)) {
    $variable_image = content_url() . '/uploads/' . $variable_product_images;
} else {
    $variable_image = WK_MARKETPLACE . 'assets/images/placeholder.png';
}

$product_ping_status = array(
    'ID' => $wk_pro_id,
    'ping_status' => 'closed',
);

wp_update_post($product_ping_status);

$postmeta_row_data = get_post_meta($wk_pro_id);

foreach ($postmeta_row_data as $key => $value) {
    $meta_arr[$key] = $value[0];
}

$product_attributes = get_post_meta($wk_pro_id, '_product_attributes', true);

$postmeta_variation = get_post_meta($var_id);

foreach ($postmeta_variation as $key => $value) {
    $variation_arr[$key] = $value[0];
}

$wc_currency = get_woocommerce_currency_symbol(get_option('woocommerce_currency'));

?>

<div class="wkmp_marketplace_variation">
	<h3>
		<button type="button" id="<?php echo $var_id; ?>" class="mp_attribute_remove btn btn-danger wkmp_var_btn" rel="<?php echo $var_id; ?>"><?php echo esc_html__('Remove', 'marketplace'); ?></button>
		<input type='hidden' value="<?php echo $var_id; ?>"  name="mp_attribute_variation_name[]" id="mp_attribute_variation_id" />

		<?php
foreach ($product_attributes as $variation) {
    ?>
		<label><?php echo ucfirst(str_replace('-', ' ', $variation['name'])) . ' '; ?></label>
			<?php
if ($variation['is_variation'] == 1) {
        $var_name = 'attribute_' . sanitize_title($variation['name']) . '';
        echo '<input type="hidden" value="' . $variation['name'] . '"  name="mp_attribute_name[' . $y . '][]" />';
        echo '<select name="attribute_' . $variation['name'] . '[' . $y . ']">
					<option value="">Choose ' . str_replace('-', ' ', $variation['name']) . '…</option>';
        $att_val = explode('|', $variation['value']);
        foreach ($att_val as $value) {
            ?>
					<option value="<?php echo $value; ?>" <?php if (isset($variation_arr[$var_name]) && trim($value) == trim($variation_arr[$var_name])) {
                echo 'selected';
            }?> ><?php echo $value; ?></option>
					<?php
}
        echo '</select>';
    }
}
?>
	<input class="variation_menu_order" name="wkmp_variation_menu_order[<?php echo $y; ?>]" value="0" type="hidden">
	</h3>
	<table style="display: table;" cellpadding="0" cellspacing="0">
		<tbody>
			<tr>
				<td class="sku" colspan="2">
					<label><?php echo esc_html__('SKU', 'marketplace') . ':'; ?></label>
					<input size="5" id="<?php echo $y; ?>" class="wkmp_variable_sku wkmp_product_input" name="wkmp_variable_sku[<?php echo $y; ?>]" value="<?php if (isset($variation_arr['_sku'])) {
    echo $variation_arr['_sku'];
}?>" placeholder="<?php if (isset($variation_arr['_sku'])) {
    echo $variation_arr['_sku'];
}?>" type="text">
					<div class="wk_variable_sku_err error-class"></div>
				</td>

					<td class="variation_data" rowspan="2">
						<table class="data_table" cellpadding="0" cellspacing="0" style="display:table;">
							<tbody>
								<tr class="variable_pricing">
									<td style="width: 50%;">
										<label><?php echo esc_html__('Regular Price', 'marketplace') . ':(' . $wc_currency . ')'; ?></label>
										<input size="5" name="wkmp_variable_regular_price[<?php echo $y; ?>]" value="<?php if (isset($variation_arr['_regular_price'])) {
    echo $variation_arr['_regular_price'];
}?>" class="wc_input_price wkmp_variable_regular_price wkmp_product_input" placeholder="<?php echo esc_html__('Variation price (required)', 'marketplace'); ?>" type="text">
									</td>
									<td>
										<label><?php echo esc_html__('Sale Price', 'marketplace') . ':(' . $wc_currency . ')'; ?><a href="javascript:void(0);" class="mp_sale_schedule"><?php echo esc_html__('Schedule', 'marketplace'); ?></a><a href="javascript:void(0);" class="mp_cancel_sale_schedule" style="display:none"><?php echo esc_html__('Cancel schedule', 'marketplace'); ?></a></label>
										<input size="5" name="wkmp_variable_sale_price[<?php echo $y; ?>]" value="<?php if (isset($variation_arr['_sale_price'])) {
    echo $variation_arr['_sale_price'];
}?>" class="wc_input_price wkmp_variable_sale_price wkmp_product_input" type="text">
										<span class="sale_pr_error error-class"></span>
									</td>
								</tr>
								<tr class="mp_sale_price_dates_fields" style="display:none">
									<td>
										<label><?php echo esc_html__('Sale start date', 'marketplace') . ':'; ?></label>
										<input id="dp1412074277629" class="sale_price_dates_from hasDatepicker wkmp_product_input" name="wkmp_variable_sale_price_dates_from[<?php echo $y; ?>]" value="<?php if (isset($variation_arr['_sale_price_dates_from'])) {
    echo $variation_arr['_sale_price_dates_from'];
}?>" placeholder="<?php echo esc_html__('From… YYYY-MM-DD', 'marketplace'); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" type="text"><img title="..." alt="..." src="<?php echo get_home_url() . '/wp-content/plugins/woocommerce/assets/images/calendar.png'; ?>" class="ui-datepicker-trigger">
									</td>
									<td>
										<label><?php echo esc_html__('Sale end date', 'marketplace') . ':'; ?></label>
										<input class="hasDatepicker wkmp_product_input" id="dp1412074277630" name="wkmp_variable_sale_price_dates_to[<?php echo $y; ?>]" value="<?php if (isset($variation_arr['_sale_price_dates_to'])) {
    echo $variation_arr['_sale_price_dates_to'];
}?>" placeholder="<?php echo esc_html__('To… YYYY-MM-DD', 'marketplace'); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" type="text"><img title="..." alt="..." src="<?php echo get_home_url() . '/wp-content/plugins/woocommerce/assets/images/calendar.png'; ?>" class="ui-datepicker-trigger">
									</td>
								</tr>
								<tr class="mpshow_if_variation_manage_stock wkmp_stock_status" style=" display:<?php if (isset($variation_arr['_manage_stock']) && $variation_arr['_manage_stock'] == 'yes') {
    echo 'table-row';
} else {
    echo 'none';
}?>;">
										<td>
											<label><?php echo esc_html__('Stock Qty', 'marketplace') . ':'; ?></label>
											<input class="wkmp_variable_stock wkmp_product_input" size="5" name="wkmp_variable_stock[<?php echo $y; ?>]" value="<?php if (isset($variation_arr['_stock'])) {
    echo $variation_arr['_stock'];
}?>" step="any" type="number">
										</td>
										<td>
											<label><?php echo esc_html__('Allow Backorders?', 'marketplace'); ?></label>
											<select name="wkmp_variable_backorders[<?php echo $y; ?>]" style="width:100%;">
												<option value="no" <?php if (isset($variation_arr['_backorders']) && $variation_arr['_backorders'] == 'no') {
    echo 'selected';
}?>><?php echo esc_html__('Do not allow', 'marketplace'); ?></option>
													<option value="notify" <?php if (isset($variation_arr['_backorders']) && $variation_arr['_backorders'] == 'notify') {
    echo 'selected';
}?>><?php echo esc_html__('Allow, but notify customer', 'marketplace'); ?></option>
													<option value="yes" <?php if (isset($variation_arr['_backorders']) && $variation_arr['_backorders'] == 'yes') {
    echo 'selected';
}?>><?php echo esc_html__('Allow', 'marketplace'); ?></option>
												</select>
										</td>
								</tr>
								<tr class="wkmp_stock_status" style="	display:<?php if (isset($variation_arr['_manage_stock']) && $variation_arr['_manage_stock'] == 'yes') {
    echo 'table-row';
} else {
    echo 'none';
}?>;">
									<td colspan="2">
										<label><?php echo esc_html__('Stock status', 'marketplace'); ?></label>
										<select name="wkmp_variable_stock_status[<?php echo $y; ?>]" style="width:100%;">
											<option value="instock" <?php if (isset($variation_arr['_stock_status']) && $variation_arr['_stock_status'] == 'instock') {
    echo 'selected';
}?>><?php echo esc_html__('In stock', 'marketplace'); ?></option>
											<option value="outofstock" <?php if (isset($variation_arr['_stock_status']) && $variation_arr['_stock_status'] == 'outofstock') {
    echo 'selected';
}?>><?php echo esc_html__('Out of stock', 'marketplace'); ?></option>
										</select>
									</td>
								</tr>
								<tr class="virtual" style="display:<?php if (isset($variation_arr['_virtual']) && $variation_arr['_virtual'] == 'yes') {
    echo 'none';
} else {
    echo 'table-row';
}?>;">
									<td style="display: table-cell;" class="mp_hide_if_variation_virtual">
										<label><?php echo esc_html__('Weight (kg):', 'marketplace'); ?></label>
										<input size="5" name="wkmp_variable_weight[<?php echo $y; ?>]" value="<?php if (isset($variation_arr['_weight'])) {
    echo $variation_arr['_weight'];
}?>" placeholder="0" class="wc_input_decimal wkmp_product_input" type="text">
									</td>
									<td style="display: table-cell;" class="dimensions_field mp_hide_if_variation_virtual">
										<label for="product_length"><?php echo esc_html__('Dimensions (L×W×H) (cm):', 'marketplace'); ?></label>
										<input id="product_length" class="input-text wc_input_decimal wkmp_product_input" size="6" name="wkmp_variable_length[<?php echo $y; ?>]" value="<?php if (isset($variation_arr['_length'])) {
    echo $variation_arr['_length'];
}?>" placeholder="0" type="text">
										<input class="input-text wc_input_decimal wkmp_product_input" size="6" name="wkmp_variable_width[<?php echo $y; ?>]" value="<?php if (isset($variation_arr['_width'])) {
    echo $variation_arr['_width'];
}?>" placeholder="0" type="text">
										<input class="input-text wc_input_decimal last wkmp_product_input" size="6" name="wkmp_variable_height[<?php echo $y; ?>]" value="<?php if (isset($variation_arr['_height'])) {
    echo $variation_arr['_height'];
}?>" placeholder="0" type="text">
									</td>
								</tr>

								<?php do_action('mp_edit_variable_product_field', $var_id);?>

								<tr class="downloadable mpshow_if_variation_wkmp_downloadable<?php echo $y; ?>" style="display:<?php if (isset($variation_arr['_downloadable']) && $variation_arr['_downloadable'] == 'yes') {
    echo 'table-row';
} else {
    echo 'none';
}?>;">
									<td colspan="2">
										<div class="form-field downloadable_files">
											<label><?php echo esc_html__('Downloadable Files:', 'marketplace'); ?></label>
											<div class="widefat">
												<div class="wkmp_variation_downloadable_file" id="variation_downloadable_file_<?php echo $y; ?>">
												<?php
$product_vars_downloadables = get_post_meta($var_id, '_downloadable_files', true);
if (empty($product_vars_downloadables)) {
    $product_vars_downloadables = array();
}
$i = 0;
if (empty($product_vars_downloadables[0])) {
    $product_vars_downloadables[0] = array();
}
foreach ($product_vars_downloadables as $pro_downloadable) {
    ?>
												<div class="tr_div">
													<div>
														<label for="downloadable_upload_file_name_<?php echo $y . '_' . $i; ?>"><?php echo esc_html__('NAME', 'marketplace'); ?></label>
														<input type="text" class="input_text wkmp_product_input" placeholder="<?php echo esc_html__('File Name', 'marketplace'); ?>" id="downloadable_upload_file_name_<?php echo $y . '_' . $i; ?>" name="_mp_variation_downloads_files_name[<?php echo $y; ?>][<?php echo $i; ?>]" value="<?php echo isset($pro_downloadable['name']) ? esc_attr($pro_downloadable['name']) : ''; ?>">
													</div>
													<div class="file_url">
														<label for="downloadable_upload_file_url_<?php echo $y . '_' . $i; ?>"><?php echo esc_html__('File Url', 'marketplace'); ?></label>
														<input type="text" class="input_text wkmp_product_input" placeholder="http://" id="downloadable_upload_file_url_<?php echo $y . '_' . $i; ?>" name="_mp_variation_downloads_files_url[<?php echo $y; ?>][<?php echo $i; ?>]" value="<?php echo isset($pro_downloadable['file']) ? esc_attr($pro_downloadable['file']) : ''; ?>">
														<a href="javascript:void(0);" class="button wkmp_downloadable_upload_file" id="<?php echo $y . '_' . $i; ?>"><?php echo esc_html__('Choose&nbsp;file', 'marketplace'); ?></a>
														<a href="javascript:void(0);" class="delete mp_var_del" id="mp_var_del_<?php echo $y . '_' . $i; ?>"><?php echo esc_html__('Delete', 'marketplace'); ?></a>
													</div>
													<div class="file_url_choose">
													</div>
												</div>
												<?php
++$i;
}
?>
												</div>
												<tr class="downloadable" style="display:<?php if (isset($variation_arr['_downloadable']) && $variation_arr['_downloadable'] == 'yes') {
    echo 'table-row';
} else {
    echo 'none';
}?>;">
													<th colspan="4" class="wkmp_add_file_middle">
														<a href="javascript:void(0);" class="button mp_varnew_file mp_var_down_load_call_<?php echo $y; ?>" id="<?php echo $y; ?>"><?php echo esc_html__('Add File', 'marketplace'); ?></a>
													</th>
												</tr>
											</div>
									</td>
								</tr>
								<?php if (isset($variation_arr['_downloadable']) && $variation_arr['_downloadable'] == 'yes'): ?>
									<tr class="downloadable mpshow_if_variation_wkmp_downloadable<?php echo $y; ?>" style="display:<?php if (isset($variation_arr['_downloadable']) && $variation_arr['_downloadable'] == 'yes') {
    echo 'table-row';
} else {
    echo 'none';
}?>;">
										<td>
											<div>
												<label><?php echo esc_html__('Download Limit:', 'marketplace'); ?></label>
												<input size="5" name="wkmp_variable_download_limit[<?php echo $y; ?>]" value="<?php if (isset($variation_arr['_download_limit'])) {
    echo $variation_arr['_download_limit'];
}?>" placeholder="<?php echo esc_html__('Unlimited', 'marketplace'); ?>" step="1" min="0" type="number" class="wkmp_product_input" />
											</div>
										</td>
										<td>
											<div>
												<label><?php echo esc_html__('Download Expiry:', 'marketplace'); ?></label>
												<input size="5" name="wkmp_variable_download_expiry[<?php echo $y; ?>]" value="<?php if (isset($variation_arr['_download_expiry'])) {
    echo $variation_arr['_download_expiry'];
}?>" placeholder="<?php echo esc_html__('Unlimited', 'marketplace'); ?>" step="1" min="0" type="number" class="wkmp_product_input" />
											</div>
										</td>
									</tr>
								<?php endif;?>
							</tbody>
						</table>
					</td>
			</tr>
			<tr>
				<td class="wkmp_upload_image_variation">
					<a href="javascript::void(0);" class="upload_var_image_button" id="var_img_<?php echo $y; ?>" ><img src="<?php echo $variable_image; ?>" id="wkmp_variation_product_var_img_<?php echo $y; ?>"><input name="upload_var_img[<?php echo $y; ?>]" id="upload_var_img_<?php echo $y; ?>" value="<?php echo $thumb_id; ?>" type="hidden"></a>
				</td>
				<td class="options">
					<label>
						<input class="checkbox" name="wkmp_variable_enabled[<?php echo $y; ?>]" type="checkbox" checked='checked'>
						<?php echo esc_html__('Enabled', 'marketplace'); ?>
					</label>
					<label title="check/uncheck to sell downloadable products"><input class="checkbox checkbox_is_downloadable" id="wkmp_downloadable<?php echo $y; ?>"  value="yes" name="wkmp_variable_is_downloadable[<?php echo $y; ?>]" type="checkbox"  <?php if (isset($variation_arr['_downloadable']) && $variation_arr['_downloadable'] == 'yes') {
    echo 'checked';
}?>>
						<?php echo esc_html__('Downloadable', 'marketplace'); ?>
					</label>
					<label title="check/uncheck to assign its weight, dimensions">
						<input class="checkbox checkbox_is_virtual" id="wkmp_virtual<?php echo $y; ?>" value="yes" name="wkmp_variable_is_virtual[<?php echo $y; ?>]" type="checkbox" <?php if (isset($variation_arr['_virtual']) && $variation_arr['_virtual'] == 'yes') {
    echo 'checked';
}?>>
						<?php echo esc_html__('Virtual', 'marketplace'); ?>
					</label>
					<label title="check/uncheck to manage stock">
						<input class="checkbox checkbox_manage_stock" id="wkmp_stock<?php echo $y; ?>" value="yes" name="wkmp_variable_manage_stock[<?php echo $y; ?>]" type="checkbox" <?php if (isset($variation_arr['_manage_stock']) && $variation_arr['_manage_stock'] == 'yes') {
    echo 'checked';
}?>>
						<?php echo esc_html__('Manage stock?', 'marketplace'); ?>
					</label>
					</td>
				</tr>
	</tbody>
</table>
</div>
<?php
++$y;
