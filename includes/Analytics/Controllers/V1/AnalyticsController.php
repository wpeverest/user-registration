<?php

namespace WPEverest\URM\Analytics\Controllers\V1;

use WPEverest\URM\Analytics\Services\AnalyticsDataService;

class AnalyticsController extends \WP_REST_Controller {

	protected $namespace = 'user-registration/v1';

	protected $rest_base = 'analytics';

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_overview' ),
					'permission_callback' => array( $this, 'check_permissions' ),
					'args'                => $this->get_analytics_args(),
				),
			)
		);
	}

	/**
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_overview( $request ) {
		$date_from  = $request->get_param( 'date_from' );
		$date_to    = $request->get_param( 'date_to' );
		$unit       = $request->get_param( 'unit' ) ?? 'day';
		$scope      = $request->get_param( 'scope' ) ?? 'all';
		$membership = $request->get_param( 'membership' ) ?? null;

		if ( empty( $date_from ) || empty( $date_to ) ) {
			return new \WP_Error(
				'rest_invalid_param',
				__( 'date_from and date_to are required parameters.', 'user-registration' ),
				[ 'status' => 400 ]
			);
		}

		$start_date = strtotime( $date_from . ' 00:00:00' );
		$end_date   = strtotime( $date_to . ' 23:59:59' );

		if ( false === $start_date || false === $end_date ) {
			return new \WP_Error(
				'rest_invalid_param',
				__( 'Invalid date format. Use YYYY-MM-DD format.', 'user-registration' ),
				[ 'status' => 400 ]
			);
		}

		if ( ! in_array( $scope, [ 'membership', 'others', 'all' ], true ) ) {
			return new \WP_Error(
				'rest_invalid_param',
				__( 'Invalid scope parameter. Allowed values are all, others, membership.', 'user-registration' ),
				[ 'status' => 400 ]
			);
		}

		if ( 'membership' === $scope && ( null === $membership || ! is_numeric( $membership ) ) ) {
			return new \WP_Error(
				'rest_invalid_param',
				__( 'Valid membership ID is required when scope is membership.', 'user-registration' ),
				[ 'status' => 400 ]
			);
		}

		$data_service = new AnalyticsDataService();

		$date_range_data = $data_service->get_date_range_members_data( $start_date, $end_date, $unit );
		$revenue_data    = $data_service->get_revenue_date_range_data( $start_date, $end_date, $unit, $scope, $membership );

		$duration       = $end_date - $start_date;
		$previous_end   = $start_date - DAY_IN_SECONDS;
		$previous_start = $previous_end - $duration;

		$comparison_data       = $data_service->get_date_range_members_data( $previous_start, $previous_end, $unit );
		$previous_revenue_data = $data_service->get_revenue_date_range_data( $previous_start, $previous_end, $unit, $scope, $membership );

		$new_members_change      = $data_service->calculate_percentage_change( $date_range_data['new_members'], $comparison_data['new_members'] );
		$approved_members_change = $data_service->calculate_percentage_change( $date_range_data['approved_members'], $comparison_data['approved_members'] );
		$pending_members_change  = $data_service->calculate_percentage_change( $date_range_data['pending_members'], $comparison_data['pending_members'] );
		$denied_members_change   = $data_service->calculate_percentage_change( $date_range_data['denied_members'], $comparison_data['denied_members'] );
		$total_revenue_change    = $data_service->calculate_percentage_change( $revenue_data['total_revenue'], $previous_revenue_data['total_revenue'] );
		$avg_order_value_change  = $data_service->calculate_percentage_change( $revenue_data['average_order_value'], $previous_revenue_data['average_order_value'] );
		$refunded_revenue_change = $data_service->calculate_percentage_change( $revenue_data['refunded_revenue'], $previous_revenue_data['refunded_revenue'] );

		$refunded_revenue_count        = $data_service->get_refunds_count( $start_date, $end_date );
		$previous_refunded_count       = $data_service->get_refunds_count( $previous_start, $previous_end );
		$refunded_revenue_count_change = $data_service->calculate_percentage_change( $refunded_revenue_count, $previous_refunded_count );

		$response = array(
			'new_members'            => array(
				'count'             => $date_range_data['new_members'],
				'previous'          => $comparison_data['new_members'],
				'percentage_change' => $new_members_change,
				'currency'          => false,
			),
			'approved_members'       => array(
				'count'             => $date_range_data['approved_members'],
				'previous'          => $comparison_data['approved_members'],
				'percentage_change' => $approved_members_change,
				'currency'          => false,
			),
			'pending_members'        => array(
				'count'             => $date_range_data['pending_members'],
				'previous'          => $comparison_data['pending_members'],
				'percentage_change' => $pending_members_change,
				'currency'          => false,
			),
			'denied_members'         => array(
				'count'             => $date_range_data['denied_members'],
				'previous'          => $comparison_data['denied_members'],
				'percentage_change' => $denied_members_change,
				'currency'          => false,
			),
			'total_revenue'          => array(
				'count'             => $revenue_data['total_revenue'],
				'previous'          => $previous_revenue_data['total_revenue'],
				'percentage_change' => $total_revenue_change,
				'currency'          => true,
			),
			'average_order_value'    => array(
				'count'             => $revenue_data['average_order_value'],
				'previous'          => $previous_revenue_data['average_order_value'],
				'percentage_change' => $avg_order_value_change,
				'currency'          => true,
			),
			'refunded_revenue'       => array(
				'count'             => $revenue_data['refunded_revenue'],
				'previous'          => $previous_revenue_data['refunded_revenue'],
				'percentage_change' => $refunded_revenue_change,
				'currency'          => true,
			),
			'refunded_revenue_count' => array(
				'count'             => $refunded_revenue_count,
				'previous'          => $previous_refunded_count,
				'percentage_change' => $refunded_revenue_count_change,
				'currency'          => false,
			),
		);

		return rest_ensure_response( $response );
	}

	public function check_permissions( $request ) {
		if ( ! current_user_can( 'manage_user_registration' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
			return new \WP_Error(
				'rest_forbidden',
				\__( 'Sorry, you are not allowed to access analytics data.', 'user-registration' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	protected function get_analytics_args() {
		return array(
			'date_from'  => [
				'description' => __( 'Start date in YYYY-MM-DD format.', 'user-registration' ),
				'type'        => 'string',
				'required'    => false,
				'default'     => gmdate( 'Y-m-d', strtotime( '-30 days' ) ),
			],
			'date_to'    => [
				'description' => __( 'End date in YYYY-MM-DD format.', 'user-registration' ),
				'type'        => 'string',
				'required'    => false,
				'default'     => gmdate( 'Y-m-d' ),
			],
			'unit'       => [
				'description' => __( 'Time unit for data aggregation (hour, day, week, month, year).', 'user-registration' ),
				'type'        => 'string',
				'required'    => false,
				'default'     => 'day',
				'enum'        => [ 'hour', 'day', 'week', 'month', 'year' ],
			],
			'scope'      => [
				'description' => __( 'Scope of the analytics data (all, others, membership).', 'user-registration' ),
				'type'        => 'string',
				'required'    => false,
				'default'     => 'all',
				'enum'        => [ 'all', 'others', 'membership' ],
			],
			'membership' => [
				'description' => __( 'Membership ID for filtering data when scope is membership.', 'user-registration' ),
				'type'        => 'string',
				'required'    => false,
				'default'     => null,
			],
		);
	}
}
