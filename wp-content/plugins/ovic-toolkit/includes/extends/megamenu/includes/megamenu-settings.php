<?php
/**
 * Ovic Megamenu Settings
 *
 * @author   KHANH
 * @category API
 * @package  Ovic_Megamenu_Settings
 * @since    1.0.0
 */
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( !class_exists( 'Ovic_Megamenu_Settings' ) ) {
	class Ovic_Megamenu_Settings
	{
		public $key_settings  = '_ovic_menu_settings';
		public $key_megamenu  = '_megamenu_item_id';
		public $defaults_atts = array(
			'enable_mega'     => 0,
			'hide_title'      => 0,
			'disable_link'    => 0,
			'menu_width'      => '',
			'menu_icon'       => '',
			'menu_content_id' => 0,
			'menu_bg'         => 0,
			'menu_content'    => '',
		);

		public function __construct()
		{
			add_action( 'wp_ajax_ovic_menu_save_all_settings', array( $this, 'save_all_settings' ) );
			add_action( 'wp_ajax_ovic_menu_create_mega_menu', array( $this, 'create_mega_menu' ) );
			add_action( 'wp_ajax_ovic_get_form_settings', array( $this, 'ajax_form_settings' ) );
			add_action( 'wp_ajax_ovic_button_settings', array( $this, 'button_settings' ) );
			/* EDIT WALKER */
			add_filter( 'wp_edit_nav_menu_walker', array( $this, 'edit_walker' ), PHP_INT_MAX, 2 );
			add_filter( 'wp_nav_menu_args', array( $this, '_change_nav_menu_args' ), PHP_INT_MAX, 1 );
			add_filter( 'nav_menu_css_class', array( $this, '_change_class_menu_items' ), PHP_INT_MAX, 4 );
			add_filter( 'nav_menu_item_title', array( $this, '_change_title_menu_items' ), PHP_INT_MAX, 4 );
			add_filter( 'nav_menu_link_attributes', array( $this, '_change_menu_link_attributes' ), PHP_INT_MAX, 4 );
			add_filter( 'walker_nav_menu_start_el', array( $this, '_change_output_menu_items' ), PHP_INT_MAX, 4 );
			/*
			ADD MOBILE MENU OPTION
			add_action( 'after_setup_theme', array( $this, '_theme_setup' ) );
			add_action( 'wp_head', array( $this, 'clone_mobile_menu' ) ); */
			/* UPDATE SAVE POST */
			add_action( 'save_post', array( $this, '_update_post' ), 100, 2 );
			/* ADD CONTENT */
			add_action( 'admin_footer', array( $this, '_add_content' ) );
			add_action( 'init', array( $this, '_register' ), 10 );
		}

		public function edit_walker()
		{
			return 'Walker_Nav_Menu_Edit_Custom';
		}

		public function _theme_setup()
		{
			register_nav_menus( array(
					'ovic_mobile_menu' => esc_html__( 'Mobile Menu', 'ovic-toolkit' ),
				)
			);
		}

		/**
		 * Replace and save custom css to post meta.
		 *
		 * @param int $post_ID
		 *
		 * @return  void
		 */
		public function _update_post( $post_ID, $post )
		{
			if ( !wp_is_post_revision( $post_ID ) && $post->post_type == 'ovic_menu' && $menu_id = get_post_meta( $post_ID, $this->key_megamenu, true ) ) {
				$settings                 = get_post_meta( $menu_id, $this->key_settings, true );
				$settings['menu_content'] = get_post_field( 'post_content', $post_ID );
				update_post_meta( $menu_id, $this->key_settings, $settings );
			}
		}

		public function _register()
		{
			$labels = array(
				'name'          => esc_html__( 'Mega Menu', 'ovic-toolkit' ),
				'singular_name' => esc_html__( 'Mega Menu', 'ovic-toolkit' ),
				'all_items'     => esc_html__( 'Mega Menu', 'ovic-toolkit' ),
			);
			$args   = array(
				'labels'              => $labels,
				'public'              => true,
				'show_in_menu'        => 'themes.php',
				'show_in_nav_menus'   => false,
				'show_in_rest'        => true,
				'exclude_from_search' => true,
				'capability_type'     => 'page',
				'supports'            => array( 'title', 'editor' ),
			);
			register_post_type( 'ovic_menu', $args );
		}

		public function _add_content()
		{
			if ( $GLOBALS['pagenow'] == 'nav-menus.php' ) {
				include_once OVIC_MEGAMENU_DIR . 'templates/form-settings.php';
			}
		}

		public static function _is_frontend_editor()
		{
			if ( $get_referer = wp_get_referer() ) {
				if ( strpos( parse_url( $get_referer, PHP_URL_QUERY ), 'vc_action=vc_inline' ) !== false ) {
					return true;
				}
			}

			return false;
		}

		public static function is_elementor( $post_id )
		{
			$post_type   = get_post_type( $post_id );
			$cpt_support = get_option( 'elementor_cpt_support', [ 'page', 'post' ] );

			if ( class_exists( '\Elementor\Plugin' ) && in_array( $post_type, $cpt_support ) ) {
				return true;
			}

			return false;
		}

		public static function get_post_megamenu( $options_id = array() )
		{
			$options_menu = array();
			$posts        = get_posts(
				array(
					'post_type'      => 'ovic_menu',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'exclude'        => $options_id,
				)
			);
			if ( !is_wp_error( $posts ) && !empty( $posts ) ) {
				foreach ( $posts as $post ) {
					$options_menu[$post->ID] = $post->post_title;
				}
				wp_reset_postdata();
			}

			return $options_menu;
		}

		public function button_settings()
		{
			$response       = array(
				'icon'     => '',
				'label'    => '',
				'megamenu' => '',
				'success'  => 'no',
			);
			$icon           = '';
			$label          = '';
			$item_id        = isset( $_POST['item_id'] ) ? $_POST['item_id'] : '';
			$settings       = get_post_meta( $item_id, $this->key_settings, true );
			$menu_icon_type = isset( $settings['menu_icon_type'] ) ? $settings['menu_icon_type'] : 'font-icon';
			if ( $settings['menu_icon'] != '' || $settings['icon_image'] != '' ) {
				if ( $menu_icon_type == 'font-icon' ):
					if ( isset( $settings['menu_icon'] ) && $settings['menu_icon'] != "" ):
						$icon = '<span class="' . $settings['menu_icon'] . '"></span>';
					endif;
				endif;
				if ( $menu_icon_type == 'image' ):
					if ( isset( $settings['icon_image'] ) && $settings['icon_image'] != "" ):
						$icon = wp_get_attachment_image( $settings['icon_image'], 'thumbnail' );
					endif;
				endif;
			}
			if ( isset( $settings['label_image'] ) && $settings['label_image'] > 0 ):
				$label = wp_get_attachment_image( $settings['label_image'], 'thumbnail' );
			endif;
			if ( $settings['enable_mega'] == 1 ) {
				$response['megamenu'] = 'button-primary';
			}
			$response['success'] = 'yes';
			$response['icon']    = $icon;
			$response['label']   = $label;
			wp_send_json( $response );
			wp_die();
		}

		public static function _return_title_menu_item( $settings, $title )
		{
			if ( isset( $settings['hide_title'] ) && $settings['hide_title'] == 1 ) {
				$title = '';
			}
			$menu_icon_type = isset( $settings['menu_icon_type'] ) ? $settings['menu_icon_type'] : 'font-icon';
			if ( $menu_icon_type == 'font-icon' ) {
				if ( isset( $settings['menu_icon'] ) && $settings['menu_icon'] != '' ) {
					$title = '<span class="icon icon-font ' . esc_attr( $settings['menu_icon'] ) . '"></span>' . $title;
				}
			} elseif ( $menu_icon_type == 'image' ) {
				if ( isset( $settings['icon_image'] ) && $settings['icon_image'] != '' && $settings['icon_image'] > 0 ) {
					$image = wp_get_attachment_image( $settings['icon_image'], 'full', false, array( 'class' => 'icon icon-image' ) );
					$title = $image . $title;
				}
			}
			if ( isset( $settings['label_image'] ) && $settings['label_image'] != '' && $settings['label_image'] > 0 ) {
				$image = wp_get_attachment_image( $settings['label_image'], 'full', false, array( 'class' => 'label-image' ) );
				$title = $title . $image;
			}

			return $title;
		}

		public static function _return_megamenu_item( $settings, $depth = 0 )
		{
			$_output     = '';
			$enable_mega = false;
			if ( isset( $settings['enable_mega'] ) && $settings['enable_mega'] == 1 && $depth == 0 ) {
				$enable_mega = true;
			}
			if ( self::_is_frontend_editor() ) {
				$enable_mega = false;
			}
			if ( $enable_mega == true ) {
				$menu_width      = ( isset( $settings['menu_width'] ) && is_numeric( $settings['menu_width'] ) && $settings['menu_width'] > 0 ) ? $settings['menu_width'] : 1170;
				$menu_content_id = isset( $settings['menu_content_id'] ) ? $settings['menu_content_id'] : 0;
				$menu_content    = isset( $settings['menu_content'] ) ? $settings['menu_content'] : '';
				if ( $menu_content_id > 0 && $enable_mega == true ) {
					$css   = '';
					$image = '';
					if ( base64_encode( base64_decode( $menu_content, true ) ) === $menu_content ) {
						$menu_content = base64_decode( $menu_content );
						$content      = apply_filters( 'the_content', $menu_content );
						$content      = str_replace( ']]>', ']]>', $content );
					} else {
						$content = get_post_field( 'post_content', $menu_content_id );
						$content = wpautop( preg_replace( '/<\/?p\>/', "\n", $content ) . "\n" );
					}
					if ( $menu_width > 0 ) {
						$css .= 'width:' . $menu_width . 'px;';
					}
					if ( isset( $settings['menu_bg'] ) && $settings['menu_bg'] > 0 && $image = wp_get_attachment_image_url( $settings['menu_bg'], 'full' ) ) {
						$css .= "background-size: cover;";
						if ( isset( $settings['bg_position'] ) ) {
							$css .= "background-position: {$settings['bg_position']};";
						}
					}
					$responsive = isset( $settings['mega_responsive'] ) ? $settings['mega_responsive'] : '';
					$attr       = array(
						'class'           => 'sub-menu megamenu',
						'data-responsive' => $responsive,
					);
					if ( $css != '' ) {
						$attr['style'] = $css;
					}
					if ( $image != '' ) {
						$attr['data-src'] = $image;
						$attr['class']    = 'sub-menu megamenu lazy';
					}
					$_output = rtrim( "<div" );
					foreach ( $attr as $name => $value ) {
						$_output .= " $name=" . '"' . $value . '"';
					}
					$_output .= '>';
					if ( self::is_elementor( $menu_content_id ) ) {
						$_output .= \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $menu_content_id );
					} else {
						$_output .= do_shortcode( shortcode_unautop( $content ) );
					}
					$_output .= '</div>';
				}
			}

			return $_output;
		}

		public function _change_menu_link_attributes( $atts, $item, $args, $depth )
		{
			$settings = get_post_meta( $item->ID, $this->key_settings, true );
			if ( isset( $settings['disable_link'] ) && $settings['disable_link'] == 1 ) {
				$atts['href'] = '#';
			}
			if ( $atts['href'] == '#' || $atts['href'] == '' ) {
				$atts['class'] = 'disable-link';
			}

			return $atts;
		}

		public function _change_output_menu_items( $item_output, $item, $depth, $args )
		{
			$settings = get_post_meta( $item->ID, $this->key_settings, true );
			if ( $item->description ) {
				$item_output .= '<span class="desc">' . $item->description . '</span>';
			}
			$item_output .= $this->_return_megamenu_item( $settings, $depth );

			return $item_output;
		}

		public function _change_title_menu_items( $title, $item, $args, $depth )
		{
			$settings = get_post_meta( $item->ID, $this->key_settings, true );
			$title    = $this->_return_title_menu_item( $settings, $title );

			return $title;
		}

		public function _change_class_menu_items( $classes, $item, $args, $depth )
		{
			$settings    = get_post_meta( $item->ID, $this->key_settings, true );
			$enable_mega = false;
			if ( isset( $settings['enable_mega'] ) && $settings['enable_mega'] == 1 && $depth == 0 ) {
				$enable_mega = true;
			}
			if ( $enable_mega == true ) {
				$classes[] = 'menu-item-has-mega-menu';
				$classes[] = 'menu-item-has-children';
				$classes[] = 'item-megamenu';
			}

			return $classes;
		}

		public function _change_nav_menu_args( $args )
		{
			if ( is_admin() ) {
				return $args;
			}
			$locations = get_nav_menu_locations();
			if ( !empty( $locations[$args['theme_location']] ) ) {
				$menu = wp_get_nav_menu_object( $locations[$args['theme_location']] );
			} elseif ( !empty( $args['menu'] ) ) {
				$menu = wp_get_nav_menu_object( $args['menu'] );
			} else {
				$menus = (array)wp_get_nav_menus();
				if ( $menus ) {
					foreach ( $menus as $menu ) {
						$has_items = wp_get_nav_menu_items( $menu->term_id, array(
								'update_post_term_cache' => false,
							)
						);
						if ( $has_items ) {
							break;
						}
					}
				}
			}
			if ( !isset( $menu ) || is_wp_error( $menu ) || !is_object( $menu ) ) {
				return $args;
			}
			$megamenu_layout         = get_term_meta( $menu->term_id, '_megamenu_layout', true );
			$megamenu_layout         = $megamenu_layout != "" ? $megamenu_layout : 'horizontal'; //vertical
			$megamenu_layout         = isset( $args['megamenu_layout'] ) ? $args['megamenu_layout'] : $megamenu_layout;
			$container_class         = $args['container_class'] . ' ovic-menu-wapper ' . $megamenu_layout;
			$args['container']       = 'div';
			$args['container_class'] = $container_class;
			$args['menu_class']      = $args['menu_class'] . ' ovic-menu';
			if ( isset( $args['mobile_enable'] ) && $args['mobile_enable'] == 1 ) {
				$args['menu_class']      = $args['menu_class'] . ' ovic-clone-mobile-menu';
				$args['container_class'] = $args['container_class'] . ' support-mobile-menu';
			}

			return $args;
		}

		/**
		 * Get the current menu ID.
		 *
		 * Most of this taken from wp-admin/nav-menus.php (no built in functions to do this)
		 *
		 * @return int
		 * @since 1.0
		 */
		public function get_selected_menu_id()
		{
			$nav_menus            = wp_get_nav_menus( array( 'orderby' => 'name' ) );
			$menu_count           = count( $nav_menus );
			$nav_menu_selected_id = isset( $_REQUEST['menu'] ) ? (int)$_REQUEST['menu'] : 0;
			$add_new_screen       = ( isset( $_GET['menu'] ) && 0 == $_GET['menu'] ) ? true : false;
			// If we have one theme location, and zero menus, we take them right into editing their first menu
			$page_count                  = wp_count_posts( 'page' );
			$one_theme_location_no_menus = ( 1 == count( get_registered_nav_menus() ) && !$add_new_screen && empty( $nav_menus ) && !empty( $page_count->publish ) ) ? true : false;
			// Get recently edited nav menu
			$recently_edited = absint( get_user_option( 'nav_menu_recently_edited' ) );
			if ( empty( $recently_edited ) && is_nav_menu( $nav_menu_selected_id ) )
				$recently_edited = $nav_menu_selected_id;
			// Use $recently_edited if none are selected
			if ( empty( $nav_menu_selected_id ) && !isset( $_GET['menu'] ) && is_nav_menu( $recently_edited ) )
				$nav_menu_selected_id = $recently_edited;
			// On deletion of menu, if another menu exists, show it
			if ( !$add_new_screen && 0 < $menu_count && isset( $_GET['action'] ) && 'delete' == $_GET['action'] )
				$nav_menu_selected_id = $nav_menus[0]->term_id;
			// Set $nav_menu_selected_id to 0 if no menus
			if ( $one_theme_location_no_menus ) {
				$nav_menu_selected_id = 0;
			} elseif ( empty( $nav_menu_selected_id ) && !empty( $nav_menus ) && !$add_new_screen ) {
				// if we have no selection yet, and we have menus, set to the first one in the list
				$nav_menu_selected_id = $nav_menus[0]->term_id;
			}

			return $nav_menu_selected_id;
		}

		public function ajax_form_settings()
		{
			$response        = array(
				'html'    => '',
				'message' => '',
				'success' => 'no',
			);
			$item_id         = isset( $_POST['item_id'] ) ? $_POST['item_id'] : '';
			$title           = isset( $_POST['item_title'] ) ? $_POST['item_title'] : '';
			$item_depth      = isset( $_POST['depth'] ) ? $_POST['depth'] : '';
			$settings        = get_post_meta( $item_id, $this->key_settings, true );
			$settings        = wp_parse_args( ( array )$settings, $this->defaults_atts );
			$menu_icon_type  = isset( $settings['menu_icon_type'] ) ? $settings['menu_icon_type'] : 'font-icon';
			$menu_content_id = isset( $settings['menu_content_id'] ) ? $settings['menu_content_id'] : 0;
			if ( $menu_content_id > 0 ) {
				$item_iframe = admin_url( 'post.php?post=' . $menu_content_id . '&action=edit' );
			} else {
				$item_iframe = '';
			}
			$placeholder         = 'data:image/png;base64,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';
			$bg_thumbnail        = !empty( $settings['menu_bg'] ) ? wp_get_attachment_image_url( $settings['menu_bg'], 'thumbnail' ) : $placeholder;
			$icon_image_thumb    = !empty( $settings['icon_image'] ) ? wp_get_attachment_image_url( $settings['icon_image'], 'thumbnail' ) : $placeholder;
			$label_image_thumb   = !empty( $settings['label_image'] ) ? wp_get_attachment_image_url( $settings['label_image'], 'thumbnail' ) : $placeholder;
			$data_template       = array(
				'item_id'        => $item_id,
				'title'          => $title,
				'item_depth'     => $item_depth,
				'menu_icon_type' => $menu_icon_type,
				'iframe'         => $item_iframe,
				'settings'       => array(
					'menu_icon'         => $settings['menu_icon'],
					'mega_responsive'   => !empty( $settings['mega_responsive'] ) ? $settings['mega_responsive'] : '',
					'enable_mega'       => $settings['enable_mega'],
					'hide_title'        => $settings['hide_title'],
					'menu_width'        => $settings['menu_width'],
					'disable_link'      => $settings['disable_link'],
					'menu_bg'           => $settings['menu_bg'],
					'bg_position'       => !empty( $settings['bg_position'] ) ? $settings['bg_position'] : '',
					'icon_image'        => !empty( $settings['icon_image'] ) ? $settings['icon_image'] : '',
					'icon_image_thumb'  => $icon_image_thumb,
					'label_image_thumb' => $label_image_thumb,
					'label_image'       => !empty( $settings['label_image'] ) ? $settings['label_image'] : '',
					'bg_thumbnail'      => $bg_thumbnail,
					'menu_content_id'   => $menu_content_id,
				),
			);
			$response['html']    = $data_template;
			$response['success'] = 'yes';
			wp_send_json( $response );
			wp_die();
		}

		public function save_all_settings()
		{
			$response                 = array(
				'url'       => '',
				'status'    => false,
				'errors'    => array(),
				'is_update' => true,
				'settings'  => array(),
			);
			$item_id                  = !empty( $_POST['item_id'] ) ? intval( $_POST['item_id'] ) : 0;
			$settings                 = !isset( $_POST['menu_settings'] ) ? array() : $_POST['menu_settings'];
			$settings['enable_mega']  = !isset( $settings['enable_mega'] ) ? 0 : 1;
			$settings['hide_title']   = !isset( $settings['hide_title'] ) ? 0 : 1;
			$settings['disable_link'] = !isset( $settings['disable_link'] ) ? 0 : 1;
			if ( $settings['enable_mega'] == 1 && $settings['menu_content_id'] != 0 ) {
				if ( $item_id != get_post_meta( $settings['menu_content_id'], $this->key_megamenu, true ) ) {
					$settings['menu_content'] = get_post_field( 'post_content', $settings['menu_content_id'] );
					update_post_meta( $settings['menu_content_id'], $this->key_megamenu, $item_id );
				}
			}
			$settings_saved = get_post_meta( $item_id, $this->key_settings, true );
			if ( is_array( $settings_saved ) && !empty( $settings_saved ) ) {
				$settings = wp_parse_args( ( array )$settings, $settings_saved );
			} else {
				$settings = wp_parse_args( ( array )$settings, $this->defaults_atts );
			}
			if ( !$item_id ) {
				$response['errors'][] = esc_html__( 'Menu item not exists.', 'ovic-toolkit' );
				exit( json_encode( $response ) );
			}
			update_post_meta( $item_id, $this->key_settings, $settings );
			$response['settings'] = $settings;
			$response['status']   = true;
			wp_send_json( $response );
			wp_die();
		}

		public function create_mega_menu()
		{
			$response      = array(
				'url'     => '',
				'html'    => '',
				'post_id' => 0,
				'status'  => false,
				'errors'  => array(),
			);
			$html_megamenu = '';
			$item_id       = isset( $_POST['item_id'] ) ? absint( $_POST['item_id'] ) : 0;
			$item_title    = isset( $_POST['item_title'] ) ? wp_unslash( $_POST['item_title'] ) : '';
			$options_id    = isset( $_POST['options_id'] ) ? wp_unslash( $_POST['options_id'] ) : array();
			$settings      = get_post_meta( $item_id, $this->key_settings, true );
			$settings      = wp_parse_args( ( array )$settings, $this->defaults_atts );
			if ( !$item_id ) {
				$response['errors'][] = esc_html__( 'Menu item not exists.', 'ovic-addon-toolkit' );
				exit( json_encode( $response ) );
			}
			$menu_content_id = isset( $settings['menu_content_id'] ) ? $settings['menu_content_id'] : 0;
			if ( isset( $_POST['post_id'] ) && $_POST['post_id'] > 0 ) {
				$menu_content_id = absint( $_POST['post_id'] );
			}
			if ( 'publish' != get_post_status( $menu_content_id ) || $menu_content_id == 0 ) {
				// Create post object
				$preflix        = uniqid( 'Megamenu-' );
				$ovic_menu_post = array(
					'post_title'   => "Megamenu - {$item_title} - {$item_id}",
					'post_content' => "",
					'post_status'  => "publish",
					'post_type'    => "ovic_menu",
				);
				// Insert the post into the database
				$menu_content_id             = wp_insert_post( $ovic_menu_post );
				$settings['menu_content_id'] = $menu_content_id;
				update_post_meta( $item_id, $this->key_settings, $settings );
			}
			$options_megamenu = self::get_post_megamenu( $options_id );
			if ( !empty( $options_megamenu ) ) {
				foreach ( $options_megamenu as $id => $title ) {
					$selected      = ( $id == $menu_content_id ) ? 'selected' : '';
					$html_megamenu .= "<option value='{$id}' {$selected}>{$title}</option>";
				}
			}
			$response['url']     = admin_url( "post.php?post={$menu_content_id}&action=edit" );
			$response['post_id'] = $menu_content_id;
			$response['html']    = $html_megamenu;
			$response['status']  = true;
			wp_send_json( $response );
			wp_die();
		}
	}

	new Ovic_Megamenu_Settings();
}