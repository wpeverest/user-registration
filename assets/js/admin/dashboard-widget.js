jQuery(function ($) {

	$( '#ur-dashboard-widget-forms' ).on( 'change', function( e ) {

		var form_id = $('#ur-dashboard-widget-forms').val();
		var data = {
			action: 'user_registration_dashboard_widget',
			form_id: form_id,
			security: ur_widget_params.widget_noncee
		};

		$.post( ur_widget_params.ajax_url, data, function( response ) {
			$('.ur-today-users').html('').html( response.today_users );
			$('.ur-last-week-users').html('').html( response.today_users );
			$('.ur-last-month-users').html('').html( response.today_users );
			$('.ur-total-users').html('').html( response.today_users );

		}).fail( function( xhr ) {
			window.console.log( xhr.responseText );
		});
	});
});
