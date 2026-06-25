<?php
/**
 * Paradise Announcement Bar Widget
 *
 * A fixed-position full-width bar for announcements, promotions, or alerts.
 * Supports icon, message, CTA button, and dismissal with session/day/permanent memory.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

class Paradise_Announcement_Bar_Widget extends Paradise_Widget_Base {

    public function get_name(): string    { return 'paradise_announcement_bar'; }
    public function get_title(): string   { return esc_html__( 'Announcement Bar', 'paradise-widgets-for-elementor' ); }
    public function get_icon(): string    { return 'eicon-alert'; }
    public function get_keywords(): array { return [ 'announcement', 'bar', 'notice', 'banner', 'alert', 'promo' ]; }

    // get_categories() and get_style_depends() come from the base — defaults
    // match. Override get_script_depends() because this widget ships a JS
    // file for dismiss behaviour and localStorage memory.
    public function get_script_depends(): array { return [ $this->get_default_handle() ]; }

    // =========================================================================
    // CONTROLS
    // =========================================================================

    protected function register_controls(): void {
        $this->section_message();
        $this->section_dismiss();
        $this->section_style_bar();
        $this->section_style_message();
        $this->section_style_cta();
        $this->section_style_icon();
        $this->section_style_close();
    }

    // ── Content: Message ──────────────────────────────────────────────────────

    private function section_message(): void {
        $this->start_controls_section( 'section_message', [
            'label' => esc_html__( 'Message', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'selected_icon', [
            'label'   => esc_html__( 'Icon', 'paradise-widgets-for-elementor' ),
            'type'    => Controls_Manager::ICONS,
            'default' => [ 'value' => '', 'library' => '' ],
        ] );

        $this->add_control( 'message', [
            'label'       => esc_html__( 'Message', 'paradise-widgets-for-elementor' ),
            'type'        => Controls_Manager::TEXTAREA,
            'default'     => esc_html__( 'Special offer — limited time only! 🎉', 'paradise-widgets-for-elementor' ),
            'dynamic'     => [ 'active' => true ],
            'rows'        => 3,
            'separator'   => 'before',
        ] );

        $this->add_control( 'cta_text', [
            'label'       => esc_html__( 'Button Text', 'paradise-widgets-for-elementor' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__( 'Learn More', 'paradise-widgets-for-elementor' ),
            'dynamic'     => [ 'active' => true ],
            'separator'   => 'before',
        ] );

        $this->add_control( 'cta_url', [
            'label'         => esc_html__( 'Button URL', 'paradise-widgets-for-elementor' ),
            'type'          => Controls_Manager::URL,
            'dynamic'       => [ 'active' => true ],
            'placeholder'   => __( 'https://', 'paradise-widgets-for-elementor' ),
            'condition'     => [ 'cta_text!' => '' ],
        ] );

        $this->end_controls_section();
    }

    // ── Content: Dismiss ──────────────────────────────────────────────────────

    private function section_dismiss(): void {
        $this->start_controls_section( 'section_dismiss', [
            'label' => esc_html__( 'Dismiss', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'show_close', [
            'label'        => esc_html__( 'Show Close Button', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'dismiss_duration', [
            'label'       => esc_html__( 'Remember Dismissal', 'paradise-widgets-for-elementor' ),
            'type'        => Controls_Manager::SELECT,
            'default'     => 'session',
            'options'     => [
                'session' => esc_html__( 'Until browser closes (session)', 'paradise-widgets-for-elementor' ),
                'days'    => esc_html__( 'For X days', 'paradise-widgets-for-elementor' ),
                'forever' => esc_html__( 'Forever', 'paradise-widgets-for-elementor' ),
            ],
            'condition'   => [ 'show_close' => 'yes' ],
            'description' => esc_html__( 'How long a visitor’s close stays remembered. This is stored in their own browser (localStorage), per device — clearing browser data or using another device shows the bar again.', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'dismiss_days', [
            'label'     => esc_html__( 'Days', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::NUMBER,
            'default'   => 7,
            'min'       => 1,
            'max'       => 365,
            'condition' => [ 'show_close' => 'yes', 'dismiss_duration' => 'days' ],
        ] );

        $this->add_control( 'bar_id', [
            'label'       => esc_html__( 'Unique Bar ID', 'paradise-widgets-for-elementor' ),
            'type'        => Controls_Manager::TEXT,
            'placeholder' => esc_html__( 'e.g. summer-promo-2024', 'paradise-widgets-for-elementor' ),
            'description' => esc_html__( 'Optional. Set a custom ID if you want the same bar across pages to share its dismissed state. Leave blank to use the widget ID.', 'paradise-widgets-for-elementor' ),
            'condition'   => [ 'show_close' => 'yes' ],
            'separator'   => 'before',
        ] );

        $this->end_controls_section();
    }

    // ── Style: Bar ────────────────────────────────────────────────────────────

    private function section_style_bar(): void {
        $this->start_controls_section( 'section_style_bar', [
            'label' => esc_html__( 'Bar', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'bar_position', [
            'label'        => esc_html__( 'Position', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::CHOOSE,
            'default'      => 'top',
            'options'      => [
                'top'    => [ 'title' => esc_html__( 'Top',    'paradise-widgets-for-elementor' ), 'icon' => 'eicon-v-align-top' ],
                'bottom' => [ 'title' => esc_html__( 'Bottom', 'paradise-widgets-for-elementor' ), 'icon' => 'eicon-v-align-bottom' ],
            ],
            'prefix_class' => 'paradise-ab-pos-',
            'render_type'  => 'template',
        ] );

        $this->add_control( 'z_index', [
            'label'     => esc_html__( 'Z-Index', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::NUMBER,
            'default'   => 9990,
            'selectors' => [ '{{WRAPPER}} .paradise-ab-wrap' => 'z-index: {{VALUE}};' ],
        ] );

        $this->add_control( 'bar_bg', [
            'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#2d3e50',
            'selectors' => [ '{{WRAPPER}} .paradise-ab-wrap' => 'background-color: {{VALUE}};' ],
            'separator' => 'before',
        ] );

        $this->add_responsive_control( 'bar_padding', [
            'label'      => esc_html__( 'Padding', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em', 'rem' ],
            'default'    => [ 'top' => 12, 'right' => 24, 'bottom' => 12, 'left' => 24, 'unit' => 'px', 'isLinked' => false ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-ab-inner' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->add_responsive_control( 'content_max_width', [
            'label'      => esc_html__( 'Content Max Width', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%' ],
            'range'      => [ 'px' => [ 'min' => 400, 'max' => 1600 ] ],
            'default'    => [ 'size' => 1200, 'unit' => 'px' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-ab-inner' => 'max-width: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->add_responsive_control( 'content_align', [
            'label'     => esc_html__( 'Content Alignment', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::CHOOSE,
            'options'   => [
                'flex-start' => [ 'title' => esc_html__( 'Left',   'paradise-widgets-for-elementor' ), 'icon' => 'eicon-text-align-left' ],
                'center'     => [ 'title' => esc_html__( 'Center', 'paradise-widgets-for-elementor' ), 'icon' => 'eicon-text-align-center' ],
                'flex-end'   => [ 'title' => esc_html__( 'Right',  'paradise-widgets-for-elementor' ), 'icon' => 'eicon-text-align-right' ],
            ],
            'default'   => 'center',
            'selectors' => [
                '{{WRAPPER}} .paradise-ab-inner' => 'justify-content: {{VALUE}};',
            ],
        ] );

        $this->end_controls_section();
    }

    // ── Style: Message ────────────────────────────────────────────────────────

    private function section_style_message(): void {
        $this->start_controls_section( 'section_style_message', [
            'label' => esc_html__( 'Message', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'message_color', [
            'label'     => esc_html__( 'Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [ '{{WRAPPER}} .paradise-ab-message' => 'color: {{VALUE}};' ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'     => 'message_typography',
            'selector' => '{{WRAPPER}} .paradise-ab-message',
        ] );

        $this->end_controls_section();
    }

    // ── Style: CTA Button ─────────────────────────────────────────────────────

    private function section_style_cta(): void {
        $this->start_controls_section( 'section_style_cta', [
            'label'     => esc_html__( 'Button', 'paradise-widgets-for-elementor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'cta_text!' => '' ],
        ] );

        $this->start_controls_tabs( 'cta_tabs' );

            $this->start_controls_tab( 'cta_tab_normal', [
                'label' => esc_html__( 'Normal', 'paradise-widgets-for-elementor' ),
            ] );

            $this->add_control( 'cta_bg', [
                'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [ '{{WRAPPER}} .paradise-ab-cta' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'cta_color', [
                'label'     => esc_html__( 'Text Color', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#2d3e50',
                'selectors' => [ '{{WRAPPER}} .paradise-ab-cta' => 'color: {{VALUE}};' ],
            ] );

            $this->end_controls_tab();

            $this->start_controls_tab( 'cta_tab_hover', [
                'label' => esc_html__( 'Hover', 'paradise-widgets-for-elementor' ),
            ] );

            $this->add_control( 'cta_bg_hover', [
                'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .paradise-ab-cta:hover' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'cta_color_hover', [
                'label'     => esc_html__( 'Text Color', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .paradise-ab-cta:hover' => 'color: {{VALUE}};' ],
            ] );

            $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control( 'cta_border_radius', [
            'label'      => esc_html__( 'Border Radius', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%' ],
            'default'    => [ 'top' => 4, 'right' => 4, 'bottom' => 4, 'left' => 4, 'unit' => 'px', 'isLinked' => true ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-ab-cta' =>
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
            'separator'  => 'before',
        ] );

        $this->add_responsive_control( 'cta_padding', [
            'label'      => esc_html__( 'Padding', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em' ],
            'default'    => [ 'top' => 6, 'right' => 16, 'bottom' => 6, 'left' => 16, 'unit' => 'px', 'isLinked' => false ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-ab-cta' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'      => 'cta_typography',
            'selector'  => '{{WRAPPER}} .paradise-ab-cta',
            'separator' => 'before',
        ] );

        $this->end_controls_section();
    }

    // ── Style: Icon ───────────────────────────────────────────────────────────

    private function section_style_icon(): void {
        $this->start_controls_section( 'section_style_icon', [
            'label'     => esc_html__( 'Icon', 'paradise-widgets-for-elementor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'selected_icon[value]!' => '' ],
        ] );

        $this->add_responsive_control( 'icon_size', [
            'label'      => esc_html__( 'Size', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'em' ],
            'range'      => [ 'px' => [ 'min' => 10, 'max' => 60 ] ],
            'default'    => [ 'size' => 18, 'unit' => 'px' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-ab-icon i'   => 'font-size: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .paradise-ab-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->add_control( 'icon_color', [
            'label'     => esc_html__( 'Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [
                '{{WRAPPER}} .paradise-ab-icon i'   => 'color: {{VALUE}};',
                '{{WRAPPER}} .paradise-ab-icon svg' => 'fill: {{VALUE}};',
            ],
        ] );

        $this->add_responsive_control( 'icon_gap', [
            'label'      => esc_html__( 'Gap', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 32 ] ],
            'default'    => [ 'size' => 8, 'unit' => 'px' ],
            'selectors'  => [ '{{WRAPPER}} .paradise-ab-icon' => 'margin-inline-end: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->end_controls_section();
    }

    // ── Style: Close Button ───────────────────────────────────────────────────

    private function section_style_close(): void {
        $this->start_controls_section( 'section_style_close', [
            'label'     => esc_html__( 'Close Button', 'paradise-widgets-for-elementor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'show_close' => 'yes' ],
        ] );

        $this->add_responsive_control( 'close_size', [
            'label'      => esc_html__( 'Size', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 12, 'max' => 40 ] ],
            'default'    => [ 'size' => 18, 'unit' => 'px' ],
            'selectors'  => [ '{{WRAPPER}} .paradise-ab-close' => 'font-size: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->add_control( 'close_color', [
            'label'     => esc_html__( 'Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => 'rgba(255,255,255,0.7)',
            'selectors' => [ '{{WRAPPER}} .paradise-ab-close' => 'color: {{VALUE}};' ],
        ] );

        $this->add_control( 'close_color_hover', [
            'label'     => esc_html__( 'Hover Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [ '{{WRAPPER}} .paradise-ab-close:hover' => 'color: {{VALUE}};' ],
        ] );

        $this->end_controls_section();
    }

    // =========================================================================
    // RENDER
    // =========================================================================

    protected function render(): void {
        $settings = $this->get_settings_for_display();
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();

        $message  = $settings['message'] ?? '';
        $cta_text = trim( $settings['cta_text'] ?? '' );
        $show_close = 'yes' === ( $settings['show_close'] ?? 'yes' );

        // Dismiss data
        $bar_id  = trim( $settings['bar_id'] ?? '' ) ?: $this->get_id();
        $duration = $settings['dismiss_duration'] ?? 'session';
        $days     = absint( $settings['dismiss_days'] ?? 7 );

        // CTA link — collect rel tokens into a single rel attribute (two separate
        // rel="" attributes would make the browser ignore all but the first, so
        // an external + nofollow link would silently drop the nofollow).
        $cta_url    = $settings['cta_url']['url'] ?? '';
        $cta_target = ! empty( $settings['cta_url']['is_external'] ) ? ' target="_blank"' : '';
        $cta_rel_tokens = [];
        if ( ! empty( $settings['cta_url']['is_external'] ) ) {
            $cta_rel_tokens[] = 'noopener';
            $cta_rel_tokens[] = 'noreferrer';
        }
        if ( ! empty( $settings['cta_url']['nofollow'] ) ) {
            $cta_rel_tokens[] = 'nofollow';
        }
        $cta_nofollow = $cta_rel_tokens ? ' rel="' . esc_attr( implode( ' ', $cta_rel_tokens ) ) . '"' : '';

        // Data attrs for JS
        $data = sprintf(
            ' data-ab-id="%s" data-ab-duration="%s" data-ab-days="%d"%s',
            esc_attr( $bar_id ),
            esc_attr( $duration ),
            $days,
            $is_editor ? ' data-ab-edit="true"' : ''
        );

        // Icon
        $icon_html = '';
        $icon = $settings['selected_icon'] ?? [];
        if ( ! empty( $icon['value'] ) ) {
            ob_start();
            \Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
            $icon_html = '<span class="paradise-ab-icon" aria-hidden="true">' . ob_get_clean() . '</span>';
        }

        $bar_position = $settings['bar_position'] ?? 'top';

        ?>
        <div class="paradise-ab-wrap"<?php echo $data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped markup assembled above ?>>
            <div class="paradise-ab-inner">

                <?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor-rendered icon HTML ?>

                <span class="paradise-ab-message"><?php echo wp_kses_post( $message ); ?></span>

                <?php if ( $cta_text && $cta_url ) : ?>
                <a href="<?php echo esc_url( $cta_url ); ?>"
                   class="paradise-ab-cta"<?php echo $cta_target . $cta_nofollow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped markup assembled above ?>>
                    <?php echo esc_html( $cta_text ); ?>
                </a>
                <?php endif; ?>

                <?php if ( $show_close ) : ?>
                <button class="paradise-ab-close" aria-label="<?php esc_attr_e( 'Close announcement', 'paradise-widgets-for-elementor' ); ?>">
                    <svg viewBox="0 0 14 14" width="1em" height="1em" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <line x1="1" y1="1" x2="13" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <line x1="13" y1="1" x2="1"  y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
                <?php endif; ?>

            </div>
        </div>

        <?php if ( $is_editor ) : ?>
        <div class="paradise-ab-editor-notice">
            <svg viewBox="0 0 20 20" width="16" height="16" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0Zm-8-5a1 1 0 0 1 1 1v4a1 1 0 1 1-2 0V6a1 1 0 0 1 1-1Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
            </svg>
            <span>
                <?php esc_html_e( 'Announcement Bar', 'paradise-widgets-for-elementor' ); ?>
                &mdash;
                <?php esc_html_e( 'displayed at the', 'paradise-widgets-for-elementor' ); ?>
                <strong><?php echo esc_html( $bar_position ); ?></strong>
                <?php esc_html_e( 'of the page', 'paradise-widgets-for-elementor' ); ?>
            </span>
        </div>
        <?php endif; ?>
        <?php
    }
}
