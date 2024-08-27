<?php

/**
 * User Registration Profile Details for Elementor.
 *
 * @package UserRegistration\Class
 * @since 3.2.2
 */

use Elementor\Plugin;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * User Registration Profile Details Widget for Elementor.
 */
class UR_Elementor_Widget_View_Details extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve shortcode widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'user-registration-view-profile-details';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve shortcode widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'View Profile Details', 'user-registration' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve shortcode widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'ur-icon-profile-details';
	}

	/**
	 * Get widget categories.
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'user-registration' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return array( 'form', 'forms', 'user-registration', 'view profile details', 'user-registration-view-profile-details' );
	}

	/**
	 * Register controls.
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'ur_elementor_view_profile_details',
			array(
				'label' => esc_html__( 'Profile Details', 'user-registration' ),
			)
		);

		$forms = $this->get_forms();

		$this->add_control(
			'ur_view_profile_details',
			array(
				'label'   => esc_html__( 'Select Form', 'user-registration' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $forms,
				'default' => 'ur_view_profile_details',
			)
		);
		$this->end_controls_section();
		do_action( 'user_registration_elementor_profile_details_style', $this );

	}

	/**
	 * Retrieve the shortcode.
	 */
	private function get_shortcode() {
		$settings = $this->get_settings_for_display();
		if ( ! $settings['ur_view_profile_details'] ) {
			return '<p>' . __( 'Please select a View Profile Details Forms.', 'user-registration' ) . '</p>';
		}
		$shortcode = '[user_registration_view_profile_details]';
		$shortcode = sprintf( apply_filters( 'user_registration_elementor_shortcode_view_profile_details', $shortcode, $settings ) );
		return $shortcode;
	}

	/**
	 * Render widget output.
	 */
	protected function render() {
		echo do_shortcode( $this->get_shortcode() );
	}

	/**
	 * Retrieve the available UR.
	 */
	public function get_forms() {
		$user_registration_forms = array();

		if ( empty( $user_registration_forms ) ) {
			$user_registration_forms['ur_view_profile_details'] = esc_html__( 'Default Form', 'user-registration' );
		}

		return $user_registration_forms;
	}
}
