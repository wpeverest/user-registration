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
		add_filter( 'ur_pattern_validation_list_in_advanced_settings', array(
				$this,
			'select_pattern_validation'
		), 10, 1 );
	}

	/**
	 * List of smart tags.
	 *
	 * @return array array of smart tags.
	 */
	public static function smart_tags_list() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'yith_wpv_panel' &&
			isset( $_GET['tab'] ) && $_GET['tab'] === 'vendors' &&
			isset( $_GET['sub_tab'] ) && $_GET['sub_tab'] === 'vendors-list' ) {
			$smart_tags = array();
		} else {
			$smart_tags = array_merge( self::ur_unauthenticated_parsable_smart_tags_list(), self::ur_authenticated_parsable_smart_tags_list() );
		}

		/**
		 * The 'user_registration_smart_tags' filter allows developers to modify the list
		 * of smart tags available in User Registration for customization.
		 *
		 * @param array $smart_tags List of smart tags before applying the filter.
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
	 * @param array $values Data values.
	 * @param array $name_value Extra values.
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
			 * @param array $default_values Default values before adding or modifying smart tags.
			 * @param string $email Email address associated with the values.
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
				} else {
					if ( empty( $value ) ) {
						$value = '';
					} else {
						$value = implode( ', ', $value );
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
							$name = isset( $values['username'] ) ? $values['username'] : sanitize_text_field( $user->user_login );
						} else {
							$name = isset( $values['username'] ) ? $values['username'] : '';
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
						$user_id         = (int) get_current_user_id();
						$form_id         = ur_get_form_id_by_userid( $user_id );
						$form_data       = user_registration_form_data( $user_id, $form_id );
						$valid_form_data = array();
						foreach ( $form_data as $key => $value ) {
							$new_key = trim( str_replace( 'user_registration_', '', $key ) );

							if ( isset( $user_data[ $new_key ] ) ) {
								$valid_form_data[ $new_key ] = (object) array(
									'field_type'   => isset( $value['type'] ) ? $value['type'] : '',
									'label'        => isset( $value['label'] ) ? $value['label'] : '',
									'field_name'   => isset( $value['field_key'] ) ? $value['field_key'] : '',
									'value'        => $user_data[ $new_key ],
									'extra_params' => array(
										'label'     => isset( $value['label'] ) ? $value['label'] : '',
										'field_key' => isset( $value['field_key'] ) ? $value['field_key'] : '',
									),
								);
							}
						}
						$all_fields_data = ur_parse_name_values_for_smart_tags( $user_id, $form_id, $valid_form_data );

						if ( ! empty( $all_fields_data ) ) {
							$all_fields = isset( $all_fields_data[1] ) ? $all_fields_data[1] : '';
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
						$id = get_the_ID();
						if ( empty( get_the_ID() ) && isset( $_SERVER['HTTP_REFERER'] ) ) {
							$id = url_to_postid( $_SERVER['HTTP_REFERER'] );
						}

						$page_title = get_the_title( $id );
						$content    = str_replace( '{{' . $other_tag . '}}', $page_title, $content );
						break;

					case 'page_url':
						$id = get_the_ID();
						if ( empty( get_the_ID() ) && isset( $_SERVER['HTTP_REFERER'] ) ) {
							$id       = url_to_postid( $_SERVER['HTTP_REFERER'] );
							$page_url = esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
						} else {
							$page_url = get_permalink( $id );
						}

						$content = str_replace( '{{' . $other_tag . '}}', $page_url, $content );
						break;

					case 'page_id':
						$id = get_the_ID();
						if ( empty( get_the_ID() ) && isset( $_SERVER['HTTP_REFERER'] ) ) {
							$id = url_to_postid( $_SERVER['HTTP_REFERER'] );
						}

						$page_id = $id;
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
						$unique_id = apply_filters( 'ur_modify_unique_id_smart_tag', $unique_id );
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
						$confirm_link = sprintf( '<a href="%s" rel="noreferrer noopener" target="_blank">%s</a>', $confirm_link, esc_html__( 'confirm link', 'user-registration' ) );

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
						$user_id             = ! empty( $values['user_id'] ) ? $values['user_id'] : get_current_user_id();
						if ( is_numeric( $profile_picture_url ) ) {
							$profile_picture_url = wp_get_attachment_url( $profile_picture_url );
						}

						$profile_picture_url = apply_filters( 'user_registration_profile_picture_url', $profile_picture_url, $user_id );
						$image               = ( ! empty( $profile_picture_url ) ) ? $profile_picture_url : $gravatar_image;
						$profile_pic_box     = '<img class="profile-preview" alt="profile-picture" src="' . esc_url( $image ) . '" />';
						$content             = str_replace( '{{' . $tag . '}}', wp_kses_post( $profile_pic_box ), $content );
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
						$endpoint             = ur_string_translation( 0, 'user_registration_edit-profile_slug', 'edit-profile' );
						$profile_details_link = '<a href="' . esc_url( ur_get_endpoint_url( $endpoint ) ) . '">' . esc_html__( 'profile details', 'user-registration' ) . '</a>';
						$content              = str_replace( '{{' . $tag . '}}', wp_kses_post( $profile_details_link ), $content );
						break;
					case 'edit_password_link':
						$endpoint           = ur_string_translation( 0, 'user_registration_edit-password_slug', 'edit-password' );
						$edit_password_link = '<a href="' . esc_url( ur_get_endpoint_url( $endpoint ) ) . '">' . esc_html__( 'edit your password', 'user-registration' ) . '</a>';
						$content            = str_replace( '{{' . $tag . '}}', wp_kses_post( $edit_password_link ), $content );
						break;
					case 'sign_out_link':
						$logout_confirmation = ur_option_checked( 'user_registration_disable_logout_confirmation', true );
						$sign_out_link       = '<a href="' . esc_url( ur_logout_url( ur_get_page_permalink( 'myaccount' ) ) ) . '" ' . ( ! $logout_confirmation ? 'class="ur-logout"' : '' ) . '>' . esc_html__( 'Sign out', 'user-registration' ) . '</a>';
						$content             = str_replace( '{{' . $tag . '}}', wp_kses_post( $sign_out_link ), $content );
						break;
					case 'passwordless_login_link':
						$passwordless_login_link = isset( $values['passwordless_login_link'] ) ? esc_url( $values['passwordless_login_link'] ) : '';
						$content                 = str_replace( '{{' . $tag . '}}', wp_kses_post( $passwordless_login_link ), $content );
						break;
					case 'ur_reset_pass_slug':
						$lost_password_page = get_option( 'user_registration_lost_password_page_id', false );
						$reset_pass_slug    = '';

						if ( $lost_password_page ) {
							$lost_password_url = get_permalink( $lost_password_page );
							$ur_lost_pass      = ( get_home_url() !== $lost_password_url ) ? $lost_password_url : wp_login_url();
							$reset_pass_slug   = str_replace( get_home_url() . '/', '', $ur_lost_pass );
						} else {
							$ur_account_page_exists   = ur_get_page_id( 'myaccount' ) > 0;
							$ur_login_or_account_page = ur_get_page_permalink( 'myaccount' );

							if ( ! $ur_account_page_exists ) {
								$ur_login_or_account_page = ur_get_page_permalink( 'login' );
							}

							$ur_login        = ( get_home_url() !== $ur_login_or_account_page ) ? $ur_login_or_account_page : wp_login_url();
							$reset_pass_slug = str_replace( get_home_url() . '/', '', $ur_login );
						}

						$content = str_replace( '{{' . $other_tag . '}}', $reset_pass_slug, $content );
						break;
					case 'sms_otp':
						$content = str_replace( '{{' . $tag . '}}', isset( $values['sms_otp'] ) ? $values['sms_otp'] : '', $content );
						break;
					case 'sms_otp_validity':
						$content = str_replace( '{{' . $tag . '}}', isset( $values['sms_otp_validity'] ) ? $values['sms_otp_validity'] : '', $content );
						break;
					case 'otp_code':
						$content = str_replace( '{{' . $tag . '}}', isset( $values['otp_code'] ) ? $values['otp_code'] : '', $content );
						break;
					case 'otp_expiry_time':
						$content = str_replace( '{{' . $tag . '}}', isset( $values['otp_expiry_time'] ) ? $values['otp_expiry_time'] : '', $content );
						break;
					case 'membership_plan_name':
						$content = str_replace( '{{' . $tag . '}}', isset( $values['membership_plan_name'] ) ? $values['membership_plan_name'] : '', $content );
						break;
					case 'membership_plan_type':
						$content = str_replace( '{{' . $tag . '}}', isset( $values['membership_plan_type'] ) ? $values['membership_plan_type'] : '', $content );
						break;
					case 'membership_plan_payment_method':
						$content = str_replace( '{{' . $tag . '}}', isset( $values['membership_plan_payment_method'] ) ? $values['membership_plan_payment_method'] : '', $content );
						break;
					case 'membership_plan_payment_amount':
						$content = str_replace( '{{' . $tag . '}}', isset( $values['membership_plan_payment_amount'] ) ? $values['membership_plan_payment_amount'] : '', $content );
						break;
					case 'membership_plan_payment_status':
						$content = str_replace( '{{' . $tag . '}}', isset( $values['membership_plan_payment_status'] ) ? $values['membership_plan_payment_status'] : '', $content );
						break;
					case 'membership_plan_billing_cycle':
						$content = str_replace( '{{' . $tag . '}}', isset( $values['membership_plan_billing_cycle'] ) ? $values['membership_plan_billing_cycle'] : '', $content );
						break;
					case 'membership_plan_trial_period':
						$content = str_replace( '{{' . $tag . '}}', isset( $values['membership_plan_trial_period'] ) ? $values['membership_plan_trial_period'] : '', $content );
						break;
					case 'membership_plan_next_billing_date':
						$content = str_replace( '{{' . $tag . '}}', isset( $values['membership_plan_next_billing_date'] ) ? $values['membership_plan_next_billing_date'] : '', $content );
						break;
					case 'membership_plan_expiry_date':
						$content = str_replace( '{{' . $tag . '}}', isset( $values['membership_plan_expiry_date'] ) ? $values['membership_plan_expiry_date'] : '', $content );
						break;
					case 'membership_plan_status':
						$content = str_replace( '{{' . $tag . '}}', isset( $values['membership_plan_status'] ) ? $values['membership_plan_status'] : '', $content );
						break;
					case 'membership_renewal_link':
						$content = str_replace( '{{' . $tag . '}}', isset( $values['membership_renewal_link'] ) ? $values['membership_renewal_link'] : '', $content );
						break;
					case 'membership_plan_details':
						$new_content = '';
						if ( ! empty( $values['membership_tags'] ) ) {
							$membership_tags = $values['membership_tags'];
							$details = array(
								'Plan Name'       => $membership_tags['membership_plan_name'] ?? '',
								'Membership Type' => $membership_tags['membership_plan_type'] ?? '',
								'Payment Details' => array(
									'Method' => $membership_tags['membership_plan_payment_method'] ?? '',
									'Amount' => $membership_tags['membership_plan_total'] ?? '',
									'Status' => $membership_tags['membership_plan_payment_status'] ?? '',
								),
								'Billing Cycle'       => $membership_tags['membership_plan_billing_cycle'] ?? '',
								'Next Billing Date'   => $membership_tags['membership_plan_next_billing_date'] ?? '',
								'Membership Status'   => $membership_tags['membership_plan_status'] ?? '',
							);

							$new_content = '<ul>';
							foreach ( $details as $k => $value ) {
								if ( is_array( $value ) ) {
									$new_content .= sprintf( '<li><b>%s</b>:<ul>', esc_html__( $k, 'user-registration' ) );
									foreach ( $value as $sub_key => $sub_value ) {
										$new_content .= sprintf( '<li><b>%s</b> - %s</li>', esc_html__( $sub_key, 'user-registration' ), esc_html( $sub_value ) );
									}
									$new_content .= '</ul></li>';
								} else {
									$new_content .= sprintf( '<li><b>%s</b> - %s</li>', esc_html__( $k, 'user-registration' ), esc_html( $value ) );
								}
							}
							$new_content .= '</ul>';
						}

						$content = str_replace( '{{' . $tag . '}}', $new_content, $content );
						break;
					case 'payment_invoice' :
						$new_content = '';
						if ( ! empty( $values['membership'] ) ) {
							$invoice_details = $values['membership_tags'];
							$invoice_details['is_membership'] = true;
						} else {
							$invoice_details['is_membership'] = false;
							$invoice_details['user_id'] = !empty( $values['user_id'] ) ? $values['user_id'] : get_current_user_id();
						}
						$template_file   = locate_template( 'payment-successful-email.php' );
						if ( ! $template_file ) {
							$template_file = UR()->plugin_path() . '/modules/membership/includes/Templates/Emails/payment-successful-email.php';
						}
						ob_start();
						require $template_file;
						$new_content = ob_get_clean();
						$content = str_replace( '{{' . $tag . '}}', $new_content, $content );
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
		 * @param array $values Values associated with the smart tags.
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

		$selector = '<a id="ur-smart-tags-selector">';
		$selector .= '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
		<path d="M10 3.33203L14.2 7.53203C14.3492 7.68068 14.4675 7.85731 14.5483 8.05179C14.629 8.24627 14.6706 8.45478 14.6706 8.66536C14.6706 8.87595 14.629 9.08446 14.5483 9.27894C14.4675 9.47342 14.3492 9.65005 14.2 9.7987L11.3333 12.6654" stroke="#6B6B6B" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
		<path d="M6.39132 3.7227C6.14133 3.47263 5.80224 3.33211 5.44865 3.33203H2.00065C1.82384 3.33203 1.65427 3.40227 1.52925 3.52729C1.40422 3.65232 1.33398 3.82189 1.33398 3.9987V7.4467C1.33406 7.80029 1.47459 8.13938 1.72465 8.38937L5.52732 12.192C5.83033 12.4931 6.24015 12.6621 6.66732 12.6621C7.09449 12.6621 7.50431 12.4931 7.80732 12.192L10.194 9.80537C10.4951 9.50236 10.6641 9.09253 10.6641 8.66537C10.6641 8.2382 10.4951 7.82837 10.194 7.52537L6.39132 3.7227Z" stroke="#6B6B6B" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
		<path d="M4.33333 6.66667C4.51743 6.66667 4.66667 6.51743 4.66667 6.33333C4.66667 6.14924 4.51743 6 4.33333 6C4.14924 6 4 6.14924 4 6.33333C4 6.51743 4.14924 6.66667 4.33333 6.66667Z" fill="#6B6B6B" stroke="#6B6B6B" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>';
		$selector .= esc_html__( 'Add Smart Tags', 'user-registration' );
		$selector .= '</a>';
		$selector .= '<select id="select-smart-tags" style="display: none;">';
		$selector .= '<option></option>';

		foreach ( $smart_tags_list as $key => $value ) {
			$selector .= '<option class="ur-select-smart-tag" value="' . esc_attr( $key ) . '"> ' . esc_html( $value ) . '</option>';
		}
		$selector .= '</select>';

		return $selector;
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
				'^[a-zA-Z]+$'                                                                                                                                                                                  => __( 'Alpha', 'user-registration' ),
				'^[a-zA-Z0-9]+$'                                                                                                                                                                               => __( 'Alphanumeric', 'user-registration' ),
				'^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$'                                                                                                                                                          => __( 'Color', 'user-registration' ),
				'^[A-Za-z]{2}$'                                                                                                                                                                                => __( 'Country Code (2 Character)', 'user-registration' ),
				'^[A-Za-z]{3}$'                                                                                                                                                                                => __( 'Country Code (3 Character)', 'user-registration' ),
				'^(0[1-9]|1[0-2])\/(0[1-9]|1\d|2\d|3[01])$'                                                                                                                                                    => __( 'Date (mm/dd)', 'user-registration' ),
				'^(0[1-9]|1\d|2\d|3[01])\/(0[1-9]|1[0-2])$'                                                                                                                                                    => __( 'Date (dd/mm)', 'user-registration' ),
				'^(0[1-9]|1[0-2])\.(0[1-9]|1\d|2\d|3[01])\.\d{4}$'                                                                                                                                             => __( 'Date (mm.dd.yyyy)', 'user-registration' ),
				'^(0[1-9]|1\d|2\d|3[01])\.(0[1-9]|1[0-2])\.\d{4}$'                                                                                                                                             => __( 'Date (dd.mm.yyyy)', 'user-registration' ),
				'^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|1\d|2\d|3[01])$'                                                                                                                                               => __( 'Date (yyyy-mm-dd)', 'user-registration' ),
				'^(0[1-9]|1[0-2])\/(0[1-9]|1\d|2\d|3[01])\/\d{4}$'                                                                                                                                             => __( 'Date (mm/dd/yyyy)', 'user-registration' ),
				'^(0[1-9]|1\d|2\d|3[01])\/(0[1-9]|1[0-2])\/\d{4}$'                                                                                                                                             => __( 'Date (dd/mm/yyyy)', 'user-registration' ),
				'^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'                                                                                                                                             => __( 'Email', 'user-registration' ),
				'^(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])$' => __( 'IP (Version 4)', 'user-registration' ),
				'^((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}))|(([0-9A-Fa-f]{1,4}:){1,7}:)|(:{2})|(([0-9A-Fa-f]{1,4})?::([0-9A-Fa-f]{1,4}:?){0,6}([0-9A-Fa-f]{1,4})?))$'                                        => __( 'IP (Version 6)', 'user-registration' ),
				'^978(?:-[\d]+){3}-[\d]$'                                                                                                                                                                      => __( 'ISBN', 'user-registration' ),
				'-?\d{1,3}\.\d+'                                                                                                                                                                               => __( 'Latitude or Longitude', 'user-registration' ),
				'^[0-9]+$'                                                                                                                                                                                     => __( 'Numeric', 'user-registration' ),
				'^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$'                                                                                                                                                   => __( 'Password (Numeric, lower, upper)', 'user-registration' ),
				'(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}'                                                                                                                                                          => __( 'Password (Numeric, lower, upper, min 8)', 'user-registration' ),
				'[0-9+()-. ]+'                                                                                                                                                                                 => __( 'Phone - General', 'user-registration' ),
				'^\+44\d{10}$'                                                                                                                                                                                 => __( 'Phone - UK', 'user-registration' ),
				'\d{3}[\-]\d{3}[\-]\d{4}'                                                                                                                                                                      => __( 'Phone - US: 123-456-7890', 'user-registration' ),
				'\([0-9]{3}\)[0-9]{3}-[0-9]{4}'                                                                                                                                                                => __( 'Phone - US: (123)456-7890', 'user-registration' ),
				'(?:\(\d{3}\)|\d{3})[- ]?\d{3}[- ]?\d{4}'                                                                                                                                                      => __( 'Phone - US: Flexible', 'user-registration' ),
				'^[A-Za-z]{1,2}\d{1,2}[A-Za-z]?\s?\d[A-Za-z]{2}$'                                                                                                                                              => __( 'Postal Code (UK)', 'user-registration' ),
				'\d+(\.\d{2})?$'                                                                                                                                                                               => __( 'Price (1.23)', 'user-registration' ),
				'^[a-zA-Z0-9-]+$'                                                                                                                                                                              => __( 'Slug', 'user-registration' ),
				'(0[0-9]|1[0-9]|2[0-3])(:[0-5][0-9]){2}'                                                                                                                                                       => __( 'Time (hh:mm:ss)', 'user-registration' ),
				'^(https?|ftp):\/\/[^\s\/$.?#].[^\s]*$'                                                                                                                                                        => __( 'URL', 'user-registration' ),
				'^\d{5}(-\d{4})?$'                                                                                                                                                                             => __( 'Zip Code', 'user-registration' ),
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
		$pattern_lists           .= '<a href="#" class="button ur-smart-tags-list-button"><span class="dashicons dashicons-editor-code"></span></a>';
		$pattern_lists           .= '<div class="ur-smart-tags-list" style="display: none">';
		$pattern_lists           .= '<div class="smart-tag-title ur-smart-tag-title">Regular Expression</div><ul class="ur-smart-tags">';
		foreach ( $pattern_validation_list as $key => $value ) {
			$pattern_lists .= '<li class="ur-select-smart-tag" data-key = "' . esc_attr( $key ) . '">' . esc_html( $value ) . '</li>';
		}
		$pattern_lists .= '</ul></div>';

		return $pattern_lists;
	}
}

new UR_Smart_Tags();
