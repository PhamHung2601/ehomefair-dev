<tr class="<?php echo SUMO_PP_PLUGIN_PREFIX . 'orderpp_payable_now_info' ; ?>">
    <th>
        <?php _e( 'Payable Now', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?>
    </th>
    <td style="vertical-align: top;">
        <strong><?php echo wc_price( $down_payment ) ; ?></strong>
    </td>
</tr>

<tr class="<?php echo SUMO_PP_PLUGIN_PREFIX . 'orderpp_payment_details_info' ; ?>">
    <th>
        <?php _e( 'Payment Details', SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?>
    </th>
    <td style="vertical-align: top;">
        <p style="font-weight:normal;text-transform:none;">
            <?php echo $payment_info ; ?>
            <?php echo $balance_payable ; ?>
        </p>
    </td>
</tr>