<?php

/**
 * @author Webkul
 * @version 2.1.0
 * This file handles admin settings interface.
 */

namespace WpMarketplaceBuyerSellerChat\Includes\Admin\Util;

if (!defined('ABSPATH')) {
    exit;
}

interface Admin_Settings_interface
{
    public function mpbs_init();
    public function mpbs_enqueue_scripts();
    /**
     * Add Menu under MP menu
     */
    public function mpbs_add_dashboard_menu();

    /**
     * Register Option Settings
     */
    public function mpbs_register_settings();
    /**
     * Register script
     */


    /**
     *  Configuration form submit function
     *  @param $_POST
     */
    public function mpbs_save_configuration($data);

    /**
     *  upload dir filter
     */
    function mpbs_upload_dir_filter($dir);

    /**
     *  write server js file
     *  @param $host host name
     */
    function mpbs_write_server_file($host, $https_enabled);
}
