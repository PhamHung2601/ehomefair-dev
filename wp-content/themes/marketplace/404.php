<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package WordPress
 * @subpackage Marketplace
 * @since 1.0
 * @version 1.0
 */
get_header(); ?>
    <div class="main-container">
        <div class="inner-page-banner">
            <div class="container">
                <h1 class="page-title">Error 404</h1>
            </div>
        </div>
        <div class="container">
            <div id="primary" class="content-area">
                <main id="main" class="site-main" role="main">
                    <section class="error-404 not-found">
                        <h1 class="title"><?php echo esc_html__( 'Opps! This page Could Not Be Found!', 'marketplace' ); ?></h1>
                        <p class="subtitle"><?php echo esc_html__( 'Sorry bit the page you are looking for does not exist, have been removed or name changed', 'marketplace' ); ?></p>
                        <!-- .page-content -->
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
                           class="button"><?php echo esc_html__( 'GO TO HOMEPAGE', 'marketplace' ); ?></a>
                    </section><!-- .error-404 -->
                </main><!-- #main -->
            </div><!-- #primary -->
        </div>
    </div>
<?php get_footer();
