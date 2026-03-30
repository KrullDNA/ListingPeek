<?php
/**
 * Elementor extension that injects peek-effect controls into the
 * JetEngine Listing Grid widget's Content tab.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KDNA_Listing_Peek_Elementor_Extension {

	/**
	 * Wire up Elementor hooks for controls and render-time attributes.
	 */
	public function __construct() {
		// Add controls after the Listing Grid's General section.
		add_action(
			'elementor/element/jet-listing-grid/section_general/after_section_end',
			array( $this, 'register_controls' ),
			10,
			2
		);

		// Inject CSS class and data attributes at render time.
		add_action(
			'elementor/frontend/widget/before_render',
			array( $this, 'before_render' )
		);
	}

	/**
	 * Register the "KDNA Listing Peek" controls section and its controls
	 * inside the JetEngine Listing Grid widget.
	 *
	 * @param \Elementor\Widget_Base $element The widget instance.
	 * @param array                  $args    Section arguments.
	 */
	public function register_controls( $element, $args ) {
		$element->start_controls_section(
			'kdna_listing_peek_section',
			array(
				'label' => __( 'KDNA Listing Peek', 'kdna-listing-peek' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		// Master toggle — default is off so existing grids are unaffected.
		$element->add_control(
			'kdna_peek_enable',
			array(
				'label'        => __( 'Enable Peek Effect', 'kdna-listing-peek' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'kdna-listing-peek' ),
				'label_off'    => __( 'Off', 'kdna-listing-peek' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		// Peek width with responsive defaults: 60 / 40 / 30.
		$element->add_responsive_control(
			'kdna_peek_width',
			array(
				'label'      => __( 'Peek Width (px)', 'kdna-listing-peek' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 200,
					),
				),
				'default'    => array(
					'size' => 60,
					'unit' => 'px',
				),
				'tablet_default' => array(
					'size' => 40,
					'unit' => 'px',
				),
				'mobile_default' => array(
					'size' => 30,
					'unit' => 'px',
				),
				'condition'  => array(
					'kdna_peek_enable' => 'yes',
				),
			)
		);

		// Fade edge toggle — on by default when peek is enabled.
		$element->add_control(
			'kdna_peek_fade',
			array(
				'label'        => __( 'Fade Edge', 'kdna-listing-peek' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'kdna-listing-peek' ),
				'label_off'    => __( 'Off', 'kdna-listing-peek' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'kdna_peek_enable' => 'yes',
				),
			)
		);

		// Fade width — only relevant when fade is on.
		$element->add_control(
			'kdna_peek_fade_width',
			array(
				'label'      => __( 'Fade Width (px)', 'kdna-listing-peek' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'size' => 40,
					'unit' => 'px',
				),
				'condition'  => array(
					'kdna_peek_enable' => 'yes',
					'kdna_peek_fade'   => 'yes',
				),
			)
		);

		$element->add_control(
			'kdna_peek_connection_heading',
			array(
				'label'     => __( 'Remote Arrows', 'kdna-listing-peek' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Connection ID — shared with the Remote Arrows widget.
		$element->add_control(
			'kdna_peek_connection_id',
			array(
				'label'       => __( 'Connection ID', 'kdna-listing-peek' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => __( 'e.g. my-slider-1', 'kdna-listing-peek' ),
				'description' => __( 'Enter the same ID on a KDNA Remote Arrows widget to control this slider remotely.', 'kdna-listing-peek' ),
				'label_block' => true,
			)
		);

		// Option to hide the default built-in arrows.
		$element->add_control(
			'kdna_peek_hide_arrows',
			array(
				'label'        => __( 'Hide Default Arrows', 'kdna-listing-peek' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'kdna-listing-peek' ),
				'label_off'    => __( 'No', 'kdna-listing-peek' ),
				'return_value' => 'yes',
				'default'      => '',
				'description'  => __( 'Hide the Listing Grid\'s built-in arrow buttons.', 'kdna-listing-peek' ),
			)
		);

		$element->end_controls_section();
	}

	/**
	 * At render time, add the peek CSS class and data attributes to the
	 * widget wrapper so front-end CSS/JS can pick them up.
	 *
	 * @param \Elementor\Widget_Base $widget The widget being rendered.
	 */
	public function before_render( $widget ) {
		// Only act on the Listing Grid widget.
		if ( 'jet-listing-grid' !== $widget->get_name() ) {
			return;
		}

		$settings = $widget->get_settings_for_display();

		// --- Connection ID and Hide Default Arrows (work independently of peek) ---
		$connection_id = ! empty( $settings['kdna_peek_connection_id'] )
			? sanitize_title( $settings['kdna_peek_connection_id'] )
			: '';

		if ( $connection_id ) {
			$widget->add_render_attribute( '_wrapper', 'data-kdna-connection-id', $connection_id );
		}

		$hide_arrows = ( ! empty( $settings['kdna_peek_hide_arrows'] ) && 'yes' === $settings['kdna_peek_hide_arrows'] );
		if ( $hide_arrows ) {
			$widget->add_render_attribute( '_wrapper', 'class', 'kdna-peek-hide-arrows' );
		}

		// --- Peek effect (only when enabled) ---
		if ( empty( $settings['kdna_peek_enable'] ) || 'yes' !== $settings['kdna_peek_enable'] ) {
			return;
		}

		// Resolve responsive peek-width values.
		$desktop = isset( $settings['kdna_peek_width']['size'] )
			? (int) $settings['kdna_peek_width']['size']
			: 60;

		$tablet = isset( $settings['kdna_peek_width_tablet']['size'] )
			? (int) $settings['kdna_peek_width_tablet']['size']
			: 40;

		$mobile = isset( $settings['kdna_peek_width_mobile']['size'] )
			? (int) $settings['kdna_peek_width_mobile']['size']
			: 30;

		// Fade settings.
		$fade       = ( ! empty( $settings['kdna_peek_fade'] ) && 'yes' === $settings['kdna_peek_fade'] );
		$fade_width = isset( $settings['kdna_peek_fade_width']['size'] )
			? (int) $settings['kdna_peek_fade_width']['size']
			: 40;

		// Add CSS class.
		$widget->add_render_attribute( '_wrapper', 'class', 'kdna-peek-active' );

		if ( $fade ) {
			$widget->add_render_attribute( '_wrapper', 'class', 'kdna-peek-fade' );
		}

		// Add data attributes for JS to read.
		$widget->add_render_attribute( '_wrapper', array(
			'data-kdna-peek-width'        => $desktop,
			'data-kdna-peek-width-tablet'  => $tablet,
			'data-kdna-peek-width-mobile'  => $mobile,
			'data-kdna-peek-fade'          => $fade ? 'yes' : 'no',
			'data-kdna-peek-fade-width'    => $fade_width,
		) );
	}
}
