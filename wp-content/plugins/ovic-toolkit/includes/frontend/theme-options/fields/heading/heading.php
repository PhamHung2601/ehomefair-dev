<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Heading
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if( ! class_exists( 'OVIC_Field_heading' ) ) {
  class OVIC_Field_heading extends OVIC_Fields {

    public function __construct( $field, $value = '', $unique = '', $where = '' ) {
      parent::__construct( $field, $value, $unique, $where );
    }

    public function output() {

      echo $this->element_before();
      echo $this->field['content'];
      echo $this->element_after();

    }

  }
}
