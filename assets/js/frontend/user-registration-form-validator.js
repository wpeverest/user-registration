/* global  user_registration_params */
(function ($) {
	var user_registration_form_selector;

	user_registration_form_selector = $(".ur-frontend-form form");

	if (user_registration_form_selector.hasClass("login")) {
		return;
	}

	var field_selector = "";

	if (user_registration_form_selector.hasClass("edit-profile")) {
		field_selector = "user_registration_";
	}

	var user_registration_form_validator = {
		$user_registration: user_registration_form_selector,
		init: function () {
			this.add_validation_methods();
			this.load_validation();
			this.init_inputMask();
			this.init_tiptip();

			// Inline validation
			this.$user_registration.on(
				"input validate change",
				".input-text, select, input:checkbox input:radio",
				this.validate_field
			);
		},
		init_inputMask: function () {
			if (typeof $.fn.inputmask !== "undefined") {
				$(".ur-masked-input").inputmask();
			}
		},
		init_tiptip: function () {
			if (typeof tipTip !== "undefined") {
				var tiptip_args = {
					attribute: "title",
					fadeIn: 50,
					fadeOut: 50,
					delay: 200,
				};
				$(".user-registration-help-tip").tipTip(tiptip_args);
			}
		},
		/**
		 * Add custom validation Methods.
		 *
		 * @since 1.9.4
		 */
		add_validation_methods: function () {
			// Validate email addresses.
			$.validator.methods.email = function (value, element) {
				/* https://stackoverflow.com/questions/2855865/jquery-validate-e-mail-address-regex */
				var pattern = new RegExp(
					/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i
				);
				return this.optional(element) || pattern.test(value);
			};

			/**
			 * Validation for username length.
			 *
			 * @since 1.9.4
			 */
			$.validator.addMethod(
				"lengthValidator",
				function (value, element, param) {
					return value.length <= param;
				},
				$.validator.format("Please enter less than {0} characters.")
			);

			/**
			 * Validation for username validation for special character.
			 *
			 * @since 1.9.7
			 */
			$.validator.addMethod(
				"SpecialCharacterValidator",
				function (value, element) {
					let reg = new RegExp(/([%\$#\*\@]+)/);
					return this.optional(element) || !reg.test(value);

				},
				user_registration_params.message_usename_character_fields

			);

			/**
			 * Validate checkbox choice limit.
			 *
			 * @since 1.9.4
			 */
			$.validator.addMethod(
				"checkLimit",
				function (value, element, param) {
					var choiceLimit = parseInt(param || 0, 10),
						$checked = "";

					if ($(element).closest(".field-checkbox").length) {
						var ul = $(element).closest("ul");
						$checked = ul.find('input[type="checkbox"]:checked');
					} else if (
						$(element).closest(".field-multi_select2").length
					) {
						$checked = $(element).val();
					}

					if (0 === choiceLimit) {
						return true;
					}

					return $checked.length <= choiceLimit;
				},

				$.validator.format("Please select no more than {0} options.")
			);
		},
		load_validation: function () {
			if (typeof $.fn.validate === "undefined") {
				return false;
			}
			var $this_node = this;

			$this_node.$user_registration.each(function () {
				var $this = $(this);
				var validator_params = $this_node.custom_validation($this);
				$this_node.custom_validation_messages();

				$this.validate({
					errorClass: "user-registration-error",
					validClass: "user-registration-valid",
					rules: validator_params.rules,
					messages: validator_params.messages,
					focusInvalid: false,
					invalidHandler: function (form, validator) {
						if (!validator.numberOfInvalids()) return;

						// Scroll to first error message on submit.
						$(window).scrollTop(
							$(validator.errorList[0].element).offset().top
						);
					},
					errorPlacement: function (error, element) {
						if (element.is("#password_2")) {
							element.parent().after(error);
						} else if (
							"radio" === element.attr("type") ||
							"checkbox" === element.attr("type") ||
							"password" === element.attr("type")
						) {
							element.parent().parent().parent().append(error);
						} else if (
							element.is("select") &&
							element
								.attr("class")
								.match(/date-month|date-day|date-year/)
						) {
							if (
								element
									.parent()
									.find(
										"label.user-registration-error:visible"
									).length === 0
							) {
								element
									.parent()
									.find("select:last")
									.after(error);
							}
						} else if (element.hasClass("ur-smart-phone-field")) {
							var wrapper = element.closest("p.form-row");
							wrapper
								.find("#" + element.data("id") + "-error")
								.remove();
							wrapper.append(error);
						} else if (
							"number" === element.attr("type") &&
							element.hasClass("ur-range-input")
						) {
							error.insertAfter(
								element
									.closest(".ur-range-row")
									.find(".ur-range-number")
							);
						} else {
							if (
								element.hasClass("urfu-file-input") ||
								element.closest(".field-multi_select2").length
							) {
								error.insertAfter(element.parent().parent());
							} else {
								error.insertAfter(element);
							}
						}
					},
					highlight: function (element, errorClass, validClass) {
						var $element = $(element),
							$parent = $element.closest(".form-row"),
							inputName = $element.attr("name");
					},
					unhighlight: function (element, errorClass, validClass) {
						var $element = $(element),
							$parent = $element.closest(".form-row"),
							inputName = $element.attr("name");

						if (
							$element.attr("type") === "radio" ||
							$element.attr("type") === "checkbox"
						) {
							$parent
								.find("input[name='" + inputName + "']")
								.addClass(validClass)
								.removeClass(errorClass);
						} else {
							$element
								.addClass(validClass)
								.removeClass(errorClass);
						}

						$parent.removeClass("user-registration-has-error");
					},
					submitHandler: function (form) {
						/**
						 * Return `true` for `Change Password` form and `Edit Profile` when ajax submission is off to allow submission
						 */
						if (
							$(form).hasClass("edit-password") ||
							($(form).hasClass("edit-profile") &&
								"no" ===
								user_registration_params.ajax_submission_on_edit_profile)
						) {
							return true;
						}

						return false;
					},
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
				confirmpassword:
					user_registration_params.message_confirm_password_fields,
			});

			var $this = $(this),
				$parent = $this.closest(".form-row"),
				validated = true,
				validate_required = $parent.is(".validate-required"),
				validate_email = $parent.is(".validate-email"),
				event_type = e.type;

			if ("input" === event_type) {
				$parent.removeClass(
					"user-registration-invalid user-registration-invalid-required-field user-registration-invalid-email user-registration-validated"
				);
			}

			if ("validate" === event_type || "change" === event_type) {
				if (validate_required) {
					if (
						"checkbox" === $this.attr("type") &&
						!$this.is(":checked")
					) {
						$parent
							.removeClass("user-registration-validated")
							.addClass(
								"user-registration-invalid user-registration-invalid-required-field"
							);
						validated = false;
					} else if ($this.val() === "") {
						$parent
							.removeClass("user-registration-validated")
							.addClass(
								"user-registration-invalid user-registration-invalid-required-field"
							);
						validated = false;
					}
				}

				if (validate_email) {
					if ($this.val()) {
						/* https://stackoverflow.com/questions/2855865/jquery-validate-e-mail-address-regex */
						var pattern = new RegExp(
							/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i
						);

						if (!pattern.test($this.val())) {
							$parent
								.removeClass("user-registration-validated")
								.addClass(
									"user-registration-invalid user-registration-invalid-email"
								);
							validated = false;
						}
					}
				}

				if (validated) {
					$parent
						.removeClass(
							"user-registration-invalid user-registration-invalid-required-field user-registration-invalid-email"
						)
						.addClass("user-registration-validated");
				}
			}
		},
		/**
		 * Add custom validation messages.
		 */
		custom_validation: function (this_node) {
			var rules = {},
				messages = {};

			if (this_node.find("#user_confirm_email").length) {
				/**
				 * For real time email matching
				 */
				var form_id = this_node.closest(".ur-frontend-form").attr("id");

				rules.user_confirm_email = {
					equalTo: "#" + form_id + " #user_email",
				};
				messages.user_confirm_email =
					user_registration_params.message_confirm_email_fields;
			}

			if (this_node.hasClass("edit-password")) {
				/**
				 * Password matching for `Change Password` form
				 */
				rules.password_2 = {
					equalTo: "#password_1",
				};
				messages.password_2 =
					user_registration_params.message_confirm_password_fields;
			} else if (
				this_node.hasClass("register") &&
				this_node.find("#user_confirm_password").length
			) {
				/**
				 * Password matching for registration form
				 */
				var form_id = this_node.closest(".ur-frontend-form").attr("id");

				rules.user_confirm_password = {
					equalTo: "#" + form_id + " #user_pass",
				};
				messages.user_confirm_password =
					user_registration_params.message_confirm_password_fields;
			}

			/**
			 * Real time username length validation
			 */
			var user_login_div = this_node.find("#user_login");

			if (user_login_div.length) {
				rules.user_login = {
					lengthValidator: user_login_div.data("username-length"),
				};
			}

			var user_login_div_ = this_node.find("#user_login");
			
			if (user_login_div_ && user_login_div_.data("username-character") == "yes") {
				rules.user_login = {
					lengthValidator: user_login_div_.data("username-length"),
					SpecialCharacterValidator: user_login_div_.data("username-character"),
				};
			}

			/**
			 * Real time choice limit validation
			 */
			var checkbox_div = this_node.find(".field-checkbox"),
				multiselect2_div = this_node.find(".field-multi_select2");

			if (checkbox_div.length) {
				checkbox_div.each(function () {
					rules[field_selector + $(this).data("field-id") + "[]"] = {
						checkLimit: $(this).find("ul").data("choice-limit")
							? $(this).find("ul").data("choice-limit")
							: 0,
					};
				});
			}

			if (multiselect2_div.length) {
				multiselect2_div.each(function () {
					rules[field_selector + $(this).data("field-id") + "[]"] = {
						checkLimit: $(this).find("select").data("choice-limit")
							? $(this).find("select").data("choice-limit")
							: 0,
					};
				});
			}

			return { rules: rules, messages: messages };
		},
		/**
		 * Override default validaton messages and add custom validation messsages.
		 */
		custom_validation_messages: function () {
			// Override default jquery validator messages with our plugin's validation messages.
			$.validator.messages.required =
				user_registration_params.message_required_fields;
			$.validator.messages.url =
				user_registration_params.message_url_fields;
			$.validator.messages.email =
				user_registration_params.message_email_fields;
			$.validator.messages.number =
				user_registration_params.message_number_fields;
			$.validator.messages.confirmpassword =
				user_registration_params.message_confirm_password_fields;
			$.validator.messages.max = function (params, element) {
				return user_registration_params.message_confirm_number_field_max.replace(
					"%qty%",
					element.max
				);
			};
			$.validator.messages.min = function (params, element) {
				return user_registration_params.message_confirm_number_field_min.replace(
					"%qty%",
					element.min
				);
			};
			$.validator.messages.step = function (params, element) {
				return user_registration_params.message_confirm_number_field_step.replace(
					"%qty%",
					element.step
				);
			};
		},
	};

	$(window).on("load", function () {
		user_registration_form_validator.init();
	});
})(jQuery);
