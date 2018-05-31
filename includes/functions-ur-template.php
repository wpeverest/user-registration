<?php
/**
 * UserRegistration Template
 *
 * Functions for the templating system.
 *
 * @author   WPEverest
 * @category Core
 * @package  UserRegistration/Functions
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Handle redirects before content is output - hooked into template_redirect so is_page works.
 */

function ur_template_redirect() {
	global $wp;

	if ( isset( $wp->query_vars['user-logout'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'user-logout' ) ) {
		// Logout
		wp_safe_redirect( str_replace( '&amp;', '&', wp_logout_url( ur_get_page_permalink( 'myaccount' ) ) ) );
		exit;
	} elseif ( isset( $wp->query_vars['user-logout'] ) && 'true' === $wp->query_vars['user-logout'] ) {
		// Redirect to the correct logout endpoint.
		wp_safe_redirect( esc_url_raw( ur_get_page_permalink( 'user-logout' ) ) );
		exit;
	}
}

add_action( 'template_redirect', 'ur_template_redirect' );
add_action( 'template_redirect', 'ur_login_template_redirect' );

/**
 * Handle redirects before content is output - hooked into template_redirect so is_page works.
 */
function ur_login_template_redirect() {
	global $post;
	$post_content = isset( $post->post_content ) ? $post->post_content : '';
	if ( has_shortcode( $post_content, 'user_registration_login' ) && is_user_logged_in() ) {
		preg_match( '/' . get_shortcode_regex() . '/s', $post_content, $matches );
		$attributes = shortcode_parse_atts( $matches[3] );

		$redirect_url = isset( $attributes['redirect_url'] ) ? $attributes['redirect_url'] : '';

		$redirect_url = trim( $redirect_url, ']' );
		$redirect_url = trim( $redirect_url, '"' );
		$redirect_url = trim( $redirect_url, "'" );


		if ( ! empty( $redirect_url ) ) {
			wp_redirect( $redirect_url );
		}
	}
}

/**
 * Add body classes for UR pages.
 *
 * @param  array $classes
 *
 * @return array
 */
function ur_body_class( $classes ) {
	$classes = (array) $classes;

	if ( is_ur_account_page() ) {

		$classes[] = 'user-registration-account';
		$classes[] = 'user-registration-page';
	}

	foreach ( UR()->query->query_vars as $key => $value ) {
		if ( is_ur_endpoint_url( $key ) ) {
			$classes[] = 'user-registration-' . sanitize_html_class( $key );
		}
	}

	return array_unique( $classes );
}


/** Forms */

if ( ! function_exists( 'user_registration_form_field' ) ) {

	/**
	 * Outputs a profile form field.
	 *
	 * @param string $key
	 * @param mixed  $args
	 * @param string $value (default: null)
	 *
	 * @return string
	 */
	function user_registration_form_field( $key, $args, $value = null ) {

		$defaults = array(
			'type'              => 'text',
			'label'             => '',
			'description'       => '',
			'placeholder'       => '',
			'maxlength'         => false,
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

		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required        = ' <abbr class="required" title="' . esc_attr__( 'required', 'user-registration' ) . '">*</abbr>';
		} else {
			$required = '';
		}

		if ( is_null( $value ) ) {
			$value = $args['default'];
		}

		// Custom attribute handling
		$custom_attributes         = array();
		$args['custom_attributes'] = array_filter( (array) $args['custom_attributes'] );

		if ( $args['maxlength'] ) {
			$args['custom_attributes']['maxlength'] = absint( $args['maxlength'] );
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

		$field           = '';
		$label_id        = $args['id'];
		$sort            = $args['priority'] ? $args['priority'] : '';
		$field_container = '<p class="form-row %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</p>';
		
		if ( $args['description'] ) {
			$field .= '<span class="description">' . $args['description'] . '</span>';
		}

		switch ( $args['type'] ) {

			case 'textarea' :

				$field .= '<textarea data-id=""' . esc_attr( $key ) . '"" name="' . esc_attr( $key ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . ( empty( $args['custom_attributes']['rows'] ) ? ' rows="2"' : '' ) . ( empty( $args['custom_attributes']['cols'] ) ? ' cols="5"' : '' ) . implode( ' ', $custom_attributes ) . '>' . esc_textarea( $value ) . '</textarea>';

				break;

			case 'checkbox' :

			$field_key = isset( $args['field_key'] ) ? $args['field_key'] : '';			
			
			if( 'privacy_policy' == $field_key ) {
				break;
			}

			if( isset($args['choices']) && count($args['choices']) >1 ){

				//TODO
				//$default = !empty($args['default']) ? unserialize( $args['default'] ) : array();		
				$default = array();

				$choices = isset( $args['choices'] ) ? $args['choices'] : array();

				$field   = '<label class="checkbox ' . implode( ' ', $custom_attributes ) . '">';
				$field   .= $args['label'] . $required . '</label>';
				$checkbox_start =0;
				foreach ( $choices as $choice_index => $choice ) {
					
					$value = '';
					if ( in_array(trim($choice), $default) ) {
						$value = 'checked="checked"';
					}

					$field .= '<label>';
					$field .= ' <input data-id="' . esc_attr( $key ) . '" ' . implode( ' ', $custom_attributes ) . ' data-value="' . $choice_index . '" type="' . esc_attr( $args['type'] ) . '" class="input-checkbox ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '[]" id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $choice_index ) . '" value="'.trim($choice).'"' . $value . ' /> ';
					$field .= trim( $choice ) . ' </label>';


					$checkbox_start++;
				}
			}
			else
			{
				$field = '<label class="checkbox ' . implode( ' ', $custom_attributes ) . '">
						<input data-id="' . esc_attr( $key ) . '" ' . implode( ' ', $custom_attributes ) . ' data-value="' . $value . '" type="' . esc_attr( $args['type'] ) . '" class="input-checkbox ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="1" ' . checked( $value, 1, false ) . ' /> '
				         . $args['label'] . $required . '</label>';

			}
			
			if ( $args['description'] ) {
				$field .= '<span class="description">' . $args['description'] . '</span>';
			}


			break;
			case 'password' :
			case 'text' :
			case 'email' :
			case 'tel' :
			case 'number' :
			case 'url' :
			case 'date':
			case 'file':

				$extra_params_key = str_replace( 'user_registration_', 'ur_', $key ) . '_params';
				$extra_params     = json_decode( get_user_meta( get_current_user_id(), $extra_params_key, true ) );

				if ( empty( $extra_params ) ) {
					$field .= '<input data-id="' . esc_attr( $key ) . '" type="' . esc_attr( $args['type'] ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . ' />';
				}
				else
				{ 
					$field .= '<input data-id="' . esc_attr( $key ) . '" type="' . esc_attr( $args['type'] ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . ' />';
				}

				break;
			case 'select' :
				$options = $field = '';
				if ( ! empty( $args['options'] ) ) {
					foreach ( $args['options'] as $option_key => $option_text ) {

						if ( '' === $option_key ) {
							// If we have a blank option, select2 needs a placeholder
							if ( empty( $args['placeholder'] ) ) {
								$args['placeholder'] = $option_text ? $option_text : __( 'Choose an option', 'user-registration' );
							}
							$custom_attributes[] = 'data-allow_clear="true"';
						}
						$options .= '<option value="' . esc_attr( trim( $option_key ) ) . '" ' . selected( $value, trim( $option_key ), false ) . '>' . esc_attr( trim( $option_text ) ) . '</option>';
					}

					$field .= '<select data-id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '">
							' . $options . '
						</select>';					
				}
				if ( $args['description'] ) {
					$field .= '<span class="description">' . $args['description'] . '</span>';
				}

				
				break;
			case 'radio' :
				$label_id = current( array_keys( $args['options'] ) );
				if ( ! empty( $args['options'] ) ) {
					foreach ( $args['options'] as $option_key => $option_text ) {
									
						$field .= '<label for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '" class="radio">';

						$field .= '<input data-id="' . esc_attr( $key ) . '" type="radio" class="input-radio ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( trim ( $option_key ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '" ' . implode( ' ', $custom_attributes ) . ' / ' . checked( $value, trim( $option_key ), false ) . ' />' . wp_kses( trim( $option_text ), array(
								'a'    => array(
									'href'  => array(),
									'title' => array()
								),
								'span' => array()
							) ) . '</label>';
					}
				}

			break;
		}// End switch().

		if ( ! empty( $field ) ) {
			$field_html = '';

			if ( $args['label'] && 'checkbox' != $args['type'] ) {
			
				$field_html .= '<label for="' . esc_attr( $label_id ) . '">' . wp_kses( $args['label'], array(
						'a'    => array(
							'href'  => array(),
							'title' => array()
						),
						'span' => array()
					) ) . $required . '</label>';
			}

			$field_html .= $field;

			$container_class = esc_attr( implode( ' ', $args['class'] ) );
			$container_id    = esc_attr( $args['id'] ) . '_field';
			$field           = sprintf( $field_container, $container_class, $container_id, $field_html );
		}

		$field = apply_filters( 'user_registration_form_field_' . $args['type'], $field, $key, $args, $value );

		if ( $args['return'] ) {
			return $field;
		} else {
			echo $field;
		}
	}
}// End if().

if ( ! function_exists( 'user_registration_form_data' ) ) {

	/**
	 * Get a profile form field data.
	 *
	 * @param string $user_id
	 * @param string $form_id
	 *
	 * @return array
	 */
	function user_registration_form_data( $user_id = 0, $form_id = 0 ) {
		$all_meta_value = get_user_meta( $user_id );
	
		$fields    = array();
		$args      = array(
			'post_type'   => 'user_registration',
			'post_status' => 'publish',
			'post__in'    => array( $form_id),
		);
		$post_data = get_posts( $args );
		$post_content       = isset( $post_data[0]->post_content ) ? $post_data[0]->post_content : '';
		$post_content_array = json_decode( $post_content );
		if ( gettype( $post_content_array ) != 'array' ) {
			return $fields;
		}

		$all_meta_value_keys = array();
		if ( gettype( $all_meta_value ) === 'array' ) {
			$all_meta_value_keys = array_keys( $all_meta_value );
		}

		foreach ( $post_content_array as $post_content_row ) {
			foreach ( $post_content_row as $post_content_grid ) {
				foreach ( $post_content_grid as $field ) {
					$field_name  = isset( $field->general_setting->field_name ) ? $field->general_setting->field_name : '';
					$field_label = isset( $field->general_setting->label ) ? $field->general_setting->label : '';
					$field_key   = isset( $field->field_key ) ? ( $field->field_key ) : '';
					$field_type  = isset( $field->field_key ) ? ur_get_field_type( $field_key ) : '';
					$required    = isset( $general_setting->required ) ? $general_setting->required :'';
					$required    = 'yes' == $required ? true : false;

					if ( empty( $field_label ) ) {
						$field_label_array = explode( '_', $field_name );
						$field_label       = join( ' ', array_map( 'ucwords', $field_label_array ) );
					}

					if ( ! empty( $field_name ) ) {
						$extra_params = array();

						switch ( $field_key ) {

							case 'radio':
							case 'select':
								$extra_params['options'] = explode( ',', $field->advance_setting->options );
								foreach ($extra_params['options'] as $key => $value) {
									$extra_params['options'][$value] = $value;
									unset($extra_params['options'][$key]);
								}							
								break;
							case 'checkbox':
								$extra_params['choices'] = explode( ',', $field->advance_setting->choices );
								foreach ($extra_params['choices'] as $key => $value) {
									$extra_params['choices'][$value] = $value;
									unset($extra_params['choices'][$key]);
								}
								break;
							case 'country':
								$class_name              = ur_load_form_field_class( $field_key );
								$extra_params['options'] = $class_name::get_instance()->get_country();
								break;
							default:
								break;
						}

						$extra_params['default'] = isset( $all_meta_value[ 'user_registration_' . $field_name ][0] ) ? $all_meta_value[ 'user_registration_' . $field_name ][0] : '';


						if ( in_array( 'user_registration_' . $field_name, $all_meta_value_keys ) ) {
							$fields[ 'user_registration_' . $field_name ] = array(
								'label'       => __( $field_label, 'user-registration' ),
								'description' => '',
								'type'        => $field_type,
								'field_key'   => $field_key,
								'required'    => $required,
							);
						} elseif ( in_array( $field_name, ur_get_user_profile_field_only() ) ) {
							$fields[ 'user_registration_' . $field_name ] = array(
								'label'       => __( $field_label, 'user-registration' ),
								'description' => '',
								'type'        => $field_type,
								'field_key'   => $field_key,
								'required'    => $required,
							);
						}

						if ( isset( $fields[ 'user_registration_' . $field_name ] ) && count( $extra_params ) > 0 ) {
							$fields[ 'user_registration_' . $field_name ] = array_merge( $fields[ 'user_registration_' . $field_name ], $extra_params );
						}
					}// End if().
				}// End foreach().
			}// End foreach().
		}// End foreach().

		
		return $fields;
	}
}// End if().

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
		ur_get_template( 'myaccount/dashboard.php', array(
			'current_user' => get_user_by( 'id', get_current_user_id() ),
		) );
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
 * @return string
 */
function ur_logout_url( $redirect = '' ) {
	$logout_endpoint = get_option( 'user_registration_logout_endpoint' );
	$redirect        = $redirect ? $redirect : ur_get_page_permalink( 'myaccount' );

	if ( $logout_endpoint ) {
		return wp_nonce_url( ur_get_endpoint_url( 'user-logout', '', $redirect ), 'user-logout' );
	} else {
		return wp_logout_url( $redirect );
	}
}