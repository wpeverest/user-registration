<?php
namespace WPEverest\URM\DiviBuilder;

use WPEverest\URM\DiviBuilder\Modules\ContentRestriction;
use WPEverest\URM\DiviBuilder\Modules\RegistrationForm;
use WPEverest\URM\DiviBuilder\Modules\LoginForm;
use WPEverest\URM\DiviBuilder\Modules\MyAccount;
use WPEverest\URM\DiviBuilder\Modules\EditPassword;
use WPEverest\URM\DiviBuilder\Modules\EditProfile;
use WPEverest\URM\DiviBuilder\Modules\MembershipGroups;
use WPEverest\URM\DiviBuilder\Modules\MembershipThankYou;

if ( file_exists( UR()->plugin_path() . '/vendor/autoload.php' ) ) {
	require_once UR()->plugin_path() . '/vendor/autoload.php';
}

defined( 'ABSPATH' ) || exit;

/**
 * Builder.
 *
 * @since xx.xx.xx
 */
class Builder {

	/**
	 * Holds single instance of the class.
	 *
	 * @var null|static
	 */
	private static $instance = null;

	/**
	 * Get instance of the class.
	 *
	 * @return static
	 */
	final public static function init() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since xx.xx.xx
	 */
	public function __construct() {
		$this->setup();
	}
	/**
	 * Init.
	 *
	 * @since xx.xx.xx
	 */
	public function setup() {

		if ( ! function_exists( 'urm_is_divi_active' ) || ! urm_is_divi_active() ) {
			return;
		}

		add_action( 'et_builder_ready', array( $this, 'register_divi_builder' ) );
	}

	/**
	 * Function to check whether the divi module is loaded or not.
	 *
	 * @since xx.xx.xx
	 */
	public function register_divi_builder() {
		if ( ! class_exists( 'ET_Builder_Module' ) ) {
			return;
		}

		$modules = apply_filters(
			'urm_divi_modules',
			array(
				'registration-form'    => RegistrationForm::class,
				'login-form'           => LoginForm::class,
				'myaccount'            => MyAccount::class,
				'edit-profile'         => EditProfile::class,
				'edit-password'        => EditPassword::class,
				'membership-groups'    => MembershipGroups::class,
				'membership-thank-you' => MembershipThankYou::class,
				'content-restriction'  => ContentRestriction::class,
			)
		);

		foreach ( $modules as $module ) {
			new $module();
		}
	}
}
