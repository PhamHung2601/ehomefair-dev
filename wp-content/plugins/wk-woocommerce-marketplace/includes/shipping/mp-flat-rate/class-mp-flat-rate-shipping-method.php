<?php
/**
 * Class MP_FLAT_RATE_SHIPPING_METHOD file.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('MP_FLAT_RATE_SHIPPING_METHOD')) {
    /**
     * Marketplace flat rate shiping class.
     */
    class MP_FLAT_RATE_SHIPPING_METHOD extends WC_Shipping_Method
    {
        /**
         * Function constructor.
         *
         * @param int $instance_id instance id
         */
        public function __construct($instance_id = 0)
        {
            $this->id = 'mp_flat_rate';
            $this->instance_id = absint($instance_id);
            $this->method_title = __('Marketplace Flat Rate Shipping', 'woocommerce');
            $this->method_description = __('Custom Flat Rate Shipping Method for WooCommerce Marketplace Plugin', 'woocommerce');

            // Load the settings.
            $this->availability = 'including';
            $this->init_form_fields();
            $this->instance_form_fields = include 'includes/settings-mp-flat-rate.php';
            $this->init_settings();

            $this->supports = array(
                'shipping-zones',
                'instance-settings',
                'instance-settings-modal',
            );

            // Define user set variables.
            $this->enabled = $this->get_option('enabled');
            $this->title = $this->get_option('title');

            add_filter('woocommerce_package_rates', array($this, 'wc_mp_flat_rate_handler'), 10, 2);
        }

        /**
         * Function to iterate through all packages.
         *
         * @param array $rates   rates array
         * @param array $package package rates
         */
        public function wc_mp_flat_rate_handler($rates, $package)
        {
            $seller_ids = array();
            $matching_zone_ids = array();
            $ids_supported_methods = array();
            $allowed_shipping_methods = array();

            foreach ($package['contents'] as $item_id => $values) {
                $product_id = $values['product_id'];

                if (isset($values["assigned-seller-$product_id"])) {
                    $seller_id = $values["assigned-seller-$product_id"];
                } else {
                    $seller_id = get_post_field('post_author', $product_id);
                }

                if (!in_array($seller_id, $seller_ids, true)) {
                    $seller_ids[] = $seller_id;
                }
            }

            $package['rates'] = array();
            $country = strtoupper(wc_clean($package['destination']['country']));
            $state = strtoupper(wc_clean($package['destination']['state']));
            $postcode = wc_normalize_postcode(wc_clean($package['destination']['postcode']));
            $cache_key = WC_Cache_Helper::get_cache_prefix('shipping_zones').'wc_shipping_zone_'.md5(sprintf('%s+%s+%s', $country, $state, $postcode));
            wp_cache_delete($cache_key, 'shipping_zones');

            $matching_zone_id = wp_cache_get($cache_key, 'shipping_zones');

            if (1 === count($seller_ids)) {
                if (false === $matching_zone_id) {
                    $matching_zone_id = $this->get_zone_id_from_package($package, $seller_ids[0]);
                    wp_cache_set($cache_key, $matching_zone_id, 'shipping_zones');
                }
            } else {
                foreach ($seller_ids as $s_key => $s_value) {
                    $user_meta = get_userdata($s_value);
                    $user_roles = $user_meta->roles;
                    if (in_array('wk_marketplace_seller', $user_roles, true)) {
                        $matching_zone_id = $this->get_zone_id_from_package($package, $s_value);
                        if (null !== $matching_zone_id) {
                            wp_cache_set($cache_key, $matching_zone_id, 'shipping_zones');
                            $matching_zone_ids[] = $matching_zone_id;
                        }
                    }
                }
            }

            if (!empty($matching_zone_ids) && count($matching_zone_ids) > 1) {
                foreach ($matching_zone_ids as $mz_key => $mz_value) {
                    $ids_zone = new WC_Shipping_Zone($mz_value ? $mz_value : 0);
                    $ids_shipping_methods = $ids_zone->get_shipping_methods(true);
                    foreach ($ids_shipping_methods as $ids_key => $ids_value) {
                        $ids_supported_methods[$mz_value][] = $ids_value->id;
                    }
                }
                if (count($ids_supported_methods) > 1) {
                    $allowed_shipping_methods = call_user_func_array('array_intersect', $ids_supported_methods);
                } else {
                    $allowed_shipping_methods = reset($ids_supported_methods);
                }
            }

            $zone = new WC_Shipping_Zone($matching_zone_id ? $matching_zone_id : 0);
            $shipping_methods = $zone->get_shipping_methods(true);

            foreach ($shipping_methods as $shipping_method) {
                if (!empty($allowed_shipping_methods) && !in_array($shipping_method->id, $allowed_shipping_methods, true)) {
                    continue;
                } elseif ((!$shipping_method->supports('shipping-zones') || $shipping_method->get_instance_id())) {
                    $package['rates'] = $package['rates'] + $shipping_method->get_rates_for_package($package); // + instead of array_merge maintains numeric keys
                }
            }

            return $package['rates'];
        }

        /**
         * Function for getting zone id.
         *
         * @param array $package   package array
         * @param int   $seller_id seller id
         */
        public function get_zone_id_from_package($package, $seller_id = '')
        {
            global $wpdb;
            $country = strtoupper(wc_clean($package['destination']['country']));
            $state = strtoupper(wc_clean($package['destination']['state']));
            $continent = strtoupper(wc_clean(WC()->countries->get_continent_code_for_country($country)));
            $postcode = wc_normalize_postcode(wc_clean($package['destination']['postcode']));

            // Work out criteria for our zone search.
            $criteria = array();
            $criteria[] = $wpdb->prepare("( ( location_type = 'country' AND location_code = %s )", $country);
            $criteria[] = $wpdb->prepare("OR ( location_type = 'state' AND location_code = %s )", $country.':'.$state);
            $criteria[] = $wpdb->prepare("OR ( location_type = 'continent' AND location_code = %s )", $continent);
            $criteria[] = 'OR ( location_type IS NULL ) )';

            // Postcode range and wildcard matching.
            $postcode_locations = $wpdb->get_results("SELECT zone_id, location_code FROM {$wpdb->prefix}woocommerce_shipping_zone_locations WHERE location_type = 'postcode';");

            if ($postcode_locations) {
                $zone_ids_with_postcode_rules = array_map('absint', wp_list_pluck($postcode_locations, 'zone_id'));
                $matches = wc_postcode_location_matcher($postcode, $postcode_locations, 'zone_id', 'location_code', $country);
                $do_not_match = array_unique(array_diff($zone_ids_with_postcode_rules, array_keys($matches)));

                if (!empty($do_not_match)) {
                    $criteria[] = 'AND zones.zone_id NOT IN ('.implode(',', $do_not_match).')';
                }
            }

            // Get matching zones.
            return $wpdb->get_var("
          SELECT zones.zone_id FROM {$wpdb->prefix}woocommerce_shipping_zones as zones
          LEFT OUTER JOIN {$wpdb->prefix}woocommerce_shipping_zone_locations as locations ON zones.zone_id = locations.zone_id AND location_type != 'postcode' JOIN {$wpdb->prefix}mpseller_meta as my_zones on zones.zone_id = my_zones.zone_id and my_zones.seller_id= '$seller_id'
          WHERE ".implode(' ', $criteria).' ORDER BY zone_order ASC LIMIT 1 '
            );
        }

        /**
         * Marketplace Flat Rate Form Fields goes here.
         */
        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable WooCommerce Marketplace Flat Rate Shipping', 'woocommerce'),
                    'default' => 'yes',
                ),
                'title' => array(
                    'title' => __('Marketplace Flat Rate Shipping', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                    'default' => __('Marketplace Flat Rate Shipping', 'woocommerce'),
                ),
            );
        }

        /**
         * Calculate shipping function.
         *
         * @param array $package packages array
         */
        public function calculate_shipping($package = array())
        {
            global $wpdb;

            $counter = '';

            $c_country = $package['destination']['country'];

            $c_state = $package['destination']['state'];

            $c_postcode = $package['destination']['postcode'];

            $cost = 0;

            $ids = array();

            if ('yes' === $this->enabled) {
                $table_name = $wpdb->prefix.'mpseller_meta';

                foreach ($package['contents'] as $item_id => $values) {
                    $product_id = $values['product_id'];

                    if (isset($values["assigned-seller-$product_id"])) {
                        $seller_details = $values["assigned-seller-$product_id"];
                    } else {
                        $seller_details = get_post_field('post_author', $product_id);
                    }

                    // $seller_details = apply_filters( 'mp_spc_override_vendor_id', $product_id, $seller_details );

                    if (!empty($seller_details)) {
                        $seller_zones = $this->get_zone_id_from_package($package, $seller_details);
                        $method = false;
                        $zone = new WC_Shipping_Zone($seller_zones ? $seller_zones : 0);
                        $shipping_methods = $zone->get_shipping_methods(true);
                        foreach ($shipping_methods as $sm_key => $sm_value) {
                            if ('mp_flat_rate' === $sm_value->id) {
                                $method = true;
                            }
                        }
                    }
                    if (empty($seller_zones) || false === $method) {
                        $cost = 0;
                        break;
                    } else {
                        if (!empty($seller_zones)) {
                            if (isset($seller_zones) && !empty($seller_zones)) {
                                $zone_locations = array();

                                $zones = new WC_Shipping_Zone($seller_zones);

                                $methods = $zones->get_shipping_methods();

                                if (!empty($methods) && isset($methods)) {
                                    foreach ($methods as $in_key => $in_value) {
                                        if ('mp_flat_rate' === $in_value->id && !in_array($seller_details, $ids, true)) {
                                            if (isset($in_value->instance_settings['cost'])) {
                                                // $cost += $in_value->instance_settings['cost'];
                                                $cost += $this->evaluate_cost(
                                                    $in_value->instance_settings['cost'], array(
                                                        'qty' => $this->get_package_item_qty($package, $seller_details),
                                                        'cost' => $this->get_cart_content_total($package, $seller_details),
                                                    )
                                                );

                                                if (!empty(WC()->session->get('shipping_sess_cost'))) {
                                                    $ses_obj = WC()->session->get('shipping_sess_cost');
                                                } else {
                                                    $ses_obj = array();
                                                }

                                                $ses_obj[$seller_details] = array(
                                                    'cost' => $in_value->instance_settings['cost'],
                                                    'title' => $in_value->id,
                                                );

                                                WC()->session->set('shipping_sess_cost', $ses_obj);
                                            }
                                        }
                                    }
                                } else {
                                    $cost = 0;
                                }
                            } else {
                                $cost = 0;
                            }
                        } else {
                            $cost = 0;
                        }
                    }

                    $ids[] = $seller_details;
                }

                if ($cost > 0) {
                    // Send the final rate to the user.
                    $rate = array(
                        'id' => $this->id,
                        'label' => $this->title,
                        'cost' => $cost,
                    );

                    $this->add_rate($rate);
                }
            }
        }

        /**
         * Get items in package.
         *
         * @param array $package   package information
         * @param array $seller_id seller id
         *
         * @return int
         */
        public function get_package_item_qty($package, $seller_id)
        {
            $total_quantity = 0;
            foreach ($package['contents'] as $item_id => $values) {
                $product_id = $values['product_id'];

                if (isset($values["assigned-seller-$product_id"])) {
                    $item_seller_id = $values["assigned-seller-$product_id"];
                } else {
                    $item_seller_id = get_post_field('post_author', $product_id);
                }

                if ($seller_id === $item_seller_id && $values['quantity'] > 0 && $values['data']->needs_shipping()) {
                    $total_quantity += $values['quantity'];
                }
            }

            return $total_quantity;
        }

        /**
         * Get items in package.
         *
         * @param array $package   package information
         * @param array $seller_id seller id
         *
         * @return int
         */
        public function get_cart_content_total($package, $seller_id)
        {
            $total_cost = 0;
            foreach ($package['contents'] as $item_id => $values) {
                $product_id = $values['product_id'];

                if (isset($values["assigned-seller-$product_id"])) {
                    $item_seller_id = $values["assigned-seller-$product_id"];
                } else {
                    $item_seller_id = get_post_field('post_author', $product_id);
                }

                if ($seller_id === $item_seller_id && $values['line_total'] > 0 && $values['data']->needs_shipping()) {
                    $total_cost += $values['line_total'];
                }
            }

            return $total_cost;
        }

        /**
         * Work out fee (shortcode).
         *
         * @param array $atts shortcode attributes
         *
         * @return string
         */
        public function fee($atts)
        {
            $atts = shortcode_atts(
                array(
                    'percent' => '',
                    'min_fee' => '',
                ), $atts, 'fee'
            );

            $calculated_fee = 0;

            if ($atts['percent']) {
                $calculated_fee = $this->fee_cost * (floatval($atts['percent']) / 100);
            }

            if ($atts['min_fee'] && $calculated_fee < $atts['min_fee']) {
                $calculated_fee = $atts['min_fee'];
            }

            return $calculated_fee;
        }

        /**
         * Evaluate a cost from a sum/string.
         *
         * @param string $sum  sum to evaluate
         * @param array  $args arguments
         *
         * @return string
         */
        protected function evaluate_cost($sum, $args = array())
        {
            include_once WC()->plugin_path().'/includes/libraries/class-wc-eval-math.php';

            $locale = localeconv();
            $decimals = array(wc_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point']);

            $this->fee_cost = $args['cost'];
            // Expand shortcodes.
            add_shortcode('fee', array($this, 'fee'));

            $sum = do_shortcode(
                str_replace(
                    array(
                        '[qty]',
                        '[cost]',
                    ),
                    array(
                        $args['qty'],
                        $args['cost'],
                    ),
                    $sum
                )
            );

            remove_shortcode('fee', array($this, 'fee'));

            // Remove whitespace from string.
            $sum = preg_replace('/\s+/', '', $sum);

            // Remove locale from string.
            $sum = str_replace($decimals, '.', $sum);

            // Trim invalid start/end characters.
            $sum = rtrim(ltrim($sum, "\t\n\r\0\x0B+*/"), "\t\n\r\0\x0B+-*/");

            // Do the math.
            return $sum ? WC_Eval_Math::evaluate($sum) : 0;
        }
    }
}
