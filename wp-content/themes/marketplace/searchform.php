<?php
/**
 * Template for displaying search forms in Marketplace
 *
 * @package WordPress
 * @subpackage Marketplace
 * @since 1.0
 * @version 1.0
 */
?>

<?php $unique_id = esc_attr( uniqid( 'search-form-' ) ); ?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <input type="search" id="<?php echo esc_attr( $unique_id ); ?>" class="search-field"
           placeholder="<?php echo esc_attr_x( 'Your search here&hellip;', 'placeholder', 'marketplace' ); ?>"
           value="<?php echo get_search_query(); ?>" name="s"/>
    <button type="submit" class="search-submit"><span class="fa fa-search" aria-hidden="true"></span></button>
</form>