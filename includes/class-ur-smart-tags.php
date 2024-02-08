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
		add_filter( 'ur_smart_tags_list_in_general', array( $this, 'select_smart_tags_in_general' ), 10, 1 );
		add_filter( 'ur_pattern_validation_list_in_advanced_settings', array( $this, 'select_pattern_validation' ), 10, 1 );
	}

	/**
	 * List of smart tags.
	 *
	 * @return array array of smart tags.
	 */
	public static function smart_tags_list() {
		$smart_tags = array_merge( self::ur_unauthenticated_parsable_smart_tags_list(), self::ur_authenticated_parsable_smart_tags_list() );
		/**
		 * The 'user_registration_smart_tags' filter allows developers to modify the list
		 * of smart tags available in User Registration for customization.
		 *
		 * @param array  $smart_tags List of smart tags before applying the filter.
		 */
		return apply_filters( 'user_registration_smart_tags', $smart_tags );
	}

	/**
	 * List of smart tags which can only be parsed when user is logged in.
	 *
	 * @return array array of smart tags.
	 */
	public static function ur_authenticated_parsable_smart_tags_list() {
		$smart_tags = array(
			'{{user_id}}'      => esc_html__( 'User ID', 'user-registration' ),
			'{{username}}'     => esc_html__( 'User Name', 'user-registration' ),
			'{{email}}'        => esc_html__( 'Email', 'user-registration' ),
			'{{ur_login}}'     => esc_html__( 'UR Login', 'user-registration' ),
			'{{all_fields}}'   => esc_html__( 'All Fields', 'user-registration' ),
			'{{auto_pass}}'    => esc_html__( 'Auto Pass', 'user-registration' ),
			'{{user_roles}}'   => esc_html__( 'User Roles', 'user-registration' ),
			'{{first_name}}'   => esc_html__( 'First Name', 'user-registration' ),
			'{{last_name}}'    => esc_html__( 'Last Name', 'user-registration' ),
			'{{display_name}}' => esc_html__( 'User Display Name', 'user-registration' ),
		);
		/**
		 * The 'user_registration_authenticated_smart_tags' filter allows developers to modify
		 * the list of smart tags available for authenticated users in User Registration.
		 *
		 * @param array $smart_tags Default list of authenticated smart tags.
		 */
		return apply_filters( 'user_registration_authenticated_smart_tags', $smart_tags );
	}

	/**
	 * List of smart tags which can be parsed before user is logged in.
	 *
	 * @return array array of smart tags.
	 */
	public static function ur_unauthenticated_parsable_smart_tags_list() {
		$smart_tags = array(
			'{{blog_info}}'        => esc_html__( 'Blog Info', 'user-registration' ),
			'{{home_url}}'         => esc_html__( 'Home URL', 'user-registration' ),
			'{{admin_email}}'      => esc_html__( 'Site Admin Email', 'user-registration' ),
			'{{site_name}}'        => esc_html__( 'Site Name', 'user-registration' ),
			'{{site_url}}'         => esc_html__( 'Site URL', 'user-registration' ),
			'{{page_title}}'       => esc_html__( 'Page Title', 'user-registration' ),
			'{{page_url}}'         => esc_html__( 'Page URL', 'user-registration' ),
			'{{page_id}}'          => esc_html__( 'Page ID', 'user-registration' ),
			'{{post_title}}'       => esc_html__( 'Post Title', 'user-registration' ),
			'{{current_date}}'     => esc_html__( 'Current Date', 'user-registration' ),
			'{{current_time}}'     => esc_html__( 'Current Time', 'user-registration' ),
			'{{current_language}}' => esc_html__( 'Current Language', 'user-registration' ),
			'{{email_token}}'      => esc_html__( 'Email Token', 'user-registration' ),
			'{{key}}'              => esc_html__( 'Key', 'user-registration' ),
			'{{user_ip_address}}'  => esc_html__( 'User IP Address', 'user-registration' ),
			'{{referrer_url}}'     => esc_html__( 'Referrer URL', 'user-registration' ),
			'{{form_id}}'          => esc_html__( 'Form ID', 'user-registration' ),
			'{{form_name}}'        => esc_html__( 'Form Name', 'user-registration' ),
			'{{author_email}}'     => esc_html__( 'Author Email', 'user-registration' ),
			'{{author_name}}'      => esc_html__( 'Author Name', 'user-registration' ),
			'{{unique_id}}'        => esc_html__( 'Unique ID', 'user-registration' ),
		);
		/**
		 * Applies a filter to modify the list of unauthenticated smart tags.
		 *
		 * The 'user_registration_unauthenticated_smart_tags' filter allows developers to customize
		 * the list of smart tags available for unauthenticated users in User Registration.
		 *
		 * @param array $smart_tags Default list of unauthenticated smart tags.
		 */
		return apply_filters( 'user_registration_unauthenticated_smart_tags', $smart_tags );
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
			$process_type   = isset( $values['process_type'] ) && 'ur_parse_after_meta_update' === $values['process_type'] ? true : false;
			$default_values = array();
			/**
			 * Applies a filter to add or modify smart tags for User Registration.
			 *
			 * The 'user_registration_add_smart_tags' filter allows developers to customize
			 * the default values by adding or modifying smart tags based on the provided email.
			 *
			 * @param array  $default_values Default values before adding or modifying smart tags.
			 * @param string $email          Email address associated with the values.
			 */
			$default_values = apply_filters( 'user_registration_add_smart_tags', $default_values, $values['email'] );

			$values    = wp_parse_args( $values, $default_values );
			$user_data = UR_Emailer::user_data_smart_tags( $values['email'] );
			if ( is_array( $name_value ) && ! empty( $name_value ) ) {
				$user_data = array_merge( $user_data, $name_value );
			}

			$values = array_merge( $values, $user_data );
			array_walk(
				$values,
				function ( &$value, $key ) {
					if ( 'user_pass' === $key ) {
						$value = esc_html__( 'Chosen Password', 'user-registration' );
					}
				}
			);

			$user_smart_tags = array_keys( $user_data );
			array_walk(
				$user_smart_tags,
				function ( &$value ) {
					$value = '{{' . trim( $value, '{}' ) . '}}';
				}
			);
			$smart_tags = $user_smart_tags;
			/**
			 * Applies a filter to modify smart tag values.
			 *
			 * The 'user_registration_smart_tag_values' filter allows developers to customize
			 * the values associated with smart tags before processing them in User Registration.
			 *
			 * @param array $values Default smart tag values.
			 */
			$values = apply_filters( 'user_registration_smart_tag_values', $values );

			foreach ( $values as $key => $value ) {
				$value = ur_format_field_values( $key, $value );
				if ( ! is_array( $value ) ) {
					if ( 'profile_pic_url' === $key && $process_type ) {
						$content = str_replace( '{{' . $key . '}}', '', $content );
						continue;
					}

					$content = str_replace( '{{' . $key . '}}', $value, $content );
				}
			}
		}

		preg_match_all( '/\{\{(.+?)\}\}/', $content, $other_tags );
		if ( ! empty( $other_tags[1] ) ) {
			foreach ( $other_tags[1] as $key => $tag ) {
				$other_tag = explode( ' ', $tag )[0];

				switch ( $other_tag ) {
					case 'updated_new_user_email':
						if ( ! empty( $values['user_pending_email'] ) ) {
							$new_email = $values['user_pending_email'];
							$content   = str_replace( '{{' . $other_tag . '}}', $new_email, $content );
						}
						break;

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
						/**
						 * Applies a filter to customize the auto-generated password.
						 *
						 * @param string $default_password Default auto-generated password.
						 */
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

					case 'admin_email':
						$admin_email = sanitize_email( get_option( 'admin_email' ) );
						$content     = str_replace( '{{' . $other_tag . '}}', $admin_email, $content );
						break;

					case 'site_name':
						$site_name = get_option( 'blogname' );
						$content   = str_replace( '{{' . $other_tag . '}}', $site_name, $content );
						break;

					case 'site_url':
						$site_url = get_option( 'siteurl' );
						$content  = str_replace( '{{' . $other_tag . '}}', $site_url, $content );
						break;

					case 'page_title':
						$page_title = get_the_title( get_the_ID() );
						$content    = str_replace( '{{' . $other_tag . '}}', $page_title, $content );
						break;

					case 'page_url':
						$page_url = get_permalink( get_the_ID() );
						$content  = str_replace( '{{' . $other_tag . '}}', $page_url, $content );
						break;

					case 'page_id':
						$page_id = get_the_ID();
						$content = str_replace( '{{' . $other_tag . '}}', $page_id, $content );
						break;

					case 'form_id':
						if ( is_user_logged_in() && empty( $values['form_id'] ) ) {
							$user_id = get_current_user_id();
							$form_id = ur_get_form_id_by_userid( $user_id );
						} else {
							$form_id = $values['form_id'];
						}

						$content = str_replace( '{{' . $other_tag . '}}', $form_id, $content );
						break;

					case 'form_name':
						if ( isset( $values['form_id'] ) ) {
							$form_name = ucfirst( get_the_title( $values['form_id'] ) );
							$content   = str_replace( '{{' . $other_tag . '}}', $form_name, $content );
						}
						break;

					case 'user_ip_address':
						$user_ip_add = ur_get_ip_address();
						$content     = str_replace( '{{' . $other_tag . '}}', $user_ip_add, $content );
						break;

					case 'referrer_url':
						$referer = ! empty( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : ''; // @codingStandardsIgnoreLine
						$content = str_replace( '{{' . $other_tag . '}}', sanitize_text_field( $referer ), $content );
						break;

					case 'current_date':
						$current_date = date_i18n( get_option( 'date_format' ) );
						$content      = str_replace( '{{' . $other_tag . '}}', sanitize_text_field( $current_date ), $content );
						break;

					case 'current_time':
						$current_time = date_i18n( get_option( 'time_format' ) );
						$content      = str_replace( '{{' . $other_tag . '}}', sanitize_text_field( $current_time ), $content );
						break;

					case 'current_language':
						$current_language = ur_get_current_language();
						$content          = str_replace( '{{' . $other_tag . '}}', sanitize_text_field( $current_language ), $content );
						break;

					case 'post_title':
						$post_title = get_the_title();
						$content    = str_replace( '{{' . $other_tag . '}}', sanitize_text_field( $post_title ), $content );
						break;

					case 'post_meta':
						preg_match_all( '/key\=(.*?)$/', $tag, $meta );
						if ( is_array( $meta ) && ! empty( $meta[1][0] ) ) {
							$key     = $meta[1][0];
							$value   = get_post_meta( get_the_ID(), $key, true );
							$content = str_replace( '{' . $tag . '}', wp_kses_post( $value ), $content );
						} else {
							$content = str_replace( '{' . $tag . '}', '', $content );
						}
						break;

					case 'author_email':
						$author  = get_the_author_meta( 'user_email' );
						$content = str_replace( '{{' . $other_tag . '}}', sanitize_text_field( $author ), $content );
						break;

					case 'author_name':
						$author  = get_the_author_meta( 'display_name' );
						$content = str_replace( '{{' . $other_tag . '}}', sanitize_text_field( $author ), $content );
						break;
					case 'unique_id':
						/**
						 * Applies a filter to determine whether more entropy should be added to the unique ID.
						 *
						 * The 'ur_unique_id_more_entropy' filter allows developers to customize
						 * whether additional entropy is included in the unique ID.
						 *
						 * @param bool $default_entropy Default value indicating whether more entropy is added.
						 */
						$uni_entropy = apply_filters( 'ur_unique_id_more_entropy', true );
						/**
						 * Applies a filter to customize the prefix for the unique ID.
						 *
						 * The 'ur_unique_id_prefix' filter allows developers to modify the default prefix used
						 * for the unique ID.
						 *
						 * @param string $default_prefix Default prefix for the unique ID.
						 */
						$prefix    = apply_filters( 'ur_unique_id_prefix', 'ur' );
						$unique_id = uniqid( $prefix, $uni_entropy );
						$content   = str_replace( '{{' . $tag . '}}', $unique_id, $content );
						break;
					case 'approval_link':
						if ( isset( $values['email'] ) && '' !== $values['email'] ) {
							$user    = get_user_by( 'email', $values['email'] );
							$user_id = $user->ID;

							$login_option = ur_get_user_login_option( $user_id );

							// If enabled approval via email setting.
							if ( ( 'admin_approval' === $login_option || 'admin_approval_after_email_confirmation' === $login_option ) ) {
								$approval_token = get_user_meta( $user_id, 'ur_confirm_approval_token', true );
								$approval_link  = '<a href="' . admin_url( '/' ) . '?ur_approval_token=' . $approval_token . '">' . esc_html__( 'Approve Now', 'user-registration' ) . '</a><br />';
								$content        = str_replace( '{{' . $tag . '}}', $approval_link, $content );
							}
						}
						break;

					case 'email_change_confirmation_link':
						// Generate a confirmation key for the email change.
						$confirm_key = wp_generate_password( 20, false );

						$user = get_current_user_id();

						// Save the confirmation key.
						update_user_meta( $user, 'user_registration_email_confirm_key', $confirm_key );

						// Send an email to the new address with confirmation link.
						$confirm_link = add_query_arg( 'confirm_email', $user, add_query_arg( 'confirm_key', $confirm_key, ur_get_my_account_url() . get_option( 'user_registration_myaccount_edit_profile_endpoint', 'edit-profile' ) ) );
						$confirm_link = sprintf( '<a href="%s" target="_blank">%s</a>', $confirm_link, esc_html__( 'confirm link', 'user-registration' ) );

						$content = str_replace( '{{' . $tag . '}}', $confirm_link, $content );
						break;

					case 'denial_link':
						if ( isset( $values['email'] ) && '' !== $values['email'] ) {
							$user    = get_user_by( 'email', $values['email'] );
							$user_id = $user->ID;

							$login_option = ur_get_user_login_option( $user_id );

							// If enabled approval via email setting.
							if ( ( 'admin_approval' === $login_option || 'admin_approval_after_email_confirmation' === $login_option ) ) {
								$denial_token = get_user_meta( $user_id, 'ur_confirm_denial_token', true );
								$denial_link  = '<a href="' . admin_url( '/' ) . '?ur_denial_token=' . $denial_token . '">' . esc_html__( 'Deny Now', 'user-registration' ) . '</a><br />';
								$content      = str_replace( '{{' . $tag . '}}', $denial_link, $content );
							}
						}
						break;
					case 'display_name':
						$user_id   = ! empty( $values['user_id'] ) ? $values['user_id'] : get_current_user_id();
						$user_data = get_userdata( $user_id );
						$content   = str_replace( '{{' . $tag . '}}', esc_html( $user_data->display_name ), $content );
						break;

					case 'profile_pic_box':
						$gravatar_image      = get_avatar_url( get_current_user_id(), $args = null );
						$profile_picture_url = get_user_meta( get_current_user_id(), 'user_registration_profile_pic_url', true );

						if ( is_numeric( $profile_picture_url ) ) {
							$profile_picture_url = wp_get_attachment_url( $profile_picture_url );
						}

						$image           = ( ! empty( $profile_picture_url ) ) ? $profile_picture_url : $gravatar_image;
						$profile_pic_box = '<img class="profile-preview" alt="profile-picture" src="' . esc_url( $image ) . '" />';
						$content         = str_replace( '{{' . $tag . '}}', wp_kses_post( $profile_pic_box ), $content );
						break;
					case 'full_name':
						$first_name = ucfirst( get_user_meta( get_current_user_id(), 'first_name', true ) );
						$last_name  = ucfirst( get_user_meta( get_current_user_id(), 'last_name', true ) );
						$full_name  = $first_name . ' ' . $last_name;
						if ( empty( $first_name ) && empty( $last_name ) ) {
							$full_name = get_userdata( get_current_user_id() )->display_name;
						}
						$content = str_replace( '{{' . $tag . '}}', esc_html( $full_name ), $content );
						break;
					case 'profile_details_link':
						$profile_details_link = '<a href="' . esc_url( ur_get_endpoint_url( 'edit-profile' ) ) . '">' . esc_html__( 'profile details', 'user-registration' ) . '</a>';
						$content              = str_replace( '{{' . $tag . '}}', wp_kses_post( $profile_details_link ), $content );
						break;
					case 'edit_password_link':
						$edit_password_link = '<a href="' . esc_url( ur_get_endpoint_url( 'edit-password' ) ) . '">' . esc_html__( 'edit your password', 'user-registration' ) . '</a>';
						$content            = str_replace( '{{' . $tag . '}}', wp_kses_post( $edit_password_link ), $content );
						break;
					case 'sign_out_link':
						$sign_out_link = '<a href="' . esc_url( ur_logout_url( ur_get_page_permalink( 'myaccount' ) ) ) . '">' . esc_html__( 'Sign out', 'user-registration' ) . '</a>';
						$content       = str_replace( '{{' . $tag . '}}', wp_kses_post( $sign_out_link ), $content );
						break;
					case 'passwordless_login_link':
						$passwordless_login_link = isset( $values['passwordless_login_link'] ) ? '<a href="' . esc_url( $values['passwordless_login_link'] ) . '"></a>' : '';
						$content                 = str_replace( '{{' . $tag . '}}', wp_kses_post( $passwordless_login_link ), $content );
						break;

				}
			}
		}
		/**
		 * Applies a filter to customize the content with smart tags.
		 *
		 * The 'user_registration_smart_tag_content' filter allows developers to modify
		 * the content that includes smart tags based on the provided values.
		 *
		 * @param string $content Default content with smart tags.
		 * @param array  $values  Values associated with the smart tags.
		 */
		$content = apply_filters( 'user_registration_smart_tag_content', $content, $values );

		return $content;
	}

	/**
	 * Smart tag list button in general setting and advanced settin of field.
	 *
	 * @param string $smart_tags list of smart tags.
	 */
	public function select_smart_tags_in_general( $smart_tags ) {
		$smart_tags_list = self::ur_unauthenticated_parsable_smart_tags_list();
		$smart_tags     .= '<a href="#" class="button ur-smart-tags-list-button"><span class="dashicons dashicons-editor-code"></span></a>';
		$smart_tags     .= '<div class="ur-smart-tags-list" style="display: none">';
		$smart_tags     .= '<div class="smart-tag-title ur-smart-tag-title">Smart Tags</div><ul class="ur-smart-tags">';
		foreach ( $smart_tags_list as $key => $value ) {
			$smart_tags .= "<li class='ur-select-smart-tag' data-key = '" . esc_attr( $key ) . "'> " . esc_html( $value ) . '</li>';
		}
		$smart_tags .= '</ul></div>';
		return $smart_tags;
	}

	/**
	 * List of Pattern which can checked against.
	 *
	 * @return array array of pattern lists.
	 */
	public static function ur_pattern_validation_lists() {
		/**
		 * Applies a filter to customize the pattern validation lists.
		 *
		 * The 'user_registration_pattern_validation_lists' filter allows developers to modify
		 * the pattern validation lists used for field validation in User Registration.
		 *
		 * @param array $pattern_lists Default pattern validation lists.
		 */
		$pattern_lists = apply_filters(
			'user_registration_pattern_validation_lists',
			array(
				'^[a-zA-Z]+$'                             => __( 'Alpha', 'user-registration' ),
				'^[a-zA-Z0-9]+$'                          => __( 'Alphanumeric', 'user-registration' ),
				'^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$'     => __( 'Color', 'user-registration' ),
				'^[A-Za-z]{2}$'                           => __( 'Country Code (2 Character)', 'user-registration' ),
				'^[A-Za-z]{3}$'                           => __( 'Country Code (3 Character)', 'user-registration' ),
				'^(0[1-9]|1[0-2])\/(0[1-9]|1\d|2\d|3[01])$' => __( 'Date (mm/dd)', 'user-registration' ),
				'^(0[1-9]|1\d|2\d|3[01])\/(0[1-9]|1[0-2])$' => __( 'Date (dd/mm)', 'user-registration' ),
				'^(0[1-9]|1[0-2])\.(0[1-9]|1\d|2\d|3[01])\.\d{4}$' => __( 'Date (mm.dd.yyyy)', 'user-registration' ),
				'^(0[1-9]|1\d|2\d|3[01])\.(0[1-9]|1[0-2])\.\d{4}$' => __( 'Date (dd.mm.yyyy)', 'user-registration' ),
				'^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|1\d|2\d|3[01])$' => __( 'Date (yyyy-mm-dd)', 'user-registration' ),
				'^(0[1-9]|1[0-2])\/(0[1-9]|1\d|2\d|3[01])\/\d{4}$' => __( 'Date (mm/dd/yyyy)', 'user-registration' ),
				'^(0[1-9]|1\d|2\d|3[01])\/(0[1-9]|1[0-2])\/\d{4}$' => __( 'Date (dd/mm/yyyy)', 'user-registration' ),
				'^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' => __( 'Email', 'user-registration' ),
				'^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?).){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$' => __( 'IP (Version 4)', 'user-registration' ),
				'((^|:)([0-9a-fA-F]{0,4})){1,8}$'         => __( 'IP (Version 6)', 'user-registration' ),
				'^978(?:-[\d]+){3}-[\d]$'                 => __( 'ISBN', 'user-registration' ),
				'-?\d{1,3}\.\d+'                          => __( 'Latitude or Longitude', 'user-registration' ),
				'^[0-9]+$'                                => __( 'Numeric', 'user-registration' ),
				'^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$' => __( 'Password (Numeric, lower, upper)', 'user-registration' ),
				'(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}'     => __( 'Password (Numeric, lower, upper, min 8)', 'user-registration' ),
				'[0-9+()-. ]+'                            => __( 'Phone - General', 'user-registration' ),
				'^\+44\d{10}$'                            => __( 'Phone - UK', 'user-registration' ),
				'\d{3}[\-]\d{3}[\-]\d{4}'                 => __( 'Phone - US: 123-456-7890', 'user-registration' ),
				'\([0-9]{3}\)[0-9]{3}-[0-9]{4}'           => __( 'Phone - US: (123)456-7890', 'user-registration' ),
				'(?:\(\d{3}\)|\d{3})[- ]?\d{3}[- ]?\d{4}' => __( 'Phone - US: Flexible', 'user-registration' ),
				'^[A-Za-z]{1,2}\d{1,2}[A-Za-z]?\s?\d[A-Za-z]{2}$' => __( 'Postal Code (UK)', 'user-registration' ),
				'\d+(\.\d{2})?$'                          => __( 'Price (1.23)', 'user-registration' ),
				'^[a-zA-Z0-9-]+$'                         => __( 'Slug', 'user-registration' ),
				'(0[0-9]|1[0-9]|2[0-3])(:[0-5][0-9]){2}'  => __( 'Time (hh:mm:ss)', 'user-registration' ),
				'^(https?|ftp):\/\/[^\s\/$.?#].[^\s]*$'   => __( 'URL', 'user-registration' ),
				'(\d{5}([\-]\d{4})?)'                     => __( 'Zip Code', 'user-registration' ),
			)
		);
		return $pattern_lists;
	}

	/**
	 * Smart tag list button in general setting and advanced settin of field.
	 *
	 * @param string $pattern_lists Pattern Lists.
	 */
	public function select_pattern_validation( $pattern_lists ) {
		$pattern_validation_list = self::ur_pattern_validation_lists();
		$pattern_lists          .= '<a href="#" class="button ur-smart-tags-list-button"><span class="dashicons dashicons-editor-code"></span></a>';
		$pattern_lists          .= '<div class="ur-smart-tags-list" style="display: none">';
		$pattern_lists          .= '<div class="smart-tag-title ur-smart-tag-title">Regular Expression</div><ul class="ur-smart-tags">';
		foreach ( $pattern_validation_list as $key => $value ) {
			$pattern_lists .= '<li class="ur-select-smart-tag" data-key = "' . esc_attr( $key ) . '">' . esc_html( $value ) . '</li>';
		}
		$pattern_lists .= '</ul></div>';
		return $pattern_lists;
	}
}

new UR_Smart_Tags();
