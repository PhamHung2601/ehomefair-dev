<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * HOOK TEMPLATE
 */
/* SHOP CONTROL */
add_filter( 'ovic_before_shop_control_html', 'marketplace_before_shop_control' );
remove_action( 'ovic_control_before_content', 'woocommerce_catalog_ordering', 10 );
remove_action( 'ovic_control_before_content', 'ovic_product_per_page_tmp', 20 );
add_action( 'ovic_control_before_content', 'woocommerce_catalog_ordering', 20 );
add_action( 'ovic_control_before_content', 'ovic_product_per_page_tmp', 10 );
add_action( 'ovic_control_after_content', 'ovic_product_per_page_tmp', 5 );
add_action( 'woocommerce_archive_description', 'marketplace_template_shop_banner', 10 );
add_filter( 'ovic_before_woocommerce_content', 'market_before_shop_loop' );
/* PRODUCT COUNTDOWN */
add_filter( 'ovic_custom_html_countdown', 'marketplace_html_product_countdown', 10, 2 );
/* REMOVE TITLE */
add_filter( 'woocommerce_show_page_title', function () { return false; } );
/* MARGIN RELATED */
add_filter( 'ovic_carousel_related_single_product', function ( $atts ) {
	$atts['owl_slide_margin'] = 0;

	return $atts;
}
);
/**
 *
 * CUSTOM CLASS CONTENT PRODUCTS SHOP
 */
add_filter( 'ovic_class_content_product', 'marketplace_class_content_product' );
/**
 *
 * HOOK MINI CART
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'marketplace_cart_link_fragment' );
add_action( 'woocommerce_before_mini_cart', 'marketplace_cart_view_count', 10 );
add_action( 'marketplace_header_mini_cart', 'marketplace_header_mini_cart' );
/**
 * HOOK TEMPLATE FUNCTIONS
 */
// custom size thumbnail single product
add_filter( 'woocommerce_get_image_size_gallery_thumbnail', 'marketplace_woocommerce_get_image_size_gallery_thumbnail' );
if ( !function_exists( 'marketplace_woocommerce_get_image_size_gallery_thumbnail' ) ) {
	function marketplace_woocommerce_get_image_size_gallery_thumbnail( $size )
	{
		$size['width']  = 110;
		$size['height'] = 125;
		$size['crop']   = 1;

		return $size;
	}
}
if ( !function_exists( 'marketplace_template_single_sharing' ) ) {
	function marketplace_template_single_sharing()
	{
		do_action( 'ovic_share_button', get_the_ID() );
	}
}
/* SHOP CONTROL */
if ( !function_exists( 'marketplace_pagination_top_control' ) ) {
	add_action( 'ovic_control_before_content', 'marketplace_pagination_top_control', 40 );
	function marketplace_pagination_top_control()
	{
		global $wp_query;
		if ( $wp_query->max_num_pages <= 1 ) :
			return;
		else : ?>
            <div class="pagination-top">
				<?php
				$curent_paged = max( 1, get_query_var( 'paged' ) );
				$max_page     = $wp_query->max_num_pages;
				?>
				<?php echo get_previous_posts_link( '<i class="fa fa-angle-left"></i>' ); ?>
                <span class="curent-page"><?php echo $curent_paged; ?></span>
                <span class="text"><?php echo esc_html__( 'of', 'marketplace' ) ?></span>
                <span class="max-page"><?php echo $max_page; ?></span>
				<?php echo get_next_posts_link( '<i class="fa fa-angle-right"></i>' ); ?>
            </div>
		<?php endif;
	}
}
if ( !function_exists( 'marketplace_before_shop_control' ) ) {
	function marketplace_before_shop_control()
	{
		?>
        <div class="ordering-top">
			<?php woocommerce_result_count(); ?>
        </div>
        <div class="shop-before-control">
			<?php do_action( 'ovic_control_before_content' ); ?>
        </div>
        <h1 class="page-title"><?php woocommerce_page_title(); ?></h1>
		<?php
	}
}
if ( !function_exists( 'market_before_shop_loop' ) ) {
	function market_before_shop_loop( $class )
	{
		$shop_products_style = Marketplace_Functions::get_option( 'ovic_shop_product_style' );
		$class[]             = $shop_products_style == '2' ? 'style-1' : '';
		$class[]             = 'style-' . $shop_products_style;

		return $class;
	}
}
/**
 *
 * HOOK TEMPLATE BANNER
 */
/* SINGLE PRODUCT */
if ( !function_exists( 'marketplace_template_single_available' ) ) {
	function marketplace_template_single_available()
	{
		global $product;

		$availability = $product->get_availability();

		if ( !empty( $availability['availability'] ) ) :
			?>
            <p class="<?php echo esc_attr( 'stock available-product ' . $availability['class'] ); ?>">
				<?php echo esc_html__( 'Availability:', 'marketplace' ); ?>
                <span> <?php echo esc_html( $availability['availability'] ); ?></span>
            </p>
		<?php
		endif;
	}
}
if ( !function_exists( 'marketplace_template_shop_banner' ) ) {
	function marketplace_template_shop_banner()
	{
		$banner     = Marketplace_Functions::get_option( 'ovic_banner_shop' );
		$url_banner = Marketplace_Functions::get_option( 'ovic_banner_link' );
		$html       = '';
		if ( $banner ) {
			$image_thumb = apply_filters( 'ovic_resize_image', $banner, false, false, true, false );
			/* html banner */
			$html .= '<div class="banner-shop">';
			$html .= '<a href="' . esc_url( $url_banner ) . '">';
			$html .= $image_thumb['img'];
			$html .= '</a>';
			$html .= '</div>';
		}
		echo $html;
	}
}
/* CLASS CONTENT PRODUCT */
if ( !function_exists( 'marketplace_class_content_product' ) ) {
	function marketplace_class_content_product( $classes )
	{
		$ovic_woo_product_style = Marketplace_Functions::get_option( 'ovic_shop_product_style', 1 );
		$shop_display_mode      = Marketplace_Functions::get_option( 'ovic_shop_list_style', 'grid' );
		if ( $shop_display_mode == 'grid' ) {
			$classes[] = $ovic_woo_product_style == '2' ? 'style-1' : '';
		}

		return $classes;
	}
}
/* SHOP CONTROL */
if ( !function_exists( 'marketplace_shop_display_mode_tmp' ) ) {
	remove_action( 'ovic_control_before_content', 'ovic_shop_display_mode_tmp', 30 );
	add_action( 'ovic_control_before_content', 'marketplace_shop_display_mode_tmp', 30 );
	function marketplace_shop_display_mode_tmp()
	{
		$shop_display_mode = Marketplace_Functions::get_option( 'ovic_shop_list_style', 'grid' );
		?>
        <div class="grid-view-mode">
            <form method="GET" action="">
                <button type="submit"
                        class="modes-mode mode-grid display-mode <?php if ( $shop_display_mode == 'grid' ): ?>active<?php endif; ?>"
                        value="grid"
                        name="ovic_shop_list_style">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <button type="submit"
                        class="modes-mode mode-list display-mode <?php if ( $shop_display_mode == 'list' ): ?>active<?php endif; ?>"
                        value="list"
                        name="ovic_shop_list_style">
                    <span></span>
                    <span></span>
                </button>
				<?php wc_query_string_form_fields( null, array( 'ovic_shop_list_style', 'submit', 'paged', 'product-page' ) ); ?>
            </form>
        </div>
		<?php
	}
}
if ( !function_exists( 'marketplace_sku_product' ) ) {
	add_action( 'marketplace_sku_product', 'marketplace_sku_product' );
	function marketplace_sku_product()
	{
		global $product;
		if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
            <div class="product-sku product_meta">
                <div class="sku_wrapper">
					<?php esc_html_e( 'SKU:', 'marketplace' ); ?>
                    <span class="sku"><?php echo ( $sku = $product->get_sku() ) ? $sku : esc_html__( 'N/A', 'marketplace' ); ?></span>
                </div>
            </div>
		<?php endif;
	}
}
if ( !function_exists( 'marketplace_html_product_countdown' ) ) {
	function marketplace_html_product_countdown( $html, $date )
	{
		ob_start();
		if ( $date > 0 ) {
			?>
            <div class="countdown-product">
                <p class="title-countdown">
                    <span><?php echo esc_html__( 'Hurry up!', 'marketplace' ); ?></span>
					<?php echo esc_html__( 'Offer end in:', 'marketplace' ); ?>
                </p>
                <div class="ovic-countdown"
                     data-datetime="<?php echo date( 'm/j/Y', $date ); ?>">
                </div>
            </div>
			<?php
		}

		return $html = ob_get_clean();
	}
}
/* MINI CART */
if ( !function_exists( 'marketplace_header_cart_link' ) ) {
	function marketplace_header_cart_link()
	{
		global $woocommerce;
		?>
        <div class="shopcart-dropdown block-cart-link" data-ovic="ovic-dropdown">
            <a class="link-dropdown" href="<?php echo wc_get_cart_url(); ?>">
                    <span class="fa fa-shopping-bag icon">
                        <span class="count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
                    </span>
                <span class="total"><?php echo $woocommerce->cart->get_cart_subtotal(); ?></span>
            </a>
        </div>
		<?php
	}
}
if ( !function_exists( 'marketplace_header_mini_cart' ) ) {
	function marketplace_header_mini_cart()
	{
		?>
        <div class="block-minicart ovic-mini-cart ovic-dropdown">
			<?php
			marketplace_header_cart_link();
			the_widget( 'WC_Widget_Cart', 'title=' );
			?>
        </div>
		<?php
	}
}
if ( !function_exists( 'marketplace_cart_link_fragment' ) ) {
	function marketplace_cart_link_fragment( $fragments )
	{
		ob_start();
		marketplace_header_cart_link();
		$fragments['div.block-cart-link'] = ob_get_clean();
		ob_start(); ?>
        <div class="mobile-block mobile-block-minicart">
            <a class="link-dropdown" href="<?php echo wc_get_cart_url(); ?>">
                    <span class="fa fa-shopping-bag icon">
                        <span class="count"><?php echo WC()->cart->cart_contents_count; ?></span>
                    </span>
                <span class="text"><?php echo esc_html__( 'Cart', 'marketplace' ); ?></span>
            </a>
        </div>
		<?php $fragments['div.mobile-block-minicart'] = ob_get_clean();

		return $fragments;
	}
}
if ( !function_exists( 'marketplace_cart_view_count' ) ) {
	function marketplace_cart_view_count()
	{
		if ( WC()->cart->get_cart_contents_count() != 0 ) {
			echo sprintf( '<div class="count-content"><span>' . esc_html__( '%s', 'marketplace' ) . '</span>' . esc_html__( ' item(s) in My Cart', 'marketplace' ) . '</div>', WC()->cart->get_cart_contents_count() );
		}
	}
}
/* AJAX UPDATE WISH LIST */
if ( !function_exists( 'marketplace_update_wishlist_count' ) ) {
	function marketplace_update_wishlist_count()
	{
		if ( function_exists( 'YITH_WCWL' ) ) {
			wp_send_json( YITH_WCWL()->count_products() );
		}
		wp_die();
	}

	// Wishlist ajaxify update
	add_action( 'wp_ajax_marketplace_update_wishlist_count', 'marketplace_update_wishlist_count' );
	add_action( 'wp_ajax_nopriv_marketplace_update_wishlist_count', 'marketplace_update_wishlist_count' );
}
/**
 *
 * REMOVE DESCRIPTION HEADING, INFOMATION HEADING
 */
add_filter( 'woocommerce_product_description_heading', 'marketplace_product_description_heading' );
if ( !function_exists( 'marketplace_product_description_heading' ) ) {
	function marketplace_product_description_heading()
	{
		return '';
	}
}
add_filter( 'woocommerce_product_additional_information_heading', 'marketplace_product_additional_information_heading' );
if ( !function_exists( 'marketplace_product_additional_information_heading' ) ) {
	function marketplace_product_additional_information_heading()
	{
		return '';
	}
}