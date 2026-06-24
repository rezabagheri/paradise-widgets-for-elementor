<?php
/**
 * Paradise Google Map Widget
 *
 * Embeds a Google Map via iframe. Source can be a Site Info address
 * (map_url field) or a manually entered URL.
 *
 * URL normalization: regular Google Maps share URLs are converted to
 * embeddable form by appending output=embed.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Paradise_Google_Map_Widget extends Paradise_Widget_Base {

    public function get_name(): string    { return 'paradise_google_map'; }
    public function get_title(): string   { return esc_html__( 'Google Map', 'paradise-widgets-for-elementor' ); }
    public function get_icon(): string    { return 'eicon-google-maps'; }
    public function get_keywords(): array { return [ 'map', 'google', 'location', 'address', 'embed' ]; }

    // get_categories() and get_style_depends() come from the base — defaults
    // match (paradise category, 'paradise-google-map' style handle).
    // No JS file: the iframe is fully declarative.

    // ── Controls ──────────────────────────────────────────────────────────────

    protected function register_controls(): void {

        // ── Mode ──────────────────────────────────────────────────────────────

        $this->start_controls_section( 'section_mode', [
            'label' => esc_html__( 'Map Mode', 'paradise-widgets-for-elementor' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'mode', [
            'label'   => esc_html__( 'Mode', 'paradise-widgets-for-elementor' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'place',
            'options' => [
                'place'      => esc_html__( 'Place', 'paradise-widgets-for-elementor' ),
                'directions' => esc_html__( 'Directions', 'paradise-widgets-for-elementor' ),
            ],
        ] );

        $this->add_control( 'map_type', [
            'label'   => esc_html__( 'Map Type', 'paradise-widgets-for-elementor' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'm',
            'options' => [
                'm' => esc_html__( 'Map', 'paradise-widgets-for-elementor' ),
                'k' => esc_html__( 'Satellite', 'paradise-widgets-for-elementor' ),
                'h' => esc_html__( 'Hybrid (Satellite + Labels)', 'paradise-widgets-for-elementor' ),
                'p' => esc_html__( 'Terrain', 'paradise-widgets-for-elementor' ),
            ],
        ] );

        $this->end_controls_section();

        // ── Place ─────────────────────────────────────────────────────────────

        $this->start_controls_section( 'section_source', [
            'label'     => esc_html__( 'Place', 'paradise-widgets-for-elementor' ),
            'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
            'condition' => [ 'mode' => 'place' ],
        ] );

        $this->add_control( 'source', [
            'label'   => esc_html__( 'Source', 'paradise-widgets-for-elementor' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'manual',
            'options' => [
                'site_info' => esc_html__( 'Site Info Address', 'paradise-widgets-for-elementor' ),
                'manual'    => esc_html__( 'Manual URL', 'paradise-widgets-for-elementor' ),
            ],
        ] );

        $this->add_control( 'location_index', [
            'label'     => esc_html__( 'Location', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::SELECT,
            'options'   => Paradise_Site_Info::get_location_select_options(),
            'default'   => '0',
            'condition' => [ 'source' => 'site_info' ],
        ] );

        $this->add_control( 'manual_url', [
            'label'       => esc_html__( 'Map URL', 'paradise-widgets-for-elementor' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => __( 'https://www.google.com/maps/embed?pb=...', 'paradise-widgets-for-elementor' ),
            'description' => esc_html__( 'Paste any Google Maps URL (share link, place, or directions). For best results: Share → Embed a map → copy the src from the iframe code.', 'paradise-widgets-for-elementor' ),
            'label_block' => true,
            'condition'   => [ 'source' => 'manual' ],
            'dynamic'     => [ 'active' => true ],
        ] );

        $this->end_controls_section();

        // ── Directions ────────────────────────────────────────────────────────

        $this->start_controls_section( 'section_directions', [
            'label'     => esc_html__( 'Directions', 'paradise-widgets-for-elementor' ),
            'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
            'condition' => [ 'mode' => 'directions' ],
        ] );

        $this->add_control( 'dir_origin', [
            'label'       => esc_html__( 'From (Origin)', 'paradise-widgets-for-elementor' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => esc_html__( 'e.g. Los Angeles, CA or leave blank for "My Location"', 'paradise-widgets-for-elementor' ),
            'label_block' => true,
            'dynamic'     => [ 'active' => true ],
        ] );

        $this->add_control( 'dir_dest_source', [
            'label'   => esc_html__( 'Destination Source', 'paradise-widgets-for-elementor' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'site_info',
            'options' => [
                'site_info' => esc_html__( 'Site Info Address', 'paradise-widgets-for-elementor' ),
                'manual'    => esc_html__( 'Manual', 'paradise-widgets-for-elementor' ),
            ],
        ] );

        $this->add_control( 'dir_dest_location', [
            'label'     => esc_html__( 'Location', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::SELECT,
            'options'   => Paradise_Site_Info::get_location_select_options(),
            'default'   => '0',
            'condition' => [ 'dir_dest_source' => 'site_info' ],
        ] );

        $this->add_control( 'dir_dest_manual', [
            'label'       => esc_html__( 'Destination Address', 'paradise-widgets-for-elementor' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'placeholder' => __( '17354 Tribune St, Granada Hills, CA 91344', 'paradise-widgets-for-elementor' ),
            'label_block' => true,
            'condition'   => [ 'dir_dest_source' => 'manual' ],
            'dynamic'     => [ 'active' => true ],
        ] );

        $this->add_control( 'travel_mode', [
            'label'   => esc_html__( 'Travel Mode', 'paradise-widgets-for-elementor' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'd',
            'options' => [
                'd' => esc_html__( 'Driving', 'paradise-widgets-for-elementor' ),
                'w' => esc_html__( 'Walking', 'paradise-widgets-for-elementor' ),
                'b' => esc_html__( 'Bicycling', 'paradise-widgets-for-elementor' ),
                'r' => esc_html__( 'Transit', 'paradise-widgets-for-elementor' ),
            ],
        ] );

        $this->end_controls_section();

        // ── Settings ──────────────────────────────────────────────────────────

        $this->start_controls_section( 'section_settings', [
            'label' => esc_html__( 'Settings', 'paradise-widgets-for-elementor' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_responsive_control( 'height', [
            'label'      => esc_html__( 'Height', 'paradise-widgets-for-elementor' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px', 'vh' ],
            'range'      => [
                'px' => [ 'min' => 100, 'max' => 900, 'step' => 10 ],
                'vh' => [ 'min' => 10,  'max' => 100 ],
            ],
            'default'    => [ 'unit' => 'px', 'size' => 400 ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-gmap-frame' => 'height: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->add_control( 'zoom', [
            'label'       => esc_html__( 'Zoom Level', 'paradise-widgets-for-elementor' ),
            'type'        => \Elementor\Controls_Manager::SLIDER,
            'range'       => [ 'px' => [ 'min' => 1, 'max' => 20 ] ],
            'default'     => [ 'size' => 15 ],
            'description' => esc_html__( '1 = world, 10 = city, 15 = streets, 20 = building.', 'paradise-widgets-for-elementor' ),
            'condition'   => [ 'mode' => 'place' ],
        ] );

        $this->add_control( 'allow_fullscreen', [
            'label'        => esc_html__( 'Allow Fullscreen', 'paradise-widgets-for-elementor' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
        ] );

        $this->end_controls_section();

        // ── Style ─────────────────────────────────────────────────────────────

        $this->start_controls_section( 'section_style', [
            'label' => esc_html__( 'Map', 'paradise-widgets-for-elementor' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_responsive_control( 'border_radius', [
            'label'      => esc_html__( 'Border Radius', 'paradise-widgets-for-elementor' ),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%' ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-gmap-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
            ],
        ] );

        $this->add_group_control( \Elementor\Group_Control_Border::get_type(), [
            'name'     => 'map_border',
            'selector' => '{{WRAPPER}} .paradise-gmap-wrap',
        ] );

        $this->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), [
            'name'     => 'map_shadow',
            'selector' => '{{WRAPPER}} .paradise-gmap-wrap',
        ] );

        $this->end_controls_section();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Normalize any Google Maps URL into an embeddable iframe src.
     *
     * Google only allows certain URL formats in iframes. This function converts
     * known patterns into the reliable maps.google.com/maps?q=...&output=embed
     * format, which correctly geocodes addresses and respects the z= zoom param.
     *
     * 1. /maps/embed?pb=...  → already a full embed blob, return as-is.
     * 2. output=embed        → already converted, return as-is.
     * 3. /maps/dir//DEST/@   → extract destination, build q= embed URL.
     * 4. /maps/place/NAME/@  → extract place name, build q= embed URL.
     * 5. ?q=QUERY            → use q directly, build q= embed URL.
     * 6. Fallback            → append output=embed.
     *
     * Note: /maps/embed?q= (without pb) does NOT support zoom or reliable
     * geocoding — always convert to maps.google.com/maps?q=...&output=embed.
     */
    private function normalize_embed_url( string $url ): string {
        if ( empty( $url ) ) return '';

        // Full embed blob (/maps/embed?pb=...) — use as-is.
        if ( strpos( $url, '/maps/embed?pb=' ) !== false ) return $url;
        if ( strpos( $url, 'output=embed' ) !== false ) return $url;

        $is_google = (
            strpos( $url, 'google.com/maps' ) !== false ||
            strpos( $url, 'maps.google.com' ) !== false
        );

        if ( ! $is_google ) return $url;

        // Directions URL: /maps/dir//DESTINATION/@ — extract the destination.
        if ( preg_match( '#/maps/dir//([^/@]+)/@#', $url, $m ) ) {
            $q = rawurldecode( str_replace( '+', ' ', $m[1] ) );
            return 'https://maps.google.com/maps?q=' . rawurlencode( $q ) . '&output=embed';
        }

        // Place URL: /maps/place/NAME/@ — extract the place name.
        if ( preg_match( '#/maps/place/([^/@]+)/@#', $url, $m ) ) {
            $q = rawurldecode( str_replace( '+', ' ', $m[1] ) );
            return 'https://maps.google.com/maps?q=' . rawurlencode( $q ) . '&output=embed';
        }

        // Already a /maps/embed?q= URL (no pb) — convert to maps.google.com format
        // so that zoom and geocoding work correctly.
        if ( strpos( $url, '/maps/embed' ) !== false ) {
            parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $params );
            if ( ! empty( $params['q'] ) ) {
                return 'https://maps.google.com/maps?q=' . rawurlencode( $params['q'] ) . '&output=embed';
            }
        }

        // URL has a ?q= parameter — use it directly.
        parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $params );
        if ( ! empty( $params['q'] ) ) {
            return 'https://maps.google.com/maps?q=' . rawurlencode( $params['q'] ) . '&output=embed';
        }

        // Fallback: append output=embed.
        $sep = ( strpos( $url, '?' ) !== false ) ? '&' : '?';
        return $url . $sep . 'output=embed';
    }

    // ── Render ────────────────────────────────────────────────────────────────

    protected function render(): void {
        $settings  = $this->get_settings_for_display();
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
        $mode      = $settings['mode'] ?? 'place';
        $map_type  = sanitize_key( $settings['map_type'] ?? 'm' );
        $from_si   = false;

        if ( 'directions' === $mode ) {
            $embed_url = $this->build_directions_url( $settings );
        } else {
            if ( 'site_info' === $settings['source'] ) {
                $raw_url = Paradise_Site_Info::get_map_url( (int) ( $settings['location_index'] ?? 0 ) );
                $from_si = true;
            } else {
                $raw_url = sanitize_text_field( $settings['manual_url'] ?? '' );
            }
            $embed_url = $this->normalize_embed_url( $raw_url );

            // Append zoom for place mode only. pb= blobs already encode zoom internally.
            $zoom     = (int) ( $settings['zoom']['size'] ?? 15 );
            $has_zoom = strpos( $embed_url, 'z=' ) !== false || strpos( $embed_url, '/maps/embed?pb=' ) !== false;
            if ( $zoom > 0 && ! $has_zoom ) {
                $embed_url .= '&z=' . $zoom;
            }
        }

        // Append map type (skip for pb= blobs — type is encoded inside).
        if ( $map_type && 'm' !== $map_type && strpos( $embed_url, '/maps/embed?pb=' ) === false ) {
            $embed_url .= '&t=' . $map_type;
        }

        if ( empty( $embed_url ) ) {
            if ( $is_editor ) {
                $msg = ( 'directions' === $mode )
                    ? esc_html__( 'Enter a destination address in the Directions settings.', 'paradise-widgets-for-elementor' )
                    : ( $from_si
                        ? esc_html__( 'The selected address has no Map URL. Go to Paradise → Site Info and add a Google Maps link.', 'paradise-widgets-for-elementor' )
                        : esc_html__( 'Enter a Google Maps URL in the Place settings.', 'paradise-widgets-for-elementor' ) );
                echo '<div class="paradise-gmap-placeholder">' . esc_html( $msg ) . '</div>';
            }
            return;
        }

        $fullscreen_attr = 'yes' === $settings['allow_fullscreen'] ? 'allowfullscreen' : '';
        ?>
        <div class="paradise-gmap-wrap">
            <?php if ( $from_si && $is_editor ) : ?>
            <div class="paradise-gmap-si-badge"><?php esc_html_e( '⚡ Live from Site Info', 'paradise-widgets-for-elementor' ); ?></div>
            <?php endif; ?>
            <iframe
                class="paradise-gmap-frame"
                src="<?php echo esc_url( $embed_url ); ?>"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                <?php echo $fullscreen_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            ></iframe>
        </div>
        <?php
    }

    /**
     * Build a Google Maps directions embed URL.
     * saddr = origin, daddr = destination, dirflg = travel mode.
     * If origin is empty, Google Maps shows the route from the user's location.
     */
    private function build_directions_url( array $settings ): string {
        if ( 'site_info' === $settings['dir_dest_source'] ) {
            $dest = Paradise_Site_Info::get_address( (int) ( $settings['dir_dest_location'] ?? 0 ) );
        } else {
            $dest = sanitize_text_field( $settings['dir_dest_manual'] ?? '' );
        }

        if ( empty( $dest ) ) return '';

        $origin = sanitize_text_field( $settings['dir_origin'] ?? '' );
        $mode   = sanitize_key( $settings['travel_mode'] ?? 'd' );

        $args = [
            'daddr'  => $dest,
            'dirflg' => $mode,
            'output' => 'embed',
        ];

        if ( $origin !== '' ) {
            $args['saddr'] = $origin;
        }

        return 'https://maps.google.com/maps?' . http_build_query( $args );
    }
}
