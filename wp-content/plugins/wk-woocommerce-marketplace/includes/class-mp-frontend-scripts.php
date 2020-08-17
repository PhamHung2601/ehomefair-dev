<?php
/**
 * Handle frontend forms
 *
 * @class    MP_Frontend_Scripts
 * @version  4.7.1
 * @package  Marketplace/Classes/
 * @category Class
 * @author   webkul
 */
class MP_Frontend_Scripts
{

    function __construct() 
    {
        add_action('wp_enqueue_scripts', array( $this, 'wkmp_add_marketplace_style_script' ));
    }
    
    public function wkmp_add_marketplace_style_script() 
    {
        wp_register_style('marketplace-style', WK_MARKETPLACE . 'style.css', '', MP_SCRIPT_VERSION);
    
        wp_enqueue_style('marketplace-style');
        wp_register_script('jquery', '//code.jquery.com/jquery-2.2.4.min.js');
        wp_enqueue_script('jquery');

        if (null !== get_query_var('main_page') && get_query_var('main_page') == get_option('mp_dashboard', 'dashboard') ) {
            wp_enqueue_script('google_chart', '//www.google.com/jsapi');

            wp_enqueue_script('mp_chart_script', '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js');

            wp_register_script('mp-chart-js', WK_MARKETPLACE . '/assets/js/chart_script.js');

            wp_enqueue_script('mp-chart-js');

            wp_localize_script(
                'mp-chart-js',
                'mp_chart_js',
                array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('ajaxnonce'),
                )
            );
        }
    }
}
new MP_Frontend_Scripts();
