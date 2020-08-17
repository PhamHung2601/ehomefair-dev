<?php
/**
 * Ovic Shortcode setup
 *
 * @author   KHANH
 * @category API
 * @package  Ovic_Shortcode
 * @since    1.0.0
 */
if ( !class_exists( 'Ovic_Shortcode' ) ) {
	class Ovic_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = '';
		/**
		 * Register shortcode with WordPress.
		 *
		 * @return  void
		 */
		/**
		 * Meta key.
		 *
		 * @var  string
		 */
		protected $css_key = '_Ovic_Shortcode_custom_css';

		public function __construct()
		{
			if ( !empty( $this->shortcode ) ) {
				add_shortcode( "ovic_{$this->shortcode}", array( $this, 'output_html' ) );
			}
			if ( class_exists( 'Vc_Manager' ) )
				add_action( 'save_post', array( $this, 'update_post' ) );
		}

		/**
		 * Replace and save custom css to post meta.
		 *
		 * @param   int $post_id
		 *
		 * @return  void
		 */
		public function update_post( $post_id )
		{
			if ( !wp_is_post_revision( $post_id ) ) {
				// Set and replace content.
				$post = $this->replace_post( $post_id );
				if ( $post ) {
					// Generate custom CSS.
					$css = $this->OvicShortcodesCustomCss( $post->post_content );
					// Update post and save CSS to post meta.
					$this->save_post( $post );
					$this->save_css_postmeta( $post_id, $css );
					do_action( 'ovic_save_post', $post_id );
				} else {
					$this->save_css_postmeta( $post_id, '' );
				}
			}
		}

		/**
		 * Replace shortcode used in a post with real content.
		 *
		 * @param   int $post_id Post ID.
		 *
		 * @return  WP_Post object or null.
		 */
		public function replace_post( $post_id )
		{
			// Get post.
			$post = get_post( $post_id );
			if ( $post ) {
				$post->post_content = preg_replace_callback(
					'/(ovic_custom_id)="[^"]+"/',
					array( $this, 'ovic_shortcode_replace_post_callback' ),
					$post->post_content
				);
			}

			return $post;
		}

		function ovic_shortcode_replace_post_callback( $matches )
		{
			// Generate a random string to use as element ID.
			$id = 'ovic_custom_' . uniqid();

			return $matches[1] . '="' . $id . '"';
		}

		/**
		 * Parse shortcode custom css string.
		 *
		 * @param   string $content
		 * @return  string
		 */
		public function OvicShortcodesCustomCss( $content )
		{
			$css = '';
			WPBMap::addAllMappedShortcodes();
			if ( preg_match_all( '/' . get_shortcode_regex() . '/', $content, $shortcodes ) ) {
				foreach ( $shortcodes[2] as $index => $tag ) {
					$atts      = shortcode_parse_atts( trim( $shortcodes[3][$index] ) );
					$shortcode = explode( '_', $tag );
					$shortcode = end( $shortcode );
					if ( strpos( $tag, 'ovic_' ) !== false ) {
						$class = 'Ovic_Shortcode_' . implode( '_', array_map( 'ucfirst', explode( '-', $shortcode ) ) );
						if ( class_exists( $class ) ) {
							$css .= $class::add_css_generate( $atts );
						}
					}
					$css .= self::add_css_editor( $atts, $tag );
				}
				foreach ( $shortcodes[5] as $shortcode_content ) {
					$css .= self::OvicShortcodesCustomCss( $shortcode_content );
				}
			}

			return $css;
		}

		/**
		 * Update post data content.
		 *
		 * @param   $post WP_Post object.
		 *
		 * @return  void
		 */
		public function save_post( $post )
		{
			// Update post content.
			global $wpdb;
			$wpdb->update(
				$wpdb->posts,
				array(
					'post_content' => $post->post_content,    // string
				),
				array(
					'ID' => $post->ID,
				),
				array( '%s' ),
				array( '%d' )
			);
			// Update post cache.
			wp_cache_replace( $post->ID, $post, 'posts' );
		}

		/**
		 * Update extra post meta.
		 *
		 * @param   int $post_id Post ID.
		 * @param   string $css Custom CSS.
		 *
		 * @return  void
		 */
		public function save_css_postmeta( $post_id, $css )
		{
			if ( $post_id && $this->css_key ) {
				if ( empty( $css ) ) {
					delete_post_meta( $post_id, $this->css_key );
				} else {
					update_post_meta( $post_id, $this->css_key, preg_replace( '/[\t\r\n]/', '', $css ) );
				}
			}
		}

		/**
		 * Generate custom CSS.
		 *
		 * @param   array $atts Shortcode parameters.
		 *
		 * @return  string
		 */
		static public function add_css_generate( $atts )
		{
			return '';
		}

		function ovic_title_shortcode( $title )
		{
			$html = '';
			$html .= '<h3 class="ovic-title">';
			$html .= '<span class="text">' . esc_html( $title ) . '</span>';
			$html .= '</h3>';
			echo apply_filters( 'ovic_custom_title_shortcode', $html, $title );
		}

		public function generate_style_font( $container_data )
		{
			$style_font_data     = array();
			$styles              = array();
			$font_container_data = explode( '|', $container_data );
			foreach ( $font_container_data as $value ) {
				if ( $value != '' ) {
					$data_style                      = explode( ':', $value );
					$style_font_data[$data_style[0]] = $data_style[1];
				}
			}
			foreach ( $style_font_data as $key => $value ) {
				if ( 'tag' !== $key && strlen( $value ) ) {
					if ( preg_match( '/description/', $key ) ) {
						continue;
					}
					if ( 'font_size' === $key || 'line_height' === $key ) {
						$value = preg_replace( '/\s+/', '', $value );
					}
					if ( 'font_size' === $key ) {
						$pattern = '/^(\d*(?:\.\d+)?)\s*(px|\%|in|cm|mm|em|rem|ex|pt|pc|vw|vh|vmin|vmax)?$/';
						// allowed metrics: http://www.w3schools.com/cssref/css_units.asp
						$regexr = preg_match( $pattern, $value, $matches );
						$value  = isset( $matches[1] ) ? (float)$matches[1] : (float)$value;
						$unit   = isset( $matches[2] ) ? $matches[2] : 'px';
						$value  = $value . $unit;
					}
					if ( strlen( $value ) > 0 ) {
						$styles[] = str_replace( '_', '-', $key ) . ': ' . urldecode( $value );
					}
				}
			}

			return !empty( $styles ) ? implode( ' !important;', $styles ) . ' !important;' : '';
		}

		public function get_google_font_data( $atts, $key = 'google_fonts' )
		{
			extract( $atts );
			$google_fonts_field          = WPBMap::getParam( "ovic_{$this->shortcode}", $key );
			$google_fonts_obj            = new Vc_Google_Fonts();
			$google_fonts_field_settings = isset( $google_fonts_field['settings'], $google_fonts_field['settings']['fields'] ) ? $google_fonts_field['settings']['fields'] : array();
			$google_fonts_data           = strlen( $atts[$key] ) > 0 ? $google_fonts_obj->_vc_google_fonts_parse_attributes( $google_fonts_field_settings, $atts[$key] ) : '';

			return $google_fonts_data;
		}

		public function add_css_editor( $atts, $tag )
		{
			$css       = '';
			$inner_css = '';
			if ( $tag == 'vc_column' || $tag == 'vc_column_inner' ) {
				$inner_css = ' > .vc_column-inner';
			}
			$editor_names = Ovic_Visual_composer::ovic_responsive_vc_data();
			/* generate main css */
			if ( isset( $atts['css'] ) && $atts['css'] != '' )
				$css .= str_replace( "{", "{$inner_css}{", $atts['css'] );
			if ( !empty( $editor_names ) && isset( $atts['ovic_custom_id'] ) ) {
				arsort( $editor_names );
				$shortcode_id = '.' . $atts['ovic_custom_id'];
				foreach ( $editor_names as $key => $data ) {
					$generate_css = '';
					if ( isset( $atts["width_unit_{$key}"] ) ) {
						$unit_css_{$key} = $atts["width_unit_{$key}"] != 'none' ? $atts["width_unit_{$key}"] : '';
					} else {
						$unit_css_{$key} = '%';
					}
					/* SCREEN CSS */
					if ( isset( $atts["css_{$key}"] ) && $atts["css_{$key}"] != '' )
						$generate_css .= str_replace( "{", "{$inner_css}{", $atts["css_{$key}"] );
					/* FONT CSS */
					if ( isset( $atts["responsive_font_{$key}"] ) && $this->generate_style_font( $atts["responsive_font_{$key}"] ) != '' )
						$generate_css .= "{$shortcode_id}{$inner_css}{{$this->generate_style_font( $atts["responsive_font_{$key}"] )}}";
					/* STYLE WIDTH CSS */
					if ( isset( $atts["width_rows_{$key}"] ) && $atts["width_rows_{$key}"] != '' )
						$generate_css .= "{$shortcode_id}{width: {$atts["width_rows_{$key}"]}{$unit_css_{$key}} !important}";
					/* DISABLE BACKGROUND CSS */
					if ( isset( $atts["disable_bg_{$key}"] ) && $atts["disable_bg_{$key}"] == 'yes' )
						$generate_css .= "{$shortcode_id}{$inner_css}{background-image: none !important;}";
					/* LETTER SPACING CSS */
					if ( isset( $atts["letter_spacing_{$key}"] ) && $atts["letter_spacing_{$key}"] != '' )
						$generate_css .= "{$shortcode_id}{$inner_css}{letter-spacing: {$atts["letter_spacing_{$key}"]} !important;}";
					/* GOOGLE FONT */
					$google_fonts_data = array();
					if ( isset( $atts["google_fonts_{$key}"] ) )
						$google_fonts_data = Ovic_Visual_composer::get_google_font_data( $tag, $atts, "google_fonts_{$key}" );
					if ( ( !isset( $atts["use_theme_fonts_{$key}"] ) || 'yes' !== $atts["use_theme_fonts_{$key}"] ) && !empty( $google_fonts_data ) && isset( $google_fonts_data['values'], $google_fonts_data['values']['font_family'], $google_fonts_data['values']['font_style'] ) ) {
						$google_fonts_family = explode( ':', $google_fonts_data['values']['font_family'] );
						$styles              = array();
						$styles[]            = 'font-family:' . $google_fonts_family[0];
						$google_fonts_styles = explode( ':', $google_fonts_data['values']['font_style'] );
						$styles[]            = 'font-weight:' . $google_fonts_styles[1];
						$styles[]            = 'font-style:' . $google_fonts_styles[2];
						if ( !empty( $styles ) ) {
							$generate_css .= "{$shortcode_id}{$inner_css}{" . implode( ';', $styles ) . "}";
						}
					}
					/* CUSTOM CSS */
					if ( isset( $atts["custom_css_{$key}"] ) ) {
						$custom_css   = trim( strip_tags( $atts["custom_css_{$key}"] ) );
						$generate_css .= "{$shortcode_id}{$inner_css}{{$custom_css}}";
					}
					/* GERENERATE MEDIA */
					if ( $generate_css != '' ) {
						if ( $data['screen'] < 999999 ) {
							$css .= "@media (max-width: {$data['screen']}px){{$generate_css}}";
						} else {
							$css .= $generate_css;
						}
					}
				}
			}

			return $css;
		}

		public function output_html( $atts, $content = null )
		{
			return '';
		}

		/* do_action( 'vc_enqueue_font_icon_element', $font ); // hook to custom do enqueue style */
		function constructIcon( $section )
		{
			vc_icon_element_fonts_enqueue( $section['i_type'] );
			$class = 'vc_tta-icon';
			if ( isset( $section['i_icon_' . $section['i_type']] ) ) {
				$class .= ' ' . $section['i_icon_' . $section['i_type']];
			} else {
				$class .= ' fa fa-adjust';
			}

			return '<i class="' . $class . '"></i>';
		}

		public static function convertAttributesToNewProgressBar( $atts )
		{
			if ( isset( $atts['values'] ) && strlen( $atts['values'] ) > 0 ) {
				$values = vc_param_group_parse_atts( $atts['values'] );
				if ( !is_array( $values ) ) {
					$temp        = explode( ',', $atts['values'] );
					$paramValues = array();
					foreach ( $temp as $value ) {
						$data               = explode( '|', $value );
						$colorIndex         = 2;
						$newLine            = array();
						$newLine['percent'] = isset( $data[0] ) ? $data[0] : 0;
						$newLine['title']   = isset( $data[1] ) ? $data[1] : '';
						if ( isset( $data[1] ) && preg_match( '/^\d{1,3}\%$/', $data[1] ) ) {
							$colorIndex         += 1;
							$newLine['percent'] = (float)str_replace( '%', '', $data[1] );
							$newLine['title']   = isset( $data[2] ) ? $data[2] : '';
						}
						if ( isset( $data[$colorIndex] ) ) {
							$newLine['customcolor'] = $data[$colorIndex];
						}
						$paramValues[] = $newLine;
					}
					$atts['values'] = urlencode( json_encode( $paramValues ) );
				}
			}

			return $atts;
		}

		function get_all_attributes( $tag, $text )
		{
			preg_match_all( '/' . get_shortcode_regex() . '/s', $text, $matches );
			$out               = array();
			$shortcode_content = array();
			if ( isset( $matches[5] ) ) {
				$shortcode_content = $matches[5];
			}
			if ( isset( $matches[2] ) ) {
				$i = 0;
				foreach ( (array)$matches[2] as $key => $value ) {
					if ( $tag === $value ) {
						$out[$i]            = shortcode_parse_atts( $matches[3][$key] );
						$out[$i]['content'] = $matches[5][$key];
					}
					$i++;
				}
			}

			return $out;
		}
	}

	new Ovic_Shortcode();
}