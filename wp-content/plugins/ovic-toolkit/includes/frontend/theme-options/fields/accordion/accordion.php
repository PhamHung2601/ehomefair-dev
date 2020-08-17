<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Accordion
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if( ! class_exists( 'OVIC_Field_accordion' ) ) {
  class OVIC_Field_accordion extends OVIC_Fields {

    public function __construct( $field, $value = '', $unique = '', $where = '' ) {
      parent::__construct( $field, $value, $unique, $where );
    }

    public function output(){

      $unallows = array( 'accordion' );

      $value = $this->element_value();

      echo $this->element_before();

      echo '<div class="ovic-accordion-items">';

      foreach ( $this->field['accordions'] as $key => $accordion ) {

        echo '<div class="ovic-accordion-item">';

          $icon = ( ! empty( $accordion['icon'] ) ) ? $accordion['icon'] : 'ovic-accordion-icon fa fa-angle-right';

          echo '<h4 class="ovic-accordion-title">';
          echo '<i class="'. $icon .'"></i>';
          echo $accordion['title'];
          echo '</h4>';

          echo '<div class="ovic-accordion-content">';

          foreach ( $accordion['fields'] as $field ) {

            if( in_array( $field['type'], $unallows ) ) { $field['_notice'] = true; }

            $field['wrap_class'] = ( ! empty( $field['wrap_class'] ) ) ? $field['wrap_class'] .' ovic-no-script' : 'ovic-no-script';

            $field_id      = ( isset( $field['id'] ) ) ? $field['id'] : '';
            $field_default = ( isset( $field['default'] ) ) ? $field['default'] : '';
            $field_value   = ( isset( $value[$field_id] ) ) ? $value[$field_id] : $field_default;
            $unique_id     = ( ! empty( $this->unique ) ) ? $this->unique .'['. $this->field['id'] .']' : $this->field['id'];

            echo ovic_add_field( $field, $field_value, $unique_id, 'field/accordion' );

          }

          echo '</div>';

        echo '</div>';

      }

      echo '</div>';

      echo $this->element_after();

    }

  }
}
