<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('MP_RMA_Shipping_Label')) {
    /**
     *
     */
    class MP_RMA_Shipping_Label
    {

        public function __construct()
        {
            add_action('mp_rma_shipping_label', array($this, 'mp_rma_shipping_label'));
            add_action('mp_display_rma_shipping_label', array($this, 'mp_display_rma_shipping_label'));
        }

        public function mp_rma_shipping_label()
        {
            if (isset($_POST['save_shipping_label'])) {
                do_action('mp_save_rma_shipping_label', $_POST);
            }
            ?>
            <form method="post" action="">
                <table width="100%">
                    <tr>
                        <td><p><label for="upload_label"><strong><?php esc_html_e('Upload shipping label', 'marketplace-rma');?></strong></label></p></td>
                        <td><p><input type="button" id="upload_shipping_label" value="<?php esc_html_e('Upload', 'marketplace-rma');?>" class="button button-secondary" /></p></td>
                        <td><p><input type="text" value="" name="shipping_label_path" class="shipping-label-path" /></p></td>
                    </tr>
                    <tr>
                        <td><p><input type="submit" name="save_shipping_label" class="button button-primary" value="<?php esc_html_e('Save', 'marketplace-rma');?>" /></p></td>
                    </tr>
                    <tr>
                        <td><hr></td>
                        <td><hr></td>
                        <td><hr></td>
                    </tr>
                </tabel>
            </form>
            <?php
do_action('mp_display_rma_shipping_label');
        }

        public function mp_display_rma_shipping_label()
        {
            require_once MP_RMA_PATH . 'includes/admin/class-mp-rma-shipping-label-list.php';
        }

    }

    new MP_RMA_Shipping_Label();

}
