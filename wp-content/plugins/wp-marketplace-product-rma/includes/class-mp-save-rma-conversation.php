<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'MP_Save_Rma_Conversation' ) )
{
    /**
     *
     */
    class MP_Save_Rma_Conversation
    {

        function __construct()
        {
            add_action( 'mp_save_rma_conversation', array( $this, 'mp_save_rma_conversation' ), 1 );
        }

        function mp_save_rma_conversation( $data )
        {

            global $wpdb, $wp_query;

            if ( ! isset( $data['rma_chat_nonce'] ) || ! wp_verify_nonce( $data['rma_chat_nonce'], 'rma_chat_nonce_action' )  )
            {
                if ( isset( $_GET['rid'] ) ) :
                ?>
                <div class="notice notice-error">
                    <p><?php _e( 'Sorry, your nonce did not verify.', 'marketplace-rma' ); ?></p>
                </div>
                <?php
                else :
								wc_add_notice( __('Sorry, your nonce did not verify.', 'marketplace-rma'), 'error' );
								wp_redirect($_SERVER['HTTP_REFERER']);
								exit;
                endif;
            }
            else if ( empty( $data['mp_rma_message'] ) )
            {
                if ( isset( $_GET['rid'] ) ) :
                ?>
                <div class="notice notice-error">
                    <p><?php _e( 'Empty message!', 'rma' ); ?></p>
                </div>
                <?php
                else :
									wc_add_notice( __('Empty message!', 'marketplace-rma'), 'error' );
									wp_redirect($_SERVER['HTTP_REFERER']);
									exit;
                endif;
            }
            else
            {
                $message   = strip_tags($data['mp_rma_message']);
                $user_id   = apply_filters( 'mp_rma_user_id', 'user_id' );
                $rma_id   = apply_filters( 'mp_rma_id', 'rma_id' );

                $attachmentPaths = '';

                $table_name = $wpdb->prefix.'mp_rma_conversation';

                $sql = $wpdb->insert(
                    $table_name,
                    array(
                        'rma_id'      => $rma_id,
                        'user_id'     => $user_id,
                        'message'     => $message,
                        'attachment'  => $attachmentPaths
                    )
                );

                if ( $sql )
                {
                    if ( isset( $_GET['rid'] ) ) :
                    ?>
                    <div class="notice notice-success">
                        <p><?php _e( 'Message sent!', 'rma' ); ?></p>
                    </div>
                    <?php
                    else :
											wc_add_notice( __('Message sent!', 'marketplace-rma'), 'success' );
											wp_redirect($_SERVER['HTTP_REFERER']);
											exit;
                    endif;
                    ?>
                    <script>
                        jQuery(document).ready(function() {
                            jQuery( 'html, body' ).animate({
                                scrollTop: jQuery("#rma-chat-form").offset().top
                            }, 1500);
                        });
                    </script>
                    <?php
                }

            }
        }

    }

    new MP_Save_Rma_Conversation();

}
