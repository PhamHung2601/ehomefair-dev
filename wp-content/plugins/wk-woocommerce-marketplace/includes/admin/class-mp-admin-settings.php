<?php
/**
 * Marketplace Admin Settings Class.
 *
 * @author webkul
 * @category Admin
 * @package webkul/Admin
 * @version   4.7.1
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}


if ( ! class_exists( 'MP_Admin_Settings' ) ) {
		/**
		 * Class admin settings
		 */
	class MP_Admin_Settings {
		/**
		 * Constructor function.
		 */
		public function __construct() {
			// Tabs callback function actions.
			add_action( 'mp_admin_settings_configuration', array( $this, 'mp_admin_settings_configuration' ) );

			add_action( 'mp_admin_settings_seller_assets', array( $this, 'mp_admin_settings_seller_assets' ) );

			add_action( 'mp_admin_settings_products_setting', array( $this, 'mp_admin_settings_products_setting' ) );

			add_action( 'mp_admin_settings_advanced', array( $this, 'mp_admin_settings_advanced' ) );

			// Nav tabs.
			echo '<div class="wrap">';

			echo '<nav class="nav-tab-wrapper">';

			$mp_tabs = array(
				'configuration'    => esc_html__( 'Configuration', 'marketplace' ),
				'products_setting' => esc_html__( 'Product Settings', 'marketplace' ),
				'seller_assets'    => esc_html__( 'Asset Visibility', 'marketplace' ),
				'advanced'         => esc_html__( 'Advanced', 'marketplace' ),
			);

			$mp_tabs = apply_filters( 'marketplace_get_settings_tabs', $mp_tabs );

			$current_tab = empty( $_GET['tab'] ) ? 'configuration' : sanitize_title( $_GET['tab'] );

			$this->id = $current_tab;

			foreach ( $mp_tabs as $name => $label ) {
				echo '<a href="' . esc_url( admin_url( 'admin.php?page=Settings&tab=' . $name ) ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . esc_attr( $label ) . '</a>';
			} ?>
			</nav>

			<h1 class="screen-reader-text">
				<?php echo esc_html( $mp_tabs[ $current_tab ] ); ?>
			</h1>

			<?php

			do_action( 'mp_admin_settings_' . $current_tab );

			echo '</div>';
		}

		/**
		 * Configuration tab callback function
		 */
		public function mp_admin_settings_configuration() {
			require 'settings/settings.php';
		}

		/**
		 * Seller Assets tab callback function
		 */
		public function mp_admin_settings_seller_assets() {
				require 'settings/seller-assets-setting.php';
		}

		/**
		 * Seller products setting tab callback function
		 */
		public function mp_admin_settings_products_setting() {
				require 'settings/seller-products-setting.php';
		}

		public function mp_admin_settings_advanced() {
				require 'settings/seller-advanced-setting.php';
		}
	}

		new MP_Admin_Settings();
}
