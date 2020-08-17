<?php

if (! defined('ABSPATH') ) {
    exit;
}

?> <div class="woocommerce-account">
    <?php

    do_action('mp_get_wc_account_menu', 'marketplace');

?>
<div class="favourite-seller woocommerce-MyAccount-content">
    <div id="notify-customer" >
        <div class="mp-modal-wrapper">
            <div class="mp-modal-dialog">
                <div class="mp-modal-content">
                    <form action="" method="post" id="snotifier">
                        <div class="mp-modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="mp-modal-title"><?php esc_html_e('Confirmation', 'marketplace'); ?></h4>
                        </div>
                        <div class="mp-modal-body">
                            <div class="form-group">
                                <label for="subject"><?php esc_html_e('Subject:', 'marketplace'); ?> <span class="required"> *</span></label>
                                <input type="text" name="customer_subject" class="form-control customer_subject" aria-describedby="subject" placeholder="<?php esc_html_e('Enter Subject', 'marketplace')?>">
                            </div>
                            <div class="form-group">
                                <label for="message"><?php esc_html_e('Message:', 'marketplace'); ?> <span class="required"> *</span></label>
                                <textarea name="customer_message" class="form-control customer_message" aria-describedby="message" placeholder="<?php esc_html_e('Enter Message', 'marketplace')?>" rows="4"></textarea>
                                <input type="hidden" name="seller_id" value="<?php echo get_current_user_id(); ?>">
                            </div>
                        </div>
                        <div class="mp-modal-footer">
                            <div class="final-result"></div>
                                <div class="reaction">
                                    <button type="button" id="wk-cancel-mail"><?php esc_html_e('Close', 'marketplace'); ?></button>
                                    <button type="submit" id="wk-send-mail"><?php esc_html_e('Send Mail', 'marketplace'); ?></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php

    $current_user = get_current_user_id();

    $customer_list = get_users(
        array(
            'meta_key'   =>'favourite_seller',
            'meta_value' => $current_user,
        )
    );
    if (! empty($customer_list) ) :
    ?>
        <div class="filter-data">
            <div class="mail-to-follower">
                <button type="button"><?php esc_html_e('Send Notification', 'marketplace'); ?></button>
            </div>
            <div class="action-delete">
                <button type="button"><?php esc_html_e('Delete Follower', 'marketplace'); ?></button>
            </div>
        </div>
        <table class="shop-fol">
            <thead>
                <tr>
                    <th style="position:relative">
                        <div class="select-all-box">
                            <div class="icheckbox_square-blue">
                                <input type="checkbox" class="mass-action-checkbox">
                                <ins class="iCheck-helper"></ins>
                            </div>
                        </div>
                    </th>
                    <th class=""><?php esc_html_e('Customer Name', 'marketplace'); ?></th>
                    <th class=""><?php esc_html_e('Customer Email', 'marketplace'); ?></th>
                    <th class=""><?php esc_html_e('Action', 'marketplace'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ( $customer_list as $ckey => $cvalue ) {
                $user_id = $cvalue->data->ID;
                $customer_country = get_user_meta($user_id, 'shipping_country', true);
                ?>
                    <tr data-id="<?php echo $user_id; ?>">
                        <td>
                            <div class=icheckbox_square-blue>
                                    <input type=checkbox class="mass-action-checkbox">
                                    <ins class=iCheck-helper></ins>
                            </div>
                        </td>
                        <td>
                            <?php echo esc_html($cvalue->data->display_name); ?>  
                        </td>
                        <td class='c-mail' data-cmail="<?php echo esc_html($cvalue->data->user_email); ?>">
                            <?php echo esc_html($cvalue->data->user_email); ?>  
                        </td>
                        <td>
                            <span class='remove-icon' data-customer-id='<?php echo esc_html($user_id); ?>' data-seller-id="<?php echo esc_html($current_user); ?>"></span>  
                        </td>
                    </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php
    else :
        ?>
        <strong>
        <?php
            esc_html_e('No Followers Available.', 'marketplace');
        ?>
        </strong>
        <?php
    endif;
    ?>
    </div>
</div>
