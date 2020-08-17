<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Tabbed
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if( ! class_exists( 'OVIC_Field_tabbed' ) ) {
  class OVIC_Field_tabbed extends OVIC_Fields {

    public function __construct( $field, $value = '', $unique = '', $where = '' ) {
      parent::__construct( $field, $value, $unique, $where );
    }

    public function output(){

      $unallows = array( 'tabbed' );
      $value    = $this->element_value();

      echo $this->element_before();

      echo '<div class="ovic-tabbed-nav">';
      foreach ( $this->field['tabs'] as $nav_key => $tab ) {

        $tabbed_active = ( empty( $nav_key ) ) ? ' class="ovic-tabbed-active"' : '';
        echo '<a href="#"'. $tabbed_active .'>'. $tab['title'] .'</a>';

      }
      echo '</div>';

      echo '<div class="ovic-tabbed-sections">';
      foreach ( $this->field['tabs'] as $section_key => $tab ) {

        $tabbed_hidden = ( ! empty( $section_key ) ) ? ' hidden' : '';

        echo '<div class="ovic-tabbed-section'. $tabbed_hidden .'">';

        foreach ( $tab['fields'] as $field ) {

          if( in_array( $field['type'], $unallows ) ) { $field['_notice'] = true; }

          $field_id      = ( isset( $field['id'] ) ) ? $field['id'] : '';
          $field_default = ( isset( $field['default'] ) ) ? $field['default'] : '';
          $field_value   = ( isset( $value[$field_id] ) ) ? $value[$field_id] : $field_default;
          $unique_id     = ( ! empty( $this->unique ) ) ? $this->unique .'['. $this->field['id'] .']' : $this->field['id'];

          echo ovic_add_field( $field, $field_value, $unique_id, 'field/tabbed' );

        }

        echo '</div>';

      }
      echo '</div>';

      echo $this->element_after();

    }

  }
}
