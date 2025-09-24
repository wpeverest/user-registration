<?php
/**
 * UR_Setting_Date Class.
 *
 * @package  UserRegistration/Form/Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Setting_Date Class.
 */
class UR_Setting_Date extends UR_Field_Settings {

	/**
	 * UR_Setting_Date Class Contructor.
	 */
	public function __construct() {
		$this->field_id = 'date_advance_setting';
	}

	/**
	 * Output Fields.
	 *
	 * @param array $field_data Render field data in html.
	 * @return string $field_data Field Data.
	 */
	public function output( $field_data = array() ) {
		$this->field_data = $field_data;
		$this->register_fields();
		$field_html = $this->fields_html;
		return $field_html;
	}

	/**
	 * Advance Fields.
	 */
	public function register_fields() {
		$fields = array(
			'custom_class'      => array(
				'label'       => __( 'Custom Class', 'user-registration' ),
				'data-id'     => $this->field_id . '_custom_class',
				'name'        => $this->field_id . '[custom_class]',
				'class'       => $this->default_class . ' ur-settings-custom-class',
				'type'        => 'text',
				'required'    => false,
				'default'     => '',
				'placeholder' => __( 'Custom Class', 'user-registration' ),
				'tip'         => __( 'Add a CSS class for custom styling.', 'user-registration' ),
			),

			'date_format'       => array(
				'type'        => 'select',
				'data-id'     => $this->field_id . '_date_format',
				'label'       => __( 'Date Format', 'user-registration' ),
				'name'        => $this->field_id . '[date_format]',
				'class'       => $this->default_class . ' ur-settings-date-format',
				'placeholder' => '',
				'default'     => 'Y-m-d',
				'required'    => false,
				'options'     => array(
					'Y-m-d'  => date_i18n( 'Y-m-d' ) . ' (Y-m-d)',
					'F j, Y' => date_i18n( 'F j, Y' ) . ' (F j, Y)',
					'd/m/Y'  => date_i18n( 'd/m/Y' ) . ' (d/m/Y)',
				),
				'tip'         => __( 'Which format do you want to use to show date.', 'user-registration' ),
			),

			'enable_min_max'    => array(
				'type'     => 'toggle',
				'data-id'  => $this->field_id . '_enable_min_max',
				'label'    => __( 'Enable Minimum and Maximum Date', 'user-registration' ),
				'name'     => $this->field_id . '[enable_min_max]',
				'class'    => $this->default_class . ' ur-settings-enable-min-max',
				'default'  => 'false',
				'required' => false,
				'tip'      => __( 'Turn this on to set minimum and maximum dates for this field.', 'user-registration' ),
			),

			'min_date'          => array(
				'label'    => __( 'Minimum Date', 'user-registration' ),
				'data-id'  => $this->field_id . '_min_date',
				'name'     => $this->field_id . '[min_date]',
				'class'    => $this->default_class . ' ur-settings-min-date',
				'type'     => 'text',
				'required' => false,
				'default'  => '',
				'tip'      => __( 'Users won’t be able to pick a date before this limit.', 'user-registration' ),
			),

			'max_date'          => array(
				'label'    => __( 'Maximum Date', 'user-registration' ),
				'data-id'  => $this->field_id . '_max_date',
				'name'     => $this->field_id . '[max_date]',
				'class'    => $this->default_class . ' ur-settings-max-date',
				'type'     => 'text',
				'required' => false,
				'default'  => '',
				'tip'      => __( 'Users won’t be able to pick a date beyond this limit.', 'user-registration' ),
			),

			'set_current_date'  => array(
				'type'     => 'toggle',
				'data-id'  => $this->field_id . '_set_current_date',
				'label'    => __( 'Set Current Date as Default Date', 'user-registration' ),
				'name'     => $this->field_id . '[set_current_date]',
				'class'    => $this->default_class . ' ur-settings-set-current-date',
				'default'  => 'false',
				'required' => false,
				'tip'      => __( 'Set current date as the default value for this field.', 'user-registration' ),
			),

			'enable_date_range' => array(
				'type'     => 'toggle',
				'data-id'  => $this->field_id . '_enable_date_range',
				'label'    => __( 'Enable Date Range', 'user-registration' ),
				'name'     => $this->field_id . '[enable_date_range]',
				'class'    => $this->default_class . ' ur-settings-enable-date-range',
				'default'  => 'false',
				'required' => false,
				'tip'      => __( 'Turn this on to allow users to select a range of dates.', 'user-registration' ),
			),

			'date_localization' => array(
				'type'     => 'select',
				'data-id'  => $this->field_id . '_date_localization',
				'label'    => __( 'Date Format Localization', 'user-registration' ),
				'name'     => $this->field_id . '[date_localization]',
				'class'    => $this->default_class . ' ur-settings-date_localization',
				'default'  => 'en',
				'required' => false,
				'options'  => array(
					'en'    => 'English',
					'ar'    => 'Arabic',
					'at'    => 'Austria',
					'az'    => 'Azerbaijan',
					'be'    => 'Belarusian',
					'bg'    => 'Bulgarian',
					'bn'    => 'Bangla',
					'bs'    => 'Bosnian',
					'cat'   => 'Catalan',
					'cs'    => 'Czech',
					'cy'    => 'Welsh',
					'da'    => 'Danish',
					'de'    => 'German',
					'eo'    => 'Esperanto',
					'es'    => 'Spanish',
					'et'    => 'Estonian',
					'fa'    => 'Persian',
					'fi'    => 'Finnish',
					'fo'    => 'Faroese',
					'fr'    => 'French',
					'ga'    => 'Irish',
					'gr'    => 'Greek',
					'he'    => 'Hebrew',
					'hi'    => 'Hindi',
					'hr'    => 'Croatian',
					'hu'    => 'Hungarian',
					'id'    => 'Indonesian',
					'is'    => 'Icelandic',
					'it'    => 'Italian',
					'ja'    => 'Japanese',
					'ka'    => 'Georgian',
					'ko'    => 'Korean',
					'km'    => 'Khmer',
					'kz'    => 'Kazakh',
					'lt'    => 'Lithuanian',
					'lv'    => 'Latvian',
					'mk'    => 'Macedonian',
					'mn'    => 'Mongolian',
					'ms'    => 'Malaysian',
					'my'    => 'Burmese',
					'nl'    => 'Dutch',
					'no'    => 'Norwegian',
					'pa'    => 'Punjabi',
					'pl'    => 'Polish',
					'pt'    => 'Portuguese',
					'ro'    => 'Romanian',
					'ru'    => 'Russian',
					'si'    => 'Sinhala',
					'sk'    => 'Slovak',
					'sl'    => 'Slovenian',
					'sq'    => 'Albanian',
					'sr'    => 'Serbian',
					'sv'    => 'Swedish',
					'th'    => 'Thai',
					'tr'    => 'Turkish',
					'uk'    => 'Ukrainian',
					'vn'    => 'Vietnamese',
					'zh'    => 'Mandarin',
					'zh_tw' => 'MandarinTraditional',
				),
				'tip'      => __( 'Choose how dates are displayed based on the user’s location or language.', 'user-registration' ),
			),
		);
			/**
			 * Filter to modify the date custom advance settings.
			 *
			 * @param $fields Custom fields for date advance settings.
			 * @param int field_id Id of the fields to be added.
			 * @param string default_class Default class for the field.
			 * @return string $fields Custom fields for the advance settings.
			 */
			$fields = apply_filters( 'date_custom_advance_settings', $fields, $this->field_id, $this->default_class );
			$this->render_html( $fields );
	}
}

return new UR_Setting_Date();
