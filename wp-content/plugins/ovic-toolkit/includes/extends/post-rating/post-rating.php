<?php
/**
 * Ovic Rating Comments setup
 *
 * @author   KHANH
 * @category API
 * @package  Ovic_Rating_Comments
 * @since    1.0.0
 */
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( !class_exists( 'Ovic_Rating_Comments' ) ) {
	class Ovic_Rating_Comments
	{
		public         $key     = '_ovic_post_rating_settings';
		public         $options = array();
		public function __construct()
		{
			add_action( 'wp_enqueue_scripts', array( $this, 'rating_scripts' ) );
			add_action( 'comment_post', array( $this, 'ovic_add_rating_comment' ), 10, 2 );
			add_action( 'ovic_rating_show', array( $this, 'ovic_rating_show' ) );
			add_action( 'ovic_avg_comment_rate', array( $this, 'ovic_avg_comment_rate' ) );
			add_filter( 'ovic_rating_options', array( $this, 'ovic_rating_options' ) );
		}

		function rating_scripts()
		{
			wp_enqueue_style( 'ovic-rating', plugin_dir_url( __FILE__ ) . 'post-rating.css' );
		}

		function ovic_avg_comment_rate( $post_id )
		{
			$s        = 0;
			$count    = 0;
			$comments = get_comments( array( 'post_id' => $post_id, 'post_type' => 'post' ) );
			foreach ( $comments as $comment ) {
				$meta_value = get_comment_meta( $comment->comment_ID, 'rating_comment_field', true );
				if ( $meta_value != '' && $comment->comment_approved == 1 ) {
					$s += $meta_value;
					$count++;
				}
			}
			if ( $s == 0 )
				return;
			$s   = $s / $count;
			$avg = ( $s / 5 ) * 100;
			?>
            <div class="star-rating ovic-post-rating">
                <span style="width:<?php echo esc_attr( $avg ); ?>%"><strong class="rating"></strong></span>
            </div>
			<?php
		}

		function ovic_rating_options()
		{
			ob_start(); ?>
            <div class="content-rating">
                <span class="star-cb-group">
                    <input type="radio" id="rating-5" name="ovic_rating_comment" value="5"/><label
                            for="rating-5"></label>
                    <input type="radio" id="rating-4" name="ovic_rating_comment" value="4"/><label
                            for="rating-4"></label>
                    <input type="radio" id="rating-3" name="ovic_rating_comment" value="3"/><label
                            for="rating-3"></label>
                    <input type="radio" id="rating-2" name="ovic_rating_comment" value="2"/><label
                            for="rating-2"></label>
                    <input type="radio" id="rating-1" name="ovic_rating_comment" value="1"/><label
                            for="rating-1"></label>
                    <input type="radio" id="rating-0" name="ovic_rating_comment" value="0"
                           class="star-cb-clear"/><label for="rating-0"></label>
                </span>
            </div>
			<?php
			return $html = ob_get_clean();
		}

		function ovic_rating_show( $comment_id )
		{
			$meta_rate = get_comment_meta( $comment_id, 'rating_comment_field', true );
			if ( $meta_rate == 5 ) {
				$star_width = 100;
			} elseif ( $meta_rate == 4 ) {
				$star_width = 80;
			} elseif ( $meta_rate == 3 ) {
				$star_width = 60;
			} elseif ( $meta_rate == 2 ) {
				$star_width = 40;
			} elseif ( $meta_rate == 1 ) {
				$star_width = 20;
			} else {
				return;
			}
			?>
            <div class="star-rating ovic-post-rating">
                <span style="width:<?php echo esc_attr( $star_width ); ?>%"><strong class="rating"></strong></span>
            </div>
			<?php
		}

		function ovic_add_rating_comment( $comment_ID, $comment_approved )
		{
			$rating_value = isset( $_POST['ovic_rating_comment'] ) ? $_POST['ovic_rating_comment'] : 0;
			add_comment_meta( $comment_ID, 'rating_comment_field', $rating_value );
		}
	}

	new Ovic_Rating_Comments();
}