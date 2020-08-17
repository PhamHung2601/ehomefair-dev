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
if ( !class_exists( 'Ovic_Shortcode_Blog' ) ) {
	class Ovic_Shortcode_Blog extends Ovic_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = 'blog';

		static public function add_css_generate( $atts )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_blog', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css = '';

			return apply_filters( 'Ovic_Shortcode_Blog_css', $css, $atts );
		}

		public function output_html( $atts, $content = null )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_blog', $atts ) : $atts;
			extract( $atts );
			$css_class   = array( 'ovic-blog' );
			$css_class[] = isset( $atts['blog_style'] ) ? $atts['blog_style'] : '';
			$css_class[] = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, '', 'ovic_blog', $atts );
			/* START */
			$i           = 0;
			$class_item  = array( 'blog-item' );
			$class_slide = array( 'owl-slick blog-list-owl equal-container better-height' );
			list( $args, $query ) = vc_build_loop_query( $atts['loop'], get_option( 'sticky_posts' ) );
			$total_post   = $query->post_count;
			$owl_settings = apply_filters( 'ovic_carousel_data_attributes', 'owl_', $atts );
			if ( $atts['owl_navigation_style'] )
				$class_slide[] = $atts['owl_navigation_style'];
			if ( $atts['owl_rows_space'] )
				$class_item[] = $atts['owl_rows_space'];
			$css_class[] = ( function_exists( 'ovic_generate_class_nav' ) ) ? ovic_generate_class_nav( 'owl_', $atts, $total_post ) : '';
			ob_start(); ?>
            <div class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
				<?php
				if ( $atts['blog_title'] )
					$this->ovic_title_shortcode( $atts['blog_title'] );
				?>
				<?php if ( $query->have_posts() ) : ?>
                    <div class="<?php echo esc_attr( implode( ' ', $class_slide ) ); ?>" <?php echo esc_attr( $owl_settings ); ?>>
						<?php while ( $query->have_posts() ) : $query->the_post();
							if ( $i % 2 == 0 ) {
								$class_item['position'] = 'left';
							} else {
								$class_item['position'] = 'right';
							}
							$i++;
							$class_item = apply_filters( 'ovic_template_blog_class', $class_item, $atts );
							?>
                            <article <?php post_class( $class_item ); ?>>
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
				'query'    => $query,
			);
			wp_reset_postdata();
			$html = ob_get_clean();

			return apply_filters( 'Ovic_Shortcode_Blog', $html, $atts, $content, $array_filter );
		}
	}

	new Ovic_Shortcode_Blog();
}