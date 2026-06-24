<?php
/**
 * Admin view — Elementor Widgets page (widget toggles only).
 *
 * Variables available: $settings (array from Paradise_EW_Admin::get())
 *
 * Plugin-wide feature flags (developer mode, FAQ CPT, profile fields) live
 * on the separate "Settings" page (admin/views/page-settings.php).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Admin view template; loop/local vars are scoped to the render include.

require_once PARADISE_EW_DIR . 'admin/views/partials/render-toggle-card.php';

// Bucket the widget registry into production and examples so each group
// renders into its own table with its own bulk-action header. The 'example'
// flag is set per-widget in Paradise_EW_Admin::$widget_registry.
$widget_groups = [
    'production' => [],
    'examples'   => [],
];
foreach ( Paradise_EW_Admin::get_widget_registry() as $key => $widget ) {
    $bucket = ! empty( $widget['example'] ) ? 'examples' : 'production';
    $widget_groups[ $bucket ][ $key ] = $widget;
}
?>
<div class="wrap paradise-ew-admin">

    <div class="paradise-ew-admin__header">
        <h1><?php esc_html_e( 'Elementor Widgets', 'paradise-widgets-for-elementor' ); ?></h1>
        <span class="paradise-ew-admin__version">
            v<?php echo esc_html( PARADISE_EW_VERSION ); ?>
        </span>
        <span class="paradise-ew-admin__dirty" hidden>
            <?php esc_html_e( 'Unsaved changes', 'paradise-widgets-for-elementor' ); ?>
        </span>
    </div>

    <?php settings_errors(); ?>

    <div class="paradise-ew-filter">
        <label for="paradise-ew-filter-input" class="screen-reader-text">
            <?php esc_html_e( 'Filter widgets', 'paradise-widgets-for-elementor' ); ?>
        </label>
        <input
            type="search"
            id="paradise-ew-filter-input"
            class="paradise-ew-filter__input"
            placeholder="<?php esc_attr_e( 'Filter — type to narrow widgets…', 'paradise-widgets-for-elementor' ); ?>"
            data-paradise-filter
            autocomplete="off"
        >
    </div>

    <form method="post" action="options.php">
        <?php settings_fields( Paradise_EW_Admin::OPTION_KEY . '_group' ); ?>

        <input type="hidden"
               name="<?php echo esc_attr( Paradise_EW_Admin::OPTION_KEY ); ?>[_section]"
               value="widgets">

        <?php paradise_ew_render_toggle_card(
            'widgets-production',
            esc_html__( 'Widgets', 'paradise-widgets-for-elementor' ),
            esc_html__( 'Enable or disable individual widgets. Disabled widgets are not registered with Elementor and will not appear in the widget panel.', 'paradise-widgets-for-elementor' ),
            $widget_groups['production'],
            'widgets',
            $settings['widgets'] ?? []
        ); ?>

        <?php if ( Paradise_EW_Admin::feature_enabled( 'developer_mode' ) ) : ?>
        <?php paradise_ew_render_toggle_card(
            'widgets-examples',
            esc_html__( 'Developer Examples', 'paradise-widgets-for-elementor' ),
            esc_html__( 'Reference widgets shipped to help developers learn the Paradise widget patterns. They live in a separate "Paradise Examples" category in the editor and are disabled by default — enable them when you want to inspect the reference inside Elementor.', 'paradise-widgets-for-elementor' ),
            $widget_groups['examples'],
            'widgets',
            $settings['widgets'] ?? []
        ); ?>
        <?php endif; ?>

        <?php submit_button( esc_html__( 'Save Widgets', 'paradise-widgets-for-elementor' ) ); ?>
    </form>

</div>
