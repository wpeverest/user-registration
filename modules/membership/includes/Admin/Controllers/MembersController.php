<?php
/**
 * URMembership MembersController.
 *
 * @class    Frontend
 * @version  1.0.0
 * @package  URMembership/MembersController
 * @category Controller
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Admin\Controllers;

use Exception;
use WPEverest\URMembership\Admin\Interfaces\MembersInterface;
use WPEverest\URMembership\Admin\Repositories\MembersRepository;
use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URMembership\Admin\Repositories\SubscriptionRepository;
use WPEverest\URMembership\Admin\Services\EmailService;
use WPEverest\URMembership\Admin\Services\MembersService;
use WPEverest\URMembership\Admin\Services\OrderService;
use WPEverest\URMembership\Admin\Services\SubscriptionService;

/**
 * MembersController
 */
class MembersController {

	/**
	 * The repository for managing members.
	 *
	 * @var use WPEverest\URMembership\Admin\Repositories\MembersRepository;
	 */
	protected $members;

	/**
	 * The repository for managing orders.
	 *
	 * @var use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
	 */
	protected $orders;

	/**
	 * The repository for managing subscriptions.
	 *
	 * @var use WPEverest\URMembership\Admin\Repositories\SubscriptionRepository;
	 */
	protected $subscriptions;

	/**
	 * Constructor for the MembersController class.
	 *
	 * @param MembersRepository $members The repository for managing members.
	 * @param OrdersRepository $orders The repository for managing orders.
	 * @param SubscriptionRepository $subscriptions The repository for managing subscriptions.
	 */
	public function __construct( MembersRepository $members, OrdersRepository $orders, SubscriptionRepository $subscriptions ) {
		$this->members       = $members;
		$this->orders        = $orders;
		$this->subscriptions = $subscriptions;

	}

	/**
	 * Creates a new member in the admin panel.
	 *
	 * @param array $data The data for creating the member.
	 *
	 * @return array An array containing the member ID and status, or an array with an error message and status.
	 */
	public function create_members_admin( $data ) {
		$data = apply_filters( 'urm_create_member_admin_before_validation', $data );

		$members_service = new MembersService();
		$validation      = $members_service->validate_user_data( $data ); // Backend validation for new users.

		if ( $validation['status'] ) {
			$this->members->wpdb()->query( 'START TRANSACTION' ); // Start the transaction.
			try {
				$data = apply_filters( 'urm_create_member_admin_before_preparing_data', $data );

				$members_data = $members_service->prepare_members_data( $data );
				$member       = $this->members->create( $members_data ); // first create the member themselves.

				if ( $member->ID ) {
					$subscription_service = new SubscriptionService();
					$subscription_data    = $subscription_service->prepare_subscription_data( $members_data, $member );
					$subscription         = $this->subscriptions->create( $subscription_data );
					$order_service        = new OrderService();
					$orders_data          = $order_service->prepare_orders_data( $members_data, $member->ID, $subscription ); // prepare data for orders table.
					$order                = $this->orders->create( $orders_data );
					$email_service        = new EmailService();
					$data                 = array_merge(
						$data,
						array(
							'payment_method' => 'free',
							'member_id'      => $member->ID,
						)
					);
					$data                 = apply_filters( 'urm_create_member_admin_before_sending_email', $data );
					$email_service->send_email( $data, 'user_register_backend_user' );

					if ( $subscription ) {
						$this->members->wpdb()->query( 'COMMIT' );

						$data = array(
							'member_id' => $member->ID,
							'status'    => true,
						);

						return apply_filters( 'urm_create_member_admin_after_member_created', $data, $member, $subscription, $order );
					}
				}
			} catch ( Exception $e ) {
				// Rollback the transaction if any operation fails.
				$this->members->wpdb()->query( 'ROLLBACK' );

				$data = array(
					'message' => $e->getMessage(),
					'status'  => false,
				);

				return apply_filters( 'urm_create_member_admin_after_error', $data, $e );
			}
		}

		return apply_filters( 'urm_create_member_admin_after_validation', $validation );
	}

	/**
	 * Creates a new member in the public panel.
	 *
	 * @param array $data The data for creating the member.
	 *
	 * @return array An array containing the member ID, subscription ID, transaction ID, and status,
	 * or an array with an error message and status.
	 */
	public function create_members_public( $data ) {
		$members_service = new MembersService();
		$validation      = $members_service->validate_user_data( $data ); // backend validation for new users.

		if ( $validation['status'] ) {
			$this->members->wpdb()->query( 'START TRANSACTION' ); // Start the transaction.
			try {
				$members_data = $members_service->prepare_members_data( $data );

				$member = $this->members->create( $members_data ); // first create the member themselves.
				if ( $member->ID ) {
					$subscription_service = new SubscriptionService();
					$subscription_data    = $subscription_service->prepare_subscription_data( $members_data, $member );
					$subscription         = $this->subscriptions->create( $subscription_data );
					$order_service        = new OrderService();
					$orders_data          = $order_service->prepare_orders_data( $members_data, $member->ID, $subscription ); // prepare data for orders table.
					$order                = $this->orders->create( $orders_data );
					if ( $subscription && $order ) {
						$this->members->wpdb()->query( 'COMMIT' );

						return array(
							'member_id'       => $member->ID,
							'subscription_id' => $subscription['ID'],
							'transaction_id'  => $orders_data['orders_data']['transaction_id'],
							'status'          => true,
						);
					}
				}
			} catch ( Exception $e ) {
				// Rollback the transaction if any operation fails.
				$this->members->wpdb()->query( 'ROLLBACK' );

				return array(
					'message' => $e->getMessage(),
					'status'  => false,
				);
			}
		} else {
			$data = apply_filters( 'urm_create_member_public_validation_failed', $data, $validation );

			return $validation;
		}
	}

}
