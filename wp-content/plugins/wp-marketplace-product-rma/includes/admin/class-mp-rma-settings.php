<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('RMA_Settings')) {

    /**
     *
     */
    class RMA_Settings
    {

        public function __construct()
        {

            add_action('mp_rma_settings', array($this, 'mp_rma_settings'));

        }

        public function mp_rma_settings()
        {

            ?>

          <form action="options.php" method="post">

              <?php

            settings_fields('mp_rma_settings_group');
            settings_errors();

            ?>
              <table class="form-table">
                  <tbody>

                      <tr valign="top">
                          <th scope="row" class="titledesc">
                              <label for="rma_enable"><?php esc_html_e('RMA Status', 'marketplace-rma');?></label>
            	            </th>
                          <td class="forminp">
                              <select name="mp_rma_status" id="rma_enable" style="min-width:350px;">
                                  <option value="">-- <?php esc_html_e('Select', 'marketplace-rma');?> --</option>
                                  <option value="enabled" <?php if (get_option('mp_rma_status') == 'enabled') {
                echo 'selected';
            }
            ?>><?php esc_html_e('Enabled', 'marketpalce-rma');?></option>
                                  <option value="disabled" <?php if (get_option('mp_rma_status') == 'disabled') {
                echo 'selected';
            }
            ?>><?php esc_html_e('Disabled', 'marketplace-rma');?></option>
                              </select>
                          </td>
                      </tr>

                      <tr valign="top">
                          <th scope="row" class="titledesc">
                              <label for="rma_time"><?php esc_html_e('RMA Time', 'marketplace-rma');?></label>
            	            </th>
                          <td class="forminp">
                              <input type="text" name="mp_rma_time" id="rma_time" value="<?php echo get_option('mp_rma_time'); ?>" style="min-width:350px;" />
                              <p class="description"><?php esc_html_e('You can add Time limit for customer, only less than these days customer can generate RMA for any order', 'marketplace-rma');?>.</p>
                          </td>
                      </tr>

                      <tr valign="top">
                          <th scope="row" class="titledesc">
                              <label for="rma_statuses"><?php esc_html_e('Order Status for RMA', 'marketplace-rma');?></label>
                          </th>
                          <td class="forminp">
                              <select name="mp_rma_order_statuses[]" id="rma_statuses" multiple="true" style="min-width:350px;">
                                  <?php foreach (wc_get_order_statuses() as $key => $value): ?>
                                      <option value="<?php echo $key; ?>" <?php if (get_option('mp_rma_order_statuses')) {foreach (get_option('mp_rma_order_statuses') as $k => $val) {
                if ($val == $key) {
                    echo 'selected';
                }

            }}?>><?php echo $value; ?></option>
                                  <?php endforeach;?>
                              </select>
                              <p class="description"><?php esc_html_e('Customer can place RMA only for those status of order which is selected here', 'marketplace-rma');?>.</p>
                          </td>
                      </tr>

                      <tr valign="top">
                          <th scope="row" class="titledesc">
                              <label for="rma_address"><?php esc_html_e('Return Address', 'marketplace-rma');?></label>
            	            </th>
                          <td class="forminp">
                              <textarea name="mp_rma_address" rows="4" id="rma_address" style="min-width:350px;"><?php echo get_option('mp_rma_address'); ?></textarea>
                              <p class="description"><?php esc_html_e('Use Comma(,) to seperate', 'marketplace-rma');?>.</p>
                              <p class="description"><?php esc_html_e('After send Shipping label to customer this will be your return address for product', 'marketplace-rma');?>.</p>
                          </td>
                      </tr>

                      <tr valign="top">
                          <th scope="row" class="titledesc">
                              <label for="rma_policy"><?php esc_html_e('RMA Policy', 'marketplace-rma');?></label>
            	            </th>
                          <td class="forminp">
                              <textarea name="mp_rma_policy" rows="4" id="rma_policy" style="min-width:350px;"><?php echo get_option('mp_rma_policy'); ?></textarea>
                              <p class="description"><?php esc_html_e('Using this you can add policy which will display to customer at time of Add RMA', 'marketplace-rma');?>.</p>
                          </td>
                      </tr>

                  </tbody>
              </table>

              <?php submit_button();?>

          </form>
          <?php

        }

    }

    new RMA_Settings();

}
