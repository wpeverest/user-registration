jQuery(
	(function ($) {
		"use strict";

		var URLiveUser = {
			init: function () {
				typeof wp.heartbeat !== "undefined" &&
					wp.heartbeat.interval("fast");
				this.sendDataToHeartbeat();
				this.handleHeartbeatResponse();
			},

			sendDataToHeartbeat: function () {
				$(document).on("heartbeat-send", function (event, data) {
					data.user_registration_new_user_notice = true;
				});
			},

			handleHeartbeatResponse: function () {
				$(document).on("heartbeat-tick", function (event, data) {
					wp.heartbeat.interval("fast");
					if(data["wp-auth-check"] === true || (data["rest_nonce"] && "true" === data["nonces_expired"])) {
						var wpAuthCheckWrap = $(document).find('#wp-auth-check-wrap');
						wpAuthCheckWrap.addClass( 'hidden' ).css( 'display', '' );
						$( '#wp-auth-check-frame' ).remove();
						$( 'body' ).removeClass( 'modal-open' );
					}

					var $user_menu = $("#menu-users .wp-menu-name"),
						$user_list = $("body.users-php .wp-list-table.users"),
						columnsCount = $user_list.find(
							"thead tr:first-child td, thead tr:first-child th"
						).length;

					if (
						typeof data.user_registration_new_user_count ===
							"undefined" ||
						!$user_list
					) {
						return;
					}

					if (data.user_registration_new_user_count > 0) {
						if (!$user_menu.find(".user-registration").length) {
							$user_menu.append(
								'<span class="user-registration awaiting-mod"><span class="user-count">' +
									data.user_registration_new_user_count +
									"</span></span>"
							);
						}

						if (
							!$user_list.find("tr.ur-user-notification").length
						) {
							$user_list
								.find("thead")
								.append(
									'<tr class="ur-user-notification"><td colspan="' +
										columnsCount +
										'"><a href="javascript:void()" onClick="window.location.reload(true);"></a></td></tr>'
								);
						}

						$user_menu
							.find(".user-registration .user-count")
							.text(data.user_registration_new_user_count);

						$user_list
							.find(".ur-user-notification a")
							.html(data.user_registration_new_user_message)
							.slideDown({
								duration: 500,
								start: function () {
									$(this).css("display", "block");
								},
							});
					} else {
						$user_menu.find(".user-registration").remove();
						$user_list.find("tr.ur-user-notification").remove();
					}

					typeof wp.heartbeat !== "undefined" &&
						wp.heartbeat.interval("standard");
				});
			},
		};
		$(document).ready(function () {
			URLiveUser.init();
		});
	})(jQuery)
);
