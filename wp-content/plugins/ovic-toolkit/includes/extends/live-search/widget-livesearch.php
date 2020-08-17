<?php
if ( !class_exists( 'Ovic_Live_Search_Widget' ) ) {
	class Ovic_Live_Search_Widget extends WP_Widget
	{
		public $defaults_atts = array(
			'title'       => '',
			'placeholder' => 'Search...',
		);

		function __construct()
		{
			/* Widget settings. */
			$widget_ops = array( 'classname' => 'live-search-widget', 'description' => esc_html__( 'A widget that displays Live Search Form', 'ovic-toolkit' ) );
			/* Create the widget. */
			parent::__construct( 'live_search_widget', esc_html__( 'Ovic: Live Search', 'ovic-toolkit' ), $widget_ops );
		}

		function widget( $args, $instance )
		{
			extract( $args );
			$instance = wp_parse_args( ( array )$instance, $this->defaults_atts );
			echo balanceTags( $args['before_widget'] );
			if ( !empty( $instance['title'] ) ) {
				echo $args['before_title'] . $instance['title'] . $args['after_title'];
			}
			?>
            <form method="get" action="<?php echo esc_url( home_url( '/' ) ) ?>"
                  class="block-search ovic-live-search-form">
				<?php if ( class_exists( 'WooCommerce' ) ): ?>
                    <input type="hidden" name="post_type" value="product"/>
				<?php endif; ?>
                <div class="search-box results-search">
                    <input autocomplete="off" type="text" class="serchfield txt-livesearch" name="s"
                           value="<?php echo esc_attr( get_search_query() ); ?>"
                           placeholder="<?php echo esc_html( $instance['placeholder'] ); ?>">
                </div>
            </form>
			<?php
			echo balanceTags( $args['after_widget'] );
		}

		function update( $new_instance, $old_instance )
		{
			$instance = $new_instance;

			return $instance;
		}

		function form( $instance )
		{
			$instance = wp_parse_args( ( array )$instance, $this->defaults_atts );
			?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'ovic-toolkit' ); ?></label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                       value="<?php echo esc_html( $instance['title'] ) ?>"/>
            </p>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'placeholder' ) ); ?>"><?php esc_html_e( 'Placeholder Text:', 'ovic-toolkit' ); ?></label>
                <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'placeholder' ) ); ?>"
                       name="<?php echo esc_attr( $this->get_field_name( 'placeholder' ) ); ?>"
                       value="<?php echo esc_html( $instance['placeholder'] ) ?>"/>
            </p>
			<?php
		}
	}
}
add_action( 'widgets_init', 'Ovic_Live_Search_Widget' );
if ( !function_exists( 'Ovic_Live_Search_Widget' ) ) {
	function Ovic_Live_Search_Widget()
	{
		register_widget( 'Ovic_Live_Search_Widget' );
	}
}
