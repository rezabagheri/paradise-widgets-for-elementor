<?php
/**
 * Paradise Author Card Widget
 *
 * Displays an author's profile card: photo, name, title/credentials,
 * bio, custom meta fields, and an optional CTA button.
 *
 * Photo sources:
 *   paradise  — paradise_profile_photo meta (falls back to Gravatar)
 *   gravatar  — WordPress Gravatar / get_avatar_url()
 *   meta_key  — any user meta key (attachment ID or URL)
 *   static    — manual image chosen in Elementor
 *
 * Custom fields are driven by a Repeater: each row maps a label to a meta key.
 * Compatible with ACF, native user meta, or any plugin that stores user meta.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Repeater;

class Paradise_Author_Card_Widget extends Paradise_Widget_Base {

    public function get_name(): string {
        return 'paradise_author_card';
    }

    public function get_title(): string {
        return esc_html__( 'Author Card', 'paradise-widgets-for-elementor' );
    }

    public function get_icon(): string {
        return 'eicon-person';
    }

    // get_categories() and get_style_depends() come from the base — defaults
    // match (paradise category, 'paradise-author-card' style handle).
    // No JS file: all interactions are CSS-only.

    // =========================================================================
    // Controls
    // =========================================================================

    protected function register_controls(): void {
        $this->register_content_controls();
        $this->register_style_controls();
    }

    // -------------------------------------------------------------------------
    // Content Tab
    // -------------------------------------------------------------------------

    private function register_content_controls(): void {

        // ── Author source ─────────────────────────────────────────────────────
        $this->start_controls_section( 'section_author', [
            'label' => esc_html__( 'Author', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'author_source', [
            'label'   => esc_html__( 'Author Source', 'paradise-widgets-for-elementor' ),
            'type'    => Controls_Manager::SELECT,
            'default' => 'current',
            'options' => [
                'current'  => esc_html__( 'Current Post Author', 'paradise-widgets-for-elementor' ),
                'specific' => esc_html__( 'Specific User', 'paradise-widgets-for-elementor' ),
            ],
        ] );

        $this->add_control( 'user_id', [
            'label'       => esc_html__( 'User ID', 'paradise-widgets-for-elementor' ),
            'type'        => Controls_Manager::NUMBER,
            'min'         => 1,
            'description' => esc_html__( 'Find User ID in wp-admin → Users → hover the user name.', 'paradise-widgets-for-elementor' ),
            'condition'   => [ 'author_source' => 'specific' ],
            'dynamic'     => [ 'active' => true ],
        ] );

        $this->end_controls_section();

        // ── Photo ─────────────────────────────────────────────────────────────
        $this->start_controls_section( 'section_photo', [
            'label' => esc_html__( 'Photo', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'show_photo', [
            'label'        => esc_html__( 'Show Photo', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
        ] );

        $this->add_control( 'photo_source', [
            'label'     => esc_html__( 'Photo Source', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::SELECT,
            'default'   => 'paradise',
            'options'   => [
                'paradise' => esc_html__( 'Paradise Profile Photo', 'paradise-widgets-for-elementor' ),
                'gravatar' => esc_html__( 'Gravatar / WordPress Avatar', 'paradise-widgets-for-elementor' ),
                'meta_key' => esc_html__( 'Custom Meta Key', 'paradise-widgets-for-elementor' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- not a query; SELECT control option label
                'static'   => esc_html__( 'Static Image', 'paradise-widgets-for-elementor' ),
            ],
            'condition' => [ 'show_photo' => 'yes' ],
        ] );

        $this->add_control( 'photo_meta_key', [
            'label'       => esc_html__( 'Meta Key', 'paradise-widgets-for-elementor' ),
            'type'        => Controls_Manager::TEXT,
            'placeholder' => __( 'e.g. acf_profile_photo', 'paradise-widgets-for-elementor' ),
            'description' => esc_html__( 'User meta key that holds an attachment ID or image URL.', 'paradise-widgets-for-elementor' ),
            'condition'   => [
                'show_photo'   => 'yes',
                'photo_source' => 'meta_key',
            ],
        ] );

        $this->add_control( 'photo_static', [
            'label'     => esc_html__( 'Image', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::MEDIA,
            'condition' => [
                'show_photo'   => 'yes',
                'photo_source' => 'static',
            ],
        ] );

        $this->add_control( 'photo_image_size', [
            'label'     => esc_html__( 'Image Resolution', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::SELECT,
            'default'   => 'medium',
            'options'   => [
                'thumbnail' => esc_html__( 'Thumbnail (150px)', 'paradise-widgets-for-elementor' ),
                'medium'    => esc_html__( 'Medium (300px)', 'paradise-widgets-for-elementor' ),
                'large'     => esc_html__( 'Large (1024px)', 'paradise-widgets-for-elementor' ),
                'full'      => esc_html__( 'Full', 'paradise-widgets-for-elementor' ),
            ],
            'condition' => [
                'show_photo'   => 'yes',
                'photo_source' => [ 'paradise', 'gravatar', 'meta_key' ],
            ],
        ] );

        $this->end_controls_section();

        // ── Info ──────────────────────────────────────────────────────────────
        $this->start_controls_section( 'section_info', [
            'label' => esc_html__( 'Info', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'show_name', [
            'label'        => esc_html__( 'Show Name', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
        ] );

        $this->add_control( 'name_format', [
            'label'     => esc_html__( 'Name Format', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::SELECT,
            'default'   => 'display_name',
            'options'   => [
                'display_name' => esc_html__( 'Display Name', 'paradise-widgets-for-elementor' ),
                'first_last'   => esc_html__( 'First Last', 'paradise-widgets-for-elementor' ),
                'last_first'   => esc_html__( 'Last, First', 'paradise-widgets-for-elementor' ),
                'first'        => esc_html__( 'First Name Only', 'paradise-widgets-for-elementor' ),
                'last'         => esc_html__( 'Last Name Only', 'paradise-widgets-for-elementor' ),
            ],
            'condition' => [ 'show_name' => 'yes' ],
        ] );

        $this->add_control( 'show_credentials', [
            'label'        => esc_html__( 'Show Credentials', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
            'description'  => esc_html__( 'Degree/license abbreviations shown inline after the name (e.g. "Anna Kravtson, D.O."). Reads paradise_profile_credentials from user meta.', 'paradise-widgets-for-elementor' ),
            'condition'    => [ 'show_name' => 'yes' ],
        ] );

        $this->add_control( 'credentials_separator', [
            'label'     => esc_html__( 'Credentials Separator', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::TEXT,
            'default'   => ', ',
            'condition' => [ 'show_credentials' => 'yes', 'show_name' => 'yes' ],
        ] );

        $this->add_control( 'show_title', [
            'label'        => esc_html__( 'Show Role / Position', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
            'description'  => esc_html__( 'Role or position shown as a subtitle below the name (e.g. "Medical Writer & Clinical Contributor"). Reads paradise_profile_title from user meta.', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'show_bio', [
            'label'        => esc_html__( 'Show Bio', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
            'description'  => esc_html__( 'Reads the WordPress "Biographical Info" field.', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'bio_word_limit', [
            'label'       => esc_html__( 'Bio Word Limit', 'paradise-widgets-for-elementor' ),
            'type'        => Controls_Manager::NUMBER,
            'min'         => 0,
            'default'     => 0,
            'description' => esc_html__( '0 = no limit.', 'paradise-widgets-for-elementor' ),
            'condition'   => [ 'show_bio' => 'yes' ],
        ] );

        $this->end_controls_section();

        // ── Links ─────────────────────────────────────────────────────────────
        $this->start_controls_section( 'section_links', [
            'label' => esc_html__( 'Links', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $link_options = [
            'none'    => esc_html__( 'None', 'paradise-widgets-for-elementor' ),
            'archive' => esc_html__( 'Author Archive', 'paradise-widgets-for-elementor' ),
            'website' => esc_html__( 'Author Website', 'paradise-widgets-for-elementor' ),
            'custom'  => esc_html__( 'Custom URL', 'paradise-widgets-for-elementor' ),
        ];

        // Photo link
        $this->add_control( 'photo_link_to', [
            'label'     => esc_html__( 'Photo Link', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::SELECT,
            'default'   => 'none',
            'options'   => $link_options,
            'condition' => [ 'show_photo' => 'yes' ],
        ] );

        $this->add_control( 'photo_link_url', [
            'label'     => esc_html__( 'Photo URL', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::URL,
            'dynamic'   => [ 'active' => true ],
            'condition' => [ 'show_photo' => 'yes', 'photo_link_to' => 'custom' ],
        ] );

        $this->add_control( 'photo_link_target', [
            'label'        => esc_html__( 'Open Photo Link in New Tab', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '_blank',
            'condition'    => [ 'show_photo' => 'yes', 'photo_link_to!' => 'none' ],
        ] );

        $this->add_control( 'links_divider', [
            'type' => Controls_Manager::DIVIDER,
        ] );

        // Name link
        $this->add_control( 'name_link_to', [
            'label'     => esc_html__( 'Name Link', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::SELECT,
            'default'   => 'none',
            'options'   => $link_options,
            'condition' => [ 'show_name' => 'yes' ],
        ] );

        $this->add_control( 'name_link_url', [
            'label'     => esc_html__( 'Name URL', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::URL,
            'dynamic'   => [ 'active' => true ],
            'condition' => [ 'show_name' => 'yes', 'name_link_to' => 'custom' ],
        ] );

        $this->add_control( 'name_link_target', [
            'label'        => esc_html__( 'Open Name Link in New Tab', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '_blank',
            'condition'    => [ 'show_name' => 'yes', 'name_link_to!' => 'none' ],
        ] );

        $this->end_controls_section();

        // ── Custom Fields ─────────────────────────────────────────────────────
        $this->start_controls_section( 'section_custom_fields', [
            'label' => esc_html__( 'Custom Fields', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'custom_fields_notice', [
            'type'            => Controls_Manager::RAW_HTML,
            'raw'             => esc_html__( 'Enter the user meta key for each field. Works with ACF, User Meta plugins, or any custom user meta.', 'paradise-widgets-for-elementor' ),
            'content_classes' => 'elementor-descriptor',
        ] );

        $repeater = new Repeater();

        $repeater->add_control( 'field_label', [
            'label'       => esc_html__( 'Label', 'paradise-widgets-for-elementor' ),
            'type'        => Controls_Manager::TEXT,
            'placeholder' => esc_html__( 'e.g. Specialty', 'paradise-widgets-for-elementor' ),
        ] );

        $repeater->add_control( 'field_meta_key', [
            'label'       => esc_html__( 'Meta Key', 'paradise-widgets-for-elementor' ),
            'type'        => Controls_Manager::TEXT,
            'placeholder' => esc_html__( 'e.g. acf_specialty', 'paradise-widgets-for-elementor' ),
        ] );

        $repeater->add_control( 'field_show_label', [
            'label'        => esc_html__( 'Show Label', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
        ] );

        $repeater->add_control( 'field_type', [
            'label'   => esc_html__( 'Display Type', 'paradise-widgets-for-elementor' ),
            'type'    => Controls_Manager::SELECT,
            'default' => 'text',
            'options' => [
                'text'  => esc_html__( 'Text', 'paradise-widgets-for-elementor' ),
                'link'  => esc_html__( 'Link', 'paradise-widgets-for-elementor' ),
                'email' => esc_html__( 'Email', 'paradise-widgets-for-elementor' ),
                'badge' => esc_html__( 'Badge', 'paradise-widgets-for-elementor' ),
            ],
        ] );

        $repeater->add_control( 'field_link_label', [
            'label'       => esc_html__( 'Link Label', 'paradise-widgets-for-elementor' ),
            'type'        => Controls_Manager::TEXT,
            'description' => esc_html__( 'Leave empty to show the URL as label.', 'paradise-widgets-for-elementor' ),
            'condition'   => [ 'field_type' => 'link' ],
        ] );

        $repeater->add_control( 'field_link_target', [
            'label'        => esc_html__( 'Open in New Tab', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '_blank',
            'condition'    => [ 'field_type' => [ 'link', 'email' ] ],
        ] );

        $this->add_control( 'custom_fields', [
            'label'       => esc_html__( 'Fields', 'paradise-widgets-for-elementor' ),
            'type'        => Controls_Manager::REPEATER,
            'fields'      => $repeater->get_controls(),
            'title_field' => '{{{ field_label || field_meta_key }}}',
        ] );

        $this->end_controls_section();

        // ── Social Links ──────────────────────────────────────────────────────
        $this->start_controls_section( 'section_social', [
            'label' => esc_html__( 'Social Links', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'show_social', [
            'label'        => esc_html__( 'Show Social Links', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
            'description'  => esc_html__( 'Links are set per user in wp-admin → Users → Edit User → Social Links.', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'social_display', [
            'label'     => esc_html__( 'Display', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::SELECT,
            'default'   => 'icon_only',
            'options'   => [
                'icon_only'  => esc_html__( 'Icon Only', 'paradise-widgets-for-elementor' ),
                'icon_label' => esc_html__( 'Icon + Label', 'paradise-widgets-for-elementor' ),
                'label_only' => esc_html__( 'Label Only', 'paradise-widgets-for-elementor' ),
            ],
            'condition' => [ 'show_social' => 'yes' ],
        ] );

        $this->end_controls_section();

        // ── Button ────────────────────────────────────────────────────────────
        $this->start_controls_section( 'section_button', [
            'label' => esc_html__( 'Button', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'show_button', [
            'label'        => esc_html__( 'Show Button', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => '',
            'return_value' => 'yes',
        ] );

        $this->add_control( 'button_text', [
            'label'     => esc_html__( 'Text', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::TEXT,
            'default'   => esc_html__( 'View Profile', 'paradise-widgets-for-elementor' ),
            'condition' => [ 'show_button' => 'yes' ],
            'dynamic'   => [ 'active' => true ],
        ] );

        $this->add_control( 'button_url_type', [
            'label'     => esc_html__( 'Link To', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::SELECT,
            'default'   => 'website',
            'options'   => [
                'website' => esc_html__( 'Author\'s Website', 'paradise-widgets-for-elementor' ),
                'archive' => esc_html__( 'Author Archive', 'paradise-widgets-for-elementor' ),
                'custom'  => esc_html__( 'Custom URL', 'paradise-widgets-for-elementor' ),
            ],
            'condition' => [ 'show_button' => 'yes' ],
        ] );

        $this->add_control( 'button_url', [
            'label'     => esc_html__( 'URL', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::URL,
            'dynamic'   => [ 'active' => true ],
            'condition' => [
                'show_button'     => 'yes',
                'button_url_type' => 'custom',
            ],
        ] );

        $this->add_control( 'button_new_tab', [
            'label'        => esc_html__( 'Open in New Tab', 'paradise-widgets-for-elementor' ),
            'type'         => Controls_Manager::SWITCHER,
            'return_value' => '_blank',
            'condition'    => [ 'show_button' => 'yes' ],
        ] );

        $this->end_controls_section();
    }

    // -------------------------------------------------------------------------
    // Style Tab
    // -------------------------------------------------------------------------

    private function register_style_controls(): void {

        // ── Card Container ────────────────────────────────────────────────────
        $this->start_controls_section( 'style_card', [
            'label' => esc_html__( 'Card', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'card_bg_color', [
            'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .paradise-author-card' => 'background-color: {{VALUE}};' ],
        ] );

        $this->add_control( 'card_padding', [
            'label'      => esc_html__( 'Padding', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em', '%' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-author-card' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->add_control( 'card_border_radius', [
            'label'      => esc_html__( 'Border Radius', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-author-card' =>
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->add_group_control( Group_Control_Border::get_type(), [
            'name'     => 'card_border',
            'selector' => '{{WRAPPER}} .paradise-author-card',
        ] );

        $this->add_group_control( Group_Control_Box_Shadow::get_type(), [
            'name'     => 'card_shadow',
            'selector' => '{{WRAPPER}} .paradise-author-card',
        ] );

        $this->end_controls_section();

        // ── Layout ────────────────────────────────────────────────────────────
        $this->start_controls_section( 'style_layout', [
            'label' => esc_html__( 'Layout', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'layout', [
            'label'   => esc_html__( 'Direction', 'paradise-widgets-for-elementor' ),
            'type'    => Controls_Manager::CHOOSE,
            'default' => 'vertical',
            'options' => [
                'vertical'   => [
                    'title' => esc_html__( 'Vertical', 'paradise-widgets-for-elementor' ),
                    'icon'  => 'eicon-v-align-top',
                ],
                'horizontal' => [
                    'title' => esc_html__( 'Horizontal', 'paradise-widgets-for-elementor' ),
                    'icon'  => 'eicon-h-align-left',
                ],
            ],
        ] );

        $this->add_control( 'alignment', [
            'label'     => esc_html__( 'Alignment', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::CHOOSE,
            'default'   => 'center',
            'options'   => [
                'left'   => [ 'title' => esc_html__( 'Left', 'paradise-widgets-for-elementor' ),   'icon' => 'eicon-text-align-left' ],
                'center' => [ 'title' => esc_html__( 'Center', 'paradise-widgets-for-elementor' ), 'icon' => 'eicon-text-align-center' ],
                'right'  => [ 'title' => esc_html__( 'Right', 'paradise-widgets-for-elementor' ),  'icon' => 'eicon-text-align-right' ],
            ],
            'selectors' => [
                '{{WRAPPER}} .paradise-author-card__body' => 'text-align: {{VALUE}};',
                '{{WRAPPER}} .paradise-author-card__photo-wrap' => 'text-align: {{VALUE}};',
            ],
            'condition' => [ 'layout' => 'vertical' ],
        ] );

        $this->add_control( 'card_gap', [
            'label'     => esc_html__( 'Gap Between Photo & Content', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::SLIDER,
            'range'     => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
            'default'   => [ 'size' => 16, 'unit' => 'px' ],
            'selectors' => [ '{{WRAPPER}} .paradise-author-card' => 'gap: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->end_controls_section();

        // ── Photo ─────────────────────────────────────────────────────────────
        $this->start_controls_section( 'style_photo', [
            'label'     => esc_html__( 'Photo', 'paradise-widgets-for-elementor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'show_photo' => 'yes' ],
        ] );

        $this->add_responsive_control( 'photo_width', [
            'label'      => esc_html__( 'Size', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%', 'vw' ],
            'range'      => [
                'px'  => [ 'min' => 30,  'max' => 400 ],
                '%'   => [ 'min' => 5,   'max' => 100 ],
                'vw'  => [ 'min' => 5,   'max' => 100 ],
            ],
            'default'    => [ 'size' => 120, 'unit' => 'px' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-author-card__photo' => 'width: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->add_control( 'photo_border_radius', [
            'label'      => esc_html__( 'Border Radius', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%' ],
            'default'    => [
                'top' => 50, 'right' => 50, 'bottom' => 50, 'left' => 50,
                'unit' => '%', 'isLinked' => true,
            ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-author-card__photo' =>
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->add_group_control( Group_Control_Border::get_type(), [
            'name'     => 'photo_border',
            'selector' => '{{WRAPPER}} .paradise-author-card__photo',
        ] );

        $this->add_group_control( Group_Control_Box_Shadow::get_type(), [
            'name'     => 'photo_shadow',
            'selector' => '{{WRAPPER}} .paradise-author-card__photo',
        ] );

        $this->end_controls_section();

        // ── Name ──────────────────────────────────────────────────────────────
        $this->start_controls_section( 'style_name', [
            'label'     => esc_html__( 'Name', 'paradise-widgets-for-elementor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'show_name' => 'yes' ],
        ] );

        $this->add_control( 'name_color', [
            'label'     => esc_html__( 'Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__name' => 'color: {{VALUE}};' ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'     => 'name_typography',
            'selector' => '{{WRAPPER}} .paradise-author-card__name',
        ] );

        $this->add_control( 'name_spacing', [
            'label'     => esc_html__( 'Bottom Spacing', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::SLIDER,
            'range'     => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
            'default'   => [ 'size' => 4, 'unit' => 'px' ],
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__name' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->end_controls_section();

        // ── Title / Credentials ───────────────────────────────────────────────
        $this->start_controls_section( 'style_credentials', [
            'label'     => esc_html__( 'Credentials', 'paradise-widgets-for-elementor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'show_credentials' => 'yes', 'show_name' => 'yes' ],
        ] );

        $this->add_control( 'credentials_color', [
            'label'     => esc_html__( 'Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__credentials' => 'color: {{VALUE}};' ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'     => 'credentials_typography',
            'selector' => '{{WRAPPER}} .paradise-author-card__credentials',
        ] );

        $this->end_controls_section();

        $this->start_controls_section( 'style_title', [
            'label'     => esc_html__( 'Role / Position', 'paradise-widgets-for-elementor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'show_title' => 'yes' ],
        ] );

        $this->add_control( 'title_color', [
            'label'     => esc_html__( 'Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__title' => 'color: {{VALUE}};' ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'     => 'title_typography',
            'selector' => '{{WRAPPER}} .paradise-author-card__title',
        ] );

        $this->add_control( 'title_spacing', [
            'label'     => esc_html__( 'Bottom Spacing', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::SLIDER,
            'range'     => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
            'default'   => [ 'size' => 8, 'unit' => 'px' ],
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__title' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->end_controls_section();

        // ── Bio ───────────────────────────────────────────────────────────────
        $this->start_controls_section( 'style_bio', [
            'label'     => esc_html__( 'Bio', 'paradise-widgets-for-elementor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'show_bio' => 'yes' ],
        ] );

        $this->add_control( 'bio_color', [
            'label'     => esc_html__( 'Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__bio' => 'color: {{VALUE}};' ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'     => 'bio_typography',
            'selector' => '{{WRAPPER}} .paradise-author-card__bio',
        ] );

        $this->add_control( 'bio_spacing', [
            'label'     => esc_html__( 'Bottom Spacing', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::SLIDER,
            'range'     => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
            'default'   => [ 'size' => 12, 'unit' => 'px' ],
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__bio' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->end_controls_section();

        // ── Custom Fields ─────────────────────────────────────────────────────
        $this->start_controls_section( 'style_fields', [
            'label' => esc_html__( 'Custom Fields', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'fields_item_gap', [
            'label'     => esc_html__( 'Gap Between Fields', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::SLIDER,
            'range'     => [ 'px' => [ 'min' => 0, 'max' => 24 ] ],
            'default'   => [ 'size' => 4, 'unit' => 'px' ],
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__fields' => 'gap: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->add_control( 'field_label_color', [
            'label'     => esc_html__( 'Label Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__field-label' => 'color: {{VALUE}};' ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'     => 'field_label_typography',
            'label'    => esc_html__( 'Label Typography', 'paradise-widgets-for-elementor' ),
            'selector' => '{{WRAPPER}} .paradise-author-card__field-label',
        ] );

        $this->add_control( 'field_value_color', [
            'label'     => esc_html__( 'Value Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__field-value' => 'color: {{VALUE}};' ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'     => 'field_value_typography',
            'label'    => esc_html__( 'Value Typography', 'paradise-widgets-for-elementor' ),
            'selector' => '{{WRAPPER}} .paradise-author-card__field-value',
        ] );

        $this->add_control( 'fields_spacing', [
            'label'     => esc_html__( 'Block Bottom Spacing', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::SLIDER,
            'range'     => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
            'default'   => [ 'size' => 12, 'unit' => 'px' ],
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__fields' => 'margin-bottom: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->add_control( 'fields_link_heading', [
            'label'     => esc_html__( 'Link Type', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::HEADING,
            'separator' => 'before',
        ] );

        $this->add_control( 'field_link_color', [
            'label'     => esc_html__( 'Link Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__field-link' => 'color: {{VALUE}};' ],
        ] );

        $this->add_control( 'fields_badge_heading', [
            'label'     => esc_html__( 'Badge Type', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::HEADING,
            'separator' => 'before',
        ] );

        $this->add_control( 'field_badge_bg', [
            'label'     => esc_html__( 'Badge Background', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#f0f0f1',
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__field-badge' => 'background-color: {{VALUE}};' ],
        ] );

        $this->add_control( 'field_badge_color', [
            'label'     => esc_html__( 'Badge Text Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__field-badge' => 'color: {{VALUE}};' ],
        ] );

        $this->add_control( 'field_badge_radius', [
            'label'      => esc_html__( 'Badge Border Radius', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::SLIDER,
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 50 ] ],
            'default'    => [ 'size' => 20, 'unit' => 'px' ],
            'selectors'  => [ '{{WRAPPER}} .paradise-author-card__field-badge' => 'border-radius: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->end_controls_section();

        // ── Social Links ──────────────────────────────────────────────────────
        $this->start_controls_section( 'style_social', [
            'label' => esc_html__( 'Social Links', 'paradise-widgets-for-elementor' ),
            'tab'   => Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'social_spacing', [
            'label'     => esc_html__( 'Top Spacing', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::SLIDER,
            'range'     => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
            'default'   => [ 'size' => 12, 'unit' => 'px' ],
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__social' => 'margin-top: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->add_control( 'social_gap', [
            'label'     => esc_html__( 'Gap Between Icons', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::SLIDER,
            'range'     => [ 'px' => [ 'min' => 0, 'max' => 32 ] ],
            'default'   => [ 'size' => 10, 'unit' => 'px' ],
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__social' => 'gap: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->add_control( 'social_icon_size', [
            'label'     => esc_html__( 'Icon Size', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::SLIDER,
            'range'     => [ 'px' => [ 'min' => 10, 'max' => 48 ] ],
            'default'   => [ 'size' => 18, 'unit' => 'px' ],
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__social-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->add_control( 'social_color', [
            'label'     => esc_html__( 'Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .paradise-author-card__social-link'       => 'color: {{VALUE}};',
                '{{WRAPPER}} .paradise-author-card__social-link:hover' => 'opacity: 0.7;',
            ],
        ] );

        $this->add_control( 'social_bg_color', [
            'label'     => esc_html__( 'Icon Background', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__social-link' => 'background-color: {{VALUE}};' ],
        ] );

        $this->add_control( 'social_icon_padding', [
            'label'     => esc_html__( 'Icon Padding', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::SLIDER,
            'range'     => [ 'px' => [ 'min' => 0, 'max' => 20 ] ],
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__social-link' => 'padding: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->add_control( 'social_border_radius', [
            'label'      => esc_html__( 'Border Radius', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-author-card__social-link' =>
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'      => 'social_typography',
            'label'     => esc_html__( 'Label Typography', 'paradise-widgets-for-elementor' ),
            'selector'  => '{{WRAPPER}} .paradise-author-card__social-label',
            'condition' => [ 'social_display' => [ 'icon_label', 'label_only' ] ],
        ] );

        $this->end_controls_section();

        // ── Button ────────────────────────────────────────────────────────────
        $this->start_controls_section( 'style_button', [
            'label'     => esc_html__( 'Button', 'paradise-widgets-for-elementor' ),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => [ 'show_button' => 'yes' ],
        ] );

        $this->add_control( 'button_spacing', [
            'label'     => esc_html__( 'Top Spacing', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::SLIDER,
            'range'     => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
            'default'   => [ 'size' => 16, 'unit' => 'px' ],
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__cta' => 'margin-top: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->add_control( 'button_padding', [
            'label'      => esc_html__( 'Padding', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em' ],
            'default'    => [ 'top' => 10, 'right' => 24, 'bottom' => 10, 'left' => 24, 'unit' => 'px', 'isLinked' => false ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-author-card__btn' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->add_control( 'button_border_radius', [
            'label'      => esc_html__( 'Border Radius', 'paradise-widgets-for-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%' ],
            'default'    => [ 'top' => 4, 'right' => 4, 'bottom' => 4, 'left' => 4, 'unit' => 'px', 'isLinked' => true ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-author-card__btn' =>
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->add_control( 'button_bg_color', [
            'label'     => esc_html__( 'Background', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#333333',
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__btn' => 'background-color: {{VALUE}};' ],
        ] );

        $this->add_control( 'button_text_color', [
            'label'     => esc_html__( 'Text Color', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__btn' => 'color: {{VALUE}};' ],
        ] );

        $this->add_control( 'button_bg_hover', [
            'label'     => esc_html__( 'Background (Hover)', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__btn:hover' => 'background-color: {{VALUE}};' ],
        ] );

        $this->add_control( 'button_text_hover', [
            'label'     => esc_html__( 'Text Color (Hover)', 'paradise-widgets-for-elementor' ),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .paradise-author-card__btn:hover' => 'color: {{VALUE}};' ],
        ] );

        $this->add_group_control( Group_Control_Typography::get_type(), [
            'name'     => 'button_typography',
            'selector' => '{{WRAPPER}} .paradise-author-card__btn',
        ] );

        $this->end_controls_section();
    }

    // =========================================================================
    // Render
    // =========================================================================

    protected function render(): void {
        $settings = $this->get_settings_for_display();

        // ── Resolve author ────────────────────────────────────────────────────
        if ( 'specific' === $settings['author_source'] ) {
            $user_id = (int) $settings['user_id'];
        } else {
            $post_id = get_the_ID();
            $user_id = $post_id ? (int) get_post_field( 'post_author', $post_id ) : get_current_user_id();
        }

        $user = $user_id ? get_userdata( $user_id ) : false;

        if ( ! $user ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="paradise-author-card paradise-author-card__placeholder">'
                   . esc_html__( 'Author Card: no author found. Check the Author Source setting.', 'paradise-widgets-for-elementor' )
                   . '</div>';
            }
            return;
        }

        // ── Photo URL ─────────────────────────────────────────────────────────
        $photo_url   = '';
        $size_map    = [ 'thumbnail' => 150, 'medium' => 300, 'large' => 1024, 'full' => 1600 ];
        $size_px     = $size_map[ $settings['photo_image_size'] ] ?? 300;

        if ( 'yes' === $settings['show_photo'] ) {
            switch ( $settings['photo_source'] ) {
                case 'paradise':
                    $photo_url = Paradise_User_Profile::get_photo_url( $user_id, $size_px );
                    break;

                case 'gravatar':
                    $photo_url = (string) get_avatar_url( $user_id, [ 'size' => $size_px ] );
                    break;

                case 'meta_key':
                    $key = sanitize_key( $settings['photo_meta_key'] );
                    if ( $key ) {
                        $val = get_user_meta( $user_id, $key, true );
                        if ( is_numeric( $val ) && (int) $val > 0 ) {
                            $photo_url = (string) ( wp_get_attachment_image_url( (int) $val, [ $size_px, $size_px ] ) ?: '' );
                        } else {
                            $photo_url = esc_url_raw( (string) $val );
                        }
                    }
                    break;

                case 'static':
                    $photo_url = $settings['photo_static']['url'] ?? '';
                    break;
            }
        }

        // ── Button URL ────────────────────────────────────────────────────────
        $btn_url    = '';
        $btn_target = $settings['button_new_tab'] ?: '_self';

        if ( 'yes' === $settings['show_button'] ) {
            switch ( $settings['button_url_type'] ) {
                case 'website':
                    $btn_url = esc_url( $user->user_url );
                    break;
                case 'archive':
                    $btn_url = esc_url( (string) get_author_posts_url( $user_id ) );
                    break;
                case 'custom':
                    $btn_url = esc_url( $settings['button_url']['url'] ?? '' );
                    break;
            }
        }

        // ── Resolve name ─────────────────────────────────────────────────────
        $first_name = (string) get_user_meta( $user_id, 'first_name', true );
        $last_name  = (string) get_user_meta( $user_id, 'last_name',  true );

        switch ( $settings['name_format'] ) {
            case 'first_last':
                $display_name = trim( $first_name . ' ' . $last_name ) ?: $user->display_name;
                break;
            case 'last_first':
                $display_name = ( $last_name && $first_name )
                    ? $last_name . ', ' . $first_name
                    : ( $last_name ?: $first_name ?: $user->display_name );
                break;
            case 'first':
                $display_name = $first_name ?: $user->display_name;
                break;
            case 'last':
                $display_name = $last_name ?: $user->display_name;
                break;
            default:
                $display_name = $user->display_name;
        }

        // ── Social links — read from user meta via Paradise_User_Profile ────────
        $social_items = ( 'yes' === ( $settings['show_social'] ?? 'yes' ) )
            ? Paradise_User_Profile::get_social_links( $user_id )
            : [];

        // ── Link resolver ─────────────────────────────────────────────────────
        $resolve_link = function( string $link_to, string $custom_url ) use ( $user, $user_id ): string {
            switch ( $link_to ) {
                case 'archive':
                    return esc_url( (string) get_author_posts_url( $user_id ) );
                case 'website':
                    return esc_url( $user->user_url );
                case 'custom':
                    return esc_url( $custom_url );
                default:
                    return '';
            }
        };

        $photo_href  = $resolve_link( $settings['photo_link_to'] ?? 'none', $settings['photo_link_url']['url'] ?? '' );
        $photo_tgt   = $settings['photo_link_target'] ?: '_self';
        $name_href   = $resolve_link( $settings['name_link_to'] ?? 'none', $settings['name_link_url']['url'] ?? '' );
        $name_tgt    = $settings['name_link_target'] ?: '_self';

        // ── CSS classes ───────────────────────────────────────────────────────
        $layout    = ( 'horizontal' === $settings['layout'] ) ? 'horizontal' : 'vertical';
        $alignment = esc_attr( $settings['alignment'] ?? 'center' );

        $this->add_render_attribute( 'wrapper', 'class', [
            'paradise-author-card',
            'paradise-author-card--' . $layout,
            'paradise-author-card--align-' . $alignment,
        ] );

        // ── Output ────────────────────────────────────────────────────────────
        ?>
        <div <?php $this->print_render_attribute_string( 'wrapper' ); ?> itemscope itemtype="https://schema.org/Person">

            <?php if ( $photo_url ) : ?>
            <div class="paradise-author-card__photo-wrap">
                <?php if ( $photo_href ) : ?>
                <a href="<?php echo esc_url( $photo_href ); ?>"
                   class="paradise-author-card__photo-link"
                   target="<?php echo esc_attr( $photo_tgt ); ?>"
                   <?php echo '_blank' === $photo_tgt ? 'rel="noopener noreferrer"' : ''; ?>>
                <?php endif; ?>
                <img class="paradise-author-card__photo"
                     src="<?php echo esc_url( $photo_url ); ?>"
                     alt="<?php echo esc_attr( $user->display_name ); ?>"
                     itemprop="image"
                     loading="lazy">
                <?php if ( $photo_href ) : ?></a><?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="paradise-author-card__body">

                <?php
                $credentials = 'yes' === ( $settings['show_credentials'] ?? 'yes' )
                    ? (string) get_user_meta( $user_id, 'paradise_profile_credentials', true )
                    : '';
                $credentials_sep = $settings['credentials_separator'] ?? ', ';

                $profile_title = 'yes' === ( $settings['show_title'] ?? 'yes' )
                    ? (string) get_user_meta( $user_id, 'paradise_profile_title', true )
                    : '';
                ?>

                <?php if ( 'yes' === $settings['show_name'] ) : ?>
                <div class="paradise-author-card__name" itemprop="name">
                    <?php if ( $name_href ) : ?>
                    <a href="<?php echo esc_url( $name_href ); ?>"
                       class="paradise-author-card__name-link"
                       target="<?php echo esc_attr( $name_tgt ); ?>"
                       <?php echo '_blank' === $name_tgt ? 'rel="noopener noreferrer"' : ''; ?>>
                    <?php endif; ?>
                    <?php echo esc_html( $display_name ); ?>
                    <?php if ( $credentials ) : ?>
                    <span class="paradise-author-card__credentials" itemprop="honorificSuffix">
                        <?php echo esc_html( $credentials_sep . $credentials ); ?>
                    </span>
                    <?php endif; ?>
                    <?php if ( $name_href ) : ?></a><?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ( $profile_title ) : ?>
                <div class="paradise-author-card__title" itemprop="jobTitle">
                    <?php echo esc_html( $profile_title ); ?>
                </div>
                <?php endif; ?>

                <?php
                $bio = 'yes' === $settings['show_bio'] ? (string) $user->description : '';
                if ( $bio ) :
                    $word_limit = (int) $settings['bio_word_limit'];
                    if ( $word_limit > 0 ) {
                        $bio = wp_trim_words( $bio, $word_limit );
                    }
                ?>
                <div class="paradise-author-card__bio" itemprop="description">
                    <?php echo wp_kses_post( $bio ); ?>
                </div>
                <?php endif; ?>

                <?php
                $custom_fields = $settings['custom_fields'] ?? [];
                $visible_fields = [];
                foreach ( $custom_fields as $field ) {
                    $key = sanitize_key( $field['field_meta_key'] ?? '' );
                    if ( ! $key ) continue;
                    $value = (string) get_user_meta( $user_id, $key, true );
                    if ( '' === $value ) continue;
                    $visible_fields[] = [
                        'label'       => $field['field_label'] ?? '',
                        'value'       => $value,
                        'show_label'  => $field['field_show_label'] ?? 'yes',
                        'type'        => $field['field_type'] ?? 'text',
                        'link_label'  => $field['field_link_label'] ?? '',
                        'link_target' => $field['field_link_target'] ?? '',
                    ];
                }
                if ( $visible_fields ) :
                ?>
                <div class="paradise-author-card__fields">
                    <?php foreach ( $visible_fields as $f ) :
                        $f_type   = $f['type'];
                        $f_target = $f['link_target'] ?: '_self';
                        $f_rel    = '_blank' === $f_target ? ' rel="noopener noreferrer"' : '';
                    ?>
                    <div class="paradise-author-card__field">
                        <?php if ( 'yes' === $f['show_label'] && '' !== $f['label'] ) : ?>
                        <span class="paradise-author-card__field-label">
                            <?php echo esc_html( $f['label'] ); ?>
                        </span>
                        <?php endif; ?>
                        <?php if ( 'link' === $f_type ) : ?>
                        <a href="<?php echo esc_url( $f['value'] ); ?>"
                           class="paradise-author-card__field-value paradise-author-card__field-link"
                           target="<?php echo esc_attr( $f_target ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped markup assembled above ?>"<?php echo $f_rel; ?>>
                            <?php echo esc_html( $f['link_label'] ?: $f['value'] ); ?>
                        </a>
                        <?php elseif ( 'email' === $f_type ) : ?>
                        <a href="mailto:<?php echo esc_attr( sanitize_email( $f['value'] ) ); ?>"
                           class="paradise-author-card__field-value paradise-author-card__field-link"
                           target="<?php echo esc_attr( $f_target ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped markup assembled above ?>"<?php echo $f_rel; ?>>
                            <?php echo esc_html( $f['value'] ); ?>
                        </a>
                        <?php elseif ( 'badge' === $f_type ) : ?>
                        <span class="paradise-author-card__field-value paradise-author-card__field-badge">
                            <?php echo esc_html( $f['value'] ); ?>
                        </span>
                        <?php else : ?>
                        <span class="paradise-author-card__field-value">
                            <?php echo esc_html( $f['value'] ); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if ( $social_items ) :
                    $social_display = esc_attr( $settings['social_display'] ?? 'icon_only' );
                ?>
                <div class="paradise-author-card__social paradise-author-card__social--<?php echo esc_attr( $social_display ); ?>">
                    <?php foreach ( $social_items as $s ) : ?>
                    <a href="<?php echo esc_url( $s['href'] ); ?>"
                       class="paradise-author-card__social-link"
                       title="<?php echo esc_attr( $s['label'] ); ?>"
                       itemprop="<?php echo strpos( $s['href'], 'mailto:' ) === 0 ? 'email' : 'sameAs'; ?>"
                       <?php if ( $s['new_tab'] ) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
                        <?php if ( 'label_only' !== $social_display ) : ?>
                        <svg class="paradise-author-card__social-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
                            <path d="<?php echo esc_attr( $s['svg'] ); ?>"/>
                        </svg>
                        <?php endif; ?>
                        <?php if ( 'icon_only' !== $social_display ) : ?>
                        <span class="paradise-author-card__social-label"><?php echo esc_html( $s['label'] ); ?></span>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if ( 'yes' === $settings['show_button'] && $btn_url ) : ?>
                <div class="paradise-author-card__cta">
                    <a href="<?php echo esc_url( $btn_url ); ?>"
                       class="paradise-author-card__btn"
                       target="<?php echo esc_attr( $btn_target ); ?>"
                       itemprop="url"
                       <?php echo '_blank' === $btn_target ? 'rel="noopener noreferrer"' : ''; ?>>
                        <?php echo esc_html( $settings['button_text'] ); ?>
                    </a>
                </div>
                <?php endif; ?>

            </div><!-- /.paradise-author-card__body -->
        </div><!-- /.paradise-author-card -->
        <?php
    }
}
