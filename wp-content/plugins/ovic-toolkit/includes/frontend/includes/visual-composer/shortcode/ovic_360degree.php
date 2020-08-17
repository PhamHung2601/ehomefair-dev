<?php
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * Shortcode class
 * @var $this "Ovic_360degree"
 * @version 1.0.0
 */
if ( !class_exists( 'Ovic_Shortcode_360degree' ) ) {
	class Ovic_Shortcode_360degree extends Ovic_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = '360degree';

		static public function add_css_generate( $atts )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_360degree', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css = '';

			return apply_filters( 'Ovic_Shortcode_360degree_css', $css, $atts );
		}

		public function ovic_360degree_template( $gallery )
		{
			$i                = 0;
			$images_js_string = '';
			$id               = rand( 0, 999 );
			$images           = explode( ',', $gallery );
			$frames_count     = count( $images );
			if ( !empty( $images ) ):
				?>
                <div id="product-360-view" class="product-360-view-wrapper">
                    <div class="ovic-threed-view threed-id-<?php echo esc_attr( $id ); ?>">
                        <ul class="threed-view-images">
							<?php if ( count( $images ) > 0 ) {
								foreach ( $images as $img_id ) {
									$i++;
									$img              = wp_get_attachment_image_src( $img_id, 'full' );
									$images_js_string .= "'" . $img[0] . "'";
									$width            = $img[1];
									$height           = $img[2];
									if ( $i < $frames_count ) {
										$images_js_string .= ",";
									}
								}
							} ?>
                        </ul>
                        <div class="spinner">
                            <span>0%</span>
                        </div>
                    </div>
                    <script type="text/javascript">
                        jQuery(document).ready(function ($) {
                            window.addEventListener('load',
                                function (ev) {
                                    $('.threed-id-<?php echo esc_attr( $id ); ?>').ThreeSixty({
                                        totalFrames: <?php echo esc_attr( $frames_count ); ?>,
                                        endFrame: <?php echo esc_attr( $frames_count ); ?>,
                                        currentFrame: 1,
                                        imgList: '.threed-view-images',
                                        progress: '.spinner',
                                        imgArray: [<?php echo htmlspecialchars_decode( $images_js_string ); ?>],
                                        height: <?php echo esc_attr( $height ); ?>,
                                        width: <?php echo esc_attr( $width ); ?>,
                                        responsive: true,
                                        navigation: true
                                    });
                                }, false);
                        });
                    </script>
                </div>
			<?php
			endif;
		}

		public function output_html( $atts, $content = null )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_360degree', $atts ) : $atts;
			extract( $atts );
			$css_class   = array( 'ovic-360degree' );
			$css_class[] = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, '', 'ovic_360degree', $atts );
			/* START */
			ob_start(); ?>
            <div class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
				<?php
				if ( $atts['title'] ) {
					$this->ovic_title_shortcode( $atts['title'] );
				}
				$this->ovic_360degree_template( $atts['gallery_degree'] );
				?>
            </div>
			<?php
			$html = ob_get_clean();

			return apply_filters( 'Ovic_Shortcode_360degree', $html, $atts, $content );
		}
	}

	new Ovic_Shortcode_360degree();
}