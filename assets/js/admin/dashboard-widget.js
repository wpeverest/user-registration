jQuery(function ($) {

	$( '#ur-dashboard-widget-forms' ).on( 'change', function( e ) {

		var form_id = $('#ur-dashboard-widget-forms').val();
		var data = {
			action: 'user_registration_dashboard_widget',
			form_id: form_id,
			security: ur_widget_params.widget_noncee
		};

		$.post( ur_widget_params.ajax_url, data, function( response ) {

		}).fail( function( xhr ) {
			window.console.log( xhr.responseText );
		});
	});
});
