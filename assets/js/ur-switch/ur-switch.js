/*!
 * JavaScript Library v1.0.0
 * Name: UR_Switch
 * Author: WPEverest
 * Versoin: 1.0.0
 */
var UR_Switch = function( element, options = {} ) {
	if ( 'string' === typeof element ) {
		element = document.getElementById( element );
	}
	var active = options.active ? true : false;

	this.element = element;
	this.onChange = options.onChange && 'function' === typeof options.onChange ? options.onChange : function() {};
	this.onTurnOn = options.onTurnOn && 'function' === typeof options.onTurnOn ? options.onTurnOn : function() {};
	this.onTurnOff = options.onTurnOff && 'function' === typeof options.onTurnOff ? options.onTurnOff : function() {};
	this.toggle = this.toggle.bind( this );
	this.turnOn = this.turnOn.bind( this );
	this.turnOff = this.turnOff.bind( this );
	this.isOn = this.isOn.bind( this );

	if ( element && 'initialized' !== element.dataset.status ) {
		var track = document.createElement( 'span' );
		var thumb = document.createElement( 'span' );

		element.classList.add( 'ur-switch' );
		track.classList.add( 'ur-switch-track' );
		thumb.classList.add( 'ur-switch-thumb' );

		if ( active ) {
			element.classList.add( 'active' );
		}

		element.appendChild( track );
		element.appendChild( thumb );

		element.addEventListener( 'click', this.toggle );
		element.dataset.status = 'initialized';
	}
};

UR_Switch.prototype.toggle = function() {
	if ( this.element.classList.contains( 'active' ) ) {
		this.element.classList.remove( 'active' );
		this.onChange( false, 'off' );
		this.onTurnOff();
	} else {
		this.element.classList.add( 'active' );
		this.onChange( true, 'on' );
		this.onTurnOn();
	}
};

UR_Switch.prototype.isOn = function() {
	if ( this.element.classList.contains( 'active' ) ) {
		return true;
	}
	return false;
};

UR_Switch.prototype.turnOn = function() {
	if ( ! this.isOn() ) {
		this.toggle();
		return true;
	}
	return false;
};

UR_Switch.prototype.turnOff = function() {
	if ( this.isOn() ) {
		this.toggle();
		return true;
	}
	return false;
};
