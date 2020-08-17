<?php
// Prevent direct access to this file
defined( 'ABSPATH' ) || die( 'Direct access to this file is not allowed.' );
/**
 * Core class.
 *
 * @package  Ovic
 * @since    1.0
 */
if ( !class_exists( 'OvicRevSlider' ) ) {
	class OvicRevSlider extends RevSliderSlider
	{
		private $slider_version = 5;
		private $id;
		private $title;
		private $alias;
		private $arrParams;
		private $settings;
		private $arrSlides      = array();

		public function __construct()
		{
			parent::__construct();
		}

		/**
		 *
		 * get data functions
		 */
		public function getTitle()
		{
			return ( $this->title );
		}

		public function getID()
		{
			return ( $this->id );
		}

		public function getParams()
		{
			return ( $this->arrParams );
		}

		/*
		 * return Slider settings
		 * @since: 5.0
		 */
		public function getSettings()
		{
			return ( $this->settings );
		}

		/*
		 * return true if slider is favorite
		 * @since: 5.0
		 */
		public function isFavorite()
		{
			if ( !empty( $this->settings ) ) {
				if ( isset( $this->settings['favorite'] ) && $this->settings['favorite'] == 'true' ) return true;
			}

			return false;
		}

		/**
		 * get sliders array - function don't belong to the object!
		 */
		public function getArrSliders( $orders = false, $templates = 'neither' )
		{
			$order_fav = false;
			if ( $orders !== false && key( $orders ) != 'favorite' ) {
				$order_direction = reset( $orders );
				$do_order        = key( $orders );
			} else {
				$do_order        = 'id';
				$order_direction = 'ASC';
				if ( is_array( $orders ) && key( $orders ) == 'favorite' ) {
					$order_direction = reset( $orders );
					$order_fav       = true;
				}
			}
			//$where = "`type` != 'template' ";
			$where = "`type` != 'template' OR `type` IS NULL";

			$response = $this->db->fetch( RevSliderGlobals::$table_sliders, $where, $do_order, '', $order_direction );

			$arrSliders = array();
			foreach ( $response as $arrData ) {
				$slider = new RevSlider();
				$slider->initByDBData( $arrData );

				/*
				This part needs to stay for backwards compatibility. It is used in the update process from v4x to v5x
				*/
				if ( $templates === true ) {
					if ( $slider->getParam( "template", "false" ) == "false" ) continue;
				} elseif ( $templates === false ) {
					if ( $slider->getParam( "template", "false" ) == "true" ) continue;
				}

				$arrSliders[] = $slider;
			}

			if ( $order_fav === true ) {
				$temp     = array();
				$temp_not = array();
				foreach ( $arrSliders as $key => $slider ) {
					if ( $slider->isFavorite() ) {
						$temp_not[] = $slider;
					} else {
						$temp[] = $slider;
					}
				}
				$arrSliders = array();
				$arrSliders = ( $order_direction == 'ASC' ) ? array_merge( $temp, $temp_not ) : array_merge( $temp_not, $temp );
			}

			return ( $arrSliders );
		}

		/**
		 * init slider by db data
		 */
		public function initByDBData( $arrData )
		{
			$this->id    = $arrData["id"];
			$this->title = $arrData["title"];
			$this->alias = $arrData["alias"];

			$settings = $arrData["settings"];
			$settings = (array)json_decode( $settings );

			$this->settings = $settings;

			$params = $arrData["params"];
			$params = (array)json_decode( $params );
			$params = RevSliderBase::translate_settings_to_v5( $params );

			$this->arrParams = $params;
		}

		/**
		 *
		 * init the slider object by database id
		 */
		public function initByID( $sliderID )
		{
			RevSliderFunctions::validateNumeric( $sliderID, "Slider ID" );

			try {
				$sliderData = $this->db->fetchSingle( RevSliderGlobals::$table_sliders, $this->db->prepare( "id = %s", array( $sliderID ) ) );
			} catch ( Exception $e ) {
				$message = $e->getMessage();
				echo $message;
				exit;
			}

			$this->initByDBData( $sliderData );
		}

		/**
		 * get slides of the current slider
		 */
		public function getSlidesFromGallery( $publishedOnly = false, $allwpml = false, $first = false )
		{
			//global $rs_slide_template;
			$this->validateInited();

			$arrSlides       = array();
			$arrSlideRecords = $this->db->fetch( RevSliderGlobals::$table_slides, $this->db->prepare( "slider_id = %s", array( $this->id ) ), "slide_order" );

			//add Slides set by postsettings, so slide_template
			/*if(!empty($rs_slide_template)){
				foreach($rs_slide_template as $rs_s_t){
					$rs_s_t_d = $this->db->fetch(RevSliderGlobals::$table_slides,$this->db->prepare("id = %s", array($rs_s_t)),"slide_order");
					foreach($rs_s_t_d as $rs_s_t_d_v){
						$arrSlideRecords[] = $rs_s_t_d_v;
					}
				}
			}*/

			$arrChildren = array();

			foreach ( $arrSlideRecords as $record ) {
				$slide = new RevSlide();
				$slide->initByData( $record );

				$slideID               = $slide->getID();
				$arrIdsAssoc[$slideID] = true;

				if ( $publishedOnly == true ) {
					$state = $slide->getParam( "state", "published" );
					if ( $state == "unpublished" ) {
						continue;
					}
				}

				$parentID = $slide->getParam( "parentid", "" );
				if ( !empty( $parentID ) ) {
					$lang = $slide->getParam( "lang", "" );
					if ( !isset( $arrChildren[$parentID] ) )
						$arrChildren[$parentID] = array();
					$arrChildren[$parentID][] = $slide;
					if ( !$allwpml )
						continue;    //skip adding to main list
				}

				//init the children array
				$slide->setArrChildren( array() );

				$arrSlides[$slideID] = $slide;

				if ( $first ) break; //we only want the first slide!
			}

			//add children array to the parent slides
			foreach ( $arrChildren as $parentID => $arr ) {
				if ( !isset( $arrSlides[$parentID] ) ) {
					continue;
				}
				$arrSlides[$parentID]->setArrChildren( $arr );
			}

			$this->arrSlides = $arrSlides;

			return ( $arrSlides );
		}

		/**
		 *
		 * validate that the slider is inited. if not - throw error
		 */
		private function validateInited()
		{
			if ( empty( $this->id ) )
				RevSliderFunctions::throwError( "The slider is not initialized!" );
		}

		/**
		 *
		 * get slider params for export slider
		 */
		private function getParamsForExport()
		{
			$exportParams = $this->arrParams;

			//modify background image
			$urlImage = RevSliderFunctions::getVal( $exportParams, "background_image" );
			if ( !empty( $urlImage ) )
				$exportParams["background_image"] = $urlImage;

			return ( $exportParams );
		}

		/**
		 *
		 * get slides for export
		 */
		public function getSlidesForExport( $useDummy = false )
		{
			$arrSlides       = $this->getSlidesFromGallery( false, true );
			$arrSlidesExport = array();

			foreach ( $arrSlides as $slide ) {
				$slideNew                = array();
				$slideNew["id"]          = $slide->getID();
				$slideNew["params"]      = $slide->getParamsForExport();
				$slideNew["slide_order"] = $slide->getOrder();
				$slideNew["layers"]      = $slide->getLayersForExport( $useDummy );
				$slideNew["settings"]    = $slide->getSettings();

				$arrSlidesExport[] = $slideNew;
			}

			return apply_filters( 'revslider_getSlidesForExport', $arrSlidesExport );
		}

		/**
		 *
		 * get slides for export
		 */
		public function getStaticSlideForExport( $useDummy = false )
		{
			$arrSlidesExport = array();

			$slide = new RevSlide();

			$staticID = $slide->getStaticSlideID( $this->id );
			if ( $staticID !== false ) {
				$slideNew = array();
				$slide->initByStaticID( $staticID );
				$slideNew["params"]      = $slide->getParamsForExport();
				$slideNew["slide_order"] = $slide->getOrder();
				$slideNew["layers"]      = $slide->getLayersForExport( $useDummy );
				$slideNew["settings"]    = $slide->getSettings();
				$arrSlidesExport[]       = $slideNew;
			}

			return apply_filters( 'revslider_getStaticSlideForExport', $arrSlidesExport );
		}

		/**
		 *
		 * export slider from data, output a file for download
		 */
		public function OvicExportSlider()
		{
			$this->validateInited();

			$sliderParams   = $this->getParamsForExport();
			$arrSlides      = $this->getSlidesForExport( $useDummy );
			$arrStaticSlide = $this->getStaticSlideForExport( $useDummy );

			$usedCaptions    = array();
			$usedAnimations  = array();
			$usedImages      = array();
			$usedSVG         = array();
			$usedVideos      = array();
			$usedNavigations = array();

			$cfw = array();
			if ( !empty( $arrSlides ) && count( $arrSlides ) > 0 ) $cfw = array_merge( $cfw, $arrSlides );
			if ( !empty( $arrStaticSlide ) && count( $arrStaticSlide ) > 0 ) $cfw = array_merge( $cfw, $arrStaticSlide );

			//remove image_id as it is not needed in export
			//plus remove background image if solid color or transparent
			if ( !empty( $arrSlides ) ) {
				foreach ( $arrSlides as $k => $s ) {
					if ( isset( $arrSlides[$k]['params']['image_id'] ) ) unset( $arrSlides[$k]['params']['image_id'] );
					if ( isset( $arrSlides[$k]['params']["background_type"] ) && ( $arrSlides[$k]['params']["background_type"] == 'solid' || $arrSlides[$k]['params']["background_type"] == "trans" || $arrSlides[$k]['params']["background_type"] == "transparent" ) ) {
						if ( isset( $arrSlides[$k]['params']['background_image'] ) )
							$arrSlides[$k]['params']['background_image'] = '';
					}
				}
			}
			if ( !empty( $arrStaticSlide ) ) {
				foreach ( $arrStaticSlide as $k => $s ) {
					if ( isset( $arrStaticSlide[$k]['params']['image_id'] ) ) unset( $arrStaticSlide[$k]['params']['image_id'] );
					if ( isset( $arrStaticSlide[$k]['params']["background_type"] ) && ( $arrStaticSlide[$k]['params']["background_type"] == 'solid' || $arrStaticSlide[$k]['params']["background_type"] == "trans" || $arrStaticSlide[$k]['params']["background_type"] == "transparent" ) ) {
						if ( isset( $arrStaticSlide[$k]['params']['background_image'] ) )
							$arrStaticSlide[$k]['params']['background_image'] = '';
					}
				}
			}

			if ( !empty( $cfw ) && count( $cfw ) > 0 ) {
				foreach ( $cfw as $key => $slide ) {
					//check if we are transparent and so on
					if ( isset( $slide['params']['image'] ) && $slide['params']['image'] != '' ) $usedImages[$slide['params']['image']] = true; //['params']['image'] background url
					if ( isset( $slide['params']['background_image'] ) && $slide['params']['background_image'] != '' ) $usedImages[$slide['params']['background_image']] = true; //['params']['image'] background url
					if ( isset( $slide['params']['slide_thumb'] ) && $slide['params']['slide_thumb'] != '' ) $usedImages[$slide['params']['slide_thumb']] = true; //['params']['image'] background url

					//html5 video
					if ( isset( $slide['params']['background_type'] ) && $slide['params']['background_type'] == 'html5' ) {
						if ( isset( $slide['params']['slide_bg_html_mpeg'] ) && $slide['params']['slide_bg_html_mpeg'] != '' ) $usedVideos[$slide['params']['slide_bg_html_mpeg']] = true;
						if ( isset( $slide['params']['slide_bg_html_webm'] ) && $slide['params']['slide_bg_html_webm'] != '' ) $usedVideos[$slide['params']['slide_bg_html_webm']] = true;
						if ( isset( $slide['params']['slide_bg_html_ogv'] ) && $slide['params']['slide_bg_html_ogv'] != '' ) $usedVideos[$slide['params']['slide_bg_html_ogv']] = true;
					} else {
						if ( isset( $slide['params']['slide_bg_html_mpeg'] ) && $slide['params']['slide_bg_html_mpeg'] != '' ) $slide['params']['slide_bg_html_mpeg'] = '';
						if ( isset( $slide['params']['slide_bg_html_webm'] ) && $slide['params']['slide_bg_html_webm'] != '' ) $slide['params']['slide_bg_html_webm'] = '';
						if ( isset( $slide['params']['slide_bg_html_ogv'] ) && $slide['params']['slide_bg_html_ogv'] != '' ) $slide['params']['slide_bg_html_ogv'] = '';
					}

					//image thumbnail
					if ( isset( $slide['layers'] ) && !empty( $slide['layers'] ) && count( $slide['layers'] ) > 0 ) {
						foreach ( $slide['layers'] as $lKey => $layer ) {
							if ( isset( $layer['style'] ) && $layer['style'] != '' ) $usedCaptions[$layer['style']] = true;
							if ( isset( $layer['animation'] ) && $layer['animation'] != '' && strpos( $layer['animation'], 'customin' ) !== false ) $usedAnimations[str_replace( 'customin-', '', $layer['animation'] )] = true;
							if ( isset( $layer['endanimation'] ) && $layer['endanimation'] != '' && strpos( $layer['endanimation'], 'customout' ) !== false ) $usedAnimations[str_replace( 'customout-', '', $layer['endanimation'] )] = true;
							if ( isset( $layer['image_url'] ) && $layer['image_url'] != '' ) $usedImages[$layer['image_url']] = true; //image_url if image caption
							if ( isset( $layer['bgimage_url'] ) && $layer['bgimage_url'] != '' ) $usedImages[$layer['bgimage_url']] = true; //image_url if background layer image

							if ( isset( $layer['type'] ) && ( $layer['type'] == 'video' || $layer['type'] == 'audio' ) ) {
								$video_data = ( isset( $layer['video_data'] ) ) ? (array)$layer['video_data'] : array();

								if ( !empty( $video_data ) && isset( $video_data['video_type'] ) && $video_data['video_type'] == 'html5' ) {
									if ( isset( $video_data['urlPoster'] ) && $video_data['urlPoster'] != '' ) $usedImages[$video_data['urlPoster']] = true;

									if ( isset( $video_data['urlMp4'] ) && $video_data['urlMp4'] != '' ) $usedVideos[$video_data['urlMp4']] = true;
									if ( isset( $video_data['urlWebm'] ) && $video_data['urlWebm'] != '' ) $usedVideos[$video_data['urlWebm']] = true;
									if ( isset( $video_data['urlOgv'] ) && $video_data['urlOgv'] != '' ) $usedVideos[$video_data['urlOgv']] = true;
								} elseif ( !empty( $video_data ) && isset( $video_data['video_type'] ) && $video_data['video_type'] != 'html5' ) { //video cover image
									if ( $video_data['video_type'] == 'audio' ) {
										if ( isset( $video_data['urlAudio'] ) && $video_data['urlAudio'] != '' ) $usedVideos[$video_data['urlAudio']] = true;
									} else {
										if ( isset( $video_data['previewimage'] ) && $video_data['previewimage'] != '' ) $usedImages[$video_data['previewimage']] = true;
									}
								}

								if ( $video_data['video_type'] != 'html5' ) {
									$video_data['urlMp4']  = '';
									$video_data['urlWebm'] = '';
									$video_data['urlOgv']  = '';
								}
								if ( $video_data['video_type'] != 'audio' ) {
									$video_data['urlAudio'] = '';
								}
								if ( isset( $layer['video_image_url'] ) && $layer['video_image_url'] != '' ) $usedImages[$layer['video_image_url']] = true;
							}

							if ( isset( $layer['type'] ) && $layer['type'] == 'svg' ) {
								if ( isset( $layer['svg'] ) && isset( $layer['svg']->src ) ) {
									$usedSVG[$layer['svg']->src] = true;
								}
							}
						}
					}
				}

				$d = array( 'usedSVG' => $usedSVG, 'usedImages' => $usedImages, 'usedVideos' => $usedVideos );
				$d = apply_filters( 'revslider_exportSlider_usedMedia', $d, $cfw, $sliderParams, $useDummy ); //  $arrSlides, $arrStaticSlide,

				$usedSVG    = $d['usedSVG'];
				$usedImages = $d['usedImages'];
				$usedVideos = $d['usedVideos'];
			}

			$arrSliderExport = array( "params" => $sliderParams, "slides" => $arrSlides );
			if ( !empty( $arrStaticSlide ) )
				$arrSliderExport['static_slides'] = $arrStaticSlide;

			$strExport = serialize( $arrSliderExport );

			//$strExportAnim = serialize(RevSliderOperations::getFullCustomAnimations());

			$exportname = ( !empty( $this->alias ) ) ? $this->alias . '.zip' : "slider_export.zip";

			RevSliderGlobals::$uploadsUrlExportZip = Ovic_Export_Data::$file_path . "/revsliders/{$exportname}";
			RevSliderGlobals::$uploadsUrlExportZip = Ovic_Export_Data::prepare_directory( RevSliderGlobals::$uploadsUrlExportZip );

			//add navigations if not default animation
			if ( isset( $sliderParams['navigation_arrow_style'] ) ) $usedNavigations[$sliderParams['navigation_arrow_style']] = true;
			if ( isset( $sliderParams['navigation_bullets_style'] ) ) $usedNavigations[$sliderParams['navigation_bullets_style']] = true;
			if ( isset( $sliderParams['thumbnails_style'] ) ) $usedNavigations[$sliderParams['thumbnails_style']] = true;
			if ( isset( $sliderParams['tabs_style'] ) ) $usedNavigations[$sliderParams['tabs_style']] = true;
			$navs = false;
			if ( !empty( $usedNavigations ) ) {
				$navs = RevSliderNavigation::export_navigation( $usedNavigations );
				if ( $navs !== false ) $navs = serialize( $navs );
			}

			$styles = '';
			if ( !empty( $usedCaptions ) ) {
				$captions = array();
				foreach ( $usedCaptions as $class => $val ) {
					$cap = RevSliderOperations::getCaptionsContentArray( $class );
					//set also advanced styles here...
					if ( !empty( $cap ) )
						$captions[] = $cap;
				}
				$styles = RevSliderCssParser::parseArrayToCss( $captions, "\n", true );
			}

			$animations = '';
			if ( !empty( $usedAnimations ) ) {
				$animation = array();
				foreach ( $usedAnimations as $anim => $val ) {
					$anima = RevSliderOperations::getFullCustomAnimationByID( $anim );
					if ( $anima !== false ) $animation[] = $anima;
				}
				if ( !empty( $animation ) ) $animations = serialize( $animation );
			}

			$usedImages = array_merge( $usedImages, $usedVideos );

			$usepcl = false;
			if ( class_exists( 'ZipArchive' ) ) {
				$zip     = new ZipArchive;
				$success = $zip->open( RevSliderGlobals::$uploadsUrlExportZip, ZIPARCHIVE::CREATE | ZipArchive::OVERWRITE );

				if ( $success !== true )
					throwError( "Can't create zip file: " . RevSliderGlobals::$uploadsUrlExportZip );
			} else {
				//fallback to pclzip
				require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );

				$pclzip = new PclZip( RevSliderGlobals::$uploadsUrlExportZip );

				//either the function uses die() or all is cool
				$usepcl = true;
			}

			//add svg to the zip
			if ( !empty( $usedSVG ) ) {
				$content_url  = content_url();
				$content_path = ABSPATH . 'wp-content';
				$ud           = wp_upload_dir();
				$up_dir       = $ud['baseurl'];
				foreach ( $usedSVG as $file => $val ) {
					if ( strpos( $file, 'http' ) !== false ) { //remove all up to wp-content folder
						$checkpath = str_replace( $content_url, '', $file );

						$checkpath2 = str_replace( $up_dir, '', $file );
						if ( $checkpath2 === $file ) { //we have an SVG like whiteboard, fallback to older export
							$checkpath2 = $checkpath;
						}
						if ( is_file( $content_path . $checkpath ) ) {
							$strExport = str_replace( $file, str_replace( '/revslider/assets/svg', '', $checkpath2 ), $strExport );
						}
					}
				}
			}

			//add images to zip
			if ( !empty( $usedImages ) ) {
				$upload_dir               = RevSliderFunctionsWP::getPathUploads();
				$upload_dir_multisiteless = wp_upload_dir();
				$cont_url                 = $upload_dir_multisiteless['baseurl'];
				$cont_url_no_www          = str_replace( 'www.', '', $upload_dir_multisiteless['baseurl'] );
				$upload_dir_multisiteless = $upload_dir_multisiteless['basedir'] . '/';

				foreach ( $usedImages as $file => $val ) {
					if ( $useDummy == "true" ) { //only use dummy images

					} else { //use the real images
						if ( strpos( $file, 'http' ) !== false ) {
							//check if we are in objects folder, if yes take the original image into the zip-

							$remove    = false;
							$checkpath = str_replace( array( $cont_url, $cont_url_no_www ), '', $file );

							if ( is_file( $upload_dir . $checkpath ) ) {
								if ( !$usepcl ) {
									$zip->addFile( $upload_dir . $checkpath, 'images/' . $checkpath );
								} else {
									$v_list = $pclzip->add( $upload_dir . $checkpath, PCLZIP_OPT_REMOVE_PATH, $upload_dir, PCLZIP_OPT_ADD_PATH, 'images/' );
								}
								$remove = true;
							} elseif ( is_file( $upload_dir_multisiteless . $checkpath ) ) {
								if ( !$usepcl ) {
									$zip->addFile( $upload_dir_multisiteless . $checkpath, 'images/' . $checkpath );
								} else {
									$v_list = $pclzip->add( $upload_dir_multisiteless . $checkpath, PCLZIP_OPT_REMOVE_PATH, $upload_dir_multisiteless, PCLZIP_OPT_ADD_PATH, 'images/' );
								}
								$remove = true;
							}

							if ( $remove ) { //as its http, remove this from strexport
								$strExport = str_replace( array( $cont_url . $checkpath, $cont_url_no_www . $checkpath ), $checkpath, $strExport );
							}
						} else {
							if ( is_file( $upload_dir . $file ) ) {
								if ( !$usepcl ) {
									$zip->addFile( $upload_dir . $file, 'images/' . $file );
								} else {
									$v_list = $pclzip->add( $upload_dir . $file, PCLZIP_OPT_REMOVE_PATH, $upload_dir, PCLZIP_OPT_ADD_PATH, 'images/' );
								}
							} elseif ( is_file( $upload_dir_multisiteless . $file ) ) {
								if ( !$usepcl ) {
									$zip->addFile( $upload_dir_multisiteless . $file, 'images/' . $file );
								} else {
									$v_list = $pclzip->add( $upload_dir_multisiteless . $file, PCLZIP_OPT_REMOVE_PATH, $upload_dir_multisiteless, PCLZIP_OPT_ADD_PATH, 'images/' );
								}
							}
						}
					}
				}
			}

			if ( !$usepcl ) {
				$zip->addFromString( "slider_export.txt", $strExport ); //add slider settings
			} else {
				$list = $pclzip->add( array( array( PCLZIP_ATT_FILE_NAME => 'slider_export.txt', PCLZIP_ATT_FILE_CONTENT => $strExport ) ) );
				if ( $list == 0 ) {
					die( "ERROR : '" . $pclzip->errorInfo( true ) . "'" );
				}
			}
			if ( strlen( trim( $animations ) ) > 0 ) {
				if ( !$usepcl ) {
					$zip->addFromString( "custom_animations.txt", $animations ); //add custom animations
				} else {
					$list = $pclzip->add( array( array( PCLZIP_ATT_FILE_NAME => 'custom_animations.txt', PCLZIP_ATT_FILE_CONTENT => $animations ) ) );
					if ( $list == 0 ) {
						die( "ERROR : '" . $pclzip->errorInfo( true ) . "'" );
					}
				}
			}
			if ( strlen( trim( $styles ) ) > 0 ) {
				if ( !$usepcl ) {
					$zip->addFromString( "dynamic-captions.css", $styles ); //add dynamic styles
				} else {
					$list = $pclzip->add( array( array( PCLZIP_ATT_FILE_NAME => 'dynamic-captions.css', PCLZIP_ATT_FILE_CONTENT => $styles ) ) );
					if ( $list == 0 ) {
						die( "ERROR : '" . $pclzip->errorInfo( true ) . "'" );
					}
				}
			}
			if ( strlen( trim( $navs ) ) > 0 ) {
				if ( !$usepcl ) {
					$zip->addFromString( "navigation.txt", $navs ); //add dynamic styles
				} else {
					$list = $pclzip->add( array( array( PCLZIP_ATT_FILE_NAME => 'navigation.txt', PCLZIP_ATT_FILE_CONTENT => $navs ) ) );
					if ( $list == 0 ) {
						die( "ERROR : '" . $pclzip->errorInfo( true ) . "'" );
					}
				}
			}

			$static_css = RevSliderOperations::getStaticCss();
			if ( trim( $static_css ) !== '' ) {
				if ( !$usepcl ) {
					$zip->addFromString( "static-captions.css", $static_css ); //add slider settings
				} else {
					$list = $pclzip->add( array( array( PCLZIP_ATT_FILE_NAME => 'static-captions.css', PCLZIP_ATT_FILE_CONTENT => $static_css ) ) );
					if ( $list == 0 ) {
						die( "ERROR : '" . $pclzip->errorInfo( true ) . "'" );
					}
				}
			}
			$enable_slider_pack = apply_filters( 'revslider_slider_pack_export', false );

			if ( $enable_slider_pack ) { //allow for slider packs the automatic creation of the info.cfg
				if ( !$usepcl ) {
					$zip->addFromString( 'info.cfg', md5( $this->alias ) ); //add slider settings
				} else {
					$list = $pclzip->add( array( array( PCLZIP_ATT_FILE_NAME => 'info.cfg', PCLZIP_ATT_FILE_CONTENT => md5( $this->alias ) ) ) );
					if ( $list == 0 ) {
						die( "ERROR : '" . $pclzip->errorInfo( true ) . "'" );
					}
				}
			}

			if ( !$usepcl ) {
				$zip->close();
			} else {
				//do nothing
			}
		}
	}

	new OvicRevSlider();
}