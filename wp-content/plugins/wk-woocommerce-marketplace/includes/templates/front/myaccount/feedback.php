<?php

global $wpdb;

$wpmp_obj13 = new MP_Form_Handler();

$user_id = get_current_user_id();

$sellerurl = get_query_var('info');

$sellerid = apply_filters('mp_get_seller_id', $sellerurl);

$review_result = get_review_by_page($sellerid);

if (is_user_logged_in() ) {
    $user_value = $sellerurl;
}

?>

<div class="mp-profile-wrapper">

    <?php require 'seller-profile-details-section.php'; ?>

    <div class="mp-shop-reviews">

    <?php
    if ($review_result['data'] ) {

        echo $review_result['count'];

        foreach ( $review_result['data'] as $value ) {

            $review_author = get_user_by('ID', $value->user_id);

            if ($review_author ) {
                $display_name = $review_author->display_name;
            } else {
                $display_name = '-';
            }

        ?>
       <div class="mp-shop-review-row">

        <div class="mp-shop-review-rating">
         <p><b><?php echo esc_html__('Review', 'marketplace'); ?></b></p>
      <div class="rating"><span><b>
                <?php
                esc_html_e('Price', 'marketplace');
                ?>
         </b></span>
                <?php
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
            esc_html_e('Value', 'marketplace');
            ?>
         </b></span>
            <?php
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
            esc_html_e('Quality', 'marketplace');
            ?>
         </b></span>
            <?php
            wp_star_rating(
                array(
                'rating' => $value->quality_r,
                'type'   => 'rating',
                )
            );
            ?>
         </div>
        </div>

        <div class="mp-shop-review-detail">
         <p><b><?php echo stripslashes(esc_attr($value->review_summary)); ?></b></p>
         <p><?php echo __('By ', 'marketplace') . ucfirst($display_name) . ' , ' . date('d M Y', strtotime($value->review_time)); ?></p>
         <p><?php echo stripslashes(esc_attr($value->review_desc)); ?></p>
        </div>

       </div>

        <?php
        }
    }
?>

    <?php require 'mp-popup-login-form.php'; ?>

</div>

</div>
