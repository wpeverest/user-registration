<?php
/**
 * Admin View: Page - System info
 *
 * @since x.x.x
 */

defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WP_Debug_Data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-debug-data.php';
}
?>
<div class="user-registration-system-info-setting">
	<div class="user-registration-settings-header">
	<div class="user-registration-options-header--top__left">
	<span class="user-registration-options-header--top__left--icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
	<path d="M1.667 4.167a2.5 2.5 0 0 1 2.5-2.5h11.667a2.5 2.5 0 0 1 2.5 2.5v11.666a2.5 2.5 0 0 1-2.5 2.5H4.167a2.5 2.5 0 0 1-2.5-2.5V4.167Zm2.5-.834a.833.833 0 0 0-.833.834v11.666c0 .46.373.834.833.834h11.667c.46 0 .833-.373.833-.834V4.167a.833.833 0 0 0-.833-.834H4.167Z" clip-rule="evenodd"></path>
	<g clip-path="url(#a)" clip-rule="evenodd">
	<path d="M6.109 10.486c.268 0 .486.218.486.486v3.403a.486.486 0 0 1-.972 0v-3.403c0-.268.217-.486.486-.486Zm0-5.347c.268 0 .486.218.486.486v3.403a.486.486 0 0 1-.972 0V5.625c0-.268.217-.486.486-.486ZM10 9.514c.268 0 .486.218.486.486v4.375a.486.486 0 1 1-.972 0V10c0-.268.217-.486.486-.486Zm0-4.375c.268 0 .486.218.486.486v2.43a.486.486 0 1 1-.972 0v-2.43c0-.268.217-.486.486-.486Zm3.89 6.319c.269 0 .486.218.486.486v2.431a.486.486 0 1 1-.972 0v-2.43c0-.269.218-.487.486-.487Zm0-6.319c.269 0 .486.218.486.486V10a.486.486 0 1 1-.972 0V5.625c0-.268.218-.486.486-.486Z"></path>
	<path d="M4.654 10.972c0-.268.218-.486.486-.486h1.945a.486.486 0 1 1 0 .972H5.14a.486.486 0 0 1-.486-.486ZM8.54 8.056c0-.269.217-.487.486-.487h1.944a.486.486 0 1 1 0 .973H9.025a.486.486 0 0 1-.486-.486Zm3.89 3.888c0-.268.218-.486.486-.486h1.945a.486.486 0 1 1 0 .973h-1.945a.486.486 0 0 1-.486-.486Z"></path>
	</g>
	<defs>
	<clipPath id="a">
		<path d="M4.167 4.167h11.667v11.667H4.167z"></path>
	</clipPath>
	</defs>
</svg>
</span>
<h3><?php esc_html_e( 'System Info', 'user-registration' ); ?></h3>
	</div>
</div>
<button class="user-registration-system-info-setting-copy"><?php esc_html_e( 'Copy Setting', 'user-registration' ); ?></button>
	<table>
	<?php
	$license_key = get_option( 'user-registration_license_key' );
	if ( is_plugin_active( 'user-registration-pro/user-registration-pro.php' ) ) {
		?>
		<tr>
			<th colspan="2">
			<?php
			$license_data = get_transient( 'ur_pro_license_plan' );
			if ( $license_key && $license_data ) {
				$name = isset( $license_data->item_name ) ? esc_html( $license_data->item_name ) : '-';
			} else {
				$name = esc_html__( 'User Registration & Membership PRO', 'user-registration' );
			}
			echo esc_html( $name );
			?>
		</th>

		</tr>
		<tr>
			<th><?php esc_html_e( 'Version', 'user-registration' ); ?></th>
			<td>
			<?php
				$plugin_file =
					WP_PLUGIN_DIR . '/user-registration-pro/user-registration-pro.php';
			if ( file_exists( $plugin_file ) ) {
				$plugin_data = get_plugin_data( $plugin_file, array( 'Version' => 'Version' ) );
				if ( ! empty( $plugin_data['Version'] ) ) {
					$plugin_version = $plugin_data['Version'];
					echo esc_html( $plugin_version ) . ' ';
				}
			} else {
				$plugin_version = null;
			}
			?>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Edition', 'user-registration' ); ?></th>
			<td>
			<?php
				$license_data = get_transient( 'ur_pro_license_plan' );

			if ( $license_key && $license_data ) {
				$edition = isset( $license_data->item_plan ) ? esc_html__( 'PRO', 'user-registration' ) : '-';
				echo esc_html( $edition );
			} else {
				echo esc_html__( 'Free', 'user-registration' );
			}
			?>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'License Key', 'user-registration' ); ?></th>
			<td>
			<?php
				$license_data = get_transient( 'ur_pro_license_plan' );

			if ( $license_key && $license_data ) {
				$license_key = isset( $license_data->license ) ? esc_html__( 'Licensed', 'user-registration' ) : '-';
				echo esc_html( $license_key );
			} else {
				echo esc_html__( 'Unlicensed', 'user-registration' );
			}
			?>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'License Activated', 'user-registration' ); ?></th>
			<td>
			<?php
				$license_data = get_transient( 'ur_pro_license_plan' );

			if ( $license_key && $license_data ) {
				$license_status = isset( $license_data->success ) ? esc_html__( 'Yes', 'user-registration' ) : '-';
				echo esc_html( $license_status );
			} else {
				echo esc_html__( 'No', 'user-registration' );
			}
			?>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'License Expires', 'user-registration' ); ?></th>
			<td>
			<?php
				$license_data = get_transient( 'ur_pro_license_plan' );
			if ( $license_key && $license_data ) {
				$expires = isset( $license_data->expires ) ? esc_html( $license_data->expires ) : '-';
				echo esc_html( $expires );
			} else {
				echo esc_html__( '-', 'user-registration' );
			}
			?>
			</td>
		</tr>
		<?php
	} elseif ( is_plugin_active( 'user-registration/user-registration.php' ) ) {
		?>
		<tr>
			<th colspan="2">
			<?php
				$plugin_name = esc_html__( 'User Registration', 'user-registration' );
				echo esc_html( $plugin_name );
			?>
			</th>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Version', 'user-registration' ); ?></th>
			<td>
			<?php
				$plugin_file = WP_PLUGIN_DIR . '/user-registration/user-registration.php';
			if ( file_exists( $plugin_file ) ) {
				$plugin_data = get_plugin_data( $plugin_file, array( 'Version' => 'Version' ) );
				if ( ! empty( $plugin_data['Version'] ) ) {
					$plugin_version = $plugin_data['Version'];
					echo esc_html( $plugin_version );
				}
			} else {
				$plugin_version = null;
			}
			?>
			</td>
		</tr>
		<?php
	}
	?>
		<!-- WordPress -->
		<tr>
			<th colspan="2">
			<?php
			esc_html_e( 'WordPress', 'user-registration' );
			?>
			</th>
		</tr>
		<tr>
		<th>
		<?php
			$require_wp     = get_plugin_data( $plugin_file, array( 'RequiresWP' => 'Requires WP' ) );
			$min_version_wp = $require_wp['RequiresWP'];
			esc_html_e( 'Version', 'user-registration' );
		if ( is_plugin_active( 'user-registration-pro/user-registration-pro.php' ) ) {
			echo esc_html( '(Min:' . $min_version_wp . ')' );
		} elseif ( is_plugin_active( 'user-registration/user-registration.php' ) ) {
			echo ' ';
		}
		?>
		</th>
		<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'MultiSite Enabled', 'user-registration' ); ?></th>
			<td><?php echo esc_html( is_multisite() ? 'Yes' : 'No' ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Home URL', 'user-registration' ); ?></th>
			<td><?php echo esc_html( home_url() ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Site URL', 'user-registration' ); ?></th>
			<td><?php echo esc_html( site_url() ); ?></td>
		</tr>
		<tr>
		<th><?php esc_html_e( 'Theme', 'user-registration' ); ?></th>
		<td>
		<?php
			$theme = wp_get_theme();
			echo isset( $theme->name ) && isset( $theme->version ) ? esc_html( $theme->name ) . ' (' . esc_html( $theme->version ) . ')' : '';
		?>
		</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Plugins', 'user-registration' ); ?></th>
			<td>
			<?php
				$all_plugins    = get_plugins();
				$active_plugins = get_option( 'active_plugins', array() );

			foreach ( $active_plugins as $plugin_file ) {
				if ( isset( $all_plugins[ $plugin_file ] ) ) {
					$plugin_data = $all_plugins[ $plugin_file ];
					echo esc_html( $plugin_data['Name'] . ' (' . $plugin_data['Version'] . ')' ) . '<br>';
				}
			}
			?>
			</td>
		</tr>
		<tr class="ur-general-settings-hide">
		<th><?php esc_html_e( 'User Registration Global Settings ', 'user-registration' ); ?></th>
		<td>
			<?php
			$global_settings = array();
			$settings        = ur_setting_keys();
			$send_all        = false;
			$send_default    = false;

			foreach ( $settings as $product => $product_settings ) {
				foreach ( $product_settings as $setting_array ) {
					$setting_key     = $setting_array[0];
					$setting_default = $setting_array[1];
					$value           = get_option( $setting_key, 'NOT_SET' );

					// Set boolean values for certain settings.
					if ( isset( $setting_array[2] ) && 'NOT_SET' !== $value && $setting_default !== $value ) {
						$value = 1;
					}

					if ( 'NOT_SET' !== $value || $send_all ) {
						$setting_content = array(
						'value' => $value //phpcs:ignore
						);

						if ( $send_default ) {
							$setting_content['default'] = $setting_default;
						}

						$global_settings[ $product ][ $setting_key ] = $setting_content;
					}
				}
			}
			echo wp_json_encode( $global_settings );
			?>
		</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Max Upload Size', 'user-registration' ); ?></th>
			<td>
			<?php
				$max_upload_size_bytes = wp_max_upload_size();
				$max_upload_size_mb    = $max_upload_size_bytes / 1024 / 1024;
				echo esc_html( $max_upload_size_mb ) . ' MB';
			?>
			</td>
		</tr>
		<!-- PHP -->
		<tr>
			<th colspan="2"><?php esc_html_e( 'PHP', 'user-registration' ); ?></th>
		</tr>
		<tr>
			<th>
			<?php
			$plugin_data     = get_plugin_data( $plugin_file, array( 'RequiresPHP' => 'Requires PHP' ) );
			$min_version_php = $plugin_data['RequiresPHP'];
			esc_html_e( 'Version', 'user-registration' );
			if ( is_plugin_active( 'user-registration-pro/user-registration-pro.php' ) ) {
				echo esc_html( '(Min:' . $min_version_php . ')' );
			} elseif ( is_plugin_active( 'user-registration/user-registration.php' ) ) {
				echo ' ';
			}
			?>
			</th>
			<td><?php echo esc_html( phpversion() ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Default Timezone', 'user-registration' ); ?></th>
			<td><?php echo esc_html( date_default_timezone_get() ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Max Execution Time', 'user-registration' ); ?></th>
			<td><?php echo esc_html( ini_get( 'max_execution_time' ) ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Memory Limit', 'user-registration' ); ?></th>
			<td><?php echo esc_html( ini_get( 'memory_limit' ) ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Max Upload Size', 'user-registration' ); ?></th>
			<td><?php echo esc_html( ini_get( 'upload_max_filesize' ) ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Max Input Variables', 'user-registration' ); ?></th>
			<td><?php echo esc_html( ini_get( 'max_input_vars' ) ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'SMTP Hostname', 'user-registration' ); ?></th>
			<td><?php echo esc_html( ini_get( 'SMTP' ) ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'SMTP Port', 'user-registration' ); ?></th>
			<td><?php echo esc_html( ini_get( 'smtp_port' ) ); ?></td>
		</tr>
		<!-- Web Server -->
		<tr>
			<th colspan="2"><?php esc_html_e( 'Web Server', 'user-registration' ); ?></th>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Name', 'user-registration' ); ?></th>
			<td>
				<?php
				$remote_addr = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';
				echo esc_html( $remote_addr );
				?>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'IP', 'user-registration' ); ?></th>
			<td>
				<?php
				$remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
				echo esc_html( $remote_addr );
				?>
			</td>
		</tr>
		<!-- MySQL -->
		<tr>
			<th colspan="2"><?php esc_html_e( 'MySQL', 'user-registration' ); ?></th>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Version', 'user-registration' ); ?></th>
			<td>
			<?php
				global $wpdb;
				echo esc_html( $wpdb->db_version() );
			?>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Max Allowed Packet', 'user-registration' ); ?></th>
			<td>
				<?php
						$max_packet_size_bytes = array(
							'label' => __( 'Max allowed packet size', 'user-registration' ),
							'value' => WP_Debug_Data::get_mysql_var( 'max_allowed_packet' ),
						);

						$info['wp-database']['fields']['max_allowed_packet'] = $max_packet_size_bytes;

						$maxp_mb = isset( $max_packet_size_bytes['value'] ) ? $max_packet_size_bytes['value'] / 1024 / 1024 : '';
						echo esc_html( $maxp_mb ) . ' MB';

						?>
			</td>
		</tr>
	</table>
</div>
