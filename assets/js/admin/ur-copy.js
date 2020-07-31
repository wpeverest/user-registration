/**
 * Simple text copy functions using native browser clipboard capabilities.
 * @since 1.4.3
 */
jQuery( function ($){
	$(document.body).find('.ur-copy-shortcode').each( function() {
		var $this = $(this);
		$this.on( 'click', function( evt ) {
			var res = $this.parent().find('.code').val();
			urSetClipboard(res, $this );

			$this.tipTip({
					'attribute': 'data-copied',
					'activation': 'focus',
					'fadeIn': 50,
					'fadeOut': 50,
					'delay': 200
				}).focus();

			evt.preventDefault();
		});
	});

});

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
