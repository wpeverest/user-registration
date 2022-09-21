/* global ur_template_controller */
jQuery(function ($) {
	/**
	 * Template actions.
	 */
	var ur_template_controller = {
		all: "#ur-form-all",
		basic: "#ur-form-basic",
		pro: "#ur-form-pro",
		results: ur_templates.ur_template_all,
		init: function () {
			ur_template_controller.latch_hooks();
		},
		latch_hooks: function () {
			$(document.body).ready(function () {
				$(ur_template_controller.all).click(function (e) {
					e.preventDefault();
					ur_template_controller.sort_all(this);
				});
				$(ur_template_controller.basic).click(function (e) {
					e.preventDefault();
					ur_template_controller.sort_basic(this);
				});
				$(ur_template_controller.pro).click(function (e) {
					e.preventDefault();
					ur_template_controller.sort_pro(this);
				});
				$(".page-title-action").click(function (e) {
					e.stopImmediatePropagation();

					$(this).html(
						ur_templates.template_refresh +
							' <div  class="ur-loading ur-loading-active"></div>'
					);
				});
			});
		},
		sort_all: function (el) {
			ur_template_controller.class_update($(el));
			ur_template_controller.render_results(
				ur_template_controller.results,
				"all"
			);
		},
		sort_basic: function (el) {
			ur_template_controller.class_update($(el));
			ur_template_controller.render_results(
				ur_template_controller.results,
				"free"
			);
		},
		sort_pro: function (el) {
			ur_template_controller.class_update($(el));
			ur_template_controller.render_results(
				ur_template_controller.results,
				"pro"
			);
		},
		class_update: function ($el) {
			$(".user-registration-tab-nav").removeClass("active");
			$el.parent().addClass("active");
		},
		render_results: function (template, allow) {
			var el_to_append = $(".ur-setup-templates"),
				error = '<div  class="ur-loading ur-loading-active"></div>';

			if (!template) {
				$("#message").remove();
				el_to_append.html(error);

				// Adds a loading screen so the async results is populated.
				window.setTimeout(function () {
					ur_template_controller.render_results(
						ur_template_controller.results,
						allow
					);
				}, 1000);

				return;
			}

			$(".user-registration-form-template").html("");

			template.forEach(function (tuple) {
				var toAppend = "",
					plan = tuple.plan.includes("free") ? "free" : "pro",
					data_plan = $(".user-registration-form-template").data(
						"license-type"
					);

				if ("all" === allow || "blank" === tuple.slug) {
					toAppend = ur_template_controller.template_snippet(
						tuple,
						plan,
						data_plan
					);
				} else if (plan === allow) {
					toAppend = ur_template_controller.template_snippet(
						tuple,
						plan,
						data_plan
					);
				}

				el_to_append.append(toAppend);
			});
		},
		template_snippet: function (template, plan, data_plan) {
			var html = "",
				modal = "ur-template-select";
			data_plan =
				"" === data_plan ? "free" : data_plan.replace("-lifetime", "");
			if (
				!template.plan.includes("free") &&
				!template.plan.includes(data_plan)
			) {
				modal = "upgrade-modal";
			}

			html +=
				'<div class="user-registration-template-wrap ur-template" id="user-registration-template-' +
				template.slug +
				'" data-plan="' +
				plan +
				'">';

			if ("blank" !== template.slug) {
				html += '<figure class="user-registration-screenshot" ';
			} else {
				html +=
					'<figure class="user-registration-screenshot ur-template-select" ';
			}

			html +=
				'data-template-name-raw="' +
				template.title +
				'" data-template="' +
				template.slug +
				'" data-template-name="' +
				template.title +
				' template">';
			html +=
				'<img src=" ' +
				ur_templates.ur_plugin_url +
				"/assets/" +
				template.image +
				' ">';

			if ("blank" !== template.slug) {
				html +=
					'<div class="form-action"><a href="#" class="user-registration-btn user-registration-btn-primary ' +
					modal +
					'" data-licence-plan="' +
					data_plan +
					'" data-template-name-raw="' +
					template.title +
					'" data-template-name="' +
					template.title +
					' template" data-template="' +
					template.slug +
					'">' +
					ur_templates.i18n_get_started +
					"</a>";
				html +=
					'<a href="' +
					template.preview_link +
					'" target="_blank" class="user-registration-btn user-registration-btn-secondary">' +
					ur_templates.i18n_get_preview +
					"</a></div>";
			}

			if (!template.plan.includes("free")) {
				var $badge_text = "";
				if (template.plan.includes("personal")) {
					$badge_text = "Personal";
				} else if (template.plan.includes("plus")) {
					$badge_text = "Plus";
				} else if (template.plan.includes("professional")) {
					$badge_text = "Professional";
				} else {
					$badge_text = "Agency";
				}

				html +=
					'<span class="user-registration-badge user-registration-badge--success">' +
					$badge_text +
					"</span>";
			}

			html +=
				'</figure><div class="user-registration-form-id-container">';
			html +=
				'<a class="user-registration-template-name ' +
				modal +
				'" href="#" data-template-name-raw="' +
				template.title +
				'" data-licence-plan="' +
				data_plan +
				'" data-template="' +
				template.slug +
				'" data-template-name="' +
				template.title +
				' template">' +
				template.title +
				"</a></div>";

			return html;
		},
	};

	ur_template_controller.init();
});
