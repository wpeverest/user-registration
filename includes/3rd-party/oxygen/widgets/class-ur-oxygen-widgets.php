<?php
/**
 * User Registration Form for Oxygen Builder.
 *
 * @package UserRegistration\Class
 * @version xx.xx.xx
 */

/**
 * Oxygen Form Widget.
 */
class UR_OXYGEN_WIDGET extends \OxyEl {


	/**
	 * Indicates whether the CSS has been added.
	 *
	 * @var bool
	 */
	protected $css_added = false;

	/**
	 * Indicates whether the JavaScript has been added.
	 *
	 * @var bool
	 */
	protected $js_added = false;

	/**
	 * The name of the widget.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The slug for the widget.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The icon for the widget.
	 *
	 * @var string
	 */
	protected $icon;

	/**
	 * The button priority for the widget.
	 *
	 * @var int
	 */
	protected $priority;


	/**
	 * Constructor for the Oxygen Form Widget.
	 *
	 * @param string $name     The name of the widget.
	 * @param string $slug     The slug for the widget.
	 * @param string $icon     The icon for the widget.
	 * @param int    $priority The button priority for the widget.
	 */
	public function __construct( $name = 'Default Name', $slug = 'default_slug', $icon = null, $priority = 10 ) {
		parent::__construct();
		$this->name     = $name;
		$this->slug     = $slug;
		$this->icon     = $icon;
		$this->priority = $priority;
	}

	/**
	 * Get the default icon for the widget.
	 *
	 * @return string The default icon in SVG format.
	 */
	protected function default_icon() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g><path fill="#82878c" d="M18.1 4h-3.8l1.2 2h3.9zM20.6 8h-3.9l1.2 2h3.9zM20.6 18H5.8L12 7.9l2.5 4.1H12l-1.2 2h7.3L12 4.1 2.2 20h19.6z"/></g></svg>';
	}

	/**
	 * Get the name of the widget.
	 *
	 * @return string The name.
	 */
	public function name() {
		lg( 'name' );
		lg( $this->name );
		return $this->name;
	}

	/**
	 * Get the slug for the widget.
	 *
	 * @return string The slug.
	 */
	public function slug() {
		return $this->slug;
	}

	/**
	 * Get the button priority for the widget.
	 *
	 * @return int The button priority.
	 */
	public function button_priority() {
		return $this->priority;
	}

	/**
	 * Get the icon for the widget.
	 *
	 * @param string $fill The fill color for the icon.
	 * @param bool   $base64 Whether to return the icon as a base64 encoded string.
	 * @return string The icon in SVG or base64 format.
	 */
	public function icon() {
		$this->icon = $this->icon ? $this->icon : $this->default_icon();
		$this->icon = $base64 ? 'data:image/svg+xml;base64,' . base64_encode( $this->icon ) : $this->icon;
	}

	/**
	 * Get the class names for the widget.
	 *
	 * @return array The class names.
	 */
	public function class_names() {
		return array(
			'oxy-ur-widget',
			'oxy-form-widget-' . $this->slug(),
		);
	}

	/**
	 * Get the button place for the widget.
	 *
	 * @return string The button place.
	 */
	public function button_place() {
		$button_place = $this->accordion_button_place();

		if ( $button_place ) {
			return 'user-registration::' . $button_place;
		}

		return '';
	}

	/**
	 * Get the accordion button place for the widget.
	 *
	 * @return string The accordion button place.
	 */
	public function accordion_button_place() {
		return 'forms';
	}

	/**
	 * Define controls for the widget.
	 */
	public function controls() {
		// Override in specific widgets.
	}

	/**
	 * Render the element's UI.
	 */
	public function render( $options, $defaults, $content ) {
		echo '<div class="ur-form-widget">';
		echo 'Default content - Override in specific widgets';
		echo '</div>';
	}
}
