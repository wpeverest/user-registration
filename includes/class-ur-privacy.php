<?php
/**
 * Privacy/GDPR related functionality which ties into WordPress functionality.
 *
 * @package UserRegistration\Classes
 * @version 1.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * UR_Privacy Class.
 */
class UR_Privacy {

	/**
	 * Init - hook into events.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'add_privacy_message' ) );
	}

	/**
	 * Adds the privacy message on UR privacy page.
	 */
	public function add_privacy_message() {
		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
			$content = $this->get_privacy_message();

			if ( $content ) {
				wp_add_privacy_policy_content( __( 'User Registration', 'user-registration' ), $this->get_privacy_message() );
			}
		}
	}

	/**
	 * Add privacy policy content for the privacy policy page.
	 *
	 * @since 1.5.0
	 */
	public function get_privacy_message() {
		$content = '
			<div class="wp-suggested-text">' .
				'<p class="privacy-policy-tutorial">' .
					__( 'This sample policy includes the basics around what personal data you may be collecting, storing and sharing, as well as who may have access to that data. Depending on what settings are enabled and which additional plugins are used, the specific information shared by your form will vary. We recommend consulting with a lawyer when deciding what information to disclose on your privacy policy.', 'user-registration' ) .
				'</p>' .
				'<p>' . __( 'We collect information about the user during the user registration form submission process on our site.', 'user-registration' ) . '</p>' .
				'<h2>' . __( 'What we collect and store', 'user-registration' ) . '</h2>' .
				'<p>' . __( 'While you visit our site, we’ll track:', 'user-registration' ) . '</p>' .
				'<ul>' .
					'<li>' . __( 'Form Fields Data: Forms Fields data includes the available field types when creating a form. We’ll use this to, for example, collect informations like Name, Email and other available fields.', 'user-registration' ) . '</li>' .
					'<li>' . __( 'Location, IP address and browser type: we’ll use this for purposes like geolocating users and reducing fraudulent activities.', 'user-registration' ) . '</li>' .
					'<li>' . __( 'Transaction Details: we’ll ask you to enter this so we can, for instance, provide subscription packs, and keep track of your payment details for subscription packs!', 'user-registration' ) . '</li>' .
				'</ul>' .
				'<p>' . __( 'When you fill up a form, we’ll ask you to provide information including your name, address, email, phone number, payment details and optional account information like username and password and any other form fields available in the registration form. We’ll use this information for purposes, such as, to:', 'user-registration' ) . '</p>' .
				'<ul>' .
					'<li>' . __( 'Send you information about your account and order', 'user-registration' ) . '</li>' .
					'<li>' . __( 'Respond to your requests, including transaction details and complaints', 'user-registration' ) . '</li>' .
					'<li>' . __( 'Process payments and prevent fraud', 'user-registration' ) . '</li>' .
					'<li>' . __( 'Set up your account for our site', 'user-registration' ) . '</li>' .
					'<li>' . __( 'Comply with any legal obligations we have, such as calculating taxes', 'user-registration' ) . '</li>' .
					'<li>' . __( 'Improve our form offerings', 'user-registration' ) . '</li>' .
					'<li>' . __( 'Send you marketing messages, if you choose to receive them', 'user-registration' ) . '</li>' .
					'<li>' . __( 'Or any other service the built form was created to comply with and it’s necessary information', 'user-registration' ) . '</li>' .
				'</ul>' .
				'<p>' . __( 'If you create an account, we will store your name, address, email and phone number, which will be used to populate the form fields for future submissions.', 'user-registration' ) . '</p>' .
				'<p>' . __( 'We generally store information about you for as long as we need the information for the purposes for which we collect and use it, and we are not legally required to continue to keep it. For example, we will store form submission information for XXX years for geolocating and marketting purposes. This includes your name, address, email, phone number.', 'user-registration' ) . '</p>' .
				'<h2>' . __( 'Who on our team has access', 'user-registration' ) . '</h2>' .
				'<p>' . __( 'Members of our team have access to the information you provide us. For example, both Administrators and Editors can access:', 'user-registration' ) . '</p>' .
				'<ul>' .
					'<li>' . __( 'Form submission information and other details related to it', 'user-registration' ) . '</li>' .
					'<li>' . __( 'Customer information like your name, email and address information.', 'user-registration' ) . '</li>' .
				'</ul>' .
				'<p>' . __( 'Our team members have access to this information to help fulfill entries and support you.', 'user-registration' ) . '</p>' .
				'<h2>' . __( 'What we share with others', 'user-registration' ) . '</h2>' .
				'<p class="privacy-policy-tutorial">' . __( 'In this section you should list who you’re sharing data with, and for what purpose. This could include, but may not be limited to, analytics, marketing, payment gateways, shipping providers, and third party embeds.', 'user-registration' ) . '</p>' .
				'<p>' . __( 'We share information with third parties who help us provide our orders and store services to you; for example --', 'user-registration' ) . '</p>' .
				'<h3>' . __( 'Payments', 'user-registration' ) . '</h3>' .
				'<p class="privacy-policy-tutorial">' . __( 'In this subsection you should list which third party payment processors you’re using to take payments on your site since these may handle customer data. We’ve included PayPal as an example, but you should remove this if you’re not using PayPal.', 'user-registration' ) . '</p>' .
				'<p>' . __( 'We accept payments through PayPal. When processing payments, some of your data will be passed to PayPal, including information required to process or support the payment, such as the purchase total and billing information.', 'user-registration' ) . '</p>' .
				'<p>' . __( 'Please see the <a href="https://www.paypal.com/us/webapps/mpp/ua/privacy-full">PayPal Privacy Policy</a> for more details.', 'user-registration' ) . '</p>' .
				'<h3>' . __( 'Available Modules', 'user-registration' ) . '</h3>' .
				'<p class="privacy-policy-tutorial">' . __( 'In this subsection you should list which third party modules you’re using to increase functionality on your site since these may handle customer data. We’ve included MailChimp as an example, but you should remove this if you’re not using MailChimp.', 'user-registration' ) . '</p>' .
				'<p>' . __( 'We send beautiful email through MailChimp. When processing emails, some of your data will be passed to MailChimp, including information required to process or support the email marketing services, such as the name, email address and any other information that you intend to pass or collect including all collected information through subscription.', 'user-registration' ) . '</p>' .
				'<p>' . __( 'Please see the <a href="https://mailchimp.com/legal/privacy/">MailChimp Privacy Policy</a> for more details.', 'user-registration' ) . '</p>' .
			'</div>';

		return apply_filters( 'user_registration_privacy_policy_content', $content );
	}
}

new UR_Privacy();
