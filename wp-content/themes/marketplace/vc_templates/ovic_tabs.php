<?php
if ( !defined( 'ABSPATH' ) ) {
    die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * Shortcode class
 * @var $this "Ovic_Tabs"
 */
if ( !class_exists( 'Ovic_Shortcode_Tabs' ) ) {
    class Ovic_Shortcode_Tabs extends Ovic_Shortcode
    {
        /**
         * Shortcode name.
         *
         * @var  string
         */
        public $shortcode = 'tabs';

        static public function add_css_generate( $atts )
        {
            $atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_tabs', $atts ) : $atts;
            extract( $atts );
            $css        = '';

            $tab_color = $atts['tab_color'] != '' ? $atts['tab_color'] : '#000';
            if ( $atts['style'] == 'style1' ) {
                $css .= ' 
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .product_title a:hover,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .product_title a:focus
                { 
                    color: ' . $tab_color . ';
                }
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .tab-head .ovic-title,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .normal-effect:not(.light-bg):not(.dark-bg)::before,
                
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .normal-effect::before,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' input[type="submit"],
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .add-to-cart a,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .ovic-accordion.style-01 .panel.active .panel-title a::before,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .ovic-category:hover .ovic-title a,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .iconbox-inner .icon,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .product-item.style-1 .yith-wcqv-button:hover,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .product-item.style-1 a.compare:hover,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .product-item.style-1 .yith-wcwl-add-to-wishlist a:hover,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .vc_btn3-container .vc_btn3.vc_btn3-style-ovic-button,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .vc_btn3-container .vc_btn3.vc_btn3-style-ovic-button:focus,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .vc_btn3-container .vc_btn3.vc_btn3-style-ovic-button:hover
                { 
                    background-color: ' . $tab_color . ';
                }
                @media (max-width: 567px){
                    .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .tab-head
                    { 
                        background-color: ' . $tab_color . ';
                    }
                }
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .border-zoom::before,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .border-scale::before,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .border-scale:not(.loading-lazy)::after,
                
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .border-zoom::before,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .border-scale::before,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .border-scale:not(.loading-lazy)::after,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .ovic-accordion.style-01 .panel.active .panel-title a::before,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .product-item.style-5 .add-to-cart a:hover,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .product-item.style-5 .gallery-dots .slick-slide:hover::after,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .product-item.style-5 .gallery-dots .slick-slide.slick-current::after,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .ovic-products.style-2 .product-list-owl .product-item .product-wrap,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .product-item.style-3 .add-to-cart a:hover,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .ovic-category .button-category:hover,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .ovic-products.style-2 .product-list-owl,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .ovic-products.style-2 .product-list-owl .slick-slide > div:last-child > .product-item .product-wrap, 
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .ovic-products.style-2 .product-list-owl .slick-track > .product-item .product-wrap,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .product-item.style-2 .product-inner,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .product-item.style-2 .add-to-cart
                { 
                    border-color: ' . $tab_color . ';
                }
                @media (min-width: 1025px){
                    .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .product-item.style-2 .product-inner:hover,
                    .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .product-item.style-2 .add-to-cart
                    {
                        border-color: ' . $tab_color . ';
                    }
                }
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .ovic-products.loading .content-product-append::after, 
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .loading-lazy::after, 
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .ovic-accordion::after, 
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .tab-container.loading::after
                { 
                    border-top-color: ' . $tab_color . ';
                }
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .tab-link,
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .tab-link li a::after
                { 
                    border-bottom-color: ' . $tab_color . ';
                }
                .ovic-tabs.style1.' . $atts['ovic_custom_id'] . ' .tab-link li.active a{
                    box-shadow: 0 1px ' . $tab_color . ';
                }
                ';
            }

            return $css;
        }

        public function output_html( $atts, $content = null )
        {
            $atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_tabs', $atts ) : $atts;
            extract( $atts );
            $css_class    = array( 'ovic-tabs' );
            $css_class[]  = $atts['style'];
            $css_class[]  = $atts['el_class'];
            $class_editor = isset( $atts['css'] ) ? vc_shortcode_custom_css_class( $atts['css'], ' ' ) : '';
            $css_class[]  = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $class_editor, 'ovic_tabs', $atts );
            $sections     = self::get_all_attributes( 'vc_tta_section', $content );
            $rand         = uniqid();
            ob_start(); ?>
            <div class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
                <?php if ( $sections && is_array( $sections ) && count( $sections ) > 0 ): ?>
                    <?php if ( $atts['tab_title'] && $atts['style'] == 'style2' ): ?>
                        <h2 class="ovic-title">
                            <span class="text"><?php echo esc_html( $atts['tab_title'] ); ?></span>
                        </h2>
                    <?php endif; ?>
                    <div class="tab-head ovic-dropdown">
                        <?php if ( $atts['tab_title'] && $atts['style'] != 'style2' ): ?>
                            <h2 class="ovic-title">
                                <span class="text"><?php echo esc_html( $atts['tab_title'] ); ?></span>
                            </h2>
                        <?php endif; ?>
                        <ul class="tab-link">
                            <?php foreach ( $sections as $key => $section ) :
                                /* Get icon from section tabs */
                                $section['i_type'] = isset( $section['i_type'] ) ? $section['i_type'] : 'fontawesome';
                                $add_icon = isset( $section['add_icon'] ) ? $section['add_icon'] : '';
                                $position_icon = isset( $section['i_position'] ) ? $section['i_position'] : '';
                                $icon_html = $this->constructIcon( $section );
                                ?>
                                <li class="<?php if ( $key == $atts['active_section'] ): ?>active<?php endif; ?>">
                                    <a class="<?php echo $key == $atts['active_section'] ? 'loaded' : ''; ?>"
                                       data-ajax="<?php echo esc_attr( $atts['ajax_check'] ) ?>"
                                       data-animate="<?php echo esc_attr( $atts['css_animation'] ); ?>"
                                       data-section="<?php echo esc_attr( $section['tab_id'] ); ?>"
                                       data-id="<?php echo get_the_ID(); ?>"
                                       href="#<?php echo esc_attr( $section['tab_id'] ); ?>-<?php echo esc_attr( $rand ); ?>">
                                        <?php if ( isset( $section['title_image'] ) ) : ?>
                                            <figure>
                                                <?php
                                                $image_thumb = apply_filters( 'ovic_resize_image', $section['title_image'], false, false, true, false );
                                                echo htmlspecialchars_decode( $image_thumb['img'] );
                                                ?>
                                            </figure>
                                        <?php else : ?>
                                            <?php echo ( 'true' === $add_icon && 'right' !== $position_icon ) ? $icon_html : ''; ?>
                                            <span><?php echo esc_html( $section['title'] ); ?></span>
                                            <?php echo ( 'true' === $add_icon && 'right' === $position_icon ) ? $icon_html : ''; ?>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if ( $atts['style'] != 'style2' ): ?>
                            <a class="toggle-tabs" href="#" data-ovic="ovic-dropdown"><i class="fa fa-ellipsis-h"></i></a>
                        <?php endif; ?>
                    </div>
                    <div class="tab-container">
                        <?php foreach ( $sections as $key => $section ): ?>
                            <div class="tab-panel <?php if ( $key == $atts['active_section'] ): ?>active<?php endif; ?>"
                                 id="<?php echo esc_attr( $section['tab_id'] ); ?>-<?php echo esc_attr( $rand ); ?>">
                                <?php if ( $atts['ajax_check'] == '1' ) :
                                    echo $key == $atts['active_section'] ? do_shortcode( $section['content'] ) : '';
                                else :
                                    echo do_shortcode( $section['content'] );
                                endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php
            $html = ob_get_clean();

            return apply_filters( 'Ovic_Shortcode_Tabs', $html, $atts, $content );
        }
    }

    new Ovic_Shortcode_Tabs();
}