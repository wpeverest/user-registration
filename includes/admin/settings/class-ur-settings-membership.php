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
            add_filter( "user_registration_get_sections_{$this->id}",  array( $this, 'get_sections_callback' ), 1, 1 );
            add_filter( "user_registration_get_settings_{$this->id}", array( $this, 'get_settings_callback' ), 1, 1 );
        }
        /**
         * Filter to provide sections submenu for membership settings.
         */
        public function get_sections_callback( $sections ) {
            $sections[ 'general' ] = __( 'General', 'user-registration' );
            $sections[ 'content-rules' ] = __( 'Content Rules', 'user-registration' );
            return $sections;
        }
        /**
         * Filter to provide sections UI for membership settings.
         */
        public function get_settings_callback( $settings ) {
            global $current_section;
            if( 'general' === $current_section ) {
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
                                'title'    => __( 'Membership', 'user-registration' ),
                                'type'     => 'card',
                                'desc'     => '',
								'before_desc'     => sprintf( 
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
                                            'automatic' => __('Renew Automatically', 'user-registration'),
                                            'manual' => __('Renew Manually', 'user-registration')
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
			$access_rules_list_link = admin_url( 'admin.php?page=user-registration-content-restriction' );
			$link_html              = sprintf( __( 'Go to <a href="%s">Content Rules page</a> for advanced restrictions', 'user-registration' ), $access_rules_list_link );

			$access_options = array( 'All Logged In Users', 'Choose Specific Roles', 'Guest Users' );

			if ( ur_check_module_activation( 'membership' ) ) {
				$access_options[] = 'Memberships';
			}

			return apply_filters(
				'user_registration_content_restriction_settings',
				array(
					'title'    => __( 'Content Restriction Settings', 'user-registration' ),
					'desc'     => UR_PRO_ACTIVE ? $link_html : '',
					'sections' => array(
						'user_registration_site_restriction_settings' => array(
							'title'    => __( 'Whole Site Restriction', 'user-registration' ),
							'type'     => 'card',
							'desc'     => '',
							'settings' => array(
								array(
									'row_class' => 'urcr_enable_disable urcr_whole_site_access_enable',
									'title'     => __( 'Enable Whole Site Restriction', 'user-registration' ),
									'desc'      => __( 'Check this option to restrict your whole site. ', 'user-registration' ),
									'id'        => 'user_registration_content_restriction_whole_site_access',
									'default'   => 'no',
									'desc_tip'  => true,
									'type'      => 'toggle',
									'autoload'  => false,
								),
							),
						),
						'user_registration_content_restriction_settings' => array(
							'title'    => __( 'Global Restriction Settings', 'user-registration' ),
							'type'     => 'card',
							'desc'     => sprintf( __( 'These settings affect whole site restriction as well as individual page/post restriction if enabled. <a href="%1$s" target="_blank" style="text-decoration: underline;" >Learn More.</a>', 'user-registration' ), esc_url_raw( 'https://docs.wpuserregistration.com/docs/content-restriction/' ) ),
							'settings' => array(
								array(
									'row_class' => 'urcr_content_restriction_allow_access_to',
									'title'     => __( 'Allow Access To', 'user-registration' ),
									'desc'      => __( 'Select Option To Allow Access To', 'user-registration' ),
									'id'        => 'user_registration_content_restriction_allow_access_to',
									'type'      => 'select',
									'class'     => 'ur-enhanced-select',
									'css'       => '',
									'desc_tip'  => true,
								    'options'   => $access_options,
								),

								array(
									'row_class' => 'urcr_content_restriction_allow_access_to_roles',
									'title'     => __( 'Select Roles', 'user-registration' ),
									'desc'      => __( 'The roles selected here will have access to restricted content.', 'user-registration' ),
									'id'        => 'user_registration_content_restriction_allow_to_roles',
									'default'   => array( 'administrator' ),
									'type'      => 'multiselect',
									'class'     => 'ur-enhanced-select',
									'css'       => 'min-width: 350px; ' . ( '1' != get_option( 'user_registration_content_restriction_allow_access_to', '0' ) ) ? 'display:none;' : '',
									'desc_tip'  => true,
									'options'   => ur_get_all_roles(),
								),

								array(
									'title'    => __( 'Restricted Content Message', 'user-registration' ),
									'desc'     => __( 'The message you would like to display in restricted content.', 'user-registration' ),
									'id'       => 'user_registration_content_restriction_message',
									'type'     => 'tinymce',
									'default'  => 'This content is restricted!',
									'css'      => '',
									'show-smart-tags-button' => false,
									'desc_tip' => true,
								),
							),
						),
					),
				)
			);
		}
    }
}

//Backward Compatibility.
return method_exists( 'UR_Settings_Membership', 'get_instance' ) ? UR_Settings_Membership::get_instance() : new UR_Settings_Membership();
