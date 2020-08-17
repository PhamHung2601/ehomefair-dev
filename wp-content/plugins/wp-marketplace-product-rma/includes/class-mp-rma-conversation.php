<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( 'MP_Rma_Conversation' ) )
{
    /**
     *
     */
    class MP_Rma_Conversation
    {

        function __construct()
        {
            add_action( 'mp_rma_view_conversation', array( $this, 'mp_customer_rma_conversation' ) );
        }

        function mp_customer_rma_conversation()
        {
            global $wp_query;

            $rma_id   = apply_filters( 'mp_rma_id', 'rma_id' );

            $user_id  = apply_filters( 'mp_rma_user_id', 'user_id' );

            if ( isset( $_POST['wk_send_message'] ) )
            {
                do_action( 'mp_save_rma_conversation', $_POST );
            }

            $wk_data = apply_filters( 'mp_get_rma_conversation', $rma_id );

            ?>

            <p><label for="textarea"><strong><?php esc_html_e('Chat', 'marketplace-rma'); ?> :</strong></label></p>

            <!-- conversation -->
            <?php if ( $wk_data['data'] ): ?>

                <div class="wk_conversation_wrapper">
                    <div class="quote-body">

                        <?php foreach (array_reverse($wk_data['data']) as $key => $value): ?>
                            <?php if ( $value->user_id != $user_id ) : ?>
                            <!-- // customer-comment -->
                            <div class="quote-comment floated-left cs-customer">
                                <div class="comment-body">
                                    <p><?php echo $value->message; ?></p>
                                </div>
                                <div class="comment-footer">
                                    <div class="comment-date">
                                        <time datetime=""><?php echo date( 'F j, Y H:i:s', strtotime($value->datetime)); ?></time>
                                    </div>
                                    <span class="cs-customer"><?php echo !empty( get_userdata( $value->user_id ) ) ? get_userdata( $value->user_id )->display_name : ''; ?></span>
                                </div>
                            </div>
                          <?php else : ?>
                            <!-- // my-comment -->
                            <div class="quote-comment floated-right cs-me">
                                <div class="comment-body">
                                    <p><?php echo $value->message; ?></p>
                                </div>
                                <div class="comment-footer">
                                    <div class="comment-date">
                                        <time datetime="2017-06-02 07:11:46"><?php echo date( 'F j, Y H:i:s', strtotime($value->datetime)); ?></time>
                                    </div>
                                    <span class="cs-me"><?php esc_html_e('Me', 'marketplace-rma'); ?></span>
                                </div>
                            </div>
                          <?php endif; ?>

                        <?php endforeach; ?>


                    </div>
                    <?php echo $wk_data['count']; ?>
                </div>

            <?php endif; ?>

            <form action="" id="rma-chat-form" method="post">
                <p><textarea name="mp_rma_message" class="message-box" rows="3"></textarea></p>
                <?php wp_nonce_field( 'rma_chat_nonce_action', 'rma_chat_nonce' ); ?>
                <p><input type="submit" value="<?php esc_html_e('Send', 'marketplace-rma'); ?>" class="button button-primary" name="wk_send_message" /></p>
            </form>

            <?php
        }

    }

    new MP_Rma_Conversation();

}
