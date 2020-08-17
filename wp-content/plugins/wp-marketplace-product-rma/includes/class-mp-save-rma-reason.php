<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('MP_Save_Rma_Reason')) {
    class MP_Save_Rma_Reason
    {
        public function __construct()
        {
            add_action('mp_save_reason_rma', array($this, 'mp_save_reason_rma'), 1);
        }

        public function mp_save_reason_rma($data)
        {
            global $wpdb;

            if (!isset($data['rma_reason_nonce']) || !wp_verify_nonce($data['rma_reason_nonce'], 'rma_reason_nonce_action')) {
                echo esc_html__('Sorry, your nonce did not verify.', 'marketplace-rma');
                exit;
            } else {
                if (!empty($data['wk_rma_reason']) && !empty($data['wk_rma_status'])) {
                    $reason = $data['wk_rma_reason'];
                    $status = $data['wk_rma_status'];
                    $table_name = $wpdb->prefix . 'mp_rma_reasons';
                    $user_id = apply_filters('mp_rma_user_id', 'user_id');
                    $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option('wkmp_seller_page_title') . "'");

                    if (empty($data['reason_id'])) {
                        $wpdb->insert(
                            $table_name,
                            array(
                                'user_id' => $user_id,
                                'reason' => $reason,
                                'status' => $status,
                            )
                        );
                        $msg = __( 'Reason Added Successfully.', 'marketplace-rma' );
                        $action = 'added';

                    } else {
                        $wpdb->update(
                            $table_name,
                            array(
                                'user_id' => $user_id,
                                'reason' => $reason,
                                'status' => $status,
                            ),
                            array(
                                'id' => $data['reason_id'],
                            )
                        );
                        $msg = __( 'Reason Updated Successfully.', 'marketplace-rma' );
                        $action = 'updated';
                    }

                    if (!is_admin()) {
                        wc_add_notice( $msg );
                        wp_redirect(site_url() . '/' . $page_name . '/rma-reason');
                        exit;
                    } else {
                        wp_redirect(site_url() . '/wp-admin/admin.php?page=mp-rma-reasons&action=' . $action );
                        exit;
                    }
                } else {
                    if( is_admin() ) {

                        ?>
                        <div class='notice notice-error is-dismissible'>
                            <p><?php esc_html_e('All fields are required.', 'marketplace-rma'); ?></p>
                        </div>
                        <?php

                    } else {
                        wc_print_notice( __('All fields are required.', 'marketplace-rma'), 'error' );
                    }
                }
            }
        }
    }

    new MP_Save_Rma_Reason();
}
