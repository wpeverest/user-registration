<?php
/**
 * User Registration Membership Table List
 *
 * @version 1.0.0
 */

namespace WPEverest\URMembership\Admin\MembershipGroups;

use WPEverest\URMembership\Admin\Services\MembershipGroupService;
use WPEverest\URMembership\TableList;

if ( ! class_exists( 'UR_List_Table' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/abstracts/abstract-ur-list-table.php';
}

/**
 * Membership table list class.
 */
class MembershipGroupsListTable extends \UR_List_Table {

	/**
	 * Initialize the Membership table list.
	 */
	public function __construct() {

		$this->post_type       = 'ur_membership_groups';
		$this->page            = 'user-registration-membership';
		$this->per_page_option = 'user_registration_membership_per_page';
		$this->addnew_action   = 'add_groups';
		$this->sort_by         = array(
			'title' => array( 'title', false ),
		);

		parent::__construct(
			array(
				'singular' => 'membership_group',
				'plural'   => 'membership_groups',
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
			<h3><?php echo _e( 'You don\'t have any Membership Groups yet.', 'user-registration' ); ?></h3>
			<p><?php echo __( 'Please add a membership group and you are good to go.', 'user-registration' ); ?></p>
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
			'cb'        => '<input type="checkbox" />',
			'title'     => __( 'Group Name', 'user-registration' ),
			'shortcode' => __( 'Shortcode', 'user-registration' ),
			'status'    => __( 'Status', 'user-registration' ),
			'action'    => __( 'Action', 'user-registration' ),
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
		$membership_group_service = new MembershipGroupService();
		$is_form_related          = $membership_group_service->get_group_form_id( $row->ID );

		$url = 'admin.php?membership_group_id=' . $row->ID . '&action=delete&page=' . $this->page;
		$url .= ( "" != $is_form_related ) ? "&form=" . get_the_title( $is_form_related ) : '';

		return admin_url( $url );
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
	public function column_status( $membership_group ) {
		$membership_content = json_decode( $membership_group->post_content, true );
		$enabled            = $membership_content['status'] == 'true';
		$status_class       = $enabled ? 'user-registration-badge user-registration-badge--success-subtle' : 'user-registration-badge user-registration-badge--secondary-subtle';
		$status_label       = $enabled ? esc_html__( 'Active', 'user-registration-content-restriction' ) : esc_html__( 'Inactive', 'user-registration-content-restriction' );

		return sprintf( '<span id="ur-membership-list-status-' . $membership_group->ID . '" class="%s" style="vertical-align: middle">%s</span>', $status_class, $status_label );
	}

	public function column_shortcode( $membership_group ) {

		$shortcode = '[user_registration_groups  id="' . $membership_group->ID . '"]';
		return "
				<div class='urm-shortcode'>
					<input type='text' onfocus='this.select();' readonly='readonly' value='$shortcode' class='widefat code'>
					<button id='copy-shortcode-" . $membership_group->ID . "' class='button ur-copy-shortcode tooltipstered' href='#' data-tip='Copy Shortcode ! ' data-copied='Copied ! '>
						<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24'>
											<path fill='#383838' fill-rule='evenodd' d='M3.116 3.116A1.25 1.25 0 0 1 4 2.75h9A1.25 1.25 0 0 1 14.25 4v1a.75.75 0 0 0 1.5 0V4A2.75 2.75 0 0 0 13 1.25H4A2.75 2.75 0 0 0 1.25 4v9A2.75 2.75 0 0 0 4 15.75h1a.75.75 0 0 0 0-1.5H4A1.25 1.25 0 0 1 2.75 13V4c0-.332.132-.65.366-.884ZM9.75 11c0-.69.56-1.25 1.25-1.25h9c.69 0 1.25.56 1.25 1.25v9c0 .69-.56 1.25-1.25 1.25h-9c-.69 0-1.25-.56-1.25-1.25v-9ZM11 8.25A2.75 2.75 0 0 0 8.25 11v9A2.75 2.75 0 0 0 11 22.75h9A2.75 2.75 0 0 0 22.75 20v-9A2.75 2.75 0 0 0 20 8.25h-9Z' clip-rule='evenodd'></path>
									</svg>
					</button>
				</div>
			";
	}

	/**
	 * @param $membership
	 *
	 * @return string
	 */
	public function column_action( $membership_group ) {

		$edit_link          = $this->get_edit_links( $membership_group );
		$delete_link        = $this->get_delete_links( $membership_group );
		$membership_content = json_decode( $membership_group->post_content, true );
		$checked            = ( $membership_content['status'] == 'true' ) ? 'checked' : '';
		$actions            = '
				<div class="row-actions ur-d-flex ur-align-items-center visible" style="gap: 5px">

					<div class="user-registration-switch">
						<input
						 		type="checkbox"
						 		' . $checked . '
							   	class="ur-membership-change-status user-registration-switch__control hide-show-check enabled"
							   	data-ur-membership-id = ' . $membership_group->ID . '
						>
					</div>
					&nbsp | &nbsp
					<span class="edit">
						<a href="' . esc_url( $edit_link ) . '">' . __( 'Edit', 'user-registration' ) . '</a>
					</span>
					&nbsp | &nbsp
					<span class="delete">
						<a class="delete-membership-groups" aria-label="' . esc_attr__( 'Delete this item', 'user-registration' ) . '" href="' . $delete_link . '">' . esc_html__( 'Delete', 'user-registration' ) . '</a>
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
						<?php esc_html_e( 'All Membership Groups', 'user-registration' ); ?>
					</h1>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->page . '&action=add_groups' ) ); ?>"
					   class="button ur-button-primary">
						+
						<?php
						echo __( 'Create new Membership Groups', 'user-registration' )
						?>
					</a>
				</div>
				<div id="user-registration-list-filters-row">

					<?php
					$this->display_search_box( 'membership-groups-list-search-input' );
					?>
				</div>
				<hr>
				<form id="membership-group-list" method="get">
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
			<input type="hidden" name="action" value="list_groups">
			<p class="search-box">
			</p>
			<div>
				<input type="search" id="<?php echo $search_id; ?>" name="s" value="<?php echo esc_attr( $_GET['s'] ?? '' ); ?>"
					   placeholder="<?php echo esc_attr( 'Search Membership Groups',' user-registration' ); ?> ..."
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
