<?php
/**
 * Class UR_Settings_Membership
 *
 * Handles the membership related settings for the User Registration & Membership plugin.
 *
 * This class is responsible for:
 * - Membership Settings.
 * - Content Restriction Settings.
 *
 * @package   UserRegistration\Admin
 * @version   5.0.0
 * @since     5.0.0
 */
if ( ! class_exists( 'UR_Settings_Membership' ) ) {
	/**
	 * UR_Settings_Membership Class
	 */
	class UR_Settings_Membership extends UR_Settings_Page {
		private static $_instance = null;
		/**
		 * Constructor.
		 */
		private function __construct() {
			$this->id    = 'membership';
			$this->label = __( 'Membership', 'user-registration' );
			parent::__construct();
			$this->handle_hooks();
		}
		public static function get_instance() {
			if ( null === self::$_instance ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}
		/**
		 * Register hooks for submenus and section UI.
		 * @return void
		 */
		public function handle_hooks() {
			add_filter( "user_registration_get_sections_{$this->id}", array( $this, 'get_sections_callback' ), 1, 1 );
			add_filter( "user_registration_get_settings_{$this->id}", array( $this, 'get_settings_callback' ), 1, 1 );
		}
		/**
		 * Filter to provide sections submenu for membership settings.
		 */
		public function get_sections_callback( $sections ) {
			$sections['general']       = __( 'General', 'user-registration' );
			$sections['content-rules'] = __( 'Content Restriction', 'user-registration' );

			return $sections;
		}
		/**
		 * Filter to provide sections UI for membership settings.
		 */
		public function get_settings_callback( $settings ) {
			global $current_section;
			if ( 'general' === $current_section ) {
				/**
				 * Filter to add the options on settings.
				 *
				 * @param array Options to be enlisted.
				 */
				$settings = apply_filters(
					'user_registration_membership_settings',
					array(
						'title'    => '',
						'sections' => array(
							'membership_settings' => array(
								'title'    => __( 'General', 'user-registration' ),
								'type'     => 'card',
								'desc'     => sprintf(
									__( '<strong>Membership page setting has moved.</strong> Configure your membership page <a href="%s">here</a>.', 'user-registration' ),
									admin_url( 'admin.php?page=user-registration-settings&tab=general&section=pages' )
								),
								'settings' => array(
									array(
										'title'    => __( 'Renewal Behaviour', 'user-registration' ),
										'desc'     => __( 'Choose how membership subscriptions are renewed, automatically through the payment provider or manually by the user', 'user-registration' ),
										'id'       => 'user_registration_renewal_behaviour',
										'type'     => 'select',
										'default'  => 'automatic',
										'class'    => 'ur-enhanced-select',
										'css'      => '',
										'options'  => array(
											'automatic' => __( 'Renew Automatically', 'user-registration' ),
											'manual'    => __( 'Renew Manually', 'user-registration' ),
										),
										'desc_tip' => true,
									),
								),
							),
						),
					)
				);
			} elseif ( 'content-rules' === $current_section ) {
				$settings = $this->urcr_settings();
			}
			return $settings;
		}

		public function urcr_settings() {
			// Build sections array
			$sections = array();

			$default_message = '<h3>' . __( 'Membership Required', 'user-registration' ) . '</h3>
<p>' . __( 'This content is available to members only.', 'user-registration' ) . '</p>
<p>' . __( 'Sign up to unlock access or log in if you already have an account.', 'user-registration' ) . '</p>
<p>{{sign_up}} {{log_in}}</p>';
			if ( class_exists( 'URCR_Admin_Assets' ) ) {
				$default_message = URCR_Admin_Assets::get_default_message();
			}

			$global_rule_id   = get_option( 'urcr_global_rule_id', '' );
			$content_rule_url = admin_url( 'admin.php' ) . '?page=user-registration-content-restriction';
			if ( ! empty( $global_rule_id ) ) {
				$content_rule_url .= '&id=' . $global_rule_id;
			}
			$sections['user_registration_content_restriction_settings'] = array(
				'title'    => __( 'Content Restriction', 'user-registration' ),
				'type'     => 'card',
				'settings' => array(
					array(
						'title'                            => __( 'Global Restriction Message', 'user-registration' ),
						'desc'                             => __( ' Default message for all restricted content.', 'user-registration' ),
						'id'                               => 'user_registration_content_restriction_message',
						'type'                             => 'tinymce',
						'default'                          => $default_message,
						'css'                              => '',
						'show-smart-tags-button'           => true,
						'show-ur-registration-form-button' => false,
						'show-reset-content-button'        => false,
						'desc_tip'                         => true,
					),
				),
			);
			$is_new_installation                                        = ur_string_to_bool( get_option( 'urm_is_new_installation', '' ) );
			if ( $is_new_installation ) {
				$sections['user_registration_content_restriction_settings']['desc'] = sprintf( __( '<strong>The Global Restriction setting has moved.</strong> You can now manage it <a href="%1$s" target="_blank" style="text-decoration: underline;" >here.</a>', 'user-registration' ), esc_url_raw( $content_rule_url ) );
			}

			return apply_filters(
				'user_registration_content_restriction_settings',
				array(
					'title'    => __( 'Content Restriction Settings', 'user-registration' ),
					'desc'     => '',
					'sections' => $sections,
				)
			);
		}
	}
}

//Backward Compatibility.
return method_exists( 'UR_Settings_Membership', 'get_instance' ) ? UR_Settings_Membership::get_instance() : new UR_Settings_Membership();
