<?php
/**
 * This file handles list for seller orders.
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Seller order list table.
 */
class Seller_Order_List extends WP_List_Table
{
    /**
     * Order List.
     *
     * @var array order List
     */
    public $order_list;

    /**
     * Seller ID.
     *
     * @var int seller ID
     */
    public $seller_id;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(
            array(
                'singular' => 'Seller Order List',
                'plural' => 'Seller Orders List',
                'ajax' => false,
            )
        );
    }

    /**
     * Handles all list functions.
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();

        $sortable = $this->get_sortable_columns();

        $hidden = $this->get_hidden_columns();

        $this->process_bulk_action();

        $data = $this->table_data();

        $totalitems = count($data);

        $screen = get_current_screen();

        $perpage = $this->get_items_per_page('product_per_page', 20);

        $this->_column_headers = array($columns, $hidden, $sortable);

        if (empty($per_page) || $per_page < 1) {
            $per_page = $screen->get_option('per_page', 'default');
        }

        /**
         * Handles sort order of data.
         *
         * @param array $a default Order
         * @param array $b result Order
         */
        function usort_reorder($a, $b)
        {
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'order_id'; // If no sort, default to title.

            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; // If no order, default to asc.

            $result = strnatcmp($a[$orderby], $b[$orderby]); // Determine sort order.

            return ('asc' === $order) ? $result : -$result; // Send final sort direction to usort.
        }

        usort($data, 'usort_reorder');

        $totalpages = ceil($totalitems / $perpage);

        $currentpage = $this->get_pagenum();

        $data = array_slice($data, (($currentpage - 1) * $perpage), $perpage);

        $this->set_pagination_args(
            array(
                'total_items' => $totalitems,
                'total_pages' => $totalpages,
                'per_page' => $perpage,
            )
        );

        $this->items = $data;
    }

    /**
     * Define the columns that are going to be used in the table.
     *
     * @return array $columns, the array of columns to use with the table
     */
    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', // Render a checkbox instead of text.
            'order_id' => __('Order Id', 'marketplace'),
            'product' => __('Product', 'marketplace'),
            'quantity' => __('Quantity', 'marketplace'),
            'status' => __('Status', 'marketplace'),
            'product_total' => __('Product Total', 'marketplace'),
            'shipping' => __('Shipping', 'marketplace'),
            'discount' => __('Discount', 'marketplace'),
            'total_commission' => __('Total Commission', 'marketplace'),
            'total_seller_amount' => __('Total Seller Amount', 'marketplace'),
            'action' => __('Action', 'marketplace'),
        );

        return $columns;
    }

    /**
     * Default Column name with data goes here.
     *
     * @param array  $item        column Array
     * @param string $column_name individual Column Slug
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'order_id':
            case 'product':
            case 'quantity':
            case 'status':
            case 'product_total':
            case 'shipping':
            case 'discount':
            case 'total_commission':
            case 'total_seller_amount':
            case 'action':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    /**
     * Decide which columns to activate the sorting functionality on.
     *
     * @return array $sortable, the array of columns that can be sorted by the user
     */
    public function get_sortable_columns()
    {
        $sortable = array(
            'order_id' => array('order_id', true),
            'status' => array('status', true),
        );

        return $sortable;
    }

    /**
     * Hidden Columns.
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Checkbox column data.
     *
     * @param array $item column data
     */
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" id="oid_%s" name="oid[]" value="%s" />', $item['id'], $item['id']);
    }

    /**
     * Product column data.
     *
     * @param array $item column data
     */
    public function column_product($item)
    {
        $pro_str = '';
        foreach ($item['product'] as $pro) {
            $pro_str = $pro_str . $pro['title'] . ' ( #' . $pro['id'] . ')<br>';
        }

        return sprintf('%s', $pro_str);
    }

    /**
     * Action column data.
     *
     * @param array $item column data
     */
    public function column_action($item)
    {
        $act_str = esc_html__('N.A', 'marketplace');
        if ($item['total_seller_amount'] > 0) {

            if ($item['action'] == 'paid') {
    
                $act_str = '<button class="button button-primary" class="admin-order-pay" disabled>' . __('Paid', 'marketplace') . '</button>';
            } else {
                $act_str = '<a href="javascript:void(0)" data-id="' . $item['id'] . '" class="page-title-action admin-order-pay">' . __('Pay', 'marketplace') . '</a>';
            }
        }

        return sprintf('%s', $act_str);
    }

    /**
     * Checkbox column data.
     *
     * @param array $item column data
     */
    public function column_product_total($item)
    {
        $order = wc_get_order( $item['order_id'] );
        return sprintf('%s', wc_price($item['product_total'], array('currency' => $order->get_currency())));
    }

    /**
     * Checkbox column data.
     *
     * @param array $item column data
     */
    public function column_shipping($item)
    {
        $order = wc_get_order( $item['order_id'] );
        return sprintf('%s', wc_price($item['shipping'], array('currency' => $order->get_currency())));
    }

    /**
     * Checkbox column data.
     *
     * @param array $item column data
     */
    public function column_total_commission($item)
    {
        $order = wc_get_order($item['order_id']);
        return sprintf('%s', wc_price($item['total_commission'], array('currency' => $order->get_currency())));
    }

    /**
     * Checkbox column data.
     *
     * @param array $item column data
     */
    public function column_total_seller_amount($item)
    {
        $reward_point_weightage = !empty( $GLOBALS['reward'] ) ?  $GLOBALS['reward']->get_woocommerce_reward_point_weightage() : 0;

        $order = wc_get_order($item['order_id']);
        $rwd_note = '';
        if (!empty($item['reward_data'])) {
            if (!empty($item['reward_data']['seller'])) {
                $rwd_note = ' - ' . wc_price($item['reward_data']['seller'] * $reward_point_weightage, array('currency' => $order->get_currency())) . '( ' . __('Reward', 'marketplace') . ' )';
            }
        }
        if (!empty($item['wallet_data'])) {
            if (!empty($item['wallet_data']['seller'])) {
                $rwd_note .= ' - ' . wc_price($item['wallet_data']['seller'], array('currency' => $order->get_currency())) . '( ' . __('Wallet', 'marketplace') . ' )';
            }
        }

        if ($item['total_seller_amount'] != $item['product_total']) {
            $tip = '<p>';
            $tip .= wc_price($item['total_seller_amount'], array('currency' => $order->get_currency()));
            $tip .= ' = ';
            $tip .= wc_price($item['product_total'], array('currency' => $order->get_currency()));
			if( $item['shipping'] != 0 ) {
				$tip .= ' + ';
				$tip .= wc_price($item['shipping'], array('currency' => $order->get_currency())) . ' ( ' . __('Shipping', 'marketplace') . ' ) ';
			}
			if( $item['total_commission'] != 0 ) {
				$tip .= ' - ';
				$tip .= wc_price($item['total_commission'], array('currency' => $order->get_currency())) . ' ( ' . __('Commission', 'marketplace') . ' ) ';
			}
            if (!empty($rwd_note)) {
                $tip .= $rwd_note;
            }
            $tip .= ' ';
            $tip .= '</p>';
            return sprintf('%s %s', '<span style="display:inline-block">' . wc_price($item['total_seller_amount'], array('currency' => $order->get_currency())) . '</span>', wc_help_tip($tip, true));
        }

        return sprintf('%s', wc_price($item['total_seller_amount'], array('currency' => $order->get_currency())));
    }

    /**
     * Checkbox column data.
     *
     * @param array $item column data
     */
    public function column_discount($item)
    {
        $order = wc_get_order($item['order_id']);
        if (!empty($item)) {
            $discount = $item['discount'];
            $result = '-';
            $amt = 0;
            if ($discount['seller'] != 0) {
                $result = '<span class="ord-sel-discount">' . __('Seller', 'marketplace') . '</span> ';
                $amt = $discount['seller'];
            } elseif ($discount['admin'] != 0) {
                $result = '<span class="ord-adm-discount">' . __('Admin', 'marketplace') . '</span> ';
                $amt = $discount['admin'];
            }
            if ($amt != 0) {
                $tip = '<p>';
                $tip .= wc_price($amt, array('currency' => $order->get_currency()));
                $tip .= '</p>';

                return sprintf('%s %s', $result, wc_help_tip($tip, true));
            }
        }

        return $result;
    }

    /**
     * Table Data.
     */
    private function table_data()
    {
        global $wpdb;

        $mp_commission = new MP_Commission();

        $data = array();
        $sel_order = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT order_id from {$wpdb->prefix}mporders where seller_id = %d", $this->seller_id));

        if (!empty($sel_order)) {
            foreach ($sel_order as $value) {
                $o_id = $value->order_id;

                $or_status = !empty(wc_get_order($o_id)) ? wc_get_order($o_id)->get_status() : '';

                if ($or_status == 'completed') {
                    $sel_ord_data = $mp_commission->get_seller_final_order_info($o_id, $this->seller_id);

                    $data[] = $sel_ord_data;
                }
            }
        }

        return $data;
    }

    /**
     * Bulk actions on list.
     */
    public function get_bulk_actions()
    {
        $actions = array(
            'pay' => esc_html__('Pay', 'marketplace'),
        );

        return $actions;
    }

    /**
     * Process bulk actions.
     */
    public function process_bulk_action()
    {
        global $commission, $transaction;

        if ($this->current_action() === 'pay') {
            if (isset($_POST['oid'])) {
                if (is_array($_POST['oid']) && !empty($_POST['oid'])) {
                    $order_ids = $_POST['oid'];
                    $t_order_ids = array();
                    $result = '';
                    $t_item_ids = array();
                    $amount = floatval(0);
                    foreach ($order_ids as $ids) {
                        $order_id = explode('-', $ids)[0];
                        $item_id = explode('-', $ids)[1];
                        $t_order_ids[] = $order_id;
                        $t_item_ids[] = $item_id;
                        $paid_status = wc_get_order_item_meta($item_id, '_paid_status');
                        if (!$paid_status) {
                            $result = $commission->update_seller_commission($this->seller_id, $item_id);
                            $amount += $result;
                            wc_update_order_item_meta($item_id, '_paid_status', 'paid');
                        }
                    }
                    if ($amount > 0) {
                        $transaction->generate($this->seller_id, $t_order_ids, $t_item_ids, $amount, '');
                    }
                }
            }
        }
    }
}
$sellerorderlist = new Seller_Order_List();
$sellerorderlist->order_list = $order_list;
$sellerorderlist->seller_id = $seller_id;

echo '<div id="notice-wrapper"></div>';

echo '<form id="seller-order-list" method="post">';

$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED);

$paged = filter_input(INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT);

printf('<input type="hidden" name="page" value="%s" />', $page);

printf('<input type="hidden" name="seller_id" id="seller_id" value="%s" />', $seller_id);

printf('<input type="hidden" name="paged" value="%d" />', $paged);

$sellerorderlist->prepare_items(); // this will prepare the items AND process the bulk actions.

$sellerorderlist->display();

echo '</form>';