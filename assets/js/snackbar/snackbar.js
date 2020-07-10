/*!
 * JavaScript Library v1.0.0
 * Name: Snackbar
 * Author: WPEverest
 * Versoin: 1.0.0
 */
var UR_Snackbar = function() {
	this.offsetY = 30;
	this.diffY = 15;
	this.body = document.getElementsByTagName( 'body' )[0];
	this.list = {};
}

/**
 * Add/Create a new snackbar. Following are the options currently supported.
 * - type: success, failure
 * - message: Message to display.
 * - duration: How long the snackbar should stay. Unit is seconds.
 */
UR_Snackbar.prototype.add = function( options = {} ) {
	var instance = this;
	var ID = 'x' + new Date().getTime().toString();
	var container = document.createElement( 'div' );
	var icon = document.createElement( 'span' );
	var label = document.createElement( 'label' );
	var bottom = this.offsetY + this.diffY * Object.keys( this.list ).length;

	// Prepare configurations.
	var stay_seconds = options.duration ? parseFloat( options.duration ) : 1;
	var message = options.message ? options.message : '';
	var type = options.type ? options.type : 'success';

	// Create text node for snackbar label.
	var text = document.createTextNode( message );

	// Calculate and assign position of the snackbar in Y axis from bottom.
	Object.values( this.list ).forEach( function ( snackbar ) {
		if ( snackbar.container ) {
			bottom += snackbar.container.clientHeight;
		}
	});
	container.style.bottom = bottom + 'px';

	// Some assignments.
	container.id = ID;
	container.classList.add( 'snackbar' );
	container.classList.add( type );
	icon.classList.add( 'icon' );

	// Initially hide the snackbar by changing position.
	container.style.right = '-600px';

	// Add custom container class.
	if ( options.containerClass ) {
		container.classList.add( options.containerClass );
	}

	// Assemble snackbar.
	container.appendChild( icon );
	label.appendChild( text );
	container.appendChild( label );

	// Add the snackbar to the 'body' tag.
	this.body.appendChild(container);

	// Change position to make it visible.
	setTimeout(() => {
		container.style.right = '6px';
	}, 50);

	// Set timer to remove the snackbar after the given duration.
	setTimeout(() => {
		container.style.right = '-600px';

		setTimeout(() => {
			container.remove();
			instance.removeListItem( ID );
		}, 500);
	}, stay_seconds * 1000);

	// Add this snackbar to the list.
	this.list[ ID ] = {
		ID: ID,
		container: container,
		icon: icon,
		text: text,
		label: label,
		options: options,
	};
}

/**
 * Remove a snackbar from the list.
 */
UR_Snackbar.prototype.removeListItem = function( ID ) {
	if ( this.list[ ID ] ) {
		delete this.list[ ID ];
	}
}
