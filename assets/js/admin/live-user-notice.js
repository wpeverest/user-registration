jQuery(function ($) {
	'use strict';

	var URLiveUser = {
		init: function() {
			wp.heartbeat.interval( 'fast' );

			this.sendDataToHeartbeat();
			this.handleHeartbeatResponse();
		},

		sendDataToHeartbeat: function() {
			$( document ).on( 'heartbeat-send', function( event, data ) {
				data.user_registration_new_user_notice = true;
			} );
		},

		handleHeartbeatResponse: function() {
			$( document ).on( 'heartbeat-tick', function ( event, data ) {

				if ( typeof data.user_registration_new_user_count === 'undefined' ) {
					return;
				}

				var $user_menu = $( '#menu-users .wp-menu-name' );

				if( data.user_registration_new_user_count > 0 ) {
					if( $user_menu.find( '.user-registration' ).length > 0 ) {
						$user_menu.find( '.user-registration .user-count' ).text( data.user_registration_new_user_count );
					} else {
						$user_menu.append( '<span class="user-registration awaiting-mod"><span class="user-count">' + data.user_registration_new_user_count + '</span></span>' );
					}

					if( $( 'body' ).hasClass( 'users-php' ) ) {
						if( $( '#new-user-live-notice' ).length > 0 ) {
							$( '#new-user-live-notice' ).replaceWith( data.user_registration_new_user_notice );
						} else {
							$( '#wpbody-content .wrap .wp-header-end' ).after( data.user_registration_new_user_notice );
						}
					}
				} else {
					$user_menu.find( '.user-registration' ).remove();
					$( '#new-user-live-notice' ).remove();
				}

				wp.heartbeat.interval( 'standard' );
			});
		}
	};
	URLiveUser.init();
}(jQuery));
