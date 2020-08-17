<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function add_favourite_seller_btn() {

	// Add new Favourite seller button after add to cart button.
	global $wpdb, $product;

	if ( isset( $_POST['submit_favourite'] ) ) {
		if ( ! isset( $_POST['fv_sel_nonce_field'] ) || ! wp_verify_nonce( $_POST['fv_sel_nonce_field'], 'fv_sel_action' ) ) {
			die( esc_html__('secrity check...!', 'marketplace') );
		} else {
			$sellers = get_user_meta( get_current_user_id(), 'favourite_seller', false );

			$seller = intval( $_POST['seller'] );

			if ( $seller == get_current_user_id() ) {
				wc_add_notice( esc_html__( 'You can not add yourself on favourite list.', 'marketplace' ), 'error' );
				wp_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '/favourite-seller' );
				exit;
			} elseif ( is_array( $sellers ) && in_array( $seller, $sellers ) ) {
				wc_add_notice( esc_html__( 'Seller is already added in favourite list.', 'marketplace' ), 'error' );
				wp_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '/favourite-seller' );
				exit;
			} else {
				$check = add_user_meta( get_current_user_id(), 'favourite_seller', $seller );
				if ( $check ) {
					wc_add_notice( esc_html__( 'Seller added successfully in favourite list.', 'marketplace' ), 'success' );
					wp_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '/favourite-seller' );
					exit;
				}
			}
		}
	}

	$table = $wpdb->prefix . 'posts'; //Good practice.

	$product_id = $product->get_id();

	$pro_author = $wpdb->get_row( "SELECT $table.post_author FROM $table WHERE $table.ID =" . $product_id );

	if ( isset( $pro_author->post_author ) && ! empty( $pro_author->post_author ) ) {
		$product_author = $pro_author->post_author;
	} else {
		$product_author = 1;
	}
	$sellers = get_user_meta( get_current_user_id(), 'favourite_seller', false );
	?>

	<div class='fav-seller'>

		<form action="" method="post">

			<?php wp_nonce_field( 'fv_sel_action', 'fv_sel_nonce_field' ); ?>

			<input type="hidden" value="<?php echo $product_author;?>" name="seller">

			<?php

			$favourite_seller_c = get_users(array(
				'meta_key'   => 'favourite_seller',
				'meta_value' => get_current_user_id(),
			));

			$favourite_seller_count = count( $favourite_seller_c );

			if ( $favourite_seller_count > 0 ) :
				?>
				<div class="favourite-count">
					<?php echo '<p><strong>' . esc_html__( 'Favourited By', 'marketplace' ) . '</strong> : ' . intval( $favourite_seller_count ) . ' Peoples</p>'; ?>
				</div>
			<?php
		endif;

			if ( ! empty( $sellers ) ) {
				if ( in_array( $product_author, $sellers ) ) {
					echo '<button type="submit" name="submit_favourite" class="button" disabled>' . esc_html__( 'Add As Favourite Seller', 'marketplace' ) . '</button>';
				} else {
					echo '<button type="submit" name="submit_favourite" class="button">' . esc_html__( 'Add As Favourite Seller', 'marketplace' ) . '</button>';
				}
			} elseif ( is_user_logged_in() ) {
				echo '<button type="submit" name="submit_favourite" class="button">' . esc_html__( 'Add As Favourite Seller', 'marketplace' ) . '</button>';
			}

			if ( ! is_user_logged_in() ) {
				echo '<a href="' . esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) . '" class="button">' . esc_html__( 'Add As Favourite Seller', 'marketplace' ) . '</a>';
			}
			?>

			</form>

		</div>

<?php
}
