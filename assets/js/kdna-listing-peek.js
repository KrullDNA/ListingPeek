/**
 * KDNA Listing Peek — front-end script
 *
 * Reads data attributes from .kdna-peek-active widgets, sets CSS custom
 * properties per breakpoint, handles resize, and supports both Slick
 * Slider and Scroll Slider modes.
 */
(function () {
	'use strict';

	/* -----------------------------------------------------------------
	 * Breakpoint thresholds (overridden by wp_localize_script if available)
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
	 * Determine current breakpoint name based on window width
	 * ----------------------------------------------------------------- */
	function getBreakpoint() {
		var w = window.innerWidth;
		if (w <= BP_MOBILE) return 'mobile';
		if (w <= BP_TABLET) return 'tablet';
		return 'desktop';
	}

	/* -----------------------------------------------------------------
	 * Apply peek CSS custom properties and classes to a single widget
	 * ----------------------------------------------------------------- */
	function applyPeek(el) {
		var bp = getBreakpoint();

		// Read data attributes.
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
	}

	/* -----------------------------------------------------------------
	 * Initialise all peek widgets on the page
	 * ----------------------------------------------------------------- */
	function initAllPeekWidgets() {
		var widgets = document.querySelectorAll('.kdna-peek-active');

		for (var i = 0; i < widgets.length; i++) {
			applyPeek(widgets[i]);
			bindSlickEvents(widgets[i]);
		}
	}

	/* -----------------------------------------------------------------
	 * Bind Slick init/reInit events so the peek re-applies after Slick
	 * recalculates its layout. Slick requires jQuery for event binding.
	 * ----------------------------------------------------------------- */
	function bindSlickEvents(el) {
		// Avoid binding more than once per element.
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
	 * Recalculate all widgets (called on debounced resize)
	 * ----------------------------------------------------------------- */
	function refreshAllPeekWidgets() {
		var widgets = document.querySelectorAll('.kdna-peek-active');
		for (var i = 0; i < widgets.length; i++) {
			applyPeek(widgets[i]);
		}
	}

	/* -----------------------------------------------------------------
	 * Window resize handler (debounced at 150ms)
	 * ----------------------------------------------------------------- */
	window.addEventListener('resize', debounce(refreshAllPeekWidgets, 150));

	/* -----------------------------------------------------------------
	 * Bootstrap: run on DOMContentLoaded
	 * ----------------------------------------------------------------- */
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAllPeekWidgets);
	} else {
		// DOM already ready (script loaded with defer or late).
		initAllPeekWidgets();
	}

	/* -----------------------------------------------------------------
	 * Bootstrap: run on Elementor frontend/init for editor preview support
	 * ----------------------------------------------------------------- */
	if (typeof jQuery !== 'undefined') {
		jQuery(window).on('elementor/frontend/init', function () {
			// Small delay to let Elementor render widgets first.
			setTimeout(initAllPeekWidgets, 100);
		});
	}

})();
