<?php
/**
 * Paradise FAQ — Custom Post Type
 *
 * Registers the `paradise_faq` CPT. Each post represents a FAQ "set"
 * (e.g. "Pricing FAQ", "General FAQ"). Q&A items are stored as post meta.
 *
 * Data model:
 *   post_title          → FAQ set name (shown in widget SELECT control)
 *   _paradise_faq_items → array of [ ['question' => …, 'answer' => …], … ]
 *   menu_order          → display order of sets in wp-admin list (lower = first)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Paradise_FAQ_CPT {

    const POST_TYPE = 'paradise_faq';
    const META_KEY  = '_paradise_faq_items';
    const NONCE     = 'paradise_faq_meta_box';

    public static function init(): void {
        add_action( 'init',                              [ __CLASS__, 'register_post_type' ] );
        add_action( 'add_meta_boxes',                    [ __CLASS__, 'add_meta_boxes' ] );
        add_action( 'save_post_' . self::POST_TYPE,      [ __CLASS__, 'save_meta_box' ], 10, 2 );
        add_action( 'admin_enqueue_scripts',             [ __CLASS__, 'enqueue_admin_assets' ] );
        add_filter( 'manage_' . self::POST_TYPE . '_posts_columns',       [ __CLASS__, 'admin_columns' ] );
        add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', [ __CLASS__, 'admin_column_content' ], 10, 2 );
    }

    // ── Registration ──────────────────────────────────────────────────────────

    public static function register_post_type(): void {
        register_post_type( self::POST_TYPE, [
            'labels' => [
                'name'               => esc_html__( 'FAQs',                   'paradise-widgets-for-elementor' ),
                'singular_name'      => esc_html__( 'FAQ',                    'paradise-widgets-for-elementor' ),
                'add_new_item'       => esc_html__( 'Add New FAQ Set',        'paradise-widgets-for-elementor' ),
                'edit_item'          => esc_html__( 'Edit FAQ Set',           'paradise-widgets-for-elementor' ),
                'new_item'           => esc_html__( 'New FAQ Set',            'paradise-widgets-for-elementor' ),
                'search_items'       => esc_html__( 'Search FAQs',            'paradise-widgets-for-elementor' ),
                'not_found'          => esc_html__( 'No FAQ sets found.',     'paradise-widgets-for-elementor' ),
                'not_found_in_trash' => esc_html__( 'No FAQ sets in Trash.',  'paradise-widgets-for-elementor' ),
                'menu_name'          => esc_html__( 'FAQs',                   'paradise-widgets-for-elementor' ),
            ],
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => 'paradise-widgets',
            'show_in_rest' => false,
            'supports'     => [ 'title', 'page-attributes' ],
            'menu_icon'    => 'dashicons-editor-help',
            'rewrite'      => false,
            'has_archive'  => false,
        ] );
    }

    // ── Meta box ──────────────────────────────────────────────────────────────

    public static function add_meta_boxes(): void {
        add_meta_box(
            'paradise_faq_items',
            esc_html__( 'FAQ Items', 'paradise-widgets-for-elementor' ),
            [ __CLASS__, 'render_meta_box' ],
            self::POST_TYPE,
            'normal',
            'high'
        );
    }

    public static function render_meta_box( \WP_Post $post ): void {
        $items = self::get_raw_items( $post->ID );
        wp_nonce_field( self::NONCE, self::NONCE . '_nonce' );
        ?>
        <div class="paradise-faq-mb">
            <div class="paradise-faq-mb-rows" id="paradise-faq-mb-rows">
                <?php foreach ( $items as $i => $item ) :
                    $editor_id = 'paradise_faq_a_' . $post->ID . '_' . $i;
                ?>
                <div class="paradise-faq-mb-row" data-editor-id="<?php echo esc_attr( $editor_id ); ?>">
                    <div class="paradise-faq-mb-row-header">
                        <span class="paradise-faq-mb-num"><?php echo esc_html( (string) ( $i + 1 ) ); ?></span>
                        <span class="paradise-faq-mb-preview"><?php echo esc_html( mb_substr( $item['question'] ?? '', 0, 60 ) ); ?></span>
                        <button type="button" class="paradise-faq-mb-remove button-link-delete"><?php esc_html_e( 'Remove', 'paradise-widgets-for-elementor' ); ?></button>
                    </div>
                    <div class="paradise-faq-mb-fields">
                        <p>
                            <label><?php esc_html_e( 'Question', 'paradise-widgets-for-elementor' ); ?></label>
                            <input type="text" class="widefat paradise-faq-mb-q" name="paradise_faq_q[]" value="<?php echo esc_attr( $item['question'] ?? '' ); ?>">
                        </p>
                        <p class="paradise-faq-mb-answer-wrap">
                            <label><?php esc_html_e( 'Answer', 'paradise-widgets-for-elementor' ); ?></label>
                            <?php
                            wp_editor(
                                wp_kses_post( $item['answer'] ?? '' ),
                                $editor_id,
                                [
                                    'textarea_name' => 'paradise_faq_a[]',
                                    'media_buttons' => false,
                                    'editor_height' => 150,
                                    'tinymce'       => [
                                        'toolbar1' => 'bold italic | link | bullist numlist | removeformat',
                                        'toolbar2' => '',
                                    ],
                                    'quicktags'     => [ 'buttons' => 'strong,em,link,ul,ol,li,close' ],
                                ]
                            );
                            ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <p class="paradise-faq-mb-footer">
                <button type="button" class="button" id="paradise-faq-mb-add">
                    + <?php esc_html_e( 'Add Item', 'paradise-widgets-for-elementor' ); ?>
                </button>
            </p>
        </div>
        <?php
    }

    public static function save_meta_box( int $post_id ): void {
        if ( ! isset( $_POST[ self::NONCE . '_nonce' ] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( sanitize_key( $_POST[ self::NONCE . '_nonce' ] ), self::NONCE ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        $questions = isset( $_POST['paradise_faq_q'] ) ? (array) wp_unslash( $_POST['paradise_faq_q'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- raw value is sanitized per-field in the save handler below
        $answers   = isset( $_POST['paradise_faq_a'] ) ? (array) wp_unslash( $_POST['paradise_faq_a'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- raw value is sanitized per-field in the save handler below

        $items = [];
        foreach ( $questions as $i => $q ) {
            $q = sanitize_text_field( $q );
            $a = wp_kses_post( $answers[ $i ] ?? '' );
            if ( '' !== $q || '' !== $a ) {
                $items[] = [ 'question' => $q, 'answer' => $a ];
            }
        }

        update_post_meta( $post_id, self::META_KEY, $items );
    }

    // ── Admin assets ──────────────────────────────────────────────────────────

    public static function enqueue_admin_assets( string $hook ): void {
        global $post;
        if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
            return;
        }
        if ( ! isset( $post ) || self::POST_TYPE !== $post->post_type ) {
            return;
        }

        // Ensures TinyMCE scripts are always loaded, even when there are 0 existing rows.
        wp_enqueue_editor();

        wp_enqueue_style(
            'paradise-faq-meta-box',
            PARADISE_EW_URL . 'assets/css/faq-meta-box.css',
            [],
            PARADISE_EW_VERSION
        );
        wp_enqueue_script(
            'paradise-faq-meta-box',
            PARADISE_EW_URL . 'assets/js/faq-meta-box.js',
            [],
            PARADISE_EW_VERSION,
            true
        );
        wp_localize_script( 'paradise-faq-meta-box', 'paradiseFaqMb', [
            'labelQuestion' => __( 'Question', 'paradise-widgets-for-elementor' ),
            'labelAnswer'   => __( 'Answer',   'paradise-widgets-for-elementor' ),
            'labelRemove'   => __( 'Remove',   'paradise-widgets-for-elementor' ),
            'rowCount'      => count( self::get_raw_items( $post->ID ) ),
        ] );
    }

    // ── Admin list columns ────────────────────────────────────────────────────

    public static function admin_columns( array $columns ): array {
        $new = [];
        foreach ( $columns as $key => $label ) {
            $new[ $key ] = $label;
            if ( 'title' === $key ) {
                $new['faq_item_count']     = esc_html__( 'Items',          'paradise-widgets-for-elementor' );
                $new['faq_first_question'] = esc_html__( 'First Question', 'paradise-widgets-for-elementor' );
            }
        }
        unset( $new['date'] );
        return $new;
    }

    public static function admin_column_content( string $column, int $post_id ): void {
        $items = self::get_raw_items( $post_id );
        if ( 'faq_item_count' === $column ) {
            echo esc_html( (string) count( $items ) );
        } elseif ( 'faq_first_question' === $column ) {
            $q = $items[0]['question'] ?? '';
            echo esc_html( mb_strlen( $q ) > 80 ? mb_substr( $q, 0, 80 ) . '…' : $q );
        }
    }

    // ── Data access ───────────────────────────────────────────────────────────

    /**
     * Raw items from post meta — unsanitized, use only in trusted admin context.
     */
    public static function get_raw_items( int $post_id ): array {
        $items = get_post_meta( $post_id, self::META_KEY, true );
        return is_array( $items ) ? $items : [];
    }

    /**
     * Items for widget display.
     * Returns [ ['question' => …, 'answer' => …], … ].
     */
    public static function get_items( int $post_id ): array {
        return self::get_raw_items( $post_id );
    }

    /**
     * Returns [ '' => '— Select —', post_id => title, … ] for the widget SELECT control.
     */
    public static function get_posts_for_select(): array {
        $posts = get_posts( [
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ] );

        $options = [ '' => esc_html__( '— Select FAQ Set —', 'paradise-widgets-for-elementor' ) ];
        foreach ( $posts as $p ) {
            $options[ $p->ID ] = $p->post_title;
        }
        return $options;
    }
}
