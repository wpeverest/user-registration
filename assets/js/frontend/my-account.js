/* global  user_registration_params  */
jQuery(function ($) {
	var user_registration_profile_picture_upload = {
		init: function () {
			this.init_event();
			this.handle_user_logout();
		},

		/**
		 * Sends the picture, the user is willing to upload as an ajax request
		 * and receives output in order to process any errors occured during profile picture upload
		 * or to display a preview of the profile picture on the frontend.
		 *
		 * @since  1.8.5
		 *
		 * @param {Function} $node Executes once the profile picture upload triggers an event.
		 */
		profile_picture_upload: function ($node) {
			var url =
				user_registration_params.ajax_url +
				"?action=user_registration_profile_pic_upload&security=" +
				user_registration_params.user_registration_profile_picture_upload_nonce;
			var formData = new FormData();
			var $this = $node;
			formData.append("file", $this[0].files[0]);

			var upload_node = $this
				.closest(".button-group")
				.find(".user_registration_profile_picture_upload");
			var upload_node_value = upload_node.text();

			var file_data = $.ajax({
				url: url,
				data: formData,
				type: "POST",
				processData: false,
				contentType: false,
				// tell jQuery not to set contentType
				beforeSend: function () {
					upload_node.text(
						user_registration_params.user_registration_profile_picture_uploading
					);
				},
				complete: function (ajax_response) {
					var message = "",
						profile_pic_url = "",
						attachment_id = "";

					$this.val("");

					var response_obj = JSON.parse(ajax_response.responseText);

					message = response_obj.data.message;

					if (!response_obj.success) {
						message =
							'<p class="uraf-profile-picture-error user-registration-error">' +
							message +
							"</p>";
					}

					if (response_obj.success) {
						message = "";

						// Gets the profile picture url and displays the picture on frontend
						profile_pic_url = response_obj.data.url;
						attachment_id = response_obj.data.attachment_id;
						upload_files = response_obj.data.upload_files;
						$this
							.closest(".button-group")
							.find("#profile_pic_url")
							.val(upload_files);
						$this
							.closest(".user-registration-profile-header")
							.find(".profile-preview")
							.attr("src", profile_pic_url);
					}

					// Shows the remove button and hides the upload and take snapshot buttons after successfull picture upload
					$this
						.closest(".button-group")
						.find(".profile-pic-remove")
						.data("attachment-id", response_obj.data.attachment_id);
					$this
						.closest(".button-group")
						.find(".profile-pic-remove")
						.prop("style", false);
					$this
						.closest(".button-group")
						.find(".user_registration_profile_picture_upload")
						.attr("style", "display:none");

					// Finds and removes any prevaling errors and appends new errors occured during picture upload
					$this
						.closest(".user-registration-profile-header")
						.find(".user-registration-profile-picture-error")
						.remove();
					$this
						.closest(".button-group")
						.after(
							'<span class="user-registration-profile-picture-error">' +
								message +
								"</span>"
						);
					upload_node.text(upload_node_value);
				}
			});
		},
		init_event: function () {
			// Trigger profile picture through ajax submission.
			$(".user_registration_profile_picture_upload").on(
				"click",
				function () {
					$(this)
						.closest(".button-group")
						.find('input[type="file"]')
						.trigger("click");
				}
			);

			// Start uploading process once the picture is uploaded.
			$(document).on(
				"change",
				'.button-group input[type="file"]',
				function () {
					user_registration_profile_picture_upload.profile_picture_upload(
						$('.button-group input[type="file"]')
					);
				}
			);
		},
		remove_avatar: function ($node) {
			var attachment_id = $node.data("attachment-id");
			if (
				$node.closest("form").find(".ur_removed_profile_pic").length <=
				0
			) {
				var ur_removed_profile_pic = document.createElement("input");
				ur_removed_profile_pic.setAttribute("type", "hidden");
				ur_removed_profile_pic.setAttribute(
					"class",
					"ur_removed_profile_pic"
				);
				ur_removed_profile_pic.setAttribute(
					"name",
					"ur_removed_profile_pic"
				);
				ur_removed_profile_pic.setAttribute("value", "");
				$node.closest("form").append(ur_removed_profile_pic);
			}

			var el_value = $node
				.closest("form")
				.find(".ur_removed_profile_pic")
				.val();
			var ur_removed_pic = new Set(
				!!el_value ? JSON.parse(el_value) : []
			);
			ur_removed_pic.add(attachment_id);
			$node
				.closest("form")
				.find(".ur_removed_profile_pic")
				.val(JSON.stringify(Array.from(ur_removed_pic)));

			var input_file = $node
				.closest("form")
				.find('input[name="profile-pic"]');
			input_hidden = $node
				.closest("form")
				.find('input[name="profile-pic-url"]');
			profile_default_input_hidden = $node
				.closest("form")
				.find('input[name="profile-default-image"]');
			preview = $node.closest("form").find("img.profile-preview");

			input_hidden.val("");
			preview.attr("src", profile_default_input_hidden.val());
			$node.hide();
			$node
				.closest(".button-group")
				.find(".user_registration_profile_picture_upload")
				.show();
			$node
				.closest(".user-registration-profile-header")
				.find(".user-registration-profile-picture-error")
				.remove();
		},
		/**
		 * Displays Logout popup.
		 */
		handle_user_logout: function () {
			$(document).on(
				"click",
				".ur-logout, .urcma-user-logout",
				function (e) {
					e.preventDefault();
					e.stopPropagation();
					var $this = $(this);

					swal.fire({
						title: $this.text().trim() + "?",
						html: user_registration_params.logout_popup_text,
						confirmButtonText: $this.text(),
						confirmButtonColor: "#F25656",
						showConfirmButton: true,
						showCancelButton: true,
						cancelButtonText: "Cancel",
						cancelButtonColor: "#FFFFFF",
						customClass: {
							container:
								"user-registration-swal2-container user-registration-logout-swal2-container",
							title: "swal2-title-border"
						},
						focusConfirm: false,
						showLoaderOnConfirm: true
					}).then(function (result) {
						if (result.isConfirmed) {
							window.location.href = $this.attr("href");
						}
					});
				}
			);
		}
	};

	// Handle profile picture remove event.
	$(".profile-pic-remove, .uraf-profile-picture-remove").on(
		"click",
		function (e) {
			e.preventDefault();
			user_registration_profile_picture_upload.remove_avatar($(this));
		}
	);

	$(document).on(
		"user_registration_frontend_before_edit_profile_submit",
		function (e, data, form) {
			var files = $(".ur_removed_profile_pic");
			$.each(files, function () {
				data["ur_removed_profile_pic"] = $(this).val();
			});
		}
	);

	/**
	 * Dismiss  a pending change of user email.
	 */
	$(document).on(
		"click",
		"input#user_registration_user_email + div.email-updated.inline a",
		function (e) {
			e.preventDefault();

			var $this = $(this);
			var url = new URL(e.target.href);
			var cancel_email_change = url.searchParams.get(
				"cancel_email_change"
			);
			var nonce = url.searchParams.get("_wpnonce");
			var ajaxUrl = user_registration_params.ajax_url;

			$.ajax({
				type: "POST",
				url: ajaxUrl,
				data: {
					action: "user_registration_cancel_email_change",
					cancel_email_change: cancel_email_change,
					_wpnonce: nonce
				},
				success: function (response) {
					if (response.success) {
						$this.parents("div.email-updated.inline").remove();
					}
				}
			});
		}
	);

	// Check if the form is edit-profile form and check if ajax submission on edit profile is enabled.
	if (
		!$(".ur-frontend-form")
			.find(".user-registration-profile-header")
			.find(".uraf-profile-picture-upload").length
	) {
		user_registration_profile_picture_upload.init();
		$(".edit-profile").on("submit", function (evt) {
			var $el = $(".ur-smart-phone-field");

			if ("true" === $el.attr("aria-invalid")) {
				evt.preventDefault();
				var wrapper = $el.closest("p.form-row");
				wrapper.find("#" + $el.data("id") + "-error").remove();
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
				wrapper.find("#" + $el.data("id")).attr("aria-invalid", true);
				return true;
			}

			var profile_picture_error = $(this)
				.find(".user-registration-profile-picture-error")
				.find(".user-registration-error").length;
			if (1 === profile_picture_error) {
				evt.preventDefault();
				return true;
			}
		});
	}

	// Fix - Date field is required error even when the "value" attribute is present in Chrome.
	$("input.flatpickr-input").each(function () {
		$(this).val($(this).attr("value"));
	});
});
