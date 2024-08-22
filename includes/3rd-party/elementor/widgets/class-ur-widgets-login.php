<?php
/**
 * User Registration Form for Elementor.
 *
 * @package UserRegistration\Class
 * @version 3.0.5
 */

use Elementor\Plugin;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * User Registration Forms Widget for Elementor.
 *
 * @since 3.0.5
 */
class UR_Elementor_Widget_Login extends Widget_Base {
	/**
	 * Get widget name.
	 *
	 * Retrieve shortcode widget name.
	 *
	 * @since 3.0.5
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'user-registration-login';
	}
	/**
	 * Get widget title.
	 *
	 * Retrieve shortcode widget title.
	 *
	 * @since 3.0.5
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Login Form', 'user-registration' );
	}
	/**
	 * Get widget icon.
	 *
	 * Retrieve shortcode widget icon.
	 *
	 * @since 3.0.5
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'ur-icon-user-registration';
	}
	/**
	 * Get widget categories.
	 *
	 * @since 3.0.5
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {

		return array(
			'user-registration',
		);
	}
	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @since 3.0.5
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return array( 'form', 'forms', 'user-registration', 'login form', 'user-registration-login', 'userregistrations' );
	}
	/**
	 * Register controls.
	 *
	 * @since 3.0.5
	 */
	protected function register_controls() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		$this->start_controls_section(
			'section_content_layout',
			array(
				'label' => esc_html__( 'Login Form', 'user-registration' ),
			)
		);

		$forms = $this->get_forms();

		$this->add_control(
			'ur_login_form',
			array(
				'label'   => esc_html__( 'Select Form', 'user-registration' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $forms,
				'default' => 'ur_login_form',
			)
		);
		$this->end_controls_section();

		do_action( 'user_registration_elementor_login_style', $this );
	}
	/**
	 * Retrieve the shortcode.
	 *
	 * @since 3.0.5
	 */
	private function get_shortcode() {

		$settings = $this->get_settings_for_display();
		if ( ! $settings['ur_login_form'] ) {
			return '<p>' . __( 'Please select a User Registration Login Forms.', 'user-registration' ) . '</p>';
		}
		$shortcode = sprintf( '[user_registration_login]' );
		return $shortcode;
	}
	/**
	 * Render widget output.
	 *
	 * @since 3.0.5
	 */
	protected function render() {
		echo do_shortcode( $this->get_shortcode() );
	}
	/**
	 * Retrieve the  available UR forms.
	 *
	 * @since 3.0.5
	 */
	public function get_forms() {
		$user_registration_forms = array();

		if ( empty( $user_registration_forms ) ) {
			$user_registration_forms['ur_login_form'] = esc_html__( 'Login Form', 'user-registration' );
			return $user_registration_forms;
		}
	}
}
