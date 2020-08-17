<?php

/**
 * @author Webkul
 * @version 2.0.0
 * Template: Chatbox reply section
 */

use WpMarketplaceBuyerSellerChat\Helper;

$data_obj = new Helper\Mpbs_Data();

$buyer_id = $data_obj->mpbs_get_current_user_id();

$chat_row = $data_obj->mpbs_check_chat_started_bs($seller_id, $buyer_id);

?>

<!-- if chat not started yet -->
<?php if (is_user_logged_in() && !$chat_row): ?>
<div class="mpbs-start-chat-container">
    <form id="mpbs_start_chat" method="post">
        <div class="fieldset">
            <div class="field required">
                <div class="control">
                    <textarea class="mpbs_start_message" name="message" placeholder="<?php echo esc_html__( 'Type your message...', 'mp_buyer_seller_chat' ); ?>"></textarea>
                </div>
            </div>
            <div class="actions-toolbar">
                <div class="primary" style="width: 100%">
                    <button type="submit" class="button mpbs-action-start-chat"><?php echo esc_html__( 'Start Chat', 'mp_buyer_seller_chat' ); ?></button>
                </div>
            </div>
        </div>
    </form>
</div>
<?php endif;?>
<!-- if chat not started yet -->

<!-- if customer not logged in -->
<?php if (!is_user_logged_in()): ?>
    <div class="mpbs-start-chat-container">
        <?php
$args = array(
    'form_id' => 'mpbs-chat-login-form',
    'remember' => false,
);
wp_login_form($args);?>
    </div>
<?php endif;?>
<!-- if customer not logged in ends-->

<!-- if chat started -->
<?php

if (is_user_logged_in() && $chat_row):

    $customer_config = $data_obj->mpbs_get_chatbox_config($seller_id);

    ?>
	<div class="mpbs-thread-container">
	    <ul class="mpbs-discussion">
	    </ul>
	</div>
	<div class="mpbs-input-area">
	    <form method="post" id="mpbs-form-reply-customer">
	        <input type="hidden" name="class" value="other" />
	        <input type="hidden" name="receiverId" value="<?php echo $customer_config['sellerData']['sellerId'] ?>" />
	        <input type="hidden" name="senderId" value="<?php echo $customer_config['customerData']['customerId'] ?>" />
	        <input type="hidden" name="customerName" value="<?php echo $customer_config['customerData']['name'] ?>" />
	        <input type="hidden" name="customerImage" value="<?php echo $customer_config['customerData']['src'] ?>" />
	        <div class="mpbs-message-box">
	            <textarea name="message" id="mpbs-chatbox-text" rows="1" placeholder="<?php echo __('Write...', 'mp_buyer_seller_chat'); ?>"></textarea>
	            <span class="dashicons dashicons-smiley mpbs-emoticons-button"></span>
	            <div class="mpbs-emoticons-container">
	                <div class="mpbs-smiley-wrapper">
	                    <div class="mpbs-smiley-pad">
	                        :smiley: :smile: :wink: :blush: :angry:
	                    </div>
	                    <div class="mpbs-smiley-pad">
	                        :laughing: :smirk: :disappointed: :sleeping: :rage:
	                    </div>
	                    <div class="mpbs-smiley-pad">
	                         :cry: :yum: :neutral_face: :astonished: :sunglasses:
	                    </div>
	                    <div class="mpbs-smiley-pad">
	                      :stuck_out_tongue_winking_eye: :confused: :scream: :stuck_out_tongue: :fearful:
	                    </div>
	                    <div class="mpbs-smiley-pad">
	                        :punch: :ok_hand: :clap: :thumbsup: :thumbsdown:
	                    </div>
	                </div>
	            </div>
	        </div>

	    </form>
	</div>

	<?php endif;?>

<script id="tmpl-mpbs_customer_chatbox_template" type="text/html">
  <div class="mpbs-thread-container">
      <ul class="mpbs-discussion"></ul>
  </div>
  <div class="mpbs-input-area">
      <form method="post" id="mpbs-form-reply-customer">
          <input type="hidden" name="class" value="other" />
          <input type="hidden" name="receiverId" value="{{{data.receiverId}}}" />
          <input type="hidden" name="senderId" value="{{{data.senderId}}}" />
          <input type="hidden" name="customerName" value="{{{data.customerName}}}" />
          <input type="hidden" name="customerImage" value="{{{data.customerImage}}}" />
          <div class="mpbs-message-box">
              <textarea name="message" id="mpbs-chatbox-text" rows="1" placeholder="<?php echo __('Write...', 'mp_buyer_seller_chat'); ?>"></textarea>
              <span class="dashicons dashicons-smiley mpbs-emoticons-button"></span>
              <div class="mpbs-emoticons-container">
                  <div class="mpbs-smiley-wrapper">
                      <div class="mpbs-smiley-pad">
                          :smiley: :smile: :wink: :blush: :angry:
                      </div>
                      <div class="mpbs-smiley-pad">
                          :laughing: :smirk: :disappointed: :sleeping: :rage:
                      </div>
                      <div class="mpbs-smiley-pad">
                           :cry: :yum: :neutral_face: :astonished: :sunglasses:
                      </div>
                      <div class="mpbs-smiley-pad">
                        :stuck_out_tongue_winking_eye: :confused: :scream: :stuck_out_tongue: :fearful:
                      </div>
                      <div class="mpbs-smiley-pad">
                          :punch: :ok_hand: :clap: :thumbsup: :thumbsdown:
                      </div>
                  </div>
              </div>
          </div>

      </form>
  </div>
</script>

<script id="tmpl-mpbs_reply_template_customer" type="text/html">
  <li class="{{{data.class}}}">
    <div class="mpbs-avatar">
      <img src="{{{data.image}}}" />
    </div>
    <div class="mpbs-message">
      <p>{{{data.message}}}</p>
      <time>{{{data.datetime}}}</time>
    </div>
  </li>
</script>
