<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
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
 *	- Bulk actions
 *	- Search
 *  - Sortable columns
 *  - Automatic translations of the columns
 *
 * @since  2.0.0
 */
abstract class UR_List_Table extends WP_List_Table {

	/**
	 * The Page name
	 */
	protected $page;

	/**
	 * Post type name, used to get options
	 */
	protected $post_type;

	/**
	 * Option name for per page.
	 */
	protected $per_page_option;

	/**
	 * Option name for per page.
	 */
	protected $addnew_action;

	/**
	 * How many items do we render per page?
	 */
	protected $items_per_page = 10;

	/**
	 * Enables search in this table listing. If this array
	 * is empty it means the listing is not searchable.
	 */
	protected $search_by = array();

	/**
	 * Columns to show in the table listing. It is a key => value pair. The
	 * key must much the table column name and the value is the label, which is
	 * automatically translated.
	 */
	protected $columns = array();

	/**
	 * Defines the row-actions. It expects an array where the key
	 * is the column name and the value is an array of actions.
	 *
	 * The array of actions are key => value, where key is the method name
	 * (with the prefix row_action_<key>) and the value is the label
	 * and title.
	 */
	protected $row_actions = array();

	/**
	 * The Primary key of our table
	 */
	protected $ID = 'ID';

	// protected $page   = ( isset( $_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
	// protected $forms  = ur_get_all_user_registration_form();
	// protected $latest = key( $forms );

		// @TODO::Verify Nonce
	protected $form_id = 0;

	/**
	 * Enables sorting, it expects an array
	 * of columns (the column names are the values)
	 */
	protected $sort_by = array();

	protected $filter_by = array();

	/**
	 * @var array The status name => count combinations for this table's items. Used to display status filters.
	 */
	protected $status_counts = array();

	/**
	 * @var array Notices to display when loading the table. Array of arrays of form array( 'class' => {updated|error}, 'message' => 'This is the notice text display.' ).
	 */
	protected $admin_notices = array();

	/**
	 * @var string Localised string displayed in the <h1> element above the able.
	 */
	protected $table_header;

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
	 */
	protected $bulk_actions = array();

	/**
	 * Makes translation easier, it basically just wraps
	 * `_x` with some default (the package name).
	 *
	 * @deprecated 3.0.0
	 */
	protected function translate( $text, $context = '' ) {
		return $text;
	}

	/**
	 * Reads `$this->bulk_actions` and returns an array that WP_List_Table understands. It
	 * also validates that the bulk method handler exists. It throws an exception because
	 * this is a library meant for developers and missing a bulk method is a development-time error.
	 */
	protected function get_bulk_actions() {
		$actions = array();
		foreach ( $this->bulk_actions as $action => $label ) {
			if ( ! is_callable( array( $this, 'bulk_' . $action ) ) ) {
				throw new RuntimeException( "The bulk action $action does not have a callback method" );
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
			array(),
			$this->get_sortable_columns(),
		);
	}

	/**
	 * Reads $this->sort_by and returns the columns name in a format that WP_Table_List
	 * expects
	 */
	public function get_sortable_columns() {
		$sort_by = array();
		foreach ( $this->sort_by as $column ) {
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
	 * Get prepared LIMIT clause for items query
	 *
	 * @global wpdb $wpdb
	 *
	 * @return string Prepared LIMIT clause for items query.
	 */
	protected function get_items_query_limit() {
		global $wpdb;

		$per_page = $this->get_items_per_page( $this->package . '_items_per_page', $this->items_per_page );
		return $wpdb->prepare( 'LIMIT %d', $per_page );
	}

	/**
	 * Returns the number of items to offset/skip for this current view.
	 *
	 * @return int
	 */
	protected function get_items_offset() {
		$per_page = $this->get_items_per_page( $this->package . '_items_per_page', $this->items_per_page );
		$current_page = $this->get_pagenum();
		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}

		return $offset;
	}

	/**
	 * Get prepared OFFSET clause for items query
	 *
	 * @global wpdb $wpdb
	 *
	 * @return string Prepared OFFSET clause for items query.
	 */
	protected function get_items_query_offset() {
		global $wpdb;

		return $wpdb->prepare( 'OFFSET %d', $this->get_items_offset() );
	}

	/**
	 * Prepares the ORDER BY sql statement. It uses `$this->sort_by` to know which
	 * columns are sortable. This requests validates the orderby $_GET parameter is a valid
	 * column and sortable. It will also use order (ASC|DESC) using DESC by default.
	 */
	protected function get_items_query_order() {
		if ( empty( $this->sort_by ) ) {
			return '';
		}

		$orderby = esc_sql( $this->get_request_orderby() );
		$order   = esc_sql( $this->get_request_order() );

		return "ORDER BY {$orderby} {$order}";
	}

	/**
	 * Return the sortable column specified for this request to order the results by, if any.
	 *
	 * @return string
	 */
	protected function get_request_orderby() {

		$valid_sortable_columns = array_values( $this->sort_by );

		if ( ! empty( $_GET['orderby'] ) && in_array( $_GET['orderby'], $valid_sortable_columns ) ) {
			$orderby = sanitize_text_field( $_GET['orderby'] );
		} else {
			$orderby = $valid_sortable_columns[0];
		}

		return $orderby;
	}

	/**
	 * Return the sortable column order specified for this request.
	 *
	 * @return string
	 */
	protected function get_request_order() {

		if ( ! empty( $_GET['order'] ) && 'desc' === strtolower( $_GET['order'] ) ) {
			$order = 'DESC';
		} else {
			$order = 'ASC';
		}

		return $order;
	}

	/**
	 * Return the status filter for this request, if any.
	 *
	 * @return string
	 */
	protected function get_request_status() {
		$status = ( ! empty( $_GET['status'] ) ) ? $_GET['status'] : '';
		return $status;
	}

	/**
	 * Return the search filter for this request, if any.
	 *
	 * @return string
	 */
	protected function get_request_search_query() {
		$search_query = ( ! empty( $_GET['s'] ) ) ? $_GET['s'] : '';
		return $search_query;
	}

	/**
	 * Process and return the columns name. This is meant for using with SQL, this means it
	 * always includes the primary key.
	 *
	 * @return array
	 */
	protected function get_table_columns() {
		$columns = array_keys( $this->columns );
		if ( ! in_array( $this->ID, $columns ) ) {
			$columns[] = $this->ID;
		}

		return $columns;
	}

	/**
	 * Check if the current request is doing a "full text" search. If that is the case
	 * prepares the SQL to search texts using LIKE.
	 *
	 * If the current request does not have any search or if this list table does not support
	 * that feature it will return an empty string.
	 *
	 * TODO:
	 *   - Improve search doing LIKE by word rather than by phrases.
	 *
	 * @return string
	 */
	protected function get_items_query_search() {
		global $wpdb;

		if ( empty( $_GET['s'] ) || empty( $this->search_by ) ) {
			return '';
		}

		$filter  = array();
		foreach ( $this->search_by as $column ) {
			$filter[] = $wpdb->prepare('`' . $column . '` like "%%s%"', $wpdb->esc_like( $_GET['s'] ));
		}
		return implode( ' OR ', $filter );
	}

	/**
	 * Prepares the SQL to filter rows by the options defined at `$this->filter_by`. Before trusting
	 * any data sent by the user it validates that it is a valid option.
	 */
	protected function get_items_query_filters() {
		global $wpdb;

		if ( ! $this->filter_by || empty( $_GET['filter_by'] ) || ! is_array( $_GET['filter_by'] ) ) {
			return '';
		}

		$filter = array();

		foreach ( $this->filter_by as $column => $options ) {
			if ( empty( $_GET['filter_by'][ $column ] ) || empty( $options[ $_GET['filter_by'][ $column ] ] ) ) {
				continue;
			}

			$filter[] = $wpdb->prepare( "`$column` = %s", $_GET['filter_by'][ $column ] );
		}

		return implode( ' AND ', $filter );

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
		if ( ! empty( $_REQUEST['status'] ) ) {
			$args['post_status'] = sanitize_text_field( $_REQUEST['status'] );
		}

		// Handle the search query.
		if ( ! empty( $_REQUEST['s'] ) ) {
			$args['s'] = sanitize_text_field( trim( wp_unslash( $_REQUEST['s'] ) ) );
		}

		$args['orderby'] = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'date_created';
		$args['order']   = isset( $_REQUEST['order'] ) && 'DESC' === strtoupper( $_REQUEST['order'] ) ? 'DESC' : 'ASC';

		// Get the registrations
		$query_posts = new WP_Query( $args );
		$this->items       = $query_posts->posts;

		// Set the pagination
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
	 *
	 * @param int $action_id
	 * @param string $row_action_type The type of action to perform on the action.
	 */
	protected function process_row_actions() {
		if ( isset( $_GET['page'] ) && $this->page === $_GET['page'] ) {
			$action = ! empty( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : null;
			$action = $action ? $action : ( ! empty( $_POST['action'] ) ? sanitize_text_field( $_POST['action'] ) : '' );
			$action = ( $action && '-1' !== $action ) ? $action : ( ! empty( $_POST['action2'] ) ? sanitize_text_field( $_POST['action2'] ) : '' );
			$nonce  = isset( $_REQUEST['nonce'] ) ? $_REQUEST['nonce'] : null;
			$nonce  = $nonce ? $nonce : ( isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : '' );
			switch ( $action ) {
				case 'duplicate':
					$post_id = isset( $_GET['post-id'] ) && is_numeric( $_GET['post-id'] ) ? $_GET['post-id'] : '';

					if ( ! current_user_can( 'publish_posts' ) ) {
						wp_die( esc_html__( 'You do not have permission to create Content Access Post!', 'user-registration-content-restriction' ) );
					} elseif ( ! wp_verify_nonce( $nonce, 'ur_duplicate_post_' . $post_id ) ) {
						wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'user-registration' ) );
					} else {
						$this->duplicate_post( $post_id );
					}
					break;

				case 'bulk_trash':
					if ( ! current_user_can( 'delete_posts' ) ) {
						wp_die( esc_html__( 'You do not have permission to trash Content Access Posts!', 'user-registration-content-restriction' ) );
					} else {
						$post_ids = array_map( 'absint', (array) $_REQUEST[$this->_args['singular']] );
						$this->bulk_trash( $post_ids );
					}
					break;

				case 'bulk_untrash':
					if ( ! current_user_can( 'edit_posts' ) ) {
						wp_die( esc_html__( 'You do not have permission to untrash Content Access Posts!', 'user-registration-content-restriction' ) );
					} else {
						$post_ids = array_map( 'absint', (array) $_REQUEST[$this->_args['singular']] );
						$this->bulk_untrash( $post_ids );
					}
					break;

				case 'bulk_delete':
					if ( ! current_user_can( 'delete_posts' ) ) {
						wp_die( esc_html__( 'You do not have permission to delete Content Access Posts!', 'user-registration-content-restriction' ) );
					} else {
						$post_ids = array_map( 'absint', (array) $_REQUEST[$this->_args['singular']] );
						$this->bulk_delete( $post_ids );
					}
					break;

				case 'empty_trash':
					if ( ! current_user_can( 'delete_posts' ) ) {
						wp_die( esc_html__( 'You do not have permission to delete Content Access Posts!', 'user-registration-content-restriction' ) );
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
				$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";

				foreach ( $post_meta_infos as $meta_info ) {
					$meta_key = $meta_info->meta_key;

					if ( '_wp_old_slug' === $meta_key ) {
						continue;
					}
					$meta_value      = addslashes( $meta_info->meta_value );
					$sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
				}
				$sql_query .= implode( ' UNION ALL ', $sql_query_sel );
				$wpdb->query( $sql_query );
			}

			/*
			 * Finally, redirect to the edit post screen for the new draft.
			 */
			$duplicate_link = $this->get_duplicate_link($new_post_id);
			wp_redirect( $duplicate_link );
			exit;
		}
	}

	/**
	 * Bulk trash posts.
	 *
	 * @since 2.0.0
	 *
	 * @param array $post_list
	 */
	public function bulk_trash( $post_list = array() ) {
		foreach ( $post_list as $post_id ) {
			wp_trash_post( $post_id );
		}

		$qty    = count( $post_list );
		$status = isset( $_GET['status'] ) ? '&status=' . sanitize_text_field( $_GET['status'] ) : '';

		wp_redirect( admin_url( 'admin.php?page='. $this->page .'' . $status . '&trashed=' . $qty ) );
		exit;
	}

	/**
	 * Bulk untrash posts.
	 *
	 * @since 2.0.0
	 *
	 * @param array $post_list
	 */
	private function bulk_untrash( $post_list = array() ) {
		foreach ( $post_list as $post_id ) {
			wp_untrash_post( $post_id );
		}

		wp_redirect( admin_url( 'admin.php?page='. $this->page .'&status=trash&untrashed=' . count( $post_list ) ) );
		exit;
	}

	/**
	 * Bulk delete (permanently) post.
	 *
	 * @since 2.0.0
	 *
	 * @param array $post_list
	 */
	private function bulk_delete( $post_list = array() ) {
		foreach ( $post_list as $post_id ) {
			wp_delete_post( $post_id, true );
		}

		$qty    = count( $post_list );
		$status = isset( $_GET['status'] ) ? '&status=' . sanitize_text_field( $_GET['status'] ) : '';

		wp_redirect( admin_url( 'admin.php?page='. $this->page .'' . $status . '&deleted=' . $qty ) );
		exit();
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

		wp_redirect( admin_url( 'admin.php?page='. $this->page .'&deleted=' . count( $post_list ) ) );
		exit;
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination.
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' === $which && isset( $_GET['status'] ) && 'trash' === $_GET['status'] && current_user_can( 'delete_posts' ) ) {
			$empty_trash_link = esc_url( wp_nonce_url( admin_url( 'admin.php?page='. $this->page .'&action=empty_trash' ), 'empty_trash' ) );

			printf(
				'<div class="alignleft actions"><a id="delete_all" class="button apply" href="%s">%s</a></div>',
				$empty_trash_link,
				esc_html__( 'Empty trash', 'user-registration')
			);
		}
	}

	/**
	 * Set the data for displaying. It will attempt to unserialize (There is a chance that some columns
	 * are serialized). This can be override in child classes for futher data transformation.
	 */
	protected function set_items( array $items ) {
		$this->items = array();
		foreach ( $items as $item ) {
			$this->items[ $item[ $this->ID ] ] = array_map( 'maybe_unserialize', $item );
		}
	}

	/**
	 * Renders the checkbox for each row, this is the first column and it is named ID regardless
	 * of how the primary key is named (to keep the code simpler). The bulk actions will do the proper
	 * name transformation though using `$this->ID`.
	 */
	public function column_cb( $row ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />',  esc_attr($this->_args['singular'] ),  esc_attr( $row->ID ) );
	}

	/**
	 * Column: Title.
	 *
	 * @param  object $post
	 *
	 * @return string
	 */
	public function column_title( $post ) {
		$edit_link 			  = $this->get_edit_links($post);
		$title                = _draft_or_post_title( $post);
		$post_status          = $post->post_status;
		$current_status_trash = ( 'trash' === $post_status );

		ob_start();

		//
		// Prepare title label.
		//

		printf( '<strong>' );

		if ( $current_status_trash ) {
			echo esc_html( $title );
		} else {
			printf( '<a href="%s" class="row-title">%s</a>', esc_url( $edit_link ), esc_html( $title ) );
		}
		printf( '</strong>' );

		//
		// Create html for column actions.
		//

		$row_actions = array();

		$actions = $this->get_row_actions($post);

		foreach ( $actions as $action => $link ) {
			$row_actions[] = sprintf( '<span class="%s">%s</span>', esc_attr( $action ), $link );
		}
		printf( '<div class="row-actions">%s</div>', implode( ' | ', $row_actions ) );

		return ob_get_clean();
	}

	/**
	 * @param mixed $post
	 *
	 */
	abstract public function get_row_actions( $post );

	/**
	 * @param mixed $post
	 *
	 */
	abstract public function get_edit_links( $post );

	/**
	 * @param mixed $post
	 *
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
		$class        = empty( $_REQUEST['status'] ) ? ' class="current"' : '';
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
			esc_html__( 'All', 	$this->page ),
			number_format_i18n( $total_posts )
		);

		$allowed_status = array( 'publish',
		'draft',
		'pending',
		'trash',
		'future',
		'private',
		'auto-draft' );
		$stati_objects  = get_post_stati(
			array(
				'show_in_admin_status_list' => true,
			),
			'objects'
		);

		foreach ( $stati_objects as $status ) {
			$status_name    = $status->name;
			$current_status = ( ! empty( $_REQUEST['status'] ) && $status_name === $_REQUEST['status'] );

			if ( empty( $post_counts->$status_name ) || ! in_array( $status_name, $allowed_status, true ) ) {
				continue;
			}

			$status_links[ $status_name ] = sprintf(
				'<a href="admin.php?page=%s&amp;status=%s" class="%s">%s (%s)</a>',
				$this->page,
				$status_name,
				$current_status ? 'current' : '',
				esc_html__( $status->label, $this->page ),
				number_format_i18n( $post_counts->$status_name )
			);
		}
		return $status_links;
	}

	/**
	 * Display the table heading and search query, if any
	 */
	protected function display_header() {
		echo '<h1 class="wp-heading-inline">' . esc_attr( $this->table_header ) . '</h1>';
		if ( $this->get_request_search_query() ) {
			/* translators: %s: search query */
			echo '<span class="subtitle">' . esc_attr( sprintf( __( 'Search results for "%s"', 'user-registration' ), $this->get_request_search_query() ) ) . '</span>';
		}
		echo '<hr class="wp-header-end">';
	}

	/**
	 * Display the table heading and search query, if any
	 */
	protected function display_admin_notices() {
		foreach ( $this->admin_notices as $notice ) {
			echo '<div id="message" class="' . $notice['class'] . '">';
			echo '	<p>' . wp_kses_post( $notice['message'] ) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * Prints the available statuses so the user can click to filter.
	 */
	protected function display_filter_by_status() {

		$status_list_items = array();
		$request_status    = $this->get_request_status();

		// Helper to set 'all' filter when not set on status counts passed in
		if ( ! isset( $this->status_counts['all'] ) ) {
			$this->status_counts = array( 'all' => array_sum( $this->status_counts ) ) + $this->status_counts;
		}

		foreach ( $this->status_counts as $status_name => $count ) {

			if ( 0 === $count ) {
				continue;
			}

			if ( $status_name === $request_status || ( empty( $request_status ) && 'all' === $status_name ) ) {
				$status_list_item = '<li class="%1$s"><strong>%3$s</strong> (%4$d)</li>';
			} else {
				$status_list_item = '<li class="%1$s"><a href="%2$s">%3$s</a> (%4$d)</li>';
			}

			$status_filter_url   = ( 'all' === $status_name ) ? remove_query_arg( 'status' ) : add_query_arg( 'status', $status_name );
			$status_filter_url   = remove_query_arg( array( 'paged', 's' ), $status_filter_url );
			$status_list_items[] = sprintf( $status_list_item, esc_attr( $status_name ), esc_url( $status_filter_url ), esc_html( ucfirst( $status_name ) ), absint( $count ) );
		}

		if ( $status_list_items ) {
			echo '<ul class="subsubsub">';
			echo implode( " | \n", $status_list_items );
			echo '</ul>';
		}
	}

	/**
	 * Renders the table list, we override the original class to render the table inside a form
	 * and to render any needed HTML (like the search box). By doing so the callee of a function can simple
	 * forget about any extra HTML.
	 */
	protected function display_table() {
		echo '<form id="' . esc_attr( $this->_args['plural'] ) . '-filter" method="get">';
		foreach ( $_GET as $key => $value ) {
			if ( '_' === $key[0] || 'paged' === $key ) {
				continue;
			}
			echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		}
		if ( ! empty( $this->search_by ) ) {
			echo $this->search_box( $this->get_search_box_button_text(), 'plugin' ); // WPCS: XSS OK
		}
		parent::display();
		echo '</form>';
	}

	/**
	 * Process any pending actions.
	 */
	public function process_actions() {
		// TODO :: process bulk action.
		$this->process_row_actions();

		if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
			// _wp_http_referer is used only on bulk actions, we remove it to keep the $_GET shorter
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
			exit;
		}
	}

	/**
	 * Render the list table page, including header, notices, status filters and table.
	 */
	public function display_page_todo() {
		$this->prepare_items();

		echo '<div class="wrap">';
		$this->display_header();
		$this->display_admin_notices();
		$this->display_filter_by_status();
		$this->display_table();
		echo '</div>';
	}

	/**
	 * Get the text to display in the search box on the list table.
	 */
	protected function get_search_box_placeholder() {
		return esc_html__( 'Search', 'user-registration' );
	}
}
