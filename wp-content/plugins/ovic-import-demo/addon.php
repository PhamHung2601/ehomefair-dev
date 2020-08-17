<?php
/**
 * Plugin Name: Ovic: Import Demo
 * Plugin URI: https://kutethemes.com/
 * Description: The plugin is supports import wordpress.
 * Author: Ovic Team
 * Author URI: https://kutethemes.com/contact-us
 * Version: 1.5.1
 * Text Domain: ovic-import
 */
// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
if ( !function_exists( 'is_plugin_active' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
if ( !class_exists( 'Ovic_Import_Demo' ) ) {
	class  Ovic_Import_Demo
	{
		/**
		 * @var Ovic_Import_Demo The one true Ovic_Import_Demo
		 */
		private static $instance;

		public static function instance()
		{
			if ( !isset( self::$instance ) && !( self::$instance instanceof Ovic_Import_Demo ) ) {
				self::$instance = new Ovic_Import_Demo;
				self::$instance->setup_constants();
				add_action( 'wp_loaded', array( self::$instance, 'after_setup_theme' ) );
				add_filter( 'plugin_row_meta', array( self::$instance, 'plugin_row_meta' ), 10, 2 );
				load_plugin_textdomain( 'ovic-import', false, OVIC_IMPORT_PLUGIN_DIR . 'languages' );
				self::$instance->includes();
			}

			return self::$instance;
		}

		public function setup_constants()
		{
			// Plugin version.
			if ( !defined( 'OVIC_IMPORT_VERSION' ) ) {
				define( 'OVIC_IMPORT_VERSION', '1.5.1' );
			}
			// Plugin basename.
			if ( !defined( 'OVIC_IMPORT_BASENAME' ) ) {
				define( 'OVIC_IMPORT_BASENAME', plugin_basename( __FILE__ ) );
			}
			// Plugin Folder Path.
			if ( !defined( 'OVIC_IMPORT_PLUGIN_DIR' ) ) {
				define( 'OVIC_IMPORT_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
			}
			// Plugin Folder URL.
			if ( !defined( 'OVIC_IMPORT_PLUGIN_URL' ) ) {
				define( 'OVIC_IMPORT_PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
			}
		}

		public function after_setup_theme()
		{
			if ( is_admin() ) {
				require_once OVIC_IMPORT_PLUGIN_DIR . 'includes/import.php';
			}
		}

		public function includes()
		{
			if ( is_admin() ) {
				require_once OVIC_IMPORT_PLUGIN_DIR . 'includes/export.php';
			}
			require_once OVIC_IMPORT_PLUGIN_DIR . 'includes/import-database/import-database.php';
			require_once OVIC_IMPORT_PLUGIN_DIR . 'includes/dashboard.php';
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
			if ( OVIC_IMPORT_BASENAME === $file ) {
				$row_meta = array(
					'docs' => '<a href="' . esc_url( 'https://kutethemes.com/how-to-use-plugin-ovic-import-demo/' ) . '" target="_blank" aria-label="' . esc_attr__( 'View Ovic Import Demo documentation', 'ovic-import' ) . '">' . esc_html__( 'Documentation', 'ovic-import' ) . '</a>',
				);

				return array_merge( $links, $row_meta );
			}

			return (array)$links;
		}
	}
}
if ( !function_exists( 'Ovic_Import_Demo' ) ) {
	function Ovic_Import_Demo()
	{
		return Ovic_Import_Demo::instance();
	}
}
Ovic_Import_Demo();