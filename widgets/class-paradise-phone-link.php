<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once PARADISE_EW_DIR . 'includes/trait-paradise-phone-helper.php';

class Paradise_Phone_Link_Widget extends Paradise_Widget_Base {

    use Paradise_Phone_Helper;

    public function get_name()  { return 'paradise_phone_link'; }
    public function get_title() { return 'Paradise Phone Link'; }
    public function get_icon()  { return 'eicon-call-to-action'; }

    // get_categories() and get_style_depends() come from the base — defaults
    // match (paradise category, 'paradise-phone-link' style handle).
    // Phone Helper trait stays. No JS file.

    protected function register_controls() {

        // ============================================================
        // TAB: CONTENT
        // ============================================================

        // -------------------- Section: Phone --------------------
        $this->start_controls_section( 'section_phone', [
            'label' => __( 'Phone Number', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'phone_number', [
            'label'       => __( 'Phone Number', 'paradise-widgets-for-elementor' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => __( '+1 (212) 555-1234', 'paradise-widgets-for-elementor' ),
            'description' => __( 'Accepts any format. Will be normalized automatically.', 'paradise-widgets-for-elementor' ),
            'dynamic'     => [ 'active' => true ],
            'label_block' => true,
        ] );

        $this->end_controls_section();

        // -------------------- Section: Prefix --------------------
        $this->start_controls_section( 'section_prefix', [
            'label' => __( 'Prefix', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'prefix_enabled', [
            'label'        => __( 'Enable Prefix', 'paradise-widgets-for-elementor' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'label_on'     => __( 'Yes', 'paradise-widgets-for-elementor' ),
            'label_off'    => __( 'No', 'paradise-widgets-for-elementor' ),
            'return_value' => 'yes',
            'default'      => 'no',
        ] );

        $this->add_control( 'prefix_text', [
            'label'       => __( 'Prefix Text', 'paradise-widgets-for-elementor' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => __( 'Call Us:', 'paradise-widgets-for-elementor' ),
            'dynamic'     => [ 'active' => true ],
            'label_block' => true,
            'condition'   => [ 'prefix_enabled' => 'yes' ],
        ] );

        $this->add_control( 'prefix_tag', [
            'label'     => __( 'HTML Tag', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::SELECT,
            'default'   => 'span',
            'options'   => [
                'h1'   => 'H1',
                'h2'   => 'H2',
                'h3'   => 'H3',
                'h4'   => 'H4',
                'h5'   => 'H5',
                'h6'   => 'H6',
                'p'    => 'P',
                'div'  => 'div',
                'span' => 'span',
            ],
            'condition' => [ 'prefix_enabled' => 'yes' ],
        ] );

        $this->end_controls_section();

        // -------------------- Section: Layout --------------------
        $this->start_controls_section( 'section_layout', [
            'label' => __( 'Layout', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'layout_mode', [
            'label'   => __( 'Layout Mode', 'paradise-widgets-for-elementor' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'number_only',
            'options' => [
                'number_only'          => 'Number Only',
                'prefix_number'        => 'Prefix + Number',
                'icon_number'          => 'Icon + Number',
                'icon_prefix_number'   => 'Icon + Prefix + Number',
            ],
        ] );

        $this->add_control( 'direction', [
            'label'     => __( 'Direction', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::CHOOSE,
            'default'   => 'inline',
            'options'   => [
                'inline'  => [
                    'title' => __( 'Inline', 'paradise-widgets-for-elementor' ),
                    'icon'  => 'eicon-navigation-horizontal',
                ],
                'stacked' => [
                    'title' => __( 'Stacked', 'paradise-widgets-for-elementor' ),
                    'icon'  => 'eicon-navigation-vertical',
                ],
            ],
            'condition' => [
                'layout_mode!' => 'number_only',
            ],
        ] );

        $this->add_control( 'selected_icon', [
            'label'     => __( 'Icon', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::ICONS,
            'default'   => [
                'value'   => 'fas fa-phone',
                'library' => 'fa-solid',
            ],
            'condition' => [
                'layout_mode' => [ 'icon_number', 'icon_prefix_number' ],
            ],
        ] );

        $this->add_control( 'icon_position', [
            'label'     => __( 'Icon Position', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::CHOOSE,
            'default'   => 'before',
            'options'   => [
                'before' => [
                    'title' => __( 'Before', 'paradise-widgets-for-elementor' ),
                    'icon'  => 'eicon-h-align-left',
                ],
                'after'  => [
                    'title' => __( 'After', 'paradise-widgets-for-elementor' ),
                    'icon'  => 'eicon-h-align-right',
                ],
            ],
            'condition' => [
                'layout_mode' => [ 'icon_number', 'icon_prefix_number' ],
            ],
        ] );

        $this->add_responsive_control( 'gap', [
            'label'      => __( 'Gap Between Elements', 'paradise-widgets-for-elementor' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'em', 'rem' ],
            'range'      => [
                'px' => [ 'min' => 0, 'max' => 50 ],
            ],
            'default'    => [ 'unit' => 'px', 'size' => 8 ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-phone-inner' => 'gap: {{SIZE}}{{UNIT}};',
            ],
            'condition' => [
                'layout_mode!' => 'number_only',
            ],
        ] );

        $this->end_controls_section();

        // -------------------- Section: Phone Format --------------------
        $this->start_controls_section( 'section_format', [
            'label' => __( 'Phone Format', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'display_format', [
            'label'   => __( 'Display Format', 'paradise-widgets-for-elementor' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'raw',
            'options' => [
                'raw'           => 'Raw (as entered)',
                'international' => 'International  e.g. +1 212 555 1234',
                'local'         => 'Local  e.g. (212) 555-1234',
                'dashes'        => 'Dashes  e.g. 212-555-1234',
                'dots'          => 'Dots  e.g. 212.555.1234',
                'custom_mask'   => 'Custom Mask',
            ],
        ] );

        $this->add_control( 'custom_mask', [
            'label'       => __( 'Custom Mask', 'paradise-widgets-for-elementor' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => __( '(###) ###-####', 'paradise-widgets-for-elementor' ),
            'description' => __( 'Use # for each digit. Example: (###) ###-####', 'paradise-widgets-for-elementor' ),
            'label_block' => true,
            'condition'   => [ 'display_format' => 'custom_mask' ],
        ] );

        $this->end_controls_section();

        // -------------------- Section: Link --------------------
        $this->start_controls_section( 'section_link', [
            'label' => __( 'Link Settings', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'link_type', [
            'label'   => __( 'Link Type', 'paradise-widgets-for-elementor' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'tel',
            'options' => [
                'tel'      => 'Phone Call (tel:)',
                'whatsapp' => 'WhatsApp',
            ],
        ] );

        $this->add_control( 'link_scope', [
            'label'   => __( 'Link Scope', 'paradise-widgets-for-elementor' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'full',
            'options' => [
                'full'   => 'Full Widget',
                'number' => 'Number Only',
                'none'   => 'No Link',
            ],
        ] );

        $this->add_control( 'country_code', [
            'label'     => __( 'Country Code', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::SELECT,
            'default'   => '1',
            'options'   => [
                '1'      => '🇺🇸 US (+1)',
                '44'     => '🇬🇧 UK (+44)',
                '49'     => '🇩🇪 DE (+49)',
                '98'     => '🇮🇷 IR (+98)',
                '971'    => '🇦🇪 UAE (+971)',
                'custom' => 'Custom',
            ],
        ] );

        $this->add_control( 'country_code_custom', [
            'label'       => __( 'Custom Country Code', 'paradise-widgets-for-elementor' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => __( '1', 'paradise-widgets-for-elementor' ),
            'description' => __( 'Enter digits only, without +', 'paradise-widgets-for-elementor' ),
            'condition'   => [ 'country_code' => 'custom' ],
        ] );

        $this->end_controls_section();

        // ============================================================
        // TAB: STYLE
        // ============================================================

        // -------------------- Style: General --------------------
        $this->start_controls_section( 'section_style_general', [
            'label' => __( 'General', 'paradise-widgets-for-elementor' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_responsive_control( 'align', [
            'label'     => __( 'Alignment', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::CHOOSE,
            'options'   => [
                'flex-start' => [ 'title' => __( 'Left', 'paradise-widgets-for-elementor' ),   'icon' => 'eicon-text-align-left' ],
                'center'     => [ 'title' => __( 'Center', 'paradise-widgets-for-elementor' ), 'icon' => 'eicon-text-align-center' ],
                'flex-end'   => [ 'title' => __( 'Right', 'paradise-widgets-for-elementor' ),  'icon' => 'eicon-text-align-right' ],
            ],
            'selectors' => [
                '{{WRAPPER}} .paradise-phone-link-wrapper' => 'align-items: {{VALUE}}; justify-content: {{VALUE}};',
            ],
        ] );

        $this->end_controls_section();

        // -------------------- Style: Prefix --------------------
        $this->start_controls_section( 'section_style_prefix', [
            'label'     => __( 'Prefix', 'paradise-widgets-for-elementor' ),
            'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [ 'prefix_enabled' => 'yes' ],
        ] );

        $this->add_control( 'prefix_color', [
            'label'     => __( 'Color', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .paradise-phone-prefix' => 'color: {{VALUE}};' ],
        ] );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'prefix_typography',
                'selector' => '{{WRAPPER}} .paradise-phone-prefix',
            ]
        );

        $this->add_responsive_control( 'prefix_margin', [
            'label'      => __( 'Margin', 'paradise-widgets-for-elementor' ),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em', 'rem' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-phone-prefix' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->end_controls_section();

        // -------------------- Style: Phone Number --------------------
        $this->start_controls_section( 'section_style_phone', [
            'label' => __( 'Phone Number', 'paradise-widgets-for-elementor' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'phone_color', [
            'label'     => __( 'Color', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .paradise-phone-number' => 'color: {{VALUE}};' ],
        ] );

        $this->add_control( 'phone_hover_color', [
            'label'     => __( 'Hover Color', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} a:hover .paradise-phone-number'           => 'color: {{VALUE}};',
                '{{WRAPPER}} .paradise-phone-number-link:hover'        => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'phone_typography',
                'selector' => '{{WRAPPER}} .paradise-phone-number',
            ]
        );

        $this->end_controls_section();

        // -------------------- Style: Icon --------------------
        $this->start_controls_section( 'section_style_icon', [
            'label'     => __( 'Icon', 'paradise-widgets-for-elementor' ),
            'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [
                'layout_mode' => [ 'icon_number', 'icon_prefix_number' ],
            ],
        ] );

        $this->add_control( 'icon_color', [
            'label'     => __( 'Color', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .paradise-phone-icon i'   => 'color: {{VALUE}};',
                '{{WRAPPER}} .paradise-phone-icon svg' => 'fill: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'icon_hover_color', [
            'label'     => __( 'Hover Color', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} a:hover .paradise-phone-icon i'   => 'color: {{VALUE}};',
                '{{WRAPPER}} a:hover .paradise-phone-icon svg' => 'fill: {{VALUE}};',
            ],
        ] );

        $this->add_responsive_control( 'icon_size', [
            'label'      => __( 'Size', 'paradise-widgets-for-elementor' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'em', 'rem' ],
            'range'      => [
                'px' => [ 'min' => 6, 'max' => 100 ],
            ],
            'default'    => [ 'unit' => 'px', 'size' => 22 ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-phone-icon i'   => 'font-size: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .paradise-phone-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->end_controls_section();

        // -------------------- Style: Link (button-like) --------------------
        $this->start_controls_section( 'section_style_link', [
            'label' => __( 'Link / Button', 'paradise-widgets-for-elementor' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'link_text_decoration', [
            'label'     => __( 'Text Decoration', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::SELECT,
            'default'   => 'none',
            'options'   => [
                'none'      => 'None',
                'underline' => 'Underline',
            ],
            'selectors' => [
                '{{WRAPPER}} a'                          => 'text-decoration: {{VALUE}};',
                '{{WRAPPER}} .paradise-phone-number-link'=> 'text-decoration: {{VALUE}};',
            ],
        ] );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name'     => 'link_bg',
                'label'    => __( 'Background', 'paradise-widgets-for-elementor' ),
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .paradise-phone-link-wrapper a, {{WRAPPER}} .paradise-phone-number-link',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'     => 'link_border',
                'selector' => '{{WRAPPER}} .paradise-phone-link-wrapper a, {{WRAPPER}} .paradise-phone-number-link',
            ]
        );

        $this->add_responsive_control( 'link_border_radius', [
            'label'      => __( 'Border Radius', 'paradise-widgets-for-elementor' ),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%', 'em' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-phone-link-wrapper a, {{WRAPPER}} .paradise-phone-number-link' =>
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->add_responsive_control( 'link_padding', [
            'label'      => __( 'Padding', 'paradise-widgets-for-elementor' ),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em', 'rem' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-phone-link-wrapper a, {{WRAPPER}} .paradise-phone-number-link' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->end_controls_section();
    }

    // ================================================================
    // RENDER
    // ================================================================

    protected function render() {
        $settings = $this->get_settings_for_display();

        $raw_phone = trim( $settings['phone_number'] ?? '' );

        if ( empty( $raw_phone ) ) {
            $this->render_editor_placeholder(
                __( 'Set the phone number in the widget settings.', 'paradise-widgets-for-elementor' )
            );
            return;
        }

        $cc        = $this->resolve_country_code( $settings );
        $link_type = $settings['link_type'] ?? 'tel';
        $href      = $this->build_phone_href( $raw_phone, $cc, $link_type );
        $display_text = $this->format_phone_display( $raw_phone, $settings );

        $layout_mode  = $settings['layout_mode']  ?? 'number_only';
        $direction    = $settings['direction']     ?? 'inline';
        $link_scope   = $settings['link_scope']    ?? 'full';
        $icon_pos     = $settings['icon_position'] ?? 'before';

        $show_prefix = ( $settings['prefix_enabled'] === 'yes' )
                       && in_array( $layout_mode, [ 'prefix_number', 'icon_prefix_number' ] );
        $show_icon   = in_array( $layout_mode, [ 'icon_number', 'icon_prefix_number' ] )
                       && ! empty( $settings['selected_icon']['value'] );

        // Direction class
        $dir_class = ( $direction === 'stacked' ) ? 'paradise-stacked' : 'paradise-inline';

        // ---- Build icon HTML ----
        $icon_html = '';
        if ( $show_icon ) {
            ob_start();
            \Elementor\Icons_Manager::render_icon(
                $settings['selected_icon'],
                [ 'aria-hidden' => 'true' ]
            );
            $icon_html = '<span class="paradise-phone-icon">' . ob_get_clean() . '</span>';
        }

        // ---- Build prefix HTML ----
        $prefix_html = '';
        if ( $show_prefix && ! empty( $settings['prefix_text'] ) ) {
            $tag         = in_array( $settings['prefix_tag'], [ 'h1','h2','h3','h4','h5','h6','p','div','span' ] )
                           ? $settings['prefix_tag'] : 'span';
            $prefix_html = sprintf(
                '<%1$s class="paradise-phone-prefix">%2$s</%1$s>',
                $tag,
                esc_html( $settings['prefix_text'] )
            );
        }

        // ---- Build number HTML ----
        $number_span = sprintf(
            '<span class="paradise-phone-number">%s</span>',
            esc_html( $display_text )
        );

        // ---- Assemble inner content ----
        // Order: [icon_before] [prefix] [icon (if after + no prefix)] [number] [icon_after]
        $inner_parts = [];

        if ( $show_icon && $icon_pos === 'before' ) {
            $inner_parts[] = $icon_html;
        }

        if ( $prefix_html ) {
            $inner_parts[] = $prefix_html;
        }

        // Number — wrapped in <a> if scope = 'number'
        if ( $link_scope === 'number' ) {
            $aria = 'whatsapp' === $link_type
                ? sprintf( 'WhatsApp %s', $display_text )
                : sprintf( 'Call %s', $display_text );
            $inner_parts[] = sprintf(
                '<a href="%s" class="paradise-phone-number-link" aria-label="%s">%s</a>',
                esc_url( $href ),
                esc_attr( $aria ),
                $number_span
            );
        } else {
            $inner_parts[] = $number_span;
        }

        if ( $show_icon && $icon_pos === 'after' ) {
            $inner_parts[] = $icon_html;
        }

        $inner_html = implode( '', $inner_parts );

        // ---- Wrapper + optional full-widget link ----
        $wrapper_open  = '<div class="paradise-phone-link-wrapper">';
        $wrapper_close = '</div>';

        if ( $link_scope === 'full' ) {
            $aria   = 'whatsapp' === $link_type
                ? sprintf( 'WhatsApp %s', $display_text )
                : sprintf( 'Call %s', $display_text );
            $target = 'whatsapp' === $link_type ? ' target="_blank" rel="noopener noreferrer"' : '';
            $inner_html = sprintf(
                '<a href="%s" class="paradise-phone-inner %s" aria-label="%s"%s>%s</a>',
                esc_url( $href ),
                esc_attr( $dir_class ),
                esc_attr( $aria ),
                $target,
                $inner_html
            );
        } else {
            $inner_html = sprintf(
                '<div class="paradise-phone-inner %s">%s</div>',
                esc_attr( $dir_class ),
                $inner_html
            );
        }

        echo $wrapper_open . $inner_html . $wrapper_close; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped markup assembled above
    }
}
