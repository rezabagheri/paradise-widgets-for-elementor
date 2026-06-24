<?php
/**
 * Paradise Business Hours Widget
 *
 * Displays business hours from Site Info with an "Open Now / Closed" badge.
 * JavaScript checks the current day and time against the stored schedule
 * using the browser's local time (adjusted for the site timezone offset
 * passed via data attribute).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Paradise_Business_Hours_Widget extends Paradise_Widget_Base {

    public function get_name(): string    { return 'paradise_business_hours'; }
    public function get_title(): string   { return esc_html__( 'Business Hours', 'paradise-widgets-for-elementor' ); }
    public function get_icon(): string    { return 'eicon-clock-o'; }
    public function get_keywords(): array { return [ 'hours', 'business', 'schedule', 'open', 'closed', 'time' ]; }

    // get_categories() and get_style_depends() come from the base — defaults
    // match (paradise category, 'paradise-business-hours' style handle).
    // Override get_script_depends() because this widget ships a JS file
    // for the live "Open Now / Closed" badge update.
    public function get_script_depends(): array { return [ $this->get_default_handle() ]; }

    // ── Controls ──────────────────────────────────────────────────────────────

    protected function register_controls(): void {

        // ── Display ───────────────────────────────────────────────────────────

        $this->start_controls_section( 'section_display', [
            'label' => esc_html__( 'Display', 'paradise-widgets-for-elementor' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'location_index', [
            'label'   => esc_html__( 'Location', 'paradise-widgets-for-elementor' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => Paradise_Site_Info::get_location_select_options(),
            'default' => '0',
        ] );

        $this->add_control( 'show_badge', [
            'label'        => esc_html__( 'Show Open/Closed Badge', 'paradise-widgets-for-elementor' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
        ] );

        $this->add_control( 'highlight_today', [
            'label'        => esc_html__( 'Highlight Today', 'paradise-widgets-for-elementor' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
        ] );

        $this->add_control( 'time_format', [
            'label'   => esc_html__( 'Time Format', 'paradise-widgets-for-elementor' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => '12h',
            'options' => [
                '12h' => esc_html__( '12-hour (9:00 AM)', 'paradise-widgets-for-elementor' ),
                '24h' => esc_html__( '24-hour (09:00)',   'paradise-widgets-for-elementor' ),
            ],
        ] );

        $this->add_control( 'closed_label', [
            'label'   => esc_html__( 'Closed Day Text', 'paradise-widgets-for-elementor' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => esc_html__( 'Closed', 'paradise-widgets-for-elementor' ),
        ] );

        $this->end_controls_section();

        // ── Style: Badge ──────────────────────────────────────────────────────

        $this->start_controls_section( 'section_style_badge', [
            'label'     => esc_html__( 'Badge', 'paradise-widgets-for-elementor' ),
            'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [ 'show_badge' => 'yes' ],
        ] );

        $this->add_control( 'badge_open_bg', [
            'label'     => esc_html__( 'Open — Background', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#00a651',
            'selectors' => [ '{{WRAPPER}} .paradise-bh-badge--open' => 'background-color: {{VALUE}};' ],
        ] );

        $this->add_control( 'badge_closed_bg', [
            'label'     => esc_html__( 'Closed — Background', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#cc0000',
            'selectors' => [ '{{WRAPPER}} .paradise-bh-badge--closed' => 'background-color: {{VALUE}};' ],
        ] );

        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
            'name'     => 'badge_typography',
            'selector' => '{{WRAPPER}} .paradise-bh-badge',
        ] );

        $this->end_controls_section();

        // ── Style: Table ──────────────────────────────────────────────────────

        $this->start_controls_section( 'section_style_table', [
            'label' => esc_html__( 'Table', 'paradise-widgets-for-elementor' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
            'name'     => 'row_typography',
            'selector' => '{{WRAPPER}} .paradise-bh-row',
        ] );

        $this->add_control( 'day_color', [
            'label'     => esc_html__( 'Day Color', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .paradise-bh-day' => 'color: {{VALUE}};' ],
        ] );

        $this->add_control( 'hours_color', [
            'label'     => esc_html__( 'Hours Color', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .paradise-bh-hours' => 'color: {{VALUE}};' ],
        ] );

        $this->add_control( 'today_bg', [
            'label'     => esc_html__( 'Today Row Background', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#f0f8f0',
            'selectors' => [ '{{WRAPPER}} .paradise-bh-row--today' => 'background-color: {{VALUE}};' ],
            'condition' => [ 'highlight_today' => 'yes' ],
        ] );

        $this->add_control( 'closed_color', [
            'label'     => esc_html__( 'Closed Day Text Color', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#aaaaaa',
            'selectors' => [ '{{WRAPPER}} .paradise-bh-row--closed .paradise-bh-hours' => 'color: {{VALUE}};' ],
        ] );

        $this->add_responsive_control( 'row_padding', [
            'label'      => esc_html__( 'Row Padding', 'paradise-widgets-for-elementor' ),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-bh-day, {{WRAPPER}} .paradise-bh-hours' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->end_controls_section();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Format a 24h "HH:MM" string for display.
     */
    private function format_time( string $time24, string $format ): string {
        if ( empty( $time24 ) ) return '';
        if ( '24h' === $format ) return $time24;

        $parts = explode( ':', $time24 );
        $h     = (int) ( $parts[0] ?? 0 );
        $m     = $parts[1] ?? '00';
        $ampm  = $h >= 12 ? 'PM' : 'AM';
        $h12   = $h % 12 ?: 12;
        return $h12 . ':' . $m . ' ' . $ampm;
    }

    // ── Render ────────────────────────────────────────────────────────────────

    protected function render(): void {
        $settings       = $this->get_settings_for_display();
        $location       = (int) ( $settings['location_index'] ?? 0 );
        $hours          = Paradise_Site_Info::get_hours( $location );
        $days           = Paradise_Site_Info::days();
        $time_format    = $settings['time_format'] ?? '12h';
        $closed_label   = esc_html( $settings['closed_label'] ?: __( 'Closed', 'paradise-widgets-for-elementor' ) );
        $show_badge     = 'yes' === $settings['show_badge'];
        $highlight      = 'yes' === $settings['highlight_today'];
        $is_open_now    = Paradise_Site_Info::is_open_now( $location );

        // Pass hours as JSON so JS can compute the badge client-side.
        // We also pass is_open_now as a server-side fallback for SSR.
        $hours_json = wp_json_encode( $hours );

        // WordPress site timezone offset in minutes (for JS Date calculations).
        try {
            $tz     = new DateTimeZone( wp_timezone_string() );
            $offset = $tz->getOffset( new DateTime( 'now' ) ) / 60;
        } catch ( Exception $e ) {
            $offset = 0;
        }
        ?>
        <div
            class="paradise-bh-wrap"
            data-bh-hours="<?php echo esc_attr( $hours_json ); ?>"
            data-bh-tz-offset="<?php echo (int) $offset; ?>"
            data-bh-highlight="<?php echo $highlight ? '1' : '0'; ?>"
            data-bh-show-badge="<?php echo $show_badge ? '1' : '0'; ?>"
        >
            <?php if ( $show_badge ) : ?>
            <div class="paradise-bh-badge <?php echo $is_open_now ? 'paradise-bh-badge--open' : 'paradise-bh-badge--closed'; ?>">
                <?php echo $is_open_now ? esc_html__( 'Open Now', 'paradise-widgets-for-elementor' ) : esc_html__( 'Closed', 'paradise-widgets-for-elementor' ); ?>
            </div>
            <?php endif; ?>

            <table class="paradise-bh-table">
                <tbody>
                <?php foreach ( $days as $slug => $label ) :
                    $entry    = $hours[ $slug ];
                    $is_open  = $entry['open'] && ! empty( $entry['from'] ) && ! empty( $entry['to'] );
                    $from_str = $is_open ? $this->format_time( $entry['from'], $time_format ) : '';
                    $to_str   = $is_open ? $this->format_time( $entry['to'],   $time_format ) : '';
                ?>
                <tr
                    class="paradise-bh-row<?php echo $is_open ? '' : ' paradise-bh-row--closed'; ?>"
                    data-bh-day="<?php echo esc_attr( $slug ); ?>"
                >
                    <td class="paradise-bh-day"><?php echo esc_html( $label ); ?></td>
                    <td class="paradise-bh-hours">
                        <?php if ( $is_open ) : ?>
                            <?php echo esc_html( $from_str . ' – ' . $to_str ); ?>
                        <?php else : ?>
                            <?php echo $closed_label; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
