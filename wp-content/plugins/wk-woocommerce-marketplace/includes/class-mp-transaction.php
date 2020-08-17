<?php
/**
 * This file handles seller order share transaction.
 *
 * @package Woocommerce Marketplace
 */

if (! defined('ABSPATH') ) {
    exit;
}

if (! class_exists('MP_Transaction') ) {
    /**
     * Marketplace transaction.
     */
    class MP_Transaction
    {

        /**
         * Base Function.
         */
        public function __construct() 
        {
            global $wpdb, $transaction;
            $this->table_name = $wpdb->prefix . 'seller_transaction';
            $transaction      = __CLASS__;
        }

        /**
         * Generate Transaction.
         *
         * @param int $seller_id Seller ID.
         * @param int $order_id  Order ID.
         * @param int $amount    Order Item ID.
         */
        public function generate( $seller_id, $order_id, $amount, $transaction_id = '' ) 
        {
            global $wpdb;
            $response = '';

            if (empty($transaction_id) ) {
                $order_password = get_post_field('post_password', $order_id);
                $replace        = 'tr-' . $seller_id;
                if (! empty($order_password) ) {
                    $transaction_id = str_replace('order_', $replace, $order_password);
                }
            }
            $current_time = date('Y-m-d H:i:s');

            if (! empty($transaction_id) ) {
                $response = $wpdb->insert(
                    $this->table_name,
                    array(
                    'transaction_id'   => $transaction_id,
                    'order_id'         => maybe_serialize($order_id),
                    'seller_id'        => $seller_id,
                    'amount'           => $amount,
                    'type'             => 'manual',
                    'method'           => 'manual',
                    'transaction_date' => $current_time,
                    ),
                    array(
                    '%s',
                    '%d',
                    '%d',
                    '%f',
                    '%s',
                    '%s',
                    '%s',
                    )
                );
            }
            return $response;
        }

        /**
         * Get Seller Transaction.
         *
         * @param int $seller_id Seller ID.
         * @param int $current_page current page.
         *
         * @return array
         */
        public function get( $seller_id, $current_page = 1 ) 
        {
            global $wpdb;
            $offset = ($current_page -1) * 10;
            $table_name = $this->table_name;
            $resp = $wpdb->get_results("SELECT count(id) FROM $table_name WHERE seller_id = '$seller_id' ORDER BY id DESC", ARRAY_A);
            $res = $wpdb->get_results("SELECT * FROM $table_name WHERE seller_id = '$seller_id' ORDER BY id DESC LIMIT $offset, 10", ARRAY_A);
            $result = array(
                'total_count' => intval($resp[0]["count(id)"]),
                'transaction' => $res
            );
            return $result;
        }

        /**
         * Get Transaction Detail.
         *
         * @param int $id        Transaction ID.
         * @param int $seller_id Seller ID.
         */
        public function get_by_id( $id, $seller_id = '' ) 
        {
            global $wpdb;
            $table_name = $this->table_name;
            if (! empty($seller_id) ) {
                $result = $wpdb->get_results("SELECT * FROM $table_name WHERE seller_id = '$seller_id' AND id = '$id'", ARRAY_A);
            } else {
                $result = $wpdb->get_results("SELECT * FROM $table_name WHERE id = '$id'", ARRAY_A);
            }
            if (! empty($result) && isset($result[0]) ) {
                return $result[0];
            }
            return array();
        }
    }
}
