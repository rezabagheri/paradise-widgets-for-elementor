<?php
/**
 * Paradise Elementor Widgets — Admin Settings
 *
 * Registers a top-level "Paradise" admin menu shared by all Paradise plugins,
 * and an "Elementor Widgets" submenu for this plugin's settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Paradise_EW_Admin {

    const OPTION_KEY    = 'paradise_ew_settings';
    const MENU_SLUG     = 'paradise-widgets';            // Parent slug + Dashboard landing
    const WIDGETS_SLUG  = 'paradise-widgets-elementor';  // "Elementor Widgets" — per-widget toggles
    const SETTINGS_SLUG = 'paradise-widgets-settings';   // "Settings" — plugin-wide feature flags

    /**
     * Widget registry — single source of truth for settings UI, asset loading, and widget instantiation.
     *
     * Per-widget keys:
     *   label       — human-readable name shown in the settings page
     *   description — short description shown in the settings page
     *   js          — (optional, default false) true if the widget ships a JS file
     *   example     — (optional, default false) marks a developer-reference widget
     *                 so the settings UI can group it separately
     *   default     — (optional, default true) initial enabled state when no
     *                 setting has been saved yet — set to false for example
     *                 widgets so end-user sites don't see them by default
     *
     * The 'file' and 'class' values are derived automatically from the registry key.
     * get_widget_registry() returns the enriched array so consumers never need to call the helpers directly.
     *
     * Method-not-property because PHP forbids function calls (like __()) in
     * static property initialisers — and the labels/descriptions need to go
     * through __() so wp i18n make-pot picks them up.
     *
     * Conventions derived from the key (e.g. 'phone_link'):
     *   slug    →  str_replace('_', '-', $key)              // 'phone-link'
     *   file    →  widgets/class-paradise-{slug}.php        // 'widgets/class-paradise-phone-link.php'
     *   class   →  Paradise_{Key_Ucwords}_Widget            // 'Paradise_Phone_Link_Widget'
     *   handle  →  paradise-{slug}                          // 'paradise-phone-link'  (same for CSS + JS)
     *   css     →  assets/css/{slug}.css                    // 'assets/css/phone-link.css'    (always present)
     *   js      →  assets/js/{slug}.js                      // 'assets/js/phone-link.js'      (only if js => true)
     */
    private static function widget_registry(): array {
        return [
            'phone_link' => [
                'label'       => __( 'Phone Link', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Clickable phone number with icon, prefix, and formatting options.', 'paradise-widgets-for-elementor' ),
            ],
            'bottom_nav' => [
                'label'       => __( 'Bottom Navigation Bar', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Fixed mobile bottom bar with icons, labels, badges, and speed dial.', 'paradise-widgets-for-elementor' ),
                'js'          => true,
            ],
            'author_card' => [
                'label'       => __( 'Author Card', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Author profile card with photo, credentials, bio, custom fields, and CTA button.', 'paradise-widgets-for-elementor' ),
            ],
            'phone_button' => [
                'label'       => __( 'Phone Button', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Fully-styled CTA button for phone calls or WhatsApp. Supports custom text, icon, colors, hover, and border radius.', 'paradise-widgets-for-elementor' ),
            ],
            'floating_call_btn' => [
                'label'       => __( 'Floating Call Button', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Fixed-position call/WhatsApp button that stays visible while scrolling. Supports icon, optional label, pulse animation, and corner positioning.', 'paradise-widgets-for-elementor' ),
            ],
            'announcement_bar' => [
                'label'       => __( 'Announcement Bar', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Fixed full-width banner for announcements, promotions, or alerts. Supports icon, message, CTA button, and dismissal with session/days/permanent memory.', 'paradise-widgets-for-elementor' ),
                'js'          => true,
            ],
            'cookie_consent_bar' => [
                'label'       => __( 'Cookie Consent Bar', 'paradise-widgets-for-elementor' ),
                'description' => __( 'GDPR/cookie consent bar with Accept and Decline buttons. Stores user choice in localStorage with configurable expiry. Dispatches consent events for analytics integration.', 'paradise-widgets-for-elementor' ),
                'js'          => true,
            ],
            'back_to_top' => [
                'label'       => __( 'Back to Top', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Fixed-position button that appears after scrolling past a threshold and smoothly returns the user to the top of the page.', 'paradise-widgets-for-elementor' ),
                'js'          => true,
            ],
            'off_canvas_menu' => [
                'label'       => __( 'Off-Canvas Menu', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Slide-in panel with a WordPress menu. Triggered by an inline button or the Paradise.openOffCanvas() JS API (e.g. from Bottom Nav).', 'paradise-widgets-for-elementor' ),
                'js'          => true,
            ],
            'sticky_header' => [
                'label'       => __( 'Sticky Header', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Place inside any Elementor section to make it sticky. Applies scroll effects (shadow, background change, shrink) when scrolling past a threshold.', 'paradise-widgets-for-elementor' ),
                'js'          => true,
            ],
            'google_map' => [
                'label'       => __( 'Google Map', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Embeds a Google Map via iframe. Source can be a Site Info address (Map URL field) or a manually entered URL. Supports border radius, shadow, and height controls.', 'paradise-widgets-for-elementor' ),
            ],
            'social_links' => [
                'label'       => __( 'Social Links', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Row or column of social media icon links. Source: Site Info socials or custom list. Supports brand/uniform colors, hover animations, icon shapes, and icon+label display modes.', 'paradise-widgets-for-elementor' ),
            ],
            'business_hours' => [
                'label'       => __( 'Business Hours', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Displays business hours from Site Info with a live Open Now / Closed badge. Highlights today\'s row and supports 12/24-hour format.', 'paradise-widgets-for-elementor' ),
                'js'          => true,
            ],
            'local_business_schema' => [
                'label'       => __( 'LocalBusiness Schema', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Invisible widget that outputs Schema.org JSON-LD markup using Site Info data (name, phone, address, hours, social links). Helps Google display rich results.', 'paradise-widgets-for-elementor' ),
            ],
            'faq_accordion' => [
                'label'       => __( 'FAQ Accordion', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Collapsible Q&A list with accordion or multi-expand mode. Supports Schema.org FAQPage markup for Google rich results.', 'paradise-widgets-for-elementor' ),
                'js'          => true,
            ],
            'soundcloud' => [
                'label'       => __( 'SoundCloud', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Embeds a SoundCloud single track or playlist (Set) via the official player iframe. Choose Visual (with artwork) or Classic (compact mini) player style, set the accent colour, and toggle auto-play, comments, related tracks, and uploader name. URL field supports Elementor Dynamic Tags, so ACF Pro users can bind it to a post field. Also ships two playlist modes: ACF Repeater (reads a per-post Repeater) and Manual List (build the list inside the widget).', 'paradise-widgets-for-elementor' ),
                'js'          => true,
            ],

            // ---- Examples (developer learning material) -------------------------
            // These widgets live in the "Paradise Examples" category in the editor,
            // separate from the production widgets above. Disabled by default so
            // end-user sites don't see them unless a developer turns them on.

            'feature_card_example' => [
                'label'       => __( 'Feature Card (Example)', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Heavily-commented reference widget for developers — read widgets/class-paradise-feature-card-example.php to learn every Paradise widget pattern (controls, render, asset handles, base-class usage). Shows in the "Paradise Examples" editor category. Disabled by default.', 'paradise-widgets-for-elementor' ),
                'example'     => true,
                'default'     => false,
            ],
        ];
    }

    /**
     * Plugin feature flags — each key defaults to the value in $feature_defaults.
     * Same method-not-property reason as widget_registry() above.
     */
    private static function feature_registry(): array {
        return [
            'developer_mode' => [
                'label'       => __( 'Developer Mode', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Surfaces developer-oriented tools: reference widgets in the "Developer Examples" section, and additional details aimed at people extending or theming this plugin. Off by default — turn on if you are building with Paradise rather than just using it.', 'paradise-widgets-for-elementor' ),
            ],
            'show_profile_social' => [
                'label'       => __( 'Social Links fields on user profiles', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Shows the Paradise Social Links section on the WordPress user profile edit page (wp-admin → Users → Edit). Disable if you manage social links elsewhere.', 'paradise-widgets-for-elementor' ),
            ],
            'faq_cpt' => [
                'label'       => __( 'FAQ Post Type', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Registers a "FAQs" post type so you can manage Q&A items centrally and use them in the FAQ Accordion widget. Disable if you only use static FAQ items.', 'paradise-widgets-for-elementor' ),
            ],
        ];
    }

    /** Default value for each feature when the option has never been saved. */
    private static array $feature_defaults = [
        'developer_mode'      => false,
        'show_profile_social' => true,
        'faq_cpt'             => true,
    ];

    public static function init(): void {
        // Priority 9 (before default 10) so our Dashboard submenu lands FIRST
        // in $submenu['paradise-widgets']. WordPress treats whichever submenu
        // is first as the landing page when the user clicks the parent menu —
        // not the one whose slug matches the parent. If we run at 10, core's
        // _add_post_type_submenus (FAQ CPT with show_in_menu => 'paradise-widgets')
        // or sibling admin classes can register their submenu before us and
        // steal the landing slot.
        add_action( 'admin_menu',    [ __CLASS__, 'register_menus' ], 9 );
        add_action( 'admin_init',    [ __CLASS__, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_assets' ] );
    }

    // -------------------------------------------------------------------------
    // Menus
    // -------------------------------------------------------------------------

    public static function register_menus(): void {
        // Top-level "Paradise" menu — only add it once (other Paradise plugins
        // will also call add_menu_page with the same slug; WordPress deduplicates).
        // Landing callback = Dashboard.
        add_menu_page(
            esc_html__( 'Paradise', 'paradise-widgets-for-elementor' ),
            esc_html__( 'Paradise', 'paradise-widgets-for-elementor' ),
            'manage_options',
            self::MENU_SLUG,
            [ __CLASS__, 'render_dashboard_page' ],
            'data:image/svg+xml;base64,' . base64_encode( self::menu_icon_svg() ),
            58
        );

        // First submenu — "Dashboard" — uses the parent slug so this IS the
        // landing page when the user clicks "Paradise" in the admin menu.
        add_submenu_page(
            self::MENU_SLUG,
            esc_html__( 'Dashboard', 'paradise-widgets-for-elementor' ),
            esc_html__( 'Dashboard', 'paradise-widgets-for-elementor' ),
            'manage_options',
            self::MENU_SLUG,
            [ __CLASS__, 'render_dashboard_page' ]
        );

        // "Elementor Widgets" — per-widget enable/disable toggles.
        add_submenu_page(
            self::MENU_SLUG,
            esc_html__( 'Elementor Widgets', 'paradise-widgets-for-elementor' ),
            esc_html__( 'Elementor Widgets', 'paradise-widgets-for-elementor' ),
            'manage_options',
            self::WIDGETS_SLUG,
            [ __CLASS__, 'render_widgets_page' ]
        );

        // "Settings" — plugin-wide feature flags (Developer Mode, FAQ CPT, …).
        add_submenu_page(
            self::MENU_SLUG,
            esc_html__( 'Settings', 'paradise-widgets-for-elementor' ),
            esc_html__( 'Settings', 'paradise-widgets-for-elementor' ),
            'manage_options',
            self::SETTINGS_SLUG,
            [ __CLASS__, 'render_settings_page' ]
        );
    }

    // -------------------------------------------------------------------------
    // Settings API
    // -------------------------------------------------------------------------

    public static function register_settings(): void {
        register_setting(
            self::OPTION_KEY . '_group',
            self::OPTION_KEY,
            [ 'sanitize_callback' => [ __CLASS__, 'sanitize_settings' ] ]
        );
    }

    /**
     * Sanitize the settings array before it is saved to the database.
     *
     * Only the section identified by the hidden `_section` marker is rewritten;
     * the other section is preserved from the existing stored value. This is
     * what stops the Widgets page from wiping all features (and vice versa)
     * just because its form happens to omit those fields — unchecked HTML
     * checkboxes don't post anything, so "missing" can't mean "set to false".
     *
     * Import/Export calls this with `_section => 'all'` so it can rewrite both
     * sections in one shot from a JSON payload.
     *
     * Any key not in the registries is discarded.
     */
    public static function sanitize_settings( mixed $input ): array {
        $section  = $input['_section'] ?? '';
        $existing = (array) get_option( self::OPTION_KEY, [] );

        // Start from existing values so unrelated sections aren't touched.
        $clean = [
            'widgets'  => (array) ( $existing['widgets']  ?? [] ),
            'features' => (array) ( $existing['features'] ?? [] ),
        ];

        if ( 'widgets' === $section || 'all' === $section ) {
            $clean['widgets'] = [];
            foreach ( array_keys( self::widget_registry() ) as $key ) {
                $clean['widgets'][ $key ] = ! empty( $input['widgets'][ $key ] );
            }
        }
        if ( 'features' === $section || 'all' === $section ) {
            $clean['features'] = [];
            foreach ( array_keys( self::feature_registry() ) as $key ) {
                $clean['features'][ $key ] = ! empty( $input['features'][ $key ] );
            }
        }

        return $clean;
    }

    // -------------------------------------------------------------------------
    // Public helper — read settings
    // -------------------------------------------------------------------------

    /**
     * Returns the stored settings array with safe defaults.
     *
     * Widget defaults: enabled (true) unless the registry entry sets
     * 'default' => false (used for example/learning widgets that should
     * stay off on end-user sites unless a developer opts in).
     *
     * Feature defaults come from self::$feature_defaults.
     */
    public static function get(): array {
        $widget_defaults = [];
        foreach ( self::widget_registry() as $key => $meta ) {
            $widget_defaults[ $key ] = $meta['default'] ?? true;
        }

        $defaults = [
            'widgets'  => $widget_defaults,
            'features' => self::$feature_defaults,
        ];
        $stored = get_option( self::OPTION_KEY, [] );
        return array_replace_recursive( $defaults, (array) $stored );
    }

    /**
     * Returns true if a specific widget is enabled.
     * Falls back to the registry's per-widget default if no stored value exists.
     */
    public static function widget_enabled( string $key ): bool {
        $settings        = self::get();
        $registry_default = self::widget_registry()[ $key ]['default'] ?? true;
        return $settings['widgets'][ $key ] ?? $registry_default;
    }

    /**
     * Returns true if a specific feature flag is enabled.
     *
     * Reads the stored option directly instead of going through self::get(),
     * because get() walks widget_registry() which calls __() on widget labels.
     * Some callers (e.g. CPT loaders bound to plugins_loaded) hit this before
     * the textdomain is ready, and would otherwise trigger _doing_it_wrong
     * notices for "Translation loading triggered too early" (WP 6.7+).
     */
    public static function feature_enabled( string $key ): bool {
        $stored = get_option( self::OPTION_KEY, [] );
        $value  = $stored['features'][ $key ] ?? null;
        if ( $value !== null ) {
            return (bool) $value;
        }
        return self::$feature_defaults[ $key ] ?? false;
    }

    // -------------------------------------------------------------------------
    // Assets
    // -------------------------------------------------------------------------

    public static function enqueue_admin_assets( string $hook ): void {
        // Load admin.css on every Paradise admin page, not just the top-level
        // Settings page. WordPress's admin-page hook strings come in two
        // shapes for plugins that register submenus under a custom menu:
        //
        //   - 'toplevel_page_{slug}'        — the top-level page itself
        //   - 'paradise_page_{slug}'        — submenus rendered via
        //                                     add_submenu_page() under
        //                                     the "Paradise" top-level
        //
        // Checking the prefix instead of a single exact match lets one
        // stylesheet cover the whole Paradise admin area (Settings,
        // Site Info, Import/Export, …) with a single declaration here.
        if ( ! str_starts_with( $hook, 'toplevel_page_' . self::MENU_SLUG )
            && ! str_starts_with( $hook, 'paradise_page_' ) ) {
            return;
        }
        wp_enqueue_style(
            'paradise-ew-admin',
            PARADISE_EW_URL . 'assets/css/admin.css',
            [],
            self::asset_version( 'assets/css/admin.css' )
        );
        wp_enqueue_script(
            'paradise-ew-admin',
            PARADISE_EW_URL . 'assets/js/admin.js',
            [],
            self::asset_version( 'assets/js/admin.js' ),
            true
        );
    }

    /**
     * Asset version helper — uses file mtime in dev so saves bust the cache
     * automatically, and the static plugin version in production so CDNs and
     * browser caches behave correctly.
     */
    private static function asset_version( string $relative_path ): string {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $full_path = PARADISE_EW_DIR . $relative_path;
            if ( file_exists( $full_path ) ) {
                return (string) filemtime( $full_path );
            }
        }
        return PARADISE_EW_VERSION;
    }

    // -------------------------------------------------------------------------
    // View
    // -------------------------------------------------------------------------

    public static function render_widgets_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'paradise-widgets-for-elementor' ) );
        }
        $settings = self::get();
        require PARADISE_EW_DIR . 'admin/views/page-widgets.php';
    }

    public static function render_settings_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'paradise-widgets-for-elementor' ) );
        }
        $settings = self::get();
        require PARADISE_EW_DIR . 'admin/views/page-settings.php';
    }

    public static function render_dashboard_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'paradise-widgets-for-elementor' ) );
        }
        $stats         = self::get_dashboard_stats();
        $shortcuts     = self::get_dashboard_shortcuts();
        $useful_links  = self::get_dashboard_useful_links();
        $system_status = self::get_dashboard_system_status();
        require PARADISE_EW_DIR . 'admin/views/page-dashboard.php';
    }

    /**
     * Returns the list of quick-link cards shown on the Dashboard.
     *
     * Each entry: dashicon class, title, description, url. Sibling pages are
     * referenced by their own PAGE_SLUG constants so a rename anywhere
     * propagates here automatically.
     */
    public static function get_dashboard_shortcuts(): array {
        return [
            [
                'icon'        => 'dashicons-layout',
                'title'       => __( 'Elementor Widgets', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Manage which Paradise widgets appear in the Elementor editor.', 'paradise-widgets-for-elementor' ),
                'url'         => admin_url( 'admin.php?page=' . self::WIDGETS_SLUG ),
            ],
            [
                'icon'        => 'dashicons-admin-generic',
                'title'       => __( 'Settings', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Configure plugin-wide feature flags and developer options.', 'paradise-widgets-for-elementor' ),
                'url'         => admin_url( 'admin.php?page=' . self::SETTINGS_SLUG ),
            ],
            [
                'icon'        => 'dashicons-store',
                'title'       => __( 'Site Info', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Business name, phone, address, hours, and social links shared across widgets.', 'paradise-widgets-for-elementor' ),
                'url'         => admin_url( 'admin.php?page=' . Paradise_Site_Info_Admin::PAGE_SLUG ),
            ],
            [
                'icon'        => 'dashicons-database',
                'title'       => __( 'Custom Fields', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Define typed fields that widgets and shortcodes can pull dynamic data from.', 'paradise-widgets-for-elementor' ),
                'url'         => admin_url( 'admin.php?page=' . Paradise_Custom_Fields_Admin::PAGE_SLUG ),
            ],
            [
                'icon'        => 'dashicons-upload',
                'title'       => __( 'Import / Export', 'paradise-widgets-for-elementor' ),
                'description' => __( 'Back up Paradise settings or migrate them between sites.', 'paradise-widgets-for-elementor' ),
                'url'         => admin_url( 'admin.php?page=' . Paradise_Import_Export::PAGE_SLUG ),
            ],
        ];
    }

    /**
     * Returns system status checks shown on the Dashboard.
     *
     * Requirements are read from the plugin header (single source of truth)
     * via get_plugin_data() so a header bump propagates here automatically.
     * One extra row — WP_DEBUG — only appears when Developer Mode is on.
     */
    public static function get_dashboard_system_status(): array {
        // Pull required versions from the plugin header so they stay in sync
        // with whatever WordPress itself uses for its own "requires" checks.
        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $header     = get_plugin_data( PARADISE_EW_DIR . 'paradise-widgets-for-elementor.php', false, false );
        $php_min    = $header['RequiresPHP'] ?? '8.0';
        $wp_min     = $header['RequiresWP']  ?? '6.1';
        $elementor_min = PARADISE_EW_MIN_ELEMENTOR_VERSION;

        $checks = [];

        // PHP
        $checks[] = [
            'label'    => __( 'PHP Version', 'paradise-widgets-for-elementor' ),
            'value'    => PHP_VERSION,
            'required' => sprintf( '%s+', $php_min ),
            'status'   => version_compare( PHP_VERSION, $php_min, '>=' ) ? 'ok' : 'fail',
        ];

        // WordPress
        global $wp_version;
        $checks[] = [
            'label'    => __( 'WordPress Version', 'paradise-widgets-for-elementor' ),
            'value'    => $wp_version,
            'required' => sprintf( '%s+', $wp_min ),
            'status'   => version_compare( $wp_version, $wp_min, '>=' ) ? 'ok' : 'fail',
        ];

        // Elementor
        if ( did_action( 'elementor/loaded' ) && defined( 'ELEMENTOR_VERSION' ) ) {
            $checks[] = [
                'label'    => __( 'Elementor Version', 'paradise-widgets-for-elementor' ),
                'value'    => ELEMENTOR_VERSION,
                'required' => sprintf( '%s+', $elementor_min ),
                'status'   => version_compare( ELEMENTOR_VERSION, $elementor_min, '>=' ) ? 'ok' : 'fail',
            ];
        } else {
            $checks[] = [
                'label'    => __( 'Elementor', 'paradise-widgets-for-elementor' ),
                'value'    => __( 'Not active', 'paradise-widgets-for-elementor' ),
                'required' => sprintf( '%s+', $elementor_min ),
                'status'   => 'fail',
            ];
        }

        // Memory Limit
        $memory_bytes = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
        $memory_min   = 64 * MB_IN_BYTES;
        $checks[] = [
            'label'    => __( 'Memory Limit', 'paradise-widgets-for-elementor' ),
            'value'    => size_format( $memory_bytes ),
            'required' => sprintf( '%s+', size_format( $memory_min ) ),
            'status'   => $memory_bytes >= $memory_min ? 'ok' : 'warning',
        ];

        // Dev-only extras
        if ( self::feature_enabled( 'developer_mode' ) ) {
            $debug_on = defined( 'WP_DEBUG' ) && WP_DEBUG;
            $checks[] = [
                'label'    => __( 'WP_DEBUG', 'paradise-widgets-for-elementor' ),
                'value'    => $debug_on
                    ? __( 'Enabled', 'paradise-widgets-for-elementor' )
                    : __( 'Disabled', 'paradise-widgets-for-elementor' ),
                'required' => '',
                'status'   => $debug_on ? 'ok' : 'info',
            ];
        }

        return $checks;
    }

    /**
     * Returns external links shown in the Dashboard "Useful Links" section.
     *
     * Support and Changelog point at GitHub (the public dev surface today).
     * Rate points at the WP.org reviews page — it 404s until the plugin is
     * approved, after which it lights up automatically with no code change.
     */
    public static function get_dashboard_useful_links(): array {
        return [
            [
                'icon'  => 'dashicons-welcome-learn-more',
                'title' => __( 'Documentation', 'paradise-widgets-for-elementor' ),
                'url'   => 'https://www.paradisecyber.com/elementor-widgets',
            ],
            [
                'icon'  => 'dashicons-sos',
                'title' => __( 'Get Support', 'paradise-widgets-for-elementor' ),
                'url'   => 'https://github.com/rezabagheri/paradise-widgets-for-elementor/issues',
            ],
            [
                'icon'  => 'dashicons-star-filled',
                'title' => __( 'Rate this Plugin', 'paradise-widgets-for-elementor' ),
                'url'   => 'https://wordpress.org/support/plugin/paradise-widgets-for-elementor/reviews/?filter=5#new-post',
            ],
            [
                'icon'  => 'dashicons-clipboard',
                'title' => __( 'Changelog', 'paradise-widgets-for-elementor' ),
                'url'   => 'https://github.com/rezabagheri/paradise-widgets-for-elementor/releases',
            ],
        ];
    }

    /**
     * Returns counts for the Dashboard "at a glance" section.
     *
     * Example widgets (registry entries with 'example' => true) are excluded
     * from both numerator and denominator when Developer Mode is off — so end
     * users see a clean "all enabled" count instead of an off-by-one that
     * begs the question "what's that disabled widget I never touched?".
     */
    public static function get_dashboard_stats(): array {
        $dev_mode = self::feature_enabled( 'developer_mode' );

        $widget_enabled = 0;
        $widget_total   = 0;
        foreach ( self::widget_registry() as $key => $meta ) {
            if ( ! empty( $meta['example'] ) && ! $dev_mode ) {
                continue;
            }
            $widget_total++;
            if ( self::widget_enabled( $key ) ) {
                $widget_enabled++;
            }
        }

        $feature_enabled = 0;
        $feature_total   = 0;
        foreach ( array_keys( self::feature_registry() ) as $key ) {
            $feature_total++;
            if ( self::feature_enabled( $key ) ) {
                $feature_enabled++;
            }
        }

        return [
            'widgets'  => [ 'enabled' => $widget_enabled, 'total' => $widget_total ],
            'features' => [ 'enabled' => $feature_enabled, 'total' => $feature_total ],
        ];
    }

    // -------------------------------------------------------------------------
    // Menu icon — silhouette of the brand "P" derived from .wordpress-org/icon.svg
    // -------------------------------------------------------------------------
    //
    // The big WP.org plugin-directory icon (256×256, red P on a dark rounded
    // plate) can't ride into the WP admin menu — that slot is 20×20 and WP
    // applies a CSS filter that flattens the icon to a single colour anyway.
    // So we extract just the P silhouette from the big icon's path data,
    // keep the 256×256 viewBox (the browser scales it down cleanly), and
    // paint it with the WP admin's default unfocused-icon grey #a7aaad so
    // it sits next to core Dashicons without standing out.
    //
    // Brand consistency: same swoosh-P shape that appears on the WP.org
    // banner and the plugin icon — just rendered single-colour for the
    // admin's monochrome icon convention.
    //
    // Hand-drawn path (not <text>) — the previous version used a sans-serif
    // <text>P</text> and rendered as whatever fallback font the browser had.
    private static function menu_icon_svg(): string {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" fill="#a7aaad">'
             . '<path d="M 80 208 L 110 96 C 122 64, 154 50, 184 58 C 214 66, 222 100, 206 130'
             .          ' C 192 156, 162 162, 138 152 C 134 150, 130 148, 128 144'
             .          ' L 116 196 L 110 218 Z"/>'
             . '</svg>';
    }

    // -------------------------------------------------------------------------
    // Registry accessors (used by the view)
    // -------------------------------------------------------------------------

    /**
     * Returns the widget registry enriched with derived 'file' and 'class' keys.
     * Consumers (e.g. register_widgets()) can use these without knowing the convention.
     */
    public static function get_widget_registry(): array {
        $enriched = [];
        foreach ( self::widget_registry() as $key => $meta ) {
            $enriched[ $key ] = $meta + [
                'file'  => self::key_to_file( $key ),
                'class' => self::key_to_class( $key ),
            ];
        }
        return $enriched;
    }

    /**
     * Derives the widget file path from the registry key.
     * Example: 'google_map' → 'widgets/class-paradise-google-map.php'
     */
    private static function key_to_file( string $key ): string {
        return 'widgets/class-paradise-' . str_replace( '_', '-', $key ) . '.php';
    }

    /**
     * Derives the widget class name from the registry key.
     * Example: 'google_map' → 'Paradise_Google_Map_Widget'
     */
    private static function key_to_class( string $key ): string {
        $parts = array_map( 'ucfirst', explode( '_', $key ) );
        return 'Paradise_' . implode( '_', $parts ) . '_Widget';
    }

    public static function get_feature_registry(): array {
        return self::feature_registry();
    }
}
