/* global  user_registration_params */
(function ($) {
	var user_registration_form_init = function () {
		var ursL10n = user_registration_params.ursL10n;

		$.fn.ur_form_submission = function () {
			// traverse all nodes
			return this.each(function () {
				// express a single node as a jQuery object
				var $this = $(this);
				var available_field = [];
				var required_fields =
					user_registration_params.form_required_fields;

				var form = {
					init: function () {},
					get_form_data: function (form_id) {
						if (
							form_id ===
								$this.closest(".ur-frontend-form").attr("id") ||
							$(".ur-frontend-form")
								.find("form.edit-profile")
								.hasClass("user-registration-EditProfileForm")
						) {
							var this_instance = this;
							var form_data = [];
							var frontend_field = form.separate_form_handler("");

							var multi_value_field = new Array();
							$.each(frontend_field, function () {
								var field_name = $(this).attr("name");
								var single_field = form.separate_form_handler(
									'[name="' + field_name + '"]'
								);

								if (single_field.length < 2) {
									var single_data =
										this_instance.get_fieldwise_data(
											$(this)
										);
									var invite_code =
										document.querySelector(
											".field-invite_code"
										);

									if (
										"invite_code" === single_data.field_name
									) {
										if (
											"none" !== invite_code.style.display
										) {
											form_data.push(single_data);
										}
									} else {
										form_data.push(single_data);
									}
								} else {
									if (
										$.inArray(
											field_name,
											multi_value_field
										) < 0
									) {
										multi_value_field.push(field_name);
									}
								}
							});

							for (
								var multi_start = 0;
								multi_start < multi_value_field.length;
								multi_start++
							) {
								var field = form.separate_form_handler(
									'[name="' +
										multi_value_field[multi_start] +
										'"]'
								);

								var node_type = field
									.get(0)
									.tagName.toLowerCase();

								var field_type =
									"undefined" !== field.eq(0).attr("type")
										? field.eq(0).attr("type")
										: "null";

								var field_value = new Array();
								$.each(field, function () {
									var this_field = $(this);

									var this_field_value = "";

									switch (
										this_field.get(0).tagName.toLowerCase()
									) {
										case "input":
											switch (field_type) {
												case "checkbox":
												case "radio":
													this_field_value =
														this_field.prop(
															"checked"
														)
															? this_field.val()
															: "";
													break;
												default:
													this_field_value =
														this_field.val();
											}
											break;
										case "select":
											this_field_value = this_field.val();
											break;
										case "textarea":
											this_field_value = this_field.val();
											break;
										default:
									}
									if (this_field_value !== "") {
										field_value.push(this_field_value);
									}
								});

								if (field_type == "checkbox") {
									if (
										field.eq(0).attr("data-field") ==
										"multiple_choice"
									) {
										var multi_choice = field_value;
										var field_value_json = 0;
										for (
											var i = 0;
											i < multi_choice.length;
											i++
										) {
											field_value_json +=
												multi_choice[i] << 0;
										}
									} else {
										var field_value_json =
											JSON.stringify(field_value);
									}
								} else if (field_type == "radio") {
									var field_value_json = field_value[0];
								} else {
									var field_value_json = field.val();
								}

								var single_form_field_name =
									multi_value_field[multi_start];
								single_form_field_name =
									single_form_field_name.replace("[]", "");

								var field_data = {
									value: field_value_json,
									field_type: field_type,
									label: field.eq(0).attr("data-label"),
									field_name: single_form_field_name,
								};

								form_data.push(field_data);
							}

							$(document).trigger(
								"user_registration_frontend_form_data_filter",
								[form_data]
							);
							return form_data;
						}
					},
					get_fieldwise_data: function (field) {
						var formwise_data = {};
						var node_type = field.get(0).tagName.toLowerCase();
						var field_name =
							"undefined" !== field.attr("name")
								? field.attr("name")
								: "null";
						var phone_id = [];

						$(".field-phone").each(function () {
							var phone_field_id = $(this)
								.find(".form-row")
								.attr("id");
							// Check if smart phone field is enabled.
							if (
								$(this)
									.find(".form-row")
									.find("#" + phone_field_id)
									.hasClass("ur-smart-phone-field")
							) {
								phone_id.push(
									$(this).find(".form-row").attr("id")
								);
							}
						});
						var field_type =
							"undefined" !== field.attr("type")
								? field.attr("type")
								: "null";
						var textarea_type = field
							.get(0)
							.className.split(" ")[0];
						formwise_data.value = "";
						switch (node_type) {
							case "input":
								switch (field_type) {
									case "checkbox":
									case "radio":
										formwise_data.value = field.prop(
											"checked"
										)
											? field.val()
											: "";
										break;
									default:
										formwise_data.value = field.val();
								}

								if (ur_includes(phone_id, field_name)) {
									formwise_data.value = field
										.siblings('input[type="hidden"]')
										.val();
								}
								break;
							case "select":
								formwise_data.value = field.val();
								break;
							case "textarea":
								switch (textarea_type) {
									case "wysiwyg":
										tinyMCE.triggerSave();
										formwise_data.value = field.val();
										break;
									default:
										formwise_data.value = field.val();
								}
								break;
							default:
						}

						$(document).trigger(
							"user_registration_frontend_form_data_render",
							[field, formwise_data]
						);
						formwise_data.field_type =
							"undefined" !== field.eq(0).attr("type")
								? field.eq(0).attr("type")
								: "null";
						if (field.attr("data-label") !== undefined) {
							formwise_data.label = field.attr("data-label");
						} else if (
							field.prev().length &&
							field.prev().get(0).tagName.toLowerCase() ===
								"label"
						) {
							formwise_data.label = field.prev().text();
						} else {
							formwise_data.label = formwise_data.field_type;
						}
						if (
							field.attr("name") !== undefined &&
							field.attr("name") !== ""
						) {
							formwise_data.field_name = field.attr("name");
							formwise_data.field_name =
								formwise_data.field_name.replace("[]", "");
						} else {
							formwise_data.field_name = "";
						}
						if (
							$.inArray(
								formwise_data.field_name,
								required_fields.join(",").trim()
							) >= 0
						) {
							available_field.push(formwise_data.field_name);
						}
						return formwise_data;
					},
					show_message: function (
						message,
						type,
						$submit_node,
						position
					) {
						$submit_node
							.closest(".user-registration")
							.find(".ur-message")
							.remove();

						// Check if the form is edit-profile form.
						if (
							$(".ur-frontend-form")
								.find("form.edit-profile")
								.hasClass("user-registration-EditProfileForm")
						) {
							var wrapper = $(
								'<div class="user-registration-' + type + '"/>'
							);
							wrapper.append(message);
							var my_account_selector = $(
								".user-registration"
							).find(".user-registration-MyAccount-navigation");
							if (my_account_selector.length) {
								wrapper.insertBefore(
									".user-registration-MyAccount-navigation"
								);
							} else {
								wrapper.insertBefore(".ur-frontend-form");
							}
						} else {
							var wrapper = $(
								'<div class="ur-message user-registration-' +
									type +
									'" id="ur-submit-message-node"/>'
							);
							wrapper.append(message);

							// Check the position set by the admin and append message accordingly.
							if ("1" === position) {
								$submit_node.append(wrapper);
							} else {
								$submit_node.prepend(wrapper);
							}
						}
					},
					/**
					 * Handles registration form submit and edit-profile form submit instances separately.
					 *
					 * @since  1.8.5
					 *
					 * @param element Element to search for in the template.
					 */
					separate_form_handler: function (element) {
						var field = "";

						// Check if the form is edit-profile form.
						if (
							$(".ur-frontend-form")
								.find("form.edit-profile")
								.hasClass("user-registration-EditProfileForm")
						) {
							field = $this
								.find(".user-registration-profile-fields")
								.find(".ur-edit-profile-field" + element);
						} else {
							field = $this
								.closest(".ur-frontend-form")
								.find(".ur-form-grid")
								.find(".ur-frontend-field" + element);
						}

						return field;
					},
					missing_attachment_handler: function name(file_upload) {
						var file_upload_field_array = [];

						//Check if file upload field exists.
						if (1 <= file_upload.length) {
							file_upload.each(function () {
								var file_upload_id = $(this).attr("id");

								if (
									$.inArray(
										file_upload_id,
										file_upload_field_array
									) === -1
								) {
									file_upload_field_array.push(
										file_upload_id
									);
								}
							});

							for (
								var i = 0;
								i < file_upload_field_array.length;
								i++
							) {
								var file_upload_val = $(
									"#" + file_upload_field_array[i]
								)
									.val()
									.split(",");

								for (
									var j = file_upload_val.length;
									j >= 0;
									j--
								) {
									if (!$.isNumeric(file_upload_val[j])) {
										file_upload_val.splice(j, 1);
									}
								}
								$("#" + file_upload_field_array[i]).val(
									file_upload_val
								);
							}
						}
					},
				};

				var events = {
					init: function () {
						this.form_submit_event();
						this.edit_profile_event();
					},
					/**
					 * Handles registration ajax form submission event.
					 *
					 */
					form_submit_event: function () {
						$(".ur-frontend-form").each(function () {
							var $registration_form = $(this);

							$registration_form
								.find("form.register")
								.off("submit")
								.on("submit", function (event) {
									event.preventDefault();

									var $this = $(this);

									// Validator messages.
									$.extend($.validator.messages, {
										required:
											user_registration_params.message_required_fields,
										url: user_registration_params.message_url_fields,
										email: user_registration_params.message_email_fields,
										number: user_registration_params.message_number_fields,
										confirmpassword:
											user_registration_params.message_confirm_password_fields,
									});

									if (
										$this.find(
											".user-registration-password-strength"
										).length > 0
									) {
										var current_strength = $this
											.find(
												".user-registration-password-strength"
											)
											.attr("data-current-strength");
										var min_strength = $this
											.find(
												".user-registration-password-strength"
											)
											.attr("data-min-strength");

										if (
											parseInt(current_strength, 0) <
											parseInt(min_strength, 0)
										) {
											if (
												$this
													.find("#user_pass")
													.val() != ""
											) {
												$this
													.find("#user_pass_error")
													.remove();

												var error_msg_dom =
													'<label id="user_pass_error" class="user-registration-error" for="user_pass">' +
													ursL10n.password_strength_error +
													".</label>";
												$this
													.find(
														".user-registration-password-hint"
													)
													.after(error_msg_dom);
												$this
													.find("#user_pass")
													.attr("aria-invalid", true);
												$this
													.find("#user_pass")
													.trigger("focus");
											}

											return false;
										}
									}

									var $el = $this.find(
										".ur-smart-phone-field"
									);

									if ("true" === $el.attr("aria-invalid")) {
										var wrapper = $el.closest("p.form-row");
										wrapper
											.find(
												"#" + $el.data("id") + "-error"
											)
											.remove();
										var phone_error_msg_dom =
											'<label id="' +
											$el.data("id") +
											"-error" +
											'" class="user-registration-error" for="' +
											$el.data("id") +
											'">' +
											user_registration_params.message_validate_phone_number +
											"</label>";
										wrapper.append(phone_error_msg_dom);
										wrapper
											.find("#" + $el.data("id"))
											.attr("aria-invalid", true);
										return true;
									}

									// Remove word added by form filler in file upload field during submission
									var file_upload =
										$this.find(".urfu-file-input");

									form.missing_attachment_handler(
										file_upload
									);

									var exist_detail = $this
										.find(".uraf-profile-picture-upload")
										.find(
											".user-registration-error"
										).length;

									if (1 === exist_detail) {
										var profile = $this
											.find(
												".uraf-profile-picture-upload"
											)
											.find(
												".uraf-profile-picture-input"
											);
										var wrapper = $this.find(
											".uraf-profile-picture-upload"
										);
										wrapper
											.find(
												"#" +
													profile.attr("name") +
													"-error"
											)
											.remove();
										wrapper
											.find(
												".uraf-profile-picture-file-error"
											)
											.remove();
										var error_message =
											'<label id="' +
											profile.attr("name") +
											"-error" +
											'" class="user-registration-error" for="' +
											profile.attr("name") +
											'">' +
											user_registration_params.message_required_fields +
											"</label>";
										wrapper
											.find(
												"button.wp_uraf_profile_picture_upload"
											)
											.after(error_message);
									}

									if (!$this.valid()) {
										return;
									}

									$this
										.find(".ur-submit-button")
										.prop("disabled", true);
									var form_data;
									var form_id = 0;
									var form_nonce = "0";
									var captchaResponse = "";
									if (
										"hcaptcha" ===
										user_registration_params.recaptcha_type
									) {
										captchaResponse = $this
											.find('[name="h-captcha-response"]')
											.val();
									} else {
										captchaResponse = $this
											.find(
												'[name="g-recaptcha-response"]'
											)
											.val();
									}

									try {
										form_data = JSON.stringify(
											form.get_form_data(
												$this
													.closest(
														".ur-frontend-form"
													)
													.attr("id")
											)
										);
									} catch (ex) {
										form_data = "";
									}

									if (
										$(this)
											.closest("form")
											.find(
												'input[name="ur-user-form-id"]'
											).length === 1
									) {
										form_id = $(this)
											.closest("form")
											.find(
												'input[name="ur-user-form-id"]'
											)
											.val();
									}

									if (
										$(this)
											.closest("form")
											.find(
												'input[name="ur_frontend_form_nonce"]'
											).length === 1
									) {
										form_nonce = $(this)
											.closest("form")
											.find(
												'input[name="ur_frontend_form_nonce"]'
											)
											.val();
									}

									var data = {
										action: "user_registration_user_form_submit",
										security:
											user_registration_params.user_registration_form_data_save,
										form_data: form_data,
										captchaResponse: captchaResponse,
										form_id: form_id,
										ur_frontend_form_nonce: form_nonce,
									};

									var $error_message = {};
									$(document).trigger(
										"user_registration_frontend_before_form_submit",
										[data, $this, $error_message]
									);

									if (
										"undefined" !==
											typeof $error_message.message &&
										"" !== $error_message.message
									) {
										form.show_message(
											"<p>" +
												$error_message.message +
												"</p>",
											"error",
											$this,
											"1"
										);
										$this
											.find(".ur-submit-button")
											.prop("disabled", false);
										return;
									}

									$this
										.find(".ur-submit-button")
										.find("span")
										.addClass("ur-front-spinner");

									$.ajax({
										url: user_registration_params.ajax_url,
										data: data,
										type: "POST",
										async: true,
										complete: function (ajax_response) {
											var ajaxFlag = [];
											ajaxFlag["status"] = false;
											$(document).trigger(
												"user_registration_frontend_before_ajax_complete_success_message",
												[ajax_response, ajaxFlag]
											);
											if (!ajaxFlag["status"]) {
												$this
													.find(".ur-submit-button")
													.find("span")
													.removeClass(
														"ur-front-spinner"
													);
												var redirect_url = $this
													.find(
														'input[name="ur-redirect-url"]'
													)
													.val();

												var message =
													$('<ul class=""/>');
												var type = "error";

												try {
													var response = JSON.parse(
														ajax_response.responseText
													);

													if (
														typeof response.success !==
															"undefined" &&
														response.success ===
															true &&
														typeof response.data
															.paypal_redirect !==
															"undefined"
													) {
														window.location =
															response.data.paypal_redirect;
													}

													if (
														typeof response.success !==
															"undefined" &&
														response.success ===
															true
													) {
														type = "message";
													}

													if (type === "message") {
														$this
															.find(
																".user-registration-password-hint"
															)
															.remove();
														$this
															.find(
																".user-registration-password-strength"
															)
															.remove();

														if (
															response.data
																.form_login_option ==
															"admin_approval"
														) {
															message.append(
																"<li>" +
																	ursL10n.user_under_approval +
																	"</li>"
															);
														} else if (
															response.data
																.form_login_option ==
																"email_confirmation" ||
															response.data
																.form_login_option ==
																"admin_approval_after_email_confirmation"
														) {
															message.append(
																"<li>" +
																	ursL10n.user_email_pending +
																	"</li>"
															);
														} else if (
															response.data
																.form_login_option ==
															"payment"
														) {
															message.append(
																"<li>" +
																	response
																		.data
																		.message +
																	"</li>"
															);
														} else {
															message.append(
																"<li>" +
																	(typeof response
																		.data
																		.message ===
																		"undefined")
																	? ursL10n.user_successfully_saved
																	: response
																			.data
																			.message +
																			"</li>"
															);
														}

														if (
															"undefined" !==
															typeof response.data
																.auto_password_generation_success_message
														) {
															message.append(
																"<li>" +
																	response
																		.data
																		.auto_password_generation_success_message +
																	"</li>"
															);
														}

														$this[0].reset();
														jQuery(
															"#billing_country"
														).trigger("change");
														jQuery(
															"#shipping_country"
														).trigger("change");

														if (
															"undefined" !==
															typeof response.data
																.role_based_redirect_url
														) {
															redirect_url =
																response.data
																	.role_based_redirect_url;
														}

														if (
															"undefined" !==
																typeof redirect_url &&
															redirect_url !== ""
														) {
															window.setTimeout(
																function () {
																	window.location =
																		redirect_url;
																},
																1000
															);
														} else {
															if (
																typeof response
																	.data
																	.auto_login !==
																	"undefined" &&
																response.data
																	.auto_login
															) {
																location.reload();
															}
														}
													} else if (
														type === "error"
													) {
														if (
															typeof response.data
																.message ===
															"object"
														) {
															$.each(
																response.data
																	.message,
																function (
																	index,
																	value
																) {
																	message.append(
																		"<li>" +
																			value +
																			"</li>"
																	);
																}
															);
														} else {
															message.append(
																"<li>" +
																	response
																		.data
																		.message +
																	"</li>"
															);
														}
													}
												} catch (e) {
													message.append(
														"<li>" +
															e.message +
															"</li>"
													);
												}

												var success_message_position =
													JSON.parse(
														ajax_response.responseText
													).data
														.success_message_positon;

												if (
													!response.data.hasOwnProperty(
														"message"
													) ||
													!response.data.message.hasOwnProperty(
														"individual"
													)
												) {
													form.show_message(
														message,
														type,
														$this,
														success_message_position
													);
												} else {
													var $field_id = [];
													$.each(
														$this
															.find(
																".ur-field-item"
															)
															.find(
																".ur-frontend-field"
															),
														function (index) {
															var $this = $(this);
															var $id =
																$this.attr(
																	"id"
																);
															$field_id.push($id);
														}
													);
													$.each(
														response.data.message,
														function (
															index,
															value
														) {
															if (
																$field_id.includes(
																	index
																)
															) {
																var error_message =
																	'<label id="' +
																	index +
																	"-error" +
																	'" class="user-registration-error" for="' +
																	index +
																	'">' +
																	value +
																	"</label>";
																var wrapper =
																	$this
																		.find(
																			".ur-field-item"
																		)
																		.find(
																			"input[id='" +
																				index +
																				"']"
																		);
																	wrapper.closest('.form-row').append(
																		error_message
																	);
															}
														}
													);
												}

												// Check the position set by the admin and scroll to the message postion accordingly.
												if (
													"1" ===
													success_message_position
												) {
													// Scroll to the bottom on ajax submission complete.
													$(window).scrollTop(
														$this
															.find(
																".ur-button-container"
															)
															.offset().top
													);
												} else {
													// Scroll to the top on ajax submission complete.
													$(window).scrollTop(
														$this
															.closest(
																".ur-frontend-form"
															)
															.offset().top
													);
												}

												$(document).trigger(
													"user_registration_frontend_after_ajax_complete",
													[
														ajax_response.responseText,
														type,
														$this,
													]
												);
												$this
													.find(".ur-submit-button")
													.prop("disabled", false);
											}
										},
									});
								});
						});
					},
					/**
					 * Handles edit-profile ajax form submission event.
					 *
					 * @since  1.8.5
					 */
					edit_profile_event: function () {
						$("form.user-registration-EditProfileForm")
							.off("submit")
							.on("submit", function (event) {
								var $this = $(this);

								// Validator messages.
								$.extend($.validator.messages, {
									required:
										user_registration_params.message_required_fields,
									url: user_registration_params.message_url_fields,
									email: user_registration_params.message_email_fields,
									number: user_registration_params.message_number_fields,
								});

								var $el = $this.find(".ur-smart-phone-field");

								if ("true" === $el.attr("aria-invalid")) {
									var wrapper = $el.closest("p.form-row");
									wrapper
										.find("#" + $el.data("id") + "-error")
										.remove();
									var phone_error_msg_dom =
										'<label id="' +
										$el.data("id") +
										"-error" +
										'" class="user-registration-error" for="' +
										$el.data("id") +
										'">' +
										user_registration_params.message_validate_phone_number +
										"</label>";
									wrapper.append(phone_error_msg_dom);
									wrapper
										.find("#" + $el.data("id"))
										.attr("aria-invalid", true);
									return true;
								}

								var exist_detail = $this
									.find(".uraf-profile-picture-upload")
									.find(".user-registration-error").length;

								if (1 === exist_detail) {
									var profile = $this
										.find(".uraf-profile-picture-upload")
										.find(".uraf-profile-picture-input");
									var wrapper = $this.find(
										".uraf-profile-picture-upload"
									);
									wrapper
										.find(
											"#" +
												profile.attr("name") +
												"-error"
										)
										.remove();
									wrapper
										.find(
											".uraf-profile-picture-file-error"
										)
										.remove();
									var error_message =
										'<label id="' +
										profile.attr("name") +
										"-error" +
										'" class="user-registration-error" for="' +
										profile.attr("name") +
										'">' +
										user_registration_params.message_required_fields +
										"</label>";
									wrapper
										.find(
											"button.wp_uraf_profile_picture_upload"
										)
										.after(error_message);
								}

								if (!$this.valid()) {
									return false;
								}

								event.preventDefault();
								$this
									.find(".user-registration-submit-Button")
									.prop("disabled", true);

								// Remove word added by form filler in file upload field during submission
								var file_upload =
									$this.find(".urfu-file-input");

								form.missing_attachment_handler(file_upload);

								var form_data;
								var form_nonce = "0";

								try {
									form_data = form.get_form_data();

									// Handle profile picture
									var profile_picture_url =
										$("#profile_pic_url").val();

									form_data.push({
										value: profile_picture_url,
										field_name:
											"user_registration_profile_pic_url",
									});

									form_data = JSON.stringify(form_data);
								} catch (ex) {
									form_data = "";
								}

								var data = {
									action: "user_registration_update_profile_details",
									security:
										user_registration_params.user_registration_profile_details_save,
									form_data: form_data,
								};

								$this
									.find(".user-registration-submit-Button")
									.find("span")
									.addClass("ur-front-spinner");

								$.ajax({
									type: "POST",
									url: user_registration_params.ajax_url,
									dataType: "JSON",
									data: data,
									complete: function (ajax_response) {
										$this
											.find("span.ur-front-spinner")
											.removeClass("ur-front-spinner");
										$this
											.closest(".user-registration")
											.find(".user-registration-error")
											.remove();
										$this
											.closest(".user-registration")
											.find(".user-registration-message")
											.remove();

										var message = $('<ul class=""/>');
										var type = "error";

										try {
											var response = JSON.parse(
												ajax_response.responseText
											);

											if (
												typeof response.success !==
													"undefined" &&
												response.success === true
											) {
												type = "message";
											}

											if (
												typeof response.data.message ===
												"object"
											) {
												$.each(
													response.data.message,
													function (index, value) {
														message.append(
															"<li>" +
																value +
																"</li>"
														);
													}
												);
											} else {
												message.append(
													"<li>" +
														response.data.message +
														"</li>"
												);
											}
										} catch (e) {
											message.append(
												"<li>" + e.message + "</li>"
											);
										}

										form.show_message(
											message,
											type,
											$this,
											"0"
										);

										// Add trigger to handle functionalities that may be needed after edit-profile ajax submission submissions.
										$(document).trigger(
											"user_registration_edit_profile_after_ajax_complete"
										);
										$this
											.find(
												".user-registration-submit-Button"
											)
											.prop("disabled", false);

										// Scroll yo the top on ajax submission complete.
										$(window).scrollTop(
											$(".user-registration").position()
										);
									},
								});
							});
					},
				};
				form.init();
				events.init();
			});
		};

		$(function () {
			// Handle user registration form submit event.
			$(".ur-submit-button").on("click", function () {
				$(this).closest("form.register").ur_form_submission();
			});

			// Handle edit-profile form submit event.
			$(".user-registration-submit-Button").on("click", function () {
				// Check if the form is edit-profile form and check if ajax submission on edit profile is enabled.
				if (
					$(".ur-frontend-form")
						.find("form.edit-profile")
						.hasClass("user-registration-EditProfileForm") &&
					"yes" ===
						user_registration_params.ajax_submission_on_edit_profile
				) {
					$(
						"form.user-registration-EditProfileForm"
					).ur_form_submission();
				}
			});

			var date_flatpickrs = {};

			$(document.body).on("click", "#load_flatpickr", function () {
				var field_id = $(this).data("id");
				var date_flatpickr = date_flatpickrs[field_id];

				// Load a flatpicker for the field, if hasn't been loaded.
				if (!date_flatpickr) {
					var formated_date = $(this)
						.closest(".ur-field-item")
						.find("#formated_date")
						.val();

					if (0 < $(".ur-frontend-form").length) {
						var date_selector = $(".ur-frontend-form #" + field_id)
							.attr("type", "text")
							.val(formated_date);
					} else {
						var date_selector = $(
							".woocommerce-MyAccount-content #" + field_id
						)
							.attr("type", "text")
							.val(formated_date);
					}

					$(this).attr(
						"data-date-format",
						date_selector.data("date-format")
					);
					$(this).attr("data-mode", date_selector.data("mode"));
					$(this).attr("data-locale", date_selector.data("locale"));
					$(this).attr(
						"data-min-date",
						date_selector.data("min-date")
					);
					$(this).attr(
						"data-max-date",
						date_selector.data("max-date")
					);
					$(this).attr("data-default-date", formated_date);
					date_flatpickr = $(this).flatpickr({
						disableMobile: true,
						onChange: function (
							selectedDates,
							dateString,
							instance
						) {
							$("#" + field_id).val(dateString);
						},
					});
					date_flatpickrs[field_id] = date_flatpickr;
				}

				if (date_flatpickr) {
					date_flatpickr.open();
				}
			});

			$(".ur-frontend-form").each(function () {
				var $registration_form = $(this).find("form.register");

				$registration_form.on(
					"focusout",
					"#user_pass, #password_1",
					function () {
						$this = $(this);
						var this_name = $(this).attr("name");
						var this_data_id = $(this).data("id");
						var enable_strength_password = $this
							.closest("form")
							.attr("data-enable-strength-password");

						if (
							"yes" === enable_strength_password ||
							"1" === enable_strength_password
						) {
							var wrapper = $this.closest("form");
							var minimum_password_strength = wrapper.attr(
								"data-minimum-password-strength"
							);
							var disallowedListArray = [];
							if (
								"function" ===
								typeof wp.passwordStrength
									.userInputDisallowedList
							) {
								disallowedListArray =
									wp.passwordStrength.userInputDisallowedList();
							} else {
								disallowedListArray =
									wp.passwordStrength.userInputBlacklist();
							}

							disallowedListArray.push(
								wrapper
									.find('input[data-id="user_email"]')
									.val()
							); // Add email address in disallowedList.
							disallowedListArray.push(
								wrapper
									.find('input[data-id="user_login"]')
									.val()
							); // Add username in disallowedList.

							var strength = wp.passwordStrength.meter(
								$this.val(),
								disallowedListArray
							);
							if (strength < minimum_password_strength) {
								if ($this.val() !== "") {
									wrapper
										.find("#" + this_data_id + "_error")
										.remove();
									var error_msg_dom =
										'<label id="' +
										this_data_id +
										'_error" class="user-registration-error" for="' +
										this_name +
										'">' +
										ursL10n.password_strength_error +
										".</label>";
									wrapper
										.find(
											".user-registration-password-hint"
										)
										.after(error_msg_dom);
								}
							}
						}
					}
				);
			});
		});

		/**
		 * Append a country option and Remove it on click, if the country is not allowed.
		 */
		$(function () {
			if (
				$(
					".user-registration-EditProfileForm.edit-profile .field-country"
				).length > 0
			) {
				$(".field-country").each(function () {
					var option_value = $(this)
						.find(".ur-data-holder")
						.data("option-value");
					var option_html = $(this)
						.find(".ur-data-holder")
						.data("option-html");
					var $select = $(this).find("select");

					if (option_value && option_html) {
						if (
							$select.find('option[value="' + option_value + '"]')
								.length === 0
						) {
							$select.append(
								"<option class='ur-remove' selected='selected' value='" +
									option_value +
									"'>" +
									option_html +
									"</option>"
							);
						}
						$(this).on("click", function () {
							$(this).find(".ur-remove").remove();
						});
					}
				});
			}
		});
	};

	/**
	 * @since 2.0.0
	 *
	 * To check and uncheck all the option in checkbox.
	 */
	$(function () {
		$(".input-checkbox").each(function () {
			var checkAll = $(this).attr("data-id");
			if (
				$('input[name="' + checkAll + '[]"]:checked').length ==
				$('[data-id = "' + checkAll + '" ]').length
			) {
				$('[data-check = "' + checkAll + '" ]').prop("checked", true);
			}
		});

		$('input[type="checkbox"]#checkall').on("click", function () {
			var checkAll = $(this).attr("data-check");
			$('[data-id = "' + checkAll + '" ]').prop(
				"checked",
				$(this).prop("checked")
			);
		});

		$(".input-checkbox").on("change", function () {
			var checkAll = $(this).attr("data-id");
			if ($(this).prop("checked") === false) {
				$('[data-check = "' + checkAll + '" ]').prop("checked", false);
			}

			if (
				$('input[name="' + checkAll + '[]"]:checked').length ==
				$('[data-id = "' + checkAll + '" ]').length
			) {
				$('[data-check = "' + checkAll + '" ]').prop("checked", true);
			}
		});
	});
	user_registration_form_init();

	/**
	 * Reinitialize the form again after page is fully loaded,
	 * in order to support third party popup plugins.
	 *
	 * @since 1.9.0
	 */
	$(window).on("load", function () {
		user_registration_form_init();
	});
})(jQuery);

function ur_includes(arr, item) {
	if (Array.isArray(arr)) {
		for (var i = 0; i < arr.length; i += 1) {
			if (arr[i] === item) {
				return true;
			}
		}
	}
	return false;
}

(function ($) {
	$(document).on("click", ".password_preview", function (e) {
		e.preventDefault();
		var ursL10n = user_registration_params.ursL10n;

		var current_task = $(this).hasClass("dashicons-hidden")
			? "show"
			: "hide";
		var $password_field = $(this)
			.closest(".user-registration-form-row")
			.find('input[name="password"]');

		// Hide/show password for user registration form
		if ($password_field.length === 0) {
			$password_field = $(this)
				.closest(".field-user_pass")
				.find('input[name="user_pass"]');
		}
		if ($password_field.length === 0) {
			$password_field = $(this)
				.closest(".field-user_confirm_password")
				.find('input[name="user_confirm_password"]');
		}

		// Hide/show password for edit password form
		if ($password_field.length === 0) {
			$password_field = $(this)
				.closest(".user-registration-form-row")
				.find('input[name="password_current"]');
		}
		if ($password_field.length === 0) {
			$password_field = $(this)
				.closest(".user-registration-form-row")
				.find('input[name="password_1"]');
		}
		if ($password_field.length === 0) {
			$password_field = $(this)
				.closest(".user-registration-form-row")
				.find('input[name="password_2"]');
		}

		if ($password_field.length > 0) {
			switch (current_task) {
				case "show":
					$password_field.attr("type", "text");
					$(this)
						.removeClass("dashicons-hidden")
						.addClass("dashicons-visibility");
					$(this).attr("title", ursL10n.hide_password_title);
					break;
				case "hide":
					$password_field.attr("type", "password");
					$(this)
						.removeClass("dashicons-visibility")
						.addClass("dashicons-hidden");
					$(this).attr("title", ursL10n.show_password_title);
					break;
			}
		}
	});
})(jQuery);
