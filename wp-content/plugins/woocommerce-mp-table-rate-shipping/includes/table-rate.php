<?php
/**
 * Marketplace Table Rate Shipping Functions
 *
 * @package     Woocommerce Marketplace Table Rate Shipping
 * @copyright   Copyright (c) 2017, Webkul
 *
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function table_rate_shipping() {

	?>

	<div class="woocommerce-account">

		<?php
		apply_filters( 'mp_get_wc_account_menu', 'marketplace' );
		?>

		<div class="woocommerce-MyAccount-content">

			<?php

			global $woocommerce;

			global $wpdb;

			$seller_id = get_current_user_id();

			$table_rate = $wpdb->prefix . "woocommerce_table_rate_shipping";

			// Submiting post data
			if( isset( $_POST['submit_csv'] ) ) {

				if ( ! empty( $_POST['shipping_table_rate_nonce'] ) && isset($_POST['shipping_table_rate_nonce'] ) ) {

					if( ! wp_verify_nonce( $_POST['shipping_table_rate_nonce'], 'shipping_table_rate_action' ) ) {

						print 'Sorry, your nonce did not verify.';

						exit;

					}
					else{

						$sanitized_data = $_POST;

						$response = saveShippingFields::update_table_rate_shipping( $sanitized_data, $_FILES );

					}
				}


			}
			if( isset( $_POST['submit_shipping'] ) ) {

				if ( ! empty( $_POST['shipping_table_rate_nonce'] ) && isset($_POST['shipping_table_rate_nonce'] ) ) {

					if( ! wp_verify_nonce( $_POST['shipping_table_rate_nonce'], 'shipping_table_rate_action' ) ) {

						print 'Sorry, your nonce did not verify.';

						exit;

					}
					else{

						$sanitized_data = $_POST;

						$response = saveShippingFields::update_table_rate_shipping( $sanitized_data );

					}
				}
			}

			$query = $wpdb->prepare("SELECT * FROM $table_rate WHERE seller_id = %d", $seller_id);

			$table_rate_shipping_data = $wpdb->get_results($query);

			?>

			<h2><?php _e( 'Marketplace Table Rate Shipping', 'mp_table_rate' ); ?></h2>

			<div class="table_rate_shipping_container">

				<h3><?php _e( 'Upload Shipping Details', 'mp_table_rate' ); ?></h3>

				<form action="" method="post" enctype="multipart/form-data">

					<?php wp_nonce_field( 'shipping_table_rate_action', 'shipping_table_rate_nonce' ); ?>

					<input type="file" name="csv_import" >

					<input type="submit" name="submit_csv" value="Import CSV">

					<a href="<?php echo plugin_dir_url(__DIR__).'media/custom_mp_table_rate.csv';?>" class="button"><?php _e( 'Download Sample File', 'mp_table_rate' ); ?></a>

				</form>

				<form action="" method="post">

					<?php wp_nonce_field( 'shipping_table_rate_action', 'shipping_table_rate_nonce' ); ?>

					<table style="width:100%;" class="table_rate_shipping">

						<thead>
							<tr>
								<th><?php _e( 'Zone Label', 'mp_table_rate' ); ?></th>
								<th><?php _e( 'Zone Region', 'mp_table_rate' ); ?></th>
								<th><?php _e( 'Select Basis', 'mp_table_rate' ); ?></th>
								<th><?php _e( 'Min Value', 'mp_table_rate' ); ?></th>
								<th><?php _e( 'Max Value', 'mp_table_rate' ); ?></th>
								<th><?php _e( 'Price', 'mp_table_rate' ); ?></th>
								<th><?php _e( 'Action', 'mp_table_rate' ); ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td>
									<button id="insert_new" class="button-primary"><?php _e( 'Insert Row', 'mp_table_rate' ); ?></button>
								</td>
							</tr>
						</tfoot>
						<tbody>

							<?php

							if( !empty( $table_rate_shipping_data ) && isset( $table_rate_shipping_data ) ) :

							$j = 0;

							$selected = '';

							foreach( $table_rate_shipping_data as $new_data ) :

							if ( isset( $new_data->shipping_basis ) && !empty( $new_data->shipping_basis ) ) {

								$select_basis = $new_data->shipping_basis;

							} ?>

							<tr>

								<td>

									<input type='text' name='_table_zname[0<?php echo $j; ?>]' value="<?php echo $new_data->shipping_label;?>" placeholder='Country Code eg-US'>

								</td>

								<td>

									<?php

									$ship_zones = $new_data->shipping_zone;

									if( strpos( $ship_zones, "," ) ) {

										$zone_c = explode( ",", $ship_zones );

									}
									else{

										if( !empty( $ship_zones ) )
										$zone_c = $ship_zones;
										else
										$zone_c = '';
									}

									?>
									<select name="selected_zone[0<?php echo $j; ?>][]" multiple="multiple" class="chosen_select enhanced wp-shipping-table-rate" style="width:160px;">

										<?php

										$countries = WC()->countries->countries;

										foreach ( $countries as $c_key => $c_value ) {
											if( !empty( $zone_c ) ) {

												if( is_array( $zone_c ) ) {

													if( in_array( $c_key, $zone_c ) ) {

														$selected = "selected";

														echo "<option value=".$c_key." ".$selected .">".$c_value."</option>";

													}
													else{

														$selected = '';

														echo "<option value=".$c_key." ".$selected .">".$c_value."</option>";

													}

												}
												else{

													if( $c_key == $zone_c ) {

														$selected = "selected";

													}
													else{

														$selected = '';

													}

													echo "<option value=".$c_key." ".$selected .">".$c_value."</option>";

												}
											}
											else{

												echo "<option value=".$c_key.">".$c_value."</option>";

											}


										}

										?>

									</select>

								</td>

								<td>

									<select name="select_type[0<?php echo $j; ?>]" class="shipping_basis_selector">

										<option><?php _e( 'Select Type', 'mp_table_rate' ); ?></option>

										<option value="pro_weight" <?php if(!empty($select_basis) && $select_basis=='pro_weight') echo "selected";?>><?php _e( 'Weight', 'mp_table_rate' ); ?></option>

										<option value="pro_pincode" <?php if(!empty($select_basis) && $select_basis=='pro_pincode') echo "selected";?>><?php _e( 'Pincode', 'mp_table_rate' ); ?></option>

										<option value="pro_global" <?php if( !empty( $select_basis ) && $select_basis=='pro_global') echo "selected";?>><?php _e( 'Global Shipping', 'mp_table_rate' ); ?></option>


									</select>

								</td>

								<td>

									<input type='text' name='_table_min_val[0<?php echo $j; ?>]' value="<?php echo $new_data->shipping_min;?>" placeholder='eg-1234' <?php if( !empty( $select_basis ) && $select_basis=='pro_global') echo "readonly";?>>

								</td>

								<td>

									<input type='text' name='_table_max_val[0<?php echo $j; ?>]' value="<?php echo $new_data->shipping_max; ?>" placeholder='eg-1234' <?php if( !empty( $select_basis ) && $select_basis=='pro_global') echo "readonly";?>>

								</td>

								<td>

									<input type='text' name='_ship_price[0<?php echo $j; ?>]' value="<?php echo $new_data->shipping_cost; ?>" placeholder='eg-1234'>

								</td>

								<td>

									<input type="hidden" name="shipping_id[<?php echo $j; ?>]" value="<?php echo $new_data->shipping_id; ?>" class="tab_rate_id">

									<button class='button-primary remove-table-row'><?php _e( 'Remove', 'mp_table_rate' ); ?></button>

								</td>

							</tr>


							<?php

							$j++;

							endforeach;

							?>


							<?php

							endif;

							?>

						</tbody>

					</table>


					<input type="hidden" name="mp_seller" value="<?php echo $seller_id; ?>">

					<button name="submit_shipping" type="submit" value="Save Shipping"><?php _e( 'Save Shipping', 'mp_table_rate' ); ?></button>

				</form>

			</div>

		</div>

	</div>

	<?php

}
