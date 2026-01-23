/**
 * UserRegistrationContentRestriction Content Access Rule Creator JS
 * Only initializes select2 for enhanced select elements
 */
(function ($) {
	// On document ready.
	$(function () {
		// Initialize enhanced select2 elements.
		init_all_enhanced_select();
	});

	/**
	 * Initialize all enhanced select elements.
	 * This function is also exposed globally so React components can call it.
	 */
	function init_all_enhanced_select() {
		var select2_changed_flag_up = false;

		$(".urcr-enhanced-select2").each(function () {
			// Skip if already initialized
			if ($(this).hasClass("select2-hidden-accessible")) {
				return;
			}

			var select2_class = $(this).data("select2_class");
			var $select = $(this);

			$select
				.select2({
					containerCssClass: select2_class,
				})
				.on("select2:selecting", function () {
					select2_changed_flag_up = true;
				})
				.on("select2:unselecting", function () {
					select2_changed_flag_up = true;
				})
				.on("select2:closing", function () {
					// Prevent closing only if user has just selected an option.
					if (select2_changed_flag_up && this.multiple) {
						select2_changed_flag_up = false;
						return false;
					}
				});
		});
	}

	// Expose function globally for React components
	window.urcrInitEnhancedSelect = init_all_enhanced_select;
})(jQuery);
