<?php
/**
 * User registration content restriction block.
 *
 * @since xx.xx.xx
 * @package user-registration
 */

defined( 'ABSPATH' ) || exit;
/**
 * Block registration form class.
 */
class UR_Block_Content_Restriction_V2 extends UR_Block_Abstract {
	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'content-restriction-v2';

	/**
	 * Build html.
	 *
	 * @param string $content
	 * @return string
	 */
	protected function build_html( $content ) {

		$attr = $this->attributes;

		$content_rule = isset( $attr['contentRule'] ) ? $attr['contentRule'] : '';

		if ( empty( $content_rule ) ) {
			return '';
		}

		$rule_post = get_post(
			$content_rule
		);

		if ( empty( $rule_post ) ) {
			return;
		}

		if ( 'publish' !== $rule_post->post_status ) {
			return;
		}

		$access_rule  = json_decode( $rule_post->post_content, true );
		$show_content = false;

		if ( urcr_is_access_rule_enabled( $access_rule ) ) {

			// Verify if required params are available.
			if ( empty( $access_rule['logic_map'] ) || empty( $access_rule['actions'] ) ) {
				$show_content = true;
			} elseif ( ! is_array( $access_rule['logic_map'] ) ) {
				$show_content = true;
			} elseif ( empty( $access_rule['logic_map']['conditions'] ) || empty( $access_rule['logic_map']['conditions'] ) ) {
				$show_content = true;
			} elseif ( urcr_is_access_rule_enabled( $access_rule ) && urcr_is_action_specified( $access_rule ) ) {

				$should_allow_access = urcr_is_allow_access( $access_rule['logic_map'] );

				$access_control = isset( $access_rule['actions'][0]['access_control'] ) && ! empty( $access_rule['actions'][0]['access_control'] ) ? $access_rule['actions'][0]['access_control'] : 'access';

				$show_content = ! ( ( true === $should_allow_access && 'restrict' === $access_control ) || ( false === $should_allow_access && 'access' === $access_control ) );
			}
		} else {
			$show_content = true;
		}

		if ( $show_content ) {

			return $content;
		} else {

			$enable_custom_message = isset( $attr['enableCustomRestrictionMessage'] ) ? $attr['enableCustomRestrictionMessage'] : false;

			if ( $enable_custom_message ) {

				$custom_message = isset( $attr['CustomRestrictionMessage'] ) ? $attr['CustomRestrictionMessage'] : '';

				return $custom_message;
			} else {

				$message = get_option( 'user_registration_content_restriction_message', __( 'This content is restricted!', 'user-registration' ) );

				return $message;
			}
		}
	}
}
