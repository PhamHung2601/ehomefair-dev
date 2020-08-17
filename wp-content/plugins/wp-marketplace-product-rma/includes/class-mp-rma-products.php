<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( 'MP_Rma_Products' ) )
{
    /**
     *
     */
    class MP_Rma_Products
    {

        function __construct()
        {
            add_action( 'mp_rma_view_products', array( $this, 'mp_customer_rma_products' ) );
        }

        function mp_customer_rma_products()
        {
            global $wpdb;
            $rma_id   = apply_filters( 'mp_rma_id', 'rma_id' );
            $wk_data = apply_filters( 'mp_get_rma_data', $rma_id );
            $item_data = maybe_unserialize($wk_data[0]->items);
            ?>
            <table class="widefat fixed" cellspacing="0">
                <thead>
                    <tr>
                        <td class="toptable"><strong><?php esc_html_e('Name', 'marketplace-rma'); ?></strong></td>
                        <td class="toptable"><strong><?php esc_html_e('Reason', 'marketplace-rma'); ?></strong></td>
                        <td class="toptable"><strong><?php esc_html_e('Return Quantity', 'marketplace-rma'); ?></strong></td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $item_data['items'] as $key => $value ): ?>
                        <tr>
                            <td><?php echo get_the_title($value); ?></td>
                            <td>
                                <?php
                                $reason_id = $item_data['reason'][$value];
                                $wk_post = $wpdb->get_results("Select reason from {$wpdb->prefix}mp_rma_reasons where id = '$reason_id'", ARRAY_A);
                                echo !empty( $wk_post[0]['reason'] ) ? $wk_post[0]['reason'] : '-';
                                ?>
                            </td>
                            <td><?php echo $item_data['quantity'][$value]; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="toptable"><strong><?php esc_html_e('Name', 'marketplace-rma'); ?></strong></td>
                        <td class="toptable"><strong><?php esc_html_e('Reason', 'marketplace-rma'); ?></strong></td>
                        <td class="toptable"><strong><?php esc_html_e('Return Quantity', 'marketplace-rma'); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
            <?php
        }

    }

    new MP_Rma_Products();

}
