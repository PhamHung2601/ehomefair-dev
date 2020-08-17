<?php
if (! defined('ABSPATH') ) {
    exit;
}

/**
 *
 */
class SaveShipingOptions
{

    /**
     * Zone Data
     *
     * @var array
     */
    protected $_mp_data = array(
    'zone_id'        => 0,
    'zone_name'      => '',
    'zone_order'     => 0,
    'zone_locations' => array()
    );

    public $shipping_details=array();

    function __construct($zone_data=0)
    {
        if (! empty($zone_data) && array_key_exists('zone-id', $zone_data) ) {
            $this->mp_read_zone($zone_data);
        } elseif (is_object($zone_data) ) {
            $this->mp_set_zone_id($zone_data->zone_id);
            $this->mp_set_zone_name($zone_data->zone_name);
            $this->mp_read_zone_locations($zone_data->zone_id);
        } elseif (0 === $zone_data ) {
            $this->mp_set_zone_name(__('Zone', 'marketplace'));
        } else {
            $this->mp_set_zone_name(__('Zone', 'marketplace'));
        }

    }

    public function mp_read_zone( $id ) 
    {
        global $wpdb;

        if ($zone_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zones WHERE zone_id = %d LIMIT 1;", $id)) ) {
            $this->mp_set_zone_id($zone_data->zone_id);
            $this->mp_set_zone_name($zone_data->zone_name);
            $this->mp_set_zone_order($zone_data->zone_order);
            $this->mp_read_zone_locations($zone_data->zone_id);
        }
    }

    function saveShippingDetails($data=array())
    {

        global $wpdb;

        $hidden_user=$data['hidden_user'];

        $shop_address=get_user_meta($hidden_user, 'shop_address', true);

        if (isset($_POST['save_shipping_details']) ) {

            $this->mp_create_zone($data);

        } elseif ($_POST['update_shipping_details'] ) {

            $this->mp_update_zone($data);

        }

        WC_Cache_Helper::incr_cache_prefix('shipping_zones');

        // Increments the transient version to invalidate cache.

        WC_Cache_Helper::get_transient_version('shipping', true);

        $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='" . get_option('wkmp_seller_page_title') . "'");

        if(!empty($shop_address)) {
            header('Location:'.site_url().'/'.$page_name.'/'.$shop_address.'/'. get_option('mp_shipping', 'shipping'));
            exit;
        }

    }

    function mp_set_zone_id($set)
    {
        $this->_mp_data['zone_id'] = is_null($set) ? null : absint($set);
    }

    function mp_get_zone_id()
    {
        return is_null($this->_mp_data['zone_id']) ? null : absint($this->_mp_data['zone_id']);
    }


    public function get_formatted_location( $final_data=array() ) 
    {

        $location_parts = array();
        $all_continents = WC()->countries->get_continents();
        $all_countries  = WC()->countries->get_countries();
        $all_states     = WC()->countries->get_states();
        $locations      = $final_data;
        $continents     = array_filter($locations, array( $this, 'location_is_continent' ));
        $countries      = array_filter($locations, array( $this, 'location_is_country' ));
        $states         = array_filter($locations, array( $this, 'location_is_state' ));
        $postcodes      = array_filter($locations, array( $this, 'location_is_postcode' ));

        foreach ( $continents as $location ) {
            $location_parts[] = $all_continents[ $location->code ]['name'];
        }

        foreach ( $countries as $location ) {
            $location_parts[] = $all_countries[ $location->code ];
        }

        foreach ( $states as $location ) {
            $location_codes = explode(':', $location->code);
            $location_parts[] = $all_states[ $location_codes[ 0 ] ][ $location_codes[ 1 ] ];
        }

        foreach ( $postcodes as $location ) {
            $location_parts[] = $location->code;
        }
        // Fix display of encoded characters.
        $location_parts = array_map('html_entity_decode', $location_parts);

        if (! empty($location_parts) ) {
            return implode(', ', $location_parts);
        } else {
            return __('Everywhere', 'marketplace');
        }
    }

    public function get_formatted_code( $final_data=array() ) 
    {

        $location_parts = array();
        $locations      = $final_data;
        $continents     = array_filter($locations, array( $this, 'location_is_continent' ));
        $countries      = array_filter($locations, array( $this, 'location_is_country' ));
        $states         = array_filter($locations, array( $this, 'location_is_state' ));
        $postcodes      = array_filter($locations, array( $this, 'location_is_postcode' ));

        foreach ( $continents as $location ) {
            $location_parts[] = $location->type.":".$location->code ;
        }

        foreach ( $countries as $location ) {
            $location_parts[] = $location->type.":".$location->code;
        }

        foreach ( $states as $location ) {
            $location_parts[] = $location->type.":".$location->code;
        }
        foreach ( $postcodes as $location ) {
            $location_parts[] = $location->type.":".$location->code;
        }
        // Fix display of encoded characters.
        $location_parts = array_map('html_entity_decode', $location_parts);

        if (! empty($location_parts) ) {
            return implode(', ', $location_parts);
        } else {
            return __('Everywhere', 'marketplace');
        }
    }


    /**
     * Location type detection
     *
     * @param  object $location
     * @return boolean
     */
    private function location_is_continent( $location ) 
    {
        return 'continent' === $location->type;
    }

    /**
     * Location type detection
     *
     * @param  object $location
     * @return boolean
     */
    private function location_is_country( $location ) 
    {
        return 'country' === $location->type;
    }

    /**
     * Location type detection
     *
     * @param  object $location
     * @return boolean
     */
    private function location_is_state( $location ) 
    {
        return 'state' === $location->type;
    }

    /**
     * Location type detection
     *
     * @param  object $location
     * @return boolean
     */
    private function location_is_postcode( $location ) 
    {
        return 'postcode' === $location->type;
    }

    /**
     * Insert zone into the database
     */
    public function mp_create_zone($data=array()) 
    {
        global $wpdb;
        $current_user=get_current_user_id();
        $final_data=array();
        if (!empty($data['mp_zone_name'])) {
            $wpdb->insert(
                $wpdb->prefix . 'woocommerce_shipping_zones', array(
                'zone_name'  => strip_tags($data['mp_zone_name']),
                'zone_order' => 1,
                ) 
            );

            $insert_id = $wpdb->insert_id;

            if (! empty($insert_id) ) {

                wc_add_notice(__('"' . $data['mp_zone_name'] . '" Zone Added Successfully.', 'success'));

                $insert_id=intval($insert_id);
                $table_name = $wpdb->prefix . 'mpseller_meta';

                $wpdb->insert(
                    $table_name,
                    array(
                    'seller_id' => intval($current_user),
                    'zone_id'   => $insert_id,
                    )
                );
            }

            if (! empty($data['zone_postcodes']) ) {
                $single_postcodes = array_filter(array_map('strtoupper', array_map('wc_clean', explode("\n", $data['zone_postcodes']))));
                foreach ($single_postcodes as $single_postcode) {
                    $wpdb->insert(
                        $wpdb->prefix . 'woocommerce_shipping_zone_locations', array(
                        'zone_id'       => $insert_id,
                        'location_code' => $single_postcode,
                        'location_type' => "postcode"
                        ) 
                    );
                }
            }
            if(!empty($data['zone_locations'])) {
                $value = $data['zone_locations'];
                foreach ($value as $key_note) {
                    $final_data=explode(":", $key_note);
                    if (isset($final_data[2])) {
                        $location_code_temp = $final_data[1].':'.$final_data[2];
                    }
                    else { $location_code_temp = $final_data[1];
                    }
                    $wpdb->insert(
                        $wpdb->prefix . 'woocommerce_shipping_zone_locations', array(
                        'zone_id'       => $insert_id,
                        'location_code' => $location_code_temp,
                        'location_type' => $final_data[0]
                        ) 
                    );
                }
            }

        }
    }

    function mp_update_zone($data)
    {
        global $wpdb;

        if (!empty($data['mp_zone_id']) ) {
            $wpdb->update(
                $wpdb->prefix . 'woocommerce_shipping_zones', array(
                'zone_name'  => strip_tags($data['mp_zone_name']),
                'zone_order' => 1  ),
                array(
                    'zone_id' => $data['mp_zone_id']
                )
            );
        }
        if (! empty($data['mp_zone_id']) ) {

            wc_add_notice(__('"' . $data['mp_zone_name'] . '" Zone Updated Successfully.', 'success'));

            $wpdb->delete($wpdb->prefix . 'woocommerce_shipping_zone_locations', array( 'zone_id' => $data['mp_zone_id'] ));

            if (isset($data['zone_locations']) && ! empty($data['zone_locations']) ) {
                $value = $data['zone_locations'];

                foreach ( $value as $key_note ) {
                    $final_data = explode(":", $key_note);
                    if (isset($final_data[2]) ) {
                        $location_code_temp_2 = $final_data[1] . ':' . $final_data[2];
                    } else {
                        $location_code_temp_2 = $final_data[1];
                    }
                    $wpdb->insert(
                        $wpdb->prefix . 'woocommerce_shipping_zone_locations', array(
                        'zone_id'       => $data['mp_zone_id'],
                        'location_code' => $location_code_temp_2,
                        'location_type' => $final_data[0],
                        ) 
                    );
                }
            }
        }

    }
    function mp_set_zone_name( $set ) 
    {
        $this->_mp_data['zone_name'] = wc_clean($set);
    }

    function mp_get_zone_name() 
    {
        return $this->_mp_data['zone_name'];
    }

}
