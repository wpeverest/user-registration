// Script to scroll menu horizontally for better User experience.
jQuery(function ($) {
	$(".user-registration_page_user-registration-settings")
		.find(".notice")
		.show();

	if ($(".ur-scroll-ui__items").length !== 0) {
		var scrollBackward,
			scrollForward,
			scrollItems,
			scrollItem,
			scrollWidth,
			scrollPos;
		scrollBackward = $(".ur-scroll-ui__scroll-nav--backward");
		scrollForward = $(".ur-scroll-ui__scroll-nav--forward");
		scrollItems = $(".ur-scroll-ui__items");
		scrollItem = $(".ur-scroll-ui__item");

		scrollBackward.on("click", function () {
			scrollWidth = scrollItems.width() - 60;
			scrollPos = scrollItems.scrollLeft() - scrollWidth;
			scrollItems.animate({ scrollLeft: scrollPos }, "slow");
		});

		scrollForward.on("click", function () {
			scrollWidth = scrollItems.width() - 60;
			scrollPos = scrollItems.scrollLeft() + scrollWidth;
			scrollItems.animate({ scrollLeft: scrollPos }, "slow");
		});

		// ScrollHandel visibility while window resizing.
		$(window).on("resize", handleMenuScroller);
		// ScrollHandel visibility while scrolling mouse.
		scrollItems.on("scroll", handleMenuScroller);

		handleMenuScroller();

		function handleMenuScroller() {
			var scrollLeft = scrollItems.scrollLeft(),
				width = scrollItems.width(),
				scrollWidth = scrollItems.get(0).scrollWidth,
				isLeftOverflow = scrollLeft > 0,
				isRightOverflow = scrollWidth - scrollLeft - width > 0,
				isOverflowing = scrollWidth > width;

			if (isOverflowing) {
				if (isLeftOverflow && isRightOverflow) {
					$(".ur-scroll-ui__scroll-nav--backward").removeClass(
						"is-disabled"
					);
					$(".ur-scroll-ui__scroll-nav--forward").removeClass(
						"is-disabled"
					);
				} else if (isLeftOverflow) {
					$(".ur-scroll-ui__scroll-nav--backward").removeClass(
						"is-disabled"
					);
					$(".ur-scroll-ui__scroll-nav--forward").addClass(
						"is-disabled"
					);
				} else {
					$(".ur-scroll-ui__scroll-nav--backward").addClass(
						"is-disabled"
					);
					$(".ur-scroll-ui__scroll-nav--forward").removeClass(
						"is-disabled"
					);
				}
			} else {
				$(".ur-scroll-ui__scroll-nav--backward").addClass(
					"is-disabled"
				);
				$(".ur-scroll-ui__scroll-nav--forward").addClass("is-disabled");
			}
		}

		// Scroll to Active Menu
		document.getElementsByClassName(
			"ur-scroll-ui__items"
		)[0].scrollLeft = 0;

		// Implement scroll to the active menu effect only for the menu items starting from the seventh position.
		for (var i = 6; i < scrollItem.length; i++) {
			if (scrollItem[i].classList.contains("is-active")) {
				document.getElementsByClassName(
					"ur-scroll-ui__items"
				)[0].scrollLeft = scrollItem[i].offsetLeft;
				break;
			}
		}
	}
});
