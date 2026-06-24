<?php
/**
 * Paradise Floating Call Button Widget
 *
 * A fixed-position CTA button that stays visible as the user scrolls.
 * Supports tel: and WhatsApp, optional label, pulse animation, and
 * full corner/offset/style customisation.
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once PARADISE_EW_DIR . 'includes/trait-paradise-phone-helper.php';

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

class Paradise_Floating_Call_Btn_Widget extends Paradise_Widget_Base
{
    use Paradise_Phone_Helper;

    public function get_name(): string
    {
        return 'paradise_floating_call_btn';
    }
    public function get_title(): string
    {
        return 'Floating Call Button';
    }
    public function get_icon(): string
    {
        return 'eicon-call-to-action';
    }
    public function get_keywords(): array
    {
        return [ 'phone', 'float', 'fixed', 'call', 'whatsapp', 'cta', 'sticky' ];
    }

    // get_categories() and get_style_depends() come from the base — defaults
    // match (paradise category, 'paradise-floating-call-btn' style handle).
    // Phone Helper trait stays. No JS file; positioning is CSS-only.

    // =========================================================================
    // CONTROLS
    // =========================================================================

    protected function register_controls(): void
    {
        $this->section_phone();
        $this->section_format();
        $this->section_label();
        $this->section_style_position();
        $this->section_style_button();
        $this->section_style_pulse();
        $this->section_style_icon();
    }

    // ── Content: Phone ────────────────────────────────────────────────────────

    private function section_phone(): void
    {
        $this->start_controls_section('section_phone', [
            'label' => esc_html__('Phone', 'paradise-widgets-for-elementor'),
        ]);

        $this->add_control('phone_number', [
            'label'       => esc_html__('Phone Number', 'paradise-widgets-for-elementor'),
            'type'        => Controls_Manager::TEXT,
            'placeholder' => __( '+1 (888) 780-0904', 'paradise-widgets-for-elementor' ),
            'dynamic'     => [ 'active' => true ],
            'label_block' => true,
        ]);

        $this->add_control('country_code', [
            'label'   => esc_html__('Country Code', 'paradise-widgets-for-elementor'),
            'type'    => Controls_Manager::SELECT,
            'default' => '1',
            'options' => [
                '1'      => '🇺🇸 US (+1)',
                '44'     => '🇬🇧 UK (+44)',
                '49'     => '🇩🇪 DE (+49)',
                '98'     => '🇮🇷 IR (+98)',
                '971'    => '🇦🇪 UAE (+971)',
                'custom' => esc_html__('Custom', 'paradise-widgets-for-elementor'),
            ],
        ]);

        $this->add_control('country_code_custom', [
            'label'     => esc_html__('Custom Country Code', 'paradise-widgets-for-elementor'),
            'type'      => Controls_Manager::TEXT,
            'placeholder' => __( '1', 'paradise-widgets-for-elementor' ),
            'description' => esc_html__('Digits only, without +', 'paradise-widgets-for-elementor'),
            'condition' => [ 'country_code' => 'custom' ],
        ]);

        $this->add_control('link_type', [
            'label'     => esc_html__('Action', 'paradise-widgets-for-elementor'),
            'type'      => Controls_Manager::SELECT,
            'default'   => 'tel',
            'options'   => [
                'tel'      => esc_html__('Phone Call (tel:)', 'paradise-widgets-for-elementor'),
                'whatsapp' => esc_html__('Open WhatsApp', 'paradise-widgets-for-elementor'),
            ],
            'separator' => 'before',
        ]);

        $this->end_controls_section();
    }

    // ── Content: Phone Format ─────────────────────────────────────────────────

    private function section_format(): void
    {
        $this->start_controls_section('section_format', [
            'label' => esc_html__('Phone Format', 'paradise-widgets-for-elementor'),
        ]);

        $this->add_control('display_format', [
            'label'   => esc_html__('Display Format', 'paradise-widgets-for-elementor'),
            'type'    => Controls_Manager::SELECT,
            'default' => 'raw',
            'options' => [
                'raw'           => esc_html__('Raw (as entered)', 'paradise-widgets-for-elementor'),
                'international' => esc_html__('International  e.g. +1 888 780 0904', 'paradise-widgets-for-elementor'),
                'local'         => esc_html__('Local  e.g. (888) 780-0904', 'paradise-widgets-for-elementor'),
                'dashes'        => esc_html__('Dashes  e.g. 888-780-0904', 'paradise-widgets-for-elementor'),
                'dots'          => esc_html__('Dots  e.g. 888.780.0904', 'paradise-widgets-for-elementor'),
                'custom_mask'   => esc_html__('Custom Mask', 'paradise-widgets-for-elementor'),
            ],
        ]);

        $this->add_control('custom_mask', [
            'label'       => esc_html__('Custom Mask', 'paradise-widgets-for-elementor'),
            'type'        => Controls_Manager::TEXT,
            'placeholder' => __( '(###) ###-####', 'paradise-widgets-for-elementor' ),
            'description' => esc_html__('Use # for each digit. Example: (###) ###-####', 'paradise-widgets-for-elementor'),
            'label_block' => true,
            'condition'   => [ 'display_format' => 'custom_mask' ],
        ]);

        $this->end_controls_section();
    }

    // ── Content: Icon & Label ─────────────────────────────────────────────────

    private function section_label(): void
    {
        $this->start_controls_section('section_label', [
            'label' => esc_html__('Icon & Label', 'paradise-widgets-for-elementor'),
        ]);

        $this->add_control('selected_icon', [
            'label'   => esc_html__('Icon', 'paradise-widgets-for-elementor'),
            'type'    => Controls_Manager::ICONS,
            'default' => [ 'value' => 'fas fa-phone', 'library' => 'fa-solid' ],
        ]);

        $this->add_control('show_label', [
            'label'        => esc_html__('Show Label', 'paradise-widgets-for-elementor'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => '',
            'separator'    => 'before',
        ]);

        $this->add_control('label_source', [
            'label'     => esc_html__('Label Source', 'paradise-widgets-for-elementor'),
            'type'      => Controls_Manager::SELECT,
            'default'   => 'formatted_number',
            'options'   => [
                'formatted_number' => esc_html__('Formatted Number', 'paradise-widgets-for-elementor'),
                'custom_text'      => esc_html__('Custom Text', 'paradise-widgets-for-elementor'),
            ],
            'condition' => [ 'show_label' => 'yes' ],
        ]);

        $this->add_control('label_text', [
            'label'       => esc_html__('Custom Label', 'paradise-widgets-for-elementor'),
            'type'        => Controls_Manager::TEXT,
            'default'     => esc_html__('Call Us', 'paradise-widgets-for-elementor'),
            'dynamic'     => [ 'active' => true ],
            'condition'   => [ 'show_label' => 'yes', 'label_source' => 'custom_text' ],
        ]);

        $this->end_controls_section();
    }

    // ── Style: Position ───────────────────────────────────────────────────────

    private function section_style_position(): void
    {
        $this->start_controls_section('section_style_pos', [
            'label' => esc_html__('Position', 'paradise-widgets-for-elementor'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('corner', [
            'label'        => esc_html__('Corner', 'paradise-widgets-for-elementor'),
            'type'         => Controls_Manager::CHOOSE,
            'default'      => 'bottom-right',
            'options'      => [
                'bottom-left'  => [ 'title' => esc_html__('Bottom Left', 'paradise-widgets-for-elementor'), 'icon' => 'eicon-v-align-bottom' ],
                'bottom-right' => [ 'title' => esc_html__('Bottom Right', 'paradise-widgets-for-elementor'), 'icon' => 'eicon-v-align-bottom' ],
                'top-left'     => [ 'title' => esc_html__('Top Left', 'paradise-widgets-for-elementor'), 'icon' => 'eicon-v-align-top' ],
                'top-right'    => [ 'title' => esc_html__('Top Right', 'paradise-widgets-for-elementor'), 'icon' => 'eicon-v-align-top' ],
            ],
            'prefix_class' => 'paradise-fcb-corner-',
        ]);

        $this->add_responsive_control('offset_v', [
            'label'      => esc_html__('Vertical Offset', 'paradise-widgets-for-elementor'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'em', '%' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 200 ] ],
            'default'    => [ 'size' => 24, 'unit' => 'px' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-fcb-wrap' => '--paradise-fcb-offset-v: {{SIZE}}{{UNIT}};',
            ],
            'separator' => 'before',
        ]);

        $this->add_responsive_control('offset_h', [
            'label'      => esc_html__('Horizontal Offset', 'paradise-widgets-for-elementor'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'em', '%' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 200 ] ],
            'default'    => [ 'size' => 24, 'unit' => 'px' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-fcb-wrap' => '--paradise-fcb-offset-h: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_control('z_index', [
            'label'     => esc_html__('Z-Index', 'paradise-widgets-for-elementor'),
            'type'      => Controls_Manager::NUMBER,
            'default'   => 9999,
            'selectors' => [ '{{WRAPPER}} .paradise-fcb-wrap' => 'z-index: {{VALUE}};' ],
            'separator' => 'before',
        ]);

        $this->end_controls_section();
    }

    // ── Style: Button ─────────────────────────────────────────────────────────

    private function section_style_button(): void
    {
        $this->start_controls_section('section_style_btn', [
            'label' => esc_html__('Button', 'paradise-widgets-for-elementor'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_responsive_control('btn_size', [
            'label'      => esc_html__('Size', 'paradise-widgets-for-elementor'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'em' ],
            'range'      => [ 'px' => [ 'min' => 32, 'max' => 120 ] ],
            'default'    => [ 'size' => 60, 'unit' => 'px' ],
            'selectors'  => [
                // Circle: equal width+height when no label
                '{{WRAPPER}} .paradise-fcb-btn--circle' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                // Pill: only height; width is auto
                '{{WRAPPER}} .paradise-fcb-btn--pill'   => 'height: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_group_control(Group_Control_Typography::get_type(), [
            'name'      => 'btn_typography',
            'selector'  => '{{WRAPPER}} .paradise-fcb-btn',
            'condition' => [ 'show_label' => 'yes' ],
        ]);

        $this->start_controls_tabs('btn_tabs');

        $this->start_controls_tab('btn_tab_normal', [
            'label' => esc_html__('Normal', 'paradise-widgets-for-elementor'),
        ]);

        $this->add_control('btn_bg', [
            'label'     => esc_html__('Background', 'paradise-widgets-for-elementor'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#2d3e50',
            'selectors' => [ '{{WRAPPER}} .paradise-fcb-btn' => 'background-color: {{VALUE}};' ],
        ]);

        $this->add_control('btn_color', [
            'label'     => esc_html__('Color', 'paradise-widgets-for-elementor'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [
                '{{WRAPPER}} .paradise-fcb-btn'             => 'color: {{VALUE}};',
                '{{WRAPPER}} .paradise-fcb-icon svg'        => 'fill: {{VALUE}};',
            ],
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('btn_tab_hover', [
            'label' => esc_html__('Hover', 'paradise-widgets-for-elementor'),
        ]);

        $this->add_control('btn_bg_hover', [
            'label'     => esc_html__('Background', 'paradise-widgets-for-elementor'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .paradise-fcb-btn:hover' => 'background-color: {{VALUE}};' ],
        ]);

        $this->add_control('btn_color_hover', [
            'label'     => esc_html__('Color', 'paradise-widgets-for-elementor'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .paradise-fcb-btn:hover'      => 'color: {{VALUE}};',
                '{{WRAPPER}} .paradise-fcb-btn:hover .paradise-fcb-icon svg' => 'fill: {{VALUE}};',
            ],
        ]);

        $this->add_control('hover_animation', [
            'label'        => esc_html__('Animation', 'paradise-widgets-for-elementor'),
            'type'         => Controls_Manager::HOVER_ANIMATION,
            'prefix_class' => 'elementor-animation-',
        ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control('btn_border_radius', [
            'label'      => esc_html__('Border Radius', 'paradise-widgets-for-elementor'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%' ],
            'default'    => [ 'top' => 50, 'right' => 50, 'bottom' => 50, 'left' => 50, 'unit' => '%', 'isLinked' => true ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-fcb-btn' =>
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
            'separator' => 'before',
        ]);

        $this->add_group_control(Group_Control_Border::get_type(), [
            'name'     => 'btn_border',
            'selector' => '{{WRAPPER}} .paradise-fcb-btn',
        ]);

        $this->add_group_control(Group_Control_Box_Shadow::get_type(), [
            'name'     => 'btn_shadow',
            'selector' => '{{WRAPPER}} .paradise-fcb-btn',
        ]);

        $this->add_responsive_control('btn_padding', [
            'label'      => esc_html__('Padding', 'paradise-widgets-for-elementor'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-fcb-btn--pill' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
            'condition'  => [ 'show_label' => 'yes' ],
            'separator'  => 'before',
        ]);

        $this->end_controls_section();
    }

    // ── Style: Pulse ──────────────────────────────────────────────────────────

    private function section_style_pulse(): void
    {
        $this->start_controls_section('section_style_pulse', [
            'label' => esc_html__('Pulse Animation', 'paradise-widgets-for-elementor'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('pulse_enabled', [
            'label'        => esc_html__('Enable Pulse', 'paradise-widgets-for-elementor'),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->add_control('pulse_color', [
            'label'     => esc_html__('Pulse Color', 'paradise-widgets-for-elementor'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#2d3e50',
            'selectors' => [
                '{{WRAPPER}} .paradise-fcb-btn--pulse' => '--paradise-fcb-pulse-color: {{VALUE}};',
            ],
            'condition' => [ 'pulse_enabled' => 'yes' ],
        ]);

        $this->add_control('pulse_size', [
            'label'      => esc_html__('Spread', 'paradise-widgets-for-elementor'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 4, 'max' => 40 ] ],
            'default'    => [ 'size' => 14, 'unit' => 'px' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-fcb-btn--pulse' => '--paradise-fcb-pulse-spread: {{SIZE}}px;',
            ],
            'condition'  => [ 'pulse_enabled' => 'yes' ],
        ]);

        $this->add_control('pulse_duration', [
            'label'      => esc_html__('Speed (seconds)', 'paradise-widgets-for-elementor'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 's' ],
            'range'      => [ 's' => [ 'min' => 0.5, 'max' => 4, 'step' => 0.1 ] ],
            'default'    => [ 'size' => 2, 'unit' => 's' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-fcb-btn--pulse' => 'animation-duration: {{SIZE}}s;',
            ],
            'condition'  => [ 'pulse_enabled' => 'yes' ],
        ]);

        $this->end_controls_section();
    }

    // ── Style: Icon ───────────────────────────────────────────────────────────

    private function section_style_icon(): void
    {
        $this->start_controls_section('section_style_icon', [
            'label' => esc_html__('Icon', 'paradise-widgets-for-elementor'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_responsive_control('icon_size', [
            'label'      => esc_html__('Size', 'paradise-widgets-for-elementor'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'em' ],
            'range'      => [ 'px' => [ 'min' => 10, 'max' => 80 ] ],
            'default'    => [ 'size' => 24, 'unit' => 'px' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-fcb-icon i'   => 'font-size: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .paradise-fcb-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('icon_gap', [
            'label'      => esc_html__('Gap', 'paradise-widgets-for-elementor'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 24 ] ],
            'default'    => [ 'size' => 8, 'unit' => 'px' ],
            'selectors'  => [ '{{WRAPPER}} .paradise-fcb-btn' => 'gap: {{SIZE}}{{UNIT}};' ],
            'condition'  => [ 'show_label' => 'yes' ],
        ]);

        $this->end_controls_section();
    }

    // =========================================================================
    // RENDER
    // =========================================================================

    protected function render(): void
    {
        $settings  = $this->get_settings_for_display();
        $raw_phone = trim($settings['phone_number'] ?? '');

        if (empty($raw_phone)) {
            $this->render_editor_placeholder(
                __('Set the phone number in the widget settings.', 'paradise-widgets-for-elementor')
            );
            return;
        }

        $cc        = $this->resolve_country_code($settings);
        $link_type = $settings['link_type'] ?? 'tel';
        $href      = $this->build_phone_href($raw_phone, $cc, $link_type);
        $display_text = $this->format_phone_display($raw_phone, $settings);

        // aria-label uses the displayed text
        /* translators: %s: the displayed phone number or contact label */
        $wa_aria = esc_html__('WhatsApp %s', 'paradise-widgets-for-elementor');
        /* translators: %s: the displayed phone number or contact label */
        $call_aria = esc_html__('Call %s', 'paradise-widgets-for-elementor');
        $aria = 'whatsapp' === $link_type
            ? sprintf($wa_aria, $display_text)
            : sprintf($call_aria, $display_text);

        $show_label  = 'yes' === ($settings['show_label'] ?? '');
        $pulse       = 'yes' === ($settings['pulse_enabled'] ?? 'yes');
        $target_attr = 'whatsapp' === $link_type ? ' target="_blank" rel="noopener noreferrer"' : '';

        // Determine label text based on label_source
        $label_text = '';
        if ($show_label) {
            $label_source = $settings['label_source'] ?? 'formatted_number';
            $label_text = ('formatted_number' === $label_source)
                ? $display_text
                : ($settings['label_text'] ?? esc_html__('Call Us', 'paradise-widgets-for-elementor'));
        }

        $btn_classes = array_filter([
            'paradise-fcb-btn',
            $show_label ? 'paradise-fcb-btn--pill' : 'paradise-fcb-btn--circle',
            $pulse ? 'paradise-fcb-btn--pulse' : '',
        ]);

        // Icon
        $icon_html = '';
        $icon = $settings['selected_icon'] ?? [];
        if (! empty($icon['value'])) {
            ob_start();
            \Elementor\Icons_Manager::render_icon($icon, [ 'aria-hidden' => 'true' ]);
            $icon_html = '<span class="paradise-fcb-icon" aria-hidden="true">' . ob_get_clean() . '</span>';
        }

        ?>
        <div class="paradise-fcb-wrap">
            <a href="<?php echo esc_url($href); ?>"
               class="<?php echo esc_attr(implode(' ', $btn_classes)); ?>"
               aria-label="<?php echo esc_attr($aria); ?>"<?php echo $target_attr; ?>>
                <?php echo $icon_html; ?>
                <?php if ($show_label) : ?>
                <span class="paradise-fcb-label">
                    <?php echo esc_html($label_text); ?>
                </span>
                <?php endif; ?>
            </a>
        </div>
        <?php
    }
}
