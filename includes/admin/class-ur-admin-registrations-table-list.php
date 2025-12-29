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
				$actions['view'] = '<a href="' . esc_url( $preview_link ) . '" rel="bookmark" rel="noreferrer noopener" target="_blank">' . esc_html__( 'Preview', 'user-registration' ) . '</a>';
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
		?>
		<div class='urm-shortcode'>
		<?php
		$shortcode = '[user_registration_form id="' . $registration->ID . '"]';
		printf( '<input type="text" onfocus="this.select();" readonly="readonly" value=\'%s\' class="widefat code"></span>', esc_attr( $shortcode ) );
		?>

		<button id='copy-shortcode-<?php echo $registration->ID; ?>' class='button ur-copy-shortcode tooltipstered' href='#' data-tip='Copy Shortcode ! ' data-copied='Copied ! '>
			<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24'>
				<path fill='#383838' fill-rule='evenodd' d='M3.116 3.116A1.25 1.25 0 0 1 4 2.75h9A1.25 1.25 0 0 1 14.25 4v1a.75.75 0 0 0 1.5 0V4A2.75 2.75 0 0 0 13 1.25H4A2.75 2.75 0 0 0 1.25 4v9A2.75 2.75 0 0 0 4 15.75h1a.75.75 0 0 0 0-1.5H4A1.25 1.25 0 0 1 2.75 13V4c0-.332.132-.65.366-.884ZM9.75 11c0-.69.56-1.25 1.25-1.25h9c.69 0 1.25.56 1.25 1.25v9c0 .69-.56 1.25-1.25 1.25h-9c-.69 0-1.25-.56-1.25-1.25v-9ZM11 8.25A2.75 2.75 0 0 0 8.25 11v9A2.75 2.75 0 0 0 11 22.75h9A2.75 2.75 0 0 0 22.75 20v-9A2.75 2.75 0 0 0 20 8.25h-9Z' clip-rule='evenodd'></path>
			</svg>
		</button>

		</div>
		<?php
	}

	/**
	 * Render the list table page, including header, notices, status filters and table.
	 */
	public function display_page() {
		$this->prepare_items();
		?>
				<hr class="wp-header-end">
				<?php echo user_registration_plugin_main_header(); ?>
				<div class="user-registration-list-table-container">
					<div id="user-registration-list-table-page">
						<div class="user-registration-list-table-header">
							<h2><?php esc_html_e( 'All Registration Forms', 'user-registration' ); ?></h2>
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
				</div>
		<?php
	}

	/**
	 * Displays the search box.
	 *
	 * @param string $text search button Text.
	 * @param string $input_id Input field id.
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) { // phpcs:ignore;
			return;
		}

		$input_id = 'user-registration-list-table-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) { // phpcs:ignore;
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />'; // phpcs:ignore;
		}
		if ( ! empty( $_REQUEST['order'] ) ) { // phpcs:ignore;
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />'; // phpcs:ignore;
		}
		if ( ! empty( $_REQUEST['post_mime_type'] ) ) { // phpcs:ignore;
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />'; // phpcs:ignore;
		}
		if ( ! empty( $_REQUEST['detached'] ) ) { // phpcs:ignore;
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />'; // phpcs:ignore;
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
