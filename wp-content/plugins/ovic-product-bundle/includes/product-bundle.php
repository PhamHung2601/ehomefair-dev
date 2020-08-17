<?php if ( !defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
if ( !class_exists( 'Ovic_Bundle_Woo' ) ) {
	class Ovic_Bundle_Woo
	{
		function __construct()
		{
			// Menu
			add_action( 'admin_menu', array( $this, 'ovic_bundle_admin_menu' ), 10 );
			// Enqueue frontend scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'bundle_enqueue_scripts' ) );
			// Enqueue backend scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'bundle_admin_enqueue_scripts' ) );
			// Backend AJAX search
			add_filter( 'woocommerce_json_search_found_products', array( $this, 'ovic_bundle_search_products' ) );
			// Backend AJAX remove
			add_filter( 'wp_ajax_ovic_bundle_remove_product', array( $this, 'ovic_bundle_remove_product' ) );
			add_action( 'wp_ajax_nopriv_ovic_bundle_remove_product', array( $this, 'ovic_bundle_remove_product' ) );
			// Product data tabs
			add_filter( 'woocommerce_product_data_tabs', array( $this, 'ovic_bundle_product_data_tabs' ), 10, 1 );
			// Product data panels
			add_action( 'woocommerce_product_data_panels', array( $this, 'ovic_bundle_product_data_panels' ) );
			add_action( 'woocommerce_process_product_meta_simple', array( $this, 'ovic_bundle_save_option_field' ) );
			// Add to cart form & button
			add_action( 'woocommerce_after_single_product_summary', array( $this, 'ovic_bundle_add_to_cart_form' ), 6 );
			// Add to cart
			add_action( 'woocommerce_ajax_added_to_cart', array( $this, 'ovic_bundle_ajax_added_to_cart' ) );
			add_action( 'woocommerce_add_to_cart', array( $this, 'ovic_bundle_add_to_cart' ), 10, 6 );
			add_filter( 'woocommerce_add_cart_item', array( $this, 'ovic_bundle_add_cart_item' ), 10, 1 );
			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'ovic_bundle_add_cart_item_data' ), 10, 2 );
			add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'ovic_bundle_get_cart_item_from_session', ), 10, 2 );
			// Cart item
			add_filter( 'woocommerce_cart_item_name', array( $this, 'ovic_bundle_cart_item_name' ), 10, 2 );
			add_filter( 'woocommerce_cart_item_price', array( $this, 'ovic_bundle_cart_item_price' ), 10, 2 );
			add_filter( 'woocommerce_cart_item_quantity', array( $this, 'ovic_bundle_cart_item_quantity' ), 10, 3 );
			add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'ovic_bundle_cart_item_subtotal' ), 10, 2 );
			add_filter( 'woocommerce_cart_item_remove_link', array( $this, 'ovic_bundle_cart_item_remove_link', ), 10, 2 );
			add_filter( 'woocommerce_cart_contents_count', array( $this, 'ovic_bundle_cart_contents_count' ) );
			add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'ovic_bundle_update_cart_item_quantity', ), 1, 2 );
			add_action( 'woocommerce_before_cart_item_quantity_zero', array( $this, 'ovic_bundle_update_cart_item_quantity', ), 1 );
			add_action( 'woocommerce_cart_item_removed', array( $this, 'ovic_bundle_cart_item_removed' ), 10, 2 );
			// Checkout item
			add_filter( 'woocommerce_checkout_item_subtotal', array( $this, 'ovic_bundle_cart_item_subtotal', ), 10, 2 );
			// Checkout order detail item
			add_filter( 'woocommerce_order_formatted_line_subtotal', array( $this, 'ovic_bundle_cart_item_subtotal', ), 10, 2 );
			// Hide on cart & checkout page
			if ( get_option( '_ovic_hide_bundle', 'no' ) == 'yes' ) {
				add_filter( 'woocommerce_cart_item_visible', array( $this, 'ovic_bundle_item_visible' ), 10, 2 );
				add_filter( 'woocommerce_order_item_visible', array( $this, 'ovic_bundle_item_visible' ), 10, 2 );
				add_filter( 'woocommerce_checkout_cart_item_visible', array( $this, 'ovic_bundle_item_visible', ), 10, 2 );
			}
			// Hide on mini-cart
			if ( get_option( '_ovic_hide_bundle_mini_cart', 'no' ) == 'yes' ) {
				add_filter( 'woocommerce_widget_cart_item_visible', array( $this, 'ovic_bundle_item_visible', ), 10, 2 );
			}
			// Item class
			add_filter( 'woocommerce_cart_item_class', array( $this, 'ovic_bundle_item_class' ), 10, 2 );
			add_filter( 'woocommerce_mini_cart_item_class', array( $this, 'ovic_bundle_item_class' ), 10, 2 );
			add_filter( 'woocommerce_order_item_class', array( $this, 'ovic_bundle_item_class' ), 10, 2 );
			// Hide item meta
			add_filter( 'woocommerce_display_item_meta', array( $this, 'ovic_bundle_display_item_meta' ), 10, 2 );
			add_filter( 'woocommerce_order_items_meta_get_formatted', array( $this, 'ovic_bundle_order_items_meta_get_formatted', ), 10, 1 );
			// Order item
			add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'ovic_bundle_add_order_item_meta', ), 10, 3 );
			add_filter( 'woocommerce_order_item_name', array( $this, 'ovic_bundle_cart_item_name' ), 10, 2 );
			// Admin order
			add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'ovic_bundle_hidden_order_item_meta', ), 10, 1 );
			add_action( 'woocommerce_before_order_itemmeta', array( $this, 'ovic_bundle_before_order_item_meta', ), 10, 1 );
			// Add custom data
			add_action( 'wp_ajax_ovic_bundle_custom_data', array( $this, 'ovic_bundle_custom_data_callback' ) );
			add_action( 'wp_ajax_nopriv_ovic_bundle_custom_data', array( $this, 'ovic_bundle_custom_data_callback' ) );
			// Calculate totals
			add_action( 'woocommerce_before_calculate_totals', array( $this, 'ovic_bundle_before_calculate_totals', ), 99, 1 );
			// Shipping
			add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'ovic_bundle_cart_shipping_packages' ) );
		}

		function ovic_bundle_admin_menu()
		{
			if ( current_user_can( 'edit_theme_options' ) ) {
				add_submenu_page(
					'ovic-plugins',
					'Ovic Product Bundle',
					'Ovic Product Bundle',
					'manage_options',
					'ovic-product-bundle',
					array( $this, 'ovic_bundle_admin_menu_content' )
				);
			}
		}

		function ovic_bundle_remove_product()
		{
			if ( isset( $_POST['id'] ) ) {
				update_post_meta( $_POST['id'], 'ovic_bundle_ids', '' );
			}
			wp_die();
		}

		function field_select( $field )
		{
			$value = isset( $field['default'] ) ? get_option( $field['id'], $field['default'] ) : '';
			?>
            <tr>
                <th><?php echo esc_html( $field['title'] ); ?></th>
                <td>
                    <select name="<?php echo esc_attr( $field['id'] ) ?>">
						<?php foreach ( $field['options'] as $key => $option ): ?>
                            <option value="<?php echo esc_attr( $key ); ?>"
								<?php selected( $value, $key, true ); ?>>
								<?php echo esc_html( $option ); ?>
                            </option>
						<?php endforeach; ?>
                    </select>
					<?php if ( isset( $field['desc'] ) ): ?>
                        <span class="description"><?php echo esc_html( $field['desc'] ); ?></span>
					<?php endif; ?>
                </td>
            </tr>
			<?php
		}

		function field_text( $field )
		{
			$value = isset( $field['default'] ) ? get_option( $field['id'], $field['default'] ) : '';
			?>
            <tr>
                <th><?php echo esc_html( $field['title'] ); ?></th>
                <td>
                    <input type="text" name="<?php echo esc_attr( $field['id'] ); ?>"
                           value="<?php echo esc_attr( $value ); ?>"/>
					<?php if ( isset( $field['desc'] ) ): ?>
                        <span class="description"><?php echo esc_html( $field['desc'] ); ?></span>
					<?php endif; ?>
                </td>
            </tr>
			<?php
		}

		function ovic_bundle_admin_menu_content()
		{
			$tabs = array(
				'general_content' => 'General',
				'license_content' => 'Get Pro? ( License )',
			);
			$tab  = 'general_content';
			if ( isset( $_GET['tab'] ) ) {
				$tab = $_GET['tab'];
			}
			?>
            <div class="ovic-wrap">
                <div id="tabs-container" role="tabpanel">
                    <div class="nav-tab-wrapper">
						<?php foreach ( $tabs as $key => $value ): ?>
							<?php
							$url = add_query_arg(
								array(
									'page' => 'ovic-product-bundle',
									'tab'  => $key,
								),
								'admin.php'
							);
							?>
                            <a class="nav-tab <?php if ( $tab == $key ): ?> nav-tab-active<?php endif; ?>"
                               href="<?php echo esc_url( $url ); ?>">
								<?php echo esc_html( $value ); ?>
                            </a>
						<?php endforeach; ?>
                    </div>
                    <div class="tab-content">
						<?php $this->$tab(); ?>
                    </div>
                </div>
            </div>
			<?php
		}

		public function general_content()
		{
			?>
            <div class="ovic_bundle_settings_page wrap">
                <h1 class="ovic_settings_page_title"><?php echo esc_html__( 'Ovic Product Bundles', 'ovic-bundle' ); ?></h1>
                <div class="ovic_settings_page_content">
                    <script>
                        jQuery(document).on('click', '.ovic_bundle_settings_page #col-right a.delete', function (e) {
                            e.preventDefault();
                            var _this   = jQuery(this),
                                _id     = _this.data('id'),
                                _parent = _this.closest('tr');

                            _parent.css('background', 'rgba(255, 138, 8, 0.66)');
                            jQuery.post(ajaxurl, {action: 'ovic_bundle_remove_product', id: _id}, function () {
                                    _parent.remove();
                                }
                            );
                        });
                    </script>
                    <div id="col-container" class="wp-clearfix">
                        <div id="col-left">
                            <div class="col-wrap">
                                <form method="post" action="options.php">
									<?php wp_nonce_field( 'update-options' ) ?>
                                    <table class="form-table">
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( 'General', 'ovic-bundle' ); ?>
                                            </th>
                                        </tr>
										<?php
										$this->field_select(
											array(
												'id'      => '_ovic_bundle_thumb',
												'title'   => esc_html__( 'Show thumbnail', 'ovic-bundle' ),
												'default' => 'yes',
												'options' => array(
													'yes' => esc_html__( 'Yes', 'ovic-bundle' ),
													'no'  => esc_html__( 'No', 'ovic-bundle' ),
												),
											)
										);
										$this->field_select(
											array(
												'id'      => '_ovic_bundle_qty',
												'title'   => esc_html__( 'Show quantity', 'ovic-bundle' ),
												'default' => 'yes',
												'options' => array(
													'yes' => esc_html__( 'Yes', 'ovic-bundle' ),
													'no'  => esc_html__( 'No', 'ovic-bundle' ),
												),
											)
										);
										$this->field_select(
											array(
												'id'      => '_ovic_bundle_price',
												'title'   => esc_html__( 'Show price', 'ovic-bundle' ),
												'default' => 'html',
												'options' => array(
													'price'    => esc_html__( 'Price', 'ovic-bundle' ),
													'html'     => esc_html__( 'Price HTML', 'ovic-bundle' ),
													'subtotal' => esc_html__( 'Subtotal', 'ovic-bundle' ),
													'no'       => esc_html__( 'No', 'ovic-bundle' ),
												),
											)
										);
										$this->field_select(
											array(
												'id'      => '_ovic_bundle_discount',
												'title'   => esc_html__( 'Show price discount', 'ovic-bundle' ),
												'default' => 'yes',
												'options' => array(
													'yes' => esc_html__( 'Yes', 'ovic-bundle' ),
													'no'  => esc_html__( 'No', 'ovic-bundle' ),
												),
											)
										);
										$this->field_select(
											array(
												'id'      => '_ovic_bundle_link',
												'title'   => esc_html__( 'Link to bundled product', 'ovic-bundle' ),
												'default' => 'yes',
												'options' => array(
													'yes' => esc_html__( 'Yes', 'ovic-bundle' ),
													'no'  => esc_html__( 'No', 'ovic-bundle' ),
												),
											)
										);
										$this->field_select(
											array(
												'id'      => '_ovic_hide_bundle',
												'title'   => esc_html__( 'Hide products in the bundle on cart & checkout page', 'ovic-bundle' ),
												'default' => 'no',
												'options' => array(
													'yes' => esc_html__( 'Yes', 'ovic-bundle' ),
													'no'  => esc_html__( 'No', 'ovic-bundle' ),
												),
												'desc'    => esc_html__( 'Hide products in the bundle, just show the main product on the cart & checkout page.', 'ovic-bundle' ),
											)
										);
										$this->field_select(
											array(
												'id'      => '_ovic_hide_bundle_mini_cart',
												'title'   => esc_html__( 'Hide products in the bundle on mini-cart', 'ovic-bundle' ),
												'default' => 'no',
												'options' => array(
													'yes' => esc_html__( 'Yes', 'ovic-bundle' ),
													'no'  => esc_html__( 'No', 'ovic-bundle' ),
												),
												'desc'    => esc_html__( 'Hide products in the bundle, just show the main product on mini-cart.', 'ovic-bundle' ),
											)
										);
										$this->field_text(
											array(
												'id'      => '_ovic_bundle_price_text',
												'title'   => esc_html__( 'Bundle price text', 'ovic-bundle' ),
												'default' => esc_html__( 'Bundle price:', 'ovic-bundle' ),
												'desc'    => esc_html__( 'The text before price when choosing variation in the bundle.', 'ovic-bundle' ),
											)
										);
										$this->field_text(
											array(
												'id'      => '_ovic_bundle_price_save_text',
												'title'   => esc_html__( 'Bundle save price text', 'ovic-bundle' ),
												'default' => esc_html__( 'You save:', 'ovic-bundle' ),
												'desc'    => esc_html__( 'The text before price you saved in the bundle.', 'ovic-bundle' ),
											)
										);
										?>
                                        <tr>
                                            <th><?php esc_html_e( 'Search limit', 'ovic-bundle' ); ?></th>
                                            <td>
                                                <input name="_ovic_search_limit" type="number" min="1"
                                                       max="500"
                                                       value="<?php echo get_option( '_ovic_search_limit', '5' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr class="submit">
                                            <th colspan="2">
                                                <input type="submit" name="submit" class="button button-primary"
                                                       value="<?php esc_html_e( 'Update Options', 'ovic-bundle' ); ?>"/>
                                                <input type="hidden" name="action" value="update"/>
                                                <input type="hidden" name="page_options"
                                                       value="_ovic_bundle_thumb,_ovic_bundle_qty,_ovic_bundle_price,_ovic_bundle_discount,_ovic_bundle_link,_ovic_hide_bundle,_ovic_hide_bundle_mini_cart,_ovic_bundle_price_text,_ovic_bundle_price_save_text,_ovic_search_limit"/>
                                            </th>
                                        </tr>
                                    </table>
                                </form>
                            </div>
                        </div>
                        <div id="col-right">
                            <div class="col-wrap">
                                <table class="wp-list-table widefat fixed striped tags ui-sortable">
                                    <thead>
                                    <tr>
                                        <th scope="col" id="thumb" class="manage-column column-thumb">
											<?php esc_html_e( 'Thumb', 'ovic-bundle' ); ?>
                                        </th>
                                        <th scope="col" id="name"
                                            class="manage-column column-name column-primary">
                                            <span><?php esc_html_e( 'Name', 'ovic-bundle' ); ?></span>
                                        </th>
                                        <th scope="col" id="slug" class="manage-column column-slug">
                                            <span><?php esc_html_e( 'Slug', 'ovic-bundle' ); ?></span>
                                        </th>
                                        <th scope="col" id="count" class="manage-column column-count">
                                            <span><?php esc_html_e( 'Count', 'ovic-bundle' ); ?></span>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody id="the-list" data-wp-lists="list:bundle">
									<?php
									$count    = 0;
									$paged    = ( isset( $_GET['paged'] ) ) ? absint( $_GET['paged'] ) : 1;
									$args     = array(
										'post_type'      => 'product',
										'posts_per_page' => 9,
										'paged'          => $paged,
										'tax_query'      => array(
											array(
												'taxonomy' => 'product_type',
												'field'    => 'slug',
												'terms'    => 'simple',
											),
										),
										'meta_query'     => array(
											array(
												'key'     => 'ovic_bundle_ids',
												'value'   => '',
												'compare' => '!=',
											),
										),
									);
									$products = new WP_Query( $args );

									if ( $products->have_posts() ) {
										while ( $products->have_posts() ) : $products->the_post();
											$count++;
											$product_id        = get_the_ID();
											$bundle_items      = get_post_meta( $product_id, 'ovic_bundle_ids', true );
											$list_bundle       = explode( ',', $bundle_items );
											$product           = wc_get_product( $product_id );
											$thumbnail         = $product->get_image( array( 40, 40 ) );
											$product_name      = $product->get_name();
											$product_slug      = $product->get_slug();
											$product_permalink = $product->is_visible() ? $product->get_permalink() : '';
											$product_edit      = get_edit_post_link( $product_id );
											?>
                                            <tr id="bundle-<?php echo esc_attr( $product_id ); ?>">
                                                <td class="thumb column-thumb" data-colname="Image">
													<?php echo wp_kses_post( $thumbnail ); ?>
                                                </td>
                                                <td class="name column-name has-row-actions column-primary"
                                                    data-colname="Name">
                                                    <figure class="thumb-info">
														<?php echo wp_kses_post( $thumbnail ); ?>
                                                    </figure>
                                                    <div class="info">
                                                        <strong>
                                                            <a href="<?php echo esc_url( $product_edit ); ?>"
                                                               class="row-title">
																<?php echo esc_html( $product_name ); ?>
                                                            </a>
                                                        </strong>
                                                        <br>
                                                        <div class="row-actions">
                                                    <span class="edit">
                                                        <a href="<?php echo esc_url( $product_edit ); ?>"
                                                           target="_blank"
                                                           aria-label="Edit “<?php echo esc_attr( $product_name ); ?>”">
                                                            Edit
                                                        </a> |
                                                    </span>
                                                            <span class="delete">
                                                        <a href="#"
                                                           data-id="<?php echo esc_attr( $product_id ); ?>"
                                                           class="delete aria-button-if-js"
                                                           aria-label="Delete “<?php echo esc_attr( $product_name ); ?>”"
                                                           role="button">
                                                            Delete
                                                        </a> |
                                                    </span>
                                                            <span class="view">
                                                        <a href="<?php echo esc_url( $product_permalink ); ?>"
                                                           aria-label="View “<?php echo esc_attr( $product_name ); ?>” archive"
                                                           target="_blank">
                                                            View
                                                        </a>
                                                    </span>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="toggle-row">
                                                        <span class="screen-reader-text">Show more details</span>
                                                    </button>
                                                </td>
                                                <td class="slug column-slug" data-colname="slug">
													<?php echo esc_html( $product_slug ); ?>
                                                </td>
                                                <td class="count column-count" data-colname="Count">
													<?php echo count( $list_bundle ); ?>
                                                </td>
                                            </tr>
										<?php
										endwhile;

										wp_reset_postdata();
									}
									?>
                                    </tbody>
                                </table>
                                <div class="tablenav bottom">
                                    <div class="tablenav-pages">
                                        <span class="displaying-num">
                                            <?php
											/* translators: %s: number */
											printf(
												esc_html__( '%s items', 'ovic-bundle' ), // %s will be a number eventually, but must be a string for now.
												$count
											);
											?>
                                        </span>
                                        <span class="pagination-links">
                                            <?php
											$next_disable = '';
											$prev_disable = ' disabled';
											$max_page     = $products->max_num_pages;
											$next_page    = intval( $paged ) + 1;
											$prev_page    = ( $paged > 1 ) ? intval( $paged ) - 1 : 0;
											if ( $next_page > $max_page ) {
												$next_disable = ' disabled';
											}
											if ( $paged > 1 ) {
												$prev_disable = '';
											}
											?>
                                            <a class="tablenav-pages-navspan button<?php echo esc_attr( $prev_disable ); ?>"
                                               href="<?php echo esc_url( get_pagenum_link( $prev_page ) ); ?>">
                                                <span class="screen-reader-text">Prev page</span>
                                                <span aria-hidden="true">‹</span>
                                            </a>
                                            <span id="table-paging" class="paging-input">
                                                <span class="tablenav-paging-text">
                                                    <?php
													printf(
														'%s %s <span class="total-pages">%s</span>',
														$paged,
														esc_html__( 'of', 'ovic-bundle' ),
														$max_page
													);
													?>
                                                </span>
                                            </span>
                                            <a class="next-page button<?php echo esc_attr( $next_disable ); ?>"
                                               href="<?php echo esc_url( get_pagenum_link( $next_page ) ); ?>">
                                                <span class="screen-reader-text">Next page</span>
                                                <span aria-hidden="true">›</span>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			<?php
		}

		public function license_content()
		{
			?>
            <div id="dashboard-license" class="dashboard-license tab-panel">
				<?php do_action( 'ovic_license_ovic-product-bundle_page' ); ?>
            </div>
			<?php
		}

		function bundle_enqueue_scripts()
		{
			wp_register_style( 'ovic_bundle-frontend', OVIC_BUNDLE_URI . 'assets/css/frontend.css' );
			wp_register_script( 'ovic_bundle-frontend', OVIC_BUNDLE_URI . 'assets/js/frontend.js', array( 'jquery' ), OVIC_BUNDLE_VERSION, true );
		}

		function bundle_admin_enqueue_scripts()
		{
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';
			if ( $screen_id == 'ovic-plugins_page_ovic-product-bundle' ) {
				wp_enqueue_style( 'ovic_bundle-dashboard', OVIC_BUNDLE_URI . 'assets/css/dashboard.css' );
			}
			if ( in_array( $screen_id, wc_get_screen_ids() ) ) {
				$ajax_url = add_query_arg(
					array(
						'action'             => 'woocommerce_json_search_products',
						'ovic_bundle_search' => '1',
					),
					admin_url( 'admin-ajax.php' )
				);
				wp_enqueue_style( 'ovic_bundle-backend', OVIC_BUNDLE_URI . 'assets/css/backend.css' );
				wp_enqueue_script( 'dragarrange', OVIC_BUNDLE_URI . 'assets/js/drag-arrange.js', array(), OVIC_BUNDLE_VERSION, true );
				wp_enqueue_script( 'accounting', OVIC_BUNDLE_URI . 'assets/js/accounting.js', array(), OVIC_BUNDLE_VERSION, true );
				wp_enqueue_script( 'ovic_bundle-backend', OVIC_BUNDLE_URI . 'assets/js/backend.js', array( 'jquery', 'accounting', 'dragarrange' ), OVIC_BUNDLE_VERSION, true );
				wp_localize_script( 'ovic_bundle-backend', 'ovic_bundle_vars', array(
						'ovic_bundle_nonce'        => wp_create_nonce( 'ovic_bundle_nonce' ),
						'security'                 => wp_create_nonce( 'search-products' ),
						'url'                      => $ajax_url,
						'limit'                    => get_option( '_ovic_search_limit', '5' ),
						'price_decimals'           => wc_get_price_decimals(),
						'price_thousand_separator' => wc_get_price_thousand_separator(),
						'price_decimal_separator'  => wc_get_price_decimal_separator(),
					)
				);
			}
		}

		function ovic_bundle_custom_data_callback()
		{
			if ( isset( $_POST['ovic_bundle_ids'] ) ) {
				if ( !isset( $_POST['ovic_bundle_nonce'] ) || !wp_verify_nonce( $_POST['ovic_bundle_nonce'], 'ovic_bundle_nonce' ) ) {
					die( 'Permissions check failed' );
				}
				if ( !isset( $_SESSION ) ) {
					session_start();
				}
				$_SESSION['ovic_bundle_ids'] = self::ovic_bundle_clean_ids( $_POST['ovic_bundle_ids'] );
			}
			wp_die();
		}

		function ovic_bundle_cart_contents_count( $count )
		{
			$cart_contents = WC()->cart->cart_contents;
			$bundled_items = 0;
			foreach ( $cart_contents as $cart_item_key => $cart_item ) {
				if ( !empty( $cart_item['ovic_bundle_parent_id'] ) ) {
					$bundled_items += $cart_item['quantity'];
				}
			}

			return intval( $count - $bundled_items );
		}

		function ovic_bundle_cart_item_name( $name, $item )
		{
			if ( isset( $item['ovic_bundle_parent_id'] ) && !empty( $item['ovic_bundle_parent_id'] ) ) {
				if ( ( strpos( $name, '</a>' ) !== false ) && ( get_option( '_ovic_bundle_link', 'yes' ) == 'yes' ) ) {
					return '<a href="' . get_permalink( $item['ovic_bundle_parent_id'] ) . '">' . get_the_title( $item['ovic_bundle_parent_id'] ) . '</a> &rarr; ' . $name;
				} else {
					return get_the_title( $item['ovic_bundle_parent_id'] ) . ' &rarr; ' . strip_tags( $name );
				}
			} else {
				return $name;
			}
		}

		function ovic_bundle_update_cart_item_quantity( $cart_item_key, $quantity = 0 )
		{
			if ( !empty( WC()->cart->cart_contents[$cart_item_key] ) && ( isset( WC()->cart->cart_contents[$cart_item_key]['ovic_bundle_keys'] ) ) ) {
				if ( $quantity <= 0 ) {
					$quantity = 0;
				} else {
					$quantity = WC()->cart->cart_contents[$cart_item_key]['quantity'];
				}
				foreach ( WC()->cart->cart_contents[$cart_item_key]['ovic_bundle_keys'] as $ovic_bundle_key ) {
					WC()->cart->set_quantity( $ovic_bundle_key, $quantity * ( WC()->cart->cart_contents[$ovic_bundle_key]['ovic_bundle_qty'] ? WC()->cart->cart_contents[$ovic_bundle_key]['ovic_bundle_qty'] : 1 ), false );
				}
			}
		}

		function ovic_bundle_cart_item_removed( $cart_item_key, $cart )
		{
			if ( isset( $cart->removed_cart_contents[$cart_item_key]['ovic_bundle_keys'] ) ) {
				$ovic_bundle_keys = $cart->removed_cart_contents[$cart_item_key]['ovic_bundle_keys'];
				foreach ( $ovic_bundle_keys as $ovic_bundle_key ) {
					unset( $cart->cart_contents[$ovic_bundle_key] );
				}
			}
		}

		function ovic_bundle_ajax_added_to_cart( $product_id )
		{
			if ( isset( $_POST['ovic_bundle_ids'] ) ) {
				$ovic_bundle_ids = $_POST['ovic_bundle_ids'];
				add_filter( 'woocommerce_add_cart_item_data',
					function ( $cart_item_data ) use ( $ovic_bundle_ids, $product_id ) {
						$terms        = get_the_terms( $product_id, 'product_type' );
						$product_type = !empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';
						if ( $product_type == 'simple' ) {
							$cart_item_data['ovic_bundle_ids'] = $ovic_bundle_ids;
						}

						return $cart_item_data;
					}
				);
			}
		}

		function ovic_bundle_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data )
		{
			if ( isset( $cart_item_data['ovic_bundle_ids'] ) && ( $cart_item_data['ovic_bundle_ids'] != '' ) ) {
				$items = explode( ',', $cart_item_data['ovic_bundle_ids'] );
				if ( is_array( $items ) && ( count( $items ) > 0 ) ) {
					// add child products
					foreach ( $items as $item ) {
						$ovic_bundle_item     = explode( '/', $item );
						$ovic_bundle_item_id  = absint( isset( $ovic_bundle_item[0] ) ? $ovic_bundle_item[0] : 0 );
						$ovic_bundle_item_qty = absint( isset( $ovic_bundle_item[1] ) ? $ovic_bundle_item[1] : 1 );
						if ( ( $ovic_bundle_item_id > 0 ) && ( $ovic_bundle_item_qty > 0 ) ) {
							$ovic_bundle_item_variation_id = 0;
							$ovic_bundle_item_variation    = array();
							// ensure we don't add a variation to the cart directly by variation ID
							if ( 'product_variation' === get_post_type( $ovic_bundle_item_id ) ) {
								$ovic_bundle_item_variation_id      = $ovic_bundle_item_id;
								$ovic_bundle_item_id                = wp_get_post_parent_id( $ovic_bundle_item_variation_id );
								$ovic_bundle_item_variation_product = wc_get_product( $ovic_bundle_item_variation_id );
								$ovic_bundle_item_variation         = $ovic_bundle_item_variation_product->get_attributes();
							}
							$ovic_bundle_product = wc_get_product( $ovic_bundle_item_id );
							if ( $ovic_bundle_product ) {
								// set price zero for child product
								if ( !$ovic_bundle_product->is_type( 'subscription' ) ) {
									$ovic_bundle_product->set_price( 0 );
								}
								// add to cart
								$ovic_bundle_product_qty = $ovic_bundle_item_qty * $quantity;
								$ovic_bundle_cart_id     = WC()->cart->generate_cart_id( $ovic_bundle_item_id, $ovic_bundle_item_variation_id, $ovic_bundle_item_variation, array(
										'ovic_bundle_parent_id'  => $product_id,
										'ovic_bundle_parent_key' => $cart_item_key,
										'ovic_bundle_qty'        => $ovic_bundle_item_qty,
									)
								);
								$ovic_bundle_item_key    = WC()->cart->find_product_in_cart( $ovic_bundle_cart_id );
								if ( !$ovic_bundle_item_key ) {
									$ovic_bundle_item_key                            = $ovic_bundle_cart_id;
									WC()->cart->cart_contents[$ovic_bundle_item_key] = array(
										'product_id'             => $ovic_bundle_item_id,
										'variation_id'           => $ovic_bundle_item_variation_id,
										'variation'              => $ovic_bundle_item_variation,
										'quantity'               => $ovic_bundle_product_qty,
										'data'                   => $ovic_bundle_product,
										'ovic_bundle_parent_id'  => $product_id,
										'ovic_bundle_parent_key' => $cart_item_key,
										'ovic_bundle_qty'        => $ovic_bundle_item_qty,
									);
								} else {
									WC()->cart->cart_contents[$ovic_bundle_item_key]['quantity'] += $ovic_bundle_product_qty;
								}
								WC()->cart->cart_contents[$cart_item_key]['ovic_bundle_keys'][] = $ovic_bundle_item_key;
							}
						}
					}
				}
			}
		}

		function ovic_bundle_add_cart_item( $cart_item )
		{
			if ( isset( $cart_item['ovic_bundle_parent_key'] ) ) {
				$cart_item['data']->price = 0;
			}

			return $cart_item;
		}

		function ovic_bundle_add_cart_item_data( $cart_item_data, $product_id )
		{
			$ovic_bundle_ids = filter_input( INPUT_POST, 'ovic_bundle_ids' );
			$terms           = get_the_terms( $product_id, 'product_type' );
			$product_type    = !empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';
			if ( $product_type == 'simple' && $ovic_bundle_ids ) {
				$cart_item_data['ovic_bundle_ids'] = $ovic_bundle_ids;
			}

			return $cart_item_data;
		}

		function ovic_bundle_item_visible( $visible, $item )
		{
			if ( isset( $item['ovic_bundle_parent_id'] ) ) {
				return false;
			} else {
				return $visible;
			}
		}

		function ovic_bundle_item_class( $class, $item )
		{
			if ( isset( $item['ovic_bundle_parent_id'] ) ) {
				$class .= ' ovic_bundle-cart-item ovic_bundle-cart-child ovic_bundle-item-child';
			} elseif ( isset( $item['ovic_bundle_ids'] ) ) {
				$class .= ' ovic_bundle-cart-item ovic_bundle-cart-parent ovic_bundle-item-parent';
			}

			return $class;
		}

		function ovic_bundle_display_item_meta( $html, $item )
		{
			if ( isset( $item['ovic_bundle_ids'] ) || isset( $item['ovic_bundle_parent_id'] ) ) {
				return '';
			} else {
				return $html;
			}
		}

		function ovic_bundle_order_items_meta_get_formatted( $formatted_meta )
		{
			foreach ( $formatted_meta as $key => $meta ) {
				if ( ( $meta['key'] == 'ovic_bundle_ids' ) || ( $meta['key'] == 'ovic_bundle_parent_id' ) ) {
					unset( $formatted_meta[$key] );
				}
			}

			return $formatted_meta;
		}

		function ovic_bundle_add_order_item_meta( $item, $cart_item_key, $values )
		{
			if ( isset( $values['ovic_bundle_parent_id'] ) ) {
				$item->update_meta_data( 'ovic_bundle_parent_id', $values['ovic_bundle_parent_id'] );
			}
			if ( isset( $values['ovic_bundle_ids'] ) ) {
				$item->update_meta_data( 'ovic_bundle_ids', $values['ovic_bundle_ids'] );
			}
		}

		function ovic_bundle_hidden_order_item_meta( $hidden )
		{
			return array_merge( $hidden, array( 'ovic_bundle_parent_id', 'ovic_bundle_ids' ) );
		}

		function ovic_bundle_before_order_item_meta( $item_id )
		{
			if ( ( $ovic_bundle_parent_id = wc_get_order_item_meta( $item_id, 'ovic_bundle_parent_id', true ) ) ) {
				echo sprintf( esc_html__( '(bundled in %s)', 'ovic-bundle' ), get_the_title( $ovic_bundle_parent_id ) );
			}
		}

		function ovic_bundle_get_cart_item_from_session( $cart_item, $item_session_values )
		{
			if ( isset( $item_session_values['ovic_bundle_ids'] ) && !empty( $item_session_values['ovic_bundle_ids'] ) ) {
				$cart_item['ovic_bundle_ids'] = $item_session_values['ovic_bundle_ids'];
			}
			if ( isset( $item_session_values['ovic_bundle_parent_id'] ) ) {
				$cart_item['ovic_bundle_parent_id']  = $item_session_values['ovic_bundle_parent_id'];
				$cart_item['ovic_bundle_parent_key'] = $item_session_values['ovic_bundle_parent_key'];
				$cart_item['ovic_bundle_qty']        = $item_session_values['ovic_bundle_qty'];
				if ( isset( $cart_item['data']->subscription_sign_up_fee ) ) {
					$cart_item['data']->subscription_sign_up_fee = 0;
				}
			}

			return $cart_item;
		}

		function ovic_bundle_cart_item_remove_link( $link, $cart_item_key )
		{
			if ( isset( WC()->cart->cart_contents[$cart_item_key]['ovic_bundle_parent_id'] ) ) {
				return '';
			}

			return $link;
		}

		function ovic_bundle_cart_item_quantity( $quantity, $cart_item_key, $cart_item )
		{
			if ( isset( $cart_item['ovic_bundle_parent_id'] ) ) {
				return $cart_item['quantity'];
			}

			return $quantity;
		}

		function ovic_bundle_get_price( $cart_item )
		{
			$product_id = $cart_item['product_id'];
			if ( $cart_item['variation_id'] > 0 ) {
				$product_id       = $cart_item['variation_id'];
				$variable_product = new WC_Product_Variation( $product_id );
				$price_sale       = $variable_product->get_price();
			} else {
				$bundle_product = wc_get_product( $product_id );
				$price_sale     = $bundle_product->get_price();
			}
			$price_sale = $price_sale * $cart_item['quantity'];
			if ( $cart_item['ovic_bundle_parent_id'] != $product_id ) {
				$bundle_ids = $this->ovic_bundle_get_items( $cart_item['ovic_bundle_parent_id'] );
				$key        = array_search( $product_id, array_column( $bundle_ids, 'id' ) );
				$price_sale = $price_sale - ( ( $bundle_ids[$key]['sale'] / 100 ) * $price_sale );
			}

			return wc_price( $price_sale );
		}

		function ovic_bundle_cart_item_price( $price, $cart_item )
		{
			if ( isset( $cart_item['ovic_bundle_parent_id'] ) ) {
				return $this->ovic_bundle_get_price( $cart_item );
			}

			return $price;
		}

		function ovic_bundle_cart_item_subtotal( $subtotal, $cart_item )
		{
			if ( isset( $cart_item['ovic_bundle_parent_id'] ) ) {
				$price = $this->ovic_bundle_get_price( $cart_item );
				if ( is_cart() )
					$price = '';

				return $price;
			}

			return $subtotal;
		}

		function ovic_bundle_search_products( $found_products )
		{
			if ( isset( $_GET['ovic_bundle_search'] ) ) {
				$html = '';
				if ( !empty( $found_products ) ) {
					$html .= '<ul>';
					foreach ( $found_products as $id => $name ) {
						$product = wc_get_product( $id );
						if ( !$product || !$product->is_in_stock() ) {
							continue;
						}
						if ( $product->is_type( 'variable' ) ) {
							$html .= '<li ' . ( !$product->is_in_stock() ? 'class="out-of-stock"' : '' ) . ' data-id="' . $product->get_id() . '" data-price="' . $product->get_variation_price( 'min' ) . '" data-price-max="' . $product->get_variation_price( 'max' ) . '" data-price-sale="' . $product->get_variation_price( 'min' ) . '"><span class="move"></span><span class="qty"></span><span class="sale"></span> <span class="name">' . $product->get_name() . '</span> (#' . $product->get_id() . ' - ' . $product->get_price_html() . ') <span class="remove">+</span></li>';
							// show all childs
							$childs = $product->get_children();
							if ( is_array( $childs ) && count( $childs ) > 0 ) {
								foreach ( $childs as $child ) {
									$product_child = wc_get_product( $child );
									$html          .= '<li ' . ( !$product_child->is_in_stock() ? 'class="out-of-stock"' : '' ) . ' data-id="' . $child . '" data-price="' . $product_child->get_price() . '" data-price-max="' . $product_child->get_price() . '" data-price-sale="' . $product_child->get_price() . '"><span class="move"></span><span class="qty"></span><span class="sale"></span> <span class="name">' . $product_child->get_name() . '</span> (#' . $product_child->get_id() . ' - ' . $product_child->get_price_html() . ') <span class="remove">+</span></li>';
								}
							}
						} else {
							$html .= '<li ' . ( !$product->is_in_stock() ? 'class="out-of-stock"' : '' ) . ' data-id="' . $product->get_id() . '" data-price="' . $product->get_price() . '" data-price-max="' . $product->get_price() . '" data-price-sale="' . $product->get_price() . '"><span class="move"></span><span class="qty"></span><span class="sale"></span> <span class="name">' . $product->get_name() . '</span> (#' . $product->get_id() . ' - ' . $product->get_price_html() . ') <span class="remove">+</span></li>';
						}
					}
					$html .= '</ul>';
				} else {
					$html = '<ul><span>' . sprintf( esc_html__( 'No results found for "%s"', 'ovic-bundle' ), $_GET['term'] ) . '</span></ul>';
				}

				return $html;
			} else {
				return $found_products;
			}
		}

		function ovic_bundle_product_data_tabs( $tabs )
		{
			$tabs['ovic_bundle'] = array(
				'label'  => esc_html__( 'Product Bundle', 'ovic-bundle' ),
				'target' => 'ovic_bundle_settings',
				'class'  => array( 'show_if_simple' ),
			);

			return $tabs;
		}

		function ovic_bundle_product_data_panels()
		{
			global $post;
			$post_id           = $post->ID;
			$ovic_bundle_items = get_post_meta( $post_id, 'ovic_bundle_ids', true );
			?>
            <div id='ovic_bundle_settings' class='panel woocommerce_options_panel ovic_bundle_table'>
                <table>
                    <tr>
                        <th>
							<?php esc_html_e( 'Search', 'ovic-bundle' ); ?> (
                            <a href="<?php echo admin_url( 'admin.php?page=ovic-product-bundle&tab=settings#search' ); ?>"
                               target="_blank">
								<?php esc_html_e( 'settings', 'ovic-bundle' ); ?>
                            </a>)
                        </th>
                        <td>
                            <div class="w100">
                                <span class="loading" id="ovic_bundle_loading">
                                    <?php esc_html_e( 'searching...', 'ovic-bundle' ); ?>
                                </span>
                                <input type="search" id="ovic_bundle_keyword"
                                       placeholder="<?php esc_html_e( 'Type any keyword to search', 'ovic-bundle' ); ?>"/>
                                <div id="ovic_bundle_results" class="ovic_bundle_results"></div>
                            </div>
                        </td>
                    </tr>
                    <tr class="ovic_bundle_tr_space">
                        <th><?php esc_html_e( 'Selected', 'ovic-bundle' ); ?></th>
                        <td>
                            <div class="w100">
                                <input type="hidden" id="ovic_bundle_ids" class="ovic_bundle_ids"
                                       name="ovic_bundle_ids"
                                       value="<?php echo esc_attr( $ovic_bundle_items ); ?>"
                                       readonly/>
                                <div id="ovic_bundle_selected" class="ovic_bundle_selected">
                                    <ul>
										<?php
										$ovic_bundle_price = 0;
										if ( $ovic_bundle_items ) {
											$ovic_bundle_items = explode( ',', $ovic_bundle_items );
											if ( is_array( $ovic_bundle_items ) && count( $ovic_bundle_items ) > 0 ) {
												foreach ( $ovic_bundle_items as $ovic_bundle_item ) {
													$ovic_bundle_item_arr  = explode( '/', $ovic_bundle_item );
													$ovic_bundle_item_id   = absint( isset( $ovic_bundle_item_arr[0] ) ? $ovic_bundle_item_arr[0] : 0 );
													$ovic_bundle_item_qty  = absint( isset( $ovic_bundle_item_arr[1] ) ? $ovic_bundle_item_arr[1] : 1 );
													$ovic_bundle_item_sale = absint( isset( $ovic_bundle_item_arr[2] ) ? $ovic_bundle_item_arr[2] : 0 );
													$ovic_bundle_product   = wc_get_product( $ovic_bundle_item_id );
													if ( !$ovic_bundle_product ) {
														continue;
													}
													$ovic_bundle_price_qty  = $ovic_bundle_product->get_price() * $ovic_bundle_item_qty;
													$ovic_bundle_price_sale = $ovic_bundle_price_qty - ( ( $ovic_bundle_item_sale / 100 ) * $ovic_bundle_price_qty );
													$ovic_bundle_price      += $ovic_bundle_price_sale;
													if ( $ovic_bundle_product->is_type( 'variable' ) ) {
														echo '<li ' . ( !$ovic_bundle_product->is_in_stock() ? 'class="out-of-stock"' : '' ) . ' data-id="' . $ovic_bundle_item_id . '" data-price="' . $ovic_bundle_product->get_variation_price( 'min' ) . '" data-price-max="' . $ovic_bundle_product->get_variation_price( 'max' ) . '" data-price-sale="' . $ovic_bundle_price_sale . '"><span class="move"></span><span class="qty"><input type="number" value="' . $ovic_bundle_item_qty . '" min="0"/></span><span class="sale"><input type="number" value="' . $ovic_bundle_item_sale . '" min="0" max="100"/>%</span>  <span class="name">' . $ovic_bundle_product->get_name() . '</span> (#' . $ovic_bundle_product->get_id() . ' - ' . $ovic_bundle_product->get_price_html() . ')<span class="remove">×</span></li>';
													} else {
														echo '<li ' . ( !$ovic_bundle_product->is_in_stock() ? 'class="out-of-stock"' : '' ) . ' data-id="' . $ovic_bundle_item_id . '" data-price="' . $ovic_bundle_product->get_price() . '" data-price-max="' . $ovic_bundle_product->get_price() . '" data-price-sale="' . $ovic_bundle_price_sale . '"><span class="move"></span><span class="qty"><input type="number" value="' . $ovic_bundle_item_qty . '" min="0"/></span><span class="sale"><input type="number" value="' . $ovic_bundle_item_sale . '" min="0" max="100"/>%</span> <span class="name">' . $ovic_bundle_product->get_name() . '</span> (#' . $ovic_bundle_product->get_id() . ' - ' . $ovic_bundle_product->get_price_html() . ')<span class="remove">×</span></li>';
													}
												}
											}
										}
										?>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="ovic_bundle_tr_space">
                        <th><?php echo esc_html__( 'Regular price', 'ovic-bundle' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></th>
                        <td>
                            <span id="ovic_bundle_regular_price"><?php echo esc_html( $ovic_bundle_price ); ?></span>
                        </td>
                    </tr>
                    <tr class="ovic_bundle_tr_space">
                        <th><?php esc_html_e( 'Optional products', 'ovic-bundle' ); ?></th>
                        <td style="font-style: italic">
                            <input id="ovic_bundle_optional_products" name="ovic_bundle_optional_products"
                                   type="checkbox" <?php echo( get_post_meta( $post_id, 'ovic_bundle_optional_products', true ) == 'on' ? 'checked' : '' ); ?>/> <?php esc_html_e( 'Buyer can change the quantity of bundled products?', 'ovic-bundle' ); ?>
                        </td>
                    </tr>
                    <tr class="ovic_bundle_tr_space">
                        <th><?php esc_html_e( 'Before text', 'ovic-bundle' ); ?></th>
                        <td>
                            <div class="w100">
                                <textarea name="ovic_bundle_before_text"
                                          placeholder="<?php esc_html_e( 'The text before bundled products', 'ovic-bundle' ); ?>"><?php echo stripslashes( get_post_meta( $post_id, 'ovic_bundle_before_text', true ) ); ?></textarea>
                            </div>
                        </td>
                    </tr>
                    <tr class="ovic_bundle_tr_space">
                        <th><?php esc_html_e( 'After text', 'ovic-bundle' ); ?></th>
                        <td>
                            <div class="w100">
                                <textarea name="ovic_bundle_after_text"
                                          placeholder="<?php esc_html_e( 'The text after bundled products', 'ovic-bundle' ); ?>"><?php echo stripslashes( get_post_meta( $post_id, 'ovic_bundle_after_text', true ) ); ?></textarea>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
			<?php
		}

		function ovic_bundle_save_option_field( $post_id )
		{
			if ( isset( $_POST['ovic_bundle_ids'] ) ) {
				update_post_meta( $post_id, 'ovic_bundle_ids', self::ovic_bundle_clean_ids( $_POST['ovic_bundle_ids'] ) );
			}
			if ( isset( $_POST['ovic_bundle_optional_products'] ) ) {
				update_post_meta( $post_id, 'ovic_bundle_optional_products', 'on' );
			} else {
				update_post_meta( $post_id, 'ovic_bundle_optional_products', 'off' );
			}
			if ( isset( $_POST['ovic_bundle_before_text'] ) && ( $_POST['ovic_bundle_before_text'] != '' ) ) {
				update_post_meta( $post_id, 'ovic_bundle_before_text', sanitize_textarea_field( $_POST['ovic_bundle_before_text'] ) );
			} else {
				delete_post_meta( $post_id, 'ovic_bundle_before_text' );
			}
			if ( isset( $_POST['ovic_bundle_after_text'] ) && ( $_POST['ovic_bundle_after_text'] != '' ) ) {
				update_post_meta( $post_id, 'ovic_bundle_after_text', sanitize_textarea_field( $_POST['ovic_bundle_after_text'] ) );
			} else {
				delete_post_meta( $post_id, 'ovic_bundle_after_text' );
			}
		}

		function ovic_bundle_add_to_cart_form()
		{
			global $product;
			$ovic_bundle_items = $this->ovic_bundle_get_items( $product->get_id() );
			if ( !empty( $ovic_bundle_items ) && $product->is_type( 'simple' ) ) {
				$this->ovic_bundle_show_items( $ovic_bundle_items );
			}
		}

		function ovic_bundle_add_to_cart_button()
		{
			add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'ovic_bundle_add_to_cart_ids' ), 10 );
			wc_get_template( 'single-product/add-to-cart/simple.php' );
			remove_action( 'woocommerce_before_add_to_cart_button', array( $this, 'ovic_bundle_add_to_cart_ids' ), 10 );
		}

		function ovic_bundle_add_to_cart_ids()
		{
			global $product;
			$ovic_bundle_ids = $product->get_id() . '/1/0,' . get_post_meta( $product->get_id(), 'ovic_bundle_ids', true );
			echo '<input name="ovic_bundle_ids" id="ovic_bundle_ids" type="hidden" value="' . $ovic_bundle_ids . '"/>';
		}

		function ovic_bundle_before_calculate_totals( $cart_object )
		{
			//  This is necessary for WC 3.0+
			if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
				return;
			}
			foreach ( $cart_object->get_cart() as $cart_item_key => $cart_item ) {
				// child product price
				if ( isset( $cart_item['ovic_bundle_parent_id'] ) && ( $cart_item['ovic_bundle_parent_id'] != '' ) ) {
					if ( !$cart_item['data']->is_type( 'subscription' ) ) {
						$cart_item['data']->set_price( 0 );
					}
				}
				// main product price
				if ( isset( $cart_item['ovic_bundle_ids'] ) && ( $cart_item['ovic_bundle_ids'] != '' ) && $cart_item['data']->is_type( 'simple' ) ) {
					$ovic_bundle_ids    = $this->ovic_bundle_get_items( $cart_item['product_id'] );
					$ovic_bundle_items  = explode( ',', $cart_item['ovic_bundle_ids'] );
					$ovic_bundle_price  = 0;
					$subscription_price = 0;
					$count              = 0;
					if ( is_array( $ovic_bundle_items ) && count( $ovic_bundle_items ) > 0 ) {
						foreach ( $ovic_bundle_items as $key => $ovic_bundle_item ) {
							$ovic_bundle_item_arr  = explode( '/', $ovic_bundle_item );
							$ovic_bundle_item_id   = absint( isset( $ovic_bundle_item_arr[0] ) ? $ovic_bundle_item_arr[0] : 0 );
							$ovic_bundle_item_qty  = absint( isset( $ovic_bundle_item_arr[1] ) ? $ovic_bundle_item_arr[1] : 0 );
							$ovic_bundle_item_sale = 0;
							if ( $key == 0 && $ovic_bundle_item_qty <= 0 ) {
								$ovic_bundle_item_qty = 1;
							}
							if ( $key > 0 && !empty( $ovic_bundle_ids ) && isset( $ovic_bundle_ids[$count]['sale'] ) ) {
								$ovic_bundle_item_sale = $ovic_bundle_ids[$count]['sale'];
								$count++;
							}
							$ovic_bundle_item_product = wc_get_product( $ovic_bundle_item_id );
							if ( !$ovic_bundle_item_product || ( $ovic_bundle_item_qty <= 0 ) ) {
								continue;
							}
							$ovic_bundle_price_qty  = $ovic_bundle_item_product->get_price() * $ovic_bundle_item_qty;
							$ovic_bundle_price_sale = $ovic_bundle_price_qty - ( ( $ovic_bundle_item_sale / 100 ) * $ovic_bundle_price_qty );
							$ovic_bundle_price      += $ovic_bundle_price_sale;
							if ( $ovic_bundle_item_product->is_type( 'subscription' ) ) {
								$subscription_price += $ovic_bundle_price_sale;
							}
						}
					}
					$cart_item['data']->set_price( floatval( $ovic_bundle_price - $subscription_price ) );
				}
			}
		}

		function ovic_bundle_cart_shipping_packages( $packages )
		{
			if ( !empty( $packages ) ) {
				foreach ( $packages as $package_key => $package ) {
					if ( !empty( $package['contents'] ) ) {
						foreach ( $package['contents'] as $cart_item_key => $cart_item ) {
							if ( isset( $cart_item['ovic_bundle_parent_id'] ) && ( $cart_item['ovic_bundle_parent_id'] != '' ) ) {
								unset( $packages[$package_key]['contents'][$cart_item_key] );
							}
						}
					}
				}
			}

			return $packages;
		}

		public function ovic_bundle_show_items( $ovic_bundle_items )
		{
			global $product;
			$product_id = $product->get_id();
			array_unshift( $ovic_bundle_items,
				array(
					'id'   => $product_id,
					'qty'  => 1,
					'sale' => 0,
				)
			);
			/* ENQUEUE SCRIPT */
			wp_enqueue_style( 'ovic_bundle-frontend' );
			wp_enqueue_script( 'ovic_bundle-frontend' );
			wp_localize_script( 'ovic_bundle-frontend', 'ovic_bundle_vars', array(
					'ajax_url'                 => admin_url( 'admin-ajax.php' ),
					'alert_selection'          => esc_html__( 'Please select some product options before adding this bundle to the cart.', 'ovic-bundle' ),
					'alert_empty'              => esc_html__( 'Please choose at least one product before adding this bundle to the cart.', 'ovic-bundle' ),
					'bundle_price_text'        => get_option( '_ovic_bundle_price_text', 'Bundle price:' ),
					'bundle_price_save_text'   => get_option( '_ovic_bundle_price_save_text', 'You save:' ),
					'change_image'             => get_option( '_ovic_bundle_change_image', 'yes' ),
					'price_format'             => get_woocommerce_price_format(),
					'price_decimals'           => wc_get_price_decimals(),
					'price_thousand_separator' => wc_get_price_thousand_separator(),
					'price_decimal_separator'  => wc_get_price_decimal_separator(),
					'currency_symbol'          => get_woocommerce_currency_symbol(),
					'ovic_bundle_nonce'        => wp_create_nonce( 'ovic_bundle_nonce' ),
				)
			);
			/* CONTENT */
			echo '<div id="ovic_bundle_wrap" class="ovic_bundle-wrap">';
			do_action( 'ovic_bundle_before_table', $product );
			if ( $ovic_bundle_before_text = apply_filters( 'ovic_bundle_before_text', get_post_meta( $product_id, 'ovic_bundle_before_text', true ), $product_id ) ) {
				echo '<div id="ovic_bundle_before_text" class="ovic_bundle-before-text ovic_bundle-text">' . do_shortcode( stripslashes( $ovic_bundle_before_text ) ) . '</div>';
			}
			$_ovic_bundle_thumb     = get_option( '_ovic_bundle_thumb', 'yes' );
			$_ovic_bundle_qty       = get_option( '_ovic_bundle_qty', 'yes' );
			$_ovic_bundle_price     = get_option( '_ovic_bundle_price', 'html' );
			$_ovic_bundle_discount  = get_option( '_ovic_bundle_discount', 'yes' );
			$_ovic_optional_product = get_post_meta( $product_id, 'ovic_bundle_optional_products', true );
			?>
            <table id="ovic_bundle_products" cellspacing="0" class="ovic_bundle-table ovic_bundle-products">
                <thead>
                <tr>
					<?php if ( $_ovic_optional_product == 'on' ) { ?>
                        <th class="manage-column check-column"></th>
					<?php } ?>
					<?php if ( $_ovic_bundle_thumb != 'no' ) { ?>
                        <th class="manage-column column-thumb"></th>
					<?php } ?>
                    <th class="manage-column column-name column-primary"><?php echo esc_html__( 'Products', 'ovic-bundle' ); ?></th>
					<?php if ( ( $_ovic_bundle_qty == 'yes' ) && $_ovic_optional_product == 'on' ) { ?>
                        <th class="manage-column column-qty"><?php echo esc_html__( 'Qty', 'ovic-bundle' ); ?></th>
					<?php } ?>
					<?php if ( $_ovic_bundle_price != 'no' ) { ?>
                        <th class="manage-column column-price"><?php echo esc_html__( 'Price', 'ovic-bundle' ); ?></th>
					<?php } ?>
					<?php if ( $_ovic_bundle_discount != 'no' ) { ?>
                        <th class="manage-column column-discount"><?php echo esc_html__( 'Discount', 'ovic-bundle' ); ?></th>
					<?php } ?>
                </tr>
                </thead>
                <tbody>
				<?php if ( !empty( $ovic_bundle_items ) ): ?>
					<?php foreach ( $ovic_bundle_items as $key => $ovic_bundle_item ) {
						$ovic_bundle_product = wc_get_product( $ovic_bundle_item['id'] );
						if ( !$ovic_bundle_product ) {
							continue;
						}
						?>
                        <tr class="ovic_bundle-product"
                            data-id="<?php echo esc_attr( $ovic_bundle_product->is_type( 'variable' ) || !$product->is_in_stock() ? 0 : $ovic_bundle_item['id'] ); ?>"
                            data-price="<?php echo esc_attr( !$product->is_in_stock() ? 0 : $ovic_bundle_product->get_price() ); ?>"
                            data-qty="<?php echo esc_attr( $ovic_bundle_item['qty'] ); ?>"
                            data-sale="<?php echo esc_attr( $ovic_bundle_item['sale'] ); ?>">
							<?php if ( $_ovic_optional_product == 'on' ) { ?>
                                <td class="ovic_bundle-check check-column">
                                    <label for="ovic_bundle-checkbox">
                                        <input type="checkbox" id="ovic_bundle-checkbox" class="input-text check"
                                               checked <?php if ( $ovic_bundle_item['id'] == $product_id ) echo 'disabled'; ?>/>
                                    </label>
                                </td>
							<?php } ?>
							<?php if ( $_ovic_bundle_thumb != 'no' ) { ?>
                                <td class="ovic_bundle-thumb column-thumb">
                                    <div class="thumb">
										<?php
										echo apply_filters( 'ovic_bundle_item_thumbnail',
											$ovic_bundle_product->get_image( array( 60, 60 ) ),
											$ovic_bundle_product
										);
										?>
                                    </div>
                                </td>
							<?php } ?>
                            <td class="ovic_bundle-title column-name">
								<?php
								do_action( 'ovic_bundle_before_item_name', $ovic_bundle_product );
								echo '<div class="ovic_bundle-title-inner">';
								if ( ( $_ovic_bundle_qty == 'yes' ) && $_ovic_optional_product != 'on' ) {
									echo apply_filters( 'ovic_bundle_text_qty', $ovic_bundle_item['qty'] . ' × ', $ovic_bundle_item['qty'], $ovic_bundle_product );
								}
								$ovic_bundle_item_name = '';
								if ( $ovic_bundle_product->is_visible() && ( get_option( '_ovic_bundle_link', 'yes' ) == 'yes' ) ) {
									$ovic_bundle_item_name .= '<a href="' . $ovic_bundle_product->get_permalink() . '" target="_blank">';
								}
								if ( $ovic_bundle_product->is_in_stock() ) {
									$ovic_bundle_item_name .= $ovic_bundle_product->get_name();
								} else {
									$ovic_bundle_item_name .= '<s>' . $ovic_bundle_product->get_name() . '</s>';
								}
								if ( $ovic_bundle_product->is_visible() && ( get_option( '_ovic_bundle_link', 'yes' ) == 'yes' ) ) {
									$ovic_bundle_item_name .= '</a>';
								}
								if ( isset( $ovic_bundle_item['sale'] ) && $ovic_bundle_item['sale'] > 0 ) {
									$ovic_bundle_item_name .= '<div class="ovic_bundle-sale">-' . $ovic_bundle_item['sale'] . '%</div>';
								}
								echo apply_filters( 'ovic_bundle_item_name', $ovic_bundle_item_name, $ovic_bundle_product );
								echo '</div>';
								do_action( 'ovic_bundle_after_item_name', $ovic_bundle_product );
								?>
                            </td>
							<?php if ( ( $_ovic_bundle_qty == 'yes' ) && $_ovic_optional_product == 'on' ) {
								$max_qty = null;
								$min_qty = ( $ovic_bundle_item['id'] == $product_id ) ? 1 : 0;
								if ( ( $ovic_bundle_product->get_backorders() == 'no' ) && ( $ovic_bundle_product->get_stock_status() != 'onbackorder' ) && is_int( $ovic_bundle_product->get_stock_quantity() ) ) {
									$max_qty = $ovic_bundle_product->get_stock_quantity();
								}
								?>
                                <td class="ovic_bundle-qty column-qty">
									<?php
									do_action( 'woocommerce_before_add_to_cart_quantity' );
									woocommerce_quantity_input(
										array(
											'input_value' => $ovic_bundle_item['qty'],
											'min_value'   => $min_qty,
											'max_value'   => $max_qty,
										),
										$ovic_bundle_product
									);
									do_action( 'woocommerce_after_add_to_cart_quantity' );
									?>
                                </td>
								<?php
							}
							?>
							<?php if ( $_ovic_bundle_price != 'no' ) { ?>
                                <td class="ovic_bundle-price column-price">
                                    <div class="price">
										<?php
										$ovic_bundle_price = '';
										switch ( $_ovic_bundle_price ) {
											case 'price':
												$ovic_bundle_price = wc_price( $ovic_bundle_product->get_price() );
												break;
											case 'html':
												$ovic_bundle_price = $ovic_bundle_product->get_price_html();
												break;
											case 'subtotal':
												$ovic_bundle_price = wc_price( $ovic_bundle_product->get_price() * $ovic_bundle_item['qty'] );
												break;
										}
										echo apply_filters( 'ovic_bundle_item_price', $ovic_bundle_price, $ovic_bundle_product );
										?>
                                    </div>
                                </td>
							<?php } ?>
							<?php if ( $_ovic_bundle_discount != 'no' ) { ?>
                                <td class="ovic_bundle-total column-discount">
                                    <div class="discount">
										<?php
										$ovic_bundle_price = $ovic_bundle_product->get_price() * $ovic_bundle_item['qty'];
										$ovic_bundle_price = wc_price( $ovic_bundle_price - ( ( $ovic_bundle_item['sale'] / 100 ) * $ovic_bundle_price ) );
										echo apply_filters( 'ovic_bundle_item_total', $ovic_bundle_price, $ovic_bundle_product );
										?>
                                    </div>
                                </td>
							<?php } ?>
                        </tr>
					<?php } ?>
				<?php endif; ?>
                </tbody>
            </table>
			<?php
			if ( $ovic_bundle_after_text = apply_filters( 'ovic_bundle_after_text', get_post_meta( $product_id, 'ovic_bundle_after_text', true ), $product_id ) ) {
				echo '<div id="ovic_bundle_after_text" class="ovic_bundle-after-text ovic_bundle-text">' . do_shortcode( stripslashes( $ovic_bundle_after_text ) ) . '</div>';
			}
			do_action( 'ovic_bundle_after_table', $product );
			?>
            <div class="footer-bundle">
                <div class="ovic-bundle-subtotal">
                    <div id="ovic_bundle_total" class="ovic_bundle-total ovic_bundle-text"></div>
                    <div id="ovic_bundle_total_save" class="ovic_bundle-total-save ovic_bundle-text"></div>
                </div>
				<?php $this->ovic_bundle_add_to_cart_button(); ?>
            </div>
			<?php
			echo '</div>';
		}

		function ovic_bundle_get_items( $product_id )
		{
			$ovic_bundle_arr = array();
			if ( ( $ovic_bundle_ids = get_post_meta( $product_id, 'ovic_bundle_ids', true ) ) ) {
				$ovic_bundle_items = explode( ',', $ovic_bundle_ids );
				if ( is_array( $ovic_bundle_items ) && count( $ovic_bundle_items ) > 0 ) {
					foreach ( $ovic_bundle_items as $ovic_bundle_item ) {
						$ovic_bundle_item_arr = explode( '/', $ovic_bundle_item );
						$ovic_bundle_arr[]    = array(
							'id'   => absint( isset( $ovic_bundle_item_arr[0] ) ? $ovic_bundle_item_arr[0] : 0 ),
							'qty'  => absint( isset( $ovic_bundle_item_arr[1] ) ? $ovic_bundle_item_arr[1] : 1 ),
							'sale' => absint( isset( $ovic_bundle_item_arr[2] ) ? $ovic_bundle_item_arr[2] : 0 ),
						);
					}
				}
			}
			if ( count( $ovic_bundle_arr ) > 0 ) {
				return $ovic_bundle_arr;
			} else {
				return false;
			}
		}

		function ovic_bundle_clean_ids( $ids )
		{
			$ids = preg_replace( '/[^,\/0-9]/', '', $ids );

			return $ids;
		}
	}

	new Ovic_Bundle_Woo();
}