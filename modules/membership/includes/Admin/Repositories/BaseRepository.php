<?php

namespace WPEverest\URMembership\Admin\Repositories;

class BaseRepository implements \WPEverest\URMembership\Admin\Interfaces\BaseInterface {
	protected $table;

	/**
	 * Return global wpdb.
	 *
	 * @return \wpdb
	 */
	public function wpdb() {
		global $wpdb;

		return $wpdb;
	}

	/**
	 * Insert new Data
	 *
	 * @param $data
	 *
	 * @return array|false|mixed|object|\stdClass|void
	 */
	public function create( $data ) {
		$result    = $this->wpdb()->insert(
			$this->table,
			$data
		);
		$insert_id = $this->wpdb()->insert_id;

		return $this->retrieve( $insert_id );
	}

	/**
	 * Retrieve specific record by id.
	 *
	 * @param $id
	 *
	 * @return array|false|mixed|object|\stdClass|void
	 */
	public function retrieve( $id ) {
		$result = $this->wpdb()->get_row(
			$this->wpdb()->prepare(
				"SELECT * FROM $this->table WHERE id = %d",
				$id
			),
			ARRAY_A
		);

		return ! $result ? false : $result;
	}

	/**
	 * Update specific record by ID
	 *
	 * @param $id
	 * @param $data
	 *
	 * @return bool|int|mixed|\mysqli_result|null
	 */
	public function update( $id, $data ) {
		return $this->wpdb()->update(
			$this->table,
			$data,
			array( 'ID' => $id )
		);
	}

	/**
	 * Delete Multiple record by ID's
	 *
	 * @param $ids
	 *
	 * @return bool|int|\mysqli_result|void|null
	 */
	public function delete_multiple( $ids ) {

		return $this->wpdb()->query( "DELETE FROM $this->table WHERE ID IN (" . $ids . ')' );
	}

	/**
	 * Delete single record by ID
	 *
	 * @param $id
	 *
	 * @return bool|int|\mysqli_result|null
	 */
	public function delete( $id ) {
		return $this->wpdb()->delete( $this->table, array( 'ID' => $id ) );
	}
}
