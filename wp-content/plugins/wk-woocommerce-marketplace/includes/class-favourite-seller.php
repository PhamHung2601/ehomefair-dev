<?php

/*----------*/ /*---------->>> Exit if Accessed Directly <<<----------*/ /*----------*/
if(!defined('ABSPATH')){
	exit;
}
	class Favourite_Seller{

		public static $endpoint = 'favourite-seller';

		public function __construct() {
			ob_start();
			// Actions used to insert a new endpoint in the WordPress.
			// Insering your new tab/page into the My Account page.
			add_action('wp_footer', array($this, 'footer'));
			add_action( 'woocommerce_account_' . self::$endpoint .  '_endpoint', array( $this, 'endpoint_content' ) );


			// Change the My Accout page title.
			add_filter( 'the_title', array( $this, 'endpoint_title' ) );
			add_filter( 'woocommerce_account_menu_items', array( $this, 'new_menu_items' ) );
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
		}



		public function footer(){
			// wp_enqueue_script('pluginjs', WK_MARKETPLACE.'/assets/js/plugin.js', array());
		}



		/**
		* Register new endpoint to use inside My Account page.
		*
		*/
		public function add_endpoints() {
			add_rewrite_endpoint( self::$endpoint, EP_ROOT | EP_PAGES );
		}
		/**
		* Add new query var.
		*
		* @param array $vars
		* @return array
		*/
		public function add_query_vars( $vars ) {
			$vars[] = self::$endpoint;
			return $vars;
		}

		/**
		* Set endpoint title.
		*
		* @param string $title
		* @return string
		*/
		public function endpoint_title( $title ) {
			global $wp_query;
			$is_endpoint = isset( $wp_query->query_vars[ self::$endpoint ] );
			if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
				// New page title.
				$title = __( 'My Favourite Seller', 'marketplace' );
				remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
			}
			return $title;
		}
		/**
		* Insert the new endpoint into the My Account menu.
		*
		* @param array $items
		* @return array
		*/
		public function new_menu_items( $items ) {
			// Remove the logout menu item.
			$logout = $items['customer-logout'];
			unset( $items['customer-logout'] );
			// Insert your custom endpoint.
			$items[ self::$endpoint ] = __( 'My Favourite Seller', 'marketplace' );
			// Insert back the logout item.
			$items['customer-logout'] = $logout;
			return $items;
		}
		/**
		* Endpoint HTML content.
		*/
		public function endpoint_content() {
			include_once(WK_MARKETPLACE_DIR . 'includes/templates/front/myaccount/favourite-seller.php');
		}
		/**
		* Plugin install action.
		* Flush rewrite rules to make our custom endpoint available.
		*/
		public static function install() {
			flush_rewrite_rules();
		}


}
new Favourite_Seller();

// Flush rewrite rules on plugin activation.

register_activation_hook( __FILE__, array( 'Favourite_Seller', 'install' ) );
