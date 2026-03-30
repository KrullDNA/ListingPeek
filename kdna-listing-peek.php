<?php
/**
 * Plugin Name: KDNA Listing Peek
 * Description: Adds a half-card peek effect to CrocoBlock JetEngine Listing Grid sliders.
 * Version:     1.0.0
 * Author:      KDNA
 * Text Domain: kdna-listing-peek
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KDNA_LISTING_PEEK_VERSION', '1.0.0' );
define( 'KDNA_LISTING_PEEK_PATH', plugin_dir_path( __FILE__ ) );
define( 'KDNA_LISTING_PEEK_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check that Elementor and JetEngine are both active before doing anything.
 * If either is missing, show an admin notice and bail — do not deactivate.
 */
function kdna_listing_peek_check_dependencies() {
	$missing = array();

	if ( ! did_action( 'elementor/loaded' ) ) {
		$missing[] = 'Elementor';
	}

	if ( ! class_exists( 'Jet_Engine' ) ) {
		$missing[] = 'JetEngine';
	}

	if ( ! empty( $missing ) ) {
		add_action( 'admin_notices', function () use ( $missing ) {
			$list = implode( ' and ', $missing );
			printf(
				'<div class="notice notice-error"><p><strong>KDNA Listing Peek</strong> requires %s to be installed and activated.</p></div>',
				esc_html( $list )
			);
		} );
		return false;
	}

	return true;
}

/**
 * Bootstrap the plugin after all plugins have loaded.
 */
function kdna_listing_peek_init() {
	if ( ! kdna_listing_peek_check_dependencies() ) {
		return;
	}

	// Load the Elementor extension once Elementor is ready.
	add_action( 'elementor/init', 'kdna_listing_peek_load_extension' );

	// Enqueue front-end assets only when needed.
	add_action( 'wp_enqueue_scripts', 'kdna_listing_peek_enqueue_assets' );
}
add_action( 'plugins_loaded', 'kdna_listing_peek_init' );

/**
 * Load the Elementor extension class that registers our controls.
 */
function kdna_listing_peek_load_extension() {
	require_once KDNA_LISTING_PEEK_PATH . 'includes/class-elementor-extension.php';
	new KDNA_Listing_Peek_Elementor_Extension();
}

/**
 * Register front-end CSS and JS. They are enqueued only when a page contains
 * a JetEngine Listing Grid (detected via shortcode or Elementor widget data).
 */
function kdna_listing_peek_enqueue_assets() {
	// Register assets so they can be enqueued conditionally.
	wp_register_style(
		'kdna-listing-peek',
		KDNA_LISTING_PEEK_URL . 'assets/css/kdna-listing-peek.css',
		array(),
		KDNA_LISTING_PEEK_VERSION
	);

	wp_register_script(
		'kdna-listing-peek',
		KDNA_LISTING_PEEK_URL . 'assets/js/kdna-listing-peek.js',
		array( 'jquery' ),
		KDNA_LISTING_PEEK_VERSION,
		true
	);

	// Pass Elementor breakpoints to JS when available.
	$breakpoints = array(
		'tablet' => 1024,
		'mobile' => 767,
	);

	if ( class_exists( '\Elementor\Plugin' ) ) {
		$kit_manager = \Elementor\Plugin::$instance->kits_manager ?? null;
		if ( $kit_manager ) {
			$active_kit      = $kit_manager->get_active_kit_for_frontend();
			$tablet_bp       = $active_kit->get_settings( 'viewport_tablet' );
			$mobile_bp       = $active_kit->get_settings( 'viewport_mobile' );
			$breakpoints['tablet'] = $tablet_bp ? (int) $tablet_bp : 1024;
			$breakpoints['mobile'] = $mobile_bp ? (int) $mobile_bp : 767;
		}
	}

	wp_localize_script( 'kdna-listing-peek', 'kdnaListingPeek', array(
		'breakpoints' => $breakpoints,
	) );

	// Determine whether the current page needs the assets.
	if ( kdna_listing_peek_should_enqueue() ) {
		wp_enqueue_style( 'kdna-listing-peek' );
		wp_enqueue_script( 'kdna-listing-peek' );
	}
}

/**
 * Decide whether to enqueue assets on the current page.
 * Returns true if the post content contains a Listing Grid shortcode
 * or if Elementor data includes the jet-listing-grid widget.
 */
function kdna_listing_peek_should_enqueue() {
	// Always load in Elementor preview / editor context.
	if ( class_exists( '\Elementor\Plugin' ) ) {
		if ( \Elementor\Plugin::$instance->preview->is_preview_mode()
			|| \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			return true;
		}
	}

	global $post;

	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	// Check for the shortcode in post content.
	if ( has_shortcode( $post->post_content, 'jet_engine_listing_grid' ) ) {
		return true;
	}

	// Check Elementor data for the widget type.
	$elementor_data = get_post_meta( $post->ID, '_elementor_data', true );
	if ( $elementor_data && strpos( $elementor_data, '"widgetType":"jet-listing-grid"' ) !== false ) {
		return true;
	}

	return false;
}
