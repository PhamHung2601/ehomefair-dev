<?php
/**
 * Installation related functions and actions.
 *
 * @author   Webkul
 * @category Admin
 * @package  webkul/Classes
 * @version     4.7.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MP_Install' ) ) :
	/**
	 * MP_Install Class.
	 */
	class MP_Install {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			register_activation_hook( MP_PLUGIN_FILE, array( $this, 'install' ) );
		}

		/**
		 * Install MP
		 */
		public function install() {

			flush_rewrite_rules();

			$this->create_mp_tables();
			$this->mp_login_withfacebook();
			$this->create_pages();
			$this->create_seller_role();
			require_once 'class-mp-uninstall.php';
			register_uninstall_hook( MP_PLUGIN_FILE, array( 'MP_UnInstall', 'wc_deactivate' ) );
		}

		/**
		 * Function for creating seller role.
		 */
		public function create_seller_role() {
			global $wp_roles;

			add_role( 'wk_marketplace_seller', 'Marketplace Seller', array(
				'read'                   => true, // True allows that capability.
				'edit_posts'             => true,
				'delete_posts'           => true, // Use false to explicitly deny.
				'publish_posts'          => true,
				'edit_published_posts'   => false,
				'upload_files'           => true,
				'delete_published_posts' => true,
			) );
			$capabilities  = $this->get_core_capabilities();
			$mp_seller_cap = array(
				'manage_marketplace',
				'edit_products',
				'manage_marketplace_products',
			);
			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->add_cap( 'administrator', $cap );
				}
			}
		}
		/**
		 * Function for geting default $capabilities.
		 */
		public function get_core_capabilities() {
			$capabilities         = array();
			$capabilities['core'] = array(
				'manage_marketplace',
				'edit_products',
				'manage_marketplace_products',
				'manage_marketplace_seller',
				'manage_marketplace_commision',
				'manage_marketplace_setting',
			);

			return $capabilities;
		}

		/**
		 * Create pages.
		 *
		 * @param string $slug slug.
		 * @param string $option option.
		 * @param string $page_title page title.
		 * @param string $page_content pag content.
		 */
		public function mp_page_creation( $slug, $option = '', $page_title = '', $page_content = '' ) {
			global $wpdb;
			$option_value = get_option( $option );
			if ( $option_value > 0 && get_post( $option_value ) ) {
				return -1;
			}

			$page_found = null;
			if ( strlen( $page_content ) > 0 ) {

				// Search for an existing page with the specified page content (typically a shortcode).
				$page_found = $wpdb->get_var( $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->posts . " WHERE post_type='mp_seller_panel' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
			} else {

				// Search for an existing page with the specified page slug.
				$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='mp_seller_panel' AND post_name = %s LIMIT 1;", $slug ) );
			}

			if ( $page_found ) {
				if ( ! $option_value ) {
					update_option( $option, $page_found );
				}
				return $page_found;
			}

			$user_id = is_user_logged_in();

			$mp_post_type = ( 'Seller' === $page_title ) ? 'page' : 'mp_seller_panel';

			$page_data = array(
				'post_status'    => 'publish',
				'post_type'      => $mp_post_type,
				'post_author'    => $user_id,
				'post_name'      => $slug,
				'post_title'     => $page_title,
				'post_content'   => $page_content,
				'post_parent'    => $post_parent,
				'comment_status' => 'closed',
			);

			$page_id = wp_insert_post( $page_data );
			if ( $option ) {
				update_option( $option, $page_id );
			}
			return $page_id;
		}

		/**
		 * Create pages that the plugin relies on, storing page id's in variables.
		 *
		 * @access public
		 * @return void
		 */
		public function create_pages() {
			register_post_type( 'mp_seller_panel' );

			$pages = apply_filters( 'marketplace_create_pages', array(
				'Seller' => array(
					'name'    => _x( 'Seller', 'Page slug', 'marketplace' ),
					'title'   => _x( 'Seller', 'Page title', 'marketplace' ),
					'content' => '[' . apply_filters( 'marketplace_seller_tag', 'marketplace' ) . ']',
				),
			) );

			foreach ( $pages as $key => $page ) {
				$this->mp_page_creation( esc_sql( $page['name'] ), 'marketplace_' . $key . '_page_id', $page['title'], $page['content'] );
			}
		}

		/**
		 * Adding the app id and key.
		 */
		public function mp_login_withfacebook() {
			add_option( 'wkfb_mp_key_app_ID', '452074638268548', '', 'yes' );
			add_option( 'wkfb_mp_app_secret_key', '7782182be29be0469caf79ab7877b958', '', 'yes' );
		}

		/**
		 * Create marketplace tables.
		 */
		private function create_mp_tables() {
			global $wpdb, $marketplace;
			$wpdb->hide_errors();
			$collate = '';
			if ( $wpdb->has_cap( 'collation' ) ) {
				if ( ! empty( $wpdb->charset ) ) {
					$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
				}
				if ( ! empty( $wpdb->collate ) ) {
					$collate .= " COLLATE $wpdb->collate";
				}
			}
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$marketplace_tables = "
			CREATE TABLE {$wpdb->prefix}mpsellerinfo (
				seller_id bigint(20) NOT NULL auto_increment,
				user_id bigint(20) NOT NULL,
				seller_key varchar(255) NULL,
				seller_value varchar(30) NULL,
				PRIMARY KEY  (seller_id)
			) $collate;
			CREATE TABLE {$wpdb->prefix}mpcommision (
				ID bigint(20) NOT NULL auto_increment,
				seller_id bigint(20),
				commision_on_seller double,
				admin_amount double,
				seller_total_ammount double,
				paid_amount double,
				last_paid_ammount double,
				last_com_on_total double,
				total_refunded_amount double,
				seller_payment_method varchar(255),
				payment_id_desc text,
				PRIMARY KEY  (ID)
			) $collate;
			CREATE TABLE {$wpdb->prefix}mpseller_meta (
				seller_meta_id bigint(20) NOT NULL auto_increment,
				seller_id bigint(20),
				zone_id bigint(20),
				PRIMARY KEY  (seller_meta_id)
			) $collate;
			CREATE TABLE {$wpdb->prefix}mpfeedback (
				ID bigint(20) NOT NULL auto_increment,
				seller_id bigint(20),
				user_id bigint(20),
				price_r int(11),
				value_r int(11),
				quality_r int(11),
				nickname varchar(255),
				review_summary text,
				review_desc text,
				review_time datetime,
				PRIMARY KEY  (ID)
			) $collate;
			CREATE TABLE {$wpdb->prefix}emailTemplate (
				 id int NOT NULL AUTO_INCREMENT,
				 title varchar(255),
				 basecolor varchar(20),
				 bodycolor varchar(20),
				 backgroundcolor varchar(20),
				 textcolor varchar(20),
				 status varchar(20),
				 UNIQUE KEY id (id)
			) $collate;
			CREATE TABLE {$wpdb->prefix}mp_notifications (
				 id bigint(20) NOT NULL AUTO_INCREMENT,
		 	   type varchar(30) NOT NULL,
		 	   author_id varchar(20) NOT NULL,
		 	   content text NOT NULL,
		 	   read_flag int(1) NOT NULL DEFAULT '0',
		 	   timestamp datetime DEFAULT NULL,
		 	   PRIMARY KEY (id)
			) $collate;
			CREATE TABLE {$wpdb->prefix}mpseller_orders (
				 id bigint(20) NOT NULL AUTO_INCREMENT,
		 	   order_id bigint(20) NOT NULL,
		 	   seller_id bigint(20) NOT NULL,
		 	   order_status varchar(30) NOT NULL,
		 	   PRIMARY KEY (id)
			) $collate;
			CREATE TABLE {$wpdb->prefix}mpseller_asktoadmin (
				 id bigint(20) NOT NULL AUTO_INCREMENT,
		 	   seller_id bigint(20) NOT NULL,
				 subject varchar(100) NOT NULL,
		 	   message varchar(500) NOT NULL,
				 create_date datetime NOT NULL,
		 	   PRIMARY KEY (id)
			) $collate;
			CREATE TABLE {$wpdb->prefix}mporders (
				id bigint(20) NOT NULL auto_increment,
				order_id bigint(20) NOT NULL,
				product_id bigint(20) NOT NULL,
				seller_id bigint(20) NOT NULL,
				amount float NOT NULL,
				admin_amount float NOT NULL,
				seller_amount float NOT NULL,
				quantity bigint(20) NOT NULL,
				commission_applied float NOT NULL,
				discount_applied float NOT NULL,
				commission_type varchar(200) NOT NULL,
				PRIMARY KEY  (id)
			) $collate;
			CREATE TABLE {$wpdb->prefix}mporders_meta (
				mmid bigint(20) NOT NULL auto_increment,
				seller_id bigint(20),
				order_id bigint(20),
				meta_key varchar(255),
				meta_value varchar(255),
				PRIMARY KEY  (mmid)
			) $collate;
			CREATE TABLE {$wpdb->prefix}seller_transaction (
				 id bigint(20) NOT NULL AUTO_INCREMENT,
				 transaction_id varchar(100) NOT NULL,
				 order_id varchar(250) NOT NULL,
				 order_item_id varchar(250) NOT NULL,
				 seller_id bigint(20) NOT NULL,
				 amount float NOT NULL,
				 type varchar(100) NOT NULL,
		 	   method varchar(100) NOT NULL,
				 transaction_date datetime NOT NULL,
		 	   PRIMARY KEY (id)
			) $collate;
			CREATE TABLE {$wpdb->prefix}mpseller_asktoadmin_meta (
				id bigint(20) NOT NULL auto_increment,
				meta_key varchar(255),
				meta_value varchar(255),
				PRIMARY KEY  (id)
			) $collate;
			";
			dbDelta( $marketplace_tables );
		}
	}
endif;
return new MP_Install();
