/* global  user_registration_params  */
jQuery(function ( $ ) {

	$('.profile-pic-remove').on('click', function (e) {
		e.preventDefault();
		var input_file = $( this ).closest('form').find( 'input[name="profile-pic"]' )
		input_hidden = $( this ).closest('form').find( 'input[name="profile-pic-url"]' ),
			profile_default_input_hidden = $( this ).closest('form').find( 'input[name="profile-default-image"]' ),
			preview      = $( this ).closest('form').find( 'img.profile-preview' );

		input_hidden.val('');
		preview.attr('src', profile_default_input_hidden.val());
		$(this).hide();
		input_file.val('').show();
	});

	$('.edit-profile').submit(function (evt) {
		var $el = $( '.ur-smart-phone-field' );

		if( 'true' === $el.attr('aria-invalid')){
			evt.preventDefault();
			var wrapper = $el.closest('p.form-row');
			wrapper.find('#' + $el.data('id') + '-error').remove();
			var phone_error_msg_dom = '<label id="' + $el.data('id') + '-error' + '" class="user-registration-error" for="' + $el.data('id') + '">' + user_registration_params.message_validate_phone_number + '</label>';
			wrapper.append(phone_error_msg_dom);
			wrapper.find('#' + $el.data('id')).attr('aria-invalid', true);
			return true;
		}
	});



});
