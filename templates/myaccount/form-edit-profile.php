<?php
/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/form-edit-profile.php.
 *
 * HOWEVER, on occasion UserRegistration will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.wpeverest.com/user-registration/template-structure/
 * @package UserRegistration/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'user_registration_before_edit_profile_form' ); ?>

<div class="ur-frontend-form login ur-edit-profile" id="ur-frontend-form">
	<form class="user-registration-EditProfileForm edit-profile" action="" method="post" enctype="multipart/form-data">
		<div class="ur-form-row">
			<div class="ur-form-grid">
				<div class="user-registration-profile-fields">
					<h2><?php esc_html_e( apply_filters( 'user_registation_profile_detail_title', __( 'Profile Detail', 'user-registration' ) ) ); //PHPCS:ignore ?></h2>
					<?php
					if ( 'no' === get_option( 'user_registration_disable_profile_picture', 'no' ) ) {
						?>
						<div class="user-registration-profile-header">
							<div class="user-registration-img-container" style="width:100%">
								<?php
								$gravatar_image      = get_avatar_url( get_current_user_id(), $args = null );
								$profile_picture_url = get_user_meta( get_current_user_id(), 'user_registration_profile_pic_url', true );
								$image               = ( ! empty( $profile_picture_url ) ) ? $profile_picture_url : $gravatar_image;
								$max_size = wp_max_upload_size();
								$max_upload_size = $max_size;

								foreach ( $form_data_array as $data ) {
									foreach ( $data as $grid_key => $grid_data ) {
										foreach ( $grid_data as $grid_data_key => $single_item ) {
											$edit_profile_valid_file_type = 'image/jpeg,image/jpg,image/gif,image/png';

											if ( 'profile_picture' === $single_item->field_key ) {
												if ( ! empty( $single_item->advance_setting->valid_file_type ) ) {
													$edit_profile_valid_file_type = implode( ', ', $single_item->advance_setting->valid_file_type );
												}
												$max_upload_size = isset( $single_item->advance_setting->max_upload_size ) && '' !== $single_item->advance_setting->max_upload_size ? $single_item->advance_setting->max_upload_size : $max_size;
											}
										}
									}
								}

								?>
									<img class="profile-preview" alt="profile-picture" src="<?php echo esc_url( $image ); ?>" style='max-width:96px; max-height:96px;' >

									<p class="user-registration-tips"><?php echo esc_html__( 'Max size: ', 'user-registration' ) . esc_attr( size_format( $max_upload_size ) ); ?></p>
								</div>
								<header>
									<p><strong><?php echo esc_html( apply_filters( 'user_registration_upload_new_profile_image_message', esc_html__( 'Upload your new profile image.', 'user-registration' ) ) ); ?></strong></p>
									<div class="button-group">
										<?php

										if ( has_action( 'uraf_profile_picture_buttons' ) ) {
											?>
											<div class="uraf-profile-picture-upload">
												<p class="form-row " id="profile_pic_url_field" data-priority="">
													<span class="uraf-profile-picture-upload-node" style="height: 0;width: 0;margin: 0;padding: 0;float: left;border: 0;overflow: hidden;">
													<input type="file" id="ur-profile-pic" name="profile-pic" class="profile-pic-upload" size="<?php echo esc_attr( $max_upload_size ); ?>" accept="<?php echo esc_attr( $edit_profile_valid_file_type ); ?>" style="<?php echo esc_attr( ( $gravatar_image !== $image ) ? 'display:none;' : '' ); ?>" />
													<?php echo '<input type="text" class="uraf-profile-picture-input input-text ur-frontend-field" name="profile_pic_url" id="profile_pic_url" value="' . esc_url( $profile_picture_url ) . '" />'; ?>
													</span>
													<?php do_action( 'uraf_profile_picture_buttons' ); ?>
												</p>
												<div style="clear:both; margin-bottom: 20px"></div>
											</div>

											<?php
										} else {
											?>
											<input type="hidden" name="profile-pic-url" id="profile_pic_url" value="<?php echo esc_attr( $profile_picture_url ); ?>" />
											<input type="hidden" name="profile-default-image" value="<?php echo esc_url( $gravatar_image ); ?>" />
											<button class="button profile-pic-remove" style="<?php echo esc_attr( ( $gravatar_image === $image ) ? 'display:none;' : '' ); ?>"><?php echo esc_html__( 'Remove', 'user-registration' ); ?></php></button>
											<?php
											if ( 'yes' === get_option( 'user_registration_ajax_form_submission_on_edit_profile', 'no' ) ) {
												?>
												<button type="button" class="button user_registration_profile_picture_upload hide-if-no-js" style="<?php echo esc_attr( ( $gravatar_image !== $image ) ? 'display:none;' : '' ); ?>" ><?php echo esc_html__( 'Upload Picture', 'user-registration' ); ?></button>
												<input type="file" id="ur-profile-pic" name="profile-pic" class="profile-pic-upload" accept="image/jpeg,image/jpg,image/gif,image/png" style="display:none" />
												<?php
											} else {
												?>
												<input type="file" id="ur-profile-pic" name="profile-pic" class="profile-pic-upload" accept="image/jpeg,image/jpg,image/gif,image/png" style="<?php echo esc_attr( ( $gravatar_image !== $image ) ? 'display:none;' : '' ); ?>" />
												<?php
											}
										}
										?>

									</div>
									<?php
									if ( ! $profile_picture_url ) {
										?>
										<span><i><?php echo esc_html__( 'You can change your profile picture on', 'user-registration' ); ?> <a href="https://en.gravatar.com/"><?php esc_html_e( 'Gravatar', 'user-registration' ); ?></a></i></span>
									<?php } ?>
								</header>
							</div>
					<?php } ?>
					<?php do_action( 'user_registration_edit_profile_form_start' ); ?>
					<div class="user-registration-profile-fields__field-wrapper">

						<?php foreach ( $form_data_array as $data ) { ?>
							<div class='ur-form-row'>
								<?php
								$width = floor( 100 / count( $data ) ) - count( $data );

								foreach ( $data as $grid_key => $grid_data ) {
									$found_field = false;

									foreach ( $grid_data as $grid_data_key => $single_item ) {
										$key = 'user_registration_' . $single_item->general_setting->field_name;
										if ( isset( $single_item->field_key ) && isset( $profile[ $key ] ) ) {
											$found_field = true;
										}
									}
									if ( $found_field ) {
										?>
										<div class="ur-form-grid ur-grid-<?php echo esc_attr( ( $grid_key + 1 ) ); ?>" style="width:<?php echo esc_attr( $width ); ?>%;">
										<?php
									}

									foreach ( $grid_data as $grid_data_key => $single_item ) {

										$key = 'user_registration_' . $single_item->general_setting->field_name;
										if ( isset( $profile[ $key ] ) ) {


											$user_id                    = get_current_user_id();
											$form_id                    = ur_get_form_id_by_userid( $user_id );
											$field                      = $profile[ $key ];
											$field['input_class']       = array( 'ur-edit-profile-field ' );
											$advance_data               = array(
												'general_setting' => (object) $single_item->general_setting,
												'advance_setting' => (object) $single_item->advance_setting,
											);
											$field['custom_attributes'] = isset( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ? $field['custom_attributes'] : array();
											$field_id                   = $single_item->general_setting->field_name;
											$cl_props                   = null;

											// If the conditional logic addon is installed.
											if ( class_exists( 'UserRegistrationConditionalLogic' ) ) {
												// Migrate the conditional logic to logic_map schema.
												$single_item = class_exists( 'URCL_Field_Settings' ) ? URCL_Field_Settings::migrate_to_logic_map_schema( $single_item ) : $single_item;

												$cl_enabled = isset( $single_item->advance_setting->enable_conditional_logic ) && ( '1' === $single_item->advance_setting->enable_conditional_logic || 'on' === $single_item->advance_setting->enable_conditional_logic ) ? 'yes' : 'no';
												$cl_props   = sprintf( 'data-conditional-logic-enabled="%s"', esc_attr( $cl_enabled ) );

												if ( 'yes' === $cl_enabled && isset( $single_item->advance_setting->cl_map ) ) {
													$cl_map   = esc_attr( $single_item->advance_setting->cl_map );
													$cl_props = sprintf( 'data-conditional-logic-enabled="%s" data-conditional-logic-map="%s"', esc_attr( $cl_enabled ), esc_attr( $cl_map ) );
												}
											}

											if ( 'profile_picture' === $single_item->field_key ) {
												continue;
											}

											// unset invite code.
											if ( 'invite_code' === $single_item->field_key ) {
												continue;
											}
											// unset learndash code.
											if ( 'learndash_course' === $single_item->field_key ) {
												continue;
											}

											// Unset multiple choice and single item.
											if ( 'multiple_choice' === $single_item->field_key || 'single_item' === $single_item->field_key ) {
												continue;
											}

											?>
											<div class="ur-field-item field-<?php echo esc_attr( $single_item->field_key ); ?>"  <?php echo $cl_props; //PHPCS:ignore?> data-field-id="<?php echo esc_attr( $field_id ); ?>">
												<?php
												$readonly_fields = ur_readonly_profile_details_fields();

												if ( array_key_exists( $field['field_key'], $readonly_fields ) ) {
													$field['custom_attributes']['readonly'] = 'readonly';
													if ( isset( $readonly_fields[ $field['field_key'] ] ['value'] ) ) {
														$field['value'] = $readonly_fields[ $field['field_key'] ] ['value'];
													}
													if ( isset( $readonly_fields[ $field['field_key'] ] ['message'] ) ) {
														$field['custom_attributes']['title'] = $readonly_fields[ $field['field_key'] ] ['message'];
														$field['input_class'][]              = 'user-registration-help-tip';
													}
												}

												if ( 'number' === $single_item->field_key ) {
													$field['min']  = isset( $advance_data['advance_setting']->min ) ? $advance_data['advance_setting']->min : '';
													$field['max']  = isset( $advance_data['advance_setting']->max ) ? $advance_data['advance_setting']->max : '';
													$field['step'] = isset( $advance_data['advance_setting']->step ) ? $advance_data['advance_setting']->step : '';
												}

												if ( 'text' === $single_item->field_key ) {
													$field['size']  = isset( $advance_data['advance_setting']->size ) ? $advance_data['advance_setting']->size : '';
												}

												if ( 'range' === $single_item->field_key ) {
													$field['range_min'] = ( isset( $advance_data['advance_setting']->range_min ) && '' !== $advance_data['advance_setting']->range_min ) ? $advance_data['advance_setting']->range_min : '0';
													$field['range_max'] = ( isset( $advance_data['advance_setting']->range_max ) && '' !== $advance_data['advance_setting']->range_max ) ? $advance_data['advance_setting']->range_max : '10';
													$field['range_step'] = isset( $advance_data['advance_setting']->range_step ) ? $advance_data['advance_setting']->range_step : '1';
													$field['enable_payment_slider'] = isset( $advance_data['advance_setting']->enable_payment_slider ) ? $advance_data['advance_setting']->enable_payment_slider : 'false';

													if ( 'true' === $advance_data['advance_setting']->enable_prefix_postfix ) {
														if ( 'true' === $advance_data['advance_setting']->enable_text_prefix_postfix ) {
															$field['range_prefix'] = isset( $advance_data['advance_setting']->range_prefix ) ? $advance_data['advance_setting']->range_prefix : '';
															$field['range_postfix'] = isset( $advance_data['advance_setting']->range_postfix ) ? $advance_data['advance_setting']->range_postfix : '';
														} else {
															$field['range_prefix'] = $field['range_min'];
															$field['range_postfix'] = $field['range_max'];
														}
													}

													// to hide the range as payment slider in edit profile.
													if ( 'true' === $field['enable_payment_slider'] ) {
														continue;
													}
												}

												if ( 'phone' === $single_item->field_key ) {
													$field['phone_format'] = $single_item->general_setting->phone_format;
													if ( 'smart' === $field['phone_format'] ) {
														unset( $field['input_mask'] );
													}
												}

												if ( isset( $single_item->general_setting->hide_label ) ) {
													if ( 'yes' === $single_item->general_setting->hide_label ) {
														unset( $field['label'] );
													}
												}

												if ( 'select' === $single_item->field_key ) {
													$option_data         = isset( $advance_data['advance_setting']->options ) ? explode( ',', $advance_data['advance_setting']->options ) : array();
													$option_advance_data = isset( $advance_data['general_setting']->options ) ? $advance_data['general_setting']->options : $option_data;
													$options             = array();

													if ( is_array( $option_advance_data ) ) {
														foreach ( $option_advance_data as $index_data => $option ) {
															$options[ $option ] = ur_string_translation( $form_id, 'user_registration_' . $advance_data['general_setting']->field_name . '_option_' . ( ++$index_data ), $option );
														}
														$field['options'] = $options;
													}

													$field['placeholder'] = $single_item->general_setting->placeholder;

													if ( isset( $field['placeholder'] ) ) {
														unset( $field['placeholder'] );
													}
												}

												if ( 'radio' === $single_item->field_key ) {
													$option_data         = isset( $advance_data['advance_setting']->options ) ? explode( ',', $advance_data['advance_setting']->options ) : array();
													$option_advance_data = isset( $advance_data['general_setting']->options ) ? $advance_data['general_setting']->options : $option_data;
													$options             = array();

													if ( is_array( $option_advance_data ) ) {
														foreach ( $option_advance_data as $index_data => $option ) {
															$options[ $option ] = ur_string_translation( $form_id, 'user_registration_' . $advance_data['general_setting']->field_name . '_option_' . ( ++$index_data ), $option );
														}
														$field['options'] = $options;
													}
												}

												if ( 'file' === $single_item->field_key ) {
													if ( isset( $single_item->general_setting->max_files ) ) {
														$field['max_files'] = $single_item->general_setting->max_files;
													} else {
														$field['max_files'] = 1;
													}

													if ( isset( $advance_data['advance_setting']->max_upload_size ) ) {
														$field['max_upload_size'] = $advance_data['advance_setting']->max_upload_size;
													}

													if ( isset( $advance_data['advance_setting']->valid_file_type ) ) {
														$field['valid_file_type'] = $advance_data['advance_setting']->valid_file_type;
													}
												}

												if ( isset( $advance_data['general_setting']->required ) ) {
													if ( in_array( $single_item->field_key, ur_get_required_fields() )
													|| 'yes' === $advance_data['general_setting']->required ) {
														$field['required']                      = true;
														$field['custom_attributes']['required'] = 'required';
													}
												}

												// Add choice_limit setting valur in order to limit choice fields.
												if ( 'checkbox' === $single_item->field_key || 'multi_select2' === $single_item->field_key ) {
													$choices     = isset( $advance_data['advance_setting']->choices ) ? explode( ',', $advance_data['advance_setting']->choices ) : array();
													$option_data = isset( $advance_data['general_setting']->options ) ? $advance_data['general_setting']->options : $choices;
													$options     = array();

													if ( is_array( $option_data ) ) {
														foreach ( $option_data as $index_data => $option ) {
															$options[ $option ] = ur_string_translation( $form_id, 'user_registration_' . $advance_data['general_setting']->field_name . '_option_' . ( ++$index_data ), $option );
														}

														$field['options'] = $options;
													}

													if ( isset( $advance_data['advance_setting']->choice_limit ) ) {
														$field['choice_limit'] = $advance_data['advance_setting']->choice_limit;
													}
													if ( isset( $advance_data['advance_setting']->select_all ) ) {
														$field['select_all'] = $advance_data['advance_setting']->select_all;
													}
												}

												if ( 'timepicker' === $single_item->field_key ) {
													$field['current_time'] = isset( $advance_data['advance_setting']->current_time ) ? $advance_data['advance_setting']->current_time : '';
													$field['time_interval'] = isset( $advance_data['advance_setting']->time_interval ) ? $advance_data['advance_setting']->time_interval : '';
													$field['time_min']      = ( isset( $advance_data['advance_setting']->time_min ) && '' !== $advance_data['advance_setting']->time_min ) ? $advance_data['advance_setting']->time_min : '';
													$field['time_max']      = ( isset( $advance_data['advance_setting']->time_max ) && '' !== $advance_data['advance_setting']->time_max ) ? $advance_data['advance_setting']->time_max : '';
													$timemin                = isset( $field['time_min'] ) ? strtolower( substr( $field['time_min'], -2 ) ) : '';
													$timemax                = isset( $field['time_max'] ) ? strtolower( substr( $field['time_max'], -2 ) ) : '';
													$minampm                = intval( $field['time_min'] ) <= 12 ? 'AM' : 'PM';
													$maxampm                = intval( $field['time_max'] ) <= 12 ? 'AM' : 'PM';

														// Handles the time format.
													if ( 'am' === $timemin || 'pm' === $timemin ) {
														$field['time_min'] = $field['time_min'];
													} else {
														$field['time_min'] = $field['time_min'] . '' . $minampm;
													}

													if ( 'am' === $timemax || 'pm' === $timemax ) {
														$field['time_max'] = $field['time_max'];
													} else {
														$field['time_max'] = $field['time_max'] . '' . $maxampm;
													}
												}

												$filter_data = array(
													'form_data' => $field,
													'data' => $advance_data,
												);

												$form_data_array = apply_filters( 'user_registration_' . $field['field_key'] . '_frontend_form_data', $filter_data );
												$field           = isset( $form_data_array['form_data'] ) ? $form_data_array['form_data'] : $field;
												$value           = ! empty( $_POST[ $key ] ) ? ur_clean( $_POST[ $key ] ) : $field['value']; //PHPCS:ignore

												user_registration_form_field( $key, $field, $value );

												/**
												 * Embed the current country value to allow to remove it if it's not allowed.
												 */
												if ( 'country' === $single_item->field_key && ! empty( $value ) ) {
													echo sprintf( '<span hidden class="ur-data-holder" data-option-value="%s" data-option-html="%s"></span>', esc_attr( $value ), esc_attr( UR_Form_Field_Country::get_instance()->get_country()[ $value ] ) );
												}
												?>
											</div>
										<?php } ?>
									<?php } ?>

									<?php if ( $found_field ) { ?>
										</div>
									<?php } ?>
								<?php } ?>
							</div>
						<?php } ?>

					</div>
					<?php
					do_action( 'user_registration_edit_profile_form' );
					$submit_btn_class = apply_filters( 'user_registration_form_update_btn_class', array() );
					?>
					<p>
						<?php
						if ( 'yes' === get_option( 'user_registration_ajax_form_submission_on_edit_profile', 'no' ) ) {
							?>
							<button type="submit" class="user-registration-submit-Button btn button <?php echo esc_attr( implode( ' ', $submit_btn_class ) ); ?>" name="save_account_details" ><span></span><?php esc_html_e( apply_filters( 'user_registration_profile_update_button', __( 'Save changes', 'user-registration' ) ) ); //PHPCS:ignore?></button>
							<?php
						} else {
							wp_nonce_field( 'save_profile_details' );
							?>
							<input type="submit" class="user-registration-Button button <?php echo esc_attr( implode( ' ', $submit_btn_class ) ); ?>" name="save_account_details" value="<?php esc_attr_e( apply_filters( 'user_registration_profile_update_button', __( 'Save changes', 'user-registration' ) ) );//PHPCS:ignore ?>" />
							<input type="hidden" name="action" value="save_profile_details" />
							<?php
						}
						?>
					</p>
				</div>
			</div>

		</div>
	</form>
</div>

<?php do_action( 'user_registration_after_edit_profile_form' ); ?>
