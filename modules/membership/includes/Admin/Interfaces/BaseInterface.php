<?php
/**
 * URMembership Interfaces.
 *
 * @package  URMembership/BaseInterface
 * @category Interface
 * @author   WPEverest
 */

namespace WPEverest\URMembership\Admin\Interfaces;

interface BaseInterface {

	/**
	 * Create a new entry in the database.
	 *
	 * @param mixed $data The data to create the entry with.
	 *
	 * @return mixed The created entry.
	 */
	public function create( $data );

	/**
	 * Retrieve an entry from the database.
	 *
	 * @param integer $id The id for the entry to retrieve with.
	 *
	 * @return mixed The retrieved entry.
	 */
	public function retrieve( $id );

	/**
	 * Update an entry in the database.
	 *
	 * @param integer $id The id for the entry to retrieve with.
	 * @param mixed   $data The data to create the entry with.
	 *
	 * @return mixed The updated entry.
	 */
	public function update( $id, $data );

	/**
	 * Delete an entry from the database.
	 *
	 * @param integer $id The id for the entry to retrieve with.
	 *
	 * @return bool Returns true on success.
	 */
	public function delete( $id );

	/**
	 * Delete multiple entries from the database.
	 *
	 * @param array $ids An array of ids for the entries to delete.
	 *
	 * @return void
	 */
	public function delete_multiple( $ids );
}
