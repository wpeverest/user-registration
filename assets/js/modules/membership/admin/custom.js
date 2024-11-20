(jQuery)(function ($) {
	/**
	 *  TODO//remove this after setting display for button none directly through css, using just for temp reasons
	 */
	$(document).ready(function () {
		$('.ur-membership-header').length > 0 ? $('#screen-meta-links').hide() : '';
		$('#toplevel_page_user-registration').find('ul li:eq(5)').hide();
	});

});
