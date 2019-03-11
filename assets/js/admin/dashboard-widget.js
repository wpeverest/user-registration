jQuery(function ($) {

	$( '#ur-dashboard-widget-forms' ).on( 'change', function( e ) {
			$('.ur-yesterday-users').html('').html('<i>'+ur_widget_params.loading+'</i>' );
			$('.ur-last-week-users').html('').html( '<i>'+ur_widget_params.loading+'</i>' );
			$('.ur-last-month-users').html('').html( '<i>'+ur_widget_params.loading+'</i>' );
			$('.ur-total-users').html('').html( '<i>'+ur_widget_params.loading+'</i>' );

		var form_id = $('#ur-dashboard-widget-forms').val();
		var data = {
			action: 'user_registration_dashboard_widget',
			form_id: form_id,
			security: ur_widget_params.widget_nonce
		};

		$.post( ur_widget_params.ajax_url, data, function( response ) {
			console.log( response );
			$('.ur-yesterday-users').html('').html( response.yesterday_users );
			$('.ur-last-week-users').html('').html( response.last_week_users );
			$('.ur-last-month-users').html('').html( response.last_month_users );
			$('.ur-total-users').html('').html( response.total_users );

		}).fail( function( xhr ) {
			window.console.log( xhr.responseText );
		});
	});
});
