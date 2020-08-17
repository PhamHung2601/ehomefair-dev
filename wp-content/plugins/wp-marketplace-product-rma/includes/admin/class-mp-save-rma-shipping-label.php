<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('MP_Save_Rma_Label')) {
    class MP_Save_Rma_Label
    {
        public function __construct()
        {
            add_action('mp_save_rma_shipping_label', array($this, 'mp_save_rma_shipping_label'), 1);
        }

        public function mp_save_rma_shipping_label($data)
        {
            $dir = wp_upload_dir();
            $label_url = $data['shipping_label_path'];
            if ($label_url) {

                $allowed_extensions = array( 'jpg', 'png', 'jpeg', 'doc', 'odt', 'pdf', 'odf' );

                $label_url = str_replace($dir['baseurl'], '', $label_url);
                $uploaded_file = pathinfo( $label_url );
                $uploaded_file_extension = $uploaded_file['extension'];

                if( !in_array( $uploaded_file_extension, $allowed_extensions ) ) {
                    ?>

					<div class="notice notice-error">
						<p><?php esc_html_e('Please upload a valid file.', 'marketplace-rma');?></p>
					</div>

					<?php
                } else {

                    $user_id = apply_filters('mp_rma_user_id', 'user_id');
                    $paths = get_user_meta($user_id, 'mp_rma_shipping_label_path', true);
    
                    if (empty($paths)) {
                        $path_data = array();
                    } else {
                        $path_data = $paths;
                    }
    
                    $path_data[] = $label_url;
    
                    $check = update_user_meta($user_id, 'mp_rma_shipping_label_path', $path_data);
    
                    if ($check) {
                        ?>
    
                        <div class="notice notice-success">
                            <p><?php esc_html_e('Shipping label uploaded successfully!', 'marketplace-rma');?></p>
                        </div>
    
                        <?php
    
                    }

                }

            }
        }
    }

    new MP_Save_Rma_Label();
}
