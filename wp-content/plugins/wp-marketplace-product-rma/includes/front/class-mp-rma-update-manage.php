<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( 'MP_Rma_Update_Manage' ) )
{
    /**
     *
     */
    class MP_Rma_Update_Manage
    {

        function __construct()
        {
            add_action( 'mp_rma_update_manage', array( $this, 'mp_rma_update_manage' ), 1 );
        }

        function mp_rma_update_manage( $rma_id )
        {
            global $wpdb;
            $table = $wpdb->prefix.'mp_rma_requests';
            $wk_data = apply_filters( 'mp_get_rma_data', $rma_id );
            $meta_table = $wpdb->prefix.'mp_rma_request_meta';
            $result = $wpdb->get_results("Select meta_value from $meta_table where meta_key = 'shipping_label' and rma_id = '$rma_id'");
						$page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='".get_option('wkmp_seller_page_title')."'");

            if ( $wk_data[0]->rma_status == 'processing' )
            {
                echo '<p><a href="'.site_url().'/'.$page_name.'/rma-print/edit/'.$rma_id.'" target="_blank" class="rma-action">Print Shipping Label</a></p>';
            }

            if ( isset( $_POST['rma_solved_submit'] ) )
            {
                if ( isset( $_POST['wk_mark_solved'] ) )
                {
                    $sql = $wpdb->update( $table,
                        array(
                          'rma_status'  => 'solved'
                        ),
                        array(
                          'id'  => $rma_id
                        )
                    );
                    if ( $sql ) {
                        $message = array();
                        $message[] = __('RMA status changed to "solved" by customer for order', 'marketplace-rma').' #'.$wk_data[0]->order_no."\n";
                        $message[] = __('RMA ID', 'marketplace-rma').' : '.$rma_id."\n\n";
                        $data = array(
                            'msg'=>$message,
                            'email'=>get_option('admin_email'),
                        );
                        do_action('woocommerce_mp_rma_mail', $data);

                        // wp_mail( get_option('admin_email'), 'RMA Status Changed', $message );
                        wp_redirect( site_url('/my-account/rma/view/'.$rma_id));
                        exit;
                    }
                }
            }
            if ( $wk_data[0]->rma_status != 'solved' ) :
            ?>
            <form action="" method="post">
                <input name="wk_mark_solved" id="wk_mark_solved" type="checkbox" class="wk_rma_checkall" />
                <label for="wk_mark_solved"><span><?php esc_html_e('Check this to mark as solved', 'marketplace-rma'); ?>.</span></label></p>
                <input type="submit" name="rma_solved_submit" value="<?php esc_html_e('Update', 'marketplace-rma');?>" class="button" />
            </form>
            <?php
            endif;
        }

    }

    new MP_Rma_Update_Manage();

}
