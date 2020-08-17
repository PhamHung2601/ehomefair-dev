<?php
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * Shortcode class
 * @var $this "Ovic_Shortcode_Instagram_2"
 * @version 1.0.0
 */
if ( !class_exists( 'Ovic_Shortcode_Instagram_2' ) ) {
	class Ovic_Shortcode_Instagram_2 extends Ovic_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = 'instagram_2';

		static public function add_css_generate( $atts )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_instagram_2', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css = '';

			return apply_filters( 'Ovic_Shortcode_Instagram_2_css', $css, $atts );
		}

		public function output_html( $atts, $content = null )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_instagram_2', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css_class   = array( 'ovic-instagram' );
			$css_class[] = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, '', 'ovic_instagram_2', $atts );
			/* START */
			$instagram_item_class = array( 'item' );
			$instagram_list_class = array( 'content-instagram' );
			$owl_settings         = '';
			ob_start();
			?>
            <div class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
				<?php if ( $atts['title'] ) : ?>
                    <h3 class="widgettitle"><?php echo esc_html( $atts['title'] ); ?></h3>
				<?php endif;
				if ( $atts['image_source'] == 'instagram' ) {
					if ( intval( $atts['id_instagram'] ) === 0 || intval( $atts['token'] ) === 0 ) {
						esc_html_e( 'No user ID specified.', 'ovic-toolkit' );
					}
					if ( !empty( $id_instagram ) && !empty( $token ) ) {
						$response = wp_remote_get( 'https://api.instagram.com/v1/users/' . esc_attr( $id_instagram ) . '/media/recent/?access_token=' . esc_attr( $token ) . '&count=' . esc_attr( $atts['items_limit'] ) );
						if ( !is_wp_error( $response ) ) {
							$items         = array();
							$response_body = json_decode( $response['body'] );
							$response_code = json_decode( $response['response']['code'] );
							if ( $response_code != 200 ) {
								echo '<p>' . esc_html__( 'User ID and access token do not match. Please check again.', 'ovic-toolkit' ) . '</p>';
							} else {
								$items_as_objects = $response_body->data;
								if ( !empty( $items_as_objects ) ) {
									foreach ( $items_as_objects as $item_object ) {
										$item['link']     = $item_object->link;
										$item['user']     = $item_object->user;
										$item['likes']    = $item_object->likes;
										$item['comments'] = $item_object->comments;
										$item['src']      = $item_object->images->{$atts['image_resolution']}->url;
										$item['width']    = $item_object->images->{$atts['image_resolution']}->width;
										$item['height']   = $item_object->images->{$atts['image_resolution']}->height;
										$items[]          = $item;
									}
								}
							}
						}
					}
				} else {
					if ( $atts['image_gallery'] ) {
						$instagram_list_class[] = 'ovic-gallery-image';
						$image_gallery          = explode( ',', $atts['image_gallery'] );
						foreach ( $image_gallery as $image ) {
							$image_thumb = wp_get_attachment_image_src( $image, 'full' );
							$items[]     = array(
								'link'   => $image_thumb[0],
								'src'    => $image_thumb[0],
								'width'  => $image_thumb[1],
								'height' => $image_thumb[2],
							);
						}
					}
				}
				if ( $atts['productsliststyle'] == 'grid' ) {
					$instagram_list_class[] = 'row auto-clear equal-container better-height ';
					$instagram_item_class[] = Ovic_Field_Advandce::generate_grid_attr( $atts['bootstrap'] );
				}
				if ( $atts['productsliststyle'] == 'owl' ) {
					$instagram_list_class[] = 'owl-slick';
					$instagram_item_class[] = $atts['owl_rows_space'];
					$owl_settings           = Ovic_Field_Advandce::generate_slide_attr( $atts['carousel'] );
				}
				if ( isset( $items ) && $items ): ?>
                    <div class="<?php echo implode( ' ', $instagram_list_class ); ?>" <?php echo esc_attr( $owl_settings ); ?>>
						<?php foreach ( $items as $item ):
							$img_lazy = "data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%20" . $item['width'] . "%20" . $item['height'] . "%27%2F%3E";
							?>
                            <div class="<?php echo implode( ' ', $instagram_item_class ); ?>">
                                <a href="<?php echo esc_url( $item['link'] ) ?>" class="thumb">
                                    <figure>
                                        <img class="img-responsive lazy" src="<?php echo esc_attr( $img_lazy ); ?>"
                                             data-src="<?php echo esc_url( $item['src'] ); ?>"
											<?php echo image_hwstring( $item['width'], $item['height'] ); ?>
                                             alt="<?php echo get_the_title(); ?>"/>
                                    </figure>
                                </a>
                            </div>
						<?php endforeach; ?>
                    </div>
				<?php endif; ?>
            </div>
			<?php
			$html = ob_get_clean();

			return apply_filters( 'Ovic_Shortcode_Instagram_2', $html, $atts, $content );
		}
	}

	new Ovic_Shortcode_Instagram_2();
}