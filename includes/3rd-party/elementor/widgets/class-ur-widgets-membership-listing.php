<?php
/**
 * User Registration Membership Listing for Elementor.
 *
 * @package UserRegistration\Class
 * @since xx.xx.xx
 */

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use WPEverest\URMembership\Admin\Repositories\MembershipGroupRepository;
use WPEverest\URMembership\ShortCodes;

/**
 * User Registration Membership Listing Widget for Elementor.
 */
class UR_Elementor_Widget_Membership_Listing extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'user-registration-membership-listing';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Membership Pricing', 'user-registration' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'ur-icon-subscription-plan';
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
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return array( 'membership', 'pricing', 'plans', 'user-registration', 'membership listing', 'membership pricing' );
	}

	/**
	 * Register controls.
	 */
	protected function register_controls() {

		// ── Content Tab ──────────────────────────────────────────────────
		$this->start_controls_section(
			'ur_elementor_membership_listing',
			array(
				'label' => esc_html__( 'Membership Pricing', 'user-registration' ),
			)
		);

		$this->add_control(
			'group_id',
			array(
				'label'   => esc_html__( 'Membership Group', 'user-registration' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->get_membership_groups(),
				'default' => '0',
			)
		);

		$this->add_control(
			'list_type',
			array(
				'label'   => esc_html__( 'Display Type', 'user-registration' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'list'  => esc_html__( 'List', 'user-registration' ),
					'row'   => esc_html__( 'Row', 'user-registration' ),
					'block' => esc_html__( 'Column', 'user-registration' ),
				),
				'default' => 'list',
			)
		);

		$this->add_control(
			'column_number',
			array(
				'label'     => esc_html__( 'Number of Columns', 'user-registration' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 6,
				'default'   => 3,
				'condition' => array(
					'list_type' => 'block',
				),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'       => esc_html__( 'Button Text', 'user-registration' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Get Started', 'user-registration' ),
			)
		);

		$this->add_control(
			'show_description',
			array(
				'label'        => esc_html__( 'Show Description', 'user-registration' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'user-registration' ),
				'label_off'    => esc_html__( 'No', 'user-registration' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'list_type!' => 'list',
				),
			)
		);

		$this->end_controls_section();

		// ── Style Tab ─────────────────────────────────────────────────────

		// Button Colors
		$this->start_controls_section(
			'ur_membership_button_style',
			array(
				'label' => esc_html__( 'Button', 'user-registration' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'button_text_color',
			array(
				'label'   => esc_html__( 'Text Color', 'user-registration' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '',
			)
		);

		$this->add_control(
			'button_bg_color',
			array(
				'label'   => esc_html__( 'Background Color', 'user-registration' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '',
			)
		);

		$this->add_control(
			'button_text_hover_color',
			array(
				'label'   => esc_html__( 'Hover Text Color', 'user-registration' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '',
			)
		);

		$this->add_control(
			'button_bg_hover_color',
			array(
				'label'   => esc_html__( 'Hover Background Color', 'user-registration' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '',
			)
		);

		$this->add_control(
			'button_font_size',
			array(
				'label'      => esc_html__( 'Font Size (px)', 'user-registration' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 8,
						'max' => 72,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'label'    => esc_html__( 'Typography', 'user-registration' ),
				'selector' => '',
			)
		);

		$this->add_control(
			'button_padding',
			array(
				'label'      => esc_html__( 'Padding', 'user-registration' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
			)
		);

		$this->add_control(
			'button_margin',
			array(
				'label'      => esc_html__( 'Margin', 'user-registration' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
			)
		);

		$this->end_controls_section();

		// Radio / Selection Color (list type only)
		$this->start_controls_section(
			'ur_membership_radio_style',
			array(
				'label'     => esc_html__( 'Radio', 'user-registration' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'list_type' => 'list',
				),
			)
		);

		$this->add_control(
			'radio_color',
			array(
				'label'   => esc_html__( 'Radio Color', 'user-registration' ),
				'type'    => Controls_Manager::COLOR,
				'default' => '',
			)
		);

		$this->end_controls_section();

		do_action( 'user_registration_elementor_membership_listing_style', $this );
	}

	/**
	 * Render widget output.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$style = array(
			'buttonTextColor'      => $settings['button_text_color'] ?? '',
			'buttonBgColor'        => $settings['button_bg_color'] ?? '',
			'buttonTextHoverColor' => $settings['button_text_hover_color'] ?? '',
			'buttonBgHoverColor'   => $settings['button_bg_hover_color'] ?? '',
			'radioColor'           => $settings['radio_color'] ?? '',
			'buttonFontSize'       => isset( $settings['button_font_size']['size'] ) && '' !== $settings['button_font_size']['size']
				? $settings['button_font_size']['size'] . ( $settings['button_font_size']['unit'] ?? 'px' )
				: '',
			'buttonTypography'     => array(
				'fontWeight' => $settings['button_typography_font_weight'] ?? '',
				'fontStyle'  => $settings['button_typography_font_style'] ?? '',
			),
			'buttonPadding'        => array(
				'top'    => isset( $settings['button_padding']['top'] ) ? $settings['button_padding']['top'] . ( $settings['button_padding']['unit'] ?? 'px' ) : '',
				'right'  => isset( $settings['button_padding']['right'] ) ? $settings['button_padding']['right'] . ( $settings['button_padding']['unit'] ?? 'px' ) : '',
				'bottom' => isset( $settings['button_padding']['bottom'] ) ? $settings['button_padding']['bottom'] . ( $settings['button_padding']['unit'] ?? 'px' ) : '',
				'left'   => isset( $settings['button_padding']['left'] ) ? $settings['button_padding']['left'] . ( $settings['button_padding']['unit'] ?? 'px' ) : '',
			),
			'buttonMargin'         => array(
				'top'    => isset( $settings['button_margin']['top'] ) ? $settings['button_margin']['top'] . ( $settings['button_margin']['unit'] ?? 'px' ) : '',
				'right'  => isset( $settings['button_margin']['right'] ) ? $settings['button_margin']['right'] . ( $settings['button_margin']['unit'] ?? 'px' ) : '',
				'bottom' => isset( $settings['button_margin']['bottom'] ) ? $settings['button_margin']['bottom'] . ( $settings['button_margin']['unit'] ?? 'px' ) : '',
				'left'   => isset( $settings['button_margin']['left'] ) ? $settings['button_margin']['left'] . ( $settings['button_margin']['unit'] ?? 'px' ) : '',
			),
		);

		$group_id = ( ! empty( $settings['group_id'] ) && '0' !== $settings['group_id'] ) ? $settings['group_id'] : '';

		$attributes = array(
			'group_id'         => $group_id,
			'id'               => $group_id,
			'list_type'        => $settings['list_type'] ?? 'list',
			'column_number'    => isset( $settings['column_number'] ) ? absint( $settings['column_number'] ) : 3,
			'button_text'      => $settings['button_text'] ?? '',
			'show_description' => 'yes' === ( $settings['show_description'] ?? 'yes' ),
			'style'            => $style,
		);

		if ( class_exists( 'WPEverest\URMembership\ShortCodes' ) ) {
			echo '<div class="user-registration-page">';
			echo ShortCodes::membership_listing( $attributes, 'user_registration_membership_listing' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</div>';
		}
	}

	/**
	 * Retrieve available membership groups for the select control.
	 *
	 * @return array
	 */
	private function get_membership_groups() {
		$options = array(
			'0' => esc_html__( 'All Memberships', 'user-registration' ),
		);

		if ( ! class_exists( 'WPEverest\URMembership\Admin\Repositories\MembershipGroupRepository' ) ) {
			return $options;
		}

		$repository = new MembershipGroupRepository();
		$groups     = $repository->get_all_membership_groups();

		if ( ! empty( $groups ) ) {
			foreach ( $groups as $group ) {
				$options[ $group['ID'] ] = esc_html( $group['post_title'] );
			}
		}

		return $options;
	}
}
