<?php

if (! class_exists('WP_List_Table') ) {

    include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

}


class Commision_Table extends WP_List_Table
{

    public function __construct()
    {
        parent::__construct(

            array(
            'singular' => 'singular_form',
            'plural'   => 'plural_form',
            'ajax'     => false,
            )
        );
        add_action('admin_menu', array( $this, 'register_mp_menu_page' ));
    }

    function delete_mp_seller()
    {

        global $wpdb;

        if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['user']) && isset($_GET['_wpnonce']) ) {

            if (( wp_create_nonce('confirm_yes_mp_nonceuser_' . $_GET['user']) == $_GET['_wpnonce'] ) ) {

                $user = $_GET['user'];

                $post_id = $wpdb->get_results("select ID from {$wpdb->prefix}posts where post_author='".$user."' ");

                foreach ( $post_id as $id ) {

                    if($wpdb->get_results("delete * from {$wpdb->prefix}postmeta where post_id='".$id->ID."'") ) {

                        wp_delete_post($id->ID);

                    }
                }

                $wpdb->get_results("delete * from $wpdb->usermeta where user_id='". $user . "'");
                wp_delete_user($user);
                echo 'user_deleted';
                echo '<a href="?page=sellers">' . esc_html__('Go Back To seller List', 'marketplace') . '</a>';
                exit;
            }
            if (wp_create_nonce('del_mp_nonceuser_' . $_GET['user']) == $_GET['_wpnonce'] ) {

                $nonce_del = wp_create_nonce('confirm_yes_mp_nonceuser_' . $_GET['user']);
                echo  __('Are you sure you want to delete this user this will delete all product and post of this user click yes to delete', 'marketplace') . " <a class='submitdelete' href='?page=sellers&action=delete&user=" . $_GET['user'] . '&_wpnonce=' . $nonce_del . "'>" . __('Yes', 'marketplace') . '</a> ' . __('click No to go back', 'marketplace') . "<a href='?page=sellers'>" . __('No', 'marketplace') . '</a>';
                exit;
            }
        }
    }


    function getdata()
    {

        global $wpdb;

        $table_name1 = $wpdb->usermeta;

        $table_name2 = $wpdb->prefix . 'mpsellerinfo';

        if(isset($_POST['s']) ) {

            $m_seller = $_POST['s'];

            $query = "Select Distinct A.user_id,A.seller_value from $table_name2 as A join $wpdb->users user on user.ID=A.user_id where (user.user_login LIKE '".$m_seller."%' or user.user_login LIKE '%".$m_seller."' or user.user_login LIKE '%".$m_seller."%')";

        }
        else {

            $query = "Select Distinct A.user_id,A.seller_value from $table_name2 as A join $wpdb->users user on user.ID=A.user_id";

        }

        $user_ids = $wpdb->get_results($query, ARRAY_A);

        $i = 0;

        foreach ( $user_ids as $id ) {

            $user_id = $id['user_id'];

            $user_result = get_user_meta($user_id);

            $user_login = get_user_by('ID', $user_id)->user_login;

            $name = $user_result['first_name'][0] ? $user_result['first_name'][0].' '.$user_result['last_name'][0] : $user_login;

            $sel_name = '<a href="'.get_home_url().'/wp-admin/admin.php?page=sellers&action=set&tab=commission&sid='.$user_id.'">'.$name.'</a>';

            $user_data = get_userdata($user_id);

            $sel_email = $user_data->user_email;

            $com_sel = $wpdb->get_results("select commision_on_seller from {$wpdb->prefix}mpcommision where seller_id = $user_id ");

            if (! empty($com_sel) ) {

                $sel_comm_percent = $com_sel[0]->commision_on_seller;

            }

            $sel_order = $wpdb->get_results(" Select * from {$wpdb->prefix}mporders where seller_id = $user_id ");

            $total     = 0;
            $sel_total = 0;
            $com_total = 0;
            $paid_amt  = 0;
            $ord_arr = array();

            foreach ( $sel_order as  $sno => $ord_sel ) {

                $order = wc_get_order($ord_sel->order_id);

                if (!empty($order) && 'completed' === $order->get_status() ) {

                    $ord_total     = $ord_sel->amount;
                    $ord_sel_total = $ord_sel->seller_amount;
                    $ord_com_total = $ord_sel->admin_amount;

                    if (0 !== $ord_sel->discount_applied ) {

                        $discount_data = $wpdb->get_results("Select * from {$wpdb->prefix}mporders_meta where seller_id = $user_id and order_id = $ord_sel->order_id and meta_key = 'discount_amt' ");

                        if (! empty($discount_data) ) {

                            $ord_sel_total = $ord_sel_total - $ord_sel->discount_applied;

                        } else {

                            $ord_com_total = $ord_com_total - $ord_sel->discount_applied;

                        }
                    }

                    if (! in_array($ord_sel->order_id, $ord_arr, true) ) {

                        $ship_data = $wpdb->get_results("Select meta_value from {$wpdb->prefix}mporders_meta where seller_id = $user_id and order_id = $ord_sel->order_id and meta_key = 'shipping_cost' ");

                        if (! empty($ship_data) ) {

                            $ord_sel_total = $ord_sel_total + $ship_data[0]->meta_value;
                            $ord_total     = $ord_total + $ship_data[0]->meta_value;
                        }

                        $ord_arr[] = $ord_sel->order_id;
                    }

                    $total     = $total + $ord_total;
                    $sel_total = $sel_total + $ord_sel_total;
                    $com_total = $com_total + $ord_com_total;

                    $pay_data = $wpdb->get_results("Select meta_value from {$wpdb->prefix}mporders_meta where seller_id = $user_id and order_id = $ord_sel->order_id and meta_key = 'paid_status' ");

                    if (! empty($pay_data) ) {

                        $paid_amt = $paid_amt + $ord_sel_total;

                    }
                }
            }

            $commision_display = $wpdb->get_results("select * from {$wpdb->prefix}mpcommision where seller_id= $user_id ");

            $cur_symbol = get_woocommerce_currency_symbol(get_option('woocommerce_currency'));

            $fresult[ $i ]['seller_id'] = $user_id;

            $fresult[ $i ]['seller_name'] = $sel_name;

            $fresult[ $i ]['seller_email'] = $sel_email;

            $fresult[ $i ]['commision'] = $sel_comm_percent . ' %';

            $fresult[ $i ]['total_sales'] = $total . ' ' . $cur_symbol;

            $fresult[ $i ]['comm_amount'] = $com_total . ' ' . $cur_symbol;

            $fresult[ $i ]['recive_amount'] = $paid_amt . ' ' . $cur_symbol;

            $fresult[ $i ]['ammount_remain'] = ( $sel_total - $paid_amt ) . ' ' . $cur_symbol;

            $fresult[ $i ]['last_pay_am'] = $commision_display[0]->last_paid_ammount . ' ' . $cur_symbol;

            $i++;
        }

        if (empty($fresult) ) {
            $fresult = '';
        }

        return $fresult;

    }



    function column_default( $item, $column_name )
    {

        switch ( $column_name ) {
        case 'seller_name':
        case 'seller_email':
        case 'commision':
        case 'total_sales':
        case 'comm_amount':
        case 'recive_amount':
        case 'ammount_remain':
        case 'last_pay_am':
            return $item[ $column_name ];
        default:
            return print_r($item, true); // Show the whole array for troubleshooting purposes.

        }
    }

    public function get_columns()
    {

        $columns = array(

        'cb'             => '<input type="checkbox" />',

        'seller_name'    => 'Seller Name',

        'seller_email'   => 'Seller Email',

        'commision'      => 'Commision %',

        'total_sales'    => 'Total Sales',

        'comm_amount'    => 'Commision Amount',

        'recive_amount'  => 'Paid Amount',

        'ammount_remain' => 'Amount Remain',

        'last_pay_am'    => 'Last Pay Amount',

        );

        return $columns;

    }



    function prepare_items()
    {

        $columns = $this->get_columns();

        $hidden = array();

        $found_data = array();

        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array( $columns, $hidden, $sortable );

        $this->_column_headers = $this->get_column_info();

        $seller_data = $this->getdata();

        if (! empty($seller_data) ) {

            usort($seller_data, array( $this, 'usort_reorder' ));

        }

        $per_page = get_option('posts_per_page');

        $current_page = $this->get_pagenum();

        $total_items = count($seller_data);

        if (! empty($seller_data) ) {

            $found_data = array_slice($seller_data, ( ( $current_page - 1 ) * $per_page ), $per_page);

        }

        $this->set_pagination_args(
            array(

            'total_items' => $total_items,

            'per_page'    => $per_page,

            ) 
        );

        $this->items = $found_data;

    }

    public function process_bulk_action()
    {

        global $wpdb;

        if (isset($_GET['_wpnonce']) && ! empty($_GET['_wpnonce']) ) {

            $_POST['_wpnonce'];

            $nonce  = filter_input(INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING);

            $action = 'bulk-' . $this->_args['plural'];

            if (! wp_verify_nonce($nonce, $action) ) {
                wp_die('Nope! Security check failed!');
            }
        }

        $action = $this->current_action();

        switch ( $action ) {

        case 'delete':
            $user_id = $_GET['user'];

            foreach ( $user_id as $u_id ) {

                if ($u_id == 1 ) {
                    continue;
                } else {

                    $post_id = $wpdb->get_results("select ID from {$wpdb->prefix}posts where post_author='" . $u_id . "'");

                    foreach ( $post_id as $id ) {

                        if ($wpdb->get_results("delete * from {$wpdb->prefix}postmeta where post_id='" . $id->ID . "'") ) {

                             wp_delete_post($id->ID);

                        }
                    }

                    $wpdb->get_results("delete * from $wpdb->usermeta where user_id='" . $u_id . "'");

                    wp_delete_user($u_id);

                }
            }

            echo 'user_deleted';

            echo "<a href='?page=sellers'>" . __( 'Go Back To seller List', 'marketplace' ) . "</a>";

            break;
        default:
            break;
        }
    }

    function column_cb( $item )
    {
        if (isset($item['seller_id']) ) {
            return sprintf('<input type="checkbox" id="user_%s"name="user[]" value="%s" />', $item['seller_id'], $item['seller_id']);
        } else {
            return sprintf('<input type="checkbox" id=""name="user[]" value="" />');
        }
    }


    function column_nickname( $item )
    {

        $nonce = wp_create_nonce('remove-users');

        $actions = array(
        'edit'   => sprintf('<a href="' . get_edit_user_link($item['id']) . '">Edit</a>'),
        'delete' => sprintf('<a class="submitdelete" href="?page=sellers&action=delete&user=%s&_wpnonce=%s">Delete</a>', $item['id'], wp_create_nonce('del_mp_nonceuser_' . $item['id'])),
        );
        return sprintf('%1$s %2$s', $item['nickname'], $this->row_actions($actions));

    }

    public function get_sortable_columns()
    {

        $sortable_columns = array(

        'nickname'   => array( 'nickname', false ),

        'name'       => array( 'name', false ),

        'user_email' => array( 'user_email', false ),

        );

        return $sortable_columns;

    }

    public function usort_reorder( $a, $b )
    {

        $orderby = ( ! empty($_GET['orderby']) ) ? $_GET['orderby'] : 'name';

        $order = ( ! empty($_GET['order']) ) ? $_GET['order'] : 'asc';

        $result = '';

        if (isset($a[ $orderby ]) ) {

            $result = strcmp($a[ $orderby ], $b[ $orderby ]);
        }

        return ( 'asc' === $order ) ? $result : -$result;

    }

    public function test_table_set_option( $status, $option, $value )
    {

        return $value;

    }

    public function register_mp_menu_page()
    {

        add_menu_page('Mp menu', 'Seller List', '', 'seller list page', 'my_custom_menu_page', plugins_url('marketplace/assets/images/tick.png'), 6);

    }

    public function update_deleted_user()
    {

        global $wpdb;

        $wpdb->get_results("Delete from {$wpdb->prefix}mpsellerinfo where user_id not in(select ID from $wpdb->users)");

    }

    public function add_options()
    {

        global $ListTable;

        $option = 'per_page';

        $args = array(

        'label'   => 'Seller',

        'default' => 10,

        'option'  => 'seller_per_page',

        );

        add_screen_option($option, $args);

        $ListTable = new Seller_List_Table();

    }

    //seller search.
    public function mp_search_seller()
    {
        ?>

        <form action="<?php $this->prepare_items(); ?>"method="post">

        <input type="hidden" name="page" value="my_list_test" />

        <?php $this->search_box('search', 'mp_search_seller'); ?>

        </form>

        <?php

    }

    //seller search end
    public function mp_get_total_order_amount( $sel_id )
    {

        global $wpdb;

        $postid = Commision_Table::getOrderId($sel_id);

        if (! empty($postid) ) {
            $sql = "select sum(meta_value) AS 'total_order_amount' from {$wpdb->prefix}woocommerce_order_itemmeta where meta_key='_line_total' and order_item_id in(" . $postid . ')';

            $total_value = $wpdb->get_var($sql);

        } else {
            $total_value=0;
        }

        return $total_value;

    }

    public function getOrderId( $sel_id )
    {

        global $wpdb;

        $sql = " SELECT DISTINCT woi.order_item_id

			FROM {$wpdb->prefix}woocommerce_order_itemmeta woi

			JOIN {$wpdb->prefix}woocommerce_order_items woitems ON woitems.order_item_id = woi.order_item_id

			JOIN {$wpdb->prefix}posts post ON woi.meta_value = post.ID

			WHERE ( woi.meta_key ='_product_id' OR woi.meta_key ='_variation_id' )

			AND post.ID = woi.meta_value

			AND post.post_author ='" . $sel_id . "'

			";

        $result = $wpdb->get_results($sql);

        $ID = array();

        foreach ( $result as $res ) {

            $ID[] = $res->order_item_id;

        }

        return implode(',', $ID);

    }


    function seller_commission()
    {

        global $wpdb;
        $com_on_seller=get_option('wkmpcom_minimum_com_onseller');
        $seller_id=$wpdb->get_results("select Distinct user_id from {$wpdb->prefix}mpsellerinfo");
        foreach($seller_id as $id)
        {
            $sel_in_commition=$wpdb->get_results("select Distinct seller_id from {$wpdb->prefix}mpcommision");
            $seller_list=array();
            foreach($sel_in_commition as $sellid)
            {
                $seller_list[]=$sellid->seller_id;
            }
            if(!in_array($id->user_id, $seller_list)) {
                $wpdb->insert("{$wpdb->prefix}mpcommision", array('id'=>'','seller_id'=>$id->user_id,'commision_on_seller'=>$com_on_seller,'admin_amount'=>0,'seller_total_ammount'=>0,'paid_amount'=>0,'last_paid_ammount'=>0,'last_com_on_total'=>0));
            }
        }
    }


    function update_seller_ammount()
    {
        global $wpdb;
        $com_on_seller=get_option('wkmpcom_minimum_com_onseller');
        $seller_id=$wpdb->get_results("select Distinct user_id from {$wpdb->prefix}mpsellerinfo");
        foreach($seller_id as $id)
        {
            $total_sales=Commision_Table::mp_get_total_order_amount($id->user_id);
            if($total_sales=='') {
                $total_sales=0;
            }
            $remain_update=$wpdb->get_results("select * from {$wpdb->prefix}mpcommision where seller_id='".$id->user_id."'");
            $money=$total_sales-$remain_update[0]->last_com_on_total;
            $comm=$remain_update[0]->commision_on_seller ? $remain_update[0]->commision_on_seller :0;
            $admin_com=$money*($comm/100);// admin commission
            $admin_money=$admin_com+$remain_update[0]->admin_amount;
            $seller_money=($money-$admin_com)+$remain_update[0]->seller_total_ammount;

            $wpdb->get_results("update {$wpdb->prefix}mpcommision set admin_amount='".$admin_money."',seller_total_ammount='".$seller_money."',last_com_on_total='".$total_sales."'where seller_id='".$id->user_id."'");
        }

    }

    function update_paypal_payment()
    {
        global $wpdb;
        if(isset($_GET['sid'])&& $_POST['payment_status']=='Completed') {
            $id=$_GET['sid'];
            $pay=$_POST['payment_gross'];
            $query = "select * from {$wpdb->prefix}mpcommision where seller_id=$id";
            $seller_data = $wpdb->get_results($query);
            $paid_ammount=$seller_data[0]->paid_amount+$pay;
            $seller_total_ammount=$seller_data[0]->seller_total_ammount-$pay;
            $last_paid_ammount=$pay;
            $seller_money=$seller_data[0]->last_com_on_total-$seller_data[0]->admin_amount;
            $remain_ammount=$seller_money-$paid_ammount;
            $wpdb->get_results("update {$wpdb->prefix}mpcommision set paid_amount='".$paid_ammount."',seller_total_ammount='".$seller_total_ammount."',seller_total_ammount='".$remain_ammount."',last_paid_ammount='".$last_paid_ammount."'where seller_id='".$id."'");
            echo "<div id='wk_payment_success'>";
            echo __("Your payment is successfull", "marketplace");
            echo "</div>";
        }
    }


}


$CommisionTable = new Commision_Table();

$CommisionTable->update_deleted_user();

$CommisionTable->seller_commission();

$CommisionTable->delete_mp_seller();

$CommisionTable->mp_search_seller();

$CommisionTable->update_paypal_payment();

add_filter('set-screen-option', 'test_table_set_option', 10, 3);

printf('<div class="wrap" id="seller-list-table"><h2>%s</h2>', __('Commision List', 'marketplace'));

echo '<form id="seller-list-table-form" method="get">';

$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED);

$paged = filter_input(INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT);

printf('<input type="hidden" name="page" value="%s" />', esc_html($page));

printf('<input type="hidden" name="paged" value="%d" />', esc_html($paged));

$CommisionTable->prepare_items(); // this will prepare the items AND process the bulk actions.

$CommisionTable->display();

echo '</form>';
echo '</div>';

?>
<div id="com-pay-ammount" style="display:none;">
</div>
