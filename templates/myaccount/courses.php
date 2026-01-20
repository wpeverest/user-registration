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
	$course_portal_page_url = get_permalink( $course_portal_page->ID );
}

?>

<div class="urm-courses-container">
	<?php
	foreach ( $courses as $cr ) {
		$GLOBALS['course'] = $cr;

		$course_id      = $cr->get_id();
		$course_name    = $cr->get_name();
		$course_url     = $cr->get_permalink();
		$date_created   = $cr->get_date_created();
		$formatted_date = $date_created ? $date_created->format( 'm/d/Y' ) : '';

		?>
		<div class="urm-course-card">
			<div class="urm-course-card__illustration">
				<a href="<?php echo esc_url( $course_url ); ?>">
					<?php echo wp_kses( $cr->get_image( 'masteriyo_thumbnail' ), 'masteriyo_image' ); ?>
				</a>
			</div>

			<div class="urm-course-card__content">
				<h3 class="urm-course-card__title">
					<?php echo esc_html( $course_name ); ?>
				</h3>
				<div class="urm-course-card__meta">
					<?php
					/* translators: %s: formatted date */
					echo esc_html( sprintf( __( 'Started on %s', 'user-registration' ), $formatted_date ) );
					?>
				</div>
			</div>

			<div class="urm-course-card__progress">
				<?php do_action( 'masteriyo_course_progress', $cr ); ?>

				<a href="<?php echo esc_url( $course_url ); ?>" class="urm-continue-course-btn">
					<?php echo esc_html__( 'Continue Course', 'user-registration' ); ?>
				</a>
			</div>
		</div>
		<?php
	}
	?>
</div>

<div class="urm-masteriyo-course-portal">
	<p><?php echo esc_html__( 'For more course details like quiz attempts, assignments and more, visit: ', 'user-registration' ); ?><a target="_blank" href="<?php echo esc_url( $course_portal_page_url ); ?>"><span><?php echo esc_html__( 'Course Portal', 'user-registration' ); ?></span></a></p>
</div>
