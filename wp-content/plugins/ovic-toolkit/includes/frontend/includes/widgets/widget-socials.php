<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Ovic socials
 *
 * Displays socials widget.
 *
 * @author   Khanh
 * @category Widgets
 * @package  Ovic/Widgets
 * @version  1.0.0
 * @extends  OVIC_Widget
 */
if ( !class_exists( 'Ovic_Socials_Widget' ) ) {
	class Ovic_Socials_Widget extends OVIC_Widget
	{
		/**
		 * Constructor.
		 */
		public function __construct()
		{
			$array_settings           = apply_filters( 'ovic_filter_settings_widget_socials',
				array(
					'title'        => array(
						'type'  => 'text',
						'title' => esc_html__( 'Title', 'ovic-toolkit' ),
					),
					'ovic_socials' => array(
						'type'    => 'checkbox',
						'class'   => 'horizontal',
						'title'   => esc_html__( 'Select Social', 'ovic-toolkit' ),
						'options' => ovic_social_option(),
					),
				)
			);
			$this->widget_cssclass    = 'widget-ovic-socials';
			$this->widget_description = esc_html__( 'Display the customer Socials.', 'ovic-toolkit' );
			$this->widget_id          = 'widget_ovic_socials';
			$this->widget_name        = esc_html__( 'Ovic: Socials', 'ovic-toolkit' );
			$this->settings           = $array_settings;
			parent::__construct();
		}

		/**
		 * Output widget.
		 *
		 * @see WP_Widget
		 *
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance )
		{
			$this->widget_start( $args, $instance );
			$default   = array(
				'socials'  => implode( ',', $instance['ovic_socials'] ),
				'el_class' => '',
			);
			$shortcode = '';
			foreach ( $default as $key => $value ) {
				$shortcode .= ' ' . $key . '="' . $value . '" ';
			}
			echo do_shortcode( '[ovic_socials' . $shortcode . ']' );
			$this->widget_end( $args );
		}
	}
}
/**
 * Register Widgets.
 *
 * @since 2.3.0
 */
function Ovic_Socials_Widget()
{
	register_widget( 'Ovic_Socials_Widget' );
}

add_action( 'widgets_init', 'Ovic_Socials_Widget' );