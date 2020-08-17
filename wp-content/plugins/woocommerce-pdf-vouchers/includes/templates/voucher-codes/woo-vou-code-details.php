<?php
/**
 * Voucher Codes Details Template
 * 
 * Handles display voucher code details data
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.8.1
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Check user is logged in or not
if ( ! is_user_logged_in() ) {

    echo esc_html__( 'You need to be logged in to your account to see your details of unredeemed voucher codes.', 'woovoucher' );
    return; 
}

global $current_user, $woo_vou_model, $woo_vou_voucher, $woo_vou_vendor_role;

$prefix = WOO_VOU_META_PREFIX; // Get prefix

//Current user role
$user_roles = isset($current_user->roles) ? $current_user->roles : array();
$user_role = array_shift($user_roles);

//get voucher admins
$voucher_admins = woo_vou_assigned_admin_roles();

$product_id = '';

$vou_code_data = apply_filters('woo_vou_code_details_vouchercode_data', $vou_code_data);


$vou_code_id = $vou_code_data['voucodeid']; // get vocher code id

$vou_change_template = get_option( 'vou_change_template' );
$voucher_options    = array();
$voucher_data       = woo_vou_get_vouchers();
foreach ( $voucher_data as $voucher ) {
    if( isset( $voucher['ID'] ) && !empty( $voucher['ID'] ) ) { // Check voucher id is not empty
        $voucher_options[$voucher['ID']] = $voucher['post_title'];
    }
}
?>
<div class="wrap">

	<?php 
		if( !empty( $_GET['message'] ) ) { 
			$new_url = remove_query_arg('message', $_SERVER['REQUEST_URI']);
			if( is_admin() ) {
				$html = '<div class="updated notice woo-vou-gift-sent-notice">';
				if( $_GET['message'] == 'woo_vou_gift_email_sent' ) {
					$html .= '<p>' . esc_html__('Gift notification email sent successfully.', 'woovoucher') . '</p>';
				} elseif ( $_GET['message'] == 'woo_vou_recipient_details_changed' ) {
                    $html .= '<p>' . esc_html__('Recipient Information changed successfully.', 'woovoucher') . '</p>';
                } elseif ( $_GET['message'] == 'woo_vou_voucode_note_changed' ) {
                    $html .= '<p>' . esc_html__('Voucher note changed successfully.', 'woovoucher') . '</p>';
                } elseif ( $_GET['message'] == 'woo_vou_voucher_information_changed' ) {
                    $html .= '<p>' . esc_html__('Voucher Information changed successfully.', 'woovoucher') . '</p>';
                }

				$html .= '</div>';
				echo $html;
			} else {
                if( $_GET['message'] == 'woo_vou_gift_email_sent' ) {
                    wc_print_notice( esc_html__('Gift notification email sent successfully.', 'woovoucher'), 'success' );
                } elseif ( $_GET['message'] == 'woo_vou_recipient_details_changed' ) {
                    wc_print_notice( esc_html__('Recipient Information changed successfully.', 'woovoucher'), 'success' );
                } elseif ( $_GET['message'] == 'woo_vou_voucode_note_changed' ) {
                    wc_print_notice( esc_html__('Voucher note changed successfully.', 'woovoucher'), 'success' );
                } elseif ( $_GET['message'] == 'woo_vou_voucher_information_changed' ) {
                    wc_print_notice( esc_html__('Voucher Information changed successfully.', 'woovoucher'), 'success' );
                }
			}
		?>
    <?php
     } ?>
	
    <h2 class="woo-vou-settings-title"><?php esc_html_e('Voucher Code', 'woovoucher'); ?><span> : </span><?php esc_html_e($vou_code_data['voucode'], 'woovoucher'); ?></h2>

    <div class="woo-vou-voucher-detail-msg"><?php esc_html_e( 'Here you can find detailed information for a voucher code.', 'woovoucher' ); ?></div>

    <div id="woo_vou_detail_voucher_code_wrap" class="post-box-container">
        <div class="metabox-holder">
            <div class="meta-box-sortables ui-sortable">

                <?php if ( !empty( $vou_code_data['redeemed_infos'] ) && is_array( $vou_code_data['redeemed_infos'] ) ) { ?>
                    <div id="woo-vou-voucher-redeem-details" class="postbox ">
                        <?php echo apply_filters('woo_vou_redeemed_info_heading','<h2 class="hndle ui-sortable-handle"><span>'.esc_html__( 'Redeem Information', 'woovoucher' ) .'</span></h2>'); ?>
                        <div class="inside">
                            <table class="widefat woo-vou-history-table">
                                <tbody>
                                    <tr class="woo-vou-history-title-row"> 
                                        <?php foreach ($vou_code_data['redeem_info_columns'] as $col_key => $column) { ?>

                                            <th><?php echo $column; ?></th><?php } ?>
                                    </tr>
                                    <?php foreach ($vou_code_data['redeemed_infos'] as $key => $redeemed_info) { ?>

                                        <tr class="woo-vou-history-title-row"><?php foreach ($vou_code_data['redeem_info_columns'] as $col_key => $column) { ?>
                                                <td class="woo-vou-history-td"><?php
                                                    switch ($col_key) {

                                                        case 'item_name' :
                                                            $column_value = "";
                                                            $sku_value = "";

                                                            if ($vou_code_data['product'] && $vou_code_data['product']->get_sku()) {
                                                                $sku_value = esc_html($vou_code_data['product']->get_sku()) . ' - ';
                                                            }

                                                            if ($vou_code_data['product'] && in_array($user_role, $voucher_admins)) {

                                                                $column_value .= $sku_value . '<a href="' . esc_url(admin_url('post.php?post=' . absint($vou_code_data['product_data']['product_id']) . '&action=edit')) . '">' . $vou_code_data['product_data']['name'] . '</a>';
                                                            } else {

                                                                $column_value .= $sku_value . esc_html($vou_code_data['product_data']['name']);
                                                            }

                                                            break;
                                                        case 'redeem_price' :

                                                            if (isset($redeemed_info['redeem_amount'])) {
                                                                $column_value = wc_price($redeemed_info['redeem_amount'], array('currency' => woo_vou_get_order_currency($vou_code_data['order'])));
                                                            }
                                                            break;
                                                        case 'redeem_by' :

                                                            if (isset($redeemed_info['redeem_by'])) {
                                                                $column_value = $redeemed_info['redeem_by'];
                                                            }
                                                            break;

                                                        case 'redeem_date' :

                                                            if (isset($redeemed_info['redeem_date'])) {
                                                                $column_value = $redeemed_info['redeem_date'];
                                                            }
                                                            break;

                                                        default:
                                                            $column_value .= '';
                                                    }
                                                    echo $column_value;
                                                    ?>

                                                </td>
                                            <?php } ?>
                                        </tr> 
                                    <?php } 

                                    do_action('woo_vou_after_redeem_info_last_row',$vou_code_data,$vou_code_data['redeemed_infos']);                                    
                                    ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php } ?>

                <!-- Product Information starts -->
                <?php 
                	if (!empty($vou_code_data['product_info_columns'])) { 
                		$product_id = absint($vou_code_data['product_information']['item_id']);
                ?>
                    <div id="woo-vou-voucher-product-details" class="postbox">
                        <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e( 'Product Information', 'woovoucher' ); ?></span></h2>
                        <div class="inside">
                            <table class="widefat woo-vou-history-table">
                                <tbody>
                                    <tr class="woo-vou-history-title-row"> <?php foreach ($vou_code_data['product_info_columns'] as $col_key => $column) { ?>

                                            <th><?php echo $column; ?></th><?php } ?>
                                    </tr>              
                                    <tr>

                                        <?php foreach ($vou_code_data['product_info_columns'] as $col_key => $column) { ?>
                                            <td class="woo-vou-history-td"><?php
                                                switch ($col_key) {

                                                    case 'item_name' :

                                                    	$column_value = "";
                                                        $sku_value = "";

                                                        if ($vou_code_data['product'] && $vou_code_data['product']->get_sku()) {
                                                            $sku_value = esc_html($vou_code_data['product']->get_sku()) . ' - ';
                                                        }

                                                        if ($vou_code_data['product'] && in_array($user_role, $voucher_admins)){

                                                            $column_value = $sku_value . '<a href="' . esc_url(admin_url('post.php?post=' . absint($vou_code_data['product_information']['item_id']) . '&action=edit')) . '">' . $vou_code_data['product_information']['item_name'] . '</a>';
                                                        } else {
                                                            $column_value = $sku_value . $vou_code_data['product_information']['item_name'];
                                                        }
                                                        break;

                                                    case 'item_price' :

                                                        $column_value = $vou_code_data['product_information']['item_price'];
                                                        break;

                                                    case 'redeemable_price' :

                                                        $column_value = $vou_code_data['product_information']['redeemable_price'];
                                                        break;
													case 'product_date_renge' :

                                                        $column_value = $vou_code_data['product_information']['product_date_renge'];
                                                        break;

                                                    default:
                                                        $column_value .= '';
                                                }
                                                echo $column_value;
                                                ?>
                                            </td><?php }
                                            ?>

                                    </tr>
                                </tbody>
                            </table></div>
                    </div>
                <?php } ?>
                <!-- Product Information ends -->

                <!-- Voucher Information Starts -->
                <?php 
                if (!empty($vou_code_data['voucher_info_columns'])) {
                	$vou_code_order_id = $vou_code_data['order_information']['order_id']; // Get order id
                	$vou_code_item_id = $vou_code_data['product_information']['item_id']; // Get item id 
                ?>
                    <div id="woo-vou-voucher-details" class="postbox ">
                        <h2 class="hndle ui-sortable-handle">
                        	<span><?php 

                        		$product_data_object 	= $vou_code_data['product'];
                        		esc_html_e( 'Voucher Information', 'woovoucher' );

                        		if( $product_data_object && 
                        			( in_array($user_role, $voucher_admins) || in_array( $user_role, $woo_vou_vendor_role ) ) ) { ?>
		                        	<a href="#" class="edit_history_details"><?php esc_html_e( 'Edit', 'woovoucher' ); ?></a></h2>
		                        <?php } ?>
                        	</span>
                        </h2>
                        <div class="inside">
						<form method="POST" class="woo-vou--history-form">
                            <table class="widefat woo-vou-history-table">
                                <tbody>
                                    <tr class="woo-vou-history-title-row"> 
									
                                        <?php 
                                        	foreach ($vou_code_data['voucher_info_columns'] as $col_key => $column) { 

                                        		$width_calc = ( 100/count($vou_code_data['voucher_info_columns']) );
                                        		$width		= "width='".$width_calc."%'";
                                        ?>
                                        <th <?php echo $width; ?>><?php echo $column; ?></th>
                                        <?php } ?>
                                    </tr>
                                    <tr class="woo-vou-history-value-row"><?php foreach ($vou_code_data['voucher_info_columns'] as $col_key => $column) { ?>

                                            <td class="woo-vou-history-td">
                                                <?php
                                                $column_value = '';

                                                switch ($col_key) {

                                                    case 'logo' :
                                                        if( isset( $vou_code_data['voucher_information']['logo'] ) && !empty( $vou_code_data['voucher_information']['logo'] ) ) {
                                                            $column_value = '<img src="' . esc_url($vou_code_data['voucher_information']['logo']) . '" alt="" width="70" height="70" />';
                                                        } else{
                                                            $column_value = esc_html__('N/A', 'woovoucher');
                                                        }
                                                        break;
                                                    case 'voucher_data' :
                                                        ob_start();
                                                        ?>                                  
                                                        <span><strong><?php esc_html_e('Vendor\'s Address', 'woovoucher'); ?></strong></span><br />
                                                        <span><?php echo $vou_code_data['vendor_address_data']; ?></span><br />
                                                        <span><strong><?php esc_html_e('Site URL', 'woovoucher'); ?></strong></span><br />
                                                        <span><?php echo!empty($vou_code_data['voucher_information']['website_url']) ? $vou_code_data['voucher_information']['website_url'] : esc_html__('N/A', 'woovoucher'); ?></span><br />
                                                        <span><strong><?php esc_html_e('Redeem Instructions', 'woovoucher'); ?></strong></span><br />
                                                        <span><?php echo!empty($vou_code_data['voucher_information']['redeem']) ? $vou_code_data['voucher_information']['redeem'] : esc_html__('N/A', 'woovoucher'); ?></span><br />
                                                        <?php if (!empty($vou_code_data['primary_vendor_data'])) { ?>
                                                        <span><strong><?php esc_html_e('Primary Vendor', 'woovoucher'); ?></strong></span><br />
                                                        <span><?php echo $vou_code_data['primary_vendor_data']['display_name'] . "(#" . $vou_code_data['primary_vendor_data']['id'] . " - " . $vou_code_data['primary_vendor_data']['user_email'] . ")"; ?></span><br />

                                                        <?php } if (!empty($vou_code_data['secondary_vendors'])) {
                                                            foreach ($vou_code_data['secondary_vendors'] as $secondary_vendor) {
                                                                $vendorData[] = $secondary_vendor['display_name'] . "(#" . $secondary_vendor['id'] . " - " . $secondary_vendor['user_email'] . ")";
                                                            }
                                                            $secondary_vendors = implode(",", $vendorData);
                                                         ?>
                                                        <span><strong><?php esc_html_e('Secondary Vendors', 'woovoucher'); ?></strong></span><br />
                                                        <span><?php echo $secondary_vendors; ?></span><br />
                                                        <?php }
														
														?>
														<span><strong><?php esc_html_e('Locations ', 'woovoucher'); ?></strong></span><br />
														<?php
															$vendor_locations = $vou_code_data['voucher_information']['vendor_locations'];
															if(isset($vendor_locations) && !empty($vendor_locations) && is_array($vendor_locations)){
																$i = 1;
																?><?php
																foreach($vendor_locations as $location_data){
																	if(!empty($location_data[$prefix.'locations']) && !empty($location_data[$prefix.'map_link']) ){
                                                                       echo '<a href="'.esc_url($location_data[$prefix.'map_link']).'" target="_blank">'.$location_data[$prefix.'locations'].'</a>'; 
                                                                    } elseif( !empty($location_data[$prefix.'locations']) && empty($location_data[$prefix.'map_link']) ){
                                                                        echo $location_data[$prefix.'locations'];
                                                                    } else{
                                                                        echo esc_html__('N/A','woovoucher');
                                                                    }

																}
															}else{
																esc_html_e('N/A','woovoucher');
															}
															
														
                                                        $column_value = ob_get_clean();
                                                        break;
                                                    case 'expires' :
														ob_start();
														 $voucher_start_date = $vou_code_data['voucher_information']['voucher_start_date'];
														if(isset($voucher_start_date) && !empty($voucher_start_date)){
															echo '<span><strong>'.esc_html__('Start Date','woovoucher').'</strong></span><br>';
															echo $voucher_start_date;
															echo '<br><span><strong>'.esc_html__('Expire Date','woovoucher').'</strong></span><br>';
															echo !empty( $vou_code_data['voucher_information']['expires'] ) ? $vou_code_data['voucher_information']['expires']: esc_html__('Never Expire', 'woovoucher');
														}else{
															echo !empty( $vou_code_data['voucher_information']['expires'] ) ? $vou_code_data['voucher_information']['expires']: esc_html__('Never Expire', 'woovoucher');
														} 
                                                        $column_value = ob_get_clean();
                                                        break;
                                                    case 'pdf_template':

                                                        $vou_pdf_template = get_the_title( $vou_code_data['voucher_information']['pdf_template'] );
                                                    	$column_value .= "<span>". ( $vou_pdf_template ? $vou_pdf_template : $vou_code_data['voucher_information']['pdf_template'] ) . "</span><br />";
                                                    	break;
													case 'other_vou_info':	
														ob_start();
														?>
														<strong><?php esc_html_e('Coupon Code Generated', 'woovoucher') ?></strong><br><?php echo isset($vou_code_data['voucher_information']['is_coupon'])?$vou_code_data['voucher_information']['is_coupon']:esc_html__('N/A', 'woovoucher'); ?>
														<br><strong><?php esc_html_e('Days Voucher cannot be Used', 'woovoucher') ?> </strong><br>
														<?php 
														$exlude_redeem_day = $vou_code_data['voucher_information']['exlude_redeem_day'];
														
														$redeem_exlude_days_str = '';
														if(isset($exlude_redeem_day) && is_array($exlude_redeem_day) && !empty($exlude_redeem_day)){
															
															foreach($exlude_redeem_day as $exlude_days){
																$redeem_exlude_days_str .= substr($exlude_days,0,3).',';
															}
															$redeem_exlude_days_str = rtrim($redeem_exlude_days_str,',');
														}
														echo !empty($redeem_exlude_days_str)?$redeem_exlude_days_str:esc_html__('N/A', 'woovoucher');
														
														
														$column_value = ob_get_clean();											
                                                    	break;
                                                    default:
                                                        $column_value .= '';
                                                }

                                                echo $column_value;
                                                ?>
                                            </td><?php }
                                            ?>
                                    </tr>
									
                                    <?php // get voucher information

                                        $woo_vou_ordered_data 	= $woo_vou_model->woo_vou_get_all_ordered_data($vou_code_order_id);
                                        //get all voucher details from order meta
										$allvoucherdata = apply_filters( 'woo_vou_order_voucher_metadata', isset( $woo_vou_ordered_data[$vou_code_item_id] ) ? $woo_vou_ordered_data[$vou_code_item_id] : array(), $vou_code_order_id, $vou_code_data['item_id'], $vou_code_item_id );
                                        
                                        $product_data_id		= 0;

                                        if( $product_data_object ) {

                                        	$product_data_id = $product_data_object->get_id();
                                    ?>

	                                    <tr class="woo-vou-history-value-row-edit"><?php foreach ($vou_code_data['voucher_info_columns'] as $col_key => $column) { ?>
	
	                                            <td class="woo-vou-history-td">
	                                                <?php
	                                                $column_value = '';
													
	                                                switch ($col_key) {
	
	                                                    case 'logo' :
	                                                        ob_start();
	                                                        ?>
															<span class="mupload_img_holder"></span>
															<input id="_woo_vou_logo[id]" name="<?php echo $prefix.'logo[id]'; ?>" value="<?php echo $allvoucherdata['vendor_logo']['id']; ?>" type="hidden">
															<input id="_woo_vou_logo[src]" name="<?php echo $prefix.'logo[src]'; ?>" value="<?php echo $allvoucherdata['vendor_logo']['src']; ?>" type="hidden">
															<input class="button-secondary woo-vou-meta-upload_image_button" rel="_woo_vou_logo" value="<?php esc_html_e( 'Upload Image', 'woovoucher' ); ?>" type="button"><br>
                                                            <?php
															$column_value = ob_get_clean();
	                                                        break;
	                                                    case 'voucher_data' :

	                                                    	if( !empty( $product_data_id ) ) {

	                                                    		$woo_vou_vendor_address = $allvoucherdata['vendor_address'];
		                                                    	if( is_array($woo_vou_vendor_address) && $product_data_object->is_type('variation')){
		                                                    		$woo_vou_vendor_address = $woo_vou_vendor_address[$product_data_id];
		                                                    	}
	                                                    	} else {

	                                                    		$woo_vou_vendor_address = '';
	                                                    	}
	                                                        ob_start();
	                                                        ?>
	                                                        <span><strong><?php echo esc_html__('Vendor\'s Address', 'woovoucher'); ?></strong></span><br />
	                                                        <span><textarea class="woo-vou-vendor-address" name="<?php echo $prefix.'vendor_address'; ?>"><?php echo $woo_vou_vendor_address; ?></textarea></span><br />
	                                                        <span><strong><?php echo esc_html__('Site URL', 'woovoucher'); ?></strong></span><br />
	                                                        <span><input type="text" class="woo-vou-website-url" name="<?php echo $prefix.'voucher_website_url'; ?>" value="<?php echo $allvoucherdata['website_url']; ?>"/></span><br />
	                                                        <span><strong><?php echo esc_html__('Redeem Instructions', 'woovoucher'); ?></strong></span><br />
	                                                        <span><textarea class="woo-vou-redeem" name="<?php echo $prefix.'voucher_redeem' ?>"><?php echo ($allvoucherdata['redeem']); ?></textarea></span><br />
                                                            <?php if (!empty($vou_code_data['primary_vendor_data'])) { ?>
                                                            <span><strong><?php esc_html_e('Primary Vendor', 'woovoucher'); ?></strong></span><br />
                                                            <span><?php echo $vou_code_data['primary_vendor_data']['display_name'] . "(#" . $vou_code_data['primary_vendor_data']['id'] . " - " . $vou_code_data['primary_vendor_data']['user_email'] . ")"; ?></span><br />

                                                            <?php } if (!empty($vou_code_data['secondary_vendors'])) {
                                                                foreach ($vou_code_data['secondary_vendors'] as $secondary_vendor) {
                                                                    $vendorData[] = $secondary_vendor['display_name'] . "(#" . $secondary_vendor['id'] . " - " . $secondary_vendor['user_email'] . ")";
                                                                }
                                                                $secondary_vendors = implode(",", $vendorData);
                                                             ?>
                                                            <span><strong><?php esc_html_e('Secondary Vendors', 'woovoucher'); ?></strong></span><br />
                                                            <span><?php echo $secondary_vendors; ?></span><br />
                                                            <?php } 
															$vendor_locations = $vou_code_data['voucher_information']['vendor_locations'];
															if(isset($vendor_locations) && !empty($vendor_locations) && is_array($vendor_locations)){
																
																?><div class='woo-vou-info-repeat' id='locations'>	<?php
																foreach($vendor_locations as $key=>$location_data){
																	
																	if( $key > 0 ) {
																		$showremove = "woo-vou-block-section";
																	} else {
																		$showremove = "woo-vou-hide-section";
																	}
																	?>
																	<div class="woo-vou-info-repater-block">
																		<br /><span><strong><?php esc_html_e('Location','woovoucher')?></strong></span><br />
																		<input type="text" name="<?php echo $prefix.'locations[]'?>" value="<?php echo $location_data[$prefix.'locations']?>" /><br /><span><strong><?php esc_html_e('Location Map Link','woovoucher')?></strong></span><br />
																		<input type="text" name="<?php echo $prefix.'map_link[]'?>" value="<?php echo $location_data[$prefix.'map_link']?>" />
																		<img id='remove-locations' class='woo-vou-repeater-remove  voucher-info-remmove <?php echo $showremove; ?>' title="<?php esc_html_e('Remove', 'woovoucher'); ?>" alt="<?php esc_html_e('Remove', 'woovoucher'); ?>" src="<?php echo esc_url(WOO_VOU_META_URL).'/images/remove.png'; ?>">
																	</div>
																	<?php 
																}
																?>
																<img id='add-locations' class='woo-vou-info-repeater-add' title="<?php esc_html_e( 'Add','woovoucher' ); ?>" alt="<?php esc_html_e('Add', 'woovoucher'); ?>" src="<?php echo esc_url(WOO_VOU_META_URL).'/images/add.png'; ?>" >
																</div>
																<?php
															}else{
																?>
																<div class='woo-vou-info-repeat' id='locations'>
																<div class="woo-vou-info-repater-block">
																		<br /><span><strong><?php esc_html_e('Location','woovoucher')?></strong></span><br />
																		<input type="text" name="<?php echo $prefix.'locations[]'?>" value="" /><br /><span><strong><?php esc_html_e('Location Map Link','woovoucher')?></strong></span><br />
																		<input type="text" name="<?php echo $prefix.'map_link[]'?>" value="" />
																		
																	</div>
																	<img id='add-locations' class='woo-vou-info-repeater-add' title="<?php esc_html_e( 'Add','woovoucher' ); ?>" alt="<?php esc_html_e('Add', 'woovoucher'); ?>" src="<?php echo esc_url(WOO_VOU_META_URL).'/images/add.png'; ?>" >
																</div>
																<?php
															}
															
	                                                        $column_value = ob_get_clean();
	                                                        break;

	                                                    case 'expires' :
														
															ob_start();
															$voucher_information_expires = !empty($vou_code_data['voucher_information']['expires'])?date( 'Y-m-d h:i',strtotime($vou_code_data['voucher_information']['expires'])):'';
															$voucher_exp_type = !empty($vou_code_data['voucher_information']['voucher_exp_type'])?$vou_code_data['voucher_information']['voucher_exp_type']:'';
															
															 $voucher_start_date = $vou_code_data['voucher_information']['voucher_start_date'];
															
															if(isset($voucher_start_date) && !empty($voucher_start_date)){
																$min_date= ( !empty($voucher_start_date) )? date('Y-m-d h:i', strtotime( $voucher_start_date )) : date('Y-m-d');
                                                                $voucher_start_date = ( !empty($voucher_start_date) )? date('Y-m-d h:i', strtotime( $voucher_start_date )) : date('Y-m-d');
																echo '<span><strong>'.esc_html__('Start Date','woovoucher').'</strong></span><br>';
																echo '<input rel="yy-mm-dd" data-max-date="'.$voucher_information_expires.'" class="woo-vou-start-date " name="'.$prefix.'voucher_start_date" value="'.$voucher_start_date.'" type="text" placeholder="YYYY-MM-DD H:I">';
																echo '<br><span><strong>'.esc_html__('Expire Date','woovoucher').'</strong></span><br>';

																echo '<input rel="yy-mm-dd" data-min-date="'.$voucher_start_date.'" class="woo-vou-expires-date " name="'.$prefix.'voucher_expires_date" value="'.$voucher_information_expires.'"  type="text" placeholder="YYYY-MM-DD H:I">';
															}
															else{
																
																$voucher_start_date			 = get_post_meta( $vou_code_id, $prefix.'start_date', true );
																$min_date 					 = ( !empty($voucher_start_date) )? date('Y-m-d h:i', strtotime( $voucher_start_date )) : date('Y-m-d');	                                                        
																echo '<input rel="yy-mm-dd" data-min-date="'.$min_date.'" class="woo-vou-expires-date " name="'.$prefix.'voucher_expires_date" value="'.$voucher_information_expires.'" readonly="" type="text" placeholder="YYYY-MM-DD H:I">';
															} 
															echo '<input type="hidden" name="'.$prefix.'exp_type" value="'.$voucher_exp_type.'">';
															$column_value = ob_get_clean();
                                                          
	                                                        break;

	                                                    case 'pdf_template':
	                                                    	if( !empty($vou_change_template) && $vou_change_template == 'yes' ){ ?>
                                                                <select id="_woo_vou_pdf_template" class="wc-enhanced-select" name="<?php echo $prefix.'pdf_template'; ?>" data-width="90%">
                                                                    <?php foreach ($voucher_options as $key => $value) {
                                                                        $selected = ( $key == $vou_code_data['voucher_information']['pdf_template']) ? 'selected' : '' ;
                                                                        echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
                                                                    } ?>
                                                                </select>
															<?php
                                                            } else {
                                                                echo "<span>". get_the_title($vou_code_data['voucher_information']['pdf_template']). "</span><br />";
                                                                echo "<input type='hidden' name='".$prefix."pdf_template' value='". $vou_code_data['voucher_information']['pdf_template']. "'>";
                                                            }
	                                                    	break;
															
															 case 'other_vou_info':
																ob_start();
														?>
														<strong><?php esc_html_e('Coupon Code Generated', 'woovoucher') ?></strong><br><?php echo isset($vou_code_data['voucher_information']['is_coupon'])?$vou_code_data['voucher_information']['is_coupon']:esc_html__('N/A', 'woovoucher'); ?>
														<br><strong><?php esc_html_e('Choose Which Days Voucher cannot be Used', 'woovoucher') ?> </strong><br>
														<?php 
														$exlude_redeem_day = $vou_code_data['voucher_information']['exlude_redeem_day'];
														$day_name = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
														?><div class="woo-vou-exlude-days-wrap"><?php
														foreach($day_name as $day){
														?>
															<label>
															<input type="checkbox" name="<?php echo $prefix.'disable_redeem_day[]'?> " id="<?php echo $prefix.'disable_redeem_day_'.$day ?>" value="<?php echo $day ?>" 
																<?php 
																	if(in_array($day,$exlude_redeem_day)){
																		echo 'checked="checked"';
																	}
																?>
															> <?php esc_html_e($day,'woovoucher') ?></label>
														<?php
														}
														?></div><?php
														
														$column_value = ob_get_clean();			
	                                                    	
                                                           
	                                                    	break;
															
	                                                    default:
	                                                        $column_value .= '';
	                                                }
	
	                                                echo apply_filters( 'woo_vou_voucher_history_value_edit_row', $column_value, $col_key, $vou_code_data );
	                                                ?>
	                                            </td><?php }
	                                            ?>
	                                    </tr>
	                                    <tr class="woo-vou-history-value-row-edit">
	                                    	<td colspan="<?php echo count($vou_code_data['voucher_info_columns']); ?>" class="voucher-submit-td">
	                                    	<input type="hidden" value="<?php echo $vou_code_order_id; ?>" name="woo_vou_order_id" />
	                                    	<input type="hidden" value="<?php echo apply_filters( 'woo_vou_edit_voucode_item_id', $vou_code_item_id, $vou_code_data['item_id'], $vou_code_order_id ); ?>" name="woo_vou_item_id" />
	                                    	<input type="hidden" value="<?php echo $product_data_id; ?>" name="woo_vou_product_id" id="woo_vou_product_id" />
	                                    	<input type="hidden" value="<?php echo $vou_code_id; ?>" name="woo_vou_code_id" />
	         	                           	<input class="button woo-vou-voucher-information-edit-cancel" value="<?php esc_html_e( 'Cancel', 'woovoucher' ); ?>" type="button">
	         	                           	<input class="button button-primary" id="woo_vou_voucher_information_update" name="woo_vou_voucher_information_update" value="<?php esc_html_e( 'Update', 'woovoucher' ); ?>" type="submit">
	         	                           	
											</td>
	                                    </tr>
										
                                    <?php } ?>
                                </tbody>
                            </table>
							 </form>
                        </div>
                    </div>
                <?php } ?>
                <!-- Voucher Information Ends -->

                <!-- Recipient Information starts -->
                <div id="woo-vou-voucher-recipient-details" class="postbox ">
                	<?php 
                		$is_item_mail_sent = '';

						if( !empty( $vou_code_data['product_variations'] ) 
							&& array_key_exists( $prefix.'recipient_gift_email_send_item', $vou_code_data['product_variations'] ) ) {

							$is_item_mail_sent = $vou_code_data['product_variations'][$prefix.'recipient_gift_email_send_item'];
						}

                		if( !empty( $is_item_mail_sent ) ) {

                			$recipient_email_sent = false;
                			if( $is_item_mail_sent == 'yes' ) {

                				$recipient_email_sent = true;
                			}
                		} else {

                			$recipient_email_sent = get_post_meta($vou_code_order_id, $prefix . 'recipient_email_sent', true); // Get recipient gift email is sent or not 
                		}
                	?>
                    <h2 class="hndle ui-sortable-handle woo-vou-recipient-details-title"><span>
                    	<?php 

	                    	echo esc_html__( 'Recipient Information', 'woovoucher' ) . wc_help_tip(esc_html__('Recipient Details can only be changed until Recipient Gift Email is not sent.', 'woovoucher'), true);
	                    	if( $product_data_object && empty($recipient_email_sent) ){ 
                    	?>
                    	<a href="#" class="edit_recipient_details"><?php esc_html_e( 'Edit', 'woovoucher' ); ?></a>
                    	<?php } ?>
                    </span></h2>
                    <div class="inside">
                        <table class="widefat woo-vou-history-table woo-vou-recipient-info-table">
                            <tbody>
                                <tr class="woo-vou-history-title-row"> 
                                    <?php 

                                    $editable_recipient_cols = 4;
                                    $i = 1;
                                    // Get product recipient meta setting
            						$recipient_data = $woo_vou_model->woo_vou_get_product_recipient_meta($product_id);

            						// Initialise variables for recipient details
                                    $recipient_name_value = $recipient_email_value = $recipient_message_value = $recipient_giftdate_value = '';
                                    
                                    
                                    $recipient_name_required = $recipient_email_required = $recipient_message_required = $recipient_giftdate_required = '';

                                    if( !empty($vou_code_data['product_variations'][$prefix.'delivery_method']) ){
                                    	$recipient_delivery_label = $vou_code_data['product_variations'][$prefix.'delivery_method']['label'];
                                    	$recipient_delivery_value = $vou_code_data['product_variations'][$recipient_delivery_label];
                                    } else {
                                    	$recipient_delivery_label = esc_html__( 'Delivery Method', 'woovoucher' );
                                    }

                                    ?>
                                    <th class="woo-vou-uneditable"><?php echo esc_html__('Recipient Information', 'woovoucher'); ?></th>
                            		<th class="woo-vou-uneditable"><?php echo $recipient_delivery_label; ?></th>
                            		<th class="woo-vou-uneditable"><?php echo esc_html__('Mail Sent', 'woovoucher'); ?></th>
                                </tr>
                                <tr class="woo-vou-history-title-row woo-vou-recipient-value">
                                <?php 

                                	$send_recipient_button = esc_html__('Send', 'woovoucher');

                                	echo '<td><table>';
                                	// Looping on all the recipient columns
                                	foreach( $vou_code_data['recipient_columns'] as $recipient_col_key => $recipient_col_val ) {


                                		$recipient_col_value 		= $recipient_col_class = '';
                                    	$recipient_col_label 		= !empty( $recipient_col_val ) && array_key_exists( 'label', $recipient_col_val ) ? $recipient_col_val['label'] : '';
                                    	$recipient_col_required 	= ${$recipient_col_key.'_required'} = !empty( $recipient_data[$recipient_col_key.'_is_required'] ) ? 'data-required="yes"' : '';

                                    
                                    	if( !empty( $vou_code_data['product_variations'][$prefix.$recipient_col_key] ) ){

                                    		$recipient_col_label = ${$recipient_col_key.'_value'} = $vou_code_data['product_variations'][$prefix.$recipient_col_key]['label'];
                                    		$recipient_col_value = $vou_code_data['product_variations'][$prefix.$recipient_col_key]['value'];
                                    	}

                                        if( $recipient_col_key == 'recipient_name'){
                                            $recipient_name_value = $recipient_col_value;
                                        }
                                        if( $recipient_col_key == 'recipient_email'){
                                            $recipient_email_value = $recipient_col_value;
                                        }
                                        if( $recipient_col_key == 'recipient_message'){
                                            $recipient_message_value = $recipient_col_value;
                                        }
                                        if( $recipient_col_key == 'recipient_giftdate'){
                                            $date_format = get_option( 'date_format' );
                                            if( !empty( $recipient_col_value ) ) {
                                            $recipient_giftdate_value = date( $date_format, strtotime( $recipient_col_value ) );
                                            }
                                        }

                                    	echo '<tr>';
                                    	echo '<th>'.$recipient_col_label;
                                    	if(!empty($recipient_col_required)){

	                            			echo '<span class="woo-vou-gift-field-required"> *</span>';
	                            		}
                                    	echo '</th>';
                                    	if( !empty( $recipient_col_value ) ) {

	                                    	if( !empty( $recipient_col_val ) && is_array( $recipient_col_val )
	                                    		&& array_key_exists( 'type', $recipient_col_val ) ) {
	
	                                    		if( $recipient_col_val['type'] == 'date' ) {
		                                    		// Get date format from global setting
													$date_format = get_option( 'date_format' );
													echo '<td>' . date( $date_format, strtotime( $recipient_col_value ) ) . '</td>';
	                                    		} else if ( $recipient_col_val['type'] == 'textarea' ) {

	                                    			echo '<td>' . nl2br( $recipient_col_value ) . '</td>';
	                                    		} else {

	                                    			echo '<td>' . $recipient_col_value .'</td>';
	                                    		}
	                                    	}
                                    	} else {

                                    		echo '<td>' . esc_html__( 'N/A', 'woovoucher' ) . '</td>';
                                    	}
                                    	echo '</tr>';
                                	}
                                	echo '</table></td>';

                            		echo ( !empty( $recipient_delivery_value ) ) ? 	'<td>' . $recipient_delivery_value .'</td>' 	: '<td>' . esc_html__( 'N/A', 'woovoucher' ) . '</td>';

                            		$send_mail_btn_txt 	= esc_html__('Send Now', 'woovoucher');
                            		$html 				= '<td>' . esc_html__( 'No', 'woovoucher' );

                            		if( !empty( $recipient_email_sent ) ) {

                            			$send_mail_btn_txt = esc_html__('Resend', 'woovoucher');
                            			$html = '<td>' .esc_html__( 'Yes', 'woovoucher' );
                            		}

                            		if( $product_data_object && in_array($user_role, $voucher_admins) 
                            			|| in_array( $user_role, $woo_vou_vendor_role ) ) {

                            			$html .= '<br /><br /><button class="woo-vou-send-gift-email button button-primary">' . $send_mail_btn_txt . '</button>';
                            			$html .= '<input type="hidden" name="woo_vou_send_gift_first_name" id="woo_vou_send_gift_first_name" value="' . $vou_code_data['buyer_information']['first_name'] . '">';
                            			$html .= '<input type="hidden" name="woo_vou_send_gift_last_name" id="woo_vou_send_gift_last_name" value="' . $vou_code_data['buyer_information']['last_name'] . '">';
                            			$html .= '<input type="hidden" name="woo_vou_send_gift_recipient_name" id="woo_vou_send_gift_recipient_name" value="' . $recipient_name_value . '">';
                            			$html .= '<input type="hidden" name="woo_vou_send_gift_recipient_email" id="woo_vou_send_gift_recipient_email" value="' . $recipient_email_value . '">';
                            			$html .= '<input type="hidden" name="woo_vou_send_gift_recipient_message" id="woo_vou_send_gift_recipient_message" value="' . $recipient_message_value . '">';
                            			$html .= '<input type="hidden" name="woo_vou_order_id" id="woo_vou_order_id" value="' . $vou_code_order_id . '" />';
	                                    $html .= '<input type="hidden" name="woo_vou_item_id" id="woo_vou_item_id" value="' . $vou_code_data['item_id'] . '" />';
	                                    $html .= '<input type="hidden" name="woo_vou_product_id" id="woo_vou_product_id" value="' . $product_data_id . '" />';
	                                    $html .= '<input type="hidden" name="woo_vou_code_id" id="woo_vou_code_id" value="' . $vou_code_id . '" />';
                            		}

                            		$html .= '</td>';
                            		echo $html;
                            	?>
                                </tr>
                                <?php if( empty($recipient_email_sent) ){ // if recipient gift email not send then enable to edit recipient details
                            		$date_format  = apply_filters('woo_vou_recipient_gift_start_end_date_format', 'd-M-Y');
                            		$vou_min_date = date( $date_format, strtotime('+1 day')) ; // Format Voucher start date 
                            	?>
	                                <form class="woo-vou-recipient-details-edit-form" method="POST">
	                                	<tr class="woo-vou-history-title-row woo-vou-recipient-value-edit">
	                                		<th>
	                                    	<?php 

	                                    	$i = 1;

	                                    	if( !empty( $vou_code_data['recipient_columns'] ) ) {
		                                    	// Looping on all the recipient columns
		                                    	echo '<table class="widefat woo-vou-edit-history-table">';
	                                			foreach( $vou_code_data['recipient_columns'] as $recipient_col_key => $recipient_col_val ) {

	                                				if( $i%2 == 1 ) {
	                                					echo '<tr>';
	                                				}

	                                				$recipient_col_value	= '';
	                                				$recipient_col_label 	= !empty( $recipient_col_val ) && array_key_exists( 'label', $recipient_col_val ) ? $recipient_col_val['label'] : '';
	                                				$recipient_col_key_dash = str_replace('_', '-', $recipient_col_key);
	                                				$recipient_col_required = !empty( $recipient_data[$recipient_col_key.'_is_required'] ) ? 'data-required="yes"' : '';
	                                				if( !empty( $vou_code_data['product_variations'][$prefix.$recipient_col_key] ) ){

			                                    		$recipient_col_label = ${$recipient_col_key.'_value'} = $vou_code_data['product_variations'][$prefix.$recipient_col_key]['label'];
			                                    		$recipient_col_value = $vou_code_data['product_variations'][$prefix.$recipient_col_key]['value'];
			                                    	}
	                                				echo '<th><strong>'.$recipient_col_label;
	                                				if(!empty($recipient_col_required)){

				                            			echo '<span class="woo-vou-gift-field-required"> *</span>';
				                            		}
	                                				echo '</strong></th>';

	                                				echo '<td>';

	                                				if( !empty( $recipient_col_val ) && is_array( $recipient_col_val )
	                                					&& array_key_exists( 'type', $recipient_col_val ) ) {

	                                					if( $recipient_col_val['type'] == 'email' ) {

	                                						echo '<input type="email" data-dash-val="'.$recipient_col_key.'" class="woo_vou_cust_email_field woo-vou-'.$recipient_col_key_dash.'" name="'.$prefix.$recipient_col_key.'" value="'.$recipient_col_value.'" '.$recipient_col_required.' id="'.$prefix.$recipient_col_key.'" />';
					                                		if( !empty( $recipient_col_required ) ) {
					                                			echo '<p class="woo-vou-recipient-error woo-vou-'.$recipient_col_key_dash.'-err-message">'.esc_html__("Field", 'woovoucher').' '.$recipient_col_label.' '.esc_html__("is required.", 'woovoucher').'</p>';
					                                		}
	                                					} else if ( $recipient_col_val['type'] == 'date' ) {

	                                						$formatted_recipient_value = !empty( $recipient_col_value ) ? date( 'Y-m-d', strtotime( $recipient_col_value ) ) : '';
		                                					echo '<input type="text" data-dash-val="'.$recipient_col_key.'" rel="yy-mm-dd" class="woo_vou_cust_date_field woo-vou-'.$recipient_col_key_dash.'" name="'.$prefix.$recipient_col_key.'" value="'.$formatted_recipient_value.'" '.$recipient_col_required.' placeholder="YYYY-MM-DD" id="'.$prefix.$recipient_col_key.'" />';
					                                		if( !empty( $recipient_col_required ) ) {
					                                			echo '<p class="woo-vou-recipient-error woo-vou-'.$recipient_col_key_dash.'-err-message">'.esc_html__("Field", 'woovoucher').' '.$recipient_col_label.' '.esc_html__("is required.", 'woovoucher').'</p>';
					                                		}
	                                					} else if ( $recipient_col_val['type'] == 'textarea' ) {

	                                						echo '<textarea class="woo-vou-'.$recipient_col_key_dash.'" name="'.$prefix.$recipient_col_key.'"'.$recipient_col_required.' >'.$recipient_col_value.'</textarea>';
					                                		if( !empty( $recipient_col_required ) ) {
					                                			echo '<p class="woo-vou-recipient-error woo-vou-'.$recipient_col_key_dash.'-err-message">'.esc_html__("Field", 'woovoucher').' '.$recipient_col_label.' '.esc_html__("is required.", 'woovoucher').'</p>';
					                                		}
	                                					} else {

	                                						echo '<input type="text" class="woo-vou-'.$recipient_col_key_dash.'" name="'.$prefix.$recipient_col_key.'" value="'.$recipient_col_value.'" '.$recipient_col_required.' />';
					                                		if( !empty( $recipient_col_required ) ) {
					                                			echo '<p class="woo-vou-recipient-error woo-vou-'.$recipient_col_key_dash.'-err-message">'.esc_html__("Field", 'woovoucher').' '.$recipient_col_label.' '.esc_html__("is required.", 'woovoucher').'</p>';
					                                		}
	                                					}
	                                				} else {

	                                					echo '<input type="text" class="woo-vou-'.$recipient_col_key_dash.'" name="'.$prefix.$recipient_col_key.'" value="'.$recipient_col_value.'" '.$recipient_col_required.' />';
				                                		if( !empty( $recipient_col_required ) ) {
				                                			echo '<p class="woo-vou-recipient-error woo-vou-'.$recipient_col_key_dash.'-err-message">'.esc_html__("Field", 'woovoucher').' '.$recipient_col_label.' '.esc_html__("is required.", 'woovoucher').'</p>';
				                                		}
	                                				}

	                                				echo '</td>';

	                                				if( $i%2 == 0 ) {
	                                					echo '</tr>';
	                                				}

	                                				$i++;
	                                			}
	                                			echo '</table>';
	                                    	}
	                                        ?>
	                                        </th>
	                                	</tr>
	                                	<tr class="woo-vou-history-title-row woo-vou-recipient-value-edit">
	                                        <td colspan="2" align="right">
	                                        	<input type="hidden" value="<?php echo $vou_code_id; ?>" name="woo_vou_voucodeid" />
	                                        	<input type="hidden" value="recipient_information" name="woo_vou_update_recipient_info" />
	                                        	
	             	                           <input class="button woo-vou-recipient-details-edit-cancel" value="<?php esc_html_e( 'Cancel', 'woovoucher' ); ?>" type="button">
	             	                           <input class="button button-primary" id="woo_vou_recipient_details_update" name="woo_vou_recipient_details_update" value="<?php esc_html_e( 'Update', 'woovoucher' ); ?>" type="submit">
	                                        </td>
	                                	</tr>
	                                </form>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Recipient Information ends -->

                <!-- Buyer Information starts -->
                <?php if (!empty($vou_code_data['buyer_info_columns'])) { ?>
                    <div id="woo-vou-voucher-buyer-details" class="postbox ">
                        <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e( 'Buyer\'s Information', 'woovoucher' ); ?></span></h2>
                        <div class="inside">
                            <table class="widefat woo-vou-history-table">
                                <tbody>
                                    <tr class="woo-vou-history-title-row"> <?php foreach ($vou_code_data['buyer_info_columns'] as $col_key => $column) { ?>

                                            <th><?php echo $column; ?></th><?php } ?>
                                    </tr>               
                                    <tr>

                                        <?php foreach ($vou_code_data['buyer_info_columns'] as $col_key => $column) { ?>

                                            <td class="woo-vou-history-td"><?php
                                                $column_value = '';

                                                switch ($col_key) {

                                                    case 'buyer_name' :
                                                        $column_value = $vou_code_data['buyer_information']['first_name'] . '&nbsp' . $vou_code_data['buyer_information']['last_name'];
                                                        $is_user = get_user_by('email', $vou_code_data['buyer_information']['email']);
                                                        if (!empty($is_user)) {
                                                            if(in_array($user_role, $voucher_admins)) {
                                                                $column_value = '<a href="' . esc_url(admin_url('user-edit.php?user_id=' . absint($is_user->data->ID))) . '">' . $vou_code_data['buyer_information']['first_name'] . '&nbsp' . $vou_code_data['buyer_information']['last_name'] . '</a>';
                                                            } else {
                                                                $column_value = $vou_code_data['buyer_information']['first_name'] . '&nbsp' . $vou_code_data['buyer_information']['last_name'];
                                                            }
                                                        }
                                                        break;

                                                    case 'buyer_email' :
                                                        $column_value = $vou_code_data['buyer_information']['email'];
                                                        break;

                                                    case 'billing_address' :
                                                        $column_value = $vou_code_data['buyer_information']['billing_address'];
                                                        break;

                                                    case 'shipping_address' :
                                                        $column_value = $vou_code_data['buyer_information']['shipping_address'];
                                                        break;

                                                    case 'buyer_phone' :
                                                        $column_value = $vou_code_data['buyer_information']['phone'];
                                                        break;

                                                    default:
                                                        $column_value .= '';
                                                }

                                                echo $column_value
                                                ?>
                                            </td><?php }
                                            ?>

                                    </tr>
                                </tbody>
                            </table></div>
                    </div>
                <?php } ?>
                <!-- Buyer Information ends -->

                <!-- Order information starts -->
                <?php if (!empty($vou_code_data['order_info_columns'])) { ?>
                    <div id="woo-vou-voucher-order-details" class="postbox">
                        <h2 class="hndle ui-sortable-handle"><span><?php _e( 'Order Information', 'woovoucher' ); ?></span></h2>
                        <div class="inside">
                            <table class="widefat woo-vou-history-table">
                                <tbody>
                                    <tr class="woo-vou-history-title-row"> <?php foreach ($vou_code_data['order_info_columns'] as $col_key => $column) { ?>

                                            <th><?php echo $column; ?></th><?php } ?>
                                    </tr>               
                                    <tr>

                                        <?php foreach ($vou_code_data['order_info_columns'] as $col_key => $column) { ?>
                                            <td class="woo-vou-history-td"><?php
                                                switch ($col_key) {

                                                    case 'order_id' :
                                                        //$column_value = $order_information['order_id'];
                                                        if(in_array($user_role, $voucher_admins)) {
                                                            $column_value = '<a href="' . esc_url(admin_url('post.php?post=' . absint($vou_code_data['order_information']['order_id']) . '&action=edit')) . '">' . $vou_code_data['order_information']['order_id'] . '</a>';
                                                        } else {
                                                            $column_value = $vou_code_data['order_information']['order_id'];
                                                        }
                                                        
                                                        break;

                                                    case 'order_date' :
                                                        $column_value = $vou_code_data['order_information']['order_date'];
                                                        ;
                                                        break;

                                                    case 'payment_method' :
                                                        $column_value = $vou_code_data['order_information']['payment_method'];
                                                        ;
                                                        break;

                                                    case 'order_total':
                                                        $column_value = $vou_code_data['order_information']['order_total'];
                                                        ;
                                                        break;

                                                    case 'order_discount' :
                                                        $column_value = $vou_code_data['order_information']['order_discount'];
                                                        ;
                                                        break;

                                                    default:
                                                        $column_value .= '';
                                                }
                                                echo $column_value;
                                                ?>
                                            </td><?php }
                                            ?>

                                    </tr>
                                </tbody>
                          </table></div>
                    </div>
                <?php } ?>
                <!-- Order Information ends -->
            </div><!-- .meta-box-sortables ui-sortable -->

            <!-- Extra Note starts -->
            <div id="woo-vou-voucher-extra-note" class="postbox">
                <h2 class="hndle ui-sortable-handle">
                    <span><?php esc_html_e( 'Voucher Note', 'woovoucher' ); ?></span>
                    <?php if( in_array($user_role, $voucher_admins) || in_array( $user_role, $woo_vou_vendor_role ) ) { ?>
                    	<a href="#" class="edit_extra_note"><?php esc_html_e( 'Edit', 'woovoucher' ); ?></a>
                    <?php } ?>
                </h2>
                <div class="inside">
                    <table class="widefat woo-vou-extra-note-table">
                        <tbody>
                            <tr class="woo-vou-extra-note-row"> 
                                <td>
                                    <?php if( !empty($vou_code_data['voucher_extra_note']) ){
                                        echo nl2br( $vou_code_data['voucher_extra_note'] );
                                    } else {
                                        echo esc_html__( 'No Extra Note added.', 'woovoucher' );
                                    }
                                    ?>
                                </td>
                            </tr>
                            <form method="POST" class="woo-vou-extra-note-form">
                                <tr class="woo-vou-extra-note-row-edit">
                                    <td>
                                        <textarea name="woo_vou_extra_note" class="voucher_extra_note" ><?php echo $vou_code_data['voucher_extra_note']; ?></textarea>
                                    </td>
                                </tr>
                                <tr class="woo-vou-extra-note-row-edit">
                                    <td colspan="4" align="right">
                                        <input type="hidden" value="<?php echo $vou_code_id; ?>" name="woo_vou_code_id" />
                                        <input class="button woo-vou-extra-note-edit-cancel" value="<?php esc_html_e( 'Cancel', 'woovoucher' ); ?>" type="button">
                                        <input class="button button-primary" id="woo_vou_extra_note_update" name="woo_vou_extra_note_update" value="<?php esc_html_e( 'Update', 'woovoucher' ); ?>" type="submit">
                                    </td>
                                </tr>
                            </form>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Extra Note ends -->
        </div><!-- .metabox-holder -->
    </div><!-- #woo_vou_detail_voucher_code_wrap -->
</div><!-- .wrap -->
<div class="woo-vou-popup-content woo-vou-recipient-email-content">
				
	<div class="woo-vou-header">
		<div class="woo-vou-header-title"><?php esc_html_e( 'Send Gift Notification Email', 'woovoucher' ); ?></div>
		<div class="woo-vou-popup-close"><a href="javascript:void(0);" class="woo-vou-close-button"><img src="<?php echo esc_url(WOO_VOU_URL) .'includes/images/tb-close.png'; ?>" alt="<?php esc_html_e( 'Close','woovoucher' ); ?>"></a></div>
	</div>
		
	<div class="woo-vou-popup">

		<div class="woo-vou-recipient-email-message woo-vou-recipient-email-errors"><?php echo esc_html__('Please enter valid Email ID', 'woovoucher'); ?></div>
		<table class="form-table woo-vou-voucher-gift-notification-table">
			<tbody>
				<tr>
					<td colspan="2"> </td>
				</tr>
				<tr>
					<td width="30%" scope="col" class="woo-vou-recipient-email"><?php esc_html_e( 'Recipient Emails', 'woovoucher' ); ?></td>
					<td width="70%">
						<input id="woo_vou_recipient_email" name="woo_vou_recipient_email" class="woo-vou-change-recipient-email" placeholder="<?php echo esc_html__('Enter Recipient Emails', 'woovoucher'); ?>" value="" type="text">
						<input id="woo_voucher_id" name="woo_vou_voucher_id" type="hidden">
						<span class="description"><?php echo esc_html__( 'You can add multiple recipient emails by comma(",") separated.', 'woovoucher' ); ?></span>
					</td>
				</tr>

				<tr>
					<td colspan="2"> </td>
				</tr>

				<tr>
					<td scope="col"></td>
					<td>
						<input type="button" class="woo-vou-send-gift-notification-email button" value="<?php esc_html_e( 'Send', 'woovoucher' ); ?>" />
						<span class="woo-vou-loader-wrap">
							<img class="woo-vou-loader" src="<?php echo esc_url(WOO_VOU_URL) . 'includes/images/ajax-loader.gif'; ?>" alt="<?php esc_html_e('Loading...', 'woovoucher'); ?>" />
						</span>
					</td>
				</tr>
			</tbody>
			
		</table>
	</div><!--.woo-vou-popup-->
</div><!--.woo-vou-popup-content-->

<div class="woo-vou-popup-overlay woo-vou-expiry-date-overlay"></div>