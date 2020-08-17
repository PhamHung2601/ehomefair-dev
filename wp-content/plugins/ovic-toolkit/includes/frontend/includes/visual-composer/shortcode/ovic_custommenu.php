<?php
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * Shortcode class
 * @var $this "Ovic_Shortcode_Custommenu"
 * @version 1.0.0
 */
if ( !class_exists( 'Ovic_Shortcode_Custommenu' ) ) {
	class Ovic_Shortcode_Custommenu extends Ovic_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = 'custommenu';

		static public function add_css_generate( $atts )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_custommenu', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css = '';

			return apply_filters( 'Ovic_Shortcode_Custommenu_css', $css, $atts );
		}

		public function output_html( $atts, $content = null )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_custommenu', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css_class   = array( 'ovic-custommenu vc_wp_custommenu wpb_content_element' );
			$css_class[] = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, '', 'ovic_custommenu', $atts );
			ob_start();
			$type = 'WP_Nav_Menu_Widget';
			$args = array();
			global $wp_widget_factory;
			?>
            <div class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
				<?php
				if ( is_object( $wp_widget_factory ) && isset( $wp_widget_factory->widgets, $wp_widget_factory->widgets[$type] ) ) {
					the_widget( $type, $atts, $args );
				} else {
					echo esc_html__( 'No content.', 'ovic-toolkit' );
				}
				?>
            </div>
			<?php
			$html = ob_get_clean();

			return apply_filters( 'Ovic_Shortcode_Custommenu', $html, $atts, $content );
		}
	}

	new Ovic_Shortcode_Custommenu();
}