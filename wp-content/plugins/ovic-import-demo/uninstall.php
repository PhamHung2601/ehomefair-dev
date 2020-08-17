<?php
/**
 * Ovic Uninstall
 *
 * Uninstalling Ovic deletes user roles, pages, tables, and options.
 *
 * @package Ovic\Uninstaller
 * @version 1.0.0
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb, $wp_version;

// Delete options.
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name='_ovic_import_checker';" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name='import_sample_data_demo_images_storage';" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name='import_sample_data_current_sample_data';" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name='import_sample_data_install_sample_package';" );
