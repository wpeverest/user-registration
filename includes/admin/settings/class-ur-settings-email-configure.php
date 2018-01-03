<?php
/**
 * Configure Email
 *
 * @class    UR_Settings_Email_Configure
 * @extends  UR_Settings_Email
 * @category Class
 * @author   WPEverest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UR_Settings_Email_Configure', false ) ) :

/**
 * UR_Settings_Email_Configure Class.
 */
class UR_Settings_Email_Configure extends UR_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id             = 'email_configure';
		$this->title          = __( 'Configure Emails', 'user-registration' );
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'         => __( 'Enable/Disable', 'online-restaurant-reservation' ),
				'type'          => 'checkbox',
				'label'         => __( 'Enable this email notification', 'online-restaurant-reservation' ),
				'default'       => 'yes',
			),
			'recipient' => array(
				'title'         => __( 'Recipient(s)', 'online-restaurant-reservation' ),
				'type'          => 'text',
				'description'   => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'online-restaurant-reservation' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
				'placeholder'   => '',
				'default'       => '',
				'desc_tip'      => true,
			),
			'subject' => array(
				'title'         => __( 'Subject', 'online-restaurant-reservation' ),
				'type'          => 'text',
				'desc_tip'      => true,
				/* translators: %s: list of placeholders */
				'description'   => sprintf( __( 'Available placeholders: %s', 'online-restaurant-reservation' ), '<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>' ),
				'placeholder'   => $this->get_default_subject(),
				'default'       => '',
			),
			'heading' => array(
				'title'         => __( 'Email heading', 'online-restaurant-reservation' ),
				'type'          => 'text',
				'desc_tip'      => true,
				/* translators: %s: list of placeholders */
				'description'   => sprintf( __( 'Available placeholders: %s', 'online-restaurant-reservation' ), '<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>' ),
				'placeholder'   => $this->get_default_heading(),
				'default'       => '',
			),
			'email_type' => array(
				'title'         => __( 'Email type', 'online-restaurant-reservation' ),
				'type'          => 'select',
				'description'   => __( 'Choose which format of email to send.', 'online-restaurant-reservation' ),
				'default'       => 'html',
				'class'         => 'email_type orr-enhanced-select',
				'options'       => $this->get_email_type_options(),
				'desc_tip'      => true,
			),
		);
	}
	public function admin_options() {
		// Do admin actions.
		?>
		<h2><?php echo esc_html__('Email Configuration','user-registration'); ?> <?php ur_back_link( __( 'Return to emails', 'user-registration' ), admin_url( 'admin.php?page=user-registration-settings&tab=email' ) ); ?></h2>

		<?php
			
		
	}

}
endif;

return new UR_Settings_Email_Configure();
