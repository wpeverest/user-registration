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

		switch ( $args['type'] ) {
			case 'textarea' :

				$field .= '<textarea name="' . esc_attr( $key ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . ( empty( $args['custom_attributes']['rows'] ) ? ' rows="2"' : '' ) . ( empty( $args['custom_attributes']['cols'] ) ? ' cols="5"' : '' ) . implode( ' ', $custom_attributes ) . '>' . esc_textarea( $value ) . '</textarea>';

				break;

			case 'checkbox' :

			$field_key = isset( $args['field_key'] ) ? $args['field_key'] : '';			
			if( 'privacy_policy' == $field_key ){
				break;
			}

			if(isset($args['choices']) && count($args['choices'])>1 ){

				$default = !empty($args['default']) ? json_decode( $args['default']  ) : array();

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
					$field .= ' <input ' . implode( ' ', $custom_attributes ) . ' data-value="' . $choice_index . '" type="' . esc_attr( $args['type'] ) . '" class="input-checkbox ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '[]" id="' . esc_attr( $args['id'] ) . '" value="'.trim($choice).'"' . $value . ' /> ';
					$field .= $choice . ' </label>';

					$checkbox_start++;
				}
			}
			else
			{
				$field = '<label class="checkbox ' . implode( ' ', $custom_attributes ) . '">
						<input ' . implode( ' ', $custom_attributes ) . ' data-value="' . $value . '" type="' . esc_attr( $args['type'] ) . '" class="input-checkbox ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="1" ' . checked( $value, 1, false ) . ' /> '
				         . $args['label'] . $required . '</label>';
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
					$field .= '<input type="' . esc_attr( $args['type'] ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . ' />';
				}
				else {
					$user_id = get_current_user_id();
					show_undefined_frontend_fields( $key, $user_id, $field, $extra_params);					
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
						$options .= '<option value="' . esc_attr( $option_key ) . '" ' . selected( $value, $option_key, false ) . '>' . esc_attr( $option_text ) . '</option>';
					}

					$field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '">
							' . $options . '
						</select>';
				}

				break;
			case 'radio' :
				$label_id = current( array_keys( $args['options'] ) );
				if ( ! empty( $args['options'] ) ) {
					foreach ( $args['options'] as $option_key => $option_text ) {
									
						$field .= '<label for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '" class="radio">';

						$field .= '<input type="radio" class="input-radio ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) . ' />' . wp_kses( $option_text, array(
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

			if ( $args['description'] ) {
				$field_html .= '<span class="description">' . esc_html( $args['description'] ) . '</span>';
			}

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
									$extra_params['options'][$key.'__'.$value] = $value;
									unset($extra_params['options'][$key]);
								}							
								break;
							case 'checkbox':
								$extra_params['choices'] = explode( ',', $field->advance_setting->choices );
								foreach ($extra_params['choices'] as $key => $value) {
									$extra_params['choices'][$key.'__'.$value] = $value;
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
						} elseif ( ! in_array( $field_name, ur_get_account_details_fields() ) ) {
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

		foreach ( $all_meta_value_keys as $single_key ) {
			if ( substr( $single_key, 0, strlen( 'user_registration_' ) ) == 'user_registration_' ) {
				if ( ! isset( $fields[ $single_key ] ) ) {

					$field_label_array     = explode( '_', str_replace( 'user_registration_', '', $single_key ) );
					$field_label           = join( ' ', array_map( 'ucwords', $field_label_array ) );
					$fields[ $single_key ] = array(
						'label'             => __( $field_label, 'user-registration' ),
						'description'       => '',
						'type'              => 'text',
						'field_key'         => 'text',
						'required'          => false,
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
						'default'           => isset( $all_meta_value[ $single_key ][0] ) ? $all_meta_value[ $single_key ][0] : '',
					);
				}
			}
		}

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

if ( ! function_exists( 'show_undefined_frontend_fields' ) ) {

	function show_undefined_frontend_fields( $key, $user_id, $field, $extra_params) {
		$value     = get_user_meta( $user_id, $key, true );
		$field_key = isset( $extra_params->field_key ) ? $extra_params->field_key : '';
		$label = isset( $extra_params->label ) ? $extra_params->label : '';

		switch ( $field_key ) {
			case "checkbox":
				$checkbox_array = json_decode( $value, true );
				
				if ( is_array( $checkbox_array ) && ! empty( $checkbox_array ) ) {
					echo '<label>'. $label . '</label>';
					foreach ( $checkbox_array as $check ) {

						echo '<label><input checked value="'. trim( $check ) .'" type="checkbox" disabled="disabled"/>' . esc_html( $check ) . '</label>';
					}
				} else {

					echo '<label><input checked type="checkbox" disabled="disabled"/>' . esc_html($label) .'</label>';
				}
			break;

			case "select":
			$old_value = $value;
			$result = explode('__', $value);
			if( is_array( $result ) && isset( $result[1] )){
				$value = $result[1];
			}
				echo '<label>'. $label . '</label>';
			?>	
				<select name="<?php echo esc_attr( $key ); ?>"
					    id="<?php echo esc_attr( $key );?>"

					    class="<?php echo( ! empty( $field['class'] ) ? esc_attr( $field['class'] ) : 'regular-text' ); ?>"
				disabled/>
					<option value="<?php echo esc_attr( $old_value );?>"><?php echo esc_attr( $value );?></option>
				</select>
			<?php

			break;

			case "radio":
			$old_value = $value;
			$result = explode('__', $value);
			if( is_array( $result ) && isset( $result[1] )){
				$value = $result[1];
			}
				echo '<label>'. $label . '</label>';
			?>
				<label><input type="radio" name="<?php echo esc_attr( $key ); ?>"
	       			id="<?php echo esc_attr( $key ); ?>"
	       			value="<?php echo esc_attr( $old_value ); ?>" checked="checked" disabled/> <?php echo esc_attr( $value );?></label>
			<?php
			break;

			case "country":
		
			include_once( dirname( __FILE__ ) . '\..\form\class-ur-country.php' );

			$country = new UR_Country;
			$countries = $country->get_country();

			if( is_array( $countries ) && array_key_exists( $value, $countries ) ){
					echo '<label>'. $label . '</label>';
				?>
					<select name="<?php echo esc_attr( $key ); ?>"
						    id="<?php echo esc_attr( $key );?>"
						    class="<?php echo( ! empty( $field['class'] ) ? esc_attr( $field['class'] ) : 'regular-text' ); ?>"
					disabled/>
					<option><?php echo esc_attr( $countries[$value] );?></option>
				<?php
			}
			
			break;

			default:
			echo '<label>'. $label . '</label>';
			?><input type="text" name="<?php echo esc_attr( $key ); ?>"
	       			id="<?php echo esc_attr( $key ); ?>"
	       			value="<?php echo esc_attr( $value ); ?>" disabled/>
	       	<?php
			
		}
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