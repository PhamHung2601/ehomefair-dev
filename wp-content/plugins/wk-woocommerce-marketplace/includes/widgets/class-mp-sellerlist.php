<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class MP_Widget_Seller_list extends WP_Widget {

	public function __construct() {

        parent::__construct(
            'mp_marketplace-seller-widget',
            __( 'Marketplace Seller list', 'marketplace' ),
            array(
                'classname'   => 'mp_marketplace_list',
                'description' => __('Display seller list.', 'marketplace' )
                )
        );

    }
	 function widget ( $args, $instance ) {
	 	extract( $args );

	 	global $wpdb;

	 	$wpmp_obj = new MP_Form_Handler();

	 	$usermeta = $wpdb->usermeta;

	 	$users = $wpdb->users;

	 	$sellerInfo = $wpdb->prefix.'mpsellerinfo';

	 	$userNo= intval( $instance['noOfUsers'], 10 );

	 	$no = 0;

		$check_value = $instance['check_value'];

		$value = $instance['value'];

	 	$sellers = "SELECT user_id FROM $sellerInfo WHERE seller_value='seller'";

	 	$page_id = $wpmp_obj->get_page_id(get_option('wkmp_seller_page_title'));

	 	$current_user = get_current_user_id();

	 	if( $id = $wpdb->get_results( $sellers ) ) {

	 		echo "<div class='wkmp_seller'><h2>" . $value . '</h2></div>';

	 		echo "<ul class='wkmp_sellermenu'>";

	 		foreach ( $id as $row ) {
	 			$no++;

				$user_id = $row->user_id;

				$shop_address = get_user_meta( $user_id, 'shop_address', true );

	 			if ( $user_id == $current_user && $check_value != 'on' ) {
	 				continue;
				}

	 			if( $user_id == 0 || $user_id == 1 ) {
	 				continue;
	 			}

	 			if( $instance['option'] == 'Nick Name' ) {

	 				$username = "SELECT user_nicename FROM $users WHERE ID = $user_id";

	 				$name = $wpdb->get_var( $username );

	 			} else {
	 				$firstName = "SELECT meta_value FROM $usermeta WHERE meta_key='first_name' AND user_id = $user_id";

	 				$fname = $wpdb->get_var($firstName);

	 				$lastName = "SELECT meta_value FROM $usermeta WHERE meta_key='last_name' AND user_id=$user_id";

	 				$lname = $wpdb->get_var($lastName);

	 				$name = $fname.' '.$lname;

	 			}

				if ( empty(trim($name)) ) {
					$username = "SELECT user_nicename FROM $users WHERE ID = $user_id ";

	 				$name = $wpdb->get_var( $username );
				}
				$name = trim( $name );

		 			if( $name != '' ) {
		 			?>
		 				<li class="wkmp-selleritem">

		 					<a href="<?php echo home_url( get_option( 'wkmp_seller_page_title' ) . "/" . get_option( 'mp_store', 'store' ) . "/" . strtolower($shop_address)); ?>">

		 						<?php echo $name; ?>

		 					</a>

		 				</li>

		 			<?php
		 		}

	 			if( $no > $userNo )

	 				break;
	 		}

	 		echo "</ul>";
		}

	 }

	 function update( $new_instance, $old_instance ) {
	 	$instance = $old_instance;

	 	$instance['value'] = $new_instance['value'];

	 	$instance['check_value'] = $new_instance['check_value'];

	 	$instance['option'] = $new_instance['option'];

	 	$instance['noOfUsers'] = $new_instance['noOfUsers'];

	 	return $instance;
	 }

	 function form( $instance ) {
	 	$object = array( 'value' => 'Seller list', 'check_value' => '', 'user_msg' => 'Display Seller Including Me ?', 'list_msg' => 'Enter list Name', 'name_option' => 'Show Seller list as:', 'nick' => 'Nick Name', 'full' => 'Full Name', 'option'=>'Full Name', 'Users' => 'No. of Users:', 'noOfUsers' => 10 );

	 	$instance = wp_parse_args( (array)$instance, $object );

	 	?>
	 		<p>
	 			<label for="<?php echo $this->get_field_id('value'); ?>"><?php echo __($instance['list_msg'], 'marketplace');	?></label>

	 			<input type="text" name="<?php echo $this->get_field_name('value'); ?>" id="<?php echo $this->get_field_id('value'); ?>" style="width:100%" value="<?php echo $instance['value']; ?>">

	 		</p>
	 		<p>
	 			<label for="<?php echo $this->get_field_id('check_value'); ?>"><?php echo __($instance['user_msg'], 'marketplace'); ?></label>

	 			<input type="checkbox" id="<?php echo $this->get_field_id('check_value'); ?>" name="<?php echo $this->get_field_name('check_value'); ?>" <?php echo isset($instance['check_value'])? 'checked':'uncheked' ;?>>

	 		</p>

	 		<p>
	 			<div><b><?php echo __( $instance['name_option'], 'marketplace' ); ?></b></div>

	 			<input type="radio" id="<?php echo $this->get_field_id('option1'); ?>" name="<?php echo $this->get_field_name('option'); ?>" value="<?php echo $instance['nick']; ?>" <?php echo ( $instance['option'] == $instance['nick'] ) ? 'checked' : '';?>>

	 			<label for="<?php echo $this->get_field_id('option1'); ?>"><?php echo __($instance['nick'], 'marketplace'); ?></label>

	 			<input type="radio" id="<?php echo $this->get_field_id('option2'); ?>" name="<?php echo $this->get_field_name('option'); ?>" value="<?php echo $instance['full']; ?>" <?php echo ( $instance['option'] == $instance['full']) ? 'checked' : '';?>>

	 			<label for="<?php echo $this->get_field_id('option2'); ?>"><?php echo __($instance['full'], 'marketplace'); ?></label>
	 		</p>

	 		<p>
	 			<b><label for="<?php echo $this->get_field_id('noOfUsers'); ?>"><?php echo __($instance['Users'], 'marketplace'); ?></label></b>

	 			<input type="text" id="<?php echo $this->get_field_id('noOfUsers'); ?>" name="<?php echo $this->get_field_name('noOfUsers'); ?>" value="<?php echo $instance['noOfUsers']?>">
	 		</p>

	 	<?php

	 }
}
register_widget( 'MP_Widget_Seller_list' );
