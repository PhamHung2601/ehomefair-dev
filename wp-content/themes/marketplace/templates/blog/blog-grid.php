<?php
// Custom columns
$classes[] = 'post-item';
$classes[] = 'col-bg-' . Marketplace_Functions::get_option( 'ovic_blog_bg_items', 12 );
$classes[] = 'col-lg-' . Marketplace_Functions::get_option( 'ovic_blog_lg_items', 12 );
$classes[] = 'col-md-' . Marketplace_Functions::get_option( 'ovic_blog_md_items', 12 );
$classes[] = 'col-sm-' . Marketplace_Functions::get_option( 'ovic_blog_sm_items', 12 );
$classes[] = 'col-xs-' . Marketplace_Functions::get_option( 'ovic_blog_xs_items', 12 );
$classes[] = 'col-ts-' . Marketplace_Functions::get_option( 'ovic_blog_ts_items', 12 );
$classes[] = apply_filters( 'marketplace_blog_content_class', '' );
if ( have_posts() ) : ?>
	<?php do_action( 'marketplace_before_blog_content' ); ?>
    <div class="row blog-grid content-post auto-clear">
		<?php while ( have_posts() ) : the_post(); ?>
            <article <?php post_class( $classes ); ?>>
				<?php
				/**
				 * Functions hooked into marketplace_post_content action
				 *
				 * @hooked marketplace_post_thumbnail          - 10
				 * @hooked marketplace_post_info               - 20
				 */
				do_action( 'marketplace_post_content' ); ?>
            </article>
		<?php endwhile;
		wp_reset_postdata(); ?>
    </div>
	<?php
	/**
	 * Functions hooked into marketplace_after_blog_content action
	 *
	 * @hooked marketplace_paging_nav               - 10
	 */
	do_action( 'marketplace_after_blog_content' ); ?>
<?php else :
	get_template_part( 'content', 'none' );
endif;