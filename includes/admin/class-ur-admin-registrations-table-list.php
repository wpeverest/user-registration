<?php
/**
 * User Registration Table List
 *
 * @version 1.2.0
 * @package UserRegistration\Admin\Registration
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Registrations table list class.
 */
class UR_Admin_Registrations_Table_List extends WP_List_Table {

	/**
	 * Initialize the registration table list.
	 */
	public function __construct() {
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
	 * Get list sortable columns.
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array(
			'title'  => array( 'title', false ),
			'author' => array( 'author', false ),
			'date'   => array( 'date', false ),
		);
	}

	/**
	 * Column cb.
	 *
	 * @param  object $registration
	 *
	 * @return string
	 */
	public function column_cb( $registration ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $registration->ID );
	}

	/**
	 * Return title column.
	 *
	 * @param  object $registration Registration forms datas.
	 *
	 * @return string
	 */
	public function column_title( $registration ) {
		$edit_link        = admin_url( 'admin.php?page=add-new-registration&amp;edit-registration=' . $registration->ID );
		$title            = _draft_or_post_title( $registration->ID );
		$post_type_object = get_post_type_object( 'user_registration' );
		$post_status      = $registration->post_status;

		// Title.
		$output = '<strong>';
		if ( 'trash' == $post_status ) {
			$output .= esc_html( $title );
		} else {
			$output .= '<a href="' . esc_url( $edit_link ) . '" class="row-title">' . esc_html( $title ) . '</a>';
		}
		$output .= '</strong>';

		// Get actions.
		$actions = array(
			'id' => sprintf( __( 'ID: %d', 'user-registration' ), $registration->ID ),
		);

		if ( current_user_can( $post_type_object->cap->edit_post, $registration->ID ) && 'trash' !== $post_status ) {
			$actions['edit'] = '<a href="' . esc_url( $edit_link ) . '">' . __( 'Edit', 'user-registration' ) . '</a>';
		}

		if ( current_user_can( $post_type_object->cap->delete_post, $registration->ID ) ) {
			if ( 'trash' == $post_status ) {
				$actions['untrash'] = '<a aria-label="' . esc_attr__( 'Restore this item from the Trash', 'user-registration' ) . '" href="' . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $registration->ID ) ), 'untrash-post_' . $registration->ID ) . '">' . esc_html__( 'Restore', 'user-registration' ) . '</a>';
			} elseif ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = '<a class="submitdelete" aria-label="' . esc_attr__( 'Move this item to the Trash', 'user-registration' ) . '" href="' . get_delete_post_link( $registration->ID ) . '">' . esc_html__( 'Trash', 'user-registration' ) . '</a>';
			}
			if ( 'trash' == $post_status || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = '<a class="submitdelete" aria-label="' . esc_attr__( 'Delete this item permanently', 'user-registration' ) . '" href="' . get_delete_post_link( $registration->ID, '', true ) . '">' . esc_html__( 'Delete permanently', 'user-registration' ) . '</a>';
			}
		}
		$duplicate_nonce = wp_create_nonce( 'user_registration_form_duplicate' . $registration->ID );

		if ( current_user_can( $post_type_object->cap->edit_post, $registration->ID ) ) {
			$preview_link = add_query_arg(
				array(
					'ur_preview' => 'true',
					'form_id'    => absint( $registration->ID ),
				),
				home_url()
			);

			$duplicate_link = admin_url( 'admin.php?page=user-registration&action=duplicate&nonce=' . $duplicate_nonce . '&form=' . $registration->ID );

			if ( 'trash' !== $post_status ) {
				$actions['view'] = '<a href="' . esc_url( $preview_link ) . '" rel="bookmark" target="_blank">' . __( 'Preview', 'user-registration' ) . '</a>';
			}

			if ( 'publish' === $post_status ) {
				$actions['duplicate'] = '<a href="' . esc_url( $duplicate_link ) . '">' . __( 'Duplicate', 'user-registration' ) . '</a>';
			}
		}

		$row_actions = array();

		foreach ( $actions as $action => $link ) {
			$row_actions[] = '<span class="' . esc_attr( $action ) . '">' . $link . '</span>';
		}

		$output .= '<div class="row-actions">' . implode( ' | ', $row_actions ) . '</div>';

		return $output;
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
		<button id="copy-shortcode" class="button ur-copy-shortcode " href="#" data-tip="<?php esc_attr_e( 'Copy Shortcode ! ', 'user - registration' ); ?>" data-copied="<?php esc_attr_e( 'Copied ! ', 'user - registration' ); ?>">
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
	 * Table list views.
	 *
	 * @return array
	 */
	protected function get_views() {
		$status_links = array();
		$num_posts    = wp_count_posts( 'user_registration', 'readable' );
		$class        = '';
		$total_posts  = array_sum( (array) $num_posts );

		// Subtract post types that are not included in the admin all list.
		foreach (
			get_post_stati(
				array(
					'show_in_admin_all_list' => false,
				)
			) as $state
		) {
			$total_posts -= $num_posts->$state;
		}

		$class = empty( $class ) && empty( $_REQUEST['status'] ) ? ' class="current"' : '';
		/* translators: %s: count */
		$status_links['all'] = "<a href='admin.php?page=user-registration'$class>" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_posts, 'posts', 'user-registration' ), number_format_i18n( $total_posts ) ) . '</a>';

		foreach (
			get_post_stati(
				array(
					'show_in_admin_status_list' => true,
				),
				'objects'
			) as $status
		) {
			$class       = '';
			$status_name = $status->name;

			if ( ! in_array(
				$status_name,
				array(
					'publish',
					'draft',
					'pending',
					'trash',
					'future',
					'private',
					'auto-draft',
				)
			)
			) {
				continue;
			}

			if ( empty( $num_posts->$status_name ) ) {
				continue;
			}

			if ( isset( $_REQUEST['status'] ) && $status_name == $_REQUEST['status'] ) {
				$class = ' class="current"';
			}

			$label = $this->get_status_label( $status_name, $status );

			$status_links[ $status_name ] = "<a href='admin.php?page=user-registration&amp;status=$status_name'$class>" . sprintf( translate_nooped_plural( $label, $num_posts->$status_name ), number_format_i18n( $num_posts->$status_name ) ) . '</a>';
		}

		return $status_links;
	}

	/**
	 * Get bulk actions.
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		if ( isset( $_GET['status'] ) && 'trash' == $_GET['status'] ) {
			return array(
				'untrash' => __( 'Restore', 'user-registration' ),
				'delete'  => __( 'Delete permanently', 'user-registration' ),
			);
		}

		return array(
			'trash' => __( 'Move to trash', 'user-registration' ),
		);
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination.
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' == $which && isset( $_GET['status'] ) && 'trash' == $_GET['status'] && current_user_can( 'delete_posts' ) ) {
			echo '<div class="alignleft actions"><a id="delete_all" class="button apply" href="' . esc_url( wp_nonce_url( admin_url( 'admin.php?page=user-registration&status=trash&empty_trash=1' ), 'empty_trash' ) ) . '">' . __( 'Empty trash', 'user-registration' ) . '</a></div>';
		}
	}

	/**
	 * Prepare table list items.
	 */
	public function prepare_items() {
		global $wpdb;

		$per_page     = $this->get_items_per_page( 'user_registration_per_page' );
		$current_page = $this->get_pagenum();

		// Query args.
		$args = array(
			'post_type'           => 'user_registration',
			'posts_per_page'      => $per_page,
			'ignore_sticky_posts' => true,
			'paged'               => $current_page,
		);

		// Handle the status query.
		if ( ! empty( $_REQUEST['status'] ) ) {
			$args['post_status'] = sanitize_text_field( $_REQUEST['status'] );
		}

		// Handle the search query.
		if ( ! empty( $_REQUEST['s'] ) ) {
			$args['s'] = sanitize_text_field( trim( wp_unslash( $_REQUEST['s'] ) ) ); // WPCS: sanitization ok, CSRF ok.
		}

		$args['orderby'] = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'date_created'; // WPCS: sanitization ok, CSRF ok.
		$args['order']   = isset( $_REQUEST['order'] ) && 'DESC' === strtoupper( $_REQUEST['order'] ) ? 'DESC' : 'ASC';

		// Get the registrations
		$registrations = new WP_Query( $args );
		$this->items   = $registrations->posts;

		// Set the pagination
		$this->set_pagination_args(
			array(
				'total_items' => $registrations->found_posts,
				'per_page'    => $per_page,
				'total_pages' => $registrations->max_num_pages,
			)
		);
	}
}
