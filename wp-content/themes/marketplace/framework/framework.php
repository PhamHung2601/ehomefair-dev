<?php
// Prevent direct access to this file
defined( 'ABSPATH' ) || die( 'Direct access to this file is not allowed.' );
/**
 * Core class.
 *
 * @package  Marketplace
 * @since    1.0
 */
if ( !class_exists( 'Marketplace_framework' ) ) {
	class Marketplace_framework
	{
		/**
		 * Define theme version.
		 *
		 * @var  string
		 */
		const VERSION = '1.0.0';

		public function __construct()
		{
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_filter( 'body_class', array( $this, 'body_class' ) );
			$this->includes();
			if ( !has_filter( 'ovic_resize_image' ) ) {
				add_filter( 'ovic_resize_image', array( $this, 'ovic_resize_image' ), 10, 5 );
			}
		}

		function body_class( $classes )
		{
			$my_theme  = wp_get_theme();
			$classes[] = $my_theme->get( 'Name' ) . "-" . $my_theme->get( 'Version' );

			return $classes;
		}

		public function enqueue_scripts()
		{
			/* CUSTOM FRAMEWORK */
			wp_enqueue_style( 'flaticon', get_theme_file_uri( '/assets/fonts/flaticon/flaticon.css' ), array(), '1.0' );
			wp_enqueue_style( 'custom-admin-css', get_theme_file_uri( '/framework/assets/admin.css' ), array(), '1.0' );
			wp_enqueue_script( 'custom-admin-js', get_theme_file_uri( '/framework/assets/admin.js' ), array(), '1.0', true );
		}

		public function includes()
		{
			/* Classes */
			require_once get_parent_theme_file_path( '/framework/includes/class-tgm-plugin-activation.php' );
			/*Plugin load*/
			require_once get_parent_theme_file_path( '/framework/settings/plugins-load.php' );
			/*Theme Functions*/
			require_once get_parent_theme_file_path( '/framework/includes/theme-functions.php' );
			require_once get_parent_theme_file_path( '/framework/settings/theme-options.php' );
			/* Custom css and js*/
			require_once get_parent_theme_file_path( '/framework/settings/custom-css.php' );
			// Register custom shortcodes
			if ( class_exists( 'Vc_Manager' ) ) {
				require_once get_parent_theme_file_path( '/framework/includes/visual-composer.php' );
			}
			if ( class_exists( 'WooCommerce' ) ) {
				require_once get_parent_theme_file_path( '/framework/includes/woo-functions.php' );
				if ( class_exists( 'Ovic_Toolkit' ) ) {
					require_once get_parent_theme_file_path( '/framework/widgets/widget-product-slide.php' );
					require_once get_parent_theme_file_path( '/framework/widgets/widget-product-filter.php' );
				}
			}
			/* WIDGET */
			if ( class_exists( 'Ovic_Toolkit' ) ) {
				require_once get_parent_theme_file_path( '/framework/widgets/widget-blog.php' );
				require_once get_parent_theme_file_path( '/framework/widgets/widget-keyword.php' );
			}
		}

		function ovic_resize_image( $attach_id, $width, $height, $crop = false, $use_lazy = false, $full_img = false )
		{
			if ( $attach_id ) {
				$image_src = wp_get_attachment_image_src( $attach_id, 'full' );
				$image_alt = get_post_meta( $attach_id, '_wp_attachment_image_alt', true );
				$vt_image  = array(
					'url'    => $image_src[0],
					'width'  => $image_src[1],
					'height' => $image_src[2],
					'img'    => '<img class="img-responsive" src="' . esc_url( $image_src[0] ) . '" ' . image_hwstring( $image_src[1], $image_src[2] ) . ' alt="' . $image_alt . '">',
				);
			} else {
				return array(
					'url'    => '',
					'width'  => '',
					'height' => '',
					'img'    => '',
				);
			}

			return $vt_image;
		}
	}

	new Marketplace_framework();
}