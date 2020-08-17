<?php
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * Shortcode class
 * @var $this "Ovic_Accordion"
 * @version 1.0.0
 */
if ( !class_exists( 'Ovic_Shortcode_Accordion' ) ) {
	class Ovic_Shortcode_Accordion extends Ovic_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = 'accordion';

		static public function add_css_generate( $atts )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_accordion', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css = '';

			return apply_filters( 'Ovic_Shortcode_Accordion_css', $css, $atts );
		}

		public function output_html( $atts, $content = null )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_accordion', $atts ) : $atts;
			extract( $atts );
			$css_class   = array( 'ovic-accordion' );
			$css_class[] = isset( $atts['style'] ) ? $atts['style'] : '';
			$css_class[] = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, '', 'ovic_accordion', $atts );
			$sections    = self::get_all_attributes( 'vc_tta_section', $content );
			$rand        = uniqid();
			ob_start(); ?>
            <div class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
				<?php if ( $sections && is_array( $sections ) && count( $sections ) > 0 ):
					foreach ( $sections as $key => $section ): ?>
						<?php
						/* Get icon from section tabs */
						$section['i_type'] = isset( $section['i_type'] ) ? $section['i_type'] : 'fontawesome';
						$add_icon          = isset( $section['add_icon'] ) ? $section['add_icon'] : '';
						$position_icon     = isset( $section['i_position'] ) ? $section['i_position'] : '';
						$icon_html         = $this->constructIcon( $section );
						?>
                        <div class="panel panel-default<?php echo $key == $atts['active_section'] ? ' active' : ''; ?>">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a class="<?php echo $key == $atts['active_section'] ? 'loaded' : ''; ?>"
                                       data-ajax="<?php echo esc_attr( $atts['ajax_check'] ) ?>"
                                       data-id="<?php echo get_the_ID(); ?>"
                                       data-animate="<?php echo esc_attr( $atts['css_animation'] ); ?>"
                                       href="#<?php echo esc_attr( $section['tab_id'] ); ?>-<?php echo esc_attr( $rand ); ?>"
                                       data-section="<?php echo esc_attr( $section['tab_id'] ); ?>">
										<?php echo ( 'true' === $add_icon && 'right' !== $position_icon ) ? $icon_html : ''; ?>
                                        <span><?php echo esc_html( $section['title'] ); ?></span>
										<?php echo ( 'true' === $add_icon && 'right' === $position_icon ) ? $icon_html : ''; ?>
                                    </a>
                                </h4>
                            </div>
                            <div id="<?php echo esc_attr( $section['tab_id'] ); ?>-<?php echo esc_attr( $rand ); ?>"
                                 class="panel-collapse<?php echo $key != $atts['active_section'] ? ' collapse' : ''; ?>">
								<?php if ( $atts['ajax_check'] == '1' ) :
									echo $key == $atts['active_section'] ? do_shortcode( $section['content'] ) : '';
								else :
									echo do_shortcode( $section['content'] );
								endif; ?>
                            </div>
                        </div>
					<?php endforeach;
				endif; ?>
            </div>
			<?php
			$html = ob_get_clean();

			return apply_filters( 'Ovic_Shortcode_Accordion', $html, $atts, $content );
		}
	}

	new Ovic_Shortcode_Accordion();
}