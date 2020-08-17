<?php
/**
 * Plugin Name: Ovic: Product Bundle
 * Plugin URI: https://kutethemes.com/
 * Description: Support WooCommerce Product Bundle.
 * Author: Ovic Team
 * Author URI: https://themeforest.net/user/kutethemes
 * Version: 1.0.8
 * Text Domain: ovic-bundle
 */
// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) exit;
if ( !class_exists( 'Ovic_Product_Bundle' ) ) {
	class  Ovic_Product_Bundle
	{
		/**
		 * @var Ovic_Product_Bundle The one true Ovic_Product_Bundle
		 */
		private static $instance;

		public static function instance()
		{
			if ( !isset( self::$instance ) && !( self::$instance instanceof Ovic_Product_Bundle ) ) {
				self::$instance = new Ovic_Product_Bundle;
				self::$instance->setup_constants();
//				self::$instance->auto_update_plugins();
				self::$instance->includes();
				add_action( 'plugins_loaded', array( self::$instance, 'load_text_domain' ) );
//				add_filter( 'plugin_row_meta', array( self::$instance, 'plugin_row_meta' ), 10, 2 );
			}

			return self::$instance;
		}

		public function setup_constants()
		{
			// Plugin version.
			if ( !defined( 'OVIC_BUNDLE_VERSION' ) ) {
				define( 'OVIC_BUNDLE_VERSION', '1.0.8' );
			}
			// Plugin basename.
			if ( !defined( 'OVIC_BUNDLE_BASENAME' ) ) {
				define( 'OVIC_BUNDLE_BASENAME', plugin_basename( __FILE__ ) );
			}
			// Plugin Folder Path.
			if ( !defined( 'OVIC_BUNDLE_DIR' ) ) {
				define( 'OVIC_BUNDLE_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
			}
			// Plugin Folder URL.
			if ( !defined( 'OVIC_BUNDLE_URI' ) ) {
				define( 'OVIC_BUNDLE_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
			}
		}

		public function includes()
		{
			require_once OVIC_BUNDLE_DIR . 'includes/welcome.php';
		}

		public function load_text_domain()
		{
			if ( !function_exists( 'WC' ) || !version_compare( WC()->version, '3.0.0', '>=' ) ) {
				add_action( 'admin_notices', array( self::$instance, 'bundle_notice_wc' ) );

				return;
			}
			load_plugin_textdomain( 'ovic-bundle', false, OVIC_BUNDLE_DIR . 'languages' );
			/* INCLUDE FILE */
			require_once OVIC_BUNDLE_DIR . 'includes/product-bundle.php';
		}

		public function bundle_notice_wc()
		{
			?>
            <div class="error">
                <p><?php esc_html_e( 'Ovic Product Bundles require WooCommerce version 3.0.0 or greater.', 'ovic-bundle' ); ?></p>
            </div>
			<?php
		}

		/**
		 * Show row meta on the plugin screen.
		 *
		 * @param mixed $links Plugin Row Meta.
		 * @param mixed $file Plugin Base file.
		 *
		 * @return array
		 */
		public function plugin_row_meta( $links, $file )
		{
			if ( OVIC_BUNDLE_BASENAME === $file ) {
				$row_meta = array(
					'upgrade' => '<a href="' . esc_url( 'https://kutethemes.com/wordpress-plugins/' ) . '" target="_blank" aria-label="' . esc_attr__( 'Upgrade to Premium', 'ovic-bundle' ) . '" style="font-weight:bold;">' . esc_html__( 'Upgrade to Premium', 'ovic-bundle' ) . '</a>',
				);

				return array_merge( $links, $row_meta );
			}

			return (array)$links;
		}

		public function auto_update_plugins()
		{
			if ( is_admin() ) {
				require_once OVIC_BUNDLE_DIR . 'includes/license/updater-admin.php';
				/* UPDATE PLUGIN AUTOMATIC */
				if ( class_exists( 'Ovic_Updater_Admin' ) ) {
					$config  = array(
						'item_name'       => 'Product Bundle', // Name of plugin
						'item_slug'       => 'ovic-product-bundle', // plugin slug
						'version'         => OVIC_BUNDLE_VERSION, // The current version of this plugin
						'root_uri'        => __FILE__, // The root file of this plugin
						'item_link'       => 'https://kutethemes.com/plugins/product-bundle/',
						'setting_license' => admin_url( 'admin.php?page=ovic-product-bundle&tab=license_content' ),
					);
					$license = new Ovic_Updater_Admin( $config );
					$license->updater();
				}
			}
		}
	}
}
if ( !function_exists( 'Ovic_Product_Bundle' ) ) {
	function Ovic_Product_Bundle()
	{
		return Ovic_Product_Bundle::instance();
	}
}
Ovic_Product_Bundle();