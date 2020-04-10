/* global user_registration_settings_params */
(function ($) {

	// Allowed Screens
	$('select#user_registration_allowed_screens').change(function () {
		if ('specific' === $(this).val()) {
			$(this).closest('tr').next('tr').hide();
			$(this).closest('tr').next().next('tr').show();
		} else if ('all_except' === $(this).val()) {
			$(this).closest('tr').next('tr').show();
			$(this).closest('tr').next().next('tr').hide();
		} else {
			$(this).closest('tr').next('tr').hide();
			$(this).closest('tr').next().next('tr').hide();
		}
	}).change();

	// Color picker
	$('.colorpick').iris({
		change: function (event, ui) {
			$(this).parent().find('.colorpickpreview').css({ backgroundColor: ui.color.toString() });
		},
		hide: true,
		border: true
	}).click(function () {
		$('.iris-picker').hide();
		$(this).closest('td').find('.iris-picker').show();
	});

	$('body').click(function () {
		$('.iris-picker').hide();
	});

	$('.colorpick').click(function (event) {
		event.stopPropagation();
	});

	// Edit prompt
	$(function () {
		var changed = false;

		$('input, textarea, select, checkbox').change(function () {
			changed = true;
		});

		$('.ur-nav-tab-wrapper a').click(function () {
			if (changed) {
				window.onbeforeunload = function () {
					return user_registration_settings_params.i18n_nav_warning;
				};
			} else {
				window.onbeforeunload = '';
			}
		});

		$('.submit input').click(function () {
			window.onbeforeunload = '';
		});
	});

	// Select all/none
	$('.user-registration').on('click', '.select_all', function () {
		$(this).closest('td').find('select option').attr('selected', 'selected');
		$(this).closest('td').find('select').trigger('change');
		return false;
	});

	$('.user-registration').on('click', '.select_none', function () {
		$(this).closest('td').find('select option').removeAttr('selected');
		$(this).closest('td').find('select').trigger('change');
		return false;
	});

	// reCaptcha version selection
	var recaptcha_input_value = $('.user-registration').find('input[name="user_registration_integration_setting_recaptcha_version"]:checked').val();
	if (recaptcha_input_value != undefined) {
		handleReCaptchaHideShow(recaptcha_input_value);
	}

	$('.user-registration').on('change', 'input[name="user_registration_integration_setting_recaptcha_version"]', function () {
		handleReCaptchaHideShow($(this).val());
	});

	function handleReCaptchaHideShow(value) {
		if (value == 'v3') {
			$('#user_registration_integration_setting_recaptcha_site_key_v3').closest('tr').show();
			$('#user_registration_integration_setting_recaptcha_site_secret_v3').closest('tr').show();
			$('#user_registration_integration_setting_recaptcha_site_key').closest('tr').hide();
			$('#user_registration_integration_setting_recaptcha_site_secret').closest('tr').hide();
		} else {
			$('#user_registration_integration_setting_recaptcha_site_key_v3').closest('tr').hide();
			$('#user_registration_integration_setting_recaptcha_site_secret_v3').closest('tr').hide();
			$('#user_registration_integration_setting_recaptcha_site_key').closest('tr').show();
			$('#user_registration_integration_setting_recaptcha_site_secret').closest('tr').show();
		}
	}
	$( '.ur-redirect-to-login-page' ).ready( function() {

		var $url       = $( '.ur-redirect-to-login-page' ),
			$check    = $( '#user_registration_login_options_prevent_core_login' ),
			$redirect = $('#user_registration_login_options_login_redirect_url');

		if ( ! $check.attr( 'checked' ) ) {
			$url.val('').closest( '.single_select_page' ).css( 'display', 'none' );
		} else {
			$redirect.attr( 'required', true );
		}
	});

	$( '#user_registration_login_options_prevent_core_login' ).change( function() {
		var $url = $( '#user_registration_login_options_prevent_core_login' );

		$('.single_select_page').toggle();
		$( '#user_registration_login_options_login_redirect_url' ).attr( 'required', function() {
			return ( 'checked' === $url.attr( 'checked' ) ) ? 'required' : false;
		});
	});
})(jQuery);
