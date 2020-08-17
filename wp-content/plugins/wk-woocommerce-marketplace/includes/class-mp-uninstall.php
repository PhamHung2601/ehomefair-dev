<?php
/*
	Delete registered settings, created tables, products on deactivation.
*/
if ( ! defined( 'ABSPATH' ) ) 
	exit; // Exit if accessed directly

if ( ! class_exists( 'MP_UnInstall' ) ) :

/**
 * MP_UnInstall Class
 */

class MP_UnInstall {
	
	public $post_id;

	function wc_deactivate() {

		global $wpdb;

		$table_name = $wpdb->prefix. 'mpsellerinfo';
 
		unregister_setting('marketplace-settings-group','wkfb_mp_key_app_ID');

		unregister_setting('marketplace-settings-group','wkfb_mp_app_secret_key');

		unregister_setting('marketplace-settings-group','wkmpcom_minimum_com_onseller');

		unregister_setting('marketplace-settings-group','wkmpseller_ammount_to_pay');

		unregister_setting('marketplace-settings-group','wkmp_seller_menu_tile');

		unregister_setting('marketplace-settings-group','wkmp_seller_page_title');		

		unregister_setting('marketplace-settings-group','wkmp_seller_allow_publish');

		unregister_setting('marketplace-settings-group','wkmp_auto_approve_seller');

		unregister_setting('marketplace-settings-group','wkmp_show_seller_seperate_form');

		delete_option( 'wkmp_seller_page_title' );

		delete_option( 'wkmp_show_seller_seperate_form' );

		delete_option('wkfb_mp_key_app_ID');

		delete_option('wkfb_mp_app_secret_key');

		$ID = $wpdb->get_results("SELECT user_id FROM $table_name");		
		
		$posts_table = $wpdb->posts;
  
		foreach ($ID as $key) {

			$id = $key->user_id;

			$sql = "update {$posts_table} SET post_author=1 WHERE post_author = $id";
			
			$wpdb->query($sql);

		}	
 
	}

}

endif;

return new MP_UnInstall();