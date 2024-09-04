<?php
/**
 * UserRegistration Admin Settings Class
 *
 * @class    UR_Admin_Settings
 * @version  1.0.0
 * @package  UserRegistration/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Admin_Settings Class.
 */
class UR_Admin_Settings {

	/**
	 * Setting pages.
	 *
	 * @var array
	 */
	private static $settings = array();

	/**
	 * Error messages.
	 *
	 * @var array
	 */
	private static $errors = array();

	/**
	 * Update messages.
	 *
	 * @var array
	 */
	private static $messages = array();

	/**
	 * Include the settings page classes.
	 */
	public static function get_settings_pages() {

		if ( empty( self::$settings ) ) {
			$settings = array();

			include_once __DIR__ . '/settings/class-ur-settings-page.php';

			if ( ! empty( $_GET['install_user_registration_pages'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
				UR_Install::create_pages();
				UR_Admin_Notices::remove_notice( 'install' );
			}

			$settings[] = include 'settings/class-ur-settings-general.php';
			$settings[] = include 'settings/class-ur-settings-captcha.php';
			$settings[] = include 'settings/class-ur-settings-email.php';
			$settings[] = include 'settings/class-ur-settings-import-export.php';
			$settings[] = include 'settings/class-ur-settings-misc.php';
			$settings[] = include 'settings/class-ur-settings-integration.php';

			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( is_plugin_active( 'user-registration-pro/user-registration.php' ) ) {
				$settings[] = include 'settings/class-ur-settings-license.php';
			}

			/**
			 * Filter to retrieve settings pages
			 *
			 * @param array $settings Settings.
			 */
			self::$settings = apply_filters( 'user_registration_get_settings_pages', $settings );
		}

		return self::$settings;
	}

	/**
	 * Save the settings.
	 */
	public static function save() {
		global $current_tab;

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'user-registration-settings' ) ) {
			die( esc_html__( 'Action failed. Please refresh the page and retry.', 'user-registration' ) );
		}
		/**
		 * Action to save current tab settings
		 */
		do_action( 'user_registration_settings_save_' . $current_tab );
		/**
		 * Action to save current tab options
		 */
		do_action( 'user_registration_update_options_' . $current_tab );
		/**
		 * Action to save options
		 */
		do_action( 'user_registration_update_options' );

		/**
		 * Filter to modify display of setting message
		 *
		 * @param boolean Show/Hide.
		 */
		$flag = apply_filters( 'show_user_registration_setting_message', true );

		$flag = apply_filters( 'user_registration_settings_prevent_default_login', $_REQUEST );

		if ( $flag && is_bool( $flag ) ) {
			self::add_message( esc_html__( 'Your settings have been saved.', 'user-registration' ) );
		} elseif ( $flag && 'redirect_login_error' === $flag ) {

			self::add_error(
				esc_html__(
					'Your settings has not been saved. You enabled "Disable Default WordPress Login Screen" but did not select a login page. Please select a page for "Redirect Default WordPress Login To".',
					'user-registration'
				)
			);

		} elseif ( $flag && 'redirect_login_not_myaccount' === $flag ) {

			self::add_error(
				esc_html__(
					'Your settings has not been saved.The selected page for "Redirect Default WordPress Login To" is not a login page. Please select a valid login page.',
					'user-registration'
				)
			);

		}
		// Flush rules.
		wp_schedule_single_event( time(), 'user_registration_flush_rewrite_rules' );

		/**
		 * Action to save settings
		 */
		do_action( 'user_registration_settings_saved' );
	}

	/**
	 * Add a message.
	 *
	 * @param string $text Text.
	 */
	public static function add_message( $text ) {
		self::$messages[] = $text;
	}

	/**
	 * Add an error.
	 *
	 * @param string $text Text.
	 */
	public static function add_error( $text ) {
		self::$errors[] = $text;
	}

	/**
	 * Output messages + errors.
	 *
	 * @echo string
	 */
	public static function show_messages() {
		if ( count( self::$errors ) > 0 ) {
			foreach ( self::$errors as $error ) {
				echo '<div id="message" class="error inline"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
			}
		} elseif ( count( self::$messages ) > 0 ) {
			foreach ( self::$messages as $message ) {
				echo '<div id="message" class="updated inline"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
			}
		}
	}

	/**
	 * Settings page.
	 *
	 * Handles the display of the main Social Sharing settings page in admin.
	 */
	public static function output() {
		global $current_section, $current_tab;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		/**
		 * Action to output start settings
		 */
		do_action( 'user_registration_settings_start' );

		wp_enqueue_script( 'user-registration-settings', UR()->plugin_url() . '/assets/js/admin/settings' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'iris', 'tooltipster' ), UR_VERSION, true );
		wp_enqueue_script( 'ur-setup' );
		wp_localize_script(
			'user-registration-settings',
			'user_registration_settings_params',
			array(
				'ajax_url'                 => admin_url( 'admin-ajax.php' ),
				'user_registration_search_global_settings_nonce' => wp_create_nonce( 'user_registration_search_global_settings' ),
				'i18n_nav_warning'         => esc_html__( 'The changes you made will be lost if you navigate away from this page.', 'user-registration' ),
				'i18n'                     => array(
					'captcha_success'   => esc_html__( 'Captcha Test Successful !', 'user-registration' ),
					'captcha_failed'    => esc_html__( 'Some error occured. Please verify that the keys you entered are valid.', 'user-registration' ),
					'unsaved_changes'   => esc_html__( 'You have some unsaved changes. Please save and try again.', 'user-registration' ),
					'pro_feature_title' => esc_html__( 'is a Pro Feature', 'user-registration' ),
					'upgrade_message'   => esc_html__(
						'We apologize, but %title% is not available with the free version. To access this fantastic features, please consider upgrading to the %plan%.',
						'user-registration'
					),
					'upgrade_plan'      => esc_html__( 'Upgrade Plan', 'user-registration' ),
					'upgrade_link'      => esc_url( 'https://wpuserregistration.com/pricing/?utm_source=integration-settings&utm_medium=premium-addon-popup&utm_campaign=' . urlencode( UR()->utm_campaign ) ),
				),
				'is_advanced_field_active' => is_plugin_active( 'user-registration-advanced-fields/user-registration-advanced-fields.php' ),

			)
		);

		// Include settings pages.
		self::get_settings_pages();

		// Get current tab/section.
		$current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( wp_unslash( $_REQUEST['section'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		/**
		 * Filter to save settings actions
		 *
		 * @param boolean Save Settings or Not.
		 */
		$flag = apply_filters( 'user_registration_settings_save_action', true );

		if ( $flag ) {
			// Save settings if data has been posted.
			if ( ! empty( $_POST ) && ! empty( $_REQUEST['_wpnonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				self::save();
			}
		}

		// Add any posted messages.
		if ( ! empty( $_GET['ur_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			self::add_error( wp_unslash( $_GET['ur_error'] ) ); // phpcs:ignore
		}

		if ( ! empty( $_GET['ur_message'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			self::add_message( wp_unslash( $_GET['ur_error'] ) ); // phpcs:ignore
		}

		/**
		 * Filter to get tabs for settings page
		 *
		 * @param array Array of settings page
		 */
		$tabs = apply_filters( 'user_registration_settings_tabs_array', array() );

		if ( 'import_export' === $current_tab ) {
			$GLOBALS['hide_save_button'] = true;
		}

		include __DIR__ . '/views/html-admin-settings.php';
	}

	/**
	 * Get a setting from the settings API.
	 *
	 * @param mixed $option_name Option Name.
	 * @param mixed $default Default.
	 *
	 * @return string
	 */
	public static function get_option( $option_name, $default = '' ) {

		global $current_section;

		if ( 'add-new-popup' === $current_section ) {
			return $default;
		} else {
			// Array value.
			if ( null !== $option_name ) {
				if ( strstr( $option_name, '[' ) ) {
					parse_str( $option_name, $option_array );

					// Option name is first key.
					$option_name = current( array_keys( $option_array ) );

					// Get value.
					$option_values = get_option( $option_name, '' );

					$key = key( $option_array[ $option_name ] );

					if ( isset( $option_values[ $key ] ) ) {
						$option_value = $option_values[ $key ];
					} else {
						$option_value = null;
					}
				} else {
					$option_value = get_option( $option_name, null );
				}
			}

			if ( is_array( $option_value ) ) {
				$option_value = array_map( 'stripslashes', $option_value );
			} elseif ( ! is_null( $option_value ) ) {
				$option_value = stripslashes( $option_value );
			}

			return null === $option_value ? $default : $option_value;
		}
	}

	/**
	 * Output admin fields.
	 *
	 * Loops though the user registration options array and outputs each field.
	 *
	 * @param array $options Opens array to output.
	 */
	public static function output_fields( $options ) {
		$settings = '';

		if ( is_array( $options ) && ! empty( $options ) ) {
			$back_link      = isset( $options['back_link'] ) ? esc_url( $options['back_link'] ) : '';
			$back_link_text = isset( $options['back_link_text'] ) ? wp_kses_post( $options['back_link_text'] ) : '';

			if ( isset( $options['back_link'] ) ) {
				$settings .= '<a href="' . esc_url( $back_link ) . '" class="page-title-action">';

				if ( isset( $options['back_link_text'] ) ) {
					$settings .= wp_kses_post( $back_link_text );
				}

				$settings .= '</a>';
			}
			$settings .= '</h3>';

			if ( isset( $options['sections'] ) ) {

				foreach ( $options['sections'] as $id => $section ) {
					if ( ! isset( $section['type'] ) ) {
						continue;
					}

					if ( 'card' === $section['type'] ) {
						$settings .= '<div class="user-registration-card ur-mt-4 ur-border-0">';

						$header_css = '';
						if ( isset( $section['preview_link'] ) ) {
							$header_css = 'display:flex; justify-content: space-between;';
						}

						$settings .= '<div class="user-registration-card__header ur-border-0" style="' . esc_attr( $header_css ) . '">';
						if ( ! empty( $section['title'] ) ) {
							$settings .= '<h3 class="user-registration-card__title">' . esc_html( strtoupper( $section['title'] ) );

							if ( isset( $section['back_link'] ) ) {
								$settings .= wp_kses_post( $section['back_link'] );
							}

							$settings .= '</h3>';
						}

						if ( isset( $section['preview_link'] ) ) {
							$settings .= wp_kses_post( $section['preview_link'] );
						}

						$settings .= '</div>';

						if ( ! empty( $section['desc'] ) ) {
							$settings .= '<p class="ur-p-tag">' . wptexturize( wp_kses_post( $section['desc'] ) ) . '</p>';
						}
						$settings .= '<div class="user-registration-card__body pt-0 pb-0">';

						if ( ! empty( $id ) ) {
							/**
							 * Action to output settings
							 */
							do_action( 'user_registration_settings_' . sanitize_title( $id ) );
						}
					}

					if ( 'accordian' === $section['type'] ) {
						$available_in = isset( $section['available_in'] ) ? sanitize_text_field( wp_unslash( $section['available_in'] ) ) : '';

						if ( isset( $section['video_id'] ) ) {
							$inactive_class = 'user-registration-inactive-addon';
							$extras         = 'data-title="' . esc_attr( $section['title'] ) . '"';
							$extras        .= 'data-id="' . esc_attr( $section['id'] ) . '"';
							$extras        .= 'data-video="' . esc_attr( $section['video_id'] ) . '"';
							$extras        .= 'data-available-in="' . esc_attr( $available_in ) . '"';
							$settings      .= '<div class="user-registration-card ur-mb-2 ' . esc_attr( $inactive_class ) . '" ' . $extras . '>';
						} else {
							$settings .= '<div class="user-registration-card ur-mb-2">';
						}
						$settings .= '<div class="user-registration-card__header ur-d-flex ur-align-items-center ur-p-3 integration-header-info accordion">';
						$settings .= '<div class="integration-detail">';
						$settings .= '<span class="integration-status">';
						$settings .= '</span>';
						$settings .= '<figure class="logo">';
						$settings .= '<img src="' . UR()->plugin_url() . '/assets/images/settings-icons/' . $section['id'] . '.png" alt="' . $section['title'] . '">';
						$settings .= '</figure>';
						if ( ! empty( $section['title'] ) ) {
							$settings .= '<h3 class="user-registration-card__title">' . esc_html( $section['title'] );
							$settings .= '</h3>';
						}
						$settings .= '</div>';
						$settings .= '<div class="integration-action">';
						$settings .= '<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><polyline points="6 9 12 15 18 9"></polyline></svg>';
						$settings .= '</div>';
						$settings .= '</div>';
						if ( isset( $section['video_id'] ) ) {
							$settings .= '<div>';
						} else {
							$settings .= '<div class="user-registration-card__body ur-p-3 integration-body-info">';
						}

						if ( ! empty( $id ) ) {
							/**
							 * Action to output settings
							 */
							do_action( 'user_registration_settings_' . sanitize_title( $id ) );
						}
					}

					if ( isset( $section['settings'] ) && ( is_array( $section['settings'] ) || is_object( $section['settings'] ) ) ) {
						foreach ( $section['settings'] as $key => $value ) {

							if ( ! isset( $value['type'] ) ) {
								continue;
							}

							if ( ! isset( $value['id'] ) ) {
								$value['id'] = '';
							}
							if ( ! isset( $value['row_class'] ) ) {
								$value['row_class'] = '';
							}
							if ( ! isset( $value['rows'] ) ) {
								$value['rows'] = '';
							}
							if ( ! isset( $value['cols'] ) ) {
								$value['cols'] = '';
							}
							if ( ! isset( $value['title'] ) ) {
								$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
							}
							if ( ! isset( $value['class'] ) ) {
								$value['class'] = '';
							}
							if ( ! isset( $value['css'] ) ) {
								$value['css'] = '';
							}
							if ( ! isset( $value['default'] ) ) {
								$value['default'] = '';
							}
							if ( ! isset( $value['desc'] ) ) {
								$value['desc'] = '';
							}
							if ( ! isset( $value['desc_tip'] ) ) {
								$value['desc_tip'] = false;
							}
							if ( ! isset( $value['desc_field'] ) ) {
								$value['desc_field'] = false;
							}
							if ( ! isset( $value['placeholder'] ) ) {
								$value['placeholder'] = '';
							}

							// Capitalize Setting Label.
							$value['title'] = self::capitalize_title( $value['title'] );

							// Custom attribute handling.
							$custom_attributes = array();

							if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
								foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
									$custom_attributes[] = esc_attr( $attribute ) . '=' . esc_attr( $attribute_value ) . '';
								}
							}

							// Description handling.
							$field_description = self::get_field_description( $value );
							extract( $field_description ); // phpcs:ignore

							// Switch based on type.
							switch ( $value['type'] ) {

								// Standard text inputs and subtypes like 'number'.
								case 'text':
								case 'email':
								case 'number':
								case 'password':
								case 'date':
									$option_value = self::get_option( $value['id'], $value['default'] );

									$settings .= '<div class="user-registration-global-settings">';
									$settings .= '<label class="ur-label" for="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['title'] ) . ' ' . wp_kses_post( $tooltip_html ) . '</label>';
									$settings .= '<div class="user-registration-global-settings--field">';
									$settings .= '<input
											name="' . esc_attr( $value['id'] ) . '"
											id="' . esc_attr( $value['id'] ) . '"
											type="' . esc_attr( $value['type'] ) . '"
											style="' . esc_attr( $value['css'] ) . '"
											value="' . esc_attr( $option_value ) . '"
											class="' . esc_attr( $value['class'] ) . '"
											placeholder="' . esc_attr( $value['placeholder'] ) . '"
											' . esc_attr( implode( ' ', $custom_attributes ) ) . ' ' . wp_kses_post( $description ) . '/>';
									$settings .= '</div>';
									$settings .= '</div>';
									break;
								case 'nonce':
									$settings .= '<div class="user-registration-global-settings">';
									$settings .= '<div class="user-registration-global-settings--field">';
									$settings .= '<input
											name="' . esc_attr( $value['id'] ) . '"
											id="' . esc_attr( $value['id'] ) . '"
											type="hidden"
											value="' . esc_attr( wp_create_nonce( $value['action'] ) ) . '"
											/>';
									$settings .= '</div>';
									$settings .= '</div>';
									break;

								// Color picker.
								case 'color':
									$option_value = self::get_option( $value['id'], $value['default'] );
									$settings    .= '<div class="user-registration-global-settings">';
									$settings    .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['title'] ) . ' ' . wp_kses_post( $tooltip_html ) . '</label>';
									$settings    .= '<div class="user-registration-global-settings--field">';
									$settings    .= '<input
											name="' . esc_attr( $value['id'] ) . '"
											id="' . esc_attr( $value['id'] ) . '"
											type="text"
											dir="ltr"
											style="' . esc_attr( $value['css'] ) . '"
											value="' . esc_attr( $option_value ) . '"
											class="' . esc_attr( $value['class'] ) . 'colorpick"
											placeholder="' . esc_attr( $value['placeholder'] ) . '"
											' . esc_attr( implode( ' ', $custom_attributes ) ) . '/>&lrm;' . wp_kses_post( $description );
									$settings    .= '<div id="colorPickerDiv_' . esc_attr( $value['id'] ) . '" class="colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"></div></div>';
									$settings    .= '</div>';
									break;

								// Textarea.
								case 'textarea':
									$option_value = self::get_option( $value['id'], $value['default'] );

									$settings .= '<div class="user-registration-global-settings">';
									$settings .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['title'] ) . ' ' . wp_kses_post( $tooltip_html ) . '</label>';
									$settings .= '<div class="user-registration-global-settings--field">';
									$settings .= wp_kses_post( $description );
									$settings .= '<textarea
											name="' . esc_attr( $value['id'] ) . '"
											id="' . esc_attr( $value['id'] ) . '"
											style="' . esc_attr( $value['css'] ) . '"
											class="' . esc_attr( $value['class'] ) . '"
											rows="' . esc_attr( $value['rows'] ) . '"
											cols="' . esc_attr( $value['cols'] ) . '"
											placeholder="' . esc_attr( $value['placeholder'] ) . '"
											' . esc_html( implode( ' ', $custom_attributes ) ) . '>'
											. esc_textarea( $option_value ) . '</textarea>';
									$settings .= '</div>';
									$settings .= '</div>';
									break;

								// Select boxes.
								case 'select':
								case 'multiselect':
									$option_value = self::get_option( $value['id'], $value['default'] );

									$settings .= '<div class="user-registration-global-settings">';
									$settings .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['title'] ) . ' ' . wp_kses_post( $tooltip_html ) . '</label>';
									$settings .= '<div class="user-registration-global-settings--field">';
									$multiple  = '';
									$type      = '';
									if ( 'multiselect' == $value['type'] ) {
										$type     = '[]';
										$multiple = 'multiple="multiple"';
									}

									$settings .= '<select
											name="' . esc_attr( $value['id'] ) . '' . $type . '"
											id="' . esc_attr( $value['id'] ) . '"
											style="' . esc_attr( $value['css'] ) . '"
											class="' . esc_attr( $value['class'] ) . '"
											' . esc_attr( implode( ' ', $custom_attributes ) ) . '
											' . esc_attr( $multiple ) . '>';

									foreach ( $value['options'] as $key => $val ) {
										$selected = '';

										if ( is_array( $option_value ) ) {
											$selected = selected( in_array( $key, $option_value ), true, false );
										} else {
											$selected = selected( $option_value, $key, false );
										}

										$settings .= '<option value="' . esc_attr( $key ) . '" ' . esc_attr( $selected ) . '>';
										$settings .= esc_html( $val );
										$settings .= '</option>';
									}

									$settings .= '</select>' . wp_kses_post( $description );
									$settings .= '</div>';
									$settings .= '</div>';
									break;

								// Radio inputs.
								case 'radio':
									$option_value = self::get_option( $value['id'], $value['default'] );
									$settings    .= '<div class="user-registration-global-settings">';
									$settings    .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['title'] ) . ' ' . wp_kses_post( $tooltip_html ) . '</label>';
									$settings    .= '<div class="user-registration-global-settings--field">';
									$settings    .= '<fieldset>';
									$settings    .= wp_kses_post( $description );
									$settings    .= '<ul>';

									foreach ( $value['options'] as $key => $val ) {
										$settings .= '<li>';
										$settings .= '<label>';
										$settings .= '<input
													name="' . esc_attr( $value['id'] ) . '"
													value="' . esc_attr( $key ) . '"
													type="radio"
													style="' . esc_attr( $value['css'] ) . '"
													class="' . esc_attr( $value['class'] ) . '"
													' . esc_attr( implode( ' ', $custom_attributes ) ) . '
													' . esc_attr( checked( $key, $option_value, false ) ) . '
													/>' . wp_kses_post( $val ) . '</label>';
										$settings .= '</li>';
									}

									$settings .= '</ul>';
									$settings .= '</fieldset>';
									$settings .= '</div>';
									$settings .= '</div>';
									break;

								// Checkbox input.
								case 'checkbox':
									$option_value = self::get_option( $value['id'], $value['default'] );

									$visbility_class = array();

									if ( ! isset( $value['hide_if_checked'] ) ) {
										$value['hide_if_checked'] = false;
									}
									if ( ! isset( $value['show_if_checked'] ) ) {
										$value['show_if_checked'] = false;
									}
									if ( 'yes' === $value['hide_if_checked'] || 'yes' === $value['show_if_checked'] ) {
										$visbility_class[] = 'hidden_option';
									}
									if ( 'option' === $value['hide_if_checked'] ) {
										$visbility_class[] = 'hide_options_if_checked';
									}
									if ( 'option' === $value['show_if_checked'] ) {
										$visbility_class[] = 'show_options_if_checked';
									}
									$settings .= '<div class="user-registration-global-settings ' . esc_attr( implode( ' ', $visbility_class ) ) . ' ' . esc_attr( $value['row_class'] ) . '">';

									if ( ! isset( $value['checkboxgroup'] ) || 'start' === $value['checkboxgroup'] ) {
										$settings .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['title'] ) . ' ' . wp_kses_post( $tooltip_html ) . '</label>';
										$settings .= '<div class="user-registration-global-settings--field">';
										$settings .= '<fieldset>';
									} else {
										$settings .= '<div class="user-registration-global-settings--field">';
										$settings .= '<fieldset class="' . esc_attr( implode( ' ', $visbility_class ) ) . '">';
									}

									$settings .= '<input
											name="' . esc_attr( $value['id'] ) . '"
											id="' . esc_attr( $value['id'] ) . '"
											type="checkbox"
											class="' . esc_attr( isset( $value['class'] ) ? $value['class'] : '' ) . '"
											value="1"
											' . esc_attr( checked( $option_value, 'yes', false ) ) . '
											' . esc_attr( implode( ' ', $custom_attributes ) ) . '/>';

									$settings .= '</fieldset>';
									$settings .= wp_kses_post( $description );
									$settings .= wp_kses_post( $desc_field );
									$settings .= '</div>';
									break;

								// Single page selects.
								case 'single_select_page':
									$args = array(
										'name'             => $value['id'],
										'id'               => $value['id'],
										'sort_column'      => 'menu_order',
										'sort_order'       => 'ASC',
										'show_option_none' => ' ',
										'class'            => $value['class'],
										'echo'             => false,
										'selected'         => absint( self::get_option( $value['id'] ) ),
									);

									if ( isset( $value['args'] ) ) {
										$args = wp_parse_args( $value['args'], $args );
									}

									$settings .= '<div class="user-registration-global-settings single_select_page" ' . ( ( isset( $value['display'] ) && 'none' === $value['display'] ) ? 'style="display:none"' : '' ) . '>';
									$settings .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['title'] ) . ' ' . wp_kses_post( $tooltip_html ) . '</label>';
									$settings .= '<div class="user-registration-global-settings--field">';
									$settings .= str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'user-registration' ) . "' style='" . esc_attr( $value['css'] ) . "' class='" . esc_attr( $value['class'] ) . "' id=", wp_dropdown_pages( $args ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									$settings .= wp_kses_post( $description );
									$settings .= '</div>';
									$settings .= '</div>';
									break;

								case 'tinymce':
									$editor_settings = array(
										'name'       => esc_attr( $value['id'] ),
										'id'         => esc_attr( $value['id'] ),
										'style'      => esc_attr( $value['css'] ),
										'default'    => esc_attr( $value['default'] ),
										'class'      => esc_attr( $value['class'] ),
										'quicktags'  => array( 'buttons' => 'em,strong,link' ),
										'tinymce'    => array(
											'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
											'theme_advanced_buttons2' => '',
										),
										'editor_css' => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>',
									);

									$option_value = self::get_option( $value['id'], $value['default'] );

									$settings .= '<div class="user-registration-global-settings">';
									$settings .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['title'] ) . ' ' . wp_kses_post( $tooltip_html ) . '</label>';
									$settings .= '<div class="user-registration-global-settings--field">';
									$settings .= wp_kses_post( $description );

									// Output buffer for tinymce editor.
									ob_start();
									wp_editor( $option_value, $value['id'], $editor_settings );
									$settings .= ob_get_clean();

									$settings .= '</div>';
									$settings .= '</div>';

									break;

								case 'link':
									$settings .= '<div class="user-registration-global-settings">';
									$settings .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_attr( $value['title'] ) . ' ' . wp_kses_post( $tooltip_html ) . '</label>';
									$settings .= '<div class="user-registration-global-settings--field">';

									if ( isset( $value['buttons'] ) && is_array( $value['buttons'] ) ) {
										foreach ( $value['buttons'] as $button ) {
											$settings .= '<a
														href="' . esc_url( $button['href'] ) . '"
														class="button ' . esc_attr( $button['class'] ) . '" style="' . esc_attr( $value['css'] ) . '">' . esc_html( $button['title'] ) . '</a>';
										}
									}

									$settings .= ( isset( $value['desc'] ) && isset( $value['desc_tip'] ) && true !== $value['desc_tip'] ) ? '<p class="description" >' . wp_kses_post( $value['desc'] ) . '</p>' : '';
									$settings .= '</div>';
									$settings .= '</div>';
									break;
								// Image upload.
								case 'image':
									$option_value = self::get_option( $value['id'], $value['default'] );

									$settings .= '<div class="user-registration-global-settings image-upload">';

									$settings .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_attr( $value['title'] ) . ' ' . wp_kses_post( $tooltip_html ) . '</label>';
									$settings .= '<div class="user-registration-global-settings--field">';
									$settings .= '<img src="' . esc_attr( $option_value ) . '" alt="' . esc_attr__( 'Header Logo', 'user-registration' ) . '" class="ur-image-uploader" height="auto" width="20%">';
									$settings .= '<button type="button" class="ur-image-uploader ur-button button-secondary" ' . ( empty( $option_value ) ? '' : 'style = "display:none"' ) . '>' . esc_html__( 'Upload Image', 'user-registration' ) . '</button>';
									$settings .= '<button type="button" class="ur-image-remover ur-button button-secondary" ' . ( ! empty( $option_value ) ? '' : 'style = "display:none"' ) . '>' . esc_html__( 'Remove Image', 'user-registration' ) . '</button>';

									$settings .= '	<input
											name="' . esc_attr( $value['id'] ) . '"
											id="' . esc_attr( $value['id'] ) . '"
											value="' . esc_attr( $option_value ) . '"
											type="hidden"
										>';
									$settings .= '</div>';
									$settings .= '</div>';
									wp_enqueue_media();

									break;

								// Radio image inputs.
								case 'radio-image':
									$option_value = self::get_option( $value['id'], $value['default'] );

									$settings .= '<div class="user-registration-global-settings radio-image">';
									$settings .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_attr( $value['title'] ) . ' ' . wp_kses_post( $tooltip_html ) . '</label>';
									$settings .= '<div class="user-registration-global-settings--field">';
									$settings .= '<ul>';

									foreach ( $value['options'] as $key => $val ) {
										$settings .= '<li>';
										$settings .= '<label class="' . ( esc_attr( checked( $key, $option_value, false ) ) ? 'selected' : '' ) . '">';
										$settings .= '<img src="' . esc_html( $val['image'] ) . '">';
										$settings .= '<input
													name="' . esc_attr( $value['id'] ) . '"
													value="' . esc_attr( $key ) . '"
													type="radio"
													style="' . esc_attr( $value['css'] ) . '"
													class="' . esc_attr( $value['class'] ) . '"
													' . esc_attr( implode( ' ', $custom_attributes ) ) . '
													' . esc_attr( checked( $key, $option_value, false ) ) . '>';

										$settings .= esc_html( $val['name'] );
										$settings .= '</label>';
										$settings .= '</li>';
									}

									$settings .= '</ul>';
									$settings .= '</div>';
									$settings .= '</div>';
									break;
								// Toggle input.
								case 'toggle':
									$option_value = self::get_option( $value['id'], $value['default'] );

									$settings .= '<div class="user-registration-global-settings">';
									$settings .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['title'] ) . ' ' . wp_kses_post( $tooltip_html ) . '</label>';
									$settings .= '<div class="user-registration-global-settings--field">';
									$settings .= '<div class="ur-toggle-section">';
									$settings .= '<span class="user-registration-toggle-form">';
									$settings .= '<input
												type="checkbox"
												name="' . esc_attr( $value['id'] ) . '"
												id="' . esc_attr( $value['id'] ) . '"
												style="' . esc_attr( $value['css'] ) . '"
												class="' . esc_attr( $value['class'] ) . '"
												value="1"
												' . esc_attr( implode( ' ', $custom_attributes ) ) . '
												' . esc_attr( checked( true, ur_string_to_bool( $option_value ), false ) ) . '>';
									$settings .= '<span class="slider round"></span>';
									$settings .= '</span>';
									$settings .= '</div>';
									$settings .= wp_kses_post( $description );
									$settings .= wp_kses_post( $desc_field );
									$settings .= '</div>';
									$settings .= '</div>';
									break;
								case 'radio-group':
									$option_value = self::get_option( $value['id'], $value['default'] );
									$options      = isset( $value['options'] ) ? $value['options'] : array(); // $args['choices'] for backward compatibility. Modified since 1.5.7.

									if ( ! empty( $options ) ) {
										$settings .= '<div class="user-registration-global-settings">';
										$settings .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['title'] ) . ' ' . wp_kses_post( $tooltip_html ) . '</label>';
										$settings .= '<div class="user-registration-global-settings--field">';

										$settings .= '<ul class="ur-radio-group-list">';
										foreach ( $options as $option_index => $option_text ) {
											$class     = str_replace( ' ', '-', strtolower( $option_text ) );
											$settings .= '<li class="ur-radio-group-list--item  ' . $class . ( trim( $option_index ) === $option_value ? ' active' : '' ) . '">';

											$checked = '';

											if ( '' !== $option_value ) {
												$checked = checked( $option_value, trim( $option_index ), false );
											}

											$settings .= '<label for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_text ) . '" class="radio">';

											if ( isset( $value['radio-group-images'] ) ) {
												$settings .= '<img src="' . $value['radio-group-images'][ $option_index ] . '" />';
											}

											$settings .= wp_kses(
												trim( $option_text ),
												array(
													'a'    => array(
														'href' => array(),
														'title' => array(),
													),
													'span' => array(),
												)
											);

											$settings .= '<input type="radio" name="' . esc_attr( $value['id'] ) . '" id="' . esc_attr( $value['id'] ) . '"	style="' . esc_attr( $value['css'] ) . '" class="' . esc_attr( $value['class'] ) . '" value="' . esc_attr( trim( $option_index ) ) . '" ' . implode( ' ', $custom_attributes ) . ' / ' . $checked . ' /> ';
											$settings .= '</label>';

											$settings .= '</li>';
										}
										$settings .= '</ul>';
										$settings .= '</div>';
										$settings .= '</div>';

									}
									break;
								// Default: run an action.
								default:
									/**
									 * Filter to retrieve default admin field for output
									 *
									 * @param string $settings Settings.
									 * @param mixed $settings Field value.
									 */
									$settings = apply_filters( 'user_registration_admin_field_' . $value['type'], $settings, $value );
									break;
							}// End switch case.
						}
					} elseif ( isset( $section['settings'] ) && is_string( $section['settings'] ) ) {
						$settings .= $section['settings'];
					}

					/**
					 * Filter to retrieve extra settings for this section.
					 *
					 * @param string $settings Settings.
					 * @param mixed $options Section options.
					 */
					$settings = apply_filters( 'user_registration_admin_after_global_settings', $settings, $options );

					$settings .= ' </div> ';
					$settings .= ' </div> ';

					if ( ! empty( $section['id'] ) ) {
						/**
						 * Action after output settings
						 */
						do_action( 'user_registration_settings_' . sanitize_title( $section['id'] ) . '_after' );
					}
				}// End foreach.
			}
		}
		echo $settings; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Helper function to get the formated description and tip HTML for a
	 * given form field. Plugins can call this when implementing their own custom
	 * settings types.
	 *
	 * @param  array $value The form field value array.
	 *
	 * @return array The description and tip as a 2 element array
	 */
	public static function get_field_description( $value ) {
		$description  = '';
		$tooltip_html = '';
		$desc_field   = '';

		if ( true === $value['desc_tip'] ) {
			$tooltip_html = $value['desc'];
		} elseif ( ! empty( $value['desc_tip'] ) ) {
			$description  = $value['desc'];
			$tooltip_html = $value['desc_tip'];
		} elseif ( ! empty( $value['desc'] ) ) {
			$description = $value['desc'];
		}

		if ( ! empty( $value['desc_field'] ) ) {
			$desc_field = $value['desc_field'];
		}

		if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ) ) ) {
			$description = ' <p style ="margin-top:0" > ' . wp_kses_post( $description ) . ' </p > ';
		} elseif ( $description && in_array( $value['type'], array( 'checkbox' ) ) ) {
			$description = wp_kses_post( $description );
		} elseif ( $description ) {
			$description = ' <span class = "description" > ' . wp_kses_post( $description ) . ' </span > ';
		}

		if ( $desc_field && in_array( $value['type'], array( 'textarea', 'radio', 'checkbox' ) ) ) {
			$desc_field = ' <p class = "description" > ' . wp_kses_post( $desc_field ) . ' </p > ';
		} elseif ( $desc_field ) {
			$desc_field = ' <span class = "description" > ' . wp_kses_post( $desc_field ) . ' </span > ';
		}

		if ( $tooltip_html ) {
			$tooltip_html = ur_help_tip( $tooltip_html );
		}

		return array(
			'description'  => $description,
			'desc_field'   => $desc_field,
			'tooltip_html' => $tooltip_html,
		);
	}

	/**
	 * Save admin fields.
	 *
	 * Loops though the user registration options array and outputs each field.
	 *
	 * @param  array $options Options array to output.
	 *
	 * @return bool
	 */
	public static function save_fields( $options ) {
		if ( empty( $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return false;
		}

		// Options to update will be stored here and saved later.
		$update_options = array();

		if ( empty( $options ) ) {
			return false;
		}
		// Return if default wp_login is disabled and no redirect url is set.
		$is_wp_login_disabled_error = apply_filters( 'user_registration_settings_prevent_default_login', $_POST );
		if ( $is_wp_login_disabled_error && 'redirect_login_error' === $is_wp_login_disabled_error ) {
			return;
		}

		// Loop options and get values to save.
		foreach ( $options['sections'] as $id => $section ) {
			if ( ! isset( $id ) || ! isset( $section['type'] ) ) {
				continue;
			}

			foreach ( $section['settings'] as $option ) {
				// Get posted value.
				if ( null !== $option['id'] ) {
					if ( strstr( $option['id'], '[' ) ) {
						parse_str( $option['id'], $option_name_array );
						$option_name = sanitize_text_field( current( array_keys( $option_name_array ) ) );

						$setting_name = key( $option_name_array[ $option_name ] );
						$raw_value    = isset( $_POST[ $option_name ][ $setting_name ] ) ? wp_unslash( $_POST[ $option_name ][ $setting_name ] ) : null; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					} else {
						$option_name  = sanitize_text_field( $option['id'] );
						$setting_name = '';
						$raw_value    = isset( $_POST[ $option['id'] ] ) ? wp_unslash( $_POST[ $option['id'] ] ) : null; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					}
				}

				// Format the value based on option type.
				switch ( $option['type'] ) {

					case 'checkbox':
					case 'toggle':
						$value = ur_string_to_bool( $raw_value );
						break;
					case 'textarea':
						$value = wp_kses_post( trim( $raw_value ) );
						break;
					case 'multiselect':
						$value = array_filter( array_map( 'ur_clean', (array) $raw_value ) );
						break;
					case 'select':
						$allowed_values = empty( $option['options'] ) ? array() : array_keys( $option['options'] );
						if ( empty( $option['default'] ) && empty( $allowed_values ) ) {
							$value = null;
							break;
						}
						$default = ( empty( $option['default'] ) ? $allowed_values[0] : $option['default'] );
						$value   = in_array( $raw_value, $allowed_values ) ? sanitize_text_field( $raw_value ) : sanitize_text_field( $default );
						break;
					case 'tinymce':
						$value = wpautop( $raw_value );
						break;

					default:
						$value = ur_clean( $raw_value );
						break;
				}

				/**
				 * Filter to Sanitize the value of an option.
				 *
				 * @param boolean $value String converted to Boolean
				 * @param mixed $option Option to save
				 * @param string $raw_value Option value to save
				 */
				$value = apply_filters( 'user_registration_admin_settings_sanitize_option', $value, $option, $raw_value );

				/**
				 * Filter to Sanitize the value of an option by option name.
				 *
				 * @param boolean $value String converted to Boolean
				 * @param mixed $option Option value to save
				 * @param string $raw_value Option value to save
				 */
				$value = apply_filters( "user_registration_admin_settings_sanitize_option_$option_name", $value, $option, $raw_value );

				if ( is_null( $value ) || count( self::$errors ) > 0 ) {
					continue;
				}

				// Check if option is an array and handle that differently to single values.
				if ( $option_name && $setting_name ) {
					if ( ! isset( $update_options[ $option_name ] ) ) {
						$update_options[ $option_name ] = get_option( $option_name, array() );
					}
					if ( ! is_array( $update_options[ $option_name ] ) ) {
						$update_options[ $option_name ] = array();
					}
					$update_options[ $option_name ][ $setting_name ] = $value;
				} else {
					$update_options[ $option_name ] = $value;
				}
			}
		}// End foreach().

		// Save all options in our array.
		foreach ( $update_options as $name => $value ) {
			update_option( $name, $value );
		}

		return true;
	}

	/**
	 * Capitalize Settings Title Phrase.
	 *
	 * @param string $text Setting Label.
	 */
	public static function capitalize_title( $text = null ) {
		$prepositions = array( 'at', 'by', 'for', 'in', 'on', 'to', 'or' );

		$words = explode( ' ', $text );

		$capitalized_words = array();

		foreach ( $words as $word ) {
			$word = trim( $word );
			if ( ! in_array( $word, $prepositions ) ) {

				// Check if the word is a shash separated terms. Eg: "Hide/Show".
				if ( strpos( $word, '/' ) ) {
					$separate_terms    = explode( '/', $word );
					$capitalized_terms = array();

					foreach ( $separate_terms as $term ) {
						$capitalized_terms[] = ucfirst( $term );
					}

					$word = implode( '/', $capitalized_terms );
				} elseif ( strpos( $word, 'CAPTCHA' ) ) {
					$word = $word;
				} else {
					$word = ucfirst( $word );
				}
			}
			$capitalized_words[] = $word;
		}

		return implode( ' ', $capitalized_words );
	}

	/**
	 * Search GLobal Settings.
	 */
	public static function search_settings() {
		$search_string = isset( $_POST['search_string'] ) ? sanitize_text_field( wp_unslash( $_POST['search_string'] ) ) : ''; //phpcs:ignore;
		$search_url    = '';
		$found         = false;

		// Create an array of results to return as JSON.
		$autocomplete_results = array();
		$index                = 0;

		$settings = self::get_settings_pages();

		if ( ! empty( $settings ) ) {

			foreach ( $settings as $key => $section ) {
				if ( is_bool( $section ) || ! method_exists( $section, 'get_settings' ) ) {
					unset( $settings[ $key ] );
					continue;
				}
				$reflection = new ReflectionProperty( get_class( $section ), 'id' );

				if ( ! $reflection->isPublic() ) {
					unset( $settings[ $key ] );
					continue;
				}
			}

			foreach ( $settings as $section ) {

				$subsections = array_values( array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) ) );

				if ( ! empty( $subsections ) ) {

					foreach ( $subsections as $subsection ) {

						if ( 'user-registration-invite-codes' === $section->id ) {
							if ( '' !== $subsection ) {
								$subsection_array = $section->get_settings( $subsection );
							}
						} else {
							switch ( $subsection ) {
								case 'login-options':
									$subsection_array = $section->get_login_options_settings();
									break;
								case 'frontend-messages':
									$subsection_array = $section->get_frontend_messages_settings();
									break;
								default:
									$subsection_array = $section->get_settings( $subsection );
									break;
							}
						}

						if ( is_array( $subsection_array ) && ! empty( $subsection_array ) ) {
							$flattened_array = self::flatten_array( $subsection_array );
							$result          = self::search_string_in_array( $search_string, $flattened_array );
							if ( ! empty( $result ) ) {
								foreach ( $result as $key => $value ) {
									$match = array_search( $value['title'], array_column( $autocomplete_results, 'label' ), true ); //phpcs:ignore;
									if ( false === $match ) {
										$autocomplete_results[ $index ]['label'] = $value['title'];
										$autocomplete_results[ $index ]['desc']  = $value['desc'];
										if ( ! empty( $subsection ) ) {
											$autocomplete_results[ $index ]['value'] = admin_url( 'admin.php?page=user-registration-settings&tab=' . $section->id . '&section=' . $subsection . '&searched_option=' . $value['id'] );
										} else {
											$autocomplete_results[ $index ]['value'] = admin_url( 'admin.php?page=user-registration-settings&tab=' . $section->id . '&searched_option=' . $value['id'] );
										}
										++$index;
									}
								}
								continue;
							}
						}
					}
				}
			}
		}

		if ( ! empty( $autocomplete_results ) ) {
			wp_send_json_success(
				array(
					'results' => $autocomplete_results,
				)
			);
		} else {
			$autocomplete_results[ $index ]['label'] = __( 'No Search result found !', 'user-registration' );
			$autocomplete_results[ $index ]['desc']  = '';
			$autocomplete_results[ $index ]['value'] = 'no_result_found';

			wp_send_json_success(
				array(
					'results' => $autocomplete_results,
				)
			);
		}
	}

	/**
	 * Search String in Array.
	 *
	 * @param string $string_to_search String to Search.
	 * @param array  $array Search Array.
	 */
	public static function search_string_in_array( $string_to_search, $array ) {
		$result = array();
		if ( is_object( $array ) ) {
			$array = (array) $array;
		}
		$index = 0;

		foreach ( $array as $key => $value ) {

			if ( is_array( $value ) ) {

				foreach ( $value as $text ) {
					if ( ! is_array( $text ) ) {
						if ( stripos( $text, $string_to_search ) !== false ) {

							$result[ $index ]['id']    = isset( $value['id'] ) ? $value['id'] : 'true';
							$result[ $index ]['title'] = isset( $value['title'] ) ? $value['title'] : 'true';
							$desc_tip                  = isset( $value['desc_tip'] ) && true !== $value['desc_tip'] ? $value['desc_tip'] : '';
							$desc                      = isset( $value['desc'] ) && true !== $value['desc'] ? $value['desc'] : '';
							$result[ $index ]['desc']  = ! empty( $desc_tip ) ? $desc_tip : $desc;
							++$index;
							break;
						}
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Return Non Nested Array from Nested Settings Array
	 *
	 * @param array $nested_array Nested Settings Array.
	 *
	 * @return array
	 */
	public static function flatten_array( $nested_array ) {

		$settings_array = array();  // create an empty array to store the list of settings.
		if ( isset( $nested_array['sections'] ) ) {
			// loop through each section in the array.
			foreach ( $nested_array['sections'] as $section ) {

				if ( isset( $section['settings'] ) ) {

					if ( is_string( $section['settings'] ) ) {
						continue;
					}

					// loop through each setting in the section and add it to the $settings_array.
					foreach ( $section['settings'] as $setting ) {
						$settings_array[] = $setting;
					}
				} else {
					$inner_settings = self::flatten_array( $section );
					if ( ! empty( $inner_settings ) ) {
						$settings_array[] = $inner_settings;
					}
				}
			}
		}

		return $settings_array;
	}
}
