<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('MP_Manage_Front_Rma')) {
    class MP_Manage_Front_Rma
    {
        public static $endpoint = 'rma';

        public function __construct()
        {
            add_action('woocommerce_account_'.self::$endpoint.'_endpoint', array($this, 'mp_rma_endpoint_content'));
        }

        public function mp_rma_endpoint_content()
        {
            global $wp_query, $wpdb;
            $table_name = $wpdb->prefix.'mp_rma_requests';
            $user_id = apply_filters('mp_rma_user_id', 'user_id');
            $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='".get_option('wkmp_seller_page_title')."'");

            $rma_stat = array(
                'processing' => esc_html__('Approve', 'marketplace-rma'),
                'declined' => esc_html__('Decline', 'marketplace-rma'),
                'solved' => esc_html__('Solved', 'marketplace-rma'),
                'pending' => esc_html__('Pending', 'marketplace-rma'),
                'cancelled' => esc_html__('Cancelled', 'marketplace-rma'),
            );

            if (isset($wp_query->query_vars['rma']) && ($wp_query->query_vars['rma'] == 'rma' || (!empty($wp_query->query_vars['rma']) && is_numeric($wp_query->query_vars['rma'])))) {
                $mp_rma_data = apply_filters('mp_get_rma_data_by_customer', $user_id, $wp_query->query_vars['rma']);
                $wk_data = $mp_rma_data['data']; 
                
                if( !empty( $_REQUEST['rma-select-status'] ) ) {
                    $rma_status = $_REQUEST['rma-select-status'];
                }
                else {
                    $rma_status = '';
                }
                
                ?>
                <form method="get" class="alignleft">

                    <select name="rma-select-status" id="rma-select-status" class="ewc-filter-rma-status" style="max-width:200px; padding: 11px;" class="form-control">

                        <option value=""><?php echo __('Select status', 'marketplace-rma'); ?></option>
                        <option value="pending" <?php echo $rma_status == 'pending' ? 'selected' : '' ?>><?php echo __('Pending', 'marketplace-rma'); ?></option>
                        <option value="processing" <?php echo $rma_status == 'processing' ? 'selected' : '' ?>><?php echo __('Processing', 'marketplace-rma'); ?></option>
                        <option value="solved" <?php echo $rma_status == 'solved' ? 'selected' : '' ?>><?php echo __('Solved', 'marketplace-rma'); ?></option>
                        <option value="declined" <?php echo $rma_status == 'declined' ? 'selected' : '' ?>><?php echo __('Declined', 'marketplace-rma'); ?></option>

                    </select>

                <input type="submit" class="button-primary" value="<?php _e('Select RMA Status', 'marketplace-rma'); ?>">

                </form>

                <?php if ($wk_data || !empty( $_REQUEST['rma-select-status'] )): ?>
                <a href="../../<?php echo $page_name; ?>/rma/add" class="woocommerce-button button mp_rma_add_rma_button" title="<?php esc_attr_e( 'Request New RMA', 'marketplace-rma' ); ?>"><?php esc_html_e('Add', 'marketplace-rma'); ?></a>
                <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
                		<thead>
                			<tr>
        									<th class="woocommerce-orders-table__header woocommerce-orders-table__header-id"><span class="nobr"><?php esc_html_e('ID', 'marketplace-rma'); ?></span></th>
        									<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-order-id"><span class="nobr"><?php esc_html_e('Order ID', 'marketplace-rma'); ?></span></th>
        									<th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-status"><span class="nobr"><?php esc_html_e('RMA Status', 'marketplace-rma'); ?></span></th>
                          <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-date"><span class="nobr"><?php esc_html_e('Date', 'marketplace-rma'); ?></span></th>
                          <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-actions"><span class="nobr"><?php esc_html_e('Action', 'marketplace-rma'); ?></span></th>
        							</tr>
                		</thead>

                		<tbody>
											<?php foreach ($wk_data as $key => $value): ?>

                	     	<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-cancelled order">
                               <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-id" data-title="ID">
                    					    <?php echo $value->id; ?>
                    					 </td>
                    					 <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-id" data-title="Order ID">
                    						 	<a href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id')); ?>view-order/<?php echo $value->order_no; ?>">#<?php echo $value->order_no; ?></a>
                    					 </td>
                    					<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-status" data-title="Status">
                                  <strong class="wk_rma_status_<?php echo $value->rma_status; ?>"><?php echo $rma_stat[$value->rma_status]; ?></strong>
                              </td>
                              <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-date" data-title="Date">
                    					  	<time datetime=""><?php echo date('F j, Y H:i:s', strtotime($value->datetime)); ?></time>
                              </td>
                              <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions" data-title="Actions">
                                  <a href="<?php echo site_url('/').$page_name; ?>/rma/edit/<?php echo $value->id; ?>" class="rma-action view"><?php esc_html_e('View', 'marketplace-rma'); ?></a>
                                  <?php if ($value->rma_status != 'cancelled' && $value->rma_status != 'solved'): ?>
                                    <span class="rma-action"> | </span>
                    					  	  <a href="" data-rma-id="<?php echo $value->id; ?>" class="mp-rma-action cancel"><?php esc_html_e('Cancel', 'marketplace-rma'); ?></a>
                                  <?php endif; ?>
                                  <?php if ($value->rma_status == 'declined' && $value->rma_status != 'cancelled' && $value->rma_status != 'solved'): ?>
                    				<span class="rma-action"> | </span>
									<a href="" data-rma-id="<?php echo $value->id; ?>" class="rma-action reopen" data-rma-status="pending"><?php echo __('Re-Open', 'marketplace-rma'); ?></a>
                                  <?php endif;?>
                              </td>
                					</tr>
										<?php endforeach; ?>
                		</tbody>
                </table>
                <?php
echo $mp_rma_data['count']; else:
                ?>
								<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info"><a class="woocommerce-Button button" href="../../<?php echo $page_name; ?>/rma/add"><?php esc_html_e('Add', 'marketplace-rma'); ?></a><?php esc_html_e('No RMA has been made yet', 'marketplace-rma'); ?>.</div>
								<?php
endif;
            } else {
                ?>
							<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info"><a class="woocommerce-Button button" href="<?php echo wc_get_endpoint_url('rma'); ?>"><?php esc_html_e('Back', 'marketplace-rma'); ?></a><?php echo __('Not found.', 'marketplace-rma'); ?></div>
							<?php
            }
        }
    }

    new MP_Manage_Front_Rma();
}
