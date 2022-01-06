<?php
/**
 * Admin View: Page - Addons
 *
 * @var string $view
 * @var object $addons
 * @package UserRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="wrap ur_addons_wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'User Registration Extensions', 'user-registration' ); ?></h1>

	<?php if ( apply_filters( 'user_registration_refresh_addons', true ) ) : ?>
		<a href="<?php echo esc_url( $refresh_url ); ?>" class="page-title-action"><?php esc_html_e( 'Refresh Extensions', 'user-registration' ); ?></a>
	<?php endif; ?>

	<hr class="wp-header-end">
	<h2 class="screen-reader-text"><?php esc_html_e( 'Filter extensions list', 'user-registration' ); ?></h2>

	<?php if ( $sections ) : ?>
		<ul class="subsubsub">
			<?php foreach ( $sections as $section_id => $section ) : ?>
				<li><a class="<?php echo $current_section === $section_id ? 'current' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration-addons&section=' . esc_attr( $section_id ) ) ); ?>"><?php echo esc_html( $section->title ); ?></a><?php echo ( end( $section_keys ) !== $section_id ) ? : ''; ?></li>
			<?php endforeach; ?>
		</ul>
		<br class="clear" />
		<?php $addons = UR_Admin_Addons::get_section_data( $current_section ); ?>
			<?php if ( $addons ) : ?>
			<ul class="products">
				<?php foreach ( $addons as $addon ) : ?>
				<li class="product">
					<a href="<?php echo esc_attr( $addon->link ); ?>">
						<h2><?php echo esc_html( $addon->title ); ?></h2>
						<?php if ( ! empty( $addon->image ) ) : ?>
							<span class="product-image"><img src="<?php echo esc_url( UR()->plugin_url() . '/assets/' . $addon->image ); ?>"/></span>
						<?php endif; ?>
						<span class="price"><?php echo isset( $addon->price ) ? wp_kses_post( $addon->price ) : ''; ?></span>
						<p><?php echo wp_kses_post( $addon->excerpt ); ?></p>
					</a>
				</li>
			<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	<?php else : ?>
		<p>
			<?php
				/* translators: %s - User Registration Extensions Catalog */
				printf( esc_html__( 'Our catalog of User Registration Extensions can be found on WPEverest.com here: <a href="%s">User Registration Extensions Catalog</a>', 'user-registration' ), 'https://wpeverest.com/user-registration-extensions/' );
			?>
		</p>
	<?php endif; ?>
</div>
