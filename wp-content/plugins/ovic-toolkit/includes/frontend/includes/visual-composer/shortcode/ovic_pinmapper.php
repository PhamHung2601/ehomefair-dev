<?php
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * Shortcode class
 * @var $this "Ovic_Pinmapper"
 * @version 1.0.0
 */
if ( !class_exists( 'Ovic_Shortcode_Pinmapper' ) ) {
	class Ovic_Shortcode_Pinmapper extends Ovic_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = 'pinmapper';

		static public function add_css_generate( $atts )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_pinmapper', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css = '';

			return apply_filters( 'Ovic_Shortcode_Pinmapper_css', $css, $atts );
		}

		public function output_html( $atts, $content = null )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_pinmapper', $atts ) : $atts;
			extract( $atts );
			$css_class   = array( 'ovic-pinmapper' );
			$css_class[] = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, '', 'ovic_pinmapper', $atts );
			ob_start(); ?>
            <div class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
				<?php echo do_shortcode( '[ovic_mapper id="' . $atts['pinmaper_style'] . '"]' ); ?>
            </div>
			<?php
			$html = ob_get_clean();

			return apply_filters( 'Ovic_Shortcode_Pinmapper', $html, $atts, $content );
		}
	}

	new Ovic_Shortcode_Pinmapper();
}