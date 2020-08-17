<?php
/**
 * This page handles the template for seller commission.
 *
 * @package Woocommerce Marketplace
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1> <?php echo esc_html__( 'Set Seller Commision', 'marketplace' ); ?> </h1>
	<p><hr></p>
	<form action='' method='post' class="form-table" name='commision-form'>
	<table>
		<tr>
			<th><label for='commision'><?php echo esc_html__( 'Commision in fixed Rate', 'marketplace' ); ?></label></th>
			<td><input type='text' title='commision in %' class="regular-text" name='commision' value='<?php echo ( $com_res[0]->commision_on_seller . ' %' ); ?>' id='commisionid' /></td>
		</tr>
		<tr><th><label for='totalsale'><?php echo esc_html__( 'Total Sale', 'marketplace' ); ?></label></th>
			<td><input type='text' name='totalsale' class="regular-text" value='<?php echo ( $com_res[0]->seller_total_ammount + $com_res[0]->admin_amount . ' ' . $cur_symbol ); ?>' id='totalsale' readonly /></td>
		</tr>
		<tr><th><label for='admincommi'><?php echo esc_html__( 'Admin Commission', 'marketplace' ); ?></label></th>
			<td><input type='text' name='admincommi' class="regular-text" value='<?php echo ( $com_res[0]->admin_amount . ' ' . $cur_symbol ); ?>' id='admincommi' readonly /></td>
		</tr>
		<tr><th><label for='currentcomm'><?php echo esc_html__( 'Existing Commission', 'marketplace' ); ?></label></th>
			<td><input type='text' name='currentcomm' class="regular-text" value='<?php echo ( $com_res[0]->commision_on_seller . ' %' ); ?>' id='currentcomm' readonly /></td>
		</tr>
	</table>
	<p><input type="submit" value="Save" class="button button-primary" /><a href="<?php echo esc_url( admin_url( 'admin.php?page=Commissions' ) ); ?>" class="button button-primary" style="margin-left:10px;"><?php echo esc_html__( 'Back', 'marketplace' ); ?></a></p>
	</form>
</div>
