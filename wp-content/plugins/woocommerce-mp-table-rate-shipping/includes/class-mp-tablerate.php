<?php
/**
 * Marketplace Table Rate Shipping Functions
 *
 * @package     Woocommerce Marketplace Table Rate Shipping
 * @copyright   Copyright (c) 2017, Webkul
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	class MP_Tablerate_Extended extends WC_Shipping_Method {

		/**
		 * __construct function.
		 *
		 * @access public
		 * @return void
		 */
		function __construct( $instance_id = 0 ) {

			$this->id           = 'mp_table_rate';
			$this->instance_id  = absint( $instance_id );
			$this->title        = __( 'Marketplace Table Rate', 'woocommerce' );
			$this->method_title = __( 'Marketplace Table Rate', 'woocommerce' );

			$this->enabled = $this->get_option( 'enabled' );

			if( $this->enabled == 'yes' ){
				update_option( 'wk_mp_shipping_plugin', true );
			}
			else{
				delete_option( 'wk_mp_shipping_plugin' );
			}

			$this->method_description  = __( 'Woocommerce Marketplace Table Rate Shipping let you define a standard rate per item, or per order according to customer selected zone.', 'woocommerce' );

			$this->init();

		}

		/**
		 * init function.
		 *
		 * @access public

		 * @return void
		 */
		function init() {

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

			add_filter( 'woocommerce_package_rates', array( $this, 'wc_hide_shipping_when_tableRate_is_available' ), 99, 2 );

		}

		/**
		 * Hide shipping rates when free shipping is available.
		 * Updated to support WooCommerce 2.6 Shipping Zones.
		 *
		 * @param array $rates Array of rates found for the package.
		 * @return array
		 */
		function wc_hide_shipping_when_tableRate_is_available( $rates, $packages ) {

			$tableRate_product = array();
			$admin_seller_both = array();

			foreach ( $rates as $rate_id => $rate ) {

				if ( 'both' === $rate_id ) {

					$admin_seller_both[ $rate_id ] = $rate;

					break;

				}

				if ( 'mp_table_rate' === $rate->method_id ) {

					$tableRate_product[ $rate_id ] = $rate;

					break;

				}

			}

			if( ! empty( $admin_seller_both ) ){

				return array();

			}
			elseif( ! empty( $tableRate_product ) ){

				return $tableRate_product;

			}
			else{

				return $rates;

			}

		}

		/**
		 * Initialise Gateway Settings Form Fields
		 *
		 * @access public
		 * @return void
		 */
		function init_form_fields() {

			global $woocommerce;

			$this->form_fields = array(

				'enabled' => array(
					'title'      => __( 'Enable/Disable', 'woocommerce' ),
					'type'       => 'checkbox',
					'label'      => __( 'Enable this shipping method', 'woocommerce' ),
					'default'    => 'no',
				),
				'title' => array(
					'title'      => __( 'Method Title', 'woocommerce' ),
					'type'       => 'text',
					'description'  => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
					'default'    => __( 'Table Rate', 'woocommerce' ),
					'desc_tip'     => true
				),
				'tax_status' => array(
					'title'     => __( 'Tax Status', 'woocommerce' ),
					'type'      => 'select',
					'default'   => 'taxable',
					'options'   => array(
						'taxable' => __( 'Taxable', 'woocommerce' ),
						'none'    => __( 'None', 'woocommerce' ),
					),
				)
			);
		}

		/**
		 * calculate_shipping function.
		 *
		 * @access public
		 * @param array   $package (default: array())
		 * @return void
		 */

		function calculate_shipping( $package = array() ) {

			if( 'yes' == $this->enabled ) {

				global $wpdb;

				$table_name = "{$wpdb->prefix}woocommerce_table_rate_shipping";

				$c_country = $package['destination']['country'];

				$c_postcode = $package['destination']['postcode'];

				$cost = 0;

				$flag = 0;

				$seller_details = array();

				$shipping_cost_by_seller = array();

				foreach ( $package['contents'] as $item_id => $item_values ) {

					$product_quantity = $item_values['quantity'];

					$product_id = intval( $item_values['product_id'] );

					$product_weight = floatval( $item_values['data']->get_data()['weight'] );

					if( isset( $item_values["assigned-seller-$product_id"] ) ){

						$seller_id = $item_values["assigned-seller-$product_id"];

					}else{

						$seller_id = $this->get_table_rate_seller_details( $product_id );

					}

					if( !array_key_exists( $seller_id, $seller_details ) ) {

						$seller_details[$seller_id] = $product_weight * $product_quantity;

					}
					else {

						$seller_details[$seller_id] += $product_weight * $product_quantity;

					}

				}

				if( !empty( $seller_details ) ) {

					$shipping_available = 1;

					foreach ( $seller_details as $seller_id => $total_product_weight ) {

						$seller_zones = $wpdb->get_results( "SELECT shipping_zone,shipping_basis,shipping_min,shipping_max,shipping_cost FROM $table_name WHERE seller_id=$seller_id ORDER BY shipping_cost ASC" );

						if( !empty( $seller_zones ) ) {

							$global_shipping_cost = 0;
							$global_shipping_available = 0;
							$global_shipping_cost_for_some_regions = 0;
							$global_shipping_cost_for_some_regions_available = 0;
							$global_last_code_for_some_regions = '';

							$flag = 0;

							foreach ( $seller_zones as $seller_key => $seller_value ) {

								$seller_ship_min = $seller_value->shipping_min;

								$seller_ship_max = $seller_value->shipping_max;

								$seller_select_basis = $seller_value->shipping_basis;

								$seller_ship_cost = floatval( $seller_value->shipping_cost );

								$matched_code = $seller_value->shipping_zone;

								if( strpos( $matched_code, "," ) !== false ) {

									// if Matched zone contains  array of countries
									$last_code = explode( ",", $matched_code );

								}
								else{

									// If maatched zone is a single country
									$last_code = $matched_code;

								}

								if( $seller_ship_min == '*' && $seller_ship_max == '*' && $seller_select_basis == 'pro_global' && $last_code == '' ) {

									$global_shipping_cost = $seller_ship_cost;
									$global_shipping_available = 1;

								}
								else if( $seller_ship_min == '*' && $seller_ship_max == '*' && $seller_select_basis == 'pro_global' && $last_code != '' ) {

									$global_shipping_cost_for_some_regions = $seller_ship_cost;
									$global_shipping_cost_for_some_regions_available = 1;
									$global_last_code_for_some_regions = $last_code;

								}

								if( is_array( $last_code ) ) {

									if( in_array( $c_country, $last_code ) ) {

										if( $seller_select_basis == 'pro_weight' ) {

											// Select basis Weight
											if( ( $total_product_weight >= $seller_ship_min ) && ( $total_product_weight <= $seller_ship_max ) ) {

												// Total Product Weight lies between Min and Max Range Set
												if( $flag == 0 ) {

													$shipping_cost_by_seller[ $seller_id ] = $seller_ship_cost;
													$cost += $seller_ship_cost;
													$flag = 1;
													continue;

												}

											}

										}
										else{

											// Select basis Pincode
											if( !empty( $c_postcode ) ) {

												if( ( $c_postcode >= $seller_ship_min ) && ( $c_postcode <= $seller_ship_max ) ){

													if( $flag == 0 ) {

														$shipping_cost_by_seller[ $seller_id ] = $seller_ship_cost;
														$cost += $seller_ship_cost;
														$flag = 1;
														continue;

													}

												}

											}

										}

									}

								}
								else if( $last_code == $c_country ) {

									if( $seller_select_basis == 'pro_weight' ) {

										// Select basis Weight
										if( ( $total_product_weight >= $seller_ship_min ) && ( $total_product_weight <= $seller_ship_max ) ) {

											// Total Product Weight lies between Min and Max Range Set
											if( $flag == 0 ) {

												$shipping_cost_by_seller[ $seller_id ] = $seller_ship_cost;
												$cost += $seller_ship_cost;
												$flag = 1;
												continue;

											}

										}

									}
									else{

										// Select basis Pincode
										if( !empty( $c_postcode ) ) {

											if( ( $c_postcode >= $seller_ship_min ) && ( $c_postcode <= $seller_ship_max ) ){

												if( $flag == 0 ) {

													$shipping_cost_by_seller[ $seller_id ] = $seller_ship_cost;
													$cost += $seller_ship_cost;
													$flag = 1;
													continue;

												}

											}

										}

									}

								}

							}

							if( $flag == 0 ) {

								if( !empty( $global_shipping_cost_for_some_regions_available ) ) {

									if( !empty( $global_last_code_for_some_regions ) ) {

										if( is_array( $global_last_code_for_some_regions ) ) {

											if( in_array( $c_country, $global_last_code_for_some_regions ) ) {

												$shipping_cost_by_seller[ $seller_id ] = $global_shipping_cost_for_some_regions;
												$cost += $global_shipping_cost_for_some_regions;
												$flag = 1;

											}

										}
										else if( $global_last_code_for_some_regions == $c_country ) {

											$shipping_cost_by_seller[ $seller_id ] = $global_shipping_cost_for_some_regions;
											$cost += $global_shipping_cost_for_some_regions;
											$flag = 1;

										}

									}

								}

								if( !empty( $global_shipping_available ) && $flag == 0 ) {

									$shipping_cost_by_seller[ $seller_id ] = $global_shipping_cost;
									$cost += $global_shipping_cost;
									$flag = 1;

								}
								else if( $flag == 0 ) {

									$shipping_cost_by_seller[ $seller_id ] = 0;
									$shipping_available = 0;

								}

							}

							if ( ! empty( WC()->session->get( 'shipping_sess_cost' ) ) ) {

								$ses_obj = WC()->session->get( 'shipping_sess_cost' );

							} else {

								$ses_obj = array();

							}

							$ses_obj[ $seller_id ] = array(
								'cost' => $shipping_cost_by_seller[ $seller_id ],
								'title' => $this->id,
							);

							WC()->session->set( 'shipping_sess_cost', $ses_obj );

						}

					}

				}

				if( $shipping_available == 0 ) {

					if( ! empty( $seller_details ) && count( $seller_details ) != 0 ) {

						$this->add_rate( array(
						'id' => 'both',
						'label' => 'No Shipping Avaliable',
						'cost' => ''
						) );

					}

				}
				else {

					// send the final rate to the user.
					$rate = array(
					'id' => $this->id,
					'label' => $this->title,
					'cost' => $cost
					);

					$this->add_rate( $rate );

				}

			}

		}

		function get_table_rate_seller_details( $pro_id ) {

  		global $wpdb;

  		$table = $wpdb->prefix . 'posts';

		  $pro_author = $wpdb->get_var( "SELECT $table.post_author FROM $table WHERE $table.ID =".$pro_id );

			return $pro_author;

		}

	}
