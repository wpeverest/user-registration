<?php
/**
 * Setup menus in WP admin.
 *
 * @class    UR_Admin_Menus
 * @version  1.0.0
 * @package  UserRegistration/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'UR_Admin_Menus', false ) ) :

	/**
	 * UR_Admin_Menus Class.
	 */
	class UR_Admin_Menus {

		/**
		 * UR_Admin_Menus Constructor.
		 */
		public function __construct() {

			// Add menus.
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );
			add_action( 'admin_menu', array( $this, 'settings_menu' ), 60 );
			add_action( 'admin_menu', array( $this, 'status_menu' ), 61 );
			add_action( 'admin_menu', array( $this, 'add_registration_menu' ), 50 );
			if ( apply_filters( 'user_registration_show_addons_page', true ) ) {
				add_action( 'admin_menu', array( $this, 'addons_menu' ), 70 );
			}
			if ( ! ur_get_license_plan() ) {
				add_action( 'admin_menu', array( $this, 'user_registration_upgrade_to_pro_menu' ), 80 );
			}

			// Set screens.
			add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 10, 3 );

			// Add endpoints custom URLs in Appearance > Menus > Pages.
			add_action( 'admin_head-nav-menus.php', array( $this, 'add_nav_menu_meta_boxes' ) );

			// Add all available upgradable fields.
			add_action( 'ur_after_other_form_fields_printed', array( $this, 'add_upgradable_other_fields' ) ); // Adds fields in the `Extra Fields` section.
			add_action( 'user_registration_extra_fields', array( $this, 'add_upgradable_extra_fields' ) );
		}

		/**
		 * Add Upgradable other fields.
		 */
		public function add_upgradable_other_fields() {
			$fields = array(
				array(
					'id'          => 'user_registration_file',
					'label'       => 'File Upload',
					'icon'        => 'ur-icon ur-icon-file-upload',
					'field_class' => 'UR_File',
					'plan'        => 'Personal Plan',
					'slug'        => 'file-upload',
					'name'        => __( 'User Registration - File Upload', 'user-registration' ),
				),
				array(
					'id'          => 'user_registration_mailchimp',
					'label'       => 'MailChimp',
					'icon'        => 'ur-icon ur-icon-mailchimp',
					'field_class' => 'UR_MailChimp',
					'plan'        => 'Personal Plan',
					'slug'        => 'mailchimp',
					'name'        => __( 'User Registration - Mailchimp', 'user-registration' ),
				),
				array(
					'id'          => 'user_registration_invite_code',
					'label'       => 'Invitation Code',
					'icon'        => 'ur-icon ur-icon-invite-codes',
					'field_class' => 'UR_Form_Field_Invite_Code',
					'plan'        => 'Professional Plan or Plus Plan',
					'slug'        => 'invite-codes',
					'name'        => __( 'User Registration Invite Codes', 'user-registration' ),
				),
				array(
					'id'          => 'user_registration_learndash',
					'label'       => 'LearnDash Course',
					'icon'        => 'ur-icon ur-icon-course',
					'field_class' => 'UR_Form_Field_Learndash_Course',
					'plan'        => 'Professional Plan or Plus Plan',
					'slug'        => 'learndash',
					'name'        => __( 'User Registration LearnDash', 'user-registration' ),
				),
			);

			if ( ! is_plugin_active( 'user-registration-payments/user-registration-payments.php' ) && ! is_plugin_active( 'user-registration-stripe/user-registration-stripe.php' ) ) {
				$fields = array_merge(
					$fields,
					array(
						array(
							'id'          => 'user_registration_stripe_gateway',
							'label'       => 'Stripe Gateway',
							'icon'        => 'ur-icon ur-icon-credit-card',
							'field_class' => 'UR_Form_Field_Stripe_Gateway',
							'plan'        => 'Professional Plan or Plus Plan',
							'slug'        => array( 'payments', 'stripe' ),
							'name'        => array( __( 'User Registration Payments', 'user-registration' ), __( 'User Registration Stripe', 'user-registration' ) ),
						),
					)
				);
			} else {
				if ( ! is_plugin_active( 'user-registration-payments/user-registration-payments.php' ) && is_plugin_active( 'user-registration-stripe/user-registration-stripe.php' ) ) {
					$fields = array_merge(
						$fields,
						array(
							array(
								'id'          => 'user_registration_stripe_gateway',
								'label'       => 'Stripe Gateway',
								'icon'        => 'ur-icon ur-icon-credit-card',
								'field_class' => 'UR_Form_Field_Stripe_Gateway',
								'plan'        => 'Professional Plan or Plus Plan',
								'slug'        => 'payments',
								'name'        => __( 'User Registration Payments', 'user-registration' ),
							),
						)
					);
				} elseif ( is_plugin_active( 'user-registration-payments/user-registration-payments.php' ) && ! is_plugin_active( 'user-registration-stripe/user-registration-stripe.php' ) ) {
					$fields = array_merge(
						$fields,
						array(
							array(
								'id'          => 'user_registration_stripe_gateway',
								'label'       => 'Stripe Gateway',
								'icon'        => 'ur-icon ur-icon-credit-card',
								'field_class' => 'UR_Form_Field_Stripe_Gateway',
								'plan'        => 'Professional Plan or Plus Plan',
								'slug'        => 'stripe',
								'name'        => __( 'User Registration Stripe', 'user-registration' ),
							),
						)
					);
				}
			}

			foreach ( $fields as $field ) {
				if ( 'user_registration_learndash' === $field['id'] ) {
					if ( ! defined( 'LEARNDASH_VERSION' ) ) {
						continue;
					}
				}

				if ( ! class_exists( $field['field_class'] ) ) {
					$this->render_upgradable_field( $field );
				}
			}
		}

			/**
			 * Add Upgradable extra fields.
			 */
		public function add_upgradable_extra_fields() {
			$field_sections = array(
				array(
					'section_title'       => 'Advanced Fields',
					'fields_parent_class' => 'URAF_Admin',
					'plan'                => 'Personal Plan',
					'slug'                => 'advanced-fields',
					'name'                => __( 'User Registration-Advanced Fields', 'user-registration' ),
					'fields'              => array(
						array(
							'id'    => 'user_registration_section_title',
							'label' => 'Section Title',
							'icon'  => 'ur-icon ur-icon-section-title',
						),
						array(
							'id'    => 'user_registration_html',
							'label' => 'HTML',
							'icon'  => 'ur-icon ur-icon-code',
						),
						array(
							'id'    => 'user_registration_timepicker',
							'label' => 'Time Picker',
							'icon'  => 'ur-icon ur-icon-time-picker',
						),
						array(
							'id'    => 'user_registration_phone',
							'label' => 'Phone',
							'icon'  => 'ur-icon ur-icon-phone',
						),
						array(
							'id'    => 'user_registration_wysiwyg',
							'label' => 'WYSIWYG',
							'icon'  => 'ur-icon ur-icon-text-editor',
						),
						array(
							'id'    => 'user_registration_select2',
							'label' => 'Select2',
							'icon'  => 'ur-icon ur-icon-select2',
						),
						array(
							'id'    => 'user_registration_multi_select2',
							'label' => 'Multi Select2',
							'icon'  => 'ur-icon ur-icon-multi-select',
						),
						array(
							'id'    => 'user_registration_profile_picture',
							'label' => 'Profile Picture',
							'icon'  => 'ur-icon ur-icon-user-display-name',
						),
						array(
							'id'    => 'user_registration_range',
							'label' => 'Range',
							'icon'  => 'ur-icon ur-icon-range',
						),
						array(
							'id'    => 'user_registration_custom_url',
							'label' => 'Custom URL',
							'icon'  => 'ur-icon ur-icon-website',
						),
						array(
							'id'    => 'user_registration_hidden',
							'label' => 'Hidden Field',
							'icon'  => 'ur-icon ur-icon-hidden-field',
						),
					),
				),
				array(
					'section_title'       => 'WooCommerce Billing Address',
					'fields_parent_class' => 'URWC_Admin',
					'plan'                => 'Personal Plan',
					'slug'                => 'woocommerce',
					'name'                => __( 'User Registration - WooCommerce', 'user-registration' ),
					'fields'              => array(
						array(
							'id'    => 'user_registration_billing_address_title',
							'label' => 'Billing Address',
							'icon'  => 'ur-icon ur-icon-bill',
						),
						array(
							'id'    => 'user_registration_billing_country',
							'label' => 'Country',
							'icon'  => 'ur-icon ur-icon-flag',
						),
						array(
							'id'    => 'user_registration_billing_first_name',
							'label' => 'First Name',
							'icon'  => 'ur-icon ur-icon-input-first-name',
						),
						array(
							'id'    => 'user_registration_billing_last_name',
							'label' => 'Last Name',
							'icon'  => 'ur-icon ur-icon-input-last-name',
						),
						array(
							'id'    => 'user_registration_billing_company',
							'label' => 'Company',
							'icon'  => 'ur-icon ur-icon-buildings',
						),
						array(
							'id'    => 'user_registration_billing_address_1',
							'label' => 'Address 1',
							'icon'  => 'ur-icon ur-icon-map-one',
						),
						array(
							'id'    => 'user_registration_billing_address_2',
							'label' => 'Address 2',
							'icon'  => 'ur-icon ur-icon-map-two',
						),
						array(
							'id'    => 'user_registration_billing_city',
							'label' => 'Town / City',
							'icon'  => 'ur-icon ur-icon-buildings',
						),
						array(
							'id'    => 'user_registration_billing_state',
							'label' => 'State / County',
							'icon'  => 'ur-icon ur-icon-state',
						),
						array(
							'id'    => 'user_registration_billing_postcode',
							'label' => 'Postcode / Zip',
							'icon'  => 'ur-icon ur-icon-zip-code',
						),
						array(
							'id'    => 'user_registration_billing_email',
							'label' => 'Email',
							'icon'  => 'ur-icon ur-icon-email',
						),
						array(
							'id'    => 'user_registration_billing_phone',
							'label' => 'Phone',
							'icon'  => 'ur-icon ur-icon-phone',
						),
						array(
							'id'    => 'user_registration_separate_shipping',
							'label' => 'Separate Shipping',
							'icon'  => 'ur-icon ur-icon-bill',
						),
					),
				),
				array(
					'section_title'       => 'WooCommerce Shipping Address',
					'fields_parent_class' => 'URWC_Admin',
					'plan'                => 'Personal Plan',
					'slug'                => 'woocommerce',
					'name'                => __( 'User Registration - WooCommerce', 'user-registration' ),
					'fields'              => array(
						array(
							'id'    => 'user_registration_shipping_address_title',
							'label' => 'Shipping Address',
							'icon'  => 'ur-icon ur-icon-bill',
						),
						array(
							'id'    => 'user_registration_shipping_country',
							'label' => 'Country',
							'icon'  => 'ur-icon ur-icon-flag',
						),
						array(
							'id'    => 'user_registration_shipping_first_name',
							'label' => 'First Name',
							'icon'  => 'ur-icon ur-icon-input-first-name',
						),
						array(
							'id'    => 'user_registration_shipping_last_name',
							'label' => 'Last Name',
							'icon'  => 'ur-icon ur-icon-input-last-name',
						),
						array(
							'id'    => 'user_registration_shipping_company',
							'label' => 'Company',
							'icon'  => 'ur-icon ur-icon-buildings',
						),
						array(
							'id'    => 'user_registration_shipping_address_1',
							'label' => 'Address 1',
							'icon'  => 'ur-icon ur-icon-map-one',
						),
						array(
							'id'    => 'user_registration_shipping_address_2',
							'label' => 'Address 2',
							'icon'  => 'ur-icon ur-icon-map-two',
						),
						array(
							'id'    => 'user_registration_shipping_city',
							'label' => 'Town / City',
							'icon'  => 'ur-icon ur-icon-buildings',
						),
						array(
							'id'    => 'user_registration_shipping_state',
							'label' => 'State / County',
							'icon'  => 'ur-icon ur-icon-state',
						),
						array(
							'id'    => 'user_registration_shipping_postcode',
							'label' => 'Postcode / Zip',
							'icon'  => 'ur-icon ur-icon-zip-code',
						),
					),
				),
				array(
					'section_title'       => 'Payment Fields',
					'fields_parent_class' => 'User_Registration_Payments_Admin',
					'plan'                => 'Professional Plan or Plus Plan',
					'slug'                => 'payments',
					'name'                => __( 'User Registration Payments', 'user-registration' ),
					'fields'              => array(
						array(
							'id'    => 'user_registration_single_item',
							'label' => 'Single Item',
							'icon'  => 'ur-icon ur-icon-file-dollar',
						),
						array(
							'id'    => 'user_registration_multiple_choice',
							'label' => 'Multiple Choice',
							'icon'  => 'ur-icon ur-icon-multichoice',
						),
						array(
							'id'    => 'user_registration_total',
							'label' => 'Total',
							'icon'  => 'ur-icon ur-icon-total',
						),
						array(
							'id'    => 'user_registration_quantity',
							'label' => 'Quantity',
							'icon'  => 'ur-icon ur-icon-quantity',
						),
					),
				),
			);

			foreach ( $field_sections as $section ) {
				$class_to_check = $section['fields_parent_class'];

				if ( ! class_exists( $class_to_check ) ) {
					$fields       = $section['fields'];
					$plan         = isset( $section['plan'] ) ? $section['plan'] : '';
					$slug         = isset( $section['slug'] ) ? $section['slug'] : '';
					$name         = isset( $section['name'] ) ? $section['name'] : '';
					$fields_count = count( $fields );

					// Set the same plan for all the section's fields.
					for ( $i = 0; $i < $fields_count; $i++ ) {
						$fields[ $i ]['plan'] = $plan;
						$fields[ $i ]['slug'] = $slug;
						$fields[ $i ]['name'] = $name;
					}

					echo '<h2 class="ur-toggle-heading">' . esc_html( $section['section_title'] ) . '</h2><hr/>';
					echo '<ul id = "ur-upgradables" class="ur-registered-list" > ';
					$this->render_upgradable_fields( $fields );
					echo '</ul >';
				}
			}
		}

			/**
			 * Render multiple upgradable fields.
			 *
			 * @param array $fields Field.
			 */
		public function render_upgradable_fields( $fields ) {
			foreach ( $fields as $field ) {
				$this->render_upgradable_field( $field );
			}
		}

			/**
			 * Render an upgradable field.
			 *
			 * @param array $args Args Data.
			 */
		public function render_upgradable_field( $args ) {
			$id    = $args['id'];
			$icon  = $args['icon'];
			$label = $args['label'];
			$plan  = isset( $args['plan'] ) ? $args['plan'] : '';
			$name  = isset( $args['name'] ) ? ( is_array( $args['name'] ) ? implode( ', and ', $args['name'] ) : $args['name'] ) : '';

			if ( isset( $args['slug'] ) ) {
				if ( is_array( $args['slug'] ) ) {
					$new_args_slug = array();

					foreach ( $args['slug'] as $args_slug ) {
						array_push( $new_args_slug, 'user-registration-' . $args_slug );
					}

					$slug = implode( ' ', $new_args_slug );
				} else {
					$slug = 'user-registration-' . $args['slug'];
				}
			}
			echo '<li id="' . esc_attr( $id ) . '_list " class="ur-registered-item ur-upgradable-field ui-draggable-disabled" data-field-id="' . esc_attr( $id ) . '" data-name="' . esc_attr( $name ) . '" data-plan="' . esc_attr( $plan ) . '" data-slug ="' . esc_attr( $slug ) . '"><span class="' . esc_attr( $icon ) . '"></span>' . esc_html( $label ) . '</li>';
		}

			/**
			 * Returns a base64 URL for the SVG for use in the menu.
			 *
			 * @param  bool $base64 Whether or not to return base64-encoded SVG.
			 * @return string
			 */
		private function get_icon_svg( $base64 = true ) {
			$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path fill="#82878c" d="M27.58 4a27.9 27.9 0 0 0-5.17 4 27 27 0 0 0-4.09 5.08 33.06 33.06 0 0 1 2 4.65A23.78 23.78 0 0 1 24 12.15V18a8 8 0 0 1-5.89 7.72l-.21.05a27 27 0 0 0-1.9-8.16A27.9 27.9 0 0 0 9.59 8a27.9 27.9 0 0 0-5.17-4L4 3.77V18a12 12 0 0 0 9.93 11.82h.14a11.72 11.72 0 0 0 3.86 0h.14A12 12 0 0 0 28 18V3.77zM8 18v-5.85a23.86 23.86 0 0 1 5.89 13.57A8 8 0 0 1 8 18zm8-16a3 3 0 1 0 3 3 3 3 0 0 0-3-3z"/></svg>';

			if ( $base64 ) {
				return 'data:image/svg+xml;base64,' . base64_encode( $svg );
			}

			return $svg;
		}

			/**
			 * Add menu items.
			 */
		public function admin_menu() {

			$registration_page = add_menu_page( 'User Registration', 'User Registration', 'manage_user_registration', 'user-registration', array( $this, 'registration_page' ), $this->get_icon_svg(), '55.8' );

			add_action( 'load-' . $registration_page, array( $this, 'registration_page_init' ) );
			add_submenu_page(
				'user-registration',
				__( 'All Forms', 'user-registration' ),
				__( 'All Forms', 'user-registration' ),
				'manage_user_registration',
				'user-registration',
				array(
					$this,
					'registration_page',
				)
			);
		}

			/**
			 * Loads screen options into memory.
			 */
		public function registration_page_init() {
			global $registration_table_list;

			if ( ! isset( $_GET['add-new-registration'] ) ) {  //phpcs:ignore WordPress.Security.NonceVerification
				$registration_table_list = new UR_Admin_Registrations_Table_List();
				$registration_table_list->process_actions();

				// Add screen option.
				add_screen_option(
					'per_page',
					array(
						'default' => 20,
						'option'  => 'user_registration_per_page',
					)
				);
			}

		}

			/**
			 * Add settings menu item.
			 */
		public function settings_menu() {
			add_submenu_page(
				'user-registration',
				__( 'User Registration settings', 'user-registration' ),
				__( 'Settings', 'user-registration' ),
				'manage_user_registration',
				'user-registration-settings',
				array(
					$this,
					'settings_page',
				)
			);
		}

			/**
			 * Add status menu item.
			 */
		public function status_menu() {
			add_submenu_page(
				'user-registration',
				__( 'User Registration Status', 'user-registration' ),
				__( 'Status', 'user-registration' ),
				'manage_user_registration',
				'user-registration-status',
				array(
					$this,
					'status_page',
				)
			);
		}

			/**
			 * Add new registration menu items.
			 */
		public function add_registration_menu() {
			add_submenu_page(
				'user-registration',
				esc_html__( 'Add New', 'user-registration' ),
				esc_html__( 'Add New', 'user-registration' ),
				'manage_user_registration',
				'add-new-registration',
				array(
					$this,
					'add_registration_page',
				)
			);
		}

			/**
			 * Upgrade to pro menu items.
			 */
		public function user_registration_upgrade_to_pro_menu() {
			add_submenu_page(
				'user-registration',
				esc_html__( 'Upgrade to Pro', 'user-registration' ),
				sprintf(
					'<span style="color:#FF8C39; font-weight: 600;"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: bottom;" ><rect x="0.5" y="0.5" width="19" height="19" rx="2.5" fill="#FF8C39" stroke="#FF8C39"/><path d="M10 5L13 13H7L10 5Z" fill="#EFEFEF"/><path fill="white" fill-rule="evenodd" d="M5 7L5.71429 13H14.2857L15 7L10 11.125L5 7ZM14.2857 13.5714H5.71427V15H14.2857V13.5714Z" clip-rule="evenodd"/></svg><span style="margin-left:5px;">%s</span></span>',
					esc_html__( 'Upgrade to Pro', 'user-registration' )
				),
				'manage_options',
				esc_url_raw( 'https://wpeverest.com/wordpress-plugins/user-registration/pricing/?utm_source=addons-page&utm_medium=upgrade-button&utm_campaign=ur-upgrade-to-pro' )
			);
		}

			/**
			 * Addons menu item.
			 */
		public function addons_menu() {
			add_submenu_page(
				'user-registration',
				__( 'User Registration extensions', 'user-registration' ),
				sprintf( '<span style="color: rgb(158, 240, 26);"><svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 2 30 30" style="fill: rgb(158, 240, 26);transform: ;msFilter:;vertical-align:middle;"><path d="M11.8,15.24l1.71-1,.57-.33a2.14,2.14,0,0,0,1-1.85V6.76a2,2,0,0,0-.28-1,2.08,2.08,0,0,0-.76-.77l-.56-.33-1.73-1L9.56,2.29a2,2,0,0,0-1-.29,2,2,0,0,0-1,.29L5.26,3.59,3.42,4.68,3,4.94a2.08,2.08,0,0,0-.76.77,2.13,2.13,0,0,0-.27,1v5.3a2,2,0,0,0,.27,1.06,2.13,2.13,0,0,0,.76.79l.45.26,1.84,1.07,2.23,1.3a2,2,0,0,0,1,.28,2.22,2.22,0,0,0,1-.26Z"/><path d="M29.78,5.71A2.16,2.16,0,0,0,29,4.94l-.56-.33-1.74-1L24.5,2.29a2,2,0,0,0-1-.29,2,2,0,0,0-1,.29l-2.23,1.3L18.37,4.68l-.45.26a2.08,2.08,0,0,0-.76.77,2.13,2.13,0,0,0-.27,1v5.3a1.89,1.89,0,0,0,.27,1.06,2.13,2.13,0,0,0,.76.79l.45.26,1.84,1.07,2.23,1.3a2,2,0,0,0,1,.28,2.16,2.16,0,0,0,1-.26l2.25-1.32,1.71-1,.57-.33a2.3,2.3,0,0,0,.76-.79,2.2,2.2,0,0,0,.27-1.06V6.76A2,2,0,0,0,29.78,5.71Z"/><path d="M21.64,18.12l-.56-.33-1.74-1-2.22-1.3a2,2,0,0,0-1-.29,2,2,0,0,0-1,.29l-2.23,1.3L11,17.85l-.45.27a2.08,2.08,0,0,0-.76.77,2.14,2.14,0,0,0-.28,1.05v5.3a1.93,1.93,0,0,0,.28,1.05,2.06,2.06,0,0,0,.76.79l.45.27,1.84,1.07,2.23,1.29a2,2,0,0,0,1,.29,2.28,2.28,0,0,0,1-.26l2.25-1.32,1.71-1,.57-.34a2.21,2.21,0,0,0,.76-.79,2.13,2.13,0,0,0,.27-1.05v-5.3a2,2,0,0,0-.28-1.05A2.16,2.16,0,0,0,21.64,18.12Z"/></svg><span style="margin-left:5px;">%s</span></span>', esc_html__( 'Extensions', 'user-registration' ) ),
				'manage_user_registration',
				'user-registration-addons',
				array(
					$this,
					'addons_page',
				)
			);
		}

			/**
			 * Validate screen options on update.
			 *
			 * @param mixed $status Status.
			 * @param mixed $option Option.
			 * @param mixed $value Value.
			 */
		public function set_screen_option( $status, $option, $value ) {
			if ( in_array( $option, array( 'user_registration_per_page' ), true ) ) {
				return $value;
			}

			return $status;
		}

			/**
			 * Init the settings page.
			 */
		public function registration_page() {
			global $registration_table_list;
			$registration_table_list->display_page();
		}

			/**
			 * Init the add registration page.
			 */
		public function add_registration_page() {
			$form_id   = isset( $_GET['edit-registration'] ) ? absint( $_GET['edit-registration'] ) : 0; //phpcs:ignore WordPress.Security.NonceVerification
			$form_data = ( $form_id ) ? UR()->form->get_form( $form_id ) : array();

			$save_label = __( 'Create Form', 'user-registration' );
			if ( ! empty( $form_data ) ) {
				$save_label   = __( 'Update form', 'user-registration' );
				$preview_link = add_query_arg(
					array(
						'ur_preview' => 'true',
						'form_id'    => $form_id,
					),
					home_url()
				);
			}

			if ( isset( $_GET['onboarding-skipped'] ) ) {
				update_option( 'user_registration_onboarding_skipped', true );
			}
			wp_enqueue_script( 'ur-setup' );
			wp_localize_script(
				'ur-setup',
				'ur_setup_params',
				array(
					'ajax_url'                     => admin_url( 'admin-ajax.php' ),
					'create_form_nonce'            => wp_create_nonce( 'user_registration_create_form' ),
					'template_licence_check_nonce' => wp_create_nonce( 'user_registration_template_licence_check' ),
					'captcha_setup_check_nonce'    => wp_create_nonce( 'user_registration_captcha_setup_check' ),
					'i18n_form_name'               => esc_html__( 'Give it a name.', 'user-registration' ),
					'i18n_form_error_name'         => esc_html__( 'You must provide a Form name', 'user-registration' ),
					'i18n_install_only'            => esc_html__( 'Activate Plugins', 'user-registration' ),
					'i18n_activating'              => esc_html__( 'Activating', 'user-registration' ),
					'i18n_activating_text'         => esc_html__( 'Please wait until the plugin is being activated', 'user-registration' ),
					'i18n_install_activate'        => esc_html__( 'Install & Activate', 'user-registration' ),
					'i18n_installing'              => esc_html__( 'Installing', 'user-registration' ),
					'i18n_ok'                      => esc_html__( 'OK', 'user-registration' ),
					'upgrade_url'                  => apply_filters( 'user_registration_upgrade_url', 'https://wpeverest.com/wordpress-plugins/user-registration/pricing/?utm_source=form-template&utm_medium=button&utm_campaign=evf-upgrade-to-pro' ),
					'upgrade_button'               => esc_html__( 'Upgrade Plan', 'user-registration' ),
					'upgrade_message'              => esc_html__( 'This template requires premium addons. Please upgrade to the Premium plan to unlock all these awesome Templates.', 'user-registration' ),
					'upgrade_title'                => esc_html__( 'is a Premium Template', 'user-registration' ),
					'i18n_form_ok'                 => esc_html__( 'Continue', 'user-registration' ),
					'i18n_form_placeholder'        => esc_html__( 'Untitled Form', 'user-registration' ),
					'i18n_form_title'              => esc_html__( 'Uplift your form experience to the next level.', 'user-registration' ),
					'download_failed'              => esc_html__( 'Download Failed. Please download and activate addon manually.', 'user-registration' ),
					'download_successful_title'    => esc_html__( 'Installation Successful.', 'user-registration' ),
					'download_successful_message'  => esc_html__( 'Addons have been installed and Activated. You have to reload the page.', 'user-registration' ),
					'save_changes_text'            => esc_html__( 'Save Changes and Reload', 'user-registration' ),
					'save_changes_warning'         => esc_html__( 'Save changes before activating the plugin', 'user-registration' ),
					'reload_text'                  => esc_html__( 'Just Reload', 'user-registration' ),
				)
			);
			if ( isset( $_GET['edit-registration'] ) ) {
				// Forms view.
				include_once dirname( __FILE__ ) . '/views/html-admin-page-forms.php';
			} else {
				UR_Admin_Form_Templates::load_template_view();
			}

			// Forms view.
			include_once dirname( __FILE__ ) . '/views/html-admin-page-forms.php';
		}

			/**
			 * Init the settings page.
			 */
		public function settings_page() {
			UR_Admin_Settings::output();
		}

			/**
			 * Init the status page.
			 */
		public function status_page() {
			UR_Admin_Status::output();
		}

			/**
			 * Init the addons page.
			 */
		public function addons_page() {
			UR_Admin_Addons::output();
		}

			/**
			 * Add custom nav meta box.
			 *
			 * Adapted from http://www.johnmorrisonline.com/how-to-add-a-fully-functional-custom-meta-box-to-wordpress-navigation-menus/.
			 */
		public function add_nav_menu_meta_boxes() {
			add_meta_box(
				'user_registration_endpoints_nav_link',
				__( 'User Registration endpoints', 'user-registration' ),
				array(
					$this,
					'nav_menu_links',
				),
				'nav-menus',
				'side',
				'low'
			);
		}

			/**
			 * Output menu links.
			 */
		public function nav_menu_links() {
			// Get items from account menu.
			$endpoints = ur_get_account_menu_items();

			// Remove dashboard item.
			if ( isset( $endpoints['dashboard'] ) ) {
				unset( $endpoints['dashboard'] );
			}

			// Include missing lost password.
			$endpoints['ur-lost-password'] = __( 'Lost password', 'user-registration' );

			$endpoints = apply_filters( 'user_registration_custom_nav_menu_items', $endpoints );

			?>
			<div id="posttype-user-registration-endpoints" class="posttypediv">
				<div id="tabs-panel-user-registration-endpoints" class="tabs-panel tabs-panel-active">
					<ul id="user-registration-endpoints-checklist" class="categorychecklist form-no-clear">
			<?php
			$i = - 1;
			foreach ( $endpoints as $key => $value ) :
				?>
							<li>
								<label class="menu-item-title">
									<input type="checkbox" class="menu-item-checkbox"
										name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-object-id]"
										value="<?php echo esc_attr( $i ); ?>"/> <?php echo esc_html( $value ); ?>
								</label>
								<input type="hidden" class="menu-item-type"
									name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-type]" value="custom"/>
								<input type="hidden" class="menu-item-title"
									name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-title]"
									value="<?php echo esc_html( $value ); ?>"/>
								<input type="hidden" class="menu-item-url"
									name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-url]"
									value="<?php echo esc_url( ur_get_account_endpoint_url( $key ) ); ?>"/>
								<input type="hidden" class="menu-item-classes"
									name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-classes]"/>
							</li>
						<?php
						$i --;
						endforeach;
			?>
					</ul>
				</div>
				<p class="button-controls">
					<span class="list-controls">
					<a href="<?php echo esc_url( admin_url( 'nav-menus.php?page-tab=all&selectall=1#posttype-user-registration-endpoints' ) ); ?>"
					class="select-all"><?php esc_html_e( 'Select all', 'user-registration' ); ?></a>
					</span>
					<span class="add-to-menu">
					<input type="submit" class="button-secondary submit-add-to-menu right"
						value="<?php esc_attr_e( 'Add to menu', 'user-registration' ); ?>"
						name="add-post-type-menu-item" id="submit-posttype-user-registration-endpoints">
					<span class="spinner"></span>
					</span>
				</p>
			</div>
				<?php
		}

			/**
			 * Get Edit Form Field.
			 *
			 * @param object $form_data Form Data.
			 *
			 * @throws Exception Throws exception if error in json.
			 */
		private function get_edit_form_field( $form_data ) {

			if ( ! empty( $form_data ) ) {
				$form_data_content = $form_data->post_content;
				$form_row_ids      = get_post_meta( $form_data->ID, 'user_registration_form_row_ids', true );
			} else {
				$form_data_content = '';
				$form_row_ids      = '';
			}

			try {
				$form_data_content = str_replace( '"noopener noreferrer"', "'noopener noreferrer'", $form_data_content );
				$form_data_array   = json_decode( $form_data_content );

				if ( json_last_error() !== JSON_ERROR_NONE ) {
					throw new Exception( '' );
				}
			} catch ( Exception $e ) {
				$form_data_array = array();
			}

			try {
				$form_row_ids_array = json_decode( $form_row_ids );

				if ( json_last_error() !== JSON_ERROR_NONE ) {
					throw new Exception( '' );
				}
			} catch ( Exception $e ) {
				$form_row_ids_array = array();
			}

			echo '<div class="ur-selected-inputs">';
			echo '<div class="ur-builder-wrapper-content">';
			?>
			<div class="ur-builder-header">
				<div class="user-registration-editable-title ur-form-name-wrapper ur-my-4">
			<?php
			$form_title = isset( $form_data->post_title ) ? trim( $form_data->post_title ) : __( 'Untitled', 'user-registration' );
			?>
					<input name="ur-form-name" id="ur-form-name" type="text" class="user-registration-editable-title__input ur-form-name regular-text menu-item-textbox" value="<?php echo esc_html( $form_title ); ?>" data-editing="false">
					<span id="ur-form-name-edit-button" class="user-registration-editable-title__icon ur-edit-form-name dashicons dashicons-edit"></span>
				</div>
				<div class="ur-builder-header-right">
				<?php do_action( 'user_registration_builder_header_extra', $form_data->ID, $form_data_array ); ?>
				</div>
			</div>
				<?php
				echo '<div class="ur-input-grids">';

				$row_id  = 0;
				$last_id = 0;

				foreach ( $form_data_array as $index => $rows ) {
					$row_id  = ( ! empty( $form_row_ids ) ) ? $form_row_ids_array[ $index ] : $index;
					$last_id = ( absint( $row_id ) > $last_id ) ? absint( $row_id ) : $last_id;

					$grid_count = count( $rows );

					$grid_one   = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path d="M28,6V26H4V6H28m2-2H2V28H30V4Z"/></svg>';
					$grid_two   = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path d="M17,4H2V28H30V4ZM4,26V6H15V26Zm24,0H17V6H28Z"/></svg>';
					$grid_three = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path d="M22,4H2V28H30V4ZM4,26V6h6V26Zm8,0V6h8V26Zm16,0H22V6h6Z"/></svg>';

					$svg_args = array(
						'svg'   => array(
							'class'           => true,
							'aria-hidden'     => true,
							'aria-labelledby' => true,
							'role'            => true,
							'xmlns'           => true,
							'width'           => true,
							'height'          => true,
							'viewbox'         => true, // <= Must be lower case!
						),
						'g'     => array( 'fill' => true ),
						'title' => array( 'title' => true ),
						'path'  => array(
							'd'    => true,
							'fill' => true,
						),
					);
					echo '<div class="ur-single-row"  data-row-id="' . esc_attr( absint( $row_id ) ) . '">';
					?>

				<div class="ur-grids">
					<button type="button" class="ur-edit-grid">
						<?php
						if ( 1 === $grid_count ) {
							echo wp_kses( $grid_one, $svg_args );
						} elseif ( 2 === $grid_count ) {
							echo wp_kses( $grid_two, $svg_args );
						} elseif ( 3 === $grid_count ) {
							echo wp_kses( $grid_three, $svg_args );
						}
						?>
					</button>
					<button type="button" class="dashicons dashicons-no-alt ur-remove-row"></button>
					<div class="ur-toggle-grid-content" style="display:none">
						<small>Select the grid column.</small>
						<div class="ur-grid-selector" data-grid = "1">
							<?php

							echo wp_kses( $grid_one, $svg_args );
							?>
						</div>
						<div class="ur-grid-selector" data-grid = "2">
							<?php echo wp_kses( $grid_two, $svg_args ); ?>
						</div>
						<div class="ur-grid-selector" data-grid = "3">
							<?php echo wp_kses( $grid_three, $svg_args ); ?>
						</div>
					</div>
				</div>

					<?php
					echo '<div class="ur-grid-lists">';

					$grid_id = 0;

					foreach ( $rows as $grid_lists ) {

						$grid_id ++;

						echo '<div ur-grid-id="' . esc_attr( $grid_id ) . '" class="ur-grid-list-item ui-sortable" style="width: 48%; min-height: 70px;">';

						foreach ( $grid_lists as $single_field ) {

							if ( isset( $single_field->field_key ) ) {
								// Hook for fields backward compatibility.
								apply_filters( 'user_registration_form_builder_field_before', $single_field );

								$admin_field = $this->get_admin_field( $single_field );
								echo '<div class="ur-selected-item">';
								echo '<div class="ur-action-buttons"><span title="Clone" class="dashicons dashicons-admin-page ur-clone"></span><span title="Trash" class="dashicons dashicons-trash ur-trash"></span></div>';
								$template = isset( $admin_field['template'] ) ? $admin_field['template'] : '' ; // @codingStandardsIgnoreLine
								echo $template; // phpcs:ignore
								echo '</div>';
							}
						}

						if ( count( $grid_lists ) === 0 ) {
							echo '<div class="user-registration-dragged-me">
						<div class="user-registration-dragged-me-text"><p>' . esc_html__( 'Drag your first form item here.', 'user-registration' ) . '</p></div>
						</div>';
						}

						echo '</div>';
					}

					echo '</div>';
					echo '</div>';

				}
				echo '<button type="button" class="button button-primary dashicons dashicons-plus-alt ur-add-new-row" data-total-rows="' . esc_attr( $last_id ) . '">' . esc_html__( 'Add New', 'user-registration' ) . '</button>';
				echo '</div>';
				echo '</div>';
				echo '</div>';
		}

			/**
			 * Get admin field.
			 *
			 * @param object $single_field Single field.
			 * @throws Exception Throw exception if empty form data.
			 */
		public static function get_admin_field( $single_field ) {

			if ( empty( $single_field->field_key ) ) {
				throw new Exception( esc_html__( 'Empty form data', 'user-registration' ) );
			}

			$class_name = 'UR_Form_Field_' . ucwords( $single_field->field_key );

			if ( class_exists( $class_name ) ) {
				return $class_name::get_instance()->get_admin_template( $single_field ); // @codingStandardsIgnoreLine
			}

			/* Backward Compat since 1.4.0 */
			$class_name_old = 'UR_' . ucwords( $single_field->field_key );
			if ( class_exists( $class_name_old ) ) {
				return $class_name_old::get_instance()->get_admin_template( $single_field );
			}
			/* Backward compat end */
		}

			/**
			 * Get registered user form fields.
			 */
		private function get_registered_user_form_fields() {

			$registered_form_fields = ur_get_user_field_only();

			echo ' <ul id = "ur-draggabled" class="ur-registered-list" > ';

			foreach ( $registered_form_fields as $field ) {

				$this->ur_get_list( $field );
			}
			echo ' </ul > ';
		}

			/**
			 * Get Registered other form field.
			 */
		private function get_registered_other_form_fields() {

			$registered_form_fields = ur_get_other_form_fields();

			echo ' <ul id = "ur-draggabled" class="ur-registered-list" > ';

			foreach ( $registered_form_fields as $field ) {

				$this->ur_get_list( $field );
			}

			do_action( 'ur_after_other_form_fields_printed' );
			echo ' </ul > ';
		}

			/**
			 * Get Admin field List.
			 *
			 * @param mixed $field Fields.
			 */
		public function ur_get_list( $field ) {

			$class_name = ur_load_form_field_class( $field );

			if ( null !== $class_name ) {
				echo wp_kses_post( $class_name::get_instance()->get_registered_admin_fields() );
			}

		}
	}

endif;

return new UR_Admin_Menus();
