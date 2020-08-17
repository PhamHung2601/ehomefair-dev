<?php
/**
 * Handles taxonomies in admin
 *
 * @class    Ovic_Admin_Taxonomies
 * @version  2.3.10
 * @package  WooCommerce/Admin
 * @brand Class
 * @author   WooThemes
 */
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Ovic_Admin_Taxonomies class.
 */
if ( !class_exists( 'Ovic_Admin_Taxonomies' ) ) {
	/**
	 * Add Widgets.
	 */
	require_once dirname( __FILE__ ) . '/product-brand-widget.php';

	class Ovic_Admin_Taxonomies
	{
		/**
		 * Constructor.
		 */
		public function __construct()
		{
			add_action( 'woocommerce_after_register_taxonomy', array( $this, 'register_product_taxonomy' ) );
			// Category/term ordering
			add_action( 'create_term', array( $this, 'create_term' ), 5, 3 );
			add_action( 'delete_term', array( $this, 'delete_term' ), 5 );
			// Add form
			add_action( 'product_brand_add_form_fields', array( $this, 'add_brand_fields' ) );
			add_action( 'product_brand_edit_form_fields', array( $this, 'edit_brand_fields' ), 10 );
			add_action( 'created_term', array( $this, 'save_brand_fields' ), 10, 3 );
			add_action( 'edit_term', array( $this, 'save_brand_fields' ), 10, 3 );
			// Add columns
			add_filter( 'manage_edit-product_brand_columns', array( $this, 'product_brand_columns' ) );
			add_filter( 'manage_product_brand_custom_column', array( $this, 'product_brand_column' ), 10, 3 );
			// Add row actions.
			add_filter( 'product_brand_row_actions', array( $this, 'product_brand_row_actions' ), 10, 2 );
			add_filter( 'admin_init', array( $this, 'handle_product_brand_row_actions' ) );
			// Add brand permalink.
			add_action( 'current_screen', array( $this, 'conditonal_includes' ) );
			// Maintain hierarchy of terms
			add_filter( 'wp_terms_checklist_args', array( $this, 'disable_checked_ontop' ) );
		}

		function conditonal_includes()
		{
			$screen = get_current_screen();
			if ( in_array( $screen->id, array( 'options-permalink' ) ) ) {
				$this->permalink_settings_init();
				$this->permalink_settings_save();
			}
		}

		function permalink_settings_init()
		{
			// Add our settings
			add_settings_field(
				'ovic_taxonomy_brand_slug', // id
				__( 'Product brand base', 'ovic-toolkit' ), // setting title
				array( &$this, 'taxonomy_slug_input' ), // display callback
				'permalink', // settings page
				'optional'                                      // settings section
			);
		}

		function taxonomy_slug_input()
		{
			$permalinks = get_option( 'ovic_product_brand_permalinks' );
			$value      = 'product-brand';
			if ( isset( $permalinks['brand_rewrite_slug'] ) ) {
				$value = $permalinks['brand_rewrite_slug'];
			}
			?>
            <input name="ovic_taxonomy_brand_slug" type="text" class="regular-text code"
                   value="<?php echo esc_attr( $value ); ?>"
                   placeholder="<?php echo _x( 'product-brand', 'slug', 'ovic-toolkit' ) ?>"/>
			<?php
		}

		function permalink_settings_save()
		{
			if ( !is_admin() ) {
				return;
			}
			// We need to save the options ourselves; settings api does not trigger save for the permalinks page
			if ( isset( $_POST['permalink_structure'] ) || isset( $_POST['ovic_taxonomy_brand_slug'] ) ) {
				// Cat and tag bases
				$ovic_taxonomy_brand_slug = wc_clean( $_POST['ovic_taxonomy_brand_slug'] );
				$permalinks               = get_option( 'ovic_product_brand_permalinks' );
				if ( !$permalinks ) {
					$permalinks = array();
				}
				$permalinks['brand_rewrite_slug'] = untrailingslashit( $ovic_taxonomy_brand_slug );
				update_option( 'ovic_product_brand_permalinks', $permalinks );
			}
		}

		function register_product_taxonomy()
		{
			$permalinks = get_option( 'ovic_product_brand_permalinks' );
			register_taxonomy(
				'product_brand',
				array( 'product' ),
				array(
					'hierarchical'          => true,
					'update_count_callback' => '_wc_term_recount',
					'label'                 => __( 'Brands', 'ovic-toolkit' ),
					'labels'                => array(
						'name'              => __( 'Product brands', 'ovic-toolkit' ),
						'singular_name'     => __( 'Category', 'ovic-toolkit' ),
						'menu_name'         => _x( 'Brands', 'Admin menu name', 'ovic-toolkit' ),
						'search_items'      => __( 'Search brands', 'ovic-toolkit' ),
						'all_items'         => __( 'All brands', 'ovic-toolkit' ),
						'parent_item'       => __( 'Parent brand', 'ovic-toolkit' ),
						'parent_item_colon' => __( 'Parent brand:', 'ovic-toolkit' ),
						'edit_item'         => __( 'Edit brand', 'ovic-toolkit' ),
						'update_item'       => __( 'Update brand', 'ovic-toolkit' ),
						'add_new_item'      => __( 'Add new brand', 'ovic-toolkit' ),
						'new_item_name'     => __( 'New brand name', 'ovic-toolkit' ),
						'not_found'         => __( 'No brands found', 'ovic-toolkit' ),
					),
					'show_ui'               => true,
					'query_var'             => true,
					'capabilities'          => array(
						'manage_terms' => 'manage_product_terms',
						'edit_terms'   => 'edit_product_terms',
						'delete_terms' => 'delete_product_terms',
						'assign_terms' => 'assign_product_terms',
					),
					'rewrite'               => array(
						'slug'         => $permalinks['brand_rewrite_slug'],
						'with_front'   => false,
						'hierarchical' => true,
					),
				)
			);
		}

		/**
		 * Order term when created (put in position 0).
		 *
		 * @param mixed  $term_id
		 * @param mixed  $tt_id
		 * @param string $taxonomy
		 */
		public function create_term( $term_id, $tt_id = '', $taxonomy = '' )
		{
			if ( 'product_brand' != $taxonomy && !taxonomy_is_product_attribute( $taxonomy ) ) {
				return;
			}
			$meta_name = taxonomy_is_product_attribute( $taxonomy ) ? 'order_' . esc_attr( $taxonomy ) : 'order';
			update_term_meta( $term_id, $meta_name, 0 );
		}

		/**
		 * When a term is deleted, delete its meta.
		 *
		 * @param mixed $term_id
		 */
		public function delete_term( $term_id )
		{
			global $wpdb;
			$term_id = absint( $term_id );
			if ( $term_id && get_option( 'db_version' ) < 34370 ) {
				$wpdb->delete( $wpdb->woocommerce_termmeta, array( 'woocommerce_term_id' => $term_id ), array( '%d' ) );
			}
		}

		public function brand_script()
		{
			?>
            <script type="text/javascript">
                /* SELECT IMAGE */
                jQuery(document).on('click', '.upload_image_button', function (event) {

                    event.preventDefault();

                    var _file_frame,
                        _this   = jQuery(this),
                        _parent = _this.closest('.field-image-select'),
                        _input  = _parent.find('.product_brand_thumbnail_id'),
                        _img    = _parent.find('.product_brand_thumbnail');

                    // If the media frame already exists, reopen it.
                    if ( _file_frame ) {
                        _file_frame.open();
                        return;
                    }

                    // Create the media frame.
                    _file_frame = wp.media.frames.downloadable_file = wp.media({
                        title: 'Choose an image',
                        button: {
                            text: 'Use image'
                        },
                        multiple: false
                    });

                    // When an image is selected, run a callback.
                    _file_frame.on('select', function () {
                        var attachment           = _file_frame.state().get('selection').first().toJSON();
                        var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

                        _input.val(attachment.id);
                        _img.find('img').attr('src', attachment_thumbnail.url);
                        _parent.find('.remove_image_button').show();
                    });

                    // Finally, open the modal.
                    _file_frame.open();
                });
                $(document).on('click', '.remove_image_button', function (e) {
                    jQuery(this).closest('.field-image-select').find('img').attr('src', '<?php echo esc_js( wc_placeholder_img_src() ); ?>');
                    jQuery(this).closest('.field-image-select').find('.product_brand_thumbnail_id').val(0);
                    jQuery(this).closest('.field-image-select').find('.remove_image_button').hide();
                    e.preventDefault();
                });
                /* SELECT IMAGE */

                jQuery(document).ajaxComplete(function (event, request, options) {
                    if ( request && 4 === request.readyState && 200 === request.status
                        && options.data && 0 <= options.data.indexOf('action=add-tag') ) {

                        var res = wpAjax.parseAjaxResponse(request.responseXML, 'ajax-response');
                        if ( !res || res.errors ) {
                            return;
                        }
                        // Clear Thumbnail fields on submit
                        jQuery(event.target).find('.product_brand_thumbnail').find('img').attr('src', '<?php echo esc_js( wc_placeholder_img_src() ); ?>');
                        jQuery(event.target).find('.product_brand_thumbnail_id').val('');
                        jQuery(event.target).find('.remove_image_button').hide();
                        return;
                    }
                });

            </script>
			<?php
		}

		/**
		 * Category thumbnail fields.
		 */
		public function add_brand_fields()
		{
			$this->brand_script();
			?>
            <div class="form-field term-thumbnail-wrap">
                <label><?php _e( 'Brand Logo', 'ovic-toolkit' ); ?></label>
                <div class="field-image-select">
                    <div class="product_brand_thumbnail" style="float: left; margin-right: 10px;">
                        <img src="<?php echo esc_url( wc_placeholder_img_src() ); ?>" width="60px" height="60px"/>
                    </div>
                    <div style="line-height: 60px;">
                        <input type="hidden" class="product_brand_thumbnail_id" name="product_brand_logo_id"/>
                        <button type="button" class="upload_image_button button">
							<?php _e( 'Upload/Add image', 'ovic-toolkit' ); ?>
                        </button>
                        <button type="button" class="remove_image_button button" style="display: none;">
							<?php _e( 'Remove image', 'ovic-toolkit' ); ?>
                        </button>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="form-field term-thumbnail-wrap">
                <label><?php _e( 'Thumbnail', 'ovic-toolkit' ); ?></label>
                <div class="field-image-select">
                    <div class="product_brand_thumbnail" style="float: left; margin-right: 10px;">
                        <img src="<?php echo esc_url( wc_placeholder_img_src() ); ?>" width="60px" height="60px"/>
                    </div>
                    <div style="line-height: 60px;">
                        <input type="hidden" class="product_brand_thumbnail_id" name="product_brand_thumbnail_id"/>
                        <button type="button" class="upload_image_button button">
							<?php _e( 'Upload/Add image', 'ovic-toolkit' ); ?>
                        </button>
                        <button type="button" class="remove_image_button button" style="display: none;">
							<?php _e( 'Remove image', 'ovic-toolkit' ); ?>
                        </button>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
			<?php
		}

		/**
		 * Edit brand thumbnail field.
		 *
		 * @param mixed $term Term (brand) being edited
		 */
		public function edit_brand_fields( $term )
		{
			$logo_id         = absint( get_term_meta( $term->term_id, 'logo_id', true ) );
			$thumbnail_id    = absint( get_term_meta( $term->term_id, 'thumbnail_id', true ) );
			$logo_image      = $logo_id ? wp_get_attachment_thumb_url( $logo_id ) : wc_placeholder_img_src();
			$thumbnail_image = $thumbnail_id ? wp_get_attachment_thumb_url( $thumbnail_id ) : wc_placeholder_img_src();
			?>
            <tr class="form-field">
                <th scope="row" valign="top"><label><?php _e( 'Brand Logo', 'ovic-toolkit' ); ?></label></th>
                <td class="field-image-select">
                    <div class="product_brand_thumbnail" style="float: left; margin-right: 10px;">
                        <img src="<?php echo esc_url( $logo_image ); ?>" width="60px" height="60px"/>
                    </div>
                    <div style="line-height: 60px;">
                        <input type="hidden" class="product_brand_thumbnail_id" name="product_brand_logo_id"
                               value="<?php echo $logo_id; ?>"/>
                        <button type="button" class="upload_image_button button">
							<?php _e( 'Upload/Add image', 'ovic-toolkit' ); ?>
                        </button>
                        <button type="button" class="remove_image_button button">
							<?php _e( 'Remove image', 'ovic-toolkit' ); ?>
                        </button>
                    </div>
                    <div class="clear"></div>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row" valign="top"><label><?php _e( 'Thumbnail', 'ovic-toolkit' ); ?></label></th>
                <td class="field-image-select">
                    <div class="product_brand_thumbnail" style="float: left; margin-right: 10px;">
                        <img src="<?php echo esc_url( $thumbnail_image ); ?>" width="60px" height="60px"/>
                    </div>
                    <div style="line-height: 60px;">
                        <input type="hidden" class="product_brand_thumbnail_id" name="product_brand_thumbnail_id"
                               value="<?php echo $thumbnail_id; ?>"/>
                        <button type="button" class="upload_image_button button">
							<?php _e( 'Upload/Add image', 'ovic-toolkit' ); ?>
                        </button>
                        <button type="button" class="remove_image_button button">
							<?php _e( 'Remove image', 'ovic-toolkit' ); ?>
                        </button>
                    </div>
					<?php $this->brand_script(); ?>
                    <div class="clear"></div>
                </td>
            </tr>
			<?php
		}

		/**
		 * save_brand_fields function.
		 *
		 * @param mixed  $term_id Term ID being saved
		 * @param mixed  $tt_id
		 * @param string $taxonomy
		 */
		public function save_brand_fields( $term_id, $tt_id = '', $taxonomy = '' )
		{
			if ( 'product_brand' === $taxonomy ) {
				if ( isset( $_POST['product_brand_thumbnail_id'] ) )
					update_term_meta( $term_id, 'thumbnail_id', absint( $_POST['product_brand_thumbnail_id'] ) );
				if ( isset( $_POST['product_brand_logo_id'] ) )
					update_term_meta( $term_id, 'logo_id', absint( $_POST['product_brand_logo_id'] ) );
			}
		}

		/**
		 * Thumbnail column added to brand admin.
		 *
		 * @param mixed $columns
		 *
		 * @return array
		 */
		public function product_brand_columns( $columns )
		{
			$new_columns = array();
			if ( isset( $columns['cb'] ) ) {
				$new_columns['cb'] = $columns['cb'];
				unset( $columns['cb'] );
			}
			$new_columns['logo']  = __( 'Logo', 'ovic-toolkit' );
			$new_columns['thumb'] = __( 'Image', 'ovic-toolkit' );
			$columns              = array_merge( $new_columns, $columns );
			$columns['handle']    = '';

			return $columns;
		}

		/**
		 * Adjust row actions.
		 *
		 * @param array  $actions Array of actions.
		 * @param object $term Term object.
		 *
		 * @return array
		 */
		public function product_brand_row_actions( $actions, $term )
		{
			$default_brand_id = absint( get_option( 'default_product_brand', 0 ) );
			if ( $default_brand_id !== $term->term_id && current_user_can( 'edit_term', $term->term_id ) ) {
				$actions['make_default'] = sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					wp_nonce_url( 'edit-tags.php?action=make_default&amp;taxonomy=product_brand&amp;tag_ID=' . absint( $term->term_id ), 'make_default_' . absint( $term->term_id ) ),
					/* translators: %s: taxonomy term name */
					esc_attr( sprintf( __( 'Make &#8220;%s&#8221; the default brand', 'ovic-toolkit' ), $term->name ) ),
					__( 'Make default', 'ovic-toolkit' )
				);
			}

			return $actions;
		}

		/**
		 * Handle custom row actions.
		 */
		public function handle_product_brand_row_actions()
		{
			if ( isset( $_GET['action'], $_GET['tag_ID'], $_GET['_wpnonce'] ) && 'make_default' === $_GET['action'] ) {
				$make_default_id = absint( $_GET['tag_ID'] );
				if ( wp_verify_nonce( $_GET['_wpnonce'], 'make_default_' . $make_default_id ) && current_user_can( 'edit_term', $make_default_id ) ) {
					update_option( 'default_product_brand', $make_default_id );
				}
			}
		}

		/**
		 * Thumbnail column value added to brand admin.
		 *
		 * @param string $columns
		 * @param string $column
		 * @param int    $id
		 *
		 * @return string
		 */
		public function product_brand_column( $columns, $column, $id )
		{
			if ( 'thumb' === $column ) {
				// Prepend tooltip for default brand.
				$default_brand_id = absint( get_option( 'default_product_brand', 0 ) );
				if ( $default_brand_id === $id ) {
					$columns .= wc_help_tip( __( 'This is the default brand and it cannot be deleted. It will be automatically assigned to products with no brand.', 'ovic-toolkit' ) );
				}
				$thumbnail_id = get_term_meta( $id, 'thumbnail_id', true );
				if ( $thumbnail_id ) {
					$image = wp_get_attachment_thumb_url( $thumbnail_id );
				} else {
					$image = wc_placeholder_img_src();
				}
				// Prevent esc_url from breaking spaces in urls for image embeds. Ref: https://core.trac.wordpress.org/ticket/23605
				$image   = str_replace( ' ', '%20', $image );
				$columns .= '<img src="' . esc_url( $image ) . '" alt="' . esc_attr__( 'Thumbnail', 'ovic-toolkit' ) . '" class="wp-post-image" height="48" width="48" />';
			}
			if ( 'logo' === $column ) {
				// Prepend tooltip for default brand.
				$default_brand_id = absint( get_option( 'default_product_brand', 0 ) );
				if ( $default_brand_id === $id ) {
					$columns .= wc_help_tip( __( 'This is the default brand and it cannot be deleted. It will be automatically assigned to products with no brand.', 'ovic-toolkit' ) );
				}
				$logo_id = get_term_meta( $id, 'logo_id', true );
				if ( $logo_id ) {
					$image = wp_get_attachment_thumb_url( $logo_id );
				} else {
					$image = wc_placeholder_img_src();
				}
				// Prevent esc_url from breaking spaces in urls for image embeds. Ref: https://core.trac.wordpress.org/ticket/23605
				$image   = str_replace( ' ', '%20', $image );
				$columns .= '<img src="' . esc_url( $image ) . '" alt="' . esc_attr__( 'Logo', 'ovic-toolkit' ) . '" class="wp-post-image" height="48" width="48" />';
			}
			if ( 'handle' === $column ) {
				$columns .= '<input type="hidden" name="term_id" value="' . esc_attr( $id ) . '" />';
			}

			return $columns;
		}

		/**
		 * Maintain term hierarchy when editing a product.
		 *
		 * @param array $args
		 *
		 * @return array
		 */
		public function disable_checked_ontop( $args )
		{
			if ( !empty( $args['taxonomy'] ) && 'product_brand' === $args['taxonomy'] ) {
				$args['checked_ontop'] = false;
			}

			return $args;
		}
	}

	new Ovic_Admin_Taxonomies();
}
