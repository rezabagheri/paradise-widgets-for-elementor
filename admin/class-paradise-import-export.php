<?php
/**
 * Paradise Import / Export
 *
 * Adds an "Import / Export" submenu under Paradise.
 * Exports Site Info + widget settings as a JSON file.
 * Imports a previously exported file, running all data through
 * the existing sanitization pipeline.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Paradise_Import_Export {

    const PAGE_SLUG    = 'paradise-import-export';
    const NONCE_EXPORT = 'paradise_export_data';
    const NONCE_IMPORT = 'paradise_import_data';

    public static function init(): void {
        add_action( 'admin_menu',                          [ __CLASS__, 'register_menu' ] );
        add_action( 'admin_post_paradise_export_data',     [ __CLASS__, 'handle_export' ] );
        add_action( 'admin_post_paradise_import_data',     [ __CLASS__, 'handle_import' ] );
    }

    // ── Menu ──────────────────────────────────────────────────────────────────

    public static function register_menu(): void {
        add_submenu_page(
            'paradise-widgets',
            esc_html__( 'Import / Export', 'paradise-widgets-for-elementor' ),
            esc_html__( 'Import / Export', 'paradise-widgets-for-elementor' ),
            'manage_options',
            self::PAGE_SLUG,
            [ __CLASS__, 'render_page' ]
        );
    }

    // ── Export ────────────────────────────────────────────────────────────────

    public static function handle_export(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'paradise-widgets-for-elementor' ) );
        }

        check_admin_referer( self::NONCE_EXPORT );

        $payload = [
            'version'       => PARADISE_EW_VERSION,
            'exported_at'   => gmdate( 'c' ),
            'site_info'     => Paradise_Site_Info::export(),
            'custom_fields' => Paradise_Custom_Fields::export(),
            'ew_settings'   => get_option( Paradise_EW_Admin::OPTION_KEY, [] ),
        ];

        // No JSON_UNESCAPED_SLASHES: this is a downloadable .json file (not inline
        // HTML), so escaping "/" as "\/" is harmless and keeps the codebase free of
        // that flag entirely. Still valid JSON that re-imports identically.
        $json     = wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
        $sitename = sanitize_file_name( strtolower( str_replace( ' ', '-', get_bloginfo( 'name' ) ) ) );
        $filename = 'paradise-' . $sitename . '-' . gmdate( 'Y-m-d' ) . '.json';

        nocache_headers();
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Content-Length: ' . strlen( $json ) );
        echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode() output
        exit;
    }

    // ── Import ────────────────────────────────────────────────────────────────

    public static function handle_import(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'paradise-widgets-for-elementor' ) );
        }

        check_admin_referer( self::NONCE_IMPORT );

        $redirect = admin_url( 'admin.php?page=' . self::PAGE_SLUG );

        // Validate upload
        $file = isset( $_FILES['paradise_import_file'] ) ? $_FILES['paradise_import_file'] : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- raw value is sanitized per-field in the save handler below

        if ( empty( $file['tmp_name'] ) || (int) $file['error'] !== UPLOAD_ERR_OK ) {
            wp_safe_redirect( add_query_arg( [ 'import' => 'error', 'reason' => 'no_file' ], $redirect ) );
            exit;
        }

        if ( strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) ) !== 'json' ) {
            wp_safe_redirect( add_query_arg( [ 'import' => 'error', 'reason' => 'not_json' ], $redirect ) );
            exit;
        }

        $content = file_get_contents( $file['tmp_name'] );
        if ( $content === false ) {
            wp_safe_redirect( add_query_arg( [ 'import' => 'error', 'reason' => 'read_error' ], $redirect ) );
            exit;
        }

        $data = json_decode( $content, true );
        if ( ! is_array( $data ) ) {
            wp_safe_redirect( add_query_arg( [ 'import' => 'error', 'reason' => 'invalid_json' ], $redirect ) );
            exit;
        }

        $imported = [];

        // Import Site Info — through save() for sanitization
        if ( isset( $data['site_info'] ) && is_array( $data['site_info'] ) ) {
            Paradise_Site_Info::save( $data['site_info'] );
            $imported[] = 'site_info';
        }

        // Import Custom Fields — through save() for type-aware sanitization.
        // Backward-compatible: JSON files exported before custom fields existed
        // (pre-v3.1.x payloads) don't carry this key, so the isset() check
        // silently skips this branch for them.
        if ( isset( $data['custom_fields'] ) && is_array( $data['custom_fields'] ) ) {
            Paradise_Custom_Fields::save( $data['custom_fields'] );
            $imported[] = 'custom_fields';
        }

        // Import widget enable/disable settings — through sanitize_settings().
        // sanitize_settings() partial-updates by default (driven by the hidden
        // _section marker that the admin forms post), so we explicitly tag
        // this payload as 'all' — an import is authoritative and rewrites
        // both the widgets and features sections in one shot.
        if ( isset( $data['ew_settings'] ) && is_array( $data['ew_settings'] ) ) {
            $payload = $data['ew_settings'];
            $payload['_section'] = 'all';
            $clean = Paradise_EW_Admin::sanitize_settings( $payload );
            update_option( Paradise_EW_Admin::OPTION_KEY, $clean );
            $imported[] = 'ew_settings';
        }

        wp_safe_redirect( add_query_arg( [
            'import'   => 'success',
            'sections' => implode( ',', $imported ),
        ], $redirect ) );
        exit;
    }

    // ── Page ──────────────────────────────────────────────────────────────────

    public static function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        require PARADISE_EW_DIR . 'admin/views/page-import-export.php';
    }
}
