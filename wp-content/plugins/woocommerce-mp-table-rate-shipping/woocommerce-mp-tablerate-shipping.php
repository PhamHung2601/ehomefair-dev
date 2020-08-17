<?php
/**
 * Plugin Name: WooCommerce Marketplace Table Rate Shipping
 * Plugin URI: http://webkul.com
 * Description: Plugin to add dynamic shipping cost to cart product depending updon customer destination
 * Version: 1.8
 * Author: Webkul
 * Author URI: http://webkul.com
 * Domain Path: plugins/woocommerce-mp-table-rate-shipping
 * Network: true
 * License: GNU/GPL for more info see license.txt included with plugin
 * License URI: http://www.gnu.org/licenseses/gpl-2.0.html
 * Text domain: mp_table_rate
 *
 *
 * @package         Woocommerce-Marketplace-Table-Rate-Shipping
 * @author          Webkul
 * @copyright       Copyright (c) 2017
 *
 */

register_activation_hook( __FILE__,'install' );

  /**
     * Installer
     */
    function install() {

        include_once( 'installer.php' );
    }

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'MP_TABLE_RATE_SHIPPING' ) ) {

    /**
     * Main MP_TABLE_RATE_SHIPPING class
     *
     * @since       1.0.0
     */
    class MP_TABLE_RATE_SHIPPING {

        /**
         * @var         MP_TABLE_RATE_SHIPPING $instance The one true MP_TABLE_RATE_SHIPPING
         * @since       1.0.0
         */
        private static $instance;

        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true MP_TABLE_RATE_SHIPPING
         */
        public static function instance() {

            if( !self::$instance ) {

                self::$instance = new MP_TABLE_RATE_SHIPPING();
                self::$instance->mp_setup_constant();
                self::$instance->includes();

            }

            return self::$instance;
        }

        function __construct() {

            add_action( 'wp_head', array( $this, 'add_new_calling_pages') );

            add_action( 'mp_woocommerce_account_menu_options', array( $this, 'add_moreseller_tab_list'), 10, 1 );

            add_action( 'woocommerce_shipping_init', array( $this, 'mp_table_rate_shipping_method_init' ) );

            add_filter( 'woocommerce_shipping_methods', array( $this, 'add_table_rate_mp' ) );

            add_action( 'wp_ajax_wpr_tr_add_row', array( 'WPR_TR_Ajax', 'add_row' ) );

            $this->frontEndIncludes();

            // add_filter( 'shipping_plugin_enabled', 'my_custom_homepage_freq', 10, 1 );

            add_action( 'admin_menu', array( $this, 'wk_mp_table_rate_add_admin_menu_for_shipping' ) );

            add_filter( 'woocommerce_cart_shipping_method_full_label', array( $this, 'wk_mp_table_rate_change_shipping_package_name' ), 10, 2 );

        }

        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
            // Include scripts
            require_once MP_SHIPPING_DIR . 'includes/include-scripts.php';
            require_once MP_SHIPPING_DIR . 'includes/functions.php';

        }

        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         *
         */

        function mp_table_rate_shipping_method_init() {

          require_once MP_SHIPPING_DIR . 'includes/class-mp-tablerate.php';

         }

        function frontEndIncludes(){

          require_once('includes/save-table-rate-shipping.php');

        }

        public function wk_mp_table_rate_change_shipping_package_name( $label, $method ) {


          if ( 'mp_table_rate' === $method->method_id && $method->cost == 0 ) {
        		$label = __( 'Free Shipping', 'mp_table_rate' );
        	}

        	return $label;

        }

        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function mp_setup_constant() {

            define( 'MP_SHIPPING_VER', '1.0.0' );  // Plugin version

            define( 'MP_SHIPPING_DIR', plugin_dir_path( __FILE__ ) );  // Plugin path

            define( 'MP_SHIPPING_URL', plugin_dir_url( __FILE__ ) );  // Plugin URL

        }

        function add_moreseller_tab_list($tab){

            $table_rate_shipping_settings_array = get_option('woocommerce_mp_table_rate_settings');

            if( !empty( $table_rate_shipping_settings_array ) && $table_rate_shipping_settings_array['enabled'] == 'yes'){

              global $wpdb;

              $user_id = get_current_user_id();

              $new_tab = array();

              $page_name = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name ='".get_option('wkmp_seller_page_title')."'");

              $new_tab['../' . $page_name . '/table-rate'] = __( 'Table Rate Shipping', 'woocommerce' );

              $tab += $new_tab;

            }

            return $tab;

        }

        public function wk_mp_table_rate_add_admin_menu_for_shipping() {

          add_menu_page( __( 'Table Rate Shipping', 'mp_table_rate' ), __( 'Table Rate Shipping', 'mp_table_rate' ), 'manage_options', 'mp-table-rate-shipping', array( $this, 'wk_mp_table_rate_menu_contents' ), 'dashicons-portfolio', 55 );

        }

        public function wk_mp_table_rate_menu_contents() {

          global $woocommerce;

        	global $wpdb;

        	$seller_id = get_current_user_id();

          $table_rate = $wpdb->prefix . "woocommerce_table_rate_shipping";

          // Submiting post data
          if( isset( $_POST['submit_csv'] ) ) {

          	if ( ! empty( $_POST['shipping_table_rate_nonce'] ) && isset( $_POST['shipping_table_rate_nonce'] ) ) {

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

        		if ( ! empty( $_POST['shipping_table_rate_nonce'] ) && isset( $_POST['shipping_table_rate_nonce'] ) ) {

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

          $query = $wpdb->prepare( "SELECT * FROM $table_rate WHERE seller_id = %d", $seller_id );

          $table_rate_shipping_data = $wpdb->get_results( $query );

        	echo "<h2>Marketplace Table Rate Shipping</h2>";

      	  ?>

        	<div class="table_rate_shipping_container">

        	<h3>Upload Shipping Details</h3>


        	<form action="" method="post" enctype="multipart/form-data">

        	  <?php wp_nonce_field( 'shipping_table_rate_action', 'shipping_table_rate_nonce' ); ?>

        	 	<input type="file" name="csv_import" class="form-control">

        	 	<input type="submit" name="submit_csv" value="Import CSV" class="button-primary">
            <a href="<?php echo plugin_dir_url(__FILE__).'media/custom_mp_table_rate.csv';?>" class="button">Download Sample File</a>

        	</form>

          <br />

        	<form action="" method="post">

        	  <?php wp_nonce_field( 'shipping_table_rate_action', 'shipping_table_rate_nonce' ); ?>

        		<table style="width:100%;" class="table_rate_shipping">

        			<thead>
        				<tr>
      						<th>Zone Label</th>
      						<th>Zone Region</th>
      						<th>Select Basis</th>
      						<th>Min Value</th>
      						<th>Max Value</th>
      						<th>Price</th>
      						<th>Action</th>
      					</tr>
      				</thead>
      				<tfoot>
      					<tr>
      						<td>
      							<button id="insert_new" class="button-primary">Insert Row</button>
      						</td>
      					</tr>
      				</tfoot>
      				<tbody>

        				<?php

        				if( !empty( $table_rate_shipping_data ) && isset( $table_rate_shipping_data ) ) :

        					$j = 0;

        					$selected = '';

                  foreach( $table_rate_shipping_data as $new_data ) :

        						if( isset( $new_data->shipping_basis ) && !empty( $new_data->shipping_basis ) ) {

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

        										foreach( $countries as $c_key => $c_value ) {
        											if( !empty( $zone_c ) ) {

        												if( is_array( $zone_c ) ) {

        														if( in_array( $c_key, $zone_c ) ) {

        															$selected = "selected";

        															echo "<option value=".$c_key." ".$selected .">".$c_value."</option>";

        														}
        														else {

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
        											else {

                                echo "<option value=".$c_key.">".$c_value."</option>";

        											}

        										}

        									?>

        								</select>

        							</td>

        							<td>

        								<select name="select_type[0<?php echo $j; ?>]" class="shipping_basis_selector">

        									<option>Select Type</option>

        									<option value="pro_weight" <?php if( !empty( $select_basis ) && $select_basis=='pro_weight') echo "selected";?>>Weight</option>

                          <option value="pro_pincode" <?php if( !empty( $select_basis ) && $select_basis=='pro_pincode') echo "selected";?>>Pincode</option>

        									<option value="pro_global" <?php if( !empty( $select_basis ) && $select_basis=='pro_global') echo "selected";?>>Global Shipping</option>

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

        								<button  class='button-primary remove-table-row'>Remove</button>

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

        				<button name="submit_shipping" class="button-primary" type="submit" value="Save Shipping">Save Shipping</button>

        			</form>

        		</div>

        <?php

        }

        function add_new_calling_pages() {

            global $current_user,$wpdb;

            $user_id = get_current_user_id();

            $seller_info = $wpdb->get_var( "SELECT user_id FROM ".$wpdb->prefix."mpsellerinfo WHERE user_id = '".$user_id ."' and seller_value='seller'" );

            $pagename = get_query_var('pagename');

            $main_page = get_query_var('main_page');

            if( !empty( $pagename ) ) {

                if( $main_page == "table-rate" && ($user_id || $seller_info > 0 ) )
                {
                    require_once 'includes/table-rate.php';

                    add_shortcode( 'marketplace','table_rate_shipping' );

                }

            }
        }




        public function add_table_rate_mp( $methods ) {

            $methods['mp_table_rate'] = 'MP_Tablerate_Extended';

            return $methods;
        }

        /**
         * If Woocommerce is not installed, show a notice
         * @return void
         */
        public function no_woo_nag() {
             // We need plugin.php!
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

            $plugins = get_plugins();

            // Set plugin directory
            $plugin_path = array_filter( explode( '/', $plugin_path ) );

            $this->plugin_path = end( $plugin_path );

            // Set plugin file
            $this->plugin_file = $plugin_file;

            // Set plugin name
            $this->plugin_name = 'WooCommerce Marketplace Table Rates';

            // Is EDD installed?
            foreach( $plugins as $plugin_path => $plugin ) {

                if( $plugin['Name'] == 'WooCommerce' ) {

                    $this->has_woo = true;

                    $this->wpr_base = $plugin_path;

                    break;
                }
            }

            if( $this->has_woo ) {

                $url  = esc_url( wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=' . $this->wpr_base ), 'activate-plugin_' . $this->wpr_base ) );

                $link = '<a href="' . $url . '">' . __( 'activate it', 'woocommerce' ) . '</a>';

            } else {

                $url  = esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' ) );

                $link = '<a href="' . $url . '">' . __( 'install it', 'woocommerce' ) . '</a>';

            }

            echo '<div class="error"><p>' . $this->plugin_name . sprintf( __( ' requires WooCommerce! Please %s to continue!', 'woocommerce' ), $link ) . '</p></div>';
        }


        /**
         * Add settings
         *
         * @access      public
         * @since       1.0.0
         * @param       array $settings The existing EDD settings array
         * @return      array The modified EDD settings array
         */
        public function settings( $settings ) {
            $new_settings = array(
                array(
                    'id'    => 'mp_table_rate_shipping_settings',
                    'name'  => '<strong>' . __( 'Plugin Name Settings', 'woocommerce' ) . '</strong>',
                    'desc'  => __( 'Configure Plugin Name Settings', 'woocommerce' ),
                    'type'  => 'header',
                )
            );

            return array_merge( $settings, $new_settings );
        }


    }
} // End if class_exists check


/**
 * The main function responsible for returning the one true MP_TABLE_RATE_SHIPPING
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \MP_TABLE_RATE_SHIPPING The one true MP_TABLE_RATE_SHIPPING

 */
function MP_Table_Rate_load() {

  if( ! class_exists( 'WooCommerce' ) ) {

    if( ! class_exists( 'MP_TableRate_Activation' ) ) {

      require_once 'includes/class-activation.php';

    }

    $activation = new MP_TableRate_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );

    $activation = $activation->run();

    //return WPRWooGiftcards::instance();
  } else {

    return MP_TABLE_RATE_SHIPPING::instance();

  }

}

add_action( 'plugins_loaded', 'wk_mp_table_rate_check_marketplace_is_installed', 11 );

/*
	Check if marketplace plugin is already installed.
*/
function wk_mp_table_rate_check_marketplace_is_installed() {

	if ( ! class_exists( 'Marketplace' ) ) {

    add_action( 'admin_notices', 'wk_mp_table_rate_check_marketplace_is_installed_notice' );

  }
	else {

		MP_Table_Rate_load();

	}

}

function wk_mp_table_rate_check_marketplace_is_installed_notice() {

  ?>
  <div class="error">
    <p><?php echo __( 'Marketplace Table Rate Shipping is enabled but not effective. It requires ', 'mp_table_rate' ) . '<a href="https://codecanyon.net/item/wordpress-woocommerce-marketplace-plugin/19214408" target="_blank">' . __( 'Marketplace Plugin', 'mp_table_rate' ) . '</a>' . __( ' in order to work.', 'Alert Message: Marketplace requires', 'mp_table_rate' ); ?></p>
  </div>
  <?php

}

remove_filter( 'woocommerce_cart_needs_shipping', 'cart_transient_updation' );
/**
 * The activation hook is called outside of the singleton because WordPress doesn't
 * register the call from within the class, since we are preferring the plugins_loaded
 * hook for compatibility, we also can't reference a function inside the plugin class
 * for the activation function. If you need an activation function, put it here.
 *
 * @since       1.0.0
 * @return      void
 */
function mp_table_rateactivation() {

  if( get_option( 'woocommerce_mp_table_rate_settings' )['enabled'] == 'yes' ){

    update_option('wk_mp_shipping_plugin',true);

  }

}

function mp_table_ratedeactivation() {

  delete_option('wk_mp_shipping_plugin');

}

register_activation_hook( __FILE__, 'mp_table_rateactivation' );

register_deactivation_hook( __FILE__, 'mp_table_ratedeactivation' );
