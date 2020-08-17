<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('MP_Add_Front_Rma')) {
    class MP_Add_Front_Rma
    {
        public function mp_add_new_rma()
        {
            if (isset($_POST['submit_rma_request'])) {
                do_action('mp_save_rma_request_details', $_POST);
            }

            if (get_option('mp_rma_order_statuses')) {
                foreach (get_option('mp_rma_order_statuses') as $key => $value) {
                    $status_arr[$value] = $value;
                }
            }

            $days = get_option('mp_rma_time');

            $customer_orders = get_posts(array(
                'numberposts' => -1,
                'meta_key' => '_customer_user',
                'meta_value' => get_current_user_id(),
                'post_type' => wc_get_order_types(),
                'post_status' => !empty($status_arr) ? array_keys($status_arr) : '',
                'date_query' => array(
                    'after' => $days . ' days ago',
                ),
            ));

            $user_id = apply_filters('mp_rma_user_id', 'user_id');

            $requested_rma = apply_filters('mp_get_customer_rma_order_id', $user_id);

            if (null == $requested_rma) {
                $requested_rma[0] = array();
            }?> <div class="woocommerce-account"> <?php

            apply_filters('mp_get_wc_account_menu', 'marketplace');

            ?>

			<div class="woocommerce-MyAccount-content">

				<h1><?php echo __('New RMA Information', 'marketplace-rma'); ?></h1>

				<form method="post" action="" class="mp_request_rma" enctype="multipart/form-data">

					<p><span class="required">* </span><label for="rma-order"><?php esc_html_e('Order', 'marketplace-rma');?></label></p>

					<p><select name="mp_rma_order" id="mp-rma-order" class="full-width form-control">
						<option value="">--<?php esc_html_e('Select', 'marketplace-rma');?>--</option>
						<?php foreach ($customer_orders as $key => $value): ?>
							<?php if (!in_array($value->ID, $requested_rma)): ?>
								<option value="<?php echo $value->ID; ?>"><?php echo '#' . $value->ID . ' ' . $value->post_title; ?></option>
							<?php endif;?>
						<?php endforeach;?>
					</select></p>

					<p><span class="required">* </span><label for="item"><?php esc_html_e('Items Ordered', 'marketplace-rma');?></label></p>

					<div class="responsive"><table class="mp_rma_items_ordered" border="1">
						<thead>
							<tr>
								<th><?php esc_html_e('Product Name', 'marketplace-rma');?></th>
								<th><?php esc_html_e('Quantity', 'marketplace-rma');?></th>
								<th></th>
								<th><?php esc_html_e('Reason', 'marketplace-rma');?></th>
								<th><?php esc_html_e('Returned Quantity', 'marketplace-rma');?></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table></div>

					<p><label for="images"><?php esc_html_e('Image(s) of Product', 'marketplace-rma');?></label></p>

					<div class="form-elm form-images">
									<div id="mk-rss-img-wrapper">
										<label class="image-preview" id="mk-rss-attach-img-label-1" for="mk-rss-attach-img-1" ><span onclick=remove_preview(1) class="mk-rss-image-remove">x</span>
										<input type="file" onchange=image_selected(1) name="product-img-1" class="hide-input" id="mk-rss-attach-img-1"></label>
									</div>
									<span class="attach-more"><a id="mk-rss-attach-more">+ <?php esc_html_e('Attach image', 'marketplace-rma');?></a></span>
								</div>

					<p><span > </span><label for="info"><?php esc_html_e('Additional Information', 'marketplace-rma');?></label></p>

					<p><textarea rows="4" class="mp_rma_add_info" name="mp_add_info"></textarea></p>

					<p><span class="required">* </span><label for="status"><?php esc_html_e('Order Delivery Status', 'marketplace-rma');?></label></p>

					<p>
						<select id="status" name="mp_order_status" class="mp_order_status form-control full-width">
							<option value="">--<?php esc_html_e('Select', 'marketplace-rma');?>--</option>
							<option value="complete"><?php esc_html_e('Complete', 'marketplace-rma');?></option>
							<option value="pending"><?php esc_html_e('Pending', 'marketplace-rma');?></option>
						</select>
					</p>

					<p><span class="required">* </span><label for="resolution"><?php esc_html_e('Resolution Type', 'marketplace-rma');?></label></p>

					<p>
						<select id="resolution" name="mp_resolution_type" class="mp_resolution form-control full-width">
							<option value="">--<?php esc_html_e('Select', 'marketplace-rma');?>--</option>
							<option value="refund"><?php esc_html_e('Refund', 'marketplace-rma');?></option>
							<option value="exchange"><?php esc_html_e('Exchange', 'marketplace-rma');?></option>
						</select>
					</p>

					<p><label for="consignment"><?php esc_html_e('Please add consignment no. if you are returning product(s)', 'marketplace-rma');?></label></p>

					<p><input type="text" name="mp_autono" class="full-width" id="consignment" value=""></p>

					<p><label for="policy"><?php esc_html_e('Return Policy', 'marketplace-rma');?></label></p>

					<p class="policy-content"><?php echo get_option('mp_rma_policy'); ?></p>

					<p><span class="required">* </span><input name="mp_policy_agree" id="wk_i_agree" type="checkbox" class="wk_rma_checkall" data-validate="{required:true}">
					<label for="wk_i_agree"><span><?php esc_html_e('I have read and agree to the policy', 'marketplace-rma');?>.</span></label></p>

					<?php wp_nonce_field('request_rma_nonce_action', 'request_rma_nonce');?>

					<p style="float:right"><input type="submit" id="mp_rma_add_button" value="<?php esc_html_e('Request', 'marketplace-rma');?>" name="submit_rma_request" class="woocommerce-button button"></p>

					<p><a href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id')); ?>rma" class="woocommerce-button button"><?php esc_html_e('Back', 'marketplace-rma');?></a></p>

				</form>

					</div>

				</div>

          <?php
}
    }
}
