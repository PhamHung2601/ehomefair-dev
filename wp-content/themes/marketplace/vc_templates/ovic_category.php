<?php
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * Shortcode class
 * @var $this "Ovic_Category"
 */
if ( !class_exists( 'Ovic_Shortcode_Category' ) ) {
	class Ovic_Shortcode_Category extends Ovic_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = 'category';

		public function output_html( $atts, $content = null )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_category', $atts ) : $atts;
			extract( $atts );
			$css_class    = array( 'ovic-category' );
			$css_class[]  = $atts['el_class'];
			$class_editor = isset( $atts['css'] ) ? vc_shortcode_custom_css_class( $atts['css'], ' ' ) : '';
			$css_class[]  = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_editor, 'ovic_category', $atts );

			$top_link         = vc_build_link( $atts['top_link'] );
			$top_link_title   = $top_link['title'] ? $top_link['title'] : esc_html__('Shop Now', 'marketplace');
			$top_link_url     = $top_link['url'] ? $top_link['url'] : '#';
			$top_link_target  = $top_link['target'] ? $top_link['target'] : '_blank';

			$bot_link         = vc_build_link( $atts['bot_link'] );
            $bot_link_title   = $bot_link['title'] ? $bot_link['title'] : esc_html__('View All', 'marketplace');
			$bot_link_url     = $bot_link['url'] ? $bot_link['url'] : '#';
			$bot_link_target  = $bot_link['target'] ? $bot_link['target'] : '_blank';
			ob_start(); ?>
            <div class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
                <div class="category-thumb">
					<?php if ( $atts['banner'] ) : ?>
                        <div class="thumb">
							<?php
							$image_thumb = apply_filters( 'ovic_resize_image', $atts['banner'], false, false, true, true );
							echo htmlspecialchars_decode( $image_thumb['img'] );
							?>
                        </div>
					<?php endif; ?>
                    <div class="content">
						<?php if ( $atts['title'] ) : ?>
                            <h3 class="ovic-title">
                                <span class="title"><?php echo esc_html( $atts['title'] ); ?></span>
                            </h3>
						<?php endif; ?>
                        <a href="<?php echo esc_url( $top_link_url ); ?>" <?php if ($top_link_url != '#'): ?> target="<?php echo esc_attr( $top_link_target ); ?>" <?php endif; ?> class="button-category button">
                            <?php echo esc_html( $top_link_title ); ?>
                        </a>
                    </div>
                </div>
				<?php $categories = explode( ',', $atts['taxonomy'] ); ?>
                <ul class="list-category">
					<?php foreach ( $categories as $category ) :
						$term = get_term_by( 'slug', $category, 'product_cat' );
						if ( !empty( $term ) && !is_wp_error( $term ) ) :
							$term_link = get_term_link( $term->term_id, 'product_cat' );
							?>
                            <li>
                                <a class="cat-filter" href="<?php echo esc_url( $term_link ); ?>">
									<?php echo esc_html( $term->name ); ?>
                                </a>
                            </li>
						<?php endif;
					endforeach; ?>
                </ul>
                <div class="bottom-button">
                    <a href="<?php echo esc_url( $bot_link_url ); ?>" <?php if ($bot_link_url != '#'): ?> target="<?php echo esc_attr( $bot_link_target ); ?>" <?php endif; ?> class="button-category"><?php echo esc_html( $bot_link_title ); ?>
                    </a>
                </div>
            </div>
			<?php
			wp_reset_postdata();
			$html = ob_get_clean();

			return apply_filters( 'Ovic_Shortcode_Category', $html, $atts, $content );
		}
	}

	new Ovic_Shortcode_Category();
}