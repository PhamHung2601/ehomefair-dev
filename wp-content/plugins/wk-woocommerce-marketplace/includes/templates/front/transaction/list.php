<?php

if (! defined('ABSPATH') ) {
    exit;
}

global $transaction;
$search  = '';
$current_user = get_current_user_id();
$page_no = get_query_var('pagenum');
$current_page = !empty($page_no) ? intval($page_no) : 1;

$transaction = $transaction->get($current_user, $current_page);

$transaction_count = $transaction['total_count'];
$transactions = $transaction['transaction'];
?>
<div class="woocommerce-account">
    <?php do_action('mp_get_wc_account_menu', 'marketplace'); ?>
    <div id="main_container" class="wk_transaction woocommerce-MyAccount-content">
        <table class="transactionhistory">

            <thead>
                <tr>
                    <th width="20%"><?php echo esc_html__('Tranaction Id', 'marketplace'); ?></th>
                    <th width="20%"><?php echo esc_html__('Date', 'marketplace'); ?></th>
                    <th width="20%"><?php echo esc_html__('Amount', 'marketplace'); ?></th>
                    <th width="20%"><?php echo esc_html__('Action', 'marketplace'); ?></th>
                </tr>
            </thead>

            <tbody>
                <?php
                if (! empty($transactions) && is_array($transactions) ) {
                    foreach ( $transactions as $trans ) {
                        $order = wc_get_order($trans['order_id']);
                        if ($order) {
                            $ord_currncy = $order->get_currency();
                        } else {
                            $ord_currncy = get_woocommerce_currency();
                        }
                        $transaction_id = $trans['transaction_id'];
                        $date           = $trans['transaction_date'];
                        $amount         = wc_price($trans['amount'], array( 'currency' => $ord_currncy ));
                        $action         = site_url(get_option('wkmp_seller_page_title').'/' . get_option('mp_transaction', 'transaction') . '/view/') . $trans['id'];
                        ?>
                        <tr>
                            <td data-tab="<?php echo esc_html__('Tranaction Id', 'marketplace'); ?>">
                                <a href="<?php echo $action; ?>">
                                    <?php echo $transaction_id; ?>
                                </a>
                            </td>
                            <td data-tab="<?php echo esc_html__('Date', 'marketplace'); ?>">
                                <?php echo $date; ?>
                            </td>
                            <td data-tab="<?php echo esc_html__('Amount', 'marketplace'); ?>">
                                <?php echo $amount; ?>
                            </td>
                            <td class="wkmp-view-wrap" data-tab="<?php echo esc_html__('Action', 'marketplace'); ?>">
                                <a href="<?php echo $action; ?>" class="button">
                                    <?php esc_html_e('View', 'marketplace') ?>
                                    <span class="wkmp-view"></span>
                                </a>
                            </td>
                        </tr>
                    <?php
                    }
                }
                ?>
            </tbody>
        </table>
        <?php
            $maxpage_count = ceil($transaction_count / 10);
        if (1 < $maxpage_count ) : 
        ?>
        <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
            <?php
            if (1 !== $current_page ) :
            ?>
            <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url(site_url(get_option('wkmp_seller_page_title').'/'.get_option('mp_transaction', 'transaction').'/page/'.($current_page - 1))); ?>">
                <?php esc_html_e('Previous', 'marketpalce'); ?>
                        </a>
                    <?php
            endif;
                        
            if (intval($maxpage_count) !== $current_page ) :
            ?>
            <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url(site_url(get_option('wkmp_seller_page_title').'/'.get_option('mp_transaction', 'transaction').'/page/'.($current_page + 1))); ?>">
                <?php esc_html_e('Next', 'marketplace'); ?>
            </a>
                <?php 
            endif;
        ?>
        </div>
        <?php
        endif;
        ?>
    </div>
</div>
