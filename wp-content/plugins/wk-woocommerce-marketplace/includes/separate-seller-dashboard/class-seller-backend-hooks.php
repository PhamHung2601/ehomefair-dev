<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Mp_Seller_Backend_Hooks' ) ) {
	/**
	 * Class for backend seller dashboard.
	 */
	class Mp_Seller_Backend_Hooks {

		/**
		 * Constructer fucnrion.
		 */
		public function __construct() {

			require_once 'class-mp-seller-backend-functions.php';

			$obj = new Mp_Seller_Backend_Functions();

			add_action( 'admin_init', array( $obj, 'mp_seller_capabilities' ) );

			add_filter( 'parse_query', array( $obj, 'mp_products_admin_filter_query' ) );

			add_filter( 'get_terms_args', array( $obj, 'mp_override_get_terms_args' ), 10, 2 );

			add_filter( 'product_type_selector', array( $obj, 'mp_seller_product_type_selector' ) );

			add_action( 'admin_menu', array( $obj, 'mp_seller_admin_menu' ) );

			add_filter( 'woocommerce_settings_tabs_array', array( $obj, 'mp_manage_wc_settings_tab_seller' ), 21 );

			add_filter( 'woocommerce_get_sections_shipping', array( $obj, 'mp_manage_wc_shipping_submenu' ) );

			add_filter( 'woocommerce_get_shipping_classes', array( $obj, 'mp_filter_seller_shipping_classes' ) );

		}
	}
	new Mp_Seller_Backend_Hooks();
}

/**
 * Class for localizing data at backend.
 */
class MP_Filter_Localize_Data extends WP_Scripts {

	public function localize( $handle, $object_name, $object_data ) {
			$object_data = apply_filters( 'mp_override_localize_script', $object_data, $handle, $object_name );
			return parent::localize( $handle, $object_name, $object_data );
	}

}

add_filter( 'mp_override_localize_script', 'mp_override_shipping_zones', 10, 3 );
/**
 * Backend seller zone function.
 *
 * @param obj    $object_data object data.
 * @param string $handle handle.
 * @param string $object_name object name.
 */
function mp_override_shipping_zones( $object_data, $handle, $object_name ) {

	global $wpdb, $current_user;

	if ( in_array( 'wk_marketplace_seller', $current_user->roles, true ) ) {
		if ( $handle == 'wc-shipping-zones' ) {
			$seller_zones = $wpdb->get_results( $wpdb->prepare( "SELECT zone_id from {$wpdb->prefix}mpseller_meta where seller_id = %d", $current_user->ID ), ARRAY_A );
			if ( $seller_zones && isset( $object_data['zones'] ) ) {
				foreach ( $seller_zones as $key => $value ) {
					$seller_zones_arr[] = intval( $value['zone_id'] );
				}

				foreach ( $object_data['zones'] as $key => $value ) {
					if ( ! in_array( $value['zone_id'], $seller_zones_arr, true ) ) {
						unset( $object_data['zones'][ $key ] );
					}
				}
			} elseif ( isset( $object_data['zones'] ) ) {
				$seller_zones_arr = array();
				foreach ( $object_data['zones'] as $key => $value ) {
					if ( ! in_array( $value['zone_id'], $seller_zones_arr, true ) ) {
						unset( $object_data['zones'][ $key ] );
					}
				}
			}
		}

		if ( $handle == 'wc-shipping-classes' ) {

			$user_shipping_classes = get_user_meta( $current_user->ID, 'shipping-classes', true );

			if ( $user_shipping_classes && $object_data['classes'] ) {
				$user_shipping_classes = maybe_unserialize( $user_shipping_classes );

				foreach ( $object_data['classes'] as $key => $value ) {
					if ( ! in_array( $value->term_id, $user_shipping_classes, true ) ) {
						unset( $object_data['classes'][ $key ] );
					}
				}
			}
		}
	}
	return $object_data;
}

add_action( 'wp_loaded', function() {
		$GLOBALS['wp_scripts'] = new MP_Filter_Localize_Data();
});
