<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Ovic price_filter
 *
 * Displays price_filter widget.
 *
 * @author   Khanh
 * @category Widgets
 * @package  Ovic/Widgets
 * @version  1.0.0
 * @extends  OVIC_Widget
 */
if ( !class_exists( 'Ovic_Product_Price_Filter' ) ) {
	class Ovic_Product_Price_Filter extends OVIC_Widget
	{
		/**
		 * Constructor.
		 */
		public function __construct()
		{
			$array_settings           = apply_filters( 'ovic_filter_settings_ovic_price_filter',
				array(
					'title'     => array(
						'type'  => 'text',
						'title' => esc_html__( 'Title', 'ovic-toolkit' ),
					),
					'delimiter' => array(
						'type'    => 'number',
						'default' => '3',
						'title'   => esc_html__( 'Delimiter', 'ovic-toolkit' ),
					),
				)
			);
			$this->widget_cssclass    = 'ovic-product-price-filter';
			$this->widget_description = esc_html__( 'Display the customer Product Filter by Price Type List.', 'ovic-toolkit' );
			$this->widget_id          = 'ovic_product_price_filter';
			$this->widget_name        = esc_html__( 'Ovic: Products Price Filter', 'ovic-toolkit' );
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
				$query         = ( !is_woocommerce() ) ? wc()->query->get_main_query()->query_vars : '';
				$prices        = $this->ovic_get_filtered_price( $query );
				$prices_symbol = get_woocommerce_currency_symbol();
				$place_price   = array();
				$min           = floor( $prices->min_price );
				$max           = ceil( $prices->max_price );
				$percent       = ( $max - $min ) / $instance['delimiter'];
				for ( $i = 1; $i <= $instance['delimiter']; $i++ ) {
					$fmin          = ( $i - 1 ) * $percent + $min;
					$fmax          = ( $i ) * $percent + $min;
					$place_price[] = array(
						'min' => floor( $fmin ),
						'max' => ceil( $fmax ),
					);
				};
				global $wp;
				if ( '' === get_option( 'permalink_structure' ) ) {
					$form_action = remove_query_arg( array( 'page', 'paged', 'product-page' ), add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
				} else {
					$form_action = preg_replace( '%\/page/[0-9]+%', '', home_url( trailingslashit( $wp->request ) ) );
				}
				ob_start();
				if ( !empty( $place_price ) ):
					?>
                    <div class="price-filter-inner">
						<?php foreach ( $place_price as $value ) : ?>
							<?php $selected = isset( $_GET['min_price'] ) && isset( $_GET['max_price'] ) && $_GET['min_price'] == $value['min'] && $_GET['max_price'] == $value['max'] ? 'selected' : ''; ?>
                            <form class="woocommerce-price" method="get"
                                  action="<?php echo esc_attr( $form_action ); ?>">
                                <button class="price-item <?php echo esc_attr( $selected ); ?>" type="submit"
                                        class="price-item" value="">
									<?php echo esc_html( $prices_symbol ) . esc_html( $value['min'] ) . '.00 - ' . esc_html( $prices_symbol ) . esc_html( $value['max'] ) . '.00'; ?>
                                </button>
                                <input type="hidden" name="min_price" value="<?php echo esc_attr( $value['min'] ); ?>"/>
                                <input type="hidden" name="max_price" value="<?php echo esc_attr( $value['max'] ); ?>"/>
								<?php wc_query_string_form_fields( null, array( 'min_price', 'max_price' ), '', true ); ?>
                            </form>
						<?php endforeach; ?>
                    </div>
				<?php
				endif;
				echo apply_filters( 'ovic_filter_ovic_price_filter', ob_get_clean(), $instance );
				$this->widget_end( $args );
			}
		}

		function ovic_get_filtered_price( $args )
		{
			global $wpdb;
			$tax_query  = isset( $args['tax_query'] ) ? $args['tax_query'] : array();
			$meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();
			if ( !is_post_type_archive( 'product' ) && !empty( $args['taxonomy'] ) && !empty( $args['term'] ) ) {
				$tax_query[] = array(
					'taxonomy' => $args['taxonomy'],
					'terms'    => array( $args['term'] ),
					'field'    => 'slug',
				);
			}
			foreach ( $meta_query + $tax_query as $key => $query ) {
				if ( !empty( $query['price_filter'] ) || !empty( $query['rating_filter'] ) ) {
					unset( $meta_query[$key] );
				}
			}
			$meta_query     = new WP_Meta_Query( $meta_query );
			$tax_query      = new WP_Tax_Query( $tax_query );
			$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
			$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );
			$sql            = "SELECT min( FLOOR( price_meta.meta_value ) ) as min_price, max( CEILING( price_meta.meta_value ) ) as max_price FROM {$wpdb->posts} ";
			$sql            .= " LEFT JOIN {$wpdb->postmeta} as price_meta ON {$wpdb->posts}.ID = price_meta.post_id " . $tax_query_sql['join'] . $meta_query_sql['join'];
			$sql            .= " 	WHERE {$wpdb->posts}.post_type IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_post_type', array( 'product' ) ) ) ) . "')
					AND {$wpdb->posts}.post_status = 'publish'
					AND price_meta.meta_key IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_meta_keys', array( '_price' ) ) ) ) . "')
					AND price_meta.meta_value > '' ";
			$sql            .= $tax_query_sql['where'] . $meta_query_sql['where'];

			return $wpdb->get_row( $sql );
		}
	}
}
/**
 * Register Widgets.
 *
 * @since 2.3.0
 */
function Ovic_Product_Price_Filter()
{
	register_widget( 'Ovic_Product_Price_Filter' );
}

add_action( 'widgets_init', 'Ovic_Product_Price_Filter' );