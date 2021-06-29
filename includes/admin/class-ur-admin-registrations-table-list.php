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
		$this->sort_by         = array(
			'title'  => array( 'title', false ),
			'author' => array( 'author', false ),
			'date'   => array( 'date', false ),
		);
		$this->bulk_actions    = $this->ur_bulk_actions();
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
		_e( 'No user registration found.', 'user-registration' );
	}

	/**
	 * Get list columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'        => '<input type="checkbox" />',
			'title'     => __( 'Title', 'user-registration' ),
			'shortcode' => __( 'Shortcode', 'user-registration' ),
			'author'    => __( 'Author', 'user-registration' ),
			'date'      => __( 'Date', 'user-registration' ),
		);
	}

	/**
	 * Post Edit Link.
	 *
	 * @param  object $row
	 *
	 * @return string
	 */
	public function get_edit_links( $row ) {
		return admin_url( 'admin.php?page=add-new-registration&amp;edit-registration=' . $row->ID );
	}


	/**
	 * Post Duplicate Link.
	 *
	 * @param  mixed $post_id
	 *
	 * @return string
	 */
	public function get_duplicate_link( $post_id ) {
		return admin_url( 'admin.php?page=add-new-registration&edit-registration=' . $post_id  );
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
			'id' => sprintf( __( 'ID: %d', 'user-registration' ), $row->ID ),
		);

		if ( current_user_can( $post_type_object->cap->edit_post, $row->ID ) && !$current_status_trash  ) {
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
				$actions['view'] = '<a href="' . esc_url( $preview_link ) . '" rel="bookmark" target="_blank">' . __( 'Preview', 'user-registration' ) . '</a>';
			}

			if ( 'publish' === $post_status ) {
				$actions['duplicate'] = '<a href="' . esc_url( $duplicate_link ) . '">' . __( 'Duplicate', 'user-registration' ) . '</a>';
			}
		}
		return $actions;
	}

	/**
	 * Return author column.
	 *
	 * @param  object $registration Registration forms datas.
	 *
	 * @return string
	 */
	public function column_author( $registration ) {
		$user = get_user_by( 'id', $registration->post_author );

		if ( ! $user ) {
			return '<span class="na">&ndash;</span>';
		}

		$user_name = ! empty( $user->data->display_name ) ? $user->data->display_name : $user->data->user_login;

		if ( current_user_can( 'edit_user' ) ) {
			return '<a href="' . esc_url(
				add_query_arg(
					array(
						'user_id' => $user->ID,
					),
					admin_url( 'user-edit.php' )
				)
			) . '">' . esc_html( $user_name ) . '</a>';
		}

		return esc_html( $user_name );
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
		echo sprintf( '<input type="text" onfocus="this.select();" readonly="readonly" value=\'%s\' class="widefat code"></span>', $shortcode );
		?>
		<button id="copy-shortcode" class="button ur-copy-shortcode " href="#" data-tip="<?php esc_attr_e( 'Copy Shortcode ! ', 'user-registration' ); ?>" data-copied="<?php esc_attr_e( 'Copied ! ', 'user-registration' ); ?>">
			<span class="dashicons dashicons-admin-page"></span>
		</button>
		<?php

	}

	/**
	 * Return created at date column.
	 *
	 * @param  object $registration Registration forms datas.
	 *
	 * @return string
	 */
	public function column_date( $registration ) {
		$post = get_post( $registration->ID );

		if ( ! $post ) {
			return;
		}

		$t_time = mysql2date(
			__( 'Y/m/d g:i:s A', 'user-registration' ),
			$post->post_date,
			true
		);
		$m_time = $post->post_date;
		$time   = mysql2date( 'G', $post->post_date )
				  - get_option( 'gmt_offset' ) * 3600;

		$time_diff = time() - $time;

		if ( $time_diff > 0 && $time_diff < 24 * 60 * 60 ) {
			$h_time = sprintf(
				__( '%s ago', 'user-registration' ),
				human_time_diff( $time )
			);
		} else {
			$h_time = mysql2date( __( 'Y/m/d', 'user-registration' ), $m_time );
		}

		return '<abbr title="' . $t_time . '">' . $h_time . '</abbr>';
	}

	/**
	 * Get the status label for licenses.
	 *
	 * @param  string   $status_name Status title.
	 * @param  stdClass $status Status value.
	 *
	 * @return array
	 */
	private function get_status_label( $status_name, $status ) {
		switch ( $status_name ) {
			case 'publish':
				/* translators: %s: count */
				$label = array(
					'singular' => __( 'Published <span class="count">(%s)</span>', 'user-registration' ),
					'plural'   => __( 'Published <span class="count">(%s)</span>', 'user-registration' ),
					'context'  => '',
					'domain'   => 'user-registration',
				);
				break;
			case 'draft':
				/* translators: %s: count */
				$label = array(
					'singular' => __( 'Draft <span class="count">(%s)</span>', 'user-registration' ),
					'plural'   => __( 'Draft <span class="count">(%s)</span>', 'user-registration' ),
					'context'  => '',
					'domain'   => 'user-registration',
				);
				break;
			case 'pending':
				/* translators: %s: count */
				$label = array(
					'singular' => __( 'Pending <span class="count">(%s)</span>', 'user-registration' ),
					'plural'   => __( 'Pending <span class="count">(%s)</span>', 'user-registration' ),
					'context'  => '',
					'domain'   => 'user-registration',
				);
				break;

			default:
				$label = $status->label_count;
				break;
		}

		return $label;
	}

	/**
	 * Define bulk actions.
	 *
	 * @return array
	 */
	public function ur_bulk_actions() {
		if ( isset( $_GET['status'] ) && 'trash' == $_GET['status'] ) {
			return array(
				'bulk_untrash' => esc_html__( 'Restore', 'user-registration-content-restriction' ),
				'bulk_delete'  => esc_html__( 'Delete permanently', 'user-registration-content-restriction' ),
			);
		}

		return array(
			'bulk_trash' => esc_html__( 'Move to trash', 'user-registration-content-restriction' ),
		);
	}

	/**
	 * Render the list table page, including header, notices, status filters and table.
	 */
	public function display_page() {
		$this->prepare_items();
		?>
			<div class="wrap">
				<h1 class="wp-heading-inline"><?php esc_html_e( 'User Registration' ); ?></h1>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=add-new-registration' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'user-registration' ); ?></a>
				<hr class="wp-header-end">
				<form id="registration-list" method="post">
					<input type="hidden" name="page" value="user-registration" />
					<?php
						$this->views();
						$this->search_box( __( 'Search Registration', 'user-registration' ), 'registration' );
						$this->display();

						wp_nonce_field( 'save', 'user_registration_nonce' );
					?>
				</form>
			</div>

		<?php
	}
}
