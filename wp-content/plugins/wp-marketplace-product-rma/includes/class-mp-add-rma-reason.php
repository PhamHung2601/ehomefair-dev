<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('MP_RMA_Add_Reason')) {
    class MP_RMA_Add_Reason
    {
        public function mp_add_reason_rma()
        {
            global $wpdb;

            if (!is_admin()) {
                ?>

				<div class="woocommerce-account">

				<?php apply_filters('mp_get_wc_account_menu', 'marketplace');?>

				<div class="woocommerce-MyAccount-content">

				<?php
}
            echo '<div class="wrap">';
            if (is_admin()) {
                echo '<h1 class="wp-heading-inline">' . __('New Reason', 'marketplace-rma') . '</h1>';
            }
            echo '<div class="tablenav top"><strong>' . __('Add Reason', 'marketplace-rma') . '</strong></div>';

            if (isset($_POST['mp_add_reason'])) {
                do_action('mp_save_reason_rma', $_POST);
            }

            $table_name = $wpdb->prefix . 'mp_rma_reasons';

            if (null != get_query_var('pid') || isset($_GET['rid'])) {
                $id = (get_query_var('pid')) ? get_query_var('pid') : $_GET['rid'];
                $wk_posts = $wpdb->get_results("Select * from $table_name where id = '$id'", ARRAY_A);
            }?>

			<form action="" method="post">

				<table class="form-table">
					<tbody>

						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="rma_reason"><?php esc_html_e('Reason', 'marketplace-rma');?></label>
							</th>
							<td class="forminp">
								<input type="text" name="wk_rma_reason" id="rma_reason" value="<?php if (isset($wk_posts)) {
                echo $wk_posts[0]['reason'];
            }?>" style="min-width:350px;" />
		                                </td>
		                            </tr>

		                            <tr valign="top">
		                                <th scope="row" class="titledesc">
		                                    <label for="reason_enable"><?php esc_html_e('Status', 'marketplace-rma');?></label>
		                  	            </th>
		                                <td class="forminp">
		                                    <select name="wk_rma_status" id="reason_enable" style="min-width:350px;">
		                                        <option value="">-- <?php esc_html_e('Select', 'marketplace-rma');?> --</option>
		                                        <option value="enabled" <?php if (isset($wk_posts) && $wk_posts[0]['status'] == 'enabled') {
                echo 'selected';
            }?>><?php esc_html_e('Enabled', 'marketplace-rma');?></option>
		                                        <option value="disabled" <?php if (isset($wk_posts) && $wk_posts[0]['status'] == 'disabled') {
                echo 'selected';
            }?>><?php esc_html_e('Disabled', 'marketplace-rma');?></option>
		                                    </select>
		                                </td>
		                            </tr>

		                            <input type="hidden" name="reason_id" value="<?php if (isset($wk_posts)) {
                echo $id;
            }?>" /></p>
		                        </tbody>
		                    </table>
		                    <?php
wp_nonce_field('rma_reason_nonce_action', 'rma_reason_nonce');
            echo '<p><input type="submit" name="mp_add_reason" class="button button-primary" value="' . esc_html__('Save', 'marketplace-rma') . '" /></p>';
            ?>

		                </form>
		                <?php
echo '</div>';
            if (!is_admin()) {
                echo '</div></div>';
            }
        }
    }
}
