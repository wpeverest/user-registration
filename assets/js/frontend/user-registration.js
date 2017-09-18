/* global  user_registration_params */
/* global  ur_google_recaptcha_code */
/* global  grecaptcha */
(function ( $ ) {
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
					$.each(frontend_field, function () {
						var single_data = this_instance.get_fieldwise_data($(this));
						form_data.push(single_data);
					});
					$(document).trigger("user_registration_frontend_form_data_filter", [ form_data ]);
					return form_data;
				},
				get_fieldwise_data: function ( field ) {
					var formwise_data = {};
					var node_type = field.get(0).tagName.toLowerCase();
					var field_type = 'undefined' !== field.attr('type') ? field.attr('type') : 'null';
					formwise_data.value = '';
					switch ( node_type ) {
						case 'input':
							switch ( field_type ) {
								case 'checkbox':
								case 'radio':
									formwise_data.value = field.prop('checked') ? field.val() : 0;
									break;
								default:
									formwise_data.value = field.val();

							}
							break;
						case 'select':
							formwise_data.value = field.val();
							break;
						case 'textarea':
							formwise_data.value = field.val();
							break;
						default:
					}

					$(document).trigger("user_registration_frontend_form_data_render", [ field, formwise_data ]);
					formwise_data.field_type = field.attr('id').replace('ur-input-type-', '');
					if ( field.attr('data-label') !== undefined ) {
						formwise_data.label = field.attr('data-label');
					} else if ( field.prev().get(0).tagName.toLowerCase() === 'label' ) {
						formwise_data.label = field.prev().text();
					} else {
						formwise_data.label = formwise_data.field_type;
					}
					if ( field.attr('name') !== undefined && field.attr('name') !== '' ) {
						formwise_data.field_name = field.attr('name');
					} else {
						formwise_data.field_name = '';
					}
					if ( $.inArray(formwise_data.field_name, $.trim(required_fields)) >= 0 ) {
						available_field.push(formwise_data.field_name);
					}
					return formwise_data;
				},
				show_message: function ( message, type, $submit_node ) {
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
					$this.on('submit', function ( event ) {
						event.preventDefault();
						var form_data;
						try {
							form_data = JSON.stringify(form.get_form_data());
						} catch ( ex ) {
							form_data = '';
						}
						var form_id = 0;
						if ( $(this).closest('form').find('input[name="ur-user-form-id"]').length === 1 ) {
							form_id = $(this).closest('form').find('input[name="ur-user-form-id"]').val();
						}
						var form_nonce = '0';
						if ( $(this).closest('form').find('input[name="ur_frontend_form_nonce"]').length === 1 ) {
							form_nonce = $(this).closest('form').find('input[name="ur_frontend_form_nonce"]').val();
						}
						var data = {
							action: 'user_registration_user_form_submit',
							security: user_registration_params.user_registration_form_data_save,
							form_data: form_data,
							form_id: form_id,
							ur_frontend_form_nonce: form_nonce
						};

						$(document).trigger("user_registration_frontend_before_form_submit", [ data, $this ]);

						if ( 'undefined' !== typeof (ur_google_recaptcha_code) ) {

							if ( '1' === ur_google_recaptcha_code.is_captcha_enable ) {
								var captchResponse = $this.find('#g-recaptcha-response').val();

								if ( 0 === captchResponse.length ) {

									form.show_message('<p>' + ursL10n.captcha_error + '</p>', 'error', $this);

									return;
								}
								grecaptcha.reset();
							}
						}

						$this.find('.ur-submit-button').find('span').addClass('ur-front-spinner');

						$.ajax({
							url: user_registration_params.ajax_url,
							data: data,
							type: 'POST',
							async: true,

							beforeSend: function () {
							},
							complete: function ( ajax_response ) {

								$this.find('.ur-submit-button').find('span').removeClass('ur-front-spinner');

								var message = $('<ul class=""/>');
								var type = 'error';
								try {
									var response = $.parseJSON(ajax_response.responseText);
									if ( typeof response.success !== 'undefined' && response.success === true ) {
										type = 'message';
									}
									if ( typeof response.data.message === 'object' ) {
										$.each(response.data.message, function () {
											$('<li/>').text(this).appendTo(message);
										});
									}
									if ( type === 'message' ) {

										message.append('<li>' + ursL10n.user_successfully_saved + '</li>');
										$this[ 0 ].reset();

										$('.user-registration-password-hint').remove();
										$('.user-registration-password-strength').remove();

										if ( user_registration_params.redirect_url !== '' ) {
											window.setTimeout(function () {
												window.location = user_registration_params.redirect_url;
											}, 1000);
										} else {

											if ( typeof response.data.auto_login !== 'undefined' && response.data.auto_login ) {
												location.reload();
											}
										}

									}
								} catch ( e ) {
									//message.addClass(type);
									message.append('<li>' + e.message + '</li>');
								}
								//message.addClass(type);
								form.show_message(message, type, $this);

								$(document).trigger("user_registration_frontend_after_ajax_complete", [ ajax_response.responseText, type, $this ]);

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
		$('.ur-frontend-form form.register').ur_form_submission();
	});

}(jQuery));

var google_recaptcha_user_registration;
var onloadURCallback = function () {

	google_recaptcha_user_registration = grecaptcha.render('node_recaptcha', {
		'sitekey': ur_google_recaptcha_code.site_key,
		'theme': 'light',
		'style': 'transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;'

	});


};
