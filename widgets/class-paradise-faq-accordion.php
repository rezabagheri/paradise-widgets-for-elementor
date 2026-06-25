<?php
/**
 * Paradise FAQ Accordion Widget
 *
 * Repeater of Q&A items with smooth accordion or multi-expand behavior.
 * Outputs Schema.org FAQPage JSON-LD for SEO.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Paradise_Faq_Accordion_Widget extends Paradise_Widget_Base {

    public function get_name(): string    { return 'paradise_faq_accordion'; }
    public function get_title(): string   { return esc_html__( 'FAQ Accordion', 'paradise-widgets-for-elementor' ); }
    public function get_icon(): string    { return 'eicon-accordion'; }
    public function get_keywords(): array { return [ 'faq', 'accordion', 'question', 'answer', 'toggle', 'collapse' ]; }

    // get_categories() and get_style_depends() come from the base — defaults
    // match. Override get_script_depends() because this widget ships a JS
    // file for expand/collapse toggling.
    public function get_script_depends(): array { return [ $this->get_default_handle() ]; }

    // ── Controls ──────────────────────────────────────────────────────────────

    protected function register_controls(): void {

        // ── Content: Items ────────────────────────────────────────────────────

        $this->start_controls_section( 'section_items', [
            'label' => esc_html__( 'FAQ Items', 'paradise-widgets-for-elementor' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $cpt_active = Paradise_EW_Admin::feature_enabled( 'faq_cpt' );

        $this->add_control( 'source', [
            'label'   => esc_html__( 'Source', 'paradise-widgets-for-elementor' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'static',
            'options' => array_filter( [
                'static'  => esc_html__( 'Static (enter manually)', 'paradise-widgets-for-elementor' ),
                'cpt'     => $cpt_active ? esc_html__( 'FAQ Post Type', 'paradise-widgets-for-elementor' ) : null,
            ] ),
        ] );

        if ( $cpt_active ) {
            $this->add_control( 'cpt_post_id', [
                'label'     => esc_html__( 'FAQ Set', 'paradise-widgets-for-elementor' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'options'   => Paradise_FAQ_CPT::get_posts_for_select(),
                'default'   => '',
                'condition' => [ 'source' => 'cpt' ],
            ] );
        }

        $this->add_control( 'items', [
            'condition' => [ 'source' => 'static' ],
            'label'  => esc_html__( 'Items', 'paradise-widgets-for-elementor' ),
            'type'   => \Elementor\Controls_Manager::REPEATER,
            'fields' => [
                [
                    'name'        => 'question',
                    'label'       => esc_html__( 'Question', 'paradise-widgets-for-elementor' ),
                    'type'        => \Elementor\Controls_Manager::TEXT,
                    'label_block' => true,
                    'default'     => esc_html__( 'What is your question?', 'paradise-widgets-for-elementor' ),
                    'dynamic'     => [ 'active' => true ],
                ],
                [
                    'name'    => 'answer',
                    'label'   => esc_html__( 'Answer', 'paradise-widgets-for-elementor' ),
                    'type'    => \Elementor\Controls_Manager::WYSIWYG,
                    'default' => esc_html__( 'Your answer goes here.', 'paradise-widgets-for-elementor' ),
                    'dynamic' => [ 'active' => true ],
                ],
            ],
            'default' => [
                [
                    'question' => esc_html__( 'What services do you offer?', 'paradise-widgets-for-elementor' ),
                    'answer'   => esc_html__( 'We offer a wide range of services tailored to your needs. Contact us to learn more.', 'paradise-widgets-for-elementor' ),
                ],
                [
                    'question' => esc_html__( 'How can I get in touch?', 'paradise-widgets-for-elementor' ),
                    'answer'   => esc_html__( 'You can reach us by phone, email, or through our contact form.', 'paradise-widgets-for-elementor' ),
                ],
                [
                    'question' => esc_html__( 'What are your business hours?', 'paradise-widgets-for-elementor' ),
                    'answer'   => esc_html__( 'We are open Monday through Friday, 9 AM to 5 PM.', 'paradise-widgets-for-elementor' ),
                ],
            ],
            'title_field' => '{{{ question }}}',
        ] );

        $this->end_controls_section();

        // ── Content: Behavior ─────────────────────────────────────────────────

        $this->start_controls_section( 'section_behavior', [
            'label' => esc_html__( 'Behavior', 'paradise-widgets-for-elementor' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'behavior', [
            'label'   => esc_html__( 'Mode', 'paradise-widgets-for-elementor' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'accordion',
            'options' => [
                'accordion' => esc_html__( 'Accordion — one open at a time', 'paradise-widgets-for-elementor' ),
                'multi'     => esc_html__( 'Multi-expand — any can be open', 'paradise-widgets-for-elementor' ),
            ],
        ] );

        $this->add_control( 'open_first', [
            'label'        => esc_html__( 'Open First Item by Default', 'paradise-widgets-for-elementor' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
        ] );

        $this->add_control( 'show_icon', [
            'label'        => esc_html__( 'Show Icon', 'paradise-widgets-for-elementor' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
        ] );

        $this->add_control( 'icon_closed', [
            'label'                  => esc_html__( 'Closed Icon', 'paradise-widgets-for-elementor' ),
            'type'                   => \Elementor\Controls_Manager::ICONS,
            'default'                => [ 'value' => 'eicon-chevron-down', 'library' => 'eicons' ],
            'skin'                   => 'inline',
            'exclude_inline_options' => [ 'svg' ],
            'condition'              => [ 'show_icon' => 'yes' ],
        ] );

        $this->add_control( 'icon_open', [
            'label'                  => esc_html__( 'Open Icon', 'paradise-widgets-for-elementor' ),
            'type'                   => \Elementor\Controls_Manager::ICONS,
            'default'                => [ 'value' => 'eicon-chevron-up', 'library' => 'eicons' ],
            'skin'                   => 'inline',
            'exclude_inline_options' => [ 'svg' ],
            'condition'              => [ 'show_icon' => 'yes' ],
        ] );

        $this->add_control( 'icon_position', [
            'label'     => esc_html__( 'Icon Position', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::SELECT,
            'default'   => 'right',
            'options'   => [
                'right' => esc_html__( 'Right', 'paradise-widgets-for-elementor' ),
                'left'  => esc_html__( 'Left', 'paradise-widgets-for-elementor' ),
            ],
            'condition' => [ 'show_icon' => 'yes' ],
        ] );

        $this->add_control( 'schema_faq', [
            'label'        => esc_html__( 'Output FAQ Schema (SEO)', 'paradise-widgets-for-elementor' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
            'description'  => esc_html__( 'Adds Schema.org FAQPage JSON-LD markup for Google rich results.', 'paradise-widgets-for-elementor' ),
        ] );

        $this->end_controls_section();

        // ── Style: Items ──────────────────────────────────────────────────────

        $this->start_controls_section( 'section_style_items', [
            'label' => esc_html__( 'Items', 'paradise-widgets-for-elementor' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_responsive_control( 'item_gap', [
            'label'      => esc_html__( 'Gap Between Items', 'paradise-widgets-for-elementor' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
            'default'    => [ 'unit' => 'px', 'size' => 8 ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-faq-wrap' => 'gap: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->add_control( 'item_bg', [
            'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .paradise-faq-item' => 'background-color: {{VALUE}};',
            ],
        ] );

        $this->add_group_control( \Elementor\Group_Control_Border::get_type(), [
            'name'     => 'item_border',
            'selector' => '{{WRAPPER}} .paradise-faq-item',
        ] );

        $this->add_responsive_control( 'item_border_radius', [
            'label'      => esc_html__( 'Border Radius', 'paradise-widgets-for-elementor' ),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em', '%' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-faq-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), [
            'name'     => 'item_box_shadow',
            'selector' => '{{WRAPPER}} .paradise-faq-item',
        ] );

        $this->end_controls_section();

        // ── Style: Question ───────────────────────────────────────────────────

        $this->start_controls_section( 'section_style_question', [
            'label' => esc_html__( 'Question', 'paradise-widgets-for-elementor' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
            'name'     => 'question_typography',
            'selector' => '{{WRAPPER}} .paradise-faq-q-text',
        ] );

        $this->add_responsive_control( 'question_padding', [
            'label'      => esc_html__( 'Padding', 'paradise-widgets-for-elementor' ),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-faq-question' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->start_controls_tabs( 'tabs_question' );

        $this->start_controls_tab( 'tab_question_normal', [
            'label' => esc_html__( 'Normal', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'question_color', [
            'label'     => esc_html__( 'Text Color', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .paradise-faq-question' => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'question_bg', [
            'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .paradise-faq-question' => 'background-color: {{VALUE}};',
            ],
        ] );

        $this->end_controls_tab();

        $this->start_controls_tab( 'tab_question_active', [
            'label' => esc_html__( 'Active', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'question_color_active', [
            'label'     => esc_html__( 'Text Color', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .paradise-faq-item--open > .paradise-faq-question' => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'question_bg_active', [
            'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .paradise-faq-item--open > .paradise-faq-question' => 'background-color: {{VALUE}};',
            ],
        ] );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();

        // ── Style: Answer ─────────────────────────────────────────────────────

        $this->start_controls_section( 'section_style_answer', [
            'label' => esc_html__( 'Answer', 'paradise-widgets-for-elementor' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
            'name'     => 'answer_typography',
            'selector' => '{{WRAPPER}} .paradise-faq-answer-inner',
        ] );

        $this->add_control( 'answer_color', [
            'label'     => esc_html__( 'Text Color', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .paradise-faq-answer-inner' => 'color: {{VALUE}};',
            ],
        ] );

        $this->add_control( 'answer_bg', [
            'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .paradise-faq-answer' => 'background-color: {{VALUE}};',
            ],
        ] );

        $this->add_responsive_control( 'answer_padding', [
            'label'      => esc_html__( 'Padding', 'paradise-widgets-for-elementor' ),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-faq-answer-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->add_control( 'answer_divider_color', [
            'label'     => esc_html__( 'Divider Color', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .paradise-faq-item--open > .paradise-faq-answer' => 'border-top-color: {{VALUE}};',
            ],
        ] );

        $this->end_controls_section();

        // ── Style: Icon ───────────────────────────────────────────────────────

        $this->start_controls_section( 'section_style_icon', [
            'label'     => esc_html__( 'Icon', 'paradise-widgets-for-elementor' ),
            'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [ 'show_icon' => 'yes' ],
        ] );

        $this->add_responsive_control( 'icon_size', [
            'label'      => esc_html__( 'Size', 'paradise-widgets-for-elementor' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 10, 'max' => 40 ] ],
            'default'    => [ 'unit' => 'px', 'size' => 18 ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-faq-q-icon i'   => 'font-size: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .paradise-faq-q-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->start_controls_tabs( 'tabs_icon_color' );

        $this->start_controls_tab( 'tab_icon_normal', [
            'label' => esc_html__( 'Normal', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'icon_color', [
            'label'     => esc_html__( 'Color', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .paradise-faq-q-icon' => 'color: {{VALUE}};',
            ],
        ] );

        $this->end_controls_tab();

        $this->start_controls_tab( 'tab_icon_active', [
            'label' => esc_html__( 'Active', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'icon_color_active', [
            'label'     => esc_html__( 'Color', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .paradise-faq-item--open .paradise-faq-q-icon' => 'color: {{VALUE}};',
            ],
        ] );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    // ── Render ────────────────────────────────────────────────────────────────

    protected function render(): void {
        $settings  = $this->get_settings_for_display();
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
        $source    = $settings['source'] ?? 'static';

        if ( 'cpt' === $source && Paradise_EW_Admin::feature_enabled( 'faq_cpt' ) ) {
            $post_id = (int) ( $settings['cpt_post_id'] ?? 0 );
            if ( 0 === $post_id ) {
                if ( $is_editor ) {
                    echo '<div class="paradise-faq-placeholder">' . esc_html__( 'Select a FAQ Set from the widget settings.', 'paradise-widgets-for-elementor' ) . '</div>';
                }
                return;
            }
            $items = Paradise_FAQ_CPT::get_items( $post_id );
        } else {
            $items = $settings['items'] ?? [];
        }

        if ( empty( $items ) ) {
            if ( $is_editor ) {
                $msg = 'cpt' === $source
                    ? esc_html__( 'No items found in this FAQ Set. Add some under Paradise → FAQs.', 'paradise-widgets-for-elementor' )
                    : esc_html__( 'Add FAQ items in the widget settings.', 'paradise-widgets-for-elementor' );
                echo '<div class="paradise-faq-placeholder">' . esc_html( $msg ) . '</div>';
            }
            return;
        }

        $behavior   = $settings['behavior']      ?? 'accordion';
        $icon_pos   = $settings['icon_position'] ?? 'right';
        $open_first = 'yes' === ( $settings['open_first'] ?? 'yes' );
        $has_icon   = 'yes' === ( $settings['show_icon']  ?? 'yes' );

        $icon_closed = $settings['icon_closed'] ?? [];
        $icon_open   = $settings['icon_open']   ?? [];

        // Fallback to defaults if Elementor didn't initialize the control values
        if ( $has_icon && empty( $icon_closed['value'] ) ) {
            $icon_closed = [ 'value' => 'eicon-chevron-down', 'library' => 'eicons' ];
        }
        if ( $has_icon && empty( $icon_open['value'] ) ) {
            $icon_open = [ 'value' => 'eicon-chevron-up', 'library' => 'eicons' ];
        }

        $wrap_classes = [ 'paradise-faq-wrap' ];
        if ( $has_icon ) {
            $wrap_classes[] = 'paradise-faq--icon-pos-' . sanitize_html_class( $icon_pos );
        }
        ?>
        <div
            class="<?php echo esc_attr( implode( ' ', $wrap_classes ) ); ?>"
            data-faq-mode="<?php echo esc_attr( $behavior ); ?>"
            itemscope itemtype="https://schema.org/FAQPage"
        >
        <?php foreach ( $items as $idx => $item ) :
            $is_open = $open_first && 0 === $idx;
            $answer_id = 'paradise-faq-ans-' . $this->get_id() . '-' . $idx;
        ?>
            <div
                class="paradise-faq-item<?php echo $is_open ? ' paradise-faq-item--open' : ''; ?>"
                itemscope itemprop="mainEntity" itemtype="https://schema.org/Question"
            >
                <div
                    class="paradise-faq-question"
                    role="button"
                    tabindex="0"
                    aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
                    aria-controls="<?php echo esc_attr( $answer_id ); ?>"
                >
                    <span class="paradise-faq-q-text" itemprop="name"><?php echo esc_html( $item['question'] ?? '' ); ?></span>
                    <?php if ( $has_icon ) : ?>
                    <span class="paradise-faq-q-icon paradise-faq-q-icon--closed" aria-hidden="true">
                        <?php \Elementor\Icons_Manager::render_icon( $icon_closed, [ 'aria-hidden' => 'true' ] ); ?>
                    </span>
                    <span class="paradise-faq-q-icon paradise-faq-q-icon--open" aria-hidden="true">
                        <?php \Elementor\Icons_Manager::render_icon( $icon_open, [ 'aria-hidden' => 'true' ] ); ?>
                    </span>
                    <?php endif; ?>
                </div>

                <div
                    class="paradise-faq-answer"
                    id="<?php echo esc_attr( $answer_id ); ?>"
                    role="region"
                    itemprop="acceptedAnswer" itemscope itemtype="https://schema.org/Answer"
                >
                    <div class="paradise-faq-answer-inner" itemprop="text">
                        <?php echo wp_kses_post( $item['answer'] ?? '' ); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>

        <?php
        if ( 'yes' === ( $settings['schema_faq'] ?? 'yes' ) ) {
            $schema_items = [];
            foreach ( $items as $item ) {
                $q = wp_strip_all_tags( $item['question'] ?? '' );
                $a = wp_strip_all_tags( $item['answer']   ?? '' );
                if ( '' !== $q && '' !== $a ) {
                    $schema_items[] = [
                        '@type'          => 'Question',
                        'name'           => $q,
                        'acceptedAnswer' => [ '@type' => 'Answer', 'text' => $a ],
                    ];
                }
            }
            if ( ! empty( $schema_items ) ) {
                $schema = [
                    '@context'   => 'https://schema.org',
                    '@type'      => 'FAQPage',
                    'mainEntity' => $schema_items,
                ];
                echo '<script type="application/ld+json">'
                   . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
                   . '</script>';
            }
        }
    }
}
