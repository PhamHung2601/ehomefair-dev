<?php
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}
/**
 * Shortcode attributes
 * @var $atts
 * Shortcode class
 * @var $this "Ovic_Shortcode_Map"
 * @version 1.0.0
 */
if ( !class_exists( 'Ovic_Shortcode_Map' ) ) {
	class Ovic_Shortcode_Map extends Ovic_Shortcode
	{
		/**
		 * Shortcode name.
		 *
		 * @var  string
		 */
		public $shortcode = 'map';

		static public function add_css_generate( $atts )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_map', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css = '';

			return apply_filters( 'Ovic_Shortcode_Map_css', $css, $atts );
		}

		public function output_html( $atts, $content = null )
		{
			$atts = function_exists( 'vc_map_get_attributes' ) ? vc_map_get_attributes( 'ovic_map', $atts ) : $atts;
			// Extract shortcode parameters.
			extract( $atts );
			$css_class   = array( 'ovic-google-maps' );
			$css_class[] = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, '', 'ovic_map', $atts );
			ob_start();
			$id = uniqid();
			?>
            <div class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>"
                 id="az-google-maps-<?php echo esc_attr( $id ); ?>"
                 style="min-height: <?php echo esc_attr( $atts['map_height'] ); ?>px">
            </div>
            <script type="text/javascript">
                window.addEventListener('load',
                    function (ev) {
                        if ( typeof google === 'object' && typeof google.maps === 'object' ) {
                            var $hue             = '',
                                $saturation      = '',
                                $modify_coloring = false,
                                $ovic_map        = {
                                    lat: <?php echo esc_attr( $atts['latitude'] ); ?>,
                                    lng: <?php echo esc_attr( $atts['longitude'] ) ?>
                                };
                            if ( $modify_coloring === true ) {
                                var $styles = [
                                    {
                                        stylers: [
                                            {hue: $hue},
                                            {invert_lightness: false},
                                            {saturation: $saturation},
                                            {lightness: 1},
                                            {
                                                featureType: "landscape.man_made",
                                                stylers: [ {
                                                    visibility: "on"
                                                } ]
                                            }
                                        ]
                                    }, {
                                        featureType: 'water',
                                        elementType: 'geometry',
                                        stylers: [
                                            {color: '#46bcec'}
                                        ]
                                    }
                                ];
                            }
                            var map = new google.maps.Map(document.getElementById("az-google-maps-<?php echo esc_attr( $id ); ?>"), {
                                zoom: <?php echo esc_attr( $atts['zoom'] ) ?>,
                                center: $ovic_map,
                                mapTypeId: google.maps.MapTypeId.<?php echo esc_attr( $atts['map_type'] ) ?>,
                                styles: $styles
                            });

                            var contentString = '<div style="background-color:#fff; padding: 30px 30px 10px 25px; width:290px;line-height: 22px" class="ovic-map-info">' +
                                '<h4 class="map-title"><?php echo esc_html( $atts['title'] ) ?></h4>' +
                                '<div class="map-field"><i class="fa fa-map-marker"></i><span><?php echo esc_html( $atts['address'] ) ?></span></div>' +
                                '<div class="map-field"><i class="fa fa-phone"></i><span><a href="tel:<?php echo esc_html( $atts['phone'] ) ?>"><?php echo esc_html( $atts['phone'] ) ?></a></span></div>' +
                                '<div class="map-field"><i class="fa fa-envelope"></i><span><a href="mailto:<?php echo esc_html( $atts['email'] ) ?>"><?php echo esc_html( $atts['email'] ) ?></a></span></div> ' +
                                '</div>';

                            var infowindow = new google.maps.InfoWindow({
                                content: contentString
                            });

                            var marker = new google.maps.Marker({
                                position: $ovic_map,
                                map: map
                            });
                            marker.addListener('click', function () {
                                infowindow.open(map, marker);
                            });
                        }
                    }, false);
            </script>
			<?php
			$html = ob_get_clean();

			return apply_filters( 'Ovic_Shortcode_Map', $html, $atts, $content );
		}
	}

	new Ovic_Shortcode_Map();
}