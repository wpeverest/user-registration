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
	exit;
}

if ( ! class_exists( 'UR_Admin_Menus', false ) ) :

	/**
	 * UR_Admin_Menus Class.
	 */
	class UR_Admin_Menus {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			// Add menus
			add_action( 'admin_init', array( $this, 'actions' ) );
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
		}

		/**
		 * Registration forms admin actions.
		 */
		public function actions() {
			if ( isset( $_GET['page'] ) && 'user-registration' === $_GET['page'] ) {

				// Bulk actions
				if ( isset( $_REQUEST['action'] ) && isset( $_REQUEST['registration'] ) ) {
					$this->bulk_actions();
				}

				// Empty trash
				if ( isset( $_GET['empty_trash'] ) ) {
					$this->empty_trash();
				}

				$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';

				$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( $_GET['nonce'] ) : '';

				$form_id = isset( $_GET['form'] ) && is_numeric( $_GET['form'] ) ? $_GET['form'] : '';

				if ( ! empty( $action ) && ! empty( $nonce ) && ! empty( $form_id ) ) {

					$flag = wp_verify_nonce( $nonce, 'user_registration_form_duplicate' . $form_id );

					if ( $flag == true && ! is_wp_error( $flag ) ) {

						if ( 'duplicate' === $action ) {

							$this->duplicate( $form_id );

						}

					}


				}
			}
		}

		/**
		 * Bulk trash/delete.
		 *
		 * @param array $registrations
		 * @param bool  $delete
		 */
		private function bulk_trash( $registrations, $delete = false ) {
			foreach ( $registrations as $registration_id ) {
				if ( $delete ) {
					wp_delete_post( $registration_id, true );
				} else {
					wp_trash_post( $registration_id );
				}
			}

			$type   = ! EMPTY_TRASH_DAYS || $delete ? 'deleted' : 'trashed';
			$qty    = count( $registrations );
			$status = isset( $_GET['status'] ) ? '&status=' . sanitize_text_field( $_GET['status'] ) : '';

			// Redirect to registrations page
			wp_redirect( admin_url( 'admin.php?page=user-registration' . $status . '&' . $type . '=' . $qty ) );
			exit();
		}

		/**
		 * Bulk untrash.
		 *
		 * @param array $registrations
		 */
		private function bulk_untrash( $registrations ) {
			foreach ( $registrations as $registration_id ) {
				wp_untrash_post( $registration_id );
			}

			$qty = count( $registrations );

			// Redirect to registrations page
			wp_redirect( admin_url( 'admin.php?page=user-registration&status=trash&untrashed=' . $qty ) );
			exit();
		}

		/**
		 * Duplicate form
		 */
		private function duplicate( $form_id ) {
			$post            = get_post( $form_id );
			$current_user    = wp_get_current_user();
			$new_post_author = $current_user->ID;

			/*
			 * if post data exists, create the post duplicate
			 */
			if ( isset( $post ) && $post != null ) {

				if ( 'publish' !== $post->post_status ) {

					return false;
				}

				/*
				 * new post data array
				 */
				$args = array(
					'comment_status' => $post->comment_status,
					'ping_status'    => $post->ping_status,
					'post_author'    => $new_post_author,
					'post_content'   => $post->post_content,
					'post_excerpt'   => $post->post_excerpt,
					'post_name'      => $post->post_name,
					'post_parent'    => $post->post_parent,
					'post_password'  => $post->post_password,
					'post_status'    => $post->post_status,
					'post_title'     => __( 'Copy of ', 'user-registration' ) . $post->post_title,
					'post_type'      => $post->post_type,
					'to_ping'        => $post->to_ping,
					'menu_order'     => $post->menu_order
				);

				/*
				 * insert the post by wp_insert_post() function
				 */
				$new_post_id = wp_insert_post( $args );

				/*
				 * duplicate all post meta just in two SQL queries
				 */
				global $wpdb;
				$post_meta_infos = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d", $form_id ) );

				if ( count( $post_meta_infos ) != 0 ) {
					$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
					foreach ( $post_meta_infos as $meta_info ) {
						$meta_key = $meta_info->meta_key;
						if ( $meta_key == '_wp_old_slug' ) {
							continue;
						}
						$meta_value      = addslashes( $meta_info->meta_value );
						$sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
					}
					$sql_query .= implode( " UNION ALL ", $sql_query_sel );
					$wpdb->query( $sql_query );
				}

				/*
				 * duplicate all post meta just in two SQL queries
				 */
				global $wpdb;
				$post_meta_infos = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d", $form_id ) );

				if ( count( $post_meta_infos ) != 0 ) {
					$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
					foreach ( $post_meta_infos as $meta_info ) {
						$meta_key = $meta_info->meta_key;
						if ( $meta_key == '_wp_old_slug' ) {
							continue;
						}
						$meta_value      = addslashes( $meta_info->meta_value );
						$sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
					}
					$sql_query .= implode( " UNION ALL ", $sql_query_sel );
					$wpdb->query( $sql_query );
				}

				/*
				 * finally, redirect to the edit post screen for the new draft
				 */
				wp_redirect( admin_url( 'admin.php?page=add-new-registration&edit-registration=' . $new_post_id ) );
				exit;
			}
		}

		/**
		 * Bulk actions.
		 */
		private function bulk_actions() {
			if ( ! current_user_can( 'edit_user_registrations' ) ) {
				wp_die( __( 'You do not have permissions to edit forms!', 'user-registration' ) );
			}

			$registrations = array_map( 'absint', (array) $_REQUEST['registration'] );

			switch ( $_REQUEST['action'] ) {
				case 'trash' :
					$this->bulk_trash( $registrations );
					break;
				case 'untrash' :
					$this->bulk_untrash( $registrations );
					break;
				case 'delete' :
					$this->bulk_trash( $registrations, true );
					break;
				default :
					break;
			}
		}

		/**
		 * Empty Trash.
		 */
		private function empty_trash() {
			if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'empty_trash' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'user-registration' ) );
			}

			if ( ! current_user_can( 'delete_user_registrations' ) ) {
				wp_die( __( 'You do not have permissions to delete forms!', 'user-registration' ) );
			}

			$registration = get_posts( array(
				'post_type'           => 'user_registration',
				'ignore_sticky_posts' => true,
				'nopaging'            => true,
				'post_status'         => 'trash',
				'fields'              => 'ids',
			) );

			foreach ( $registration as $webhook_id ) {
				wp_delete_post( $webhook_id, true );
			}

			$qty = count( $registration );

			// Redirect to registrations page
			wp_redirect( admin_url( 'admin.php?page=user-registration&deleted=' . $qty ) );
			exit();
		}

		/**
		 * Add menu items.
		 */
		public function admin_menu() {
			$registration_page = add_menu_page( __( 'User Registration', 'user-registration' ), __( 'User Registration', 'user-registration' ), 'manage_user_registration', 'user-registration', array( $this, 'registration_page' ), 'dashicons-universal-access-alt', '55.8' );

			add_action( 'load-' . $registration_page, array( $this, 'registration_page_init' ) );
		}

		/**
		 * Loads screen options into memory.
		 */
		public function registration_page_init() {
			global $registration_table_list;

			if ( ! isset( $_GET['add-new-registration'] ) ) { // WPCS: input var okay, CSRF ok.
				$registration_table_list = new UR_Admin_Registrations_Table_List();

				// Add screen option.
				add_screen_option( 'per_page', array(
					'default' => 20,
					'option'  => 'user_registration_per_page',
				) );
			}
		}

		/**
		 * Add menu item.
		 */
		public function settings_menu() {
			add_submenu_page( 'user-registration', __( 'User Registration settings', 'user-registration' ), __( 'Settings', 'user-registration' ), 'manage_user_registration', 'user-registration-settings', array(
				$this,
				'settings_page'
			) );
		}

		/**
		 * Add menu item.
		 */
		public function status_menu() {
			add_submenu_page( 'user-registration', __( 'User Registration Status', 'user-registration' ), __( 'Status', 'user-registration' ), 'manage_user_registration', 'user-registration-status', array(
				$this,
				'status_page'
			) );
		}

		/**
		 * Add menu items.
		 */
		public function add_registration_menu() {
			add_submenu_page( 'user-registration', __( 'Add New', 'user-registration' ), __( 'Add New', 'user-registration' ), 'manage_user_registration', 'add-new-registration', array(
				$this,
				'add_registration_page'
			) );
		}

		/**
		 * Addons menu item.
		 */
		public function addons_menu() {
			add_submenu_page( 'user-registration', __( 'User Registration extensions', 'user-registration' ), __( 'Extensions', 'user-registration' ), 'manage_user_registration', 'user-registration-addons', array(
				$this,
				'addons_page'
			) );
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

			$registration_table_list->prepare_items();
			?>
			<div class="wrap">
				<h1 class="wp-heading-inline"><?php esc_html_e( 'User Registration', 'user-registration' ); ?></h1>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=add-new-registration' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'user-registration' ); ?></a>
				<hr class="wp-header-end">
				<form id="registration-list" method="post">
					<input type="hidden" name="page" value="user-registration" />
					<?php
						$registration_table_list->views();
						$registration_table_list->search_box( __( 'Search Registration', 'user-registration' ), 'registration' );
						$registration_table_list->display();

						wp_nonce_field( 'save', 'user_registration_nonce' );
					?>
				</form>
			</div>
			<?php
		}

		/**
		 * Init the add registration page.
		 */
		public function add_registration_page() {
			$post_id   = isset( $_GET['edit-registration'] ) ? $_GET['edit-registration'] : 0;
			$args      = array(
				'post_type'   => 'user_registration',
				'post_status' => 'publish',
				'post__in'    => array( $post_id ),
			);
			$post_data = get_posts( $args );

			$save_label = __( 'Create Form', 'user-registration' );

			if ( $post_id > 0 ) {
				$save_label = __( 'Update form', 'user-registration' );
			}

			// Forms view
			include_once( dirname( __FILE__ ) . '/views/html-admin-page-forms.php' );
		}


		/**
		 * Init the settings page.
		 */
		public function settings_page() {
			UR_Admin_Settings::output();
		}

		/**
		 * Init the settings page.
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
			add_meta_box( 'user_registration_endpoints_nav_link', __( 'User Registration endpoints', 'user-registration' ), array(
				$this,
				'nav_menu_links'
			), 'nav-menus', 'side', 'low' );
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
			$endpoints['lost-password'] = __( 'Lost password', 'user-registration' );

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

		private function get_edit_form_field( $post_data ) {
			if ( isset( $post_data[0] ) ) {
				$form_data = $post_data[0]->post_content;
			} else {
				$form_data = '';
			}

			try {
				$form_data_array = json_decode( $form_data );

				if ( json_last_error() != JSON_ERROR_NONE ) {

					throw new Exception( '' );
				}
			}
			catch ( Exception $e ) {
				$form_data_array = array();
			}

			echo '<div class="ur-selected-inputs">';

			$row_count = 0;

			foreach ( $form_data_array as $rows ) {

				$row_count ++;

				echo '<div class="ur-single-row">';

				echo '<div class="ur-grids">';

				$grid_string = ceil( UR_Config::$ur_form_grid / count( $rows ) ) . '/' . UR_Config::$ur_form_grid;

				echo '<div class="ur-grid-navigation ur-nav-right dashicons dashicons-arrow-left-alt2"></div>';

				echo '<div class="ur-grid-size" data-active-grid="' . count( $rows ) . '">' . $grid_string . '</div>';

				echo '<div class="ur-grid-navigation ur-nav-left dashicons dashicons-arrow-right-alt2"></div>';

				$add_or_remove_icon = '';

				echo '<button type="button" class="dashicons dashicons-no-alt ur-remove-row">' . $add_or_remove_icon . '</button>';

				echo '<div style="clear:both"></div>';
				echo '</div>';


				echo '<div class="ur-grid-lists">';

				$grid_id = 0;

				foreach ( $rows as $grid_lists ) {

					$grid_id ++;

					echo '<div ur-grid-id="' . $grid_id . '" class="ur-grid-list-item ui-sortable" style="width: 48%; min-height: 70px;">';

					foreach ( $grid_lists as $single_field ) {
						if ( isset( $single_field->field_key ) ) {
							echo '<div class="ur-selected-item">';

							echo '<div class="ur-action-buttons"><span title="Clone" class="dashicons dashicons-admin-page ur-clone"></span><span title="Trash" class="dashicons dashicons-trash ur-trash"></span></div>';

							$this->get_admin_field( $single_field );

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
			echo '<button type="button" class="dashicons dashicons-plus-alt ur-add-new-row">' . $add_or_remove_icon . '</button>';
			echo '</div>';
		}

		public static function get_admin_field( $single_field ) {
			if ( $single_field->field_key == null || $single_field->field_key == '' ) {
				throw new Exception( __( 'Empty form data', 'user-registration' ) );
			}

			$class_name = 'UR_' . ucwords( $single_field->field_key );

			if ( class_exists( $class_name ) ) {
				echo $class_name::get_instance()->get_admin_template( $single_field );
			}
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
			echo ' </ul > ';
		}

		private function ur_get_list( $field ) {

			$class_name = ur_load_form_field_class( $field );

			if ( $class_name !== null ) {
				echo $class_name::get_instance()->get_registered_admin_fields();
			}

		}
	}

endif;

return new UR_Admin_Menus();
