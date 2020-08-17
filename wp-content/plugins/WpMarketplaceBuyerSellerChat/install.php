<?php

/**
 * @package WpMarketplaceBuyerSellerChat
 * @author Webkul
 */

if (! class_exists('Mpbs_Install_Schema')) {
    /**
     *
     */
    class Mpbs_Install_Schema
    {

        function mpbs_create_tables()
        {
            global $wpdb;

            $charset_collate = $wpdb->get_charset_collate();

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $chat = $wpdb->prefix.'chat_table';

            $chat_table="CREATE TABLE IF NOT EXISTS ".$chat." (id int(11) NOT NULL AUTO_INCREMENT,sender_id int(11) NOT NULL,receiver_id int(11) NOT NULL,  time_stamp int(15) NOT NULL, status int(1) NOT NULL, message longtext NOT NULL, user_public_ip varchar(45) NOT NULL,PRIMARY KEY (id)) $charset_collate;";

            dbDelta($chat_table);

            $user = $wpdb->prefix.'user_table';

            $user_table="CREATE TABLE IF NOT EXISTS ".$user." (id int(11) NOT NULL AUTO_INCREMENT, user_id int(11) NOT NULL,status int(1) NOT NULL, PRIMARY KEY (id)) $charset_collate;";

            dbDelta($user_table);

            $user_meta=$wpdb->prefix.'user_table_meta';

            $user_table_meta = "CREATE TABLE IF NOT EXISTS ".$user_meta." (id int(11) NOT NULL AUTO_INCREMENT,seller_id int(11) NOT NULL,buyer_id int(11) NOT NULL,chat_window varchar(30) NOT NULL, PRIMARY KEY (id)) $charset_collate; ";

            dbDelta($user_table_meta);
        }
    }

}
