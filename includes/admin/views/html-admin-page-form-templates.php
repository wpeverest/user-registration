<?php
/**
 * Admin View: Form Templates Selector
 *
 * @package UserRegistration/Admin/FormTemplates
 *
 * @var string $view
 * @var object $templates
 */

defined( 'ABSPATH' ) || exit;

$refresh_url     = add_query_arg(
	array(
		'page'               => 'add-new-registration&create-form=1',
		'action'             => 'ur-template-refresh',
		'ur-template-nonce' => wp_create_nonce( 'refresh' ),
	),
	admin_url( 'admin.php' )
);
$license_plan    = ur_get_license_plan();

?>
<div class ="wrap user-registration">
	<div class="user-registration-loader-overlay" style="display:none">
		<div class="ur-loading ur-loading-active"></div>
	</div>
	<div class="user-registration-setup user-registration-setup--form">
		<div class="user-registration-setup-header">
			<div class="user-registration-logo">
				<svg xmlns="http://www.w3.org/2000/svg" height="32" width="32" viewBox="0 0 24 24"><path fill="#7e3bd0" d="M21.23,10H17.79L16.62,8h3.46ZM17.77,4l1.15,2H15.48L14.31,4Zm-15,16L12,4l5.77,10H10.85L12,12h2.31L12,8,6.23,18H20.08l1.16,2Z"/></svg>
			</div>
			<h4><?php esc_html_e( 'Add New Form', 'user-registration' ); ?></h4>
			<?php if ( apply_filters( 'user_registration_refresh_templates', true ) ) : ?>
				<a href="<?php echo esc_url( $refresh_url ); ?>" class="user-registration-btn page-title-action"><?php esc_html_e( 'Refresh Templates', 'user-registration' ); ?></a>
			<?php endif; ?>
			<nav class="user-registration-tab">
				<ul>
					<li class="user-registration-tab-nav active">
						<a href="#" id="ur-form-all" class="user-registration-tab-nav-link"><?php esc_html_e( 'All', 'user-registration' ); ?></a>
					</li>
					<li class="user-registration-tab-nav">
						<a href="#" id="ur-form-basic" class="user-registration-tab-nav-link"><?php esc_html_e( 'Free', 'user-registration' ); ?></a>
					</li>
					<li class="user-registration-tab-nav">
						<a href="#" id="ur-form-pro" class="user-registration-tab-nav-link"><?php esc_html_e( 'Premium', 'user-registration' ); ?></a>
					</li>
				</ul>
			</nav>
		</div>
		<?php
		if ( 'false' === filter_input( INPUT_GET, 'ur-templates-fetch' ) ) {
			echo '<div id="message" class="notice notice-warning is-dismissible"><p>' . esc_html__( 'Couldn\'t connect to templates server. Please reload again.', 'user-registration' ) . '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">x</span></button></div>';
		}
		?>
		<div class="user-registration-form-template ur-setup-templates" data-license-type="<?php echo esc_attr( $license_plan ); ?>">
			<?php
			if ( empty( $templates ) ) {
				echo '<div id="message" class="error"><p>' . esc_html__( 'Something went wrong. Please refresh your templates.', 'user-registration' ) . '</p></div>';
			} else {
				foreach ( $templates as $template ) :
					$badge         = '';
					$upgrade_class = 'ur-template-select';
					$preview_link  = isset( $template->preview_link ) ? $template->preview_link : '';
					$click_class   = '';
					if ( ! in_array( 'free', $template->plan, true ) ) {
						if ( in_array( 'personal', $template->plan, true ) ) {
							$badge_text = esc_html( 'Personal' );
						} elseif ( in_array( 'plus', $template->plan, true ) ) {
							$badge_text = esc_html( 'Plus' );
						} elseif ( in_array( 'professional', $template->plan, true ) ) {
							$badge_text = esc_html( 'Professional' );
						}
						$badge = '<span class="user-registration-badge user-registration-badge--success">' . $badge_text . '</span>';
					}

					if ( 'blank' === $template->slug ) {
						$click_class = 'ur-template-select';
					}

					// Upgrade checks.
					if ( empty( $license_plan ) && ! in_array( 'free', $template->plan, true ) ) {
						$upgrade_class = 'upgrade-modal';
					} elseif ( ! in_array( str_replace( '-lifetime', '', $license_plan ), $template->plan, true ) && ! in_array( 'free', $template->plan, true ) ) {
						$upgrade_class = 'upgrade-modal';
					}

					/* translators: %s: Template title */
					$template_name = sprintf( esc_attr_x( '%s template', 'Template name', 'user-registration' ), esc_attr( $template->title ) );
					?>
					<div class="user-registration-template-wrap ur-template"  id="user-registration-template-<?php echo esc_attr( $template->slug ); ?>">
						<figure class="user-registration-screenshot <?php echo esc_attr( $click_class ); ?>" data-template-name-raw="<?php echo esc_attr( $template->title ); ?>" data-template="<?php echo esc_attr( $template->slug ); ?>" data-template-name="<?php echo esc_attr( $template_name ); ?>">
							<img src="<?php echo esc_url( evf()->plugin_url() . '/assets/' . $template->image ); ?>"/>
							<?php echo wp_kses_post( $badge ); ?>
							<?php if ( 'blank' !== $template->slug ) : ?>
								<div class="form-action">
									<a href="#" class="user-registration-btn user-registration-btn-primary <?php echo esc_attr( $upgrade_class ); ?>" data-licence-plan="<?php echo esc_attr( $license_plan ); ?>" data-template-name-raw="<?php echo esc_attr( $template->title ); ?>" data-template-name="<?php echo esc_attr( $template_name ); ?>" data-template="<?php echo esc_attr( $template->slug ); ?>"><?php esc_html_e( 'Get Started', 'user-registration' ); ?></a>
									<a href="<?php echo esc_url( $preview_link ); ?>" target="_blank" class="user-registration-btn user-registration-btn-secondary ur-template-preview"><?php esc_html_e( 'Preview', 'user-registration' ); ?></a>
								</div>
							<?php endif; ?>
						</figure>
						<div class="user-registration-form-id-container">
							<a class="user-registration-template-name <?php echo esc_attr( $upgrade_class ); ?>" href="#" data-licence-plan="<?php echo esc_attr( $license_plan ); ?>" data-template-name-raw="<?php echo esc_attr( $template->title ); ?>" data-template="<?php echo esc_attr( $template->slug ); ?>" data-template-name="<?php echo esc_attr( $template_name ); ?>"><?php echo esc_html( $template->title ); ?></a>
						</div>
					</div>
					<?php
				endforeach;
			}
			?>
		</div>
	</div>
</div>
