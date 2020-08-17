<?php get_header(); ?>
<?php
/* Data MetaBox */
$data_meta = get_post_meta( get_the_ID(), '_custom_page_side_options', true );
/* Data MetaBox */
$ovic_page_layout  = get_post_meta( get_the_ID(), 'ovic_page_layout', true );
$ovic_page_class   = get_post_meta( get_the_ID(), 'ovic_page_extra_class', true );
$ovic_page_sidebar = get_post_meta( get_the_ID(), 'ovic_page_used_sidebar', true );
if ( !is_active_sidebar( $ovic_page_sidebar ) ){
    $ovic_page_layout = 'full';
}
/*Main container class*/
$ovic_main_container_class   = array();
$ovic_main_container_class[] = $ovic_page_class;
$ovic_main_container_class[] = 'main-container';
if ( $ovic_page_layout == 'full' ) {
	$ovic_main_container_class[] = 'no-sidebar';
} else {
	$ovic_main_container_class[] = $ovic_page_layout . '-sidebar';
}
$ovic_main_content_class   = array();
$ovic_main_content_class[] = 'main-content';
if ( $ovic_page_layout == 'full' ) {
	$ovic_main_content_class[] = 'col-sm-12';
} else {
	$ovic_main_content_class[] = 'col-lg-9 col-md-8';
}
$ovic_slidebar_class   = array();
$ovic_slidebar_class[] = 'sidebar';
if ( $ovic_page_layout != 'full' ) {
	$ovic_slidebar_class[] = 'col-lg-3 col-md-4';
}
?>
    <main class="site-main <?php echo esc_attr( implode( ' ', $ovic_main_container_class ) ); ?>">
        <div class="container">
			<?php do_action( 'ovic_breadcrumb' ); ?>
            <div class="row">
                <div class="<?php echo esc_attr( implode( ' ', $ovic_main_content_class ) ); ?>">
                    <h2 class="page-title">
						<?php single_post_title(); ?>
                    </h2>
					<?php
					if ( have_posts() ) {
						while ( have_posts() ) {
							the_post();
							?>
                            <div class="page-main-content">
								<?php
								the_content();
								wp_link_pages( array(
										'before'      => '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'marketplace' ) . '</span>',
										'after'       => '</div>',
										'link_before' => '<span>',
										'link_after'  => '</span>',
										'pagelink'    => '<span class="screen-reader-text">' . esc_html__( 'Page', 'marketplace' ) . ' </span>%',
										'separator'   => '<span class="screen-reader-text">, </span>',
									)
								);
								?>
                            </div>
							<?php
							// If comments are open or we have at least one comment, load up the comment template.
							if ( comments_open() || get_comments_number() ) :
								comments_template();
							endif;
							?>
							<?php
						}
					}
					?>
                </div>
				<?php if ( $ovic_page_layout != "full" ): ?>
                    <div id="widget-area"
                         class="widget-area <?php echo esc_attr( implode( ' ', $ovic_slidebar_class ) ); ?>">
						<?php if ( is_active_sidebar( $ovic_page_sidebar ) ) : ?>
							<?php dynamic_sidebar( $ovic_page_sidebar ); ?>
						<?php endif; ?>
                    </div><!-- .widget-area -->
				<?php endif; ?>
            </div>
        </div>
    </main>
<?php get_footer(); ?>