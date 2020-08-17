<?php
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * Shortcode class
 * @var $this "Ovic_Products"
 * @version 1.0.1
 */
if ( !class_exists( 'Ovic_Shortcode_Products' ) ) {
	class Ovic_Shortcode_Products extends Ovic_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = 'products';

		static public function add_css_generate( $atts )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_products', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css = '';

			return apply_filters( 'Ovic_Shortcode_Products_css', $css, $atts );
		}

		public function output_html( $atts, $content = null )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_products', $atts ) : $atts;
			extract( $atts );
			$css_class   = array( 'ovic-products' );
			$css_class[] = 'style-' . $atts['product_style'];
			$css_class[] = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, '', 'ovic_products', $atts );
			/* Product Size */
			if ( $atts['product_image_size'] ) {
				if ( $atts['product_image_size'] == 'custom' ) {
					$thumb_width  = $atts['product_custom_thumb_width'];
					$thumb_height = $atts['product_custom_thumb_height'];
				} else {
					$product_image_size = explode( "x", $atts['product_image_size'] );
					$thumb_width        = $product_image_size[0];
					$thumb_height       = $product_image_size[1];
				}
				if ( $thumb_width > 0 ) {
					add_filter( 'ovic_shop_product_thumb_width', function () use ( $thumb_width ) { return $thumb_width; } );
				}
				if ( $thumb_height > 0 ) {
					add_filter( 'ovic_shop_product_thumb_height', function () use ( $thumb_height ) { return $thumb_height; } );
				}
			}
			$products             = apply_filters( 'ovic_getProducts', $atts );
			$total_product        = $products->post_count;
			$product_item_class   = array( 'product-item', $atts['target'] );
			$product_item_class[] = 'style-' . $atts['product_style'];
			$product_list_class   = array();
			$owl_settings         = '';
			if ( $atts['productsliststyle'] == 'grid' ) {
				$product_list_class[] = 'product-list-grid row auto-clear equal-container better-height ';
				$product_item_class[] = $atts['boostrap_rows_space'];
				$product_item_class[] = 'col-bg-' . $atts['boostrap_bg_items'];
				$product_item_class[] = 'col-lg-' . $atts['boostrap_lg_items'];
				$product_item_class[] = 'col-md-' . $atts['boostrap_md_items'];
				$product_item_class[] = 'col-sm-' . $atts['boostrap_sm_items'];
				$product_item_class[] = 'col-xs-' . $atts['boostrap_xs_items'];
				$product_item_class[] = 'col-ts-' . $atts['boostrap_ts_items'];
			}
			if ( $atts['productsliststyle'] == 'owl' ) {
				$css_class[] = ( function_exists( 'ovic_generate_class_nav' ) ) ? ovic_generate_class_nav( 'owl_', $atts, $total_product ) : '';
				if ( $total_product < $atts['owl_lg_items'] ) {
					$atts['owl_loop'] = 'false';
				}
				$product_list_class[] = 'product-list-owl owl-slick equal-container better-height';
				$product_list_class[] = $atts['owl_navigation_style'];
				$product_item_class[] = $atts['owl_rows_space'];
				$owl_settings         = apply_filters( 'ovic_carousel_data_attributes', 'owl_', $atts );
			}
			ob_start(); ?>
            <div class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
				<?php
				if ( $atts['title'] )
					$this->ovic_title_shortcode( $atts['title'] );
				if ( $products->have_posts() ): ?>
                    <div class="<?php echo esc_attr( implode( ' ', $product_list_class ) ); ?>" <?php echo esc_attr( $owl_settings ); ?>>
						<?php while ( $products->have_posts() ) : $products->the_post(); ?>
							<?php $product_item_class = apply_filters( 'ovic_class_item_shortcode_product', $product_item_class, $atts ); ?>
                            <div <?php wc_product_class( $product_item_class, get_the_ID() ); ?>>
								<?php do_action( 'ovic_product_template', 'style-' . $atts['product_style'] ); ?>
                            </div>
						<?php endwhile; ?>
                    </div>
				<?php else: ?>
                    <p>
                        <strong><?php esc_html_e( 'No Product', 'ovic-toolkit' ); ?></strong>
                    </p>
				<?php endif; ?>
            </div>
			<?php
			remove_all_filters( 'ovic_shop_product_thumb_width' );
			remove_all_filters( 'ovic_shop_product_thumb_height' );
			$array_filter = array(
				'item_class'    => $product_item_class,
				'contain_class' => $product_list_class,
				'carousel'      => $owl_settings,
				'query'         => $products,
			);
			wp_reset_postdata();
			$html = ob_get_clean();

			return apply_filters( 'Ovic_Shortcode_Products', $html, $atts, $content, $array_filter );
		}
	}

	new Ovic_Shortcode_Products();
}