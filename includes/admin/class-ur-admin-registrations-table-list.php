<?php
/**
 * User Registration Table List
 *
 * @version 1.2.0
 * @package UserRegistration\Admin\Registration
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'UR_List_Table' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/abstracts/abstract-ur-list-table.php';
}

/**
 * Registrations table list class.
 */
class UR_Admin_Registrations_Table_List extends UR_List_Table {

	/**
	 * Initialize the registration table list.
	 */
	public function __construct() {
		$this->post_type       = 'user_registration';
		$this->page            = 'user-registration';
		$this->per_page_option = 'user_registration_per_page';
		parent::__construct(
			array(
				'singular' => 'registration',
				'plural'   => 'registrations',
				'ajax'     => false,
			)
		);
	}

	/**
	 * No items found text.
	 */
	public function no_items() {
		esc_html_e( 'No user registration found.', 'user-registration' );
	}

	/**
	 * Get list columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'        => '<input type="checkbox" />',
			'title'     => esc_html__( 'Title', 'user-registration' ),
			'shortcode' => esc_html__( 'Shortcode', 'user-registration' ),
			'author'    => esc_html__( 'Author', 'user-registration' ),
			'date'      => esc_html__( 'Date', 'user-registration' ),
		);
	}

	/**
	 * Post Edit Link.
	 *
	 * @param object $row Post.
	 *
	 * @return string
	 */
	public function get_edit_links( $row ) {
		return admin_url( 'admin.php?page=add-new-registration&amp;edit-registration=' . $row->ID );
	}


	/**
	 * Post Duplicate Link.
	 *
	 * @param  mixed $post_id Post ID.
	 *
	 * @return string
	 */
	public function get_duplicate_link( $post_id ) {
		return admin_url( 'admin.php?page=add-new-registration&edit-registration=' . $post_id );
	}

	/**
	 * Column: Actions.
	 *
	 * @param  object $row Post.
	 *
	 * @return string
	 */
	public function get_row_actions( $row ) {
		$edit_link            = $this->get_edit_links( $row );
		$post_status          = $row->post_status;
		$post_type_object     = get_post_type_object( $row->post_type );
		$current_status_trash = ( 'trash' === $post_status );

		// Get actions.
		$actions = array(
			// Translators: %d is a placeholder for the Post ID.
			'id' => sprintf( esc_html__( 'ID: %d', 'user-registration' ), $row->ID ),
		);

		if ( current_user_can( $post_type_object->cap->edit_post, $row->ID ) && ! $current_status_trash ) {
			$actions['edit'] = '<a href="' . esc_url( $edit_link ) . '">' . __( 'Edit', 'user-registration' ) . '</a>';
		}

		if ( current_user_can( $post_type_object->cap->delete_post, $row->ID ) ) {
			if ( $current_status_trash ) {
				$actions['untrash'] = '<a aria-label="' . esc_attr__( 'Restore this item from the Trash', 'user-registration' ) . '" href="' . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $row->ID ) ), 'untrash-post_' . $row->ID ) . '">' . esc_html__( 'Restore', 'user-registration' ) . '</a>';
			} elseif ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = '<a class="submitdelete" aria-label="' . esc_attr__( 'Move this item to the Trash', 'user-registration' ) . '" href="' . get_delete_post_link( $row->ID ) . '">' . esc_html__( 'Trash', 'user-registration' ) . '</a>';
			}
			if ( $current_status_trash || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = '<a class="submitdelete" aria-label="' . esc_attr__( 'Delete this item permanently', 'user-registration' ) . '" href="' . get_delete_post_link( $row->ID, '', true ) . '">' . esc_html__( 'Delete permanently', 'user-registration' ) . '</a>';
			}
		}
		$duplicate_nonce = wp_create_nonce( 'ur_duplicate_post_' . $row->ID );
		if ( current_user_can( $post_type_object->cap->edit_post, $row->ID ) ) {
			$preview_link = add_query_arg(
				array(
					'ur_preview' => 'true',
					'form_id'    => absint( $row->ID ),
				),
				home_url()
			);

			$duplicate_link = admin_url( 'admin.php?page=user-registration&action=duplicate&nonce=' . $duplicate_nonce . '&post-id=' . $row->ID );

			if ( 'trash' !== $post_status ) {
				$actions['view'] = '<a href="' . esc_url( $preview_link ) . '" rel="bookmark" target="_blank">' . esc_html__( 'Preview', 'user-registration' ) . '</a>';
			}

			if ( 'publish' === $post_status ) {
				$actions['duplicate'] = '<a href="' . esc_url( $duplicate_link ) . '">' . esc_html__( 'Duplicate', 'user-registration' ) . '</a>';
			}

			if ( 'publish' === $post_status ) {
				$actions['locate'] = '<a href="#" class="ur-form-locate" data-id= "' . esc_attr( $row->ID ) . '">' . esc_html__( 'Locate', 'user-registration' ) . '</a>';
			}
		}
		return $actions;
	}

	/**
	 * Return shortcode column.
	 *
	 * @param  object $registration Registration forms datas.
	 *
	 * @return void
	 */
	public function column_shortcode( $registration ) {
		$shortcode = '[user_registration_form id="' . $registration->ID . '"]';
		printf( '<input type="text" onfocus="this.select();" readonly="readonly" value=\'%s\' class="widefat code"></span>', esc_attr( $shortcode ) );
		?>
		<button id="copy-shortcode-<?php echo esc_attr( $registration->ID ); ?>" class="button ur-copy-shortcode " href="#" data-tip="<?php esc_attr_e( 'Copy Shortcode ! ', 'user-registration' ); ?>" data-copied="<?php esc_attr_e( 'Copied ! ', 'user-registration' ); ?>">
			<span class="dashicons dashicons-admin-page"></span>
		</button>
		<?php
	}

	/**
	 * Render the list table page, including header, notices, status filters and table.
	 */
	public function display_page() {
		$this->prepare_items();
		?>
				<div class="ur-admin-page-topnav" id="ur-lists-page-topnav">
					<div class="ur-page-title__wrapper">
						<div class="ur-page-title__wrapper-logo">
							<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
								<path d="M29.2401 2.25439C27.1109 3.50683 25.107 5.13503 23.3536 6.88846C21.6002 8.64188 19.972 10.6458 18.7195 12.6497C19.5962 14.4031 20.3477 16.1566 20.9739 18.0352C22.1011 15.6556 23.4788 13.5264 25.2323 11.6477V18.4109C25.2323 22.544 22.4769 26.1761 18.4691 27.3033H18.2185C17.9681 24.047 17.2166 20.9158 16.0894 17.91C14.4612 13.7769 11.9563 10.0196 8.69995 6.88846C6.94652 5.13503 4.94263 3.63208 2.81347 2.25439L2.3125 2.00388V18.2857C2.3125 24.9237 7.07177 30.6849 13.7097 31.8121H13.835C15.3379 32.0626 16.8409 32.0626 18.2185 31.8121H18.3438C24.9818 30.6849 29.7411 24.9237 29.7411 18.2857V2.00388L29.2401 2.25439ZM6.82128 18.2857V11.6477C10.7039 16.0313 13.0835 21.4168 13.5845 27.1781C9.57669 26.0509 6.82128 22.4188 6.82128 18.2857ZM15.9642 0C14.0855 0 12.5825 1.50291 12.5825 3.38158C12.5825 5.26025 14.0855 6.7632 15.9642 6.7632C17.8428 6.7632 19.3457 5.26025 19.3457 3.38158C19.3457 1.50291 17.8428 0 15.9642 0Z" fill="#475BB2"/>
							</svg>
						</div>
						<div class="ur-page-title__wrapper-menu">
							<ul class="ur-page-title__wrapper-menu__items">
								<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration' ) ); ?>" class="current"><?php esc_html_e( 'Registration Forms', 'user-registration' ); ?></a></li>
								<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration&tab=login-forms' ) ); ?>" class=""><?php esc_html_e( 'Login Forms', 'user-registration' ); ?></a></li>
							</ul>
						</div>
					</div>
					<div class="ur-page-actions">
						<button id="ur-lists-page-settings-button" class="ur-button-primary"
							title="<?php esc_html_e( 'Screen Options', 'user-registration' ); ?>">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
								<g clip-path="url(#a)">
									<path fill="#fff" fill-rule="evenodd"
										d="M11.293 2.293A1 1 0 0 1 13 3v.094a2.65 2.65 0 0 0 1.601 2.423 2.65 2.65 0 0 0 2.918-.532l.008-.008.06-.06a1 1 0 0 1 1.416 0 .999.999 0 0 1 0 1.415l-.06.06-.008.009a2.65 2.65 0 0 0-.607 2.729c.012.09.037.18.073.264A2.65 2.65 0 0 0 20.826 11H21a1 1 0 0 1 0 2h-.094a2.65 2.65 0 0 0-2.423 1.601 2.65 2.65 0 0 0 .532 2.918l.008.008.06.06a1 1 0 0 1 0 1.416 1 1 0 0 1-1.415 0l-.06-.06-.009-.008a2.651 2.651 0 0 0-2.918-.532 2.65 2.65 0 0 0-1.601 2.423V21a1 1 0 0 1-2 0v-.113a2.65 2.65 0 0 0-1.705-2.415 2.651 2.651 0 0 0-2.894.543l-.008.008-.06.06a.999.999 0 0 1-1.416 0 1 1 0 0 1 0-1.415l.06-.06.008-.009a2.65 2.65 0 0 0 .532-2.918 2.65 2.65 0 0 0-2.423-1.601H3a1 1 0 0 1 0-2h.113a2.65 2.65 0 0 0 2.414-1.705 2.65 2.65 0 0 0-.542-2.894l-.008-.008-.06-.06a1 1 0 0 1 0-1.416 1 1 0 0 1 1.415 0l.06.06.009.008a2.65 2.65 0 0 0 2.729.607 1 1 0 0 0 .264-.073A2.65 2.65 0 0 0 11 3.174V3a1 1 0 0 1 .293-.707ZM12 0a3 3 0 0 0-3 3v.167a.65.65 0 0 1-.285.534 1 1 0 0 0-.199.064.65.65 0 0 1-.714-.127l-.054-.055a3 3 0 1 0-4.245 4.244l.055.055a.65.65 0 0 1 .127.714l-.024.059a.65.65 0 0 1-.585.425H3a3 3 0 1 0 0 6h.167a.65.65 0 0 1 .594.394l.004.01a.65.65 0 0 1-.127.714l-.055.055a3 3 0 0 0 3.27 4.895 3 3 0 0 0 .974-.65v-.001l.055-.055a.65.65 0 0 1 .714-.127l.059.023a.651.651 0 0 1 .425.586V21a3 3 0 1 0 6 0v-.168a.65.65 0 0 1 .394-.593l.01-.004a.65.65 0 0 1 .714.127l.055.055a2.999 2.999 0 0 0 4.244 0l-.707-.707.707.707a2.999 2.999 0 0 0 0-4.244l-.055-.055a.65.65 0 0 1-.127-.714l.004-.01a.65.65 0 0 1 .594-.394H21a3 3 0 0 0 0-6h-.168a.65.65 0 0 1-.533-.285 1.006 1.006 0 0 0-.064-.199.65.65 0 0 1 .127-.714l.055-.054a2.999 2.999 0 0 0-.973-4.896 3 3 0 0 0-3.271.651l-.055.055a.65.65 0 0 1-.714.127l-.01-.004A.65.65 0 0 1 15 3.087V3a3 3 0 0 0-3-3Zm-2 12a2 2 0 1 1 4 0 2 2 0 0 1-4 0Zm2-4a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"
										clip-rule="evenodd" />
								</g>
								<defs>
									<clipPath id="a">
										<path fill="#fff" d="M0 0h24v24H0z" />
									</clipPath>
								</defs>
							</svg>
						</button>
					</div>
				</div>
				<hr class="wp-header-end">
				<div id="user-registration-list-table-page">
					<div class="user-registration-list-table-header">
						<h2>All Registration Forms</h2>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=add-new-registration' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'user-registration' ); ?></a>
					</div>
					<div class="user-registration-list-table-page__body">
						<form id="registration-list" class="user-registration-list-table-action-form" method="get" >
							<input type="hidden" name="page" value="user-registration" />
							<?php
							echo "<div id='user-registration-list-filters-row'>";
								$this->views();
								$this->search_box( esc_html__( 'Search Registration', 'user-registration' ), 'user-registration-list-table' );
								echo '</div>';

								$this->display();

								wp_nonce_field( 'save', 'user_registration_nonce' );
							?>
						</form>
					</div>
				</div>

		<?php
	}

	/**
	 * Displays the search box.
	 *
	 * @since 4.1
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = 'user-registration-list-table-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['detached'] ) ) {
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
		}
		?>
			<div id="user-registration-list-search-form">
				<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
				<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" placeholder="<?php esc_html_e( 'Search Forms ...', 'user-registration' ); ?>" />
				<button type="submit" id="search-submit">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
						<path fill="#000" fill-rule="evenodd" d="M4 11a7 7 0 1 1 12.042 4.856 1.012 1.012 0 0 0-.186.186A7 7 0 0 1 4 11Zm12.618 7.032a9 9 0 1 1 1.414-1.414l3.675 3.675a1 1 0 0 1-1.414 1.414l-3.675-3.675Z" clip-rule="evenodd"/>
					</svg>
				</button>
			</div>
			<?php
	}
}
