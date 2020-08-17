<?php
global $post;
$enable_related   = Marketplace_Functions::get_option( 'ovic_enable_related_post' );
$number_related   = Marketplace_Functions::get_option( 'ovic_related_post_per_page', 6 );
$categories       = get_the_category( $post->ID );
if ( $categories && $enable_related == 1 ) :
	$woo_ls_items = Marketplace_Functions::get_option( 'ovic_related_post_ls_items', 3 );
	$woo_lg_items = Marketplace_Functions::get_option( 'ovic_related_post_lg_items', 3 );
	$woo_md_items = Marketplace_Functions::get_option( 'ovic_related_post_md_items', 3 );
	$woo_sm_items = Marketplace_Functions::get_option( 'ovic_related_post_sm_items', 2 );
	$woo_xs_items = Marketplace_Functions::get_option( 'ovic_related_post_xs_items', 1 );
	$woo_ts_items = Marketplace_Functions::get_option( 'ovic_related_post_ts_items', 1 );
	$atts         = array(
		'owl_loop'     => 'false',
		'owl_ts_items' => $woo_ts_items,
		'owl_xs_items' => $woo_xs_items,
		'owl_sm_items' => $woo_sm_items,
		'owl_md_items' => $woo_md_items,
		'owl_lg_items' => $woo_lg_items,
		'owl_ls_items' => $woo_ls_items,
	);
	$owl_settings = apply_filters( 'ovic_carousel_data_attributes', 'owl_', $atts );
	$category_ids = array();
	foreach ( $categories as $value ) {
		$category_ids[] = $value->term_id;
	}
	$args      = array(
		'category__in'        => $category_ids,
		'post__not_in'        => array( $post->ID ),
		'posts_per_page'      => $number_related,
		'ignore_sticky_posts' => 1,
		'orderby'             => 'rand',
	);
	$new_query = new wp_query( $args );
	if ( $new_query->have_posts() ) : ?>
        <div class="related-post owl-slick" <?php echo esc_attr( $owl_settings ); ?>>
			<?php while ( $new_query->have_posts() ): $new_query->the_post();
				$image_thumb = apply_filters( 'ovic_resize_image', get_post_thumbnail_id(), 350, 250, true, true );
				?>
                <article <?php post_class( 'post-item' ); ?>>
                    <div class="post-inner">
                        <div class="post-thumb">
                            <a class="thumb-link" href="<?php the_permalink(); ?>">
                                <figure><?php echo htmlspecialchars_decode( $image_thumb['img'] ); ?></figure>
                            </a>
                        </div>
                        <div class="post-info">
                            <h2 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            <ul class="post-meta">
                                <li class="author">
                                    <i class="fa fa-user" aria-hidden="true"></i>
                                    <span><?php echo esc_html__( 'By: ', 'marketplace' ) ?></span>
                                    <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) ?>">
										<?php the_author() ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </article>
			<?php endwhile; ?>
        </div>
	<?php endif;
endif;
wp_reset_postdata();