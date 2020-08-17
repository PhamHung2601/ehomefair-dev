<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

// Ensure visibility
if ( empty( $product ) || !$product->is_visible() ) {
	return;
}

// Custom columns
$ovic_woo_bg_items = apply_filters( 'ovic_get_option', 'ovic_woo_bg_items', 4 );
$ovic_woo_lg_items = apply_filters( 'ovic_get_option', 'ovic_woo_lg_items', 4 );
$ovic_woo_md_items = apply_filters( 'ovic_get_option', 'ovic_woo_md_items', 4 );
$ovic_woo_sm_items = apply_filters( 'ovic_get_option', 'ovic_woo_sm_items', 6 );
$ovic_woo_xs_items = apply_filters( 'ovic_get_option', 'ovic_woo_xs_items', 6 );
$ovic_woo_ts_items = apply_filters( 'ovic_get_option', 'ovic_woo_ts_items', 12 );
$shop_display_mode = apply_filters( 'ovic_get_option', 'ovic_shop_list_style', 'grid' );
$product_style     = apply_filters( 'ovic_get_option', 'ovic_shop_product_style', 1 );
$template_style    = 'style-' . $product_style;
$classes           = array( 'product-item' );
if ( $shop_display_mode == 'list' ) {
	$classes[] = 'list col-sm-12';
} else {
	$classes[] = 'col-bg-' . $ovic_woo_bg_items;
	$classes[] = 'col-lg-' . $ovic_woo_lg_items;
	$classes[] = 'col-md-' . $ovic_woo_md_items;
	$classes[] = 'col-sm-' . $ovic_woo_sm_items;
	$classes[] = 'col-xs-' . $ovic_woo_xs_items;
	$classes[] = 'col-ts-' . $ovic_woo_ts_items;
}
if ( $shop_display_mode != 'list' ) {
	$classes[] = $template_style;
}
$classes        = apply_filters( 'ovic_class_content_product', $classes );
$template_style = apply_filters( 'ovic_style_content_product', $template_style );
?>
<li <?php wc_product_class( $classes, $product ); ?>>
	<?php if ( $shop_display_mode == 'list' ):
		apply_filters( 'ovic_product_template', 'list' );
	else:
		apply_filters( 'ovic_product_template', $template_style );
	endif; ?>
</li>