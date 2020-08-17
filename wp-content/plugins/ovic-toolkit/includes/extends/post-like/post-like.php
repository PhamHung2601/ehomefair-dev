<?php
/**
 * Ovic Post Like setup
 *
 * @author   KHANH
 * @category API
 * @package  Ovic_Post_Like
 * @since    1.0.0
 */
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( !class_exists( 'Ovic_Post_Like' ) ) {
	class Ovic_Post_Like
	{
		public $comment_like_count    = '_comment_like_count';
		public $user_comment_liked    = '_user_comment_liked';
		public $user_like_count       = '_user_like_count';
		public $post_like_count       = '_post_like_count';
		public $user_liked            = '_user_liked';
		public $user_comment_IP       = '_user_comment_IP';
		public $user_IP               = '_user_IP';
		public $comment_like_modified = '_comment_like_modified';
		public $post_like_modified    = '_post_like_modified';

		public function __construct()
		{
			add_action( 'wp_enqueue_scripts', array( $this, 'ovic_enqueue_scripts' ) );
			add_action( 'wp_ajax_nopriv_process_simple_like', array( $this, 'process_simple_like' ) );
			add_action( 'wp_ajax_process_simple_like', array( $this, 'process_simple_like' ) );
			/* User Profile List */
			add_action( 'ovic_show_user_profile', array( $this, 'ovic_show_user_likes' ) );
			/* Like Button */
			add_action( 'ovic_simple_likes_button', array( $this, 'ovic_simple_likes_button' ) );
		}

		function ovic_enqueue_scripts()
		{
			wp_register_script( 'ovic-post-like', plugin_dir_url( __FILE__ ) . 'post-like.js', array( 'jquery' ), '1.0', true );
			wp_localize_script( 'ovic-post-like', 'simpleLikes', array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'like'    => __( 'Like', 'ovic-toolkit' ),
					'unlike'  => __( 'Unlike', 'ovic-toolkit' ),
				)
			);
		}

		/**
		 * Processes like/unlike
		 * @since    0.5
		 */
		function process_simple_like()
		{
			// Security
			$nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( $_REQUEST['nonce'] ) : 0;
			if ( !wp_verify_nonce( $nonce, 'simple-likes-nonce' ) ) {
				exit( __( 'Not permitted', 'ovic-toolkit' ) );
			}
			// Test if javascript is disabled
			$disabled = ( isset( $_REQUEST['disabled'] ) && $_REQUEST['disabled'] == true ) ? true : false;
			// Test if this is a comment
			$is_comment = ( isset( $_REQUEST['is_comment'] ) && $_REQUEST['is_comment'] == 1 ) ? 1 : 0;
			// Base variables
			$post_id    = ( isset( $_REQUEST['post_id'] ) && is_numeric( $_REQUEST['post_id'] ) ) ? $_REQUEST['post_id'] : '';
			$result     = array();
			$post_users = NULL;
			$like_count = 0;
			// Get plugin options
			if ( $post_id != '' ) {
				$count = ( $is_comment == 1 ) ? get_comment_meta( $post_id, $this->comment_like_count, true ) : get_post_meta( $post_id, $this->post_like_count, true ); // like count
				$count = ( isset( $count ) && is_numeric( $count ) ) ? $count : 0;
				if ( !$this->already_liked( $post_id, $is_comment ) ) { // Like the post
					if ( is_user_logged_in() ) { // user is logged in
						$user_id    = get_current_user_id();
						$post_users = $this->post_user_likes( $user_id, $post_id, $is_comment );
						if ( $is_comment == 1 ) {
							// Update User & Comment
							$user_like_count = get_user_option( $this->comment_like_count, $user_id );
							$user_like_count = ( isset( $user_like_count ) && is_numeric( $user_like_count ) ) ? $user_like_count : 0;
							update_user_option( $user_id, $this->comment_like_count, ++$user_like_count );
							if ( $post_users ) {
								update_comment_meta( $post_id, $this->user_comment_liked, $post_users );
							}
						} else {
							// Update User & Post
							$user_like_count = get_user_option( $this->user_like_count, $user_id );
							$user_like_count = ( isset( $user_like_count ) && is_numeric( $user_like_count ) ) ? $user_like_count : 0;
							update_user_option( $user_id, $this->user_like_count, ++$user_like_count );
							if ( $post_users ) {
								update_post_meta( $post_id, $this->user_liked, $post_users );
							}
						}
					} else { // user is anonymous
						$user_ip    = $this->sl_get_ip();
						$post_users = $this->post_ip_likes( $user_ip, $post_id, $is_comment );
						// Update Post
						if ( $post_users ) {
							if ( $is_comment == 1 ) {
								update_comment_meta( $post_id, $this->user_comment_IP, $post_users );
							} else {
								update_post_meta( $post_id, $this->user_IP, $post_users );
							}
						}
					}
					$like_count         = ++$count;
					$response['status'] = "liked";
					$response['icon']   = $this->get_liked_icon();
				} else { // Unlike the post
					if ( is_user_logged_in() ) { // user is logged in
						$user_id    = get_current_user_id();
						$post_users = $this->post_user_likes( $user_id, $post_id, $is_comment );
						// Update User
						if ( $is_comment == 1 ) {
							$user_like_count = get_user_option( $this->comment_like_count, $user_id );
							$user_like_count = ( isset( $user_like_count ) && is_numeric( $user_like_count ) ) ? $user_like_count : 0;
							if ( $user_like_count > 0 ) {
								update_user_option( $user_id, $this->comment_like_count, --$user_like_count );
							}
						} else {
							$user_like_count = get_user_option( $this->user_like_count, $user_id );
							$user_like_count = ( isset( $user_like_count ) && is_numeric( $user_like_count ) ) ? $user_like_count : 0;
							if ( $user_like_count > 0 ) {
								update_user_option( $user_id, $this->user_like_count, --$user_like_count );
							}
						}
						// Update Post
						if ( $post_users ) {
							$uid_key = array_search( $user_id, $post_users );
							unset( $post_users[$uid_key] );
							if ( $is_comment == 1 ) {
								update_comment_meta( $post_id, $this->user_comment_liked, $post_users );
							} else {
								update_post_meta( $post_id, $this->user_liked, $post_users );
							}
						}
					} else { // user is anonymous
						$user_ip    = $this->sl_get_ip();
						$post_users = $this->post_ip_likes( $user_ip, $post_id, $is_comment );
						// Update Post
						if ( $post_users ) {
							$uip_key = array_search( $user_ip, $post_users );
							unset( $post_users[$uip_key] );
							if ( $is_comment == 1 ) {
								update_comment_meta( $post_id, $this->user_comment_IP, $post_users );
							} else {
								update_post_meta( $post_id, $this->user_IP, $post_users );
							}
						}
					}
					$like_count         = ( $count > 0 ) ? --$count : 0; // Prevent negative number
					$response['status'] = "unliked";
					$response['icon']   = $this->get_unliked_icon();
				}
				if ( $is_comment == 1 ) {
					update_comment_meta( $post_id, $this->comment_like_count, $like_count );
					update_comment_meta( $post_id, $this->comment_like_modified, date( 'Y-m-d H:i:s' ) );
				} else {
					update_post_meta( $post_id, $this->post_like_count, $like_count );
					update_post_meta( $post_id, $this->post_like_modified, date( 'Y-m-d H:i:s' ) );
				}
				$response['count']   = $this->get_like_count( $like_count );
				$response['testing'] = $is_comment;
				if ( $disabled == true ) {
					if ( $is_comment == 1 ) {
						wp_redirect( get_permalink( get_the_ID() ) );
						exit();
					} else {
						wp_redirect( get_permalink( $post_id ) );
						exit();
					}
				} else {
					wp_send_json( $response );
				}
			}
		}

		/**
		 * Utility to test if the post is already liked
		 * @since    0.5
		 * @param $post_id
		 * @param $is_comment
		 * @return string
		 */
		function already_liked( $post_id, $is_comment )
		{
			$post_users = NULL;
			$user_id    = NULL;
			if ( is_user_logged_in() ) { // user is logged in
				$user_id         = get_current_user_id();
				$post_meta_users = ( $is_comment == 1 ) ? get_comment_meta( $post_id, $this->user_comment_liked ) : get_post_meta( $post_id, $this->user_liked );
				if ( count( $post_meta_users ) != 0 ) {
					$post_users = $post_meta_users[0];
				}
			} else { // user is anonymous
				$user_id         = $this->sl_get_ip();
				$post_meta_users = ( $is_comment == 1 ) ? get_comment_meta( $post_id, $this->user_comment_IP ) : get_post_meta( $post_id, $this->user_IP );
				if ( count( $post_meta_users ) != 0 ) { // meta exists, set up values
					$post_users = $post_meta_users[0];
				}
			}
			if ( is_array( $post_users ) && in_array( $user_id, $post_users ) ) {
				return true;
			} else {
				return false;
			}
		} // already_liked()

		/**
		 * Output the like button
		 * @since    0.5
		 * @param $is_comment
		 * @param $post_id
		 */
		function ovic_simple_likes_button( $post_id, $is_comment = NULL )
		{
			wp_enqueue_script( 'ovic-post-like' );
			$class      = array( 'sl-button' );
			$is_comment = ( NULL == $is_comment ) ? 0 : 1;
			$nonce      = wp_create_nonce( 'simple-likes-nonce' ); // Security
			if ( $is_comment == 1 ) {
				$class['id']      = "sl-comment-button-{$post_id}";
				$class['comment'] = 'sl-comment';
				$like_count       = get_comment_meta( $post_id, $this->comment_like_count, true );
				$like_count       = ( isset( $like_count ) && is_numeric( $like_count ) ) ? $like_count : 0;
			} else {
				$class['id']      = "sl-button-{$post_id}";
				$class['comment'] = '';
				$like_count       = get_post_meta( $post_id, $this->post_like_count, true );
				$like_count       = ( isset( $like_count ) && is_numeric( $like_count ) ) ? $like_count : 0;
			}
			$count      = '<span class="count">' . $this->get_like_count( $like_count ) . '</span>';
			$icon_empty = $this->get_unliked_icon();
			$icon_full  = $this->get_liked_icon();
			// Liked/Unliked Variables
			if ( $this->already_liked( $post_id, $is_comment ) ) {
				$class['like'] = 'liked';
				$icon          = '<span class="icon">' . $icon_full . '</span>';
				$title_html    = '<span class="title">' . __( 'Liked', 'ovic-toolkit' ) . '</span>';
			} else {
				$class['like'] = '';
				$icon          = '<span class="icon">' . $icon_empty . '</span>';
				$title_html    = '<span class="title">' . __( 'Like', 'ovic-toolkit' ) . '</span>';
			}
			$content = $icon . $count . $title_html;
			?>
            <div class="sl-wrapper">
                <a href="#" class="<?php echo esc_attr( implode( ' ', $class ) ); ?>"
                   data-nonce="<?php echo esc_attr( $nonce ); ?>"
                   data-post_id="<?php echo esc_attr( $post_id ); ?>"
                   data-iscomment="<?php echo esc_attr( $is_comment ); ?>">
					<?php echo apply_filters( 'ovic_content_like_buttom', $content, $icon, $count, $title_html ); ?>
                </a>
            </div>
			<?php
		} // ovic_simple_likes_button()

		/**
		 * Utility retrieves post meta user likes (user id array),
		 * then adds new user id to retrieved array
		 * @since    0.5
		 * @param $post_id
		 * @param $is_comment
		 * @param $user_id
		 * @return string
		 */
		function post_user_likes( $user_id, $post_id, $is_comment )
		{
			$post_users      = '';
			$post_meta_users = ( $is_comment == 1 ) ? get_comment_meta( $post_id, $this->user_comment_liked ) : get_post_meta( $post_id, $this->user_liked );
			if ( count( $post_meta_users ) != 0 ) {
				$post_users = $post_meta_users[0];
			}
			if ( !is_array( $post_users ) ) {
				$post_users = array();
			}
			if ( !in_array( $user_id, $post_users ) ) {
				$post_users['user-' . $user_id] = $user_id;
			}

			return $post_users;
		} // post_user_likes()

		/**
		 * Utility retrieves post meta ip likes (ip array),
		 * then adds new ip to retrieved array
		 * @since    0.5
		 * @param $post_id
		 * @param $is_comment
		 * @param $user_ip
		 * @return string
		 */
		function post_ip_likes( $user_ip, $post_id, $is_comment )
		{
			$post_users      = '';
			$post_meta_users = ( $is_comment == 1 ) ? get_comment_meta( $post_id, $this->user_comment_IP ) : get_post_meta( $post_id, $this->user_IP );
			// Retrieve post information
			if ( count( $post_meta_users ) != 0 ) {
				$post_users = $post_meta_users[0];
			}
			if ( !is_array( $post_users ) ) {
				$post_users = array();
			}
			if ( !in_array( $user_ip, $post_users ) ) {
				$post_users['ip-' . $user_ip] = $user_ip;
			}

			return $post_users;
		} // post_ip_likes()

		/**
		 * Utility to retrieve IP address
		 * @since    0.5
		 */
		function sl_get_ip()
		{
			if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip = ( isset( $_SERVER['REMOTE_ADDR'] ) ) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
			}
			$ip = filter_var( $ip, FILTER_VALIDATE_IP );
			$ip = ( $ip === false ) ? '0.0.0.0' : $ip;

			return $ip;
		} // sl_get_ip()

		/**
		 * Utility returns the button icon for "like" action
		 * @since    0.5
		 */
		function get_liked_icon()
		{
			/* If already using Font Awesome with your theme, replace svg with: <i class="fa fa-heart"></i> */
			$icon = '<i class="fa fa-heart" aria-hidden="true"></i>';

			return apply_filters( 'ovic_filter_like_icon', $icon );
		} // get_liked_icon()

		/**
		 * Utility returns the button icon for "unlike" action
		 * @since    0.5
		 */
		function get_unliked_icon()
		{
			/* If already using Font Awesome with your theme, replace svg with: <i class="fa fa-heart-o"></i> */
			$icon = '<i class="fa fa-heart-o" aria-hidden="true"></i>';

			return apply_filters( 'ovic_filter_unlike_icon', $icon );
		} // get_unliked_icon()

		/**
		 * Utility function to format the button count,
		 * appending "K" if one thousand or greater,
		 * "M" if one million or greater,
		 * and "B" if one billion or greater (unlikely).
		 * $precision = how many decimal points to display (1.25K)
		 * @since    0.5
		 * @param $number
		 * @return string
		 */
		function sl_format_count( $number )
		{
			$precision = 2;
			if ( $number >= 1000 && $number < 1000000 ) {
				$formatted = number_format( $number / 1000, $precision ) . 'K';
			} else if ( $number >= 1000000 && $number < 1000000000 ) {
				$formatted = number_format( $number / 1000000, $precision ) . 'M';
			} else if ( $number >= 1000000000 ) {
				$formatted = number_format( $number / 1000000000, $precision ) . 'B';
			} else {
				$formatted = $number; // Number is less than 1000
			}
			$formatted = str_replace( '.00', '', $formatted );

			return $formatted;
		} // sl_format_count()

		/**
		 * Utility retrieves count plus count options,
		 * returns appropriate format based on options
		 * @since    0.5
		 * @param $like_count
		 * @return string
		 */
		function get_like_count( $like_count )
		{
			if ( is_numeric( $like_count ) && $like_count > 0 ) {
				$number = $this->sl_format_count( $like_count );
			} else {
				$number = 0;
			}
			$count = '<span class="sl-count">' . $number . '</span>';

			return $count;
		} // get_like_count()

		function ovic_show_user_likes( $user_ID = null )
		{
			$userID = ( $user_ID == null ) ? get_current_user_id() : $user_ID;
			?>
            <table class="form-table">
                <thead>
                <tr>
                    <th><label for="user_likes"><?php _e( 'You Liked:', 'ovic-toolkit' ); ?></label></th>
                </tr>
                </thead>
                <tbody>
				<?php
				$types      = get_post_types( array( 'public' => true ) );
				$args       = array(
					'numberposts' => -1,
					'post_type'   => $types,
					'meta_query'  => array(
						array(
							'key'     => '_user_liked',
							'value'   => $userID,
							'compare' => 'LIKE',
						),
					),
				);
				$like_query = new WP_Query( $args );
				if ( $like_query->have_posts() ) :
					while ( $like_query->have_posts() ) :$like_query->the_post(); ?>
                        <tr>
                            <td>
                                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
									<?php the_title(); ?>
                                </a>
                            </td>
                        </tr>
					<?php
					endwhile;
				else : ?>
                    <tr>
                        <td><?php _e( 'You do not like anything yet.', 'ovic-toolkit' ); ?></td>
                    </tr>
				<?php endif;
				wp_reset_postdata();
				?>
                </tbody>
            </table>
		<?php } // ovic_show_user_likes()
	}

	new Ovic_Post_Like();
}