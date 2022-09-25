<?php
/**
 * UserRegistration Template
 *
 * Functions for the templating system.
 *
 * @package  UserRegistration/Functions
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'template_redirect', 'ur_template_redirect' );
add_action( 'template_redirect', 'ur_login_template_redirect' );
add_action( 'template_redirect', 'ur_registration_template_redirect' );

/**
 * Redirect after logout.
 * Handle redirects before content is output - hooked into template_redirect so is_page works.
 */
function ur_template_redirect() {
	global $wp;

	if ( isset( $wp->query_vars['user-logout'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'user-logout' ) ) { //PHPCS:ignore;
		// Logout.
		$redirect_url = str_replace( '/user-logout', '', $wp->request );
		wp_safe_redirect( str_replace( '&amp;', '&', wp_logout_url( $redirect_url ) ) );
		exit;
	} elseif ( isset( $wp->query_vars['user-logout'] ) && 'true' === $wp->query_vars['user-logout'] ) {

		// Redirect to the correct logout endpoint.
		wp_safe_redirect( esc_url_raw( ur_get_page_permalink( 'user-logout' ) ) );
		exit;
	}
}

/**
 * Check for login shortcode in the page and redirect to the url passed with login shortcode parameter redirect_url
 * Handle redirects before content is output - hooked into template_redirect so is_page works.
 */
function ur_login_template_redirect() {
	 global $post;

	$post_content = isset( $post->post_content ) ? $post->post_content : '';

	if ( ( has_shortcode( $post_content, 'user_registration_login' ) || has_shortcode( $post_content, 'user_registration_my_account' ) ) && is_user_logged_in() ) {
		preg_match( '/' . get_shortcode_regex() . '/s', $post_content, $matches );

		// Remove all html tags.
		$escaped_atts_string = preg_replace( '/<[\/]{0,1}[^<>]*>/', '', $matches[3] );

		$attributes   = shortcode_parse_atts( $escaped_atts_string );
		$redirect_url = isset( $attributes['redirect_url'] ) ? $attributes['redirect_url'] : '';
		$redirect_url = trim( $redirect_url, ']' );
		$redirect_url = trim( $redirect_url, '"' );
		$redirect_url = trim( $redirect_url, "'" );

		$redirect_url = apply_filters( 'user_registration_redirect_url_after_login', $redirect_url );

		if ( ! is_elementor_editing_page() && ! empty( $redirect_url ) ) {
			wp_redirect( $redirect_url );
			exit();
		}
	}
}

/**
 * Redirects the logged in user to the option set in form settings if registration page is selected.
 * Donot redirect for admins.
 *
 * @return void
 * @since  1.5.1
 */
function ur_registration_template_redirect() {

	// Return if the user is not logged in.
	if ( is_user_logged_in() === false ) {
		return;
	}

	$current_user    = wp_get_current_user();
	$current_user_id = $current_user->ID;
	$form_id = 0;

	// Donot redirect for admins.
	if ( in_array( 'administrator', wp_get_current_user()->roles ) ) {
		return;
	} else {

		global $post;

		$post_content = isset( $post->post_content ) ? $post->post_content : '';

		$shortcodes = parse_blocks( $post_content );
		$matched = false;
		foreach ( $shortcodes as $shortcode ) {
			if ( ! empty( $shortcode['blockName'] ) ) {
				if ( 'user-registration/form-selector' === $shortcode['blockName'] && isset( $shortcode['attrs']['formId'] ) ) {
					$matched = true;
					$form_id = $shortcode['attrs']['formId'];

				}
			}
		}

		if ( has_shortcode( $post_content, 'user_registration_form' ) ) {

			$attributes = ur_get_shortcode_attr( $post_content );
			$form_id    = isset( $attributes[0]['id'] ) ? $attributes[0]['id'] : 0;
			$matched = true;
		}

		if ( $matched ) {
			preg_match_all( '!\d+!', $form_id, $form_id );

			$redirect_url = ur_get_single_post_meta( $form_id[0][0], 'user_registration_form_setting_redirect_options', '' );
			$redirect_url = apply_filters( 'user_registration_redirect_from_registration_page', $redirect_url, $current_user );
			$redirect_url = ur_string_translation( $form_id[0][0], 'user_registration_form_setting_redirect_options', $redirect_url );

			if ( ! is_elementor_editing_page() && ! empty( $redirect_url ) ) {
				wp_redirect( $redirect_url );
				exit();
			}
		}
	}
}

/**
 * Add body classes for UR pages.
 *
 * @param  array $classes Classes.
 *
 * @return array
 */
function ur_body_class( $classes ) {
	$classes   = (array) $classes;
	$classes[] = 'user-registration-page';
	if ( is_ur_account_page() ) {
		$classes[] = 'user-registration-account';
	}

	foreach ( UR()->query->query_vars as $key => $value ) {
		if ( is_ur_endpoint_url( $key ) ) {
			$classes[] = 'user-registration-' . sanitize_html_class( $key );
		}
	}

	return array_unique( $classes );
}


if ( ! function_exists( 'user_registration_form_field' ) ) {

	/**
	 * Outputs a form fields on frontend.
	 *
	 * @param string $key Key.
	 * @param mixed  $args Arguments.
	 * @param string $value (default: null).
	 *
	 * @return string
	 */
	function user_registration_form_field( $key, $args, $value = null ) {

		/* Conditional Logic codes */
		$rules                      = array();
		$rules['conditional_rules'] = isset( $args['conditional_rules'] ) ? $args['conditional_rules'] : '';
		$rules['logic_gate']        = isset( $args['logic_gate'] ) ? $args['logic_gate'] : '';
		$rules['rules']             = isset( $args['rules'] ) ? $args['rules'] : array();
		$rules['required']          = isset( $args['required'] ) ? $args['required'] : '';

		foreach ( $rules['rules'] as $rules_key => $rule ) {
			if ( empty( $rule['field'] ) ) {
				unset( $rules['rules'][ $rules_key ] );
			}
		}

		$required = '';

		$rules['rules'] = array_values( $rules['rules'] );

		$rules = ( ! empty( $rules['rules'] ) && isset( $args['enable_conditional_logic'] ) ) ? wp_json_encode( $rules ) : '';
		/*Conditonal Logic codes end*/

		$defaults = array(
			'type'              => 'text',
			'label'             => '',
			'description'       => '',
			'placeholder'       => '',
			'size'              => false,
			'min'               => false,
			'max'               => false,
			'required'          => false,
			'autocomplete'      => false,
			'id'                => $key,
			'class'             => array(),
			'input_class'       => array(),
			'return'            => false,
			'options'           => array(),
			'custom_attributes' => array(),
			'validate'          => array(),
			'default'           => '',
			'autofocus'         => '',
			'priority'          => '',
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'user_registration_form_field_args', $args, $key, $value );

		if ( true === $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required        = ' <abbr class="required" title="' . esc_attr__( 'required', 'user-registration' ) . '">*</abbr>';
		} else {
			$args['required'] = '';
		}

		if ( is_null( $value ) || empty( $value ) ) {
			$value = $args['default'];
		}

		// Custom attribute handling.
		$custom_attributes         = array();
		$args['custom_attributes'] = array_filter( (array) $args['custom_attributes'] );

		if ( $args['size'] ) {
			$args['custom_attributes']['maxlength'] = absint( $args['size'] );
		}

		if ( ! empty( $args['min'] ) || '0' === $args['min'] ) {
			$args['custom_attributes']['min'] = $args['min'];
		}

		if ( ! empty( $args['max'] ) || '0' === $args['max'] ) {
			$args['custom_attributes']['max'] = $args['max'];
		}

		if ( ! empty( $args['step'] ) ) {
			$args['custom_attributes']['step'] = $args['step'];
		}

		if ( ! empty( $args['autocomplete'] ) ) {
			$args['custom_attributes']['autocomplete'] = $args['autocomplete'];
		}

		if ( true === $args['autofocus'] ) {
			$args['custom_attributes']['autofocus'] = 'autofocus';
		}

		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		if ( ! empty( $args['validate'] ) ) {
			foreach ( $args['validate'] as $validate ) {
				$args['class'][] = 'validate-' . $validate;
			}
		}

		$tooltip_html = '';

		if ( isset( $args['tooltip'] ) && 'yes' === $args['tooltip'] ) {
			$tooltip_html = ur_help_tip( $args['tooltip_message'], false, 'ur-portal-tooltip' );
		}

		$cl_html = '';

		if ( isset( $args['enable_conditional_logic'] ) && true === $args['enable_conditional_logic'] ) {
			$cl_map  = isset( $args['cl_map'] ) ? $args['cl_map'] : '';
			$cl_html = sprintf( 'data-conditional-logic-enabled="yes" data-conditional-logic-map="%s"', esc_attr( $cl_map ) );
		}

		$field           = '';
		$label_id        = $args['id'];
		$sort            = $args['priority'] ? $args['priority'] : '';
		$field_container = '<div class="form-row %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '" ' . $cl_html . '>%3$s</div>';
		$class           = '';
		if ( ! is_admin() ) {
			$form_id = isset( $args['form_id'] ) ? $args['form_id'] : '';

			$class = apply_filters( 'user_registration_field_icon_enabled_class', $class, $form_id );
		}

		switch ( $args['type'] ) {

			case 'title':
				$field .= '<h4>' . esc_html( $args['title'] ) . '</h4>';
				break;

			case 'textarea':
				$field .= '<textarea data-rules="' . esc_attr( $rules ) . '" data-id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . ( empty( $args['custom_attributes']['rows'] ) ? ' rows="2"' : '' ) . ( empty( $args['custom_attributes']['cols'] ) ? ' cols="5"' : '' ) . implode( ' ', $custom_attributes ) . '>' . esc_textarea( $value ) . '</textarea>';
				break;

			case 'checkbox':
				$field_key     = isset( $args['field_key'] ) ? $args['field_key'] : '';
				$default_value = isset( $args['default_value'] ) ? $args['default_value'] : '';    // Backward compatibility. Modified since 1.5.7.
				$default       = ! empty( $value ) ? $value : $default_value;
				$select_all    = isset( $args['select_all'] ) ? $args['select_all'] : '';
				$options       = isset( $args['options'] ) ? $args['options'] : ( $args['choices'] ? $args['choices'] : array() ); // $args['choices'] for backward compatibility. Modified since 1.5.7.
				$choice_limit = isset( $args['choice_limit'] ) ? $args['choice_limit'] : '';
				$choice_limit_attr = '';
				if ( '' !== $choice_limit ) {
					$choice_limit_attr = 'data-choice-limit="' . $choice_limit . '"';
				}

				if ( isset( $options ) && array_filter( $options ) ) {

					if ( ! empty( $default ) ) {
						$default = ( is_serialized( $default ) ) ? unserialize( $default ) : $default;
					}

					$choices = isset( $options ) ? $options : array();

					$field  = '<label class="ur-label" ' . implode( ' ', $custom_attributes ) . '>';
					$field .= $args['label'] . $required . $tooltip_html . '</label>';

					$checkbox_start = 0;

					$field .= '<ul ' . $choice_limit_attr . '>';

					if ( 'yes' === $select_all ) {
						$field .= '<li class="ur-checkbox-list"><input type="checkbox" id="checkall" class="ur-input-checkbox"  data-check="' . esc_attr( $key ) . '"/>';
						$field .= '<label class="ur-checkbox-label">  ' . esc_html__( 'Select All', 'user-registration' ) . '</label></li>';
					}
					foreach ( $choices as $choice_index => $choice ) {

						$value = '';
						if ( '' !== $default ) {
							if ( is_array( $default ) && in_array( trim( $choice_index ), $default ) ) {
								$value = 'checked="checked"';
							} elseif ( $default === $choice_index ) {
								$value = 'checked="checked"';
							}
						}
						$field .= '<li class="ur-checkbox-list">';
						$field .= '<input data-rules="' . esc_attr( $rules ) . '" data-id="' . esc_attr( $key ) . '" ' . implode( ' ', $custom_attributes ) . ' data-value="' . esc_attr( $choice_index ) . '" type="' . esc_attr( $args['type'] ) . '" class="input-checkbox ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '[]" id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $choice_index ) . '" value="' . trim( $choice_index ) . '"' . esc_attr( $value ) . ' /> ';
						$field .= '<label class="ur-checkbox-label" for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $choice_index ) . '">' . trim( $choice ) . '</label> </li>';
						$checkbox_start++;
					}
					$field .= '</ul>';
				} else {
					$field = '<label class="ur-label checkbox" ' . implode( ' ', $custom_attributes ) . '>
							<input data-rules="' . esc_attr( $rules ) . '" data-id="' . esc_attr( $key ) . '" ' . implode( ' ', $custom_attributes ) . ' data-value="' . $value . '" type="' . esc_attr( $args['type'] ) . '" class="input-checkbox ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="1" ' . checked( $value, 1, false ) . ' /> '
						. $args['label'] . $required . $tooltip_html . '</label>';
				}
				break;

			case 'password':
				$extra_params_key = str_replace( 'user_registration_', 'ur_', $key ) . '_params';
				$extra_params     = json_decode( get_user_meta( get_current_user_id(), $extra_params_key, true ) );
				$field .= ' <span class="input-wrapper"> ';
				if ( empty( $extra_params ) ) {
					$field_container = '<div class="form-row %1$s hide_show_password" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</div>';
					$field          .= '<span class="password-input-group input-form-field-icons">';
					$field          .= '<input data-rules="' . esc_attr( $rules ) . '" data-id="' . esc_attr( $key ) . '" type="' . esc_attr( $args['type'] ) . '" class="input-text ' . $class . ' input-' . esc_attr( $args['type'] ) . ' ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . ' />';
					if ( 'yes' === get_option( 'user_registration_login_option_hide_show_password', 'no' ) ) {
						$field .= '<a href="javaScript:void(0)" class="password_preview dashicons dashicons-hidden" title=" Show password "></a>';
					}
					$field .= '</span>';
				} else {
					$field .= '<input data-rules="' . esc_attr( $rules ) . '" data-id="' . esc_attr( $key ) . '" type="' . esc_attr( $args['type'] ) . '" class="input-text  ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . ' />';
				}
				if ( ! is_admin() ) {
					$field  = apply_filters( 'user_registration_field_icon', $field, $form_id, $args );
					$field .= ' </span> ';
				}
				break;

			case 'text':
			case 'email':
			case 'tel':
			case 'number':
			case 'url':
			case 'file':
			case 'timepicker':
				$extra_params_key = str_replace( 'user_registration_', 'ur_', $key ) . '_params';
				$extra_params     = json_decode( get_user_meta( get_current_user_id(), $extra_params_key, true ) );
				$current_time     = isset( $args['current_time'] ) ? $args['current_time'] : '';
				$time_interval    = isset( $args['time_interval'] ) ? $args['time_interval'] : '';
				$time_min    = isset( $args['time_min'] ) ? $args['time_min'] : '';
				$time_max    = isset( $args['time_max'] ) ? $args['time_max'] : '';
				$username_length  = isset( $args['username_length'] ) ? $args['username_length'] : '';
				$username_character = isset( $args['username_character'] ) ? $args['username_character'] : '';
				$attr = '';
				if ( '' !== $username_length ) {
					$attr .= 'data-username-length="' . $username_length . '"';
				}

				if ( $username_character ) {
					$attr .= 'data-username-character="' . $username_character . '"';
				}

				if ( '' !== $time_interval ) {
					$attr .= 'data-time-interval="' . $time_interval . '"';
				}

				if ( '' !== $time_min ) {
					$attr .= 'data-time-min="' . $time_min . '"';
				}

				if ( '' !== $time_max ) {
					$attr .= 'data-time-max="' . $time_max . '"';
				}

				if ( $current_time ) {
					$attr .= 'data-current-time="' . $current_time . '"';
				}

				$field .= ' <span class="input-wrapper"> ';
				if ( isset( $args['autocomplete_address'] ) && 'yes' == $args['autocomplete_address'] ) {
					$attr .= 'data-autocomplete-address="' . $args['autocomplete_address'] . '"';
					$attr .= 'data-address-style="' . $args['address_style'] . '"';
					$attr .= 'data-current-location="' . get_option( 'user_registration_google_map_current_location', '' ) . '"';
					if ( 'map' == $args['address_style'] ) {
						$field .= '<div id="ur-geolocation-map" class="ur-geolocation-map"></div>';
					}
				}
				if ( empty( $extra_params ) ) {
					$field .= '<input data-rules="' . esc_attr( $rules ) . '" data-id="' . esc_attr( $key ) . '" type="' . esc_attr( $args['type'] ) . '" class="input-text ' . $class . ' input-' . esc_attr( $args['type'] ) . ' ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . ' ' . $attr . '/>';
				} else {
					$field .= '<input data-rules="' . esc_attr( $rules ) . '" data-id="' . esc_attr( $key ) . '" type="' . esc_attr( $args['type'] ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . ' ' . $attr . ' />';
				}

				if ( ! is_admin() ) {
					$field  = apply_filters( 'user_registration_field_icon', $field, $form_id, $args );
					$field .= ' </span> ';
				}
				break;

			case 'date':
				$extra_params_key = str_replace( 'user_registration_', 'ur_', $key ) . '_params';
				$extra_params     = json_decode( get_user_meta( get_current_user_id(), $extra_params_key, true ) );

				$actual_value = $value;
				if ( isset( $args['custom_attributes']['data-date-format'] ) ) {
					$date_format  = $args['custom_attributes']['data-date-format'];
					$default_date = isset( $args['custom_attributes']['data-default-date'] ) ? $args['custom_attributes']['data-default-date'] : '';
					if ( empty( $value ) && 'today' === $default_date ) {
						$value        = date_i18n( $date_format );
						$actual_value = date_i18n( $date_format );
					} else {
						$value = str_replace( '/', '-', $value );
						if ( ! strpos( $value, 'to' ) ) {
							$value = '' !== $value ? date_i18n( $date_format, strtotime( $value ) ) : '';
						} else {
							$date_range = explode( 'to', $value );
							$value      = date_i18n( $date_format, strtotime( trim( $date_range[0] ) ) ) . ' to ' . date_i18n( $date_format, strtotime( trim( $date_range[1] ) ) );
						}
					}
				}

				$field .= ' <span class="input-wrapper"> ';

				if ( empty( $extra_params ) ) {
					$field .= '<input data-rules="' . esc_attr( $rules ) . '" data-id="' . esc_attr( $key ) . '" type="text" id="load_flatpickr" value="' . esc_attr( $actual_value ) . '" class="regular-text ' . esc_attr( $class ) . '" readonly placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . implode( ' ', $custom_attributes ) . ' />';
					$field .= '<input type="hidden" id="formated_date" value="' . esc_attr( $value ) . '"/>';
					$field .= '<input data-rules="' . esc_attr( $rules ) . '" data-id="' . esc_attr( $key ) . '" type="text" data-field-type="' . esc_attr( $args['type'] ) . '" value="' . esc_attr( $actual_value ) . '" class="input-text input-' . esc_attr( $args['type'] ) . ' ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  ' . implode( ' ', $custom_attributes ) . ' style="display:none"/>';
				} else {
					$field .= '<input data-rules="' . esc_attr( $rules ) . '" data-id="' . esc_attr( $key ) . '" type="text" id="load_flatpickr" value="' . esc_attr( $actual_value ) . '"  class="regular-text ' . $class . '" readonly placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . implode( ' ', $custom_attributes ) . ' />';
					$field .= '<input type="hidden" id="formated_date" value="' . esc_attr( $value ) . '"/>';
					$field .= '<input data-rules="' . esc_attr( $rules ) . '" data-id="' . esc_attr( $key ) . '" type="text" data-field-type="' . esc_attr( $args['type'] ) . '" value="' . esc_attr( $actual_value ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  ' . implode( ' ', $custom_attributes ) . ' style="display:none" />';
				}

				if ( ! is_admin() ) {
					$field = apply_filters( 'user_registration_field_icon', $field, $form_id, $args );
				}
				$field .= '</span> ';
				break;

			case 'color':
				$field .= '<input data-rules="' . esc_attr( $rules ) . '" data-id="' . esc_attr( $key ) . '" type="text" class="input-text input-color ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $args['default'] ) . '" ' . implode( ' ', $custom_attributes ) . ' />';
				break;

			case 'select':
				$default_value = isset( $args['default_value'] ) ? $args['default_value'] : ''; // Backward compatibility. Modified since 1.5.7.

				$value   = ! empty( $value ) ? $value : $default_value;
				$options = $field .= '';
				if ( ! empty( $args['options'] ) ) {
					// If we have a blank option, select2 needs a placeholder.
					if ( '' === $value && ! empty( $args['placeholder'] ) ) {
						$options .= '<option value="" selected disabled>' . esc_html( $args['placeholder'] ) . '</option>';
					}

					$custom_attributes[] = 'data-allow_clear="true"';
					foreach ( $args['options'] as $option_key => $option_text ) {
						$selected_attribute = '';

						if ( '' !== $value ) {
							$selected_attribute = selected( $value, trim( $option_key ), false );
						}
						$options .= '<option value="' . esc_attr( trim( $option_key ) ) . '" ' . $selected_attribute . '>' . esc_attr( trim( $option_text ) ) . '</option>';
					}

					$field .= '<select data-rules="' . esc_attr( $rules ) . '" data-id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '">
							' . $options . '
						</select>';
				}
				break;

			case 'multiselect':
				$options = $field .= '';

				if ( is_serialized( $value ) ) {
					$default_value = unserialize( $value );
				} else {
					$default_value = $value;
				}

				if ( ! empty( $args['options'] ) ) {
					foreach ( $args['options'] as $option_key => $option_text ) {

						if ( '' === $option_key ) {
							// If we have a blank option, select2 needs a placeholder.
							if ( empty( $args['placeholder'] ) ) {
								$args['placeholder'] = $option_text ? $option_text : __( 'Choose an option', 'user-registration' );
							}
							$custom_attributes[] = 'data-allow_clear="true"';
						}

						if ( is_array( $default_value ) ) {
							$options .= '<option value="' . esc_attr( trim( $option_key ) ) . '" ' . selected( in_array( trim( $option_key ), $default_value ), true, false ) . '>' . esc_attr( trim( $option_text ) ) . '</option>';
						} else {
							$options .= '<option value="' . esc_attr( trim( $option_key ) ) . '" ' . selected( $default_value, trim( $option_key ), false ) . '>' . esc_attr( trim( $option_text ) ) . '</option>';
						}
					}

					$field .= '<select multiple data-rules="' . esc_attr( $rules ) . '" data-id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '[]" id="' . esc_attr( $args['id'] ) . '" class="select ur-enhanced-select' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '">
							' . $options . '
						</select>';
				}
				break;

			case 'radio':
				$default_value = isset( $args['default_value'] ) ? $args['default_value'] : ''; // Backward compatibility. Modified since 1.5.7.
				$value         = ! empty( $value ) ? $value : $default_value;
				$label_id      = current( array_keys( $args['options'] ) );
				if ( ! empty( $args['options'] ) ) {

					$field .= '<ul>';
					foreach ( $args['options'] as $option_index => $option_text ) {

						$field .= '<li class="ur-radio-list">';

						$checked = '';
						if ( ! empty( $value ) ) {
							$checked = checked( $value, trim( $option_index ), false );
						}

						$field .= '<input data-rules="' . esc_attr( $rules ) . '" data-id="' . esc_attr( $key ) . '" type="radio" class="input-radio ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( trim( $option_index ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_text ) . '" ' . implode( ' ', $custom_attributes ) . ' / ' . $checked . ' /> ';
						$field .= '<label for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_text ) . '" class="radio">';

						$field .= wp_kses(
							trim( $option_text ),
							array(
								'a'    => array(
									'href'  => array(),
									'title' => array(),
								),
								'span' => array(),
							)
						) . '</label></li>';
					}
					$field .= '</ul>';
				}
				break;
		} // End switch().

		if ( $args['description'] ) {
			$field .= '<span class="description">' . $args['description'] . '</span>';
		}

		if ( ! empty( $field ) ) {

			$field_html = '';
			if ( $args['label'] && 'checkbox' != $args['type'] ) {
				$field_html .= '<label for="' . esc_attr( $label_id ) . '" class="ur-label">' . wp_kses(
					$args['label'],
					array(
						'a'    => array(
							'href'  => array(),
							'title' => array(),
						),
						'span' => array(),
					)
				) . $required . $tooltip_html . '</label>';
			}

			$field_html     .= $field;
			$container_class = esc_attr( implode( ' ', $args['class'] ) );
			$container_id    = esc_attr( $args['id'] ) . '_field';
			$field           = sprintf( $field_container, $container_class, $container_id, $field_html );
		}

		$field = apply_filters( 'user_registration_form_field_' . $args['type'], $field, $key, $args, $value );
		if ( $args['return'] ) {
			return $field;
		} else {
			echo $field; //PHPCS:ignore;
		}
	}
} // End if().

if ( ! function_exists( 'user_registration_form_data' ) ) {

	/**
	 * Get form fields to display on profile tab
	 *
	 * @param string $user_id User Id.
	 * @param string $form_id Form Id.
	 *
	 * @return array
	 */
	function user_registration_form_data( $user_id = 0, $form_id = 0 ) {
		$all_meta_value = get_user_meta( $user_id );
		$user_details   = get_user_by( 'ID', $user_id );
		$user_info      = (array) $user_details->data;
		$fields         = array();

		$post_content_array = ( $form_id ) ? UR()->form->get_form( $form_id, array( 'content_only' => true ) ) : array();

		$all_meta_value_keys = array();
		if ( gettype( $all_meta_value ) === 'array' ) {
			$all_meta_value_keys = array_keys( $all_meta_value );
		}

		$post_content_array = apply_filters( 'user_registration_profile_account_filter_all_fields', $post_content_array, $form_id );

		foreach ( $post_content_array as $post_content_row ) {
			foreach ( $post_content_row as $post_content_grid ) {
				foreach ( $post_content_grid as $field ) {
					$field_name        = isset( $field->general_setting->field_name ) ? $field->general_setting->field_name : '';
					$field_label       = isset( $field->general_setting->label ) ? $field->general_setting->label : '';
					$field_description = isset( $field->general_setting->description ) ? $field->general_setting->description : '';
					$placeholder       = isset( $field->general_setting->placeholder ) ? $field->general_setting->placeholder : '';
					$options           = isset( $field->general_setting->options ) ? $field->general_setting->options : array();
					$field_key         = isset( $field->field_key ) ? ( $field->field_key ) : '';
					$field_type        = isset( $field->field_key ) ? ur_get_field_type( $field_key ) : '';
					$required          = isset( $field->general_setting->required ) ? $field->general_setting->required : '';
					$required          = 'yes' == $required ? true : false;
					$enable_cl         = isset( $field->advance_setting->enable_conditional_logic ) && ( '1' === $field->advance_setting->enable_conditional_logic || 'on' === $field->advance_setting->enable_conditional_logic ) ? true : false;
					$cl_map            = isset( $field->advance_setting->cl_map ) ? $field->advance_setting->cl_map : '';
					$custom_attributes = isset( $field->general_setting->custom_attributes ) ? $field->general_setting->custom_attributes : array();
					$enable_validate_unique = isset( $field->advance_setting->validate_unique ) ? $field->advance_setting->validate_unique : false;
					$validate_message       = isset( $field->advance_setting->validation_message ) ? $field->advance_setting->validation_message : esc_html__( 'This field value need to be unique.', 'user-registration' );

					if ( empty( $field_label ) ) {
						$field_label_array = explode( '_', $field_name );
						$field_label       = join( ' ', array_map( 'ucwords', $field_label_array ) );
					}

					if ( ! empty( $field_name ) ) {
						$extra_params = array();

						switch ( $field_key ) {

							case 'radio':
							case 'select':
								$advanced_options        = isset( $field->advance_setting->options ) ? $field->advance_setting->options : '';
								$advanced_options        = explode( ',', $advanced_options );
								$extra_params['options'] = ! empty( $options ) ? $options : $advanced_options;
								$extra_params['options'] = array_map( 'trim', $extra_params['options'] );

								$extra_params['options'] = array_combine( $extra_params['options'], $extra_params['options'] );

								break;

							case 'checkbox':
								$advanced_options        = isset( $field->advance_setting->choices ) ? $field->advance_setting->choices : '';
								$advanced_options        = explode( ',', $advanced_options );
								$extra_params['options'] = ! empty( $options ) ? $options : $advanced_options;
								$extra_params['options'] = array_map( 'trim', $extra_params['options'] );

								$extra_params['options'] = array_combine( $extra_params['options'], $extra_params['options'] );

								break;

							case 'date':
								$date_format       = isset( $field->advance_setting->date_format ) ? $field->advance_setting->date_format : '';
								$min_date          = isset( $field->advance_setting->min_date ) ? str_replace( '/', '-', $field->advance_setting->min_date ) : '';
								$max_date          = isset( $field->advance_setting->max_date ) ? str_replace( '/', '-', $field->advance_setting->max_date ) : '';
								$set_current_date  = isset( $field->advance_setting->set_current_date ) ? $field->advance_setting->set_current_date : '';
								$enable_date_range = isset( $field->advance_setting->enable_date_range ) ? $field->advance_setting->enable_date_range : '';
								$date_localization = isset( $field->advance_setting->date_localization ) ? $field->advance_setting->date_localization : '';
								$extra_params['custom_attributes']['data-date-format'] = $date_format;

								if ( isset( $field->advance_setting->enable_min_max ) && 'true' === $field->advance_setting->enable_min_max ) {
									$extra_params['custom_attributes']['data-min-date'] = '' !== $min_date ? date_i18n( $date_format, strtotime( $min_date ) ) : '';
									$extra_params['custom_attributes']['data-max-date'] = '' !== $max_date ? date_i18n( $date_format, strtotime( $max_date ) ) : '';
								}
								$extra_params['custom_attributes']['data-default-date'] = $set_current_date;
								$extra_params['custom_attributes']['data-mode']         = $enable_date_range;
								$extra_params['custom_attributes']['data-locale']       = $date_localization;
								break;

							case 'country':
								$class_name              = ur_load_form_field_class( $field_key );
								$extra_params['options'] = $class_name::get_instance()->get_selected_countries( $form_id, $field_name );
								break;

							case 'file':
								$extra_params['max_files'] = isset( $field->general_setting->max_files ) ? $field->general_setting->max_files : '';
								break;

							case 'phone':
								$extra_params['phone_format'] = isset( $field->general_setting->phone_format ) ? $field->general_setting->phone_format : '';
								break;

							default:
								break;
						}

						$extra_params['default'] = isset( $all_meta_value[ 'user_registration_' . $field_name ][0] ) ? $all_meta_value[ 'user_registration_' . $field_name ][0] : ( isset( $all_meta_value[ $field_name ][0] ) ? $all_meta_value[ $field_name ][0] : '' );
						if ( empty( $extra_params['default'] ) ) {
							$extra_params['default'] = isset( $user_info[ $field_name ] ) ? $user_info[ $field_name ] : '';
						}

						if ( in_array( $field_key, ur_get_user_profile_field_only() ) ) {

							$fields[ 'user_registration_' . $field_name ] = array(
								'label'       => ur_string_translation( $form_id, 'user_registration_' . $field_name . '_label', $field_label ),
								'description' => ur_string_translation( $form_id, 'user_registration_' . $field_name . '_description', $field_description ),
								'type'        => $field_type,
								'placeholder' => ur_string_translation( $form_id, 'user_registration_' . $field_name . '_placeholder', $placeholder ),
								'field_key'   => $field_key,
								'required'    => $required,
							);

							if ( true === $enable_cl ) {
								$fields[ 'user_registration_' . $field_name ]['enable_conditional_logic'] = $enable_cl;
								$fields[ 'user_registration_' . $field_name ]['cl_map']                   = $cl_map;
							}
						}

						if ( true === $enable_cl ) {
							$fields[ 'user_registration_' . $field_name ]['enable_conditional_logic'] = $enable_cl;
							$fields[ 'user_registration_' . $field_name ]['cl_map']                   = $cl_map;
						}

						if ( count( $custom_attributes ) > 0 ) {
							$extra_params['custom_attributes'] = $custom_attributes;
						}

						if ( isset( $field->advance_setting->validate_unique ) ) {
							$fields[ 'user_registration_' . $field_name ]['validate_unique']  = $enable_validate_unique;
							$fields[ 'user_registration_' . $field_name ]['validate_message'] = $validate_message;
						}

						if ( isset( $fields[ 'user_registration_' . $field_name ] ) && count( $extra_params ) > 0 ) {
							$fields[ 'user_registration_' . $field_name ] = array_merge( $fields[ 'user_registration_' . $field_name ], $extra_params );
						}
						$filter_data = array(
							'fields'     => $fields,
							'field'      => $field,
							'field_name' => $field_name,
						);

						$filtered_data_array = apply_filters( 'user_registration_profile_account_filter_' . $field_key, $filter_data, $form_id );
						if ( isset( $filtered_data_array['fields'] ) ) {
							$fields = $filtered_data_array['fields'];
						}
					} // End if().
				} // End foreach().
			} // End foreach().
		} // End foreach().
		return $fields;
	}
} // End if().

if ( ! function_exists( 'user_registration_account_content' ) ) {

	/**
	 * My Account content output.
	 */
	function user_registration_account_content() {
		global $wp;

		if ( ! empty( $wp->query_vars ) ) {
			foreach ( $wp->query_vars as $key => $value ) {
				// Ignore pagename param.
				if ( 'pagename' === $key ) {
					continue;
				}

				if ( has_action( 'user_registration_account_' . $key . '_endpoint' ) ) {
					do_action( 'user_registration_account_' . $key . '_endpoint', $value );
					return;
				}
			}
		}

		// No endpoint found? Default to dashboard.
		ur_get_template(
			'myaccount/dashboard.php',
			array(
				'current_user' => get_user_by( 'id', get_current_user_id() ),
			)
		);
	}
}

if ( ! function_exists( 'user_registration_account_navigation' ) ) {

	/**
	 * My Account navigation template.
	 */
	function user_registration_account_navigation() {
		ur_get_template( 'myaccount/navigation.php' );
	}
}

if ( ! function_exists( 'user_registration_account_edit_profile' ) ) {

	/**
	 * My Account > Edit profile template.
	 */
	function user_registration_account_edit_profile() {
		 UR_Shortcode_My_Account::edit_profile();
	}
}

if ( ! function_exists( 'user_registration_account_edit_account' ) ) {

	/**
	 * My Account > Edit account template.
	 */
	function user_registration_account_edit_account() {
		 UR_Shortcode_My_Account::edit_account();
	}
}

/**
 * Get logout endpoint.
 *
 * @param string $redirect URL.
 *
 * @return string
 */
function ur_logout_url( $redirect = '' ) {
	$logout_endpoint = get_option( 'user_registration_logout_endpoint' );

	global $post;
	$wp_version = '5.0';
	$post_content = isset( $post->post_content ) ? $post->post_content : '';

	if ( ( ur_post_content_has_shortcode( 'user_registration_login' ) || ur_post_content_has_shortcode( 'user_registration_my_account' ) ) && is_user_logged_in() ) {
		if ( version_compare( $GLOBALS['wp_version'], $wp_version, '>=' ) ) {
			$blocks = parse_blocks( $post_content );
			$new_shortcode = '';

			foreach ( $blocks as $block ) {
				if ( 'core/shortcode' === $block['blockName'] && isset( $block['innerHTML'] ) ) {
					$new_shortcode = $block['innerHTML'];
				} elseif ( 'user-registration/form-selector' === $block['blockName'] && isset( $block['attrs']['shortcode'] ) ) {
					$new_shortcode = '[' . $block['attrs']['shortcode'] . ']';
				}

				if ( 'user-registration/form-selector' === $block['blockName'] && isset( $block['attrs']['logoutUrl'] ) ) {
					$redirect = $block['attrs']['logoutUrl'];
				}
			}
			preg_match( '/' . get_shortcode_regex() . '/s', $new_shortcode, $matches );

		} else {
			preg_match( '/' . get_shortcode_regex() . '/s', $post_content, $matches );
		}

		$matches_attr = isset( $matches[3] ) ? $matches[3] : '';
		$attributes = shortcode_parse_atts( $matches_attr );
		/**
		 * Introduced logout_redirect parameter in user_registration_my_account shortcode.
		 *
		 * @since  1.7.5
		 */
		if ( isset( $attributes['logout_redirect'] ) ) {
			$redirect = isset( $attributes['logout_redirect'] ) ? $attributes['logout_redirect'] : '';
			$redirect = trim( $redirect, ']' );
			$redirect = trim( $redirect, '"' );
			$redirect = trim( $redirect, "'" );
			$redirect = '' != $redirect ? home_url( $redirect ) : ur_get_page_permalink( 'myaccount' );
		}
	} else {
		$blocks = parse_blocks( $post->post_content );

		foreach ( $blocks as $block ) {
			if ( 'user-registration/form-selector' === $block['blockName'] && isset( $block['attrs']['logoutUrl'] ) ) {
				$redirect = home_url( $block['attrs']['logoutUrl'] );
			}
		}
	}
	$redirect = apply_filters( 'user_registration_redirect_after_logout', $redirect );

	if ( $logout_endpoint && ! is_front_page() ) {
		return wp_nonce_url( ur_get_endpoint_url( 'user-logout', '', $redirect ), 'user-logout' );
	} else {
		if ( '' === $redirect ) {
			$redirect = home_url();
		}
		return wp_logout_url( $redirect );
	}
}

/**
 * See if current page elementor page for editing.
 *
 * @since 1.8.5
 *
 * @return bool
 */
function is_elementor_editing_page() {
	return ( ! empty( $_POST['action'] ) && 'elementor_ajax' === $_POST['action'] ) || //PHPCS:ignore;
		! empty( $_GET['elementor-preview'] ) ||
		( ! empty( $_GET['action'] ) && 'elementor' === $_GET['action'] );
}
