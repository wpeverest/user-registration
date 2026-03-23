<?php
/**
 * URM_FIXED_PERIOD_MEMBERSHIP_ADMIN setup
 *
 * @package URM_FIXED_PERIOD_MEMBERSHIP_ADMIN
 * @since  1.0.0
 */

namespace WPEverest\URM\FixedPeriodMemebership;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Admin' ) ) :

	/**
	 * Admin masteriyo integration Clas s
	 *
	 */
	class Admin {

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function init() {
			// If the single instance hasn't been set, set it now.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		private function __construct() {

			// do_action( 'ur_membership_team_membership', $membership, $membership_details );
			add_action( 'ur_membership_team_membership', array( $this, 'fixed_membership_settings' ), 10, 2 );
		}

		public function fixed_membership_settings( $membership, $membership_details ) {
			error_log( print_r( $membership_details, true ) );
			?>
		<div class="ur-membership-sync-to-email-marketing-addons <?php echo ! UR_PRO_ACTIVE ? 'upgradable-type' : ''; ?> ">
					<div class="ur-membership-selection-container ur-d-flex ur-mt-2 ur-align-items-center"
						style="gap:20px;">
						<div class="ur-label" style="width: 30%">
							<label class="ur-membership-enable-email-marketing-sync-action"
									for="ur-membership-email-marketing-sync-action"><?php esc_html_e( 'Membership Duration', 'user-registration' ); ?>
							</label>
						</div>
						<div class="ur-toggle-section m1-auto" style="width: 100%">
							<span class="user-registration-toggle-form">

						<?php
							$fixed_period            = isset( $membership_details['fixed_period'] ) ? $membership_details['fixed_period'] : array();
							$is_email_marketing_sync = ur_string_to_bool( isset( $fixed_period['is_enable'] ) ? $fixed_period['is_enable'] : '0' );
						?>
								<input
									data-key-name="Fixed period membership Action"
									id="ur-membership-email-marketing-sync-action" type="checkbox"
									class="user-registration-switch__control hide-show-check enabled"
									<?php echo ! UR_PRO_ACTIVE ? 'disabled' : ''; ?>
									name="ur_membership_fixed_period_action"
									style="width: 100%; text-align: left"
								<?php echo $is_email_marketing_sync ? esc_attr( 'checked' ) : ''; ?>
									>
								<span class="slider round"></span>
								<?php
								if ( ! UR_PRO_ACTIVE ) {
									ur_render_premium_feature_gate();
								}
								?>
							</span>
						</div>
						<div style="display:flex; flex-direction:column; gap:24px; width:100%">
							<div class="ur-toggle-section m1-auto" style="width: 100%">
						<span><?php echo esc_html__( 'Enable Expiration', 'user-registration' ); ?></span>
							</div>
						</div>
					</div>
					<div class="ur-membership-selection-container ur-d-flex ur-mt-2 ur-align-items-center" style="gap:20px;">
						<div class="ur-label" style="width: 30%">
						</div>
						<div class="ur-toggle-section m1-auto" style="width: 100%">
							test
						</div>
					</div>
				</div>
			<?php

			return;
		}
	}
endif;
