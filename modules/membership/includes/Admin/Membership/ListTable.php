<?php
/**
 * User Registration Membership Table List
 *
 * @version 1.0.0
 */

namespace WPEverest\URMembership\Admin\Membership;

use UR_Base_Layout;
use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\TableList;

if ( ! class_exists( 'UR_List_Table' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/abstracts/abstract-ur-list-table.php';
}
if ( ! class_exists( 'UR_Base_Layout' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/admin/class-ur-admin-base-layout.php';
}

/**
 * Membership table list class.
 */
class ListTable extends \UR_List_Table {

	/**
	 * Initialize the Membership table list.
	 */
	public function __construct() {

		$this->post_type       = 'ur_membership';
		$this->page            = 'user-registration-membership';
		$this->per_page_option = 'user_registration_membership_per_page';
		$this->addnew_action   = 'add_new_membership';
		$this->sort_by         = array(
			'title' => array( 'title', false ),
		);

		parent::__construct(
			array(
				'singular' => 'membership',
				'plural'   => 'memberships',
				'ajax'     => false,
			)
		);
	}

	/**
	 * No items found text.
	 */
	public function no_items() {
		UR_Base_Layout::no_items( 'Memberships' );
	}

	/**
	 * Get list columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'draggable'        => __( '', 'user-registration' ),
			'title'            => __( 'Name', 'user-registration' ),
			'membership_price' => __( 'Price', 'user-registration' ),
			'membership_type'  => __( 'Plan', 'user-registration' ),
			'status'           => __( 'Status', 'user-registration' ),
			'members'          => __( 'Members', 'user-registration' ),
		);
	}

	/**
	 * Post Edit Link.
	 *
	 * @param object $row
	 *
	 * @return string
	 */
	public function get_edit_links( $row ) {
		return admin_url( 'admin.php?post_id=' . $row->ID . '&action=' . $this->addnew_action . '&page=' . $this->page );
	}

	public function get_delete_links( $row ) {

		return admin_url( 'admin.php?membership=' . $row->ID . '&action=delete&page=' . $this->page );
	}

	/**
	 * Post Duplicate Link.
	 *
	 * @param object $row
	 *
	 * @return string
	 */
	public function get_duplicate_link( $row ) {
		return admin_url( 'post.php?post=' . $row->ID . '&action=edit' );
	}

	/**
	 * @param $membership
	 *
	 * @return array
	 */
	public function get_row_actions( $membership ) {
		$actions = array();

		$actions['id'] = "ID: $membership->ID";

		// Add Edit action
		$actions['edit'] = sprintf(
			'<a href="%s" class="ur-row-actions">%s</a>',
			esc_url( $this->get_edit_links( $membership ) ),
			__( 'Edit', 'user-registration' )
		);

		// Add Delete action
		$actions['delete'] = sprintf(
			'<a href="%s" class="delete-membership ur-row-actions" data-membership-id="' . esc_attr( $membership->ID ) . '" aria-label="' . esc_attr__( 'Delete this item', 'user-registration' ) . '">%s</a>',
			esc_url( wp_nonce_url( $this->get_delete_links( $membership ), 'urm_delete_nonce' ) ),
			__( 'Delete', 'user-registration' )
		);

		return $actions;
	}

	public function column_draggable() {
		return '<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
			<path d="M7 12c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222C7.985 14.222 7 13.227 7 12Zm0-7.778C7 2.995 7.985 2 9.2 2c1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222A2.21 2.21 0 0 1 7 4.222Zm0 15.556c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.994 2.2 2.222A2.211 2.211 0 0 1 9.2 22C7.985 22 7 21.005 7 19.778ZM13.6 12c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222c-1.215 0-2.2-.995-2.2-2.222Zm0-7.778C13.6 2.995 14.585 2 15.8 2c1.215 0 2.2.995 2.2 2.222a2.211 2.211 0 0 1-2.2 2.222 2.21 2.21 0 0 1-2.2-2.222Zm0 15.556c0-1.227.985-2.222 2.2-2.222 1.215 0 2.2.994 2.2 2.222A2.211 2.211 0 0 1 15.8 22c-1.215 0-2.2-.995-2.2-2.222Z"/>
		</svg>';
	}

	/**
	 * @param $membership
	 *
	 * @return string
	 */
	public function column_membership_price( $membership ) {
		$membership_repository      = new MembershipRepository();
		$membership                 = $membership_repository->get_single_membership_by_ID( $membership->ID );
		$membership['post_content'] = json_decode( $membership['post_content'], true );
		$membership['meta_value']   = json_decode( $membership['meta_value'], true );
		$membership                 = apply_filters( 'build_membership_list_frontend', array( (array) $membership ) );
		$price                      = 0;
		if ( ! empty( $membership ) ) {
			$price = $membership[0]['period'];
		}

		return $price;
	}

	/**
	 * @param $membership
	 *
	 * @return string
	 */
	public function column_status( $membership ) {
		$membership_content = json_decode( $membership->post_content, true );
		$enabled            = $membership_content['status'] == 'true';
		$actions            = '<div class="ur-status-toggle ur-d-flex ur-align-items-center visible" style="gap: 5px">';
		$actions           .= '<div class="ur-toggle-section">';
		$actions           .= '<span class="user-registration-toggle-form">';
		$actions           .= '<input
						id="ur-membership-change-status"
						class="ur-membership-change-status user-registration-switch__control hide-show-check enabled"
						type="checkbox"
						value="1"
						' . esc_attr( checked( true, ur_string_to_bool( $enabled ), false ) ) . '
						data-ur-membership-id="' . esc_attr( $membership->ID ) . '">';
		$actions           .= '<span class="slider round"></span>';
		$actions           .= '</span>';
		$actions           .= '</div>';
		$actions           .= '</div>';

		return $actions;
	}

	/**
	 * @param $membership
	 *
	 * @return string
	 */
	public function column_membership_type( $membership ) {
		$data         = json_decode( wp_unslash( $membership->post_content ), true );
		$status_class = ( 'free' == $data['type'] ? 'user-registration-badge user-registration-badge--success-subtle' : ( 'paid' == $data['type'] ? 'user-registration-badge user-registration-badge--secondary-subtle' : 'user-registration-badge user-registration-badge--danger-subtle' ) );

		return sprintf( '<span class="%s">%s</span>', $status_class, esc_html( $data['type'] ) );
	}

	/**
	 * @param $membership
	 *
	 * @return string
	 */
	public function column_members( $membership ) {
		global $wpdb;
		$subscription_table = TableList::subscriptions_table();

		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT user_id) total from $subscription_table
        		WHERE item_id = %d",
				$membership->ID
			),
			ARRAY_A
		);

		return sprintf( '<a target="_blank" href="%s"> %d </a>', admin_url( "admin.php?page=user-registration-users&membership_id=$membership->ID" ), $result[0]['total'] );
	}

	/**
	 * Render the list table page, including header, notices, status filters and table.
	 */
	public function display_page() {
		UR_Base_Layout::render_layout(
			$this,
			array(
				'page'           => $this->page,
				'title'          => esc_html__( 'Memberships', 'user-registration' ),
				'add_new_action' => 'add_new_membership',
				'search_id'      => 'membership-list-search-input',
				'skip_query_key' => 'add-new-membership',
				'form_id'        => 'membership-list',
			)
		);
	}

	/**
	 * Display Search Box
	 *
	 * @param $search_id
	 *
	 * @return void
	 */
	public function display_search_box( $search_id ) {
		?>
			<input type="hidden" name="page" value="user-registration-membership">
			<p class="search-box">
			</p>
			<div id="user-registration-list-search-form">
				<?php
				$placeholder = __( 'Search Membership', 'user-registration' );
				UR_Base_Layout::display_search_field( $search_id, $placeholder );
				?>
			</div>
			<p></p>
		<?php
	}

	/**
	 * @return array
	 * @global string $comment_status
	 */
	protected function get_bulk_actions() {
		$actions = array(// 'delete' => __( 'Delete permanently' )
		);

		return $actions;
	}

	/**
	 * Override prepare_items to use saved membership order.
	 *
	 * @return void
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

		// Check for saved membership order (only if not searching and no status filter)
		$saved_order = get_option( 'ur_membership_order', array() );
		if ( ! empty( $saved_order ) && empty( $_REQUEST['s'] ) && empty( $_REQUEST['status'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			// Get all membership IDs to find any new ones not in saved order
			$all_memberships_query = new \WP_Query(
				array(
					'post_type'      => $this->post_type,
					'posts_per_page' => - 1,
					'fields'         => 'ids',
					'post_status'    => ! empty( $args['post_status'] ) ? $args['post_status'] : 'any',
				)
			);
			$all_membership_ids    = $all_memberships_query->posts;

			// Merge saved order with any new memberships not in the saved order
			$new_memberships = array_diff( $all_membership_ids, $saved_order );
			$final_order     = array_merge( $saved_order, $new_memberships );

			// Use saved order with new memberships appended
			$args['post__in'] = $final_order;
			$args['orderby']  = 'post__in';
			$args['order']    = 'ASC';
		} else {
			// Use default ordering
			$args['orderby'] = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'date_created'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$args['order']   = isset( $_REQUEST['order'] ) && 'ASC' === strtoupper( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) ? 'ASC' : 'DESC'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		// Get the memberships.
		$query_posts = new \WP_Query( $args );
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
	 * Override single_row to add data-membership-id attribute.
	 *
	 * @param object $item The current item.
	 */
	public function single_row( $item ) {
		echo '<tr id="membership-' . esc_attr( $item->ID ) . '" data-membership-id="' . esc_attr( $item->ID ) . '" class="ur-membership-sortable-row">';
		$this->single_row_columns( $item );
		echo '</tr>';
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
