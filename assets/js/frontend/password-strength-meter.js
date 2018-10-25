/* global wp, ur_password_strength_meter_params */
jQuery(function ( $ ) {
	var pwsL10n = ur_password_strength_meter_params.pwsL10n;
	/**
	 * Password Strength Meter class.
	 */
	var ur_password_strength_meter = {
		/**
		 * Initialize strength meter actions.
		 */
		init: function () {
			var $this = this;
			$(document.body).on('keyup change', 'input[name="user_pass"], input[name="password_2"]', function () {

				var enable_strength_password = $(this).closest('form').attr('data-enable-strength-password');
				if ( 'no' === enable_strength_password ) {

					return;
				}
				$this.strengthMeter($(this));

			});
		},
		/**
		 * Strength Meter.
		 */
		strengthMeter: function ( self ) {
			var wrapper = self.closest('form'),
				// submit     = $( 'button[type="submit"]', wrapper ),
				field = $(self, wrapper);
			// strength   = 1;
			// fieldValue = field.val();
			ur_password_strength_meter.includeMeter(wrapper, field);
			ur_password_strength_meter.checkPasswordStrength(wrapper, field);
			// if ( fieldValue.length > 0 && strength < ur_password_strength_meter.min_password_strength ) {
			// 	submit.attr( 'disabled', 'disabled' ).addClass( 'disabled' );
			// } else {
			// 	submit.removeAttr( 'disabled', 'disabled' ).removeClass( 'disabled' );
			// }
		},
		/**
		 * Include meter HTML.
		 *
		 * @param {Object} wrapper
		 * @param {Object} field
		 */
		includeMeter: function ( wrapper, field ) {
			var meter = wrapper.find('.user-registration-password-strength');
			if ( '' === field.val() ) {
				meter.remove();
				$(document.body).trigger('ur-password-strength-removed');
			} else if ( 0 === meter.length ) {
				field.after('<div class="user-registration-password-strength" aria-live="polite" data-min-strength="' + ur_password_strength_meter_params.min_password_strength + '"></div>');
				$(document.body).trigger('ur-password-strength-added');
			}
		},
		/**
		 * Check password strength.
		 *
		 * @param {Object} field
		 *
		 * @return {Int}
		 */
		checkPasswordStrength: function ( wrapper, field ) {
			var meter = wrapper.find('.user-registration-password-strength');
			var hint = wrapper.find('.user-registration-password-hint');
			var hint_html = '<small class="user-registration-password-hint">' + ur_password_strength_meter_params.i18n_password_hint + '</small>';
			var strength = wp.passwordStrength.meter(field.val(), wp.passwordStrength.userInputBlacklist());
			var error = '';
			// Reset
			meter.removeClass('short bad good strong');
			hint.remove();
			// Error to append
			if ( strength < ur_password_strength_meter_params.min_password_strength ) {
				error = ' - ' + ur_password_strength_meter_params.i18n_password_error;
			}
			wrapper.find('.user-registration-password-strength').attr('data-current-strength', strength);

			switch ( strength ) {
				case 0:
					meter.addClass('short').html(pwsL10n.shortpw + error);
					meter.after(hint_html);
					break;
				case 1:
					meter.addClass('bad').html(pwsL10n.bad + error);
					meter.after(hint_html);
					break;
				case 2:
					meter.addClass('bad').html(pwsL10n.bad + error);
					meter.after(hint_html);
					break;
				case 3:
					meter.addClass('good').html(pwsL10n.good + error);
					break;
				case 4:
					meter.addClass('strong').html(pwsL10n.strong + error);
					break;
				case 5:
					meter.addClass('short').html(pwsL10n.mismatch);
					break;
			}
			return strength;
		}
	};
	ur_password_strength_meter.init();
});
