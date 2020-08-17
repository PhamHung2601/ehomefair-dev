<?php

if (! defined('ABSPATH') ) {
    exit;
}

/**
 * Column created.
 *
 * @param int $order_id order id.
 */
function wk_mp_invoice( $order_id ) 
{
    include_once 'order/invoice.php';
}

/**
 * Column created.
 */
function order_history() 
{
    include_once 'order/order-history.php';
}

/**
 * Column created.
 */
function order_view() 
{
    include_once 'order/order-view.php';
}
/**
 * Order statsus.
 *
 * @param array $data data array.
 */
function mp_order_update_status( $data ) 
{
    include_once 'order/order-status.php';
}
