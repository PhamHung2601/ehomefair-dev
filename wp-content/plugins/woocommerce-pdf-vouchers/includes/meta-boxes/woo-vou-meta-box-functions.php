<?php 

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * get meta pages
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_get_meta_pages() {
	
	$args = array(WOO_VOU_MAIN_POST_TYPE);
	
	// Check for which post type we need to add the meta box
	if( $args == 'all' ) {
		$pages = get_post_types( array( 'public' => true ), 'names' );
	} else {
		$pages = $args;
	}
	
	return $pages;
}

/**
 * meta value
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_meta_value( $field ) {
	
	global $post;
	
	$post_id =  apply_filters( 'woo_vou_edit_product_id', $post->ID, $post );

	$option_name = $field['id'];
	if( !empty( $field['option_name'] ) ) {
		$option_name = $field['option_name'];
	}

	$meta = get_post_meta( $post_id, $option_name, true );


	$meta = ( isset( $meta ) ) ? $meta : '';

	if ( !empty( $field['key_name'] ) ) {

		if( !empty( $meta[$field['key_name']] ) ) {
			$meta = $meta[$field['key_name']];
		} else {
			$meta = '';
		}
	}

	if( !in_array( $field['type'], array( 'image', 'repeater', 'file', 'cond', 'fileadvanced' ) ) ) {
		$meta = is_array( $meta ) ? array_map( 'esc_attr', $meta ) : esc_attr( $meta );
	}

	
	return $meta;
}

/**
 * Begin Tab Content Field.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_content_begin( $echo = true ) {
	
	$html = '';
	
	$html .= '<table class="woo-vou-wrapper form-table">';
	
	$html .= '<tbody>';
	
	if( $echo ) {
		echo $html;
	} else {
		return $html;
	}
}

/**
 * End Tab Content Field.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_content_end( $echo = true ) {
	
	$html = '';
	
	$html .= '</tbody>';
	
	$html .= '</table>';
	
	if( $echo ) {
		echo $html;
	} else {
		return $html;
	}
}

/**
 * Begin Field.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_show_field_begin( $field ) {

	$field_begin = '';
	$field_wrap_class = isset( $field['wrap_class'] ) ? $field['wrap_class'] : '';
	
	$field_begin .= '<tr valign="top" class="' . $field_wrap_class . '">';
	$field_begin .= "<th>";
	
	if ( isset($field['name']) && !empty($field['name']) ) {
		$field_begin .= "<label for='{$field['id']}'>{$field['name']}</label>";
	}
	
	$field_begin .= '</th>';
	$field_begin .= '<td>';
	
	return $field_begin;
}

/**
 * End Field.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public 
 */
function woo_vou_show_field_end( $field, $meta=NULL ,$group = false) {

	$field_end = '';
	
	if ( isset($field['desc']) && !empty($field['desc']) ) {
		$field_end .= "<div class='woo-vou-meta-field-end'><span class='description'>{$field['desc']}</span></div></td>";
	} else {
		$field_end .= "</td>";
	}
	
	$field_end .= '</tr>';
	
	return $field_end;
}
	   
/**
 * Show Field Hidden.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_add_hidden( $args, $echo = true ) {  

	$html = '';
	
	$new_field = array( 'type' => 'hidden' );
	$field = array_merge( $new_field, $args );

	$meta = woo_vou_meta_value( $field );
	
	$html .= "<input type='hidden' class='regular-text' name='{$field['id']}' id='{$field['id']}' value='{$meta}'/>";

	if($echo) {
		echo $html;
	} else {
		return $html;
	}
	
} 
	   
/**
 * Show Field Text.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_add_text( $args, $echo = true ) {  
	
	$html = '';
	
	$new_field = array( 'type' => 'text', 'name' => 'Text Field', 'class' => 'regular-text' );
	$field = array_merge( $new_field, $args );
	
	$meta = woo_vou_meta_value( $field );
	
	$html .= woo_vou_show_field_begin( $field );
	
	$html .= "<input type='text' class='woo-vou-meta-text {$field['id']}' name='{$field['id']}' id='{$field['id']}' value='{$meta}' />";
	
	$html .= woo_vou_show_field_end( $field );
	
	if($echo) {
		echo $html;
	} else {
		return $html;
	}
	
} 

/**
 * Show Field Number.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_add_number( $args, $echo = true ) {  
	
	$html = '';
	
	$new_field = array( 'type' => 'number', 'name' => 'Number Field', 'field_desc' => '', 'class' => '', 'min' => '', 'max' => '' );
	$field = array_merge( $new_field, $args );
	
	$meta = woo_vou_meta_value( $field );

	$default_meta = isset( $field['default'] ) ? $field['default'] : '';
	$meta = !empty( $meta ) ? $meta : $default_meta;
	
	$html .= woo_vou_show_field_begin( $field );
	
	$html .= "<input type='number' class='woo-vou-meta-text {$field['class']}' name='{$field['id']}' id='{$field['id']}' value='{$meta}'";
	$html .= ( !empty($field['min']) ) ? " min='{$field['min']}'" : '' ;
	$html .= ( !empty($field['max']) ) ? " max='{$field['max']}'" : '' ;
	$html .= " />";
	$html .= $field['field_desc'];
	
	$html .= woo_vou_show_field_end( $field );
	
	if($echo) {
		echo $html;
	} else {
		return $html;
	}
	
} 

/**
 * Show Field Textarea.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_add_textarea( $args, $echo = true ) {
	
	$html = '';
	
	$new_field = array( 'type' => 'textarea', 'name' => 'Textarea Field' );
	$field = array_merge( $new_field, $args );

	$meta = woo_vou_meta_value( $field );
	
	$html .= woo_vou_show_field_begin( $field );
	
	$html .= "<textarea class='woo-vou-meta-textarea large-text' name='{$field['id']}' id='{$field['id']}' cols='60' rows='10'>{$meta}</textarea>";
	
	$html .= woo_vou_show_field_end( $field );
	
	if($echo) {
		echo $html;
	} else {
		return $html;
	}
	
}
	   
/**
 * Show Field Paragraph.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_add_paragraph( $args, $echo = true ) {  

	$html = '';
	
	$new_field = array( 'type' => 'paragraph', 'value' => '' );
	$field = array_merge( $new_field, $args );

	$html .= '<p>'.$field['value'].'</p>';

	if($echo) {
		echo $html;
	} else {
		return $html;
	}
	
} 

/**
 * Show Field Checkbox.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_add_checkbox( $args, $echo = true ) {
	
	$html = '';
	
	$new_field = array( 'type' => 'checkbox', 'name' => 'Checkbox Field' );
	$field = array_merge( $new_field, $args );

	$meta = woo_vou_meta_value( $field );
	
	$html .= woo_vou_show_field_begin( $field );
	
	$html .= "<input type='checkbox' class='woo-vou-meta-checkbox' name='{$field['id']}' id='{$field['id']}'" . checked(!empty($meta), true, false) . " />";
	
	$html .= woo_vou_show_field_end( $field );
	
	if($echo) {
		echo $html;
	} else {
		return $html;
	}
	
}

/**
 * Show Checkbox List Field.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_add_checkbox_list( $args, $echo = true ) {

	$html = '';
	
	$new_field = array( 'type' => 'checkbox_list', 'name' => 'Checkbox List Field' );
	$field = array_merge( $new_field, $args );

	$meta = woo_vou_meta_value( $field );
	
	if( ! is_array( $meta ) ) {
		$meta = (array) $meta;
	}

	$html .= woo_vou_show_field_begin( $field );
	
	$cb_html = array();
	
	foreach ($field['options'] as $key => $value) {
		$cb_html[] = "<input type='checkbox' class='woo-vou-meta-checkbox_list' name='{$field['id']}[]' value='{$key}'" . checked( in_array( $key, $meta ), true, false ) . " /> {$value}";
	}
	
	$html .= implode( '<br />' , $cb_html );
	  
	$html .= woo_vou_show_field_end( $field );
	
	if($echo) {
		echo $html;
	} else {
		return $html;
	}
	
}

/**
 * Show Field Select.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_add_select( $args, $echo = true ) {

	$html = '';
	
	$new_field = array( 'type' => 'select', 'name' => 'Select Field', 'multiple' => false, 'style' => '' );
	$field = array_merge( $new_field, $args );
	
	$default_meta = isset( $field['default'] ) ? $field['default'] : '';
	
	$meta = woo_vou_meta_value( $field );
	$meta = !empty( $meta ) ? $meta : $default_meta;
	
	if( ! is_array( $meta ) ) {
		$meta = (array) $meta;
	}
	
	$html .= woo_vou_show_field_begin( $field );
	
	$html .= "<select class='woo-vou-meta-select {$field['class']} ".($field['multiple'] ? 'woo-vou-meta-multiple-select' : 'woo-vou-meta-single-select')."' name='{$field['id']}" . ( $field['multiple'] ? "[]' id='{$field['id']}' multiple='multiple'" : "'" ) . " style='" . esc_attr( $field['style'] ) . "'>";
	
	foreach ( $field['options'] as $key => $value ) {
		$html .= "<option value='{$key}'" . selected( in_array( $key, $meta ), true, false ) . ">{$value}</option>";
	}
	
	$html .= "</select>";	
		
	$html .= woo_vou_show_field_end( $field );
	
	if($echo) {
		echo $html;
	} else {
		return $html;
	}
	
}

/**
 * Show Radio Field.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public 
 */
function woo_vou_add_radio( $args, $echo = true ) {

	$html = '';
	
	$new_field = array( 'type' => 'radio', 'name' => 'Radio Field' );
	$field = array_merge( $new_field, $args );

	$default_meta = isset( $field['default'] ) ? $field['default'] : '';
	
	$meta = woo_vou_meta_value( $field );
	$meta = !empty( $meta ) ? $meta : $default_meta;
	
	if( ! is_array( $meta ) ) {
		$meta = (array) $meta;
	}
  
	$html .= woo_vou_show_field_begin( $field );
	
	foreach ( $field['options'] as $key => $value ) {
		$html .= "<input type='radio' id='{$field['id']}_{$key}' class='woo-vou-meta-radio' name='{$field['id']}' value='{$key}'" . checked( in_array( $key, $meta ), true, false ) . " /> <label for='{$field['id']}_{$key}' class='woo-vou-meta-radio-label'>{$value}</label>";
	}
	
	$html .= woo_vou_show_field_end( $field );
	
	if($echo) {
		echo $html;
	} else {
		return $html;
	}
	
}

/**
 * Show Date Field.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_add_datetime( $args, $echo = true ) {

	$html = '';
	
	$new_field = array('type' => 'datetime','format'=>'d MM, yy','name' => 'Date Time Field');
	$field = array_merge( $new_field, $args );

	$meta = woo_vou_meta_value( $field );
	
	if(isset($meta) && !empty($meta) && !is_array($meta)) { //check datetime value is set & not array & not empty
		$meta = date('d-m-Y h:i a',strtotime($meta));
	} else {
		$meta = '';
	}
	
	$html .= woo_vou_show_field_begin( $field );
	
	$html .= "<input type='text' class='woo-vou-meta-datetime' name='{$field['id']}' id='{$field['id']}' rel='{$field['format']}' value='{$meta}' size='30' />";
	
	$html .= woo_vou_show_field_end( $field );
	
	if($echo) {
		echo $html;
	} else {
		return $html;
	}
}

/**
 * Show Image Field.
 *
 * @param array $field 
 * @param array $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_add_image( $args, $echo = true ) {

	$html = '';
	
	$new_field = array( 'type' => 'image', 'name' => 'Image Field' );
	$field = array_merge( $new_field, $args );

	$html .= woo_vou_show_field_begin( $field );
	
	$html .= wp_nonce_field( "woo-vou-meta-delete-mupload_{$field['id']}", "nonce-delete-mupload_".$field['id'], false, false );

	$meta = woo_vou_meta_value( $field );
	
	if( is_array( $meta ) ) {
		if( isset( $meta[0] ) && is_array( $meta[0] ) ) {
			$meta = $meta[0];
		}
	}
	
	if( is_array( $meta ) && isset( $meta['src'] ) && $meta['src'] != '' ) {
		$html .= "<span class='mupload_img_holder'><img src='".esc_url($meta['src'])."' /></span>";
		$html .= "<input type='hidden' name='".$field['id']."[id]' id='".$field['id']."[id]' value='".$meta['id']."' />";
		$html .= "<input type='hidden' name='".$field['id']."[src]' id='".$field['id']."[src]' value='".$meta['src']."' />";
		$html .= "<input class='woo-vou-meta-delete_image_button button-secondary' type='button' rel='".$field['id']."' value='" . esc_html__( 'Delete Image', 'woovoucher' ) . "' />";
	} else {
		$html .= "<span class='mupload_img_holder'></span>";
		$html .= "<input type='hidden' name='".$field['id']."[id]' id='".$field['id']."[id]' value='' />";
		$html .= "<input type='hidden' name='".$field['id']."[src]' id='".$field['id']."[src]' value='' />";
		$html .= "<input class='woo-vou-meta-upload_image_button button-secondary' type='button' rel='".$field['id']."' value='" . esc_html__( 'Upload Image', 'woovoucher' ) . "' />";
	}
	
	$html .= woo_vou_show_field_end( $field );
	
	if($echo) {
		echo $html;
	} else {
		return $html;
	}
}

/**
 * Show Wysiwig Field.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_add_wysiwyg( $args ) {

	global $wp_version;
	
	$html = '';
	
	$new_field = array( 'type' => 'wysiwyg', 'name' => 'WYSIWYG Editor Field' );
	$field = array_merge( $new_field, $args );

	$meta = woo_vou_meta_value( $field );
	
	$html .= woo_vou_show_field_begin( $field );

	echo $html;
	
	// Add TinyMCE script for WP version < 3.3
	if ( version_compare( $wp_version, '3.2.1' ) < 1 ) {
		echo "<textarea class='woo-vou-meta-wysiwyg theEditor large-text' name='{$field['id']}' id='{$field['id']}' cols='60' rows='10'>{$meta}</textarea>";
	} else {
		// Use new wp_editor() since WP 3.3
		wp_editor( html_entity_decode($meta), $field['id'], array( 'editor_class' => 'woo-vou-meta-wysiwyg' ) );
	}
 
	$html = woo_vou_show_field_end( $field );
	
	echo $html;
}

/**
 * Show File Field.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_add_fileadvanced( $args, $echo = true ) {

	$html = '';
	
	$new_field = array( 'type' => 'fileadvanced', 'name' => 'Advanced File Field' );
	$field = array_merge( $new_field, $args );

	$meta = woo_vou_meta_value( $field );
	
	$namesarr = $field;
	$namesarr['id'] = $namesarr['id'].'_name';
	$namesmeta = woo_vou_meta_value( $namesarr );
	
	if ( ! is_array( $meta ) ) {
		$meta = (array) $meta;
	}

	$html .= woo_vou_show_field_begin( $field );

	if( ! empty( $meta ) ) {
		$nonce = wp_create_nonce( 'at_ajax_delete' );

			foreach( ( array )$meta as $key => $att ) {

				if(!empty($att)) {
					$splitname = pathinfo( $att );
					$filename = isset( $namesmeta[$key] ) && !empty( $namesmeta[$key] ) ? $namesmeta[$key] : $splitname['filename'];
					$html .= "<div class='file-input-advanced'>";
					$html .= "<input type='text' name='{$field['id']}_name[]' value='{$filename}' class='woo-vou-upload-file-name' placeholder='".esc_html__('File Name','woovoucher')."'/>";
					$html .= "<input type='text' name='{$field['id']}[]' value='".$att."' class='woo-vou-upload-file-link' placeholder='http://'/>";
					$html .= "<span class='woo-vou-upload-files'><a class='woo-vou-upload-fileadvanced' href='javascript:void(0);'>".esc_html__( 'Upload a File','woovoucher')."</a></span>";
					$html .= "<a href='javascript:void(0);' class='woo-vou-delete-fileadvanced'><img src='".esc_url(WOO_VOU_META_URL)."/images/delete-16.png' alt='".esc_html__('Delete','woovoucher')."'/></a>";
					$html .= "</div><!-- End .file-input-advanced -->";
				}
			}
	} 
	if(empty($meta[0])){
		
		$html .= "<div class='file-input-advanced'>";
		$html .= "<input type='text' name='{$field['id']}_name[]' value='' class='woo-vou-upload-file-name' placeholder='".esc_html__('File Name','woovoucher')."'/>";
		$html .= "<input type='text' name='{$field['id']}[]' value='' class='woo-vou-upload-file-link' placeholder='http://'/>";
		$html .= "<span class='woo-vou-upload-files'><a class='woo-vou-upload-fileadvanced' href='javascript:void(0);'>".esc_html__( 'Upload a File','woovoucher')."</a></span>";
		$html .= "<a href='javascript:void(0);' class='woo-vou-delete-fileadvanced'><img src='".esc_url(WOO_VOU_META_URL)."/images/delete-16.png' alt='".esc_html__('Delete','woovoucher')."'/></a>";
		$html .= "</div><!-- End .file-input-advanced -->";
	}
	
	$html .= "<a class='woo-vou-meta-add-fileadvanced button' href='javascript:void(0);'>" . esc_html__( 'Add more files', 'woovoucher' ) . "</a>";
	
	$html .= woo_vou_show_field_end( $field );
	
	if($echo) {
		echo $html;
	} else {
		return $html;
	}
}

/**
 * Show Color Field.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_add_color( $args, $echo = true ) {

	global $wp_version;
	
	//If the WordPress version is greater than or equal to 3.5, then load the new WordPress color picker.
    if ( $wp_version >= 3.5 ){
        //Both the necessary css and javascript have been registered already by WordPress, so all we have to do is load them with their handle.
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_style( 'wp-color-picker' );
    }
    //If the WordPress version is less than 3.5 load the older farbtasic color picker.
    else {
        //As with wp-color-picker the necessary css and javascript have been registered already by WordPress, so all we have to do is load them with their handle.
        wp_enqueue_script( 'farbtastic' );
        wp_enqueue_style( 'farbtastic' );
    }
    
	$html = '';
	
	$new_field = array( 'type' => 'color', 'name' => esc_html__('ColorPicker Field', 'woovoucher'), 'class' => '' );
	$field = array_merge( $new_field, $args );

	$meta = woo_vou_meta_value( $field );
	
	if ( empty( $meta ) ) {
		$meta = '';
	}
	  
	$html .= woo_vou_show_field_begin( $field );
	
	if( $wp_version >= 3.5 ) {
									
		$html .= "<input type='text' value='{$meta}' id='{$field['id']}' name='{$field['id']}' class='woo-vou-meta-color-iris ".( isset( $field['class'] )? " {$field['class']}": "")." ' data-default-color='' />";
		
	} else {
		$html .= "<div class='woo-vou-color-picker-wrapper'>
					<input type='text' value='{$meta}' id='{$field['id']}' name='{$field['id']}' class='{$field['id']}' />
					<input type='button' class='woo-vou-meta-color-iris ".( isset( $field['class'] )? " {$field['class']}": "")." ' value='".esc_html__('Select Color','woovoucher')."'>
					<div class='colorpicker'></div>
				</div>";
	}
	
	$html .= woo_vou_show_field_end( $field );
	
	if($echo) {
		echo $html;
	} else {
		return $html;
	}
}

/**
 * Show Date Field.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_add_date( $args, $echo = true ) {

	$html = '';
	
	$new_field = array( 'type' => 'date', 'format'=>'d MM, yy', 'name' => 'Date Field' );
	$field = array_merge( $new_field, $args );

	$meta = woo_vou_meta_value( $field );
	
	$meta = !is_array($meta) ? $meta : '' ;
	
	$html .= woo_vou_show_field_begin( $field );
	
	$html .= "<input type='text' class='woo-vou-meta-date' name='{$field['id']}' id='{$field['id']}' rel='{$field['format']}' value='{$meta}' size='30' />";
	
	$html .= woo_vou_show_field_end( $field );
	
	if($echo) {
		echo $html;
	} else {
		return $html;
	}
}

/**
 * Show time field.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public 
 */
function woo_vou_add_time( $args, $echo = true ) {

	$html = '';
	
	$new_field = array( 'type' => 'time', 'format'=>'hh:mm', 'name' => 'Time Field', 'ampm' => false );
	$field = array_merge( $new_field, $args );

	$meta = woo_vou_meta_value( $field );
	
	$ampm = ($field['ampm'])? 'true' : 'false';
	
	$html .= woo_vou_show_field_begin( $field );
	
	$html .= "<input type='text' class='woo-vou-meta-time' name='{$field['id']}' id='{$field['id']}' data-ampm='{$ampm}' rel='{$field['format']}' value='{$meta}' size='30' />";
	
	$html .= woo_vou_show_field_end( $field );
	
	if($echo) {
		echo $html;
	} else {
		return $html;
	}
}

/**
 * Show File Field.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_add_file( $args, $echo = true ) {

	$html = '';
	
	$new_field = array( 'type' => 'file', 'name' => 'File Field' );
	$field = array_merge( $new_field, $args );

	$meta = woo_vou_meta_value( $field );
	
	if ( ! is_array( $meta ) ) {
		$meta = (array) $meta;
	}

	$html .= woo_vou_show_field_begin( $field );
	
	if( ! empty( $meta ) ) {
		$nonce = wp_create_nonce( 'at_ajax_delete' );
		$html .= '<div><strong>' . esc_html__( 'Uploaded files', 'woovoucher' ) . '</strong></div>';
		$html .= '<ol class="woo-vou-meta-upload">';
		
			foreach( ( array )$meta[0] as $key => $att ) {

				$html .= "<li>" . wp_get_attachment_url( $att) . " (<a class='woo-vou-meta-delete-file' href='#' rel='{$nonce}|$key|{$field['id']}|{$att}'>" . esc_html__( 'Delete', 'woovoucher' ) . "</a>)</li>";
			}
		$html .= '</ol>';
	}

	// show form upload
	$html .= "<div class='woo-vou-meta-file-upload-label'>";
	$html .= "<strong>" . esc_html__( 'Upload new files', 'woovoucher' ) . "</strong>";
	$html .= "</div>";
	$html .= "<div class='new-files'>";
	$html .= "<div class='file-input'>";
	$html .= "<input type='file' name='{$field['id']}[]' />";
	$html .= "</div><!-- End .file-input -->";
	$html .= "<a class='woo-vou-meta-add-file button' href='#'>" . esc_html__( 'Add more files', 'woovoucher' ) . "</a>";
	$html .= "<div class='clear'></div>";
	$html .= "</div><!-- End .new-files -->";
	
	$html .= woo_vou_show_field_end( $field );
	
	if($echo) {
		echo $html;
	} else {
		return $html;
	}
}

/**
 * Show Field Import CSV.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_add_importcsv( $args, $echo = true ) {  

	$html = '';
	
	$new_field = array( 'type' => 'importcsv','name' => esc_html__( 'Import Voucher Codes Field', 'woovoucher' ));
	$field = array_merge( $new_field, $args );

	$meta = woo_vou_meta_value( $field );
	
	$html .= woo_vou_show_field_begin( $field );
	
	$html .= '<input type="button" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['btntext'].'" class="woo-vou-meta-vou-import-data button-secondary">';
	
	$html .= woo_vou_show_field_end( $field );
	
	if($echo) {
		echo $html;
	} else {
		return $html;
	}
	
}   
 
/**
 * Show Field Used Voucher Code.
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_add_usedvoucodes( $args, $echo = true ) {  

	global $post, $woo_vou_render;
	
	$html = '';
	
	$new_field = array( 'type' => 'usedvoucodes','name' => esc_html__( 'Redeemed Voucher Codes Field', 'woovoucher' ));
	$field = array_merge( $new_field, $args );

	$meta = woo_vou_meta_value( $field );
						
	$html .= woo_vou_show_field_begin( $field );

	$html .= '<input type="button" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['btntext'].'" class="woo-vou-meta-vou-used-data button-secondary">';
	
	$html .= $woo_vou_render->woo_vou_used_popup( $post->ID );
	
	$html .= woo_vou_show_field_end( $field );
	
	if($echo) {
		echo $html;
	} else {
		return $html;
	}
	
} 

/**
 * Add Repeater Block
 * 
 * Handles to add repeater block
 *
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_add_repeater_block( $args, $echo = true ) {

	global $post,$woo_vou_model;
	
	$new_field = array( 'type' => 'repeater', 'id'=> $args['id'], 'name' => 'Reapeater Field', 'fields' => array() );
	
	$field = array_merge( $new_field, $args );
	
	$meta = woo_vou_meta_value( $field );
	
	$html = '';
	
	$html .= woo_vou_show_field_begin( $field );
	
	$html .= "<div class='woo-vou-meta-repeat' id='{$field['id']}'>";
	
	if( !empty( $meta ) && count( $meta ) > 0 ) {
		
		$row = '';
		
		for ( $i = 0; $i < ( count ( $meta ) ); $i++ ) {
		
			$row .= "	<div class='woo-vou-meta-repater-block'>
							<table class='repeater-table form-table'>
								<tbody>";
			
			for ( $k = 0; $k < count( $field['fields'] ); $k++ ) {
				
				$row .= woo_vou_show_field_begin( $field['fields'][$k] );
				
				$row .= "<input type='text' name='{$field['fields'][$k]['id']}[]' class='woo-vou-meta-text regular-text' value='{$woo_vou_model->woo_vou_escape_attr( $meta[$i][$field['fields'][$k]['id']] )}'/>";
				
				$row .= woo_vou_show_field_end( $field['fields'][$k] );
				
			}
			
			$row .= "			</tbody>
							</table>";
			if( $i > 0 ) {
				$showremove = "woo-vou-block-section";
			} else {
				$showremove = "woo-vou-hide-section";
			}
			
			$row .= "	<img id='remove-{$args['id']}' class='woo-vou-repeater-remove ".$showremove."' title='".esc_attr__('Remove', 'woovoucher')."' alt='".esc_attr__('Remove', 'woovoucher')."' src='".esc_url(WOO_VOU_META_URL)."/images/remove.png'>";
			
			$row .= "		</div><!--.woo-vou-meta-repater-block-->";
			
		}
		$html .= $row;
		
	} else {
		
		$row = '';
		$row .= "	<div class='woo-vou-meta-repater-block'>
							<table class='repeater-table form-table'>
								<tbody>";
				
				for ( $i = 0; $i < count ( $field['fields'] ); $i++ ) {
					
					$row .= 	woo_vou_show_field_begin( $field['fields'][$i] );
					
					$row .= "	<input type='text' name='{$field['fields'][$i]['id']}[]' class='woo-vou-meta-text regular-text'/>";
					
					$row .=		woo_vou_show_field_end( $field['fields'][$i] );
					
				}
				
			$row .= "		</tbody>
						</table>";
				
			$row .= "	<img id='remove-{$args['id']}' class='woo-vou-repeater-remove woo-vou-hide-section' title='".esc_attr__('Remove', 'woovoucher')."' alt='".esc_attr__('Remove', 'woovoucher')."' src='".esc_url(WOO_VOU_META_URL)."/images/remove.png'>";
			
			$row .= "		</div><!--.woo-vou-meta-repater-block-->";
		
		$html .= $row;
			
	}
	
	$html .= "	<img id='add-{$args['id']}' class='woo-vou-repeater-add' title='".esc_attr__( 'Add','woovoucher')."' alt='".esc_attr__( 'Add', 'woovoucher')."' src='".esc_url(WOO_VOU_META_URL)."/images/add.png'>";
	
	$html .= "	</div><!--.woo-vou-meta-repeat-->";
	
	$html .= woo_vou_show_field_end( $field );
	
	if($echo) {
		echo $html;
	} else {
		return $html;
	}
}
 
/**
 * Show Background Pattern Field
 *
 * @param string $field 
 * @param string $meta 
 * @since 1.0
 * @access public
 */
function woo_vou_add_bg_pattern( $args, $echo = true ) {  
	
	$html = '';
									
	$new_field = array( 'type' => 'text', 'name' => 'Background Pattern Field' );
	$field = array_merge( $new_field, $args );
	
	$all_background_patterns = isset( $field['options'] ) ? $field['options'] : array();
	
	$default_meta = isset( $field['default'] ) ? $field['default'] : '';
	
	$meta = woo_vou_meta_value( $field );
	$meta = !empty( $meta ) ? $meta : $default_meta;
	
	$html .= woo_vou_show_field_begin( $field );
	
	if( !empty( $all_background_patterns ) ) { // Check pattern options are not empty
		
		foreach ( $all_background_patterns as $pattern ) { 
			$background_pattern_css = $meta == $pattern ? 'woo-vou-meta-bg-pattern-selected' : '';
		
			$html .= '<img class="woo-vou-meta-bg-patterns ' . $background_pattern_css . '" id="woo_vou_meta_img_' . $pattern . '" src="' . esc_url(WOO_VOU_IMG_URL) . '/patterns/' . $pattern . '.png' . '" data-pattern="' . $pattern . '" alt="' . ucwords( $pattern ) . '" title="' . ucwords( $pattern ) . '" />';
		}
	}
	
	$html .= '<input class="woo-vou-meta-bg-patterns-opt" type="hidden" id="' . $field['id'] . '" name="' . $field['id'] . '" value="' . $meta . '" />';
	
	$html .= woo_vou_show_field_end( $field );
	
	if($echo) {
		echo $html;
	} else {
		return $html;
	}
	
}
?>