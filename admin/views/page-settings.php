<?php
/**
 * Admin view — Settings page (plugin-wide feature flags).
 *
 * Variables available: $settings (array from Paradise_EW_Admin::get())
 *
 * Per-widget enable/disable lives on the "Elementor Widgets" page
 * (admin/views/page-widgets.php).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once PARADISE_EW_DIR . 'admin/views/partials/render-toggle-card.php';
?>
<div class="wrap paradise-ew-admin">

    <div class="paradise-ew-admin__header">
        <h1><?php esc_html_e( 'Settings', 'paradise-widgets-for-elementor' ); ?></h1>
        <span class="paradise-ew-admin__version">
            v<?php echo esc_html( PARADISE_EW_VERSION ); ?>
        </span>
        <span class="paradise-ew-admin__dirty" hidden>
            <?php esc_html_e( 'Unsaved changes', 'paradise-widgets-for-elementor' ); ?>
        </span>
    </div>

    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php settings_fields( Paradise_EW_Admin::OPTION_KEY . '_group' ); ?>

        <input type="hidden"
               name="<?php echo esc_attr( Paradise_EW_Admin::OPTION_KEY ); ?>[_section]"
               value="features">

        <?php paradise_ew_render_toggle_card(
            'features',
            esc_html__( 'Features', 'paradise-widgets-for-elementor' ),
            esc_html__( 'Enable or disable optional plugin features.', 'paradise-widgets-for-elementor' ),
            Paradise_EW_Admin::get_feature_registry(),
            'features',
            $settings['features'] ?? []
        ); ?>

        <?php submit_button( esc_html__( 'Save Settings', 'paradise-widgets-for-elementor' ) ); ?>
    </form>

</div>
