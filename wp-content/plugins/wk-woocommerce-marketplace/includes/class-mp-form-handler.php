<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * Handle frontend forms.
 *
 * @class MP_Form_Handler
 *
 * @version 4.7.1
 *
 * @category Class
 *
 * @author webkul
 */
class MP_Form_Handler
{
    public $child;

    /**
     * Constructor.
     */
    public function __construct()
    {
        include_once sprintf('%s/front/save-shipping-options.php', dirname(__FILE__));
    }

    /**
     * Fix author to upload image.
     *
     * @param int $post_id post id
     */
    public function marketplace_media_fix($post_id = '')
    {
        global $frontier_post_id;
        global $post_ID;

        /* WordPress 3.4.2 fix */
        $post_ID = $post_id;

        // WordPress 3.5.1 fix.
        $frontier_post_id = $post_id;

        $p = add_filter('media_view_settings', array($this, 'marketplace_media_fix_filter'), 10, 2);
    }

    /**
     * Fix insert media editor button filter.
     *
     * @param array $settings setting array
     * @param int   $post     post
     */
    public function marketplace_media_fix_filter($settings, $post)
    {
        global $frontier_post_id;

        $settings['post']['id'] = $frontier_post_id;

        return $settings;
    }

    /**
     * Fix author to upload image  end.
     *
     * @param int    $id         userid
     * @param string $avtar_type type
     */
    public function get_user_avatar($id, $avtar_type)
    {
        global $wpdb;

        return $wpdb->get_results("SELECT um.meta_value,pm.meta_value from $wpdb->usermeta um  join $wpdb->postmeta pm on um.meta_value=pm.post_id  where um.user_id=$id and um.meta_key='_thumbnail_id_".$avtar_type."' and pm.meta_key='_wp_attached_file'");
    }

    public function update_user_new_avtar($id, $post_meta_value, $avtar_type)
    {
        global $wpdb;
        $del_post_id = $wpdb->get_var("select meta_value from $wpdb->usermeta where meta_key='_thumbnail_id_".$avtar_type."' and user_id='$id'");
        $delid = delete_post_meta($del_post_id, '_wp_attached_file', $post_meta_value);

        return $delid;
    }

    public function getOrderId()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $sql = "select woitems.order_item_id from {$wpdb->prefix}woocommerce_order_itemmeta woi join {$wpdb->prefix}woocommerce_order_items woitems on woitems.order_item_id=woi.order_item_id join {$wpdb->prefix}posts post on woi.meta_value=post.ID where woi.meta_key='_product_id' and post.ID=woi.meta_value and post.post_author='".$user_id."' GROUP BY order_id";
        $result = $wpdb->get_results($sql);
        $ID = array();
        foreach ($result as $res) {
            $ID[] = $res->order_item_id;
        }

        return implode(',', $ID);
    }

    public function calling_pages()
    {
        global $current_user, $wpdb, $wp_query;
        $current_user = wp_get_current_user();
        $seller_info = $wpdb->get_var('SELECT user_id FROM '.$wpdb->prefix."mpsellerinfo WHERE user_id = '".$current_user->ID."' and seller_value='seller'");

        $pagename = get_query_var('pagename');
        $main_page = get_query_var('main_page');
        $edit_info = get_query_var('action');
        $info = get_query_var('info');
        $edit_id = get_query_var('pid');
        $seller_id = get_query_var('sid');
        $order_id = get_query_var('order_id');
        $ship = get_query_var('ship');
        $zone_id = get_query_var('zone_id');
        $ship_page = get_query_var('ship_page');
        $page_no = get_query_var('pagenum');
        
        if (!empty($pagename)) {
            include_once 'templates/front/class-mp-order-functions.php';
    
            if ($pagename == get_option('wkmp_seller_page_title') && $main_page == 'invoice' && !empty($order_id)) {
                if (!empty($main_page) && ($current_user->ID || $seller_info > 0)) {
                    wk_mp_invoice($order_id);
                    die;
                } else {
                    global $wp_query;
                    $wp_query->set_404();
                    status_header(404);
                    get_template_part(404);
                    exit();
                }
            }

            if ($main_page == get_option('mp_profile', 'profile') && ($current_user->ID || $seller_info > 0)) {
                add_shortcode('marketplace', 'seller_profile');
            }

            if ($main_page == get_option('mp_profile', 'profile') && $info == 'edit' && ($current_user->ID && $seller_info > 0)) {
                add_shortcode('marketplace', 'edit_profile');
            } elseif ($main_page == get_option('mp_product_list', 'product-list') && $seller_info > 0) {
                include 'front/product-list.php';
                add_shortcode('marketplace', 'product_list', 'product-list');
            } elseif ($main_page == get_option('mp_add_product', 'add-product') && $seller_info > 0) {
                add_shortcode('marketplace', 'add_product');
            } elseif (($main_page == 'product' && $edit_info == 'edit' && !empty($edit_id)) && $seller_info > 0) {
                add_shortcode('marketplace', 'edit_product');
            } elseif ($main_page == 'change-password' && $seller_info > 0) {
                add_shortcode('marketplace', 'wk_Change_password');
            } elseif ($main_page == 'product' && $info == 'edit' && $seller_info > 0) {
                add_shortcode('marketplace', 'edit_product');
            } elseif ($main_page == get_option('mp_dashboard', 'dashboard') && $seller_info > 0) {
                add_shortcode('marketplace', 'dashboard');
            } elseif ($main_page ==  get_option( 'mp_store', 'store' ) && !empty($info)) {
                add_shortcode('marketplace', 'spreview');
            } elseif ($main_page == get_option( 'mp_seller_product', 'seller-product' ) && !empty($info)) {
                add_shortcode('marketplace', 'seller_all_product');
            } elseif ($main_page == 'add-feedback' && ($current_user->ID || $seller_info > 0)) {
                add_shortcode('marketplace', 'add_feedback');
            } elseif ($main_page == 'feedback') {
                add_shortcode('marketplace', 'efeedback');
            } elseif ($main_page == get_option('mp_shop_follower', 'shop-follower') && ($current_user->ID || $seller_info > 0)) {
                add_shortcode('marketplace', 'shop_followers', 'shop-follower');
            } elseif ($main_page == get_option('mp_order_history', 'order-history') && $seller_info > 0) {
                if (!empty($order_id)) {
                    add_shortcode('marketplace', 'order_view');
                } else {
                    add_shortcode('marketplace', 'order_history');
                }
            } elseif ($main_page == get_option('mp_transaction', 'transaction') && $seller_info > 0) {
                if (!empty($edit_id) && !empty($edit_info)) {
                    add_shortcode('marketplace', 'seller_transaction_view');
                } else {
                    add_shortcode('marketplace', 'seller_transaction');
                }
            } elseif ($main_page == get_option('mp_to', 'to') && $seller_info > 0) {
                add_shortcode('marketplace', 'asktoadmin');
            } elseif (!empty($main_page) && $ship_page == get_option('mp_shipping', 'shipping') && $seller_info > 0) {
                add_shortcode('marketplace', 'manage_shipping');
            } elseif (!empty($main_page) && $ship ==  get_option('mp_shipping', 'shipping') && !empty($edit_info) && $seller_info > 0) {
                if ($edit_info == 'edit') {
                    add_shortcode('marketplace', 'edit_shipping');
                } elseif ($edit_info == 'add') {
                    add_shortcode('marketplace', 'add_shipping');
                } else {
                    add_shortcode('marketplace', 'displayForm');
                }
            } else {
                // call registration form from page.
                add_shortcode('marketplace', 'displayForm');
            }
        } else {
            // call registration form from page.
            add_shortcode('marketplace', 'displayForm');
        }
    }

    // update product category.
    public static function update_pro_category($cat_id, $postid)
    {
        if (is_array($cat_id) && array_key_exists('1', $cat_id)) {
            wp_set_object_terms($postid, $cat_id, 'product_cat');
        } elseif (is_array($cat_id)) {
            $term = get_term_by('slug', $cat_id[0], 'product_cat');
            wp_set_object_terms($postid, $term->term_id, 'product_cat');
        }
    }

    public function redirect_to_productpage()
    {
        $params = array('page' => 'List');
        $url = add_query_arg($params, get_permalink());

        return $url;
    }

    /**
     * Profile edit redirection.
     */
    public function profile_edit_redirection()
    {
        global $current_user,$wpdb, $woocommerce;

        $error = array();

        $current_user = wp_get_current_user();

        if (isset($_POST['wk_firstname']) && isset($_POST['wk_lastname']) && isset($_POST['wk_user_nonece']) && wp_verify_nonce(sanitize_key($_POST['wk_user_nonece']), 'edit_profile')) { // Input var okay.
            $first_name = isset($_POST['wk_firstname']) ? sanitize_text_field(wp_unslash($_POST['wk_firstname'])) : ''; // Input var okay.

            $last_name = isset($_POST['wk_lastname']) ? sanitize_text_field(wp_unslash($_POST['wk_lastname'])) : ''; // Input var okay.

            $shop_name = isset($_POST['wk_storename']) ? sanitize_text_field(wp_unslash($_POST['wk_storename'])) : ''; // Input var okay.

            $shop_phone = isset($_POST['wk_storephone']) ? strip_tags(wp_unslash($_POST['wk_storephone'])) : ''; // Input var okay.

            $about_shop = isset($_POST['wk_marketplace_about_shop']) ? strip_tags($_POST['wk_marketplace_about_shop']) : ''; // Input var okay.

            // new fields.
            $user_address_1 = isset($_POST['wk_store_add1']) ? strip_tags($_POST['wk_store_add1']) : ''; // Input var okay.

            $user_address_2 = isset($_POST['wk_store_add2']) ? strip_tags($_POST['wk_store_add2']) : ''; // Input var okay.

            $user_city = isset($_POST['wk_store_city']) ? strip_tags($_POST['wk_store_city']) : ''; // Input var okay.

            $user_postcode = isset($_POST['wk_store_postcode']) ? strip_tags($_POST['wk_store_postcode']) : ''; // Input var okay.

            $user_country = isset($_POST['wk_store_country']) ? strip_tags($_POST['wk_store_country']) : ''; // Input var okay.
            
            $user_state = isset($_POST['wk_store_state']) ? strip_tags($_POST['wk_store_state']) : ''; // Input var okay.

            $user_payment_details = isset($_POST['mp_seller_payment_details']) ? strip_tags($_POST['mp_seller_payment_details']) : ''; // Input var okay.

            if ($user_address_1) {
                if (preg_match('/^[A-Za-z0-9_ -]{1,40}$/', $user_address_1)) {
                    update_user_meta($current_user->ID, 'billing_address_1', $user_address_1);
                }
            }

            if ($user_address_2) {
                if (preg_match('/^[A-Za-z0-9_ -]{1,40}$/', $user_address_2)) {
                    update_user_meta($current_user->ID, 'billing_address_2', $user_address_2);
                }
            }

            if ($user_city) {
                if (preg_match('/^[A-Za-z0-9_ -]{1,40}$/', $user_city)) {
                    update_user_meta($current_user->ID, 'billing_city', $user_city);
                }
            }

            if ($user_country) {
                update_user_meta($current_user->ID, 'billing_country', $user_country);
            }
            if ($user_state) {
                global $woocommerce;
                $countries_obj = new WC_Countries();
                $countries = $countries_obj->__get('countries');
                $cntry = get_user_meta($current_user->ID, 'billing_country', true);
                if (WC()->countries->get_states($cntry)) {
                    $states = WC()->countries->get_states($cntry);
                    if (isset($states[$user_state])) {
                        update_user_meta($current_user->ID, 'billing_state', $user_state);
                    } elseif (in_array($user_state, $states, true)) {
                        $state_code = array_search($user_state, $states, true);
                        update_user_meta($current_user->ID, 'billing_state', $state_code);
                    }
                } else {
                    update_user_meta($current_user->ID, 'billing_state', $user_state);
                }
            }

            if ($user_postcode) {
                if (preg_match('/^[A-Z0-9]{1,10}$/', $user_postcode)) {
                    update_user_meta($current_user->ID, 'billing_postcode', $user_postcode);
                }
            }

            $fb_url = isset($_POST['settings']['social']['fb']) ? filter_var(wp_unslash($_POST['settings']['social']['fb']), FILTER_SANITIZE_URL) : ''; // Input var okay.

            $gplus_url = isset($_POST['settings']['social']['gplus']) ? filter_var(wp_unslash($_POST['settings']['social']['gplus']), FILTER_SANITIZE_URL) : ''; // Input var okay.

            $twitter_url = isset($_POST['settings']['social']['twitter']) ? filter_var(wp_unslash($_POST['settings']['social']['twitter']), FILTER_SANITIZE_URL) : ''; // Input var okay.

            $in_url = isset($_POST['settings']['social']['linked']) ? filter_var(wp_unslash($_POST['settings']['social']['linked']), FILTER_SANITIZE_URL) : ''; // Input var okay.

            $yt_url = isset($_POST['settings']['social']['youtube']) ? filter_var(wp_unslash($_POST['settings']['social']['youtube']), FILTER_SANITIZE_URL) : ''; // Input var okay.

            $banner_visibility = isset($_POST['mp_display_banner']) ? strip_tags($_POST['mp_display_banner']) : ''; // Input var okay.

            $userdata = array(
                'ID' => $current_user->ID,
                'user_email' => isset($_POST['user_email']) ? sanitize_email(wp_unslash($_POST['user_email'])) : '', // Input var okay.
            );

            if (isset($_POST['user_email']) && wp_unslash($_POST['user_email']) && filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL) === false) { // Input var okay.
                $error['em-error'] = __('E-mail not valid.', 'marketplace');
            } elseif ($userdata['user_email']) {
                $c = wp_update_user($userdata);

                if (isset($c->errors) && $c->errors && isset($c->errors['existing_user_email'][0])) {
                    $error['em-error'] = __($c->errors['existing_user_email'][0], 'marketplace');
                }
            } else {
                $error['em-error'] = __('E-mail is required.', 'marketplace');
            }

            if ($banner_visibility == 'yes') {
                update_user_meta($current_user->ID, 'shop_banner_visibility', 'yes');
            } else {
                update_user_meta($current_user->ID, 'shop_banner_visibility', 'no');
            }

            if ($first_name) {
                if (preg_match('/^[A-Za-z0-9_-]{1,40}$/', $first_name)) {
                    update_user_meta($current_user->ID, 'first_name', $first_name);
                } else {
                    $error['fn-error'] = __('First name is not valid.', 'marketplace');
                }
            } else {
                update_user_meta($current_user->ID, 'first_name', $first_name);
            }

            if ($last_name) {
                if (preg_match('/^[A-Za-z0-9_-]{1,40}$/', $last_name)) {
                    update_user_meta($current_user->ID, 'last_name', $last_name);
                } else {
                    $error['fn-error'] = __('Last name is not valid.', 'marketplace');
                }
            } else {
                update_user_meta($current_user->ID, 'last_name', $last_name);
            }

            if ($shop_name) {
                if (preg_match("/^[-A-Za-z0-9_\s]{1,40}$/", $shop_name)) {
                    update_user_meta($current_user->ID, 'shop_name', $shop_name);
                } else {
                    $error['fn-error'] = __('Shop name is not valid.', 'marketplace');
                }
            } else {
                $error['sn-error'] = __('Shop name is required.', 'marketplace');
            }

            if ($shop_phone) {
                if (strlen($shop_phone) > 10) {
                    $error['phn-error'] = __('Phone number length must not exceed 10.', 'marketplace');
                } elseif (preg_match('/^[0-9]{1,10}$/', $shop_phone)) {
                    update_user_meta($current_user->ID, 'billing_phone', $shop_phone);
                } else {
                    $error['phn-error'] = __('Entered phone number is not valid.', 'marketplace');
                }
            } else {
                update_user_meta($current_user->ID, 'billing_phone', '');
            }

            update_user_meta($current_user->ID, 'about_shop', $about_shop);

            if ($fb_url) {
                if (filter_var($fb_url, FILTER_VALIDATE_URL) !== false) {
                    update_user_meta($current_user->ID, 'social_facebook', $fb_url);
                } else {
                    $error['fb-error'] = __('Facebook URL not valid', 'marketplace');
                }
            } else {
                update_user_meta($current_user->ID, 'social_facebook', '');
            }

            if ($twitter_url) {
                if (filter_var($twitter_url, FILTER_VALIDATE_URL) !== false) {
                    update_user_meta($current_user->ID, 'social_twitter', $twitter_url);
                } else {
                    $error['tw-error'] = __('Twitter URL not valid', 'marketplace');
                }
            } else {
                update_user_meta($current_user->ID, 'social_twitter', '');
            }

            if ($gplus_url) {
                if (filter_var($gplus_url, FILTER_VALIDATE_URL) !== false) {
                    update_user_meta($current_user->ID, 'social_gplus', $gplus_url);
                } else {
                    $error['gp-error'] = __('Google Plus URL not valid', 'marketplace');
                }
            } else {
                update_user_meta($current_user->ID, 'social_gplus', '');
            }

            if ($in_url) {
                if (filter_var($in_url, FILTER_VALIDATE_URL) !== false) {
                    update_user_meta($current_user->ID, 'social_linkedin', $in_url);
                } else {
                    $error['in-error'] = __('LinkedIN URL not valid', 'marketplace');
                }
            } else {
                update_user_meta($current_user->ID, 'social_linkedin', '');
            }

            if ($yt_url) {
                if (filter_var($yt_url, FILTER_VALIDATE_URL) !== false) {
                    update_user_meta($current_user->ID, 'social_youtube', $yt_url);
                } else {
                    $error['yt-error'] = __('Youtube URL not valid', 'marketplace');
                }
            } else {
                update_user_meta($current_user->ID, 'social_youtube', '');
            }

            if (isset($_POST['mp-remove-company-logo']) && !empty($_POST['mp-remove-company-logo'])) { // Input var okay.
                $logo_thumb_id = !empty($_POST['mp-remove-company-logo']) ? intval(wp_unslash($_POST['mp-remove-company-logo'])) : ''; // Input var okay.
                delete_user_meta($current_user->ID, '_thumbnail_id_company_logo');
            }

            if (isset($_POST['mp-remove-shop-banner']) && !empty($_POST['mp-remove-shop-banner'])) { // Input var okay.
                $logo_thumb_id = !empty($_POST['mp-remove-shop-banner']) ? intval(wp_unslash($_POST['mp-remove-shop-banner'])) : ''; // Input var okay.
                delete_user_meta($current_user->ID, '_thumbnail_id_shop_banner');
            }

            if (isset($_POST['mp-remove-avatar']) && !empty($_POST['mp-remove-avatar'])) { // Input var okay.
                $logo_thumb_id = !empty($_POST['mp-remove-avatar']) ? intval(wp_unslash($_POST['mp-remove-avatar'])) : ''; // Input var okay.
                delete_user_meta($current_user->ID, '_thumbnail_id_avatar');
            }

            $shop_logo_arr = $_FILES; // Input var okay.

            $shop_banner_arr = $shop_logo_arr;

            if (isset($_FILES['mp_useravatar']['name']) && '' !== $_FILES['mp_useravatar']['name']) { // Input var okay.
                $av_error = $this->upload_avatar($current_user->ID, $_FILES, 'avatar'); // Input var okay.
                if ($av_error) {
                    $error['av-error'] = $av_error;
                }
            }
            if (isset($shop_banner_arr['wk_mp_shop_banner']['name']) && '' !== $shop_banner_arr['wk_mp_shop_banner']['name']) { // Input var okay.
                $bn_error = $this->upload_avatar($current_user->ID, $shop_banner_arr, 'shop_banner'); // Input var okay.
                if ($bn_error) {
                    $error['bn-error'] = $bn_error;
                }
            }
            if ('' !== $shop_logo_arr['mp_company_logo']['name']) { // Input var okay.
                $logo_error = $this->upload_avatar($current_user->ID, $shop_logo_arr, 'company_logo');
                if ($logo_error) {
                    $error['logo-error'] = $logo_error;
                }
            }

            if ($user_payment_details != '') {
                update_user_meta($current_user->ID, 'mp_seller_payment_details', $user_payment_details);
            }

            do_action('mp_save_seller_profile_details', $_POST, $current_user->ID);

            do_action('marketplace_save_seller_payment_details');

            if ($error) {
                foreach ($error as $key => $value) {
                    wc_add_notice(__($value, 'marketplace'), 'error');
                    wp_redirect($_POST['_wp_http_referer']);
                    exit;
                }
            } else {
                if (is_admin()) {
                    return array(
                        'success' => __('Profile updated successfully.', 'marketplace'),
                    );
                } else {
                    wc_add_notice(__('Profile updated successfully.', 'marketplace'), 'success');
                    wp_redirect($_POST['_wp_http_referer']);
                    exit;
                }
            }
        }
    }

    public function produt_by_seller_ID($seller, $str)
    {
        global $wpdb;
        $seque = '';
        $product_by = '';
        if (!empty($str)) {
            $arr = explode('_', $str);
            if ($arr[0] == 'price') {
                $product_by = '_sale_price';
            } else {
                $product_by = 'post_title';
            }
            if ($arr[1] == 'h') {
                $seque = 'desc';
            } else {
                $seque = 'asc';
            }
        }
        if (isset($arr[0]) && $arr[0] == 'price') {
            $products = $wpdb->get_results("select post.post_title,post.post_name,post.ID,pmeta.meta_value as sale_price,pmeta1.meta_value as regular_price from $wpdb->posts as post join $wpdb->postmeta as pmeta on post.ID=pmeta.post_id join $wpdb->postmeta as pmeta1 on post.ID=pmeta1.post_id where post.post_author=$seller and post.post_type='product' and post.post_status='publish' and pmeta1.meta_key='_regular_price' and pmeta.meta_key='_sale_price' order by sale_price $seque");
        } else {
            $products = $wpdb->get_results("select post.post_title,post.post_name,post.ID,pmeta.meta_value as sale_price,pmeta1.meta_value as regular_price from $wpdb->posts as post join $wpdb->postmeta as pmeta on post.ID=pmeta.post_id join $wpdb->postmeta as pmeta1 on post.ID=pmeta1.post_id where post.post_author=$seller and post.post_type='product' and post.post_status='publish' and pmeta1.meta_key='_regular_price' and pmeta.meta_key='_sale_price' order by post.post_title $seque");
        }

        return $products;
    }

    // category tree nmanagement.
    public function get_level($parent_term, $level)
    {
        global $wpdb;
        if ($parent_term != 0) {
            ++$level;
            $term = $wpdb->get_var("select parent from $wpdb->term_taxonomy where term_id=".$parent_term);

            return $this->get_level($term, $level);
        } else {
            return $level;
        }
    }

    public function get_page_id($page_name)
    {
        global $wpdb;
        $page_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '".$page_name."'");

        return $page_id;
    }

    // seller prview function.
    public function spreview($seller_id)
    {
        global $wpdb;
        if ($seller_id != '') {
            $seller_data = $wpdb->get_results("SELECT umeta.* FROM $wpdb->usermeta as umeta join {$wpdb->prefix}mpsellerinfo as mpseller on umeta.user_id=mpseller.user_id WHERE umeta.user_id = '$seller_id' and mpseller.seller_value='seller'");

            return $seller_data;
        }
    }

    // seller preview function.
    public function seller_product($seller_id)
    {
        global $wpdb;
        $seller_product = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}posts where post_author = '$seller_id' and post_type='product' and post_status='publish' order by ID desc");

        return $seller_product;
    }

    public function seller_product_meta($seller_id)
    {
        global $wpdb;

        return $wpdb->get_results("SELECT pmeta.meta_key,pmeta.meta_value FROM {$wpdb->prefix}postmeta as pmeta join {$wpdb->prefix}posts as post on pmeta.post_id=post.ID WHERE post.post_author = '$seller_id'");
    }

    // manage multiple categories.
    public function mp_product_categories1($parent, $obj_id)
    {
        global $wpdb;
        $product_category = $wpdb->get_results("select wpt.*,wptt.*,wptt.parent as cat_parent from $wpdb->terms wpt join $wpdb->term_taxonomy wptt on wpt.term_id=wptt.term_id where wptt.taxonomy='product_cat' and wptt.parent=$parent");
        $this_pro_cat = $wpdb->get_results("select term_taxonomy_id from $wpdb->term_relationships where object_id=$obj_id");
        static $prod_cat = array();
        foreach ($this_pro_cat as $t) {
            $prod_cat[] = $t->term_taxonomy_id;
        }

        foreach ($product_category as $procat) {
            $opt_selected = '';
            $wk_sp = $this->get_level($procat->cat_parent, 0);
            for ($i = 0; $i < $wk_sp; ++$i) {
                $wk_space .= '&nbsp;&nbsp;';
            }
            if (in_array($procat->term_id, $prod_cat)) {
                $opt_selected = 'selected="selected"';
            }
            echo "<option value='".$procat->term_id."' $opt_selected >".$wk_space.$procat->name.'</option>';
            $this->mp_product_categories1($procat->term_id, 0);
        }
    }

    public function process_reset_password()
    {
        if (isset($_POST['user_login'])) {
            $result = pass_reset();
            if (is_wp_error($result)) {
                echo '<div class="jerror">'.$result->get_error_message().'</div>';
            }
        }
    }

    public function inform_marketplace_seller($pid)
    {
        global $wpdb;
        $query = "select user_email from $wpdb->users as user join {$wpdb->prefix}posts as post on post.post_author=user.ID where post.ID=$pid";

        return $wpdb->get_results($query);
    }

    // new customer Order.
    public function seller_email_order_items($order, $items)
    {
        foreach ($items as $item_id => $item) :

            $_product = apply_filters('woocommerce_order_item_product', $order->get_product_from_item($item), $item);
            $item_meta = new WC_Order_Item_Product($item, $_product);
            $tr_data = '<tr> <td style="text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;">';
            $show_image = false;
            // Show title/image etc
            if ($show_image) {
                $tr_data .= apply_filters('woocommerce_order_item_thumbnail', '<img src="'.($_product->get_image_id() ? current(wp_get_attachment_image_src($_product->get_image_id(), 'thumbnail')) : wc_placeholder_img_src()).'" alt="'.__('Product Image', 'woocommerce').'" height="'.esc_attr($image_size[1]).'" width="'.esc_attr($image_size[0]).'" style="vertical-align:middle; margin-right: 10px;" />', $item);
            }

            // Product name.
            $tr_data .= apply_filters('woocommerce_order_item_name', $item['name'], $item);
            $show_sku = true;
            // SKU.
            if ($show_sku && is_object($_product) && $_product->get_sku()) {
                $tr_data .= ' (#'.$_product->get_sku().')';
            }
            $show_download_links = false;
            // File URLs.
            if ($show_download_links && is_object($_product) && $_product->exists() && $_product->is_downloadable()) {
                $download_files = $order->get_item_downloads($item);
                $i = 0;

                foreach ($download_files as $download_id => $file) {
                    ++$i;

                    if (count($download_files) > 1) {
                        $prefix = sprintf(__('Download', 'marketplace').$i);
                    } elseif ($i == 1) {
                        $prefix = __('Download', 'marketplace');
                    }

                    $tr_data .= '<br/><small>'.$prefix.': <a href="'.esc_url($file['download_url']).'" target="_blank">'.esc_html($file['name']).'</a></small>';
                }
            }

            // Variation.
            if ($item_meta->get_meta()) {
                $tr_data .= '<br/><small>'.nl2br($item_meta->display(true, true)).'</small>';
            }

            $tr_data .= '</td><td style="text-align:left; vertical-align:middle; border: 1px solid #eee;">'.$item['qty'].'</td>';
            $tr_data .= '<td style="text-align:left; vertical-align:middle; border: 1px solid #eee;">'.$order->get_formatted_line_subtotal($item).'</td></tr>';

            $show_purchase_note = false;
            if ($show_purchase_note && is_object($_product) && $purchase_note = get_post_meta($_product->id, '_purchase_note', true)) :
                $tr_data .= '<tr><td colspan="3" style="text-align:left; vertical-align:middle; border: 1px solid #eee;">'.wpautop(do_shortcode(wp_kses_post($purchase_note))).'</td></tr>';
            endif;
        endforeach;

        return $tr_data;
    }

    public function product_from_diffrent_seller($items)
    {
        $mp_product_author = array();
        foreach ($items as $key => $item) {
            $item_id = $item['product_id'];
            $author_email = $this->inform_marketplace_seller($item_id);
            $send_to = $author_email[0]->user_email;
            if (in_array($send_to, $mp_product_author)) {
                $mp_product_author[$send_to][] = $item;
            } else {
                $mp_product_author[$send_to][] = $item;
            }
        }

        return $mp_product_author;
    }

    public function get_seller_subtotal_to_display($order, $per_seller_items, $compound = false, $tax_display = '')
    {
        $subtotal = 0;

        if (!$compound) {
            foreach ($per_seller_items as $item) {
                if (!isset($item['line_subtotal']) || !isset($item['line_subtotal_tax'])) {
                    return '';
                }

                $subtotal += $item['line_subtotal'];

                if ('incl' == $tax_display) {
                    $subtotal += $item['line_subtotal_tax'];
                }
            }

            $subtotal = wc_price($subtotal, array('currency' => $order->get_currency()));

            $prices_require_tax = false;

            if ($tax_display == 'excl' && $prices_require_tax) {
                $subtotal .= ' <small>'.WC()->countries->ex_tax_or_vat().'</small>';
            }
        } else {
            if ('incl' == $tax_display) {
                return '';
            }

            foreach ($this->get_items() as $item) {
                $subtotal += $item['line_subtotal'];
            }

            // Add Shipping Costs.
            $subtotal += $this->get_total_shipping();

            // Remove non-compound taxes.
            foreach ($this->get_taxes() as $tax) {
                if (!empty($tax['compound'])) {
                    continue;
                }

                $subtotal = $subtotal + $tax['tax_amount'] + $tax['shipping_tax_amount'];
            }

            // Remove discounts.
            $subtotal = $subtotal - $this->get_cart_discount();

            $subtotal = wc_price($subtotal, array('currency' => $this->get_order_currency()));
        }

        return apply_filters('woocommerce_order_subtotal_to_display', $subtotal, $compound, $this);
    }

    public function send_mail_to_inform_seller($order)
    {
        $items = $order->get_items();
        $per_seller_items = $this->product_from_diffrent_seller($items);
        $recent_user = wp_get_current_user();
        $cur_email = $recent_user->user_email;
        foreach ($per_seller_items as $key => $items) {
            do_action('woocommerce_seller_new_order', $items, $key);
        }
    }

    // get product image.
    public function get_product_image($pro_id, $meta_value)
    {
        global $wpdb;
        $p = get_post_meta($pro_id, $meta_value, true);
        if ($p == null) {
            return '';
        }
        $product_image = get_post_meta($p, '_wp_attached_file', true);

        return $product_image;
    }

    public function insert_avatar_attachment($file_avt, $user_id, $avtar_type, $setthumb = 'false')
    {
        global $wpdb;
        $error = '';
        // check to make sure its a successful upload.
        if ($_FILES[$file_avt]['error'] !== UPLOAD_ERR_OK) {
            __return_false();
        }
        include_once ABSPATH.'wp-admin/includes/image.php';
        include_once ABSPATH.'wp-admin/includes/file.php';
        include_once ABSPATH.'wp-admin/includes/media.php';

        switch ($avtar_type) {
        case 'company_logo':
            $file_name = 'Logo';
            break;
        case 'avatar':
            $file_name = 'Profile Image';
            break;
        case 'shop_banner':
            $file_name = 'Banner';
            break;
        default:
            break;
        }

        if ($_FILES[$file_avt]['size'] > wp_max_upload_size()) {
            $error = $file_name.__('file size too large ', 'marketplace').'[ <= '.number_format(wp_max_upload_size() / 1048576).' MB ]';
        }

        $file_type = mime_content_type($_FILES[$file_avt]['tmp_name']);
        $allowed_types = array(
            'image/png',
            'image/jpeg',
            'image/jpg',
        );
        if (!$error && !in_array($file_type, $allowed_types)) {
            $error = __('Upload valid ', 'marketplace').$file_name.__(' file type ', 'marketplace').'[ png, jpeg, jpg ]';
        }

        if (!$error) {
            $attach_id = media_handle_upload($file_avt, $user_id);
            $profile_image = $this->get_user_avatar($user_id, $avtar_type);
            if (!empty($profile_image[0]->meta_value)) {
                $del = $this->update_user_new_avtar($user_id, $profile_image[0]->meta_value, $avtar_type);
                if ($del) {
                    update_user_meta($user_id, '_thumbnail_id_'.$avtar_type, $attach_id);
                }
            } else {
                $data_usermeta = array(
                    'user_id' => $user_id,
                    'meta_key' => '_thumbnail_id_'.$avtar_type,
                    'meta_value' => $attach_id,
                );
                $wpdb->insert($wpdb->usermeta, $data_usermeta);
            }
        }

        return $error;
    }

    public function upload_avatar($user_id, $imagfile, $avtar_type)
    {
        if ($avtar_type == 'shop_banner') {
            $files = $imagfile['wk_mp_shop_banner'];
        }
        if ($avtar_type == 'avatar') {
            $files = $imagfile['mp_useravatar'];
        }
        if ($avtar_type == 'company_logo') {
            $files = $imagfile['mp_company_logo'];
        }
        $_FILES = array('upload_attachment' => $files);
        foreach ($_FILES as $file => $array) {
            $newupload = $this->insert_avatar_attachment($file, $user_id, $avtar_type);
        }

        return $newupload;
    }

    public static function admin_ask($email, $subject, $ask)
    {
        do_action('asktoadmin_mail', $email, $subject, $ask);

        if (is_admin()) {
            ?>
            <div class="notice notice-success">
                <p><?php echo esc_html__('Your query has been received successfully.', 'marketplace'); ?></p>
            </div>
            <?php
        } else {
            wc_add_notice(esc_html__('Your query has been received successfully.', 'marketplace'), 'success');
        }
    }

    public function update_marketplace_seller_roles($user_id)
    {
        $user = new WP_User($user_id);
        $user->remove_role('owner');
        echo get_option('default_role');
        exit;
        $user->add_role('administrator');
    }

    // check user inputs.
    public function mp_check_input_data($data)
    {
        return htmlspecialchars($data);
    }
}
new MP_Form_Handler();
