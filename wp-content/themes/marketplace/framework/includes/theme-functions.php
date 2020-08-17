<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * HOOK FOOTER
 */
add_action( 'marketplace_footer', 'marketplace_footer_top', 10 );
add_action( 'marketplace_footer', 'marketplace_footer_middle', 20 );
add_action( 'marketplace_footer', 'marketplace_footer_bottom', 30 );
add_action( 'marketplace_footer', 'marketplace_footer_mobile', 40 );
/**
 * HOOK HEADER
 */
add_action( 'marketplace_before_header', 'marketplace_header_sticky', 10 );
add_action( 'marketplace_header', 'marketplace_header_top', 10 );
add_action( 'marketplace_header', 'marketplace_header_middle', 20 );
add_action( 'marketplace_header', 'marketplace_header_nav', 30 );
add_action( 'marketplace_header', 'marketplace_header_mobile', 25 );
/**
 *
 * HOOK BLOG META
 */
/* POST INFO */
add_action( 'marketplace_post_info_content', 'marketplace_post_title', 10 );
add_action( 'marketplace_post_info_content', 'marketplace_post_content', 20 );
add_action( 'marketplace_post_info_content', 'marketplace_post_readmore', 30 );
add_action( 'marketplace_post_info_content', 'marketplace_post_meta', 40 );
/* POST META */
add_action( 'marketplace_post_meta_content', 'marketplace_post_sticky', 10 );
add_action( 'marketplace_post_meta_content', 'marketplace_post_calendar', 20 );
add_action( 'marketplace_post_meta_content', 'marketplace_post_author', 30 );
add_action( 'marketplace_post_meta_content', 'marketplace_post_tags', 40 );
add_action( 'marketplace_post_meta_content', 'marketplace_post_category', 50 );
/**
 *
 * HOOK BLOG GRID
 */
add_action( 'marketplace_after_blog_content', 'marketplace_paging_nav', 10 );
add_action( 'marketplace_post_content', 'marketplace_post_thumbnail', 10 );
add_action( 'marketplace_post_content', 'marketplace_post_info', 20 );
/**
 *
 * HOOK BLOG STANDARD
 */
add_action( 'marketplace_standard_post_content', 'marketplace_post_thumbnail', 10 );
add_action( 'marketplace_standard_post_content', 'marketplace_post_info', 20 );
/**
 *
 * HOOK BLOG SINGLE
 */
add_action( 'marketplace_single_post_content', 'marketplace_post_thumbnail', 10 );
add_action( 'marketplace_single_post_content', 'marketplace_post_info', 20 );
add_action( 'marketplace_single_post_content', 'marketplace_post_single_author', 30 );
/**
 * HOOK TEMPLATE
 */
add_filter( 'wp_nav_menu_items', 'marketplace_top_right_menu', 10, 2 );
add_filter( 'ovic_load_icon_json', 'marketplace_add_icon' );
add_filter( 'ovic_filter_like_icon', 'marketplace_like_icon' );
add_filter( 'ovic_filter_unlike_icon', 'marketplace_unlike_icon' );
?>
<?php
/**
 * HOOK TEMPLATE FUNCTIONS
 */
if ( !function_exists( 'marketplace_get_logo' ) ) {
	function marketplace_get_logo()
	{
		$logo_url = get_theme_file_uri( '/assets/images/logo.png' );
		$logo     = Marketplace_Functions::get_option( 'ovic_logo' );
		if ( $logo != '' ) {
			$logo_url = wp_get_attachment_image_url( $logo, 'full' );
		}
		$html = '<a href="' . esc_url( home_url( '/' ) ) . '"><img alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" src="' . esc_url( $logo_url ) . '" class="_rw" /></a>';
		echo apply_filters( 'marketplace_site_logo', $html );
	}
}
if ( !function_exists( 'marketplace_add_icon' ) ) {
	function marketplace_add_icon( $icon )
	{
		$icon[] = array(
			'name'  => 'Flaticon',
			'icons' => array(
				'flaticon-cart',
				'flaticon-heart',
				'flaticon-user',
				'flaticon-credit-card',
				'flaticon-support',
				'flaticon-protect',
				'flaticon-transport',
			),
		);

		return $icon;
	}
}
if ( !function_exists( 'marketplace_like_icon' ) ) {
	function marketplace_like_icon( $icon )
	{
		return $icon = '<i class="fa fa-thumbs-up"></i>';
	}
}
if ( !function_exists( 'marketplace_unlike_icon' ) ) {
	function marketplace_unlike_icon( $icon )
	{
		return $icon = '<i class="fa fa-thumbs-o-up"></i>';
	}
}
if ( !function_exists( 'marketplace_top_right_menu' ) ) {
	function marketplace_top_right_menu( $items, $args )
	{
		if ( $args->theme_location == 'top_right_menu' ) {
			$content = '';
			ob_start();
			do_action( 'ovic_header_language' );
			$content .= ob_get_clean();
			$content .= $items;
			$items   = $content;
		}

		return $items;
	}
}
/**
 *
 * TEMPLATE BLOG
 */
if ( !function_exists( 'marketplace_paging_nav' ) ) {
	function marketplace_paging_nav()
	{
		global $wp_query;
		$max = $wp_query->max_num_pages;
		// Don't print empty markup if there's only one page.
		if ( $max >= 2 ) {
			echo get_the_posts_pagination( array(
					'screen_reader_text' => '&nbsp;',
					'before_page_number' => '',
					'prev_text'          => esc_html__( 'Prev', 'marketplace' ),
					'next_text'          => esc_html__( 'Next', 'marketplace' ),
				)
			);
		}
	}
}
if ( !function_exists( 'marketplace_post_thumbnail' ) ) {
	function marketplace_post_thumbnail()
	{
		$ovic_blog_style = Marketplace_Functions::get_option( 'ovic_blog_list_style', 'grid' );
		$class           = 'post-thumb';
		if ( $ovic_blog_style != 'grid' ) {
			$width  = 1170;
			$height = 610;
		} else {
			$width  = 420;
			$height = 297;
		}
		if ( is_single() && !has_post_thumbnail() ) {
			$class .= ' no-thumb';
		}
		?>
        <div class="<?php echo esc_attr( $class ); ?>">
			<?php
			if ( is_single() ) {
				the_post_thumbnail( 'full' );
			} else {
				$image_thumb = apply_filters( 'ovic_resize_image', get_post_thumbnail_id(), $width, $height, true, true );
				echo '<a href="' . get_permalink() . '"><figure>';
				echo htmlspecialchars_decode( $image_thumb['img'] );
				echo '</figure></a>';
			}
			?>
			<?php if ( $ovic_blog_style != 'grid' || is_single() ) : ?>
                <div class="post-date">
                    <span class="date"><?php echo get_the_date( 'd' ); ?></span>
                    <span class="month"><?php echo get_the_date( 'M' ); ?></span>
                </div>
			<?php endif; ?>
        </div>
		<?php
		if ( $ovic_blog_style == 'grid' && !is_single() ) : ?>
            <div class="post-date">
                <span class="date"><?php echo get_the_date( 'd' ); ?></span>
                <span class="month"><?php echo get_the_date( 'M' ); ?></span>
            </div>
		<?php
		endif;
	}
}
if ( !function_exists( 'marketplace_callback_comment' ) ) {
	/**
	 * Marketplace comment template
	 *
	 * @param array $comment the comment array.
	 * @param array $args the comment args.
	 * @param int $depth the comment depth.
	 * @since 1.0.0
	 */
	function marketplace_callback_comment( $comment, $args, $depth )
	{
		if ( 'div' == $args['style'] ) {
			$tag       = 'div ';
			$add_below = 'comment';
		} else {
			$tag       = 'li ';
			$add_below = 'div-comment';
		}
		?>
        <<?php echo esc_attr( $tag ); ?><?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ) ?> id="comment-<?php echo get_comment_ID(); ?>">
        <div class="comment-body">
            <div class="comment-meta commentmetadata">
                <div class="comment-author vcard">
					<?php echo get_avatar( $comment, 128 ); ?>
					<?php printf( wp_kses_post( '<cite class="fn">%s</cite>', 'marketplace' ), get_comment_author_link() ); ?>
                </div>
				<?php if ( '0' == $comment->comment_approved ) : ?>
                    <em class="comment-awaiting-moderation"><?php esc_attr_e( 'Your comment is awaiting moderation.', 'marketplace' ); ?></em>
                    <br/>
				<?php endif; ?>
                <a href="<?php echo esc_url( htmlspecialchars( get_comment_link( get_comment_ID() ) ) ); ?>"
                   class="comment-date">
					<?php echo '<time datetime="' . get_comment_date( 'c' ) . '">' . get_comment_date() . '</time>'; ?>
                </a>
				<?php edit_comment_link( __( 'Edit', 'marketplace' ), '  ', '' ); ?>
				<?php do_action( 'ovic_comment_meta' ); ?>
            </div>
			<?php echo ( 'div' != $args['style'] ) ? '<div id="div-comment-' . get_comment_ID() . '" class="comment-content">' : '' ?>
            <div class="comment-text">
				<?php comment_text(); ?>
            </div>
            <div class="reply">
                <div class="reply-content">
                    <i class="fa fa-commenting"></i>
					<?php comment_reply_link( array_merge( $args, array( 'add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
                </div>
				<?php do_action( 'ovic_simple_likes_button', get_comment_ID(), 1 ); ?>
            </div>
			<?php echo 'div' != $args['style'] ? '</div>' : ''; ?>
        </div>
		<?php
	}
}
if ( !function_exists( 'marketplace_post_title' ) ) {
	function marketplace_post_title()
	{
		?>
        <h2 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<?php
	}
}
if ( !function_exists( 'marketplace_post_content' ) ) {
	function marketplace_post_content()
	{
		?>
        <div class="post-content">
			<?php echo wp_trim_words( apply_filters( 'the_excerpt', get_the_excerpt() ), 12, esc_html__( '...', 'marketplace' ) ); ?>
        </div>
		<?php
	}
}
if ( !function_exists( 'marketplace_post_single_content' ) ) {
	function marketplace_post_single_content()
	{
		?>
        <div class="post-content">
			<?php
			/* translators: %s: Name of current post */
			the_content( sprintf(
					esc_html__( 'Continue reading %s', 'marketplace' ),
					the_title( '<span class="screen-reader-text">', '</span>', false )
				)
			);
			wp_link_pages( array(
					'before'      => '<div class="post-pagination"><span class="title">' . esc_html__( 'Pages:', 'marketplace' ) . '</span>',
					'after'       => '</div>',
					'link_before' => '<span>',
					'link_after'  => '</span>',
				)
			);
			?>
        </div>
		<?php
	}
}
if ( !function_exists( 'marketplace_post_readmore' ) ) {
	function marketplace_post_readmore()
	{
		?>
        <div class="read-more">
            <a href="<?php the_permalink(); ?>">
				<?php echo esc_html__( 'Read more', 'marketplace' ); ?>
            </a>
        </div>
		<?php
	}
}
if ( !function_exists( 'marketplace_post_sticky' ) ) {
	function marketplace_post_sticky()
	{
		if ( is_sticky() ) : ?>
            <li class="sticky-post"><i class="fa fa-flag"></i>
				<?php echo esc_html__( ' Sticky', 'marketplace' ); ?>
            </li>
		<?php endif;
	}
}
if ( !function_exists( 'marketplace_post_calendar' ) ) {
	function marketplace_post_calendar()
	{
		?>
        <li class="date">
            <i class="fa fa-calendar" aria-hidden="true"></i>
			<?php echo get_the_date(); ?>
        </li>
		<?php
	}
}
if ( !function_exists( 'marketplace_post_author' ) ) {
	function marketplace_post_author()
	{
		?>
        <li class="author">
            <i class="fa fa-user" aria-hidden="true"></i>
            <span><?php echo esc_html__( 'By: ', 'marketplace' ) ?></span>
            <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) ?>">
				<?php the_author() ?>
            </a>
        </li>
		<?php
	}
}
if ( !function_exists( 'marketplace_post_tags' ) ) {
	function marketplace_post_tags()
	{
		$get_term_tag = get_the_terms( get_the_ID(), 'post_tag' );
		if ( !is_wp_error( $get_term_tag ) && !empty( $get_term_tag ) ) : ?>
            <li class="tags">
                <i class="fa fa-tags" aria-hidden="true"></i>
				<?php the_tags( '' ); ?>
            </li>
		<?php endif;
	}
}
if ( !function_exists( 'marketplace_post_category' ) ) {
	function marketplace_post_category()
	{
		$get_term_cat = get_the_terms( get_the_ID(), 'category' );
		if ( !is_wp_error( $get_term_cat ) && !empty( $get_term_cat ) ) : ?>
            <li class="category">
                <i class="fa fa-folder-open" aria-hidden="true"></i>
				<?php the_category(); ?>
            </li>
		<?php endif;
	}
}
if ( !function_exists( 'marketplace_post_meta' ) ) {
	function marketplace_post_meta()
	{ ?>
        <ul class="post-meta">
			<?php
			/**
			 * Functions hooked into marketplace_footer action
			 *
			 * @hooked marketplace_post_sticky              - 10
			 * @hooked marketplace_post_calendar            - 20
			 * @hooked marketplace_post_author              - 30
			 * @hooked marketplace_post_tags                - 40
			 * @hooked marketplace_post_category            - 50
			 */
			do_action( 'marketplace_post_meta_content' );
			?>
        </ul>
		<?php
	}
}
if ( !function_exists( 'marketplace_post_info' ) ) {
	function marketplace_post_info()
	{ ?>
        <div class="post-info">
			<?php
			/**
			 * Functions hooked into marketplace_post_info_content action
			 *
			 * @hooked marketplace_post_title               - 10
			 * @hooked marketplace_post_content             - 20
			 * @hooked marketplace_post_readmore            - 30
			 * @hooked marketplace_post_meta                - 40
			 */
			do_action( 'marketplace_post_info_content' );
			?>
        </div>
		<?php
	}
}
if ( !function_exists( 'marketplace_post_single_author' ) ) {
	function marketplace_post_single_author()
	{
		$enable_share_post = Marketplace_Functions::get_option( 'enable_share_post' );
		?>
        <div class="author-info">
            <div class="content-author">
                <div class="author">
					<?php echo get_avatar( get_the_author_meta( 'ID' ), 26 ); ?>
                    <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) ?>">
						<?php the_author() ?>
                    </a>
                </div>
                <div class="list">
                    <div class="item-view">
                        <i class="fa fa-eye"></i>
						<?php do_action( 'ovic_get_post_views', get_the_ID() ); ?>
                    </div>
                    <div class="item-comment">
                        <i class="fa fa-commenting"></i>
						<?php comments_number(
							esc_html__( '0', 'marketplace' ),
							esc_html__( '1', 'marketplace' ),
							esc_html__( '%', 'marketplace' )
						);
						?>
                    </div>
                </div>
            </div>
			<?php if ( $enable_share_post == 1 ) : ?>
                <div class="share">
                    <span class="title"><?php echo esc_html__( 'Share: ', 'marketplace' ); ?></span>
					<?php do_action( 'ovic_share_button', get_the_ID() ); ?>
                </div>
			<?php endif; ?>
        </div>
		<?php
	}
}
/**
 * TEMPLATE FOOTER
 */
if ( !function_exists( 'marketplace_footer_top' ) ) {
	function marketplace_footer_top()
	{
		$ovic_footer_grid = Marketplace_Functions::get_option( 'ovic_grid_footer', '' );
		if ( !empty( $ovic_footer_grid ) ): ?>
            <div class="top-footer">
                <div class="container">
                    <div class="footer-inner">
                        <div class="row">
							<?php foreach ( $ovic_footer_grid as $item ) :
								$column = array( 'col-lg-' . $item['ovic_footer_column'] );
								$column[] = 'col-md-' . $item['ovic_footer_column_ipad'];
								$column[] = 'col-sm-' . $item['ovic_footer_column_mobile'];
								if ( is_active_sidebar( $item['ovic_footer_sidebar'] ) ) : ?>
                                    <div class="<?php echo implode( ' ', $column ); ?>">
										<?php dynamic_sidebar( $item['ovic_footer_sidebar'] ); ?>
                                    </div>
								<?php endif; ?>
							<?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
		<?php endif;
	}
}
if ( !function_exists( 'marketplace_footer_middle' ) ) {
	function marketplace_footer_middle()
	{
		$mid_footer_sidebar = Marketplace_Functions::get_option( 'mid_footer_sidebar', '' );
		if ( is_active_sidebar( $mid_footer_sidebar ) ) : ?>
            <div class="middle-footer">
                <div class="container">
                    <div class="footer-inner">
						<?php dynamic_sidebar( $mid_footer_sidebar ); ?>
                    </div>
                </div>
            </div>
		<?php endif;
	}
}
if ( !function_exists( 'marketplace_footer_bottom' ) ) {
	function marketplace_footer_bottom()
	{
		$bottom_footer_sidebar_left  = Marketplace_Functions::get_option( 'bottom_footer_sidebar_left', '' );
		$bottom_footer_sidebar_right = Marketplace_Functions::get_option( 'bottom_footer_sidebar_right', '' );
		if ( is_active_sidebar( $bottom_footer_sidebar_left ) || is_active_sidebar( $bottom_footer_sidebar_right ) ) : ?>
            <div class="bottom-footer">
                <div class="container">
                    <div class="footer-inner">
                        <div class="row">
							<?php if ( is_active_sidebar( $bottom_footer_sidebar_left ) ) : ?>
                                <div class="col-md-6">
									<?php dynamic_sidebar( $bottom_footer_sidebar_left ); ?>
                                </div>
							<?php endif; ?>
							<?php if ( is_active_sidebar( $bottom_footer_sidebar_right ) ) : ?>
                                <div class="col-md-6">
									<?php dynamic_sidebar( $bottom_footer_sidebar_right ); ?>
                                </div>
							<?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
		<?php endif;
	}
}
if ( !function_exists( 'marketplace_footer_mobile' ) ) {
	function marketplace_footer_mobile()
	{
		$myaccount_link = wp_login_url();
		if ( class_exists( 'WooCommerce' ) ) {
			$myaccount_link = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
		}
		?>
        <div class="mobile-footer is-sticky">
            <div class="mobile-footer-inner">
                <div class="mobile-block">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <span class="fa fa-home icon" aria-hidden="true"></span>
                        <span class="text"><?php echo esc_html__( 'Home', 'marketplace' ); ?></span>
                    </a>
                </div>
				<?php if ( class_exists( 'YITH_Woocompare' ) ) :
					global $yith_woocompare; ?>
                    <div class="mobile-block mobile-block-compare">
                        <a href="<?php echo add_query_arg( array( 'iframe' => 'true' ), $yith_woocompare->obj->view_table_url() ) ?>"
                           class="compare added" rel="nofollow">
                            <span class="fa fa-bar-chart icon"></span>
                            <span class="text"><?php echo esc_html__( 'Compare', 'marketplace' ); ?></span>
                        </a>
                    </div>
				<?php endif; ?>
				<?php if ( defined( 'YITH_WCWL' ) ) :
					$yith_wcwl_wishlist_page_id = get_option( 'yith_wcwl_wishlist_page_id' );
					$wishlist_url = get_page_link( $yith_wcwl_wishlist_page_id );
					if ( $wishlist_url != '' ) : ?>
                        <div class="mobile-block mobile-block-wishlist">
                            <a class="woo-wishlist-link" href="<?php echo esc_url( $wishlist_url ); ?>">
                            <span class="fa fa-heart icon">
                                <span class="count"><?php echo YITH_WCWL()->count_products(); ?></span>
                            </span>
                                <span class="text"><?php echo esc_html__( 'Wishlist', 'marketplace' ); ?></span>
                            </a>
                        </div>
					<?php endif;
				endif; ?>
				<?php if ( class_exists( 'WooCommerce' ) ) : ?>
                    <div class="mobile-block mobile-block-minicart">
                        <a class="link-dropdown" href="<?php echo wc_get_cart_url(); ?>">
                        <span class="fa fa-shopping-bag icon">
                            <span class="count"><?php echo WC()->cart->cart_contents_count; ?></span>
                        </span>
                            <span class="text"><?php echo esc_html__( 'Cart', 'marketplace' ); ?></span>
                        </a>
                    </div>
				<?php endif; ?>
                <div class="mobile-block mobile-block-userlink">
                    <a data-ovic="ovic-dropdown" class="woo-wishlist-link"
                       href="<?php echo esc_url( $myaccount_link ); ?>">
                        <span class="fa fa-user icon" aria-hidden="true"></span>
                        <span class="text"><?php echo esc_html__( 'Account', 'marketplace' ); ?></span>
                    </a>
                </div>
                <div class="mobile-block block-menu-bar">
                    <a href="#" class="menu-bar menu-toggle">
                    <span class="icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                        <span class="text"><?php echo esc_html__( 'Menu', 'marketplace' ); ?></span>
                    </a>
                </div>
            </div>
        </div>
		<?php
	}
}
/**
 * TEMPLATE HEADER
 */
if ( !function_exists( 'marketplace_header_top' ) ) {
	function marketplace_header_top()
	{
		?>
        <div class="header-top">
            <div class="container">
                <div class="header-top-inner">
					<?php
					if ( has_nav_menu( 'top_left_menu' ) ) {
						wp_nav_menu( array(
								'menu'            => 'top_left_menu',
								'theme_location'  => 'top_left_menu',
								'depth'           => 2,
								'container'       => '',
								'container_class' => '',
								'container_id'    => '',
								'menu_class'      => 'marketplace-nav top-bar-menu',
							)
						);
					}
					if ( has_nav_menu( 'top_right_menu' ) ) {
						wp_nav_menu( array(
								'menu'            => 'top_right_menu',
								'theme_location'  => 'top_right_menu',
								'depth'           => 2,
								'container'       => '',
								'container_class' => '',
								'container_id'    => '',
								'menu_class'      => 'marketplace-nav top-bar-menu right',
							)
						);
					}
					?>
                </div>
            </div>
        </div>
		<?php
	}
}
if ( !function_exists( 'marketplace_user_link' ) ) {
	function marketplace_user_link()
	{
		$myaccount_link = wp_login_url();
		$currentUser    = wp_get_current_user();
		if ( class_exists( 'WooCommerce' ) ) {
			$myaccount_link = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
		}
		?>
        <div class="menu-item block-userlink ovic-dropdown">
			<?php if ( is_user_logged_in() ): ?>
                <a data-ovic="ovic-dropdown" class="woo-wishlist-link"
                   href="<?php echo esc_url( $myaccount_link ); ?>">
                    <span class="fa fa-user icon" aria-hidden="true"></span>
                    <span class="text"><?php echo esc_html( $currentUser->display_name ); ?></span>
                </a>
				<?php if ( function_exists( 'wc_get_account_menu_items' ) ): ?>
                    <ul class="sub-menu">
						<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
                            <li class="menu-item <?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
                                <a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
                            </li>
						<?php endforeach; ?>
                    </ul>
				<?php else: ?>
                    <ul class="sub-menu">
                        <li class="menu-item">
                            <a href="<?php echo wp_logout_url( get_permalink() ); ?>"><?php esc_html_e( 'Logout', 'marketplace' ); ?></a>
                        </li>
                    </ul>
				<?php endif;
			else: ?>
                <a class="woo-wishlist-link" href="<?php echo esc_url( $myaccount_link ); ?>">
                    <span class="fa fa-user icon"></span>
                    <span class="text"><?php echo esc_html__( 'Login', 'marketplace' ); ?></span>
                </a>
			<?php endif; ?>
        </div>
		<?php
	}
}
if ( !function_exists( 'marketplace_header_middle' ) ) {
	function marketplace_header_middle()
	{
		?>
        <div class="header-middle">
            <div class="container">
                <div class="header-middle-inner">
                    <div class="logo">
						<?php marketplace_get_logo(); ?>
                    </div>
                    <div class="header-control">
						<?php
						do_action( 'ovic_search_form' );
						marketplace_user_link();
						if ( defined( 'YITH_WCWL' ) ) :
							$yith_wcwl_wishlist_page_id = get_option( 'yith_wcwl_wishlist_page_id' );
							$wishlist_url = get_page_link( $yith_wcwl_wishlist_page_id );
							if ( $wishlist_url != '' ) : ?>
                                <div class="block-wishlist">
                                    <a class="woo-wishlist-link" href="<?php echo esc_url( $wishlist_url ); ?>">
                                        <span class="fa fa-heart icon"></span>
                                        <span class="count"><?php echo YITH_WCWL()->count_products(); ?></span>
                                    </a>
                                </div>
							<?php endif;
						endif;
						do_action( 'marketplace_header_mini_cart' );
						?>
                        <div class="block-menu-bar">
                            <a class="menu-bar menu-toggle" href="#">
                                <span></span>
                                <span></span>
                                <span></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}
}
if ( !function_exists( 'marketplace_header_nav' ) ) {
	function marketplace_header_nav()
	{
		?>
        <div class="header-nav">
            <div class="container">
                <div class="header-nav-inner">
					<?php do_action( 'ovic_header_vertical', 'vertical_menu', true ); ?>
                    <div class="box-header-nav main-menu-wapper">
						<?php
						if ( has_nav_menu( 'primary' ) ) {
							wp_nav_menu( array(
									'menu'            => 'primary',
									'theme_location'  => 'primary',
									'depth'           => 3,
									'container'       => '',
									'container_class' => '',
									'container_id'    => '',
									'menu_class'      => 'clone-main-menu marketplace-nav main-menu',
									'mobile_enable'   => true,
								)
							);
						}
						?>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}
}
if ( !function_exists( 'marketplace_header_language' ) ) {
	function marketplace_header_language()
	{
		$list_language = '';
		$menu_language = '';
		$languages     = apply_filters( 'wpml_active_languages', NULL, 'skip_missing=0' );
		if ( !empty( $languages ) ) {
			foreach ( $languages as $l ) {
				if ( !$l['active'] ) {
					$list_language .= '
						<li>
                            <a href="' . esc_url( $l['url'] ) . '">
                                <img src="' . esc_url( $l['country_flag_url'] ) . '" height="12"
                                     alt="' . esc_attr( $l['language_code'] ) . '" width="18"/>
								' . esc_html( $l['native_name'] ) . '
                            </a>
                        </li>';
				}
			}
			$menu_language = '
                 <li class="menu-item block-language">
                    <h4 class="title">' . esc_html__( 'Language:', 'marketplace' ) . '</h4>
                    <ul>' . $list_language . '</ul>
                </li>';
			echo '<li class="menu-item block-currency">
                    <h4 class="title">' . esc_html__( 'Currency:', 'marketplace' ) . '</h4>';
			do_action( 'wcml_currency_switcher', array( 'format' => '%code%', 'switcher_style' => 'wcml-dropdown' ) );
			echo '</li>';
		}
		echo htmlspecialchars_decode( $menu_language );
	}
}
if ( !function_exists( 'marketplace_header_mobile' ) ) {
	function marketplace_header_mobile()
	{
		?>
        <div class="header-mobile">
            <div class="container">
                <div class="header-mobile-inner">
                    <div class="logo">
						<?php marketplace_get_logo(); ?>
                    </div>
                    <div class="header-control">
                        <div class="header-settings ovic-dropdown">
                            <a href="#" data-ovic="ovic-dropdown">
                                <span class="fa fa-arrow-down" aria-hidden="true"></span>
                            </a>
                            <ul class="sub-menu">
                                <li class="settings-block search">
                                    <h4 class="title"><?php echo esc_html__( 'Search:', 'marketplace' ) ?></h4>
									<?php do_action( 'ovic_search_form' ); ?>
                                </li>
								<?php marketplace_header_language(); ?>
                            </ul>
                        </div>
                        <div class="block-menu-bar">
                            <a class="menu-bar menu-toggle" href="#">
                                <span></span>
                                <span></span>
                                <span></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}
}
if ( !function_exists( 'marketplace_header_sticky' ) ) {
	function marketplace_header_sticky()
	{
		$enable_sticky_menu = Marketplace_Functions::get_option( 'ovic_sticky_menu' );
		if ( $enable_sticky_menu == 1 ): ?>
            <div class="header-sticky">
                <div class="container">
                    <div class="header-nav-inner">
						<?php do_action( 'ovic_header_vertical', 'vertical_menu' ); ?>
                        <div class="box-header-nav main-menu-wapper">
							<?php
							if ( has_nav_menu( 'primary' ) ) {
								wp_nav_menu( array(
										'menu'            => 'primary',
										'theme_location'  => 'primary',
										'depth'           => 3,
										'container'       => '',
										'container_class' => '',
										'container_id'    => '',
										'menu_class'      => 'clone-main-menu marketplace-nav main-menu',
									)
								);
							}
							?>
                        </div>
                        <div class="header-control">
							<?php do_action( 'marketplace_header_mini_cart' ); ?>
                        </div>
                    </div>
                </div>
            </div>
		<?php endif;
	}
}