<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$wpmp_obj10 = new MP_Form_Handler();

require_once WK_MARKETPLACE_DIR . 'includes/front/facebookv5/src/Facebook/autoload.php';

$page_name = $wpdb->get_var( "SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option( 'wkmp_seller_page_title' ) . "'" );

/***************faceboook***************/

if ( isset( $_GET['check'] ) ) {
	$store_name = get_query_var( 'info' );
	wp_redirect( home_url( '/' . $page_name . '/add-feedback/' ) . $store_name );
	exit();
}

if ( isset( $_GET['checkpoint'] ) ) {

	$checkpoint = $_GET['checkpoint'];

	$appid = get_option( 'wkfb_mp_key_app_ID' );

	$secretkey = get_option( 'wkfb_mp_app_secret_key' );

	if ( $checkpoint && $appid && $secretkey ) {
		$key = $_GET['key'];

		$facebook = new Facebook\Facebook([
			'app_id'                => $appid,
			'app_secret'            => $secretkey,
			'default_graph_version' => 'v2.5',
		]);

		$helper = $facebook->getRedirectLoginHelper();

		$permissions = [ 'id', 'email', 'name' ]; // optional.

		try {
			if ( isset( $_SESSION['localhost_app_token'] ) ) {
				$accessToken = $_SESSION['localhost_app_token'];
			} else {
				$accessToken = $helper->getAccessToken();
			}
		} catch( Facebook\Exceptions\FacebookResponseException $e ) {
				echo esc_html__( 'Graph returned an error: ', 'marketplace' ) . $e->getMessage();
				exit;
		} catch( Facebook\Exceptions\FacebookSDKException $e ) {
			echo esc_html__( 'Facebook SDK returned an error: ', 'marketplace' ) . $e->getMessage();
			exit;
		}

		$accessToken = $key;

		if ( isset( $accessToken ) ) {
			if ( isset( $_SESSION['localhost_app_token'] ) ) {
				$facebook->setDefaultAccessToken( $_SESSION['localhost_app_token'] );
			} else {
				$_SESSION['localhost_app_token'] = (string) $accessToken;

				$oAuth2Client = $facebook->getOAuth2Client();

				$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken( $_SESSION['localhost_app_token'] );

				$_SESSION['localhost_app_token'] = (string) $longLivedAccessToken;

				$facebook->setDefaultAccessToken( $_SESSION['localhost_app_token'] );
			}
		}

		$user_info = null;

		if ( ! empty( $_SESSION['localhost_app_token'] ) ) {
			try {
				$profile_request = $facebook->get( '/me?fields=id,name,email' );
				$user_info = $profile_request->getGraphNode()->asArray();
			} catch( Facebook\Exceptions\FacebookResponseException $e ) {
				echo __('Graph returned an error: ', 'marketplace') . $e->getMessage();
				session_destroy();
				header("Location: ./");
				exit;
			} catch( Facebook\Exceptions\FacebookSDKException $e ) {
				echo __('Facebook SDK returned an error: ', 'marketplace') . $e->getMessage();
				exit;
			}

			$wk_user_name = $user_info['name'];

			$registerDate = date( 'Y-m-d H:i:s' );

			$first_name = isset( $user_info['first_name'] ) ? $user_info['first_name'] : '';

			$last_name = isset( $user_info['last_name'] ) ? $user_info['last_name'] : '';

			$wk_email = $user_info['email'];

			$login_name = explode( '@', $user_info['email'] );

			$user_url = isset( $user_info['link'] ) ? $user_info['link'] : '';

			$wk_random_password = wp_generate_password();

			if ( ! email_exists( $wk_email ) ) {

				$user_id = wp_create_user( $wk_email, $wk_random_password, $wk_email );
				update_user_meta( $user_id, 'first_name', $first_name );
				update_user_meta( $user_id, 'last_name', $last_name );

				if ( ! is_wp_error( $user_id ) ) {
					wp_set_current_user( $user_id ); // set the current wp user.
					wp_set_auth_cookie( $user_id );
					$store_name = get_query_var( 'info' );
					wp_redirect( home_url( '/' . $page_name . '/add-feedback/') . $store_name );
					exit;
				}
			} else {
				$user = get_user_by( 'email', $wk_email );
				$user_id = (int) $user->data->ID;
				$user_id = wp_update_user(
					array(
						'ID'        => $user_id,
						'user_pass' => $wk_random_password,
					)
				);

				if ( ! is_wp_error( $user_id ) ) {
					wp_set_current_user( $user_id ); // set the current wp user.
					wp_set_auth_cookie( $user_id );
					$store_name = get_query_var( 'info' );
					wp_redirect( home_url( '/' . $page_name . '/add-feedback/' ) . $store_name );
					exit;
				}
			}
		}
	}
}

/***************faceboook**************/

$sellerurl = urldecode( get_query_var( 'info' ) );

global $wpdb;

$user = get_users(
	array(
	 'meta_key'   => 'shop_address',
	 'meta_value' => $sellerurl,
	)
);

if ( ! empty( $user ) ) :
	foreach ( $user as $value ) {
		$sellerid = $value->ID;
	}

	$currency = get_woocommerce_currency_symbol( get_option( 'woocommerce_currency' ) );

	$sell_data = $wpmp_obj10->spreview( $sellerid );

	$seller_product = $wpmp_obj10->seller_product( $sellerid );

	$lenghtProduct = count( $seller_product );

	if ( is_array( $sell_data ) ) {
		foreach ( $sell_data as $key => $value ) {
			$seller_all[ $value->meta_key ] = $value->meta_value;
		}
	} else {
		$seller_all[] = '';
	}

	$seller_name = ( $seller_all['first_name'] ) ? $seller_all['first_name'] . ' ' . $seller_all['last_name'] : $seller_all['nickname'];

	$banner = $wpmp_obj10->get_user_avatar( $sellerid, 'shop_banner' );

	$shop_logo = $wpmp_obj10->get_user_avatar( $sellerid, 'company_logo' );

	if ( is_user_logged_in() ) {
		$user_value = $sellerurl;
	}

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

	<div class="mp-profile-wrapper woocommerce">

		<h1 class="mp-page-title"><?php esc_html_e( 'Seller', 'marketplace' ); ?> - <?php echo $seller_name; ?></h1>

		<?php if ( isset( $seller_all['shop_banner_visibility'] ) && $seller_all['shop_banner_visibility'] == 'yes' ) : ?>

		<div class="mp-profile-banner">
			<?php
			if ( ! isset( $banner[0]->meta_value ) ) {
				?>
				<img src="<?php echo WK_MARKETPLACE . 'assets/images/woocommerce-marketplace-banner.png'; ?>" class="mp-shop-banner" />
				<?php
			} else {
				?>
				<img src="<?php echo content_url() . '/uploads/' . $banner[0]->meta_value; ?>" class="mp-shop-banner" />
				<?php
			}
			?>
		</div>
		<?php endif; ?>

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
									<?php echo wc_get_rating_html( $price_stars ); ?>
									<p>( <?php echo number_format( $price_stars, 2 ) . '/' . $total_feedback; ?> )</p>
								</div>
								<div class="mp-avg-rating">
									<p><?php esc_html_e( 'Value', 'marketplace' ); ?></p>
									<?php echo wc_get_rating_html( $value_stars ); ?>
									<p>( <?php echo number_format( $value_stars, 2 ) . '/' . $total_feedback; ?> )</p>
								</div>
								<div class="mp-avg-rating">
									<p><?php esc_html_e( 'Quality', 'marketplace' ); ?></p>
									<?php echo wc_get_rating_html( $quality_stars ); ?>
									<p>( <?php echo number_format( $quality_stars, 2 ) . '/' . $total_feedback; ?> )</p>
								</div>
							</div>
						</a>
						<?php else:
							if( get_current_user_id() ) {
								if ( get_current_user_id() !== $sellerid 	) {
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
						<?php } ?>
					<?php endif; ?>
				</div>
			</div>

			<?php do_action( 'mp_edit_seller_profile' ); ?>

			<div class="mp-shop-actions-info">
				<div class="mp-shop-action-wrapper">
					<div class="mp-shop-info">
						<?php if ( get_option( 'wkmp_show_seller_email' ) == 'yes' ) : ?>
							<p><span class="shop-mail"></span><span class="content"><a href="mailto:<?php echo $seller_user_data->user_email; ?>"><?php echo $seller_user_data->user_email; ?></a></span></p>
						<?php endif; ?>

						<?php if ( get_option( 'wkmp_show_seller_contact' ) == 'yes' ) : ?>
						<?php if ( isset( $seller_all['billing_phone'] ) && $seller_all['billing_phone'] ): ?>
							<p><span class="shop-phone"></span><span class="content"><a href="tel:<?php echo $seller_all['billing_phone']; ?>" target="_blank" title="<?php esc_html_e( 'Click to Dial - Phone Only', 'marketplace' );?>"><?php echo $seller_all['billing_phone']; ?></a></span></p>
						<?php endif; ?>
						<?php endif; ?>

						<?php if ( get_option( 'wkmp_show_seller_address' ) == 'yes' ) : ?>
						<?php if ( isset( $seller_all['billing_country'] ) && $seller_all['billing_country'] ) : ?>
							<p><span class="shop-location"></span><span class="content"><?php echo WC()->countries->countries[ $seller_all['billing_country'] ]; ?></span></p>
						<?php endif; ?>
						<?php endif; ?>

						<?php if ( get_option( 'wkmp_show_seller_social_links' ) == 'yes' ) : ?>
							<?php require 'seller-profile-social-links-section.php'; ?>
						<?php endif; ?>
					</div>
					<div class="mp-shop-actions">
						<a class="button wc-forward" href="<?php echo esc_url( get_permalink() . get_option( 'mp_seller_product', 'seller-product' ) . '/' . $sellerurl ); ?>"><?php echo __( 'View Collection', 'marketplace' ); ?></a>
						<?php
						$review_check = get_user_meta( get_current_user_id(), '_seller_review', true );
						if ( empty( $review_check ) ) {
							$review_check = array();
						}
						if ( get_current_user_id() ) {
							if ( get_current_user_id() !== $sellerid && ! in_array( $sellerid, $review_check ) ) {
								?>
								<div class="wk_write_review">
									<a class="btn btn-default button button-small open-review-form forloginuser wk_mpsocial_feedback" href="#wk_review_form"><?php echo __( 'Write A Review !', 'marketplace' ); ?></a>
								</div>
								<?php
							}
						} else { ?>
							<div class="wk_write_review">
								<a class="button open-review-form forloginuser wk_mpsocial_feedback" href="javascript:void(0);"><?php echo __( 'Write A Review !', 'marketplace'); ?></a>
							</div>
					<?php } ?>
					</div>
				</div>
			</div>

		</div>

		<?php do_action( 'mkt_before_seller_preview_products' ); ?>

		<div class="mp-seller-recent-product">
			<h3><?php esc_html_e( 'Recent Product from Seller', 'marketplace' ); ?></h3>
			<?php
			$query_args = array(
				'author'         => $sellerid,
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 6,
			);

			$products = new WP_Query( $query_args );

			if ( $products->have_posts() ) {
				woocommerce_product_loop_start();
				while ( $products->have_posts() ) : $products->the_post();
					wc_get_template_part( 'content', 'product' );
				endwhile;
				woocommerce_product_loop_end();
			} else {
				echo esc_html__( 'No product available !', 'marketplace' );
			}

			wp_reset_postdata();

			?>
		</div>

		<?php do_action( 'mkt_after_seller_preview_products' ); ?>

		<!-- About shop -->
		<div class="mp-about-shop">
			<p><b><?php echo esc_html__( 'About', 'marketplace' ); ?></b></p>
			<p><?php echo isset( $seller_all['about_shop'] ) ? esc_attr( $seller_all['about_shop'] ) : 'N/A'; ?></p>
		</div>

		<?php do_action( 'mkt_before_seller_review_data' ); ?>

		<!-- Shop reviews -->
		<?php if ( $review_data ) : ?>
			<div class="mp-shop-reviews">
				<?php

				$review_data = apply_filters( 'mkt_filter_seller_reviews', $review_data );

				foreach ( $review_data as $key => $value ) {
					$review_author = get_user_by( 'ID', $value->user_id );

					if ( $review_author ) {
							$display_name = $review_author->display_name;
					} else {
							$display_name = '-';
					}

					?>
					<div class="mp-shop-review-row">

						<div class="mp-shop-review-rating">
							<p><b><?php echo esc_html__( 'Review', 'marketplace' ); ?></b></p>
							<div class="rating"><span><b>
								<?php
								esc_html_e( 'Price', 'marketplace' ); ?></b></span><?php
								wp_star_rating(
									array(
										'rating' => $value->price_r,
										'type'   => 'rating',
									)
								);
								?>
							</div>
							<div class="rating"><span><b>
								<?php
								esc_html_e( 'Value', 'marketplace' ); ?></b></span><?php
								wp_star_rating(
									array(
										'rating' => $value->value_r,
										'type'   => 'rating',
									)
								);
								?>
							</div>
							<div><span><b>
								<?php
								esc_html_e( 'Quality', 'marketplace' ); ?></b></span><?php
								wp_star_rating( array(
									'rating' => $value->quality_r,
									'type'   => 'rating',
								) );
								?>
							</div>
						</div>

						<div class="mp-shop-review-detail">
							<p><b><?php echo stripslashes( esc_attr( $value->review_summary ) ); ?></b></p>
							<p><?php echo esc_html__( 'By ', 'marketplace' ) . '<b>' . ucfirst( $display_name ) . '</b> , ' . date( 'd M Y', strtotime( $value->review_time ) ); ?></p>
							<p><?php echo stripslashes( esc_attr( $value->review_desc ) ); ?></p>
						</div>

					</div>
					<?php
				}
				?>
				<div class="mp-review-page-link"><a href="<?php echo esc_url( get_permalink() . 'feedback/' . $sellerurl ); ?>" class="button" ><?php echo esc_html__( 'View All Reviews', 'marketplace' ); ?></a></div>
				</div>
				<?php
				endif;
				?>
	</div>

	<?php require 'mp-popup-login-form.php'; ?>

<?php else : ?>

	<h1><?php echo esc_html__( 'Oops! That page can’t be found.', 'marketplace' ); ?></h1>
	<p><?php echo esc_html__( 'Nothing was found at this location.', 'marketplace' ); ?></p>

<?php endif; ?>
