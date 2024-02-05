<?php
/**
 * Handle data for the current customers session.
 *
 * @class    UR_Session
 * @version  1.0.0
 * @package  UserRegistration/Abstracts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * UR_Session class.
 */
abstract class UR_Session {

	/**
	 * Customer ID.
	 *
	 * @var int customer id.
	 */
	protected $_customer_id;

	/**
	 * Session Data.
	 *
	 * @var array Session Data.
	 */
	protected $_data = array();

	/**
	 * When something changes.
	 *
	 * @var bool $_dirty When something changes.
	 */
	protected $_dirty = false;

	/**
	 * __get function.
	 *
	 * @param mixed $key Key.
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->get( $key );
	}

	/**
	 * __set function.
	 *
	 * @param mixed $key Key.
	 * @param mixed $value Value.
	 */
	public function __set( $key, $value ) {
		$this->set( $key, $value );
	}

	 /**
	  * __isset function.
	  *
	  * @param mixed $key Key.
	  * @return bool
	  */
	public function __isset( $key ) {
		return isset( $this->_data[ sanitize_title( $key ) ] );
	}

	/**
	 * __unset function.
	 *
	 * @param mixed $key Key.
	 */
	public function __unset( $key ) {
		if ( isset( $this->_data[ $key ] ) ) {
			unset( $this->_data[ $key ] );
			$this->_dirty = true;
		}
	}

	/**
	 * Get a session variable.
	 *
	 * @param string $key Key.
	 * @param  mixed  $default used if the session variable isn't set.
	 * @return array|string value of session variable
	 */
	public function get( $key, $default = null ) {
		$key = sanitize_key( $key );

		return isset( $this->_data[ $key ] ) ? ur_maybe_unserialize( $this->_data[ $key ] ) : $default;
	}

	/**
	 * Set a session variable.
	 *
	 * @param string $key Key.
	 * @param mixed  $value Value.
	 */
	public function set( $key, $value ) {
		if ( $value !== $this->get( $key ) ) {
			$this->_data[ sanitize_key( $key ) ] = maybe_serialize( $value );
			$this->_dirty                        = true;
		}
	}

	/**
	 * Get customer id function.
	 *
	 * @return int
	 */
	public function get_customer_id() {
		return $this->_customer_id;
	}
}
