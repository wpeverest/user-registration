<?php
/**
 * User Registration Content Restriction Shortcodes.
 *
 * @class    URCR_Shortcodes
 * @version  4.0
 * @package  UserRegistrationContentRestriction/Classes
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URCR_Shortcodes Class
 */

class URCR_Shortcodes {

	public function __construct() {
		add_shortcode( 'urcr_restrict', array( $this, 'urcr_restrict_shortcode' ) );
	}

	public function urcr_restrict_shortcode( $atts, $content = null ) {
		global $post;
		/** For divi builder edit
		 * global $post will be the empty on edit mode.
		 *
		 * @since 4.2.1
		 */

		if ( empty( $post ) && ( function_exists( 'urm_is_divi_active' ) && urm_is_divi_active() ) ) {
			$post = isset( $atts['post_id'] ) ? get_post( absint( $atts['post_id'] ) ) : null;
		}

		if ( ! is_object( $post ) ) {
			return;
		}

		$enable_disable = ur_string_to_bool( get_option( 'user_registration_content_restriction_enable', true ) );
		if ( $enable_disable ) {
			$override_global_settings = get_post_meta( $post->ID, 'urcr_meta_override_global_settings', $single = true );

			$allowed_roles       = get_option( 'user_registration_content_restriction_allow_to_roles', 'administrator' );
			$allowed_memberships = get_option( 'user_registration_content_restriction_allow_to_memberships' );

			$current_user_role         = is_user_logged_in() ? wp_get_current_user()->roles[0] : 'guest';
			$get_meta_data_roles       = get_post_meta( $post->ID, 'urcr_meta_roles', $single = true );
			$get_meta_data_memberships = get_post_meta( $post->ID, 'urcr_meta_memberships', true );
			foreach ($atts as $key => $value) {
				if (is_string($value)) {
					$value = html_entity_decode($value, ENT_QUOTES);
					$value = str_replace(['“', '”'], '"', $value);
					$atts[$key] = trim($value, '"');
				}
			}

			if (isset($atts['access_specific_role']) && ! empty($atts['access_specific_role']) && 'choose_specific_roles' === $atts['access_all_roles']) {
				$specific_roles = $atts['access_specific_role'];
			}

			if (isset($atts['access_membership_role']) && !empty($atts['access_membership_role']) && 'memberships' === $atts['access_all_roles']) {
				$memberships_roles = $atts['access_membership_role'];
			}


			$access_control = isset( $atts['access_control'] ) ? trim( $atts['access_control'] ) : '';

			$roles                = isset( $atts['access_role'] ) ? trim( $atts['access_role'] ) : '';

			if ( isset( $atts['access_all_roles'],$atts['enable_content_restriction'] ) && ! empty( $atts['access_all_roles']  ) &&  $atts['enable_content_restriction'] == true ) {
				$roles = trim( $atts['access_all_roles'] );
			}

			$is_membership_active = ur_check_module_activation( 'membership' );

			if ( $is_membership_active ) {
				$members_subscription = new \WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository();
				$subscription         = $members_subscription->get_member_subscription( wp_get_current_user()->ID );

				$current_user_membership = ( ! empty( $subscription ) ) ? $subscription['item_id'] : array();
			}

			if ( empty( $roles ) ) {
				$override_global_settings = get_post_meta( $post->ID, 'urcr_meta_override_global_settings', $single = true );

				if ( $override_global_settings !== 'on' ) {

					if ( '0' == get_option( 'user_registration_content_restriction_allow_access_to', '0' ) ) {
						if ( is_user_logged_in() ) {
							return do_shortcode( $content );
						}
					} elseif ( '1' == get_option( 'user_registration_content_restriction_allow_access_to' ) ) {
						if ( is_array( $allowed_roles ) && in_array( $current_user_role, $allowed_roles ) ) {
							return do_shortcode( $content );
						}
					} elseif ( '2' === get_option( 'user_registration_content_restriction_allow_access_to' ) ) {
						if ( ! is_user_logged_in() ) {
							return do_shortcode( $content );
						}
					} elseif ( '3' === get_option( 'user_registration_content_restriction_allow_access_to' ) ) {
						if ( is_array( $allowed_memberships ) && in_array( $current_user_membership, $allowed_memberships ) ) {
							return do_shortcode( $content );
						}
					}
				} else {
					$get_meta_data_allow_to = get_post_meta( $post->ID, 'urcr_allow_to', $single = true );

					switch ( $get_meta_data_allow_to ) {
						case '0':
							if ( is_user_logged_in() ) {
								return do_shortcode( $content );
							}
							break;
						case '1':
							if ( isset( $get_meta_data_roles ) && ! empty( $get_meta_data_roles ) ) {
								if ( is_array( $get_meta_data_roles ) && in_array( $current_user_role, $get_meta_data_roles ) ) {
									return do_shortcode( $content );
								}
							}
							break;
						case '2':
							if ( ! is_user_logged_in() ) {
								return do_shortcode( $content );
							}
							break;
						case '3':
							if ( ! empty( $get_meta_data_memberships ) ) {
								if ( is_array( $get_meta_data_memberships ) && in_array( $current_user_membership, $get_meta_data_memberships ) ) {
									return do_shortcode( $content );
								}
							}
							break;
					}
				}
			}

			$specific_roles = isset($specific_roles) ? explode( ',', $specific_roles ) : array();
			$specific_roles = array_map( 'trim', $specific_roles );

			$memberships_roles = isset($memberships_roles) ? explode( ',', $memberships_roles ) : array();
			$memberships_roles = array_map( function($role) { return trim(str_replace('″', '', $role)) ;}, $memberships_roles );
			$message = '';
			if ( $override_global_settings === 'on' ) {
				$message = ! empty(get_post_meta( $post->ID, 'urcr_meta_content', $single = true )) ? get_post_meta( $post->ID, 'urcr_meta_content', $single = true ) : '';
			} elseif ( isset( $atts['enable_content_restriction']) && $atts['enable_content_restriction'] === "true" ) {
				$message = isset($atts['message']) ? wp_kses_post( html_entity_decode( $atts['message'] ) ) : get_option( 'user_registration_content_restriction_message' );
			}

			$message = empty( $message ) ? __( 'This content is restricted!', 'user-registration' ) : $message;
			$message = apply_filters( 'user_registration_process_smart_tags', $message );
			$message = do_shortcode( $message );

			if ( isset( $atts['enable_content_restriction']) && $atts['enable_content_restriction'] == true ) {
				$control_type = $atts['access_control'] ?? 'access';
				$matched = false;

				switch ($roles) {
					case 'all_logged_in_users':
						$matched = is_user_logged_in();
						break;

					case 'choose_specific_roles':
						if (!empty($specific_roles) && in_array($current_user_role, $specific_roles, true)) {
							$matched = true;
						}
						break;

					case 'guest_users':
						$matched = !is_user_logged_in();
						break;

					case 'memberships':
						if (!empty($memberships_roles) && in_array($current_user_membership, $memberships_roles, true)) {
							$matched = true;
						}
						break;
				}

				// Apply access_control logic
				if (
					($control_type === 'access' && $matched) ||
					($control_type === 'restrict' && !$matched)
				) {
					return do_shortcode($content);
				}
			}
			return '<span class="urcr-restrict-message">' . $message . '</span>';

		}
			return do_shortcode( $content );
	}
}
return new URCR_Shortcodes();
