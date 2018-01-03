<?php
/**
 * Configure Email
 *
 * @class    UR_Settings_Email_Configure
 * @extends  UR_Settings_Email
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_Settings_Email_Configure', false ) ) :

/**
 * UR_Settings_Email_Configure Class.
 */
class UR_Settings_Email_Configure extends UR_Settings_Email {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id             = 'email_configure';
		$this->title          = __( 'Configure Emails', 'user-registration' );
	}

}
endif;

return new UR_Settings_Email_Configure();
