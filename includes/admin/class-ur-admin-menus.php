<?php
/**
 * Setup menus in WP admin.
 *
 * @class    UR_Admin_Menus
 * @version  1.0.0
 * @package  UserRegistration/Admin
 * @category Admin
 * @author   WPEverest
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

			// Set screens
			add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 10, 3 );

			// Add endpoints custom URLs in Appearance > Menus > Pages.
			add_action( 'admin_head-nav-menus.php', array( $this, 'add_nav_menu_meta_boxes' ) );

			// Add all available upgradable fields.
			add_action( 'ur_after_other_form_fields_printed', array( $this, 'add_upgradable_other_fields' ) ); // Adds fields in the `Extra Fields` section.
			add_action( 'user_registration_extra_fields', array( $this, 'add_upgradable_extra_fields' ) );
		}

		public function add_upgradable_other_fields() {
			$fields = array(
				array(
					'id'          => 'user_registration_file',
					'label'       => 'File Upload',
					'icon'        => 'ur-icon ur-icon-file-upload',
					'field_class' => 'UR_File',
					'plan'        => 'Personal Plan',
				),
				array(
					'id'          => 'user_registration_mailchimp',
					'label'       => 'MailChimp',
					'icon'        => 'ur-icon ur-icon-mailchimp',
					'field_class' => 'UR_MailChimp',
					'plan'        => 'Personal Plan',
				),
				array(
					'id'          => 'user_registration_invite_code',
					'label'       => 'Invitation Code',
					'icon'        => 'ur-icon ur-icon-invite-codes',
					'field_class' => 'UR_Form_Field_Invite_Code',
					'plan'        => 'Professional Plan or Plus Plan',
				),
			);

			foreach ( $fields as $field ) {
				if ( ! class_exists( $field['field_class'] ) ) {
					$this->render_upgradable_field( $field );
				}
			}
		}

		public function add_upgradable_extra_fields() {
			$field_sections = array(
				array(
					'section_title'       => 'Advanced Fields',
					'fields_parent_class' => 'URAF_Admin',
					'plan'                => 'Personal Plan',
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
					),
				),
				array(
					'section_title'       => 'WooCommerce Billing Address',
					'fields_parent_class' => 'URWC_Admin',
					'plan'                => 'Personal Plan',
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
					'fields'              => array(
						array(
							'id'    => 'user_registration_single_item',
							'label' => 'Single Item',
							'icon'  => 'ur-icon ur-icon-file-dollar',
						),
						array(
							'id'    => 'user_registration_stripe_gateway',
							'label' => 'Stripe Gateway',
							'icon'  => 'ur-icon ur-icon-credit-card',
						),
					),
				),
			);

			foreach ( $field_sections as $section ) {
				$class_to_check = $section['fields_parent_class'];

				if ( ! class_exists( $class_to_check ) ) {
					$fields = $section['fields'];
					$plan   = isset( $section['plan'] ) ? $section['plan'] : '';

					// Set the same plan for all the section's fields.
					for ( $i = 0; $i < count( $fields ); $i++ ) {
						$fields[ $i ]['plan'] = $plan;
					}

					echo '<h2 class="ur-toggle-heading">' . __( $section['section_title'], 'user-registration' ) . '</h2><hr/>';
					echo '<ul id = "ur-upgradables" class="ur-registered-list" > ';
					$this->render_upgradable_fields( $fields );
					echo '</ul >';
				}
			}
		}

		/**
		 * Render multiple upgradable fields.
		 */
		public function render_upgradable_fields( $fields ) {
			foreach ( $fields as $field ) {
				$this->render_upgradable_field( $field );
			}
		}

		/**
		 * Render an upgradable field.
		 */
		public function render_upgradable_field( $args ) {
			$id    = $args['id'];
			$icon  = $args['icon'];
			$label = $args['label'];
			$plan  = isset( $args['plan'] ) ? $args['plan'] : '';

			echo '<li id="' . $id . '_list " class="ur-registered-item ur-upgradable-field ui-draggable-disabled" data-field-id="' . $id . '" data-plan="' . $plan . '"><span class="' . $icon . '"></span>' . $label . '</li>';
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
			$registration_page = add_menu_page( __( 'User Registration' ), __( 'User Registration' ), 'manage_user_registration', 'user-registration', array( $this, 'registration_page' ), $this->get_icon_svg(), '55.8' );

			add_action( 'load-' . $registration_page, array( $this, 'registration_page_init' ) );
		}

		/**
		 * Loads screen options into memory.
		 */
		public function registration_page_init() {
			global $registration_table_list;

			if ( ! isset( $_GET['add-new-registration'] ) ) { // WPCS: input var okay, CSRF ok.
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
				__( 'Add New', 'user-registration' ),
				__( 'Add New', 'user-registration' ),
				'manage_user_registration',
				'add-new-registration',
				array(
					$this,
					'add_registration_page',
				)
			);
		}

		/**
		 * Addons menu item.
		 */
		public function addons_menu() {
			add_submenu_page(
				'user-registration',
				__( 'User Registration extensions', 'user-registration' ),
				__( 'Extensions', 'user-registration' ),
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
			$form_id   = isset( $_GET['edit-registration'] ) ? absint( $_GET['edit-registration'] ) : 0;
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

			// Forms view
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
					<a href="<?php echo admin_url( 'nav-menus.php?page-tab=all&selectall=1#posttype-user-registration-endpoints' ); ?>"
					   class="select-all"><?php _e( 'Select all', 'user-registration' ); ?></a>
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

				if ( json_last_error() != JSON_ERROR_NONE ) {
					throw new Exception( '' );
				}
			} catch ( Exception $e ) {
				$form_data_array = array();
			}

			try {
				$form_row_ids_array = json_decode( $form_row_ids );

				if ( json_last_error() != JSON_ERROR_NONE ) {
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

				echo '<div class="ur-single-row"  data-row-id="' . absint( $row_id ) . '">';
				?>

				<div class="ur-grids">
					<button type="button" class="ur-edit-grid">
						<?php
						if ( 1 === $grid_count ) {
							echo $grid_one; // phpcs:ignore WordPress.Security.EscapeOutput
						} elseif ( 2 === $grid_count ) {
							echo $grid_two; // phpcs:ignore WordPress.Security.EscapeOutput
						} elseif ( 3 === $grid_count ) {
							echo $grid_three; // phpcs:ignore WordPress.Security.EscapeOutput
						}
						?>
					</button>
					<button type="button" class="dashicons dashicons-no-alt ur-remove-row"></button>
					<div class="ur-toggle-grid-content" style="display:none">
						<small>Select the grid column.</small>
						<div class="ur-grid-selector" data-grid = "1">
							<?php echo $grid_one; // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
						<div class="ur-grid-selector" data-grid = "2">
							<?php echo $grid_two; // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
						<div class="ur-grid-selector" data-grid = "3">
							<?php echo $grid_three; // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
					</div>
				</div>

				<?php
				echo '<div class="ur-grid-lists">';

				$grid_id = 0;

				foreach ( $rows as $grid_lists ) {

					$grid_id ++;

					echo '<div ur-grid-id="' . $grid_id . '" class="ur-grid-list-item ui-sortable" style="width: 48%; min-height: 70px;">';

					foreach ( $grid_lists as $single_field ) {

						if ( isset( $single_field->field_key ) ) {
							// Hook for fields backward compatibility.
							apply_filters( 'user_registration_form_builder_field_before', $single_field );

							$admin_field = $this->get_admin_field( $single_field );
							echo '<div class="ur-selected-item">';
							echo '<div class="ur-action-buttons"><span title="Clone" class="dashicons dashicons-admin-page ur-clone"></span><span title="Trash" class="dashicons dashicons-trash ur-trash"></span></div>';
							$template = isset( $admin_field['template'] ) ? $admin_field['template'] : '' ; // @codingStandardsIgnoreLine
							echo $template;
							echo '</div>';
						}
					}

					if ( count( $grid_lists ) == 0 ) {
						echo '<div class="user-registration-dragged-me">
						<div class="user-registration-dragged-me-text"><p>' . esc_html( 'Drag your first form item here.', 'user-registration' ) . '</p></div>
						</div>';
					}

					echo '</div>';
				}

				echo '</div>';
				echo '</div>';

			}// End foreach().
			echo '<button type="button" class="button button-primary dashicons dashicons-plus-alt ur-add-new-row" data-total-rows="' . $last_id . '">' . esc_html( 'Add New', 'user-registration' ) . '</button>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}

		public static function get_admin_field( $single_field ) {

			if ( empty( $single_field->field_key ) ) {
				throw new Exception( __( 'Empty form data', 'user-registration' ) );
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

		private function get_registered_user_form_fields() {

			$registered_form_fields = ur_get_user_field_only();

			echo ' <ul id = "ur-draggabled" class="ur-registered-list" > ';

			foreach ( $registered_form_fields as $field ) {

				$this->ur_get_list( $field );
			}
			echo ' </ul > ';
		}

		private function get_registered_other_form_fields() {

			$registered_form_fields = ur_get_other_form_fields();

			echo ' <ul id = "ur-draggabled" class="ur-registered-list" > ';

			foreach ( $registered_form_fields as $field ) {

				$this->ur_get_list( $field );
			}

			do_action( 'ur_after_other_form_fields_printed' );
			echo ' </ul > ';
		}

		public function ur_get_list( $field ) {

			$class_name = ur_load_form_field_class( $field );

			if ( $class_name !== null ) {
				echo $class_name::get_instance()->get_registered_admin_fields();
			}

		}
	}

endif;

return new UR_Admin_Menus();
