<?php
if ( ! class_exists( 'Ovic_Attribute_Product_Meta' ) ) {
	class Ovic_Attribute_Product_Meta
	{
		public $plugin_uri;
		public $screen;
		public $taxonomy;
		public $meta_key = 'attribute_swatch';

		public function __construct()
		{
			include_once dirname( __FILE__ ) . '/product-attribute-term.php';
			$pagenow          = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
			$this->taxonomy   = isset( $_REQUEST['taxonomy'] ) ? $_REQUEST['taxonomy'] : '';
			$this->plugin_uri = trailingslashit( plugin_dir_url( __FILE__ ) );
			if ( strpos( $this->taxonomy, 'pa_' ) !== false ) {
				$attribute = $this->get_product_attribute( $this->taxonomy );
				if ( $attribute['type'] === 'box_style' ) {
					add_action( $this->taxonomy . '_add_form_fields', array( $this, 'add_attr_field' ) );
					add_action( $this->taxonomy . '_edit_form_fields', array( $this, 'edit_attr_field' ), 10, 2 );
					add_filter( 'manage_edit-' . $this->taxonomy . '_columns', array( $this, 'product_attr_columns' ) );
					add_filter( 'manage_' . $this->taxonomy . '_custom_column', array(
						$this,
						'product_attr_column'
					), 10, 3 );
				}
			}
			if ( strpos( $pagenow, 'product_attributes' ) !== false ) {
				add_filter( 'product_attributes_type_selector', array( $this, 'product_attributes_type_selector' ) );
				add_action( 'woocommerce_after_add_attribute_fields', array( $this, 'add_attribute_fields' ) );
				add_action( 'woocommerce_after_edit_attribute_fields', array( $this, 'edit_attribute_fields' ) );
				add_action( 'woocommerce_attribute_updated', array( $this, 'update_attribute' ), 10, 2 );
				add_action( 'woocommerce_attribute_added', array( $this, 'update_attribute' ), 10, 2 );
			}
			add_action( 'created_term', array( $this, 'product_field_save' ), 10, 3 );
			add_action( 'edit_term', array( $this, 'product_field_save' ), 10, 3 );
			add_action( 'dokan_product_option_terms', array( $this, 'dokan_option_terms' ), 10, 2 );
			add_action( 'woocommerce_product_option_terms', array( $this, 'product_option_terms' ), 10, 2 );
			add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array(
				$this,
				'wc_variation_attribute_options'
			), 99, 2 );
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
		}

		public function scripts()
		{
			if ( class_exists( 'WooCommerce' ) ) {
				wp_enqueue_style( 'product-attributes-swatches', $this->plugin_uri . 'product-attribute.css', array(), '1.0' );
				wp_enqueue_script( 'product-attributes-swatches', $this->plugin_uri . 'product-attribute.js', array(), '1.0', true );
			}
		}

		public function product_attributes_type_selector( $types )
		{
			$ovic_types = array(
				'box_style' => esc_html__( 'Box Style', 'ovic-toolkit' ),
			);

			return array_merge( $types, $ovic_types );
		}

		public function update_attribute( $id, $data )
		{
			if ( $data['attribute_type'] === 'box_style' ) {
				global $wpdb;
				$data['attribute_public'] = isset( $data['has_archives'] ) ? (int) $data['has_archives'] : 0;
				$data['attribute_size']   = isset( $_POST['attribute_size'] ) ? $_POST['attribute_size'] : '20x20';
				$row                      = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$wpdb->prefix}woocommerce_attribute_taxonomies' AND column_name = 'attribute_size'" );
				if ( empty( $row ) ) {
					$wpdb->query( "ALTER TABLE {$wpdb->prefix}woocommerce_attribute_taxonomies ADD attribute_size VARCHAR(20) NOT NULL DEFAULT '{$data['attribute_size']}'" );
				} else {
					$wpdb->update(
						$wpdb->prefix . 'woocommerce_attribute_taxonomies',
						$data,
						array( 'attribute_id' => $id )
					);
				}
			}
		}

		public function add_attribute_fields()
		{
			?>
            <div class="form-field" style="display: none;">
                <label><?php esc_html_e( 'Size Box', 'ovic-toolkit' ); ?></label>
                <input type="text" name="attribute_size" id="attribute_size" value="20x20">
                <p class="description"><?php esc_html_e( 'Determines Size in this attribute.{width}x{height}', 'ovic-toolkit' ); ?></p>
            </div>
            <script type="text/javascript">
                var _element = jQuery( '#attribute_type' );

                function show_size( val ) {
                    if ( val !== 'select' ) {
                        jQuery( '#attribute_size' ).closest( '.form-field' ).css( 'display', 'block' );
                    } else {
                        jQuery( '#attribute_size' ).closest( '.form-field' ).css( 'display', 'none' );
                    }
                }

                _element.on( 'change', function () {
                    show_size( jQuery( this ).val() );
                } );
                show_size( _element.val() );
            </script>
			<?php
		}

		public function edit_attribute_fields()
		{
			global $wpdb;
			$attribute_id = absint( $_GET['edit'] );
			$attribute    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_id = %s", $attribute_id ) );
			if ( is_wp_error( $attribute ) ) {
				return;
			}
			$attribute_size = isset( $attribute->attribute_size ) ? $attribute->attribute_size : '20x20';
			?>
            <tr class="form-field form-required" <?php if ( $attribute->attribute_type == 'select' ): ?> style="display: none;" <?php endif; ?>>
                <th scope="row" valign="top"><label><?php _e( 'Size Box', 'ovic-toolkit' ); ?></label></th>
                <td>
                    <input type="text" id="attribute_size" name="attribute_size"
                           value="<?php echo esc_attr( $attribute_size ); ?>">
                    <p class="description"><?php esc_html_e( 'Determines Size in this attribute.{width}x{height}', 'ovic-toolkit' ); ?></p>
                    <script type="text/javascript">
                        var _element = jQuery( '#attribute_type' );

                        function show_size( val ) {
                            if ( val !== 'select' ) {
                                jQuery( '#attribute_size' ).closest( '.form-field' ).css( 'display', 'table-row' );
                            } else {
                                jQuery( '#attribute_size' ).closest( '.form-field' ).css( 'display', 'none' );
                            }
                        }

                        _element.on( 'change', function () {
                            show_size( jQuery( this ).val() );
                        } );
                        show_size( _element.val() );
                    </script>
                </td>
            </tr>
			<?php
		}

		public function dokan_option_terms( $attribute_taxonomy, $i )
		{
			global $post, $thepostid;
			$taxonomy = 'pa_' . $attribute_taxonomy->attribute_name;
			if ( ! $thepostid ) {
				$thepostid = $post->ID;
			};
			if ( 'box_style' === $attribute_taxonomy->attribute_type ) : ?>
                <select multiple="multiple" style="width:100%"
                        data-placeholder="<?php esc_attr_e( 'Select terms', 'ovic-toolkit' ); ?>"
                        class="dokan_attribute_values dokan-select2" name="attribute_values[<?php echo $i; ?>][]">
					<?php
					$args      = array(
						'orderby'    => 'name',
						'hide_empty' => 0
					);
					$all_terms = get_terms( $taxonomy, apply_filters( 'dokan_product_attribute_terms', $args ) );
					if ( $all_terms ) {
						foreach ( $all_terms as $term ) {
							echo '<option value="' . esc_attr( $term->slug ) . '" ' . selected( has_term( absint( $term->term_id ), $taxonomy, $thepostid ), true, false ) . '>' . esc_attr( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) ) . '</option>';
						}
					}
					?>
                </select>
                <div class="dokan-pre-defined-attribute-btn-group">
                    <button class="dokan-btn dokan-btn-default plus dokan-select-all-attributes"><?php esc_html_e( 'Select all', 'ovic-toolkit' ); ?></button>
                    <button class="dokan-btn dokan-btn-default minus dokan-select-no-attributes"><?php esc_html_e( 'Select none', 'ovic-toolkit' ); ?></button>
                    <!-- <button class="dokan-btn dokan-btn-default fr plus dokan-add-new-attribute"><?php esc_html_e( 'Add new', 'ovic-toolkit' ); ?></button> -->
                </div>
			<?php
			endif;
		}

		public function product_option_terms( $attribute_taxonomy, $i )
		{
			global $post, $thepostid, $product_object;
			if ( 'box_style' === $attribute_taxonomy->attribute_type ) {
				$taxonomy = wc_attribute_taxonomy_name( $attribute_taxonomy->attribute_name );

				$product_id = $thepostid;

				if ( is_null( $thepostid ) && isset( $_POST['post_id'] ) ) {
					$product_id = absint( $_POST['post_id'] );
				}

				$args = array(
					'orderby'    => 'name',
					'hide_empty' => 0,
				);
				?>
                <select multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select terms', 'ovic-toolkit' ); ?>"
                        class="multiselect attribute_values wc-enhanced-select"
                        name="attribute_values[<?php echo $i; ?>][]">
					<?php
					$all_terms = get_terms( $taxonomy, apply_filters( 'woocommerce_product_attribute_terms', $args ) );
					if ( $all_terms ) :
						foreach ( $all_terms as $term ) :
							echo '<option value="' . esc_attr( $term->term_id ) . '" ' . selected( has_term( absint( $term->term_id ), $taxonomy, $product_id ), true, false ) . '>' . esc_attr( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) ) . '</option>';
						endforeach;
					endif;
					?>
                </select>
                <button class="button plus select_all_attributes"><?php esc_html_e( 'Select all', 'ovic-toolkit' ); ?></button>
                <button class="button minus select_no_attributes"><?php esc_html_e( 'Select none', 'ovic-toolkit' ); ?></button>
                <button class="button fr plus add_new_attribute"><?php esc_html_e( 'Add new', 'ovic-toolkit' ); ?></button>
				<?php
			}
		}

		//The field used when adding a new term to an attribute taxonomy
		public function add_attr_field()
		{
			$id_type  = "product_attribute_meta[{$this->meta_key}][type]";
			$id_color = "product_attribute_meta[{$this->meta_key}][color]";
			$id_photo = "product_attribute_meta[{$this->meta_key}][photo]";
			?>
            <div class="form-field term-display-type-wrap">
                <label for="display_type"><?php _e( 'Display type', 'ovic-toolkit' ); ?></label>
                <select name="<?php echo esc_attr( $id_type ); ?>" id="product_attribute_type_id">
                    <option value="-1"><?php echo esc_html__( 'None', 'ovic-toolkit' ); ?></option>
                    <option value="color"><?php echo esc_html__( 'Color', 'ovic-toolkit' ); ?></option>
                    <option value="photo" selected><?php echo esc_html__( 'Photo', 'ovic-toolkit' ); ?></option>
                    <option value="label"><?php echo esc_html__( 'Label', 'ovic-toolkit' ); ?></option>
                </select>
                <script type="text/javascript">
                    var _element = jQuery( '#product_attribute_type_id' );

                    function show_swatch( val ) {
                        jQuery( '.form-field.swatch:not(.' + val + ')' ).css( 'display', 'none' );
                        jQuery( '.form-field.swatch.' + val ).css( 'display', 'block' );
                    }

                    _element.on( 'change', function () {
                        show_swatch( jQuery( this ).val() );
                    } );
                    jQuery( document ).ready( function () {
                        show_swatch( _element.val() );
                    } );
                </script>
            </div>
            <div class="form-field term-display-type-wrap swatch color" style="display: none;">
                <label><?php _e( '', 'ovic-toolkit' ); ?></label>
                <input name="<?php echo esc_attr( $id_color ); ?>" id="product_attribute_color_id" value="">
                <script type="text/javascript">
                    window.addEventListener( 'load',
                        function ( ev ) {
                            if ( jQuery.fn.wpColorPicker ) {
                                jQuery( '#product_attribute_color_id' ).wpColorPicker();
                            }
                        }, false );
                </script>
            </div>
            <div class="form-field term-display-type-wrap swatch photo">
                <label><?php _e( 'Thumbnail', 'ovic-toolkit' ); ?></label>
                <div id="product_attribute_thumbnail" style="float: left; margin-right: 10px;">
                    <img src="<?php echo esc_url( wc_placeholder_img_src() ); ?>" width="60px" height="60px"/>
                </div>
                <div style="line-height: 60px;">
                    <input type="hidden"
                           id="product_attribute_thumbnail_id"
                           name="<?php echo esc_attr( $id_photo ); ?>"/>
                    <button type="button"
                            class="upload_image_button button"><?php _e( 'Upload/Add image', 'ovic-toolkit' ); ?></button>
                    <button type="button"
                            class="remove_image_button button"><?php _e( 'Remove image', 'ovic-toolkit' ); ?></button>
                </div>
                <script type="text/javascript">

                    // Only show the "remove image" button when needed
                    if ( !jQuery( '#product_attribute_thumbnail_id' ).val() ) {
                        jQuery( '.remove_image_button' ).hide();
                    }

                    // Uploading files
                    var file_frame;

                    jQuery( document ).on( 'click', '.upload_image_button', function ( event ) {

                        event.preventDefault();

                        // If the media frame already exists, reopen it.
                        if ( file_frame ) {
                            file_frame.open();
                            return;
                        }

                        // Create the media frame.
                        file_frame = wp.media.frames.downloadable_file = wp.media( {
                            title: '<?php _e( 'Choose an image', 'ovic-toolkit' ); ?>',
                            button: {
                                text: '<?php _e( 'Use image', 'ovic-toolkit' ); ?>'
                            },
                            multiple: false
                        } );

                        // When an image is selected, run a callback.
                        file_frame.on( 'select', function () {
                            var attachment           = file_frame.state().get( 'selection' ).first().toJSON();
                            var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

                            jQuery( '#product_attribute_thumbnail_id' ).val( attachment.id );
                            jQuery( '#product_attribute_thumbnail' ).find( 'img' ).attr( 'src', attachment_thumbnail.url );
                            jQuery( '.remove_image_button' ).show();
                        } );

                        // Finally, open the modal.
                        file_frame.open();
                    } );

                    jQuery( document ).on( 'click', '.remove_image_button', function () {
                        jQuery( '#product_attribute_thumbnail' ).find( 'img' ).attr( 'src', '<?php echo esc_js( wc_placeholder_img_src() ); ?>' );
                        jQuery( '#product_attribute_thumbnail_id' ).val( '' );
                        jQuery( '.remove_image_button' ).hide();
                        return false;
                    } );

                    jQuery( document ).ajaxComplete( function ( event, request, options ) {
                        if ( request && 4 === request.readyState && 200 === request.status
                            && options.data && 0 <= options.data.indexOf( 'action=add-tag' ) ) {

                            var res = wpAjax.parseAjaxResponse( request.responseXML, 'ajax-response' );
                            if ( !res || res.errors ) {
                                return;
                            }
                            // Clear Thumbnail fields on submit
                            jQuery( '#product_attribute_thumbnail' ).find( 'img' ).attr( 'src', '<?php echo esc_js( wc_placeholder_img_src() ); ?>' );
                            jQuery( '#product_attribute_thumbnail_id' ).val( '' );
                            jQuery( '.remove_image_button' ).hide();
                            // Clear Display type field on submit
                            jQuery( '#display_type' ).val( '' );
                            return;
                        }
                    } );

                </script>
                <div class="clear"></div>
            </div>
			<?php
		}

		//The field used when editing an existing proeuct attribute taxonomy term
		public function edit_attr_field( $term, $taxonomy )
		{
			$id_type     = "product_attribute_meta[{$this->meta_key}][type]";
			$id_color    = "product_attribute_meta[{$this->meta_key}][color]";
			$id_photo    = "product_attribute_meta[{$this->meta_key}][photo]";
			$swatch_term = new Ovic_Term( $this->meta_key, $term->term_id, $taxonomy, false );
			if ( $swatch_term->get_image_src() ) {
				$image = $swatch_term->get_image_src();
			} else {
				$image = wc_placeholder_img_src();
			}
			?>
            <tr class="form-field">
                <th scope="row" valign="top"><label><?php _e( 'Display type', 'ovic-toolkit' ); ?></label></th>
                <td>
                    <select name="<?php echo esc_attr( $id_type ); ?>" id="product_attribute_type_id">
                        <option value="-1" <?php echo selected( '-1', $swatch_term->get_type() ); ?>>
							<?php echo esc_html__( 'None', 'ovic-toolkit' ); ?>
                        </option>
                        <option value="color" <?php echo selected( 'color', $swatch_term->get_type() ); ?>>
							<?php echo esc_html__( 'Color', 'ovic-toolkit' ); ?>
                        </option>
                        <option value="photo" <?php echo selected( 'photo', $swatch_term->get_type() ); ?>>
							<?php echo esc_html__( 'Photo', 'ovic-toolkit' ); ?>
                        </option>
                        <option value="label" <?php echo selected( 'label', $swatch_term->get_type() ); ?>>
							<?php echo esc_html__( 'Label', 'ovic-toolkit' ); ?>
                        </option>
                    </select>
                    <script type="text/javascript">
                        var _element = jQuery( '#product_attribute_type_id' );

                        function show_swatch( val ) {
                            jQuery( '.form-field.swatch:not(.' + val + ')' ).css( 'display', 'none' );
                            jQuery( '.form-field.swatch.' + val ).css( 'display', 'table-row' );
                        }

                        _element.on( 'change', function () {
                            show_swatch( jQuery( this ).val() );
                        } );
                        jQuery( document ).ready( function () {
                            show_swatch( _element.val() );
                        } );
                    </script>
                </td>
            </tr>
            <tr class="form-field swatch color <?php echo $swatch_term->get_type() == 'color' ? 'active' : ''; ?>">
                <th scope="row" valign="top"><label><?php _e( '', 'ovic-toolkit' ); ?></label></th>
                <td>
                    <input name="<?php echo esc_attr( $id_color ); ?>" id="product_attribute_color_id"
                           value="<?php echo $swatch_term->get_color(); ?>">
                    <script type="text/javascript">
                        window.addEventListener( 'load',
                            function ( ev ) {
                                if ( jQuery.fn.wpColorPicker ) {
                                    jQuery( '#product_attribute_color_id' ).wpColorPicker();
                                }
                            }, false );
                    </script>
                </td>
            </tr>
            <tr class="form-field swatch photo <?php echo $swatch_term->get_type() == 'photo' ? 'active' : ''; ?>">
                <th scope="row" valign="top"><label><?php _e( '', 'ovic-toolkit' ); ?></label></th>
                <td>
                    <label><?php _e( 'Thumbnail', 'ovic-toolkit' ); ?></label>
                    <div id="product_attribute_thumbnail" style="float: left; margin-right: 10px;">
                        <img src="<?php echo esc_url( $image ); ?>" width="60px" height="60px"/>
                    </div>
                    <div style="line-height: 60px;">
                        <input type="hidden"
                               id="product_attribute_thumbnail_id"
                               name="<?php echo esc_attr( $id_photo ); ?>"
                               value="<?php echo $swatch_term->get_image_id(); ?>"/>
                        <button type="button"
                                class="upload_image_button button"><?php _e( 'Upload/Add image', 'ovic-toolkit' ); ?></button>
                        <button type="button"
                                class="remove_image_button button"><?php _e( 'Remove image', 'ovic-toolkit' ); ?></button>
                    </div>
                    <script type="text/javascript">

                        // Only show the "remove image" button when needed
                        if ( '0' === jQuery( '#<?php echo esc_attr( $id_photo ); ?>' ).val() ) {
                            jQuery( '.remove_image_button' ).hide();
                        }

                        // Uploading files
                        var file_frame;

                        jQuery( document ).on( 'click', '.upload_image_button', function ( event ) {

                            event.preventDefault();

                            // If the media frame already exists, reopen it.
                            if ( file_frame ) {
                                file_frame.open();
                                return;
                            }

                            // Create the media frame.
                            file_frame = wp.media.frames.downloadable_file = wp.media( {
                                title: '<?php _e( 'Choose an image', 'ovic-toolkit' ); ?>',
                                button: {
                                    text: '<?php _e( 'Use image', 'ovic-toolkit' ); ?>'
                                },
                                multiple: false
                            } );

                            // When an image is selected, run a callback.
                            file_frame.on( 'select', function () {
                                var attachment           = file_frame.state().get( 'selection' ).first().toJSON();
                                var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

                                jQuery( '#product_attribute_thumbnail_id' ).val( attachment.id );
                                jQuery( '#product_attribute_thumbnail' ).find( 'img' ).attr( 'src', attachment_thumbnail.url );
                                jQuery( '.remove_image_button' ).show();
                            } );

                            // Finally, open the modal.
                            file_frame.open();
                        } );

                        jQuery( document ).on( 'click', '.remove_image_button', function () {
                            jQuery( '#product_attribute_thumbnail' ).find( 'img' ).attr( 'src', '<?php echo esc_js( wc_placeholder_img_src() ); ?>' );
                            jQuery( '#product_attribute_thumbnail_id' ).val( '' );
                            jQuery( '.remove_image_button' ).hide();
                            return false;
                        } );

                    </script>
                    <div class="clear"></div>
                </td>
            </tr>
			<?php
		}

		//Saves the product attribute taxonomy term data
		public function product_field_save( $term_id, $tt_id, $taxonomy )
		{
			if ( isset( $_POST['product_attribute_meta'] ) ) {
				$metas = $_POST['product_attribute_meta'];
				if ( isset( $metas[ $this->meta_key ] ) ) {
					$data  = $metas[ $this->meta_key ];
					$photo = isset( $data['photo'] ) ? $data['photo'] : '';
					$color = isset( $data['color'] ) ? $data['color'] : '';
					$type  = isset( $data['type'] ) ? $data['type'] : '';
					update_term_meta( $term_id, $taxonomy . '_' . $this->meta_key . '_type', $type );
					update_term_meta( $term_id, $taxonomy . '_' . $this->meta_key . '_photo', $photo );
					update_term_meta( $term_id, $taxonomy . '_' . $this->meta_key . '_color', $color );
				}
			}
		}

		//Registers a column for this attribute taxonomy for this image
		public function product_attr_columns( $columns )
		{
			$new_columns = array();
			if ( isset( $columns['cb'] ) ) {
				$new_columns['cb'] = $columns['cb'];
				unset( $columns['cb'] );
			}
			$new_columns['thumb'] = __( 'Image', 'ovic-toolkit' );
			$columns              = array_merge( $new_columns, $columns );
			$columns['handle']    = '';

			return $columns;
		}

		//Renders the custom column as defined in product_attr_columns
		public function product_attr_column( $columns, $column, $id )
		{
			if ( 'thumb' === $column ) {
				$swatch_term = new Ovic_Term( $this->meta_key, $id, $this->taxonomy, false );
				$columns     .= $swatch_term->get_output();
			}
			if ( 'handle' === $column ) {
				$columns .= '<input type="hidden" name="term_id" value="' . esc_attr( $id ) . '" />';
			}

			return $columns;
		}

		/**
		 * Get attribute
		 * @return array
		 */
		public function wc_get_attribute_taxonomies()
		{
			global $woocommerce;
			if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
				return wc_get_attribute_taxonomies();
			} else {
				return $woocommerce->get_attribute_taxonomies();
			}
		}

		public function wc_variation_attribute_options( $html, $args )
		{
			// Get selected value.
			if ( false === $args['selected'] && $args['attribute'] && $args['product'] instanceof WC_Product ) {
				$selected_key     = 'attribute_' . sanitize_title( $args['attribute'] );
				$args['selected'] = isset( $_REQUEST[ $selected_key ] ) ? wc_clean( wp_unslash( $_REQUEST[ $selected_key ] ) ) : $args['product']->get_variation_default_attribute( $args['attribute'] ); // WPCS: input var ok, CSRF ok, sanitization ok.
			}
			$options               = $args['options'];
			$product               = $args['product'];
			$attribute             = $args['attribute'];
			$name                  = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
			$id                    = $args['id'] ? $args['id'] : sanitize_title( $attribute );
			$class                 = $args['class'];
			$show_option_none      = (bool) $args['show_option_none'];
			$show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __( 'Choose an option', 'ovic-toolkit' ); // We'll do our best to hide the placeholder, but we'll need to show something when resetting options.
			if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
				$attributes = $product->get_variation_attributes();
				$options    = $attributes[ $attribute ];
			}
			if ( ! empty( $options ) && taxonomy_exists( $attribute ) && $this->get_product_attribute( $attribute ) ) {
				$html               = '';
				$swatch_width       = 20;
				$swatch_height      = 20;
				$attribute_taxonomy = $this->get_product_attribute( $attribute );
				if ( isset( $attribute_taxonomy['size'] ) ) {
					$attribute_size = explode( 'x', $attribute_taxonomy['size'] );
					if ( count( $attribute_size ) == 2 ) {
						$swatch_width  = $attribute_size[0];
						$swatch_height = $attribute_size[1];
					}
				}
				$swatch_width  = apply_filters( 'ovic_attribute_swatch_width', $swatch_width );
				$swatch_height = apply_filters( 'ovic_attribute_swatch_height', $swatch_height );
				$html          .= '<select data-attributetype="' . $attribute_taxonomy['type'] . '" data-id="' . esc_attr( $id ) . '" class="attribute-select ' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . '" data-attribute_name="attribute_' . esc_attr( sanitize_title( $attribute ) ) . '" data-show_option_none="' . ( $show_option_none ? 'yes' : 'no' ) . '">';
				$html          .= '<option data-type="" data-' . esc_attr( $id ) . '="" value="">' . esc_html( $show_option_none_text ) . '</option>';
				// Get terms if this is a taxonomy - ordered. We need the names too.
				$terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );
				foreach ( $terms as $term ) {
					if ( in_array( $term->slug, $options ) ) {
						// For color attribute
						$data_type  = get_term_meta( $term->term_id, $term->taxonomy . '_attribute_swatch_type', true );
						$data_color = get_term_meta( $term->term_id, $term->taxonomy . '_attribute_swatch_color', true );
						$data_photo = get_term_meta( $term->term_id, $term->taxonomy . '_attribute_swatch_photo', true );
						$photo_url  = wp_get_attachment_image_url( $data_photo, array(
							$swatch_width,
							$swatch_height
						) );
						if ( ! $photo_url ) {
							$photo_url = wc_placeholder_img_src();
						}
						if ( $data_type == 'color' ) {
							$html .= '<option data-width="' . $swatch_width . '" data-height="' . $swatch_height . '" data-type="' . esc_attr( $data_type ) . '" data-' . esc_attr( $id ) . '="' . esc_attr( $data_color ) . '" value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $args['selected'] ), $term->slug, false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) ) . '</option>';
						} elseif ( $data_type == 'photo' ) {
							$html .= '<option data-width="' . $swatch_width . '" data-height="' . $swatch_height . '" data-type="' . esc_attr( $data_type ) . '" data-' . esc_attr( $id ) . '=" url(' . esc_url( $photo_url ) . ') " value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $args['selected'] ), $term->slug, false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) ) . '</option>';
						} elseif ( $data_type == 'label' ) {
							$html .= '<option data-width="' . $swatch_width . '" data-height="' . $swatch_height . '" data-type="' . esc_attr( $data_type ) . '" data-' . esc_attr( $id ) . '="" value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $args['selected'] ), $term->slug, false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) ) . '</option>';
						} else {
							$html .= '<option data-width="' . $swatch_width . '" data-height="' . $swatch_height . '" data-type="' . esc_attr( $data_type ) . '" data-' . esc_attr( $id ) . '="' . esc_attr( $term->slug ) . '" value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $args['selected'] ), $term->slug, false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) ) . '</option>';
						}
					}
				}
				$html .= '</select>';
				$html .= '<div class="data-val attribute-' . esc_attr( $id ) . '" data-attributetype="' . $attribute_taxonomy['type'] . '"></div>';
			}

			return $html;
		}

		public function get_product_attribute( $attribute )
		{
			global $wpdb;
			$product_attribute = array();
			$attribute_name    = str_replace( 'pa_', '', $attribute );
			$attribute         = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s", $attribute_name ) );
			if ( ! empty( $attribute ) ) {
				$product_attribute         = array(
					'id'           => intval( $attribute->attribute_id ),
					'name'         => $attribute->attribute_label,
					'slug'         => wc_attribute_taxonomy_name( $attribute->attribute_name ),
					'type'         => $attribute->attribute_type,
					'order_by'     => $attribute->attribute_orderby,
					'has_archives' => (bool) $attribute->attribute_public,
				);
				$product_attribute['size'] = isset( $attribute->attribute_size ) ? $attribute->attribute_size : '20x20';
			}

			return $product_attribute;
		}
	}

	new Ovic_Attribute_Product_Meta();
}