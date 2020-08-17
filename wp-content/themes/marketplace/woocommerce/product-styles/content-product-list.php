<?php
/**
 * Name: Product list
 * Slug: content-product-list
 **/
?>
<?php

global $product, $post;

$availability = $product->get_availability();

remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
add_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_rating', 1 );
add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 30 );
?>
    <div class="product-inner">
		<?php
		/**
		 * woocommerce_before_shop_loop_item hook.
		 *
		 * @removed woocommerce_template_loop_product_link_open - 10
		 */
		do_action( 'woocommerce_before_shop_loop_item' );
		?>
        <div class="product-thumb">
			<?php
			/**
			 * woocommerce_before_shop_loop_item_title hook.
			 *
			 * @hooked ovic_woocommerce_group_flash - 10
			 * @hooked ovic_template_loop_product_thumbnail - 10
			 */
			do_action( 'woocommerce_before_shop_loop_item_title' );
			?>
        </div>
        <div class="product-info">
			<?php
			/**
			 * woocommerce_shop_loop_item_title hook.
			 *
			 * @hooked ovic_template_loop_product_title - 10
			 */
			do_action( 'woocommerce_shop_loop_item_title' );
			?>
            <div class="excerpt-content">
				<?php echo get_the_excerpt(); ?>
            </div>
			<?php if ( !empty( $availability['availability'] ) ) : ?>
                <p class="<?php echo esc_attr( 'stock available-product ' . $availability['class'] ); ?>">
					<?php echo esc_html__( 'Availability:', 'marketplace' ); ?>
                    <span> <?php echo esc_html( $availability['availability'] ); ?></span>
                </p>
			<?php endif; ?>
			<?php
			/**
			 * woocommerce_after_shop_loop_item_title hook.
			 *
			 * @hooked woocommerce_template_loop_rating - 5
			 * @hooked woocommerce_template_loop_price - 10
			 */
			do_action( 'woocommerce_after_shop_loop_item_title' );
			?>
            <div class="add-to-cart">
				<?php
				/**
				 * woocommerce_after_shop_loop_item hook.
				 *
				 * @removed woocommerce_template_loop_product_link_close - 5
				 * @hooked woocommerce_template_loop_add_to_cart - 10
				 */
				do_action( 'woocommerce_after_shop_loop_item' );
				?>
            </div>
            <div class="group-button">
				<?php do_action( 'ovic_function_shop_loop_item_quickview' ); ?>
				<?php do_action( 'ovic_function_shop_loop_item_wishlist' ); ?>
				<?php do_action( 'ovic_function_shop_loop_item_compare' ); ?>
            </div>
        </div>
    </div>
<?php
remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_rating', 1 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 30 );
add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
?>