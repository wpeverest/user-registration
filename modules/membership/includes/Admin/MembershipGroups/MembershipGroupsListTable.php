<?php
/**
 * User Registration Membership Table List
 *
 * @version 1.0.0
 */

namespace WPEverest\URMembership\Admin\MembershipGroups;

use UR_Base_Layout;
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
		UR_Base_Layout::no_items('Membership Groups');
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

		$url  = 'admin.php?membership_group_id=' . $row->ID . '&action=delete&page=' . $this->page;
		$url .= ( '' != $is_form_related ) ? '&form=' . get_the_title( $is_form_related ) : '';

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
	 * @param $membership_group
	 *
	 * @return array
	 */
	public function get_row_actions( $membership_group ) {
		$actions = array();

		$actions['id'] = "ID: $membership_group->ID";

		// Add Edit action
		$actions['edit'] = sprintf(
			'<a href="%s" class="ur-row-actions">%s</a>',
			esc_url( $this->get_edit_links( $membership_group ) ),
			__( 'Edit', 'user-registration' )
		);

		// Add Delete action
		$actions['delete'] = sprintf(
			'<a href="%s" class="delete-membership-groups ur-row-actions" data-membership-group-id="' . esc_attr( $membership_group->ID ) . '" aria-label="' . esc_attr__( 'Delete this item', 'user-registration' ) . '">%s</a>',
			esc_url( wp_nonce_url( $this->get_delete_links( $membership_group ), 'urm_delete_nonce' ) ),
			__( 'Delete', 'user-registration' )
		);

		return $actions;
	}

	/**
	 * @param $membership
	 *
	 * @return string
	 */
	public function column_status( $membership_group ) {
		$membership_content = json_decode( $membership_group->post_content, true );
		$enabled            = $membership_content['status'] == 'true';
		$actions            = '<div class="ur-status-toggle ur-d-flex ur-align-items-center visible" style="gap: 5px">';
		$actions           .= '<div class="ur-toggle-section">';
		$actions           .= '<span class="user-registration-toggle-form">';
		$actions           .= '<input
						class="ur-membership-change-status user-registration-switch__control hide-show-check enabled"
						type="checkbox"
						value="1"
						' . esc_attr( checked( true, ur_string_to_bool( $enabled ), false ) ) . '
						data-ur-membership-id="' . esc_attr( $membership_group->ID ) . '">';
		$actions           .= '<span class="slider round"></span>';
		$actions           .= '</span>';
		$actions           .= '</div>';
		$actions           .= '</div>';

		return $actions;
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
	 * Render the list table page, including header, notices, status filters and table.
	 */
	public function display_page() {
		UR_Base_Layout::render_layout(
			$this,
			array(
				'page'           => $this->page,
				'title'          => esc_html__( 'All Membership Groups', 'user-registration' ),
				'add_new_action' => 'add_groups',
				'search_id'      => 'membership-groups-list-search-input',
				'skip_query_key' => 'add-new-membership',
				'form_id'        => 'membership-group-list',
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
			<input type="hidden" name="action" value="list_groups">
			<p class="search-box">
			</p>
			<div id="user-registration-list-search-form">
				<?php
				$placeholder = __( 'Search Membership Groups', 'user-registration' );
				UR_Base_Layout::display_search_field( $search_id, $placeholder );
				?>
			</div>
			<p></p>
		<?php
	}

	/**
	 * @return array
	 * @global string $comment_status
	 *
	 */
	protected function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete permanently' ),
		);

		return $actions;
	}

	/**
	 * Displays the bulk actions dropdown.
	 *
	 * @since 3.1.0
	 *
	 * @param string $which The location of the bulk actions: 'top' or 'bottom'.
	 *                      This is designated as optional for backward compatibility.
	 */
	protected function bulk_actions( $which = '' ) {
		if ( is_null( $this->_actions ) ) {
			$this->_actions = $this->get_bulk_actions();

			/**
			 * Filters the items in the bulk actions menu of the list table.
			 *
			 * The dynamic portion of the hook name, `$this->screen->id`, refers
			 * to the ID of the current screen.
			 *
			 * @since 3.1.0
			 * @since 5.6.0 A bulk action can now contain an array of options in order to create an optgroup.
			 *
			 * @param array $actions An array of the available bulk actions.
			 */
			$this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

			$two = '';
		} else {
			$two = '2';
		}

		if ( empty( $this->_actions ) ) {
			return;
		}

		echo '<label for="bulk-action-selector-' . esc_attr( $which ) . '" class="screen-reader-text">' . __( 'Select bulk action' ) . '</label>';
		echo '<select name="bulk_action' . $two . '" id="bulk-action-selector-' . esc_attr( $which ) . "\">\n";
		echo '<option value="-1">' . __( 'Bulk actions' ) . "</option>\n";

		foreach ( $this->_actions as $key => $value ) {
			if ( is_array( $value ) ) {
				echo "\t" . '<optgroup label="' . esc_attr( $key ) . '">' . "\n";

				foreach ( $value as $name => $title ) {
					$class = ( 'edit' === $name ) ? ' class="hide-if-no-js"' : '';

					echo "\t\t" . '<option value="' . esc_attr( $name ) . '"' . $class . '>' . $title . "</option>\n";
				}
				echo "\t" . "</optgroup>\n";
			} else {
				$class = ( 'edit' === $key ) ? ' class="hide-if-no-js"' : '';

				echo "\t" . '<option value="' . esc_attr( $key ) . '"' . $class . '>' . $value . "</option>\n";
			}
		}

		echo "</select>\n";

		submit_button( __( 'Apply' ), 'action', '', false, array( 'id' => "doaction$two" ) );
		echo "\n";
	}
}
