jQuery(document).ready(function($){
	var url      = window.location.href;
	var containsUserRegistration = url.includes("form=user_registration");
	if (! containsUserRegistration ) {
			return;
	}

	var $dot = $( '<span class="ur-shortcode-form-embed-dot">&nbsp;</span>' ),
	anchor = isGutenberg() ? '.block-editor .edit-post-header' : '';

	var content = '<div><h3>'+user_registration_blocks_editor_prams.i18n_add_a_block+'</h3><p>'+user_registration_blocks_editor_prams.i18n_add_a_block_tip+'</p><i class="ur-shortcode-form-embed-theme-tooltips-red-arrow"></i><button type="button" class="ur-shortcod-form-embed-theme-done-btn">'+user_registration_blocks_editor_prams.i18n_done_btn+'</button></div>'
	var tooltipsterArgs = {
		content          : $( content ),
		trigger          : 'load',
		interactive      : true,
		animationDuration: 0,
		delay            : 0,
		theme            : [ 'tooltipster-default', 'ur-shortcode-form-embed-theme'],
		side             : isGutenberg ? 'bottom' : 'right',
		distance         : 3,
		functionReady    : function( instance, helper ) {

			instance._$tooltip.on( 'click', 'button', function() {

				instance.close();
				$( '.ur-shortcode-form-embed-dot' ).remove();
			} );

			instance.reposition();
		},
	};

	if ( ! isGutenberg ) {
		$dot.insertAfter( anchor ).tooltipster( tooltipsterArgs ).tooltipster( 'open' );
	}

	// The Gutenberg header can be loaded after the window load event.
	// We have to wait until the Gutenberg heading is added to the DOM.
	var closeAnchorListener = wp.data.subscribe( function() {

		if ( ! $( anchor ).length ) {
			return;
		}

		// Close the listener to avoid an infinite loop.
		closeAnchorListener();

		$dot.insertAfter( anchor ).tooltipster( tooltipsterArgs ).tooltipster( 'open' );
	} );

	function isGutenberg() {

		return typeof wp !== 'undefined' && Object.prototype.hasOwnProperty.call( wp, 'blocks' );
	}

})
