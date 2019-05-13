/**
 * UserRegistration Admin JS
 * global i18n_admin
 */
jQuery(function ($) {

	/**
	 * Append form settings to fileds section.
	 */
	var selector = $('.ur-tab-lists').find('li').last(); // Selector: Form settings tab.

	$(selector).on('click', function () {
		var fields_panel = $('.ur-selected-inputs');

		// Empty fields panel.
		fields_panel.children().hide();

		// Get form settings
		var form_settings_section = $('.ur-registered-inputs nav').find('#ur-tab-field-settings');
		var form_settings = form_settings_section.find('form');

		// Append form settings to fields panel.
		form_settings.appendTo(fields_panel);

		// Show only the form settings in fields panel.
		fields_panel.find('form#ur-field-settings').show();

		// Get all form settings
		var fields_panel_section = fields_panel.find('form #ur-field-all-settings').children();

		// Hide all fields settings from fields panel.
		fields_panel_section.hide();

		// Show general settings.
		fields_panel.find('form #ur-field-all-settings #general-settings').show();

		fields_panel_section.each(function (index, value) {

			var appending_text = $(value).find('h3').text();
			var appending_id = $(value).attr('id');

			// Append the title and div now under form settings.
			if (form_settings_section.find('#' + appending_id).length === 0) {
				form_settings_section.append('<div id="' + appending_id + '">' + appending_text + '</div>');
			}

			// Add active class to general settings and form-settings-tab for all settings.
			form_settings_section.find('#general-settings').addClass('active');
			form_settings_section.find('#' + appending_id).addClass('form-settings-tab');

			$(form_settings_section.find('#' + appending_id)).on('click', function () {

				// Remove all active classes initially.
				$(this).parent().find('.active').removeClass('active');

				// Add active class on clicked tab.
				$(this).addClass('active');

				// Hide other settings and show respective id's settings.
				fields_panel.find('form #ur-field-all-settings').children().hide();
				fields_panel.find('form #ur-field-all-settings').find('#' + appending_id).show();
			});
		});
	});

	/**
	 * Display fields panels on fields tab click.
	 */
	var fields = $('.ur-tab-lists').find('li').first(); // Fields tab.

	$(fields).on('click', function () {
		fields_panel = $('.ur-selected-inputs');
		fields_panel.children().show();
		fields_panel.find('form#ur-field-settings').hide();
	});

	/**
	 * Hide/Show minimum password strength field on the basis of enable strong password value.
	 */
	var minimum_password_strength_wrapper_field = $('#general-settings').find('#user_registration_form_setting_minimum_password_strength_field');
	var strong_password_field = $('#general-settings').find('#user_registration_form_setting_enable_strong_password_field select#user_registration_form_setting_enable_strong_password');
	var enable_strong_password = strong_password_field.val();

	if ('yes' === enable_strong_password) {
		minimum_password_strength_wrapper_field.show();
	} else {
		minimum_password_strength_wrapper_field.hide();
	}

	$(strong_password_field).change(function () {

		if ('yes' === $(this).val()) {
			minimum_password_strength_wrapper_field.show('slow');
		} else {
			minimum_password_strength_wrapper_field.hide('slow');
		}
	});

	// Tooltips
	$(document.body).on('init_tooltips', function () {
		var tiptip_args = {
			'attribute': 'data-tip',
			'fadeIn': 50,
			'fadeOut': 50,
			'delay': 200,
			'keepAlive': true
		};
		$('.tips, .help_tip, .user-registration-help-tip').tipTip(tiptip_args);

		tiptip_args['keepAlive'] = false;
		$('.ur-copy-shortcode').tipTip(tiptip_args);

		// Add tiptip to parent element for widefat tables
		$('.parent-tips').each(function () {
			$(this).closest('a, th').attr('data-tip', $(this).data('tip')).tipTip(tiptip_args).css('cursor', 'help');
		});
	}).trigger('init_tooltips');
	$('body').on('keypress', '#ur-form-name', function (e) {
		if (13 === e.which) {
			$('#save_form_footer').eq(0).trigger('click');
		}
	});
});

(function ($, user_registration_admin_data) {
	var i18n_admin = user_registration_admin_data.i18n_admin;
	$(function () {
		var user_profile_modal = {
			init: function () {
				$(document.body).on('click', '.column-data_link a', this.add_item).on('ur_backbone_modal_loaded', this.backbone.init).on('ur_backbone_modal_response', this.backbone.response);
			},
			add_item: function (e) {
				e.preventDefault();
				$(this).URBackboneModal({ template: 'test-demo' });
				return false;
			},
			backbone: {
				init: function (e, target) {
					if ('test-demo' === target) {
					}
				},
				response: function (e, target) {
					if ('test-demo' === target) {
					}
				}
			}
		};
		user_profile_modal.init();
		$.fn.ur_form_builder = function () {
			var loaded_params = {
				'active_grid': user_registration_admin_data.active_grid,
				'number_of_grid_list': user_registration_admin_data.number_of_grid,
				'min_grid_height': 70
			};
			// traverse all nodes
			return this.each(function () {
				// express a single node as a jQuery object
				var $this = $(this);
				var builder = {
					init: function () {
						this.single_row();
						manage_required_fields();
					},
					get_grid_button: function () {
						var grid_button = $('<div class="ur-grid-containner"/>');
						var grid_string = ur_math_ceil(ur_parse_int(loaded_params.number_of_grid_list) / ur_parse_int(loaded_params.active_grid)) + '/' + loaded_params.number_of_grid_list;
						var grid_content = '<div class="ur-grid-navigation ur-nav-right dashicons dashicons-arrow-left-alt2"></div>' + '<div class="ur-grid-size" data-active-grid="' + loaded_params.active_grid + '">' + grid_string + '</div>' + '<div class="ur-grid-navigation ur-nav-left dashicons dashicons-arrow-right-alt2"></div>' + '<button type="button" class="dashicons dashicons-no-alt ur-remove-row"></button>';
						grid_button.html(grid_content);
						return grid_button.html();
					},
					single_row: function () {
						var single_row = $('<div class=\'ur-single-row\'/>');
						single_row.append($('<div class=\'ur-grids\'/>'));
						var grid_button = this.get_grid_button();
						single_row.find('.ur-grids').append(grid_button);
						single_row.find('.ur-grids').find('span[data-id="' + loaded_params.active_grid + '"]').addClass('ur-active-grid');
						var grid_list = this.get_grid_lists(loaded_params.active_grid);
						single_row.append('<div style="clear:both"></div>');
						single_row.append(grid_list);
						single_row.append('<div style="clear:both"></div>');
						$this.append(single_row);
						$this.find('.ur-add-new-row').remove();
						$this.append('<button type="button" class="dashicons dashicons-plus-alt ur-add-new-row ui-sortable-handle"></button>');
						events.render_draggable_sortable();
						builder.manage_empty_grid();
						if (user_registration_admin_data.is_edit_form === '1') {
							$('.ur-single-row').eq($('.ur-single-row').length - 1).remove();
						}
						if (user_registration_admin_data.is_edit_form !== '1') {

							$('.ur-single-row').eq(0).find('.ur-grid-lists').eq(0).find('.ur-grid-list-item').eq(0).find('.user-registration-dragged-me').remove();
							$('.ur-single-row').eq(0).find('.ur-grid-lists').eq(0).find('.ur-grid-list-item').eq(0).append(user_registration_admin_data.required_form_html);
						}
						manage_draggable_users_fields();
					},
					get_grid_lists: function (number_of_grid) {
						var grid_lists = $('<div class="ur-grid-lists"/>');
						var total_width = 0;
						for (var i = 1; i <= number_of_grid; i++) {
							var grid_list_item = $('<div ur-grid-id=\'' + i + '\' class=\'ur-grid-list-item\'></div>');
							var width = Math.floor(100 / number_of_grid) - number_of_grid;
							total_width += width;
							grid_list_item.css({
								'width': width + '%',
								'min-height': loaded_params.min_grid_height + 'px'
							});
							grid_lists.append(grid_list_item);
						}
						grid_lists.append('<div style="clear:both"></div>');
						grid_lists.find('.ur-grid-list-item').eq('0').css({});
						return grid_lists;
					},
					populate_dropped_node: function (container, form_field_id) {
						var data = {
							action: 'user_registration_user_input_dropped',
							security: user_registration_admin_data.user_input_dropped,
							form_field_id: form_field_id
						};
						var template_text = '<div class="ur-selected-item ajax_added"><div class="ur-action-buttons">' + '<span title="Clone" class="dashicons dashicons-admin-page ur-clone"></span>' + '<span title="Trash" class="dashicons dashicons-trash ur-trash"></span>' + '</div>(content)</div>';
						container.closest('.ur-single-row').find('.user-registration-dragged-me').fadeOut();
						$.ajax({
							url: user_registration_admin_data.ajax_url,
							data: data,
							type: 'POST',
							beforeSend: function () {
								container.removeAttr('class').removeAttr('id').removeAttr('data-field-id').addClass('ur-selected-item').css({ 'width': 'auto' });
								container.html('<small class="spinner is-active"></small>');
								container.addClass('ur-item-dragged');
							},
							complete: function (response) {
								builder.manage_empty_grid();
								if (response.responseJSON.success === true) {
									var template = $(template_text.replace('(content)', response.responseJSON.data.template));
									template.removeClass('ajax_added');
									template.removeClass('ur-item-dragged');
									container.find('.ajax_added').find('.spinner').remove();
									container.find('.ajax_added').remove();
									$(template).insertBefore(container);
									container.remove();
								}
								manage_draggable_users_fields();

								var populated_item = template.closest('.ur-selected-item ').find("[data-field='field_name']").val();
								manage_conditional_field_options(populated_item);

							}
						});
					},
					manage_empty_grid: function () {
						var main_containner = $('.ur-selected-inputs');
						var drag_me = $('<div class="user-registration-dragged-me"/>');
						drag_me.html('<div class="user-registration-dragged-me-text"><p>' + i18n_admin.i18n_drag_your_first_item_here + '</p></div>');
						main_containner.find('.user-registration-dragged-me').remove();
						$.each(main_containner.find('.ur-grid-list-item'), function () {
							var $this = $(this);
							if ($(this).find('.ur-selected-item').length === 0) {
								$this.append(drag_me.clone());
							}
						});
					}
				};
				var events = {
					register: function () {
						this.register_add_new_row();
						this.register_remove_row();
						this.change_ur_grids();
						this.remove_selected_item();
						this.clone_selected_item();
					},
					register_add_new_row: function () {
						var $this_obj = this;
						$('body').on('click', '.ur-add-new-row', function () {
							var single_row_clone = $(this).closest('.ur-selected-inputs').find('.ur-single-row').eq(0).clone();
							single_row_clone.find('.ur-grid-lists').html('');
							single_row_clone.find('.ur-grids').find('span').removeClass('ur-active-grid');
							single_row_clone.find('.ur-grids').find('span[data-id="' + loaded_params.active_grid + '"]').addClass('ur-active-grid');
							// $(this).closest('.ur-single-row').find('.ur-add-new-row').remove();
							// $(this).closest('.ur-single-row').find('.ur-remove-row').html('-');
							var grid_list = builder.get_grid_lists(loaded_params.active_grid);
							single_row_clone.find('.ur-grid-lists').append(grid_list.html());
							single_row_clone.insertBefore('.ur-add-new-row');
							$this_obj.render_draggable_sortable();
							builder.manage_empty_grid();
						});
					},
					register_remove_row: function () {
						var $this = this;
						$('body').on('click', '.ur-remove-row', function () {
							if ($('.ur-selected-inputs').find('.ur-single-row').length > 1) {
								var confirm = window.confirm(i18n_admin.i18n_are_you_sure_want_to_delete);
								if (confirm) {
									var btn = $(this).prev();
									var new_btn;
									if (btn.hasClass('ur-add-new-row')) {
										new_btn = btn.clone();
									} else {
										new_btn = $(this).clone().attr('class', 'dashicons-minus ur-remove-row');
									}
									if (new_btn.hasClass('ur-add-new-row')) {
										$(this).closest('.ur-single-row').prev().find('.ur-remove-row').before(new_btn);
									}
									$(this).closest('.ur-single-row').remove();
									$this.check_grid();
								}
							} else {
								window.alert(i18n_admin.i18n_at_least_one_row_need_to_select);
							}
						});
					},
					change_ur_grids: function () {
						var $this_obj = this;
						$('body').on('click', '.ur-single-row .ur-nav-right', function () {
							var $this_single_row = $(this).closest('.ur-single-row');
							var grid_id = $(this).closest('.ur-grids').find('.ur-grid-size').attr('data-active-grid');
							if (grid_id >= loaded_params.number_of_grid_list) {
								return;
							}
							grid_id = ur_parse_int(grid_id) + 1;
							var grid_string = ur_math_ceil(ur_parse_int(loaded_params.number_of_grid_list) / ur_parse_int(grid_id)) + '/' + loaded_params.number_of_grid_list;
							$(this).closest('.ur-grids').find('.ur-grid-size').attr('data-active-grid', grid_id);
							$(this).closest('.ur-grids').find('.ur-grid-size').text(grid_string);
							var grids = builder.get_grid_lists(grid_id);
							$.each($this_single_row.find('.ur-grid-lists .ur-grid-list-item'), function () {
								$(this).children('*').each(function () {
									grids.find('.ur-grid-list-item').eq(0).append($(this).clone());  // "this" is the current element in the loop
								});
							});
							$this_single_row.find('.ur-grid-lists').eq(0).hide();
							grids.clone().insertAfter($this_single_row.find('.ur-grid-lists'));
							$this_single_row.find('.ur-grid-lists').eq(0).remove();
							$this_obj.render_draggable_sortable();
							builder.manage_empty_grid();
						});
						$('body').on('click', '.ur-single-row .ur-nav-left', function () {
							var $this_single_row = $(this).closest('.ur-single-row');
							var grid_id = $(this).closest('.ur-grids').find('.ur-grid-size').attr('data-active-grid');
							if (grid_id <= 1) {
								return;
							}
							grid_id = ur_parse_int(grid_id) - 1;
							var grid_string = ur_math_ceil(ur_parse_int(loaded_params.number_of_grid_list) / ur_parse_int(grid_id)) + '/' + loaded_params.number_of_grid_list;
							$(this).closest('.ur-grids').find('.ur-grid-size').attr('data-active-grid', grid_id);
							$(this).closest('.ur-grids').find('.ur-grid-size').text(grid_string);
							var grids = builder.get_grid_lists(grid_id);
							$.each($this_single_row.find('.ur-grid-lists .ur-grid-list-item'), function () {
								$(this).children('*').each(function () {
									grids.find('.ur-grid-list-item').eq(0).append($(this).clone());  // "this" is the current element in the loop
								});
							});
							$this_single_row.find('.ur-grid-lists').eq(0).hide();
							grids.clone().insertAfter($this_single_row.find('.ur-grid-lists'));
							$this_single_row.find('.ur-grid-lists').eq(0).remove();
							$this_obj.render_draggable_sortable();
							builder.manage_empty_grid();
						});
					},
					render_draggable_sortable: function () {
						$('.ur-grid-list-item').sortable({
							containment: '.ur-selected-inputs',
							over: function () {
								$(this).addClass('ur-sortable-active');
								builder.manage_empty_grid();
							},
							out: function () {
								$(this).removeClass('ur-sortable-active');
								builder.manage_empty_grid();
							},
							revert: true,
							connectWith: '.ur-grid-list-item'
						}).disableSelection();
						$('.ur-selected-inputs').sortable({
							containment: '.ur-selected-inputs',
							tolerance: 'pointer',
							revert: 'invalid',
							placeholder: 'ur-single-row',
							forceHelperSize: true,
							over: function () {
								$(this).addClass('ur-sortable-active');
							},
							out: function () {
								$(this).removeClass('ur-sortable-active');
							}
						});
						$('#ur-draggabled li').draggable({
							connectToSortable: '.ur-grid-list-item',
							containment: '.ur-registered-from',
							helper: 'clone',
							revert: 'invalid',
							// start: function (event, ui) {
							// },
							stop: function (event, ui) {
								if ($(ui.helper).closest('.ur-grid-list-item').length === 0) {
									return;
								}
								var data_field_id = $.trim($(ui.helper).attr('data-field-id').replace('user_registration_', ''));
								var length_of_required = $('.ur-selected-inputs').find('.ur-field[data-field-key="' + data_field_id + '"]').length;
								var only_one_field_index = $.makeArray(user_registration_admin_data.form_one_time_draggable_fields);
								if (length_of_required > 0 && $.inArray(data_field_id, only_one_field_index) >= 0) {
									show_message(i18n_admin.i18n_user_required_field_already_there);
									$(ui.helper).remove();
									return;
								}
								var clone = $(ui.helper);
								var form_field_id = $(clone).attr('data-field-id');
								if (typeof form_field_id !== 'undefined') {
									var this_clone = $(ui.helper).closest('.ur-grid-list-item').find('li[data-field-id="' + $(this).attr('data-field-id') + '"]');
									builder.populate_dropped_node(this_clone, form_field_id);
								}
							}
						}).disableSelection();
					},
					remove_selected_item: function () {
						var $this = this;
						$('body').on('click', '.ur-selected-item .ur-action-buttons  .ur-trash', function ( e ) {
							var removed_item = $(this).closest('.ur-selected-item ').find("[data-field='field_name']").val();
							$(this).closest('.ur-selected-item ').remove();
							$this.check_grid();
							builder.manage_empty_grid();
							manage_draggable_users_fields();

							//remove item from conditional logic options
							jQuery('[class*="urcl-settings-rules_field_"] option[value="' + removed_item + '"]').remove();

							return false; // To prevent click on whole item.
						});
					},
					clone_selected_item: function () {
						$('body').on('click', '.ur-selected-item .ur-action-buttons  .ur-clone', function () {
							var data_field_key = $(this).closest('.ur-selected-item ').find('.ur-field').attr('data-field-key');
							var selected_node = $('.ur-selected-inputs').find('.ur-field[data-field-key="' + data_field_key + '"]');
							var length_of_required = selected_node.length;
							if (length_of_required > 0 && $.inArray(data_field_key, user_registration_admin_data.form_one_time_draggable_fields) > -1) {
								show_message(i18n_admin.i18n_user_required_field_already_there_could_not_clone);
								return;
							}
							var clone = $(this).closest('.ur-selected-item ').clone();
							var label_node = clone.find('input[data-field="field_name"]');
							var regex = /\d+/g;
							var matches = label_node.val().match(regex);
							var find_string = matches.length > 0 ? matches[matches.length - 1] : '';
							var label_string = label_node.val().replace(find_string, '');
							clone.find('input[data-field="field_name"]').attr('value', label_string + new Date().getTime());
							$(this).closest('.ur-grid-list-item').append(clone);
						});
					},
					check_grid: function () {
						$('.ur-tabs').tabs({ disabled: [1] });
						$('.ur-tabs').find('a').eq(0).trigger('click');
						$('.ur-tabs').find( '[aria-controls="ur-tab-field-options"]' ).addClass( "ur-no-pointer" );
						$('.ur-selected-item').removeClass('ur-item-active');
					}
				};
				builder.init();
				events.register();
			});
		};
		$('.ur-selected-inputs').ur_form_builder();
		$('.ur-tabs').find('a').click(function () {
			$('.ur-tabs').find('a').removeClass('active');
			$(this).addClass('active');
		});
		$('.ur-tabs').tabs();
		$('.ur-tabs').find('a').eq(0).trigger('click');
		$('.ur-tabs').tabs({ disabled: [1] });
		$('body').on('click', '.ur-selected-item', function () {
			$('.ur-registered-inputs').find('ul li.ur-no-pointer').removeClass('ur-no-pointer');
			$('.ur-selected-item').removeClass('ur-item-active');
			$(this).addClass('ur-item-active');
			render_advance_setting($(this));
			init_events();
		});
		function render_advance_setting(selected_obj) {
			var advance_setting = selected_obj.find('.ur-advance-setting-block').clone();
			var general_setting = selected_obj.find('.ur-general-setting-block').clone();
			var form = $('<form id=\'ur-setting-form\'/>');
			$('#ur-tab-field-options').html('');
			form.append(general_setting);
			form.append(advance_setting);
			$('#ur-tab-field-options').append(form);
			//$('#ur-tab-field-options').append(advance_setting);
			$('#ur-tab-field-options').find('.ur-advance-setting-block').show();
			$('#ur-tab-field-options').find('.ur-general-setting-block').show();
			if ($('.ur-item-active').length === 1) {
				$('.ur-tabs').tabs('enable', 1);
				$('.ur-tabs').find('a').eq(1).trigger('click');
			}
			$('.ur-options-list').sortable({
				containment: '.ur-general-setting-options',
			});
		}

		$('.ur_save_form_action_button').on('click', function () {
			var validation_response = get_validation_status();
			if (validation_response.validation_status === false) {
				show_message(validation_response.message);
				return;
			}

			var form_data = get_form_data();
			var ur_form_id = $('#ur_form_id').val();
			var ur_form_id_localization = user_registration_admin_data.post_id;
			if (ur_parse_int(ur_form_id_localization, 0) !== ur_parse_int(ur_form_id, 0)) {
				ur_form_id = 0;
			}

			var form_setting_data = $('#ur-field-settings').serializeArray();

			var data = {
				action: 'user_registration_form_save_action',
				security: user_registration_admin_data.ur_form_save,
				data: {
					form_data: JSON.stringify(form_data),
					form_name: $('#ur-form-name').val(),
					form_id: ur_form_id,
					form_setting_data: form_setting_data
				}
			};
			$.ajax({
				url: user_registration_admin_data.ajax_url,
				data: data,
				type: 'POST',
				beforeSend: function () {
					var spinner = '<span class="spinner is-active" style="float: left;margin-top: 6px;"></span>';
					$('.ur_save_form_action_button').closest('.publishing-action').append(spinner);
					$('.ur-notices').remove();
				},
				complete: function (response) {
					$('.ur_save_form_action_button').closest('.publishing-action').find('.spinner').remove();
					if (response.responseJSON.success === true) {
						var success_message = i18n_admin.i18n_form_successfully_saved;
						show_message(success_message, 'success');
						var location = user_registration_admin_data.admin_url + response.responseJSON.data.post_id;
						window.location = location;
					} else {
						var error = response.responseJSON.data.message;
						show_message(error);
					}
				}
			});
		});

	});
	function show_message(message, type) {
		var message_string;
		if (type === 'success') {
			message_string = '<div class="updated ur-notices" style="border-color: green;"><p><strong>' + i18n_admin.i18n_success + '! </strong>' + message + '</p></div>';
		} else {
			message_string = '<div class="updated ur-notices" style="border-color: red;"><p><strong>' + i18n_admin.i18n_error + '!!! </strong>' + message + '</p></div>';
		}

		$('.ur-form-subcontainer').find('.ur-notices').remove();
		$('.ur-form-subcontainer').prepend(message_string);
		$('html, body').animate({
			scrollTop: ($('.ur-notices').offset().top) - 50
		}, 600);
	}

	function get_validation_status() {
		var only_one_field_index = $.makeArray(user_registration_admin_data.form_one_time_draggable_fields);
		var required_fields = $.makeArray(user_registration_admin_data.form_required_fields);
		var response = {
			validation_status: true,
			message: ''
		};
		if ($('.ur-selected-item').length === 0) {
			response.validation_status = false;
			response.message = i18n_admin.i18n_at_least_one_field_need_to_select;
			return response;
		}
		if ($('#ur-form-name').val() === '') {
			response.validation_status = false;
			response.message = i18n_admin.i18n_empty_form_name;
			return response;
		}
		if ($('.ur_save_form_action_button').closest('.publishing-action').find('.spinner').length > 0) {
			response.validation_status = false;
			response.message = i18n_admin.i18n_previous_save_action_ongoing;
			return response;
		}
		$.each($('.ur-selected-inputs .ur-general-setting-block input[data-field="field_name"]'), function () {
			var $field = $(this);
			var need_to_break = false;
			var field_attribute;
			try {
				var field_value = $field.val();
				var length = $('.ur-selected-inputs .ur-general-setting-block').find('input[data-field="field_name"][value="' + field_value + '"]').length;
				if (length > 1) {
					throw i18n_admin.i18n_duplicate_field_name;
				}
				if ($field.closest('.ur-general-setting-block').find('input[data-field="label"]').val() === '') {
					$field = $field.closest('.ur-general-setting-block').find('input[data-field="label"]');
					throw i18n_admin.i18n_empty_field_label;
				}
				var field_regex = /[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/gm;
				var regex_result = field_value.match(field_regex);
				if (regex_result !== null && regex_result.length === 1 && regex_result[0] === field_value) {
				} else {
					throw i18n_admin.i18n_invald_field_name;
				}
			} catch (err) {
				response.validation_status = false;
				response.message = err.message === undefined ? err : err.message;
				$field.closest('.ur-selected-item').trigger('click');
				field_attribute = $field.attr('data-field');
				$('#ur-setting-form').find('input[data-field="' + field_attribute + '"]').css({ 'border': '1px solid red' });
				setTimeout(function () {
					$('#ur-setting-form').find('input[data-field="' + field_attribute + '"]').removeAttr('style');
				}, 2000);
				need_to_break = true;  //console.log('User registration console ' + $field.closest('.ur-selected-item').find('.ur-label label').text());
			}
			if (need_to_break) {
				return false;
			}
		});
		for (var single_field = 0; single_field < only_one_field_index.length; single_field++) {
			if ($('.ur-selected-inputs').find('.ur-field[data-field-key="' + only_one_field_index[single_field] + '"]').length > 1) {
				response.validation_status = false;
				response.message = i18n_admin.i18n_multiple_field_key + only_one_field_index[single_field];
				break;
			}
		}
		for (var required_index = 0; required_index < required_fields.length; required_index++) {

			if ($('.ur-selected-inputs').find('.ur-field[data-field-key="' + required_fields[required_index] + '"]').length === 0) {
				response.validation_status = false;

				if (required_index === 0) {
					var field = i18n_admin.i18n_user_email;
				} else if (required_index === 1) {
					var field = i18n_admin.i18n_user_password;
				}

				response.message = field + ' ' + i18n_admin.i18n_field_is_required;
				break;
			}
		}
		return response;
	}

	function get_form_data() {
		var form_data = [];
		var single_row = $('.ur-selected-inputs .ur-single-row');
		$.each(single_row, function () {
			var grid_list_item = $(this).find('.ur-grid-list-item');
			var single_row_data = [];
			$.each(grid_list_item, function () {
				var grid_item = $(this);
				var grid_wise_data = get_grid_wise_data(grid_item);
				single_row_data.push(grid_wise_data);
			});

			form_data.push(single_row_data);
		});
		return form_data;
	}

	function get_grid_wise_data($grid_item) {
		var all_field_item = $grid_item.find('.ur-selected-item');
		var all_field_data = [];
		$.each(all_field_item, function () {
			var $this_item = $(this);
			var field_key = $this_item.find('.ur-field').attr('data-field-key');
			var single_field_data = {
				field_key: field_key,
				general_setting: get_field_general_setting($this_item),
				advance_setting: get_field_advance_setting($this_item)
			};

			all_field_data.push(single_field_data);
		});
		return all_field_data;
	}

	function get_field_general_setting($single_item) {

		var general_setting_field = $single_item.find('.ur-general-setting-block').find('.ur-general-setting-field');
		var general_setting_data = {};

		var option_values  = [];
		var default_values = [];
		$.each(general_setting_field, function () {

			var is_checkbox = $(this).closest('.ur-general-setting-block').hasClass('ur-general-setting-checkbox');

			if( 'options' === $(this).attr('data-field') ) {
				general_setting_data['options'] = option_values.push( get_ur_data($(this) ) );
				general_setting_data['options'] = option_values;
			} else {

				if( 'default_value' === $(this).attr('data-field') ) {

					if( is_checkbox === true ) {
						if( $(this).is(":checked") ) {
							general_setting_data['default_value'] = default_values.push( get_ur_data( $(this)));
							general_setting_data['default_value'] = default_values;
						}
					} else if( $(this).is(":checked") ) {
							general_setting_data['default_value'] = get_ur_data($(this) );
					}

				} else {
					general_setting_data[$(this).attr('data-field')] = get_ur_data($(this)) ;
				}
			}
		});


		return general_setting_data;
	}

	function get_field_advance_setting($single_item) {
		var advance_setting_field = $single_item.find('.ur-advance-setting-block').find('.ur_advance_setting');
		var advance_setting_data = {};
		$.each(advance_setting_field, function () {
			advance_setting_data[$(this).attr('data-advance-field')] = get_ur_data($(this));
		});
		return advance_setting_data;
	}

	function get_ur_data($this_node) {
		var node_type = $this_node.get(0).tagName.toLowerCase();
		var value = '';
		switch (node_type) {
			case 'input':
				value = $this_node.val();
				break;
			case 'select':
				value = $this_node.val();
				break;
			case 'textarea':
				value = $this_node.val();
				break;
			default:
		}
		return value;
	}

	function init_events() {
		var general_setting = $('.ur-general-setting-field');
		$.each(general_setting, function () {
			var $this_obj = $(this);
			switch ($this_obj.attr('data-field')) {
				case 'label':
					$this_obj.on('keyup', function () {
						trigger_general_setting_label($(this));
					});
					break;
				case 'field_name':
				case 'input_mask':
					$this_obj.on('change', function () {
						trigger_general_setting_field_name($(this));
					});
				case 'default_value':
					$this_obj.on('change', function () {

						if ( 'default_value' === $this_obj.attr('data-field') ) {
							if( $this_obj.closest('.ur-general-setting-block').hasClass('ur-general-setting-select') ) {
								render_select_box( $(this) );
							} else if ( $this_obj.closest('.ur-general-setting-block').hasClass('ur-general-setting-radio') ) {
								render_radio( $(this) );
							} else if ( $this_obj.closest('.ur-general-setting-block').hasClass('ur-general-setting-checkbox') ) {
								render_check_box( $(this) );
							}
						}
					});
				break;
				case 'options':
					$this_obj.on('keyup', function () {

						if( $this_obj.closest('.ur-general-setting-block').hasClass('ur-general-setting-select') && $this_obj.siblings('input[data-field="default_value"]').is(':checked') ) {
							render_select_box( $(this) );
						} else if ( $this_obj.closest('.ur-general-setting-block').hasClass('ur-general-setting-radio') ) {
							render_radio( $(this) );
						} else if ( $this_obj.closest('.ur-general-setting-block').hasClass('ur-general-setting-checkbox') ) {
							render_check_box( $(this) );
						}

						trigger_general_setting_options($(this));
					});
					break;
				case 'placeholder':
					$this_obj.on('keyup', function () {
						trigger_general_setting_placeholder($(this));
					});
					break;
				case 'required':
					$this_obj.on('change', function () {
						trigger_general_setting_required($(this));
					});
					break;
				case 'hide_label':
					$this_obj.on('change', function () {
						trigger_general_setting_hide_label($(this));
					});
					break;
				case 'description':
				case 'html':
					$this_obj.on('keyup', function () {
						trigger_general_setting_description($(this));
					});
					break;
			}
		});
		var advance_settings = $('.ur_advance_setting');
		$.each(advance_settings, function () {
			var $this_node = $(this);
			var node_type = $this_node.get(0).tagName.toLowerCase();
			var event = 'change';
			switch (node_type) {
				case 'input':
					event = 'keyup';
					break;
				case 'select':
					event = 'change';
					break;
				case 'textarea':
					event = 'keyup';
					break;
				default:
					event = 'change';
			}
			$(this).on(event, function () {
				trigger_advance_setting($this_node, node_type);
			});
			$(this).on('paste', function () {
				trigger_advance_setting($this_node, node_type);
			});
		});
	}

	function trigger_advance_setting($this_node, node_type) {
		var wrapper = $('.ur-selected-item.ur-item-active');
		var this_node_id = $this_node.attr('data-id');
		var hidden_node = wrapper.find('.ur-advance-setting-block').find('[data-id="' + this_node_id + '"]');
		switch (node_type) {
			case 'input':
				hidden_node.val($this_node.val());
				break;
			case 'select':
				hidden_node.find('option[value="' + $this_node.val() + '"]').attr('selected', 'selected');
				break;
			case 'textarea':
				hidden_node.val($this_node.val());
				render_text_area($this_node.val());
				break;
		}
	}

	function render_text_area(value) {
		var wrapper = $('.ur-selected-item.ur-item-active');
		var field_type = wrapper.find('.ur-field');
		switch (field_type.attr('data-field-key')) {
			case 'select':
				render_select_box(value);
				break;
			case 'checkbox':
				render_check_box(value);
				break;
			case 'radio':
				render_radio(value);
				break;
		}
	}
	function render_check_box(this_node) {

		var array_value = [];
		var li_elements = this_node.closest('ul').find('li');
		var checked_index = this_node.closest('li').index();

		li_elements.each( function( index, element) {
			var value 	 = $( element ).find('input.ur-type-checkbox-label').val();
				value 	 = $.trim(value);
				checkbox = $( element ).find('input.ur-type-checkbox-value').is( ':checked' );
				array_value.push( {value:value, checkbox:checkbox });
		});

		var wrapper = $('.ur-selected-item.ur-item-active');
		var checkbox = wrapper.find('.ur-field');
		checkbox.html('');

		for (var i = 0; i < array_value.length; i++) {
			if (array_value[i] !== '') {
				checkbox.append('<label><input value="' + array_value[i].value.trim() + '" type="checkbox" ' + ( (array_value[i].checkbox) ? 'checked' : '' ) + ' disabled>' + array_value[i].value.trim() + '</label>');
			}
		}

		if( this_node.is( ':checked' ) ) {
			wrapper.find('.ur-general-setting-options li:nth(' + checked_index + ') input[data-field="default_value"]').attr( 'checked', 'checked' );
		} else {
			wrapper.find('.ur-general-setting-options li:nth(' + checked_index + ') input[data-field="default_value"]').removeAttr( 'checked' );
		}
	}

	function render_radio(this_node) {
		var li_elements = this_node.closest('ul').find('li');
		var checked_index = undefined;
		var	array_value = [];

		li_elements.each( function( index, element) {
			var value = $( element ).find('input.ur-type-radio-label').val();
			value = $.trim(value);
			radio = $( element ).find('input.ur-type-radio-value').is( ':checked' );
			// Set checked elements index value
			if( radio === true) {
				checked_index = index;
			}
			array_value.push({value:value, radio:radio });
		});

		var wrapper = $('.ur-selected-item.ur-item-active');
		var radio = wrapper.find('.ur-field');
		radio.html('');

		for (var i = 0; i < array_value.length; i++) {
			if (array_value[i] !== '') {
				radio.append('<label><input value="' + array_value[i].value.trim() + '" type="radio" ' + ( (array_value[i].radio)? 'checked' : '' ) + ' disabled>' + array_value[i].value.trim() + '</label>');
			}
		}

		// Loop through options in active fields general setting hidden div.
		wrapper.find( '.ur-general-setting-options > ul.ur-options-list > li' ).each( function( index, element ) {
			var radio_input = $(element).find( '[data-field="default_value"]' );
			if( index === checked_index ){
				radio_input.attr( 'checked', 'checked' );
			}else{
				radio_input.removeAttr( 'checked' );
			}
		} );
	}

	function render_select_box(this_node) {
		value = $.trim( this_node.val() );
		var wrapper = $('.ur-selected-item.ur-item-active');
		var checked_index = this_node.closest('li').index();
		var select = wrapper.find('.ur-field').find('select');

		select.html('');
		select.append('<option value=\'' + value + '\'>' + value + '</option>');

		wrapper.find('.ur-general-setting-options li input[data-field="default_value"]').removeAttr( 'checked' );
		wrapper.find('.ur-general-setting-options li:nth(' + checked_index + ') input[data-field="default_value"]').attr( 'checked', 'checked' );
	}

	function trigger_general_setting_field_name($label) {
		var wrapper = $('.ur-selected-item.ur-item-active');
		wrapper.find('.ur-general-setting-block').find('input[data-field="' + $label.attr('data-field') + '"]').attr('value', $label.val());
	}

	function trigger_general_setting_options($label) {

		var wrapper = $('.ur-selected-item.ur-item-active');
		var index = $label.closest('li').index();
		wrapper.find( '.ur-general-setting-block li:nth(' + index + ') input[data-field="' + $label.attr('data-field') + '"]' ).attr( 'value', $label.val() );
		wrapper.find( '.ur-general-setting-block li:nth(' + index + ') input[data-field="default_value"]' ).val( $label.val() );
		$label.closest('li').find('[data-field="default_value"]').val( $label.val() );
	}

	function trigger_general_setting_label($label) {
		var wrapper = $('.ur-selected-item.ur-item-active');
		wrapper.find('.ur-label').find('label').text($label.val());

		var wrapper = $('.ur-selected-item.ur-item-active');
		wrapper.find('.ur-general-setting-block').find('input[data-field="' + $label.attr('data-field') + '"]').attr('value', $label.val());

	}

	function trigger_general_setting_description($label) {
		var wrapper = $('.ur-selected-item.ur-item-active');
		wrapper.find('.ur-field').find('textarea').attr('description', $label.val());
		wrapper.find('.ur-general-setting-block').find('textarea[data-field="' + $label.attr('data-field') + '"]').val($label.val());
	}

	function trigger_general_setting_placeholder($label) {
		var wrapper = $('.ur-selected-item.ur-item-active');
		wrapper.find('.ur-field').find('input').attr('placeholder', $label.val());
		wrapper.find('.ur-general-setting-block').find('input[data-field="' + $label.attr('data-field') + '"]').val($label.val());
	}

	function trigger_general_setting_required($label) {
		var wrapper = $('.ur-selected-item.ur-item-active');
		wrapper.find('.ur-label').find('label').find('span').remove();
		if ($label.val() === 'yes') {
			wrapper.find('.ur-label').find('label').append('<span style="color:red">*</span>');
		}
		wrapper.find('.ur-general-setting-block').find('select[data-field="' + $label.attr('data-field') + '"]').find('option[value="' + $label.val() + '"]').attr('selected', 'selected');
	}

	function trigger_general_setting_hide_label($label) {
		var wrapper = $('.ur-selected-item.ur-item-active');
		wrapper.find('.ur-label').find('label').find('span').remove();
		wrapper.find('.ur-general-setting-block').find('select[data-field="' + $label.attr('data-field') + '"]').find('option[value="' + $label.val() + '"]').attr('selected', 'selected');
	}

	function manage_required_fields() {
		var required_fields = user_registration_admin_data.form_required_fields;

		var selected_inputs = $('.ur-selected-inputs');

		if ($.isArray(required_fields)) {

			for (var i = 0; i < required_fields.length; i++) {

				var field_node = selected_inputs.find('.ur-field[data-field-key="' + required_fields[i] + '"]');

				field_node.closest('.ur-selected-item').find('select[data-field="required"]').val('yes').trigger('change');
				field_node.closest('.ur-selected-item').find('select[data-field="required"]').find('option[value="yes"]').attr('selected', 'selected');
				field_node.closest('.ur-selected-item').find('select[data-field="required"]').attr('disabled', 'disabled');
			}
		}

		var label_node = selected_inputs.find('select[data-field="required"]').find('option[selected="selected"][value="yes"]').closest('.ur-selected-item').find('.ur-label').find('label');
		label_node.find('span').remove();
		label_node.append('<span style="color:red">*</span>');
	}

	function manage_draggable_users_fields() {

		var single_draggable_fields = user_registration_admin_data.form_one_time_draggable_fields;

		var ul_node = $('#ur-tab-registered-fields').find('ul.ur-registered-list');

		$.each(ul_node.find('li'), function () {

			var $this = $(this);

			var data_field_id = $(this).attr('data-field-id').replace('user_registration_', '');

			if ($.inArray(data_field_id, single_draggable_fields) >= 0) {

				if ($('.ur-selected-inputs').find('.ur-field[data-field-key="' + data_field_id + '"]').length > 0) {
					$this.draggable('disable');
				} else {
					$this.draggable('enable');
				}
			}
		});
	}

	function manage_conditional_field_options(populated_item) {

		jQuery('.ur-grid-lists .ur-selected-item .ur-admin-template').each(function () {
			var field_label = jQuery(this).find('.ur-label label').text();
			var field_key = jQuery(this).find('.ur-field').attr('data-field-key');

			//strip certain fields
			if ('section_title' == field_key || 'html' == field_key || 'wysiwyg' == field_key || 'billing_address_title' == field_key || 'shipping_address_title' == field_key) {
				return;
			}

			var general_setting = jQuery(this).find('.ur-general-setting-block .ur-general-setting');
			general_setting.each(function () {
				var field_name = jQuery(this).find("[data-field='field_name']").val();
				if (typeof field_name !== 'undefined') {

					//check if option exist in the given select
					var select_value = jQuery(".urcl-rules select.ur_advance_setting.urcl-settings-rules_field_1 option[value='" + field_name + "']").length > 0;
					if (!select_value == true) {
						jQuery('[class*="urcl-settings-rules_field_"]').append('<option value ="' + field_name + '" data-type="' + field_key + '">' + field_label + ' </option>');
						if (field_name == populated_item) {
							jQuery('.urcl-rules select.ur_advance_setting.urcl-settings-rules_field_1.empty-fields option[value="' + populated_item + '"]').remove();
						}
					} else {
						jQuery('.urcl-rules select.ur_advance_setting.urcl-settings-rules_field_1.empty-fields').append('<option value ="' + field_name + '" data-type="' + field_key + '">' + field_label + ' </option>');
					}
				}
			});
		});
		jQuery('.urcl-rules select.ur_advance_setting.urcl-settings-rules_field_1.empty-fields').removeClass('empty-fields');
	}

	function ur_math_ceil(value) {
		return Math.ceil(value, 0);
	}

	function ur_parse_int(value) {
		return parseInt(value, 0);
	}

	setTimeout(function () {
		var date_selector = $('#profile-page form#your-profile  input[type="date"]');
		if (date_selector.length > 0) {
			date_selector.addClass('flatpickr-field').attr('type', 'text').flatpickr({
				disableMobile: true
			});
		}
	}, 2);

	$(document).on('click', '#ur-tab-registered-fields h2', function () {
		if ($(this).hasClass('closed')) {
			$(this).removeClass('closed');
		} else {
			$(this).addClass('closed');
		}
		var field_list = $(this).find(' ~ .ur-registered-list')[0];
		$(field_list).slideToggle();
	});

	$(document).on('click', '.ur-options-list .add', function( e ) {

		e.preventDefault();
		var $this 		    = $(this),
			$wrapper        = $( '.ur-selected-item.ur-item-active' ),
			this_index = $this.parent('li').index(),
			cloning_element = $this.parent('li').clone(true, true);

		cloning_element.find('input[data-field="options"]').val('');
		cloning_element.find('input[data-field="default_value"]').removeAttr('checked');

		$this.parent('li').after( cloning_element );
		$wrapper.find( '.ur-general-setting-options .ur-options-list > li:nth( ' + this_index + ' )' ).after( cloning_element.clone(true, true) );

		if ( $this.closest('.ur-general-setting-block').hasClass('ur-general-setting-radio') ) {
			render_radio( $this );
		} else if( $this.closest('.ur-general-setting-block').hasClass('ur-general-setting-checkbox') ) {
			render_check_box( $this );
		}
	});

	$(document).on('click', '.ur-options-list .remove', function( e ) {

		e.preventDefault();
		var $this 		    = $(this),
			$parent_ul      = $(this).closest('ul');
			$any_siblings   = $parent_ul.find('li');
			$wrapper        = $( '.ur-selected-item.ur-item-active' ),
			this_index 		= $this.parent('li').index();

		if( $parent_ul.find('li').length > 1 ) {

			$this.parent('li').remove();
			$wrapper.find( '.ur-general-setting-options .ur-options-list > li:nth( ' + this_index + ' )' ).remove();

			if ( $any_siblings.closest('.ur-general-setting-block').hasClass('ur-general-setting-radio') ) {
				render_radio( $any_siblings );
			} else if( $any_siblings.closest('.ur-general-setting-block').hasClass('ur-general-setting-checkbox') ) {
				render_check_box( $any_siblings );
			}
		}
	});

	$( document ).on('sortstop', '.ur-options-list', function( event, ui ) {
		var $this = $( this );
		ur_clone_options( $this );
		if ( $this.closest('.ur-general-setting-block').hasClass('ur-general-setting-radio') ) {
			render_radio( $this );
		} else if( $this.closest('.ur-general-setting-block').hasClass('ur-general-setting-checkbox') ) {
			render_check_box( $this );
		}
	});

	function ur_clone_options( $this_obj ) {
		var cloning_options = $this_obj.clone( true, true );
		var wrapper 		= $('.ur-selected-item.ur-item-active');
		var cloning_element 	= wrapper.find( '.ur-general-setting-options .ur-options-list');
		cloning_element.html('');
		cloning_element.replaceWith(cloning_options);
	}

}(jQuery, window.user_registration_admin_data));
