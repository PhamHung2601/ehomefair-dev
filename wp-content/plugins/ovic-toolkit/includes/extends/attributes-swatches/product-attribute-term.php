<?php
if ( !class_exists( 'Ovic_Term' ) ) {
	class Ovic_Term
	{
		public $attribute_meta_key;
		public $term_id;
		public $term;
		public $term_label;
		public $term_slug;
		public $taxonomy_slug;
		public $selected;
		public $type;
		public $color;
		public $thumbnail_src;
		public $thumbnail_id;

		public function __construct( $attribute_data_key, $term_id, $taxonomy, $selected = false )
		{
			$this->attribute_meta_key = $attribute_data_key;
			$this->term_id            = $term_id;
			$this->term               = get_term( $term_id, $taxonomy );
			$this->term_label         = $this->term->name;
			$this->term_slug          = $this->term->slug;
			$this->taxonomy_slug      = $taxonomy;
			$this->selected           = $selected;
			$this->on_init();
		}

		public function on_init()
		{
			$type                = get_term_meta( $this->term_id, $this->meta_key() . '_type', true );
			$color               = get_term_meta( $this->term_id, $this->meta_key() . '_color', true );
			$this->thumbnail_id  = get_term_meta( $this->term_id, $this->meta_key() . '_photo', true );
			$this->thumbnail_src = wc_placeholder_img_src();
			$this->color         = '#FFFFFF';
			$this->type          = $type;
			if ( $type == 'photo' ) {
				if ( $this->thumbnail_id ) {
					$imgsrc = wp_get_attachment_image_src( $this->thumbnail_id, array( 40, 40 ) );
					if ( $imgsrc ) {
						$this->thumbnail_src = current( $imgsrc );
					} else {
						$this->thumbnail_src = wc_placeholder_img_src();
					}
				} else {
					$this->thumbnail_src = wc_placeholder_img_src();
				}
			} elseif ( $type == 'color' ) {
				$this->color = $color;
			}
		}

		public function get_output( $placeholder = true, $placeholder_src = 'default' )
		{
			$picker       = '';
			$href         = apply_filters( 'woocommerce_swatches_get_swatch_href', '#', $this );
			$anchor_class = apply_filters( 'woocommerce_swatches_get_swatch_anchor_css_class', 'swatch-anchor', $this );
			$image_class  = apply_filters( 'woocommerce_swatches_get_swatch_image_css_class', 'swatch-img', $this );
			$image_alt    = apply_filters( 'woocommerce_swatches_get_swatch_image_alt', 'thumbnail', $this );
			if ( $this->type == 'photo' || $this->type == 'image' ) {
				$picker .= '<a href="' . $href . '" style="display:block;width:40px;height:40px;" title="' . $this->term_label . '" class="' . $anchor_class . '">';
				$picker .= '<img src="' . apply_filters( 'woocommerce_swatches_get_swatch_image', $this->thumbnail_src, $this->term_slug, $this->taxonomy_slug, $this ) . '" alt="' . $image_alt . '" style="margin:0;" class="wp-post-image swatch-photo' . $this->meta_key() . ' ' . $image_class . '"/>';
				$picker .= '</a>';
			} elseif ( $this->type == 'color' ) {
				$picker .= '<a href="' . $href . '" style="font-size:0;display:block;width:40px;height:40px;background-color:' . apply_filters( 'woocommerce_swatches_get_swatch_color', $this->color, $this->term_slug, $this->taxonomy_slug, $this ) . ';" title="' . $this->term_label . '" class="' . $anchor_class . '">' . $this->term_label . '</a>';
			} elseif ( $placeholder ) {
				if ( $placeholder_src == 'default' ) {
					$src = wc_placeholder_img_src();
				} else {
					$src = $placeholder_src;
				}
				$picker .= '<a href="' . $href . '" style="display:block;width:40px;height:40px;" title="' . $this->term_label . '"  class="' . $anchor_class . '">';
				$picker .= '<img src="' . $src . '" alt="' . $image_alt . '" style="margin:0;" class="wp-post-image swatch-photo ' . $this->meta_key() . ' ' . $image_class . '"/>';
				$picker .= '</a>';
			} else {
				return '';
			}

			return apply_filters( 'woocommerce_swatches_picker_html', $picker, $this );
		}

		public function get_type()
		{
			return $this->type;
		}

		public function get_color()
		{
			return $this->color;
		}

		public function get_image_src()
		{
			return $this->thumbnail_src;
		}

		public function get_image_id()
		{
			return $this->thumbnail_id;
		}

		public function meta_key()
		{
			return $this->taxonomy_slug . '_' . $this->attribute_meta_key;
		}
	}
}
