<?php
$ovic_blog_used_sidebar = Marketplace_Functions::get_option( 'ovic_blog_used_sidebar', 'widget-area' );
if ( is_single() ) {
	$ovic_blog_used_sidebar = Marketplace_Functions::get_option( 'ovic_single_used_sidebar', 'widget-area' );
}
?>
<?php if ( is_active_sidebar( $ovic_blog_used_sidebar ) ) : ?>
    <div id="widget-area" class="widget-area sidebar-blog">
		<?php dynamic_sidebar( $ovic_blog_used_sidebar ); ?>
    </div><!-- .widget-area -->
<?php endif; ?>