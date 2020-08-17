<?php
/**
 * Ovic Sample Settings
 *
 * @author   KHANH
 * @category API
 * @package  Ovic_Sample_Settings
 * @since    1.0.1
 */
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( !class_exists( 'Ovic_Sample_Settings' ) ) {
	class Ovic_Sample_Settings
	{
		public function __construct()
		{
			// Filter Sample Data Menu
			add_filter( 'import_sample_data_packages', array( $this, 'import_sample_data_packages' ) );
			add_filter( 'import_sample_data_required_plugins', array( $this, 'import_sample_data_required_plugins' ) );
			add_filter( 'import_sample_data_demo_site_pattern', array( $this, 'import_sample_data_demo_site_pattern' ) );
			add_filter( 'import_sample_data_theme_option_key', array( $this, 'import_sample_data_theme_option_key' ) );

			add_action( 'import_sample_data_after_install_sample_data', array( $this, 'import_sample_data_after_install_sample_data' ), 10, 1 );
		}

		public function import_sample_data_demo_site_pattern( $demo_site_pattern )
		{
			$demo_site_pattern = 'https?(%3A|:)[%2F\\\\/]+(rc|demo|new-boutique)\.kutethemes\.net';

			return $demo_site_pattern;
		}

		public function import_sample_data_theme_option_key( $theme_option_key )
		{
			$theme_option_key = '_ovic_customize_options';

			return $theme_option_key;
		}

		public function import_sample_data_required_plugins( $plugins )
		{
			$plugins = array(
				array(
					'name'        => 'WPBakery Visual Composer',
					'slug'        => 'js_composer',
					'source'      => esc_url( 'https://plugins.kutethemes.net/js_composer.zip' ),
					'source_type' => 'external',
					'file_path'   => 'js_composer/js_composer.php',
				),
				array(
					'name'        => 'Revolution Slider',
					'slug'        => 'revslider',
					'source'      => esc_url( 'https://plugins.kutethemes.net/revslider.zip' ),
					'source_type' => 'external',
					'file_path'   => 'revslider/revslider.php',
				),
				array(
					'name'        => 'WooCommerce',
					'slug'        => 'woocommerce',
					'required'    => true,
					'file_path'   => 'woocommerce/woocommerce.php',
					'source_type' => 'repo',
				),
			);

			return $plugins;
		}

		public function import_sample_data_packages( $packages )
		{
			return array(
				'main' => array(
					'id'          => 'main',
					'name'        => 'Main Demo',
					'thumbnail'   => 'https://via.placeholder.com/400x200',
					'demo'        => 'https://envy.kutethemes.net',
					'download'    => 'http://localhost:8888/sample-data/ex-data.zip',
					'tags'        => array( 'all', 'simple' ),
					'main'        => true,
					'sample-page' => array(
						array(
							'name'      => 'Home Creative',
							'slug'      => 'home-creative',
							'thumbnail' => 'https://via.placeholder.com/180x130',
							'settings'  => array(
								'used_header' => 'style11',
								'footer_used' => 2934,
							),
						),
						array(
							'name'      => 'Home Categories #1',
							'slug'      => 'home',
							'thumbnail' => 'https://via.placeholder.com/180x130',
							'settings'  => array(
								'used_header' => 'default',
								'footer_used' => 541,
							),
						),
					),
				),
				//and more...
			);
		}

		public function import_sample_data_after_install_sample_data( $package )
		{
			// Do something here!
		}
	}
}

new Ovic_Sample_Settings();