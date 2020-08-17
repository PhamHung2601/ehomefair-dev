<?php

/**
 * Used Codes Listing
 * 
 * Template for Used Codes Listing
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.8.1
 **/

global $woo_vou_model;

//model class
$model = $woo_vou_model;

$prefix = WOO_VOU_META_PREFIX;

$page = isset( $_POST['paging'] ) ? $_POST['paging'] : '1';

$generatepdfurl_args = array(
	'woo-vou-voucher-gen-pdf'	=> '1',
	'woo_vou_action'			=> 'unused',
	'vou-data'					=> 'expired',
);

$generatecsvurl_args = array(
	'woo-vou-voucher-exp-csv'	=> '1',
	'woo_vou_action'			=> 'unused',
	'vou-data'					=> 'expired',
);

if( isset( $_GET['woo_vou_post_id'] ) && !empty( $_GET['woo_vou_post_id'] ) ) {

	$generatepdfurl_args['woo_vou_post_id']	= $_GET['woo_vou_post_id'];
	$generatecsvurl_args['woo_vou_post_id']	= $_GET['woo_vou_post_id'];
}

if( isset( $_GET['woo_vou_partial_used_voucode'] ) && !empty( $_GET['woo_vou_partial_used_voucode'] ) ) {
	$generatepdfurl_args['woo_vou_partial_used_voucode']	= $_GET['woo_vou_partial_used_voucode'];
	$generatecsvurl_args['woo_vou_partial_used_voucode']	= $_GET['woo_vou_partial_used_voucode'];
}
	
$generatepdfurl = add_query_arg( $generatepdfurl_args );
$generatecsvurl = add_query_arg( $generatecsvurl_args );

?>

<!-- Get generate pdf button -->
<a href="<?php echo esc_url($generatecsvurl); ?>" id="woo-vou-export-csv-btn" class="woo-vou-btn-front woo-vou-grncsv-btn" title="<?php echo esc_html__('Export CSV','woovoucher'); ?>"><?php echo esc_html__( 'Export CSV', 'woovoucher' ); ?></a>
<a href="<?php echo esc_url($generatepdfurl); ?>" id="woo-vou-pdf-btn" class="woo-vou-btn-front woo-vou-grnpdf-btn" title="<?php echo esc_html__('Generate PDF','woovoucher'); ?>"><?php echo esc_html__( 'Generate PDF', 'woovoucher' ); ?></a>

<!-- hidden data to get in ajax -->
<input type="hidden" id="woo_vou_product_filter" value="<?php echo isset( $_GET['woo_vou_post_id'] ) ? $_GET['woo_vou_post_id'] : ''; ?>">
<input type="hidden" class="wpw-fp-bulk-paging" value="<?php echo $page; ?>" />

<div class="woo-vou-clear" ></div>

<!-- Table formation starts -->
<table class="woo-vou-unused-codes-table wp-list-table widefat fixed striped purchasedvous">
	<thead>
		<tr class="woo-vou-unused-codes-table-row-head">
				<?php 
						//do action to add header title of orders list before
						do_action('woo_vou_unused_codes_header_before');
				?>
				<th width="12%" scope="col" id='code' class="manage-column column-code column-primary sortable asc"><?php esc_html_e( 'Voucher Code','woovoucher' );?></th>
				<th width="18%" scope="col" id='product_info' class="manage-column column-product_info"><?php esc_html_e( 'Product Information','woovoucher' );?></th>
				<th width="25%" scope="col" id='buyers_info' class="manage-column column-buyers_info"><?php esc_html_e( "Buyer's Information",'woovoucher' );?></th>
				<th width="25%" scope="col" id='order_info' class="manage-column column-order_info"><?php esc_html_e( 'Order Information','woovoucher' );?></th>
				<th width="10%" scope="col" id='code_details' class="manage-column column-code_details"><?php esc_html_e( 'View Details','woovoucher' );?></th>
				<?php 
						//do action to add header title of orders list after
						do_action('woo_vou_unused_codes_header_after');
				?>
		</tr>
	</thead>
	
	<tbody id="the-list" data-wp-lists='list:purchasedvou'>
	<?php	
	if( empty( $result_arr ) ) {
		echo "<tr><td colspan='5' class='woo-vou-no-record-message'>" . esc_html__( 'No expired voucher codes yet.','woovoucher' ) . "</td></tr>";
	} else {
		foreach ( $result_arr as $key => $value ) {	

			$current_page_url = !empty( $_POST['current_page_url'] ) ? $_POST['current_page_url'] : '';
			$vou_code_detail_page_url   = add_query_arg( array( 'vou_code' => $value['ID'] ), $current_page_url);
			$order = wc_get_order($value['order_id']);
		?>
			<tr class="woo-vou-unused-codes-row-body">
				<?php 
						//do action to add row for orders list before
						do_action( 'woo_vou_unused_codes_row_before', $value ); 
				?>
				<td class="woo-vou-unused-code-list-codes code column-code has-row-actions column-primary"  data-colname="Voucher Code"><?php 	echo $value['code']; ?> <button type="button" class="toggle-row"><span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'woovoucher' ); ?></span></button></td>
				<?php 
				
				if( empty( $order ) ) { ?>
				<td colspan="4" class="woo-vou-unused-code-list-codes code column-code has-row-actions column-primary order-deleted-column"  data-colname="Voucher Code"><?php esc_html_e('Order of this voucher code doesn\'t exist.','woovoucher')?></td>
				<?php 
				}
				else{
				?>
				<td class='product_info column-product_info' data-colname="Product Information"><?php	echo woo_vou_display_product_info_html( $value['order_id'], $value['code'] );		?></td>
				<td class='product_info column-product_info' data-colname="Buyer's Information"><?php	echo woo_vou_display_buyer_info_html( $value['buyers_info'] ); ?></td>
				<td class='product_info column-product_info' data-colname="Order Information"><?php	echo woo_vou_display_order_info_html( $value['order_id'] ); ?></td>
				<td class="code_details column-code_details" data-colname="View Details">
					<a href="<?php echo esc_url($vou_code_detail_page_url);?>" class="woo-vou-code-detailview" target="_blank"><?php esc_html_e( 'View','woovoucher' );?></a>
					<?php

						$enable_partial_redeem 			= get_option( 'vou_enable_partial_redeem' );
						$vou_partial_redeem_product_ids = get_option('vou_partial_redeem_product_ids');
						
						if( ( $enable_partial_redeem == "yes" ) || ( ($enable_partial_redeem != "yes") && !empty($vou_partial_redeem_product_ids) ) ) {

							// Get redeem method meta
							$redeem_method = get_post_meta( $value['ID'], $prefix.'redeem_method', true );
				
							// If redeem method is not empty and set to partial
							if( !empty( $redeem_method ) && $redeem_method == 'partial' ) {

								echo '<a href="' . esc_url($vou_code_detail_page_url) . '" class="woo-vou-code-detailview" target="_blank"><div class="woo-vou-vou-code-partial-used">'. esc_html__( 'Partially Used', 'woovoucher' ) .'</div></a>';
							} 
						}
					?>
				</td>
				<?php } // if order found

						//do action to add row for orders list after
						do_action( 'woo_vou_unused_codes_row_after', $value ); 
				?>
			</tr>
	<?php	} }  ?>
	</tbody>
	<tfoot>
		<tr class="woo-vou-unused-codes-row-foot">
			<?php 
					//do action to add row in footer before
					do_action('woo_vou_unused_codes_footer_before');
			?>
			<th width="12%" scope="col" id='code' class="manage-column column-code column-primary sortable asc"><?php esc_html_e( 'Voucher Code','woovoucher' );?></th>
			<th width="18%" scope="col" id='product_info' class="manage-column column-product_info"><?php esc_html_e( 'Product Information','woovoucher' );?></th>
			<th width="25%" scope="col" id='buyers_info' class="manage-column column-buyers_info"><?php esc_html_e( "Buyer's Information",'woovoucher' );?></th>
			<th width="25%" scope="col" id='order_info' class="manage-column column-order_info"><?php esc_html_e( 'Order Information','woovoucher' );?></th>
			<th width="10%" scope="col" id='code_details' class="manage-column column-code_details"><?php esc_html_e( 'View Details','woovoucher' );?></th>
			<?php 
					//do action to add row in footer after
					do_action('woo_vou_unused_codes_footer_after');
			?>
		</tr>
	</tfoot>
</table>
<!-- Code for paging starts -->
<div class="woo-vou-paging woo-vou-unused-codes-paging">
	<div id="woo-vou-tablenav-pages" class="woo-vou-tablenav-pages">
		<?php echo $paging->getOutput(); ?>
	</div><!--.woo-vou-tablenav-pages-->
</div>
<!-- Code for paging ends -->
<div class="woo-vou-unused-codes-loader woo-vou-unusedcodes-loader">
	<img src="<?php echo esc_url(WOO_VOU_IMG_URL);?>/loader.gif"/>
</div><!--.woo-vou-unusedcodes-loader-->