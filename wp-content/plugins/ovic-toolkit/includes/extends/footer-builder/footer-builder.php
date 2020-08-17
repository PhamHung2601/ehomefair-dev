<?php
/**
 * Ovic Footer Builder setup
 *
 * @author   KHANH
 * @category API
 * @package  Ovic_Footer_Builder
 * @since    1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Ovic Footer Builder setup
 *
 * @Active: add_theme_support( 'ovic-footer-builder' );
 */
if ( ! class_exists( 'Ovic_Footer_Builder' ) ) {
	class Ovic_Footer_Builder
	{
		public function __construct()
		{
			add_action( 'init', array( &$this, 'post_type' ), 999 );

			add_filter( 'ovic_config_customize_sections', array( $this, 'add_theme_options' ) );
			add_filter( 'ovic_config_customize_sections_v2', array( $this, 'add_theme_options' ) );

			add_action( 'wp_footer', array( $this, 'ovic_footer_content' ) );

			add_filter( 'ovic_main_custom_css', array( $this, 'ovic_shortcodes_custom_css' ) );
		}

		public static function ovic_get_footer_preview()
		{
			$footer_preview = array();
			$args           = array(
				'post_type'      => 'footer',
				'posts_per_page' => - 1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			);
			$posts          = get_posts( $args );
			foreach ( $posts as $post ) {
				setup_postdata( $post );
				$footer_preview[ $post->ID ] = array(
					'title'   => $post->post_title,
					'preview' => wp_get_attachment_image_url( get_post_thumbnail_id( $post->ID ), 'full' ),
				);
			}
			wp_reset_postdata();

			return $footer_preview;
		}

		function add_theme_options( $options )
		{
			if ( current_theme_supports( 'ovic-footer-builder' ) ) {
				$options['footer']['fields'] = array(
					'ovic_footer_template' => array(
						'id'      => 'ovic_footer_template',
						'type'    => 'select_preview',
						'title'   => esc_html__( 'Footer Template', 'ovic-toolkit' ),
						'desc'    => esc_html__( 'Select a Footer layout in page', 'ovic-toolkit' ),
						'options' => $this->ovic_get_footer_preview(),
					),
				);
			}

			return apply_filters( 'ovic_footer_builder_option', $options );
		}

		public function get_footer_option()
		{
			$footer_options   = apply_filters( 'ovic_get_option', 'ovic_footer_template', '' );
			$override_options = apply_filters( 'ovic_overide_footer_template', '' );
			if ( $override_options != '' ) {
				$footer_options = $override_options;
			}

			return $footer_options;
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

		function ovic_footer_content()
		{
			$footer_options = $this->get_footer_option();
			if ( current_theme_supports( 'ovic-footer-builder' ) ) {
				$footer_content = '';
				$class          = array( 'footer ovic-footer' );
				$args           = array(
					'post_type'      => 'footer',
					'posts_per_page' => 1,
				);
				if ( $footer_options ) {
					$args['p'] = $footer_options;
				}
				$query = new WP_Query( $args );
				ob_start(); ?>
                <footer class="<?php echo esc_attr( implode( ' ', $class ) ); ?>">
					<?php
					do_action( 'ovic_before_footer_content' );
					if ( $query->have_posts() ):
						while ( $query->have_posts() ): $query->the_post();
							if ( self::is_elementor( get_the_ID() ) ) {
								echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( get_the_ID() );
							} else {
								$post_id = get_post( get_the_ID() );
								$content = $post_id->post_content;
								$content = apply_filters( 'the_content', $content );
								$content = str_replace( ']]>', ']]>', $content );
								echo '<div class="container">' . $content . '</div>';
							}
						endwhile;
						wp_reset_postdata();
					endif;
					do_action( 'ovic_after_footer_content' );
					?>
                </footer>
				<?php
				echo apply_filters( 'ovic_filter_content_footer', ob_get_clean(), $footer_content, $class, $footer_options );
			}
		}

		function ovic_shortcodes_custom_css( $css )
		{
			if ( current_theme_supports( 'ovic-footer-builder' ) ) {
				$post_custom_css = array();
				$footer_options  = $this->get_footer_option();
				if ( $footer_options != '' ) {
					$post_custom_css[] = get_post_meta( $footer_options, '_wpb_post_custom_css', true );
					$post_custom_css[] = get_post_meta( $footer_options, '_wpb_shortcodes_custom_css', true );
					$post_custom_css[] = get_post_meta( $footer_options, '_Ovic_Shortcode_custom_css', true );
					$post_custom_css[] = get_post_meta( $footer_options, '_Ovic_VC_Shortcode_Custom_Css', true );
					if ( count( $post_custom_css ) > 0 ) {
						$css .= implode( ' ', $post_custom_css );
					}
				}
			}

			return $css;
		}

		function post_type()
		{
			/* Footer */
			$args = array(
				'labels'              => array(
					'name'               => __( 'Footers', 'ovic-toolkit' ),
					'singular_name'      => __( 'Footers', 'ovic-toolkit' ),
					'add_new'            => __( 'Add New', 'ovic-toolkit' ),
					'add_new_item'       => __( 'Add new footer', 'ovic-toolkit' ),
					'edit_item'          => __( 'Edit footer', 'ovic-toolkit' ),
					'new_item'           => __( 'New footer', 'ovic-toolkit' ),
					'view_item'          => __( 'View footer', 'ovic-toolkit' ),
					'search_items'       => __( 'Search template footer', 'ovic-toolkit' ),
					'not_found'          => __( 'No template items found', 'ovic-toolkit' ),
					'not_found_in_trash' => __( 'No template items found in trash', 'ovic-toolkit' ),
					'parent_item_colon'  => __( 'Parent template item:', 'ovic-toolkit' ),
					'menu_name'          => __( 'Footer Builder', 'ovic-toolkit' ),
				),
				'hierarchical'        => false,
				'description'         => __( 'To Build Template Footer.', 'ovic-toolkit' ),
				'supports'            => array(
					'title',
					'editor',
					'thumbnail',
					'revisions',
				),
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => 'ovic-dashboard',
				'menu_position'       => 4,
				'show_in_nav_menus'   => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'query_var'           => true,
				'can_export'          => true,
				'show_in_rest'        => true,
				'rewrite'             => false,
				'capability_type'     => 'page',
			);
			if ( current_theme_supports( 'ovic-footer-builder' ) ) {
				register_post_type( 'footer', $args );
			}
		}
	}

	new Ovic_Footer_Builder();
}