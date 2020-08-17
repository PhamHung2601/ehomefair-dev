<?php
/**
 * File for metabox.
 *
 * @package wk-woocommerce-marketplace/includes/templates/admin/single-product
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*----------*/ /*---------->>> Product Seller Selection <<<----------*/ /*----------*/

wp_nonce_field( 'blog_save_meta_box_data', 'blog_meta_box_nonce' );

global $wpdb, $post;

$sql = "SELECT user_id from {$wpdb->prefix}mpsellerinfo where seller_value = 'seller'";

$result = $wpdb->get_results( $sql );

$users = $wpdb->users;

?>

<div class="return-seller">

	<select name="seller_id" style="width:100%">

		<option value="">--<?php echo esc_html( 'Select Seller', 'marketplace' ); ?>--</option>

		<?php
		foreach ( $result as $key ) {
			$username = "SELECT user_nicename FROM $users WHERE ID = $key->user_id ";

			$name = $wpdb->get_var( $username );
			$val  = ( get_user_meta( $key->user_id, 'first_name', true ) ) ? get_user_meta( $key->user_id, 'first_name', true ) : $name;
			?>
			<option value="<?php echo esc_attr( $key->user_id ); ?>" <?php echo esc_html( ( $post->post_author == $key->user_id ) ? 'selected' : '' ); ?>><?php echo esc_html( $val ); ?></option>
			<?php
		}
		?>

	</select>

</div>
