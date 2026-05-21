<?php
/**
 * Paradise SoundCloud Widget
 *
 * Embeds a SoundCloud single track, a Set (playlist), or a curated playlist
 * assembled from per-post fields or an in-widget list. A single SELECT at
 * the top — `source` — picks the mode:
 *
 *   single        Mode 1 + 2: one SoundCloud URL (track or /sets/...).
 *                 SoundCloud's player auto-detects which is which, so
 *                 both share the same iframe render. The URL control has
 *                 Elementor Dynamic Tags enabled, so ACF Pro (or any URL
 *                 tag provider) can bind it to a custom field on the
 *                 current post without this widget knowing about ACF.
 *
 *   acf_repeater  Mode 3: read a Repeater field on the current post (via
 *                 `get_field()` if ACF is active, else `get_post_meta()`
 *                 fallback) for a curated list of tracks. Field name +
 *                 four sub-field names (url, title, artist, description)
 *                 are configurable; defaults match a sensible schema.
 *
 *   manual_list   Mode 4: a list of tracks built directly in the widget
 *                 via an Elementor Repeater Control. No external dep —
 *                 works on any site without ACF. Same four sub-fields
 *                 (URL, title, artist, description) for schema parity
 *                 with Mode 3, so a user can move data between modes.
 *
 * Modes 3 + 4 share a render path: one iframe at the top, then a list of
 * `<button>`s underneath; clicking a track loads it into the iframe via
 * SoundCloud's Widget API JS (no fresh ~500 KB player per track).
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

        $this->add_control( 'source', [
            'label'       => esc_html__( 'Source', 'paradise-widgets-for-elementor' ),
            'type'        => \Elementor\Controls_Manager::SELECT,
            'default'     => 'single',
            'options'     => [
                'single'       => esc_html__( 'Single Track or Set URL', 'paradise-widgets-for-elementor' ),
                'acf_repeater' => esc_html__( 'ACF Repeater Playlist', 'paradise-widgets-for-elementor' ),
                'manual_list'  => esc_html__( 'Manual List Playlist', 'paradise-widgets-for-elementor' ),
            ],
            'description' => esc_html__( 'Single = one SoundCloud track or Set, auto-detected. ACF Repeater = read a list of tracks from a Repeater field on the current post. Manual List = build the track list directly in the widget.', 'paradise-widgets-for-elementor' ),
        ] );

        $this->add_control( 'url', [
            'label'         => esc_html__( 'Track or Playlist URL', 'paradise-widgets-for-elementor' ),
            'type'          => \Elementor\Controls_Manager::URL,
            'placeholder'   => 'https://soundcloud.com/artist/track-name',
            'show_external' => false,
            'default'       => [ 'url' => '', 'is_external' => false, 'nofollow' => false ],
            'dynamic'       => [ 'active' => true ],
            'description'   => esc_html__( 'Paste a SoundCloud track URL or a Set (playlist) URL. Both are auto-detected.', 'paradise-widgets-for-elementor' ),
            'condition'     => [ 'source' => 'single' ],
        ] );

        // ── ACF Repeater (Mode 3) — field-name controls ──────────────────────
        // Sub-field defaults follow a sensible Repeater schema. All four are
        // overridable per widget so users with existing field names don't have
        // to rename anything in ACF. Description is optional at render time —
        // empty rows just skip that line.

        $this->add_control( 'acf_field_name', [
            'label'       => esc_html__( 'Repeater Field Name', 'paradise-widgets-for-elementor' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'default'     => 'tracks',
            'description' => esc_html__( 'Name of the ACF Repeater field on the current post. Read via get_field() with a get_post_meta() fallback if ACF is inactive.', 'paradise-widgets-for-elementor' ),
            'condition'   => [ 'source' => 'acf_repeater' ],
        ] );

        $this->add_control( 'acf_sub_url', [
            'label'     => esc_html__( 'URL Sub-field', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::TEXT,
            'default'   => 'url',
            'condition' => [ 'source' => 'acf_repeater' ],
        ] );

        $this->add_control( 'acf_sub_title', [
            'label'     => esc_html__( 'Title Sub-field', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::TEXT,
            'default'   => 'title',
            'condition' => [ 'source' => 'acf_repeater' ],
        ] );

        $this->add_control( 'acf_sub_artist', [
            'label'     => esc_html__( 'Artist Sub-field', 'paradise-widgets-for-elementor' ),
            'type'      => \Elementor\Controls_Manager::TEXT,
            'default'   => 'artist',
            'condition' => [ 'source' => 'acf_repeater' ],
        ] );

        $this->add_control( 'acf_sub_description', [
            'label'       => esc_html__( 'Description Sub-field', 'paradise-widgets-for-elementor' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'default'     => 'description',
            'description' => esc_html__( 'Optional. Rows with no value here just skip the description line.', 'paradise-widgets-for-elementor' ),
            'condition'   => [ 'source' => 'acf_repeater' ],
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
        $source   = $settings['source'] ?? 'single';

        if ( 'acf_repeater' === $source ) {
            $this->render_acf_repeater_mode( $settings );
            return;
        }

        if ( 'single' !== $source ) {
            $this->render_editor_placeholder(
                esc_html__( 'No tracks to play yet. Add data for the selected source mode.', 'paradise-widgets-for-elementor' )
            );
            return;
        }

        $raw_url = trim( $settings['url']['url'] ?? '' );

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

    // ── Mode 3: ACF Repeater ──────────────────────────────────────────────────

    /**
     * Mode 3 entry point: pull the Repeater field off the current post and
     * hand the normalized track array to the shared playlist renderer.
     */
    private function render_acf_repeater_mode( array $settings ): void {
        $post_id = (int) get_the_ID();

        if ( $post_id <= 0 ) {
            $this->render_editor_placeholder(
                esc_html__( 'No current post — this mode reads tracks from a Repeater field on the current post. Place the widget inside a single-post template, or use Manual List mode instead.', 'paradise-widgets-for-elementor' )
            );
            return;
        }

        $tracks = $this->read_acf_repeater_tracks( $post_id, $settings );
        $this->render_playlist( $tracks, $settings );
    }

    /**
     * Read the configured Repeater field on $post_id and normalize each row
     * into our `{ url, title, artist, description }` shape. Rows with an
     * empty or invalid SoundCloud URL are skipped silently (no error UI for
     * malformed individual rows — the editor placeholder only fires when
     * *every* row is unusable).
     *
     * Loose coupling: prefers ACF's `get_field()` (which returns a proper
     * array of rows), falls back to `get_post_meta()` for the rare case
     * where the data exists but ACF is inactive. We do NOT walk numbered
     * postmeta rows (`tracks_0_url`, `tracks_0_title`, …) without ACF —
     * that's deep ACF-internals territory and not worth the complexity.
     */
    private function read_acf_repeater_tracks( int $post_id, array $settings ): array {
        $field_name = trim( $settings['acf_field_name'] ?? 'tracks' );
        if ( '' === $field_name ) {
            return [];
        }

        $rows = function_exists( 'get_field' )
            ? get_field( $field_name, $post_id )
            : get_post_meta( $post_id, $field_name, true );

        if ( ! is_array( $rows ) || empty( $rows ) ) {
            return [];
        }

        $sub_url    = trim( $settings['acf_sub_url']         ?? 'url' );
        $sub_title  = trim( $settings['acf_sub_title']       ?? 'title' );
        $sub_artist = trim( $settings['acf_sub_artist']      ?? 'artist' );
        $sub_desc   = trim( $settings['acf_sub_description'] ?? 'description' );

        $tracks = [];
        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }
            $url = trim( (string) ( $row[ $sub_url ] ?? '' ) );
            if ( '' === $url || ! $this->is_valid_soundcloud_url( $url ) ) {
                continue;
            }
            $tracks[] = [
                'url'         => $url,
                'title'       => trim( (string) ( $row[ $sub_title ]  ?? '' ) ),
                'artist'      => trim( (string) ( $row[ $sub_artist ] ?? '' ) ),
                'description' => trim( (string) ( $row[ $sub_desc ]   ?? '' ) ),
            ];
        }
        return $tracks;
    }

    // ── Shared playlist renderer (Mode 3 + Mode 4) ────────────────────────────

    /**
     * Render the playlist UI: one iframe playing the first track, plus a
     * `<ol>` of `<button>`s for each track. Buttons carry data-url so the
     * Widget API JS (commit 4 of Phase 2) can swap tracks into the same
     * player without re-loading SoundCloud's ~500 KB player JS per track.
     *
     * Until the JS lands, the list is static — buttons render but clicking
     * does nothing functional. The first track plays in the iframe; clicks
     * become interactive once `assets/js/soundcloud.js` is enqueued.
     */
    private function render_playlist( array $tracks, array $settings ): void {
        if ( empty( $tracks ) ) {
            $this->render_editor_placeholder(
                esc_html__( 'No valid SoundCloud tracks found. Check that the URLs point to soundcloud.com (not on.soundcloud.com).', 'paradise-widgets-for-elementor' )
            );
            return;
        }

        $is_visual = 'visual' === ( $settings['player_mode'] ?? 'visual' );
        $height    = $is_visual ? self::HEIGHT_VISUAL : self::HEIGHT_CLASSIC;
        $first_url = $tracks[0]['url'];
        $embed_url = $this->build_embed_url( $first_url, $settings, $is_visual );
        ?>
        <div class="paradise-soundcloud-wrap paradise-soundcloud-playlist" data-paradise-sc-list>
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
            <ol class="paradise-soundcloud-tracks">
                <?php foreach ( $tracks as $i => $track ) : ?>
                    <li class="paradise-soundcloud-track-item">
                        <button
                            type="button"
                            class="paradise-soundcloud-track<?php echo 0 === $i ? ' is-playing' : ''; ?>"
                            data-url="<?php echo esc_attr( $track['url'] ); ?>"
                            data-index="<?php echo (int) $i; ?>"
                            aria-current="<?php echo 0 === $i ? 'true' : 'false'; ?>"
                        >
                            <span class="paradise-soundcloud-track-num"><?php echo (int) ( $i + 1 ); ?></span>
                            <span class="paradise-soundcloud-track-meta">
                                <?php if ( '' !== $track['title'] ) : ?>
                                    <span class="paradise-soundcloud-track-title"><?php echo esc_html( $track['title'] ); ?></span>
                                <?php endif; ?>
                                <?php if ( '' !== $track['artist'] ) : ?>
                                    <span class="paradise-soundcloud-track-artist"><?php echo esc_html( $track['artist'] ); ?></span>
                                <?php endif; ?>
                                <?php if ( '' !== $track['description'] ) : ?>
                                    <span class="paradise-soundcloud-track-description"><?php echo esc_html( $track['description'] ); ?></span>
                                <?php endif; ?>
                            </span>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ol>
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
