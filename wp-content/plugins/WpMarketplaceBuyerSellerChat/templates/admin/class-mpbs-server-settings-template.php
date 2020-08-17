<?php

/**
 * @author Webkul
 * @version 2.1.0
 * This file handles server settings template at admin end.
 */

namespace WpMarketplaceBuyerSellerChat\Templates\Admin;

use WpMarketplaceBuyerSellerChat\Helper;

if (!defined('ABSPATH')) {
    exit;
}

if (! class_exists('Mpbs_Server_Settings_Template')) {
    /**
     *
     */
    class Mpbs_Server_Settings_Template
    {
        function mpbs_server_settings_template()
        {
            $helper = new Helper\Mpbs_Data();

            if (isset($_POST['mpbs_configuration'])) {
                do_action('mpbs_save_configuration', $_POST);
            }
            
            $private_key_file = MPBS_FILE ."/assets/serverJs/";

            ?>
            <div class="wrap" id="mpbs-admin-config">

                <h1 class="wp-heading-inline"><?php echo __('Server Settings', 'mp_buyer_seller_chat'); ?></h1>

                <?php if (! $helper->mpbs_is_server_running()) : ?>
                    <a href="JavaScript:void(0);" class="page-title-action" id="mpbs-start-stop-server" data-action="start"><?php echo __('Start', 'mp_buyer_seller_chat'); ?></a>
                <?php else : ?>
                    <a href="JavaScript:void(0);" class="page-title-action" id="mpbs-start-stop-server" data-action="stop"><?php echo __('Stop', 'mp_buyer_seller_chat'); ?></a>
                <?php endif; ?>

                <div class="notice notice-warning is-dismissible">
                  <p><?php echo __( 'Hello ! If you found any issue regarding functionality or any error, please mail us at <a href="mailto:support@webkul.com">support@webkul.com</a>, as it is possible sometime because of confliction with theme or any other plugin which overriding the default functionalities, so that we can help you out. Thanks !!!', 'mp_buyer_seller_chat' ); ?></p>
                </div>

                <form method="post" action="" id="mpbs_configuration_form" enctype="multipart/form-data">

                  <table class="form-table">
                    <tbody>

                      <tr valign="top">
                        <th scope="row" class="titledesc">
                        
                        <label for="mpbs_host_name"><?php echo __('Host Name <small>[website]</small>', 'mp_buyer_seller_chat'); ?></label>
                        <?php echo wc_help_tip(esc_html('Host Name.', 'mp_buyer_seller_chat'), false); ?>
                        </th>

                        <td class="forminp forminp-text">
                          <input type="text" id="mpbs_host_name" name="mpbs_host_name" value="<?php echo get_option('mpbs_host_name'); ?>" style="min-width: 350px;" />
                          
                        </td>
                      </tr>

                      <tr valign="top">
                        <th scope="row" class="titledesc">
                          <label for="mpbs_port_num"><?php echo __('Port Number <small>[website]</small>', 'mp_buyer_seller_chat'); ?></label>
                          <?php echo wc_help_tip(esc_html('Port Number.', 'mp_buyer_seller_chat'), false); ?>
                        </th>

                        <td class="forminp forminp-text">
                          <input type="text" id="mpbs_port_num" name="mpbs_port_number" value="<?php echo get_option('mpbs_port_number'); ?>" style="min-width: 350px;" />
                        </td>
                      </tr>

                      <tr valign="top">
                        <th scope="row" class="titledesc">
                          <label for="mpbs_chat_name"><?php echo __('Chat Name <small>[store view]</small>', 'mp_buyer_seller_chat'); ?></label>
                          <?php echo wc_help_tip(esc_html('Chat Name.', 'mp_buyer_seller_chat'), false); ?>
                        </th>

                        <td class="forminp forminp-text">
                          <input type="text" id="mpbs_chat_name" name="mpbs_chat_name" value="<?php echo get_option('mpbs_chat_name'); ?>" style="min-width: 350px;" />
                          <p class="description"><?php echo __('The name which will display to all users in front-end.', 'mp_buyer_seller_chat'); ?></p>
                        </td>
                      </tr>
                      
                      <tr valign="top">
                        <th scope="row" class="titledesc">
                          <label for="mpbs_receiver_chat_timetxtcolor"><?php echo __('Receiver Chat Time Text Color', 'mp_buyer_seller_chat'); ?></label>
                          <?php echo wc_help_tip(esc_html('Receiver Chat Time Text Color.', 'mp_buyer_seller_chat'), false); ?>
                        </th>

                        <td class="forminp forminp-text">
                          <input type="text" id="mpbs_receiver_chat_timetxtcolor" name="mpbs_receiver_chat_timetxtcolor" value="<?php echo get_option('mpbs_receiver_chat_timetxtcolor'); ?>" style="min-width: 350px;" />
                          <p class="description"><?php echo __('The receiver chatbox time text color.', 'mp_buyer_seller_chat'); ?></p>
                        </td>
                      </tr>

                      <tr valign="top">
                        <th scope="row" class="titledesc">
                          <label for="mpbs_receiver_chat_bgcolor"><?php echo __('Receiver Chat Background Color', 'mp_buyer_seller_chat'); ?></label>
                          <?php echo wc_help_tip(esc_html('Receiver Chat Background Color.', 'mp_buyer_seller_chat'), false); ?>
                        </th>

                        <td class="forminp forminp-text">
                          <input type="text" id="mpbs_receiver_chat_bgcolor" name="mpbs_receiver_chat_bgcolor" value="<?php echo get_option('mpbs_receiver_chat_bgcolor'); ?>" style="min-width: 350px;" />
                          <p class="description"><?php echo __('The receiver chatbox background color.', 'mp_buyer_seller_chat'); ?></p>
                        </td>
                      </tr>

                      <tr valign="top">
                        <th scope="row" class="titledesc">
                          <label for="mpbs_receiver_text_color"><?php echo __('Receiver Text Color', 'mp_buyer_seller_chat'); ?></label>
                          <?php echo wc_help_tip(esc_html('Receiver Text Color.', 'mp_buyer_seller_chat'), false); ?>
                        </th>

                        <td class="forminp forminp-text">
                          <input type="text" id="mpbs_receiver_text_color" name="mpbs_receiver_text_color" value="<?php echo get_option('mpbs_receiver_text_color'); ?>" style="min-width: 350px;" />
                          <p class="description"><?php echo __('The receiver text color will set the chat box receiver text color.', 'mp_buyer_seller_chat'); ?></p>
                        </td>
                      </tr>
                      <tr valign="top">
                        <th scope="row" class="titledesc">
                          <label for="mpbs_admin_chat_stript_color"><?php echo __('Seller Chat Strip Color', 'mp_buyer_seller_chat'); ?></label>
                          <?php echo wc_help_tip(esc_html('Seller Chat Strip Color.', 'mp_buyer_seller_chat'), false); ?>
                        </th>

                        <td class="forminp forminp-text">
                          <input type="text" id="mpbs_admin_chat_stript_color" name="mpbs_admin_chat_stript_color" value="<?php echo get_option('mpbs_admin_chat_stript_color'); ?>" style="min-width: 350px;" />
                          <p class="description"><?php echo __('The admin chat stript color.', 'mp_buyer_seller_chat'); ?></p>
                        </td>
                      </tr>

                      <tr valign="top">
                        <th scope="row" class="titledesc">
                          <label for="mpbs_sender_chat_timetxtcolor"><?php echo __('Sender Chat Time Text Color', 'mp_buyer_seller_chat'); ?></label>
                          <?php echo wc_help_tip(esc_html('Sender Chat Time Text Color.', 'mp_buyer_seller_chat'), false); ?>
                        </th>

                        <td class="forminp forminp-text">
                          <input type="text" id="mpbs_sender_chat_timetxtcolor" name="mpbs_sender_chat_timetxtcolor" value="<?php echo get_option('mpbs_sender_chat_timetxtcolor'); ?>" style="min-width: 350px;" />
                          <p class="description"><?php echo __('The sender chatbox time text color.', 'mp_buyer_seller_chat'); ?></p>
                        </td>
                      </tr>

                      <tr valign="top">
                        <th scope="row" class="titledesc">
                          <label for="mpbs_sender_chat_bgcolor"><?php echo __('Sender Chat background Color', 'mp_buyer_seller_chat'); ?></label>
                          <?php echo wc_help_tip(esc_html('Sender Chat background Color.', 'mp_buyer_seller_chat'), false); ?>
                        </th>

                        <td class="forminp forminp-text">
                          <input type="text" id="mpbs_sender_chat_bgcolor" name="mpbs_sender_chat_bgcolor" value="<?php echo get_option('mpbs_sender_chat_bgcolor'); ?>" style="min-width: 350px;" />
                          <p class="description"><?php echo __('The sender chatbox background color.', 'mp_buyer_seller_chat'); ?></p>
                        </td>
                      </tr>

                      <tr valign="top">
                        <th scope="row" class="titledesc">
                          <label for="mpbs_customer_chat_stript_color"><?php echo __('Customer Chat Strip Color', 'mp_buyer_seller_chat'); ?></label>
                          <?php echo wc_help_tip(esc_html('Customer Chat Strip Color.', 'mp_buyer_seller_chat'), false); ?>
                        </th>

                        <td class="forminp forminp-text">
                          <input type="text" id="mpbs_customer_chat_stript_color" name="mpbs_customer_chat_stript_color" value="<?php echo get_option('mpbs_customer_chat_stript_color'); ?>" style="min-width: 350px;" />
                          <p class="description"><?php echo __('The customer chat stript color.', 'mp_buyer_seller_chat'); ?></p>
                        </td>
                      </tr>
 

                      <tr valign="top">
                        <th scope="row" class="titledesc">
                          <label for="mpbs_sender_text_color"><?php echo __('Sender Text Color', 'mp_buyer_seller_chat'); ?></label>
                          <?php echo wc_help_tip(esc_html('Sender Text Color.', 'mp_buyer_seller_chat'), false); ?>
                        </th>

                        <td class="forminp forminp-text">
                          <input type="text" id="mpbs_sender_text_color" name="mpbs_sender_text_color" value="<?php echo get_option('mpbs_sender_text_color'); ?>" style="min-width: 350px;" />
                          <p class="description"><?php echo __('The sender text color will set the chat box sender text color.', 'mp_buyer_seller_chat'); ?></p>
                        </td>
                      </tr>

                      <tr valign="top">
                        <th scope="row" class="titledesc">
                          <label for="mpbs_https_enabled"><?php echo __('HTTPS Enabled <small>[website]</small>', 'mp_buyer_seller_chat'); ?></label>
                          <?php echo wc_help_tip(esc_html('HTTPS Enabled.', 'mp_buyer_seller_chat'), false); ?>
                        </th>

                        <td class="forminp">
                          <select id="mpbs_https_enabled" name="mpbs_https_enabled" style="min-width: 350px;" >
                            <option value="">--Select--</option>
                            <option value="yes" <?php if (get_option('mpbs_https_enabled') == 'yes') echo 'selected'; ?>><?php echo __('Yes', 'mp_buyer_seller_chat'); ?></option>
                            <option value="no" <?php if (get_option('mpbs_https_enabled') == 'no') echo 'selected'; ?>><?php echo __('No', 'mp_buyer_seller_chat'); ?></option>
                          </select>
                        </td>
                      </tr>

                      <tr valign="top" class="mpbs_display_server_file_rows" <?php if (get_option('mpbs_https_enabled') == 'yes' ) echo 'style="display:table-row"'; else echo 'style="display:none"'; ?>>
                        <th scope="row" class="titledesc">
                          <label for="mpbs_server_private_key"><?php echo __('Upload Server Private Key File <small>[website]</small>', 'mp_buyer_seller_chat'); ?></label>
                          <?php echo wc_help_tip(esc_html('Upload Server Private Key File.', 'mp_buyer_seller_chat'), false); ?>
                        </th>

                        <td class="forminp">
                          <input type="hidden" class="mpbs_remove_input" name="mpbs_remove_server_file[]" />
                          <input type="file" name="mpbs_server_private_key" id="mpbs_server_private_key" />
                          <label class="mpbs_upload_span" for="mpbs_server_private_key"><?php if (get_option('mpbs_server_private_key')) {
                              $name = explode('/', get_option('mpbs_server_private_key'));
                              if (file_exists($private_key_file.end($name))) {
                                echo '<span class="dashicons dashicons-no mpbs_remove_file" data-option="mpbs_server_private_key" data-confirm="' . __('Are you sure ? This action will remove uploaded file permanently!!!', 'mp_buyer_seller_chat') .'"></span>' . end($name);
                              } else {
                                echo '<span class="dashicons dashicons-upload"></span>';
                              }
                          } else {
                              echo '<span class="dashicons dashicons-upload"></span>';
                          } ?></label>
                          <p class="description"><?php echo __('You can get this file from your host provider, file extension must be: .key', 'mp_buyer_seller_chat'); ?></p>
                        </td>
                      </tr>

                      <tr valign="top" class="mpbs_display_server_file_rows" <?php if (get_option('mpbs_https_enabled') == 'yes') echo 'style="display:table-row"'; else echo 'style="display:none"'; ?>>
                        <th scope="row" class="titledesc">
                          <label for="mpbs_server_certificate_file"><?php echo __('Upload Server Certificate Key File <small>[website]</small>', 'mp_buyer_seller_chat'); ?></label>
                          <?php echo wc_help_tip(esc_html('Upload Server Certificate Key File.', 'mp_buyer_seller_chat'), false); ?>
                        </th>

                        <td class="forminp">
                          <input type="hidden" class="mpbs_remove_input" name="mpbs_remove_server_file[]" />
                          <input type="file" name="mpbs_server_certificate_file" id="mpbs_server_certificate_file" />
                          <label class="mpbs_upload_span" for="mpbs_server_certificate_file"><?php if (get_option('mpbs_server_certificate_file')) {
                              $name = explode('/', get_option('mpbs_server_certificate_file'));
                              if (file_exists($private_key_file.end($name))) {
                                echo '<span class="dashicons dashicons-no mpbs_remove_file" data-option="mpbs_server_certificate_file" data-confirm="' . __('Are you sure ? This action will remove uploaded file permanently!!!', 'mp_buyer_seller_chat') .'"></span>' . end($name);
                              } else {
                                echo '<span class="dashicons dashicons-upload"></span>';
                              }
                          } else {
                              echo '<span class="dashicons dashicons-upload"></span>';
                          } ?></label>
                          <p class="description"><?php echo __('You can get this file from your host provider, file extension must be: .crt', 'mp_buyer_seller_chat'); ?></p>
                        </td>
                      </tr>

                      <tr valign="top" class="mpbs_display_server_file_rows" <?php if (get_option('mpbs_https_enabled') == 'yes') echo 'style="display:table-row"'; else echo 'style="display:none"'; ?>>
                        <th scope="row" class="titledesc">
                          <label for="mpbs_server_ca_bundle_file"><?php echo __('Upload Server CA Bundle File <small>[website]</small>', 'mp_buyer_seller_chat'); ?></label>
                          <?php echo wc_help_tip(esc_html('Upload Server CA Bundle File.', 'mp_buyer_seller_chat'), false); ?>
                        </th>

                        <td class="forminp">
                          <input type="hidden" class="mpbs_remove_input" name="mpbs_remove_server_file[]" />
                          <input type="file" name="mpbs_server_ca_bundle_file" id="mpbs_server_ca_bundle_file" />
                          <label class="mpbs_upload_span" for="mpbs_server_ca_bundle_file"><?php if (get_option('mpbs_server_ca_bundle_file')) {
                              $name = explode('/', get_option('mpbs_server_ca_bundle_file'));
                              if (file_exists($private_key_file.end($name))) {
                                echo '<span class="dashicons dashicons-no mpbs_remove_file" data-option="mpbs_server_ca_bundle_file" data-confirm="' . __('Are you sure ? This action will remove uploaded file permanently!!!', 'mp_buyer_seller_chat') .'"></span>' . end($name);
                              }else{
                                echo '<span class="dashicons dashicons-upload"></span>';
                              }
                          } else {
                              echo '<span class="dashicons dashicons-upload"></span>';
                          } ?></label>
                          <p class="description"><?php echo __('You can get this file from your host provider, file extension must be: .ca-bundle', 'mp_buyer_seller_chat'); ?></p>
                        </td>
                      </tr>

                    </tbody>
                  </table>

                  <?php wp_nonce_field('mpbs_configuration_nonce_action', 'mpbs_configuration_nonce'); ?>
                  <?php submit_button(__('Save Changes', 'mp_buyer_seller_chat'), 'primary', 'mpbs_configuration'); ?>

                </form>

                <div id="responsedialog"><p></p></div>
            </div>
            <?php
        }
    }
}
