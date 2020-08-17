<?php
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * Shortcode class
 * @var $this "Ocolus_Products"
 * @version 1.0.0
 */
if ( !class_exists( 'Ovic_Shortcode_Products_2' ) ) {
	class Ovic_Shortcode_Products_2 extends Ovic_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = 'products_2';

		static public function add_css_generate( $atts )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_products_2', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css = '';

			return apply_filters( 'Ovic_Shortcode_Products_2_css', $css, $atts );
		}

		public function output_html( $atts, $content = null )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_products_2', $atts ) : $atts;
			extract( $atts );
			$css_class   = array( 'ovic-products' );
			$css_class[] = 'style-' . $atts['product_style'];
			$css_class[] = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, '', 'ovic_products_2', $atts );
			/* START */
			$html = '';
			/* Product Size */
			if ( $atts['product_image_size'] ) {
				if ( $atts['product_image_size'] == 'custom' ) {
					$thumb_width  = $atts['custom_thumb_width'];
					$thumb_height = $atts['custom_thumb_height'];
				} else {
					$product_image_size = explode( "x", $atts['product_image_size'] );
					$thumb_width        = $product_image_size[0];
					$thumb_height       = $product_image_size[1];
				}
				if ( $thumb_width > 0 ) {
					add_filter( 'ovic_shop_product_thumb_width', create_function( '', 'return ' . $thumb_width . ';' ) );
				}
				if ( $thumb_height > 0 ) {
					add_filter( 'ovic_shop_product_thumb_height', create_function( '', 'return ' . $thumb_height . ';' ) );
				}
			}
			$product_item_class = array( 'product-item', $atts['target'] );
			$product_list_class = array();
			$owl_settings       = '';
			if ( $atts['productsliststyle'] == 'grid' ) {
				$product_list_class[] = 'product-list-grid row auto-clear equal-container better-height';
				$product_item_class[] = Ovic_Field_Advandce::generate_grid_attr( $atts['bootstrap'] );
			}
			if ( $atts['productsliststyle'] == 'owl' ) {
				$product_list_class[] = 'product-list-owl owl-slick equal-container better-height';
				$product_list_class[] = $atts['owl_navigation_style'];
				$product_item_class[] = $atts['owl_rows_space'];
				$owl_settings         = Ovic_Field_Advandce::generate_slide_attr( $atts['carousel'] );
			}
			$style_product      = 'style-' . $atts['product_style'];
			$product_list_class = implode( ' ', $product_list_class );
			$data_attr          = "<ul class='{$product_list_class}' {$owl_settings}>";
			add_filter( 'woocommerce_product_loop_start',
				function () use ( $data_attr ) {
					return $data_attr;
				}
			);
			add_filter( 'ovic_class_content_product',
				function ( $classes ) use ( $product_item_class ) {
					$classes = $product_item_class;

					return $classes;
				}
			);
			add_filter( 'ovic_style_content_product',
				function () use ( $style_product ) {
					return $style_product;
				}
			);
			if ( $atts['filter'] && $atts['attribute'] != '' ) {
				$atts['terms'] = $atts['filter'];
			}
			$atts_shortcodes = array(
				'limit'     => '-1',      // Results limit.
				'columns'   => '',        // Number of columns.
				'orderby'   => 'title',   // menu_order, title, date, rand, price, popularity, rating, or id.
				'order'     => 'ASC',     // ASC or DESC.
				'ids'       => '',        // Comma separated IDs.
				'skus'      => '',        // Comma separated SKUs.
				'category'  => '',        // Comma separated category slugs or ids.
				'attribute' => '',        // Single attribute slug.
				'terms'     => '',        // Comma separated term slugs or ids.
				'class'     => '',        // HTML class.
			);
			if ( $atts['pagination'] == 1 )
				$atts_shortcodes['paginate'] = true;
			foreach ( $atts_shortcodes as $key => $shortcode ) {
				if ( isset( $atts[$key] ) && $atts[$key] != '' )
					$atts_shortcodes[$key] = $atts[$key];
			}
			$html .= '<div class="' . esc_attr( implode( ' ', $css_class ) ) . '">';
			$html .= WC_Shortcodes::$atts['target']( $atts_shortcodes );
			$html .= '</div>';
			remove_all_filters( 'ovic_style_content_product' );
			remove_all_filters( 'ovic_class_content_product' );
			remove_all_filters( 'woocommerce_product_loop_start' );

			return apply_filters( 'Ovic_Shortcode_Products_2', $html, $atts, $content );
		}
	}

	new Ovic_Shortcode_Products_2();
}