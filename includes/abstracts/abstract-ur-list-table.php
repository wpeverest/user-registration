<?php
/**
 * User Registration Abstract List Table class
 *
 * @package UserRegistrationAbstractListTable
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * User Registration Abstract List Table class
 *
 * This abstract class enhances WP_List_Table making it ready to use.
 *
 * By extending this class we can focus on describing how our table looks like,
 * which columns needs to be shown, filter, ordered by and more and forget about the details.
 *
 * This class supports:
 *  - Bulk actions
 *  - Search
 *  - Sortable columns
 *  - Automatic translations of the columns
 *
 * @since  2.0.0
 */
abstract class UR_List_Table extends WP_List_Table {

	/**
	 * The Page name
	 *
	 * @var $page
	 */
	protected $page;

	/**
	 * Post type name, used to get options
	 *
	 * @var $post_type
	 */
	protected $post_type;

	/**
	 * Option name for per page.
	 *
	 * @var $per_page_option
	 */
	protected $per_page_option;

	/**
	 * Option name for per page.
	 *
	 * @var $addnew_action
	 */
	protected $addnew_action;

	/**
	 * How many items do we render per page?
	 *
	 * @var $items_per_page
	 */
	protected $items_per_page = 10;

	/**
	 * Enables search in this table listing. If this array
	 * is empty it means the listing is not searchable.
	 *
	 * @var $search_by
	 */
	protected $search_by = array();

	/**
	 * Columns to show in the table listing. It is a key => value pair. The
	 * key must much the table column name and the value is the label, which is
	 * automatically translated.
	 *
	 * @var $columns
	 */
	protected $columns = array();

	/**
	 * Defines the row-actions. It expects an array where the key
	 * is the column name and the value is an array of actions.
	 *
	 * The array of actions are key => value, where key is the method name
	 * (with the prefix row_action_<key>) and the value is the label
	 * and title.
	 *
	 * @var $row_actions
	 */
	protected $row_actions = array();

	/**
	 * Enables sorting, it expects an array
	 * of columns (the column names are the values)
	 *
	 * @var $sort_by
	 */
	protected $sort_by = array(
		'title'  => array( 'title', false ),
		'date'   => array( 'date', true ),
		'author' => array( 'author', false ),
	);

	/**
	 * Enables bulk actions. It must be an array where the key is the action name
	 * and the value is the label (which is translated automatically). It is important
	 * to notice that it will check that the method exists (`bulk_$name`) and will throw
	 * an exception if it does not exists.
	 *
	 * This class will automatically check if the current request has a bulk action, will do the
	 * validations and afterwards will execute the bulk method, with two arguments. The first argument
	 * is the array with primary keys, the second argument is a string with a list of the primary keys,
	 * escaped and ready to use (with `IN`).
	 *
	 * @var $bulk_actions
	 */
	protected $bulk_actions = array();

	/**
	 * Reads `$this->bulk_actions` and returns an array that WP_List_Table understands. It
	 * also validates that the bulk method handler exists. It throws an exception because
	 * this is a library meant for developers and missing a bulk method is a development-time error.
	 *
	 * @throws RuntimeException RuntimeException.
	 */
	protected function get_bulk_actions() {
		if ( isset( $_GET['status'] ) && ( 'trashed' == $_GET['status'] || 'trash' == $_GET['status'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$actions = array(
				'untrash' => __( 'Restore', 'user-registration' ),
				'delete'  => __( 'Delete permanently', 'user-registration' ),
			);
		} else {
			$actions = array(
				'trash' => __( 'Move to trash', 'user-registration' ),
			);
		}

		foreach ( $this->bulk_actions as $action => $label ) {
			if ( ! is_callable( array( $this, 'bulk_' . $action ) ) ) {
				throw new RuntimeException(
					/* translators: %s: Error message */
					sprintf( esc_html__( 'The bulk action %s does not have a callback method', 'user-registration' ), esc_html( $action ) )
				);
			}

			$actions[ $action ] = $label;
		}
		return $actions;
	}

	/**
	 * Prepares the _column_headers property which is used by WP_Table_List at rendering.
	 * It merges the columns and the sortable columns.
	 */
	protected function prepare_column_headers() {
		$this->_column_headers = array(
			$this->get_columns(),
			$this->get_hidden_columns(),
			$this->get_sortable_columns(),
		);
	}

	/**
	 * Reads $this->sort_by and returns the columns name in a format that WP_Table_List
	 * expects
	 */
	public function get_sortable_columns() {
		$sort_by = array();
		foreach ( $this->sort_by as $column => $value ) {
			$sort_by[ $column ] = array( $column, true );
		}
		return $sort_by;
	}

	/**
	 * Returns the columns names for rendering. It adds a checkbox for selecting everything
	 * as the first column
	 */
	public function get_columns() {
		$columns = array_merge(
			array( 'cb' => '<input type="checkbox" />' ),
			$this->columns
		);

		return $columns;
	}

	/**
	 * Prepares the data to feed WP_Table_List.
	 *
	 * This has the core for selecting, sorting and filting data. To keep the code simple
	 * its logic is split among many methods (get_items_query_*).
	 *
	 * Beside populating the items this function will also count all the records that matches
	 * the filtering criteria and will do fill the pagination variables.
	 */
	public function prepare_items() {

		$this->prepare_column_headers();
		$per_page     = $this->get_items_per_page( $this->per_page_option );
		$current_page = $this->get_pagenum();

		// Query args.
		$args = array(
			'post_type'           => $this->post_type,
			'posts_per_page'      => $per_page,
			'ignore_sticky_posts' => true,
			'paged'               => $current_page,
		);

		// Handle the status query.
		if ( ! empty( $_REQUEST['status'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$args['post_status'] = sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		// Handle the search query.
		if ( ! empty( $_REQUEST['s'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$args['s'] = trim( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$args['orderby'] = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'date_created'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$args['order']   = isset( $_REQUEST['order'] ) && 'ASC' === strtoupper( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) ? 'ASC' : 'DESC'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Get the registrations.
		$query_posts = new WP_Query( $args );
		$this->items = $query_posts->posts;

		// Set the pagination.
		$this->set_pagination_args(
			array(
				'total_items' => $query_posts->found_posts,
				'per_page'    => $per_page,
				'total_pages' => $query_posts->max_num_pages,
			)
		);
	}

	/**
	 * Implements the logic behind processing an action once an action link is clicked on the list table.
	 */
	protected function process_row_actions() {
		if ( isset( $_GET['page'] ) ) {
			$action = ! empty( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : null;
			$action = $action ? $action : ( ! empty( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '' );
			$action = ( $action && '-1' !== $action ) ? $action : ( ! empty( $_POST['action2'] ) ? sanitize_text_field( wp_unslash( $_POST['action2'] ) ) : '' );
			$action = ! empty( $action ) ? $action : ( ( isset( $_REQUEST['empty_trash'] ) && ! empty( $_REQUEST['empty_trash'] ) ) ? 'empty_trash' : '' );
			$nonce  = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : null;
			$nonce  = $nonce ? $nonce : ( isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '' );
			switch ( $action ) {
				case 'duplicate':
					$post_id = isset( $_GET['post-id'] ) && is_numeric( $_GET['post-id'] ) ? sanitize_text_field( wp_unslash( $_GET['post-id'] ) ) : '';

					if ( ! current_user_can( 'publish_posts' ) ) {
						wp_die( esc_html__( 'You do not have permission to create post!', 'user-registration' ) );
					} elseif ( ! wp_verify_nonce( $nonce, 'ur_duplicate_post_' . $post_id ) ) {
						wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'user-registration' ) );
					} else {
						$this->duplicate_post( $post_id );
					}
					break;

				case 'bulk_trash':
				case 'trash':
					check_admin_referer( 'bulk-' . $this->_args['plural'] );

					if ( ! current_user_can( 'delete_posts' ) ) {
						wp_die( esc_html__( 'You do not have permission to trash posts!', 'user-registration' ) );
					} else {
						$post_ids = isset( $_REQUEST[ $this->_args['singular'] ] ) ? array_map( 'absint', (array) $_REQUEST[ $this->_args['singular'] ] ) : '';
						$this->bulk_trash( $post_ids );
					}

					break;

				case 'bulk_untrash':
				case 'untrash':
					check_admin_referer( 'bulk-' . $this->_args['plural'] );

					if ( ! current_user_can( 'edit_posts' ) ) {
						wp_die( esc_html__( 'You do not have permission to untrash posts!', 'user-registration' ) );
					} else {
						$post_ids = isset( $_REQUEST[ $this->_args['singular'] ] ) ? array_map( 'absint', (array) $_REQUEST[ $this->_args['singular'] ] ) : '';
						$this->bulk_untrash( $post_ids );
					}

					break;

				case 'bulk_delete':
				case 'delete':
					check_admin_referer( 'bulk-' . $this->_args['plural'] );

					if ( ! current_user_can( 'delete_posts' ) ) {
						wp_die( esc_html__( 'You do not have permission to delete posts!', 'user-registration' ) );
					} else {
						$post_ids = isset( $_REQUEST[ $this->_args['singular'] ] ) ? array_map( 'absint', (array) $_REQUEST[ $this->_args['singular'] ] ) : '';
						$this->bulk_delete( $post_ids );
					}
					break;

				case 'empty_trash':
					if ( ! current_user_can( 'delete_posts' ) ) {
						wp_die( esc_html__( 'You do not have permission to delete posts!', 'user-registration' ) );
					} elseif ( ! wp_verify_nonce( $nonce, 'empty_trash' ) ) {
						wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'user-registration' ) );
					} else {
						$this->empty_trash();
					}
					break;
			}
		}
	}

	/**
	 * Duplicate a content access post.
	 *
	 * @param mixed $post_id Post Id.
	 * @since 2.0.0
	 */
	public function duplicate_post( $post_id ) {
		$post         = get_post( $post_id );
		$current_user = wp_get_current_user();

		/*
		 * if post data exists, create the post duplicate.
		 */
		if ( isset( $post ) && null !== $post ) {

			if ( 'publish' !== $post->post_status ) {
				return false;
			}
			$post->post_content = str_replace( '\\', '\\\\', $post->post_content );

			/*
			 * new post data array.
			 */
			$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $current_user->ID,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $post->post_name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => $post->post_status,
				'post_title'     => esc_html__( 'Copy of ', 'user-registration' ) . $post->post_title,
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order,
			);

			/*
			 * Insert the post by wp_insert_post() function.
			 */
			$new_post_id = wp_insert_post( $args );

			/*
			 * Duplicate all post meta just in two SQL queries.
			 */
			global $wpdb;
			$post_meta_infos = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d", $post_id ) );

			if ( count( $post_meta_infos ) ) {
				foreach ( $post_meta_infos as $meta_info ) {
					$meta_key = $meta_info->meta_key;

					if ( '_wp_old_slug' === $meta_key ) {
						continue;
					}
					if ( substr( $meta_key, 0, 18 ) === 'user_registration_' ) {

						$meta_value = addslashes( $meta_info->meta_value );
						if ( ! add_post_meta( $new_post_id, $meta_key, $meta_value, true ) ) {
							update_post_meta( $new_post_id, $meta_key, $meta_value );
						}
					}
				}
			}
			/**
			 * Action to add content after form duplication.
			 *
			 * @param array $post_id The post id.
			 * @param array $new_post_id The new post id.
			 */
			do_action( 'user_registration_after_form_duplication', $post_id, $new_post_id );

			/*
			 * Finally, redirect to the edit post screen for the new draft.
			 */
			$duplicate_link = $this->get_duplicate_link( $new_post_id );
			wp_redirect( $duplicate_link );
			exit;
		}
	}

	/**
	 * Bulk trash posts.
	 *
	 * @since 2.0.0
	 *
	 * @param array $post_list Post List.
	 */
	public function bulk_trash( $post_list = array() ) {
		if ( ! empty( $post_list ) ) {
			$qty = count( $post_list );

			if ( $qty > 0 ) {
				foreach ( $post_list as $post_id ) {
					wp_trash_post( $post_id );
				}

				$status = isset( $_GET['status'] ) ? '&status=' . sanitize_text_field( wp_unslash( $_GET['status'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

				wp_redirect( admin_url( 'admin.php?page=' . $this->page . '' . $status . '&trashed=' . $qty ) );
				exit;
			}
		}
	}

	/**
	 * Bulk untrash posts.
	 *
	 * @since 2.0.0
	 *
	 * @param array $post_list Post List.
	 */
	private function bulk_untrash( $post_list = array() ) {
		foreach ( $post_list as $post_id ) {
			wp_untrash_post( $post_id );
		}

		wp_redirect( admin_url( 'admin.php?page=' . $this->page . '&status=trash&untrashed=' . count( $post_list ) ) );
		exit;
	}

	/**
	 * Bulk delete (permanently) post.
	 *
	 * @since 2.0.0
	 *
	 * @param array $post_list Post List.
	 */
	private function bulk_delete( $post_list = array() ) {
		$qty = is_array( $post_list ) ? count( $post_list ) : 0;
		if ( $qty > 0 ) {
			foreach ( $post_list as $post_id ) {
				wp_delete_post( $post_id, true );
			}

			$status = isset( $_GET['status'] ) ? '&status=' . sanitize_text_field( wp_unslash( $_GET['status'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			wp_redirect( admin_url( 'admin.php?page=' . $this->page . '' . $status . '&deleted=' . $qty ) );
			exit();
		}
	}

	/**
	 * Empty trash.
	 *
	 * @since 2.0.0
	 */
	public function empty_trash() {
		$post_list = get_posts(
			array(
				'post_type'           => $this->post_type,
				'ignore_sticky_posts' => true,
				'nopaging'            => true,
				'post_status'         => 'trash',
				'fields'              => 'ids',
			)
		);

		foreach ( $post_list as $webhook_id ) {
			wp_delete_post( $webhook_id, true );
		}

		wp_redirect( admin_url( 'admin.php?page=' . $this->page . '&deleted=' . count( $post_list ) ) );
		exit;
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination.
	 *
	 * @param string $which Which.
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' === $which && isset( $_GET['status'] ) && 'trash' === $_GET['status'] && current_user_can( 'delete_posts' ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$empty_trash_link = esc_url( wp_nonce_url( admin_url( 'admin.php?page=' . $this->page . '&action=empty_trash' ), 'empty_trash' ) );

			printf(
				'<div class="alignleft actions"><a id="delete_all" class="button apply" href="%s">%s</a></div>',
				esc_url( $empty_trash_link ),
				esc_html__( 'Empty trash', 'user-registration' )
			);
		}
	}

	/**
	 * Renders the checkbox for each row, this is the first column and it is named ID regardless
	 * of how the primary key is named (to keep the code simpler). The bulk actions will do the proper
	 * name transformation though using `$this->ID`.
	 *
	 * @param object $row Row.
	 */
	public function column_cb( $row ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', esc_attr( $this->_args['singular'] ), esc_attr( $row->ID ) );
	}

	/**
	 * Column: Title.
	 *
	 * @param  object $post Post.
	 *
	 * @return string
	 */
	public function column_title( $post ) {
		$edit_link            = $this->get_edit_links( $post );
		$title                = _draft_or_post_title( $post->ID );
		$post_status          = $post->post_status;
		$current_status_trash = ( 'trash' === $post_status );

		ob_start();

		//
		// Prepare title label.
		//

		printf( '<strong>' );
		printf( sprintf( '<div class="ur-edit-title">' ) );

		if ( $current_status_trash ) {
			echo esc_html( $title );
		} else {
			printf( '<a href="%s" class="row-title">%s</a>', esc_url( $edit_link ), esc_html( $title ) );
		}
		printf( '</div>' );
		printf( '</strong>' );

		//
		// Create html for column actions.
		//

		$row_actions = array();

		$actions = $this->get_row_actions( $post );

		foreach ( $actions as $action => $link ) {
			$row_actions[] = sprintf( '<span class="%s">%s</span>', esc_attr( $action ), $link );
		}
		printf( '<div class="row-actions">%s</div>', implode( ' | ', $row_actions ) ); //phpcs:ignore

		return ob_get_clean();
	}

	/**
	 * Column: Created Date.
	 *
	 * @param  object $items Items.
	 *
	 * @return string
	 */
	public function column_date( $items ) {
		$post = get_post( $items->ID );

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
				/* translators: %s - Human readable time */
				__( '%s ago', 'user-registration' ),
				human_time_diff( $time )
			);
		} else {
			$h_time = mysql2date( __( 'Y/m/d', 'user-registration' ), $m_time );
		}

		return '<abbr title="' . $t_time . '">' . $h_time . '</abbr>';
	}

	/**
	 * Return author column.
	 *
	 * @param  object $items Items.
	 *
	 * @return string
	 */
	public function column_author( $items ) {
		$user = get_user_by( 'id', $items->post_author );

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
	 * Display row action.
	 *
	 * @param mixed $post Post.
	 */
	abstract public function get_row_actions( $post );

	/**
	 * Display edit links.
	 *
	 * @param mixed $post Post.
	 */
	abstract public function get_edit_links( $post );

	/**
	 * Display duplicate links.
	 *
	 * @param mixed $post Post.
	 */
	abstract public function get_duplicate_link( $post );


	/**
	 * Display item counts by status and links.
	 *
	 * @return array
	 */
	protected function get_views() {
		$status_links = array();
		$post_counts  = wp_count_posts( $this->post_type, 'readable' );
		$class        = empty( $_REQUEST['status'] ) ? ' class="current"' : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$total_posts  = array_sum( (array) $post_counts );

		// Substract counts of posts with a status that is not included in "All" list like trash, inherit etc.
		$stati = get_post_stati(
			array(
				'show_in_admin_all_list' => false,
			)
		);
		foreach ( $stati as $state ) {
			$total_posts -= $post_counts->$state;
		}

		$status_links['all'] = sprintf(
			'<a href="admin.php?page=%s" %s >%s (%s)</a>',
			$this->page,
			$class,
			esc_html__( 'All', 'user-registration' ),
			number_format_i18n( $total_posts )
		);

		$allowed_status = array(
			'publish',
			'draft',
			'pending',
			'trash',
			'future',
			'private',
			'auto-draft',
		);
		$stati_objects  = get_post_stati(
			array(
				'show_in_admin_status_list' => true,
			),
			'objects'
		);

		foreach ( $stati_objects as $status ) {
			$status_name    = $status->name;
			$current_status = ( ! empty( $_REQUEST['status'] ) && $status_name === $_REQUEST['status'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( empty( $post_counts->$status_name ) || ! in_array( $status_name, $allowed_status, true ) ) {
				continue;
			}

			$status_links[ $status_name ] = sprintf(
				'<a href="admin.php?page=%s&amp;status=%s" class="%s">%s (%s)</a>',
				$this->page,
				$status_name,
				$current_status ? 'current' : '',
				esc_html__( $status->label, $this->page ), //phpcs:ignore
				number_format_i18n( $post_counts->$status_name )
			);
		}
		return $status_links;
	}

	/**
	 * Process any pending actions.
	 */
	public function process_actions() {
		// TODO :: process bulk action.
		$this->process_row_actions();

		if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			// _wp_http_referer is used only on bulk actions, we remove it to keep the $_GET shorter
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ); // phpcs:ignore
			exit;
		}
	}

	/**
	 * Get a list of hidden columns.
	 *
	 * @return array
	 */
	protected function get_hidden_columns() {
		return get_hidden_columns( $this->screen );
	}
}
