<?php

if (! class_exists('WP_List_Table') ) {
    include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Seller_List_Table extends WP_List_Table
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
            if (wp_create_nonce('del_mp_nonceuser_' . $_GET['user']) == $_GET['_wpnonce'] ) {

                $nonce_del = wp_create_nonce('confirm_yes_mp_nonceuser_' . $_GET['user']);

                $user_id = $_GET['user'];

                $user = get_user_by('id', $user_id);

                $admin_users = get_users(
                    array(
                    'role' => 'administrator',
                    ) 
                );

                if ($user ) :
                    ?>
                <form method="post" action="<?php echo '?page=sellers&action=delete&user=' . $_GET['user'] . '&_wpnonce=' . $nonce_del . ''; ?>">
                    <div class="wrap">
                        <h1 class="wp-heading-inline"><?php echo esc_html__('Delete Seller', 'marketplace'); ?></h1>
                        <div class="mp-seller-delete-page">
                            <p><?php echo esc_html__('You have specified this user for deletion:', 'marketplace'); ?></p>
                            <input type="hidden" value="15" name="users[]" />
                            <h3>
                                <strong><?php echo __('ID ', 'marketplace') . '#' . $user_id; ?>: <?php echo ( get_user_meta($user_id, 'first_name', true) ) ? ucfirst(get_user_meta($user_id, 'first_name', true)) . ' ' . ucfirst(get_user_meta($user_id, 'last_name', true)) : $user->user_nicename; ?>
                                </strong>
                            </h3>
                            <fieldset>
                                <h4><legend><?php echo esc_html__('What should be done with content owned by this user?', 'marketplace'); ?></legend></h4>
                                <ul style="list-style:none;">
                                    <li>
                                        <label>
                                                <input type="radio" value="delete" name="delete_option" id="delete_option0" checked=""><?php echo esc_html__('Delete all content.', 'marketplace'); ?>
                                        </label>
                                    </li>
                                    <li>
                                        <input type="radio" value="reassign" name="delete_option" id="delete_option1">
                                        <label for="delete_option1"><?php echo esc_html__('Attribute all content to - ', 'marketplace'); ?></label>
                                        <select class="" id="reassign_user" name="reassign_user" style="min-width:250px;">
                            <?php
                            foreach ( $admin_users as $key => $value ) {
                                echo '<option value="' . $value->ID . '">' . $value->user_nicename . '</option>';
                            }

                            $sql_query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}mpsellerinfo WHERE seller_value='seller' AND user_id != %d", $user_id);

                            $seller_list = $wpdb->get_results($sql_query);

                            foreach ( $seller_list as $key => $value ) {
                                $seller = get_user_by('id', $value->user_id);
                                ?>
                                                <option value="<?php echo $seller->ID; ?>"><?php echo ( get_user_meta($seller->ID, 'first_name', true) ) ? ucfirst(get_user_meta($seller->ID, 'first_name', true)) . ' ' . ucfirst(get_user_meta($seller->ID, 'last_name', true)) : $seller->user_nicename; ?>
                                                </option>
                                <?php
                            }
                            ?>
                                        </select>
                                    </li>
                                </ul>
                            </fieldset>

                            <?php wp_nonce_field('marketplace-delete-seller'); ?>

                            <p class="submit">
                                <input type="submit" class="button-primary" value="Confirm Deletion" name="submit">
                            </p>
                        </div>
                    </div>
                </form>

                        <?php
                else :
                    echo '<div class="wrap"><div id="message" class="error notice is-dismissible"><p>' . esc_html__('Invalid User!', 'marketplace') . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="' . esc_url(admin_url('admin.php?page=sellers')) . '">' . esc_html__('Back', 'marketplace') . '</a></p></div></div>';
                endif;
                exit;
            }

            if (( wp_create_nonce('confirm_yes_mp_nonceuser_' . $_GET['user']) == $_GET['_wpnonce'] ) && is_admin() && ! empty($_POST['_wpnonce']) ) {
                wp_verify_nonce($_POST['_wpnonce'], 'marketplace-delete-seller');
                $user_id       = $_GET['user'];
                $delete_option = $_POST['delete_option'];

                if ($delete_option == 'reassign' ) {
                     $reassign_user_id = $_POST['reassign_user'];
                     wp_delete_user($user_id, $reassign_user_id);
                } else {
                    wp_delete_user($user_id);
                }
                wp_redirect('?page=sellers');
                exit;
            }
        }
    }


    function getdata()
    {

        global $wpdb;

        $table_name1 = $wpdb->usermeta;

        $table_name2 = $wpdb->prefix . 'mpsellerinfo';

        $fresult = array();
        if (isset($_GET['s']) ) {

            $m_seller = $_GET['s'];

            $query = "Select Distinct A.user_id,A.seller_value from $table_name2 as A join $wpdb->users user on user.ID=A.user_id where (user.user_login LIKE '" . $m_seller . "%' or user.user_login LIKE '%" . $m_seller . "' or user.user_login LIKE '%" . $m_seller . "%')";

        } else {

            $query = "Select Distinct A.user_id,A.seller_value from $table_name2 as A join $wpdb->users user on user.ID=A.user_id ORDER BY user.ID DESC";

        }

        $user_ids = $wpdb->get_results($query, ARRAY_A);

        foreach ( $user_ids as $id ) {

            $user_result[] = get_user_meta($id['user_id']);

            $user_data[] = get_userdata($id['user_id']);

            $user_edit_link[] = get_edit_user_link($id['user_id']);

            $user_products[] = $wpdb->get_results("SELECT COUNT( ID ) AS product FROM {$wpdb->prefix}posts WHERE post_author='" . $id['user_id'] . "' and post_type='product'");

        }

        $i = 0;

        if (! empty($user_result) ) {

            foreach ( $user_result as $result ) {

                $fresult[ $i ]['id'] = $user_data[ $i ]->data->ID;

                $fresult[ $i ]['nickname'] = '<strong><a href="' . $user_edit_link[ $i ] . '">' . $user_data[ $i ]->data->user_login . '</a></strong>';

                $fresult[ $i ]['name'] = $result['first_name'][0] . '&nbsp;' . $result['last_name'][0];

                $fresult[ $i ]['user_email'] = $user_data[ $i ]->data->user_email;

                $fresult[ $i ]['user_posts'] = $user_products[$i][0]->product;

                if ($user_ids[ $i ]['seller_value'] == 'seller' ) {
                    $user_ids[ $i ]['seller_value'] = '1';

                    $fresult[ $i ]['seller_approval'] = "<a href='javascript:void(0);' class='wk_seller_app_button active' id='wk_seller_approval_mp" . $user_ids[ $i ]['user_id'] . '_mp' . $user_ids[ $i ]['seller_value'] . "'>" . __('Disapprove', 'marketplace') . '</a>';

                } else {

                    $user_ids[ $i ]['seller_value']   = '0';
                    $fresult[ $i ]['seller_approval'] = "<a href='javascript:void(0);' class='wk_seller_app_button' id='wk_seller_approval_mp" . $user_ids[ $i ]['user_id'] . '_mp' . $user_ids[ $i ]['seller_value'] . "'>" . __('Approve', 'marketplace') . '</a>';

                }

                $fresult[ $i ]['seller_action'] = '<a href="' . esc_url(admin_url('admin.php?page=sellers&action=set&tab=details&sid=' . $user_data[ $i ]->data->ID)) . '" class="button button-primary">' . __('Manage', 'marketplace') . '</a>';

                $i++;

            }
        }
        return $fresult;
    }

    function column_default( $item, $column_name )
    {
        switch ( $column_name ) {
        case 'nickname':
        case 'name':
        case 'user_email':
        case 'user_posts':
        case 'seller_approval':
        case 'seller_action':
            return $item[ $column_name ];
        default:
            return print_r($item, true); // Show the whole array for troubleshooting purposes.
        }
    }

    function get_columns()
    {
        $columns = array(
        'cb'              => '<input type="checkbox" />',
        'nickname'        => __('UserName', 'marketplace'),
        'name'            => __('Name', 'marketplace'),
        'user_email'      => __('E-mail', 'marketplace'),
        'user_posts'      => __('Products', 'marketplace'),
        'seller_approval' => __('Seller Access', 'marketplace'),
        'seller_action'   => __('Action', 'marketplace'),
        );

        $columns = apply_filters('mkt_sellerlist_add_columns', $columns);

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

        $this->process_bulk_action();
        $seller_data = $this->getdata();
        $item_data   = $this->getdata();
        if (! empty($seller_data) ) {
            usort($item_data, array( $this, 'usort_reorder' ));
        }
        $per_page     = get_option('posts_per_page');
        $current_page = $this->get_pagenum();
        $total_items  = count($this->getdata());
        if (! empty($seller_data) ) {
            $item_data = array_slice($this->getdata(), ( ( $current_page - 1 ) * $per_page ), $per_page);
        }
        $this->set_pagination_args(
            array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            ) 
        );
        $this->items = $item_data;
    }

    public function process_bulk_action()
    {
        // security check!
        if (isset($_GET['_wpnonce']) && ! empty($_GET['_wpnonce']) ) {
            $nonce  = filter_input(INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];
            if (! wp_verify_nonce($nonce, $action) ) {
                wp_die('Nope! Security check failed!');
            }
        }
        $action = $this->current_action();

        switch ( $action ) {
        case 'delete':
            $user_id=$_GET['user'];
            foreach ( $user_id as $u_id ) {
                if ($u_id == 1 ) {
                    continue;
                }
                else {
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
            echo esc_html__('User Deleted', 'marketplace');
            echo "<a href='?page=sellers'>" . esc_html__('Go Back To seller List', 'marketplace') . '</a>';
            break;
        default:
            break;
        }
    }


    function get_bulk_actions()
    {
        $actions = array(
        'delete' => __('Delete', 'marketplace'),
        );
        return $actions;
    }


    function column_cb( $item )
    {
        return sprintf('<input type="checkbox" id="user_%s"name="user[]" value="%s" />', $item['id'], $item['id']);
    }

    // action start here.
    function column_nickname( $item )
    {
        $nonce   = wp_create_nonce('remove-users');
        $actions = array(
        'edit'   => sprintf('<a href="' . get_edit_user_link($item['id']) . '">' . __('Edit', 'marketplace') . '</a>'),
        'delete' => sprintf('<a class="submitdelete" href="?page=sellers&action=delete&user=%s&_wpnonce=%s">Delete</a>', $item['id'], wp_create_nonce('del_mp_nonceuser_' . $item['id'])),
        );
        return sprintf('%1$s %2$s', $item['nickname'], $this->row_actions($actions));
    }

    // shorting on title click.
    function get_sortable_columns()
    {
        $sortable_columns = array(
        'nickname'   => array( 'nickname', false ),
        'name'       => array( 'name', false ),
        'user_email' => array( 'user_email', false ),
        );
        return $sortable_columns;
    }


    function usort_reorder( $a, $b )
    {
        $orderby = ( ! empty($_GET['orderby']) ) ? $_GET['orderby'] : 'name';
        $order   = ( ! empty($_GET['order']) ) ? $_GET['order'] : 'asc';
        $result  = strcmp($a[ $orderby ], $b[ $orderby ]);
        return ( $order === 'asc' ) ? $result : -$result;
    }


    function test_table_set_option( $status, $option, $value )
    {
        return $value;
    }


    function register_mp_menu_page()
    {
        add_menu_page('Mp menu', __('Seller List', 'marketplace'), '', 'seller list page', 'my_custom_menu_page', plugins_url('marketplace/assets/images/tick.png'), 6);
    }


    function update_deleted_user()
    {
        global $wpdb;
        $wpdb->get_results("Delete from {$wpdb->prefix}mpsellerinfo where user_id not in(select ID from $wpdb->users)");
    }


    function add_options()
    {
        global $ListTable;
        $option = 'per_page';
        $args   = array(
        'label'   => 'Seller',
        'default' => 10,
        'option'  => 'seller_per_page',
        );
        add_screen_option($option, $args);
        $ListTable = new Seller_List_Table();
    }


    // seller search.
    function mp_search_seller()
    {
        ?>
        <form action="<?php $this->prepare_items(); ?>"method="GET">
        <?php $this->search_box(__('search', 'marketplace'), 'mp_search_seller'); ?>
        </form>
        <?php
    }
}
$sellerListTable = new Seller_List_Table();

$sellerListTable->update_deleted_user();

$sellerListTable->delete_mp_seller();

add_filter('set-screen-option', 'test_table_set_option', 10, 3);

printf('<div class="wrap" id="seller-list-table"><h2>%s</h2>', esc_html__('Seller List', 'marketplace'));

echo '<form id="seller-list-table-form" method="get">';

$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED);

$paged = filter_input(INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT);

printf('<input type="hidden" name="page" value="%s" />', $page);

printf('<input type="hidden" name="paged" value="%d" />', $paged);

$sellerListTable->prepare_items(); // this will prepare the items AND process the bulk actions.

$sellerListTable->mp_search_seller();

$sellerListTable->display();

echo '</form>';

echo '</div>';
