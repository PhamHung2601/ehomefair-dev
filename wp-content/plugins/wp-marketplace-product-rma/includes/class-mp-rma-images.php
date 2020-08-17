<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( 'MP_Rma_Images' ) )
{
    /**
     *
     */
    class MP_Rma_Images
    {

        function __construct()
        {
            add_action( 'mp_rma_view_images', array( $this, 'mp_customer_rma_images' ) );
            add_action( 'admin_footer', array( $this, 'mp_rma_image_overlay' ) );
            add_action( 'wp_footer', array( $this, 'mp_rma_image_overlay' ) );
        }

        function mp_customer_rma_images()
        {
						$dir = wp_upload_dir();
            $rma_id   = apply_filters( 'mp_rma_id', 'rma_id' );
            $mp_data = apply_filters( 'mp_get_rma_data', $rma_id );
						if ( $mp_data[0]->images_path ) :
            ?>
            <table class="rma-images">
              <tbody>
                <tr>
                    <?php foreach ( explode( ";", $mp_data[0]->images_path) as $key => $value): ?>
                      <td><a href="" class="mp-rma-image-link" data-source="<?php echo $dir['baseurl'].$value; ?>"><img src="<?php echo $dir['baseurl'].$value; ?>"></a></td>
                    <?php endforeach; ?>
                </tr>
              </tbody>
            </table>
            <?php
						else:
							?>
							<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
									<?php echo __('No image found.', 'marketplace-rma'); ?>
							</div>
							<?php
						endif;
        }

        function mp_rma_image_overlay()
        {
            ?>
            <div class="mp-rma-image-full-overlay-bg"></div>
            <div class="mp-rma-image-full-overlay">
                <button title="<?php esc_html_e('Close (Esc)', 'marketplace-rma'); ?>" type="button" class="mfp-close">×</button>
                <div class="mp-rma-image-full-cover">
                    <img src="" />
                </div>
            </div>
            <?php
        }

    }

    new MP_Rma_Images();

}
