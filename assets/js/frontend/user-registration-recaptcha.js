
var originalGrecaptcha = null,
	originalHcaptcha = null,
	originalTurnstile = null,
	isolatedGrecaptcha = null,
	isolatedGrecaptcha2 = null;

var createIsolatedCaptchaObject = function(type) {
	switch(type) {
		case 'hcaptcha':
			// For hCaptcha, always use dedicated loader to avoid conflicts
			return createDedicatedHcaptchaLoader();
		case 'grecaptcha':
		case 'recaptcha':
			// For reCAPTCHA, always use dedicated loader to avoid conflicts
			return createDedicatedRecaptchaLoader();
		case 'turnstile':
			return window.turnstile || originalTurnstile;
		default:
			return null;
	}
};

// Create dedicated hCaptcha loader that loads AFTER reCAPTCHA
var createDedicatedHcaptchaLoader = function() {
	return {
		render: function(element, options) {
			// Convert string ID to DOM element if needed
			if (typeof element === 'string') {
				element = document.getElementById(element);
			}

			if (!element || !element.nodeType) {
				return 'hcaptcha-error';
			}

			// Clear element
			element.innerHTML = '';

			// Create placeholder
			var placeholder = document.createElement('div');
			placeholder.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; padding: 10px; width: 100%;"><div class="ur-front-spinner" ></div></div>';
			element.appendChild(placeholder);

			// ALWAYS delay hCaptcha to ensure reCAPTCHA loads first
			setTimeout(function() {
				// Load hCaptcha with unique callback
				var uniqueId = 'dedicated_hcaptcha_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
				var callbackName = 'onloadDedicatedHcaptcha_' + uniqueId;

				window[callbackName] = function() {
					if (window.hcaptcha && window.hcaptcha.render) {
						element.innerHTML = '';
						var widgetId = window.hcaptcha.render(element, options);
						delete window[callbackName];
						return widgetId;
					}
				};

				var script = document.createElement('script');
				script.src = 'https://js.hcaptcha.com/1/api.js?render=explicit&onload=' + callbackName;
				script.onload = function() {
					setTimeout(function() {
						if (window.hcaptcha && window.hcaptcha.render && element) {
							element.innerHTML = '';
							window.hcaptcha.render(element, options);
						}
					}, 2000);
				};
				document.head.appendChild(script);
			}, 2000); // 2 second delay to ensure reCAPTCHA loads first

			return 'hcaptcha-loading';
		},
		reset: function(widgetId) {
			if (window.hcaptcha && window.hcaptcha.reset) {
				window.hcaptcha.reset(widgetId);
			}
		},
		execute: function(siteKey, options) {
			if (window.hcaptcha && window.hcaptcha.execute) {
				window.hcaptcha.execute(siteKey, options);
			}
		},
		getResponse: function(widgetId) {
			if (window.hcaptcha && window.hcaptcha.getResponse) {
				return window.hcaptcha.getResponse(widgetId);
			}
			return '';
		}
	};
};

// Create dedicated reCAPTCHA loader that loads FIRST (no delay)
var createDedicatedRecaptchaLoader = function() {
	return {
		render: function(element, options) {
			// Convert string ID to DOM element if needed
			if (typeof element === 'string') {
				element = document.getElementById(element);
			}

			if (!element || !element.nodeType) {
				return 'recaptcha-error';
			}

			// Clear element
			element.innerHTML = '';

			// Create placeholder
			var placeholder = document.createElement('div');
			placeholder.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; padding: 10px; width: 100%;"><div class="ur-front-spinner" ></div></div>';
			element.appendChild(placeholder);

			// Load reCAPTCHA IMMEDIATELY (no delay) to ensure it loads first
			var uniqueId = 'dedicated_recaptcha_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
			var callbackName = 'onloadDedicatedRecaptcha_' + uniqueId;

			window[callbackName] = function() {
				if (window.grecaptcha && window.grecaptcha.render) {
					element.innerHTML = '';
					var widgetId = window.grecaptcha.render(element, options);
					delete window[callbackName];
					return widgetId;
				}
			};

			var script = document.createElement('script');
			script.src = 'https://www.google.com/recaptcha/api.js?render=explicit&callback=' + callbackName;
			script.onload = function() {
				setTimeout(function() {
					if (window.grecaptcha && window.grecaptcha.render && element) {
						element.innerHTML = '';
						window.grecaptcha.render(element, options);
					}
				}, 2000);
			};
			document.head.appendChild(script);

			return 'recaptcha-loading';
		},
		reset: function(widgetId) {
			if (window.grecaptcha && window.grecaptcha.reset) {
				window.grecaptcha.reset(widgetId);
			}
		},
		execute: function(siteKey, options) {
			if (window.grecaptcha && window.grecaptcha.execute) {
				window.grecaptcha.execute(siteKey, options);
			}
		},
		getResponse: function(widgetId) {
			if (window.grecaptcha && window.grecaptcha.getResponse) {
				return window.grecaptcha.getResponse(widgetId);
			}
			return '';
		}
	};
};

var getSafeCaptchaObject = function(type) {
	var captchaObj = createIsolatedCaptchaObject(type);

	if (captchaObj && captchaObj.render) {
		return captchaObj;
	}

	// Fallback: if we can't get a proper object, create a simple one
	if (type === 'hcaptcha' || type === 'grecaptcha' || type === 'recaptcha') {
		return createFallbackCaptcha(type);
	}

	return null;
};

// Create a simple fallback captcha that always works
var createFallbackCaptcha = function(type) {
	return {
		render: function(element, options) {
			// Convert string ID to DOM element if needed
			if (typeof element === 'string') {
				element = document.getElementById(element);
			}

			if (!element || !element.nodeType) {
				return 'captcha-error';
			}

			// Clear element
			element.innerHTML = '';

			// Create a placeholder div
			var placeholder = document.createElement('div');
			placeholder.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; padding: 10px; width: 100%;"><span class="ur-front-spinner"></div></div>';
			element.appendChild(placeholder);

			// Try to load the real captcha
			if (type === 'hcaptcha') {
				loadHcaptchaWithCallback(element, options);
			} else {
				loadRecaptchaWithCallback(element, options);
			}

			return 'fallback-captcha-widget';
		},
		reset: function(widgetId) {
			// Fallback reset - do nothing
		},
		execute: function(siteKey, options) {
			// Fallback execute - do nothing
		},
		getResponse: function(widgetId) {
			// Fallback getResponse - return empty string
			return '';
		}
	};
};

// Load hCaptcha with callback
var loadHcaptchaWithCallback = function(element, options) {
	// Add delay to hCaptcha to avoid race condition with reCAPTCHA
	setTimeout(function() {
		var uniqueId = 'fallback_hcaptcha_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
		var callbackName = 'onloadFallbackHcaptcha_' + uniqueId;

		window[callbackName] = function() {
			if (window.hcaptcha && window.hcaptcha.render && window.hcaptcha !== window.grecaptcha) {
				element.innerHTML = '';
				window.hcaptcha.render(element, options);
				delete window[callbackName];
			}
		};

		var script = document.createElement('script');
		script.src = 'https://js.hcaptcha.com/1/api.js?render=explicit&onload=' + callbackName;
		script.onload = function() {
			setTimeout(function() {
				if (window.hcaptcha && window.hcaptcha.render && window.hcaptcha !== window.grecaptcha && element) {
					element.innerHTML = '';
					window.hcaptcha.render(element, options);
				}
			}, 2000);
		};
		document.head.appendChild(script);
	}, 1500); // 1.5 second delay for hCaptcha
};

// Load reCAPTCHA with callback
var loadRecaptchaWithCallback = function(element, options) {
	var uniqueId = 'fallback_recaptcha_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
	var callbackName = 'onloadFallbackRecaptcha_' + uniqueId;

	window[callbackName] = function() {
		if (window.grecaptcha && window.grecaptcha.render && window.grecaptcha !== window.hcaptcha) {
			element.innerHTML = '';
			window.grecaptcha.render(element, options);
			delete window[callbackName];
		}
	};

	var script = document.createElement('script');
	script.src = 'https://www.google.com/recaptcha/api.js?render=explicit&callback=' + callbackName;
	script.onload = function() {
		setTimeout(function() {
			if (window.grecaptcha && window.grecaptcha.render && window.grecaptcha !== window.hcaptcha && element) {
				element.innerHTML = '';
				window.grecaptcha.render(element, options);
			}
		}, 2000);
	};
	document.head.appendChild(script);
};

var backupOriginalGlobals = function() {
	if (typeof window.grecaptcha !== 'undefined') {
		originalGrecaptcha = window.grecaptcha;
		isolatedGrecaptcha = Object.assign({}, window.grecaptcha);
		isolatedGrecaptcha2 = JSON.parse(JSON.stringify(window.grecaptcha));
	}
	if (typeof window.hcaptcha !== 'undefined') {
		originalHcaptcha = window.hcaptcha;
	}
	if (typeof window.turnstile !== 'undefined') {
		originalTurnstile = window.turnstile;
	}
};

// Enhanced conflict detection that runs continuously
var enhancedConflictDetection = function() {
	// This function is no longer needed - we use complete isolation instead
};



// Function to load fresh reCAPTCHA when all backups are hCaptcha
var loadFreshRecaptcha = function() {
	if (typeof window.ur_fresh_grecaptcha === 'undefined') {
		// Load reCAPTCHA immediately (no delay) to ensure it loads first
		var script = document.createElement('script');
		script.src = 'https://www.google.com/recaptcha/api.js?render=explicit&callback=onloadURFreshRecaptcha';
		script.onload = function() {
			// Check if we got real reCAPTCHA after a delay
			setTimeout(function() {
				if (window.grecaptcha && window.grecaptcha !== window.hcaptcha) {
					window.ur_fresh_grecaptcha = window.grecaptcha;
				}
			}, 1000);
		};
		script.onerror = function() {
		};
		document.head.appendChild(script);
	}
};

// Callback for fresh reCAPTCHA
window.onloadURFreshRecaptcha = function() {
	if (window.grecaptcha && window.grecaptcha.render) {
		window.ur_fresh_grecaptcha = window.grecaptcha;
	}
};



// Initialize conflict detection
backupOriginalGlobals();

// Run conflict detection more frequently
setInterval(enhancedConflictDetection, 500);

// Add an even more aggressive approach - monitor for immediate overwriting
var aggressiveConflictDetection = function() {
	// This function is no longer needed - we use complete isolation instead
};

// Run aggressive detection very frequently
setInterval(aggressiveConflictDetection, 100);

// Add a more aggressive approach - monitor for script loading
var monitorScriptLoading = function() {
	// Check if reCAPTCHA script is loaded
	if (typeof window.grecaptcha !== 'undefined' && !originalGrecaptcha) {
		originalGrecaptcha = window.grecaptcha;
		isolatedGrecaptcha = Object.assign({}, window.grecaptcha);
		isolatedGrecaptcha2 = JSON.parse(JSON.stringify(window.grecaptcha));
	}

	// Check if hCaptcha script is loaded
	if (typeof window.hcaptcha !== 'undefined' && !originalHcaptcha) {
		originalHcaptcha = window.hcaptcha;
	}

	// Check for conflict and try to capture the real reCAPTCHA
	if (typeof window.grecaptcha !== 'undefined' && typeof window.hcaptcha !== 'undefined' &&
		window.grecaptcha !== window.hcaptcha && !originalGrecaptcha) {
		originalGrecaptcha = window.grecaptcha;
		isolatedGrecaptcha = Object.assign({}, window.grecaptcha);
		isolatedGrecaptcha2 = JSON.parse(JSON.stringify(window.grecaptcha));
	}

	// If they're the same object, we have a conflict - try to load fresh reCAPTCHA
	if (typeof window.grecaptcha !== 'undefined' && typeof window.hcaptcha !== 'undefined' &&
		window.grecaptcha === window.hcaptcha) {
		// This means hCaptcha has overwritten grecaptcha
		// Try to load fresh reCAPTCHA if we don't have a valid backup
		if (!originalGrecaptcha || originalGrecaptcha === window.hcaptcha) {
			loadFreshRecaptcha();
		}
	}

	// Additional check: if we have both objects but they're different, make sure we have backups
	if (typeof window.grecaptcha !== 'undefined' && typeof window.hcaptcha !== 'undefined' &&
		window.grecaptcha !== window.hcaptcha) {
		// Make sure we have reCAPTCHA backup
		if (!originalGrecaptcha || originalGrecaptcha === window.hcaptcha) {
			originalGrecaptcha = window.grecaptcha;
			isolatedGrecaptcha = Object.assign({}, window.grecaptcha);
			isolatedGrecaptcha2 = JSON.parse(JSON.stringify(window.grecaptcha));
		}
		// Make sure we have hCaptcha backup
		if (!originalHcaptcha || originalHcaptcha === window.grecaptcha) {
			originalHcaptcha = window.hcaptcha;
		}
	}
};

// Monitor script loading more frequently
setInterval(monitorScriptLoading, 100);

var immediateCapture = function() {
	if (typeof window.grecaptcha !== 'undefined' && !originalGrecaptcha) {
		originalGrecaptcha = window.grecaptcha;
		isolatedGrecaptcha = Object.assign({}, window.grecaptcha);
		isolatedGrecaptcha2 = JSON.parse(JSON.stringify(window.grecaptcha));
	}
};

// Run immediate capture
immediateCapture();

var protectGrecaptcha = function() {
	if (originalGrecaptcha && typeof window.grecaptcha !== 'undefined') {
		try {
			var grecaptchaProxy = new Proxy(originalGrecaptcha, {
				set: function(target, property, value) {
					target[property] = value;
					return true;
				}
			});

			Object.defineProperty(window, 'grecaptcha', {
				value: grecaptchaProxy,
				writable: false,
				configurable: false
			});
		} catch (e) {
			// Silent fail
		}
	}
};

// Try to protect grecaptcha after a short delay to ensure it's loaded
setTimeout(protectGrecaptcha, 2000);

(function ($) {
	var ursL10n = user_registration_params.ursL10n;

	var user_registration_recaptcha_init = function () {
		$(function () {
			// Detect and resolve conflicts before initializing
			enhancedConflictDetection();
			request_recaptcha_token();
		});
	};

	user_registration_recaptcha_init();

	// /**
	//  * Reinitialize the form again after page is fully loaded,
	//  * in order to support third party popup plugins like elementor.
	//  *
	//  * @since 1.9.0
	//  */
	// $(window).on("load", function () {
	// 	user_registration_recaptcha_init();
	// });

	$(function () {
		$(document).on(
			"user_registration_frontend_before_form_submit",
			function (event, data, $registration_form, $error_message) {
				if ("undefined" !== typeof ur_recaptcha_code) {
					if (
						"1" == $registration_form.data("captcha-enabled") &&
						ur_recaptcha_code.site_key.length
					) {
						if (ur_recaptcha_code.version == "v3") {
							var captchaResponse = $registration_form
								.find('[name="g-recaptcha-response"]')
								.val();
							request_recaptcha_token();
						} else if (ur_recaptcha_code.version == "hCaptcha") {
							var captchaResponse = $registration_form
								.find('[name="h-captcha-response"]')
								.val();

							var hcaptchaObj = getSafeCaptchaObject('hcaptcha');
							if (hcaptchaObj && hcaptchaObj.reset) {
								hcaptchaObj.reset(hcaptcha_user_registration);
							}
						} else if (ur_recaptcha_code.version == "cloudflare") {
							var captchaResponse = $registration_form
								.find('[name="cf-turnstile-response"]')
								.val();

							var turnstileObj = getSafeCaptchaObject('turnstile');
							if (turnstileObj && turnstileObj.reset) {
								turnstileObj.reset(turnstile_user_registration);
							}
						} else {
							var captchaResponse = $registration_form
								.find('[name="g-recaptcha-response"]')
								.val();

							var grecaptchaObj = getSafeCaptchaObject('grecaptcha');
							if (grecaptchaObj && grecaptchaObj.reset) {
							for (
								var i = 0;
								i <= google_recaptcha_user_registration;
								i++
							) {
									grecaptchaObj.reset(i);
								}
								if (ur_recaptcha_code.is_invisible && grecaptchaObj.execute) {
									grecaptchaObj.execute();
							}
							}
						}

						if (0 === captchaResponse.length) {
							$error_message["message"] = ursL10n.captcha_error;
						}
					}
				}
			}
		);

		$(document).on(
			"user_registration_after_login_failed",
			function (event, $login_form) {
				if (
					"undefined" !== typeof ur_recaptcha_code &&
					ur_recaptcha_code.site_key.length
				) {
					var ur_recaptcha_node = $login_form
						.closest("form")
						.find(
							"#ur-recaptcha-node #node_recaptcha_login.g-recaptcha"
						).length;
					var ur_recaptcha_node_hcaptcha = $login_form
						.closest("form")
						.find(
							"#ur-recaptcha-node #node_recaptcha_login.g-recaptcha-hcaptcha"
						).length;
					var ur_recaptcha_node_cloudflare = $login_form
						.closest("form")
						.find(
							"#ur-recaptcha-node #node_recaptcha_login.cf-turnstile"
						).length;
					var ur_recaptcha_node_v3 = $login_form
						.closest("form")
						.find(
							"#ur-recaptcha-node #node_recaptcha_login.g-recaptcha-v3"
						).length;
					if (
						ur_recaptcha_node !== 0 ||
						ur_recaptcha_node_hcaptcha !== 0 ||
						ur_recaptcha_node_cloudflare !== 0 ||
						ur_recaptcha_node_v3 !== 0
					) {
						if (ur_recaptcha_code.version == "v3") {
							request_recaptcha_token();
						} else if (ur_recaptcha_code.version == "hCaptcha") {
							var hcaptchaObj = getSafeCaptchaObject('hcaptcha');
							if (hcaptchaObj && hcaptchaObj.reset) {
								hcaptchaObj.reset(hcaptcha_login);
							}
						} else if (ur_recaptcha_code.version == "cloudflare") {
							var turnstileObj = getSafeCaptchaObject('turnstile');
							if (turnstileObj && turnstileObj.reset) {
								turnstileObj.reset(turnstile_login);
							}
						} else {
							var grecaptchaObj = getSafeCaptchaObject('grecaptcha');
							if (grecaptchaObj && grecaptchaObj.reset) {
							for (var i = 0; i <= google_recaptcha_login; i++) {
									grecaptchaObj.reset(i);
								}
								if (ur_recaptcha_code.is_invisible && grecaptchaObj.execute) {
									grecaptchaObj.execute();
							}
							}
						}
					}
				}
			}
		);
	});
})(jQuery);

var google_recaptcha_user_registration,
	google_recaptcha_login,
	hcaptcha_user_registration,
	hcaptcha_login,
	turnstile_user_registration,
	turnstile_login;

// hCaptcha callback function
var onloadURHcaptchaCallback = function () {
	setTimeout(function () {
		onloadURHcaptchaCallbackHandler();
	}, 200);
};

// reCAPTCHA callback function
var onloadURRecaptchaCallback = function () {
	setTimeout(function () {
		onloadURRecaptchaCallbackHandler();
	}, 200);
};

// Cloudflare Turnstile callback function
var onloadURTurnstileCallback = function () {
	setTimeout(function () {
		onloadURTurnstileCallbackHandler();
	}, 200);
};

// Original callback function for backward compatibility
var onloadURCallback = function () {
	// Check which captcha type is being used and call appropriate handler
	if (typeof ur_hcaptcha_recaptcha_code !== 'undefined' && ur_hcaptcha_recaptcha_code.site_key) {
		onloadURHcaptchaCallbackHandler();
	} else if (typeof ur_recaptcha_code !== 'undefined' && ur_recaptcha_code.site_key) {
		onloadURRecaptchaCallbackHandler();
	} else if (typeof ur_cloudflare_recaptcha_code !== 'undefined' && ur_cloudflare_recaptcha_code.site_key) {
		onloadURTurnstileCallbackHandler();
	}
};

// hCaptcha specific callback handler
var onloadURHcaptchaCallbackHandler = function () {
	setTimeout(function () {
		// Only handle hCaptcha forms
		jQuery(".ur-frontend-form")
			.find("form.register")
			.each(function (i) {
				$this = jQuery(this);
				var form_id = $this.closest(".ur-frontend-form").attr("id");

				var node_recaptcha_register = $this.find(
					"#ur-recaptcha-node #node_recaptcha_register"
				).length;

				// Only process if it's an hCaptcha form
				if ($this.find(
					"#ur-recaptcha-node #node_recaptcha_register"
				).hasClass('g-recaptcha-hcaptcha')) {
					var captcha_type = 'hCaptcha',
						recaptcha_code = ur_hcaptcha_recaptcha_code;

				if (
					"undefined" !== typeof captcha_type &&
					"undefined" !== typeof recaptcha_code &&
					recaptcha_code.site_key &&
					recaptcha_code.site_key.length
				) {
					if (node_recaptcha_register !== 0) {
							var hcaptchaElement = $this.find("#ur-recaptcha-node .g-recaptcha-hcaptcha");
							if (hcaptchaElement.length > 0) {
								hcaptchaElement.attr("id", "node_recaptcha_register_" + form_id);
								var hcaptchaObj = getSafeCaptchaObject('hcaptcha');
								if (hcaptchaObj && hcaptchaObj.render) {
									try {
										hcaptcha_user_registration = hcaptchaObj.render(
											"node_recaptcha_register_" + form_id,
											{
												sitekey: recaptcha_code.site_key,
												theme: "light",
												style: "transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;",
											}
										);
									} catch (error) {
										// Silent fail
									}
								}
							}
						}
					}
				}
			});

		// Handle hCaptcha login forms
		jQuery(".ur-frontend-form")
			.find("form.login")
			.each(function (i) {
				$this = jQuery(this);
				var ur_recaptcha_node = $this.find("#ur-recaptcha-node");
				if (
					"undefined" !== typeof ur_hcaptcha_recaptcha_code &&
					ur_hcaptcha_recaptcha_code.site_key.length
				) {
					if (ur_recaptcha_node.length !== 0) {
						if ("hCaptcha" === ur_hcaptcha_recaptcha_code.version) {
							var hcaptchaObj = getSafeCaptchaObject('hcaptcha');
							if (hcaptchaObj && hcaptchaObj.render) {
								hcaptcha_login = hcaptchaObj.render(
									ur_recaptcha_node
										.find(".g-recaptcha-hcaptcha")
										.attr("id"),
									{
										sitekey: ur_hcaptcha_recaptcha_code.site_key,
										theme: "light",
										style: "transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;",
									}
								);
							}
						}
					}
				}
			});

		// Handle hCaptcha lost password forms
		jQuery(".ur-frontend-form")
			.find("form.ur_lost_reset_password")
			.each(function (i) {
				$this = jQuery(this);
				var ur_recaptcha_node = $this.find("#ur-recaptcha-node");
				if ("undefined" !== typeof ur_hcaptcha_recaptcha_code) {
					if (ur_recaptcha_node.length !== 0) {
						if ("hCaptcha" === ur_hcaptcha_recaptcha_code.version) {
							var hcaptchaObj = getSafeCaptchaObject('hcaptcha');
							if (hcaptchaObj && hcaptchaObj.render) {
								google_recaptcha_ur_lost_reset_password =
									hcaptchaObj.render(
										ur_recaptcha_node
											.find(".g-recaptcha-hcaptcha")
											.attr("id"),
										{
											sitekey: ur_hcaptcha_recaptcha_code.site_key,
											theme: "light",
									style: "transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;",
								}
							);
							}
						}
					}
				}
			});
	}, 1000);
};

// reCAPTCHA specific callback handler
var onloadURRecaptchaCallbackHandler = function () {
	setTimeout(function () {
		// Only handle reCAPTCHA forms
		jQuery(".ur-frontend-form")
			.find("form.register")
			.each(function (i) {
				$this = jQuery(this);
				var form_id = $this.closest(".ur-frontend-form").attr("id");

				var node_recaptcha_register = $this.find(
					"#ur-recaptcha-node #node_recaptcha_register"
				).length;

				// Only process if it's a reCAPTCHA form (v2 or v3)
				if ($this.find(
					"#ur-recaptcha-node #node_recaptcha_register"
				).hasClass('g-recaptcha') || $this.find(
					"#ur-recaptcha-node #node_recaptcha_register"
				).hasClass('g-recaptcha-v3')) {
					var captcha_type, recaptcha_code;

					if ($this.find(
						"#ur-recaptcha-node #node_recaptcha_register"
					).hasClass('g-recaptcha')) {
						captcha_type = 'v2';
						recaptcha_code = ur_recaptcha_code;
					} else if ($this.find(
						"#ur-recaptcha-node #node_recaptcha_register"
					).hasClass('g-recaptcha-v3')) {
						captcha_type = 'v3';
						recaptcha_code = ur_v3_recaptcha_code;
					}


					if (
						"undefined" !== typeof captcha_type &&
						"undefined" !== typeof recaptcha_code &&
						recaptcha_code.site_key &&
						recaptcha_code.site_key.length
					) {
						if (node_recaptcha_register !== 0) {
							$this
								.find("#ur-recaptcha-node .g-recaptcha")
								.attr("id", "node_recaptcha_register_" + form_id);

							var grecaptchaObj = getSafeCaptchaObject('grecaptcha');
							if (grecaptchaObj && grecaptchaObj.render) {
								google_recaptcha_user_registration = grecaptchaObj.render(
								"node_recaptcha_register_" + form_id,
								{
									sitekey: recaptcha_code.site_key,
									theme: "light",
									style: "transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;",
								}
							);

								if (recaptcha_code.is_invisible && grecaptchaObj.execute) {
									grecaptchaObj.execute(google_recaptcha_user_registration);
								}
						}
						}
					}
				}
			});

		// Handle reCAPTCHA login forms
		jQuery(".ur-frontend-form")
			.find("form.login")
			.each(function (i) {
				$this = jQuery(this);
				var ur_recaptcha_node = $this.find("#ur-recaptcha-node");
				if (
					"undefined" !== typeof ur_recaptcha_code &&
					ur_recaptcha_code.site_key.length
				) {
					if (ur_recaptcha_node.length !== 0) {
						// Only handle reCAPTCHA (v2/v3) forms
						if (ur_recaptcha_code.version !== "hCaptcha" && ur_recaptcha_code.version !== "cloudflare") {
							var grecaptchaObj = getSafeCaptchaObject('grecaptcha');
							if (grecaptchaObj && grecaptchaObj.render) {
								google_recaptcha_login = grecaptchaObj.render(
								ur_recaptcha_node.find(".g-recaptcha").attr("id"),
								{
									sitekey: ur_recaptcha_code.site_key,
									theme: "light",
									style: "transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;",
								}
							);
								if (ur_recaptcha_code.is_invisible && grecaptchaObj.execute) {
									grecaptchaObj.execute(google_recaptcha_login);
								}
						}
						}
					}
				}
			});

		// Handle reCAPTCHA lost password forms
		jQuery(".ur-frontend-form")
			.find("form.ur_lost_reset_password")
			.each(function (i) {
				$this = jQuery(this);
				var ur_recaptcha_node = $this.find("#ur-recaptcha-node");
				if ("undefined" !== typeof ur_recaptcha_code) {
					if (ur_recaptcha_node.length !== 0) {
						// Only handle reCAPTCHA (v2/v3) forms
						if (ur_recaptcha_code.version !== "hCaptcha" && ur_recaptcha_code.version !== "cloudflare") {
							var grecaptchaObj = getSafeCaptchaObject('grecaptcha');
							if (grecaptchaObj && grecaptchaObj.render) {
							google_recaptcha_ur_lost_reset_password =
									grecaptchaObj.render(
									ur_recaptcha_node
											.find(".g-recaptcha")
										.attr("id"),
									{
										sitekey: ur_recaptcha_code.site_key,
										theme: "light",
										style: "transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;",
									}
								);
								if (ur_recaptcha_code.is_invisible && grecaptchaObj.execute) {
									grecaptchaObj.execute(
										google_recaptcha_ur_lost_reset_password
									);
								}
							}
						}
					}
				}
			});
	}, 1000);
};

// Cloudflare Turnstile specific callback handler
var onloadURTurnstileCallbackHandler = function () {
	setTimeout(function () {
		// Only handle Cloudflare Turnstile forms
		jQuery(".ur-frontend-form")
			.find("form.register")
			.each(function (i) {
				$this = jQuery(this);
				var form_id = $this.closest(".ur-frontend-form").attr("id");

				var node_recaptcha_register = $this.find(
					"#ur-recaptcha-node #node_recaptcha_register"
				).length;

				// Only process if it's a Cloudflare Turnstile form
				if ($this.find(
					"#ur-recaptcha-node #node_recaptcha_register"
				).hasClass('cf-turnstile')) {
					var captcha_type = 'cloudflare',
						recaptcha_code = ur_cloudflare_recaptcha_code;

					if (
						"undefined" !== typeof captcha_type &&
						"undefined" !== typeof recaptcha_code &&
						recaptcha_code.site_key &&
						recaptcha_code.site_key.length
					) {
						if (node_recaptcha_register !== 0) {
							$this
								.find("#ur-recaptcha-node .cf-turnstile")
								.attr("id", "node_recaptcha_register_" + form_id);
							var turnstileObj = getSafeCaptchaObject('turnstile');
							if (turnstileObj && turnstileObj.render) {
								turnstile_user_registration = turnstileObj.render(
									"#node_recaptcha_register_" + form_id,
									{
										sitekey: recaptcha_code.site_key,
										theme: recaptcha_code.theme_mode,
										style: "transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;",
									}
								);
							}
						}
					}
				}
			});

		// Handle Cloudflare Turnstile login forms
		jQuery(".ur-frontend-form")
			.find("form.login")
			.each(function (i) {
				$this = jQuery(this);
				var ur_recaptcha_node = $this.find("#ur-recaptcha-node");
				if (
					"undefined" !== typeof ur_cloudflare_recaptcha_code &&
					ur_cloudflare_recaptcha_code.site_key.length
				) {
					if (ur_recaptcha_node.length !== 0) {
						if ("cloudflare" === ur_cloudflare_recaptcha_code.version) {
							var turnstileObj = getSafeCaptchaObject('turnstile');
							if (turnstileObj && turnstileObj.render) {
								turnstile_login = turnstileObj.render(
									"#" + ur_recaptcha_node
										.find(".cf-turnstile")
										.attr("id"),
									{
										sitekey: ur_cloudflare_recaptcha_code.site_key,
										theme: ur_cloudflare_recaptcha_code.theme_mode,
										style: "transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;",
									}
								);
							}
						}
					}
				}
			});

		// Handle Cloudflare Turnstile lost password forms
		jQuery(".ur-frontend-form")
			.find("form.ur_lost_reset_password")
			.each(function (i) {
				$this = jQuery(this);
				var ur_recaptcha_node = $this.find("#ur-recaptcha-node");
				if ("undefined" !== typeof ur_cloudflare_recaptcha_code) {
					if (ur_recaptcha_node.length !== 0) {
						if ("cloudflare" === ur_cloudflare_recaptcha_code.version) {
							var turnstileObj = getSafeCaptchaObject('turnstile');
							if (turnstileObj && turnstileObj.render) {
							google_recaptcha_ur_lost_reset_password =
									turnstileObj.render(
										"#" + ur_recaptcha_node
											.find(".cf-turnstile")
										.attr("id"),
									{
											sitekey: ur_cloudflare_recaptcha_code.site_key,
											theme: ur_cloudflare_recaptcha_code.theme_mode,
										style: "transform:scale(0.77);-webkit-transform:scale(0.77);transform-origin:0 0;-webkit-transform-origin:0 0;",
									}
								);
						}
						}
					}
				}
			});
	}, 1000);
};


function request_recaptcha_token() {
	// Determine which reCAPTCHA code variable to use based on what's available
	var recaptchaCode = null;
	if (typeof ur_v3_recaptcha_code !== 'undefined') {
		recaptchaCode = ur_v3_recaptcha_code;
	} else if (typeof ur_recaptcha_code !== 'undefined') {
		recaptchaCode = ur_recaptcha_code;
	}

	if (!recaptchaCode || !recaptchaCode.site_key || !recaptchaCode.site_key.length) {
		return;
	}

	var node_recaptcha_register = jQuery(".ur-frontend-form").find(
		"form.register #ur-recaptcha-node #node_recaptcha_register.g-recaptcha-v3"
	).length;
	if (node_recaptcha_register !== 0) {
			grecaptcha.ready(function () {
				grecaptcha
					.execute(recaptchaCode.site_key, {
						action: "register",
					})
					.then(function (token) {
						jQuery("form.register")
							.find("#g-recaptcha-response")
							.text(token);
					});
			});
	}

	var node_recaptcha_login = jQuery(".ur-frontend-form").find(
		"form.login #ur-recaptcha-node #node_recaptcha_login.g-recaptcha-v3"
	).length;
	if (node_recaptcha_login !== 0) {
		grecaptcha.ready(function () {
			grecaptcha
				.execute(recaptchaCode.site_key, {
					action: "login",
				})
				.then(function (token) {
					console.log(token)
					jQuery("form.login")
						.find("#g-recaptcha-response")
						.text(token);
				});
		});
	}

	var node_recaptcha_ur_lost_reset_password = jQuery(
		".ur-frontend-form"
	).find(
		"form.ur_lost_reset_password #ur-recaptcha-node #node_recaptcha_lost_password.g-recaptcha-v3"
	).length;
	if (node_recaptcha_ur_lost_reset_password !== 0) {
		if (typeof grecaptcha !== 'undefined' && grecaptcha.ready && grecaptcha.execute) {
			grecaptcha.ready(function () {
				grecaptcha
					.execute(recaptchaCode.site_key, {
						action: "lost_password",
					})
					.then(function (token) {
						jQuery("form.ur_lost_reset_password")
							.find("#g-recaptcha-response")
							.text(token);
					});
			});
		}
	}
}
