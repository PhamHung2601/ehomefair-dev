<?php if ( !defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Framework admin enqueue style and scripts
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_admin_enqueue_scripts' ) ) {
	function ovic_admin_enqueue_scripts()
	{
		// check for developer mode
		$ovic_uri = OVIC_OPTIONS_URL;
		if ( is_ssl() ) {
			$ovic_uri = str_replace( 'http://', 'https://', OVIC_OPTIONS_URL );
		}
		// admin utilities
		wp_enqueue_media();
		// wp core styles
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'jquery-ui-datepicker' );
		// framework core styles
		wp_enqueue_style( 'ovic-options', $ovic_uri . '/assets/css/ovic.min.css', array(), '1.0.0', 'all' );
		if ( is_rtl() ) {
			wp_enqueue_style( 'ovic-options', $ovic_uri . '/assets/css/ovic-rtl.min.css', array(), '1.0.0', 'all' );
		}
		// wp core scripts
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		// framework core scripts
		wp_enqueue_script( 'ovic-plugins', $ovic_uri . '/assets/js/ovic-plugins.min.js', array(), '1.0.0', true );
		wp_enqueue_script( 'ovic-options', $ovic_uri . '/assets/js/ovic.min.js', array( 'ovic-plugins' ), '1.0.0', true );
	}

	add_action( 'admin_enqueue_scripts', 'ovic_admin_enqueue_scripts', 999 );
}
