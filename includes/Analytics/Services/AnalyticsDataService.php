<?php

namespace WPEverest\URM\Analytics\Services;

defined( 'ABSPATH' ) || exit;

use WPEverest\URM\Analytics\Traits\DateUtils;
use WPEverest\URMembership\TableList;

// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

class AnalyticsDataService {

	use DateUtils;

	/**
	 * @param int    $start_date
	 * @param int    $end_date
	 * @param string $unit
	 * @return array
	 */
	public function get_date_range_members_data( $start_date, $end_date, $unit = 'day' ) {
		global $wpdb;

		$date_keys     = $this->generate_date_keys( $start_date, $end_date, $unit );
		$default_value = array(
			'new_members_in_a_day'      => 0,
			'approved_members_in_a_day' => 0,
			'pending_members_in_a_day'  => 0,
			'denied_members_in_a_day'   => 0,
		);
		$daily_data    = $this->initialize_data_structure( $date_keys, $default_value );

		// %% escapes MySQL format specifiers from wpdb::prepare
		$group_by_map = array(
			'hour'  => "DATE_FORMAT(u.user_registered, '%%Y-%%m-%%d %%H')",
			'day'   => 'DATE(u.user_registered)',
			'week'  => 'DATE(DATE_SUB(u.user_registered, INTERVAL WEEKDAY(u.user_registered) DAY))',
			'month' => "DATE_FORMAT(u.user_registered, '%%Y-%%m')",
			'year'  => "DATE_FORMAT(u.user_registered, '%%Y')",
		);
		$group_by     = $group_by_map[ $unit ] ?? $group_by_map['day'];

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT {$group_by} AS time_key,
					COUNT(DISTINCT u.ID) AS total_count,
					SUM(CASE
						WHEN um_email.meta_value IS NOT NULL AND um_email.meta_value != '' THEN
							CASE WHEN CAST(um_email.meta_value AS UNSIGNED) = 1 THEN 1 ELSE 0 END
						WHEN um_status.meta_value IS NULL OR um_status.meta_value = '' THEN 1
						WHEN CAST(um_status.meta_value AS UNSIGNED) = 1 THEN 1
						ELSE 0
					END) AS approved_count,
					SUM(CASE
						WHEN um_email.meta_value IS NOT NULL AND um_email.meta_value != '' THEN
							CASE WHEN CAST(um_email.meta_value AS UNSIGNED) != 1 THEN 1 ELSE 0 END
						WHEN um_status.meta_value IS NOT NULL AND um_status.meta_value != ''
							AND CAST(um_status.meta_value AS UNSIGNED) = 0 THEN 1
						ELSE 0
					END) AS pending_count,
					SUM(CASE
						WHEN ( um_email.meta_value IS NULL OR um_email.meta_value = '' )
							AND um_status.meta_value IS NOT NULL AND um_status.meta_value != ''
							AND CAST(um_status.meta_value AS UNSIGNED) != 0
							AND CAST(um_status.meta_value AS UNSIGNED) != 1 THEN 1
						ELSE 0
					END) AS denied_count
				FROM {$wpdb->users} u
				INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = %s
				LEFT JOIN {$wpdb->usermeta} um_status ON u.ID = um_status.user_id AND um_status.meta_key = %s
				LEFT JOIN {$wpdb->usermeta} um_email ON u.ID = um_email.user_id AND um_email.meta_key = %s
				WHERE u.user_registered BETWEEN %s AND %s
				GROUP BY time_key ORDER BY time_key",
				'ur_form_id',
				'ur_user_status',
				'ur_confirm_email',
				wp_date( 'Y-m-d H:i:s', $start_date ),
				wp_date( 'Y-m-d H:i:s', $end_date )
			)
		);

		if ( empty( $results ) ) {
			return array(
				'new_members'      => 0,
				'approved_members' => 0,
				'pending_members'  => 0,
				'denied_members'   => 0,
				'date_difference'  => human_time_diff( $start_date, $end_date ),
				'daily_data'       => $daily_data,
			);
		}

		$new_members      = 0;
		$approved_members = 0;
		$pending_members  = 0;
		$denied_members   = 0;

		foreach ( $results as $row ) {
			$time_key = $row->time_key;
			$total    = (int) $row->total_count;
			$approved = (int) $row->approved_count;
			$pending  = (int) $row->pending_count;
			$denied   = (int) $row->denied_count;

			$new_members      += $total;
			$approved_members += $approved;
			$pending_members  += $pending;
			$denied_members   += $denied;

			if ( isset( $daily_data[ $time_key ] ) ) {
				$daily_data[ $time_key ]['new_members_in_a_day']      = $total;
				$daily_data[ $time_key ]['approved_members_in_a_day'] = $approved;
				$daily_data[ $time_key ]['pending_members_in_a_day']  = $pending;
				$daily_data[ $time_key ]['denied_members_in_a_day']   = $denied;
			}
		}

		$total_members = max( $new_members, 1 );

		return array(
			'new_members'                 => $new_members,
			'approved_members'            => $approved_members,
			'approved_members_percentage' => round( ( $approved_members / $total_members ) * 100, 2 ),
			'pending_members'             => $pending_members,
			'pending_members_percentage'  => round( ( $pending_members / $total_members ) * 100, 2 ),
			'denied_members'              => $denied_members,
			'denied_members_percentage'   => round( ( $denied_members / $total_members ) * 100, 2 ),
			'date_difference'             => human_time_diff( $start_date, $end_date ),
			'daily_data'                  => $daily_data,
		);
	}


	/**
	 * @param array $member_ids
	 * @return array
	 */
	protected function fetch_member_meta_bulk( $member_ids ) {
		if ( empty( $member_ids ) ) {
			return array();
		}

		global $wpdb;

		$meta_data = array_fill_keys(
			$member_ids,
			array(
				'user_status'       => '',
				'user_email_status' => '',
			)
		);

		foreach ( array_chunk( $member_ids, 500 ) as $chunk ) {
			$placeholders = implode( ',', array_fill( 0, count( $chunk ), '%d' ) );
			$rows         = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT user_id, meta_key, meta_value FROM {$wpdb->usermeta}
					 WHERE user_id IN ({$placeholders}) AND meta_key IN ('ur_user_status', 'ur_confirm_email')",
					...$chunk
				)
			);
			foreach ( $rows as $row ) {
				if ( 'ur_user_status' === $row->meta_key ) {
					$meta_data[ (int) $row->user_id ]['user_status'] = $row->meta_value;
				} elseif ( 'ur_confirm_email' === $row->meta_key ) {
					$meta_data[ (int) $row->user_id ]['user_email_status'] = $row->meta_value;
				}
			}
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
	 * @param int    $start_date
	 * @param int    $end_date
	 * @param string $unit
	 * @return array
	 */
	protected function generate_date_keys( $start_date, $end_date, $unit = 'day' ) {
		$date_keys = array();

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
		$data = array();
		foreach ( $date_keys as $key ) {
			$data[ $key ] = $default_value;
		}
		return $data;
	}

	/**
	 * @param int    $start_date
	 * @param int    $end_date
	 * @param string $unit
	 * @return array
	 */
	public function initialize_daily_data_structure( $start_date, $end_date, $unit = 'day' ) {
		$date_keys = $this->generate_date_keys( $start_date, $end_date, $unit );

		$default_value = array(
			'total_revenue'        => 0,
			'paid_revenue'         => 0,
			'refunded_revenue'     => 0,
			'completed_orders'     => 0,
			'refunded_orders'      => 0,
			'new_payments_revenue' => 0,
			'single_item_revenue'  => 0,
			'average_order_value'  => 0,
		);

		return $this->initialize_data_structure( $date_keys, $default_value );
	}

	/**
	 * @param int      $start_date
	 * @param int      $end_date
	 * @param string   $unit
	 * @param string   $scope 'all'|'membership'|'others'
	 * @param int|null $membership
	 * @return array
	 */
	public function get_revenue_date_range_data( $start_date, $end_date, $unit = 'day', $scope = 'all', $membership = null ) {
		global $wpdb;
		$orders_table = ( new TableList() )->orders_table();
		$daily_data   = $this->initialize_daily_data_structure( $start_date, $end_date, $unit );

		$include_single_items = ( 'others' === $scope || 'all' === $scope );

		$single_item_revenue = array();
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

			return array(
				'total_revenue'        => $total_single_item_revenue,
				'net_revenue'          => $total_single_item_revenue,
				'refunded_revenue'     => 0,
				'total_orders'         => 0,
				'total_refunds'        => 0,
				'new_payments_revenue' => $total_single_item_revenue,
				'single_item_revenue'  => $total_single_item_revenue,
				'average_order_value'  => 0,
				'daily_data'           => $daily_data,
			);
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

				return array(
					'total_revenue'        => $total_single_item_revenue,
					'net_revenue'          => $total_single_item_revenue,
					'refunded_revenue'     => 0,
					'total_orders'         => 0,
					'total_refunds'        => 0,
					'new_payments_revenue' => $total_single_item_revenue,
					'single_item_revenue'  => $total_single_item_revenue,
					'average_order_value'  => 0,
					'daily_data'           => $daily_data,
				);
			}

			return array(
				'total_revenue'        => 0,
				'net_revenue'          => 0,
				'refunded_revenue'     => 0,
				'total_orders'         => 0,
				'total_refunds'        => 0,
				'new_payments_revenue' => 0,
				'single_item_revenue'  => 0,
				'average_order_value'  => 0,
				'daily_data'           => $daily_data,
			);
		}

		$membership   = ( 'membership' === $scope && null !== $membership ) ? absint( $membership ) : null;
		$group_by_map = array(
			'hour'  => "DATE_FORMAT(created_at, '%Y-%m-%d %H')",
			'day'   => 'DATE(created_at)',
			'week'  => 'DATE(DATE_SUB(created_at, INTERVAL WEEKDAY(created_at) DAY))',
			'month' => "DATE_FORMAT(created_at, '%Y-%m')",
			'year'  => "DATE_FORMAT(created_at, '%Y')",
		);

		$group_by       = $group_by_map[ $unit ] ?? $group_by_map['day'];
		$start_date_str = wp_date( 'Y-m-d H:i:s', $start_date );
		$end_date_str   = wp_date( 'Y-m-d H:i:s', $end_date );

		$daily_data = $this->initialize_daily_data_structure( $start_date, $end_date, $unit );

		$membership_filter = '';
		$query_params      = array( $start_date_str, $end_date_str );
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
				$daily_data[ $time_key ] = array(
					'total_revenue'        => (float) $row->total_revenue,
					'paid_revenue'         => $paid_revenue,
					'refunded_revenue'     => (float) $row->refunded_revenue,
					'completed_orders'     => $completed_orders,
					'refunded_orders'      => (int) $row->refunded_orders,
					'new_payments_revenue' => $new_payments_revenue,
					'average_order_value'  => $average_order_value,
					'single_item_revenue'  => 0,
				);
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

		return array(
			'total_revenue'        => $total_revenue,
			'net_revenue'          => $net_revenue,
			'refunded_revenue'     => $total_refunded,
			'total_orders'         => $total_orders,
			'total_refunds'        => $total_refunds,
			'new_payments_revenue' => $total_new_payments_revenue,
			'single_item_revenue'  => $total_single_item_revenue,
			'average_order_value'  => $total_average_order_value,
			'daily_data'           => $daily_data,
		);
	}

	public function get_single_item_revenue_data( $start_date, $end_date, $unit = 'day' ) {
		/** @var \wpdb $wpdb */
		global $wpdb;

		$daily_data = $this->initialize_daily_data_structure( $start_date, $end_date, $unit );

		$start_year  = (int) wp_date( 'Y', $start_date );
		$end_year    = (int) wp_date( 'Y', $end_date );
		$year_params = array();
		foreach ( range( $start_year, $end_year ) as $y ) {
			$year_params[] = '%"' . $y . '-%';
		}
		$year_likes = implode( ' OR ', array_fill( 0, count( $year_params ), 'meta_value LIKE %s' ) );

		$invoices = $wpdb->get_col(
			$wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				"SELECT DISTINCT meta_value FROM {$wpdb->usermeta}
				 WHERE meta_key = %s AND ({$year_likes})",
				'ur_payment_invoices',
				...$year_params
			)
		);

		$format_map = array(
			'hour'  => 'Y-m-d H',
			'day'   => 'Y-m-d',
			'week'  => 'Y-m-d',
			'month' => 'Y-m',
			'year'  => 'Y',
		);

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
