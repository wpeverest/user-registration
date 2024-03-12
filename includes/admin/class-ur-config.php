<?php
/**
 * Configuration file.
 *
 * @class    UR_Config
 * @version  1.0.0
 * @package  UserRegistration/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Config Class.
 */
class UR_Config {

	/**
	 * User Registration Form Grid.
	 *
	 * @var int FOrm Grid.
	 */
	public static $ur_form_grid = 3;

	/**
	 * Default active grid.
	 *
	 * @var int default active grid.
	 */
	public static $default_active_grid = 1;
}
