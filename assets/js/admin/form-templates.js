jQuery(function ($) {

	var ur_form_templates = {
		init: function () {
			this.wrapper = $('.user-registration-form-template-wrapper');
			this.wrapper.find('.user-registration-tab-nav a').on('click', this.form_template_plan_type);

		},
		form_template_plan_type: function (e) {
			e.preventDefault();
 			var $this = $(e.target);
			var plan_type = $this.attr('data-plan');
			$this.closest('ul').find('li').removeClass('active');
			$this.closest('li').addClass('active');
			var template_wrap = ur_form_templates.wrapper.find('.user-registration-form-template');
			template_wrap.attr('data-filter-template', plan_type);

		}
	};
	ur_form_templates.init();
});
