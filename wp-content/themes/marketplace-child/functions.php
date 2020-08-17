<?php
add_action( 'wp_enqueue_scripts', 'marketplace_child_enqueue_styles' );
function marketplace_child_enqueue_styles()
{
	wp_enqueue_style( 'parent-style', get_theme_file_uri( '/style.css' ) );
	if ( is_rtl() ) {
		wp_enqueue_style( 'rtl-style', get_theme_file_uri( '/rtl.css' ) );
	}
}

function new_contact_methods( $contactmethods ) {
     $contactmethods['billing_phone'] = 'Phone Number';
     return $contactmethods;
 }
 add_filter( 'user_contactmethods', 'new_contact_methods', 10, 1 );

 function new_modify_user_table( $column ) {
     $column['billing_phone'] = 'Phone';
     return $column;
 }
 add_filter( 'manage_users_columns', 'new_modify_user_table' );

 function new_modify_user_table_row( $val, $column_name, $user_id ) {
     switch ($column_name) {
         case 'billing_phone' :
             return get_the_author_meta( 'billing_phone', $user_id );
         default:
     }
     return $val;
 }
 add_filter( 'manage_users_custom_column', 'new_modify_user_table_row', 10, 3 );

 function wooc_extra_register_fields() {?>
       <p class="form-row form-row-wide">
       <label for="reg_billing_phone"><?php _e( 'Phone', 'woocommerce' ); ?><span class="required">*</span></label>
       <input type="text" class="input-text" name="billing_phone" id="reg_billing_phone" value="<?php esc_attr_e( $_POST['billing_phone'] ); ?>" />
       </p>
       <?php
 }
 add_action( 'woocommerce_register_form_start', 'wooc_extra_register_fields' );

function wooc_save_extra_register_fields( $customer_id ) {
    if ( isset( $_POST['billing_phone'] ) ) {
                 // Phone input filed which is used in WooCommerce
                 update_user_meta( $customer_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
          }
}
add_action( 'woocommerce_created_customer', 'wooc_save_extra_register_fields' );


// Utility function to get all available shipping zones locations
// function get_wc_shipping_zones_locations( ){
//     global $wpdb;

//     return $wpdb->get_col("
//         SELECT DISTINCT location_code 
//         FROM {$wpdb->prefix}woocommerce_shipping_zone_locations
//     ");
// }

// // Add a dropdown to filter orders by state
// add_action('restrict_manage_posts', 'add_shop_order_filter_by_state');
// function add_shop_order_filter_by_state(){
//     global $pagenow, $typenow;

//     if( 'shop_order' === $typenow && 'edit.php' === $pagenow ) {
//         // Get available countries codes with their states code/name pairs
//         $country_states = WC()->countries->get_allowed_country_states();

//         // Initializing
//         $filter_id   = 'shipping_state';
//         $current     = isset($_GET[$filter_id])? $_GET[$filter_id] : '';

//         echo '<select name="'.$filter_id.'">
//         <option value="">'.__( 'Filter by State/Province', 'woocommerce' )."</option>";

//         // Loop through shipping zones locations array
//         foreach( get_wc_shipping_zones_locations() as $country_state ) {
//             $country_state = explode(':', $country_state);
//             $country_code  = reset($country_state);
//             $state_code    = end($country_state);

//             if ( isset( $country_states[$country_code][$state_code] ) 
//             && $state_name = $country_states[$country_code][$state_code] ) {
//                 printf( '<option value="%s"%s>%s</option>', $state_code, 
//                     $state_code === $current ? '" selected="selected"' : '', 
//                 $state_name );
//             }
//         }
//         echo '</select>';
//     }
// }

// // Process the filter dropdown for orders by shipping state
// add_filter( 'request', 'process_admin_shop_order_filtering_by_state', 99 );
// function process_admin_shop_order_filtering_by_state( $vars ) {
//     global $pagenow, $typenow;

//     $filter_id = 'shipping_state';

//     if ( $pagenow == 'edit.php' && 'shop_order' === $typenow 
//     && isset( $_GET[$filter_id] ) && ! empty($_GET[$filter_id]) ) {
//         $vars['meta_key']   = '_shipping_state';
//         $vars['meta_value'] = $_GET[$filter_id];
//         $vars['orderby']    = 'meta_value';
//     }
//     return $vars;
// }
 
function seller_list_column( $columns ) {
    $columns['seller'] = 'Seller';
    return $columns;
}
add_filter( 'manage_edit-shop_order_columns', 'seller_list_column' );
 

function seller_list_column_content( $column ) {

    global $wpdb;
    global $post;
    $order = wc_get_order( $post->ID );
    $order_id =  $order->get_order_number();

    if ( 'seller' === $column ) {

      $sel_order = $wpdb->get_results( "SELECT seller_id FROM wpnw_mpseller_orders where order_id = $order_id" );
      $sel_number = $sel_order[0]->seller_id;
      $seller_id = $wpdb->get_results( "SELECT display_name FROM wpnw_users where ID = $sel_number" );
      echo ucfirst($seller_id[0]->display_name); 
      // var_dump($seller_id);die();
      
      
    }
}

add_action( 'manage_shop_order_posts_custom_column', 'seller_list_column_content' );

// Add a dropdown to filter orders by seller
add_action('restrict_manage_posts', 'add_shop_order_filter_by_seller');
function add_shop_order_filter_by_seller(){
  echo '<input type="text" id="SellerInput" onkeyup="myFunction()" placeholder="Search for Seller.." title="Type in a name">';
  ?>

  <script>
function myFunction() {
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById("SellerInput");
  filter = input.value.toUpperCase();
  table = document.getElementById("the-list");
  tr = table.getElementsByTagName("tr");
  for (i = 0; i < tr.length; i++) {
    td = tr[i].getElementsByTagName("td")[8];
    if (td) {
      txtValue = td.textContent || td.innerText;
      if (txtValue.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
    }       
  }
}
</script>
<?php
}

function custom_wc_single_product(){

    $product_cats = wp_get_post_terms( get_the_ID(), 'product_cat' );

    if ( $product_cats && ! is_wp_error ( $product_cats ) ){

        $single_cat = array_shift( $product_cats ); ?>

        <!-- <h2 itemprop="name" class="product_category_title"><span><?php echo $single_cat->name; ?></span></h2> -->
        <div class="products product-grid">
          <h2 class="product-grid-title"><span>Upsell Products</span></h2>
      </div>
      <div id="custom-upsell"><?php echo do_shortcode('[products limit="5" orderby="rand" category="'.$single_cat->slug.'"]'); ?></div>
      <script type="text/javascript">
        jQuery(document).ready(function($) {
          var backButton = '<span class="fa fa-angle-left prev slick-arrow slick-disabled" aria-disabled="true" style="display: block;"></span>';
          var nextButton = '<span class="fa fa-angle-right next slick-arrow" aria-disabled="false" style="display: block;"></span>';
             jQuery('#custom-upsell .products').slick({
                dots: false,
                infinite: true,
                speed: 500,
                slidesToShow: 3,
                slidesToScroll: 1,
                autoplay: false,
                autoplaySpeed: 2000,
                arrows: true,
                prevArrow: backButton,
              nextArrow: nextButton,
                responsive: [{
                  breakpoint: 768,
                  settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1
                  }
                },
                {
                   breakpoint: 400,
                   settings: {
                      arrows: false,
                      slidesToShow: 1,
                      slidesToScroll: 1
                   }
                }]
            });
        });
         </script>  

<?php }
}
add_action( 'woocommerce_after_single_product_summary', 'custom_wc_single_product', 10 );