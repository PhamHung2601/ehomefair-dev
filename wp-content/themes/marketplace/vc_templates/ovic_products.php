<?php
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * Shortcode class
 * @var $this "Ovic_Products"
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
			extract( $atts );
			$css = '';
			/* SLIDE MARGIN */
			if ( $atts['slide_margin'] == '' || $atts['slide_margin'] == 0 ) {
				$css .= '
                    .' . $atts['ovic_custom_id'] . ' .owl-slick{ margin: 0;display:inline-block;width:100%;} 
                    .' . $atts['ovic_custom_id'] . ' .slick-list .slick-slide{ margin: 0;}
                ';
			} else {
				$css .= '
                    .' . $atts['ovic_custom_id'] . ' .owl-slick{ margin:0 -' . intval( $atts['slide_margin'] ) / 2 . 'px;display:inline-block;width:calc(100% + ' . $atts['slide_margin'] . 'px)} 
                    .' . $atts['ovic_custom_id'] . ' .slick-list .slick-slide{ margin:0 ' . intval( $atts['slide_margin'] ) / 2 . 'px;}
                ';
			}

			return $css;
		}

		public function output_html( $atts, $content = null )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_products', $atts ) : $atts;
			extract( $atts );
			$css_class    = array( 'ovic-products' );
			$css_class[]  = 'style-' . $atts['product_style'];
			$css_class[]  = $atts['el_class'];
			$class_editor = isset( $atts['css'] ) ? vc_shortcode_custom_css_class( $atts['css'], ' ' ) : '';
			$css_class[]  = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_editor, 'ovic_products', $atts );
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
			if ( $atts['product_style'] == 2 ) {
				$css_class[]          = 'style-1';
				$product_item_class[] = 'style-1';
			}
			$product_list_class = array();
			$owl_settings       = '';
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
				<?php if ( $atts['title'] ) : ?>
                    <h3 class="ovic-title">
                        <span class="title"><?php echo esc_html( $atts['title'] ); ?></span>
                    </h3>
				<?php endif;
				if ( $products->have_posts() ): ?>
                    <div class="<?php echo esc_attr( implode( ' ', $product_list_class ) ); ?>" <?php echo esc_attr( $owl_settings ); ?>>
						<?php while ( $products->have_posts() ) : $products->the_post(); ?>
                            <div <?php wc_product_class( $product_item_class, get_the_ID() ); ?>>
								<?php do_action( 'ovic_product_template', 'style-' . $atts['product_style'] ); ?>
                            </div>
						<?php endwhile; ?>
                    </div>
				<?php else: ?>
                    <p>
                        <strong><?php esc_html_e( 'No Product', 'marketplace' ); ?></strong>
                    </p>
				<?php endif; ?>
            </div>
			<?php
			remove_all_filters( 'ovic_shop_product_thumb_width' );
			remove_all_filters( 'ovic_shop_product_thumb_height' );
			wp_reset_postdata();
			$html = ob_get_clean();

			return apply_filters( 'Ovic_Shortcode_Products', $html, $atts, $content );
		}
	}

	new Ovic_Shortcode_Products();
}