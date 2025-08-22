<?php
/**
 * UserRegistration Form Functions
 *
 * Functions related to forms.
 *
 * @package  UserRegistration/Functions
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ur_get_form_fields( $form_id ) {
	$form_id = (int) $form_id;

	$form_fields = array();

	if ( ! empty( $form_id ) ) {
		$post_content_array = ( $form_id ) ? UR()->form->get_form( $form_id, array( 'content_only' => true ) ) : array();
		if( is_iterable( $post_content_array ) ) {
			foreach ( $post_content_array as $row_index => $row ) {
				foreach ( $row as $grid_index => $grid ) {
					foreach ( $grid as $field_index => $field ) {
						$field_name = isset( $field->general_setting->field_name ) ? $field->general_setting->field_name : '';

						if ( $field_name ) {
							$form_fields[ $field_name ] = $field;
						}
					}
				}
			}
		}
	}

	return $form_fields;
}

function ur_get_form_field_keys( $form_id ) {
	$form_fields = ur_get_form_fields( $form_id );

	$field_keys = array();

	if ( ! empty( $form_fields ) && is_array( $form_fields ) ) {
		$field_keys = array_keys( $form_fields );
	}

	return $field_keys;
}

/**
 * Returns settings for all form fields in proper array format.
 *
 * Uses UR_FrontEnd_Form_Handler::get_form_field_data() function.
 *
 * @param integer $form_id Form Id.
 * @return array
 */
function ur_get_form_field_data( $form_id = 0 ) {

	$post_content_array = ( $form_id ) ? UR()->form->get_form( $form_id, array( 'content_only' => true ) ) : array();

	$form_field_data = UR_Frontend_Form_Handler::get_form_field_data( $post_content_array );

	return $form_field_data;
}
