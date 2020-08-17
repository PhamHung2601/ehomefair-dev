<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wpmp_obj11 = new MP_Form_Handler();

$sell_data = $wpmp_obj11->spreview( $sellerid );

if ( is_array( $sell_data ) ) {
	foreach ( $sell_data as $key => $value ) {
		$seller_all[ $value->meta_key ] = $value->meta_value;
	}
} else {
	$seller_all[] = '';
}

$shop_logo = $wpmp_obj11->get_user_avatar( $sellerid, 'company_logo' );

$seller_name = ( $seller_all['first_name'] ) ? $seller_all['first_name'] . ' ' . $seller_all['last_name'] : $seller_all['nickname'];

$review_data = get_review( $sellerid );

$num_of_stars = $total_feedback = $price_stars = $value_stars = $quality_stars = 0;

if ( $review_data ) {
	foreach ( $review_data as $item ) {
		$num_of_stars  += $item->price_r;
		$price_stars   += $item->price_r;
		$num_of_stars  += $item->value_r;
		$value_stars   += $item->value_r;
		$num_of_stars  += $item->quality_r;
		$quality_stars += $item->quality_r;
		$total_feedback++;
	}
}

if ( $num_of_stars != 0 ) {
	$quality        = $num_of_stars / ( $total_feedback * 3 );
	$price_stars   /= $total_feedback;
	$value_stars   /= $total_feedback;
	$quality_stars /= $total_feedback;
} else {
	$quality = 0;
}

require_once ABSPATH . 'wp-admin/includes/template.php';

$seller_user_data = get_user_by( 'ID', $sellerid );

?>

<h1 class="mp-page-title"><?php if ( get_query_var('main_page') == 'add-feedback' ) esc_html_e( 'Review ', 'marketplace' ); esc_html_e( 'Seller', 'marketplace' ); ?> - <?php echo esc_html( $seller_name ); ?></h1>

<div class="mp-profile-information">

	<div class="mp-shop-stats">
		<?php
		if ( isset( $shop_logo[0]->meta_value ) ) {
			echo '<img src="' . content_url() . '/uploads/' . $shop_logo[0]->meta_value . '" class="mp-shop-logo" />';
		} else {
			echo '<img src="' . WK_MARKETPLACE . 'assets/images/shop-logo.png" class="mp-shop-logo" />';
		}
		?>
		<div class="mp-seller-avg-rating">
			<?php if ( $quality ) : ?>
				<h2><span class="single-star"></span><?php echo number_format( $quality, 2 ); ?></h2>
				<a href="javascript:void(0)" class="mp-avg-rating-box-link"><?php _e( 'Average Rating', 'marketplace' ); ?>
					<div class="mp-avg-rating-box">
						<div class="mp-avg-rating">
							<p><?php esc_html_e( 'Price', 'marketplace' ); ?></p>
							<?php
							echo wc_get_rating_html( $price_stars );
							?>
							<p>( <?php echo number_format( $price_stars, 2 ) . '/' . $total_feedback; ?> )</p>
						</div>
						<div class="mp-avg-rating">
							<p><?php esc_html_e( 'Value', 'marketplace' ); ?></p>
							<?php
							echo wc_get_rating_html( $value_stars );
							?>
							<p>( <?php echo number_format( $value_stars, 2 ) . '/' . $total_feedback; ?> )</p>
						</div>
						<div class="mp-avg-rating">
							<p><?php esc_html_e( 'Quality', 'marketplace' ); ?></p>
							<?php
							echo wc_get_rating_html( $quality_stars );
							?>
							<p>( <?php echo number_format( $quality_stars, 2 ) . '/' . $total_feedback; ?> )</p>
						</div>
					</div>
				</a>
			<?php
			else :
				if ( ( null !== get_query_var( 'main_page' ) && get_query_var( 'main_page' ) != 'add-feedback' ) ) {
					if ( get_current_user_id() ) {
						if ( get_current_user_id() !== $sellerid ) {
							?>
							<div class="wk_write_review">
								<a class="open-review-form forloginuser wk_mpsocial_feedback" href="#wk_review_form"><?php echo esc_html__( 'Be the first one to review!', 'marketplace' ); ?></a>
							</div>
							<?php
						}
					} else {
						?>
						<div class="wk_write_review">
							<a class="open-review-form forloginuser wk_mpsocial_feedback" href="javascript:void(0);"><?php echo esc_html__( 'Be the first one to review!', 'marketplace' ); ?></a>
						</div>
				<?php
					}
				}
				?>
		<?php endif; ?>
		</div>
	</div>

	<div class="mp-shop-actions-info">
		<div class="mp-shop-action-wrapper">
			<div class="mp-shop-info">
				<?php if ( get_option( 'wkmp_show_seller_email' ) == 'yes' ) : ?>
					<p><span class="shop-mail"></span><span class="content"><a href="mailto:<?php echo $seller_user_data->user_email; ?>"><?php echo $seller_user_data->user_email; ?></a></span></p>
				<?php endif; ?>

				<?php if ( get_option( 'wkmp_show_seller_contact' ) == 'yes' ): ?>
				<?php if ( isset( $seller_all['billing_phone'] ) && $seller_all['billing_phone'] ) : ?>
					<p><span class="shop-phone"></span><span class="content"><a href="tel:<?php echo $seller_all['billing_phone']; ?>" target="_blank" title="<?php echo esc_html( 'Click to Dial - Phone Only', 'marketplace' ); ?>"><?php echo $seller_all['billing_phone']; ?></a></span></p>
				<?php endif; ?>
				<?php endif; ?>

				<?php if ( get_option( 'wkmp_show_seller_address' ) == 'yes' ) : ?>
				<?php if ( isset( $seller_all['billing_country'] ) && $seller_all['billing_country'] ) : ?>
					<p><span class="shop-location"></span><span class="content"><?php echo esc_html( WC()->countries->countries[ $seller_all['billing_country'] ] ); ?></span></p>
				<?php endif; ?>
				<?php endif; ?>

				<?php if ( get_option( 'wkmp_show_seller_social_links' ) == 'yes' ) : ?>
					<?php require 'seller-profile-social-links-section.php'; ?>
				<?php endif; ?>
			</div>
			<div class="mp-shop-actions">
				<a class="button wc-forward" href="<?php echo esc_url( get_permalink() . get_option( 'mp_store', 'store' ). '/' . $sellerurl ); ?>"><?php echo esc_html__( 'View Profile', 'marketplace' ); ?></a>
				<?php
				if ( ( null !== get_query_var( 'main_page' ) && get_query_var( 'main_page' ) != 'add-feedback' ) ) {
						$review_check = get_user_meta( get_current_user_id(), '_seller_review', true );
					if ( empty( $review_check ) ) {
							$review_check = array();
					}
					if ( get_current_user_id() ) {
						if ( get_current_user_id() !== $sellerid && ! in_array( $sellerid, $review_check, true ) ) {
								?>
								<div class="wk_write_review">
									<a class="btn btn-default button button-small open-review-form forloginuser wk_mpsocial_feedback" href="#wk_review_form"><?php echo esc_html__( 'Write A Review !', 'marketplace' ); ?></a>
								</div>
								<?php
						}
					} else {
						?>
						<div class="wk_write_review">
							<a class="button open-review-form forloginuser wk_mpsocial_feedback" href="javascript:void(0);"><?php echo esc_html__( 'Write A Review !', 'marketplace' ); ?></a>
						</div>
						<?php
					}
				}
				?>
			</div>
		</div>
	</div>

</div>
