<?php

if (!defined('ABSPATH')) {
    exit;
}

$wpmp_obj10 = new MP_Form_Handler();

?>

<div class="wkmp-feedback-popup-container">
    <div class="wkmp_feedback_popup">
        <?php if (!empty($user_value)) { ?>
            <input type='hidden' value="<?php echo esc_html($user_value);?>" id="feedbackloged_in_status" />
        <?php } else { ?>
            <input type='hidden' value="" id="feedbackloged_in_status" />
        <?php } ?>
        
        <input type='hidden' value="<?php echo esc_url(site_url()); ?>" id="base_url" />
        <div id="fb-root"></div>
        <div class="wkmp_cross_login"></div>
        <?php
        $feedback_url = '';
        $shop_address = get_user_meta($sellerid, 'shop_address', true);
        ?>
        <div id='feedback_form'>
            <?php wc_print_notices();?>
            <form method="post" class="login">
                <p class="form-row form-row-wide">
                    <label for="username"><?php esc_html_e('Username or email address', 'marketplace');?> <span class="required">*</span></label>
                    <?php if (!empty($_POST['username'])) { ?>
                        <input type="text" class="input-text" name="wkmp_username" id="username" value="<?php echo esc_attr($_POST['username']); ?>" />
                    <?php } else { ?>
                        <input type="text" class="input-text" name="wkmp_username" id="username" />
                    <?php } ?>
                </p>
                <p style="display:none;">
                    <input type="hidden" name="wkfb_mp_key_app_idID" id="wkfb_mp_key_app_idID" value="<?php echo get_option('wkfb_mp_key_app_ID'); ?>" />
                    <input type="hidden" name="wkfb_mp_app_secret_kekey" id="wkfb_mp_app_secret_kekey" value="<?php echo get_option('wkfb_mp_app_secret_key'); ?>" />
                    <input type="hidden" name="wkfacebook_login_page_id" id="wkfacebook_login_page_id" value="<?php echo $wpmp_obj10->get_page_id(get_option('wkmp_seller_page_title')); ?>" />
                </p>

                <p class="form-row form-row-wide">
                    <label for="password"><?php esc_html_e('Password', 'marketplace');?> <span class="required">*</span></label>
                    <input class="input-text" autoComplete="on" type="password" name="password" id="password" />
                </p>
                <p class="form-row wkmp-login-button">
                    <?php wp_nonce_field('marketplace-user');?>
                    <input type="hidden" value="<?php echo esc_url(get_permalink() . 'add-feedback/' . $shop_address); ?>" name="_wp_http_referer">
                    <input type="submit" class="button" id='submit-btn-feedback' name="login" value="<?php esc_html_e('Login', 'marketplace');?>" />
                    <a href="<?php echo esc_url(wc_lostpassword_url()); ?>"><?php esc_html_e('Lost your password?', 'marketplace');?></a>
                    <a href="javascript:void(0);"><img border="0" id='mp-fb-login-btn'/></a>
                </p>
            </form>
        </div>
    </div>
</div>
