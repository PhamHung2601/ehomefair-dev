<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Ovic Mailchimp
 *
 * Displays Mailchimp widget.
 *
 * @author   Khanh
 * @category Widgets
 * @package  Ovic/Widgets
 * @version  1.0.0
 * @extends  OVIC_Widget
 */
if ( !class_exists( 'Ovic_Mailchimp_Widget' ) ) {
	class Ovic_Mailchimp_Widget extends OVIC_Widget
	{
		/**
		 * Constructor.
		 */
		public function __construct()
		{
			$array_settings           = apply_filters( 'ovic_filter_settings_widget_mailchimp',
				array(
					'title'       => array(
						'type'  => 'text',
						'title' => esc_html__( 'Title', 'ovic-toolkit' ),
					),
					'description' => array(
						'type'  => 'textarea',
						'title' => esc_html__( 'Description:', 'ovic-toolkit' ),
					),
					'background'  => array(
						'type'  => 'image',
						'title' => esc_html__( 'Background:', 'ovic-toolkit' ),
					),
					'show_list'   => array(
						'type'       => 'select',
						'title'      => esc_html__( 'Show Mailchimp List', 'ovic-toolkit' ),
						'options'    => array(
							'yes' => esc_html__( 'Yes', 'ovic-toolkit' ),
							'no'  => esc_html__( 'no', 'ovic-toolkit' ),
						),
						'default'    => 'no',
						'attributes' => array(
							'style' => 'width: 100%',
						),
					),
					'field_name'  => array(
						'type'       => 'select',
						'title'      => esc_html__( 'Show Field Name', 'ovic-toolkit' ),
						'options'    => array(
							'yes' => esc_html__( 'Yes', 'ovic-toolkit' ),
							'no'  => esc_html__( 'no', 'ovic-toolkit' ),
						),
						'default'    => 'no',
						'attributes' => array(
							'data-depend-id' => 'field_name',
							'style'          => 'width: 100%',
						),
					),
					'fname_text'  => array(
						'type'       => 'text',
						'default'    => esc_html__( 'First Name', 'ovic-toolkit' ),
						'dependency' => array( 'field_name', '==', 'yes' ),
						'title'      => esc_html__( 'First Name Text:', 'ovic-toolkit' ),
					),
					'lname_text'  => array(
						'type'       => 'text',
						'default'    => esc_html__( 'Last Name', 'ovic-toolkit' ),
						'dependency' => array( 'field_name', '==', 'yes' ),
						'title'      => esc_html__( 'Last Name Text:', 'ovic-toolkit' ),
					),
					'placeholder' => array(
						'type'    => 'text',
						'title'   => esc_html__( 'Placeholder Text:', 'ovic-toolkit' ),
						'default' => esc_html__( 'Your email letter', 'ovic-toolkit' ),
					),
					'button_text' => array(
						'type'    => 'text',
						'title'   => esc_html__( 'Button Text:', 'ovic-toolkit' ),
						'default' => esc_html__( 'Subscribe', 'ovic-toolkit' ),
					),
				)
			);
			$this->widget_cssclass    = 'widget-ovic-mailchimp';
			$this->widget_description = esc_html__( 'Display the customer Newsletter.', 'ovic-toolkit' );
			$this->widget_id          = 'widget_ovic_mailchimp';
			$this->widget_name        = esc_html__( 'Ovic: Newsletter', 'ovic-toolkit' );
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
			$style = '';
			if ( $instance['background'] != '' ) {
				$style = 'style="background-image: url(' . wp_get_attachment_image_url( $instance['background'], 'full' ) . ')"';
			}
			$default   = array(
				'show_list'   => $instance['show_list'],
				'field_name'  => $instance['field_name'],
				'fname_text'  => $instance['fname_text'],
				'lname_text'  => $instance['lname_text'],
				'placeholder' => $instance['placeholder'],
				'button_text' => $instance['button_text'],
			);
			$shortcode = '';
			foreach ( $default as $key => $value ) {
				$shortcode .= ' ' . $key . '="' . $value . '" ';
			}
			ob_start();
			?>
            <div class="widget-form-wrap" <?php echo $style; ?>>
				<?php
				if ( $instance['description'] != '' ) {
					echo '<p class="desc">' . wp_specialchars_decode( $instance['description'] ) . '</p>';
				}
				?>
				<?php echo do_shortcode( '[ovic_mailchimp' . $shortcode . ']' ); ?>
            </div>
			<?php
			echo apply_filters( 'ovic_filter_widget_newsletter', ob_get_clean(), $instance );
			$this->widget_end( $args );
		}
	}
}
add_action( 'widgets_init', 'Ovic_Mailchimp_Widget' );
if ( !function_exists( 'Ovic_Mailchimp_Widget' ) ) {
	function Ovic_Mailchimp_Widget()
	{
		register_widget( 'Ovic_Mailchimp_Widget' );
	}
}