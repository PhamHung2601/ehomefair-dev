<?php
/**
 * Created by PhpStorm.
 * User: HOANG KHANH
 * Date: 12/28/2017
 * Time: 1:10 PM
 */
/**
 *
 * Setup Framework Class
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !class_exists( 'OVIC' ) ) {
	class OVIC
	{
		/**
		 *
		 * instance
		 * @access private
		 * @var OVIC
		 *
		 */
		private static $instance = null;

		public function __construct()
		{
			$this->constants();
			$this->includes();
		}

		// instance
		public static function instance()
		{
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public static function locate_template( $template, $load = true )
		{
			$located     = '';
			$located_dir = '';
			$override    = apply_filters( 'ovic_override_framework', 'ovic-override' );
			if ( file_exists( get_stylesheet_directory() . '/' . $override . '/' . $template ) ) {
				$located = get_stylesheet_directory() . '/' . $override . '/' . $template;
			} elseif ( file_exists( get_template_directory() . '/' . $override . '/' . $template ) ) {
				$located = get_template_directory() . '/' . $override . '/' . $template;
			} elseif ( file_exists( OVIC_OPTIONS_DIR . '/' . $template ) ) {
				$located = OVIC_OPTIONS_DIR . '/' . $template;
			}
			if ( $load && !empty( $located ) ) {
				global $wp_query;
				if ( is_object( $wp_query ) && function_exists( 'load_template' ) ) {
					load_template( $located, true );
				} else {
					require_once( $located );
				}
			}
			if ( !$load ) {
				$located_dir = OVIC_OPTIONS_DIR . '/' . $template;
			}

			return $located_dir;
		}

		// Define constants
		public function constants()
		{
			$dirname        = wp_normalize_path( dirname( dirname( __FILE__ ) ) );
			$theme_dir      = wp_normalize_path( get_template_directory() );
			$plugin_dir     = wp_normalize_path( WP_PLUGIN_DIR );
			$located_plugin = ( preg_match( '#' . sanitize_file_name( $plugin_dir ) . '#', sanitize_file_name( $dirname ) ) ) ? true : false;
			$directory      = ( $located_plugin ) ? $plugin_dir : $theme_dir;
			$directory_uri  = ( $located_plugin ) ? WP_PLUGIN_URL : get_template_directory_uri();
			$foldername     = str_replace( $directory, '', $dirname );
			defined( 'OVIC_OPTIONS_DIR' ) or define( 'OVIC_OPTIONS_DIR', $directory . $foldername );
			defined( 'OVIC_OPTIONS_URL' ) or define( 'OVIC_OPTIONS_URL', $directory_uri . $foldername );
		}

		// Includes framework files
		public function includes()
		{
			// includes helpers
			$this->locate_template( 'functions/helpers.php' );
			$this->locate_template( 'functions/deprecated.php' );
			$this->locate_template( 'functions/fallback.php' );
			$this->locate_template( 'functions/actions.php' );
			$this->locate_template( 'functions/enqueue.php' );
			$this->locate_template( 'functions/sanitize.php' );
			$this->locate_template( 'functions/validate.php' );
			// includes classes
			$this->locate_template( 'classes/abstract.class.php' );
			$this->locate_template( 'classes/fields.class.php' );
			$this->locate_template( 'classes/framework.class.php' );
			$this->locate_template( 'classes/customize.class.php' );
			$this->locate_template( 'classes/taxonomy.class.php' );
			$this->locate_template( 'classes/metabox.class.php' );
			// includes classes
			do_action( 'ovic_includes' );
		}
	}

	OVIC::instance();
}