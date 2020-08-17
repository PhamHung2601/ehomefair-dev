<?php
if ( !isset( $content_width ) ) $content_width = 900;
if ( !class_exists( 'Marketplace_Functions' ) ) {
	class Marketplace_Functions
	{
		/**
		 * @var Marketplace_Functions The one true Marketplace_Functions
		 * @since 1.0
		 */
		private static $instance;

		public static function instance()
		{
			if ( !isset( self::$instance ) && !( self::$instance instanceof Marketplace_Functions ) ) {
				self::$instance = new Marketplace_Functions;
			}
			add_action( 'after_setup_theme', array( self::$instance, 'marketplace_setup' ) );
			add_action( 'widgets_init', array( self::$instance, 'widgets_init' ) );
			add_action( 'wp_enqueue_scripts', array( self::$instance, 'enqueue_scripts' ) );
			add_filter( 'get_default_comment_status', array( self::$instance, 'open_default_comments_for_page' ), 10, 3 );
			add_filter( 'comment_form_fields', array( self::$instance, 'marketplace_move_comment_field_to_bottom' ), 10, 3 );
			self::includes();

			return self::$instance;
		}

		public function marketplace_setup()
		{
			load_theme_textdomain( 'marketplace', get_template_directory() . '/languages' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'title-tag' );
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'custom-background' );
			add_theme_support( 'customize-selective-refresh-widgets' );
			add_editor_style(
				array(
					'style-editor.css',
					self::fonts_url(),
				)
			);
			/*This theme uses wp_nav_menu() in two locations.*/
			register_nav_menus( array(
					'primary'        => esc_html__( 'Primary Menu', 'marketplace' ),
					'vertical_menu'  => esc_html__( 'Vertical Menu', 'marketplace' ),
					'top_left_menu'  => esc_html__( 'Top Menu First', 'marketplace' ),
					'top_right_menu' => esc_html__( 'Top Menu Second', 'marketplace' ),
				)
			);
			add_theme_support( 'html5', array(
					'search-form', 'comment-form', 'comment-list', 'gallery', 'caption',
				)
			);
			add_theme_support( 'post-formats',
				array(
					'image',
					'video',
					'quote',
					'link',
					'gallery',
					'audio',
				)
			);
			add_theme_support( 'ovic-theme-option' );
			/*Support woocommerce*/
			add_theme_support( 'woocommerce' );
			add_theme_support( 'wc-product-gallery-lightbox' );
			add_theme_support( 'wc-product-gallery-slider' );
			add_theme_support( 'wc-product-gallery-zoom' );
		}

		public function marketplace_move_comment_field_to_bottom( $fields )
		{
			$comment_field = $fields['comment'];
			unset( $fields['comment'] );
			$fields['comment'] = $comment_field;

			return $fields;
		}

		/**
		 * Register widget area.
		 *
		 * @since marketplace 1.0
		 *
		 * @link https://codex.wordpress.org/Function_Reference/register_sidebar
		 */
		function widgets_init()
		{
			register_sidebar( array(
					'name'          => esc_html__( 'Widget Area', 'marketplace' ),
					'id'            => 'widget-area',
					'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'marketplace' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2 class="widgettitle">',
					'after_title'   => '<span class="arow"></span></h2>',
				)
			);
			register_sidebar( array(
					'name'          => esc_html__( 'Widget Shop', 'marketplace' ),
					'id'            => 'widget-shop',
					'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'marketplace' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2 class="widgettitle">',
					'after_title'   => '<span class="arow"></span></h2>',
				)
			);
			register_sidebar( array(
					'name'          => esc_html__( 'Widget Product', 'marketplace' ),
					'id'            => 'widget-product',
					'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'marketplace' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2 class="widgettitle">',
					'after_title'   => '<span class="arow"></span></h2>',
				)
			);
			register_sidebar( array(
					'name'          => esc_html__( 'Widget Elements', 'marketplace' ),
					'id'            => 'widget-elements',
					'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'marketplace' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2 class="widgettitle">',
					'after_title'   => '<span class="arow"></span></h2>',
				)
			);
			register_sidebar( array(
					'name'          => esc_html__( 'Footer Top 1', 'marketplace' ),
					'id'            => 'footer-top-1',
					'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'marketplace' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2 class="widgettitle">',
					'after_title'   => '<span class="arow"></span></h2>',
				)
			);
			register_sidebar( array(
					'name'          => esc_html__( 'Footer Top 2', 'marketplace' ),
					'id'            => 'footer-top-2',
					'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'marketplace' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2 class="widgettitle">',
					'after_title'   => '<span class="arow"></span></h2>',
				)
			);
			register_sidebar( array(
					'name'          => esc_html__( 'Footer Top 3', 'marketplace' ),
					'id'            => 'footer-top-3',
					'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'marketplace' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2 class="widgettitle">',
					'after_title'   => '<span class="arow"></span></h2>',
				)
			);
			register_sidebar( array(
					'name'          => esc_html__( 'Footer Top 4', 'marketplace' ),
					'id'            => 'footer-top-4',
					'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'marketplace' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2 class="widgettitle">',
					'after_title'   => '<span class="arow"></span></h2>',
				)
			);
			register_sidebar( array(
					'name'          => esc_html__( 'Footer Top 5', 'marketplace' ),
					'id'            => 'footer-top-5',
					'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'marketplace' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2 class="widgettitle">',
					'after_title'   => '<span class="arow"></span></h2>',
				)
			);
			register_sidebar( array(
					'name'          => esc_html__( 'Footer Top 6', 'marketplace' ),
					'id'            => 'footer-top-6',
					'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'marketplace' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2 class="widgettitle">',
					'after_title'   => '<span class="arow"></span></h2>',
				)
			);
			register_sidebar( array(
					'name'          => esc_html__( 'Footer Middle', 'marketplace' ),
					'id'            => 'footer-mid-widget-area',
					'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'marketplace' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2 class="widgettitle">',
					'after_title'   => '<span class="arow"></span></h2>',
				)
			);
			register_sidebar( array(
					'name'          => esc_html__( 'Footer Bottom Left', 'marketplace' ),
					'id'            => 'footer-bottom-left-widget-area',
					'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'marketplace' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2 class="widgettitle">',
					'after_title'   => '<span class="arow"></span></h2>',
				)
			);
			register_sidebar( array(
					'name'          => esc_html__( 'Footer Bottom Right', 'marketplace' ),
					'id'            => 'footer-bottom-right-widget-area',
					'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'marketplace' ),
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2 class="widgettitle">',
					'after_title'   => '<span class="arow"></span></h2>',
				)
			);
		}

		/**
		 * Register custom fonts.
		 */
		function fonts_url()
		{
			$fonts_url = '';
			/**
			 * Translators: If there are characters in your language that are not
			 * supported by Montserrat, translate this to 'off'. Do not translate
			 * into your own language.
			 */
			$montserrat = esc_html_x( 'on', 'Montserrat font: on or off', 'marketplace' );
			if ( 'off' !== $montserrat ) {
				$font_families   = array();
				$font_families[] = 'Rubik:300,300i,400,400i,500,500i,700,700i,900,900i';
				$font_families[] = 'Lato:300,300i,400,400i,700,700i,900,900i';
				$query_args      = array(
					'family' => urlencode( implode( '|', $font_families ) ),
					'subset' => urlencode( 'latin,latin-ext' ),
				);
				$fonts_url       = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
			}

			return esc_url_raw( $fonts_url );
		}

		/**
		 * Enqueue scripts and styles.
		 *
		 * @since marketplace 1.0
		 */
		function enqueue_scripts()
		{
			global $wp_query;
			$posts = $wp_query->posts;
			wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
			wp_dequeue_style( 'yith-wcwl-font-awesome' );
			wp_dequeue_style( 'yith-quick-view' );
			wp_dequeue_script( 'prettyPhoto' );
			foreach ( $posts as $post ) {
				if ( is_a( $post, 'WP_Post' ) && !has_shortcode( $post->post_content, 'contact-form-7' ) ) {
					wp_dequeue_script( 'contact-form-7' );
				}
			}
			// Add custom fonts, used in the main stylesheet.
			wp_enqueue_style( 'marketplace-fonts', self::fonts_url(), array(), null );
			// Theme stylesheet.
			wp_enqueue_style( 'bootstrap', get_theme_file_uri( '/assets/css/bootstrap.min.css' ), array(), '1.0' );
			wp_enqueue_style( 'flaticon', get_theme_file_uri( '/assets/fonts/flaticon/flaticon.css' ), array(), '1.0' );
			wp_enqueue_style( 'font-awesome', get_theme_file_uri( '/assets/fonts/font-awesome/font-awesome.min.css' ), array(), '1.0' );
			if ( is_rtl() ) {
				wp_enqueue_style( 'marketplace_custom_css', get_theme_file_uri( '/assets/css/style-rtl.min.css' ), array(), '1.0', 'all' );
			} else {
				wp_enqueue_style( 'marketplace_custom_css', get_theme_file_uri( '/assets/css/style.min.css' ), array(), '1.0', 'all' );
			}
			wp_enqueue_style( 'marketplace-main-style', get_stylesheet_uri() );
			if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
				wp_enqueue_script( 'comment-reply' );
			}
			if ( class_exists( 'Ovic_Toolkit' ) ) {
				wp_enqueue_script( 'marketplace-script', get_theme_file_uri( '/assets/js/functions.min.js' ), array(), '1.0' );
			}
			wp_localize_script( 'marketplace-script', 'marketplace_ajax_frontend', array(
					'ajaxurl'  => admin_url( 'admin-ajax.php' ),
					'security' => wp_create_nonce( 'marketplace_ajax_frontend' ),
				)
			);
			$enable_sticky_menu = apply_filters( 'ovic_get_option', 'ovic_sticky_menu' );
			wp_localize_script( 'marketplace-script', 'marketplace_global_frontend',
				array(
					'ovic_sticky_menu' => $enable_sticky_menu,
				)
			);
		}

		public static function get_option( $key, $default = '' )
		{
			if ( has_filter( 'ovic_get_option' ) ) {
				return apply_filters( 'ovic_get_option', $key, $default );
			}

			return $default;
		}

		/**
		 * Filter whether comments are open for a given post type.
		 *
		 * @param string $status Default status for the given post type,
		 *                             either 'open' or 'closed'.
		 * @param string $post_type Post type. Default is `post`.
		 * @param string $comment_type Type of comment. Default is `comment`.
		 *
		 * @return string (Maybe) filtered default status for the given post type.
		 */
		function open_default_comments_for_page( $status, $post_type, $comment_type )
		{
			if ( 'page' == $post_type ) {
				return 'open';
			}

			return $status;
		}

		public static function includes()
		{
			include_once get_parent_theme_file_path( '/framework/framework.php' );
		}
	}
}
if ( !function_exists( 'Marketplace_Functions' ) ) {
	function Marketplace_Functions()
	{
		return Marketplace_Functions::instance();
	}

	Marketplace_Functions();
}