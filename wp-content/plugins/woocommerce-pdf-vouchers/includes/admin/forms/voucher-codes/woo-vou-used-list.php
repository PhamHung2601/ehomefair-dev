<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Used Voucher Code List Page
 * 
 * The html markup for the used voucher code list
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WOO_Vou_Used_List extends WP_List_Table {

	public $model, $voucher, $render, $per_page;

	function __construct() {

		global $woo_vou_model, $woo_vou_render, $woo_vou_voucher;

		//Set parent defaults
		parent::__construct( array(
									'singular'  => 'usedvou',//singular name of the listed records
									'plural'    => 'usedvous',//plural name of the listed records
									'ajax'      => false//does this table support ajax?
								) );

		$this->model = $woo_vou_model;
		$this->render = $woo_vou_render;
		$this->voucher	= $woo_vou_voucher;

		$this->per_page	= apply_filters( 'woo_vou_used_posts_per_page', 10 ); // Per page
	}

	/**
	 * Displaying Prodcuts
	 * 
	 * Does prepare the data for displaying the products in the table.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function display_used_vouchers() {

		global $current_user, $woo_vou_vendor_role;

		$prefix = WOO_VOU_META_PREFIX;

		$args = $data = array();

		// Taking parameter
		$orderby 	= isset( $_GET['orderby'] )	? urldecode( $_GET['orderby'] )		: 'ID';
		$order		= isset( $_GET['order'] )	? $_GET['order']                	: 'DESC';
		$search 	= isset( $_GET['s'] ) 		? sanitize_text_field( trim($_GET['s']) )	: null;

		$args = array(
						'posts_per_page'	=> $this->per_page,
						'page'				=> isset( $_GET['paged'] ) ? $_GET['paged'] : null,
						'orderby'			=> $orderby,
						'order'				=> $order,
						'offset'  			=> ( $this->get_pagenum() - 1 ) * $this->per_page,
						'woo_vou_list'		=> true
					);

		$search_meta = 	array(
							array(
								'key' 		=> $prefix . 'used_codes',
								'value' 	=> '',
								'compare' 	=> '!='
							)
						);
						
		//Get user role
		$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
		$user_role	= array_shift( $user_roles );

		//voucher admin roles
		$admin_roles	= woo_vou_assigned_admin_roles();

		if( !in_array( $user_role, $admin_roles ) ) {// voucher admin can redeem all codes
			$args['author'] = $current_user->ID;
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

		// If Partially Used checkbox is ticked than only show voucher codes which are used partially
		if( !empty( $_GET['woo_vou_partial_used_voucode'] ) && $_GET['woo_vou_partial_used_voucode'] == 'yes' ){

			// Search for code having meta key _woo_vou_redeem_method and meta value partial
			$search_meta = array_merge(array( array( 
													'key' 		=> $prefix . 'redeem_method',
													'value'		=> 'partial',
													) ), $search_meta);
		}

		if( !empty( $search ) ) {

			$search_meta =	array(
								'relation' => 'AND',
								($search_meta),
								array(
									'relation'	=> 'OR',
									array(
											'key'		=> $prefix.'used_codes',
											'value'		=> $_GET['s'],
											'compare'	=> 'LIKE',
										),
									array(
											'key'		=> $prefix.'first_name',
											'value'		=> $_GET['s'],
											'compare'	=> 'LIKE',
										),
									array(
											'key'		=> $prefix.'last_name',
											'value'		=> $_GET['s'],
											'compare'	=> 'LIKE',
										),
									array(
											'key'		=> $prefix.'order_id',
											'value'		=> $_GET['s'],
											'compare'	=> 'LIKE',
										),
									array(
											'key'		=> $prefix.'order_date',
											'value'		=> $_GET['s'],
											'compare'	=> 'LIKE',
										)
								)
							);
		}
		
		$args['meta_query']	= $search_meta;

		// Apply filter for arguments to get used voucher code list
		$args = apply_filters( 'woo_vou_get_used_vou_list_args', $args );

		//get used voucher codes data from database
		$woo_data 	= woo_vou_get_voucher_details( $args );
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
				elseif( $user_id == '0' ){
					$display_name = esc_html__( 'Guest user', 'woovoucher' );
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

				$data[$key]['buyers_info'] = $this->model->woo_vou_get_buyer_information( $order_id );
			}
		}

		$result_arr['data']		= !empty($data) ? $data : array();
		$result_arr['total'] 	= isset( $woo_data['total'] ) ? $woo_data['total'] 	: 0; // Total no of data

		return $result_arr;
	}

	/**
	 * Mange column data
	 * 
	 * Default Column for listing table
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function column_default( $item, $column_name ) {

		global $current_user, $woo_vou_vendor_role;

		//Get user role
		$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
		$user_role	= array_shift( $user_roles );

		$column_value = '';

		switch( $column_name ) {
			case 'code':
				$column_value = $item[ $column_name ];
				break;
			case 'buyers_info' :
				$column_value = woo_vou_display_buyer_info_html( $item[ $column_name ] );
				break;
			case 'redeem_by' :
				$column_value =woo_vou_display_redeem_info_html( $item['ID'], $item['order_id'] );
				break;
			case 'product_info' :
				$column_value = woo_vou_display_product_info_html( $item['order_id'], $item['code'] );
				break;
			case 'order_info' :
				$column_value = woo_vou_display_order_info_html( $item['order_id'] );
				break;
			case 'code_details':
				$vou_code_detail_page_url   = add_query_arg( array( 'page' => 'woo-vou-codes', 'vou_code' => $item['ID'] ), admin_url('admin.php'));
				$column_value = '<a href="' . esc_url($vou_code_detail_page_url) . '" class="woo-vou-code-detailview  woo-vou-action-button" target="_blank" title="' . esc_html__('View', 'woovoucher') . '"></a>';
				break;
			default:
				$column_value = isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
				break;
		}
		
		return apply_filters( 'woo_vou_used_column_value', $column_value, $column_name, $item );
	}

	/**
	 * Display Columns of checkbox
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			 $this->_args['singular'],
			 $item['ID']
		);
	}

	/**
	 * Display Columns
	 * 
	 * Handles which columns to show in table
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function get_columns(){

		$columns = array(
							'code'          =>  esc_html__( 'Voucher Code', 'woovoucher' ),
							'product_info'  =>  esc_html__( 'Product Information', 'woovoucher' ),
							'buyers_info'   =>  esc_html__( 'Buyer\'s Information', 'woovoucher' ),
							'order_info'    =>  esc_html__( 'Order Information', 'woovoucher' ),                            
							'redeem_by'     =>  esc_html__( 'Redeem Information', 'woovoucher' ),
							'code_details'  =>  esc_html__( 'View Details', 'woovoucher' ),
						);

		return apply_filters( 'woo_vou_used_add_column', $columns );
	}

	/**
	 * Sortable Columns
	 * 
	 * Handles soratable columns of the table
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function get_sortable_columns() {

		$sortable_columns = array(
    								'code'			=>	array( 'code', true ),		            
						            'order_date'	=>	array( 'order_date', true ),
						            'order_id'		=>	array( 'order_id', true ),						            
								);
		return apply_filters( 'woo_vou_used_add_sortable_column', $sortable_columns );
	}

	/**
	 * Handles when no voucher codes not found
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function no_items() {

		//message to show when no records in database table
		esc_html_e( 'No redeemed voucher codes yet.', 'woovoucher' );
	}

	/**
     * Bulk actions field
     * 
     * Handles Bulk Action combo box values
     * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
     */
	function get_bulk_actions() {
		//bulk action combo box parameter
		//if you want to add some more value to bulk action parameter then push key value set in below array
		$actions = array();
		return $actions;
    }

	/**
	 * Add Filter for Sorting
	 * 
	 * Handles to add filter for sorting
	 * in listing
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function extra_tablenav( $which ) {

		if( $which == 'top' ) {

			global $current_user, $woo_vou_vendor_role;

			$prefix = WOO_VOU_META_PREFIX;
			$args	= array();

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

			if( in_array( $user_role, $woo_vou_vendor_role ) ) { // Check vendor user role
				$args['author']	= $current_user->ID;
			}

			$args = apply_filters('woo_vou_used_product_list', $args);

    		$products_data	= woo_vou_get_products_by_voucher( $args );
    		$users_data		= $this->voucher->woo_vou_get_redeem_users_by_voucher( $args );
            
            $woo_vou_start_date = isset( $_GET['woo_vou_start_date'] ) ? sanitize_text_field( $_GET['woo_vou_start_date'] ) : null;
            $woo_vou_end_date   = isset( $_GET['woo_vou_end_date'] ) ? sanitize_text_field( $_GET['woo_vou_end_date'] )   : null;
            $woo_vou_post_id    = isset( $_GET['woo_vou_post_id'] ) ? sanitize_text_field( $_GET['woo_vou_post_id'] )   : null;
            $woo_vou_user_id    = isset( $_GET['woo_vou_user_id'] ) ? sanitize_text_field( $_GET['woo_vou_user_id'] )   : null;
            $woo_vou_partial_used	= ( !empty( $_GET['woo_vou_partial_used_voucode'] ) && $_GET['woo_vou_partial_used_voucode'] == 'yes' ) ? "checked='checked'" : '';
            
    		echo '<div class="alignleft actions woo-vou-dropdown-wrapper">';?>
				<select id="woo_vou_post_id" name="woo_vou_post_id" class="woo_vou_multi_select">
					<option value=""><?php esc_html_e( 'Show all products', 'woovoucher' ); ?></option><?php
					if( !empty( $products_data ) ) {
						foreach ( $products_data as $product_data ) {
							echo '<option value="' . $product_data['ID'] . '" ' . selected( $woo_vou_post_id, $product_data['ID'], false ) . '>' . $product_data['post_title'] . '</option>';
						}
					}?>
				</select>
				<select id="woo_vou_user_id" name="woo_vou_user_id" class="woo_vou_multi_select">
					<option value=""><?php esc_html_e( 'Redeem by all', 'woovoucher' ); ?></option><?php
					if( !empty( $users_data ) ) {
						foreach ( $users_data as $id=>$name ) {
							echo '<option value="' . $id . '" ' . selected( $woo_vou_user_id, $id, false ) . '>' . $name . '</option>';
						}
					}?>
				</select>
				<input type="text" id="woo_vou_start_date" name="woo_vou_start_date" class="woo-vou-meta-datetime" rel="yy-mm-dd" placeholder="<?php _e( 'Redeem Start Date', 'woovoucher' ); ?>" value="<?php echo $woo_vou_start_date; ?>">
				<input type="text" id="woo_vou_end_date" name="woo_vou_end_date" class="woo-vou-meta-datetime" rel="yy-mm-dd" placeholder="<?php _e( 'Redeem End Date', 'woovoucher' ); ?>" value="<?php echo $woo_vou_end_date; ?>">
				<input type="checkbox" id="woo_vou_partial_used_voucode" name="woo_vou_partial_used_voucode" value="yes" <?php echo $woo_vou_partial_used; ?>><label for="woo_vou_partial_used_voucode" class="woo-vou-partial-used-voucode-label" /><?php esc_html_e('Partially Used', 'woovoucher'); ?>  </label>
				<?php
                submit_button( esc_html__( 'Apply', 'woovoucher' ), 'button', false, false, array( 'id' => 'post-query-submit' ) );
                if( !empty( $woo_vou_post_id ) || !empty( $woo_vou_user_id ) || ! empty( $woo_vou_start_date ) || ! empty( $woo_vou_end_date ) || !empty( $woo_vou_partial_used ) ) : ?>
                    <a href="<?php echo admin_url( 'admin.php?page=woo-vou-codes&vou-data=used' ); ?>" class="button-secondary woo-vou-clear-filter"><?php esc_html_e( 'Clear Filter', 'woovoucher' ); ?></a>
                <?php endif;
			echo '</div>';
    	}
    }

    /**
     * Prepare for show
     *
     * @package WooCommerce - PDF Vouchers
 	 * @since 1.0.0
     */
	function prepare_items() {

		// Get how many records per page to show
		$per_page	= $this->per_page;

		// Get All, Hidden, Sortable columns
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		// Get final column header
		$this->_column_headers	= array( $columns, $hidden, $sortable );

		// Get Data of particular page
		$data_res 	= $this->display_used_vouchers();
		$data 		= $data_res['data'];

		// Get current page number
		$current_page = $this->get_pagenum();

		// Get total count
		$total_items  = $data_res['total'];

		// Get page items
		$this->items = $data;

		// We also have to register our pagination options & calculations.
		$this->set_pagination_args( array(
											'total_items' => $total_items,
											'per_page'    => $per_page,
											'total_pages' => ceil( $total_items/$per_page )
										) );
    }
    
    /**
     * Manage Delete Link
     *
     * @package WooCommerce - PDF Vouchers
 	 * @since 2.6.3
     */
    public function column_code( $item ){
    	
    	$actions = array();
    		        
        //Return the title contents	        
        return apply_filters('woo_vou_used_voucher_column_code',sprintf('%1$s',
            $item['code']),$item);

    }

    /**
     * Adds html for Partial Used if voucher code is partially used
     *
     * @package WooCommerce - PDF Vouchers
 	 * @since 3.3.1
     */
    public function column_code_details( $item ){

    	global $woo_vou_voucher;

    	$prefix = WOO_VOU_META_PREFIX;

    	$partially_used = '';
    	
    	if( !get_post_status( $item['post_name'] ) ) {
	        
    		//Build voucher delete action
	        $column_value = sprintf( '<a class="woo_vou_delete_voucher_code woo-vou-action-button" href="?page=%s&vou-data=%s&action=%s&code_id=%s&order_id=%s" title="'.esc_html__('Delete', 'woovoucher').'"></a>', $_REQUEST['page'], $_REQUEST['vou-data'], 'delete', $item['ID'], $item['post_name'] );

    	} else {

	    	$args = array(
			    'woo_vou_list' => true,
			    'post_parent' => $item['ID']
			);

			//get partially used voucher codes data from database
			$redeemed_data = woo_vou_get_partially_redeem_details($args);
			$redeemed_data_cnt = isset($redeemed_data['total']) ? $redeemed_data['total'] : '';

	    	$vou_code_detail_page_url   	= add_query_arg( array( 'page' => 'woo-vou-codes', 'vou_code' => $item['ID'] ), admin_url('admin.php'));
			$column_value 					= '<a href="' . esc_url($vou_code_detail_page_url) . '" class="woo-vou-code-detailview woo-vou-action-button" target="_blank" title="' . esc_html__('View', 'woovoucher') . '"</a>';

			if( !empty($redeemed_data_cnt) && $redeemed_data_cnt > 1 ) {

				// Create html for partial redeem
				$partially_used  = '<a href="' . esc_url($vou_code_detail_page_url) . '" class="woo-vou-code-partial-used-btn woo-vou-action-button" target="_blank" title="'. esc_html__( 'Partially Used', 'woovoucher') .'"></a>';
			}
		}

		$column_value = apply_filters( 'woo_vou_used_code_details_column_value', $column_value, $item );

		//Return the title contents	        
        return sprintf('%1$s %2$s',
            $column_value,
            $partially_used
        );
    }
}

global $current_user;

//Create an instance of our package class...
$WooUsedVouListTable = new WOO_Vou_Used_List();

//Fetch, prepare, sort, and filter our data...
$WooUsedVouListTable->prepare_items();?>

<div class="wrap"><?php 
	
	if( isset( $_GET['message'] ) && !empty( $_GET['message'] ) && $_GET['message'] == '1' ) { //check message
		echo '<div class="updated" id="message">
				<p><strong>'.esc_html__("Voucher code deleted successfully.",'woovoucher').'</strong></p>
			  </div>';
	}
	
	//showing sorting links on the top of the list
	$WooUsedVouListTable->views();?>

	<?php do_action( 'woo_vou_used_list_before_form', $WooUsedVouListTable ); ?>

    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="product-filter" method="get" action="">

    	<!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <input type="hidden" name="vou-data" value="<?php echo $_REQUEST['vou-data'] ?>" />

        <!-- Search Title -->
        <?php $WooUsedVouListTable->search_box( esc_html__( 'Search', 'woovoucher' ), 'woovoucher' ); ?>

        <div class="alignright">
        <?php
			$generatpdfurl = add_query_arg( array( 
													'woo-vou-voucher-gen-pdf'	=> '1',
													'woo_vou_action'			=> 'used'
												));
			$exportcsvurl  = add_query_arg( array( 
													'woo-vou-voucher-exp-csv'	=>	'1',
													'woo_vou_action'			=> 'used'
												));
			
			$html = '<a href="'.esc_url($exportcsvurl).'" id="woo-vou-export-csv-btn" class="button-secondary woo-gen-pdf" title="'.esc_html__( 'Export CSV', 'woovoucher' ).'">'.esc_html__( 'Export CSV', 'woovoucher' ).'</a>';
			echo apply_filters( 'woo_vou_hide_voucher_codes_export_csv', $html );

			$html = '<a href="'.esc_url($generatpdfurl).'" id="woo-vou-pdf-btn" class="button-secondary" title="'.esc_html__('Generate PDF','woovoucher').'">'.esc_html__( 'Generate PDF', 'woovoucher' ).'</a>';
			echo apply_filters( 'woo_vou_hide_voucher_codes_generate_pdf', $html );
		?>
		</div>

        <!-- Now we can render the completed list table -->
        <?php $WooUsedVouListTable->display(); ?>
    </form>
</div>