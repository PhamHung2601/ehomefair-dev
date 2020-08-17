<?php

/**
 * @author Webkul
 * @implements Assets_Interface
 */

namespace WpMarketplaceBuyerSellerChat\Includes\Front;

use WpMarketplaceBuyerSellerChat\Includes\Front\Util;
use WpMarketplaceBuyerSellerChat\Helper;

if (!defined('ABSPATH')) {
    exit;
}

if (! class_exists('Mpbs_Script_Loader')) {
    class Mpbs_Script_Loader implements Util\Assets_Interface
    {
        public function __construct()
        {
            $this->coreConfig = new Helper\Mpbs_Core_Config();
        }
        /**
        *
        */
        public function wkcInit()
        {
            add_action('wp_enqueue_scripts', array($this, 'wkcEnqueueScripts'));
            add_action('admin_enqueue_scripts', array($this, 'wkcEnqueueScripts'));
        }
        /**
        * Front scripts and style enqueue
        */
        public function wkcEnqueueScripts()
        {
            wp_enqueue_media();

            wp_enqueue_style('mpbs_style', MPBS_URL . 'assets/css/style.css', '', MPBS_SCRIPT_VERSION);

            wp_enqueue_script('socket', MPBS_URL . 'assets/js/socket.io.js');

            wp_enqueue_script('mpbs_script', MPBS_URL . 'assets/js/plugin.js', array( 'jquery' ), MPBS_SCRIPT_VERSION);

            wp_localize_script('mpbs_script', 'chatboxCoreConfig', $this->coreConfig->mpbs_get_core_config());

            wp_localize_script('mpbs_script', 'enabledCustomerList', $this->coreConfig->mpbs_get_enabled_customer_list());

            wp_localize_script('mpbs_script', 'chatboxAjax', array(
                'url'   => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mpbs-front-ajax-nonce'),
                'pluginsUrl' => MPBS_URL
            ));

            $this->mpbs_add_default_stylesheet();
        }

        function mpbs_add_default_stylesheet(){

            $colors = $this->get_default_colors();

            echo '<style>';
            ?>
            .mpbs-active-users .mpbs-chat-menu .mpbs-panel-control{
                border-color: <?php echo $colors['admin']['strip'];?> !important;
            }
            .mpbs-active-users .mpbs-chat-menu .mpbs-heading{
                background-color: <?php echo $colors['admin']['strip'];?> !important;
                border-bottom-color: <?php echo $colors['admin']['strip'];?> !important;
            }
            .mpbs-chatbox-container .mpbs-chatbox-top-bar, #mpbs-chat-window-container .mpbs-chatbox-container .mpbs-chatbox-top-bar{
                background-color: <?php echo $colors['customer']['strip'];?> !important;
            }
            .mpbs-active-users .mpbs-chatbox-container > .mpbs-chatbox-top-bar{
                background-color: #fff !important;
            }
            .mpbs-chatbox-container .mpbs-thread-container .mpbs-discussion li.self .mpbs-message{
                background-color: <?php echo $colors['sender']['bgcolor'];?> !important;
            }
            .mpbs-chatbox-container .mpbs-thread-container .mpbs-discussion li.self .mpbs-message p{
                color: <?php echo $colors['sender']['text'];?> !important;
            }
            .mpbs-chatbox-container .mpbs-thread-container .mpbs-discussion li.self .mpbs-message time{
                color: <?php echo $colors['sender']['time'];?> !important;
            }

            .mpbs-chatbox-container .mpbs-thread-container .mpbs-discussion li.other .mpbs-message{
                background-color: <?php echo $colors['receiver']['bgcolor'];?> !important;

            }
            .mpbs-chatbox-container .mpbs-thread-container .mpbs-discussion li.other .mpbs-message p{
                color: <?php echo $colors['receiver']['text'];?> !important;

            }
            .mpbs-chatbox-container .mpbs-thread-container .mpbs-discussion li.other .mpbs-message time{
                color: <?php echo $colors['receiver']['time'];?> !important;

            }
            .mpbs-chatbox-container .mpbs-thread-container .mpbs-discussion li.other .mpbs-message:before{
                border-color: <?php echo $colors['receiver']['bgcolor'];?> !important;
                border-left-color: transparent !important;
                border-bottom-color: transparent !important;
            }
            .mpbs-chatbox-container .mpbs-thread-container .mpbs-discussion li.self .mpbs-message:before{
                border-color: <?php echo $colors['sender']['bgcolor'];?> !important;
                border-right-color: transparent !important;
                border-bottom-color: transparent !important;
            }
            .mpbs-active-users .mpbs-chat-menu {
                border-color: <?php echo $colors['admin']['strip'];?> !important;
            }
            .mpbs-chatbox-container .mpbs-chat-controls {
                border-color: <?php echo $colors['customer']['strip'];?> !important;
            }
            .mpbs-active-users .mpbs-chat-menu .mpbs-chatbox-container .mpbs-chat-controls {
                border-bottom-color: <?php echo $colors['admin']['strip'];?> !important;
            }
            .mpbs-chatbox-container .mpbs-start-chat-container, .mpbs-chatbox-container .mpbs-thread-container {
                border-color: <?php echo $colors['customer']['strip'];?> !important;
            }
            .mpbs-chatbox-container .mpbs-input-area {
                border-color: <?php echo $colors['customer']['strip'];?> !important;
            }
            <?php
            echo '</style>';
        }

        function get_default_colors() {

            $admin_chatbox = get_option( 'mpbs_admin_chat_stript_color', '#96588a');
            $customer_chatbox = get_option( 'mpbs_customer_chat_stript_color', '#96588a' );
            $sender_text_color = get_option( 'mpbs_sender_text_color', '#fff');
            $receiver_text_color = get_option( 'mpbs_receiver_text_color', '#96588a' );
            $sender_chatbg_color = get_option( 'mpbs_sender_chat_bgcolor', '#96588a');
            $receiver_chatbg_color = get_option( 'mpbs_receiver_chat_bgcolor', 'rgba(150,88,138,.05)' );
            $sender_chattime_color = get_option( 'mpbs_sender_chat_timetxtcolor', '#fff');
            $receiver_chattime_color = get_option( 'mpbs_receiver_chat_timetxtcolor', '#999' );

            $colors = array(
                'sender' => array(
                    'text' => $sender_text_color,
                    'bgcolor' => $sender_chatbg_color,
                    'time' => $sender_chattime_color,
                ),
                'receiver' => array(
                    'text' => $receiver_text_color,
                    'bgcolor' => $receiver_chatbg_color,
                    'time' => $receiver_chattime_color,
                ),
                'admin' => array(
                    'strip' => $admin_chatbox
                ),
                'customer' => array(
                    'strip' => $customer_chatbox
                )
            );

            return $colors;
        }

    }
}
