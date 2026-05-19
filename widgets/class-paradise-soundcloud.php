<?php
/**
 * Paradise SoundCloud Widget
 *
 * Embeds a SoundCloud track or Set (playlist) via SoundCloud's official
 * player iframe. SoundCloud's player auto-detects whether the URL points
 * to a single track or a Set and renders the appropriate UI — track URLs
 * get a single-track player, /sets/ URLs get a playlist player with track
 * list and continuous playback. So Mode 1 (single) and Mode 2 (playlist
 * URL) share the same render path; only the URL differs.
 *
 * The URL control has Elementor Dynamic Tags enabled. Users with ACF Pro
 * (or any provider that registers an Elementor URL/text dynamic tag) can
 * bind the field to a custom field on the current post — no ACF awareness
 * inside this widget. That keeps the coupling loose and forward-compatible
 * with whatever per-post field system the user adopts later.
 *
 * Phase 2 (separate session) will add a third "Custom Playlist" mode that
 * accepts an array of track URLs (from an ACF Repeater, or a manual list)
 * and renders a unified player using SoundCloud's Widget API JS. That mode
 * needs JS, so the registry entry will gain 'js' => true at that point.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Paradise_Soundcloud_Widget extends Paradise_Widget_Base {

    public function get_name(): string    { return 'paradise_soundcloud'; }
    public function get_title(): string   { return esc_html__( 'SoundCloud', 'paradise-widgets-for-elementor' ); }
    // Brand icon from Font Awesome 5 (bundled with Elementor's editor). All
    // other Paradise widgets use eicon-*, but SoundCloud is the first
    // brand-specific widget — the recognisable cloud logo is a much faster
    // visual cue in the widget panel than a generic 'eicon-headphones'.
    public function get_icon(): string    { return 'fab fa-soundcloud'; }
    public function get_keywords(): array { return [ 'soundcloud', 'audio', 'music', 'podcast', 'embed', 'player' ]; }

    // SoundCloud's brand orange — also the player's documented default.
    private const DEFAULT_COLOR = '#ff5500';

    // Heights match SoundCloud's own embed presets. Visual = square artwork
    // variant; Classic = compact one-line player.
    private const HEIGHT_VISUAL  = 450;
    private const HEIGHT_CLASSIC = 160;

    // ── Controls ──────────────────────────────────────────────────────────────

    protected function register_controls(): void {

        // ── Content ───────────────────────────────────────────────────────────

        $this->start_controls_section( 'section_content', [
            'label' => esc_html__( 'SoundCloud', 'paradise-widgets-for-elementor' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'url', [
            'label'         => esc_html__( 'Track or Playlist URL', 'paradise-widgets-for-elementor' ),
            'type'          => \Elementor\Controls_Manager::URL,
            'placeholder'   => 'https://soundcloud.com/artist/track-name',
            'show_external' => false,
            'default'       => [ 'url' => '', 'is_external' => false, 'nofollow' => false ],
            'dynamic'       => [ 'active' => true ],
            'description'   => esc_html__( 'Paste a SoundCloud track URL or a Set (playlist) URL. Both are auto-detected.', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'player_mode', [
            'label'   => esc_html__( 'Player Style', 'paradise-widgets-for-elementor' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'visual',
            'options' => [
                'visual'  => esc_html__( 'Visual (with artwork)', 'paradise-widgets-for-elementor' ),
                'classic' => esc_html__( 'Classic (mini, compact)', 'paradise-widgets-for-elementor' ),
            ],
        ] );

        $this->add_control( 'color', [
            'label'       => esc_html__( 'Accent Color', 'paradise-widgets-for-elementor' ),
            'type'        => \Elementor\Controls_Manager::COLOR,
            'default'     => self::DEFAULT_COLOR,
            'description' => esc_html__( 'Colour of play button, progress bar, and links inside the player.', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'auto_play', [
            'label'       => esc_html__( 'Auto-play', 'paradise-widgets-for-elementor' ),
            'type'        => \Elementor\Controls_Manager::SWITCHER,
            'default'     => '',
            'description' => esc_html__( 'Browsers block auto-play with sound on most pages. Use sparingly.', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'show_comments', [
            'label'   => esc_html__( 'Show Comments', 'paradise-widgets-for-elementor' ),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => '',
        ] );

        $this->add_control( 'show_related', [
            'label'   => esc_html__( 'Show Related Tracks', 'paradise-widgets-for-elementor' ),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => '',
        ] );

        $this->add_control( 'show_user', [
            'label'   => esc_html__( 'Show Uploader Name', 'paradise-widgets-for-elementor' ),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
        ] );

        $this->end_controls_section();

        // ── Style ─────────────────────────────────────────────────────────────

        $this->start_controls_section( 'section_style', [
            'label' => esc_html__( 'Style', 'paradise-widgets-for-elementor' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_control( 'max_width', [
            'label'      => esc_html__( 'Max Width', 'paradise-widgets-for-elementor' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%' ],
            'range'      => [
                'px' => [ 'min' => 200, 'max' => 1200 ],
                '%'  => [ 'min' => 10,  'max' => 100  ],
            ],
            'default'    => [ 'unit' => '%', 'size' => 100 ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-soundcloud-wrap' => 'max-width: {{SIZE}}{{UNIT}}; margin-inline: auto;',
            ],
        ] );

        $this->add_control( 'border_radius', [
            'label'      => esc_html__( 'Border Radius', 'paradise-widgets-for-elementor' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 32 ] ],
            'default'    => [ 'unit' => 'px', 'size' => 4 ],
            'selectors'  => [
                '{{WRAPPER}} .paradise-soundcloud-frame' => 'border-radius: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->end_controls_section();
    }

    // ── Render ────────────────────────────────────────────────────────────────

    protected function render(): void {
        $settings = $this->get_settings_for_display();
        $raw_url  = trim( $settings['url']['url'] ?? '' );

        if ( $raw_url === '' ) {
            $this->render_editor_placeholder(
                esc_html__( 'Paste a SoundCloud track or playlist URL in the widget settings.', 'paradise-widgets-for-elementor' )
            );
            return;
        }

        if ( ! $this->is_valid_soundcloud_url( $raw_url ) ) {
            $this->render_editor_placeholder(
                esc_html__( 'That URL doesn’t look like a SoundCloud link. Expected something like soundcloud.com/artist/track.', 'paradise-widgets-for-elementor' )
            );
            return;
        }

        $is_visual = 'visual' === ( $settings['player_mode'] ?? 'visual' );
        $height    = $is_visual ? self::HEIGHT_VISUAL : self::HEIGHT_CLASSIC;
        $embed_url = $this->build_embed_url( $raw_url, $settings, $is_visual );
        ?>
        <div class="paradise-soundcloud-wrap">
            <iframe
                class="paradise-soundcloud-frame"
                src="<?php echo esc_url( $embed_url ); ?>"
                width="100%"
                height="<?php echo (int) $height; ?>"
                scrolling="no"
                frameborder="no"
                allow="autoplay"
                loading="lazy"
                title="<?php echo esc_attr__( 'SoundCloud audio player', 'paradise-widgets-for-elementor' ); ?>"
            ></iframe>
        </div>
        <?php
    }

    /**
     * Validate that $url points to a SoundCloud-hosted resource we can embed.
     *
     * Strict allowlist: only canonical hosts are accepted.
     *
     * Deliberately excluded:
     *   on.soundcloud.com  — short link, the iframe can't follow redirects
     *   w.soundcloud.com   — already the embed URL, would double-embed
     *   api.soundcloud.com — API endpoint, not for embedding
     *
     * FILTER_VALIDATE_URL also rejects scheme-less input like
     * "soundcloud.com/track" — a feature for us, since the iframe needs a
     * proper https URL anyway.
     */
    private function is_valid_soundcloud_url( string $url ): bool {
        $url = trim( $url );

        if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            return false;
        }

        $host = strtolower( parse_url( $url, PHP_URL_HOST ) ?? '' );
        return in_array( $host, [ 'soundcloud.com', 'm.soundcloud.com', 'www.soundcloud.com' ], true );
    }


    /**
     * Build the w.soundcloud.com/player iframe URL with all the widget's
     * settings applied as query parameters.
     *
     * Reference: https://developers.soundcloud.com/docs/api/html5-widget
     * The hex color is passed WITHOUT the '#' (http_build_query will URL-
     * encode '#' to '%23' which is what the player expects).
     */
    private function build_embed_url( string $url, array $settings, bool $is_visual ): string {
        $params = [
            'url'             => $url,
            'visual'          => $is_visual ? 'true' : 'false',
            'color'           => $settings['color'] ?? self::DEFAULT_COLOR,
            'auto_play'       => ( 'yes' === ( $settings['auto_play']     ?? '' ) )    ? 'true' : 'false',
            'show_comments'   => ( 'yes' === ( $settings['show_comments'] ?? '' ) )    ? 'true' : 'false',
            'show_user'       => ( 'yes' === ( $settings['show_user']     ?? 'yes' ) ) ? 'true' : 'false',
            'hide_related'    => ( 'yes' === ( $settings['show_related']  ?? '' ) )    ? 'false' : 'true',
            'show_reposts'    => 'false',
            'show_teaser'     => 'true',
            'sharing'         => 'true',
            'liking'          => 'true',
            'download'        => 'false',
            'buying'          => 'false',
        ];

        return 'https://w.soundcloud.com/player/?' . http_build_query( $params );
    }
}
