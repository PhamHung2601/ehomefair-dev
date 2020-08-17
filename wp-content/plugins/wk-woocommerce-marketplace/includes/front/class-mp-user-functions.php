<?php

if (! defined('ABSPATH') ) {
    exit;
}

/**
 * Set reviews.
 */
function set_reviews() 
{
    global $wpdb;

    $sql = '';

    $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option('wkmp_seller_page_title') . "'");

    if (isset($_POST['feed_value']) && isset($_POST['feed_summary']) && isset($_POST['feed_review']) && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_key(wp_unslash($_POST['_wpnonce'])), 'mp-seller-review-nonce') ) { // Input var okay.

        $seller_id = isset($_POST['mp_wk_seller']) ? intval(wp_unslash($_POST['mp_wk_seller'])) : ''; // Input var okay.

        $user_id = isset($_POST['mp_wk_user']) ? intval(wp_unslash($_POST['mp_wk_user'])) : ''; // Input var okay.

        $feedprice = isset($_POST['feed_price']) ? intval(wp_unslash($_POST['feed_price'])) : ''; // Input var okay.

        $feed_value = isset($_POST['feed_value']) ? intval(wp_unslash($_POST['feed_value'])) : ''; // Input var okay.

        $feed_quality = isset($_POST['feed_quality']) ? intval(wp_unslash($_POST['feed_quality'])) : ''; // Input var okay.

        $user_details = get_user_by('ID', $user_id);

        $nickname = get_user_meta($user_id, 'first_name', true) ? get_user_meta($user_id, 'first_name', true) . ' ' . get_user_meta($user_id, 'last_name', true) : $user_details->display_name; // Input var okay.

        $summary = isset($_POST['feed_summary']) ? strip_tags($_POST['feed_summary']) : ''; // Input var okay.

        $review = isset($_POST['feed_review']) ? strip_tags($_POST['feed_review']) : ''; // Input var okay.

        $create_date = date('Y-m-d H:i:s');

        if (! empty($seller_id) && ! empty($user_id) && ! empty($feedprice) && ! empty($feed_value) && ! empty($feed_quality) && ! empty($nickname) && ! empty($summary) && ! empty($review) && ! empty($create_date) ) {

            $sql = $wpdb->insert(
                $wpdb->prefix . 'mpfeedback',
                array(
                'seller_id'      => $seller_id,
                'user_id'        => $user_id,
                'price_r'        => $feedprice,
                'value_r'        => $feed_value,
                'quality_r'      => $feed_quality,
                'nickname'       => $nickname,
                'review_summary' => $summary,
                'review_desc'    => $review,
                'review_time'    => $create_date,
                ),
                array(
                '%d',
                '%d',
                '%d',
                '%d',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                )
            );

            $user_reviews = get_user_meta($user_id, '_seller_review', true);
            if (empty($user_reviews) ) {
                $user_reviews = array();
            }
            array_push($user_reviews, $seller_id);
            update_user_meta($user_id, '_seller_review', $user_reviews);
        } else {
            wc_print_notice(__('Fill all required fields.', 'marketplace'), 'error');
        }

        do_action('mp_save_seller_review_notification', $_POST, $wpdb->insert_id);

        if ($sql ) {
            wc_add_notice(__('Review added successfully.', 'marketplace'), 'success');
            wp_redirect(site_url($page_name) . '/feedback/' . $_POST['mp_wk_sellerurl']);
            exit;
        }

    } else {
        wc_print_notice(__('Fill all required fields.', 'marketplace'), 'error');
    }
}

/**
 * Get review.
 *
 * @param int $id id.
 */
function get_review( $id ) 
{
    global $wpdb;

    return $wpdb->get_results($wpdb->prepare("SELECT * from {$wpdb->prefix}mpfeedback where seller_id = %d and status = %d order by review_time desc limit 3", $id, 1));
}

/**
 * Get seller details
 *
 * @param  int $user_id user id.
 * @return [type]          [description]
 */
function get_seller_details( $user_id ) 
{
    return get_user_meta($user_id);
}

/**
 * Mail to admin
 */
function admin_mailer() 
{

    global $wpdb;

    $error = array();

    if (isset($_POST['subject']) && isset($_POST['message']) ) {

        $subject = ! empty($_POST['subject']) ? strip_tags(wp_unslash($_POST['subject'])) : ''; // Input var okay.

        $message = ! empty($_POST['message']) ? strip_tags(wp_unslash($_POST['message'])) : ''; // Input var okay.

        if (! empty($subject) && ! empty($message) ) { // Input var okay.
            $current_user = wp_get_current_user();

            $message_length = strlen($message);

            if (! preg_match('/^[A-Za-z0-9 ]{1,100}$/', $subject) ) {
                $error['subject-invalid'] = __('Subject Invalid.', 'marketplace');
            } elseif ($message_length > 500 ) {
                $error['message-length'] = __('Message length should be less than 500.', 'marketplace');
            } else {
                $current_time = date('Y-m-d H:i:s');

                $sql = $wpdb->insert(
                    $wpdb->prefix . 'mpseller_asktoadmin',
                    array(
                    'seller_id'   => $current_user->ID,
                    'subject'     => $subject,
                    'message'     => $message,
                    'create_date' => $current_time,
                    ),
                    array(
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    )
                );

                if ($sql ) {
                    do_action('asktoadmin_mail', $current_user->user_email, $subject, $message);

                    if (is_admin()) {
                        ?>
                         <div class="notice notice-success">
                          <p><?php echo esc_html__('Your query has been received successfully.', 'marketplace'); ?></p>
                        </div>
                <?php
                    } else {
                        wc_print_notice(esc_html__('Your query has been received successfully.', 'marketplace'), 'success');
                    }
                }
            }
        } else {
            $error['empty-field'] = __('Fill required fields.', 'marketplace');
        }
    }

    return $error;
}

/**
 * Reset password.
 */
function pass_reset() 
{
    global $wpdb, $current_site;
    $errors = new WP_Error();
    if (empty($_POST['user_login']) ) {
        $errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or e-mail address.', 'marketplace'));
    } elseif (strpos($_POST['user_login'], '@') ) {
        $user_data = get_user_by('email', trim($_POST['user_login']));
        if (empty($user_data) ) {
            $errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.', 'marketplace'));
        }
    } else {
        $login     = trim($_POST['user_login']);
        $user_data = get_user_by('login', $login);
    }
    do_action('lostpassword_post');
    if ($errors->get_error_code() ) {
        return $errors;
    }
    if (! $user_data ) {
        $errors->add('invalidcombo', '<strong>' . __('ERROR', 'marketplace') . '</strong>:' . __(' Invalid username or e-mail.', 'marketplace'));
        return $errors;
    }
    $user_login = $user_data->user_login;
    $user_email = $user_data->user_email;
    do_action('retreive_password', $user_login);
    do_action('retrieve_password', $user_login);
    $allow = apply_filters('allow_password_reset', true, $user_data->ID);
    if (! $allow ) {
        return new WP_Error('no_password_reset', __('Password reset is not allowed for this user', 'marketplace'));
    } elseif (is_wp_error($allow) ) {
        return $allow;
    }
    $key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
    if (empty($key) ) {
        $key = wp_generate_password(20, false);
        do_action('retrieve_password_key', $user_login, $key);
        $wpdb->update($wpdb->users, array( 'user_activation_key' => $key ), array( 'user_login' => $user_login ));
    }

    $message  = __('Someone requested that the password be reset for the following account:', 'marketplace') . "\r\n\r\n";
    $message .= network_site_url() . "\r\n\r\n";
    $message .= sprintf(__('Username: ', 'marketplace') . $user_login) . "\r\n\r\n";
    $message .= __('If this was a mistake, just ignore this email and nothing will happen.', 'marketplace') . "\r\n\r\n";
    $message .= __('To reset your password, visit the following address:', 'marketplace') . "\r\n\r\n";
    $message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";

    if (is_multisite() ) {
        $blogname = $GLOBALS['current_site']->site_name;
    } else {
        // The blogname option is escaped with esc_html on the way into the database in sanitize_option
        // we want to reverse this for the plain text arena of emails.
        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
    }
    $title    = sprintf($blogname . __(' Password Reset', 'marketplace'));
    $title    = apply_filters('retrieve_password_title', $title);
    $message  = apply_filters('retrieve_password_message', $message, $key);
    if ($message && ! wp_mail($user_email, $title, $message) ) {
        $errors->add('invalidcombo', '<strong>' . __('ERROR', 'marketplace') . '</strong>:' . __(' The e-mail could not be sent. Possible reason: your host may have disabled the mail() function...', 'marketplace'));
        return $errors;
    }
    return true;
}

/**
 * Seller collection pagination
 *
 * @param int $max_num_pages max page count.
 */
function mp_seller_collection_pagination( $max_num_pages ) 
{
    if ($max_num_pages > 1 ) {
        ?>
     <nav class="woocommerce-pagination">
        <?php
        echo paginate_links(
            apply_filters(
                'woocommerce_pagination_args', array(
                'base'      => esc_url_raw(str_replace(999999999, '%#%', remove_query_arg('add-to-cart', get_pagenum_link(999999999, false)))),
                'format'    => '',
                'add_args'  => false,
                'current'   => max(1, get_query_var('pagenum')),
                'total'     => $max_num_pages,
                'prev_text' => '&larr;',
                'next_text' => '&rarr;',
                'type'      => 'list',
                'end_size'  => 3,
                'mid_size'  => 3,
                ) 
            ) 
        );
        ?>
     </nav>
        <?php
    }
}

/**
 *  Seller ID based on shop url
 *
 * @param  string $shop_url shop url.
 * @return seller_id
 */
function mp_return_seller_id( $shop_url ) 
{
    $user = get_users(
        array(
        'meta_key'   => 'shop_address',
        'meta_value' => $shop_url,
        )
    );

    foreach ( $user as $value ) {
        $seller_id = $value->ID;
    }

    return $seller_id;
}

/**
 * Get review.
 *
 * @param int $id id.
 */
function get_review_by_page( $id ) 
{
    global $wpdb;

    $table_name     = $wpdb->prefix . 'mpfeedback';
    $pagination     = '';
    $query          = "SELECT * FROM $table_name where seller_id = '$id' and status = '1'";
    $total_query    = "SELECT COUNT(1) FROM (${query}) AS combined_table";
    $total          = $wpdb->get_var($total_query);
    $items_per_page = get_option('posts_per_page');
    $page           = ( get_query_var('pagenum') ) ? get_query_var('pagenum') : 1;
    $offset         = ( $page * $items_per_page ) - $items_per_page;
    $result         = $wpdb->get_results($query . " ORDER BY id DESC LIMIT ${offset}, ${items_per_page}");
    $totalpage      = ceil($total / $items_per_page);

    if ($totalpage > 1 ) {
        $pagination = '<nav class="woocommerce-pagination">' . paginate_links(
            apply_filters(
                'woocommerce_pagination_args', array(
                'base'      => esc_url_raw(str_replace(999999999, '%#%', remove_query_arg('add-to-cart', get_pagenum_link(999999999, false)))),
                'format'    => '',
                'add_args'  => false,
                'current'   => max(1, get_query_var('pagenum')),
                'total'     => $totalpage,
                'prev_text' => '&larr;',
                'next_text' => '&rarr;',
                'type'      => 'list',
                'end_size'  => 3,
                'mid_size'  => 3,
                ) 
            ) 
        ) . '</nav>';
    }

    return array(
    'data'  => $result,
    'count' => $pagination,
    );
}
