/* global  user_registration_params  */
jQuery(function ( $ ) {

	$('.profile-pic-remove').on('click', function (e) {
		e.preventDefault();
		var input_file = $( this ).closest('form').find( 'input[name="profile-pic"]' );
			input_hidden = $( this ).closest('form').find( 'input[name="profile-pic-url"]' );
			profile_default_input_hidden = $( this ).closest('form').find( 'input[name="profile-default-image"]' );
			preview      = $( this ).closest('form').find( 'img.profile-preview' );

		input_hidden.val('');
		preview.attr('src', profile_default_input_hidden.val());
		$(this).hide();
		// Check if ajax submission on edit profile is enabled.
		if( 'yes' === user_registration_params.ajax_submission_on_edit_profile ) {
			$(this).closest('.button-group').find('.user_registration_profile_picture_upload').show();
			$(this).closest('.user-registration-profile-header').find('.user-registration-profile-picture-error').remove();
		} else {
			input_file.val('').show();
		}
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

		// Check if ajax submission on edit profile is enabled.
	if( 'yes' === user_registration_params.ajax_submission_on_edit_profile ) {

		// Trigger profile picture through ajax submission.
		$('.user_registration_profile_picture_upload').on('click', function () {
			$(this).closest('.button-group').find('input[type="file"]').trigger('click');
		});

		$(document).on('change', '.button-group input[type="file"]', function () {
			var url =user_registration_params.ajax_url + '?action=user_registration_profile_pic_upload&security=' + user_registration_params.user_registration_profile_picture_upload_nonce;
			var formData = new FormData();
			var $this = $(this);
			formData.append('file', $this[0].files[0]);

			var upload_node = $(this).closest('.button-group').find('.user_registration_profile_picture_upload');
			var upload_node_value = upload_node.text();

			var file_data = $.ajax({
				url: url,
				data: formData,
				type: 'POST',
				processData: false,
				contentType: false,
				// tell jQuery not to set contentType
				beforeSend: function () {
					upload_node.text( user_registration_params.user_registration_profile_picture_uploading );
				},
				complete: function (ajax_response) {
					var message = '';
					var profile_pic_url = '';

					// $node.parent().parent().parent().find('.user-registration-error').remove();
					$this.val('');

					var response_obj = $.parseJSON( ajax_response.responseText );

					message = response_obj.data.message;

					if ( !response_obj.success ) {
						message = '<p class="uraf-profile-picture-error user-registration-error">' + message + '</p>';
					}

					if ( response_obj.success ) {
						message = '';

						// Gets the profile picture url and displays the picture on frontend
						profile_pic_url = response_obj.data.url;
						$this.closest('.button-group').find('#profile_pic_url').val( profile_pic_url );
						$this.closest('.user-registration-profile-header').find('.profile-preview').attr( 'src', profile_pic_url );
					}

					// Shows the remove button and hides the upload and take snapshot buttons after successfull picture upload
					$this.closest('.button-group').find('.profile-pic-remove').removeAttr('style');
					$this.closest('.button-group').find('.user_registration_profile_picture_upload').attr('style', 'display:none');

					// Finds and removes any prevaling errors and appends new errors occured during picture upload
					$this.closest('.user-registration-profile-header').find('.user-registration-profile-picture-error').remove();
					$this.closest('.button-group').after('<span class="user-registration-profile-picture-error">' + message + '</span>');
					upload_node.text( upload_node_value );
				}
			});
		});
	}
});
