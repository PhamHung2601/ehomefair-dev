<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Ovic catalog_ordering
 *
 * Displays catalog_ordering widget.
 *
 * @author   Khanh
 * @category Widgets
 * @package  Ovic/Widgets
 * @version  1.0.0
 * @extends  OVIC_Widget
 */
if ( !class_exists( 'Ovic_Catalog_Ordering' ) ) {
	class Ovic_Catalog_Ordering extends OVIC_Widget
	{
		/**
		 * Constructor.
		 */
		public function __construct()
		{
			$array_settings           = apply_filters( 'ovic_filter_settings_ovic_catalog_ordering',
				array(
					'title' => array(
						'type'  => 'text',
						'title' => esc_html__( 'Title', 'ovic-toolkit' ),
					),
				)
			);
			$this->widget_cssclass    = 'ovic-catalog-ordering';
			$this->widget_description = esc_html__( 'Display the customer Catalog Ordering Type List.', 'ovic-toolkit' );
			$this->widget_id          = 'ovic_catalog_ordering';
			$this->widget_name        = esc_html__( 'Ovic: Catalog Ordering', 'ovic-toolkit' );
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
			if ( !is_product() && is_woocommerce() ) {
				$this->widget_start( $args, $instance );
				$catalog_orderby_options = array(
					'menu_order' => esc_html__( 'Default', 'ovic-toolkit' ),
					'popularity' => esc_html__( 'Popularity', 'ovic-toolkit' ),
					'rating'     => esc_html__( 'Average Rating', 'ovic-toolkit' ),
					'date'       => esc_html__( 'Newness', 'ovic-toolkit' ),
					'price'      => esc_html__( 'Price: low to high', 'ovic-toolkit' ),
					'price-desc' => esc_html__( 'Price: high to low', 'ovic-toolkit' ),
				);
				global $wp;
				if ( '' === get_option( 'permalink_structure' ) ) {
					$form_action = remove_query_arg( array( 'page', 'paged', 'product-page' ), add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
				} else {
					$form_action = preg_replace( '%\/page/[0-9]+%', '', home_url( trailingslashit( $wp->request ) ) );
				}
				ob_start();
				?>
                <div class="catalog-ordering-inner">
                    <form class="woocommerce-ordering" method="get" action="<?php echo esc_attr( $form_action ); ?>">
						<?php foreach ( $catalog_orderby_options as $id => $name ) : ?>
							<?php $selected = isset( $_GET['orderby'] ) && $_GET['orderby'] == $id ? 'selected' : ''; ?>
                            <button type="submit" class="ordering-item <?php echo esc_attr( $selected ); ?>"
                                    value="<?php echo esc_attr( $id ); ?>"
                                    name="orderby"><?php echo esc_html( $name ); ?></button>
						<?php endforeach; ?>
						<?php wc_query_string_form_fields( null, array( 'orderby', 'submit', 'paged', 'product-page' ) ); ?>
                    </form>
                </div>
				<?php
				echo apply_filters( 'ovic_filter_ovic_catalog_ordering', ob_get_clean(), $instance );
				$this->widget_end( $args );
			}
		}
	}
}
/**
 * Register Widgets.
 *
 * @since 2.3.0
 */
function Ovic_Catalog_Ordering()
{
	register_widget( 'Ovic_Catalog_Ordering' );
}

add_action( 'widgets_init', 'Ovic_Catalog_Ordering' );