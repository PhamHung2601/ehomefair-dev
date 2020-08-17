<?php if ( !defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 *
 * Email validate
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_validate_email' ) ) {
	function ovic_validate_email( $args )
	{
		$field_value = $args['value'];
		// Getting title of field.
		// $field_title = $args['field']['title']; // getting title of field.
		if ( !sanitize_email( $field_value ) ) {
			return __( 'Please write a valid email address!', 'ovic-toolkit' );
		}
	}
}
/**
 *
 * Numeric validate
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_validate_numeric' ) ) {
	function ovic_validate_numeric( $args )
	{
		if ( !is_numeric( $args['value'] ) ) {
			return __( 'Please write a numeric data!', 'ovic-toolkit' );
		}
	}
}
/**
 *
 * Required validate
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_validate_required' ) ) {
	function ovic_validate_required( $args )
	{
		if ( empty( $args['value'] ) ) {
			return __( 'Fatal Error! This field is required!', 'ovic-toolkit' );
		}
	}
}
/**
 *
 * Email validate for Customizer
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_customize_validate_email' ) ) {
	function ovic_customize_validate_email( $validity, $value, $wp_customize )
	{
		// Getting title of field.
		// $field_title = $wp_customize->manager->get_control( $wp_customize->id )->field['title'];
		if ( !sanitize_email( $value ) ) {
			$validity->add( 'required', __( 'Please write a valid email address!', 'ovic-toolkit' ) );
		}

		return $validity;
	}
}
/**
 *
 * Numeric validate for Customizer
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_customize_validate_numeric' ) ) {
	function ovic_customize_validate_numeric( $validity, $value, $wp_customize )
	{
		if ( !is_numeric( $value ) ) {
			$validity->add( 'required', __( 'Please write a numeric data!', 'ovic-toolkit' ) );
		}

		return $validity;
	}
}
/**
 *
 * Required validate for Customizer
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( !function_exists( 'ovic_customize_validate_required' ) ) {
	function ovic_customize_validate_required( $validity, $value, $wp_customize )
	{
		if ( empty( $value ) ) {
			$validity->add( 'required', __( 'Fatal Error! This field is required!', 'ovic-toolkit' ) );
		}

		return $validity;
	}
}
