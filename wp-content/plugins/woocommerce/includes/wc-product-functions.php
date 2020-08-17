<?php
/**
 * WooCommerce Product Functions
 *
 * Functions for product specific things.
 *
 * @package WooCommerce/Functions
 * @version 3.0.0
 */

use Automattic\Jetpack\Constants;

defined( 'ABSPATH' ) || exit;

/**
 * Standard way of retrieving products based on certain parameters.
 *
 * This function should be used for product retrieval so that we have a data agnostic
 * way to get a list of products.
 *
 * Args and usage: https://github.com/woocommerce/woocommerce/wiki/wc_get_products-and-WC_Product_Query
 *
 * @since  3.0.0
 * @param  array $args Array of args (above).
 * @return array|stdClass Number of pages and an array of product objects if
 *                             paginate is true, or just an array of values.
 */
function wc_get_products( $args ) {
	// Handle some BW compatibility arg names where wp_query args differ in naming.
	$map_legacy = array(
		'numberposts'    => 'limit',
		'post_status'    => 'status',
		'post_parent'    => 'parent',
		'posts_per_page' => 'limit',
		'paged'          => 'page',
	);

	foreach ( $map_legacy as $from => $to ) {
		if ( isset( $args[ $from ] ) ) {
			$args[ $to ] = $args[ $from ];
		}
	}

	$query = new WC_Product_Query( $args );
	return $query->get_products();
}

/**
 * Main function for returning products, uses the WC_Product_Factory class.
 *
 * This function should only be called after 'init' action is finished, as there might be taxonomies that are getting
 * registered during the init action.
 *
 * @since 2.2.0
 *
 * @param mixed $the_product Post object or post ID of the product.
 * @param array $deprecated Previously used to pass arguments to the factory, e.g. to force a type.
 * @return WC_Product|null|false
 */
function wc_get_product( $the_product = false, $deprecated = array() ) {
	if ( ! did_action( 'woocommerce_init' ) || ! did_action( 'woocommerce_after_register_taxonomy' ) || ! did_action( 'woocommerce_after_register_post_type' ) ) {
		/* translators: 1: wc_get_product 2: woocommerce_init 3: woocommerce_after_register_taxonomy 4: woocommerce_after_register_post_type */
		wc_doing_it_wrong( __FUNCTION__, sprintf( __( '%1$s should not be called before the %2$s, %3$s and %4$s actions have finished.', 'woocommerce' ), 'wc_get_product', 'woocommerce_init', 'woocommerce_after_register_taxonomy', 'woocommerce_after_register_post_type' ), '3.9' );
		return false;
	}
	if ( ! empty( $deprecated ) ) {
		wc_deprecated_argument( 'args', '3.0', 'Passing args to wc_get_product is deprecated. If you need to force a type, construct the product class directly.' );
	}
	return WC()->product_factory->get_product( $the_product, $deprecated );
}

/**
 * Get a product object.
 *
 * @see WC_Product_Factory::get_product_classname
 * @since 3.9.0
 * @param string $product_type Product type. If used an invalid type a WC_Product_Simple instance will be returned.
 * @param int    $product_id   Product ID.
 * @return WC_Product
 */
function wc_get_product_object( $product_type, $product_id = 0 ) {
	$classname = WC_Product_Factory::get_product_classname( $product_id, $product_type );

	return new $classname( $product_id );
}

/**
 * Returns whether or not SKUS are enabled.
 *
 * @return bool
 */
function wc_product_sku_enabled() {
	return apply_filters( 'wc_product_sku_enabled', true );
}

/**
 * Returns whether or not product weights are enabled.
 *
 * @return bool
 */
function wc_product_weight_enabled() {
	return apply_filters( 'wc_product_weight_enabled', true );
}

/**
 * Returns whether or not product dimensions (HxWxD) are enabled.
 *
 * @return bool
 */
function wc_product_dimensions_enabled() {
	return apply_filters( 'wc_product_dimensions_enabled', true );
}

/**
 * Clear transient cache for product data.
 *
 * @param int $post_id (default: 0) The product ID.
 */
function wc_delete_product_transients( $post_id = 0 ) {
	// Transient data to clear with a fixed name which may be stale after product updates.
	$transients_to_clear = array(
		'wc_products_onsale',
		'wc_featured_products',
		'wc_outofstock_count',
		'wc_low_stock_count',
	);

	foreach ( $transients_to_clear as $transient ) {
		delete_transient( $transient );
	}

	if ( $post_id > 0 ) {
		// Transient names that include an ID - since they are dynamic they cannot be cleaned in bulk without the ID.
		$post_transient_names = array(
			'wc_product_children_',
			'wc_var_prices_',
			'wc_related_',
			'wc_child_has_weight_',
			'wc_child_has_dimensions_',
		);

		foreach ( $post_transient_names as $transient ) {
			delete_transient( $transient . $post_id );
		}
	}

	// Increments the transient version to invalidate cache.
	WC_Cache_Helper::get_transient_version( 'product', true );

	do_action( 'woocommerce_delete_product_transients', $post_id );
}

/**
 * Function that returns an array containing the IDs of the products that are on sale.
 *
 * @since 2.0
 * @return array
 */
function wc_get_product_ids_on_sale() {
	// Load from cache.
	$product_ids_on_sale = get_transient( 'wc_products_onsale' );

	// Valid cache found.
	if ( false !== $product_ids_on_sale ) {
		return $product_ids_on_sale;
	}

	$data_store          = WC_Data_Store::load( 'product' );
	$on_sale_products    = $data_store->get_on_sale_products();
	$product_ids_on_sale = wp_parse_id_list( array_merge( wp_list_pluck( $on_sale_products, 'id' ), array_diff( wp_list_pluck( $on_sale_products, 'parent_id' ), array( 0 ) ) ) );

	set_transient( 'wc_products_onsale', $product_ids_on_sale, DAY_IN_SECONDS * 30 );

	return $product_ids_on_sale;
}

/**
 * Function that returns an array containing the IDs of the featured products.
 *
 * @since 2.1
 * @return array
 */
function wc_get_featured_product_ids() {
	// Load from cache.
	$featured_product_ids = get_transient( 'wc_featured_products' );

	// Valid cache found.
	if ( false !== $featured_product_ids ) {
		return $featured_product_ids;
	}

	$data_store           = WC_Data_Store::load( 'product' );
	$featured             = $data_store->get_featured_product_ids();
	$product_ids          = array_keys( $featured );
	$parent_ids           = array_values( array_filter( $featured ) );
	$featured_product_ids = array_unique( array_merge( $product_ids, $parent_ids ) );

	set_transient( 'wc_featured_products', $featured_product_ids, DAY_IN_SECONDS * 30 );

	return $featured_product_ids;
}

/**
 * Filter to allow product_cat in the permalinks for products.
 *
 * @param  string  $permalink The existing permalink URL.
 * @param  WP_Post $post WP_Post object.
 * @return string
 */
function wc_product_post_type_link( $permalink, $post ) {
	// Abort if post is not a product.
	if ( 'product' !== $post->post_type ) {
		return $permalink;
	}

	// Abort early if the placeholder rewrite tag isn't in the generated URL.
	if ( false === strpos( $permalink, '%' ) ) {
		return $permalink;
	}

	// Get the custom taxonomy terms in use by this post.
	$terms = get_the_terms( $post->ID, 'product_cat' );

	if ( ! empty( $terms ) ) {
		$terms           = wp_list_sort(
			$terms,
			array(
				'parent'  => 'DESC',
				'term_id' => 'ASC',
			)
		);
		$category_object = apply_filters( 'wc_product_post_type_link_product_cat', $terms[0], $terms, $post );
		$product_cat     = $category_object->slug;

		if ( $category_object->parent ) {
			$ancestors = get_ancestors( $category_object->term_id, 'product_cat' );
			foreach ( $ancestors as $ancestor ) {
				$ancestor_object = get_term( $ancestor, 'product_cat' );
				if ( apply_filters( 'woocommerce_product_post_type_link_parent_category_only', false ) ) {
					$product_cat = $ancestor_object->slug;
				} else {
					$product_cat = $ancestor_object->slug . '/' . $product_cat;
				}
			}
		}
	} else {
		// If no terms are assigned to this post, use a string instead (can't leave the placeholder there).
		$product_cat = _x( 'uncategorized', 'slug', 'woocommerce' );
	}

	$find = array(
		'%year%',
		'%monthnum%',
		'%day%',
		'%hour%',
		'%minute%',
		'%second%',
		'%post_id%',
		'%category%',
		'%product_cat%',
	);

	$replace = array(
		date_i18n( 'Y', strtotime( $post->post_date ) ),
		date_i18n( 'm', strtotime( $post->post_date ) ),
		date_i18n( 'd', strtotime( $post->post_date ) ),
		date_i18n( 'H', strtotime( $post->post_date ) ),
		date_i18n( 'i', strtotime( $post->post_date ) ),
		date_i18n( 's', strtotime( $post->post_date ) ),
		$post->ID,
		$product_cat,
		$product_cat,
	);

	$permalink = str_replace( $find, $replace, $permalink );

	return $permalink;
}
add_filter( 'post_type_link', 'wc_product_post_type_link', 10, 2 );

/**
 * Get the placeholder image URL either from media, or use the fallback image.
 *
 * @param string $size Thumbnail size to use.
 * @return string
 */
function wc_placeholder_img_src( $size = 'woocommerce_thumbnail' ) {
	$src               = WC()->plugin_url() . '/assets/images/placeholder.png';
	$placeholder_image = get_option( 'woocommerce_placeholder_image', 0 );

	if ( ! empty( $placeholder_image ) ) {
		if ( is_numeric( $placeholder_image ) ) {
			$image = wp_get_attachment_image_src( $placeholder_image, $size );

			if ( ! empty( $image[0] ) ) {
				$src = $image[0];
			}
		} else {
			$src = $placeholder_image;
		}
	}

	return apply_filters( 'woocommerce_placeholder_img_src', $src );
}

/**
 * Get the placeholder image.
 *
 * Uses wp_get_attachment_image if using an attachment ID @since 3.6.0 to handle responsiveness.
 *
 * @param string       $size Image size.
 * @param string|array $attr Optional. Attributes for the image markup. Default empty.
 * @return string
 */
function wc_placeholder_img( $size = 'woocommerce_thumbnail', $attr = '' ) {
	$dimensions        = wc_get_image_size( $size );
	$placeholder_image = get_option( 'woocommerce_placeholder_image', 0 );

	$default_attr = array(
		'class' => 'woocommerce-placeholder wp-post-image',
		'alt'   => __( 'Placeholder', 'woocommerce' ),
	);

	$attr = wp_parse_args( $attr, $default_attr );

	if ( wp_attachment_is_image( $placeholder_image ) ) {
		$image_html = wp_get_attachment_image(
			$placeholder_image,
			$size,
			false,
			$attr
		);
	} else {
		$image      = wc_placeholder_img_src( $size );
		$hwstring   = image_hwstring( $dimensions['width'], $dimensions['height'] );
		$attributes = array();

		foreach ( $attr as $name => $value ) {
			$attribute[] = esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
		}

		$image_html = '<img src="' . esc_url( $image ) . '" ' . $hwstring . implode( ' ', $attribute ) . '/>';
	}

	return apply_filters( 'woocommerce_placeholder_img', $image_html, $size, $dimensions );
}

/**
 * Variation Formatting.
 *
 * Gets a formatted version of variation data or item meta.
 *
 * @param array|WC_Product_Variation $variation Variation object.
 * @param bool                       $flat Should this be a flat list or HTML list? (default: false).
 * @param bool                       $include_names include attribute names/labels in the list.
 * @param bool                       $skip_attributes_in_name Do not list attributes already part of the variation name.
 * @return string
 */
function wc_get_formatted_variation( $variation, $flat = false, $include_names = true, $skip_attributes_in_name = false ) {
	$return = '';

	if ( is_a( $variation, 'WC_Product_Variation' ) ) {
		$variation_attributes = $variation->get_attributes();
		$product              = $variation;
		$variation_name       = $variation->get_name();
	} else {
		$product        = false;
		$variation_name = '';
		// Remove attribute_ prefix from names.
		$variation_attributes = array();
		if ( is_array( $variation ) ) {
			foreach ( $variation as $key => $value ) {
				$variation_attributes[ str_replace( 'attribute_', '', $key ) ] = $value;
			}
		}
	}

	$list_type = $include_names ? 'dl' : 'ul';

	if ( is_array( $variation_attributes ) ) {

		if ( ! $flat ) {
			$return = '<' . $list_type . ' class="variation">';
		}

		$variation_list = array();

		foreach ( $variation_attributes as $name => $value ) {
			// If this is a term slug, get the term's nice name.
			if ( taxonomy_exists( $name ) ) {
				$term = get_term_by( 'slug', $value, $name );
				if ( ! is_wp_error( $term ) && ! empty( $term->name ) ) {
					$value = $term->name;
				}
			}

			// Do not list attributes already part of the variation name.
			if ( '' === $value || ( $skip_attributes_in_name && wc_is_attribute_in_product_name( $value, $variation_name ) ) ) {
				continue;
			}

			if ( $include_names ) {
				if ( $flat ) {
					$variation_list[] = wc_attribute_label( $name, $product ) . ': ' . rawurldecode( $value );
				} else {
					$variation_list[] = '<dt>' . wc_attribute_label( $name, $product ) . ':</dt><dd>' . rawurldecode( $value ) . '</dd>';
				}
			} else {
				if ( $flat ) {
					$variation_list[] = rawurldecode( $value );
				} else {
					$variation_list[] = '<li>' . rawurldecode( $value ) . '</li>';
				}
			}
		}

		if ( $flat ) {
			$return .= implode( ', ', $variation_list );
		} else {
			$return .= implode( '', $variation_list );
		}

		if ( ! $flat ) {
			$return .= '</' . $list_type . '>';
		}
	}
	return $return;
}

/**
 * Function which handles the start and end of scheduled sales via cron.
 */
function wc_scheduled_sales() {
	$data_store = WC_Data_Store::load( 'product' );

	// Sales which are due to start.
	$product_ids = $data_store->get_starting_sales();
	if ( $product_ids ) {
		do_action( 'wc_before_products_starting_sales', $product_ids );
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( $product ) {
				$sale_price = $product->get_sale_price();

				if ( $sale_price ) {
					$product->set_price( $sale_price );
					$product->set_date_on_sale_from( '' );
				} else {
					$product->set_date_on_sale_to( '' );
					$product->set_date_on_sale_from( '' );
				}

				$product->save();
			}
		}
		do_action( 'wc_after_products_starting_sales', $product_ids );

		WC_Cache_Helper::get_transient_version( 'product', true );
		delete_transient( 'wc_products_onsale' );
	}

	// Sales which are due to end.
	$product_ids = $data_store->get_ending_sales();
	if ( $product_ids ) {
		do_action( 'wc_before_products_ending_sales', $product_ids );
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( $product ) {
				$regular_price = $product->get_regular_price();
				$product->set_price( $regular_price );
				$product->set_sale_price( '' );
				$product->set_date_on_sale_to( '' );
				$product->set_date_on_sale_from( '' );
				$product->save();
			}
		}
		do_action( 'wc_after_products_ending_sales', $product_ids );

		WC_Cache_Helper::get_transient_version( 'product', true );
		delete_transient( 'wc_products_onsale' );
	}
}
add_action( 'woocommerce_scheduled_sales', 'wc_scheduled_sales' );

/**
 * Get attachment image attributes.
 *
 * @param array $attr Image attributes.
 * @return array
 */
function wc_get_attachment_image_attributes( $attr ) {
	if ( isset( $attr['src'] ) && strstr( $attr['src'], 'woocommerce_uploads/' ) ) {
		$attr['src'] = wc_placeholder_img_src();

		if ( isset( $attr['srcset'] ) ) {
			$attr['srcset'] = '';
		}
	}
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'wc_get_attachment_image_attributes' );


/**
 * Prepare attachment for JavaScript.
 *
 * @param array $response JS version of a attachment post object.
 * @return array
 */
function wc_prepare_attachment_for_js( $response ) {

	if ( isset( $response['url'] ) && strstr( $response['url'], 'woocommerce_uploads/' ) ) {
		$response['full']['url'] = wc_placeholder_img_src();
		if ( isset( $response['sizes'] ) ) {
			foreach ( $response['sizes'] as $size => $value ) {
				$response['sizes'][ $size ]['url'] = wc_placeholder_img_src();
			}
		}
	}

	return $response;
}
add_filter( 'wp_prepare_attachment_for_js', 'wc_prepare_attachment_for_js' );

/**
 * Track product views.
 */
function wc_track_product_view() {
	if ( ! is_singular( 'product' ) || ! is_active_widget( false, false, 'woocommerce_recently_viewed_products', true ) ) {
		return;
	}

	global $post;

	if ( empty( $_COOKIE['woocommerce_recently_viewed'] ) ) { // @codingStandardsIgnoreLine.
		$viewed_products = array();
	} else {
		$viewed_products = wp_parse_id_list( (array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) ); // @codingStandardsIgnoreLine.
	}

	// Unset if already in viewed products list.
	$keys = array_flip( $viewed_products );

	if ( isset( $keys[ $post->ID ] ) ) {
		unset( $viewed_products[ $keys[ $post->ID ] ] );
	}

	$viewed_products[] = $post->ID;

	if ( count( $viewed_products ) > 15 ) {
		array_shift( $viewed_products );
	}

	// Store for session only.
	wc_setcookie( 'woocommerce_recently_viewed', implode( '|', $viewed_products ) );
}

add_action( 'template_redirect', 'wc_track_product_view', 20 );

/**
 * Get product types.
 *
 * @since 2.2
 * @return array
 */
function wc_get_product_types() {
	return (array) apply_filters(
		'product_type_selector',
		array(
			'simple'   => __( 'Simple product', 'woocommerce' ),
			'grouped'  => __( 'Grouped product', 'woocommerce' ),
			'external' => __( 'External/Affiliate product', 'woocommerce' ),
			'variable' => __( 'Variable product', 'woocommerce' ),
		)
	);
}

/**
 * Check if product sku is unique.
 *
 * @since 2.2
 * @param int    $product_id Product ID.
 * @param string $sku Product SKU.
 * @return bool
 */
function wc_product_has_unique_sku( $product_id, $sku ) {
	$data_store = WC_Data_Store::load( 'product' );
	$sku_found  = $data_store->is_existing_sku( $product_id, $sku );

	if ( apply_filters( 'wc_product_has_unique_sku', $sku_found, $product_id, $sku ) ) {
		return false;
	}

	return true;
}

/**
 * Force a unique SKU.
 *
 * @since  3.0.0
 * @param  integer $product_id Product ID.
 */
function wc_product_force_unique_sku( $product_id ) {
	$product     = wc_get_product( $product_id );
	$current_sku = $product ? $product->get_sku( 'edit' ) : '';

	if ( $current_sku ) {
		try {
			$new_sku = wc_product_generate_unique_sku( $product_id, $current_sku );

			if ( $current_sku !== $new_sku ) {
				$product->set_sku( $new_sku );
				$product->save();
			}
		} catch ( Exception $e ) {} // @codingStandardsIgnoreLine.
	}
}

/**
 * Recursively appends a suffix until a unique SKU is found.
 *
 * @since  3.0.0
 * @param  integer $product_id Product ID.
 * @param  string  $sku Product SKU.
 * @param  integer $index An optional index that can be added to the product SKU.
 * @return string
 */
function wc_product_generate_unique_sku( $product_id, $sku, $index = 0 ) {
	$generated_sku = 0 < $index ? $sku . '-' . $index : $sku;

	if ( ! wc_product_has_unique_sku( $product_id, $generated_sku ) ) {
		$generated_sku = wc_product_generate_unique_sku( $product_id, $sku, ( $index + 1 ) );
	}

	return $generated_sku;
}

/**
 * Get product ID by SKU.
 *
 * @since  2.3.0
 * @param  string $sku Product SKU.
 * @return int
 */
function wc_get_product_id_by_sku( $sku ) {
	$data_store = WC_Data_Store::load( 'product' );
	return $data_store->get_product_id_by_sku( $sku );
}

/**
 * Get attibutes/data for an individual variation from the database and maintain it's integrity.
 *
 * @since  2.4.0
 * @param  int $variation_id Variation ID.
 * @return array
 */
function wc_get_product_variation_attributes( $variation_id ) {
	// Build variation data from meta.
	$all_meta                = get_post_meta( $variation_id );
	$parent_id               = wp_get_post_parent_id( $variation_id );
	$parent_attributes       = array_filter( (array) get_post_meta( $parent_id, '_product_attributes', true ) );
	$found_parent_attributes = array();
	$variation_attributes    = array();

	// Compare to parent variable product attributes and ensure they match.
	foreach ( $parent_attributes as $attribute_name => $options ) {
		if ( ! empty( $options['is_variation'] ) ) {
			$attribute                 = 'attribute_' . sanitize_title( $attribute_name );
			$found_parent_attributes[] = $attribute;
			if ( ! array_key_exists( $attribute, $variation_attributes ) ) {
				$variation_attributes[ $attribute ] = ''; // Add it - 'any' will be asumed.
			}
		}
	}

	// Get the variation attributes from meta.
	foreach ( $all_meta as $name => $value ) {
		// Only look at valid attribute meta, and also compare variation level attributes and remove any which do not exist at parent level.
		if ( 0 !== strpos( $name, 'attribute_' ) || ! in_array( $name, $found_parent_attributes, true ) ) {
			unset( $variation_attributes[ $name ] );
			continue;
		}
		/**
		 * Pre 2.4 handling where 'slugs' were saved instead of the full text attribute.
		 * Attempt to get full version of the text attribute from the parent.
		 */
		if ( sanitize_title( $value[0] ) === $value[0] && version_compare( get_post_meta( $parent_id, '_product_version', true ), '2.4.0', '<' ) ) {
			foreach ( $parent_attributes as $attribute ) {
				if ( 'attribute_' . sanitize_title( $attribute['name'] ) !== $name ) {
					continue;
				}
				$text_attributes = wc_get_text_attributes( $attribute['value'] );

				foreach ( $text_attributes as $text_attribute ) {
					if ( sanitize_title( $text_attribute ) === $value[0] ) {
						$value[0] = $text_attribute;
						break;
					}
				}
			}
		}

		$variation_attributes[ $name ] = $value[0];
	}

	return $variation_attributes;
}

/**
 * Get all product cats for a product by ID, including hierarchy
 *
 * @since  2.5.0
 * @param  int $product_id Product ID.
 * @return array
 */
function wc_get_product_cat_ids( $product_id ) {
	$product_cats = wc_get_product_term_ids( $product_id, 'product_cat' );

	foreach ( $product_cats as $product_cat ) {
		$product_cats = array_merge( $product_cats, get_ancestors( $product_cat, 'product_cat' ) );
	}

	return $product_cats;
}

/**
 * Gets data about an attachment, such as alt text and captions.
 *
 * @since 2.6.0
 *
 * @param int|null        $attachment_id Attachment ID.
 * @param WC_Product|bool $product WC_Product object.
 *
 * @return array
 */
function wc_get_product_attachment_props( $attachment_id = null, $product = false ) {
	$props      = array(
		'title'   => '',
		'caption' => '',
		'url'     => '',
		'alt'     => '',
		'src'     => '',
		'srcset'  => false,
		'sizes'   => false,
	);
	$attachment = get_post( $attachment_id );

	if ( $attachment && 'attachment' === $attachment->post_type ) {
		$props['title']   = wp_strip_all_tags( $attachment->post_title );
		$props['caption'] = wp_strip_all_tags( $attachment->post_excerpt );
		$props['url']     = wp_get_attachment_url( $attachment_id );

		// Alt text.
		$alt_text = array( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ), $props['caption'], wp_strip_all_tags( $attachment->post_title ) );

		if ( $product && $product instanceof WC_Product ) {
			$alt_text[] = wp_strip_all_tags( get_the_title( $product->get_id() ) );
		}

		$alt_text     = array_filter( $alt_text );
		$props['alt'] = isset( $alt_text[0] ) ? $alt_text[0] : '';

		// Large version.
		$full_size           = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) );
		$src                 = wp_get_attachment_image_src( $attachment_id, $full_size );
		$props['full_src']   = $src[0];
		$props['full_src_w'] = $src[1];
		$props['full_src_h'] = $src[2];

		// Gallery thumbnail.
		$gallery_thumbnail                = wc_get_image_size( 'gallery_thumbnail' );
		$gallery_thumbnail_size           = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] ) );
		$src                              = wp_get_attachment_image_src( $attachment_id, $gallery_thumbnail_size );
		$props['gallery_thumbnail_src']   = $src[0];
		$props['gallery_thumbnail_src_w'] = $src[1];
		$props['gallery_thumbnail_src_h'] = $src[2];

		// Thumbnail version.
		$thumbnail_size       = apply_filters( 'woocommerce_thumbnail_size', 'woocommerce_thumbnail' );
		$src                  = wp_get_attachment_image_src( $attachment_id, $thumbnail_size );
		$props['thumb_src']   = $src[0];
		$props['thumb_src_w'] = $src[1];
		$props['thumb_src_h'] = $src[2];

		// Image source.
		$image_size      = apply_filters( 'woocommerce_gallery_image_size', 'woocommerce_single' );
		$src             = wp_get_attachment_image_src( $attachment_id, $image_size );
		$props['src']    = $src[0];
		$props['src_w']  = $src[1];
		$props['src_h']  = $src[2];
		$props['srcset'] = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $attachment_id, $image_size ) : false;
		$props['sizes']  = function_exists( 'wp_get_attachment_image_sizes' ) ? wp_get_attachment_image_sizes( $attachment_id, $image_size ) : false;
	}
	return $props;
}

/**
 * Get product visibility options.
 *
 * @since 3.0.0
 * @return array
 */
function wc_get_product_visibility_options() {
	return apply_filters(
		'woocommerce_product_visibility_options',
		array(
			'visible' => __( 'Shop and search results', 'woocommerce' ),
			'catalog' => __( 'Shop only', 'woocommerce' ),
			'search'  => __( 'Search results only', 'woocommerce' ),
			'hidden'  => __( 'Hidden', 'woocommerce' ),
		)
	);
}

/**
 * Get product tax class options.
 *
 * @since 3.0.0
 * @return array
 */
function wc_get_product_tax_class_options() {
	$tax_classes           = WC_Tax::get_tax_classes();
	$tax_class_options     = array();
	$tax_class_options[''] = __( 'Standard', 'woocommerce' );

	if ( ! empty( $tax_classes ) ) {
		foreach ( $tax_classes as $class ) {
			$tax_class_options[ sanitize_title( $class ) ] = $class;
		}
	}
	return $tax_class_options;
}

/**
 * Get stock status options.
 *
 * @since 3.0.0
 * @return array
 */
function wc_get_product_stock_status_options() {
	return apply_filters(
		'woocommerce_product_stock_status_options',
		array(
			'instock'     => __( 'In stock', 'woocommerce' ),
			'outofstock'  => __( 'Out of stock', 'woocommerce' ),
			'onbackorder' => __( 'On backorder', 'woocommerce' ),
		)
	);
}

/**
 * Get backorder options.
 *
 * @since 3.0.0
 * @return array
 */
function wc_get_product_backorder_options() {
	return array(
		'no'     => __( 'Do not allow', 'woocommerce' ),
		'notify' => __( 'Allow, but notify customer', 'woocommerce' ),
		'yes'    => __( 'Allow', 'woocommerce' ),
	);
}

/**
 * Get related products based on product category and tags.
 *
 * @since  3.0.0
 * @param  int   $product_id  Product ID.
 * @param  int   $limit       Limit of results.
 * @param  array $exclude_ids Exclude IDs from the results.
 * @return array
 */
function wc_get_related_products( $product_id, $limit = 5, $exclude_ids = array() ) {

	$product_id     = absint( $product_id );
	$limit          = $limit >= -1 ? $limit : 5;
	$exclude_ids    = array_merge( array( 0, $product_id ), $exclude_ids );
	$transient_name = 'wc_related_' . $product_id;
	$query_args     = http_build_query(
		array(
			'limit'       => $limit,
			'exclude_ids' => $exclude_ids,
		)
	);

	$transient     = get_transient( $transient_name );
	$related_posts = $transient && isset( $transient[ $query_args ] ) ? $transient[ $query_args ] : false;

	// We want to query related posts if they are not cached, or we don't have enough.
	if ( false === $related_posts || count( $related_posts ) < $limit ) {

		$cats_array = apply_filters( 'woocommerce_product_related_posts_relate_by_category', true, $product_id ) ? apply_filters( 'woocommerce_get_related_product_cat_terms', wc_get_product_term_ids( $product_id, 'product_cat' ), $product_id ) : array();
		$tags_array = apply_filters( 'woocommerce_product_related_posts_relate_by_tag', true, $product_id ) ? apply_filters( 'woocommerce_get_related_product_tag_terms', wc_get_product_term_ids( $product_id, 'product_tag' ), $product_id ) : array();

		// Don't bother if none are set, unless woocommerce_product_related_posts_force_display is set to true in which case all products are related.
		if ( empty( $cats_array ) && empty( $tags_array ) && ! apply_filters( 'woocommerce_product_related_posts_force_display', false, $product_id ) ) {
			$related_posts = array();
		} else {
			$data_store    = WC_Data_Store::load( 'product' );
			$related_posts = $data_store->get_related_products( $cats_array, $tags_array, $exclude_ids, $limit + 10, $product_id );
		}

		if ( $transient ) {
			$transient[ $query_args ] = $related_posts;
		} else {
			$transient = array( $query_args => $related_posts );
		}

		set_transient( $transient_name, $transient, DAY_IN_SECONDS );
	}

	$related_posts = apply_filters(
		'woocommerce_related_products',
		$related_posts,
		$product_id,
		array(
			'limit'        => $limit,
			'excluded_ids' => $exclude_ids,
		)
	);

	if ( apply_filters( 'woocommerce_product_related_posts_shuffle', true ) ) {
		shuffle( $related_posts );
	}

	return array_slice( $related_posts, 0, $limit );
}

/**
 * Retrieves product term ids for a taxonomy.
 *
 * @since  3.0.0
 * @param  int    $product_id Product ID.
 * @param  string $taxonomy   Taxonomy slug.
 * @return array
 */
function wc_get_product_term_ids( $product_id, $taxonomy ) {
	$terms = get_the_terms( $product_id, $taxonomy );
	return ( empty( $terms ) || is_wp_error( $terms ) ) ? array() : wp_list_pluck( $terms, 'term_id' );
}

/**
 * For a given product, and optionally price/qty, work out the price with tax included, based on store settings.
 *
 * @since  3.0.0
 * @param  WC_Product $product WC_Product object.
 * @param  array      $args Optional arguments to pass product quantity and price.
 * @return float
 */
function wc_get_price_including_tax( $product, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'qty'   => '',
			'price' => '',
		)
	);

	$price = '' !== $args['price'] ? max( 0.0, (float) $args['price'] ) : $product->get_price();
	$qty   = '' !== $args['qty'] ? max( 0.0, (float) $args['qty'] ) : 1;

	if ( '' === $price ) {
		return '';
	} elseif ( empty( $qty ) ) {
		return 0.0;
	}

	$line_price   = $price * $qty;
	$return_price = $line_price;

	if ( $product->is_taxable() ) {
		if ( ! wc_prices_include_tax() ) {
			$tax_rates = WC_Tax::get_rates( $product->get_tax_class() );
			$taxes     = WC_Tax::calc_tax( $line_price, $tax_rates, false );

			if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
				$taxes_total = array_sum( $taxes );
			} else {
				$taxes_total = array_sum( array_map( 'wc_round_tax_total', $taxes ) );
			}

			$return_price = round( $line_price + $taxes_total, wc_get_price_decimals() );
		} else {
			$tax_rates      = WC_Tax::get_rates( $product->get_tax_class() );
			$base_tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class( 'unfiltered' ) );

			/**
			 * If the customer is excempt from VAT, remove the taxes here.
			 * Either remove the base or the user taxes depending on woocommerce_adjust_non_base_location_prices setting.
			 */
			if ( ! empty( WC()->customer ) && WC()->customer->get_is_vat_exempt() ) { // @codingStandardsIgnoreLine.
				$remove_taxes = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? WC_Tax::calc_tax( $line_price, $base_tax_rates, true ) : WC_Tax::calc_tax( $line_price, $tax_rates, true );

				if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
					$remove_taxes_total = array_sum( $remove_taxes );
				} else {
					$remove_taxes_total = array_sum( array_map( 'wc_round_tax_total', $remove_taxes ) );
				}

				$return_price = round( $line_price - $remove_taxes_total, wc_get_price_decimals() );

				/**
			 * The woocommerce_adjust_non_base_location_prices filter can stop base taxes being taken off when dealing with out of base locations.
			 * e.g. If a product costs 10 including tax, all users will pay 10 regardless of location and taxes.
			 * This feature is experimental @since 2.4.7 and may change in the future. Use at your risk.
			 */
			} elseif ( $tax_rates !== $base_tax_rates && apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ) {
				$base_taxes   = WC_Tax::calc_tax( $line_price, $base_tax_rates, true );
				$modded_taxes = WC_Tax::calc_tax( $line_price - array_sum( $base_taxes ), $tax_rates, false );

				if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
					$base_taxes_total   = array_sum( $base_taxes );
					$modded_taxes_total = array_sum( $modded_taxes );
				} else {
					$base_taxes_total   = array_sum( array_map( 'wc_round_tax_total', $base_taxes ) );
					$modded_taxes_total = array_sum( array_map( 'wc_round_tax_total', $modded_taxes ) );
				}

				$return_price = round( $line_price - $base_taxes_total + $modded_taxes_total, wc_get_price_decimals() );
			}
		}
	}
	return apply_filters( 'woocommerce_get_price_including_tax', $return_price, $qty, $product );
}

/**
 * For a given product, and optionally price/qty, work out the price with tax excluded, based on store settings.
 *
 * @since  3.0.0
 * @param  WC_Product $product WC_Product object.
 * @param  array      $args Optional arguments to pass product quantity and price.
 * @return float
 */
function wc_get_price_excluding_tax( $product, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'qty'   => '',
			'price' => '',
		)
	);

	$price = '' !== $args['price'] ? max( 0.0, (float) $args['price'] ) : $product->get_price();
	$qty   = '' !== $args['qty'] ? max( 0.0, (float) $args['qty'] ) : 1;

	if ( '' === $price ) {
		return '';
	} elseif ( empty( $qty ) ) {
		return 0.0;
	}

	$line_price = $price * $qty;

	if ( $product->is_taxable() && wc_prices_include_tax() ) {
		$tax_rates      = WC_Tax::get_rates( $product->get_tax_class() );
		$base_tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class( 'unfiltered' ) );
		$remove_taxes   = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? WC_Tax::calc_tax( $line_price, $base_tax_rates, true ) : WC_Tax::calc_tax( $line_price, $tax_rates, true );
		$return_price   = $line_price - array_sum( $remove_taxes ); // Unrounded since we're dealing with tax inclusive prices. Matches logic in cart-totals class. @see adjust_non_base_location_price.
	} else {
		$return_price = $line_price;
	}

	return apply_filters( 'woocommerce_get_price_excluding_tax', $return_price, $qty, $product );
}

/**
 * Returns the price including or excluding tax, based on the 'woocommerce_tax_display_shop' setting.
 *
 * @since  3.0.0
 * @param  WC_Product $product WC_Product object.
 * @param  array      $args Optional arguments to pass product quantity and price.
 * @return float
 */
function wc_get_price_to_display( $product, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'qty'   => 1,
			'price' => $product->get_price(),
		)
	);

	$price = $args['price'];
	$qty   = $args['qty'];

	return 'incl' === get_option( 'woocommerce_tax_display_shop' ) ?
		wc_get_price_including_tax(
			$product,
			array(
				'qty'   => $qty,
				'price' => $price,
			)
		) :
		wc_get_price_excluding_tax(
			$product,
			array(
				'qty'   => $qty,
				'price' => $price,
			)
		);
}

/**
 * Returns the product categories in a list.
 *
 * @param int    $product_id Product ID.
 * @param string $sep (default: ', ').
 * @param string $before (default: '').
 * @param string $after (default: '').
 * @return string
 */
function wc_get_product_category_list( $product_id, $sep = ', ', $before = '', $after = '' ) {
	return get_the_term_list( $product_id, 'product_cat', $before, $sep, $after );
}

/**
 * Returns the product tags in a list.
 *
 * @param int    $product_id Product ID.
 * @param string $sep (default: ', ').
 * @param string $before (default: '').
 * @param string $after (default: '').
 * @return string
 */
function wc_get_product_tag_list( $product_id, $sep = ', ', $before = '', $after = '' ) {
	return get_the_term_list( $product_id, 'product_tag', $before, $sep, $after );
}

/**
 * Callback for array filter to get visible only.
 *
 * @since  3.0.0
 * @param  WC_Product $product WC_Product object.
 * @return bool
 */
function wc_products_array_filter_visible( $product ) {
	return $product && is_a( $product, 'WC_Product' ) && $product->is_visible();
}

/**
 * Callback for array filter to get visible grouped products only.
 *
 * @since  3.1.0
 * @param  WC_Product $product WC_Product object.
 * @return bool
 */
function wc_products_array_filter_visible_grouped( $product ) {
	return $product && is_a( $product, 'WC_Product' ) && ( 'publish' === $product->get_status() || current_user_can( 'edit_product', $product->get_id() ) );
}

/**
 * Callback for array filter to get products the user can edit only.
 *
 * @since  3.0.0
 * @param  WC_Product $product WC_Product object.
 * @return bool
 */
function wc_products_array_filter_editable( $product ) {
	return $product && is_a( $product, 'WC_Product' ) && current_user_can( 'edit_product', $product->get_id() );
}

/**
 * Callback for array filter to get products the user can view only.
 *
 * @since  3.4.0
 * @param  WC_Product $product WC_Product object.
 * @return bool
 */
function wc_products_array_filter_readable( $product ) {
	return $product && is_a( $product, 'WC_Product' ) && current_user_can( 'read_product', $product->get_id() );
}

/**
 * Sort an array of products by a value.
 *
 * @since  3.0.0
 *
 * @param array  $products List of products to be ordered.
 * @param string $orderby Optional order criteria.
 * @param string $order Ascending or descending order.
 *
 * @return array
 */
function wc_products_array_orderby( $products, $orderby = 'date', $order = 'desc' ) {
	$orderby = strtolower( $orderby );
	$order   = strtolower( $order );
	switch ( $orderby ) {
		case 'title':
		case 'id':
		case 'date':
		case 'modified':
		case 'menu_order':
		case 'price':
			usort( $products, 'wc_products_array_orderby_' . $orderby );
			break;
		case 'none':
			break;
		default:
			shuffle( $products );
			break;
	}
	if ( 'desc' === $order ) {
		$products = array_reverse( $products );
	}
	return $products;
}

/**
 * Sort by title.
 *
 * @since  3.0.0
 * @param  WC_Product $a First WC_Product object.
 * @param  WC_Product $b Second WC_Product object.
 * @return int
 */
function wc_products_array_orderby_title( $a, $b ) {
	return strcasecmp( $a->get_name(), $b->get_name() );
}

/**
 * Sort by id.
 *
 * @since  3.0.0
 * @param  WC_Product $a First WC_Product object.
 * @param  WC_Product $b Second WC_Product object.
 * @return int
 */
function wc_products_array_orderby_id( $a, $b ) {
	if ( $a->get_id() === $b->get_id() ) {
		return 0;
	}
	return ( $a->get_id() < $b->get_id() ) ? -1 : 1;
}

/**
 * Sort by date.
 *
 * @since  3.0.0
 * @param  WC_Product $a First WC_Product object.
 * @param  WC_Product $b Second WC_Product object.
 * @return int
 */
function wc_products_array_orderby_date( $a, $b ) {
	if ( $a->get_date_created() === $b->get_date_created() ) {
		return 0;
	}
	return ( $a->get_date_created() < $b->get_date_created() ) ? -1 : 1;
}

/**
 * Sort by modified.
 *
 * @since  3.0.0
 * @param  WC_Product $a First WC_Product object.
 * @param  WC_Product $b Second WC_Product object.
 * @return int
 */
function wc_products_array_orderby_modified( $a, $b ) {
	if ( $a->get_date_modified() === $b->get_date_modified() ) {
		return 0;
	}
	return ( $a->get_date_modified() < $b->get_date_modified() ) ? -1 : 1;
}

/**
 * Sort by menu order.
 *
 * @since  3.0.0
 * @param  WC_Product $a First WC_Product object.
 * @param  WC_Product $b Second WC_Product object.
 * @return int
 */
function wc_products_array_orderby_menu_order( $a, $b ) {
	if ( $a->get_menu_order() === $b->get_menu_order() ) {
		return 0;
	}
	return ( $a->get_menu_order() < $b->get_menu_order() ) ? -1 : 1;
}

/**
 * Sort by price low to high.
 *
 * @since  3.0.0
 * @param  WC_Product $a First WC_Product object.
 * @param  WC_Product $b Second WC_Product object.
 * @return int
 */
function wc_products_array_orderby_price( $a, $b ) {
	if ( $a->get_price() === $b->get_price() ) {
		return 0;
	}
	return ( $a->get_price() < $b->get_price() ) ? -1 : 1;
}

/**
 * Queue a product for syncing at the end of the request.
 *
 * @param int $product_id Product ID.
 */
function wc_deferred_product_sync( $product_id ) {
	global $wc_deferred_product_sync;

	if ( empty( $wc_deferred_product_sync ) ) {
		$wc_deferred_product_sync = array();
	}

	$wc_deferred_product_sync[] = $product_id;
}

/**
 * See if the lookup table is being generated already.
 *
 * @since 3.6.0
 * @return bool
 */
function wc_update_product_lookup_tables_is_running() {
	$table_updates_pending = WC()->queue()->search(
		array(
			'status'   => 'pending',
			'group'    => 'wc_update_product_lookup_tables',
			'per_page' => 1,
		)
	);

	return (bool) count( $table_updates_pending );
}

/**
 * Populate lookup table data for products.
 *
 * @since 3.6.0
 */
function wc_update_product_lookup_tables() {
	global $wpdb;

	$is_cli = Constants::is_true( 'WP_CLI' );

	if ( ! $is_cli ) {
		WC_Admin_Notices::add_notice( 'regenerating_lookup_table' );
	}

	// Note that the table is not yet generated.
	update_option( 'woocommerce_product_lookup_table_is_generating', true );

	// Make a row per product in lookup table.
	$wpdb->query(
		"
		INSERT IGNORE INTO {$wpdb->wc_product_meta_lookup} (`product_id`)
		SELECT
			posts.ID
		FROM {$wpdb->posts} posts
		WHERE
			posts.post_type IN ('product', 'product_variation')
		"
	);

	// List of column names in the lookup table we need to populate.
	$columns = array(
		'min_max_price',
		'stock_quantity',
		'sku',
		'stock_status',
		'average_rating',
		'total_sales',
		'downloadable',
		'virtual',
		'onsale',
		'tax_class',
		'tax_status', // When last column is updated, woocommerce_product_lookup_table_is_generating is updated.
	);

	foreach ( $columns as $index => $column ) {
		if ( $is_cli ) {
			wc_update_product_lookup_tables_column( $column );
		} else {
			WC()->queue()->schedule_single(
				time() + $index,
				'wc_update_product_lookup_tables_column',
				array(
					'column' => $column,
				),
				'wc_update_product_lookup_tables'
			);
		}
	}

	// Rating counts are serialised so they have to be unserialised before populating the lookup table.
	if ( $is_cli ) {
		$rating_count_rows = $wpdb->get_results(
			"
			SELECT post_id, meta_value FROM {$wpdb->postmeta}
			WHERE meta_key = '_wc_rating_count'
			AND meta_value != ''
			AND meta_value != 'a:0:{}'
			",
			ARRAY_A
		);
		wc_update_product_lookup_tables_rating_count( $rating_count_rows );
	} else {
		WC()->queue()->schedule_single(
			time() + 10,
			'wc_update_product_lookup_tables_rating_count_batch',
			array(
				'offset' => 0,
				'limit'  => 50,
			),
			'wc_update_product_lookup_tables'
		);
	}
}

/**
 * Populate lookup table column data.
 *
 * @since 3.6.0
 * @param string $column Column name to set.
 */
function wc_update_product_lookup_tables_column( $column ) {
	if ( empty( $column ) ) {
		return;
	}
	global $wpdb;
	switch ( $column ) {
		case 'min_max_price':
			$wpdb->query(
				"
				UPDATE
					{$wpdb->wc_product_meta_lookup} lookup_table
					INNER JOIN (
						SELECT lookup_table.product_id, MIN( meta_value+0 ) as min_price, MAX( meta_value+0 ) as max_price
						FROM {$wpdb->wc_product_meta_lookup} lookup_table
						LEFT JOIN {$wpdb->postmeta} meta1 ON lookup_table.product_id = meta1.post_id AND meta1.meta_key = '_price'
						WHERE
							meta1.meta_value <> ''
						GROUP BY lookup_table.product_id
					) as source on source.product_id = lookup_table.product_id
				SET
					lookup_table.min_price = source.min_price,
					lookup_table.max_price = source.max_price
				"
			);
			break;
		case 'stock_quantity':
			$wpdb->query(
				"
				UPDATE
					{$wpdb->wc_product_meta_lookup} lookup_table
					LEFT JOIN {$wpdb->postmeta} meta1 ON lookup_table.product_id = meta1.post_id AND meta1.meta_key = '_manage_stock'
					LEFT JOIN {$wpdb->postmeta} meta2 ON lookup_table.product_id = meta2.post_id AND meta2.meta_key = '_stock'
				SET
					lookup_table.stock_quantity = meta2.meta_value
				WHERE
					meta1.meta_value = 'yes'
				"
			);
			break;
		case 'sku':
		case 'stock_status':
		case 'average_rating':
		case 'total_sales':
		case 'tax_class':
		case 'tax_status':
			if ( 'total_sales' === $column ) {
				$meta_key = 'total_sales';
			} elseif ( 'average_rating' === $column ) {
				$meta_key = '_wc_average_rating';
			} else {
				$meta_key = '_' . $column;
			}
			$column = esc_sql( $column );
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query(
				$wpdb->prepare(
					"
					UPDATE
						{$wpdb->wc_product_meta_lookup} lookup_table
						LEFT JOIN {$wpdb->postmeta} meta ON lookup_table.product_id = meta.post_id AND meta.meta_key = %s
					SET
						lookup_table.`{$column}` = meta.meta_value
					",
					$meta_key
				)
			);
			// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			break;
		case 'downloadable':
		case 'virtual':
			$column   = esc_sql( $column );
			$meta_key = '_' . $column;
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query(
				$wpdb->prepare(
					"
					UPDATE
						{$wpdb->wc_product_meta_lookup} lookup_table
						LEFT JOIN {$wpdb->postmeta} meta1 ON lookup_table.product_id = meta1.post_id AND meta1.meta_key = %s
					SET
						lookup_table.`{$column}` = IF ( meta1.meta_value = 'yes', 1, 0 )
					",
					$meta_key
				)
			);
			// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			break;
		case 'onsale':
			$column   = esc_sql( $column );
			$decimals = absint( wc_get_price_decimals() );

			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query(
				$wpdb->prepare(
					"
					UPDATE
						{$wpdb->wc_product_meta_lookup} lookup_table
						LEFT JOIN {$wpdb->postmeta} meta1 ON lookup_table.product_id = meta1.post_id AND meta1.meta_key = '_price'
						LEFT JOIN {$wpdb->postmeta} meta2 ON lookup_table.product_id = meta2.post_id AND meta2.meta_key = '_sale_price'
					SET
						lookup_table.`{$column}` = IF (
							CAST( meta1.meta_value AS DECIMAL ) >= 0
							AND CAST( meta2.meta_value AS CHAR ) != ''
							AND CAST( meta1.meta_value AS DECIMAL( 10, %d ) ) = CAST( meta2.meta_value AS DECIMAL( 10, %d ) )
						, 1, 0 )
					",
					$decimals,
					$decimals
				)
			);
			// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			break;
	}

	// Final column - mark complete.
	if ( 'tax_status' === $column ) {
		delete_option( 'woocommerce_product_lookup_table_is_generating' );
	}
}
add_action( 'wc_update_product_lookup_tables_column', 'wc_update_product_lookup_tables_column' );

/**
 * Populate rating count lookup table data for products.
 *
 * @since 3.6.0
 * @param array $rows Rows of rating counts to update in lookup table.
 */
function wc_update_product_lookup_tables_rating_count( $rows ) {
	if ( ! $rows || ! is_array( $rows ) ) {
		return;
	}
	global $wpdb;

	foreach ( $rows as $row ) {
		$count = array_sum( (array) maybe_unserialize( $row['meta_value'] ) );
		$wpdb->update(
			$wpdb->wc_product_meta_lookup,
			array(
				'rating_count' => absint( $count ),
			),
			array(
				'product_id' => absint( $row['post_id'] ),
			)
		);
	}
}

/**
 * Populate a batch of rating count lookup table data for products.
 *
 * @since 3.6.2
 * @param array $offset Offset to query.
 * @param array $limit  Limit to query.
 */
function wc_update_product_lookup_tables_rating_count_batch( $offset = 0, $limit = 0 ) {
	global $wpdb;

	if ( ! $limit ) {
		return;
	}

	$rating_count_rows = $wpdb->get_results(
		$wpdb->prepare(
			"
			SELECT post_id, meta_value FROM {$wpdb->postmeta}
			WHERE meta_key = '_wc_rating_count'
			AND meta_value != ''
			AND meta_value != 'a:0:{}'
			ORDER BY post_id ASC
			LIMIT %d, %d
			",
			$offset,
			$limit
		),
		ARRAY_A
	);

	if ( $rating_count_rows ) {
		wc_update_product_lookup_tables_rating_count( $rating_count_rows );
		WC()->queue()->schedule_single(
			time() + 1,
			'wc_update_product_lookup_tables_rating_count_batch',
			array(
				'offset' => $offset + $limit,
				'limit'  => $limit,
			),
			'wc_update_product_lookup_tables'
		);
	}
}
add_action( 'wc_update_product_lookup_tables_rating_count_batch', 'wc_update_product_lookup_tables_rating_count_batch', 10, 2 );
/**
 * Output a text input box.
 *
 * @param array $field
 */
function woocommerce_wp_text_input_custom( $field ) {
    global $thepostid, $post;

    $thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
    $field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
    $field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
    $field['style']         = isset( $field['style'] ) ? $field['style'] : '';
    $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
    $field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
    $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
    $field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';
    $field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : false;
    $data_type              = empty( $field['data_type'] ) ? '' : $field['data_type'];

    switch ( $data_type ) {
        case 'price':
            $field['class'] .= ' wc_input_price';
            $field['value']  = wc_format_localized_price( $field['value'] );
            break;
        case 'decimal':
            $field['class'] .= ' wc_input_decimal';
            $field['value']  = wc_format_localized_decimal( $field['value'] );
            break;
        case 'stock':
            $field['class'] .= ' wc_input_stock';
            $field['value']  = wc_stock_amount( $field['value'] );
            break;
        case 'url':
            $field['class'] .= ' wc_input_url';
            $field['value']  = esc_url( $field['value'] );
            break;

        default:
            break;
    }

    // Custom attribute handling
    $custom_attributes = array();

    if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

        foreach ( $field['custom_attributes'] as $attribute => $value ) {
            $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
        }
    }

    echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">
		<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';

    if ( ! empty( $field['description'] ) && false !== $field['desc_tip'] ) {
        echo wc_help_tip( $field['description'] );
    }

    echo '<input type="' . esc_attr( $field['type'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . implode( ' ', $custom_attributes ) . ' /> ';

    if ( ! empty( $field['description'] ) && false === $field['desc_tip'] ) {
        echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
    }

    echo '</p>';
}

/**
 * Output a hidden input box.
 *
 * @param array $field
 */
function woocommerce_wp_hidden_input_custom( $field ) {
    global $thepostid, $post;

    $thepostid      = empty( $thepostid ) ? $post->ID : $thepostid;
    $field['value'] = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
    $field['class'] = isset( $field['class'] ) ? $field['class'] : '';

    echo '<input type="hidden" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" /> ';
}

/**
 * Output a textarea input box.
 *
 * @param array $field
 */
function woocommerce_wp_textarea_input_custom( $field ) {
    global $thepostid, $post;

    $thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
    $field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
    $field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
    $field['style']         = isset( $field['style'] ) ? $field['style'] : '';
    $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
    $field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
    $field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : false;
    $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
    $field['rows']          = isset( $field['rows'] ) ? $field['rows'] : 2;
    $field['cols']          = isset( $field['cols'] ) ? $field['cols'] : 20;

    // Custom attribute handling
    $custom_attributes = array();

    if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

        foreach ( $field['custom_attributes'] as $attribute => $value ) {
            $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
        }
    }

    echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">
		<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';

    if ( ! empty( $field['description'] ) && false !== $field['desc_tip'] ) {
        echo wc_help_tip( $field['description'] );
    }

    echo '<textarea class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '"  name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" rows="' . esc_attr( $field['rows'] ) . '" cols="' . esc_attr( $field['cols'] ) . '" ' . implode( ' ', $custom_attributes ) . '>' . esc_textarea( $field['value'] ) . '</textarea> ';

    if ( ! empty( $field['description'] ) && false === $field['desc_tip'] ) {
        echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
    }

    echo '</p>';
}

/**
 * Output a checkbox input box.
 *
 * @param array $field
 */
function woocommerce_wp_checkbox_custom( $field ) {
    global $thepostid, $post;

    $thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
    $field['class']         = isset( $field['class'] ) ? $field['class'] : 'checkbox';
    $field['style']         = isset( $field['style'] ) ? $field['style'] : '';
    $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
    $field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
    $field['cbvalue']       = isset( $field['cbvalue'] ) ? $field['cbvalue'] : 'yes';
    $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
    $field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : false;

    // Custom attribute handling
    $custom_attributes = array();

    if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

        foreach ( $field['custom_attributes'] as $attribute => $value ) {
            $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
        }
    }

    echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">
		<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';

    if ( ! empty( $field['description'] ) && false !== $field['desc_tip'] ) {
        echo wc_help_tip( $field['description'] );
    }

    echo '<input type="checkbox" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['cbvalue'] ) . '" ' . checked( $field['value'], $field['cbvalue'], false ) . '  ' . implode( ' ', $custom_attributes ) . '/> ';

    if ( ! empty( $field['description'] ) && false === $field['desc_tip'] ) {
        echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
    }

    echo '</p>';
}

/**
 * Output a select input box.
 *
 * @param array $field Data about the field to render.
 */
function woocommerce_wp_select_custom( $field ) {
    global $thepostid, $post;

    $thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
    $field     = wp_parse_args(
        $field, array(
            'class'             => 'select short',
            'style'             => '',
            'wrapper_class'     => '',
            'value'             => get_post_meta( $thepostid, $field['id'], true ),
            'name'              => $field['id'],
            'desc_tip'          => false,
            'custom_attributes' => array(),
        )
    );

    $wrapper_attributes = array(
        'class' => $field['wrapper_class'] . " form-field {$field['id']}_field",
    );

    $label_attributes = array(
        'for' => $field['id'],
    );

    $field_attributes          = (array) $field['custom_attributes'];
    $field_attributes['style'] = $field['style'];
    $field_attributes['id']    = $field['id'];
    $field_attributes['name']  = $field['name'];
    $field_attributes['class'] = $field['class'];

    $tooltip     = ! empty( $field['description'] ) && false !== $field['desc_tip'] ? $field['description'] : '';
    $description = ! empty( $field['description'] ) && false === $field['desc_tip'] ? $field['description'] : '';
    ?>
    <p <?php echo wc_implode_html_attributes( $wrapper_attributes ); // WPCS: XSS ok. ?>>
        <label <?php echo wc_implode_html_attributes( $label_attributes ); // WPCS: XSS ok. ?>><?php echo wp_kses_post( $field['label'] ); ?></label>
        <?php if ( $tooltip ) : ?>
            <?php echo wc_help_tip( $tooltip ); // WPCS: XSS ok. ?>
        <?php endif; ?>
        <select <?php echo wc_implode_html_attributes( $field_attributes ); // WPCS: XSS ok. ?>>
            <?php
            foreach ( $field['options'] as $key => $value ) {
                echo '<option value="' . esc_attr( $key ) . '"' . wc_selected( $key, $field['value'] ) . '>' . esc_html( $value ) . '</option>';
            }
            ?>
        </select>
        <?php if ( $description ) : ?>
            <span class="description"><?php echo wp_kses_post( $description ); ?></span>
        <?php endif; ?>
    </p>
    <?php
}

/**
 * Output a radio input box.
 *
 * @param array $field
 */
function woocommerce_wp_radio_custom( $field ) {
    global $thepostid, $post;

    $thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
    $field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';
    $field['style']         = isset( $field['style'] ) ? $field['style'] : '';
    $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
    $field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
    $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
    $field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : false;

    echo '<fieldset class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><legend>' . wp_kses_post( $field['label'] ) . '</legend>';

    if ( ! empty( $field['description'] ) && false !== $field['desc_tip'] ) {
        echo wc_help_tip( $field['description'] );
    }

    echo '<ul class="wc-radios">';

    foreach ( $field['options'] as $key => $value ) {

        echo '<li><label><input
				name="' . esc_attr( $field['name'] ) . '"
				value="' . esc_attr( $key ) . '"
				type="radio"
				class="' . esc_attr( $field['class'] ) . '"
				style="' . esc_attr( $field['style'] ) . '"
				' . checked( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '
				/> ' . esc_html( $value ) . '</label>
		</li>';
    }
    echo '</ul>';

    if ( ! empty( $field['description'] ) && false === $field['desc_tip'] ) {
        echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
    }

    echo '</fieldset>';
}

/**
 * Get payment plans product setting fields.
 */
function get_product_settings($postId = null) {

    woocommerce_wp_checkbox_custom( array(
        'label'    => __( 'Enable SUMO Payment Plans' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
        'id'       => SUMO_PP_PLUGIN_PREFIX . 'enable_sumopaymentplans' ,
        'desc_tip' => __( 'Enabling this option allows you to configure the product to accept product booking by paying a deposit amount / purchase the product by choosing from the available payment plans' , SUMO_PP_PLUGIN_TEXT_DOMAIN ),
        'value'    => get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'enable_sumopaymentplans' , true ) ,
    ) ) ;
    woocommerce_wp_select_custom( array(
        'label'         => __( 'Payment Type' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
        'id'            => SUMO_PP_PLUGIN_PREFIX . 'payment_type' ,
        'wrapper_class' => SUMO_PP_PLUGIN_PREFIX . 'fields' ,
        'options'       => array(
            'pay-in-deposit' => __( 'Pay a Deposit Amount' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
            'payment-plans'  => __( 'Pay with Payment Plans' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
        ) ,
        'value'         => get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'payment_type' , true ) ,
        'description'   => '<a href="#" class="'.SUMO_PP_PLUGIN_PREFIX.'schedule_availability">' . __('Schedule', SUMO_PP_PLUGIN_TEXT_DOMAIN) . '</a>',
    )) ;

    $from = get_post_meta($postId, SUMO_PP_PLUGIN_PREFIX . 'available_dates_from', true) ;
    $to   = get_post_meta($postId, SUMO_PP_PLUGIN_PREFIX . 'available_dates_to', true) ;
    ?>
    <p class="form-field <?php echo SUMO_PP_PLUGIN_PREFIX . 'availability_dates_field' . ' ' . SUMO_PP_PLUGIN_PREFIX . 'fields' ; ?>">
        <input type="text" class="short" name="<?php echo SUMO_PP_PLUGIN_PREFIX . 'available_dates_from' ; ?>" id="<?php echo SUMO_PP_PLUGIN_PREFIX . 'available_dates_from' ; ?>" value="<?php echo '' === $from ? '' : date_i18n( 'Y-m-d', _sumo_pp_get_timestamp( $from ) ) ?>" placeholder="<?php _e('From&hellip;YYYY-MM-DD', SUMO_PP_PLUGIN_TEXT_DOMAIN) ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
        <input type="text" class="short" name="<?php echo SUMO_PP_PLUGIN_PREFIX . 'available_dates_to' ; ?>" id="<?php echo SUMO_PP_PLUGIN_PREFIX . 'available_dates_to' ; ?>" value="<?php echo '' === $to ? '' : date_i18n( 'Y-m-d', _sumo_pp_get_timestamp( $to ) ) ?>" placeholder="<?php _e('To&hellip;YYYY-MM-DD', SUMO_PP_PLUGIN_TEXT_DOMAIN) ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
        <a href="#" class="description <?php echo SUMO_PP_PLUGIN_PREFIX . 'cancel_availability' ; ?>"><?php echo __('Cancel', SUMO_PP_PLUGIN_TEXT_DOMAIN) ?></a><?php echo wc_help_tip(__('The payment plan/deposit options will be given to users starting at 00:00:00 of "From" date and ending at 23:59:59 of "To" date.', SUMO_PP_PLUGIN_TEXT_DOMAIN)) ?>
    </p>
    <?php
    woocommerce_wp_checkbox_custom( array(
        'label'         => __( 'Apply Global Level Settings' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
        'id'            => SUMO_PP_PLUGIN_PREFIX . 'apply_global_settings' ,
        'wrapper_class' => SUMO_PP_PLUGIN_PREFIX . 'fields' ,
        'value'         => get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'apply_global_settings' , true ) ,
        'desc_tip'      => __( 'When enabled, the settings for SUMO Payment Plans will apply from global level' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
    ) ) ;
    woocommerce_wp_checkbox_custom( array(
        'label'         => __( 'Force Deposit/Payment Plans' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
        'id'            => SUMO_PP_PLUGIN_PREFIX . 'force_deposit' ,
        'wrapper_class' => SUMO_PP_PLUGIN_PREFIX . 'fields' ,
        'value'         => get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'force_deposit' , true ) ,
        'desc_tip'      => __( 'When enabled, the user will be forced to pay a deposit amount' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
    ) ) ;
    woocommerce_wp_select_custom( array(
        'label'         => __( 'Deposit Type' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
        'id'            => SUMO_PP_PLUGIN_PREFIX . 'deposit_type' ,
        'wrapper_class' => SUMO_PP_PLUGIN_PREFIX . 'fields' ,
        'options'       => array(
            'pre-defined'  => __( 'Predefined Deposit Amount' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
            'user-defined' => __( 'User Defined Deposit Amount' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
        ),
        'value'         => get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'deposit_type' , true ) ,
    ) ) ;
    woocommerce_wp_select_custom( array(
        'label'         => __( 'Deposit Price Type' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
        'id'            => SUMO_PP_PLUGIN_PREFIX . 'deposit_price_type' ,
        'wrapper_class' => SUMO_PP_PLUGIN_PREFIX . 'fields' ,
        'options'       => array(
            'fixed-price'              => __( 'Fixed Price' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
            'percent-of-product-price' => __( 'Percentage of Product Price' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
        ),
        'value'         => get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'deposit_price_type' , true ) ,
    ) ) ;
    woocommerce_wp_text_input_custom( array(
        'label'         => __( 'Deposit Amount' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
        'id'            => SUMO_PP_PLUGIN_PREFIX . 'fixed_deposit_price' ,
        'wrapper_class' => SUMO_PP_PLUGIN_PREFIX . 'fields' ,
        'style'         => 'width:20%;' ,
        'data_type'     => 'price' ,
        'value'         => get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'fixed_deposit_price' , true ) ,
    ) ) ;
    woocommerce_wp_text_input_custom( array(
        'label'             => __( 'Deposit Percentage' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
        'id'                => SUMO_PP_PLUGIN_PREFIX . 'fixed_deposit_percent' ,
        'wrapper_class'     => SUMO_PP_PLUGIN_PREFIX . 'fields' ,
        'style'             => 'width:20%;' ,
        'type'              => 'number' ,
        'custom_attributes' => array(
            'step' => '0.01' ,
        ),
        'value'             => get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'fixed_deposit_percent' , true ) ,
    ) ) ;
    woocommerce_wp_select_custom( array(
        'label'         => __( 'User Defined Deposit Type' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
        'id'            => SUMO_PP_PLUGIN_PREFIX . 'user_defined_deposit_type' ,
        'wrapper_class' => SUMO_PP_PLUGIN_PREFIX . 'fields' ,
        'options'       => array(
            'percent-of-product-price' => __( 'Percentage of Product Price' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
            'fixed-price'              => __( 'Fixed Price' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
        ),
        'value'         => get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'user_defined_deposit_type' , true ) ,
    ) ) ;
    woocommerce_wp_text_input_custom( array(
        'label'         => __( 'Minimum Deposit Price' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
        'id'            => SUMO_PP_PLUGIN_PREFIX . 'min_user_defined_deposit_price' ,
        'wrapper_class' => SUMO_PP_PLUGIN_PREFIX . 'fields' ,
        'style'         => 'width:20%;' ,
        'data_type'     => 'price' ,
        'value'         => get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'min_user_defined_deposit_price' , true ) ,
    ) ) ;
    woocommerce_wp_text_input_custom( array(
        'label'         => __( 'Maximum Deposit Price' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
        'id'            => SUMO_PP_PLUGIN_PREFIX . 'max_user_defined_deposit_price' ,
        'wrapper_class' => SUMO_PP_PLUGIN_PREFIX . 'fields' ,
        'style'         => 'width:20%;' ,
        'data_type'     => 'price' ,
        'value'         => get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'max_user_defined_deposit_price' , true ) ,
    ) ) ;
    woocommerce_wp_text_input_custom( array(
        'label'             => __( 'Minimum Deposit(%)' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
        'id'                => SUMO_PP_PLUGIN_PREFIX . 'min_deposit' ,
        'wrapper_class'     => SUMO_PP_PLUGIN_PREFIX . 'fields' ,
        'style'             => 'width:20%;' ,
        'type'              => 'number' ,
        'custom_attributes' => array(
            'min'  => '0.01' ,
            'max'  => '99.99' ,
            'step' => '0.01' ,
        ) ,
        'value'             => get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'min_deposit' , true ) ,
    ) ) ;
    woocommerce_wp_text_input_custom( array(
        'label'             => __( 'Maximum Deposit(%)' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
        'id'                => SUMO_PP_PLUGIN_PREFIX . 'max_deposit' ,
        'wrapper_class'     => SUMO_PP_PLUGIN_PREFIX . 'fields' ,
        'style'             => 'width:20%;' ,
        'type'              => 'number' ,
        'custom_attributes' => array(
            'min'  => '0.01' ,
            'max'  => '99.99' ,
            'step' => '0.01' ,
        ) ,
        'value'             => get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'max_deposit' , true ) ,
    ) ) ;
    ?>
    <p class="form-field <?php echo SUMO_PP_PLUGIN_PREFIX . 'pay_balance_type_field' . ' ' . SUMO_PP_PLUGIN_PREFIX . 'fields' ; ?>">
        <label for="<?php echo SUMO_PP_PLUGIN_PREFIX . 'pay_balance_type' ; ?>"><?php _e( 'Deposit Balance Payment Due Date' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?></label>
        <select id="<?php echo SUMO_PP_PLUGIN_PREFIX . "pay_balance_type" ; ?>" name="<?php echo SUMO_PP_PLUGIN_PREFIX . "pay_balance_type" ; ?>">
            <option value="after" <?php selected( true , 'after' === get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'pay_balance_type' , true ) ) ?>><?php _e( 'After' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ?></option>
            <option value="before" <?php selected( true , 'before' === get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'pay_balance_type' , true ) ) ?>><?php _e( 'Before' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ?></option>
        </select>
        <span>
                <input type="number" id="<?php echo SUMO_PP_PLUGIN_PREFIX . "pay_balance_after" ; ?>" name="<?php echo SUMO_PP_PLUGIN_PREFIX . "pay_balance_after" ; ?>" value="<?php echo '' === get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'balance_payment_due' , true ) ? get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'pay_balance_after' , true ) : get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'balance_payment_due' , true ) ; ?>" style="width:20%;">
                <span class="description"><?php _e( 'day(s) from the date of deposit payment' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ?></span>
            </span>
        <span>
                <input type="text" placeholder="<?php esc_attr_e( 'YYYY-MM-DD' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ?>" id="<?php echo SUMO_PP_PLUGIN_PREFIX . "pay_balance_before" ; ?>" name="<?php echo SUMO_PP_PLUGIN_PREFIX . "pay_balance_before" ; ?>" value="<?php echo get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'pay_balance_before' , true ) ; ?>" style="width:20%;">
            </span>
        <?php if( class_exists( 'SUMO_Bookings' ) ) { ?>
            <span>
                    <input type="number" min="0" id="<?php echo SUMO_PP_PLUGIN_PREFIX . "pay_balance_before_booked_date" ; ?>" name="<?php echo SUMO_PP_PLUGIN_PREFIX . "pay_balance_before_booked_date" ; ?>" value="<?php echo get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'pay_balance_before_booked_date' , true ) ; ?>" style="width:20%;display: none;">
                    <span class="description"><?php _e( 'day(s) of booking start date' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ?></span>
                </span>
        <?php } ?>
    </p>
    <p class="form-field <?php echo SUMO_PP_PLUGIN_PREFIX . 'set_expired_deposit_payment_as_field' . ' ' . SUMO_PP_PLUGIN_PREFIX . 'fields' ; ?>">
        <label for="<?php echo SUMO_PP_PLUGIN_PREFIX . 'set_expired_deposit_payment_as' ; ?>"><?php _e( 'After Balance Payment Due Date' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?></label>
        <select id="<?php echo SUMO_PP_PLUGIN_PREFIX . "set_expired_deposit_payment_as" ; ?>" name="<?php echo SUMO_PP_PLUGIN_PREFIX . "set_expired_deposit_payment_as" ; ?>">
            <option value="normal" <?php selected( true , 'normal' === get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'set_expired_deposit_payment_as' , true ) ) ?>><?php _e( 'Disable SUMO Payment Plans' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ?></option>
            <option value="out-of-stock" <?php selected( true , 'out-of-stock' === get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'set_expired_deposit_payment_as' , true ) ) ?>><?php _e( 'Set Product as Out of Stock' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ?></option>
        </select>
    </p>
    <p class="form-field <?php echo SUMO_PP_PLUGIN_PREFIX . 'selected_plans_field' . ' ' . SUMO_PP_PLUGIN_PREFIX . 'fields' ; ?>">
        <label for="<?php echo SUMO_PP_PLUGIN_PREFIX . 'selected_plans' ; ?>"><?php _e( 'Select Plans' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?></label>
        <span class="<?php echo SUMO_PP_PLUGIN_PREFIX . 'add_plans' ; ?>">
                <span class="woocommerce-help-tip" data-tip="<?php _e( 'Select the layout as per your theme preference' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?>"></span>
                <a href="#" class="button" id="<?php echo SUMO_PP_PLUGIN_PREFIX . 'add_col_1_plan' ; ?>"><?php _e( 'Add Row for Column 1' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?></a>
                <a href="#" class="button" id="<?php echo SUMO_PP_PLUGIN_PREFIX . 'add_col_2_plan' ; ?>"><?php _e( 'Add Row for Column 2' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ; ?></a>
                <span class="spinner"></span>
            </span>
    </p>
    <style>
        table._sumo_pp_footable{
            margin-bottom:10px !important;
        }
        table._sumo_pp_footable td, table._sumo_pp_footable td:last-child{
            border-top:none !important;
            border-left:none !important;
            border-right:none !important;
            border-bottom:1px solid #ccc !important;
        }
        table._sumo_pp_footable td select, table._sumo_pp_footable td span{
            width:100% !important;
        }
        table._sumo_pp_footable td span{
            padding-right:0px !important;
        }
        table._sumo_pp_footable td .select2-container--default  .select2-selection--single .select2-selection__arrow{
            width:auto !important;
            right:8px;
        }
        table._sumo_pp_footable td .select2-container--default  .select2-selection--single .select2-selection__arrow b{
            width:auto !important;
            text-align:right;
        }
        table._sumo_pp_footable td .select2-selection__clear{
            width:auto !important;
            margin-right:20%;

        }
        table._sumo_pp_footable td a{
            float:right !important;

        }
    </style>
    <?php
    $selected_plans     = get_post_meta( $postId , SUMO_PP_PLUGIN_PREFIX . 'selected_plans' , true ) ;
    $selected_plans     = $bkw_selected_plans = is_array( $selected_plans ) && ! empty( $selected_plans ) ? $selected_plans : array( 'col_1' => array() , 'col_2' => array() ) ;

    if( ! isset( $bkw_selected_plans[ 'col_1' ] ) ) {
        $selected_plans = array( 'col_1' => array() , 'col_2' => array() ) ;

        foreach( $bkw_selected_plans as $row_id => $selected_plan ) {
            $selected_plans[ 'col_1' ][] = ! empty( $selected_plan ) ? ( array ) $selected_plan : array() ;
        }
    }

    foreach( $selected_plans as $column_id => $selected_datas ) {
        $inline_style = 'col_1' === $column_id ? 'float:left;margin-left:3px;' : 'float:right;margin-right:3px;' ;
        $inline_style.='width:49%;clear:none;' ;
        ?>
        <table class="widefat wc_input_table wc_gateways sortable <?php echo SUMO_PP_PLUGIN_PREFIX . 'footable ' . SUMO_PP_PLUGIN_PREFIX . "selected_col_{$column_id}_plans " . SUMO_PP_PLUGIN_PREFIX . 'selected_plans ' . SUMO_PP_PLUGIN_PREFIX . 'fields' ; ?>" style="<?php echo $inline_style ; ?>">
            <tbody class="selected_plans">
            <?php
            if( is_array( $selected_datas ) && ! empty( $selected_datas ) ) {
                foreach( $selected_datas as $row_id => $selected_data ) {
                    $selected_plan_field = '<tr><td class="sort" width="1%"></td><td>' ;
                    $selected_plan_field .= _sumo_pp_wc_search_field( array(
                        'class'       => 'wc-product-search' ,
                        'action'      => '_sumo_pp_json_search_payment_plans' ,
                        'id'          => "selected_{$column_id}_payment_plan_{$row_id}" ,
                        'name'        => "_sumo_pp_selected_plans[{$column_id}][{$row_id}]" ,
                        'type'        => 'payment_plans' ,
                        'multiple'    => false ,
                        'options'     => ( array ) $selected_data ,
                        'placeholder' => __( 'Search for a payment plan&hellip;' , SUMO_PP_PLUGIN_TEXT_DOMAIN ) ,
                    ) , false ) ;
                    $selected_plan_field .= '</td><td>' ;
                    $selected_plan_field .= '<a href="#" class="remove_row button">X</a>' ;
                    $selected_plan_field .= '</td></tr>' ;
                    echo $selected_plan_field ;
                }
            }
            ?>
            </tbody>
        </table>
        <?php
    }
}