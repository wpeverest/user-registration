jQuery(function ( $ ) {
	$('.profile-pic-upload').on('click', function (e) {
		e.preventDefault();
		var preview      = $( this ).closest('form').find( 'img.profile-preview' ),
			image = wp.media( {multiple: false} ).open(),
			input_hidden = $( this ).closest('form').find( 'input[name="profile-pic-id"]' ),
			remove_button = $( this ).closest('form').find( 'button.profile-pic-remove' );

		image.on( 'select', function () {
			// This will return the selected image from the Media Uploader, the result is an object.
			var uploadedImage = image.state().get( 'selection' ).first(),
				previewImage  = uploadedImage.toJSON().sizes.full.url,
				imageID,
				imageUrl;

			imageUrl    = uploadedImage.toJSON().sizes.full.url;
			imageID     = uploadedImage.toJSON().id;

			if ( !_.isUndefined( uploadedImage.toJSON().sizes.thumbnail ) ) {
				previewImage = uploadedImage.toJSON().sizes.thumbnail.url;
			}else{
				previewImage = imageUrl;
			}

			// Show extra controls if the value has an image.
			if( '' !== previewImage ) {
				preview.attr('src', previewImage);
				input_hidden.val(imageID);
				remove_button.show();
			}
		} );
	});

	$('.profile-pic-remove').on('click', function (e) {
		e.preventDefault();
		var input_hidden = $( this ).closest('form').find( 'input[name="profile-pic-id"]' ),
			profile_default_input_hidden = $( this ).closest('form').find( 'input[name="profile-default-image"]' ),
			preview      = $( this ).closest('form').find( 'img.profile-preview' );
		input_hidden.val('');
		preview.attr('src', profile_default_input_hidden.val());
		$(this).hide();
	});
});
