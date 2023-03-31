<?php
/**
 * Add extra profile fields for users in admin
 *
 * @package  UserRegistration/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Admin_Profile', false ) ) :

	/**
	 * UR_Admin_Profile Class.
	 */
	class UR_Admin_Profile {

		/**
		 * Class Constructor.
		 */
		public function __construct() {
			add_action( 'show_user_profile', array( $this, 'show_user_extra_fields' ) );
			add_action( 'edit_user_profile', array( $this, 'show_user_extra_fields' ) );
			add_action( 'personal_options_update', array( $this, 'update_user_profile' ) );
			add_action( 'edit_user_profile_update', array( $this, 'update_user_profile' ) );
		}

		/**
		 * Get Customer meta fields.
		 *
		 * @deprecated 1.4.1
		 * @param array $all_meta_value meta value.
		 * @param int   $form_id Form Id.
		 */
		public function get_customer_meta_fields( $all_meta_value, $form_id ) {
			ur_deprecated_function( 'UR_Admin_Profile::get_customer_meta_fields', '1.4.1', 'UR_Admin_Profile::get_user_meta_by_form_fields' );
		}

		/**
		 * Get User extra fields from usermeta and integrate with form
		 *
		 * @param  int $user_id User Id.
		 * @return array Fields to display which are filtered through user_registration_profile_meta_fields before being returned
		 */
		public function get_user_meta_by_form_fields( $user_id ) {

			$show_fields       = array();
			$form_id           = ur_get_form_id_by_userid( $user_id );
			$all_meta_for_user = $this->get_user_meta_by_prefix( $user_id, 'user_registration_' );
			$form_fields       = $this->get_form_fields( $all_meta_for_user, $form_id );

			if ( ! empty( $form_fields ) ) {
				unset( $form_fields['user_registration_profile_pic_url'] );
				$show_fields = apply_filters(
					'user_registration_profile_meta_fields',
					array(
						'user_registration' => array(
							'title'  => sprintf( esc_html__( 'User Extra Information', 'user-registration' ), '' ),
							'fields' => $form_fields,
						),
					)
				);
			}
			return $show_fields;
		}

		/**
		 * Add cus;tomer meta fields.
		 *
		 * @deprecated 1.4.1
		 * @param array $all_meta_value Meta value.
		 * @param int   $form_id Form id.
		 */
		public function add_customer_meta_fields( $all_meta_value, $form_id ) {
			ur_deprecated_function( 'UR_Admin_Profile::add_customer_meta_fields', '1.4.1', 'UR_Admin_Profile::show_user_extra_fields' );
		}

		/**
		 * Show user extra information in users profile page.
		 *
		 * @param WP_User $user Users Data.
		 */
		public function show_user_extra_fields( $user ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$show_fields = $this->get_user_meta_by_form_fields( $user->ID );
			foreach ( $show_fields as $fieldset_key => $fieldset ) :
				?>
				<h2><?php echo esc_html( $fieldset['title'] ); ?></h2>
				<table class="form-table" id="<?php echo esc_attr( 'fieldset-' . $fieldset_key ); ?>">

					<?php
					$profile_field_type = array(
						'select',
						'country',
						'checkbox',
						'button',
						'textarea',
						'radio',
					);
					foreach ( $fieldset['fields'] as $key => $field ) :

						$field['label']       = isset( $field['label'] ) ? $field['label'] : '';
						$field['description'] = isset( $field['description'] ) ? $field['description'] : '';
						$attributes           = isset( $field['attributes'] ) ? $field['attributes'] : array();
						$attribute_string     = '';
						$date_format          = '';
						$date_mode            = '';

						foreach ( $attributes as $name => $value ) {
							if ( 'data-date-format' === $name ) {
								$date_format = $value;
							}
							if ( 'data-mode' === $name ) {
								$date_mode = $value;
							}
							if ( is_bool( $value ) ) {
								if ( $value ) {
									$attribute_string .= $name . ' ';
								}
							} else {
								$attribute_string .= sprintf( '%s="%s" ', $name, $value );
							}
						}

						$field_label = $field['label'];
						$field_type  = isset( $field['type'] ) ? $field['type'] : '';

						if ( ! in_array( $field_type, $profile_field_type, true ) ) {
							$extra_params_key = str_replace( 'user_registration_', 'ur_', $key ) . '_params';
							$extra_params     = json_decode( get_user_meta( $user->ID, $extra_params_key, true ) );
							$field_label      = isset( $extra_params->label ) ? $extra_params->label : $field_label;
						}
						?>
						<?php if ( 'multiple_choice' === $field_type || 'single_item' === $field_type || 'total_field' === $field_type ) { ?>
						<?php } else { ?>
						<tr>
							<th>
								<label
								for="<?php echo esc_attr( $key ); ?>">
												<?php
												echo esc_html( $field_label );
												?>
								</label>
								<p><span class="description"><?php echo wp_kses_post( $field['description'] ); ?></span></p>
							</th>
							<td>
								<?php if ( ! empty( $field['type'] ) && 'select' === $field['type'] ) : ?>
									<select name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>"
											class="<?php echo esc_attr( $field['class'] ); ?>" style="width: 25em;">
										<option><?php esc_html_e( 'Select', 'user-registration' ); ?></option>
										<?php
										$selected = get_user_meta( $user->ID, $key, true );
										foreach ( $field['options'] as $option_key => $option_value ) :
											?>
											<option value="<?php echo esc_attr( trim( $option_key ) ); ?>" <?php esc_attr( selected( $selected, trim( $option_key ), true ) ); ?>><?php echo esc_html( trim( $option_value ) ); ?></option>
										<?php endforeach; ?>
									</select>

								<?php elseif ( ! empty( $field['type'] ) && 'country' === $field['type'] ) : ?>
									<select name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>"
											class="<?php echo esc_attr( $field['class'] ); ?>" style="width: 25em;">
										<option><?php esc_html_e( 'Select', 'user-registration' ); ?></option>
										<?php
										$selected = esc_attr( get_user_meta( $user->ID, $key, true ) );
										foreach ( $field['options'] as $option_key => $option_value ) :
											?>
											<option
												value="<?php echo esc_attr( trim( $option_key ) ); ?>" <?php esc_attr( selected( $selected, $option_key, true ) ); ?>><?php echo esc_attr( trim( $option_value ) ); ?></option>
										<?php endforeach; ?>
									</select>

								<?php elseif ( ! empty( $field['type'] ) && 'radio' === $field['type'] ) : ?>
									<?php
									$db_value = get_user_meta( $user->ID, $key, true );
									if ( is_array( $field['options'] ) ) {
										foreach ( $field['options'] as $option_key => $option_value ) {
											?>
											<label><input type="radio"
															name="<?php echo esc_attr( $key ); ?>"
															id="<?php echo esc_attr( $key ); ?>"
															value="<?php echo esc_attr( trim( $option_key ) ); ?>"
															class="<?php echo esc_attr( $field['class'] ); ?>" <?php esc_attr( checked( $db_value, trim( $option_value ), true ) ); ?>  ><?php echo esc_html( trim( $option_value ) ); ?>
											</label><br/>
											<?php
										}
									}
									?>

								<?php elseif ( ! empty( $field['type'] ) && 'checkbox' === $field['type'] ) : ?>
									<?php

									$value = get_user_meta( $user->ID, $key, true );

									if ( is_array( $field['choices'] ) && array_filter( $field['choices'] ) ) {
										foreach ( $field['choices'] as $choice ) {
											$option = ur_sanitize_tooltip( trim( $choice ) );
											?>
											<label><input type="checkbox"
															name="<?php echo esc_attr( $key ); ?>[]"
															id="<?php echo esc_attr( $key ); ?>"
															value="<?php echo esc_attr( $option ); ?>"
															class="<?php echo esc_attr( $field['class'] ); ?>"
																			  <?php
																				if ( is_array( $value ) && in_array( $option, $value ) ) {
																					echo 'checked="checked"';
																				} elseif ( $value == $option ) {
																					echo 'checked="checked"';
																				}
																				?>
											 ><?php echo wp_kses_post( trim( $choice ) ); ?></label><br/>
											<?php
										}
									} else {
										?>
										<input type="checkbox" name="<?php echo esc_attr( $key ); ?>"
											   id="<?php echo esc_attr( $key ); ?>" value="1"
											   class="<?php echo esc_attr( $field['class'] ); ?>"
																 <?php
																	if ( '1' == $value ) {
																		echo 'checked="checked"';
																	}
																	?>
										 >
										<?php
									}
									?>
								<?php elseif ( ! empty( $field['type'] ) && 'button' === $field['type'] ) : ?>
									<button id="<?php echo esc_attr( $key ); ?>"
											class="button <?php echo esc_attr( $field['class'] ); ?>"><?php echo esc_html( $field['text'] ); ?></button>
								<?php elseif ( ! empty( $field['type'] ) && 'privacy_policy' === $field['type'] ) : ?>
								<input checked type="checkbox" disabled="disabled"/>
								<?php elseif ( ! empty( $field['type'] ) && 'textarea' === $field['type'] ) : ?>
									<textarea name="<?php echo esc_attr( $key ); ?>"
											  id="<?php echo esc_attr( $key ); ?>"

											  class="<?php echo( ! empty( $field['class'] ) ? esc_attr( $field['class'] ) : 'regular-text' ); ?>"
										<?php echo esc_attr( $attribute_string ); ?>
											  rows="5"
											  cols="30"><?php echo esc_attr( $this->get_user_meta( $user->ID, $key ) ); ?></textarea>

											  <?php elseif ( ! empty( $field['type'] ) && 'date' === $field['type'] ) : ?>
												  <?php
													$value        = $this->get_user_meta( $user->ID, $key );
													$actual_value = $value;
													$value        = str_replace( '/', '-', $value );
													if ( ! strpos( $value, 'to' ) ) {
														$value = '' !== $value ? date_i18n( $date_format, strtotime( $value ) ) : '';
													} else {
														$date_range = explode( 'to', $value );
														$value      = date_i18n( $date_format, strtotime( trim( $date_range[0] ) ) ) . ' to ' . date_i18n( $date_format, strtotime( trim( $date_range[1] ) ) );
													}
													?>
									<input type="text"
										   value="<?php echo esc_attr( $actual_value ); ?>"
										   class="ur-flatpickr-field regular-text"
										   data-id = '<?php echo esc_attr( $key ); ?>'
										   <?php echo $attribute_string; //phpcs:ignore?>
										   readonly />
										   <input type="hidden" id="formated_date" value="<?php echo esc_attr( $value ); ?>"/>
										   <input type="text" name="<?php echo esc_attr( $key ); ?>"
										   id="<?php echo esc_attr( $key ); ?>"
										   value="<?php echo esc_attr( $value ); ?>"
										   class="<?php echo( ! empty( $field['class'] ) ? esc_attr( $field['class'] ) : 'regular-text' ); ?>"
										   style="display:none"
												  <?php echo $attribute_string; //phpcs:ignore?>
											/>

												  <?php
								else :

									if ( ! empty( $field['type'] ) ) {
										$data = array(
											'key'   => $key,
											'value' => $this->get_user_meta( $user->ID, $key ),
											'attribute_string' => $attribute_string,
											'field' => $field,

										);
										do_action( 'user_registration_profile_field_' . $field['type'], $data );
									} else {
										$extra_params_key = str_replace( 'user_registration_', 'ur_', $key ) . '_params';
										$extra_params     = json_decode( get_user_meta( $user->ID, $extra_params_key, true ) );

										if ( empty( $extra_params ) ) {
											?>
											<input type="text" name="<?php echo esc_attr( $key ); ?>"
												   id="<?php echo esc_attr( $key ); ?>"
												   value="<?php echo esc_attr( $this->get_user_meta( $user->ID, $key ) ); ?>"
												   class="<?php echo( ! empty( $field['class'] ) ? esc_attr( $field['class'] ) : 'regular-text' ); ?>"
												<?php echo esc_attr( $attribute_string ); ?>
											/>

											<?php
										}
									} endif;
								?>
								<br/>
							</td>
						</tr>
							<?php
						}
					endforeach;
					?>
				</table>
				<?php
			endforeach;

			do_action( 'user_registration_after_user_extra_information', $user );
		}

		/**
		 * Save customer meta fields.
		 *
		 * @deprecated 1.4.1
		 * @param array $all_meta_value Meta value.
		 * @param int   $form_id Form id.
		 */
		public function save_customer_meta_fields( $all_meta_value, $form_id ) {
			ur_deprecated_function( 'UR_Admin_Profile::save_customer_meta_fields', '1.4.1', 'UR_Admin_Profile::update_user_profile' );
		}

		/**
		 * Save user extra fields on edit user pages.
		 *
		 * @param int $user_id User ID of the user being saved.
		 */
		public function update_user_profile( $user_id ) {

			$save_fields = $this->get_user_meta_by_form_fields( $user_id );

			if ( ! empty( $save_fields ) ) {

				$form_id = ur_get_form_id_by_userid( $user_id );

				$profile = user_registration_form_data( $user_id, $form_id );
				do_action( 'user_registration_after_admin_save_profile_validation', $user_id, $profile );

				foreach ( $save_fields as $fieldset ) {
					foreach ( $fieldset['fields'] as $key => $field ) {
						if ( isset( $field['type'] ) && ( 'checkbox' === $field['type'] || 'multi_select2' === $field['type'] || 'wysiwyg' === $field['type'] ) ) {
							if ( isset( $_POST[ $key ] ) ) {  //phpcs:ignore
								if ( is_array( $_POST[ $key ] ) ) { //phpcs:ignore
									$value = array_map( 'sanitize_text_field', $_POST[ $key ] ); //phpcs:ignore
								} else {
									$value = sanitize_text_field( $_POST[ $key ] ); //phpcs:ignore
								}
								update_user_meta( absint( $user_id ), sanitize_text_field( $key ), $value );
							} else {
								update_user_meta( absint( $user_id ), sanitize_text_field( $key ), '' );
							}
						} elseif ( isset( $_POST[ $key ] ) ) { //phpcs:ignore
							update_user_meta( absint( $user_id ), sanitize_text_field( $key ), sanitize_text_field( $_POST[ $key ] ) ); //phpcs:ignore
						}
					}
				}
			}
		}

		/**
		 * Get user meta for a given key, with fallbacks to core user info for pre-existing fields.
		 *
		 * @param int    $user_id User ID of the user being edited.
		 * @param string $key  key for user meta field.
		 *
		 * @return string
		 */
		protected function get_user_meta( $user_id, $key ) {
			$value           = get_user_meta( $user_id, $key, true );
			$existing_fields = array( 'billing_first_name', 'billing_last_name' );
			if ( ! $value && in_array( $key, $existing_fields ) ) {
				$value = get_user_meta( $user_id, str_replace( 'billing_', '', $key ), true );
			} elseif ( ! $value && ( 'billing_email' === $key ) ) {
				$user  = get_userdata( $user_id );
				$value = $user->user_email;
			}

			return $value;
		}

		/**
		 * Get user meta for a given key prefix, with fallbacks to core user info for pre-existing fields.
		 *
		 * @param int    $user_id User ID of the user being edited.
		 * @param string $key_prefix Prefix.
		 * @return array
		 */
		protected function get_user_meta_by_prefix( $user_id, $key_prefix ) {

			$values        = get_user_meta( $user_id );
			$return_values = array();

			if ( gettype( $values ) != 'array' ) {
				return $return_values;
			}

			foreach ( $values as $meta_key => $value ) {
				if ( substr( $meta_key, 0, strlen( $key_prefix ) ) == $key_prefix ) {
					if ( isset( $value[0] ) ) {
						$return_values[ $meta_key ] = $value[0];
					} elseif ( gettype( $values ) == 'string' ) {
						$return_values[ $meta_key ] = $value;
					}
				}
			}

			return $return_values;
		}

		/**
		 * Get User Meta fields.
		 *
		 * @deprecated 1.4.1
		 * @param array $all_meta_value Meta value.
		 * @param int   $form_id Form Id.
		 */
		public function get_user_meta_fields( $all_meta_value, $form_id ) {
			ur_deprecated_function( 'UR_Admin_Profile::get_user_meta_fields', '1.4.1', 'UR_Admin_Profile::get_form_fields' );
		}

		/**
		 * Get all the registration form fields.
		 *
		 * @param array $all_meta_value Meta value.
		 * @param int   $form_id Form Id.
		 */
		protected function get_form_fields( $all_meta_value, $form_id ) {
			$form_id            = ( $form_id ) ? $form_id : 0;
			$post_content_array = UR()->form->get_form(
				$form_id,
				array(
					'content_only' => true,
					'publish'      => true,
				)
			);

			$fields = array();
			if ( gettype( $post_content_array ) != 'array' ) {
				return $fields;
			}

			$all_meta_value_keys = array_keys( $all_meta_value );

			foreach ( $post_content_array as $post_content_row ) {
				foreach ( $post_content_row as $post_content_grid ) {
					foreach ( $post_content_grid as $field ) {
						$field_name        = isset( $field->general_setting->field_name ) ? $field->general_setting->field_name : '';
						$field_label       = isset( $field->general_setting->label ) ? $field->general_setting->label : '';
						$field_description = isset( $field->general_setting->description ) ? $field->general_setting->description : '';
						$field_key         = isset( $field->field_key ) ? $field->field_key : '';

						if ( 'stripe_gateway' === $field_name ) {
							break;
						}

						if ( '' == $field_label && isset( $field->general_setting->field_name ) ) {
							$field_label_array = explode( '_', $field->general_setting->field_name );
							$field_label       = join( ' ', array_map( 'ucwords', $field_label_array ) );
						}
						if ( '' != $field_name ) {
							$field_index = '';

							if ( in_array( 'user_registration_' . $field_name, $all_meta_value_keys ) ) {
								$field_index            = 'user_registration_' . $field_name;
								$fields[ $field_index ] = array(
									'label'       => $field_label,
									'description' => $field_description,
								);

							} elseif ( ! in_array( $field_name, ur_get_fields_without_prefix() ) ) {
								$field_index            = 'user_registration_' . $field_name;
								$fields[ $field_index ] = array(
									'label'       => $field_label,
									'description' => $field_description,
								);
							}
							switch ( $field_key ) {

								case 'select':
									// Backward compatibility. Modified since 1.5.7.
									$options     = isset( $field->advance_setting->options ) ? explode( ',', $field->advance_setting->options ) : array();
									$option_data = isset( $field->general_setting->options ) ? $field->general_setting->options : $options;
									$option_data = array_map( 'trim', $option_data );

									if ( is_array( $option_data ) && '' != $field_index ) {
										foreach ( $option_data as $index_data => $option ) {
											$fields[ $field_index ]['options'][ $option ] = $option;
										}
										$fields[ $field_index ]['type']  = 'select';
										$fields[ $field_index ]['class'] = '';
									}
									break;

								case 'radio':
									// Backward compatibility. Modified since 1.5.7.
									$options     = isset( $field->advance_setting->options ) ? explode( ',', $field->advance_setting->options ) : array();
									$option_data = isset( $field->general_setting->options ) ? $field->general_setting->options : $options;
									$option_data = array_map( 'trim', $option_data );

									if ( is_array( $option_data ) && '' != $field_index ) {
										foreach ( $option_data as $index_data => $option ) {
											$fields[ $field_index ]['options'][ $option ] = $option;
										}
										$fields[ $field_index ]['type']  = 'radio';
										$fields[ $field_index ]['class'] = '';
									}
									break;

								case 'country':
									$country                           = ur_load_form_field_class( $field_key );
									$fields[ $field_index ]['options'] = $country::get_instance()->get_country();
									$fields[ $field_index ]['type']    = 'country';
									$fields[ $field_index ]['class']   = '';
									break;

								case 'textarea':
									$fields[ $field_index ]['type']  = 'textarea';
									$fields[ $field_index ]['class'] = '';
									break;

								case 'mailchimp':
								case 'mailerlite':
								case 'checkbox':
									// Backward compatibility. Modified since 1.5.7.
									$options      = isset( $field->advance_setting->choices ) ? explode( ',', $field->advance_setting->choices ) : array();
									$choices_data = isset( $field->general_setting->options ) ? $field->general_setting->options : $options;
									$choices_data = array_map( 'trim', $choices_data );

									$fields[ $field_index ]['choices'] = $choices_data;
									$fields[ $field_index ]['type']    = 'checkbox';
									$fields[ $field_index ]['class']   = '';
									break;

								case 'date':
									$fields[ $field_index ]['type'] = 'date';
									$date_format                    = isset( $field->advance_setting->date_format ) ? $field->advance_setting->date_format : '';
									$fields[ $field_index ]['attributes']['data-date-format'] = $date_format;

									if ( isset( $field->advance_setting->enable_min_max ) && ur_string_to_bool( $field->advance_setting->enable_min_max ) ) {
										if ( ! empty( $field->advance_setting->min_date ) ) {
											$min_date = str_replace( '/', '-', $field->advance_setting->min_date );
											$fields[ $field_index ]['attributes']['data-min-date'] = '' !== $min_date ? date_i18n( $date_format, strtotime( $min_date ) ) : '';
										}

										if ( ! empty( $field->advance_setting->max_date ) ) {
											$max_date = str_replace( '/', '-', $field->advance_setting->max_date );
											$fields[ $field_index ]['attributes']['data-max-date'] = '' !== $max_date ? date_i18n( $date_format, strtotime( $max_date ) ) : '';
										}
									}

									if ( ! empty( $field->advance_setting->set_current_date ) ) {
										$set_current_date = isset( $field->advance_setting->set_current_date ) ? ur_string_to_bool( $field->advance_setting->set_current_date ) : '';
										$fields[ $field_index ]['attributes']['data-default-date'] = $set_current_date;
									}

									if ( ! empty( $field->advance_setting->enable_date_range ) ) {
										$enable_date_range                                 = isset( $field->advance_setting->enable_date_range ) ? ur_string_to_bool( $field->advance_setting->enable_date_range ) : '';
										$fields[ $field_index ]['attributes']['data-mode'] = $enable_date_range;
									}

									if ( ! empty( $field->advance_setting->date_localization ) ) {
										$date_localization                                   = isset( $field->advance_setting->date_localization ) ? $field->advance_setting->date_localization : 'en';
										$fields[ $field_index ]['attributes']['data-locale'] = $date_localization;
									}
									break;

								case 'privacy_policy':
									$fields[ $field_index ]['type'] = 'privacy_policy';
									break;

								case 'multiple_choice':
									$fields[ $field_index ]['type'] = 'multiple_choice';
									break;

								case 'single_item':
									$fields[ $field_index ]['type'] = 'single_item';
									break;

								case 'total_field':
									$fields[ $field_index ]['type'] = 'total_field';
									break;
							}
						}// End switch().
						$filter_data         = array(
							'fields'     => $fields,
							'field'      => $field,
							'field_name' => $field_name,
						);
						$filtered_data_array = apply_filters( 'user_registration_profile_field_filter_' . $field_key, $filter_data );
						if ( isset( $filtered_data_array['fields'] ) ) {
							$fields = $filtered_data_array['fields'];
						}
					}// End foreach().
				}// End foreach().
			}// End foreach().
			return $fields;
		}
	}
endif;

return new UR_Admin_Profile();
