<?php

/**
 * @author Webkul
 * @version 2.0.0
 * This file handles helper config class.
 */

namespace WpMarketplaceBuyerSellerChat\Helper;

if (!defined('ABSPATH')) {
    exit;
}

if (! class_exists('Mpbs_Core_Config')) {
    /**
     *
     */
    class Mpbs_Core_Config implements Util\Core_Config_Interface
    {
        public function __construct()
        {
            $this->dataHelper = new Mpbs_Data();
        }

        public function mpbs_get_core_config()
        {
            $data = array(
                'serverRunning' => $this->dataHelper->mpbs_is_server_running(),
                'host'          => get_option('mpbs_host_name') . ':' . get_option('mpbs_port_number'),
                'name'          => get_option('mpbs_chat_name'),
                'pluginsPath'   => MPBS_URL
            );
            return $data;
        }

        public function mpbs_get_enabled_customer_list()
        {
            $data = array(
                'customerList'  => $this->dataHelper->mpbs_get_initialised_chat_list()
            );
            return $data;
        }
    }
}
