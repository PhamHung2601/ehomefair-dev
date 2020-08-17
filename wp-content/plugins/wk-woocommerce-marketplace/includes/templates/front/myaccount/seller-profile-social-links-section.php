<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo '<div class="mp-shop-social-links">';
if ( isset( $seller_all['social_facebook'] ) && $seller_all['social_facebook'] ) {
	echo '<a href="' . $seller_all['social_facebook'] . '" target="_blank" class="mp-social-icon fb"></a>';
}
if ( isset( $seller_all['social_twitter'] ) && $seller_all['social_twitter'] ) {
	echo '<a href="' . $seller_all['social_twitter'] . '" target="_blank" class="mp-social-icon twitter"></a>';
}
if ( isset( $seller_all['social_gplus'] ) && $seller_all['social_gplus'] ) {
	echo '<a href="' . $seller_all['social_gplus'] . '" target="_blank" class="mp-social-icon gplus"></a>';
}
if ( isset( $seller_all['social_linkedin'] ) && $seller_all['social_linkedin'] ) {
	echo '<a href="' . $seller_all['social_linkedin'] . '" target="_blank" class="mp-social-icon in"></a>';
}
if ( isset( $seller_all['social_youtube'] ) && $seller_all['social_youtube'] ) {
	echo '<a href="' . $seller_all['social_youtube'] . '" target="_blank" class="mp-social-icon yt"></a>';
}
echo '</div>';
