=== User Registration - User Profile, Membership and More ===
Contributors: WPEverest
Tags: user registration, registration, profile-builder, user profile, form, registration form, login form, user login, membership
Requires at least: 4.0
Tested up to: 4.9
Stable tag: 1.3.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Drag and Drop user registration and login form builder

== Description ==

User Registration plugin provides you with an easy way to create frontend user registration and login forms. Drag and Drop fields make ordering and creating forms extremely easy. The plugin is lightweight, exendible and can be used to create any type of registration form.

Supports frontend profile account page for profile edit, password change, Log out and more.

View [All features](https://wpeverest.com/wordpress-plugins/user-registration/)

View [Demo](http://demo.wpeverest.com/user-registration/)

Get [free support](https://wpeverest.com/support-forum/)

Check [documentation](http://docs.wpeverest.com/docs/user-registration/)

= User Registration Plugin in action: =

[youtube https://www.youtube.com/watch?v=zNhNvj8jPhM]

### Features And Options:
* Simple, Clean and Beautiful Registration Forms
* Drag and Drop Fields
* Unlimited Registration Forms
* Multiple Column Design
* Multiple Form template designs
* Shortcode Support
* Google Recaptcha Support
* Email notifications
* Email Customizers
* Form Duplicate Option
* Profile account page
* Admin approval option
* Auto login option
* Email confirmation to register
* Enable/Disable Strong Password
* Default User Role Selection Option
* Well Documented
* Translation ready

### Premium Addons

User Registration can be easily extended with some premium addons.

* [Social Connect](https://wpeverest.com/wordpress-plugins/user-registration/social-connect/) - Allows users to register/login to your site with social platforms like Facebook, Twitter, Google+ or LinkedIn.

* [Content Restriction](https://wpeverest.com/wordpress-plugins/user-registration/content-restriction/) - allows you to restrict full or partial content from page, post to only logged in users or logged in users with specific roles.

* [File Upload](https://wpeverest.com/wordpress-plugins/user-registration/file-upload/) - Allow you to add upload field in registration form so that users can upload documents, images and more.

* [WooCommerce Integration](https://wpeverest.com/wordpress-plugins/user-registration/woocommerce-integration/) - Integrate and syncs WooCommerce related information to user registration account page like orders, customer details, Billings.

* [MailChimp](https://wpeverest.com/wordpress-plugins/user-registration/woocommerce-integration/) - Let you sync your registered users with MailChimp list. Automatically add users to your selected MailChimp list upon registration.

== Installation ==

1. Install the plugin either via the WordPress.org plugin directory, or by uploading the files to your server (in the /wp-content/plugins/ directory).
2. Activate the User Registration plugin through the 'Plugins' menu in WordPress.
3. Go to User Registration->Add New and start creating a registration form.

== Frequently Asked Questions ==

= What is the plugin license? =

* This plugin is released under a GPL license.

= Does the plugin work with any WordPress themes?

Yes, the plugin is designed to work with any themes that have been coded following WordPress guidelines.

== Screenshots ==

1. Mulitple Registration Forms
2. Form Fields
3. Frontend Form
4. Frontend Form
5. Mulitple Column Support
6. Account Details
7. Dashboard
8. Field Options
9. Login Form
10. Settings
11. Shortcode

== Changelog ==

= 1.3.0 - - 11/05/2018 =
* Refactor - user_password field for mod security reason
* Refactor - Meta keys and field keys
* Feature - Cutomizable reset password email
* Feature - Deafult user fields on profile tab in my account section
* Fix - Redirect via template_redirect hook removing js redirection
* Add - Filter hooks for every email classes

= 1.2.5.1 - 27/04/2018 =
* Tweak - Text color of paragraph in myaccount section
* Fix - Use template_redirect hook instead to check verification token

= 1.2.5 - 19/04/2018 =
* Fix - Default meta keys issue
* Refactor - Default user meta keys migration

= 1.2.4 - 12/04/2018 =
* Feature - Introduce jquery validate for client side validation
* Feature - Allow user to resend email verification link
* Fix - Manually created user requiring verification issue
* Fix - Get form data by key issue
* Fix - Console errors in the backend

= 1.2.3 - 06/04/2018 =
* Fix - undefined index error in frontend

= 1.2.2 - 06/04/2018 =
* Feature - Registration without username option
* Feature - Customizable form submission messages
* Feature - Hide label option in each field
* Feature - Description box for each field
* Fix - Date rendering issue
* Tweak - Remove login header from login shortcode

= 1.2.1 - 16/03/2018 =
* Feature - Pending users approval notice for admin
* Feature - Form filled data in email
* Fix - Pending email issue
* Fix - Redirect from email url to default login message issue
* Fix - Load email classes only on demand


= 1.2.0 - 22/02/2018 =
* Fix - Design issue in mobile view.
* Fix - Missing closing anchor tag.
* Fix - Remove email token after use.
* Fix - Store radio field value properly.
* Fix - Console DOM error for checkbox field.
* Feature - Redirect to custom page via email.
* Refactor - Usermeta data store process.

[See changelog for all versions](https://raw.githubusercontent.com/wpeverest/user-registration/master/CHANGELOG.txt).

== Upgrade Notice ==

= 1.3.0 =
1.3.0 is a major release. Make a full site backup and run the database updater immediately after the upgrade.