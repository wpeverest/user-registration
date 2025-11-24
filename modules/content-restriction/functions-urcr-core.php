<?php
/**
 * UserRegistrationContentRestriction Functions.
 *
 * General core functions available on both the front-end and admin.
 *
 * @author   WPEverest
 * @category Core
 * @package  UserRegistrationContentRestriction/Functions
 * @version  1.0.0
 */

defined( 'ABSPATH' ) || exit;

function urcr_get_allow_options() {
	global $wp_roles;

	if ( ! class_exists( 'WP_Roles' ) ) {
		return array();
	}

	if ( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}

	$roles     = isset( $wp_roles->roles ) ? $wp_roles->roles : array();
	$all_roles = array();

	foreach ( $roles as $role_key => $role ) {
		$all_roles[ $role_key ] = $role['name'];
	}

	return apply_filters( 'user_registration_content_restriction_to_roles', $all_roles );
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $var Data to sanitize.
 *
 * @return string|array
 */
function urcr_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'urcr_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

/**
 * Get a list containing all available capabilities.
 *
 * @return array List of capabilities.
 * @since 2.0.0
 */
function urcr_get_all_capabilities() {
	global $wp_roles;

	$wp_capabilities = array();

	foreach ( $wp_roles->roles as $role ) {
		$role_cap_slugs    = array_keys( $role['capabilities'] );
		$role_capabilities = array_combine( $role_cap_slugs, $role_cap_slugs );
		$wp_capabilities   = array_merge( $wp_capabilities, $role_capabilities );
	}

	return apply_filters( 'urcr_capabilities_list', $wp_capabilities );
}

/**
 * See if the given access rule is enabled.
 *
 * @param array $access_rule Acess Rule.
 *
 * @return bool
 * @since 2.0.0
 */
function urcr_is_access_rule_enabled( $access_rule = array() ) {
	$access_rule = (array) $access_rule;

	if ( isset( $access_rule['enabled'] ) && true === $access_rule['enabled'] ) {
		return true;
	}

	return false;
}

/**
 * See if any action has been specified for a content acess rule.
 *
 * @param array $access_rule Access Rule.
 *
 * @return bool
 * @since 2.0.0
 */
function urcr_is_action_specified( $access_rule = array() ) {
	$access_rule = (array) $access_rule;

	if ( ! empty( $access_rule['actions'] ) ) {
		$actions = (array) $access_rule['actions'];

		foreach ( $actions as $action ) {
			if ( ! empty( $action['type'] ) ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * See if the post is in the provided targets list.
 *
 * @param array       $targets Targets list.
 * @param object|null $target_post Post to check against.
 *
 * @return bool
 * @since 2.0.0
 */
function urcr_is_target_post( $targets = array(), $target_post = null ) {

	if ( is_array( $targets ) ) {
		foreach ( $targets as $target ) {
			if ( isset( $target['type'] ) && ! empty( $target['value'] ) ) {
				switch ( $target['type'] ) {
					case 'wp_posts':
						$post_id         = ( 'object' === gettype( $target_post ) && $target_post->ID ) ? strval( $target_post->ID ) : '0';
						$target_post_ids = (array) $target['value'];

						if ( in_array( $post_id, $target_post_ids, true ) ) {
							return true;
						}
						break;

					case 'wp_pages':
						$page_id         = ( 'object' === gettype( $target_post ) && $target_post->ID ) ? strval( $target_post->ID ) : '0';
						$target_page_ids = (array) $target['value'];

						if ( in_array( $page_id, $target_page_ids, true ) ) {
							return true;
						}
						break;

					case 'post_types':
						$post_type         = ( 'object' === gettype( $target_post ) && $target_post->post_type ) ? strval( $target_post->post_type ) : '';
						$post_type         = ( is_singular( 'product' ) && is_array( $target_post ) ) ? $target_post[0]->post_type : $post_type;
						$target_post_types = (array) $target['value'];

						if ( in_array( $post_type, $target_post_types, true ) ) {
							return true;
						}
						$products_page_id = intval( get_option( 'woocommerce_shop_page_id' ) );
						// Check if its a woocommerce product page.
						if ( is_object( $target_post ) && (int) $products_page_id === $target_post->ID ) {
							return true;
						}

						break;

					case 'taxonomy':
						if ( ! empty( $target['taxonomy'] ) && ! empty( $target['value'] ) ) {
							// Check if its a woocommerce product page.
							$products_page_id = intval( get_option( 'woocommerce_shop_page_id' ) );
							$post_taxonomies  = get_post_taxonomies( $target_post );
							if ( is_numeric( $target_post ) && $target_post == $products_page_id ) {
								return true;
							}

							if ( ! in_array( $target['taxonomy'], (array) $post_taxonomies ) ) {
								return - 1;
							}

							/**
							 * Filter to modify the post taxonomy status.
							 *
							 * @since xx.xx.xx
							 */
							$post_status = apply_filters( 'user_registration_membership_post_taxonomy_status', '' );

							if ( ! empty( $post_status ) && isset( $target_post->post_status ) && $target_post->post_status === $post_status ) {
								return -1;
							}

							$terms = get_the_terms( $target_post, $target['taxonomy'] );

							if ( empty( $terms ) || is_wp_error( $terms ) ) {
								return - 1;
							}

							foreach ( $terms as $term ) {
								if ( in_array( $term->term_id, $target['value'] ) ) {
									return true;
								}
							}
						}
						break;
					case 'whole_site':
						return true;
						break;

					default:
						break;
				}
			}
		}
	}

	return false;
}

/**
 * See if the required conditions are met by the given logic map to be resolved as true.
 *
 * @param array $targets Targets list.
 *
 * @return bool
 * @deprecated 1.1.2
 */
function urcr_is_current_target( $targets = array() ) {
	if ( function_exists( 'ur_deprecated_function' ) ) {
		ur_deprecated_function( 'urcr_is_current_target', '1.1.2', 'urcr_is_target_post' );
	}

	return urcr_is_target_post( $targets, null );
}

/**
 * See if the required conditions are met by the given logic map to be resolved as true.
 *
 * @param array       $logic_map Logic Map.
 * @param object|null $target_post Post to check against.
 *
 * @return bool
 * @since 2.0.0
 */
function urcr_is_allow_access( $logic_map = array(), $target_post = null ) {
	global $post;

	if ( ! is_object( $target_post ) ) {
		$target_post = $post;
	}

	$logic_map = (array) $logic_map;

	if ( ! empty( $logic_map ) ) {
		$type = $logic_map['type'];

		// Process Logic Map.
		if ( 'group' === $type ) {
			$gate = ! empty( $logic_map['logic_gate'] ) ? $logic_map['logic_gate'] : 'OR';

			foreach ( $logic_map['conditions'] as $sub_logic_map ) {
				$is_allow_access = urcr_is_allow_access( $sub_logic_map, $target_post );

				if ( 'AND' === $gate && false === $is_allow_access ) {
					return false;
				} elseif ( 'NOT' === $gate && true === $is_allow_access ) {
					return false;
				} elseif ( 'OR' === $gate && true === $is_allow_access ) {
					return true;
				}
			}
			if ( 'AND' === $gate || 'NOT' === $gate ) {
				return true;
			}
			if ( 'OR' === $gate ) {
				return false;
			}
		} else {
			$user = wp_get_current_user();

			switch ( $type ) {
				case 'roles':
					if ( $user->ID && count( array_intersect( (array) $user->roles, $logic_map['value'] ) ) ) {
						return true;
					}
					break;

				case 'capabilities':
					if ( $user->ID ) {
						$allowed_caps = isset( $logic_map['value'] ) ? (array) $logic_map['value'] : array();

						foreach ( $allowed_caps as $cap ) {
							if ( current_user_can( $cap, $target_post->ID ) ) {
								return true;
							}
						}
					}
					break;

				case 'user_registered_date':
					if ( $user->ID ) {
						$registered_date = ! empty( $user->data->user_registered ) ? $user->data->user_registered : '';
						$date_range      = ! empty( $logic_map['value'] ) ? explode( 'to', (string) $logic_map['value'] ) : array();
						$start_date      = ! empty( $date_range[0] ) ? trim( $date_range[0] ) : '';
						$end_date        = ! empty( $date_range[1] ) ? trim( $date_range[1] ) : '';

						if ( ! empty( $start_date ) && ! empty( $end_date ) && ur_falls_in_date_range( $registered_date, $start_date, $end_date ) ) {
							return true;
						}
					}
					break;

				case 'access_period':
					if ( $user->ID ) {
						$registered_date = ! empty( $user->data->user_registered ) ? $user->data->user_registered : '';
						$access_period   = ! empty( $logic_map['value'] ) ? $logic_map['value'] : '';

						$now             = time();
						$registered_time = strtotime( $registered_date );
						$datediff        = $now - $registered_time;

						$actual_days = round( $datediff / ( 60 * 60 * 24 ) );

						if ( ! empty( $access_period['input'] ) ) {
							if ( 'During' === $access_period['select'] && $actual_days <= $access_period['input'] ) {
								return true;
							} elseif ( 'After' === $access_period['select'] && $actual_days > 0 && $actual_days > $access_period['input'] ) {
								return true;
							}
						}
					}
					break;

				case 'ur_form_field':
					if ( $user->ID ) {
						$registered_date = ! empty( $user->data->user_registered ) ? $user->data->user_registered : '';
						$form_field_data = ! empty( $logic_map['value'] ) ? $logic_map['value'] : '';
						$user_form_id    = ur_get_form_id_by_userid( $user->ID );

						if ( isset( $form_field_data['form_id'] ) && $form_field_data['form_id'] == $user_form_id ) {
							$flag = array();

							foreach ( $form_field_data['form_fields'] as $key => $data ) {
								$field_name = str_replace( 'user_registration_', '', $data['field_name'] );
								$field_name = ur_get_field_name_with_prefix_usermeta( $field_name );

								switch ( $field_name ) {
									case 'user_login':
										$user_field_value = $user->user_login;
										break;
									case 'user_nicename':
										$user_field_value = $user->user_nicename;
										break;
									case 'user_email':
										$user_field_value = $user->user_email;
										break;
									case 'user_url':
										$user_field_value = $user->user_url;
										break;
									case 'display_name':
										$user_field_value = $user->display_name;
										break;

									default:
										$user_field_value = get_user_meta( $user->ID, $field_name, true );
										break;
								}

								$user_field_value = is_array( $user_field_value ) ? implode( ',', $user_field_value ) : strval( $user_field_value );

								if ( 'is' == $data['operator'] && $user_field_value === $data['value'] ) {
									array_push( $flag, true );
								} elseif ( 'is not' == $data['operator'] && $user_field_value !== $data['value'] ) {
									array_push( $flag, true );
								} elseif ( 'empty' === $data['operator'] && empty( $user_field_value ) ) {
									array_push( $flag, true );
								} elseif ( 'not empty' === $data['operator'] && ! empty( $user_field_value ) ) {
									array_push( $flag, true );
								}
							}

							if ( count( $flag ) === count( $form_field_data['form_fields'] ) ) {
								return true;
							}
						}
					}
					break;

				case 'user_state':
					$should_be_logged_in = ( isset( $logic_map['value'] ) && 'logged-in' === $logic_map['value'] ) ? true : false;

					if ( $should_be_logged_in ) {
						return is_user_logged_in();
					} else {
						return ! is_user_logged_in();
					}
					break;

				case 'profile_completeness':
					$completeness_level = get_user_meta( $user->ID, 'user_registration_profile_completeness_completed_profile_percentage', true );
					$completeness_level = ! empty( $completeness_level ) ? (int) $completeness_level : 100;

					$threshold = ! empty( $logic_map['value'] ) ? (int) $logic_map['value'] : 0;

					if ( $completeness_level > $threshold ) {
						return true;
					}

					break;

				case 'post_count':
					if ( $user->ID ) {
						$public_posts_by_user_count   = (int) count_user_posts( $user->ID, 'post', true );
						$minimum_required_posts_count = ! empty( $logic_map['value'] ) ? (int) $logic_map['value'] : 0;

						if ( $public_posts_by_user_count >= $minimum_required_posts_count ) {
							return true;
						}
					}
					break;

				case 'email_domain':
					if ( $user->ID ) {
						$domains           = ! empty( $logic_map['value'] ) ? explode( ',', (string) $logic_map['value'] ) : array();
						$domains           = array_map( 'trim', $domains );
						$user_email        = explode( '@', $user->data->user_email );
						$user_email_domain = isset( $user_email[1] ) ? trim( $user_email[1] ) : '';

						if ( in_array( $user_email_domain, $domains, true ) ) {
							return true;
						}
					}
					break;

				case 'registration_source':
					if ( $user->ID ) {
						$registered_source = ur_get_registration_source_id( $user->ID );
						$sources           = ! empty( $logic_map['value'] ) ? $logic_map['value'] : array();

						if ( in_array( $registered_source, $sources, true ) ) {
							return true;
						}
					}
					break;

				case 'payment_status':
					if ( $user->ID ) {
						$payment_status = get_user_meta( $user->ID, 'ur_payment_status', true );
						$sources        = ! empty( $logic_map['value'] ) ? $logic_map['value'] : array();

						if ( in_array( $payment_status, $sources, true ) ) {
							return true;
						}
					}
					break;
				case 'membership':
					if ( $user->ID && ur_check_module_activation( 'membership' ) ) {
						$members_repository = new \WPEverest\URMembership\Admin\Repositories\MembersRepository();
						$user_membership    = $members_repository->get_member_membership_by_id( $user->ID );
						$is_user_membership_active = ! empty( $user_membership['status'] ) && 'active' === $user_membership['status'];
						$sources            = ! empty( $logic_map['value'] ) ? $logic_map['value'] : array();

						if ( ! empty( $user_membership ) && in_array( $user_membership['post_id'], $sources, true ) && $is_user_membership_active ) {
							return true;
						}
					}
			}

			return false;
		}
	}

	return true;
}

/**
 * See if the required conditions are met by the given logic map to be resolved as true.
 *
 * @param array $logic_map Logic Map.
 *
 * @return bool
 * @deprecated 1.1.2
 */
function urcr_resolve_logic_map( $logic_map = array() ) {
	if ( function_exists( 'ur_deprecated_function' ) ) {
		ur_deprecated_function( 'urcr_resolve_logic_map', '1.1.2', 'urcr_is_allow_access' );
	}

	return urcr_is_allow_access( $logic_map, null );
}

/**
 * See if a elementor content have been restricted and shown a message.
 *
 * @return boolean
 * @since 1.1.3
 */
function urcr_is_elementor_content_restricted() {
	return isset( $GLOBALS['urcr_ecr_flag'] ) && $GLOBALS['urcr_ecr_flag'] === true;
}

/**
 * Set a flag to indicate that a elementor content have been restricted and shown a message.
 *
 * @since 1.1.3
 */
function urcr_set_elementor_content_restricted() {
	$GLOBALS['urcr_ecr_flag'] = true;
}

/**
 * Apply content restriction to the current content.
 *
 * @param array       $actions Sequence of actions to run.
 * @param object|null $target_post Post to check against.
 *
 * @return bool
 * @since 2.0.0
 */
function urcr_apply_content_restriction( $actions, &$target_post = null ) {
	global $post;

	if ( ! is_object( $target_post ) ) {
		$target_post = $post;
	}

	$actions = (array) $actions;
	$action  = $actions[0];

	// Filter to buypass access rules for certain conditions.
	$run_rule = apply_filters( 'urcr_pre_confirm_access_rules_implementation', true, $target_post );

	if ( false === $run_rule ) {
		return false;
	}

	if ( isset( $target_post->ID ) && $target_post->ID && ! empty( $action['type'] ) ) {
		if ( 'message' === $action['type'] ) {
			$message = ! empty( $action['message'] ) ? urldecode( $action['message'] ) : '';
			$message = apply_filters( 'user_registration_process_smart_tags', $message );

			$target_post->post_content = $message;

			// Add filter for elementor content.
			add_filter(
				'elementor/frontend/the_content',
				function () use ( $message ) {
					if ( ! urcr_is_elementor_content_restricted() ) {
						urcr_set_elementor_content_restricted();

						return $message;
					}

					return '';
				}
			);

			return true;
		} elseif ( 'redirect' === $action['type'] ) {
			$redirect_url = trim( ! empty( $action['redirect_url'] ) ? $action['redirect_url'] : admin_url() );
			$redirect_url = urldecode( $redirect_url );

			if ( strpos( $redirect_url, 'http' ) !== 0 ) {
				$redirect_url = 'http://' . $redirect_url;
			}
			wp_redirect( $redirect_url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
			exit;
		} elseif ( 'redirect_to_local_page' === $action['type'] ) {
			$page_id = ! empty( $action['local_page'] ) ? $action['local_page'] : null;

			if ( $target_post->ID && strval( $page_id ) === strval( $target_post->ID ) ) {
				wp_die( esc_html__( 'URCR: Cannot redirect to same page. The target page was selected as redirection target for content restriction.', 'user-registration' ) );
			}
			if ( $page_id ) {
				$page_url = get_page_link( $page_id );
				wp_redirect( $page_url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
				exit;
			}
		} elseif ( 'ur-form' === $action['type'] ) {
			$ur_form_id                = ! empty( $action['ur_form'] ) ? $action['ur_form'] : '';
			$shortcode                 = sprintf( '[user_registration_form id="%s"]', $ur_form_id );
			$target_post->post_content = $shortcode;

			// Add filter for elementor content.
			add_filter(
				'elementor/frontend/the_content',
				function () use ( $shortcode ) {
					if ( ! urcr_is_elementor_content_restricted() ) {
						urcr_set_elementor_content_restricted();

						return $shortcode;
					}

					return '';
				}
			);

			return true;
		} elseif ( 'shortcode' === $action['type'] && ! empty( $action['shortcode'] ) ) {
			$shortcode_tag  = ! empty( $action['shortcode']['tag'] ) ? $action['shortcode']['tag'] : '';
			$shortcode_args = ! empty( $action['shortcode']['args'] ) ? urldecode( $action['shortcode']['args'] ) : '';

			if ( ! preg_match( '/id=["\']?(\d+)["\']?/', $shortcode_args, $matches ) ) {
				$shortcode_id = trim( $shortcode_args );

				if ( is_numeric( $shortcode_id ) ) {
					$shortcode_args = 'id="' . $shortcode_id . '"';
				} else {
					$shortcode_args = 'id=' . $shortcode_id;
				}
			}

			$shortcode                 = sprintf( '[%s %s]', $shortcode_tag, $shortcode_args );
			$target_post->post_content = $shortcode;

			// Add filter for elementor content.
			add_filter(
				'elementor/frontend/the_content',
				function () use ( $shortcode ) {
					if ( ! urcr_is_elementor_content_restricted() ) {
						urcr_set_elementor_content_restricted();

						return $shortcode;
					}

					return '';
				}
			);

			return true;
		}
	}

	return false;
}

/**
 * Get other templates (e.g. my account) passing attributes and including the file.
 *
 * @param string $template_name Template Name.
 * @param array  $args Extra arguments(default: array()).
 * @param string $template_path Path of template provided (default: '').
 * @param string $default_path Default path of template provided(default: '').
 */
function urcr_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args ); // phpcs:ignore
	}

	$located = urcr_locate_template( $template_name, $template_path, $default_path );

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters( 'urcr_get_template', $located, $template_name, $args, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', esc_html( $located ) ), '1.0' );

		return;
	}

	do_action( 'user_registration_content_restriction_before_template_part', $template_name, $template_path, $located, $args );

	include $located;

	do_action( 'user_registration_content_restriction_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *        yourtheme        /    $template_path    /    $template_name
 *        yourtheme        /    $template_name
 *        $default_path    /    $template_name
 *
 * @param string $template_name Template Name.
 * @param string $template_path Path of template provided (default: '').
 * @param string $default_path Default path of template provided(default: '').
 *
 * @return string
 */
function urcr_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = UR()->plugin_path() . '/templates/modules/content-restriction/';
	}

	if ( ! $default_path ) {
		$default_path = UR()->plugin_path() . '/templates/modules/content-restriction/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);

	// Get default template.
	if ( ! $template || UR_TEMPLATE_DEBUG_MODE ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'user_registration_content_restriction_locate_template', $template, $template_name, $template_path );
}

/**
 * Common function for template access actions
 *
 * @param mixed $target_post Post.
 * @param mixed $action Action.
 */
function urcr_advanced_access_actions( $target_post, $action ) {
	if ( $target_post->ID && ! empty( $action['type'] ) ) {
		if ( 'message' === $action['type'] ) {
			$message = ! empty( $action['message'] ) ? urldecode( $action['message'] ) : '';
			$message = apply_filters( 'user_registration_process_smart_tags', $message );
			if ( function_exists( 'apply_shortcodes' ) ) {
				echo apply_shortcodes( $message );
			} else {
				echo do_shortcode( $message );
			}
		} elseif ( 'redirect' === $action['type'] ) {
			$redirect_url = trim( ! empty( $action['redirect_url'] ) ? $action['redirect_url'] : admin_url() );
			$redirect_url = urldecode( $redirect_url );

			if ( strpos( $redirect_url, 'http' ) !== 0 ) {
				$redirect_url = 'http://' . $redirect_url;
			}
			echo "<script>window.location.href = '" . esc_url( $redirect_url ) . "';</script>";

			wp_redirect( $redirect_url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
			exit;
		} elseif ( 'redirect_to_local_page' === $action['type'] ) {
			$page_id = ! empty( $action['local_page'] ) ? $action['local_page'] : null;

			if ( $target_post->ID && strval( $page_id ) === strval( $target_post->ID ) ) {
				wp_die( esc_html__( 'URCR: Cannot redirect to same page. The target page was selected as redirection target for content restriction.', 'user-registration' ) );
			}
			if ( $page_id ) {
				$page_url = get_page_link( $page_id );
				echo "<script>window.location.href = '" . esc_url( $page_url ) . "';</script>";
				wp_redirect( $page_url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
				exit;
			}
		} elseif ( 'ur-form' === $action['type'] ) {
			$ur_form_id = ! empty( $action['ur_form'] ) ? $action['ur_form'] : '';
			$shortcode  = sprintf( '[user_registration_form id="%s"]', $ur_form_id );
			if ( function_exists( 'apply_shortcodes' ) ) {
				echo apply_shortcodes( $shortcode );
			} else {
				echo do_shortcode( $shortcode );
			}
		} elseif ( 'shortcode' === $action['type'] && ! empty( $action['shortcode'] ) ) {
			$shortcode_tag  = ! empty( $action['shortcode']['tag'] ) ? $action['shortcode']['tag'] : '';
			$shortcode_args = ! empty( $action['shortcode']['args'] ) ? urldecode( $action['shortcode']['args'] ) : '';
			$shortcode      = sprintf( '[%s %s]', $shortcode_tag, $shortcode_args );
			if ( function_exists( 'apply_shortcodes' ) ) {
				echo apply_shortcodes( $shortcode );
			} else {
				echo do_shortcode( $shortcode );
			}
		}
	}
}

function ur_restrict_files( $file, $restriction_conditions, $restricted_files ) {
	$restriction_conditions  = json_decode( $restriction_conditions, true );
	$restrict_logout_user    = isset( $restriction_conditions['is_logout'] ) ? $restriction_conditions['is_logout'] : false;
	$restrict_based_on_role  = isset( $restriction_conditions['role'] ) ? $restriction_conditions['role'] : array();
	$restriction_redirection = isset( $restriction_conditions['redirect'] ) ? $restriction_conditions['redirect'] : false;
	$restriction_message     = isset( $restriction_conditions['message'] ) ? $restriction_conditions['message'] : false;
	$default_message         = __( "You don't have permission to access this file.", 'user-registration' );

	$pattern   = '/' . basename( WP_CONTENT_DIR ) . '\/(.+)/';
	$file_name = preg_match( $pattern, $file, $matches ) ? $matches[1] : '';
	if ( in_array( $file_name, $restricted_files ) ) {

		$is_user_logged_in = is_user_logged_in();
		if ( $restrict_logout_user && ! $is_user_logged_in ) {
			if ( $restriction_redirection ) {
				wp_redirect( $restriction_redirection );
				exit;
			} else {
				echo $restriction_message ? $restriction_message : $default_message;
				exit;
			}
		} else {
			$fp = fopen( $file, 'r' );
			header( 'Content-type: ' . mime_content_type( $file ) );
			fpassthru( $fp );
			fclose( $fp );
			exit;
		}

		if ( is_array( $restrict_based_on_role ) && ! empty( $restrict_based_on_role ) ) {
			$user = wp_get_current_user();
			foreach ( $restrict_based_on_role as $role ) {
				if ( in_array( $role, $user->roles ) ) {
					if ( $restriction_redirection ) {
						wp_redirect( $restriction_redirection );
						exit;
					} else {
						echo $restriction_message ? $restriction_message : $default_message;
						exit;
					}
				}
			}
		} else {
			$fp = fopen( $file, 'r' );
			header( 'Content-type: ' . mime_content_type( $file ) );
			fpassthru( $fp );
			fclose( $fp );
			exit;
		}

		// For default not to restrict for admin.
		$current_user = wp_get_current_user();
		if ( ! empty( $current_user->roles ) && in_array( 'administrator', $current_user->roles ) ) {
			$fp = fopen( $file, 'r' );
			header( 'Content-type: ' . mime_content_type( $file ) );
			fpassthru( $fp );
			fclose( $fp );
			exit;
		}
		// Default send 403 forbidden.
			header( 'HTTP/1.0 403 Forbidden' );
			exit;

	} else {
		$fp = fopen( $file, 'r' );
		header( 'Content-type: ' . mime_content_type( $file ) );
		fpassthru( $fp );
		fclose( $fp );
		exit;
	}
}

