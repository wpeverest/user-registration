<?php
/**
 * User Registration Content Restriction - Content Access Rules Table List
 *
 * @version 1.0.0
 *
 * @package UserRegistrationContentRestriction\Admin
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'UR_List_Table' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/abstracts/abstract-ur-list-table.php';
}
/**
 * Content access rules table list class.
 *
 * @since 2.0.0
 */
class URCR_Admin_Content_Access_Rules_Table_List extends UR_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->post_type       = 'urcr_access_rule';
		$this->page            = 'user-registration-content-restriction';
		$this->per_page_option = 'urcr_access_rules_per_page';
		$this->addnew_action   = 'add_new_urcr_content_access_rule';
		parent::__construct(
			array(
				'singular' => 'content-access-rule',
				'plural'   => 'content-access-rules',
				'ajax'     => false,
			)
		);
	}

	/**
	 * No items found text.
	 */
	public function no_items() {
		esc_html_e( 'No content access rule found.', 'user-registration' );
	}

	/**
	 * Define columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'             => '<input type="checkbox" />',
			'title'          => esc_html__( 'Title', 'user-registration' ),
			'status'         => esc_html__( 'Status', 'user-registration' ),
			'access_control' => esc_html__( 'Access Control', 'user-registration' ),
			'action'         => esc_html__( 'Action', 'user-registration' ),
			'author'         => esc_html__( 'Author', 'user-registration' ),
			'date'           => esc_html__( 'Last Update', 'user-registration' ),
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
		return admin_url( 'admin.php?page=' . $this->page . '&action=' . $this->addnew_action . '&post-id=' . $row->ID );
	}

	/**
	 * Post Duplicate Link.
	 *
	 * @param  object $row
	 *
	 * @return string
	 */
	public function get_duplicate_link( $row_id ) {
		return admin_url( 'admin.php?page=' . $this->page . '&action=' . $this->addnew_action . '&post-id=' . $row_id );
	}


	/**
	 * Column: Actions.
	 *
	 * @param  object $row
	 *
	 * @return array
	 */
	public function get_row_actions( $row ) {

		$edit_link            = $this->get_edit_links( $row );
		$post_status          = $row->post_status;
		$post_type_object     = get_post_type_object( $row->post_type );
		$current_status_trash = ( 'trash' === $post_status );

		//
		// Prepare column actions.
		//

		// Column ID.
		$actions = array(
			'id' => sprintf( '%s: %d', esc_html__( 'ID', 'user-registration' ), $row->ID ),
		);

		// Edit Action.
		if ( current_user_can( 'edit_post', $row->ID ) && 'trash' !== $post_status ) {
			$actions['edit'] = sprintf( '<a href="%s">%s</a>', esc_url( $edit_link ), esc_html__( 'Edit', 'user-registration' ) );
		}

		// Trash / Untrash / Delete Actions.
		if ( current_user_can( 'delete_post', $row->ID ) ) {

			if ( $current_status_trash ) {
				$untrash_link       = wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $row->ID ) ), 'untrash-post_' . $row->ID );
				$actions['untrash'] = sprintf(
					'<a aria-label="%s" href="%s">%s</a>',
					esc_attr__( 'Restore this item from the Trash', 'user-registration' ),
					$untrash_link,
					esc_html__( 'Restore', 'user-registration' )
				);
			} elseif ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = sprintf(
					'<a class="submitdelete" aria-label="%s" href="%s">%s</a>',
					esc_attr__( 'Move this item to the Trash', 'user-registration' ),
					get_delete_post_link( $row->ID ),
					esc_html__( 'Trash', 'user-registration' )
				);
			}

			if ( $current_status_trash || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = sprintf(
					'<a class="submitdelete" aria-label="%s" href="%s">%s</a>',
					esc_attr__( 'Delete this item permanently', 'user-registration' ),
					get_delete_post_link( $row->ID, '', true ),
					esc_html__( 'Delete permanently', 'user-registration' )
				);
			}
		}

		// Duplicate Post Action.
		if ( current_user_can( 'edit_post', $row->ID ) ) {
			$_nonce         = wp_create_nonce( 'ur_duplicate_post_' . $row->ID );
			$duplicate_link = admin_url( 'admin.php?page=user-registration-content-restriction&action=duplicate&nonce=' . $_nonce . '&post-id=' . $row->ID );

			if ( 'publish' === $post_status ) {
				$actions['duplicate'] = sprintf( '<a href="%s">%s</a>', esc_url( $duplicate_link ), esc_html__( 'Duplicate', 'user-registration' ) );
			}
		}

		return $actions;
	}

	/**
	 * Column: Status.
	 *
	 * @param  object $access_rule_post
	 *
	 * @return string
	 */
	public function column_status( $access_rule_post ) {
		$access_rule  = json_decode( $access_rule_post->post_content, true );
		$enabled      = urcr_is_access_rule_enabled( $access_rule );
		$data_rule_id = absint( $access_rule_post->ID );
		$status_class = $enabled ? 'enabled' : '';
		$status_checked = $enabled ? 'checked="checked"' : '';
		$output       = sprintf( '<input type="checkbox" class="user-registration-switch__control hide-show-check urcr-enable-access-rule %s" data-rule-id="%d" %s>', esc_attr( $status_class ), $data_rule_id, esc_attr( $status_checked ) );
		return $output;
	}

	/**
	 * Column: Status.
	 *
	 * @param  object $access_rule_post
	 *
	 * @return string
	 */
	public function column_access_control( $access_rule_post ) {
		$access_rule    = json_decode( $access_rule_post->post_content, true );
		$enabled        = urcr_is_access_rule_enabled( $access_rule );
		$access_control = isset( $access_rule['actions'][0]['access_control'] ) ? $access_rule['actions'][0]['access_control'] : '';
		$status_label   = ( 'access' === $access_control || '' === $access_control ) ? esc_html__( 'Access', 'user-registration' ) : esc_html__( 'Restrict', 'user-registration' );
		$output         = sprintf( '<span>%s</span>', $status_label );
		return $output;
	}

	/**
	 * Column: Action.
	 *
	 * @param  object $access_rule_post
	 *
	 * @return string
	 */
	public function column_action( $access_rule_post ) {
		$access_rule = json_decode( $access_rule_post->post_content, true );
		$actions     = array();

		foreach ( $access_rule['actions'] as $action ) {
			$actions[] = ! empty( $action['label'] ) ? $action['label'] : strtoupper( isset( $action['type'] ) ? $action['type'] : '' );
		}
		$actions = implode( ' | ', $actions );
		$actions = ! empty( $actions ) ? $actions : '—';

		return '<span>' . $actions . '</span>';
	}

	/**
	 * Render the list table page, including header, notices, status filters and table.
	 */
	public function display_page() {
		$this->prepare_items();
		?>
		<hr class="wp-header-end">
		<?php echo user_registration_plugin_main_header(); ?>
		<div class="user-registration-list-table-container">
			<div id="user-registration-list-table-page">
				<div class="user-registration-list-table-header">
					<h2><?php esc_html_e( 'All Rules', 'user-registration' ); ?></h2>
					<a href="#" class="page-title-action"><?php esc_html_e( 'Add New', 'user-registration' ); ?></a>
				</div>
				<div class="user-registration-list-table-page__body">
					<form id="urcr-content-access-rules-list" method="get" class="user-registration-list-table-action-form">
						<input type="hidden" name="page" value="user-registration-content-restriction" />
						<?php
						echo "<div id='user-registration-list-filters-row'>";
//						$this->views();
//						$this->search_box( esc_html__( 'Search Rule', 'user-registration' ), 'content-access-rule' );
//						echo '</div>';
						$this->display();
						?>
					</form>
				</div>
			</div>
		</div>

		<?php
	}

	/**
	 * Displays the search box.
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = 'user-registration-list-table-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['detached'] ) ) {
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
		}
		?>
		<div id="user-registration-list-search-form">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" placeholder="<?php esc_html_e( 'Search Rules ...', 'user-registration' ); ?>" />
			<button type="submit" id="search-submit">
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<path fill="#000" fill-rule="evenodd" d="M4 11a7 7 0 1 1 12.042 4.856 1.012 1.012 0 0 0-.186.186A7 7 0 0 1 4 11Zm12.618 7.032a9 9 0 1 1 1.414-1.414l3.675 3.675a1 1 0 0 1-1.414 1.414l-3.675-3.675Z" clip-rule="evenodd"/>
				</svg>
			</button>
		</div>
		<?php
	}
}

