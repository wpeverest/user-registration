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
		'page'               => 'add-new-registration',
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
			<div class="ur-brand-logo ur-px-2">
				<img src="<?php echo esc_url( UR()->plugin_url() . '/assets/images/logo.svg' ); ?>" alt="">
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
						$upgrade_class = 'ur-template-select';
					}

					/* translators: %s: Template title */
					$template_name = sprintf( esc_attr_x( '%s template', 'Template name', 'user-registration' ), esc_attr( $template->title ) );
					?>
					<div class="user-registration-template-wrap ur-template"  id="user-registration-template-<?php echo esc_attr( $template->slug ); ?>">
						<figure class="user-registration-screenshot <?php echo esc_attr( $click_class ); ?>" data-template-name-raw="<?php echo esc_attr( $template->title ); ?>" data-template="<?php echo esc_attr( $template->slug ); ?>" data-template-name="<?php echo esc_attr( $template_name ); ?>">
							<img src="<?php echo esc_url( $template->image ); ?>"/>
							<?php echo wp_kses_post( $badge ); ?>
							<?php if ( 'blank' !== $template->slug ) : ?>
								<div class="form-action">
									<a href="#" class="user-registration-btn button-primary <?php echo esc_attr( $upgrade_class ); ?>" data-licence-plan="<?php echo esc_attr( $license_plan ); ?>" data-template-name-raw="<?php echo esc_attr( $template->title ); ?>" data-template-name="<?php echo esc_attr( $template_name ); ?>" data-template="<?php echo esc_attr( $template->slug ); ?>"><?php esc_html_e( 'Get Started', 'user-registration' ); ?></a>
									<a href="<?php echo esc_url( $preview_link ); ?>" target="_blank" class="user-registration-btn button-secondary ur-template-preview"><?php esc_html_e( 'Preview', 'user-registration' ); ?></a>
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


<?php
/**
 * Prints the JavaScript templates for install admin notices.
 *
 * Template takes one argument with four values:
 *
 *     param {object} data {
 *         Arguments for admin notice.
 *
 *         @type string id        ID of the notice.
 *         @type string className Class names for the notice.
 *         @type string message   The notice's message.
 *         @type string type      The type of update the notice is for. Either 'plugin' or 'theme'.
 *     }
 *
 * @since 1.6.0
 */
function user_registration_print_admin_notice_templates() {
	?>
	<script id="tmpl-wp-installs-admin-notice" type="text/html">
		<div <# if ( data.id ) { #>id="{{ data.id }}"<# } #> class="notice {{ data.className }}"><p>{{{ data.message }}}</p></div>
	</script>
	<script id="tmpl-wp-bulk-installs-admin-notice" type="text/html">
		<div id="{{ data.id }}" class="{{ data.className }} notice <# if ( data.errors ) { #>notice-error<# } else { #>notice-success<# } #>">
			<p>
				<# if ( data.successes ) { #>
					<# if ( 1 === data.successes ) { #>
						<# if ( 'plugin' === data.type ) { #>
							<?php
							/* translators: %s: Number of plugins */
							printf( esc_html__( '%s plugin successfully installed.', 'user-registration' ), '{{ data.successes }}' );
							?>
						<# } #>
					<# } else { #>
						<# if ( 'plugin' === data.type ) { #>
							<?php
							/* translators: %s: Number of plugins */
							printf( esc_html__( '%s plugins successfully installed.', 'user-registration' ), '{{ data.successes }}' );
							?>
						<# } #>
					<# } #>
				<# } #>
				<# if ( data.errors ) { #>
					<button class="button-link bulk-action-errors-collapsed" aria-expanded="false">
						<# if ( 1 === data.errors ) { #>
							<?php
							/* translators: %s: Number of failed installs */
							printf( esc_html__( '%s install failed.', 'user-registration' ), '{{ data.errors }}' );
							?>
						<# } else { #>
							<?php
							/* translators: %s: Number of failed installs */
							printf( esc_html__( '%s installs failed.', 'user-registration' ), '{{ data.errors }}' );
							?>
						<# } #>
						<span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'user-registration' ); ?></span>
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
				<# } #>
			</p>
			<# if ( data.errors ) { #>
				<ul class="bulk-action-errors hidden">
					<# _.each( data.errorMessages, function( errorMessage ) { #>
						<li>{{ errorMessage }}</li>
					<# } ); #>
				</ul>
			<# } #>
		</div>
	</script>
	<?php
}
user_registration_print_admin_notice_templates();
