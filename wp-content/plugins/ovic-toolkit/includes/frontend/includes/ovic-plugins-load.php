<?php
if ( !class_exists( 'Ovic_PluginLoad' ) ) {
	class Ovic_PluginLoad
	{
		public $plugins = array();
		public $config  = array();

		public function __construct()
		{
			$this->plugins();
			$this->config();
			if ( !class_exists( 'TGM_Plugin_Activation' ) ) {
				return false;
			}
			if ( function_exists( 'tgmpa' ) ) {
				tgmpa( $this->plugins, $this->config );
			}
		}

		public function plugins()
		{
			$this->plugins = array(
				array(
					'name'               => 'Ovic Demo', // The plugin name
					'slug'               => 'ovic-demo', // The plugin slug (typically the folder name)
					'source'             => esc_url( 'https://plugins.kutethemes.net/ovic-demo.zip' ), // The plugin source
					'required'           => true, // If false, the plugin is only 'recommended' instead of required
					'version'            => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
					'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
					'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
					'external_url'       => '', // If set, overrides default API URL and points to an external URL
				),
			);
		}

		public function config()
		{
			$this->config = array(
				'id'           => 'ovic',                 // Unique ID for hashing notices for multiple instances of TGMPA.
				'default_path' => '',                      // Default absolute path to bundled plugins.
				'menu'         => 'ovic-install-plugins', // Menu slug.
				'parent_slug'  => 'themes.php',            // Parent menu slug.
				'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
				'has_notices'  => true,                    // Show admin notices or not.
				'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
				'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
				'is_automatic' => true,                   // Automatically activate plugins after installation or not.
				'message'      => '',                      // Message to output right before the plugins table.
			);
		}
	}
}
if ( !function_exists( 'Ovic_PluginLoad' ) ) {
	function Ovic_PluginLoad()
	{
		$server_name = $_SERVER['SERVER_NAME'];
		if ( has_action( 'tgmpa_register' ) && strpos( $server_name, 'kutethemes' ) !== false || strpos( $server_name, 'kute-themes' ) !== false ) {
			new  Ovic_PluginLoad();
		}
	}
}
add_action( 'tgmpa_register', 'Ovic_PluginLoad' );