<?php
/**
 * Linked product options.
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="linked_product_data" class="wkmp_profile_input">

	<div class="options_group show_if_grouped">
		<p class="form-field">
				<label for="grouped_products"><?php esc_html_e( 'Grouped products', 'marketplace' ); ?></label>
				<select class="wc-product-search" multiple="multiple" style="width: 50%;" name="mp_grouped_products[]" data-sortable="true" data-placeholder="<?php esc_attr_e( 'Search&hellip;', 'marketplace' ); ?>">
					<?php
					$product_ids = $product->is_type( 'grouped' ) ? $product->get_children( 'edit' ) : array();

					foreach ( $product_array as $key => $value ) {
						$item = wc_get_product( $value->ID );
						if ( is_object( $item ) && $wk_pro_id != $value->ID ) {
						?>
						<option value="<?php echo $value->ID; ?>" <?php if ( in_array( $value->ID, $product_ids ) ) { echo 'selected'; } ?>>
							<?php echo wp_kses_post( $item->get_formatted_name() ); ?>
						</option>
							<?php
						}
					}
						?>
				</select>
		</p>
	</div>
</div>
