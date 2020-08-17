<?php
if ( class_exists( 'Ovic_Toolkit' ) ) {
    add_filter( 'ovic_main_custom_css', 'marketplace_theme_color' );
} else {
    add_action( 'wp_enqueue_scripts', 'marketplace_custom_inline_css', 999 );
}
if ( !function_exists( 'marketplace_custom_inline_css' ) ) {
    function marketplace_custom_inline_css()
    {
        $css     = '';
        $css     = marketplace_theme_color( $css );
        $content = preg_replace( '/\s+/', ' ', $css );
        wp_add_inline_style( 'marketplace_custom_css', $content );
    }
}
if ( !function_exists( 'marketplace_theme_color' ) ) {
	function marketplace_theme_color( $css )
	{
        $main_bg_color    = Marketplace_Functions::get_option( 'ovic_main_color', '#0163d2' );
        $main_text_color  = Marketplace_Functions::get_option( 'ovic_text_color', '#ffffff' );
        $main_hover_color = Marketplace_Functions::get_option( 'ovic_hover_color', '#0163d2' );
        /* Main background color */
		$css .= '
        .ovic-iconbox.style1 .icon,  
        .widget_shopping_cart .count-content span
        {
            color: ' . $main_bg_color . ';
        }
        .normal-effect::before,
		a.button, 
		button, 
		input[type="submit"],
		.widget #today,
		.post-item .post-date .month,
        .woocommerce-pagination a.page-numbers:hover,
        .woocommerce-pagination .page-numbers.current,
		.navigation .page-numbers.current,
		.navigation a.page-numbers:hover,
		.pagination .page-numbers.current,
		.pagination a.page-numbers:hover,
		.header-top,
		.chosen-container .chosen-results li.highlighted,
        .block-wishlist .woo-wishlist-link .count,
		.block-minicart .link-dropdown .count,
		.add-to-cart a,
		a.backtotop i,
		.slick-slider .slick-arrow:hover,
		#ship-to-different-address label input[type="checkbox"]:checked + span::before,
		.widget .tagcloud a:hover,
		.widget_layered_nav .list-group li a:hover::before,
		.ovic_product_filter .widget_product_categories ul li a:hover::before,
		.widget_layered_nav .list-group li.chosen>a::before,
		.ovic_product_filter .widget_product_categories ul li.current-cat>a::before,
		.ovic-accordion.style-01 .panel.active .panel-title a::before,
		.post-pagination span:not(.title),
		.page-links span:not(.page-links-title),
        .ovic-category:hover .ovic-title a,
        .block-nav-category,
        .mfp-content .mfp-close:hover,
		.iconbox-inner .icon,
        .block-search .btn-submit,
        .product-item.style-1 .yith-wcqv-button:hover,
        .product-item.style-1 a.compare:hover,
        .product-item.style-1 .yith-wcwl-add-to-wishlist a:hover,
        .vc_btn3-container .vc_btn3.vc_btn3-style-ovic-button,
        .vc_btn3-container .vc_btn3.vc_btn3-style-ovic-button:focus,
        .vc_btn3-container .vc_btn3.vc_btn3-style-ovic-button:hover,
        .mobile-footer-inner .icon .count,
        .ovic-mapper .ovic-pin .ovic-popup-footer a:hover,
        .widget-ovic-mailchimp .newsletter-form-wrap.processing::after,
		.ovic-live-search-form .keyword-current,
        .ovic-live-search-form .view-all
		{
			background-color: ' . $main_bg_color . ';
		}
		.border-zoom::before,
		.border-scale::before,
		.border-scale:not(.loading-lazy)::after,
        .woocommerce-pagination a.page-numbers:hover,
        .woocommerce-pagination .page-numbers.current,
		.navigation .page-numbers.current,
		.navigation a.page-numbers:hover,
		.pagination .page-numbers.current,
		.pagination a.page-numbers:hover,
		a.backtotop,
		.flex-control-nav .slick-slide img.flex-active,
		.flex-control-nav .slick-slide img:hover,
		.slick-slider .slick-arrow:hover,
		.widget .tagcloud a:hover,
		.widget_layered_nav .list-group li a:hover::before,
		.ovic_product_filter .widget_product_categories ul li a:hover::before,
		.widget_layered_nav .list-group li.chosen>a::before,
		.ovic_product_filter .widget_product_categories ul li.current-cat>a::before,
		.ovic-accordion.style-01 .panel.active .panel-title a::before,
		.post-pagination span:not(.title),
		.page-links span:not(.page-links-title),
		.widget_layered_nav .color-group>*.selected  i::before,
		.ui-slider .ui-slider-handle,
		.product-item.style-5 .add-to-cart a:hover,
        .product-item.style-5 .gallery-dots .slick-slide:hover::after,
        .product-item.style-5 .gallery-dots .slick-slide.slick-current::after,
        .ovic-products.style-2 .product-list-owl .product-item .product-wrap,
        .ovic-tabs .tab-link li.active a,
        .ovic-tabs .tab-link li:hover a,
        .product-item.style-3 .add-to-cart a:hover,
        .ovic-tabs.style2 .ovic-title,
        .ovic-category .button-category:hover,
        .woocommerce-cart-form .shop_table .actions>.button:not([disabled]),
        .woocommerce-cart-form .shop_table .actions .button:hover,
        .ovic-products.style-2 .product-list-owl,
        .ovic-products.style-2 .product-list-owl .slick-slide > div:last-child > .product-item .product-wrap, 
        .ovic-products.style-2 .product-list-owl .slick-track > .product-item .product-wrap,
        .product-item.style-2 .product-inner,
        .product-item.style-2 .add-to-cart,
        .ovic-mapper .ovic-pin .ovic-popup-footer a:hover
		{
			border-color: ' . $main_bg_color . ';
		}
		.ovic-products.loading .content-product-append::after, 
		.loading-lazy::after, 
		.ovic-accordion::after, 
		.tab-container.loading::after{
		    border-top-color: ' . $main_bg_color . ';
		}
		@media (min-width: 1025px){
            .header-nav .header-nav-inner{
                box-shadow: 0 3px ' . $main_bg_color . ';
            }
            .product-item.style-2 .product-inner:hover,
            .product-item.style-2 .add-to-cart
            {
                border-color: ' . $main_bg_color . ';
            }
		}
		@media (max-width: 1024px){
            .header-nav .header-nav-inner{
                border-bottom: 3px solid ' . $main_bg_color . ';
            }
		}
		';
        /* Main text color */
        $css .= '
        .header-top,
        .block-wishlist .woo-wishlist-link .count,
        .block-minicart .link-dropdown .count,
        a.button, 
        button, 
        input[type="submit"],
        a.button:hover,
        a.button:focus, 
        button:hover, 
        .add-to-cart a,
        .add-to-cart a:focus,
        .add-to-cart a:hover,
        input[type="submit"]:hover,
        .block-nav-category .block-title,
        .chosen-container .chosen-results li.highlighted,
        .post-pagination span:not(.title),
        .page-links span:not(.page-links-title),
        a.backtotop,
        a.backtotop:hover, 
        a.backtotop:focus,
        .slick-slider .slick-arrow:hover,
        .widget_layered_nav .list-group li a:hover::after, 
        .ovic_product_filter .widget_product_categories ul li a:hover::after, 
        .widget_layered_nav .list-group li.chosen a::after, 
        .ovic_product_filter .widget_product_categories ul li.current-cat a::after,
        .mfp-content .mfp-close:hover,
        .post-item .post-date .month,
        .navigation .page-numbers.current, 
        .pagination .page-numbers.current,
        .navigation a.page-numbers:hover, 
        .pagination a.page-numbers:hover,
        .widget .tagcloud a:hover,
        .block-search .btn-submit,
        .ovic-accordion.style-01 .panel.active .panel-title a::before,
        .iconbox-inner .icon,
        .product-item.style-1 .yith-wcqv-button:hover, 
        .product-item.style-1 a.compare:hover, 
        .product-item.style-1 .yith-wcwl-add-to-wishlist a:hover,
        .mobile-footer-inner .icon .count,
        .vc_btn3-container .vc_btn3.vc_btn3-style-ovic-button,
        .vc_btn3-container .vc_btn3.vc_btn3-style-ovic-button:focus,
        .vc_btn3-container .vc_btn3.vc_btn3-style-ovic-button:hover,
        .ovic-mapper .ovic-pin .ovic-popup-footer a:hover,
        .ovic-live-search-form .view-all
        {
            color: ' . $main_text_color . ';
        }
        ';
        /* Main text hover color */
        $css .= '
        a:hover,
		a:focus,
		.sticky-post,
		.post-item .post-content a,
		.comments-area .comment-content a,
		.ovic-dropdown > .sub-menu > .menu-item > a:hover, 
		.wcml-dropdown .wcml-cs-submenu > li > a:hover,
		.box-header-nav .main-menu > .menu-item.active > a,
		.box-header-nav .main-menu>.menu-item:hover > a,
		.box-header-nav .main-menu>.menu-item>.sub-menu:not(.megamenu) .menu-item:hover > a,
        .block-nav-category .vertical-menu > .menu-item:hover > a,
        .block-nav-category .vertical-menu > .menu-item > .sub-menu:not(.megamenu) > .menu-item:hover > a,
        .block-nav-category .vertical-menu > .menu-item > .sub-menu:not(.megamenu) .sub-menu > .menu-item:hover > a,
		.entry-summary a.compare:hover,
		.entry-summary a.compare:focus,
		.product-item a.compare:hover,
		.product-item a.compare:focus,
		.yith-wcwl-add-to-wishlist a:hover,
		a.yith-wcqv-button:hover,
		.woocommerce-account-fields .create-account > label:hover,
		.woocommerce-form-login .form-row label.woocommerce-form__label-for-checkbox:hover,
		.woocommerce .user-role>.radio:hover,
		.entry-summary .cart .price,
		.widget_product_categories ul li .carets:hover,
		.widget_product_categories ul li.show-sub>*:not(.children),
		.widget_product_categories ul li.show-sub::before,
		.widget_layered_nav .list-group li > a:hover,
		.ovic_product_filter .widget_product_categories ul li > a:hover
		{
			color: ' . $main_hover_color . ';
		}
        ';

		return $css;
	}
}