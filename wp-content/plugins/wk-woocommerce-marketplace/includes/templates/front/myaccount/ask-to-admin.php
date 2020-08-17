<?php
/**
 * File for ask to admin template.
 *
 * @package wk-woocommerce-marketplace/includes/template/front/my-account/
 */

if (! defined('ABSPATH') ) {
    exit;
}

global $wpdb;

$error = array();

if (isset($_POST['ask_to_admin']) ) { // Input var okay.
    if (isset($_POST['ask_to_admin_nonce']) && ! empty($_POST['ask_to_admin_nonce']) ) { // Input var okay.
        if (wp_verify_nonce(sanitize_key($_POST['ask_to_admin_nonce']), 'ask_to_admin_nonce_action') ) { // Input var okay.
            $error = admin_mailer();
        } else {
            $error['nonce-error'] = esc_html__('Security check failed, nonce verification failed!', 'marketplace');
        }
    } else {
        $error['nonce-error'] = esc_html__('Security check failed, nonce empty!', 'marketplace');
    }
    if ($error ) {
        foreach ( $error as $key => $value ) {
            if (is_admin() ) {
                ?>
             <div class="wrap">
              <div class="notice notice-error">
               <p><?php echo esc_html($value); ?></p>
                    </div>
                </div>
                <?php
            } else {
                wc_print_notice($value, 'error');
            }
        }
    }
}

if (! is_admin() ) :
?>
<div class="woocommerce-account">
<?php do_action('mp_get_wc_account_menu', 'marketplace'); ?>
<div class="woocommerce-MyAccount-content">
<?php endif; ?>

    <!-- Form -->
    <div id="ask-data">
        <form id="ask-form" method="post" action="">
            <p>
                <label class="label" for="query_user_sub"><b><?php echo esc_html__('Subject', 'marketplace'); ?></b><span class="required"> *</span></label>
                <input id='query_user_sub' class="wkmp-querysubject regular-text" type="text" name="subject">
                <span  id="askesub_error" class="error-class"></span>
            </p>
            <p>
                <label class="label" for="userquery"><b><?php echo esc_html__('Message', 'marketplace'); ?><span class="required"> *</span></b></label>
                <textarea id="userquery" rows="4" class="wkmp-queryquestion regular-text" name="message"></textarea>
                <span  id="askquest_error" class="error-class"></span>
            </p>
            <div class="">
                <?php wp_nonce_field('ask_to_admin_nonce_action', 'ask_to_admin_nonce'); ?>
                <input id="askToAdminBtn" type="submit" name="ask_to_admin" value="<?php echo esc_html__('Ask', 'marketplace'); ?>" class="button button-primary">
            </div>
        </form>
    </div>

    <?php
    global $wp_query;
    $page_no = get_query_var('pagenum');
    $current_page = !empty($page_no) ? intval($page_no) : 1; 
    $offset = ($current_page -1) * 10;
    
    $user_id = get_current_user_id();

    $query_result = $wpdb->get_results($wpdb->prepare(" SELECT * FROM {$wpdb->prefix}mpseller_asktoadmin where seller_id = %d ORDER BY id DESC LIMIT $offset, 10 ", $user_id));
    
    $total_askto = $wpdb->get_results($wpdb->prepare(" SELECT DISTINCT id FROM {$wpdb->prefix}mpseller_asktoadmin where seller_id = %d", $user_id));
    
    $askto_count = 0;
    if (!empty($total_askto)) {
        $askto_count = count($total_askto);
    }
    
    if ($query_result ) :

        if (! is_admin() ) :
        ?>
            <div class="mp-asktoadmin-history" id="main_container">
                <table class="mp-asktoadmin-history-table" width="100%">
                    <thead>
                        <tr>
                            <th width="25%"><?php echo esc_html__('Date', 'marketplace'); ?></th>
                            <th width="25%"><?php echo esc_html__('Subject', 'marketplace'); ?></th>
                            <th width="50%"><?php echo esc_html__('Message', 'marketplace'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $query_result as $key => $value ) : ?>
                        <tr>
                            <td data-tab="<?php echo esc_html__('Date', 'marketplace'); ?>"><?php echo date('d-M-Y', strtotime($value->create_date)); ?></td>
                            <td data-tab="<?php echo esc_html__('Subject', 'marketplace'); ?>"><?php echo esc_html($value->subject); ?></td>
                            <td data-tab="<?php echo esc_html__('Message', 'marketplace'); ?>"><?php echo esc_html($value->message); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php
                $maxpage_count = ceil($askto_count / 10);
                if (1 < $maxpage_count ) : 
                    ?>
                    <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
                        <?php
                        if (1 !== $current_page ) :
                        ?>
                        <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url(site_url(get_option('wkmp_seller_page_title').'/'.get_option('mp_to', 'to').'/page/'.($current_page - 1))); ?>">
                            <?php _e('Previous', 'marketpalce'); ?>
                        </a>
                        <?php
                        endif;
                        if (intval($maxpage_count) !== $current_page ) :
                        ?>
                        <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url(site_url(get_option('wkmp_seller_page_title').'/'.get_option('mp_to', 'to').'/page/'.($current_page + 1))); ?>">
                            <?php _e('Next', 'marketplace'); ?>
                        </a>
                        <?php 
                        endif;
                        ?>
                    </div>
                <?php
                endif;
                ?>
            </div>
            <?php
            if (! is_admin() ) :
            ?>
                    </div>
                </div>
            <?php 
            endif;
        endif;
    endif;
