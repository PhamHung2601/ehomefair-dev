<?php

if( !class_exists('Ovic_Contextual_Help')){
    class  Ovic_Contextual_Help{

        public function __construct(){

            add_action('load-ovic_page_ovic-settings',array( $this,'settings_contextual_help'));
        }

        public function settings_contextual_help(){
            $screen = get_current_screen();

            if ( $screen->id != 'ovic_page_ovic-settings' )
                return;

            $tabs = $this->get_help_tabs();

            $screen->set_help_sidebar(
                '<p><strong>' . sprintf( __( 'For more information:', 'ovic-toolkit' ) . '</strong></p>' .
                        '<p>' . sprintf( __( 'Visit the <a href="%s">documentation</a> on the Kutethemes website.', 'ovic-toolkit' ), esc_url( 'http://help.kutethemes.com/' ) ) ) . '</p>'
                 );

            if( is_array( $tabs ) && count( $tabs ) > 0){
                foreach ( $tabs as $tab ){
                    $screen->add_help_tab($tab );
                }
            }
        }

        public function get_help_tabs(){
            // Page Settings help
            $tabs = array(
                array(
                    'id'	    => 'ovic-settings-general',
                    'title'	    => __( 'General', 'ovic-toolkit' ),
                    'content'	=> '<p>' . __( 'This screen provides the most basic settings for configuring your store. You can set the currency, page templates, and turn <em>Test Mode</em> on and off.', 'ovic-toolkit' ) . '</p>'
                ),
                array(
                    'id'	    => 'ovic-settings-mailchip',
                    'title'	    => __( 'Mailchip', 'ovic-toolkit' ),
                    'content'	=> '<p>' . __( 'Mailchip Settings ...', 'ovic-toolkit' ) . '</p>'
                ),
            );

            return apply_filters('ovic_contextual_help_tabs',$tabs);
        }
    }

    new Ovic_Contextual_Help();
}