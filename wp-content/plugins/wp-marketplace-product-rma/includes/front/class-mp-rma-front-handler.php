<?php
/**
*   Seller Panel: Auction Menu
*/
if ( ! defined( 'MP_RMA_PATH' ) ) {

    exit( 'Direct access forbidden.' );

}

if ( !class_exists( 'MP_Rma_Front_Handler' ) )
{
    /**
     *
     */
    class MP_Rma_Front_Handler
    {
        public static $endpoint = 'rma';

        function __construct()
        {
            add_action( 'marketplace_list_seller_option', array( $this, 'mp_add_rma_link' ) );
            add_filter( 'query_vars', array( $this, 'mp_rma_add_query_vars' ), 0 );

            // Change the My Accout page title.
      			add_filter( 'the_title', array( $this, 'mp_rma_endpoint_title' ) );

            add_filter( 'woocommerce_account_menu_items', array( $this, 'mp_rma_new_menu_items' ) );

            add_filter('mp_woocommerce_account_menu_options', array($this, 'mp_rma_links_in_menu'));

            add_filter('woocommerce_account_menu_item_classes', array($this, 'mp_rma_add_menu_active_class'), 10, 2);
        }

        function mp_add_rma_link()
        {
            global $wpdb;
            $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='".get_option('wkmp_seller_page_title')."'");
            echo '<li class="wkmp-selleritem rma"><a href="#">RMA</a><ul><li class="wkmp-selleritem"><a href="'.home_url("/".$page_name."/manage-rma").'">'.__('Manage RMA', 'marketplace-rma').'</a></li><li class="wkmp-selleritem"><a href="'.home_url("/".$page_name."/rma-reason").'">'.__('RMA Reason', 'marketplace-rma').'</a></li></ul></li>';
        }

        /**
    		* Add new query var.
    		*
    		* @param array $vars
    		* @return array
    		*/
    		public function mp_rma_add_query_vars( $vars )
        {
      			$vars[] = self::$endpoint;
      			return $vars;
    		}
        /**
    		* Set endpoint title.
    		*
    		* @param string $title
    		* @return string
    		*/
    		public function mp_rma_endpoint_title( $title ) {
      			global $wp_query;
      			$is_endpoint = isset( $wp_query->query_vars[ self::$endpoint ] );
      			if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
      				// New page title.
      				$title = __( 'RMA', 'woocommerce' );
      				remove_filter( 'the_title', array( $this, 'mp_rma_endpoint_title' ) );
      			}
      			return $title;
    		}
        /**
    		* Insert the new endpoint into the My Account menu.
    		*
    		* @param array $items
    		* @return array
    		*/
    		public function mp_rma_new_menu_items( $items ) {
      			// Remove the logout menu item.
      			$logout = $items['customer-logout'];
      			unset( $items['customer-logout'] );
      			// Insert your custom endpoint.
      			$items[ self::$endpoint ] = __( 'RMA', 'woocommerce' );
      			// Insert back the logout item.
      			$items['customer-logout'] = $logout;
      			return $items;
    		}

        /**
         *  Add links with mp menu links
         */
        function mp_rma_links_in_menu($items) {
            global $wpdb;

            $user_id = get_current_user_id();

            $new_items = array();

            $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='".get_option('wkmp_seller_page_title')."'");

            $new_items['../' . $page_name . '/manage-rma'] = __( 'Manage RMA', 'marketplace-rma' );

            $items += $new_items;

            return $items;
        }

        function mp_rma_add_menu_active_class($classes, $endpoint)
        {
            global $wpdb, $wp_query;

            $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option('wkmp_seller_page_title') . "'");

            if (is_page($page_name)) {
                $actual_link = ( isset( $_SERVER['HTTPS'] ) ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

                $current = str_replace( home_url() . '/', '', $actual_link );

                $endpoint = str_replace( '../', '', $endpoint );

                if ( ( get_query_var( 'main_page' ) == 'rma-reason' || get_query_var( 'main_page' ) == 'add-reason' || get_query_var( 'main_page' ) == 'rma' ) && strpos( $endpoint, 'manage-rma' ) !== false ) {
                  $classes[] = 'is-active';
                  $count = 0;
                }
                if ('rma' === $endpoint && ($key = array_search('is-active', $classes)) !== false) {
                    unset($classes[$key]);
                }
            }
            return $classes;
        }
    }
}
