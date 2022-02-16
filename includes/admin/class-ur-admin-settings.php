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

			include_once dirname( __FILE__ ) . '/settings/class-ur-settings-page.php';

			$settings[] = include 'settings/class-ur-settings-general.php';
			$settings[] = include 'settings/class-ur-settings-integration.php';
			$settings[] = include 'settings/class-ur-settings-email.php';
			$settings[] = include 'settings/class-ur-settings-import-export.php';
			$settings[] = include 'settings/class-ur-settings-license.php';

			self::$settings = apply_filters( 'user_registration_get_settings_pages', $settings );
		}

		return self::$settings;
	}

	/**
	 * Save the settings.
	 */
	public static function save() {
		global $current_tab;

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'user-registration-settings' ) ) {
			die( __( 'Action failed. Please refresh the page and retry.', 'user-registration' ) );
		}

		// Trigger actions.
		do_action( 'user_registration_settings_save_' . $current_tab );
		do_action( 'user_registration_update_options_' . $current_tab );
		do_action( 'user_registration_update_options' );

		$flag = apply_filters( 'show_user_registration_setting_message', true );

		if ( $flag ) {
			self::add_message( esc_html__( 'Your settings have been saved.', 'user-registration' ) );
		}

		// Flush rules.
		wp_schedule_single_event( time(), 'user_registration_flush_rewrite_rules' );

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
		if ( sizeof( self::$errors ) > 0 ) {
			foreach ( self::$errors as $error ) {
				echo '<div id="message" class="error inline"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
			}
		} elseif ( sizeof( self::$messages ) > 0 ) {
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

		do_action( 'user_registration_settings_start' );

		wp_enqueue_script( 'user-registration-settings', UR()->plugin_url() . '/assets/js/admin/settings' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'iris' ), UR_VERSION, true );

		wp_localize_script(
			'user-registration-settings',
			'user_registration_settings_params',
			array(
				'i18n_nav_warning' => esc_html__( 'The changes you made will be lost if you navigate away from this page.', 'user-registration' ),
			)
		);

		// Include settings pages.
		self::get_settings_pages();

		// Get current tab/section
		$current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] );
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );

		$flag = apply_filters( 'user_registration_settings_save_action', true );

		if ( $flag ) {

			// Save settings if data has been posted.
			if ( ! empty( $_POST ) && ! empty( $_REQUEST['_wpnonce'] ) ) {
				self::save();
			}
		}

		// Add any posted messages
		if ( ! empty( $_GET['ur_error'] ) ) {
			self::add_error( stripslashes( $_GET['ur_error'] ) );
		}

		if ( ! empty( $_GET['ur_message'] ) ) {
			self::add_message( stripslashes( $_GET['ur_error'] ) );
		}

		// Get tabs for the settings page
		$tabs = apply_filters( 'user_registration_settings_tabs_array', array() );

		if ( 'import_export' === $current_tab ) {
			$GLOBALS['hide_save_button'] = true;
		}

		include dirname( __FILE__ ) . '/views/html-admin-settings.php';
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
			if ( strstr( $option_name, '[' ) ) {
				parse_str( $option_name, $option_array );

				// Option name is first key
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
	 * @param array[] $options Opens array to output.
	 */
	public static function output_fields( $options ) {
		$settings = '';

		if ( is_array( $options ) && ! empty( $options ) ) {

			$settings .= '<h3 class="ur-settings-section-header main_header">' . esc_html( ucwords( $options['title'] ) );
			$back_link = isset( $options['back_link'] ) ? esc_url( $options['back_link'] ) : '';
			$back_link_text = isset( $options['back_link_text'] ) ? wp_kses_post( $options['back_link_text'] ) : '';

			if ( isset( $options['back_link'] ) ) {
				$settings .= '<a href="' . esc_url( $back_link ) . '" class="page-title-action">';

				if ( isset( $options['back_link_text'] ) ) {
					$settings .= wp_kses_post( $back_link_text );
				}

				$settings .= '</a>';
			}
			$settings .= '</h3>';

			foreach ( $options['sections'] as $id => $section ) {
				if ( ! isset( $section['type'] ) ) {
					continue;
				}

				if ( 'card' === $section['type'] ) {
					$settings .= '<div class="user-registration-card ur-mt-4 ur-border-0">';
					$settings .= '<div class="user-registration-card__header ur-border-0">';

					if ( ! empty( $section['title'] ) ) {
						$settings .= '<h3 class="user-registration-card__title">' . esc_html( strtoupper( $section['title'] ) );

						if ( isset( $section['back_link'] ) ) {
							$settings .= wp_kses_post( $section['back_link'] );
						}

						$settings .= '</h3>';
					}
					$settings .= '</div>';

					if ( ! empty( $section['desc'] ) ) {
						$settings .= '<p class="ur-p-tag">' . wptexturize( wp_kses_post( $section['desc'] ) ) . '</p>';
					}
					$settings .= '<div class="user-registration-card__body pt-0 pb-0">';
					$settings .= '<table class="form-table">' . "\n\n";

					if ( ! empty( $id ) ) {
						do_action( 'user_registration_settings_' . sanitize_title( $id ) );
					}
				}

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

					// Custom attribute handling
					$custom_attributes = array();

					if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
						foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
							$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
						}
					}

					// Description handling.
					$field_description = self::get_field_description( $value );
					extract( $field_description );

					// Switch based on type.
					switch ( $value['type'] ) {

						// Standard text inputs and subtypes like 'number'.
						case 'text':
						case 'email':
						case 'number':
						case 'password':
						case 'date':
							$option_value = self::get_option( $value['id'], $value['default'] );

							$settings .= '<tr valign="top" class="' . esc_attr( $value['row_class'] ) . '">';
							$settings .= '<th scope="row" class="titledesc">';
							$settings .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['title'] ) . '</label>' . wp_kses_post( $tooltip_html ) . '</th>';
							$settings .= '<td class="forminp forminp-' . esc_attr( sanitize_title( $value['type'] ) ) . '">';
							$settings .= '<input
										name="' . esc_attr( $value['id'] ) . '"
										id="' . esc_attr( $value['id'] ) . '"
										type="' . esc_attr( $value['type'] ) . '"
										style="' . esc_attr( $value['css'] ) . '"
										value="' . esc_attr( $option_value ) . '"
										class="' . esc_attr( $value['class'] ) . '"
										placeholder="' . esc_attr( $value['placeholder'] ) . '"
										' . esc_attr( implode( ' ', $custom_attributes ) ) . ' ' . wp_kses_post( $description ) . '</td></tr>';
							break;

						// Color picker.
						case 'color':
							$option_value = self::get_option( $value['id'], $value['default'] );
							$settings .= '<tr valign="top" class="' . esc_attr( $value['row_class'] ) . '">';
							$settings .= '<th scope="row" class="titledesc">';
							$settings .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['title'] ) . '</label>';
							$settings .= wp_kses_post( $tooltip_html );
							$settings .= '</th>';
							$settings .= '<td class="forminp forminp-' . esc_attr( sanitize_title( $value['type'] ) ) . '">&lrm';
							$settings .= '<span class="colorpickpreview" style="background: ' . esc_attr( $option_value ) . '"></span>';
							$settings .= '<input
										name="' . esc_attr( $value['id'] ) . '"
										id="' . esc_attr( $value['id'] ) . '"
										type="text"
										dir="ltr"
										style="' . esc_attr( $value['css'] ) . '"
										value="' . esc_attr( $option_value ) . '"
										class="' . esc_attr( $value['class'] ) . 'colorpick"
										placeholder="' . esc_attr( $value['placeholder'] ) . '"
										' . esc_attr( implode( ' ', $custom_attributes ) ) . '/>&lrm;' . wp_kses_post( $description );
							$settings .= '<div id="colorPickerDiv_' . esc_attr( $value['id'] ) . '" class="colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"></div></td></tr>';
							break;

						// Textarea.
						case 'textarea':
							$option_value = self::get_option( $value['id'], $value['default'] );

							$settings .= '<tr valign="top" class="' . esc_attr( $value['row_class'] ) . '">';
							$settings .= '<th scope="row" class="titledesc">';
							$settings .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['title'] ) . '</label>';
							$settings .= wp_kses_post( $tooltip_html );
							$settings .= '</th>';
							$settings .= '<td class="forminp forminp-' . esc_attr( sanitize_title( $value['type'] ) ) . '">';
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
							$settings .= '</td></tr>';
							break;

						// Select boxes.
						case 'select':
						case 'multiselect':
							$option_value = self::get_option( $value['id'], $value['default'] );

							$settings .= '<tr valign="top" class="' . esc_attr( $value['row_class'] ) . '">';
							$settings .= '<th scope="row" class="titledesc">';
							$settings .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['title'] ) . '</label>';
							$settings .= wp_kses_post( $tooltip_html );
							$settings .= '</th>';
							$settings .= '<td class="forminp forminp-' . esc_attr( sanitize_title( $value['type'] ) ) . '">';

							$multiple = '';
							$type = '';
							if ( 'multiselect' == $value['type'] ) {
								$type = '[]';
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

							$settings .= '</select>' . wp_kses_post( $description ) . '</td></tr>';
							break;

						// Radio inputs.
						case 'radio':
							$option_value = self::get_option( $value['id'], $value['default'] );
							$settings .= '<tr valign="top" class="' . esc_attr( $value['row_class'] ) . '">';
							$settings .= '<th scope="row" class="titledesc">';
							$settings .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['title'] ) . '</label>';
							$settings .= wp_kses_post( $tooltip_html );
							$settings .= '</th>';
							$settings .= '<td class="forminp forminp-' . esc_attr( sanitize_title( $value['type'] ) ) . '">';
							$settings .= '<fieldset>';
							$settings .= wp_kses_post( $description );
							$settings .= '<ul>';

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
							$settings .= '</td>';
							$settings .= '</tr>';
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

							if ( ! isset( $value['checkboxgroup'] ) || 'start' === $value['checkboxgroup'] ) {
								$settings .= '<tr valign="top" class="' . esc_attr( implode( ' ', $visbility_class ) ) . ' ' . esc_attr( $value['row_class'] ) . '">';
								$settings .= '<th scope="row" class="titledesc">';
								$settings .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['title'] ) . '</label>';
								$settings .= wp_kses_post( $tooltip_html );
								$settings .= '</th><td class="forminp forminp-checkbox"><fieldset>';
							} else {
								$settings .= '<fieldset class="' . esc_attr( implode( ' ', $visbility_class ) ) . '">';
							}

							$settings .= '<label for="' . esc_attr( $value['id'] ) . '">';
							$settings .= '<input
										name="' . esc_attr( $value['id'] ) . '"
										id="' . esc_attr( $value['id'] ) . '"
										type="checkbox"
										class="' . esc_attr( isset( $value['class'] ) ? $value['class'] : '' ) . '"
										value="1"
										' . esc_attr( checked( $option_value, 'yes', false ) ) . '
										' . esc_attr( implode( ' ', $custom_attributes ) ) . '/>' . wp_kses_post( $description ) . '</label>';

							if ( ! isset( $value['checkboxgroup'] ) || 'end' === $value['checkboxgroup'] ) {
								$settings .= '</fieldset>';
								$settings .= wp_kses_post( $desc_field );
								$settings .= '</td></tr>';
							} else {
								$settings .= '</fieldset>';
								$settings .= wp_kses_post( $desc_field );
							}
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

							$settings .= '<tr valign="top" class="single_select_page ' . esc_attr( $value['row_class'] ) . '" ' . ( ( isset( $value['display'] ) && $value['display'] === 'none' ) ? 'style="display:none"' : '' ) . '>';
							$settings .= '<th scope="row" class="titledesc">' . esc_html( $value['title'] ) . ' ' . wp_kses_post( $tooltip_html );
							$settings .= '</th>';
							$settings .= '<td class="forminp">';
							$settings .= str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'user-registration' ) . "' style='" . esc_attr( $value['css'] ) . "' class='" . esc_attr( $value['class'] ) . "' id=", wp_dropdown_pages( $args ) );
							$settings .= wp_kses_post( $description );
							$settings .= '</td></tr>';
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

							$settings .= '<tr valign="top" class="' . esc_attr( $value['row_class'] ) . '">';
							$settings .= '<th scope="row" class="titledesc">';
							$settings .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_html( $value['title'] ) . '</label>';
							$settings .= wp_kses_post( $tooltip_html );
							$settings .= '</th>';
							$settings .= '<td class="forminp forminp-' . esc_attr( sanitize_title( $value['type'] ) ) . '">';
							$settings .= wp_kses_post( $description );

							// Output buffer for tinymce editor
							ob_start();
							wp_editor( $option_value, $value['id'], $editor_settings );
							$settings .= ob_get_clean();

							$settings .= '</td>';
							$settings .= '</tr>';
							break;

						case 'link':
							$settings .= '<tr valign="top" class="' . esc_attr( $value['row_class'] ) . '">';
							$settings .= '<th scope="row" class="titledesc">';
							$settings .= '<label for="' . esc_attr( $value['id'] ) . '">' . esc_attr( $value['title'] ) . '</label>';
							$settings .= wp_kses_post( $tooltip_html );
							$settings .= '</th>';
							$settings .= '<td>';

							if ( isset( $value['buttons'] ) && is_array( $value['buttons'] ) ) {
								foreach ( $value['buttons'] as $button ) {
									$settings .= '<a
													href="' . esc_url( $button['href'] ) . '"
													class="button ' . esc_attr( $button['class'] ) . '" style="' . esc_attr( $value['css'] ) . '">' . esc_html( $button['title'] ) . '</a>';
								}
							}

								$settings .= ( isset( $value['desc'] ) && isset( $value['desc_tip'] ) && true !== $value['desc_tip'] ) ? '<p class="description" >' . wp_kses_post( $value['desc'] ) . '</p>' : '';
								$settings .= '</td>';
								$settings .= '</tr>';
							break;

						// Default: run an action.
						default:
							$settings = apply_filters( 'user_registration_admin_field_' . $value['type'], $settings, $value );
							break;
					}// End switch case.
				}
				$settings .= '</table>';
				$settings .= '</div>';
				$settings .= '</div>';

				if ( ! empty( $section['id'] ) ) {
					do_action( 'user_registration_settings_' . sanitize_title( $section['id'] ) . '_after' );
				}
			}// End foreach.
		}
		echo $settings;
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
		$desc_field = '';

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
			$description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
		} elseif ( $description && in_array( $value['type'], array( 'checkbox' ) ) ) {
			$description = wp_kses_post( $description );
		} elseif ( $description ) {
			$description = '<span class="description">' . wp_kses_post( $description ) . '</span>';
		}

		if ( $desc_field && in_array( $value['type'], array( 'textarea', 'radio', 'checkbox' ) ) ) {
			$desc_field = '<p class="description">' . wp_kses_post( $desc_field ) . '</p>';
		} elseif ( $desc_field ) {
			$desc_field = '<span class="description">' . wp_kses_post( $desc_field ) . '</span>';
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
		if ( empty( $_POST ) ) {
			return false;
		}

		// Options to update will be stored here and saved later.
		$update_options = array();

		if ( empty( $options ) ) {
			return false;
		}

		// Loop options and get values to save.
		foreach ( $options['sections'] as $id => $section ) {
			if ( ! isset( $id ) || ! isset( $section['type'] ) ) {
				continue;
			}

			foreach ( $section['settings'] as $option ) {
				// Get posted value.
				if ( strstr( $option['id'], '[' ) ) {
					parse_str( $option['id'], $option_name_array );
					$option_name = sanitize_text_field( current( array_keys( $option_name_array ) ) );

					$setting_name = key( $option_name_array[ $option_name ] );
					$raw_value    = isset( $_POST[ $option_name ][ $setting_name ] ) ? wp_unslash( $_POST[ $option_name ][ $setting_name ] ) : null;
				} else {
					$option_name  = sanitize_text_field( $option['id'] );
					$setting_name = '';
					$raw_value    = isset( $_POST[ $option['id'] ] ) ? wp_unslash( $_POST[ $option['id'] ] ) : null;
				}

				// Format the value based on option type.
				switch ( $option['type'] ) {

					case 'checkbox':
						$value = '1' === $raw_value || 'yes' === $raw_value ? 'yes' : 'no';
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
				 * Sanitize the value of an option.
				 */
				$value = apply_filters( 'user_registration_admin_settings_sanitize_option', $value, $option, $raw_value );

				/**
				 * Sanitize the value of an option by option name.
				 */
				$value = apply_filters( "user_registration_admin_settings_sanitize_option_$option_name", $value, $option, $raw_value );

				if ( is_null( $value ) ) {
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
}
