<?php
/**
 * Easy Digital Downloads Theme Updater
 *
 * @package EDD Theme Updater
 */
// Includes the files needed for the theme updater
if ( !class_exists( 'EDD_Theme_Updater_Admin' ) ) {
	include( dirname( __FILE__ ) . '/theme-updater-admin.php' );
}
if ( !class_exists( 'Ovic_Updater_Theme' ) ) {
	class Ovic_Updater_Theme
	{
		public function __construct()
		{
			$theme_data      = wp_get_theme();
			$file_stylesheet = trailingslashit( get_template_directory() ) . 'style.css';
			$theme_info      = get_file_data( $file_stylesheet, array( 'market' => 'Market' ) );
			$market          = ( isset( $theme_info['market'] ) ) ? $theme_info['market'] : '';
			// No support Update Theme On Themeforet
			if ( $market == 'Envato' ) return false;
			if ( $market == 'Templatemonster' ) return false;
			// Loads the updater classes
			$updater = new EDD_Theme_Updater_Admin(
			// Config settings
				$config = array(
					'remote_api_url' => 'http://kutethemes.com', // Site where EDD is hosted
					'item_name'      => $theme_data->get( 'Name' ), // Name of theme
					'theme_slug'     => $theme_data->get( 'TextDomain' ), // Theme slug
					'version'        => $theme_data->get( 'Version' ), // The current version of this theme
					'author'         => $theme_data->get( 'Author' ), // The author of this theme
					'download_id'    => '', // Optional, used for generating a license renewal link
					'renew_url'      => '' // Optional, allows for a custom license renewal link
				),
				// Strings
				$strings = array(
					'theme-license'             => __( 'Theme License', 'ovic-toolkit' ),
					'enter-key'                 => __( 'Enter your theme license key.', 'ovic-toolkit' ),
					'license-key'               => __( 'License Key', 'ovic-toolkit' ),
					'license-action'            => __( 'License Action', 'ovic-toolkit' ),
					'deactivate-license'        => __( 'Deactivate License', 'ovic-toolkit' ),
					'activate-license'          => __( 'Activate License', 'ovic-toolkit' ),
					'status-unknown'            => __( 'License status is unknown.', 'ovic-toolkit' ),
					'renew'                     => __( 'Renew?', 'ovic-toolkit' ),
					'unlimited'                 => __( 'unlimited', 'ovic-toolkit' ),
					'license-key-is-active'     => __( 'License key is active.', 'ovic-toolkit' ),
					'expires%s'                 => __( 'Expires %s.', 'ovic-toolkit' ),
					'%1$s/%2$-sites'            => __( 'You have %1$s / %2$s sites activated.', 'ovic-toolkit' ),
					'license-key-expired-%s'    => __( 'License key expired %s.', 'ovic-toolkit' ),
					'license-key-expired'       => __( 'License key has expired.', 'ovic-toolkit' ),
					'license-keys-do-not-match' => __( 'License keys do not match.', 'ovic-toolkit' ),
					'license-is-inactive'       => __( 'License is inactive.', 'ovic-toolkit' ),
					'license-key-is-disabled'   => __( 'License key is disabled.', 'ovic-toolkit' ),
					'site-is-inactive'          => __( 'Site is inactive.', 'ovic-toolkit' ),
					'license-status-unknown'    => __( 'License status is unknown.', 'ovic-toolkit' ),
					'update-notice'             => __( "Updating this theme will lose any customizations you have made. 'Cancel' to stop, 'OK' to update.", 'ovic-toolkit' ),
					'update-available'          => __( '<strong>%1$s %2$s</strong> is available. <a href="%3$s" class="thickbox" title="%4s">Check out what\'s new</a> or <a href="%5$s"%6$s>update now</a>.', 'ovic-toolkit' ),
				)
			);
		}
	}

	new Ovic_Updater_Theme();
}



