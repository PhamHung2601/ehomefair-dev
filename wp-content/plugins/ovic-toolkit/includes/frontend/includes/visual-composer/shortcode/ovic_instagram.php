<?php
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * Shortcode class
 * @var $this "Ovic_Shortcode_Instagram"
 * @version 1.0.0
 */
if ( !class_exists( 'Ovic_Shortcode_Instagram' ) ) {
	class Ovic_Shortcode_Instagram extends Ovic_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = 'instagram';

		static public function add_css_generate( $atts )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_instagram', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css = '';

			return apply_filters( 'Ovic_Shortcode_Instagram_css', $css, $atts );
		}

		public function output_html( $atts, $content = null )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_instagram', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css_class   = array( 'ovic-instagram' );
			$css_class[] = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, '', 'ovic_instagram', $atts );
			/* START */
			$instagram_item_class = array( 'item' );
			$instagram_list_class = array( 'content-instagram' );
			$owl_settings         = '';
			$items                = array();
			ob_start();
			?>
            <div class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
				<?php if ( $atts['title'] ) : ?>
                    <h3 class="widgettitle"><?php echo esc_html( $atts['title'] ); ?></h3>
				<?php endif;
				if ( $atts['image_source'] == 'instagram' ) {
					$id_instagram  = trim( $atts['id_instagram'] );
					$token         = trim( $atts['token'] );
					$items_limit   = trim( $atts['items_limit'] );
					$key_instagram = "ovic_instagram_media_{$id_instagram}_{$token}_{$items_limit}";
					$instagram     = get_transient( $key_instagram );
					if ( intval( $id_instagram ) === 0 || intval( $token ) === 0 ) {
						echo sprintf( '<div class="alert alert-warning"><strong>%s</strong> %s</div>',
							esc_html__( 'Warning!', 'ocolus' ),
							esc_html__( 'No user ID specified.', 'ovic-toolkit' )
						);
					}
					if ( empty( $instagram ) || $instagram === false && $atts['id_instagram'] && $atts['token'] ) {
						$instagram_api = add_query_arg(
							array(
								'access_token' => $token,
								'count'        => $items_limit,
							),
							"https://api.instagram.com/v1/users/{$id_instagram}/media/recent"
						);
						$response      = wp_remote_get( $instagram_api );
						if ( !is_wp_error( $response ) ) {
							$response_body = json_decode( $response['body'], true );
							$response_code = json_decode( $response['response']['code'] );
							if ( $response_code != 200 ) {
								echo sprintf( '<div class="alert alert-warning"><strong>%s</strong> %s</div>',
									esc_html__( 'Warning!', 'ovic-toolkit' ),
									esc_html__( 'User ID and access token do not match. Please check again.', 'ovic-toolkit' )
								);
							} else {
								$items_as_objects = $response_body['data'];
								if ( !empty( $items_as_objects ) ) {
									foreach ( $items_as_objects as $item_object ) {
										$image               = $item_object['images'][$atts['image_resolution']];
										$item['link']        = $item_object['link'];
										$item['user']        = $item_object['user'];
										$item['type']        = $item_object['type'];
										$item['time']        = $item_object['created_time'];
										$item['likes']       = $item_object['likes'];
										$item['comments']    = $item_object['comments'];
										$item['images']      = $item_object['images'];
										$item['description'] = '';
										if ( isset( $item_object['caption']['text'] ) ) {
											$item['description'] = $item_object['caption']['text'];
										}
										$item['src']    = $image['url'];
										$item['width']  = $image['width'];
										$item['height'] = $image['height'];
										$items[]        = $item;
									}
								}
							}
							set_transient( $key_instagram, $items, 12 * HOUR_IN_SECONDS );
						} elseif ( isset( $response->errors ) && !empty( $response->errors ) ) {
							delete_transient( $key_instagram );
							foreach ( $response->errors as $errors ) {
								if ( !empty( $errors ) ) {
									foreach ( $errors as $error ) {
										echo sprintf( '<div class="alert alert-warning"><strong>%s</strong> %s</div>',
											esc_html__( 'Warning!', 'ovic-toolkit' ),
											esc_html( $error )
										);
									}
								}
							}
						}
					} else {
						$items = $instagram;
					}
				} else {
					if ( $atts['image_gallery'] ) {
						$instagram_list_class[] = 'ovic-gallery-image';
						$image_gallery          = explode( ',', $atts['image_gallery'] );
						foreach ( $image_gallery as $image ) {
							list( $src, $width, $height ) = wp_get_attachment_image_src( $image, 'full' );
							$items[] = array(
								'link'        => $src,
								'user'        => '',
								'type'        => '',
								'time'        => '',
								'description' => '',
								'likes'       => '',
								'comments'    => '',
								'images'      => array(
									'thumbnail'           => array(
										'url'    => $src,
										'width'  => $width,
										'height' => $height,
									),
									'low_resolution'      => array(
										'url'    => $src,
										'width'  => $width,
										'height' => $height,
									),
									'standard_resolution' => array(
										'url'    => $src,
										'width'  => $width,
										'height' => $height,
									),
								),
								'src'         => $src,
								'width'       => $width,
								'height'      => $height,
							);
						}
					}
				}
				if ( $atts['productsliststyle'] == 'grid' ) {
					$instagram_list_class[] = 'row auto-clear equal-container better-height ';
					$instagram_item_class[] = $atts['boostrap_rows_space'];
					$instagram_item_class[] = 'col-bg-' . $atts['boostrap_bg_items'];
					$instagram_item_class[] = 'col-lg-' . $atts['boostrap_lg_items'];
					$instagram_item_class[] = 'col-md-' . $atts['boostrap_md_items'];
					$instagram_item_class[] = 'col-sm-' . $atts['boostrap_sm_items'];
					$instagram_item_class[] = 'col-xs-' . $atts['boostrap_xs_items'];
					$instagram_item_class[] = 'col-ts-' . $atts['boostrap_ts_items'];
				}
				if ( $atts['productsliststyle'] == 'owl' ) {
					$instagram_list_class[] = 'owl-slick';
					$instagram_list_class[] = $atts['owl_navigation_style'];
					$instagram_item_class[] = $atts['owl_rows_space'];
					$owl_settings           = apply_filters( 'ovic_carousel_data_attributes', 'owl_', $atts );
				}
				if ( isset( $items ) && $items ): ?>
                    <div class="<?php echo implode( ' ', $instagram_list_class ); ?>" <?php echo esc_attr( $owl_settings ); ?>>
						<?php foreach ( $items as $item ):
							$image = $item['images'][$atts['image_resolution']];
							$img_lazy = "data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22{$image['width']}%22%20height%3D%22{$image['height']}%22%20viewBox%3D%220%200%20{$image['width']}%20{$image['height']}%22%3E%3C%2Fsvg%3E";
							?>
                            <div class="<?php echo implode( ' ', $instagram_item_class ); ?>">
                                <a href="<?php echo esc_url( $item['link'] ) ?>" class="thumb">
                                    <figure>
                                        <img class="img-responsive lazy" src="<?php echo esc_attr( $img_lazy ); ?>"
                                             data-src="<?php echo esc_url( $image['url'] ); ?>"
											<?php echo image_hwstring( $image['width'], $image['height'] ); ?>
                                             alt="<?php echo esc_attr( $item['description'] ); ?>"/>
                                    </figure>
                                </a>
                            </div>
						<?php endforeach; ?>
                    </div>
				<?php endif; ?>
            </div>
			<?php
			$html = ob_get_clean();

			return apply_filters( 'Ovic_Shortcode_Instagram', $html, $atts, $content );
		}
	}

	new Ovic_Shortcode_Instagram();
}