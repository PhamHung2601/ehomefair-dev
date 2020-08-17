<?php
if ( !class_exists( 'Ovic_Import_Database_Settings' ) ) {
	class Ovic_Import_Database_Settings
	{
		public static function plugins()
		{
			$plugins = array();
			$plugins = apply_filters( 'import_sample_data_required_plugins', $plugins );

			return $plugins;
		}

		public static function demo_site_pattern()
		{
			$demo_site_pattern = '';
			$demo_site_pattern = apply_filters( 'import_sample_data_demo_site_pattern', $demo_site_pattern );

			return $demo_site_pattern;
		}

		public static function theme_option_key()
		{
			$theme_option_key = '';
			$theme_option_key = apply_filters( 'import_sample_data_theme_option_key', $theme_option_key );

			return $theme_option_key;
		}
	}
}