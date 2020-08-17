<?php
/**
 * Plugin Name:       Better Admin Users Search
 * Plugin URI:        https://github.com/Applelo/Better-Admin-Users-Search
 * Description:       A plugin to improve users admin search
 * Version:           1.1.7
 * Author:            Applelo
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       baus
 * Domain Path:       i18n
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

define('BETTER_ADMIN_USERS_SEARCH_VERSION', '1.1.7' );
define('BETTER_ADMIN_USERS_SEARCH_PREFIX', 'baus' );



require plugin_dir_path( __FILE__ ) . 'includes/class-better-admin-users-search.php';

function baus_load_i18n() {
    load_plugin_textdomain( 'baus', FALSE, basename( dirname( __FILE__ ) ) . '/i18n/' );
}

function baus_add_action_links ( $links ) {
    $mylinks = array(
        '<a href="' . admin_url( 'options-general.php?page=baus_options' ) . '">' . __('Settings', 'baus') . '</a>',
    );
    return array_merge( $mylinks, $links );
}

function baus_create_default_options()
{
    $default_option = array(
        "baus_metas" => array(),
        "baus_default_value_user_url" => "on",
        "baus_default_value_user_email" => "on",
        "baus_default_value_user_nicename" => "on",
        "baus_default_value_display_name" => "on",
        "baus_default_value_user_login" => "on"
    );

    if( !get_option(BETTER_ADMIN_USERS_SEARCH_PREFIX . '_options') ) {
        update_option(BETTER_ADMIN_USERS_SEARCH_PREFIX . '_options', $default_option);
    }
}




register_activation_hook( __FILE__, 'baus_create_default_options' );
add_action( 'plugins_loaded', 'baus_load_i18n');
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'baus_add_action_links' );
new Better_Admin_Users_Search();
