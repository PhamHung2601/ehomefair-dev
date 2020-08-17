<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';
}

if (!class_exists('MP_RMA_Reasons')) {
    class MP_RMA_Reasons extends WP_List_Table
    {
        public function __construct()
        {
            parent::__construct(array(
                'singular' => 'RMA Reason',
                'plural' => 'RMA Reasons',
                'ajax' => false,
            ));
        }

        public function prepare_items()
        {
            global $wpdb;

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
                $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'reason'; //If no sort, default to title

                $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc

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
                'reason' => __('Reason', 'marketplace-rma'),
                'status' => __('Status', 'marketplace-rma'),
            );
        }

        public function column_status($item)
        {
            $rma_res_stat = array(
                'enabled' => esc_html__('Enabled', 'marketplace-rma'),
                'disabled' => esc_html__('Disabled', 'marketplace-rma'),
            );

            return $rma_res_stat[$item['status']];
        }

        public function column_default($item, $column_name)
        {
            switch ($column_name) {
                case 'reason':
                case 'status':
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
                'reason' => array('reason', true),
            );
        }

        public function get_hidden_columns()
        {
            return array();
        }

        public function column_cb($item)
        {
            return sprintf('<input type="checkbox" id="reason_%s" name="reason[]" value="%s" />', $item['id'], $item['id']);
        }

        private function table_data()
        {
            global $wpdb;

            $data = array();

            $table_name = $wpdb->prefix.'mp_rma_reasons';

            $user_id = apply_filters('mp_rma_user_id', 'user_id');

            if (isset($_REQUEST['s'])) {
                $string = $_REQUEST['s'];
                $wk_posts = $wpdb->get_results("Select * from $table_name where reason like '%$string%' and user_id = '$user_id'");
            } else {
                $wk_posts = $wpdb->get_results("Select * from $table_name where user_id = '$user_id'");
            }

            $i = 0;

            $reason = array();

            $status = array();

            foreach ($wk_posts as $key => $value) {
                $id[] = $value->id;
                $reason[] = $value->reason;
                $status[] = $value->status;
                $data[] = array(
                    'id' => $id[$i],
                    'reason' => $reason[$i],
                    'status' => $status[$i],
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

            $table = $wpdb->prefix.'mp_rma_reasons';

            if (isset($_GET['_wpnonce']) && !empty($_GET['_wpnonce'])) {
                $nonce = filter_input(INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING);
                $action = 'bulk-'.$this->_args['plural'];
                if (!wp_verify_nonce($nonce, $action)) {
                    wp_die(__('Nope! Security check failed!', 'marketplace-rma'));
                }
            }

            if ($this->current_action() == 'delete') {
                if (is_array($_GET['reason'])) {
                    foreach ($_GET['reason'] as $key => $value) {
                        $wpdb->delete($table, array('id' => $value));
                    }
                } else {
                    $wpdb->delete($table, array('id' => $_GET['reason']));
                }
            }
        }

        public function column_reason($item)
        {
            $actions = array(
                'edit' => sprintf('<a href="admin.php?page=mp-rma-reasons&action=add&rid=%s">%s</a>', $item['id'], __('Edit', 'marketplace-rma')),

                'delete' => sprintf('<a href="admin.php?page=mp-rma-reasons&action=delete&reason=%s&_wpnonce=%s" class="delete-reason">%s</a>', $item['id'], wp_create_nonce('bulk-rmareasons'), __('Delete', 'marketplace-rma')),
            );

            return sprintf('%1$s %2$s', $item['reason'], $this->row_actions($actions));
        }
    }
}
