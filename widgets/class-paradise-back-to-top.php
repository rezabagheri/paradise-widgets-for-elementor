<?php
/**
 * Paradise Back to Top Button Widget
 *
 * A fixed-position button that appears after scrolling past a threshold
 * and smoothly scrolls the page back to the top when clicked.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;

class Paradise_Back_To_Top_Widget extends Paradise_Widget_Base {

    public function get_name(): string    { return 'paradise_back_to_top'; }
    public function get_title(): string   { return esc_html__( 'Back to Top', 'paradise-widgets-for-elementor' ); }
    public function get_icon(): string    { return 'eicon-arrow-up'; }
    public function get_keywords(): array { return [ 'back', 'top', 'scroll', 'up', 'button', 'float' ]; }

    // get_categories() and get_style_depends() come from the base — defaults
    // match (paradise category, 'paradise-back-to-top' style handle).
    // Override get_script_depends() because this widget ships a JS file
    // for scroll-threshold visibility toggling and smooth-scroll behaviour.
    public function get_script_depends(): array { return [ $this->get_default_handle() ]; }

    // =========================================================================
    // CONTROLS
    // =========================================================================

    protected function register_controls(): void {
        $this->section_button();
        $this->section_style_button();
        $this->section_style_animation();
    }

    // ── Content: Button ───────────────────────────────────────────────────────

    private function section_button(): void {
        $this->start_controls_section( 'section_button', [
            'label' => esc_html__( 'Button', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'selected_icon', [
            'label'   => esc_html__( 'Icon', 'paradise-widgets-for-elementor' ),
            'type'    => Controls_Manager::ICONS,
            'default' => [ 'value' => 'eicon-chevron-up', 'library' => 'eicons' ],
        ] );

        $this->add_control( 'aria_label', [
            'label'     => esc_html__( 'Accessibility Label', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::TEXT,
            'default'   => esc_html__( 'Back to top', 'paradise-widgets-for-elementor' ),
            'separator' => 'before',
        ] );

        $this->add_control( 'scroll_threshold', [
            'label'       => esc_html__( 'Scroll Threshold (px)', 'paradise-widgets-for-elementor' ),
            'type'        => Controls_Manager::NUMBER,
            'default'     => 300,
            'min'         => 50,
            'description' => esc_html__( 'Pixels to scroll before the button becomes visible.', 'paradise-widgets-for-elementor' ),
            'separator'   => 'before',
        ] );

        $this->end_controls_section();
    }

    // ── Style: Button ─────────────────────────────────────────────────────────

    private function section_style_button(): void {
        $this->start_controls_section( 'section_style_button', [
            'label' => esc_html__( 'Button', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'corner', [
            'label'        => esc_html__( 'Corner', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::CHOOSE,
            'default'      => 'bottom-right',
            'options'      => [
                'bottom-left'  => [ 'title' => esc_html__( 'Bottom Left',  'paradise-widgets-for-elementor' ), 'icon' => 'eicon-h-align-left' ],
                'bottom-right' => [ 'title' => esc_html__( 'Bottom Right', 'paradise-widgets-for-elementor' ), 'icon' => 'eicon-h-align-right' ],
            ],
            'prefix_class' => 'paradise-btt-corner-',
            'render_type'  => 'template',
        ] );

        $this->add_responsive_control( 'bottom_offset', [
            'label'      => esc_html__( 'Distance from Bottom', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 8, 'max' => 200 ] ],
            'default'    => [ 'size' => 24, 'unit' => 'px' ],
            'selectors'  => [ '{{WRAPPER}} .paradise-btt-btn' => '--paradise-btt-bottom: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->add_responsive_control( 'side_offset', [
            'label'      => esc_html__( 'Distance from Side', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 8, 'max' => 200 ] ],
            'default'    => [ 'size' => 24, 'unit' => 'px' ],
            'selectors'  => [ '{{WRAPPER}} .paradise-btt-btn' => '--paradise-btt-side: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->add_control( 'z_index', [
            'label'     => esc_html__( 'Z-Index', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::NUMBER,
            'default'   => 9970,
            'selectors' => [ '{{WRAPPER}} .paradise-btt-btn' => 'z-index: {{VALUE}};' ],
        ] );

        $this->add_responsive_control( 'button_size', [
            'label'      => esc_html__( 'Size', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 28, 'max' => 100 ] ],
            'default'    => [ 'size' => 48, 'unit' => 'px' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-btt-btn' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ],
            'separator'  => 'before',
        ] );

        $this->add_responsive_control( 'icon_size', [
            'label'      => esc_html__( 'Icon Size', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'em' ],
            'range'      => [ 'px' => [ 'min' => 10, 'max' => 60 ] ],
            'default'    => [ 'size' => 20, 'unit' => 'px' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-btt-btn i'   => 'font-size: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .paradise-btt-btn svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->add_responsive_control( 'border_radius', [
            'label'      => esc_html__( 'Border Radius', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%' ],
            'default'    => [ 'top' => 50, 'right' => 50, 'bottom' => 50, 'left' => 50, 'unit' => '%', 'isLinked' => true ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-btt-btn' =>
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->add_group_control( Group_Control_Box_Shadow::get_type(), [
            'name'     => 'button_shadow',
            'selector' => '{{WRAPPER}} .paradise-btt-btn',
        ] );

        $this->start_controls_tabs( 'button_tabs', [ 'separator' => 'before' ] );

            $this->start_controls_tab( 'button_tab_normal', [
                'label' => esc_html__( 'Normal', 'paradise-widgets-for-elementor' ),
            ] );

            $this->add_control( 'bg_color', [
                'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#333333',
                'selectors' => [ '{{WRAPPER}} .paradise-btt-btn' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'icon_color', [
                'label'     => esc_html__( 'Icon Color', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .paradise-btt-btn i'   => 'color: {{VALUE}};',
                    '{{WRAPPER}} .paradise-btt-btn svg' => 'fill: {{VALUE}};',
                ],
            ] );

            $this->end_controls_tab();

            $this->start_controls_tab( 'button_tab_hover', [
                'label' => esc_html__( 'Hover', 'paradise-widgets-for-elementor' ),
            ] );

            $this->add_control( 'bg_color_hover', [
                'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .paradise-btt-btn:hover' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'icon_color_hover', [
                'label'     => esc_html__( 'Icon Color', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .paradise-btt-btn:hover i'   => 'color: {{VALUE}};',
                    '{{WRAPPER}} .paradise-btt-btn:hover svg' => 'fill: {{VALUE}};',
                ],
            ] );

            $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    // ── Style: Animation ──────────────────────────────────────────────────────

    private function section_style_animation(): void {
        $this->start_controls_section( 'section_style_animation', [
            'label' => esc_html__( 'Animation', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'show_animation', [
            'label'   => esc_html__( 'Show Animation', 'paradise-widgets-for-elementor' ),
            'type'    => Controls_Manager::SELECT,
            'default' => 'fade-slide',
            'options' => [
                'fade'       => esc_html__( 'Fade',           'paradise-widgets-for-elementor' ),
                'fade-slide' => esc_html__( 'Fade + Slide Up', 'paradise-widgets-for-elementor' ),
            ],
            'prefix_class' => 'paradise-btt-anim-',
        ] );

        $this->add_control( 'animation_duration', [
            'label'      => esc_html__( 'Duration (ms)', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::NUMBER,
            'default'    => 300,
            'min'        => 100,
            'max'        => 1000,
            'selectors'  => [ '{{WRAPPER}} .paradise-btt-btn' => '--paradise-btt-duration: {{VALUE}}ms;' ],
        ] );

        $this->end_controls_section();
    }

    // =========================================================================
    // RENDER
    // =========================================================================

    protected function render(): void {
        $settings  = $this->get_settings_for_display();
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();

        $icon      = $settings['selected_icon'] ?? [];
        $aria      = trim( $settings['aria_label'] ?? 'Back to top' );
        $threshold = absint( $settings['scroll_threshold'] ?? 300 );

        $icon_html = '';
        if ( ! empty( $icon['value'] ) ) {
            ob_start();
            \Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
            $icon_html = ob_get_clean();
        }

        $data = sprintf(
            ' data-btt-threshold="%d"%s',
            $threshold,
            $is_editor ? ' data-btt-edit="true"' : ''
        );

        ?>
        <button class="paradise-btt-btn"
                <?php echo $data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped markup assembled above ?>
                aria-label="<?php echo esc_attr( $aria ); ?>"
                title="<?php echo esc_attr( $aria ); ?>"
                type="button">
            <?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor-rendered icon HTML ?>
        </button>

        <?php if ( $is_editor ) : ?>
        <div class="paradise-btt-editor-notice">
            <svg viewBox="0 0 20 20" width="16" height="16" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0Zm-8-5a1 1 0 0 1 1 1v4a1 1 0 1 1-2 0V6a1 1 0 0 1 1-1Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
            </svg>
            <span>
                <?php esc_html_e( 'Back to Top Button', 'paradise-widgets-for-elementor' ); ?>
                &mdash;
                <?php esc_html_e( 'appears after scrolling', 'paradise-widgets-for-elementor' ); ?>
                <strong><?php echo esc_html( $threshold ); ?>px</strong>
            </span>
        </div>
        <?php endif; ?>
        <?php
    }
}
