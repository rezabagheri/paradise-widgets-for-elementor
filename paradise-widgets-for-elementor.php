<?php

/**
 * Plugin Name:       Paradise Widgets for Elementor
 * Plugin URI:        https://github.com/rezabagheri/paradise-widgets-for-elementor
 * Description:       Advanced custom Elementor widgets by Paradise. Phone Link, Bottom Navigation Bar, and more.
 * Version:           3.2.0
 * Requires at least: 6.1
 * Tested up to:      7.0
 * Requires PHP:      8.0
 * Requires Plugins:  elementor
 * Author:            Reza Bagheri
 * Author URI:        https://github.com/rezabagheri
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       paradise-widgets-for-elementor
 * Domain Path:       /languages
 */

if (! defined('ABSPATH')) {
    exit;
}

define('PARADISE_EW_VERSION', '3.2.0');
define('PARADISE_EW_DIR', plugin_dir_path(__FILE__));
define('PARADISE_EW_URL', plugin_dir_url(__FILE__));
define('PARADISE_EW_MIN_ELEMENTOR_VERSION', '3.5.0');

final class Paradise_Elementor_Widgets
{
    private static $instance = null;

    public static function get_instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('plugins_loaded', [ $this, 'load_site_info' ]);
        add_action('plugins_loaded', [ $this, 'load_custom_fields' ]);
        add_action('plugins_loaded', [ $this, 'load_admin' ]);
        add_action('plugins_loaded', [ $this, 'check_elementor_loaded' ], 20);
        // FAQ CPT registration calls feature_enabled(), which reads translated
        // labels via __(). Defer to `init` so the textdomain is loaded first;
        // priority 5 still fires before register_post_type at default priority.
        add_action('init', [ $this, 'load_faq_cpt_if_enabled' ], 5);
        add_action('elementor/init', [ $this, 'init' ]);
    }

    /**
     * Show an admin notice if Elementor is not active. Runs at plugins_loaded:20 so
     * Elementor's own bootstrap (priority 10) has had a chance to fire elementor/loaded.
     */
    public function check_elementor_loaded(): void
    {
        if ( ! did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', [ $this, 'notice_missing_elementor' ] );
        }
    }

    public function load_site_info(): void
    {
        require_once PARADISE_EW_DIR . 'includes/class-paradise-site-info.php';
        add_action('init', [ 'Paradise_Site_Info', 'register_shortcode' ]);
    }

    public function load_custom_fields(): void
    {
        require_once PARADISE_EW_DIR . 'includes/class-paradise-custom-fields.php';
        add_action('init', [ 'Paradise_Custom_Fields', 'register_shortcode' ]);
    }

    public function load_admin(): void
    {
        require_once PARADISE_EW_DIR . 'admin/class-paradise-ew-admin.php';
        Paradise_EW_Admin::init();

        require_once PARADISE_EW_DIR . 'admin/class-paradise-user-profile.php';
        Paradise_User_Profile::init();

        require_once PARADISE_EW_DIR . 'admin/class-paradise-site-info-admin.php';
        Paradise_Site_Info_Admin::init();

        require_once PARADISE_EW_DIR . 'admin/class-paradise-custom-fields-admin.php';
        Paradise_Custom_Fields_Admin::init();

        require_once PARADISE_EW_DIR . 'admin/class-paradise-import-export.php';
        Paradise_Import_Export::init();
    }

    public function load_faq_cpt_if_enabled(): void
    {
        require_once PARADISE_EW_DIR . 'includes/class-paradise-faq-cpt.php';
        if ( Paradise_EW_Admin::feature_enabled( 'faq_cpt' ) ) {
            Paradise_FAQ_CPT::init();
        }
    }

    // Note: no load_plugin_textdomain() call — WordPress.org auto-loads translations
    // for hosted plugins (since WP 4.6), and this plugin requires WP 6.1+.

    public function init(): void
    {
        // Skip registration on outdated Elementor — show notice instead of risking a fatal.
        if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, PARADISE_EW_MIN_ELEMENTOR_VERSION, '<' ) ) {
            add_action( 'admin_notices', [ $this, 'notice_outdated_elementor' ] );
            return;
        }

        add_action('elementor/elements/categories_registered', [ $this, 'register_category' ]);
        add_action('elementor/widgets/register', [ $this, 'register_widgets' ]);
        add_action('elementor/frontend/after_enqueue_styles', [ $this, 'enqueue_assets' ]);
        add_action('elementor/editor/after_enqueue_styles', [ $this, 'enqueue_assets' ]);
        add_action('elementor/preview/enqueue_styles', [ $this, 'enqueue_editor_placeholder_style' ]);
        add_action('elementor/dynamic_tags/register', [ $this, 'register_dynamic_tags' ]);
    }

    public function notice_missing_elementor(): void
    {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }
        printf(
            '<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
            esc_html__( 'Paradise Widgets for Elementor requires Elementor to be installed and active.', 'paradise-widgets-for-elementor' )
        );
    }

    public function notice_outdated_elementor(): void
    {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }
        printf(
            '<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
            sprintf(
                /* translators: %s: required Elementor version */
                esc_html__( 'Paradise Widgets for Elementor requires Elementor %s or greater.', 'paradise-widgets-for-elementor' ),
                esc_html( PARADISE_EW_MIN_ELEMENTOR_VERSION )
            )
        );
    }

    public function register_dynamic_tags( $dynamic_tags_manager ): void
    {
        require_once PARADISE_EW_DIR . 'includes/class-paradise-dynamic-tags.php';
        Paradise_Dynamic_Tags::register( $dynamic_tags_manager );

        require_once PARADISE_EW_DIR . 'includes/class-paradise-custom-fields-dynamic-tags.php';
        Paradise_CF_Tags::register( $dynamic_tags_manager );
    }

    public function register_category($elements_manager): void
    {
        $elements_manager->add_category('paradise', [
            'title' => esc_html__('Paradise Widgets', 'paradise-widgets-for-elementor'),
            'icon'  => 'fa fa-plug',
        ]);

        // Separate category for example/reference widgets shipped to help
        // plugin developers learn the Paradise patterns. Distinct from
        // the main "Paradise Widgets" category so end users don't confuse
        // teaching material with production widgets.
        $elements_manager->add_category('paradise-examples', [
            'title' => esc_html__('Paradise Examples', 'paradise-widgets-for-elementor'),
            'icon'  => 'fa fa-graduation-cap',
        ]);
    }

    /**
     * Register all per-widget CSS (always) and JS (when widget declares 'js' => true) handles.
     *
     * Naming convention derived from the registry key:
     *   key 'phone_link'  →  handle 'paradise-phone-link', file 'assets/css/phone-link.css'
     *
     * Elementor calls each widget's get_style_depends() / get_script_depends() and enqueues
     * only the handles needed for widgets actually on the page — keeping registration cheap.
     */
    /**
     * Enqueue the empty-widget placeholder stylesheet into the Elementor editor
     * preview iframe only. The placeholder markup is emitted by
     * Paradise_Widget_Base::render_editor_placeholder() during edit mode; this
     * keeps its CSS in a proper stylesheet instead of an inline <style> block.
     */
    public function enqueue_editor_placeholder_style(): void
    {
        wp_enqueue_style(
            'paradise-editor-placeholder',
            PARADISE_EW_URL . 'assets/css/editor-placeholder.css',
            [],
            PARADISE_EW_VERSION
        );
    }

    public function enqueue_assets(): void
    {
        foreach ( Paradise_EW_Admin::get_widget_registry() as $key => $config ) {
            $slug   = str_replace( '_', '-', $key );
            $handle = 'paradise-' . $slug;

            wp_register_style(
                $handle,
                PARADISE_EW_URL . 'assets/css/' . $slug . '.css',
                [],
                PARADISE_EW_VERSION
            );

            if ( ! empty( $config['js'] ) ) {
                wp_register_script(
                    $handle,
                    PARADISE_EW_URL . 'assets/js/' . $slug . '.js',
                    [],
                    PARADISE_EW_VERSION,
                    true
                );
            }
        }
    }

    /**
     * Instantiate every enabled widget. File path and class name are derived from the
     * registry key — see Paradise_EW_Admin::get_widget_registry() for the convention.
     *
     * To add a new widget:
     *   1. Create widgets/class-paradise-{slug}.php with class Paradise_{Key_Ucwords}_Widget
     *   2. Add one entry to Paradise_EW_Admin::$widget_registry (label + description, plus 'js' => true if it ships JS)
     *   3. Place assets at assets/css/{slug}.css and (if JS) assets/js/{slug}.js
     */
    public function register_widgets($widgets_manager): void
    {
        // Load the base class once, before any widget file is required — concrete
        // widgets that opt into Paradise_Widget_Base need their parent class to
        // exist at file-parse time. Safe to require here: Elementor is already
        // loaded (we're inside the elementor/widgets/register action), so the
        // \Elementor\Widget_Base symbol that the base extends is resolvable.
        require_once PARADISE_EW_DIR . 'includes/class-paradise-widget-base.php';

        foreach (Paradise_EW_Admin::get_widget_registry() as $key => $config) {
            if (!Paradise_EW_Admin::widget_enabled($key)) {
                continue;
            }
            require_once PARADISE_EW_DIR . $config['file'];
            $widgets_manager->register(new $config['class']());
        }
    }
}

Paradise_Elementor_Widgets::get_instance();
