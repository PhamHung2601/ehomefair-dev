<?php
/**
 *
 * Ovic Blog
 *
 */
if ( !class_exists( 'Recent_Blog_Widget' ) ) {
	class Recent_Blog_Widget extends WP_Widget
	{
		function __construct()
		{
			$widget_ops = array(
				'classname'   => 'widget-recent-post',
				'description' => 'Widget post.',
			);
			parent::__construct( 'widget_ovic_post', 'Ovic: Recent Blog', $widget_ops );
		}

		function widget( $args, $instance )
		{
			extract( $args );
			echo $args['before_widget'];
			if ( !empty( $instance['title'] ) ) {
				echo $args['before_title'] . $instance['title'] . $args['after_title'];
			}
			$args_loop = array(
				'post_type'           => 'post',
				'showposts'           => $instance['number'],
				'nopaging'            => 0,
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'order'               => 'DESC',
			);
			if ( $instance['choose_post'] == '0' ) {
				if ( $instance['type_post'] == 'popular' ) {
					$args_loop['cat']      = $instance['category'];
					$args_loop['meta_key'] = 'ovic_post_views_count';
					$args_loop['olderby']  = 'meta_value_num';
				} else {
					$args_loop['cat'] = $instance['category'];
				}
			} else {
				$args_loop['post__in'] = $instance['ids'];
			}
			$loop_posts = new WP_Query( $args_loop );
			if ( $loop_posts->have_posts() ) : ?>
                <div class="ovic-blog">
					<?php while ( $loop_posts->have_posts() ) : $loop_posts->the_post() ?>
                        <article <?php post_class( 'blog-item' ); ?>>
                            <div class="post-thumb">
                                <a href="<?php the_permalink(); ?>">
									<?php
									$image_thumb = apply_filters( 'ovic_resize_image', get_post_thumbnail_id(), 85, 60, true, true );
									echo htmlspecialchars_decode( $image_thumb['img'] );
									?>
                                </a>
                            </div>
                            <div class="post-info">
                                <h4 class="post-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h4>
                                <div class="post-meta">
                                    <a class="author" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) ?>"><?php the_author(); ?></a>
                                    -
                                    <span class="date"><?php echo get_the_date( 'd M Y' ); ?></span>
                                </div>
                            </div>
                        </article>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
                </div>
			<?php else :
				get_template_part( 'content', 'none' );
			endif;
			echo $args['after_widget'];
		}

		function update( $new_instance, $old_instance )
		{
			$instance                = $old_instance;
			$instance['ids']         = $new_instance['ids'];
			$instance['title']       = $new_instance['title'];
			$instance['number']      = $new_instance['number'];
			$instance['choose_post'] = $new_instance['choose_post'];
			$instance['type_post']   = $new_instance['type_post'];
			$instance['category']    = $new_instance['category'];

			return $instance;
		}

		function form( $instance )
		{
			//
			// set defaults
			// -------------------------------------------------
			$instance    = wp_parse_args(
				$instance,
				array(
					'title'       => '',
					'number'      => '4',
					'choose_post' => '0',
					'ids'         => '',
					'type_post'   => '',
					'category'    => '',
				)
			);
			$title_value = $instance['title'];
			$title_field = array(
				'id'    => $this->get_field_name( 'title' ),
				'name'  => $this->get_field_name( 'title' ),
				'type'  => 'text',
				'title' => esc_html__( 'Title', 'marketplace' ),
			);
			echo ovic_add_field( $title_field, $title_value );
			$choose_post_value = $instance['choose_post'];
			$choose_post_field = array(
				'id'         => $this->get_field_name( 'choose_post' ),
				'name'       => $this->get_field_name( 'choose_post' ),
				'type'       => 'select',
				'options'    => array(
					'0' => 'Loop Post',
					'1' => 'Single Post',
				),
				'attributes' => array(
					'data-depend-id' => 'choose_post',
					'style'          => 'width: 100%;',
				),
				'title'      => esc_html__( 'Choose Type Post', 'marketplace' ),
			);
			echo ovic_add_field( $choose_post_field, $choose_post_value );
			$ids_value = $instance['ids'];
			$ids_field = array(
				'id'         => $this->get_field_name( 'ids' ),
				'name'       => $this->get_field_name( 'ids' ),
				'type'       => 'select',
				'options'    => 'posts',
				'query_args' => array(
					'post_type' => 'post',
					'orderby'   => 'post_date',
					'order'     => 'DESC',
				),
				'class'      => 'chosen',
				'attributes' => array(
					'multiple' => 'multiple',
					'style'    => 'width: 100%;',
				),
				'dependency' => array( 'choose_post', '==', '1' ),
				'title'      => esc_html__( 'Search by name Post', 'marketplace' ),
			);
			echo ovic_add_field( $ids_field, $ids_value );
			$category_value = $instance['category'];
			$category_field = array(
				'id'         => $this->get_field_name( 'category' ),
				'name'       => $this->get_field_name( 'category' ),
				'type'       => 'select',
				'options'    => 'categories',
				'query_args' => array(
					'orderby' => 'name',
					'order'   => 'ASC',
				),
				'class'      => 'chosen',
				'attributes' => array(
					'multiple' => 'multiple',
					'style'    => 'width: 100%;',
				),
				'dependency' => array( 'choose_post', '==', '0' ),
				'title'      => esc_html__( 'Category', 'marketplace' ),
			);
			echo ovic_add_field( $category_field, $category_value );
			$type_post_value = $instance['type_post'];
			$type_post_field = array(
				'id'         => $this->get_field_name( 'type_post' ),
				'name'       => $this->get_field_name( 'type_post' ),
				'type'       => 'select',
				'options'    => array(
					'latest'  => 'Latest Post',
					'popular' => 'Popular Post',
				),
				'attributes' => array(
					'style' => 'width: 100%;',
				),
				'dependency' => array( 'choose_post', '==', '0' ),
				'title'      => esc_html__( 'Sort By', 'marketplace' ),
			);
			echo ovic_add_field( $type_post_field, $type_post_value );
			$number_value = $instance['number'];
			$number_field = array(
				'id'         => $this->get_field_name( 'number' ),
				'name'       => $this->get_field_name( 'number' ),
				'type'       => 'number',
				'attributes' => array(
					'style' => 'width: 100%;',
				),
				'dependency' => array( 'choose_post', '==', '0' ),
				'title'      => esc_html__( 'Number Post', 'marketplace' ),
			);
			echo ovic_add_field( $number_field, $number_value );
		}
	}
}
if ( !function_exists( 'Recent_Blog_Widget_init' ) ) {
	function Recent_Blog_Widget_init()
	{
		register_widget( 'Recent_Blog_Widget' );
	}

	add_action( 'widgets_init', 'Recent_Blog_Widget_init', 2 );
}