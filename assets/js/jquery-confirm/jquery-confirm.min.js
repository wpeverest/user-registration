/*!
 * jquery-confirm v3.3.4 (http://craftpip.github.io/jquery-confirm/)
 * Author: Boniface Pereira
 * Website: www.craftpip.com
 * Contact: hey@craftpip.com
 *
 * Copyright 2013-2019 jquery-confirm
 * Licensed under MIT (https://github.com/craftpip/jquery-confirm/blob/master/LICENSE)
 */ !(function (a) {
	"function" == typeof define && define.amd
		? define(["jquery"], a)
		: "object" == typeof module && module.exports
		? (module.exports = function (c, b) {
				return (
					void 0 === b &&
						(b =
							"undefined" != typeof window
								? require("jquery")
								: require("jquery")(c)),
					a(b),
					b
				);
		  })
		: a(jQuery);
})(function ($) {
	"use strict";
	var a = window;
	($.fn.confirm = function (b, c) {
		return (
			void 0 === b && (b = {}),
			"string" == typeof b && (b = { content: b, title: !!c && c }),
			$(this).each(function () {
				var c = $(this);
				if (c.attr("jc-attached")) {
					console.warn(
						"jConfirm has already been attached to this element ",
						c[0]
					);
					return;
				}
				c.on("click", function (f) {
					f.preventDefault();
					var d = $.extend({}, b);
					if (
						(c.attr("data-title") &&
							(d.title = c.attr("data-title")),
						c.attr("data-content") &&
							(d.content = c.attr("data-content")),
						void 0 === d.buttons && (d.buttons = {}),
						(d.$target = c),
						c.attr("href") && 0 === Object.keys(d.buttons).length)
					) {
						var e = $.extend(
								!0,
								{},
								a.jconfirm.pluginDefaults.defaultButtons,
								(a.jconfirm.defaults || {}).defaultButtons || {}
							),
							g = Object.keys(e)[0];
						(d.buttons = e),
							(d.buttons[g].action = function () {
								location.href = c.attr("href");
							});
					}
					(d.closeIcon = !1), $.confirm(d);
				}),
					c.attr("jc-attached", !0);
			}),
			$(this)
		);
	}),
		($.confirm = function (b, c) {
			void 0 === b && (b = {}),
				"string" == typeof b && (b = { content: b, title: !!c && c });
			var d = !1 !== b.buttons;
			if (
				("object" != typeof b.buttons && (b.buttons = {}),
				0 === Object.keys(b.buttons).length && d)
			) {
				var e = $.extend(
					!0,
					{},
					a.jconfirm.pluginDefaults.defaultButtons,
					(a.jconfirm.defaults || {}).defaultButtons || {}
				);
				b.buttons = e;
			}
			return a.jconfirm(b);
		}),
		($.alert = function (b, c) {
			void 0 === b && (b = {}),
				"string" == typeof b && (b = { content: b, title: !!c && c });
			var f = !1 !== b.buttons;
			if (
				("object" != typeof b.buttons && (b.buttons = {}),
				0 === Object.keys(b.buttons).length && f)
			) {
				var d = $.extend(
						!0,
						{},
						a.jconfirm.pluginDefaults.defaultButtons,
						(a.jconfirm.defaults || {}).defaultButtons || {}
					),
					e = Object.keys(d)[0];
				b.buttons[e] = d[e];
			}
			return a.jconfirm(b);
		}),
		($.dialog = function (b, c) {
			return (
				void 0 === b && (b = {}),
				"string" == typeof b &&
					(b = {
						content: b,
						title: !!c && c,
						closeIcon: function () {},
					}),
				(b.buttons = {}),
				void 0 === b.closeIcon && (b.closeIcon = function () {}),
				(b.confirmKeys = [13]),
				a.jconfirm(b)
			);
		}),
		(a.jconfirm = function (c) {
			void 0 === c && (c = {});
			var b = $.extend(!0, {}, a.jconfirm.pluginDefaults);
			a.jconfirm.defaults && (b = $.extend(!0, b, a.jconfirm.defaults)),
				(b = $.extend(!0, {}, b, c));
			var d = new a.Jconfirm(b);
			return a.jconfirm.instances.push(d), d;
		}),
		(a.Jconfirm = function (a) {
			$.extend(this, a), this._init();
		}),
		(a.Jconfirm.prototype = {
			_init: function () {
				var b = this;
				a.jconfirm.instances.length ||
					(a.jconfirm.lastFocused = $("body").find(":focus")),
					(this._id = Math.round(99999 * Math.random())),
					(this.contentParsed = $(document.createElement("div"))),
					this.lazyOpen ||
						setTimeout(function () {
							b.open();
						}, 0);
			},
			_buildHTML: function () {
				var c = this;
				this._parseAnimation(this.animation, "o"),
					this._parseAnimation(this.closeAnimation, "c"),
					this._parseBgDismissAnimation(
						this.backgroundDismissAnimation
					),
					this._parseColumnClass(this.columnClass),
					this._parseTheme(this.theme),
					this._parseType(this.type);
				var a = $(this.template);
				a
					.find(".jconfirm-box")
					.addClass(this.animationParsed)
					.addClass(this.backgroundDismissAnimationParsed)
					.addClass(this.typeParsed),
					this.typeAnimated &&
						a
							.find(".jconfirm-box")
							.addClass("jconfirm-type-animated"),
					this.useBootstrap
						? (a
								.find(".jc-bs3-row")
								.addClass(this.bootstrapClasses.row),
						  a
								.find(".jc-bs3-row")
								.addClass(
									"justify-content-md-center justify-content-sm-center justify-content-xs-center justify-content-lg-center"
								),
						  a
								.find(".jconfirm-box-container")
								.addClass(this.columnClassParsed),
						  this.containerFluid
								? a
										.find(".jc-bs3-container")
										.addClass(
											this.bootstrapClasses.containerFluid
										)
								: a
										.find(".jc-bs3-container")
										.addClass(
											this.bootstrapClasses.container
										))
						: a.find(".jconfirm-box").css("width", this.boxWidth),
					this.titleClass &&
						a.find(".jconfirm-title-c").addClass(this.titleClass),
					a.addClass(this.themeParsed);
				var b = "jconfirm-box" + this._id;
				a
					.find(".jconfirm-box")
					.attr("aria-labelledby", b)
					.attr("tabindex", -1),
					a.find(".jconfirm-content").attr("id", b),
					null !== this.bgOpacity &&
						a.find(".jconfirm-bg").css("opacity", this.bgOpacity),
					this.rtl && a.addClass("jconfirm-rtl"),
					(this.$el = a.appendTo(this.container)),
					(this.$jconfirmBoxContainer = this.$el.find(
						".jconfirm-box-container"
					)),
					(this.$jconfirmBox = this.$body =
						this.$el.find(".jconfirm-box")),
					(this.$jconfirmBg = this.$el.find(".jconfirm-bg")),
					(this.$title = this.$el.find(".jconfirm-title")),
					(this.$titleContainer = this.$el.find(".jconfirm-title-c")),
					(this.$content = this.$el.find("div.jconfirm-content")),
					(this.$contentPane = this.$el.find(
						".jconfirm-content-pane"
					)),
					(this.$icon = this.$el.find(".jconfirm-icon-c")),
					(this.$closeIcon = this.$el.find(".jconfirm-closeIcon")),
					(this.$holder = this.$el.find(".jconfirm-holder")),
					(this.$btnc = this.$el.find(".jconfirm-buttons")),
					(this.$scrollPane = this.$el.find(".jconfirm-scrollpane")),
					c.setStartingPoint(),
					(this._contentReady = $.Deferred()),
					(this._modalReady = $.Deferred()),
					this.$holder.css({
						"padding-top": this.offsetTop,
						"padding-bottom": this.offsetBottom,
					}),
					this.setTitle(),
					this.setIcon(),
					this._setButtons(),
					this._parseContent(),
					this.initDraggable(),
					this.isAjax && this.showLoading(!1),
					$.when(this._contentReady, this._modalReady)
						.then(function () {
							c.isAjaxLoading
								? setTimeout(function () {
										(c.isAjaxLoading = !1),
											c.setContent(),
											c.setTitle(),
											c.setIcon(),
											setTimeout(function () {
												c.hideLoading(!1),
													c._updateContentMaxHeight();
											}, 100),
											"function" ==
												typeof c.onContentReady &&
												c.onContentReady();
								  }, 50)
								: (c._updateContentMaxHeight(),
								  c.setTitle(),
								  c.setIcon(),
								  "function" == typeof c.onContentReady &&
										c.onContentReady()),
								c.autoClose && c._startCountDown();
						})
						.then(function () {
							c._watchContent();
						}),
					"none" === this.animation &&
						((this.animationSpeed = 1), (this.animationBounce = 1)),
					this.$body.css(
						this._getCSS(this.animationSpeed, this.animationBounce)
					),
					this.$contentPane.css(this._getCSS(this.animationSpeed, 1)),
					this.$jconfirmBg.css(this._getCSS(this.animationSpeed, 1)),
					this.$jconfirmBoxContainer.css(
						this._getCSS(this.animationSpeed, 1)
					);
			},
			_typePrefix: "jconfirm-type-",
			typeParsed: "",
			_parseType: function (a) {
				this.typeParsed = this._typePrefix + a;
			},
			setType: function (a) {
				var b = this.typeParsed;
				this._parseType(a),
					this.$jconfirmBox.removeClass(b).addClass(this.typeParsed);
			},
			themeParsed: "",
			_themePrefix: "jconfirm-",
			setTheme: function (a) {
				var b = this.theme;
				(this.theme = a || this.theme),
					this._parseTheme(this.theme),
					b && this.$el.removeClass(b),
					this.$el.addClass(this.themeParsed),
					(this.theme = a);
			},
			_parseTheme: function (a) {
				var b = this;
				(a = a.split(",")),
					$.each(a, function (d, c) {
						-1 === c.indexOf(b._themePrefix) &&
							(a[d] = b._themePrefix + $.trim(c));
					}),
					(this.themeParsed = a.join(" ").toLowerCase());
			},
			backgroundDismissAnimationParsed: "",
			_bgDismissPrefix: "jconfirm-hilight-",
			_parseBgDismissAnimation: function (b) {
				var a = b.split(","),
					c = this;
				$.each(a, function (d, b) {
					-1 === b.indexOf(c._bgDismissPrefix) &&
						(a[d] = c._bgDismissPrefix + $.trim(b));
				}),
					(this.backgroundDismissAnimationParsed = a
						.join(" ")
						.toLowerCase());
			},
			animationParsed: "",
			closeAnimationParsed: "",
			_animationPrefix: "jconfirm-animation-",
			setAnimation: function (a) {
				(this.animation = a || this.animation),
					this._parseAnimation(this.animation, "o");
			},
			_parseAnimation: function (d, a) {
				a = a || "o";
				var c = d.split(","),
					e = this;
				$.each(c, function (b, a) {
					-1 === a.indexOf(e._animationPrefix) &&
						(c[b] = e._animationPrefix + $.trim(a));
				});
				var b = c.join(" ").toLowerCase();
				return (
					"o" === a
						? (this.animationParsed = b)
						: (this.closeAnimationParsed = b),
					b
				);
			},
			setCloseAnimation: function (a) {
				(this.closeAnimation = a || this.closeAnimation),
					this._parseAnimation(this.closeAnimation, "c");
			},
			setAnimationSpeed: function (a) {
				this.animationSpeed = a || this.animationSpeed;
			},
			columnClassParsed: "",
			setColumnClass: function (a) {
				if (!this.useBootstrap) {
					console.warn(
						"cannot set columnClass, useBootstrap is set to false"
					);
					return;
				}
				(this.columnClass = a || this.columnClass),
					this._parseColumnClass(this.columnClass),
					this.$jconfirmBoxContainer.addClass(this.columnClassParsed);
			},
			_updateContentMaxHeight: function () {
				var a =
					$(window).height() -
					(this.$jconfirmBox.outerHeight() -
						this.$contentPane.outerHeight()) -
					(this.offsetTop + this.offsetBottom);
				this.$contentPane.css({ "max-height": a + "px" });
			},
			setBoxWidth: function (a) {
				if (this.useBootstrap) {
					console.warn(
						"cannot set boxWidth, useBootstrap is set to true"
					);
					return;
				}
				(this.boxWidth = a), this.$jconfirmBox.css("width", a);
			},
			_parseColumnClass: function (b) {
				var a;
				switch ((b = b.toLowerCase())) {
					case "xl":
					case "xlarge":
						a = "col-md-12";
						break;
					case "l":
					case "large":
						a = "col-md-8 col-md-offset-2";
						break;
					case "m":
					case "medium":
						a = "col-md-6 col-md-offset-3";
						break;
					case "s":
					case "small":
						a = "col-md-4 col-md-offset-4";
						break;
					case "xs":
					case "xsmall":
						a = "col-md-2 col-md-offset-5";
						break;
					default:
						a = b;
				}
				this.columnClassParsed = a;
			},
			initDraggable: function () {
				var b = this,
					a = this.$titleContainer;
				this.resetDrag(),
					this.draggable &&
						(a.on("mousedown", function (c) {
							a.addClass("jconfirm-hand"),
								(b.mouseX = c.clientX),
								(b.mouseY = c.clientY),
								(b.isDrag = !0);
						}),
						$(window).on("mousemove." + this._id, function (a) {
							b.isDrag &&
								((b.movingX =
									a.clientX - b.mouseX + b.initialX),
								(b.movingY = a.clientY - b.mouseY + b.initialY),
								b.setDrag());
						}),
						$(window).on("mouseup." + this._id, function () {
							a.removeClass("jconfirm-hand"),
								b.isDrag &&
									((b.isDrag = !1),
									(b.initialX = b.movingX),
									(b.initialY = b.movingY));
						}));
			},
			resetDrag: function () {
				(this.isDrag = !1),
					(this.initialX = 0),
					(this.initialY = 0),
					(this.movingX = 0),
					(this.movingY = 0),
					(this.mouseX = 0),
					(this.mouseY = 0),
					this.$jconfirmBoxContainer.css(
						"transform",
						"translate(0px, 0px)"
					);
			},
			setDrag: function () {
				if (this.draggable) {
					this.alignMiddle = !1;
					var e = this.$jconfirmBox.outerWidth(),
						f = this.$jconfirmBox.outerHeight(),
						g = $(window).width(),
						h = $(window).height(),
						a = this,
						d = 1;
					if (a.movingX % d == 0 || a.movingY % d == 0) {
						if (a.dragWindowBorder) {
							var b = g / 2 - e / 2,
								c = h / 2 - f / 2;
							(c -= a.dragWindowGap),
								(b -= a.dragWindowGap),
								b + a.movingX < 0
									? (a.movingX = -b)
									: b - a.movingX < 0 && (a.movingX = b),
								c + a.movingY < 0
									? (a.movingY = -c)
									: c - a.movingY < 0 && (a.movingY = c);
						}
						a.$jconfirmBoxContainer.css(
							"transform",
							"translate(" +
								a.movingX +
								"px, " +
								a.movingY +
								"px)"
						);
					}
				}
			},
			_scrollTop: function () {
				if ("undefined" != typeof pageYOffset) return pageYOffset;
				var b = document.body,
					a = document.documentElement;
				return (a = a.clientHeight ? a : b).scrollTop;
			},
			_watchContent: function () {
				var a = this;
				this._timer && clearInterval(this._timer);
				var b = 0;
				this._timer = setInterval(function () {
					if (a.smoothContent) {
						var c = a.$content.outerHeight() || 0;
						c !== b && (b = c);
						var d = $(window).height();
						a.offsetTop +
							a.offsetBottom +
							a.$jconfirmBox.height() -
							a.$contentPane.height() +
							a.$content.height() <
						d
							? a.$contentPane.addClass("no-scroll")
							: a.$contentPane.removeClass("no-scroll");
					}
				}, this.watchInterval);
			},
			_overflowClass: "jconfirm-overflow",
			_hilightAnimating: !1,
			highlight: function () {
				this.hiLightModal();
			},
			hiLightModal: function () {
				var a = this;
				if (!this._hilightAnimating) {
					a.$body.addClass("hilight");
					var b = parseFloat(a.$body.css("animation-duration")) || 2;
					(this._hilightAnimating = !0),
						setTimeout(function () {
							(a._hilightAnimating = !1),
								a.$body.removeClass("hilight");
						}, 1e3 * b);
				}
			},
			_bindEvents: function () {
				var a = this;
				(this.boxClicked = !1),
					this.$scrollPane.click(function (f) {
						if (!a.boxClicked) {
							var b,
								d = !1,
								c = !1;
							if (
								("string" ==
									typeof (b =
										"function" == typeof a.backgroundDismiss
											? a.backgroundDismiss()
											: a.backgroundDismiss) &&
								void 0 !== a.buttons[b]
									? ((d = b), (c = !1))
									: (c = void 0 === b || !0 == !!b),
								d)
							) {
								var e = a.buttons[d].action.apply(a);
								c = void 0 === e || !!e;
							}
							c ? a.close() : a.hiLightModal();
						}
						a.boxClicked = !1;
					}),
					this.$jconfirmBox.click(function (b) {
						a.boxClicked = !0;
					});
				var b = !1;
				$(window).on("jcKeyDown." + a._id, function (a) {
					b || (b = !0);
				}),
					$(window).on("keyup." + a._id, function (c) {
						b && (a.reactOnKey(c), (b = !1));
					}),
					$(window).on("resize." + this._id, function () {
						a._updateContentMaxHeight(),
							setTimeout(function () {
								a.resetDrag();
							}, 100);
					});
			},
			_cubic_bezier: "0.36, 0.55, 0.19",
			_getCSS: function (a, b) {
				return {
					"-webkit-transition-duration": a / 1e3 + "s",
					"transition-duration": a / 1e3 + "s",
					"-webkit-transition-timing-function":
						"cubic-bezier(" + this._cubic_bezier + ", " + b + ")",
					"transition-timing-function":
						"cubic-bezier(" + this._cubic_bezier + ", " + b + ")",
				};
			},
			_setButtons: function () {
				var c = this,
					a = 0;
				if (
					("object" != typeof this.buttons && (this.buttons = {}),
					$.each(this.buttons, function (b, d) {
						(a += 1),
							"function" == typeof d &&
								(c.buttons[b] = d = { action: d }),
							(c.buttons[b].text = d.text || b),
							(c.buttons[b].btnClass =
								d.btnClass || "btn-default"),
							(c.buttons[b].action = d.action || function () {}),
							(c.buttons[b].keys = d.keys || []),
							(c.buttons[b].isHidden = d.isHidden || !1),
							(c.buttons[b].isDisabled = d.isDisabled || !1),
							$.each(c.buttons[b].keys, function (a, d) {
								c.buttons[b].keys[a] = d.toLowerCase();
							});
						var e = $('<button type="button" class="btn"></button>')
							.html(c.buttons[b].text)
							.addClass(c.buttons[b].btnClass)
							.prop("disabled", c.buttons[b].isDisabled)
							.css("display", c.buttons[b].isHidden ? "none" : "")
							.click(function (d) {
								d.preventDefault();
								var a = c.buttons[b].action.apply(c, [
									c.buttons[b],
								]);
								c.onAction.apply(c, [b, c.buttons[b]]),
									c._stopCountDown(),
									(void 0 === a || a) && c.close();
							});
						(c.buttons[b].el = e),
							(c.buttons[b].setText = function (a) {
								e.html(a);
							}),
							(c.buttons[b].addClass = function (a) {
								e.addClass(a);
							}),
							(c.buttons[b].removeClass = function (a) {
								e.removeClass(a);
							}),
							(c.buttons[b].disable = function () {
								(c.buttons[b].isDisabled = !0),
									e.prop("disabled", !0);
							}),
							(c.buttons[b].enable = function () {
								(c.buttons[b].isDisabled = !1),
									e.prop("disabled", !1);
							}),
							(c.buttons[b].show = function () {
								(c.buttons[b].isHidden = !1),
									e.css("display", "");
							}),
							(c.buttons[b].hide = function () {
								(c.buttons[b].isHidden = !0),
									e.css("display", "none");
							}),
							(c["$_" + b] = c["$$" + b] = e),
							c.$btnc.append(e);
					}),
					0 === a && this.$btnc.hide(),
					null === this.closeIcon && 0 === a && (this.closeIcon = !0),
					this.closeIcon)
				) {
					if (this.closeIconClass) {
						var b = '<i class="' + this.closeIconClass + '"></i>';
						this.$closeIcon.html(b);
					}
					this.$closeIcon.click(function (f) {
						f.preventDefault();
						var a,
							d = !1,
							b = !1;
						if (
							("string" ==
								typeof (a =
									"function" == typeof c.closeIcon
										? c.closeIcon()
										: c.closeIcon) &&
							void 0 !== c.buttons[a]
								? ((d = a), (b = !1))
								: (b = void 0 === a || !0 == !!a),
							d)
						) {
							var e = c.buttons[d].action.apply(c);
							b = void 0 === e || !!e;
						}
						b && c.close();
					}),
						this.$closeIcon.show();
				} else this.$closeIcon.hide();
			},
			setTitle: function (a, b) {
				if (((b = b || !1), void 0 !== a)) {
					if ("string" == typeof a) this.title = a;
					else if ("function" == typeof a) {
						"function" == typeof a.promise &&
							console.error(
								"Promise was returned from title function, this is not supported."
							);
						var c = a();
						"string" == typeof c
							? (this.title = c)
							: (this.title = !1);
					} else this.title = !1;
				}
				(!this.isAjaxLoading || b) &&
					(this.$title.html(this.title || ""),
					this.updateTitleContainer());
			},
			setIcon: function (a, b) {
				if (((b = b || !1), void 0 !== a)) {
					if ("string" == typeof a) this.icon = a;
					else if ("function" == typeof a) {
						var c = a();
						"string" == typeof c
							? (this.icon = c)
							: (this.icon = !1);
					} else this.icon = !1;
				}
				(!this.isAjaxLoading || b) &&
					(this.$icon.html(
						this.icon ? '<i class="' + this.icon + '"></i>' : ""
					),
					this.updateTitleContainer());
			},
			updateTitleContainer: function () {
				this.title || this.icon
					? this.$titleContainer.show()
					: this.$titleContainer.hide();
			},
			setContentPrepend: function (a, b) {
				a && this.contentParsed.prepend(a);
			},
			setContentAppend: function (a) {
				a && this.contentParsed.append(a);
			},
			setContent: function (b, a) {
				a = !!a;
				var c = this;
				b && this.contentParsed.html("").append(b),
					(!this.isAjaxLoading || a) &&
						(this.$content.html(""),
						this.$content.append(this.contentParsed),
						setTimeout(function () {
							c.$body
								.find("input[autofocus]:visible:first")
								.focus();
						}, 100));
			},
			loadingSpinner: !1,
			showLoading: function (a) {
				(this.loadingSpinner = !0),
					this.$jconfirmBox.addClass("loading"),
					a && this.$btnc.find("button").prop("disabled", !0);
			},
			hideLoading: function (a) {
				(this.loadingSpinner = !1),
					this.$jconfirmBox.removeClass("loading"),
					a && this.$btnc.find("button").prop("disabled", !1);
			},
			ajaxResponse: !1,
			contentParsed: "",
			isAjax: !1,
			isAjaxLoading: !1,
			_parseContent: function () {
				var c = this,
					b = "&nbsp;";
				if ("function" == typeof this.content) {
					var a = this.content.apply(this);
					"string" == typeof a
						? (this.content = a)
						: ("object" == typeof a &&
								"function" == typeof a.always &&
								((this.isAjax = !0),
								(this.isAjaxLoading = !0),
								a.always(function (a, b, d) {
									(c.ajaxResponse = {
										data: a,
										status: b,
										xhr: d,
									}),
										c._contentReady.resolve(a, b, d),
										"function" == typeof c.contentLoaded &&
											c.contentLoaded(a, b, d);
								})),
						  (this.content = b));
				}
				if (
					"string" == typeof this.content &&
					"url:" === this.content.substr(0, 4).toLowerCase()
				) {
					(this.isAjax = !0), (this.isAjaxLoading = !0);
					var d = this.content.substring(4, this.content.length);
					$.get(d)
						.done(function (a) {
							c.contentParsed.html(a);
						})
						.always(function (a, b, d) {
							(c.ajaxResponse = { data: a, status: b, xhr: d }),
								c._contentReady.resolve(a, b, d),
								"function" == typeof c.contentLoaded &&
									c.contentLoaded(a, b, d);
						});
				}
				this.content || (this.content = b),
					this.isAjax ||
						(this.contentParsed.html(this.content),
						this.setContent(),
						c._contentReady.resolve());
			},
			_stopCountDown: function () {
				clearInterval(this.autoCloseInterval),
					this.$cd && this.$cd.remove();
			},
			_startCountDown: function () {
				var e = this,
					a = this.autoClose.split("|");
				if (2 !== a.length)
					return (
						console.error(
							"Invalid option for autoClose. example 'close|10000'"
						),
						!1
					);
				var b = a[0],
					c = parseInt(a[1]);
				if (void 0 === this.buttons[b])
					return (
						console.error(
							"Invalid button key '" + b + "' for autoClose"
						),
						!1
					);
				var d = Math.ceil(c / 1e3);
				(this.$cd = $(
					'<span class="countdown"> (' + d + ")</span>"
				).appendTo(this["$_" + b])),
					(this.autoCloseInterval = setInterval(function () {
						e.$cd.html(" (" + (d -= 1) + ") "),
							d <= 0 &&
								(e["$$" + b].trigger("click"),
								e._stopCountDown());
					}, 1e3));
			},
			_getKey: function (a) {
				switch (a) {
					case 192:
						return "tilde";
					case 13:
						return "enter";
					case 16:
						return "shift";
					case 9:
						return "tab";
					case 20:
						return "capslock";
					case 17:
						return "ctrl";
					case 91:
						return "win";
					case 18:
						return "alt";
					case 27:
						return "esc";
					case 32:
						return "space";
				}
				var b = String.fromCharCode(a);
				return !!/^[A-z0-9]+$/.test(b) && b.toLowerCase();
			},
			reactOnKey: function (d) {
				var a,
					f = this,
					b = $(".jconfirm");
				if (b.eq(b.length - 1)[0] !== this.$el[0]) return !1;
				var c = d.which;
				if (
					this.$content.find(":input").is(":focus") &&
					/13|32/.test(c)
				)
					return !1;
				var e = this._getKey(c);
				"esc" === e &&
					this.escapeKey &&
					(!0 === this.escapeKey
						? this.$scrollPane.trigger("click")
						: ("string" == typeof this.escapeKey ||
								"function" == typeof this.escapeKey) &&
						  (a =
								"function" == typeof this.escapeKey
									? this.escapeKey()
									: this.escapeKey) &&
						  (void 0 === this.buttons[a]
								? console.warn(
										"Invalid escapeKey, no buttons found with key " +
											a
								  )
								: this["$_" + a].trigger("click"))),
					$.each(this.buttons, function (a, b) {
						-1 !== b.keys.indexOf(e) &&
							f["$_" + a].trigger("click");
					});
			},
			setDialogCenter: function () {
				console.info(
					"setDialogCenter is deprecated, dialogs are centered with CSS3 tables"
				);
			},
			_unwatchContent: function () {
				clearInterval(this._timer);
			},
			close: function (c) {
				var b = this;
				return (
					"function" == typeof this.onClose && this.onClose(c),
					this._unwatchContent(),
					$(window).unbind("resize." + this._id),
					$(window).unbind("keyup." + this._id),
					$(window).unbind("jcKeyDown." + this._id),
					this.draggable &&
						($(window).unbind("mousemove." + this._id),
						$(window).unbind("mouseup." + this._id),
						this.$titleContainer.unbind("mousedown")),
					b.$el.removeClass(b.loadedClass),
					$("body").removeClass("jconfirm-no-scroll-" + b._id),
					b.$jconfirmBoxContainer.removeClass(
						"jconfirm-no-transition"
					),
					setTimeout(function () {
						b.$body.addClass(b.closeAnimationParsed),
							b.$jconfirmBg.addClass("jconfirm-bg-h"),
							setTimeout(function () {
								b.$el.remove(), a.jconfirm.instances;
								for (
									var c = a.jconfirm.instances.length - 1;
									c >= 0;
									c--
								)
									a.jconfirm.instances[c]._id === b._id &&
										a.jconfirm.instances.splice(c, 1);
								if (
									!a.jconfirm.instances.length &&
									b.scrollToPreviousElement &&
									a.jconfirm.lastFocused &&
									a.jconfirm.lastFocused.length &&
									$.contains(
										document,
										a.jconfirm.lastFocused[0]
									)
								) {
									var e = a.jconfirm.lastFocused;
									if (b.scrollToPreviousElementAnimate) {
										var f = $(window).scrollTop(),
											d =
												a.jconfirm.lastFocused.offset()
													.top,
											g = $(window).height();
										if (d > f && d < f + g) e.focus();
										else {
											var h = d - Math.round(g / 3);
											$("html, body").animate(
												{ scrollTop: h },
												b.animationSpeed,
												"swing",
												function () {
													e.focus();
												}
											);
										}
									} else e.focus();
									a.jconfirm.lastFocused = !1;
								}
								"function" == typeof b.onDestroy &&
									b.onDestroy();
							}, 0.4 *
								("none" === b.closeAnimation
									? 1
									: b.animationSpeed));
					}, 50),
					!0
				);
			},
			open: function () {
				return (
					!this.isOpen() &&
					(this._buildHTML(), this._bindEvents(), this._open(), !0)
				);
			},
			setStartingPoint: function () {
				var b = !1;
				if (!0 !== this.animateFromElement && this.animateFromElement)
					(b = this.animateFromElement),
						(a.jconfirm.lastClicked = !1);
				else {
					if (
						!a.jconfirm.lastClicked ||
						!0 !== this.animateFromElement
					)
						return !1;
					(b = a.jconfirm.lastClicked), (a.jconfirm.lastClicked = !1);
				}
				if (!b) return !1;
				var e = b.offset(),
					f = b.outerHeight() / 2,
					g = b.outerWidth() / 2;
				(f -= this.$jconfirmBox.outerHeight() / 2),
					(g -= this.$jconfirmBox.outerWidth() / 2);
				var c = e.top + f;
				c -= this._scrollTop();
				var d = e.left + g,
					h = $(window).height() / 2,
					i = $(window).width() / 2,
					j = h - this.$jconfirmBox.outerHeight() / 2,
					k = i - this.$jconfirmBox.outerWidth() / 2;
				if (((c -= j), (d -= k), Math.abs(c) > h || Math.abs(d) > i))
					return !1;
				this.$jconfirmBoxContainer.css(
					"transform",
					"translate(" + d + "px, " + c + "px)"
				);
			},
			_open: function () {
				var a = this;
				"function" == typeof a.onOpenBefore && a.onOpenBefore(),
					this.$body.removeClass(this.animationParsed),
					this.$jconfirmBg.removeClass("jconfirm-bg-h"),
					this.$body.focus(),
					a.$jconfirmBoxContainer.css(
						"transform",
						"translate(0px, 0px)"
					),
					setTimeout(function () {
						a.$body.css(a._getCSS(a.animationSpeed, 1)),
							a.$body.css({
								"transition-property":
									a.$body.css("transition-property") +
									", margin",
							}),
							a.$jconfirmBoxContainer.addClass(
								"jconfirm-no-transition"
							),
							a._modalReady.resolve(),
							"function" == typeof a.onOpen && a.onOpen(),
							a.$el.addClass(a.loadedClass);
					}, this.animationSpeed);
			},
			loadedClass: "jconfirm-open",
			isClosed: function () {
				return !this.$el || 0 === this.$el.parent().length;
			},
			isOpen: function () {
				return !this.isClosed();
			},
			toggle: function () {
				this.isOpen() ? this.close() : this.open();
			},
		}),
		(a.jconfirm.instances = []),
		(a.jconfirm.lastFocused = !1),
		(a.jconfirm.pluginDefaults = {
			template:
				'<div class="jconfirm"><div class="jconfirm-bg jconfirm-bg-h"></div><div class="jconfirm-scrollpane"><div class="jconfirm-row"><div class="jconfirm-cell"><div class="jconfirm-holder"><div class="jc-bs3-container"><div class="jc-bs3-row"><div class="jconfirm-box-container jconfirm-animated"><div class="jconfirm-box" role="dialog" aria-labelledby="labelled" tabindex="-1"><div class="jconfirm-closeIcon">&times;</div><div class="jconfirm-title-c"><span class="jconfirm-icon-c"></span><span class="jconfirm-title"></span></div><div class="jconfirm-content-pane"><div class="jconfirm-content"></div></div><div class="jconfirm-buttons"></div><div class="jconfirm-clear"></div></div></div></div></div></div></div></div></div></div>',
			title: "Hello",
			titleClass: "",
			type: "default",
			typeAnimated: !0,
			draggable: !0,
			dragWindowGap: 15,
			dragWindowBorder: !0,
			animateFromElement: !0,
			alignMiddle: !0,
			smoothContent: !0,
			content: "Are you sure to continue?",
			buttons: {},
			defaultButtons: {
				ok: { action: function () {} },
				close: { action: function () {} },
			},
			contentLoaded: function () {},
			icon: "",
			lazyOpen: !1,
			bgOpacity: null,
			theme: "light",
			animation: "scale",
			closeAnimation: "scale",
			animationSpeed: 400,
			animationBounce: 1,
			escapeKey: !0,
			rtl: !1,
			container: "body",
			containerFluid: !1,
			backgroundDismiss: !1,
			backgroundDismissAnimation: "shake",
			autoClose: !1,
			closeIcon: null,
			closeIconClass: !1,
			watchInterval: 100,
			columnClass:
				"col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 col-xs-10 col-xs-offset-1",
			boxWidth: "50%",
			scrollToPreviousElement: !0,
			scrollToPreviousElementAnimate: !0,
			useBootstrap: !0,
			offsetTop: 40,
			offsetBottom: 40,
			bootstrapClasses: {
				container: "container",
				containerFluid: "container-fluid",
				row: "row",
			},
			onContentReady: function () {},
			onOpenBefore: function () {},
			onOpen: function () {},
			onClose: function () {},
			onDestroy: function () {},
			onAction: function () {},
		});
	var b = !1;
	$(window).on("keydown", function (c) {
		if (!b) {
			var d = $(c.target),
				a = !1;
			d.closest(".jconfirm-box").length && (a = !0),
				a && $(window).trigger("jcKeyDown"),
				(b = !0);
		}
	}),
		$(window).on("keyup", function () {
			b = !1;
		}),
		(a.jconfirm.lastClicked = !1),
		$(document).on("mousedown", "button, a, [jc-source]", function () {
			a.jconfirm.lastClicked = $(this);
		});
});
