/**
 * KDNA Listing Peek — front-end script
 *
 * Reads data attributes from .kdna-peek-active widgets, sets CSS custom
 * properties per breakpoint, handles resize, and supports both Slick
 * Slider and Scroll Slider modes.
 *
 * Edge cases handled:
 *   - RTL layouts (peek flips to left edge)
 *   - Last slide group detection (hides peek when nothing left to show)
 *   - Not-enough-items detection (disables peek when items <= columns)
 *   - Scroll slider end detection via IntersectionObserver
 *   - Elementor editor preview support
 */
(function () {
	'use strict';

	/* -----------------------------------------------------------------
	 * Breakpoint thresholds — overridden by wp_localize_script values
	 * passed from PHP when Elementor's kit settings are available.
	 * ----------------------------------------------------------------- */
	var BP_TABLET = 1024;
	var BP_MOBILE = 767;

	if (
		typeof window.kdnaListingPeek !== 'undefined' &&
		window.kdnaListingPeek.breakpoints
	) {
		BP_TABLET = parseInt(window.kdnaListingPeek.breakpoints.tablet, 10) || BP_TABLET;
		BP_MOBILE = parseInt(window.kdnaListingPeek.breakpoints.mobile, 10) || BP_MOBILE;
	}

	/* -----------------------------------------------------------------
	 * RTL detection — true when <html> or <body> has dir="rtl".
	 * ----------------------------------------------------------------- */
	var isRTL = (
		document.documentElement.getAttribute('dir') === 'rtl' ||
		(document.body && document.body.getAttribute('dir') === 'rtl')
	);

	/* -----------------------------------------------------------------
	 * Utility: simple debounce
	 * ----------------------------------------------------------------- */
	function debounce(fn, delay) {
		var timer;
		return function () {
			clearTimeout(timer);
			timer = setTimeout(fn, delay);
		};
	}

	/* -----------------------------------------------------------------
	 * Determine current breakpoint name based on window width.
	 * ----------------------------------------------------------------- */
	function getBreakpoint() {
		var w = window.innerWidth;
		if (w <= BP_MOBILE) return 'mobile';
		if (w <= BP_TABLET) return 'tablet';
		return 'desktop';
	}

	/* -----------------------------------------------------------------
	 * Apply peek CSS custom properties and classes to a single widget.
	 * ----------------------------------------------------------------- */
	function applyPeek(el) {
		var bp = getBreakpoint();

		// Read data attributes set by PHP in before_render.
		var widthDesktop = parseInt(el.getAttribute('data-kdna-peek-width'), 10) || 60;
		var widthTablet  = parseInt(el.getAttribute('data-kdna-peek-width-tablet'), 10) || 40;
		var widthMobile  = parseInt(el.getAttribute('data-kdna-peek-width-mobile'), 10) || 30;
		var fade         = el.getAttribute('data-kdna-peek-fade') === 'yes';
		var fadeWidth    = parseInt(el.getAttribute('data-kdna-peek-fade-width'), 10) || 40;

		// Pick the correct peek width for the current breakpoint.
		var peekWidth;
		if (bp === 'mobile') {
			peekWidth = widthMobile;
		} else if (bp === 'tablet') {
			peekWidth = widthTablet;
		} else {
			peekWidth = widthDesktop;
		}

		// Set CSS custom properties on the widget wrapper.
		el.style.setProperty('--kdna-peek-width', peekWidth + 'px');
		el.style.setProperty('--kdna-peek-fade-width', fadeWidth + 'px');

		// Toggle the fade class based on the data attribute.
		if (fade) {
			el.classList.add('kdna-peek-fade');
		} else {
			el.classList.remove('kdna-peek-fade');
		}

		// Check if there are enough items to justify the peek effect.
		checkNotEnoughItems(el);
	}

	/* -----------------------------------------------------------------
	 * Not-enough-items detection.
	 * If the grid has fewer real slides than Slick's slidesToShow value,
	 * there is nothing to peek — disable the effect.
	 * ----------------------------------------------------------------- */
	function checkNotEnoughItems(el) {
		if (typeof jQuery === 'undefined') return;

		var $slider = jQuery(el).find('.slick-slider');
		if (!$slider.length) return;

		var slickData = $slider.slick('getSlick');
		if (!slickData) return;

		var totalSlides  = $slider.find('.slick-slide:not(.slick-cloned)').length;
		var slidesToShow = slickData.options && slickData.options.slidesToShow
			? slickData.options.slidesToShow
			: 1;

		if (totalSlides <= slidesToShow) {
			el.classList.add('kdna-peek-disabled');
		} else {
			el.classList.remove('kdna-peek-disabled');
		}
	}

	/* -----------------------------------------------------------------
	 * Last-slide-group detection for Slick sliders.
	 * Listens to Slick's afterChange event and adds/removes
	 * .kdna-peek-last-slide when the slider reaches the final group.
	 * ----------------------------------------------------------------- */
	function bindLastSlideDetection(el) {
		if (el._kdnaPeekLastSlideBound) return;
		el._kdnaPeekLastSlideBound = true;

		if (typeof jQuery === 'undefined') return;

		var $slider = jQuery(el).find('.slick-slider');
		if (!$slider.length) return;

		$slider.on('afterChange', function (_event, slick, currentSlide) {
			var totalSlides  = slick.slideCount;
			var slidesToShow = slick.options.slidesToShow || 1;
			var slidesToScroll = slick.options.slidesToScroll || 1;

			// On the last group when current slide + slidesToShow >= total.
			var isLastGroup = (currentSlide + slidesToShow) >= totalSlides;

			// Also detect when Slick cannot scroll further.
			var maxIndex = totalSlides - slidesToScroll;
			if (currentSlide >= maxIndex) {
				isLastGroup = true;
			}

			if (isLastGroup) {
				el.classList.add('kdna-peek-last-slide');
			} else {
				el.classList.remove('kdna-peek-last-slide');
			}
		});

		// Run an initial check — if the slider initialised on the last group.
		$slider.on('init reInit', function (_event, slick) {
			var currentSlide = slick.currentSlide || 0;
			var totalSlides  = slick.slideCount;
			var slidesToShow = slick.options.slidesToShow || 1;

			if ((currentSlide + slidesToShow) >= totalSlides) {
				el.classList.add('kdna-peek-last-slide');
			} else {
				el.classList.remove('kdna-peek-last-slide');
			}
		});
	}

	/* -----------------------------------------------------------------
	 * Scroll slider end detection via IntersectionObserver.
	 * Watches the last child of the scroll container — when it becomes
	 * fully visible, adds .kdna-peek-scroll-end to hide the peek.
	 * ----------------------------------------------------------------- */
	function bindScrollSliderEndDetection(el) {
		if (el._kdnaPeekScrollBound) return;
		el._kdnaPeekScrollBound = true;

		var scrollContainer = el.querySelector('.jet-listing-grid__scroll-slider');
		if (!scrollContainer) return;

		var lastChild = scrollContainer.lastElementChild;
		if (!lastChild) return;

		// Use IntersectionObserver if supported, scoped to the scroll container.
		if ('IntersectionObserver' in window) {
			var observer = new IntersectionObserver(
				function (entries) {
					entries.forEach(function (entry) {
						if (entry.isIntersecting && entry.intersectionRatio >= 0.95) {
							el.classList.add('kdna-peek-scroll-end');
						} else {
							el.classList.remove('kdna-peek-scroll-end');
						}
					});
				},
				{
					root: scrollContainer,
					threshold: [0, 0.95]
				}
			);
			observer.observe(lastChild);

			// Store reference so we can disconnect if re-initialised.
			el._kdnaPeekScrollObserver = observer;
		} else {
			// Fallback: listen to scroll events on the container.
			scrollContainer.addEventListener('scroll', debounce(function () {
				var atEnd = isRTL
					? scrollContainer.scrollLeft <= 1
					: (scrollContainer.scrollLeft + scrollContainer.clientWidth) >= (scrollContainer.scrollWidth - 1);

				if (atEnd) {
					el.classList.add('kdna-peek-scroll-end');
				} else {
					el.classList.remove('kdna-peek-scroll-end');
				}
			}, 100));
		}
	}

	/* -----------------------------------------------------------------
	 * Bind Slick init/reInit events so the peek re-applies after Slick
	 * recalculates its layout. Slick requires jQuery for event binding.
	 * ----------------------------------------------------------------- */
	function bindSlickEvents(el) {
		if (el._kdnaPeekSlickBound) return;
		el._kdnaPeekSlickBound = true;

		if (typeof jQuery === 'undefined') return;

		var $slider = jQuery(el).find('.slick-slider');
		if (!$slider.length) return;

		$slider.on('init reInit', function () {
			applyPeek(el);
		});
	}

	/* -----------------------------------------------------------------
	 * Initialise a single peek widget: apply styles, bind all events.
	 * ----------------------------------------------------------------- */
	function initWidget(el) {
		applyPeek(el);
		bindSlickEvents(el);
		bindLastSlideDetection(el);
		bindScrollSliderEndDetection(el);
	}

	/* -----------------------------------------------------------------
	 * Initialise all peek widgets found on the page.
	 * ----------------------------------------------------------------- */
	function initAllPeekWidgets() {
		var widgets = document.querySelectorAll('.kdna-peek-active');
		for (var i = 0; i < widgets.length; i++) {
			initWidget(widgets[i]);
		}

		// Also initialise any remote arrows on the page.
		initRemoteArrows();
	}

	/* -----------------------------------------------------------------
	 * Recalculate all widgets (called on debounced resize).
	 * ----------------------------------------------------------------- */
	function refreshAllPeekWidgets() {
		var widgets = document.querySelectorAll('.kdna-peek-active');
		for (var i = 0; i < widgets.length; i++) {
			applyPeek(widgets[i]);
		}
	}

	/* -----------------------------------------------------------------
	 * Window resize handler — debounced at 150 ms.
	 * ----------------------------------------------------------------- */
	window.addEventListener('resize', debounce(refreshAllPeekWidgets, 150));

	/* -----------------------------------------------------------------
	 * Bootstrap: run on DOMContentLoaded (or immediately if DOM is ready).
	 * ----------------------------------------------------------------- */
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAllPeekWidgets);
	} else {
		initAllPeekWidgets();
	}

	/* -----------------------------------------------------------------
	 * Bootstrap: Elementor frontend/init hook.
	 * Ensures the peek effect works in Elementor's live preview/editor
	 * by re-initialising after Elementor renders its widgets.
	 * ----------------------------------------------------------------- */
	if (typeof jQuery !== 'undefined') {
		jQuery(window).on('elementor/frontend/init', function () {
			// Delay to let Elementor finish rendering widgets.
			setTimeout(initAllPeekWidgets, 200);
		});

		// Re-init when Elementor editor triggers a widget render in preview.
		// elementorFrontend.hooks fires after each widget is rendered.
		jQuery(document).on('elementor/frontend/widget/ready', function () {
			setTimeout(initAllPeekWidgets, 100);
		});
	}

	/* -----------------------------------------------------------------
	 * Remote Arrows — connect remote prev/next buttons to a Listing
	 * Grid slider via the shared data-kdna-connection-id /
	 * data-kdna-remote-id attribute pair.
	 * ----------------------------------------------------------------- */
	function initRemoteArrows() {
		var remotes = document.querySelectorAll('.kdna-remote-arrows[data-kdna-remote-id]');

		for (var i = 0; i < remotes.length; i++) {
			bindRemote(remotes[i]);
		}
	}

	/**
	 * Bind click handlers on a single remote arrows container.
	 */
	function bindRemote(remote) {
		if (remote._kdnaRemoteBound) return;
		remote._kdnaRemoteBound = true;

		var connectionId = remote.getAttribute('data-kdna-remote-id');
		if (!connectionId) return;

		var prevBtn = remote.querySelector('.kdna-remote-arrows__btn--prev');
		var nextBtn = remote.querySelector('.kdna-remote-arrows__btn--next');

		if (prevBtn) {
			prevBtn.addEventListener('click', function () {
				triggerSlide(connectionId, 'prev');
			});
		}

		if (nextBtn) {
			nextBtn.addEventListener('click', function () {
				triggerSlide(connectionId, 'next');
			});
		}
	}

	/**
	 * Find the connected Listing Grid by connection ID and trigger
	 * a Slick prev/next slide, or scroll the scroll-slider container.
	 */
	function triggerSlide(connectionId, direction) {
		var grid = document.querySelector('[data-kdna-connection-id="' + connectionId + '"]');
		if (!grid) return;

		// Slick Slider mode.
		if (typeof jQuery !== 'undefined') {
			var $slider = jQuery(grid).find('.slick-slider');
			if ($slider.length) {
				if (direction === 'prev') {
					$slider.slick('slickPrev');
				} else {
					$slider.slick('slickNext');
				}
				return;
			}
		}

		// Scroll Slider mode — scroll by one item width.
		var scrollContainer = grid.querySelector('.jet-listing-grid__scroll-slider');
		if (scrollContainer) {
			var firstChild = scrollContainer.firstElementChild;
			var scrollAmount = firstChild ? firstChild.offsetWidth : 300;

			if (direction === 'prev') {
				scrollAmount = -scrollAmount;
			}

			// Flip direction for RTL.
			if (isRTL) {
				scrollAmount = -scrollAmount;
			}

			scrollContainer.scrollBy({ left: scrollAmount, behavior: 'smooth' });
		}
	}

	/* -----------------------------------------------------------------
	 * Elementor editor panel: listen for setting changes so the preview
	 * updates in real time as the user toggles controls.
	 * ----------------------------------------------------------------- */
	if (typeof window.elementor !== 'undefined') {
		window.elementor.on('document:loaded', function () {
			setTimeout(initAllPeekWidgets, 300);
		});
	} else if (typeof jQuery !== 'undefined') {
		// elementor may not be available yet — wait for it.
		jQuery(window).on('elementor:init', function () {
			if (typeof window.elementor !== 'undefined') {
				window.elementor.on('document:loaded', function () {
					setTimeout(initAllPeekWidgets, 300);
				});
			}
		});
	}

})();
