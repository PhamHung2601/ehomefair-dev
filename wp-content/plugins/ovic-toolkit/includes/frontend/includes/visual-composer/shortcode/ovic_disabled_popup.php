<?php
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * Shortcode class
 * @var $this "Ovic_Shortcode_Disabled_Popup"
 * @version 1.0.0
 */
if ( !class_exists( 'Ovic_Shortcode_Disabled_Popup' ) ) {
	class Ovic_Shortcode_Disabled_Popup extends Ovic_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = 'disabled_popup';

		static public function add_css_generate( $atts )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_disabled_popup', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css = '';

			return apply_filters( 'Ovic_Shortcode_Disabled_Popup_css', $css, $atts );
		}

		public function output_html( $atts, $content = null )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_disabled_popup', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css_class   = array( 'ovic-disabled-popup' );
			$css_class[] = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, '', 'ovic_disabled_popup', $atts );
			ob_start();
			?>
            <div class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
                <div class="checkbox btn-checkbox">
                    <label for="ovic_disabled_popup_by_user">
                        <input id="ovic_disabled_popup_by_user" name="ovic_disabled_popup_by_user"
                               class="ovic_disabled_popup_by_user" type="checkbox">
                        <span><?php echo wp_specialchars_decode( $atts['text'] ); ?></span>
                    </label>
                </div>
            </div>
			<?php
			$html = ob_get_clean();

			return apply_filters( 'Ovic_Shortcode_Disabled_Popup', $html, $atts, $content );
		}
	}

	new Ovic_Shortcode_Disabled_Popup();
}