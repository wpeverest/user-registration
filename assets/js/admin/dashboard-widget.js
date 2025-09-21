jQuery(function ($) {

	$( document.body ).ready( function( e ) {
		// Update anchor href for li with class toplevel_page_user-registration
		$('li.toplevel_page_user-registration > a').attr('href', 'admin.php?page=user-registration');

		$('.ur-today-users, .ur-last-week-users, .ur-last-month-users, .ur-total-users').html('').html('<i>'+ur_widget_params.loading+'</i>' );
		$('#ur-dashboard-widget-forms').html('').append('<option>'+ur_widget_params.loading+'</option');

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
