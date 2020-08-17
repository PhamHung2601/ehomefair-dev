<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Abstract Widget Class
 *
 * @author   Khanh
 * @category Widgets
 * @package  Ovic/Abstracts
 * @version  2.5.0
 * @extends  WP_Widget
 */
if ( ! class_exists( 'OVIC_Widget' ) ) {
	abstract class OVIC_Widget extends WP_Widget
	{
		/**
		 * CSS class.
		 *
		 * @var string
		 */
		public $widget_cssclass;
		/**
		 * Widget description.
		 *
		 * @var string
		 */
		public $widget_description;
		/**
		 * Widget ID.
		 *
		 * @var string
		 */
		public $widget_id;
		/**
		 * Widget name.
		 *
		 * @var string
		 */
		public $widget_name;
		/**
		 * Settings.
		 *
		 * @var array
		 */
		public $settings;

		/**
		 * Constructor.
		 */
		public function __construct()
		{
			$widget_ops = array(
				'classname'                   => $this->widget_cssclass,
				'description'                 => $this->widget_description,
				'customize_selective_refresh' => true,
			);
			parent::__construct( $this->widget_id, $this->widget_name, $widget_ops );
			add_action( 'save_post', array( $this, 'flush_widget_cache' ) );
			add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
			add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
		}

		/**
		 * Get cached widget.
		 *
		 * @param  array  $args  Arguments.
		 *
		 * @return bool true if the widget is cached otherwise false
		 */
		public function get_cached_widget( $args )
		{
			// Don't get cache if widget_id doesn't exists.
			if ( empty( $args['widget_id'] ) ) {
				return false;
			}

			$cache = wp_cache_get( $this->get_widget_id_for_cache( $this->widget_id ), 'widget' );

			if ( ! is_array( $cache ) ) {
				$cache = array();
			}

			if ( isset( $cache[ $this->get_widget_id_for_cache( $args['widget_id'] ) ] ) ) {
				echo $cache[ $this->get_widget_id_for_cache( $args['widget_id'] ) ]; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

				return true;
			}

			return false;
		}

		/**
		 * Cache the widget.
		 *
		 * @param  array  $args  Arguments.
		 * @param  string  $content  Content.
		 *
		 * @return string the content that was cached
		 */
		public function cache_widget( $args, $content )
		{
			// Don't set any cache if widget_id doesn't exist.
			if ( empty( $args['widget_id'] ) ) {
				return $content;
			}

			$cache = wp_cache_get( $this->get_widget_id_for_cache( $this->widget_id ), 'widget' );

			if ( ! is_array( $cache ) ) {
				$cache = array();
			}

			$cache[ $this->get_widget_id_for_cache( $args['widget_id'] ) ] = $content;

			wp_cache_set( $this->get_widget_id_for_cache( $this->widget_id ), $cache, 'widget' );

			return $content;
		}

		/**
		 * Flush the cache.
		 */
		public function flush_widget_cache()
		{
			foreach ( array( 'https', 'http' ) as $scheme ) {
				wp_cache_delete( $this->get_widget_id_for_cache( $this->widget_id, $scheme ), 'widget' );
			}
		}

		/**
		 * Get this widgets title.
		 *
		 * @param  array  $instance  Array of instance options.
		 *
		 * @return string
		 */
		protected function get_instance_title( $instance )
		{
			if ( isset( $instance['title'] ) ) {
				return $instance['title'];
			}

			if ( isset( $this->settings, $this->settings['title'], $this->settings['title']['std'] ) ) {
				return $this->settings['title']['std'];
			}

			return '';
		}

		/**
		 * Output the html at the start of a widget.
		 *
		 * @param  array  $args  Arguments.
		 * @param  array  $instance  Instance.
		 */
		public function widget_start( $args, $instance )
		{
			echo $args['before_widget']; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

			$title = apply_filters( 'widget_title', $this->get_instance_title( $instance ), $instance, $this->id_base );

			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title']; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
			}
		}

		/**
		 * Output the html at the end of a widget.
		 *
		 * @param  array  $args  Arguments.
		 */
		public function widget_end( $args )
		{
			echo $args['after_widget']; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Updates a particular instance of a widget.
		 *
		 * @param  array  $new_instance
		 * @param  array  $old_instance
		 *
		 * @return array
		 * @see    WP_Widget->update
		 */
		public function update( $new_instance, $old_instance )
		{
			$instance = $old_instance;

			if ( empty( $this->settings ) ) {
				return $instance;
			}

			// Loop settings and get values to save.
			foreach ( $this->settings as $key => $setting ) {
				if ( ! isset( $setting['type'] ) ) {
					continue;
				}
				$id               = ! is_numeric( $key ) ? $key : $setting['id'];
				$instance[ $key ] = ! empty( $new_instance[ $id ] ) ? $new_instance[ $id ] : null;
			}

			$this->flush_widget_cache();

			return $instance;
		}

		/**
		 * Outputs the settings update form.
		 *
		 * @param  array  $instance
		 *
		 * @see   WP_Widget->form
		 *
		 */
		public function form( $instance )
		{
			if ( empty( $this->settings ) ) {
				return;
			}

			foreach ( $this->settings as $key => $setting ) {
				$default = isset( $setting['default'] ) ? $setting['default'] : '';
				$value   = isset( $instance[ $key ] ) ? $instance[ $key ] : $default;
				$field   = array(
					'id'   => $this->get_field_name( $key ),
					'name' => $this->get_field_name( $key ),
				);
				$field   = array_merge( $field, $setting );

				echo ovic_add_field( $field, $value );
			}
		}

		/**
		 * Get current page URL with various filtering props supported by WC.
		 *
		 * @return string
		 * @since  3.3.0
		 */
		protected function get_current_page_url()
		{
			if ( ! class_exists( 'WooCommerce' ) ) {
				return '';
			}
			if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
				$link = home_url();
			} elseif ( is_shop() ) {
				$link = get_permalink( wc_get_page_id( 'shop' ) );
			} elseif ( is_product_category() ) {
				$link = get_term_link( get_query_var( 'product_cat' ), 'product_cat' );
			} elseif ( is_product_tag() ) {
				$link = get_term_link( get_query_var( 'product_tag' ), 'product_tag' );
			} else {
				$queried_object = get_queried_object();
				$link           = get_term_link( $queried_object->slug, $queried_object->taxonomy );
			}

			// Min/Max.
			if ( isset( $_GET['min_price'] ) ) {
				$link = add_query_arg( 'min_price', wc_clean( wp_unslash( $_GET['min_price'] ) ), $link );
			}

			if ( isset( $_GET['max_price'] ) ) {
				$link = add_query_arg( 'max_price', wc_clean( wp_unslash( $_GET['max_price'] ) ), $link );
			}

			// Order by.
			if ( isset( $_GET['orderby'] ) ) {
				$link = add_query_arg( 'orderby', wc_clean( wp_unslash( $_GET['orderby'] ) ), $link );
			}

			/**
			 * Search Arg.
			 * To support quote characters, first they are decoded from &quot; entities, then URL encoded.
			 */
			if ( get_search_query() ) {
				$link = add_query_arg( 's', rawurlencode( htmlspecialchars_decode( get_search_query() ) ), $link );
			}

			// Post Type Arg.
			if ( isset( $_GET['post_type'] ) ) {
				$link = add_query_arg( 'post_type', wc_clean( wp_unslash( $_GET['post_type'] ) ), $link );

				// Prevent post type and page id when pretty permalinks are disabled.
				if ( is_shop() ) {
					$link = remove_query_arg( 'page_id', $link );
				}
			}

			// Min Rating Arg.
			if ( isset( $_GET['rating_filter'] ) ) {
				$link = add_query_arg( 'rating_filter', wc_clean( wp_unslash( $_GET['rating_filter'] ) ), $link );
			}

			// All current filters.
			if ( $_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes() ) { // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found, WordPress.CodeAnalysis.AssignmentInCondition.Found
				foreach ( $_chosen_attributes as $name => $data ) {
					$filter_name = wc_attribute_taxonomy_slug( $name );
					if ( ! empty( $data['terms'] ) ) {
						$link = add_query_arg( 'filter_' . $filter_name, implode( ',', $data['terms'] ), $link );
					}
					if ( 'or' === $data['query_type'] ) {
						$link = add_query_arg( 'query_type_' . $filter_name, 'or', $link );
					}
				}
			}

			return apply_filters( 'ovic_widget_get_current_page_url', $link, $this );
		}

		/**
		 * Get widget id plus scheme/protocol to prevent serving mixed content from (persistently) cached widgets.
		 *
		 * @param  string  $widget_id  Id of the cached widget.
		 * @param  string  $scheme  Scheme for the widget id.
		 *
		 * @return string            Widget id including scheme/protocol.
		 * @since  3.4.0
		 */
		protected function get_widget_id_for_cache( $widget_id, $scheme = '' )
		{
			if ( $scheme ) {
				$widget_id_for_cache = $widget_id . '-' . $scheme;
			} else {
				$widget_id_for_cache = $widget_id . '-' . ( is_ssl() ? 'https' : 'http' );
			}

			return apply_filters( 'ovic_cached_widget_id', $widget_id_for_cache );
		}
	}
}
