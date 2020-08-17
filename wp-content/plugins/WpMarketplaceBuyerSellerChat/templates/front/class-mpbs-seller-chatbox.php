<?php

/**
 * @author Webkul
 * @implements Seller_Chatbox_Interface
 */

namespace WpMarketplaceBuyerSellerChat\Templates\Front;

use WpMarketplaceBuyerSellerChat\Templates\Front\Util;

use WpMarketplaceBuyerSellerChat\Helper;

if (!defined('ABSPATH')) {
    exit;
}

if (! class_exists('Mpbs_Seller_Chatbox')) {
    class Mpbs_Seller_Chatbox implements Util\Seller_Chatbox_Interface
    {
        /**
         * Seller chatbox
         */
        public function mpbs_seller_chatbox()
        {
            $helper = new Helper\Mpbs_Data();

            $user_id = $helper->mpbs_get_current_user_id();

            $image_src = $helper->mpbs_get_user_image_by_id($user_id);
            ?>
            <div class="mpbs-chat-menu">
                <div class="mpbs-panel-control"></div>

                <div class="mpbs-heading">
                    <p><?php echo get_option( 'mpbs_chat_name' ); ?></p>
                </div>

                <div class="mpbs-chatbox-container">
                    <div class="mpbs-chat-controls mpbs-chatbox-top-bar">

                        <div class="mpbs-profile-image">
                          <img src="<?php echo $image_src; ?>" height="35" width="35">
                          <span class="mpbs-self-status">
                            <div class="status" style="float:none;"></div>
                          </span>
                        </div>


                        <div class="mpbs-user-select-status seller">

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
                    </div>
                </div>

                <ul class="mpbs-chat-customer-list"></ul>
            </div>

            <div id="mpbs-chat-window-container"></div>

            <script id="tmpl-mpbs_customer_chat_window" type="text/html">
                <div class="mpbs-chatbox-container open" id="mpbs-chat-window-{{{data.receiverId}}}">
                      <header class="mpbs-chatbox-top-bar">
                          <span class="mpbs_chat_status"><div class="status {{{data.status}}}" style="float:none;"></div></span>

                          <p class="mpbs-label">{{{data.chatName}}}</p>

                          <div class="mpbs-box-controls close"></div>

                          <div class="mpbs-box-controls maximize minimize"></div>
                      </header>


                      <div class="mpbs-chat-controls" style="">

                                    <div class="mpbs-profile-image">
                                    <img src="<?php echo $image_src; ?>" height="35" width="35">
                                    </div>



                                    <div class="mpbs-previous-history">

                                        <div class="mpbs-history-clock-seller">

                                            <ul class="mpbs-history-options-seller mpbs-controls-option-box">
                                                <span class="mpbs-status-point"></span>

                                                <li class="mpbs-history-options-1" data-value="1" data-customerId="{{{data.receiverId}}}" data-sellerId ="{{{data.senderId}}}"><?php echo esc_html__( 'Last 24-hrs', 'mp_buyer_seller_chat' ); ?></li>
                                                <li class="mpbs-history-options-2" data-value="2" data-customerId="{{{data.receiverId}}}" data-sellerId ="{{{data.senderId}}}"><?php echo esc_html__( 'Last 7-days', 'mp_buyer_seller_chat' ); ?></li>
                                                <li class="mpbs-history-options-3" data-value="3" data-customerId="{{{data.receiverId}}}" data-sellerId ="{{{data.senderId}}}"><?php echo esc_html__( 'Last 30-days', 'mp_buyer_seller_chat' ); ?></li>
                                                <li class="mpbs-history-options-4" data-value="4" data-customerId="{{{data.receiverId}}}" data-sellerId ="{{{data.senderId}}}"><?php echo esc_html__( 'Forever', 'mp_buyer_seller_chat' ); ?></li>
                                            </ul>

                                        </div>

                                    </div>

                                    </div>






                      <div class="mpbs-thread-container"><ul class="mpbs-discussion"></ul></div>
                      <div class="mpbs-input-area">
                          <form method="post" id="mpbs-form-reply-{{{data.receiverId}}}" class="mpbs-form-reply-seller">
                              <input type="hidden" name="class" value="other">
                              <input type="hidden" name="receiverId" value="{{{data.receiverId}}}">
                              <input type="hidden" name="senderId" value="{{{data.senderId}}}">
                              <input type="hidden" name="customerName" value="{{{data.chatName}}}">
                              <input type="hidden" name="sellerImage" value="{{{data.sellerImage}}}" />
                              <div class="mpbs-message-box">
                                  <textarea name="message" class="mpbs-chatbox-text-seller" rows="1" placeholder="<?php esc_attr_e('Write...', 'mp_buyer_seller_chat'); ?>"></textarea>
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
                  </div>
            </script>

            <script id="tmpl-mpbs_reply_template" type="text/html">
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
            <?php
        }
    }
}
