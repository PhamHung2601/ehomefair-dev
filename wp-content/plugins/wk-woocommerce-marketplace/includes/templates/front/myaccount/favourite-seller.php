<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wk_mkt_seller;

?>

<div class="favourite-seller">

	<?php

	$seller_list = get_user_meta( get_current_user_id(), 'favourite_seller', false );

	if ( ! empty( $seller_list ) ) {

	?>

	<table class="shop-fol customer-end">

		<thead>

			<tr>
				<th class=""><?php esc_html_e( 'Seller Profile', 'marketplace' ); ?></th>
				<th class=""><?php esc_html_e( 'Seller Name', 'marketplace' ); ?></th>
				<th class=""><?php esc_html_e( 'Seller Collection', 'marketplace' ); ?></th>
				<th class=""><?php esc_html_e( 'Action', 'marketplace' ); ?></th>
			</tr>

		</thead>

		<tbody>
			<?php

			if ( ! empty( $seller_list ) ) {

				$wpmp_obj12 = new MP_Form_Handler();


				foreach ( $seller_list as $seller_key => $seller_value ) {

					$avatar = $wpmp_obj12->get_user_avatar( $seller_value, 'avatar' );

					if ( empty( $avatar ) ) {

						$avatar = WK_MARKETPLACE . '/assets/images/genric-male.png';

					} else {

						$up = wp_upload_dir();

						$avatar = $avatar[0]->meta_value;
						$avatar = $up['baseurl'] . '/' . $avatar;
					}

					$seller_store = get_user_meta( $seller_value, 'shop_address', true );

					$seller_name = get_user_meta( $seller_value, 'first_name', true );


				?>

				<tr class="order">

					<td><img src="<?php echo esc_html( $avatar ); ?>" alt="" height="40" width="40"></td>

					<td><?php echo esc_html( $seller_name ); ?></td>

					<?php

					if ( empty( $seller_store ) ) {

						echo '<td>';
						echo esc_html__( 'Not Available', 'marketplace' );
						echo '</td>';
					} else {

						echo '<td><a href=' . esc_url( home_url( get_option( 'wkmp_seller_page_title' ) . '/'. get_option( 'mp_store', 'store' ) .'/' . $seller_store ) ) . '>' . esc_html( $seller_store ) . '</a></td>';

					}
							?>

							<td><span class="remove-icon" data-seller-id="<?php echo esc_html( $seller_value ); ?>" data-customer-id="<?php echo intval( get_current_user_id() ); ?>"></span></td>

						</tr>

						<?php
				}
			} else {

				echo '<td><strong>' . esc_html__( 'Favourite list empty.', 'marketplace' ) . '</strong></td>';
			}
			?>
		</tbody>

	</table>

<?php
	} else {
	?>
	<div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
		<a class="woocommerce-Button button" href="<?php echo esc_html( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"><?php echo esc_html__( 'Go shop', 'marketplace' ); ?></a><?php echo esc_html__( 'No seller&#39;s added yet.', 'marketplace' ); ?></div>
<?php
	}
	?>

</div>
<?php
