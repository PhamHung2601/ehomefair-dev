<?php
/**
 * This file handles list for seller orders.
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Seller order list table.
 */
class Seller_Transaction_List extends WP_List_Table
{
    /**
     * Seller Transaction List.
     *
     * @var array transaction List
     */
    public $transaction;

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
                'singular' => 'Seller Transaction List',
                'plural' => 'Seller Transactions List',
                'ajax' => false,
            )
        );
    }

    /**
     * Handles all list functions.
     */
    public function prepare_items()
    {
        global $wpdb, $transaction;
        
        $columns = $this->get_columns();

        $sortable = $this->get_sortable_columns();

        $hidden = $this->get_hidden_columns();

        $currentpage = $this->get_pagenum();
        
        $seller_transaction = $transaction->get(intval($this->seller_id), $currentpage);
        
        $data = $this->table_data($seller_transaction['transaction']);

        $totalitems = $seller_transaction['total_count'];

        $user = get_current_user_id();

        $screen = get_current_screen();

        // $perpage = $this->get_items_per_page('product_per_page', 20);

        $this->_column_headers = array($columns, $hidden, $sortable);

        // if (empty($per_page) || $per_page < 1) {
        //     $per_page = $screen->get_option('per_page', 'default');
        // }

        $this->usort($data, 'usort_reorder');

        $totalpages = ceil($totalitems / 10);

        // $data = array_slice($data, (($currentpage - 1) * $perpage), $perpage);

        $this->set_pagination_args(
            array(
                'total_items' => $totalitems,
                'total_pages' => $totalpages,
                'per_page' => 10,
            )
        );

        $this->items = $data;
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

        $result = strcmp($a[$orderby], $b[$orderby]); // Determine sort order.

        return ('asc' === $order) ? $result : -$result; // Send final sort direction to usort.
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
            'transaction_id' => __('Transaction Id', 'marketplace'),
            'order_id' => __('Order Id', 'marketplace'),
            'amount' => __('Amount', 'marketplace'),
            'type' => __('Type', 'marketplace'),
            'method' => __('Method', 'marketplace'),
            'created_on' => __('Created On', 'marketplace'),
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
        case 'transaction_id':
        case 'order_id':
        case 'amount':
        case 'type':
        case 'method':
        case 'created_on':
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
            'transaction_id' => array('transaction_id', true),
            'order_id' => array('order_id', true),
            'created_on' => array('created_on', true),
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
     * Table Data.
     */
    private function table_data($sel_transaction)
    {
    
        $data = array();
        $i = 0;
        $transaction_id = array();
        $order_id = array();
        $amount = array();
        $type = array();
        $method = array();
        $created_on = array();
        
        if (!empty($sel_transaction)) {
            foreach ($sel_transaction as $transaction) {
                $order = wc_get_order($transaction['order_id']);
                if ($order) {
                    $amount[] = wc_price($transaction['amount'], array('currency' => $order->get_currency() ));
                } else {
                    
                    $amount[] = wc_price($transaction['amount'], array('currency' => get_woocommerce_currency() ));
                }
                $id[] = $transaction['id'];
                $transaction_id[] = $transaction['transaction_id'];
                $order_id[] = $transaction['order_id'];
                $type[] = $transaction['type'];
                $method[] = $transaction['method'];
                $created_on[] = get_date_from_gmt($transaction['transaction_date']);
                $data[] = array(
                    'id' => $id[$i],
                    'transaction_id' => $transaction_id[$i],
                    'order_id' => $order_id[$i],
                    'amount' => $amount[$i],
                    'type' => $type[$i],
                    'method' => $method[$i],
                    'created_on' => $created_on[$i],
                );
                ++$i;
            }
        }
        
        return $data;
    }

    /**
     * Column actions.
     *
     * @param array $item column Array
     */
    public function column_transaction_id($item)
    {
        $actions = array(
            'view' => sprintf('<a href="admin.php?page=sellers&id=%s&action=set&tab=transactions&sid=%s">%s</a>', $item['id'], $this->seller_id, esc_html__('View', 'marketplace')),
        );

        return sprintf('%1$s %2$s', $item['transaction_id'], $this->row_actions($actions));
    }
}
$sellertransactionlist = new Seller_Transaction_List();
// $sellertransactionlist->transaction = $seller_transaction;
$sellertransactionlist->seller_id = $seller_id;

echo '<form id="seller-order-list" method="post">';

$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED);

$paged = filter_input(INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT);

printf('<input type="hidden" name="page" value="%s" />', $page);

printf('<input type="hidden" name="paged" value="%d" />', $paged);

$sellertransactionlist->prepare_items(); // this will prepare the items AND process the bulk actions.

$sellertransactionlist->display();

echo '</form>';
