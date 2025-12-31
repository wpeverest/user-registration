<?php

namespace WPEverest\URM\Analytics\Traits;

defined( 'ABSPATH' ) || exit;

trait DateUtils {

	/**
	 * @param int $timestamp
	 * @param string $unit
	 * @return string
	 */
	protected function get_date_key_for_timestamp( $timestamp, $unit = 'day' ) {
		if ( 'hour' === $unit ) {
			return wp_date( 'Y-m-d H', $timestamp );
		} elseif ( 'month' === $unit ) {
			return wp_date( 'Y-m', $timestamp );
		} elseif ( 'year' === $unit ) {
			return wp_date( 'Y', $timestamp );
		} elseif ( 'week' === $unit ) {
			return $this->get_week_monday_key( $timestamp );
		} else {
			return wp_date( 'Y-m-d', $timestamp );
		}
	}

	/**
	 * @param int $timestamp
	 * @return string
	 */
	protected function get_week_monday_key( $timestamp ) {
		$date = new \DateTime();
		$date->setTimestamp( $timestamp );

		$day_of_week    = (int) $date->format( 'w' );
		$days_to_monday = ( $day_of_week === 0 ? 6 : $day_of_week - 1 );

		if ( $days_to_monday > 0 ) {
			$date->modify( "-{$days_to_monday} days" );
		}

		return $date->format( 'Y-m-d' );
	}

	/**
	 * @param int $timestamp
	 * @param string $unit
	 * @param bool $is_end_date
	 * @return \DateTime
	 */
	protected function create_aligned_datetime( $timestamp, $unit, $is_end_date = false ) {
		$date = new \DateTime();
		$date->setTimestamp( $timestamp );

		if ( $is_end_date && in_array( $unit, [ 'week', 'month', 'year' ], true ) ) {
			$date->setTime( 23, 59, 59 );
		} else {
			$date->setTime( 0, 0, 0 );
		}

		switch ( $unit ) {
			case 'week':
				$day_of_week    = (int) $date->format( 'w' );
				$days_to_monday = ( $day_of_week === 0 ? 6 : $day_of_week - 1 );
				if ( $days_to_monday > 0 && ! $is_end_date ) {
					$date->modify( "-{$days_to_monday} days" );
					$date->setTime( 0, 0, 0 );
				}
				break;
			case 'month':
				if ( ! $is_end_date ) {
					$date->modify( 'first day of this month' );
					$date->setTime( 0, 0, 0 );
				}
				break;
			case 'year':
				if ( ! $is_end_date ) {
					$date->modify( 'first day of January this year' );
					$date->setTime( 0, 0, 0 );
				}
				break;
		}

		return $date;
	}

	/**
	 * @param int|null $timestamp
	 * @return string
	 */
	protected function timestamp_to_sql_datetime( $timestamp ) {
		return wp_date( 'Y-m-d H:i:s', $timestamp );
	}
}
