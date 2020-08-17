<?php

if (! defined('ABSPATH')) {
		exit; //exit if directly accessed
}

/**
 * Seller product's configuration
 */

?>

<p><?php echo __('Manage product settings for seller.', 'marketplace'); ?></p>
<?php settings_errors(); ?>
<form method="post" action="options.php">
		<?php settings_fields( 'marketplace-products-settings-group' ); ?>

		<table class="form-table">
				<tbody>
						<tr valign="top">
								<th scope="row">
										<label for="wkmp_seller_allow_publish"><?php echo __('Allow seller to publish', 'marketplace'); ?></label>
								</th>

								<td class="forminp">
										<input name="wkmp_seller_allow_publish" type="checkbox" id="wkmp_seller_allow_publish" value="1" <?php checked( get_option( 'wkmp_seller_allow_publish' ), 1 ); ?>/>
										<?php echo _e("Can user publish his/her item online", "marketplace"); ?>
								</td>
						</tr>

						<tr valign="top">
								<th scope="row">
										<label for="wkmp_seller_allowed_product_types"><?php echo __('Product type for seller', 'marketplace'); ?></label>
								</th>

								<td class="forminp">
										<select name="wkmp_seller_allowed_product_types[]" multiple="true" id="wkmp_seller_allowed_product_types" data-placeholder="Select product type..." style="min-width:350px;">
												<?php foreach (wc_get_product_types() as $key => $value) : ?>
														<?php if (get_option('wkmp_seller_allowed_product_types')) : ?>
																<option value="<?php echo $key; ?>" <?php if (in_array($key, get_option('wkmp_seller_allowed_product_types'))) {
																	echo 'selected';
																} ?>><?php echo $value; ?></option>
														<?php else : ?>
																<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
														<?php endif; ?>
												<?php endforeach; ?>
										</select>
								</td>
						</tr>

						<tr valign="top">
								<th scope="row">
										<label for="wkmp_seller_allowed_categories"><?php echo __('Allowed categories', 'marketplace'); ?></label>
								</th>

								<td class="forminp">
										<select name="wkmp_seller_allowed_categories[]" multiple="true" id="wkmp_seller_allowed_categories" data-placeholder="Select categories..." style="min-width:350px;">
												<?php

												$product_categories = get_terms('product_cat', array(
													'hide_empty' => false
												));

												if (!empty($product_categories)) :
														foreach ($product_categories as $key => $value) : ?>
																<?php if (get_option('wkmp_seller_allowed_categories')) : ?>
																		<option value="<?php echo $value->slug; ?>" <?php if (in_array($value->slug, get_option('wkmp_seller_allowed_categories'))) {
																			echo 'selected';
																		} ?>><?php echo $value->name; ?></option>
																<?php else : ?>
																		<option value="<?php echo $value->slug; ?>"><?php echo $value->name; ?></option>
																<?php endif; ?>
														<?php endforeach; ?>
												<?php endif; ?>
										</select>
								</td>
						</tr>
				</tbody>
		</table>

		<?php	submit_button(); ?>
</form>
