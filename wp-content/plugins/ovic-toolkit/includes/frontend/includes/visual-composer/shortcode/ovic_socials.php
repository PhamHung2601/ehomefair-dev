<?php
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * Shortcode class
 * @var $this "Ovic_Socials"
 * @version 1.0.0
 */
if ( !class_exists( 'Ovic_Shortcode_Socials' ) ) {
	class Ovic_Shortcode_Socials extends Ovic_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = 'socials';

		static public function add_css_generate( $atts )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_socials', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css = '';

			return apply_filters( 'Ovic_Shortcode_Socials_css', $css, $atts );
		}

		public function output_html( $atts, $content = null )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_socials', $atts ) : $atts;
			extract( $atts );
			$css_class       = array( 'ovic-socials widget-socials' );
			$css_class[]     = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, '', 'ovic_socials', $atts );
			$all_socials     = apply_filters( 'ovic_get_option', 'user_all_social' );
			$get_all_socials = explode( ',', $atts['socials'] );
			ob_start(); ?>
            <div class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
                <div class="content-socials">
					<?php if ( !empty( $get_all_socials ) ) : ?>
                        <ul class="socials-list">
							<?php foreach ( $get_all_socials as $value ) : ?>
								<?php if ( isset( $all_socials[$value] ) ) :
									$array_socials = $all_socials[$value]; ?>
                                    <li>
                                        <a href="<?php echo esc_url( $array_socials['link_social'] ) ?>">
                                            <span class="<?php echo esc_attr( $array_socials['icon_social'] ); ?>"></span>
											<?php echo esc_html( $array_socials['title_social'] ); ?>
                                        </a>
                                    </li>
								<?php endif; ?>
							<?php endforeach; ?>
                        </ul>
					<?php endif; ?>
                </div>
            </div>
			<?php
			$html = ob_get_clean();

			return apply_filters( 'Ovic_Shortcode_Socials', $html, $atts, $content );
		}
	}

	new Ovic_Shortcode_Socials();
}