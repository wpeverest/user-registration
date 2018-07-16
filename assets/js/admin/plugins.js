/* global ur_plugins_params */
jQuery( function( $ ) {

   $( document.body ).on( 'click' ,'tr[data-plugin="user-registration/user-registration.php"] span.deactivate a:not(.hasNotice)', function( e ) {
		e.preventDefault();

		var data = {
			action: 'user_registration_deactivation_notice',
			security: ur_plugins_params.deactivation_nonce
		};

		$.post( ur_plugins_params.ajax_url, data, function( response ) {
			$( 'tr[data-plugin="user-registration/user-registration.php"] span.deactivate a' ).addClass( 'hasNotice' );
			$( 'tr[id="user-registration-license-row"]' ).addClass( 'update user-registration-deactivation-notice' ).after( response  );
		}).fail( function( xhr ) {
			window.console.log( xhr.responseText );
		});
   });
});
