<?php
/**
 * Form Preview.
 *
 * @package UserRegistration\Classes
 * @version 1.6.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle frontend forms.
 *
 * @class       UR_Preview
 * @version     1.0.0
 * @package     UserRegistration/Classes/
 */
class UR_Preview {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Init hook function.
	 */
	public function init() {
		if ( is_user_logged_in() && ! is_admin() ) {
			if ( isset( $_GET['ur_preview'] ) ) {
				add_filter( 'edit_post_link', array( $this, 'edit_form_link' ) );
				add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
				add_filter( 'home_template_hierarchy', array( $this, 'template_include' ) );
				add_filter( 'frontpage_template_hierarchy', array( $this, 'template_include' ) );
				add_action( 'template_redirect', array( $this, 'handle_preview' ) );
				add_filter( 'astra_remove_entry_header_content', '__return_true' ); // Need to remove in next version, If astra release the patches.

			} elseif ( isset( $_GET['ur_login_preview'] ) ) {
				add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
				add_action( 'template_redirect', array( $this, 'handle_login_preview' ) );
				add_filter( 'home_template_hierarchy', array( $this, 'template_include' ) );
				add_filter( 'frontpage_template_hierarchy', array( $this, 'template_include' ) );
				add_filter( 'astra_remove_entry_header_content', '__return_true' ); // Need to remove in next version, If astra release the patches.

			} elseif ( isset( $_GET['ur_email_preview'] ) ) {
				add_filter( 'template_include', array( $this, 'handle_email_preview' ), PHP_INT_MAX );
				add_filter( 'astra_remove_entry_header_content', '__return_true' ); // Need to remove in next version, If astra release the patches.
			}
		}
	}

	/**
	 * Change edit link of preview page.
	 *
	 * @param string $link Link.
	 */
	public function edit_form_link( $link ) {
		$form_id       = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
		$edit_form_url = add_query_arg(
			array(
				'page'              => 'add-new-registration',
				'edit-registration' => $form_id,
			),
			admin_url( 'admin.php' )
		);

		$link = '<a class="post-edit-link" href="' . esc_url( $edit_form_url ) . '">' . __( 'Edit Form', 'user-registration' ) . '</a>';
		return $link;
	}

	/**
	 * Hook into pre_get_posts to limit posts.
	 *
	 * @param WP_Query $q Query instance.
	 */
	public function pre_get_posts( $q ) {
		// Limit one post to query.
		if ( $q->is_main_query() ) {
			$q->set( 'posts_per_page', 1 );
		}
	}

	/**
	 * A list of template candidates.
	 *
	 * @param array $templates A list of template candidates, in descending order of priority.
	 */
	public function template_include( $templates ) {
		return array( 'page.php', 'single.php', 'index.php' );
	}

	/**
	 * Handles the preview of form.
	 */
	public function handle_preview() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( isset( $_GET['form_id'] ) ) {
			add_filter( 'the_title', array( $this, 'form_preview_title' ) );
			add_filter( 'the_content', array( $this, 'form_preview_content' ) );
			add_filter( 'get_the_excerpt', array( $this, 'form_preview_content' ) );
			add_filter( 'post_thumbnail_html', '__return_empty_string' );
		}
	}

	/**
	 * Filter the title and insert form preview title.
	 *
	 * @param  string $title Existing title.
	 * @return string
	 */
	public static function form_preview_title( $title ) {
		$form_id   = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0 ; // @codingStandardsIgnoreLine

		if ( $form_id && in_the_loop() ) {
			$form_data = UR()->form->get_form( $form_id );

			if ( ! empty( $form_data ) ) {
				/* translators: %s - Form name. */
				return sprintf( esc_html__( '%s &ndash; Preview', 'user-registration' ), sanitize_text_field( $form_data->post_title ) );
			}
		}

		return $title;
	}

	/**
	 * Displays content of form preview.
	 *
	 * @param string $content Page/Post content.
	 * @return string
	 */
	public function form_preview_content( $content ) {
		$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;

		remove_filter( 'the_content', array( $this, 'form_preview_content' ) );
		if ( function_exists( 'apply_shortcodes' ) ) {
			$content = apply_shortcodes( '[user_registration_form id="' . $form_id . '"]' );
		} else {
			$content = do_shortcode( '[user_registration_form id="' . $form_id . '"]' );
		}

		return $content;
	}

	/**
	 * Handles the preview of login form.
	 */
	public function handle_login_preview() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		add_filter( 'the_title', array( $this, 'login_form_preview_title' ) );
		add_filter( 'the_content', array( $this, 'login_form_preview_content' ) );
	}

	/**
	 * Filter the title and insert form preview title.
	 *
	 * @param  string $title Existing title.
	 * @return string
	 */
	public static function login_form_preview_title( $title ) {
		if ( in_the_loop() ) {
			/* translators: %s - Form name. */
			return sprintf( esc_html__( '%s &ndash; Preview', 'user-registration' ), sanitize_text_field( 'Login Form' ) );
		}

		return $title;
	}

	/**
	 * Displays content of login form preview.
	 *
	 * @param string $content Page/Post content.
	 * @return string
	 */
	public function login_form_preview_content( $content ) {
		remove_filter( 'the_content', array( $this, 'form_preview_content' ) );

		wp_enqueue_script( 'ur-my-account' );
		$recaptcha_enabled = ur_option_checked( 'user_registration_login_options_enable_recaptcha', false );
		$recaptcha_node    = ur_get_recaptcha_node( 'login', $recaptcha_enabled );

		ob_start();
		echo '<div id="user-registration">';
		ur_get_template(
			'myaccount/form-login.php',
			array(
				'recaptcha_node' => $recaptcha_node,
				'redirect'       => '',
			)
		);
		echo '</div>';
		return ob_get_clean();
	}

	/**
	 * Displays content of email preview.
	 *
	 * @param string $content Page/Post content.
	 * @return string
	 */
	public function handle_email_preview() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$option_name    = isset( $_GET['ur_email_preview'] ) ? sanitize_text_field( $_GET['ur_email_preview'] ) : '';
		$email_template = isset( $_GET['ur_email_template'] ) ? sanitize_text_field( $_GET['ur_email_template'] ) : '';

		$class_name = 'UR_Settings_' . str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $option_name ) ) );

		$emails = apply_filters( 'user_registration_email_classes', array() );

		if ( isset( $emails[ $class_name ] ) && ! class_exists( $class_name ) ) {
			$class_name = get_class( $emails[ $class_name ] );
		}

		if ( ! class_exists( $class_name ) ) {
			echo '<h3>' . esc_html_e( 'Something went wrong. Please verify if the email you want to preview exists or addon it is associated with is activated.' ) . '</h3>';
		} else {
			$class_instance  = new $class_name();
			$default_content = 'ur_get_' . $option_name;

			if ( ! method_exists( $class_instance, $default_content ) ) {
				$default_content = 'user_registration_get_' . $option_name;
			}

			$email_content = get_option( 'user_registration_' . $option_name, $class_instance->$default_content() );
			$email_content = apply_filters( 'user_registration_process_smart_tags', $email_content );

			ur_get_template(
				'email-preview.php',
				array(
					'email_content'  => $email_content,
					'email_template' => $email_template,
				)
			);
		}

	}
}

new UR_Preview();
