<?php
do_action( 'marketplace_before_single_blog_content' );
remove_action( 'marketplace_post_info_content', 'marketplace_post_content', 20 );
remove_action( 'marketplace_post_info_content', 'marketplace_post_readmore', 30 );
remove_action( 'marketplace_post_info_content', 'marketplace_post_meta', 40 );
add_action( 'marketplace_post_info_content', 'marketplace_post_single_content', 20 );
add_action( 'marketplace_post_info_content', 'marketplace_post_tags', 30 );
?>
    <article <?php post_class( 'post-item post-single' ); ?>>
		<?php
		/**
		 * Functions hooked into marketplace_single_post_content action
		 *
		 * @hooked marketplace_post_thumbnail          - 10
		 * @hooked marketplace_post_info               - 20
		 * @hooked marketplace_post_single_author      - 30
		 */
		do_action( 'marketplace_single_post_content' ); ?>
    </article>
<?php
add_action( 'marketplace_post_info_content', 'marketplace_post_content', 20 );
add_action( 'marketplace_post_info_content', 'marketplace_post_readmore', 30 );
add_action( 'marketplace_post_info_content', 'marketplace_post_meta', 40 );
remove_action( 'marketplace_post_info_content', 'marketplace_post_single_content', 20 );
remove_action( 'marketplace_post_info_content', 'marketplace_post_tags', 30 );
do_action( 'marketplace_after_single_blog_content' );
get_template_part( 'templates/blog/blog', 'related' );