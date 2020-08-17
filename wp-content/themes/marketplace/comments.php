<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Marketplace
 * @since 1.0
 * @version 1.0
 */
if ( post_password_required() ) {
	return;
}
$fields            = array(
	'name'  => '<p class="comment-reply-content"><input type="text" name="author" id="name" class="input-form name" placeholder="' . esc_html__( 'Your name', 'marketplace' ) . '"/></p>',
	'email' => '<p class="comment-reply-content"><input type="text" name="email" id="email" class="input-form email" placeholder="' . esc_html__( 'Your email', 'marketplace' ) . '"/></p>',
);
$comment_field     = '<p class="comment-reply-content"><textarea class="input-form" id="comment" name="comment" cols="45" rows="6" aria-required="true" placeholder="' . esc_html__( 'Your Comments', 'marketplace' ) . '"></textarea></p>';
$comment_field     .= apply_filters( 'ovic_add_field_comment', '' );
$comment_form_args = array(
	'class_submit'  => 'button',
	'comment_field' => $comment_field,
	'fields'        => $fields,
	'label_submit'  => esc_html__( 'Submit', 'marketplace' ),
	'title_reply'   => esc_html__( 'Leave your Comment', 'marketplace' ),
);
?>
<div id="comments" class="comments-area">
	<?php if ( have_comments() ) : ?>
        <h2 class="comments-title">
			<?php
			$comments_number = get_comments_number();
			if ( '1' === $comments_number ) {
				/* translators: %s: post title */
				printf( _x( 'One Reply to &ldquo;%s&rdquo;', '', 'marketplace' ), get_the_title() );
			} else {
				printf(
				/* translators: 1: number of comments, 2: post title */
					_nx(
						'%1$s Reply to &ldquo;%2$s&rdquo;',
						'%1$s Replies to &ldquo;%2$s&rdquo;',
						$comments_number,
						'',
						'marketplace'
					),
					number_format_i18n( $comments_number ),
					get_the_title()
				);
			}
			?>
        </h2>
        <ol class="comment-list">
			<?php
			wp_list_comments( array(
					'style'    => 'ol',
					'callback' => 'marketplace_callback_comment',
				)
			);
			?>
        </ol>
	<?php
	endif;
	the_comments_pagination( array(
			'screen_reader_text' => '',
			'prev_text'          => '<span class="screen-reader-text">' . esc_html__( 'Prev', 'marketplace' ) . '</span>',
			'next_text'          => '<span class="screen-reader-text">' . esc_html__( 'Next', 'marketplace' ) . '</span>',
		)
	);
	if ( !comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
        <p class="no-comments"><?php echo esc_html__( 'Comments are closed.', 'marketplace' ); ?></p>
	<?php
	endif;
	comment_form( $comment_form_args );
	?>
</div><!-- #comments -->
