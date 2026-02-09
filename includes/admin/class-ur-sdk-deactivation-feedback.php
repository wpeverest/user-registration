<?php
/**
 * User Registration - ThemeGrill SDK deactivation feedback
 *
 * Customizes the ThemeGrill SDK uninstall feedback popup for User Registration.
 * The actual popup is rendered and handled by the SDK (UninstallFeedback module).
 *
 * @package UserRegistration\Admin
 * @since   2.3.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'UR_SDK_Deactivation_Feedback', false ) ) {

	/**
	 * UR_SDK_Deactivation_Feedback Class
	 */
	class UR_SDK_Deactivation_Feedback {

		/**
		 * Class constructor.
		 * Registers filters to customize the ThemeGrill SDK deactivation popup for User Registration.
		 */
		public function __construct() {
			add_filter( 'user_registration_feedback_deactivate_button_submit', array( $this, 'button_submit_label' ) );
			add_filter( 'user_registration_feedback_deactivate_button_cancel', array( $this, 'button_cancel_label' ) );
			add_filter( 'user_registration_feedback_deactivate_options', array( $this, 'deactivate_options' ) );
			add_filter( 'user_registration_feedback_deactivate_options_skip_randomize', '__return_true' );
			// Register labels filter early so it runs when SDK applies it on init; also patch labels after SDK loads as fallback.
			add_filter( 'themegrill_sdk_labels', array( $this, 'deactivate_options_labels' ), 999, 1 );
			add_action( 'init', array( $this, 'patch_sdk_labels_after_init' ), 15 );
		}

		/**
		 * Submit button label (matches previous popup wording).
		 *
		 * @param string $label Default label.
		 * @return string
		 */
		public function button_submit_label( $label ) {
			return __( 'Submit & Deactivate', 'user-registration' );
		}

		/**
		 * Cancel / Skip button label (matches previous popup wording).
		 *
		 * @param string $label Default label.
		 * @return string
		 */
		public function button_cancel_label( $label ) {
			return __( 'Skip & Deactivate', 'user-registration' );
		}

		/**
		 * Deactivation feedback options (structure only). Labels are set via themegrill_sdk_labels.
		 * 1. Found bugs or errors [R] → What bugs did you run into? [IF]
		 * 2. Confusing to use [R] → What feature felt confusing? [IF]
		 * 3. I no longer need the plugin [R]
		 * 4. Other [R] → Tell us your reason [IF] (SDK adds id999)
		 *
		 * @param array $options Default plugin options (id3–id6).
		 * @return array
		 */
		public function deactivate_options( $options ) {
			return array(
				'id3'   => array(
					'id'   => 3,
					'type' => 'textarea',
				),
				'id4'   => array(
					'id'   => 4,
					'type' => 'textarea',
				),
				'id5'   => array(
					'id'   => 5,
				),
				'id999' => array(
					'id'   => 999,
					'type' => 'textarea',
				),
			);
		}

		/**
		 * Labels for deactivation options (titles and placeholders).
		 *
		 * @param array $labels ThemeGrill SDK labels.
		 * @return array
		 */
		public function deactivate_options_labels( $labels ) {
			if ( ! isset( $labels['uninstall']['options'] ) || ! is_array( $labels['uninstall']['options'] ) ) {
				return $labels;
			}

			$labels['uninstall']['options']['id3'] = array(
				'title'       => __( 'Found bugs or errors', 'user-registration' ),
				'placeholder' => __( 'What bugs did you run into?', 'user-registration' ),
			);
			$labels['uninstall']['options']['id4'] = array(
				'title'       => __( 'Confusing to use', 'user-registration' ),
				'placeholder' => __( 'What feature felt confusing?', 'user-registration' ),
			);
			$labels['uninstall']['options']['id5'] = array(
				'title'       => __( 'I no longer need the plugin', 'user-registration' ),
				'placeholder' => '',
			);
			$labels['uninstall']['options']['id999'] = array(
				'title'       => __( 'Other', 'user-registration' ),
				'placeholder' => __( 'Tell us your reason', 'user-registration' ),
			);

			return $labels;
		}

		/**
		 * Patch ThemeGrill SDK labels after init so custom option labels are applied.
		 * The SDK applies themegrill_sdk_labels in Loader::init() on init priority 10.
		 * This runs at priority 15 and directly sets Loader::$labels so our options show correctly.
		 */
		public function patch_sdk_labels_after_init() {
			if ( ! class_exists( 'ThemeGrillSDK\Loader' ) ) {
				return;
			}
			$our_options = array(
				'id3'   => array(
					'title'       => __( 'Found bugs or errors', 'user-registration' ),
					'placeholder' => __( 'What bugs did you run into?', 'user-registration' ),
				),
				'id4'   => array(
					'title'       => __( 'Confusing to use', 'user-registration' ),
					'placeholder' => __( 'What feature felt confusing?', 'user-registration' ),
				),
				'id5'   => array(
					'title'       => __( 'I no longer need the plugin', 'user-registration' ),
					'placeholder' => '',
				),
				'id999' => array(
					'title'       => __( 'Other', 'user-registration' ),
					'placeholder' => __( 'Tell us your reason', 'user-registration' ),
				),
			);
			if ( isset( \ThemeGrillSDK\Loader::$labels['uninstall']['options'] ) && is_array( \ThemeGrillSDK\Loader::$labels['uninstall']['options'] ) ) {
				\ThemeGrillSDK\Loader::$labels['uninstall']['options'] = array_merge(
					\ThemeGrillSDK\Loader::$labels['uninstall']['options'],
					$our_options
				);
			}
		}
	}

	new UR_SDK_Deactivation_Feedback();
}
