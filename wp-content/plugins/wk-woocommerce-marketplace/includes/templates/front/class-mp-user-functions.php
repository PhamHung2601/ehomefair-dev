<?php

if (! defined('ABSPATH') ) {
    exit;
}

/**
 * Set user role.
 *
 * @param int    $user_id .
 * @param string $role    .
 * @param string $old     .
 */
function mp_set_user_role( $user_id, $role, $old ) 
{

    global $wpdb;

    $seller_table = $wpdb->prefix . 'mpsellerinfo';

    $seller_id = $wpdb->get_results("SELECT seller_id from $seller_table where user_id = '$user_id'");

    if ($seller_id ) {

        $seller_id = $seller_id[0]->seller_id;

        foreach ( $old as $key => $value ) {

            if ($value == 'wk_marketplace_seller' ) {

                $seller = array( 'seller_value' => 'customer' );

                $seller_res = $wpdb->update($seller_table, $seller, array( 'seller_id' => $seller_id ));

            }
        }
    }
}

function asktoadmin() 
{
    include_once 'myaccount/ask-to-admin.php';
}

function wk_Change_password() 
{
    include_once 'myaccount/forgot-password.php';
}

function shop_followers() 
{
    include_once 'myaccount/shop-followers.php';
}

function spreview() 
{
    include_once 'myaccount/preview.php';
}

function seller_all_product() 
{
    include_once 'myaccount/user-product.php';
}

function add_feedback() 
{
    include_once 'myaccount/add-feedback.php';
}

function efeedback() 
{
    include_once 'myaccount/feedback.php';
}

function seller_profile( $atts ) 
{
    include_once 'myaccount/profile.php';
}

function edit_profile() 
{
    include_once 'myaccount/prof_edit.php';
}

function dashboard() 
{

    if (! class_exists('WC_Admin_Report') ) {
        include WC_ABSPATH . 'includes/admin/reports/class-wc-admin-report.php';
    }

    include_once 'myaccount/class-mp-report-dashboard.php';

    $dash_obj = new MP_Report_Dashboard();

    $dash_obj->mp_dashboard_page();

}

function seller_transaction_view() 
{

    global $transaction, $commission;
    $current_user       = get_current_user_id();
    $transaction_id     = get_query_var('pid');
    $transaction_detail = $transaction->get_by_id($transaction_id, $current_user);
    $admin_rate         = $commission->get_admin_rate($current_user);
    extract($transaction_detail);

    $columns = apply_filters(
        'mp_account_transactions_columns', array(
        'order-id'         => __('Order Id', 'marketplace'),
        'product-name'     => __('Product Name', 'marketplace'),
        'product-quantity' => __('Qty', 'marketplace'),
        'total-price'      => __('Total Price', 'marketplace'),
        'commission'       => __('Commission', 'marketplace'),
        'subtotal'         => __('Subtotal', 'marketplace'),
        ) 
    );

    include_once WK_MARKETPLACE_DIR . 'includes/templates/front/transaction/view.php';
}

function seller_transaction() 
{
    include_once WK_MARKETPLACE_DIR . 'includes/templates/front/transaction/list.php';
}
