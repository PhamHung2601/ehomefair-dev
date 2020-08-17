<?php

if( ! defined ( 'ABSPATH' ) )

    exit;


/*---------->>> Custom Rewrites <<<----------*/



add_filter('rewrite_rules_array', 'wp_insertcustom_rules');

add_filter('query_vars', 'wp_insertcustom_vars');



/*----------*/ /*---------->>> SAVING FORM DATA <<<----------*/ /*----------*/

add_action( 'save_post', 'save_version_meta', 10, 3 );

add_action( 'personal_options_update', 'save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_extra_user_profile_fields' );

add_action( 'user_profile_update_errors', 'mp_validate_extra_profile_fields', 10, 3 );
