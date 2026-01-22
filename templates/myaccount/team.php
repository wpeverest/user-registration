<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/team.php.
 *
 * HOWEVER, on occasion UserRegistration will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.wpuserregistration.com/docs/how-to-edit-user-registration-template-files-such-as-login-form/
 * @package UserRegistration/Templates
 * @version 1.0.0
 */

use WPEverest\URTeamMembership\Admin\TeamRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( empty( $teams ) || empty( $teams['items'] ) ) {
	echo esc_html_e( 'You do not have any team records', 'user-registration' );
	return;
}

$current     = intval( $teams['page'] ?? 1 );
$total_pages = intval( $teams['total_pages'] ?? 1 );


// If only one team, display the edit page directly
if ( 1 === $teams['total_items'] ) {
	$team            = $teams['items'][0];
	$team_repository = new TeamRepository();
	$team            = $team_repository->get_single_team_by_ID( $team['ID'] );
	$current_user_id = get_current_user_id();

	if ( $team && isset( $team['meta']['urm_team_leader_id'] ) && intval( $team['meta']['urm_team_leader_id'] ) === $current_user_id ) {

		ur_get_template(
			'myaccount/edit-team.php',
			array(
				'team' => $team,
			)
		);
		return;
	}
}
?>

<div class="user-registration-MyAccount-content__body">
	<div class="ur-account-table-container">
		<div class="ur-account-table-wrapper">
			<table class="ur-account-table">
				<thead class="ur-account-table__header">
					<tr class="ur-account-table__row">
						<th class="ur-account-table__cell ur-account-table__header-cell"><?php esc_html_e( 'Team Name', 'user-registration' ); ?></th>
						<th class="ur-account-table__cell ur-account-table__header-cell"><?php esc_html_e( 'Members', 'user-registration' ); ?></th>
						<th class="ur-account-table__cell ur-account-table__header-cell"><?php esc_html_e( 'Action', 'user-registration' ); ?></th>
					</tr>
				</thead>
				<tbody class="ur-account-table__body">
					<?php
					foreach ( $teams['items'] as $team ) :

						$current_url = get_permalink( get_option( 'user_registration_myaccount_page_id' ) ) . 'ur-membership/';
						?>
							<tr class="ur-account-table__row">
								<td class="ur-account-table__cell ur-account-table__cell--team-name"><?php echo isset( $team['post_title'] ) && ! empty( $team['post_title'] ) ? esc_html( $team['post_title'] ) : __( 'N/A', 'user-registration' ); ?></td>
								<td class="ur-account-table__cell ur-account-table__cell--team-members"><?php echo esc_html( $team['meta']['urm_used_seats'] ?? 1 ); ?></td>
								<td class="ur-account-table__cell ur-account-table__cell--action">
									<div class="team-row-btn-container">
										<a href="<?php echo esc_url( ur_get_account_endpoint_url( 'urm-team' ) . '?action=edit&team_id=' . $team['ID'] ); ?>" class="ur-account-action-link team-tab-btn edit-team-btn" data-id="<?php echo esc_attr( $team['ID'] ?? '' ); ?>">
											<?php esc_html_e( 'Edit', 'user-registration' ); ?>
										</a>
										|
										<a class="ur-account-action-link team-tab-btn delete-team-btn" data-id="<?php echo esc_attr( $team['ID'] ?? '' ); ?>">
											<?php esc_html_e( 'Delete', 'user-registration' ); ?>
										</a>
									</div>
								</td>
							</tr>
						<?php
					endforeach;
					?>
				</tbody>
			</table>
		</div>
		<?php
		if ( $total_pages > 1 ) :
			?>
			<div class="ur-pagination">
				<?php
				echo paginate_links(
					array(
						'base'      => trailingslashit( $current_url ) . '%_%',
						'format'    => 'page/%#%/',
						'current'   => $current,
						'total'     => $total_pages,
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
						'type'      => 'list',
					)
				);
				?>
				</div>

			<?php
		endif;
		?>
	</div>
</div>
