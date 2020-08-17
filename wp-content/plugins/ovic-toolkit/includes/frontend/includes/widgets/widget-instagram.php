<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Ovic instagram
 *
 * Displays instagram widget.
 *
 * @author   Khanh
 * @category Widgets
 * @package  Ovic/Widgets
 * @version  1.0.0
 * @extends  OVIC_Widget
 */
if ( !class_exists( 'Ovic_Instagram_Widget' ) ) {
	class Ovic_Instagram_Widget extends OVIC_Widget
	{
		/**
		 * Constructor.
		 */
		public function __construct()
		{
			$array_settings           = apply_filters( 'ovic_filter_settings_instagram_contact',
				array(
					'title'            => array(
						'type'  => 'text',
						'title' => esc_html__( 'Title', 'ovic-toolkit' ),
					),
					'image_source'     => array(
						'type'       => 'select',
						'title'      => esc_html__( 'Image Source', 'ovic-toolkit' ),
						'options'    => array(
							'instagram' => esc_html__( 'From Instagram', 'ovic-toolkit' ),
							'gallery'   => esc_html__( 'From Local Image', 'ovic-toolkit' ),
						),
						'default'    => 'instagram',
						'attributes' => array(
							'data-depend-id' => 'image_source',
						),
					),
					'image_gallery'    => array(
						'type'       => 'gallery',
						'title'      => esc_html__( 'Image Gallery', 'ovic-toolkit' ),
						'dependency' => array( 'image_source', '==', 'gallery' ),
					),
					'image_resolution' => array(
						'type'       => 'select',
						'title'      => esc_html__( 'Image Resolution', 'ovic-toolkit' ),
						'options'    => array(
							'thumbnail'           => esc_html__( 'Thumbnail', 'ovic-toolkit' ),
							'low_resolution'      => esc_html__( 'Low Resolution', 'ovic-toolkit' ),
							'standard_resolution' => esc_html__( 'Standard Resolution', 'ovic-toolkit' ),
						),
						'default'    => 'thumbnail',
						'dependency' => array( 'image_source', '==', 'instagram' ),
					),
					'id_instagram'     => array(
						'type'       => 'text',
						'title'      => esc_html__( 'ID Instagram', 'ovic-toolkit' ),
						'dependency' => array( 'image_source', '==', 'instagram' ),
					),
					'token'            => array(
						'type'       => 'text',
						'title'      => esc_html__( 'Token Instagram', 'ovic-toolkit' ),
						'dependency' => array( 'image_source', '==', 'instagram' ),
						'desc'       => wp_kses( sprintf( '<a href="%s" target="_blank">' . esc_html__( 'Get Token Instagram Here!', 'ovic-toolkit' ) . '</a>', 'http://instagram.pixelunion.net' ), array( 'a' => array( 'href' => array(), 'target' => array() ) ) ),
					),
					'items_limit'      => array(
						'type'       => 'number',
						'default'    => '5',
						'dependency' => array( 'image_source', '==', 'instagram' ),
						'title'      => esc_html__( 'Items Instagram', 'ovic-toolkit' ),
					),
				)
			);
			$this->widget_cssclass    = 'widget-ovic-instagram ovic-instagram';
			$this->widget_description = esc_html__( 'Display the customer Instagram.', 'ovic-toolkit' );
			$this->widget_id          = 'widget_ovic_instagram';
			$this->widget_name        = esc_html__( 'Ovic: Instagram', 'ovic-toolkit' );
			$this->settings           = $array_settings;
			parent::__construct();
		}

		/**
		 * Output widget.
		 *
		 * @see WP_Widget
		 *
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance )
		{
			$this->widget_start( $args, $instance );
			$items = array();
			$class = array( 'content-instagram' );
			if ( $instance['image_source'] == 'instagram' ) {
				$id_instagram  = trim( $instance['id_instagram'] );
				$token         = trim( $instance['token'] );
				$items_limit   = trim( $instance['items_limit'] );
				$key_instagram = "ovic_instagram_media_{$id_instagram}_{$token}_{$items_limit}";
				$instagram     = get_transient( $key_instagram );
				$instagram_api = add_query_arg(
					array(
						'access_token' => $token,
						'count'        => $items_limit,
					),
					"https://api.instagram.com/v1/users/{$id_instagram}/media/recent"
				);
				$response      = wp_remote_get( $instagram_api );
				if ( $instagram === false || empty( $instagram ) ) {
					if ( !is_wp_error( $response ) && $response != '' ) {
						$response_body = json_decode( $response['body'], true );
						$response_code = json_decode( $response['response']['code'] );
						if ( $response_code != 200 ) {
							echo sprintf( '<div class="alert alert-warning"><strong>%s</strong> %s</div>',
								esc_html__( 'Warning!', 'ovic-toolkit' ),
								esc_html__( 'User ID and access token do not match. Please check again.', 'ovic-toolkit' )
							);
						} else {
							$items_as_objects = isset( $response_body['data'] ) ? $response_body['data'] : array();
							if ( !empty( $items_as_objects ) ) {
								foreach ( $items_as_objects as $item_object ) {
									$image               = $item_object['images'][$instance['image_resolution']];
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
				if ( $instance['image_gallery'] ) {
					$class[]       = 'ovic-gallery-image';
					$image_gallery = explode( ',', $instance['image_gallery'] );
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
			if ( isset( $items ) && !empty( $items ) ):
				ob_start(); ?>
                <div class="<?php echo esc_attr( implode( ' ', $class ) ); ?>">
					<?php foreach ( $items as $item ):
						$image = $item['images'][$instance['image_resolution']];
						$img_lazy = "data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22{$image['width']}%22%20height%3D%22{$image['height']}%22%20viewBox%3D%220%200%20{$image['width']}%20{$image['height']}%22%3E%3C%2Fsvg%3E";
						?>
                        <a href="<?php echo esc_url( $item['link'] ) ?>" class="item image-link">
                            <figure>
                                <img class="img-responsive lazy" src="<?php echo esc_attr( $img_lazy ); ?>"
                                     data-src="<?php echo esc_url( $image['url'] ); ?>"
									<?php echo image_hwstring( $image['width'], $image['height'] ); ?>
                                     alt="<?php echo esc_attr( $item['description'] ); ?>"/>
                            </figure>
                        </a>
					<?php endforeach; ?>
                </div>
				<?php
				echo apply_filters( 'ovic_filter_widget_instagram', ob_get_clean(), $instance, $items );
			endif;
			$this->widget_end( $args );
		}
	}
}
/**
 * Register Widgets.
 *
 * @since 2.3.0
 */
function Ovic_Instagram_Widget()
{
	register_widget( 'Ovic_Instagram_Widget' );
}

add_action( 'widgets_init', 'Ovic_Instagram_Widget' );