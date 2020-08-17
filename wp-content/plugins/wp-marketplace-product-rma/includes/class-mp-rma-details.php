<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('MP_Rma_Details')) {
    class MP_Rma_Details
    {
        public function __construct()
        {
            add_action('mp_rma_view_details', array($this, 'mp_customer_rma_details'));
        }

        public function mp_customer_rma_details()
        {
            $rma_id = apply_filters('mp_rma_id', 'rma_id');
            $wk_data = apply_filters('mp_get_rma_data', $rma_id);
            $rma_stat = array(
                'processing' => esc_html__('Approve', 'marketplace-rma'),
                'declined' => esc_html__('Decline', 'marketplace-rma'),
                'solved' => esc_html__('Solved', 'marketplace-rma'),
                'pending' => esc_html__('Pending', 'marketplace-rma'),
            );
            $or_stat = array(
                'complete' => __('Complete', 'marketplace-rma'),
                'on-hold' => __('On Hold', 'marketplace-rma'),
                'processing' => __('Processing', 'marketplace-rma'),
                'cancelled' => __('Cancelled', 'marketplace-rma'),
                'failed' => __('Failed', 'marketplace-rma'),
                'refunded' => __('Refunded', 'marketplace-rma'),
                'pending' => __('Pending', 'marketplace-rma'),
            );
            
            $resoltn_stat = array(
                'refund' => __('Refund', 'marketplace-rma'),
                'exchange' => __('Exchange', 'marketplace-rma'),
            );
            if (isset($_GET['rid'])) {
                $order_url = get_edit_post_link($wk_data[0]->order_no);
            } else {
                $order_url = get_permalink(get_option('woocommerce_myaccount_page_id')).'view-order/'.$wk_data[0]->order_no;
            } ?>
            <table class="widefat fixed" cellspacing="0">
                <thead>
                    <tr>
                        <td class="toptable"><strong><?php esc_html_e('Option', 'marketplace-rma'); ?></strong></td>
                        <td class="toptable"><strong><?php esc_html_e('Value', 'marketplace-rma'); ?></strong></td>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <td><?php esc_html_e('Order ID', 'marketplace-rma'); ?></td>
                        <td>#<?php echo $wk_data[0]->order_no; ?></td>
                    </tr>

                    <tr>
                        <td><?php esc_html_e('RMA Status', 'marketplace-rma'); ?></td>
                        <td>
                          <strong class="wk_rma_status_<?php echo $wk_data[0]->rma_status; ?>"><?php echo $rma_stat[$wk_data[0]->rma_status]; ?></strong>
                        </td>
                    </tr>

                    <tr>
                        <td><?php esc_html_e('Delivery Status', 'marketplace-rma'); ?></td>
                        <td>
                          <strong class="wk_delivery_status"><?php echo $or_stat[$wk_data[0]->order_status]; ?></strong>
                        </td>
                    </tr>

                    <tr>
                        <td><?php esc_html_e('Customer Name', 'marketplace-rma'); ?></td>
                        <td><?php echo get_userdata($wk_data[0]->customer_id)->display_name; ?></td>
                    </tr>

                    <tr>
                        <td><?php esc_html_e('Resolution Type', 'marketplace-rma'); ?></td>
                        <td><?php echo $resoltn_stat[$wk_data[0]->resolution]; ?></td>
                    </tr>

                    <tr>
                        <td><?php esc_html_e('Additional Information', 'marketplace-rma'); ?></td>
                        <td><?php echo $wk_data[0]->information; ?></td>
                    </tr>

                </tbody>
                <tfoot>
                    <tr>
                        <td class="toptable"><strong><?php esc_html_e('Option', 'marketplace-rma'); ?></strong></td>
                        <td class="toptable"><strong><?php esc_html_e('Value', 'marketplace-rma'); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
            <?php
        }
    }

    new MP_Rma_Details();
}
