<?php 
	
/**
 * Used Voucher Codes Template
 * 
 * Handles to return and display data for used voucher codes
 * 
 * Override this template by copying it to yourtheme/woocommerce/woocommerce-pdf-vouchers/voucher-codes/woo-vou-used-codes.php
 *
 * @package WooCommerce - PDF Vouchers
 * @since 2.8.1
 */
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if ( ! is_user_logged_in() ) { //check user is logged in or not

	echo esc_html__( 'You need to be logged in to your account to see your redeemed voucher codes.', 'woocommerce' );
	return; 
}

global $product, $current_user, $woo_vou_vendor_role, $woo_vou_model, $woo_vou_voucher;

$prefix = WOO_VOU_META_PREFIX;

//Get user role
$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
$user_role	= array_shift( $user_roles );

//voucher admin roles
$admin_roles	= woo_vou_assigned_admin_roles();

// Get option whether to allow all logged user to voucher codes page
$vou_enable_logged_user_check_voucher_code = get_option('vou_enable_logged_user_check_voucher_code');
if( !in_array( $user_role, $woo_vou_vendor_role )  && !in_array( $user_role, $admin_roles )
	&& ( empty($vou_enable_logged_user_check_voucher_code) || $vou_enable_logged_user_check_voucher_code == 'no' ) ) {

	echo esc_html__( 'Sorry, you are not allowed to access redeemed voucher codes.', 'woovoucher' );
	return; 
}

$vou_enable_vendor_access_all_voucodes = get_option('vou_enable_vendor_access_all_voucodes');
$args['meta_query'] = array(
							array(
									'key'		=> $prefix.'used_codes',
									'value'		=> '',
									'compare'	=> '!=',
								)
							);

//Get user role
$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
$user_role	= array_shift( $user_roles );

if( in_array( $user_role, $woo_vou_vendor_role ) && 
	( empty( $vou_enable_vendor_access_all_voucodes ) || $vou_enable_vendor_access_all_voucodes == 'no' ) ) { // Check vendor user role
	$args['author']	= $current_user->ID;
}

$args = apply_filters('woo_vou_used_product_list', $args);

$products_data	= woo_vou_get_products_by_voucher( $args );
$users_data		= $woo_vou_voucher->woo_vou_get_redeem_users_by_voucher( $args );

$woo_vou_start_date 	= isset( $_GET['woo_vou_start_date'] ) ? sanitize_text_field( $_GET['woo_vou_start_date'] ) : null;
$woo_vou_end_date   	= isset( $_GET['woo_vou_end_date'] ) ? sanitize_text_field( $_GET['woo_vou_end_date'] )   : null;
$woo_vou_post_id    	= isset( $_GET['woo_vou_post_id'] ) ? sanitize_text_field( $_GET['woo_vou_post_id'] )   : null;
$woo_vou_user_id        = isset( $_GET['woo_vou_user_id'] ) ? sanitize_text_field( $_GET['woo_vou_user_id'] )   : null;
$woo_vou_partial_used	= ( !empty( $_GET['woo_vou_partial_used_voucode'] ) && $_GET['woo_vou_partial_used_voucode'] == 'yes' ) ? "checked='checked'" : '';
?>

<!-- Used Voucher codes list table -->
<h3><?php esc_html_e( 'Redeemed Voucher Codes', 'woovoucher' ); ?></h3>

<!-- Start search used Voucher Codes through there dates -->
<form action="" method="GET" class="search-form">
	<div class="woo-vou-table-filter-wrap">
		<select id="woo_vou_post_id" name="woo_vou_post_id" class="woo_vou_multi_select" data-width="170px">
			<option value=""><?php esc_html_e( 'Show all products', 'woovoucher' ); ?></option><?php
			if( !empty( $products_data ) ) {
				foreach ( $products_data as $product_data ) {
					echo '<option value="' . $product_data['ID'] . '" ' . selected( $woo_vou_post_id, $product_data['ID'], false ) . '>' . $product_data['post_title'] . '</option>';
				}
			}?>
		</select>
	</div>
	<div class="woo-vou-table-filter-wrap">
		<select id="woo_vou_user_id" name="woo_vou_user_id" class="woo_vou_multi_select" data-width="170px">
			<option value=""><?php esc_html_e( 'Redeem by all', 'woovoucher' ); ?></option><?php
			if( !empty( $users_data ) ) {
				foreach ( $users_data as $id=>$name ) {
					echo '<option value="' . $id . '" ' . selected( $woo_vou_user_id, $id, false ) . '>' . $name . '</option>';
				}
			}?>
		</select>
	</div>
	<div class="woo-vou-table-filter-wrap">
		<input type="text" id="woo_vou_start_date" name="woo_vou_start_date" class="woo-vou-meta-datetime" rel="yy-mm-dd" placeholder="<?php esc_html_e( 'Redeem Start Date', 'woovoucher' ); ?>" value="<?php echo $woo_vou_start_date; ?>" placeholder="YYYY-MM-DD H:I">
	</div>
	<div class="woo-vou-table-filter-wrap">
		<input type="text" id="woo_vou_end_date" name="woo_vou_end_date" class="woo-vou-meta-datetime" rel="yy-mm-dd" placeholder="<?php esc_html_e( 'Redeem End Date', 'woovoucher' ); ?>" value="<?php echo $woo_vou_end_date; ?>" placeholder="YYYY-MM-DD H:I">
	</div>
	<?php do_action('woo_vou_after_redeem_end_date'); ?>
	
	<div class="woo-vou-table-filter-wrap">
		<input type="checkbox" id="woo_vou_partial_used_voucode" name="woo_vou_partial_used_voucode" value="yes" <?php echo $woo_vou_partial_used; ?>><label for="woo_vou_partial_used_voucode" class="woo-vou-partial-used-voucode-label woo-vou-partial-used-voucode-label-style" /><?php esc_html_e('Partially Used', 'woovoucher'); ?>  </label>
	</div>
	<div class="woo-vou-table-filter-wrap">
		<input type="submit" value="<?php esc_html_e('Apply', 'woovoucher'); ?>" class="woo-vou-btn-front woo-vou-apply-btn" id="woo-vou-filter-apply-btn"></input>
	</div>
	<?php 
	if( !empty( $woo_vou_post_id ) || ! empty( $woo_vou_start_date ) || ! empty( $woo_vou_end_date ) || !empty( $woo_vou_partial_used ) ) : ?>
        <a href="<?php echo get_page_link(); ?>" class="button-secondary woo-vou-btn-front"><?php esc_html_e( 'Clear Filter', 'woovoucher' ); ?></a>
    <?php endif; ?>
</form>
<!-- End search used Voucher Codes through there dates -->

<div class="woo-vou-usedvoucodes woo-vou-used-codes-html">
<?php
	
	if( isset( $_POST['woo_vou_start_date'] ) && !empty( $_POST['woo_vou_start_date'] ) )
		$_GET['woo_vou_start_date'] = $_POST['woo_vou_start_date'];
	if( isset( $_POST['woo_vou_end_date'] ) && !empty( $_POST['woo_vou_end_date'] ) )
		$_GET['woo_vou_end_date'] = $_POST['woo_vou_end_date'];
	if( isset( $_POST['woo_vou_post_id'] ) && !empty( $_POST['woo_vou_post_id'] ) )
		$_GET['woo_vou_post_id'] = $_POST['woo_vou_post_id'];
	if( isset( $_POST['woo_vou_partial_used_voucode'] ) && !empty( $_POST['woo_vou_partial_used_voucode'] ) )
		$_GET['woo_vou_partial_used_voucode'] = $_POST['woo_vou_partial_used_voucode'];
		
		
	$perpage = apply_filters( 'woo_vou_used_voucher_codes_per_page', 10 );
	
	//Get Prefix
	$prefix		= WOO_VOU_META_PREFIX;

	// start paging
	$paging = new Woo_Vou_Pagination_Public( 'woo_vou_used_codes_ajax_pagination' );
	
	$args = $data = array();
	
	// Taking parameter
	$orderby 	= 'ID';
	$order		= 'DESC';

	$args = array(
					'paged'				=> isset( $_POST['paging'] ) ? $_POST['paging'] : null,
					'orderby'			=> $orderby,
					'order'				=> $order,
					'woo_vou_list'		=> true
				);

	$search_meta = 	array(
						array(
							'key' 		=> $prefix . 'used_codes',
							'value' 	=> '',
							'compare' 	=> '!='
						)
					);

	// If Partially Used checkbox is ticked than only show voucher codes which are used partially
    if( !empty( $_GET['woo_vou_partial_used_voucode'] ) && ( $_GET['woo_vou_partial_used_voucode'] == 'yes' || $_GET['woo_vou_partial_used_voucode'] == 1 ) ) {	

		// Search for code having meta key _woo_vou_redeem_method and meta value partial
		$search_meta = array_merge(array( array( 
												'key' 		=> $prefix . 'redeem_method',
												'value'		=> 'partial',
												) ), $search_meta);
	}

	// Get option whether to allow all vendor to redeem voucher codes
    $vou_enable_vendor_access_all_voucodes = get_option('vou_enable_vendor_access_all_voucodes');

    // voucher admin can redeem all codes
	if( in_array( $user_role, $woo_vou_vendor_role ) 
		&& ( empty($vou_enable_vendor_access_all_voucodes) || $vou_enable_vendor_access_all_voucodes == 'no' ) ) {

		$args['author'] = $current_user->ID;

	} elseif( !in_array( $user_role, $woo_vou_vendor_role ) && !in_array( $user_role, $admin_roles ) ) {
		
		$search_meta =	array(
							'relation' => 'AND',
							($search_meta),
							array(
								array(
										'key'		=> $prefix.'customer_user',
										'value'		=> $current_user->ID,
										'compare'	=> '=',
									)
							)
						);
	}

	if( isset( $_GET['woo_vou_post_id'] ) && !empty( $_GET['woo_vou_post_id'] ) ) {
		$args['post_parent'] = $_GET['woo_vou_post_id'];
	}
	
	if( isset( $_GET['woo_vou_user_id'] ) && !empty( $_GET['woo_vou_user_id'] ) ) {
		
		$search_meta =	array(
							'relation' => 'AND',
							($search_meta),
							array(
								array(
										'key'		=> $prefix.'redeem_by',
										'value'		=> $_GET['woo_vou_user_id'],
										'compare'	=> '=',
									)
							)
						);
	}
	
	if( isset( $_GET['woo_vou_start_date'] ) && !empty( $_GET['woo_vou_start_date'] ) ) {
		
		$search_meta =	array(
							'relation' => 'AND',
							($search_meta),
							array(
								array(
										'key'		=> $prefix.'used_code_date',
										'value'		=> date( "Y-m-d H:i:s", strtotime( $_GET['woo_vou_start_date'] ) ),
										'compare'	=> '>=',
									)
							)
						);
	}
	
	if( isset( $_GET['woo_vou_end_date'] ) && !empty( $_GET['woo_vou_end_date'] ) ) {
		
		$search_meta =	array(
							'relation' => 'AND',
							($search_meta),
							array(
								array(
										'key'		=> $prefix.'used_code_date',
										'value'		=> date( "Y-m-d H:i:s", strtotime( $_GET['woo_vou_end_date'] ) ),
										'compare'	=> '<=',
									)
							)
						);
	}
	
	$args['meta_query']	= $search_meta;


	// Apply filter for arguments to get used voucher code list
	$args = apply_filters( 'woo_vou_change_used_vou_list_args', $args );

	// Get count for used voucher codes from database without post per page param
	$count_data 	= woo_vou_get_voucher_details( $args );
	
	// Specify paging params
	$paging->items( count( $count_data['data'] ) ); // Get total paging items
	$paging->limit( $perpage ); // limit entries per page
	
	if( isset( $_POST['paging'] ) ) {
		$paging->currentPage( $_POST['paging'] ); // gets and validates the current page
	}
	
	$paging->calculate(); // calculates what to show

	$paging->parameterName( 'paging' ); // Specify parameter name for paging
	
	$args['posts_per_page'] = $perpage; // Specify post per page param now
	
	//get used voucher codes data from database
	$woo_data   = woo_vou_get_voucher_details( $args );
	$data		= isset( $woo_data['data'] ) ? $woo_data['data'] : '';

	if( !empty( $data ) ) {

		foreach ( $data as $key => $value ) {

			$user_id 	  = get_post_meta( $value['ID'], $prefix.'redeem_by', true );
			$user_detail  = get_userdata( $user_id );
			$user_profile = add_query_arg( array('user_id' => $user_id), admin_url('user-edit.php') );
			$display_name = isset( $user_detail->display_name ) ? $user_detail->display_name : '';

			if( !empty( $display_name ) ) {
				$display_name = '<a href="'.esc_url($user_profile).'">'.$display_name.'</a>';
			} 
			elseif ( $user_id == '0' ) {
				$display_name = esc_html__( 'Guest User', 'woovoucher' );
			}
			else {
				$display_name = esc_html__( 'N/A', 'woovoucher' );
			}

			$data[$key]['ID'] 			= $value['ID'];
			$data[$key]['post_parent'] 	= $value['post_parent'];
			$data[$key]['code'] 		= get_post_meta( $value['ID'], $prefix.'used_codes', true );
			$data[$key]['redeem_by'] 	= $display_name;
			$data[$key]['first_name'] 	= get_post_meta( $value['ID'], $prefix.'first_name', true );
			$data[$key]['last_name'] 	= get_post_meta( $value['ID'], $prefix.'last_name', true );
			$data[$key]['order_id'] 	= get_post_meta( $value['ID'], $prefix.'order_id', true );
			$data[$key]['order_date'] 	= get_post_meta( $value['ID'], $prefix.'order_date', true );
			$data[$key]['product_title']= get_the_title( $value['post_parent'] );

			$order_id = $data[$key]['order_id'];

			$data[$key]['buyers_info'] = $woo_vou_model->woo_vou_get_buyer_information( $order_id );
		}
	}
	$result_arr	= !empty($data) ? $data : array();
	
	if( isset( $result_arr ) ) { //check if Array of Used Voucher Codes is empty
		
		// do action add something before used codes table
		do_action( 'woo_vou_used_codes_table_before', $result_arr );
				
		// start displaying the paging if needed
		// do action add used codes listing table
		do_action( 'woo_vou_used_voucher_codes_table', $result_arr, $paging );

		// do action add something after used codes table after	
		do_action( 'woo_vou_used_codes_table_after', $result_arr );
		
	} ?>
</div>