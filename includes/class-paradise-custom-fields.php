<?php
/**
 * Paradise Custom Fields
 *
 * User-defined static fields organized into groups. Fields are addressed
 * by a globally-unique key (groups exist only for admin organization).
 *
 * This is intentionally NOT a replacement for ACF — fields here are
 * site-wide constants (copyright text, footer logo, etc.) not attached
 * to post types or taxonomies.
 *
 * Data structure (paradise_custom_fields option):
 * {
 *   groups: [
 *     {
 *       slug:   string,                  // 'footer'
 *       label:  string,                  // 'Footer'
 *       fields: [
 *         { key: 'copyright', label: 'Copyright', type: 'text',     value: '© 2026 …' },
 *         { key: 'logo',      label: 'Logo',      type: 'image',    value: 123        },
 *         ...
 *       ],
 *     },
 *     ...
 *   ]
 * }
 *
 * Usage:
 *   Paradise_Custom_Fields::get_value('copyright')              → raw stored value
 *   Paradise_Custom_Fields::render('copyright')                 → escaped/output string
 *   Paradise_Custom_Fields::render('logo', 'html')              → <img …>
 *   [paradise_field key="copyright"]
 *   [paradise_field key="logo" output="html"]
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Paradise_Custom_Fields {

    const OPTION_KEY = 'paradise_custom_fields';

    // ── Type registry ─────────────────────────────────────────────────────────

    /**
     * Available field types.
     *
     * Each entry MUST provide:
     *   label       — string, shown in the admin type <select>
     *   sanitize    — callable(mixed $value): mixed
     *                 Pre-save sanitizer. Receives the raw POST value
     *                 (already unslashed), returns the value as it
     *                 should be persisted in the option.
     *   render      — callable(mixed $value, string $output): string
     *                 Output transformer. Receives the stored value
     *                 and the user-requested output mode (from the
     *                 shortcode `output` attribute), returns the final
     *                 string to emit. Must escape for HTML context.
     *   el_category — string (one of: 'text', 'url', 'image')
     *                 Elementor dynamic-tag category this type maps to.
     *                 Used by the dynamic tags shipped in a follow-up
     *                 release to decide which SELECT shows which fields.
     *
     * Filter `paradise_custom_field_types` lets sites add their own
     * types (color, number, etc.) without modifying this file.
     *
     * @return array<string, array{label:string, sanitize:callable, render:callable, el_category:string}>
     */
    public static function get_types(): array {
        $types = [
            // ── text ──────────────────────────────────────────────────────────
            // Simple one-line string. Stored as plain text, output as
            // HTML-escaped text. Output mode is ignored (text has no
            // meaningful variants).
            'text' => [
                'label'       => __( 'Text', 'paradise-widgets-for-elementor' ),
                'sanitize'    => static fn( $v ) => sanitize_text_field( (string) $v ),
                'render'      => static fn( $v, $output ) => esc_html( (string) $v ),
                'el_category' => 'text',
            ],

            // ── textarea ──────────────────────────────────────────────────────
            // Multi-line text. `sanitize_textarea_field` preserves newlines
            // (unlike `sanitize_text_field`) while still stripping HTML and
            // control chars. Render escapes first, then runs wpautop so the
            // output mirrors how WP renders post content (paragraphs in <p>,
            // single newlines as <br>). `output="raw"` gives the plain
            // escaped string — useful when injecting into a context where
            // tags would break (e.g. an HTML attribute).
            'textarea' => [
                'label'       => __( 'Textarea', 'paradise-widgets-for-elementor' ),
                'sanitize'    => static fn( $v ) => sanitize_textarea_field( (string) $v ),
                'render'      => static fn( $v, $output ) => 'raw' === $output
                    ? esc_html( (string) $v )
                    : wpautop( esc_html( (string) $v ) ),
                'el_category' => 'text',
            ],


            // ── url ───────────────────────────────────────────────────────────
            // Single URL string. esc_url_raw and esc_url are NOT
            // interchangeable: esc_url_raw is for storage (no HTML
            // entity encoding — `&` stays as `&`), esc_url is for HTML
            // output (HTML-encodes special chars so the URL is safe in
            // an attribute). Using esc_url_raw inside HTML output would
            // emit `?a=1&b=2` instead of `?a=1&amp;b=2` — invalid HTML.
            //
            //   output=""     → escaped URL (default; suitable for href/src)
            //   output="link" → <a href="...">URL</a>
            'url' => [
                'label'       => __( 'URL', 'paradise-widgets-for-elementor' ),
                'sanitize'    => static fn( $v ) => esc_url_raw( (string) $v ),
                'render'      => static function ( $v, $output ) {
                    $url = (string) $v;
                    if ( $url === '' ) {
                        return '';
                    }
                    return match ( $output ) {
                        'link' => '<a href="' . esc_url( $url ) . '">' . esc_html( $url ) . '</a>',
                        default => esc_url( $url ),
                    };
                },
                'el_category' => 'url',
            ],

            // ── image ─────────────────────────────────────────────────────────
            // Attachment ID stored as int. We store the ID (not the URL) so
            // the URL stays fresh if WP regenerates sizes, the file is moved
            // to a CDN, or the user replaces the media file. Render converts
            // ID → URL/HTML at output time via the WP attachment helpers.
            //
            //   output=""     → wp_get_attachment_url($id), HTML-escaped
            //   output="html" → <img …> with srcset, alt, loading="lazy"
            //                   (wp_get_attachment_image handles all of these)
            //   output="id"   → the numeric ID as a string
            'image' => [
                'label'       => __( 'Image', 'paradise-widgets-for-elementor' ),
                'sanitize'    => static fn( $v ) => absint( $v ),
                'render'      => static function ( $v, $output ) {
                    $id = absint( $v );
                    if ( $id === 0 ) {
                        return '';
                    }
                    return match ( $output ) {
                        'html'  => (string) wp_get_attachment_image( $id, 'full' ),
                        'id'    => (string) $id,
                        default => esc_url( (string) wp_get_attachment_url( $id ) ),
                    };
                },
                'el_category' => 'image',
            ],

            // ── date ──────────────────────────────────────────────────────────
            // ISO-8601 (YYYY-MM-DD) stored, locale-formatted on output. Sanitize
            // accepts the strict ISO shape only — anything else collapses to ''
            // so a corrupted POST can't write garbage into the option.
            //
            //   output=""          → date_i18n(get_option('date_format'), …)
            //   output="raw"       → YYYY-MM-DD (e.g. for HTML <time datetime>)
            //   output="timestamp" → Unix seconds at midnight site-tz
            'date' => [
                'label'       => __( 'Date', 'paradise-widgets-for-elementor' ),
                'sanitize'    => static fn( $v ) => preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $v ) ? (string) $v : '',
                'render'      => static function ( $v, $output ) {
                    $iso = (string) $v;
                    if ( $iso === '' ) {
                        return '';
                    }
                    $ts = strtotime( $iso . ' 00:00:00' );
                    if ( $ts === false ) {
                        return '';
                    }
                    return match ( $output ) {
                        'raw'       => esc_html( $iso ),
                        'timestamp' => (string) $ts,
                        default     => esc_html( (string) date_i18n( (string) get_option( 'date_format' ), $ts ) ),
                    };
                },
                'el_category' => 'text',
            ],

            // ── time ──────────────────────────────────────────────────────────
            // HH:MM (24-hour) stored, locale-formatted on output. Anchored to
            // "today" so date_i18n can format it through the same pipeline as
            // dates without inventing a custom time formatter.
            //
            //   output=""    → date_i18n(get_option('time_format'), …)
            //   output="raw" → HH:MM
            'time' => [
                'label'       => __( 'Time', 'paradise-widgets-for-elementor' ),
                'sanitize'    => static fn( $v ) => preg_match( '/^\d{2}:\d{2}$/', (string) $v ) ? (string) $v : '',
                'render'      => static function ( $v, $output ) {
                    $hm = (string) $v;
                    if ( $hm === '' ) {
                        return '';
                    }
                    $ts = strtotime( 'today ' . $hm );
                    if ( $ts === false ) {
                        return '';
                    }
                    return match ( $output ) {
                        'raw'   => esc_html( $hm ),
                        default => esc_html( (string) date_i18n( (string) get_option( 'time_format' ), $ts ) ),
                    };
                },
                'el_category' => 'text',
            ],

            // ── email ─────────────────────────────────────────────────────────
            // Visible in both TEXT and URL dynamic-tag dropdowns — the latter
            // lets Button/Link controls bind a mailto: directly. Render gives
            // a plain escaped address by default, and a mailto: form on demand
            // (used by both the URL dynamic tag and the shortcode link mode).
            //
            //   output=""       → escaped email string
            //   output="mailto" → mailto: URL (used for Button URL bindings)
            //   output="link"   → <a href="mailto:…">email</a>
            'email' => [
                'label'       => __( 'Email', 'paradise-widgets-for-elementor' ),
                'sanitize'    => static fn( $v ) => sanitize_email( (string) $v ),
                'render'      => static function ( $v, $output ) {
                    $email = (string) $v;
                    if ( $email === '' ) {
                        return '';
                    }
                    return match ( $output ) {
                        'mailto' => esc_url( 'mailto:' . $email ),
                        'link'   => '<a href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a>',
                        default  => esc_html( $email ),
                    };
                },
                // Two categories: text (Heading, Button label, etc.) AND
                // url (Button URL, Link control). field_options_for_category
                // accepts both string and array shapes — see the registry
                // helper's comment for the rationale.
                'el_category' => [ 'text', 'url' ],
            ],

            // ── number ────────────────────────────────────────────────────────
            // Integer stored. Validation via filter_var so '1.5' coerces
            // cleanly (it returns false and we fall back to 0). PHP's
            // (int) cast would silently truncate '1.5' to 1 — semantically
            // different and harder to catch.
            'number' => [
                'label'       => __( 'Number', 'paradise-widgets-for-elementor' ),
                'sanitize'    => static fn( $v ) => (int) filter_var( (string) $v, FILTER_VALIDATE_INT ),
                'render'      => static fn( $v, $output ) => esc_html( (string) (int) $v ),
                'el_category' => 'text',
            ],

            // ── color ─────────────────────────────────────────────────────────
            // 6-digit hex (#RRGGBB). Lowercased on save for cache friendliness.
            // Anything that doesn't match the hex pattern (e.g. 'rgb(…)' or a
            // 3-digit shorthand) collapses to '' rather than being stored.
            'color' => [
                'label'       => __( 'Color', 'paradise-widgets-for-elementor' ),
                'sanitize'    => static fn( $v ) => preg_match( '/^#[0-9a-fA-F]{6}$/', (string) $v ) ? strtolower( (string) $v ) : '',
                'render'      => static fn( $v, $output ) => esc_html( (string) $v ),
                'el_category' => 'text',
            ],

            // ── range ─────────────────────────────────────────────────────────
            // An open-bounded integer pair representing an interval (min,max).
            // Bounds are NOT clamped — a user can pick 50–200, 1–10, -40–40,
            // or anything that makes sense for their data. Sanitize only
            // enforces "both are integers" and swaps them if min > max so the
            // stored form's ordering invariant always holds.
            //
            // Stored as the comma string "min,max" for simplicity and
            // round-trip friendliness. A legacy single value (e.g. "50" from
            // an earlier preview of this type) migrates to "0,50" on save.
            //
            //   output=""    → "10 – 80" (en-dash)
            //   output="min" → "10"
            //   output="max" → "80"
            //   output="raw" → "10,80"
            'range' => [
                'label'    => __( 'Range', 'paradise-widgets-for-elementor' ),
                'sanitize' => static function ( $v ) {
                    $raw = trim( (string) $v );
                    if ( $raw === '' ) {
                        return '';
                    }
                    $parts = explode( ',', $raw );
                    if ( count( $parts ) === 1 ) {
                        // Legacy single-value migration: treat as max with min=0.
                        $min = 0;
                        $max = (int) filter_var( $parts[0], FILTER_VALIDATE_INT );
                    } else {
                        $min = (int) filter_var( $parts[0], FILTER_VALIDATE_INT );
                        $max = (int) filter_var( $parts[1], FILTER_VALIDATE_INT );
                    }
                    if ( $min > $max ) {
                        [ $min, $max ] = [ $max, $min ];
                    }
                    return $min . ',' . $max;
                },
                'render'   => static function ( $v, $output ) {
                    $raw = (string) $v;
                    if ( $raw === '' ) {
                        return '';
                    }
                    $parts = explode( ',', $raw );
                    $min   = (int) ( $parts[0] ?? 0 );
                    $max   = (int) ( $parts[1] ?? $min );
                    return match ( $output ) {
                        'min'   => esc_html( (string) $min ),
                        'max'   => esc_html( (string) $max ),
                        'raw'   => esc_html( $raw ),
                        default => esc_html( $min . ' – ' . $max ), // en-dash
                    };
                },
                'el_category' => 'text',
            ],
        ];

        return apply_filters( 'paradise_custom_field_types', $types );
    }

    /**
     * Return [slug => label] map of type slugs to display labels — useful
     * for building the type <select> in the admin UI.
     *
     * @return array<string, string>
     */
    public static function get_type_options(): array {
        $options = [];
        foreach ( self::get_types() as $slug => $type ) {
            $options[ $slug ] = $type['label'];
        }
        return $options;
    }

    // ── Internal data loading ─────────────────────────────────────────────────

    private static function load(): array {
        $data = (array) get_option( self::OPTION_KEY, [] );
        if ( ! isset( $data['groups'] ) || ! is_array( $data['groups'] ) ) {
            $data['groups'] = [];
        }
        return $data;
    }

    // ── Data access ───────────────────────────────────────────────────────────

    /**
     * Return all groups, sequentially indexed.
     *
     * @return array<int, array{slug:string, label:string, fields:array}>
     */
    public static function get_groups(): array {
        return array_values( self::load()['groups'] ?? [] );
    }

    /**
     * Find a field by its globally-unique key. Returns null if not found.
     *
     * @return array{key:string, label:string, type:string, value:mixed}|null
     */
    public static function get_field( string $key ): ?array {
        foreach ( self::get_groups() as $group ) {
            foreach ( $group['fields'] ?? [] as $field ) {
                if ( ( $field['key'] ?? '' ) === $key ) {
                    return $field;
                }
            }
        }
        return null;
    }

    /** Get the raw stored value for a field, or empty string if not found. */
    public static function get_value( string $key ): mixed {
        $field = self::get_field( $key );
        return $field['value'] ?? '';
    }

    /**
     * Render a field through its type's render callback.
     *
     * @param string $key    Field key.
     * @param string $output Output mode passed through to the type's renderer
     *                       (meaning is type-specific — e.g. for image:
     *                       '' → URL, 'html' → <img>).
     * @return string  HTML-safe output. Returns '' if the field doesn't
     *                 exist or its type is no longer registered.
     */
    public static function render( string $key, string $output = '' ): string {
        $field = self::get_field( $key );
        if ( null === $field ) {
            return '';
        }

        $types = self::get_types();
        $type  = $types[ $field['type'] ] ?? null;
        if ( null === $type || ! is_callable( $type['render'] ?? null ) ) {
            return '';
        }

        return (string) call_user_func( $type['render'], $field['value'] ?? '', $output );
    }

    // ── Persistence ───────────────────────────────────────────────────────────

    /**
     * Save raw POST data to the option, running each field through its
     * type's sanitize callback. Unknown types are dropped. Groups with
     * neither a slug nor a label are dropped (truly empty). Fields with
     * no key and no label, or an unregistered type, are dropped.
     *
     * If a group's slug is empty but its label is set, the slug is
     * auto-derived from the label via sanitize_title() — same pattern
     * WP uses for post and category slugs. Field keys behave the same
     * way: empty key + non-empty label = key derived from label.
     */
    public static function save( array $raw ): void {
        $types = self::get_types();
        $data  = [ 'groups' => [] ];

        $seen_keys = []; // enforce globally-unique field keys

        foreach ( $raw['groups'] ?? [] as $group_raw ) {
            $slug  = sanitize_key( $group_raw['slug']  ?? '' );
            $label = sanitize_text_field( $group_raw['label'] ?? '' );

            // Auto-derive slug from label if the user only filled the
            // label (common UX pattern — they think of the group by name).
            if ( $slug === '' && $label !== '' ) {
                $slug = sanitize_key( sanitize_title( $label ) );
            }
            if ( $slug === '' ) {
                continue;
            }

            $fields = [];
            foreach ( $group_raw['fields'] ?? [] as $field_raw ) {
                $key        = sanitize_key( $field_raw['key']  ?? '' );
                $type       = sanitize_key( $field_raw['type'] ?? '' );
                $field_label = sanitize_text_field( $field_raw['label'] ?? '' );

                // Same auto-derive trick for field keys.
                if ( $key === '' && $field_label !== '' ) {
                    $key = sanitize_key( sanitize_title( $field_label ) );
                }
                if ( $key === '' || ! isset( $types[ $type ] ) ) {
                    continue;
                }
                if ( isset( $seen_keys[ $key ] ) ) {
                    // First occurrence wins — duplicates from a stale
                    // submission are silently dropped rather than
                    // overwriting. The admin UI should prevent dupes
                    // before submit, so this is just a guardrail.
                    continue;
                }
                $seen_keys[ $key ] = true;

                $sanitize = $types[ $type ]['sanitize'] ?? null;
                $value    = is_callable( $sanitize )
                    ? call_user_func( $sanitize, $field_raw['value'] ?? '' )
                    : '';

                $fields[] = [
                    'key'   => $key,
                    'label' => $field_label,
                    'type'  => $type,
                    'value' => $value,
                ];
            }

            $data['groups'][] = [
                'slug'   => $slug,
                'label'  => $label,
                'fields' => $fields,
            ];
        }

        update_option( self::OPTION_KEY, $data );
    }

    // ── Export ────────────────────────────────────────────────────────────────

    /**
     * Snapshot of the stored data, in a shape that save() accepts directly.
     * Used by the Paradise_Import_Export class to bundle custom fields
     * into the same JSON payload as Site Info + widget settings.
     *
     * No normalisation step — unlike Site Info (which merges default hours
     * into each location for export-time completeness), custom fields are
     * already in their canonical form on disk. A round-trip
     * `save( export() )` is idempotent.
     */
    public static function export(): array {
        return [
            'groups' => self::get_groups(),
        ];
    }

    // ── Shortcode ─────────────────────────────────────────────────────────────

    public static function register_shortcode(): void {
        add_shortcode( 'paradise_field', [ __CLASS__, 'shortcode_handler' ] );
    }

    /**
     * [paradise_field key="copyright"]            → escaped text
     * [paradise_field key="logo" output="html"]   → <img …>
     *
     * Returns an empty string for unknown keys (callers can fall back).
     */
    public static function shortcode_handler( array $atts ): string {
        $atts = shortcode_atts( [
            'key'    => '',
            'output' => '',
        ], $atts, 'paradise_field' );

        $key = trim( (string) $atts['key'] );
        if ( $key === '' ) {
            return '';
        }

        return self::render( $key, (string) $atts['output'] );
    }
}
