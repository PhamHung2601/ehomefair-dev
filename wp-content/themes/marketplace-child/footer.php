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

<script type="text/javascript">
  jQuery(document).ready(function(){
  jQuery("#widget_ovic_product_filter-1 h2.widgettitle").click(function(){
    jQuery("#widget_ovic_product_filter-1").toggleClass("collapsed");
  });
});
</script>


<script type="text/javascript">
  /* shop page filter */
  jQuery(document).ready(function(){
  jQuery("#ovic_nav_menu-5 h2.widgettitle").click(function(){
    jQuery("#ovic_nav_menu-5").toggleClass("collapsed");
  });

  jQuery("#ovic_nav_menu-6 h2.widgettitle").click(function(){
    jQuery("#ovic_nav_menu-6").toggleClass("collapsed");
  });

  jQuery("#ovic_nav_menu-7 h2.widgettitle").click(function(){
    jQuery("#ovic_nav_menu-7").toggleClass("collapsed");
  });
});
</script>

<?php wp_footer(); ?>

<script>
jQuery(".logged-in .main-content .page-title, .woocommerce-MyAccount-navigation").click( function(){
    if ( jQuery(".woocommerce-MyAccount-navigation").hasClass("account-collapsed") ) {
        jQuery(".woocommerce-MyAccount-navigation").removeClass("account-collapsed");
    } else {
        jQuery(".woocommerce-MyAccount-navigation").removeClass("account-collapsed");
        jQuery(".woocommerce-MyAccount-navigation").addClass("account-collapsed");    
    }
});

jQuery(window).scroll(function() {    
    var scroll = jQuery(window).scrollTop();
    if (scroll >= 200) {
        jQuery(".logged-in .main-content h2.page-title").addClass("accountmenusticky");
    } else {
        jQuery(".logged-in .main-content h2.page-title").removeClass("accountmenusticky");
    }
});

</script>

</body>
</html>
