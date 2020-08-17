<?php

/**
 *        RMA: Admin Menus.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('MP_RMA_Admin_Menus')) {
    /**
     *     Admin Menu Class.
     */
    class MP_RMA_Admin_Menus
    {
        public function __construct()
        {
            add_action('admin_menu', array($this, 'mp_rma_admin_menu'));
        }

        public function mp_rma_admin_menu()
        {
            add_menu_page(__('Marketplace RMA', 'marketplace-rma'), __('Marketplace RMA', 'marketplace-rma'), 'manage_options', 'marketplace-rma', array($this, 'mp_rma_order_menu'), MP_RMA_URL.'assets/images/product-return.png', '55');

            $hook = add_submenu_page('marketplace-rma', __('Marketplace RMA', 'marketplace-rma'), __('Manage RMA', 'marketplace-rma'), 'manage_options', 'marketplace-rma', array($this, 'mp_rma_order_menu'));

            $hook = add_submenu_page('marketplace-rma', __('RMA Reasons', 'marketplace-rma'), __('Manage Reasons', 'marketplace-rma'), 'manage_options', 'mp-rma-reasons', array($this, 'mp_rma_reasons'));

            $hook = add_submenu_page('marketplace-rma', __('Configuration', 'marketplace-rma'), __('Configuration', 'marketplace-rma'), 'manage_options', 'mp-rma-config', array($this, 'mp_rma_configuration'));

            add_action("load-$hook", array($this, 'mp_rma_add_rule_screen_option'));

            add_filter('set-screen-option', 'mp_rma_set_options', 10, 3);
        }

        public function mp_rma_add_rule_screen_option()
        {
            $options = 'per_page';

            $args = array(
                'label' => __('Product Per Page', 'marketplace-rma'),
                'default' => 20,
                'option' => 'product_per_page',
            );

            add_screen_option($options, $args);
        }

        public function mp_rma_set_options($status, $option, $value)
        {
            return $value;
        }

        public function mp_rma_order_menu()
        {
            if (isset($_GET['rid']) && isset($_GET['action']) && $_GET['action'] == 'view') {
                $id = $_GET['rid'];
                echo '<div class="wrap auction">';

                echo '<nav class="nav-tab-wrapper">';

                echo '<h1 class="wp-heading-inline">'.__('RMA Details', 'marketplace-rma').'</h1>';

                echo '<a href="admin.php?page=marketplace-rma" class="page-title-action">'.__('Back', 'marketplace-rma').'</a>';

                $wksa_tabs = array(
                    'details' => __('RMA Details', 'marketplace-rma'),
                    'products' => __('Products', 'marketplace-rma'),
                    'conversation' => __('Conversation', 'marketplace-rma'),
                    'images' => __('RMA Images', 'marketplace-rma'),
                    'shipping_label' => __('Manage', 'marketplace-rma'),
                );

                echo '<p>'.__('View RMA ID', 'marketplace-rma').' #'.$id.'</p>';

                $current_tab = empty($_GET['tab']) ? 'details' : sanitize_title($_GET['tab']);

                foreach ($wksa_tabs as $name => $label) {
                    echo '<a href="'.admin_url('admin.php?page=marketplace-rma&rid='.$id.'&action=view&tab='.$name).'" class="nav-tab '.($current_tab == $name ? 'nav-tab-active' : '').'">'.$label.'</a>';
                } ?>
                    </nav>

                    <h1 class="screen-reader-text"><?php echo esc_html($wksa_tabs[$current_tab]); ?></h1>

                    <?php

                do_action('mp_rma_view_'.$current_tab);

                echo '</div>';
            } else {
                require_once 'class-mp-manage-rma.php';
                $list_obj = new MP_Manage_Rma();
                echo '<div class="wrap">';
                echo '<h1 class="wp-heading-inline">'.esc_html__( 'RMA System', 'marketplace-rma' ).'</h1>';
                $list_obj->prepare_items(); ?>

                <form method="get">

                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />

                    <?php

                $list_obj->search_box(__('Search Order ID', 'marketplace-rma'), 'search-id');

                $list_obj->display(); ?>

                </form>

                <?php

                echo '</div>';
            }
        }

        public function mp_rma_configuration()
        {
            echo '<div class="wrap auction">';

            echo '<nav class="nav-tab-wrapper">';

            echo '<h1 class="wp-heading-inline" style="margin-bottom: 20px;display: block;">Configuration</h1>';

            $wksa_tabs = array(
                'settings' => __('RMA Settings', 'marketplace-rma'),
                'shipping_label' => __('Add Shipping Label', 'marketplace-rma'),
            );

            $current_tab = empty($_GET['tab']) ? 'settings' : sanitize_title($_GET['tab']);

            foreach ($wksa_tabs as $name => $label) {
                echo '<a href="'.admin_url('admin.php?page=mp-rma-config&tab='.$name).'" class="nav-tab '.($current_tab == $name ? 'nav-tab-active' : '').'">'.$label.'</a>';
            } ?>
                </nav>

                <h1 class="screen-reader-text"><?php echo esc_html($wksa_tabs[$current_tab]); ?></h1>

                <?php

            do_action('mp_rma_'.$current_tab);

            echo '</div>';
        }

        public function mp_rma_reasons()
        {
            if (isset($_GET['page']) && isset($_GET['action']) && $_GET['page'] == 'mp-rma-reasons' && $_GET['action'] == 'add') {
                require_once MP_RMA_PATH.'includes/class-mp-add-rma-reason.php';
                $obj = new MP_RMA_Add_Reason();
                $obj->mp_add_reason_rma();
            } else {

                if( isset( $_GET['action'] ) ) {

                    if( $_GET['action'] == 'added' ) {
                        ?>
                        <div class='notice notice-success is-dismissible'>
                            <p><?php esc_html_e( 'RMA reason added successfully', 'marketplace-rma' ); ?></p>
                        </div>
                        <?php
                    } else if( $_GET['action'] == 'updated' ) {
                        ?>
                        <div class='notice notice-success is-dismissible'>
                            <p><?php esc_html_e( 'RMA reason updated successfully', 'marketplace-rma' ); ?></p>
                        </div>
                        <?php
                    } else if( $_GET['action'] == 'delete' ) {
                        ?>
                        <div class='notice notice-success is-dismissible'>
                            <p><?php esc_html_e( 'RMA reason deleted successfully', 'marketplace-rma' ); ?></p>
                        </div>
                        <?php
                    }

                }

                $list_obj = new MP_RMA_Reasons();
                echo '<div class="wrap">';
                echo '<h1 class="wp-heading-inline">'.__('RMA Reasons', 'marketplace-rma').'</h1>';
                echo '<a href="admin.php?page=mp-rma-reasons&action=add" class="page-title-action">'.__('Add Reason', 'marketplace-rma').'</a>';
                $list_obj->prepare_items(); ?>

                <form method="get">

                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />

                    <?php

                $list_obj->search_box(__('Search', 'marketplace-rma'), 'search-id');

                $list_obj->display(); ?>

                </form>
                <?php

                echo '</div>';
            }
        }
    }

    new MP_RMA_Admin_Menus();
}
