<?php
if ( !class_exists( 'Ovic_Meta_Box' ) ) {
	class Ovic_Meta_Box
	{
		public function __construct()
		{
			add_action( 'cmb2_admin_init', array( $this, 'cmb2_admin_init' ) );
		}

		public function cmb2_admin_init()
		{
			$options = $this->get_options();
			if ( !empty( $options ) ) {
				foreach ( $options as $option ) {
					if ( isset( $option['fields'] ) && !empty( $option['fields'] ) ) {
						$cmb_demo = new_cmb2_box( $option );
						foreach ( $option['fields'] as $field ) {
							$cmb_demo->add_field( $field );
						}
					}
				}
			}
		}

		public function get_options()
		{
			$sidebars = $this->get_sidebars();
			$options  = array(
				array(
					'id'           => 'page_options',
					'title'        => esc_html__( 'Page Options', 'ovic-toolkit' ),
					'object_types' => array( 'page' ), // Post type
					'context'      => 'normal',
					'fields'       => array(
						array(
							'name'    => __( 'Page layout', 'ovic-toolkit' ),
							'desc'    => __( 'Layout using for page', 'ovic-toolkit' ),
							'id'      => 'ovic_page_layout',
							'type'    => 'image_select',
							'options' => array(
								'full'  => array( 'title' => 'Full Width', 'alt' => 'Full Width', 'img' => OVIC_TOOLKIT_PLUGIN_URL . 'assets/images/1column.png' ),
								'left'  => array( 'title' => 'Sidebar Left', 'alt' => 'Sidebar Left', 'img' => OVIC_TOOLKIT_PLUGIN_URL . 'assets/images/2cl.png' ),
								'right' => array( 'title' => 'Sidebar Right', 'alt' => 'Sidebar Right', 'img' => OVIC_TOOLKIT_PLUGIN_URL . 'assets/images/2cr.png' ),
							),
							'default' => 'left',
						),
						array(
							'name'             => esc_html__( 'Sidebar for page layout', 'ovic-toolkit' ),
							'id'               => 'ovic_page_used_sidebar',
							'type'             => 'select',
							'show_option_none' => true,
							'options'          => $sidebars,
							'desc'             => esc_html__( 'Setting sidebar in the area sidebar', 'ovic-toolkit' ),
							'dependency'       => array(
								'id'    => 'ovic_page_layout',
								'value' => array( 'left', 'right' ),
							),
						),
						array(
							'name'             => esc_html__( 'Extra Class', 'ovic-toolkit' ),
							'id'               => 'ovic_page_extra_class',
							'type'             => 'text',
							'show_option_none' => true,
							'desc'             => esc_html__( 'Setting Extra Class in page', 'ovic-toolkit' ),
						),
					),
				),
			);

			return apply_filters( 'ovic_registered_metabox_settings', $options );
		}

		public function get_sidebars()
		{
			global $wp_registered_sidebars;
			$sidebars = array();
			foreach ( $wp_registered_sidebars as $sidebar ) {
				$sidebars[$sidebar['id']] = $sidebar['name'];
			}

			return apply_filters( 'ovic_settings_get_sidebar_registered', $sidebars );
		}
	}

	new Ovic_Meta_Box();
}