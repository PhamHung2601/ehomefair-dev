<?php

global $wpdb;
$obb = new MP_Form_Handler();
$current_user = wp_get_current_user();
$table_users  = $wpdb->users;
$table_seller = $wpdb->prefix . "mpsellerinfo";

$myrows = $wpdb->get_results( "SELECT u.*,s.* FROM ".$table_users." u join ".$table_seller." s on s.user_id=u.ID where u.ID='$current_user->ID'");

$user_rows=$wpdb->get_results("select um.meta_key,um.meta_value from $wpdb->usermeta um where um.user_id='$current_user->ID'");
$user_meta=array();

foreach( $user_rows as $value ) {
	$user_meta[ $value->meta_key ] = $value->meta_value;
}
$avatar = $obb->get_user_avatar( $current_user->ID, 'avatar' );

do_action( 'mp_get_wc_account_menu', 'marketplace' );

	echo '<section id="contentForm" class="woocommerce-MyAccount-content wk_profileclass">';
	echo '<form method="post">';

	if ( isset( $avatar[0]->meta_value ) ) {
		echo '<div class="wkmp_profileimg"><img src="'.content_url().'/uploads/'.$avatar[0]->meta_value.'" /></div>';
	}
	else
	{
	echo '<div class="wkmp_profileimg"><img src="'.WK_MARKETPLACE.'assets/images/genric-male.png" /></div>';
	}
	echo '<div class="wkmp_profileinfo">';
	echo '<div class="wkmp_profiledata">';
	echo __('Username', 'marketplace').': ';
	echo $current_user->user_login . '</div>';
	echo '<div class="wkmp_profiledata">';
	echo __('Email', 'marketplace') . ': ';
	echo $current_user->user_email . '</div>';
	echo '<div class="wkmp_profiledata">';
	echo __('First name', 'marketplace').': ';
	echo $current_user->user_firstname . '</div>';
	echo '<div class="wkmp_profiledata">';
	echo __('Last name', 'marketplace').': ';
	echo $current_user->user_lastname . '</div>';
	echo '<div class="wkmp_profiledata">';
	echo __('Display name', 'marketplace').': ';
	echo $current_user->display_name . '</div>';

	echo '<div><a class="button" href="'.get_permalink().'profile/edit" title="' . esc_html__( 'Edit Profile', 'marketplace' ) . '">' . esc_html__( 'Edit', 'marketplace' ) . '</a>&nbsp;&nbsp;<a class="button" href="'.wp_logout_url().'" title="' . esc_html__( 'Logout', 'marketplace' ) . '">' . esc_html( 'Logout', 'marketplace' ) . '</a><br /></div></form>';
	echo '</div>';
	echo '</section>';
