<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Ovic Products Slide
 *
 * Displays Products Slide widget.
 *
 * @author   Khanh
 * @category Widgets
 * @package  Ovic/Widgets
 * @version  1.0.0
 * @extends  OVIC_Widget
 */
if ( class_exists( 'OVIC_Widget' ) ) {
	if ( !class_exists( 'Products_Slide_Widget' ) ) {
		class Products_Slide_Widget extends OVIC_Widget
		{
			/**
			 * Constructor.
			 */
			public function __construct()
			{
				$array_settings           = apply_filters( 'ovic_filter_settings_widget_product_slide',
					array(
						'title'    => array(
							'type'  => 'text',
							'title' => esc_html__( 'Title', 'marketplace' ),
						),
						'target'   => array(
							'type'       => 'select',
							'options'    => array(
								'best-selling'      => esc_html__( 'Best Selling Products', 'marketplace' ),
								'top-rated'         => esc_html__( 'Top Rated Products', 'marketplace' ),
								'recent-product'    => esc_html__( 'Recent Products', 'marketplace' ),
								'featured_products' => esc_html__( 'Featured Products', 'marketplace' ),
								'on_sale'           => esc_html__( 'On Sale', 'marketplace' ),
								'on_new'            => esc_html__( 'On New', 'marketplace' ),
							),
							'attributes' => array(
								'data-depend-id' => 'target',
								'style'          => 'width: 100%;',
							),
							'title'      => esc_html__( 'Choose Target', 'marketplace' ),
						),
						'orderby'  => array(
							'type'       => 'select',
							'options'    => array(
								'date'          => esc_html__( 'Date', 'marketplace' ),
								'ID'            => esc_html__( 'ID', 'marketplace' ),
								'author'        => esc_html__( 'Author', 'marketplace' ),
								'title'         => esc_html__( 'Title', 'marketplace' ),
								'modified'      => esc_html__( 'Modified', 'marketplace' ),
								'rand'          => esc_html__( 'Random', 'marketplace' ),
								'comment_count' => esc_html__( 'Comment count', 'marketplace' ),
								'menu_order'    => esc_html__( 'Menu order', 'marketplace' ),
								'_sale_price'   => esc_html__( 'Sale price', 'marketplace' ),
							),
							'attributes' => array(
								'style' => 'width: 100%;',
							),
							'title'      => esc_html__( 'Order By', 'marketplace' ),
						),
						'order'    => array(
							'type'       => 'select',
							'options'    => array(
								'ASC'  => esc_html__( 'ASC', 'marketplace' ),
								'DESC' => esc_html__( 'DESC', 'marketplace' ),
							),
							'attributes' => array(
								'style' => 'width: 100%;',
							),
							'title'      => esc_html__( 'Order', 'marketplace' ),
						),
						'per_page' => array(
							'type'  => 'number',
							'title' => esc_html__( 'Product per page', 'marketplace' ),
						),
					)
				);
				$this->widget_cssclass    = 'widget-products-slide';
				$this->widget_description = esc_html__( 'Display the customer Products Slide.', 'marketplace' );
				$this->widget_id          = 'widget_products';
				$this->widget_name        = esc_html__( 'Ovic: Products Slide', 'marketplace' );
				$this->settings           = $array_settings;
				parent::__construct();
			}

			/**
			 * Output widget.
			 *
			 * @see WP_Widget
			 *
			 * @param array $args
			 * @param array $instance
			 */
			public function widget( $args, $instance )
			{
				$this->widget_start( $args, $instance );
				ob_start();
				$product_item_class   = array( 'product-item' );
				$product_item_class[] = 'style-1';
				$atts                 = array(
					'owl_loop'       => 'false',
					'owl_autoplay'   => 'false',
					'owl_slidespeed' => '400',
					'owl_slide_margin' => '0',
					'owl_ts_items'   => 1,
					'owl_xs_items'   => 2,
					'owl_sm_items'   => 3,
					'owl_md_items'   => 1,
					'owl_lg_items'   => 1,
					'owl_ls_items'   => 1,
				);
				$owl_settings         = apply_filters( 'ovic_carousel_data_attributes', 'owl_', $atts );
				$products             = apply_filters( 'ovic_getProducts', $instance );
				?>
                <div class="ovic-products style-1">
					<?php if ( $products->have_posts() ): ?>
                        <ul class="product-list-owl owl-slick equal-container better-height" <?php echo esc_attr( $owl_settings ); ?>>
							<?php while ( $products->have_posts() ) : $products->the_post(); ?>
                                <li <?php post_class( $product_item_class ); ?>>
									<?php get_template_part( 'woocommerce/product-styles/content-product-style', '1' ); ?>
                                </li>
							<?php endwhile; ?>
							<?php wp_reset_postdata(); ?>
                        </ul>
					<?php else: ?>
                        <p>
                            <strong><?php esc_html_e( 'No Product', 'marketplace' ); ?></strong>
                        </p>
					<?php endif; ?>
                </div>
				<?php
				echo apply_filters( 'ovic_filter_widget_product_slide', ob_get_clean(), $instance );
				$this->widget_end( $args );
			}
		}
	}
	/**
	 * Register Widgets.
	 *
	 * @since 2.3.0
	 */
	function Products_Slide_Widget()
	{
		register_widget( 'Products_Slide_Widget' );
	}

	add_action( 'widgets_init', 'Products_Slide_Widget' );
}