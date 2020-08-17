<?php
/**
 *
 * Ovic Product Filter
 *
 */
if ( !class_exists( 'Product_Filter_Widget' ) ) {
	class Product_Filter_Widget extends WP_Widget
	{
		function __construct()
		{
			$widget_ops = array(
				'classname'   => 'ovic_product_filter',
				'description' => 'Widget Product Filter.',
			);
			parent::__construct( 'widget_ovic_product_filter', 'Ovic: Product Filter', $widget_ops );
		}

		function widget( $args, $instance )
		{
			extract( $args );
			echo $args['before_widget'];
			if ( !empty( $instance['title'] ) ) {
				echo $args['before_title'] . $instance['title'] . $args['after_title'];
			} ?>
            <div class="filter-content">
				<?php
				if ( isset( $instance['categories_settings']['enable'] ) && $instance['categories_settings']['enable'] == 1 ) {
					$setting = array(
						'title'              => $instance['categories_settings']['title'],
						'count'              => isset( $instance['categories_settings']['count'] ) ? $instance['categories_settings']['count'] : 0,
						'show_children_only' => 1,
					);
					the_widget( 'WC_Widget_Product_Categories', $setting );
				}
				?>
				<?php
				if ( isset( $instance['price_settings']['enable'] ) && $instance['price_settings']['enable'] == 1 ) {
					$setting = array(
						'title' => $instance['price_settings']['title'],
					);
					the_widget( 'WC_Widget_Price_Filter', $setting );
				}
				?>
				<?php
				foreach ( $instance['attribute_settings'] as $key => $settings ) {
					$enable               = isset( $settings['enable'] ) ? $settings['enable'] : 0;
					$setting              = $settings;
					$setting['attribute'] = $key;
					if ( $enable == 1 ) {
						the_widget( 'Ovic_Attribute_Product_Widget', $setting );
					}
				}
				$setting = array(
					'title' => '',
				);
				the_widget( 'WC_Widget_Layered_Nav_Filters', $setting );
				?>
            </div>
			<?php
			echo $args['after_widget'];
		}

		function update( $new_instance, $old_instance )
		{
			$instance                        = $old_instance;
			$instance['title']               = $new_instance['title'];
			$instance['attribute_settings']  = $new_instance['attribute_settings'];
			$instance['categories_settings'] = $new_instance['categories_settings'];
			$instance['price_settings']      = $new_instance['price_settings'];

			return $instance;
		}

		function form( $instance )
		{
			$attribute_taxonomies = wc_get_attribute_taxonomies();
			$instance             = wp_parse_args(
				$instance,
				array(
					'title'               => '',
					'attribute_settings'  => array(),
					'categories_settings' => array(
						'title'  => __( 'Categories', 'marketplace' ),
						'enable' => 1,
						'count'  => 1,
					),
					'price_settings'      => array(
						'title'  => __( 'Price', 'marketplace' ),
						'enable' => 1,
					),
				)
			);
			extract( $instance );
			?>
            <p>
				<?php esc_html_e( 'Title:', 'marketplace' ); ?>
                <input class="widefat" type="text" name="<?php echo $this->get_field_name( 'title' ); ?>"
                       id="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>">
            </p>
            <div class="item-group">
                <p class="head"><strong><?php esc_html_e( 'Categories', 'marketplace' ); ?></strong></p>
                <div class="content">
                    <p>
                        <input type="checkbox" <?php checked( isset( $categories_settings['enable'] ), 1 ); ?>
                               name="<?php echo $this->get_field_name( 'categories_settings' ); ?>[enable]" value="1">
						<?php esc_html_e( 'Enable', 'marketplace' ); ?>
                    </p>
                    <p>
						<?php esc_html_e( 'Title:', 'marketplace' ); ?>
                        <input class="widefat" type="text"
                               name="<?php echo $this->get_field_name( 'categories_settings' ); ?>[title]"
                               value="<?php echo esc_html( $categories_settings['title'] ); ?>">
                    </p>
                    <p>
                        <input type="checkbox" <?php checked( isset( $categories_settings['count'] ), 1 ); ?>
                               name="<?php echo $this->get_field_name( 'categories_settings' ); ?>[count]" value="1">
						<?php esc_html_e( 'Show product counts', 'marketplace' ); ?>
                    </p>
                </div>
            </div>
            <div class="item-group">
                <p class="head"><strong><?php esc_html_e( 'Price', 'marketplace' ); ?></strong></p>
                <div class="content">
                    <p>
                        <input type="checkbox" <?php checked( isset( $price_settings['enable'] ), 1 ); ?>
                               name="<?php echo $this->get_field_name( 'price_settings' ); ?>[enable]" value="1">
						<?php esc_html_e( 'Enable', 'marketplace' ); ?>
                    </p>
                    <p>
						<?php esc_html_e( 'Title:', 'marketplace' ); ?>
                        <input class="widefat" type="text"
                               name="<?php echo $this->get_field_name( 'price_settings' ); ?>[title]"
                               value="<?php echo esc_html( $price_settings['title'] ); ?>">
                    </p>
                </div>
            </div>
			<?php
			$attribute_settings = $instance['attribute_settings'];
			foreach ( $attribute_taxonomies as $attribute ) {
				$enable       = isset( $attribute_settings[$attribute->attribute_name]['enable'] ) ? $attribute_settings[$attribute->attribute_name]['enable'] : 0;
				$title        = isset( $attribute_settings[$attribute->attribute_name]['title'] ) ? $attribute_settings[$attribute->attribute_name]['title'] : '';
				$display_type = isset( $attribute_settings[$attribute->attribute_name]['display_type'] ) ? $attribute_settings[$attribute->attribute_name]['display_type'] : 'list';
				$query_type   = isset( $attribute_settings[$attribute->attribute_name]['query_type'] ) ? $attribute_settings[$attribute->attribute_name]['query_type'] : 'AND';
				?>

                <div class="item-group">
                    <p class="head"><strong><?php echo $attribute->attribute_label; ?></strong></p>
                    <div class="content">
                        <p>
                            <input type="checkbox" <?php checked( $enable, 1 ); ?>
                                   name="<?php echo $this->get_field_name( 'attribute_settings' ); ?>[<?php echo $attribute->attribute_name; ?>][enable]"
                                   value="1">
							<?php esc_html_e( 'Enable', 'marketplace' ); ?>
                        </p>
                        <p>
							<?php esc_html_e( 'Custom Attribute name:', 'marketplace' ); ?>
                            <input class="widefat" type="text"
                                   name="<?php echo $this->get_field_name( 'attribute_settings' ); ?>[<?php echo $attribute->attribute_name; ?>][title]"
                                   value="<?php echo esc_html( $title ); ?>">
                        </p>
                        <p>
							<?php esc_html_e( 'Display Type:', 'marketplace' ); ?>
                            <select class="widefat"
                                    name="<?php echo $this->get_field_name( 'attribute_settings' ); ?>[<?php echo $attribute->attribute_name; ?>][display_type]">
                                <option <?php if ( $display_type == 'list' ): ?> selected <?php endif; ?>
                                        value="list"><?php esc_html_e( 'List', 'marketplace' ); ?></option>
                                <option <?php if ( $display_type == 'dropdown' ): ?> selected <?php endif; ?>
                                        value="dropdown"><?php esc_html_e( 'Dropdown', 'marketplace' ); ?></option>
                                <option <?php if ( $display_type == 'inline' ): ?> selected <?php endif; ?>
                                        value="inline"><?php esc_html_e( 'Inline', 'marketplace' ); ?></option>
                                <option <?php if ( $display_type == 'color' ): ?> selected <?php endif; ?>
                                        value="color"><?php esc_html_e( 'Color', 'marketplace' ); ?></option>
                            </select>
                        </p>
                        <p>
							<?php esc_html_e( 'Query type:', 'marketplace' ); ?>
                            <select class="widefat"
                                    name="<?php echo $this->get_field_name( 'attribute_settings' ); ?>[<?php echo $attribute->attribute_name; ?>][query_type]">
                                <option <?php if ( $query_type == 'AND' ): ?> selected <?php endif; ?>
                                        value="AND"><?php esc_html_e( 'AND', 'marketplace' ); ?></option>
                                <option <?php if ( $query_type == 'OR' ): ?> selected <?php endif; ?>
                                        value="OR"><?php esc_html_e( 'OR', 'marketplace' ); ?></option>
                            </select>
                        </p>
                    </div>
                </div>
				<?php
			}
		}
	}
}
if ( !function_exists( 'Product_Filter_Widget_init' ) ) {
	function Product_Filter_Widget_init()
	{
		register_widget( 'Product_Filter_Widget' );
	}

	add_action( 'widgets_init', 'Product_Filter_Widget_init', 2 );
}