/* exported evfSetClipboard, evfClearClipboard */

/**
 * Simple text copy functions using native browser clipboard capabilities.
 * @since 1.2.0
 */

/**
 * Set the user's clipboard contents.
 *
 * @param string data: Text to copy to clipboard.
 * @param object $el: jQuery element to trigger copy events on. (Default: document)
 */
function urSetClipboard( data, $el ) {
	if ( 'undefined' === typeof $el ) {
		$el = jQuery( document );
	}
	var $temp_input = jQuery( '<textarea style="opacity:0">' );
	jQuery( 'body' ).append( $temp_input );
	$temp_input.val( data ).select();

	$el.trigger( 'beforecopy' );
	try {
		document.execCommand( 'copy' );
		$el.trigger( 'aftercopy' );
	} catch ( err ) {
		$el.trigger( 'aftercopyfailure' );
	}

	$temp_input.remove();
}

/**
 * Clear the user's clipboard.
 */
function urClearClipboard() {
	urSetClipboard( '' );
}


/* global user_registration_settings_params ur-copy-shortcode*/ 
// (function ( $ ) {

// 	// Open modal when media button is clicked
// 	$(document).on('click', '.ur-copy-shortcode', function(event) {			
// 		event.preventDefault();
// 		var res = $.copy($('input').text());
//     	$("#status").text(res);
// 	});

// })(jQuery);
