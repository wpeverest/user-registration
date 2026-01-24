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

if ( ! class_exists( 'UR_Base_Layout' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/admin/class-ur-admin-base-layout.php';
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
		UR_Base_Layout::no_items( 'Registration Forms' );
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
		$class = '';
		$attr  = '';

		if ( ! ur_check_module_activation( 'multiple-registration' ) ) {
			$class = ' ur-activate-dependent-module ui-add-disabled';
			$attr  = 'data-slug="user-registration-multiple-registration"
					data-name="User Registration - Multiple Registration"
					data-plan="free"
					aria-disabled="true"';
		}
		?>
		<hr class="wp-header-end">
		<?php
		echo user_registration_plugin_main_header();
		UR_Base_Layout::render_layout(
			$this,
			array(
				'page'           => $this->page,
				'title'          => esc_html__( 'Registration Forms', 'user-registration' ),
				'add_new_action' => '',
				'search_id'      => 'user-registration-list-table-search-input',
				'form_id'        => 'registration-list',
				'add_page_key'   => 'add-new-registration',
				'add_new_class'  => $class,
				'add_new_attr'   => $attr,
			)
		);
	}

	/**
	 * Displays the search box.
	 *
	 * @param string $text search button Text.
	 * @param string $input_id Input field id.
	 */
	public function display_search_box( $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) { // phpcs:ignore;
			return;
		}

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
				<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html__( 'Search Registration Form', 'user-registration' ); ?>:</label>
				<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" placeholder="<?php esc_html_e( 'Search Registration Form', 'user-registration' ); ?>" />
				<button type="submit" id="search-submit">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
						<path fill="#000" fill-rule="evenodd" d="M4 11a7 7 0 1 1 12.042 4.856 1.012 1.012 0 0 0-.186.186A7 7 0 0 1 4 11Zm12.618 7.032a9 9 0 1 1 1.414-1.414l3.675 3.675a1 1 0 0 1-1.414 1.414l-3.675-3.675Z" clip-rule="evenodd"/>
					</svg>
				</button>
		</div>
			<?php
	}

	/**
	 * Displays the table.
	 *
	 * @since 3.1.0
	 */
	public function display() {
		$singular = $this->_args['singular'];

		$this->display_tablenav( 'top' );

		$this->screen->render_screen_reader_content( 'heading_list' );
		?>
		<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
				<?php $this->print_table_description(); ?>
			<thead>
			<tr>
				<?php $this->print_column_headers(); ?>
			</tr>
			</thead>

			<tbody id="the-list"
				<?php
				if ( $singular ) {
					echo " data-wp-lists='list:$singular'";
				}
				?>
				>
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>

		</table>
		<?php
		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Generates the table navigation above or below the table
	 *
	 * @since 4.1
	 *
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php if ( $this->has_items() && 'top' === $which ) : ?>
				<div>
					<div class="alignleft actions bulkactions">
						<?php $this->bulk_actions( $which ); ?>
					</div>
					<?php $this->extra_tablenav( $which ); ?>
				</div>
				<?php
			endif;
			if ( 'bottom' === $which ) :
				?>
				<div class="alignleft">
					<?php $this->footer_text(); ?>
				</div>
				<?php
				$this->pagination( $which );
			endif;
			?>
		</div>
		<?php
	}

	/**
	 * Displays the pagination.
	 *
	 * @since 3.1.0
	 *
	 * @param string $which The location of the pagination: Either 'top' or 'bottom'.
	 */
	protected function pagination( $which ) {
		if ( empty( $this->_pagination_args['total_items'] ) ) {
			return;
		}

		$total_items     = $this->_pagination_args['total_items'];
		$total_pages     = $this->_pagination_args['total_pages'];
		$infinite_scroll = false;
		if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
			$infinite_scroll = $this->_pagination_args['infinite_scroll'];
		}

		if ( 'top' === $which && $total_pages > 1 ) {
			$this->screen->render_screen_reader_content( 'heading_pagination' );
		}

		$current              = $this->get_pagenum();
		$removable_query_args = wp_removable_query_args();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

		$current_url = remove_query_arg( $removable_query_args, $current_url );

		$page_links = array();

		$total_pages_before = '<span class="paging-input">';
		$total_pages_after  = '</span></span>';

		$disable_first = false;
		$disable_last  = false;
		$disable_prev  = false;
		$disable_next  = false;

		if ( 1 === $current ) {
			$disable_first = true;
			$disable_prev  = true;
		}
		if ( $total_pages === $current ) {
			$disable_last = true;
			$disable_next = true;
		}

		if ( $disable_first ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='first-page button' href='%s'>" .
					"<span class='screen-reader-text'>%s</span>" .
					"<span aria-hidden='true'>%s</span>" .
				'</a>',
				esc_url( remove_query_arg( 'paged', $current_url ) ),
				/* translators: Hidden accessibility text. */
				__( 'First page' ),
				'&laquo;'
			);
		}

		if ( $disable_prev ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='prev-page button' href='%s'>" .
					"<span class='screen-reader-text'>%s</span>" .
					"<span aria-hidden='true'>%s</span>" .
				'</a>',
				esc_url( add_query_arg( 'paged', max( 1, $current - 1 ), $current_url ) ),
				/* translators: Hidden accessibility text. */
				__( 'Previous page' ),
				'&lsaquo;'
			);
		}

		if ( 'bottom' === $which ) {
			$html_current_page  = $current;
			$total_pages_before = sprintf(
				'<span class="screen-reader-text">%s</span>' .
				'<span id="table-paging" class="paging-input">' .
				'<span class="tablenav-paging-text">',
				/* translators: Hidden accessibility text. */
				__( 'Current Page' )
			);
		} else {
			$html_current_page = sprintf(
				'<label for="current-page-selector" class="screen-reader-text">%s</label>' .
				"<input class='current-page' id='current-page-selector' type='text'
					name='paged' value='%s' size='%d' aria-describedby='table-paging' />" .
				"<span class='tablenav-paging-text'>",
				/* translators: Hidden accessibility text. */
				__( 'Current Page' ),
				$current,
				strlen( $total_pages )
			);
		}

		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );

		$page_links[] = $total_pages_before . sprintf(
			/* translators: 1: Current page, 2: Total pages. */
			_x( '%1$s of %2$s', 'paging' ),
			$html_current_page,
			$html_total_pages
		) . $total_pages_after;

		if ( $disable_next ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='next-page button' href='%s'>" .
					"<span class='screen-reader-text'>%s</span>" .
					"<span aria-hidden='true'>%s</span>" .
				'</a>',
				esc_url( add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ),
				/* translators: Hidden accessibility text. */
				__( 'Next page' ),
				'&rsaquo;'
			);
		}

		if ( $disable_last ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='last-page button' href='%s'>" .
					"<span class='screen-reader-text'>%s</span>" .
					"<span aria-hidden='true'>%s</span>" .
				'</a>',
				esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
				/* translators: Hidden accessibility text. */
				__( 'Last page' ),
				'&raquo;'
			);
		}

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) ) {
			$pagination_links_class .= ' hide-if-js';
		}
		$output = "\n<span class='$pagination_links_class'>" . implode( "\n", $page_links ) . '</span>';

		if ( $total_pages ) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		} else {
			$page_class = ' no-pages';
		}
		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $this->_pagination;
	}
}
