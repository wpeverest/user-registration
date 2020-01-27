/* global  user_registration_params */
/* global  ur_google_recaptcha_code */
/* global  grecaptcha */
(function ($) {
	var user_registration = {
		$user_registration: $('.ur-frontend-form form.register'),
		init: function () {
			this.load_validation();
			this.init_inputMask();
			this.init_tiptip();

			// Inline validation
			this.$user_registration.on('input validate change', '.input-text, select, input:checkbox input:radio', this.validate_field);
		},
		init_inputMask: function () {
			if (typeof $.fn.inputmask !== 'undefined') {
				$('.ur-masked-input').inputmask();
			}
		},
		init_tiptip: function () {
			if( typeof tipTip !== 'undefined' ) {
				var tiptip_args = {
					'attribute': 'title',
					'fadeIn': 50,
					'fadeOut': 50,
					'delay': 200,
				};
				$('.user-registration-help-tip').tipTip(tiptip_args);
			}
		},
		load_validation: function () {
			if (typeof $.fn.validate === 'undefined') {
				return false;
			}

			// Validate email addresses.
			$.validator.methods.email = function (value, element) {
				/* https://stackoverflow.com/questions/2855865/jquery-validate-e-mail-address-regex */
				var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
				return this.optional(element) || pattern.test(value);
			};

			this.$user_registration.each(function () {
				var $this = $(this);

				$this.validate({
					errorClass: 'user-registration-error',
					validClass: 'user-registration-valid',
					errorPlacement: function (error, element) {
						if ( 'radio' === element.attr('type') || 'checkbox' === element.attr('type') || 'password' === element.attr('type') ) {
							element.parent().parent().parent().append(error);
						} else if ( element.is('select') && element.attr('class').match(/date-month|date-day|date-year/) ) {
							if (element.parent().find('label.user-registration-error:visible').length === 0) {
								element.parent().find('select:last').after(error);
							}
						} else if( element.hasClass('ur-smart-phone-field') ) {
							var wrapper = element.closest('p.form-row');
							wrapper.find('#' + element.data('id') + '-error').remove();
							wrapper.append(error);
						} else {
							if ( element.hasClass('urfu-file-input') ) {
								error.insertAfter(element.parent().parent());
							} else {
								error.insertAfter(element);
							}
						}
					},
					highlight: function (element, errorClass, validClass) {
						var $element = $(element),
							$parent = $element.closest('.form-row'),
							inputName = $element.attr('name');

					},
					unhighlight: function (element, errorClass, validClass) {
						var $element = $(element),
							$parent = $element.closest('.form-row'),
							inputName = $element.attr('name');

						if ($element.attr('type') === 'radio' || $element.attr('type') === 'checkbox') {
							$parent.find('input[name=\'' + inputName + '\']').addClass(validClass).removeClass(errorClass);
						} else {
							$element.addClass(validClass).removeClass(errorClass);
						}

						$parent.removeClass('user-registration-has-error');
					},
					submitHandler: function (form) {
						return false;
					}
				});
			});
		},
		validate_field: function (e) {

			// Validator messages.
			$.extend($.validator.messages, {
				required: user_registration_params.message_required_fields,
				url: user_registration_params.message_url_fields,
				email: user_registration_params.message_email_fields,
				number: user_registration_params.message_number_fields,
				confirmpassword: user_registration_params.message_confirm_password_fields,
			});

			var $this = $(this),
				$parent = $this.closest('.form-row'),
				validated = true,
				validate_required = $parent.is('.validate-required'),
				validate_email = $parent.is('.validate-email'),
				event_type = e.type;

			if ('input' === event_type) {
				$parent.removeClass('user-registration-invalid user-registration-invalid-required-field user-registration-invalid-email user-registration-validated');
			}

			if ('validate' === event_type || 'change' === event_type) {

				if (validate_required) {
					if ('checkbox' === $this.attr('type') && !$this.is(':checked')) {
						$parent.removeClass('user-registration-validated').addClass('user-registration-invalid user-registration-invalid-required-field');
						validated = false;
					} else if ($this.val() === '') {
						$parent.removeClass('user-registration-validated').addClass('user-registration-invalid user-registration-invalid-required-field');
						validated = false;
					}
				}

				if (validate_email) {
					if ($this.val()) {
						/* https://stackoverflow.com/questions/2855865/jquery-validate-e-mail-address-regex */
						var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);

						if (!pattern.test($this.val())) {
							$parent.removeClass('user-registration-validated').addClass('user-registration-invalid user-registration-invalid-email');
							validated = false;
						}
					}
				}

				if (validated) {
					$parent.removeClass('user-registration-invalid user-registration-invalid-required-field user-registration-invalid-email').addClass('user-registration-validated');
				}
			}
		}
	};

	user_registration.init();

	var ursL10n = user_registration_params.ursL10n;

	$.fn.ur_form_submission = function () {

		// traverse all nodes
		return this.each(function () {
			// express a single node as a jQuery object
			var $this = $(this);
			var available_field = [];
			var required_fields = user_registration_params.form_required_fields;
			var form = {
				init: function () {

				},
				get_form_data: function () {
					var this_instance = this;
					var form_data = [];
					var frontend_field = $this.closest('.ur-frontend-form').find('.ur-form-grid').find('.ur-frontend-field');
					var multi_value_field = new Array();
					$.each(frontend_field, function () {
						var field_name = $(this).attr('name');
						var single_field = $this.closest('.ur-frontend-form').find('.ur-form-grid').find('.ur-frontend-field[name="' + field_name + '"]');
						if (single_field.length < 2) {
							var single_data = this_instance.get_fieldwise_data($(this));
							form_data.push(single_data);
						} else {
							if ($.inArray(field_name, multi_value_field) < 0) {
								multi_value_field.push(field_name);
							}
						}
					});

					for (var multi_start = 0; multi_start < multi_value_field.length; multi_start++) {

						var field = $this.closest('.ur-frontend-form').find('.ur-form-grid').find('.ur-frontend-field[name="' + multi_value_field[multi_start] + '"]');

						var node_type = field.get(0).tagName.toLowerCase();
						var field_type = 'undefined' !== field.eq(0).attr('type') ? field.eq(0).attr('type') : 'null';
						var field_value = new Array();
						$.each(field, function () {
							var this_field = $(this);

							var this_field_value = '';

							switch (this_field.get(0).tagName.toLowerCase()) {

								case 'input':
									switch (field_type) {
										case 'checkbox':
										case 'radio':
											this_field_value = this_field.prop('checked') ? this_field.val() : '';
											break;
										default:
											this_field_value = this_field.val();
									}
									break;
								case 'select':
									this_field_value = this_field.val();
									break;
								case 'textarea':
									this_field_value = this_field.val();
									break;
								default:
							}
							if (this_field_value !== '') {
								field_value.push(this_field_value);
							}
						});

						if (field_type == 'checkbox') {
							var field_value_json = JSON.stringify(field_value);
						}
						else if (field_type == 'radio') {
							var field_value_json = field_value[0];
						} else {
							var field_value_json = field.val();
						}

						var single_form_field_name = multi_value_field[multi_start];
						single_form_field_name = single_form_field_name.replace('[]', '');

						var field_data = {
							value: field_value_json,
							field_type: field_type,
							label: field.eq(0).attr('data-label'),
							field_name: single_form_field_name,
						};

						form_data.push(field_data);
					}

					$(document).trigger("user_registration_frontend_form_data_filter", [form_data]);
					return form_data;
				},
				get_fieldwise_data: function (field) {
					var formwise_data = {};
					var node_type = field.get(0).tagName.toLowerCase();
					var field_type = 'undefined' !== field.attr('type') ? field.attr('type') : 'null';
					var textarea_type = field.get(0).className.split(" ")[0];
					formwise_data.value = '';
					switch (node_type) {
						case 'input':
							switch (field_type) {
								case 'checkbox':
								case 'radio':
									formwise_data.value = field.prop('checked') ? field.val() : '';
									break;
								default:
									formwise_data.value = field.val();
							}
							break;
						case 'select':
							formwise_data.value = field.val();
							break;
						case 'textarea':
							switch (textarea_type) {
								case 'wysiwyg':
									tinyMCE.triggerSave();
									formwise_data.value = field.val();
									break;
								default:
									formwise_data.value = field.val();
							}
							break;
						default:
					}

					$(document).trigger("user_registration_frontend_form_data_render", [field, formwise_data]);
					formwise_data.field_type = 'undefined' !== field.eq(0).attr('type') ? field.eq(0).attr('type') : 'null';
					if (field.attr('data-label') !== undefined) {
						formwise_data.label = field.attr('data-label');
					} else if ( field.prev().length && field.prev().get(0).tagName.toLowerCase() === 'label') {
						formwise_data.label = field.prev().text();
					} else {
						formwise_data.label = formwise_data.field_type;
					}
					if (field.attr('name') !== undefined && field.attr('name') !== '') {
						formwise_data.field_name = field.attr('name');
						formwise_data.field_name = formwise_data.field_name.replace('[]', '');
					} else {
						formwise_data.field_name = '';
					}
					if ($.inArray(formwise_data.field_name, $.trim(required_fields)) >= 0) {
						available_field.push(formwise_data.field_name);
					}
					return formwise_data;
				},
				show_message: function (message, type, $submit_node) {
					$submit_node.find('.ur-message').remove();
					var wrapper = $('<div class="ur-message user-registration-' + type + '" id="ur-submit-message-node"/>');
					//wrapper.addClass(type);
					wrapper.append(message);
					$submit_node.append(wrapper);

				}
			};
			var events = {
				init: function () {
					this.form_submit_event();
				},
				form_submit_event: function () {

					$('form.register').on('submit', function (event) {
						var $this = $(this);

						// Validator messages.
						$.extend($.validator.messages, {
							required: user_registration_params.message_required_fields,
							url: user_registration_params.message_url_fields,
							email: user_registration_params.message_email_fields,
							number: user_registration_params.message_number_fields,
							confirmpassword: user_registration_params.message_confirm_password_fields,
						});

						if ($this.find('.user-registration-password-strength').length > 0) {

							var current_strength = $this.find('.user-registration-password-strength').attr('data-current-strength');
							var min_strength = $this.find('.user-registration-password-strength').attr('data-min-strength');

							if (parseInt(current_strength, 0) < parseInt(min_strength, 0)) {

								if ( $this.find('#user_pass').val() != "" ) {
									$this.find( '#user_pass_error' ).remove();

									var error_msg_dom = '<label id="user_pass_error" class="user-registration-error" for="user_pass">' + ursL10n.password_strength_error + '.</label>';
									$this.find('.user-registration-password-hint').after( error_msg_dom );
									$this.find('#user_pass').attr('aria-invalid',true);
									$this.find('#user_pass').focus();
								}

								return false;
							}
						}

						var $el = $( '.ur-smart-phone-field' );

						if( 'true' === $el.attr('aria-invalid')){
							var wrapper = $el.closest('p.form-row');
							wrapper.find('#' + $el.data('id') + '-error').remove();
							var phone_error_msg_dom = '<label id="' + $el.data('id') + '-error' + '" class="user-registration-error" for="' + $el.data('id') + '">' + user_registration_params.message_validate_phone_number + '</label>';
							wrapper.append(phone_error_msg_dom);
							wrapper.find('#' + $el.data('id')).attr('aria-invalid', true);
							return true;
						}

						var exist_detail = $('.uraf-profile-picture-upload').find('.uraf-profile-picture-file-error').length;

						if( 0 === exist_detail ){
							var profile = $('.uraf-profile-picture-upload').find('.uraf-profile-picture-input')
							var wrapper = $('.uraf-profile-picture-upload');
							wrapper.find('#' + profile.attr('id') + '-error').remove();
							var error_message = '<label id="' + profile.attr('id') + '-error' + '" class="user-registration-error" for="' + profile.attr('id') + '">' + user_registration_params.message_required_fields + '</label>';
							wrapper.find('button.wp_uraf_profile_picture_upload').after(error_message);
						}

						if (!$this.valid()) {
							return;
						}

						event.preventDefault();
						$this.find( '.ur-submit-button' ).prop( 'disabled', true );
						var form_data;
						var form_id = 0;
						var form_nonce = '0';
						var captchaResponse = $this.find('#g-recaptcha-response').val();

						try {
							form_data = JSON.stringify(form.get_form_data());
						} catch (ex) {
							form_data = '';
						}

						if ($(this).closest('form').find('input[name="ur-user-form-id"]').length === 1) {
							form_id = $(this).closest('form').find('input[name="ur-user-form-id"]').val();
						}

						if ($(this).closest('form').find('input[name="ur_frontend_form_nonce"]').length === 1) {
							form_nonce = $(this).closest('form').find('input[name="ur_frontend_form_nonce"]').val();
						}

						var data = {
							action: 'user_registration_user_form_submit',
							security: user_registration_params.user_registration_form_data_save,
							form_data: form_data,
							captchaResponse: captchaResponse,
							form_id: form_id,
							ur_frontend_form_nonce: form_nonce
						};

						$(document).trigger("user_registration_frontend_before_form_submit", [data, $this]);

						if ( 'undefined' !== typeof ( ur_google_recaptcha_code ) ) {

							if ( '1' === ur_google_recaptcha_code.is_captcha_enable ) {

								var captchaResponse = $this.find('#g-recaptcha-response').val();

								if (0 === captchaResponse.length) {

									form.show_message('<p>' + ursL10n.captcha_error + '</p>', 'error', $this);
									return;
								}

								if (ur_google_recaptcha_code.version == 'v3') {
									request_recaptcha_token();
								} else {
									grecaptcha.reset();
								}
							}
						}

						$this.find('.ur-submit-button').find('span').addClass('ur-front-spinner');

						$.ajax({
							url: user_registration_params.ajax_url,
							data: data,
							type: 'POST',
							async: true,
							complete: function ( ajax_response ) {

								$this.find('.ur-submit-button').find('span').removeClass('ur-front-spinner');
								var redirect_url = $this.find('input[name="ur-redirect-url"]').val();

								var message = $('<ul class=""/>');
								var type = 'error';

								try {

									var response = $.parseJSON(ajax_response.responseText);

									if (typeof response.success !== 'undefined' && response.success === true && typeof response.data.paypal_redirect !== 'undefined') {
										window.location = response.data.paypal_redirect;
									}

									if (typeof response.success !== 'undefined' && response.success === true) {
										type = 'message';
									}

									if (type === 'message') {

										$('.user-registration-password-hint').remove();
										$('.user-registration-password-strength').remove();

										if (user_registration_params.login_option == 'admin_approval') {
											message.append('<li>' + ursL10n.user_under_approval + '</li>');
										}
										else if (user_registration_params.login_option == 'email_confirmation') {
											message.append('<li>' + ursL10n.user_email_pending + '</li>');
										}
										else if (user_registration_params.login_option == 'payment') {
											message.append('<li>' + response.data.message + '</li>');
										}
										else {
											message.append('<li>' + ursL10n.user_successfully_saved + '</li>');
										}

										$this[0].reset();
										jQuery('#billing_country').trigger('change');
										jQuery('#shipping_country').trigger('change');

										if ( 'undefined' !== typeof redirect_url && redirect_url !== '') {
											window.setTimeout(function () {
												window.location = redirect_url;
											}, 1000);
										} else {

											if (typeof response.data.auto_login !== 'undefined' && response.data.auto_login) {
												location.reload();
											}
										}
									} else if (type === 'error') {
										if (typeof response.data.message === 'object') {
											$.each(response.data.message, function (index, value) {
												message.append('<li>' + value + '</li>');
											});
										} else {
											message.append('<li>' + response.data.message + '</li>');
										}
									}
								} catch (e) {
									message.append('<li>' + e.message + '</li>');
								}

								form.show_message(message, type, $this);

								$(document).trigger("user_registration_frontend_after_ajax_complete", [ajax_response.responseText, type, $this]);
								$this.find( '.ur-submit-button' ).prop( 'disabled', false );
							}
						});
					});
				}
			};
			form.init();
			events.init();
		});
	};

	$(function () {
		$('form.register').ur_form_submission();
		var date_selector = $('.ur-frontend-form  input[type="date"]');
		if (date_selector.length > 0) {
			date_selector.addClass('flatpickr-field').attr('type', 'text').flatpickr({
				disableMobile: true
			});
		}

		$("form.register").on("focusout", "#user_pass", function() {
			$this = $('#user_pass');
			var enable_strength_password  = $this.closest( 'form' ).attr( 'data-enable-strength-password' );

			if ( 'yes' === enable_strength_password ) {
				var wrapper                   = $this.closest('form');
				var minimum_password_strength = wrapper.attr( 'data-minimum-password-strength' );
				var blacklistArray            = wp.passwordStrength.userInputBlacklist();

				blacklistArray.push( wrapper.find( 'input[data-id="user_email"]' ).val() ); // Add email address in blacklist.
				blacklistArray.push( wrapper.find( 'input[data-id="user_login"]' ).val() ); // Add username in blacklist.

				var strength = wp.passwordStrength.meter( $this.val(), blacklistArray );
				if( strength < minimum_password_strength ) {
					if( wrapper.find('input[data-id="user_pass"]').val() !== "" ){
						wrapper.find( '#user_pass_error' ).remove();
						var error_msg_dom = '<label id="user_pass_error" class="user-registration-error" for="user_pass">' + ursL10n.password_strength_error +'.</label>';
						wrapper.find('.user-registration-password-hint').after( error_msg_dom );
					}
				}
			}
		});
	});

	$(function () {
		request_recaptcha_token();
	});

	$( document ).on( 'click', '.password_preview', function( e ) {
		e.preventDefault();
		var current_task = ( $(this).hasClass( 'dashicons-hidden' ) ) ? 'show' : 'hide';
		var $password_field = $(this).closest( '.user-registration-form-row' ).find( 'input[name="password"]' );

		if( $password_field.length === 0 ) {
			$password_field = $(this).closest( '.field-user_pass' ).find( 'input[name="user_pass"]' );
		}
		if( $password_field.length === 0 ) {
			$password_field = $(this).closest( '.field-user_confirm_password' ).find( 'input[name="user_confirm_password"]' );
		}
		if( $password_field.length === 0 ) {
			$password_field = $(this).closest( '.field-user_pass' ).find( 'input[name="user_registration_user_pass"]' );
		}
		if( $password_field.length === 0 ) {
			$password_field = $(this).closest( '.field-user_confirm_password' ).find( 'input[name="user_registration_user_confirm_password"]' );
		}

		if( $password_field.length > 0 ) {
			switch( current_task ) {
				case 'show':
					$password_field.attr( 'type', 'text' );
					$(this).removeClass( 'dashicons-hidden' ).addClass( 'dashicons-visibility' );
					$(this).attr( 'title', ursL10n.hide_password_title );
					break;
				case'hide':
					$password_field.attr( 'type', 'password' );
					$(this).removeClass( 'dashicons-visibility' ).addClass( 'dashicons-hidden' );
					$(this).attr( 'title', ursL10n.show_password_title );
					break;
			}
		}
	} );

}(jQuery));

var google_recaptcha_user_registration;
var onloadURCallback = function () {

	var node_recaptcha_register = jQuery('.ur-frontend-form').find('form.register #ur-recaptcha-node #node_recaptcha_register').length;

	if (node_recaptcha_register !== 0) {
		google_recaptcha_user_registration = grecaptcha.render('node_recaptcha_register', {
			'sitekey': ur_google_recaptcha_code.site_key,
			'theme': 'light',
			'style': 'transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;'
		});
	}

	var node_recaptcha_login = jQuery('.ur-frontend-form').find('form.login .ur-form-row .ur-form-grid #ur-recaptcha-node #node_recaptcha_login').length;

	if (node_recaptcha_login !== 0) {
		grecaptcha.render('node_recaptcha_login', {
			'sitekey': ur_google_recaptcha_code.site_key,
			'theme': 'light',
			'style': 'transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;'
		});
	}
};

function request_recaptcha_token() {
	var node_recaptcha_register = jQuery('.ur-frontend-form').find('form.register #ur-recaptcha-node #node_recaptcha_register.g-recaptcha-v3').length;
	if (node_recaptcha_register !== 0) {
		grecaptcha.ready(function () {
			grecaptcha.execute(ur_google_recaptcha_code.site_key, { action: 'register' }).then(function (token) {
				jQuery('form.register').find('#g-recaptcha-response').text(token);
			});
		});
	}
	var node_recaptcha_login = jQuery('.ur-frontend-form').find('form.login .ur-form-row .ur-form-grid #ur-recaptcha-node #node_recaptcha_login.g-recaptcha-v3').length;
	if (node_recaptcha_login !== 0) {
		grecaptcha.ready(function () {
			grecaptcha.execute(ur_google_recaptcha_code.site_key, { action: 'login' }).then(function (token) {
				jQuery('form.login').find('#g-recaptcha-response').text(token);
			});
		});
	}
};
