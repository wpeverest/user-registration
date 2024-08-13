"use strict";

(function ($) {
  var UR_SMS_VERIFICATION = {

    init: function () {
      UR_SMS_VERIFICATION.init_triggers();
      UR_SMS_VERIFICATION.init_render();
    },

    init_triggers: function () {
      $("#user-registration-sms-verification-otp-submit-btn").on("click", function (e) {
		  e.preventDefault();
		  e.stopPropagation();
		  UR_SMS_VERIFICATION.submit();
		});

		$("#user-registration-sms-verification-otp-field").on("keypress", function (e) {
			if (e.key === "Enter") {
          e.preventDefault();
          UR_SMS_VERIFICATION.submit();
        }
      });

      $("#user-registration-sms-verification-otp-resend-btn").on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        UR_SMS_VERIFICATION.resend_otp();
      });
    },

    /**
     * Render initial values and message.
     */
    init_render: function () {
	UR_SMS_VERIFICATION.show_message(
		user_registration_sms_verification_parameters.otp_sent_message,
		"user-registration-message"
	);
    },

    /**
     * Show message to user in message box.
     *
     * @param {string} msg Message to display
     */
    show_message: function (msg = "", elementClass = "user-registration-info") {
      var el = $("<li></li>");

      el.html(msg);

      $("#user-registration-sms-verification-message-container")
        .removeClass()
        .addClass(elementClass)
        .empty()
        .append(el);
    },

    /**
     * Submit the Entered OTP.
     *
     * @returns void
     */
    submit: function () {
      var spinner = $("#user-registration-sms-verification-spinner").addClass(
        "ur-front-spinner"
      );

      var otp_code = $("#user-registration-sms-verification-otp-field").val();
      var redirect_on_login = $(
        "#user-registration-sms-verification-validate-otp-redirect"
      ).val();

      if (!otp_code.length) {
        UR_SMS_VERIFICATION.show_message(
          user_registration_sms_verification_parameters.otp_empty_message,
          "user-registration-error"
        );
        spinner.removeClass("ur-front-spinner");
        return;
      }

      var submit_btn = $("#user-registration-sms-verification-otp-submit-btn").prop(
        "disabled",
        true
      );

      var resend_btn = $("#user-registration-sms-verification-otp-resend-btn").attr(
        "disabled",
        true
      );

      var params = new URLSearchParams(window.location.search);
      $.ajax({
        type: "POST",
        url: user_registration_sms_verification_parameters.ajax_url,
        data: {
          action: user_registration_sms_verification_parameters.sms_otp_submit_action,
          security: user_registration_sms_verification_parameters.sms_otp_submit_nonce,
          otp_code: otp_code,
          redirect_on_login: redirect_on_login,
          user_id: user_registration_sms_verification_parameters.user_id,
          remember_me: params.get("remember_me"),
        },
        success: function (response) {
          if (true == response.success ) {
            if (response.data.redirect) {
              UR_SMS_VERIFICATION.show_message(
                response.data.message,
                "user-registration-message"
              );
              window.location.replace(response.data.redirect);
            }
          } else {
            UR_SMS_VERIFICATION.show_message(
              response.data.message,
              "user-registration-error"
            );
            submit_btn.prop("disabled", false);
          }
          spinner.removeClass("ur-front-spinner");
          UR_SMS_VERIFICATION.render_values();
        },
        dataType: "json",
      });
    },

    /**
     * Send Resend OTP request to server.
     *
     * @returns void.
     */
    resend_otp: function () {
      var resend_btn = $("#user-registration-sms-verification-otp-resend-btn").prop(
        "disabled",
        true
      );

      $.ajax({
        type: "POST",
        url: user_registration_sms_verification_parameters.ajax_url,
        action: user_registration_sms_verification_parameters.sms_otp_resend_action,
        data: {
          action: user_registration_sms_verification_parameters.sms_otp_resend_action,
          security: user_registration_sms_verification_parameters.sms_otp_resend_nonce,
          user_id: user_registration_sms_verification_parameters.user_id,
        },
        success: function (response) {
          if (response.success) {
            UR_SMS_VERIFICATION.show_message(
              response.data.message,
              "user-registration-message"
            );
          } else {
            UR_SMS_VERIFICATION.show_message(
              response.data.message,
              "user-registration-error"
            );
          }
          resend_btn.prop("disabled", false);
        },
        dataType: "json",
      });
    },

    /**
     * Prevent user from logging in.
     */
    hold_user_login: function () {
      $("#user-registration-sms-verification-otp-field").prop("disabled", true);
      $("#user-registration-sms-verification-otp-resend-btn").prop("disabled", true);
      $("#user-registration-sms-verification-otp-submit-btn").prop("disabled", true);

      UR_SMS_VERIFICATION.redirect_to_login_page();
    },

    /**
     * Redirect user to login page.
     *
     * @param {int} start_time Timeout beore redirecting to login page.
     */
    redirect_to_login_page: function (start_time = 0) {
      if (user_registration_sms_verification_parameters.login_page_url) {
        setTimeout(function () {
          UR_SMS_VERIFICATION.show_message(
            "Redirecting to login page . . .",
            "user-registration-info"
          );
          window.location.replace(
            user_registration_sms_verification_parameters.login_page_url
          );
        }, start_time * 1000);
      }
    },
  };

  $(document).ready(function () {
    UR_SMS_VERIFICATION.init();
  });
})(jQuery);
