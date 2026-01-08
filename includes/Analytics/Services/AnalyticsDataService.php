<?php

namespace WPEverest\URM\Analytics\Services;

defined( 'ABSPATH' ) || exit;

use WPEverest\URM\Analytics\Traits\DateUtils;
use WPEverest\URMembership\TableList;

// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

class AnalyticsDataService {

	use DateUtils;

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @param string $unit
	 * @return array
	 */
	public function get_date_range_members_data( $start_date, $end_date, $unit = 'day' ) {
		global $wpdb;

		$new_members      = 0;
		$approved_members = 0;
		$pending_members  = 0;
		$denied_members   = 0;

		$date_keys = $this->generate_date_keys( $start_date, $end_date, $unit );

		$default_value = [
			'new_members_in_a_day'      => 0,
			'approved_members_in_a_day' => 0,
			'pending_members_in_a_day'  => 0,
			'denied_members_in_a_day'   => 0,
		];

		$daily_data = $this->initialize_data_structure( $date_keys, $default_value );

		$members = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT u.ID, u.user_registered
					FROM {$wpdb->users} u
					INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
					WHERE um.meta_key = %s
					AND u.user_registered BETWEEN %s AND %s",
				'ur_form_id',
				wp_date( 'Y-m-d H:i:s', $start_date ),
				wp_date( 'Y-m-d H:i:s', $end_date )
			)
		);

		if ( empty( $members ) ) {
			return [
				'new_members'      => 0,
				'approved_members' => 0,
				'pending_members'  => 0,
				'denied_members'   => 0,
				'date_difference'  => human_time_diff( $start_date, $end_date ),
				'daily_data'       => $daily_data,
			];
		}

		$member_ids       = wp_list_pluck( $members, 'ID' );
		$member_meta_data = $this->fetch_member_meta_bulk( $member_ids );

		foreach ( $members as $member ) {
			$member_id              = $member->ID;
			$registration_timestamp = strtotime( $member->user_registered );
			$registration_date      = $this->get_date_key_for_timestamp( $registration_timestamp, $unit );

			++$new_members;

			$status       = $member_meta_data[ $member_id ]['user_status'] ?? '';
			$email_status = $member_meta_data[ $member_id ]['user_email_status'] ?? '';

			if ( '' === $status && '' === $email_status ) {
				++$approved_members;
				$member_status = 'approved';
			} elseif ( '' !== $status && '' === $email_status ) {
				if ( 1 === (int) $status ) {
					++$approved_members;
					$member_status = 'approved';
				} elseif ( 0 === (int) $status ) {
					++$pending_members;
					$member_status = 'pending';
				} else {
					++$denied_members;
					$member_status = 'denied';
				}
			} elseif ( '' !== $email_status ) {
				if ( 1 === (int) $email_status ) {
					++$approved_members;
					$member_status = 'approved';
				} else {
					++$pending_members;
					$member_status = 'pending';
				}
			}

			if ( isset( $daily_data[ $registration_date ] ) ) {
				++$daily_data[ $registration_date ]['new_members_in_a_day'];
				if ( 'approved' === $member_status ) {
					++$daily_data[ $registration_date ]['approved_members_in_a_day'];
				} elseif ( 'pending' === $member_status ) {
					++$daily_data[ $registration_date ]['pending_members_in_a_day'];
				} elseif ( 'denied' === $member_status ) {
					++$daily_data[ $registration_date ]['denied_members_in_a_day'];
				}
			}
		}

		$total_members = max( $new_members, 1 );

		return [
			'new_members'                 => $new_members,
			'approved_members'            => $approved_members,
			'approved_members_percentage' => round( ( $approved_members / $total_members ) * 100, 2 ),
			'pending_members'             => $pending_members,
			'pending_members_percentage'  => round( ( $pending_members / $total_members ) * 100, 2 ),
			'denied_members'              => $denied_members,
			'denied_members_percentage'   => round( ( $denied_members / $total_members ) * 100, 2 ),
			'date_difference'             => human_time_diff( $start_date, $end_date ),
			'daily_data'                  => $daily_data,
		];
	}


	/**
	 * @param array $member_ids
	 * @return array
	 */
	protected function fetch_member_meta_bulk( $member_ids ) {
		if ( empty( $member_ids ) ) {
			return [];
		}

		update_meta_cache( 'user', $member_ids );

		$meta_data = [];
		foreach ( $member_ids as $member_id ) {
			$user_status       = get_user_meta( $member_id, 'ur_user_status', true );
			$user_email_status = get_user_meta( $member_id, 'ur_confirm_email', true );

			$meta_data[ $member_id ] = [
				'user_status'       => $user_status,
				'user_email_status' => $user_email_status,
			];
		}

		return $meta_data;
	}

	/**
	 * @param float $current
	 * @param float $previous
	 * @return float Percentage
	 */
	public function calculate_percentage_change( $current, $previous ) {
		$current  = (float) $current;
		$previous = (float) $previous;

		if ( 0.0 === $previous && 0.0 === $current ) {
			return 0.0;
		}

		if ( 0.0 === $previous ) {
			return $current > 0 ? 100.0 : -100.0;
		}

		return round( ( ( $current - $previous ) / $previous ) * 100, 2 );
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @return int
	 */
	public function get_refunds_count( $start_date, $end_date ) {
		global $wpdb;
		$orders_table = TableList::orders_table();

		if ( ! $this->table_exists( $orders_table ) ) {
			return 0;
		}

		$start_date_str = wp_date( 'Y-m-d H:i:s', $start_date );
		$end_date_str   = wp_date( 'Y-m-d H:i:s', $end_date );

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$orders_table}
				WHERE status = 'refunded'
				AND created_at BETWEEN %s AND %s",
				$start_date_str,
				$end_date_str
			)
		);

		return (int) $result;
	}

	/**
	 * @param string $table_name
	 * @return bool
	 */
	protected function table_exists( $table_name ) {
		global $wpdb;
		$result = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
		return $result === $table_name;
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @param string $unit
	 * @return array
	 */
	protected function generate_date_keys( $start_date, $end_date, $unit = 'day' ) {
		$date_keys = [];

		if ( 'hour' === $unit ) {
			$date_format = 'Y-m-d H';
			$incrementor = HOUR_IN_SECONDS;
			for ( $i = $start_date; $i <= $end_date; $i += $incrementor ) {
				$date_keys[] = wp_date( $date_format, $i );
			}
		} elseif ( 'month' === $unit ) {
			$current_date = new \DateTime();
			$current_date->setTimestamp( $start_date );
			$end_date_obj = new \DateTime();
			$end_date_obj->setTimestamp( $end_date );
			$end_date_obj->setTime( 23, 59, 59 );
			$current_date->modify( 'first day of this month' );
			$current_date->setTime( 0, 0, 0 );

			while ( $current_date <= $end_date_obj ) {
				$date_keys[] = $current_date->format( 'Y-m' );
				$current_date->modify( '+1 month' );
			}
		} elseif ( 'year' === $unit ) {
			$current_date = new \DateTime();
			$current_date->setTimestamp( $start_date );
			$end_date_obj = new \DateTime();
			$end_date_obj->setTimestamp( $end_date );
			$end_date_obj->setTime( 23, 59, 59 );
			$current_date->modify( 'first day of January this year' );
			$current_date->setTime( 0, 0, 0 );

			while ( $current_date <= $end_date_obj ) {
				$date_keys[] = $current_date->format( 'Y' );
				$current_date->modify( '+1 year' );
			}
		} elseif ( 'week' === $unit ) {
			$current_date = new \DateTime();
			$current_date->setTimestamp( $start_date );
			$end_date_obj = new \DateTime();
			$end_date_obj->setTimestamp( $end_date );
			$end_date_obj->setTime( 23, 59, 59 );
			$day_of_week    = (int) $current_date->format( 'w' );
			$days_to_monday = ( $day_of_week === 0 ? 6 : $day_of_week - 1 );
			$current_date->modify( "-{$days_to_monday} days" );
			$current_date->setTime( 0, 0, 0 );

			while ( $current_date <= $end_date_obj ) {
				$date_keys[] = $current_date->format( 'Y-m-d' );
				$current_date->modify( '+1 week' );
			}
		} else {
			$date_format = 'Y-m-d';
			$incrementor = DAY_IN_SECONDS;
			for ( $i = $start_date; $i <= $end_date; $i += $incrementor ) {
				$date_keys[] = wp_date( $date_format, $i );
			}
		}

		return $date_keys;
	}

	/**
	 * @param array $date_keys
	 * @param mixed $default_value
	 * @return array
	 */
	protected function initialize_data_structure( $date_keys, $default_value ) {
		$data = [];
		foreach ( $date_keys as $key ) {
			$data[ $key ] = $default_value;
		}
		return $data;
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @param string $unit
	 * @return array
	 */
	public function initialize_daily_data_structure( $start_date, $end_date, $unit = 'day' ) {
		$date_keys = $this->generate_date_keys( $start_date, $end_date, $unit );

		$default_value = [
			'total_revenue'        => 0,
			'paid_revenue'         => 0,
			'refunded_revenue'     => 0,
			'completed_orders'     => 0,
			'refunded_orders'      => 0,
			'new_payments_revenue' => 0,
			'single_item_revenue'  => 0,
			'average_order_value'  => 0,
		];

		return $this->initialize_data_structure( $date_keys, $default_value );
	}

	/**
	 * @param int $start_date
	 * @param int $end_date
	 * @param string $unit
	 * @param string $scope 'all'|'membership'|'others'
	 * @param int|null $membership
	 * @return array
	 */
	public function get_revenue_date_range_data( $start_date, $end_date, $unit = 'day', $scope = 'all', $membership = null ) {
		global $wpdb;
		$orders_table = ( new TableList() )->orders_table();
		$daily_data   = $this->initialize_daily_data_structure( $start_date, $end_date, $unit );

		$include_single_items = ( 'others' === $scope || 'all' === $scope );

		$single_item_revenue = [];
		if ( $include_single_items ) {
			$single_item_revenue = $this->get_single_item_revenue_data( $start_date, $end_date, $unit );
		}

		if ( 'others' === $scope ) {
			$total_single_item_revenue = 0;
			foreach ( $single_item_revenue as $time_key => $data ) {
				$single_item_amount = $data['single_item_revenue'] ?? 0;
				if ( isset( $daily_data[ $time_key ] ) ) {
					$daily_data[ $time_key ]['total_revenue']        = $single_item_amount;
					$daily_data[ $time_key ]['new_payments_revenue'] = $single_item_amount;
					$daily_data[ $time_key ]['single_item_revenue']  = $single_item_amount;
					$total_single_item_revenue                      += $single_item_amount;
				}
			}

			return [
				'total_revenue'        => $total_single_item_revenue,
				'net_revenue'          => $total_single_item_revenue,
				'refunded_revenue'     => 0,
				'total_orders'         => 0,
				'total_refunds'        => 0,
				'new_payments_revenue' => $total_single_item_revenue,
				'single_item_revenue'  => $total_single_item_revenue,
				'average_order_value'  => 0,
				'daily_data'           => $daily_data,
			];
		}

		if ( ! $this->table_exists( $orders_table ) ) {
			if ( $include_single_items ) {
				$total_single_item_revenue = 0;
				foreach ( $single_item_revenue as $time_key => $data ) {
					$single_item_amount = $data['single_item_revenue'] ?? 0;
					if ( isset( $daily_data[ $time_key ] ) ) {
						$daily_data[ $time_key ]['total_revenue']        = $single_item_amount;
						$daily_data[ $time_key ]['new_payments_revenue'] = $single_item_amount;
						$daily_data[ $time_key ]['single_item_revenue']  = $single_item_amount;
						$total_single_item_revenue                      += $single_item_amount;
					}
				}

				return [
					'total_revenue'        => $total_single_item_revenue,
					'net_revenue'          => $total_single_item_revenue,
					'refunded_revenue'     => 0,
					'total_orders'         => 0,
					'total_refunds'        => 0,
					'new_payments_revenue' => $total_single_item_revenue,
					'single_item_revenue'  => $total_single_item_revenue,
					'average_order_value'  => 0,
					'daily_data'           => $daily_data,
				];
			}

			return [
				'total_revenue'        => 0,
				'net_revenue'          => 0,
				'refunded_revenue'     => 0,
				'total_orders'         => 0,
				'total_refunds'        => 0,
				'new_payments_revenue' => 0,
				'single_item_revenue'  => 0,
				'average_order_value'  => 0,
				'daily_data'           => $daily_data,
			];
		}

		$membership   = ( 'membership' === $scope && null !== $membership ) ? absint( $membership ) : null;
		$group_by_map = [
			'hour'  => "DATE_FORMAT(created_at, '%Y-%m-%d %H')",
			'day'   => 'DATE(created_at)',
			'week'  => 'DATE(DATE_SUB(created_at, INTERVAL WEEKDAY(created_at) DAY))',
			'month' => "DATE_FORMAT(created_at, '%Y-%m')",
			'year'  => "DATE_FORMAT(created_at, '%Y')",
		];

		$group_by       = $group_by_map[ $unit ] ?? $group_by_map['day'];
		$start_date_str = wp_date( 'Y-m-d H:i:s', $start_date );
		$end_date_str   = wp_date( 'Y-m-d H:i:s', $end_date );

		$daily_data = $this->initialize_daily_data_structure( $start_date, $end_date, $unit );

		$membership_filter = '';
		$query_params      = [ $start_date_str, $end_date_str ];
		if ( ! empty( $membership ) ) {
			$membership_filter = ' AND item_id = %d';
			$query_params[]    = absint( $membership );
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					{$group_by} AS time_key,
					COALESCE(SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END), 0) AS total_revenue,
					COALESCE(SUM(CASE WHEN status = 'completed' AND order_type = 'paid' THEN total_amount ELSE 0 END), 0) AS paid_revenue,
					COALESCE(SUM(CASE WHEN status = 'refunded' THEN total_amount ELSE 0 END), 0) AS refunded_revenue,
					COUNT(CASE WHEN status = 'completed' THEN 1 END) AS completed_orders,
					COUNT(CASE WHEN status = 'refunded' THEN 1 END) AS refunded_orders
				FROM {$orders_table}
				WHERE created_at BETWEEN %s AND %s {$membership_filter}
				GROUP BY time_key
				ORDER BY time_key",
				...$query_params
			)
		);

		foreach ( $results as $row ) {
			$time_key             = $row->time_key;
			$paid_revenue         = (float) $row->paid_revenue;
			$completed_orders     = (int) $row->completed_orders;
			$new_payments_revenue = $paid_revenue;
			$average_order_value  = $completed_orders > 0 ? ( (float) $row->total_revenue ) / $completed_orders : 0;

			if ( isset( $daily_data[ $time_key ] ) ) {
				$daily_data[ $time_key ] = [
					'total_revenue'        => (float) $row->total_revenue,
					'paid_revenue'         => $paid_revenue,
					'refunded_revenue'     => (float) $row->refunded_revenue,
					'completed_orders'     => $completed_orders,
					'refunded_orders'      => (int) $row->refunded_orders,
					'new_payments_revenue' => $new_payments_revenue,
					'average_order_value'  => $average_order_value,
					'single_item_revenue'  => 0,
				];
			}
		}

		if ( $include_single_items ) {
			foreach ( $single_item_revenue as $time_key => $data ) {
				$single_item_amount = $data['single_item_revenue'] ?? 0;

				if ( isset( $daily_data[ $time_key ] ) ) {
					$daily_data[ $time_key ]['single_item_revenue']   = $single_item_amount;
					$daily_data[ $time_key ]['total_revenue']        += $single_item_amount;
					$daily_data[ $time_key ]['new_payments_revenue'] += $single_item_amount;

					$orders = $daily_data[ $time_key ]['completed_orders'];
					if ( $orders > 0 ) {
						$daily_data[ $time_key ]['average_order_value'] = $daily_data[ $time_key ]['total_revenue'] / $orders;
					}
				}
			}
		}

		$total_revenue              = array_sum( array_column( $daily_data, 'total_revenue' ) );
		$total_refunded             = array_sum( array_column( $daily_data, 'refunded_revenue' ) );
		$net_revenue                = $total_revenue - $total_refunded;
		$total_orders               = array_sum( array_column( $daily_data, 'completed_orders' ) );
		$total_refunds              = array_sum( array_column( $daily_data, 'refunded_orders' ) );
		$total_new_payments_revenue = array_sum( array_column( $daily_data, 'new_payments_revenue' ) );
		$total_single_item_revenue  = array_sum( array_column( $daily_data, 'single_item_revenue' ) );
		$total_average_order_value  = $total_orders > 0 ? $total_revenue / $total_orders : 0;

		return [
			'total_revenue'        => $total_revenue,
			'net_revenue'          => $net_revenue,
			'refunded_revenue'     => $total_refunded,
			'total_orders'         => $total_orders,
			'total_refunds'        => $total_refunds,
			'new_payments_revenue' => $total_new_payments_revenue,
			'single_item_revenue'  => $total_single_item_revenue,
			'average_order_value'  => $total_average_order_value,
			'daily_data'           => $daily_data,
		];
	}

	public function get_single_item_revenue_data( $start_date, $end_date, $unit = 'day' ) {
		/** @var \wpdb $wpdb */
		global $wpdb;

		$daily_data = $this->initialize_daily_data_structure( $start_date, $end_date, $unit );

		$invoices = array_unique(
			$wpdb->get_col(
				$wpdb->prepare(
					"SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = %s",
					'ur_payment_invoices'
				)
			)
		);

		$format_map = [
			'hour'  => 'Y-m-d H',
			'day'   => 'Y-m-d',
			'week'  => 'Y-m-d',
			'month' => 'Y-m',
			'year'  => 'Y',
		];

		$format = $format_map[ $unit ] ?? $format_map['day'];

		foreach ( $invoices as $invoice ) {
			$invoice_data = maybe_unserialize( $invoice, true );
			if ( ! is_array( $invoice_data ) || ! isset( $invoice_data[0] ) || ! is_array( $invoice_data[0] ) ) {
				continue;
			}
			$invoice_data = $invoice_data[0];

			if ( ! isset( $invoice_data['invoice_date'], $invoice_data['invoice_amount'], $invoice_data['invoice_status'] ) ) {
				continue;
			}

			if ( 'completed' !== $invoice_data['invoice_status'] ) {
				continue;
			}

			$date              = new \DateTime( $invoice_data['invoice_date'] );
			$invoice_timestamp = $date->getTimestamp();

			if ( $invoice_timestamp < $start_date || $invoice_timestamp > $end_date ) {
				continue;
			}

			if ( 'week' === $unit ) {
				$date->modify( '-' . ( (int) $date->format( 'N' ) - 1 ) . ' days' );
			}

			$time_key = $date->format( $format );
			$amount   = (float) $invoice_data['invoice_amount'];

			if ( isset( $daily_data[ $time_key ] ) ) {
				$daily_data[ $time_key ]['single_item_revenue'] = ( $daily_data[ $time_key ]['single_item_revenue'] ?? 0 ) + $amount;
			}
		}

		return $daily_data;
	}
}
