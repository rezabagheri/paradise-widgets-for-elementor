<?php
/**
 * Paradise Off-Canvas Menu Widget
 *
 * A slide-in panel triggered by an inline button or the Public API.
 * Renders a WordPress nav menu inside a fixed-position panel with overlay.
 *
 * Public API:
 *   Paradise.openOffCanvas(id)
 *   Paradise.closeOffCanvas(id)
 *   Paradise.toggleOffCanvas(id)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class Paradise_Off_Canvas_Menu_Widget extends Paradise_Widget_Base {

    public function get_name(): string    { return 'paradise_off_canvas_menu'; }
    public function get_title(): string   { return esc_html__( 'Off-Canvas Menu', 'paradise-widgets-for-elementor' ); }
    public function get_icon(): string    { return 'eicon-menu-bar'; }
    public function get_keywords(): array { return [ 'off-canvas', 'menu', 'drawer', 'sidebar', 'nav', 'mobile' ]; }

    // get_categories() and get_style_depends() come from the base — defaults
    // match. Override get_script_depends() because this widget ships a JS
    // file for slide-in animation and the Paradise.openOffCanvas() API.
    public function get_script_depends(): array { return [ $this->get_default_handle() ]; }

    // =========================================================================
    // CONTROLS
    // =========================================================================

    protected function register_controls(): void {
        $this->section_trigger();
        $this->section_panel();
        $this->section_style_trigger();
        $this->section_style_panel();
        $this->section_style_header();
        $this->section_style_menu();
        $this->section_style_overlay();
    }

    // ── Content: Trigger ──────────────────────────────────────────────────────

    private function section_trigger(): void {
        $this->start_controls_section( 'section_trigger', [
            'label' => esc_html__( 'Trigger Button', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'trigger_icon', [
            'label'   => esc_html__( 'Icon', 'paradise-widgets-for-elementor' ),
            'type'    => Controls_Manager::ICONS,
            'default' => [ 'value' => 'eicon-menu-bar', 'library' => 'eicons' ],
        ] );

        $this->add_control( 'trigger_label', [
            'label'     => esc_html__( 'Label', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::TEXT,
            'default'   => esc_html__( 'Menu', 'paradise-widgets-for-elementor' ),
            'dynamic'   => [ 'active' => true ],
            'separator' => 'before',
        ] );

        $this->add_control( 'trigger_label_position', [
            'label'   => esc_html__( 'Label Position', 'paradise-widgets-for-elementor' ),
            'type'    => Controls_Manager::SELECT,
            'default' => 'none',
            'options' => [
                'none'   => esc_html__( 'Hidden',      'paradise-widgets-for-elementor' ),
                'after'  => esc_html__( 'After Icon',  'paradise-widgets-for-elementor' ),
                'before' => esc_html__( 'Before Icon', 'paradise-widgets-for-elementor' ),
            ],
            'condition' => [ 'trigger_label!' => '' ],
        ] );

        $this->end_controls_section();
    }

    // ── Content: Panel ────────────────────────────────────────────────────────

    private function section_panel(): void {
        $this->start_controls_section( 'section_panel', [
            'label' => esc_html__( 'Panel', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'panel_position', [
            'label'        => esc_html__( 'Slide In From', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::CHOOSE,
            'default'      => 'right',
            'options'      => [
                'left'  => [ 'title' => esc_html__( 'Left',  'paradise-widgets-for-elementor' ), 'icon' => 'eicon-h-align-left' ],
                'right' => [ 'title' => esc_html__( 'Right', 'paradise-widgets-for-elementor' ), 'icon' => 'eicon-h-align-right' ],
            ],
            'prefix_class' => 'paradise-ocm-pos-',
            'render_type'  => 'template',
        ] );

        // Build registered WP menu options
        $nav_menus    = wp_get_nav_menus();
        $menu_options = [ '0' => esc_html__( '— Select a menu —', 'paradise-widgets-for-elementor' ) ];
        foreach ( $nav_menus as $menu ) {
            $menu_options[ $menu->term_id ] = $menu->name;
        }

        $this->add_control( 'nav_menu', [
            'label'       => esc_html__( 'Menu', 'paradise-widgets-for-elementor' ),
            'type'        => Controls_Manager::SELECT,
            'options'     => $menu_options,
            'default'     => '0',
            'separator'   => 'before',
            'description' => esc_html__( 'Which WordPress menu fills the sliding panel. Build menus under Appearance → Menus. Tip: this widget also exposes Paradise.toggleOffCanvas("id") so other elements (e.g. a Bottom Navigation item) can open it.', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'panel_title', [
            'label'     => esc_html__( 'Panel Title', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::TEXT,
            'default'   => '',
            'dynamic'   => [ 'active' => true ],
            'separator' => 'before',
        ] );

        $this->add_control( 'show_close_btn', [
            'label'        => esc_html__( 'Show Close Button', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'close_on_overlay', [
            'label'        => esc_html__( 'Close on Overlay Click', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->end_controls_section();
    }

    // ── Style: Trigger Button ─────────────────────────────────────────────────

    private function section_style_trigger(): void {
        $this->start_controls_section( 'section_style_trigger', [
            'label' => esc_html__( 'Trigger Button', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_responsive_control( 'trigger_icon_size', [
            'label'      => esc_html__( 'Icon Size', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'em' ],
            'range'      => [ 'px' => [ 'min' => 12, 'max' => 60 ] ],
            'default'    => [ 'size' => 22, 'unit' => 'px' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-ocm-trigger i'   => 'font-size: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .paradise-ocm-trigger svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->add_responsive_control( 'trigger_label_gap', [
            'label'      => esc_html__( 'Icon–Label Gap', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 24 ] ],
            'default'    => [ 'size' => 8, 'unit' => 'px' ],
            'selectors'  => [ '{{WRAPPER}} .paradise-ocm-trigger' => 'gap: {{SIZE}}{{UNIT}};' ],
            'condition'  => [ 'trigger_label_position!' => 'none' ],
        ] );

        $this->start_controls_tabs( 'trigger_tabs', [ 'separator' => 'before' ] );

            $this->start_controls_tab( 'trigger_tab_normal', [
                'label' => esc_html__( 'Normal', 'paradise-widgets-for-elementor' ),
            ] );

            $this->add_control( 'trigger_bg', [
                'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .paradise-ocm-trigger' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'trigger_color', [
                'label'     => esc_html__( 'Color', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .paradise-ocm-trigger'     => 'color: {{VALUE}};',
                    '{{WRAPPER}} .paradise-ocm-trigger i'   => 'color: {{VALUE}};',
                    '{{WRAPPER}} .paradise-ocm-trigger svg' => 'fill: {{VALUE}};',
                ],
            ] );

            $this->end_controls_tab();

            $this->start_controls_tab( 'trigger_tab_hover', [
                'label' => esc_html__( 'Hover', 'paradise-widgets-for-elementor' ),
            ] );

            $this->add_control( 'trigger_bg_hover', [
                'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .paradise-ocm-trigger:hover' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'trigger_color_hover', [
                'label'     => esc_html__( 'Color', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .paradise-ocm-trigger:hover'     => 'color: {{VALUE}};',
                    '{{WRAPPER}} .paradise-ocm-trigger:hover i'   => 'color: {{VALUE}};',
                    '{{WRAPPER}} .paradise-ocm-trigger:hover svg' => 'fill: {{VALUE}};',
                ],
            ] );

            $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control( 'trigger_padding', [
            'label'      => esc_html__( 'Padding', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em' ],
            'default'    => [ 'top' => 8, 'right' => 8, 'bottom' => 8, 'left' => 8, 'unit' => 'px', 'isLinked' => true ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-ocm-trigger' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
            'separator'  => 'before',
        ] );

        $this->add_responsive_control( 'trigger_border_radius', [
            'label'      => esc_html__( 'Border Radius', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-ocm-trigger' =>
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'      => 'trigger_label_typography',
            'selector'  => '{{WRAPPER}} .paradise-ocm-trigger-label',
            'condition' => [ 'trigger_label_position!' => 'none' ],
            'separator' => 'before',
        ] );

        $this->end_controls_section();
    }

    // ── Style: Panel ──────────────────────────────────────────────────────────

    private function section_style_panel(): void {
        $this->start_controls_section( 'section_style_panel', [
            'label' => esc_html__( 'Panel', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_responsive_control( 'panel_width', [
            'label'      => esc_html__( 'Width', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%', 'vw' ],
            'range'      => [ 'px' => [ 'min' => 200, 'max' => 800 ] ],
            'default'    => [ 'size' => 300, 'unit' => 'px' ],
            'selectors'  => [ '{{WRAPPER}} .paradise-ocm-panel' => '--paradise-ocm-width: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->add_control( 'panel_bg', [
            'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [ '{{WRAPPER}} .paradise-ocm-panel' => 'background-color: {{VALUE}};' ],
        ] );

        $this->add_control( 'animation_duration', [
            'label'     => esc_html__( 'Animation Duration (ms)', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::NUMBER,
            'default'   => 300,
            'min'       => 100,
            'max'       => 800,
            'selectors' => [ '{{WRAPPER}} .paradise-ocm-panel, {{WRAPPER}} .paradise-ocm-overlay' => '--paradise-ocm-duration: {{VALUE}}ms;' ],
        ] );

        $this->add_control( 'z_index', [
            'label'     => esc_html__( 'Z-Index', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::NUMBER,
            'default'   => 9995,
            'selectors' => [ '{{WRAPPER}} .paradise-ocm-panel' => 'z-index: {{VALUE}}; --paradise-ocm-overlay-z: calc({{VALUE}} - 1);' ],
        ] );

        $this->end_controls_section();
    }

    // ── Style: Header ─────────────────────────────────────────────────────────

    private function section_style_header(): void {
        $this->start_controls_section( 'section_style_header', [
            'label' => esc_html__( 'Panel Header', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'header_bg', [
            'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .paradise-ocm-panel-header' => 'background-color: {{VALUE}};' ],
        ] );

        $this->add_responsive_control( 'header_padding', [
            'label'      => esc_html__( 'Padding', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em' ],
            'default'    => [ 'top' => 16, 'right' => 16, 'bottom' => 16, 'left' => 16, 'unit' => 'px', 'isLinked' => true ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-ocm-panel-header' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->add_control( 'panel_title_color', [
            'label'     => esc_html__( 'Title Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#333333',
            'selectors' => [ '{{WRAPPER}} .paradise-ocm-panel-title' => 'color: {{VALUE}};' ],
            'condition' => [ 'panel_title!' => '' ],
            'separator' => 'before',
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'      => 'panel_title_typography',
            'selector'  => '{{WRAPPER}} .paradise-ocm-panel-title',
            'condition' => [ 'panel_title!' => '' ],
        ] );

        $this->add_responsive_control( 'close_size', [
            'label'      => esc_html__( 'Close Button Size', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 12, 'max' => 40 ] ],
            'default'    => [ 'size' => 20, 'unit' => 'px' ],
            'selectors'  => [ '{{WRAPPER}} .paradise-ocm-close' => 'font-size: {{SIZE}}{{UNIT}};' ],
            'condition'  => [ 'show_close_btn' => 'yes' ],
            'separator'  => 'before',
        ] );

        $this->add_control( 'close_color', [
            'label'     => esc_html__( 'Close Button Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#666666',
            'selectors' => [ '{{WRAPPER}} .paradise-ocm-close' => 'color: {{VALUE}};' ],
            'condition' => [ 'show_close_btn' => 'yes' ],
        ] );

        $this->add_control( 'close_color_hover', [
            'label'     => esc_html__( 'Close Button Hover Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#111111',
            'selectors' => [ '{{WRAPPER}} .paradise-ocm-close:hover' => 'color: {{VALUE}};' ],
            'condition' => [ 'show_close_btn' => 'yes' ],
        ] );

        $this->end_controls_section();
    }

    // ── Style: Menu Items ─────────────────────────────────────────────────────

    private function section_style_menu(): void {
        $this->start_controls_section( 'section_style_menu', [
            'label' => esc_html__( 'Menu Items', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->start_controls_tabs( 'menu_tabs' );

            $this->start_controls_tab( 'menu_tab_normal', [
                'label' => esc_html__( 'Normal', 'paradise-widgets-for-elementor' ),
            ] );

            $this->add_control( 'item_color', [
                'label'     => esc_html__( 'Text Color', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#333333',
                'selectors' => [ '{{WRAPPER}} .paradise-ocm-menu a' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'item_bg', [
                'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .paradise-ocm-menu a' => 'background-color: {{VALUE}};' ],
            ] );

            $this->end_controls_tab();

            $this->start_controls_tab( 'menu_tab_hover', [
                'label' => esc_html__( 'Hover', 'paradise-widgets-for-elementor' ),
            ] );

            $this->add_control( 'item_color_hover', [
                'label'     => esc_html__( 'Text Color', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .paradise-ocm-menu a:hover' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'item_bg_hover', [
                'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .paradise-ocm-menu a:hover' => 'background-color: {{VALUE}};' ],
            ] );

            $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control( 'item_padding', [
            'label'      => esc_html__( 'Item Padding', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em' ],
            'default'    => [ 'top' => 12, 'right' => 16, 'bottom' => 12, 'left' => 16, 'unit' => 'px', 'isLinked' => false ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-ocm-menu a' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
            'separator'  => 'before',
        ] );

        $this->add_control( 'item_divider_color', [
            'label'     => esc_html__( 'Divider Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#eeeeee',
            'selectors' => [
                '{{WRAPPER}} .paradise-ocm-menu > li + li' => 'border-top: 1px solid {{VALUE}};',
            ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'      => 'item_typography',
            'selector'  => '{{WRAPPER}} .paradise-ocm-menu a',
            'separator' => 'before',
        ] );

        $this->end_controls_section();
    }

    // ── Style: Overlay ────────────────────────────────────────────────────────

    private function section_style_overlay(): void {
        $this->start_controls_section( 'section_style_overlay', [
            'label' => esc_html__( 'Overlay', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'overlay_color', [
            'label'     => esc_html__( 'Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => 'rgba(0,0,0,0.5)',
            'selectors' => [ '{{WRAPPER}} .paradise-ocm-overlay' => 'background-color: {{VALUE}};' ],
        ] );

        $this->end_controls_section();
    }

    // =========================================================================
    // RENDER
    // =========================================================================

    protected function render(): void {
        $settings  = $this->get_settings_for_display();
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();

        $panel_id          = $this->get_id();
        $menu_id           = absint( $settings['nav_menu'] ?? 0 );
        $panel_title       = trim( $settings['panel_title'] ?? '' );
        $show_close        = 'yes' === ( $settings['show_close_btn'] ?? 'yes' );
        $close_on_overlay  = 'yes' === ( $settings['close_on_overlay'] ?? 'yes' );

        $trigger_icon      = $settings['trigger_icon'] ?? [];
        $trigger_label     = trim( $settings['trigger_label'] ?? '' );
        $trigger_label_pos = $settings['trigger_label_position'] ?? 'none';

        // Trigger icon HTML
        $icon_html = '';
        if ( ! empty( $trigger_icon['value'] ) ) {
            ob_start();
            \Elementor\Icons_Manager::render_icon( $trigger_icon, [ 'aria-hidden' => 'true' ] );
            $icon_html = ob_get_clean();
        }

        $label_html = ( $trigger_label && 'none' !== $trigger_label_pos )
            ? '<span class="paradise-ocm-trigger-label">' . esc_html( $trigger_label ) . '</span>'
            : '';

        $panel_element_id = 'paradise-ocm-panel-' . esc_attr( $panel_id );

        ?>
        <div class="paradise-ocm-wrap"
             data-ocm-id="<?php echo esc_attr( $panel_id ); ?>"
             data-ocm-overlay="<?php echo $close_on_overlay ? 'true' : 'false'; ?>"
             <?php echo $is_editor ? 'data-ocm-edit="true"' : ''; ?>>

            <!-- Trigger button (inline) -->
            <button class="paradise-ocm-trigger"
                    aria-expanded="false"
                    aria-controls="<?php echo esc_attr( $panel_element_id ); ?>"
                    aria-label="<?php esc_attr_e( 'Open menu', 'paradise-widgets-for-elementor' ); ?>"
                    type="button">
                <?php if ( 'before' === $trigger_label_pos ) echo $label_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped markup assembled above ?>
                <?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor-rendered icon HTML ?>
                <?php if ( 'after' === $trigger_label_pos ) echo $label_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped markup assembled above ?>
            </button>

            <!-- Overlay (fixed) -->
            <div class="paradise-ocm-overlay" aria-hidden="true"></div>

            <!-- Panel (fixed, slides in) -->
            <div class="paradise-ocm-panel"
                 id="<?php echo esc_attr( $panel_element_id ); ?>"
                 role="dialog"
                 aria-modal="true"
                 aria-label="<?php echo $panel_title ? esc_attr( $panel_title ) : esc_attr__( 'Navigation', 'paradise-widgets-for-elementor' ); ?>">

                <?php if ( $panel_title || $show_close ) : ?>
                <div class="paradise-ocm-panel-header">
                    <?php if ( $panel_title ) : ?>
                    <span class="paradise-ocm-panel-title"><?php echo esc_html( $panel_title ); ?></span>
                    <?php endif; ?>

                    <?php if ( $show_close ) : ?>
                    <button class="paradise-ocm-close"
                            aria-label="<?php esc_attr_e( 'Close menu', 'paradise-widgets-for-elementor' ); ?>"
                            type="button">
                        <svg viewBox="0 0 14 14" width="1em" height="1em" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <line x1="1" y1="1" x2="13" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <line x1="13" y1="1" x2="1"  y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <nav class="paradise-ocm-nav"
                     aria-label="<?php esc_attr_e( 'Off-canvas navigation', 'paradise-widgets-for-elementor' ); ?>">
                    <?php
                    if ( $menu_id > 0 ) {
                        wp_nav_menu( [
                            'menu'        => $menu_id,
                            'container'   => false,
                            'menu_class'  => 'paradise-ocm-menu',
                            'fallback_cb' => false,
                            'depth'       => 2,
                        ] );
                    } elseif ( $is_editor ) {
                        echo '<p class="paradise-ocm-no-menu">'
                            . esc_html__( 'Select a menu in the Panel settings.', 'paradise-widgets-for-elementor' )
                            . '</p>';
                    }
                    ?>
                </nav>

            </div><!-- .paradise-ocm-panel -->

        </div><!-- .paradise-ocm-wrap -->

        <?php if ( $is_editor ) : ?>
        <div class="paradise-ocm-editor-notice">
            <svg viewBox="0 0 20 20" width="16" height="16" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0Zm-8-5a1 1 0 0 1 1 1v4a1 1 0 1 1-2 0V6a1 1 0 0 1 1-1Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
            </svg>
            <span>
                <?php esc_html_e( 'Off-Canvas Menu', 'paradise-widgets-for-elementor' ); ?>
                &mdash;
                <?php esc_html_e( 'Public API:', 'paradise-widgets-for-elementor' ); ?>
                <strong>Paradise.openOffCanvas('<?php echo esc_html( $panel_id ); ?>')</strong>
            </span>
        </div>
        <?php endif; ?>
        <?php
    }
}
