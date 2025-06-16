<?php
/**
 * User Registration Membership Table List
 *
 * @version 1.0.0
 */

namespace WPEverest\URMembership\Payment\Admin;

use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URMembership\TableList;

if ( ! class_exists( 'UR_List_Table' ) ) {
	include_once dirname( UR_PLUGIN_FILE ) . '/includes/abstracts/abstract-ur-list-table.php';
}

/**
 * Orders table list class.
 */
class OrdersListTable extends \UR_List_Table {
	protected $orders_repository, $is_membership_active;

	/**
	 * Initialize the Orders table list.
	 */
	public function __construct() {

		$this->page                 = 'user-registration-membership';
		$this->per_page_option      = 'user_registration_membership_per_page';
		$this->addnew_action        = 'add_new_orders';
		$this->sort_by              = array(
			'title' => array( 'title', false ),
		);
		$this->is_membership_active = ur_check_module_activation( 'membership' );
		$this->orders_repository    = ( $this->is_membership_active ) ? new OrdersRepository() : '';

		parent::__construct(
			array(
				'singular' => 'order',
				'plural'   => 'orders',
				'ajax'     => true,
			)
		);
	}


	/**
	 * Prepares the items for display in the list table.
	 *
	 * This function prepares the column headers, sets the items per page, gets the current page number,
	 * determines the payment for parameter, prepares the query arguments, retrieves the total items,
	 * sets the items, and sets the pagination arguments.
	 *
	 * @return void
	 */
	public function prepare_items() {
		$this->prepare_column_headers();
		$per_page     = $this->get_items_per_page( $this->per_page_option );
		$current_page = $this->get_pagenum();
		$payment_for  = isset( $_REQUEST['payment_for'] ) && ! empty( $_REQUEST['payment_for'] ) ? $_REQUEST['payment_for'] : ( $this->is_membership_active ? 'memberships' : 'forms' );

		$args = $this->prepare_query_args( $payment_for, $per_page, $current_page );

		$total_items = array();
		if ( $payment_for === 'memberships' ) {
			$total_items = $this->orders_repository->get_all( $args );
		} else {
			$total_items = $this->get_user_payments( $args );
		}

		$this->items = $total_items;

		$this->set_pagination_args(
			array(
				'total_items' => count( $total_items ),
				'per_page'    => $per_page,
				'total_pages' => ceil( count( $total_items ) / $per_page ),
			)
		);
	}

	/**
	 * @param $payment_for
	 * @param $per_page
	 * @param $current_page
	 *
	 * @return array
	 */
	private function prepare_query_args( $payment_for, $per_page, $current_page ) {
		$args = array(
			'posts_per_page' => $per_page,
			'paged'          => $current_page,
		);

		$params_mapping = array(
			'status'         => 'ur_payment_status',
			'membership_id'  => 'membership_id',
			'payment_method' => 'ur_payment_method',
			'form_id'        => 'ur_form_id',
			's'              => 'search',
		);

		foreach ( $params_mapping as $request_param => $query_param ) {
			if ( ! empty( $_REQUEST[ $request_param ] ) ) {
				$value = sanitize_text_field( wp_unslash( $_REQUEST[ $request_param ] ) );
				if ( $payment_for === 'memberships' ) {
					$args[ $request_param ] = $value;
				} elseif ( $query_param === 'search' ) {
						$args['search']         = '*' . esc_attr( $value ) . '*';
						$args['search_columns'] = array( 'display_name', 'user_email' );
				} else {
					$args['meta_query'][] = array(
						'key'     => $query_param,
						'value'   => $value,
						'compare' => 'LIKE',
					);
				}
			}
		}

		$orderby         = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'created_at';
		$args['orderby'] = in_array( $orderby, array( 'created_at', 'status' ) ) ? $orderby : 'created_at';

		$order         = isset( $_REQUEST['order'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) : 'DESC';
		$args['order'] = in_array( $order, array( 'ASC', 'DESC' ) ) ? $order : 'DESC';

		return $args;
	}

	/**
	 * @param $args
	 *
	 * @return array
	 */
	private function get_user_payments( $args ) {
		$args['meta_key']               = 'ur_payment_status';
		$args['meta_compare']           = 'EXISTS';
		$args['meta_query']['relation'] = 'AND';

		$user_query = new \WP_User_Query( $args );
		$users      = $user_query->get_results();

		$total_items = array();
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$meta_value = get_user_meta( $user->ID, 'ur_payment_invoices', true );

				$total_items[] = array(
					'user_id'        => $user->ID,
					'display_name'   => $user->user_login,
					'user_email'     => $user->user_email,
					'transaction_id' => $meta_value[0]['invoice_no'] ?? '',
					'post_title'     => $meta_value[0]['invoice_plan'] ?? '',
					'status'         => get_user_meta( $user->ID, 'ur_payment_status', true ),
					'created_at'     => $meta_value[0]['invoice_date'] ?? '',
					'type'           => get_user_meta( $user->ID, 'ur_payment_type', true ),
					'payment_method' => str_replace( '_', ' ', get_user_meta( $user->ID, 'ur_payment_method', true ) ),
				);
			}
		}

		return $total_items;
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'transaction_id':
				return isset( $item['transaction_id'] ) && ! empty( $item['transaction_id'] ) ? $item['transaction_id'] : ( $item['order_id'] ?? '' );
			case 'username':
				return esc_html( $item['display_name'] );
			case 'membership_type':
				return $this->show_column_membership_type( $item );
			case 'status':
				return $this->show_column_status( $item );
			case 'created_at':
				return date_i18n( get_option( 'date_format' ), strtotime( $item[ $column_name ] ) );
			case 'post_title':
			case 'payment_method':
				return esc_html( ucfirst( $item[ $column_name ] ) );
			case 'payer_email':
				return esc_html( $item['user_email'] );
			default:
				return print_r( $item, true ); // Fallback output for unknown columns
		}
	}

	/**
	 * No items found text.
	 */
	public function no_items() {
		$image_url = esc_url( plugin_dir_url( UR_PLUGIN_FILE ) . 'assets/images/empty-table.png' );
		?>
		<div class="empty-list-table-container">
			<img src="<?php echo $image_url; ?>" alt="">
			<h3><?php echo __( 'You don\'t have any Payments yet.', 'user-registration' ); ?></h3>
			<p><?php echo __( 'Please add Payments and you are good to go.', 'user-registration' ); ?></p>
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
			'transaction_id'  => __( 'Transaction ID', 'user-registration' ),
			'username'        => __( 'Username', 'user-registration' ),
			'post_title'      => __( 'Product', 'user-registration' ),
			'membership_type' => __( 'Type', 'user-registration' ),
			'payment_method'  => __( 'Gateway', 'user-registration' ),
			'payer_email'     => __( 'Payer Email', 'user-registration' ),
			'status'          => __( 'Status', 'user-registration' ),
			'created_at'      => __( 'Payment Date', 'user-registration' ),
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
	 * @param $order
	 *
	 * @return array
	 */
	public function get_row_actions( $order ) {

		return array();
	}

	/**
	 * @param $order
	 *
	 * @return string
	 */
	public function show_column_status( $order ) {

		$order_id = $order['order_id'] ?? $order['user_id'];

		return sprintf( '<span id="ur-order-%d"  class="payment-status-btn %s">%s</span>', absint( $order_id ), $order['status'], esc_html( ucfirst( $order['status'] ) ) );
	}

	/**
	 * @param $orders
	 *
	 * @return string
	 */
	public function show_column_membership_type( $orders ) {

		if ( isset( $orders['order_id'] ) ) {
			$data = json_decode( wp_unslash( $orders['post_content'] ), true );
			$type = $data['type'];
		} else {
			$type = $orders['type'];
		}
		$status_class = ( $type == 'free' ? 'user-registration-badge user-registration-badge--success-subtle' : ( $type == 'paid' ? 'user-registration-badge user-registration-badge--secondary-subtle' : 'user-registration-badge user-registration-badge--danger-subtle' ) );

		return sprintf( '<span class="%s">%s</span>', $status_class, esc_html( ucfirst( $type ) ) );
	}

	public function get_sortable_columns() {
		return array(
			'created_at' => array( 'created_at' ),
			'status'     => array( 'status' ),
		);
	}


	/**
	 * @param $order
	 *
	 * @return string
	 */
	public function column_action( $order ) {
		$order_id = $order['order_id'] ?? 0;
		$user_id  = $order['user_id'] ?? 0;

		return '
				<div class="row-actions ur-d-flex ur-align-items-center visible" style="gap: 5px">
					<span class="view">
						<a class="show-order-detail" data-user-id=' . esc_attr( $user_id ) . ' data-order-id = ' . esc_attr( $order_id ) . ' href="javascript:void(0)">' . __( 'View', 'user-registration' ) . '</a>
					</span>
					&nbsp | &nbsp
					<span class="trash">
						<a data-user-id=' . esc_attr( $user_id ) . ' data-order-id = ' . esc_attr( $order_id ) . ' class="single-delete-order" style="cursor:pointer" >' . esc_html__( 'Trash', 'user-registration' ) . '</a>
					</span>
					</div>
					';
	}

	/**
	 * Render the list table page, including header, notices, status filters and table.
	 */
	public function display_page() {
		$this->prepare_items();
		if ( ! isset( $_GET['add-new-membership'] ) ) { // phpcs:ignore Standard.Category.SniffName.ErrorCode: input var okay, CSRF ok.
			?>
			<div class="wrap">
				<form id="membership-list" method="get">
					<input type="hidden" name="page" value="<?php echo $this->page; ?>"/>
					<?php
					$this->display();
					?>
				</form>
			</div>
			<!--			modal to show the details of individual order/payment-->
			<div id="payment-detail-modal"></div>
			<?php
		}
	}

	public function display() {
		$this->display_tablenav( 'top' );
		$this->screen->render_screen_reader_content( 'heading_list' );
		?>
		<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
			<thead>
			<tr>
				<?php $this->print_column_headers(); ?>
			</tr>
			</thead>
			<tbody id="the-list">
			<?php $this->display_rows_or_placeholder(); ?>
			</tbody>
		</table>
		<?php
		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Displays advance filter.
	 *
	 * @since 4.1
	 */
	public function display_advance_filter() {
		$input_id             = 'user-registration-payment-history-search';
		$is_membership_active = $this->is_membership_active;
		$show_membership      = ( isset( $_REQUEST['payment_for'] ) && 'memberships' == $_REQUEST['payment_for'] || ( ! isset( $_REQUEST['payment_for'] ) && $this->is_membership_active !== null ) );

		?>
		<!--		main search box-->
		<div class="search-box">
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s"
					value="<?php _admin_search_query(); ?>"
					placeholder="<?php esc_html_e( 'Search Members ...', 'user-registration' ); ?>"/>
			<input type="hidden" name="page" value="member-payment-history">
			<?php wp_nonce_field( 'user-registration-pro-filter-members' ); ?>
			<button type="submit" id="search-submit">
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<path fill="#000" fill-rule="evenodd"
							d="M4 11a7 7 0 1 1 12.042 4.856 1.012 1.012 0 0 0-.186.186A7 7 0 0 1 4 11Zm12.618 7.032a9 9 0 1 1 1.414-1.414l3.675 3.675a1 1 0 0 1-1.414 1.414l-3.675-3.675Z"
							clip-rule="evenodd"/>
				</svg>
			</button>
		</div>
		<div style="display: flex; gap: 10px">
			<select name="payment_for" id="user-registration-pro-payment-type-filters" class="ur-enhanced-select">
				<option
					value="" selected><?php echo esc_html__( 'Select Module', 'user-registration' ); ?></option>
				<option
					value="forms" <?php echo isset( $_REQUEST['payment_for'] ) && 'forms' == $_REQUEST['payment_for'] ? 'selected=selected' : ''; ?>><?php echo esc_html__( 'Forms', 'user-registration' ); ?></option>
				<?php
				if ( $this->is_membership_active ) :
					?>
					<option
						value="memberships" <?php echo ( isset( $_REQUEST['payment_for'] ) && 'memberships' == $_REQUEST['payment_for'] ) || ! isset( $_REQUEST['payment_for'] ) ? 'selected=selected' : ''; ?>><?php echo esc_html__( 'Membership', 'user-registration' ); ?></option>
					<?php
				endif;
				?>
			</select>
		</div>
		<!--		membership dropdown-->
		<div class="module-box" id="user-registration-pro-memberships-filters-container"
			style="display:<?php echo $show_membership ? 'flex' : 'none'; ?>; gap: 10px;">
			<select name="membership_id" id="user-registration-pro-memberships-filter" class="ur-enhanced-select">
				<option
					value=""><?php echo esc_html__( 'All Membership', 'user-registration' ); ?></option>
				<?php
				foreach ( $this->get_all_memberships() as $id => $form ) {
					$selected = isset( $_REQUEST['membership_id'] ) && $id == $_REQUEST['membership_id'] ? 'selected=selected' : '';
					?>
					<option
						value='<?php echo esc_attr( $id ); ?>' <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $form ); ?></option>
					<?php
				}
				?>
			</select>
		</div>

		<div class="module-box" id="user-registration-pro-forms-filters-container"
			style="display:<?php echo ! $show_membership && $_REQUEST['payment_for'] == 'forms' ? 'flex' : 'none'; ?>; gap: 10px;">
			<select name="form_id" id="user-registration-pro-forms-filter" class="ur-enhanced-select">
				<option
					value=""><?php echo esc_html__( 'All Forms', 'user-registration' ); ?></option>
				<?php
				foreach ( ur_get_all_user_registration_form() as $id => $form ) {
					$selected = isset( $_REQUEST['form_id'] ) && $id == $_REQUEST['form_id'] ? 'selected=selected' : '';
					?>
					<option
						value='<?php echo esc_attr( $id ); ?>' <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $form ); ?></option>
					<?php
				}
				?>
			</select>
		</div>

		<div class="" id="user-registration-pro-members-filters" style="display: flex; gap: 10px">
			<select name="payment_method" id="user_registration_pro_users_form_filter" class="ur-enhanced-select">
				<option
					value=""><?php echo esc_html__( 'All Gateway', 'user-registration' ); ?></option>
				<?php
				$options = ( isset( $_REQUEST['payment_for'] ) && 'membership' == $_REQUEST['payment_for'] ) ? get_option( 'ur_membership_payment_gateways' ) : get_option( 'ur_payment_gateways' );

				foreach ( $options as $id => $form ) {
					$selected = isset( $_REQUEST['payment_method'] ) && $id == $_REQUEST['payment_method'] ? 'selected=selected' : '';
					?>
					<option
						value='<?php echo esc_attr( $id ); ?>' <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $form ); ?></option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="" id="user-registration-pro-members-filters" style="display: flex; gap: 10px">
			<select name="status" id="user_registration_pro_users_form_filter" class="ur-enhanced-select">
				<option
					value=""><?php echo esc_html__( 'All Status', 'user-registration' ); ?></option>
				<?php
				foreach ( array( 'completed', 'pending', 'failed', 'refunded' ) as $id => $status ) {
					$selected = isset( $_REQUEST['status'] ) && $status == $_REQUEST['status'] ? 'selected=selected' : '';
					?>
					<option
						value='<?php echo esc_attr( $status ); ?>' <?php echo esc_attr( $selected ); ?>><?php echo esc_html( ucfirst( $status ) ); ?></option>
					<?php
				}
				?>
			</select>
		</div>
		<button type="submit" name="ur_users_filter" id="user-registration-users-filter-btn"
				class="button ur-button-primary">
			<?php esc_html_e( 'Filter', 'user-registration' ); ?>
		</button>
		<?php
	}

	/**
	 * @return array
	 */
	public function get_all_memberships() {
		$posts        = get_posts(
			array(
				'post_type'   => 'ur_membership',
				'numberposts' => - 1,
			)
		);
		$active_posts = array_filter(
			json_decode( json_encode( $posts ), true ),
			function ( $item ) {
				$content = json_decode( wp_unslash( $item['post_content'] ), true );

				return $content['status'];
			}
		);

		return wp_list_pluck( $active_posts, 'post_title', 'ID' );
	}

	public function get_all_forms() {
		$posts        = get_posts(
			array(
				'post_type'   => 'ur_membership',
				'numberposts' => - 1,
			)
		);
		$active_posts = array_filter(
			json_decode( json_encode( $posts ), true ),
			function ( $item ) {
				$content = json_decode( wp_unslash( $item['post_content'] ), true );

				return $content['status'];
			}
		);

		return wp_list_pluck( $active_posts, 'post_title', 'ID' );
	}

	/**
	 * Generates the table navigation above or below the table
	 *
	 * @param string $which
	 *
	 * @since 3.1.0
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
			 * @param array $actions An array of the available bulk actions.
			 *
			 * @since 5.6.0 A bulk action can now contain an array of options in order to create an optgroup.
			 *
			 * @since 3.1.0
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
		echo '<select name="action' . $two . '" class="ur-enhanced-select" id="bulk-action-selector-' . esc_attr( $which ) . "\">\n";
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

		submit_button( __( 'Apply' ), 'action', '', false, array( 'id' => "doaction-orders$two" ) );
		echo "\n";
	}

	/**
	 * @return array
	 */
	protected function get_bulk_actions() {
		$actions = array();
		if ( is_multisite() ) {
			if ( current_user_can( 'remove_users' ) ) {
				$actions['remove'] = __( 'Remove', 'user-registration' );
			}
		} elseif ( current_user_can( 'delete_users' ) ) {
				$actions['delete'] = __( 'Delete', 'user-registration' );
		}

		return $actions;
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="order_id[]" value="%1$s" data-user-id="%2$s" /><span class="spinner"></span>', esc_attr( $item['order_id'] ?? '' ), esc_attr( $item['user_id'] ?? '' ) );
	}
}
