<?php
/**
 * KDNA Remote Arrows — an Elementor widget that renders prev/next
 * buttons which control a connected JetEngine Listing Grid slider
 * from anywhere on the page via a shared Connection ID.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KDNA_Remote_Arrows_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'kdna-remote-arrows';
	}

	public function get_title() {
		return __( 'KDNA Remote Arrows', 'kdna-listing-peek' );
	}

	public function get_icon() {
		return 'eicon-arrow-left';
	}

	public function get_categories() {
		return array( 'general' );
	}

	public function get_keywords() {
		return array( 'arrows', 'remote', 'slider', 'navigation', 'kdna', 'listing', 'prev', 'next' );
	}

	protected function register_controls() {

		/* ===============================================================
		 * CONTENT TAB — Connection
		 * ============================================================= */
		$this->start_controls_section(
			'kdna_ra_section_connection',
			array(
				'label' => __( 'Connection', 'kdna-listing-peek' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'kdna_ra_connection_id',
			array(
				'label'       => __( 'Connection ID', 'kdna-listing-peek' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => __( 'e.g. my-slider-1', 'kdna-listing-peek' ),
				'description' => __( 'Enter the same Connection ID on the Listing Grid\'s KDNA Listing Peek section to link them.', 'kdna-listing-peek' ),
				'label_block' => true,
			)
		);

		$this->end_controls_section();

		/* ===============================================================
		 * CONTENT TAB — Icons
		 * ============================================================= */
		$this->start_controls_section(
			'kdna_ra_section_icons',
			array(
				'label' => __( 'Icons', 'kdna-listing-peek' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'kdna_ra_icon_prev',
			array(
				'label'   => __( 'Previous Icon', 'kdna-listing-peek' ),
				'type'    => \Elementor\Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-chevron-left',
					'library' => 'fa-solid',
				),
			)
		);

		$this->add_control(
			'kdna_ra_icon_next',
			array(
				'label'   => __( 'Next Icon', 'kdna-listing-peek' ),
				'type'    => \Elementor\Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-chevron-right',
					'library' => 'fa-solid',
				),
			)
		);

		$this->end_controls_section();

		/* ===============================================================
		 * CONTENT TAB — Layout
		 * ============================================================= */
		$this->start_controls_section(
			'kdna_ra_section_layout',
			array(
				'label' => __( 'Layout', 'kdna-listing-peek' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		// Horizontal or vertical orientation.
		$this->add_control(
			'kdna_ra_orientation',
			array(
				'label'   => __( 'Orientation', 'kdna-listing-peek' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => array(
					'row'    => array(
						'title' => __( 'Horizontal', 'kdna-listing-peek' ),
						'icon'  => 'eicon-ellipsis-h',
					),
					'column' => array(
						'title' => __( 'Vertical', 'kdna-listing-peek' ),
						'icon'  => 'eicon-ellipsis-v',
					),
				),
				'default'   => 'row',
				'selectors' => array(
					'{{WRAPPER}} .kdna-remote-arrows' => 'flex-direction: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'kdna_ra_align',
			array(
				'label'   => __( 'Alignment', 'kdna-listing-peek' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => array(
					'flex-start' => array(
						'title' => __( 'Start', 'kdna-listing-peek' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'kdna-listing-peek' ),
						'icon'  => 'eicon-h-align-center',
					),
					'flex-end' => array(
						'title' => __( 'End', 'kdna-listing-peek' ),
						'icon'  => 'eicon-h-align-right',
					),
					'space-between' => array(
						'title' => __( 'Space Between', 'kdna-listing-peek' ),
						'icon'  => 'eicon-h-align-stretch',
					),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .kdna-remote-arrows' => 'justify-content: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'kdna_ra_gap',
			array(
				'label'      => __( 'Gap', 'kdna-listing-peek' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 100 ),
					'em' => array( 'min' => 0, 'max' => 10, 'step' => 0.1 ),
				),
				'default'    => array( 'size' => 12, 'unit' => 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .kdna-remote-arrows' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		/* ===============================================================
		 * STYLE TAB — Arrows
		 * ============================================================= */
		$this->start_controls_section(
			'kdna_ra_style_arrows',
			array(
				'label' => __( 'Arrows', 'kdna-listing-peek' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		// Icon size.
		$this->add_responsive_control(
			'kdna_ra_icon_size',
			array(
				'label'      => __( 'Icon Size', 'kdna-listing-peek' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array( 'min' => 6, 'max' => 100 ),
				),
				'default'    => array( 'size' => 20, 'unit' => 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn i'   => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .kdna-remote-arrows__btn svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// Button width (for square / circle shapes).
		$this->add_responsive_control(
			'kdna_ra_btn_width',
			array(
				'label'      => __( 'Button Width', 'kdna-listing-peek' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array( 'min' => 20, 'max' => 150 ),
				),
				'selectors'  => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// Button height.
		$this->add_responsive_control(
			'kdna_ra_btn_height',
			array(
				'label'      => __( 'Button Height', 'kdna-listing-peek' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array( 'min' => 20, 'max' => 150 ),
				),
				'selectors'  => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// Padding.
		$this->add_responsive_control(
			'kdna_ra_padding',
			array(
				'label'      => __( 'Padding', 'kdna-listing-peek' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Border.
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'kdna_ra_border',
				'selector' => '{{WRAPPER}} .kdna-remote-arrows__btn',
			)
		);

		// Border radius.
		$this->add_responsive_control(
			'kdna_ra_border_radius',
			array(
				'label'      => __( 'Border Radius', 'kdna-listing-peek' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Transition duration.
		$this->add_control(
			'kdna_ra_transition',
			array(
				'label'     => __( 'Transition Duration (ms)', 'kdna-listing-peek' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 0,
				'max'       => 2000,
				'step'      => 50,
				'default'   => 250,
				'selectors' => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn' => 'transition-duration: {{VALUE}}ms;',
				),
				'separator' => 'before',
			)
		);

		/* --- Normal / Hover tabs --- */
		$this->start_controls_tabs( 'kdna_ra_state_tabs' );

		/* Normal state */
		$this->start_controls_tab(
			'kdna_ra_tab_normal',
			array( 'label' => __( 'Normal', 'kdna-listing-peek' ) )
		);

		$this->add_control(
			'kdna_ra_color',
			array(
				'label'     => __( 'Icon Color', 'kdna-listing-peek' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#333333',
				'selectors' => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .kdna-remote-arrows__btn svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'kdna_ra_bg',
			array(
				'label'     => __( 'Background Color', 'kdna-listing-peek' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'kdna_ra_box_shadow',
				'selector' => '{{WRAPPER}} .kdna-remote-arrows__btn',
			)
		);

		$this->end_controls_tab();

		/* Hover state */
		$this->start_controls_tab(
			'kdna_ra_tab_hover',
			array( 'label' => __( 'Hover', 'kdna-listing-peek' ) )
		);

		$this->add_control(
			'kdna_ra_color_hover',
			array(
				'label'     => __( 'Icon Color', 'kdna-listing-peek' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn:hover'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .kdna-remote-arrows__btn:hover svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'kdna_ra_bg_hover',
			array(
				'label'     => __( 'Background Color', 'kdna-listing-peek' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'kdna_ra_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'kdna-listing-peek' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'kdna_ra_box_shadow_hover',
				'selector' => '{{WRAPPER}} .kdna-remote-arrows__btn:hover',
			)
		);

		$this->add_control(
			'kdna_ra_hover_scale',
			array(
				'label'     => __( 'Scale on Hover', 'kdna-listing-peek' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array( 'min' => 0.5, 'max' => 2, 'step' => 0.05 ),
				),
				'selectors' => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn:hover' => 'transform: scale({{SIZE}});',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();

		/* ===============================================================
		 * STYLE TAB — Previous Arrow (individual overrides)
		 * ============================================================= */
		$this->start_controls_section(
			'kdna_ra_style_prev',
			array(
				'label' => __( 'Previous Arrow', 'kdna-listing-peek' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'kdna_ra_prev_note',
			array(
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => __( 'Override styles for the Previous button only. Leave empty to inherit from the shared Arrows styles above.', 'kdna-listing-peek' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->start_controls_tabs( 'kdna_ra_prev_tabs' );

		$this->start_controls_tab(
			'kdna_ra_prev_normal',
			array( 'label' => __( 'Normal', 'kdna-listing-peek' ) )
		);

		$this->add_control(
			'kdna_ra_prev_color',
			array(
				'label'     => __( 'Icon Color', 'kdna-listing-peek' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn--prev'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .kdna-remote-arrows__btn--prev svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'kdna_ra_prev_bg',
			array(
				'label'     => __( 'Background Color', 'kdna-listing-peek' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn--prev' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'kdna_ra_prev_hover',
			array( 'label' => __( 'Hover', 'kdna-listing-peek' ) )
		);

		$this->add_control(
			'kdna_ra_prev_color_hover',
			array(
				'label'     => __( 'Icon Color', 'kdna-listing-peek' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn--prev:hover'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .kdna-remote-arrows__btn--prev:hover svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'kdna_ra_prev_bg_hover',
			array(
				'label'     => __( 'Background Color', 'kdna-listing-peek' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn--prev:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();

		/* ===============================================================
		 * STYLE TAB — Next Arrow (individual overrides)
		 * ============================================================= */
		$this->start_controls_section(
			'kdna_ra_style_next',
			array(
				'label' => __( 'Next Arrow', 'kdna-listing-peek' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'kdna_ra_next_note',
			array(
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => __( 'Override styles for the Next button only. Leave empty to inherit from the shared Arrows styles above.', 'kdna-listing-peek' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->start_controls_tabs( 'kdna_ra_next_tabs' );

		$this->start_controls_tab(
			'kdna_ra_next_normal',
			array( 'label' => __( 'Normal', 'kdna-listing-peek' ) )
		);

		$this->add_control(
			'kdna_ra_next_color',
			array(
				'label'     => __( 'Icon Color', 'kdna-listing-peek' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn--next'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .kdna-remote-arrows__btn--next svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'kdna_ra_next_bg',
			array(
				'label'     => __( 'Background Color', 'kdna-listing-peek' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn--next' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'kdna_ra_next_hover',
			array( 'label' => __( 'Hover', 'kdna-listing-peek' ) )
		);

		$this->add_control(
			'kdna_ra_next_color_hover',
			array(
				'label'     => __( 'Icon Color', 'kdna-listing-peek' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn--next:hover'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .kdna-remote-arrows__btn--next:hover svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'kdna_ra_next_bg_hover',
			array(
				'label'     => __( 'Background Color', 'kdna-listing-peek' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .kdna-remote-arrows__btn--next:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Render the widget HTML on the front end.
	 */
	protected function render() {
		$settings      = $this->get_settings_for_display();
		$connection_id = sanitize_title( $settings['kdna_ra_connection_id'] ?? '' );

		if ( empty( $connection_id ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="kdna-remote-arrows kdna-remote-arrows--empty">'
					. esc_html__( 'Enter a Connection ID to link this to a Listing Grid.', 'kdna-listing-peek' )
					. '</div>';
			}
			return;
		}

		$icon_prev = $settings['kdna_ra_icon_prev'] ?? array();
		$icon_next = $settings['kdna_ra_icon_next'] ?? array();
		?>
		<div class="kdna-remote-arrows" data-kdna-remote-id="<?php echo esc_attr( $connection_id ); ?>">
			<button type="button"
				class="kdna-remote-arrows__btn kdna-remote-arrows__btn--prev"
				aria-label="<?php esc_attr_e( 'Previous', 'kdna-listing-peek' ); ?>">
				<?php \Elementor\Icons_Manager::render_icon( $icon_prev, array( 'aria-hidden' => 'true' ) ); ?>
			</button>
			<button type="button"
				class="kdna-remote-arrows__btn kdna-remote-arrows__btn--next"
				aria-label="<?php esc_attr_e( 'Next', 'kdna-listing-peek' ); ?>">
				<?php \Elementor\Icons_Manager::render_icon( $icon_next, array( 'aria-hidden' => 'true' ) ); ?>
			</button>
		</div>
		<?php
	}
}
