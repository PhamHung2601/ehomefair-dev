<?php
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * Shortcode class
 * @var $this "Ovic_Blog"
 * @version 1.0.0
 */
if ( !class_exists( 'Ovic_Shortcode_Blog_2' ) ) {
	class Ovic_Shortcode_Blog_2 extends Ovic_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = 'blog_2';

		static public function add_css_generate( $atts )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_blog_2', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css = '';

			return apply_filters( 'Ovic_Shortcode_Blog_2_css', $css, $atts );
		}

		public function output_html( $atts, $content = null )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_blog_2', $atts ) : $atts;
			extract( $atts );
			$css_class   = array( 'ovic-blog' );
			$css_class[] = isset( $atts['blog_style'] ) ? $atts['blog_style'] : '';
			$css_class[] = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, '', 'ovic_blog_2', $atts );
			/* START */
			$i               = 0;
			$post_item_class = array( 'blog-item', $atts['blog_style'] );
			$post_list_class = array();
			list( $args, $query ) = vc_build_loop_query( $atts['loop'], get_option( 'sticky_posts' ) );
			$owl_settings = '';
			if ( $atts['postliststyle'] == 'grid' ) {
				$post_list_class[] = 'row auto-clear equal-container better-height ';
				$post_item_class[] = Ovic_Field_Advandce::generate_grid_attr( $atts['bootstrap'] );
			}
			if ( $atts['postliststyle'] == 'owl' ) {
				$post_list_class[] = 'owl-slick';
				$post_item_class[] = $atts['owl_rows_space'];
				$owl_settings      = Ovic_Field_Advandce::generate_slide_attr( $atts['carousel'] );
			}
			ob_start(); ?>
			<div class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
				<?php
				if ( $atts['blog_title'] )
					$this->ovic_title_shortcode( $atts['blog_title'] );
				?>
				<?php if ( $query->have_posts() ) : ?>
					<div class="<?php echo esc_attr( implode( ' ', $post_list_class ) ); ?>" <?php echo esc_attr( $owl_settings ); ?>>
						<?php while ( $query->have_posts() ) : $query->the_post();
							if ( $i % 2 == 0 ) {
								$post_item_class['position'] = 'left';
							} else {
								$post_item_class['position'] = 'right';
							}
							$i++;
							$post_item_class = apply_filters( 'ovic_template_blog_class', $post_item_class, $atts );
							?>
							<article <?php post_class( $post_item_class ); ?>>
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
				'carousel' => $owl_settings,
				'query'    => $args,
			);
			wp_reset_postdata();
			$html = ob_get_clean();

			return apply_filters( 'Ovic_Shortcode_Blog_2', $html, $atts, $content, $array_filter );
		}
	}

	new Ovic_Shortcode_Blog_2();
}