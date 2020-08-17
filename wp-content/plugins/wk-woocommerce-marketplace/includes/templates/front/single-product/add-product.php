<?php

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$wpmp_obj4 = new MP_Form_Handler();

$wpmp_obj4->marketplace_media_fix();

?>
<div class="woocommerce-account">
<?php
do_action('mp_get_wc_account_menu', 'marketplace');
?>

<div class="form woocommerce-MyAccount-content add-product-form">

	<div class="wkmp_container">
	<?php

if (isset($_POST['product_cate']) && isset($_POST['product_type']) && isset($_POST['add_product_cat_type'])):
?>
		<form action="<?php echo esc_url(get_permalink() . 'product/edit'); ?>" method="post" enctype="multipart/form-data" id="product-form" class="form-add-new">

            <fieldset>
				<div class="wkmp_profile_input">
					<label for="product_name"><?php echo esc_html__('Product Name', 'marketplace'); ?><span class="required">*</span></label>
					<input class="wkmp_product_input" type="text" name="product_name" id="product_name" size="54" value="" />
					<div id="pro_name_error" class="error-class"></div>
				</div>

				<div class="wkmp_profile_input">
					<label for="product_desc"><?php echo esc_html__('About Product', 'marketplace'); ?></label>

					<?php
$settings = array(
    'media_buttons' => true, // show insert/upload button(s).
    'textarea_name' => 'product_desc',
    'textarea_rows' => get_option('default_post_edit_rows', 10),
    'tabindex' => '',
    'teeny' => false,
    'dfw' => false,
    'tinymce' => true, /* load TinyMCE, can be used to pass settings directly to TinyMCE using an array()*/
    'quicktags' => false, /* load Quicktags, can be used to pass settings directly to Quicktags using an array()*/
);

if (isset($post_row_data[0]->post_content)) {
    $content = $post_row_data[0]->post_content;
}

if (isset($content)) {
    echo wp_editor($content, 'product_desc', $settings);
} else {
    echo wp_editor('', 'product_desc', $settings);
}

$reg_val = '';
$sel_val = '';

if ($_POST['product_type'] == 'variable' || $_POST['product_type'] == 'grouped') {
    $reg_val = 'disabled';
    $sel_val = 'disabled';
}

?>
					<div id="long_desc_error" class="error-class"></div>
				</div>

				<div class="wkmp_profile_input">
					<?php
if (array_key_exists('1', $_POST['product_cate'])) {
    $product_cat = implode(',', $_POST['product_cate']);
} else {
    $product_cat = $_POST['product_cate'][0];
}
?>
					<input type = "hidden" name = "product_cate" value = "<?php echo $product_cat; ?>" >
					<input type = "hidden" name = "product_type" value = "<?php echo $_POST['product_type']; ?>" >
				</div>

				<div class="wkmp_profile_input">
					<label for="fileUpload"><?php echo esc_html__('Product Thumbnail', 'marketplace'); ?></label>

					<div id="product_image"></div>
					<input type="hidden"  id="product_thumb_image_mp" name="product_thumb_image_mp" />
					<p><a class="upload mp_product_thumb_image button" data-type-error="<?php echo esc_html__('Only jpg|png|jpeg files are allowed.', 'marketplace'); ?>" href="javascript:void(0);" /><?php esc_html_e('Upload Thumb', 'marketplace');?></a></p>
				</div>

				<div class="wkmp_profile_input">
					<label for="product_sku"><?php echo esc_html_e('Product SKU', 'marketplace'); ?>
						<span class="required">*</span> &nbsp;
						<span class="help">
							<div class="wkmp-help-tip-sol"><?php echo esc_html_e('SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.', 'marketplace'); ?>
							</div>
							<span class="help-tip"></span>
						</span>
					</label>

					<input class="wkmp_product_input" type="text" name="product_sku" id="product_sku" value="" />
					<div id="pro_sku_error" class="error-class"></div>

				</div>

				<div class="wkmp_profile_input">
					<label for="regu_price"><?php echo esc_html_e('Regular Price', 'marketplace'); ?><span class="required">*</span></label>
					<input class="wkmp_product_input" type="text" name="regu_price" id="regu_price" value="" <?php if (!empty($reg_val)) {
    echo 'disabled = ' . $reg_val;
}
?> />
					<div id="regl_pr_error" class="error-class"></div>
				</div>

				<div class="wkmp_profile_input">
					<label for="sale_price"><?php echo esc_html_e('Sale Price', 'marketplace'); ?></label>

					<input class="wkmp_product_input" type="text" name="sale_price" id="sale_price" value="" <?php if (!empty($sel_val)) {
    echo 'disabled = ' . $sel_val;
}
?> />
					<div id="sale_pr_error" class="error-class"></div>
				</div>

                <div id="general_product_data" class="panel woocommerce_options_panel">
                    <?= get_product_settings(); ?>
                </div>

				<div class="wkmp_profile_input">
					<label for="short_desc"><?php echo esc_html_e('Product Short Description ', 'marketplace'); ?></label>
					<?php

$settings = array(
    'media_buttons' => false, // show insert/upload button(s).
    'textarea_name' => 'short_desc',
    'textarea_rows' => get_option('default_post_edit_rows', 10),
    'tabindex' => '',
    'editor_class' => 'frontend',
    'teeny' => false,
    'dfw' => false,
    'tinymce' => true,
    'quicktags' => false,
    'drag_drop_upload' => true,
);

if (isset($post_row_data[0]->post_excerpt)) {
    $short_content = $post_row_data[0]->post_excerpt;
}

if (isset($short_content)) {
    echo wp_editor($short_content, 'short_desc', $settings);
} else {
    echo wp_editor('', 'short_desc', $settings);
}

?>

					<div id="short_desc_error" class="error-class"></div>
				</div>

				<div class="wkmp_profile_input">
					<input type="submit" name="add_product_sub" id="add_product_sub" value='<?php echo esc_html__('Save', 'marketplace'); ?>' class="button"/></td>
				</div>

				<?php apply_filters('mp_user_redirect', 'redirect user');?>

			</fieldset>

		</form>

	<?php
else:

    if (isset($_POST['add_product_cat_type'])) {

        wc_print_notice(' Sorry, Firstly select product category(s) and type. ', 'error');

    }

    ?>
		<form action = "<?php echo esc_url(get_permalink() . get_option( 'mp_add_product', 'add-product' )); ?>" method="post" >
			<table style="width:100%">
				<tbody>
					<tr>
						<td>
							<label for="mp_seller_product_categories"><?php echo esc_html__('Product categories', 'marketplace'); ?></label>
						</td>
						<td>
							<?php
    $allowed_cat = get_user_meta(get_current_user_id(), 'wkmp_seller_allowed_categories', true);

    if (!$allowed_cat) {
        $allowed_categories = get_option('wkmp_seller_allowed_categories');
    } else {
        $allowed_categories = $allowed_cat;
    }

    require 'class-taxonomy-filter.php';

    $product_categories = wp_dropdown_categories(array(
        'show_option_none' => '',
        'hierarchical' => 1,
        'hide_empty' => 0,
        'name' => 'product_cate[]',
        'id' => 'mp_seller_product_categories',
        'taxonomy' => 'product_cat',
        'title_li' => '',
        'orderby' => 'name',
        'order' => 'ASC',
        'class' => '',
        'exclude' => '',
        'selected' => array(),
        'echo' => 0,
        'value_field' => 'slug',
        'walker' => new MpProductCategoryTree($allowed_categories),
    ));

    echo str_replace('<select', '<select  style="width:100%" data-placeholder="' . __('Choose category(s)', 'marketplace') . '" multiple="multiple" ', $product_categories);

    ?>

							</td>

						</tr>

						<tr>

							<td>

								<label for="product_type"><?php echo esc_html('Product Type', 'marketplace'); ?></label>

							</td>

							<td>

								<select name="product_type" id="product_type" class="mp-toggle-select" >

									<?php

    $mp_product_type = wc_get_product_types();

    $allowed_product_types = get_option('wkmp_seller_allowed_product_types');

    foreach ($mp_product_type as $key => $pro_type):
        if ($allowed_product_types):
            if (in_array($key, $allowed_product_types)):
            ?>
														<option value="<?php echo $key; ?>"><?php echo $pro_type; ?></option>
														<?php
        endif;
    else:
    ?>
												<option value="<?php echo $key; ?>"><?php echo $pro_type; ?></option>
							<?php endif;?>
					<?php endforeach;?>

							</select>

							</td>

						</tr>

						<tr>
							<td></td>
							<td>
								<input type="submit" name="add_product_cat_type" id="add_product_cat_type" value='<?php echo esc_html__('Next', 'marketplace'); ?>' class="button"/></td>
							</tr>

					</tbody>

			</table>

		</form>

	<?php endif;?>

	</div>

</div>

</div>
