/*global jQuery, Backbone, _ */
( function ( $, Backbone, _ ) {
	'use strict';

	/**
	 * UserRegistration Backbone Modal plugin
	 *
	 * @param {object} options
	 */
	$.fn.URBackboneModal = function ( options ) {
		return this.each(function () {
			( new $.URBackboneModal($(this), options) );
		});
	};

	/**
	 * Initialize the Backbone Modal
	 *
	 * @param {object} element [description]
	 * @param {object} options [description]
	 */
	$.URBackboneModal = function ( element, options ) {
		// Set settings
		var settings = $.extend({}, $.URBackboneModal.defaultOptions, options);

		if ( settings.template ) {
			new $.URBackboneModal.View({
				target: settings.template,
				string: settings.variable
			});
		}
	};

	/**
	 * Set default options
	 *
	 * @type {object}
	 */
	$.URBackboneModal.defaultOptions = {
		template: '',
		variable: {}
	};

	/**
	 * Create the Backbone Modal
	 *
	 * @return {null}
	 */
	$.URBackboneModal.View = Backbone.View.extend({
		tagName: 'div',
		id: 'ur-backbone-modal-dialog',
		_target: undefined,
		_string: undefined,
		events: {
			'click .modal-close': 'closeButton',
			'click #btn-ok': 'addButton',
			'touchstart #btn-ok': 'addButton',
			'keydown': 'keyboardActions'
		},
		resizeContent: function () {
			var $content = $('.ur-backbone-modal-content').find('article');
			var max_h = $(window).height() * 0.75;

			$content.css({
				'max-height': max_h + 'px'
			});
		},
		initialize: function ( data ) {
			var view = this;
			this._target = data.target;
			this._string = data.string;
			_.bindAll(this, 'render');
			this.render();

			$(window).resize(function () {
				view.resizeContent();
			});
		},
		render: function () {
			var template = wp.template(this._target);

			this.$el.attr('tabindex', '0').append(
				template(this._string)
			);

			$(document.body).css({
				'overflow': 'hidden'
			}).append(this.$el);

			this.resizeContent();
			this.$el.focus();
			$(document.body).trigger('init_tooltips');

			$(document.body).trigger('ur_backbone_modal_loaded', this._target);
		},
		closeButton: function ( e ) {
			e.preventDefault();
			$(document.body).trigger('ur_backbone_modal_before_remove', this._target);
			this.undelegateEvents();
			$(document).off('focusin');
			$(document.body).css({
				'overflow': 'auto'
			});
			this.remove();
			$(document.body).trigger('ur_backbone_modal_removed', this._target);
		},
		addButton: function ( e ) {
			$(document.body).trigger('ur_backbone_modal_response', [ this._target, this.getFormData() ]);
			this.closeButton(e);
		},
		getFormData: function () {
			var data = {};

			$(document.body).trigger('ur_backbone_modal_before_update', this._target);

			$.each($('form', this.$el).serializeArray(), function ( index, item ) {
				if ( item.name.indexOf('[]') !== -1 ) {
					item.name = item.name.replace('[]', '');
					data[ item.name ] = $.makeArray(data[ item.name ]);
					data[ item.name ].push(item.value);
				} else {
					data[ item.name ] = item.value;
				}
			});

			return data;
		},
		keyboardActions: function ( e ) {
			var button = e.keyCode || e.which;

			// Enter key
			if ( 13 === button && !( e.target.tagName && ( e.target.tagName.toLowerCase() === 'input' || e.target.tagName.toLowerCase() === 'textarea' ) ) ) {
				this.addButton(e);
			}

			// ESC key
			if ( 27 === button ) {
				this.closeButton(e);
			}
		}
	});

}(jQuery, Backbone, _));
