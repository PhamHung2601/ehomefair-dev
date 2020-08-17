<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('MP_Rma_Seller_Update_Status_Manage')) {
    class MP_Rma_Seller_Update_Status_Manage
    {
        public function __construct()
        {
            add_action('mp_rma_view_seller_shipping_label', array($this, 'mp_rma_view_update_status_seller'));
        }

        public function mp_rma_view_update_status_seller()
        {
            global $wpdb;
            $rma_id = apply_filters('mp_rma_id', 'user_id');
            $wk_data = apply_filters('mp_get_rma_data', $rma_id);
            $table = $wpdb->prefix.'mp_rma_requests';
            $meta_table = $wpdb->prefix.'mp_rma_request_meta';
            $count = 0;

            if (isset($_POST['submit_rma_status'])) {
                if (isset($_POST['wk_update_status'])) {
                    $status = $_POST['wk_update_status'];
                    $sql_status = $wpdb->update($table,
                        array(
                            'rma_status' => $status,
                        ),
                        array(
                            'id' => $rma_id,
                        )
                    );

                    if ($sql_status) {
                        $message = array();
                        $message[] = __('RMA status changed to', 'marketplace-rma').' "'.$status.'" '.__('by Seller for order', 'marketplace-rma').' #'.$wk_data[0]->order_no."\n";
                        $message[] = __('RMA ID', 'marketplace-rma').' : '.$rma_id."\n\n";
                        $data = array(
                            'msg' => $message,
                            'email' => get_userdata($wk_data[0]->customer_id)->user_email,
                        );
                        do_action('woocommerce_mp_rma_mail', $data);
                        wc_add_notice(__('RMA status changed to', 'marketplace-rma').' '.$status.'.', 'success');
                    }
                }

                // update label
                if (isset($_POST['shipping_label']) || isset($_POST['shipping_label_path'])) {
                    $sql = '';
                    $label = isset($_POST['shipping_label']) ? $_POST['shipping_label'] : '	';
                    $result = $wpdb->get_results("Select meta_value from $meta_table where meta_key = 'shipping_label' and rma_id = '$rma_id'");
                    if (isset($_POST['shipping_label_path']) && !empty($_POST['shipping_label_path'])) {
                        $dir = wp_upload_dir();
                        $label_url = $_POST['shipping_label_path'];
                        $label = str_replace($dir['baseurl'], '', $label_url);
                    }
                    if ($result) {
                        $sql = $wpdb->update($meta_table,
                            array(
                                'meta_value' => $label,
                            ),
                            array(
                                'rma_id' => $rma_id,
                                'meta_key' => 'shipping_label',
                            )
                        );
                    } else {
                        $sql = $wpdb->insert($meta_table,
                            array(
                                'rma_id' => $rma_id,
                                'meta_key' => 'shipping_label',
                                'meta_value' => $label,
                            )
                        );
                    }
                    if ($sql) {
                        wc_add_notice(__('Shipping label updated successfully!', 'marketplace-rma'), 'success');
                    }
                }

                // upload label
                if (isset($_POST['shipping_label_path']) && !empty($_POST['shipping_label_path'])) {
                    $dir = wp_upload_dir();
                    $label_url = $_POST['shipping_label_path'];
                    $label_url = str_replace($dir['baseurl'], '', $label_url);
                    $user_id = apply_filters('mp_rma_user_id', 'user_id');
                    $paths = get_user_meta($user_id, 'mp_rma_shipping_label_path', true);

                    if (empty($paths)) {
                        $path_data = array();
                    } else {
                        $path_data = $paths;
                    }

                    $path_data[] = $label_url;

                    $check = update_user_meta($user_id, 'mp_rma_shipping_label_path', $path_data);

                    if ($check) {
                        wc_add_notice(__('Shipping label uploaded and sent successfully!', 'marketplace-rma'), 'success');
                    }
                }

                wp_redirect($_SERVER['HTTP_REFERER']);
                exit;
            }

            $wk_data = apply_filters('mp_get_rma_data', $rma_id);
            $shipping_labels = apply_filters('mp_get_shipping_labels', $rma_id);

            $dir = wp_upload_dir();

            $result = $wpdb->get_results("Select meta_value from $meta_table where meta_key = 'shipping_label' and rma_id = '$rma_id'"); ?>
            <form method="post" action="">

                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="rma_status"><strong><?php esc_html_e('RMA Status', 'marketplace-rma'); ?></strong></label>
                            </th>

                            <td class="forminp">
                                <select name="wk_update_status" id="rma_status" style="min-width:350px;">
                                    <option value="">-- <?php esc_html_e('Select', 'marketplace-rma'); ?> --</option>
                                    <option value="processing" <?php if ($wk_data[0]->rma_status == 'processing') {
                echo 'selected';
            } ?>><?php esc_html_e('Approve', 'marketplace-rma'); ?></option>
                                    <option value="declined" <?php if ($wk_data[0]->rma_status == 'declined') {
                echo 'selected';
            } ?>><?php esc_html_e('Decline', 'marketplace-rma'); ?></option>
                                    <option value="solved" <?php if ($wk_data[0]->rma_status == 'solved') {
                echo 'selected';
            } ?>><?php esc_html_e('Solved', 'marketplace-rma'); ?></option>
                                </select>
                            </td>
                        </tr>

                        <?php if ($wk_data[0]->resolution == 'exchange'): ?>

														<tr><td></td><td><?php esc_html_e('You can upload and send label from here and can select from previously added label(s)', 'marketplace-rma'); ?>.</td></tr>
														<tr>
																<td><p><label for="upload_label"><strong><?php esc_html_e('Upload shipping label', 'marketplace-rma'); ?></strong></label></p></td>
																<td><p><input type="button" id="upload_shipping_label" value="<?php esc_html_e('Upload', 'marketplace-rma'); ?>" class="button button-secondary" /></p></td>
																<td><p><input type="text" value="" name="shipping_label_path" class="shipping-label-path" /></p></td>
														</tr>
														<?php if ($shipping_labels): ?>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label for=""><strong><?php esc_html_e('Select Shipping Label', 'marketplace-rma'); ?></strong></label>
                                </th>
                                <td class="forminp">
                                    <?php foreach ($shipping_labels as $key => $value): ?>
	                                      <div class="shipping_label_each">
	                                          <label for="shipping_label_<?php echo $key; ?>"><img src="<?php echo $dir['baseurl'].$value; ?>" class="shipping_label_img"></label>
	                                          <input id="shipping_label_<?php echo $key; ?>" value="<?php echo $value; ?>" type="radio" <?php if ($result && $result[0]->meta_value == $value) {
                echo 'checked';
            } ?> name="shipping_label" class="shipping_label">
	                                      </div>
                                    <?php $count = $key;
            endforeach; ?>
																		<?php if ($result && !in_array($result[0]->meta_value, $shipping_labels)): ?>
																			<div class="shipping_label_each">
																					<label for="shipping_label_<?php echo ++$count; ?>"><img src="<?php echo $dir['baseurl'].$result[0]->meta_value; ?>" class="shipping_label_img"></label>
																					<input id="shipping_label_<?php echo ++$count; ?>" value="<?php echo $result[0]->meta_value; ?>" type="radio" <?php echo 'checked'; ?> name="shipping_label" class="shipping_label">
																			</div>
																		<?php endif; ?>
                                </td>
                            </tr>
													<?php endif; ?>

                        <?php endif; ?>

                    </tbody>
                </table>
                <p><input type="submit" name="submit_rma_status" class="button button-primary" value="<?php esc_html_e('Update', 'marketplace-rma'); ?>" /></p>
            </form>
            <?php
        }
    }

    new MP_Rma_Seller_Update_Status_Manage();
}
