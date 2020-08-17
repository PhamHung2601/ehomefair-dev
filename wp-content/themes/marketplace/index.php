<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Marketplace
 */
?>
<?php
get_header();
$term_id = get_queried_object_id();
/* Blog Layout */
$video                = '';
$ovic_blog_layout     = Marketplace_Functions::get_option( 'ovic_sidebar_blog_layout', 'left' );
$ovic_blog_list_style = Marketplace_Functions::get_option( 'ovic_blog_list_style', 'grid' );
$ovic_blog_used_sidebar = Marketplace_Functions::get_option( 'ovic_blog_used_sidebar', 'widget-area' );
$ovic_container_class = array( 'main-container' );
if ( is_single() ) {
	/*Single post layout*/
	$ovic_blog_layout = Marketplace_Functions::get_option( 'ovic_sidebar_single_layout', 'left' );
    $ovic_blog_used_sidebar = Marketplace_Functions::get_option( 'ovic_single_used_sidebar', 'widget-area' );
}
if ( !is_active_sidebar( $ovic_blog_used_sidebar ) ){
    $ovic_blog_layout = 'full';
}
if ( $ovic_blog_layout == 'full' ) {
	$ovic_container_class[] = 'no-sidebar';
} else {
	$ovic_container_class[] = $ovic_blog_layout . '-sidebar';
}
$ovic_content_class   = array();
$ovic_content_class[] = 'main-content ovic_blog';
if ( $ovic_blog_layout == 'full' ) {
	$ovic_content_class[] = 'col-sm-12';
} else {
	$ovic_content_class[] = 'col-lg-9 col-md-8';
}
$ovic_slidebar_class   = array();
$ovic_slidebar_class[] = 'sidebar ovic_sidebar';
if ( $ovic_blog_layout != 'full' ) {
	$ovic_slidebar_class[] = 'col-lg-3 col-md-4';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $ovic_container_class ) ); ?>">
<!--	--><?php //if ( is_search() ) : ?>
<!--        <div class="container">-->
<!--			--><?php //if ( have_posts() ) : ?>
<!--                <h1 class="page-title">--><?php //printf( esc_html__( 'Search Results for: %s', 'marketplace' ), '<span>' . get_search_query() . '</span>' ); ?><!--</h1>-->
<!--			--><?php //endif; ?>
<!--        </div>-->
<!--	--><?php //endif; ?>
    <!-- POST LAYOUT -->
    <div class="container">
		<?php do_action( 'ovic_breadcrumb' ); ?>
<!--        <h1 class="page-title">--><?php //single_post_title(); ?><!--</h1>-->
        <div class="row">
            <div class="<?php echo esc_attr( implode( ' ', $ovic_content_class ) ); ?>">
				<?php
				if ( is_single() ) {
					while ( have_posts() ): the_post();
						do_action( 'ovic_set_post_views', get_the_ID() );
						get_template_part( 'templates/blog/blog', 'single' );
						/*If comments are open or we have at least one comment, load up the comment template.*/
						if ( comments_open() || get_comments_number() ) :
							comments_template();
						endif;
					endwhile;
					wp_reset_postdata();
				} else {
					get_template_part( 'templates/blog/blog', $ovic_blog_list_style );
				} ?>
            </div>
			<?php if ( $ovic_blog_layout != 'full' ): ?>
                <div class="<?php echo esc_attr( implode( ' ', $ovic_slidebar_class ) ); ?>">
					<?php get_sidebar(); ?>
                </div>
			<?php endif; ?>
        </div>
    </div>
</div>
<?php get_footer(); ?>
