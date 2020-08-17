<?php
// Prevent direct access to this file
defined( 'ABSPATH' ) || die( 'Direct access to this file is not allowed.' );
/**
 * Core class.
 *
 * @package  Ovic
 * @since    1.0
 */
if ( !class_exists( 'Ovic_Import_Database_Content' ) ) {
	class Ovic_Import_Database_Content
	{
		public function __construct()
		{
			// Filter Sample Data Menu
			add_filter( 'import_sample_data_packages', array( $this, 'sample_data_packages' ) );
			add_filter( 'import_sample_data_required_plugins', array( $this, 'required_plugins' ) );
			add_filter( 'import_sample_data_demo_site_pattern', array( $this, 'site_pattern' ) );
			add_filter( 'import_sample_data_theme_option_key', array( $this, 'theme_option_key' ) );

			add_action( 'import_sample_data_after_install_sample_data', array( $this, 'after_install_data' ), 10, 1 );
		}

		public function site_pattern( $demo_site_pattern )
		{
			return '{site_pattern}';
		}

		public function theme_option_key( $theme_option_key )
		{
			return '{option_key}';
		}

		public function required_plugins( $plugins )
		{
			return /*plugins*/ ;
		}

		public function sample_data_packages( $packages )
		{
			return array(
				'main' => array(
					'id'        => 'main',
					'name'      => 'Main Demo',
					'thumbnail' => wp_get_theme()->get_screenshot(),
					'demo'      => '{home_urls}',
					'download'  => get_theme_file_path( 'importer/data/sample-data.zip' ),
					'tags'      => array( 'all', 'simple' ),
					'main'      => true,
				),
			);
		}

		public function after_install_data( $package )
		{
			$menus    = get_terms(
				'nav_menu',
				array(
					'hide_empty' => true,
				)
			);
			$home_url = get_home_url();
			if ( !empty( $menus ) ) {
				foreach ( $menus as $menu ) {
					$items = wp_get_nav_menu_items( $menu->term_id );
					if ( !empty( $items ) ) {
						foreach ( $items as $item ) {
							$_menu_item_url = get_post_meta( $item->ID, '_menu_item_url', true );
							if ( !empty( $_menu_item_url ) ) {
								$_menu_item_url = str_replace( '{home_urls}', $home_url, $_menu_item_url );
								$_menu_item_url = str_replace( '{home_url}', $home_url, $_menu_item_url );
								update_post_meta( $item->ID, '_menu_item_url', $_menu_item_url );
							}
						}
					}
				}
			}
		}
	}

	new Ovic_Import_Database_Content();
}
