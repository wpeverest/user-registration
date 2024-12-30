// Script to scroll menu horizontally for better User experience.
jQuery(function ($) {
	$(".user-registration-membership_page_user-registration-settings")
		.find(".notice")
		.show();

	if ($(".ur-scroll-ui__items").length !== 0) {
		var scrollBackward = $(".ur-scroll-ui__scroll-nav--backward"),
			scrollForward = $(".ur-scroll-ui__scroll-nav--forward"),
			scrollItems = $(".ur-scroll-ui__items"),
			scrollItem = $(".ur-scroll-ui__item"),
			container = $(".ur-scroll-ui__scroll-nav")
				.not(".ur-scroll-ui__scroll-nav--backward ")
				.not(".ur-scroll-ui__scroll-nav--forward"),
			currentWidth = $(window).width();

		var scrollItems = $(".ur-scroll-ui__items");
		var items = scrollItems.find("li");
		var itemWidth = Math.max.apply(
			Math,
			items
				.map(function () {
					return $(this).outerWidth(true);
				})
				.get()
		); /* include margins */
		var visibleItems =
			currentWidth >= 1200
				? 6
				: currentWidth >= 992
				? 5
				: currentWidth >= 768
				? 4
				: currentWidth >= 576
				? 3
				: 2; /* adjust to desired number of visible items */
		var containerWidth = itemWidth * visibleItems;

		if (currentWidth <= 992) {
			/* set container width and scrollItems position */
			container.width(containerWidth);
		} else {
			container.width("100%");
		}

		scrollItems.css("position", "relative");

		/* add arrow click handlers */
		scrollBackward.on("click", function () {
			scrollItems.animate({ scrollLeft: "-=" + containerWidth }, "fast");
		});

		scrollForward.on("click", function () {
			scrollItems.animate({ scrollLeft: "+=" + containerWidth }, "fast");
		});

		// Scroll to Active scrollItems
		document.getElementsByClassName(
			"ur-scroll-ui__items"
		)[0].scrollLeft = 0;

		// Implement scroll to the active scrollItems effect only for the scrollItems items starting from the seventh position.
		for (var i = visibleItems; i < scrollItem.length; i++) {
			if (scrollItem[i].classList.contains("current")) {
				document.getElementsByClassName(
					"ur-scroll-ui__items"
				)[0].scrollLeft = scrollItem[i].offsetLeft;
				break;
			}
		}

		// ScrollHandel visibility while window resizing.
		$(window).on("resize", handlescrollItemsScroller);
		// ScrollHandel visibility while scrolling mouse.
		scrollItems.on("scroll", handlescrollItemsScroller);

		handlescrollItemsScroller();

		function handlescrollItemsScroller() {
			var scrollLeft = scrollItems.scrollLeft(),
				width = scrollItems.width(),
				scrollWidth = scrollItems.get(0).scrollWidth,
				isLeftOverflow = scrollLeft > 0,
				isRightOverflow = scrollWidth - scrollLeft - width > 0,
				isOverflowing = scrollWidth > width;

			if (scrollItems.find("li").length <= visibleItems) {
				return;
			}

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
	}
});
