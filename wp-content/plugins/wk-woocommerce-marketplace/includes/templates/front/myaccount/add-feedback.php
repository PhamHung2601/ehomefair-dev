<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user_id = get_current_user_id();

if ( isset( $_POST ) && ! empty( $_POST ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'mp-seller-review-nonce' ) ) { // Input var okay.
	if ( isset( $_POST['mp_wk_seller'] ) && intval( $_POST['mp_wk_seller'] ) !== $user_id ) {
		set_reviews();
	} else {
		wc_print_notice( __( 'Error: Can&#39;t post review for self store.', 'marketplace' ), 'error' );
	}
	unset( $_POST ); // Input var okay.
}

$current_time = date( 'Y-m-d H:i:s' );

$sellerurl = get_query_var( 'info' );

$sellerid = apply_filters( 'mp_get_seller_id', $sellerurl );

$review_check = get_user_meta( $user_id, '_seller_review', true );

if ( empty( $review_check ) ) {
	$review_check = array();
}

?>

<div class="mp-profile-wrapper">

	<?php
	require 'seller-profile-details-section.php';

	if ( in_array( $sellerid, $review_check, true ) ) {
		?>
		<p>
			<?php
				echo esc_html__( 'You have already reviewed this shop.', 'marketplace' );
			?>
		</p>
		<?php
	}

	if ( $sellerid !== $user_id && ! in_array( $sellerid, $review_check, true ) ) {

		?>

		<div class="mp-add-feedback-section">
			<h4><?php esc_html_e( 'Write your review', 'marketplace' ); ?></h4>

			<b><p><?php echo esc_html__( 'How do you rate this store', 'marketplace' ) . ' ? <span class="error-class">*</span>'; ?></p></b>

			<form action="" class="mp-seller-review-form" method="post" enctype="multipart/form-data">

				<div class="wkmp_feedback_main_in">

					<div class="mp-feedback-price-rating mp-rating-input">
						<p><?php echo esc_html__( 'Price', 'marketplace' ); ?></p>
						<p class="mp-star-rating">
								<a class="star" href="#" data-rate="1" data-type="price"></a>
								<a class="star" href="#" data-rate="2" data-type="price"></a>
								<a class="star" href="#" data-rate="3" data-type="price"></a>
								<a class="star" href="#" data-rate="4" data-type="price"></a>
								<a class="star" href="#" data-rate="5" data-type="price"></a>
						</p>
						<select name="feed_price" id="feed-price-rating" aria-required="true" style="display:none;">
							<option value=""><?php esc_html__( 'Rate&hellip;', 'woocommerce' ); ?></option>
							<option value="5"><?php esc_html__( 'Perfect', 'woocommerce' ); ?></option>
							<option value="4"><?php esc_html__( 'Good', 'woocommerce' ); ?></option>
							<option value="3"><?php esc_html__( 'Average', 'woocommerce' ); ?></option>
							<option value="2"><?php esc_html__( 'Not that bad', 'woocommerce' ); ?></option>
							<option value="1"><?php esc_html__( 'Very poor', 'woocommerce' ); ?></option>
						</select>
					</div>

					<div class="mp-feedback-value-rating mp-rating-input">
						<p><?php echo esc_html__( 'Value', 'marketplace' ); ?></p>
						<p class="mp-star-rating">
								<a class="star" href="#" data-rate="1" data-type="value"></a>
								<a class="star" href="#" data-rate="2" data-type="value"></a>
								<a class="star" href="#" data-rate="3" data-type="value"></a>
								<a class="star" href="#" data-rate="4" data-type="value"></a>
								<a class="star" href="#" data-rate="5" data-type="value"></a>
						</p>
						<select name="feed_value" id="feed-value-rating" aria-required="true" style="display:none;">
							<option value=""><?php esc_html__( 'Rate&hellip;', 'woocommerce' ); ?></option>
							<option value="5"><?php esc_html__( 'Perfect', 'woocommerce' ); ?></option>
							<option value="4"><?php esc_html__( 'Good', 'woocommerce' ); ?></option>
							<option value="3"><?php esc_html__( 'Average', 'woocommerce' ); ?></option>
							<option value="2"><?php esc_html__( 'Not that bad', 'woocommerce' ); ?></option>
							<option value="1"><?php esc_html__( 'Very poor', 'woocommerce' ); ?></option>
						</select>
					</div>

					<div class="mp-feedback-quality-rating mp-rating-input">
						<p><?php echo esc_html__( 'Quality', 'marketplace' ); ?></p>
						<p class="mp-star-rating">
								<a class="star" href="#" data-rate="1" data-type="quality"></a>
								<a class="star" href="#" data-rate="2" data-type="quality"></a>
								<a class="star" href="#" data-rate="3" data-type="quality"></a>
								<a class="star" href="#" data-rate="4" data-type="quality"></a>
								<a class="star" href="#" data-rate="5" data-type="quality"></a>
						</p>
						<select name="feed_quality" id="feed-quality-rating" aria-required="true" style="display:none;">
							<option value=""><?php esc_html__( 'Rate&hellip;', 'woocommerce' ); ?></option>
							<option value="5"><?php esc_html__( 'Perfect', 'woocommerce' ); ?></option>
							<option value="4"><?php esc_html__( 'Good', 'woocommerce' ); ?></option>
							<option value="3"><?php esc_html__( 'Average', 'woocommerce' ); ?></option>
							<option value="2"><?php esc_html__( 'Not that bad', 'woocommerce' ); ?></option>
							<option value="1"><?php esc_html__( 'Very poor', 'woocommerce' ); ?></option>
						</select>
					</div>

				</div>

				<div class="error-class" id="feedback-rate-error"></div>

				<div class="wkmp_feedback_fields_in">
					<p><b><?php esc_html_e( 'Subject', 'marketplace' ); ?><span class="error-class">*</span></b></p>

					<input type="text" name="feed_summary" class="form-row-wide">
				</div>

				<div class="wkmp_feedback_fields_in">

					<p><b><?php esc_html_e( 'Review', 'marketplace' ); ?><span class="error-class">*</span></b></p>

					<textarea rows='4' name='feed_review' class="form-row-wide"></textarea>

				</div>

				<?php wp_nonce_field( 'mp-seller-review-nonce' ); ?>

				<input type="hidden" name="create_date" value="<?php echo $current_time; ?>" />

				<input type="hidden" name="mp_wk_seller" value="<?php echo $sellerid; ?>" />

				<input type="hidden" name="mp_wk_user" value="<?php echo $user_id; ?>" />

				<input type="hidden" name="mp_wk_sellerurl" value="<?php echo $sellerurl; ?>" />

				<p><input type="submit" id="wk_mp_reviews_user" value="<?php esc_html_e( 'Submit Review', 'marketplace' ); ?>" class="button" /></p>

			</form>

		</div>

		<?php

	}

	?>

</div>
