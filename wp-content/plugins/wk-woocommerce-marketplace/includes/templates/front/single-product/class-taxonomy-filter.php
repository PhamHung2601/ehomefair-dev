<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seller product category
 */
class MpProductCategoryTree extends Walker {

	var $tree_type = 'category';

	var $db_fields = array('parent' => 'parent', 'id' => 'term_id');

	/**
	 * Constructoe function.
	 *
	 * @param array $allowed_cat allowed category for seller.
	 */
	public function __construct( $allowed_cat = array() ) {
		$this->allowed_categories = $allowed_cat;
	}

	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		$pad      = str_repeat( '&nbsp;', $depth * 3 );
		$cat_name = apply_filters( 'list_cats', $category->name, $category );

		if ( $this->allowed_categories ) :
			if ( in_array( $category->slug, $this->allowed_categories ) ) :
				$output .= "\t<option class=\"level-$depth\" value=\"" . $category->slug . "\"";
				if ( in_array( $category->slug, $args['selected'] ) ) {
					$output .= ' selected="selected"';
				}
				$output .= '>';
				$output .= $pad . $cat_name;
				$output .= "</option>\n";
			endif;
		else :
			$output .= "\t<option class=\"level-$depth\" value=\"" . $category->slug . "\"";
			if ( in_array( $category->slug, $args['selected'] ) ) {
				$output .= ' selected="selected"';
			}
			$output .= '>';
			$output .= $pad . $cat_name;
			$output .= "</option>\n";
		endif;
	}
}
