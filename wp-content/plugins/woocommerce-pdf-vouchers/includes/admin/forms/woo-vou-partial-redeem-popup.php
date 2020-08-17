<?php
// Get all product have enable vocuher option
$pro_total_args = array(
    'post_type'      => 'product',
    'posts_per_page' => 20,
    'orderby' => array( 'post_date_gmt' => 'ASC' ),
    'meta_query'	 => array( 
    	array(
	    	'key' => '_woo_vou_enable',
	    	'value' => 'yes',
	    	'compare' => '='
    	)
     )
);
$products 		= new WP_Query( $pro_total_args );

// Get product and products variation total
$pro_total 		= $products->post_count;
$products_total = $pro_total;

$vou_partial_redeem_product_ids 		= get_option('vou_partial_redeem_product_ids');
$vou_partial_redeem_product_ids_array 	= !empty( $vou_partial_redeem_product_ids ) ? explode( ',', $vou_partial_redeem_product_ids ) : array();
?>
<!-- listing all post related post -->
<div class="woo-vou-popup-content woo-vou-product-partial-codes-popup">

	<div class="woo-vou-header">
		<div class="woo-vou-header-title">
			<?php 
				esc_html_e( 'Products', 'woovoucher' );
				echo wc_help_tip(esc_html__('List of Voucher enabled products.', 'woovoucher'));
			?>
		</div>
		<div class="woo-vou-popup-close"><a href="javascript:void(0);" class="woo-vou-close-button"><img src="<?php echo esc_url(WOO_VOU_URL) .'includes/images/tb-close.png'; ?>" alt="<?php esc_attr_e( 'Close','woovoucher' ); ?>"></a></div>
	</div>

	<div class="woo-vou-file-errors"><?php echo sprintf( esc_html__( 'Please uncheck %1s Enable Partial Redemption %2s to enable partial redeem at product level.', 'woovoucher' ), '<strong>', '</strong>' ); ?></div>
	<div class="woo_vou_product_partial_submit">
		<input type="hidden" name="woo_vou_selected_products" id="woo_vou_selected_products" value="<?php echo $vou_partial_redeem_product_ids; ?>">
		<input type="hidden" name="woo_vou_total_selected_products" id="woo_vou_total_selected_products" value="<?php echo count($vou_partial_redeem_product_ids_array); ?>">
		<input type="hidden" name="woo_vou_current_page" id="woo_vou_current_page" value="1">
		<div class="woo-vou-search-products">
			<input type="text" name="woo_vou_search_product_by" id="woo_vou_search_product_by" placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woovoucher' ); ?>">
			<input type="button" value="<?php esc_attr_e( 'Search', 'woovoucher' ); ?>" id="woo_vou_search_product_by_btn" class="button-primary">
		</div>
		<div class="woo-vou-right-column">
			<a href="javascript:void(0);" class="woo_vou_checkall_products"><?php esc_html_e( 'Check all', 'woovoucher' ); ?></a> /
			<a href="javascript:void(0);" class="woo_vou_uncheckall_products"><?php esc_html_e( 'Uncheck all', 'woovoucher' ); ?></a>
			<input type="button" value="<?php esc_attr_e( 'Done', 'woovoucher' ); ?>" id="woo_vou_set_submit_indivisual" class="button-primary">
		</div>
	</div><!-- .woo_vou_product_partial_submit -->

	<div class="woo-vou-popup">
		<div class="woo-vou-product-list">
			<ul class="woo_vou_product_partial_list">
				<?php
				if ( $products->have_posts() ) {
					$product_counter = 1;
					$products_list_2 = false;
	
					// get product loop
					while ( $products->have_posts() ) { $products->the_post();
	
						// If current product counter is greater-than half total product
						if( ( ( $products_total/2 ) < $product_counter ) && ( $products_list_2 == false ) ){
	
							$products_list_2 = true;
							echo '</ul><ul class="woo_vou_product_partial_list">';
						}
	
						$posttitle = $products->post->post_title;
						if( strlen( $posttitle ) > 35 ) {
	
							$posttitle = substr( $posttitle, 0, 35 );
							$posttitle = $posttitle.'...';
						}
	
						$product_data = wc_get_product( $products->post->ID );
	
						if( $product_data->is_type( 'variable' ) ) {
							$product_get_children = $product_data->get_children();
							
							if( !empty($product_get_children) ){ 
								$product_counter++;
							?>
								<li>
								    <input type="checkbox" class="woo-vou-product-partial-input woo-vou-product-variation-parent" value="<?php echo $products->post->ID; ?>" />
									<a class="woo-vou-variable-parent" href="<?php echo get_edit_post_link($products->post->ID); ?>" target="_blank"><?php echo '#' . $products->post->ID . ' - ' . esc_html__($posttitle,'woovoucher'); ?></a>
									<p class="woo-vou-variation-wrapper">
										<span class="woo-vou-toggle-variations woo-vou-plus"></span>										
									</p>
									<ul class="woo-vou-product-variation-list">
										<?php // show product variation
	
										foreach($product_get_children as $product_variation_id){

											$_variation_pro = wc_get_product($product_variation_id);
											$checked = ( in_array( $product_variation_id, $vou_partial_redeem_product_ids_array ) ) ? 'checked="checked"' : '';
											$posttitle = $_variation_pro->get_name();
											if( strlen( $posttitle ) > 35 ) {
												$posttitle = substr( $posttitle, 0, 35 );
												$posttitle = $posttitle.'...';
											}
											?><li>
												<input type="checkbox" class="woo-vou-product-partial-input woo-vou-product-variation woo-vou-product-parent-<?php echo $products->post->ID; ?>" id="woo_vou_product_partial_<?php echo $product_variation_id; ?>" name="woo_vou_product_partial[]" value="<?php echo $product_variation_id; ?>" <?php echo $checked; ?> />
												<label for="woo_vou_product_partial_<?php echo $product_variation_id;?>"><?php echo '#' . $product_variation_id . ' - ' . $posttitle; ?></label>
											</li>
											<?php
										} ?>
									</ul>
								</li>
							<?php wp_reset_postdata();
							}
						} else {
	
							$checked = ( in_array( $products->post->ID, $vou_partial_redeem_product_ids_array ) ) ? 'checked="checked"' : '';
							?>
	
							<li>
								<input type="checkbox" class="woo-vou-product-partial-input" id="woo_vou_product_partial_<?php echo $products->post->ID; ?>" name="woo_vou_product_partial[]" value="<?php echo $products->post->ID; ?>" <?php echo $checked; ?> />
								<label for="woo_vou_product_partial_<?php echo $products->post->ID;?>"><a href="<?php echo get_edit_post_link($products->post->ID); ?>" target="_blank"><?php echo '#' . $products->post->ID . ' - ' . esc_html__($posttitle,'woovoucher'); ?></a></label>
							</li><?php	
							$product_counter++;
						}
					}
				} else { ?>
					<li><?php esc_html_e( 'No Products.', 'woovoucher' ); ?></li><?php	
				}
				wp_reset_query(); ?>
			</ul>
		</div>
	</div><!--woo-vou-popup-->
	<div class="woo-vou-load-more-wrap">
		<input type="button" class="woo-vou-load-more-btn button-primary" value="<?php esc_attr_e( 'Load More', 'woovoucher' ); ?>" id="woo_vou_load_more_btn">
	</div>
	<div class="woo-vou-partial-redeem-popup-loader"><img src="<?php echo esc_url(WOO_VOU_IMG_URL).'/ajax-loader-2.gif' ?>"></div>
	<div class="woo-vou-partial-redeem-popup-overlay"></div>
</div><!--woo-vou-popup-content woo-vou-purchased-codes-popup_-->
<div class="woo-vou-popup-overlay woo-vou-product-partial-codes-popup"></div>