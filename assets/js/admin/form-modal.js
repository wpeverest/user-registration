;(function($){
	$(function(){
		// Close modal
		var urModalClose = function() {
			if ( $('#ur-modal-select-form').length ) {
				$('#ur-modal-select-form').get(0).selectedIndex = 0;
			}
			$('#ur-modal-backdrop, #ur-modal-wrap').css('display','none');
			$( document.body ).removeClass( 'modal-open' );
		};
		// Open modal when media button is clicked
		$(document).on('click', '.ur-insert-form-button', function(event) {
			event.preventDefault();
			$('#ur-modal-backdrop, #ur-modal-wrap').css('display','block');
			$( document.body ).addClass( 'modal-open' );
		});
		// Close modal on close or cancel links
		$(document).on('click', '#ur-modal-close, #ur-modal-cancel a', function(event) {
			event.preventDefault();
			urModalClose();
		});
		// Insert shortcode into TinyMCE
		$(document).on('click', '#ur-modal-submit', function(event) {
			event.preventDefault();
			var shortcode;
			shortcode = '[user_registration_form id="' + $('#ur-modal-select-form').val() + '"';
			shortcode = shortcode+']';
			wp.media.editor.insert(shortcode);
			urModalClose();
		});

		//Insert Smart Tag into TinyMCE
		$(document).on('change', '#select-smart-tags', function(event) {
			event.preventDefault();
			var smart_tag;
			smart_tag = $(this).val();
			wp.media.editor.insert(smart_tag);
			urModalClose();
			$("#select-smart-tags").val($("#select-smart-tags option:first").val());
		});

		/**
		 * Check all options when Select All is checked or vice versa in nav menus.
		 */
		$('#ur-pro-popups-tab, #ur-endpoints-tab').on('change', function (e) {
			var select_all = $(e.target);

			var options = select_all
				.closest('#posttype-user-registration-modal, #posttype-user-registration-endpoints')
				.find('.menu-item-checkbox');

			var check_all = false;

			if (select_all.is(':checked')) {
				check_all = true;
			};

			options.each(function (i, option) {
				$(option).prop('checked', check_all);
			});
		});

		/**
		 * Check Select All checkbox when all options are selected and vice versa in nav menus.
		 */
		$('#posttype-user-registration-modal, #posttype-user-registration-endpoints')
			.find('.menu-item-checkbox')
			.on('change', function (e) {
				var $this = $(e.target);
				var all_checked = true;

				var options = $this.closest('ul').find('.menu-item-checkbox');
				options.each(function (_, option) {

					if (! $(option).is(':checked')) {
						all_checked = false;
					}

					if (!all_checked) {
						return false;
					}
				});

				var select_all = $this
					.closest('#posttype-user-registration-modal, #posttype-user-registration-endpoints')
					.find('.select-all');

				if (all_checked) {
					select_all.prop('checked', true);
				} else{
					select_all.prop('checked', false);
				}
			});
	});

}(jQuery));
