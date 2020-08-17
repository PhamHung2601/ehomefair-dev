<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

global $wpdb, $post, $mp_obj;
$wpmp_obj3 = new MP_Form_Handler();
$wk_id = get_query_var('pid');
$mainpage = get_query_var('main_page');
$wc_currency = get_woocommerce_currency_symbol(get_option('woocommerce_currency'));

$user_id = get_current_user_id();
if (!empty($wk_id)) {
    $post_id = $wk_pro_id = $wk_id;
    if ($_POST) {
        if (isset($_POST['add_product_sub'])) {
            product_add_update();
        }
    }
} else {
    if (isset($_POST['add_product_sub'])) {
        $wk_pro_p = product_add_update();
        $wk_pro_id = $wk_pro_p[0];
    }
}

$wpmp_obj3->marketplace_media_fix();

$product_auth = $wpdb->get_var("select post_author from $wpdb->posts where ID='" . $wk_pro_id . "'");

// Check if product author is same as of logged in user.
if (isset($wk_pro_id) && $product_auth == $user_id) {
    $post_row_data = $wpdb->get_results("select * from $wpdb->posts where ID=" . $wk_pro_id);
    $postmeta_row_data = get_post_meta($wk_pro_id);
    $product_images = $wpmp_obj3->get_product_image($wk_pro_id, '_thumbnail_id');

    $meta_arr = array();

    foreach ($postmeta_row_data as $key => $value) {
        $meta_arr[$key] = $value[0];
    }

    $product_attributes = get_post_meta($wk_pro_id, '_product_attributes', true);
    $display_variation = 'no';
    if (!empty($product_attributes)) {
        foreach ($product_attributes as $variation) {
            if ($variation['is_variation'] == 1) {
                $display_variation = 'yes';
            }
        }
    }

    $image_gallary = $wpmp_obj3->get_product_image($wk_pro_id, '_product_image_gallery');

    function marketplace_wp_text_input($field, $wk_pro_id)
    {
        global $post;
        $thepostid = empty($wk_id) ? $post->ID : $wk_id;
        $field['placeholder'] = isset($field['placeholder']) ? $field['placeholder'] : '';
        $field['class'] = isset($field['class']) ? $field['class'] : 'short';
        $field['style'] = isset($field['style']) ? $field['style'] : '';
        $field['wrapper_class'] = isset($field['wrapper_class']) ? $field['wrapper_class'] : '';
        $field['value'] = isset($field['value']) ? $field['value'] : get_post_meta($thepostid, $field['id'], true);
        $field['name'] = isset($field['name']) ? $field['name'] : $field['id'];
        $field['type'] = isset($field['type']) ? $field['type'] : 'text';
        $data_type = empty($field['data_type']) ? '' : $field['data_type'];

        switch ($data_type) {
            case 'price':
                $field['class'] .= ' wc_input_price';
                $field['value'] = wc_format_localized_price($field['value']);
                break;
            case 'decimal':
                $field['class'] .= ' wc_input_decimal';
                $field['value'] = wc_format_localized_decimal($field['value']);
                break;
            case 'stock':
                $field['class'] .= ' wc_input_stock';
                $field['value'] = wc_stock_amount($field['value']);
                break;
            case 'url':
                $field['class'] .= ' wc_input_url';
                $field['value'] = esc_url($field['value']);
                break;

            default:
                break;
        }

        // Custom attribute handling.
        $custom_attributes = array();

        if (!empty($field['custom_attributes']) && is_array($field['custom_attributes'])) {

            foreach ($field['custom_attributes'] as $attribute => $value) {
                $custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($value) . '"';
            }
        }

        echo '<p class="form-field ' . esc_attr($field['id']) . '_field ' . esc_attr($field['wrapper_class']) . '"><label for="' . esc_attr($field['id']) . '">' . wp_kses_post($field['label']) . '</label><input type="' . esc_attr($field['type']) . '" class="' . esc_attr($field['class']) . '" style="' . esc_attr($field['style']) . '" name="' . esc_attr($field['name']) . '" id="' . esc_attr($field['id']) . '" value="' . esc_attr($field['value']) . '" placeholder="' . esc_attr($field['placeholder']) . '" ' . implode(' ', $custom_attributes) . ' /> ';

        if (!empty($field['description'])) {

            if (isset($field['desc_tip']) && false !== $field['desc_tip']) {
                echo wc_help_tip($field['description']);
            } else {
                echo '<span class="description">' . wp_kses_post($field['description']) . '</span>';
            }
        }
        echo '</p>';
    }

    $mp_product_type = wc_get_product_types();

    $product = wc_get_product($wk_pro_id);

    ?>
<div class="woocommerce-account">
<?php

    do_action('mp_get_wc_account_menu', 'marketplace');

    ?>

<div class="form woocommerce-MyAccount-content add-product-form">

	<input type="hidden" name="var_variation_display" id="var_variation_display" value="<?php echo $display_variation; ?>" />

	<ul id='edit_product_tab'>

			<li><a id='edit_tab'><?php echo esc_html_e('Edit', 'marketplace'); ?></a></li>
			<?php
if ($product->get_type() != 'grouped' && $product->get_type() != 'external') {
        $show = $ship_show = '';
    } else {
        $show = $ship_show = "style='display:none;'";
    }

    if ($meta_arr['_virtual'] == 'yes') {
        $ship_show = "style='display:none;'";
    }
    ?>
			<li <?php echo $show; ?>><a id='inventorytab'><?php echo esc_html_e('Inventory', 'marketplace'); ?></a></li>

			<li <?php echo $ship_show; ?> ><a id='shippingtab'><?php echo esc_html_e('Shipping', 'marketplace'); ?></a></li>

			<li><a id='linkedtab'><?php echo esc_html_e('Linked Products', 'marketplace'); ?></a></li>

			<li><a id='attributestab'><?php echo esc_html_e('Attributes', 'marketplace'); ?></a></li>

			<li style="display:none;"><a id='external_affiliate_tab'><?php echo esc_html_e('External/Affiliate', 'marketplace'); ?></a></li>

			<li style="display:none;"><a id='avariationtab'><?php echo esc_html_e('Variations', 'marketplace'); ?></a></li>

			<li><a id='pro_statustab'><?php echo esc_html_e('Product Status', 'marketplace'); ?></a></li>

			<?php do_action('mp_edit_product_tab_links');?>

	</ul>


	<form action="" method="post" enctype="multipart/form-data" id="product-form">

		<div class="wkmp_container form" id="edit_tabwk">

			<div class="wkmp_profile_input">

				<label for="product_type"><?php echo esc_html__('Product Type', 'marketplace') . ':'; ?></label>

				<select name="product_type" id="product_type" class="mp-toggle-select">

					<?php
$pro_term_relation = $wpdb->get_var("select wtr.term_taxonomy_id from {$wpdb->prefix}term_relationships as wtr join {$wpdb->prefix}term_taxonomy wtt on  wtr.term_taxonomy_id=wtt.term_taxonomy_id where wtt.taxonomy='product_type' and wtr.object_id=$wk_pro_id");

    $allowed_product_types = get_option('wkmp_seller_allowed_product_types');

    foreach ($mp_product_type as $key => $pro_type) {
        if ($allowed_product_types):
            if (in_array($key, $allowed_product_types)):
            ?>
										<option value="<?php echo $key; ?>" <?php if ($key == $product->get_type()) {
                echo 'selected="selected"';
            }
            ?> ><?php echo $pro_type; ?></option>
									<?php
endif;
        else:
        ?>
							<option value="<?php echo $key; ?>" <?php if ($key == $product->get_type()) {
            echo 'selected="selected"';
        }
        ?> ><?php echo $pro_type; ?></option>
								<?php endif;?>
					<?php
}
    ?>

						<?php if ($allowed_product_types && !in_array($product->get_type(), $allowed_product_types)): ?>
								<option value="<?php echo $product->get_type(); ?>" selected><?php echo $mp_product_type[$product->get_type()]; ?></option>
						<?php endif;?>

				</select>

			</div>

			<div class="wkmp_profile_input">

				<label for="product_name"><?php echo esc_html_e('Product Name', 'marketplace'); ?><span class="required">*</span>&nbsp;&nbsp;:</label>

				<input class="wkmp_product_input" type="text" name="product_name" id="product_name" size="54" value="<?php if (isset($post_row_data[0]->post_title)) {
        echo $post_row_data[0]->post_title;
    }
    ?>" />

				<div id="pro_name_error" class="error-class"></div>


			</div>

			<div class="wkmp_profile_input" style="display:none">

				<?php if (!empty($wk_pro_id) && !empty($mainpage) && $mainpage == 'product') {?>

					<input type="hidden" value="<?php echo $wk_pro_id; ?>" name="sell_pr_id" id="sell_pr_id" />

					<input type="hidden" value="<?php echo $product->get_type(); ?>" name="sell_pr_type" id="sell_pr_type" />

					<input type="hidden" name="active_product_tab" id="active_product_tab" value="<?php echo isset($_POST['active_product_tab']) ? $_POST['active_product_tab'] : ''; ?>" />

				<?php }?>


			</div>


			<div class="wkmp_profile_input">

				<label for="product_desc"><?php echo esc_html_e('About Product', 'marketplace'); ?></label>

				<?php

    $settings = array(
        'media_buttons' => true, // show insert/upload button(s).
        'textarea_name' => 'product_desc',
        'textarea_rows' => get_option('default_post_edit_rows', 10),
        'tabindex' => '',
        'teeny' => false,
        'dfw' => false,
        'tinymce' => true,
        'quicktags' => false,
    );

    if (isset($post_row_data[0]->post_content)) {
        $content = $post_row_data[0]->post_content;
    }

    if (isset($content)) {
        echo wp_editor($content, 'product_desc', $settings);
    } else {
        echo wp_editor('', 'product_desc', $settings);
    }
    ?>

				<div id="long_desc_error" class="error-class"></div>

			</div>

			<div class="wkmp_profile_input">

				<label for="product_category"><?php echo esc_html_e('Product Category', 'marketplace'); ?></label>

				<?php
$categories = array();

    $categories = wp_get_post_terms($wk_pro_id, 'product_cat', array('fields' => 'slugs'));

    ?>

				<?php

    $allowed_cat = get_user_meta($user_id, 'wkmp_seller_allowed_categories', true);

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
        'selected' => $categories,
        'echo' => 0,
        'value_field' => 'slug',
        'walker' => new MpProductCategoryTree($allowed_categories),
    ));

    echo str_replace('<select', '<select data-placeholder="' . esc_html__('Choose category(s)', 'marketplace') . '" multiple="multiple" ', $product_categories);

    if ($product->get_type() == 'variable' || $product->get_type() == 'grouped') {
        $reg_val = 'disabled';
        $sel_val = 'disabled';
    }

    ?>

			</div>


			<div class="wkmp_profile_input">

				<label for="fileUpload"><?php echo esc_html_e('Product Thumbnail', 'marketplace'); ?></label>

				<?php if (isset($meta_arr['image'])) {?>

					<img src="" width="50" height="50">

				<?php }?>

				<div id="product_image"></div>

				<input type="hidden"  id="product_thumb_image_mp" name="product_thumb_image_mp" value="<?php if (isset($meta_arr['_thumbnail_id'])) {
        echo $meta_arr['_thumbnail_id'];
    }
    ?>" />

				<?php

    if (!empty($product_images)) {
        echo '<div id="mp-product-thumb-img-div" style="display:inline-block;position:relative;"><img style="display:inline;vertical-align:middle;" src="' . content_url() . '/uploads/' . $product_images . '" width=50 height=50 /><span style="    right: -20px;top: -12px;" title="'.__('Remove', 'marketplace').'" class="mp-image-remove-icon">x</span></div>';
    }

    ?>

				<p><a class="upload mp_product_thumb_image button" data-type-error="<?php echo esc_html__('Only jpg|png|jpeg files are allowed.', 'marketplace'); ?>" href="javascript:void(0);" /><?php esc_html_e('Upload', 'marketplace');?></a></p>


			</div>

			<div class="wkmp_profile_input">

				<label for="product_sku"><?php echo esc_html_e('Product SKU', 'marketplace'); ?>
					<span class="required">*</span>: &nbsp;
					<span class="help">
						<div class="wkmp-help-tip-sol">
							<?php echo esc_html_e('SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.', 'marketplace'); ?>
						</div>
						<span class="help-tip"></span>
					</span>
				</label>

				<?php
if (!isset($meta_arr['_sku'])) {
        echo '<input class="wkmp_product_input" type="text" name="product_sku" id="product_sku" value="" />';
    } else {
        echo '<p>' . $meta_arr['_sku'] . '</p>';
    }

    ?>

				<div id="pro_sku_error" class="error-class"></div>

			</div>

			<div class="wkmp_profile_input" id="regularPrice">

				<label for="regu_price"><?php echo esc_html__('Regular Price', 'marketplace'); ?>
					<span class="required">*</span>
				</label>

				<input class="wkmp_product_input" type="text" name="regu_price" id="regu_price" value="<?php if (isset($meta_arr['_regular_price'])) {
        echo $meta_arr['_regular_price'];
    }
    ?>" <?php echo ($pro_term_relation == 4 || $pro_term_relation == 3) ? 'disabled="disabled"' : ''; ?> <?php if (!empty($reg_val)) {
        echo 'disabled = ' . $reg_val;
    }
    ?> />

				<div id="regl_pr_error" class="error-class"></div>

			</div>

			<div class="wkmp_profile_input" id="salePrice">

				<label for="sale_price"><?php echo esc_html__('Sale Price', 'marketplace'); ?></label>

				<input class="wkmp_product_input" type="text" name="sale_price" id="sale_price" value="<?php if (isset($meta_arr['_sale_price'])) {
        echo $meta_arr['_sale_price'];
    }
    ?>" <?php echo ($pro_term_relation == 4) ? 'disabled="disabled"' : ''; ?> <?php if (!empty($reg_val)) {
        echo 'disabled = ' . $reg_val;
    }
    ?> />

				<div id="sale_pr_error" class="error-class"></div>

			</div>

            <div id="general_product_data" class="panel woocommerce_options_panel">
                <?= get_product_settings($wk_pro_id); ?>
            </div>

			<div class="wkmp_profile_input">

				<label for="short_desc"><?php echo esc_html_e('Product Short Description ', 'marketplace'); ?></label>

				<?php

    $settings = array(
        'media_buttons' => false, // show insert/upload button(s).
        'textarea_name' => 'short_desc',
        'textarea_rows' => get_option('default_post_edit_rows', 10),
        'tabindex' => '',
        'editor_class' => 'backend',
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

		</div>


		<div class="wkmp_container" id="inventorytabwk">

			<div class="wkmp_profile_input">

				<label for="wk-mp-stock"><?php echo esc_html_e('Manage Stock', 'marketplace') . '?'; ?></label>

				<p><input type="checkbox" class="wkmp_stock_management" id="wk_stock_management" name="wk_stock_management" value ="yes" <?php if ($meta_arr['_manage_stock'] == 'yes') {
        echo 'checked';
    }
    ?> /><label for="wk_stock_management"><?php esc_html_e('Enable stock management at product level', 'marketplace');?></label></p>

			</div>

			<div class="wkmp_profile_input" style="display:none;">

				<label for="wk-mp-stock"><?php echo esc_html__('Stock Qty', 'marketplace'); ?></label>

				<input type="text" class="wkmp_product_input" placeholder="<?php echo esc_attr__('0', 'marketplace'); ?>" name="wk-mp-stock-qty" id="wk-mp-stock-qty" value="<?php echo isset($meta_arr['_stock']) ? $meta_arr['_stock'] : ''; ?>" />

			</div>

			<div class="wkmp_profile_input" style="display:none;">

				<label for="wk-mp-backorders"><?php echo esc_html__('Allow Backorders', 'marketplace'); ?></label>

				<select name="_backorders" id="_backorders" class="form-control">

					<option value="no" <?php if (isset($meta_arr['_backorders']) && $meta_arr['_backorders'] == 'no') {
        echo 'selected="selected"';
    }
    ?>><?php echo esc_html_e('Do not allow', 'marketplace'); ?></option>

					<option value="notify" <?php if (isset($meta_arr['_backorders']) && $meta_arr['_backorders'] == 'notify') {
        echo 'selected="selected"';
    }
    ?>><?php echo esc_html_e('Allow but notify customer', 'marketplace'); ?></option>

					<option value="yes" <?php if (isset($meta_arr['_backorders']) && $meta_arr['_backorders'] == 'yes') {
        echo 'selected="selected"';
    }
    ?>><?php esc_html_e('Allow', 'marketplace');?></option>

				</select>

			</div>

			<div class="wkmp_profile_input">
				<label for="wk-mp-stock"><?php echo esc_html__('Stock Status', 'marketplace'); ?></label>

				<select name="_stock_status" id="_stock_status" class="form-control">

					<option value="instock" <?php if (isset($meta_arr['_stock_status']) && $meta_arr['_stock_status'] == 'instock') {
        echo 'selected="selected"';
    }
    ?> ><?php esc_html_e('In Stock', 'marketplace');?></option>

					<option value="outofstock" <?php if (isset($meta_arr['_stock_status']) && $meta_arr['_stock_status'] == 'outofstock') {
        echo 'selected="selected"';
    }
    ?>><?php esc_html_e('Out of Stock', 'marketplace');?></option>

				</select>

			</div>

			<?php do_action('mp_edit_product_field', $wk_pro_id);?>

		</div>

		<div class="wkmp_container" id="shippingtabwk">

			<?php

    echo '<div class="options_group wkmp_profile_input">';

    // Weight.
    if (wc_product_weight_enabled()) {

        marketplace_wp_text_input(array('id' => '_weight', 'label' => __('Weight', 'marketplace') . ' (' . get_option('woocommerce_weight_unit') . ')', 'placeholder' => wc_format_localized_decimal(0), 'desc_tip' => 'true', 'description' => __('Weight in decimal form', 'marketplace'), 'type' => 'text', 'data_type' => 'decimal', 'value' => esc_attr(wc_format_localized_decimal(get_post_meta($wk_pro_id, '_weight', true)))), $wk_pro_id);
    }

    // Size fields.
    if (wc_product_dimensions_enabled()) {
        ?>
					<label for="product_length"><?php echo esc_html__('Dimensions', 'marketplace') . ' (' . get_option('woocommerce_dimension_unit') . ')'; ?></label>
					<span class="wrap">
						<input id="product_length" placeholder="<?php esc_attr_e('Length', 'marketplace');?>" class="input-text wc_input_decimal" size="6" type="text" name="_length" value="<?php echo esc_attr(wc_format_localized_decimal(get_post_meta($wk_pro_id, '_length', true))); ?>" />
						<input placeholder="<?php esc_attr_e('Width', 'marketplace');?>" class="input-text wc_input_decimal" size="6" type="text" name="_width" value="<?php echo esc_attr(wc_format_localized_decimal(get_post_meta($wk_pro_id, '_width', true))); ?>" />
						<input placeholder="<?php esc_attr_e('Height', 'marketplace');?>" class="input-text wc_input_decimal last" size="6" type="text" name="_height" value="<?php echo esc_attr(wc_format_localized_decimal(get_post_meta($wk_pro_id, '_height', true))); ?>" />
					</span>

					<?php echo wc_help_tip(esc_html__('LxWxH in decimal form', 'marketplace')); ?>

				<?php
}

    echo '</div>';

    echo '<div class="options_group wkmp_profile_input">';

    // Shipping Class.
    $classes = get_the_terms($wk_id, 'product_shipping_class');
    if ($classes && !is_wp_error($classes)) {
        $current_shipping_class = current($classes)->term_id;
    } else {
        $current_shipping_class = '';
    }

    $args = array(
        'taxonomy' => 'product_shipping_class',
        'hide_empty' => 0,
        'show_option_none' => __('No shipping class', 'marketplace'),
        'name' => 'product_shipping_class',
        'id' => 'product_shipping_class',
        'selected' => $current_shipping_class,
        'class' => 'select short',
    );

    ?><label for="product_shipping_class"><?php esc_html_e('Shipping class', 'marketplace');?></label> <?php wp_dropdown_categories($args);?> <?php echo wc_help_tip(esc_html__('Shipping classes are used by certain shipping methods to group similar products.', 'marketplace')); ?><?php

    do_action('marketplace_product_options_shipping', $wk_pro_id);

    echo '</div>';

    ?>

		</div>

		<div class="wkmp_container" id="linkedtabwk">

		<?php

    $product_query = $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type = 'product' and post_status = 'publish' and post_author = '%d' ORDER BY ID DESC", $user_id);

    $product_array = $wpdb->get_results($product_query);

    if ($product->is_type('grouped')):
        include 'wk-html-product-linked.php';
    endif;

    ?>


			<div class="options_group wkmp_profile_input">
				<p class="form-field">
					<label for="upsell_ids"><?php esc_html_e('Upsells', 'marketplace');?></label>
					<select class="wc-product-search" multiple="multiple" style="width: 50%;" id="upsell_ids" name="upsell_ids[]" data-placeholder="<?php esc_attr_e('Search&hellip;', 'marketplace');?>">
						<?php
$product_ids = $product->get_upsell_ids('edit');
    foreach ($product_array as $key => $value) {
        $item = wc_get_product($value->ID);
        if (is_object($item) && $wk_pro_id != $value->ID) {?>
									<option value="<?php echo $value->ID; ?>" <?php if (in_array($value->ID, $product_ids)) {
            echo 'selected';
        }
            ?>> <?php echo wp_kses_post($item->get_formatted_name()); ?></option>
							<?php
}
    }
    ?>
					</select>
				</p>

				<?php if (!$product->is_type('external') && !$product->is_type('grouped')): ?>
						<p class="form-field hide_if_grouped hide_if_external">
							<label for="crosssell_ids"><?php esc_html_e('Cross-sells', 'marketplace');?></label>
							<select class="wc-product-search" multiple="multiple" style="width: 50%;" id="crosssell_ids" name="crosssell_ids[]" data-placeholder="<?php esc_attr_e('Search&hellip;', 'marketplace');?>">
								<?php
$product_ids = $product->get_cross_sell_ids('edit');
    foreach ($product_array as $key => $value) {
        $item = wc_get_product($value->ID);
        if (is_object($item) && $wk_pro_id != $value->ID) {
            ?>
										<option value="<?php echo $value->ID; ?>" <?php if (in_array($value->ID, $product_ids)) {
                echo 'selected';
            }
            ?>> <?php echo wp_kses_post($item->get_formatted_name()) ?></option>
										<?php
}
    }
    ?>
							</select>
						</p>
				<?php endif;?>
			</div>

		</div>

		<div class="wkmp_container" id="attributestabwk">

			<div class="input_fields_toolbar">

				<button class="btn btn-success add-variant-attribute"><?php echo esc_html__('Add an attribute', 'marketplace'); ?></button>

			</div>


			<div  class="wk_marketplace_attributes">
				<?php
if (!empty($product_attributes)) {
        $i = 0;
        foreach ($product_attributes as $key_at => $proatt) {
            $optin = $product->get_attribute($key_at);

            $optin = str_replace(',', ' |', $optin);
            ?>
				<div class="wkmp_attributes">
					<div class="box-header attribute-remove">
					<input type="text" class="mp-attributes-name wkmp_product_input" placeholder="Attribute name" name="pro_att[<?php echo $i; ?>][name]" value="<?php echo str_replace('-', ' ', $proatt['name']); ?>"/>
					<input type="text" class="option wkmp_product_input" title="<?php echo esc_html__('attribue value by seprating comma eg. a|b|c', 'marketplace'); ?>" placeholder=" <?php echo esc_html__('Value eg. a|b|c', 'marketplace'); ?>" name="pro_att[<?php echo $i; ?>][value]" value="<?php
echo esc_attr($proatt['value']); ?>
					"/>
					<input type="hidden" name="pro_att[<?php echo $i; ?>][position]" class="attribute_position" value="<?php echo $proatt['position']; ?>"/>
						<span class="mp_actions">
							<button class="mp_attribute_remove btn btn-danger"><?php echo esc_html__('Remove', 'marketplace'); ?></button>
						</span>
					</div>
					<div class="box-inside clearfix">
						<div class="wk-mp-attribute-config">
							<div class="checkbox-inline">
								<input type="checkbox" id="is_visible_page[<?php echo $i; ?>]" class="checkbox" name="pro_att[<?php echo $i; ?>][is_visible]" value="1" <?php if ($proatt['is_visible'] == '1') {
                echo 'checked';
            }
            ?>/><label for="is_visible_page[<?php echo $i; ?>]"><?php echo esc_html__('Visible on the product page', 'marketplace'); ?></label></div>

								<?php if ($product->is_type('variable')): ?>

									<div class="checkbox-inline">
										<input type="checkbox" class="checkbox" name="pro_att[<?php echo $i; ?>][is_variation]" value="1" id="used_for_variation[<?php echo $i; ?>]" <?php if ($proatt['is_variation'] == '1') {
                echo 'checked';
            }
            ?>/><label for="used_for_variation[<?php echo $i; ?>]"><?php echo esc_html__('Used for variations', 'marketplace'); ?></label>
									</div>

								<?php endif;?>
								<input type="hidden" name="pro_att[<?php echo $i; ?>][is_taxonomy]" value="<?php echo isset($proatt['taxonomy']) ? $proatt['taxonomy'] : ''; ?>"/>
							</div>
							<div class="attribute-options"></div>
						</div>
					</div>
					<?php
$i++;
        }
    }
    ?>
			</div>

			</div>

			<div class="wkmp_container" id="external_affiliate_tabwk">

				<div class="wkmp_profile_input">

					<label for="product_url"><?php echo esc_html__('Product URL', 'marketplace'); ?></label>

					<input class="wkmp_product_input" type="text" name="product_url" id="product_url" size="54" value="<?php if (isset($meta_arr['_product_url'])) {
        echo $meta_arr['_product_url'];
    }
    ?>" />

					<div id="pro_url_error" class="error-class"></div>

				</div>

				<div class="wkmp_profile_input">

					<label for="button_txt"><?php echo esc_html__('Button Text', 'marketplace'); ?></label>

					<input class="wkmp_product_input" type="text" name="button_txt" id="button_txt" size="54" value="<?php if (isset($meta_arr['_button_text'])) {
        echo $meta_arr['_button_text'];
    }
    ?>" />

					<div id="pro_btn_txt_error" class="error-class"></div>

				</div>

			</div>

	<!-- varication attribute of the product -->
	<div class="wkmp_container" id="avariationtabwk">
		<div id="mp_attribute_variations">
			<?php
echo marketplace_attributes_variation($wk_pro_id);
    ?>
		</div>
		<div class="input_fields_toolbar_variation">
			<div id="mp-loader"></div>
			<button id="mp_var_attribute_call" class="btn btn-success"><?php echo '+ ';
    esc_html_e('Add Variation', 'marketplace'); ?></button>
		</div>

	</div>

			<div class="wkmp_container" id="pro_statustabwk">
				<?php if (get_option('wkmp_seller_allow_publish')) {?>
						<div class="mp-sidebar-container">
							<div class="mp_wk-post-status wkmp-toggle-sidebar">
								<label for="post_status"><?php echo esc_html_e('Product Status', 'marketplace') . ' :'; ?></label>
								<?php
if (isset($post_row_data[0]->post_status) && !empty($post_row_data[0]->post_status) && $post_row_data[0]->post_status == 'publish') {
        echo '<span class="mp-toggle-selected-display green">'.__('Online', 'marketplace').'</span>';
    } else {
        echo '<span class="mp-toggle-selected-display">'.__('Draft', 'marketplace').'</span>';
    }

        ?>
							<a class="mp-toggle-sider-edit label label-success button" href="javascript:void(0);" style="display: inline;"><?php echo esc_html('Edit', 'marketplace'); ?></a>
							<div class="wkmp-toggle-select-container mp-hide" style="display: none;">
								<select id="product_post_status" class="wkmp-toggle-select" name="mp_product_status">
									<option value=""><?php echo esc_html__('Select status', 'marketplace'); ?></option>
									<option value="publish" <?php if ($post_row_data[0]->post_status == 'publish') {
            echo 'selected="selected"';
        }
        ?>><?php echo esc_html_e('Online', 'marketplace'); ?></option>
									<option value="draft"  <?php if ($post_row_data[0]->post_status == 'draft') {
            echo 'selected="selected"';
        }
        ?>><?php echo esc_html_e('Draft', 'marketplace'); ?></option>
								</select>
								<a class="mp-toggle-save button" href="javascript:void(0);"><?php echo esc_html__('OK', 'marketplace'); ?></a>
								<a class="mp-toggle-cancel button" href="javascript:void(0);"><?php echo esc_html__('Cancel', 'marketplace'); ?></a>
							</div>
						</div>
					</div>
			<?php
}

    if ($product->get_type() == 'simple') {

        ?>

				<div class="wkmp-side-head">
					<label class="checkbox-inline">
						<input type="checkbox" id="_ckvirtual" class="wk-dwn-check" name="_virtual" value="yes" <?php if (isset($meta_arr['_virtual']) && $meta_arr['_virtual'] == 'yes') {echo 'checked';}?>/>&nbsp;&nbsp;
						<?php esc_html_e('Virtual', 'marketplace');?>
					</label>
				</div>

				<hr class="mp-section-seperate">
				<!-- downloadable starts -->
				<div class="wkmp-side-head">
					<label class="checkbox-inline">
						<input type="checkbox" id="_ckdownloadable" class="wk-dwn-check" name="_downloadable" value="yes" <?php if (isset($meta_arr['_downloadable']) && $meta_arr['_downloadable'] == 'yes') {
            echo 'checked';
        }
        ?>/>&nbsp;&nbsp;
						<?php esc_html_e('Downloadable Product', 'marketplace');?>
					</label>
				</div>
				<div class="wk-mp-side-body" style="display:<?php if (isset($meta_arr['_downloadable']) && $meta_arr['_downloadable'] == 'yes') {
            echo 'block';
        } else {
            echo 'none';
        }
        ?>" >
					<?php
$mp_downloadable_files = get_post_meta($wk_pro_id, '_downloadable_files', true);
        ?>
					<div class="form-field downloadable_files">
						<label><?php echo esc_html('Downloadable files', 'marketplace'); ?></label>
						<table class="widefat">
							<thead>
								<tr>
									<th><?php echo esc_html('Name', 'marketplace'); ?></th>
									<th colspan="2"><?php echo esc_html('File URL', 'marketplace'); ?></th>
									<th>&nbsp;</th>
								</tr>
							</thead>
							<tbody>
								<?php
if ($mp_downloadable_files) {
            foreach ($mp_downloadable_files as $key => $file) {
                include 'wk-html-product-download.php';
            }
        }
        ?>
							</tbody>
							<tfoot>
								<tr>
									<th colspan="5">
										<a href="#" class="button insert" data-row="<?php
$key = '';
        $file = array(
            'file' => '',
            'name' => '',
        );
        ob_start();
        include 'wk-html-product-download.php';
        echo esc_attr(ob_get_clean());
        ?>">
											<?php esc_html_e('Add File', 'marketplace');?>
										</a>
									</th>
							</tr>
					</tfoot>
				</table>
			</div>
			<p class="form-field _download_limit_field wkmp_profile_input">
				<label for="_download_limit"><?php echo esc_html__('Download limit', 'marketplace'); ?></label>
				<input type="number" class="short wkmp_product_input" style="padding: 3px 5px;" name="_download_limit" id="_download_limit" value="<?php if (isset($meta_arr['_download_limit'])) {if (-1 == $meta_arr['_download_limit']) {echo '';} else {echo $meta_arr['_download_limit'];}}?>" placeholder="<?php esc_attr_e('Unlimited','marketplace'); ?>" step="1" min="0" />
				<span class="description"><?php echo esc_html__('Leave blank for unlimited re-downloads.', 'marketplace'); ?></span>
			</p>

			<p class="form-field _download_expiry_field ">
				<label for="_download_expiry"><?php echo esc_html__('Download expiry', 'marketplace'); ?></label>
				<input type="number" class="short wkmp_product_input" style="padding: 3px 5px;" name="_download_expiry" id="_download_expiry" value="<?php if (isset($meta_arr['_download_expiry'])) {if (-1 == $meta_arr['_download_expiry']) {echo '';} else {echo $meta_arr['_download_expiry'];}}?>" placeholder="<?php esc_attr_e('Never','marketplace'); ?>" step="1" min="0" />
				<span class="description"><?php echo esc_html__('Enter the number of days before a download link expires, or leave blank.', 'marketplace'); ?></span>
			</p>
		</div>
<?php }?>
		<hr class="mp-section-seperate">
		<!-- downloadable ends -->

		<div class="wkmp-side-head"><label><?php echo __('Image Gallery', 'marketplace'); ?></label></div>

			<div id="wk-mp-product-images">
				<div id="product_images_container">
					<?php
if (isset($meta_arr['_product_image_gallery']) && $meta_arr['_product_image_gallery'] != '') {
        $image_id = explode(',', get_post_meta($wk_pro_id, '_product_image_gallery', true));
        for ($i = 0; $i < count($image_id); $i++) {
            $image_url = wp_get_attachment_image_src($image_id[$i]);
            echo "<div class='mp_pro_image_gallary'><img src='" . $image_url[0] . "' width=50 height=50 />";
            ?>
							<a href="javascript:void(0);" id="<?php echo $wk_pro_id . 'i_' . $image_id[$i]; ?>" class="mp-img-delete_gal" title="<?php esc_attr_e('Delete image','marketplace'); ?>"><?php echo esc_html__('Delete', 'marketplace'); ?></a>
						</div>
					<?php
}
    }
    ?>
				</div>
				<div id="handleFileSelectgalaray">
				</div>
				<input type="hidden" class="wkmp_product_input" name="product_image_Galary_ids" id="product_image_Galary_ids" value="<?php if (isset($meta_arr['_product_image_gallery'])) {
        echo $meta_arr['_product_image_gallery'];
    }
    ?>" />
			</div>
			<a href="javascript:void(0);" class="add-mp-product-images btn">+ <?php echo esc_html__('Add product images', 'marketplace'); ?></a>
		</p>
		<?php wp_nonce_field('marketplace-edid_product');?>
		</div>
		<?php do_action('mp_edit_product_tabs_content', $wk_pro_id);?>
		<br>
		<input type="submit" name="add_product_sub" id="add_product_sub" value="<?php esc_attr_e('Update','marketplace'); ?>" class="button"/></td>
		</form>
		</div>
		<?php
unset($_POST);
} elseif (empty($product_auth)) {
    echo '<h2>' . esc_html__('No product found...', 'marketplace') . '</h2>';
    echo '<a href="' . esc_html(site_url()) . '/' . get_option('wkmp_seller_page_title') . '/' . get_option( 'mp_add_product', 'add-product' ) . '">Create New Product</a>';
} else {
    ?>
				<div class="woocommerce-account">
					<?php
do_action('mp_get_wc_account_menu', 'marketplace');
    ?>
					<div class="woocommerce-MyAccount-content">
						<?php
global $wpdb;
    $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option('wkmp_seller_page_title') . "'");
    echo '<div class="woocommerce-Message woocommerce-Message--info woocommerce-error">
							<a class="woocommerce-Button button" href="' . site_url($page_name) . '/' . get_option( 'mp_product_list','product-list' ) . '">
								' . esc_html__('Go To Products', 'marketplace') . '		</a>
							' . esc_html__("Sorry, but you can not edit other sellers' product..!", 'marketplace') . '
						</div>';
    ?>
					</div>
				</div>
				<?php
}