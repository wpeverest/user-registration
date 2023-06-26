<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handle data for the current customers session
 *
 * @class    UR_Session
 * @version  1.0.0
 * @package  UserRegistration/Abstracts
 * @category Abstract Class
 * @author   WPEverest
 */
abstract class UR_Session {

	/** @var int $_customer_id */
	protected $_customer_id;

	/** @var array $_data  */
	protected $_data = array();

	/** @var bool $_dirty When something changes */
	protected $_dirty = false;

	/**
	 * __get function.
	 *
	 * @param mixed $key
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->get( $key );
	}

	/**
	 * __set function.
	 *
	 * @param mixed $key
	 * @param mixed $value
	 */
	public function __set( $key, $value ) {
		$this->set( $key, $value );
	}

	 /**
	  * __isset function.
	  *
	  * @param mixed $key
	  * @return bool
	  */
	public function __isset( $key ) {
		return isset( $this->_data[ sanitize_title( $key ) ] );
	}

	/**
	 * __unset function.
	 *
	 * @param mixed $key
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
	 * @param string $key
	 * @param  mixed  $default used if the session variable isn't set
	 * @return array|string value of session variable
	 */
	public function get( $key, $default = null ) {
		$key = sanitize_key( $key );

		return isset( $this->_data[ $key ] ) ? ur_maybe_unserialize( $this->_data[ $key ] ) : $default;
	}

	/**
	 * Set a session variable.
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function set( $key, $value ) {
		if ( $value !== $this->get( $key ) ) {
			$this->_data[ sanitize_key( $key ) ] = maybe_serialize( $value );
			$this->_dirty                        = true;
		}
	}

	/**
	 * get_customer_id function.
	 *
	 * @access public
	 * @return int
	 */
	public function get_customer_id() {
		return $this->_customer_id;
	}
}
