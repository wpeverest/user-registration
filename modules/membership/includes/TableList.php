<?php
/**
 * URMembership TableList
 *
 * Table-list for membership addon.
 *
 * @class    TableList
 * @package  URMembership/TableList
 * @category Class
 * @author   WPEverest
 */

namespace WPEverest\URMembership;

/**
 * Class consisting all tables used throughout the plugin.
 */
class TableList {

	/**
	 * Returns orders table name.
	 *
	 * @return string
	 */
	public static function orders_table() {
		global $wpdb;

		return $wpdb->prefix . 'ur_membership_orders';
	}

	/**
	 * Returns the name of the order meta table.
	 *
	 * @return string The name of the order meta table.
	 */
	public static function order_meta_table() {
		global $wpdb;

		return $wpdb->prefix . 'ur_membership_ordermeta';
	}

	/**
	 * Returns the name of the subscriptions table.
	 *
	 * @return string The name of the subscriptions table.
	 */
	public static function subscriptions_table() {
		global $wpdb;

		return $wpdb->prefix . 'ur_membership_subscriptions';
	}

	/**
	 * Returns the name of the subscriptions events table.
	 *
	 * @return string The name of the subscriptions events table.
	 */
	public static function subscription_events_table() {
		global $wpdb;

		return $wpdb->prefix . 'ur_membership_subscription_events';
	}

	/**
	 * Returns the name of the users table.
	 *
	 * @return string The name of the users table.
	 */
	public static function users_table() {
		global $wpdb;

		return $wpdb->prefix . 'users';
	}

	/**
	 * Returns the name of the users meta table.
	 *
	 * @return string The name of the users meta table.
	 */
	public static function users_meta_table() {
		global $wpdb;

		return $wpdb->prefix . 'usermeta';
	}

	/**
	 * Returns the name of the posts table.
	 *
	 * @return string The name of the posts table.
	 */
	public static function posts_table() {
		global $wpdb;

		return $wpdb->posts;
	}

	/**
	 * Returns the name of the posts meta table.
	 *
	 * @return string The name of the posts meta table.
	 */
	public static function posts_meta_table() {
		global $wpdb;

		return $wpdb->postmeta;
	}
}
