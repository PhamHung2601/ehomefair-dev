<?php
get_post_format();
if ( have_posts() ) : ?>
	<?php do_action( 'marketplace_before_blog_content' ); ?>
    <div class="content-post">
		<?php while ( have_posts() ) : the_post();
			remove_action( 'marketplace_post_info_content', 'marketplace_post_content', 20 );
			remove_action( 'marketplace_post_info_content', 'marketplace_post_readmore', 30 );
			add_action( 'marketplace_post_info_content', 'marketplace_post_single_content', 50 );
			?>
            <article <?php post_class( 'post-item' ); ?>>
				<?php
				/**
				 * Functions hooked into marketplace_standard_post_content action
				 *
				 * @hooked marketplace_post_thumbnail          - 10
				 * @hooked marketplace_post_info               - 20
				 */
				do_action( 'marketplace_standard_post_content' ); ?>
            </article>
			<?php
			add_action( 'marketplace_post_info_content', 'marketplace_post_content', 20 );
			add_action( 'marketplace_post_info_content', 'marketplace_post_readmore', 30 );
			remove_action( 'marketplace_post_info_content', 'marketplace_post_single_content', 50 );
		endwhile;
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
endif; ?>