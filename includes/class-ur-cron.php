<?php
/**
 * UR Cron
 *UR
 * @package     UR
 * @subpackage  Classes/Cron
 * @since 2.3.1
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * UR_Cron Class
 *
 * This class handles scheduled events
 *
 * @since 2.3.1
 */
class UR_Cron
{

	/**
	 * Init WordPress hook
	 *
	 * @since 2.3.1
	 * @see UR_Cron::weekly_events()
	 */
	public function __construct()
	{
		add_filter('cron_schedules', array($this, 'add_schedules'));
		add_action('wp', array($this, 'schedule_events'));
	}

	/**
	 * Registers new cron schedules
	 *
	 * @param array $schedules
	 * @return array
	 * @since 2.3.1
	 *
	 */
	public function add_schedules($schedules = array())
	{
		/*Adds once in biweekly to the existing schedules*/
		$schedules['biweekly'] = array(
			'interval' => (DAY_IN_SECONDS*15),
			'display' => __('Every 15 days', 'user-registration'),
		);

		return $schedules;
	}

	/**
	 * Schedules our events
	 *
	 * @return void
	 * @since 2.3.1
	 */
	public function schedule_events()
	{
		$this->biweekly_events();
	}

	/**
	 * Schedule biweekly events
	 *
	 * @access private
	 * @return void
	 * @since 2.3.1
	 */
	private function biweekly_events()
	{
		if (!wp_next_scheduled('user_registration_biweekly_scheduled_events')) {
			wp_schedule_event(time(), 'biweekly', 'user_registration_biweekly_scheduled_events');
		}
	}

}

$user_registration_cron = new UR_Cron();
