<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Dokan Class. To enhance compatibility with Dokan plugin.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 3.8.2
 */
class WOO_Vou_WeDevs_Dokan {
	
	var $scripts, $model, $render, $voumeta, $vouadmin;
	
	public function __construct() {
		
		global $woo_vou_scripts, $woo_vou_model, $woo_vou_render, $woo_vou_admin_meta, $woo_vou_admin;
		
		$this->scripts 	= $woo_vou_scripts;
		$this->model 	= $woo_vou_model;
		$this->render 	= $woo_vou_render;
		$this->voumeta	= $woo_vou_admin_meta;
		$this->vouadmin = $woo_vou_admin;

		// include required files
		$this->includes();
		
	}
	
	/**
	 * Include required core files used to add compability of Dokan for the front end
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.2
	 */
	public function includes() {
		
		//Vendor Class for front-end
		require_once ( WOO_VOU_META_DIR . '/woo-vou-meta-box-functions.php' );		
	}
	
	/**
	 * Modify Dokan Role Array
	 * 
	 * Handle to modify vendor role array
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.8.2
	 */
	public function woo_vou_dokan_voucher_role( $woo_vou_roles ) {

		// Get global options
		$vou_enable_auto_integrate_dokan_vendor = get_option('vou_enable_auto_integrate_dokan_vendor');

		// add doken seller as a vendor
		if( empty( $vou_enable_auto_integrate_dokan_vendor ) || $vou_enable_auto_integrate_dokan_vendor == 'yes' ) {
			$woo_vou_roles[] = 'seller';
		}

		return $woo_vou_roles;
	}

	/**
	 * Modify WeDevs Dokan Role Array
	 * 
	 * Handle to modify settings array
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.8.2
	 */
	public function woo_vou_dokan_settings( $settings ) {

		// add doken seller as a vendor
		array_push($settings, array( 
				'name'	=>	esc_html__( 'Dokan Multi Vendor Settings', 'woovoucher' ),
				'type'	=>	'title',
				'desc'	=>	'',
				'id'	=>	'vou_dokan_settings'
			),
			array(
					'id'		=> 'vou_enable_auto_integrate_dokan_vendor',
					'name'		=> esc_html__( 'Auto Integrate vendor with PDF Voucher', 'woovoucher' ),
					'desc'		=> esc_html__( 'Auto Integrate Dokan Multi Vendor vendor\'s access', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">' . esc_html__( 'Check this box if you want to integrate Dokan Multi Vendor vendors with PDF Vouchers vendors so that you have same access as pdf vouchers vendor\'s have.', 'woovoucher' ) . '</p>',
					'default'	=> 'yes'
				),
				array(
					'id'		=> 'vou_enable_wedevs_dokan_vendor_acess_pdf_vou_meta',
					'name'		=> esc_html__( 'Enable Access to PDF Vouchers tab', 'woovoucher' ),
					'desc'		=> esc_html__( 'Enable Access to PDF Vouchers tab', 'woovoucher' ),
					'type'		=> 'checkbox',
					'desc_tip'	=> '<p class="description">' . esc_html__( 'Check this box if you want to allow vendors to access PDF Vouchers on add/edit product page on frontend and backend.', 'woovoucher' ) . '</p>',
					'default'	=> 'yes'
				),
			array( 
				'type' 		=> 'sectionend',
				'id' 		=> 'vou_dokan_settings'
			)
		);
		return $settings;
	}
	
	/**
	 * PDF Voucher settings
	 * 
	 * Handle add PDF voucher settings to vendor dashboard
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.8.2
	 */
	public function woo_vou_dokan_get_vendor_products($product_arr){
		
		$args = array(
			'posts_per_page' => -1,
			'post_status'	=> 'publish',
			'author'         => get_current_user_id(),
		);
		$product_query = dokan()->product->all($args);
		if ( $product_query->have_posts() ) {
			while ($product_query->have_posts()) {
				$product_query->the_post(); 
				
				$product_obj = wc_get_product( get_the_ID());				
				if( !empty( $product_obj ) ) {
					$product_arr[get_the_ID()] = $product_obj->get_formatted_name();
				}
			}
		}
		
		return $product_arr;
	}
	
	/**
	 * PDF Voucher settings
	 * 
	 * Handle add PDF voucher settings to vendor dashboard
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.8.2
	 */
	public function woo_vou_dokan_register_settings_for_vender_dashboard(){
		?>
		<div class="dokan-product-pdf-voucher dokan-edit-row <?php echo esc_attr( $class ); ?>">
			<div class="dokan-section-heading" data-togglehandler="dokan_product_pdf_voucher">
				<h2><i class="fa fa-wrench"></i> <?php esc_html_e( 'PDF Vouchers', 'woovoucher' ); ?></h2>
				<p><?php esc_html_e( 'Manage PDF Vouchers settings for this product.', 'woovoucher' ); ?></p>
				<a href="#" class="dokan-section-toggle">
					<i class="fa fa-sort-desc fa-flip-vertical" aria-hidden="true"></i>
				</a>
				<div class="dokan-clearfix"></div>
			</div>

			<div class="dokan-section-content">
				<div class="dokan-clearfix"></div>				
				<?php
					$this->voumeta->woo_vou_product_write_panel(true); // to fix inline css iss with wcmp
				?>
			</div><!-- .dokan-side-right -->
		</div><!-- .dokan-product-inventory -->
		<?php
		
	}
	
	
	/**
	 * include JS
	 * 
	 * Handels to add JS on vendor dashboard page
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.2
	 */
	 public function woo_vou_dokan_vendor_scripts() {
		
		global $post, $wp_version, $woocommerce;
	
		
		if ( dokan_is_seller_dashboard() || ( get_query_var( 'edit' ) && is_singular( 'product' ) ) ) {
			
			// Enqueue Meta Box Scripts
			wp_enqueue_script( 'woo-vou-meta-box', WOO_VOU_META_URL . '/js/meta-box.js', array( 'jquery' ), WOO_VOU_PLUGIN_VERSION, true );			
			
			
			//localize script
			$newui = $wp_version >= '3.5' ? '1' : '0'; //check wp version for showing media uploader
			wp_localize_script( 
				'woo-vou-meta-box', 
				'WooVou', 
				array(	
					'new_media_ui'	=>	$newui,
					'one_file_min'	=>  esc_html__('You must have at least one file.','woovoucher' )
				)
			);
		
			// register and enqueue script		
			wp_register_script( 'woo-vou-script-metabox', WOO_VOU_URL.'includes/js/woo-vou-metabox.js', array( 'jquery', 'jquery-form' ), WOO_VOU_PLUGIN_VERSION, true ); 
			wp_enqueue_script( 'woo-vou-script-metabox' );
			
			// localize script
			wp_localize_script( 
				'woo-vou-script-metabox', 
				'WooVouMeta', 			
				array(	
					'invalid_url' 			=> esc_html__( 'Please enter valid url (i.e. http://www.example.com).', 'woovoucher' ),
					'noofvouchererror' 		=> '<div>' . esc_html__( 'Please enter Number of Voucher Codes.', 'woovoucher' ) . '</div>',
					'onlydigitserror' 		=> '<div>' . esc_html__( 'Please enter only Numeric values in Number of Voucher Codes.', 'woovoucher' ) . '</div>',
					'patternemptyerror' 	=> '<div>' . esc_html__( 'Please enter Pattern to import voucher code(s).', 'woovoucher' ) . '</div>',
					'generateerror' 		=> '<div>' . esc_html__( 'Please enter Valid Pattern to import voucher code(s).', 'woovoucher' ) . '</div>',
					'filetypeerror'			=> '<div>' . esc_html__( 'Please upload csv file.', 'woovoucher' ) . '</div>',
					'fileerror'				=> '<div>' . esc_html__( 'File can not be empty, please upload valid file.', 'woovoucher' ) . '</div>',
					'enable_voucher'        => get_option( 'vou_enable_voucher' ), //Localize "Auto Enable Voucher" setting to use in JS
					'ajaxurl'               => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
				) 
			);
		
			// Enqueue for  image or file uploader
			wp_enqueue_script( 'media-upload' );
			add_thickbox();
			wp_enqueue_script( 'jquery-ui-sortable' );
			
			// woocommerce js directory url
			$js_dir = $woocommerce->plugin_url() . '/assets/js/';
			
			// Use minified libraries if SCRIPT_DEBUG is turned off
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';				
			
			// Enqueue for datepicker
			wp_enqueue_script( array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-slider' ) );
			
			wp_deregister_script( 'datepicker-slider' );
			wp_register_script( 'datepicker-slider', WOO_VOU_META_URL.'/js/datetimepicker/jquery-ui-slider-Access.js', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_script( 'datepicker-slider' );
			
			wp_deregister_script( 'timepicker-addon' );
			wp_register_script( 'timepicker-addon', WOO_VOU_META_URL.'/js/datetimepicker/jquery-date-timepicker-addon.js', array('datepicker-slider'), WOO_VOU_PLUGIN_VERSION, true );
			wp_enqueue_script( 'timepicker-addon' );			
			
			// Enqueu built-in script for color picker.
			if( wp_style_is( 'wp-color-picker', 'registered' ) ) { //since WordPress 3.5
				wp_enqueue_script( 'wp-color-picker' );
			} else {
				wp_enqueue_script( 'farbtastic' );
			}
			
			// Register for dokan dashboard page
			wp_register_script( 'woo-vou-wedevs-dokan-public-script', WOO_VOU_URL.'includes/js/woo-vou-wedevs-dokan-public-script.js', array( 'jquery'), WOO_VOU_PLUGIN_VERSION, true ); 
			wp_enqueue_script( 'woo-vou-wedevs-dokan-public-script' );
			
			
			$vou_enable_wedevs_dokan_vendor_acess_pdf_vou_meta = get_option('vou_enable_wedevs_dokan_vendor_acess_pdf_vou_meta');
			// localize script
			wp_localize_script( 
				'woo-vou-wedevs-dokan-public-script', 
				'WooVouDokanPublic', 			
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
					'vou_enable_wedevs_dokan_vendor_acess_pdf_vou_meta' => $vou_enable_wedevs_dokan_vendor_acess_pdf_vou_meta,
				) 
			);
			
			
		}		
	} 
	
	/**
	 * Add Popup For import Voucher Code in Admin
	 * 
	 * Handels to show import voucher code popup
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.2
	 */
	public function woo_vou_dokan_import_code_popup() {		
		global $post;
	
		if ( dokan_is_seller_dashboard() || ( get_query_var( 'edit' ) && is_singular( 'product' ) ) ) {		
			// include import voucher code popup file
			include_once( WOO_VOU_ADMIN . '/forms/metabox-popups/woo-vou-import-code-popup.php' );
		}
	}
	
	
	/**
	 * include css
	 * 
	 * Handels to add css on vendor dashboard page
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.2
	 */
	public function woo_vou_dokan_vendor_styles() {
		
		global $post, $wp_version, $woocommerce;
		
		
		if ( dokan_is_seller_dashboard() || ( get_query_var( 'edit' ) && is_singular( 'product' ) ) ) {
			// Enqueue Meta Box Style		
			wp_enqueue_style( 'woo-vou-meta-box-1', WOO_VOU_META_URL . '/css/meta-box.css');		
			
			wp_register_style( 'woo-vou-style-metabox-1', WOO_VOU_URL.'includes/css/woo-vou-metabox.css');
		
			wp_enqueue_style( 'woo-vou-style-metabox-1' );
			
			// css directory url of woocommerce
			$css_dir = $woocommerce->plugin_url() . '/assets/css/';
		
			// enqueue woocommerce admin styles
			wp_enqueue_style( 'woo_vou_admin_styles', $css_dir . 'admin.css', array(), WOOCOMMERCE_VERSION );							
		
			// Enqueue for datepicker
			wp_enqueue_style( 'woo-vou-meta-jquery-ui-css', WOO_VOU_META_URL.'/css/datetimepicker/date-time-picker.css', array(), WOO_VOU_PLUGIN_VERSION );
			
			// Enqueue built-in style for color picker.
			if( wp_style_is( 'wp-color-picker', 'registered' ) ) { //since WordPress 3.5
				wp_enqueue_style( 'wp-color-picker' );
			} else {
				wp_enqueue_style( 'farbtastic' );
			}			
			wp_register_style( 'woo-vou-vendor-pro-styles', WOO_VOU_URL . 'includes/css/woo-vou-vendor-pro.css', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_style( 'woo-vou-vendor-pro-styles' );

			wp_register_style( 'woo-vou-wedevs-dokan-public-styles', WOO_VOU_URL . 'includes/css/woo-vou-wedevs-front-style.css', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_style( 'woo-vou-wedevs-dokan-public-styles' );

			wp_register_style( 'woo-vou-dokan-front-styles', WOO_VOU_URL . 'includes/css/woo-vou-dokan-front.css', array(), WOO_VOU_PLUGIN_VERSION );
			wp_enqueue_style( 'woo-vou-dokan-front-styles' ); 
		}
	}
	
	
	
	/**
	 * Save pdf voucher data
	 * 
	 * Handels to save all pdf voucher data
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.2
	 */
	public function woo_vou_dokan_save_voucher_meta($post_id){
		woo_vou_product_save_data( $post_id, get_post( $post_id ) );

		// Vendor auto save
		$user_id = get_current_user_id(); // Get current user

		// get prefix
		$prefix = WOO_VOU_META_PREFIX;

		if( isset($_POST['_woo_vou_enable']) && !empty($user_id) ){
			// Vendor User
			update_post_meta( $post_id, $prefix . 'vendor_user', $user_id );
		}
	}

	/**
	 * Code to show pdf voucher tab on backend if both option selected 
	 * 
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.2
	 */
	public function woo_vou_dokan_check_tab_show_to_backend( $show ){
		$vou_enable_auto_integrate_dokan_vendor = get_option('vou_enable_auto_integrate_dokan_vendor');
		$vou_enable_wedevs_dokan_vendor_acess_pdf_vou_meta = get_option('vou_enable_wedevs_dokan_vendor_acess_pdf_vou_meta');
		$user = wp_get_current_user();

		if( ( $vou_enable_auto_integrate_dokan_vendor != 'yes' || $vou_enable_wedevs_dokan_vendor_acess_pdf_vou_meta != 'yes' ) && !in_array( 'administrator', (array) $user->roles ) ){
			$show = false;
		}

		return $show;
	}
	
	
	
	/**
	 * Adding Hooks
	 * 
	 * Adding proper hooks for Vendor.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 3.8.2
	 */
	public function add_hooks(){ 

		// Append the pdf voucher role
		add_filter( 'woo_vou_edit_vendor_role', array($this, 'woo_vou_dokan_voucher_role' ) );

		// Append the settings array
		add_filter( 'woo_vou_misc_settings', array( $this, 'woo_vou_dokan_settings' ) );
		
		add_action('dokan_product_edit_after_main',array($this,'woo_vou_dokan_register_settings_for_vender_dashboard'));
		
		
		// add action to add scripts for vendor dashboard page
		add_action('wp_enqueue_scripts', array( $this, 'woo_vou_dokan_vendor_scripts') );	
		
		// add action to add css for on vendor dashboard page
		add_action( 'wp_enqueue_scripts', array( $this, 'woo_vou_dokan_vendor_styles' ) );		
		
		// add action to include import voucher code popup file
		add_action( 'wp_footer', array( $this, 'woo_vou_dokan_import_code_popup' ) );		
		
		add_action('dokan_process_product_meta',array($this,'woo_vou_dokan_save_voucher_meta'),10,1);

		// Filter to show or hide pdf voucher tab on backend if both option selected
		add_filter('woo_vou_pdf_voucher_tab_show_in_product', array($this,'woo_vou_dokan_check_tab_show_to_backend') );
		
		//add_filter('woo_vou_coupon_product_opt',array($this,'woo_vou_dokan_get_vendor_products'),10,1);
	
	}	

}