<?php
/**
 * Admin view — Paradise Widgets for Elementor Dashboard (landing page).
 *
 * Variables available:
 *   $stats        array — shape defined by Paradise_EW_Admin::get_dashboard_stats()
 *   $shortcuts    array — shape defined by Paradise_EW_Admin::get_dashboard_shortcuts()
 *   $useful_links  array — shape defined by Paradise_EW_Admin::get_dashboard_useful_links()
 *   $system_status array — shape defined by Paradise_EW_Admin::get_dashboard_system_status()
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Admin view template; loop/local vars are scoped to the render include.

$status_icons = [
    'ok'      => 'dashicons-yes-alt',
    'warning' => 'dashicons-warning',
    'fail'    => 'dashicons-dismiss',
    'info'    => 'dashicons-info',
];

$widgets_enabled  = (int) ( $stats['widgets']['enabled']  ?? 0 );
$widgets_total    = (int) ( $stats['widgets']['total']    ?? 0 );
$features_enabled = (int) ( $stats['features']['enabled'] ?? 0 );
$features_total   = (int) ( $stats['features']['total']   ?? 0 );
?>
<div class="wrap paradise-ew-admin paradise-ew-dashboard">

    <header class="paradise-ew-admin__header">
        <h1 class="paradise-ew-admin__title">
            <?php esc_html_e( 'Paradise Widgets for Elementor', 'paradise-widgets-for-elementor' ); ?>
            <span class="paradise-ew-admin__version">v<?php echo esc_html( PARADISE_EW_VERSION ); ?></span>
        </h1>
    </header>

    <p class="paradise-ew-dashboard__subtitle">
        <?php esc_html_e( 'Welcome — manage which widgets and features are active on your site.', 'paradise-widgets-for-elementor' ); ?>
    </p>

    <div class="paradise-ew-admin__card">
        <div class="paradise-ew-admin__card-header">
            <div>
                <h2 class="paradise-ew-admin__card-title">
                    <?php esc_html_e( 'At a glance', 'paradise-widgets-for-elementor' ); ?>
                </h2>
                <p class="paradise-ew-admin__card-desc">
                    <?php esc_html_e( 'Quick snapshot of what is currently active.', 'paradise-widgets-for-elementor' ); ?>
                </p>
            </div>
        </div>

        <ul class="paradise-ew-dashboard__stats">
            <li>
                <strong><?php echo esc_html( $widgets_enabled ); ?></strong>
                <span><?php
                    printf(
                        /* translators: %d = total number of widgets */
                        esc_html__( 'of %d widgets enabled', 'paradise-widgets-for-elementor' ),
                        (int) $widgets_total
                    );
                ?></span>
            </li>
            <li>
                <strong><?php echo esc_html( $features_enabled ); ?></strong>
                <span><?php
                    printf(
                        /* translators: %d = total number of features */
                        esc_html__( 'of %d features enabled', 'paradise-widgets-for-elementor' ),
                        (int) $features_total
                    );
                ?></span>
            </li>
        </ul>
    </div>

    <div class="paradise-ew-admin__card">
        <div class="paradise-ew-admin__card-header">
            <div>
                <h2 class="paradise-ew-admin__card-title">
                    <?php esc_html_e( 'Quick Links', 'paradise-widgets-for-elementor' ); ?>
                </h2>
                <p class="paradise-ew-admin__card-desc">
                    <?php esc_html_e( 'Jump straight to any Paradise admin page.', 'paradise-widgets-for-elementor' ); ?>
                </p>
            </div>
        </div>

        <div class="paradise-ew-dashboard__shortcuts">
            <?php foreach ( $shortcuts as $shortcut ) : ?>
                <a class="paradise-ew-dashboard__shortcut" href="<?php echo esc_url( $shortcut['url'] ); ?>">
                    <span class="dashicons <?php echo esc_attr( $shortcut['icon'] ); ?>" aria-hidden="true"></span>
                    <h3 class="paradise-ew-dashboard__shortcut-title">
                        <?php echo esc_html( $shortcut['title'] ); ?>
                    </h3>
                    <p class="paradise-ew-dashboard__shortcut-desc">
                        <?php echo esc_html( $shortcut['description'] ); ?>
                    </p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="paradise-ew-admin__card">
        <div class="paradise-ew-admin__card-header">
            <div>
                <h2 class="paradise-ew-admin__card-title">
                    <?php esc_html_e( 'Useful Links', 'paradise-widgets-for-elementor' ); ?>
                </h2>
                <p class="paradise-ew-admin__card-desc">
                    <?php esc_html_e( 'Help, feedback, and what is new.', 'paradise-widgets-for-elementor' ); ?>
                </p>
            </div>
        </div>

        <ul class="paradise-ew-dashboard__links">
            <?php foreach ( $useful_links as $link ) : ?>
                <li>
                    <a class="paradise-ew-dashboard__link"
                       href="<?php echo esc_url( $link['url'] ); ?>"
                       target="_blank"
                       rel="noopener noreferrer">
                        <span class="dashicons <?php echo esc_attr( $link['icon'] ); ?>" aria-hidden="true"></span>
                        <span><?php echo esc_html( $link['title'] ); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="paradise-ew-admin__card">
        <div class="paradise-ew-admin__card-header">
            <div>
                <h2 class="paradise-ew-admin__card-title">
                    <?php esc_html_e( 'System Status', 'paradise-widgets-for-elementor' ); ?>
                </h2>
                <p class="paradise-ew-admin__card-desc">
                    <?php esc_html_e( 'Environment checks for the plugin requirements.', 'paradise-widgets-for-elementor' ); ?>
                </p>
            </div>
        </div>

        <table class="paradise-ew-status">
            <tbody>
                <?php foreach ( $system_status as $check ) :
                    $icon = $status_icons[ $check['status'] ] ?? 'dashicons-info';
                ?>
                <tr class="paradise-ew-status__row paradise-ew-status--<?php echo esc_attr( $check['status'] ); ?>">
                    <td class="paradise-ew-status__icon">
                        <span class="dashicons <?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
                    </td>
                    <td class="paradise-ew-status__label">
                        <?php echo esc_html( $check['label'] ); ?>
                    </td>
                    <td class="paradise-ew-status__value">
                        <?php echo esc_html( $check['value'] ); ?>
                    </td>
                    <td class="paradise-ew-status__required">
                        <?php if ( ! empty( $check['required'] ) ) : ?>
                            <?php
                            printf(
                                /* translators: %s = minimum required version, e.g. "8.0+" */
                                esc_html__( 'required %s', 'paradise-widgets-for-elementor' ),
                                esc_html( $check['required'] )
                            );
                            ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
