<?php
/**
 * User Registration Form for Elementor.
 *
 * @package UserRegistration\Class
 * @since 3.2.2
 */

use Elementor\Plugin;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * User Registration Forms Widget for Elementor.
 */
class UR_Elementor_Widget_Popup extends Widget_Base {
	/**
	 * Get widget name.
	 *
	 * Retrieve shortcode widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'user-registration-popup';
	}
	/**
	 * Get widget title.
	 *
	 * Retrieve shortcode widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Popup Form', 'user-registration' );
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
		return array( 'form', 'forms', 'user-registration', 'login form', 'user-registration-login', 'userregistrations' );
	}
	/**
	 * Register controls.
	 */
	protected function register_controls() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		$this->start_controls_section(
			'section_content_layout',
			array(
				'label' => esc_html__( 'Popup', 'user-registration' ),
			)
		);

		$forms = $this->get_forms();

		$this->add_control(
			'ur_popup_form',
			array(
				'label'   => esc_html__( 'Select Popup', 'user-registration' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $forms,
			)
		);

		$this->end_controls_section();

		do_action( 'user_registration_elementor_popup_style', $this );

	}
	/**
	 * Retrieve the shortcode.
	 */
	private function get_shortcode() {

		$settings = $this->get_settings_for_display();
		if ( ! $settings['ur_popup_form'] ) {
			return '<p>' . __( 'Please select a User Registration Popup.', 'user-registration' ) . '</p>';
		}
		$attributes = array(
			'id' => $settings['ur_popup_form'],
		);
		$this->add_render_attribute( 'shortcode', $attributes );
		$shortcode   = array();
		$shortcode[] = sprintf( '[user_registration_popup %s]', $this->get_render_attribute_string( 'shortcode' ) );

		return implode( '', $shortcode );
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
			$ur_forms = ur_get_all_user_registration_pop();
			if ( ! empty( $ur_forms ) ) {

				foreach ( $ur_forms as $form_value => $form_name ) {
					$user_registration_forms[ $form_value ] = $form_name;
				}
			} else {
				$user_registration_forms[0] = esc_html__( 'You have not created a popup, Please Create a popup first', 'user-registration' );
			}
			return $user_registration_forms;
		}
	}
}
