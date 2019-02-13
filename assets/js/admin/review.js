jQuery( function( $ ) {

   	// Review notice.
    jQuery('body').on('click', '#user-registration-review-notice .notice-dismiss', function(e) {
        e.preventDefault();

        jQuery("#user-registration-review-notice").hide();

		var data = {
			action: 'user_registration_dismiss_review_notice',
			security: ur_review_params.review_nonce,
			dismissed: true,
		};

		$.post( ur_review_params.ajax_url, data, function( response ) {
			// Success. Do nothing. Silence is golden.
        });
    });
});
