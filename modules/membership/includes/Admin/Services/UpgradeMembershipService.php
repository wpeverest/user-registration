<?php

namespace WPEverest\URMembership\Admin\Services;

use WPEverest\URMembership\Admin\Repositories\MembershipRepository;
use WPEverest\URMembership\Admin\Repositories\MembersOrderRepository;
use WPEverest\URMembership\Admin\Repositories\MembersSubscriptionRepository;
use WPEverest\URMembership\Admin\Repositories\OrdersRepository;
use WPEverest\URMembership\Admin\Repositories\SubscriptionRepository;
use WPEverest\URMembership\Admin\Repositories\MembershipGroupRepository;
use WPEverest\URMembership\Admin\Services\MembershipService;

class UpgradeMembershipService {

	protected $members_subscription_repository, $members_orders_repository, $membership_repository, $orders_repository, $subscription_repository, $membership_group_repository, $membership_service;

	public function __construct() {
		$this->members_subscription_repository = new MembersSubscriptionRepository();
		$this->subscription_repository         = new SubscriptionRepository();
		$this->members_orders_repository       = new MembersOrderRepository();
		$this->membership_repository           = new MembershipRepository();
		$this->orders_repository               = new OrdersRepository();
		$this->membership_group_repository     = new MembershipGroupRepository();
		$this->membership_service              = new MembershipService();
	}

	/**
	 * Handle Paid to Paid membership Upgrade
	 *
	 * @param $current_membership_details
	 * @param $selected_membership_details
	 * @param $subscription
	 *
	 * @return false[]
	 */
	public function calculate_chargeable_amount( $selected_amount, $current_amount, $upgrade_type ) {
		if ( 'full' === $upgrade_type ) {
			return $selected_amount;
		}
		if ( $selected_amount > $current_amount ) {
			return $selected_amount - $current_amount;
		}

		return $selected_amount;
	}

	public function handle_paid_to_paid_membership_upgrade( $current_membership_details, $selected_membership_details, $subscription ) {
		$upgrade_settings = $current_membership_details['upgrade_settings'];
		$response         = array(
			'status' => false,
		);
		if ( ! ( $upgrade_settings['upgrade_action'] ) ) {
			$response['status']  = true;
			$response['message'] = __( 'Membership upgrade is not enabled for this plan', 'user-registration' );
		}
		$response['status']            = true;
		$response['chargeable_amount'] = $this->calculate_chargeable_amount(
			$selected_membership_details['amount'],
			$current_membership_details['amount'],
			$upgrade_settings['upgrade_type']
		);

		return $response;
	}

	public function handle_paid_to_subscription_membership_upgrade( $current_membership_details, $selected_membership_details, $subscription ) {
		$selected_membership_amount   = $selected_membership_details['amount'];
		$current_membership_amount    = $current_membership_details['amount'];
		$upgrade_type                 = $current_membership_details['upgrade_settings']['upgrade_type'];
		$remaining_subscription_value = $selected_membership_details['subscription']['value'];
		$delayed_until                = '';

		$chargeable_amount = $this->calculate_chargeable_amount(
			$selected_membership_amount,
			$current_membership_amount,
			$upgrade_type
		);

		if ( 'partial' === $upgrade_type && $selected_membership_amount < $current_membership_amount ) {
			$delayed_until = $subscription['expiry_date'];
		}

		return array(
			'status'                       => true,
			'chargeable_amount'            => $chargeable_amount,
			'remaining_subscription_value' => $remaining_subscription_value,
			'delayed_until'                => $delayed_until,
		);
	}

	public function handle_subscription_to_paid_or_subscription_membership_upgrade( $current_membership_details, $selected_membership_details, $subscription, $is_trial ) {
		$selected_membership_amount   = $selected_membership_details['amount'];
		$current_membership_amount    = $current_membership_details['amount'];
		$upgrade_type                 = $current_membership_details['upgrade_settings']['upgrade_type'];
		$chargeable_amount            = 0;
		$remaining_subscription_value = $selected_membership_details['subscription']['value'];
		$delayed_until                = '';
		$timezone                     = get_option( 'timezone_string' );
		if ( ! $timezone ) {
			$timezone = 'UTC';
		}
		$tz       = new \DateTimeZone( $timezone );
		$dateTime = \DateTime::createFromFormat( 'Y-m-d', date( 'Y-m-d' ), $tz );

		if ( 'full' === $upgrade_type ) {
			$chargeable_amount = $selected_membership_amount;
		} elseif ( $selected_membership_amount > $current_membership_amount ) {
				$start_date                          = new \DateTime( $subscription['start_date'], $tz );
				$days_passed                         = $dateTime->diff( $start_date )->format( '%a' );
				$current_membership_duration_in_days = convert_to_days( $current_membership_details['subscription']['value'], $current_membership_details['subscription']['duration'] );
				$price_per_day                       = $current_membership_amount / $current_membership_duration_in_days;
				$prorate_discount                    = $current_membership_amount - ( $price_per_day * $days_passed );
				$chargeable_amount                   = ( $is_trial ) ? $selected_membership_amount : ( $selected_membership_amount - $prorate_discount );
		} else {
			$chargeable_amount = $selected_membership_amount;
			$delayed_until     = $subscription['expiry_date'];
			if ( $is_trial ) {
				$expiry_date          = new \DateTime( $subscription['expiry_date'], $tz );
				$trial_in_days        = convert_to_days( $selected_membership_details['trial_data']['value'], $selected_membership_details['trial_data']['duration'] );
				$trial_end_date       = ! empty( $subscription['trial_end_date'] ) ? $subscription['trial_end_date'] : date( 'Y-m-d 00:00:00', strtotime( "+$trial_in_days days" ) );
				$trial_end_date_obj   = new \DateTime( $trial_end_date, $tz );
				$remaining_trial_days = $dateTime->diff( $trial_end_date_obj )->format( '%a' );
				$delayed_until        = $expiry_date->modify( "+$remaining_trial_days days" )->format( 'Y-m-d' );
			}
		}

		return array(
			'status'                       => true,
			'chargeable_amount'            => $chargeable_amount,
			'remaining_subscription_value' => $remaining_subscription_value,
			'delayed_until'                => $delayed_until,
		);
	}

	/**
	 * Fetch upgrade paths for selected memberships in the group.
	 *
	 * @param string $memberships Membership List.
	 * @param string $upgrade_process Upgrade Process.
	 */
	public function fetch_upgrade_paths( $memberships, $upgrade_process = 'automatic' ) {
		$upgrade_type = 'full';

		if ( 'automatic' === $upgrade_process ) {
			$paths = array();
			foreach ( $memberships as $current ) {
				if ( empty( $current['ID'] ) ) {
					continue;
				}

				$current_id     = (int) $current['ID'];
				$current_meta   = isset( $current['meta_value'] ) ? $current['meta_value'] : array();
				$current_amount = isset( $current_meta['amount'] ) ? (float) $current_meta['amount'] : 0.0;

				$options = array();
				foreach ( $memberships as $target ) {
					if ( empty( $target['ID'] ) ) {
						continue;
					}

					$target_id = (int) $target['ID'];

					if ( $target_id === $current_id ) {
						continue;
					}

					$target_meta   = isset( $target['meta_value'] ) ? $target['meta_value'] : array();
					$target_amount = isset( $target_meta['amount'] ) ? (float) $target_meta['amount'] : 0.0;

					$target_membership_detais = $this->membership_service->prepare_single_membership_data( $this->membership_repository->get_single_membership_by_ID( $target_id ) );
					$target_label             = $target_membership_detais['post_title'];

					if ( $target_amount <= $current_amount ) {
						continue;
					}

					$chargeable = $this->calculate_chargeable_amount(
						$target_amount,
						$current_amount,
						$upgrade_type
					);

					$options[] = array(
						'membership_id'     => $target_id,
						'label'             => $target_label,
						'chargeable_amount' => (float) $chargeable,
						'target_amount'     => (float) $target_amount,
						'current_amount'    => (float) $current_amount,
					);
				}

				usort(
					$options,
					function ( $a, $b ) {
						if ( $a['chargeable_amount'] < $b['chargeable_amount'] ) {
							return -1;
						}
						if ( $a['chargeable_amount'] > $b['chargeable_amount'] ) {
							return 1;
						}
						if ( $a['target_amount'] < $b['target_amount'] ) {
							return -1;
						}
						if ( $a['target_amount'] > $b['target_amount'] ) {
							return 1;
						}

						return (int) $a['membership_id'] <=> (int) $b['membership_id'];
					}
				);

				$paths[ $current_id ] = $options;
			}

			uasort(
				$paths,
				function ( $a, $b ) {
					$count_a = is_array( $a ) ? count( $a ) : 0;
					$count_b = is_array( $b ) ? count( $b ) : 0;

					if ( $count_a < $count_b ) {
						return 1;
					}
					if ( $count_a > $count_b ) {
						return -1;
					}

					return 0;
				}
			);

			return $paths;
		}

		return array();
	}

	/**
	 * Build upgrade path html for user.
	 *
	 * @param array $upgrade_paths Upgrade Paths for the memberships inside the group.
	 */
	public function build_upgrade_paths( $upgrade_paths ) {
		$built_upgrade_paths = array();

		foreach ( $upgrade_paths as $membership_id => $path ) {

			$membership_details = $this->membership_service->prepare_single_membership_data(
				$this->membership_repository->get_single_membership_by_ID( $membership_id )
			);

			$current_label = $membership_details['post_title'];
			$paths_label   = array( $current_label );
			$paths_label   = array_map(
				function ( $single_path ) {
					return isset( $single_path['label'] ) ? $single_path['label'] : null;
				},
				$path
			);

			if ( ! empty( $paths_label ) ) {

				array_unshift( $paths_label, $current_label );
				$paths_label = array_filter( $paths_label );

				$built_upgrade_paths[] = sprintf(
					'Upgrade paths for %s: %s',
					$current_label,
					print_r( implode( ' -> ', $paths_label ), true )
				);
			}
		}

		ob_start();
		if ( ! empty( $built_upgrade_paths ) ) {
			?>
			<div class="ur-p-tag">
				Automatic upgrade paths setup for memberships in this group:
				<ul>
					<?php
					foreach ( $built_upgrade_paths as $paths ) {
						?>
						<li><?php echo esc_html( $paths ); ?></li>
						<?php
					}
					?>
				</ul>
			</div>
			<?php
		}
		return ob_get_clean();
	}
}
