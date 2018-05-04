<?php
/**
 * Add extra profile fields for users in admin
 *
 * @author   WPEverest
 * @category Admin
 * @package  UserRegistration/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'UR_Admin_Profile', false ) ) :

	/**
	 * UR_Admin_Profile Class.
	 */
	class UR_Admin_Profile {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			add_action( 'show_user_profile', array( $this, 'add_customer_meta_fields' ) );
			add_action( 'edit_user_profile', array( $this, 'add_customer_meta_fields' ) );

			add_action( 'personal_options_update', array( $this, 'save_customer_meta_fields' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_customer_meta_fields' ) );
		}

		/**
		 * Get Address Fields for the edit user pages.
		 *
		 * @return array Fields to display which are filtered through user_registration_profile_meta_fields before being returned
		 */
		public function get_customer_meta_fields( $user_id ) {
			$show_fields = array();
			$form_id     = $this->get_user_meta( $user_id, 'ur_form_id' );

			$all_meta_for_user = $this->get_user_meta_by_prefix( $user_id, 'user_registration_' );

			$extra_meta_fields = $this->get_user_meta_fields( $all_meta_for_user, $form_id );
			
			if ( ! empty( $extra_meta_fields ) ) {
				$show_fields = apply_filters( 'user_registration_profile_meta_fields', array(
					'user_registration' => array(
						'title'  => sprintf( __( 'User Extra Information %s', 'user-registration' ), '' ),
						'fields' => $extra_meta_fields,
					),
				) );
			}
			return $show_fields;
		}

		/**
		 * Show Address Fields on edit user pages.
		 *
		 * @param WP_User $user
		 */
		public function add_customer_meta_fields( $user ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$show_fields = $this->get_customer_meta_fields( $user->ID );

			foreach ( $show_fields as $fieldset_key => $fieldset ) :

				?>
				<h2><?php echo $fieldset['title']; ?></h2>
				<table class="form-table" id="<?php echo esc_attr( 'fieldset-' . $fieldset_key ); ?>">
					<?php
					$profile_field_type       = array(
						'select',
						'country',
						'checkbox',
						'button',
						'textarea',
						'radio'
					);
					foreach ( $fieldset['fields'] as $key => $field ) :
						$field['label'] = isset( $field['label'] ) ? $field['label'] : '';
						$field['description'] = isset( $field['description'] ) ? $field['description'] : '';
						$attributes           = isset( $field['attributes'] ) ? $field['attributes'] : array();

						$attribute_string = '';
						foreach ( $attributes as $name => $value ) {
							if ( is_bool( $value ) ) {
								if ( $value ) {
									$attribute_string .= $name . ' ';
								}
							} else {
								$attribute_string .= sprintf( '%s="%s" ', $name, $value );
							}
						}

						$field_label = $field['label'];

						$field_type = isset( $field['type'] ) ? $field['type'] : '';

						if ( ! in_array( $field_type, $profile_field_type ) ) {
							$extra_params_key = str_replace( 'user_registration_', 'ur_', $key ) . '_params';
							$extra_params     = json_decode( get_user_meta( $user->ID, $extra_params_key, true ) );
							$field_label      = isset( $extra_params->label ) ? $extra_params->label : $field_label;
						}
						?>

						<tr>
							<th>
								<label
									for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field_label ); ?></label>
							</th>
							<td>	
								<?php if ( ! empty( $field['type'] ) && 'select' === $field['type'] ) : ?>
									<select name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>"
									        class="<?php echo esc_attr( $field['class'] ); ?>" style="width: 25em;">
										<option><?php echo __( 'Select', 'user-registration' ); ?></option>
										<?php
										$selected = esc_attr( get_user_meta( $user->ID, $key, true ) );
										foreach ( $field['options'] as $option_key => $option_value ) : ?>						
											<option value="<?php echo esc_attr( trim ( $option_key ) ); ?>" <?php selected( $selected, trim( $option_key ), true ); ?>><?php echo esc_attr( trim ( $option_value ) ); ?></option>
										<?php endforeach; ?>
									</select>
								
								<?php elseif ( ! empty( $field['type'] ) && 'country' === $field['type'] ) : ?>
									<select name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>"
									        class="<?php echo esc_attr( $field['class'] ); ?>" style="width: 25em;">
										<option><?php echo __( 'Select', 'user-registration' ); ?></option>
										<?php
										$selected = esc_attr( get_user_meta( $user->ID, $key, true ) );
										foreach ( $field['options'] as $option_key => $option_value ) : ?>
											<option
												value="<?php echo esc_attr( trim ( $option_key ) ); ?>" <?php selected( $selected, $option_key, true ); ?>><?php echo esc_attr( trim ( $option_value ) ); ?></option>
										<?php endforeach; ?>
									</select>

								<?php elseif ( ! empty( $field['type'] ) && 'radio' === $field['type'] ) : ?>
									<?php 
									$db_value = get_user_meta( $user->ID, $key, true );
									if( is_array( $field['options'] ) ) {
										foreach( $field['options'] as $option_key => $option_value ) {
											?>
											<label><input type="radio"
											                name="<?php echo esc_attr( $key ); ?>"
											                id="<?php echo esc_attr( $key ); ?>"
											                value="<?php echo esc_attr( trim ( $option_key ) ); ?>"
											                class="<?php echo esc_attr( $field['class'] ); ?>" <?php checked( $db_value, trim( $option_value ), true ); ?>  ><?php echo trim ( $option_value ); ?>
											</label><br/>
											<?php
										}
									}
									?>

								<?php elseif ( ! empty( $field['type'] ) && 'checkbox' === $field['type'] ) : ?>
									<?php

									$value = get_user_meta( $user->ID, $key, true );								

									if ( count( $field['choices'] ) > 1 && is_array( $field['choices'] ) ) {
										foreach ( $field['choices'] as $choice ) {
											?><label><input type="checkbox"
											                name="<?php echo esc_attr( $key ); ?>[]"
											                id="<?php echo esc_attr( $key ); ?>"
											                value="<?php echo esc_attr( trim( $choice ) ); ?>"
											                class="<?php echo esc_attr( $field['class'] ); ?>" <?php if (is_array( $value ) && in_array( trim( $choice ), $value ) ) {
												echo 'checked="checked"';
											} ?> ><?php echo trim( $choice ); ?></label><br/>
											<?php
										}
									} else {
										?>
										<input type="checkbox" name="<?php echo esc_attr( $key ); ?>"
										       id="<?php echo esc_attr( $key ); ?>" value="1"
										       class="<?php echo esc_attr( $field['class'] ); ?>" <?php if ( $value == '1' ) {
											echo 'checked="checked"';
										} ?> >
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

								<?php else  :							

									if ( ! empty( $field['type'] ) ) {
										$data = array(
											'key'              => $key,
											'value'            => $this->get_user_meta( $user->ID, $key ),
											'attribute_string' => $attribute_string,
											'field'            => $field

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

										<?php } 

									} endif; ?>
								<br/>
								<span class="description"><?php echo wp_kses_post( $field['description'] ); ?></span>
							</td>
						</tr>
						<?php
					endforeach;
					?>
				</table>
				<?php
			endforeach;
		}


		/**
		 * Save Address Fields on edit user pages.
		 *
		 * @param int $user_id User ID of the user being saved
		 */
		public function save_customer_meta_fields( $user_id ) {

			$save_fields = $this->get_customer_meta_fields( $user_id );

			foreach ( $save_fields as $fieldset ) {
				foreach ( $fieldset['fields'] as $key => $field ) {

					if ( isset( $field['type'] ) && 'checkbox' === $field['type'] ) {
						if ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) {
							update_user_meta( $user_id, $key, $_POST[ $key ] );
						} 
					} elseif ( isset( $_POST[ $key ] ) ) {
						update_user_meta( $user_id, $key, sanitize_text_field( $_POST[ $key ] ) );
					}
				}
			}
		}

		/**
		 * Get user meta for a given key, with fallbacks to core user info for pre-existing fields.
		 *
		 * @since 3.1.0
		 *
		 * @param int    $user_id User ID of the user being edited
		 * @param string $key     Key for user meta field
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
		 * Get user meta for a given key, with fallbacks to core user info for pre-existing fields.
		 *
		 * @since 3.1.0
		 *
		 * @param int    $user_id User ID of the user being edited
		 * @param string $key     Key for user meta field
		 *
		 * @return array
		 */
		protected function get_user_meta_by_prefix( $user_id, $key_prefix ) {

			$values = get_user_meta( $user_id );

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

		protected function get_user_meta_fields( $all_meta_value, $form_id ) {

			$post_id   = $form_id;
			$args      = array(
				'post_type'   => 'user_registration',
				'post_status' => 'publish',
				'post__in'    => array( $post_id ),
			);
			$post_data = get_posts( $args );

			$post_content = isset( $post_data[0]->post_content ) ? $post_data[0]->post_content : '';

			$fields = array();

			$post_content_array = json_decode( $post_content );

			if ( gettype( $post_content_array ) != 'array' ) {

				return $fields;
			}
			$all_meta_value_keys = array_keys( $all_meta_value );

			foreach ( $post_content_array as $post_content_row ) {

				foreach ( $post_content_row as $post_content_grid ) {

					foreach ( $post_content_grid as $field ) {

						$field_name = isset( $field->general_setting->field_name ) ? $field->general_setting->field_name : '';

						$field_label = isset( $field->general_setting->label ) ? $field->general_setting->label : '';

						$field_key = isset( $field->field_key ) ? $field->field_key : '';

						if ( $field_label == '' && isset( $field->general_setting->field_name ) ) {

							$field_label_array = explode( '_', $field->general_setting->field_name );

							$field_label = join( ' ', array_map( 'ucwords', $field_label_array ) );

						}

						if ( $field_name != '' ) {

							$field_index = '';
							$woocommerce_fields = function_exists( 'ur_get_all_woocommerce_fields' ) ? ur_get_all_woocommerce_fields() : array();
							if ( in_array( 'user_registration_' . $field_name, $all_meta_value_keys ) ) {

								$field_index            = 'user_registration_' . $field_name;
								$fields[ $field_index ] = array(
									'label'       => __( $field_label, 'user-registration' ),
									'description' => '',


								);

							} 
							elseif ( ! in_array( $field_name, ur_get_user_field_only() ) && ! in_array( $field_name, $woocommerce_fields ) ) {

								$field_index           = $field_name;
								$fields[ $field_name ] = array(
									'label'       => __( $field_label, 'user-registration' ),
									'description' => '',
								);
							}
							switch ( $field_key ) {

								case 'select':

									$option_data = isset( $field->advance_setting->options ) ? explode( ',', $field->advance_setting->options ) : array();

									if ( is_array( $option_data ) && $field_index != '' ) {

										foreach ( $option_data as $index_data => $option ) {

											$fields[ $field_index ]['options'][ $option ] = $option;
										}
										$fields[ $field_index ]['type']  = 'select';
										$fields[ $field_index ]['class'] = '';
									}
									break;
								case 'radio':

									$option_data = isset( $field->advance_setting->options ) ? explode( ',', $field->advance_setting->options ) : array();

									if ( is_array( $option_data ) && $field_index != '' ) {

										foreach ( $option_data as $index_data => $option ) {

											$fields[ $field_index ]['options'][ $option ] = $option;
										}
										$fields[ $field_index ]['type']  = 'radio';

										$fields[ $field_index ]['class'] = '';
									}
									break;

								case 'country':

									$country = ur_load_form_field_class( $field_key );

									$fields[ $field_index ]['options'] = $country::get_instance()->get_country();

									$fields[ $field_index ]['type'] = 'country';

									$fields[ $field_index ]['class'] = '';

									break;
								case 'textarea':

									$fields[ $field_index ]['type'] = 'textarea';

									$fields[ $field_index ]['class'] = '';

									break;
								case 'mailchimp':
								case 'checkbox':

									$choices_data = isset( $field->advance_setting->choices ) ? ( $field->advance_setting->choices ) : '';

									$choices_data = explode( ",", $choices_data );

									$fields[ $field_index ]['choices'] = $choices_data;

									$fields[ $field_index ]['type']  = 'checkbox';
									$fields[ $field_index ]['class'] = '';

									break;
								case 'privacy_policy':
									$fields[ $field_index ]['type']  = 'privacy_policy';
									break;
							}
						}// End switch().
						$filter_data         = array(
							'fields'     => $fields,
							'field'      => $field,
							'field_name' => $field_name
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
