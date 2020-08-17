<?php

if ( !class_exists( 'MP_Rma_print_Shipping' ) )
{
    /**
     *
     */
    class MP_Rma_print_Shipping
    {

        function mp_rma_print_shipping_label()
        {
            global $wpdb;
            $rma_id   = apply_filters( 'mp_rma_id', 'rma_id' );
            $meta_table = $wpdb->prefix.'mp_rma_request_meta';
            $result = $wpdb->get_results("Select meta_value from $meta_table where meta_key = 'shipping_label' and rma_id = '$rma_id'");
            $address = get_option('mp_rma_address');
            $dir = wp_upload_dir();
            $wk_data = apply_filters( 'mp_get_rma_data', $rma_id );
            $item_data = maybe_unserialize($wk_data[0]->items);

            ?>
            <style type="text/css">
            @media print
            {
            body * { visibility: hidden; }
            .wk_rma_label_wrapper *{ visibility: visible; }
            .wk_rma_label_wrapper { position: absolute; top: 40px; left: 0px; right: 0; bottom: 0; margin: auto;}
            }
            .label_shippping { float: right; }
            .table-bordered {
                border: 1px solid #ddd;
            }
            .table {
                width: 100%;
                max-width: 100%;
            }
            .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
                padding: 8px;
                line-height: 1.42857143;
                vertical-align: top;
                border-top: 1px solid #ddd;
            }
            .table-bordered>tbody>tr>td, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>td, .table-bordered>tfoot>tr>th, .table-bordered>thead>tr>td, .table-bordered>thead>tr>th {
                border: 1px solid #ddd;
            }
            </style>

            <div style="text-align:right"><button onclick="javascript:window.print()"><i class="fa fa-print"></i></button></div>
            <div class="wk_rma_label_wrapper">

                <p class="head"><?php esc_html_e('Return Mailing Label', 'marketplace-rma'); ?></p>
                <div class="wk_mailing_label">
                    <div class="label_shippping">
                        <?php if ( $result ): ?>
                            <img src="<?php echo $dir['baseurl'].$result[0]->meta_value; ?>" width="100" height="100" />
                        <?php else: ?>
                            <p><?php esc_html_e('Shipping Label', 'marketplace-rma'); ?></p>
                        <?php endif; ?>
                    </div>
                    <p><strong><?php esc_html_e('From', 'marketplace-rma'); ?></strong></p>
                    <div>
                      ------------------------------------------------------------------<br>
                      ------------------------------------------------------------------<br>
                      ------------------------------------------------------------------<br>
                      ------------------------------------------------------------------<br>
                    </div>
                    <p><strong>To</strong></p>
                    <?php foreach (explode(",",$address) as $key => $value): ?>
                        <?php echo $value.'<br>'; ?>
                    <?php endforeach; ?>
                </div>

                <p class="head"><?php esc_html_e('Return Authorisation Label', 'marketplace-rma'); ?></p>
                <div class="wk_authorisation_label">
                    <p><strong><?php esc_html_e('Order ID', 'marketplace-rma'); ?>: </strong><?php echo $wk_data[0]->order_no; ?>  <strong> <?php esc_html_e('RMA ID', 'marketplace-rma');?> : </strong><?php echo $rma_id; ?></p>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                              <td><?php esc_html_e('Product Name', 'marketplace-rma'); ?></td>
                              <td><?php esc_html_e('Reason', 'marketplace-rma'); ?> </td>
                              <td><?php esc_html_e('Quantity', 'marketplace-rma'); ?></td>
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
                                        echo $wk_post[0]['reason'];
                                        ?>
                                    </td>
                                    <td><?php echo $item_data['quantity'][$value]; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>

            <?php
        }

    }

}
