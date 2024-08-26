<?php

/**
 * User Registration Edit Password Form for Elementor.
 *
 * @package UserRegistration\Class
 * @since 3.2.2
 */

use Elementor\Plugin;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * User Registration Edit Password Forms Widget for Elementor.
 */
class UR_Elementor_Widget_Edit_Password extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve shortcode widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'user-registration-edit-password';
	}
	/**
	 * Get widget title.
	 *
	 * Retrieve shortcode widget title.
	 *
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Edit Password', 'user-registration' );
	}
	/**
	 * Get widget icon.
	 *
	 * Retrieve shortcode widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'ur-icon-user-registration';
	}
	/**
	 * Get widget categories.
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
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return array( 'form', 'forms', 'user-registration', 'login form', 'user-registration-edit-password', 'userregistrations' );
	}
	/**
	 * Register controls.
	 */
	protected function register_controls() {
	 // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		$this->start_controls_section(
			'ur_elementor_edit_password',
			array(
				'label' => esc_html__( 'Edit Profile', 'user-registration' ),
			)
		);

		$forms = $this->get_forms();

		$this->add_control(
			'ur_edit_password',
			array(
				'label'   => esc_html__( 'Select Form', 'user-registration' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $forms,
				'default' => 'ur_edit_password',
			)
		);
		$this->end_controls_section();

		do_action( 'user_registration_elementor_edit_password_style', $this );
	}
	/**
	 * Retrieve the shortcode.
	 */
	private function get_shortcode() {

		$settings = $this->get_settings_for_display();
		if ( ! $settings['ur_edit_password'] ) {
			return '<p>' . __( 'Please select a Edit Password Form.', 'user-registration' ) . '</p>';
		}
		$shortcode = '[user_registration_edit_password]';
		$shortcode = sprintf( apply_filters( 'user_registration_elementor_shortcode_edit_password', $shortcode, $settings ) );
		return $shortcode;
	}
	/**
	 * Render widget output.
	 */
	protected function render() {
		echo do_shortcode( $this->get_shortcode() );
	}
	/**
	 * Retrieve the  available UR forms.
	 */
	public function get_forms() {
		$user_registration_forms = array();

		if ( empty( $user_registration_forms ) ) {
			$user_registration_forms['ur_edit_password'] = esc_html__( 'Default Form', 'user-registration' );
			return $user_registration_forms;
		}
	}
}
