<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Product_List_Table extends WP_List_Table
{
    public function __construct()
    {
        parent::__construct(
            array(
                'singular' => 'singular_form',
                'plural' => 'plural_form',
                'ajax' => false,
            )
        );
    }

    public function extra_tablenav($which)
    {
        global $wpdb;
        $nonce = wp_create_nonce();

        if ($which == 'top') {
            $cr_id = get_current_user_id();
            ?>

            <div class="alignleft actions bulkactions">

                <select name="check-pro" class="ewc-filter-cat">

                    <option value=""><?php echo esc_html__('Filter by Product', 'marketplace'); ?></option>

                    <option value="assign"><?php echo esc_html__('Assigned', 'marketplace'); ?></option>

                    <option value="<?php echo esc_attr($cr_id); ?>"><?php echo esc_html__('UnAssigned', 'marketplace'); ?></option>

                </select>

                <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($nonce); ?>">
                <?php

            submit_button(esc_html__('Change', 'marketplace'), 'button', 'changeit', false);?>
            </div>

            <div class="alignleft actions bulkactions">

                <select name="mp-assign-product" id="mp-product-seller-select-list" class="ewc-filter-cat" style="min-width:200px;">
                    <option value=""><?php echo esc_html__('Select Seller', 'marketplace'); ?></option>
                    <?php
global $wpdb;

            $users = $wpdb->users;

            $admin_users = get_users('role=administrator');

            foreach ($admin_users as $key) {
                ?>
                        <option value="<?php echo $key->ID; ?>"><?php echo (get_user_meta($key->ID, 'first_name', true)) ? get_user_meta($key->ID, 'first_name', true) : $key->user_nicename; ?></option>
                    <?php
}

            $sql = "SELECT user_id from {$wpdb->prefix}mpsellerinfo where seller_value = 'seller'";

            $result = $wpdb->get_results($sql);

            if ($result):
                foreach ($result as $key) {
                    $username = "SELECT user_nicename FROM $users WHERE ID = $key->user_id ";

                    $name = $wpdb->get_var($username);?>
	                            <option value="<?php echo $key->user_id; ?>"><?php echo (get_user_meta($key->user_id, 'first_name', true)) ? get_user_meta($key->user_id, 'first_name', true) : $name; ?></option>
	                        <?php
    }
            endif;?>
                </select>

                <input type="hidden" name="product-assign_nonce" value="<?php echo $nonce; ?>">

                <?php submit_button(esc_html__('Assign', 'marketplace'), 'button', 'mp-assign-product-seller', false, array('data-alert-msg' => esc_html__('Are you sure you want to do this?', 'marketplace')));?>

            </div>

            <div class="alignleft actions bulkactions">

                <select name="changeSeller" class="ewc-filter-cat">

                    <option value=""><?php echo esc_html__('Filter by Seller', 'marketplace'); ?></option>
                    <option value="1"><?php echo (get_user_meta(1, 'first_name', true)) ? get_user_meta(1, 'first_name', true) : 'Admin'; ?></option>
                    <?php

            $result = $wpdb->get_results($wpdb->prepare("SELECT user_id from {$wpdb->prefix}mpsellerinfo where seller_value = %s", 'seller'));

            if ($result):
                foreach ($result as $key) {
                    $name = $wpdb->get_var($wpdb->prepare("SELECT user_nicename FROM $users WHERE ID = %d ", $key->user_id));?>
	                            <option value="<?php echo esc_attr($key->user_id); ?>"><?php echo (get_user_meta($key->user_id, 'first_name', true)) ? get_user_meta($key->user_id, 'first_name', true) : $name; ?></option>
	                        <?php
    }
            endif;?>

                </select>

                <input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>">
                <?php

            submit_button(esc_html__('Change', 'marketplace'), 'button', 'changeBySeller', false);?>
            </div>

            <?php
}
    }

    public function getdata($perpage)
    {
        global $wpdb;

        if (isset($_GET['paged'])) {
            $paged = $_GET['paged'];
        } else {
            $paged = 1;
        }

        $offset = ($paged - 1) * $perpage;

        $fresult = array();

        if (isset($_POST['mp-assign-product-seller'])) {

            if (isset($_POST['product']) && !empty($_POST['product']) && !empty($_POST['mp-assign-product'])) {
                $seller_id = $_POST['mp-assign-product'];

                if (is_array($_POST['product'])) {
                    foreach ($_POST['product'] as $key => $value) {
                        $arg = array(
                            'ID' => intval($value),
                            'post_author' => $seller_id,
                        );

                        wp_update_post($arg);

                        $args = array(
                            'numberposts' => -1,
                            'order' => 'ASC',
                            'post_parent' => intval($value),
                            'post_type' => 'product_variation',
                        );

                        $variations = get_children($args);

                        if ($variations) {
                            foreach ($variations as $k => $val) {
                                $arg = array(
                                    'ID' => $val->ID,
                                    'post_author' => $seller_id,
                                );
                                wp_update_post($arg);
                            }
                        }
                    }?>
                    <div id="message" class="updated notice is-dismissible">
                        <p><?php echo esc_html__('Seller updated for selected product(s).', 'marketplace'); ?></p>
                    </div>
                    <?php
}
            } else {
                ?>
                <div id="message" class="error notice is-dismissible">
                    <p><?php echo esc_html__('Please select product and seller both.', 'marketplace'); ?></p>
                </div>
                <?php
}
        }
        if (isset($_POST['changeBySeller']) && isset($_POST['changeSeller']) && $_POST['changeSeller']) {

            $query = "SELECT post.ID as post_id from {$wpdb->prefix}posts as post where post.post_type='product' and (post.post_status='publish' or post.post_status='draft') and post.post_author = '$_POST[changeSeller]'";

            $count_query = "SELECT count(post.ID) from {$wpdb->prefix}posts as post where post.post_type='product' and (post.post_status='publish' or post.post_status='draft') and post.post_author = '$_POST[changeSeller]'";

        } elseif (isset($_POST['changeit'])) {

            $val = $_POST['check-pro'];

            if ($val == 1) {

                $query = "SELECT post.ID as post_id from {$wpdb->prefix}posts as post where post.post_type='product' and (post.post_status='publish' or post.post_status='draft') and post.post_author = 1 LIMIT $perpage OFFSET $offset";

                $count_query = "SELECT count(post.ID) from {$wpdb->prefix}posts as post where post.post_type='product' and (post.post_status='publish' or post.post_status='draft') and post.post_author = 1";

            } else {

                $query = "SELECT post.ID as post_id from {$wpdb->prefix}posts as post where post.post_type='product' and (post.post_status='publish' or post.post_status='draft') and post.post_author!=1 LIMIT $perpage OFFSET $offset";

                $count_query = "SELECT count(post.ID) from {$wpdb->prefix}posts as post where post.post_type='product' and (post.post_status='publish' or post.post_status='draft') and post.post_author!=1";

            }

        } elseif (isset($_POST['s'])) {

            $p_search = $_POST['s'];

            $query = "SELECT post.ID as post_id from {$wpdb->prefix}posts as post where post.post_type='product' and (post.post_status='publish' or post.post_status='draft') and (post.post_title like '" . $p_search . "%' or post.post_title like '%" . $p_search . "' or post.post_title like '%" . $p_search . "%') LIMIT $perpage OFFSET $offset";

            $count_query = "SELECT count(post.ID) from {$wpdb->prefix}posts as post where post.post_type='product' and (post.post_status='publish' or post.post_status='draft') and (post.post_title like '" . $p_search . "%' or post.post_title like '%" . $p_search . "' or post.post_title like '%" . $p_search . "%')";

        } else {

            $query = "SELECT post.ID as post_id from {$wpdb->prefix}posts as post where post.post_type='product' and (post.post_status='publish' or post.post_status='draft') LIMIT $perpage OFFSET $offset";

            $count_query = "SELECT count(post.ID) from {$wpdb->prefix}posts as post where post.post_type='product' and (post.post_status='publish' or post.post_status='draft')";
        }

        $post_ids = $wpdb->get_results($query, ARRAY_A);

        $count_post_ids = $wpdb->get_var($count_query);

        if (!empty($post_ids)) {
            foreach ($post_ids as $id) {
                $post_result[] = get_post($id['post_id'], ARRAY_A);

                $post_data[] = get_post_meta($id['post_id']);

                $post_cat = get_the_category($id['post_id']);

                $category = get_categories($post_cat);

            }

            $i = 0;

            $p_type = wc_get_product_types();

            foreach ($post_result as $result) {

                $product_tags = get_the_terms($result['ID'], 'product_tag');

                $product_cats = get_the_terms($result['ID'], 'product_cat');

                $product_object = wc_get_product($result['ID']);

                $product_type = '';

                $p_cat = array();

                if (!empty($product_cats)) {
                    foreach ($product_cats as $cat) {
                        $p_cat[] = $cat->name;
                    }
                    $category = implode(',', $p_cat);
                } else {
                    $category = '';
                }
                $p_tags = array();

                if (!empty($product_tags)) {
                    foreach ($product_tags as $tag) {
                        $p_tags[] = $tag->name;
                    }
                    $tags = implode(',', $p_tags);
                } else {
                    $tags = '';
                }

                $thumnail_image = explode(',', get_post_thumbnail_id($result['ID']));

                $product_thum = $wpdb->get_var("select meta_value from {$wpdb->prefix}postmeta where post_id='" . $thumnail_image[0] . "' and meta_key='_wp_attached_file'");

                $product_thum = get_post_meta($thumnail_image[0], '_wp_attached_file', true);

                $post_date = explode(' ', $result['post_date']);

                if ($product_thum == '') {
                    $fresult[$i]['Image'] = '<img class="attachment-shop_thumbnail wp-post-image" width="50" height="50" alt="" src="' . WK_MARKETPLACE . 'assets/images/placeholder.png' . '">';
                } else {
                    $fresult[$i]['Image'] = '<img class="attachment-shop_thumbnail wp-post-image" width="50" height="50" alt="" src="' . content_url() . '/uploads/' . $product_thum . '">';
                }

                $fresult[$i]['id'] = $result['ID'];

                $fresult[$i]['Title'] = $result['post_name'];

                $fresult[$i]['Name'] = '<a href="post.php?post=' . $result['ID'] . '&action=edit">' . $result['post_title'] . '</a>';

                $fresult[$i]['SKU'] = (isset($post_data[$i]['_sku'][0]) ? $post_data[$i]['_sku'][0] : 0);

                $fresult[$i]['Stock'] = isset($post_data[$i]['_stock_status'][0]) ? '<mark class="instock">' . $post_data[$i]['_stock_status'][0] . '</mark>' : 'draft';

                if ($product_object->is_type('simple')) {
                    $fresult[$i]['Price'] = '<span class="amount">' . wc_price($product_object->get_price()) . '</span>';
                } elseif ($product_object->is_type('variable')) {
                    $fresult[$i]['Price'] = '<span class="price"><span class="amount">' . wc_price($product_object->get_variation_prices()['price'] ? min($product_object->get_variation_prices()['price']) : 0) . '</span>&ndash;<span class="amount">' . wc_price($product_object->get_variation_prices()['price'] ? max($product_object->get_variation_prices()['price']) : 0) . '</span></span>';
                } elseif ($product_object->is_type('external')) {
                    $fresult[$i]['Price'] = '<span class="amount">' . wc_price($product_object->get_price()) . '</span>';
                } elseif ($product_object->is_type('grouped')) {
                    $fresult[$i]['Price'] = '<span class="amount">-</span>';
                } else {
                    $fresult[$i]['Price'] = '<span class="amount">' . wc_price($product_object->get_price()) . '</span>';
                }
                $product_type = $p_type[$product_object->get_type()];

                $fresult[$i]['Categories'] = $category;

                $fresult[$i]['Tags'] = $tags;

                $fresult[$i]['featured'] = $product_object->is_featured() ? 'Yes' : 'No';

                $fresult[$i]['Type'] = $product_type;

                $fresult[$i]['Date'] = $post_date[0] . '<br>' . $result['post_status'];

                if ($result['post_author'] == 1) {
                    $fresult[$i]['Seller'] = 'Admin';
                } else {
                    $fresult[$i]['Seller'] = get_user_meta($result['post_author'], 'first_name', true) . ' ' . get_user_meta($result['post_author'], 'last_name', true);
                }

                ++$i;
            }

            return array(
                'data' => $fresult,
                'count_data' => $count_post_ids,
            );
        }
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'Image':
            case 'Name':
            case 'SKU':
            case 'Stock':
            case 'Price':
            case 'Categories':
            case 'Tags':
            case 'featured':
            case 'Type':
            case 'Date':
            case 'Seller':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'Image' => '<span class="wc-image tips">' . esc_html__('Image', 'marketplace') . 'Image</span>',
            'Name' => esc_html__('Product', 'marketplace'),
            'SKU' => esc_html__('SKU', 'marketplace'),
            'Stock' => esc_html__('Stock', 'marketplace'),
            'Price' => esc_html__('Price', 'marketplace'),
            'Categories' => esc_html__('Categories', 'marketplace'),
            'Tags' => esc_html__('Tags', 'marketplace'),
            'featured' => esc_html__('Featured', 'marketplace'),
            'Type' => esc_html__('Type', 'marketplace'),
            'Date' => esc_html__('Date', 'marketplace'),
            'Seller' => esc_html__('Seller', 'marketplace'),
        );

        return $columns;
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $found_data = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->_column_headers = $this->get_column_info();
        $this->process_bulk_action();
        $per_page = $this->get_items_per_page('product_per_page', 20);
        $current_page = $this->get_pagenum();

        $data_return = $this->getdata($per_page);

        $total_items = !empty($data_return['count_data']) ? $data_return['count_data'] : 0;

        $data_return = $data_return['data'];

        if (!empty($data_return)) {
            usort($data_return, array($this, 'usort_reorder'));
        }

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
        ));

        $this->items = $data_return;
    }

    public function process_bulk_action()
    {
        // security check!
        if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {
            $nonce = filter_input(INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING);
            if (wp_create_nonce('mp-product-list') == $_POST['_wpnonce']) {
                wp_die(esc_html__('Nope! Security check failed!', 'marketplace'));
            }
        }

        if ($this->current_action() == 'trash') {
            if (isset($_POST['product']) && is_array($_POST['product'])) {
                foreach ($_POST['product'] as $value) {
                    $product_trashed = array('ID' => $value, 'post_status' => 'trash');
                    wp_update_post($product_trashed);
                }?>
                <div id="message" class="updated notice is-dismissible">
                    <p><?php echo count($_POST['product']) . esc_html__('product moved to the Trash.', 'marketplace'); ?>
                </div>
            <?php
} else {
                if (isset($_GET['_wpnonce'])) {
                    if (wp_create_nonce('trash_' . $_GET['post']) == $_GET['_wpnonce']) {
                        $product_trashed = array(
                            'ID' => $_GET['post'],
                            'post_status' => 'trash',
                        );

                        if (wp_update_post($product_trashed)) {
                            wp_redirect($_SERVER['HTTP_REFERER']);
                            exit;
                        }
                    }
                }
            }
        }
    }

    public function get_bulk_actions()
    {
        $actions = array(
            'trash' => 'Trash',
        );

        return $actions;
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="product[]" value="%s" />', $item['id']);
    }

    public function column_Name($item)
    {
        $actions = array(
            'ID' => sprintf('ID:%s', $item['id']),
            'Edit' => sprintf('<a href="post.php?post=%s&action=edit">%s</a>', $item['id'], esc_html__('Edit', 'marketplace')),
            'View' => sprintf('<a href="%s?post_type=product&p=%s">%s</a>', esc_url(get_site_url()), $item['id'], esc_html__('View', 'marketplace')),
            'Trash' => sprintf('<a class="submitdelete" title="%s" href="?page=products&post=%s&action=trash&_wpnonce=%s">%s</a>', esc_html__('move this to the trash', 'marketplace'), $item['id'], wp_create_nonce('trash_' . $item['id']), esc_html__('Trash', 'marketplace')),
        );

        return sprintf('%1$s %2$s', $item['Name'], $this->row_actions($actions));
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'Name' => array('Name', false),
            'Stock' => array('Stock', false),
            'Price' => array('Price', false),
            'Date' => array('Date', false),
            'Seller' => array('Seller', false),
        );

        return $sortable_columns;
    }

    public function usort_reorder($a, $b)
    {
        $orderby = (!empty($_POST['orderby'])) ? $_POST['orderby'] : 'name';
        $order = (!empty($_POST['order'])) ? $_POST['order'] : 'asc';
        $result = '';

        if (isset($a[$orderby])) {
            $result = strcmp($a[$orderby], $b[$orderby]);
        }

        return ($order === 'asc') ? $result : -$result;
    }
}

$ProductListTable = new Product_List_Table();

printf('<div class="wrap" id="product-list-table"><h1 class="wp-heading-inline">%s</h1>', esc_html__('Product List', 'marketplace'));

$ProductListTable->prepare_items();

?>
<form method="post">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
    <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('mp-product-list'); ?>" />

    <?php

$ProductListTable->search_box(esc_html__('Search', 'marketplace'), 'search-id');

$ProductListTable->display();

?>
</form>
<?php

echo '</div>';
