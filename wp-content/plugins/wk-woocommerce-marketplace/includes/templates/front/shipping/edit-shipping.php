<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$user_id = get_current_user_id();

$mainpage = get_query_var( 'main_page' );

$zone_id = get_query_var( 'zone_id' );

try {
	$zones = new WC_Shipping_Zone( $zone_id );

	$shop_address = get_user_meta( $user_id, 'shop_address', true );

	$table_name_check = $wpdb->prefix . 'mpseller_meta';

	$seller_zone_check = $wpdb->get_results( "SELECT * FROM $table_name_check where seller_id=" . $user_id . ' and zone_id=' . $zone_id );

	$zone_name = $zones->get_zone_name();

	$zone_locations = $zones->get_zone_locations();

	$final_obj = new SaveShipingOptions();

	?> <div class="woocommerce-account"> <?php

	do_action( 'mp_get_wc_account_menu', 'marketplace' );

	?>

	<div class="woocommerce-MyAccount-content">

	<?php

	if ( $mainpage === $shop_address && ! empty( $seller_zone_check ) ) :

	?>

		<form action="" method="post">

			<?php wp_nonce_field( 'shipping_action', 'shipping_nonce' ); ?>

			<table class="wc-shipping-zones widefat">
				<thead>

					<tr>

					</tr>

				</thead>

				<tfoot>

					<tr>

						<td colspan="4">

							<input type="submit" name="update_shipping_details" class="button button-primary wc-shipping-zone-update" value="<?php esc_attr_e( 'Update changes', 'woocommerce' ); ?>" />

						</td>

					</tr>

				</tfoot>

				<tbody class="wc-shipping-zone-rows ui-sortable">
				<?php
				$ship_locations  = $final_obj->get_formatted_location( $zone_locations );
				$ship_locations  = explode( ',', $ship_locations );
				$ship_code_array = $final_obj->get_formatted_code( $zone_locations );
				$ship_code_array = explode( ',', $ship_code_array );
				?>

					<tr class="final-editing">

						<td><label for="mp_zone_name"><?php echo esc_html_e( 'Zone Name', 'marketplace' ); ?><span class="required">*</span>&nbsp;&nbsp;:</label></td>

						<td class="wc-shipping-zone-name">

							<input type="text" name="mp_zone_name" value="<?php echo html_entity_decode( stripslashes( $zone_name ) );?>" data-attribute="zone_name"  placeholder="<?php echo esc_html( 'Zone Name', 'marketplace' ); ?>">

						</td>

					</tr>

					<tr>

						<td class="wc-shipping-zone-region"><label for="mp_zone_region"><?php echo esc_html_e( 'Zone Region', 'marketplace' );?><span class="required">*</span>&nbsp;&nbsp;:</label></td>

						<input type="hidden" name="hidden_user" value="<?php echo $user_id; ?>">

						<td class="wc-shipping-zone-region">
						<div class="edit">
							<div class="mp_shipping_country">
								<?php
								$i = 0;
								$zone = WC_Shipping_Zones::get_zone( absint( $zone_id ) );

								if ( ! $zone ) {
										wp_die( esc_html__( 'Zone does not exist!', 'marketplace' ) );
								}

								$allowed_countries = WC()->countries->get_allowed_countries();
								$wc_shipping       = WC_Shipping ::instance();
								$shipping_methods  = $wc_shipping->get_shipping_methods();
								$continents        = WC()->countries->get_continents();

								// Prepare locations.
								$locations = array();
								$postcodes = array();

								foreach ( $zone->get_zone_locations() as $location ) {
									if ( 'postcode' === $location->type ) {
										$postcodes[] = $location->code;
									} else {
										$locations[] = $location->type . ':' . $location->code;
									}
								}
									?>
										<input type="hidden" value="<?php echo $zone_id; ?>" name="mp_zone_id" />
										<select multiple="multiple" data-attribute="zone_locations" id="new_zone_locations" name="zone_locations[]" data-placeholder="<?php esc_html_e( 'Select regions within this zone', 'marketplace' ); ?>" class="wc-shipping-zone-region-select chosen_select">
											<?php
											foreach ( $continents as $continent_code => $continent ) {
												echo '<option value="continent:' . esc_attr( $continent_code ) . '" ' . selected( in_array( "continent:$continent_code", $locations ), true, false ) . ' alt="">' . esc_html( $continent['name'] ) . '</option>';

												$countries = array_intersect( array_keys( $allowed_countries ), $continent['countries'] );

												foreach ( $countries as $country_code ) {
													echo '<option value="country:' . esc_attr( $country_code ) . '" ' . selected( in_array( "country:$country_code", $locations ), true, false ) . ' alt="' . esc_attr( $continent['name'] ) . '">' . esc_html( '&nbsp;&nbsp; ' . $allowed_countries[ $country_code ] ) . '</option>';

													if ( $states = WC()->countries->get_states( $country_code ) ) {
														foreach ( $states as $state_code => $state_name ) {
															echo '<option value="state:' . esc_attr( $country_code . ':' . $state_code ) . '" ' . selected( in_array( "state:$country_code:$state_code", $locations ), true, false ) . ' alt="' . esc_attr( $continent['name'] . ' ' . $allowed_countries[ $country_code ] ) . '">' . esc_html( '&nbsp;&nbsp;&nbsp;&nbsp; ' . $state_name ) . '</option>';
														}
													}
												}
											}
										?>
										</select>
								</div>
								<a class="wc-shipping-zone-postcodes-toggle" href="#"><?php echo esc_html__( 'Limit to specific ZIP/postcodes', 'marketplace' ); ?></a>
								<div class="wc-shipping-zone-postcodes">
									<textarea name="zone_postcodes" placeholder="List 1 postcode per line" class="input-text large-text" cols="25" rows="5"></textarea>
									<span class="description"><?php echo esc_html__( 'Postcodes containing wildcards (e.g. CB23*) and fully numeric ranges (e.g. <code>90210...99000</code>) are also supported.', 'marketplace' ); ?></span>
								</div>
								</div>

						</td>
					</tr>

					<tr>

						<td><label for="mp_zone_shipping"><?php echo esc_html_e( 'Shipping Method', 'marketplace' ); ?>&nbsp;&nbsp;:</label></td>

						<td class="wc-shipping-zone-methods shipping-extended">

							<div>

								<ul>
									<?php

									$methods = $zones->get_shipping_methods();

									if ( ! empty( $methods ) ) {

										foreach ( $methods as $method ) {

											$settings_html = $method->generate_settings_html( $method->get_instance_form_fields(), false );

											$ship_slug = $method->get_rate_id();

												$ship_slug = explode( ':', $ship_slug );
												?>

												<div id="modal-ship-rate<?php echo $ship_slug[1]; ?>" style="display:none">

													<div class="shipping-method-add-cost">

															<table class="form-table">

																<?php echo $settings_html; ?>

															</table>

														<input type="hidden" name="instance_id" value="<?php echo $method->instance_id; ?>">

														<button class='button button-primary btn-save-cost' ><?php echo esc_html__( 'Save Changes', 'marketplace' ); ?></button>

													</div>

												</div>

											<?php
											$class_name = 'yes' === $method->enabled ? 'method_enabled' : 'method_disabled';
												echo '<li class="wc-shipping-zone-method outer-ship-method">
													<span data-methid="' . $zone_id . '-' . $method->instance_id . '" class="del-ship-method"></span>
													<a href="#TB_inline?width=800&height=500&inlineId=modal-ship-rate' . $ship_slug[1] . '" class="' . esc_attr( $class_name ) . ' thickbox" title="' . esc_html( $method->get_title() ) . ' Setting">' . esc_html( $method->get_title() ) . '
													</a>
												  </li>';
										}
									} else {
											echo '<p>' . esc_html__( 'No shipping methods offered to this zone.', 'marketplace' ) . '</p>';
									}

									add_thickbox();

							?>

									<li class="wc-shipping-zone-methods-add-row"></li>
								</ul>

								<a href="#TB_inline?width=600&height=280&inlineId=modal-window-id" class="thickbox add_shipping_method tips button" title="<?php esc_attr_e( 'Add Shipping Method', 'marketplace' ); ?>" data-tip="<?php esc_attr_e( 'Add shipping method', 'marketplace' ); ?>" data-disabled-tip="<?php esc_attr_e( 'Save changes to continue adding shipping methods to this zone', 'marketplace' ); ?>"><?php esc_html_e( 'Add shipping method', 'marketplace' ); ?></a>

								<div id="modal-window-id" style="display:none">

									<div class="shipping-method-modal">
										<br />
										<p><?php echo esc_html__( 'Choose the shipping method you wish to add. Only shipping methods which support zones are listed.', 'marketplace' ); ?></p>


											<select name="add_method_id" id="add_method_id" data-get-zone="<?php echo $zone_id; ?>">
											<?php
											global $woocommerce;

											$shipping_methods = $woocommerce->shipping->load_shipping_methods();
											
											foreach ( $shipping_methods as $key => $value ) {

												$show_shipping_in_options = apply_filters( 'wk_mp_show_seller_available_shipping', true, $value );

												if ( $show_shipping_in_options && 'flat_rate' !== $value->id ) {
													echo "<option value='$value->id'>" . $value->method_title . '</option>';
												}

											}
												?>
											</select>

										<br />
										<br />
										<p><strong><?php echo esc_html__( 'Lets you charge a fixed rate for shipping.', 'marketplace' ); ?></strong></p>

										<button class='button button-primary add-ship-method'><?php echo esc_html__( 'Add Shipping Method', 'marketplace' ); ?></button>

									</div>

								</div>

							</div>

						</td>

					</tr>

				</tbody>

			</table>

		</form>

		<?php else : ?>

			<h1><?php echo esc_html__( 'Cheating huh ???', 'marketplace' ); ?></h1>
			<p><?php echo esc_html__( "Sorry, You can't edit other seller's shipping zone.", 'marketplace' ); ?></p>

		<?php
	endif;
	?>
</div>
</div>
<?php

} catch ( Exception $e ) {
	wc_print_notice( $e->getMessage(), 'error' );
}
