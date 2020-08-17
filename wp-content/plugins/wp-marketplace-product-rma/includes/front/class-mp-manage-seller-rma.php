<?php

if (!class_exists('MP_Manage_Seller_Rma')) {
    class MP_Manage_Seller_Rma
    {
        public function mp_manage_seller_rmas()
        {
            global $wpdb;
            $user_id = apply_filters('mp_rma_user_id', 'user_id');
            $table_name = $wpdb->prefix . 'mp_rma_requests';
            $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option('wkmp_seller_page_title') . "'");
            if( !empty( $_REQUEST['rma-select-status'] ) ) {
                $rma_status = $_REQUEST['rma-select-status'];
                $rma_status_filter_query = "AND rma_status='$rma_status'";
            }
            else {
                $rma_status_filter_query = '';
                $rma_status = '';
            }
            $wk_data = $wpdb->get_results( "SELECT * from $table_name where seller_id = '$user_id' $rma_status_filter_query ORDER BY id ASC", ARRAY_A);

            $rma_stat = array(
                'processing' => esc_html__('Approve', 'marketplace-rma'),
                'declined' => esc_html__('Decline', 'marketplace-rma'),
                'solved' => esc_html__('Solved', 'marketplace-rma'),
                'pending' => esc_html__('Pending', 'marketplace-rma'),
            );?> <div class="woocommerce-account"> <?php

            apply_filters('mp_get_wc_account_menu', 'marketplace');?>

                <div class="woocommerce-MyAccount-content">

                    <div id="main_container">

                    <?php echo '<a href="' . home_url('/' . $page_name . '/rma-reason') . '" class="button mp_rma_manage_reasons_button">' . __('Manage Reason(s)', 'marketplace-rma') . '</a>'; ?>

                    <form method="get" class="alignleft">

                        <select name="rma-select-status" id="rma-select-status" class="ewc-filter-rma-status" style="max-width:200px; padding: 11px;" class="form-control">

                            <option value=""><?php echo __('Select status', 'marketplace-rma'); ?></option>
                            <option value="pending" <?php echo $rma_status == 'pending' ? 'selected' : '' ?>><?php echo __('Pending', 'marketplace-rma'); ?></option>
                            <option value="processing" <?php echo $rma_status == 'processing' ? 'selected' : '' ?>><?php echo __('Processing', 'marketplace-rma'); ?></option>
                            <option value="solved" <?php echo $rma_status == 'solved' ? 'selected' : '' ?>><?php echo __('Solved', 'marketplace-rma'); ?></option>
                            <option value="declined" <?php echo $rma_status == 'declined' ? 'selected' : '' ?>><?php echo __('Declined', 'marketplace-rma'); ?></option>

                        </select>

                        <input type="submit" class="button-primary" value="<?php _e('Select RMA Status', 'marketplace-rma'); ?>">

                    </form>

                    <table class="mpRmaList">
                        <thead>
                            <tr>
                                <th><?php _e('ID', 'marketplace-rma');?></th>
                                <th><?php _e('Order ID', 'marketplace-rma');?></th>
                                <th><?php _e('Customer Name', 'marketplace-rma');?></th>
                                <th><?php _e('Products', 'marketplace-rma');?></th>
                                <th><?php _e('Reason', 'marketplace-rma');?></th>
                                <th><?php _e('RMA Status', 'marketplace-rma');?></th>
                                <th><?php _e('Date', 'marketplace-rma');?></th>
                                <th><?php _e('Action', 'marketplace-rma');?></th>
                            </tr>

                        </thead>
                        <tbody>
                            <?php foreach ($wk_data as $key => $value): ?>

                            <tr>
                                <td><?php _e($value['id']);?></td>
                                <td><?php _e($value['order_no']);?></td>
                                <td><?php _e(get_userdata($value['customer_id'])->display_name);?></td>
                                <td><?php
foreach (maybe_unserialize($value['items'])['items'] as $k => $val) {
                echo get_the_title($val) . '<br>';
            }?></td>
                                 <td><?php
foreach (maybe_unserialize($value['items'])['reason'] as $k => $val) {
                $wk_post = $wpdb->get_results("Select reason from {$wpdb->prefix}mp_rma_reasons where id = '$val'", ARRAY_A);
                echo $wk_post[0]['reason'] . '<br>';
            }?></td>
                                  <td><?php echo '<strong class="wk_rma_status_' . $value['rma_status'] . '">' . $rma_stat[$value['rma_status']] . '</strong>'; ?></td>
                                  <td><?php echo $value['datetime']; ?></td>
                                  <td><a id="viewprod" title="<?php esc_html_e('Edit', 'marketplace-rma');?>" href="../../<?php echo $page_name; ?>/rma/edit/<?php echo $value['id']; ?>"><?php esc_html_e('edit', 'marketplace-rma');?></a></td>
                            </tr>

                            <?php endforeach;?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
            <?php
}
    }
}
