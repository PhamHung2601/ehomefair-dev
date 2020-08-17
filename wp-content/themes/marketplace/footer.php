<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Marketplace
 * @since 1.0
 * @version 1.0
 */
?>
<?php
$footer_class     = array( 'footer style1' );
$footer_class[]   = apply_filters( 'marketplace_footer_class', '' );
$footer_attribute = apply_filters( 'marketplace_footer_attribute', '' );
?>
<?php do_action( 'marketplace_before_footer' ); ?>
<footer class="<?php echo esc_attr( implode( ' ', $footer_class ) ); ?>" <?php echo esc_attr( $footer_attribute ); ?>>
	<?php
	/**
	 * Functions hooked into marketplace_footer action
	 *
	 * @hooked marketplace_footer_top                - 10
	 * @hooked marketplace_footer_middle             - 20
	 * @hooked marketplace_footer_bottom             - 30
	 */
	do_action( 'marketplace_footer' ); ?>
</footer>
<?php do_action( 'marketplace_after_footer' ); ?>
<a href="#" class="backtotop">
    <i class="fa fa-angle-up"></i>
</a>
<?php wp_footer(); ?>
</body>
</html>
