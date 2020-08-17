<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Appliances
 * @since 1.0
 * @version 1.0
 */
?>
    <!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="profile" href="http://gmpg.org/xfn/11">
		<?php wp_head(); ?>
    </head>
<body <?php body_class(); ?>>
<?php
$header_class     = array( 'header style1' );
$header_class[]   = apply_filters( 'marketplace_header_class', '' );
$header_attribute = apply_filters( 'marketplace_header_attribute', '' );
?>
<?php do_action( 'marketplace_before_header' ); ?>
    <header id="header"
            class="<?php echo esc_attr( implode( ' ', $header_class ) ); ?>"
		<?php echo esc_attr( $header_attribute ); ?>>
		<?php
		/**
		 * Functions hooked into marketplace_footer action
		 *
		 * @hooked marketplace_header_top                   - 10
		 * @hooked marketplace_header_middle                - 20
		 * @hooked marketplace_header_nav                   - 30
		 */
		do_action( 'marketplace_header' ); ?>
    </header>
<?php do_action( 'marketplace_after_header' ); ?>