<?php
/**
 * Smart tag functionality.
 *
 * @package  UserRegistration/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Smart Tag Class.
 */
class UR_Smart_Tags {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'user_registration_process_smart_tags', array( $this, 'process' ), 10, 3 );
	}

	/**
	 * List of smart tags.
	 *
	 * @return array array of smart tags.
	 */
	public static function smart_tags_list() {
		$smart_tags = array(
			'{{user_id}}'     => __( 'User ID', 'user-registration' ),
			'{{username}}'    => __( 'User Name', 'user-registration' ),
			'{{email}}'       => __( 'Email', 'user-registration' ),
			'{{email_token}}' => __( 'Email Token', 'user-registration' ),
			'{{blog_info}}'   => __( 'Blog Info', 'user-registration' ),
			'{{home_url}}'    => __( 'Home URL', 'user-registration' ),
			'{{ur_login}}'    => __( 'UR Login', 'user-registration' ),
			'{{key}}'         => __( 'Key', 'user-registration' ),
			'{{all_fields}}'  => __( 'All Fields', 'user-registration' ),
			'{{auto_pass}}'   => __( 'Auto Pass', 'user-registration' ),
			'{{user_roles}}'  => __( 'User Roles', 'user-registration' ),
		);
		return apply_filters( 'user_registration_smart_tags', $smart_tags );
	}
	/**
	 * Process and parse smart tags.
	 *
	 * @param string $content Contents.
	 * @param array  $values Data values.
	 * @param array  $name_value  Extra values.
	 */
	public function process( $content = '', $values = array(), $name_value = array() ) {
		if ( ! empty( $values['email'] ) ) {
			$default_values = array();
			$default_values = apply_filters( 'user_registration_add_smart_tags', $default_values, $values['email'] );

			$values    = wp_parse_args( $values, $default_values );
			$user_data = UR_Emailer::user_data_smart_tags( $values['email'] );
			if ( is_array( $name_value ) && ! empty( $name_value ) ) {
				$user_data = array_merge( $user_data, $name_value );
			}

			$values = array_merge( $values, $user_data );
			array_walk(
				$values,
				function( &$value, $key ) {
					if ( 'user_pass' === $key ) {
						$value = esc_html__( 'Chosen Password', 'user-registration' );
					}
				}
			);

			$user_smart_tags = array_keys( $user_data );
			array_walk(
				$user_smart_tags,
				function( &$value ) {
					$value = '{{' . trim( $value, '{}' ) . '}}';
				}
			);
			$smart_tags = $user_smart_tags;
			foreach ( $values as $key => $value ) {
				$value = ur_format_field_values( $key, $value );
				if ( ! is_array( $value ) ) {
					$content = str_replace( '{{' . $key . '}}', $value, $content );
				}
			}
		}

		preg_match_all( '/\{\{(.+?)\}\}/', $content, $other_tags );
		if ( ! empty( $other_tags[1] ) ) {
			foreach ( $other_tags[1] as $key => $tag ) {
				$other_tag = explode( ' ', $tag )[0];
				switch ( $other_tag ) {
					case 'user_id':
						$user_id = ! empty( $values['user_id'] ) ? $values['user_id'] : get_current_user_id();
						$content = str_replace( '{{' . $other_tag . '}}', $user_id, $content );
						break;
					case 'username':
						if ( is_user_logged_in() ) {
							$user = wp_get_current_user();
							$name = sanitize_text_field( $user->user_login );
						} else {
							$name = '';
						}
						$content = str_replace( '{{' . $other_tag . '}}', $name, $content );
						break;
					case 'ur_login':
						$ur_account_page_exists   = ur_get_page_id( 'myaccount' ) > 0;
						$ur_login_or_account_page = ur_get_page_permalink( 'myaccount' );

						if ( ! $ur_account_page_exists ) {
							$ur_login_or_account_page = ur_get_page_permalink( 'login' );
						}

						$ur_login = ( get_home_url() !== $ur_login_or_account_page ) ? $ur_login_or_account_page : wp_login_url();
						$ur_login = str_replace( get_home_url() . '/', '', $ur_login );
						$content  = str_replace( '{{' . $other_tag . '}}', $ur_login, $content );
						break;
					case 'auto_pass':
						$user_pass = apply_filters( 'user_registration_auto_generated_password', 'user_pass' );
						$content   = str_replace( '{{' . $other_tag . '}}', $user_pass, $content );
						break;
					case 'user_roles':
						if ( ! empty( $values['user_id'] ) ) {
							$user_id    = $values['user_id'];
							$user_roles = ur_get_user_roles( $user_id )[0];
						} elseif ( is_user_logged_in() && empty( $values['user_id'] ) ) {
							$user_id    = get_current_user_id();
							$user_roles = ur_get_user_roles( $user_id )[0];
						} else {
							$user_roles = '';
						}
						$content = str_replace( '{{' . $other_tag . '}}', $user_roles, $content );
						break;
					case 'blog_info':
						$blog_info = get_bloginfo();
						$content   = str_replace( '{{' . $other_tag . '}}', $blog_info, $content );
						break;
					case 'home_url':
						$home_url = get_home_url();
						$content  = str_replace( '{{' . $other_tag . '}}', $home_url, $content );
						break;
					case 'email':
						if ( ! empty( $values['email'] ) ) {
							$email = $values['email'];
						} elseif ( is_user_logged_in() && empty( $values['email'] ) ) {
							$user  = wp_get_current_user();
							$email = sanitize_text_field( $user->user_email );
						} else {
							$email = '';
						}
						$content = str_replace( '{{' . $other_tag . '}}', $email, $content );
						break;
					case 'email_token':
						if ( ! empty( $values['email_token'] ) ) {
							$email_token = $values['email_token'];
						} else {
							$email_token = '';
						}
						$content = str_replace( '{{' . $other_tag . '}}', $email_token, $content );
						break;
					case 'key':
						if ( ! empty( $values['key'] ) ) {
							$key = $values['key'];
						} else {
							$key = '';
						}
						$content = str_replace( '{{' . $other_tag . '}}', $key, $content );
						break;
					case 'all_fields':
						if ( ! empty( $values['all_fields'] ) ) {
							$all_fields = $values['all_fields'];
						} else {
							$all_fields = '';
						}
						$content = str_replace( '{{' . $other_tag . '}}', $all_fields, $content );
						break;
				}
			}
		}
		return $content;
	}
}

new UR_Smart_Tags();
