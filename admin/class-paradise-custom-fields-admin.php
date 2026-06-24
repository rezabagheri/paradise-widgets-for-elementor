<?php
/**
 * Paradise Custom Fields — Admin
 *
 * Registers a "Custom Fields" submenu under the Paradise admin menu and
 * handles saving user-defined groups + fields. Field rendering in the UI
 * is type-aware: each entry in Paradise_Custom_Fields::get_types() drives
 * which <input>/<textarea>/media-picker appears.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Paradise_Custom_Fields_Admin {

    const PAGE_SLUG = 'paradise-custom-fields';
    const NONCE     = 'paradise_custom_fields_save';

    public static function init(): void {
        add_action( 'admin_menu',                              [ __CLASS__, 'register_menu' ] );
        add_action( 'admin_post_paradise_save_custom_fields',  [ __CLASS__, 'handle_save' ] );
        add_action( 'admin_enqueue_scripts',                   [ __CLASS__, 'enqueue_assets' ] );
    }

    // ── Menu ──────────────────────────────────────────────────────────────────

    public static function register_menu(): void {
        add_submenu_page(
            'paradise-widgets',
            esc_html__( 'Custom Fields', 'paradise-widgets-for-elementor' ),
            esc_html__( 'Custom Fields', 'paradise-widgets-for-elementor' ),
            'manage_options',
            self::PAGE_SLUG,
            [ __CLASS__, 'render_page' ]
        );
    }

    // ── Assets ────────────────────────────────────────────────────────────────

    /**
     * Enqueue the page CSS + JS *and* the WP media library scripts so the
     * "Choose Image" button can open the media modal. wp_enqueue_media()
     * loads everything wp.media needs (backbone views, mce, models, etc.).
     */
    public static function enqueue_assets( string $hook ): void {
        if ( strpos( $hook, self::PAGE_SLUG ) === false ) {
            return;
        }

        wp_enqueue_media();

        wp_enqueue_style(
            'paradise-custom-fields-admin',
            PARADISE_EW_URL . 'assets/css/custom-fields-admin.css',
            [],
            PARADISE_EW_VERSION
        );

        wp_enqueue_script(
            'paradise-custom-fields-admin',
            PARADISE_EW_URL . 'assets/js/custom-fields-admin.js',
            [ 'jquery-ui-sortable' ],
            PARADISE_EW_VERSION,
            true
        );
    }

    // ── Save handler ──────────────────────────────────────────────────────────

    public static function handle_save(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'paradise-widgets-for-elementor' ) );
        }

        check_admin_referer( self::NONCE );

        $raw = isset( $_POST['paradise_custom_fields'] ) && is_array( $_POST['paradise_custom_fields'] )
            ? wp_unslash( $_POST['paradise_custom_fields'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- raw value is sanitized per-field in the save handler below
            : [];

        Paradise_Custom_Fields::save( $raw );

        wp_safe_redirect( add_query_arg( 'saved', '1',
            admin_url( 'admin.php?page=' . self::PAGE_SLUG )
        ) );
        exit;
    }

    // ── Page render ───────────────────────────────────────────────────────────

    public static function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        require_once PARADISE_EW_DIR . 'admin/views/page-custom-fields.php';
    }
}
