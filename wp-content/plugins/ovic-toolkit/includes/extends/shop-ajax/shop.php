<?php
/**
 * Ovic Shop Ajax Setup
 *
 * @author   KHANH
 * @category API
 * @package  Ovic_Shop_Ajax
 * @since    1.0.0
 */
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( !class_exists( 'Ovic_Shop_Ajax' ) ) {
	class Ovic_Shop_Ajax
	{
		/**
		 * @var Ovic_Shop_Ajax The one true Ovic_Shop_Ajax
		 */
		private static $instance;

		public static function instance()
		{
			if ( !isset( self::$instance ) && !( self::$instance instanceof Ovic_Shop_Ajax ) ) {
				self::$instance = new Ovic_Shop_Ajax;
				add_action( 'wp_enqueue_scripts', array( self::$instance, 'scripts' ) );
			}

			return self::$instance;
		}

		function scripts()
		{
			if ( apply_filters( 'ovic_get_option', 'ovic_woo_enable_ajax' ) == 1 && class_exists( 'WooCommerce' ) && !is_product() && is_woocommerce() ) {
				$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
				$classes = array(
					'ul.products',
					'.ovic-responsive-filter',
					'.widget_product_categories',
					'.widget_product_tag_cloud',
					'.widget_layered_nav',
					'.grid-view-mode',
					'.widget_price_filter',
					'.ovic-product-price-filter',
					'.woocommerce-result-count',
					'.woocommerce-products-header__title',
					'.woocommerce-pagination',
					'.ovic-catalog-ordering',
					'.widget-ovic-price-filter',
					'.widget-ovic-catalog-ordering',
				);
				$class   = apply_filters( 'ovic_get_option', 'ovic_woo_ajax_response', array() );
				if ( !empty( $class ) ) {
					$classes = array_merge( $classes, array_column( $class, 'class' ) );
				}
				$script = array( WC()->plugin_url() . '/assets/js/frontend/price-slider' . $suffix . '.js' );
				wp_enqueue_style( 'ovic-shop-ajax', plugin_dir_url( __FILE__ ) . 'shop.css', array(), '1.0' );
				wp_enqueue_script( 'ovic-shop-ajax', plugin_dir_url( __FILE__ ) . 'shop.js', array(), '1.0', true );
				wp_localize_script( 'ovic-shop-ajax', 'ovic_shop_ajax', array(
						'response_class'  => !empty( $classes ) ? array_unique( $classes ) : array(),
						'response_script' => !empty( $script ) ? array_values( $script ) : array(),
						'woo_shop_link'   => get_permalink( wc_get_page_id( 'shop' ) ),
					)
				);
			}
		}
	}
}
if ( !function_exists( 'ovic_instance_shop_ajax' ) ) {
	function ovic_instance_shop_ajax()
	{
		/**
		 * SHOP AJAX FILTER
		 */
		add_action( 'woocommerce_before_main_content', 'ovic_woocommerce_filter', 30 );
		if ( !function_exists( 'ovic_woocommerce_filter' ) ) {
			function ovic_woocommerce_filter()
			{
				$enable = apply_filters( 'ovic_get_option', 'ovic_woo_enable_ajax' );
				if ( $enable == 1 && !is_product() && is_woocommerce() ): ?>
                    <div class="ovic-shop-filter">
                        <div class="inner-content">
                            <span><?php echo esc_html__( 'Fitered by: ', 'ovic-toolkit' ); ?></span>
                            <div class="ovic-responsive-filter">
								<?php the_widget( 'WC_Widget_Layered_Nav_Filters', 'title=' ); ?>
                            </div>
                            <a href="<?php echo get_permalink( wc_get_page_id( 'shop' ) ); ?>"
                               class="filter-item reset">
                                <i class="fa fa-trash-o"></i>
                            </a>
                        </div>
                    </div>
				<?php
				endif;
			}
		}

		return Ovic_Shop_Ajax::instance();
	}

	ovic_instance_shop_ajax();
}