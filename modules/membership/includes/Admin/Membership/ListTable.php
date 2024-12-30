<?php
/**
 * User Registration Membership Table List
 *
 * @version 1.0.0
 */

namespace WPEverest\URMembership\Admin\Membership;

use WPEverest\URMembership\TableList;

if ( ! class_exists( 'UR_List_Table' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/abstracts/abstract-ur-list-table.php';
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
			'cb'              => '<input type="checkbox" />',
			'title'           => __( 'Membership Name', 'user-registration' ),
			'membership_type' => __( 'Membership Plan Type', 'user-registration' ),
			'members'         => __( 'Members', 'user-registration' ),
			'status'          => __( 'Status', 'user-registration' ),
			'action'          => __( 'Action', 'user-registration' ),
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

		return array();
	}

	/**
	 * @param $membership
	 *
	 * @return string
	 */
	public function column_status( $membership ) {
		$membership_content = json_decode( $membership->post_content, true );
		$enabled            = $membership_content['status'] == 'true';
		$status_class       = $enabled ? 'user-registration-badge user-registration-badge--success-subtle' : 'user-registration-badge user-registration-badge--secondary-subtle';
		$status_label       = $enabled ? esc_html__( 'Active', 'user-registration-content-restriction' ) : esc_html__( 'Inactive', 'user-registration-content-restriction' );

		return sprintf( '<span id="ur-membership-list-status-' . $membership->ID . '" class="%s">%s</span>', $status_class, $status_label );
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

		return $result[0]['total'];
	}

	/**
	 * @param $membership
	 *
	 * @return string
	 */
	public function column_action( $membership ) {

		$edit_link          = $this->get_edit_links( $membership );
		$delete_link = $this->get_delete_links($membership);
		$membership_content = json_decode( $membership->post_content, true );
		$checked            = ( $membership_content['status'] == 'true' ) ? 'checked' : '';
		$actions            = '
				<div class="row-actions ur-d-flex ur-align-items-center visible" style="gap: 5px">

					<div class="user-registration-switch">
						<input
						 		type="checkbox"
						 		' . $checked . '
							   	class="ur-membership-change-status user-registration-switch__control hide-show-check enabled"
							   	data-ur-membership-id = ' . $membership->ID . '
						>
					</div>
					&nbsp | &nbsp
					<span class="edit">
						<a href="' . esc_url( $edit_link ) . '">' . __( 'Edit', 'user-registration' ) . '</a>
					</span>
					&nbsp | &nbsp
					<span class="delete">
						<a class="delete-membership" aria-label="' . esc_attr__( 'Delete this item', 'user-registration' ) . '" href="' . $delete_link . '">' . esc_html__( 'Delete', 'user-registration' ) . '</a>
					</span>
					</div>

					';

		return $actions;
	}

	/**
	 * Render the list table page, including header, notices, status filters and table.
	 */
	public function display_page() {
		$this->prepare_items();
		if ( ! isset( $_GET['add-new-membership'] ) ) { // phpcs:ignore Standard.Category.SniffName.ErrorCode: input var okay, CSRF ok.
			?>
			<div id="user-registration-list-table-page">
				<div class="user-registration-list-table-heading">
					<h1>
						<?php esc_html_e( 'All Membership', 'user-registration' ); ?>
					</h1>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->page . '&action=add_new_membership' ) ); ?>"
					   class="button ur-button-primary">
						+
						<?php
						echo __( 'Create new Membership', 'user-registration' )
						?>
					</a>
				</div>
				<div id="user-registration-list-filters-row">

					<?php
					$this->display_search_box( 'membership-list-search-input' );
					?>
				</div>
				<hr>
				<form id="membership-list" method="get">
					<input type="hidden" name="page" value="<?php echo $this->page; ?>"/>
					<?php
					$this->screen->render_screen_reader_content( 'heading_list' );

					$this->display();
					?>
				</form>
			</div>
			<?php
		}
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
				<input type="search" id="<?php echo $search_id; ?>" name="s" value="<?php echo ($_GET['s']) ?? ''; ?>" placeholder="Search Membership ..."
					   autocomplete="off">
				<button type="submit" id="search-submit">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
						<path fill="#000" fill-rule="evenodd"
							  d="M4 11a7 7 0 1 1 12.042 4.856 1.012 1.012 0 0 0-.186.186A7 7 0 0 1 4 11Zm12.618 7.032a9 9 0 1 1 1.414-1.414l3.675 3.675a1 1 0 0 1-1.414 1.414l-3.675-3.675Z"
							  clip-rule="evenodd"></path>
					</svg>
				</button>
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
		$actions = array(
			'delete' => __( 'Delete permanently' )
		);

		return $actions;
	}
}
