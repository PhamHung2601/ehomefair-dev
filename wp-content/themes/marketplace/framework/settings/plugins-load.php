<?php
if ( !class_exists( 'Marketplace_PluginLoad' ) ) {
	class Marketplace_PluginLoad
	{
		public $plugins = array();
		public $config  = array();

		public function __construct()
		{
			$this->plugins();
			$this->config();
			if ( !class_exists( 'TGM_Plugin_Activation' ) ) {
				return;
			}
			if ( function_exists( 'tgmpa' ) ) {
				tgmpa( $this->plugins, $this->config );
			}
		}

		public function plugins()
		{
			$this->plugins = array(
				array(
					'name'     => 'Ovic Toolkit',
					'slug'     => 'ovic-toolkit',
					'source'   => esc_url( 'http://plugins.kutethemes.net/ovic-toolkit.zip' ),
					'version'  => '',
					'required' => true,
				),
				array(
					'name'     => 'Ovic: Product Bundle',
					'slug'     => 'ovic-product-bundle',
					'required' => true,
				),
				array(
					'name'     => 'Ovic Import Demo',
					'slug'     => 'ovic-import-demo',
					'required' => true,
				),
				array(
					'name'     => 'Revolution Slider',
					'slug'     => 'revslider',
					'source'   => esc_url( 'http://plugins.kutethemes.net/revslider.zip' ),
					'required' => true,
					'version'  => '5.4.8.3',
				),
				array(
					'name'     => 'WPBakery Visual Composer',
					'slug'     => 'js_composer',
					'source'   => esc_url( 'https://plugins.kutethemes.net/js_composer.zip' ),
					'required' => true,
				),
				array(
					'name'     => 'WooCommerce',
					'slug'     => 'woocommerce',
					'required' => true,
				),
				array(
					'name' => 'YITH WooCommerce Compare',
					'slug' => 'yith-woocommerce-compare',
				),
				array(
					'name' => 'YITH WooCommerce Wishlist',
					'slug' => 'yith-woocommerce-wishlist',
				),
				array(
					'name' => 'YITH WooCommerce Quick View',
					'slug' => 'yith-woocommerce-quick-view',
				),
				array(
					'name' => 'Contact Form 7',
					'slug' => 'contact-form-7',
				),
			);
		}

		public function config()
		{
			$this->config = array(
				'id'           => 'marketplace',
				'default_path' => '',
				'menu'         => 'marketplace-install-plugins',
				'parent_slug'  => 'themes.php',
				'capability'   => 'edit_theme_options',
				'has_notices'  => true,
				'dismissable'  => true,
				'dismiss_msg'  => '',
				'is_automatic' => true,
				'message'      => '',
			);
		}
	}
}
if ( !function_exists( 'Marketplace_PluginLoad' ) ) {
	function Marketplace_PluginLoad()
	{
		new  Marketplace_PluginLoad();
	}
}
add_action( 'tgmpa_register', 'Marketplace_PluginLoad' );