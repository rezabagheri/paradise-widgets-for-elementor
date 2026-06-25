<?php
/**
 * Paradise Cookie Consent Bar Widget
 *
 * A fixed full-width bar for GDPR/cookie consent.
 * Supports Accept / Decline buttons, privacy policy link,
 * and configurable consent expiry via localStorage.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class Paradise_Cookie_Consent_Bar_Widget extends Paradise_Widget_Base {

    public function get_name(): string    { return 'paradise_cookie_consent_bar'; }
    public function get_title(): string   { return esc_html__( 'Cookie Consent Bar', 'paradise-widgets-for-elementor' ); }
    public function get_icon(): string    { return 'eicon-info-circle'; }
    public function get_keywords(): array { return [ 'cookie', 'consent', 'gdpr', 'privacy', 'bar', 'notice' ]; }

    // get_categories() and get_style_depends() come from the base — defaults
    // match. Override get_script_depends() because this widget ships a JS
    // file for Accept/Decline handling and localStorage expiry.
    public function get_script_depends(): array { return [ $this->get_default_handle() ]; }

    // =========================================================================
    // CONTROLS
    // =========================================================================

    protected function register_controls(): void {
        $this->section_message();
        $this->section_buttons();
        $this->section_storage();
        $this->section_style_bar();
        $this->section_style_message();
        $this->section_style_accept();
        $this->section_style_decline();
        $this->section_style_privacy();
    }

    // ── Content: Message ──────────────────────────────────────────────────────

    private function section_message(): void {
        $this->start_controls_section( 'section_message', [
            'label' => esc_html__( 'Message', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'message', [
            'label'   => esc_html__( 'Message', 'paradise-widgets-for-elementor' ),
            'type'    => Controls_Manager::TEXTAREA,
            'default' => esc_html__( 'We use cookies to enhance your browsing experience and analyze our traffic.', 'paradise-widgets-for-elementor' ),
            'dynamic' => [ 'active' => true ],
            'rows'    => 3,
        ] );

        $this->add_control( 'privacy_text', [
            'label'     => esc_html__( 'Privacy Link Text', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::TEXT,
            'default'   => esc_html__( 'Privacy Policy', 'paradise-widgets-for-elementor' ),
            'dynamic'   => [ 'active' => true ],
            'separator' => 'before',
        ] );

        $this->add_control( 'privacy_url', [
            'label'     => esc_html__( 'Privacy Link URL', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::URL,
            'dynamic'   => [ 'active' => true ],
            'condition' => [ 'privacy_text!' => '' ],
        ] );

        $this->end_controls_section();
    }

    // ── Content: Buttons ──────────────────────────────────────────────────────

    private function section_buttons(): void {
        $this->start_controls_section( 'section_buttons', [
            'label' => esc_html__( 'Buttons', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'accept_text', [
            'label'   => esc_html__( 'Accept Button Text', 'paradise-widgets-for-elementor' ),
            'type'    => Controls_Manager::TEXT,
            'default' => esc_html__( 'Accept All', 'paradise-widgets-for-elementor' ),
            'dynamic' => [ 'active' => true ],
        ] );

        $this->add_control( 'show_decline', [
            'label'        => esc_html__( 'Show Decline Button', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
            'separator'    => 'before',
        ] );

        $this->add_control( 'decline_text', [
            'label'     => esc_html__( 'Decline Button Text', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::TEXT,
            'default'   => esc_html__( 'Decline', 'paradise-widgets-for-elementor' ),
            'dynamic'   => [ 'active' => true ],
            'condition' => [ 'show_decline' => 'yes' ],
        ] );

        $this->end_controls_section();
    }

    // ── Content: Storage ──────────────────────────────────────────────────────

    private function section_storage(): void {
        $this->start_controls_section( 'section_storage', [
            'label' => esc_html__( 'Storage', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'consent_expiry', [
            'label'       => esc_html__( 'Consent Expiry (days)', 'paradise-widgets-for-elementor' ),
            'type'        => Controls_Manager::NUMBER,
            'default'     => 365,
            'min'         => 1,
            'max'         => 730,
            'description' => esc_html__( 'How long to remember the user\'s choice.', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'bar_id', [
            'label'       => esc_html__( 'Unique Bar ID', 'paradise-widgets-for-elementor' ),
            'type'        => Controls_Manager::TEXT,
            'placeholder' => esc_html__( 'e.g. main-cookie-notice', 'paradise-widgets-for-elementor' ),
            'description' => esc_html__( 'Optional. Useful when you want the same consent state shared across pages. Leave blank to use the widget ID.', 'paradise-widgets-for-elementor' ),
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
            'default'      => 'bottom',
            'options'      => [
                'top'    => [ 'title' => esc_html__( 'Top',    'paradise-widgets-for-elementor' ), 'icon' => 'eicon-v-align-top' ],
                'bottom' => [ 'title' => esc_html__( 'Bottom', 'paradise-widgets-for-elementor' ), 'icon' => 'eicon-v-align-bottom' ],
            ],
            'prefix_class' => 'paradise-ccb-pos-',
            'render_type'  => 'template',
        ] );

        $this->add_control( 'z_index', [
            'label'     => esc_html__( 'Z-Index', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::NUMBER,
            'default'   => 9980,
            'selectors' => [ '{{WRAPPER}} .paradise-ccb-wrap' => 'z-index: {{VALUE}};' ],
        ] );

        $this->add_control( 'bar_bg', [
            'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#1a1a2e',
            'selectors' => [ '{{WRAPPER}} .paradise-ccb-wrap' => 'background-color: {{VALUE}};' ],
            'separator' => 'before',
        ] );

        $this->add_responsive_control( 'bar_padding', [
            'label'      => esc_html__( 'Padding', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em', 'rem' ],
            'default'    => [ 'top' => 14, 'right' => 24, 'bottom' => 14, 'left' => 24, 'unit' => 'px', 'isLinked' => false ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-ccb-inner' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->add_responsive_control( 'content_max_width', [
            'label'      => esc_html__( 'Content Max Width', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%' ],
            'range'      => [ 'px' => [ 'min' => 400, 'max' => 1600 ] ],
            'default'    => [ 'size' => 1200, 'unit' => 'px' ],
            'selectors'  => [ '{{WRAPPER}} .paradise-ccb-inner' => 'max-width: {{SIZE}}{{UNIT}};' ],
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
            'default'   => '#cccccc',
            'selectors' => [ '{{WRAPPER}} .paradise-ccb-message' => 'color: {{VALUE}};' ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'     => 'message_typography',
            'selector' => '{{WRAPPER}} .paradise-ccb-message',
        ] );

        $this->end_controls_section();
    }

    // ── Style: Accept Button ──────────────────────────────────────────────────

    private function section_style_accept(): void {
        $this->start_controls_section( 'section_style_accept', [
            'label' => esc_html__( 'Accept Button', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->start_controls_tabs( 'accept_tabs' );

            $this->start_controls_tab( 'accept_tab_normal', [
                'label' => esc_html__( 'Normal', 'paradise-widgets-for-elementor' ),
            ] );

            $this->add_control( 'accept_bg', [
                'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#4caf50',
                'selectors' => [ '{{WRAPPER}} .paradise-ccb-accept' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'accept_color', [
                'label'     => esc_html__( 'Text Color', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [ '{{WRAPPER}} .paradise-ccb-accept' => 'color: {{VALUE}};' ],
            ] );

            $this->end_controls_tab();

            $this->start_controls_tab( 'accept_tab_hover', [
                'label' => esc_html__( 'Hover', 'paradise-widgets-for-elementor' ),
            ] );

            $this->add_control( 'accept_bg_hover', [
                'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .paradise-ccb-accept:hover' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'accept_color_hover', [
                'label'     => esc_html__( 'Text Color', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .paradise-ccb-accept:hover' => 'color: {{VALUE}};' ],
            ] );

            $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control( 'accept_border_radius', [
            'label'      => esc_html__( 'Border Radius', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%' ],
            'default'    => [ 'top' => 4, 'right' => 4, 'bottom' => 4, 'left' => 4, 'unit' => 'px', 'isLinked' => true ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-ccb-accept' =>
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
            'separator'  => 'before',
        ] );

        $this->add_responsive_control( 'accept_padding', [
            'label'      => esc_html__( 'Padding', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em' ],
            'default'    => [ 'top' => 8, 'right' => 20, 'bottom' => 8, 'left' => 20, 'unit' => 'px', 'isLinked' => false ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-ccb-accept' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'      => 'accept_typography',
            'selector'  => '{{WRAPPER}} .paradise-ccb-accept',
            'separator' => 'before',
        ] );

        $this->end_controls_section();
    }

    // ── Style: Decline Button ─────────────────────────────────────────────────

    private function section_style_decline(): void {
        $this->start_controls_section( 'section_style_decline', [
            'label'     => esc_html__( 'Decline Button', 'paradise-widgets-for-elementor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'show_decline' => 'yes' ],
        ] );

        $this->start_controls_tabs( 'decline_tabs' );

            $this->start_controls_tab( 'decline_tab_normal', [
                'label' => esc_html__( 'Normal', 'paradise-widgets-for-elementor' ),
            ] );

            $this->add_control( 'decline_bg', [
                'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => 'rgba(255,255,255,0.08)',
                'selectors' => [ '{{WRAPPER}} .paradise-ccb-decline' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'decline_color', [
                'label'     => esc_html__( 'Text Color', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#cccccc',
                'selectors' => [ '{{WRAPPER}} .paradise-ccb-decline' => 'color: {{VALUE}};' ],
            ] );

            $this->end_controls_tab();

            $this->start_controls_tab( 'decline_tab_hover', [
                'label' => esc_html__( 'Hover', 'paradise-widgets-for-elementor' ),
            ] );

            $this->add_control( 'decline_bg_hover', [
                'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .paradise-ccb-decline:hover' => 'background-color: {{VALUE}};' ],
            ] );

            $this->add_control( 'decline_color_hover', [
                'label'     => esc_html__( 'Text Color', 'paradise-widgets-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .paradise-ccb-decline:hover' => 'color: {{VALUE}};' ],
            ] );

            $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control( 'decline_border_radius', [
            'label'      => esc_html__( 'Border Radius', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%' ],
            'default'    => [ 'top' => 4, 'right' => 4, 'bottom' => 4, 'left' => 4, 'unit' => 'px', 'isLinked' => true ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-ccb-decline' =>
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
            'separator'  => 'before',
        ] );

        $this->add_responsive_control( 'decline_padding', [
            'label'      => esc_html__( 'Padding', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em' ],
            'default'    => [ 'top' => 8, 'right' => 20, 'bottom' => 8, 'left' => 20, 'unit' => 'px', 'isLinked' => false ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-ccb-decline' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'      => 'decline_typography',
            'selector'  => '{{WRAPPER}} .paradise-ccb-decline',
            'separator' => 'before',
        ] );

        $this->end_controls_section();
    }

    // ── Style: Privacy Link ───────────────────────────────────────────────────

    private function section_style_privacy(): void {
        $this->start_controls_section( 'section_style_privacy', [
            'label'     => esc_html__( 'Privacy Link', 'paradise-widgets-for-elementor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'privacy_text!' => '' ],
        ] );

        $this->add_control( 'privacy_color', [
            'label'     => esc_html__( 'Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#90caf9',
            'selectors' => [ '{{WRAPPER}} .paradise-ccb-privacy' => 'color: {{VALUE}};' ],
        ] );

        $this->add_control( 'privacy_color_hover', [
            'label'     => esc_html__( 'Hover Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .paradise-ccb-privacy:hover' => 'color: {{VALUE}};' ],
        ] );

        $this->add_control( 'privacy_underline', [
            'label'        => esc_html__( 'Underline', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
            'selectors'    => [
                '{{WRAPPER}} .paradise-ccb-privacy' => 'text-decoration: {{VALUE}};',
            ],
            'selectors_dictionary' => [
                'yes' => 'underline',
                ''    => 'none',
            ],
        ] );

        $this->end_controls_section();
    }

    // =========================================================================
    // RENDER
    // =========================================================================

    protected function render(): void {
        $settings  = $this->get_settings_for_display();
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();

        $message      = $settings['message'] ?? '';
        $privacy_text = trim( $settings['privacy_text'] ?? '' );
        $accept_text  = trim( $settings['accept_text'] ?? 'Accept All' );
        $show_decline = 'yes' === ( $settings['show_decline'] ?? 'yes' );
        $decline_text = trim( $settings['decline_text'] ?? 'Decline' );

        $bar_id = trim( $settings['bar_id'] ?? '' ) ?: $this->get_id();
        $expiry = absint( $settings['consent_expiry'] ?? 365 );

        $bar_position = $settings['bar_position'] ?? 'bottom';

        // Privacy link
        $privacy_url    = $settings['privacy_url']['url'] ?? '';
        $privacy_target = ! empty( $settings['privacy_url']['is_external'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';

        // Data attrs for JS
        $data = sprintf(
            ' data-ccb-id="%s" data-ccb-expiry="%d"%s',
            esc_attr( $bar_id ),
            $expiry,
            $is_editor ? ' data-ccb-edit="true"' : ''
        );

        ?>
        <div class="paradise-ccb-wrap"<?php echo $data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped markup assembled above ?>>
            <div class="paradise-ccb-inner">

                <div class="paradise-ccb-text">
                    <span class="paradise-ccb-message"><?php echo wp_kses_post( $message ); ?></span>

                    <?php if ( $privacy_text && $privacy_url ) : ?>
                    <a href="<?php echo esc_url( $privacy_url ); ?>"
                       class="paradise-ccb-privacy"<?php echo $privacy_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped markup assembled above ?>>
                        <?php echo esc_html( $privacy_text ); ?>
                    </a>
                    <?php elseif ( $privacy_text ) : ?>
                    <span class="paradise-ccb-privacy"><?php echo esc_html( $privacy_text ); ?></span>
                    <?php endif; ?>
                </div>

                <div class="paradise-ccb-actions">
                    <?php if ( $show_decline && $decline_text ) : ?>
                    <button class="paradise-ccb-decline" type="button">
                        <?php echo esc_html( $decline_text ); ?>
                    </button>
                    <?php endif; ?>

                    <button class="paradise-ccb-accept" type="button">
                        <?php echo esc_html( $accept_text ); ?>
                    </button>
                </div>

            </div>
        </div>

        <?php if ( $is_editor ) : ?>
        <div class="paradise-ccb-editor-notice">
            <svg viewBox="0 0 20 20" width="16" height="16" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0Zm-8-5a1 1 0 0 1 1 1v4a1 1 0 1 1-2 0V6a1 1 0 0 1 1-1Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
            </svg>
            <span>
                <?php esc_html_e( 'Cookie Consent Bar', 'paradise-widgets-for-elementor' ); ?>
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
