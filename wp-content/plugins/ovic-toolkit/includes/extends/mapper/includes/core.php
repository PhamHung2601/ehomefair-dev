<?php
if ( !class_exists( 'Ovic_Mapper' ) ) {
	class Ovic_Mapper
	{
		/**
		 * Variable to hold class prefix supported for autoloading.
		 *
		 * @var  string
		 */
		protected static $prefix = 'Ovic_Mapper_';

		/**
		 * Initialize.
		 *
		 * @return  void
		 */
		public static function initialize()
		{
			// Register class autoloader.
			spl_autoload_register( array( __CLASS__, 'autoload' ) );
			// Initialize custom post type.
			Ovic_Mapper_Post_Type::initialize();
			// Initialize shortcode.
			Ovic_Mapper_Shortcode::initialize();
			// Add image size for woocommerce product
			$size_thumb = apply_filters( 'ovic_pinmap_product_thumbnail', array( 'width' => 100, 'height' => 150, 'crop' => true ) );
			add_image_size( 'ovic-wc-thumbnail', $size_thumb['width'], $size_thumb['height'], $size_thumb['crop'] );
		}

		/**
		 * Method to autoload class declaration file.
		 *
		 * @param   string $class_name Name of class to load declaration file for.
		 *
		 * @return  mixed
		 */
		public static function autoload( $class_name )
		{
			// Verify class prefix.
			if ( 0 !== strpos( $class_name, self::$prefix ) ) {
				return false;
			}
			// Generate file path from class name.
			$base = OVIC_TOOLKIT_PLUGIN_DIR . '/includes/extends/mapper/includes/';
			$path = strtolower( str_replace( '_', '/', substr( $class_name, strlen( self::$prefix ) ) ) );
			// Check if class file exists.
			$standard    = $path . '.php';
			$alternative = $path . '/' . basename( $path ) . '.php';
			while ( true ) {
				// Check if file exists in standard path.
				if ( @is_file( $base . $standard ) ) {
					$exists = $standard;
					break;
				}
				// Check if file exists in alternative path.
				if ( @is_file( $base . $alternative ) ) {
					$exists = $alternative;
					break;
				}
				// If there is no more alternative file, quit the loop.
				if ( false === strrpos( $standard, '/' ) || 0 === strrpos( $standard, '/' ) ) {
					break;
				}
				// Generate more alternative files.
				$standard    = preg_replace( '#/([^/]+)$#', '-\\1', $standard );
				$alternative = dirname( $standard ) . '/' . substr( basename( $standard ), 0, -4 ) . '/' . basename( $standard );
			}
			// Include class declaration file if exists.
			if ( isset( $exists ) ) {
				return include_once $base . $exists;
			}

			return false;
		}
	}

	$ovic_papper = new Ovic_Mapper();
	$ovic_papper::initialize();
}
