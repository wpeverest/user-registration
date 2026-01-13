<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/user-registration/myaccount/courses.php.
 *
 * HOWEVER, on occasion UserRegistration will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.wpuserregistration.com/docs/how-to-edit-user-registration-template-files-such-as-login-form/
 * @package UserRegistration/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$course_portal_page_url = '';

$course_portal_page = get_page_by_path( 'course-portal' );

if ( $course_portal_page ) {
	// Get the permalink (URL) of the course_portal_page
	$course_portal_page_url = get_permalink( $course_portal_page->ID );
}

?>

<div class="masteriyo-courses-wrapper masteriyo-course columns-3 list-view"
	style="">
	<?php
	foreach ( $courses as $cr ) {
		$GLOBALS['course'] = $cr;
		\masteriyo_get_template_part( 'content', 'course' );
	}
	?>
</div>

<div class="urm-masteriyo-course-portal">
<p><?php echo esc_html__( 'For more course details like quiz attempts, assignments and more, visit: ', 'user-registration' ); ?><a target="_blank" href="<?php echo esc_url( $course_portal_page_url ); ?>"><span><?php echo esc_html__( 'Course Portal', 'user-registration' ); ?></span></a></p>
</div>
