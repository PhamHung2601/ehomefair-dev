<?php
/* Custom Font icon*/
if ( !function_exists( 'marketplace_font_vc' ) ) {
	function marketplace_font_vc()
	{
		return array(
			array( 'flaticon-cart' => 'cart' ),
			array( 'flaticon-heart' => 'heart' ),
			array( 'flaticon-user' => 'user' ),
			array( 'flaticon-credit-card' => 'credit-card' ),
			array( 'flaticon-support' => 'support' ),
			array( 'flaticon-protect' => 'protect' ),
			array( 'flaticon-transport' => 'transport' ),
		);
	}

	add_filter( 'ovic_add_icon_field', 'marketplace_font_vc' );
}
if ( !function_exists( 'marketplace_add_cta_button_super_color' ) ) {
	function marketplace_add_cta_button_super_color()
	{
		$param                                              = WPBMap::getParam( 'vc_btn', 'style' );
		$param['value'][__( 'Ovic Button', 'marketplace' )] = 'ovic-button';
		vc_update_shortcode_param( 'vc_btn', $param );
	}

	add_action( 'vc_after_init', 'marketplace_add_cta_button_super_color' );
}

if ( !function_exists( 'marketplace_vc_fonts' ) ) {
	function marketplace_vc_fonts( $fonts_list )
	{
		/* Gotham */
		$Rubik              = new stdClass();
		$Rubik->font_family = "Rubik";
		$Rubik->font_styles = "300,300i,400,400i,500,500i,700,700i";
		$Rubik->font_types  = '300 light :300:normal,300 light italic :300:italic,400 regular:400:normal,400 regular italic:400:italic,500 medium:500:normal,500 medium italic:500:italic,700 bold :700:normal,700 bold italic:700:italic';
		$Rubik->font_styles = 'regular';
		$fonts              = array( $Rubik );

		return array_merge( $fonts_list, $fonts );
	}

	add_filter( 'vc_google_fonts_get_fonts_filter', 'marketplace_vc_fonts' );
}

if ( !function_exists( 'marketplace_param_visual_composer' ) ) {
	function marketplace_param_visual_composer( $param )
	{
		// CUSTOM PRODUCT SIZE
		$product_size_width_list = array();
		$width                   = 300;
		$height                  = 300;
		$crop                    = 1;
		if ( function_exists( 'wc_get_image_size' ) ) {
			$size   = wc_get_image_size( 'shop_catalog' );
			$width  = isset( $size['width'] ) ? $size['width'] : $width;
			$height = isset( $size['height'] ) ? $size['height'] : $height;
			$crop   = isset( $size['crop'] ) ? $size['crop'] : $crop;
		}
		for ( $i = 100; $i < $width; $i = $i + 10 ) {
			array_push( $product_size_width_list, $i );
		}
		$product_size_list                         = array();
		$product_size_list[$width . 'x' . $height] = $width . 'x' . $height;
		foreach ( $product_size_width_list as $k => $w ) {
			$w = intval( $w );
			if ( isset( $width ) && $width > 0 ) {
				$h = round( $height * $w / $width );
			} else {
				$h = $w;
			}
			$product_size_list[$w . 'x' . $h] = $w . 'x' . $h;
		}
		/* ADD PARAM*/
		vc_add_params(
			'vc_single_image',
			array(
				array(
					'param_name' => 'image_effect',
					'heading'    => esc_html__( 'Effect', 'marketplace' ),
					'group'      => esc_html__( 'Image Effect', 'marketplace' ),
					'type'       => 'dropdown',
					'value'      => array(
						esc_html__( 'None', 'marketplace' )                      => 'none',
						esc_html__( 'Normal Effect', 'marketplace' )             => 'effect normal-effect',
						esc_html__( 'Normal Effect Dark Color', 'marketplace' )  => 'effect normal-effect dark-bg',
						esc_html__( 'Normal Effect Light Color', 'marketplace' ) => 'effect normal-effect light-bg',
						esc_html__( 'Bounce In', 'marketplace' )                 => 'effect bounce-in',
						esc_html__( 'Plus Zoom', 'marketplace' )                 => 'effect plus-zoom',
						esc_html__( 'Border Zoom', 'marketplace' )               => 'effect border-zoom',
						esc_html__( 'Border ScaleUp', 'marketplace' )            => 'effect border-scale',
					),
					'sdt'        => 'none',
				),
			)
		);
		$product_size_list['Custom']          = 'custom';
		$param['ovic_accordion']['params'][0] = array(
			'type'        => 'select_preview',
			'heading'     => esc_html__( 'Select style', 'marketplace' ),
			'value'       => array(
				'default'  => array(
					'title'   => 'Default',
					'preview' => '',
				),
				'style-01' => array(
					'title'   => 'Style 01',
					'preview' => get_theme_file_uri( '/assets/images/shortcode-preview/style1.jpg' ),
				),
			),
			'default'     => 'style-01',
			'admin_label' => true,
			'param_name'  => 'style',
		);
		$param['ovic_category']               = array(
			'name'        => esc_html__( 'Ovic: Category', 'marketplace' ),
			'base'        => 'ovic_category', // shortcode
			'icon'        => get_theme_file_uri( 'assets/images/diagram.svg' ),
			'category'    => esc_html__( 'Ovic Shortcode', 'marketplace' ),
			'description' => esc_html__( 'Display a Category.', 'marketplace' ),
			'params'      => array(
				array(
					'type'       => 'attach_image',
					'heading'    => esc_html__( 'Banner Category', 'marketplace' ),
					'param_name' => 'banner',
				),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Title', 'marketplace' ),
					'param_name'  => 'title',
					'description' => esc_html__( 'The title of shortcode', 'marketplace' ),
					'admin_label' => true,
				),
				array(
					'type'        => 'vc_link',
					'heading'     => esc_html__( 'Top Link', 'marketplace' ),
					'param_name'  => 'top_link',
					'description' => esc_html__( 'The Link in top of shortcode', 'marketplace' ),
				),
				array(
					'type'        => 'taxonomy',
					'heading'     => esc_html__( 'Product Category', 'marketplace' ),
					'param_name'  => 'taxonomy',
					'options'     => array(
						'multiple'   => true,
						'hide_empty' => true,
						'taxonomy'   => 'product_cat',
					),
					'placeholder' => esc_html__( 'Choose category', 'marketplace' ),
					'description' => esc_html__( 'Note: If you want to narrow output, select category(s) above. Only selected categories will be displayed.', 'marketplace' ),
				),
				array(
					'type'        => 'vc_link',
					'heading'     => esc_html__( 'Bottom Link', 'marketplace' ),
					'param_name'  => 'bot_link',
					'description' => esc_html__( 'The Link in bottom of shortcode', 'marketplace' ),
				),
			),
		);
		$param['ovic_iconbox']['params'][0]   = array(
			'type'        => 'select_preview',
			'heading'     => esc_html__( 'Select style', 'marketplace' ),
			'value'       => array(
				'default' => array(
					'title'   => esc_html__( 'Default', 'marketplace' ),
					'preview' => get_theme_file_uri( '/assets/images/shortcode-preview/iconbox/default.jpg' ),
				),
				'style1'  => array(
					'title'   => esc_html__( 'Style 1', 'marketplace' ),
					'preview' => get_theme_file_uri( '/assets/images/shortcode-preview/iconbox/style1.jpg' ),
				),
			),
			'default'     => 'default',
			'admin_label' => true,
			'param_name'  => 'style',
		);
		$param['ovic_tabs']['params']         = array(
			array(
				'type'        => 'select_preview',
				'heading'     => esc_html__( 'Select style', 'marketplace' ),
				'value'       => array(
					'default' => array(
						'title'   => esc_html__( 'Default', 'marketplace' ),
						'preview' => get_theme_file_uri( '/assets/images/shortcode-preview/tabs/default.jpg' ),
					),
					'style1'  => array(
						'title'   => esc_html__( 'Style 1', 'marketplace' ),
						'preview' => get_theme_file_uri( '/assets/images/shortcode-preview/tabs/style1.jpg' ),
					),
					'style2'  => array(
						'title'   => esc_html__( 'Style 2', 'marketplace' ),
						'preview' => get_theme_file_uri( '/assets/images/shortcode-preview/tabs/style2.jpg' ),
					),
				),
				'default'     => 'default',
				'admin_label' => true,
				'param_name'  => 'style',
			),
			array(
				'type'       => 'colorpicker',
				'heading'    => esc_html__( 'Tab Color', 'marketplace' ),
				'param_name' => 'tab_color',
				'std'        => '#000000',
				'dependency' => array( 'element' => 'style', 'value' => array( 'style1' ) ),
			),
			array(
				'type'        => 'textfield',
				'heading'     => esc_html__( 'Title', 'marketplace' ),
				'param_name'  => 'tab_title',
				'description' => esc_html__( 'The title of shortcode', 'marketplace' ),
				'admin_label' => true,
			),
			vc_map_add_css_animation(),
			array(
				'param_name' => 'ajax_check',
				'heading'    => esc_html__( 'Using Ajax Tabs', 'marketplace' ),
				'type'       => 'dropdown',
				'value'      => array(
					esc_html__( 'Yes', 'marketplace' ) => '1',
					esc_html__( 'No', 'marketplace' )  => '0',
				),
				'std'        => '0',
			),
			array(
				'type'       => 'number',
				'heading'    => esc_html__( 'Active Section', 'marketplace' ),
				'param_name' => 'active_section',
				'std'        => 0,
			),
		);
		$param['ovic_slide']['as_parent']     = array(
			'only' => 'vc_single_image, vc_custom_heading, ovic_heading, vc_column_text, ovic_iconbox, ovic_category, ovic_person',
		);

		return $param;
	}

	add_filter( 'ovic_add_param_visual_composer', 'marketplace_param_visual_composer' );
}