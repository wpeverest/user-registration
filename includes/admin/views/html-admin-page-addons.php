<?php
/**
 * Admin View: Page - Addons
 *
 * @var string $view
 * @var object $addons
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="wrap ur_addons_wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'User Registration Extensions', 'user-registration'  ); ?></h1>
	<hr class="wp-header-end">
	<h2 class="screen-reader-text"><?php esc_html_e( 'Filter extensions list', 'user-registration' ); ?></h2>

	<?php if ( $sections ) : ?>
		<ul class="subsubsub">
			<?php foreach ( $sections as $section_id => $section ) : ?>
				<li><a class="<?php echo $current_section === $section_id ? 'current' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=user-registration-addons&section=' . esc_attr( $section_id ) ); ?>"><?php echo esc_html( $section->title ); ?></a><?php echo ( end( $section_keys ) !== $section_id ) ? : ''; ?></li>
			<?php endforeach; ?>
		</ul>
		<br class="clear" />
		<?php if ( $addons = UR_Admin_Addons::get_section_data( $current_section ) ) : ?>
			<div class="wp-list-table widefat extension-install">
					<h2 class="screen-reader-text"><?php esc_html_e( 'Extensions list', 'user-registration' ); ?></h2>

					<div class="the-list">
						<?php foreach ( $addons as $addon ) : ?>
							<div class="plugin-card plugin-card-<?php echo esc_attr( $addon->slug ); ?>">
								<div class="plugin-card-left">
									<a href="<?php echo esc_url( $addon->link ); ?>">
										<img src="<?php echo esc_url( $addon->image ); ?>" class="plugin-icon" alt="" />
									</a>
								</div>
								<div class="plugin-card-right">
									<div class="name column-name">
										<a href="<?php echo esc_url( $addon->link ); ?>">
											<h3 class="plugin-name">
												<?php echo esc_html( $addon->title ); ?>
											</h3>
										</a>
									</div>
									<div class="desc column-description">
										<p class="plugin-desc"><?php echo esc_html( $addon->excerpt ); ?></p>
									</div>
									<div class="plugin-card-buttons">
										<?php if( get_option( "user-registration_license_key" ) ) { ?>
												<?php echo do_action( 'user_registration_after_addons_description', $addon ); ?>
											<?php } else { ?>
												<div class="action-buttons upgrade-plan">
													<a class="button upgrade-now" href="https://wpeverest.com/wordpress-plugins/user-registration/pricing/?utm_source=addons-page&utm_medium=upgrade-button&utm_campaign=evf-upgrade-to-pro" target="_blank"><?php esc_html_e( 'Upgrade Plan', 'user-registration' ); ?></a>
												</div>
										<?php } ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
		<?php endif; ?>
	<?php else : ?>
		<p><?php printf( __( 'Our catalog of User Registration Extensions can be found on WPEverest.com here: <a href="%s">User Registration Extensions Catalog</a>', 'user-registration' ), 'https://wpeverest.com/user-registration-extensions/' ); ?></p>
	<?php endif; ?>
</div>
