<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
*
*/
class saveShippingFields
{
	public $post_data=array();

	public static function update_table_rate_shipping( $data, $files = array() ) {

		global $wpdb;

		if( isset( $data['submit_shipping'] ) ) {

				$table_name = "{$wpdb->prefix}woocommerce_table_rate_shipping";

				$_new_arr = array();

				if( isset( $data['shipping_id'] ) ) {

					$shipping_id = $data['shipping_id'];
				}
				else{

					$shipping_id = '';
				}

				if( isset( $data['_table_zname'] ) ) {

					$zone_label = $data['_table_zname'];
				}
				else{

					$zone_label = '';
				}

				if( isset( $data['selected_zone'] ) ) {

					$selected_zone = $data['selected_zone'];
				}
				else{

					$selected_zone = '';
				}

				if( isset( $data['select_type'] ) ) {

					$select_type = $data['select_type'];
				}
				else{

					$select_type = '';
				}

				if( isset( $data['_table_min_val'] ) ) {

					$table_min_val = $data['_table_min_val'];
				}
				else{

					$table_min_val = '';
				}

				if( isset( $data['_table_max_val'] ) ) {

					$table_max_val = $data['_table_max_val'];
				}
				else{

					$table_max_val = '';
				}

				if( isset( $data['_ship_price'] ) ) {

					$ship_price = $data['_ship_price'];
				}
				else{

					$ship_price = '';
				}

				if( isset( $data['mp_seller'] ) ) {

					$mp_seller = $data['mp_seller'];

				}
				else{

					$mp_seller = '';
				}


				$j = 0;

					if( empty( $zone_label ) || empty( $mp_seller ) || empty( $ship_price ) || empty( $table_max_val ) || empty( $table_min_val ) || empty( $select_type ) ){

						if( is_admin() ) {
							?>
								<div class='notice notice-error is-dismissible'>

								  <p><?php echo __( 'Please try again entering table rate shipping data.You may have left some fields blank or entered wrong.!', 'mp_table_rate' ); ?></p>

								</div>
							<?php
						}
						else {

							wc_print_notice( __( 'Please try again entering table rate shipping data.You may have left some fields blank or entered wrong.!', 'mp_table_rate' ), 'error' );

						}

					}
					else if( in_array( 'Select Type', $select_type ) ) {

						if( is_admin() ) {
							?>
							<div class='notice notice-error is-dismissible'>

								<p><?php echo __( 'Please select the shipping basis!', 'mp_table_rate' ); ?></p>

							</div>
							<?php
						}
						else {

							wc_print_notice( __( 'Please select the shipping basis!', 'mp_table_rate' ), 'error' );

						}

					}
					else{

						foreach( $zone_label as $d_key => $d_value ) {

							if( !empty( $data['_table_zname'] ) || !empty( $data['selected_zone'] ) || !empty( $data['select_type'] ) || !empty( $data['_table_min_val'] ) || !empty( $data['_table_max_val'] ) || !empty( floatval($data['_ship_price'] ) ) ) {

								if( $ship_price[$d_key] != '' ) {

									if( isset( $shipping_id[$j] ) && !empty( $shipping_id[$j] ) ) {

										if( !empty( $selected_zone[$d_key] ) ) {

											$_new_arr[] = array(
												"shipping_id"	=>	$shipping_id[$j],
												"seller"		=>	$mp_seller,
												"_table_zname"	=>	$zone_label[$d_key],
												"selected_zone"	=>  $selected_zone[$d_key],
												"select_type"	=>	$select_type[$d_key],
												'_table_min_val'=>	$table_min_val[$d_key],
												'_table_max_val'=>	$table_max_val[$d_key],
												'_ship_price'	=>	$ship_price[$d_key]
											);
										}
										else{

											$_new_arr[] = array(
												"shipping_id"	=>	$shipping_id[$j],
												"seller"		=>	$mp_seller,
												"_table_zname"	=>	$zone_label[$d_key],
												"selected_zone"	=>  "",
												"select_type"	=>	$select_type[$d_key],
												'_table_min_val'=>	$table_min_val[$d_key],
												'_table_max_val'=>	$table_max_val[$d_key],
												'_ship_price'	=>	$ship_price[$d_key]
											);
										}
									}
									else{

										if( !empty( $selected_zone[$d_key] ) ) {

											$_new_arr[] = array(
												"seller"		=>	$mp_seller,
												"_table_zname"	=>	$zone_label[$d_key],
												"selected_zone"	=>  $selected_zone[$d_key],
												"select_type"	=>	$select_type[$d_key],
												'_table_min_val'=>	$table_min_val[$d_key],
												'_table_max_val'=>	$table_max_val[$d_key],
												'_ship_price'	=>	$ship_price[$d_key]
											);
										}
										else{

											$_new_arr[] = array(
												"seller"		=>	$mp_seller,
												"_table_zname"	=>	$zone_label[$d_key],
												"selected_zone"	=> 	"",
												"select_type"	=>	$select_type[$d_key],
												'_table_min_val'=>	$table_min_val[$d_key],
												'_table_max_val'=>	$table_max_val[$d_key],
												'_ship_price'	=>	$ship_price[$d_key]
												);
											}

										}

								}
								else {
									if( is_admin() ) {
										?>
										<div class='notice notice-error is-dismissible'>

											<p><?php echo __( 'Please enter the price for shipping!', 'mp_table_rate' ); ?></p>

										</div>
										<?php
									}
									else {

										wc_print_notice( __( 'Please enter the price for shipping!', 'mp_table_rate' ), 'error' );

									}
								}

							}

							$j++;
						}

						foreach ( $_new_arr as $new_seller_ship ) {

							if( isset( $new_seller_ship['shipping_id'] ) && !empty( $new_seller_ship['shipping_id'] ) ) {
								if( is_array( $new_seller_ship['selected_zone'] ) ) {
									$flag = count( $new_seller_ship['selected_zone'] );
								}else{
									$flag = 1;
								}
								if( $flag > 1 && !empty( $new_seller_ship['selected_zone'] ) ) {
									$zones = implode( ",", $new_seller_ship['selected_zone'] );
								}
								else{
									if( $flag == 1 ) {
										if( empty( $new_seller_ship['selected_zone'] ) ) {

											$zones = '';

										}
										else{

											$zones = $new_seller_ship['selected_zone'][0];
										}
									}
									else{

										$zones = $new_seller_ship['selected_zone'];
									}
								}

								$check_for_duplicate_shipping = 0;

								$check_for_duplicate_shipping = $wpdb->get_var( "SELECT count(shipping_id) FROM {$wpdb->prefix}woocommerce_table_rate_shipping WHERE shipping_basis='".$new_seller_ship['select_type']."' AND shipping_zone='$zones' AND seller_id=$mp_seller AND shipping_min='".$new_seller_ship['_table_min_val']."' AND shipping_max='".$new_seller_ship['_table_max_val']."' AND shipping_id!=".$new_seller_ship['shipping_id'] );

								if( empty( $check_for_duplicate_shipping ) ) {

									$wpdb->update(
										$table_name,
										array(
											'seller_id' => $new_seller_ship['seller'] ,
											'shipping_label' => $new_seller_ship['_table_zname'] ,
											'shipping_zone' =>$zones,
											'shipping_basis' =>$new_seller_ship['select_type'],
											'shipping_min' =>$new_seller_ship['_table_min_val'],
											'shipping_max' =>$new_seller_ship['_table_max_val'],
											'shipping_cost' =>$new_seller_ship['_ship_price']
										),
										array( 'shipping_id' => $new_seller_ship['shipping_id'] ),
										array(
											'%d',
											'%s',
											'%s',
											'%s',
											'%s',
											'%s',
											'%s'
										),
										array('%d')
									);

									$shipping_added_info = 1;
									$shipping_added_action = 'updated';

								}
								else {

									if( is_admin() ) {
										?>
										<div class='notice notice-error is-dismissible'>

											<p><?php echo __( 'This shipping range is already present, please add a new one.', 'mp_table_rate' ); ?></p>

										</div>
										<?php
									}
									else {
										wc_print_notice( __( 'This shipping range is already present, please add a new one.', 'mp_table_rate' ), 'error' );
									}

								}

							}
							else{

								if( !empty( $new_seller_ship['selected_zone'] ) && count( $new_seller_ship['selected_zone'] ) > 1 && !empty( $new_seller_ship['selected_zone'] ) ){
									$zones=implode(",",$new_seller_ship['selected_zone']);
								}
								else{
									if( !empty( $new_seller_ship['selected_zone'] ) && count( $new_seller_ship['selected_zone'] ) == 1 ){

										if ( empty( $new_seller_ship['selected_zone'] ) ) {

											$zones = '';

										}
										else{

											$zones = $new_seller_ship['selected_zone'][0];

										}
									}
									else{

										$zones = $new_seller_ship['selected_zone'];

									}
								}

								$sel_id = $new_seller_ship['seller'];
								$ship_label = $new_seller_ship['_table_zname'];
								$ship_type = $new_seller_ship['select_type'];
								$ship_min = $new_seller_ship['_table_min_val'];
								$ship_max = $new_seller_ship['_table_max_val'];

								$check_data = " SELECT * FROM $table_name WHERE seller_id = '$sel_id'  AND shipping_label = '$ship_label' AND shipping_zone = '$zones' AND shipping_basis = '$ship_type' AND shipping_min = '$ship_min' AND shipping_max = '$ship_max'" ;

								$selected_data = $wpdb->get_row( $check_data ) ;

								if( empty( $selected_data ) ) {

									$check_for_duplicate_shipping = 0;

									$check_for_duplicate_shipping = $wpdb->get_var( "SELECT count(shipping_id) FROM {$wpdb->prefix}woocommerce_table_rate_shipping WHERE shipping_basis='".$new_seller_ship['select_type']."' AND shipping_zone='$zones' AND seller_id=$mp_seller AND shipping_min='".$new_seller_ship['_table_min_val']."' AND shipping_max='".$new_seller_ship['_table_max_val']."'" );

									if( empty( $check_for_duplicate_shipping ) ) {

										$wpdb->insert(
											$table_name,
											array(
												'seller_id' => $new_seller_ship['seller'] ,
												'shipping_label' => $new_seller_ship['_table_zname'] ,
												'shipping_zone' =>$zones,
												'shipping_basis' =>$new_seller_ship['select_type'],
												'shipping_min' =>$new_seller_ship['_table_min_val'],
												'shipping_max' =>$new_seller_ship['_table_max_val'],
												'shipping_cost' =>$new_seller_ship['_ship_price']
											)
										);

										$shipping_added_info = 1;
										$shipping_added_action = 'added';

									}
									else {

										if( is_admin() ) {
											?>
											<div class='notice notice-error is-dismissible'>

												<p><?php echo __( 'This shipping range is already present, please add a new one.', 'mp_table_rate' ); ?></p>

											</div>
											<?php
										}
										else {
											wc_print_notice( __( 'This shipping range is already present, please add a new one.', 'mp_table_rate' ), 'error' );
										}

									}

								}
								else{
									if( is_admin() ) {
										?>
										<div class='notice notice-error is-dismissible'>

										  <p><?php echo __( 'Data you have entered is already present.', 'mp_table_rate' ); ?></p>

										</div>
										<?php
									}
									else {
										wc_print_notice( __( 'Data you have entered is already present.', 'mp_table_rate' ), 'error' );
									}
								}

							}

						}

						if( !empty( $shipping_added_info ) && !empty( $shipping_added_action ) ) {

							if( is_admin() ) {
								?>
								<div class='notice notice-success is-dismissible'>

									<p><?php echo __( 'Shipping has been successfully ' . $shipping_added_action . '.', 'mp_table_rate' ); ?></p>

								</div>
								<?php
							}
							else {

								wc_print_notice( __( 'Shipping has been successfully ' . $shipping_added_action . '.', 'mp_table_rate' ), 'success' );

							}

						}

					}

			}

			elseif ( $data['submit_csv'] ) {

				$table_name = "{$wpdb->prefix}woocommerce_table_rate_shipping";

	    	$target_dir = plugins_url()."/uploads/";

	    	$url = wp_upload_dir();

				$target_file = $url['basedir'].'/' . basename( $files['csv_import']['name'] );

				$count = 0;

				$res = '';

				$uploadOk = 1;

	    	$csv_tmpName = $files['csv_import']['tmp_name'];

	    	$csv_name = $files['csv_import']['name'];

	    	$csv_type = $files['csv_import']['type'];

	    	$csv_size = $files['csv_import']['size'];

				if( empty( $files['csv_import']['name'] ) ) {
					if( is_admin() ){
						?>

						<div class='notice notice-error is-dismissible'>

						  <p><?php echo __( 'Choose a file first.', 'mp_table_rate' ); ?></p>

						</div>

						<?php
					}
					else{
						wc_print_notice( __( 'Choose a file first.', 'mp_table_rate' ), 'error' );
					}

				  $uploadOk = 0;

				}
				else {

					// Check file size
					if ( $csv_size > 10000000 ) {
						if( is_admin() ){
							?>

							<div class='notice notice-error is-dismissible'>

								<p><?php echo __( 'Sorry, your file is too large.', 'mp_table_rate' ); ?></p>

							</div>

							<?php
						}
						else{
							wc_print_notice( __( 'Sorry, your file is too large.', 'mp_table_rate' ), 'error' );
						}

						$uploadOk = 0;
					}
					if ( file_exists( $target_file ) ) {
						if( is_admin() ) {
							?>

							<div class='notice notice-error is-dismissible'>

								<p><?php echo __( 'File already exists.', 'mp_table_rate' ); ?></p>

							</div>

							<?php
						}
						else{
							wc_print_notice( __( 'File already exists.', 'mp_table_rate' ), 'error' );
						}

						$uploadOk = 0;

					}

					// Allow certain file formats
					if( $csv_type != "text/csv" ) {
						if( is_admin() ) {
							?>
							<div class='notice notice-error is-dismissible'>

								<p><?php echo __( 'Sorry, only CSV files are allowed.', 'mp_table_rate' ); ?></p>

							</div>

							<?php
						}
						else{
							wc_print_notice( __( 'Sorry, only CSV files are allowed.', 'mp_table_rate' ), 'error' );
						}
						$uploadOk = 0;
					}

					// Check if $uploadOk is set to 0 by an error
					if ( $uploadOk == 0 ) {
						if( is_admin() ) {
							?>

							<div class='notice notice-error is-dismissible'>

								<p><?php echo __( 'Sorry, your file was not uploaded.', 'mp_table_rate' ); ?></p>

							</div>

							<?php
						}
						else{
							wc_print_notice( __( 'Sorry, your file was not uploaded.', 'mp_table_rate' ), 'error' );
						}
						// if everything is ok, try to upload file
					}
					else {

						$seller_id = get_current_user_id();

						if ( is_uploaded_file( $files['csv_import']['tmp_name'] ) ) {

							move_uploaded_file( $files['csv_import']['tmp_name'], $target_file );

							$row = 0;

							if ( ( $handle = fopen( $target_file, "r" ) ) !== FALSE ) {

								while( $data = fgetcsv( $handle, 10000, "," ) ) {

									if( $row !== 0 ) {

										foreach( $data as $final_key ) {

											$new_arr[$row][] = $final_key;

										}
									}

									$row++;
								}
								fclose( $handle );

								if( empty( $new_arr ) ) {

									if( is_admin() ) {
										?>

										<div class='notice notice-error is-dismissible'>

											<p><?php echo __( 'File format not correct.', 'mp_table_rate' ); ?></p>

										</div>

										<?php
									}
									else{
										wc_print_notice( __( 'File format not correct.', 'mp_table_rate' ), 'error' );
									}

								}
								else{

									foreach ( $new_arr as $final_data_arr ) {

										if( is_string( $final_data_arr[0] ) && is_string( $final_data_arr[1] ) && is_string( $final_data_arr[2] ) ) {
											$final_data_arr[1] = strtoupper( $final_data_arr[1] );
											$final_data_arr[3] = (int)$final_data_arr[3];
											$final_data_arr[4] = (int)$final_data_arr[4];
											$final_data_arr[5] = (int)$final_data_arr[5];

											if( in_array( '', $final_data_arr ) ) {

												if( is_admin() ) {
													?>

													<div class='notice notice-error is-dismissible'>

														<p><?php echo __( 'File format not correct.', 'mp_table_rate' ); ?></p>

													</div>

													<?php
												}
												else{
													wc_print_notice( __( 'File format not correct.', 'mp_table_rate' ), 'error' );
												}
												break;
											}

											$selected_data = $wpdb->get_row("SELECT shipping_id FROM $table_name WHERE seller_id ='$seller_id'  AND shipping_label ='$final_data_arr[0]' AND shipping_zone ='$final_data_arr[1]' AND shipping_basis ='$final_data_arr[2]' AND shipping_min ='$final_data_arr[3]' AND shipping_max ='$final_data_arr[4]'");

											if( !empty( $selected_data ) ) {
												$wpdb->update(
												$table_name,
												array(
												'seller_id' => $seller_id ,
												'shipping_label' => $final_data_arr[0],
												'shipping_zone' => $final_data_arr[1],
												'shipping_basis' => $final_data_arr[2],
												'shipping_min' => $final_data_arr[3],
												'shipping_max' => $final_data_arr[4],
												'shipping_cost' => $final_data_arr[5]
												),
												array( 'shipping_id' => $selected_data->shipping_id ),
												array(
												'%d',
												'%s',
												'%s',
												'%s',
												'%d',
												'%d',
												'%d'
												),
												array('%d')
												);
											}
											else{
												$res=$wpdb->insert(
												$table_name,
												array(
												'seller_id' => $seller_id ,
												'shipping_label' => $final_data_arr[0],
												'shipping_zone' => $final_data_arr[1],
												'shipping_basis' => $final_data_arr[2],
												'shipping_min' => $final_data_arr[3],
												'shipping_max' => $final_data_arr[4],
												'shipping_cost' => $final_data_arr[5]
												)
												);

											}

											$count++;

										}

									}
								}
								if( $res ) {

									if( is_admin() ) {
										?>

										<div class='notice notice-success is-dismissible'>

											<p><?php echo $count.__( " rows successfully imported.!", 'mp_table_rate' ); ?></p>

										</div>

										<div class='notice notice-success is-dismissible'>

											<p><?php echo __( 'Table rate shipping data successfully imported.!', 'mp_table_rate' ); ?></p>

										</div>

										<?php
									}
									else{

										wc_print_notice( $count.__( " rows successfully imported.!", 'mp_table_rate' ), 'success' );

										wc_print_notice( __( 'Table rate shipping data successfully imported.!', 'mp_table_rate' ), 'success' );
									}

								}
								else{

									if( is_admin() ) {
										?>

										<div class='notice notice-error is-dismissible'>

											<p><?php echo __( 'Please try again importing table rate shipping data or it is already imported.!', 'mp_table_rate' ); ?></p>

										</div>

										<?php
									}
									else{
										wc_print_notice( __( 'Please try again importing table rate shipping data or it is already imported.!', 'mp_table_rate' ), 'error' );
									}

								}

							}

						} else {

							if( is_admin() ) {
								?>

								<div class='notice notice-error is-dismissible'>

									<p><?php echo __( 'Sorry, there was an error uploading your file.', 'mp_table_rate' ); ?></p>

								</div>

								<?php
							}
							else{
								wc_print_notice( __( 'Sorry, there was an error uploading your file.', 'mp_table_rate' ), 'error' );
							}

						}

					}

				}

			}

	}

}
