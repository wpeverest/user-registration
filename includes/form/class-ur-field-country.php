<?php
/**
 * UserRegistration Admin.
 *
 * @class    UR_Field_Country
 * @version  1.0.0
 * @package  UserRegistration/Form
 * @category Admin
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UR_Field_Country Class
 */
class UR_Field_Country extends UR_Form_Field {

	private static $_instance;


	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}


	public function get_country() {

		return apply_filters( 'user_registration_countries_list', array(
			'AF' => __( 'Afghanistan', 'user-registration' ),
			'AX' => __( '&#197;land Islands', 'user-registration' ),
			'AL' => __( 'Albania', 'user-registration' ),
			'DZ' => __( 'Algeria', 'user-registration' ),
			'AS' => __( 'American Samoa', 'user-registration' ),
			'AD' => __( 'Andorra', 'user-registration' ),
			'AO' => __( 'Angola', 'user-registration' ),
			'AI' => __( 'Anguilla', 'user-registration' ),
			'AQ' => __( 'Antarctica', 'user-registration' ),
			'AG' => __( 'Antigua and Barbuda', 'user-registration' ),
			'AR' => __( 'Argentina', 'user-registration' ),
			'AM' => __( 'Armenia', 'user-registration' ),
			'AW' => __( 'Aruba', 'user-registration' ),
			'AU' => __( 'Australia', 'user-registration' ),
			'AT' => __( 'Austria', 'user-registration' ),
			'AZ' => __( 'Azerbaijan', 'user-registration' ),
			'BS' => __( 'Bahamas', 'user-registration' ),
			'BH' => __( 'Bahrain', 'user-registration' ),
			'BD' => __( 'Bangladesh', 'user-registration' ),
			'BB' => __( 'Barbados', 'user-registration' ),
			'BY' => __( 'Belarus', 'user-registration' ),
			'BE' => __( 'Belgium', 'user-registration' ),
			'PW' => __( 'Belau', 'user-registration' ),
			'BZ' => __( 'Belize', 'user-registration' ),
			'BJ' => __( 'Benin', 'user-registration' ),
			'BM' => __( 'Bermuda', 'user-registration' ),
			'BT' => __( 'Bhutan', 'user-registration' ),
			'BO' => __( 'Bolivia', 'user-registration' ),
			'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'user-registration' ),
			'BA' => __( 'Bosnia and Herzegovina', 'user-registration' ),
			'BW' => __( 'Botswana', 'user-registration' ),
			'BV' => __( 'Bouvet Island', 'user-registration' ),
			'BR' => __( 'Brazil', 'user-registration' ),
			'IO' => __( 'British Indian Ocean Territory', 'user-registration' ),
			'VG' => __( 'British Virgin Islands', 'user-registration' ),
			'BN' => __( 'Brunei', 'user-registration' ),
			'BG' => __( 'Bulgaria', 'user-registration' ),
			'BF' => __( 'Burkina Faso', 'user-registration' ),
			'BI' => __( 'Burundi', 'user-registration' ),
			'KH' => __( 'Cambodia', 'user-registration' ),
			'CM' => __( 'Cameroon', 'user-registration' ),
			'CA' => __( 'Canada', 'user-registration' ),
			'CV' => __( 'Cape Verde', 'user-registration' ),
			'KY' => __( 'Cayman Islands', 'user-registration' ),
			'CF' => __( 'Central African Republic', 'user-registration' ),
			'TD' => __( 'Chad', 'user-registration' ),
			'CL' => __( 'Chile', 'user-registration' ),
			'CN' => __( 'China', 'user-registration' ),
			'CX' => __( 'Christmas Island', 'user-registration' ),
			'CC' => __( 'Cocos (Keeling) Islands', 'user-registration' ),
			'CO' => __( 'Colombia', 'user-registration' ),
			'KM' => __( 'Comoros', 'user-registration' ),
			'CG' => __( 'Congo (Brazzaville)', 'user-registration' ),
			'CD' => __( 'Congo (Kinshasa)', 'user-registration' ),
			'CK' => __( 'Cook Islands', 'user-registration' ),
			'CR' => __( 'Costa Rica', 'user-registration' ),
			'HR' => __( 'Croatia', 'user-registration' ),
			'CU' => __( 'Cuba', 'user-registration' ),
			'CW' => __( 'Cura&ccedil;ao', 'user-registration' ),
			'CY' => __( 'Cyprus', 'user-registration' ),
			'CZ' => __( 'Czech Republic', 'user-registration' ),
			'DK' => __( 'Denmark', 'user-registration' ),
			'DJ' => __( 'Djibouti', 'user-registration' ),
			'DM' => __( 'Dominica', 'user-registration' ),
			'DO' => __( 'Dominican Republic', 'user-registration' ),
			'EC' => __( 'Ecuador', 'user-registration' ),
			'EG' => __( 'Egypt', 'user-registration' ),
			'SV' => __( 'El Salvador', 'user-registration' ),
			'GQ' => __( 'Equatorial Guinea', 'user-registration' ),
			'ER' => __( 'Eritrea', 'user-registration' ),
			'EE' => __( 'Estonia', 'user-registration' ),
			'ET' => __( 'Ethiopia', 'user-registration' ),
			'FK' => __( 'Falkland Islands', 'user-registration' ),
			'FO' => __( 'Faroe Islands', 'user-registration' ),
			'FJ' => __( 'Fiji', 'user-registration' ),
			'FI' => __( 'Finland', 'user-registration' ),
			'FR' => __( 'France', 'user-registration' ),
			'GF' => __( 'French Guiana', 'user-registration' ),
			'PF' => __( 'French Polynesia', 'user-registration' ),
			'TF' => __( 'French Southern Territories', 'user-registration' ),
			'GA' => __( 'Gabon', 'user-registration' ),
			'GM' => __( 'Gambia', 'user-registration' ),
			'GE' => __( 'Georgia', 'user-registration' ),
			'DE' => __( 'Germany', 'user-registration' ),
			'GH' => __( 'Ghana', 'user-registration' ),
			'GI' => __( 'Gibraltar', 'user-registration' ),
			'GR' => __( 'Greece', 'user-registration' ),
			'GL' => __( 'Greenland', 'user-registration' ),
			'GD' => __( 'Grenada', 'user-registration' ),
			'GP' => __( 'Guadeloupe', 'user-registration' ),
			'GU' => __( 'Guam', 'user-registration' ),
			'GT' => __( 'Guatemala', 'user-registration' ),
			'GG' => __( 'Guernsey', 'user-registration' ),
			'GN' => __( 'Guinea', 'user-registration' ),
			'GW' => __( 'Guinea-Bissau', 'user-registration' ),
			'GY' => __( 'Guyana', 'user-registration' ),
			'HT' => __( 'Haiti', 'user-registration' ),
			'HM' => __( 'Heard Island and McDonald Islands', 'user-registration' ),
			'HN' => __( 'Honduras', 'user-registration' ),
			'HK' => __( 'Hong Kong', 'user-registration' ),
			'HU' => __( 'Hungary', 'user-registration' ),
			'IS' => __( 'Iceland', 'user-registration' ),
			'IN' => __( 'India', 'user-registration' ),
			'ID' => __( 'Indonesia', 'user-registration' ),
			'IR' => __( 'Iran', 'user-registration' ),
			'IQ' => __( 'Iraq', 'user-registration' ),
			'IE' => __( 'Ireland', 'user-registration' ),
			'IM' => __( 'Isle of Man', 'user-registration' ),
			'IL' => __( 'Israel', 'user-registration' ),
			'IT' => __( 'Italy', 'user-registration' ),
			'CI' => __( 'Ivory Coast', 'user-registration' ),
			'JM' => __( 'Jamaica', 'user-registration' ),
			'JP' => __( 'Japan', 'user-registration' ),
			'JE' => __( 'Jersey', 'user-registration' ),
			'JO' => __( 'Jordan', 'user-registration' ),
			'KZ' => __( 'Kazakhstan', 'user-registration' ),
			'KE' => __( 'Kenya', 'user-registration' ),
			'KI' => __( 'Kiribati', 'user-registration' ),
			'KW' => __( 'Kuwait', 'user-registration' ),
			'KG' => __( 'Kyrgyzstan', 'user-registration' ),
			'LA' => __( 'Laos', 'user-registration' ),
			'LV' => __( 'Latvia', 'user-registration' ),
			'LB' => __( 'Lebanon', 'user-registration' ),
			'LS' => __( 'Lesotho', 'user-registration' ),
			'LR' => __( 'Liberia', 'user-registration' ),
			'LY' => __( 'Libya', 'user-registration' ),
			'LI' => __( 'Liechtenstein', 'user-registration' ),
			'LT' => __( 'Lithuania', 'user-registration' ),
			'LU' => __( 'Luxembourg', 'user-registration' ),
			'MO' => __( 'Macao S.A.R., China', 'user-registration' ),
			'MK' => __( 'Macedonia', 'user-registration' ),
			'MG' => __( 'Madagascar', 'user-registration' ),
			'MW' => __( 'Malawi', 'user-registration' ),
			'MY' => __( 'Malaysia', 'user-registration' ),
			'MV' => __( 'Maldives', 'user-registration' ),
			'ML' => __( 'Mali', 'user-registration' ),
			'MT' => __( 'Malta', 'user-registration' ),
			'MH' => __( 'Marshall Islands', 'user-registration' ),
			'MQ' => __( 'Martinique', 'user-registration' ),
			'MR' => __( 'Mauritania', 'user-registration' ),
			'MU' => __( 'Mauritius', 'user-registration' ),
			'YT' => __( 'Mayotte', 'user-registration' ),
			'MX' => __( 'Mexico', 'user-registration' ),
			'FM' => __( 'Micronesia', 'user-registration' ),
			'MD' => __( 'Moldova', 'user-registration' ),
			'MC' => __( 'Monaco', 'user-registration' ),
			'MN' => __( 'Mongolia', 'user-registration' ),
			'ME' => __( 'Montenegro', 'user-registration' ),
			'MS' => __( 'Montserrat', 'user-registration' ),
			'MA' => __( 'Morocco', 'user-registration' ),
			'MZ' => __( 'Mozambique', 'user-registration' ),
			'MM' => __( 'Myanmar', 'user-registration' ),
			'NA' => __( 'Namibia', 'user-registration' ),
			'NR' => __( 'Nauru', 'user-registration' ),
			'NP' => __( 'Nepal', 'user-registration' ),
			'NL' => __( 'Netherlands', 'user-registration' ),
			'NC' => __( 'New Caledonia', 'user-registration' ),
			'NZ' => __( 'New Zealand', 'user-registration' ),
			'NI' => __( 'Nicaragua', 'user-registration' ),
			'NE' => __( 'Niger', 'user-registration' ),
			'NG' => __( 'Nigeria', 'user-registration' ),
			'NU' => __( 'Niue', 'user-registration' ),
			'NF' => __( 'Norfolk Island', 'user-registration' ),
			'MP' => __( 'Northern Mariana Islands', 'user-registration' ),
			'KP' => __( 'North Korea', 'user-registration' ),
			'NO' => __( 'Norway', 'user-registration' ),
			'OM' => __( 'Oman', 'user-registration' ),
			'PK' => __( 'Pakistan', 'user-registration' ),
			'PS' => __( 'Palestinian Territory', 'user-registration' ),
			'PA' => __( 'Panama', 'user-registration' ),
			'PG' => __( 'Papua New Guinea', 'user-registration' ),
			'PY' => __( 'Paraguay', 'user-registration' ),
			'PE' => __( 'Peru', 'user-registration' ),
			'PH' => __( 'Philippines', 'user-registration' ),
			'PN' => __( 'Pitcairn', 'user-registration' ),
			'PL' => __( 'Poland', 'user-registration' ),
			'PT' => __( 'Portugal', 'user-registration' ),
			'PR' => __( 'Puerto Rico', 'user-registration' ),
			'QA' => __( 'Qatar', 'user-registration' ),
			'RE' => __( 'Reunion', 'user-registration' ),
			'RO' => __( 'Romania', 'user-registration' ),
			'RU' => __( 'Russia', 'user-registration' ),
			'RW' => __( 'Rwanda', 'user-registration' ),
			'BL' => __( 'Saint Barth&eacute;lemy', 'user-registration' ),
			'SH' => __( 'Saint Helena', 'user-registration' ),
			'KN' => __( 'Saint Kitts and Nevis', 'user-registration' ),
			'LC' => __( 'Saint Lucia', 'user-registration' ),
			'MF' => __( 'Saint Martin (French part)', 'user-registration' ),
			'SX' => __( 'Saint Martin (Dutch part)', 'user-registration' ),
			'PM' => __( 'Saint Pierre and Miquelon', 'user-registration' ),
			'VC' => __( 'Saint Vincent and the Grenadines', 'user-registration' ),
			'SM' => __( 'San Marino', 'user-registration' ),
			'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'user-registration' ),
			'SA' => __( 'Saudi Arabia', 'user-registration' ),
			'SN' => __( 'Senegal', 'user-registration' ),
			'RS' => __( 'Serbia', 'user-registration' ),
			'SC' => __( 'Seychelles', 'user-registration' ),
			'SL' => __( 'Sierra Leone', 'user-registration' ),
			'SG' => __( 'Singapore', 'user-registration' ),
			'SK' => __( 'Slovakia', 'user-registration' ),
			'SI' => __( 'Slovenia', 'user-registration' ),
			'SB' => __( 'Solomon Islands', 'user-registration' ),
			'SO' => __( 'Somalia', 'user-registration' ),
			'ZA' => __( 'South Africa', 'user-registration' ),
			'GS' => __( 'South Georgia/Sandwich Islands', 'user-registration' ),
			'KR' => __( 'South Korea', 'user-registration' ),
			'SS' => __( 'South Sudan', 'user-registration' ),
			'ES' => __( 'Spain', 'user-registration' ),
			'LK' => __( 'Sri Lanka', 'user-registration' ),
			'SD' => __( 'Sudan', 'user-registration' ),
			'SR' => __( 'Suriname', 'user-registration' ),
			'SJ' => __( 'Svalbard and Jan Mayen', 'user-registration' ),
			'SZ' => __( 'Swaziland', 'user-registration' ),
			'SE' => __( 'Sweden', 'user-registration' ),
			'CH' => __( 'Switzerland', 'user-registration' ),
			'SY' => __( 'Syria', 'user-registration' ),
			'TW' => __( 'Taiwan', 'user-registration' ),
			'TJ' => __( 'Tajikistan', 'user-registration' ),
			'TZ' => __( 'Tanzania', 'user-registration' ),
			'TH' => __( 'Thailand', 'user-registration' ),
			'TL' => __( 'Timor-Leste', 'user-registration' ),
			'TG' => __( 'Togo', 'user-registration' ),
			'TK' => __( 'Tokelau', 'user-registration' ),
			'TO' => __( 'Tonga', 'user-registration' ),
			'TT' => __( 'Trinidad and Tobago', 'user-registration' ),
			'TN' => __( 'Tunisia', 'user-registration' ),
			'TR' => __( 'Turkey', 'user-registration' ),
			'TM' => __( 'Turkmenistan', 'user-registration' ),
			'TC' => __( 'Turks and Caicos Islands', 'user-registration' ),
			'TV' => __( 'Tuvalu', 'user-registration' ),
			'UG' => __( 'Uganda', 'user-registration' ),
			'UA' => __( 'Ukraine', 'user-registration' ),
			'AE' => __( 'United Arab Emirates', 'user-registration' ),
			'GB' => __( 'United Kingdom (UK)', 'user-registration' ),
			'US' => __( 'United States (US)', 'user-registration' ),
			'UM' => __( 'United States (US) Minor Outlying Islands', 'user-registration' ),
			'VI' => __( 'United States (US) Virgin Islands', 'user-registration' ),
			'UY' => __( 'Uruguay', 'user-registration' ),
			'UZ' => __( 'Uzbekistan', 'user-registration' ),
			'VU' => __( 'Vanuatu', 'user-registration' ),
			'VA' => __( 'Vatican', 'user-registration' ),
			'VE' => __( 'Venezuela', 'user-registration' ),
			'VN' => __( 'Vietnam', 'user-registration' ),
			'WF' => __( 'Wallis and Futuna', 'user-registration' ),
			'EH' => __( 'Western Sahara', 'user-registration' ),
			'WS' => __( 'Samoa', 'user-registration' ),
			'YE' => __( 'Yemen', 'user-registration' ),
			'ZM' => __( 'Zambia', 'user-registration' ),
			'ZW' => __( 'Zimbabwe', 'user-registration' ),
		) );
	}

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		$this->id = 'user_registration_country';

		$this->form_id = 1;

		$this->registered_fields_config = array(

			'label' => __( 'Country','user-registration' ),

			'icon' => 'dashicons dashicons-admin-site',
		);

		$this->field_defaults = array(

			'default_label' => __( 'Country','user-registration' ),

			'default_field_name' => 'country_' . ur_get_random_number(),
		);
	}


	public function get_registered_admin_fields() {

		return '<li id="' . $this->id . '_list "

				class="ur-registered-item draggable"

                data-field-id="' . $this->id . '"><span class="' . $this->registered_fields_config['icon'] . '"></span>' . $this->registered_fields_config['label'] . '</li>';
	}


	public function validation( $single_form_field, $form_data, $filter_hook, $form_id ) {
		// TODO: Implement validation() method.
	}
}

return UR_Field_Country::get_instance();
