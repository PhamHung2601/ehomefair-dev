<?php

/**
 * Handels to show import voucher code popup
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

	global $post;
		
?> 	

<div class="woo-vou-popup-content woo-vou-import-content">
				
	<div class="woo-vou-header">
		<div class="woo-vou-header-title"><?php esc_html_e( 'Generate / Import Codes', 'woovoucher' ); ?></div>
		<div class="woo-vou-popup-close"><a href="javascript:void(0);" class="woo-vou-close-button"><img src="<?php echo esc_url(WOO_VOU_URL) .'includes/images/tb-close.png'; ?>" alt="<?php esc_html_e( 'Close','woovoucher' ); ?>"></a></div>
	</div>
		
	<div class="woo-vou-popup">

		<div class="woo-vou-file-errors"></div>
		<form method="POST" action="" enctype="multipart/form-data" id="woo_vou_import_csv">
			<table class="form-table woo-vou-import-table">
				<tbody>
					<tr>
						<td colspan="2"><strong><?php esc_html_e( 'General', 'woovoucher' ); ?><strong></td>
					</tr>
					<tr>
						<td scope="col" class="woo-vou-field-title"><?php esc_html_e( 'Delete Existing Code', 'woovoucher' ); ?></td>
						<td>
							<select name="woo_vou_delete_code" class="woo-vou-delete-code">
								<option value=""><?php esc_html_e( 'No', 'woovoucher' ); ?></option>
								<option value="y"><?php esc_html_e( 'Yes', 'woovoucher' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<strong><?php esc_html_e( 'Generate Options', 'woovoucher' ); ?><strong>
						</td>
					</tr>
				</tbody>
				<tbody id="woo-vou-code-generate-part">
					<tr>
						<td scope="col" class="woo-vou-field-title"><?php esc_html_e( 'Number of Voucher Codes', 'woovoucher' ); ?></td>
						<td>
							<input type="text" class="woo-vou-no-of-voucher" value="" />
						</td>
					</tr>
					<tr class="woo-vou-submisssion-tr">
						<td scope="col" class="woo-vou-field-title woo-vou-submission-label"><?php esc_html_e( 'Submission', 'woovoucher' ); ?></td>
						<td>
							<span class="woo-vou-prefix-span"><strong><?php esc_html_e( 'Prefix', 'woovoucher' ); ?></strong></span>
							<span class="woo-vou-seperator-span"><strong><?php esc_html_e( 'Separator', 'woovoucher' ); ?></strong></span>
							<span class="woo-vou-pattern-span"><strong><?php esc_html_e( 'Pattern', 'woovoucher' ); ?></strong></span><br />
							<input type="text" class="woo-vou-code-prefix" value="" placeholder="WPWeb" />
							<input type="text" class="woo-vou-code-seperator" value="" placeholder="-" />
							<input type="text" class="woo-vou-code-pattern" value="" placeholder="LLDD" />
							<span class="woo-vou-generate-pattern-example"><a href="https://docs.wpwebelite.com/woocommerce-pdf-vouchers/pdf-vouchers-setup-docs/#wpweb_generate_code_example" target="_blank"><?php esc_html_e('View Example', 'woovoucher'); ?></a></span><br />
							<span class="description">
								<strong><?php esc_html_e( 'Prefix', 'woovoucher' ); ?></strong> - <?php esc_html_e( 'Prefix Text to appear before the code.', 'woovoucher' ); ?><br />
								<strong><?php esc_html_e( 'Separator', 'woovoucher' ); ?></strong> - <?php esc_html_e( 'Separator  symbol which appear between prefix and code.', 'woovoucher' ); ?><br />
								<strong><?php esc_html_e( 'Pattern', 'woovoucher' ); ?></strong> - <?php esc_html_e( 'Unique pattern for code. You can define a pattern using following characters. ', 'woovoucher' ); ?>
								<strong>L</strong> - <?php esc_html_e('Uppercase Letter, ', 'woovoucher'); ?><strong>l</strong> - <?php esc_html_e('Lowercase Letter, ', 'woovoucher'); ?><strong>D</strong> - <?php esc_html_e('Digit.', 'woovoucher') ?><br />
							</span>
						</td>
					</tr>
					<tr>
						<td scope="col"></td>
						<td>
							<input type="button" class="woo-vou-import-btn button-secondary" value="<?php esc_html_e( 'Generate Codes', 'woovoucher' ); ?>" />
							<img class="woo-vou-loader" src="<?php echo esc_url(WOO_VOU_URL) . 'includes/images/ajax-loader.gif'; ?>" alt="<?php esc_html_e('Loading...', 'woovoucher'); ?>" />
						</td>
					</tr>
				</tbody>
				<tbody>
					<tr>
						<td colspan="2">
							<strong><?php esc_html_e( 'Import Options', 'woovoucher' ); ?><strong>
						</td>
					</tr>
				</tbody>
				<tbody id="woo-vou-code-import-part">
					<tr>
						<td scope="col"><?php esc_html_e( 'CSV Separator', 'woovoucher' ); ?></td>
						<td>
							<input type="text" id="woo_vou_csv_sep" name="woo_vou_csv_sep" class="woo-vou-csv-sep"/>
						</td>
					</tr>
					<tr>
						<td scope="col"><?php esc_html_e( 'CSV Enclosure', 'woovoucher' ); ?></td>
						<td>
							<input type="text" id="woo_vou_csv_enc" name="woo_vou_csv_enc" class="woo-vou-csv-enc"/>
						</td>
					</tr>
					<tr>
						<td scope="col" class="woo-vou-field-title"><?php esc_html_e( 'Upload CSV File', 'woovoucher' ); ?></td>
						<td>
							<input type="file" id="woo_vou_csv_file" name="woo_vou_csv_file" class="woo-vou-csv-file"/>
						</td>
					</tr>
					<tr>
						<td scope="col"></td>
						<td>
							<input type="hidden" id="woo_vou_existing_code" name="woo_vou_existing_code" value="" />
							<input type="submit" name="woo_vou_import_csv" id="woo_vou_import_csv" value="<?php esc_html_e( 'Import Codes', 'woovoucher' ); ?>" class="button-secondary woo-vou-meta-vou-import-codes">
						</td>
					</tr>
						
				</tbody>
			</table>
		</form>
	</div><!--.woo-vou-popup-->
</div><!--.woo-vou-popup-content-->

<div class="woo-vou-popup-overlay woo-vou-import-overlay"></div>