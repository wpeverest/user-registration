jQuery(function ($) {

	$( document.body ).ready( function( e ) {
		var wrap = $( '#wp-auth-check-wrap' );
		/**
		 * Hides the authentication form popup.
		 *
		 * @since 3.6.0
		 * @private
		 */
		function hide() {
			var adminpage = window.adminpage,
				wp        = window.wp;

			$( window ).off( 'beforeunload.wp-auth-check' );

			// When on the Edit Post screen, speed up heartbeat
			// after the user logs in to quickly refresh nonces.
			if ( ( adminpage === 'post-php' || adminpage === 'post-new-php' ) && wp && wp.heartbeat ) {
				wp.heartbeat.connectNow();
			}

			wrap.fadeOut( 200, function() {
				wrap.addClass( 'hidden' ).css( 'display', '' );
				$( '#wp-auth-check-frame' ).remove();
				$( 'body' ).removeClass( 'modal-open' );
			});
		}
		$('.ur-today-users, .ur-last-week-users, .ur-last-month-users, .ur-total-users').html('').html('<i>'+ur_widget_params.loading+'</i>' );
		$('#ur-dashboard-widget-forms').html('').append('<option>'+ur_widget_params.loading+'</option');
		$($(document).on("heartbeat-tick.wp-auth-check", function (event, data) {

			if ('wp-auth-check' in data) {
				if (data['wp-auth-check'] && !wrap.hasClass('hidden')) {
					hide();
				}
			}
		}));
		var data = {
			action: 'user_registration_dashboard_widget',
			security: ur_widget_params.widget_nonce
		};

		$.post( ur_widget_params.ajax_url, data, function( response ) {
			var forms = response.forms;

			$('#ur-dashboard-widget-forms').html('');
			$.each( forms, function( form_id, form_name ) {
				$('#ur-dashboard-widget-forms').append('<option value="'+ form_id +'">' + form_name + '</option');
			});

			$('#ur-dashboard-widget-forms').trigger('change')

		}).fail( function( xhr ) {
			window.console.log( xhr.responseText );
		});

		$( '#ur-dashboard-widget-forms' ).on('change', function() {
			$('.ur-today-users, .ur-last-week-users, .ur-last-month-users, .ur-total-users').html('').html('<i>'+ur_widget_params.loading+'</i>' );

			var form_id = $(this).val();
			data.form_id = form_id;

			$.post( ur_widget_params.ajax_url, data, function( response ) {

				$('.ur-today-users').html('').html( response.user_report.today_users );
				$('.ur-last-week-users').html('').html( response.user_report.last_week_users );
				$('.ur-last-month-users').html('').html( response.user_report.last_month_users );
				$('.ur-total-users').html('').html( response.user_report.total_users );

			}).fail( function( xhr ) {
				window.console.log( xhr.responseText );
			});
		});
	});
});
