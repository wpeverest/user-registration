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
});
