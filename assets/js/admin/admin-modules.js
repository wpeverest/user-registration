/* global user_registration_module_params */
(function ( $ ) {

	var modules = {

		install: function ( $install_node ) {

			if ( $install_node.hasClass('button-disabled') ) {

				return;
			}

			var $list_node = $install_node.closest('li.ur-module-list');

			var data = {

				security: $list_node.find('.ur-module-content').attr('data-nonce'),

				module_name: $list_node.attr('data-module-name'),

				module_file: $list_node.attr('data-module-file'),

				action: 'user_registration_module_ajax_module_install'
			};

			var message_class = 'notice-error';
			var notice = $('<div class="notice ' + message_class + '"></div>');
			$list_node.find('.spinner').remove();
			$list_node.find('.notice').remove();
			$install_node.after('<span class="spinner is-active"></span>');
			$.ajax({
				url: user_registration_module_params.ajax_url,
				data: data,
				type: 'POST',
				beforeSend: function () {
				},
				complete: function ( ajax_response ) {

					var responseJSON = ajax_response.responseJSON;

					notice.removeClass(message_class);

					var message;

					if ( 'undefined' === typeof responseJSON ) {
						message = user_registration_module_params.error_could_not_install;

					} else if ( 'undefined' === typeof responseJSON.data ) {
						message = user_registration_module_params.error_could_not_install;

					} else if ( 'undefined' === typeof responseJSON.data.message ) {

						message = user_registration_module_params.error_could_not_install;
					} else if ( true === responseJSON.success ) {

						message = responseJSON.data.message;

						message_class = 'notice-success';

						$install_node.text($install_node.attr('data-already-installed'));

						$install_node.addClass('button-disabled');

					} else if ( false === responseJSON.success ) {

						message = responseJSON.data.message;

						message_class = 'notice-error';
					}
					notice.addClass(message_class);
					notice.append('<p>' + message + '</p>');
					$list_node.find('.spinner').remove();
					$install_node.after(notice);


					try {

					} catch ( e ) {

					}

				}
			});

		}

	};


	$(document).ready(function () {

		$('body').on('click', '.ur-admin-module-block button.ur-module-install', function () {

			modules.install($(this));
		})
	});


})(jQuery);
