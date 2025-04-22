<?php
/**
 * Abstract UR_Nav_Menu_Item Class
 *
 * @since v5.2.0
 * @package  UserRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Login_Logout_Nav_Menu_Item Class
 */
class UR_Login_Logout_Nav_Menu_Item extends UR_Nav_Menu_Item {
	protected $menu_item_prefix = 'ur_login_logout_';
	protected function get_title() {
		return __('Login|Logout', 'user-registration');
	}
	/**
	 * Get menu item field options to modify via menu editor UI.
	 * @return array[]
	 */
	protected function get_fields()
	{
		return array(
			'login_label' => array(
				'label' => 'Login Label',
				'type' => 'text',
				'default' => 'Login',
			),
			'logout_label' => array(
				'label' => 'Logout Label',
				'type' => 'text',
				'default' => 'Logout',
			),
		);
	}
}
new UR_Login_Logout_Nav_Menu_Item();
