(function ($) {
	var UR_SMSIntegration_Admin = {
		init: function () {
			$(".ur_sms_integration_account_action_button").on("click", function () {
				var sms_integration_wrapper = $(this).closest(".sms_integration-wrapper");
				var client_number = $("#ur_twilio_client_number").val();
				var client_id = $("#ur_twilio_client_id").val();
				var connection_name = $("#ur_twilio_client_auth").val();
				var form_data = new FormData();

				form_data.append("ur_twilio_client_number", client_number);
				form_data.append("ur_twilio_client_id", client_id);
				form_data.append("ur_twilio_client_auth", connection_name);
				form_data.append(
					"action",
					"user_registration_sms_integration_connection_action"
				);
				form_data.append(
					"security",
					ur_sms_integration_params.ur_sms_integration_connection_save
				);

				$.ajax({
					url: ur_sms_integration_params.ajax_url,
					dataType: "json", // what to expect back from the PHP script, if anything
					cache: false,
					contentType: false,
					processData: false,
					data: form_data,
					type: "post",
					beforeSend: function () {
						var spinner =
							'<span class="ur-spinner is-active"></span>';

						$(".ur_sms_integration_account_action_button").append(
							spinner
						);
					},
					complete: function (response) {
						$(".ur_sms_integration_account_action_button")
							.find(".ur-spinner")
							.remove();
						var connected_sms_integration_wrapper =
							sms_integration_wrapper.find(
								".ur-integration-connected-accounts"
							);

						if (response.responseJSON.success === true) {
							var $new_account =
								response.responseJSON.data.new_connection;

							var html = "";
							if (0 < connected_sms_integration_wrapper.length) {
								html += "<li>";
								html +=
									'<div class="ur-integration-connected-accounts--label"><strong> ' +
									$new_account["label"] +
									"</strong></div>";
								html +=
									'<div class="ur-integration-connected-accounts--date">Connected on ' +
									$new_account["date"] +
									"</div>";
								html +=
									'<div class="ur-integration-connected-accounts--disconnect">';
								html +=
									"<a href='#' class='disconnect ur-sms_integration-disconnect-account' data-key='" +
									$new_account["client_number"] +
									"' > " +
									ur_sms_integration_params.i18n_disconnect +
									"</a>";
								html += "</div>";
								html += "</li>";

								connected_sms_integration_wrapper.append(html);
							} else {
								html +=
									'<div id="sms_integration_accounts" class="postbox">';
								html +=
									'<ul class="ur-integration-connected-accounts">';
								html += "<li>";
								html +=
									'<div class="ur-integration-connected-accounts--label"><strong> ' +
									$new_account["label"] +
									"</strong></div>";
								html +=
									'<div class="ur-integration-connected-accounts--date">Connected on ' +
									$new_account["date"] +
									"</div>";
								html +=
									'<div class="ur-integration-connected-accounts--disconnect">';
								html +=
									"<a href='#' class='disconnect ur-sms_integration-disconnect-account' data-key='" +
									$new_account["client_number"] +
									"' > " +
									ur_sms_integration_params.i18n_disconnect +
									"</a>";
								html += "</div>";
								html += "</li>";
								html += "</ul>";
								html += "</div>";
								$(".sms_integration-wrapper").append(html);
								$(".sms_integration-wrapper")
									.closest(".user-registration-card")
									.find(".integration-status")
									.addClass(
										"ur-integration-account-connected"
									);
							}
							$(".ur-sms_integration_notice").remove();
							$("#ur_twilio_client_number").val("");
							$("#ur_twilio_client_id").val("");
							$("#ur_twilio_client_auth").val("");
						} else {
							$(".ur-sms_integration_notice").remove();
							var message_string =
								'<div id="message" class="error inline ur-sms_integration_notice"><p><strong>' +
								response.responseJSON.data.message +
								"</strong></p></div>";
							sms_integration_wrapper
								.closest(".ur-export-users-page")
								.prepend(message_string);
						}
					},
				});
			});

			$(document).on(
				"click",
				".ur-sms_integration-disconnect-account",
				function (e) {
					UR_SMSIntegration_Admin.disconnect_connection(this, e);
				}
			);

			// Save SMS Integration to $_POST
			$(document).on(
				"user_registration_admin_before_form_submit",
				function (event, data) {
					var $client_number = $("#ur_sms_integration_account").val();

					var integration =
						UR_SMSIntegration_Admin.save_sms_integration_form_settings(
							$client_number
						);
					if ("undefined" !== typeof $client_number) {
						data.data["ur_sms_integration_integration"] = integration;
					}
				}
			);

			//reset list fields on list change.
			$(document).on(
				"change",
				"#ur_sms_integration_integration_list_id",
				function () {
					var list_fields_option = $(
						".ur_sms_integration_fields .column-form-fields"
					).find("select");
					$.each(list_fields_option, function(){
						if ($(this).val() != "user_email") {
							$(this).prop("selectedIndex", 0);
						}
					});
				}
			);
		},
		disconnect_connection: function (el, e) {
			e.preventDefault();
			var $this = $(el);
			data = {
				action: "user_registration_sms_integration_connection_disconnect_action",
				security:
					ur_sms_integration_params.ur_sms_integration_connection_disconnect,
				client_number: $this.data("key"),
			};
			ur_confirmation(
				user_registration_form_builder_data.i18n_admin
					.i18n_are_you_sure_want_to_delete_row,
				{
					title: user_registration_form_builder_data.i18n_admin
						.i18n_msg_delete,
					confirm: function () {
						$.ajax({
							url: ur_sms_integration_params.ajax_url,
							method: "POST",
							data: data,
						}).done(function (response) {
							if (response.success === true) {
								Swal.fire({
									icon: "success",
									title: response.data.message,
									customClass:
										"user-registration-swal2-modal user-registration-swal2-modal--center",
									showConfirmButton: false,
									timer: 1000,
								}).then(function (result) {
									window.location.reload();
								});
							} else {
								Swal.showValidationMessage(
									response.data.message
								);
								$(".swal2-actions").removeClass(
									"swal2-loading"
								);
								$(".swal2-actions")
									.find("button")
									.prop("disabled", false);
							}
						});
					},
					reject: function () {
						// Do Nothing.
					},
				}
			);
		},
		/**
		 * Save SMS Form Settings from form builder
		 */
		save_sms_integration_form_settings: function ($client_number) {
			var list_id = $("#ur_sms_integration_integration_list_id").val();
			var fields = $(".ur_sms_integration_fields table select");
			var double_optin = $("#ur_sms_integration_double_optin").is(":checked");
			var list_fields = {};
			$.each(fields, function (key, field) {
				list_fields[field.id] = field.value;
			});

			var conditional_logic_element = $(
				"div[data-source='sms_integration']"
			).closest(".ur_conditional_logic_container");
			var form_fields = $(conditional_logic_element).find(
				".ur-conditional-wrapper"
			);
			var enable_conditional_logic = $(conditional_logic_element)
				.find("#ur_use_conditional_logic")
				.is(":checked");
			var conditional_logic_data = {};
			$.each(form_fields, function (key, field) {
				conditional_logic_data["conditional_field"] = $(
					conditional_logic_element
				)
					.find(".ur_conditional_field")
					.val();
				conditional_logic_data["conditional_operator"] = $(
					conditional_logic_element
				)
					.find(".ur-conditional-condition")
					.val();
				conditional_logic_data["conditional_value"] = $(
					conditional_logic_element
				)
					.find(".ur-conditional-input")
					.val();
			});
			return {
				client_number: $client_number,
				list_id: list_id,
				list_fields: JSON.stringify(list_fields),
				double_optin: double_optin,
				enable_conditional_logic: enable_conditional_logic,
				conditional_logic_data: conditional_logic_data,
			};
		},
	};
	$(document).ready(function () {
		UR_SMSIntegration_Admin.init();
	});
})(jQuery);
