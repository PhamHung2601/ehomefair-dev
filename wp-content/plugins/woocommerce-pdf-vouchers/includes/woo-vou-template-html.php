<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Voucher Template Functions
 * 
 * All voucher template functions handles to 
 * different functions 
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.0.0
 */

/**
 * Create Default Templates
 * 
 * Handle to create default templates
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_create_default_templates() {

	global $user_ID;

	$prefix = WOO_VOU_META_PREFIX;

	// Create Multi Color Template
	$template_muc_content = '<table class="woo_vou_pdf_table">
							<tbody>
								<tr>
								<td colspan="1"></td>
								<td colspan="2">[woo_vou_logo]{vendorlogo}

								[/woo_vou_logo]</td>
								<td colspan="1"></td>
								</tr>
								<tr>
								<td colspan="4"></td>
								</tr>
								<tr>
								<td colspan="2">[woo_vou_code_title color="#000000" fontsize="18" textalign="left"]Voucher Code

								[/woo_vou_code_title][woo_vou_code codetextalign="left"]{codes}

								[/woo_vou_code]</td>
								<td colspan="2">[woo_vou_vendor_address]
								<p style="text-align: right;">{vendoraddress}</p>

								[/woo_vou_vendor_address]</td>
								</tr>
								<tr>
								<td colspan="2">[woo_vou_expire_date]Expire: {expiredate}

								[/woo_vou_expire_date]</td>
								<td colspan="2">[woo_vou_siteurl]
								<p style="text-align: right;">{siteurl}</p>

								[/woo_vou_siteurl]</td>
								</tr>
								<tr>
								<td colspan="4"></td>
								</tr>
								<tr>
								<td colspan="4">[woo_vou_redeem]
								<p style="text-align: center;">{redeem}</p>

								[/woo_vou_redeem]</td>
								</tr>
								<tr>
								<td colspan="4"></td>
								</tr>
								<tr>
								<td colspan="4">[woo_vou_location]
								<h3 style="text-align: center;">AVAILABLE AT</h3>
								<p style="text-align: center;"><span style="font-size: 10pt;">{location}</span></p>

								[/woo_vou_location]</td>
								</tr>
							</tbody>
						</table>';
	$template_muc_meta_content = '<div class="woo_vou_controls_editor text_column blankbox draghandle one_fourth" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/4</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="one_fourth" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column logoblock draghandle one_half" style="background-color: rgb(255, 255, 255); display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>{vendorlogo}</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_logo_width" name="woo_vou_text_width" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox draghandle one_fourth" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/4</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="one_fourth" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox full_width draghandle" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column textblock draghandle one_half" style="display: block; color: rgb(0, 0, 0); text-align: left; z-index: 0; left: 0px; top: 0px;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editcode" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text" style="font-size: 18pt;"><p>Voucher Code</p></div><div class="woo_vou_text_codes"><p>{codes}</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_text_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_text_bg" name="woo_vou_text_bg" value="" type="hidden"><input class="woo_vou_text_font_color" id="woo_vou_text_font_color" name="woo_vou_text_font_color" value="#000000" type="hidden"><input class="woo_vou_text_font_size" id="woo_vou_text_font_size" name="woo_vou_text_font_size" value="18" type="hidden"><input class="woo_vou_text_text_align" id="woo_vou_text_text_align" name="woo_vou_text_text_align" value="left" type="hidden"><input class="woo_vou_text_code_text_align" id="woo_vou_text_code_text_align" name="woo_vou_text_code_text_align" value="left" type="hidden"><input class="woo_vou_text_code_border" id="woo_vou_text_code_border" name="woo_vou_text_code_border" value="" type="hidden"><input class="woo_vou_text_code_column" id="woo_vou_text_code_column" name="woo_vou_text_code_column" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column venaddrblock draghandle one_half" style="display: block; z-index: 0; left: 0px; top: 0px;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editvenaddr" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p style="text-align: right;">{vendoraddress}</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_venaddr_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_venaddr_bg" name="woo_vou_venaddr_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column expireblock draghandle one_half" style="z-index: 0; left: 0px; top: 0px; display: block; background-color: rgb(255, 255, 255);"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editexpire" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Expire: {expiredate}</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_expire_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_expire_bg" name="woo_vou_expire_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column siteurlblock draghandle one_half" style="z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editsiteurl" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p style="text-align: right;">{siteurl}</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_siteurl_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_siteurl_bg" name="woo_vou_siteurl_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox full_width draghandle" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column messagebox full_width draghandle" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); display: block; z-index: 0; left: 0px; top: 0px;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editredeem" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p style="text-align: center;">{redeem}</p></div><input value="" class="woo_vou_text_bg" id="woo_vou_msg_color" name="woo_vou_msg_color" type="hidden"><input id="woo_vou_messagebox_width" class="woo_vou_txtclass_width" name="woo_vou_text_width" value="full_width" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox full_width draghandle" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column locblock draghandle full_width" style="display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editloc" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><h3 style="text-align: center;">AVAILABLE AT</h3><p style="text-align: center;"><span style="font-size: 10pt;">{location}</span></p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_loc_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_loc_bg" name="woo_vou_loc_bg" value="" type="hidden"></div>';
	$template_muc_page = array(
								'post_type' 	=> WOO_VOU_POST_TYPE,
								'post_status' 	=> 'publish',
								'post_title' 	=> esc_html__( 'Multi Color Template', 'woovoucher' ),
								'post_content' 	=> $template_muc_content,
								'post_author' 	=> $user_ID,
								'menu_order' 	=> 5,
								'comment_status' => 'closed'
							);

	$template_muc_page_id = wp_insert_post( $template_muc_page );

	if( $template_muc_page_id ) { //Check template id

		update_post_meta( $template_muc_page_id, $prefix . 'meta_content', $template_muc_meta_content );
		update_post_meta( $template_muc_page_id, $prefix . 'editor_status', 'true' );
		update_post_meta( $template_muc_page_id, $prefix . 'pdf_bg_style', 'pattern' );
		update_post_meta( $template_muc_page_id, $prefix . 'pdf_bg_pattern', 'pattern5' );
		update_post_meta( $template_muc_page_id, $prefix . 'pdf_bg_img', '' );
		update_post_meta( $template_muc_page_id, $prefix . 'pdf_bg_color', '#eaeaea' );
		update_post_meta( $template_muc_page_id, $prefix . 'pdf_view', 'land' );
		update_post_meta( $template_muc_page_id, $prefix . 'pdf_margin_top', '30' );
		update_post_meta( $template_muc_page_id, $prefix . 'pdf_margin_bottom', '25' );
		update_post_meta( $template_muc_page_id, $prefix . 'pdf_margin_left', '25' );
		update_post_meta( $template_muc_page_id, $prefix . 'pdf_margin_right', '25' );
	}

	// End code for Multi Color Template

	// Create Pink Template
	$template_pink_content = '<table class="woo_vou_pdf_table">
							<tbody>
								<tr>
									<td colspan="4">[woo_vou_logo]{vendorlogo}[/woo_vou_logo]</td>
								</tr>
								<tr>
									<td colspan="4"></td>
								</tr>
								<tr>
									<td colspan="2">[woo_vou_code_title color="#000000" fontsize="16" textalign="left"]Voucher Code[/woo_vou_code_title][woo_vou_code codetextalign="left"]{codes}[/woo_vou_code]</td>
									<td colspan="2">[woo_vou_vendor_address]{vendoraddress}[/woo_vou_vendor_address]</td>
								</tr>
								<tr>
									<td colspan="4"></td>
								</tr>
								<tr>
									<td colspan="2">[woo_vou_expire_date]Expire: {expiredate}[/woo_vou_expire_date]</td>
									<td colspan="2">[woo_vou_siteurl]{siteurl}[/woo_vou_siteurl]</td>
								</tr>
								<tr>
									<td colspan="4"></td>
								</tr>
								<tr>
									<td colspan="2">[woo_vou_redeem]{redeem}[/woo_vou_redeem]</td>
									<td colspan="2">[woo_vou_location]<h4>AVAILABLE AT</h4><span style="font-size: 10pt;">{location}</span>[/woo_vou_location]</td>
								</tr>
							</tbody>
						</table>';
	$template_pink_meta_content = '<div class="woo_vou_controls_editor text_column logoblock draghandle full_width" style="background-color: rgb(255, 255, 255); display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>{vendorlogo}</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_logo_width" name="woo_vou_text_width" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox full_width draghandle" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column textblock draghandle one_half" style="display: block; color: rgb(0, 0, 0); text-align: left;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editcode" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text" style="font-size: 16pt;"><p>Voucher Code</p></div><div class="woo_vou_text_codes"><p>{codes}</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_text_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_text_bg" name="woo_vou_text_bg" value="" type="hidden"><input class="woo_vou_text_font_color" id="woo_vou_text_font_color" name="woo_vou_text_font_color" value="#000000" type="hidden"><input class="woo_vou_text_font_size" id="woo_vou_text_font_size" name="woo_vou_text_font_size" value="16" type="hidden"><input class="woo_vou_text_text_align" id="woo_vou_text_text_align" name="woo_vou_text_text_align" value="left" type="hidden"><input class="woo_vou_text_code_text_align" id="woo_vou_text_code_text_align" name="woo_vou_text_code_text_align" value="left" type="hidden"><input class="woo_vou_text_code_border" id="woo_vou_text_code_border" name="woo_vou_text_code_border" value="" type="hidden"><input class="woo_vou_text_code_column" id="woo_vou_text_code_column" name="woo_vou_text_code_column" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column venaddrblock draghandle one_half" style="background-color: rgb(255, 255, 255); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editvenaddr" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>{vendoraddress}</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_venaddr_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_venaddr_bg" name="woo_vou_venaddr_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox full_width draghandle" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column expireblock draghandle one_half" style="background-color: rgb(255, 255, 255); display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editexpire" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Expire: {expiredate}</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_expire_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_expire_bg" name="woo_vou_expire_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column siteurlblock draghandle one_half" style="background-color: rgb(255, 255, 255); display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editsiteurl" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>{siteurl}</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_siteurl_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_siteurl_bg" name="woo_vou_siteurl_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox full_width draghandle" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column messagebox draghandle one_half" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editredeem" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>{redeem}</p></div><input value="" class="woo_vou_text_bg" id="woo_vou_msg_color" name="woo_vou_msg_color" type="hidden"><input id="woo_vou_messagebox_width" class="woo_vou_txtclass_width" name="woo_vou_text_width" value="one_half" type="hidden"></div><div class="woo_vou_controls_editor text_column locblock draghandle one_half" style="display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editloc" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><h4>AVAILABLE AT</h4><p><span style="font-size: 10pt;">{location}</span></p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_loc_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_loc_bg" name="woo_vou_loc_bg" value="" type="hidden"></div>';
	$template_pink_page = array(
								'post_type' 	=> WOO_VOU_POST_TYPE,
								'post_status' 	=> 'publish',
								'post_title' 	=> esc_html__( 'Pink Template', 'woovoucher' ),
								'post_content' 	=> $template_pink_content,
								'post_author' 	=> $user_ID,
								'menu_order' 	=> 4,
								'comment_status' => 'closed'
							);

	$template_pink_page_id = wp_insert_post( $template_pink_page );

	if( $template_pink_page_id ) { //Check template id

		update_post_meta( $template_pink_page_id, $prefix . 'meta_content', $template_pink_meta_content );
		update_post_meta( $template_pink_page_id, $prefix . 'editor_status', 'true' );
		update_post_meta( $template_pink_page_id, $prefix . 'pdf_bg_style', 'pattern' );
		update_post_meta( $template_pink_page_id, $prefix . 'pdf_bg_pattern', 'pattern4' );
		update_post_meta( $template_pink_page_id, $prefix . 'pdf_bg_img', '' );
		update_post_meta( $template_pink_page_id, $prefix . 'pdf_bg_color', '#cccccc' );
		update_post_meta( $template_pink_page_id, $prefix . 'pdf_view', 'land' );
		update_post_meta( $template_pink_page_id, $prefix . 'pdf_margin_top', '30' );
		update_post_meta( $template_pink_page_id, $prefix . 'pdf_margin_bottom', '25' );
		update_post_meta( $template_pink_page_id, $prefix . 'pdf_margin_left', '25' );
		update_post_meta( $template_pink_page_id, $prefix . 'pdf_margin_right', '25' );
	}

	// End code for Pink Template

	// Create Blue Template
	$template_blue_content = '<table class="woo_vou_pdf_table">
							<tbody>
								<tr>
									<td colspan="4">[woo_vou_site_logo]{sitelogo}[/woo_vou_site_logo]</td>
								</tr>
								<tr>
									<td colspan="4">[woo_vou_logo]{vendorlogo}[/woo_vou_logo]</td>
								</tr>
								<tr>
									<td colspan="4"></td>
								</tr>
								<tr>
									<td colspan="4">[woo_vou_code_title fontsize="16" textalign="left"]<span style="color: #1e73be;">Voucher Code</span>[/woo_vou_code_title][woo_vou_code codetextalign="left"]{codes}[/woo_vou_code]</td>
								</tr>
								<tr>
									<td colspan="4"></td>
								</tr>
								<tr>
									<td colspan="4">[woo_vou_vendor_address]{vendoraddress}[/woo_vou_vendor_address]</td>
								</tr>
								<tr>
									<td colspan="4">[woo_vou_siteurl]{siteurl}[/woo_vou_siteurl]</td>
								</tr>
								<tr>
									<td colspan="4">[woo_vou_expire_date]Expire: {expiredate}[/woo_vou_expire_date]</td>
								</tr>
								<tr>
									<td colspan="4"></td>
								</tr>
								<tr>
									<td colspan="4">[woo_vou_redeem]{redeem}[/woo_vou_redeem]</td>
								</tr>
								<tr>
									<td colspan="4"></td>
								</tr>
								<tr>
									<td colspan="4">[woo_vou_location]<h3 style="text-align: center;"><span style="color: #1e73be;">AVAILABLE AT</span></h3><p style="text-align: center;"><span style="font-size: 10pt;">{location}</span></p>[/woo_vou_location]</td>
								</tr>
							</tbody>
						</table>';
	$template_blue_meta_content = '<div class="woo_vou_controls_editor text_column sitelogoblock draghandle full_width" style="background-color: rgb(255, 255, 255); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>{sitelogo}</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_site_logo_width" name="woo_vou_text_width" type="hidden"></div><div class="woo_vou_controls_editor text_column logoblock draghandle full_width" style="background-color: rgb(255, 255, 255); display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>{vendorlogo}</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_logo_width" name="woo_vou_text_width" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox full_width draghandle" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column textblock full_width draghandle" style="display: block; color: rgb(30, 115, 190); text-align: left;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editcode" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text" style="font-size: 16pt;"><p><span style="color: #1e73be;">Voucher Code</span></p></div><div class="woo_vou_text_codes"><p>{codes}</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_text_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_text_bg" name="woo_vou_text_bg" value="" type="hidden"><input class="woo_vou_text_font_color" id="woo_vou_text_font_color" name="woo_vou_text_font_color" value="#1e73be" type="hidden"><input class="woo_vou_text_font_size" id="woo_vou_text_font_size" name="woo_vou_text_font_size" value="16" type="hidden"><input class="woo_vou_text_text_align" id="woo_vou_text_text_align" name="woo_vou_text_text_align" value="left" type="hidden"><input class="woo_vou_text_code_text_align" id="woo_vou_text_code_text_align" name="woo_vou_text_code_text_align" value="left" type="hidden"><input class="woo_vou_text_code_border" id="woo_vou_text_code_border" name="woo_vou_text_code_border" value="" type="hidden"><input class="woo_vou_text_code_column" id="woo_vou_text_code_column" name="woo_vou_text_code_column" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox full_width draghandle" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column venaddrblock full_width draghandle" style="background-color: rgb(255, 255, 255); display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editvenaddr" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>{vendoraddress}</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_venaddr_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_venaddr_bg" name="woo_vou_venaddr_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column siteurlblock draghandle full_width" style="background-color: rgb(255, 255, 255); display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editsiteurl" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>{siteurl}</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_siteurl_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_siteurl_bg" name="woo_vou_siteurl_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column expireblock draghandle full_width" style="background-color: rgb(255, 255, 255); display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editexpire" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Expire: {expiredate}</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_expire_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_expire_bg" name="woo_vou_expire_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox full_width draghandle" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column messagebox full_width draghandle" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editredeem" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>{redeem}</p></div><input value="" class="woo_vou_text_bg" id="woo_vou_msg_color" name="woo_vou_msg_color" type="hidden"><input id="woo_vou_messagebox_width" class="woo_vou_txtclass_width" name="woo_vou_text_width" value="full_width" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox full_width draghandle" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column locblock full_width draghandle" style="z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editloc" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><h3 style="text-align: center;"><span style="color: #1e73be;">AVAILABLE AT</span></h3><p style="text-align: center;"><span style="font-size: 10pt;">{location}</span></p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_loc_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_loc_bg" name="woo_vou_loc_bg" value="" type="hidden"></div>';
	$template_blue_page = array(
								'post_type' 	=> WOO_VOU_POST_TYPE,
								'post_status' 	=> 'publish',
								'post_title' 	=> esc_html__( 'Blue Template', 'woovoucher' ),
								'post_content' 	=> $template_blue_content,
								'post_author' 	=> $user_ID,
								'menu_order' 	=> 3,
								'comment_status' => 'closed'
							);

	$template_blue_page_id = wp_insert_post( $template_blue_page );

	if( $template_blue_page_id ) { //Check template id

		update_post_meta( $template_blue_page_id, $prefix . 'meta_content', $template_blue_meta_content );
		update_post_meta( $template_blue_page_id, $prefix . 'editor_status', 'true' );
		update_post_meta( $template_blue_page_id, $prefix . 'pdf_bg_style', 'pattern' );
		update_post_meta( $template_blue_page_id, $prefix . 'pdf_bg_pattern', 'pattern3' );
		update_post_meta( $template_blue_page_id, $prefix . 'pdf_bg_img', '' );
		update_post_meta( $template_blue_page_id, $prefix . 'pdf_bg_color', '' );
		update_post_meta( $template_blue_page_id, $prefix . 'pdf_view', 'port' );
		update_post_meta( $template_blue_page_id, $prefix . 'pdf_margin_top', '30' );
		update_post_meta( $template_blue_page_id, $prefix . 'pdf_margin_bottom', '25' );
		update_post_meta( $template_blue_page_id, $prefix . 'pdf_margin_left', '20' );
		update_post_meta( $template_blue_page_id, $prefix . 'pdf_margin_right', '20' );
	}

	// End code for Blue Template

	// Create Green Template
	$template_green_content = '<table class="woo_vou_pdf_table">
							<tbody>
								<tr>
								<td colspan="1"></td>
								<td colspan="2">[woo_vou_logo]{vendorlogo}

								[/woo_vou_logo]</td>
								<td colspan="1"></td>
								</tr>
								<tr>
								<td colspan="4"></td>
								</tr>
								<tr>
								<td colspan="2">[woo_vou_vendor_address]{vendoraddress}

								[/woo_vou_vendor_address]</td>
								<td colspan="2">[woo_vou_code_title color="#000000" fontsize="18" textalign="right"]Voucher Code

								[/woo_vou_code_title][woo_vou_code codetextalign="right"]{codes}

								[/woo_vou_code]</td>
								</tr>
								<tr>
								<td colspan="2">[woo_vou_siteurl]{siteurl}

								[/woo_vou_siteurl]</td>
								<td colspan="2">[woo_vou_expire_date]
								<p style="text-align: right;">Expire: {expiredate}</p>

								[/woo_vou_expire_date]</td>
								</tr>
								<tr>
								<td colspan="4"></td>
								</tr>
								<tr>
								<td colspan="4">[woo_vou_redeem]{redeem}

								[/woo_vou_redeem]</td>
								</tr>
								<tr>
								<td colspan="4">[woo_vou_location]
								<h3 style="text-align: center;">AVAILABLE AT</h3>
								<p style="text-align: center;"><span style="font-size: 10pt;">{location}</span></p>

								[/woo_vou_location]</td>
								</tr>
							</tbody>
						</table>';
	$template_green_meta_content = '<div class="woo_vou_controls_editor text_column blankbox draghandle one_fourth" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/4</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="one_fourth" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column logoblock draghandle one_half" style="background-color: rgb(255, 255, 255); display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>{vendorlogo}</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_logo_width" name="woo_vou_text_width" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox draghandle one_fourth" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/4</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="one_fourth" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox draghandle full_width" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); display: block; z-index: 0; left: 0px; top: 0px;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column venaddrblock draghandle one_half" style="background-color: rgb(255, 255, 255); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editvenaddr" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>{vendoraddress}</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_venaddr_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_venaddr_bg" name="woo_vou_venaddr_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column textblock draghandle one_half" style="display: block; color: rgb(0, 0, 0); text-align: right; z-index: 0; left: 0px; top: 0px;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editcode" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text" style="font-size: 18pt;"><p>Voucher Code</p></div><div class="woo_vou_text_codes"><p>{codes}</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_text_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_text_bg" name="woo_vou_text_bg" value="" type="hidden"><input class="woo_vou_text_font_color" id="woo_vou_text_font_color" name="woo_vou_text_font_color" value="#000000" type="hidden"><input class="woo_vou_text_font_size" id="woo_vou_text_font_size" name="woo_vou_text_font_size" value="18" type="hidden"><input class="woo_vou_text_text_align" id="woo_vou_text_text_align" name="woo_vou_text_text_align" value="right" type="hidden"><input class="woo_vou_text_code_text_align" id="woo_vou_text_code_text_align" name="woo_vou_text_code_text_align" value="right" type="hidden"><input class="woo_vou_text_code_border" id="woo_vou_text_code_border" name="woo_vou_text_code_border" value="" type="hidden"><input class="woo_vou_text_code_column" id="woo_vou_text_code_column" name="woo_vou_text_code_column" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column siteurlblock draghandle one_half" style="background-color: rgb(255, 255, 255); display: block; z-index: 0; left: 0px; top: 0px;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editsiteurl" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>{siteurl}</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_siteurl_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_siteurl_bg" name="woo_vou_siteurl_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column expireblock draghandle one_half" style="z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editexpire" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p style="text-align: right;">Expire: {expiredate}</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_expire_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_expire_bg" name="woo_vou_expire_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox full_width draghandle" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); display: block; z-index: 0; left: 0px; top: 0px;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column messagebox draghandle full_width" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); display: block; z-index: 0; left: 0px; top: 0px;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editredeem" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>{redeem}</p></div><input value="" class="woo_vou_text_bg" id="woo_vou_msg_color" name="woo_vou_msg_color" type="hidden"><input id="woo_vou_messagebox_width" class="woo_vou_txtclass_width" name="woo_vou_text_width" value="full_width" type="hidden"></div><div class="woo_vou_controls_editor text_column locblock full_width draghandle" style="display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editloc" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><h3 style="text-align: center;">AVAILABLE AT</h3><p style="text-align: center;"><span style="font-size: 10pt;">{location}</span></p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_loc_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_loc_bg" name="woo_vou_loc_bg" value="" type="hidden"></div>';
	$template_green_page = array(
									'post_type' 	=> WOO_VOU_POST_TYPE,
									'post_status' 	=> 'publish',
									'post_title' 	=> esc_html__( 'Green Template', 'woovoucher' ),
									'post_content' 	=> $template_green_content,
									'post_author' 	=> $user_ID,
									'menu_order' 	=> 2,
									'comment_status' => 'closed'
								);

	$template_green_page_id = wp_insert_post( $template_green_page );

	if( $template_green_page_id ) { //Check template id

		update_post_meta( $template_green_page_id, $prefix . 'meta_content', $template_green_meta_content );
		update_post_meta( $template_green_page_id, $prefix . 'editor_status', 'true' );
		update_post_meta( $template_green_page_id, $prefix . 'pdf_bg_style', 'pattern' );
		update_post_meta( $template_green_page_id, $prefix . 'pdf_bg_pattern', 'pattern1' );
		update_post_meta( $template_green_page_id, $prefix . 'pdf_bg_img', '' );
		update_post_meta( $template_green_page_id, $prefix . 'pdf_bg_color', '#e0e0e0' );
		update_post_meta( $template_green_page_id, $prefix . 'pdf_view', 'land' );
		update_post_meta( $template_green_page_id, $prefix . 'pdf_margin_top', '30' );
		update_post_meta( $template_green_page_id, $prefix . 'pdf_margin_bottom', '25' );
		update_post_meta( $template_green_page_id, $prefix . 'pdf_margin_left', '22' );
		update_post_meta( $template_green_page_id, $prefix . 'pdf_margin_right', '22' );
	}

	// End code for Green Template

	// Create Default Template
	$default_template_content = '<table class="woo_vou_pdf_table">
									<tbody>
										<tr>
											<td colspan="1"></td>
											<td colspan="2">[woo_vou_logo]{vendorlogo}

											[/woo_vou_logo]</td>
											<td colspan="1"></td>
										</tr>
										<tr>
											<td colspan="4"></td>
										</tr>
										<tr>
											<td colspan="2">[woo_vou_code_title fontsize="18" textalign="left"]Voucher Code

											[/woo_vou_code_title][woo_vou_code codetextalign="left"]{codes}

											[/woo_vou_code]</td>
											<td colspan="2">[woo_vou_vendor_address]
											<p style="text-align: right;">{vendoraddress}</p>

											[/woo_vou_vendor_address]</td>
										</tr>
										<tr>
											<td colspan="2"></td>
											<td colspan="2">[woo_vou_siteurl]
											<p style="text-align: right;">{siteurl}</p>

											[/woo_vou_siteurl]</td>
										</tr>
										<tr>
											<td colspan="2"></td>
											<td colspan="2">[woo_vou_expire_date]
											<p style="text-align: right;">Expire: {expiredate}</p>

											[/woo_vou_expire_date]</td>
										</tr>
										<tr>
											<td colspan="4"></td>
										</tr>
										<tr>
											<td colspan="4"></td>
										</tr>
										<tr>
											<td colspan="4">[woo_vou_redeem]
											<p style="text-align: center;">{redeem}</p>

											[/woo_vou_redeem]</td>
										</tr>
										<tr>
											<td colspan="4"></td>
										</tr>
										<tr>
											<td colspan="1"></td>
											<td colspan="2">[woo_vou_location]
											<h3 style="text-align: center;">AVAILABLE AT</h3>
											<p style="text-align: center;"><span style="font-size: 10pt;">{location}</span></p>

											[/woo_vou_location]</td>
											<td colspan="1"></td>
										</tr>
									</tbody>
								</table>';
	$default_template_meta_content = '<div class="woo_vou_controls_editor text_column blankbox draghandle one_fourth" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/4</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="one_fourth" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column logoblock draghandle one_half" style="background-color: rgb(255, 255, 255); display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>{vendorlogo}</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_logo_width" name="woo_vou_text_width" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox draghandle one_fourth" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/4</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="one_fourth" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column textblock draghandle one_half" style="display: block; color: rgb(0, 0, 0); text-align: left; z-index: 0; left: 0px; top: 0px;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editcode" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text" style="font-size: 18pt;"><p>Voucher Code</p></div><div class="woo_vou_text_codes"><p>{codes}</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_text_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_text_bg" name="woo_vou_text_bg" value="" type="hidden"><input class="woo_vou_text_font_color" id="woo_vou_text_font_color" name="woo_vou_text_font_color" value="#000000" type="hidden"><input class="woo_vou_text_font_size" id="woo_vou_text_font_size" name="woo_vou_text_font_size" value="18" type="hidden"><input class="woo_vou_text_text_align" id="woo_vou_text_text_align" name="woo_vou_text_text_align" value="left" type="hidden"><input class="woo_vou_text_code_text_align" id="woo_vou_text_code_text_align" name="woo_vou_text_code_text_align" value="left" type="hidden"><input class="woo_vou_text_code_border" id="woo_vou_text_code_border" name="woo_vou_text_code_border" value="" type="hidden"><input class="woo_vou_text_code_column" id="woo_vou_text_code_column" name="woo_vou_text_code_column" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column venaddrblock draghandle one_half" style="display: block; z-index: 0; left: 0px; top: 0px;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editvenaddr" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p style="text-align: right;">{vendoraddress}</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_venaddr_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_venaddr_bg" name="woo_vou_venaddr_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox draghandle one_half" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column siteurlblock draghandle one_half" style="z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editsiteurl" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p style="text-align: right;">{siteurl}</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_siteurl_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_siteurl_bg" name="woo_vou_siteurl_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox draghandle one_half" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column expireblock draghandle one_half" style="z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editexpire" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p style="text-align: right;">Expire: {expiredate}</p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_expire_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_expire_bg" name="woo_vou_expire_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox full_width draghandle" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column messagebox full_width draghandle" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); display: block; z-index: 0; left: 0px; top: 0px;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editredeem" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p style="text-align: center;">{redeem}</p></div><input value="" class="woo_vou_text_bg" id="woo_vou_msg_color" name="woo_vou_msg_color" type="hidden"><input id="woo_vou_messagebox_width" class="woo_vou_txtclass_width" name="woo_vou_text_width" value="full_width" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox full_width draghandle" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/1</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="full_width" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox draghandle one_fourth" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/4</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="one_fourth" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column locblock draghandle one_half" style="display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/2</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_change editloc" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><h3 style="text-align: center;">AVAILABLE AT</h3><p style="text-align: center;"><span style="font-size: 10pt;">{location}</span></p></div><input value="one_half" class="woo_vou_txtclass_width" id="woo_vou_loc_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_text_bg" id="woo_vou_loc_bg" name="woo_vou_loc_bg" value="" type="hidden"></div><div class="woo_vou_controls_editor text_column blankbox draghandle one_fourth" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); z-index: 0; left: 0px; top: 0px; display: block;"><div class="woo_vou_controller"><a class="woo_vou_lesser_width" href="javascript:void(0);"></a><span class="width_size">1/4</span><a class="woo_vou_greater_width" href="javascript:void(0);"></a><a class="woo_vou_remove" href="javascript:void(0);"></a></div><div class="woo_vou_text"><p>Blank Block</p></div><input value="one_fourth" class="woo_vou_txtclass_width" id="woo_vou_blank_width" name="woo_vou_text_width" type="hidden"><input class="woo_vou_blank_bg" id="woo_vou_blank_bg" name="woo_vou_blank_bg" value="" type="hidden"></div>';
	$default_template_page = array(
									'post_type' 	=> WOO_VOU_POST_TYPE,
									'post_status' 	=> 'publish',
									'post_title' 	=> esc_html__( 'Default Template', 'woovoucher' ),
									'post_content' 	=> $default_template_content,
									'post_author' 	=> $user_ID,
									'menu_order' 	=> 1,
									'comment_status' => 'closed'
								);

	$default_template_page_id = wp_insert_post( $default_template_page );

	if( $default_template_page_id ) { //Check template id

		update_post_meta( $default_template_page_id, $prefix . 'meta_content', $default_template_meta_content );
		update_post_meta( $default_template_page_id, $prefix . 'editor_status', 'true' );
		update_post_meta( $default_template_page_id, $prefix . 'pdf_bg_style', 'pattern' );
		update_post_meta( $default_template_page_id, $prefix . 'pdf_bg_pattern', 'pattern2' );
		update_post_meta( $default_template_page_id, $prefix . 'pdf_bg_img', '' );
		update_post_meta( $default_template_page_id, $prefix . 'pdf_bg_color', '#eaeaea' );
		update_post_meta( $default_template_page_id, $prefix . 'pdf_view', 'land' );
		update_post_meta( $default_template_page_id, $prefix . 'pdf_margin_top', '20' );
		update_post_meta( $default_template_page_id, $prefix . 'pdf_margin_bottom', '25' );
		update_post_meta( $default_template_page_id, $prefix . 'pdf_margin_left', '25' );
		update_post_meta( $default_template_page_id, $prefix . 'pdf_margin_right', '25' );
	}

	$default_templates = array(
									'default_template' 	=> $default_template_page_id,
									'green_template'	=> $template_green_page_id,
									'blue_template' 	=> $template_blue_page_id,
									'pink_template' 	=> $template_pink_page_id,
									'muc_template' 		=> $template_muc_page_id,
								);

	return $default_templates;
}