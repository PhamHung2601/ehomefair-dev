<?php
/**
 * Class set commission.
 */
class SetCommition
{

    /**
     * Constructor function.
     */
    public function __construct()
    {

        add_action('mp_manage_seller_commission', array($this, 'mp_manage_seller_commission'), 10, 1);

        add_action('mp_manage_seller_orders', array($this, 'mp_manage_seller_orders'));

        add_action('mp_manage_seller_transactions', array($this, 'mp_manage_seller_transactions'));

        add_action('mp_manage_seller_details', array($this, 'mp_manage_seller_details'));

        add_action('mp_manage_seller_assign_category', array($this, 'mp_manage_seller_assign_category'));

        // Nav tabs.
        echo '<div class="wrap">';

        $seller_id = '';

        if (isset($_GET['sid']) && !empty($_GET['sid'])) {
            $seller_id = $_GET['sid'];
        }

        if (!empty($seller_id)) {
            $user = get_user_by('ID', $seller_id);
            if ($user && in_array('wk_marketplace_seller', $user->roles, true)) {
                $ds = get_user_meta($seller_id, 'first_name', true) ? (get_user_meta($seller_id, 'first_name', true) . ' ' . get_user_meta($seller_id, 'last_name', true)) : $user->user_nicename;
                ?><h1 class="wp-heading-inline">
					<?php echo sprintf(esc_html__('Seller', 'marketplace') . '- %s', esc_html($ds)); ?></h1>
					<?php

                echo '<nav class="nav-tab-wrapper">';
                $mp_tabs = array(
                    'details' => __('Details', 'marketplace'),
                    'orders' => __('Orders', 'marketplace'),
                    'transactions' => __('Transactions', 'marketplace'),
                    'commission' => __('Commission', 'marketplace'),
                    'assign_category' => __('Assign Category', 'marketplace'),
                );

                $mp_tabs = apply_filters('marketplace_get_seller_settings_tabs', $mp_tabs);

                $current_tab = empty($_GET['tab']) ? 'orders' : sanitize_title($_GET['tab']);

                $this->id = $current_tab;

                foreach ($mp_tabs as $name => $label) {
                    echo '<a href="' . esc_url(admin_url('admin.php?page=sellers&action=set&tab=' . $name . '&sid=' . $seller_id)) . '" class="nav-tab ' . ($current_tab === $name ? 'nav-tab-active' : '') . '">' . esc_html($label) . '</a>';
                }

                ?>
			</nav>

			<h1 class="screen-reader-text">
				<?php echo esc_html($mp_tabs[$current_tab]); ?>
			</h1>

			<?php

                do_action('mp_manage_seller_' . $current_tab, $seller_id);
            } else {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p>' . esc_html__('Invalid Seller ID.!', 'marketplace') . '</p>';
                echo '</div>';
            }
        }

        echo '</div>';

    }

    /**
     * Manage seller commission.
     *
     * @param int $seller_id seller id.
     */
    public function mp_manage_seller_commission($seller_id)
    {

        global $wpdb;

        $sid = (int) $seller_id;

        if ($sid) {

            if (isset($_POST['commision'])) {

                $commision = (int) $_POST['commision'];

                if ($commision >= 0 && $commision <= 100) {

                    $sql = $wpdb->update(
                        "{$wpdb->prefix}mpcommision",
                        array(
                            'commision_on_seller' => $commision,
                        ),
                        array(
                            'seller_id' => $sid,
                        ),
                        array(
                            '%d',
                        ),
                        array('%d')
                    );

                    if ($sql) {

                        echo '<div class="notice notice-success is-dismissible">';
                        echo '<p>' . esc_html__('Commision Rate is updated.!', 'marketplace') . '</p>';
                        echo '</div>';

                    } else {

                        echo '<div class="notice notice-error is-dismissible">';
                        echo '<p>' . esc_html__('Please update the commission rate', 'marketplace') . '</p>';
                        echo '</div>';
                    }
                } else {

                    echo '<div class="notice notice-error is-dismissible">';
                    echo '<p>' . esc_html__('Commision value is invalid.!', 'marketplace') . '</p>';
                    echo '</div>';

                }
            }

            $com_res = $wpdb->get_results($wpdb->prepare("select * from {$wpdb->prefix}mpcommision  where seller_id=%d", $sid));

            $cur_symbol = get_woocommerce_currency_symbol(get_option('woocommerce_currency'));

            if (!$com_res) {
                $com_on_seller = get_option('wkmpcom_minimum_com_onseller');
                $wpdb->insert($wpdb->prefix . 'mpcommision', array(
                    'id' => '',
                    'seller_id' => $sid,
                    'commision_on_seller' => $com_on_seller,
                    'admin_amount' => 0,
                    'seller_total_ammount' => 0,
                    'paid_amount' => 0,
                    'last_paid_ammount' => 0,
                    'last_com_on_total' => 0,
                    'total_refunded_amount' => 0,
                ));
                $com_res = $wpdb->get_results($wpdb->prepare("select * from {$wpdb->prefix}mpcommision  where seller_id=%d", $sid));
            }

            if (is_admin()) {

                require_once WK_MARKETPLACE_DIR . 'includes/templates/admin/account/seller/manage-commission.php';

            }
        } else {

            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>' . esc_html__('Commision id is not valid.!', 'marketplace') . '</p>';
            echo '</div>';
        }
    }

    /**
     * Column created.
     *
     * @param int $seller_id seller id.
     */
    public function mp_manage_seller_orders($seller_id)
    {
        mp_manage_seller_orders($seller_id);
    }

    /**
     * Column created.
     *
     * @param int $seller_id seller_id.
     */
    public function mp_manage_seller_transactions($seller_id)
    {
        global $transaction, $commission;
        $seller_transaction = $transaction->get($seller_id);

        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $transaction_id = $_GET['id'];
            $transaction_detail = $transaction->get_by_id($transaction_id, $seller_id);
            $admin_rate = $commission->get_admin_rate($seller_id);
            extract($transaction_detail);
            $columns = apply_filters('mp_account_transactions_columns', array(
                'order-id' => __('Order Id', 'woocommerce'),
                'product-name' => __('Product Name', 'woocommerce'),
                'product-quantity' => __('Qty', 'woocommerce'),
                'total-price' => __('Order Total', 'woocommerce'),
                'commission' => __('Commission', 'woocommerce'),
                'subtotal' => __('Transaction Total', 'woocommerce'),
            ));
            if (!empty($transaction_detail)) {
                require_once WK_MARKETPLACE_DIR . 'includes/templates/admin/account/seller/transaction/view.php';
            } else {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p>Invalid Transaction.!</p>';
                echo '</div>';
            }
        } else {
            require_once WK_MARKETPLACE_DIR . 'includes/admin/account/seller/transactions.php';
        }
    }

    /**
     * Manage seller details.
     *
     * @param int $seller_id seller id.
     */
    public function mp_manage_seller_details($seller_id)
    {
        require_once WK_MARKETPLACE_DIR . 'includes/templates/admin/account/seller/profile.php';
    }

    /**
     * Manage seller assigned categories.
     *
     * @param int $seller_id seller id.
     */
    public function mp_manage_seller_assign_category($seller_id)
    {

        if (isset($_POST['mp_submit_assign_category'])) {

            $cats = isset($_POST['wkmp_seller_allowed_categories']) ? $_POST['wkmp_seller_allowed_categories'] : '';

            $true = update_user_meta($seller_id, 'wkmp_seller_allowed_categories', $cats);

            if ($true) {
                echo '<div class="notice notice-success is-dismissible">';
                ?>
					<p>
						<?php
echo esc_html__('Categories saved successfully.', 'marketplace');
                ?>
					</p>
					<?php
echo '</div>';
            }
        }

        $allowed_cat = get_user_meta($seller_id, 'wkmp_seller_allowed_categories', true);

        ?>

			<p><?php echo esc_html__('Allowed Categories for a seller to add products.', 'marketplace'); ?></p>

			<form method="POST" action="">
					<table class="form-table">
						<tbody>
									<tr valign="top">
											<th scope="row">
													<label for="wkmp_seller_allowed_categories"><?php echo esc_html__('Allowed categories', 'marketplace'); ?></label>
											</th>

											<td class="forminp">
													<select name="wkmp_seller_allowed_categories[]" multiple="true" id="wkmp_allowed_categories_per_seller" data-placeholder="Select categories..." style="min-width:350px;">
															<?php

        $product_categories = get_terms('product_cat', array(
            'hide_empty' => false,
        ));

        if (!empty($product_categories)):
            foreach ($product_categories as $key => $value):
                if ($allowed_cat):
                ?>
																				<option value="<?php echo esc_attr($value->slug); ?>"
																					<?php
        if (in_array($value->slug, $allowed_cat, true)) {
                    echo 'selected';
                }
                ?>
																				>
																				<?php echo esc_attr($value->name); ?></option>
																					<?php else: ?>
																						<option value="<?php echo esc_attr($value->slug); ?>"><?php echo esc_attr($value->name); ?></option>
																				<?php endif;?>
																	<?php endforeach;?>
															<?php endif;?>
													</select>
											</td>
									</tr>
							</tbody>
					</table>
					<p><input type="submit" class="button button-primary" name="mp_submit_assign_category" value="<?php echo esc_html__('Save', 'marketplace'); ?>" />
			</form>
			<?php
}

}

new SetCommition();
