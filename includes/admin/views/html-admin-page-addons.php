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
	<nav class="nav-tab-wrapper ur-nav-tab-wrapper">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=user-registration-addons' ) ); ?>" class="nav-tab nav-tab-active"><?php _e( 'Browse Extensions', 'user-registration' ); ?></a>
	</nav>

	<h1 class="screen-reader-text"><?php _e( 'User Registration Extensions', 'user-registration' ); ?></h1>

	<?php if ( $sections ) : ?>
		<ul class="subsubsub">
			<?php foreach ( $sections as $section_id => $section ) : ?>
				<li><a class="<?php echo $current_section === $section_id ? 'current' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=user-registration-addons&section=' . esc_attr( $section_id ) ); ?>"><?php echo esc_html( $section->title ); ?></a><?php echo ( end( $section_keys ) !== $section_id ) ? : ''; ?></li>
			<?php endforeach; ?>
		</ul>
		<br class="clear" />
		<?php if ( $addons = UR_Admin_Addons::get_section_data( $current_section ) ) : ?>
			<ul class="products">
			<?php foreach ( $addons as $addon ) : ?>
				<li class="product">
					<a href="<?php echo esc_attr( $addon->link ); ?>">
						<h2><?php echo esc_html( $addon->title ); ?></h2>
						<?php if ( ! empty( $addon->image ) ) : ?>
							<span class="product-image"><img src="<?php echo esc_attr( $addon->image ); ?>"/></span>
						<?php endif; ?>
						<span class="price"><?php echo isset( $addon->price ) ? wp_kses_post( $addon->price ) : ''; ?></span>
						<p><?php echo wp_kses_post( $addon->excerpt ); ?></p>
					</a>
				</li>
			<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	<?php else : ?>
		<p><?php printf( __( 'Our catalog of User Registration Extensions can be found on WPEverest.com here: <a href="%s">User Registration Extensions Catalog</a>', 'user-registration' ), 'https://wpeverest.com/user-registration-extensions/' ); ?></p>
	<?php endif; ?>
</div>
