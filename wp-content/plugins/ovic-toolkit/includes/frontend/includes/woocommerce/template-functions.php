<?php
/**
 * WooCommerce Template
 *
 * Functions for the templating system.
 *
 * @author   Khanh
 * @category Core
 * @package  Ovic_Woo_Functions
 * @version  1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! function_exists( 'ovic_action_after_setup_theme' ) ) {
	function ovic_action_after_setup_theme()
	{
		/* QUICK VIEW */
		if ( class_exists( 'YITH_WCQV_Frontend' ) ) {
			// Class frontend
			$enable           = get_option( 'yith-wcqv-enable' ) == 'yes' ? true : false;
			$enable_on_mobile = get_option( 'yith-wcqv-enable-mobile' ) == 'yes' ? true : false;
			// Class frontend
			if ( ( ! wp_is_mobile() && $enable ) || ( wp_is_mobile() && $enable_on_mobile && $enable ) ) {
				remove_action( 'woocommerce_after_shop_loop_item', array(
					YITH_WCQV_Frontend::get_instance(),
					'yith_add_quick_view_button'
				), 15 );
				add_action( 'ovic_function_shop_loop_item_quickview', array(
					YITH_WCQV_Frontend::get_instance(),
					'yith_add_quick_view_button'
				), 5 );
			}
		}
		/* WISH LIST */
		if ( defined( 'YITH_WCWL' ) ) {
			if ( ! function_exists( 'ovic_function_shop_loop_item_wishlist' ) ) {
				function ovic_function_shop_loop_item_wishlist()
				{
					echo do_shortcode( "[yith_wcwl_add_to_wishlist]" );
				}
			}
			add_action( 'ovic_function_shop_loop_item_wishlist', 'ovic_function_shop_loop_item_wishlist', 1 );
		}
		/* COMPARE */
		if ( class_exists( 'YITH_Woocompare' ) && get_option( 'yith_woocompare_compare_button_in_products_list' ) == 'yes' ) {
			global $yith_woocompare;
			$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
			if ( $yith_woocompare->is_frontend() || $is_ajax ) {
				if ( $is_ajax ) {
					if ( ! class_exists( 'YITH_Woocompare_Frontend' ) && file_exists( YITH_WOOCOMPARE_DIR . 'includes/class.yith-woocompare-frontend.php' ) ) {
						require_once YITH_WOOCOMPARE_DIR . 'includes/class.yith-woocompare-frontend.php';
					}
					$yith_woocompare->obj = new YITH_Woocompare_Frontend();
				}
				/* Remove button */
				remove_action( 'woocommerce_after_shop_loop_item', array(
					$yith_woocompare->obj,
					'add_compare_link'
				), 20 );
				/* Add compare button */
				if ( ! function_exists( 'ovic_wc_loop_product_compare_btn' ) ) {
					function ovic_wc_loop_product_compare_btn()
					{
						if ( shortcode_exists( 'yith_compare_button' ) ) {
							echo do_shortcode( '[yith_compare_button product_id="' . get_the_ID() . '"]' );
						} else {
							if ( class_exists( 'YITH_Woocompare_Frontend' ) ) {
								echo do_shortcode( '[yith_compare_button product_id="' . get_the_ID() . '"]' );
							}
						}
					}
				}
				add_action( 'ovic_function_shop_loop_item_compare', 'ovic_wc_loop_product_compare_btn', 1 );
			}
		}
	}
}
if ( ! function_exists( 'ovic_custom_available_variation' ) ) {
	function ovic_custom_available_variation( $data, $product, $variation )
	{
		if ( has_filter( 'ovic_shop_product_thumb_width' ) && has_filter( 'ovic_shop_product_thumb_height' ) ) {
			// GET SIZE IMAGE SETTING
			$width  = 300;
			$height = 300;
			$size   = wc_get_image_size( 'shop_catalog' );
			if ( $size ) {
				$width  = $size['width'];
				$height = $size['height'];
			}
			$width                      = apply_filters( 'ovic_shop_product_thumb_width', $width );
			$height                     = apply_filters( 'ovic_shop_product_thumb_height', $height );
			$image_variable             = apply_filters( 'ovic_resize_image', $data['image_id'], $width, $height, true, false );
			$data['image']['src']       = $image_variable['url'];
			$data['image']['url']       = $image_variable['url'];
			$data['image']['full_src']  = $image_variable['url'];
			$data['image']['thumb_src'] = $image_variable['url'];
			$data['image']['srcset']    = $image_variable['url'];
			$data['image']['src_w']     = $width;
			$data['image']['src_h']     = $height;
		}

		return $data;
	}

	add_filter( 'woocommerce_available_variation', 'ovic_custom_available_variation', 10, 3 );
}
if ( ! function_exists( 'ovic_action_attributes' ) ) {
	function ovic_action_attributes()
	{
		global $product;
		if ( $product->get_type() == 'variable' ) : ?>
			<?php
			if ( ! wp_script_is( 'wc-add-to-cart-variation', 'enqueued' ) ) {
				wp_enqueue_script( 'wc-add-to-cart-variation' );
			}
			$attributes           = $product->get_variation_attributes();
			$get_variations       = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
			$available_variations = $get_variations ? $product->get_available_variations() : false;
			$attribute_keys       = array_keys( $attributes );
			if ( ! empty( $attributes ) ):?>
                <form class="variations_form cart" method="post" enctype='multipart/form-data'
                      data-product_id="<?php echo absint( $product->get_id() ); ?>"
                      data-product_variations="<?php echo htmlspecialchars( wp_json_encode( $available_variations ) ); ?>">
                    <table class="variations">
                        <tbody>
						<?php foreach ( $attributes as $attribute_name => $options ) : ?>
                            <tr>
                                <td class="label">
                                    <label><?php echo wc_attribute_label( $attribute_name ); ?></label>
                                </td>
                                <td class="value">
									<?php
									wc_dropdown_variation_attribute_options(
										array(
											'options'   => $options,
											'attribute' => $attribute_name,
											'product'   => $product,
										)
									);
									echo end( $attribute_keys ) === $attribute_name ? wp_kses_post( apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'ovic-toolkit' ) . '</a>' ) ) : '';
									?>
                                </td>
                            </tr>
						<?php endforeach; ?>
                        </tbody>
                    </table>
                </form>
			<?php
			endif;
		endif;
	}
}
if ( ! function_exists( 'ovic_wc_get_template_part' ) ) {
	function ovic_wc_get_template_part( $template, $slug, $name )
	{
		if ( $slug == 'content' && $name == 'product' ) {
			if ( is_file( get_template_directory() . '/woocommerce/content-product.php' ) ) {
				$template = apply_filters( 'ovic_woocommerce_content_product', get_template_directory() . '/woocommerce/content-product.php' );
			} else {
				$template = apply_filters( 'ovic_woocommerce_content_product', plugin_dir_path( __FILE__ ) . 'content-product.php' );
			}
		}

		return $template;
	}
}
if ( ! function_exists( 'ovic_woocommerce_breadcrumb' ) ) {
	function ovic_woocommerce_breadcrumb()
	{
		$args = array(
			'delimiter'   => '',
			'wrap_before' => '<div class="col-sm-12"><ul class="woocommerce-breadcrumb breadcrumb">',
			'wrap_after'  => '</ul></div>',
			'before'      => '<li>',
			'after'       => '</li>',
		);
		woocommerce_breadcrumb( $args );
	}
}
if ( ! function_exists( 'ovic_default_products' ) ) {
	function ovic_default_products()
	{
		/**
		 * Hook: woocommerce_before_shop_loop_item.
		 *
		 * @hooked woocommerce_template_loop_product_link_open - 10
		 */
		do_action( 'woocommerce_before_shop_loop_item' );
		/**
		 * Hook: woocommerce_before_shop_loop_item_title.
		 *
		 * @hooked woocommerce_show_product_loop_sale_flash - 10
		 * @hooked woocommerce_template_loop_product_thumbnail - 10
		 */
		do_action( 'woocommerce_before_shop_loop_item_title' );
		/**
		 * Hook: woocommerce_shop_loop_item_title.
		 *
		 * @hooked woocommerce_template_loop_product_title - 10
		 */
		do_action( 'woocommerce_shop_loop_item_title' );
		/**
		 * Hook: woocommerce_after_shop_loop_item_title.
		 *
		 * @hooked woocommerce_template_loop_rating - 5
		 * @hooked woocommerce_template_loop_price - 10
		 */
		do_action( 'woocommerce_after_shop_loop_item_title' );
		/**
		 * Hook: woocommerce_after_shop_loop_item.
		 *
		 * @hooked woocommerce_template_loop_product_link_close - 5
		 * @hooked woocommerce_template_loop_add_to_cart - 10
		 */
		do_action( 'woocommerce_after_shop_loop_item' );
	}
}
if ( ! function_exists( 'ovic_before_woocommerce_content' ) ) {
	function ovic_before_woocommerce_content()
	{
		$class = array( 'row auto-clear equal-container better-height ovic-products' );
		$class = apply_filters( 'ovic_before_woocommerce_content', $class );
		echo '<div class="' . implode( ' ', $class ) . '">';
	}
}
if ( ! function_exists( 'ovic_after_woocommerce_content' ) ) {
	function ovic_after_woocommerce_content()
	{
		echo '</div>';
	}
}
if ( ! function_exists( 'ovic_woocommerce_options_sidebar' ) ) {
	function ovic_woocommerce_options_sidebar()
	{
		$shop_layout  = apply_filters( 'ovic_get_option', 'ovic_sidebar_shop_layout', 'left' );
		$shop_sidebar = apply_filters( 'ovic_get_option', 'ovic_shop_used_sidebar', 'shop-widget-area' );
		if ( is_product() ) {
			$shop_layout  = apply_filters( 'ovic_get_option', 'ovic_sidebar_single_product_layout', 'left' );
			$shop_sidebar = apply_filters( 'ovic_get_option', 'ovic_single_product_used_sidebar', 'product-widget-area' );
		}
		if ( ! is_active_sidebar( $shop_sidebar ) || function_exists( 'dokan_is_store_page' ) && dokan_is_store_page() ) {
			$shop_layout = 'full';
		}

		return array(
			'layout'  => $shop_layout,
			'sidebar' => $shop_sidebar,
		);
	}
}
if ( ! function_exists( 'ovic_woocommerce_before_loop_content' ) ) {
	function ovic_woocommerce_before_loop_content()
	{
		/*Shop layout*/
		$option_layout        = ovic_woocommerce_options_sidebar();
		$shop_layout          = $option_layout['layout'];
		$main_content_class   = array();
		$main_content_class[] = 'main-content';
		if ( $shop_layout == 'full' ) {
			$main_content_class[] = 'col-sm-12';
		} else {
			$main_content_class[] = 'col-lg-9 col-md-8 has-sidebar';
		}
		$main_content_class = apply_filters( 'ovic_class_archive_content', $main_content_class, $shop_layout );
		echo '<div class="' . esc_attr( implode( ' ', $main_content_class ) ) . '">';
	}
}
if ( ! function_exists( 'ovic_woocommerce_after_loop_content' ) ) {
	function ovic_woocommerce_after_loop_content()
	{
		echo '</div>';
	}
}
if ( ! function_exists( 'ovic_woocommerce_before_main_content' ) ) {
	function ovic_woocommerce_before_main_content()
	{
		/*Main container class*/
		$option_layout        = ovic_woocommerce_options_sidebar();
		$html                 = '';
		$main_container_class = array();
		if ( is_product() ) {
			$thumbnail_layout       = apply_filters( 'ovic_get_option', 'ovic_single_product_thumbnail', 'vertical' );
			$main_container_class[] = 'single-thumb-' . $thumbnail_layout;
		}
		$shop_layout            = $option_layout['layout'];
		$main_container_class[] = 'main-container shop-page';
		if ( $shop_layout == 'full' ) {
			$main_container_class[] = 'no-sidebar';
		} else {
			$main_container_class[] = $shop_layout . '-sidebar';
		}
		$main_container_class = apply_filters( 'ovic_class_before_main_content_product', $main_container_class, $shop_layout );
		/* CONTENT */
		$html .= '<div class="' . esc_attr( implode( ' ', $main_container_class ) ) . '">';
		$html .= '<div class="container">';
		$html .= '<div class="row">';
		echo apply_filters( 'ovic_woocommerce_before_main_content', $html );
	}
}
if ( ! function_exists( 'ovic_woocommerce_after_main_content' ) ) {
	function ovic_woocommerce_after_main_content()
	{
		$html = '</div></div></div>';
		echo apply_filters( 'ovic_woocommerce_after_main_content', $html );
	}
}
if ( ! function_exists( 'ovic_woocommerce_sidebar' ) ) {
	function ovic_woocommerce_sidebar()
	{
		$sidebar_class   = array();
		$option_layout   = ovic_woocommerce_options_sidebar();
		$shop_layout     = $option_layout['layout'];
		$shop_sidebar    = $option_layout['sidebar'];
		$sidebar_class[] = 'sidebar';
		if ( $shop_layout != 'full' ) {
			$sidebar_class[] = 'col-lg-3 col-md-4';
		}
		$sidebar_class = apply_filters( 'ovic_class_sidebar_content_product', $sidebar_class, $shop_layout, $shop_sidebar );
		if ( $shop_layout != "full" ): ?>
            <div class="<?php echo esc_attr( implode( ' ', $sidebar_class ) ); ?>">
				<?php if ( is_active_sidebar( $shop_sidebar ) ) : ?>
                    <div id="widget-area" class="widget-area shop-sidebar">
						<?php dynamic_sidebar( $shop_sidebar ); ?>
                    </div><!-- .widget-area -->
				<?php endif; ?>
            </div>
		<?php endif;
	}
}
if ( ! function_exists( 'ovic_product_get_rating_html' ) ) {
	function ovic_product_get_rating_html( $html, $rating, $count )
	{
		global $product;
		$rating_count = isset( $product ) ? $product->get_rating_count() : 0;
		if ( $rating_count > 0 ) {
			$html = '<div class="rating-wapper"><div class="star-rating">';
			$html .= wc_get_star_rating_html( $rating, $count );
			$html .= '</div>';
			$html .= '<span class="review">( ' . $rating_count . ' ' . esc_html__( 'review', 'ovic-toolkit' ) . ' )</span>';
			$html .= '</div>';
		} else {
			$html = '';
		}

		return $html;
	}
}
if ( ! function_exists( 'ovic_before_shop_control' ) ) {
	function ovic_before_shop_control()
	{
		ob_start(); ?>
        <div class="shop-control shop-before-control">
			<?php
			/**
			 * ovic_control_before_content hook.
			 *
			 * @hooked woocommerce_catalog_ordering - 10
			 * @hooked ovic_product_per_page_tmp - 20
			 * @hooked ovic_shop_display_mode_tmp - 30
			 */
			do_action( 'ovic_control_before_content' ); ?>
        </div>
		<?php
		$html = ob_get_clean();
		echo apply_filters( 'ovic_before_shop_control_html', $html );
	}
}
if ( ! function_exists( 'ovic_after_shop_control' ) ) {
	function ovic_after_shop_control()
	{
		ob_start(); ?>
        <div class="shop-control shop-after-control">
			<?php
			/**
			 * ovic_control_after_content hook.
			 *
			 * @hooked woocommerce_result_count - 10
			 * @hooked ovic_custom_pagination - 20
			 */
			do_action( 'ovic_control_after_content' ); ?>
        </div>
		<?php
		$html = ob_get_clean();
		echo apply_filters( 'ovic_after_shop_control_html', $html );
	}
}
if ( ! function_exists( 'ovic_shop_display_mode_tmp' ) ) {
	function ovic_shop_display_mode_tmp()
	{
		global $wp;
		if ( '' === get_option( 'permalink_structure' ) ) {
			$form_action = remove_query_arg( array(
				'page',
				'paged',
				'product-page'
			), add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
		} else {
			$form_action = preg_replace( '%\/page/[0-9]+%', '', home_url( trailingslashit( $wp->request ) ) );
		}
		$shop_display_mode = apply_filters( 'ovic_get_option', 'ovic_shop_list_style', 'grid' );
		?>
        <div class="grid-view-mode">
            <form method="get" action="<?php echo esc_url( $form_action ); ?>">
				<?php ob_start(); ?>
                <button type="submit"
                        class="modes-mode mode-grid display-mode <?php if ( $shop_display_mode == 'grid' ): ?>active<?php endif; ?>"
                        value="grid"
                        name="ovic_shop_list_style">
                        <span class="button-inner">
                            <?php echo esc_html__( 'Grid', 'ovic-toolkit' ); ?>
                        </span>
                </button>
                <button type="submit"
                        class="modes-mode mode-list display-mode <?php if ( $shop_display_mode == 'list' ): ?>active<?php endif; ?>"
                        value="list"
                        name="ovic_shop_list_style">
                        <span class="button-inner">
                            <?php echo esc_html__( 'List', 'ovic-toolkit' ); ?>
                        </span>
                </button>
				<?php echo apply_filters( 'ovic_woocommerce_display_mode_button', ob_get_clean(), $shop_display_mode ); ?>
				<?php wc_query_string_form_fields( null, array(
					'ovic_shop_list_style',
					'submit',
					'paged',
					'product-page'
				) ); ?>
            </form>
        </div>
		<?php
	}
}
if ( ! function_exists( 'ovic_loop_shop_per_page' ) ) {
	function ovic_loop_shop_per_page()
	{
		$ovic_woo_products_perpage = apply_filters( 'ovic_get_option', 'ovic_product_per_page', '12' );

		return $ovic_woo_products_perpage;
	}
}
if ( ! function_exists( 'ovic_woof_products_query' ) ) {
	function ovic_woof_products_query( $wr )
	{
		$ovic_woo_products_perpage = apply_filters( 'ovic_get_option', 'ovic_product_per_page', '12' );
		$wr['posts_per_page']      = $ovic_woo_products_perpage;

		return $wr;
	}
}
if ( ! function_exists( 'ovic_product_per_page_tmp' ) ) {
	function ovic_product_per_page_tmp()
	{
		global $wp;
		if ( '' === get_option( 'permalink_structure' ) ) {
			$form_action = remove_query_arg( array(
				'page',
				'paged',
				'product-page'
			), add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
		} else {
			$form_action = preg_replace( '%\/page/[0-9]+%', '', home_url( trailingslashit( $wp->request ) ) );
		}
		$total   = wc_get_loop_prop( 'total' );
		$perpage = apply_filters( 'ovic_get_option', 'ovic_product_per_page', '12' );
		?>
        <form class="per-page-form" method="get" action="<?php echo esc_attr( $form_action ); ?>">
			<?php ob_start(); ?>
            <label>
                <select name="ovic_product_per_page" class="option-perpage">
                    <option value="<?php echo esc_attr( $perpage ); ?>" <?php echo esc_attr( 'selected' ); ?>>
						<?php echo sprintf( '%s %s',
							esc_html__( 'Show', 'ovic-toolkit' ),
							zeroise( $perpage, 2 )
						); ?>
                    </option>
					<?php if ( $perpage != 5 ) : ?>
                        <option value="5">
							<?php echo esc_html__( 'Show 05', 'ovic-toolkit' ); ?>
                        </option>
					<?php endif; ?>
					<?php if ( $perpage != 10 ) : ?>
                        <option value="10">
							<?php echo esc_html__( 'Show 10', 'ovic-toolkit' ); ?>
                        </option>
					<?php endif; ?>
					<?php if ( $perpage != 12 ) : ?>
                        <option value="12">
							<?php echo esc_html__( 'Show 12', 'ovic-toolkit' ); ?>
                        </option>
					<?php endif; ?>
					<?php if ( $perpage != 15 ) : ?>
                        <option value="15">
							<?php echo esc_html__( 'Show 15', 'ovic-toolkit' ); ?>
                        </option>
					<?php endif; ?>
                    <option value="<?php echo esc_attr( $total ); ?>">
						<?php echo esc_html__( 'Show All', 'ovic-toolkit' ); ?>
                    </option>
                </select>
            </label>
			<?php echo apply_filters( 'ovic_woocommerce_per_page_button', ob_get_clean(), $perpage ); ?>
			<?php wc_query_string_form_fields( null, array(
				'ovic_product_per_page',
				'submit',
				'paged',
				'product-page'
			) ); ?>
        </form>
		<?php
	}
}
if ( ! function_exists( 'ovic_custom_pagination' ) ) {
	function ovic_custom_pagination()
	{
		global $wp_query;
		ob_start();
		if ( $wp_query->max_num_pages > 1 ) {
			?>
            <nav class="woocommerce-pagination pagination">
				<?php
				echo paginate_links( apply_filters( 'woocommerce_pagination_args', array(
					'base'      => esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) ),
					'format'    => '',
					'add_args'  => false,
					'current'   => max( 1, get_query_var( 'paged' ) ),
					'total'     => $wp_query->max_num_pages,
					'prev_text' => esc_html__( 'Previous', 'ovic-toolkit' ),
					'next_text' => esc_html__( 'Next', 'ovic-toolkit' ),
					'type'      => 'plain',
					'end_size'  => 3,
					'mid_size'  => 3,
				) ) );
				?>
            </nav>
			<?php
		}
		echo apply_filters( 'ovic_custom_pagination', ob_get_clean() );
	}
}
if ( ! function_exists( 'ovic_related_title_product' ) ) {
	function ovic_related_title_product( $prefix )
	{
		if ( $prefix == 'ovic_woo_crosssell' ) {
			$default_text = esc_html__( 'Cross Sell Products', 'ovic-toolkit' );
		} elseif ( $prefix == 'ovic_woo_related' ) {
			$default_text = esc_html__( 'Related Products', 'ovic-toolkit' );
		} else {
			$default_text = esc_html__( 'Upsell Products', 'ovic-toolkit' );
		}
		$title = apply_filters( 'ovic_get_option', $prefix . '_products_title', $default_text );
		ob_start();
		if ( $title ):
			?>
            <h2 class="product-grid-title">
                <span><?php echo esc_html( $title ); ?></span>
            </h2>
		<?php
		endif;
		$html = ob_get_clean();
		echo apply_filters( 'ovic_filter_related_title_product', $html, $prefix );
	}
}
if ( ! function_exists( 'ovic_carousel_products' ) ) {
	function ovic_carousel_products( $prefix, $product_args )
	{
		$enable_product = apply_filters( 'ovic_get_option', $prefix . '_enable', 'enable' );
		$product_style  = apply_filters( 'ovic_get_option', 'ovic_shop_product_style', 1 );
		if ( $enable_product == 'disable' ) {
			return;
		}
		$ovic_woo_product_style = apply_filters( 'ovic_single_product_style', $product_style );
		$template_style         = 'style-' . $ovic_woo_product_style;
		$classes                = array( 'product-item' );
		$classes_contain        = array( 'products product-grid' );
		$classes[]              = $template_style;
		$classes[]              = apply_filters( 'ovic_single_product_class', '' );
		$woo_ls_items           = apply_filters( 'ovic_get_option', $prefix . '_ls_items', 3 );
		$woo_lg_items           = apply_filters( 'ovic_get_option', $prefix . '_lg_items', 3 );
		$woo_md_items           = apply_filters( 'ovic_get_option', $prefix . '_md_items', 3 );
		$woo_sm_items           = apply_filters( 'ovic_get_option', $prefix . '_sm_items', 2 );
		$woo_xs_items           = apply_filters( 'ovic_get_option', $prefix . '_xs_items', 1 );
		$woo_ts_items           = apply_filters( 'ovic_get_option', $prefix . '_ts_items', 1 );
		$atts                   = array(
			'owl_loop'              => 'false',
			'owl_ts_items'          => $woo_ts_items,
			'owl_xs_items'          => $woo_xs_items,
			'owl_sm_items'          => $woo_sm_items,
			'owl_md_items'          => $woo_md_items,
			'owl_lg_items'          => $woo_lg_items,
			'owl_ls_items'          => $woo_ls_items,
			'owl_responsive_margin' => 480,
			'owl_slide_margin'      => 30,
		);
		$classes_contain[]      = $prefix . '-product';
		$classes_contain[]      = ovic_generate_class_nav( 'owl_', $atts, count( $product_args ) );
		$atts                   = apply_filters( 'ovic_carousel_related_single_product', $atts );
		$owl_settings           = apply_filters( 'ovic_carousel_data_attributes', 'owl_', $atts );
		if ( $product_args ) : ?>
            <div class="<?php echo esc_attr( implode( ' ', $classes_contain ) ); ?>">
				<?php ovic_related_title_product( $prefix ); ?>
                <div class="owl-slick owl-products equal-container better-height" <?php echo esc_attr( $owl_settings ); ?>>
					<?php foreach ( $product_args as $object ) : ?>
                        <div <?php wc_product_class( $classes, $object ) ?>>
							<?php
							$post_object = get_post( $object->get_id() );
							setup_postdata( $GLOBALS['post'] =& $post_object );
							do_action( 'ovic_product_template', $template_style );
							?>
                        </div>
					<?php endforeach; ?>
                </div>
            </div>
		<?php endif;
		wp_reset_postdata();
	}
}
if ( ! function_exists( 'ovic_cross_sell_products' ) ) {
	function ovic_cross_sell_products( $limit = 2, $columns = 2, $orderby = 'rand', $order = 'desc' )
	{
		if ( is_checkout() ) {
			return;
		}
		// Get visible cross sells then sort them at random.
		$cross_sells = array_filter( array_map( 'wc_get_product', WC()->cart->get_cross_sells() ), 'wc_products_array_filter_visible' );
		wc_set_loop_prop( 'name', 'cross-sells' );
		wc_set_loop_prop( 'columns', apply_filters( 'woocommerce_cross_sells_columns', $columns ) );
		// Handle orderby and limit results.
		$orderby     = apply_filters( 'woocommerce_cross_sells_orderby', $orderby );
		$order       = apply_filters( 'woocommerce_cross_sells_order', $order );
		$cross_sells = wc_products_array_orderby( $cross_sells, $orderby, $order );
		$limit       = apply_filters( 'woocommerce_cross_sells_total', $limit );
		$cross_sells = $limit > 0 ? array_slice( $cross_sells, 0, $limit ) : $cross_sells;
		ovic_carousel_products( 'ovic_woo_crosssell', $cross_sells );
	}
}
if ( ! function_exists( 'ovic_related_products' ) ) {
	function ovic_related_products()
	{
		global $product;
		if ( ! $product ) {
			return;
		}
		$args     = array(
			'posts_per_page' => 4,
			'columns'        => 4,
			'orderby'        => 'rand',
		);
		$args     = apply_filters( 'woocommerce_output_related_products_args', $args );
		$defaults = array(
			'posts_per_page' => 2,
			'columns'        => 2,
			'orderby'        => 'rand', // @codingStandardsIgnoreLine.
			'order'          => 'desc',
		);
		$args     = wp_parse_args( $args, $defaults );
		// Set global loop values.
		wc_set_loop_prop( 'name', 'related' );
		wc_set_loop_prop( 'columns', apply_filters( 'woocommerce_related_products_columns', $args['columns'] ) );
		// Get visible related products then sort them at random.
		$args['related_products'] = array_filter( array_map( 'wc_get_product', wc_get_related_products( $product->get_id(), $args['posts_per_page'], $product->get_upsell_ids() ) ), 'wc_products_array_filter_visible' );
		// Handle orderby.
		$args['related_products'] = wc_products_array_orderby( $args['related_products'], $args['orderby'], $args['order'] );
		ovic_carousel_products( 'ovic_woo_related', $args['related_products'] );
	}
}
if ( ! function_exists( 'ovic_upsell_display' ) ) {
	function ovic_upsell_display( $orderby = 'rand', $order = 'desc', $limit = '-1', $columns = 4 )
	{
		global $product;
		if ( ! $product ) {
			return;
		}
		// Handle the legacy filter which controlled posts per page etc.
		$args = apply_filters( 'woocommerce_upsell_display_args', array(
				'posts_per_page' => $limit,
				'orderby'        => $orderby,
				'columns'        => $columns,
			)
		);
		wc_set_loop_prop( 'name', 'up-sells' );
		wc_set_loop_prop( 'columns', apply_filters( 'woocommerce_upsells_columns', isset( $args['columns'] ) ? $args['columns'] : $columns ) );
		$orderby = apply_filters( 'woocommerce_upsells_orderby', isset( $args['orderby'] ) ? $args['orderby'] : $orderby );
		$limit   = apply_filters( 'woocommerce_upsells_total', isset( $args['posts_per_page'] ) ? $args['posts_per_page'] : $limit );
		// Get visible upsells then sort them at random, then limit result set.
		$upsells = wc_products_array_orderby( array_filter( array_map( 'wc_get_product', $product->get_upsell_ids() ), 'wc_products_array_filter_visible' ), $orderby, $order );
		$upsells = $limit > 0 ? array_slice( $upsells, 0, $limit ) : $upsells;
		ovic_carousel_products( 'ovic_woo_upsell', $upsells );
	}
}
if ( ! function_exists( 'ovic_template_loop_product_title' ) ) {
	function ovic_template_loop_product_title()
	{
		global $product;

		$title_class = array( 'product-name product_title' );
		$permalink   = apply_filters( 'woocommerce_loop_product_link', $product->get_permalink(), $product );
		?>
        <h3 class="<?php echo esc_attr( implode( ' ', $title_class ) ); ?>">
            <a href="<?php echo esc_url( $permalink ); ?>"><?php the_title(); ?></a>
        </h3>
		<?php
	}
}
if ( ! function_exists( 'ovic_template_loop_product_thumbnail' ) ) {
	function ovic_template_loop_product_thumbnail()
	{
		global $product;
		// GET SIZE IMAGE SETTING
		$width  = 300;
		$height = 300;
		$crop   = true;
		$size   = wc_get_image_size( 'shop_catalog' );
		if ( $size ) {
			$width  = $size['width'];
			$height = $size['height'];
			if ( ! $size['crop'] ) {
				$crop = false;
			}
		}
		$lazy_load          = true;
		$thumbnail_id       = $product->get_image_id();
		$default_attributes = $product->get_default_attributes();
		$width              = apply_filters( 'ovic_shop_product_thumb_width', $width );
		$height             = apply_filters( 'ovic_shop_product_thumb_height', $height );
		if ( ! empty( $default_attributes ) ) {
			$lazy_load = false;
		}
		$image_thumb = apply_filters( 'ovic_resize_image', $thumbnail_id, $width, $height, $crop, $lazy_load );
		$permalink   = apply_filters( 'woocommerce_loop_product_link', $product->get_permalink(), $product );
		?>
        <a class="thumb-link woocommerce-product-gallery__image" href="<?php echo esc_url( $permalink ); ?>">
            <figure>
				<?php echo wp_specialchars_decode( $image_thumb['img'] ); ?>
            </figure>
        </a>
		<?php
	}
}
if ( ! function_exists( 'ovic_custom_new_flash' ) ) {
	function ovic_custom_new_flash()
	{
		global $post, $product;
		$postdate      = get_the_time( 'Y-m-d' );
		$postdatestamp = strtotime( $postdate );
		$newness       = apply_filters( 'ovic_get_option', 'ovic_product_newness', 7 );
		if ( ( time() - ( 60 * 60 * 24 * (int) $newness ) ) < (int) $postdatestamp ) :
			echo apply_filters( 'woocommerce_new_flash', '<span class="onnew"><span class="text">' . esc_html__( 'New', 'ovic-toolkit' ) . '</span></span>', $post, $product );
		endif;
	}
}
if ( ! function_exists( 'ovic_woocommerce_group_flash' ) ) {
	function ovic_woocommerce_group_flash()
	{
		?>
        <div class="flash">
			<?php do_action( 'ovic_group_flash_content' ); ?>
        </div>
		<?php
	}
}
if ( ! function_exists( 'ovic_custom_sale_flash' ) ) {
	function ovic_custom_sale_flash( $text )
	{
		$percent = ovic_get_percent_discount();
		if ( $percent != '' ) {
			return '<span class="onsale"><span class="text">' . $percent . '</span></span>';
		}

		return '';
	}
}
if ( ! function_exists( 'ovic_get_percent_discount' ) ) {
	function ovic_get_percent_discount()
	{
		global $product;
		$percent = '';
		if ( $product->is_on_sale() ) {
			if ( $product->is_type( 'variable' ) ) {
				$available_variations = $product->get_available_variations();
				$maximumper           = 0;
				$minimumper           = 0;
				$percentage           = 0;
				for ( $i = 0; $i < count( $available_variations ); ++ $i ) {
					$variation_id      = $available_variations[ $i ]['variation_id'];
					$variable_product1 = new WC_Product_Variation( $variation_id );
					$regular_price     = $variable_product1->get_regular_price();
					$sales_price       = $variable_product1->get_sale_price();
					if ( $regular_price > 0 && $sales_price > 0 ) {
						$percentage = round( ( ( ( $regular_price - $sales_price ) / $regular_price ) * 100 ), 0 );
					}
					if ( $minimumper == 0 ) {
						$minimumper = $percentage;
					}
					if ( $percentage > $maximumper ) {
						$maximumper = $percentage;
					}
					if ( $percentage < $minimumper ) {
						$minimumper = $percentage;
					}
				}
				if ( $minimumper == $maximumper ) {
					$percent .= '-' . $minimumper . '%';
				} else {
					$percent .= '-(' . $minimumper . '-' . $maximumper . ')%';
				}
			} else {
				if ( $product->get_regular_price() > 0 && $product->get_sale_price() > 0 ) {
					$percentage = round( ( ( ( $product->get_regular_price() - $product->get_sale_price() ) / $product->get_regular_price() ) * 100 ), 0 );
					$percent    .= '-' . $percentage . '%';
				}
			}
		}

		return $percent;
	}
}
if ( ! function_exists( 'ovic_function_shop_loop_item_countdown' ) ) {
	function ovic_function_shop_loop_item_countdown()
	{
		global $product;
		$date = ovic_get_max_date_sale( $product->get_id() );
		ob_start();
		if ( $date > 0 ) {
			?>
            <div class="ovic-countdown"
                 data-datetime="<?php echo date( 'm/j/Y g:i:s', $date ); ?>">
            </div>
			<?php
		}
		$html = ob_get_clean();
		echo apply_filters( 'ovic_custom_html_countdown', $html, $date );
	}
}
if ( ! function_exists( 'ovic_get_max_date_sale' ) ) {
	function ovic_get_max_date_sale( $product_id )
	{
		$date_now = current_time( 'timestamp', 0 );
		// Get variations
		$args          = array(
			'post_type'   => 'product_variation',
			'post_status' => array( 'private', 'publish' ),
			'numberposts' => - 1,
			'orderby'     => 'menu_order',
			'order'       => 'asc',
			'post_parent' => $product_id,
		);
		$variations    = get_posts( $args );
		$variation_ids = array();
		if ( $variations ) {
			foreach ( $variations as $variation ) {
				$variation_ids[] = $variation->ID;
			}
		}
		$sale_price_dates_to = false;
		if ( ! empty( $variation_ids ) ) {
			global $wpdb;
			$sale_price_dates_to = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_sale_price_dates_to' and post_id IN(" . join( ',', $variation_ids ) . ") ORDER BY meta_value DESC LIMIT 1" );
			if ( $sale_price_dates_to != '' ) {
				return $sale_price_dates_to;
			}
		}
		if ( ! $sale_price_dates_to ) {
			$sale_price_dates_to   = get_post_meta( $product_id, '_sale_price_dates_to', true );
			$sale_price_dates_from = get_post_meta( $product_id, '_sale_price_dates_from', true );
			if ( $sale_price_dates_to == '' || $date_now < $sale_price_dates_from ) {
				$sale_price_dates_to = '0';
			}
		}

		return $sale_price_dates_to;
	}
}
if ( ! function_exists( 'ovic_add_to_cart_single' ) ) {
	function ovic_add_to_cart_single()
	{
		$product_id        = isset( $_POST['product_id'] ) ? apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) ) : 0;
		$product           = wc_get_product( $product_id );
		$quantity          = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( $_POST['quantity'] );
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
		$product_status    = get_post_status( $product_id );
		$variation_id      = isset( $_POST['variation_id'] ) ? $_POST['variation_id'] : 0;
		$variation         = array();
		if ( $product && 'variation' === $product->get_type() ) {
			$variation_id = $product_id;
			$product_id   = $product->get_parent_id();
			$variation    = $product->get_variation_attributes();
		}
		if ( $product && $passed_validation && 'publish' === $product_status ) {
			if ( 'variation' === $product->get_type() && $variation_id > 0 && $product_id > 0 ) {
				WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );
			} elseif ( is_array( $quantity ) && ! empty( $quantity ) && 'group' === $product->get_type() ) {
				foreach ( $quantity as $product_id => $qty ) {
					if ( $qty > 0 ) {
						WC()->cart->add_to_cart( $product_id, $qty );
					}
				}
			} elseif ( ! is_array( $quantity ) && is_numeric( $quantity ) && 'simple' === $product->get_type() ) {
				WC()->cart->add_to_cart( $product_id, $quantity );
			}
			do_action( 'woocommerce_ajax_added_to_cart', $product_id );
			if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
				wc_add_to_cart_message( array( $product_id => $quantity ), true );
			}
			// Return fragments
			WC_AJAX::get_refreshed_fragments();
		} else {
			// If there was an error adding to the cart, redirect to the product page to show any errors
			$data = array(
				'error'       => true,
				'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
			);
			wp_send_json( $data );
		}
		wp_die();
	}
}