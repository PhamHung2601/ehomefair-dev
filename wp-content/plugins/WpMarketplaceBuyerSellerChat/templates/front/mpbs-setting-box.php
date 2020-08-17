<?php

use WpMarketplaceBuyerSellerChat\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$helper = new Helper\Mpbs_Data();

$user_id = $helper->mpbs_get_current_user_id();

$data = $helper->mpbs_get_user_data( $user_id );

$image_src = $helper->mpbs_get_user_image_by_id( $user_id );

if ( isset( $_POST['mpbs-settings-submit'] ) ) {
	do_action( 'mpbs_save_profile_data_hook' );
}
?>

<div class="mpbs-profile-setting-overlay"></div>

<div class="mpbs-profile-setting-box">
  <span class="mpbs-close-box" title="<?php echo esc_html__( 'Close', 'mp_buyer_seller_chat' ); ?>"></span>
  <h1><?php echo esc_html__( 'Profile Setting', 'mp_buyer_seller_chat' ); ?></h1>
  <form method="post" action="" class="mpbs-setting-from" id="mpbs-profile-submit" enctype="multipart/form-data">
    <table class="form-table">
      <tbody>
        <tr>
          <th><div id="buyer-profile-image"><img src="<?php echo $image_src; ?>" id="buyer-profile-image" height="60px" width="70px"></div></th>

          <td><input type="file" data-error="<?php echo esc_html__( 'File not supported!', 'mp_buyer_seller_chat' ); ?>" data-size-error="<?php echo esc_html__( 'File size exceeded supported size[2mb]!', 'mp_buyer_seller_chat' ); ?>" id="buyer-image" name="buyer_profile_img"><input type="button" id="wk-buyer-thumb" value="<?php echo esc_html__( 'Change', 'mp_buyer_seller_chat' ); ?>"></td>
        </tr>
        <tr>
          <th><label for="buyer_name"><?php echo esc_html__( 'First Name', 'mp_buyer_seller_chat' ); ?></label></th>
          <td><input type="text" id="buyer_name" name="buyer_first_name" class="buyer-first-name" value="<?php if ( isset( $data->first_name ) ) echo $data->first_name; ?>"></td>
        </tr>
        <tr>
          <th><label for="buyer_last_name"><?php echo esc_html__( 'Last Name', 'mp_buyer_seller_chat' ); ?></label></th>
          <td><input type="text" id="buyer_last_name" name="buyer_last_name" class="buyer-first-name" value="<?php if ( isset( $data->last_name ) ) echo $data->last_name; ?>"></td>
        </tr>
        <tr>
          <td>
            <p class="submit">
                <?php wp_nonce_field( 'mpbs_profile_nonce_action', 'mpbs_profile_nonce' ); ?>
                <input type="submit" class="button button-primary" name="mpbs-settings-submit" value="<?php echo esc_html__( 'Save', 'mp_buyer_seller_chat' ); ?>">
            </p>
          </td>
        </tr>
      </tbody>
    </table>
  </form>
</div>
