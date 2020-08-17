<?php

/**
 * @author Webkul
 * @version 2.0.0
 * Template: Chatbox top bar
 */

use WpMarketplaceBuyerSellerChat\Helper;

$helper = new Helper\Mpbs_Data();

$user_id = $helper->mpbs_get_current_user_id();

$image_src = $helper->mpbs_get_user_image_by_id($user_id);

$chat_row = $helper->mpbs_check_chat_started_bs($seller_id, $user_id);

if (is_product() || ($user_id && ! $helper->mpbs_author_is_seller($user_id))) :
?>

<header class="mpbs-chatbox-top-bar">
    <span class="mpbs_chat_status"><div class="status" style="float:none;"></div></span>

    <p class="mpbs-label"><?php echo get_option('mpbs_chat_name'); ?></p>

    <div class="mpbs-box-controls close"></div>

    <div class="mpbs-box-controls maximize"></div>
</header>

<?php endif; ?>

<?php if (is_user_logged_in()) : ?>
<div class="mpbs-chat-controls" style="<?php if (! $chat_row) echo 'display:none';  ?>">

    <div class="mpbs-profile-image">
      <img src="<?php echo $image_src; ?>" height="35" width="35">
      <span class="mpbs-self-status">
        <div class="status" style="float:none;"></div>
      </span>
    </div>


    <div class="mpbs-user-select-status customer">

        <span class="mpbs-chat-status">

            <div class="mpbs-chat-status-options mpbs-controls-option-box">

                <span class="mpbs-status-point"></span>

                <div class="list-group-item chatStatus" data-id="1"><?php echo esc_html__( 'Online', 'mp_buyer_seller_chat' ); ?><span class="mpbs-status online"></span></div>

                <div class="list-group-item chatStatus" data-id="2"><?php echo esc_html__( 'Busy', 'mp_buyer_seller_chat' ); ?><span class="mpbs-status busy"></span></div>

                <div class="list-group-item chatStatus" data-id="3"><?php echo esc_html__( 'Offline', 'mp_buyer_seller_chat' ); ?><span class="mpbs-status offline"></span></div>

            </div>

        </span>

    </div>

    <div class="mpbs-profile-setting">

        <div class="mpbs-chat-setting">

            <ul class="mpbs-chat-setting-options mpbs-controls-option-box">

                <span class="mpbs-status-point"></span>

                <li class="mpbs-chat-setting-options-1" id="buyerProfileSetting"><?php echo esc_html__( 'Profile Settings', 'mp_buyer_seller_chat' ); ?></li>

            </ul>

        </div>

    </div>

    <div class="mpbs-previous-history">

        <div class="mpbs-history-clock">

            <ul class="mpbs-history-options mpbs-controls-option-box">
                <span class="mpbs-status-point"></span>

                <li class="mpbs-history-options-1" data-value="1"><?php echo esc_html__( 'Last 24-hrs', 'mp_buyer_seller_chat' ); ?></li>
                <li class="mpbs-history-options-2" data-value="2"><?php echo esc_html__( 'Last 7-days', 'mp_buyer_seller_chat' ); ?></li>
                <li class="mpbs-history-options-3" data-value="3"><?php echo esc_html__( 'Last 30-days', 'mp_buyer_seller_chat' ); ?></li>
                <li class="mpbs-history-options-4" data-value="4"><?php echo esc_html__( 'Forever', 'mp_buyer_seller_chat' ); ?></li>
            </ul>

        </div>

    </div>

</div>
<?php endif; ?>
