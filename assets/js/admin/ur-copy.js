/**
 * Simple text copy functions using native browser clipboard capabilities.
 * @since 1.4.3
 */
jQuery(function ($) {
	var URCopyShortcode = {
		/**
		 * Initiate copy shortcode process.
		 */
		init: function () {
			ur_init_tooltips(".ur-copy-shortcode, .ur-portal-tooltip", {
				keepAlive: false
			});
			$(".ur-copy-shortcode").each(function () {
				var $this = $(this);

				$this.on("click", function (evt) {
					var res = $this.parent().find(".code").val();
					URCopyShortcode.urSetClipboard(res, $this);
					$this
						.tooltipster("content", $(this).attr("data-copied"))
						.trigger("focus")
						.on("mouseleave", function () {
							var $this = $(this);
							setTimeout(function () {
								$this.tooltipster(
									"content",
									$this.attr("data-tip")
								);
							}, 1000);
						});
					evt.preventDefault();
				});
			});
		},
		/**
		 * Set the user's clipboard contents.
		 *
		 * @param string data: Text to copy to clipboard.
		 * @param object $el: jQuery element to trigger copy events on. (Default: document)
		 */
		urSetClipboard: function (data, $el) {
			if ("undefined" === typeof $el) {
				$el = jQuery(document);
			}

			var $temp_input = jQuery('<textarea style="opacity:0">');
			jQuery("body").append($temp_input);
			$temp_input.val(data).select();

			$el.trigger("beforecopy");
			try {
				document.execCommand("copy");
				$el.trigger("aftercopy");
			} catch (err) {
				$el.trigger("aftercopyfailure");
			}

			$temp_input.remove();
		},
		/**
		 * Clear the user's clipboard.
		 */
		urClearClipboard: function () {
			URCopyShortcode.urSetClipboard("");
		}
	};

	/**
	 * Initiate copy shortcode process.
	 */
	URCopyShortcode.init();

	function ur_init_tooltips($elements, options) {
		if (undefined !== $elements && null !== $elements && "" !== $elements) {
			var args = {
				theme: "tooltipster-borderless",
				maxWidth: 200,
				multiple: true,
				interactive: true,
				position: "bottom",
				contentAsHTML: true,
				functionInit: function (instance, helper) {
					var $origin = jQuery(helper.origin),
						dataTip = $origin.attr("data-tip");

					if (dataTip) {
						instance.content(dataTip);
					}
				}
			};

			if (options && "object" === typeof options) {
				Object.keys(options).forEach(function (key) {
					args[key] = options[key];
				});
			}

			if ("string" === typeof $elements) {
				jQuery($elements).tooltipster(args);
			} else {
				$elements.tooltipster(args);
			}
		}
	}
});
