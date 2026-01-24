<?php
/**
 * Helper setup
 *
 * @package Helper
 * @since  1.0.0
 */

namespace WPEverest\URM\ContentDrip;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Helper' ) ) :

	/**
	 * Helper masteriyo integration Clas s
	 *
	 */
	class Helper {
		public static function global_default_message() {
			$default_message = '<h3>' . __( 'Unlocking Soon...', 'user-registration' ) . '</h3>
<p>' . __(
				'This content is not available yet. It will unlock {{urm_drip_time}}.',
				'user-registration'
			) . '</p><p>' . __( 'Please check back later to continue.', 'user-registration' ) . '</p>';

			return $default_message;
		}
	}
endif;
