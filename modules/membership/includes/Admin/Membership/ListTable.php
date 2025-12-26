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
		$image_url = esc_url( plugin_dir_url( UR_PLUGIN_FILE ) . 'assets/images/empty-table.png' );
		?>
		<div class="empty-list-table-container">
			<img src="<?php echo $image_url; ?>" alt="">
			<h3><?php echo _e( 'You don\'t have any Memberships yet.', 'user-registration' ); ?></h3>
			<p><?php echo __( 'Please add memberships and you are good to go.', 'user-registration' ); ?></p>
		</div>
		<?php
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

		$actions['id'] = '<span>ID: ' . $membership->ID . '</span>';

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
		$actions            .= '<div class="ur-toggle-section">';
		$actions            .= '<span class="user-registration-toggle-form">';
		$actions            .= '<input
						id="ur-membership-change-status"
						class="ur-membership-change-status user-registration-switch__control hide-show-check enabled"
						type="checkbox"
						value="1"
						' . esc_attr( checked( true, ur_string_to_bool( $enabled ), false ) ) . '
						data-ur-membership-id="' . esc_attr( $membership->ID ) . '">';
		$actions            .= '<span class="slider round"></span>';
		$actions            .= '</span>';
		$actions            .= '</div>';
		$actions            .= '</div>';

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

		return sprintf( '<a target="_blank" href="%s"> %d </a>', admin_url( "admin.php?page=user-registration-members&membership_id=$membership->ID" ), $result[0]['total'] );

	}

	/**
	 * Render the list table page, including header, notices, status filters and table.
	 */
	public function display_page() {
		UR_Base_Layout::render_layout( $this, array(
			'page'           => $this->page,
			'title'          => esc_html__( 'All Membership', 'user-registration' ),
			'add_new_action' => 'add_new_membership',
			'search_id'      => 'membership-list-search-input',
			'skip_query_key' => 'add-new-membership',
			'form_id'        => 'membership-list',
		) );
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
		<form method="get" id="user-registration-list-search-form">
			<input type="hidden" name="page" value="user-registration-membership">
			<p class="search-box">
			</p>
			<div>
				<?php
				$placeholder = __( 'Search Membership', 'user-registration' );
				UR_Base_Layout::display_search_field( $search_id, $placeholder );
				?>
			</div>
			<p></p>
		</form>
		<?php

	}

	/**
	 * @return array
	 * @global string $comment_status
	 *
	 */
	protected function get_bulk_actions() {
		$actions = array(//			'delete' => __( 'Delete permanently' )
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
}
