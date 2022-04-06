<?php
/**
 * User Registration Form for Elementor.
 *
 * @package UserRegistration\Class
 * @version 2.1.6
 */

use Elementor\Plugin;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * User Registration Forms Widget for Elementor.
 *
 * @since 2.1.6
 */

class UR_Widget extends Widget_Base {
	/**
	 * Get widget name.
	 *
	 * Retrieve shortcode widget name.
	 *
	 * @since 2.1.6
	 *
	 * @return string Widget name.
	 */

	public function get_name() {
		return 'user-registration';
	}
	/**
	 * Get widget title.
	 *
	 * Retrieve shortcode widget title.
	 *
	 * @since 2.1.6
	 *
	 * @return string Widget title.
	 */

	public function get_title() {
		return __( 'User Registration', 'user-registration' );
	}
	/**
	 * Get widget icon.
	 *
	 * Retrieve shortcode widget icon.
	 *
	 * @since 2.1.6
	 *
	 * @return string Widget icon.
	 */

	public function get_icon() {
		return 'eicon-document-file';
	}
	/**
	 * Get widget categories.
	 *
	 * @since 2.1.6
	 *
	 * @return array Widget categories.
	 */

	public function get_categories() {

		if ( class_exists( 'User_Registration_Style_Customizer' ) ) {
			return array(
				'user-registration',
			);
		} else {
			return array(
				'basic',
			);
		}
	}
	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @since 2.1.6
	 *
	 * @return array Widget keywords.
	 */

	public function get_keywords() {
		return array( 'form', 'forms', 'user-registration', 'registration form', 'userregistration', 'userregistrations' );
	}
	/**
	 * Register controls.
	 *
	 * @since 2.1.6
	 */

	protected function register_controls() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		$this->start_controls_section(
			'section_content_layout',
			array(
				'label' => esc_html__( 'Form', 'user-registration' ),
			)
		);

		$forms = $this->get_forms();

		$this->add_control(
			'user_registration_form',
			array(
				'label'   => esc_html__( 'Select Form', 'user-registration' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $forms,
			)

		);

		$this->end_controls_section();

		do_action( 'user_registration_elementor_style', $this );

	}
	/**
	 * Retrieve the shortcode.
	 *
	 * @since 2.1.6
	 */

	private function get_shortcode() {

		$settings = $this->get_settings_for_display();
		if ( ! $settings['user_registration_form'] ) {
			return '<p>' . __( 'Please select a User Registration Forms.', 'user-registration' ) . '</p>';
		}

		$attributes = array(
			'id' => $settings['user_registration_form'],
		);

		$this->add_render_attribute( 'shortcode', $attributes );
		$shortcode   = array();
		$shortcode[] = sprintf( '[user_registration_form %s]', $this->get_render_attribute_string( 'shortcode' ) );

		return implode( '', $shortcode );

	}
	/**
	 * Render widget output.
	 *
	 * @since 2.1.6
	 */

	protected function render() {
		echo do_shortcode( $this->get_shortcode() );
	}
	/**
	 * Retrieve the  available UR forms.
	 *
	 * @since 2.1.6
	 */
	
	public function get_forms() {
		$user_registration_forms = array();

		if ( empty( $user_registration_forms ) ) {
			$ur_forms = ur_get_all_user_registration_form();
			if ( ! empty( $ur_forms) ) {

				foreach ( $ur_forms as $form_value => $form_name ) {
					$user_registration_forms[ $form_value ] = $form_name;
				}

			} else {
				$user_registration_forms[0] = esc_html__( 'Yo have not created a form, Please Create a form first', 'user_registration' );
			}

			return $user_registration_forms;
		}
	}

}
