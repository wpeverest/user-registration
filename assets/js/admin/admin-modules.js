/* global user_registration_module_params */
(function ( $ ) {

	var modules = {

		ajax_request: function ( data, method, url, before_callback, complete_callback ) {

			var before_callback_param = '';

			var before_callback_extra_args = '';

			if ( 'undefined' !== typeof before_callback ) {

				before_callback_param = 'undefined' !== typeof  before_callback.method ? before_callback.method : '';
				before_callback_extra_args = 'undefined' !== typeof  before_callback.args ? before_callback.args : '';
			}
			var complete_callback_param = '';
			var complete_callback_extra_args = '';

			if ( 'undefined' !== typeof complete_callback ) {

				complete_callback_param = 'undefined' !== typeof  complete_callback.method ? complete_callback.method : '';
				complete_callback_extra_args = 'undefined' !== typeof  complete_callback.args ? complete_callback.args : '';
			}

			var data_param = 'undefined' !== typeof  data ? data : {};

			var method_param = 'undefined' !== typeof  method ? method : 'POST';

			var url_param = 'undefined' !== typeof  url ? url : '';

			if ( '' === url_param ) {

				throw 'URL param empty.';
			}
			$.ajax({

				url: url_param,

				data: data,

				type: method,

				beforeSend: function ( $arg ) {

					if ( typeof modules[ before_callback_param ] === 'function' ) {

						modules[ before_callback_param ]($arg, before_callback_param);
					}

				},
				complete: function ( ajax_response ) {

					if ( typeof modules[ complete_callback_param ] === 'function' ) {

						modules[ complete_callback_param ](ajax_response, complete_callback_extra_args);

					}
				}
			});

		},
		uninstall: function ( $uninstall_node ) {

			var $list_node = $uninstall_node.closest('li.ur-module-list');

			var data = {

				security: $list_node.find('.ur-module-content').attr('data-nonce'),

				module_name: $list_node.attr('data-module-name'),

				module_file: $list_node.attr('data-module-file'),

				action: 'user_registration_module_ajax_module_uninstall'
			};

			var message_class = 'notice-error';
			var notice = $('<div class="notice ' + message_class + '"></div>');
			$list_node.find('.spinner').remove();
			$list_node.find('.notice').remove();
			$uninstall_node.after('<span class="spinner is-active"></span>');

			var after_callback = { method: '', args: '' };
			after_callback.method = 'after_uninstall';
			after_callback.args = { notice: notice, uninstall_node: $uninstall_node, list_node: $list_node };
			this.ajax_request(data, 'POST', user_registration_module_params.ajax_url, '', after_callback);


		},
		install: function ( $install_node ) {

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

			var after_callback = { method: '', args: '' };
			after_callback.method = 'after_install';
			after_callback.args = { notice: notice, install_node: $install_node, list_node: $list_node };
			this.ajax_request(data, 'POST', user_registration_module_params.ajax_url, '', after_callback);


		},
		after_install: function ( ajax_response, extra_params ) {

			var notice = 'undefined' !== extra_params.notice ? extra_params.notice : '';

			var $install_node = 'undefined' !== extra_params.install_node ? extra_params.install_node : '';

			var $list_node = 'undefined' !== extra_params.list_node ? extra_params.list_node : '';

			var responseJSON = ajax_response.responseJSON;

			var message_class = 'notice-error';

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

				$install_node.removeClass('ur-module-install').removeClass('button-primary').addClass('ur-module-uninstall');

				$install_node.text(user_registration_module_params.uninstall);


			} else if ( false === responseJSON.success ) {

				message = responseJSON.data.message;

				message_class = 'notice-error';
			}
			notice.addClass(message_class);

			notice.append('<p>' + message + '</p>');

			$list_node.find('.spinner').remove();

			$install_node.after(notice);

		}, after_uninstall: function ( ajax_response, extra_params ) {

			var notice = 'undefined' !== extra_params.notice ? extra_params.notice : '';

			var $uninstall_node = 'undefined' !== extra_params.uninstall_node ? extra_params.uninstall_node : '';

			var $list_node = 'undefined' !== extra_params.list_node ? extra_params.list_node : '';

			var responseJSON = ajax_response.responseJSON;

			var message_class = 'notice-error';

			notice.removeClass(message_class);

			var message;

			if ( 'undefined' === typeof responseJSON ) {
				message = user_registration_module_params.error_could_not_uninstall;

			} else if ( 'undefined' === typeof responseJSON.data ) {
				message = user_registration_module_params.error_could_not_uninstall;

			} else if ( 'undefined' === typeof responseJSON.data.message ) {

				message = user_registration_module_params.error_could_not_uninstall;

			} else if ( true === responseJSON.success ) {

				message = responseJSON.data.message;

				message_class = 'notice-success';

				$uninstall_node.removeClass('ur-module-uninstall').addClass('button-primary ur-module-install');

				$uninstall_node.text(user_registration_module_params.install);


			} else if ( false === responseJSON.success ) {

				message = responseJSON.data.message;

				message_class = 'notice-error';
			}
			notice.addClass(message_class);

			notice.append('<p>' + message + '</p>');

			$list_node.find('.spinner').remove();

			$uninstall_node.after(notice);

		}


	};


	$(document).ready(function () {

		$('body').on('click', '.ur-admin-module-block button.ur-module-install', function () {

			modules.install($(this));
		})
		$('body').on('click', '.ur-admin-module-block button.ur-module-uninstall', function () {

			modules.uninstall($(this));
		})
	});


})(jQuery);
