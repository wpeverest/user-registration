/**
 * UserRegistration Admin JS
 * global i18n_admin
 */
jQuery(function ($) {

	// Bind UI Action handlers for searching fields.
	$( document.body ).on( 'input', '#ur-search-fields', function() {
		var search_string = $( this ).val().toLowerCase();
		
		// Show/Hide fields.
		$( '.ur-registered-item' ).each( function() {
			var field_label = $( this ).text().toLowerCase();
			if ( field_label.search( search_string ) > -1 ) {
				$( this ).addClass( 'ur-searched-item' );
				$( this ).show();
			} else {
				$( this ).removeClass( 'ur-searched-item' );
				$( this ).hide();
			}
		})

		// Show/Hide field sections.
		$( '.ur-registered-list' ).each( function() {
			var search_result_fields_count = $( this ).find( '.ur-registered-item.ur-searched-item' ).length;
			var hr = $( this ).prev( 'hr' );
			var heading = $( this ).prev( 'hr' ).prev( '.ur-toggle-heading' );
			
			if ( 0 === search_result_fields_count ) {
				hr.hide();
				heading.hide();
			} else {
				hr.show();
				heading.show();
			}
		})

		// Show/Hide fields not found indicator.
		if ( $( '.ur-registered-item.ur-searched-item' ).length ) {
			$( '.ur-fields-not-found' ).hide();
		} else {
			$( '.ur-fields-not-found' ).show();
		}
	});

	// Bind UI Actions for upgradable fields
	$( document ).on( 'mousedown', '.ur-upgradable-field', function( e ) {
		e.preventDefault();

		var icon = '<i class="dashicons dashicons-lock"></i>';
		var label = $(this).text();
		var title = icon + '<div class="ur-swal-title">' + label + ' is a Premium field.</div>';
		var plan = $(this).data('plan');
		var message = label + ' field is not available right now. Please upgrade to <strong>' + plan + '</strong> of the plugin to unlock this field.';

		Swal.fire({
			title: title,
			html: message,
			showCloseButton: true,
			confirmButtonText: 'Let\'s do it'
		}).then( function(result) {
			if ( result.value ) {
				var url = 'https://wpeverest.com/wordpress-plugins/user-registration/pricing/?utm_source=pro-fields&utm_medium=popup-button&utm_campaign=ur-upgrade-to-pro';
				window.open( url, '_blank' );
			}
		});
	});

	// Adjust builder width
	$( window ).on( 'resize orientationchange', function() {
		var resizeTimer;

		clearTimeout( resizeTimer );
		resizeTimer = setTimeout( function() {
			$( document.body ).trigger( 'adjust_builder_width' );
		}, 250 );
	} );

	$( document.body ).on( 'click', '#collapse-button', function() {
		$( document.body ).trigger( 'ur_adjust_builder_width' );
	} );

	$( document.body ).on( 'ur_adjust_builder_width', function() {
		var adminMenuWidth = $( '#adminmenuwrap' ).width(),
			$builder = $( '.user-registration_page_add-new-registration .ur-form-subcontainer .menu-edit' ),
			$loading = $( '.user-registration_page_add-new-registration .ur-form-subcontainer .ur-loading-container' );

		$builder.css({ 'left': adminMenuWidth + 'px'});
		$loading.fadeOut(1000);
	}).trigger( 'ur_adjust_builder_width' );

	// Form name edit.
	$( document.body ).on( 'click', '.ur-form-container .ur-registered-from .ur-form-name-wrapper .ur-edit-form-name', function() {
		var $input = $(this).siblings( '#ur-form-name' );
		if( ! $input.hasClass( 'ur-editing' ) ) {
			$input.focus();
		}
		$input.toggleClass( 'ur-editing' );
	} );

	$( document ).on( 'init_perfect_scrollbar update_perfect_scrollbar', function() {

		// Init perfect Scrollbar.
		if ( 'undefined' !== typeof PerfectScrollbar ) {
			var builder_wrapper = $( '.ur-builder-wrapper' ),
				tab_content = $( '.ur-tab-contents' );

			if( builder_wrapper.length >= 1 && 'undefined' === typeof window.ur_builder_scrollbar ) {
				window.ur_builder_scrollbar = new PerfectScrollbar( builder_wrapper.selector, {
					suppressScrollX: true
				} );
			} else if( 'undefined' !== typeof window.ur_builder_scrollbar ) {
				window.ur_builder_scrollbar.update();
			}

			if( tab_content.length >= 1 && 'undefined' === typeof window.ur_tab_scrollbar ) {
				window.ur_tab_scrollbar = new PerfectScrollbar( tab_content.selector, {
					suppressScrollX: true
				} );
			} else if ( 'undefined' !== typeof window.ur_tab_scrollbar ) {
				window.ur_tab_scrollbar.update();
				tab_content.scrollTop( 0 );
			}
		}
	} );

	/**
	 * Append form settings to fileds section.
	 */
	$( document ).ready( function() {

		$( document ).trigger( 'init_perfect_scrollbar' );

		var fields_panel = $('.ur-selected-inputs');
		var form_settings_section = $('.ur-registered-inputs nav').find('#ur-tab-field-settings');
		var form_settings = form_settings_section.find('form');

		form_settings.appendTo(fields_panel);

		fields_panel.find('form #ur-field-all-settings > div').each(function (index, el) {

			var appending_text = $(el).find('h3').text();
			var appending_id = $(el).attr('id');

			form_settings_section.append('<div id="' + appending_id + '" class="form-settings-tab">' + appending_text + '</div>');
			$( el ).hide();
		});


		// Add active class to general settings and form-settings-tab for all settings.
		form_settings_section.find('#general-settings').addClass('active');
		fields_panel.find( '#ur-field-all-settings div#general-settings' ).show();

		form_settings_section.find('.form-settings-tab').on('click', function () {

			this_id = $( this ).attr( 'id' );
			// Remove all active classes initially.
			$(this).siblings().removeClass('active');

			// Add active class on clicked tab.
			$(this).addClass('active');

			// Hide other settings and show respective id's settings.
			fields_panel.find('form #ur-field-all-settings > div').hide();
			fields_panel.find('form #ur-field-all-settings > div#' + this_id ).show();
			$( document ).trigger( 'update_perfect_scrollbar' );
			$( '.ur-builder-wrapper' ).scrollTop( 0 );
		});
	} );

	$( document ).on( 'click', '.ur-tab-lists li[role="tab"] a.nav-tab', function( e, $type ) {
		$( document ).trigger( 'update_perfect_scrollbar' );

		if( 'triggered_click' != $type ) {
			$( '.ur-builder-wrapper' ).scrollTop( 0 );
			$( '.ur-builder-wrapper-content' ).scrollTop( 0 );
		}
	} );

	// Setting Tab.
	$(document).on('click', '.ur-tab-lists li[aria-controls="ur-tab-field-settings"]', function () {

		// Empty fields panels.
		$( '.ur-builder-wrapper-content' ).hide();
		$( '.ur-builder-wrapper-footer' ).hide();

		// Show only the form settings in fields panel.
		$('.ur-selected-inputs').find('form#ur-field-settings').show();
	});

	/**
	 * Display fields panels on fields tab click.
	 */
	$(document).on('click', 'ul.ur-tab-lists li[aria-controls="ur-tab-registered-fields"]', function () {

		// Show field panels.
		$( '.ur-builder-wrapper-content' ).show();
		$( '.ur-builder-wrapper-footer' ).show();

		// Hide the form settings in fields panel.
		$( '.ur-selected-inputs' ).find( 'form#ur-field-settings' ).hide();
	});

	/**
	 * Hide/Show minimum password strength field on the basis of enable strong password value.
	 */
	var minimum_password_strength_wrapper_field = $('#general-settings').find('#user_registration_form_setting_minimum_password_strength_field');
	var strong_password_field = $('#general-settings').find('#user_registration_form_setting_enable_strong_password_field input#user_registration_form_setting_enable_strong_password');
	var enable_strong_password = strong_password_field.is(':checked');

	if ( 'yes' === enable_strong_password || true === enable_strong_password ) {
		minimum_password_strength_wrapper_field.show();
	} else {
		minimum_password_strength_wrapper_field.hide();
	}

	$(strong_password_field).change(function () {
		enable_strong_password = $(this).is(':checked');

		if ( 'yes' === enable_strong_password || true === enable_strong_password ) {
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

	$( '#ur-full-screen-mode' ).on( 'click', function(e) {
		e.preventDefault();
		var $this = $( this );

		if( $this.hasClass( 'closed' ) ) {
			$this.removeClass( 'closed' );
			$this.addClass( 'opened' );

			$( 'body' ).addClass( 'ur-full-screen-mode' );
		} else {
			$this.removeClass( 'opened' );
			$this.addClass( 'closed' );

			$( 'body' ).removeClass( 'ur-full-screen-mode' );
		}
	} );

	$( document ).on( 'keyup', function( e ) {
		if( 'Escape' === e.key ) {
			$( '#ur-full-screen-mode.opened' ).trigger( 'click' );
		}
	} );

	$( 'input.input-color' ).wpColorPicker();
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
						var grid_content = '<button type="button" class="ur-edit-grid"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path d="M28,6V26H4V6H28m2-2H2V28H30V4Z"/></svg></button>';
						grid_content += '<button type="button" class="dashicons dashicons-no-alt ur-remove-row"></button>';
						grid_content += '<div class="ur-toggle-grid-content" style="display:none">';
						grid_content += '<small>Select the grid column.</small>';
						grid_content += '<div class="ur-grid-selector" data-grid = "1">';
						grid_content += '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path d="M28,6V26H4V6H28m2-2H2V28H30V4Z"/></svg>';
						grid_content += '</div>';
						grid_content += '<div class="ur-grid-selector" data-grid = "2">';
						grid_content += '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path d="M17,4H2V28H30V4ZM4,26V6H15V26Zm24,0H17V6H28Z"/></svg>';
						grid_content += '</div>';
						grid_content += '<div class="ur-grid-selector" data-grid = "3">';
						grid_content += '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path d="M22,4H2V28H30V4ZM4,26V6h6V26Zm8,0V6h8V26Zm16,0H22V6h6Z"/></svg>';
						grid_content += '</div>';
						grid_content += '</div>';
						grid_button.html(grid_content);
						return grid_button.html();
					},
					single_row: function () {

						if (user_registration_admin_data.is_edit_form !== '1') {
							var single_row = $('<div class=\'ur-single-row\'/ data-row-id="0">');
							single_row.append($('<div class=\'ur-grids\'/>'));
							var grid_button = this.get_grid_button();
							single_row.find('.ur-grids').append(grid_button);
							single_row.find('.ur-grids').find('span[data-id="' + loaded_params.active_grid + '"]').addClass('ur-active-grid');
							var grid_list = this.get_grid_lists(loaded_params.active_grid);
							single_row.append('<div style="clear:both"></div>');
							single_row.append(grid_list);
							single_row.append('<div style="clear:both"></div>');

							$this.append(single_row);
							$('.ur-single-row').eq(0).find('.ur-grid-lists').eq(0).find('.ur-grid-list-item').eq(0).find('.user-registration-dragged-me').remove();
							$('.ur-single-row').eq(0).find('.ur-grid-lists').eq(0).find('.ur-grid-list-item').eq(0).append(user_registration_admin_data.required_form_html);
						}

						if ( $this.find('.ur-add-new-row').length == 0 ) {
							$this.append('<button type="button" class="button button-primary dashicons dashicons-plus-alt ur-add-new-row ui-sortable-handle" data-total-rows="0">' + user_registration_admin_data.add_new + '</button>');
							var total_rows = $this.find('.ur-add-new-row').siblings('.ur-single-row').last().prev().attr('data-row-id');
							$this.find('.ur-add-new-row').attr('data-total-rows', total_rows );
						}
						events.render_draggable_sortable();
						builder.manage_empty_grid();
						manage_draggable_users_fields();
					},
					get_grid_lists: function (number_of_grid) {
						var grid_lists = $('<div class="ur-grid-lists"/>');
						for (var i = 1; i <= number_of_grid; i++) {
							var grid_list_item = $('<div ur-grid-id=\'' + i + '\' class=\'ur-grid-list-item\'></div>');
							var width = Math.floor(100 / number_of_grid) - number_of_grid;
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

								$( '.ur-input-type-select2 .ur-field[data-field-key="select2"] select, .ur-input-type-multi-select2 .ur-field[data-field-key="multi_select2"] select' ).selectWoo();
							}
						});
					},
					manage_empty_grid: function () {
						var main_containner = $('.ur-input-grids');
						var drag_me = $('<div class="user-registration-dragged-me"/>');
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
							var total_rows = $( this ).attr( 'data-total-rows' );
							$( this ).attr( 'data-total-rows', parseInt( total_rows ) + 1 );

							var single_row_clone = $(this).closest('.ur-input-grids').find('.ur-single-row').eq(0).clone();
							single_row_clone.attr( 'data-row-id', parseInt( total_rows ) + 1 );
							single_row_clone.find('.ur-grid-lists').html('');
							single_row_clone.find('.ur-grids').find('span').removeClass('ur-active-grid');
							single_row_clone.find('.ur-grids').find('span[data-id="' + loaded_params.active_grid + '"]').addClass('ur-active-grid');
							var grid_list = builder.get_grid_lists(loaded_params.active_grid);
							single_row_clone.find('.ur-grid-lists').append(grid_list.html());
							single_row_clone.insertBefore('.ur-add-new-row');
							single_row_clone.show();
							$this_obj.render_draggable_sortable();
							builder.manage_empty_grid();
							$( document ).trigger( 'user_registration_row_added', [single_row_clone] );
						});
					},
					register_remove_row: function () {
						var $this = this;
						$('body').on('click', '.ur-remove-row', function () {
							if ($('.ur-input-grids').find('.ur-single-row:visible').length > 1) {
								var $this_row = $( this );
								ur_confirmation( i18n_admin.i18n_are_you_sure_want_to_delete, {
									confirm: function() {
										var btn = $this_row.prev();
										var new_btn;
										if (btn.hasClass('ur-add-new-row')) {
											new_btn = btn.clone();
										} else {
											new_btn = $this_row.clone().attr('class', 'dashicons-minus ur-remove-row');
										}
										if (new_btn.hasClass('ur-add-new-row')) {
											$this_row.closest('.ur-single-row').prev().find('.ur-remove-row').before(new_btn);
										}
										var single_row = $this_row.closest('.ur-single-row');
										$( document ).trigger( 'user_registration_row_deleted', [ single_row ] );
										single_row.remove();
										$this.check_grid();
										manage_draggable_users_fields();
										Swal.fire({
											type: 'success',
											title: 'Successfully deleted!',
											showConfirmButton: false,
											timer: 1000
										});
									},
								} );
							} else {
								ur_alert( i18n_admin.i18n_at_least_one_row_need_to_select )
							}
						});
					},
					change_ur_grids: function () {
						var $this_obj = this;

						$( document ).on( 'click', '.ur-grids .ur-edit-grid', function(e) {
							e.stopPropagation();
							$( this ).siblings( '.ur-toggle-grid-content' ).stop(true).slideToggle( 200 );
						} );
						$( document ).on( 'click', function() {
							$( '.ur-toggle-grid-content' ).stop(true).slideUp( 200 );
						} );

						$( document ).on( 'click', '.ur-grids .ur-toggle-grid-content .ur-grid-selector', function() {
							var $this_single_row = $( this ).closest( '.ur-single-row' ),
								grid_num = $( this ).attr( 'data-grid' ),
								$grids = builder.get_grid_lists(grid_num);

							// Prevent from selecting same grid.
							if( $this_single_row.find( '.ur-grid-lists .ur-grid-list-item' ).length === parseInt( grid_num ) ) {
								return;
							}

							$this_single_row.find( 'button.ur-edit-grid' ).html( $( this ).html() );

							$.each($this_single_row.find('.ur-grid-lists .ur-grid-list-item'), function () {
								$(this).children('*').each(function () {
									$grids.find('.ur-grid-list-item').eq(0).append($(this).clone());  // "this" is the current element in the loop
								});
							});
							$this_single_row.find('.ur-grid-lists').eq(0).hide();
							$grids.clone().insertAfter($this_single_row.find('.ur-grid-lists'));
							$this_single_row.find('.ur-grid-lists').eq(0).remove();
							$this_obj.render_draggable_sortable();
							builder.manage_empty_grid();
						} );
					},
					render_draggable_sortable: function () {
						$('.ur-grid-list-item').sortable({
							containment: '.ur-input-grids',
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
						$('.ur-input-grids').sortable({
							containment: '.ur-builder-wrapper',
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
						$('#ur-draggabled .draggable').draggable({
							connectToSortable: '.ur-grid-list-item',
							containment: '.ur-registered-from',
							helper: function() {
								return $( this ).clone().insertAfter( $( this ).closest( '.ur-tab-contents' ).siblings( '.ur-tab-lists' ) );
							},
							revert: 'invalid',
							// start: function (event, ui) {
							// },
							stop: function (event, ui) {
								if ($(ui.helper).closest('.ur-grid-list-item').length === 0) {
									return;
								}
								var data_field_id = $.trim($(ui.helper).attr('data-field-id').replace('user_registration_', ''));
								var length_of_required = $('.ur-input-grids').find('.ur-field[data-field-key="' + data_field_id + '"]').length;
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
							var selected_node = $('.ur-input-grids').find('.ur-field[data-field-key="' + data_field_key + '"]');
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
						$('.ur-tabs').find('a').eq(0).trigger('click', ['triggered_click']);
						$('.ur-tabs').find( '[aria-controls="ur-tab-field-options"]' ).addClass( "ur-no-pointer" );
						$('.ur-selected-item').removeClass('ur-item-active');
					}
				};
				builder.init();
				events.register();
			});
		};
		$('.ur-input-grids').ur_form_builder();
		$('.ur-tabs .ur-tab-lists').find('a.nav-tab').click(function () {
			$('.ur-tabs .ur-tab-lists').find('a.nav-tab').removeClass('active');
			$(this).addClass('active');
		});
		$('.ur-tabs').tabs();
		$('.ur-tabs').find('a').eq(0).trigger('click', ['triggered_click']);
		$('.ur-tabs').tabs({ disabled: [1] });

		/**
		 * This block of code is for the "Selected Countries" option of "Country" field
		 *
		 * Doc: https://select2.org/
		 * Ref: https://jsfiddle.net/Lkkm2L48/7/
		 */
		var SelectionAdapter, DropdownAdapter;
		$.fn.select2.amd.require([
			'select2/selection/single',
			'select2/selection/placeholder',
			'select2/dropdown',
			'select2/dropdown/search',
			'select2/dropdown/attachBody',
			'select2/utils',
			'select2/selection/eventRelay',
		], function (SingleSelection, Placeholder, Dropdown, DropdownSearch, AttachBody, Utils, EventRelay) {
			// Add placeholder which shows current number of selections
			SelectionAdapter = Utils.Decorate(
				SingleSelection,
				Placeholder
			);

			// Allow to flow/fire events
			SelectionAdapter = Utils.Decorate(
				SelectionAdapter,
				EventRelay
			);

			// Add search box in dropdown
			DropdownAdapter = Utils.Decorate(
				Dropdown,
				DropdownSearch
			);

			// Add attach-body in dropdown
			DropdownAdapter = Utils.Decorate(
				DropdownAdapter,
				AttachBody
			);

			/**
			 * Create UnSelectAll Adapter for unselect-all button
			 *
			 * Ref: http://jsbin.com/seqonozasu/1/edit?html,js,output
			 */
			function UnselectAll() {}
			UnselectAll.prototype.render = function ( decorated ) {
				var self = this;
				var $rendered = decorated.call( this );
				var $unSelectAllButton = $( '<button class="button button-secondary button-medium ur-unselect-all-countries-button" type="button">Unselect All</button>' );

				$unSelectAllButton.on( 'click', function() {
					self.$element.val( [] );
					self.$element.trigger( 'change' );
					self.trigger('close');
				});
				$rendered.find( '.select2-dropdown' ).prepend( $unSelectAllButton );

				return $rendered;
			};

			// Add unselect all button in dropdown
			DropdownAdapter = Utils.Decorate(
				DropdownAdapter,
				UnselectAll
			);

			/**
			 * Create SelectAll Adapter for select-all button
			 *
			 * Ref: http://jsbin.com/seqonozasu/1/edit?html,js,output
			 */
			function SelectAll() {}
			SelectAll.prototype.render = function ( decorated ) {
				var self = this;
				var $rendered = decorated.call( this );
				var $selectAllButton = $( '<button class="button button-secondary button-medium ur-select-all-countries-button" type="button">Select All</button>' );

				$selectAllButton.on( 'click', function() {
					var $options = self.$element.find( 'option' );
					var values = [];

					$options.each( function() {
						values.push( $(this).val() );
					})
					self.$element.val( values );
					self.$element.trigger( 'change' );
					self.trigger('close');
				});
				$rendered.find( '.select2-dropdown' ).prepend( $selectAllButton );

				return $rendered;
			};

			// Add select all button in dropdown
			DropdownAdapter = Utils.Decorate(
				DropdownAdapter,
				SelectAll
			);
		});

		$(document).on('click', '.ur-selected-item', function () {
			$('.ur-registered-inputs').find('ul li.ur-no-pointer').removeClass('ur-no-pointer');
			$('.ur-selected-item').removeClass('ur-item-active');
			$(this).addClass('ur-item-active');
			render_advance_setting($(this));
			init_events();
			$( document ).trigger( 'update_perfect_scrollbar' );

			var field_key = $(this).find('.ur-field').data('field-key');

			if ( 'country' === field_key || 'billing_country' === field_key || 'shipping_country' === field_key ) {
				/**
				 * Bind UI actions for `Selective Countries` feature
				 */
				var $selected_countries_option_field = $('#ur-setting-form select.ur-settings-selected-countries');
				$selected_countries_option_field.on('change', function ( e ) {
					var selected_countries_iso_s = $( this ).val();
					var html = '';
					var self = this;

					// Get html of selected countries
					if ( Array.isArray( selected_countries_iso_s ) ) {
						selected_countries_iso_s.forEach( function( iso ) {
							var country_name = $(self).find('option[value="' + iso + '"]').html();
							html += '<option value="' + iso + '">' + country_name + '</option>';
						});
					}

					// Update default_value options in `Field Options` tab
					$('#ur-setting-form select.ur-settings-default-value').html( html )

					// Update default_value options (hidden)
					$('.ur-selected-item.ur-item-active select.ur-settings-default-value').html( html )
				})
				.select2({
					placeholder: 'Select countries...',
					selectionAdapter: SelectionAdapter,
					dropdownAdapter: DropdownAdapter,
					templateResult: function ( data ) {

						if ( ! data.id ) {
							return data.text;
						}

						return $( '<div></div>' ).text( data.text ).addClass( 'wrap' );
					},
					templateSelection: function ( data ) {

						if ( ! data.id ) {
							return data.text;
						}
						var length = 0;

						if ( $selected_countries_option_field.val() ) {
							length = $selected_countries_option_field.val().length;
						}

						return "Selected " + length + " country(s)";
					},
				})
				/**
				 * The following block of code is required to fix the following issue:
				 * - When the dropdown is open, if the contents of this option's container changes, for example when a different field is
				 * activated, the behaviour of input tags changes. Specifically, when pressed space key inside ANY input tag, the dropdown
				 * APPEARS.
				 *
				 * P.S. The option we're talking about is `Selective Countries` for country field.
				 */
				.on( 'select2:close', function ( e ) {
					setTimeout( function() {
						$( ':focus' ).blur();
					}, 1 );
				});
			}
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
				$('.ur-tabs').find('a').eq(1).trigger('click', ['triggered_click']);
			}
			$('.ur-options-list').sortable({
				containment: '.ur-general-setting-options',
			});
		}

		$('.ur_import_form_action_button').on('click', function () {
			var file_data = $('#jsonfile').prop('files')[0];
			var form_data = new FormData();
			form_data.append('jsonfile', file_data);
			form_data.append('action', 'user_registration_import_form_action');
			form_data.append('security', user_registration_admin_data.ur_import_form_save);

			$.ajax({
				url: user_registration_admin_data.ajax_url,
				dataType: 'json',  // what to expect back from the PHP script, if anything
				cache: false,
				contentType: false,
				processData: false,
				data:form_data,
				type: 'post',
				beforeSend: function () {
					var spinner = '<span class="spinner is-active" style="float: left;margin-top: 6px;"></span>';
					$('.ur_import_form_action_button').closest('.publishing-action').append(spinner);
					$('.ur-import_notice').remove();
				},
				complete: function (response) {
					var message_string = '';

					$('.ur_import_form_action_button').closest('.publishing-action').find('.spinner').remove();
					$('.ur-import_notice').remove();

					if (response.responseJSON.success === true) {
						message_string = '<div id="message" class="updated inline ur-import_notice"><p><strong>' + response.responseJSON.data.message + '</strong></p></div>';
					} else {
						message_string = '<div id="message" class="error inline ur-import_notice"><p><strong>' + response.responseJSON.data.message + '</strong></p></div>';
					}

					$('.ur-export-users-page').prepend(message_string);
					$('#jsonfile').val("");
				}
			});
		});

		$('.ur_save_form_action_button').on('click', function () {
			ur_save_form();
		});

		/**
		 * For toggling quick links content.
		 */
		$( document.body ).on( 'click', '.ur-quick-links-content', function( e ) {
			e.stopPropagation();
		});
		$( document.body ).on( 'click', '.ur-button-quick-links', function( e ) {
			e.stopPropagation();
			$( '.ur-quick-links-content' ).slideToggle();
		});
		$( document.body ).on( 'click', function( e ) {
			if ( ! $( '.ur-quick-links-content' ).is( ':hidden' ) ) {
				$( '.ur-quick-links-content' ).slideToggle();
			}
		});

		$(window).on( 'keydown', function(event) {
			if (event.ctrlKey || event.metaKey) {
				if( 's' === String.fromCharCode(event.which).toLowerCase() ) {
					event.preventDefault();
					ur_save_form();
					return false;
				}
			}
		});
	});

	function ur_save_form() {
		var validation_response = get_validation_status();
		if (validation_response.validation_status === false) {
			show_message(validation_response.message);
			return;
		}

		var form_data = get_form_data();
		var form_row_ids = get_form_row_ids();
		var ur_form_id = $('#ur_form_id').val();
		var ur_form_id_localization = user_registration_admin_data.post_id;
		if (ur_parse_int(ur_form_id_localization, 0) !== ur_parse_int(ur_form_id, 0)) {
			ur_form_id = 0;
		}

		var form_setting_data = $('#ur-field-settings :not(.urcl-user-role-field)').serializeArray();

		var conditional_roles_settings_data = get_form_conditional_role_data();

		/** TODO:: Hanlde from multistep forms add-on if possible. */
		var multipart_page_setting = $('#ur-multi-part-page-settings').serializeArray();
		/** End Multistep form code. */

		var data = {
			action: 'user_registration_form_save_action',
			security: user_registration_admin_data.ur_form_save,
			data: {
				form_data: JSON.stringify(form_data),
				form_row_ids: JSON.stringify(form_row_ids),
				form_name: $('#ur-form-name').val(),
				form_id: ur_form_id,
				form_setting_data: form_setting_data,
				conditional_roles_settings_data: conditional_roles_settings_data,
				multipart_page_setting: multipart_page_setting,
			}
		};

		$.ajax({
			url: user_registration_admin_data.ajax_url,
			data: data,
			type: 'POST',
			beforeSend: function () {
				var spinner = '<span class="ur-spinner is-active"></span>';
				$('.ur_save_form_action_button').append(spinner);
				$('.ur-notices').remove();
			},
			complete: function (response) {
				$('.ur_save_form_action_button').find('.ur-spinner').remove();
				if (response.responseJSON.success === true) {
					var success_message = i18n_admin.i18n_form_successfully_saved;

					if ( user_registration_admin_data.is_edit_form !== '1' ) {
						var title = "Form successfully created."
						message_body = "<p>Want to create a login form as well? Check this <a target='_blank' href='https://docs.wpeverest.com/docs/user-registration/registration-form-and-login-form/how-to-show-login-form/'>link</a>. To know more about other cool features check our <a target='_blank' href='https://docs.wpeverest.com/docs/user-registration/'>docs</a>.</p>"
						Swal.fire({
							type: 'success',
							title: title,
							html: message_body,
						}).then( function( value ) {

							if( 0 === parseInt( ur_form_id ) ) {
								window.location = user_registration_admin_data.admin_url + response.responseJSON.data.post_id;
							}
						})
					} else {
						show_message(success_message, 'success');

						if( 0 === parseInt( ur_form_id ) ) {
							window.location = user_registration_admin_data.admin_url + response.responseJSON.data.post_id;
						}
					}
				} else {
					var error = response.responseJSON.data.message;
					show_message(error);
				}
			}
		});
	}

	$( document ).on( 'click', '.ur-message .ur-message-close', function() {
		$message = $( this ).closest( '.ur-message' );
		removeMessage( $message );
	} );

	function show_message(message, type) {
		var $message_container = $( '.ur-form-container' ).find( '.ur-builder-message-container' ),
			$admin_bar = $( '#wpadminbar' ),
			message_string = '';

		if( 0 === $message_container.length ) {
			$( '.ur-form-container' ).append( '<div class="ur-builder-message-container"></div>' );
			$message_container = $( '.ur-form-container' ).find( '.ur-builder-message-container' );
			$message_container.css( { 'top' : $admin_bar.height() + 'px' } );
		}

		if( 'success' === type ) {
			message_string = '<div class="ur-message"><div class="ur-success"><p><strong>' + i18n_admin.i18n_success + '! </strong>' + message + '</p><span class="dashicons dashicons-no-alt ur-message-close"></span></div></div>';
		} else {
			message_string = '<div class="ur-message"><div class="ur-error"><p><strong>' + i18n_admin.i18n_error + '! </strong>' + message + '</p><span class="dashicons dashicons-no-alt ur-message-close"></span></div></div>';
		}

		var $message = $( message_string ).prependTo( $message_container );
		setTimeout( function( ) {
			$message.addClass( 'entered' );
		}, 50 );

		setTimeout( function( ) {
			removeMessage( $message );
		}, 2000 );
	}

	function removeMessage( $message ) {
		$message.removeClass( 'entered' ).addClass( 'exiting' );
		setTimeout( function() {
			$message.remove();
		}, 120 );
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
		if ($('.ur_save_form_action_button').find('.ur-spinner').length > 0) {
			response.validation_status = false;
			response.message = i18n_admin.i18n_previous_save_action_ongoing;
			return response;
		}
		$.each($( '.ur-selected-item select.ur-settings-selected-countries' ), function () {
			var selected_countries = $( this ).val();
			if (
				! selected_countries ||
				( Array.isArray( selected_countries ) && selected_countries.length === 0 )
			) {
				response.validation_status = false;
				response.message = i18n_admin.i18n_select_countries;
				return response;
			}
		});
		$.each($('.ur-input-grids .ur-general-setting-block input[data-field="field_name"]'), function () {
			var $field = $(this);
			var need_to_break = false;
			var field_attribute;
			try {
				var field_value = $field.val();
				var length = $('.ur-input-grids .ur-general-setting-block').find('input[data-field="field_name"][value="' + field_value + '"]').length;
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
			if ($('.ur-input-grids').find('.ur-field[data-field-key="' + only_one_field_index[single_field] + '"]').length > 1) {
				response.validation_status = false;
				response.message = i18n_admin.i18n_multiple_field_key + only_one_field_index[single_field];
				break;
			}
		}
		for (var required_index = 0; required_index < required_fields.length; required_index++) {

			if ($('.ur-input-grids').find('.ur-field[data-field-key="' + required_fields[required_index] + '"]').length === 0) {
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

	function get_form_conditional_role_data() {
		var form_data = [];
		var single_row = $('.urcl-role-logic-wrap');

		$.each(single_row, function () {
			var grid_list_item = $(this).find('.urcl-user-role-field');
			var all_field_data = [];
			var or_field_data = [];
			var assign_role = '';
			$.each(grid_list_item, function () {
				$field_key = $(this).attr('name').split('[');

				if ( 'user_registration_form_conditional_user_role' === $field_key[0] ) {
					assign_role =  $(this).val();
					grid_list_item.splice( $(this) , 1);
				}
			});

			var conditional_group = $(this).find('.urcl-conditional-group');
			$.each(conditional_group, function () {
				var inner_conditions = [];
				var grid_list_item = $(this).find('.urcl-user-role-field');
				$.each(grid_list_item, function () {
					var conditions = {
						field_key:  $(this).attr('name'),
						field_value:  $(this).val(),
					};
					inner_conditions.push( conditions );
				});
				all_field_data.push( inner_conditions );
			});

			var or_groups = $(this).find('.urcl-or-groups');
			$.each(or_groups, function () {
				var conditional_or_group = $(this).find('.urcl-conditional-or-group');
				var or_data = [];
				$.each(conditional_or_group, function () {
					var inner_or_conditions = [];
					var or_list_item = $(this).find('.urcl-user-role-field');
					$.each(or_list_item, function () {
						var or_conditions = {
							field_key:  $(this).attr('name'),
							field_value:  $(this).val(),
						};
						inner_or_conditions.push( or_conditions );
					});
					or_data.push( inner_or_conditions );
				});
				or_field_data.push( or_data );
			});
			var all_fields = {
				assign_role:  assign_role,
				conditions:  all_field_data,
				or_conditions:  or_field_data,
			};
			form_data.push(all_fields);
		});
		return form_data;
	}

	function get_form_data() {
		var form_data = [];
		var single_row = $('.ur-input-grids .ur-single-row');
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

	function get_form_row_ids() {
		var row_ids = [];
		var single_row = $('.ur-input-grids .ur-single-row');
		$.each(single_row, function () {
			row_ids.push($(this).attr( 'data-row-id' ));
		});
		return row_ids;
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

			var is_checkbox = $(this).closest('.ur-general-setting').hasClass('ur-setting-checkbox');

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

				} else if ( 'html' === $(this).attr('data-field') ) {
					general_setting_data[$(this).attr('data-field')] = get_ur_data($(this)).replace(/"/g, "'");
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

		$('.ur-settings-enable-min-max').on('change', function () {
			if('true' === $(this).val()){
				$('.ur-advance-min_date').show();
				$('.ur-advance-max_date').show();
				if('' === $('.ur-settings-min-date').val()){
					$('.ur-settings-min-date').addClass('flatpickr-field').flatpickr({
						disableMobile : true,
						static        : true,
						onChange      : function(selectedDates, dateStr, instance) {
							$('.ur-settings-min-date').val(dateStr);
						},
						onOpen: function(selectedDates, dateStr, instance) {
							instance.set('maxDate', new Date($('.ur-settings-max-date').val()));
						},
					});
				}
				if('' === $('.ur-settings-max-date').val()){
					$('.ur-settings-max-date').addClass('flatpickr-field').flatpickr({
						disableMobile : true,
						static        : true,
						onChange      : function(selectedDates, dateStr, instance) {
							$('.ur-settings-max-date').val(dateStr);
						},
						onOpen: function(selectedDates, dateStr, instance) {
							instance.set('minDate', new Date($('.ur-settings-min-date').val()));
						},
					});
				}

			}else{
				$('.ur-advance-min_date').hide();
				$('.ur-advance-max_date').hide();
				$('.ur-settings-min-date').val('');
				$('.ur-settings-max-date').val('');
			}
		});

		$.each(advance_settings, function () {
			var $this_node = $(this);
			switch ($this_node.attr('data-advance-field')) {
				case 'date_format':
					$this_node.on('change', function () {
						trigger_general_setting_date_format($(this));
					});
					break;
				case 'min_date':
					if('true' === $('.ur-settings-enable-min-max').val()){
						$(this).addClass('flatpickr-field').flatpickr({
							disableMobile : true,
							static        : true,
							defaultDate   : new Date($('.ur-settings-min-date').val()),
							onChange      : function(selectedDates, dateStr, instance) {
								$('.ur-settings-min-date').val(dateStr);
							},
							onOpen: function(selectedDates, dateStr, instance) {
								instance.set('maxDate', new Date($('.ur-settings-max-date').val()));
							},
						});
					}else{
						$('.ur-advance-min_date').hide();
						$('.ur-settings-min-date').val('');
					}
					break;
				case 'max_date':
					if('true' === $('.ur-settings-enable-min-max').val()){
						$(this).addClass('flatpickr-field').flatpickr({
							disableMobile : true,
							static        : true,
							defaultDate   : new Date($('.ur-settings-max-date').val()),
							onChange      : function(selectedDates, dateStr, instance) {
								$('.ur-settings-max-date').val(dateStr);
							},
							onOpen: function(selectedDates, dateStr, instance) {
								instance.set('minDate', new Date($('.ur-settings-min-date').val()));
							},
						});
					}else{
						$('.ur-advance-max_date').hide();
						$('.ur-settings-max-date').val('');
					}
					break;
			}
			var node_type = $this_node.get(0).tagName.toLowerCase();

			if( 'country_advance_setting_default_value' === $this_node.attr('data-id') ){
				$('.ur-builder-wrapper #ur-input-type-country').find('option[value="' + $this_node.val() + '"]').attr('selected', 'selected');
			}
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
				hidden_node.find('option').removeAttr('selected');

				if ( $this_node.prop('multiple') ) {
					var selected_options = $this_node.val();

					if ( Array.isArray( selected_options ) ) {
						selected_options.forEach( function( value ) {
							hidden_node.find( 'option[value="' + value + '"]' ).attr( 'selected', 'selected' );
						});
					}
				} else {
					hidden_node.find('option[value="' + $this_node.val() + '"]').attr( 'selected', 'selected' );
				}
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

	function trigger_general_setting_date_format($label){
		var wrapper = $('.ur-selected-item.ur-item-active');
		wrapper.find('.ur-field').find('input').attr('placeholder', $label.val());
	}

	function trigger_general_setting_hide_label($label) {
		var wrapper = $('.ur-selected-item.ur-item-active');
		wrapper.find('.ur-label').find('label').find('span').remove();
		wrapper.find('.ur-general-setting-block').find('select[data-field="' + $label.attr('data-field') + '"]').find('option[value="' + $label.val() + '"]').attr('selected', 'selected');
	}

	function manage_required_fields() {
		var required_fields = user_registration_admin_data.form_required_fields;

		var selected_inputs = $('.ur-input-grids');

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

				if ($('.ur-input-grids').find('.ur-field[data-field-key="' + data_field_id + '"]').length > 0) {
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

	$(document).ready(function () {

		var flatpickr_loaded = false;

		$('#load_flatpickr').click( function() {
			var date_selector = $('#profile-page form#your-profile  input[type="date"]');
			date_selector.attr('type', 'text');
			date_selector.val( $('#formated_date').val() );

			var date_field = date_selector.attr('id');
			var date_flatpickr;

			if ( ! flatpickr_loaded ) {
				$(this).attr('data-date-format', date_selector.data('date-format'));
				$(this).attr('data-mode', date_selector.data('mode'));
				$(this).attr('data-min-date', date_selector.data('min-date'));
				$(this).attr('data-max-date', date_selector.data('max-date'));
				$(this).attr('data-default-date', $('#formated_date').val());
				date_flatpickr = $(this).flatpickr({
					disableMobile: true,
					onChange      : function(selectedDates, dateStr, instance) {
						$('#'+ date_field).val(dateStr);
					},
				});

				flatpickr_loaded = true;
			}

			if ( date_flatpickr ) {
				date_flatpickr.open();
			}
		});
	});


	$(document).on('click', '.ur-toggle-heading', function () {

		if ($(this).hasClass('closed')) {
			$(this).removeClass('closed');
		} else {
			$(this).addClass('closed');
		}
		var field_list = $(this).find(' ~ .ur-registered-list')[0];
		$(field_list).slideToggle();

		// For `Field Options` section
		if ( $( this ).parent().is( '.ur-general-setting-block' ) || $( this ).parent().is( '.ur-advance-setting-block' ) ) {
			$( this ).siblings( 'div' ).slideToggle();
		}
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

function ur_alert( message, options ) {
	if( 'undefined' === typeof options ) {
		options = {};
	}
	Swal.fire({
		type: 'error',
		title: options.title,
		text: message,
	});
}

function ur_confirmation( message, options ) {
	if( 'undefined' === typeof options ) {
		options = {};
	}
	Swal.fire({
		title: options.title,
		text: message,
		type: ( 'undefined' !== typeof options.type ) ? options.type : 'warning',
		showCancelButton: ( 'undefined' !== typeof options.showCancelButton ) ? options.showCancelButton : true,
		confirmButtonText: ( 'undefined' !== typeof options.confirmButtonText ) ? options.confirmButtonText : 'OK',
		cancelButtonText: ( 'undefined' !== typeof options.cancelButtonText ) ? options.cancelButtonText :'Cancel',
	}).then( function(result) {
		if (result.value) {
			options.confirm();
		} else {
			options.reject();
		}
	});
}
