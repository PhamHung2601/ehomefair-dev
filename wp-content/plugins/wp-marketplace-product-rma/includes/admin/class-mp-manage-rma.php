<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if (!class_exists('MP_Manage_Rma')) {
    class MP_Manage_Rma extends WP_List_Table
    {
        public function __construct()
        {
            parent::__construct(array(
                'singular' => 'RMA',
                'plural' => 'RMAs',
                'ajax' => false,
            ));
        }

        public function prepare_items()
        {
            $columns = $this->get_columns();

            $sortable = $this->get_sortable_columns();

            $hidden = $this->get_hidden_columns();

            $this->process_bulk_action();

            $data = $this->table_data();

            $totalitems = count($data);

            $user = get_current_user_id();

            $screen = get_current_screen();

            $perpage = $this->get_items_per_page('product_per_page', 20);

            $this->_column_headers = array($columns, $hidden, $sortable);

            if (empty($per_page) || $per_page < 1) {
                $per_page = $screen->get_option('per_page', 'default');
            }

            function usort_reorder($a, $b)
            {
                $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'order_id'; //If no sort, default to title

                $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc

                $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order

                return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
            }

            usort($data, 'usort_reorder');

            $totalpages = ceil($totalitems / $perpage);

            $currentPage = $this->get_pagenum();

            $data = array_slice($data, (($currentPage - 1) * $perpage), $perpage);

            $this->set_pagination_args(array(
                'total_items' => $totalitems,

                'total_pages' => $totalpages,

                'per_page' => $perpage,
            ));

            $this->items = $data;
        }

        /**
         * Define the columns that are going to be used in the table.
         *
         * @return array $columns, the array of columns to use with the table
         */
        public function get_columns()
        {
            return $columns = array(
                'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
                'order_id' => __('Order Id', 'marketplace-rma'),
                'cust_name' => __('Customer Name', 'marketplace-rma'),
                'products' => __('Products', 'marketplace-rma'),
                'reason' => __('Reason', 'marketplace-rma'),
                'rma_status' => __('RMA Status', 'marketplace-rma'),
                'delivery_status' => __('Delivery Status', 'marketplace-rma'),
                'date' => __('Date', 'marketplace-rma'),
            );
        }

        public function column_rma_status($item)
        {
            $rma_stat = array(
                'processing' => esc_html__('Approve', 'marketplace-rma'),
                'declined' => esc_html__('Decline', 'marketplace-rma'),
                'solved' => esc_html__('Solved', 'marketplace-rma'),
                'pending' => esc_html__('Pending', 'marketplace-rma'),
                'cancelled' => esc_html__('Cancelled', 'marketplace-rma'),
            );

            $rma_status = !empty( $rma_stat[$item['rma_status']] ) ? $rma_stat[$item['rma_status']] : ( !empty( $item['rma_status'] ) ? $item['rma_status'] : 'N/A' );

            return '<strong class="wk_rma_status_' . $item['rma_status'] . '">' . $rma_status . '</strong>';
        }

        public function column_delivery_status($item)
        {
            $or_stat = array(
                'complete' => __('Complete', 'marketplace-rma'),
                'on-hold' => __('On Hold', 'marketplace-rma'),
                'processing' => __('Processing', 'marketplace-rma'),
                'cancelled' => __('Cancelled', 'marketplace-rma'),
                'failed' => __('Failed', 'marketplace-rma'),
                'refunded' => __('Refunded', 'marketplace-rma'),
                'pending' => __('Pending', 'marketplace-rma'),
            );

            return $or_stat[$item['delivery_status']];
        }

        public function column_default($item, $column_name)
        {
            switch ($column_name) {
                case 'order_id':
                case 'cust_name':
                case 'products':
                case 'reason':
                case 'rma_status':
                case 'delivery_status':
                case 'date':
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
            return $sortable = array(
                'order_id' => array('order_id', true),
            );
        }

        public function get_hidden_columns()
        {
            return array();
        }

        public function column_cb($item)
        {
            return sprintf('<input type="checkbox" id="rma_%s" name="rma[]" value="%s" />', $item['id'], $item['id']);
        }

        public function extra_tablenav( $which ) {

            if( current_user_can('administrator') ) {

                if ( $which == "top" ) {

                    if( !empty( $_REQUEST['rma-select-status'] ) ) {
                        $rma_status = $_REQUEST['rma-select-status'];
                    }
                    else {
                        $rma_status = '';
                    }

                    ?>

                    <div class="alignleft actions bulkactions">

                        <select name="rma-select-status" id="rma-select-status" class="ewc-filter-rma-status" style="min-width:200px;">

                            <option value=""><?php echo __('Select status', 'marketplace-rma'); ?></option>
                            <option value="pending" <?php echo $rma_status == 'pending' ? 'selected' : '' ?>><?php echo __('Pending', 'marketplace-rma'); ?></option>
                            <option value="processing" <?php echo $rma_status == 'processing' ? 'selected' : '' ?>><?php echo __('Processing', 'marketplace-rma'); ?></option>
                            <option value="solved" <?php echo $rma_status == 'solved' ? 'selected' : '' ?>><?php echo __('Solved', 'marketplace-rma'); ?></option>
                            <option value="declined" <?php echo $rma_status == 'declined' ? 'selected' : '' ?>><?php echo __('Declined', 'marketplace-rma'); ?></option>

                        </select>

                        <?php submit_button(__('Select RMA Status', 'marketplace-rma'), 'button', 'select-rma-status', false); ?>

                    </div>

                    <?php
                }
            }
        }

        private function table_data()
        {
            global $wpdb;

            $data = array();

            $table_name = $wpdb->prefix . 'mp_rma_requests';

            $rma_status_filter_query = '';

            if ( isset( $_REQUEST['s'] ) )
            {
                if( !empty( $_REQUEST['rma-select-status'] ) ) {
                    $rma_status = $_REQUEST['rma-select-status'];
                    $rma_status_filter_query = "AND rma_status='$rma_status'";
                }
                $string = $_REQUEST['s'];
                $wk_posts = $wpdb->get_results("Select * from $table_name where order_no like '%$string%' $rma_status_filter_query");
            }
            else
            {
                if( !empty( $_REQUEST['rma-select-status'] ) ) {
                    $rma_status = $_REQUEST['rma-select-status'];
                    $rma_status_filter_query = "WHERE rma_status='$rma_status'";
                }
                $wk_posts = $wpdb->get_results("Select * from $table_name $rma_status_filter_query");
            }

            $i = 0;

            $order_id = array();
            $cust_name = array();
            $products = array();
            $reason = array();
            $rma_status = array();
            $delivery_status = array();
            $date = array();

            foreach ($wk_posts as $key => $value) {
                $product = '';
                $reasons = '';
                $id[] = $value->id;
                $order_id[] = $value->order_no;
                $cust_name[] = get_userdata($value->customer_id)->display_name;
                foreach (maybe_unserialize($value->items)['items'] as $val) {
                    $product .= get_the_title($val) . '<br>';
                }
                $products[] = $product;
                foreach (maybe_unserialize($value->items)['reason'] as $reason_id) {
                    $wk_post = $wpdb->get_results("Select reason from {$wpdb->prefix}mp_rma_reasons where id = '$reason_id'", ARRAY_A);
                    $reasons .= !empty( $wk_post[0]['reason'] ) ? $wk_post[0]['reason'] . '<br>' : '-<br>';
                }
                $reason[] = $reasons;
                $rma_status[] = $value->rma_status;
                $delivery_status[] = $value->order_status;
                $date[] = $value->datetime;
                $data[] = array(
                    'id' => $id[$i],
                    'order_id' => $order_id[$i],
                    'cust_name' => $cust_name[$i],
                    'products' => $products[$i],
                    'reason' => $reason[$i],
                    'rma_status' => $rma_status[$i],
                    'delivery_status' => $delivery_status[$i],
                    'date' => $date[$i],
                );

                ++$i;
            }

            return $data;
        }

        public function get_bulk_actions()
        {
            $actions = array(
                'delete' => __('Delete', 'marketplace-rma'),
            );

            return $actions;
        }

        public function process_bulk_action()
        {
            global $wpdb;

            $table = $wpdb->prefix . 'mp_rma_requests';

            if (isset($_GET['_wpnonce']) && !empty($_GET['_wpnonce'])) {
                $nonce = filter_input(INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING);
                $action = 'bulk-' . $this->_args['plural'];
                if (!wp_verify_nonce($nonce, $action)) {
                    wp_die('Nope! Security check failed!');
                }
            }

            if ($this->current_action() == 'delete') {
                if (is_array($_GET['rma'])) {
                    foreach ($_GET['rma'] as $key => $value) {
                        $wpdb->delete($table, array('id' => $value));
                    }
                } else {
                    $wpdb->delete($table, array('id' => $_GET['rid']));
                }
                ?>
                <div class='notice notice-success is-dismissible'>
                    <p><?php esc_html_e( 'RMA request(s) deleted successfully', 'marketplace-rma' );; ?></p>
                </div>
                <?php
            }
        }

        public function column_order_id($item)
        {
            $actions = array(
                'view' => sprintf('<a href="admin.php?page=marketplace-rma&rid=%s&action=view">%s</a>', $item['id'], __('View', 'marketplace-rma')),

                'delete' => sprintf('<a href="admin.php?page=marketplace-rma&action=delete&rid=%s&_wpnonce=%s" class="delete-rma">%s</a>', $item['id'], wp_create_nonce('bulk-rmas'), __('Delete', 'marketplace-rma')),
            );

            return sprintf('%1$s %2$s', $item['order_id'], $this->row_actions($actions));
        }
    }
}
