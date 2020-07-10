/*!
 * JavaScript Library v1.0.0
 * Name: UR_Toggle_Buttons
 * Author: WPEverest
 * Versoin: 1.0.0
 */

/**
 * Create a new toggle buttons group and return html. Following are the options currently supported.
 * - id: ID for the instance.
 * - className: Class for the parent element.
 * - buttons: List of buttons. Consists of two keys i.e. value and text.
 * - value: Initially selected button value.
 */
window.ur_create_toggle_buttons = function( args ) {
	var id = (args.id ? args.id : ''),
		className = (args.className ? args.className : ''),
		html = '<div class="user-registration-button-group user-registration-button-group-' + id + ' ' + className + '">',
		buttons = (args.buttons && Array.isArray( args.buttons )) ? args.buttons : [],
		value = args.value ? args.value : '',
		active = '';

	buttons.forEach( function( button ) {
		if ( value === button.value ) {
			active = 'is-active';
		} else {
			active = '';
		}
		html += '<button class="button button-tertiary urbg-item urbg-item-' + id + ' ' + active + '" data-value="' + button.value + '">' + button.text + '</button>';
	});
	html += '</div>';

	return html;
}

jQuery( function( $ ) {
	$( document.body ).on( 'click', '.urbg-item', function () {
		if ( ! $( this ).is( '.is-active' ) ) {
			$( this ).siblings().removeClass( 'is-active' );
			$( this ).addClass( 'is-active' );
		}
	});
});
