<?php

if (!class_exists('MP_Manage_Seller_Rma_Reason')) {
    class MP_Manage_Seller_Rma_Reason
    {
        public function mp_manage_seller_reason()
        {
            global $wpdb;

            $wpmp_pid = '';

            $rma_res_stat = array(
                'enabled' => esc_html__('Enabled', 'marketplace-rma'),
                'disabled' => esc_html__('Disabled', 'marketplace-rma'),
            );

            $user_id = apply_filters('mp_rma_user_id', 'user_id');

            $wk_data = apply_filters('mp_get_rma_reasons', $user_id);

            $mainpage = get_query_var('main_page');

            $p_id = get_query_var('pid');

            $action = get_query_var('action');

            if (!empty($p_id)) {
                $wpmp_pid = $p_id;
            }

            $product_auth = $wpdb->get_var("select user_id from {$wpdb->prefix}mp_rma_reasons where id='$wpmp_pid'");

            $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option('wkmp_seller_page_title') . "'");

            if (!empty($mainpage) && !empty($action)) {

                if ($mainpage == 'rma-reason' && $action == 'delete' && $product_auth == $user_id && isset( $_GET['mp_rma_reason_delete_nonce'] )) {

                    if( !wp_verify_nonce( $_GET['mp_rma_reason_delete_nonce'], 'mp_rma_reason_delete_nonce' ) ) {
                    
                        wc_add_notice( __('Sorry, your nonce did not verify.', 'marketplace-rma'), 'error' );
                        wp_redirect(site_url() . '/' . $page_name . '/rma-reason');
                        exit;
    
                    }

                    $sql = $wpdb->delete($wpdb->prefix . 'mp_rma_reasons', array('id' => $wpmp_pid));

                    if ($sql) {
                        wc_add_notice( __( 'Reason deleted successfully', 'marketplace-rma' ) );
                        wp_redirect(site_url() . '/' . $page_name . '/rma-reason');
                        exit;
                    }
                }

            }?> <div class="woocommerce-account"> <?php

            apply_filters('mp_get_wc_account_menu', 'marketplace');?>

                <div class="woocommerce-MyAccount-content">

                    <div id="main_container">

                    <?php echo '<a href="../add-reason" class="button mp-button-right">' . __('Add', 'marketplace-rma') . '</a>'; ?>

                    <table class="reasonlist">
                        <thead>
                            <tr>
                                <th><?php echo __('Reason', 'marketplace-rma'); ?></th>
                                <th><?php echo __('Status', 'marketplace-rma'); ?></th>
                                <th><?php echo __('Action', 'marketplace-rma'); ?></th>
                            </tr>

                        </thead>
                        <tbody>
                            <?php foreach ($wk_data as $key => $value): ?>
                                <tr>
                                    <td><?php echo $value['reason']; ?></td>
                                    <td><?php echo $rma_res_stat[$value['status']]; ?></td>
                                    <td><?php echo '<a id="editprod" class="mp-action" href="edit/' . $value['id'] . '">' . __('edit', 'marketplace-rma') . '</a>'; ?><a id="delprod" class="mp-action" href="delete/<?php echo $value['id'] . '?mp_rma_reason_delete_nonce=' . wp_create_nonce( 'mp_rma_reason_delete_nonce' ); ?>" onclick="return confirm(<?php esc_html_e('Are you sure ? Delete Reason also affect RMA Order and you can lost some data related to these reasons !!', 'marketplace-rma');?>)" class="ask"><?php esc_html_e('delete', 'marketplace-rma');?></a></td>
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
