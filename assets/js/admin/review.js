jQuery( function( $ ) {

   	// Review notice.
    jQuery('body').on('click', '#user-registration-review-notice .notice-dismiss', function(e) {
        e.preventDefault();
        jQuery("#user-registration-review-notice").hide();

        wp.ajax.post('user-registration-dismiss-review-notice', {
            dismissed: true
        });
    });
});
