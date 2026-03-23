<?php
/**
 * WPEverest\URM\FIXED_PERIOD_MEMBERSHIP Frontend.
 *
 * @class    Frontend
 * @package  WPEverest\URM\FIXED_PERIOD_MEMBERSHIP\Frontend
 * @category Frontend
 */

namespace WPEverest\URM\FixedPeriodMemebership;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Frontend {

	/**
	 * Constructor – initialize hooks.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Register all frontend hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
	}
}
