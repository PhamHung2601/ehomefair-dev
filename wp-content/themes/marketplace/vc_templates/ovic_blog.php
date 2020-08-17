<?php
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * Shortcode class
 * @var $this "Ovic_Blog"
 */
if ( !class_exists( 'Ovic_Shortcode_Blog' ) ) {
	class Ovic_Shortcode_Blog extends Ovic_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = 'blog';

		public function output_html( $atts, $content = null )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_blog', $atts ) : $atts;
			extract( $atts );
			$css_class    = array( 'ovic-blog' );
			$css_class[]  = $atts['blog_style'];
			$css_class[]  = $atts['el_class'];
			$class_editor = isset( $atts['css'] ) ? vc_shortcode_custom_css_class( $atts['css'], ' ' ) : '';
			$css_class[]  = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_editor, 'ovic_blog', $atts );
			/* START */
			$i             = 0;
			$data_loop     = vc_build_loop_query( $atts['loop'] )[1];
			$owl_settings  = apply_filters( 'ovic_carousel_data_attributes', 'owl_', $atts );
			ob_start(); ?>
			<div class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
				<?php if ( $atts['blog_title'] ) : ?>
					<h4 class="ovic-title">
						<span><?php echo esc_html( $atts['blog_title'] ); ?></span>
					</h4>
				<?php endif; ?>
				<?php if ( $data_loop->have_posts() ) : ?>
					<div class="blog-grid blog-list-owl owl-slick" <?php echo esc_attr( $owl_settings ); ?>>
						<?php while ( $data_loop->have_posts() ) : $data_loop->the_post();
							$positions = array( 'blog-item post-item' );
							if ( $i % 2 == 0 ) {
								$positions[] = 'left';
							} else {
								$positions[] = 'right';
							}
							$i++;
							?>
							<article <?php post_class( $positions ); ?>>
								<div class="blog-inner">
									<?php do_action( 'get_template_blog', $atts['blog_style'] ); ?>
								</div>
							</article>
						<?php endwhile; ?>
					</div>
				<?php else :
					get_template_part( 'content', 'none' );
				endif; ?>
			</div>
			<?php
			$array_filter = array(
				$owl_settings,
				$data_loop,
			);
			wp_reset_postdata();
			$html = ob_get_clean();

			return apply_filters( 'Ovic_Shortcode_Blog', $html, $atts, $content, $array_filter );
		}
	}

	new Ovic_Shortcode_Blog();
}