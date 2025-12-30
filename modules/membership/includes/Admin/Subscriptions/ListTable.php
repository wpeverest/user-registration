<?php

namespace WPEverest\URMembership\Admin\Subscriptions;

use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Repositories\SubscriptionRepository;

if ( ! class_exists( 'UR_List_Table' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/abstracts/abstract-ur-list-table.php';
}

class ListTable extends \UR_List_Table {

	public function __construct() {
		$this->post_type       = 'ur_membership';
		$this->page            = 'user-registration-subscriptions';
		$this->per_page_option = 'user_registration_subscriptions_per_page';
		$this->addnew_action   = 'add_new_subscription';
		$this->sort_by         = [
			'subscription_id'   => [ 'subscription_id', false ],
			'user_id'           => [ 'user_id', false ],
			'item_id'           => [ 'item_id', false ],
			'start_date'        => [ 'start_date', true ],
			'next_billing_date' => [ 'next_billing_date', false ],
			'status'            => [ 'status', false ],
		];

		parent::__construct(
			[
				'singular' => 'subscription',
				'plural'   => 'subscriptions',
				'ajax'     => false,
			]
		);
	}

	public function get_duplicate_link( $subscription ) {
		return '';
	}

	public function get_edit_links( $subscription ) {
		return admin_url( "admin.php?page={$this->page}&action=edit&id={$subscription->ID}" );
	}

	public function get_columns() {
		return apply_filters(
			'ur_membership_subscriptions_list_table_columns',
			[
				'id'         => __( 'Subscription ID', 'user-registration' ),
				'user_id'    => __( 'Member', 'user-registration' ),
				'item_id'    => __( 'Membership', 'user-registration' ),
				'start_date' => __( 'Created At', 'user-registration' ),
				'status'     => __( 'Status', 'user-registration' ),
			]
		);
	}

	public function get_hidden_columns() {
		return array();
	}

	public function column_id( $subscription ) {
		$edit_link = $this->get_edit_links( $subscription );
		foreach ( $this->get_row_actions( $subscription ) as $action => $link ) {
			$row_actions[] = sprintf( '<span class="%s">%s</span>', esc_attr( $action ), $link );
		}
		return sprintf(
			'<strong><div class="ur-edit-title"><a href="%s" class="row-title">#%s</a></div></strong><div class="row-actions">%s</div>',
			esc_url( $edit_link ),
			esc_html( $subscription->ID ),
			implode( ' | ', $row_actions )
		);
	}

	public function column_user_id( $subscription ) {
		$user              = get_user( $subscription->user_id );
		$first_name        = get_user_meta( $subscription->user_id, 'first_name', true );
		$last_name         = get_user_meta( $subscription->user_id, 'last_name', true );
		$user_display_name = $user->user_login;
		if ( $first_name || $last_name ) {
			$user_display_name = trim( $first_name . ' ' . $last_name );
		} elseif ( ! empty( $user->display_name ) ) {
			$user_display_name = $user->display_name;
		} elseif ( ! empty( $user->nickname ) ) {
			$user_display_name = $user->nickname;
		}
		return sprintf(
			'<a href="%s">%s</a>',
			admin_url( "admin.php?page=user-registration-members&action=edit&member_id={$subscription->user_id}" ),
			$user_display_name
		);
	}

	public function column_item_id( $subscription ) {
		$membership = ( new MembershipRepository() )->get_single_membership_by_ID( $subscription->item_id );
		if ( ! $membership ) {
			return '-';
		}
		return sprintf(
			'<a href="%s">%s</a>',
			admin_url( "admin.php?post_id={$subscription->item_id}&action=add_new_membership&page=user-registration-membership" ),
			$membership['post_title']
		);
	}

	public function column_start_date( $subscription ) {
		return date_i18n( get_option( 'date_format' ), strtotime( $subscription->start_date ) );
	}

	public function column_next_billing_date( $subscription ) {
		return date_i18n( get_option( 'date_format' ), strtotime( $subscription->next_billing_date ) );
	}

	public function column_status( $subscription ) {
		return sprintf(
			'<span class="subscription-status %s">%s</span>',
			$subscription->status,
			ucfirst( $subscription->status )
		);
	}

	public function prepare_items() {
		$this->prepare_column_headers();

		$per_page     = $this->get_items_per_page( $this->get_items_per_page_key(), 20 );
		$current_page = $this->get_pagenum();
		$orderby      = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'ID';
		$order        = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'DESC';

		$search     = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$membership = isset( $_GET['membership'] ) ? absint( wp_unslash( $_GET['membership'] ) ) : 0;
		$status     = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';

		$args = array(
			'page'     => $current_page,
			'per_page' => $per_page,
			'orderby'  => $orderby,
			'order'    => $order,
			'search'   => $search,
			'item_id'  => $membership,
			'status'   => $status,
		);

		$subscription_repository = new SubscriptionRepository();
		$result                  = $subscription_repository->query( $args );

		$this->items = $result['items'];

		$this->set_pagination_args(
			array(
				'total_items' => $result['total'],
				'per_page'    => $result['per_page'],
				'total_pages' => $result['total_pages'],
			)
		);
	}

	public function display_page() {
		$this->prepare_items();
		?>
<div id="user-registration-list-table-page">
	<div class="user-registration-list-table-heading">
		<h1>
			<?php esc_html_e( 'All Subscriptions', 'user-registration' ); ?>
		</h1>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->page . '&action=create' ) ); ?>"
			class="page-title-action">
			<?php esc_html_e( 'Add New', 'user-registration' ); ?>
		</a>
	</div>
	<div id="user-registration-list-filters-row">
		<form method="get" id="user-registration-list-search-form">
			<input type="hidden" name="page" value="user-registration-membership">
			<div>
				<input type="search" id="subscriptions-list-search-input" name="s"
					value="<?php echo esc_attr( $_GET['s'] ?? '' ); ?>"
					placeholder="<?php esc_attr_e( 'Search Subscription', ' user-registration' ); ?> ..."
					autocomplete="off">
				<button type="submit" id="search-submit">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
						<path fill="#000" fill-rule="evenodd"
							d="M4 11a7 7 0 1 1 12.042 4.856 1.012 1.012 0 0 0-.186.186A7 7 0 0 1 4 11Zm12.618 7.032a9 9 0 1 1 1.414-1.414l3.675 3.675a1 1 0 0 1-1.414 1.414l-3.675-3.675Z"
							clip-rule="evenodd"></path>
					</svg>
				</button>
			</div>
		</form>
	</div>
	<hr />
	<form id="subscriptions-list" method="get">
		<input type="hidden" name="page" value="<?php echo esc_attr( $this->page ); ?>" />
		<?php
				$this->screen->render_screen_reader_content( 'heading_list' );
				$this->display();
		?>
	</form>
</div>
		<?php
	}

	public function get_row_actions( $row ) {
		$delete_url = wp_nonce_url(
			admin_url( 'admin.php?page=user-registration-subscriptions&action=delete&id=' . $row->ID ),
			'ur_subscription_delete'
		);
		return [
			'id'     => sprintf(
				/* translators: %d: Item id */
				esc_html__( 'ID: %d', 'user-registration' ),
				$row->ID
			),
			'edit'   => '<a href="' . esc_url( $this->get_edit_links( $row ) ) . '">' . __( 'Edit', 'user-registration' ) . '</a>',
			'delete' => '<a class="submitdelete" aria-label="' . esc_attr__( 'Delete this item permanently', 'user-registration' ) . '" href="' . $delete_url . '">' . esc_html__( 'Delete', 'user-registration' ) . '</a>',
		];
	}
}
