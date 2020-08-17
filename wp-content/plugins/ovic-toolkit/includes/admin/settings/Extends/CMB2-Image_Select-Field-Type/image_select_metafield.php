<?php
if (!class_exists('Ovic_Cmb2_Image_Select_Field')){
    class Ovic_Cmb2_Image_Select_Field{
        public function __construct(){
            add_action( 'admin_enqueue_scripts', array(  $this, 'enqueue_scripts' ) );
            add_action( 'cmb2_render_image_select', array( $this,'cmb2_render_image_select'), 10, 5 );
        }

        public function enqueue_scripts(){
            wp_enqueue_style( 'image-select-metafield', OVIC_TOOLKIT_PLUGIN_URL . 'includes/admin/settings/Extends/CMB2-Image_Select-Field-Type/image_select_metafield.css' );
            wp_enqueue_script( 'image-select-metafield', OVIC_TOOLKIT_PLUGIN_URL . 'includes/admin/settings/Extends/CMB2-Image_Select-Field-Type/image_select_metafield.js' ,array( 'jquery' ) );
        }

        function cmb2_render_image_select( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {


            $conditional_value =(isset($field->args['attributes']['data-conditional-value'])?'data-conditional-value="' .esc_attr($field->args['attributes']['data-conditional-value']).'"':'');
            $conditional_id =(isset($field->args['attributes']['data-conditional-id'])?' data-conditional-id="'.esc_attr($field->args['attributes']['data-conditional-id']).'"':'');
            $default_value = isset($field->args['attributes']['default']) ? $field->args['attributes']['default'] :'';
            $image_select = '<ul id="cmb2-image-select'.$field->args['_id'].'" class="cmb2-image-select-list">';
            foreach ( $field->options() as $value => $item ) {
                $selected = ($value === ($escaped_value==''?$default_value:$escaped_value )) ? 'checked="checked"' : '';
                $image_select .= '<li class="cmb2-image-select '.($selected!= ''?'cmb2-image-select-selected':'').'"><label for="' . $field->args['_id'] . esc_attr( $value ) . '">
			<input '.$conditional_value.$conditional_id.' type="radio" id="'. $field->args['_id'] . esc_attr( $value ) . '" name="' . $field->args['_name'] . '" value="' . esc_attr( $value ) . '" ' . $selected . ' class="cmb2-option"><img class="" style=" width: auto; " alt="' . $item['alt'] . '" src="' . $item['img'] . '">
			</label></li>';
            }
            $image_select .= '</ul>';
            $image_select .= $field_type_object->_desc( true );
            echo $image_select;
        }
    }
    new Ovic_Cmb2_Image_Select_Field();
}

