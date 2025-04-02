/*global console, UR_Snackbar, Swal*/
(function ($, urmo_data) {
		var modal = $('#payment-detail-modal');
		if (UR_Snackbar) {
			var snackbar = new UR_Snackbar();
		}

		var handle_orders_utils = {
			/**
			 *
			 * @param $element
			 * @returns {boolean}
			 */
			append_spinner: function ($element) {
				if ($element && $element.append) {
					var spinner = '<span class="ur-spinner is-active"></span>';

					$element.append(spinner);
					return true;
				}
				return false;
			},
			/**
			 * Remove spinner elements from a element.
			 *
			 * @param {jQuery} $element
			 */
			remove_spinner: function ($element) {
				if ($element && $element.remove) {
					$element.find('.ur-spinner').remove();
					return true;
				}
				return false;
			},
			if_empty: function (value, _default) {
				if (null === value || undefined === value || '' === value) {
					return _default;
				}
				return value;
			},
			/**
			 * Enable/Disable save buttons i.e. 'Save' button and 'Save as Draft' button.
			 *
			 * @param {Boolean} disable Whether to disable or enable.
			 */
			toggleSaveButtons: function (disable) {
				disable = handle_orders_utils.if_empty(disable, true);
				$('.approve-payment').prop('disabled', !!disable);
			},
			handle_bulk_delete_action: function (form) {
				Swal.fire({
					title:
						'<img src="' +
						urmo_data.delete_icon +
						'" id="delete-user-icon">' +
						urmo_data.labels.i18n_prompt_title,
					html: '<p id="html_1">' +
						urmo_data.labels.i18n_prompt_bulk_subtitle +
						'</p>',
					showCancelButton: true,
					confirmButtonText: urmo_data.labels.i18n_prompt_delete,
					cancelButtonText: urmo_data.labels.i18n_prompt_cancel,
					allowOutsideClick: false
				}).then(function (result) {
					if (result.isConfirmed) {
						var selected_orders = form.find('tbody input[type=checkbox]:checked'),
							order_ids = [],
							user_ids = [];

						if (selected_orders.length < 1) {
							handle_orders_utils.show_failure_message(
								urmo_data.labels.i18n_prompt_no_order_selected
							);
							return;
						}
						//prepare orders data
						selected_orders.each(function () {
								if ($(this).val() !== '') {
									order_ids.push($(this).val());
								} else if ($(this).data('user-id') !== '') {
									user_ids.push($(this).data('user-id'));
								}
						});

						//send request
						handle_orders_utils.send_data(
							{
								action: 'user_registration_membership_delete_orders',
								order_ids: JSON.stringify(order_ids),
								user_ids: JSON.stringify(user_ids)
							},
							{
								success: function (response) {
									if (response.success) {
										$('.ur-member-save-btn').text('Save');
										handle_orders_utils.show_success_message(
											response.data.message
										);
										handle_orders_utils.remove_deleted_orders(selected_orders, true);
									} else {
										handle_orders_utils.show_failure_message(
											response.data.message
										);
									}
								},
								failure: function (xhr, statusText) {
									handle_orders_utils.show_failure_message(
										urmo_data.labels.network_error +
										'(' +
										statusText +
										')'
									);
								},
								complete: function () {
									window.location.reload();
								}
							}
						);
					}
				});
			},

			handle_single_delete_action: function ($this) {
				var order_id = $this.data('order-id');
				var user_id = $this.data('user-id');
				Swal.fire({
					title:
						'<img src="' +
						urmo_data.delete_icon +
						'" id="delete-user-icon">' +
						urmo_data.labels.i18n_prompt_title,
					html: '<p id="html_1">' +
						urmo_data.labels.i18n_prompt_single_subtitle +
						'</p>',
					showCancelButton: true,
					confirmButtonText: urmo_data.labels.i18n_prompt_delete,
					cancelButtonText: urmo_data.labels.i18n_prompt_cancel,
					allowOutsideClick: false
				}).then(function (result) {
					if (result.isConfirmed) {
						//send request
						handle_orders_utils.send_data(
							{
								action: 'user_registration_membership_delete_order',
								order_id: order_id,
								user_id: user_id
							},
							{
								success: function (response) {
									if (response.success) {
										handle_orders_utils.show_success_message(
											response.data.message
										);

										handle_orders_utils.remove_deleted_orders($this, false);
									} else {
										handle_orders_utils.show_failure_message(
											response.data.message
										);
									}
								},
								failure: function (xhr, statusText) {
									handle_orders_utils.show_failure_message(
										urmo_data.labels.network_error +
										'(' +
										statusText +
										')'
									);
								},
								complete: function () {
									window.location.reload();
								}
							}
						);
					}
				});
			},
			/**
			 * Send data to the backend API.
			 *
			 * @param {JSON} data Data to send.
			 * @param {JSON} callbacks Callbacks list.
			 */
			send_data: function (data, callbacks) {
				var success_callback =
						'function' === typeof callbacks.success ? callbacks.success : function () {
						},
					failure_callback =
						'function' === typeof callbacks.failure ? callbacks.failure : function () {
						},
					beforeSend_callback =
						'function' === typeof callbacks.beforeSend ? callbacks.beforeSend : function () {
						},
					complete_callback =
						'function' === typeof callbacks.complete ? callbacks.complete : function () {
						};

				// Inject default data.
				if (!data._wpnonce && urmo_data) {
					data._wpnonce = urmo_data._nonce;
				}
				$.ajax({
					type: 'post',
					dataType: 'json',
					url: urmo_data.ajax_url,
					data: data,
					beforeSend: beforeSend_callback,
					success: success_callback,
					error: failure_callback,
					complete: complete_callback
				});
			},
			/**
			 * Show success message using snackbar.
			 *
			 * @param {String} message Message to show.
			 */
			show_success_message: function (message) {
				if (snackbar) {
					snackbar.add({
						type: 'success',
						message: message,
						duration: 5
					});
					return true;
				}
				return false;
			},

			/**
			 * Show failure message using snackbar.
			 *
			 * @param {String} message Message to show.
			 */
			show_failure_message: function (message) {
				if (snackbar) {
					snackbar.add({
						type: 'failure',
						message: message,
						duration: 6
					});
					return true;
				}
				return false;
			},

			remove_deleted_orders: function (selected_orders, is_multiple) {
				if (is_multiple) {
					selected_orders.each(function (index) {
						if (index !== 0) {
							$(this).parents('tr').remove();
						}
					});
				} else {
					$(selected_orders).parents('tr').remove();
				}
			},

			open_modal: function () {
				this.clear_modal();
				modal.css({'display': 'flex'});
			},
			close_modal: function () {
				modal.css({'display': 'none'});
			},
			clear_modal: function () {
				modal.find('.modal-body').empty();
			}
		};

		$(document).ready(function () {

			$('#doaction-orders,#doaction-orders2').on('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var form = $('#ur-membership-payment-history-form'),
					selectedAction = form.find('select#bulk-action-selector-top option:selected').val();
				switch (selectedAction) {
					case 'delete' :
						handle_orders_utils.handle_bulk_delete_action(form);
						break;
					default:
						break;
				}

			});

			$('.single-delete-order').on('click', function () {
				handle_orders_utils.handle_single_delete_action($(this));
			});
			//show the order detail
			$(document).on('click', '.show-order-detail', function () {
				var $this = $(this),
					order_id = $this.data('order-id'),
					modal_body = modal.find('.modal-body'),
					user_id = $this.data('user-id');

				handle_orders_utils.open_modal();
				handle_orders_utils.append_spinner(modal_body);

				handle_orders_utils.send_data(
					{
						action: 'user_registration_membership_show_order_detail',
						order_id: order_id,
						user_id: user_id
					},
					{
						success: function (response) {
							if (response.success) {
								var template = JSON.parse(response.data);
								modal_body.html(template);
							} else {
								handle_orders_utils.show_failure_message(
									response.data.message
								);
							}
						},
						failure: function (xhr, statusText) {
							handle_orders_utils.show_failure_message(
								urmo_data.labels.network_error +
								'(' +
								statusText +
								')'
							);
						},
						complete: function () {

						}
					}
				);
			});
			//close modal if clicked outside the box
			$(window).click(function (event) {
				if ($(event.target).is(modal)) {
					handle_orders_utils.close_modal();
				}
			});
			//close modal on click
			$(document).on('click', '.close-button', function () {
				handle_orders_utils.close_modal();
			});
			// toggle advance search select for membership and form
			$(document).on('change', '#user-registration-pro-payment-type-filters', function () {
				var $this = $(this),
					selected_val = $this.find('option:selected').val(),
					module_box = $('.module-box');

				module_box.each(function (key, item) {
					$(item).hide();
					$(item).find('select option').removeAttr('selected');
				});
				$('#user-registration-pro-' + selected_val + '-filters-container').css({'display': 'flex'});

			});
			$(document).on('click', '.approve-payment', function () {
				var $this = $(this),
					order_id = $this.data('order-id');
				handle_orders_utils.append_spinner($this);
				handle_orders_utils.toggleSaveButtons(true);

				handle_orders_utils.send_data(
					{
						action: 'user_registration_membership_approve_payment',
						order_id: order_id
					},
					{
						success: function (response) {
							response = JSON.parse(response.data);
							if (response.status) {
								handle_orders_utils.show_success_message(
									response.message
								);
								// update the status in modal
								$this.siblings('span').removeClass('pending').addClass('completed').text(urmo_data.labels.i18n_payment_completed);
								//update the status in table
								$('#ur-order-' + order_id).removeClass('pending').addClass('completed').text(urmo_data.labels.i18n_payment_completed);
								$this.remove();
							} else {
								handle_orders_utils.show_failure_message(
									response.message
								);
							}
						},
						failure: function (xhr, statusText) {
							console.log(xhr);
							handle_orders_utils.show_failure_message(
								urmo_data.labels.network_error +
								'(' +
								statusText +
								')'
							);
						},
						complete: function () {
							handle_orders_utils.remove_spinner($this);
							handle_orders_utils.toggleSaveButtons(false);

						}
					}
				);

			});
		});

	}
)
(jQuery, window.urm_orders_localized_data);
