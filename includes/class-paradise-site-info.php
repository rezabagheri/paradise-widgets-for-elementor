<?php
/**
 * Paradise Site Info
 *
 * Centralized data store for site-wide business information.
 * Supports multiple locations (branches), each with phones, emails,
 * a single address, a Google Map URL, and business hours.
 * Social links and business name are global (per brand, not per location).
 *
 * Data structure (paradise_site_info option):
 * {
 *   name:      string,
 *   socials:   [{platform, url}, ...],
 *   locations: [
 *     { label, phones:[{label,value}], emails:[{label,value}],
 *       address, map_url, hours:{day:{open,from,to}} },
 *     ...
 *   ]
 * }
 *
 * Usage:
 *   Paradise_Site_Info::get('phones', 0)       → phones[] for location 0
 *   Paradise_Site_Info::get_value('phones', 0) → first phone value, location 0
 *   Paradise_Site_Info::get_address(0)         → address string for location 0
 *   Paradise_Site_Info::get_map_url(0)         → map embed URL for location 0
 *   Paradise_Site_Info::get('socials')         → all social links (global)
 *   Paradise_Site_Info::get_hours(0)           → hours array for location 0
 *
 * Shortcode:
 *   [paradise_site_info type="phone" index="0" location="0"]
 *   [paradise_site_info type="phone" label="Main" location="Main Branch"]
 *   [paradise_site_info type="address" location="0"]
 *   [paradise_site_info type="address_map" location="1"]
 *   [paradise_site_info type="social_url" index="0"]
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Paradise_Site_Info {

    const OPTION_KEY = 'paradise_site_info';

    // ── Internal data loading ─────────────────────────────────────────────────

    /**
     * Load and normalize stored data. Auto-migrates the legacy flat structure.
     */
    private static function load(): array {
        $data = get_option( self::OPTION_KEY, [] );
        return self::maybe_migrate( (array) $data );
    }

    /**
     * If data uses the pre-2.3 flat structure (no 'locations' key), convert it
     * transparently. The saved option is NOT modified — migration runs on read only.
     */
    private static function maybe_migrate( array $data ): array {
        if ( isset( $data['locations'] ) ) {
            return $data;
        }

        $first = $data['addresses'][0] ?? [];

        return [
            'name'      => $data['name'] ?? '',
            'socials'   => $data['socials'] ?? [],
            'locations' => [
                [
                    'label'   => __( 'Main', 'paradise-widgets-for-elementor' ),
                    'phones'  => $data['phones'] ?? [],
                    'emails'  => $data['emails'] ?? [],
                    'address' => $first['value']   ?? '',
                    'map_url' => $first['map_url']  ?? '',
                    'hours'   => $data['hours']     ?? [],
                ],
            ],
        ];
    }

    // ── Data access ───────────────────────────────────────────────────────────

    /**
     * Get all saved items for a section type within a location.
     *
     * @param string $type      'phones' | 'emails' | 'socials'
     *                          ('socials' is global — location is ignored).
     * @param int    $location  Location index (0-based).
     * @return array
     */
    public static function get( string $type, int $location = 0 ): array {
        $data = self::load();

        if ( 'socials' === $type ) {
            return array_values( $data['socials'] ?? [] );
        }

        $locations = $data['locations'] ?? [];
        $loc       = $locations[ $location ] ?? $locations[0] ?? [];
        return array_values( $loc[ $type ] ?? [] );
    }

    /**
     * Get a specific field from a specific item in a section.
     *
     * @param string $type      Section type.
     * @param int    $index     Zero-based item index.
     * @param string $field     Field name: 'value' (default), 'label', 'url', 'platform'.
     * @param int    $location  Location index.
     * @return string
     */
    public static function get_value( string $type, int $index, string $field = 'value', int $location = 0 ): string {
        $items = self::get( $type, $location );
        return (string) ( $items[ $index ][ $field ] ?? '' );
    }

    /** Get the address string for a location. */
    public static function get_address( int $location = 0 ): string {
        $data = self::load();
        $loc  = $data['locations'][ $location ] ?? $data['locations'][0] ?? [];
        return (string) ( $loc['address'] ?? '' );
    }

    /** Get the Google Map embed URL for a location. */
    public static function get_map_url( int $location = 0 ): string {
        $data = self::load();
        $loc  = $data['locations'][ $location ] ?? $data['locations'][0] ?? [];
        return (string) ( $loc['map_url'] ?? '' );
    }

    /** Get the global business name. */
    public static function get_name(): string {
        return (string) ( self::load()['name'] ?? '' );
    }

    /** Get all locations as a sequential array. */
    public static function get_locations(): array {
        return array_values( self::load()['locations'] ?? [] );
    }

    /**
     * Return [index => label] options suitable for Elementor SELECT controls.
     */
    public static function get_location_select_options(): array {
        $locations = self::get_locations();

        if ( empty( $locations ) ) {
            return [ '' => esc_html__( '— No locations saved —', 'paradise-widgets-for-elementor' ) ];
        }

        $options = [];
        foreach ( $locations as $i => $loc ) {
            $label       = trim( $loc['label'] ?? '' );
            $options[ $i ] = $label !== ''
                ? $label
                /* translators: %d: 1-based location index, used as a placeholder label when the user hasn't named the location yet */
                : sprintf( esc_html__( 'Location %d', 'paradise-widgets-for-elementor' ), $i + 1 );
        }

        return $options;
    }

    /**
     * Return an [index => display_label] array for a section type within a location.
     */
    public static function get_select_options( string $type, int $location = 0 ): array {
        $items = self::get( $type, $location );

        if ( empty( $items ) ) {
            return [ '' => esc_html__( '— No items saved —', 'paradise-widgets-for-elementor' ) ];
        }

        $options = [];
        foreach ( $items as $i => $item ) {
            if ( 'socials' === $type ) {
                $platform_name = self::social_platforms()[ $item['platform'] ?? '' ] ?? ( $item['platform'] ?? '' );
                $options[ $i ] = $platform_name;
            } else {
                $label = trim( $item['label'] ?? '' );
                $val   = trim( $item['value'] ?? '' );
                $short = mb_strlen( $val ) > 24 ? mb_substr( $val, 0, 24 ) . '…' : $val;
                $options[ $i ] = $label !== '' ? $label . ' — ' . $short : $short;
            }
        }

        return $options;
    }

    // ── Business Hours ────────────────────────────────────────────────────────

    /** @return array<string, string>  slug → display label */
    public static function days(): array {
        return [
            'monday'    => esc_html__( 'Monday',    'paradise-widgets-for-elementor' ),
            'tuesday'   => esc_html__( 'Tuesday',   'paradise-widgets-for-elementor' ),
            'wednesday' => esc_html__( 'Wednesday', 'paradise-widgets-for-elementor' ),
            'thursday'  => esc_html__( 'Thursday',  'paradise-widgets-for-elementor' ),
            'friday'    => esc_html__( 'Friday',    'paradise-widgets-for-elementor' ),
            'saturday'  => esc_html__( 'Saturday',  'paradise-widgets-for-elementor' ),
            'sunday'    => esc_html__( 'Sunday',    'paradise-widgets-for-elementor' ),
        ];
    }

    /** Default hours: Mon–Fri 09:00–17:00, weekends closed. */
    public static function default_hours(): array {
        $weekdays = [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday' ];
        $defaults = [];
        foreach ( array_keys( self::days() ) as $day ) {
            $defaults[ $day ] = [
                'open' => in_array( $day, $weekdays, true ),
                'from' => '09:00',
                'to'   => '17:00',
            ];
        }
        return $defaults;
    }

    /**
     * Get saved hours for a location, merged with defaults so all 7 days are present.
     */
    public static function get_hours( int $location = 0 ): array {
        $data      = self::load();
        $locations = $data['locations'] ?? [];
        $loc       = $locations[ $location ] ?? $locations[0] ?? [];
        $saved     = $loc['hours'] ?? [];
        $result    = [];

        foreach ( self::default_hours() as $day => $defaults ) {
            $entry          = array_merge( $defaults, $saved[ $day ] ?? [] );
            $entry['open']  = (bool) $entry['open'];
            $result[ $day ] = $entry;
        }

        return $result;
    }

    /**
     * Check whether the business is currently open (uses the site timezone).
     */
    public static function is_open_now( int $location = 0 ): bool {
        try {
            $tz  = new DateTimeZone( wp_timezone_string() );
            $now = new DateTime( 'now', $tz );
        } catch ( Exception $e ) {
            $now = new DateTime( 'now' );
        }

        $day   = strtolower( $now->format( 'l' ) );
        $time  = $now->format( 'H:i' );
        $hours = self::get_hours( $location );
        $entry = $hours[ $day ] ?? null;

        if ( $entry && ! empty( $entry['open'] ) && ! empty( $entry['from'] ) && ! empty( $entry['to'] )
            && self::time_in_range( $time, $entry['from'], $entry['to'] ) ) {
            return true;
        }

        // Overnight span that began the previous day (e.g. yesterday 22:00 → today 02:00).
        $prev_day = strtolower( ( clone $now )->modify( '-1 day' )->format( 'l' ) );
        $prev     = $hours[ $prev_day ] ?? null;
        if ( $prev && ! empty( $prev['open'] ) && ! empty( $prev['from'] ) && ! empty( $prev['to'] )
            && $prev['from'] > $prev['to'] && $time <= $prev['to'] ) {
            return true;
        }

        return false;
    }

    /**
     * Whether H:i $time is within [$from, $to], handling overnight ranges
     * (from > to means the span crosses midnight, e.g. 22:00 → 02:00).
     */
    private static function time_in_range( string $time, string $from, string $to ): bool {
        return ( $from <= $to ) ? ( $time >= $from && $time <= $to )
                                : ( $time >= $from || $time <= $to );
    }

    /** @return array<string, string>  slug → display name */
    public static function social_platforms(): array {
        return [
            'instagram' => 'Instagram',
            'facebook'  => 'Facebook',
            'twitter'   => 'X (Twitter)',
            'linkedin'  => 'LinkedIn',
            'youtube'   => 'YouTube',
            'tiktok'    => 'TikTok',
            'pinterest' => 'Pinterest',
            'snapchat'  => 'Snapchat',
            'threads'   => 'Threads',
            'whatsapp'  => 'WhatsApp',
        ];
    }

    /**
     * Brand SVG for a social platform. Used by both the Social Links widget
     * (frontend rendering) and the Site Info admin (visual confirmation
     * next to the platform <select>). Currently the widget keeps its own
     * private copy of these strings; a future commit can DRY by having
     * the widget call this method.
     *
     * @return string  inline <svg> with fill: currentColor — empty string
     *                 for an unknown / not-set platform.
     */
    public static function social_icon_svg( string $platform ): string {
        $icons = [
            'instagram' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
            'facebook'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
            'twitter'   => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
            'linkedin'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
            'youtube'   => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
            'tiktok'    => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>',
            'pinterest' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0C5.373 0 0 5.372 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>',
            'snapchat'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.206.793c.99 0 4.347.276 5.93 3.821.529 1.193.403 3.219.299 4.847l-.003.06c-.012.18-.024.358-.029.53.16.068.329.092.499.092.3 0 .599-.09.832-.284.249-.195.496-.466.755-.683a.886.886 0 0 1 .58-.186c.19 0 .38.052.553.138.419.21.655.587.672 1.019a.804.804 0 0 1-.128.475c.184.176.37.353.557.529a.55.55 0 0 1 .163.48.558.558 0 0 1-.295.42c-.097.057-.185.098-.27.135.03.035.061.07.09.107.232.277.478.645.478 1.032 0 .478-.255.904-.716 1.195a2.076 2.076 0 0 1-.48.223c.03.072.05.15.05.233 0 .178-.069.34-.181.458.026.11.04.225.04.344v.03c0 .495-.168.953-.432 1.303-.254.337-.607.553-.978.553H18.6a3.36 3.36 0 0 1-.26-.021c-.097-.009-.195-.017-.295-.017-.168 0-.34.019-.503.052a1.832 1.832 0 0 0-.428.177c-.456.268-.983.673-1.655.673-.163 0-.327-.021-.489-.067a2.01 2.01 0 0 0-.591-.088c-.209 0-.417.03-.622.088l-.005.001c-.16.045-.321.066-.481.066-.673 0-1.203-.407-1.66-.676a1.835 1.835 0 0 0-.424-.175 2.028 2.028 0 0 0-.506-.052c-.101 0-.2.008-.297.017a3.306 3.306 0 0 1-.261.021h-.024c-.37 0-.723-.216-.977-.553-.264-.35-.432-.808-.432-1.303v-.03c0-.119.014-.234.04-.344a.678.678 0 0 1-.181-.458c0-.083.02-.16.05-.233a2.078 2.078 0 0 1-.48-.223c-.461-.291-.716-.717-.716-1.195 0-.387.246-.755.478-1.032.031-.037.061-.072.09-.107a2.177 2.177 0 0 1-.27-.135.558.558 0 0 1-.294-.42.55.55 0 0 1 .162-.48c.187-.176.374-.353.558-.529a.804.804 0 0 1-.128-.475c.017-.432.253-.809.672-1.019a1.138 1.138 0 0 1 .553-.138c.21 0 .414.065.58.186.259.217.506.488.755.683.234.194.533.284.832.284.17 0 .339-.024.499-.092-.005-.172-.017-.35-.029-.53l-.003-.06c-.104-1.628-.23-3.654.299-4.847C7.86 1.069 11.216.793 12.206.793z"/></svg>',
            'threads'   => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.186 24h-.007c-3.581-.024-6.334-1.205-8.184-3.509C2.35 18.44 1.5 15.586 1.472 12.01v-.017c.03-3.579.879-6.43 2.525-8.482C5.845 1.205 8.6.024 12.18 0h.014c2.746.02 5.043.725 6.826 2.098 1.677 1.29 2.858 3.13 3.509 5.467l-2.04.569c-1.104-3.96-3.898-5.984-8.304-6.015-2.91.022-5.11.936-6.54 2.717C4.307 6.504 3.616 8.914 3.589 12c.027 3.086.718 5.496 2.057 7.164 1.43 1.783 3.631 2.698 6.54 2.717 2.623-.02 4.358-.631 5.8-2.045 1.647-1.613 1.618-3.593 1.09-4.798-.31-.71-.873-1.3-1.634-1.75-.192 1.352-.622 2.446-1.284 3.272-.886 1.102-2.14 1.704-3.73 1.79-1.202.065-2.361-.218-3.259-.801-1.063-.689-1.685-1.74-1.752-2.964-.065-1.19.408-2.285 1.33-3.082.88-.76 2.119-1.207 3.583-1.291a13.853 13.853 0 0 1 3.02.142c-.126-.742-.375-1.332-.75-1.757-.513-.586-1.308-.883-2.359-.89h-.029c-.844 0-1.992.232-2.721 1.32L7.734 7.847c.98-1.454 2.568-2.256 4.478-2.256h.044c3.194.02 5.097 1.975 5.287 5.388.108.046.217.093.321.142 1.49.7 2.58 1.761 3.154 3.07.797 1.82.871 4.79-1.548 7.158C17.506 23.267 15.199 24 12.186 24zm-.5-9.528c-.404 0-.813.049-1.212.148-1.31.327-2.032 1.098-1.97 2.099.084 1.37 1.487 1.838 2.762 1.768 1.26-.07 2.182-.707 2.692-1.845.274-.61.433-1.413.476-2.386a11.26 11.26 0 0 0-2.748.216z"/></svg>',
            'whatsapp'  => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>',
        ];

        return $icons[ $platform ] ?? '';
    }

    // ── Export ────────────────────────────────────────────────────────────────

    /**
     * Return a fully-normalized snapshot of all Site Info data for export.
     * Hours are merged with defaults so all 7 days are present in every location.
     */
    public static function export(): array {
        $data      = self::load();
        $locations = [];
        foreach ( $data['locations'] ?? [] as $i => $loc ) {
            $loc['hours'] = self::get_hours( $i );
            $locations[]  = $loc;
        }
        return [
            'name'      => $data['name']    ?? '',
            'socials'   => $data['socials'] ?? [],
            'locations' => $locations,
        ];
    }

    // ── Persistence ───────────────────────────────────────────────────────────

    public static function save( array $raw ): void {
        $data = [
            'name'      => sanitize_text_field( $raw['name'] ?? '' ),
            'socials'   => [],
            'locations' => [],
        ];

        foreach ( $raw['socials'] ?? [] as $item ) {
            $url = esc_url_raw( $item['url'] ?? '' );
            if ( $url === '' ) continue;
            $data['socials'][] = [
                'platform' => sanitize_key( $item['platform'] ?? '' ),
                'url'      => $url,
            ];
        }

        foreach ( $raw['locations'] ?? [] as $loc_raw ) {
            $loc = [
                'label'   => sanitize_text_field( $loc_raw['label']   ?? '' ),
                'phones'  => [],
                'emails'  => [],
                'address' => sanitize_text_field( $loc_raw['address'] ?? '' ),
                'map_url' => esc_url_raw( $loc_raw['map_url']          ?? '' ),
                'hours'   => [],
            ];

            foreach ( $loc_raw['phones'] ?? [] as $item ) {
                $value = sanitize_text_field( $item['value'] ?? '' );
                if ( $value === '' ) continue;
                $loc['phones'][] = [
                    'label' => sanitize_text_field( $item['label'] ?? '' ),
                    'value' => $value,
                ];
            }

            foreach ( $loc_raw['emails'] ?? [] as $item ) {
                $value = sanitize_email( $item['value'] ?? '' );
                if ( $value === '' ) continue;
                $loc['emails'][] = [
                    'label' => sanitize_text_field( $item['label'] ?? '' ),
                    'value' => $value,
                ];
            }

            foreach ( array_keys( self::days() ) as $day ) {
                $entry = $loc_raw['hours'][ $day ] ?? [];
                $loc['hours'][ $day ] = [
                    'open' => ! empty( $entry['open'] ),
                    'from' => sanitize_text_field( $entry['from'] ?? '' ),
                    'to'   => sanitize_text_field( $entry['to']   ?? '' ),
                ];
            }

            $data['locations'][] = $loc;
        }

        // Always keep at least one location
        if ( empty( $data['locations'] ) ) {
            $data['locations'][] = [
                'label' => __( 'Main', 'paradise-widgets-for-elementor' ), 'phones' => [], 'emails' => [],
                'address' => '', 'map_url' => '', 'hours' => [],
            ];
        }

        update_option( self::OPTION_KEY, $data );
    }

    // ── Shortcode ─────────────────────────────────────────────────────────────

    public static function register_shortcode(): void {
        add_shortcode( 'paradise_site_info', [ __CLASS__, 'shortcode_handler' ] );
    }

    private static function resolve_index( string $type, array $atts, int $location = 0 ): int {
        $label_attr = trim( $atts['label'] ?? '' );

        if ( $label_attr !== '' ) {
            foreach ( self::get( $type, $location ) as $i => $item ) {
                if ( strcasecmp( $item['label'] ?? '', $label_attr ) === 0 ) {
                    return $i;
                }
            }
        }

        return max( 0, (int) ( $atts['index'] ?? 0 ) );
    }

    private static function resolve_location( array $atts ): int {
        $loc = trim( $atts['location'] ?? '' );

        if ( $loc === '' ) return 0;
        if ( ctype_digit( $loc ) ) return (int) $loc;

        foreach ( self::get_locations() as $i => $location ) {
            if ( strcasecmp( $location['label'] ?? '', $loc ) === 0 ) {
                return $i;
            }
        }

        return 0;
    }

    public static function shortcode_handler( array $atts ): string {
        $atts = shortcode_atts( [
            'type'     => 'phone',
            'index'    => 0,
            'label'    => __( '', 'paradise-widgets-for-elementor' ),
            'location' => '',
        ], $atts, 'paradise_site_info' );

        $location = self::resolve_location( $atts );

        switch ( $atts['type'] ) {
            case 'phone':
                return esc_html( self::get_value( 'phones', self::resolve_index( 'phones', $atts, $location ), 'value', $location ) );

            case 'email':
                return esc_html( self::get_value( 'emails', self::resolve_index( 'emails', $atts, $location ), 'value', $location ) );

            case 'address':
                return esc_html( self::get_address( $location ) );

            case 'address_map':
                return esc_url( self::get_map_url( $location ) );

            case 'social_url':
                return esc_url( self::get_value( 'socials', self::resolve_index( 'socials', $atts ), 'url' ) );

            default:
                return '';
        }
    }
}
