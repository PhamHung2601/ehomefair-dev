<?php
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * Shortcode class
 * @var $this "Ovic_Slide"
 * @version 1.0.0
 */
if ( !class_exists( 'Ovic_Shortcode_Slide' ) ) {
	class Ovic_Shortcode_Slide extends Ovic_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = 'slide';

		static public function add_css_generate( $atts )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_slide', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css = '';

			return apply_filters( 'Ovic_Shortcode_Slide_css', $css, $atts );
		}

		public function output_html( $atts, $content = null )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_slide', $atts ) : $atts;
			extract( $atts );
			$css_class   = array( 'ovic-slide' );
			$css_class[] = $atts['owl_rows_space'];
			$css_class[] = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, '', 'ovic_slide', $atts );
			/* START */
			$class_slide  = array( 'owl-slick' );
			$owl_settings = apply_filters( 'ovic_carousel_data_attributes', 'owl_', $atts );
			if ( $atts['owl_navigation_style'] )
				$class_slide[] = $atts['owl_navigation_style'];
			ob_start(); ?>
            <div class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
				<?php
				if ( $atts['slider_title'] )
					$this->ovic_title_shortcode( $atts['slider_title'] );
				?>
                <div class="<?php echo esc_attr( implode( ' ', $class_slide ) ); ?>" <?php echo esc_attr( $owl_settings ); ?>>
					<?php echo wpb_js_remove_wpautop( $content ); ?>
                </div>
            </div>
			<?php
			$html = ob_get_clean();

			return apply_filters( 'Ovic_Shortcode_Slide', $html, $atts, $content );
		}
	}

	new Ovic_Shortcode_Slide();
}