/**
 * UserRegistration Admin JS
 * global i18n_admin
 *
 * @since 2.0.0
 */
(function ($, user_registration_form_builder_data) {
	$(function () {
		var URFormBuilder = {
			/**
			 * Start the form builder.
			 */
			init: function () {
				var i18n_admin = user_registration_form_builder_data.i18n_admin;
				URFormBuilder.init_user_profile_modal();

				//Initialize Form Builder.
				URFormBuilder.init_form_builder();
				//Field option tab
				$(document).on(
					"click",
					'ul.ur-tab-lists li[aria-controls="ur-tab-field-options"]',
					function () {
						// Hide the form settings in fields panel.
						$(".ur-selected-inputs")
							.find("form#ur-field-settings")
							.hide();
						//Show field panels
						$(".ur-builder-wrapper-content").show();
						$(".ur-builder-wrapper-footer").show();
						if ($(".ur-selected-item.ur-item-active").length == 0) {
							//Selecting first ur selected item
							URFormBuilder.handle_selected_item(
								$(".ur-selected-item:first")
							);
						}
					}
				);
				// Handle the field settings when a field is selected in the form builder.
				$(document).on("click", ".ur-selected-item", function () {
					URFormBuilder.handle_selected_item($(this));
				});
				// Run keyboard shortcuts action in form builder area only.
				if (user_registration_form_builder_data.is_form_builder) {
					$(window).on("keydown", function (event) {
						if (event.ctrlKey || event.metaKey) {
							if (
								"s" ===
									String.fromCharCode(
										event.which
									).toLowerCase() ||
								83 === event.which
							) {
								event.preventDefault();
								URFormBuilder.ur_save_form();
								return false;
							}
						}
					});
					// preview the form on key event
					$(window).on("keydown", function (e) {
						if (e.ctrlKey || e.metaKey) {
							if (
								"p" ===
									String.fromCharCode(
										e.which
									).toLowerCase() ||
								80 === e.which
							) {
								e.preventDefault();
								window.open(
									user_registration_form_builder_data.ur_preview
								);
							}
						}
					});
					// View user list table on key event.
					$(window).on("keydown", function (e) {
						if (e.ctrlKey || e.metaKey) {
							if (
								"u" ===
									String.fromCharCode(
										e.which
									).toLowerCase() ||
								85 === e.which
							) {
								e.preventDefault();
								window.open(
									user_registration_form_builder_data.ur_user_list_table
								);
							}
						}
					});
					// Show Keyboard Shortcuts Help Dialog Box on keypress.
					$(window).on("keydown", function (e) {
						if (e.ctrlKey || e.metaKey) {
							if (
								"h" ===
									String.fromCharCode(
										e.which
									).toLowerCase() ||
								85 === e.which
							) {
								e.preventDefault();
								URFormBuilder.ur_show_help();
								return false;
							}
						}
					});
				}

				// Show Help Dialog when quick link is clicked.
				$("#ur-keyboard-shortcut-link").on("click", function (e) {
					e.preventDefault();
					$(".ur-quick-links-content").slideToggle();
					URFormBuilder.ur_show_help();
				});

				// Save the form when Update Form button is clicked.
				$(".ur_save_form_action_button").on("click", function () {
					URFormBuilder.ur_save_form();
				});

				// Close validation message on form builder.
				$(document).on(
					"click",
					".ur-message .ur-message-close",
					function () {
						$message = $(this).closest(".ur-message");
						URFormBuilder.removeMessage($message);
					}
				);

				// Initialize the actions on choice field options.
				URFormBuilder.init_choice_field_options();

				// Show Help Dialog when new form is created.
				$(document).ready(function () {
					var queryString = window.location.search;
					var urlParams = new URLSearchParams(queryString);
					var urPage = urlParams.get("page");
					var isEditPage = urlParams.get("edit-registration");
					var isTemplatePage = $(".user-registration-setup").length;

					var previousPage = document.referrer.split("page=")[1];

					if (
						"add-new-registration" === urPage &&
						(null === isEditPage ||
							(null !== isEditPage &&
								"add-new-registration" === previousPage)) &&
						0 === isTemplatePage
					) {
						URFormBuilder.ur_show_help();
					}
				});

				// Toggle `Bulk Add` option.
				$(document.body).on(
					"click",
					".ur-toggle-bulk-options",
					function (e) {
						e.preventDefault();
						$this = $(this);

						var bulk_options_html = "";
						bulk_options_html +=
							'<div class="ur-bulk-options-container">';
						bulk_options_html +=
							'<div class="ur-general-setting ur-setting-textarea ur-general-setting-bulk-options ur-bulk-options-container"><label for="ur-type-textarea">' +
							$this.data("bulk-options-label") +
							'<span class="ur-portal-tooltip tooltipstered" data-tip="' +
							$this.data("bulk-options-tip") +
							'"></span></label>';
						bulk_options_html +=
							'<textarea data-field="description" class="ur-general-setting-field ur-type-textarea"></textarea></div>';
						bulk_options_html +=
							'<a class="button button-small ur-add-bulk-options" href="#">' +
							$this.data("bulk-options-button") +
							"</a></div>";

						if (
							$this.parent().next(".ur-bulk-options-container")
								.length
						) {
							if (
								$this
									.parent()
									.next(".ur-bulk-options-container")
									.css("display") === "none"
							) {
								$this
									.parent()
									.next(".ur-bulk-options-container")
									.show();
							} else {
								$this
									.parent()
									.next(".ur-bulk-options-container")
									.hide();
							}
						} else {
							$(bulk_options_html)
								.insertAfter($this.parent())
								.trigger("init_tooltips");
						}
					}
				);

				// Add custom list of options.
				$(document.body).on(
					"click",
					".ur-add-bulk-options",
					function (e) {
						e.preventDefault();
						var options = $(this).parent().next(".ur-options-list");
						var bulk_options_container = $(this).parent(
							".ur-bulk-options-container"
						);
						if (options.length) {
							var options_texts = bulk_options_container
								.find(".ur-type-textarea")
								.val()
								.replace(/<\/?[^>]+(>|$)/g, "")
								.split("\n");

							options_texts = $.unique(options_texts);

							options_texts.forEach(function (option_text) {
								if ("" !== option_text) {
									var $add_button = options
										.find("li")
										.last()
										.find("a.add");

									URFormBuilder.add_choice_field_option(
										$add_button,
										option_text.trim()
									);
								}
							});
							bulk_options_container
								.find(".ur-type-textarea")
								.val("");
						}
					}
				);
			},
			init_user_profile_modal: function () {
				var user_profile_modal = {
					init: function () {
						$(document.body)
							.on("click", ".column-data_link a", this.add_item)
							.on("ur_backbone_modal_loaded", this.backbone.init)
							.on(
								"ur_backbone_modal_response",
								this.backbone.response
							);
					},
					add_item: function (e) {
						e.preventDefault();
						$(this).URBackboneModal({ template: "test-demo" });
						return false;
					},
					backbone: {
						init: function (e, target) {
							if ("test-demo" === target) {
							}
						},
						response: function (e, target) {
							if ("test-demo" === target) {
							}
						},
					},
				};
				user_profile_modal.init();
			},
			/**
			 * Handle the process of saving the form when called.
			 */
			ur_save_form: function () {
				var validation_response = URFormBuilder.get_validation_status();
				if (validation_response.validation_status === false) {
					URFormBuilder.show_message(validation_response.message);
					return;
				}
				if (typeof tinyMCE !== "undefined") {
					tinyMCE.triggerSave();
				}

				var form_data = URFormBuilder.get_form_data();
				var form_row_ids = URFormBuilder.get_form_row_ids();
				var ur_form_id = $("#ur_form_id").val();
				var ur_form_id_localization =
					user_registration_form_builder_data.post_id;
				if (
					URFormBuilder.ur_parse_int(ur_form_id_localization, 0) !==
					URFormBuilder.ur_parse_int(ur_form_id, 0)
				) {
					ur_form_id = 0;
				}

				var exclude_serialize_setting_classes =
					".urcl-user-role-field, .uret-override-content-field, .ur_mailerlite_settings";

				var form_setting_data = $(
					"#ur-field-settings :not(" +
						exclude_serialize_setting_classes +
						")"
				).serializeArray();

				var conditional_roles_settings_data =
					URFormBuilder.get_form_conditional_role_data();
				var conditional_submit_settings_data =
					URFormBuilder.get_form_conditional_submit_data();
				var email_content_override_settings_data =
					URFormBuilder.get_form_email_content_override_data();

				/** TODO:: Handle from multistep forms add-on if possible. */
				var multipart_page_setting = $(
					"#ur-multi-part-page-settings"
				).serializeArray();
				/** End Multistep form code. */

				var profile_completeness__custom_percentage = $(
					"#user_registration_profile_completeness_custom_percentage_field input, #user_registration_profile_completeness_custom_percentage_field select"
				).serializeArray();

				var data = {
					action: "user_registration_form_save_action",
					security: user_registration_form_builder_data.ur_form_save,
					data: {
						form_data: JSON.stringify(form_data),
						form_row_ids: JSON.stringify(form_row_ids),
						form_name: $("#ur-form-name").val(),
						form_id: ur_form_id,
						form_setting_data: form_setting_data,
						conditional_roles_settings_data:
							conditional_roles_settings_data,
						conditional_submit_settings_data:
							conditional_submit_settings_data,
						email_content_override_settings_data:
							email_content_override_settings_data,
						multipart_page_setting: multipart_page_setting,
						profile_completeness__custom_percentage:
							profile_completeness__custom_percentage,
					},
				};

				$(document).trigger(
					"user_registration_admin_before_form_submit",
					[data]
				);
				// validation for unsupported currency by paypal.
				if (
					typeof data.data.ur_invalid_currency_status !==
						"undefined" &&
					data.data.ur_invalid_currency_status[0]
						.validation_status === false
				) {
					URFormBuilder.show_message(
						data.data.ur_invalid_currency_status[0]
							.validation_message
					);
					return;
				}

				// Profile Completeness validation.
				if (
					$(
						"#user_registration_profile_completeness_completion_percentage",
						$(document)
					).length != 0
				) {
					var sanitized_percent = parseFloat(
						$(
							"#user_registration_profile_completeness_completion_percentage",
							$(document)
						)
							.val()
							.replace(/[^\d\.]/g, "")
							.replace(/\.(([^\.]*)\.)*/g, ".$2")
					);

					if (sanitized_percent <= 0) {
						URFormBuilder.show_message(
							user_registration_form_builder_data.i18n_admin
								.i18n_pc_profile_completion_error
						);
						return;
					}

					var sum = 0;

					$.each(
						profile_completeness__custom_percentage,
						function (index, field) {
							if (
								field.name ==
									"user_registration_profile_completeness_custom_percentage_field[]" &&
								field.value !== ""
							) {
								sum += parseFloat(
									profile_completeness__custom_percentage[
										index + 1
									].value
								);
							}
						}
					);

					if (sum > sanitized_percent) {
						URFormBuilder.show_message(
							user_registration_form_builder_data.i18n_admin
								.i18n_pc_custom_percentage_filed_error
						);
						return;
					}
				}

				$.ajax({
					url: user_registration_form_builder_data.ajax_url,
					data: data,
					type: "POST",
					beforeSend: function () {
						var spinner =
							'<span class="ur-spinner is-active"></span>';
						$(".ur_save_form_action_button").append(spinner);
						$(".ur-notices").remove();
					},
					complete: function (response) {
						$(".ur_save_form_action_button")
							.find(".ur-spinner")
							.remove();
						if (response.responseJSON.success === true) {
							var success_message =
								user_registration_form_builder_data.i18n_admin
									.i18n_form_successfully_saved;

							if (
								!user_registration_form_builder_data.is_edit_form
							) {
								var title = "Form successfully created.";
								message_body =
									"<p>Want to create a login form as well? Check this <a target='_blank' href='https://docs.wpuserregistration.com/registration-form-and-login-form/how-to-show-login-form/'>link</a>. To know more about other cool features check our <a target='_blank' href='https://docs.wpuserregistration.com/'>docs</a>.</p>";
								Swal.fire({
									icon: "success",
									title: title,
									html: message_body,
								}).then(function (value) {
									if (0 === parseInt(ur_form_id)) {
										window.location =
											user_registration_form_builder_data.admin_url +
											response.responseJSON.data.post_id;
									}
								});
							} else {
								URFormBuilder.show_message(
									success_message,
									"success"
								);

								if (0 === parseInt(ur_form_id)) {
									window.location =
										user_registration_form_builder_data.admin_url +
										response.responseJSON.data.post_id;
								}
							}
						} else {
							var error = response.responseJSON.data.message;
							URFormBuilder.show_message(error);
						}
					},
				});
			},
			/**
			 * Show Help Popup
			 */
			ur_show_help: function () {
				if (!$(".jconfirm").length) {
					var shortcut_keys_html = "<ul>";

					$.each(
						user_registration_form_builder_data.i18n_shortcut_keys,
						function (key, value) {
							shortcut_keys_html +=
								'<li class="ur-shortcut-keyword"><div class="ur-shortcut-title">' +
								value +
								'</div><div class="ur-key"><span class="ur-key-ctrl">' +
								key.split("+")[0] +
								'</span><i class="ur-key-plus"> + </i><span class="ur-key-character"><b>' +
								key.split("+")[1] +
								"</b></span></div></li>";
						}
					);

					shortcut_keys_html += "</ul>";

					jc = $.dialog({
						title: user_registration_form_builder_data.i18n_shortcut_key_title,
						content: shortcut_keys_html,
						icon: "dashicons dashicons-info",
						type: "blue",
						useBootstrap: "false",
						boxWidth: "550px",
						buttons: {
							confirm: {
								text: user_registration_form_builder_data.i18n_close,
								btnClass: "btn-confirm",
								keys: ["enter"],
							},
						},
						escapeKey: true,
						backgroundDismiss: function () {
							return true;
						},
						theme: "material",
					});
				} else {
					jc.close();
				}
			},
			/**
			 * Returns all the validation messages for the specific form in form builder.
			 */
			get_validation_status: function () {
				var only_one_field_index = $.makeArray(
					user_registration_form_builder_data.form_one_time_draggable_fields
				);
				var required_fields = $.makeArray(
					user_registration_form_builder_data.form_required_fields
				);
				var response = {
					validation_status: true,
					message: "",
				};
				if ($(".ur-selected-item").length === 0) {
					response.validation_status = false;
					response.message =
						user_registration_form_builder_data.i18n_admin.i18n_at_least_one_field_need_to_select;
					return response;
				}
				if ($("#ur-form-name").val() === "") {
					response.validation_status = false;
					response.message =
						user_registration_form_builder_data.i18n_admin.i18n_empty_form_name;
					return response;
				}
				if (
					$(".ur_save_form_action_button").find(".ur-spinner")
						.length > 0
				) {
					response.validation_status = false;
					response.message =
						user_registration_form_builder_data.i18n_admin.i18n_previous_save_action_ongoing;
					return response;
				}
				$.each(
					$(
						".ur-selected-item select.ur-settings-selected-countries"
					),
					function () {
						var selected_countries = $(this).val();
						if (
							!selected_countries ||
							(Array.isArray(selected_countries) &&
								selected_countries.length === 0)
						) {
							response.validation_status = false;
							response.message =
								user_registration_form_builder_data.i18n_admin.i18n_select_countries;
							return response;
						}
					}
				);
				$.each(
					$(
						'.ur-input-grids .ur-general-setting-block input[data-field="field_name"]'
					),
					function () {
						var $field = $(this);
						var need_to_break = false;
						var field_attribute;
						try {
							var field_value = $field.val();
							var length = $(
								".ur-input-grids .ur-general-setting-block"
							).find(
								'input[data-field="field_name"][value="' +
									field_value +
									'"]'
							).length;
							if (length > 1) {
								throw user_registration_form_builder_data
									.i18n_admin.i18n_duplicate_field_name;
							}
							if (
								$field
									.closest(".ur-general-setting-block")
									.find('input[data-field="label"]')
									.val() === ""
							) {
								$field = $field
									.closest(".ur-general-setting-block")
									.find('input[data-field="label"]');
								throw user_registration_form_builder_data
									.i18n_admin.i18n_empty_field_label;
							}
							var field_regex =
								/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/gm;
							var regex_result = field_value.match(field_regex);
							if (
								regex_result !== null &&
								regex_result.length === 1 &&
								regex_result[0] === field_value
							) {
							} else {
								throw user_registration_form_builder_data
									.i18n_admin.i18n_invald_field_name;
							}
						} catch (err) {
							response.validation_status = false;
							response.message =
								err.message === undefined ? err : err.message;
							$field
								.closest(".ur-selected-item")
								.trigger("click");
							field_attribute = $field.attr("data-field");
							$("#ur-setting-form")
								.find(
									'input[data-field="' +
										field_attribute +
										'"]'
								)
								.css({ border: "1px solid red" });
							setTimeout(function () {
								$("#ur-setting-form")
									.find(
										'input[data-field="' +
											field_attribute +
											'"]'
									)
									.removeAttr("style");
							}, 2000);
							need_to_break = true;
						}
						if (need_to_break) {
							return false;
						}
					}
				);
				for (
					var single_field = 0;
					single_field < only_one_field_index.length;
					single_field++
				) {
					if (
						$(".ur-input-grids").find(
							'.ur-field[data-field-key="' +
								only_one_field_index[single_field] +
								'"]'
						).length > 1
					) {
						response.validation_status = false;
						response.message =
							user_registration_form_builder_data.i18n_admin
								.i18n_multiple_field_key +
							only_one_field_index[single_field];
						break;
					}
				}
				var paypal = $("#user_registration_enable_paypal_standard");
				var stripe = $("#user_registration_enable_stripe");

				if (paypal.is(":checked")) {
					var payment_fields = ["payment_fields"];

					required_fields = required_fields.concat(payment_fields);
				} else {
					if (stripe.is(":checked")) {
						var stripe_fields = [
							"payment_fields",
							"stripe_gateway",
						];

						required_fields = required_fields.concat(stripe_fields);
					}
				}
				for (
					var required_index = 0;
					required_index < required_fields.length;
					required_index++
				) {
					if (required_fields[required_index] === "payment_fields") {
						var multiple_choice = $(".ur-input-grids").find(
							'.ur-field[data-field-key="multiple_choice"]'
						).length;
						var single_item = $(".ur-input-grids").find(
							'.ur-field[data-field-key="single_item"]'
						).length;
						var payment_slider = $(".ur-input-grids").find(
							".ur-payment-slider-sign:visible"
						).length;

						if (
							multiple_choice < 1 &&
							single_item < 1 &&
							payment_slider < 1
						) {
							response.validation_status = false;

							var field =
								user_registration_form_builder_data.i18n_admin
									.i18n_payment_field;

							response.message =
								field +
								" " +
								user_registration_form_builder_data.i18n_admin
									.i18n_field_is_required;
							break;
						}
					} else {
						if (
							$(".ur-input-grids").find(
								'.ur-field[data-field-key="' +
									required_fields[required_index] +
									'"]'
							).length === 0
						) {
							response.validation_status = false;

							if (required_index === 0) {
								var field =
									user_registration_form_builder_data
										.i18n_admin.i18n_user_email;
							} else if (required_index === 1) {
								var field =
									user_registration_form_builder_data
										.i18n_admin.i18n_user_password;
							} else {
								var field =
									user_registration_form_builder_data
										.i18n_admin.i18n_stripe_field;
							}

							response.message =
								field +
								" " +
								user_registration_form_builder_data.i18n_admin
									.i18n_field_is_required;
							break;
						}
					}
				}

				$.each(
					$(".ur-input-grids").find(
						'.ur-field[data-field-key="text"]'
					),
					function () {
						var $size_field = $(this)
							.closest(".ur-selected-item")
							.find(".ur-advance-setting-block .ur-settings-size")
							.val();
						var label = $(this)
							.closest(".ur-selected-item")
							.find(".ur-label label")
							.html();

						if ($size_field < 1) {
							response.validation_status = false;
							response.message =
								label +
								" " +
								user_registration_form_builder_data.i18n_admin
									.i18n_input_size;
						}
					}
				);
				$.each(
					$(".ur-input-grids").find(
						'.ur-field[data-field-key="password"]'
					),
					function () {
						var $size_field = $(this)
							.closest(".ur-selected-item")
							.find(".ur-advance-setting-block .ur-settings-size")
							.val();
						var label = $(this)
							.closest(".ur-selected-item")
							.find(".ur-label label")
							.html();
						if ($size_field < 1) {
							response.validation_status = false;
							response.message =
								label +
								" " +
								user_registration_form_builder_data.i18n_admin
									.i18n_input_size;
						}
					}
				);

				$.each(
					$(".ur-input-grids").find(
						'.ur-field[data-field-key="file"]'
					),
					function () {
						var $maximum_number_limit_on_uploads = $(this)
							.closest(".ur-selected-item")
							.find(
								".ur-general-setting-block .ur-general-setting-maximum-number-limit-on-uploads input"
							)
							.val();
						var label = $(this)
							.closest(".ur-selected-item")
							.find(".ur-label label")
							.html();
						if ($maximum_number_limit_on_uploads < 1) {
							response.validation_status = false;
							response.message =
								label +
								" " +
								user_registration_form_builder_data.i18n_admin
									.i18n_input_size;
						}

						var $max_upload_size = $(this)
							.closest(".ur-selected-item")
							.find(
								".ur-advance-setting-block input[data-id='file_advance_setting_max_upload_size']"
							)
							.val();

						var max_upload_size_ini =
							user_registration_form_builder_data.max_upload_size_ini;

						if (
							parseInt($max_upload_size) >
							parseInt(max_upload_size_ini)
						) {
							response.validation_status = false;
							response.message =
								label +
								" " +
								user_registration_form_builder_data.i18n_admin
									.i18n_max_upload_size;
						}
					}
				);

				$.each(
					$(".ur-input-grids").find(
						'.ur-field[data-field-key="number"]'
					),
					function () {
						var $size_field = $(this)
							.closest(".ur-selected-item")
							.find(".ur-advance-setting-block .ur-settings-size")
							.val();
						var label = $(this)
							.closest(".ur-selected-item")
							.find(".ur-label label")
							.html();

						if ($size_field < 1) {
							response.validation_status = false;
							response.message =
								label +
								" " +
								user_registration_form_builder_data.i18n_admin
									.i18n_input_size;
						}
						var $min = $(this)
							.closest(".ur-selected-item")
							.find(".ur-advance-setting-block .ur-settings-min")
							.val();
						var $max = $(this)
							.closest(".ur-selected-item")
							.find(".ur-advance-setting-block .ur-settings-max")
							.val();

						if (parseFloat($min) > parseFloat($max)) {
							response.validation_status = false;
							response.message =
								label +
								" " +
								user_registration_form_builder_data.i18n_admin
									.i18n_min_max_input;
						}
					}
				);

				$.each(
					$(".ur-input-grids").find(
						'.ur-field[data-field-key="timepicker"]'
					),
					function () {
						var $time_interval = $(this)
							.closest(".ur-selected-item")
							.find(
								".ur-advance-setting-block .ur-settings-time_interval"
							)
							.val();
						var label = $(this)
							.closest(".ur-selected-item")
							.find(".ur-label label")
							.html();
						if ($time_interval < 1) {
							response.validation_status = false;
							response.message =
								label +
								" " +
								user_registration_form_builder_data.i18n_admin
									.i18n_input_size;
						}
					}
				);

				$.each(
					$(".ur-input-grids").find(
						'.ur-field[data-field-key="range"]'
					),
					function () {
						var $size_field = $(this)
							.closest(".ur-selected-item")
							.find(".ur-advance-setting-block .ur-settings-step")
							.val();
						var label = $(this)
							.closest(".ur-selected-item")
							.find(".ur-label label")
							.html();

						if ($size_field < 1) {
							response.validation_status = false;
							response.message =
								label +
								" " +
								user_registration_form_builder_data.i18n_admin
									.i18n_input_size;
						}
						var $min = $(this)
							.closest(".ur-selected-item")
							.find(".ur-advance-setting-block .ur-settings-min")
							.val();
						var $max = $(this)
							.closest(".ur-selected-item")
							.find(".ur-advance-setting-block .ur-settings-max")
							.val();

						if (parseFloat($min) > parseFloat($max)) {
							response.validation_status = false;
							response.message =
								label +
								" " +
								user_registration_form_builder_data.i18n_admin
									.i18n_min_max_input;
						}
					}
				);
				return response;
			},
			/**
			 * Show all the validation messages while saving the form in form builder.
			 *
			 * @param string message Specific validation message.
			 * @param string type The type or status of message, i.e. success or failure
			 */
			show_message: function (message, type) {
				var $message_container = $(".ur-form-container").find(
						".ur-builder-message-container"
					),
					$admin_bar = $("#wpadminbar"),
					message_string = "";

				if (0 === $message_container.length) {
					$(".ur-form-container").append(
						'<div class="ur-builder-message-container"></div>'
					);
					$message_container = $(".ur-form-container").find(
						".ur-builder-message-container"
					);
					$message_container.css({ top: $admin_bar.height() + "px" });
				}

				if ("success" === type) {
					message_string =
						'<div class="ur-message"><div class="ur-success"><p><strong>' +
						user_registration_form_builder_data.i18n_admin
							.i18n_success +
						"! </strong>" +
						message +
						'</p><span class="dashicons dashicons-no-alt ur-message-close"></span></div></div>';
				} else {
					$(".ur-error").remove();
					message_string =
						'<div class="ur-message"><div class="ur-error"><p><strong>' +
						user_registration_form_builder_data.i18n_admin
							.i18n_error +
						"! </strong>" +
						message +
						'</p><span class="dashicons dashicons-no-alt ur-message-close"></span></div></div>';
				}

				var $message = $(message_string).prependTo($message_container);
				setTimeout(function () {
					$message.addClass("entered");
				}, 50);

				if ($(".ur-error").find(".ur-captcha-error").length == 1) {
					$(".ur-error").css("width", "490px");
					setTimeout(function () {
						URFormBuilder.removeMessage($message);
					}, 5000);
				} else {
					setTimeout(function () {
						URFormBuilder.removeMessage($message);
					}, 3000);
				}
			},
			/**
			 * Remove the validation message when calles.
			 *
			 * @param string $message Validation message string.
			 */
			removeMessage: function ($message) {
				$message.removeClass("entered").addClass("exiting");
				setTimeout(function () {
					$message.remove();
				}, 120);
			},
			/**
			 * Get all the form data from form builder.
			 */
			get_form_data: function () {
				var form_data = [];
				var single_row = $(".ur-input-grids .ur-single-row");
				$.each(single_row, function () {
					var grid_list_item = $(this).find(".ur-grid-list-item");
					var single_row_data = [];
					$.each(grid_list_item, function () {
						var grid_item = $(this);
						var grid_wise_data =
							URFormBuilder.get_grid_wise_data(grid_item);
						single_row_data.push(grid_wise_data);
					});

					form_data.push(single_row_data);
				});
				return form_data;
			},
			/**
			 * Get all the grid wise form data from form builder.
			 *
			 * @param Object $grid_item Contains information about grids in which the whole form has been divided into.
			 */
			get_grid_wise_data: function ($grid_item) {
				var all_field_item = $grid_item.find(".ur-selected-item");
				var all_field_data = [];
				$.each(all_field_item, function () {
					var $this_item = $(this);
					var field_key = $this_item
						.find(".ur-field")
						.attr("data-field-key");
					var field_id = "user_registration_" + field_key;
					var icon_class = $("li[data-field-id ='" + field_id + "']")
						.find(".ur-icon")
						.attr("class");
					var single_field_data = {
						field_key: field_key,
						general_setting:
							URFormBuilder.get_field_general_setting($this_item),
						advance_setting:
							URFormBuilder.get_field_advance_setting($this_item),
						icon: icon_class,
					};

					all_field_data.push(single_field_data);
				});
				return all_field_data;
			},
			/**
			 * Get the general settings of a specific field in the grid.
			 *
			 * @param Object $single_item Contains information about each items in the grid.
			 */
			get_field_general_setting: function ($single_item) {
				var general_setting_field = $single_item
					.find(".ur-general-setting-block")
					.find(".ur-general-setting-field");
				var general_setting_data = {};

				var option_values = [];
				var default_values = [];
				$.each(general_setting_field, function () {
					var is_checkbox = $(this)
						.closest(".ur-general-setting")
						.hasClass("ur-setting-checkbox");

					if ("options" === $(this).attr("data-field")) {
						if (
							"multiple_choice" ===
							$(this).attr("data-field-name")
						) {
							var li_elements = $(this).closest("ul").find("li");
							var array_value = [];

							li_elements.each(function (index, element) {
								var label = $(element)
									.find("input.ur-type-checkbox-label")
									.val();

								var value = $(element)
									.find("input.ur-type-checkbox-money-input")
									.val();
								var sell_value = $(element)
									.find(
										"input.ur-checkbox-selling-price-input"
									)
									.val();
								if (
									array_value.every(function (each_value) {
										return each_value.label !== label;
									})
								) {
									general_setting_data["options"] =
										array_value.push({
											label: label,
											value: value,
											sell_value: sell_value,
										});
								}
								general_setting_data["options"] = array_value;
							});
						} else if (
							"captcha" ===
							$(this).attr("data-field-name")) {
							var li_elements = $(this).closest("ul").find("li");
							var captcha_value = [];

							li_elements.each(function (index, element) {

								var question = $(element)
									.find("input.ur-type-captcha-question")
									.val();

								var answer = $(element)
									.find(".ur-type-captcha-answer")
									.val();
								if (
									captcha_value.every(function (each_value) {
										return each_value.question !== question;
									})
								) {
									general_setting_data["options"] =
									captcha_value.push({
											question: question,
											answer: answer,
										});
								}
								general_setting_data["options"] = captcha_value;
							});
						} else {
							var choice_value = URFormBuilder.get_ur_data(
								$(this)
							).trim();
						}
						if (
							option_values.every(function (each_value) {
								return each_value !== choice_value;
							})
						) {
							general_setting_data["options"] =
								option_values.push(choice_value);
							general_setting_data["options"] = option_values;
						}
					} else {
						if ("default_value" === $(this).attr("data-field")) {
							if (is_checkbox === true) {
								if ($(this).is(":checked")) {
									general_setting_data["default_value"] =
										default_values.push(
											URFormBuilder.get_ur_data($(this))
										);
									general_setting_data["default_value"] =
										default_values;
								}
							} else if ($(this).is(":checked")) {
								general_setting_data["default_value"] =
									URFormBuilder.get_ur_data($(this));
							}
						} else if ("html" === $(this).attr("data-field")) {
							general_setting_data[$(this).attr("data-field")] =
								URFormBuilder.get_ur_data($(this)).replace(
									/"/g,
									"'"
								);
						} else {
							general_setting_data[$(this).attr("data-field")] =
								URFormBuilder.get_ur_data($(this));
						}
					}
				});
				return general_setting_data;
			},
			/**
			 * Get the advance settings of a specific field in the grid.
			 *
			 * @param Object $single_item Contains information about each items in the grid.
			 */
			get_field_advance_setting: function ($single_item) {
				var advance_setting_field = $single_item
					.find(".ur-advance-setting-block")
					.find(".ur_advance_setting");
				var advance_setting_data = {};
				$.each(advance_setting_field, function () {
					advance_setting_data[$(this).attr("data-advance-field")] =
						URFormBuilder.get_ur_data($(this));
				});
				return advance_setting_data;
			},
			/**
			 * Get the datas of each nodes in the field settings.
			 *
			 * @param Object $this_node Specific nodes for each settings.
			 */
			get_ur_data: function ($this_node) {
				var node_type = $this_node.get(0).tagName.toLowerCase();
				var value = "";

				switch (node_type) {
					case "input":
						// Check input type.
						switch ($this_node.attr("type")) {
							case "checkbox":
								value = $this_node.is(":checked");

								if (
									$this_node.hasClass(
										"ur-type-checkbox-value"
									)
								) {
									value = $this_node.val();
								}

								if (
									$this_node.hasClass("ur-type-toggle") &&
									!value
								) {
									value = "false";
								}
								break;

							default:
								value = $this_node.val();
								break;
						}

						break;
					case "select":
						value = $this_node.val();
						break;
					case "textarea":
						value = $this_node.val();
						break;
					default:
				}

				return value;
			},
			/**
			 * Get all form row ids.
			 */
			get_form_row_ids: function () {
				var row_ids = [];
				var single_row = $(".ur-input-grids .ur-single-row");
				$.each(single_row, function () {
					row_ids.push($(this).attr("data-row-id"));
				});
				return row_ids;
			},
			/**
			 * Parse integer value from string.
			 *
			 * @param string Parse integer value form a string.
			 */
			ur_parse_int: function (value) {
				return parseInt(value, 0);
			},
			/**
			 * Rounds a number up to the next largest integer.
			 */
			ur_math_ceil: function (value) {
				return Math.ceil(value, 0);
			},
			/**
			 * Get all the conditions datas that the user has set in conditionally assign user role settings.
			 */
			get_form_conditional_role_data: function () {
				var form_data = [];
				var single_row = $(".urcl-role-logic-wrap");

				$.each(single_row, function () {
					var grid_list_item = $(this).find(".urcl-user-role-field");
					var all_field_data = [];
					var or_field_data = [];
					var assign_role = "";
					$.each(grid_list_item, function () {
						$field_key = $(this).attr("name").split("[");

						if (
							"user_registration_form_conditional_user_role" ===
							$field_key[0]
						) {
							assign_role = $(this).val();
							grid_list_item.splice($(this), 1);
						}
					});

					var conditional_group = $(this).find(
						".urcl-conditional-group"
					);
					$.each(conditional_group, function () {
						var inner_conditions = [];
						var grid_list_item = $(this).find(
							".urcl-user-role-field"
						);
						$.each(grid_list_item, function () {
							var conditions = {
								field_key: $(this).attr("name"),
								field_value: $(this).val(),
							};
							inner_conditions.push(conditions);
						});
						all_field_data.push(inner_conditions);
					});

					var or_groups = $(this).find(".urcl-or-groups");
					$.each(or_groups, function () {
						var conditional_or_group = $(this).find(
							".urcl-conditional-or-group"
						);
						var or_data = [];
						$.each(conditional_or_group, function () {
							var inner_or_conditions = [];
							var or_list_item = $(this).find(
								".urcl-user-role-field"
							);
							$.each(or_list_item, function () {
								var or_conditions = {
									field_key: $(this).attr("name"),
									field_value: $(this).val(),
								};
								inner_or_conditions.push(or_conditions);
							});
							or_data.push(inner_or_conditions);
						});
						or_field_data.push(or_data);
					});
					var all_fields = {
						assign_role: assign_role,
						conditions: all_field_data,
						or_conditions: or_field_data,
					};
					form_data.push(all_fields);
				});
				return form_data;
			},
			/**
			 * Get all the conditions data for conditional logic settings for submit button.
			 */
			get_form_conditional_submit_data: function () {
				var form_data = [];
				var single_row = $(".urcl-submit-logic-wrap");

				$.each(single_row, function () {
					var grid_list_item = $(this).find(".urcl-submit-field");
					var all_field_data = [];
					var or_field_data = [];
					var action = "";
					$.each(grid_list_item, function () {
						$field_key = $(this).attr("name").split("[");

						if (
							"user_registration_form_conditional_submit" ===
							$field_key[0]
						) {
							action = $(this).val();
							grid_list_item.splice($(this), 1);
						}
					});

					var conditional_group = $(this).find(
						".urcl-conditional-group"
					);
					$.each(conditional_group, function () {
						var inner_conditions = [];
						var grid_list_item = $(this).find(".urcl-submit-field");
						$.each(grid_list_item, function () {
							var conditions = {
								field_key: $(this).attr("name"),
								field_value: $(this).val(),
							};
							inner_conditions.push(conditions);
						});
						all_field_data.push(inner_conditions);
					});

					var or_groups = $(this).find(".urcl-or-groups");
					$.each(or_groups, function () {
						var conditional_or_group = $(this).find(
							".urcl-conditional-or-group"
						);
						var or_data = [];
						$.each(conditional_or_group, function () {
							var inner_or_conditions = [];
							var or_list_item =
								$(this).find(".urcl-submit-field");
							$.each(or_list_item, function () {
								var or_conditions = {
									field_key: $(this).attr("name"),
									field_value: $(this).val(),
								};
								inner_or_conditions.push(or_conditions);
							});
							or_data.push(inner_or_conditions);
						});
						or_field_data.push(or_data);
					});
					var all_fields = {
						action: action,
						conditions: all_field_data,
						or_conditions: or_field_data,
					};

					form_data.push(all_fields);
				});
				return form_data;
			},
			/**
			 * Get all the overrided email contents saved by the user.
			 */
			get_form_email_content_override_data: function () {
				var specific_email_contents = {};
				var single_row = $(
					".user-registration-email-template-content-wrap"
				);

				$.each(single_row, function () {
					var email_title_item = $(this).find(
						".user-registration-card__header"
					);
					var email_body_item = $(this).find(
						".user-registration-card__body"
					);

					specific_email_contents[$(this).prop("id")] = {
						title: email_title_item
							.find(".user-registration-card__title ")
							.text(),
						description: email_title_item
							.find(".user-registration-help-tip")
							.data("description"),
						override: email_title_item
							.find("#uret_override_" + $(this).prop("id"))
							.hasClass("enabled")
							? 1
							: 0,
						subject: email_body_item
							.find(".uret_subject_input")
							.val(),
						content: email_body_item
							.find(
								"#user_registration_" +
									$(this).prop("id") +
									"_content"
							)
							.val(),
					};
				});

				return specific_email_contents;
			},
			/**
			 * Handles all the actions performed inside form builder.
			 */
			init_form_builder: function () {
				$.fn.ur_form_builder = function () {
					var loaded_params = {
						active_grid:
							user_registration_form_builder_data.active_grid,
						number_of_grid_list:
							user_registration_form_builder_data.number_of_grid,
						min_grid_height: 70,
					};
					// traverse all nodes
					return this.each(function () {
						// express a single node as a jQuery object
						var $this = $(this);
						var builder = {
							init: function () {
								this.single_row();
								this.manage_required_fields();
								this.manage_label_hidden_fields();
							},
							single_row: function () {
								if (
									!user_registration_form_builder_data.is_edit_form
								) {
									var single_row = $(
										"<div class='ur-single-row'/ data-row-id=\"0\">"
									);
									single_row.append(
										$("<div class='ur-grids'/>")
									);
									var grid_button = this.get_grid_button();
									single_row
										.find(".ur-grids")
										.append(grid_button);
									single_row
										.find(".ur-grids")
										.find(
											'span[data-id="' +
												loaded_params.active_grid +
												'"]'
										)
										.addClass("ur-active-grid");
									var grid_list = this.get_grid_lists(
										loaded_params.active_grid
									);
									single_row.append(
										'<div style="clear:both"></div>'
									);
									single_row.append(grid_list);
									single_row.append(
										'<div style="clear:both"></div>'
									);

									$this.append(single_row);
									$(".ur-single-row")
										.eq(0)
										.find(".ur-grid-lists")
										.eq(0)
										.find(".ur-grid-list-item")
										.eq(0)
										.find(".user-registration-dragged-me")
										.remove();
									$(".ur-single-row")
										.eq(0)
										.find(".ur-grid-lists")
										.eq(0)
										.find(".ur-grid-list-item")
										.eq(0)
										.append(
											user_registration_form_builder_data.required_form_html
										);
								}

								if ($this.find(".ur-add-new-row").length == 0) {
									$this.append(
										'<button type="button" class="button button-primary dashicons dashicons-plus-alt ur-add-new-row ui-sortable-handle" data-total-rows="0">' +
											user_registration_form_builder_data.add_new +
											"</button>"
									);
									var total_rows = $this
										.find(".ur-add-new-row")
										.siblings(".ur-single-row")
										.last()
										.prev()
										.attr("data-row-id");
									$this
										.find(".ur-add-new-row")
										.attr("data-total-rows", total_rows);
								}
								events.render_draggable_sortable();
								builder.manage_empty_grid();
								builder.manage_draggable_users_fields();
							},
							/**
							 * Structure for grid buttons.
							 */
							get_grid_button: function () {
								var grid_button = $(
									'<div class="ur-grid-containner"/>'
								);
								var grid_content =
									'<button type="button" class="ur-edit-grid"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path d="M28,6V26H4V6H28m2-2H2V28H30V4Z"/></svg></button>';
								grid_content +=
									'<button type="button" class="dashicons dashicons-no-alt ur-remove-row"></button>';
								grid_content +=
									'<div class="ur-toggle-grid-content" style="display:none">';
								grid_content +=
									"<small>Select the grid column.</small>";
								grid_content +=
									'<div class="ur-grid-selector" data-grid = "1">';
								grid_content +=
									'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path d="M28,6V26H4V6H28m2-2H2V28H30V4Z"/></svg>';
								grid_content += "</div>";
								grid_content +=
									'<div class="ur-grid-selector" data-grid = "2">';
								grid_content +=
									'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path d="M17,4H2V28H30V4ZM4,26V6H15V26Zm24,0H17V6H28Z"/></svg>';
								grid_content += "</div>";
								grid_content +=
									'<div class="ur-grid-selector" data-grid = "3">';
								grid_content +=
									'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path d="M22,4H2V28H30V4ZM4,26V6h6V26Zm8,0V6h8V26Zm16,0H22V6h6Z"/></svg>';
								grid_content += "</div>";
								grid_content += "</div>";
								grid_button.html(grid_content);
								return grid_button.html();
							},
							/**
							 * Structure for grid lists.
							 *
							 * @param integer number_of_grid Number of grids in the form.
							 */
							get_grid_lists: function (number_of_grid) {
								var grid_lists = $(
									'<div class="ur-grid-lists"/>'
								);
								for (var i = 1; i <= number_of_grid; i++) {
									var grid_list_item = $(
										"<div ur-grid-id='" +
											i +
											"' class='ur-grid-list-item'></div>"
									);
									var width =
										Math.floor(100 / number_of_grid) -
										number_of_grid;
									grid_list_item.css({
										width: width + "%",
										"min-height":
											loaded_params.min_grid_height +
											"px",
									});
									grid_lists.append(grid_list_item);
								}
								grid_lists.append(
									'<div style="clear:both"></div>'
								);
								grid_lists
									.find(".ur-grid-list-item")
									.eq("0")
									.css({});
								return grid_lists;
							},
							/**
							 * Hides label of fields if hide label option is enabled.
							 */
							manage_label_hidden_fields: function () {
								$('input[data-field="hide_label"]').each(
									function () {
										if ($(this).is(":checked")) {
											$(this)
												.closest(".ur-selected-item")
												.find(".ur-label")
												.find("label")
												.hide();
										} else {
											$(this)
												.closest(".ur-selected-item")
												.find(".ur-label")
												.find("label")
												.show();
										}
									}
								);
							},
							/**
							 * Information about required fields
							 */
							manage_required_fields: function () {
								var required_fields =
									user_registration_form_builder_data.form_required_fields;

								var selected_inputs = $(".ur-input-grids");

								if (Array.isArray(required_fields)) {
									for (
										var i = 0;
										i < required_fields.length;
										i++
									) {
										var field_node = selected_inputs.find(
											'.ur-field[data-field-key="' +
												required_fields[i] +
												'"]'
										);

										field_node
											.closest(".ur-selected-item")
											.find(
												'input[data-field="required"]'
											)
											.trigger("change");
										field_node
											.closest(".ur-selected-item")
											.find(
												'input[data-field="required"]'
											)
											.attr("disabled", "disabled");
									}
								}

								selected_inputs
									.find(".ur-selected-item")
									.each(function () {
										if (
											$(this)
												.find(
													'input[data-field="required"]'
												)
												.is(":checked")
										) {
											var label_node = $(this)
												.find(".ur-label")
												.find("label");
											label_node
												.find("span:contains('*')")
												.remove();
											label_node.append(
												'<span style="color:red">*</span>'
											);
										}
									});
							},
							/**
							 * Structure for empty grid.
							 */
							manage_empty_grid: function () {
								var main_containner = $(".ur-input-grids");
								var drag_me = $(
									'<div class="user-registration-dragged-me"/>'
								);
								main_containner
									.find(".user-registration-dragged-me")
									.remove();
								$.each(
									main_containner.find(".ur-grid-list-item"),
									function () {
										var $this = $(this);
										if (
											$(this).find(".ur-selected-item")
												.length === 0
										) {
											$this.append(drag_me.clone());
										}
									}
								);
							},
							/**
							 * Manage draggable user fields.
							 */
							manage_draggable_users_fields: function () {
								var single_draggable_fields =
									user_registration_form_builder_data.form_one_time_draggable_fields;

								var ul_node = $(
									"#ur-tab-registered-fields"
								).find("ul.ur-registered-list");

								$.each(ul_node.find("li"), function () {
									var $this = $(this);

									var data_field_id = $(this)
										.attr("data-field-id")
										.replace("user_registration_", "");

									if (
										$.inArray(
											data_field_id,
											single_draggable_fields
										) >= 0
									) {
										if (
											$(".ur-input-grids").find(
												'.ur-field[data-field-key="' +
													data_field_id +
													'"]'
											).length > 0
										) {
											$this.draggable("disable");
											$this.addClass("ur-locked-field");
											$this.addClass(
												"ur-one-time-draggable-disabled"
											);
										} else {
											$this.draggable("enable");
											$this.removeClass(
												"ur-locked-field"
											);
											$this.removeClass(
												"ur-one-time-draggable-disabled"
											);
										}
									}
								});

								var locked = ul_node.find(".ur-locked-field");
								$.each(locked, function () {
									$this = $(this);
									$this.draggable("disable");
								});
							},
							/**
							 * Populate the dropped node when a field is dragged from field container to form builder area.
							 */
							populate_dropped_node: function (
								container,
								form_field_id
							) {
								var data = {
									action: "user_registration_user_input_dropped",
									security:
										user_registration_form_builder_data.user_input_dropped,
									form_field_id: form_field_id,
								};

								var template_text =
									'<div class="ur-selected-item ajax_added"><div class="ur-action-buttons">' +
									'<span title="Clone" class="dashicons dashicons-admin-page ur-clone"></span>' +
									'<span title="Trash" class="dashicons dashicons-trash ur-trash"></span>' +
									"</div>(content)</div>";
								container
									.closest(".ur-single-row")
									.find(".user-registration-dragged-me")
									.fadeOut();
								$.ajax({
									url: user_registration_form_builder_data.ajax_url,
									data: data,
									type: "POST",
									beforeSend: function () {
										container
											.removeAttr("class")
											.removeAttr("id")
											.removeAttr("data-field-id")
											.addClass("ur-selected-item")
											.css({ width: "auto" });
										container.html(
											'<small class="spinner is-active"></small>'
										);
										container.addClass("ur-item-dragged");
									},
									complete: function (response) {
										builder.manage_empty_grid();

										if (
											response.responseJSON.success ===
											true
										) {
											var template = $(
												template_text.replace(
													"(content)",
													response.responseJSON.data
														.template
												)
											);
											template.removeClass("ajax_added");
											template.removeClass(
												"ur-item-dragged"
											);
											container
												.find(".ajax_added")
												.find(".spinner")
												.remove();
											container
												.find(".ajax_added")
												.remove();

											$(template).insertBefore(container);
											container.remove();
										}
										builder.manage_draggable_users_fields();

										var populated_item = template
											.closest(".ur-selected-item ")
											.find("[data-field='field_name']")
											.val();
										URFormBuilder.manage_conditional_field_options(
											populated_item
										);

										$(
											'.ur-input-type-select2 .ur-field[data-field-key="select2"] select, .ur-input-type-multi-select2 .ur-field[data-field-key="multi_select2"] select'
										).selectWoo();

										var $template = $(template);

										// Get fieldKey from data-field-key attribute.
										var fieldKey = $template
											.find(".ur-field")
											.data("field-key");

										// Get field name.
										var fieldName = $template
											.find(
												'.ur-general-setting.ur-general-setting-field-name input[name="ur_general_setting[field_name]"]'
											)
											.val();

										// Get label text from label tag, excluding any span tags
										var label = $template
											.find(".ur-label label")
											.contents()
											.filter(function () {
												return this.nodeType === 3; // Filter out non-text nodes (e.g. <span> tags)
											})
											.text()
											.trim();

										// Get the visibility of the field.
										var visibleTo = $template
											.find(
												'select.ur_advance_setting.ur-settings-field-visibility[name="' +
													fieldKey +
													'_advance_setting[field_visibility]"]'
											)
											.val();

										$(document.body).trigger(
											"ur_new_field_created",
											[
												{
													fieldKey: fieldKey,
													fieldName: fieldName,
													label: label,
													visibleTo: visibleTo,
												},
											]
										);
									},
								});
							},
						};
						var events = {
							register: function () {
								this.register_add_new_row();
								this.register_remove_row();
								this.change_ur_grids();
								this.remove_selected_item();
								this.clone_selected_item();
							},
							register_add_new_row: function () {
								var $this_obj = this;
								$("body").on(
									"click",
									".ur-add-new-row",
									function () {
										var total_rows =
											$(this).attr("data-total-rows");
										$(this).attr(
											"data-total-rows",
											parseInt(total_rows) + 1
										);

										var single_row_clone = $(this)
											.closest(".ur-input-grids")
											.find(".ur-single-row")
											.eq(0)
											.clone();
										single_row_clone.attr(
											"data-row-id",
											parseInt(total_rows) + 1
										);
										single_row_clone
											.find(".ur-grid-lists")
											.html("");
										single_row_clone
											.find(".ur-grids")
											.find("span")
											.removeClass("ur-active-grid");
										single_row_clone
											.find(".ur-grids")
											.find(
												'span[data-id="' +
													loaded_params.active_grid +
													'"]'
											)
											.addClass("ur-active-grid");
										var grid_list = builder.get_grid_lists(
											loaded_params.active_grid
										);
										single_row_clone
											.find(".ur-grid-lists")
											.append(grid_list.html());
										single_row_clone.insertBefore(
											".ur-add-new-row"
										);
										single_row_clone.show();
										$this_obj.render_draggable_sortable();
										builder.manage_empty_grid();
										$(document).trigger(
											"user_registration_row_added",
											[single_row_clone]
										);
									}
								);
							},
							register_remove_row: function () {
								var $this = this;
								$("body").on(
									"click",
									".ur-remove-row",
									function () {
										if (
											$(".ur-input-grids").find(
												".ur-single-row:visible"
											).length > 1
										) {
											var $this_row = $(this);
											ur_confirmation(
												user_registration_form_builder_data
													.i18n_admin
													.i18n_are_you_sure_want_to_delete_row,
												{
													title: user_registration_form_builder_data
														.i18n_admin
														.i18n_msg_delete,
													confirm: function () {
														var btn =
															$this_row.prev();
														var new_btn;
														if (
															btn.hasClass(
																"ur-add-new-row"
															)
														) {
															new_btn =
																btn.clone();
														} else {
															new_btn = $this_row
																.clone()
																.attr(
																	"class",
																	"dashicons-minus ur-remove-row"
																);
														}
														if (
															new_btn.hasClass(
																"ur-add-new-row"
															)
														) {
															$this_row
																.closest(
																	".ur-single-row"
																)
																.prev()
																.find(
																	".ur-remove-row"
																)
																.before(
																	new_btn
																);
														}
														var single_row =
															$this_row.closest(
																".ur-single-row"
															);
														$(document).trigger(
															"user_registration_row_deleted",
															[single_row]
														);

														// Remove Row Fields from Conditional Select Dropdown.
														var row_fields =
															single_row.find(
																".ur-grid-lists .ur-selected-item .ur-general-setting"
															);
														$(row_fields).each(
															function () {
																var field_label =
																	$(this)
																		.closest(
																			".ur-selected-item"
																		)
																		.find(
																			" .ur-admin-template .ur-label label"
																		)
																		.text();
																var field_key =
																	$(this)
																		.closest(
																			".ur-selected-item"
																		)
																		.find(
																			" .ur-admin-template .ur-field"
																		)
																		.data(
																			"field-key"
																		);

																//strip certain fields
																if (
																	"section_title" ==
																		field_key ||
																	"html" ==
																		field_key ||
																	"wysiwyg" ==
																		field_key ||
																	"billing_address_title" ==
																		field_key ||
																	"shipping_address_title" ==
																		field_key
																) {
																	return;
																}

																var field_name =
																	$(this)
																		.find(
																			"[data-field='field_name']"
																		)
																		.val();

																if (
																	typeof field_name !==
																	"undefined"
																) {
																	// Remove item from conditional logic options
																	$(
																		'[class*="urcl-settings-rules_field_"] option[value="' +
																			field_name +
																			'"]'
																	).remove();

																	// Remove Field from Form Setting Conditionally Assign User Role.
																	$(
																		'[class*="urcl-field-conditional-field-select"] option[value="' +
																			field_name +
																			'"]'
																	).remove();
																}
															}
														);
														single_row.remove();
														$this.check_grid();
														builder.manage_draggable_users_fields();

														Swal.fire({
															icon: "success",
															title: "Successfully deleted!",
															customClass:
																"user-registration-swal2-modal user-registration-swal2-modal--center user-registration-swal2-no-button",
															showConfirmButton: false,
															timer: 1000,
														});
													},
													reject: function () {
														// Do Nothing.
													},
												}
											);
										} else {
											URFormBuilder.ur_alert(
												user_registration_form_builder_data
													.i18n_admin
													.i18n_at_least_one_row_is_required_to_create_a_registration_form,
												{
													title: user_registration_form_builder_data
														.i18n_admin
														.i18n_cannot_delete_row,
												}
											);
										}
									}
								);
							},
							change_ur_grids: function () {
								var $this_obj = this;

								$(document).on(
									"click",
									".ur-grids .ur-edit-grid",
									function (e) {
										e.stopPropagation();
										$(this)
											.siblings(".ur-toggle-grid-content")
											.stop(true)
											.slideToggle(200);
									}
								);
								$(document).on("click", function () {
									$(".ur-toggle-grid-content")
										.stop(true)
										.slideUp(200);
								});

								$(document).on(
									"click",
									".ur-grids .ur-toggle-grid-content .ur-grid-selector",
									function () {
										var $this_single_row =
												$(this).closest(
													".ur-single-row"
												),
											grid_num =
												$(this).attr("data-grid"),
											grid_comp = $this_single_row.find(
												".ur-grid-lists .ur-grid-list-item"
											).length,
											$grids =
												builder.get_grid_lists(
													grid_num
												),
											iterator = 0;

										// Prevent from selecting same grid.
										if (
											$this_single_row.find(
												".ur-grid-lists .ur-grid-list-item"
											).length === parseInt(grid_num)
										) {
											return;
										}

										$this_single_row
											.find("button.ur-edit-grid")
											.html($(this).html());

										$.each(
											$this_single_row.find(
												".ur-grid-lists .ur-grid-list-item"
											),
											function () {
												$(this)
													.children("*")
													.each(function () {
														$grids
															.find(
																".ur-grid-list-item"
															)
															.eq(iterator)
															.append(
																$(this).clone()
															); // "this" is the current element in the loop.

														// In case the fields have to be redistributed into 2 columns - prioritizes left column first, if 3rd column is going away.
														if (
															3 ===
																parseInt(
																	$(this)
																		.parent()
																		.attr(
																			"ur-grid-id"
																		)
																) &&
															3 ===
																parseInt(
																	grid_comp
																) &&
															2 ===
																parseInt(
																	grid_num
																)
														) {
															iterator = Math.abs(
																--iterator
															); // Alternates between 0 and 1.
														}
													});

												// Decides to check if it's trying to push into lower amount of columns.
												// If so, it simply resets the index to 0 to disallow elements from removed rows.
												if (
													parseInt(grid_num) >
														grid_comp ||
													($(this).children("*")
														.length &&
														2 <= parseInt(grid_num))
												) {
													iterator =
														parseInt(grid_num) <=
														++iterator
															? 0
															: iterator;
												}
											}
										);

										$this_single_row
											.find(".ur-grid-lists")
											.eq(0)
											.hide();
										$grids
											.clone()
											.insertAfter(
												$this_single_row.find(
													".ur-grid-lists"
												)
											);
										$this_single_row
											.find(".ur-grid-lists")
											.eq(0)
											.remove();
										$this_obj.render_draggable_sortable();
										builder.manage_empty_grid();
									}
								);
							},
							render_draggable_sortable: function () {
								$(".ur-grid-list-item")
									.sortable({
										containment: ".ur-input-grids",
										over: function () {
											$(this).addClass(
												"ur-sortable-active"
											);
											builder.manage_empty_grid();
										},
										out: function () {
											$(this).removeClass(
												"ur-sortable-active"
											);
											builder.manage_empty_grid();
										},
										connectWith: ".ur-grid-list-item",
									})
									.disableSelection();
								$(".ur-input-grids").sortable({
									containment: ".ur-builder-wrapper",
									tolerance: "pointer",
									placeholder: "ur-single-row",
									forceHelperSize: true,
									over: function () {
										$(this).addClass("ur-sortable-active");
									},
									out: function () {
										$(this).removeClass(
											"ur-sortable-active"
										);
									},
								});
								$("#ur-draggabled .draggable")
									.draggable({
										connectToSortable: ".ur-grid-list-item",
										containment: ".ur-registered-from",
										helper: function () {
											return $(this)
												.clone()
												.insertAfter(
													$(this)
														.closest(
															".ur-tab-contents"
														)
														.siblings(
															".ur-tab-lists"
														)
												);
										},
										// start: function (event, ui) {
										// },
										stop: function (event, ui) {
											if (
												$(ui.helper).closest(
													".ur-grid-list-item"
												).length === 0
											) {
												return;
											}
											var data_field_id = $(ui.helper)
												.attr("data-field-id")
												.replace(
													"user_registration_",
													""
												)
												.trim();
											var length_of_required = $(
												".ur-input-grids"
											).find(
												'.ur-field[data-field-key="' +
													data_field_id +
													'"]'
											).length;

											var only_one_field_index =
												$.makeArray(
													user_registration_form_builder_data.form_one_time_draggable_fields
												);
											if (
												length_of_required > 0 &&
												$.inArray(
													data_field_id,
													only_one_field_index
												) >= 0
											) {
												show_message(
													user_registration_form_builder_data
														.i18n_admin
														.i18n_user_required_field_already_there
												);
												$(ui.helper).remove();
												return;
											}
											var clone = $(ui.helper);
											var form_field_id =
												$(clone).attr("data-field-id");
											if (
												typeof form_field_id !==
												"undefined"
											) {
												var this_clone = $(ui.helper)
													.closest(
														".ur-grid-list-item"
													)
													.find(
														'li[data-field-id="' +
															$(this).attr(
																"data-field-id"
															) +
															'"]'
													);
												builder.populate_dropped_node(
													this_clone,
													form_field_id
												);
											}
										},
									})
									.disableSelection();
							},
							remove_selected_item: function () {
								var $this = this;
								$("body").on(
									"click",
									".ur-selected-item .ur-action-buttons .ur-trash",
									function (e) {
										var removed_item = $(this)
												.closest(".ur-selected-item ")
												.find(
													"[data-field='field_name']"
												)
												.val(),
											ele = $this,
											$ele = $(this);

										// Get fieldKey from data-field-key attribute.
										var fieldKey = $ele
											.closest(".ur-selected-item")
											.find(".ur-field")
											.data("field-key");

										// Get field name.
										var fieldName = $ele
											.closest(".ur-selected-item")
											.find(
												'.ur-general-setting.ur-general-setting-field-name input[name="ur_general_setting[field_name]"]'
											)
											.val();

										// Get label text from label tag, excluding any span tags
										var label = $ele
											.closest(".ur-selected-item")
											.find(".ur-label label")
											.contents()
											.filter(function () {
												return this.nodeType === 3; // Filter out non-text nodes (e.g. <span> tags)
											})
											.text()
											.trim();

										ur_confirmation(
											user_registration_form_builder_data
												.i18n_admin
												.i18n_are_you_sure_want_to_delete_field,
											{
												title: user_registration_form_builder_data
													.i18n_admin.i18n_msg_delete,
												showCancelButton: true,
												confirmButtonText:
													user_registration_form_builder_data
														.i18n_admin
														.i18n_choice_ok,
												cancelButtonText:
													user_registration_form_builder_data
														.i18n_admin
														.i18n_choice_cancel,
												ele: ele,
												$ele: $ele,
												removed_item: removed_item,
												confirm: function () {
													$ele.closest(
														".ur-selected-item "
													).remove();
													ele.check_grid();
													builder.manage_empty_grid();
													builder.manage_draggable_users_fields();

													// Remove item from conditional logic options
													$(
														'[class*="urcl-settings-rules_field_"] option[value="' +
															removed_item +
															'"]'
													).remove();

													// Remove Field from Form Setting Conditionally Assign User Role.
													$(
														'[class*="urcl-field-conditional-field-select"] option[value="' +
															removed_item +
															'"]'
													).remove();

													$(document.body).trigger(
														"ur_field_removed",
														[
															{
																fieldName:
																	fieldName,
																fieldKey:
																	fieldKey,
																label: label,
															},
														]
													);

													// To prevent click on whole item.
													return false;
												},
												reject: function () {
													return false;
												},
											}
										);
									}
								);
							},

							clone_selected_item: function () {
								$("body").on(
									"click",
									".ur-selected-item .ur-action-buttons  .ur-clone",
									function () {
										var data_field_key = $(this)
											.closest(".ur-selected-item ")
											.find(".ur-field")
											.attr("data-field-key");
										var selected_node = $(
											".ur-input-grids"
										).find(
											'.ur-field[data-field-key="' +
												data_field_key +
												'"]'
										);
										var length_of_required =
											selected_node.length;
										if (
											length_of_required > 0 &&
											$.inArray(
												data_field_key,
												user_registration_form_builder_data.form_one_time_draggable_fields
											) > -1
										) {
											URFormBuilder.show_message(
												user_registration_form_builder_data
													.i18n_admin
													.i18n_user_required_field_already_there_could_not_clone
											);
											return;
										}
										var clone = $(this)
											.closest(".ur-selected-item ")
											.clone();
										var label_node = clone.find(
											'input[data-field="field_name"]'
										);
										var regex = /\d+/g;
										var matches = label_node
											.val()
											.match(regex);
										var find_string =
											matches.length > 0
												? matches[matches.length - 1]
												: "";
										var label_string = label_node
											.val()
											.replace(find_string, "");
										clone
											.find(
												'input[data-field="field_name"]'
											)
											.attr(
												"value",
												label_string +
													new Date().getTime()
											);
										$(this)
											.closest(".ur-grid-list-item")
											.append(clone);

										var populated_item = clone
											.find("[data-field='field_name']")
											.val();
										URFormBuilder.manage_conditional_field_options(
											populated_item
										);
									}
								);
							},
							check_grid: function () {
								$(".ur-tabs").tabs({ disabled: [1] });
								$(".ur-tabs")
									.find("a")
									.eq(0)
									.trigger("click", ["triggered_click"]);
							},
						};
						builder.init();
						events.register();
					});
				};

				$(".ur-input-grids").ur_form_builder();
				$(".ur-tabs .ur-tab-lists").on(
					"click",
					"a.nav-tab",
					function () {
						$(".ur-tabs .ur-tab-lists")
							.find("a.nav-tab")
							.removeClass("active");
						$(this).addClass("active");
					}
				);
				$(".ur-tabs").tabs();
				$(".ur-tabs")
					.find("a")
					.eq(0)
					.trigger("click", ["triggered_click"]);
				$(".ur-tabs").tabs({ disabled: [1] });
			},
			manage_conditional_field_options: function (populated_item) {
				$(".ur-grid-lists .ur-selected-item .ur-general-setting").each(
					function () {
						var field_label = $(this)
							.closest(".ur-selected-item")
							.find(" .ur-admin-template .ur-label label")
							.text();
						var field_key = $(this)
							.closest(".ur-selected-item")
							.find(" .ur-admin-template .ur-field")
							.data("field-key");

						//strip certain fields
						if (
							"section_title" == field_key ||
							"html" == field_key ||
							"wysiwyg" == field_key ||
							"billing_address_title" == field_key ||
							"shipping_address_title" == field_key
						) {
							return;
						}

						var field_name = $(this)
							.find("[data-field='field_name']")
							.val();
						if (typeof field_name !== "undefined") {
							var select_value_for_user_role =
								$(
									".urcl-field-conditional-field-select option[value='" +
										field_name +
										"']"
								).length > 0;
							if (select_value_for_user_role === false) {
								// Append Field in Form Setting Conditionally Assign User Role.
								$(
									'[class*="urcl-field-conditional-field-select"]'
								).append(
									'<option value ="' +
										field_name +
										'" data-type="' +
										field_key +
										'">' +
										field_label +
										" </option>"
								);
							}
							//check if option exist in the given select
							var select_value =
								$(
									".urcl-rules select.ur_advance_setting.urcl-settings-rules_field_1 option[value='" +
										field_name +
										"']"
								).length > 0;
							if (select_value === false) {
								// Append Field in Field Options
								$(
									'[class*="urcl-settings-rules_field_"]'
								).append(
									'<option value ="' +
										field_name +
										'" data-type="' +
										field_key +
										'">' +
										field_label +
										" </option>"
								);

								if (field_name == populated_item) {
									$(
										'.urcl-rules select.ur_advance_setting.urcl-settings-rules_field_1.empty-fields option[value="' +
											populated_item +
											'"]'
									).remove();
								}
							} else {
								$(
									".urcl-rules select.ur_advance_setting.urcl-settings-rules_field_1.empty-fields"
								).append(
									'<option value ="' +
										field_name +
										'" data-type="' +
										field_key +
										'">' +
										field_label +
										" </option>"
								);
							}
						}
					}
				);
				$(
					".urcl-rules select.ur_advance_setting.urcl-settings-rules_field_1.empty-fields"
				).removeClass("empty-fields");
			},
			/**
			 * Handles all the operations performed on a selected field.
			 */
			handle_selected_item: function (selected_item) {
				$(".ur-selected-item").removeClass("ur-item-active");
				$(selected_item).addClass("ur-item-active");
				URFormBuilder.render_advance_setting($(selected_item));
				URFormBuilder.init_events();
				$(document).trigger("update_perfect_scrollbar");

				var field_key = $(selected_item)
					.find(".ur-field")
					.data("field-key");

				if (
					"country" === field_key ||
					"billing_country" === field_key ||
					"shipping_country" === field_key
				) {
					/**
					 * Bind UI actions for `Selective Countries` feature
					 */
					var $selected_countries_option_field = $(
						"#ur-setting-form select.ur-settings-selected-countries"
					);
					$selected_countries_option_field
						.on("change", function (e) {
							var selected_countries_iso_s = $(this).val();
							var html = "";
							var self = this;

							// Get html of selected countries
							if (Array.isArray(selected_countries_iso_s)) {
								selected_countries_iso_s.forEach(function (
									iso
								) {
									var country_name = $(self)
										.find('option[value="' + iso + '"]')
										.html();
									html +=
										'<option value="' +
										iso +
										'">' +
										country_name +
										"</option>";
								});
							}

							// Update default_value options in `Field Options` tab
							$(
								"#ur-setting-form select.ur-settings-default-value"
							).html(html);

							// Update default_value options (hidden)
							$(
								".ur-selected-item.ur-item-active select.ur-settings-default-value"
							).html(html);
						})
						.select2({
							placeholder: "Select countries...",
							selectionAdapter: SelectionAdapter,
							dropdownAdapter: DropdownAdapter,
							templateResult: function (data) {
								if (!data.id) {
									return data.text;
								}

								return $("<div></div>")
									.text(data.text)
									.addClass("wrap");
							},
							templateSelection: function (data) {
								if (!data.id) {
									return data.text;
								}
								var length = 0;

								if ($selected_countries_option_field.val()) {
									length =
										$selected_countries_option_field.val()
											.length;
								}

								return "Selected " + length + " country(s)";
							},
						})
						.on("change", function (e) {
							$(".urcl-rules, .urcl-conditional-group").each(
								function () {
									var $urcl_field = $(this).find(
										".urcl-field"
									).length
										? $(this).find(".urcl-field")
										: $(this).find(".urcl-form-group");
									var type = $urcl_field
										.find("select option:selected")
										.data("type");

									if (
										"country" === type ||
										"billing_country" === type ||
										"shipping_country" === type
									) {
										var field_name = $urcl_field
											.find("select option:selected")
											.val();
										var selected_value = $(this)
											.find(".urcl-value select")
											.val();
										var countries = $(
											'.ur-general-setting-field-name input[value="' +
												field_name +
												'"'
										)
											.closest(".ur-selected-item")
											.find(
												".ur-advance-selected_countries select option:selected"
											);
										var options_html = [];

										$(this)
											.find(".urcl-value select")
											.html(
												'<option value="">--select--</option>'
											);
										countries.each(function () {
											var country_iso = $(this).val();
											var country_name = $(this).text();

											options_html.push(
												'<option value="' +
													country_iso +
													'">' +
													country_name +
													"</option>"
											);
										});
										$(this)
											.find(".urcl-value select")
											.append(options_html.join(""));
										$(this)
											.find(".urcl-value select")
											.val(selected_value);
										$(this)
											.find(
												'.urcl-value select option[value="' +
													selected_value +
													'"]'
											)
											.attr("selected", "selected");
									}
								}
							);
						})
						/**
						 * The following block of code is required to fix the following issue:
						 * - When the dropdown is open, if the contents of this option's container changes, for example when a different field is
						 * activated, the behaviour of input tags changes. Specifically, when pressed space key inside ANY input tag, the dropdown
						 * APPEARS.
						 *
						 * P.S. The option we're talking about is `Selective Countries` for country field.
						 */
						.on("select2:close", function (e) {
							setTimeout(function () {
								$(":focus").trigger("blur");
							}, 1);
						});
				}

				$(document.body).trigger("ur_rendered_field_options");
				$(document.body).trigger("init_tooltips");
				$(document.body).trigger("init_field_options_toggle");
			},
			/**
			 * Render the advance setting for selected field.
			 *
			 * @param Object selected_obj Selected field in the form builder area.
			 */
			render_advance_setting: function (selected_obj) {
				var advance_setting = selected_obj
					.find(".ur-advance-setting-block")
					.clone();
				var general_setting = selected_obj
					.find(".ur-general-setting-block")
					.clone();
				var form = $("<form id='ur-setting-form'/>");
				$("#ur-tab-field-options").html("");
				form.append(general_setting);
				form.append(advance_setting);
				$("#ur-tab-field-options").append(form);
				// $("#ur-tab-field-options").append(advance_setting);
				$("#ur-tab-field-options")
					.find(".ur-advance-setting-block")
					.show();
				$("#ur-tab-field-options")
					.find(".ur-general-setting-block")
					.show();
				if ($(".ur-item-active").length === 1) {
					$(".ur-tabs").tabs().tabs("enable", 1);
					$(".ur-tabs")
						.find("a")
						.eq(1)
						.trigger("click", ["triggered_click"]);
				}
				$(".ur-options-list").sortable({
					containment: ".ur-general-setting-options",
				});
			},

			/**
			 * Trigger the changes from field settings and reflect the changes in the form fields in form builder area.
			 */
			init_events: function () {
				var general_setting = $(".ur-general-setting-field");
				$.each(general_setting, function () {
					var $this_obj = $(this);
					switch ($this_obj.attr("data-field")) {
						case "label":
							$this_obj.on("keyup", function () {
								URFormBuilder.trigger_general_setting_label(
									$(this)
								);
							});
							break;
						case "field_name":
						case "max_files":
						case "input_mask":
						case "hidden_value":
						case "custom_class":
							$this_obj.on("change", function () {
								URFormBuilder.trigger_general_setting_field_name(
									$(this)
								);
							});
						case "default_value":
							$this_obj.on("change", function () {
								if (
									"default_value" ===
									$this_obj.attr("data-field")
								) {
									if (
										$this_obj
											.closest(
												".ur-general-setting-block"
											)
											.hasClass(
												"ur-general-setting-select"
											)
									) {
										URFormBuilder.render_select_box(
											$(this)
										);
									} else if (
										$this_obj
											.closest(
												".ur-general-setting-block"
											)
											.hasClass(
												"ur-general-setting-radio"
											)
									) {
										URFormBuilder.render_radio($(this));
									} else if (
										$this_obj
											.closest(
												".ur-general-setting-block"
											)
											.hasClass(
												"ur-general-setting-checkbox"
											)
									) {
										URFormBuilder.render_check_box($(this));
									} else if (
										$this_obj
											.closest(
												".ur-general-setting-block"
											)
											.hasClass(
												"ur-general-setting-multiple_choice"
											)
									) {
										URFormBuilder.render_multiple_choice(
											$(this)
										);
									}
								}
							});
							break;
						case "options":
							$this_obj.on("keyup", function () {
								if (
									$this_obj
										.closest(".ur-general-setting-block")
										.hasClass(
											"ur-general-setting-select"
										) &&
									$this_obj
										.siblings(
											'input[data-field="default_value"]'
										)
										.is(":checked")
								) {
									URFormBuilder.render_select_box($(this));
								} else if (
									$this_obj
										.closest(".ur-general-setting-block")
										.hasClass("ur-general-setting-radio")
								) {
									URFormBuilder.render_radio($(this));
								} else if (
									$this_obj
										.closest(".ur-general-setting-block")
										.hasClass("ur-general-setting-checkbox")
								) {
									URFormBuilder.render_check_box($(this));
								} else if (
									$this_obj
										.closest(".ur-general-setting-block")
										.hasClass(
											"ur-general-setting-multiple_choice"
										)
								) {
									URFormBuilder.render_multiple_choice(
										$(this)
									);
								}

								URFormBuilder.trigger_general_setting_options(
									$(this)
								);
							});
							break;
						case "selling_price":
							if (!$this_obj.is(":checked")) {
								$(this)
									.closest(".ur-general-setting-block")
									.find(".ur-selling-price")
									.hide();
							}

							$this_obj.on("change", function () {
								$(this)
									.closest(".ur-general-setting-block")
									.find(".ur-selling-price")
									.toggle();

								$(".ur-selected-item.ur-item-active")
									.find(".ur-general-setting-block")
									.find(".ur-selling-price")
									.toggle();
							});
							$this_obj.on("change", function () {
								URFormBuilder.trigger_general_setting_selling_price(
									$(this)
								);
							});
							break;
						case "placeholder":
							$this_obj.on("keyup", function () {
								URFormBuilder.trigger_general_setting_placeholder(
									$(this)
								);
							});
							break;
						case "required":
							$this_obj.on("change", function () {
								URFormBuilder.trigger_general_setting_required(
									$(this)
								);
							});
							break;
						case "hide_label":
							$this_obj.on("change", function () {
								URFormBuilder.trigger_general_setting_hide_label(
									$(this)
								);
							});
							break;
						case "description":
						case "html":
							$this_obj.on("keyup", function () {
								URFormBuilder.trigger_general_setting_description(
									$(this)
								);
							});
							break;
					}
					$(document.body).trigger('ur_general_field_settings_to_update_form_fields_in_builder',[$this_obj]);
				});
				var advance_settings = $(
					"#ur-setting-form .ur_advance_setting"
				);

				$(".ur-settings-enable-min-max").on("change", function () {
					if ($(this).is(":checked")) {
						$(
							".ur-item-active .ur-advance-min_date, #ur-setting-form .ur-advance-min_date"
						).show();
						$(
							".ur-item-active .ur-advance-max_date, #ur-setting-form .ur-advance-max_date"
						).show();

						$("#ur-setting-form .ur-settings-min-date")
							.addClass("flatpickr-field")
							.flatpickr({
								disableMobile: true,
								onChange: function (
									selectedDates,
									dateStr,
									instance
								) {
									$(
										".ur-item-active .ur-settings-min-date"
									).val(dateStr);
								},
								onOpen: function (
									selectedDates,
									dateStr,
									instance
								) {
									instance.set(
										"maxDate",
										// new Date(
										$(
											".ur-item-active .ur-settings-max-date"
										).val()
										// )
									);
								},
							});

						$("#ur-setting-form .ur-settings-max-date")
							.addClass("flatpickr-field")
							.flatpickr({
								disableMobile: true,
								onChange: function (
									selectedDates,
									dateStr,
									instance
								) {
									$(
										".ur-item-active .ur-settings-max-date"
									).val(dateStr);
								},
								onOpen: function (
									selectedDates,
									dateStr,
									instance
								) {
									instance.set(
										"minDate",
										// new Date(
										$(
											".ur-item-active .ur-settings-min-date"
										).val()
										// )
									);
								},
							});
					} else {
						$(
							".ur-item-active .ur-advance-min_date, #ur-setting-form .ur-advance-min_date"
						).hide();
						$(
							".ur-item-active .ur-advance-max_date, #ur-setting-form .ur-advance-max_date"
						).hide();
					}
				});

				$.each(advance_settings, function () {
					var $this_node = $(this);
					switch ($this_node.attr("data-advance-field")) {
						case "date_format":
							$this_node.on("change", function () {
								URFormBuilder.trigger_general_setting_date_format(
									$(this)
								);
							});
							break;
						case "min_date":
							if (
								$(".ur-item-active")
									.find(".ur-settings-enable-min-max")
									.is(":checked")
							) {
								$(this)
									.addClass("flatpickr-field")
									.flatpickr({
										disableMobile: true,
										defaultDate: new Date(
											$(".ur-item-active")
												.find(".ur-settings-min-date")
												.val()
										),
										onChange: function (
											selectedDates,
											dateStr,
											instance
										) {
											$(".ur-item-active")
												.find(".ur-settings-min-date")
												.val(dateStr);
										},
										onOpen: function (
											selectedDates,
											dateStr,
											instance
										) {
											instance.set(
												"maxDate",
												new Date(
													$(".ur-item-active")
														.find(
															".ur-settings-max-date"
														)
														.val()
												)
											);
										},
									});
							} else {
								$(
									".ur-item-active .ur-advance-min_date, #ur-setting-form .ur-advance-min_date"
								).hide();
							}
							break;
						case "max_date":
							if (
								$(".ur-item-active")
									.find(".ur-settings-enable-min-max")
									.is(":checked")
							) {
								$(this)
									.addClass("flatpickr-field")
									.flatpickr({
										disableMobile: true,
										defaultDate: new Date(
											$(".ur-item-active")
												.find(".ur-settings-max-date")
												.val()
										),
										onChange: function (
											selectedDates,
											dateStr,
											instance
										) {
											$(".ur-item-active")
												.find(".ur-settings-max-date")
												.val(dateStr);
										},
										onOpen: function (
											selectedDates,
											dateStr,
											instance
										) {
											instance.set(
												"minDate",
												new Date(
													$(
														".ur-item-active .ur-settings-min-date"
													).val()
												)
											);
										},
									});
							} else {
								$(
									".ur-item-active .ur-advance-max_date, #ur-setting-form .ur-advance-max_date"
								).hide();
							}
							break;

						case "enable_prepopulate":
							if ($this_node.is(":checked")) {
								$(this)
									.closest(".ur-advance-setting-block")
									.find(".ur-advance-parameter_name")
									.show();
							} else {
								$(this)
									.closest(".ur-advance-setting-block")
									.find(".ur-advance-parameter_name")
									.hide();
							}

							$this_node.on("click", function () {
								var wrapper = $(
										".ur-selected-item.ur-item-active"
									),
									selector_field_name = $(this)
										.closest("#ur-setting-form")
										.find("[data-field='field_name']")
										.val(),
									active_field_name = wrapper
										.find("[data-field='field_name']")
										.val();
								wrapper
									.find(".ur-advance-setting-block")
									.find(
										'input[data-field="' +
											$(this).attr("data-field") +
											'"]'
									)
									.prop("checked", $(this).is(":checked"));

								if (selector_field_name === active_field_name) {
									if ($(this).is(":checked")) {
										$(this)
											.closest(
												".ur-advance-setting-block"
											)
											.find(".ur-advance-parameter_name")
											.show();
										$(".ur-selected-item.ur-item-active")
											.find(".ur-advance-parameter_name")
											.show();
									} else {
										$(this)
											.closest(
												".ur-advance-setting-block"
											)
											.find(".ur-advance-parameter_name")
											.hide();
										$(".ur-selected-item.ur-item-active")
											.find(".ur-advance-parameter_name")
											.hide();
									}
								}
							});
							break;
						case "autocomplete_address":
							if ($this_node.is(":checked")) {
								$(this)
									.closest(".ur-advance-setting-block")
									.find(".ur-advance-address_style")
									.show();
							} else {
								$(this)
									.closest(".ur-advance-setting-block")
									.find(".ur-advance-address_style")
									.hide();
							}

							$this_node.on("click", function () {
								var wrapper = $(
										".ur-selected-item.ur-item-active"
									),
									selector_field_name = $(this)
										.closest("#ur-setting-form")
										.find("[data-field='field_name']")
										.val(),
									active_field_name = wrapper
										.find("[data-field='field_name']")
										.val();
								wrapper
									.find(".ur-advance-setting-block")
									.find(
										'input[data-field="' +
											$(this).attr("data-field") +
											'"]'
									)
									.prop("checked", $(this).is(":checked"));

								if (selector_field_name === active_field_name) {
									if ($(this).is(":checked")) {
										$(this)
											.closest(
												".ur-advance-setting-block"
											)
											.find(".ur-advance-address_style")
											.show();
										$(".ur-selected-item.ur-item-active")
											.find(".ur-advance-address_style")
											.show();
									} else {
										$(this)
											.closest(
												".ur-advance-setting-block"
											)
											.find(".ur-advance-address_style")
											.hide();
										$(".ur-selected-item.ur-item-active")
											.find(".ur-advance-address_style")
											.hide();
									}
								}
							});
							break;
						case "validate_unique":
							if ($this_node.is(":checked")) {
								$(this)
									.closest(".ur-advance-setting-block")
									.find(".ur-advance-validation_message")
									.show();
							} else {
								$(this)
									.closest(".ur-advance-setting-block")
									.find(".ur-advance-validation_message")
									.hide();
							}

							$this_node.on("click", function () {
								var wrapper = $(
										".ur-selected-item.ur-item-active"
									),
									selector_field_name = $(this)
										.closest("#ur-setting-form")
										.find("[data-field='field_name']")
										.val(),
									active_field_name = wrapper
										.find("[data-field='field_name']")
										.val();
								wrapper
									.find(".ur-advance-setting-block")
									.find(
										'input[data-field="' +
											$(this).attr("data-field") +
											'"]'
									)
									.prop("checked", $(this).is(":checked"));

								if (selector_field_name === active_field_name) {
									if ($(this).is(":checked")) {
										$(this)
											.closest(
												".ur-advance-setting-block"
											)
											.find(
												".ur-advance-validation_message"
											)
											.show();
										$(".ur-selected-item.ur-item-active")
											.find(
												".ur-advance-validation_message"
											)
											.show();
									} else {
										$(this)
											.closest(
												".ur-advance-setting-block"
											)
											.find(
												".ur-advance-validation_message"
											)
											.hide();
										$(".ur-selected-item.ur-item-active")
											.find(
												".ur-advance-validation_message"
											)
											.hide();
									}
								}
							});
							break;
						case "enable_selling_price_single_item":
							if (!$this_node.is(":checked")) {
								$(this)
									.closest(".ur-advance-setting-block")
									.find(".ur-advance-selling_price")
									.hide();
							}

							$this_node.on("change", function () {
								$(this)
									.closest(".ur-advance-setting-block")
									.find(".ur-advance-selling_price")
									.toggle();

								$(".ur-selected-item.ur-item-active")
									.find(".ur-advance-selling_price")
									.toggle();
							});
							break;
						case "enable_pattern" :
							if (!$this_node.is(":checked")) {
								$(this)
									.closest(".ur-advance-setting-block")
									.find(".ur-advance-pattern_value")
									.hide();
									$(this)
									.closest(".ur-advance-setting-block")
									.find(".ur-advance-pattern_message")
									.hide();
							}

							$this_node.on("change", function () {
								$(this)
									.closest(".ur-advance-setting-block")
									.find(".ur-advance-pattern_value")
									.toggle();

									$(this)
									.closest(".ur-advance-setting-block")
									.find(".ur-advance-pattern_message")
									.toggle();
							});
							break;
					}
					var node_type = $this_node.get(0).tagName.toLowerCase();

					if (
						"country_advance_setting_default_value" ===
						$this_node.attr("data-id")
					) {
						$(".ur-builder-wrapper #ur-input-type-country")
							.find('option[value="' + $this_node.val() + '"]')
							.attr("selected", "selected");
					}
					var event = "change";
					switch (node_type) {
						case "input":
							event = "keyup click";
							break;
						case "select":
							event = "change";
							break;
						case "textarea":
							event = "keyup";
							break;
						default:
							event = "change";
					}

					if (
						"valid_file_type" ===
							$this_node.attr("data-advance-field") ||
						"payment_methods" ===
							$this_node.attr("data-advance-field")
					) {
						$this_node.select2();
					}

					$(this).on(event, function () {
						URFormBuilder.trigger_advance_setting(
							$this_node,
							node_type
						);
					});
					$(this).on("paste", function () {
						URFormBuilder.trigger_advance_setting(
							$this_node,
							node_type
						);
					});
				});
			},
			/**
			 * Reflects changes in label field of field settings into selected field in form builder area.
			 *
			 * @param object $label Label field from field settings.
			 */
			trigger_general_setting_label: function ($label) {
				var wrapper = $(".ur-selected-item.ur-item-active");
				wrapper.find(".ur-label").find("label").text($label.val());

				if (
					$(".ur-selected-item.ur-item-active .ur-general-setting")
						.find("[data-field='required']")
						.is(":checked")
				) {
					wrapper
						.find(".ur-label")
						.find("label")
						.append('<span style="color:red">*</span>');
				}

				wrapper
					.find(".ur-general-setting-block")
					.find(
						'input[data-field="' + $label.attr("data-field") + '"]'
					)
					.attr("value", $label.val());

				var field_name = $(
					".ur-selected-item.ur-item-active .ur-general-setting"
				)
					.find("[data-field='field_name']")
					.val();
				// Change label of field in conditional logic options
				$(
					'[class*="urcl-settings-rules_field_"] option[value="' +
						field_name +
						'"]'
				).text($label.val());

				// Change label of field in Form Setting Conditionally Assign User Role.
				$(
					'[class*="urcl-field-conditional-field-select"] option[value="' +
						field_name +
						'"]'
				).text($label.val());
			},
			/**
			 * Reflects changes in field name field of field settings into selected field in form builder area.
			 *
			 * @param object $label Label for field name from field settings.
			 */
			trigger_general_setting_field_name: function ($label) {
				var wrapper = $(".ur-selected-item.ur-item-active");
				var old_field_name = wrapper
					.find(".ur-general-setting-block")
					.find(
						'input[data-field="' + $label.attr("data-field") + '"]'
					)
					.attr("value");
				wrapper
					.find(".ur-general-setting-block")
					.find(
						'input[data-field="' + $label.attr("data-field") + '"]'
					)
					.attr("value", $label.val());

				// Change Field Name of field in conditional logic options
				$(
					'[class*="urcl-settings-rules_field_"] option[value="' +
						old_field_name +
						'"]'
				).attr("value", $label.val());

				// Change Field Name of field in Form Setting Conditionally Assign User Role.
				$(
					'[class*="urcl-field-conditional-field-select"] option[value="' +
						old_field_name +
						'"]'
				).attr("value", $label.val());
			},
			/**
			 * Reflects changes in textarea field of field settings into selected field in form builder area.
			 *
			 * @param object this_node Textarea field from field settings.
			 */
			render_text_area: function (value) {
				var wrapper = $(".ur-selected-item.ur-item-active");
				var field_type = wrapper.find(".ur-field");
				switch (field_type.attr("data-field-key")) {
					case "select":
						URFormBuilder.render_select_box(value);
						break;
					case "checkbox":
						URFormBuilder.render_check_box(value);
						break;
					case "radio":
						URFormBuilder.render_radio(value);
						break;
					case "multiple_choice":
						URFormBuilder.render_multiple_choice(value);
						break;
				}
				$(document.body).trigger("ur_sync_textarea_field_settings_in_selected_field_of_form_builder",[field_type,value]);
			},
			/**
			 * Reflects changes in select field of field settings into selected field in form builder area.
			 *
			 * @param object this_node Select field from field settings.
			 */
			render_select_box: function (this_node) {
				var value = this_node.val().trim();
				var wrapper = $(".ur-selected-item.ur-item-active");
				var checked_index = this_node.closest("li").index();
				var select = wrapper.find(".ur-field").find("select");

				select.html("");
				select.append(
					"<option value='" + value + "'>" + value + "</option>"
				);

				// Loop through options in active fields general setting hidden div.
				wrapper
					.find(
						".ur-general-setting-options > ul.ur-options-list > li"
					)
					.each(function (index, element) {
						var radio_input = $(element).find(
							'[data-field="default_value"]'
						);
						if (index === checked_index) {
							radio_input.prop("checked", true);
						} else {
							radio_input.prop("checked", false);
						}
					});
			},
			/**
			 * Reflects changes in radio field of field settings into selected field in form builder area.
			 *
			 * @param object this_node Radio field from field settings.
			 */
			render_radio: function (this_node) {
				var li_elements = this_node.closest("ul").find("li");
				var checked_index = undefined;
				var array_value = [];

				li_elements.each(function (index, element) {
					var value = $(element)
						.find("input.ur-type-radio-label")
						.val();
					value = value.trim();

					// To remove all HTML tags from a value.
					value = value.replace(/<\/?[^>]+(>|$)/g, "");

					radio = $(element)
						.find("input.ur-type-radio-value")
						.is(":checked");
					// Set checked elements index value
					if (radio === true) {
						checked_index = index;
					}

					if (
						array_value.every(function (each_value) {
							return each_value.value !== value;
						})
					) {
						array_value.push({ value: value, radio: radio });
					}
				});

				var wrapper = $(".ur-selected-item.ur-item-active");
				var radio = wrapper.find(".ur-field");
				radio.html("");

				for (var i = 0; i < array_value.length; i++) {
					if (array_value[i] !== "") {
						radio.append(
							'<label><input value="' +
								array_value[i].value.trim() +
								'" type="radio" ' +
								(array_value[i].radio ? "checked" : "") +
								" disabled>" +
								array_value[i].value.trim() +
								"</label>"
						);
					}
				}

				// Loop through options in active fields general setting hidden div.
				wrapper
					.find(
						".ur-general-setting-options > ul.ur-options-list > li"
					)
					.each(function (index, element) {
						var radio_input = $(element).find(
							'[data-field="default_value"]'
						);
						if (index === checked_index) {
							radio_input.prop("checked", true);
						} else {
							radio_input.prop("checked", false);
						}
					});
			},
			/**
			 * Reflects changes in checkbox field of field settings into selected field in form builder area.
			 *
			 * @param object this_node Checkbox field from field settings.
			 */
			render_check_box: function (this_node) {
				var array_value = [];
				var li_elements = this_node.closest("ul").find("li");
				var checked_index = this_node.closest("li").index();
				li_elements.each(function (index, element) {
					var value = $(element)
						.find("input.ur-type-checkbox-label")
						.val();
					value = value.trim();

					// To remove all HTML tags (opening or closing or self closing)from a string except for anchor tags.
					value = value.replace(/<(?!\/?a\b)[^>]+>/gi, "");

					// To remove attributes except "href, target, download, rel, hreflang, type, name, accesskey, tabindex, title" from anchor tag.
					value = value.replace(
						/(?!href|target|download|rel|hreflang|type|name|accesskey|tabindex|title)\b\w+=['"][^'"]*['"]/g,
						""
					);

					// To add a closing </a> tag to a string if an open <a> tag is present but not closed.
					if (/<a(?:(?!<\/a>).)*$/.test(value)) {
						value += "</a>";
					}

					checkbox = $(element)
						.find("input.ur-type-checkbox-value")
						.is(":checked");

					if (
						array_value.every(function (each_value) {
							return each_value.value !== value;
						})
					) {
						array_value.push({ value: value, checkbox: checkbox });
					}
				});

				var wrapper = $(".ur-selected-item.ur-item-active");
				var checkbox = wrapper.find(".ur-field");
				checkbox.html("");

				for (var i = 0; i < array_value.length; i++) {
					if (array_value[i] !== "") {
						array_value[i].value = array_value[i].value.replaceAll(
							'"',
							"'"
						);
						checkbox.append(
							'<label><input value="' +
								array_value[i].value.trim() +
								'" type="checkbox" ' +
								(array_value[i].checkbox ? "checked" : "") +
								" disabled>" +
								array_value[i].value.trim() +
								"</label>"
						);
					}
				}

				if ("checkbox" === this_node.attr("type")) {
					if (this_node.is(":checked")) {
						wrapper
							.find(
								".ur-general-setting-options li:nth(" +
									checked_index +
									') input[data-field="default_value"]'
							)
							.prop("checked", true);
					} else {
						wrapper
							.find(
								".ur-general-setting-options li:nth(" +
									checked_index +
									') input[data-field="default_value"]'
							)
							.prop("checked", false);
					}
				}
			},
			/**
			 * Reflects changes in multiple choice field of field settings into selected field in form builder area.
			 *
			 * @param object this_node  multiple choice  field from field settings.
			 *
			 * @since 2.0.3
			 */
			render_multiple_choice: function (this_node) {
				var array_value = [];
				var li_elements = this_node.closest("ul").find("li");
				var checked_index = this_node.closest("li").index();
				li_elements.each(function (index, element) {
					var label = $(element)
						.find("input.ur-type-checkbox-label")
						.val();
					var value = $(element)
						.find("input.ur-type-checkbox-money-input")
						.val();
					var sell_value = $(element)
						.find("input.ur-checkbox-selling-price-input")
						.val();
					var currency = $(element)
						.find("input.ur-type-checkbox-money-input")
						.attr("data-currency");

					label = label.trim();
					value = value.trim();
					sell_value = sell_value.trim();
					currency = currency.trim();
					checkbox = $(element)
						.find("input.ur-type-checkbox-value")
						.is(":checked");

					if (
						array_value.every(function (each_value) {
							return each_value.label !== label;
						})
					) {
						array_value.push({
							label: label,
							value: value,
							sell_value: sell_value,
							currency: currency,
							checkbox: checkbox,
						});
					}
				});

				var wrapper = $(".ur-selected-item.ur-item-active");
				var checkbox = wrapper.find(".ur-field");
				checkbox.html("");

				for (var i = 0; i < array_value.length; i++) {
					if (array_value[i] !== "") {
						checkbox.append(
							'<label><input value="' +
								array_value[i].label.trim() +
								'" type="checkbox" ' +
								(array_value[i].checkbox ? "checked" : "") +
								" disabled>" +
								array_value[i].label.trim() +
								" - " +
								array_value[i].currency.trim() +
								" " +
								array_value[i].value.trim() +
								"</label>"
						);
					}
				}

				if ("checkbox" === this_node.attr("type")) {
					if (this_node.is(":checked")) {
						wrapper
							.find(
								".ur-general-setting-options li:nth(" +
									checked_index +
									') input[data-field="default_value"]'
							)
							.prop("checked", true);
					} else {
						wrapper
							.find(
								".ur-general-setting-options li:nth(" +
									checked_index +
									') input[data-field="default_value"]'
							)
							.prop("checked", false);
					}
				}
			},
			/**
			 * Reflects changes in options of choice fields of field settings into selected field in form builder area.
			 *
			 * @param object $label Options of choice fields from field settings.
			 */
			trigger_general_setting_options: function ($label) {
				var wrapper = $(".ur-selected-item.ur-item-active");
				var index = $label.closest("li").index();
				var multiple_choice = $label.attr("data-field-name");

				if ("multiple_choice" === multiple_choice) {
					wrapper
						.find(
							".ur-general-setting-block li:nth(" +
								index +
								') input[name="' +
								$label.attr("name") +
								'"]'
						)
						.val($label.val());
				} else if ( "captcha" === $label.attr("data-field-name") ) {
					wrapper
					.find(
						".ur-general-setting-block li:nth(" +
							index +
							') input[name="' +
							$label.attr("name") +
							'"]'
					)
					.val($label.val());
				} else {
					wrapper
						.find(
							".ur-general-setting-block li:nth(" +
								index +
								') input[data-field="' +
								$label.attr("data-field") +
								'"]'
						)
						.val($label.val());
				}
				wrapper
					.find(
						".ur-general-setting-block li:nth(" +
							index +
							') input[data-field="default_value"]'
					)
					.val($label.val());

				$label
					.closest("li")
					.find('[data-field="default_value"]')
					.val($label.val());
			},
			/**
			 * Reflects changes in enable selling price field of field settings into selected field in form builder area.
			 *
			 * @param object $label enable selling price field of fields from field settings.
			 */
			trigger_general_setting_selling_price: function ($label) {
				var wrapper = $(".ur-selected-item.ur-item-active");

				wrapper
					.find(".ur-general-setting-block")
					.find(
						'input[data-field="' + $label.attr("data-field") + '"]'
					)
					.prop("checked", $label.is(":checked"));
			},
			/**
			 * Reflects changes in descriptions field of field settings into selected field in form builder area.
			 *
			 * @param object $label Descriptions field of fields from field settings.
			 */
			trigger_general_setting_description: function ($label) {
				var wrapper = $(".ur-selected-item.ur-item-active");
				wrapper
					.find(".ur-field")
					.find("textarea")
					.attr("description", $label.val());
				wrapper
					.find(".ur-general-setting-block")
					.find(
						'textarea[data-field="' +
							$label.attr("data-field") +
							'"]'
					)
					.val($label.val());
			},
			/**
			 * Reflects changes in placeholder field of field settings into selected field in form builder area.
			 *
			 * @param object $label Placeholder field of fields from field settings.
			 */
			trigger_general_setting_placeholder: function ($label) {
				var wrapper = $(".ur-selected-item.ur-item-active");
				wrapper
					.find(".ur-field")
					.find("input")
					.attr("placeholder", $label.val());
				wrapper
					.find(".ur-general-setting-block")
					.find(
						'input[data-field="' + $label.attr("data-field") + '"]'
					)
					.val($label.val());
			},
			/**
			 * Reflects changes in required field of field settings into selected field in form builder area.
			 *
			 * @param object $label Required field of fields from field settings.
			 */
			trigger_general_setting_required: function ($label) {
				var wrapper = $(".ur-selected-item.ur-item-active");
				wrapper
					.find(".ur-general-setting-block")
					.find(
						'input[data-field="' + $label.attr("data-field") + '"]'
					)
					.prop("checked", $label.is(":checked"));

				wrapper
					.find(".ur-label")
					.find("label")
					.find("span:contains(*)")
					.remove();

				if ($label.is(":checked")) {
					wrapper
						.find(".ur-label")
						.find("label")
						.append('<span style="color:red">*</span>');
				}
			},
			/**
			 * Reflects changes in required field of field settings into selected field in form builder area.
			 *
			 * @param object $label Required field of fields from field settings.
			 */
			trigger_general_setting_date_format: function ($label) {
				var wrapper = $(".ur-selected-item.ur-item-active");
				wrapper
					.find(".ur-field")
					.find("input")
					.attr("placeholder", $label.val());
			},
			/**
			 * Reflects changes in hide label field of field settings into selected field in form builder area.
			 *
			 * @param object $label Hide label field of fields from field settings.
			 */
			trigger_general_setting_hide_label: function ($label) {
				var wrapper = $(".ur-selected-item.ur-item-active");

				wrapper
					.find(".ur-general-setting-block")
					.find(
						'input[data-field="' + $label.attr("data-field") + '"]'
					)
					.prop("checked", $label.is(":checked"));

				if ($label.is(":checked")) {
					wrapper.find(".ur-label").find("label").hide();
				} else {
					wrapper.find(".ur-label").find("label").show();
				}
			},

			/**
			 * Reflects changes in hide advance settings of field settings into selected field in form builder area.
			 *
			 * @param object $label Advance settings of fields from field settings.
			 */
			trigger_advance_setting: function ($this_node, node_type) {
				var wrapper = $(".ur-selected-item.ur-item-active");
				var this_node_id = $this_node.attr("data-id");
				var hidden_node = wrapper
					.find(".ur-advance-setting-block")
					.find('[data-id="' + this_node_id + '"]');

				switch (node_type) {
					case "input":
						if ($this_node.attr("type") === "checkbox") {
							hidden_node.prop(
								"checked",
								$this_node.is(":checked")
							);
						} else {
							hidden_node.val($this_node.val());
						}
						break;
					case "select":
						hidden_node.find("option").prop("selected", false);

						if ($this_node.prop("multiple")) {
							var selected_options = $this_node.val();
							hidden_node.val(selected_options);
						} else {
							hidden_node
								.find(
									'option[value="' + $this_node.val() + '"]'
								)
								.prop("selected", true);
						}
						break;
					case "textarea":
						hidden_node.val($this_node.val());
						URFormBuilder.render_text_area($this_node.val());
						break;
				}
			},
			/**
			 * Sweetalert2 alert popup.
			 *
			 * @param string message Message to be shown in popup.
			 * @param object options Options for popup.
			 */
			ur_alert: function (message, options) {
				if ("undefined" === typeof options) {
					options = {};
				}
				Swal.fire({
					icon: "error",
					title: options.title,
					text: message,
					customClass:
						"user-registration-swal2-modal user-registration-swal2-modal--center",
				});
			},
			/**
			 * Handle all actions performed on the choice field options.
			 */
			init_choice_field_options: function () {
				//Handle Sorting for options of choice fields
				$(document).on(
					"sortstop",
					".ur-options-list",
					function (event, ui) {
						var $this_obj = $(this);
						URFormBuilder.handle_options_sort($this_obj);
					}
				);

				//Handle adding of options in choice fields on click.
				$(document).on("click", ".ur-options-list .add", function (e) {
					e.preventDefault();
					var $this = $(this);
					URFormBuilder.add_choice_field_option($this);
				});

				//Handle remmoval of options in choice fields on click.
				$(document).on(
					"click",
					".ur-options-list .remove",
					function (e) {
						e.preventDefault();
						var $this = $(this);
						URFormBuilder.remove_choice_field_option($this);
					}
				);
			},
			/**
			 * Clone options and render them for choice fields in form builder.
			 *
			 * @param object $this_obj The field option to clone and render.
			 */
			handle_options_sort: function ($this_obj) {
				URFormBuilder.ur_clone_options($this_obj);
				if (
					$this
						.closest(".ur-general-setting-block")
						.hasClass("ur-general-setting-radio")
				) {
					URFormBuilder.render_radio($this_obj);
				} else if (
					$this
						.closest(".ur-general-setting-block")
						.hasClass("ur-general-setting-checkbox")
				) {
					URFormBuilder.render_check_box($this_obj);
				} else if (
					$this
						.closest(".ur-general-setting-block")
						.hasClass("ur-general-setting-multiple_choice")
				) {
					URFormBuilder.render_multiple_choice($this_obj);
				}
			},
			/**
			 * Clone choice field options.
			 *
			 * @param object $this_obj The field option to clone.
			 */
			ur_clone_options: function ($this_obj) {
				var cloning_options = $this_obj.clone(true, true);
				var wrapper = $(".ur-selected-item.ur-item-active");
				var cloning_element = wrapper.find(
					".ur-general-setting-options .ur-options-list"
				);
				cloning_element.html("");
				cloning_element.replaceWith(cloning_options);
			},
			/**
			 * Add a new option in choice field when called.
			 *
			 * @param object $this_obj The field option to add.
			 * @param string value The value of the option.
			 */
			add_choice_field_option: function ($this, value) {
				var $wrapper = $(".ur-selected-item.ur-item-active"),
					this_index = $this.parent("li").index(),
					cloning_element = $this.parent("li").clone(true, true);

				cloning_element
					.find('input[data-field="options"]')
					.val(typeof value !== "undefined" ? value : "");
				cloning_element
					.find('input[data-field="default_value"]')
					.prop("checked", false);
				cloning_element.find('select[data-field="options"]').val("");

				$this.parent("li").after(cloning_element);
				$wrapper
					.find(
						".ur-general-setting-options .ur-options-list > li:nth( " +
							this_index +
							" )"
					)
					.after(cloning_element.clone(true, true));

				if (
					$this
						.closest(".ur-general-setting-block")
						.hasClass("ur-general-setting-radio")
				) {
					URFormBuilder.render_radio($this);
				} else if (
					$this
						.closest(".ur-general-setting-block")
						.hasClass("ur-general-setting-checkbox")
				) {
					URFormBuilder.render_check_box($this);
				} else if (
					$this
						.closest(".ur-general-setting-block")
						.hasClass("ur-general-setting-multiple_choice")
				) {
					URFormBuilder.render_captcha_question($this);
				} else if (
					$this
						.closest(".ur-general-setting-block")
						.hasClass("ur-general-setting-captcha-question")
				) {
					URFormBuilder.render_captcha_question($this);
				}

				$(document.body).trigger("ur_field_option_changed", [
					{ action: "add", wrapper: $wrapper },
				]);
			},
			/**
			 * Remove an option in choice field when called.
			 *
			 * @param object $this_obj The field option to remove.
			 */
			remove_choice_field_option: function ($this) {
				var $parent_ul = $this.closest("ul"),
					$any_siblings = $parent_ul.find("li"),
					$wrapper = $(".ur-selected-item.ur-item-active"),
					this_index = $this.parent("li").index();

				if ($parent_ul.find("li").length > 1) {
					$this.parent("li").remove();
					$wrapper
						.find(
							".ur-general-setting-options .ur-options-list > li:nth( " +
								this_index +
								" )"
						)
						.remove();

					if (
						$any_siblings
							.closest(".ur-general-setting-block")
							.hasClass("ur-general-setting-radio")
					) {
						URFormBuilder.render_radio($any_siblings);
					} else if (
						$any_siblings
							.closest(".ur-general-setting-block")
							.hasClass("ur-general-setting-checkbox")
					) {
						URFormBuilder.render_check_box($any_siblings);
					} else if (
						$any_siblings
							.closest(".ur-general-setting-block")
							.hasClass("ur-general-setting-multiple_choice")
					) {
						URFormBuilder.render_multiple_choice($any_siblings);
					} else if (
						$any_siblings
							.closest(".ur-general-setting-block")
							.hasClass("ur-general-setting-captcha-question")
					) {
						URFormBuilder.render_captcha_question($any_siblings);
					}
				}

				$(document.body).trigger("ur_field_option_changed", [
					{ action: "remove", wrapper: $wrapper },
				]);
			},
		};

		URFormBuilder.init();

		/**
		 * Load flatpickr and handle changes in date field settings.
		 */
		$(document).ready(function () {
			var date_flatpickrs = {};

			$(document.body).on("click", ".ur-flatpickr-field", function () {
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
						onOpen: function (selectedDates, dateStr, instance) {
							instance.set(
								"minDate",
								date_selector.data("min-date")
							);
							instance.set(
								"maxDate",
								date_selector.data("max-date")
							);
						},
					});
					date_flatpickrs[field_id] = date_flatpickr;
				}

				if (date_flatpickr) {
					date_flatpickr.open();
				}
			});
		});
		/**
		 * For toggling headings.
		 */
		$(document).on("click", ".ur-toggle-heading", function () {
			if ($(this).hasClass("closed")) {
				$(this).removeClass("closed");
			} else {
				$(this).addClass("closed");
			}
			$(this)
				.parent(".user-registration-field-option-group")
				.toggleClass("closed")
				.toggleClass("open");
			var field_list = $(this).find(" ~ .ur-registered-list")[0];
			$(field_list).slideToggle();

			// For `Field Options` section
			$(this).siblings(".ur-toggle-content").stop().slideToggle();
		});

		$(document.body)
			.on("init_field_options_toggle", function () {
				$(".user-registration-field-option-group.closed").each(
					function () {
						$(this).find(".ur-toggle-content").hide();
					}
				);
			})
			.trigger("init_field_options_toggle");

		/**
		 * For toggling quick links content.
		 */

		$(document.body).on("click", ".ur-quick-links-content", function (e) {
			e.stopPropagation();
		});
		$(document.body).on("click", ".ur-button-quick-links", function (e) {
			e.stopPropagation();
			$(".ur-quick-links-content").slideToggle();
		});
		$(document.body).on("click", function (e) {
			if (!$(".ur-quick-links-content").is(":hidden")) {
				$(".ur-quick-links-content").slideToggle();
			}
		});

		$(document)
			.find(
				"input[name='user_registration_form_setting_minimum_password_strength']"
			)
			.each(function () {
				$(this).on("click", function () {
					$(this)
						.closest(".ur-radio-group-list")
						.find(".active")
						.removeClass("active");
					$(this)
						.closest(".ur-radio-group-list")
						.find(
							"input[name='user_registration_form_setting_minimum_password_strength']"
						)
						.prop("checked", false);
					$(this)
						.closest(".ur-radio-group-list--item")
						.addClass("active");

					$(this).prop("checked", true);
				});
			});

		$(document).on("click", function () {
			if ($(document).find(".ur-smart-tags-list").is(":visible")) {
				$(".ur-smart-tags-list").hide();
			}
		});

		$(".ur-smart-tags-list").hide();

		$(document.body).on(
			"click",
			".ur-smart-tags-list-button",
			function (e) {
				e.stopPropagation();
				$(".ur-smart-tags-list").hide();
				$(this).parent().find(".ur-smart-tags-list").toggle("show");
			}
		);

		$(document.body).on("click", ".ur-select-smart-tag", function (event) {
			event.preventDefault();
			var smart_tag;
			input_value = $(this)
				.parent()
				.parent()
				.parent()
				.find("input")
				.val();
			smart_tag = $(this).data("key");
			input_value = smart_tag;
			var inputElement = $(this).parent().parent().parent().find("input"),
			advanceFieldData = inputElement.data("advance-field"),
			fieldData = inputElement.data("field"),
			field_name = advanceFieldData !== undefined ? advanceFieldData : fieldData;
			update_input(field_name,input_value);

			$(this).parent().parent().parent().find("input").val(input_value);
			$(document.body).find(".ur-smart-tags-list").hide();
		});

		$(document.body).on(
			"change",
			".ur_advance_setting.ur-settings-default-value",
			function () {
				input_value = $(this).val();
				field_name = $(this).data("advance-field");
				update_input(input_value);
			}
		);
		$(document.body).on(
			"change",
			".ur-general-setting.ur-general-setting-hidden-value input",
			function () {
				input_value = $(this).val();
				field_name = $(this).data("field");
				update_input(input_value);
			}
		);

		$(document.body).on(
			"change",
			".ur_advance_setting.ur-settings-pattern_value",
			function () {
				input_value = $(this).val();
				field_name = $(this).data("advance-field");
				update_input(input_value);
			}
		);

		/**
		 * For update the default value.
		 */
		function update_input(field_name,input_value) {
			active_field = $(".ur-item-active");
			target_input_field = $(active_field).find(
				".user-registration-field-option-group.ur-advance-setting-block"
			);
			ur_toggle_content = target_input_field.find(
				".ur-advance-setting.ur-advance-default_value"
			);
			target_input = $(ur_toggle_content).find(
				'input[data-advance-field="' + field_name + '"]'
			);
			target_textarea = $(ur_toggle_content).find(
				'input[data-advance-field="' + field_name + '"]'
			);

			target_input_hidden_field = $(active_field).find(
				".ur-general-setting-block"
			);
			ur_toggle_hidden_content = target_input_hidden_field.find(
				".ur-general-setting.ur-general-setting-hidden-value"
			);
			target_hidden_input = $(ur_toggle_hidden_content).find(
				'input[data-field="' + field_name + '"]'
			);
			// pattern value
			ur_toggle_pattern_content = target_input_field.find(
				".ur-advance-setting.ur-advance-pattern_value"
			);
			target_pattern_input = $(ur_toggle_pattern_content).find(
				'input[data-advance-field="' + field_name + '"]'
			);
			target_input.val(input_value);
			target_textarea.val(input_value);
			target_hidden_input.val(input_value);
			target_pattern_input.val(input_value);
		}

		/**
		 * This block of code is for the "Selected Countries" option of "Country" field
		 *
		 * Doc: https://select2.org/
		 * Ref: https://jsfiddle.net/Lkkm2L48/7/
		 */
		var SelectionAdapter, DropdownAdapter;
		$.fn.select2.amd.require(
			[
				"select2/selection/single",
				"select2/selection/placeholder",
				"select2/dropdown",
				"select2/dropdown/search",
				"select2/dropdown/attachBody",
				"select2/utils",
				"select2/selection/eventRelay",
			],
			function (
				SingleSelection,
				Placeholder,
				Dropdown,
				DropdownSearch,
				AttachBody,
				Utils,
				EventRelay
			) {
				// Add placeholder which shows current number of selections
				SelectionAdapter = Utils.Decorate(SingleSelection, Placeholder);

				// Allow to flow/fire events
				SelectionAdapter = Utils.Decorate(SelectionAdapter, EventRelay);

				// Add search box in dropdown
				DropdownAdapter = Utils.Decorate(Dropdown, DropdownSearch);

				// Add attach-body in dropdown
				DropdownAdapter = Utils.Decorate(DropdownAdapter, AttachBody);

				/**
				 * Create UnSelectAll Adapter for unselect-all button
				 *
				 * Ref: http://jsbin.com/seqonozasu/1/edit?html,js,output
				 */
				function UnselectAll() {}
				UnselectAll.prototype.render = function (decorated) {
					var self = this;
					var $rendered = decorated.call(this);
					var $unSelectAllButton = $(
						'<button class="button button-secondary button-medium ur-unselect-all-countries-button" type="button">Unselect All</button>'
					);

					$unSelectAllButton.on("click", function () {
						self.$element.val([]);
						self.$element.trigger("change");
						self.trigger("close");
					});
					$rendered
						.find(".select2-dropdown")
						.prepend($unSelectAllButton);

					return $rendered;
				};

				// Add unselect all button in dropdown
				DropdownAdapter = Utils.Decorate(DropdownAdapter, UnselectAll);

				/**
				 * Create SelectAll Adapter for select-all button
				 *
				 * Ref: http://jsbin.com/seqonozasu/1/edit?html,js,output
				 */
				function SelectAll() {}
				SelectAll.prototype.render = function (decorated) {
					var self = this;
					var $rendered = decorated.call(this);
					var $selectAllButton = $(
						'<button class="button button-secondary button-medium ur-select-all-countries-button" type="button">Select All</button>'
					);

					$selectAllButton.on("click", function () {
						var $options = self.$element.find("option");
						var values = [];

						$options.each(function () {
							values.push($(this).val());
						});
						self.$element.val(values);
						self.$element.trigger("change");
						self.trigger("close");
					});
					$rendered
						.find(".select2-dropdown")
						.prepend($selectAllButton);

					return $rendered;
				};

				// Add select all button in dropdown
				DropdownAdapter = Utils.Decorate(DropdownAdapter, SelectAll);
			}
		);

		// Prevent invalid input for Max Upload Size setting and Maximum upload limit input.

		$(document.body).on(
			"input",
			'[data-advance-field="max_upload_size"], [data-field="max_files"]',
			function () {
				var $this = $(this);
				var inputValue = $this.val();
				inputValue = inputValue.replace(/[^0-9]/g, "");
				$this.val(inputValue);
			}
		);

		$(document.body).on(
			"focusout",
			'[data-advance-field="max_upload_size"], [data-field="max_files"]',
			function () {
				var $this = $(this);
				var inputValue = $this.val();

				if ("" === inputValue || 0 === parseInt(inputValue)) {
					inputValue = "";
				} else {
					inputValue = parseInt(inputValue); // Remove prefixing zeros(0).
				}

				$this.val(inputValue);
			}
		);
	});
})(jQuery, window.user_registration_form_builder_data);
