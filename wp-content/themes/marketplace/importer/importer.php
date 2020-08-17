<?php
// Prevent direct access to this file
defined( 'ABSPATH' ) || die( 'Direct access to this file is not allowed.' );
/**
 * Core class.
 *
 * @package  Ovic
 * @since    1.0
 */
if ( !class_exists( 'Ovic_theme_Import' ) ) {
	class Ovic_theme_Import
	{
		/**
		 * Define theme version.
		 *
		 * @var  string
		 */
		const VERSION = '1.0.0';

		public function __construct()
		{
			add_filter( 'ovic_import_config', array( $this, 'ovic_import_config' ) );
			add_action( 'ovic_after_content_import', array( $this, 'after_content_import' ) );
			add_filter( 'ovic_import_wooCommerce_attributes', array( $this, 'ovic_import_wooCommerce_attributes' ) );
		}

		function ovic_import_wooCommerce_attributes()
		{
			$attributes = array(
				array(
					'attribute_label'   => 'Brands',
					'attribute_name'    => 'brands',
					'attribute_type'    => 'select', // text, box_style, select
					'attribute_orderby' => 'menu_order',
					'attribute_public'  => '0',
				),
				array(
					'attribute_label'   => 'Color',
					'attribute_name'    => 'color',
					'attribute_type'    => 'select', // text, box_style, select
					'attribute_orderby' => 'menu_order',
					'attribute_public'  => '0',
				),
				array(
					'attribute_label'   => 'Size',
					'attribute_name'    => 'size',
					'attribute_type'    => 'select', // text, box_style, select
					'attribute_orderby' => 'menu_order',
					'attribute_public'  => '0',
				),
			);

			return $attributes;
		}

		function ovic_import_config()
		{
			$registed_menu = array(
				'primary'        => esc_html__( 'Primary Menu', 'marketplace' ),
				'vertical_menu'  => esc_html__( 'Vertical Menu', 'marketplace' ),
				'top_left_menu'  => esc_html__( 'Top Menu First', 'marketplace' ),
				'top_right_menu' => esc_html__( 'Top Menu Second', 'marketplace' ),
			);
			$menu_location = array(
				'primary'        => 'Primary Menu',
				'vertical_menu'  => 'Vertical Menu',
				'top_left_menu'  => 'Top Menu First',
				'top_right_menu' => 'Top Menu Second',
			);
			$data_filter   = array(
				'data_import' => array(
					'main_demo'        => get_home_url( '/' ),
					'theme_option'     => get_template_directory() . '/importer/data/theme-options.txt',
					'setting_option'   => get_template_directory() . '/importer/data/setting-options.txt',
					'content_path'     => get_template_directory() . '/importer/data/content.xml',
					'content_path_rtl' => get_template_directory() . '/importer/data/content-rtl.xml',
					'widget_path'      => get_template_directory() . '/importer/data/widgets.wie',
					'revslider_path'   => get_template_directory() . '/importer/revsliders/',
				),
				'data_demos'  => array(
					array(
						'name'           => esc_html__( 'Demo', 'marketplace' ),
						'slug'           => 'home-01',
						'menus'          => $registed_menu,
						'homepage'       => 'Home 01',
						'blogpage'       => 'Blog',
						'preview'        => get_theme_file_uri( 'screenshot.jpg' ),
						'demo_link'      => get_home_url( '/' ),
						'menu_locations' => $menu_location,
						'theme_option'   => get_template_directory() . '/importer/data/theme-options.txt',
						'setting_option' => get_template_directory() . '/importer/data/setting-options.txt',
						'content_path'   => get_template_directory() . '/importer/data/content.xml',
						'widget_path'    => get_template_directory() . '/importer/data/widgets.wie',
						'revslider_path' => get_template_directory() . '/importer/revsliders/',
					),
					array(
						'name'           => esc_html__( 'Demo RTL', 'marketplace' ),
						'slug'           => 'home-01',
						'menus'          => $registed_menu,
						'homepage'       => 'Home 01',
						'blogpage'       => 'Blog',
						'preview'        => get_theme_file_uri( 'screenshot.jpg' ),
						'demo_link'      => get_home_url( '/' ),
						'menu_locations' => $menu_location,
						'theme_option'   => get_template_directory() . '/importer/data/theme-options.txt',
						'setting_option' => get_template_directory() . '/importer/data/setting-options.txt',
						'content_path'   => get_template_directory() . '/importer/data/content-rtl.xml',
						'widget_path'    => get_template_directory() . '/importer/data/widgets.wie',
						'revslider_path' => get_template_directory() . '/importer/revsliders/',
					),
				),
				'woo_single'  => 430, // Main image width
				'woo_catalog' => 240, // Thumbnail width
				'woo_ratio'   => '240:274', // Thumbnail cropping
			);

			return $data_filter;
		}

		public function after_content_import( $data )
		{
			$menus    = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
			$home_url = get_home_url( '/' );
			if ( !empty( $menus ) ) {
				foreach ( $menus as $menu ) {
					$items = wp_get_nav_menu_items( $menu->term_id );
					if ( !empty( $items ) ) {
						foreach ( $items as $item ) {
							$_menu_item_url = get_post_meta( $item->ID, '_menu_item_url', true );
							if ( !empty( $_menu_item_url ) ) {
								$_menu_item_url = str_replace( 'https://marketplace.kute-themes.com/', $home_url, $_menu_item_url );
								$_menu_item_url = str_replace( 'http://marketplace.kute-themes.com/', $home_url, $_menu_item_url );
								update_post_meta( $item->ID, '_menu_item_url', $_menu_item_url );
							}
						}
					}
				}
			}
		}
	}

	new Ovic_theme_Import();
}