<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('MP_View_Front_Rma')) {
    /**
     *
     */
    class MP_View_Front_Rma
    {

        public function mp_front_view_rma()
        {
            global $wp_query;
            $rma_id = apply_filters('mp_rma_id', 'rma_id');
            $wk_data = apply_filters('mp_get_rma_data', $rma_id);
            $user_id = apply_filters('mp_rma_user_id', 'user_id');

            if (!$wk_data || ($wk_data[0]->customer_id != $user_id && $wk_data[0]->seller_id != $user_id)) {
                echo '<div class="woocommerce-error">Invalid RMA. <a href="' . get_permalink(get_option("woocommerce_myaccount_page_id")) . '" class="wc-forward">' . esc_html__('My account', 'marketplace-rma') . '</a></div>';
                exit;
            }

            ?> <div class="woocommerce-account"> <?php

            apply_filters('mp_get_wc_account_menu', 'marketplace');

            ?>

                <div class="woocommerce-MyAccount-content">

				            <ul id='mp_rma_details_tab'>

				          	    <li><a id='details_tab'><?php echo _e("RMA Details", "marketplace-rma"); ?></a></li>

				                <li><a id='products_tab' class="inactive"><?php echo _e("Products", "marketplace-rma"); ?></a></li>

				                <li><a id='images_tab' class="inactive"><?php echo _e("RMA Images", "marketplace-rma"); ?></a></li>

				          	   	<li><a id='update_manage_tab' class="inactive"><?php echo _e("Manage", "marketplace-rma"); ?></a></li>

				            </ul>

				            <div class="wk_mp_rma_container" id="details_tab_wk">
				                <?php do_action('mp_rma_view_details');?>
				                <?php do_action('mp_rma_view_conversation');?>
				            </div>
				            <div class="wk_mp_rma_container" id="products_tab_wk">
				                <?php do_action('mp_rma_view_products');?>
				            </div>
				            <div class="wk_mp_rma_container" id="images_tab_wk">
				                <?php do_action('mp_rma_view_images');?>
				            </div>
				            <div class="wk_mp_rma_container" id="update_manage_tab_wk">
				                <?php
if ($wk_data[0]->seller_id != $user_id):
                do_action('mp_rma_update_manage', $rma_id);
            else:
                do_action('mp_rma_view_seller_shipping_label', $rma_id);
            endif;
            ?>
				            </div>
								</div>
						</div>
            <?php

        }

    }

}
