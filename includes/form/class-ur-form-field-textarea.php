<?php
/**
 * UR_Form_Field_Textarea.
 *
 * @package  UserRegistration/Form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Form_Field_Textarea Class
 */
class UR_Form_Field_Textarea extends UR_Form_Field {

	/**
	 * Instance Variable.
	 *
	 * @var [mixed]
	 */
	private static $_instance;

	/**
	 * Get Instance of class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id                       = 'user_registration_textarea';
		$this->form_id                  = 1;
		$this->registered_fields_config = array(
			'label' => __( 'Textarea', 'user-registration' ),
			'icon'  => 'ur-icon ur-icon-textarea',
		);

		$this->field_defaults = array(
			'default_label'      => __( 'Textarea', 'user-registration' ),
			'default_field_name' => 'textarea_' . ur_get_random_number(),
		);
	}

	/**
	 * Get Registered admin fields.
	 */
	public function get_registered_admin_fields() {

		return '<li id="' . $this->id . '_list " class="ur-registered-item draggable" data-field-id="' . $this->id . '"><span class="' . $this->registered_fields_config['icon'] . '"></span>' . $this->registered_fields_config['label'] . '</li>';
	}

	/**
	 * Validate field.
	 *
	 * @param [object] $single_form_field Field Data.
	 * @param [object] $form_data Form Data.
	 * @param [string] $filter_hook Hook.
	 * @param [int]    $form_id Form id.
	 */
	public function validation( $single_form_field, $form_data, $filter_hook, $form_id ) {

		$value = isset( $form_data->value ) ? $form_data->value : '';
		$label = $single_form_field->general_setting->label;

		// Validate Limit Length.
		if ( isset( $single_form_field->advance_setting->limit_length ) && $single_form_field->advance_setting->limit_length ) {
			if ( isset( $single_form_field->advance_setting->limit_length_limit_count ) && isset( $single_form_field->advance_setting->limit_length_limit_mode) )

			$max_size = $single_form_field->advance_setting->limit_length_limit_count;

			if ( $single_form_field->advance_setting->limit_length_limit_mode === 'characters' ) {
				if ( is_wp_error( UR_Validation::validate_max_length( $value, $max_size ) ) ) {
					add_filter(
						$filter_hook,
						function ( $msg ) use ( $max_size, $label ) {
							return sprintf(
								'Please enter a value of length less than %d for %s',
								$max_size,
								"<strong>$label</strong>."
							);
						}
					);
				}
			} else if ( $single_form_field->advance_setting->limit_length_limit_mode === 'words' ) {
				if ( is_wp_error( UR_Validation::validate_max_words_length( $value, $max_size ) ) ) {
					add_filter(
						$filter_hook,
						function ( $msg ) use ( $max_size, $label ) {
							return sprintf(
								'Please enter number of words less than %d for %s',
								$max_size,
								"<strong>$label</strong>."
							);
						}
					);
				}
			}

		}

		// Validate Minimum Length.
		if ( isset( $single_form_field->advance_setting->minimum_length ) && $single_form_field->advance_setting->minimum_length ) {
			if ( isset( $single_form_field->advance_setting->minimum_length_limit_count ) && isset( $single_form_field->advance_setting->minimum_length_limit_mode) )

			$min_size = $single_form_field->advance_setting->minimum_length_limit_count;

			if ( $single_form_field->advance_setting->minimum_length_limit_mode === 'characters' ) {
				if ( is_wp_error( UR_Validation::validate_min_length( $value, $min_size ) ) ) {
					add_filter(
						$filter_hook,
						function ( $msg ) use ( $min_size, $label ) {
							return sprintf(
								'Please enter a value of length at least %d for %s',
								$min_size,
								"<strong>$label</strong>."
							);
						}
					);
				}
			} else if ( $single_form_field->advance_setting->minimum_length_limit_mode === 'words' ) {
				if ( is_wp_error( UR_Validation::validate_min_words_length( $value, $min_size ) ) ) {
					add_filter(
						$filter_hook,
						function ( $msg ) use ( $min_size, $label ) {
							return sprintf(
								'Please enter number of words at least %d for %s',
								$min_size,
								"<strong>$label</strong>."
							);
						}
					);
				}
			}
		}
	}
}

return UR_Form_Field_Textarea::get_instance();
